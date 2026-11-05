<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPayment;
use App\Models\BankBatchLog;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankBatchProcessController extends Controller
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

        return view('accounting.bank-batch-process', compact('bankNames', 'offices'));
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operation_type' => 'required|in:Deposit,Clear Check,Cancel Deposit,Cancel Clear',
            'post_date' => 'required|date',
            'action_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            'bank_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $op = $request->input('operation_type');
        $postDate = $request->input('post_date');
        $actionDate = $request->input('action_date');
        $officeId = $request->input('office_id');
        $bankName = $request->input('bank_name');

        $query = AccountingPayment::with(['tradePartner', 'currency', 'office']);

        if ($officeId) {
            $query->where('office_id', $officeId);
        }
        if ($bankName) {
            $query->where('bank_name', $bankName);
        }

        if ($op === 'Deposit' || $op === 'Clear Check') {
            $query->whereNull('clear_date')
                   ->whereNull('void_date')
                   ->where('payment_date', '<=', $postDate);
            if ($op === 'Deposit') {
                $query->where('type', 'RECEIVED');
            } else {
                $query->where('type', 'MADE');
            }
        } elseif ($op === 'Cancel Deposit' || $op === 'Cancel Clear') {
            if (!$actionDate) {
                return response()->json(['success' => false, 'message' => 'Action date is required for cancel operations.'], 422);
            }
            $query->whereNotNull('clear_date')
                   ->whereNull('void_date')
                   ->where('clear_date', '<=', $actionDate);
            if ($op === 'Cancel Deposit') {
                $query->where('type', 'RECEIVED');
            } else {
                $query->where('type', 'MADE');
            }
        }

        $payments = $query->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'payment_no' => $p->payment_no,
                'payment_date' => $p->payment_date?->format('Y-m-d') ?? '--',
                'trade_partner' => $p->tradePartner?->name ?? 'N/A',
                'amount' => round((float) $p->amount, 2),
                'currency' => $p->currency?->code ?? 'USD',
                'office' => $p->office?->code ?? 'All',
                'bank_name' => $p->bank_name ?? '--',
                'check_no' => $p->check_no ?? '--',
                'clear_date' => $p->clear_date?->format('Y-m-d') ?? '--',
                'payment_level' => $p->payment_level ?? '--',
            ];
        });

        return response()->json([
            'success' => true,
            'payments' => $payments,
            'count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
        ]);
    }

    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operation_type' => 'required|in:Deposit,Clear Check,Cancel Deposit,Cancel Clear',
            'post_date' => 'required|date',
            'action_date' => 'nullable|date',
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'exists:payments,id',
            'office' => 'nullable|string',
            'bank_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $op = $request->input('operation_type');
        $postDate = $request->input('post_date');
        $actionDate = $request->input('action_date');
        $ids = $request->input('payment_ids');

        $payments = AccountingPayment::whereIn('id', $ids)->get();
        $totalAmount = 0;
        $processedIds = [];

        foreach ($payments as $p) {
            if ($op === 'Deposit') {
                $p->clear_date = $postDate;
            } elseif ($op === 'Clear Check') {
                $p->clear_date = $postDate;
            } elseif ($op === 'Cancel Deposit' || $op === 'Cancel Clear') {
                $p->clear_date = null;
            }
            $p->save();
            $totalAmount += (float) $p->amount;
            $processedIds[] = $p->id;
        }

        $officeLabel = $request->input('office') ?: 'All';
        $bankLabel = $request->input('bank_name') ?: '';

        BankBatchLog::create([
            'user_id' => auth()->id(),
            'operation_type' => $op,
            'post_date' => $postDate,
            'action_date' => $actionDate,
            'office' => $officeLabel,
            'bank_name' => $bankLabel,
            'total_amount' => round($totalAmount, 2),
            'payment_count' => count($processedIds),
            'payment_ids' => $processedIds,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($processedIds) . ' payment(s) processed successfully.',
            'processed_count' => count($processedIds),
            'total_amount' => round($totalAmount, 2),
        ]);
    }

    public function log(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(5, (int) $request->input('per_page', 10)));

        $query = BankBatchLog::with('user')->latest();

        if ($request->filled('operation_type')) {
            $query->where('operation_type', $request->input('operation_type'));
        }
        if ($request->filled('bank_name')) {
            $query->where('bank_name', $request->input('bank_name'));
        }

        $total = $query->count();
        $logs = $query->skip(($page - 1) * $perPage)->take($perPage)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'date' => $log->created_at?->format('m-d-Y') ?? '--',
                'operation' => $log->user?->name ?? 'System',
                'action' => $log->operation_type,
                'post_date' => $log->post_date?->format('Y-m-d') ?? '--',
                'office' => $log->office ?? 'All',
                'bank_name' => $log->bank_name ?? '',
                'action_date' => $log->action_date?->format('Y-m-d') ?? '--',
                'amount' => round((float) $log->total_amount, 2),
                'payment_count' => $log->payment_count,
                'payment_ids' => $log->payment_ids ?? [],
            ];
        });

        return response()->json([
            'success' => true,
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    public function logDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'log_id' => 'required|exists:bank_batch_logs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $log = BankBatchLog::findOrFail($request->input('log_id'));
        $paymentIds = $log->payment_ids ?? [];

        $payments = AccountingPayment::whereIn('id', $paymentIds)
            ->with(['tradePartner', 'currency', 'office'])
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'payment_no' => $p->payment_no,
                    'payment_date' => $p->payment_date?->format('Y-m-d') ?? '--',
                    'trade_partner' => $p->tradePartner?->name ?? 'N/A',
                    'amount' => round((float) $p->amount, 2),
                    'currency' => $p->currency?->code ?? 'USD',
                    'office' => $p->office?->code ?? 'All',
                    'bank_name' => $p->bank_name ?? '--',
                    'check_no' => $p->check_no ?? '--',
                    'clear_date' => $p->clear_date?->format('Y-m-d') ?? '--',
                ];
            });

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'operation_type' => $log->operation_type,
                'post_date' => $log->post_date?->format('Y-m-d'),
                'action_date' => $log->action_date?->format('Y-m-d'),
                'office' => $log->office ?? 'All',
                'bank_name' => $log->bank_name ?? '',
                'total_amount' => round((float) $log->total_amount, 2),
                'payment_count' => $log->payment_count,
                'user' => $log->user?->name ?? 'System',
                'created_at' => $log->created_at?->format('Y-m-d H:i'),
            ],
            'payments' => $payments,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $logs = BankBatchLog::with('user')->latest()->get();

        $filename = 'bank-batch-log-' . now()->format('YmdHis') . '.csv';

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Bank Batch Process Log']);
            fwrite($handle, "\n");
            fputcsv($handle, ['Date', 'Operator', 'Action', 'Post Date', 'Office', 'Bank', 'Deposit/Clear Date', 'Amount', 'Transactions']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('m-d-Y') ?? '',
                    $log->user?->name ?? 'System',
                    $log->operation_type,
                    $log->post_date?->format('Y-m-d') ?? '',
                    $log->office ?? 'All',
                    $log->bank_name ?? '',
                    $log->action_date?->format('Y-m-d') ?? '',
                    number_format($log->total_amount, 2),
                    $log->payment_count,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
