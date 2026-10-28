<?php

namespace App\Services;

use App\Models\OceanImport;
use App\Models\OceanImportHbl;
use App\Models\OceanImportContainer;
use Illuminate\Support\Facades\DB;

class OceanImportService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            \Log::info('=== OceanImportService Store ===');
            \Log::info('Data received in service:', [
                'has_containers' => isset($data['containers']),
                'containers_count' => isset($data['containers']) ? count($data['containers']) : 0
            ]);
            
            $oceanImport = OceanImport::create($data);
            \Log::info('OceanImport created with ID:', ['id' => $oceanImport->id]);

            if (isset($data['containers']) && is_array($data['containers'])) {
                \Log::info('Processing containers:', ['count' => count($data['containers'])]);
                foreach ($data['containers'] as $index => $containerData) {
                    \Log::info("Creating container #{$index}:", $containerData);
                    $container = $oceanImport->containers()->create($containerData);
                    \Log::info("Container created:", ['id' => $container->id, 'container_no' => $container->container_no]);
                }
            } else {
                \Log::warning('No containers data found or containers is not an array');
            }

            if (isset($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    $hbl = $oceanImport->hbls()->create($hblData);
                    $this->syncHblRelations($hbl, $hblData, $oceanImport);
                }
            }

            if (isset($data['charges'])) {
                foreach ($data['charges'] as $chargeData) {
                    $currencyId = null;
                    if (isset($chargeData['currency'])) {
                        $currencyId = \App\Models\Currency::where('code', $chargeData['currency'])->first()?->id;
                    }
                    $type = (isset($chargeData['pr']) && $chargeData['pr'] == 'Pay') ? 'AP' : 'AR';
                    $pc = (isset($chargeData['ppc']) && $chargeData['ppc'] == 'Prepaid') ? 'PREPAID' : 'COLLECT';
                    
                    $rate = floatval($chargeData['rate'] ?? 0);
                    $qty = floatval($chargeData['qty'] ?? 1);
                    $amount = $rate * $qty;
                    $taxPercent = floatval($chargeData['vat'] ?? 0);
                    $taxAmount = $amount * ($taxPercent / 100);
                    $totalAmount = $amount + $taxAmount;

                    $partyNameId = isset($chargeData['party_name_id']) ? intval($chargeData['party_name_id']) : null;
                    if (!$partyNameId && isset($chargeData['party_name']) && is_numeric($chargeData['party_name'])) {
                        $partyNameId = intval($chargeData['party_name']);
                    }
                    $billToId = null;
                    $vendorId = null;
                    if ($type === 'AP') {
                        $vendorId = $partyNameId;
                    } else {
                        $billToId = $partyNameId;
                    }

                    $oceanImport->charges()->create([
                        'type' => $type,
                        'charge_code' => $chargeData['chrg_code'] ?? '',
                        'charge_name' => $chargeData['chrg_code'] ?? 'Charge',
                        'bill_to_id' => $billToId,
                        'vendor_id' => $vendorId,
                        'pc' => $pc,
                        'qty' => $qty,
                        'unit' => $chargeData['qty_type'] ?? 'UNIT',
                        'currency_id' => $currencyId,
                        'rate' => $rate,
                        'amount' => $amount,
                        'tax_percent' => $taxPercent,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'roe' => floatval($chargeData['roe'] ?? 1),
                        'vat' => $taxPercent,
                        'invoice_no' => $chargeData['inv_no'] ?? null,
                        'invoice_date' => isset($chargeData['financial_date']) ? $chargeData['financial_date'] : null,
                        'remark' => $chargeData['remark_text'] ?? $chargeData['eq_bl_no'] ?? null,
                    ]);
                }
            }

            if (isset($data['memos'])) {
                foreach ($data['memos'] as $memoData) {
                    $oceanImport->memos()->create([
                        'subject' => $memoData['subject'],
                        'content' => $memoData['content'] ?? null,
                        'user_id' => $memoData['user_id'] ?? auth()->id(),
                    ]);
                }
            }

            // Log history
            \App\Models\OceanImportHistory::create([
                'ocean_import_id' => $oceanImport->id,
                'action' => 'Created',
                'details' => 'Shipment created successfully.',
                'user_id' => auth()->id() ?? null
            ]);

            return $oceanImport;
        });
    }

    public function update(OceanImport $oceanImport, array $data)
    {
        return DB::transaction(function () use ($oceanImport, $data) {
            $oceanImport->update($data);

            if (isset($data['containers'])) {
                $submittedContainerIds = collect($data['containers'])->pluck('id')->filter()->toArray();
                $oceanImport->containers()->whereNotIn('id', $submittedContainerIds)->delete();

                foreach ($data['containers'] as $containerData) {
                    if (isset($containerData['id'])) {
                        $container = OceanImportContainer::find($containerData['id']);
                        if ($container) $container->update($containerData);
                    } else {
                        $oceanImport->containers()->create($containerData);
                    }
                }
            } else {
                $oceanImport->containers()->delete();
            }

            if (isset($data['hbls'])) {
                $submittedHblIds = collect($data['hbls'])->pluck('id')->filter()->toArray();
                $oceanImport->hbls()->whereNotIn('id', $submittedHblIds)->delete();

                foreach ($data['hbls'] as $hblData) {
                    if (isset($hblData['id'])) {
                        $hbl = OceanImportHbl::find($hblData['id']);
                        if ($hbl) {
                            $hbl->update($hblData);
                            $this->syncHblRelations($hbl, $hblData, $oceanImport);
                        }
                    } else {
                        $hbl = $oceanImport->hbls()->create($hblData);
                        $this->syncHblRelations($hbl, $hblData, $oceanImport);
                    }
                }
            } else {
                $oceanImport->hbls()->delete();
            }

            if (isset($data['charges'])) {
                $submittedChargeIds = collect($data['charges'])->pluck('id')->filter()->toArray();
                $oceanImport->charges()->where('is_invoiced', false)->whereNotIn('id', $submittedChargeIds)->delete();

                foreach ($data['charges'] as $chargeData) {
                    $currencyId = null;
                    if (isset($chargeData['currency'])) {
                        $currencyId = \App\Models\Currency::where('code', $chargeData['currency'])->first()?->id;
                    }
                    $type = (isset($chargeData['pr']) && $chargeData['pr'] == 'Pay') ? 'AP' : 'AR';
                    $pc = (isset($chargeData['ppc']) && $chargeData['ppc'] == 'Prepaid') ? 'PREPAID' : 'COLLECT';
                    
                    $rate = floatval($chargeData['rate'] ?? 0);
                    $qty = floatval($chargeData['qty'] ?? 1);
                    $amount = $rate * $qty;
                    $taxPercent = floatval($chargeData['vat'] ?? 0);
                    $taxAmount = $amount * ($taxPercent / 100);
                    $totalAmount = $amount + $taxAmount;

                    $partyNameId = isset($chargeData['party_name_id']) ? intval($chargeData['party_name_id']) : null;
                    if (!$partyNameId && isset($chargeData['party_name']) && is_numeric($chargeData['party_name'])) {
                        $partyNameId = intval($chargeData['party_name']);
                    }
                    $billToId = null;
                    $vendorId = null;
                    if ($type === 'AP') {
                        $vendorId = $partyNameId;
                    } else {
                        $billToId = $partyNameId;
                    }

                    $mapped = [
                        'type' => $type,
                        'party' => $chargeData['party'] ?? 'Freight',
                        'party_name' => $chargeData['party_name'] ?? '',
                        'sal' => $chargeData['sal'] ?? 'Sea',
                        'charge_code' => $chargeData['chrg_code'] ?? '',
                        'charge_name' => $chargeData['chrg_code'] ?? 'Charge',
                        'bill_to_id' => $billToId,
                        'vendor_id' => $vendorId,
                        'pc' => $pc,
                        'qty' => $qty,
                        'unit' => $chargeData['qty_type'] ?? 'UNIT',
                        'currency_id' => $currencyId,
                        'rate' => $rate,
                        'amount' => $amount,
                        'tax_percent' => $taxPercent,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'roe' => floatval($chargeData['roe'] ?? 1),
                        'vat' => $taxPercent,
                        'invoice_no' => $chargeData['inv_no'] ?? null,
                        'invoice_date' => isset($chargeData['financial_date']) ? $chargeData['financial_date'] : null,
                        'remark' => $chargeData['remark_text'] ?? $chargeData['eq_bl_no'] ?? null,
                    ];

                    if (isset($chargeData['id']) && $chargeData['id']) {
                        $charge = \App\Models\OceanImportCharge::find($chargeData['id']);
                        if ($charge) $charge->update($mapped);
                    } else {
                        $oceanImport->charges()->create($mapped);
                    }
                }
            } else {
                $oceanImport->charges()->where('is_invoiced', false)->delete();
            }

            if (isset($data['memos'])) {
                $submittedMemoIds = collect($data['memos'])->pluck('id')->filter()->toArray();
                $oceanImport->memos()->whereNotIn('id', $submittedMemoIds)->delete();

                foreach ($data['memos'] as $memoData) {
                    $mapped = [
                        'subject' => $memoData['subject'],
                        'content' => strlen($memoData['content'] ?? '') > 5000 ? substr($memoData['content'], 0, 5000) : ($memoData['content'] ?? null),
                        'user_id' => $memoData['user_id'] ?? auth()->id(),
                    ];

                    if (isset($memoData['id']) && $memoData['id']) {
                        $memo = \App\Models\OceanImportMemo::find($memoData['id']);
                        if ($memo) $memo->update($mapped);
                    } else {
                        $oceanImport->memos()->create($mapped);
                    }
                }
            } else {
                $oceanImport->memos()->delete();
            }

            // Log history
            \App\Models\OceanImportHistory::create([
                'ocean_import_id' => $oceanImport->id,
                'action' => 'Updated',
                'details' => 'Shipment updated successfully.',
                'user_id' => auth()->id() ?? null
            ]);

            return $oceanImport;
        });
    }

    protected function syncHblRelations(OceanImportHbl $hbl, array $hblData, OceanImport $oceanImport)
    {
        // 1. Containers
        $syncData = [];
        if (isset($hblData['containers']) && is_array($hblData['containers'])) {
            foreach ($hblData['containers'] as $cData) {
                $containerNo = $cData['container_no'] ?? '';
                if ($containerNo) {
                    $container = $oceanImport->containers()->where('container_no', $containerNo)->first();
                    if (!$container) {
                        $container = $oceanImport->containers()->create([
                            'container_no' => $containerNo,
                            'pkg_qty' => $cData['pkg_qty'] ?? null,
                            'weight_kg' => $cData['weight_kg'] ?? null,
                            'measure_cbm' => $cData['measure_cbm'] ?? null,
                        ]);
                    }
                    if ($container) {
                        $syncData[$container->id] = [
                            'pkg_qty' => $cData['pkg_qty'] ?? null,
                            'pkg_unit' => $cData['pkg_unit'] ?? null,
                            'weight_kg' => $cData['weight_kg'] ?? null,
                            'weight_unit' => $cData['weight_unit'] ?? null,
                            'measure_cbm' => $cData['measure_cbm'] ?? null,
                            'measure_unit' => $cData['measure_unit'] ?? null,
                            'po_no' => $cData['po_no'] ?? null,
                        ];
                    }
                }
            }
        }
        $hbl->containers()->sync($syncData);

        // 2. Commodities
        $hbl->commodities()->delete();
        if (isset($hblData['commodities']) && is_array($hblData['commodities'])) {
            foreach ($hblData['commodities'] as $commData) {
                $hbl->commodities()->create($commData);
            }
        }

        // 3. Receipts
        $hbl->receipts()->delete();
        if (isset($hblData['receipts']) && is_array($hblData['receipts'])) {
            foreach ($hblData['receipts'] as $recData) {
                $hbl->receipts()->create($recData);
            }
        }
    }
}
