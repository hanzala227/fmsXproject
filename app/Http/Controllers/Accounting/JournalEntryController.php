<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingJournal;
use App\Models\JournalEntryLine;
use App\Models\GlAccount;
use App\Models\Office;
use App\Models\TradePartner;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $companyId    = auth()->user()->company_id ?? null;
        $offices      = Office::where('is_active', true)->orderBy('name')->get();
        $currencies   = Currency::orderBy('code')->get();
        $partners     = TradePartner::when($companyId, fn($q) => $q->where('company_id', $companyId))->where('status', '!=', 'INACTIVE')->orderBy('name')->get();
        $glAccounts   = GlAccount::active()->orderBy('code')->get(['id', 'code', 'name']);
        $nextEntryNo  = AccountingJournal::generateEntryNo();

        return view('accounting.journal-entry', compact('offices', 'currencies', 'partners', 'glAccounts', 'nextEntryNo'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entry_id'      => 'nullable|exists:journal_entries,id',
            'entry_date'    => 'required|date',
            'description'   => 'nullable|string',
            'remark'        => 'nullable|string',
            'office_id'     => 'nullable|exists:offices,id',
            'lines'         => 'required|array|min:1',
            'lines.*.gl_account_id'    => 'required|exists:gl_accounts,id',
            'lines.*.sub'               => 'nullable|string|max:50',
            'lines.*.entity_type'       => 'nullable|in:COMPANY,BANK',
            'lines.*.trade_partner_id'  => 'nullable|exists:trade_partners,id',
            'lines.*.description'       => 'nullable|string',
            'lines.*.office_id'         => 'nullable|exists:offices,id',
            'lines.*.local_debit'       => 'nullable|numeric|min:0',
            'lines.*.local_credit'      => 'nullable|numeric|min:0',
            'lines.*.currency_id'       => 'nullable|exists:currencies,id',
            'lines.*.foreign_rate'      => 'nullable|numeric|min:0',
            'lines.*.foreign_debit'     => 'nullable|numeric|min:0',
            'lines.*.foreign_credit'    => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $companyId = auth()->user()->company_id ?? null;
        if ($request->filled('entry_id')) {
            $existing = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))->find($request->entry_id);
            if (!$existing) {
                return response()->json(['success' => false, 'message' => 'Journal entry not found.'], 404);
            }
            if ($existing->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($existing->entry_date, $existing->office_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Control: This journal entry cannot be updated because the record or accounting period is blocked.'
                ], 403);
            }
        }
        if (\App\Models\AccountingBlockDate::isDateBlocked($request->entry_date, $request->office_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access Control: Cannot post journal transactions to a blocked accounting period.'
            ], 403);
        }

        $totalDebit  = (float) collect($request->lines)->sum('local_debit');
        $totalCredit = (float) collect($request->lines)->sum('local_credit');

        if ($totalDebit <= 0.001 || $totalCredit <= 0.001) {
            return response()->json(['success' => false, 'message' => 'Journal entry lines must have positive debit and credit amounts.'], 422);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json(['success' => false, 'message' => 'Total debit must equal total credit.'], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->filled('entry_id')) {
                $entry = AccountingJournal::when($companyId, fn($q) => $q->where('company_id', $companyId))->findOrFail($request->entry_id);
                if ($entry->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($entry->entry_date, $entry->office_id)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Access Control: This journal entry cannot be edited because the record or original accounting period is blocked.'
                    ], 403);
                }
                $entry->update([
                    'entry_date'  => $request->entry_date,
                    'description' => $request->description,
                    'remark'      => $request->remark,
                    'office_id'   => $request->office_id,
                ]);

                // Delete existing lines and re-create
                $entry->lines()->delete();
            } else {
                $entry = AccountingJournal::create([
                    'company_id'  => $companyId,
                    'entry_no'    => AccountingJournal::generateEntryNo(),
                    'entry_date'  => $request->entry_date,
                    'description' => $request->description,
                    'remark'      => $request->remark,
                    'office_id'   => $request->office_id,
                    'created_by'  => auth()->id(),
                    'status'      => 'POSTED',
                ]);
            }

            foreach ($request->lines as $idx => $line) {
                $entry->lines()->create([
                    'company_id'       => $entry->company_id ?? $companyId,
                    'line_no'          => $idx + 1,
                    'gl_account_id'    => $line['gl_account_id'],
                    'sub'              => $line['sub'] ?? null,
                    'entity_type'      => $line['entity_type'] ?? 'COMPANY',
                    'trade_partner_id' => $line['trade_partner_id'] ?? null,
                    'description'      => $line['description'] ?? null,
                    'office_id'        => $line['office_id'] ?? $request->office_id,
                    'local_debit'      => $line['local_debit'] ?? 0,
                    'local_credit'     => $line['local_credit'] ?? 0,
                    'currency_id'      => $line['currency_id'] ?? null,
                    'foreign_rate'     => $line['foreign_rate'] ?? 1,
                    'foreign_debit'    => $line['foreign_debit'] ?? 0,
                    'foreign_credit'   => $line['foreign_credit'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Journal entry ' . ($request->filled('entry_id') ? 'updated' : 'saved') . ' successfully.',
                'entry_id' => $entry->id,
                'entry_no' => $entry->entry_no,
                'next_entry_no' => AccountingJournal::generateEntryNo(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error saving journal entry: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->merge(['entry_id' => $id]);
        return $this->store($request);
    }

    public function destroy($id)
    {
        try {
            $companyId = auth()->user()->company_id ?? null;
            $entry = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))->findOrFail($id);
            if ($entry->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($entry->entry_date, $entry->office_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Control: This journal entry cannot be deleted because the record or accounting period is blocked.'
                ], 403);
            }

            DB::beginTransaction();
            $entry->lines()->delete();
            $entry->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Journal Entry deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Journal Entry: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getGlAccounts(Request $request)
    {
        $term = $request->input('q', '');
        $accounts = GlAccount::active()
            ->when($term, fn($q) => $q->search($term))
            ->orderBy('code')
            ->limit(50)
            ->get(['id', 'code', 'name', 'type']);

        return response()->json($accounts);
    }

    public function getNextEntryNo()
    {
        return response()->json(['entry_no' => AccountingJournal::generateEntryNo()]);
    }

    public function list(Request $request)
    {
        $companyId = auth()->user()->company_id ?? null;
        $query = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->with(['office', 'creator', 'lines'])
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($qq) use ($term) {
                    $qq->where('entry_no', 'LIKE', "%{$term}%")
                       ->orWhere('description', 'LIKE', "%{$term}%")
                       ->orWhere('remark', 'LIKE', "%{$term}%");
                });
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->office_id, fn($q) => $q->where('office_id', $request->office_id))
            ->when($request->from_date, fn($q) => $q->where('entry_date', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->where('entry_date', '<=', $request->to_date))
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        $entries = $query->paginate($request->per_page ?? 25);

        $data = $entries->through(function ($entry) {
            $totalDebit = $entry->lines->sum('local_debit');
            $totalCredit = $entry->lines->sum('local_credit');
            return [
                'id' => $entry->id,
                'entry_no' => $entry->entry_no,
                'entry_date' => $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '',
                'description' => $entry->description,
                'remark' => $entry->remark,
                'office_name' => $entry->office ? ($entry->office->code ?? $entry->office->name) : 'N/A',
                'status' => $entry->status ?? 'POSTED',
                'total_debit' => (float)$totalDebit,
                'total_credit' => (float)$totalCredit,
                'lines_count' => $entry->lines->count(),
                'creator_name' => $entry->creator ? $entry->creator->name : 'N/A',
                'created_at' => $entry->created_at ? $entry->created_at->format('Y-m-d H:i') : '',
            ];
        });

        return response()->json($data);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && !$request->ajax()) {
            return redirect()->route('accounting.journal.entry', ['id' => $id]);
        }

        $companyId = auth()->user()->company_id ?? null;
        $entry = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->with(['lines.glAccount', 'lines.tradePartner', 'lines.office', 'lines.currency', 'office', 'creator'])
            ->findOrFail($id);

        return response()->json($entry);
    }

    public function exportExcel(Request $request)
    {
        $companyId = auth()->user()->company_id ?? null;
        $query = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->with(['office', 'creator', 'lines.glAccount', 'lines.tradePartner']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($qq) use ($term) {
                $qq->where('entry_no', 'LIKE', "%{$term}%")
                   ->orWhere('description', 'LIKE', "%{$term}%")
                   ->orWhere('remark', 'LIKE', "%{$term}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('entry_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('entry_date', '<=', $request->to_date);
        }

        $entries = $query->orderByDesc('entry_date')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="journal-entries-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($entries) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Entry No', 'Entry Date', 'Office', 'Status', 'Line No', 'GL Code', 'GL Name', 'Description', 'Local Debit', 'Local Credit', 'Entity', 'Partner', 'Remark']);

            foreach ($entries as $entry) {
                if ($entry->lines->isEmpty()) {
                    fputcsv($file, [
                        $entry->entry_no,
                        $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '',
                        $entry->office ? $entry->office->name : '',
                        $entry->status,
                        '-', '-', '-',
                        $entry->description,
                        0.00, 0.00,
                        '-', '-',
                        $entry->remark
                    ]);
                } else {
                    foreach ($entry->lines as $line) {
                        fputcsv($file, [
                            $entry->entry_no,
                            $entry->entry_date ? $entry->entry_date->format('Y-m-d') : '',
                            $entry->office ? $entry->office->name : '',
                            $entry->status,
                            $line->line_no,
                            $line->glAccount ? $line->glAccount->code : '',
                            $line->glAccount ? $line->glAccount->name : '',
                            $line->description ?? $entry->description,
                            $line->local_debit,
                            $line->local_credit,
                            $line->entity_type,
                            $line->tradePartner ? $line->tradePartner->name : '',
                            $entry->remark
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

