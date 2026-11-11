<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Currency;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IncomeStatementController extends Controller
{
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('accounting.income-statement', compact('offices'));
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'office_id'  => 'nullable|exists:offices,id',
            'type'       => 'nullable|in:standard,bymonth',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $officeId  = $request->input('office_id');
        $type      = $request->input('type', 'standard');
        $companyId = auth()->user()->company_id ?? 1;

        $startCarbon = Carbon::parse($startDate);
        $endCarbon   = Carbon::parse($endDate);

        // Helper currency conversion to functional base USD
        $toUSD = function ($amount, $currCode, $modelRate = null) {
            $amount = (float) $amount;
            if ($modelRate && (float) $modelRate > 0) {
                return ((float) $modelRate != 1.0) ? round($amount / (float) $modelRate, 2) : $amount;
            }
            $currCode = $currCode ? strtoupper(trim($currCode)) : 'USD';
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
        };

        // Query active & verified invoices scoped to company
        $invoices = Invoice::reportable()
            ->where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->with(['payments', 'office', 'currency', 'billTo'])
            ->orderBy('invoice_date')
            ->get();

        $revenueItems  = [];
        $expenseItems  = [];
        $totalRevenue  = 0.0;
        $totalExpenses = 0.0;

        foreach ($invoices as $invoice) {
            $totalPaid  = (float) $invoice->payments->whereNull('void_date')->sum('amount');
            $invAmount  = (float) $invoice->total_amount;
            $invType    = strtoupper($invoice->type ?? '');
            $docType    = strtoupper($invoice->document_type ?? '');
            $isCN       = $docType === 'CN';
            $isDN       = $docType === 'DN';
            $officeName = $invoice->office?->name ?? 'HQ';
            $currCode   = $invoice->currency?->code ?? $invoice->currency?->name ?? 'USD';

            $baseAmount = $toUSD($invAmount, $currCode, $invoice->exchange_rate);
            $basePaid   = $toUSD($totalPaid, $currCode, $invoice->exchange_rate);

            if ($invType === 'AR') {
                $signedAmount = $isCN ? -$baseAmount : $baseAmount;
                $partnerName = $invoice->billTo?->name ?? 'Customer';
                $descPrefix = $isCN ? 'Credit Note (Revenue Reduction) - ' : ($isDN ? 'Debit Note - ' : 'Operating Revenue - ');
                $revenueItems[] = [
                    'invoice_no'   => $invoice->invoice_no ?? '',
                    'invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                    'description'  => $descPrefix . $partnerName,
                    'office'       => $officeName,
                    'currency'     => $currCode,
                    'total_amount' => $signedAmount,
                    'paid_amount'  => $basePaid,
                    'balance'      => $signedAmount - $basePaid,
                ];
                $totalRevenue += $signedAmount;
            } elseif ($invType === 'AP') {
                $isCostReduction = ($isDN || $isCN);
                $signedAmount = $isCostReduction ? -$baseAmount : $baseAmount;
                $partnerName = $invoice->billTo?->name ?? 'Payable To';
                $descPrefix = $isDN ? 'Debit Note (Cost Recovery) - ' : ($isCN ? 'Credit Note (Cost Reduction) - ' : 'Operating Expense - ');
                $expenseItems[] = [
                    'invoice_no'   => $invoice->invoice_no ?? '',
                    'invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                    'description'  => $descPrefix . $partnerName,
                    'office'       => $officeName,
                    'currency'     => $currCode,
                    'total_amount' => $signedAmount,
                    'paid_amount'  => $basePaid,
                    'balance'      => $signedAmount - $basePaid,
                ];
                $totalExpenses += $signedAmount;
            }
        }

        // Include Customer / Vendor Payment Discounts (consistent with Balance Sheet Net Income)
        $discPayments = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('discount', '>', 0)
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        foreach ($discPayments as $dp) {
            $discUSD = $toUSD((float) $dp->discount, $dp->currency?->code, $dp->exchange_rate);
            if ($discUSD <= 0.001) continue;
            if (strtoupper($dp->type ?? '') === 'RECEIVED') {
                $revenueItems[] = [
                    'invoice_no'   => $dp->payment_no ?? 'DISC',
                    'invoice_date' => $dp->payment_date?->format('Y-m-d') ?? '',
                    'description'  => 'Sales Discount Allowed (Money Receipt)',
                    'office'       => 'HQ',
                    'currency'     => 'USD',
                    'total_amount' => -$discUSD,
                    'paid_amount'  => -$discUSD,
                    'balance'      => 0.0,
                ];
                $totalRevenue -= $discUSD;
            } elseif (strtoupper($dp->type ?? '') === 'MADE') {
                $expenseItems[] = [
                    'invoice_no'   => $dp->payment_no ?? 'DISC',
                    'invoice_date' => $dp->payment_date?->format('Y-m-d') ?? '',
                    'description'  => 'Purchase Discount Earned (Vendor Payment)',
                    'office'       => 'HQ',
                    'currency'     => 'USD',
                    'total_amount' => -$discUSD,
                    'paid_amount'  => -$discUSD,
                    'balance'      => 0.0,
                ];
                $totalExpenses -= $discUSD;
            }
        }

        // Include posted manual Journal Entries for THIS company affecting Revenue (4xxx) or Expense (5xxx/6xxx) accounts
        // Resolve each line against gl_accounts first, falling back to company-scoped legacy gl_codes only when absent from gl_accounts
        $allJeLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->leftJoin('trade_partners', 'trade_partners.id', '=', 'journal_entry_lines.trade_partner_id')
            ->leftJoin('gl_accounts', 'gl_accounts.id', '=', 'journal_entry_lines.gl_account_id')
            ->leftJoin('gl_codes', function ($join) use ($companyId) {
                $join->on('gl_codes.id', '=', 'journal_entry_lines.gl_account_id')
                     ->where('gl_codes.company_id', '=', $companyId);
            })
            ->where('journal_entries.company_id', $companyId)
            ->whereNull('journal_entries.deleted_at')
            ->where('journal_entries.status', '=', 'POSTED')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
            ->when($officeId, fn($q) => $q->where('journal_entries.office_id', $officeId))
            ->select(
                'journal_entry_lines.*',
                'journal_entries.entry_no',
                'journal_entries.entry_date',
                'trade_partners.name as partner_name',
                'gl_accounts.code as gl_acc_code',
                'gl_accounts.type as gl_acc_type',
                'gl_codes.gl_code as legacy_gl_code'
            )
            ->get();

        foreach ($allJeLines as $line) {
            $accType = strtoupper((string) ($line->gl_acc_type ?? ''));
            $accCode = (string) ($line->gl_acc_code ?? $line->legacy_gl_code ?? '');
            $dr = (float) ($line->local_debit ?? 0);
            $cr = (float) ($line->local_credit ?? 0);

            $isRevenueAcc = $accType === 'REVENUE' || (empty($accType) && str_starts_with($accCode, '4'));
            $isExpenseAcc = $accType === 'EXPENSE' || (empty($accType) && (str_starts_with($accCode, '5') || str_starts_with($accCode, '6')));

            if ($isRevenueAcc) {
                $amt = $cr - $dr;
                if (abs($amt) <= 0.001) continue;
                $revenueItems[] = [
                    'invoice_no'   => $line->entry_no ?? 'JE',
                    'invoice_date' => $line->entry_date ? date('Y-m-d', strtotime($line->entry_date)) : '',
                    'description'  => 'Journal Adjustment - ' . ($line->description ?: ($line->partner_name ?: 'Revenue Adjustment')),
                    'office'       => 'HQ',
                    'currency'     => 'USD',
                    'total_amount' => $amt,
                    'paid_amount'  => $amt,
                    'balance'      => 0.0,
                ];
                $totalRevenue += $amt;
            } elseif ($isExpenseAcc) {
                $amt = $dr - $cr;
                if (abs($amt) <= 0.001) continue;
                $expenseItems[] = [
                    'invoice_no'   => $line->entry_no ?? 'JE',
                    'invoice_date' => $line->entry_date ? date('Y-m-d', strtotime($line->entry_date)) : '',
                    'description'  => 'Journal Adjustment - ' . ($line->description ?: ($line->partner_name ?: 'Expense Adjustment')),
                    'office'       => 'HQ',
                    'currency'     => 'USD',
                    'total_amount' => $amt,
                    'paid_amount'  => $amt,
                    'balance'      => 0.0,
                ];
                $totalExpenses += $amt;
            }
        }

        $totalRevenue  = round($totalRevenue, 2);
        $totalExpenses = round($totalExpenses, 2);
        $netIncome     = round($totalRevenue - $totalExpenses, 2);
        $marginPct     = ($totalRevenue != 0) ? round(($netIncome / $totalRevenue) * 100, 2) : 0.0;

        $months = [];
        if ($type === 'bymonth') {
            $current = $startCarbon->copy()->startOfMonth();
            while ($current->lte($endCarbon)) {
                $mStart = $current->copy()->startOfMonth();
                $mEnd   = $current->copy()->endOfMonth();
                if ($mStart->lt($startCarbon)) $mStart = $startCarbon->copy();
                if ($mEnd->gt($endCarbon)) $mEnd = $endCarbon->copy();

                $mLabel = $current->format('M Y');
                $mRevenue  = 0.0;
                $mExpenses = 0.0;

                foreach ($revenueItems as $ri) {
                    $d = Carbon::parse($ri['invoice_date']);
                    if ($d->gte($mStart) && $d->lte($mEnd)) {
                        $mRevenue += $ri['total_amount'];
                    }
                }
                foreach ($expenseItems as $ei) {
                    $d = Carbon::parse($ei['invoice_date']);
                    if ($d->gte($mStart) && $d->lte($mEnd)) {
                        $mExpenses += $ei['total_amount'];
                    }
                }

                $months[] = [
                    'label'    => $mLabel,
                    'revenue'  => round($mRevenue, 2),
                    'expenses' => round($mExpenses, 2),
                    'net'      => round($mRevenue - $mExpenses, 2),
                ];

                $current->addMonth();
            }
        }

        return response()->json([
            'success'        => true,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'office_id'      => $officeId,
            'type'           => $type,
            'revenue_items'  => $revenueItems,
            'expense_items'  => $expenseItems,
            'total_revenue'  => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income'     => $netIncome,
            'margin_pct'     => $marginPct,
            'months'         => $months,
        ]);
    }

    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.income-statement-print', [
            'revenueItems'  => $data['revenue_items'] ?? [],
            'expenseItems'  => $data['expense_items'] ?? [],
            'totalRevenue'  => $data['total_revenue'] ?? 0,
            'totalExpenses' => $data['total_expenses'] ?? 0,
            'netIncome'     => $data['net_income'] ?? 0,
            'marginPct'     => $data['margin_pct'] ?? 0,
            'startDate'     => $request->start_date ?? date('Y-01-01'),
            'endDate'       => $request->end_date ?? date('Y-m-d'),
            'type'          => $request->type ?? 'standard',
            'months'        => $data['months'] ?? [],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data          = $this->view($request)->getData(true);
        $revenueItems  = $data['revenue_items'] ?? [];
        $expenseItems  = $data['expense_items'] ?? [];
        $totalRevenue  = $data['total_revenue'] ?? 0;
        $totalExpenses = $data['total_expenses'] ?? 0;
        $netIncome     = $data['net_income'] ?? 0;

        $filename = 'income-statement-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($revenueItems, $expenseItems, $totalRevenue, $totalExpenses, $netIncome) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Income Statement Report']);
            fputcsv($handle, ['Period', (request()->start_date ?? date('Y-01-01')) . ' ~ ' . (request()->end_date ?? date('Y-m-d'))]);
            fputcsv($handle, []);

            // Revenue Section
            fputcsv($handle, ['REVENUE']);
            fputcsv($handle, ['Invoice No', 'Date', 'Description', 'Office', 'Currency', 'Total Amount', 'Paid Amount', 'Balance']);
            foreach ($revenueItems as $item) {
                fputcsv($handle, [
                    $item['invoice_no'],
                    $item['invoice_date'],
                    $item['description'],
                    $item['office'],
                    $item['currency'],
                    number_format($item['total_amount'], 2),
                    number_format($item['paid_amount'], 2),
                    number_format($item['balance'], 2),
                ]);
            }
            fputcsv($handle, ['', '', '', '', 'Total Revenue', number_format($totalRevenue, 2)]);
            fputcsv($handle, []);

            // Expenses Section
            fputcsv($handle, ['EXPENSES']);
            fputcsv($handle, ['Invoice No', 'Date', 'Description', 'Office', 'Currency', 'Total Amount', 'Paid Amount', 'Balance']);
            foreach ($expenseItems as $item) {
                fputcsv($handle, [
                    $item['invoice_no'],
                    $item['invoice_date'],
                    $item['description'],
                    $item['office'],
                    $item['currency'],
                    number_format($item['total_amount'], 2),
                    number_format($item['paid_amount'], 2),
                    number_format($item['balance'], 2),
                ]);
            }
            fputcsv($handle, ['', '', '', '', 'Total Expenses', number_format($totalExpenses, 2)]);
            fputcsv($handle, []);

            // Net Income
            fputcsv($handle, ['', '', '', '', 'NET INCOME', number_format($netIncome, 2)]);

            fclose($handle);
        }, 200, $headers);
    }
}
