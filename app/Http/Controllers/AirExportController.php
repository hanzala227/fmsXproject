<?php

namespace App\Http\Controllers;

use App\Models\AirExport;
use App\Models\Office;
use App\Models\Port;
use App\Models\TradePartner;
use App\Services\AirExportService;
use App\Services\ShipmentMemoAutoPopulationService;
use App\Http\Requests\StoreAirExportRequest;
use App\Http\Requests\UpdateAirExportRequest;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;

class AirExportController extends Controller
{
    protected $airExportService;

    public function __construct(AirExportService $service)
    {
        $this->airExportService = $service;
    }

    public function index(Request $request)
    {
        $query = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'forwardingAgent', 'overseaAgent', 'shipper', 'dmCustomer',
            'hbls',
        ]);

        $this->applyFiltersToQuery($query, $request);

        $sortField = $request->get('sort', 'created_at');
        $sortDir   = $request->get('dir', 'desc');
        $allowedSorts = ['file_no', 'mawb_no', 'etd', 'eta', 'created_at', 'post_date', 'flight_no'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $shipments = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        $offices = Office::where('is_active', true)->get();
        $users = \App\Models\User::all();
        $agents = TradePartner::whereNotNull('name')->where(function($q) { $q->where('type', 'carrier')->orWhereNull('type'); })->orderBy('name')->get();
        $ports = Port::all();

        return view('air-export.list', compact('shipments', 'offices', 'users', 'agents', 'ports'));
    }

    private function applyFiltersToQuery($query, Request $request)
    {
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mawb_no', 'like', "%{$search}%")
                  ->orWhere('flight_no', 'like', "%{$search}%");
            });
        }

        // Per-column filters from filter row
        if ($request->filled('filter_file_no')) {
            $query->where('file_no', 'like', "%{$request->filter_file_no}%");
        }
        if ($request->filled('filter_mawb_no')) {
            $query->where('mawb_no', 'like', "%{$request->filter_mawb_no}%");
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('office', fn($q) => $q->where('code', 'like', "%{$request->filter_office}%"));
        }
        if ($request->filled('filter_shipper')) {
            $query->whereHas('shipper', fn($q) => $q->where('name', 'like', "%{$request->filter_shipper}%"));
        }
        if ($request->filled('filter_etd')) {
            $query->where('etd', 'like', "%{$request->filter_etd}%");
        }
        if ($request->filled('filter_eta')) {
            $query->where('eta', 'like', "%{$request->filter_eta}%");
        }
        if ($request->filled('filter_dep')) {
            $query->whereHas('depPort', fn($q) => $q->where('name', 'like', "%{$request->filter_dep}%"));
        }
        if ($request->filled('filter_dst')) {
            $query->whereHas('dstPort', fn($q) => $q->where('name', 'like', "%{$request->filter_dst}%"));
        }
        if ($request->filled('filter_oa')) {
            $query->whereHas('overseaAgent', fn($q) => $q->where('name', 'like', "%{$request->filter_oa}%"));
        }
        if ($request->filled('filter_customer')) {
            $query->whereHas('dmCustomer', fn($q) => $q->where('name', 'like', "%{$request->filter_customer}%"));
        }
        if ($request->filled('filter_oa')) {
            $query->whereHas('overseaAgent', fn($q) => $q->where('name', 'like', "%{$request->filter_oa}%"));
        }

        // Advanced filters
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        if ($request->filled('op_id')) {
            $query->where('op_id', $request->op_id);
        }
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }
        if ($request->filled('dep_port_id')) {
            $query->where('dep_port_id', $request->dep_port_id);
        }
        if ($request->filled('dst_port_id')) {
            $query->where('dst_port_id', $request->dst_port_id);
        }
        if ($request->filled('etd_from')) {
            $query->where('etd', '>=', $request->etd_from);
        }
        if ($request->filled('etd_to')) {
            $query->where('etd', '<=', $request->etd_to);
        }
        if ($request->filled('eta_from')) {
            $query->where('eta', '>=', $request->eta_from);
        }
        if ($request->filled('eta_to')) {
            $query->where('eta', '<=', $request->eta_to);
        }

        return $query;
    }

    public function create(Request $request)
    {
        $offices = Office::where('is_active', true)->get();
        $ports = Port::all();
        $agents = TradePartner::all();
        $users = \App\Models\User::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $currencies = Currency::all();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->orderBy('name')->get();
        if ($carriers->isEmpty()) $carriers = $agents;

        $page = $request->segment(2);
        $quotations = \App\Models\Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'items.currency'])->forModule('Air Export')->latest()->get();

        // Handle booking conversion - load data from booking
        if ($request->has('booking')) {
            $booking = \App\Models\AirBooking::findOrFail($request->booking);
            
            // Create a new AirExport object with booking data
            $airExport = new AirExport();
            $airExport->booking_no = $booking->booking_no;
            $airExport->mawb_no = $booking->booking_no; // Use booking_no as MAWB initially
            $airExport->office_id = $booking->office_id;
            $airExport->op_id = $booking->op_id;
            $airExport->carrier_id = $booking->carrier_id;
            $airExport->oversea_agent_id = $booking->oversea_agent_id;
            $airExport->flight_no = $booking->flight_no;
            $airExport->dep_port_id = $booking->dep_port_id;
            $airExport->dst_port_id = $booking->dst_port_id;
            $airExport->etd = $booking->etd;
            $airExport->eta = $booking->eta;
            $airExport->pkg_qty = $booking->pkg_qty ?? 0;
            $airExport->pkg_unit_id = $booking->pkg_unit_id;
            $airExport->gross_weight = $booking->gross_weight ?? 0;
            $airExport->chargeable_weight = $booking->chargeable_weight ?? 0;
            $airExport->volume = $booking->volume ?? 0;
            $airExport->shipper_id = $booking->shipper_id;
            $airExport->freight_term = $booking->wt_val_payment;
            $airExport->post_date = now();
            
            $chargesData = collect();
            return view('air-export.create', compact('airExport', 'offices', 'ports', 'agents', 'carriers', 'users', 'packageUnits', 'currencies', 'page', 'quotations', 'chargesData', 'booking'));
        }

        // Handle copy - load data from existing shipment
        if ($request->has('copy')) {
            $airExport = AirExport::with(['hbls', 'charges.currency'])->findOrFail($request->copy);
            // Null the ID so the create view treats this as a new record (POST), not an edit (PUT)
            $airExport->id = null;
            $airExport->mawb_no = null;
            $airExport->file_no = null;
            $airExport->is_blocked = false;
            $airExport->created_at = null;
            $chargesData = $airExport->charges->isNotEmpty()
                ? $airExport->charges->map(fn($c) => [
                    'id' => null,
                    'selected' => false,
                    'party' => $c->party ?? 'Custom',
                    'party_name_id' => $c->type === 'AP' ? ($c->vendor_id ?? '') : ($c->bill_to_id ?? ''),
                    'sal' => $c->sal ?? 'Air',
                    'pr' => ($c->type === 'AP' || $c->type === 'origin_cost') ? 'Pay' : 'Rec',
                    'ppc' => ($c->pc === 'PREPAID') ? 'Prepaid' : 'Colle',
                    'chrg_code' => $c->charge_code ?? '',
                    'charge_name' => $c->charge_name ?? '',
                    'currency' => $c->currency->code ?? 'USD',
                    'currency_id' => $c->currency_id,
                    'rate' => (float)($c->rate ?? 0),
                    'qty' => (float)($c->qty ?? 1),
                    'qty_type' => $c->unit ?? 'B/L',
                    'roe' => (float)($c->roe ?? 1.0),
                    'vat' => (float)($c->tax_percent ?? 0),
                    'amount' => (float)($c->amount ?? 0),
                    'total_amount' => (float)($c->total_amount ?? $c->amount ?? 0),
                    'type' => $c->type,
                    'vendor_id' => $c->vendor_id,
                    'bill_to_id' => $c->bill_to_id,
                    'inv_no' => '',
                    'financial_date' => date('Y-m-d'),
                    'eq_bl_no' => $c->remark ?? '',
                    'remark' => false,
                    'mbl_no' => '',
                ])
                : collect();
            return view('air-export.create', compact('airExport', 'offices', 'ports', 'agents', 'carriers', 'users', 'packageUnits', 'currencies', 'page', 'quotations', 'chargesData'));
        }

        $chargesData = collect();

        return view('air-export.create', compact('offices', 'ports', 'agents', 'carriers', 'users', 'packageUnits', 'currencies', 'page', 'quotations', 'chargesData'));
    }

    public function store(StoreAirExportRequest $request)
    {
        try {
            // DIAGNOSTIC: Field Population Report
            $allFields = [
                // Core Fields (Main Tab - Basic Info)
                'file_no', 'mawb_no', 'booking_no', 'post_date',
                'office_id', 'op_id',
                
                // Agent & Carrier Fields
                'forwarding_agent_id', 'oversea_agent_id', 'carrier_id', 'acct_carrier_id',
                
                // Flight & Route Details
                'flight_no', 'dep_port_id', 'dst_port_id',
                'etd', 'eta', 'atd', 'ata',
                
                // Quantities & Measurements
                'pkg_qty', 'pkg_unit_id', 'gross_weight', 'chargeable_weight', 'volume',
                'buying_rate', 'selling_rate',
                
                // Terms & Options
                'freight_term', 'is_ecommerce', 'sales_type', 'is_blocked',
                
                // Direct Master Fields
                'is_direct_master', 'dm_customer_id', 'dm_shipper_id', 'dm_bill_to_id',
                'dm_consignee_id', 'dm_notify_id', 'dm_sales_person_id', 'agent_ref_no',
                
                // MAWB Party Fields
                'shipper_id', 'consignee_id', 'notify_id', 'actual_shipper_id',
                
                // Additional & Extended Fields
                'cargo_type', 'display_unit', 'itn_no', 'cers_no', 'reference_no',
                'awb_date', 'cargo_ready_date', 'issuing_carrier', 'awb_type',
                'dv_carriage', 'dv_customs', 'insurance', 'wt_val', 'other_term',
                'other_charges', 'accounting_info', 'commodities',
                'internal_remark', 'color', 'label_description',
            ];

            $requestData = $request->validated();
            \Log::info("AirExportController Request Payload:", $request->all());
            if ($request->has('charges')) $requestData['charges'] = $request->input('charges');
            if ($request->has('hbls')) $requestData['hbls'] = $request->input('hbls');
            if ($request->has('routes')) $requestData['routes'] = $request->input('routes');
            if ($request->has('other_charges')) $requestData['other_charges'] = $request->input('other_charges');
            if ($request->has('accounting_info')) $requestData['accounting_info'] = $request->input('accounting_info');
            if ($request->has('commodities')) $requestData['commodities'] = $request->input('commodities');
            $filled = [];
            $empty = [];

            foreach ($allFields as $field) {
                if (isset($requestData[$field]) && $requestData[$field] !== null && $requestData[$field] !== '') {
                    $filled[] = $field;
                } else {
                    $empty[] = $field;
                }
            }

            $report = [
                'total_fields' => count($allFields),
                'filled_count' => count($filled),
                'empty_count' => count($empty),
                'filled_fields' => $filled,
                'empty_fields' => $empty,
                'percentage_filled' => round((count($filled) / count($allFields)) * 100, 2),
            ];

            \Log::info('=== AIR EXPORT CREATE - FIELD DIAGNOSTIC ===');
            \Log::info('Total Fields: ' . $report['total_fields']);
            \Log::info('Filled: ' . $report['filled_count'] . ' (' . $report['percentage_filled'] . '%)');
            \Log::info('Empty: ' . $report['empty_count']);
            \Log::info('');
            \Log::info('FILLED FIELDS:');
            foreach ($filled as $f) {
                $value = $requestData[$f];
                if (is_bool($value)) $value = $value ? 'true' : 'false';
                \Log::info('  ✓ ' . $f . ' = ' . json_encode($value));
            }
            \Log::info('');
            \Log::info('EMPTY FIELDS:');
            foreach ($empty as $f) {
                \Log::info('  ✗ ' . $f);
            }
            \Log::info('=========================================');

            $shipment = $this->airExportService->store($requestData);

            return redirect()->route('air-export.edit', $shipment->id)
                ->with('success', 'Air Export Shipment created successfully.')
                ->with('diagnostic', $report);
                
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Air Export Store - Database Error:', [
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
            
            return back()->withInput()->with('error', 'Unable to save the shipment. Please check your data and try again.');
            
        } catch (\Exception $e) {
            \Log::error('Air Export Store - General Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'An unexpected error occurred. Please try again or contact support if the problem persists.');
        }
    }

    public function edit(AirExport $airExport)
    {
        $airExport->load(['hbls.customer', 'hbls.shipper', 'hbls.consignee', 'charges.currency', 'documents']);
        
        $offices = Office::where('is_active', true)->get();
        $ports = Port::all();
        $agents = TradePartner::all();
        $users = \App\Models\User::all();
        $packageUnits = \App\Models\PackageUnit::all();
        $currencies = Currency::all();
        $carriers = TradePartner::whereIn('type', ['CR', 'CARRIER'])->orderBy('name')->get();
        if ($carriers->isEmpty()) $carriers = $agents;

        $quotations = \App\Models\Quotation::with(['customer', 'salesPerson', 'pol', 'pod', 'carrier', 'op', 'items.currency'])->forModule('Air Export')->latest()->get();
        
        $chargesData = $airExport->charges->isNotEmpty()
            ? $airExport->charges->map(fn($c) => [
                'id' => $c->id,
                'selected' => false,
                'party' => $c->party ?? 'Custom',
                'party_name_id' => $c->type === 'AP' ? ($c->vendor_id ?? '') : ($c->bill_to_id ?? ''),
                'sal' => $c->sal ?? 'Air',
                'pr' => ($c->type === 'AP' || $c->type === 'origin_cost') ? 'Pay' : 'Rec',
                'ppc' => ($c->pc === 'PREPAID') ? 'Prepaid' : 'Colle',
                'chrg_code' => $c->charge_code ?? '',
                'charge_name' => $c->charge_name ?? '',
                'currency' => $c->currency->code ?? 'USD',
                'currency_id' => $c->currency_id,
                'rate' => (float)($c->rate ?? 0),
                'qty' => (float)($c->qty ?? 1),
                'qty_type' => $c->unit ?? 'B/L',
                'roe' => (float)($c->roe ?? 1.0),
                'vat' => (float)($c->tax_percent ?? 0),
                'amount' => (float)($c->amount ?? 0),
                'total_amount' => (float)($c->total_amount ?? $c->amount ?? 0),
                'type' => $c->type,
                'vendor_id' => $c->vendor_id,
                'bill_to_id' => $c->bill_to_id,
                'inv_no' => $c->invoice_no ?? '',
                'financial_date' => $c->invoice_date ? (is_string($c->invoice_date) ? $c->invoice_date : $c->invoice_date->format('Y-m-d')) : '',
                'eq_bl_no' => $c->remark ?? '',
                'remark' => false,
                'mbl_no' => $airExport->file_no ?? ($airExport->mawb_no ?? ''),
                'is_invoiced' => $c->is_invoiced,
            ])
            : collect();
        
        return view('air-export.create', compact('airExport', 'offices', 'ports', 'agents', 'carriers', 'users', 'packageUnits', 'currencies', 'quotations', 'chargesData'));
    }

    public function update(UpdateAirExportRequest $request, AirExport $airExport)
    {
        // DIAGNOSTIC: Field Population Report for UPDATE
        $allFields = [
            // Core Fields (Main Tab - Basic Info)
            'file_no', 'mawb_no', 'booking_no', 'post_date',
            'office_id', 'op_id',
            
            // Agent & Carrier Fields
            'forwarding_agent_id', 'oversea_agent_id', 'carrier_id', 'acct_carrier_id',
            
            // Flight & Route Details
            'flight_no', 'dep_port_id', 'dst_port_id',
            'etd', 'eta', 'atd', 'ata',
            
            // Quantities & Measurements
            'pkg_qty', 'pkg_unit_id', 'gross_weight', 'chargeable_weight', 'volume',
            'buying_rate', 'selling_rate',
            
            // Terms & Options
            'freight_term', 'is_ecommerce', 'sales_type', 'is_blocked',
            
            // Direct Master Fields
            'is_direct_master', 'dm_customer_id', 'dm_shipper_id', 'dm_bill_to_id',
            'dm_consignee_id', 'dm_notify_id', 'dm_sales_person_id', 'agent_ref_no',
            
            // MAWB Party Fields
            'shipper_id', 'consignee_id', 'notify_id', 'actual_shipper_id',
            
            // Additional Fields (from validation)
            'incoterm_id', 'mark_number', 'service_term_from', 'service_term_to',
            'agent_id', 'co_loader_id', 'trans_port_id', 'trans_port1_id',
            'trans_port2_id', 'trans_port3_id', 'delivery_port_id', 'route_data',
            'cargo_type', 'display_unit', 'itn_no', 'cers_no', 'reference_no',
            'awb_date', 'cargo_ready_date', 'issuing_carrier', 'awb_type',
            'dv_carriage', 'dv_customs', 'insurance', 'wt_val', 'other_term',
            'other_charges', 'accounting_info', 'commodities',
            
            // Other Fields
            'internal_remark', 'color', 'label_description',
        ];

        $requestData = $request->validated();
        \Log::info("AirExportController Request Payload:", $request->all());
        if ($request->has('charges')) $requestData['charges'] = $request->input('charges');
        if ($request->has('hbls')) $requestData['hbls'] = $request->input('hbls');
        if ($request->has('routes')) $requestData['routes'] = $request->input('routes');
        if ($request->has('other_charges')) $requestData['other_charges'] = $request->input('other_charges');
        if ($request->has('accounting_info')) $requestData['accounting_info'] = $request->input('accounting_info');
        if ($request->has('commodities')) $requestData['commodities'] = $request->input('commodities');
        $filled = [];
        $empty = [];

        foreach ($allFields as $field) {
            if (isset($requestData[$field]) && $requestData[$field] !== null && $requestData[$field] !== '') {
                $filled[] = $field;
            } else {
                $empty[] = $field;
            }
        }

        $report = [
            'total_fields' => count($allFields),
            'filled_count' => count($filled),
            'empty_count' => count($empty),
            'filled_fields' => $filled,
            'empty_fields' => $empty,
            'percentage_filled' => round((count($filled) / count($allFields)) * 100, 2),
        ];

        \Log::info('=== AIR EXPORT UPDATE - FIELD DIAGNOSTIC ===');
        \Log::info('Shipment ID: ' . $airExport->id . ' | File No: ' . $airExport->file_no);
        \Log::info('Total Fields: ' . $report['total_fields']);
        \Log::info('Filled: ' . $report['filled_count'] . ' (' . $report['percentage_filled'] . '%)');
        \Log::info('Empty: ' . $report['empty_count']);
        \Log::info('');
        \Log::info('FILLED FIELDS:');
        foreach ($filled as $f) {
            $value = $requestData[$f];
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            if (is_array($value)) $value = json_encode($value);
            \Log::info('  ✓ ' . $f . ' = ' . $value);
        }
        \Log::info('');
        \Log::info('EMPTY FIELDS:');
        foreach ($empty as $f) {
            \Log::info('  ✗ ' . $f);
        }
        \Log::info('=========================================');

        $this->airExportService->update($airExport, $requestData);

        return back()
            ->with('success', 'Air Export Shipment updated successfully.')
            ->with('diagnostic', $report);
    }

    public function mblList(Request $request)
    {
        $query = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'overseaAgent', 'shipper', 'dmCustomer',
            'hbls',
        ]);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mawb_no', 'like', "%{$search}%")
                  ->orWhere('flight_no', 'like', "%{$search}%")
                  ->orWhereHas('shipper', fn($v) => $v->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('overseaAgent', fn($v) => $v->where('name', 'like', "%{$search}%"));
            });
        }

        // Filters
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->filled('op_id')) {
            $query->where('op_id', $request->op_id);
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        // Per-column filters from filter row
        $filterable = [
            'filter_file_no' => 'file_no',
            'filter_mawb_no' => 'mawb_no',
            'filter_etd'     => 'etd',
            'filter_eta'     => 'eta',
            'filter_shipper' => null,
            'filter_customer' => null,
            'filter_dep'     => null,
            'filter_dst'     => null,
        ];
        foreach ($filterable as $param => $column) {
            if ($val = $request->input($param)) {
                match ($param) {
                    'filter_file_no'   => $query->where('file_no', 'like', "%{$val}%"),
                    'filter_mawb_no'   => $query->where('mawb_no', 'like', "%{$val}%"),
                    'filter_etd'       => $query->where('etd', 'like', "%{$val}%"),
                    'filter_eta'       => $query->where('eta', 'like', "%{$val}%"),
                    'filter_shipper'   => $query->whereHas('shipper', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_customer'  => $query->whereHas('dmCustomer', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_dep'       => $query->whereHas('depPort', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_dst'       => $query->whereHas('dstPort', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    default => null,
                };
            }
        }

        if ($request->filled('dep_port_id')) {
            $query->where('dep_port_id', $request->dep_port_id);
        }

        if ($request->filled('dst_port_id')) {
            $query->where('dst_port_id', $request->dst_port_id);
        }

        if ($request->filled('etd_from')) {
            $query->where('etd', '>=', $request->etd_from);
        }

        if ($request->filled('etd_to')) {
            $query->where('etd', '<=', $request->etd_to);
        }

        // Sort (whitelisted)
        $sortField = $request->get('sort', 'created_at');
        $sortDir   = $request->get('dir', 'desc');
        $allowedSorts = ['file_no', 'mawb_no', 'etd', 'eta', 'created_at', 'post_date', 'flight_no'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $shipments = $query->orderBy($sortField, $sortDir)->paginate(20)->withQueryString();

        $offices = Office::where('is_active', true)->get();
        $operators = \App\Models\User::orderBy('name')->get();
        $carriers = TradePartner::whereNotNull('name')->where(function($q) { $q->where('type', 'carrier')->orWhereNull('type'); })->orderBy('name')->get();
        $ports = \App\Models\Port::all();
        $users = \App\Models\User::all();

        return view('air-export.mbl-list', compact('shipments', 'users', 'offices', 'operators', 'carriers', 'ports'));
    }

    public function hblList(Request $request)
    {
        $query = \App\Models\AirExportHbl::with([
            'airExport.office', 'airExport.depPort', 'airExport.dstPort',
            'airExport.overseaAgent',
            'customer', 'shipper', 'consignee', 'notifyParty',
            'salesPerson', 'packageUnit', 'op'
        ]);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb_no', 'like', "%{$search}%")
                  ->orWhereHas('airExport', fn($sq) => $sq->where('file_no', 'like', "%{$search}%"))
                  ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('consignee', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('shipper', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        // Per-column filters
        $allowedFilters = ['filter_hawb', 'filter_file_no', 'filter_customer',
                          'filter_consignee', 'filter_shipper', 'filter_sales',
                          'filter_dep', 'filter_dst'];
        foreach ($allowedFilters as $f) {
            if ($val = $request->input($f)) {
                match ($f) {
                    'filter_hawb'      => $query->where('hawb_no', 'like', "%{$val}%"),
                    'filter_file_no'   => $query->whereHas('airExport', fn($q) => $q->where('file_no', 'like', "%{$val}%")),
                    'filter_customer'  => $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_consignee' => $query->whereHas('consignee', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_shipper'   => $query->whereHas('shipper', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_sales'     => $query->whereHas('salesPerson', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_dep'       => $query->whereHas('airExport.depPort', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_dst'       => $query->whereHas('airExport.dstPort', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    default => null,
                };
            }
        }

        // Sort (whitelisted)
        $sortable = ['created_at', 'hawb_no', 'gross_weight', 'chargeable_weight'];
        $sortField = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'created_at';
        $sortDir   = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $hbls  = $query->paginate($request->input('per_page', 20))->withQueryString();
        $users = \App\Models\User::all();

        return view('air-export.hbl-list', compact('hbls', 'users'));
    }

    public function destroy(AirExport $airExport)
    {
        $airExport->delete();
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Shipment deleted successfully.']);
        }
        return redirect()->route('air-export.index')->with('success', 'Shipment deleted.');
    }

    public function exportCsv(Request $request)
    {
        $query = AirExport::with(['office', 'operator', 'carrier', 'depPort', 'dstPort', 'hbls', 'shipper']);

        // Apply same filters as mblList()
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_no', 'like', "%{$search}%")
                  ->orWhere('mawb_no', 'like', "%{$search}%")
                  ->orWhere('flight_no', 'like', "%{$search}%");
            });
        }
        foreach (['office_id', 'op_id', 'carrier_id', 'dep_port_id', 'dst_port_id'] as $f) {
            if ($request->filled($f)) $query->where($f, $request->$f);
        }
        if ($request->filled('etd_from')) $query->where('etd', '>=', $request->etd_from);
        if ($request->filled('etd_to')) $query->where('etd', '<=', $request->etd_to);

        // Per-column filters from filter row
        foreach (['filter_file_no', 'filter_mawb_no', 'filter_office', 'filter_shipper', 'filter_etd', 'filter_eta', 'filter_dep', 'filter_dst', 'filter_oa', 'filter_customer'] as $param) {
            if ($val = $request->input($param)) {
                match ($param) {
                    'filter_file_no'   => $query->where('file_no', 'like', "%{$val}%"),
                    'filter_mawb_no'   => $query->where('mawb_no', 'like', "%{$val}%"),
                    'filter_office'    => $query->whereHas('office', fn($q) => $q->where('code', 'like', "%{$val}%")),
                    'filter_shipper'   => $query->whereHas('shipper', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_etd'       => $query->where('etd', 'like', "%{$val}%"),
                    'filter_eta'       => $query->where('eta', 'like', "%{$val}%"),
                    'filter_dep'       => $query->whereHas('depPort', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_dst'       => $query->whereHas('dstPort', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_oa'        => $query->whereHas('overseaAgent', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_customer'  => $query->whereHas('dmCustomer', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    default => null,
                };
            }
        }

        $shipments = $query->latest()->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="air-export-mbls.csv"',
        ];
        $callback = function () use ($shipments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['File No', 'MAWB No', 'Carrier', 'ETD', 'ETA', 'Departure', 'Destination', 'Shipper', 'Flight No', 'GW (KG)', 'CW (KG)', 'Status', 'HBLs']);
            foreach ($shipments as $s) {
                fputcsv($file, [
                    $s->file_no, $s->mawb_no, $s->carrier->name ?? '',
                    $s->etd ? $s->etd->format('Y-m-d') : '',
                    $s->eta ? $s->eta->format('Y-m-d') : '',
                    $s->depPort->name ?? '', $s->dstPort->name ?? '',
                    $s->shipper->name ?? '', $s->flight_no ?? '',
                    number_format($s->gross_weight ?? 0, 2),
                    number_format($s->chargeable_weight ?? 0, 2),
                    $s->is_blocked ? 'Blocked' : 'Open',
                    $s->hbls->count(),
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_exports,id']);
        AirExport::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) deleted.']);
    }

    public function updateColor(Request $request, $id)
    {
        $shipment = AirExport::findOrFail($id);
        $request->validate(['color' => 'nullable|string|max:20']);
        $shipment->update(['color' => $request->color]);
        return response()->json(['success' => true]);
    }

    public function hblUpdateColor(Request $request, $id)
    {
        $hbl = \App\Models\AirExportHbl::findOrFail($id);
        $request->validate(['color' => 'nullable|string|max:20']);
        $hbl->update(['color' => $request->color]);
        return response()->json(['success' => true]);
    }

    public function bulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_exports,id']);
        AirExport::whereIn('id', $request->ids)->update(['is_blocked' => true]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) blocked.']);
    }

    public function bulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_exports,id']);
        AirExport::whereIn('id', $request->ids)->update(['is_blocked' => false]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' shipment(s) unblocked.']);
    }

    public function bulkChangeOp(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_exports,id', 'op_id' => 'required|exists:users,id']);
        AirExport::whereIn('id', $data['ids'])->update(['op_id' => $data['op_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' shipment(s) OP changed.']);
    }

    public function bulkChangeSales(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_exports,id', 'sales_person_id' => 'required|exists:users,id']);
        AirExport::whereIn('id', $data['ids'])->update(['dm_sales_person_id' => $data['sales_person_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' shipment(s) sales changed.']);
    }

    // ==================== HBL BULK OPERATIONS ====================

    public function hblBulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_export_hbls,id']);
        \App\Models\AirExportHbl::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) deleted.']);
    }

    public function hblBulkChangeSales(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_export_hbls,id', 'sales_person_id' => 'required|exists:users,id']);
        \App\Models\AirExportHbl::whereIn('id', $data['ids'])->update(['sales_person_id' => $data['sales_person_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' HBL(s) sales changed.']);
    }

    public function hblBulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_export_hbls,id']);
        \App\Models\AirExportHbl::whereIn('id', $request->ids)->update(['is_blocked' => true]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) blocked.']);
    }

    public function hblBulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_export_hbls,id']);
        \App\Models\AirExportHbl::whereIn('id', $request->ids)->update(['is_blocked' => false]);
        return response()->json(['success' => true, 'message' => count($request->ids) . ' HBL(s) unblocked.']);
    }

    public function hblBulkChangeOp(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:air_export_hbls,id', 'op_id' => 'required|exists:users,id']);
        \App\Models\AirExportHbl::whereIn('id', $data['ids'])->update(['op_id' => $data['op_id']]);
        return response()->json(['success' => true, 'message' => count($data['ids']) . ' HBL(s) OP changed.']);
    }

    public function hblExportCsv(Request $request)
    {
        $query = \App\Models\AirExportHbl::with(['airExport', 'customer', 'shipper', 'consignee', 'salesPerson']);

        // Apply same filters as hblList()
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hawb_no', 'like', "%{$search}%")
                  ->orWhereHas('airExport', fn($sq) => $sq->where('file_no', 'like', "%{$search}%"))
                  ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        foreach (['filter_hawb', 'filter_file_no', 'filter_customer', 'filter_shipper'] as $f) {
            if ($val = $request->input($f)) {
                match ($f) {
                    'filter_hawb'     => $query->where('hawb_no', 'like', "%{$val}%"),
                    'filter_file_no'  => $query->whereHas('airExport', fn($q) => $q->where('file_no', 'like', "%{$val}%")),
                    'filter_customer' => $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    'filter_shipper'  => $query->whereHas('shipper', fn($q) => $q->where('name', 'like', "%{$val}%")),
                    default => null,
                };
            }
        }

        $hbls = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="air-export-hbls.csv"',
        ];
        $callback = function () use ($hbls) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['HAWB No', 'File No', 'Customer', 'Shipper', 'Consignee', 'GW (KG)', 'CW (KG)', 'Sales Person', 'Created']);
            foreach ($hbls as $h) {
                fputcsv($file, [
                    $h->hawb_no,
                    $h->airExport->file_no ?? '',
                    $h->customer->name ?? '',
                    $h->shipper->name ?? '',
                    $h->consignee->name ?? '',
                    $h->gross_weight ? number_format($h->gross_weight, 2) : '0.00',
                    $h->chargeable_weight ? number_format($h->chargeable_weight, 2) : '0.00',
                    $h->salesPerson->name ?? '',
                    $h->created_at ? $h->created_at->format('Y-m-d') : '',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // ==================== CHARGES AJAX ENDPOINTS ====================

    public function addCharge(Request $request, AirExport $airExport)
    {
        $charge = $this->airExportService->createCharge($airExport, $request->all());
        $charge->load('currency');
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function updateCharge(Request $request, $chargeId)
    {
        $charge = Charge::findOrFail($chargeId);
        $this->airExportService->updateCharge($charge, $request->all());
        $charge->load('currency');
        return response()->json(['success' => true, 'charge' => $charge]);
    }

    public function deleteCharge($chargeId)
    {
        $charge = Charge::findOrFail($chargeId);
        $charge->delete();
        return response()->json(['success' => true]);
    }

    public function deleteAllCharges(AirExport $airExport)
    {
        $airExport->charges()->delete();
        return response()->json(['success' => true]);
    }

    public function getCharges(AirExport $airExport)
    {
        $charges = $airExport->charges()->with('currency')->latest()->get();
        return response()->json($charges);
    }

    // ==================== HISTORY ====================

    public function getHistory(AirExport $airExport)
    {
        $logs = $airExport->statusLogs()->with('user')->latest()->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action ?? $log->status_name,
                'details' => $log->details,
                'user' => $log->user ? $log->user->name : 'System',
                'user_initials' => $log->user ? substr($log->user->name, 0, 1) : 'S',
                'created_at' => $log->created_at ? $log->created_at->format('m-d-Y') : '',
                'created_time' => $log->created_at ? $log->created_at->format('H:i') : '',
            ];
        });

        return response()->json($logs);
    }

    public function saveInternalMessage(Request $request, AirExport $airExport)
    {
        $request->validate(['message' => 'nullable|string']);
        
        $airExport->internal_remark = $request->message;
        $airExport->save();

        return response()->json(['success' => true]);
    }

    // ==================== MEMOS ====================

    public function getMemos(AirExport $airExport)
    {
        $memos = $airExport->memos()->latest()->get();
        return response()->json($memos);
    }

    public function addMemo(Request $request, AirExport $airExport)
    {
        $data = $request->validate([
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
        ]);

        $memo = $airExport->memos()->create([
            'subject' => $data['subject'] ?? '',
            'body' => $data['body'] ?? '',
            'user_id' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'memo' => $memo]);
    }

    public function updateMemo(Request $request, $memoId)
    {
        $memo = \App\Models\AirExportMemo::findOrFail($memoId);
        $data = $request->validate([
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
        ]);
        $memo->update($data);
        return response()->json(['success' => true, 'memo' => $memo]);
    }

    public function deleteMemo($memoId)
    {
        $memo = \App\Models\AirExportMemo::findOrFail($memoId);
        $memo->delete();
        return response()->json(['success' => true]);
    }

    // ==================== EMAIL CHARGE ====================

    public function emailCharge(Request $request, AirExport $airExport)
    {
        $request->validate(['charge_id' => 'required|exists:charges,id']);
        \Illuminate\Support\Facades\Log::info('Email charge requested', [
            'shipment_id' => $airExport->id,
            'charge_id' => $request->charge_id,
        ]);
        return response()->json(['success' => true, 'message' => 'Email functionality will be implemented.']);
    }

    public function documentPackage($id, Request $request)
    {
        $shipment = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'forwardingAgent', 'overseaAgent', 'shipper', 'consignee',
            'dmCustomer', 'dmShipper', 'dmConsignee',
            'hbls.shipper', 'hbls.consignee', 'hbls.notifyParty', 'hbls.packageUnit',
            'charges'
        ])->findOrFail($id);

        $reportsStr = $request->query('reports', 'manifest,mawb_print,local_invoice,credit_debit,hawb_print,commercial_invoice,packing_list');
        $selectedReports = array_filter(explode(',', $reportsStr));
        $agentType = $request->query('agent_type', 'master');

        return view('air-export.document-package', compact('shipment', 'selectedReports', 'agentType'));
    }

    public function consolidatedManifest($id, Request $request)
    {
        $shipment = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'forwardingAgent', 'overseaAgent', 'shipper', 'consignee',
            'dmCustomer', 'dmShipper', 'dmConsignee',
            'hbls.shipper', 'hbls.consignee', 'hbls.notifyParty', 'hbls.packageUnit',
            'charges'
        ])->findOrFail($id);

        $agentType = $request->query('agent_type', 'master');

        return view('air-export.consolidated-manifest', compact('shipment', 'agentType'));
    }

    public function bookingConfirmation($id, Request $request)
    {
        $shipment = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'forwardingAgent', 'overseaAgent', 'shipper', 'consignee', 'notifyParty',
            'dmCustomer', 'dmShipper', 'dmConsignee',
            'hbls.shipper', 'hbls.consignee', 'hbls.notifyParty', 'hbls.packageUnit',
            'charges'
        ])->findOrFail($id);

        $agentType = $request->query('agent_type', 'master');

        return view('air-export.booking-confirmation', compact('shipment', 'agentType'));
    }

    public function mawbPackageLabel($id, Request $request)
    {
        $shipment = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'forwardingAgent', 'overseaAgent', 'shipper', 'consignee',
            'hbls'
        ])->findOrFail($id);

        $totalPcs = $shipment->hbls->sum('pkg_qty') ?: 1;

        return view('air-export.mawb-package-label', compact('shipment', 'totalPcs'));
    }

    public function updateLabelDescription($id, Request $request)
    {
        $shipment = AirExport::findOrFail($id);
        $shipment->update([
            'label_description' => $request->input('label_description')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Label description saved to database',
            'label_description' => $shipment->label_description
        ]);
    }

    public function packageLabelList($id, Request $request)
    {
        $shipment = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort', 'shipper', 'consignee',
            'hbls.packageUnit'
        ])->findOrFail($id);

        return view('air-export.package-label-list', compact('shipment'));
    }

    public function profitDetailView($id, Request $request)
    {
        $shipment = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'overseaAgent', 'shipper', 'consignee',
            'hbls.shipper', 'hbls.consignee', 'hbls.customer',
            'charges.currency', 'charges.billTo', 'charges.vendor'
        ])->findOrFail($id);

        $totalRevenue = $shipment->charges->where('type', 'AR')->sum('amount');
        $totalCost = $shipment->charges->whereIn('type', ['AP', 'origin_cost'])->sum('amount');
        $totalProfit = $totalRevenue - $totalCost;
        $profitPercentage = $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 2) . '%' : 'N/A';
        $profitMargin = $totalCost > 0 ? number_format(($totalCost / $totalCost) * 100, 2) . '%' : 'N/A';

        return view('air-export.profit-detail', compact(
            'shipment', 'totalRevenue', 'totalCost', 'totalProfit', 'profitPercentage', 'profitMargin'
        ));
    }

    public function profitSummaryView($id, Request $request)
    {
        $shipment = AirExport::with([
            'office', 'operator', 'carrier',
            'depPort', 'dstPort',
            'overseaAgent', 'shipper', 'consignee',
            'hbls', 'charges.currency'
        ])->findOrFail($id);

        $totalRevenue = $shipment->charges->where('type', 'AR')->sum('amount');
        $totalCost = $shipment->charges->whereIn('type', ['AP', 'origin_cost'])->sum('amount');
        $totalProfit = $totalRevenue - $totalCost;

        return view('air-export.profit-summary', compact(
            'shipment', 'totalRevenue', 'totalCost', 'totalProfit'
        ));
    }

    public function createInvoiceFromCharges(Request $request, $airExportId, \App\Services\InvoiceGenerationService $invoiceService)
    {
        $airExport = AirExport::findOrFail($airExportId);

        $savedChargeIds = [];
        if ($request->has('charges') && is_array($request->charges)) {
            foreach ($request->charges as $cData) {
                if (empty($cData['chrg_code']) && empty($cData['charge_name'])) continue;
                
                $currencyId = null;
                if (!empty($cData['currency'])) {
                    $curr = Currency::where('code', $cData['currency'])->first();
                    $currencyId = $curr?->id;
                }

                $rate = (float)($cData['rate'] ?? 0);
                $qty = (float)($cData['qty'] ?? 1);
                $roe = (float)($cData['roe'] ?? 1.0);
                $vat = (float)($cData['vat'] ?? 0);
                $amt = $rate * $qty;
                $taxAmt = $amt * ($vat / 100);
                $totAmt = $amt + $taxAmt;

                if (!empty($cData['id'])) {
                    Charge::where('id', $cData['id'])->update([
                        'party' => $cData['party'] ?? 'Custom',
                        'sal' => $cData['sal'] ?? 'Air',
                        'type' => ($cData['pr'] ?? 'Rec') === 'Pay' ? 'AP' : 'AR',
                        'pc' => ($cData['ppc'] ?? 'Colle') === 'Prepaid' ? 'PREPAID' : 'COLLECT',
                        'charge_code' => $cData['chrg_code'] ?? '',
                        'charge_name' => $cData['charge_name'] ?? '',
                        'currency_id' => $currencyId,
                        'rate' => $rate,
                        'qty' => $qty,
                        'unit' => $cData['qty_type'] ?? 'B/L',
                        'roe' => $roe,
                        'tax_percent' => $vat,
                        'amount' => $amt,
                        'tax_amount' => $taxAmt,
                        'total_amount' => $totAmt,
                    ]);
                    $savedChargeIds[] = $cData['id'];
                } else {
                    $newCharge = $airExport->charges()->create([
                        'party' => $cData['party'] ?? 'Custom',
                        'sal' => $cData['sal'] ?? 'Air',
                        'type' => ($cData['pr'] ?? 'Rec') === 'Pay' ? 'AP' : 'AR',
                        'pc' => ($cData['ppc'] ?? 'Colle') === 'Prepaid' ? 'PREPAID' : 'COLLECT',
                        'charge_code' => $cData['chrg_code'] ?? '',
                        'charge_name' => $cData['charge_name'] ?? '',
                        'currency_id' => $currencyId,
                        'rate' => $rate,
                        'qty' => $qty,
                        'unit' => $cData['qty_type'] ?? 'B/L',
                        'roe' => $roe,
                        'tax_percent' => $vat,
                        'amount' => $amt,
                        'tax_amount' => $taxAmt,
                        'total_amount' => $totAmt,
                    ]);
                    $savedChargeIds[] = $newCharge->id;
                }
            }
        }

        $query = Charge::where('chargeable_type', 'App\Models\AirExport')
            ->where('chargeable_id', $airExportId);

        if ($request->has('ids') && is_array($request->ids) && count($request->ids) > 0) {
            $query->whereIn('id', $request->ids);
        } else if (count($savedChargeIds) > 0) {
            $query->whereIn('id', $savedChargeIds);
        } else {
            $query->where(function($q) {
                $q->where('is_invoiced', false)->orWhereNull('invoice_no');
            });
        }

        $charges = $query->get();

        if ($charges->count() == 0) {
            if ($request->has('ids') && is_array($request->ids) && count($request->ids) > 0) {
                $alreadyInvoiced = Charge::where('chargeable_type', 'App\Models\AirExport')
                    ->where('chargeable_id', $airExportId)
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

            $allChargesCount = Charge::where('chargeable_type', 'App\Models\AirExport')->where('chargeable_id', $airExportId)->count();
            if ($allChargesCount > 0) {
                $existingInv = Charge::where('chargeable_type', 'App\Models\AirExport')->where('chargeable_id', $airExportId)->whereNotNull('invoice_no')->pluck('invoice_no')->first();
                return response()->json([
                    'success' => true,
                    'message' => 'Charges are already invoiced. Opening Freight Invoice...',
                    'invoice_no' => $existingInv ?? 'INV-EXISTING',
                    'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'air-export', 'id' => $airExportId])
                ]);
            }
            return response()->json(['success' => false, 'message' => 'No charges found for this shipment. Please add charges first.'], 400);
        }

        $companyId = auth()->user()->company_id ?? 1;
        $result = $invoiceService->generateInvoicesFromCharges(
            $companyId,
            'App\Models\AirExport',
            $airExportId,
            'Export',
            'Air',
            $charges
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        $invNo = implode(', ', $result['invoices']);

        // === AUTO-POPULATION INTEGRATION ===
        $shipment = AirExport::with([
            'forwardingAgent', 'carrier', 'dmShipper', 'dmConsignee', 'dmNotify', 
            'agent', 'salesPerson', 'dmCustomer', 'coLoader', 'hbls'
        ])->find($airExportId);

        $autoPopService = new ShipmentMemoAutoPopulationService();
        $autoPopulatedData = [
            'master_bl' => $autoPopService->getAutoPopulatedData('air-export', $shipment, 'master_bl'),
            'house_bl' => $autoPopService->getAutoPopulatedData('air-export', $shipment, 'house_bl'),
            'enabled_fields' => [
                'master_bl' => $autoPopService->getEnabledFields('air-export', 'master_bl'),
                'house_bl' => $autoPopService->getEnabledFields('air-export', 'house_bl'),
            ],
        ];

        return response()->json([
            'success' => true,
            'invoice_no' => $invNo,
            'freight_invoice_url' => route('shipments.freight-invoice', ['type' => 'air-export', 'id' => $airExportId]),
            'auto_populated_data' => $autoPopulatedData
        ]);
    }

    public function exportChargesToExcel($airExportId)
    {
        $airExport = AirExport::with(['charges.billTo', 'charges.vendor', 'charges.currency'])->findOrFail($airExportId);
        $charges = $airExport->charges;

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="air-export-charges-' . $airExportId . '-' . now()->format('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($charges) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Party', 'Type', 'P/C', 'Code', 'Rate', 'Qty', 'Unit', 'Amount', 'ROE', 'VAT %', 'Total Amount', 'Invoice No', 'Remark']);
            foreach ($charges as $c) {
                fputcsv($file, [
                    $c->type === 'AP' ? ($c->vendor->name ?? '--') : ($c->billTo->name ?? '--'),
                    $c->type,
                    $c->pc,
                    $c->charge_code,
                    $c->rate,
                    $c->qty,
                    $c->unit,
                    $c->amount,
                    $c->roe,
                    $c->tax_percent ?? 0,
                    $c->total_amount,
                    $c->invoice_no,
                    $c->remark
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printCharges($airExportId)
    {
        $shipment = AirExport::with(['office', 'operator', 'carrier', 'depPort', 'dstPort', 'charges.currency', 'charges.billTo', 'charges.vendor'])->findOrFail($airExportId);
        return view('air-export.print-charges', compact('shipment'));
    }

    // ==================== STATUS LOGS ====================
    
    public function getStatusLogs(Request $request, AirExport $airExport)
    {
        // Fetch activity logs for this shipment
        $logs = \App\Models\ActivityLog::where('subject_type', 'App\Models\AirExport')
            ->where('subject_id', $airExport->id)
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->description ?? 'Updated',
                    'user' => $log->causer ? $log->causer->name : 'System',
                    'user_code' => $log->causer ? $log->causer->user_id : 'SYS',
                    'date' => $log->created_at->format('m-d-Y'),
                    'time' => $log->created_at->format('H:i'),
                    'details' => $log->properties ?? [],
                ];
            });

        return response()->json($logs);
    }

    // ==================== DOC CENTER OPERATIONS ====================
    
    public function getDocuments(Request $request, AirExport $airExport)
    {
        $documents = $airExport->documents()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->original_name,
                    'file_name' => $doc->file_name,
                    'size' => $doc->file_size,
                    'type' => $doc->mime_type,
                    'uploaded_at' => $doc->created_at->format('Y-m-d H:i:s'),
                    'uploaded_by' => $doc->user ? $doc->user->name : 'Unknown',
                    'download_url' => route('air-export.documents.download', ['airExport' => $airExport->id, 'document' => $doc->id]),
                ];
            }),
        ]);
    }

    public function uploadDocuments(Request $request, AirExport $airExport)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'file|max:10240', // 10MB max
        ]);

        $uploaded = [];
        
        foreach ($request->file('documents') as $file) {
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . $originalName;
            $path = $file->storeAs('air-export-documents/' . $airExport->id, $fileName, 'public');
            
            $document = $airExport->documents()->create([
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
            ]);
            
            $uploaded[] = [
                'id' => $document->id,
                'name' => $document->original_name,
                'size' => $document->file_size,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' document(s) uploaded successfully',
            'documents' => $uploaded,
        ]);
    }

    public function downloadDocument(AirExport $airExport, $documentId)
    {
        $document = $airExport->documents()->findOrFail($documentId);
        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return response()->download($filePath, $document->original_name);
    }

    public function deleteDocument(AirExport $airExport, $documentId)
    {
        $document = $airExport->documents()->findOrFail($documentId);
        
        // Delete physical file
        $filePath = storage_path('app/public/' . $document->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $document->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ]);
    }

    public function hawbPrint($id, $hawbIndex)
    {
        if (file_exists(base_path('run_replace.php'))) {
            require_once base_path('run_replace.php');
        }
        // Ensure the blank HAWB template PNG converted from PDF exists
        $pngPath = public_path('hbl-backgrounds/hawb-blank-1.png');
        if (!file_exists($pngPath)) {
            $pdfPath = base_path('templateforhawbblank/HAWB BLANK COPY.pdf');
            if (file_exists($pdfPath)) {
                $outputPrefix = public_path('hbl-backgrounds/hawb-blank');
                @exec("pdftoppm -png -r 300 " . escapeshellarg($pdfPath) . " " . escapeshellarg($outputPrefix));
            }
        }

        $shipment = AirExport::with([
            'office', 'operator', 'forwardingAgent', 'overseaAgent', 
            'carrier', 'depPort', 'dstPort', 'packageUnit', 
            'hbls.shipper', 'hbls.consignee', 'hbls.notifyParty', 
            'hbls.customer', 'hbls.packageUnit'
        ])->findOrFail($id);

        $hbl = $shipment->hbls->values()->get($hawbIndex);
        if (!$hbl) {
            $hbl = $shipment->hbls->first();
        }

        $mawbNo = $shipment->mawb_no ?? '';
        $airlineCode = '';
        $serialNo = '';
        if (str_contains($mawbNo, '-')) {
            $parts = explode('-', $mawbNo, 2);
            $airlineCode = $parts[0];
            $serialNo = $parts[1];
        } elseif (strlen($mawbNo) >= 3) {
            $airlineCode = substr($mawbNo, 0, 3);
            $serialNo = substr($mawbNo, 3);
        }

        $getCountryName = function($country) {
            if (is_null($country)) return '';
            if (is_string($country) || is_numeric($country)) return (string)$country;
            return $country->name ?? $country->code ?? '';
        };

        $shipperStr = '';
        if ($hbl && $hbl->shipper) {
            $countryName = $getCountryName($hbl->shipper->country);
            $cityLine = implode(', ', array_filter([$hbl->shipper->city, $hbl->shipper->state, $hbl->shipper->zip, $countryName]));
            $shipperStr = implode("\n", array_filter([$hbl->shipper->name, $hbl->shipper->address, $cityLine]));
        } elseif ($shipment->shipper) {
            $shipperStr = implode("\n", array_filter([$shipment->shipper->name, $shipment->shipper->address]));
        }

        $consigneeStr = '';
        if ($hbl && $hbl->consignee) {
            $countryName = $getCountryName($hbl->consignee->country);
            $cityLine = implode(', ', array_filter([$hbl->consignee->city, $hbl->consignee->state, $hbl->consignee->zip, $countryName]));
            $consigneeStr = implode("\n", array_filter([$hbl->consignee->name, $hbl->consignee->address, $cityLine]));
        }

        $agentStr = '';
        if ($shipment->forwardingAgent) {
            $countryName = $getCountryName($shipment->forwardingAgent->country);
            $cityLine = trim(($shipment->forwardingAgent->city ?? '') . ' ' . $countryName);
            $agentStr = implode("\n", array_filter([$shipment->forwardingAgent->name, $shipment->forwardingAgent->address, $cityLine]));
        } else {
            $agentStr = "FREIGHTX LOGISTICS INC\n71246 FISHER ESTATE APT. 970\nNORTH KELSEY, KS 97983-3182\nUNITED STATES";
        }

        $notifyStr = '';
        if ($hbl && $hbl->notifyParty) {
            $countryName = $getCountryName($hbl->notifyParty->country);
            $cityLine = implode(', ', array_filter([$hbl->notifyParty->city, $hbl->notifyParty->state, $hbl->notifyParty->zip, $countryName]));
            $notifyStr = implode("\n", array_filter(['NOTIFY: ' . $hbl->notifyParty->name, $hbl->notifyParty->address, $cityLine]));
        } elseif ($shipment->notifyParty) {
            $countryName = $getCountryName($shipment->notifyParty->country);
            $cityLine = implode(', ', array_filter([$shipment->notifyParty->city, $shipment->notifyParty->state, $shipment->notifyParty->zip, $countryName]));
            $notifyStr = implode("\n", array_filter(['NOTIFY: ' . $shipment->notifyParty->name, $shipment->notifyParty->address, $cityLine]));
        } else {
            $notifyStr = "NOTIFY: SAME AS CONSIGNEE";
        }

        $overseaStr = '';
        if ($shipment->overseaAgent) {
            $countryName = $getCountryName($shipment->overseaAgent->country);
            $cityLine = implode(', ', array_filter([$shipment->overseaAgent->city, $shipment->overseaAgent->state, $shipment->overseaAgent->zip, $countryName]));
            $overseaStr = implode("\n", array_filter(['OVERSEA AGENT: ' . $shipment->overseaAgent->name, $shipment->overseaAgent->address, $cityLine]));
        } else {
            $overseaStr = "OVERSEA AGENT: FREIGHTX GLOBAL NETWORK\nLONDON OFFICE, UK";
        }

        $pkgQty = ($hbl->pkg_qty ?? 0) ?: ($shipment->pkg_qty ?? 0);
        $pkgUnit = $hbl?->packageUnit?->code ?? $shipment?->packageUnit?->code ?? 'CARTON(S)';

        $data = [
            'hawb_number' => $hbl->hawb_no ?? ('MAH-' . str_pad($shipment->id, 7, '0', STR_PAD_LEFT)),
            'mawb_number' => $mawbNo,
            'airline_code' => $airlineCode ?: '016',
            'departure_code' => $shipment->depPort?->code ?? 'LAX',
            'serial_no' => $serialNo ?: '2034 6045',
            'file_no' => $shipment->file_no ?? ('MAE-' . date('Y') . str_pad($shipment->id, 4, '0', STR_PAD_LEFT)),
            
            'shipper' => $shipperStr,
            'shipper_account_no' => $hbl->shipper?->account_no ?? '',

            'consignee' => $consigneeStr,
            'consignee_account_no' => $hbl->consignee?->account_no ?? '',

            'issuing_agent' => $agentStr,
            'agent_iata_code' => $shipment->forwardingAgent?->iata_code ?? '',
            'agent_account_no' => $shipment->forwardingAgent?->account_no ?? '',
            
            'notify_info' => $notifyStr,
            'oversea_info' => $overseaStr,

            'airport_departure' => ($shipment->depPort?->code ? '(' . $shipment->depPort->code . ') ' : '') . ($shipment->depPort?->name ?? 'LOS ANGELES INTL'),
            'airport_destination' => ($shipment->dstPort?->code ? '(' . $shipment->dstPort->code . ') ' : '') . ($shipment->dstPort?->name ?? 'LONDON HEATHROW'),
            'routing_to1' => $shipment->dstPort?->code ?? 'LHR',
            'routing_by1' => $shipment->carrier?->name ?? '3M COMPANY',
            'routing_to2' => '',
            'routing_by2' => '',
            'routing_to3' => '',
            'routing_by3' => '',
            
            'requested_flight_no' => $shipment->flight_no ?? 'UA923',
            'requested_flight_date' => $shipment->etd ? (is_string($shipment->etd) ? substr($shipment->etd, 5, 5) : $shipment->etd->format('m-d')) : '05-20',
            'currency' => 'CAD',
            'chgs_code' => 'PP',
            'wt_val_ppd' => true,
            'wt_val_coll' => false,
            'other_ppd' => true,
            'other_coll' => false,
            'dv_carriage' => $hbl->dv_carriage ?? 'NVD',
            'dv_customs' => $hbl->dv_customs ?? 'NCV',
            'insurance_amount' => $hbl->insurance ?? 'XXX',

            'accounting_info' => $hbl->hbl_remark ?? '',
            'handling_info' => $hbl->handling_info ?? $shipment->handling_info ?? '',
            'export_destination' => 'UNITED KINGDOM',

            'pkg_qty' => $pkgQty ? (number_format($pkgQty) . "\n" . $pkgUnit) : ("0\n" . $pkgUnit),
            'gross_weight' => ($hbl->gross_weight ?? 0) ?: ($shipment->gross_weight ?? 0),
            'weight_unit' => 'kg',
            'rate_class' => '',
            'commodity_item_no' => '',
            'chargeable_weight' => ($hbl->chargeable_weight ?? 0) ?: ($shipment->chargeable_weight ?? 0),
            'rate_charge' => ($hbl->selling_rate ?? 0) ?: ($shipment->selling_rate ?? 0),
            'total_charge' => 'AS ARRANGED',
            'nature_quantity' => ($hbl->commodity ?? $hbl->description ?? '"FREIGHT PREPAID"'),

            'prepaid_weight_charge' => 'AS ARRANGED',
            'collect_weight_charge' => '',
            'prepaid_valuation' => '',
            'collect_valuation' => '',
            'prepaid_tax' => '',
            'collect_tax' => '',
            'due_agent_prepaid' => '',
            'due_agent_collect' => '',
            'due_carrier_prepaid' => '',
            'due_carrier_collect' => '',
            'total_prepaid' => 'AS ARRANGED',
            'total_collect' => '',

            'executed_date' => $shipment->post_date ? (is_string($shipment->post_date) ? substr($shipment->post_date, 0, 10) : $shipment->post_date->format('m-d-Y')) : date('m-d-Y'),
            'executed_place' => $shipment->depPort?->code ?? 'LAX',
            'signature_carrier' => ($shipment->carrier?->name ?? 'FREIGHTX') . "\n" . ($shipment->forwardingAgent?->name ?? '3M COMPANY'),
        ];

        return view('air-export.hawb-print', compact('data', 'shipment', 'hbl', 'hawbIndex'));
    }
}
