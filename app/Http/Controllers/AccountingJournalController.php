<?php

namespace App\Http\Controllers;

use App\Models\AccountingJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingJournalController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountingJournal::with(['lines', 'office', 'creator'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('entry_no', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $entries = $request->has('limit') ? $query->paginate($request->input('limit', 15)) : $query->get();
        return response()->json($entries);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'nullable|date',
            'description' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'office_id' => 'nullable|exists:offices,id',
            'status' => 'nullable|string|max:50',
            'lines' => 'nullable|array',
            'lines.*.account_id' => 'nullable|integer',
            'lines.*.debit' => 'nullable|numeric',
            'lines.*.credit' => 'nullable|numeric',
            'lines.*.description' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $data = $validated;
            if (empty($data['entry_no'])) {
                $data['entry_no'] = AccountingJournal::generateEntryNo();
            }
            if (empty($data['entry_date'])) {
                $data['entry_date'] = date('Y-m-d');
            }
            if (empty($data['status'])) {
                $data['status'] = 'DRAFT';
            }
            $data['created_by'] = auth()->id() ?? 1;

            $journal = AccountingJournal::create($data);

            if (!empty($validated['lines'])) {
                foreach ($validated['lines'] as $line) {
                    $journal->lines()->create($line);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Journal entry created successfully.',
                'data' => $journal->load(['lines', 'office'])
            ], 201);
        });
    }

    public function show($id)
    {
        $journal = AccountingJournal::with(['lines', 'office', 'creator'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $journal
        ]);
    }

    public function update(Request $request, $id)
    {
        $journal = AccountingJournal::findOrFail($id);

        $validated = $request->validate([
            'entry_date' => 'nullable|date',
            'description' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'office_id' => 'nullable|exists:offices,id',
            'status' => 'nullable|string|max:50',
            'lines' => 'nullable|array',
            'lines.*.account_id' => 'nullable|integer',
            'lines.*.debit' => 'nullable|numeric',
            'lines.*.credit' => 'nullable|numeric',
            'lines.*.description' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($journal, $validated) {
            $journal->update($validated);

            if (isset($validated['lines']) && is_array($validated['lines'])) {
                $journal->lines()->delete();
                foreach ($validated['lines'] as $line) {
                    $journal->lines()->create($line);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Journal entry updated successfully.',
                'data' => $journal->load(['lines', 'office'])
            ]);
        });
    }

    public function destroy($id)
    {
        $journal = AccountingJournal::findOrFail($id);
        $journal->lines()->delete();
        $journal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Journal entry deleted successfully.'
        ]);
    }
}
