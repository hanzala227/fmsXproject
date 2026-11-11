<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\AccountingPayment;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $bankNames = DB::table('banks')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->pluck('bank_name')
            ->merge(
                AccountingPayment::where('company_id', $companyId)
                    ->whereNotNull('bank_name')
                    ->where('bank_name', '!=', '')
                    ->distinct()
                    ->pluck('bank_name')
            )
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $offices = Office::where('is_active', true)->orderBy('code')->get();

        return view('accounting.bank-reconciliation', compact('bankNames', 'offices'));
    }

    private function getReportData(Request $request)
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $bankName = $request->input('bank_name');
        $periodDate = $request->input('period_date', date('Y-m-d'));
        $officeId = $request->input('office_id');

        $bankRow = !empty($bankName)
            ? DB::table('banks')->where('bank_name', $bankName)->where('company_id', $companyId)->first()
            : null;
        if ($bankRow) {
            $openingBalance = (float) $bankRow->initial_amount;
            $bankCurrency = $bankRow->currency ?? 'USD';
        } else {
            // bank_accounts table is legacy and unscoped by company_id; default safely without crashing
            $openingBalance = 0.0;
            $bankCurrency = 'USD';
        }

        $baseQuery = AccountingPayment::where('company_id', $companyId)
            ->where('bank_name', $bankName)
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->whereNull('void_date')
            ->where('payment_date', '<=', $periodDate);

        if ($officeId) {
            $baseQuery->where('office_id', $officeId);
        }

        $allPayments = $baseQuery->with(['tradePartner', 'currency', 'office'])->get();

        $deposits = $allPayments->where('type', 'RECEIVED');
        $checks = $allPayments->where('type', 'MADE');

        $totalDeposits = $deposits->sum('amount');
        $totalChecks = $checks->sum('amount');

        $isClearedByPeriod = fn ($p) => $p->clear_date !== null && $p->clear_date->toDateString() <= $periodDate;

        $clearedDeposits = $deposits->filter($isClearedByPeriod);
        $clearedChecks = $checks->filter($isClearedByPeriod);
        $outstandingDeposits = $deposits->reject($isClearedByPeriod);
        $outstandingChecks = $checks->reject($isClearedByPeriod);

        $clearedDepositAmount = $clearedDeposits->sum('amount');
        $clearedCheckAmount = $clearedChecks->sum('amount');
        $outstandingDepositAmount = $outstandingDeposits->sum('amount');
        $outstandingCheckAmount = $outstandingChecks->sum('amount');

        $statementBeginning = $openingBalance;
        $statementEnding = $openingBalance + $clearedDepositAmount - $clearedCheckAmount;
        $actualEnding = $statementEnding + $outstandingDepositAmount - $outstandingCheckAmount;

        $bookBeginning = $openingBalance;
        $bookEnding = $openingBalance + $totalDeposits - $totalChecks;

        $depositRows = [];
        foreach ($outstandingDeposits as $p) {
            $depositRows[] = [
                'id' => $p->id,
                'post_date' => $p->payment_date?->format('Y-m-d') ?? '--',
                'check_no' => $p->check_no ?: 'View',
                'received_from' => $p->tradePartner?->name ?? 'N/A',
                'currency' => $p->currency?->code ?? $bankCurrency,
                'amount' => round((float) $p->amount, 2),
                'office' => $p->office?->code ?? 'N/A',
                'deposit' => $p->clear_date ? 'Y' : 'N',
                'deposit_date' => $p->clear_date?->format('Y-m-d') ?? '--',
                'void' => $p->void_date ? 'Y' : 'N',
                'void_date' => $p->void_date?->format('Y-m-d') ?? '--',
            ];
        }

        $checkRows = [];
        foreach ($outstandingChecks as $p) {
            $checkRows[] = [
                'id' => $p->id,
                'post_date' => $p->payment_date?->format('Y-m-d') ?? '--',
                'check_no' => $p->check_no ?: 'View',
                'pay_to' => $p->tradePartner?->name ?? 'N/A',
                'currency' => $p->currency?->code ?? $bankCurrency,
                'amount' => round((float) $p->amount, 2),
                'office' => $p->office?->code ?? 'N/A',
                'clear' => $p->clear_date ? 'Y' : 'N',
                'clear_date' => $p->clear_date?->format('Y-m-d') ?? '--',
                'void' => $p->void_date ? 'Y' : 'N',
                'void_date' => $p->void_date?->format('Y-m-d') ?? '--',
            ];
        }

        $bankBookDiff = round($actualEnding - $bookEnding, 2);

        return [
            'bank_name' => $bankName,
            'bank_currency' => $bankCurrency,
            'period_date' => $periodDate,
            'summary' => [
                'statement' => [
                    'beginning_balance' => round($statementBeginning, 2),
                    'deposit_credit' => round($clearedDepositAmount, 2),
                    'checks_debit' => round($clearedCheckAmount, 2),
                    'ending_balance' => round($statementEnding, 2),
                ],
                'outstanding' => [
                    'deposit_credit' => round($outstandingDepositAmount, 2),
                    'checks_debit' => round($outstandingCheckAmount, 2),
                    'actual_ending' => round($actualEnding, 2),
                ],
                'book' => [
                    'beginning_balance' => round($bookBeginning, 2),
                    'deposit_credit' => round($totalDeposits, 2),
                    'checks_debit' => round($totalChecks, 2),
                    'ending_balance' => round($bookEnding, 2),
                ],
                'bank_book_diff' => $bankBookDiff,
            ],
            'deposit_rows' => $depositRows,
            'check_rows' => $checkRows,
        ];
    }

    public function view(Request $request)
    {
        if (!$request->input('bank_name')) {
            return response()->json(['success' => false, 'message' => 'Please select a bank.'], 422);
        }

        $data = $this->getReportData($request);
        $data['success'] = true;

        return response()->json($data);
    }

    public function reconcile(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'clear_date' => 'required|date',
        ]);

        $companyId = auth()->user()?->company_id;
        $payment = AccountingPayment::when($companyId, fn($q) => $q->where('company_id', $companyId))->findOrFail($request->input('payment_id'));
        $payment->clear_date = $request->input('clear_date');
        $payment->save();

        return response()->json(['success' => true, 'message' => 'Payment reconciled successfully.']);
    }

    public function unreconcile(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $companyId = auth()->user()?->company_id;
        $payment = AccountingPayment::when($companyId, fn($q) => $q->where('company_id', $companyId))->findOrFail($request->input('payment_id'));
        $payment->clear_date = null;
        $payment->save();

        return response()->json(['success' => true, 'message' => 'Payment unreconciled successfully.']);
    }

    public function printReport(Request $request)
    {
        $data = $this->getReportData($request);

        return view('accounting.bank-reconciliation-print', $data);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);

        $filename = 'bank-reconciliation-' . $data['bank_name'] . '-' . $data['period_date'] . '-' . now()->format('His') . '.csv';

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Bank Reconciliation - ' . $data['bank_name'] . ' - As of ' . $data['period_date']]);
            fwrite($handle, "\n");

            fputcsv($handle, ['STATEMENT BALANCE']);
            fputcsv($handle, ['Beginning Balance', number_format($data['summary']['statement']['beginning_balance'], 2)]);
            fputcsv($handle, ['Deposit and Credit', number_format($data['summary']['statement']['deposit_credit'], 2)]);
            fputcsv($handle, ['Checks and Debit', number_format($data['summary']['statement']['checks_debit'], 2)]);
            fputcsv($handle, ['Ending Balance', number_format($data['summary']['statement']['ending_balance'], 2)]);
            fwrite($handle, "\n");

            fputcsv($handle, ['OUTSTANDING']);
            fputcsv($handle, ['Deposit and Credit', number_format($data['summary']['outstanding']['deposit_credit'], 2)]);
            fputcsv($handle, ['Checks and Debit', number_format($data['summary']['outstanding']['checks_debit'], 2)]);
            fputcsv($handle, ['Actual Ending Balance', number_format($data['summary']['outstanding']['actual_ending'], 2)]);
            fwrite($handle, "\n");

            fputcsv($handle, ['BOOK BALANCE']);
            fputcsv($handle, ['Beginning Balance', number_format($data['summary']['book']['beginning_balance'], 2)]);
            fputcsv($handle, ['Deposit and Credit', number_format($data['summary']['book']['deposit_credit'], 2)]);
            fputcsv($handle, ['Checks and Debit', number_format($data['summary']['book']['checks_debit'], 2)]);
            fputcsv($handle, ['Ending Balance', number_format($data['summary']['book']['ending_balance'], 2)]);
            fwrite($handle, "\n");
            fputcsv($handle, ['Bank & Book Difference', number_format($data['summary']['bank_book_diff'], 2)]);
            fwrite($handle, "\n");

            fputcsv($handle, ['DEPOSIT & CREDIT (Outstanding)']);
            fputcsv($handle, ['Post Date', 'Check No.', 'Received From', 'Currency', 'Amount', 'Office']);
            foreach ($data['deposit_rows'] as $row) {
                fputcsv($handle, [$row['post_date'], $row['check_no'], $row['received_from'], $row['currency'], number_format($row['amount'], 2), $row['office']]);
            }
            fwrite($handle, "\n");

            fputcsv($handle, ['CHECKS & DEBIT (Outstanding)']);
            fputcsv($handle, ['Post Date', 'Check No.', 'Pay To', 'Currency', 'Amount', 'Office']);
            foreach ($data['check_rows'] as $row) {
                fputcsv($handle, [$row['post_date'], $row['check_no'], $row['pay_to'], $row['currency'], number_format($row['amount'], 2), $row['office']]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
