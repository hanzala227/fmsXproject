<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPayment;
use App\Models\BankAccount;
use App\Models\ClearCheckExcelLog;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClearCheckByExcelController extends Controller
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

        return view('accounting.bank-clear-check-by-excel', compact('bankNames'));
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'bank_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $file = $request->file('file');
        $bankName = $request->input('bank_name');
        $fileName = $file->getClientOriginalName();

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return response()->json(['success' => false, 'message' => 'Failed to read file.'], 422);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return response()->json(['success' => false, 'message' => 'File is empty or invalid format.'], 422);
        }

        $headerLower = array_map('strtolower', array_map('trim', $header));

        $checkNoIdx = null;
        $amountIdx = null;

        foreach ($headerLower as $i => $col) {
            if (in_array($col, ['check_no', 'check no', 'checkno', 'check_number', 'check number', 'check no.', 'check #'])) {
                $checkNoIdx = $i;
            }
            if (in_array($col, ['amount'])) {
                $amountIdx = $i;
            }
        }

        if ($checkNoIdx === null) {
            fclose($handle);
            return response()->json(['success' => false, 'message' => 'CSV must have a "Check No." column.'], 422);
        }

        $rows = [];
        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) <= $checkNoIdx) continue;

            $checkNo = trim($row[$checkNoIdx]);
            if (empty($checkNo)) continue;

            $csvAmount = null;
            if ($amountIdx !== null && isset($row[$amountIdx])) {
                $csvAmount = (float) str_replace(',', '', trim($row[$amountIdx]));
            }

            $rows[] = [
                'row' => $rowNum,
                'check_no' => $checkNo,
                'csv_amount' => $csvAmount,
            ];
        }
        fclose($handle);

        if (empty($rows)) {
            return response()->json(['success' => false, 'message' => 'No valid rows found in file.'], 422);
        }

        $results = [];

        foreach ($rows as $row) {
            $query = AccountingPayment::where('check_no', $row['check_no'])
                ->where('bank_name', $bankName)
                ->whereNull('clear_date')
                ->whereNull('void_date')
                ->with(['tradePartner', 'currency']);

            $payment = $query->first();

            if ($payment) {
                $results[] = [
                    'id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'check_no' => $payment->check_no,
                    'bank_amount' => round((float) $payment->amount, 2),
                    'payment_date' => $payment->payment_date?->format('Y-m-d') ?? '--',
                    'trade_partner' => $payment->tradePartner?->name ?? 'N/A',
                    'amount' => $row['csv_amount'] ?? round((float) $payment->amount, 2),
                    'currency' => $payment->currency?->code ?? 'USD',
                    'office' => $payment->office?->code ?? 'All',
                    'csv_row' => $row['row'],
                    'matched' => true,
                ];
            } else {
                $results[] = [
                    'id' => null,
                    'check_no' => $row['check_no'],
                    'bank_amount' => '--',
                    'payment_date' => '--',
                    'trade_partner' => '--',
                    'amount' => $row['csv_amount'] ?? '--',
                    'currency' => '--',
                    'office' => '--',
                    'csv_row' => $row['row'],
                    'matched' => false,
                ];
            }
        }

        $totalAmount = 0;
        foreach ($results as $r) {
            if ($r['matched'] && is_numeric($r['amount'])) {
                $totalAmount += (float) $r['amount'];
            }
        }

        return response()->json([
            'success' => true,
            'file_name' => $fileName,
            'bank_name' => $bankName,
            'results' => $results,
            'total_count' => count($results),
            'selected_count' => count(array_filter($results, fn($r) => $r['matched'])),
            'total_amount' => round($totalAmount, 2),
        ]);
    }

    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string',
            'file_name' => 'required|string',
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'exists:payments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $bankName = $request->input('bank_name');
        $paymentIds = $request->input('payment_ids');
        $now = now()->toDateString();

        $payments = AccountingPayment::whereIn('id', $paymentIds)->get();
        $totalAmount = 0;
        $processedIds = [];

        foreach ($payments as $p) {
            $p->clear_date = $now;
            $p->save();
            $totalAmount += (float) $p->amount;
            $processedIds[] = $p->id;
        }

        ClearCheckExcelLog::create([
            'user_id' => auth()->id(),
            'file_name' => $request->input('file_name'),
            'bank_name' => $bankName,
            'clear_date' => $now,
            'total_amount' => round($totalAmount, 2),
            'matched_count' => count($processedIds),
            'unmatched_count' => 0,
            'matched_ids' => $processedIds,
            'unmatched_rows' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($processedIds) . ' check(s) cleared successfully.',
            'processed_count' => count($processedIds),
            'total_amount' => round($totalAmount, 2),
        ]);
    }

    public function history(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(5, (int) $request->input('per_page', 10)));

        $query = ClearCheckExcelLog::with('user')->latest();
        $total = $query->count();
        $logs = $query->skip(($page - 1) * $perPage)->take($perPage)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'bank_name' => $log->bank_name ?? 'All',
                'file_name' => $log->file_name,
                'date' => $log->created_at?->format('m-d-Y') ?? '--',
                'uploader' => $log->user?->name ?? 'System',
                'matched_count' => $log->matched_count,
                'total_amount' => round((float) $log->total_amount, 2),
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
            'log_id' => 'required|exists:clear_check_excel_logs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $log = ClearCheckExcelLog::findOrFail($request->input('log_id'));
        $paymentIds = $log->matched_ids ?? [];

        $payments = AccountingPayment::whereIn('id', $paymentIds)
            ->with(['tradePartner', 'currency', 'office'])
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'payment_no' => $p->payment_no,
                    'check_no' => $p->check_no ?? '--',
                    'amount' => round((float) $p->amount, 2),
                    'currency' => $p->currency?->code ?? 'USD',
                    'bank_name' => $p->bank_name ?? '--',
                    'payment_date' => $p->payment_date?->format('Y-m-d') ?? '--',
                    'trade_partner' => $p->tradePartner?->name ?? 'N/A',
                    'office' => $p->office?->code ?? 'All',
                    'clear_date' => $p->clear_date?->format('Y-m-d') ?? '--',
                ];
            });

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'file_name' => $log->file_name,
                'clear_date' => $log->clear_date?->format('Y-m-d'),
                'total_amount' => round((float) $log->total_amount, 2),
                'matched_count' => $log->matched_count,
                'unmatched_count' => $log->unmatched_count,
                'unmatched_rows' => $log->unmatched_rows ?? [],
                'operator' => $log->user?->name ?? 'System',
                'created_at' => $log->created_at?->format('Y-m-d H:i'),
            ],
            'payments' => $payments,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $logs = ClearCheckExcelLog::with('user')->latest()->get();

        $filename = 'clear-check-excel-log-' . now()->format('YmdHis') . '.csv';

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Clear Check by Excel Log']);
            fwrite($handle, "\n");
            fputcsv($handle, ['Date', 'Operator', 'File Name', 'Clear Date', 'Matched', 'Unmatched', 'Total Amount']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('m-d-Y') ?? '',
                    $log->user?->name ?? 'System',
                    $log->file_name,
                    $log->clear_date?->format('Y-m-d') ?? '',
                    $log->matched_count,
                    $log->unmatched_count,
                    number_format($log->total_amount, 2),
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
