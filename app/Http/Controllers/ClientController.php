<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;
use App\Models\AirImport;
use App\Models\AirExport;
use App\Models\Invoice;
use App\Models\OceanImport;
use App\Models\OceanExport;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index()
    {
        $loggedInUser = auth()->user();

        $companyId = $loggedInUser->company_id;
        $customerId = $loggedInUser->trade_partner_id;


        /*
        |--------------------------------------------------------------------------
        | AIR IMPORT
        |--------------------------------------------------------------------------
        */

        $airImports = AirImport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | AIR EXPORT
        |--------------------------------------------------------------------------
        */

        $airExports = AirExport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | OCEAN IMPORT
        |--------------------------------------------------------------------------
        */

        $oceanImports = OceanImport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | OCEAN EXPORT
        |--------------------------------------------------------------------------
        */

        $oceanExports = OceanExport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | MERGE ALL SHIPMENTS
        |--------------------------------------------------------------------------
        */

        $allShipments = collect();

        foreach ($airImports as $shipment) {
            $shipment->shipment_mode = 'Air Import';
            $allShipments->push($shipment);
        }

        foreach ($airExports as $shipment) {
            $shipment->shipment_mode = 'Air Export';
            $allShipments->push($shipment);
        }

        foreach ($oceanImports as $shipment) {
            $shipment->shipment_mode = 'Ocean Import';
            $allShipments->push($shipment);
        }

        foreach ($oceanExports as $shipment) {
            $shipment->shipment_mode = 'Ocean Export';
            $allShipments->push($shipment);
        }


        /*
        |--------------------------------------------------------------------------
        | SORT ALL SHIPMENTS
        |--------------------------------------------------------------------------
        */

        $allShipments = $allShipments
            ->sortByDesc('created_at')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | RECENT SHIPMENTS
        |--------------------------------------------------------------------------
        */

        $recentShipments = $allShipments
            ->take(5);


        /*
        |--------------------------------------------------------------------------
        | TOTAL SHIPMENTS
        |--------------------------------------------------------------------------
        */

        $totalShipments = $allShipments->count();


        /*
        |--------------------------------------------------------------------------
        | IN TRANSIT
        |--------------------------------------------------------------------------
        */

        $inTransit = $allShipments->filter(function ($shipment) {

            return in_array(
                strtolower($shipment->status ?? ''),
                [
                    'in transit',
                    'transit',
                    'in_transit'
                ]
            );

        })->count();


        /*
        |--------------------------------------------------------------------------
        | ARRIVED
        |--------------------------------------------------------------------------
        */

        $arrived = $allShipments->filter(function ($shipment) {

            return strtolower($shipment->status ?? '') === 'arrived';

        })->count();


        /*
        |--------------------------------------------------------------------------
        | DELIVERED
        |--------------------------------------------------------------------------
        */

        $delivered = $allShipments->filter(function ($shipment) {

            return in_array(
                strtolower($shipment->status ?? ''),
                [
                    'delivered',
                    'complete',
                    'completed'
                ]
            );

        })->count();


        /*
        |--------------------------------------------------------------------------
        | PENDING
        |--------------------------------------------------------------------------
        */

        $pending = $allShipments->filter(function ($shipment) {

            return in_array(
                strtolower($shipment->status ?? ''),
                [
                    'pending',
                    'pending booking',
                    'pending_documents',
                    'pending documents'
                ]
            );

        })->count();


        /*
        |--------------------------------------------------------------------------
        | INVOICE DATA
        |--------------------------------------------------------------------------
        */

        $invoices = Invoice::where('company_id', $companyId)
            ->where('bill_to_id', $customerId)
            ->where('type', 'AR')
            ->whereNull('deleted_at')
            ->latest()
            ->get();


        $totalInvoices = $invoices->count();

        $totalInvoiceAmount = $invoices->sum('total_amount');

        $paidInvoiceAmount = $invoices->sum('paid_amount');

        $outstandingInvoiceAmount = $invoices->sum('balance_amount');

        $overdueInvoiceAmount = $invoices
            ->filter(function ($invoice) {

                return strtolower($invoice->status ?? '') === 'overdue';

            })
            ->sum('balance_amount');


        /*
        |--------------------------------------------------------------------------
        | UPCOMING SHIPMENTS
        |--------------------------------------------------------------------------
        */

        $upcomingShipments = $allShipments
            ->filter(function ($shipment) {

                return !empty($shipment->etd)
                    && $shipment->etd >= now();

            })
            ->sortBy('etd')
            ->take(5)
            ->values();


        /*
        |--------------------------------------------------------------------------
        | SHIPMENT STATUS COUNTS
        |--------------------------------------------------------------------------
        */

        $shipmentStatus = [
            'inTransit' => $inTransit,
            'arrived'   => $arrived,
            'delivered' => $delivered,
            'pending'   => $pending,
        ];


        return view('client.clientdashboard', compact(
            'loggedInUser',

            'airImports',
            'airExports',
            'oceanImports',
            'oceanExports',

            'allShipments',
            'recentShipments',
            'upcomingShipments',

            'totalShipments',
            'inTransit',
            'arrived',
            'delivered',
            'pending',

            'shipmentStatus',

            'invoices',
            'totalInvoices',
            'totalInvoiceAmount',
            'paidInvoiceAmount',
            'outstandingInvoiceAmount',
            'overdueInvoiceAmount'
        ));
    }
       
    


    public function shipments()
    {
        $loggedInUser = auth()->user();
        $companyId = $loggedInUser->company_id;
        $customerId = $loggedInUser->trade_partner_id;

        // Filter closure: match customer, shipper, or consignee for the logged-in trade partner
        $filterClosure = function ($q) use ($customerId) {
            $q->where('dm_customer_id', $customerId)
              ->orWhere('dm_shipper_id', $customerId)
              ->orWhere('dm_consignee_id', $customerId);
        };

        // Air Import
        $airImports = AirImport::where('company_id', $companyId)
            ->where($filterClosure)
            ->with(['depPort', 'dstPort', 'carrier', 'dmCustomer', 'dmShipper', 'dmConsignee', 'dmNotify', 'hbls'])
            ->latest()
            ->get();

        // Air Export
        $airExports = AirExport::where('company_id', $companyId)
            ->where($filterClosure)
            ->with(['depPort', 'dstPort', 'carrier', 'dmCustomer', 'dmShipper', 'dmConsignee', 'dmNotify', 'hbls'])
            ->latest()
            ->get();

        // Ocean Import
        $oceanImports = OceanImport::where('company_id', $companyId)
            ->where($filterClosure)
            ->with(['portOfLoading', 'portOfDischarge', 'placeOfDelivery', 'finalDestination', 'receipt', 'vessel', 'carrier', 'dmCustomer', 'dmShipper', 'dmConsignee', 'dmNotify', 'containers.containerType', 'containers.packageUnit', 'hbls'])
            ->latest()
            ->get();

        // Ocean Export
        $oceanExports = OceanExport::where('company_id', $companyId)
            ->where($filterClosure)
            ->with(['portOfLoading', 'portOfDischarge', 'placeOfDelivery', 'finalDestination', 'placeOfReceipt', 'vessel', 'carrier', 'dmCustomer', 'dmShipper', 'dmConsignee', 'dmNotify', 'containers.containerType', 'containers.packageUnit', 'hbls'])
            ->latest()
            ->get();

        return view('client.shipments', compact(
            'airImports',
            'airExports',
            'oceanImports',
            'oceanExports'
        ));
    }

    public function tracking(Request $request)
    {
        $loggedInUser = auth()->user();
        $companyId = $loggedInUser->company_id;
        $customerId = $loggedInUser->trade_partner_id;

        $searchNumber = strtoupper(trim((string) $request->get('q', $request->get('number', ''))));
        $activeTracking = null;
        $errorMessage = null;

        // Fetch real ocean import shipments for this customer
        $oceanImports = OceanImport::with(['portOfLoading', 'portOfDischarge', 'vessel', 'containers'])
            ->where('company_id', $companyId)
            ->when($customerId, function ($q) use ($customerId) {
                $q->where(function ($sub) use ($customerId) {
                    $sub->where('dm_customer_id', $customerId)
                        ->orWhere('dm_shipper_id', $customerId)
                        ->orWhere('dm_consignee_id', $customerId);
                });
            })
            ->latest()
            ->take(20)
            ->get();

        if ($searchNumber) {
            // First check if already synced in database
            $activeTracking = \App\Models\ShipmentTracking::with(['events'])
                ->where('tracking_number', $searchNumber)
                ->first();

            // If not found in DB, query the real carrier API
            if (!$activeTracking) {
                try {
                    $activeTracking = \App\Services\Tracking\TrackingManager::syncTracking($searchNumber);
                } catch (\Throwable $e) {
                    $errorMessage = $e->getMessage();
                }
            }
        }

        return view('client.tracking', compact('oceanImports', 'activeTracking', 'searchNumber', 'errorMessage'));
    }

    public function bookings()
    {
        return view('client.booking');
    }

    public function documents()
    {
        return view('client.document');
    }

    public function requests()
    {
        return view('client.request');
    }

    public function messages()
    {
        return view('client.message');
    }

    public function notifications()
    {
        $loggedInUser = auth()->user();
        $companyId = $loggedInUser->company_id;
        $customerId = $loggedInUser->trade_partner_id;

        $filterClosure = function ($q) use ($customerId) {
            $q->where('dm_customer_id', $customerId)
              ->orWhere('dm_shipper_id', $customerId)
              ->orWhere('dm_consignee_id', $customerId);
        };

        $notifications = collect();

        // 1. Fetch Ocean Imports
        $oceanImports = OceanImport::where('company_id', $companyId)
            ->where($filterClosure)
            ->with(['portOfLoading', 'portOfDischarge', 'vessel', 'carrier', 'hbls'])
            ->latest()
            ->take(30)
            ->get();

        foreach ($oceanImports as $oi) {
            $pol = $oi->portOfLoading->name ?? ($oi->portOfLoading->code ?? 'Origin Port');
            $pod = $oi->portOfDischarge->name ?? ($oi->portOfDischarge->code ?? 'Discharge Port');
            $vessel = $oi->vessel->name ?? ($oi->carrier->name ?? 'Ocean Carrier');
            $fileNo = $oi->file_no ?? 'MOI-'.$oi->id;
            $mbl = $oi->mbl_no ?? '-';

            // Event A: Shipment Booking Created
            $notifications->push([
                'id' => 'oi-created-'.$oi->id,
                'category' => 'shipment',
                'type' => 'Ocean Import',
                'icon' => 'bi-water',
                'icon_color' => '#1d4ed8',
                'icon_bg' => '#eff6ff',
                'priority' => 'info',
                'title' => 'Ocean Import Booking Placed: ' . $fileNo,
                'message' => "Shipment scheduled for maritime corridor {$pol} &rarr; {$pod}. Carrier: {$vessel}. Master Ref: {$mbl}.",
                'ref_no' => $fileNo,
                'link' => route('client.shipments'),
                'link_text' => 'View Shipment',
                'time' => $oi->created_at ?? now(),
                'status' => 'Booking Confirmed',
                'badge' => 'Shipment Created'
            ]);

            // Event B: Departure (ATD or In Transit status)
            if ($oi->atd || stripos($oi->status, 'transit') !== false || stripos($oi->status, 'departed') !== false) {
                $notifications->push([
                    'id' => 'oi-departed-'.$oi->id,
                    'category' => 'milestone',
                    'type' => 'Maritime Transit',
                    'icon' => 'bi-arrow-repeat',
                    'icon_color' => '#0284c7',
                    'icon_bg' => '#f0f9ff',
                    'priority' => 'milestone',
                    'title' => 'Vessel In Transit: ' . $fileNo,
                    'message' => "Vessel {$vessel} is en route to destination port {$pod}. Estimated Arrival: " . ($oi->eta ? \Carbon\Carbon::parse($oi->eta)->format('d M Y') : 'TBA') . ".",
                    'ref_no' => $fileNo,
                    'link' => route('client.tracking') . '?number=' . urlencode($oi->mbl_no ?: $fileNo),
                    'link_text' => 'Track Live Vessel',
                    'time' => $oi->atd ? \Carbon\Carbon::parse($oi->atd) : ($oi->created_at ? $oi->created_at->addHours(4) : now()),
                    'status' => 'In Transit',
                    'badge' => 'Transit Update'
                ]);
            }

            // Event C: Arrival / Port Berth
            if (stripos($oi->status, 'arrived') !== false || stripos($oi->status, 'port') !== false) {
                $notifications->push([
                    'id' => 'oi-arrived-'.$oi->id,
                    'category' => 'milestone',
                    'type' => 'Port Arrival',
                    'icon' => 'bi-geo-alt',
                    'icon_color' => '#059669',
                    'icon_bg' => '#ecfdf5',
                    'priority' => 'action',
                    'title' => 'Cargo Arrived at Destination Port: ' . $fileNo,
                    'message' => "Consignment has safely berthed at {$pod}. Discharge and terminal verification in progress.",
                    'ref_no' => $fileNo,
                    'link' => route('client.shipments'),
                    'link_text' => 'Check Customs',
                    'time' => $oi->ata ? \Carbon\Carbon::parse($oi->ata) : now(),
                    'status' => 'Arrived at Port',
                    'badge' => 'Discharge Complete'
                ]);
            }

            // Event D: Delivery / Final Release
            if (stripos($oi->status, 'delivered') !== false || stripos($oi->status, 'complete') !== false) {
                $notifications->push([
                    'id' => 'oi-delivered-'.$oi->id,
                    'category' => 'milestone',
                    'type' => 'Final Delivery',
                    'icon' => 'bi-check2-circle',
                    'icon_color' => '#059669',
                    'icon_bg' => '#ecfdf5',
                    'priority' => 'success',
                    'title' => 'Shipment Delivered Successfully: ' . $fileNo,
                    'message' => "Shipment {$fileNo} has cleared customs and container cargo was successfully delivered to consignee.",
                    'ref_no' => $fileNo,
                    'link' => route('client.shipments'),
                    'link_text' => 'POD Archive',
                    'time' => $oi->updated_at ?? now(),
                    'status' => 'Delivered',
                    'badge' => 'Delivered'
                ]);
            }
        }

        // 2. Fetch Ocean Exports
        $oceanExports = OceanExport::where('company_id', $companyId)
            ->where($filterClosure)
            ->with(['portOfLoading', 'portOfDischarge', 'vessel', 'carrier', 'hbls'])
            ->latest()
            ->take(30)
            ->get();

        foreach ($oceanExports as $oe) {
            $pol = $oe->portOfLoading->name ?? ($oe->portOfLoading->code ?? 'POL');
            $pod = $oe->portOfDischarge->name ?? ($oe->portOfDischarge->code ?? 'POD');
            $fileNo = $oe->file_no ?? 'MOE-'.$oe->id;

            $notifications->push([
                'id' => 'oe-created-'.$oe->id,
                'category' => 'shipment',
                'type' => 'Ocean Export',
                'icon' => 'bi-ship',
                'icon_color' => '#0d9488',
                'icon_bg' => '#f0fdfa',
                'priority' => 'info',
                'title' => 'Ocean Export Booking Confirmed: ' . $fileNo,
                'message' => "Outbound maritime freight scheduled from {$pol} to {$pod}. Carrier booking confirmed.",
                'ref_no' => $fileNo,
                'link' => route('client.shipments'),
                'link_text' => 'View Shipment',
                'time' => $oe->created_at ?? now(),
                'status' => $oe->status ?? 'Booking Placed',
                'badge' => 'Export Booking'
            ]);
        }

        // 3. Fetch Air Freight
        $airImports = AirImport::where('company_id', $companyId)
            ->where($filterClosure)
            ->with(['depPort', 'dstPort', 'carrier'])
            ->latest()
            ->take(20)
            ->get();

        foreach ($airImports as $ai) {
            $dep = $ai->depPort->name ?? ($ai->depPort->code ?? 'Origin Airport');
            $dst = $ai->dstPort->name ?? ($ai->dstPort->code ?? 'Dest Airport');
            $fileNo = $ai->file_no ?? 'MAI-'.$ai->id;

            $notifications->push([
                'id' => 'ai-created-'.$ai->id,
                'category' => 'shipment',
                'type' => 'Air Freight',
                'icon' => 'bi-airplane',
                'icon_color' => '#6366f1',
                'icon_bg' => '#eef2ff',
                'priority' => 'info',
                'title' => 'Air Freight Shipment Issued: ' . $fileNo,
                'message' => "Air cargo movement logged from {$dep} to {$dst}. Flight: " . ($ai->flight_no ?: 'Scheduled') . ". MAWB: " . ($ai->mawb_no ?: '-') . ".",
                'ref_no' => $fileNo,
                'link' => route('client.shipments'),
                'link_text' => 'View Flight Schedule',
                'time' => $ai->created_at ?? now(),
                'status' => $ai->status ?? 'In Transit',
                'badge' => 'Air Freight'
            ]);
        }

        // 4. Fetch Invoices for this customer
        $invoices = \Illuminate\Support\Facades\DB::table('invoices as i')
            ->leftJoin('currencies as c', 'c.id', '=', 'i.currency_id')
            ->where('i.company_id', $companyId)
            ->where('i.bill_to_id', $customerId)
            ->where('i.type', 'AR')
            ->whereIn('i.status', ['POSTED', 'PARTIAL', 'PAID'])
            ->whereNull('i.deleted_at')
            ->latest('i.invoice_date')
            ->take(20)
            ->get(['i.id', 'i.invoice_no', 'i.invoice_date', 'i.due_date', 'i.total_amount', 'i.paid_amount', 'i.balance_amount', 'i.status', 'c.code as currency_code']);

        foreach ($invoices as $inv) {
            $curr = $inv->currency_code ?: 'USD';
            $amt = number_format((float) $inv->total_amount, 2);

            $notifications->push([
                'id' => 'inv-posted-'.$inv->id,
                'category' => 'invoice',
                'type' => 'Commercial Billing',
                'icon' => 'bi-receipt-cutoff',
                'icon_color' => '#d97706',
                'icon_bg' => '#fffbeb',
                'priority' => ($inv->status === 'PAID') ? 'success' : 'action',
                'title' => 'AR Commercial Invoice Issued: ' . $inv->invoice_no,
                'message' => "Official invoice #{$inv->invoice_no} has been posted for {$curr} {$amt}. Status: {$inv->status}. Due Date: " . ($inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : 'Net 30') . ".",
                'ref_no' => $inv->invoice_no,
                'link' => route('cp.invoices.show', $inv->id),
                'link_text' => 'Review Invoice',
                'time' => $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date) : now(),
                'status' => $inv->status,
                'badge' => 'Invoice ' . $inv->status
            ]);
        }

        // 5. Fetch Quotations for this customer
        $quotes = \Illuminate\Support\Facades\DB::table('quotations as q')
            ->leftJoin('ports as pol', 'pol.id', '=', 'q.pol_id')
            ->leftJoin('ports as pod', 'pod.id', '=', 'q.pod_id')
            ->where('q.company_id', $companyId)
            ->where('q.customer_id', $customerId)
            ->where('q.status', '!=', 'Draft')
            ->whereNull('q.deleted_at')
            ->latest('q.quote_date')
            ->take(20)
            ->get(['q.id', 'q.quote_no', 'q.quote_date', 'q.expiry_date', 'q.transport_mode', 'q.commodity', 'q.status', 'pol.name as pol_name', 'pod.name as pod_name']);

        foreach ($quotes as $q) {
            $notifications->push([
                'id' => 'quote-sent-'.$q->id,
                'category' => 'quote',
                'type' => 'Freight Quotation',
                'icon' => 'bi-file-earmark-check',
                'icon_color' => '#4f46e5',
                'icon_bg' => '#eef2ff',
                'priority' => 'info',
                'title' => 'Freight Quotation Active: ' . $q->quote_no,
                'message' => "Quotation #{$q->quote_no} ready for review on route " . ($q->pol_name ?: 'POL') . " &rarr; " . ($q->pod_name ?: 'POD') . ". Valid until " . ($q->expiry_date ? \Carbon\Carbon::parse($q->expiry_date)->format('d M Y') : 'Open') . ".",
                'ref_no' => $q->quote_no,
                'link' => route('cp.quotes.show', $q->id),
                'link_text' => 'Review Quotation',
                'time' => $q->quote_date ? \Carbon\Carbon::parse($q->quote_date) : now(),
                'status' => $q->status,
                'badge' => 'Quotation ' . $q->status
            ]);
        }

        // Chronological Sort: newest events first
        $sortedNotifications = $notifications->sortByDesc(function ($item) {
            return $item['time'] instanceof \Carbon\Carbon ? $item['time']->timestamp : strtotime($item['time']);
        })->values();

        $totalNotifications = $sortedNotifications->count();
        $shipmentCount = $sortedNotifications->where('category', 'shipment')->count();
        $milestoneCount = $sortedNotifications->where('category', 'milestone')->count();
        $invoiceCount = $sortedNotifications->where('category', 'invoice')->count();
        $quoteCount = $sortedNotifications->where('category', 'quote')->count();

        return view('client.notification', compact(
            'sortedNotifications',
            'totalNotifications',
            'shipmentCount',
            'milestoneCount',
            'invoiceCount',
            'quoteCount'
        ));
    }

    public function reports()
    {
        return view('client.report');
    }

    public function company()
    {
        return view('client.company');
    }

    public function settings()
    {
        return view('client.setting');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }

    public function search(Request $request)
{
    $q = trim((string) $request->query('q', ''));
 
    if (mb_strlen($q) < 2) {
        return response()->json(['results' => []]);
    }
 
    // % aur _ ko escape kar do taake user wildcard na laga sake
    $like = '%' . addcslashes($q, '%_\\') . '%';
 
    $companyId = auth()->user()->company_id;
 
    // id = shipments page ki section id, refs = file_no ke ilawa jin columns mein bhi search ho
    $tabs = [
        ['id' => 'air-import',   'label' => 'Air Import',   'mode' => 'air',   'model' => \App\Models\AirImport::class,   'refs' => ['mawb_no']],
        ['id' => 'air-export',   'label' => 'Air Export',   'mode' => 'air',   'model' => \App\Models\AirExport::class,   'refs' => ['mawb_no']],
        ['id' => 'ocean-import', 'label' => 'Ocean Import', 'mode' => 'ocean', 'model' => \App\Models\OceanImport::class, 'refs' => ['mbl_no']],
        ['id' => 'ocean-export', 'label' => 'Ocean Export', 'mode' => 'ocean', 'model' => \App\Models\OceanExport::class, 'refs' => ['mbl_no', 'booking_no']],
    ];
 
    $found = [];   // file_no => ['file_no' => ..., 'tabs' => [...]]
 
    foreach ($tabs as $tab) {
        $rows = $tab['model']::query()
            ->where('company_id', $companyId)            // <-- client.shipments wali hi filter
            ->where(function ($w) use ($like, $tab) {
                $w->where('file_no', 'like', $like);
                foreach ($tab['refs'] as $col) {
                    $w->orWhere($col, 'like', $like);
                }
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get(['file_no']);
 
        foreach ($rows as $row) {
            $key = $row->file_no;
            if (empty($key)) continue;
 
            $found[$key]['file_no'] = $key;
            $found[$key]['tabs'][]  = [
                'id'    => $tab['id'],
                'label' => $tab['label'],
                'mode'  => $tab['mode'],
            ];
        }
    }
 
    return response()->json([
        'results' => array_slice(array_values($found), 0, 8),
    ]);
}
 
}