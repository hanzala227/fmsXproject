<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AirImport;
use App\Models\AirExport;
use App\Models\OceanImport;
use App\Models\OceanExport;
use App\Models\Invoice;
use App\Models\Quotation;

class ClientReportController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Logged-in Customer
        |--------------------------------------------------------------------------
        */

        $loggedInUser = auth()->user();

        $companyId = $loggedInUser->company_id;
        $customerId = $loggedInUser->trade_partner_id;


        /*
        |--------------------------------------------------------------------------
        | Shipment Data
        |--------------------------------------------------------------------------
        */

        $airImports = AirImport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->get();

        $airExports = AirExport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->get();

        $oceanImports = OceanImport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->get();

        $oceanExports = OceanExport::where('company_id', $companyId)
            ->where('dm_customer_id', $customerId)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Combine All Shipments
        |--------------------------------------------------------------------------
        */

        $allShipments = collect();

        foreach ($airImports as $shipment) {

            $shipment->shipment_type = 'Air Import';

            $allShipments->push($shipment);
        }

        foreach ($airExports as $shipment) {

            $shipment->shipment_type = 'Air Export';

            $allShipments->push($shipment);
        }

        foreach ($oceanImports as $shipment) {

            $shipment->shipment_type = 'Ocean Import';

            $allShipments->push($shipment);
        }

        foreach ($oceanExports as $shipment) {

            $shipment->shipment_type = 'Ocean Export';

            $allShipments->push($shipment);
        }


        /*
        |--------------------------------------------------------------------------
        | Shipment Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $fromDate = $request->from_date;

            $allShipments = $allShipments->filter(function ($shipment) use ($fromDate) {

                $shipmentDate = $shipment->created_at;

                return $shipmentDate &&
                    $shipmentDate->format('Y-m-d') >= $fromDate;
            });
        }


        if ($request->filled('to_date')) {

            $toDate = $request->to_date;

            $allShipments = $allShipments->filter(function ($shipment) use ($toDate) {

                $shipmentDate = $shipment->created_at;

                return $shipmentDate &&
                    $shipmentDate->format('Y-m-d') <= $toDate;
            });
        }


        if ($request->filled('status')) {

            $selectedStatus = strtolower($request->status);

            $allShipments = $allShipments->filter(function ($shipment) use ($selectedStatus) {

                $shipmentStatus = strtolower($shipment->status ?? '');

                if ($selectedStatus === 'in_transit') {

                    return in_array($shipmentStatus, [
                        'in transit',
                        'transit',
                        'in_transit'
                    ]);
                }

                return $shipmentStatus === $selectedStatus;
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Shipment Statistics
        |--------------------------------------------------------------------------
        */

        $totalShipments = $allShipments->count();


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


        $arrived = $allShipments->filter(function ($shipment) {

            return strtolower($shipment->status ?? '') === 'arrived';

        })->count();


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
        | Invoice Data
        |--------------------------------------------------------------------------
        */

        $invoiceQuery = Invoice::where('company_id', $companyId)
            ->where('bill_to_id', $customerId)
            ->where('type', 'AR')
            ->whereNull('deleted_at');


        /*
        |--------------------------------------------------------------------------
        | Invoice Date Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $invoiceQuery->whereDate(
                'invoice_date',
                '>=',
                $request->from_date
            );
        }


        if ($request->filled('to_date')) {

            $invoiceQuery->whereDate(
                'invoice_date',
                '<=',
                $request->to_date
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invoice Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $selectedStatus = strtolower($request->status);

            if ($selectedStatus === 'in_transit') {

                // Invoices don't have shipment status.
                // So don't apply this status to invoices.

            } else {

                $invoiceQuery->whereRaw(
                    'LOWER(status) = ?',
                    [$selectedStatus]
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Get Invoices
        |--------------------------------------------------------------------------
        */

        $invoices = $invoiceQuery
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Invoice Statistics
        |--------------------------------------------------------------------------
        */

        $totalInvoices = $invoices->count();

        $totalInvoiceAmount = $invoices->sum('total_amount');

        $paidInvoiceAmount = $invoices->sum('paid_amount');

        $outstandingInvoiceAmount = $invoices->sum('balance_amount');


        /*
        |--------------------------------------------------------------------------
        | Overdue Invoices
        |--------------------------------------------------------------------------
        */

        $overdueInvoiceAmount = $invoices
            ->filter(function ($invoice) {

                return strtolower(
                    $invoice->status ?? ''
                ) === 'overdue';

            })
            ->sum('balance_amount');


        /*
        |--------------------------------------------------------------------------
        | Quotations
        |--------------------------------------------------------------------------
        */

        $quotations = Quotation::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Report Type
        |--------------------------------------------------------------------------
        */

        $reportType = $request->report_type;


        /*
        |--------------------------------------------------------------------------
        | Return Reports Page
        |--------------------------------------------------------------------------
        */

        return view('client.report', compact(

            'loggedInUser',

            // Shipments
            'airImports',
            'airExports',
            'oceanImports',
            'oceanExports',
            'allShipments',

            // Shipment statistics
            'totalShipments',
            'inTransit',
            'arrived',
            'delivered',
            'pending',

            // Invoices
            'invoices',
            'totalInvoices',
            'totalInvoiceAmount',
            'paidInvoiceAmount',
            'outstandingInvoiceAmount',
            'overdueInvoiceAmount',

            // Quotations
            'quotations',

            // Filters
            'reportType'
        ));
    }
}
