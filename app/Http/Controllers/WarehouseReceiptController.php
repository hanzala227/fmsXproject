<?php

namespace App\Http\Controllers;

use App\Models\WarehouseReceipt;
use App\Models\WarehouseReceiptItem;
use App\Models\Office;
use App\Models\TradePartner;
use App\Models\User;
use App\Models\Document;
use App\Http\Requests\StoreWarehouseReceiptRequest;
use App\Http\Requests\UpdateWarehouseReceiptRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WarehouseReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseReceipt::with(['warehouse', 'customer', 'shipper', 'consignee', 'office', 'operator']);

        $this->applyFiltersToQuery($query, $request);

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['receipt_no', 'receipt_date', 'tracking_no', 'cargo_type', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $receipts = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        return view('warehouse.receipts.list', compact('receipts'));
    }

    private function applyFiltersToQuery($query, Request $request)
    {
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('tracking_no', 'like', "%{$search}%")
                  ->orWhere('commodity', 'like', "%{$search}%")
                  ->orWhere('location_code', 'like', "%{$search}%")
                  ->orWhere('carrier_name', 'like', "%{$search}%")
                  ->orWhere('po_no', 'like', "%{$search}%")
                  ->orWhere('check_no', 'like', "%{$search}%")
                  ->orWhereHas('shipper', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('consignee', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('filter_receipt_no')) {
            $query->where('receipt_no', 'like', "%{$request->filter_receipt_no}%");
        }
        if ($request->filled('filter_date')) {
            $query->where('receipt_date', 'like', "%{$request->filter_date}%");
        }
        if ($request->filled('filter_tracking')) {
            $query->where('tracking_no', 'like', "%{$request->filter_tracking}%");
        }
        if ($request->filled('filter_shipper')) {
            $query->whereHas('shipper', fn($q) => $q->where('name', 'like', "%{$request->filter_shipper}%"));
        }
        if ($request->filled('filter_consignee')) {
            $query->whereHas('consignee', fn($q) => $q->where('name', 'like', "%{$request->filter_consignee}%"));
        }
        if ($request->filled('filter_customer')) {
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$request->filter_customer}%"));
        }
        if ($request->filled('filter_cargo_type')) {
            $query->where('cargo_type', 'like', "%{$request->filter_cargo_type}%");
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('office', fn($q) => $q->where('code', 'like', "%{$request->filter_office}%"));
        }

        return $query;
    }

    public function create()
    {
        $warehouses = TradePartner::whereIn('type', ['WH', 'CF', 'CY', 'WAREHOUSE', 'CFS'])
            ->orWhereJsonContains('popup_tips->warehouse', true)
            ->get();
        $customers = TradePartner::whereIn('type', ['CS', 'CLIENT'])->get();
        $shippers = TradePartner::all();
        $consignees = TradePartner::all();
        $offices = Office::where('is_active', true)->get();
        $users = User::all();
        $packageUnits = \App\Models\PackageUnit::all();

        // Auto-generate receipt number
        $lastReceipt = WarehouseReceipt::withTrashed()->latest('id')->first();
        $nextNo = $lastReceipt ? 'WR-' . str_pad($lastReceipt->id + 1, 6, '0', STR_PAD_LEFT) : 'WR-000001';

        return view('warehouse.receipts.create', compact(
            'warehouses', 'customers', 'shippers', 'consignees',
            'offices', 'users', 'packageUnits', 'nextNo'
        ));
    }

    public function store(StoreWarehouseReceiptRequest $request)
    {
        $data = $request->validated();
        $data['is_hazardous'] = $request->boolean('is_hazardous');
        $data['is_heat_treated'] = $request->boolean('is_heat_treated');

        // Auto-generate receipt_no if requested
        if ($request->input('auto_gen') === '1') {
            $lastReceipt = WarehouseReceipt::withTrashed()->latest('id')->first();
            $data['receipt_no'] = $lastReceipt ? 'WR-' . str_pad($lastReceipt->id + 1, 6, '0', STR_PAD_LEFT) : 'WR-000001';
        } elseif (empty($data['receipt_no'])) {
            // Fallback: generate if empty
            $lastReceipt = WarehouseReceipt::withTrashed()->latest('id')->first();
            $data['receipt_no'] = $lastReceipt ? 'WR-' . str_pad($lastReceipt->id + 1, 6, '0', STR_PAD_LEFT) : 'WR-000001';
        }

        // Save charges and memos as JSON
        if ($request->filled('charges_json')) {
            $data['charges_data'] = json_decode($request->input('charges_json'), true);
        }
        if ($request->filled('memos_json')) {
            $data['memos_data'] = json_decode($request->input('memos_json'), true);
        }

        DB::beginTransaction();
        try {
            $receipt = WarehouseReceipt::create($data);

            // Save receipt items
            if ($request->filled('items_json')) {
                $items = json_decode($request->input('items_json'), true) ?? [];
                foreach ($items as $itemData) {
                    if (!empty($itemData['pkg_qty'])) {
                        $receipt->items()->create([
                            'length_cm' => $itemData['length'] ?? null,
                            'width_cm' => $itemData['width'] ?? null,
                            'height_cm' => $itemData['height'] ?? null,
                            'dimension' => $itemData['dimension'] ?? null,
                            'pkg_qty' => $itemData['pkg_qty'] ?? 0,
                            'unit' => $itemData['unit'] ?? null,
                            'sku_po' => $itemData['sku_po'] ?? null,
                            'pallet_qty' => $itemData['pallet_qty'] ?? 0,
                            'weight_kg' => $itemData['weight_kg'] ?? 0,
                            'weight_lbs' => $itemData['weight_lbs'] ?? 0,
                            'volume_cbm' => $itemData['volume_cbm'] ?? 0,
                            'volume_cft' => $itemData['volume_cft'] ?? 0,
                            'act_weight_kg' => $itemData['act_weight_kg'] ?? 0,
                            'act_weight_lbs' => $itemData['act_weight_lbs'] ?? 0,
                            'item_date' => $itemData['item_date'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'receipt' => $receipt, 'message' => 'Warehouse Receipt created successfully.']);
            }

            if ($request->input('save_action') === 'save_new') {
                return redirect()->route('warehouse.receipts.create')
                    ->with('success', 'Warehouse Receipt created successfully.');
            }

            return redirect()->route('warehouse.receipts.edit', $receipt->id)
                ->with('success', 'Warehouse Receipt created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create receipt: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $receipt = WarehouseReceipt::with(['customer', 'shipper', 'consignee', 'office', 'operator', 'items', 'documents'])->findOrFail($id);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $receipt
            ]);
        }

        return redirect()->route('warehouse.receipts.edit', $receipt->id);
    }

    public function edit($id)
    {
        $receipt = WarehouseReceipt::with(['items', 'documents.uploader'])->findOrFail($id);
        $docData = $receipt->documents->map(fn($d) => [
            'id' => $d->id,
            'file_name' => $d->file_name,
            'file_extension' => $d->file_extension,
            'file_size' => $d->file_size,
            'uploader_name' => $d->uploader->name ?? 'N/A',
            'created_at' => $d->created_at?->format('Y-m-d') ?? '',
        ])->values()->toArray();
        $memoData = $receipt->memos_data ?? [];
        $warehouses = TradePartner::whereIn('type', ['WH', 'CF', 'CY', 'WAREHOUSE', 'CFS'])
            ->orWhereJsonContains('popup_tips->warehouse', true)
            ->get();
        $customers = TradePartner::whereIn('type', ['CS', 'CLIENT'])->get();
        $shippers = TradePartner::all();
        $consignees = TradePartner::all();
        $offices = Office::where('is_active', true)->get();
        $users = User::all();
        $packageUnits = \App\Models\PackageUnit::all();

        return view('warehouse.receipts.create', compact(
            'receipt', 'warehouses', 'customers', 'shippers', 'consignees',
            'offices', 'users', 'packageUnits', 'docData', 'memoData'
        ));
    }

    public function update(UpdateWarehouseReceiptRequest $request, $id)
    {
        $receipt = WarehouseReceipt::findOrFail($id);
        $data = $request->validated();
        $data['is_hazardous'] = $request->boolean('is_hazardous');
        $data['is_heat_treated'] = $request->boolean('is_heat_treated');

        // Auto-generate receipt_no if requested
        if ($request->input('auto_gen') === '1') {
            $lastReceipt = WarehouseReceipt::withTrashed()->latest('id')->first();
            $data['receipt_no'] = $lastReceipt ? 'WR-' . str_pad($lastReceipt->id + 1, 6, '0', STR_PAD_LEFT) : 'WR-000001';
        }

        // Save charges and memos as JSON
        if ($request->filled('charges_json')) {
            $data['charges_data'] = json_decode($request->input('charges_json'), true);
        }
        if ($request->filled('memos_json')) {
            $data['memos_data'] = json_decode($request->input('memos_json'), true);
        }

        DB::beginTransaction();
        try {
            $receipt->update($data);

            // Replace items: delete old, insert new
            if ($request->filled('items_json')) {
                $receipt->items()->delete();
                $items = json_decode($request->input('items_json'), true) ?? [];
                foreach ($items as $itemData) {
                    if (!empty($itemData['pkg_qty'])) {
                        $receipt->items()->create([
                            'length_cm' => $itemData['length'] ?? null,
                            'width_cm' => $itemData['width'] ?? null,
                            'height_cm' => $itemData['height'] ?? null,
                            'dimension' => $itemData['dimension'] ?? null,
                            'pkg_qty' => $itemData['pkg_qty'] ?? 0,
                            'unit' => $itemData['unit'] ?? null,
                            'sku_po' => $itemData['sku_po'] ?? null,
                            'pallet_qty' => $itemData['pallet_qty'] ?? 0,
                            'weight_kg' => $itemData['weight_kg'] ?? 0,
                            'weight_lbs' => $itemData['weight_lbs'] ?? 0,
                            'volume_cbm' => $itemData['volume_cbm'] ?? 0,
                            'volume_cft' => $itemData['volume_cft'] ?? 0,
                            'act_weight_kg' => $itemData['act_weight_kg'] ?? 0,
                            'act_weight_lbs' => $itemData['act_weight_lbs'] ?? 0,
                            'item_date' => $itemData['item_date'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'receipt' => $receipt, 'message' => 'Warehouse Receipt updated successfully.']);
            }

            if ($request->input('save_action') === 'save_new') {
                return redirect()->route('warehouse.receipts.create')
                    ->with('success', 'Warehouse Receipt updated successfully.');
            }

            return redirect()->route('warehouse.receipts.index')
                ->with('success', 'Warehouse Receipt updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update receipt: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $receipt = WarehouseReceipt::findOrFail($id);
        $receipt->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Warehouse Receipt deleted successfully.']);
        }

        return redirect()->route('warehouse.receipts.index')
            ->with('success', 'Warehouse Receipt deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No records selected.']);
        }
        WarehouseReceipt::whereIn('id', $ids)->delete();
        return response()->json(['success' => true, 'message' => count($ids) . ' receipt(s) deleted successfully.']);
    }

    public function updateColor(Request $request, $id)
    {
        $receipt = WarehouseReceipt::findOrFail($id);
        $receipt->update(['color' => $request->input('color', null)]);
        return response()->json(['success' => true, 'message' => 'Status color updated.']);
    }

    public function exportCsv(Request $request)
    {
        $query = WarehouseReceipt::with(['warehouse', 'customer', 'shipper', 'consignee', 'office', 'operator']);
        $this->applyFiltersToQuery($query, $request);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="warehouse-receipts-' . date('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($query) {
            if (ob_get_level()) { ob_end_flush(); }
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, [
                'Receipt No', 'Received Date', 'Truck B/L', 'Shipper', 'Consignee',
                'Customer', 'Cargo Type', 'Commodity', 'Location', 'Carrier',
                'Office', 'Hazmat', 'Heat Treated', 'Freight Charge', 'Freight Amount', 'Check No'
            ]);

            $query->orderBy('receipt_no')->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $r) {
                    fputcsv($handle, [
                        $r->receipt_no,
                        $r->receipt_date ? $r->receipt_date->format('Y-m-d H:i') : '',
                        $r->tracking_no,
                        $r->shipper->name ?? '',
                        $r->consignee->name ?? '',
                        $r->customer->name ?? '',
                        $r->cargo_type,
                        $r->commodity,
                        $r->location_code,
                        $r->carrier_name,
                        $r->office->code ?? '',
                        $r->is_hazardous ? 'Yes' : 'No',
                        $r->is_heat_treated ? 'Yes' : 'No',
                        $r->freight_charge_type,
                        $r->freight_amount,
                        $r->check_no,
                    ]);
                }
                flush();
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, 'warehouse-receipts-' . date('Y-m-d') . '.csv', $headers);
    }

    public function uploadDocument(Request $request, WarehouseReceipt $warehouse_receipt)
    {
        $request->validate(['file' => 'required|file|max:10240']);

        $file = $request->file('file');
        $path = $file->store('documents/receipts', 'public');

        $document = $warehouse_receipt->documents()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        $document->load('uploader');

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'file_name' => $document->file_name,
                'file_extension' => $document->file_extension,
                'file_size' => $document->file_size,
                'uploader_name' => $document->uploader?->name ?? 'N/A',
                'created_at' => $document->created_at?->format('Y-m-d') ?? '',
            ],
        ]);
    }

    public function deleteDocument(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }

    public function downloadDocument(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->withErrors(['error' => 'File not found.']);
        }
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Generate next receipt number (AJAX)
     */
    public function generateReceiptNo()
    {
        $lastReceipt = WarehouseReceipt::withTrashed()->latest('id')->first();
        $nextNo = $lastReceipt ? 'WR-' . str_pad($lastReceipt->id + 1, 6, '0', STR_PAD_LEFT) : 'WR-000001';
        return response()->json(['receipt_no' => $nextNo]);
    }
}
