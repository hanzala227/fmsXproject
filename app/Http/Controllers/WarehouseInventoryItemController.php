<?php

namespace App\Http\Controllers;

use App\Models\WarehouseInventoryItem;
use App\Models\WarehouseReceivingItem;
use App\Models\TradePartner;
use App\Models\PackageUnit;
use App\Http\Requests\StoreWarehouseInventoryItemRequest;
use App\Http\Requests\UpdateWarehouseInventoryItemRequest;
use Illuminate\Http\Request;

class WarehouseInventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseInventoryItem::with(['warehouse', 'unit']);

        $this->applyFiltersToQuery($query, $request);

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['item_name', 'sku', 'created_at', 'weight_kg', 'volume_cbm', 'warehouse_id'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $items = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        $warehouses = TradePartner::whereIn('type', ['WH', 'WAREHOUSE'])->orderBy('name')->get();
        $units = PackageUnit::all();
        $customers = TradePartner::whereNotIn('type', ['WH', 'WAREHOUSE'])->whereNotNull('name')->orderBy('name')->get();
        $vendors = TradePartner::whereNotIn('type', ['WH', 'WAREHOUSE'])->whereNotNull('name')->orderBy('name')->get();

        if ($request->ajax()) {
            return view('warehouse.items', compact('items', 'warehouses', 'units', 'customers', 'vendors'));
        }

        return view('warehouse.items', compact('items', 'warehouses', 'units', 'customers', 'vendors'));
    }

    private function applyFiltersToQuery($query, Request $request)
    {
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('upc_ean', 'like', "%{$search}%")
                  ->orWhere('mpn', 'like', "%{$search}%")
                  ->orWhere('hts_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_sku')) {
            $query->where('sku', 'like', "%{$request->filter_sku}%");
        }
        if ($request->filled('filter_item_name')) {
            $query->where('item_name', 'like', "%{$request->filter_item_name}%");
        }
        if ($request->filled('filter_warehouse')) {
            $query->whereHas('warehouse', fn($q) => $q->where('name', 'like', "%{$request->filter_warehouse}%"));
        }
        if ($request->filled('filter_unit')) {
            $query->whereHas('unit', fn($q) => $q->where('name', 'like', "%{$request->filter_unit}%"));
        }
        if ($request->filled('filter_description')) {
            $query->where('description', 'like', "%{$request->filter_description}%");
        }

        return $query;
    }

    public function store(StoreWarehouseInventoryItemRequest $request)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('product_photo')) {
            $data['product_photo'] = $request->file('product_photo')->store('items/photos', 'public');
        }

        $item = WarehouseInventoryItem::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inventory item created successfully.', 'item' => $item]);
        }

        return redirect()->route('items.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function update(UpdateWarehouseInventoryItemRequest $request, $id)
    {
        $item = WarehouseInventoryItem::findOrFail($id);
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('product_photo')) {
            // Delete old photo if exists
            if ($item->product_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($item->product_photo);
            }
            $data['product_photo'] = $request->file('product_photo')->store('items/photos', 'public');
        } else {
            // Don't overwrite existing photo if no new file
            unset($data['product_photo']);
        }

        $item->update($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inventory item updated successfully.', 'item' => $item]);
        }

        return redirect()->route('items.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $item = WarehouseInventoryItem::findOrFail($id);
        $item->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Inventory item deleted successfully.']);
        }

        return redirect()->route('items.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    public function updateColor(Request $request, WarehouseInventoryItem $item)
    {
        $request->validate(['color' => 'nullable|string|max:20']);
        $item->update(['color' => $request->color]);
        return response()->json(['success' => true, 'color' => $item->color]);
    }

    public function exportCsv(Request $request)
    {
        $query = WarehouseInventoryItem::with(['warehouse', 'unit']);
        $this->applyFiltersToQuery($query, $request);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="warehouse-items-' . now()->format('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($query) {
            if (ob_get_level()) { ob_end_flush(); }
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'ID', 'Warehouse', 'SKU', 'Item Name', 'Description', 'Qty Unit',
                'On Hand Qty', 'Available Qty', 'Weight (KG)', 'Volume (CBM)', 'Created At',
            ]);

            $query->latest()->chunk(200, function ($chunk) use ($file) {
                foreach ($chunk as $item) {
                    fputcsv($file, [
                        $item->id,
                        $item->warehouse->name ?? '',
                        $item->sku,
                        $item->item_name,
                        $item->description,
                        $item->unit->name ?? '',
                        $item->on_hand_qty,
                        $item->available_qty,
                        $item->weight_kg,
                        $item->volume_cbm,
                        $item->created_at->format('Y-m-d H:i'),
                    ]);
                }
                flush();
            });
            fclose($file);
        };

        return response()->streamDownload($callback, 'warehouse-items-' . now()->format('Y-m-d') . '.csv', $headers);
    }

    public function bulkDelete(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('items.index');
        }
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:warehouse_inventory_items,id']);
        $count = WarehouseInventoryItem::whereIn('id', $request->ids)->delete();
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $count . ' item(s) deleted successfully.'
            ]);
        }
        return redirect()->route('items.index')->with('success', $count . ' item(s) deleted successfully.');
    }

    public function summary(Request $request)
    {
        $query = WarehouseInventoryItem::with(['warehouse', 'unit', 'customer', 'latestReceivingItem.receiving.office', 'latestReceivingItem.receiving.customer']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('upc_ean', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_customer')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"))
                  ->orWhereHas('latestReceivingItem.receiving.customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"));
            });
        }
        if ($request->filled('filter_warehouse')) {
            $query->whereHas('warehouse', fn($q) => $q->where('name', 'like', "%{$request->filter_warehouse}%"));
        }
        if ($request->filled('filter_sku')) {
            $query->where('sku', 'like', "%{$request->filter_sku}%");
        }
        if ($request->filled('filter_item_name')) {
            $query->where('item_name', 'like', "%{$request->filter_item_name}%");
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['sku', 'item_name', 'on_hand_qty', 'available_qty', 'weight_kg', 'volume_cbm', 'created_at', 'warehouse_id'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $statsQuery = clone $query;
        $stats = [
            'total_items' => $statsQuery->count(),
            'total_on_hand' => (clone $statsQuery)->sum('on_hand_qty'),
            'total_available' => (clone $statsQuery)->sum('available_qty'),
            'total_weight' => (clone $statsQuery)->sum('weight_kg'),
            'total_volume' => (clone $statsQuery)->sum('volume_cbm'),
        ];

        $items = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        $warehouses = TradePartner::whereIn('type', ['WH', 'WAREHOUSE'])->orderBy('name')->get();
        $units = PackageUnit::all();
        $customers = TradePartner::whereNotIn('type', ['WH', 'WAREHOUSE'])->whereNotNull('name')->orderBy('name')->get();

        return view('warehouse.inventory.summary', compact('items', 'stats', 'warehouses', 'units', 'customers'));
    }

    public function summaryStore(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|integer|exists:trade_partners,id',
            'sku' => 'required|string|max:100',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'customer_id' => 'nullable|integer',
            'upc_ean' => 'nullable|string|max:100',
            'unit_id' => 'nullable|integer',
            'on_hand_qty' => 'required|numeric|min:0',
            'available_qty' => 'required|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:enable,disable',
        ]);

        $validated['status'] = $validated['status'] ?? 'enable';
        $item = WarehouseInventoryItem::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Inventory summary item created successfully.', 'item' => $item]);
        }

        return redirect()->route('inventory.summary')->with('success', 'Inventory summary item created successfully.');
    }

    public function summaryUpdate(Request $request, $id)
    {
        $item = WarehouseInventoryItem::findOrFail($id);
        $validated = $request->validate([
            'warehouse_id' => 'required|integer|exists:trade_partners,id',
            'sku' => 'required|string|max:100',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'customer_id' => 'nullable|integer',
            'upc_ean' => 'nullable|string|max:100',
            'unit_id' => 'nullable|integer',
            'on_hand_qty' => 'required|numeric|min:0',
            'available_qty' => 'required|numeric|min:0',
            'weight_kg' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:enable,disable',
        ]);

        $item->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Inventory summary item updated successfully.', 'item' => $item]);
        }

        return redirect()->route('inventory.summary')->with('success', 'Inventory summary item updated successfully.');
    }

    public function summaryDestroy(Request $request, $id)
    {
        $item = WarehouseInventoryItem::findOrFail($id);
        $item->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Inventory summary item deleted successfully.']);
        }

        return redirect()->route('inventory.summary')->with('success', 'Inventory summary item deleted successfully.');
    }

    public function summaryBulkDelete(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('inventory.summary');
        }
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:warehouse_inventory_items,id']);
        $count = WarehouseInventoryItem::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $count . ' item(s) deleted successfully.'
            ]);
        }
        return redirect()->route('inventory.summary')->with('success', $count . ' item(s) deleted successfully.');
    }

    public function detail(Request $request)
    {
        $query = WarehouseReceivingItem::with([
            'receiving.office',
            'receiving.trucker',
            'receiving.shipFrom',
            'receiving.customer',
            'receiving.receipt.customer',
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('customer_po', 'like', "%{$search}%")
                  ->orWhere('order_po_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_customer')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('receiving.customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"))
                  ->orWhereHas('receiving.receipt.customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"));
            });
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('receiving.office', fn($q) => $q->where('code', 'like', "%{$request->filter_office}%"));
        }
        if ($request->filled('filter_date')) {
            $query->whereHas('receiving', fn($q) => $q->where('receiving_date', 'like', "%{$request->filter_date}%"));
        }
        if ($request->filled('filter_sku')) {
            $query->where('sku_no', 'like', "%{$request->filter_sku}%");
        }
        if ($request->filled('filter_item_name')) {
            $query->where('description', 'like', "%{$request->filter_item_name}%");
        }
        if ($request->filled('filter_warehouse')) {
            $query->whereHas('receiving.receipt', fn($q) => $q->whereHas('warehouse', fn($wq) => $wq->where('name', 'like', "%{$request->filter_warehouse}%")));
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['sku_no', 'description', 'qty', 'weight_kg', 'measure_cbm', 'created_at', 'customer_po'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $cloneQuery = clone $query;
        $totals = [
            'qty' => (clone $cloneQuery)->sum('qty'),
            'weight' => (clone $cloneQuery)->sum('weight_kg'),
            'measure' => (clone $cloneQuery)->sum('measure_cbm'),
        ];

        $items = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        $customers = TradePartner::whereNotIn('type', ['WH', 'WAREHOUSE'])->whereNotNull('name')->orderBy('name')->get();
        $warehouses = TradePartner::whereIn('type', ['WH', 'WAREHOUSE'])->orderBy('name')->get();
        $offices = \App\Models\Office::where('is_active', true)->get();
        $packageUnits = PackageUnit::all();
        $receivings = \App\Models\WarehouseReceiving::latest()->take(50)->get();

        return view('warehouse.inventory.detail', compact('items', 'totals', 'customers', 'warehouses', 'offices', 'packageUnits', 'receivings'));
    }

    public function detailStore(Request $request)
    {
        $validated = $request->validate([
            'warehouse_receiving_id' => 'nullable|integer',
            'sku_no' => 'required|string|max:100',
            'customer_po' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'order_po_no' => 'nullable|string|max:100',
            'order_qty' => 'nullable|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'qty_unit' => 'nullable|string|max:50',
            'pack' => 'nullable|numeric|min:0',
            'pack_unit' => 'nullable|string|max:50',
            'pallet' => 'nullable|string|max:100',
            'weight_kg' => 'nullable|numeric|min:0',
            'measure_cbm' => 'nullable|numeric|min:0',
        ]);

        if (empty($validated['warehouse_receiving_id'])) {
            $validated['warehouse_receiving_id'] = null;
        }

        $item = WarehouseReceivingItem::create($validated);

        // Also sync/create WarehouseInventoryItem master record if SKU doesn't exist
        $master = WarehouseInventoryItem::where('sku', $item->sku_no)->first();
        if (!$master) {
            $defaultWh = TradePartner::whereIn('type', ['WH', 'WAREHOUSE'])->first();
            WarehouseInventoryItem::create([
                'warehouse_id' => $defaultWh?->id,
                'sku' => $item->sku_no,
                'item_name' => $item->description,
                'description' => $item->description,
                'on_hand_qty' => $item->qty,
                'available_qty' => $item->qty,
                'weight_kg' => $item->weight_kg ?? 0,
                'volume_cbm' => $item->measure_cbm ?? 0,
                'status' => 'enable',
            ]);
        } else {
            $master->update([
                'on_hand_qty' => $master->on_hand_qty + $item->qty,
                'available_qty' => $master->available_qty + $item->qty,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Inventory detail created successfully.', 'item' => $item]);
        }

        return redirect()->route('inventory.detail')->with('success', 'Inventory detail record created successfully.');
    }

    public function detailUpdate(Request $request, $id)
    {
        $item = WarehouseReceivingItem::findOrFail($id);
        $validated = $request->validate([
            'warehouse_receiving_id' => 'nullable|integer',
            'sku_no' => 'required|string|max:100',
            'customer_po' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'order_po_no' => 'nullable|string|max:100',
            'order_qty' => 'nullable|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'qty_unit' => 'nullable|string|max:50',
            'pack' => 'nullable|numeric|min:0',
            'pack_unit' => 'nullable|string|max:50',
            'pallet' => 'nullable|string|max:100',
            'weight_kg' => 'nullable|numeric|min:0',
            'measure_cbm' => 'nullable|numeric|min:0',
        ]);

        if (empty($validated['warehouse_receiving_id'])) {
            $validated['warehouse_receiving_id'] = null;
        }

        $item->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Inventory detail record updated successfully.', 'item' => $item]);
        }

        return redirect()->route('inventory.detail')->with('success', 'Inventory detail record updated successfully.');
    }

    public function detailDestroy(Request $request, $id)
    {
        $item = WarehouseReceivingItem::findOrFail($id);
        $item->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Inventory detail record deleted successfully.']);
        }

        return redirect()->route('inventory.detail')->with('success', 'Inventory detail record deleted successfully.');
    }

    public function detailBulkDelete(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('inventory.detail');
        }
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:warehouse_receiving_items,id']);
        $count = WarehouseReceivingItem::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $count . ' detail record(s) deleted successfully.'
            ]);
        }
        return redirect()->route('inventory.detail')->with('success', $count . ' record(s) deleted successfully.');
    }

    public function detailExportCsv(Request $request)
    {
        $query = WarehouseReceivingItem::with([
            'receiving.office',
            'receiving.trucker',
            'receiving.shipFrom',
            'receiving.customer',
            'receiving.receipt.customer',
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('customer_po', 'like', "%{$search}%")
                  ->orWhere('order_po_no', 'like', "%{$search}%");
            });
        }
        if ($request->filled('filter_customer')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('receiving.customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"))
                  ->orWhereHas('receiving.receipt.customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"));
            });
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('receiving.office', fn($q) => $q->where('code', 'like', "%{$request->filter_office}%"));
        }
        if ($request->filled('filter_date')) {
            $query->whereHas('receiving', fn($q) => $q->where('receiving_date', 'like', "%{$request->filter_date}%"));
        }
        if ($request->filled('filter_sku')) {
            $query->where('sku_no', 'like', "%{$request->filter_sku}%");
        }
        if ($request->filled('filter_item_name')) {
            $query->where('description', 'like', "%{$request->filter_item_name}%");
        }
        if ($request->filled('filter_warehouse')) {
            $query->whereHas('receiving.receipt', fn($q) => $q->whereHas('warehouse', fn($wq) => $wq->where('name', 'like', "%{$request->filter_warehouse}%")));
        }

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-detail-' . now()->format('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($query) {
            if (ob_get_level()) { ob_end_flush(); }
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Date', 'Customer', 'SKU No.', 'Product Description',
                'Booking no', 'Office', 'Trucker', 'From / To',
                'Qty', 'PCS', 'Weight (KG)', 'Measurement (CBM)', 'Status',
            ]);

            $query->latest()->chunk(200, function ($chunk) use ($file) {
                foreach ($chunk as $item) {
                    fputcsv($file, [
                        $item->receiving?->receiving_date?->format('m-d-Y') ?? $item->created_at?->format('m-d-Y') ?? '',
                        $item->receiving?->customer?->name ?? $item->receiving?->receipt?->customer?->name ?? '',
                        $item->sku_no,
                        $item->description,
                        $item->receiving?->bl_no ?? '',
                        $item->receiving?->office?->code ?? '',
                        $item->receiving?->trucker?->name ?? '',
                        $item->receiving?->shipFrom?->name ?? '',
                        $item->qty ?? 0,
                        $item->pack ?? 0,
                        $item->weight_kg ?? 0,
                        $item->measure_cbm ?? 0,
                        $item->receiving?->status ?? '',
                    ]);
                }
                flush();
            });
            fclose($file);
        };

        return response()->streamDownload($callback, 'inventory-detail-' . now()->format('Y-m-d') . '.csv', $headers);
    }

    public function summaryExportCsv(Request $request)
    {
        $query = WarehouseInventoryItem::with(['warehouse', 'unit', 'customer', 'latestReceivingItem.receiving.office', 'latestReceivingItem.receiving.customer']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('upc_ean', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_customer')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"))
                  ->orWhereHas('latestReceivingItem.receiving.customer', fn($sq) => $sq->where('name', 'like', "%{$request->filter_customer}%"));
            });
        }
        if ($request->filled('filter_warehouse')) {
            $query->whereHas('warehouse', fn($q) => $q->where('name', 'like', "%{$request->filter_warehouse}%"));
        }
        if ($request->filled('filter_sku')) {
            $query->where('sku', 'like', "%{$request->filter_sku}%");
        }
        if ($request->filled('filter_item_name')) {
            $query->where('item_name', 'like', "%{$request->filter_item_name}%");
        }

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-summary-' . now()->format('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($query) {
            if (ob_get_level()) { ob_end_flush(); }
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Customer', 'Warehouse', 'SKU No.', 'Customer P.O.', 'Product Description',
                'B/L No.', 'Office', 'UPC/EAN',
                'On Hand Qty', 'Allocated Qty', 'Available Qty', 'Qty Unit',
                'Weight (KG)', 'Measurement (CBM)', 'Inner Pack',
                'On Hand Pcs', 'Allocated Pcs', 'Available Pcs',
            ]);

            $query->latest()->chunk(200, function ($chunk) use ($file) {
                foreach ($chunk as $item) {
                    $rec = $item->latestReceivingItem;
                    $cust = $item->customer ?? $rec?->receiving?->customer;
                    fputcsv($file, [
                        $cust?->name ?? '',
                        $item->warehouse->name ?? '',
                        $item->sku,
                        $rec->customer_po ?? '',
                        $item->item_name,
                        $rec->receiving->bl_no ?? '',
                        $rec->receiving->office->code ?? '',
                        $item->upc_ean ?? $rec->sku_no ?? '',
                        $item->on_hand_qty,
                        0, // Allocated Qty
                        $item->available_qty,
                        $item->unit->name ?? '',
                        $item->weight_kg,
                        $item->volume_cbm,
                        0, // Inner Pack
                        $item->on_hand_qty,
                        0, // Allocated Pcs
                        $item->available_qty,
                    ]);
                }
                flush();
            });
            fclose($file);
        };

        return response()->streamDownload($callback, 'inventory-summary-' . now()->format('Y-m-d') . '.csv', $headers);
    }
}
