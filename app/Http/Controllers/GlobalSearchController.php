<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OceanImport;
use App\Models\OceanExport;
use App\Models\AirImport;
use App\Models\AirExport;
use App\Models\TruckShipment;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\TradePartner;
use App\Models\WarehouseReceipt;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query  = trim($request->get('q', ''));
        $module = trim($request->get('module', 'All'));

        if (strlen($query) < 1) {
            return response()->json(['records' => []]);
        }

        $records = [];
        $limit   = 8;

        // ─────────────────────────────────────────────
        // 1. Ocean Import  (file_no | mbl_no | hbl_no)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Ocean Import') {
            try {
                $results = OceanImport::with(['hbls', 'dmCustomer'])
                    ->where(function ($q) use ($query) {
                        $q->where('file_no', 'like', "%{$query}%")
                          ->orWhere('mbl_no', 'like', "%{$query}%")
                          ->orWhereHas('hbls', fn($hq) => $hq->where('hbl_no', 'like', "%{$query}%"));
                    })
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $hblNos = $item->hbls?->pluck('hbl_no')->filter()->implode(', ') ?: 'N/A';
                    $records[] = [
                        'category'    => 'Ocean Import',
                        'icon'        => 'fa-ship',
                        'badge_color' => '#2563eb',
                        'title'       => $item->file_no,
                        'subtitle'    => 'MBL: ' . ($item->mbl_no ?: 'N/A')
                                       . ' | HBL: ' . $hblNos
                                       . ($item->dmCustomer ? ' | ' . $item->dmCustomer->name : ''),
                        'url'         => url('/ocean-import/' . $item->id . '/edit'),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 2. Ocean Export  (file_no | mbl_no | hbl_no)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Ocean Export') {
            try {
                $results = OceanExport::with(['hbls', 'dmCustomer'])
                    ->where(function ($q) use ($query) {
                        $q->where('file_no', 'like', "%{$query}%")
                          ->orWhere('mbl_no', 'like', "%{$query}%")
                          ->orWhereHas('hbls', fn($hq) => $hq->where('hbl_no', 'like', "%{$query}%"));
                    })
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $hblNos = $item->hbls?->pluck('hbl_no')->filter()->implode(', ') ?: 'N/A';
                    $records[] = [
                        'category'    => 'Ocean Export',
                        'icon'        => 'fa-ship',
                        'badge_color' => '#059669',
                        'title'       => $item->file_no,
                        'subtitle'    => 'MBL: ' . ($item->mbl_no ?: 'N/A')
                                       . ' | HBL: ' . $hblNos
                                       . ($item->dmCustomer ? ' | ' . $item->dmCustomer->name : ''),
                        'url'         => url('/ocean-export/' . $item->id . '/edit'),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 3. Air Import  (file_no | mawb_no | hawb_no)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Air Import') {
            try {
                $results = AirImport::with(['hbls', 'dmCustomer'])
                    ->where(function ($q) use ($query) {
                        $q->where('file_no', 'like', "%{$query}%")
                          ->orWhere('mawb_no', 'like', "%{$query}%")
                          ->orWhereHas('hbls', fn($hq) => $hq->where('hawb_no', 'like', "%{$query}%"));
                    })
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $hawbNos = $item->hbls?->pluck('hawb_no')->filter()->implode(', ') ?: 'N/A';
                    $records[] = [
                        'category'    => 'Air Import',
                        'icon'        => 'fa-plane',
                        'badge_color' => '#7c3aed',
                        'title'       => $item->file_no,
                        'subtitle'    => 'MAWB: ' . ($item->mawb_no ?: 'N/A')
                                       . ' | HAWB: ' . $hawbNos
                                       . ($item->dmCustomer ? ' | ' . $item->dmCustomer->name : ''),
                        'url'         => url('/air-import/' . $item->id . '/edit'),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 4. Air Export  (file_no | mawb_no | hawb_no)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Air Export') {
            try {
                $results = AirExport::with(['hbls', 'dmCustomer'])
                    ->where(function ($q) use ($query) {
                        $q->where('file_no', 'like', "%{$query}%")
                          ->orWhere('mawb_no', 'like', "%{$query}%")
                          ->orWhereHas('hbls', fn($hq) => $hq->where('hawb_no', 'like', "%{$query}%"));
                    })
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $hawbNos = $item->hbls?->pluck('hawb_no')->filter()->implode(', ') ?: 'N/A';
                    $records[] = [
                        'category'    => 'Air Export',
                        'icon'        => 'fa-plane',
                        'badge_color' => '#d97706',
                        'title'       => $item->file_no,
                        'subtitle'    => 'MAWB: ' . ($item->mawb_no ?: 'N/A')
                                       . ' | HAWB: ' . $hawbNos
                                       . ($item->dmCustomer ? ' | ' . $item->dmCustomer->name : ''),
                        'url'         => url('/air-export/' . $item->id . '/edit'),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 5. Truck Shipment  (file_no | mbl_no)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Trucking') {
            try {
                $results = TruckShipment::with('customer')
                    ->where(function ($q) use ($query) {
                        $q->where('file_no', 'like', "%{$query}%")
                          ->orWhere('mbl_no', 'like', "%{$query}%");
                    })
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $records[] = [
                        'category'    => 'Trucking',
                        'icon'        => 'fa-truck',
                        'badge_color' => '#dc2626',
                        'title'       => $item->file_no,
                        'subtitle'    => 'MBL: ' . ($item->mbl_no ?: 'N/A')
                                       . ($item->customer ? ' | ' . $item->customer->name : ''),
                        'url'         => url('/truck/' . $item->id . '/edit'),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 6. Invoices  (invoice_no | file_no | bill_to_name)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Accounting') {
            try {
                $results = Invoice::where(function ($q) use ($query) {
                        $q->where('invoice_no', 'like', "%{$query}%")
                          ->orWhere('file_no', 'like', "%{$query}%")
                          ->orWhere('bill_to_name', 'like', "%{$query}%");
                    })
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $records[] = [
                        'category'    => 'Invoice',
                        'icon'        => 'fa-file-text-o',
                        'badge_color' => '#0284c7',
                        'title'       => $item->invoice_no ?: ('Invoice #' . $item->id),
                        'subtitle'    => 'File: ' . ($item->file_no ?: 'N/A')
                                       . ' | Bill To: ' . ($item->bill_to_name ?: 'N/A')
                                       . ' | $' . number_format($item->total_amount ?? 0, 2),
                        'url'         => url('/accounting/invoice/' . $item->id),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 7. Quotations  (quote_no)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Sales') {
            try {
                $results = Quotation::with('customer')
                    ->where('quote_no', 'like', "%{$query}%")
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $records[] = [
                        'category'    => 'Quotation',
                        'icon'        => 'fa-file-text',
                        'badge_color' => '#4f46e5',
                        'title'       => $item->quote_no,
                        'subtitle'    => 'Customer: ' . ($item->customer?->name ?? 'N/A')
                                       . ' | Module: ' . ($item->module ?: 'N/A'),
                        'url'         => url('/sales/quotation/' . $item->id . '/edit'),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 8. Trade Partners  (name | code | email)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Trade Partners') {
            try {
                $results = TradePartner::where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('code', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $records[] = [
                        'category'    => 'Trade Partner',
                        'icon'        => 'fa-building',
                        'badge_color' => '#0891b2',
                        'title'       => $item->name,
                        'subtitle'    => 'Code: ' . ($item->code ?: 'N/A')
                                       . ' | Type: ' . ($item->type ?: 'N/A'),
                        'url'         => url('/trade-partner/' . $item->id . '/edit'),
                    ];
                }
            } catch (\Exception $e) {}
        }

        // ─────────────────────────────────────────────
        // 9. Warehouse Receipts  (receipt_no)
        // ─────────────────────────────────────────────
        if ($module === 'All' || $module === 'Warehouse') {
            try {
                $results = WarehouseReceipt::with('customer')
                    ->where('receipt_no', 'like', "%{$query}%")
                    ->limit($limit)->get();

                foreach ($results as $item) {
                    $records[] = [
                        'category'    => 'Warehouse Receipt',
                        'icon'        => 'fa-cubes',
                        'badge_color' => '#ca8a04',
                        'title'       => $item->receipt_no ?: ('WR #' . $item->id),
                        'subtitle'    => 'Customer: ' . ($item->customer?->name ?? 'N/A')
                                       . ' | Status: ' . ($item->status ?: 'N/A'),
                        'url'         => url('/warehouse/receipts/' . $item->id),
                    ];
                }
            } catch (\Exception $e) {}
        }

        return response()->json([
            'records' => array_slice($records, 0, 20),
        ]);
    }
}
