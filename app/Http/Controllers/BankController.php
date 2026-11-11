<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BankController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $banks = DB::table('banks')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bank) {
                $bank->selected = false;
                $bank->_unsaved = false;
                $bank->is_active = (bool) $bank->is_active;
                $bank->revenue_default = (bool) $bank->revenue_default;
                $bank->cost_default = (bool) $bank->cost_default;
                $bank->check_by_sequence = (bool) $bank->check_by_sequence;
                $bank->clear_check_by_cycle = (bool) $bank->clear_check_by_cycle;
                $bank->display_remark = (bool) $bank->display_remark;
                $bank->invoice_remark = (bool) $bank->invoice_remark;
                return $bank;
            });

        // Get dropdown data
        $currencies = DB::table('currencies')->select('code', 'name')->orderBy('code')->get();
        $offices = DB::table('offices')->select('id', 'name')->orderBy('name')->get();
        $glAccounts = DB::table('gl_accounts')->select('id', 'code', 'name')->orderBy('code')->get();

        return view('accounting.bank-list', compact('banks', 'currencies', 'offices', 'glAccounts'));
    }

    public function getBanks(Request $request)
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $query = DB::table('banks')->where('company_id', $companyId);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('gl_no', 'like', "%{$search}%")
                  ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        $banks = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bank) {
                $bank->selected = false;
                $bank->_unsaved = false;
                $bank->is_active = (bool) $bank->is_active;
                $bank->revenue_default = (bool) $bank->revenue_default;
                $bank->cost_default = (bool) $bank->cost_default;
                $bank->check_by_sequence = (bool) $bank->check_by_sequence;
                $bank->clear_check_by_cycle = (bool) $bank->clear_check_by_cycle;
                $bank->display_remark = (bool) $bank->display_remark;
                $bank->invoice_remark = (bool) $bank->invoice_remark;
                return $bank;
            });

        return response()->json(['banks' => $banks]);
    }

    public function bulkSave(Request $request)
    {
        try {
            $banks = $request->banks;
            $savedBanks = [];

            foreach ($banks as $bankData) {
                // Validate required fields
                if (empty($bankData['bank_name'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bank Name is required for all banks'
                    ], 422);
                }

                $saveData = [
                    'bank_name' => $bankData['bank_name'],
                    'gl_no' => $bankData['gl_no'] ?? '',
                    'initial_amount' => $bankData['initial_amount'] ?? 0,
                    'currency' => $bankData['currency'] ?? 'CAD',
                    'revenue_default' => $bankData['revenue_default'] ?? false,
                    'cost_default' => $bankData['cost_default'] ?? false,
                    'notes_receivable' => $bankData['notes_receivable'] ?? '',
                    'notes_payable' => $bankData['notes_payable'] ?? '',
                    'is_active' => $bankData['is_active'] ?? true,
                    'inactive_date' => $bankData['inactive_date'] ?? null,
                    'check_by_sequence' => $bankData['check_by_sequence'] ?? false,
                    'clear_check_by_cycle' => $bankData['clear_check_by_cycle'] ?? false,
                    'display_remark' => $bankData['display_remark'] ?? false,
                    'invoice_remark' => $bankData['invoice_remark'] ?? false,
                    'updated_at' => now(),
                ];

                // Handle JSON fields
                if (isset($bankData['check_sequences'])) {
                    $saveData['check_sequences'] = json_encode($bankData['check_sequences']);
                }
                if (isset($bankData['clear_check_config'])) {
                    $saveData['clear_check_config'] = json_encode($bankData['clear_check_config']);
                }
                if (isset($bankData['display_information'])) {
                    $saveData['display_information'] = $bankData['display_information'];
                }
                if (isset($bankData['invoice_settings'])) {
                    $saveData['invoice_settings'] = json_encode($bankData['invoice_settings']);
                }
                if (isset($bankData['is_default_invoice_bank'])) {
                    $saveData['is_default_invoice_bank'] = $bankData['is_default_invoice_bank'];
                }

                if (isset($bankData['id']) && $bankData['id']) {
                    // Update existing bank
                    DB::table('banks')
                        ->where('id', $bankData['id'])
                        ->where('company_id', auth()->user()?->company_id ?? 1)
                        ->update($saveData);
                    
                    $savedBanks[] = array_merge($bankData, [
                        'updated_at' => now()->toDateTimeString()
                    ]);
                } else {
                    // Create new bank
                    $saveData['company_id'] = auth()->user()?->company_id ?? 1;
                    $saveData['created_at'] = now();
                    $id = DB::table('banks')->insertGetId($saveData);

                    $savedBanks[] = array_merge($bankData, [
                        'id' => $id,
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($banks) . ' bank(s) saved successfully',
                'banks' => $savedBanks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save banks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $companyId = auth()->user()?->company_id ?? 1;
            // Check foreign key dependencies
            $hasPayments = DB::table('accounting_payments')->where('bank_id', $id)->exists();
            if (!$hasPayments && Schema::hasTable('payments')) {
                $hasPayments = DB::table('payments')->where('bank_id', $id)->exists();
            }

            if ($hasPayments) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete bank. Financial transactions exist for this bank account. Please set status to Inactive instead.'
                ], 422);
            }

            DB::table('banks')->where('id', $id)->where('company_id', $companyId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bank deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bank: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $companyId = auth()->user()?->company_id ?? 1;
            $ids = $request->ids ?? [];
            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => 'No banks selected']);
            }

            $deletableIds = [];
            $blockedCount = 0;

            foreach ($ids as $id) {
                $bankObj = DB::table('banks')->where('id', $id)->first();
                $bName = $bankObj?->bank_name;
                $hasPayments = false;
                if ($bName) {
                    $hasPayments = DB::table('payments')->where('bank_name', $bName)->whereNull('deleted_at')->exists();
                    if (!$hasPayments && !empty($bankObj->gl_no)) {
                        $hasPayments = DB::table('payments')->where('bank_name', $bankObj->gl_no)->whereNull('deleted_at')->exists();
                    }
                }

                if ($hasPayments) {
                    $blockedCount++;
                } else {
                    $deletableIds[] = $id;
                }
            }

            if (!empty($deletableIds)) {
                DB::table('banks')->whereIn('id', $deletableIds)->where('company_id', $companyId)->delete();
            }

            if ($blockedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => count($deletableIds) . ' bank(s) deleted. ' . $blockedCount . ' bank(s) skipped due to linked transactions.'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => count($deletableIds) . ' bank(s) deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function print(Request $request)
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $query = DB::table('banks')->where('company_id', $companyId);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('gl_no', 'like', "%{$search}%")
                  ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        $banks = $query->orderBy('created_at', 'desc')->get();

        return view('accounting.bank-list-print', compact('banks'));
    }

    public function export(Request $request)
    {
        $companyId = auth()->user()?->company_id ?? 1;
        $query = DB::table('banks')->where('company_id', $companyId);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('gl_no', 'like', "%{$search}%")
                  ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        $banks = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bank-list-' . date('Y-m-d') . '.csv"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($banks) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Header row
            fputcsv($handle, [
                'Bank Name', 'G/L No.', 'Initial Amount', 'Currency',
                'Revenue Default', 'Cost Default', 'Notes Receivable', 'Notes Payable',
                'Active', 'Inactive Date', 'Check By Sequence', 'Clear Check By Cycle',
                'Display Remark', 'Invoice Remark'
            ]);

            // Data rows
            foreach ($banks as $bank) {
                fputcsv($handle, [
                    $bank->bank_name,
                    $bank->gl_no ?? '',
                    $bank->initial_amount ?? 0,
                    $bank->currency ?? '',
                    $bank->revenue_default ? 'Yes' : 'No',
                    $bank->cost_default ? 'Yes' : 'No',
                    $bank->notes_receivable ?? '',
                    $bank->notes_payable ?? '',
                    $bank->is_active ? 'Yes' : 'No',
                    $bank->inactive_date ?? '',
                    $bank->check_by_sequence ? 'Yes' : 'No',
                    $bank->clear_check_by_cycle ? 'Yes' : 'No',
                    $bank->display_remark ? 'Yes' : 'No',
                    $bank->invoice_remark ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, 'bank-list-' . date('Y-m-d') . '.csv', $headers);
    }

    /**
     * Get next check number for a bank
     */
    public function getNextCheckNumber(Request $request, $bankId)
    {
        $bankService = new \App\Services\BankService();
        
        $result = $bankService->getNextCheckNumber(
            $bankId,
            $request->input('office_id'),
            $request->input('currency', 'CAD')
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'check_number' => $result['check_number'],
                'next_number' => $result['next_number'],
                'message' => 'Next check number generated: ' . $result['check_number']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No check sequence configured for this office/currency'
        ], 404);
    }

    /**
     * Import Excel for check clearing
     */
    public function importCheckClearing(Request $request, $bankId)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('excel_file');
        $filePath = $file->store('temp');
        $fullPath = storage_path('app/' . $filePath);

        $bankService = new \App\Services\BankService();
        $result = $bankService->importCheckClearing($bankId, $fullPath);

        // Clean up temp file
        unlink($fullPath);

        return response()->json($result);
    }

    /**
     * Get default invoice bank
     */
    public function getDefaultInvoiceBank()
    {
        $bankService = new \App\Services\BankService();
        $bank = $bankService->getDefaultInvoiceBank();

        if ($bank) {
            return response()->json([
                'success' => true,
                'bank' => $bank
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No default invoice bank configured'
        ], 404);
    }

    /**
     * Get bank display information
     */
    public function getBankDisplayInfo($bankId)
    {
        $bankService = new \App\Services\BankService();
        $info = $bankService->getBankDisplayInfo($bankId);

        if ($info) {
            return response()->json([
                'success' => true,
                'display_information' => $info
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No display information configured for this bank'
        ], 404);
    }
}
