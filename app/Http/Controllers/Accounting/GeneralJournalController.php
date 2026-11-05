<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingJournal;
use App\Models\Office;
use Illuminate\Http\Request;

class GeneralJournalController extends Controller
{
    public function index(Request $request)
    {
        // If AJAX request for grid update, return JSON
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return $this->ajaxGrid($request);
        }

        $query = AccountingJournal::with(['office', 'creator', 'lines']);
        $query = $this->applyFilters($query, $request);
        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(25);

        $offices = Office::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return view('accounting.general-journal', compact('entries', 'offices'));
    }

    private function ajaxGrid(Request $request)
    {
        $query = AccountingJournal::with(['office', 'creator', 'lines']);
        $query = $this->applyFilters($query, $request);
        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(25);

        $rows = view('accounting.partials.general-journal-rows', compact('entries'))->render();
        $pagination = $entries->appends($request->query())->links('vendor.pagination.custom')->render();

        return response()->json([
            'success'    => true,
            'html'       => $rows,
            'pagination' => $pagination,
            'first'      => $entries->firstItem() ?? 0,
            'last'       => $entries->lastItem() ?? 0,
            'total'      => $entries->total(),
        ]);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('entry_no', 'LIKE', "%{$term}%")
                  ->orWhere('remark', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }
        if ($request->filled('from_date')) {
            $query->where('entry_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('entry_date', '<=', $request->to_date);
        }
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        return $query;
    }

    public function printReport(Request $request)
    {
        $data = $this->getEntries($request);
        return view('accounting.general-journal-print', $data);
    }

    public function exportExcel(Request $request)
    {
        $data    = $this->getEntries($request);
        $entries = $data['entries'];

        $filename = 'general-journal-' . now()->format('Y-m-d-His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['General Journal Report']);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Post Date', 'Entry No', 'Seq', 'Remark / Description', 'Total Debit', 'Total Credit', 'Status', 'Issued By', 'Office']);

            foreach ($entries as $e) {
                $totalDebit  = $e->lines->sum('local_debit');
                $totalCredit = $e->lines->sum('local_credit');
                fputcsv($handle, [
                    $e->entry_date?->format('Y-m-d'),
                    $e->entry_no,
                    $e->id,
                    $e->remark ?? $e->description ?? '',
                    number_format($totalDebit, 2),
                    number_format($totalCredit, 2),
                    $e->status ?? 'POSTED',
                    $e->creator?->name ?? '',
                    $e->office?->code ?? $e->office?->name ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function destroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No entries selected.']);
        }

        $entries = AccountingJournal::whereIn('id', $ids)->get();
        foreach ($entries as $entry) {
            $status = strtoupper($entry->status ?? 'POSTED');
            if (in_array($status, ['POSTED', 'APPROVED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document Control: Posted or approved journal entries cannot be deleted. Reversal or cancellation is required.'
                ], 403);
            }
        }

        foreach ($entries as $entry) {
            $entry->lines()->delete();
            $entry->delete();
        }

        return response()->json(['success' => true, 'message' => count($ids) . ' entry(ies) deleted.']);
    }

    private function getEntries(Request $request)
    {
        $query = AccountingJournal::with(['office', 'creator', 'lines']);
        $query = $this->applyFilters($query, $request);
        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->get();
        return compact('entries');
    }
}
