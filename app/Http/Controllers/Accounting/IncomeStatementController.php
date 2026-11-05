<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Office;
use Illuminate\Http\Request;
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

        $startCarbon = Carbon::parse($startDate);
        $endCarbon   = Carbon::parse($endDate);

        $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->with(['payments', 'office', 'currency'])
            ->get();

        $revenueItems   = [];
        $expenseItems   = [];
        $totalRevenue   = 0;
        $totalExpenses  = 0;

        foreach ($invoices as $invoice) {
            $totalPaid  = (float) $invoice->payments->sum('amount');
            $balance    = (float) $invoice->total_amount;
            $invType    = strtoupper($invoice->type ?? '');
            $isCN       = strtoupper($invoice->document_type ?? '') === 'CN';
            $officeName = $invoice->office?->name ?? 'N/A';
            $currCode   = $invoice->currency?->code ?? $invoice->currency?->name ?? 'USD';
            $rate       = (float) ($invoice->exchange_rate ?? $invoice->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;

            // Convert to base functional currency (USD)
            $baseBalance = ($rate != 1.0) ? round($balance / $rate, 2) : $balance;
            $basePaid    = ($rate != 1.0) ? round($totalPaid / $rate, 2) : $totalPaid;

            $signedAmount = $isCN ? -$baseBalance : $baseBalance;

            $item = [
                'invoice_no'   => $invoice->invoice_no ?? '',
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                'description'  => $invoice->description ?? $invoice->remark ?? '',
                'office'       => $officeName,
                'currency'     => $currCode,
                'total_amount' => $signedAmount,
                'paid_amount'  => $basePaid,
                'balance'      => $baseBalance - $basePaid,
            ];

            if (in_array($invType, ['AR', 'RECEIVED'])) {
                $revenueItems[] = $item;
                $totalRevenue += $signedAmount;
            } else {
                $expenseItems[] = $item;
                $totalExpenses += $baseBalance;
            }
        }

        $netIncome = $totalRevenue - $totalExpenses;

        $months = [];
        if ($type === 'bymonth') {
            $current = $startCarbon->copy()->startOfMonth();
            while ($current->lte($endCarbon)) {
                $mStart = $current->copy()->startOfMonth();
                $mEnd   = $current->copy()->endOfMonth();
                if ($mStart->lt($startCarbon)) $mStart = $startCarbon->copy();
                if ($mEnd->gt($endCarbon)) $mEnd = $endCarbon->copy();

                $mLabel = $current->format('M Y');
                $mRevenue  = 0;
                $mExpenses = 0;

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
                    'revenue'  => $mRevenue,
                    'expenses' => $mExpenses,
                    'net'      => $mRevenue - $mExpenses,
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
            'startDate'     => $request->start_date ?? date('Y-m-d'),
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

        return response()->stream(function () use ($revenueItems, $expenseItems, $totalRevenue, $totalExpenses, $netIncome, $request) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Income Statement']);
            fputcsv($handle, ['Period', $request->start_date ?? '' . ' ~ ' . $request->end_date ?? '']);
            fputcsv($handle, []);

            fputcsv($handle, ['REVENUE']);
            fputcsv($handle, ['Invoice No', 'Invoice Date', 'Description', 'Office', 'Currency', 'Total Amount', 'Paid Amount', 'Balance']);
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
            fputcsv($handle, ['Total Revenue', '', '', '', '', number_format($totalRevenue, 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['EXPENSES']);
            fputcsv($handle, ['Invoice No', 'Invoice Date', 'Description', 'Office', 'Currency', 'Total Amount', 'Paid Amount', 'Balance']);
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
            fputcsv($handle, ['Total Expenses', '', '', '', '', number_format($totalExpenses, 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['NET INCOME', '', '', '', '', number_format($netIncome, 2)]);

            fclose($handle);
        }, 200, $headers);
    }
}
