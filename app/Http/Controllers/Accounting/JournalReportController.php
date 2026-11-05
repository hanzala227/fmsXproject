<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\JournalEntryLine;
use App\Models\AccountingJournal;
use App\Models\GlAccount;
use App\Models\TradePartner;
use App\Models\Currency;
use Illuminate\Http\Request;

class JournalReportController extends Controller
{
    public function index()
    {
        $offices      = Office::where('is_active', true)->orderBy('name')->get();
        $currencies   = Currency::orderBy('code')->get();
        $glAccounts   = GlAccount::active()->orderBy('code')->get(['id', 'code', 'name']);
        $partners     = TradePartner::where('status', '!=', 'INACTIVE')->orderBy('name')->limit(100)->get(['id', 'name', 'code']);
        $nextEntryNo  = AccountingJournal::generateEntryNo();

        return view('accounting.journal-report', compact('offices', 'currencies', 'glAccounts', 'partners', 'nextEntryNo'));
    }

    private function getReportData(Request $request)
    {
        $startDate  = $request->input('start_date', $request->input('period_from', date('Y-m-d', strtotime('first day of this month'))));
        $endDate    = $request->input('end_date', $request->input('period_to', date('Y-m-d')));
        $officeId   = $request->input('office_id');
        $glFrom     = $request->input('gl_from');
        $glTo       = $request->input('gl_to');
        $currencyId = $request->input('currency_id');
        $search     = $request->input('search');

        if ($startDate && $endDate && $startDate > $endDate) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }

        $query = JournalEntryLine::with([
                'journalEntry',
                'glAccount',
                'tradePartner',
                'office',
                'currency',
            ])
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('entry_date', [$startDate, $endDate])
                  ->whereNotIn('status', ['VOIDED', 'VOID', 'CANCELLED']);
            })
            ->when($officeId, function ($q) use ($officeId) {
                $q->where(function ($sq) use ($officeId) {
                    $sq->where('office_id', $officeId)
                      ->orWhereHas('journalEntry', fn($je) => $je->where('office_id', $officeId));
                });
            })
            ->when($currencyId, fn($q) => $q->where('currency_id', $currencyId))
            ->when($glFrom, function ($q) use ($glFrom) {
                $q->whereHas('glAccount', fn($g) => $g->where('code', '>=', $glFrom));
            })
            ->when($glTo, function ($q) use ($glTo) {
                $q->whereHas('glAccount', fn($g) => $g->where('code', '<=', $glTo));
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('description', 'LIKE', "%{$search}%")
                      ->orWhereHas('journalEntry', fn($je) => $je->where('entry_no', 'LIKE', "%{$search}%")->orWhere('description', 'LIKE', "%{$search}%"))
                      ->orWhereHas('tradePartner', fn($tp) => $tp->where('name', 'LIKE', "%{$search}%"))
                      ->orWhereHas('glAccount', fn($g) => $g->where('code', 'LIKE', "%{$search}%")->orWhere('name', 'LIKE', "%{$search}%"));
                });
            })
            ->orderBy('journal_entry_id')
            ->orderBy('line_no');

        $lines = $query->get();

        $results = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $je = $line->journalEntry;
            $foreignAmount = $line->foreign_debit ?: $line->foreign_credit;
            $results[] = [
                'id' => $line->id,
                'date' => $je?->entry_date?->format('Y-m-d') ?? '',
                'date_formatted' => $je?->entry_date?->format('m-d-Y') ?? '',
                'gl_no' => $line->glAccount?->code ?? '',
                'gl_desc' => $line->glAccount?->name ?? '',
                'source' => $je?->source ?? 'Manual',
                'ref_no' => $je?->entry_no ?? '',
                'office' => $line->office?->name ?? $je?->office?->name ?? '',
                'company' => $line->tradePartner?->name ?? '',
                'description' => $line->description ?? $je?->description ?? '',
                'debit' => (float) $line->local_debit,
                'credit' => (float) $line->local_credit,
                'foreign_amount' => (float) $foreignAmount,
                'cur' => $line->currency?->code ?? 'USD',
                'rate' => (float) ($line->foreign_rate ?? 1),
            ];
            $totalDebit += (float) $line->local_debit;
            $totalCredit += (float) $line->local_credit;
        }

        return [$results, $startDate, $endDate, round($totalDebit, 2), round($totalCredit, 2)];
    }

    public function apiView(Request $request)
    {
        [$results, $startDate, $endDate, $totalDebit, $totalCredit] = $this->getReportData($request);

        return response()->json([
            'success' => true,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'total_records' => count($results),
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            'results' => $results,
        ]);
    }

    public function preview(Request $request)
    {
        [$results, $startDate, $endDate, $totalDebit, $totalCredit] = $this->getReportData($request);
        return view('accounting.journal-report-preview', compact(
            'results', 'startDate', 'endDate', 'totalDebit', 'totalCredit'
        ) + ['printMode' => false]);
    }

    public function printReport(Request $request)
    {
        [$results, $startDate, $endDate, $totalDebit, $totalCredit] = $this->getReportData($request);
        return view('accounting.journal-report-preview', compact(
            'results', 'startDate', 'endDate', 'totalDebit', 'totalCredit'
        ) + ['printMode' => true]);
    }

    public function exportExcel(Request $request)
    {
        [$results, $startDate, $endDate, $totalDebit, $totalCredit] = $this->getReportData($request);

        $filename = 'journal-report-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($results, $startDate, $endDate, $totalDebit, $totalCredit) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Journal Report']);
            fputcsv($handle, ['Period', $startDate, '~', $endDate]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Date', 'G/L No.', 'G/L Desc.', 'Source', 'Ref. No.',
                'Office', 'Company', 'Description',
                'Debit', 'Credit', 'Foreign Amount', 'Cur', 'Rate',
            ]);

            foreach ($results as $row) {
                fputcsv($handle, [
                    $row['date_formatted'],
                    $row['gl_no'],
                    $row['gl_desc'],
                    $row['source'],
                    $row['ref_no'],
                    $row['office'],
                    $row['company'],
                    $row['description'],
                    $row['debit'] ? number_format($row['debit'], 2) : '0.00',
                    $row['credit'] ? number_format($row['credit'], 2) : '0.00',
                    $row['foreign_amount'] ? number_format($row['foreign_amount'], 2) : '',
                    $row['cur'],
                    $row['rate'] != 1 ? number_format($row['rate'], 6) : '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL', '', '', '', '',
                '', '', '',
                number_format($totalDebit, 2),
                number_format($totalCredit, 2),
                '', '', '',
            ]);
            fputcsv($handle, [count($results) . ' Record(s)', '', '', '', '', '', '', '', '', '', '', '', '']);

            fclose($handle);
        }, 200, $headers);
    }
}
