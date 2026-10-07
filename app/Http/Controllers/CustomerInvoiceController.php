<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceController extends Controller
{
    /**
     * Base query:
     * Sirf logged-in customer ki invoices.
     *
     * Company level:
     * users.company_id
     *
     * Customer level:
     * users.trade_partner_id = invoices.bill_to_id
     */
    private function base()
    {
        $u = auth()->user();

        abort_if(!$u, 403);

        abort_if(!$u->company_id, 403);

        abort_if(!$u->trade_partner_id, 403);

        return DB::table('invoices as i')
            ->leftJoin('currencies as c', 'c.id', '=', 'i.currency_id')

            // Tenant / Company security
            ->where('i.company_id', $u->company_id)

            // Customer security
            ->where('i.bill_to_id', $u->trade_partner_id)

            // Only Accounts Receivable invoices
            ->where('i.type', 'AR')

            // Only customer-visible invoice statuses
            ->whereIn('i.status', [
                'POSTED',
                'PARTIAL',
                'PAID'
            ])

            // Ignore deleted invoices
            ->whereNull('i.deleted_at')

            ->select([
                'i.id',
                'i.invoice_no',
                'i.invoice_date',
                'i.due_date',
                'i.billing_address',
                'i.subtotal',
                'i.tax_total',
                'i.total_amount',
                'i.paid_amount',
                'i.balance_amount',
                'i.status',
                'i.discount_pct',
                'i.shipping_amount',

                'c.code as currency',
                'c.symbol',
            ]);
    }


    /**
     * Invoice Listing
     */
    public function index(Request $r)
    {
        $base = $this->base();

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totals = DB::query()
            ->fromSub(clone $base, 't')
            ->selectRaw('
                COALESCE(SUM(total_amount), 0) as billed,
                COALESCE(SUM(paid_amount), 0) as paid,
                COALESCE(SUM(balance_amount), 0) as balance
            ')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($r->filled('status')) {
            $base->where('i.status', $r->status);
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($r->filled('search')) {
            $base->where(
                'i.invoice_no',
                'like',
                '%' . $r->search . '%'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Overdue
        |--------------------------------------------------------------------------
        */

        if ($r->boolean('overdue')) {
            $base
                ->where('i.status', '!=', 'PAID')
                ->whereNotNull('i.due_date')
                ->where(
                    'i.due_date',
                    '<',
                    now()->toDateString()
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Invoice List
        |--------------------------------------------------------------------------
        */

        $invoices = $base
            ->orderByDesc('i.invoice_date')
            ->paginate(15)
            ->withQueryString();


        return view(
            'client.invoice',
            compact('invoices', 'totals')
        );
    }


    /**
     * Invoice Details
     */
    public function show($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Get invoice
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | base() already checks:
        |
        | company_id
        | +
        | bill_to_id
        |
        | So customer cannot open another customer's invoice
        | simply by changing the URL ID.
        |
        */

        $invoice = $this->base()
            ->where('i.id', $id)
            ->first();

        abort_if(!$invoice, 404);


        /*
        |--------------------------------------------------------------------------
        | Invoice Lines
        |--------------------------------------------------------------------------
        */

        $lines = DB::table('invoice_lines')
            ->where('invoice_id', $id)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        $payments = DB::table('payments')
            ->where('invoice_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('payment_date')
            ->get([
                'payment_no',
                'payment_date',
                'amount',
                'payment_method',
                'reference_no',
            ]);


        return view(
            'client.invoiceshow',
            compact(
                'invoice',
                'lines',
                'payments'
            )
        );
    }
}