<?php

namespace App\Services;

use App\Models\OceanExport;
use App\Models\OceanExportHbl;
use App\Models\OceanExportContainer;
use App\Models\ShipmentStatusLog;
use Illuminate\Support\Facades\DB;

class OceanExportService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Normalize empty strings to null for all ID fields
            foreach ($data as $key => $value) {
                if (is_string($value) && $value === '' && str_ends_with($key, '_id')) {
                    $data[$key] = null;
                }
            }

            // We rely on OceanExportController's sanitization for foreign key existence
            $oceanExport = OceanExport::create($data);

            if (isset($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    $hblData = array_merge($this->getHblDefaults(), $hblData);

                    foreach ($hblData as $hk => $hv) {
                        if (is_string($hv) && $hv === '' && str_ends_with($hk, '_id')) {
                            $hblData[$hk] = null;
                        }
                    }
                    $newHbl = $oceanExport->hbls()->create($hblData);
                    
                    if (isset($hblData['commodities']) && is_array($hblData['commodities'])) {
                        foreach ($hblData['commodities'] as $commodityData) {
                            $newHbl->commodities()->create($commodityData);
                        }
                    }
                }
            }

            if (isset($data['containers'])) {
                foreach ($data['containers'] as $containerData) {
                    $containerData = array_merge($this->getContainerDefaults(), $containerData);
                    $oceanExport->containers()->create($containerData);
                }
            }

            if (isset($data['charges'])) {
                foreach ($data['charges'] as $chargeData) {
                    $chargeData = array_merge($this->getChargeDefaults(), $chargeData);
                    $oceanExport->charges()->create($this->mapChargeData($chargeData));
                }
            }

            $oceanExport->statusLogs()->create([
                'user_id' => auth()->id(),
                'status_code' => 'CREATED',
                'status_name' => 'CREATED',
                'details' => 'Ocean Export shipment created.',
            ]);

            return $oceanExport;
        });
    }

    public function update(OceanExport $oceanExport, array $data)
    {
        return DB::transaction(function () use ($oceanExport, $data) {
            $oceanExport->update($data);

            $submittedHblIds = [];
            if (isset($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    if (isset($hblData['id'])) {
                        $hbl = OceanExportHbl::find($hblData['id']);
                        if ($hbl && $hbl->ocean_export_id === $oceanExport->id) {
                            $hbl->update($hblData);
                            $submittedHblIds[] = $hbl->id;
                        }
                    } else {
                        $hblData = array_merge($this->getHblDefaults(), $hblData);
                        $hbl = $oceanExport->hbls()->create($hblData);
                        $submittedHblIds[] = $hbl->id;
                    }
                    
                    if (isset($hbl)) {
                        if (isset($hblData['commodities']) && is_array($hblData['commodities'])) {
                            $submittedCommIds = [];
                            foreach ($hblData['commodities'] as $commData) {
                                if (isset($commData['id'])) {
                                    $commModel = $hbl->commodities()->find($commData['id']);
                                    if ($commModel) {
                                        $commModel->update($commData);
                                        $submittedCommIds[] = $commModel->id;
                                    }
                                } else {
                                    $newComm = $hbl->commodities()->create($commData);
                                    $submittedCommIds[] = $newComm->id;
                                }
                            }
                            $hbl->commodities()->whereNotIn('id', $submittedCommIds)->delete();
                        } else {
                            $hbl->commodities()->delete();
                        }
                    }
                }
            }
            $oceanExport->hbls()->whereNotIn('id', $submittedHblIds)->delete();

            $submittedContainerIds = [];
            if (isset($data['containers'])) {
                foreach ($data['containers'] as $containerData) {
                    if (isset($containerData['id'])) {
                        $container = OceanExportContainer::find($containerData['id']);
                        if ($container && $container->ocean_export_id === $oceanExport->id) {
                            $container->update($containerData);
                            $submittedContainerIds[] = $container->id;
                        }
                    } else {
                        $containerData = array_merge($this->getContainerDefaults(), $containerData);
                        $newContainer = $oceanExport->containers()->create($containerData);
                        $submittedContainerIds[] = $newContainer->id;
                    }
                }
            }
            $oceanExport->containers()->whereNotIn('id', $submittedContainerIds)->delete();

            if (isset($data['charges'])) {
                $oceanExport->charges()->delete();
                foreach ($data['charges'] as $chargeData) {
                    $chargeData = array_merge($this->getChargeDefaults(), $chargeData);
                    $oceanExport->charges()->create($this->mapChargeData($chargeData));
                }
            }

            $oceanExport->statusLogs()->create([
                'user_id' => auth()->id(),
                'status_code' => 'UPDATED',
                'status_name' => 'UPDATED',
                'details' => 'Ocean Export shipment updated.',
            ]);

            return $oceanExport;
        });
    }

    private function getHblDefaults(): array
    {
        return [
            'hbl_no' => '',
            'quotation_no' => '',
            'vessel_name' => '',
            'voyage_no' => '',
            'pre_carriage_by' => '',
            'service_term' => '',
            'ship_mode' => '',
            'ship_type' => '',
            'cargo_type' => '',
            'freight_payable_at' => '',
            'is_express_bl' => 0,
            'is_door_move' => 0,
            'is_customs_clear' => 0,
            'is_customs_hold' => 0,
            'is_obl_received' => 0,
            'is_fr_released' => 0,
            'is_an_sent' => 0,
            'is_do_sent' => 0,
            'is_ecommerce' => 0,
            'is_customs_doc' => 0,
        ];
    }

    private function getContainerDefaults(): array
    {
        return [
            'container_no' => '',
            'pkg_qty' => 0,
            'weight_kg' => 0,
            'measure_cbm' => 0,
            'is_dg' => 0,
        ];
    }

    private function getChargeDefaults(): array
    {
        return [
            'charge_code' => '',
            'amount' => 0,
        ];
    }

    protected function mapChargeData(array $data): array
    {
        $type = ($data['pr'] ?? 'Rec') === 'Pay' ? 'AP' : 'AR';
        $pc = ($data['ppc'] ?? 'Colle') === 'Prepaid' ? 'PREPAID' : 'COLLECT';

        $rate = floatval($data['rate'] ?? 0);
        $qty = floatval($data['qty'] ?? 1);
        $amount = $rate * $qty;
        $taxPercent = floatval($data['vat'] ?? 0);
        $taxAmount = $amount * ($taxPercent / 100);
        $totalAmount = $amount + $taxAmount;

        $partyNameId = isset($data['party_name_id']) ? intval($data['party_name_id']) : null;
        if (!$partyNameId && isset($data['party_name']) && is_numeric($data['party_name'])) {
            $partyNameId = intval($data['party_name']);
        }

        $mapped = [
            'party' => $data['party'] ?? 'Freight',
            'party_name' => $data['party_name'] ?? '',
            'sal' => $data['sal'] ?? 'Sea',
            'charge_code' => $data['chrg_code'] ?? '',
            'charge_name' => $data['chrg_code'] ?? 'Charge',
            'type' => $type,
            'pc' => $pc,
            'qty' => $qty,
            'unit' => $data['qty_type'] ?? 'B/L',
            'rate' => $rate,
            'amount' => $amount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'invoice_no' => $data['inv_no'] ?? null,
            'invoice_date' => $data['financial_date'] ?? null,
            'remark' => $data['eq_bl_no'] ?? $data['remark_text'] ?? null,
        ];

        if ($type === 'AP') {
            $mapped['vendor_id'] = $partyNameId;
        } else {
            $mapped['bill_to_id'] = $partyNameId;
        }

        if (!empty($data['currency'])) {
            $currency = \App\Models\Currency::where('code', $data['currency'])->first();
            $mapped['currency_id'] = $currency?->id;
        }

        return $mapped;
    }
}
