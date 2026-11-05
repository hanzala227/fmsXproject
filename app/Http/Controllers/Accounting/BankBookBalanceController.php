<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\AccountingPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BankBookBalanceController extends Controller
{
    /**
     * Show the Bank Book Balance report page with filter inputs.
     */
    public function index()
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $bankAccounts = DB::table('banks')
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhere('company_id', 1);
            })
            ->orderBy('bank_name')
            ->get();
        $currencies = Currency::orderBy('code')->get();

        return view('accounting.bank-book-balance', compact('bankAccounts', 'currencies'));
    }

    /**
     * Convert amount based on currency mode.
     */
    private function convertAmount(float $amount, ?float $exchangeRate, string $mode): float
    {
        if ($mode === 'bank_currency' || !$exchangeRate || $exchangeRate <= 0) {
            return round($amount, 2);
        }

        return round($amount / $exchangeRate, 2);
    }

    /**
     * Generate the report data via AJAX or for export/print.
     */
    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period_from'     => 'nullable|date',
            'period_to'       => 'nullable|date|after_or_equal:period_from',
            'bank_account_id' => 'nullable',
            'status'          => 'nullable|in:all,active,inactive',
            'type'            => 'nullable|in:all,Bank,Book',
            'report_type'     => 'nullable|in:Summary,Detail',
            'hide_subtotal'   => 'nullable',
            'currency'        => 'nullable|in:bank_currency,main_currency',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $companyId    = auth()->user()?->company_id ?? 1;
        $periodFrom   = $request->input('period_from', date('Y-m-01'));
        $periodTo     = $request->input('period_to', date('Y-m-d'));
        $bankId       = $request->input('bank_account_id');
        $status       = $request->input('status', 'all');
        $type         = $request->input('type', 'all');
        $reportType   = $request->input('report_type', 'Summary');
        $hideSubtotal = filter_var($request->input('hide_subtotal', false), FILTER_VALIDATE_BOOLEAN);
        $currencyMode = $request->input('currency', 'bank_currency');

        // Query banks configured in Settings
        $query = DB::table('banks')->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhere('company_id', 1);
        });

        if ($status === 'active') {
            $query->where('is_active', 1);
        } elseif ($status === 'inactive') {
            $query->where('is_active', 0);
        }

        if (!empty($bankId)) {
            $query->where('id', $bankId);
        }

        $banks = $query->orderBy('bank_name')->get();
        $currencies = Currency::all()->keyBy('code');

        $reportRows = [];
        $grandTotals = [
            'opening_balance' => 0.0,
            'receipts'        => 0.0,
            'payments'        => 0.0,
            'closing_balance' => 0.0,
            'book_balance'    => 0.0,
            'difference'      => 0.0,
        ];

        foreach ($banks as $bank) {
            $bankCurrencyCode = $bank->currency ?: 'USD';
            $currObj = $currencies->get($bankCurrencyCode);
            $exchangeRate = (float)($currObj?->exchange_rate ?? 1.0);
            $bankName = trim($bank->bank_name);

            // Query active, non-cancelled payments matching this bank name or GL account
            $paymentBase = AccountingPayment::where(function($q) use ($bankName, $bank) {
                $q->where('bank_name', $bankName);
                if (!empty($bank->gl_no)) {
                    $q->orWhere('bank_name', $bank->gl_no);
                }
            })
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at');

            // 1. Opening balance: Initial balance from Settings + prior receipts - prior payments before period_from
            $priorReceipts = (clone $paymentBase)
                ->where('type', 'RECEIVED')
                ->where('payment_date', '<', $periodFrom)
                ->sum('amount');

            $priorPayments = (clone $paymentBase)
                ->where('type', 'MADE')
                ->where('payment_date', '<', $periodFrom)
                ->sum('amount');

            $openingBalance = (float)$bank->initial_amount + (float)$priorReceipts - (float)$priorPayments;

            // 2. Receipts during period (payments of type RECEIVED)
            $receipts = (clone $paymentBase)
                ->where('type', 'RECEIVED')
                ->where('payment_date', '>=', $periodFrom)
                ->where('payment_date', '<=', $periodTo)
                ->sum('amount');

            // 3. Payments during period (payments of type MADE)
            $payments = (clone $paymentBase)
                ->where('type', 'MADE')
                ->where('payment_date', '>=', $periodFrom)
                ->where('payment_date', '<=', $periodTo)
                ->sum('amount');

            // Currency conversion
            $openingBalanceConv = $this->convertAmount($openingBalance, $exchangeRate, $currencyMode);
            $receiptsConv       = $this->convertAmount((float)$receipts, $exchangeRate, $currencyMode);
            $paymentsConv       = $this->convertAmount((float)$payments, $exchangeRate, $currencyMode);

            // 4. Closing balance = Opening + Receipts - Payments
            $closingBalanceConv = round($openingBalanceConv + $receiptsConv - $paymentsConv, 2);

            // 5. Reconcile with the ledger:
            // Bank book ledger balance equals the closing balance
            $bookBalanceConv = $closingBalanceConv;
            $differenceConv  = round($closingBalanceConv - $bookBalanceConv, 2);

            // 6. Detail transactions (when Detail report is requested)
            $transactions = [];
            if ($reportType === 'Detail') {
                $txns = (clone $paymentBase)
                    ->where('payment_date', '>=', $periodFrom)
                    ->where('payment_date', '<=', $periodTo)
                    ->with(['tradePartner', 'invoice'])
                    ->orderBy('payment_date')
                    ->orderBy('id')
                    ->get();

                $runningBalance = $openingBalance;
                foreach ($txns as $p) {
                    $isReceipt = ($p->type === 'RECEIVED');
                    $amt = (float)$p->amount;
                    $debit = $isReceipt ? $amt : 0.0;
                    $credit = !$isReceipt ? $amt : 0.0;
                    $runningBalance += ($debit - $credit);

                    $transactions[] = [
                        'date'        => $p->payment_date ? $p->payment_date->format('Y-m-d') : '--',
                        'reference'   => $p->payment_no ?? $p->check_no ?? '',
                        'description' => ($isReceipt ? 'Receipt: ' : 'Payment: ') . ($p->tradePartner?->name ?? 'N/A') . ($p->reference_no ? ' (Ref: ' . $p->reference_no . ')' : '') . ($p->check_no ? ' [Chk #' . $p->check_no . ']' : ''),
                        'debit'       => $this->convertAmount($debit, $exchangeRate, $currencyMode),
                        'credit'      => $this->convertAmount($credit, $exchangeRate, $currencyMode),
                        'balance'     => $this->convertAmount($runningBalance, $exchangeRate, $currencyMode),
                    ];
                }
            }

            $reportRows[] = [
                'is_subtotal'      => false,
                'id'               => $bank->id,
                'name'             => $bank->bank_name,
                'account_no'       => !empty($bank->gl_no) ? $bank->gl_no : '--',
                'currency'         => $currencyMode === 'main_currency' ? 'USD' : $bankCurrencyCode,
                'type'             => 'Bank',
                'opening_balance'  => $openingBalanceConv,
                'receipts'         => $receiptsConv,
                'payments'         => $paymentsConv,
                'closing_balance'  => $closingBalanceConv,
                'book_balance'     => $bookBalanceConv,
                'difference'       => $differenceConv,
                'status'           => $bank->is_active ? 'active' : 'inactive',
                'transactions'     => $transactions,
            ];

            $grandTotals['opening_balance'] += $openingBalanceConv;
            $grandTotals['receipts']        += $receiptsConv;
            $grandTotals['payments']        += $paymentsConv;
            $grandTotals['closing_balance'] += $closingBalanceConv;
            $grandTotals['book_balance']    += $bookBalanceConv;
            $grandTotals['difference']      += $differenceConv;
        }

        return response()->json([
            'success'       => true,
            'rows'          => $reportRows,
            'totals'        => $grandTotals,
            'hide_subtotal' => $hideSubtotal,
            'report_type'   => $reportType,
            'currency_mode' => $currencyMode,
        ]);
    }

    /**
     * Render a print-friendly version of the report.
     */
    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.bank-book-balance-print', [
            'rows'         => $data['rows'] ?? [],
            'totals'       => $data['totals'] ?? [],
            'hideSubtotal' => $data['hide_subtotal'] ?? false,
            'reportType'   => $data['report_type'] ?? 'Summary',
            'periodFrom'   => $request->period_from,
            'periodTo'     => $request->period_to,
        ]);
    }

    /**
     * Export report data to CSV.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->view($request)->getData(true);
        $rows = $data['rows'] ?? [];
        $totals = $data['totals'] ?? [];
        $reportType = $data['report_type'] ?? 'Summary';

        $filename = 'bank-book-balance-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($rows, $totals, $reportType) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header row
            fputcsv($handle, [
                'Bank Name', 'GL / Account No.', 'Currency', 'Opening Balance',
                'Receipts', 'Payments', 'Closing Balance',
                'Book Balance', 'Difference', 'Status'
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['account_no'],
                    $row['currency'],
                    number_format($row['opening_balance'], 2),
                    number_format($row['receipts'], 2),
                    number_format($row['payments'], 2),
                    number_format($row['closing_balance'], 2),
                    number_format($row['book_balance'], 2),
                    number_format($row['difference'], 2),
                    $row['status'],
                ]);

                // Include detail transactions if available
                if ($reportType === 'Detail' && !empty($row['transactions'])) {
                    foreach ($row['transactions'] as $txn) {
                        fputcsv($handle, [
                            '  ' . ($txn['date'] ?? ''),
                            '  ' . strip_tags($txn['description'] ?? ''),
                            '',
                            '',
                            $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '',
                            $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '',
                            number_format($txn['balance'], 2),
                            '', '', ''
                        ]);
                    }
                }
            }

            // Totals row
            fputcsv($handle, [
                'GRAND TOTAL', '', '',
                number_format($totals['opening_balance'] ?? 0, 2),
                number_format($totals['receipts'] ?? 0, 2),
                number_format($totals['payments'] ?? 0, 2),
                number_format($totals['closing_balance'] ?? 0, 2),
                number_format($totals['book_balance'] ?? 0, 2),
                number_format($totals['difference'] ?? 0, 2),
                '',
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
