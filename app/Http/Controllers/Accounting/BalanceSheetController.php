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
     * Generate the Balance Sheet report data via AJAX.
     *
     * GAAP Balance Sheet equation: Assets = Liabilities + Equity
     *
     * Assets:
     *   - Cash & Cash Equivalents (Bank accounts closing balances as of date)
     *   - Accounts Receivable (AR invoices outstanding as of date)
     *   - Advances to Vendors / Prepayments (when disbursements exceed billed AP)
     *
     * Liabilities:
     *   - Accounts Payable (AP invoices outstanding as of date)
     *   - Customer Advances / Unearned Revenue (when receipts exceed billed AR)
     *
     * Equity:
     *   - Owner's Capital (Initial Equity from Settings / Bank initial balances)
     *   - Retained Earnings (Cumulative Net Income: Revenue - Expenses)
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

        // ── 1. Cash & Cash Equivalents (Bank Accounts) ──
        $banks = DB::table('banks')->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhere('company_id', 1);
        })->where('is_active', 1)->orderBy('bank_name')->get();

        $cashLines = [];
        $totalCashUSD = 0.0;
        $initialCapitalUSD = 0.0;

        foreach ($banks as $bank) {
            $code = $bank->currency ?: 'USD';
            $currObj = $currencies->get($code);
            $rate = (float)($currObj?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;

            $initUSD = round(((float)$bank->initial_amount) / $rate, 2);
            $initialCapitalUSD += $initUSD;

            // Bank payments query
            $bankPayQuery = AccountingPayment::where(function($q) use ($bank) {
                $q->where('bank_name', trim($bank->bank_name));
                if (!empty($bank->gl_no)) {
                    $q->orWhere('bank_name', trim($bank->gl_no));
                }
            })
            ->where('payment_date', '<=', $asOfDate)
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId));

            $rec = (float)(clone $bankPayQuery)->where('type', 'RECEIVED')->sum('amount');
            $pay = (float)(clone $bankPayQuery)->where('type', 'MADE')->sum('amount');

            $bankClose = (float)$bank->initial_amount + $rec - $pay;
            $bankCloseUSD = round($bankClose / $rate, 2);
            $totalCashUSD += $bankCloseUSD;

            $cashLines[] = [
                'label'  => $bank->bank_name,
                'detail' => $code . ' (' . ($bank->gl_no ?: 'Cash in Bank') . ')',
                'amount' => $bankCloseUSD,
                'is_sub' => true,
            ];
        }

        $cashLines[] = [
            'label'    => 'Total Cash & Cash Equivalents',
            'detail'   => '',
            'amount'   => $totalCashUSD,
            'is_total' => true,
        ];

        // ── 2. Invoices (AR & AP) ──
        $invoices = Invoice::with('currency')
            ->where('invoice_date', '<=', $asOfDate)
            ->where('status', '!=', 'VOID')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        $totalARRevenue = 0.0;
        $totalAPExpenses = 0.0;

        foreach ($invoices as $inv) {
            $rate = (float)($inv->exchange_rate ?? $inv->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;
            $baseAmount = ($rate != 1.0) ? round(((float)$inv->total_amount) / $rate, 2) : (float)$inv->total_amount;
            $isCN = strtoupper($inv->document_type ?? '') === 'CN';
            $isDN = strtoupper($inv->document_type ?? '') === 'DN';

            if ($inv->type === 'AR') {
                $totalARRevenue += ($isCN ? -$baseAmount : $baseAmount);
            } elseif ($inv->type === 'AP') {
                $totalAPExpenses += ($isDN ? -$baseAmount : $baseAmount);
            }
        }

        // ── 3. Total Payments / Receipts as of date ──
        $allPayments = AccountingPayment::with('currency')
            ->where('payment_date', '<=', $asOfDate)
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        $totalRecUSD = 0.0;
        $totalPayUSD = 0.0;

        foreach ($allPayments as $p) {
            $rate = (float)($p->exchange_rate ?? $p->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;
            $baseAmount = ($rate != 1.0) ? round(((float)$p->amount) / $rate, 2) : (float)$p->amount;
            if ($p->type === 'RECEIVED') {
                $totalRecUSD += $baseAmount;
            } elseif ($p->type === 'MADE') {
                $totalPayUSD += $baseAmount;
            }
        }

        // ── 4. AR and AP Position & Prepayments ──
        $arOutstanding = $totalARRevenue - $totalRecUSD;
        $netAR = max(0.0, $arOutstanding);
        $customerAdvances = max(0.0, -$arOutstanding);

        $apOutstanding = $totalAPExpenses - $totalPayUSD;
        $netAP = max(0.0, $apOutstanding);
        $vendorAdvances = max(0.0, -$apOutstanding);

        // ── 5. Build Report Sections ──
        $sections = [];

        // ASSETS
        $currentAssetLines = $cashLines;
        $currentAssetLines[] = [
            'label'  => 'Accounts Receivable',
            'detail' => 'Billed AR Invoices Outstanding',
            'amount' => $netAR,
        ];

        if ($vendorAdvances > 0) {
            $currentAssetLines[] = [
                'label'  => 'Advances to Vendors / Prepayments',
                'detail' => 'Disbursements in excess of billed AP invoices',
                'amount' => $vendorAdvances,
            ];
        }

        $totalAssets = round($totalCashUSD + $netAR + $vendorAdvances, 2);

        $sections[] = [
            'title'  => 'Assets',
            'groups' => [
                [
                    'name'  => 'Current Assets',
                    'lines' => $currentAssetLines,
                    'total' => $totalAssets,
                ],
            ],
            'total' => $totalAssets,
        ];

        // LIABILITIES
        $currentLiabLines = [
            [
                'label'  => 'Accounts Payable',
                'detail' => 'Billed AP Invoices Outstanding',
                'amount' => $netAP,
            ],
        ];

        if ($customerAdvances > 0) {
            $currentLiabLines[] = [
                'label'  => 'Customer Advances / Unearned Revenue',
                'detail' => 'Receipts in excess of billed AR invoices',
                'amount' => $customerAdvances,
            ];
        }

        $totalLiabilities = round($netAP + $customerAdvances, 2);

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

        // EQUITY
        $retainedEarnings = round($totalARRevenue - $totalAPExpenses, 2);
        $totalEquity = round($initialCapitalUSD + $retainedEarnings, 2);

        $equityLines = [
            [
                'label'  => "Owner's Capital / Opening Equity",
                'detail' => 'Initial capital investment and bank balances',
                'amount' => $initialCapitalUSD,
            ],
            [
                'label'  => 'Retained Earnings (Cumulative Net Income)',
                'detail' => 'Revenue (' . number_format($totalARRevenue, 2) . ') - Expenses (' . number_format($totalAPExpenses, 2) . ')',
                'amount' => $retainedEarnings,
            ],
        ];

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

        return response()->json([
            'success'    => true,
            'as_of_date' => $asOfDate,
            'office_id'  => $officeId,
            'sections'   => $sections,
            'summary'    => [
                'total_assets'                 => $totalAssets,
                'total_liabilities'            => $totalLiabilities,
                'total_equity'                 => $totalEquity,
                'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
                'is_balanced'                  => $isBalanced,
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
            'sections' => $data['sections'] ?? [],
            'summary'  => $data['summary'] ?? [],
            'asOfDate' => $request->as_of_date ?? date('Y-m-d'),
            'currency' => $request->currency ?? 'original',
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

        $filename = 'balance-sheet-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($sections, $summary) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['Balance Sheet Report']);
            fputcsv($handle, ['As of Date', request()->as_of_date ?? date('Y-m-d')]);
            fputcsv($handle, []);

            foreach ($sections as $section) {
                fputcsv($handle, [strtoupper($section['title'])]);
                fputcsv($handle, []);

                foreach ($section['groups'] as $group) {
                    fputcsv($handle, ['  ' . $group['name']]);
                    fputcsv($handle, ['    Description', '', 'Amount']);

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

            fputcsv($handle, ['SUMMARY']);
            fputcsv($handle, ['Total Assets', '', number_format($summary['total_assets'] ?? 0, 2)]);
            fputcsv($handle, ['Total Liabilities', '', number_format($summary['total_liabilities'] ?? 0, 2)]);
            fputcsv($handle, ['Total Equity', '', number_format($summary['total_equity'] ?? 0, 2)]);
            fputcsv($handle, ['Total Liabilities & Equity', '', number_format($summary['total_liabilities_and_equity'] ?? 0, 2)]);
            fputcsv($handle, ['Balanced', '', ($summary['is_balanced'] ?? false) ? 'Yes' : 'No']);

            fclose($handle);
        }, 200, $headers);
    }
}
