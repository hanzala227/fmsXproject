<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\OceanImport;
use App\Models\OceanExport;
use App\Models\AirImport;
use App\Models\AirExport;
use App\Models\TruckShipment;
use App\Models\Invoice;
use App\Models\Office;
use Illuminate\Http\Request;

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
            'record_type' => 'required|in:shipment,ar_ap_dc,deposit_payment,general_journal',
            'office_id' => 'nullable|exists:offices,id',
            'is_blocked' => 'nullable|in:yes,no',
            'post_date_from' => 'nullable|date',
            'post_date_to' => 'nullable|date',
            'file_no' => 'nullable|string|max:255',
            'bl_no' => 'nullable|string|max:255',
            'shipment_types' => 'nullable|array',
            'shipment_types.*' => 'string|in:ocean_import,ocean_export,air_import,air_export,truck,misc,warehouse',
        ]);

        $results = collect();
        $recordType = $request->record_type;

        if ($recordType === 'shipment') {
            $results = $this->searchShipments($request);
        } elseif ($recordType === 'ar_ap_dc') {
            $results = $this->searchArApDc($request);
        } elseif ($recordType === 'deposit_payment') {
            $results = $this->searchDepositPayment($request);
        } elseif ($recordType === 'general_journal') {
            $results = $this->searchGeneralJournal($request);
        }

        return response()->json([
            'success' => true,
            'data' => $results->values(),
            'total' => $results->count(),
        ]);
    }

    private function searchShipments(Request $request)
    {
        $types = $request->shipment_types ?? ['ocean_import', 'ocean_export', 'air_import', 'air_export', 'truck', 'misc', 'warehouse'];
        $results = collect();

        if (in_array('ocean_import', $types)) {
            $query = OceanImport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'portOfLoading', 'portOfDischarge', 'vessel'])
                ->select('id', 'file_no', 'mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'pol_id', 'pod_id', 'vessel_id', 'is_blocked');

            $this->applyShipmentFilters($query, $request, 'mbl_no');
            $items = $query->get()->map(fn($r) => $this->formatShipmentResult($r, 'OCEAN IMPORT'));
            $results = $results->concat($items);
        }

        if (in_array('ocean_export', $types)) {
            $query = OceanExport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'portOfLoading', 'portOfDischarge', 'vessel'])
                ->select('id', 'file_no', 'mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'pol_id', 'pod_id', 'vessel_id', 'is_blocked');

            $this->applyShipmentFilters($query, $request, 'mbl_no');
            $items = $query->get()->map(fn($r) => $this->formatShipmentResult($r, 'OCEAN EXPORT'));
            $results = $results->concat($items);
        }

        if (in_array('air_import', $types)) {
            $query = AirImport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'depPort', 'dstPort'])
                ->select('id', 'file_no', 'mawb_no as mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'dep_port_id as pol_id', 'dst_port_id as pod_id', 'is_blocked');

            $this->applyShipmentFilters($query, $request, 'mawb_no');
            $items = $query->get()->map(fn($r) => $this->formatShipmentResult($r, 'AIR IMPORT'));
            $results = $results->concat($items);
        }

        if (in_array('air_export', $types)) {
            $query = AirExport::with(['office', 'overseaAgent', 'carrier', 'dmCustomer', 'depPort', 'dstPort'])
                ->select('id', 'file_no', 'mawb_no as mbl_no', 'post_date', 'office_id', 'oversea_agent_id', 'carrier_id', 'dm_customer_id', 'dep_port_id as pol_id', 'dst_port_id as pod_id', 'is_blocked');

            $this->applyShipmentFilters($query, $request, 'mawb_no');
            $items = $query->get()->map(fn($r) => $this->formatShipmentResult($r, 'AIR EXPORT'));
            $results = $results->concat($items);
        }

        if (in_array('truck', $types)) {
            $query = TruckShipment::with(['office', 'customer', 'pol', 'pod'])
                ->select('id', 'file_no', 'mbl_no', 'post_date', 'office_id', 'customer_id', 'pol_id', 'pod_id', 'is_blocked');

            $this->applyShipmentFilters($query, $request, 'mbl_no');
            $items = $query->get()->map(fn($r) => $this->formatTruckResult($r));
            $results = $results->concat($items);
        }

        return $results->sortBy('post_date')->values();
    }

    private function applyShipmentFilters($query, Request $request, $blColumn)
    {
        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->is_blocked === 'yes') {
            $query->where('is_blocked', true);
        } elseif ($request->is_blocked === 'no') {
            $query->where('is_blocked', false);
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

        if ($request->bl_no && $blColumn) {
            $query->where($blColumn, 'like', '%' . $request->bl_no . '%');
        }
    }

    private function formatShipmentResult($record, $type)
    {
        return [
            'id' => $record->id,
            'type' => $type,
            'office' => $record->office?->code ?? '',
            'post_date' => $record->post_date?->format('m-d-Y') ?? '',
            'file_no' => $record->file_no ?? '',
            'bl_no' => $record->mbl_no ?? '',
            'oversea_agent' => $record->overseaAgent?->name ?? '',
            'carrier' => $record->carrier?->name ?? '',
            'customer' => $record->dmCustomer?->name ?? '',
            'pol' => $record->portOfLoading?->name ?? $record->depPort?->name ?? '',
            'pod' => $record->portOfDischarge?->name ?? $record->dstPort?->name ?? '',
            'vessel_flt_no' => $record->vessel?->name ?? '',
            'is_blocked' => $record->is_blocked ?? false,
            'model_type' => $type === 'OCEAN IMPORT' ? 'ocean_import' : ($type === 'OCEAN EXPORT' ? 'ocean_export' : ($type === 'AIR IMPORT' ? 'air_import' : 'air_export')),
            'record_table' => $type === 'OCEAN IMPORT' ? 'ocean_imports' : ($type === 'OCEAN EXPORT' ? 'ocean_exports' : ($type === 'AIR IMPORT' ? 'air_imports' : 'air_exports')),
        ];
    }

    private function formatTruckResult($record)
    {
        return [
            'id' => $record->id,
            'type' => 'TRUCK',
            'office' => $record->office?->code ?? '',
            'post_date' => $record->post_date?->format('m-d-Y') ?? '',
            'file_no' => $record->file_no ?? '',
            'bl_no' => $record->mbl_no ?? '',
            'oversea_agent' => '',
            'carrier' => '',
            'customer' => $record->customer?->name ?? '',
            'pol' => $record->pol?->name ?? '',
            'pod' => $record->pod?->name ?? '',
            'vessel_flt_no' => $record->vessel_flight_no ?? '',
            'is_blocked' => $record->is_blocked ?? false,
            'model_type' => 'truck',
            'record_table' => 'truck_shipments',
        ];
    }

    private function searchArApDc(Request $request)
    {
        $query = Invoice::with(['office', 'billTo'])
            ->select('id', 'invoice_no', 'invoice_date as post_date', 'office_id', 'bill_to_id', 'total_amount', 'balance_amount', 'status', 'type');

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
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

        $items = $query->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'type' => $r->type === 'AR' ? 'A/R' : ($r->type === 'AP' ? 'A/P' : 'DC'),
                'office' => $r->office?->code ?? '',
                'post_date' => $r->post_date?->format('m-d-Y') ?? '',
                'file_no' => $r->invoice_no ?? '',
                'bl_no' => '',
                'oversea_agent' => '',
                'carrier' => '',
                'customer' => $r->billTo?->name ?? '',
                'pol' => '',
                'pod' => '',
                'vessel_flt_no' => '',
                'is_blocked' => false,
                'model_type' => 'invoice',
                'record_table' => 'invoices',
            ];
        });

        return $items;
    }

    private function searchDepositPayment(Request $request)
    {
        return collect();
    }

    private function searchGeneralJournal(Request $request)
    {
        $query = \App\Models\AccountingJournal::with(['office', 'creator'])
            ->select('id', 'entry_no', 'entry_date as post_date', 'office_id', 'description', 'status', 'created_by');

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
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

        $items = $query->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'type' => 'GENERAL JOURNAL',
                'office' => $r->office?->code ?? '',
                'post_date' => $r->post_date?->format('m-d-Y') ?? '',
                'file_no' => $r->entry_no ?? '',
                'bl_no' => '',
                'oversea_agent' => '',
                'carrier' => '',
                'customer' => '',
                'pol' => '',
                'pod' => '',
                'vessel_flt_no' => '',
                'is_blocked' => false,
                'model_type' => 'general_journal',
                'record_table' => 'journal_entries',
                'description' => $r->description ?? '',
            ];
        });

        return $items;
    }

    public function apply(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'action' => 'required|in:block,unblock',
            'record_table' => 'required|string',
        ]);

        $table = $request->record_table;
        $blocked = $request->action === 'block';
        $ids = $request->ids;

        $allowedTables = ['ocean_imports', 'ocean_exports', 'air_imports', 'air_exports', 'truck_shipments'];

        if (!in_array($table, $allowedTables)) {
            return response()->json(['success' => false, 'message' => 'Invalid record type.'], 422);
        }

        $affected = \DB::table($table)->whereIn('id', $ids)->update(['is_blocked' => $blocked]);

        $typeMap = [
            'ocean_imports' => 'OCEAN IMPORT',
            'ocean_exports' => 'OCEAN EXPORT',
            'air_imports' => 'AIR IMPORT',
            'air_exports' => 'AIR EXPORT',
            'truck_shipments' => 'TRUCK',
        ];

        foreach ($ids as $id) {
            $record = \DB::table($table)->where('id', $id)->first();
            AccountingBlockHistory::create([
                'program' => 'Block Maintenance',
                'is_blocked' => $blocked,
                'block_type' => $typeMap[$table] ?? strtoupper($table),
                'ref_no' => $record->file_no ?? null,
                'block_date' => $record->post_date ?? now()->toDateString(),
                'office_id' => $record->office_id ?? null,
                'execute_by' => auth()->id(),
                'executed_at' => now(),
                'record_id' => $id,
                'record_table' => $table,
            ]);
        }

        $label = $blocked ? 'blocked' : 'unblocked';

        return response()->json([
            'success' => true,
            'message' => "{$affected} record(s) {$label} successfully.",
            'affected' => $affected,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $response = $this->search($request);
        $data = json_decode($response->getContent(), true);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="accounting_block_maintenance.csv"',
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Type', 'Office', 'Post Date', 'Booking no', 'BL No.', 'Oversea Agent', 'Carrier', 'Customer', 'POL', 'POD', 'Vessel/FLT No.', 'Blocked']);
            foreach ($data['data'] ?? [] as $row) {
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
        };

        return response()->stream($callback, 200, $headers);
    }
}
