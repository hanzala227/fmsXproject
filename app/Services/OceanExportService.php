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

            if (isset($data['containers'])) {
                foreach ($data['containers'] as $containerData) {
                    $containerData = array_merge($this->getContainerDefaults(), $containerData);
                    $oceanExport->containers()->create($containerData);
                }
            }

            if (isset($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    $hblData = array_merge($this->getHblDefaults(), $hblData);

                    foreach ($hblData as $hk => $hv) {
                        if (is_string($hv) && $hv === '' && str_ends_with($hk, '_id')) {
                            $hblData[$hk] = null;
                        }
                    }
                    $newHbl = $oceanExport->hbls()->create($hblData);
                    $this->syncHblRelations($newHbl, $hblData, $oceanExport);
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
            $user = auth()->user();
            $userName = $user ? $user->name : 'System';

            // Snapshot old states
            $oldMblAttributes = $oceanExport->getAttributes();
            $oldContainerCount = $oceanExport->containers()->count();
            $oldHblCount = $oceanExport->hbls()->count();
            $oldChargeCount = $oceanExport->charges()->count();

            // Perform MBL update
            $oceanExport->update($data);

            // Compute changed MBL attributes
            $changes = [];
            $friendlyFieldNames = [
                'mbl_no' => 'MBL No.',
                'booking_no' => 'Booking No.',
                'post_date' => 'Post Date',
                'vessel_id' => 'Vessel',
                'voyage' => 'Voyage',
                'pol_id' => 'Port of Loading',
                'pod_id' => 'Port of Discharge',
                'etd' => 'ETD',
                'eta' => 'ETA',
                'ship_mode' => 'Ship Mode',
                'carrier_id' => 'Carrier',
                'oversea_agent_id' => 'Oversea Agent',
                'forwarding_agent_id' => 'Forwarding Agent',
                'sub_bl_no' => 'Sub B/L No.',
                'internal_remark' => 'Internal Remark',
                'is_hold' => 'Hold Status',
                'is_blocked' => 'Blocked Status',
                'obl_type' => 'OBL Type',
                'bl_type' => 'BL Type',
                'cargo_type' => 'Cargo Type'
            ];

            foreach ($oceanExport->getChanges() as $field => $newVal) {
                if (in_array($field, ['updated_at', 'created_at', 'id'])) continue;
                $label = $friendlyFieldNames[$field] ?? ucwords(str_replace('_', ' ', $field));
                $oldVal = $oldMblAttributes[$field] ?? 'empty';
                $oldValStr = is_null($oldVal) || $oldVal === '' ? '(empty)' : (string)$oldVal;
                $newValStr = is_null($newVal) || $newVal === '' ? '(empty)' : (string)$newVal;
                if ($oldValStr !== $newValStr) {
                    $changes[] = "$label changed from \"$oldValStr\" to \"$newValStr\"";
                }
            }

            // Sync containers
            $submittedContainerIds = [];
            $containersAdded = 0;
            $containersUpdated = 0;
            if (isset($data['containers'])) {
                foreach ($data['containers'] as $containerData) {
                    if (isset($containerData['id'])) {
                        $container = OceanExportContainer::find($containerData['id']);
                        if ($container && $container->ocean_export_id === $oceanExport->id) {
                            $container->update($containerData);
                            $submittedContainerIds[] = $container->id;
                            $containersUpdated++;
                        }
                    } else {
                        $containerData = array_merge($this->getContainerDefaults(), $containerData);
                        $newContainer = $oceanExport->containers()->create($containerData);
                        $submittedContainerIds[] = $newContainer->id;
                        $containersAdded++;
                    }
                }
            }
            $containersDeleted = $oceanExport->containers()->whereNotIn('id', $submittedContainerIds)->delete();
            if ($containersAdded > 0) $changes[] = "$containersAdded container(s) added";
            if ($containersDeleted > 0) $changes[] = "$containersDeleted container(s) removed";

            // Sync HBLs
            $submittedHblIds = [];
            $hblsAdded = 0;
            $hblsUpdated = 0;
            if (isset($data['hbls'])) {
                foreach ($data['hbls'] as $hblData) {
                    if (isset($hblData['id'])) {
                        $hbl = OceanExportHbl::find($hblData['id']);
                        if ($hbl && $hbl->ocean_export_id === $oceanExport->id) {
                            $hbl->update($hblData);
                            $submittedHblIds[] = $hbl->id;
                            $hblsUpdated++;
                        }
                    } else {
                        $hblData = array_merge($this->getHblDefaults(), $hblData);
                        $hbl = $oceanExport->hbls()->create($hblData);
                        $submittedHblIds[] = $hbl->id;
                        $hblsAdded++;
                    }
                    
                    if (isset($hbl)) {
                        $this->syncHblRelations($hbl, $hblData, $oceanExport);
                    }
                }
            }
            $hblsDeleted = $oceanExport->hbls()->whereNotIn('id', $submittedHblIds)->delete();
            if ($hblsAdded > 0) $changes[] = "$hblsAdded House B/L added";
            if ($hblsDeleted > 0) $changes[] = "$hblsDeleted House B/L removed";

            // Sync charges
            if (isset($data['charges'])) {
                $oceanExport->charges()->delete();
                $chargeCount = 0;
                foreach ($data['charges'] as $chargeData) {
                    $chargeData = array_merge($this->getChargeDefaults(), $chargeData);
                    $oceanExport->charges()->create($this->mapChargeData($chargeData));
                    $chargeCount++;
                }
                if ($chargeCount !== $oldChargeCount) {
                    $changes[] = "Charges updated ($chargeCount total charges)";
                }
            }

            // Create descriptive detailed log entry
            $details = count($changes) > 0
                ? implode('; ', $changes) . '.'
                : 'Shipment saved without field changes.';

            $oceanExport->statusLogs()->create([
                'user_id' => auth()->id(),
                'status_code' => 'UPDATED',
                'status_name' => 'UPDATED',
                'details' => $details,
            ]);

            return $oceanExport;
        });
    }

    protected function syncHblRelations(OceanExportHbl $hbl, array $hblData, OceanExport $oceanExport)
    {
        // 1. Containers
        $syncData = [];
        if (isset($hblData['containers']) && is_array($hblData['containers'])) {
            foreach ($hblData['containers'] as $cData) {
                $containerNo = $cData['container_no'] ?? '';
                if ($containerNo) {
                    $container = $oceanExport->containers()->where('container_no', $containerNo)->first();
                    if (!$container) {
                        $container = $oceanExport->containers()->create([
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
