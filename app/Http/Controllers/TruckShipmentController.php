<?php

namespace App\Http\Controllers;

use App\Models\TruckShipment;
use App\Models\Office;
use App\Models\Port;
use App\Models\TradePartner;
use App\Models\Quotation;
use App\Models\User;
use App\Models\ContainerType;
use App\Models\TruckShipmentContainer;
use App\Models\TruckShipmentCommodity;
use App\Models\WarehouseReceipt;
use App\Models\WarehouseReceiptItem;
use App\Services\TruckShipmentService;
use App\Http\Requests\StoreTruckShipmentRequest;
use App\Http\Requests\UpdateTruckShipmentRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TruckShipmentController extends Controller
{
    protected $truckShipmentService;

    public function __construct(TruckShipmentService $service)
    {
        $this->truckShipmentService = $service;
    }

    public function index(Request $request)
    {
        $query = TruckShipment::with([
            'office', 'operator', 'customer', 'shipper', 'consignee', 'trucker', 'pol', 'pod',
            'finalDestination', 'billTo', 'packageUnit', 'charges', 'containers'
        ]);

        // Quick search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mbl_no', 'like', "%{$search}%")
                  ->orWhere('hbl_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('trucker', function($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Column filters
        if ($request->filter_file_no) {
            $query->where('file_no', 'like', '%' . $request->filter_file_no . '%');
        }
        if ($request->filter_post_date) {
            $query->whereDate('post_date', 'like', '%' . $request->filter_post_date . '%');
        }
        if ($request->filter_customer) {
            $query->where(function($q) use ($request) {
                $q->where('customer_id', $request->filter_customer)
                  ->orWhereHas('customer', function($c) use ($request) {
                      $c->where('name', 'like', '%' . $request->filter_customer . '%');
                  });
            });
        }
        if ($request->filter_trucker) {
            $query->whereHas('trucker', function($t) use ($request) {
                $t->where('name', 'like', '%' . $request->filter_trucker . '%');
            });
        }
        if ($request->filter_mbl_no) {
            $query->where('mbl_no', 'like', '%' . $request->filter_mbl_no . '%');
        }
        if ($request->filter_hbl_no) {
            $query->where('hbl_no', 'like', '%' . $request->filter_hbl_no . '%');
        }

        $sortField = $request->sort ?? 'created_at';
        $sortDir = $request->dir ?? 'desc';
        $allowedSorts = ['file_no', 'post_date', 'created_at', 'mbl_no', 'pkg_qty', 'weight_kg'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $perPage = (int) ($request->per_page ?? 20);
        if ($perPage < 1) $perPage = 20;

        $shipments = $query->orderBy($sortField, $sortDir)->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('truck.partials.list-rows', compact('shipments'))->render(),
                'pagination' => view('truck.partials.list-pagination', compact('shipments'))->render(),
                'total' => $shipments->total(),
                'from' => $shipments->firstItem(),
                'to' => $shipments->lastItem(),
            ]);
        }

        return view('truck.list', compact('shipments'));
    }

    public function myShipmentList(Request $request)
    {
        $query = TruckShipment::with([
            'office', 'operator', 'customer', 'shipper', 'consignee', 'trucker', 'pol', 'pod',
            'finalDestination', 'billTo', 'packageUnit', 'charges', 'containers'
        ]);

        // Filter to current user's shipments (OP or Sales), or unassigned
        $query->where(function ($q) {
            $q->where('op_id', auth()->id())
              ->orWhere('sales_id', auth()->id())
              ->orWhere(function ($sub) {
                  $sub->whereNull('op_id')->whereNull('sales_id');
              });
        });

        // Quick search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mbl_no', 'like', "%{$search}%")
                  ->orWhere('hbl_no', 'like', "%{$search}%");
            });
        }

        // Column filters
        if ($request->filter_file_no) {
            $query->where('file_no', 'like', '%' . $request->filter_file_no . '%');
        }
        if ($request->filter_customer) {
            $query->where('customer_id', $request->filter_customer);
        }

        $sortField = $request->sort ?? 'created_at';
        $sortDir = $request->dir ?? 'desc';
        $allowedSorts = ['file_no', 'post_date', 'created_at', 'mbl_no', 'pkg_qty', 'weight_kg'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $shipments = $query->orderBy($sortField, $sortDir)->paginate($request->per_page ?? 20)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('truck.partials.list-rows', compact('shipments'))->render(),
                'pagination' => view('truck.partials.list-pagination', compact('shipments'))->render(),
                'total' => $shipments->total(),
                'from' => $shipments->firstItem(),
                'to' => $shipments->lastItem(),
            ]);
        }

        $offices = Office::where('is_active', true)->get();
        $users = User::all();
        $agents = TradePartner::all();
        $ports = Port::all()->latest()->take(10)->get();

        return view('truck.my-shipment-list', compact('shipments', 'offices', 'users', 'agents', 'ports'));
    }



    public function create(Request $request)
    {
        $offices = Office::where('is_active', true)->get();
        $ports = Port::all()->latest()->take(10)->get();
        $agents = TradePartner::all();
        $users = \App\Models\User::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $quotations = Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'items.currency'])->forModule('Truck')->latest()->get();
        $locations = TradePartner::whereIn('type', ['CF', 'CY', 'WH', 'WAREHOUSE', 'CFS'])
            ->orWhere('type', 'LOCATION')
            ->get();
        $truckers = TradePartner::whereIn('type', ['TRUCKER', 'CR', 'CARRIER', 'CS', 'PR'])->get();
        $currencies = \App\Models\Currency::all();
        $containerTypes = ContainerType::all();

        $page = $request->segment(2);

        // Handle copy from existing shipment
        $copyShipment = null;
        if ($request->has('copy')) {
            $copyId = $request->query('copy');
            $copyShipment = TruckShipment::with([
                'office', 'operator', 'customer', 'shipper', 'consignee', 'trucker',
                'pol', 'pod', 'finalDestination', 'billTo', 'packageUnit',
                'memos', 'containers', 'charges'
            ])->find($copyId);
        }

        return view('truck.create', compact(
            'offices', 'ports', 'agents', 'users', 'packageUnits',
            'quotations', 'locations', 'truckers', 'currencies', 'page', 'containerTypes',
            'copyShipment'
        ));
    }

    public function store(StoreTruckShipmentRequest $request)
    {
        $shipment = $this->truckShipmentService->store($request->validated());

        return redirect()->route('truck.edit', $shipment->id)
            ->with('success', 'Truck Shipment created successfully.');
    }

    public function edit(TruckShipment $truckShipment)
    {
        $truckShipment->load(['charges', 'documents.uploader', 'workOrders', 'memos', 'containers', 'statusLogs']);

        $offices = Office::where('is_active', true)->get();
        $ports = Port::all()->latest()->take(10)->get();
        $agents = TradePartner::all();
        $users = \App\Models\User::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $quotations = Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'items.currency'])->forModule('Truck')->latest()->get();
        $locations = TradePartner::whereIn('type', ['CF', 'CY', 'WH', 'WAREHOUSE', 'CFS'])
            ->orWhere('type', 'LOCATION')
            ->get();
        $truckers = TradePartner::whereIn('type', ['TRUCKER', 'CR', 'CARRIER', 'CS', 'PR'])->get();
        $currencies = \App\Models\Currency::all();

        $containerTypes = ContainerType::all();

        return view('truck.create', compact(
            'truckShipment', 'offices', 'ports', 'agents', 'users', 'packageUnits',
            'quotations', 'locations', 'truckers', 'currencies', 'containerTypes'
        ));
    }

    public function update(UpdateTruckShipmentRequest $request, TruckShipment $truckShipment)
    {
        $this->truckShipmentService->update($truckShipment, $request->validated());

        return back()->with('success', 'Truck Shipment updated successfully.');
    }

    public function updateColor(Request $request, TruckShipment $truckShipment)
    {
        $request->validate([
            'color' => 'nullable|string|max:20',
        ]);

        $truckShipment->update(['color' => $request->color]);

        return response()->json(['success' => true, 'color' => $truckShipment->color]);
    }



    public function destroy(TruckShipment $truckShipment)
    {
        try {
            $truckShipment->delete();
            return response()->json(['success' => true, 'message' => 'Truck Shipment deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    // ========== Bulk Actions ==========

    public function bulkDelete(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('truck.my-shipment-list');
        }

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected.']);
        }

        try {
            TruckShipment::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => count($ids) . ' shipment(s) deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    public function bulkBlock(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('truck.my-shipment-list');
        }

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected.']);
        }

        try {
            TruckShipment::whereIn('id', $ids)->update(['is_blocked' => true]);
            return response()->json(['success' => true, 'message' => count($ids) . ' shipment(s) blocked.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Block failed: ' . $e->getMessage()], 500);
        }
    }

    public function bulkUnblock(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('truck.my-shipment-list');
        }

        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected.']);
        }

        try {
            TruckShipment::whereIn('id', $ids)->update(['is_blocked' => false]);
            return response()->json(['success' => true, 'message' => count($ids) . ' shipment(s) unblocked.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unblock failed: ' . $e->getMessage()], 500);
        }
    }

    public function exportCsv(Request $request)
    {
        $query = TruckShipment::with([
            'office', 'operator', 'customer', 'shipper', 'consignee', 'trucker', 'pol', 'pod',
            'finalDestination', 'billTo', 'packageUnit', 'charges', 'containers'
        ]);

        $query->where(function ($q) {
            $q->where('op_id', auth()->id())
              ->orWhere('sales_id', auth()->id())
              ->orWhere(function ($sub) {
                  $sub->whereNull('op_id')->whereNull('sales_id');
              });
        });

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mbl_no', 'like', "%{$search}%")
                  ->orWhere('hbl_no', 'like', "%{$search}%");
            });
        }

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="truck-shipments-' . date('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($query) {
            if (ob_get_level()) { ob_end_flush(); }
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'Booking no', 'Post Date', 'Customer', 'Trucker', 'MB/L No.', 'HB/L No.',
                'Package Qty', 'Weight (Kg)', 'POL', 'POD', 'Final Destination',
                'AR Balance', 'Delivered', 'Internal Remark'
            ]);

            $query->latest()->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $s) {
                    $arTotal = $s->charges->where('type', 'AR')->sum('amount');
                    fputcsv($handle, [
                        $s->file_no,
                        $s->post_date ? $s->post_date->format('Y-m-d') : '',
                        $s->customer->name ?? '',
                        $s->trucker->name ?? '',
                        $s->mbl_no ?? '',
                        $s->hbl_no ?? '',
                        $s->pkg_qty ?? 0,
                        $s->weight_kg ?? 0,
                        $s->pol->name ?? '',
                        $s->pod->name ?? '',
                        $s->finalDestination->name ?? '',
                        number_format($arTotal, 2),
                        $s->is_delivered ? 'Yes' : 'No',
                        $s->internal_remark ?? '',
                    ]);
                }
                flush();
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, 'truck-shipments-' . date('Y-m-d') . '.csv', $headers);
    }

    public function myShipmentListPrint(Request $request)
    {
        $query = TruckShipment::with([
            'office', 'operator', 'customer', 'shipper', 'consignee', 'trucker', 'pol', 'pod',
            'finalDestination', 'billTo', 'packageUnit', 'charges', 'containers'
        ]);

        // Filter to current user's shipments (OP or Sales), or unassigned
        $query->where(function ($q) {
            $q->where('op_id', auth()->id())
              ->orWhere('sales_id', auth()->id())
              ->orWhere(function ($sub) {
                  $sub->whereNull('op_id')->whereNull('sales_id');
              });
        });

        // Quick search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mbl_no', 'like', "%{$search}%")
                  ->orWhere('hbl_no', 'like', "%{$search}%");
            });
        }

        // Column filters
        if ($request->filter_file_no) {
            $query->where('file_no', 'like', '%' . $request->filter_file_no . '%');
        }
        if ($request->filter_customer) {
            $query->where('customer_id', $request->filter_customer);
        }
        if ($request->filter_post_date) {
            $query->whereDate('post_date', 'like', '%' . $request->filter_post_date . '%');
        }

        // Sorting
        $sortField = $request->sort ?? 'created_at';
        $sortDir = $request->dir ?? 'desc';
        $allowedSorts = ['file_no', 'post_date', 'created_at', 'mbl_no', 'pkg_qty', 'weight_kg'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        // Get all records for print (no pagination limit, but reasonable cap)
        $shipments = $query->orderBy($sortField, $sortDir)->paginate(500);

        return view('truck.my-shipment-list-print', compact('shipments'));
    }

    // ========== Document Management ==========

    public function uploadDocument(Request $request, TruckShipment $truckShipment)
    {
        $request->validate([
            'document' => 'required|file|max:10240',
            'file_name' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('document');
        $fileName = $request->file_name ?? $file->getClientOriginalName();
        $path = $file->store('documents/truck-shipments/' . $truckShipment->id, 'public');

        $document = $truckShipment->documents()->create([
            'file_name' => $fileName,
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'document_type' => $request->document_type,
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);

        $document->load('uploader');

        if ($request->wantsJson()) {
            return response()->json($document);
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function deleteDocument($document)
    {
        $doc = \App\Models\Document::findOrFail($document);
        
        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['success' => true]);
    }

    public function downloadDocument($document)
    {
        $doc = \App\Models\Document::findOrFail($document);
        
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path)) {
            abort(404, 'File not found.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    // ========== Memo Management ==========

    public function addMemo(Request $request, TruckShipment $truckShipment)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'nullable|string',
            'has_alert' => 'nullable|boolean',
        ]);

        $memo = $truckShipment->memos()->create([
            'subject' => $request->subject,
            'content' => $request->content,
            'has_alert' => $request->boolean('has_alert'),
            'user_id' => auth()->id(),
        ]);

        $memo->load('user');

        return response()->json($memo);
    }

    public function updateMemo(Request $request, $truck_shipment, $memo)
    {
        $memo = \App\Models\TruckShipmentMemo::findOrFail($memo);

        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'nullable|string',
            'has_alert' => 'nullable|boolean',
        ]);

        $memo->update([
            'subject' => $request->subject,
            'content' => $request->content,
            'has_alert' => $request->boolean('has_alert'),
        ]);

        return response()->json($memo);
    }

    public function deleteMemo($truck_shipment, $memo)
    {
        $memo = \App\Models\TruckShipmentMemo::findOrFail($memo);
        $memo->delete();

        return response()->json(['success' => true]);
    }

    // ========== Charge Management ==========

    public function storeCharge(Request $request, TruckShipment $truckShipment)
    {
        $data = $request->validate([
            'type' => 'required|string|in:AR,AP',
            'charge_code' => 'nullable|string|max:50',
            'charge_name' => 'nullable|string|max:255',
            'bill_to_id' => 'nullable|exists:trade_partners,id',
            'vendor_id' => 'nullable|exists:trade_partners,id',
            'qty' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'currency_id' => 'nullable|exists:currencies,id',
            'rate' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
        ]);

        $data['chargeable_type'] = get_class($truckShipment);
        $data['chargeable_id'] = $truckShipment->id;

        $charge = $truckShipment->charges()->create($data);
        $charge->load('currency');

        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function updateCharge(Request $request, $charge)
    {
        $charge = \App\Models\Charge::findOrFail($charge);

        $data = $request->validate([
            'type' => 'nullable|string|in:AR,AP',
            'charge_code' => 'nullable|string|max:50',
            'charge_name' => 'nullable|string|max:255',
            'bill_to_id' => 'nullable|exists:trade_partners,id',
            'vendor_id' => 'nullable|exists:trade_partners,id',
            'qty' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'currency_id' => 'nullable|exists:currencies,id',
            'rate' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string',
        ]);

        $charge->update($data);

        return response()->json(['success' => true, 'charge' => $charge->fresh()->load('currency')]);
    }

    public function deleteCharge($charge)
    {
        $charge = \App\Models\Charge::findOrFail($charge);
        $charge->delete();

        return response()->json(['success' => true]);
    }

    public function createInvoiceFromCharges(Request $request, TruckShipment $truckShipment)
    {
        $charges = $truckShipment->charges()->where('is_invoiced', false)->get();

        if ($charges->count() === 0) {
            $allChargesCount = $truckShipment->charges()->count();
            if ($allChargesCount > 0) {
                $existingInv = $truckShipment->charges()->whereNotNull('invoice_no')->pluck('invoice_no')->first();
                return response()->json([
                    'success' => true,
                    'message' => 'Charges are already invoiced (' . ($existingInv ?? 'Invoiced') . '). Opening Freight Invoice...',
                    'invoice_no' => $existingInv ?? 'INV-EXISTING',
                    'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'ocean-export', 'id' => $truckShipment->id])
                ]);
            }
            return response()->json(['success' => false, 'message' => 'No charges found for this shipment. Please add charges first.'], 400);
        }

        $invNo = 'SCL' . sprintf('%08d', $truckShipment->id);
        foreach ($charges as $charge) {
            $charge->update([
                'is_invoiced' => true,
                'invoice_no' => $invNo,
                'invoice_date' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'invoice_no' => $invNo,
            'message' => "Created invoice {$invNo} for " . $charges->count() . " charges.",
            'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'ocean-export', 'id' => $truckShipment->id])
        ]);
    }

    public function pickupDeliveryOrder(TruckShipment $truckShipment)
    {
        $truckShipment->load(['customer', 'shipper', 'consignee', 'trucker', 'pol', 'pod', 'containers']);
        return view('truck.pickup-delivery-order', compact('truckShipment'));
    }

    public function bolPrint(TruckShipment $truckShipment)
    {
        $truckShipment->load(['customer', 'shipper', 'consignee', 'trucker', 'pol', 'pod', 'containers']);
        return view('truck.bol-print', compact('truckShipment'));
    }

    public function profitReportSummary(TruckShipment $truckShipment)
    {
        $truckShipment->load(['customer', 'charges']);
        return view('truck.profit-report-summary', compact('truckShipment'));
    }

    public function profitReportDetail(TruckShipment $truckShipment)
    {
        $truckShipment->load(['customer', 'charges']);
        return view('truck.profit-report-detail', compact('truckShipment'));
    }

    public function cargoManifestStatus(TruckShipment $truckShipment)
    {
        $truckShipment->load(['customer', 'containers']);
        return view('truck.cargo-manifest-status', compact('truckShipment'));
    }

    public function show($id)
    {
        $shipment = TruckShipment::with([
            'office', 'operator', 'salesPerson', 'customer', 'shipper', 'consignee', 'trucker',
            'pol', 'pod', 'finalDestination', 'billTo', 'packageUnit', 'quotation',
            'charges', 'documents', 'memos', 'containers.containerType', 'commodities', 'warehouseReceipts'
        ])->findOrFail($id);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $shipment
            ]);
        }

        return redirect()->route('truck-shipments.edit', $shipment->id);
    }

    public function getContainers($id)
    {
        $shipment = TruckShipment::findOrFail($id);
        $containers = $shipment->containers()->with('containerType')->get();
        return response()->json($containers);
    }

    public function storeContainer(Request $request, $id)
    {
        $shipment = TruckShipment::findOrFail($id);
        $data = $request->validate([
            'container_no' => 'nullable|string|max:100',
            'tp_sz' => 'nullable|string|max:50',
            'container_type_id' => 'nullable|integer',
            'seal_no' => 'nullable|string|max:100',
            'pickup_no' => 'nullable|string|max:100',
            'pkg' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'measurement' => 'nullable|numeric',
            'lfd' => 'nullable|date',
            'appointment' => 'nullable|date',
            'pickup_date' => 'nullable|date',
            'empty_return_date' => 'nullable|date',
            'pier_pass' => 'nullable|string|max:100',
            'po_no' => 'nullable|string|max:100',
        ]);

        $container = $shipment->containers()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Container added successfully',
            'container' => $container->load('containerType')
        ], 201);
    }

    public function updateContainer(Request $request, $id, $containerId)
    {
        $shipment = TruckShipment::findOrFail($id);
        $container = $shipment->containers()->findOrFail($containerId);

        $data = $request->validate([
            'container_no' => 'nullable|string|max:100',
            'tp_sz' => 'nullable|string|max:50',
            'container_type_id' => 'nullable|integer',
            'seal_no' => 'nullable|string|max:100',
            'pickup_no' => 'nullable|string|max:100',
            'pkg' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'measurement' => 'nullable|numeric',
            'lfd' => 'nullable|date',
            'appointment' => 'nullable|date',
            'pickup_date' => 'nullable|date',
            'empty_return_date' => 'nullable|date',
            'pier_pass' => 'nullable|string|max:100',
            'po_no' => 'nullable|string|max:100',
        ]);

        $container->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Container updated successfully',
            'container' => $container->load('containerType')
        ]);
    }

    public function deleteContainer($id, $containerId)
    {
        $shipment = TruckShipment::findOrFail($id);
        $container = $shipment->containers()->findOrFail($containerId);
        $container->delete();

        return response()->json([
            'success' => true,
            'message' => 'Container deleted successfully'
        ]);
    }

    public function getCommodities($id)
    {
        $shipment = TruckShipment::findOrFail($id);
        $commodities = $shipment->commodities()->get();
        return response()->json($commodities);
    }

    public function storeCommodity(Request $request, $id)
    {
        $shipment = TruckShipment::findOrFail($id);
        $data = $request->validate([
            'container_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'hts_code' => 'nullable|string|max:100',
            'po_no' => 'nullable|string|max:100',
            'pkg_qty' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'cbm' => 'nullable|numeric',
        ]);

        $commodity = $shipment->commodities()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Commodity added successfully',
            'commodity' => $commodity
        ], 201);
    }

    public function updateCommodity(Request $request, $id, $commodityId)
    {
        $shipment = TruckShipment::findOrFail($id);
        $commodity = $shipment->commodities()->findOrFail($commodityId);

        $data = $request->validate([
            'container_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'hts_code' => 'nullable|string|max:100',
            'po_no' => 'nullable|string|max:100',
            'pkg_qty' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'cbm' => 'nullable|numeric',
        ]);

        $commodity->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Commodity updated successfully',
            'commodity' => $commodity
        ]);
    }

    public function deleteCommodity($id, $commodityId)
    {
        $shipment = TruckShipment::findOrFail($id);
        $commodity = $shipment->commodities()->findOrFail($commodityId);
        $commodity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Commodity deleted successfully'
        ]);
    }

    public function linkWarehouseReceipts(Request $request, $id)
    {
        $shipment = TruckShipment::findOrFail($id);
        $receiptIds = $request->input('receipt_ids', []);

        if (!empty($receiptIds)) {
            WarehouseReceipt::whereIn('id', $receiptIds)->update(['truck_shipment_id' => $shipment->id]);
        }

        $totals = WarehouseReceiptItem::whereIn('warehouse_receipt_id', $receiptIds)
            ->selectRaw('COALESCE(SUM(pkg_qty), 0) as total_pkg, COALESCE(SUM(weight_kg), 0) as total_weight, COALESCE(SUM(volume_cbm), 0) as total_cbm')
            ->first();

        return response()->json([
            'success' => true,
            'message' => count($receiptIds) . ' warehouse receipt(s) linked successfully',
            'totals' => [
                'pkg' => (float) ($totals->total_pkg ?? 0),
                'weight' => (float) ($totals->total_weight ?? 0),
                'cbm' => (float) ($totals->total_cbm ?? 0),
            ]
        ]);
    }

    public function createAndLinkReceipt(Request $request, $id)
    {
        $shipment = TruckShipment::findOrFail($id);

        $validated = $request->validate([
            'warehouse_id' => 'required',
            'receive_date' => 'required',
            'customer_id' => 'nullable',
            'shipper_id' => 'nullable',
            'tracking_no' => 'nullable|string',
            'carrier_name' => 'nullable|string',
            'cargo_type' => 'nullable|string',
            'po_no' => 'nullable|string',
        ]);

        $receiptData = array_merge($request->all(), [
            'truck_shipment_id' => $shipment->id,
            'receipt_no' => $request->receipt_no ?: 'WR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'receipt_date' => $request->receive_date ? date('Y-m-d H:i:s', strtotime($request->receive_date)) : now(),
            'company_id' => $shipment->company_id,
        ]);

        $receipt = WarehouseReceipt::create($receiptData);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse receipt created and linked successfully',
            'receipt' => $receipt,
            'totals' => [
                'pkg' => 0,
                'weight' => 0,
                'cbm' => 0,
            ]
        ], 201);
    }
}
