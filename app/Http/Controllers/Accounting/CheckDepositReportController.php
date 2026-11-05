<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPayment;
use App\Models\Office;
use App\Models\TradePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckDepositReportController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $bankNames = DB::table('banks')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->pluck('bank_name')
            ->merge(AccountingPayment::whereNotNull('bank_name')->where('bank_name', '!=', '')->distinct()->pluck('bank_name'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $offices = Office::where('is_active', true)->orderBy('code')->get();
        $vendors = TradePartner::orderBy('name')->get();

        return view('accounting.bank-check-deposit-report', compact('bankNames', 'offices', 'vendors'));
    }

    private function getReportData(Request $request)
    {
        $reportTypeChecks = $request->boolean('report_type_checks', true);
        $reportTypeDeposits = $request->boolean('report_type_deposits', true);
        $officeId = $request->input('office_id');
        $vendorId = $request->input('vendor_id');
        $paymentType = $request->input('payment_type', 'ALL');
        $periodType = $request->input('period_type', 'post_date');
        $periodDate = $request->input('period_date', date('Y-m-d'));
        $asOfToday = $request->boolean('as_of_today', false);
        $depositClearOnly = $request->boolean('deposit_clear_only', false);
        $summarySort = $request->input('summary_sort', 'bank');
        $detailSort = $request->input('detail_sort', 'date');
        $showRemark = $request->boolean('show_remark', false);
        $showDetail = $request->boolean('show_detail', false);
        $perPage = (int) $request->input('per_page', 25);
        $page = (int) $request->input('page', 1);

        $effectiveDate = $asOfToday ? date('Y-m-d') : $periodDate;

        $dateColumn = $periodType === 'bank_date' ? 'clear_date' : 'payment_date';

        $query = AccountingPayment::whereNotNull('bank_name');

        if ($reportTypeChecks && !$reportTypeDeposits) {
            $query->where('type', 'MADE');
        } elseif ($reportTypeDeposits && !$reportTypeChecks) {
            $query->where('type', 'RECEIVED');
        }

        if ($officeId) {
            $query->where('office_id', $officeId);
        }

        if ($vendorId) {
            $query->where('trade_partner_id', $vendorId);
        }

        if ($paymentType !== 'ALL') {
            $query->where('payment_method', $paymentType);
        }

        if ($effectiveDate) {
            $query->where($dateColumn, '<=', $effectiveDate);
        }

        if ($depositClearOnly) {
            $query->whereNotNull('clear_date');
        }

        $query->with(['tradePartner', 'currency', 'office']);

        $payments = $query->get();

        $grouped = $payments->groupBy('bank_name');

        $summaryRows = [];
        $grandTotalRecords = 0;
        $grandTotalDeposit = 0;
        $grandTotalCheckPaid = 0;
        $grandTotalNet = 0;

        foreach ($grouped as $bankName => $bankPayments) {
            $deposits = $bankPayments->where('type', 'RECEIVED');
            $checks = $bankPayments->where('type', 'MADE');
            $depositSum = round($deposits->sum('amount'), 2);
            $checkPaidSum = round($checks->sum('amount'), 2);
            $net = round($depositSum - $checkPaidSum, 2);
            $recordCount = $bankPayments->count();

            $summaryRows[] = [
                'bank_name' => $bankName,
                'record_count' => $recordCount,
                'deposit' => $depositSum,
                'check_paid' => $checkPaidSum,
                'total' => $net,
            ];

            $grandTotalRecords += $recordCount;
            $grandTotalDeposit += $depositSum;
            $grandTotalCheckPaid += $checkPaidSum;
            $grandTotalNet += $net;
        }

        $sortKeys = ['bank' => 'bank_name', 'date' => 'bank_name', 'vendor_customer' => 'bank_name'];
        $sortCol = $sortKeys[$summarySort] ?? 'bank_name';
        usort($summaryRows, function ($a, $b) use ($sortCol) {
            return strcasecmp($a[$sortCol] ?? '', $b[$sortCol] ?? '');
        });

        $totalRows = count($summaryRows);
        $offset = ($page - 1) * $perPage;
        $paginatedRows = array_slice($summaryRows, $offset, $perPage);
        $totalPages = (int) ceil($totalRows / $perPage);

        $detailRows = [];
        if ($showDetail) {
            $detailQuery = $payments;
            if ($detailSort === 'check_no') {
                $detailQuery = $detailQuery->sortBy('check_no');
            } elseif ($detailSort === 'amount') {
                $detailQuery = $detailQuery->sortByDesc('amount');
            } else {
                $detailQuery = $detailQuery->sortBy('payment_date');
            }

            foreach ($detailQuery as $p) {
                $detailRows[] = [
                    'id' => $p->id,
                    'payment_no' => $p->payment_no,
                    'payment_date' => $p->payment_date?->format('Y-m-d') ?? '--',
                    'check_no' => $p->check_no ?? '--',
                    'party_name' => $p->tradePartner?->name ?? 'N/A',
                    'reference_no' => $p->reference_no ?? '--',
                    'currency' => $p->currency?->code ?? 'USD',
                    'amount' => round((float) $p->amount, 2),
                    'office' => $p->office?->code ?? 'N/A',
                    'clear_date' => $p->clear_date?->format('Y-m-d') ?? '--',
                    'void_date' => $p->void_date?->format('Y-m-d') ?? '--',
                    'payment_method' => $p->payment_method ?? '--',
                    'remark' => $p->remark ?? '',
                    'bank_name' => $p->bank_name ?? '--',
                    'type' => $p->type,
                    'status' => $p->void_date ? 'Void' : ($p->clear_date ? 'Cleared' : 'Outstanding'),
                    'color' => $p->color ?? '',
                ];
            }
        }

        return [
            'summary_rows' => $paginatedRows,
            'detail_rows' => $detailRows,
            'grand_total' => [
                'record_count' => $grandTotalRecords,
                'deposit' => round($grandTotalDeposit, 2),
                'check_paid' => round($grandTotalCheckPaid, 2),
                'total' => round($grandTotalNet, 2),
            ],
            'total_rows' => $totalRows,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage,
            'show_detail' => $showDetail,
            'show_remark' => $showRemark,
            'summary_sort' => $summarySort,
            'detail_sort' => $detailSort,
            'period_type' => $periodType,
            'effective_date' => $effectiveDate,
        ];
    }

    public function view(Request $request)
    {
        $data = $this->getReportData($request);
        $data['success'] = true;

        return response()->json($data);
    }

    public function printReport(Request $request)
    {
        $request->merge(['show_detail' => true, 'per_page' => 9999]);
        $data = $this->getReportData($request);

        return view('accounting.bank-check-deposit-report-print', $data);
    }

    public function exportExcel(Request $request)
    {
        $request->merge(['per_page' => 9999]);
        $data = $this->getReportData($request);

        $filename = 'check-deposit-report-' . now()->format('Ymd-His') . '.csv';

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Check/Deposit Report']);
            fputcsv($handle, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
            fwrite($handle, "\n");

            fputcsv($handle, ['Bank Name', 'Record(s)', 'Deposit', 'Check Paid', 'Total']);
            foreach ($data['summary_rows'] as $row) {
                fputcsv($handle, [
                    $row['bank_name'],
                    $row['record_count'],
                    number_format($row['deposit'], 2),
                    number_format($row['check_paid'], 2),
                    number_format($row['total'], 2),
                ]);
            }
            fputcsv($handle, [
                'Grand Total',
                $data['grand_total']['record_count'],
                number_format($data['grand_total']['deposit'], 2),
                number_format($data['grand_total']['check_paid'], 2),
                number_format($data['grand_total']['total'], 2),
            ]);

            if (!empty($data['detail_rows'])) {
                fwrite($handle, "\n");
                fputcsv($handle, ['DETAIL']);
                fputcsv($handle, ['Payment No.', 'Date', 'Check No.', 'Party', 'Ref No.', 'Currency', 'Amount', 'Office', 'Clear Date', 'Status']);
                foreach ($data['detail_rows'] as $row) {
                    fputcsv($handle, [
                        $row['payment_no'], $row['payment_date'], $row['check_no'],
                        $row['party_name'], $row['reference_no'], $row['currency'],
                        number_format($row['amount'], 2), $row['office'],
                        $row['clear_date'], $row['status'],
                    ]);
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
