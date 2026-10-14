<?php

namespace App\Services;

use App\Models\AirImport;
use App\Models\AirImportHbl;
use App\Models\AirImportContainer;
use App\Models\ShipmentStatusLog;
use Illuminate\Support\Facades\DB;

class AirImportService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $airImport = AirImport::create($data);

            // Handle HBLs
            if (isset($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    if (empty($hblData['hawb_no'])) {
                        continue;
                    }
                    $airImport->hbls()->create($hblData);
                }
            }

            // Handle containers
            if (isset($data['containers'])) {
                foreach ($data['containers'] as $containerData) {
                    $containerData['air_import_id'] = $airImport->id;
                    unset($containerData['id'], $containerData['selected'], $containerData['expanded']);
                    AirImportContainer::create($containerData);
                }
            }

            // Handle charges
            if (isset($data['charges'])) {
                foreach ($data['charges'] as $chargeData) {
                    $this->createCharge($airImport, $chargeData);
                }
            }

            // Log history
            ShipmentStatusLog::create([
                'shipment_type' => AirImport::class,
                'shipment_id' => $airImport->id,
                'status_code' => 'CREATED',
                'status_name' => 'Created',
                'details' => 'Air Import shipment created.',
                'user_id' => auth()->id(),
                'event_time' => now(),
            ]);

            return $airImport;
        });
    }

    public function update(AirImport $airImport, array $data)
    {
        return DB::transaction(function () use ($airImport, $data) {
            $airImport->update($data);

            // Handle HBLs - delete removed ones, update existing, add new
            if (isset($data['hbls'])) {
                $submittedHblIds = collect($data['hbls'])->pluck('id')->filter()->toArray();
                $airImport->hbls()->whereNotIn('id', $submittedHblIds)->delete();

                foreach ($data['hbls'] as $hblData) {
                    if (empty($hblData['hawb_no']) && empty($hblData['id'])) {
                        continue;
                    }
                    if (isset($hblData['id'])) {
                        $hbl = AirImportHbl::find($hblData['id']);
                        if ($hbl) $hbl->update($hblData);
                    } else {
                        $airImport->hbls()->create($hblData);
                    }
                }
            }

            // Handle containers - delete removed ones, update existing, add new
            if (isset($data['containers'])) {
                $submittedContainerIds = collect($data['containers'])->pluck('id')->filter()->toArray();
                $airImport->containers()->whereNotIn('id', $submittedContainerIds)->delete();

                foreach ($data['containers'] as $containerData) {
                    unset($containerData['selected'], $containerData['expanded']);
                    if (isset($containerData['id'])) {
                        $container = AirImportContainer::find($containerData['id']);
                        if ($container) $container->update($containerData);
                    } else {
                        $containerData['air_import_id'] = $airImport->id;
                        AirImportContainer::create($containerData);
                    }
                }
            }

            // Handle charges - delete removed ones, update existing, add new
            if (isset($data['charges'])) {
                $submittedChargeIds = collect($data['charges'])->pluck('id')->filter()->toArray();
                $airImport->charges()->whereNotIn('id', $submittedChargeIds)->delete();

                foreach ($data['charges'] as $chargeData) {
                    if (!empty($chargeData['id'])) {
                        $charge = \App\Models\Charge::find($chargeData['id']);
                        if ($charge) {
                            $this->updateCharge($charge, $chargeData);
                        }
                    } else {
                        $this->createCharge($airImport, $chargeData);
                    }
                }
            } else {
                $airImport->charges()->delete();
            }

            // Log history
            ShipmentStatusLog::create([
                'shipment_type' => AirImport::class,
                'shipment_id' => $airImport->id,
                'status_code' => 'UPDATED',
                'status_name' => 'Updated',
                'details' => 'Shipment details updated.',
                'user_id' => auth()->id(),
                'event_time' => now(),
            ]);

            return $airImport;
        });
    }

    public function createCharge(AirImport $airImport, array $data)
    {
        $currencyId = null;
        if (isset($data['currency'])) {
            $currency = \App\Models\Currency::where('code', $data['currency'])->first();
            $currencyId = $currency ? $currency->id : null;
        }

        $type = (isset($data['pr']) && $data['pr'] === 'Pay') ? 'AP' : 'AR';
        $pc = (isset($data['ppc']) && $data['ppc'] === 'Prepaid') ? 'PREPAID' : 'COLLECT';

        $rate = floatval($data['rate'] ?? 0);
        $qty = floatval($data['qty'] ?? 1);
        $amount = $rate * $qty;
        $taxPercent = floatval($data['vat'] ?? 0);
        $taxAmount = $amount * ($taxPercent / 100);
        $totalAmount = $amount + $taxAmount;

        $partyNameId = !empty($data['party_name_id']) ? $data['party_name_id'] : null;

        return $airImport->charges()->create([
            'type' => $type,
            'charge_code' => $data['chrg_code'] ?? '',
            'charge_name' => !empty($data['charge_name']) ? $data['charge_name'] : ($data['chrg_code'] ?? 'Charge'),
            'party' => $data['party'] ?? 'Custom',
            'party_name' => $data['party_name'] ?? '',
            'sal' => $data['sal'] ?? 'Air',
            'pc' => $pc,
            'qty' => $qty,
            'unit' => $data['qty_type'] ?? $data['unit'] ?? 'UNIT',
            'currency_id' => $currencyId,
            'rate' => $rate,
            'amount' => $amount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'bill_to_id' => ($type === 'AR') ? $partyNameId : null,
            'vendor_id' => ($type === 'AP') ? $partyNameId : null,
            'invoice_no' => $data['inv_no'] ?? null,
            'invoice_date' => !empty($data['financial_date']) ? $data['financial_date'] : null,
            'remark' => $data['eq_bl_no'] ?? null,
            'seal_no2' => $data['seal_no2'] ?? null,
            'pickup_no' => $data['pickup_no'] ?? null,
            'cprs_no' => $data['cprs_no'] ?? null,
            'cnru_no' => $data['cnru_no'] ?? null,
            'it_no' => $data['it_no'] ?? null,
            'dg' => $data['dg'] ?? null,
            'temp' => $data['temp'] ?? null,
            'vent' => $data['vent'] ?? null,
            'storage_start_date' => !empty($data['storage_start_date']) ? $data['storage_start_date'] : null,
            'storage_end_date' => !empty($data['storage_end_date']) ? $data['storage_end_date'] : null,
            'carrier_release' => !empty($data['carrier_release']),
            'yard_location' => $data['yard_location'] ?? null,
            'unload_vessel_date' => !empty($data['unload_vessel_date']) ? $data['unload_vessel_date'] : null,
            'gate_in_date' => !empty($data['gate_in_date']) ? $data['gate_in_date'] : null,
            'rail_start_date' => !empty($data['rail_start_date']) ? $data['rail_start_date'] : null,
            'pod_eta_date' => !empty($data['pod_eta_date']) ? $data['pod_eta_date'] : null,
            'available_pickup' => !empty($data['available_pickup']),
            'weight_lb' => !empty($data['weight_lb']) ? floatval($data['weight_lb']) : null,
            'appt_date' => !empty($data['appt_date']) ? $data['appt_date'] : null,
            'trucker_id' => !empty($data['trucker_id']) ? $data['trucker_id'] : null,
            'pickup_date' => !empty($data['pickup_date']) ? $data['pickup_date'] : null,
            'gate_out_date' => !empty($data['gate_out_date']) ? $data['gate_out_date'] : null,
            'fdest_eta_date' => !empty($data['fdest_eta_date']) ? $data['fdest_eta_date'] : null,
            'eta_door_date' => !empty($data['eta_door_date']) ? $data['eta_door_date'] : null,
            'ata_door_date' => !empty($data['ata_door_date']) ? $data['ata_door_date'] : null,
            'measurement_cft' => !empty($data['measurement_cft']) ? floatval($data['measurement_cft']) : null,
            'container_remarks' => $data['remarks'] ?? null,
            'internal_remarks' => $data['internal_remarks'] ?? null,
            'empty_confirmed_date' => !empty($data['empty_confirmed_date']) ? $data['empty_confirmed_date'] : null,
            'empty_return_date' => !empty($data['empty_return_date']) ? $data['empty_return_date'] : null,
            'complete' => !empty($data['complete']),
        ]);
    }

    public function updateCharge(\App\Models\Charge $charge, array $data)
    {
        $currencyId = null;
        if (isset($data['currency'])) {
            $currency = \App\Models\Currency::where('code', $data['currency'])->first();
            $currencyId = $currency ? $currency->id : null;
        }

        $type = (isset($data['pr']) && $data['pr'] === 'Pay') ? 'AP' : 'AR';
        $pc = (isset($data['ppc']) && $data['ppc'] === 'Prepaid') ? 'PREPAID' : 'COLLECT';

        $rate = floatval($data['rate'] ?? $charge->rate);
        $qty = floatval($data['qty'] ?? $charge->qty);
        $amount = $rate * $qty;
        $taxPercent = floatval($data['vat'] ?? $charge->tax_percent);
        $taxAmount = $amount * ($taxPercent / 100);
        $totalAmount = $amount + $taxAmount;

        $partyNameId = !empty($data['party_name_id']) ? $data['party_name_id'] : null;

        $charge->update([
            'type' => $type,
            'charge_code' => $data['chrg_code'] ?? $charge->charge_code,
            'charge_name' => !empty($data['charge_name']) ? $data['charge_name'] : ($data['chrg_code'] ?? $charge->charge_name),
            'party' => $data['party'] ?? $charge->party ?? 'Custom',
            'party_name' => $data['party_name'] ?? $charge->party_name ?? '',
            'sal' => $data['sal'] ?? $charge->sal ?? 'Air',
            'pc' => $pc,
            'qty' => $qty,
            'unit' => $data['qty_type'] ?? $data['unit'] ?? $charge->unit,
            'currency_id' => $currencyId ?? $charge->currency_id,
            'rate' => $rate,
            'amount' => $amount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'bill_to_id' => ($type === 'AR') ? $partyNameId : $charge->bill_to_id,
            'vendor_id' => ($type === 'AP') ? $partyNameId : $charge->vendor_id,
            'invoice_no' => $data['inv_no'] ?? $charge->invoice_no,
            'invoice_date' => !empty($data['financial_date']) ? $data['financial_date'] : $charge->invoice_date,
            'remark' => $data['eq_bl_no'] ?? $charge->remark,
            'seal_no2' => $data['seal_no2'] ?? $charge->seal_no2,
            'pickup_no' => $data['pickup_no'] ?? $charge->pickup_no,
            'cprs_no' => $data['cprs_no'] ?? $charge->cprs_no,
            'cnru_no' => $data['cnru_no'] ?? $charge->cnru_no,
            'it_no' => $data['it_no'] ?? $charge->it_no,
            'dg' => $data['dg'] ?? $charge->dg,
            'temp' => $data['temp'] ?? $charge->temp,
            'vent' => $data['vent'] ?? $charge->vent,
            'storage_start_date' => !empty($data['storage_start_date']) ? $data['storage_start_date'] : $charge->storage_start_date,
            'storage_end_date' => !empty($data['storage_end_date']) ? $data['storage_end_date'] : $charge->storage_end_date,
            'carrier_release' => isset($data['carrier_release']) ? !empty($data['carrier_release']) : $charge->carrier_release,
            'yard_location' => $data['yard_location'] ?? $charge->yard_location,
            'unload_vessel_date' => !empty($data['unload_vessel_date']) ? $data['unload_vessel_date'] : $charge->unload_vessel_date,
            'gate_in_date' => !empty($data['gate_in_date']) ? $data['gate_in_date'] : $charge->gate_in_date,
            'rail_start_date' => !empty($data['rail_start_date']) ? $data['rail_start_date'] : $charge->rail_start_date,
            'pod_eta_date' => !empty($data['pod_eta_date']) ? $data['pod_eta_date'] : $charge->pod_eta_date,
            'available_pickup' => isset($data['available_pickup']) ? !empty($data['available_pickup']) : $charge->available_pickup,
            'weight_lb' => isset($data['weight_lb']) ? floatval($data['weight_lb']) : $charge->weight_lb,
            'appt_date' => !empty($data['appt_date']) ? $data['appt_date'] : $charge->appt_date,
            'trucker_id' => !empty($data['trucker_id']) ? $data['trucker_id'] : $charge->trucker_id,
            'pickup_date' => !empty($data['pickup_date']) ? $data['pickup_date'] : $charge->pickup_date,
            'gate_out_date' => !empty($data['gate_out_date']) ? $data['gate_out_date'] : $charge->gate_out_date,
            'fdest_eta_date' => !empty($data['fdest_eta_date']) ? $data['fdest_eta_date'] : $charge->fdest_eta_date,
            'eta_door_date' => !empty($data['eta_door_date']) ? $data['eta_door_date'] : $charge->eta_door_date,
            'ata_door_date' => !empty($data['ata_door_date']) ? $data['ata_door_date'] : $charge->ata_door_date,
            'measurement_cft' => isset($data['measurement_cft']) ? floatval($data['measurement_cft']) : $charge->measurement_cft,
            'container_remarks' => $data['remarks'] ?? $charge->container_remarks,
            'internal_remarks' => $data['internal_remarks'] ?? $charge->internal_remarks,
            'empty_confirmed_date' => !empty($data['empty_confirmed_date']) ? $data['empty_confirmed_date'] : $charge->empty_confirmed_date,
            'empty_return_date' => !empty($data['empty_return_date']) ? $data['empty_return_date'] : $charge->empty_return_date,
            'complete' => isset($data['complete']) ? !empty($data['complete']) : $charge->complete,
        ]);

        return $charge;
    }
}
