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


// Air Import
$airImports = AirImport::where('company_id', $companyId)
    ->where('dm_customer_id', $customerId)
    ->latest()
    ->get();


// Air Export
$airExports = AirExport::where('company_id', $companyId)
    ->where('dm_customer_id', $customerId)
    ->latest()
    ->get();


// Ocean Import
$oceanImports = OceanImport::where('company_id', $companyId)
    ->where('dm_customer_id', $customerId)
    ->latest()
    ->get();


// Ocean Export
$oceanExports = OceanExport::where('company_id', $companyId)
    ->where('dm_customer_id', $customerId)
    ->latest()
    ->get();


return view('client.shipments', compact(
    'airImports',
    'airExports',
    'oceanImports',
    'oceanExports'
));
    }

    public function tracking()
    {
        return view('client.tracking');
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
        return view('client.notification');
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