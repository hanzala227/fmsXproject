<?php

namespace App\Http\Controllers;

use App\Models\AirImport;
use App\Models\AirImportHbl;
use App\Models\AirImportContainer;
use App\Models\Office;
use App\Models\Port;
use App\Models\TradePartner;
use App\Models\ContainerType;
use App\Models\Currency;
use App\Models\Charge;
use App\Models\ShipmentStatusLog;
use App\Services\AirImportService;
use App\Services\ShipmentMemoAutoPopulationService;
use App\Http\Requests\StoreAirImportRequest;
use App\Http\Requests\UpdateAirImportRequest;
use Illuminate\Http\Request;

class AirImportController extends Controller
{
    protected $airImportService;

    public function __construct(AirImportService $service)
    {
        $this->airImportService = $service;
    }

    public function index(Request $request)
    {
        $query = AirImport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'forwardingAgent', 'overseaAgent',
            'acctCarrier', 'packageUnit',
            'hbls', 'dmSalesPerson', 'shipper_rel',
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mawb_no', 'like', "%{$search}%")
                  ->orWhere('flight_no', 'like', "%{$search}%");
            });
        }
        if ($request->filled('filter_file_no')) {
            $query->where('file_no', 'like', '%' . $request->filter_file_no . '%');
        }
        if ($request->filled('filter_mawb')) {
            $query->where('mawb_no', 'like', '%' . $request->filter_mawb . '%');
        }
        if ($request->filled('filter_agent')) {
            $query->whereHas('overseaAgent', fn($q) => $q->where('name', 'like', '%' . $request->filter_agent . '%'));
        }
        if ($request->filled('filter_shipper')) {
            $query->whereHas('shipper_rel', fn($q) => $q->where('name', 'like', '%' . $request->filter_shipper . '%'));
        }
        if ($request->filled('filter_hawb')) {
            $query->whereHas('hbls', fn($q) => $q->where('hawb_no', 'like', '%' . $request->filter_hawb . '%'));
        }

        $shipments = $query->latest()->paginate(20)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('air-import.partials.list-rows', compact('shipments'))->render(),
                'pagination' => view('vendor.pagination.custom', ['paginator' => $shipments])->render(),
                'first' => $shipments->firstItem() ?? 0,
                'last' => $shipments->lastItem() ?? 0,
                'total' => $shipments->total(),
            ]);
        }

        return view('air-import.list', compact('shipments', 'users'));
    }

    public function myShipmentList(Request $request)
    {
        $query = AirImport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'forwardingAgent', 'overseaAgent',
            'acctCarrier', 'packageUnit',
            'hbls', 'dmSalesPerson', 'shipper_rel',
        ]);

        if (auth()->check()) {
            $userId = auth()->id();
            $query->where(function($q) use ($userId) {
                $q->where('op_id', $userId)
                  ->orWhere('dm_sales_person_id', $userId)
                  ->orWhereHas('hbls', fn($hq) => $hq->where('sales_person_id', $userId)->orWhere('op_id', $userId));
            });
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mawb_no', 'like', "%{$search}%")
                  ->orWhere('flight_no', 'like', "%{$search}%");
            });
        }
        if ($request->filled('filter_file_no')) {
            $query->where('file_no', 'like', '%' . $request->filter_file_no . '%');
        }
        if ($request->filled('filter_mawb')) {
            $query->where('mawb_no', 'like', '%' . $request->filter_mawb . '%');
        }
        if ($request->filled('filter_agent')) {
            $query->whereHas('overseaAgent', fn($q) => $q->where('name', 'like', '%' . $request->filter_agent . '%'));
        }
        if ($request->filled('filter_shipper')) {
            $query->whereHas('shipper_rel', fn($q) => $q->where('name', 'like', '%' . $request->filter_shipper . '%'));
        }
        if ($request->filled('filter_hawb')) {
            $query->whereHas('hbls', fn($q) => $q->where('hawb_no', 'like', '%' . $request->filter_hawb . '%'));
        }

        $shipments = $query->latest()->paginate(20)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('air-import.partials.my-shipment-rows', compact('shipments'))->render(),
                'pagination' => view('vendor.pagination.custom', ['paginator' => $shipments])->render(),
                'first' => $shipments->firstItem() ?? 0,
                'last' => $shipments->lastItem() ?? 0,
                'total' => $shipments->total(),
            ]);
        }

        return view('air-import.my-shipment-list', compact('shipments', 'users'));
    }

    public function create(Request $request)
    {
        $offices = Office::where('is_active', true)->get();
        $ports = Port::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $packageUnits = \App\Models\PackageUnit::all();
        $containerTypes = ContainerType::all();
        $incoterms = \App\Models\Incoterm::all();
        $serviceTerms = \App\Models\ServiceTerm::all();
        $currencies = Currency::all();
        
        // Separate trade partners by type for easier use in dropdowns
        $allAgents = TradePartner::orderBy('name')->get();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->orderBy('name')->get();
        $customers = TradePartner::whereIn('type', ['CS', 'CUSTOMER', 'CLIENT'])->orderBy('name')->get();
        $agents = TradePartner::whereIn('type', ['AG', 'AGENT', 'OA'])->orderBy('name')->get();
        $truckers = TradePartner::whereIn('type', ['TR', 'TRUCKER'])->orderBy('name')->get();
        $brokers = TradePartner::whereIn('type', ['CB', 'BROKER'])->orderBy('name')->get();
        $forwarders = TradePartner::whereIn('type', ['FW', 'FORWARDER'])->orderBy('name')->get();
        $coloaders = TradePartner::whereIn('type', ['CL', 'COLOADER'])->orderBy('name')->get();
        
        // Fallback to all if type-specific lists are empty
        if ($carriers->isEmpty()) $carriers = $allAgents;
        if ($customers->isEmpty()) $customers = $allAgents;
        if ($agents->isEmpty()) $agents = $allAgents;
        if ($truckers->isEmpty()) $truckers = $allAgents;
        if ($brokers->isEmpty()) $brokers = $allAgents;
        if ($forwarders->isEmpty()) $forwarders = $allAgents;
        if ($coloaders->isEmpty()) $coloaders = $allAgents;
        
        $page = $request->route()->getName();
        $quotations = \App\Models\Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'items.currency'])->forModule('Air Import')->latest()->get();
        
        $chargesData = collect();
        
        return view('air-import.index', compact(
            'offices', 'ports', 'users', 'packageUnits', 
            'containerTypes', 'incoterms', 'serviceTerms', 'currencies',
            'allAgents', 'carriers', 'customers', 'agents', 'truckers', 'brokers', 'forwarders', 'coloaders',
            'page', 'quotations', 'chargesData'
        ));
    }

    public function store(StoreAirImportRequest $request)
    {
        $shipment = $this->airImportService->store($request->validated());

        return redirect()->route('air-import.edit', $shipment->id)
            ->with('success', 'Air Import Shipment created successfully.');
    }

    public function edit(AirImport $airImport)
    {
        $airImport->load([
            'hbls.customer', 'hbls.shipper', 'hbls.consignee',
            'charges.currency', 'documents', 'containers',
            'statusLogs.user', 'office', 'carrier', 'overseaAgent',
            'depPort', 'dstPort', 'operator', 'packageUnit',
            'forwardingAgent', 'acctCarrier',
            'dmCustomer', 'dmShipper', 'dmConsignee', 'dmNotify', 'dmBillTo', 'dmSalesPerson',
        ]);
        // Format charge data for frontend compatibility
        $airImport->charges = $airImport->charges->map(function($charge) use ($airImport) {
            $data = $charge->toArray();
            
            $data['chrg_code'] = $charge->charge_code ?? '';
            $data['charge_name'] = $charge->charge_name ?? '';
            $data['pr'] = $charge->type === 'AP' ? 'Pay' : 'Rec';
            $data['ppc'] = $charge->pc === 'PREPAID' ? 'Prepaid' : 'Colle';
            $data['qty_type'] = $charge->unit ?? 'UNIT';
            $data['inv_no'] = $charge->invoice_no ?? '';
            $data['financial_date'] = $charge->invoice_date ? $charge->invoice_date->format('Y-m-d') : null;
            $data['currency'] = $charge->currency ? $charge->currency->code : '';
            
            if ($charge->party_name) {
                $data['party_name'] = $charge->party_name;
            } else if ($charge->type === 'AP' && $charge->vendor) {
                $data['party_name'] = $charge->vendor->name;
                $data['party_name_id'] = $charge->vendor_id;
            } else if ($charge->type !== 'AP' && $charge->billTo) {
                $data['party_name'] = $charge->billTo->name;
                $data['party_name_id'] = $charge->bill_to_id;
            } else {
                $data['party_name'] = '';
                $data['party_name_id'] = '';
            }
            
            $data['party'] = $charge->party ?: 'Custom'; 
            $data['sal'] = $charge->sal ?: 'Air'; 
            $data['mbl_no'] = $airImport->file_no; 
            $data['eq_bl_no'] = $charge->remark ?? ''; 
            $data['remark'] = $charge->remark ?? false;
            $data['_selected'] = false;

            return (object) $data;
        });
        
        $offices = Office::where('is_active', true)->get();
        $ports = Port::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $packageUnits = \App\Models\PackageUnit::all();
        $containerTypes = ContainerType::all();
        $incoterms = \App\Models\Incoterm::all();
        $serviceTerms = \App\Models\ServiceTerm::all();
        $currencies = Currency::all();
        
        // Separate trade partners by type
        $allAgents = TradePartner::orderBy('name')->get();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->orderBy('name')->get();
        $customers = TradePartner::whereIn('type', ['CS', 'CUSTOMER', 'CLIENT'])->orderBy('name')->get();
        $agents = TradePartner::whereIn('type', ['AG', 'AGENT', 'OA'])->orderBy('name')->get();
        $truckers = TradePartner::whereIn('type', ['TR', 'TRUCKER'])->orderBy('name')->get();
        $brokers = TradePartner::whereIn('type', ['CB', 'BROKER'])->orderBy('name')->get();
        $forwarders = TradePartner::whereIn('type', ['FW', 'FORWARDER'])->orderBy('name')->get();
        $coloaders = TradePartner::whereIn('type', ['CL', 'COLOADER'])->orderBy('name')->get();
        
        // Fallback to all if type-specific lists are empty
        if ($carriers->isEmpty()) $carriers = $allAgents;
        if ($customers->isEmpty()) $customers = $allAgents;
        if ($agents->isEmpty()) $agents = $allAgents;
        if ($truckers->isEmpty()) $truckers = $allAgents;
        if ($brokers->isEmpty()) $brokers = $allAgents;
        if ($forwarders->isEmpty()) $forwarders = $allAgents;
        if ($coloaders->isEmpty()) $coloaders = $allAgents;
        
        $quotations = \App\Models\Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'items.currency'])->forModule('Air Import')->latest()->get();
        
        $chargesData = $airImport->charges->isNotEmpty()
            ? $airImport->charges->map(fn($c) => [
                'id' => $c->id,
                'selected' => false,
                'expanded' => false,
                'party' => $c->party ?? 'Freight',
                'party_name' => $c->party_name ?? ($c->billTo->name ?? ($c->vendor->name ?? '')),
                'party_name_id' => $c->bill_to_id ?? $c->vendor_id ?? '',
                'sal' => $c->sal ?? 'Air',
                'chrg_code' => $c->charge_code,
                'charge_name' => $c->charge_name ?? $c->charge_code,
                'currency' => $c->currency->code ?? 'USD',
                'rate' => $c->rate,
                'qty' => $c->qty,
                'qty_type' => $c->unit ?? 'B/L',
                'pr' => $c->type === 'AP' ? 'Pay' : 'Rec',
                'ppc' => $c->pc === 'PREPAID' ? 'Prepaid' : 'Colle',
                'eq_bl_no' => $c->remark ?? '',
                'vat' => $c->tax_percent ?? 0,
                'roe' => 1.0,
                'inv_no' => $c->invoice_no ?? '',
                'is_invoiced' => $c->is_invoiced,
                'financial_date' => !empty($c->invoice_date) ? (is_string($c->invoice_date) ? substr($c->invoice_date, 0, 10) : $c->invoice_date->format('Y-m-d')) : date('Y-m-d'),
                'seal_no2' => $c->seal_no2 ?? '',
                'pickup_no' => $c->pickup_no ?? '',
                'cprs_no' => $c->cprs_no ?? '',
                'cnru_no' => $c->cnru_no ?? '',
                'it_no' => $c->it_no ?? '',
                'dg' => $c->dg ?? 'No',
                'temp' => $c->temp ?? '',
                'vent' => $c->vent ?? '',
                'storage_start_date' => !empty($c->storage_start_date) ? \Carbon\Carbon::parse($c->storage_start_date)->format('Y-m-d') : '',
                'storage_end_date' => !empty($c->storage_end_date) ? \Carbon\Carbon::parse($c->storage_end_date)->format('Y-m-d') : '',
                'carrier_release' => !empty($c->carrier_release),
                'yard_location' => $c->yard_location ?? '',
                'unload_vessel_date' => !empty($c->unload_vessel_date) ? \Carbon\Carbon::parse($c->unload_vessel_date)->format('Y-m-d') : '',
                'gate_in_date' => !empty($c->gate_in_date) ? \Carbon\Carbon::parse($c->gate_in_date)->format('Y-m-d') : '',
                'rail_start_date' => !empty($c->rail_start_date) ? \Carbon\Carbon::parse($c->rail_start_date)->format('Y-m-d') : '',
                'pod_eta_date' => !empty($c->pod_eta_date) ? \Carbon\Carbon::parse($c->pod_eta_date)->format('Y-m-d') : '',
                'available_pickup' => !empty($c->available_pickup),
                'weight_lb' => $c->weight_lb ?? '',
                'appt_date' => !empty($c->appt_date) ? \Carbon\Carbon::parse($c->appt_date)->format('Y-m-d') : '',
                'trucker_id' => $c->trucker_id ?? '',
                'pickup_date' => !empty($c->pickup_date) ? \Carbon\Carbon::parse($c->pickup_date)->format('Y-m-d') : '',
                'gate_out_date' => !empty($c->gate_out_date) ? \Carbon\Carbon::parse($c->gate_out_date)->format('Y-m-d') : '',
                'fdest_eta_date' => !empty($c->fdest_eta_date) ? \Carbon\Carbon::parse($c->fdest_eta_date)->format('Y-m-d') : '',
                'eta_door_date' => !empty($c->eta_door_date) ? \Carbon\Carbon::parse($c->eta_door_date)->format('Y-m-d') : '',
                'ata_door_date' => !empty($c->ata_door_date) ? \Carbon\Carbon::parse($c->ata_door_date)->format('Y-m-d') : '',
                'measurement_cft' => $c->measurement_cft ?? '',
                'remarks' => $c->container_remarks ?? '',
                'internal_remarks' => $c->internal_remarks ?? '',
                'empty_confirmed_date' => !empty($c->empty_confirmed_date) ? \Carbon\Carbon::parse($c->empty_confirmed_date)->format('Y-m-d') : '',
                'empty_return_date' => !empty($c->empty_return_date) ? \Carbon\Carbon::parse($c->empty_return_date)->format('Y-m-d') : '',
                'complete' => (bool)$c->complete,
            ])
            : collect();
        
        return view('air-import.index', compact(
            'airImport', 'offices', 'ports', 'users', 'packageUnits',
            'containerTypes', 'incoterms', 'serviceTerms', 'currencies',
            'allAgents', 'carriers', 'customers', 'agents', 'truckers', 'brokers', 'forwarders', 'coloaders',
            'quotations', 'chargesData'
        ));
    }

    public function update(UpdateAirImportRequest $request, AirImport $airImport)
    {
        $this->airImportService->update($airImport, $request->validated());

        return back()->with('success', 'Air Import Shipment updated successfully.');
    }

    public function mblList()
    {
        $shipments = AirImport::with(['office', 'operator', 'carrier'])
            ->latest()
            ->paginate(20);
        $users = \App\Models\User::all();
        return view('air-import.mbl-list', compact('shipments', 'users'));
    }

    public function hblList(Request $request)
    {
        $query = AirImportHbl::with([
            'airImport.depPort', 'airImport.dstPort', 'airImport.office', 'airImport.carrier',
            'customer', 'shipper', 'consignee', 'salesPerson', 'op',
        ]);

        // Global search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb_no', 'like', "%{$search}%")
                  ->orWhereHas('airImport', fn($aq) => $aq->where('file_no', 'like', "%{$search}%")
                      ->orWhere('mawb_no', 'like', "%{$search}%"))
                  ->orWhereHas('shipper', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('consignee', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('customer', fn($tq) => $tq->where('name', 'like', "%{$search}%"));
            });
        }

        // Per-column filters
        if ($request->filled('filter_hawb')) {
            $query->where('hawb_no', 'like', "%{$request->filter_hawb}%");
        }
        if ($request->filled('filter_file_no')) {
            $query->whereHas('airImport', fn($aq) => $aq->where('file_no', 'like', "%{$request->filter_file_no}%"));
        }
        if ($request->filled('filter_customer')) {
            $query->whereHas('customer', fn($tq) => $tq->where('name', 'like', "%{$request->filter_customer}%"));
        }
        if ($request->filled('filter_shipper')) {
            $query->whereHas('shipper', fn($tq) => $tq->where('name', 'like', "%{$request->filter_shipper}%"));
        }
        if ($request->filled('filter_consignee')) {
            $query->whereHas('consignee', fn($tq) => $tq->where('name', 'like', "%{$request->filter_consignee}%"));
        }
        if ($request->filled('filter_dep')) {
            $query->whereHas('airImport.depPort', fn($pq) => $pq->where('name', 'like', "%{$request->filter_dep}%"));
        }
        if ($request->filled('filter_dst')) {
            $query->whereHas('airImport.dstPort', fn($pq) => $pq->where('name', 'like', "%{$request->filter_dst}%"));
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['hawb_no', 'pkg_qty', 'gross_weight', 'chargeable_weight', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $hbls = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('air-import.partials.hbl-list-rows', compact('hbls'))->render(),
                'pagination' => view('vendor.pagination.custom', ['paginator' => $hbls])->render(),
                'first' => $hbls->firstItem() ?? 0,
                'last' => $hbls->lastItem() ?? 0,
                'total' => $hbls->total(),
            ]);
        }

        return view('air-import.hbl-list', compact('hbls', 'users'));
    }

    public function destroy(AirImport $airImport)
    {
        $airImport->delete();
        return redirect()->route('air-import.index')->with('success', 'Shipment deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_imports,id']);
        AirImport::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) deleted.']);
    }

    public function updateColor(Request $request, AirImport $airImport)
    {
        try {
            $request->validate(['color' => 'nullable|string|max:20']);
            $airImport->update(['color' => $request->color]);
            
            return response()->json([
                'success' => true,
                'message' => 'Color updated successfully',
                'color' => $airImport->color
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update color: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkChangeOp(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_imports,id', 'op_id' => 'required|exists:users,id']);
        AirImport::whereIn('id', $data['ids'])->update(['op_id' => $data['op_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' shipment(s) OP changed.']);
    }

    public function bulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_imports,id']);
        AirImport::whereIn('id', $request->ids)->update(['is_blocked' => true]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) blocked.']);
    }

    public function bulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_imports,id']);
        AirImport::whereIn('id', $request->ids)->update(['is_blocked' => false]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) unblocked.']);
    }

    public function bulkChangeSales(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_imports,id', 'sales_person_id' => 'required|exists:users,id']);
        AirImport::whereIn('id', $data['ids'])->update(['dm_sales_person_id' => $data['sales_person_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' shipment(s) sales changed.']);
    }

    // ==================== HBL BULK OPERATIONS ====================

    public function hblBulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_import_hbls,id']);
        AirImportHbl::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) deleted.']);
    }

    public function hblBulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_import_hbls,id']);
        AirImportHbl::whereIn('id', $request->ids)->update(['is_blocked' => true]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) blocked.']);
    }

    public function hblBulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_import_hbls,id']);
        AirImportHbl::whereIn('id', $request->ids)->update(['is_blocked' => false]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) unblocked.']);
    }

    public function hblBulkChangeOp(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_import_hbls,id', 'op_id' => 'required|exists:users,id']);
        AirImportHbl::whereIn('id', $data['ids'])->update(['op_id' => $data['op_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' HBL(s) OP changed.']);
    }

    public function hblBulkChangeSales(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_import_hbls,id', 'sales_person_id' => 'required|exists:users,id']);
        AirImportHbl::whereIn('id', $data['ids'])->update(['sales_person_id' => $data['sales_person_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' HBL(s) sales changed.']);
    }

    public function exportCsv()
    {
        $shipments = AirImport::with(['office', 'operator', 'carrier', 'depPort', 'dstPort', 'hbls'])->latest()->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="air-import-shipments.csv"',
        ];
        $callback = function () use ($shipments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['File No', 'MAWB No', 'Carrier', 'ETD', 'ETA', 'Departure', 'Destination', 'Oversea Agent', 'Flight No']);
            foreach ($shipments as $s) {
                fputcsv($file, [
                    $s->file_no, $s->mawb_no, $s->carrier->name ?? '',
                    $s->etd ? $s->etd->format('Y-m-d H:i') : '',
                    $s->eta ? $s->eta->format('Y-m-d H:i') : '',
                    $s->depPort->name ?? '', $s->dstPort->name ?? '',
                    $s->overseaAgent->name ?? '', $s->flight_no ?? '',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function hblExportCsv(Request $request)
    {
        $query = AirImportHbl::with([
            'airImport.depPort', 'airImport.dstPort', 'airImport.carrier',
            'customer', 'shipper', 'consignee', 'salesPerson', 'op',
        ]);

        // Apply same filters as hblList
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb_no', 'like', "%{$search}%")
                  ->orWhereHas('airImport', fn($aq) => $aq->where('file_no', 'like', "%{$search}%")
                      ->orWhere('mawb_no', 'like', "%{$search}%"))
                  ->orWhereHas('shipper', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('consignee', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('customer', fn($tq) => $tq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('filter_hawb')) {
            $query->where('hawb_no', 'like', "%{$request->filter_hawb}%");
        }
        if ($request->filled('filter_file_no')) {
            $query->whereHas('airImport', fn($aq) => $aq->where('file_no', 'like', "%{$request->filter_file_no}%"));
        }
        if ($request->filled('filter_customer')) {
            $query->whereHas('customer', fn($tq) => $tq->where('name', 'like', "%{$request->filter_customer}%"));
        }
        if ($request->filled('filter_shipper')) {
            $query->whereHas('shipper', fn($tq) => $tq->where('name', 'like', "%{$request->filter_shipper}%"));
        }
        if ($request->filled('filter_consignee')) {
            $query->whereHas('consignee', fn($tq) => $tq->where('name', 'like', "%{$request->filter_consignee}%"));
        }
        if ($request->filled('filter_dep')) {
            $query->whereHas('airImport.depPort', fn($pq) => $pq->where('name', 'like', "%{$request->filter_dep}%"));
        }
        if ($request->filled('filter_dst')) {
            $query->whereHas('airImport.dstPort', fn($pq) => $pq->where('name', 'like', "%{$request->filter_dst}%"));
        }

        $hbls = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="air-import-hbls-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($hbls) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'HAWB No', 'File No', 'MAWB No', 'Customer', 'Shipper', 'Consignee',
                'Package Qty', 'G. Weight (KG)', 'C. Weight (KG)',
                'ETD', 'ETA', 'Departure', 'Destination',
                'Sales', 'OP', 'Created At'
            ]);
            foreach ($hbls as $h) {
                fputcsv($file, [
                    $h->hawb_no,
                    $h->airImport->file_no ?? '--',
                    $h->airImport->mawb_no ?? '--',
                    $h->customer->name ?? '--',
                    $h->shipper->name ?? '--',
                    $h->consignee->name ?? '--',
                    $h->pkg_qty ?? '--',
                    $h->gross_weight ? number_format($h->gross_weight, 2) : '--',
                    $h->chargeable_weight ? number_format($h->chargeable_weight, 2) : '--',
                    $h->airImport->etd ? $h->airImport->etd->format('m-d-Y') : '--',
                    $h->airImport->eta ? $h->airImport->eta->format('m-d-Y') : '--',
                    $h->airImport->depPort->name ?? '--',
                    $h->airImport->dstPort->name ?? '--',
                    $h->salesPerson->name ?? '--',
                    $h->op->name ?? '--',
                    $h->created_at ? $h->created_at->format('m-d-Y H:i') : '--',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==================== CHARGES AJAX ENDPOINTS ====================

    public function addCharge(Request $request, AirImport $airImport)
    {
        $charge = $this->airImportService->createCharge($airImport, $request->all());
        $charge->load('currency');
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function updateCharge(Request $request, $chargeId)
    {
        $charge = \App\Models\Charge::findOrFail($chargeId);
        $this->airImportService->updateCharge($charge, $request->all());
        $charge->load('currency');
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function deleteCharge($chargeId)
    {
        $charge = \App\Models\Charge::findOrFail($chargeId);
        $charge->delete();
        return response()->json(['success' => true]);
    }

    public function deleteAllCharges(AirImport $airImport)
    {
        $airImport->charges()->delete();
        return response()->json(['success' => true]);
    }

    public function getCharges(AirImport $airImport)
    {
        $charges = $airImport->charges()->with('currency')->latest()->get();
        return response()->json($charges);
    }

    // ==================== CONTAINERS AJAX ENDPOINTS ====================

    public function addContainer(Request $request, AirImport $airImport)
    {
        $container = $airImport->containers()->create($request->validate([
            'container_no' => 'nullable|string',
            'pp_ctf' => 'nullable|string',
            'container_type' => 'nullable|string',
            'seal_no' => 'nullable|string',
            'seal_no2' => 'nullable|string',
            'lfd' => 'nullable|date',
            'fdd' => 'nullable|date',
            'pkg_qty' => 'nullable|numeric',
            'weight_kg' => 'nullable|numeric',
            'measure_cbm' => 'nullable|numeric',
        ]));
        return response()->json(['success' => true, 'container' => $container]);
    }

    public function updateContainer(Request $request, AirImportContainer $container)
    {
        $container->update($request->all());
        return response()->json(['success' => true, 'container' => $container]);
    }

    public function deleteContainer(AirImportContainer $container)
    {
        $container->delete();
        return response()->json(['success' => true]);
    }

    // ==================== FILING AJAX ENDPOINTS ====================

    public function updateFiling(Request $request, AirImport $airImport)
    {
        $data = $request->validate([
            'shipper_id' => 'nullable|exists:trade_partners,id',
            'consignee_id' => 'nullable|exists:trade_partners,id',
            'bill_to_id' => 'nullable|exists:trade_partners,id',
            'oversea_agent_id' => 'nullable|exists:trade_partners,id',
            'notify_id' => 'nullable|exists:trade_partners,id',
            'trucker_id' => 'nullable|exists:trade_partners,id',
            'pod_eta' => 'nullable|date',
            'ship_mode' => 'nullable|string',
            'go_date' => 'nullable|date',
            'sub_bl_no' => 'nullable|string',
            'final_destination_id' => 'nullable|exists:ports,id',
            'delivery_location_id' => 'nullable|exists:trade_partners,id',
            'final_eta' => 'nullable|date',
            'last_free_day' => 'nullable|date',
            'storage_start_date' => 'nullable|date',
            'internal_remark' => 'nullable|string',
            'cy_cfs_loc' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'ams_no' => 'nullable|string',
            'isf_no' => 'nullable|string',
            'isf_matched_date' => 'nullable|date',
            'isf_3rd_party' => 'nullable|boolean',
            'sales_type' => 'nullable|string',
            'c_released_date' => 'nullable|date',
            'entry_no' => 'nullable|string',
            'ror' => 'nullable|boolean',
            'released_by_id' => 'nullable|exists:users,id',
            'do_sent' => 'nullable|boolean',
            'do_sent_date' => 'nullable|date',
            'entry_doc_sent_date' => 'nullable|date',
            'hold' => 'nullable|boolean',
            'door_delivered_date' => 'nullable|date',
            'class_of_entry' => 'nullable|string',
            'cargo_released_to' => 'nullable|string',
            'ship_type' => 'nullable|string',
            'freight_term' => 'nullable|string',
            'incoterm_id' => 'nullable|string',
            'service_term_from' => 'nullable|string',
            'service_term_to' => 'nullable|string',
            'cargo_type' => 'nullable|string',
            // Direct Master fields
            'is_direct_master' => 'nullable|boolean',
            'dm_customer_id' => 'nullable|exists:trade_partners,id',
            'dm_shipper_id' => 'nullable|exists:trade_partners,id',
            'dm_consignee_id' => 'nullable|exists:trade_partners,id',
            'dm_notify_id' => 'nullable|exists:trade_partners,id',
            'dm_bill_to_id' => 'nullable|exists:trade_partners,id',
            'dm_sales_person_id' => 'nullable|exists:users,id',
        ]);

        $airImport->update($data);

        ShipmentStatusLog::create([
            'shipment_type' => AirImport::class,
            'shipment_id' => $airImport->id,
            'status_code' => 'FILING_UPDATED',
            'status_name' => 'Filing Updated',
            'details' => 'Filing details updated.',
            'user_id' => auth()->id(),
            'event_time' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Filing details updated successfully.']);
    }

    // ==================== HISTORY AJAX ENDPOINTS ====================

    public function getHistory(AirImport $airImport)
    {
        $history = $airImport->statusLogs()->with('user')->latest()->get();
        return response()->json($history);
    }

    // ==================== DOCUMENT AJAX ENDPOINTS ====================

    public function uploadDocument(Request $request, AirImport $airImport)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('shipments/documents', 'public');

        $doc = $airImport->documents()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'document' => $doc]);
    }

    public function deleteDocument($documentId)
    {
        $doc = \App\Models\Document::findOrFail($documentId);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return response()->json(['success' => true]);
    }

    public function downloadDocument($documentId)
    {
        $doc = \App\Models\Document::findOrFail($documentId);
        return \Illuminate\Support\Facades\Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    public function createInvoiceFromCharges(Request $request, $airImportId, \App\Services\InvoiceGenerationService $invoiceService)
    {
        AirImport::findOrFail($airImportId);

        $query = Charge::where('chargeable_type', 'App\Models\AirImport')
            ->where('chargeable_id', $airImportId);

        if ($request->has('ids') && is_array($request->ids) && count($request->ids) > 0) {
            $query->whereIn('id', $request->ids);
        } else {
            $query->where(function($q) {
                $q->where('is_invoiced', false)->orWhereNull('invoice_no');
            });
        }

        $charges = $query->get();

        if ($charges->count() == 0) {
            if ($request->has('ids') && is_array($request->ids) && count($request->ids) > 0) {
                $alreadyInvoiced = Charge::where('chargeable_type', 'App\Models\AirImport')
                    ->where('chargeable_id', $airImportId)
                    ->whereIn('id', $request->ids)
                    ->where('is_invoiced', true)
                    ->pluck('invoice_no')
                    ->first();
                if ($alreadyInvoiced) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected charge(s) are already invoiced (' . $alreadyInvoiced . ').'
                    ], 400);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Selected charge(s) not found in database. Please click "Save" on the shipment first.'
                ], 400);
            }

            $allChargesCount = Charge::where('chargeable_type', 'App\Models\AirImport')->where('chargeable_id', $airImportId)->count();
            if ($allChargesCount > 0) {
                $existingInv = Charge::where('chargeable_type', 'App\Models\AirImport')->where('chargeable_id', $airImportId)->whereNotNull('invoice_no')->pluck('invoice_no')->first();
                return response()->json([
                    'success' => true,
                    'message' => 'Charges are already invoiced (' . ($existingInv ?? 'Invoiced') . '). Opening Freight Invoice...',
                    'invoice_no' => $existingInv ?? 'INV-EXISTING',
                    'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'air-import', 'id' => $airImportId])
                ]);
            }
            return response()->json(['success' => false, 'message' => 'No charges found for this shipment. Please add charges first.'], 400);
        }

        $companyId = auth()->user()->company_id ?? 1;
        $result = $invoiceService->generateInvoicesFromCharges(
            $companyId,
            'App\Models\AirImport',
            $airImportId,
            'Import',
            'Air',
            $charges
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        $invNo = implode(', ', $result['invoices']);

        // === AUTO-POPULATION INTEGRATION ===
        $shipment = AirImport::with([
            'forwardingAgent', 'carrier', 'dmShipper', 'dmConsignee', 'dmNotify', 
            'deliveryAgent', 'salesPerson', 'dmCustomer', 'hbls'
        ])->find($airImportId);

        $autoPopService = new ShipmentMemoAutoPopulationService();
        $autoPopulatedData = [
            'master_bl' => $autoPopService->getAutoPopulatedData('air-import', $shipment, 'master_bl'),
            'house_bl' => $autoPopService->getAutoPopulatedData('air-import', $shipment, 'house_bl'),
            'enabled_fields' => [
                'master_bl' => $autoPopService->getEnabledFields('air-import', 'master_bl'),
                'house_bl' => $autoPopService->getEnabledFields('air-import', 'house_bl'),
            ],
        ];

        return response()->json([
            'success' => true,
            'invoice_no' => $invNo,
            'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'air-import', 'id' => $airImportId]),
            'auto_populated_data' => $autoPopulatedData
        ]);
    }

    public function profitSummaryView($id, Request $request)
    {
        $shipment = AirImport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'hbls', 'charges.currency'
        ])->findOrFail($id);

        return view('air-import.profit-summary', compact('shipment'));
    }

    public function profitDetailView($id, Request $request)
    {
        $shipment = AirImport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'hbls', 'charges.currency'
        ])->findOrFail($id);

        return view('air-import.profit-detail', compact('shipment'));
    }
}
