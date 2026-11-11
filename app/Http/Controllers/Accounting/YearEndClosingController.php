<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\YearEndClosing;
use App\Models\AccountingJournal;
use App\Models\JournalEntryLine;
use App\Models\GlAccount;
use App\Models\Office;
use Illuminate\Http\Request;

class YearEndClosingController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id ?? null;
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $changeLog = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->with(['office', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        $lastClosing = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('action', 'CLOSE')
            ->orderByDesc('fiscal_year')
            ->first();

        $lastCancelled = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('action', 'CANCEL')
            ->orderByDesc('fiscal_year')
            ->first();

        $currentYear = (int) date('Y');
        $lastClosedYear = (int) ($lastClosing?->fiscal_year ?? 0);
        $nextYear = $lastClosedYear >= $currentYear ? $lastClosedYear + 1 : ($lastClosedYear > 0 ? $lastClosedYear + 1 : $currentYear);

        $canPerform = $nextYear <= $currentYear;
        $canCancel = $lastClosing && (!$lastCancelled || $lastCancelled->fiscal_year < $lastClosing->fiscal_year);

        $performYear = $canPerform ? $nextYear : $currentYear;
        $cancelYear = $lastClosing?->fiscal_year ?? null;
        $closingDate = $cancelYear ? date('Y-m-d', strtotime("{$cancelYear}-12-31")) : date('Y-m-d', strtotime("{$performYear}-12-31"));

        return view('accounting.year-end-closing', compact(
            'offices', 'changeLog', 'lastClosing', 'lastCancelled',
            'canPerform', 'canCancel', 'performYear', 'cancelYear', 'closingDate', 'currentYear'
        ));
    }

    public function status(Request $request)
    {
        $companyId = auth()->user()->company_id ?? null;
        $lastClosing = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('action', 'CLOSE')
            ->orderByDesc('fiscal_year')
            ->first();

        $lastCancelled = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('action', 'CANCEL')
            ->orderByDesc('fiscal_year')
            ->first();

        $currentYear = (int) date('Y');
        $lastClosedYear = (int) ($lastClosing?->fiscal_year ?? 0);
        $nextYear = $lastClosedYear >= $currentYear ? $lastClosedYear + 1 : ($lastClosedYear > 0 ? $lastClosedYear + 1 : $currentYear);

        $canPerform = $nextYear <= $currentYear;
        $canCancel = $lastClosing && (!$lastCancelled || $lastCancelled->fiscal_year < $lastClosing->fiscal_year);

        $performYear = $canPerform ? $nextYear : $currentYear;
        $cancelYear = $lastClosing?->fiscal_year ?? null;

        return response()->json([
            'success' => true,
            'can_perform' => $canPerform,
            'can_cancel' => $canCancel,
            'perform_year' => $performYear,
            'cancel_year' => $cancelYear,
            'last_closed_year' => $lastClosedYear,
            'has_closings' => YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))->where('action', 'CLOSE')->exists(),
        ]);
    }

    public function checkUncleared(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
        ]);

        $companyId = auth()->user()->company_id ?? null;
        $year = $request->fiscal_year;
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        $unclearedPayments = \DB::table('payments')
            ->leftJoin('trade_partners', 'payments.trade_partner_id', '=', 'trade_partners.id')
            ->leftJoin('offices', 'payments.office_id', '=', 'offices.id')
            ->when($companyId, fn($q) => $q->where('payments.company_id', $companyId))
            ->where('payments.payment_date', '>=', $startDate)
            ->where('payments.payment_date', '<=', $endDate)
            ->whereNull('payments.clear_date')
            ->whereNull('payments.void_date')
            ->select(
                'payments.id',
                'payments.type as payment_type',
                'payments.payment_date as post_date',
                'trade_partners.name as vendor',
                'payments.check_no',
                'payments.bank_name',
                'payments.amount as paid_amount',
                'payments.clear_date as clear_deposit_date'
            )
            ->get();

        return response()->json([
            'success' => true,
            'uncleared_count' => $unclearedPayments->count(),
            'uncleared_payments' => $unclearedPayments,
        ]);
    }

    public function perform(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
            'force' => 'nullable|boolean',
        ]);

        $companyId = auth()->user()->company_id ?? null;
        $year = $request->fiscal_year;
        $closingDate = "{$year}-12-31";
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        $existing = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('fiscal_year', $year)
            ->where('action', 'CLOSE')
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => "Year {$year} is already closed."]);
        }

        if (!$request->force) {
            $unclearedCount = \DB::table('payments')
                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                ->where('payment_date', '>=', $startDate)
                ->where('payment_date', '<=', $endDate)
                ->whereNull('clear_date')
                ->whereNull('void_date')
                ->count();

            if ($unclearedCount > 0) {
                return response()->json([
                    'success' => false,
                    'has_uncleared' => true,
                    'uncleared_count' => $unclearedCount,
                    'message' => "There are {$unclearedCount} uncleared payment(s). Use force=true to proceed anyway.",
                ]);
            }
        }

        $incomeAccounts = GlAccount::where('type', 'REVENUE')->where('is_active', true)->get();
        $expenseAccounts = GlAccount::where('type', 'EXPENSE')->where('is_active', true)->get();
        $retainedEarnings = GlAccount::where('code', '30101')->first();

        if (!$retainedEarnings) {
            return response()->json(['success' => false, 'message' => 'Retained Earnings account (30101) not found.']);
        }

        $totalRevenue = 0;
        $totalExpense = 0;
        $entriesCreated = 0;

        \DB::beginTransaction();

        try {
            foreach ($incomeAccounts as $account) {
                $balance = $this->getAccountBalance($account->id, $startDate, $endDate);
                if ($balance != 0) {
                    $entryNo = AccountingJournal::generateEntryNo();
                    $entry = AccountingJournal::create([
                        'entry_no' => $entryNo,
                        'entry_date' => $closingDate,
                        'description' => "Year-End Closing - {$account->name} ({$year})",
                        'office_id' => null,
                        'created_by' => auth()->id(),
                        'status' => 'POSTED',
                    ]);

                    if ($balance > 0) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 1,
                            'gl_account_id' => $account->id,
                            'description' => "Close {$account->name}",
                            'local_debit' => $balance,
                            'local_credit' => 0,
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 2,
                            'gl_account_id' => $retainedEarnings->id,
                            'description' => "Transfer to Retained Earnings",
                            'local_debit' => 0,
                            'local_credit' => $balance,
                        ]);
                    } else {
                        $abs = abs($balance);
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 1,
                            'gl_account_id' => $retainedEarnings->id,
                            'description' => "Transfer from Retained Earnings",
                            'local_debit' => $abs,
                            'local_credit' => 0,
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 2,
                            'gl_account_id' => $account->id,
                            'description' => "Close {$account->name}",
                            'local_debit' => 0,
                            'local_credit' => $abs,
                        ]);
                    }

                    $totalRevenue += max(0, $balance);
                    $entriesCreated++;
                }
            }

            foreach ($expenseAccounts as $account) {
                $balance = $this->getAccountBalance($account->id, $startDate, $endDate);
                if ($balance != 0) {
                    $entryNo = AccountingJournal::generateEntryNo();
                    $entry = AccountingJournal::create([
                        'entry_no' => $entryNo,
                        'entry_date' => $closingDate,
                        'description' => "Year-End Closing - {$account->name} ({$year})",
                        'office_id' => null,
                        'created_by' => auth()->id(),
                        'status' => 'POSTED',
                    ]);

                    $abs = abs($balance);
                    if ($balance > 0) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 1,
                            'gl_account_id' => $retainedEarnings->id,
                            'description' => "Transfer from Retained Earnings",
                            'local_debit' => $abs,
                            'local_credit' => 0,
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 2,
                            'gl_account_id' => $account->id,
                            'description' => "Close {$account->name}",
                            'local_debit' => 0,
                            'local_credit' => $abs,
                        ]);
                    } else {
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 1,
                            'gl_account_id' => $account->id,
                            'description' => "Close {$account->name}",
                            'local_debit' => $abs,
                            'local_credit' => 0,
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_no' => 2,
                            'gl_account_id' => $retainedEarnings->id,
                            'description' => "Transfer to Retained Earnings",
                            'local_debit' => 0,
                            'local_credit' => $abs,
                        ]);
                    }

                    $totalExpense += max(0, $balance);
                    $entriesCreated++;
                }
            }

            YearEndClosing::create([
                'fiscal_year' => $year,
                'closing_date' => $closingDate,
                'action' => 'CLOSE',
                'office_id' => null,
                'created_by' => auth()->id(),
                'summary' => json_encode([
                    'total_revenue' => $totalRevenue,
                    'total_expense' => $totalExpense,
                    'net_income' => $totalRevenue - $totalExpense,
                    'entries_created' => $entriesCreated,
                ]),
                'entries_created' => $entriesCreated,
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Year-End Closing for {$year} completed. {$entriesCreated} journal entries created.",
                'entries_created' => $entriesCreated,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
        ]);

        $companyId = auth()->user()->company_id ?? null;
        $year = $request->fiscal_year;
        $closingDate = "{$year}-12-31";

        $lastClose = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('fiscal_year', $year)
            ->where('action', 'CLOSE')
            ->orderByDesc('created_at')
            ->first();
        if (!$lastClose) {
            return response()->json(['success' => false, 'message' => "No closing found for year {$year}."]);
        }

        $lastCancel = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('fiscal_year', $year)
            ->where('action', 'CANCEL')
            ->orderByDesc('created_at')
            ->first();
        if ($lastCancel && $lastCancel->created_at->gt($lastClose->created_at)) {
            return response()->json(['success' => false, 'message' => "Year {$year} closing is already cancelled."]);
        }

        \DB::beginTransaction();

        try {
            $closingEntries = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
                ->where('description', 'LIKE', "%Year-End Closing%({$year})%")
                ->where('entry_date', $closingDate)
                ->get();

            foreach ($closingEntries as $entry) {
                $entry->update(['status' => 'VOIDED']);
            }

            YearEndClosing::create([
                'fiscal_year' => $year,
                'closing_date' => $closingDate,
                'action' => 'CANCEL',
                'office_id' => null,
                'created_by' => auth()->id(),
                'summary' => json_encode(['voided_entries' => $closingEntries->count()]),
                'entries_created' => 0,
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Year-End Closing for {$year} has been cancelled. {$closingEntries->count()} entries voided.",
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function detail(Request $request, $id)
    {
        $companyId = auth()->user()->company_id ?? null;
        $closing = YearEndClosing::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->with(['office', 'creator'])
            ->findOrFail($id);
        $entries = AccountingJournal::when($companyId, fn($q) => $q->whereHas('creator', fn($c) => $c->where('company_id', $companyId)))
            ->where('description', 'LIKE', "%Year-End Closing%")
            ->where('entry_date', $closing->closing_date)
            ->get();

        return response()->json([
            'success' => true,
            'closing' => [
                'id' => $closing->id,
                'fiscal_year' => $closing->fiscal_year,
                'action' => $closing->action,
                'closing_date' => $closing->closing_date->format('m-d-Y'),
                'entries_created' => $closing->entries_created,
                'summary' => json_decode($closing->summary, true),
            ],
            'entries' => $entries->map(fn($e) => [
                'entry_no' => $e->entry_no,
                'description' => $e->description,
                'status' => $e->status,
            ]),
        ]);
    }

    private function getAccountBalance($accountId, $startDate, $endDate)
    {
        $companyId = auth()->user()->company_id ?? null;
        $debits = JournalEntryLine::where('gl_account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $companyId) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'POSTED')
                  ->when($companyId, fn($cq) => $cq->whereHas('creator', fn($c) => $c->where('company_id', $companyId)));
            })
            ->sum('local_debit');

        $credits = JournalEntryLine::where('gl_account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $companyId) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'POSTED')
                  ->when($companyId, fn($cq) => $cq->whereHas('creator', fn($c) => $c->where('company_id', $companyId)));
            })
            ->sum('local_credit');

        return $debits - $credits;
    }
}
