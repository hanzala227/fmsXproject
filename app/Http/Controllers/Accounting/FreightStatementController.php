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
        if (!$request->has('as_of_date') || empty($request->input('as_of_date'))) {
            $request->merge(['as_of_date' => date('Y-m-d')]);
        }

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
        $companyId       = auth()->user()->company_id ?? 1;

        $asOfCarbon = Carbon::parse($asOfDate);

        $query = Invoice::where('company_id', $companyId)
            ->where('invoice_date', '<=', $asOfDate)
            ->where('status', '!=', 'VOID')
            ->where('verification_status', '!=', 'Cancelled')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->with(['billTo', 'currency', 'office', 'payments' => fn($p) => $p->whereNull('void_date'), 'invoiceable']);

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

        $invoices = $query->orderBy('invoice_date')->get();

        $results = [];
        foreach ($invoices as $invoice) {
            $totalPaid = (float) $invoice->payments->whereNull('void_date')->sum('amount');
            $invType   = strtoupper($invoice->type ?? '');
            $docType   = strtoupper($invoice->document_type ?? '');
            $isCN      = $docType === 'CN';
            $isDN      = $docType === 'DN';
            $rawTotal  = (float) $invoice->total_amount;

            // DR/CR and Balance logic
            if ($invType === 'AR') {
                if ($isCN) {
                    $drAmount = 0.0;
                    $crAmount = $rawTotal;
                    $balance  = -$rawTotal - $totalPaid;
                } else {
                    $drAmount = $rawTotal;
                    $crAmount = 0.0;
                    $balance  = $rawTotal - $totalPaid;
                }
            } else { // AP
                if ($isDN) {
                    $drAmount = $rawTotal;
                    $crAmount = 0.0;
                    $balance  = -$rawTotal - $totalPaid;
                } else {
                    $drAmount = 0.0;
                    $crAmount = $rawTotal;
                    $balance  = $rawTotal - $totalPaid;
                }
            }

            $skip = false;
            if ($paymentStatus === 'paid' && abs($balance) > 0.01) $skip = true;
            if ($paymentStatus === 'open' && abs($balance) < 0.01) $skip = true;
            if ($hideOverpaid === '1' && $balance < -0.01) $skip = true;
            if ($skip) continue;

            $partnerName = $invoice->billTo?->name ?? 'N/A';
            $officeName  = $invoice->office?->name ?? 'N/A';
            $currCode    = $invoice->currency?->code ?? $invoice->currency?->name ?? 'USD';

            $mblNo = '';
            $hblNo = '';
            $etd   = '';
            $bookingNo = '';

            if ($invoice->invoiceable) {
                $inv = $invoice->invoiceable;
                $mblNo = $inv->mbl_no ?? $inv->mawb_no ?? $invoice->mbl_no ?? '';
                $hblNo = $inv->hbl_no ?? $inv->hawb_no ?? '';
                if (empty($hblNo) && isset($inv->hbls) && $inv->hbls->isNotEmpty()) {
                    $hblNo = $inv->hbls->pluck('hbl_no')->filter()->implode(', ');
                }
                $bookingNo = $inv->booking_no ?? $inv->file_no ?? '';

                if (isset($inv->etd) && $inv->etd) {
                    $etd = Carbon::parse($inv->etd)->format('Y-m-d');
                } elseif (isset($inv->departure_date) && $inv->departure_date) {
                    $etd = Carbon::parse($inv->departure_date)->format('Y-m-d');
                } elseif (isset($inv->flight_date) && $inv->flight_date) {
                    $etd = Carbon::parse($inv->flight_date)->format('Y-m-d');
                }
            } else {
                $mblNo = $invoice->mbl_no ?? '';
            }

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
                'total_amount' => $rawTotal,
                'dr_amount'    => round($drAmount, 2),
                'cr_amount'    => round($crAmount, 2),
                'paid_amount'  => round($totalPaid, 2),
                'balance'      => round($balance, 2),
            ];
        }

        usort($results, fn($a, $b) => strcasecmp($a['partner_name'], $b['partner_name']) ?: strcasecmp($a['invoice_date'], $b['invoice_date']));

        $totalDR      = round(array_sum(array_column($results, 'dr_amount')), 2);
        $totalCR      = round(array_sum(array_column($results, 'cr_amount')), 2);
        $totalPaid    = round(array_sum(array_column($results, 'paid_amount')), 2);
        $totalBalance = round(array_sum(array_column($results, 'balance')), 2);

        $currencyTotals = [];
        foreach ($results as $r) {
            $cur = $r['currency'] ?: 'USD';
            if (!isset($currencyTotals[$cur])) {
                $currencyTotals[$cur] = ['currency' => $cur, 'dr' => 0, 'cr' => 0, 'paid' => 0, 'balance' => 0];
            }
            $currencyTotals[$cur]['dr']      = round($currencyTotals[$cur]['dr'] + $r['dr_amount'], 2);
            $currencyTotals[$cur]['cr']      = round($currencyTotals[$cur]['cr'] + $r['cr_amount'], 2);
            $currencyTotals[$cur]['paid']    = round($currencyTotals[$cur]['paid'] + $r['paid_amount'], 2);
            $currencyTotals[$cur]['balance'] = round($currencyTotals[$cur]['balance'] + $r['balance'], 2);
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

            $showBkg = ($request->input('show_booking_number') === '1');
            $headerCols = ['Post Date', 'ETD', 'Invoice No.'];
            if ($showBkg) {
                $headerCols[] = 'Booking No.';
            }
            $headerCols = array_merge($headerCols, ['MB/L No.', 'HB/L No.', 'Partner', 'Cur.', 'DRI/AR (+)', 'CRI/AP (-)', 'Paid', 'Balance']);
            fputcsv($handle, $headerCols);

            foreach ($results as $row) {
                $rowCols = [$row['invoice_date'], $row['etd'], $row['invoice_no']];
                if ($showBkg) {
                    $rowCols[] = $row['booking_no'];
                }
                $rowCols = array_merge($rowCols, [
                    $row['mbl_no'],
                    $row['hbl_no'],
                    $row['partner_name'],
                    $row['currency'],
                    number_format($row['dr_amount'], 2),
                    number_format($row['cr_amount'], 2),
                    number_format($row['paid_amount'], 2),
                    number_format($row['balance'], 2),
                ]);
                fputcsv($handle, $rowCols);
            }

            fputcsv($handle, []);
            $totalCols = ['TOTAL (BALANCE)', ''];
            if ($showBkg) {
                $totalCols[] = '';
            }
            $totalCols = array_merge($totalCols, [
                '', '', '', '',
                number_format($summary['total_dr'] ?? 0, 2),
                number_format($summary['total_cr'] ?? 0, 2),
                number_format($summary['total_paid'] ?? 0, 2),
                number_format($summary['total_balance'] ?? 0, 2),
            ]);
            fputcsv($handle, $totalCols);

            fclose($handle);
        }, 200, $headers);
    }
}
