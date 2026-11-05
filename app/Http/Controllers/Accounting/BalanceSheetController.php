<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceSheetRequest;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\AccountingPayment;
use App\Models\Office;
use Illuminate\Http\Request;
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
     * Balance Sheet equation: Assets = Liabilities + Equity
     *
     * Assets:
     *   - Cash & Cash Equivalents (bank opening balances + net receipts/payments)
     *   - Accounts Receivable (AR invoices outstanding)
     *
     * Liabilities:
     *   - Accounts Payable (AP invoices outstanding)
     *
     * Equity:
     *   - Retained Earnings (Revenue - Expenses)
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

        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $officeId = $request->input('office_id');

        // ── Total receipts and payments up to as-of date (computed in functional base currency) ──
        $payments = AccountingPayment::with('currency')
            ->where('payment_date', '<=', $asOfDate)
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        $totalReceipts = 0.0;
        $totalPaymentsMade = 0.0;

        foreach ($payments as $p) {
            $rate = (float) ($p->exchange_rate ?? $p->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;
            $baseAmount = ($rate != 1.0) ? round(((float)$p->amount) / $rate, 2) : (float)$p->amount;
            if ($p->type === 'RECEIVED') {
                $totalReceipts += $baseAmount;
            } elseif ($p->type === 'MADE') {
                $totalPaymentsMade += $baseAmount;
            }
        }

        // ── Cash & Cash Equivalents ──
        $bankAccounts = BankAccount::with('currency')->orderBy('name')->get();

        $cashItems = [];
        $totalBankOpeningBalances = 0;

        foreach ($bankAccounts as $account) {
            $rate = (float) ($account->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;
            $openingBalance = ($rate != 1.0) ? round(((float)$account->opening_balance) / $rate, 2) : (float)$account->opening_balance;
            $totalBankOpeningBalances += $openingBalance;

            $cashItems[] = [
                'name'     => $account->name . ($account->account_no ? ' (' . $account->account_no . ')' : ''),
                'bank_name' => $account->bank_name ?? '',
                'currency' => $account->currency?->code ?? 'USD',
                'balance'  => $openingBalance,
            ];
        }

        $netCashMovement = $totalReceipts - $totalPaymentsMade;
        $totalCash = $totalBankOpeningBalances + $netCashMovement;

        // ── Accounts Receivable & Accounts Payable (in functional base currency) ──
        $invoices = Invoice::with('currency')
            ->where('invoice_date', '<=', $asOfDate)
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->get();

        $totalAR = 0.0;
        $totalAP = 0.0;

        foreach ($invoices as $inv) {
            $rate = (float) ($inv->exchange_rate ?? $inv->currency?->exchange_rate ?? 1.0);
            if ($rate <= 0) $rate = 1.0;
            $baseAmount = ($rate != 1.0) ? round(((float)$inv->total_amount) / $rate, 2) : (float)$inv->total_amount;
            $isCN = strtoupper($inv->document_type ?? '') === 'CN';
            $isDN = strtoupper($inv->document_type ?? '') === 'DN';

            if ($inv->type === 'AR') {
                $totalAR += ($isCN ? -$baseAmount : $baseAmount);
            } elseif ($inv->type === 'AP') {
                $totalAP += ($isDN ? -$baseAmount : $baseAmount);
            }
        }

        // Payments received reduce AR
        $netAR = max(0.0, $totalAR - $totalReceipts);

        // Payments made reduce AP
        $netAP = max(0.0, $totalAP - $totalPaymentsMade);

        // ── Equity (Retained Earnings) ──
        $totalRevenue = $totalAR;
        $totalExpenses = $totalAP;
        $retainedEarnings = $totalRevenue - $totalExpenses;

        // ── Build Report Sections ──
        $sections = [];

        // ASSETS
        $assetLines = [];

        // Cash & Cash Equivalents group
        foreach ($cashItems as $item) {
            $assetLines[] = [
                'label'  => $item['name'],
                'detail' => $item['bank_name'],
                'amount' => $item['balance'],
            ];
        }

        // Add opening balance subtotal if multiple banks
        if (count($cashItems) > 1) {
            $assetLines[] = [
                'label'  => 'Total Bank Opening Balances',
                'detail' => '',
                'amount' => $totalBankOpeningBalances,
                'is_sub' => true,
            ];
        }

        $assetLines[] = [
            'label'  => 'Net Cash Movement (Receipts - Payments)',
            'detail' => number_format($totalReceipts, 2) . ' received - ' . number_format($totalPaymentsMade, 2) . ' paid',
            'amount' => $netCashMovement,
        ];

        $assetLines[] = [
            'label'  => 'Total Cash & Cash Equivalents',
            'detail' => '',
            'amount' => $totalCash,
            'is_total' => true,
        ];

        $assetLines[] = [
            'label'  => 'Accounts Receivable',
            'detail' => 'AR invoices outstanding',
            'amount' => $netAR,
        ];

        $totalAssets = $totalCash + $netAR;

        $sections[] = [
            'title'  => 'Assets',
            'groups' => [
                [
                    'name'  => 'Current Assets',
                    'lines' => $assetLines,
                    'total' => $totalAssets,
                ],
            ],
            'total' => $totalAssets,
        ];

        // LIABILITIES
        $liabilityLines = [
            [
                'label'  => 'Accounts Payable',
                'detail' => 'AP invoices outstanding',
                'amount' => $netAP,
            ],
        ];

        $totalLiabilities = $netAP;

        $sections[] = [
            'title'  => 'Liabilities',
            'groups' => [
                [
                    'name'  => 'Current Liabilities',
                    'lines' => $liabilityLines,
                    'total' => $totalLiabilities,
                ],
            ],
            'total' => $totalLiabilities,
        ];

        // EQUITY
        $equityLines = [
            [
                'label'  => 'Retained Earnings',
                'detail' => 'Revenue (' . number_format($totalRevenue, 2) . ') - Expenses (' . number_format($totalExpenses, 2) . ')',
                'amount' => $retainedEarnings,
            ],
        ];

        $totalEquity = $retainedEarnings;

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

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

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
                'is_balanced'                  => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
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
