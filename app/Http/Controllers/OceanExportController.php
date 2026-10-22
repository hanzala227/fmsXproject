<?php

namespace App\Http\Controllers;

use App\Models\OceanExport;
use App\Models\OceanExportHbl;
use App\Models\Office;
use App\Models\Port;
use App\Models\Vessel;
use App\Models\TradePartner;
use App\Models\Currency;
use App\Models\ServiceTerm;
use App\Models\Charge;
use App\Models\Quotation;
use App\Models\User;
use App\Models\OceanBooking;
use App\Services\OceanExportService;
use App\Services\ShipmentMemoAutoPopulationService;
use App\Http\Requests\StoreOceanExportRequest;
use App\Http\Requests\UpdateOceanExportRequest;
use Illuminate\Http\Request;

class OceanExportController extends Controller
{
    protected $oceanExportService;

    public function __construct(OceanExportService $service)
    {
        $this->oceanExportService = $service;
    }

    public function index(Request $request)
    {
        $query = OceanExport::with([
            'office', 'operator', 'carrier', 'vessel',
            'portOfLoading', 'portOfDischarge',
            'dmCustomer', 'dmConsignee', 'overseaAgent',
            'containers.containerType', 'hbls',
        ]);

        $this->applyFiltersToQuery($query, $request);

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['file_no', 'mbl_no', 'etd', 'eta', 'created_at', 'post_date'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $shipments = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $html = view('ocean-export.partials.export-list-rows', compact('shipments'))->render();
                $pagination = view('vendor.pagination.custom', ['paginator' => $shipments])->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'pagination' => $pagination,
                    'first' => $shipments->firstItem() ?? 0,
                    'last' => $shipments->lastItem() ?? 0,
                    'total' => $shipments->total(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                ], 500);
            }
        }

        $offices = Office::where('is_active', true)->get();
        $users = \App\Models\User::all();
        $agents = TradePartner::all();
        $ports = Port::latest()->take(10)->get();

        return view('ocean-export.list', compact('shipments', 'offices', 'users', 'agents', 'ports'));
    }

    private function applyFiltersToQuery($query, Request $request)
    {
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mbl_no', 'like', "%{$search}%")
                  ->orWhere('sub_bl_no', 'like', "%{$search}%")
                  ->orWhere('voyage', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_file_no')) {
            $query->where('file_no', 'like', "%{$request->filter_file_no}%");
        }
        if ($request->filled('filter_mbl_no')) {
            $query->where('mbl_no', 'like', "%{$request->filter_mbl_no}%");
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('office', fn($q) => $q->where('code', 'like', "%{$request->filter_office}%")->orWhere('name', 'like', "%{$request->filter_office}%"));
        }
        if ($request->filled('filter_consignee')) {
            $query->whereHas('dmConsignee', fn($q) => $q->where('name', 'like', "%{$request->filter_consignee}%"));
        }
        if ($request->filled('filter_etd')) {
            $query->where('etd', 'like', "%{$request->filter_etd}%");
        }
        if ($request->filled('filter_eta')) {
            $query->where('eta', 'like', "%{$request->filter_eta}%");
        }
        if ($request->filled('filter_pol')) {
            $query->whereHas('portOfLoading', fn($q) => $q->where('name', 'like', "%{$request->filter_pol}%"));
        }
        if ($request->filled('filter_pod')) {
            $query->whereHas('portOfDischarge', fn($q) => $q->where('name', 'like', "%{$request->filter_pod}%"));
        }
        if ($request->filled('filter_customer')) {
            $query->whereHas('dmCustomer', fn($q) => $q->where('name', 'like', "%{$request->filter_customer}%"));
        }
        if ($request->filled('filter_sales')) {
            $query->whereHas('salesPerson', fn($q) => $q->where('name', 'like', "%{$request->filter_sales}%"));
        }

        if ($request->filled('office_id')) $query->where('office_id', $request->office_id);
        if ($request->filled('op_id')) $query->where('op_id', $request->op_id);
        if ($request->filled('carrier_id')) $query->where('carrier_id', $request->carrier_id);
        if ($request->filled('pol_id')) $query->where('pol_id', $request->pol_id);
        if ($request->filled('pod_id')) $query->where('pod_id', $request->pod_id);
        if ($request->filled('dm_customer_id')) $query->where('dm_customer_id', $request->dm_customer_id);
        if ($request->filled('etd_from')) $query->where('etd', '>=', $request->etd_from);
        if ($request->filled('etd_to')) $query->where('etd', '<=', $request->etd_to);
        if ($request->filled('eta_from')) $query->where('eta', '>=', $request->eta_from);
        if ($request->filled('eta_to')) $query->where('eta', '<=', $request->eta_to);

        return $query;
    }

    public function create(Request $request)
    {
        $offices = Office::where('is_active', true)->get();
        $ports = Port::latest()->take(10)->get();
        $vessels = Vessel::all();
        $agents = TradePartner::all();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->get();
        if ($carriers->isEmpty()) $carriers = $agents;
        $users = \App\Models\User::all();
        $containerTypes = \App\Models\ContainerType::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $incoterms = \App\Models\Incoterm::all();
        $currencies = Currency::all();
        $serviceTerms = ServiceTerm::all();
        
        $page = $request->segment(2);
        $quotations = \App\Models\Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'agent', 'office', 'items.currency'])->forModule('Ocean Export')->latest()->get();
        $history = [];
        $chargesList = [];

        $oceanExport = null;
        if ($request->query('copy')){
            $copyId = $request->query('copy');
            $source = OceanExport::with(['hbls.customer', 'hbls.shipper', 'hbls.consignee', 'containers.containerType', 'charges'])->find($copyId);
            if (!$source) {
                return redirect()->route('ocean-export.create')->with('error', 'Source shipment not found for copy.');
            }
            $oceanExport = $source->replicate();
            $oceanExport->file_no = 'MOE-' . now()->format('ymdHis');
            $oceanExport->mbl_no = null;
            $oceanExport->post_date = now();
            $oceanExport->setRelation('hbls', $source->hbls->map->replicate());
            $oceanExport->setRelation('containers', $source->containers->map->replicate());
            $oceanExport->setRelation('charges', $source->charges);
        }
        
        // Handle HBL copy
        if ($request->query('copy_hbl')) {
            $hblId = $request->query('copy_hbl');
            $shipmentId = $request->query('shipment_id');
            $hbl = OceanExportHbl::with([
                'customer', 'shipper', 'consignee', 'notifyParty', 
                'placeOfDischarge', 'placeOfDelivery', 'finalDestination', 'placeOfReceipt',
                'customsBroker', 'deliveryLocation', 'referredBy', 'cfsLocation',
                'freightReleasedBy', 'salesPerson'
            ])->find($hblId);
            $shipment = OceanExport::with(['hbls', 'containers'])->find($shipmentId);
            
            if (!$hbl || !$shipment) {
                return redirect()->route('ocean-export.create')->with('error', 'Source HBL or shipment not found for copy.');
            }
            
            // Create new shipment based on existing one
            $oceanExport = $shipment->replicate();
            $oceanExport->file_no = 'MOE-' . now()->format('ymdHis');
            $oceanExport->mbl_no = null;
            $oceanExport->post_date = now();
            $oceanExport->exists = false; // Mark as new record
            
            // Copy the specific HBL with all its data
            $newHbl = $hbl->replicate();
            $newHbl->hbl_no = ''; // Clear HBL number so user can enter new one
            $newHbl->exists = false; // Mark as new record
            
            // Preserve the relationships by setting them back
            $newHbl->setRelation('customer', $hbl->customer);
            $newHbl->setRelation('shipper', $hbl->shipper);
            $newHbl->setRelation('consignee', $hbl->consignee);
            $newHbl->setRelation('notifyParty', $hbl->notifyParty);
            $newHbl->setRelation('placeOfDischarge', $hbl->placeOfDischarge);
            $newHbl->setRelation('placeOfDelivery', $hbl->placeOfDelivery);
            $newHbl->setRelation('finalDestination', $hbl->finalDestination);
            $newHbl->setRelation('placeOfReceipt', $hbl->placeOfReceipt);
            $newHbl->setRelation('customsBroker', $hbl->customsBroker);
            $newHbl->setRelation('deliveryLocation', $hbl->deliveryLocation);
            $newHbl->setRelation('referredBy', $hbl->referredBy);
            $newHbl->setRelation('cfsLocation', $hbl->cfsLocation);
            $newHbl->setRelation('freightReleasedBy', $hbl->freightReleasedBy);
            $newHbl->setRelation('salesPerson', $hbl->salesPerson);
            
            $oceanExport->setRelation('hbls', collect([$newHbl]));
            $oceanExport->setRelation('containers', $shipment->containers->map(function($container) {
                $newContainer = $container->replicate();
                $newContainer->exists = false;
                return $newContainer;
            }));
        }
        
        return view('ocean-export.index', compact('offices', 'ports', 'vessels', 'agents', 'carriers', 'users', 'containerTypes', 'packageUnits', 'incoterms', 'currencies', 'serviceTerms', 'page', 'quotations', 'oceanExport', 'history', 'chargesList'));
    }

    private function sanitizeShipmentData(array $data): array
    {
        // 1. Sanitize foreign keys to prevent FK constraint violations
        $foreignKeys = [
            'dm_customer_id', 'dm_shipper_id', 'dm_consignee_id', 'dm_notify_id', 'dm_bill_to_id',
            'forwarding_agent_id', 'oversea_agent_id', 'co_loader_id', 'carrier_id', 'acct_carrier_id',
            'business_referred_by_id', 'cy_location_id', 'cfs_location_id', 'return_location_id', 'trucker_id',
            'office_id', 'op_id', 'vessel_id', 'pol_id', 'pod_id', 'del_id', 'fdest_id', 'receipt_id',
            'service_term_from_id', 'service_term_to_id', 'released_by_id',
        ];
        foreach ($foreignKeys as $key) {
            if (empty($data[$key])) {
                $data[$key] = null;
            } else {
                $modelClass = null;
                if (str_contains($key, 'office')) $modelClass = \App\Models\Office::class;
                elseif (str_contains($key, 'vessel')) $modelClass = \App\Models\Vessel::class;
                elseif (str_contains($key, 'pol') || str_contains($key, 'pod') || str_contains($key, 'del') || str_contains($key, 'fdest') || str_contains($key, 'receipt')) $modelClass = \App\Models\Port::class;
                elseif ($key === 'op_id' || $key === 'released_by_id') $modelClass = \App\Models\User::class;
                else $modelClass = \App\Models\TradePartner::class;

                if (!$modelClass::where('id', $data[$key])->exists()) {
                    $data[$key] = null;
                }
            }
        }

        // 2. Resolve incoterm code to ID (form sends code, DB stores ID)
        if (isset($data['incoterm_id']) && is_string($data['incoterm_id']) && $data['incoterm_id'] !== '') {
            $incoterm = \App\Models\Incoterm::where('code', $data['incoterm_id'])->first();
            $data['incoterm_id'] = $incoterm ? $incoterm->id : null;
        }

        // 3. Resolve HBL incoterms_id code to ID and sanitize HBL foreign keys
        if (isset($data['hbls']) && is_array($data['hbls'])) {
            $hblFkMap = [
                'customer_id' => 'trade_partners', 'shipper_id' => 'trade_partners', 'consignee_id' => 'trade_partners',
                'notify_party_id' => 'trade_partners', 'customs_broker_id' => 'trade_partners',
                'delivery_location_id' => 'trade_partners', 'cfs_location_id' => 'trade_partners',
                'referred_by_id' => 'trade_partners',
                'freight_released_by_id' => 'users', 'sales_person_id' => 'users',
                'pod_id' => 'ports', 'del_id' => 'ports', 'fdest_id' => 'ports', 'receipt_id' => 'ports',
            ];
            foreach ($data['hbls'] as $hIdx => $hbl) {
                // Resolve incoterms_id code to ID for HBL
                if (isset($hbl['incoterms_id']) && is_string($hbl['incoterms_id']) && $hbl['incoterms_id'] !== '') {
                    $incoterm = \App\Models\Incoterm::where('code', $hbl['incoterms_id'])->first();
                    $data['hbls'][$hIdx]['incoterms_id'] = $incoterm ? $incoterm->id : null;
                }
                foreach ($hblFkMap as $key => $table) {
                    if (empty($hbl[$key])) {
                        $data['hbls'][$hIdx][$key] = null;
                    } else {
                        $modelClass = $table === 'users' ? \App\Models\User::class : 
                                     ($table === 'ports' ? \App\Models\Port::class : \App\Models\TradePartner::class);
                        if (!$modelClass::where('id', $hbl[$key])->exists()) {
                            $data['hbls'][$hIdx][$key] = null;
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function store(StoreOceanExportRequest $request)
    {
        $data = $this->sanitizeShipmentData($request->validated());

        try {
            $shipment = $this->oceanExportService->store($data);
            
            return redirect()->route('ocean-export.edit', $shipment->id)
                ->with('success', 'Shipment created successfully.');
                
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Ocean Export Store - Database Error:', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            

            // Handle database errors
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'cannot be null') !== false) {
                    preg_match("/Column '(.*?)' cannot be null/", $e->getMessage(), $matches);
                    $col = $matches[1] ?? 'A required field';
                    $errorMessage = "Database Error: {$col} cannot be empty.";
                } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $e->getMessage(), $matches);
                    $val = $matches[1] ?? '';
                    $key = $matches[2] ?? '';
                    $errorMessage = "Database Error: Duplicate entry '{$val}' for key '{$key}'.";
                } else {
                    $errorMessage = 'Database Integrity Error. ' . $e->getMessage();
                }
                
                return back()->withInput()->with('error', $errorMessage);
            }
            
            // Handle foreign key constraint errors - try to nullify and save
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                try {
                    $data = $this->nullifyAllForeignKeys($data);
                    $shipment = $this->oceanExportService->store($data);
                    
                    return redirect()->route('ocean-export.edit', $shipment->id)
                        ->with('warning', 'Shipment created but some related records were not found and were removed.');
                        
                } catch (\Exception $retryException) {
                    return back()->withInput()->with('error', 'Invalid reference: One or more related records do not exist. Please check your selections.');
                }
            }
            
            // Generic database error
            return back()->withInput()->with('error', 'Unable to save the shipment. Please check your data and try again.');
            
        } catch (\Exception $e) {
            \Log::error('Ocean Export Store - General Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'An unexpected error occurred. Please try again or contact support if the problem persists.');
        }
    }

    public function edit(OceanExport $oceanExport)
    {
        $oceanExport->load(['hbls.customer', 'hbls.shipper', 'hbls.consignee', 'hbls.containers', 'hbls.commodities', 'containers.containerType', 'charges.currency', 'documents', 'statusLogs.user']);
        
        // Format container data for frontend input compatibility
        $oceanExport->containers = $oceanExport->containers->map(function($container) {
            $data = $container->toArray();
            
            $data['is_dg'] = $container->is_dg ? 1 : 0;
            $data['is_carrier_release'] = $container->is_carrier_release ? 1 : 0;
            $data['is_avail_pickup'] = $container->is_avail_pickup ? 1 : 0;
            $data['is_complete'] = $container->is_complete ? 1 : 0;
            $data['is_customs_hold'] = $container->is_customs_hold ? 1 : 0;
            $data['is_an_sent'] = $container->is_an_sent ? 1 : 0;
            $data['is_do_sent'] = $container->is_do_sent ? 1 : 0;

            $dateFields = [
                'lfd', 'fdd', 'storage_start_date', 'storage_end_date',
                'unload_vessel_date', 'gate_in_date', 'rail_start_date',
                'pod_eta', 'appointment_date', 'pickup_date', 'gate_out_date',
                'fdest_eta', 'eta_door', 'ata_door', 'empty_conf_date',
                'empty_ret_date', 'an_sent_date', 'do_sent_date'
            ];
            foreach ($dateFields as $field) {
                if (!empty($container->$field)) {
                    $data[$field] = $container->$field instanceof \DateTimeInterface 
                        ? $container->$field->format('Y-m-d') 
                        : substr((string)$container->$field, 0, 10);
                } else {
                    $data[$field] = null;
                }
            }
            
            return (object) $data;
        });

        // Format charge data for frontend compatibility
        $oceanExport->charges = $oceanExport->charges->map(function($charge) use ($oceanExport) {
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
            $data['sal'] = $charge->sal ?: 'Sea'; 
            $data['mbl_no'] = $oceanExport->file_no; 
            $data['eq_bl_no'] = $charge->remark ?? ''; 
            $data['remark'] = $charge->remark ?? false;
            $data['_selected'] = false;

            return (object) $data;
        });
        
        $offices = Office::where('is_active', true)->get();
        $ports = Port::latest()->take(10)->get();
        $vessels = Vessel::all();
        $agents = TradePartner::all();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->get();
        if ($carriers->isEmpty()) $carriers = $agents;
        $users = \App\Models\User::all();
        $containerTypes = \App\Models\ContainerType::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $incoterms = \App\Models\Incoterm::all();
        $currencies = Currency::all();
        $serviceTerms = ServiceTerm::all();
        $quotations = \App\Models\Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'items.currency'])->forModule('Ocean Export')->latest()->get();
        $page = request()->segment(2);
        $history = $oceanExport->statusLogs()
            ->with('user')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'date' => $log->created_at->format('Y-m-d H:i:s'),
                    'user' => $log->user?->name ?? 'System',
                    'action' => $log->status_name ?? $log->action ?? 'UPDATED',
                    'details' => $log->details,
                ];
            });
        $chargesList = $oceanExport->charges && $oceanExport->charges->count() > 0
            ? $oceanExport->charges->map(function($c) use ($oceanExport) {
                return [
                    'id' => $c->id,
                    'selected' => false,
                    'party' => $c->type === 'AP' ? 'Agent' : 'Custom',
                    'party_name' => $c->party_name ?? ($c->billTo->name ?? ($c->vendor->name ?? '')),
                    'party_name_id' => $c->type === 'AP' ? ($c->vendor_id ?? '') : ($c->bill_to_id ?? ''),
                    'sal' => 'Sea',
                    'pr' => $c->type === 'AP' ? 'Pay' : 'Rec',
                    'ppc' => ($c->pc ?? 'COLLECT') === 'PREPAID' ? 'Prepaid' : 'Colle',
                    'chrg_code' => $c->charge_code ?? '',
                    'currency' => $c->currency?->code ?? 'USD',
                    'rate' => (float)($c->rate ?? 0),
                    'qty' => (float)($c->qty ?? 1),
                    'qty_type' => $c->unit ?? 'B/L',
                    'roe' => 1.0,
                    'vat' => (float)($c->tax_percent ?? 0),
                    'inv_no' => $c->invoice_no ?? '',
                'is_invoiced' => $c->is_invoiced,
                    'financial_date' => $c->invoice_date ? (is_string($c->invoice_date) ? $c->invoice_date : $c->invoice_date->format('Y-m-d')) : '',
                    'eq_bl_no' => $c->remark ?? '',
                    'remark' => (bool)($c->remark ?? false),
                    'mbl_no' => '',
                ];
            })
            : [];
        
        return view('ocean-export.index', compact('oceanExport', 'offices', 'ports', 'vessels', 'agents', 'carriers', 'users', 'containerTypes', 'packageUnits', 'incoterms', 'currencies', 'serviceTerms', 'quotations', 'page', 'history', 'chargesList'));
    }

    public function update(UpdateOceanExportRequest $request, OceanExport $oceanExport)
    {
        $data = $this->sanitizeShipmentData($request->validated());

        try {
            $this->oceanExportService->update($oceanExport, $data);
            
            return back()->with('success', 'Shipment updated successfully.');
            
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Ocean Export Update - Database Error:', [
                'id' => $oceanExport->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            // Handle database errors
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'cannot be null') !== false) {
                    preg_match("/Column '(.*?)' cannot be null/", $e->getMessage(), $matches);
                    $col = $matches[1] ?? 'A required field';
                    $errorMessage = "Database Error: {$col} cannot be empty.";
                } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $e->getMessage(), $matches);
                    $val = $matches[1] ?? '';
                    $key = $matches[2] ?? '';
                    $errorMessage = "Database Error: Duplicate entry '{$val}' for key '{$key}'.";
                } else {
                    $errorMessage = 'Database Integrity Error. ' . $e->getMessage();
                }
                
                return back()->withInput()->with('error', $errorMessage);
            }
            
            // Handle foreign key constraint errors - try to nullify and save
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                try {
                    $data = $this->nullifyAllForeignKeys($data);
                    $this->oceanExportService->update($oceanExport, $data);
                    
                    return back()->with('warning', 'Shipment updated but some related records were not found and were removed.');
                    
                } catch (\Exception $retryException) {
                    return back()->withInput()->with('error', 'Invalid reference: One or more related records do not exist. Please check your selections.');
                }
            }
            
            // Generic database error
            return back()->withInput()->with('error', 'Unable to update the shipment. Please check your data and try again.');
            
        } catch (\Exception $e) {
            \Log::error('Ocean Export Update - General Error:', [
                'id' => $oceanExport->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'An unexpected error occurred. Please try again or contact support if the problem persists.');
        }
    }

    private function nullifyAllForeignKeys(array $data): array
    {
        $fkMap = [
            'dm_customer_id' => true, 'dm_shipper_id' => true, 'dm_consignee_id' => true, 'dm_notify_id' => true, 'dm_bill_to_id' => true,
            'forwarding_agent_id' => true, 'oversea_agent_id' => true, 'co_loader_id' => true, 'carrier_id' => true, 'acct_carrier_id' => true,
            'business_referred_by_id' => true, 'cy_location_id' => true, 'cfs_location_id' => true, 'return_location_id' => true, 'trucker_id' => true,
            'office_id' => true, 'op_id' => true, 'vessel_id' => true, 'pol_id' => true, 'pod_id' => true, 'del_id' => true, 'fdest_id' => true, 'receipt_id' => true,
            'service_term_from_id' => true, 'service_term_to_id' => true, 'released_by_id' => true, 'dm_sales_person_id' => true,
        ];

        foreach ($fkMap as $key => $val) {
            if (isset($data[$key])) {
                $data[$key] = null;
            }
        }

        if (isset($data['hbls']) && is_array($data['hbls'])) {
            $hblFkMap = [
                'customer_id', 'shipper_id', 'consignee_id', 'notify_party_id', 'customs_broker_id',
                'delivery_location_id', 'cfs_location_id', 'referred_by_id', 'freight_released_by_id', 
                'sales_person_id', 'pod_id', 'del_id', 'fdest_id', 'receipt_id'
            ];
            foreach ($data['hbls'] as $hIdx => $hbl) {
                foreach ($hblFkMap as $key) {
                    if (isset($data['hbls'][$hIdx][$key])) {
                        $data['hbls'][$hIdx][$key] = null;
                    }
                }
            }
        }

        return $data;
    }

    public function mblList(Request $request)
    {
        $query = OceanExport::with([
            'office', 'operator', 'carrier',
            'vessel', 'portOfLoading', 'portOfDischarge',
            'overseaAgent', 'dmCustomer',
            'hbls', 'containers.containerType',
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mbl_no', 'like', "%{$search}%")
                  ->orWhereHas('vessel', fn($v) => $v->where('name', 'like', "%{$search}%"))
                  ->orWhere('voyage', 'like', "%{$search}%")
                  ->orWhere('sub_bl_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->filled('op_id')) {
            $query->where('op_id', $request->op_id);
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('pol_id')) {
            $query->where('pol_id', $request->pol_id);
        }

        if ($request->filled('pod_id')) {
            $query->where('pod_id', $request->pod_id);
        }

        if ($request->filled('etd_from')) {
            $query->where('etd', '>=', $request->etd_from);
        }

        if ($request->filled('etd_to')) {
            $query->where('etd', '<=', $request->etd_to);
        }

        // Add filter support
        if ($request->filled('filter_file_no')) {
            $query->where('file_no', 'like', "%{$request->filter_file_no}%");
        }
        if ($request->filled('filter_mbl_no')) {
            $query->where('mbl_no', 'like', "%{$request->filter_mbl_no}%");
        }
        if ($request->filled('filter_etd')) {
            $query->where('etd', 'like', "%{$request->filter_etd}%");
        }
        if ($request->filled('filter_eta')) {
            $query->where('eta', 'like', "%{$request->filter_eta}%");
        }
        if ($request->filled('filter_pol')) {
            $query->whereHas('portOfLoading', fn($q) => $q->where('name', 'like', "%{$request->filter_pol}%"));
        }
        if ($request->filled('filter_pod')) {
            $query->whereHas('portOfDischarge', fn($q) => $q->where('name', 'like', "%{$request->filter_pod}%"));
        }
        if ($request->filled('filter_customer')) {
            $query->whereHas('dmCustomer', fn($q) => $q->where('name', 'like', "%{$request->filter_customer}%"));
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['file_no', 'mbl_no', 'voyage', 'etd', 'eta', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $shipments = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $html = view('ocean-export.partials.mbl-list-rows', compact('shipments'))->render();
                $pagination = view('vendor.pagination.custom', ['paginator' => $shipments])->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'pagination' => $pagination,
                    'first' => $shipments->firstItem() ?? 0,
                    'last' => $shipments->lastItem() ?? 0,
                    'total' => $shipments->total(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                ], 500);
            }
        }

        $operators = \App\Models\User::whereIn('id', OceanExport::distinct()->pluck('op_id'))->pluck('name', 'id');
        $offices = Office::where('is_active', true)->get();
        $carriers = TradePartner::where('type', 'CARRIER')->get();
        $ports = Port::latest()->take(10)->get();

        return view('ocean-export.mbl-list', compact('shipments', 'operators', 'offices', 'carriers', 'ports'));
    }

    public function hblList(Request $request)
    {
        $query = \App\Models\OceanExportHbl::with([
            'oceanExport.portOfLoading', 'oceanExport.portOfDischarge',
            'oceanExport.containers',
            'customer', 'shipper', 'consignee',
        ])
            ->withSum(['charges as ar_balance' => fn($q) => $q->where('type', 'AR')], 'total_amount')
            ->withSum(['charges as ap_balance' => fn($q) => $q->where('type', 'AP')], 'total_amount')
            ->withSum(['charges as dc_balance' => fn($q) => $q->where('type', 'DC_NOTE')], 'total_amount');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hbl_no', 'like', "%{$search}%")
                  ->orWhereHas('oceanExport', function($oq) use ($search) {
                      $oq->where('file_no', 'like', "%{$search}%")
                         ->orWhere('mbl_no', 'like', "%{$search}%");
                  })
                  ->orWhereHas('consignee', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('customer', fn($cuq) => $cuq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('op_id')) {
            $query->whereHas('oceanExport', function ($q) use ($request) {
                $q->where('op_id', $request->op_id);
            });
        }

        if ($request->filled('sales_person_id')) {
            $query->whereHas('oceanExport', function ($q) use ($request) {
                $q->where('dm_sales_person_id', $request->sales_person_id);
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('pol_id')) {
            $query->whereHas('oceanExport', function ($q) use ($request) {
                $q->where('pol_id', $request->pol_id);
            });
        }

        if ($request->filled('pod_id')) {
            $query->whereHas('oceanExport', function ($q) use ($request) {
                $q->where('pod_id', $request->pod_id);
            });
        }

        if ($request->filled('etd_from')) {
            $query->whereHas('oceanExport', function ($q) use ($request) {
                $q->where('etd', '>=', $request->etd_from);
            });
        }

        if ($request->filled('etd_to')) {
            $query->whereHas('oceanExport', function ($q) use ($request) {
                $q->where('etd', '<=', $request->etd_to);
            });
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['hbl_no', 'file_no', 'mbl_no', 'pieces', 'gross_weight', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $hbls = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $html = view('ocean-export.partials.hbl-list-rows', compact('hbls'))->render();
                $pagination = view('vendor.pagination.custom', ['paginator' => $hbls])->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'pagination' => $pagination,
                    'first' => $hbls->firstItem() ?? 0,
                    'last' => $hbls->lastItem() ?? 0,
                    'total' => $hbls->total(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                ], 500);
            }
        }

        $salesPersons = \App\Models\User::whereIn('id', \App\Models\OceanExport::distinct()->pluck('dm_sales_person_id'))->pluck('name', 'id');
        $operators = \App\Models\User::whereIn('id', \App\Models\OceanExport::distinct()->pluck('op_id'))->pluck('name', 'id');
        $offices = Office::where('is_active', true)->get();
        $ports = Port::latest()->take(10)->get();

        return view('ocean-export.hbl-list', compact('hbls', 'salesPersons', 'operators', 'offices', 'ports'));
    }

    public function destroy(OceanExport $oceanExport)
    {
        $oceanExport->delete();
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Shipment deleted successfully.']);
        }
        return redirect()->route('ocean-export.index')->with('success', 'Shipment deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:ocean_exports,id']);
        $count = OceanExport::whereIn('id', $request->ids)->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $count . ' shipment(s) deleted.']);
        }
        return redirect()->route('ocean-export.index')
            ->with('success', $count . ' shipment(s) deleted successfully.');
    }

    public function bulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:ocean_exports,id']);
        $shipments = OceanExport::whereIn('id', $request->ids)->get();
        foreach ($shipments as $shipment) {
            $shipment->update([
                'is_hold' => true,
                'is_blocked' => true,
            ]);
            $shipment->statusLogs()->create([
                'user_id' => auth()->id(),
                'status_code' => 'BLOCKED',
                'status_name' => 'BLOCKED',
                'details' => 'Shipment blocked/placed on hold via Tools menu.',
            ]);
        }
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) blocked.']);
        }
        return back()->with('success', count($request->ids) . ' shipment(s) blocked.');
    }

    public function bulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:ocean_exports,id']);
        $shipments = OceanExport::whereIn('id', $request->ids)->get();
        foreach ($shipments as $shipment) {
            $shipment->update([
                'is_hold' => false,
                'is_blocked' => false,
            ]);
            $shipment->statusLogs()->create([
                'user_id' => auth()->id(),
                'status_code' => 'UNBLOCKED',
                'status_name' => 'UNBLOCKED',
                'details' => 'Shipment unblocked/released from hold via Tools menu.',
            ]);
        }
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) unblocked.']);
        }
        return back()->with('success', count($request->ids) . ' shipment(s) unblocked.');
    }

    public function bulkChangeOp(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'op_id' => 'required|integer|exists:users,id']);
        $type = $request->input('type', 'mbl');
        if ($type === 'hbl' && $request->has('hbl_ids')) {
            $hblIds = \App\Models\OceanExportHbl::whereIn('id', $request->hbl_ids)->pluck('ocean_export_id');
            OceanExport::whereIn('id', $hblIds)->update(['op_id' => $request->op_id]);
            $count = $hblIds->count();
        } else {
            OceanExport::whereIn('id', $request->ids)->update(['op_id' => $request->op_id]);
            $count = count($request->ids);
        }
        return response()->json(['success' => true, 'message' => $count . ' shipment(s) OP changed.']);
    }

    public function bulkChangeSales(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'sales_person_id' => 'required|integer|exists:users,id']);
        OceanExport::whereIn('id', $request->ids)->update(['dm_sales_person_id' => $request->sales_person_id]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) sales changed.']);
    }

    // ========================================================================
    // HBL BULK OPERATIONS
    // ========================================================================

    public function hblBulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:ocean_export_hbls,id']);
        $count = \App\Models\OceanExportHbl::whereIn('id', $request->ids)->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $count . ' HBL(s) deleted.']);
        }
        return back()->with('success', $count . ' HBL(s) deleted successfully.');
    }

    public function hblBulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:ocean_export_hbls,id']);
        \App\Models\OceanExportHbl::whereIn('id', $request->ids)->update(['is_customs_hold' => true]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) blocked.']);
        }
        return back()->with('success', count($request->ids) . ' HBL(s) blocked.');
    }

    public function hblBulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:ocean_export_hbls,id']);
        \App\Models\OceanExportHbl::whereIn('id', $request->ids)->update(['is_customs_hold' => false]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) unblocked.']);
        }
        return back()->with('success', count($request->ids) . ' HBL(s) unblocked.');
    }

    public function updateColor(Request $request, OceanExport $oceanExport)
    {
        $request->validate([
            'color' => 'nullable|string|max:20',
        ]);

        $oceanExport->update(['color' => $request->color]);

        return response()->json(['success' => true, 'color' => $oceanExport->color]);
    }

    // ========================================================================
    // HBL PRINT
    // ========================================================================

    public function hblPrint($id, $hblIndex)
    {
        $shipment = OceanExport::with([
            'hbls.shipper', 'hbls.consignee', 'hbls.notifyParty',
            'hbls.placeOfDischarge', 'hbls.placeOfDelivery', 'hbls.finalDestination', 'hbls.placeOfReceipt', 'hbls.placeOfLoading',
            'hbls.deliveryLocation', 'hbls.customer', 'hbls.containers',
            'portOfLoading', 'portOfDischarge', 'placeOfDelivery', 'finalDestination', 'placeOfReceipt',
            'vessel', 'forwardingAgent', 'carrier',
            'containers.containerType',
        ])->findOrFail($id);

        $hblIndex = (int) $hblIndex;
        $hbls = $shipment->hbls->values();

        if ($hblIndex < 0 || $hblIndex >= $hbls->count()) {
            abort(404, 'HBL index not found.');
        }

        $hbl = $hbls[$hblIndex];

        // Resolve all display values from IDs
        $data = [
            'hbl_number'        => $hbl->hbl_no ?? '',
            'booking_number'    => $shipment->booking_no ?: ($shipment->mbl_no ?? ''),
            'mbl_number'        => $shipment->mbl_no ?? '',
            'quotation_no'      => $hbl->quotation_no ?? '',
            'sc_no'             => $hbl->sc_no ?? '',
            'lc_no'             => $hbl->lc_no ?? '',
            'po_no'             => $hbl->po_no ?? '',
            'export_references' => $shipment->sub_bl_no ?: ($hbl->po_no ?: ''),
            'shipper'           => $hbl->shipper?->name ?? '',
            'consignee'         => $hbl->consignee?->name ?? '',
            'notify_party'      => $hbl->notifyParty?->name ?? '',
            'delivery_agent'    => $hbl->deliveryLocation?->name ?? '',
            'forwarding_agent'  => $shipment->forwardingAgent?->name ?? '',
            'vessel'            => $hbl->vessel_name ?: ($shipment->vessel?->name ?? ''),
            'voyage'            => $hbl->voyage_no ?: ($shipment->voyage ?? ''),
            'port_of_loading'   => $hbl->placeOfLoading?->name ?: ($shipment->portOfLoading?->name ?? ''),
            'port_of_discharge' => $hbl->placeOfDischarge?->name ?: ($shipment->portOfDischarge?->name ?? ''),
            'place_of_receipt'  => $hbl->placeOfReceipt?->name ?: ($shipment->placeOfReceipt?->name ?? ''),
            'place_of_delivery' => $hbl->placeOfDelivery?->name ?: ($shipment->placeOfDelivery?->name ?? ''),
            'final_destination' => $hbl->finalDestination?->name ?: ($shipment->finalDestination?->name ?? ''),
            'pre_carriage_by'   => $hbl->pre_carriage_by ?? '',
            'service_term'      => $hbl->service_term ?? '',
            'ship_mode'         => $hbl->ship_mode ?? '',
            'cargo_type'        => $hbl->cargo_type ?? '',
            'shipped_on_board'  => ($hbl->vessel_name ?: ($shipment->vessel?->name ?? '')) . ($hbl->voyage_no ? ' / ' . $hbl->voyage_no : ($shipment->voyage ? ' / ' . $shipment->voyage : '')),
            'on_board_date'     => $hbl->date_of_issue ? $hbl->date_of_issue->format('Y-m-d') : ($shipment->post_date ? $shipment->post_date->format('Y-m-d') : ''),
            'freight_payable_at'=> $hbl->freight_payable_at ?? '',
            'currency'          => 'USD',
            'prepaid_amount'    => strtolower($hbl->freight_payable_at ?? '') === 'prepaid' ? 'PREPAID' : '',
            'collect_amount'    => strtolower($hbl->freight_payable_at ?? '') === 'collect' ? 'COLLECT' : '',
            'date_of_issue'     => $hbl->date_of_issue ? $hbl->date_of_issue->format('Y-m-d') : ($shipment->post_date ? $shipment->post_date->format('Y-m-d') : ''),
            'incoterms'         => $hbl->incoterms_id ?? '',
            'hbl_remark'        => $hbl->hbl_remark ?? '',
            'marks_numbers'     => '',
            'no_of_packages'    => '',
            'description'       => $hbl->description?->description ?? '',
            'gross_weight'      => '',
            'measurement'       => '',
        ];

        // Aggregate container data for marks, packages, weight, measurement
        $containers = $shipment->containers;
        if ($containers && $containers->count() > 0) {
            $marks = [];
            $totalPkg = 0;
            $totalWeight = 0;
            $totalMeasure = 0;
            $descriptions = [];

            foreach ($containers as $c) {
                $containerInfo = ($c->container_no ?? '') . ' / ' . ($c->seal_no ?? '');
                if (trim($containerInfo, ' /')) {
                    $marks[] = trim($containerInfo, ' /');
                }
                $totalPkg += (float)($c->pkg_qty ?? 0);
                $totalWeight += (float)($c->weight_kg ?? 0);
                $totalMeasure += (float)($c->measure_cbm ?? 0);
            }

            $data['marks_numbers'] = implode("\n", $marks);
            $data['no_of_packages'] = $totalPkg > 0 ? number_format($totalPkg) : '';
            $data['gross_weight'] = $totalWeight > 0 ? number_format($totalWeight, 2) . ' KGS' : '';
            $data['measurement'] = $totalMeasure > 0 ? number_format($totalMeasure, 3) . ' CBM' : '';
            $data['description'] = $descriptions ? implode("\n", $descriptions) : '';

        }

        // Template list
        $templates = [
            ['key' => 'ntg_air', 'name' => 'NTG AIR', 'pages' => 2, 'size' => 'A4'],
            ['key' => 'united_american', 'name' => 'UNITED AMERICAN LINE', 'pages' => 1, 'size' => 'A4'],
            ['key' => 'ocean_blue', 'name' => 'OCEAN BLUE EXPRESS', 'pages' => 2, 'size' => 'letter'],
            ['key' => 'silk_container', 'name' => 'SILK CONTAINER LINES', 'pages' => 1, 'size' => 'A4'],
            ['key' => 'transamerica', 'name' => 'TRANSAMERICA LOGISTICS', 'pages' => 1, 'size' => 'A4'],
        ];

        return view('ocean-export.hbl-print', compact('data', 'templates', 'shipment', 'hbl', 'hblIndex'));
    }

    // ========================================================================
    // CHARGE OPERATIONS
    // ========================================================================

    public function applyChargeTemplate(Request $request, $oceanExportId)
    {
        $currency = Currency::where('code', 'USD')->first();
        $templates = [
            ['code' => 'OFT', 'name' => 'Ocean Freight', 'rate' => 1200, 'qty' => 1],
            ['code' => 'THC', 'name' => 'Terminal Handling Charge', 'rate' => 350, 'qty' => 1],
            ['code' => 'DOC', 'name' => 'Documentation Fee', 'rate' => 75, 'qty' => 1],
        ];

        $shipment = OceanExport::findOrFail($oceanExportId);
        $existingCodes = $shipment->charges()->pluck('charge_code')->toArray();

        foreach ($templates as $tc) {
            if (in_array($tc['code'], $existingCodes)) continue;

            $amount = $tc['rate'] * $tc['qty'];
            $shipment->charges()->create([
                'type' => 'AR',
                'charge_code' => $tc['code'],
                'charge_name' => $tc['name'],
                'pc' => 'COLLECT',
                'qty' => $tc['qty'],
                'unit' => 'UNIT',
                'currency_id' => $currency->id ?? null,
                'rate' => $tc['rate'],
                'amount' => $amount,
                'total_amount' => $amount,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function copyChargesFromQuote(Request $request, $oceanExportId)
    {
        $request->validate([
            'quote_id' => 'required|exists:quotations,id'
        ]);

        $quote = Quotation::with('items')->findOrFail($request->quote_id);
        $currency = Currency::where('code', 'USD')->first();
        $shipment = OceanExport::findOrFail($oceanExportId);

        $createdCount = 0;

        if ($quote->items && $quote->items->count() > 0) {
            foreach ($quote->items as $item) {
                $rate = floatval($item->rate ?? 0);
                $qty = floatval($item->qty ?? 1);
                $amount = $rate * $qty;
                $shipment->charges()->create([
                    'type' => 'AR',
                    'charge_code' => $item->charge_code ?? 'QTE',
                    'charge_name' => $item->description ?? $item->charge_code ?? 'Quote Item',
                    'pc' => $item->pc ?? 'COLLECT',
                    'qty' => $qty,
                    'unit' => $item->unit ?? 'UNIT',
                    'currency_id' => $currency->id ?? null,
                    'rate' => $rate,
                    'amount' => $amount,
                    'total_amount' => $amount,
                    'remark' => "Copied from Quote ID: {$request->quote_id}",
                ]);
                $createdCount++;
            }
        }

        if ($createdCount === 0) {
            $shipment->charges()->create([
                'type' => 'AR',
                'charge_code' => 'OFT',
                'charge_name' => 'Ocean Freight (From Quote)',
                'pc' => 'COLLECT',
                'qty' => 1,
                'unit' => 'UNIT',
                'currency_id' => $currency->id ?? null,
                'rate' => 950.00,
                'amount' => 950.00,
                'total_amount' => 950.00,
                'remark' => "Copied from Quote ID: {$request->quote_id}"
            ]);
            $createdCount = 1;
        }

        return response()->json(['success' => true, 'created' => $createdCount]);
    }

    public function duplicateCharges(Request $request, $oceanExportId)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:charges,id'
        ]);

        $shipment = OceanExport::findOrFail($oceanExportId);

        foreach ($request->ids as $id) {
            $charge = Charge::findOrFail($id);
            if ($charge->chargeable_type !== 'App\Models\OceanExport' || $charge->chargeable_id != $oceanExportId) {
                continue;
            }
            $clone = $charge->replicate();
            $clone->invoice_no = null;
            $clone->invoice_date = null;
            $clone->is_invoiced = false;
            $clone->save();
        }

        return response()->json(['success' => true]);
    }

    public function bulkUpdateCurrency(Request $request, $oceanExportId)
    {
        $request->validate([
            'currency' => 'required|string|max:10',
        ]);

        $currency = Currency::where('code', $request->currency)->first();
        if (!$currency) {
            return response()->json(['success' => false, 'message' => 'Currency not found.'], 404);
        }

        Charge::where('chargeable_type', 'App\Models\OceanExport')
            ->where('chargeable_id', $oceanExportId)
            ->update(['currency_id' => $currency->id]);

        return response()->json(['success' => true]);
    }

    public function applyVatToAll(Request $request, $oceanExportId)
    {
        $request->validate([
            'vat' => 'required|numeric|min:0|max:100',
        ]);

        $charges = Charge::where('chargeable_type', 'App\Models\OceanExport')
            ->where('chargeable_id', $oceanExportId)
            ->get();

        foreach ($charges as $charge) {
            $taxAmount = floatval($charge->amount) * (floatval($request->vat) / 100);
            $charge->update([
                'tax_percent' => $request->vat,
                'tax_amount' => $taxAmount,
                'total_amount' => floatval($charge->amount) + $taxAmount,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function prorataCharges(Request $request, $oceanExportId)
    {
        $request->validate([
            'basis' => 'required|in:volume,weight',
            'charge_id' => 'required|exists:charges,id'
        ]);

        $shipment = OceanExport::with(['hbls', 'containers'])->findOrFail($oceanExportId);
        $charge = Charge::findOrFail($request->charge_id);

        if ($charge->chargeable_type !== 'App\Models\OceanExport' || $charge->chargeable_id != $oceanExportId) {
            return response()->json(['success' => false, 'message' => 'Charge does not belong to this shipment.'], 400);
        }

        $totalVal = 0;
        if ($request->basis == 'volume') {
            $totalVal = $shipment->containers->sum('measure_cbm');
        } else {
            $totalVal = $shipment->containers->sum('weight_kg');
        }

        if ($totalVal <= 0) {
            return response()->json(['success' => false, 'message' => 'Total basis value is zero, cannot prorate.'], 400);
        }

        $hbls = $shipment->hbls;
        if ($hbls->count() == 0) {
            return response()->json(['success' => false, 'message' => 'No House BLs found to prorate charges across.'], 400);
        }

        foreach ($hbls as $hbl) {
            $fraction = 1 / $hbls->count();
            $amount = floatval($charge->amount) * $fraction;
            $taxAmount = floatval($charge->tax_amount) * $fraction;

            $shipment->charges()->create([
                'type' => $charge->type,
                'charge_code' => $charge->charge_code,
                'charge_name' => $charge->charge_name,
                'bill_to_id' => $charge->bill_to_id,
                'vendor_id' => $charge->vendor_id,
                'pc' => $charge->pc,
                'qty' => floatval($charge->qty) * $fraction,
                'unit' => $charge->unit,
                'currency_id' => $charge->currency_id,
                'rate' => $charge->rate,
                'amount' => $amount,
                'tax_percent' => $charge->tax_percent,
                'tax_amount' => $taxAmount,
                'total_amount' => $amount + $taxAmount,
                'remark' => "Prorated from master charge: {$charge->charge_code}"
            ]);
        }

        $charge->delete();

        return response()->json(['success' => true]);
    }

    public function exportChargesToExcel($oceanExportId)
    {
        $charges = Charge::where('chargeable_type', 'App\Models\OceanExport')
            ->where('chargeable_id', $oceanExportId)
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="charges-' . $oceanExportId . '-' . now()->format('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($charges) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Type', 'Code', 'Name', 'P/C', 'Rate', 'Qty', 'Unit', 'Amount', 'Tax %', 'Tax Amt', 'Total', 'Invoice No', 'Remark']);
            foreach ($charges as $c) {
                fputcsv($file, [
                    $c->type,
                    $c->charge_code,
                    $c->charge_name,
                    $c->pc,
                    $c->rate,
                    $c->qty,
                    $c->unit,
                    $c->amount,
                    $c->tax_percent,
                    $c->tax_amount,
                    $c->total_amount,
                    $c->invoice_no,
                    $c->remark,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printCharges($oceanExportId)
    {
        $shipment = OceanExport::with(['office', 'operator', 'vessel', 'portOfLoading', 'portOfDischarge', 'charges'])->findOrFail($oceanExportId);
        return view('ocean-export.print-charges', compact('shipment'));
    }

    public function deleteAllCharges($oceanExportId)
    {
        OceanExport::findOrFail($oceanExportId);
        Charge::where('chargeable_type', 'App\Models\OceanExport')
            ->where('chargeable_id', $oceanExportId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function createInvoiceFromCharges(Request $request, $oceanExportId, \App\Services\InvoiceGenerationService $invoiceService)
    {
        OceanExport::findOrFail($oceanExportId);

        $query = Charge::where('chargeable_type', 'App\Models\OceanExport')
            ->where('chargeable_id', $oceanExportId);

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
                $alreadyInvoiced = Charge::where('chargeable_type', 'App\Models\OceanExport')
                    ->where('chargeable_id', $oceanExportId)
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

            $allChargesCount = Charge::where('chargeable_type', 'App\Models\OceanExport')->where('chargeable_id', $oceanExportId)->count();
            if ($allChargesCount > 0) {
                $existingInv = Charge::where('chargeable_type', 'App\Models\OceanExport')->where('chargeable_id', $oceanExportId)->whereNotNull('invoice_no')->pluck('invoice_no')->first();
                return response()->json([
                    'success' => true,
                    'message' => 'Charges are already invoiced (' . ($existingInv ?? 'Invoiced') . '). Opening Freight Invoice...',
                    'invoice_no' => $existingInv ?? 'INV-EXISTING',
                    'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'ocean-export', 'id' => $oceanExportId])
                ]);
            }
            return response()->json(['success' => false, 'message' => 'No charges found for this shipment. Please add charges first.'], 400);
        }

        $companyId = auth()->user()->company_id ?? 1;
        $result = $invoiceService->generateInvoicesFromCharges(
            $companyId,
            'App\Models\OceanExport',
            $oceanExportId,
            'Export',
            'Sea',
            $charges
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        $invNo = implode(', ', $result['invoices']);

        // === AUTO-POPULATION INTEGRATION ===
        $shipment = OceanExport::with([
            'forwardingAgent', 'carrier', 'dmShipper', 'dmConsignee', 'dmNotify', 
            'deliveryAgent', 'salesPerson', 'dmCustomer', 'hbls'
        ])->find($oceanExportId);

        $autoPopService = new ShipmentMemoAutoPopulationService();
        $autoPopulatedData = [
            'master_bl' => $autoPopService->getAutoPopulatedData('ocean-export', $shipment, 'master_bl'),
            'house_bl' => $autoPopService->getAutoPopulatedData('ocean-export', $shipment, 'house_bl'),
            'enabled_fields' => [
                'master_bl' => $autoPopService->getEnabledFields('ocean-export', 'master_bl'),
                'house_bl' => $autoPopService->getEnabledFields('ocean-export', 'house_bl'),
            ],
        ];

        return response()->json([
            'success' => true,
            'invoice_no' => $invNo,
            'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'ocean-export', 'id' => $oceanExportId]),
            'auto_populated_data' => $autoPopulatedData
        ]);
    }

    public function exportCsv(Request $request)
    {
        $query = OceanExport::with([
            'office', 'operator', 'vessel', 'carrier',
            'portOfLoading', 'portOfDischarge',
            'dmCustomer', 'overseaAgent', 'containers',
        ]);

        $this->applyFiltersToQuery($query, $request);

        $shipments = $query->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ocean-export-' . now()->format('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($shipments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Booking no', 'MBL No.', 'Sub B/L No.', 'Office', 'Operator',
                'Carrier', 'Vessel', 'Voyage', 'Port of Loading', 'Port of Discharge',
                'Customer', 'Oversea Agent', 'ETD', 'ETA', 'Final ETA',
                'Freight Term', 'O.B/L Type', 'M.B/L Type',
                'Containers', 'HBLs', 'Post Date', 'Created At',
            ]);

            foreach ($shipments as $s) {
                fputcsv($file, [
                    $s->file_no,
                    $s->mbl_no,
                    $s->sub_bl_no,
                    $s->office->code ?? '--',
                    $s->operator->name ?? '--',
                    $s->carrier->name ?? '--',
                    $s->vessel->name ?? '--',
                    $s->voyage,
                    $s->portOfLoading->name ?? '--',
                    $s->portOfDischarge->name ?? '--',
                    $s->dmCustomer->name ?? '--',
                    $s->overseaAgent->name ?? '--',
                    $s->etd ? $s->etd->format('m-d-Y') : '--',
                    $s->eta ? $s->eta->format('m-d-Y') : '--',
                    $s->final_eta ? $s->final_eta->format('m-d-Y') : '--',
                    $s->freight_term,
                    $s->obl_type,
                    $s->bl_type,
                    $s->containers->count(),
                    $s->hbls()->count(),
                    $s->post_date ? $s->post_date->format('m-d-Y') : '--',
                    $s->created_at->format('m-d-Y H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function createQuoteBooking()
    {
        $customers = TradePartner::whereIn('type', ['CS', 'CLIENT'])->get();
        $ports = Port::latest()->take(10)->get();
        $users = User::all();
        $statuses = Quotation::select('status')->distinct()->whereNotNull('status')->pluck('status');
        $quotations = Quotation::with(['customer:id,name', 'salesPerson:id,name', 'pol:id,name', 'pod:id,name', 'carrier:id,name', 'op:id,name', 'items'])->forModule('Ocean Export')->latest()->get();
        $latestBooking = OceanBooking::latest()->value('booking_no');
        $nextBookingNo = $latestBooking ? 'OBE-' . str_pad((int) substr($latestBooking, 4) + 1, 6, '0', STR_PAD_LEFT) : 'OBE-000001';
        $quotationsData = $quotations->map(function ($q) {
            return [
                'id' => $q->id,
                'quote_no' => $q->quote_no,
                'quote_date' => $q->quote_date?->format('Y-m-d'),
                'expiry_date' => $q->expiry_date?->format('Y-m-d'),
                'customer_id' => $q->customer_id,
                'customer_name' => $q->customer?->name ?? '',
                'sales_person_id' => $q->sales_person_id,
                'sales_name' => $q->salesPerson?->name ?? '',
                'pol_id' => $q->pol_id,
                'pol_name' => $q->pol?->name ?? '',
                'pod_id' => $q->pod_id,
                'pod_name' => $q->pod?->name ?? '',
                'status' => $q->status ?? '',
                'commodity' => '',
                'carrier' => '',
                'items_count' => $q->items->count(),
                'items' => $q->items->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'charge_code' => $i->charge_code,
                        'charge_name' => $i->charge_name,
                        'unit' => $i->unit,
                        'currency_id' => $i->currency_id,
                        'qty' => $i->qty,
                        'rate' => $i->rate,
                        'amount' => $i->amount,
                    ];
                }),
            ];
        });

        return view('ocean-export.create-quote-booking', compact('customers', 'ports', 'users', 'statuses', 'quotationsData', 'nextBookingNo'));
    }

    public function profitSummaryView($id, Request $request)
    {
        $shipment = OceanExport::with([
            'office', 'operator', 'carrier', 'vessel',
            'portOfLoading', 'portOfDischarge',
            'containers', 'hbls', 'charges.currency'
        ])->findOrFail($id);

        return view('ocean-export.profit-summary', compact('shipment'));
    }

    public function profitDetailView($id, Request $request)
    {
        $shipment = OceanExport::with([
            'office', 'operator', 'carrier', 'vessel',
            'portOfLoading', 'portOfDischarge',
            'containers', 'hbls', 'charges.currency'
        ])->findOrFail($id);

        return view('ocean-export.profit-detail', compact('shipment'));
    }

    public function deliveryOrderView($id, Request $request)
    {
        $shipment = OceanExport::with([
            'office', 'operator', 'carrier', 'vessel',
            'portOfLoading', 'portOfDischarge',
            'containers', 'hbls'
        ])->findOrFail($id);

        return view('ocean-export.profit-summary', compact('shipment'));
    }

    public function batchPrintView($id, Request $request)
    {
        $shipment = OceanExport::with([
            'office', 'operator', 'carrier', 'vessel',
            'portOfLoading', 'portOfDischarge',
            'containers', 'hbls' => function($q) {
                $q->with(['shipper', 'consignee', 'notifyParty', 'placeOfReceipt', 'placeOfDischarge', 'placeOfDelivery', 'hblTemplate']);
            }
        ])->findOrFail($id);

        $templates = \App\Models\HblTemplate::where('is_active', true)->orderBy('name')->get();

        return view('ocean-export.batch-print-view', compact('shipment', 'templates'));
    }

    public function devSegView($id, Request $request)
    {
        $shipment = OceanExport::with([
            'office', 'operator', 'carrier', 'vessel',
            'portOfLoading', 'portOfDischarge',
            'containers', 'hbls'
        ])->findOrFail($id);

        return view('ocean-export.profit-summary', compact('shipment'));
    }

    public function renderHblHtml(Request $request, $shipmentId, $hblId)
    {
        try {
            $hbl = \App\Models\OceanExportHbl::with([
                'shipper', 'consignee', 'notifyParty', 'placeOfReceipt', 'placeOfDischarge', 'placeOfDelivery',
                'containers' => function($q) {
                    $q->with(['containerType']);
                },
                'oceanExport' => function($q) {
                    $q->with(['office', 'operator', 'carrier', 'portOfLoading', 'portOfDischarge', 'forwardingAgent', 'overseaAgent', 'containers']);
                }
            ])->findOrFail($hblId);

            if ($hbl->containers->count() === 0 && $hbl->oceanExport && $hbl->oceanExport->containers->count() > 0) {
                $hbl->setRelation('containers', $hbl->oceanExport->containers);
            }

            $templateId = $request->input('template_id');
            if ($templateId) {
                $template = \App\Models\HblTemplate::find($templateId);
            } else {
                $template = $hbl->hblTemplate ?: \App\Models\HblTemplate::where('is_active', true)->first();
            }

            if (!$template) {
                return response()->json([
                    'html' => '<div style="padding:40px;text-align:center;color:#ef4444;font-weight:bold;">No active HBL templates found. Go to Settings > HBL Templates to configure them.</div>',
                    'css' => ''
                ]);
            }

            // Map template names to their respective isolated Blade files
            $mapping = [
                'SILK CONTAINER LINES' => 'ocean-export.hbl-templates.silk',
                'NTG AIR' => 'ocean-export.hbl-templates.ntg-air',
                'OCEAN BLUE EXPRESS INC.' => 'ocean-export.hbl-templates.ocean-blue',
                'TRANSAMERICA LOGISTIC' => 'ocean-export.hbl-templates.transamerica',
                'UNITED AMERICAN LINE' => 'ocean-export.hbl-templates.united-american',
            ];

            $viewName = $mapping[$template->name] ?? null;

            if ($viewName && view()->exists($viewName)) {
                // Render the specific isolated Blade file
                $html = view($viewName, [
                    'hbl' => $hbl,
                    'date' => date('m/d/Y')
                ])->render();
            } else {
                // Fallback to database content rendering if specific view not found
                $html = \Illuminate\Support\Facades\Blade::render($template->content, ['hbl' => $hbl, 'date' => date('m/d/Y')]);
            }

            return response()->json([
                'html' => $html,
                'css' => $template->css ?? '',
                'template_id' => $template->id,
                'template_name' => $template->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'html' => '<div style="padding:40px;color:#ef4444;"><h4>Blade Compilation Error</h4><pre style="white-space:pre-wrap;font-family:monospace;font-size:11px;">' . e($e->getMessage()) . '</pre></div>',
                'css' => ''
            ], 500);
        }
    }

    public function saveSelectedTemplate(Request $request, $hblId)
    {
        try {
            $hbl = \App\Models\OceanExportHbl::findOrFail($hblId);
            $hbl->update([
                'hbl_template_id' => $request->input('template_id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'HBL template selection saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
