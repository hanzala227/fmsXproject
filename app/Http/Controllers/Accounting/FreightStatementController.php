<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Office;
use App\Models\TradePartner;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FreightStatementController extends Controller
{
    public function index()
    {
        $offices       = Office::where('is_active', true)->orderBy('name')->get();
        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies    = Currency::orderBy('name')->get();
        $accountGroups = TradePartner::whereNotNull('credit_limit_group_name')
            ->where('credit_limit_group_name', '!=', '')
            ->distinct()
            ->pluck('credit_limit_group_name')
            ->sort()
            ->values();

        return view('accounting.freight-statement', compact('offices', 'tradePartners', 'currencies', 'accountGroups'));
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'as_of_date'       => 'required|date',
            'office_id'        => 'nullable|exists:offices,id',
            'partner_type'     => 'nullable|in:agent_customer,account_group',
            'bill_to_id'       => 'nullable|exists:trade_partners,id',
            'account_group'    => 'nullable|string',
            'payment_status'   => 'nullable|in:all,open,paid',
            'trans_type'       => 'nullable|string',
            'hide_overpaid'    => 'nullable|in:0,1',
            'invoice_local_received' => 'nullable|in:0,1',
            'show_booking_number'    => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $asOfDate        = $request->input('as_of_date');
        $officeId        = $request->input('office_id');
        $billToId        = $request->input('bill_to_id');
        $partnerType     = $request->input('partner_type', 'agent_customer');
        $accountGroup    = $request->input('account_group', '');
        $paymentStatus   = $request->input('payment_status', 'all');
        $transType       = $request->input('trans_type', 'all');
        $hideOverpaid    = $request->input('hide_overpaid', '0');
        $invoiceLocalReceived = $request->input('invoice_local_received', '0');
        $showBookingNumber    = $request->input('show_booking_number', '0');

        $asOfCarbon = Carbon::parse($asOfDate);

        $query = Invoice::where('invoice_date', '<=', $asOfDate)
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->with(['billTo', 'currency', 'office', 'payments', 'invoiceable']);

        if ($partnerType === 'account_group') {
            if ($accountGroup) {
                $query->whereHas('billTo', fn($q) => $q->where('credit_limit_group_name', $accountGroup));
            }
        } else {
            if ($billToId) {
                $query->where('bill_to_id', $billToId);
            }
        }

        if ($invoiceLocalReceived === '1') {
            $query->where('type', 'AR')
                  ->whereHas('billTo', fn($q) => $q->where('type', 'CLIENT'));
        }

        if ($transType !== 'all') {
            $transMap = [
                'debit' => ['AR'],
                'credit' => ['AP'],
                'ar'    => ['AR'],
                'ap'    => ['AP'],
            ];
            $rawTypes = array_map('strtoupper', array_map('trim', explode(',', $transType)));
            $allowedTypes = [];
            foreach ($rawTypes as $rt) {
                if (isset($transMap[strtolower($rt)])) {
                    $allowedTypes = array_merge($allowedTypes, $transMap[strtolower($rt)]);
                } else {
                    $allowedTypes[] = $rt;
                }
            }
            $allowedTypes = array_unique($allowedTypes);
            $query->whereIn('type', $allowedTypes);
        }

        $invoices = $query->get();

        $results = [];
        foreach ($invoices as $invoice) {
            $totalPaid = (float) $invoice->payments->sum('amount');
            $balance   = (float) $invoice->total_amount - $totalPaid;

            $skip = false;
            if ($paymentStatus === 'paid' && abs($balance) > 0.01) $skip = true;
            if ($paymentStatus === 'open' && abs($balance) < 0.01) $skip = true;
            if ($hideOverpaid === '1' && $balance < 0) $skip = true;
            if ($skip) continue;

            $invType     = strtoupper($invoice->type ?? '');
            $partnerName = $invoice->billTo?->name ?? 'N/A';
            $officeName  = $invoice->office?->name ?? 'N/A';
            $currCode    = $invoice->currency?->code ?? $invoice->currency?->name ?? 'USD';

            $mblNo = '';
            $hblNo = '';
            $etd   = '';
            $bookingNo = '';

            if ($invoice->invoiceable) {
                $mblNo = $invoice->invoiceable->mbl_no ?? $invoice->invoiceable->booking_no ?? '';
                $hblNo = $invoice->invoiceable->hbl_no ?? '';
                $etd   = isset($invoice->invoiceable->etd) ? Carbon::parse($invoice->invoiceable->etd)->format('Y-m-d') : '';
                $bookingNo = $invoice->invoiceable->booking_no ?? '';
            }

            $drAmount = $invType === 'AR' ? (float) $invoice->total_amount : 0;
            $crAmount = $invType === 'AP' ? (float) $invoice->total_amount : 0;

            $results[] = [
                'invoice_no'   => $invoice->invoice_no ?? '',
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                'due_date'     => $invoice->due_date?->format('Y-m-d') ?? '',
                'partner_name' => $partnerName,
                'office'       => $officeName,
                'currency'     => $currCode,
                'type'         => $invType,
                'mbl_no'       => $mblNo,
                'hbl_no'       => $hblNo,
                'etd'          => $etd,
                'booking_no'   => $bookingNo,
                'total_amount' => (float) $invoice->total_amount,
                'dr_amount'    => $drAmount,
                'cr_amount'    => $crAmount,
                'paid_amount'  => $totalPaid,
                'balance'      => $balance,
            ];
        }

        usort($results, fn($a, $b) => strcasecmp($a['partner_name'], $b['partner_name']) ?: strcasecmp($a['invoice_date'], $b['invoice_date']));

        $totalDR      = array_sum(array_column($results, 'dr_amount'));
        $totalCR      = array_sum(array_column($results, 'cr_amount'));
        $totalPaid    = array_sum(array_column($results, 'paid_amount'));
        $totalBalance = array_sum(array_column($results, 'balance'));

        $currencyTotals = [];
        foreach ($results as $r) {
            $cur = $r['currency'];
            if (!isset($currencyTotals[$cur])) {
                $currencyTotals[$cur] = ['currency' => $cur, 'dr' => 0, 'cr' => 0, 'paid' => 0, 'balance' => 0];
            }
            $currencyTotals[$cur]['dr']      += $r['dr_amount'];
            $currencyTotals[$cur]['cr']      += $r['cr_amount'];
            $currencyTotals[$cur]['paid']    += $r['paid_amount'];
            $currencyTotals[$cur]['balance'] += $r['balance'];
        }

        $partnerInfo = null;
        if ($billToId) {
            $tp = TradePartner::find($billToId);
            if ($tp) {
                $partnerInfo = [
                    'name'    => $tp->name,
                    'address' => $tp->local_address ?? '',
                    'city'    => $tp->city ?? '',
                    'country' => $tp->country?->name ?? '',
                ];
            }
        } elseif ($partnerType === 'account_group' && $accountGroup) {
            $partnerInfo = [
                'name'    => $accountGroup,
                'address' => '',
                'city'    => '',
                'country' => '',
            ];
        }

        return response()->json([
            'success'          => true,
            'as_of_date'       => $asOfDate,
            'results'          => $results,
            'currency_totals'  => array_values($currencyTotals),
            'partner_info'     => $partnerInfo,
            'show_booking_number' => $showBookingNumber,
            'summary'          => [
                'total_dr'      => $totalDR,
                'total_cr'      => $totalCR,
                'total_paid'    => $totalPaid,
                'total_balance' => $totalBalance,
                'count'         => count($results),
            ],
        ]);
    }

    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.freight-statement-print', [
            'results'          => $data['results'] ?? [],
            'currencyTotals'   => $data['currency_totals'] ?? [],
            'partnerInfo'      => $data['partner_info'] ?? null,
            'summary'          => $data['summary'] ?? [],
            'asOfDate'         => $request->as_of_date ?? date('Y-m-d'),
            'showBookingNumber' => $request->show_booking_number ?? '0',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data    = $this->view($request)->getData(true);
        $results = $data['results'] ?? [];
        $summary = $data['summary'] ?? [];

        $filename = 'freight-statement-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($results, $summary, $request) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Freight Statement']);
            fputcsv($handle, ['As of Date', $request->as_of_date ?? '']);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Post Date', 'ETD', 'Invoice No.', 'MB/L No.', 'HB/L No.', 'Cur.',
                'DRI/AR (+)', 'CRI/AP (-)', 'Paid', 'Balance',
            ]);

            foreach ($results as $row) {
                fputcsv($handle, [
                    $row['invoice_date'], $row['etd'], $row['invoice_no'], $row['mbl_no'], $row['hbl_no'], $row['currency'],
                    number_format($row['dr_amount'], 2),
                    number_format($row['cr_amount'], 2),
                    number_format($row['paid_amount'], 2),
                    number_format($row['balance'], 2),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL (BALANCE)', '', '', '', '',
                number_format($summary['total_dr'] ?? 0, 2),
                number_format($summary['total_cr'] ?? 0, 2),
                number_format($summary['total_paid'] ?? 0, 2),
                number_format($summary['total_balance'] ?? 0, 2),
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
