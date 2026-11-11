<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Office;
use App\Models\TradePartner;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AgingReportController extends Controller
{
    /**
     * Helper to convert foreign currency amount to base USD.
     */
    private function toUSD(float $amount, ?string $currCode, $modelRate = null): float
    {
        if ($modelRate && (float) $modelRate > 0) {
            return ((float) $modelRate != 1.0) ? round($amount / (float) $modelRate, 2) : $amount;
        }
        $currCode = $currCode ?: 'USD';
        if ($currCode === 'USD') return $amount;

        $rateRecord = DB::table('currency_rates')
            ->where('from_currency', $currCode)
            ->where('to_currency', 'USD')
            ->orderByDesc('as_of_date')
            ->first();
        if ($rateRecord && (float) $rateRecord->rate_internal > 0) {
            $rate = (float) $rateRecord->rate_internal;
        } else {
            $curr = Currency::where('code', $currCode)->first();
            $rate = ($curr && (float) $curr->exchange_rate > 0) ? (float) $curr->exchange_rate : 1.0;
        }
        return ($rate > 0 && $rate != 1.0) ? round($amount / $rate, 2) : $amount;
    }
    public function index()
    {
        $companyId     = auth()->user()?->company_id;
        $offices       = Office::where('is_active', true)->orderBy('name')->get();
        $users         = User::when($companyId, fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get();
        $accountGroups = TradePartner::whereNotNull('credit_limit_group_name')
            ->where('credit_limit_group_name', '!=', '')
            ->distinct()
            ->pluck('credit_limit_group_name')
            ->sort()
            ->values();

        return view('accounting.aging-report', compact('offices', 'users', 'accountGroups'));
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'as_of_date'         => 'required|date',
            'office_id'          => 'nullable|exists:offices,id',
            'report_type'        => 'nullable|in:summary,detail',
            'aging_type'         => 'nullable|string',
            'trade_partner_type' => 'nullable|string',
            'customer_name'      => 'nullable|string',
            'partner_name'       => 'nullable|string',
            'group_by'           => 'nullable|in:trade_partner,sales_invoice,account_group',
            'sort_by'            => 'nullable|in:due_date,etd,eta',
            'ending_date_type'   => 'nullable|in:post_date,invoice_date,etd,eta',
            'payment_data'       => 'nullable|in:include,exclude',
            'include_prepaid'    => 'nullable|in:0,1',
            'opt_hide_overpaid'  => 'nullable|in:0,1',
            'opt_hide_zero_bal'  => 'nullable|in:0,1',
            'opt_hide_negative'  => 'nullable|in:0,1',
            'opt_filter_credit'  => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $asOfDate         = $request->input('as_of_date', date('Y-m-d'));
        $officeId         = $request->input('office_id');
        $reportType       = $request->input('report_type', 'summary');
        $agingType        = $request->input('aging_type', 'ar,ap');
        $tradePartnerType = $request->input('trade_partner_type', 'all');
        $customerName     = trim($request->input('customer_name', $request->input('partner_name', $request->input('search', ''))));
        $groupBy          = $request->input('group_by', 'trade_partner');
        $sortBy           = $request->input('sort_by', 'due_date');
        $endingDateType   = $request->input('ending_date_type', 'invoice_date');
        $paymentData      = $request->input('payment_data', 'include');
        $includePrepaid   = $request->input('include_prepaid', '0');
        $hideOverpaid     = $request->input('opt_hide_overpaid', '0');
        $hideZeroBal      = $request->input('opt_hide_zero_bal', '0');
        $hideNegative     = $request->input('opt_hide_negative', '0');
        $filterCredit     = $request->input('opt_filter_credit', '0');
        $companyId        = auth()->user()->company_id ?? 1;

        $asOfCarbon = Carbon::parse($asOfDate);

        $results = [];
        $invoiceTypes = array_map('strtoupper', array_map('trim', explode(',', $agingType)));

        if (empty($invoiceTypes)) {
            $invoiceTypes = ['AR', 'AP'];
        }

        $invoices = Invoice::reportable()
            ->where('company_id', $companyId)
            ->whereIn('type', $invoiceTypes)
            ->where('invoice_date', '<=', $asOfDate)
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->with([
                'billTo',
                'currency',
                'invoiceable',
                'payments' => fn($q) => $q->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->whereNull('void_date')
                    ->where('payment_date', '<=', $asOfDate),
            ])
            ->withCount(['payments as future_payments_count' => function($q) use ($asOfDate) {
                $q->whereNull('deleted_at')->whereNull('cancelled_at')->whereNull('void_date')->where('payment_date', '>', $asOfDate);
            }])
            ->get();

        $grouped = [];
        foreach ($invoices as $invoice) {
            $currCode = $invoice->currency?->code ?: ($invoice->currency?->name ?: 'USD');
            $rawTotal = (float) $invoice->total_amount;
            $activePays = $invoice->payments;
            $rawPaid  = (float) $activePays->sum('amount')
                      + (float) $activePays->sum('tds_tax')
                      + (float) $activePays->sum('discount');
            $docType  = strtoupper($invoice->document_type ?? '');
            $isCreditAdjustment = ($invoice->type === 'AR' && $docType === 'CN')
                || ($invoice->type === 'AP' && in_array($docType, ['DN', 'CN']));

            $rawOutstanding = max(0.0, $rawTotal - $rawPaid);
            $hasFuturePayments = ((int) ($invoice->future_payments_count ?? 0)) > 0;
            if (($invoice->status === 'PAID' && !$hasFuturePayments) || $rawOutstanding <= 0.001) {
                $rawOutstanding = 0.0;
            }

            $rawBalance = $isCreditAdjustment ? -abs($rawOutstanding) : $rawOutstanding;

            $balance = $this->toUSD($rawBalance, $currCode, $invoice->exchange_rate);

            if (abs($balance) <= 0.001 && $hideZeroBal === '1') {
                continue;
            }
            if ($hideOverpaid === '1' && $balance < 0) {
                continue;
            }
            if ($hideNegative === '1' && $balance < 0) {
                continue;
            }
            if ($filterCredit === '1' && $balance <= 0) {
                continue;
            }

            $partnerId   = $invoice->bill_to_id ?? 0;
            $partnerName = $invoice->billTo?->name ?? 'N/A';
            $partnerCode = $invoice->billTo?->code ?? '';
            $partnerType = strtoupper(trim($invoice->billTo?->type ?? ''));
            $invoiceDate = $invoice->invoice_date;
            $dueDate     = $invoice->due_date;

            // Customer Name / Partner Name filter
            if ($customerName !== '') {
                $matchesName = (stripos($partnerName, $customerName) !== false) ||
                               (stripos($partnerCode, $customerName) !== false) ||
                               (stripos($invoice->billTo?->alias ?? '', $customerName) !== false);
                if (!$matchesName) {
                    continue;
                }
            }

            // Customer Type filter
            if ($tradePartnerType === 'customer') {
                $isCustomer = in_array($partnerType, ['CS', 'CUSTOMER', 'CLIENT', 'CO']) || ($invoice->type === 'AR' && !in_array($partnerType, ['CR', 'CARRIER', 'AIR CARRIER', 'AIRLINE']));
                if (!$isCustomer) {
                    continue;
                }
            } elseif ($tradePartnerType === 'ocean_carrier') {
                $isAirline = str_contains(strtoupper($partnerName), 'AIRLINE') || str_contains(strtoupper($partnerName), 'AIR CARRIER') || in_array($partnerType, ['AIR CARRIER', 'AIR_CARRIER']);
                $isOcean = !$isAirline && (
                    in_array($partnerType, ['OCEAN CARRIER', 'OCEAN_CARRIER']) 
                    || (in_array($partnerType, ['CR', 'CARRIER']) && (
                        str_contains(strtoupper($partnerName), 'SHIPPING') || 
                        preg_match('/\bLINES?\b/i', $partnerName) || 
                        str_contains(strtoupper($partnerName), 'ZIM') || 
                        str_contains(strtoupper($partnerName), 'COSCO') || 
                        str_contains(strtoupper($partnerName), 'MAERSK') || 
                        str_contains(strtoupper($partnerName), 'WESTWOOD') || 
                        str_contains($invoice->invoiceable_type ?? '', 'Ocean')
                    ))
                );
                if (!$isOcean) {
                    continue;
                }
            } elseif ($tradePartnerType === 'air_carrier') {
                $isAir = in_array($partnerType, ['AIR CARRIER', 'AIR_CARRIER']) 
                    || (in_array($partnerType, ['CR', 'CARRIER']) && (
                        str_contains(strtoupper($partnerName), 'AIR') || 
                        str_contains(strtoupper($partnerName), 'AIRLINE') || 
                        str_contains($invoice->invoiceable_type ?? '', 'Air')
                    ));
                if (!$isAir) {
                    continue;
                }
            } elseif ($tradePartnerType === 'oversea_agent') {
                $isAgent = in_array($partnerType, ['AGENT', 'OVERSEA AGENT', 'OVERSEA_AGENT', 'OVERSEAS AGENT']);
                if (!$isAgent) {
                    continue;
                }
            } elseif ($tradePartnerType === 'exclude_oversea_agent') {
                $isAgent = in_array($partnerType, ['AGENT', 'OVERSEA AGENT', 'OVERSEA_AGENT', 'OVERSEAS AGENT']);
                if ($isAgent) {
                    continue;
                }
            }

            $daysOverdue = 0;
            if ($dueDate) {
                $dueCarbon = Carbon::parse($dueDate);
                if ($asOfCarbon->gt($dueCarbon)) {
                    $daysOverdue = (int) $dueCarbon->diffInDays($asOfCarbon, false);
                }
            }

            $current = 0;
            $over1_30 = 0;
            $over31_60 = 0;
            $over61_90 = 0;
            $over90 = 0;

            if ($daysOverdue <= 0) {
                $current = $balance;
            } elseif ($daysOverdue <= 30) {
                $over1_30 = $balance;
            } elseif ($daysOverdue <= 60) {
                $over31_60 = $balance;
            } elseif ($daysOverdue <= 90) {
                $over61_90 = $balance;
            } else {
                $over90 = $balance;
            }

            $currencyCode = $invoice->currency?->code ?: ($invoice->currency?->name ?: 'USD');

            if (!isset($grouped[$partnerId])) {
                $grouped[$partnerId] = [
                    'partner_id'   => $partnerId,
                    'partner_name' => $partnerName,
                    'partner_type' => $partnerType,
                    'currency'     => 'USD',
                    'current'      => 0,
                    'over1_30'     => 0,
                    'over31_60'    => 0,
                    'over61_90'    => 0,
                    'over90'       => 0,
                    'total'        => 0,
                    'invoices'     => [],
                ];
            }

            $grouped[$partnerId]['current']  += $current;
            $grouped[$partnerId]['over1_30'] += $over1_30;
            $grouped[$partnerId]['over31_60'] += $over31_60;
            $grouped[$partnerId]['over61_90'] += $over61_90;
            $grouped[$partnerId]['over90']    += $over90;
            $grouped[$partnerId]['total']     += $balance;

            $grouped[$partnerId]['invoices'][] = [
                'invoice_no'   => $invoice->invoice_no ?? '',
                'invoice_date' => $invoiceDate?->format('Y-m-d') ?? '',
                'due_date'     => $dueDate?->format('Y-m-d') ?? '',
                'total_amount' => $this->toUSD($rawTotal, $currCode, $invoice->exchange_rate),
                'paid_amount'  => $this->toUSD($rawPaid, $currCode, $invoice->exchange_rate),
                'balance'      => $balance,
                'raw_balance'  => $rawBalance,
                'type'         => $invoice->type ?? '',
                'currency'     => 'USD',
                'orig_currency'=> $currencyCode,
                'days_overdue' => (int) $daysOverdue,
                'current'      => $current,
                'over1_30'     => $over1_30,
                'over31_60'    => $over31_60,
                'over61_90'    => $over61_90,
                'over90'       => $over90,
            ];
        }

        $results = array_values($grouped);

        if ($hideZeroBal === '1') {
            $results = array_filter($results, fn($r) => abs($r['total']) > 0.01);
            $results = array_values($results);
        }

        if ($sortBy === 'eta') {
            usort($results, fn($a, $b) => $b['over90'] <=> $a['over90']);
        } elseif ($sortBy === 'etd') {
            usort($results, fn($a, $b) => $b['over61_90'] <=> $a['over61_90']);
        } else {
            usort($results, fn($a, $b) => strcasecmp($a['partner_name'], $b['partner_name']));
        }

        $totalCurrent   = array_sum(array_column($results, 'current'));
        $totalOver1_30  = array_sum(array_column($results, 'over1_30'));
        $totalOver31_60 = array_sum(array_column($results, 'over31_60'));
        $totalOver61_90 = array_sum(array_column($results, 'over61_90'));
        $totalOver90    = array_sum(array_column($results, 'over90'));
        $grandTotal     = $totalCurrent + $totalOver1_30 + $totalOver31_60 + $totalOver61_90 + $totalOver90;

        return response()->json([
            'success'     => true,
            'as_of_date'  => $asOfDate,
            'office_id'   => $officeId,
            'report_type' => $reportType,
            'aging_type'  => $agingType,
            'results'     => $results,
            'summary'     => [
                'total_current'   => $totalCurrent,
                'total_over1_30'  => $totalOver1_30,
                'total_over31_60' => $totalOver31_60,
                'total_over61_90' => $totalOver61_90,
                'total_over90'    => $totalOver90,
                'grand_total'     => $grandTotal,
                'partner_count'   => count($results),
            ],
        ]);
    }

    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.aging-report-print', [
            'results'    => $data['results'] ?? [],
            'summary'    => $data['summary'] ?? [],
            'asOfDate'   => $request->as_of_date ?? date('Y-m-d'),
            'agingType'  => $request->aging_type ?? 'ar,ap',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data     = $this->view($request)->getData(true);
        $results  = $data['results'] ?? [];
        $summary  = $data['summary'] ?? [];

        $filename = 'aging-report-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($results, $summary) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Aging Report']);
            fputcsv($handle, ['As of Date', request()->as_of_date ?? date('Y-m-d')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Name',
                'Current Balance',
                'Over 1-30 Days',
                'Over 31-60 Days',
                'Over 61-90 Days',
                'Over 90 Days',
                'Total Balance',
                'Currency',
            ]);

            foreach ($results as $row) {
                fputcsv($handle, [
                    $row['partner_name'],
                    number_format($row['current'], 2),
                    number_format($row['over1_30'], 2),
                    number_format($row['over31_60'], 2),
                    number_format($row['over61_90'], 2),
                    number_format($row['over90'], 2),
                    number_format($row['total'], 2),
                    $row['currency'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                number_format($summary['total_current'] ?? 0, 2),
                number_format($summary['total_over1_30'] ?? 0, 2),
                number_format($summary['total_over31_60'] ?? 0, 2),
                number_format($summary['total_over61_90'] ?? 0, 2),
                number_format($summary['total_over90'] ?? 0, 2),
                number_format($summary['grand_total'] ?? 0, 2),
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
