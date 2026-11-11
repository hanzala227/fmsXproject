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
            ->where('company_id', $companyId)
            ->where('is_active', 1)
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
        $status       = $request->input('status', 'active');
        $type         = $request->input('type', 'all');
        $reportType   = $request->input('report_type', 'Summary');
        $hideSubtotal = filter_var($request->input('hide_subtotal', false), FILTER_VALIDATE_BOOLEAN);
        $currencyMode = $request->input('currency', 'main_currency'); // Default to USD (main_currency)

        // Query banks configured in Settings
        $query = DB::table('banks')->where('company_id', $companyId);

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
        $currencySubtotals = [];
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
            if ($exchangeRate <= 0) $exchangeRate = 1.0;
            $bankName = trim($bank->bank_name);

            // Active, non-cancelled payments matching this bank name or GL account
            $paymentRows = AccountingPayment::with('currency')
                ->where('company_id', $companyId)
                ->where(function($q) use ($bankName, $bank) {
                    $q->where('bank_name', $bankName);
                    if (!empty($bank->gl_no)) {
                        $q->orWhere('bank_name', $bank->gl_no);
                    }
                })
                ->whereNull('deleted_at')
                ->whereNull('cancelled_at')
                ->whereNull('void_date')
                ->where('payment_date', '<=', $periodTo)
                ->get();

            $toPaymentUSD = function ($p) use ($exchangeRate, $currencyMode): float {
                $amt = (float) $p->amount;
                if ($currencyMode === 'bank_currency') {
                    return round($amt, 2);
                }
                $pRate = (float) ($p->exchange_rate ?? $p->currency?->exchange_rate ?? $exchangeRate);
                if ($pRate <= 0) $pRate = $exchangeRate;
                return round($amt / $pRate, 2);
            };

            $priorReceiptsUSD = 0.0;
            $priorPaymentsUSD = 0.0;
            $receiptsUSD = 0.0;
            $paymentsUSD = 0.0;
            foreach ($paymentRows as $prRow) {
                $pDate = $prRow->payment_date?->toDateString() ?? '';
                $convAmt = $toPaymentUSD($prRow);
                if ($pDate < $periodFrom) {
                    if ($prRow->type === 'RECEIVED') $priorReceiptsUSD += $convAmt;
                    elseif ($prRow->type === 'MADE') $priorPaymentsUSD += $convAmt;
                } else {
                    if ($prRow->type === 'RECEIVED') $receiptsUSD += $convAmt;
                    elseif ($prRow->type === 'MADE') $paymentsUSD += $convAmt;
                }
            }

            $openingInitConv = $this->convertAmount((float) $bank->initial_amount, $exchangeRate, $currencyMode);
            $openingBalanceConv = round($openingInitConv + $priorReceiptsUSD - $priorPaymentsUSD, 2);
            $receiptsConv       = round($receiptsUSD, 2);
            $paymentsConv       = round($paymentsUSD, 2);
            $openingBalance     = (float) $bank->initial_amount
                + (float) $paymentRows->where('type', 'RECEIVED')->filter(fn($r) => ($r->payment_date?->toDateString() ?? '') < $periodFrom)->sum('amount')
                - (float) $paymentRows->where('type', 'MADE')->filter(fn($r) => ($r->payment_date?->toDateString() ?? '') < $periodFrom)->sum('amount');

            // 4. Closing balance = Opening + Receipts - Payments
            $closingBalanceConv = round($openingBalanceConv + $receiptsConv - $paymentsConv, 2);

            // 5. Book balance & difference
            $bookBalanceConv = $closingBalanceConv;
            $differenceConv  = round($closingBalanceConv - $bookBalanceConv, 2);

            // Grand totals in USD
            $grandTotals['opening_balance'] += $openingBalanceConv;
            $grandTotals['receipts']        += $receiptsConv;
            $grandTotals['payments']        += $paymentsConv;
            $grandTotals['closing_balance'] += $closingBalanceConv;
            $grandTotals['book_balance']    += $closingBalanceConv;
            $grandTotals['difference']      += 0.0;

            // Detail transactions (when Detail report is requested)
            $transactions = [];
            if ($reportType === 'Detail') {
                $txns = $paymentRows
                    ->filter(fn($p) => ($p->payment_date?->toDateString() ?? '') >= $periodFrom && ($p->payment_date?->toDateString() ?? '') <= $periodTo)
                    ->sortBy([['payment_date', 'asc'], ['id', 'asc']]);
                $txns->load(['tradePartner', 'invoice']);

                $runningBalance = $currencyMode === 'bank_currency' ? $openingBalance : $openingBalanceConv;
                foreach ($txns as $p) {
                    $isReceipt = ($p->type === 'RECEIVED');
                    $convAmt = $toPaymentUSD($p);
                    $debit = $isReceipt ? $convAmt : 0.0;
                    $credit = !$isReceipt ? $convAmt : 0.0;
                    $runningBalance = round($runningBalance + $debit - $credit, 2);

                    $transactions[] = [
                        'date'        => $p->payment_date ? $p->payment_date->format('Y-m-d') : '--',
                        'reference'   => $p->payment_no ?? $p->check_no ?? '',
                        'description' => ($isReceipt ? 'Receipt: ' : 'Payment: ') . ($p->tradePartner?->name ?? 'N/A') . ($p->reference_no ? ' (Ref: ' . $p->reference_no . ')' : '') . ($p->check_no ? ' [Chk #' . $p->check_no . ']' : ''),
                        'debit'       => $debit,
                        'credit'      => $credit,
                        'balance'     => $runningBalance,
                    ];
                }
            }

            $currDisplay = ($currencyMode === 'main_currency')
                ? ($bankCurrencyCode !== 'USD' ? 'USD (' . $bankCurrencyCode . ' @ ' . number_format($exchangeRate, 4) . ')' : 'USD')
                : $bankCurrencyCode;

            $reportRows[] = [
                'is_subtotal'      => false,
                'id'               => $bank->id,
                'name'             => $bank->bank_name,
                'account_no'       => !empty($bank->gl_no) ? $bank->gl_no : 'N/A',
                'currency'         => $currDisplay,
                'exchange_rate'    => $exchangeRate,
                'opening_balance'  => $openingBalanceConv,
                'receipts'         => $receiptsConv,
                'payments'         => $paymentsConv,
                'closing_balance'  => $closingBalanceConv,
                'book_balance'     => $bookBalanceConv,
                'difference'       => $differenceConv,
                'status'           => $bank->is_active ? 'active' : 'inactive',
                'transactions'     => $transactions,
            ];
        }

        // Round grand totals
        foreach ($grandTotals as $k => $v) {
            $grandTotals[$k] = round($v, 2);
        }

        return response()->json([
            'success'            => true,
            'rows'               => $reportRows,
            'currency_subtotals' => [],
            'totals_usd'         => $grandTotals,
            'totals'             => $grandTotals,
            'hide_subtotal'      => $hideSubtotal,
            'report_type'        => $reportType,
            'currency_mode'      => $currencyMode,
        ]);
    }

    /**
     * Render a print-friendly version of the report.
     */
    public function printReport(Request $request)
    {
        if (!$request->has('currency')) {
            $request->merge(['currency' => 'main_currency']);
        }
        if (!$request->has('status')) {
            $request->merge(['status' => 'active']);
        }

        $data = $this->view($request)->getData(true);

        return view('accounting.bank-book-balance-print', [
            'rows'               => $data['rows'] ?? [],
            'totalsUSD'          => $data['totals_usd'] ?? [],
            'totals'             => $data['totals_usd'] ?? [],
            'hideSubtotal'       => $data['hide_subtotal'] ?? false,
            'reportType'         => $data['report_type'] ?? 'Summary',
            'currencyMode'       => $data['currency_mode'] ?? 'main_currency',
            'periodFrom'         => $request->period_from,
            'periodTo'           => $request->period_to,
        ]);
    }

    /**
     * Export report data to CSV.
     */
    public function exportExcel(Request $request)
    {
        if (!$request->has('currency')) {
            $request->merge(['currency' => 'main_currency']);
        }
        if (!$request->has('status')) {
            $request->merge(['status' => 'active']);
        }

        $data = $this->view($request)->getData(true);
        $rows = $data['rows'] ?? [];
        $totals = $data['totals_usd'] ?? [];
        $reportType = $data['report_type'] ?? 'Summary';

        $filename = 'bank-book-balance-usd-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($rows, $totals, $reportType) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header row
            fputcsv($handle, [
                'Bank Name', 'GL / Account No.', 'Currency', 'Opening Balance (USD)',
                'Receipts (USD)', 'Payments (USD)', 'Closing Balance (USD)',
                'Book Balance (USD)', 'Difference (USD)', 'Status'
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

                // Detail transactions
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

            // Grand Total USD
            fputcsv($handle, []);
            fputcsv($handle, [
                'GRAND TOTAL (USD)', '', 'USD',
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
