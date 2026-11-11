<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingBlockHistory;
use App\Models\Office;
use Illuminate\Http\Request;

class AccountingBlockHistoryController extends Controller
{
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('accounting.accounting-block-history', compact('offices'));
    }

    public function search(Request $request)
    {
        $companyId = auth()->user()->company_id ?? null;
        $query = AccountingBlockHistory::when($companyId, fn($q) => $q->whereHas('executor', fn($e) => $e->where('company_id', $companyId)))
            ->with(['office', 'executor']);

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->execute_date) {
            $query->whereDate('executed_at', $request->execute_date);
        }

        if ($request->program && $request->program !== 'all') {
            $query->where('program', $request->program);
        }

        $perPage = $request->per_page ?? 25;
        $results = $query->orderByDesc('executed_at')->paginate($perPage);

        $formatted = $results->getCollection()->map(function ($r) {
            return [
                'id' => $r->id,
                'program' => $r->program,
                'is_blocked' => $r->is_blocked,
                'block_type' => $r->block_type,
                'block_date' => $r->block_date?->format('m-d-Y') ?? '',
                'ref_no' => $r->ref_no ?? '',
                'office' => $r->office?->code ?? '',
                'execute_by' => $r->executor ? $r->executor->name . ' (' . $r->executor->email . ')' : '',
                'executed_at' => $r->executed_at?->format('m-d-Y H:i') ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'total' => $results->total(),
            'current_page' => $results->currentPage(),
            'last_page' => $results->lastPage(),
            'per_page' => $results->perPage(),
            'from' => $results->firstItem(),
            'to' => $results->lastItem(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $request->merge(['per_page' => 99999]);
        $response = $this->search($request);
        $data = json_decode($response->getContent(), true);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="accounting_block_history.csv"',
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Program', 'Blocked', 'Block Type', 'Block Date', 'Ref No.', 'Office', 'Execute By', 'Date Execute']);
            foreach ($data['data'] ?? [] as $row) {
                fputcsv($handle, [
                    $row['program'] ?? '',
                    ($row['is_blocked'] ?? false) ? 'Yes' : 'No',
                    $row['block_type'] ?? '',
                    $row['block_date'] ?? '',
                    $row['ref_no'] ?? '',
                    $row['office'] ?? '',
                    $row['execute_by'] ?? '',
                    $row['executed_at'] ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
