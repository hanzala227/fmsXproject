<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\AccountingJournal;
use App\Models\Currency;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrialBalanceController extends Controller
{
    /**
     * Show the Trial Balance report page with filter inputs.
     */
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('accounting.trial-balance', compact('offices'));
    }

    /**
     * Generate the Trial Balance report data via AJAX.
     *
     * A Trial Balance lists all accounts with their debit and credit balances.
     * Total Debits must equal Total Credits.
     */
    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period_from' => 'nullable|date',
            'period_to'   => 'nullable|date|after_or_equal:period_from',
            'office_id'   => 'nullable|exists:offices,id',
            'format'      => 'nullable|in:standard,debit_credit,currency_detail',
            'group_by_sub' => 'nullable|boolean',
            'hide_zero'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $periodFrom = $request->input('period_from', date('Y-01-01'));
        $periodTo   = $request->input('period_to', date('Y-m-d'));
        $officeId   = $request->input('office_id');
        $format     = $request->input('format', 'standard');
        $groupBySub = filter_var($request->input('group_by_sub', true), FILTER_VALIDATE_BOOLEAN);
        $hideZero   = filter_var($request->input('hide_zero', false), FILTER_VALIDATE_BOOLEAN);
        $companyId  = auth()->user()->company_id ?? 1;

        $accounts = [];

        // Helper: Convert foreign amounts to base USD using historical rate or currency rates
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
            return ($rate != 1.0) ? round(((float) $amount) / $rate, 2) : (float) $amount;
        };

        // ── 1. Cash & Cash Equivalents (Banks from Settings + Undeposited Cash) ──
        $banks = DB::table('banks')
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhere('company_id', 1);
            })
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        $totalInitialBankUSD = 0.0;
        $trackedBankNames = [];

        foreach ($banks as $bank) {
            $currCode = $bank->currency ?: 'USD';
            $initialUSD = $toUSD($bank->initial_amount, $currCode);
            $totalInitialBankUSD += $initialUSD;
            $trackedBankNames[] = trim($bank->bank_name);
            if (!empty($bank->gl_no)) {
                $trackedBankNames[] = trim($bank->gl_no);
            }

            // Prior Receipts & Payments before period_from
            $priorBankRec = AccountingPayment::where('company_id', $companyId)
                ->where(function ($q) use ($bank) {
                    $q->where('bank_name', trim($bank->bank_name));
                    if (!empty($bank->gl_no)) $q->orWhere('bank_name', trim($bank->gl_no));
                })
                ->where('type', 'RECEIVED')
                ->where('payment_date', '<', $periodFrom)
                ->whereNull('void_date')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get()
                ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

            $priorBankPay = AccountingPayment::where('company_id', $companyId)
                ->where(function ($q) use ($bank) {
                    $q->where('bank_name', trim($bank->bank_name));
                    if (!empty($bank->gl_no)) $q->orWhere('bank_name', trim($bank->gl_no));
                })
                ->where('type', 'MADE')
                ->where('payment_date', '<', $periodFrom)
                ->whereNull('void_date')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get()
                ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

            // Period Receipts & Payments
            $periodBankRec = AccountingPayment::where('company_id', $companyId)
                ->where(function ($q) use ($bank) {
                    $q->where('bank_name', trim($bank->bank_name));
                    if (!empty($bank->gl_no)) $q->orWhere('bank_name', trim($bank->gl_no));
                })
                ->where('type', 'RECEIVED')
                ->whereBetween('payment_date', [$periodFrom, $periodTo])
                ->whereNull('void_date')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get()
                ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

            $periodBankPay = AccountingPayment::where('company_id', $companyId)
                ->where(function ($q) use ($bank) {
                    $q->where('bank_name', trim($bank->bank_name));
                    if (!empty($bank->gl_no)) $q->orWhere('bank_name', trim($bank->gl_no));
                })
                ->where('type', 'MADE')
                ->whereBetween('payment_date', [$periodFrom, $periodTo])
                ->whereNull('void_date')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get()
                ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

            $opening = $initialUSD + $priorBankRec - $priorBankPay;
            $closing = $opening + $periodBankRec - $periodBankPay;

            $glCode = '1000';
            if (!empty($bank->gl_no)) {
                $codeExtract = preg_replace('/[^0-9]/', '', substr($bank->gl_no, 0, 5));
                if ($codeExtract) $glCode = $codeExtract;
            }

            if (!$hideZero || abs($opening) > 0.001 || abs($periodBankRec) > 0.001 || abs($periodBankPay) > 0.001 || abs($closing) > 0.001) {
                $accounts[] = [
                    'code'            => $glCode,
                    'sub_code'        => $bank->bank_name,
                    'name'            => $bank->bank_name . ($currCode !== 'USD' ? " ($currCode)" : ''),
                    'group'           => 'Assets',
                    'sub_group'       => 'Cash & Cash Equivalents',
                    'currency'        => $currCode,
                    'opening_balance' => $opening,
                    'debit'           => $periodBankRec,
                    'credit'          => $periodBankPay,
                    'closing_balance' => $closing,
                    'type'            => 'debit',
                ];
            }
        }

        // Unassigned Cash (Payments not specifically linked to a Settings bank)
        $unassignedPriorRec = AccountingPayment::where('company_id', $companyId)
            ->where(fn($q) => $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'RECEIVED')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $unassignedPriorPay = AccountingPayment::where('company_id', $companyId)
            ->where(fn($q) => $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'MADE')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $unassignedPeriodRec = AccountingPayment::where('company_id', $companyId)
            ->where(fn($q) => $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'RECEIVED')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $unassignedPeriodPay = AccountingPayment::where('company_id', $companyId)
            ->where(fn($q) => $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'MADE')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $unassignedOpening = $unassignedPriorRec - $unassignedPriorPay;
        $unassignedClosing = $unassignedOpening + $unassignedPeriodRec - $unassignedPeriodPay;

        if (!$hideZero || abs($unassignedOpening) > 0.001 || abs($unassignedPeriodRec) > 0.001 || abs($unassignedPeriodPay) > 0.001 || abs($unassignedClosing) > 0.001) {
            $accounts[] = [
                'code'            => '1010',
                'sub_code'        => 'UNDEPOSITED_FUNDS',
                'name'            => 'Operating Cash / Undeposited Funds',
                'group'           => 'Assets',
                'sub_group'       => 'Cash & Cash Equivalents',
                'currency'        => 'USD',
                'opening_balance' => $unassignedOpening,
                'debit'           => $unassignedPeriodRec,
                'credit'          => $unassignedPeriodPay,
                'closing_balance' => $unassignedClosing,
                'type'            => 'debit',
            ];
        }

        // Total payments summary for AR and AP reconciliations
        $totalPriorRec = AccountingPayment::where('company_id', $companyId)
            ->where('type', 'RECEIVED')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $totalPriorPay = AccountingPayment::where('company_id', $companyId)
            ->where('type', 'MADE')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $totalPeriodRec = AccountingPayment::where('company_id', $companyId)
            ->where('type', 'RECEIVED')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $totalPeriodPay = AccountingPayment::where('company_id', $companyId)
            ->where('type', 'MADE')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        // ── 2. Accounts Receivable (Code 1200) ──
        $priorAR = Invoice::where('company_id', $companyId)
            ->where('type', 'AR')
            ->where('invoice_date', '<', $periodFrom)
            ->where('status', '!=', 'VOID')
            ->where('verification_status', '!=', 'Cancelled')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($i) => ($i->document_type === 'CN' ? -1 : 1) * $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));

        $periodAR = Invoice::where('company_id', $companyId)
            ->where('type', 'AR')
            ->whereBetween('invoice_date', [$periodFrom, $periodTo])
            ->where('status', '!=', 'VOID')
            ->where('verification_status', '!=', 'Cancelled')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($i) => ($i->document_type === 'CN' ? -1 : 1) * $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));

        $arOpening = $priorAR - $totalPriorRec;
        $arClosing = $arOpening + $periodAR - $totalPeriodRec;

        if (!$hideZero || abs($arOpening) > 0.001 || abs($periodAR) > 0.001 || abs($totalPeriodRec) > 0.001 || abs($arClosing) > 0.001) {
            $accounts[] = [
                'code'            => '1200',
                'sub_code'        => 'AR',
                'name'            => 'Accounts Receivable',
                'group'           => 'Assets',
                'sub_group'       => 'Current Assets',
                'currency'        => 'USD',
                'opening_balance' => $arOpening,
                'debit'           => $periodAR,
                'credit'          => $totalPeriodRec,
                'closing_balance' => $arClosing,
                'type'            => 'debit',
            ];
        }

        // ── 3. Accounts Payable (Code 2000) ──
        $priorAP = Invoice::where('company_id', $companyId)
            ->where('type', 'AP')
            ->where('invoice_date', '<', $periodFrom)
            ->where('status', '!=', 'VOID')
            ->where('verification_status', '!=', 'Cancelled')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($i) => ($i->document_type === 'DN' ? -1 : 1) * $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));

        $periodAP = Invoice::where('company_id', $companyId)
            ->where('type', 'AP')
            ->whereBetween('invoice_date', [$periodFrom, $periodTo])
            ->where('status', '!=', 'VOID')
            ->where('verification_status', '!=', 'Cancelled')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($i) => ($i->document_type === 'DN' ? -1 : 1) * $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));

        $apOpening = $priorAP - $totalPriorPay;
        $apClosing = $apOpening + $periodAP - $totalPeriodPay;

        if (!$hideZero || abs($apOpening) > 0.001 || abs($periodAP) > 0.001 || abs($totalPeriodPay) > 0.001 || abs($apClosing) > 0.001) {
            $accounts[] = [
                'code'            => '2000',
                'sub_code'        => 'AP',
                'name'            => 'Accounts Payable',
                'group'           => 'Liabilities',
                'sub_group'       => 'Current Liabilities',
                'currency'        => 'USD',
                'opening_balance' => $apOpening,
                'debit'           => $totalPeriodPay,
                'credit'          => $periodAP,
                'closing_balance' => $apClosing,
                'type'            => 'credit',
            ];
        }

        // ── 4. Equity: Owner's Capital & Retained Earnings ──
        if (!$hideZero || abs($totalInitialBankUSD) > 0.001) {
            $accounts[] = [
                'code'            => '3000',
                'sub_code'        => 'CAPITAL',
                'name'            => "Owner's Capital / Opening Equity",
                'group'           => 'Equity',
                'sub_group'       => 'Owner Equity',
                'currency'        => 'USD',
                'opening_balance' => $totalInitialBankUSD,
                'debit'           => 0.0,
                'credit'          => 0.0,
                'closing_balance' => $totalInitialBankUSD,
                'type'            => 'credit',
            ];
        }

        $priorNetIncome = $priorAR - $priorAP;
        if (abs($priorNetIncome) > 0.001) {
            $accounts[] = [
                'code'            => '3200',
                'sub_code'        => 'RETAINED_EARNINGS',
                'name'            => 'Retained Earnings (Prior Periods)',
                'group'           => 'Equity',
                'sub_group'       => 'Owner Equity',
                'currency'        => 'USD',
                'opening_balance' => $priorNetIncome,
                'debit'           => 0.0,
                'credit'          => 0.0,
                'closing_balance' => $priorNetIncome,
                'type'            => 'credit',
            ];
        }

        // ── 5. Revenue (Operating Revenue, Code 4000) ──
        if (!$hideZero || abs($periodAR) > 0.001) {
            $accounts[] = [
                'code'            => '4000',
                'sub_code'        => 'REV',
                'name'            => 'Freight & Operating Revenue',
                'group'           => 'Revenue',
                'sub_group'       => 'Operating Revenue',
                'currency'        => 'USD',
                'opening_balance' => 0.0,
                'debit'           => 0.0,
                'credit'          => $periodAR,
                'closing_balance' => $periodAR,
                'type'            => 'credit',
            ];
        }

        // ── 6. Operating Expenses (Expenses, Code 5000) ──
        if (!$hideZero || abs($periodAP) > 0.001) {
            $accounts[] = [
                'code'            => '5000',
                'sub_code'        => 'EXP',
                'name'            => 'Cost of Freight & Operating Expenses',
                'group'           => 'Expenses',
                'sub_group'       => 'Operating Expenses',
                'currency'        => 'USD',
                'opening_balance' => 0.0,
                'debit'           => $periodAP,
                'credit'          => 0.0,
                'closing_balance' => $periodAP,
                'type'            => 'debit',
            ];
        }

        // ── 7. Manual Journal Entries (from AccountingJournal / journal_entries) ──
        $journalLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->leftJoin('gl_codes', 'gl_codes.id', '=', 'journal_entry_lines.gl_account_id')
            ->whereNull('journal_entries.deleted_at')
            ->where('journal_entries.status', '!=', 'VOIDED')
            ->whereBetween('journal_entries.entry_date', [$periodFrom, $periodTo])
            ->when($officeId, fn($q) => $q->where('journal_entries.office_id', $officeId))
            ->select(
                'journal_entry_lines.*',
                'gl_codes.gl_code as gl_code',
                'gl_codes.gl_name_eng as gl_name'
            )
            ->get();

        foreach ($journalLines as $line) {
            $lDebit = (float) ($line->local_debit ?? 0);
            $lCredit = (float) ($line->local_credit ?? 0);
            if ($lDebit <= 0.001 && $lCredit <= 0.001) continue;

            $accounts[] = [
                'code'            => $line->gl_code ?: '6000',
                'sub_code'        => 'JE',
                'name'            => $line->gl_name ?: ($line->description ?: 'Journal Adjustment'),
                'group'           => ($lDebit > $lCredit) ? 'Expenses' : 'Revenue',
                'sub_group'       => 'Journal Adjustments',
                'currency'        => 'USD',
                'opening_balance' => 0.0,
                'debit'           => $lDebit,
                'credit'          => $lCredit,
                'closing_balance' => ($lDebit >= $lCredit) ? ($lDebit - $lCredit) : ($lCredit - $lDebit),
                'type'            => ($lDebit >= $lCredit) ? 'debit' : 'credit',
            ];
        }

        // Enrich accounts with Debit/Credit balance formatting fields
        foreach ($accounts as &$acc) {
            $isDeb = ($acc['type'] === 'debit');
            $op = (float) $acc['opening_balance'];
            $cl = (float) $acc['closing_balance'];

            $acc['opening_debit']  = $isDeb ? max(0.0, $op) : max(0.0, -$op);
            $acc['opening_credit'] = $isDeb ? max(0.0, -$op) : max(0.0, $op);
            $acc['closing_debit']  = $isDeb ? max(0.0, $cl) : max(0.0, -$cl);
            $acc['closing_credit'] = $isDeb ? max(0.0, -$cl) : max(0.0, $cl);
        }
        unset($acc);

        // ── Group accounts if requested ──
        $groupedAccounts = [];
        if ($groupBySub) {
            $grouped = [];
            foreach ($accounts as $acc) {
                $key = $acc['group'] . '|' . $acc['sub_group'];
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'group'          => $acc['group'],
                        'sub_group'      => $acc['sub_group'],
                        'accounts'       => [],
                        'opening_balance'=> 0,
                        'opening_debit'  => 0,
                        'opening_credit' => 0,
                        'debit'          => 0,
                        'credit'         => 0,
                        'closing_balance'=> 0,
                        'closing_debit'  => 0,
                        'closing_credit' => 0,
                    ];
                }
                $grouped[$key]['accounts'][]       = $acc;
                $grouped[$key]['opening_balance'] += $acc['opening_balance'];
                $grouped[$key]['opening_debit']   += $acc['opening_debit'];
                $grouped[$key]['opening_credit']  += $acc['opening_credit'];
                $grouped[$key]['debit']           += $acc['debit'];
                $grouped[$key]['credit']          += $acc['credit'];
                $grouped[$key]['closing_balance'] += $acc['closing_balance'];
                $grouped[$key]['closing_debit']   += $acc['closing_debit'];
                $grouped[$key]['closing_credit']  += $acc['closing_credit'];
            }
            $groupedAccounts = array_values($grouped);
        }

        // ── Calculate Totals ──
        $totalDebit          = array_sum(array_column($accounts, 'debit'));
        $totalCredit         = array_sum(array_column($accounts, 'credit'));
        $totalOpeningDebit   = array_sum(array_column($accounts, 'opening_debit'));
        $totalOpeningCredit  = array_sum(array_column($accounts, 'opening_credit'));
        $totalClosingDebit   = array_sum(array_column($accounts, 'closing_debit'));
        $totalClosingCredit  = array_sum(array_column($accounts, 'closing_credit'));
        $totalOpeningBalance = array_sum(array_column($accounts, 'opening_balance'));
        $totalClosingBalance = array_sum(array_column($accounts, 'closing_balance'));

        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;

        return response()->json([
            'success'      => true,
            'period_from'  => $periodFrom,
            'period_to'    => $periodTo,
            'office_id'    => $officeId,
            'format'       => $format,
            'group_by_sub' => $groupBySub,
            'accounts'     => $accounts,
            'grouped'      => $groupedAccounts,
            'summary'      => [
                'total_debit'           => round($totalDebit, 2),
                'total_credit'          => round($totalCredit, 2),
                'total_opening_debit'   => round($totalOpeningDebit, 2),
                'total_opening_credit'  => round($totalOpeningCredit, 2),
                'total_closing_debit'   => round($totalClosingDebit, 2),
                'total_closing_credit'  => round($totalClosingCredit, 2),
                'total_opening_balance' => round($totalOpeningBalance, 2),
                'total_closing_balance' => round($totalClosingBalance, 2),
                'is_balanced'           => $isBalanced,
                'account_count'         => count($accounts),
            ],
        ]);
    }

    /**
     * Render a print-friendly version of the report.
     */
    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.trial-balance-print', [
            'accounts'     => $data['accounts'] ?? [],
            'grouped'      => $data['grouped'] ?? [],
            'summary'      => $data['summary'] ?? [],
            'periodFrom'   => $request->period_from ?? date('Y-01-01'),
            'periodTo'     => $request->period_to ?? date('Y-m-d'),
            'format'       => $request->format ?? 'standard',
            'groupBySub'   => $request->group_by_sub ?? true,
        ]);
    }

    /**
     * Export report data to CSV.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->view($request)->getData(true);
        $accounts = $data['accounts'] ?? [];
        $grouped  = $data['grouped'] ?? [];
        $summary  = $data['summary'] ?? [];
        $groupBySub = $data['group_by_sub'] ?? true;
        $format     = $data['format'] ?? 'standard';

        $filename = 'trial-balance-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($accounts, $grouped, $summary, $groupBySub, $format) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['Trial Balance Report']);
            fputcsv($handle, ['Period', (request()->period_from ?? date('Y-01-01')) . ' to ' . (request()->period_to ?? date('Y-m-d'))]);
            fputcsv($handle, []);

            // Header
            if ($format === 'debit_credit') {
                fputcsv($handle, ['Code', 'Account', 'Group', 'Opening (Dr)', 'Opening (Cr)', 'Debit', 'Credit', 'Closing (Dr)', 'Closing (Cr)']);
            } else {
                fputcsv($handle, ['Code', 'Account', 'Group', 'Opening Balance', 'Debit', 'Credit', 'Closing Balance']);
            }

            if ($groupBySub && !empty($grouped)) {
                foreach ($grouped as $grp) {
                    fputcsv($handle, []);
                    fputcsv($handle, ['-- ' . strtoupper($grp['group']) . ' / ' . $grp['sub_group'] . ' --']);

                    foreach ($grp['accounts'] as $acc) {
                        if ($format === 'debit_credit') {
                            fputcsv($handle, [
                                $acc['code'],
                                $acc['name'],
                                $acc['group'],
                                number_format($acc['opening_debit'], 2),
                                number_format($acc['opening_credit'], 2),
                                number_format($acc['debit'], 2),
                                number_format($acc['credit'], 2),
                                number_format($acc['closing_debit'], 2),
                                number_format($acc['closing_credit'], 2),
                            ]);
                        } else {
                            fputcsv($handle, [
                                $acc['code'],
                                $acc['name'],
                                $acc['group'],
                                number_format($acc['opening_balance'], 2),
                                number_format($acc['debit'], 2),
                                number_format($acc['credit'], 2),
                                number_format($acc['closing_balance'], 2),
                            ]);
                        }
                    }

                    if ($format === 'debit_credit') {
                        fputcsv($handle, [
                            '',
                            'Subtotal',
                            '',
                            number_format($grp['opening_debit'], 2),
                            number_format($grp['opening_credit'], 2),
                            number_format($grp['debit'], 2),
                            number_format($grp['credit'], 2),
                            number_format($grp['closing_debit'], 2),
                            number_format($grp['closing_credit'], 2),
                        ]);
                    } else {
                        fputcsv($handle, [
                            '',
                            'Subtotal',
                            '',
                            number_format($grp['opening_balance'], 2),
                            number_format($grp['debit'], 2),
                            number_format($grp['credit'], 2),
                            number_format($grp['closing_balance'], 2),
                        ]);
                    }
                }
            } else {
                foreach ($accounts as $acc) {
                    if ($format === 'debit_credit') {
                        fputcsv($handle, [
                            $acc['code'],
                            $acc['name'],
                            $acc['group'],
                            number_format($acc['opening_debit'], 2),
                            number_format($acc['opening_credit'], 2),
                            number_format($acc['debit'], 2),
                            number_format($acc['credit'], 2),
                            number_format($acc['closing_debit'], 2),
                            number_format($acc['closing_credit'], 2),
                        ]);
                    } else {
                        fputcsv($handle, [
                            $acc['code'],
                            $acc['name'],
                            $acc['group'],
                            number_format($acc['opening_balance'], 2),
                            number_format($acc['debit'], 2),
                            number_format($acc['credit'], 2),
                            number_format($acc['closing_balance'], 2),
                        ]);
                    }
                }
            }

            // Grand Total
            fputcsv($handle, []);
            if ($format === 'debit_credit') {
                fputcsv($handle, [
                    '',
                    'GRAND TOTAL',
                    '',
                    number_format($summary['total_opening_debit'] ?? 0, 2),
                    number_format($summary['total_opening_credit'] ?? 0, 2),
                    number_format($summary['total_debit'] ?? 0, 2),
                    number_format($summary['total_credit'] ?? 0, 2),
                    number_format($summary['total_closing_debit'] ?? 0, 2),
                    number_format($summary['total_closing_credit'] ?? 0, 2),
                ]);
            } else {
                fputcsv($handle, [
                    '',
                    'GRAND TOTAL',
                    '',
                    number_format($summary['total_opening_balance'] ?? 0, 2),
                    number_format($summary['total_debit'] ?? 0, 2),
                    number_format($summary['total_credit'] ?? 0, 2),
                    number_format($summary['total_closing_balance'] ?? 0, 2),
                ]);
            }
            fputcsv($handle, ['', 'Balanced', '', '', '', '', ($summary['is_balanced'] ?? false) ? 'Yes' : 'No']);

            fclose($handle);
        }, 200, $headers);
    }
}
