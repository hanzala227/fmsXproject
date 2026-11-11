<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Office;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BalanceSheetController extends Controller
{
    /**
     * Show the Balance Sheet report page with filter inputs.
     */
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('accounting.balance-sheet', compact('offices'));
    }

    /**
     * Generate the subledger-based Management Balance Sheet report data via AJAX.
     *
     * Rules:
     * 1. Subledger-based only: Cash from banks, AR/AP from invoice outstanding balances.
     * 2. No plugs or artificial balancing figures anywhere.
     * 3. Multi-currency converted at proper rates as of report date / invoice rate.
     * 4. Cash ties to Bank Book closing.
     * 5. AR ties to Receivable Aging total.
     * 6. AP ties to Payable Aging total.
     * 7. TDS Receivable (Asset) and TDS Payable (Liability) from payments tds_tax.
     * 8. Discounts route to Net Income (Retained Earnings), not Balance Sheet asset/liability.
     * 9. Unallocated payments with no matching bank on a separate red alert line.
     * 10. Variances displayed transparently in red if non-zero.
     */
    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'as_of_date' => 'nullable|date',
            'office_id'  => 'nullable|exists:offices,id',
            'currency'   => 'nullable|in:original,converted',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $companyId = auth()->user()?->company_id ?? 1;
        $asOfDate  = $request->input('as_of_date', date('Y-m-d'));
        $officeId  = $request->input('office_id');

        $currencies = Currency::all()->keyBy('code');

        // ── 1. Cash & Cash Equivalents (Active Bank Accounts) ──
        $banks = DB::table('banks')->where('company_id', $companyId)
            ->where('is_active', 1)->orderBy('bank_name')->get();

        $cashLines = [];
        $totalCashUSD = 0.0;
        $initialCapitalUSD = 0.0;
        $activeBankNames = [];
        $activeBankGLs = [];

        foreach ($banks as $bank) {
            $code = $bank->currency ?: 'USD';
            $currObj = $currencies->get($code);
            $rate = (float)($currObj?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;

            $initUSD = round(((float)$bank->initial_amount) / $rate, 2);
            $initialCapitalUSD += $initUSD;

            $bName = trim($bank->bank_name);
            $activeBankNames[] = $bName;
            if (!empty($bank->gl_no)) {
                $activeBankGLs[] = trim($bank->gl_no);
            }

            // Bank payments query up to as_of_date (strictly scoped to company, using locked per-payment FX rate)
            $bankPayRows = AccountingPayment::with('currency')
                ->where('company_id', $companyId)
                ->where(function($q) use ($bName, $bank) {
                    $q->where('bank_name', $bName);
                    if (!empty($bank->gl_no)) {
                        $q->orWhere('bank_name', trim($bank->gl_no));
                    }
                })
                ->where('payment_date', '<=', $asOfDate)
                ->whereNull('deleted_at')
                ->whereNull('cancelled_at')
                ->whereNull('void_date')
                ->when($officeId, fn($q) => $q->where('office_id', $officeId))
                ->get();

            $rec = 0.0;
            $pay = 0.0;
            $recUSD = 0.0;
            $payUSD = 0.0;
            foreach ($bankPayRows as $bp) {
                $pAmt = (float) $bp->amount;
                $pRate = (float) ($bp->exchange_rate ?? $bp->currency?->exchange_rate ?? $rate);
                if ($pRate <= 0) $pRate = $rate;
                $pUsd = round($pAmt / $pRate, 2);
                if ($bp->type === 'RECEIVED') {
                    $rec += $pAmt;
                    $recUSD += $pUsd;
                } elseif ($bp->type === 'MADE') {
                    $pay += $pAmt;
                    $payUSD += $pUsd;
                }
            }

            $bankClose = (float)$bank->initial_amount + $rec - $pay;
            $bankCloseUSD = round($initUSD + $recUSD - $payUSD, 2);
            $totalCashUSD += $bankCloseUSD;

            $cashLines[] = [
                'label'  => $bank->bank_name,
                'detail' => $code . ' ' . number_format($bankClose, 2) . ' @ ' . number_format($rate, 4) . ' (' . ($bank->gl_no ?: 'Cash in Bank') . ')',
                'amount' => $bankCloseUSD,
                'is_sub' => true,
            ];
        }

        $totalCashUSD = round($totalCashUSD, 2);
        $initialCapitalUSD = round($initialCapitalUSD, 2);

        $cashLines[] = [
            'label'    => 'Total Cash & Cash Equivalents',
            'detail'   => 'Sum of active bank closing balances (ties to Bank Book)',
            'amount'   => $totalCashUSD,
            'is_total' => true,
        ];

        // ── 2. Unallocated Payments (needs fixing) ──
        $allPayments = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->where('payment_date', '<=', $asOfDate)
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        $unallocatedPayments = [];
        $unallocatedNetUSD = 0.0;
        foreach ($allPayments as $p) {
            $bName = trim($p->bank_name ?? '');
            $matched = in_array($bName, $activeBankNames) || in_array($bName, $activeBankGLs);
            if (!$matched) {
                $unallocatedPayments[] = $p;
                $pRate = (float)($p->exchange_rate ?? $p->currency?->exchange_rate ?? 1.0);
                if ($pRate <= 0) $pRate = 1.0;
                $amtUSD = round(((float)$p->amount) / $pRate, 2);
                if ($p->type === 'RECEIVED') {
                    $unallocatedNetUSD += $amtUSD;
                } elseif ($p->type === 'MADE') {
                    $unallocatedNetUSD -= $amtUSD;
                }
            }
        }
        $unallocatedNetUSD = round($unallocatedNetUSD, 2);
        $unallocatedCount = count($unallocatedPayments);

        // ── 3. Accounts Receivable (AR) ──
        $arInvoices = Invoice::with([
            'currency',
            'payments' => function($q) use ($asOfDate) {
                $q->whereNull('deleted_at')
                  ->whereNull('cancelled_at')
                  ->whereNull('void_date')
                  ->where('payment_date', '<=', $asOfDate);
            },
        ])
        ->withCount([
            'payments as active_payments_count' => function($q) {
                $q->whereNull('deleted_at')->whereNull('cancelled_at')->whereNull('void_date');
            },
            'payments as future_payments_count' => function($q) use ($asOfDate) {
                $q->whereNull('deleted_at')->whereNull('cancelled_at')->whereNull('void_date')->where('payment_date', '>', $asOfDate);
            },
        ])
        ->reportable()
        ->where('company_id', $companyId)
        ->where('type', 'AR')
        ->where('invoice_date', '<=', $asOfDate)
        ->when($officeId, fn($q) => $q->where('office_id', $officeId))
        ->get();

        $totalARUSD = 0.0;
        $totalARRaw = 0.0;
        $totalAROriginalRevenueUSD = 0.0;
        $totalCustomerDiscountsUSD = 0.0;
        $totalTdsReceivableUSD = 0.0;
        $unlinkedArPaidUSD = 0.0;

        foreach ($arInvoices as $inv) {
            $rate = (float)($inv->exchange_rate ?? $inv->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;

            $isCN = strtoupper($inv->document_type ?? '') === 'CN';
            $invTotalUSD = round(((float)$inv->total_amount) / $rate, 2);
            $totalAROriginalRevenueUSD += ($isCN ? -$invTotalUSD : $invTotalUSD);

            $cashRec = (float)$inv->payments->where('type', 'RECEIVED')->sum('amount');
            $tdsRec  = (float)$inv->payments->where('type', 'RECEIVED')->sum('tds_tax');
            $discRec = (float)$inv->payments->where('type', 'RECEIVED')->sum('discount');

            $totalCustomerDiscountsUSD += round($discRec / $rate, 2);
            $totalTdsReceivableUSD     += round($tdsRec / $rate, 2);

            $settled = $cashRec + $tdsRec + $discRec;
            $rawOutstanding = max(0.0, (float)$inv->total_amount - $settled);
            $hasFuturePayments = ((int) ($inv->future_payments_count ?? 0)) > 0;
            if ($inv->status === 'PAID' && !$hasFuturePayments) {
                $unlinkedArPaidUSD += round(((float)$inv->total_amount - $settled) / $rate, 2);
                $rawOutstanding = 0.0;
            } elseif ($rawOutstanding <= 0.001) {
                $rawOutstanding = 0.0;
            }

            $convOutstanding = round($rawOutstanding / $rate, 2);

            $signedRaw  = $isCN ? -$rawOutstanding : $rawOutstanding;
            $signedConv = $isCN ? -$convOutstanding : $convOutstanding;

            $totalARRaw += $signedRaw;
            $totalARUSD += $signedConv;
        }

        // Unattached (on-account) Customer Receipts TDS: convert to USD via locked rate
        $unattachedRecRows = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->whereNull('invoice_id')
            ->where('type', 'RECEIVED')
            ->where('payment_date', '<=', $asOfDate)
            ->whereNull('deleted_at')->whereNull('cancelled_at')->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();
        $unattachedTdsRec = 0.0;
        $unattachedRecTotalUSD = 0.0;
        foreach ($unattachedRecRows as $up) {
            $uRate = (float) ($up->exchange_rate ?? $up->currency?->exchange_rate ?? 1.0);
            if ($uRate <= 0) $uRate = 1.0;
            $uCashUSD = round(((float) $up->amount) / $uRate, 2);
            $uTdsUSD  = round(((float) ($up->tds_tax ?? 0)) / $uRate, 2);
            $uDiscUSD = round(((float) ($up->discount ?? 0)) / $uRate, 2);
            $unattachedTdsRec += $uTdsUSD;
            $totalCustomerDiscountsUSD += $uDiscUSD;
            $unattachedRecTotalUSD += ($uCashUSD + $uTdsUSD + $uDiscUSD);
        }
        $unappliedCustomerAdvancesUSD = round(max(0.0, $unattachedRecTotalUSD - max(0.0, $unlinkedArPaidUSD)), 2);
        $totalTdsReceivableUSD = round($totalTdsReceivableUSD + $unattachedTdsRec, 2);
        $totalARUSD = round($totalARUSD, 2);
        $totalARRaw = round($totalARRaw, 2);

        // ── 4. Accounts Payable (AP) ──
        $apInvoices = Invoice::with([
            'currency',
            'payments' => function($q) use ($asOfDate) {
                $q->whereNull('deleted_at')
                  ->whereNull('cancelled_at')
                  ->whereNull('void_date')
                  ->where('payment_date', '<=', $asOfDate);
            },
        ])
        ->withCount([
            'payments as active_payments_count' => function($q) {
                $q->whereNull('deleted_at')->whereNull('cancelled_at')->whereNull('void_date');
            },
            'payments as future_payments_count' => function($q) use ($asOfDate) {
                $q->whereNull('deleted_at')->whereNull('cancelled_at')->whereNull('void_date')->where('payment_date', '>', $asOfDate);
            },
        ])
        ->reportable()
        ->where('company_id', $companyId)
        ->where('type', 'AP')
        ->where('invoice_date', '<=', $asOfDate)
        ->when($officeId, fn($q) => $q->where('office_id', $officeId))
        ->get();

        $totalAPUSD = 0.0;
        $totalAPRaw = 0.0;
        $totalAPOriginalCostUSD = 0.0;
        $totalVendorDiscountsUSD = 0.0;
        $totalTdsPayableUSD = 0.0;
        $unlinkedApPaidUSD = 0.0;

        foreach ($apInvoices as $inv) {
            $rate = (float)($inv->exchange_rate ?? $inv->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;

            $isCostReduction = in_array(strtoupper($inv->document_type ?? ''), ['DN', 'CN']);
            $invTotalUSD = round(((float)$inv->total_amount) / $rate, 2);
            $totalAPOriginalCostUSD += ($isCostReduction ? -$invTotalUSD : $invTotalUSD);

            $cashPaid    = (float)$inv->payments->where('type', 'MADE')->sum('amount');
            $tdsWithheld = (float)$inv->payments->where('type', 'MADE')->sum('tds_tax');
            $discEarned  = (float)$inv->payments->where('type', 'MADE')->sum('discount');

            $totalVendorDiscountsUSD += round($discEarned / $rate, 2);
            $totalTdsPayableUSD      += round($tdsWithheld / $rate, 2);

            $settled = $cashPaid + $tdsWithheld + $discEarned;
            $rawOutstanding = max(0.0, (float)$inv->total_amount - $settled);
            $hasFuturePayments = ((int) ($inv->future_payments_count ?? 0)) > 0;
            if ($inv->status === 'PAID' && !$hasFuturePayments) {
                $unlinkedApPaidUSD += round(((float)$inv->total_amount - $settled) / $rate, 2);
                $rawOutstanding = 0.0;
            } elseif ($rawOutstanding <= 0.001) {
                $rawOutstanding = 0.0;
            }

            $convOutstanding = round($rawOutstanding / $rate, 2);

            $signedRaw  = $isCostReduction ? -abs($rawOutstanding) : $rawOutstanding;
            $signedConv = $isCostReduction ? -abs($convOutstanding) : $convOutstanding;

            $totalAPRaw += $signedRaw;
            $totalAPUSD += $signedConv;
        }

        // Unattached (on-account) Vendor Disbursements TDS: convert to USD via locked rate
        $unattachedPayRows = AccountingPayment::with('currency')
            ->where('company_id', $companyId)
            ->whereNull('invoice_id')
            ->where('type', 'MADE')
            ->where('payment_date', '<=', $asOfDate)
            ->whereNull('deleted_at')->whereNull('cancelled_at')->whereNull('void_date')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();
        $unattachedTdsPay = 0.0;
        $unattachedPayTotalUSD = 0.0;
        foreach ($unattachedPayRows as $up) {
            $uRate = (float) ($up->exchange_rate ?? $up->currency?->exchange_rate ?? 1.0);
            if ($uRate <= 0) $uRate = 1.0;
            $uCashUSD = round(((float) $up->amount) / $uRate, 2);
            $uTdsUSD  = round(((float) ($up->tds_tax ?? 0)) / $uRate, 2);
            $uDiscUSD = round(((float) ($up->discount ?? 0)) / $uRate, 2);
            $unattachedTdsPay += $uTdsUSD;
            $totalVendorDiscountsUSD += $uDiscUSD;
            $unattachedPayTotalUSD += ($uCashUSD + $uTdsUSD + $uDiscUSD);
        }
        $unappliedVendorPrepaymentsUSD = round(max(0.0, $unattachedPayTotalUSD - max(0.0, $unlinkedApPaidUSD)), 2);
        $totalTdsPayableUSD = round($totalTdsPayableUSD + $unattachedTdsPay, 2);
        $totalAPUSD = round($totalAPUSD, 2);
        $totalAPRaw = round($totalAPRaw, 2);

        // ── 4B. Posted Manual Journal Entries (strictly company-scoped) ──
        $jeLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->leftJoin('gl_accounts', 'gl_accounts.id', '=', 'journal_entry_lines.gl_account_id')
            ->leftJoin('gl_codes', function ($join) use ($companyId) {
                $join->on('gl_codes.id', '=', 'journal_entry_lines.gl_account_id')
                     ->where('gl_codes.company_id', '=', $companyId);
            })
            ->where('journal_entries.company_id', $companyId)
            ->whereNull('journal_entries.deleted_at')
            ->where('journal_entries.status', '=', 'POSTED')
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->when($officeId, fn($q) => $q->where('journal_entries.office_id', $officeId))
            ->select(
                'journal_entry_lines.gl_account_id',
                'journal_entry_lines.local_debit',
                'journal_entry_lines.local_credit',
                'gl_accounts.code as acc_code',
                'gl_accounts.name as acc_name',
                'gl_accounts.type as acc_type',
                'gl_codes.gl_code as legacy_code',
                'gl_codes.gl_name_eng as legacy_name'
            )
            ->get();

        $jeAssetBuckets = [];
        $jeLiabBuckets  = [];
        $jeEqBuckets    = [];
        $jeAssetsTotalUSD = 0.0;
        $jeLiabsTotalUSD  = 0.0;
        $jeEqTotalUSD     = 0.0;
        $jeNetRevenueUSD  = 0.0;
        $jeNetExpenseUSD  = 0.0;

        foreach ($jeLines as $line) {
            $code = (string) ($line->acc_code ?: ($line->legacy_code ?: 'GL-' . $line->gl_account_id));
            $name = (string) ($line->acc_name ?: ($line->legacy_name ?: 'Journal Adjustment'));
            $type = strtoupper((string) ($line->acc_type ?? ''));
            if (empty($type)) {
                if (str_starts_with($code, '1')) $type = 'ASSET';
                elseif (str_starts_with($code, '2')) $type = 'LIABILITY';
                elseif (str_starts_with($code, '3')) $type = 'EQUITY';
                elseif (str_starts_with($code, '4')) $type = 'REVENUE';
                else $type = 'EXPENSE';
            }

            $dr = (float) ($line->local_debit ?? 0);
            $cr = (float) ($line->local_credit ?? 0);

            if ($type === 'ASSET') {
                $delta = $dr - $cr;
                $jeAssetsTotalUSD += $delta;
                if (!isset($jeAssetBuckets[$code])) $jeAssetBuckets[$code] = ['label' => "{$code} - {$name} (Journal)", 'amount' => 0.0];
                $jeAssetBuckets[$code]['amount'] += $delta;
            } elseif ($type === 'LIABILITY') {
                $delta = $cr - $dr;
                $jeLiabsTotalUSD += $delta;
                if (!isset($jeLiabBuckets[$code])) $jeLiabBuckets[$code] = ['label' => "{$code} - {$name} (Journal)", 'amount' => 0.0];
                $jeLiabBuckets[$code]['amount'] += $delta;
            } elseif ($type === 'EQUITY') {
                $delta = $cr - $dr;
                $jeEqTotalUSD += $delta;
                if (!isset($jeEqBuckets[$code])) $jeEqBuckets[$code] = ['label' => "{$code} - {$name} (Journal)", 'amount' => 0.0];
                $jeEqBuckets[$code]['amount'] += $delta;
            } elseif ($type === 'REVENUE') {
                $jeNetRevenueUSD += ($cr - $dr);
            } else {
                $jeNetExpenseUSD += ($dr - $cr);
            }
        }

        // ── 5. Build Report Sections ──
        $currentAssetLines = $cashLines;

        if ($unallocatedCount > 0) {
            $currentAssetLines[] = [
                'label'    => 'Unallocated payments (needs fixing)',
                'detail'   => $unallocatedCount . ' payment(s) without matching active bank',
                'amount'   => $unallocatedNetUSD,
                'is_alert' => true,
            ];
        }

        $currentAssetLines[] = [
            'label'  => 'Accounts Receivable (AR)',
            'detail' => 'Billed AR Invoices Outstanding (ties to Aging)',
            'amount' => $totalARUSD,
        ];

        if ($totalTdsReceivableUSD > 0) {
            $currentAssetLines[] = [
                'label'  => 'Withholding Tax / TDS Receivable',
                'detail' => 'Tax withheld by customers on money receipts',
                'amount' => $totalTdsReceivableUSD,
            ];
        }

        foreach ($jeAssetBuckets as $b) {
            if (abs($b['amount']) > 0.001) {
                $currentAssetLines[] = [
                    'label'  => $b['label'],
                    'detail' => 'Posted manual journal adjustments',
                    'amount' => round($b['amount'], 2),
                ];
            }
        }

        if ($unappliedVendorPrepaymentsUSD > 0) {
            $currentAssetLines[] = [
                'label'  => 'Vendor Prepayments (Unattached)',
                'detail' => 'On-account vendor disbursements pending bill application',
                'amount' => $unappliedVendorPrepaymentsUSD,
            ];
        }

        $totalAssets = round($totalCashUSD + $unallocatedNetUSD + $totalARUSD + $totalTdsReceivableUSD + $unappliedVendorPrepaymentsUSD + $jeAssetsTotalUSD, 2);

        $sections = [
            [
                'title'  => 'Assets',
                'groups' => [
                    [
                        'name'  => 'Current Assets',
                        'lines' => $currentAssetLines,
                        'total' => $totalAssets,
                    ],
                ],
                'total' => $totalAssets,
            ],
        ];

        // LIABILITIES
        $currentLiabLines = [
            [
                'label'  => 'Accounts Payable (AP)',
                'detail' => 'Billed AP Invoices Outstanding (ties to Aging)',
                'amount' => $totalAPUSD,
            ],
        ];

        if ($totalTdsPayableUSD > 0) {
            $currentLiabLines[] = [
                'label'  => 'Withholding Tax / TDS Payable',
                'detail' => 'Tax withheld on vendor disbursements',
                'amount' => $totalTdsPayableUSD,
            ];
        }

        if ($unappliedCustomerAdvancesUSD > 0) {
            $currentLiabLines[] = [
                'label'  => 'Customer Advances (Unattached)',
                'detail' => 'On-account customer receipts pending invoice application',
                'amount' => $unappliedCustomerAdvancesUSD,
            ];
        }

        foreach ($jeLiabBuckets as $b) {
            if (abs($b['amount']) > 0.001) {
                $currentLiabLines[] = [
                    'label'  => $b['label'],
                    'detail' => 'Posted manual journal adjustments',
                    'amount' => round($b['amount'], 2),
                ];
            }
        }

        $totalLiabilities = round($totalAPUSD + $totalTdsPayableUSD + $unappliedCustomerAdvancesUSD + $jeLiabsTotalUSD, 2);

        $sections[] = [
            'title'  => 'Liabilities',
            'groups' => [
                [
                    'name'  => 'Current Liabilities',
                    'lines' => $currentLiabLines,
                    'total' => $totalLiabilities,
                ],
            ],
            'total' => $totalLiabilities,
        ];

        // EQUITY (No balancing figure / no plug)
        $revTotalForEq = $totalAROriginalRevenueUSD + $jeNetRevenueUSD;
        $expTotalForEq = $totalAPOriginalCostUSD + $jeNetExpenseUSD;
        $netIncomeUSD = round($revTotalForEq - $expTotalForEq - $totalCustomerDiscountsUSD + $totalVendorDiscountsUSD, 2);
        $totalEquity = round($initialCapitalUSD + $jeEqTotalUSD + $netIncomeUSD, 2);

        $equityLines = [
            [
                'label'  => "Owner's Capital / Opening Equity",
                'detail' => 'Initial capital balances of active banks',
                'amount' => $initialCapitalUSD,
            ],
            [
                'label'  => 'Cumulative Net Income (Retained Earnings)',
                'detail' => 'Revenue ($' . number_format($revTotalForEq, 2) . ') - Costs ($' . number_format($expTotalForEq, 2) . ')' .
                            ($totalCustomerDiscountsUSD > 0 ? ' - Sales Disc ($' . number_format($totalCustomerDiscountsUSD, 2) . ')' : '') .
                            ($totalVendorDiscountsUSD > 0 ? ' + Purch Disc ($' . number_format($totalVendorDiscountsUSD, 2) . ')' : ''),
                'amount' => $netIncomeUSD,
            ],
        ];

        foreach ($jeEqBuckets as $b) {
            if (abs($b['amount']) > 0.001) {
                $equityLines[] = [
                    'label'  => $b['label'],
                    'detail' => 'Posted manual journal adjustments',
                    'amount' => round($b['amount'], 2),
                ];
            }
        }

        $sections[] = [
            'title'  => 'Equity',
            'groups' => [
                [
                    'name'  => "Owner's Equity",
                    'lines' => $equityLines,
                    'total' => $totalEquity,
                ],
            ],
            'total' => $totalEquity,
        ];

        $totalLiabilitiesAndEquity = round($totalLiabilities + $totalEquity, 2);
        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;
        $varianceEquation = round($totalAssets - $totalLiabilitiesAndEquity, 2);

        // ── 6. Dynamic Reconciliation Checks (Tying to Bank Book & Aging) ──
        $agingCtrl = new AgingReportController();
        $reqAR = new Request(['as_of_date' => $asOfDate, 'aging_type' => 'ar', 'office_id' => $officeId]);
        $agingARResp = $agingCtrl->view($reqAR)->getData(true);
        $agingARTotal = (float)($agingARResp['summary']['grand_total'] ?? 0.0);

        $reqAP = new Request(['as_of_date' => $asOfDate, 'aging_type' => 'ap', 'office_id' => $officeId]);
        $agingAPResp = $agingCtrl->view($reqAP)->getData(true);
        $agingAPTotal = (float)($agingAPResp['summary']['grand_total'] ?? 0.0);

        $bankBookCtrl = new BankBookBalanceController();
        $reqBB = new Request(['period_from' => '2000-01-01', 'period_to' => $asOfDate, 'status' => 'active', 'currency' => 'main_currency']);
        $bankBookResp = $bankBookCtrl->view($reqBB)->getData(true);
        $bankBookClosingUSD = (float)($bankBookResp['totals_usd']['closing_balance'] ?? ($bankBookResp['totals']['closing_balance'] ?? 0.0));

        $reconciliationChecks = [
            'cash' => [
                'label'      => 'Cash vs Bank Book Closing',
                'bs_amount'  => $totalCashUSD,
                'ref_amount' => $bankBookClosingUSD,
                'variance'   => round($totalCashUSD - $bankBookClosingUSD, 2),
                'is_match'   => abs($totalCashUSD - $bankBookClosingUSD) < 0.01,
            ],
            'ar' => [
                'label'      => 'Accounts Receivable vs Aging Report',
                'bs_amount'  => $totalARUSD,
                'ref_amount' => $agingARTotal,
                'variance'   => round($totalARUSD - $agingARTotal, 2),
                'is_match'   => abs($totalARUSD - $agingARTotal) < 0.01,
            ],
            'ap' => [
                'label'      => 'Accounts Payable vs Aging Report',
                'bs_amount'  => $totalAPUSD,
                'ref_amount' => $agingAPTotal,
                'variance'   => round($totalAPUSD - $agingAPTotal, 2),
                'is_match'   => abs($totalAPUSD - $agingAPTotal) < 0.01,
            ],
            'equation' => [
                'label'      => 'Accounting Equation: Assets = L + E',
                'bs_amount'  => $totalAssets,
                'ref_amount' => $totalLiabilitiesAndEquity,
                'variance'   => $varianceEquation,
                'is_match'   => $isBalanced,
            ],
        ];

        return response()->json([
            'success'               => true,
            'title'                 => 'Management Balance Sheet - based on invoices and payments, unaudited',
            'as_of_date'            => $asOfDate,
            'office_id'             => $officeId,
            'sections'              => $sections,
            'reconciliation_checks' => $reconciliationChecks,
            'summary'               => [
                'total_assets'                 => $totalAssets,
                'total_liabilities'            => $totalLiabilities,
                'total_equity'                 => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'variance'                     => $varianceEquation,
                'is_balanced'                  => $isBalanced,
                'unallocated_count'            => $unallocatedCount,
                'unallocated_amount'           => $unallocatedNetUSD,
            ],
        ]);
    }

    /**
     * Render a print-friendly version of the report.
     */
    public function printReport(Request $request)
    {
        $data = $this->view($request)->getData(true);

        return view('accounting.balance-sheet-print', [
            'title'                 => $data['title'] ?? 'Management Balance Sheet - based on invoices and payments, unaudited',
            'sections'              => $data['sections'] ?? [],
            'summary'               => $data['summary'] ?? [],
            'reconciliationChecks'  => $data['reconciliation_checks'] ?? [],
            'asOfDate'              => $request->as_of_date ?? date('Y-m-d'),
            'currency'              => $request->currency ?? 'converted',
        ]);
    }

    /**
     * Export report data to CSV.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->view($request)->getData(true);
        $sections = $data['sections'] ?? [];
        $summary = $data['summary'] ?? [];
        $checks = $data['reconciliation_checks'] ?? [];

        $filename = 'management-balance-sheet-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($sections, $summary, $checks) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['Management Balance Sheet - based on invoices and payments, unaudited']);
            fputcsv($handle, ['As of Date', request()->as_of_date ?? date('Y-m-d')]);
            fputcsv($handle, []);

            foreach ($sections as $section) {
                fputcsv($handle, [strtoupper($section['title'])]);
                fputcsv($handle, []);

                foreach ($section['groups'] as $group) {
                    fputcsv($handle, ['  ' . $group['name']]);
                    fputcsv($handle, ['    Description', 'Detail', 'Amount (USD)']);

                    foreach ($group['lines'] as $line) {
                        $label = $line['label'];
                        if (!empty($line['is_sub']) || !empty($line['is_total'])) {
                            $label = '>> ' . $label;
                        }
                        fputcsv($handle, [
                            '    ' . $label,
                            $line['detail'] ?? '',
                            number_format($line['amount'], 2),
                        ]);
                    }

                    fputcsv($handle, [
                        '  Total ' . $group['name'],
                        '',
                        number_format($group['total'], 2),
                    ]);
                    fputcsv($handle, []);
                }

                fputcsv($handle, [
                    'Total ' . $section['title'],
                    '',
                    number_format($section['total'], 2),
                ]);
                fputcsv($handle, []);
            }

            fputcsv($handle, ['SUMMARY & FINANCIAL POSITION']);
            fputcsv($handle, ['Total Assets (USD)', '', number_format($summary['total_assets'] ?? 0, 2)]);
            fputcsv($handle, ['Total Liabilities (USD)', '', number_format($summary['total_liabilities'] ?? 0, 2)]);
            fputcsv($handle, ['Total Equity (USD)', '', number_format($summary['total_equity'] ?? 0, 2)]);
            fputcsv($handle, ['Total Liabilities & Equity (USD)', '', number_format($summary['total_liabilities_and_equity'] ?? 0, 2)]);
            fputcsv($handle, ['Equation Variance (USD)', '', number_format($summary['variance'] ?? 0, 2)]);
            fputcsv($handle, ['Balanced', '', ($summary['is_balanced'] ?? false) ? 'Yes' : 'No']);
            fputcsv($handle, []);

            fputcsv($handle, ['INTEGRITY & SUBLEDGER RECONCILIATION CHECKS']);
            fputcsv($handle, ['Check', 'BS Amount', 'Reference Amount', 'Variance', 'Status']);
            foreach ($checks as $c) {
                fputcsv($handle, [
                    $c['label'],
                    number_format($c['bs_amount'], 2),
                    number_format($c['ref_amount'], 2),
                    number_format($c['variance'], 2),
                    $c['is_match'] ? 'PASS' : 'VARIANCE',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
