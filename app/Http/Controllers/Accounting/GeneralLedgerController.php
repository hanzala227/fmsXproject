<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Office;
use App\Models\TradePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralLedgerController extends Controller
{
    /**
     * Show the General Ledger Report page with filter inputs.
     */
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $glAccounts = $this->getGLAccounts();

        return view('accounting.general-ledger', compact('offices', 'glAccounts'));
    }

    /**
     * Get available GL accounts for the filter dropdowns.
     */
    private function getGLAccounts(): array
    {
        return [
            ['code' => '1000', 'name' => 'Cash & Cash Equivalents'],
            ['code' => '1200', 'name' => 'Accounts Receivable'],
            ['code' => '2000', 'name' => 'Accounts Payable'],
            ['code' => '4000', 'name' => 'Revenue'],
            ['code' => '5000', 'name' => 'Expenses'],
        ];
    }

    /**
     * Generate the General Ledger Report data via AJAX.
     */
    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period_from'  => 'nullable|date',
            'period_to'    => 'nullable|date|after_or_equal:period_from',
            'office_id'    => 'nullable|exists:offices,id',
            'report_type'  => 'nullable|in:summary,detail,trade_partner,ga_expense',
            'gl_from'      => 'nullable|string',
            'gl_to'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $periodFrom = $request->input('period_from', date('Y-01-01'));
        $periodTo   = $request->input('period_to', date('Y-m-d'));
        $officeId   = $request->input('office_id');
        $reportType = $request->input('report_type', 'summary');
        $glFrom     = $request->input('gl_from');
        $glTo       = $request->input('gl_to');

        $accounts = [];

        // Build GL accounts list with optional range filter
        $allAccounts = $this->getGLAccounts();
        if ($glFrom) {
            $allAccounts = array_filter($allAccounts, fn($a) => $a['code'] >= $glFrom);
        }
        if ($glTo) {
            $allAccounts = array_filter($allAccounts, fn($a) => $a['code'] <= $glTo);
        }

        foreach ($allAccounts as $glAccount) {
            $code = $glAccount['code'];
            $name = $glAccount['name'];

            // Calculate opening balance (sum of all transactions before period_from)
            $openingBalance = $this->calculateOpeningBalance($code, $periodFrom, $officeId);

            // Get transactions in period
            $transactions = $this->getTransactions($code, $periodFrom, $periodTo, $officeId, $reportType);

            // Calculate closing balance
            $totalDebit  = array_sum(array_column($transactions, 'debit'));
            $totalCredit = array_sum(array_column($transactions, 'credit'));
            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $accounts[] = [
                'code'            => $code,
                'name'            => $name,
                'opening_balance' => $openingBalance,
                'transactions'    => $transactions,
                'total_debit'     => $totalDebit,
                'total_credit'    => $totalCredit,
                'closing_balance' => $closingBalance,
                'transaction_count' => count($transactions),
            ];
        }

        // Grand totals
        $grandTotalDebit  = array_sum(array_column($accounts, 'total_debit'));
        $grandTotalCredit = array_sum(array_column($accounts, 'total_credit'));
        $grandOpening     = array_sum(array_column($accounts, 'opening_balance'));
        $grandClosing     = array_sum(array_column($accounts, 'closing_balance'));

        return response()->json([
            'success'     => true,
            'period_from' => $periodFrom,
            'period_to'   => $periodTo,
            'office_id'   => $officeId,
            'report_type' => $reportType,
            'accounts'    => $accounts,
            'summary'     => [
                'grand_total_debit'  => $grandTotalDebit,
                'grand_total_credit' => $grandTotalCredit,
                'grand_opening'      => $grandOpening,
                'grand_closing'      => $grandClosing,
                'is_balanced'        => abs($grandTotalDebit - $grandTotalCredit) < 0.01,
                'account_count'      => count($accounts),
                'total_transactions' => array_sum(array_column($accounts, 'transaction_count')),
            ],
        ]);
    }

    /**
     * Calculate opening balance for an account before the period start.
     */
    private function calculateOpeningBalance(string $glCode, string $periodFrom, ?int $officeId): float
    {
        switch ($glCode) {
            case '1000': // Cash
                return (float) BankAccount::sum('opening_balance');

            case '1200': // AR
                $arTotal = (float) (Invoice::where('type', 'AR')
                    ->where('invoice_date', '<', $periodFrom)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->selectRaw("COALESCE(SUM(CASE WHEN document_type = 'CN' THEN -total_amount ELSE total_amount END), 0) as net")
                    ->value('net') ?? 0);
                $arPayments = (float) AccountingPayment::where('type', 'RECEIVED')
                    ->where('payment_date', '<', $periodFrom)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->sum('amount');
                return $arTotal - $arPayments;

            case '2000': // AP
                $apTotal = (float) (Invoice::where('type', 'AP')
                    ->where('invoice_date', '<', $periodFrom)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->selectRaw("COALESCE(SUM(CASE WHEN document_type = 'DN' THEN -total_amount ELSE total_amount END), 0) as net")
                    ->value('net') ?? 0);
                $apPayments = (float) AccountingPayment::where('type', 'MADE')
                    ->where('payment_date', '<', $periodFrom)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->sum('amount');
                return $apTotal - $apPayments;

            case '4000': // Revenue
                return (float) (Invoice::where('type', 'AR')
                    ->where('invoice_date', '<', $periodFrom)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->selectRaw("COALESCE(SUM(CASE WHEN document_type = 'CN' THEN -total_amount ELSE total_amount END), 0) as net")
                    ->value('net') ?? 0);

            case '5000': // Expenses
                return (float) (Invoice::where('type', 'AP')
                    ->where('invoice_date', '<', $periodFrom)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->selectRaw("COALESCE(SUM(CASE WHEN document_type = 'DN' THEN -total_amount ELSE total_amount END), 0) as net")
                    ->value('net') ?? 0);

            default:
                return 0;
        }
    }

    /**
     * Get transactions for an account within the period.
     */
    private function getTransactions(string $glCode, string $periodFrom, string $periodTo, ?int $officeId, string $reportType): array
    {
        $transactions = [];

        switch ($glCode) {
            case '1000': // Cash - show receipts and payments
                $receipts = AccountingPayment::where('type', 'RECEIVED')
                    ->where('payment_date', '>=', $periodFrom)
                    ->where('payment_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner', 'invoice'])
                    ->orderBy('payment_date')
                    ->get();

                foreach ($receipts as $p) {
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Receipt from ' . ($p->tradePartner?->name ?? 'N/A'),
                        'debit'       => (float) $p->amount,
                        'credit'      => 0,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => $p->invoice?->invoice_no ?? '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }

                $payments = AccountingPayment::where('type', 'MADE')
                    ->where('payment_date', '>=', $periodFrom)
                    ->where('payment_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner', 'invoice'])
                    ->orderBy('payment_date')
                    ->get();

                foreach ($payments as $p) {
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Payment to ' . ($p->tradePartner?->name ?? 'N/A'),
                        'debit'       => 0,
                        'credit'      => (float) $p->amount,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => $p->invoice?->invoice_no ?? '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }
                break;

            case '1200': // AR - show AR invoices and payments received
                $invoices = Invoice::where('type', 'AR')
                    ->where('invoice_date', '>=', $periodFrom)
                    ->where('invoice_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo'])
                    ->orderBy('invoice_date')
                    ->get();

                foreach ($invoices as $inv) {
                    $isCN = strtoupper($inv->document_type ?? '') === 'CN';
                    $amount = (float) $inv->total_amount;
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => ($isCN ? 'Credit Note - ' : 'Invoice - ') . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => 0,
                        'credit'      => $isCN ? -$amount : $amount,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }

                $arPayments = AccountingPayment::where('type', 'RECEIVED')
                    ->where('payment_date', '>=', $periodFrom)
                    ->where('payment_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner'])
                    ->orderBy('payment_date')
                    ->get();

                foreach ($arPayments as $p) {
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Payment received - ' . ($p->tradePartner?->name ?? 'N/A'),
                        'debit'       => (float) $p->amount,
                        'credit'      => 0,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }
                break;

            case '2000': // AP - show AP invoices and payments made
                $invoices = Invoice::where('type', 'AP')
                    ->where('invoice_date', '>=', $periodFrom)
                    ->where('invoice_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo'])
                    ->orderBy('invoice_date')
                    ->get();

                foreach ($invoices as $inv) {
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => 'Invoice - ' . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => (float) $inv->total_amount,
                        'credit'      => 0,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }

                $apPayments = AccountingPayment::where('type', 'MADE')
                    ->where('payment_date', '>=', $periodFrom)
                    ->where('payment_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner'])
                    ->orderBy('payment_date')
                    ->get();

                foreach ($apPayments as $p) {
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Payment made - ' . ($p->tradePartner?->name ?? 'N/A'),
                        'debit'       => 0,
                        'credit'      => (float) $p->amount,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }
                break;

            case '4000': // Revenue - show AR invoices only
                $invoices = Invoice::where('type', 'AR')
                    ->where('invoice_date', '>=', $periodFrom)
                    ->where('invoice_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo'])
                    ->orderBy('invoice_date')
                    ->get();

                foreach ($invoices as $inv) {
                    $isCN = strtoupper($inv->document_type ?? '') === 'CN';
                    $amount = (float) $inv->total_amount;
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => ($isCN ? 'Credit Note (Contra-Revenue) - ' : 'Revenue - ') . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => 0,
                        'credit'      => $isCN ? -$amount : $amount,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }
                break;

            case '5000': // Expenses - show AP invoices only
                $invoices = Invoice::where('type', 'AP')
                    ->where('invoice_date', '>=', $periodFrom)
                    ->where('invoice_date', '<=', $periodTo)
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo'])
                    ->orderBy('invoice_date')
                    ->get();

                foreach ($invoices as $inv) {
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => 'Expense - ' . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => (float) $inv->total_amount,
                        'credit'      => 0,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }
                break;
        }

        // Sort by date
        usort($transactions, fn($a, $b) => strcmp($a['date'], $b['date']));

        // Calculate running balance
        $running = 0;
        foreach ($transactions as &$txn) {
            $running += ($txn['debit'] - $txn['credit']);
            $txn['running_balance'] = $running;
        }

        return $transactions;
    }

    /**
     * Render a print-friendly version of the report.
     */
    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.general-ledger-print', [
            'accounts'   => $data['accounts'] ?? [],
            'summary'    => $data['summary'] ?? [],
            'periodFrom' => $request->period_from ?? date('Y-01-01'),
            'periodTo'   => $request->period_to ?? date('Y-m-d'),
            'reportType' => $request->report_type ?? 'summary',
        ]);
    }

    /**
     * Export report data to CSV.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->view($request)->getData(true);
        $accounts = $data['accounts'] ?? [];
        $summary  = $data['summary'] ?? [];

        $filename = 'general-ledger-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($accounts, $summary) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['General Ledger Report']);
            fputcsv($handle, ['Period', request()->period_from ?? date('Y-01-01') . ' to ' . (request()->period_to ?? date('Y-m-d'))]);
            fputcsv($handle, []);

            foreach ($accounts as $account) {
                fputcsv($handle, [strtoupper($account['name']) . ' (' . $account['code'] . ')']);
                fputcsv($handle, ['Opening Balance', '', '', '', number_format($account['opening_balance'], 2)]);
                fputcsv($handle, ['Date', 'Reference', 'Description', 'Debit', 'Credit', 'Running Balance']);

                foreach ($account['transactions'] as $txn) {
                    fputcsv($handle, [
                        $txn['date'],
                        $txn['reference'],
                        $txn['description'],
                        $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '',
                        $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '',
                        number_format($txn['running_balance'], 2),
                    ]);
                }

                fputcsv($handle, [
                    '',
                    'Total ' . $account['name'],
                    '',
                    number_format($account['total_debit'], 2),
                    number_format($account['total_credit'], 2),
                    number_format($account['closing_balance'], 2),
                ]);
                fputcsv($handle, ['Closing Balance', '', '', '', '', number_format($account['closing_balance'], 2)]);
                fputcsv($handle, []);
            }

            // Grand Total
            fputcsv($handle, ['GRAND TOTAL']);
            fputcsv($handle, [
                'Total Debit',
                '',
                '',
                number_format($summary['grand_total_debit'] ?? 0, 2),
            ]);
            fputcsv($handle, [
                'Total Credit',
                '',
                '',
                '',
                number_format($summary['grand_total_credit'] ?? 0, 2),
            ]);
            fputcsv($handle, ['Balanced', '', '', '', '', ($summary['is_balanced'] ?? false) ? 'Yes' : 'No']);

            fclose($handle);
        }, 200, $headers);
    }
}
