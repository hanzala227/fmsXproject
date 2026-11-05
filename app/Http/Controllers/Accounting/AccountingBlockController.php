<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingBlockDate;
use App\Models\AccountingBlockHistory;
use App\Models\Office;
use Illuminate\Http\Request;

class AccountingBlockController extends Controller
{
    public function index()
    {
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        // Get latest block/unblock status per office (including global/all offices)
        $latestRecords = AccountingBlockDate::with(['office', 'creator'])
            ->orderByDesc('id')
            ->get()
            ->unique('office_id');

        return view('accounting.accounting-block', compact('offices', 'latestRecords'));
    }

    public function apply(Request $request)
    {
        // Access Control: Only Administrators can modify accounting block status
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Administrator privileges required to block or unblock accounting periods.'
            ], 403);
        }

        $request->validate([
            'office_id'  => 'nullable|exists:offices,id',
            'action'     => 'required|in:BLOCK,UNBLOCK',
            'block_date' => 'required|date',
        ]);

        $blockDate = AccountingBlockDate::create([
            'office_id'  => $request->office_id,
            'block_date' => $request->block_date,
            'action'     => $request->action,
            'created_by' => auth()->id(),
        ]);

        // Audit Trail
        AccountingBlockHistory::create([
            'program'      => 'Accounting Block',
            'is_blocked'   => ($request->action === 'BLOCK'),
            'block_type'   => 'PERIOD',
            'ref_no'       => 'BLK-' . date('ymd', strtotime($request->block_date)),
            'block_date'   => $request->block_date,
            'office_id'    => $request->office_id,
            'execute_by'   => auth()->id(),
            'executed_at'  => now(),
            'record_id'    => $blockDate->id,
            'record_table' => 'accounting_block_dates',
        ]);

        $label = ($request->action === 'BLOCK') ? 'blocked' : 'unblocked';
        $officeName = $request->office_id ? Office::find($request->office_id)?->name : 'All Offices';

        return response()->json([
            'success' => true,
            'message' => "Accounting data successfully {$label} for {$officeName} before " . date('m-d-Y', strtotime($request->block_date)) . ".",
        ]);
    }
}
