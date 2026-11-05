<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\AccountingJournal;
use App\Models\Currency;
use App\Models\Office;
use App\Models\TradePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GeneralLedgerController extends Controller
{
    /**
     * Show the General Ledger Report page with filter inputs.
     */
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $companyId = auth()->user()->company_id ?? 1;
        $glAccounts = $this->getGLAccounts($companyId);

        return view('accounting.general-ledger', compact('offices', 'glAccounts'));
    }

    /**
     * Get available GL accounts for the filter dropdowns.
     */
    private function getGLAccounts(?int $companyId = null): array
    {
        $baseAccounts = [
            ['code' => '1000', 'name' => 'Cash & Cash Equivalents', 'type' => 'debit'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'debit'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'credit'],
            ['code' => '3000', 'name' => "Owner's Capital / Opening Equity", 'type' => 'credit'],
            ['code' => '4000', 'name' => 'Freight & Operating Revenue', 'type' => 'credit'],
            ['code' => '5000', 'name' => 'Cost of Freight & Operating Expenses', 'type' => 'debit'],
        ];

        // Also include any GL accounts from gl_codes that have journal entries
        $usedGlIds = DB::table('journal_entry_lines')->distinct()->pluck('gl_account_id')->filter()->all();
        if (!empty($usedGlIds)) {
            $extraGl = DB::table('gl_codes')
                ->whereIn('id', $usedGlIds)
                ->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)->orWhere('company_id', 1);
                })
                ->get();
            foreach ($extraGl as $gl) {
                $code = $gl->gl_code;
                if (!in_array($code, array_column($baseAccounts, 'code'))) {
                    $baseAccounts[] = [
                        'code' => $code,
                        'name' => $gl->gl_name_eng ?: ($gl->gl_name_local ?: 'General Ledger Account'),
                        'type' => 'debit',
                    ];
                }
            }
        }

        usort($baseAccounts, fn($a, $b) => strcmp($a['code'], $b['code']));
        return $baseAccounts;
    }

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
        $companyId  = auth()->user()->company_id ?? 1;

        $accounts = [];

        // Build GL accounts list with optional range filter
        $allAccounts = $this->getGLAccounts($companyId);
        if ($glFrom) {
            $allAccounts = array_filter($allAccounts, fn($a) => $a['code'] >= $glFrom);
        }
        if ($glTo) {
            $allAccounts = array_filter($allAccounts, fn($a) => $a['code'] <= $glTo);
        }

        foreach ($allAccounts as $glAccount) {
            $code = $glAccount['code'];
            $name = $glAccount['name'];
            $type = $glAccount['type'] ?? 'debit';

            // Calculate opening balance (sum of all transactions before period_from)
            $openingBalance = $this->calculateOpeningBalance($code, $periodFrom, $officeId, $companyId);

            // Get transactions in period
            $transactions = $this->getTransactions($code, $periodFrom, $periodTo, $officeId, $companyId, $reportType, $openingBalance, $type);

            $totalDebit  = round(array_sum(array_column($transactions, 'debit')), 2);
            $totalCredit = round(array_sum(array_column($transactions, 'credit')), 2);

            // Closing balance matches final running balance or opening + activity
            if (!empty($transactions)) {
                $closingBalance = end($transactions)['running_balance'];
            } else {
                $closingBalance = $openingBalance;
            }

            $accounts[] = [
                'code'              => $code,
                'name'              => $name,
                'type'              => $type,
                'opening_balance'   => round($openingBalance, 2),
                'transactions'      => $transactions,
                'total_debit'       => $totalDebit,
                'total_credit'      => $totalCredit,
                'closing_balance'   => round($closingBalance, 2),
                'transaction_count' => count($transactions),
            ];
        }

        // Grand totals
        $grandTotalDebit  = round(array_sum(array_column($accounts, 'total_debit')), 2);
        $grandTotalCredit = round(array_sum(array_column($accounts, 'total_credit')), 2);
        $grandOpening     = round(array_sum(array_column($accounts, 'opening_balance')), 2);
        $grandClosing     = round(array_sum(array_column($accounts, 'closing_balance')), 2);

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
    private function calculateOpeningBalance(string $glCode, string $periodFrom, ?int $officeId, int $companyId): float
    {
        switch ($glCode) {
            case '1000': // Cash & Cash Equivalents
                $banks = DB::table('banks')
                    ->where(function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhere('company_id', 1);
                    })
                    ->where('is_active', 1)
                    ->get();
                $initialCash = $banks->sum(fn($b) => $this->toUSD((float) $b->initial_amount, $b->currency));

                $priorRec = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'RECEIVED')
                    ->where('payment_date', '<', $periodFrom)
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->get()
                    ->sum(fn($p) => $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate));

                $priorPay = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'MADE')
                    ->where('payment_date', '<', $periodFrom)
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->get()
                    ->sum(fn($p) => $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate));

                return $initialCash + $priorRec - $priorPay;

            case '1200': // Accounts Receivable (Asset: Debit normal)
                $arPrior = Invoice::where('company_id', $companyId)
                    ->where('type', 'AR')
                    ->where('invoice_date', '<', $periodFrom)
                    ->where('status', '!=', 'VOID')
                    ->where('verification_status', '!=', 'Cancelled')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->get()
                    ->sum(fn($i) => ($i->document_type === 'CN' ? -1 : 1) * $this->toUSD((float) $i->total_amount, $i->currency?->code, $i->exchange_rate));

                $arRecPrior = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'RECEIVED')
                    ->where('payment_date', '<', $periodFrom)
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->get()
                    ->sum(fn($p) => $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate));

                return $arPrior - $arRecPrior;

            case '2000': // Accounts Payable (Liability: Credit normal)
                $apPrior = Invoice::where('company_id', $companyId)
                    ->where('type', 'AP')
                    ->where('invoice_date', '<', $periodFrom)
                    ->where('status', '!=', 'VOID')
                    ->where('verification_status', '!=', 'Cancelled')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->get()
                    ->sum(fn($i) => ($i->document_type === 'DN' ? -1 : 1) * $this->toUSD((float) $i->total_amount, $i->currency?->code, $i->exchange_rate));

                $apPayPrior = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'MADE')
                    ->where('payment_date', '<', $periodFrom)
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->get()
                    ->sum(fn($p) => $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate));

                return $apPrior - $apPayPrior;

            case '3000': // Owner's Capital / Opening Equity (Equity: Credit normal)
                $banks = DB::table('banks')
                    ->where(function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhere('company_id', 1);
                    })
                    ->where('is_active', 1)
                    ->get();
                return (float) $banks->sum(fn($b) => $this->toUSD((float) $b->initial_amount, $b->currency));

            case '4000': // Revenue (Nominal account)
            case '5000': // Expenses (Nominal account)
                return 0.0;

            default:
                // Check if this is a custom GL account from journal entries
                $gl = DB::table('gl_codes')->where('gl_code', $glCode)->first();
                if ($gl) {
                    $priorLines = DB::table('journal_entry_lines')
                        ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
                        ->where('journal_entry_lines.gl_account_id', $gl->id)
                        ->whereNull('journal_entries.deleted_at')
                        ->where('journal_entries.status', '!=', 'VOIDED')
                        ->where('journal_entries.entry_date', '<', $periodFrom)
                        ->when($officeId, fn($q) => $q->where('journal_entries.office_id', $officeId))
                        ->get();
                    $dr = (float) $priorLines->sum('local_debit');
                    $cr = (float) $priorLines->sum('local_credit');
                    return $dr - $cr;
                }
                return 0.0;
        }
    }

    /**
     * Get transactions for an account within the period and compute running balance.
     */
    private function getTransactions(
        string $glCode,
        string $periodFrom,
        string $periodTo,
        ?int $officeId,
        int $companyId,
        string $reportType,
        float $openingBalance,
        string $accountType
    ): array {
        $transactions = [];

        switch ($glCode) {
            case '1000': // Cash - show receipts (Debit) and payments (Credit)
                $receipts = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'RECEIVED')
                    ->whereBetween('payment_date', [$periodFrom, $periodTo])
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner', 'invoice', 'currency'])
                    ->get();

                foreach ($receipts as $p) {
                    $amt = $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate);
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Cash Receipt from ' . ($p->tradePartner?->name ?? 'N/A') . ($p->bank_name ? " [{$p->bank_name}]" : ''),
                        'debit'       => $amt,
                        'credit'      => 0.0,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => $p->invoice?->invoice_no ?? '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }

                $payments = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'MADE')
                    ->whereBetween('payment_date', [$periodFrom, $periodTo])
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner', 'invoice', 'currency'])
                    ->get();

                foreach ($payments as $p) {
                    $amt = $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate);
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Cash Disbursement to ' . ($p->tradePartner?->name ?? 'N/A') . ($p->bank_name ? " [{$p->bank_name}]" : ''),
                        'debit'       => 0.0,
                        'credit'      => $amt,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => $p->invoice?->invoice_no ?? '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }
                break;

            case '1200': // AR - show AR invoices (Debit) and payments received (Credit)
                $invoices = Invoice::where('company_id', $companyId)
                    ->where('type', 'AR')
                    ->whereBetween('invoice_date', [$periodFrom, $periodTo])
                    ->where('status', '!=', 'VOID')
                    ->where('verification_status', '!=', 'Cancelled')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo', 'currency'])
                    ->get();

                foreach ($invoices as $inv) {
                    $isCN = strtoupper($inv->document_type ?? '') === 'CN';
                    $amt = $this->toUSD((float) $inv->total_amount, $inv->currency?->code, $inv->exchange_rate);
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => ($isCN ? 'Credit Note - ' : 'Invoice Billed - ') . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => $isCN ? 0.0 : $amt,
                        'credit'      => $isCN ? $amt : 0.0,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }

                $arPayments = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'RECEIVED')
                    ->whereBetween('payment_date', [$periodFrom, $periodTo])
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner', 'currency'])
                    ->get();

                foreach ($arPayments as $p) {
                    $amt = $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate);
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Customer Settlement - ' . ($p->tradePartner?->name ?? 'N/A'),
                        'debit'       => 0.0,
                        'credit'      => $amt,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }
                break;

            case '2000': // AP - show AP invoices (Credit) and payments made (Debit)
                $invoices = Invoice::where('company_id', $companyId)
                    ->where('type', 'AP')
                    ->whereBetween('invoice_date', [$periodFrom, $periodTo])
                    ->where('status', '!=', 'VOID')
                    ->where('verification_status', '!=', 'Cancelled')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo', 'currency'])
                    ->get();

                foreach ($invoices as $inv) {
                    $isDN = strtoupper($inv->document_type ?? '') === 'DN';
                    $amt = $this->toUSD((float) $inv->total_amount, $inv->currency?->code, $inv->exchange_rate);
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => ($isDN ? 'Debit Note - ' : 'Payable Billed - ') . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => $isDN ? $amt : 0.0,
                        'credit'      => $isDN ? 0.0 : $amt,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }

                $apPayments = AccountingPayment::where('company_id', $companyId)
                    ->where('type', 'MADE')
                    ->whereBetween('payment_date', [$periodFrom, $periodTo])
                    ->whereNull('void_date')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['tradePartner', 'currency'])
                    ->get();

                foreach ($apPayments as $p) {
                    $amt = $this->toUSD((float) $p->amount, $p->currency?->code, $p->exchange_rate);
                    $transactions[] = [
                        'date'        => $p->payment_date?->format('Y-m-d') ?? '',
                        'reference'   => $p->payment_no ?? '',
                        'description' => 'Vendor Disbursement - ' . ($p->tradePartner?->name ?? 'N/A'),
                        'debit'       => $amt,
                        'credit'      => 0.0,
                        'partner'     => $p->tradePartner?->name ?? '',
                        'invoice_no'  => '',
                        'method'      => $p->payment_method ?? '',
                    ];
                }
                break;

            case '4000': // Revenue - show AR invoices (Credit) & CN (Debit)
                $invoices = Invoice::where('company_id', $companyId)
                    ->where('type', 'AR')
                    ->whereBetween('invoice_date', [$periodFrom, $periodTo])
                    ->where('status', '!=', 'VOID')
                    ->where('verification_status', '!=', 'Cancelled')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo', 'currency'])
                    ->get();

                foreach ($invoices as $inv) {
                    $isCN = strtoupper($inv->document_type ?? '') === 'CN';
                    $amt = $this->toUSD((float) $inv->total_amount, $inv->currency?->code, $inv->exchange_rate);
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => ($isCN ? 'Credit Note (Contra-Revenue) - ' : 'Operating Revenue - ') . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => $isCN ? $amt : 0.0,
                        'credit'      => $isCN ? 0.0 : $amt,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }
                break;

            case '5000': // Expenses - show AP invoices (Debit) & DN (Credit)
                $invoices = Invoice::where('company_id', $companyId)
                    ->where('type', 'AP')
                    ->whereBetween('invoice_date', [$periodFrom, $periodTo])
                    ->where('status', '!=', 'VOID')
                    ->where('verification_status', '!=', 'Cancelled')
                    ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                    ->with(['billTo', 'currency'])
                    ->get();

                foreach ($invoices as $inv) {
                    $isDN = strtoupper($inv->document_type ?? '') === 'DN';
                    $amt = $this->toUSD((float) $inv->total_amount, $inv->currency?->code, $inv->exchange_rate);
                    $transactions[] = [
                        'date'        => $inv->invoice_date?->format('Y-m-d') ?? '',
                        'reference'   => $inv->invoice_no ?? '',
                        'description' => ($isDN ? 'Debit Note (Expense Recovery) - ' : 'Operating Expense - ') . ($inv->billTo?->name ?? 'N/A'),
                        'debit'       => $isDN ? 0.0 : $amt,
                        'credit'      => $isDN ? $amt : 0.0,
                        'partner'     => $inv->billTo?->name ?? '',
                        'invoice_no'  => $inv->invoice_no ?? '',
                        'method'      => '',
                    ];
                }
                break;
        }

        // Include any manual Journal Entries affecting this GL account
        $targetGl = DB::table('gl_codes')->where('gl_code', $glCode)->first();
        if ($targetGl) {
            $jeLines = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
                ->leftJoin('trade_partners', 'trade_partners.id', '=', 'journal_entry_lines.trade_partner_id')
                ->where('journal_entry_lines.gl_account_id', $targetGl->id)
                ->whereNull('journal_entries.deleted_at')
                ->where('journal_entries.status', '!=', 'VOIDED')
                ->whereBetween('journal_entries.entry_date', [$periodFrom, $periodTo])
                ->when($officeId, fn($q) => $q->where('journal_entries.office_id', $officeId))
                ->select(
                    'journal_entry_lines.*',
                    'journal_entries.entry_no',
                    'journal_entries.entry_date',
                    'journal_entries.description as je_desc',
                    'trade_partners.name as partner_name'
                )
                ->get();

            foreach ($jeLines as $line) {
                $dr = (float) ($line->local_debit ?? 0);
                $cr = (float) ($line->local_credit ?? 0);
                if ($dr <= 0.001 && $cr <= 0.001) continue;

                $transactions[] = [
                    'date'        => $line->entry_date ? date('Y-m-d', strtotime($line->entry_date)) : '',
                    'reference'   => $line->entry_no ?? '',
                    'description' => 'Journal: ' . ($line->description ?: ($line->je_desc ?: 'Journal Adjustment')),
                    'debit'       => $dr,
                    'credit'      => $cr,
                    'partner'     => $line->partner_name ?? '',
                    'invoice_no'  => '',
                    'method'      => 'JOURNAL',
                ];
            }
        }

        // Sort chronologically by date
        usort($transactions, fn($a, $b) => strcmp($a['date'], $b['date']));

        // Calculate accurate Running Balance starting from Opening Balance
        $running = $openingBalance;
        foreach ($transactions as &$txn) {
            if ($accountType === 'credit') {
                $running += ($txn['credit'] - $txn['debit']);
            } else {
                $running += ($txn['debit'] - $txn['credit']);
            }
            $txn['running_balance'] = round($running, 2);
        }
        unset($txn);

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
            fputcsv($handle, ['Period', (request()->period_from ?? date('Y-01-01')) . ' to ' . (request()->period_to ?? date('Y-m-d'))]);
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
