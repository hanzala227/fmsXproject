<?php

namespace App\Http\Controllers;

use App\Models\TradePartner;
use App\Models\TradePartnerCredit;
use App\Models\CreditLimitGroup;
use App\Models\AccountGroup;
use App\Http\Requests\StoreTradePartnerCreditRequest;
use App\Http\Requests\UpdateTradePartnerCreditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradePartnerCreditController extends Controller
{
    /**
     * Display the Trade Partner Credit Entry page.
     */
    public function index(Request $request)
    {
        $query = TradePartner::with(['accountGroup', 'creditLimitGroup'])
            ->select([
                'id', 'code', 'name', 'alias', 'type',
                'account_group_id', 'payment_type', 'credit_term_unit',
                'credit_term_days', 'credit_limit', 'remark',
                'account_group_id', 'credit_limit_group_id',
            ]);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('alias', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($code = $request->get('filter_code')) {
            $query->where('code', 'like', "%{$code}%");
        }
        if ($name = $request->get('filter_name')) {
            $query->where('name', 'like', "%{$name}%");
        }
        if ($alias = $request->get('filter_alias')) {
            $query->where('alias', 'like', "%{$alias}%");
        }
        if ($type = $request->get('filter_type')) {
            $query->where('type', $type);
        }
        if ($accountGroup = $request->get('filter_account_group')) {
            $query->where('account_group_id', $accountGroup);
        }
        if ($payment = $request->get('filter_payment')) {
            $query->where('payment_type', $payment);
        }

        $partners = $query->orderBy('name')->paginate(50)->withQueryString();

        $creditLimitGroups = CreditLimitGroup::with('tradePartners')->orderBy('name')->get();
        $accountGroups = AccountGroup::orderBy('name')->get();
        $totalCreditLimitAll = TradePartner::sum('credit_limit');

        return view('trade-partner.credit-entry', compact(
            'partners', 'creditLimitGroups', 'accountGroups', 'totalCreditLimitAll'
        ));
    }

    /**
     * Export credit entry data as CSV.
     */
    public function exportCsv()
    {
        $partners = TradePartner::with('accountGroup')
            ->select([
                'id', 'code', 'name', 'alias', 'type',
                'account_group_id', 'payment_type', 'credit_term_unit',
                'credit_term_days', 'credit_limit', 'remark',
            ])
            ->orderBy('name')
            ->get();

        $filename = 'credit-entries-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($partners) {
            $handle = fopen('php://output', 'w');

            // BOM for UTF-8 Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'Code', 'Customer', 'Alias', 'Type',
                'Account Group', 'Payment Type', 'Credit Term',
                'Days', 'Credit Limit', 'Current Balance',
                'Over Limit', 'Remark'
            ]);

            // Data rows
            foreach ($partners as $p) {
                fputcsv($handle, [
                    $p->code,
                    $p->name,
                    $p->alias ?? '',
                    $p->type,
                    $p->accountGroup->name ?? '',
                    $p->payment_type ?? 'CREDIT',
                    $p->credit_term_unit ?? 'Days',
                    $p->credit_term_days ?? 0,
                    number_format($p->credit_limit ?? 0, 2),
                    '0.00',
                    '0.00',
                    $p->remark ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk save credit entries for trade partners.
     */
    public function saveCreditEntries(StoreTradePartnerCreditRequest $request)
    {
        $entries = $request->input('entries', []);

        if (empty($entries)) {
            return response()->json([
                'success' => false,
                'message' => 'No entries provided to save.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($entries as $entry) {
                if (!isset($entry['id'])) continue;

                $partner = TradePartner::find($entry['id']);
                if (!$partner) continue;

                $updateData = [];

                if (array_key_exists('account_group_id', $entry)) {
                    $updateData['account_group_id'] = $entry['account_group_id'] ?: null;
                }
                if (array_key_exists('payment_type', $entry)) {
                    $updateData['payment_type'] = $entry['payment_type'];
                }
                if (array_key_exists('credit_term_unit', $entry)) {
                    $updateData['credit_term_unit'] = $entry['credit_term_unit'];
                }
                if (array_key_exists('credit_term_days', $entry)) {
                    $updateData['credit_term_days'] = (int)($entry['credit_term_days'] ?? 0);
                }
                if (array_key_exists('credit_limit', $entry)) {
                    $updateData['credit_limit'] = (float)($entry['credit_limit'] ?? 0);
                }
                if (array_key_exists('remark', $entry)) {
                    $updateData['remark'] = $entry['remark'];
                }

                if (!empty($updateData)) {
                    $partner->update($updateData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Credit entries saved successfully.',
                'updated_count' => count($entries),
                'totalCreditLimitAll' => TradePartner::sum('credit_limit')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save credit entries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================================
     * CREDIT LIMIT GROUP CRUD
     * ==========================================================
     */

    /**
     * List all credit limit groups (JSON).
     */
    public function listGroups()
    {
        $groups = CreditLimitGroup::withCount('tradePartners')->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $groups]);
    }

    /**
     * Store a new credit limit group.
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:credit_limit_groups,name',
            'description' => 'nullable|string|max:500',
            'payment_type' => 'nullable|string|in:COD,CREDIT,PREPAID,COLLECT',
            'credit_term_unit' => 'nullable|string|max:50',
            'credit_term_days' => 'nullable|integer|min:0|max:9999',
            'credit_limit' => 'nullable|numeric|min:0|max:9999999999.99',
        ]);

        $group = CreditLimitGroup::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Credit limit group created.',
            'data' => $group->loadCount('tradePartners')
        ]);
    }

    /**
     * Update a credit limit group.
     */
    public function updateGroup(Request $request, CreditLimitGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:credit_limit_groups,name,' . $group->id,
            'description' => 'nullable|string|max:500',
            'payment_type' => 'nullable|string|in:COD,CREDIT,PREPAID,COLLECT',
            'credit_term_unit' => 'nullable|string|max:50',
            'credit_term_days' => 'nullable|integer|min:0|max:9999',
            'credit_limit' => 'nullable|numeric|min:0|max:9999999999.99',
        ]);

        $group->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Credit limit group updated.',
            'data' => $group->loadCount('tradePartners')
        ]);
    }

    /**
     * Delete a credit limit group.
     */
    public function destroyGroup(CreditLimitGroup $group)
    {
        $memberCount = $group->tradePartners()->count();
        if ($memberCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete: {$memberCount} customer(s) are assigned to this group."
            ], 422);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Credit limit group deleted.'
        ]);
    }

    /**
     * Bulk delete credit limit groups.
     */
    public function bulkDeleteGroups(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No groups selected.'], 422);
        }

        $groups = CreditLimitGroup::whereIn('id', $ids)->get();
        $deleted = 0;
        $skipped = [];

        foreach ($groups as $group) {
            $count = $group->tradePartners()->count();
            if ($count > 0) {
                $skipped[] = "{$group->name} ({$count} member(s))";
                continue;
            }
            $group->delete();
            $deleted++;
        }

        $message = "{$deleted} group(s) deleted.";
        if (!empty($skipped)) {
            $message .= ' Skipped (has members): ' . implode(', ', $skipped);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreTradePartnerCreditRequest $request)
    {
        $data = $request->validated();
        $credit = TradePartnerCredit::create($data);
        return response()->json(['success' => true, 'data' => $credit]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TradePartnerCredit $tradePartnerCredit)
    {
        return response()->json($tradePartnerCredit);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TradePartnerCredit $tradePartnerCredit)
    {
        return response()->json($tradePartnerCredit);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateTradePartnerCreditRequest $request, TradePartnerCredit $tradePartnerCredit)
    {
        $tradePartnerCredit->update($request->validated());
        return response()->json(['success' => true, 'data' => $tradePartnerCredit]);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(TradePartnerCredit $tradePartnerCredit)
    {
        $tradePartnerCredit->delete();
        return response()->json(['success' => true, 'message' => 'Credit entry deleted.']);
    }
}
