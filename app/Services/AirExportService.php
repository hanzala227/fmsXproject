<?php

namespace App\Services;

use App\Models\AirExport;
use App\Models\AirExportHbl;
use App\Models\Charge;
use Illuminate\Support\Facades\DB;

class AirExportService
{
    private function coerceNumericDefaults(array $data): array
    {
        $zeroFields = ['pkg_qty', 'gross_weight', 'chargeable_weight', 'volume', 'buying_rate', 'selling_rate'];
        foreach ($zeroFields as $field) {
            if (array_key_exists($field, $data) && ($data[$field] === null || $data[$field] === '')) {
                $data[$field] = 0;
            }
        }
        return $data;
    }

    private function syncRouteDefaults(array $data): array
    {
        if (!empty($data['routes']) && is_array($data['routes'])) {
            $firstRoute = $data['routes'][0] ?? null;
            $lastRoute = end($data['routes']) ?: null;
            if ($firstRoute && is_array($firstRoute)) {
                if (empty($data['dep_port_id']) && !empty($firstRoute['airport_id'])) {
                    $data['dep_port_id'] = $firstRoute['airport_id'];
                }
                if (empty($data['etd']) && !empty($firstRoute['etd'])) {
                    $data['etd'] = substr($firstRoute['etd'], 0, 10);
                }
                if (empty($data['atd']) && !empty($firstRoute['atd'])) {
                    $data['atd'] = substr($firstRoute['atd'], 0, 10);
                }
                if (empty($data['flight_no']) && !empty($firstRoute['flight_no'])) {
                    $data['flight_no'] = $firstRoute['flight_no'];
                }
                if (empty($data['carrier_id']) && !empty($firstRoute['carrier_id'])) {
                    $data['carrier_id'] = $firstRoute['carrier_id'];
                }
            }
            if ($lastRoute && is_array($lastRoute)) {
                if (empty($data['dst_port_id']) && !empty($lastRoute['airport_id'])) {
                    $data['dst_port_id'] = $lastRoute['airport_id'];
                }
                if (empty($data['eta']) && !empty($lastRoute['eta'])) {
                    $data['eta'] = substr($lastRoute['eta'], 0, 10);
                }
                if (empty($data['ata']) && !empty($lastRoute['ata'])) {
                    $data['ata'] = substr($lastRoute['ata'], 0, 10);
                }
            }
        }
        return $data;
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data = $this->coerceNumericDefaults($data);
            $data = $this->syncRouteDefaults($data);
            if (empty($data['file_no'])) {
                $data['file_no'] = \App\Services\DocumentSequenceService::generateBookingNo('MAE', AirExport::class);
            }
            $airExport = AirExport::create($data);

            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    if (empty($hblData['hawb_no'])) {
                        continue;
                    }
                    $hblData = $this->coerceNumericDefaults($hblData);
                    $airExport->hbls()->create($hblData);
                }
            }

            if (isset($data['charges'])) {
                foreach ($data['charges'] as $chargeData) {
                    $this->createCharge($airExport, $chargeData);
                }
            }

            $airExport->statusLogs()->create([
                'user_id' => auth()->id(),
                'status_code' => 'CREATED',
                'status_name' => 'CREATED',
                'details' => 'Air Export shipment created.',
            ]);

            return $airExport;
        });
    }

    public function update(AirExport $airExport, array $data)
    {
        return DB::transaction(function () use ($airExport, $data) {
            $data = $this->coerceNumericDefaults($data);
            $data = $this->syncRouteDefaults($data);
            $airExport->update($data);

            $submittedHblIds = [];
            if (isset($data['hbls']) && is_array($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    if (empty($hblData['hawb_no'])) {
                        continue;
                    }
                    $hblData = $this->coerceNumericDefaults($hblData);
                    if (isset($hblData['id']) && $hblData['id']) {
                        $hbl = AirExportHbl::withTrashed()->find($hblData['id']);
                        if ($hbl && $hbl->air_export_id == $airExport->id) {
                            $hbl->restore();
                            $hbl->update($hblData);
                            $submittedHblIds[] = $hbl->id;
                            continue;
                        }
                    }
                    $newHbl = $airExport->hbls()->create($hblData);
                    $submittedHblIds[] = $newHbl->id;
                }
            }
            $airExport->hbls()->whereNotIn('id', $submittedHblIds)->delete();

            if (isset($data['charges'])) {
                $submittedChargeIds = collect($data['charges'])->pluck('id')->filter()->toArray();
                $airExport->charges()->where('is_invoiced', false)->whereNotIn('id', $submittedChargeIds)->delete();

                foreach ($data['charges'] as $chargeData) {
                    if (!empty($chargeData['id'])) {
                        $charge = Charge::find($chargeData['id']);
                        if ($charge) {
                            $this->updateCharge($charge, $chargeData);
                        }
                    } else {
                        $this->createCharge($airExport, $chargeData);
                    }
                }
            } else {
                $airExport->charges()->where('is_invoiced', false)->delete();
            }

            $airExport->statusLogs()->create([
                'user_id' => auth()->id(),
                'status_code' => 'UPDATED',
                'status_name' => 'UPDATED',
                'details' => 'Air Export shipment updated.',
            ]);

            return $airExport;
        });
    }

    public function createCharge(AirExport $airExport, array $data)
    {
        $currencyId = null;
        if (isset($data['currency'])) {
            $currency = \App\Models\Currency::where('code', $data['currency'])->first();
            $currencyId = $currency ? $currency->id : null;
        } elseif (isset($data['currency_id'])) {
            $currencyId = $data['currency_id'];
        }

        $type = (isset($data['pr']) && $data['pr'] === 'Pay') ? 'AP' : ($data['type'] ?? 'AR');
        $pc = (isset($data['ppc']) && $data['ppc'] === 'Prepaid') ? 'PREPAID' : 'COLLECT';

        $rate = floatval($data['rate'] ?? 0);
        $qty = floatval($data['qty'] ?? 1);
        $roe = floatval($data['roe'] ?? 1.0);
        $amount = $rate * $qty * $roe;
        $taxPercent = floatval($data['vat'] ?? 0);
        $taxAmount = $amount * ($taxPercent / 100);
        $totalAmount = $amount + $taxAmount;

        $partyNameId = !empty($data['party_name_id']) ? $data['party_name_id'] : null;

        return $airExport->charges()->create([
            'type' => $type,
            'charge_code' => $data['chrg_code'] ?? ($data['charge_code'] ?? ''),
            'charge_name' => !empty($data['charge_name']) ? $data['charge_name'] : ($data['chrg_code'] ?? 'Charge'),
            'party' => $data['party'] ?? 'Custom',
            'party_name' => $data['party_name'] ?? '',
            'sal' => $data['sal'] ?? 'Air',
            'pc' => $pc,
            'qty' => $qty,
            'unit' => $data['qty_type'] ?? ($data['unit'] ?? 'B/L'),
            'currency_id' => $currencyId,
            'rate' => $rate,
            'roe' => $roe,
            'amount' => $amount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'bill_to_id' => ($type === 'AR') ? ($partyNameId ?? ($data['bill_to_id'] ?? null)) : ($data['bill_to_id'] ?? null),
            'vendor_id' => ($type === 'AP') ? ($partyNameId ?? ($data['vendor_id'] ?? null)) : ($data['vendor_id'] ?? null),
            'invoice_no' => $data['inv_no'] ?? ($data['invoice_no'] ?? null),
            'invoice_date' => !empty($data['financial_date']) ? $data['financial_date'] : null,
            'remark' => $data['eq_bl_no'] ?? ($data['remark'] ?? null),
        ]);
    }

    public function updateCharge(Charge $charge, array $data)
    {
        $currencyId = $charge->currency_id;
        if (isset($data['currency'])) {
            $currency = \App\Models\Currency::where('code', $data['currency'])->first();
            if ($currency) $currencyId = $currency->id;
        } elseif (isset($data['currency_id'])) {
            $currencyId = $data['currency_id'];
        }

        $type = (isset($data['pr']) && $data['pr'] === 'Pay') ? 'AP' : ($data['type'] ?? $charge->type);
        $pc = (isset($data['ppc']) && $data['ppc'] === 'Prepaid') ? 'PREPAID' : 'COLLECT';

        $rate = floatval($data['rate'] ?? $charge->rate);
        $qty = floatval($data['qty'] ?? $charge->qty);
        $roe = floatval($data['roe'] ?? $charge->roe ?? 1.0);
        $amount = $rate * $qty * $roe;
        $taxPercent = floatval($data['vat'] ?? $charge->tax_percent ?? 0);
        $taxAmount = $amount * ($taxPercent / 100);
        $totalAmount = $amount + $taxAmount;

        $partyNameId = !empty($data['party_name_id']) ? $data['party_name_id'] : null;

        $charge->update([
            'type' => $type,
            'charge_code' => $data['chrg_code'] ?? ($data['charge_code'] ?? $charge->charge_code),
            'charge_name' => !empty($data['charge_name']) ? $data['charge_name'] : $charge->charge_name,
            'party' => $data['party'] ?? $charge->party,
            'party_name' => $data['party_name'] ?? $charge->party_name,
            'sal' => $data['sal'] ?? $charge->sal,
            'pc' => $pc,
            'qty' => $qty,
            'unit' => $data['qty_type'] ?? ($data['unit'] ?? $charge->unit),
            'currency_id' => $currencyId,
            'rate' => $rate,
            'roe' => $roe,
            'amount' => $amount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'bill_to_id' => ($type === 'AR') ? ($partyNameId ?? $charge->bill_to_id) : $charge->bill_to_id,
            'vendor_id' => ($type === 'AP') ? ($partyNameId ?? $charge->vendor_id) : $charge->vendor_id,
            'invoice_no' => $data['inv_no'] ?? ($data['invoice_no'] ?? $charge->invoice_no),
            'invoice_date' => !empty($data['financial_date']) ? $data['financial_date'] : $charge->invoice_date,
            'remark' => $data['eq_bl_no'] ?? ($data['remark'] ?? $charge->remark),
        ]);

        return $charge;
    }
}
