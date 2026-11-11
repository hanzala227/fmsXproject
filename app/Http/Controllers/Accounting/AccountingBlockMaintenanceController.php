<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\OceanImport;
use App\Models\OceanExport;
use App\Models\AirImport;
use App\Models\AirExport;
use App\Models\TruckShipment;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\AccountingJournal;
use App\Models\AccountingBlockHistory;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingBlockMaintenanceController extends Controller
{
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('accounting.accounting-block-maintenance', compact('offices'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'record_type'      => 'required|in:shipment,ar_ap_dc,deposit_payment,general_journal',
            'office_id'        => 'nullable|exists:offices,id',
            'is_blocked'       => 'nullable|in:yes,no',
            'post_date_from'   => 'nullable|date',
            'post_date_to'     => 'nullable|date',
            'file_no'          => 'nullable|string|max:255',
            'bl_no'            => 'nullable|string|max:255',
            'shipment_types'   => 'nullable|array',
            'shipment_types.*' => 'string|in:ocean_import,ocean_export,air_import,air_export,truck,misc,warehouse',
        ]);

        $recordType = $request->record_type;

        if ($recordType === 'shipment') {
            $results = $this->searchShipments($request);
        } elseif ($recordType === 'ar_ap_dc') {
            $results = $this->searchArApDc($request);
        } elseif ($recordType === 'deposit_payment') {
            $results = $this->searchDepositPayment($request);
        } elseif ($recordType === 'general_journal') {
            $results = $this->searchGeneralJournal($request);
        } else {
            $results = collect();
        }

        return response()->json([
            'success' => true,
            'data'    => $results->values(),
            'total'   => $results->count(),
        ]);
    }

    private function searchShipments(Request $request)
    {
        $types = $request->shipment_types ?? ['ocean_import', 'ocean_export', 'air_import', 'air_export', 'truck', 'misc', 'warehouse'];
        $results = collect();

        if (in_array('ocean_import', $types)) {
            $q = OceanImport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'portOfLoading', 'portOfDischarge', 'vessel'])
                ->select('id', 'file_no', 'mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'pol_id', 'pod_id', 'vessel_id', 'is_blocked');
            $results = $results->merge($this->applyShipmentFilters($q, $request, 'ocean_imports', 'OCEAN IMPORT'));
        }

        if (in_array('ocean_export', $types)) {
            $q = OceanExport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'portOfLoading', 'portOfDischarge', 'vessel'])
                ->select('id', 'file_no', 'mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'pol_id', 'pod_id', 'vessel_id', 'is_blocked');
            $results = $results->merge($this->applyShipmentFilters($q, $request, 'ocean_exports', 'OCEAN EXPORT'));
        }

        if (in_array('air_import', $types)) {
            $q = AirImport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'depPort', 'dstPort'])
                ->select('id', 'file_no', 'mawb_no as mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'dep_port_id as pol_id', 'dst_port_id as pod_id', 'is_blocked');
            $results = $results->merge($this->applyShipmentFilters($q, $request, 'air_imports', 'AIR IMPORT'));
        }

        if (in_array('air_export', $types)) {
            $q = AirExport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'depPort', 'dstPort'])
                ->select('id', 'file_no', 'mawb_no as mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'dep_port_id as pol_id', 'dst_port_id as pod_id', 'is_blocked');
            $results = $results->merge($this->applyShipmentFilters($q, $request, 'air_exports', 'AIR EXPORT'));
        }

        if (in_array('truck', $types)) {
            $q = TruckShipment::with(['office', 'dmCustomer', 'pol', 'pod'])
                ->select('id', 'file_no', 'mbl_no', 'post_date', 'office_id', 'customer_id', 'pol_id', 'pod_id', 'is_blocked');
            $results = $results->merge($this->applyShipmentFilters($q, $request, 'truck_shipments', 'TRUCK'));
        }

        return $results;
    }

    private function applyShipmentFilters($query, Request $request, string $table, string $typeLabel)
    {
        $companyId = auth()->user()->company_id ?? null;
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->is_blocked === 'yes') {
            $query->where('is_blocked', true);
        } elseif ($request->is_blocked === 'no') {
            $query->where(function($q) {
                $q->where('is_blocked', false)->orWhereNull('is_blocked');
            });
        }

        if ($request->post_date_from) {
            $query->where('post_date', '>=', $request->post_date_from);
        }

        if ($request->post_date_to) {
            $query->where('post_date', '<=', $request->post_date_to);
        }

        if ($request->file_no) {
            $query->where('file_no', 'like', '%' . $request->file_no . '%');
        }

        if ($request->bl_no) {
            $query->where(function ($q) use ($request) {
                $q->where('mbl_no', 'like', '%' . $request->bl_no . '%');
                if (\Schema::hasColumn($q->getModel()->getTable(), 'mawb_no')) {
                    $q->orWhere('mawb_no', 'like', '%' . $request->bl_no . '%');
                }
            });
        }

        return $query->get()->map(function ($r) use ($table, $typeLabel) {
            return [
                'id'            => $r->id,
                'type'          => $typeLabel,
                'office'        => $r->office?->code ?? '',
                'post_date'     => $r->post_date ? \Carbon\Carbon::parse($r->post_date)->format('m-d-Y') : '',
                'file_no'       => $r->file_no ?? '',
                'bl_no'         => $r->mbl_no ?? '',
                'oversea_agent' => $r->overseaAgent?->name ?? '',
                'carrier'       => $r->carrier?->name ?? '',
                'customer'      => $r->dmCustomer?->name ?? ($r->customer?->name ?? ''),
                'pol'           => $r->portOfLoading?->name ?? ($r->depPort?->name ?? ($r->pol?->name ?? '')),
                'pod'           => $r->portOfDischarge?->name ?? ($r->dstPort?->name ?? ($r->pod?->name ?? '')),
                'vessel_flt_no' => $r->vessel?->name ?? ($r->voyage ?? ''),
                'is_blocked'    => (bool)($r->is_blocked ?? false),
                'model_type'    => $typeLabel,
                'record_table'  => $table,
            ];
        });
    }

    private function searchArApDc(Request $request)
    {
        $companyId = auth()->user()->company_id ?? null;
        $query = Invoice::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->with(['billTo', 'office'])
            ->select('id', 'invoice_no', 'type', 'document_type', 'invoice_date as post_date', 'office_id', 'bill_to_id', 'is_blocked');

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->is_blocked === 'yes') {
            $query->where('is_blocked', true);
        } elseif ($request->is_blocked === 'no') {
            $query->where(function($q) {
                $q->where('is_blocked', false)->orWhereNull('is_blocked');
            });
        }

        if ($request->post_date_from) {
            $query->where('invoice_date', '>=', $request->post_date_from);
        }

        if ($request->post_date_to) {
            $query->where('invoice_date', '<=', $request->post_date_to);
        }

        if ($request->file_no) {
            $query->where('invoice_no', 'like', '%' . $request->file_no . '%');
        }

        return $query->get()->map(function ($r) {
            $typeStr = $r->type === 'AR' ? 'A/R' : ($r->type === 'AP' ? 'A/P' : 'DC');
            if ($r->document_type === 'DN') $typeStr = 'DEBIT NOTE';
            if ($r->document_type === 'CN') $typeStr = 'CREDIT NOTE';

            return [
                'id'            => $r->id,
                'type'          => $typeStr,
                'office'        => $r->office?->code ?? '',
                'post_date'     => $r->post_date ? \Carbon\Carbon::parse($r->post_date)->format('m-d-Y') : '',
                'file_no'       => $r->invoice_no ?? '',
                'bl_no'         => '',
                'oversea_agent' => '',
                'carrier'       => '',
                'customer'      => $r->billTo?->name ?? '',
                'pol'           => '',
                'pod'           => '',
                'vessel_flt_no' => '',
                'is_blocked'    => (bool)($r->is_blocked ?? false),
                'model_type'    => 'invoice',
                'record_table'  => 'invoices',
            ];
        });
    }

    private function searchDepositPayment(Request $request)
    {
        $companyId = auth()->user()->company_id ?? null;
        $query = AccountingPayment::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->with(['tradePartner', 'office'])
            ->select('id', 'payment_no', 'type', 'payment_date as post_date', 'office_id', 'trade_partner_id', 'check_no', 'is_blocked');

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->is_blocked === 'yes') {
            $query->where('is_blocked', true);
        } elseif ($request->is_blocked === 'no') {
            $query->where(function($q) {
                $q->where('is_blocked', false)->orWhereNull('is_blocked');
            });
        }

        if ($request->post_date_from) {
            $query->where('payment_date', '>=', $request->post_date_from);
        }

        if ($request->post_date_to) {
            $query->where('payment_date', '<=', $request->post_date_to);
        }

        if ($request->file_no) {
            $query->where(function($q) use ($request) {
                $q->where('payment_no', 'like', '%' . $request->file_no . '%')
                  ->orWhere('check_no', 'like', '%' . $request->file_no . '%');
            });
        }

        return $query->get()->map(function ($r) {
            return [
                'id'            => $r->id,
                'type'          => $r->type === 'RECEIVED' ? 'RECEIPT' : 'PAYMENT',
                'office'        => $r->office?->code ?? '',
                'post_date'     => $r->post_date ? \Carbon\Carbon::parse($r->post_date)->format('m-d-Y') : '',
                'file_no'       => $r->payment_no ?? '',
                'bl_no'         => $r->check_no ?? '',
                'oversea_agent' => '',
                'carrier'       => '',
                'customer'      => $r->tradePartner?->name ?? '',
                'pol'           => '',
                'pod'           => '',
                'vessel_flt_no' => '',
                'is_blocked'    => (bool)($r->is_blocked ?? false),
                'model_type'    => 'payment',
                'record_table'  => 'payments',
            ];
        });
    }

    private function searchGeneralJournal(Request $request)
    {
        $companyId = auth()->user()->company_id ?? null;
        $query = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->with(['office', 'creator'])
            ->select('id', 'entry_no', 'entry_date as post_date', 'office_id', 'description', 'status', 'created_by', 'is_blocked');

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->is_blocked === 'yes') {
            $query->where('is_blocked', true);
        } elseif ($request->is_blocked === 'no') {
            $query->where(function($q) {
                $q->where('is_blocked', false)->orWhereNull('is_blocked');
            });
        }

        if ($request->post_date_from) {
            $query->where('entry_date', '>=', $request->post_date_from);
        }

        if ($request->post_date_to) {
            $query->where('entry_date', '<=', $request->post_date_to);
        }

        if ($request->file_no) {
            $query->where('entry_no', 'like', '%' . $request->file_no . '%');
        }

        return $query->get()->map(function ($r) {
            return [
                'id'            => $r->id,
                'type'          => 'GENERAL JOURNAL',
                'office'        => $r->office?->code ?? '',
                'post_date'     => $r->post_date ? \Carbon\Carbon::parse($r->post_date)->format('m-d-Y') : '',
                'file_no'       => $r->entry_no ?? '',
                'bl_no'         => '',
                'oversea_agent' => '',
                'carrier'       => '',
                'customer'      => '',
                'pol'           => '',
                'pod'           => '',
                'vessel_flt_no' => '',
                'is_blocked'    => (bool)($r->is_blocked ?? false),
                'model_type'    => 'general_journal',
                'record_table'  => 'journal_entries',
                'description'   => $r->description ?? '',
            ];
        });
    }

    public function apply(Request $request)
    {
        // Access Control: Only administrators can modify block status
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Administrator privileges required to change block status.'
            ], 403);
        }

        $request->validate([
            'ids'          => 'required|array',
            'ids.*'        => 'required|integer',
            'action'       => 'required|in:block,unblock',
            'record_table' => 'required|string',
        ]);

        $table   = $request->record_table;
        $blocked = ($request->action === 'block');
        $ids     = $request->ids;

        $allowedTables = [
            'ocean_imports', 'ocean_exports', 'air_imports', 'air_exports', 'truck_shipments',
            'invoices', 'payments', 'journal_entries'
        ];

        if (!in_array($table, $allowedTables)) {
            return response()->json(['success' => false, 'message' => 'Invalid record type.'], 422);
        }

        $companyId = auth()->user()->company_id ?? null;
        if ($companyId) {
            if ($table === 'journal_entries') {
                $validIds = DB::table('journal_entries')
                    ->join('users', 'journal_entries.created_by', '=', 'users.id')
                    ->where('users.company_id', $companyId)
                    ->whereIn('journal_entries.id', $ids)
                    ->pluck('journal_entries.id')
                    ->toArray();
            } else {
                $validIds = DB::table($table)
                    ->where('company_id', $companyId)
                    ->whereIn('id', $ids)
                    ->pluck('id')
                    ->toArray();
            }
            $ids = $validIds;
        }

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No matching records found for this company.'], 404);
        }

        $affected = DB::table($table)->whereIn('id', $ids)->update(['is_blocked' => $blocked]);

        $typeMap = [
            'ocean_imports'   => 'OCEAN IMPORT',
            'ocean_exports'   => 'OCEAN EXPORT',
            'air_imports'     => 'AIR IMPORT',
            'air_exports'     => 'AIR EXPORT',
            'truck_shipments' => 'TRUCK',
            'invoices'        => 'INVOICE',
            'payments'        => 'PAYMENT',
            'journal_entries' => 'GENERAL JOURNAL',
        ];

        foreach ($ids as $id) {
            $record = DB::table($table)->where('id', $id)->first();
            $refNo = $record->file_no ?? $record->invoice_no ?? $record->payment_no ?? $record->entry_no ?? ('#' . $id);
            $postDate = $record->post_date ?? $record->invoice_date ?? $record->payment_date ?? $record->entry_date ?? now()->toDateString();

            AccountingBlockHistory::create([
                'program'      => 'Block Maintenance',
                'is_blocked'   => $blocked,
                'block_type'   => $typeMap[$table] ?? strtoupper($table),
                'ref_no'       => $refNo,
                'block_date'   => $postDate,
                'office_id'    => $record->office_id ?? null,
                'execute_by'   => auth()->id(),
                'executed_at'  => now(),
                'record_id'    => $id,
                'record_table' => $table,
            ]);
        }

        $label = $blocked ? 'blocked' : 'unblocked';

        return response()->json([
            'success'  => true,
            'message'  => "{$affected} record(s) {$label} successfully.",
            'affected' => $affected,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $searchResponse = $this->search($request);
        $data = $searchResponse->getData(true);
        $rows = $data['data'] ?? [];

        $filename = 'accounting-block-maintenance-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Type', 'Office', 'Post Date', 'File No. / Ref No.',
                'BL No.', 'Oversea Agent', 'Carrier', 'Customer',
                'POL', 'POD', 'Vessel / Flight', 'Blocked'
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['type'] ?? '',
                    $row['office'] ?? '',
                    $row['post_date'] ?? '',
                    $row['file_no'] ?? '',
                    $row['bl_no'] ?? '',
                    $row['oversea_agent'] ?? '',
                    $row['carrier'] ?? '',
                    $row['customer'] ?? '',
                    $row['pol'] ?? '',
                    $row['pod'] ?? '',
                    $row['vessel_flt_no'] ?? '',
                    ($row['is_blocked'] ?? false) ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
