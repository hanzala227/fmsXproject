<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\AccountingPayment;
use Illuminate\Http\Request;

class BankOutstandingController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()?->company_id;
        $offices = Office::where('is_active', true)->orderBy('code')->get();
        $bankNames = AccountingPayment::whereNull('clear_date')
            ->whereNull('void_date')
            ->whereNotNull('bank_name')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->distinct()
            ->pluck('bank_name')
            ->sort()
            ->values();

        return view('accounting.bank-outstanding', compact('offices', 'bankNames'));
    }

    private function getReportData(Request $request)
    {
        $companyId = auth()->user()?->company_id;
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $officeId = $request->input('office_id');
        $groupByOffice = filter_var($request->input('group_by_office', false), FILTER_VALIDATE_BOOLEAN);

        $query = AccountingPayment::whereNull('clear_date')
            ->whereNull('void_date')
            ->where('payment_date', '<=', $asOfDate)
            ->whereNotNull('bank_name')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        if ($officeId) {
            $query->where('office_id', $officeId);
        }

        $payments = $query->with(['office', 'currency', 'bankCurrency'])->get();

        $grouped = $payments->groupBy(function ($p) use ($groupByOffice) {
            $key = $p->bank_name;
            if ($groupByOffice) {
                $key .= '||' . ($p->office?->code ?? 'N/A');
            }
            return $key;
        });

        $rows = [];
        $grandTotals = ['check_received' => 0, 'check_paid' => 0, 'total' => 0];

        foreach ($grouped as $key => $items) {
            if ($groupByOffice) {
                [$bankName, $officeCode] = explode('||', $key);
            } else {
                $bankName = $key;
                $officeCode = null;
            }

            $received = $items->where('type', 'RECEIVED')->sum('amount');
            $paid = $items->where('type', 'MADE')->sum('amount');
            $total = $received - $paid;
            $currencyCode = $items->first()->bankCurrency?->code
                ?? $items->first()->currency?->code
                ?? 'USD';

            $rows[] = [
                'bank_name' => $bankName,
                'office' => $officeCode,
                'currency' => $currencyCode,
                'check_received' => round($received, 2),
                'check_paid' => round($paid, 2),
                'total' => round($total, 2),
            ];

            $grandTotals['check_received'] += $received;
            $grandTotals['check_paid'] += $paid;
            $grandTotals['total'] += $total;
        }

        $grandTotals['check_received'] = round($grandTotals['check_received'], 2);
        $grandTotals['check_paid'] = round($grandTotals['check_paid'], 2);
        $grandTotals['total'] = round($grandTotals['total'], 2);

        return [
            'rows' => $rows,
            'totals' => $grandTotals,
            'as_of_date' => $asOfDate,
            'group_by_office' => $groupByOffice,
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
        $data = $this->getReportData($request);

        return view('accounting.bank-outstanding-print', [
            'rows' => $data['rows'],
            'totals' => $data['totals'],
            'asOfDate' => $data['as_of_date'],
            'groupByOffice' => $data['group_by_office'],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);
        $rows = $data['rows'];
        $totals = $data['totals'];
        $asOfDate = $data['as_of_date'];

        $filename = 'bank-outstanding-' . $asOfDate . '-' . now()->format('His') . '.csv';

        return response()->stream(function () use ($rows, $totals, $asOfDate) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Bank Outstanding Report - As of ' . $asOfDate]);
            fwrite($handle, "\n");
            fputcsv($handle, [
                'Bank Name', 'Office', 'Currency',
                'Check Received', 'Check Paid', 'Total',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['bank_name'],
                    $row['office'] ?? '',
                    $row['currency'],
                    number_format($row['check_received'], 2),
                    number_format($row['check_paid'], 2),
                    number_format($row['total'], 2),
                ]);
            }

            fwrite($handle, "\n");
            fputcsv($handle, [
                'GRAND TOTAL', '', '',
                number_format($totals['check_received'] ?? 0, 2),
                number_format($totals['check_paid'] ?? 0, 2),
                number_format($totals['total'] ?? 0, 2),
            ]);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
