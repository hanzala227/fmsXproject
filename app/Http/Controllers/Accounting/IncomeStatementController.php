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
            if ($modelRate && (float) $modelRate > 0) {
                return ((float) $modelRate != 1.0) ? round(((float) $amount) / (float) $modelRate, 2) : (float) $amount;
            }
            $currCode = $currCode ?: 'USD';
            if ($currCode === 'USD') return (float) $amount;

            $rate = 1.0;
            $rateRecord = DB::table('currency_rates')
                ->where('from_currency', $currCode)
                ->where('to_currency', 'USD')
                ->orderByDesc('as_of_date')
                ->first();
            if ($rateRecord && (float) $rateRecord->rate_internal > 0) {
                $rate = (float) $rateRecord->rate_internal;
            } else {
                $curr = Currency::where('code', $currCode)->first();
                if ($curr && (float) $curr->exchange_rate > 0) {
                    $rate = (float) $curr->exchange_rate;
                }
            }
            return ($rate > 0 && $rate != 1.0) ? round(((float) $amount) / $rate, 2) : (float) $amount;
        };

        // Query active invoices scoped to company and excluding VOID/Cancelled documents
        $invoices = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', '!=', 'VOID')
            ->where('verification_status', '!=', 'Cancelled')
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
                $revenueItems[] = [
                    'invoice_no'   => $invoice->invoice_no ?? '',
                    'invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                    'description'  => ($isCN ? 'Credit Note - ' : 'Operating Revenue - ') . $partnerName,
                    'office'       => $officeName,
                    'currency'     => $currCode,
                    'total_amount' => $signedAmount,
                    'paid_amount'  => $basePaid,
                    'balance'      => $signedAmount - $basePaid,
                ];
                $totalRevenue += $signedAmount;
            } elseif ($invType === 'AP') {
                $signedAmount = $isDN ? -$baseAmount : $baseAmount;
                $partnerName = $invoice->billTo?->name ?? 'Payable To';
                $expenseItems[] = [
                    'invoice_no'   => $invoice->invoice_no ?? '',
                    'invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                    'description'  => ($isDN ? 'Debit Note - ' : 'Operating Expense - ') . $partnerName,
                    'office'       => $officeName,
                    'currency'     => $currCode,
                    'total_amount' => $signedAmount,
                    'paid_amount'  => $basePaid,
                    'balance'      => $signedAmount - $basePaid,
                ];
                $totalExpenses += $signedAmount;
            }
        }

        // Include any manual Journal Entries affecting Revenue or Expense accounts
        $journalLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->leftJoin('gl_codes', 'gl_codes.id', '=', 'journal_entry_lines.gl_account_id')
            ->whereNull('journal_entries.deleted_at')
            ->where('journal_entries.status', '!=', 'VOIDED')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
            ->when($officeId, fn($q) => $q->where('journal_entries.office_id', $officeId))
            ->select('journal_entry_lines.*', 'journal_entries.entry_no', 'journal_entries.entry_date', 'gl_codes.gl_code', 'gl_codes.gl_name_eng')
            ->get();

        foreach ($journalLines as $line) {
            $dr = (float) ($line->local_debit ?? 0);
            $cr = (float) ($line->local_credit ?? 0);
            if ($dr <= 0.001 && $cr <= 0.001) continue;

            $isRev = ($cr > $dr);
            $amt = $isRev ? ($cr - $dr) : ($dr - $cr);

            $item = [
                'invoice_no'   => $line->entry_no ?? 'JE',
                'invoice_date' => $line->entry_date ? date('Y-m-d', strtotime($line->entry_date)) : '',
                'description'  => 'Journal: ' . ($line->gl_name_eng ?: ($line->description ?: 'Journal Adjustment')),
                'office'       => 'HQ',
                'currency'     => 'USD',
                'total_amount' => $amt,
                'paid_amount'  => $amt,
                'balance'      => 0.0,
            ];

            if ($isRev) {
                $revenueItems[] = $item;
                $totalRevenue += $amt;
            } else {
                $expenseItems[] = $item;
                $totalExpenses += $amt;
            }
        }

        $totalRevenue  = round($totalRevenue, 2);
        $totalExpenses = round($totalExpenses, 2);
        $netIncome     = round($totalRevenue - $totalExpenses, 2);

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
