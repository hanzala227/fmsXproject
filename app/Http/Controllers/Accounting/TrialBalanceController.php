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
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        $glNoCounts = $banks->pluck('gl_no')->filter()->map(fn($g) => trim($g))->countBy();
        $totalInitialBankUSD = 0.0;
        $trackedBankNames = [];

        foreach ($banks as $bank) {
            $currCode = $bank->currency ?: 'USD';
            $initialUSD = $toUSD($bank->initial_amount, $currCode);
            $totalInitialBankUSD += $initialUSD;
            $bName = trim($bank->bank_name);
            $trackedBankNames[] = $bName;
            $uniqueGlNo = (!empty($bank->gl_no) && ($glNoCounts[trim($bank->gl_no)] ?? 0) === 1) ? trim($bank->gl_no) : null;
            if ($uniqueGlNo) {
                $trackedBankNames[] = $uniqueGlNo;
            }

            $bankFilter = function ($q) use ($bName, $uniqueGlNo) {
                $q->where('bank_name', $bName);
                if ($uniqueGlNo) {
                    $q->orWhere('bank_name', $uniqueGlNo);
                }
            };

            // Prior Receipts & Payments before period_from
            $priorBankRec = AccountingPayment::with('currency')
                ->where('company_id', $companyId)
                ->where($bankFilter)
                ->where('type', 'RECEIVED')
                ->where('payment_date', '<', $periodFrom)
                ->whereNull('void_date')
                ->whereNull('cancelled_at')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get()
                ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

            $priorBankPay = AccountingPayment::with('currency')
                ->where('company_id', $companyId)
                ->where($bankFilter)
                ->where('type', 'MADE')
                ->where('payment_date', '<', $periodFrom)
                ->whereNull('void_date')
                ->whereNull('cancelled_at')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get()
                ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

            // Period Receipts & Payments
            $periodBankRec = AccountingPayment::with('currency')
                ->where('company_id', $companyId)
                ->where($bankFilter)
                ->where('type', 'RECEIVED')
                ->whereBetween('payment_date', [$periodFrom, $periodTo])
                ->whereNull('void_date')
                ->whereNull('cancelled_at')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get()
                ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

            $periodBankPay = AccountingPayment::with('currency')
                ->where('company_id', $companyId)
                ->where($bankFilter)
                ->where('type', 'MADE')
                ->whereBetween('payment_date', [$periodFrom, $periodTo])
                ->whereNull('void_date')
                ->whereNull('cancelled_at')
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
        $trackedBankNames = array_values(array_unique($trackedBankNames));
        $unassignedPriorRec = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where(fn($q) => empty($trackedBankNames) ? $q : $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'RECEIVED')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $unassignedPriorPay = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where(fn($q) => empty($trackedBankNames) ? $q : $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'MADE')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $unassignedPeriodRec = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where(fn($q) => empty($trackedBankNames) ? $q : $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'RECEIVED')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($p) => $toUSD($p->amount, $p->currency?->code, $p->exchange_rate));

        $unassignedPeriodPay = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where(fn($q) => empty($trackedBankNames) ? $q : $q->whereNull('bank_name')->orWhereNotIn('bank_name', $trackedBankNames))
            ->where('type', 'MADE')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
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

        // Total payment Cash, TDS, and Discount breakdown for AR & AP reconciliations
        $sumPayParts = function ($collection) use ($toUSD) {
            $cash = 0.0; $tds = 0.0; $disc = 0.0;
            foreach ($collection as $p) {
                $curr = $p->currency?->code;
                $cUsd = $toUSD((float) $p->amount, $curr, $p->exchange_rate);
                $tUsd = $toUSD((float) ($p->tds_tax ?? 0), $curr, $p->exchange_rate);
                $dUsd = $toUSD((float) ($p->discount ?? 0), $curr, $p->exchange_rate);
                $cash += $cUsd;
                $tds  += $tUsd;
                $disc += $dUsd;
            }
            return ['cash' => $cash, 'tds' => $tds, 'disc' => $disc, 'settled' => $cash + $tds + $disc];
        };

        $priorRecParts = $sumPayParts(AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where('type', 'RECEIVED')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get());

        $priorPayParts = $sumPayParts(AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where('type', 'MADE')
            ->where('payment_date', '<', $periodFrom)
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get());

        $periodRecParts = $sumPayParts(AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where('type', 'RECEIVED')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get());

        $periodPayParts = $sumPayParts(AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where('type', 'MADE')
            ->whereBetween('payment_date', [$periodFrom, $periodTo])
            ->whereNull('void_date')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get());

        // ── 2. Accounts Receivable (Code 1200) & TDS Receivable (Code 1300) ──
        $priorAR = Invoice::reportable()
            ->where('company_id', $companyId)
            ->where('type', 'AR')
            ->where('invoice_date', '<', $periodFrom)
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($i) => ($i->document_type === 'CN' ? -1 : 1) * $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));

        $periodAR_Invoices = Invoice::reportable()
            ->where('company_id', $companyId)
            ->where('type', 'AR')
            ->whereBetween('invoice_date', [$periodFrom, $periodTo])
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        $periodAR_Gross = $periodAR_Invoices->where('document_type', '!=', 'CN')->sum(fn($i) => $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));
        $periodAR_CN    = $periodAR_Invoices->where('document_type', '=', 'CN')->sum(fn($i) => $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));
        $periodAR_Net   = $periodAR_Gross - $periodAR_CN;

        $arOpening = $priorAR - $priorRecParts['settled'];
        $arClosing = $arOpening + $periodAR_Net - $periodRecParts['settled'];

        if (!$hideZero || abs($arOpening) > 0.001 || abs($periodAR_Gross) > 0.001 || abs($periodRecParts['settled']) > 0.001 || abs($arClosing) > 0.001) {
            $accounts[] = [
                'code'            => '1200',
                'sub_code'        => 'AR',
                'name'            => 'Accounts Receivable',
                'group'           => 'Assets',
                'sub_group'       => 'Current Assets',
                'currency'        => 'USD',
                'opening_balance' => $arOpening,
                'debit'           => $periodAR_Gross,
                'credit'          => $periodRecParts['settled'] + $periodAR_CN,
                'closing_balance' => $arClosing,
                'type'            => 'debit',
            ];
        }

        $tdsRecOpen = $priorRecParts['tds'];
        $tdsRecClose = $tdsRecOpen + $periodRecParts['tds'];
        if (abs($tdsRecOpen) > 0.001 || abs($periodRecParts['tds']) > 0.001 || abs($tdsRecClose) > 0.001) {
            $accounts[] = [
                'code'            => '1300',
                'sub_code'        => 'TDS_REC',
                'name'            => 'Withholding Tax / TDS Receivable',
                'group'           => 'Assets',
                'sub_group'       => 'Current Assets',
                'currency'        => 'USD',
                'opening_balance' => $tdsRecOpen,
                'debit'           => $periodRecParts['tds'],
                'credit'          => 0.0,
                'closing_balance' => $tdsRecClose,
                'type'            => 'debit',
            ];
        }

        // ── 3. Accounts Payable (Code 2000) & TDS Payable (Code 2100) ──
        $priorAP = Invoice::reportable()
            ->where('company_id', $companyId)
            ->where('type', 'AP')
            ->where('invoice_date', '<', $periodFrom)
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get()
            ->sum(fn($i) => (in_array(strtoupper($i->document_type ?? ''), ['DN', 'CN']) ? -1 : 1) * $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));

        $periodAP_Invoices = Invoice::reportable()
            ->where('company_id', $companyId)
            ->where('type', 'AP')
            ->whereBetween('invoice_date', [$periodFrom, $periodTo])
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        $periodAP_Gross = $periodAP_Invoices->whereNotIn('document_type', ['DN', 'CN'])->sum(fn($i) => $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));
        $periodAP_CN_DN = $periodAP_Invoices->whereIn('document_type', ['DN', 'CN'])->sum(fn($i) => $toUSD($i->total_amount, $i->currency?->code, $i->exchange_rate));
        $periodAP_Net   = $periodAP_Gross - $periodAP_CN_DN;

        $apOpening = $priorAP - $priorPayParts['settled'];
        $apClosing = $apOpening + $periodAP_Net - $periodPayParts['settled'];

        if (!$hideZero || abs($apOpening) > 0.001 || abs($periodAP_Gross) > 0.001 || abs($periodPayParts['settled']) > 0.001 || abs($apClosing) > 0.001) {
            $accounts[] = [
                'code'            => '2000',
                'sub_code'        => 'AP',
                'name'            => 'Accounts Payable',
                'group'           => 'Liabilities',
                'sub_group'       => 'Current Liabilities',
                'currency'        => 'USD',
                'opening_balance' => $apOpening,
                'debit'           => $periodPayParts['settled'] + $periodAP_CN_DN,
                'credit'          => $periodAP_Gross,
                'closing_balance' => $apClosing,
                'type'            => 'credit',
            ];
        }

        $tdsPayOpen = $priorPayParts['tds'];
        $tdsPayClose = $tdsPayOpen + $periodPayParts['tds'];
        if (abs($tdsPayOpen) > 0.001 || abs($periodPayParts['tds']) > 0.001 || abs($tdsPayClose) > 0.001) {
            $accounts[] = [
                'code'            => '2100',
                'sub_code'        => 'TDS_PAY',
                'name'            => 'Withholding Tax / TDS Payable',
                'group'           => 'Liabilities',
                'sub_group'       => 'Current Liabilities',
                'currency'        => 'USD',
                'opening_balance' => $tdsPayOpen,
                'debit'           => 0.0,
                'credit'          => $periodPayParts['tds'],
                'closing_balance' => $tdsPayClose,
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

        $priorNetIncome = $priorAR - $priorAP - $priorRecParts['disc'] + $priorPayParts['disc'];
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

        if (abs($periodRecParts['disc']) > 0.001) {
            $accounts[] = [
                'code'            => '4900',
                'sub_code'        => 'DISC_ALLOWED',
                'name'            => 'Sales Discounts Allowed',
                'group'           => 'Revenue',
                'sub_group'       => 'Operating Revenue',
                'currency'        => 'USD',
                'opening_balance' => 0.0,
                'debit'           => $periodRecParts['disc'],
                'credit'          => 0.0,
                'closing_balance' => $periodRecParts['disc'],
                'type'            => 'debit',
            ];
        }

        if (abs($periodPayParts['disc']) > 0.001) {
            $accounts[] = [
                'code'            => '5900',
                'sub_code'        => 'DISC_EARNED',
                'name'            => 'Purchase Discounts Earned',
                'group'           => 'Expenses',
                'sub_group'       => 'Operating Expenses',
                'currency'        => 'USD',
                'opening_balance' => 0.0,
                'debit'           => 0.0,
                'credit'          => $periodPayParts['disc'],
                'closing_balance' => $periodPayParts['disc'],
                'type'            => 'credit',
            ];
        }

        // ── 5. Revenue (Operating Revenue, Code 4000) ──
        if (!$hideZero || abs($periodAR_Gross) > 0.001 || abs($periodAR_CN) > 0.001) {
            $accounts[] = [
                'code'            => '4000',
                'sub_code'        => 'REV',
                'name'            => 'Freight & Operating Revenue',
                'group'           => 'Revenue',
                'sub_group'       => 'Operating Revenue',
                'currency'        => 'USD',
                'opening_balance' => 0.0,
                'debit'           => $periodAR_CN,
                'credit'          => $periodAR_Gross,
                'closing_balance' => $periodAR_Net,
                'type'            => 'credit',
            ];
        }

        // ── 6. Operating Expenses (Expenses, Code 5000) ──
        if (!$hideZero || abs($periodAP_Gross) > 0.001 || abs($periodAP_CN_DN) > 0.001) {
            $accounts[] = [
                'code'            => '5000',
                'sub_code'        => 'EXP',
                'name'            => 'Cost of Freight & Operating Expenses',
                'group'           => 'Expenses',
                'sub_group'       => 'Operating Expenses',
                'currency'        => 'USD',
                'opening_balance' => 0.0,
                'debit'           => $periodAP_Gross,
                'credit'          => $periodAP_CN_DN,
                'closing_balance' => $periodAP_Net,
                'type'            => 'debit',
            ];
        }

        // ── 7. Manual Journal Entries (strictly company-scoped, POSTED, mapped via gl_accounts first) ──
        $jePriorNominalNet = 0.0;
        $journalLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->leftJoin('gl_accounts', 'gl_accounts.id', '=', 'journal_entry_lines.gl_account_id')
            ->leftJoin('gl_codes', function ($join) use ($companyId) {
                $join->on('gl_codes.id', '=', 'journal_entry_lines.gl_account_id')
                     ->where('gl_codes.company_id', '=', $companyId);
            })
            ->where('journal_entries.company_id', $companyId)
            ->whereNull('journal_entries.deleted_at')
            ->where('journal_entries.status', '=', 'POSTED')
            ->where('journal_entries.entry_date', '<=', $periodTo)
            ->when($officeId, fn($q) => $q->where('journal_entries.office_id', $officeId))
            ->select(
                'journal_entry_lines.*',
                'journal_entries.entry_date',
                'gl_accounts.code as acc_code',
                'gl_accounts.name as acc_name',
                'gl_accounts.type as acc_type',
                'gl_codes.gl_code as legacy_code',
                'gl_codes.gl_name_eng as legacy_name'
            )
            ->get();

        $jeGrouped = [];
        foreach ($journalLines as $line) {
            $lDebit  = (float) ($line->local_debit ?? 0);
            $lCredit = (float) ($line->local_credit ?? 0);
            if ($lDebit <= 0.001 && $lCredit <= 0.001) continue;

            $code = (string) ($line->acc_code ?: ($line->legacy_code ?: '6000'));
            $name = (string) ($line->acc_name ?: ($line->legacy_name ?: ($line->description ?: 'Journal Adjustment')));
            $rawType = strtoupper((string) ($line->acc_type ?? ''));
            if (empty($rawType)) {
                if (str_starts_with($code, '1')) $rawType = 'ASSET';
                elseif (str_starts_with($code, '2')) $rawType = 'LIABILITY';
                elseif (str_starts_with($code, '3')) $rawType = 'EQUITY';
                elseif (str_starts_with($code, '4')) $rawType = 'REVENUE';
                else $rawType = 'EXPENSE';
            }

            $isBeforePeriod = $line->entry_date < $periodFrom;
            if ($isBeforePeriod && in_array($rawType, ['REVENUE', 'EXPENSE'])) {
                $jePriorNominalNet += ($lCredit - $lDebit);
                continue;
            }

            if (!isset($jeGrouped[$code])) {
                [$grp, $subGrp, $normType] = match ($rawType) {
                    'ASSET'     => ['Assets', 'General Ledger Adjustments', 'debit'],
                    'LIABILITY' => ['Liabilities', 'General Ledger Adjustments', 'credit'],
                    'EQUITY'    => ['Equity', 'Owner Equity', 'credit'],
                    'REVENUE'   => ['Revenue', 'Operating Revenue', 'credit'],
                    default     => ['Expenses', 'Operating Expenses', 'debit'],
                };
                $jeGrouped[$code] = [
                    'code'            => $code,
                    'sub_code'        => 'JE',
                    'name'            => $name,
                    'group'           => $grp,
                    'sub_group'       => $subGrp,
                    'currency'        => 'USD',
                    'opening_balance' => 0.0,
                    'debit'           => 0.0,
                    'credit'          => 0.0,
                    'closing_balance' => 0.0,
                    'type'            => $normType,
                ];
            }

            if ($isBeforePeriod) {
                $delta = ($jeGrouped[$code]['type'] === 'debit') ? ($lDebit - $lCredit) : ($lCredit - $lDebit);
                $jeGrouped[$code]['opening_balance'] += $delta;
            } else {
                $jeGrouped[$code]['debit']  += $lDebit;
                $jeGrouped[$code]['credit'] += $lCredit;
            }
        }

        // Apply prior nominal JE net income into Retained Earnings (3200) if present
        if (abs($jePriorNominalNet) > 0.001) {
            $reIdx = null;
            foreach ($accounts as $idx => $a) {
                if ($a['code'] === '3200') { $reIdx = $idx; break; }
            }
            if ($reIdx !== null) {
                $accounts[$reIdx]['opening_balance'] += $jePriorNominalNet;
                $accounts[$reIdx]['closing_balance'] += $jePriorNominalNet;
            } else {
                $accounts[] = [
                    'code'            => '3200',
                    'sub_code'        => 'RETAINED_EARNINGS',
                    'name'            => 'Retained Earnings (Prior Periods)',
                    'group'           => 'Equity',
                    'sub_group'       => 'Owner Equity',
                    'currency'        => 'USD',
                    'opening_balance' => $jePriorNominalNet,
                    'debit'           => 0.0,
                    'credit'          => 0.0,
                    'closing_balance' => $jePriorNominalNet,
                    'type'            => 'credit',
                ];
            }
        }

        foreach ($jeGrouped as $jeAcc) {
            $jeAcc['closing_balance'] = ($jeAcc['type'] === 'debit')
                ? ($jeAcc['opening_balance'] + $jeAcc['debit'] - $jeAcc['credit'])
                : ($jeAcc['opening_balance'] + $jeAcc['credit'] - $jeAcc['debit']);
            if (!$hideZero || abs($jeAcc['opening_balance']) > 0.001 || abs($jeAcc['debit']) > 0.001 || abs($jeAcc['credit']) > 0.001 || abs($jeAcc['closing_balance']) > 0.001) {
                $accounts[] = $jeAcc;
            }
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

        $isBalanced = abs($totalOpeningDebit - $totalOpeningCredit) < 0.01
            && abs($totalDebit - $totalCredit) < 0.01
            && abs($totalClosingDebit - $totalClosingCredit) < 0.01;

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
