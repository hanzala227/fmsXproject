<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerQuoteController extends Controller
{
    /**
     * Base query for the logged-in customer's quotations.
     */
    private function customerQuotesQuery()
    {
        $loggedInUser = auth()->user();

        abort_if(!$loggedInUser, 403);
        abort_if(!$loggedInUser->company_id, 403);
        abort_if(!$loggedInUser->trade_partner_id, 403);

        return DB::table('quotations as quotation')

            // POL
            ->leftJoin(
                'ports as pol',
                'pol.id',
                '=',
                'quotation.pol_id'
            )

            // POD
            ->leftJoin(
                'ports as pod',
                'pod.id',
                '=',
                'quotation.pod_id'
            )

            /*
            |--------------------------------------------------------------------------
            | Company Security
            |--------------------------------------------------------------------------
            */
            ->where(
                'quotation.company_id',
                $loggedInUser->company_id
            )

            /*
            |--------------------------------------------------------------------------
            | Customer Security
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | customer_id must contain the trade_partner ID.
            |
            */
            ->where(
                'quotation.customer_id',
                $loggedInUser->trade_partner_id
            )

            /*
            |--------------------------------------------------------------------------
            | Customer-visible quotations only
            |--------------------------------------------------------------------------
            */
            ->whereNull('quotation.deleted_at')
            ->where('quotation.status', '!=', 'Draft')

            /*
            |--------------------------------------------------------------------------
            | Columns visible to customer
            |--------------------------------------------------------------------------
            */
            ->select([
                'quotation.id',
                'quotation.quote_no',
                'quotation.quote_date',
                'quotation.expiry_date',

                'quotation.transport_mode',
                'quotation.shipping_type',

                'quotation.commodity',
                'quotation.status',

                'quotation.weight_kg',
                'quotation.volume_cbm',

                'quotation.remark',
                'quotation.quotation_remark',

                // POL
                'pol.name as pol_name',
                'pol.code as pol_code',

                // POD
                'pod.name as pod_name',
                'pod.code as pod_code',
            ]);
    }


    /**
     * Show customer's quotations.
     */
    public function index(Request $request)
    {
        $quotesQuery = $this->customerQuotesQuery();


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $quotesQuery->where(
                'quotation.status',
                $request->status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $searchText = $request->search;

            $quotesQuery->where(function ($searchQuery) use ($searchText) {

                $searchQuery
                    ->where(
                        'quotation.quote_no',
                        'like',
                        '%' . $searchText . '%'
                    )
                    ->orWhere(
                        'quotation.commodity',
                        'like',
                        '%' . $searchText . '%'
                    );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Get Quotations
        |--------------------------------------------------------------------------
        */

        $quotes = $quotesQuery
            ->orderByDesc('quotation.quote_date')
            ->paginate(15)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Available Statuses
        |--------------------------------------------------------------------------
        */

        $statuses = [
            'Pending',
            'Sent',
            'Won',
            'Lost',
            'Expired',
            'Cancelled',
        ];


        return view(
            'client.quote',
            compact(
                'quotes',
                'statuses'
            )
        );
    }


    /**
     * Show one quotation.
     */
    public function show($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Get quotation
        |--------------------------------------------------------------------------
        |
        | customerQuotesQuery() already checks:
        |
        | company_id
        | +
        | customer_id
        |
        | Therefore a customer cannot open another customer's quote
        | by changing the URL ID.
        |
        */

        $quote = $this->customerQuotesQuery()
            ->where('quotation.id', $id)
            ->first();


        abort_if(!$quote, 404);


        /*
        |--------------------------------------------------------------------------
        | Quotation Charges
        |--------------------------------------------------------------------------
        */

        $quotationCharges = DB::table('quotation_dest_charges')
            ->where('quotation_id', $id)
            ->where('show', 1)
            ->orderBy('sort_order')
            ->get();


        return view(
            'client.quoteshow',
            compact(
                'quote',
                'quotationCharges'
            )
        );
    }
}