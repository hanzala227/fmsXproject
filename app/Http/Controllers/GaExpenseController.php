<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\TradePartner;
use App\Models\Currency;
use App\Models\Office;
use App\Models\User;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GaExpenseController extends Controller
{
    /**
     * List all G&A expenses (AP-type invoices).
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['billTo', 'currency', 'office', 'issuer', 'payments'])
            ->where('type', 'AP');

        // Quick search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('billing_address', 'like', "%{$search}%")
                  ->orWhereHas('billTo', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter row params
        if ($request->filled('filter_party')) {
            $party = $request->input('filter_party');
            $query->whereHas('billTo', function ($q) use ($party) {
                $q->where('name', 'like', "%{$party}%");
            });
        }

        if ($request->filled('filter_file_no')) {
            $fileNo = $request->input('filter_file_no');
            $query->where(function ($q) use ($fileNo) {
                $q->whereHasMorph('invoiceable', '*', function ($sub) use ($fileNo) {
                    $sub->where('file_no', 'like', "%{$fileNo}%");
                });
            });
        }

        if ($request->filled('filter_inv_no')) {
            $query->where('invoice_no', 'like', '%' . $request->input('filter_inv_no') . '%');
        }

        if ($request->filled('filter_type')) {
            $query->where('type', $request->input('filter_type'));
        } else {
            $query->where('type', 'AP');
        }

        if ($request->filled('filter_office')) {
            $office = $request->input('filter_office');
            $query->whereHas('office', function ($q) use ($office) {
                $q->where('code', 'like', "%{$office}%")
                  ->orWhere('name', 'like', "%{$office}%");
            });
        }

        if ($request->filled('filter_status')) {
            $query->where('status', $request->input('filter_status'));
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['invoice_no', 'invoice_date', 'due_date', 'subtotal', 'tax_total', 'total_amount', 'paid_amount', 'balance_amount', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $pageSize = $request->input('limit', 15);
        $expenses = $query->orderBy($sortField, $sortDir)->paginate($pageSize)->withQueryString();

        // Summary stats
        $totalAmount = Invoice::where('type', 'AP')->where('status', '!=', 'VOID')->sum('total_amount');
        $totalPaid = Invoice::where('type', 'AP')->where('status', '!=', 'VOID')->sum('paid_amount');
        $totalBalance = Invoice::where('type', 'AP')->where('status', '!=', 'VOID')->sum('balance_amount');

        return view('accounting.ga-expense-list', compact('expenses', 'totalAmount', 'totalPaid', 'totalBalance'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->get();
        $users = User::all();
        return view('accounting.create-ga-expense', compact('tradePartners', 'currencies', 'offices', 'users'));
    }

    /**
     * Store a new G&A expense (AP invoice).
     */
    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();
        $data['type'] = 'AP';
        $data['payable_to'] = $request->input('payable_to');
        $data['discount_pct'] = $data['discount_pct'] ?? 0;
        $data['tax_pct'] = $data['tax_pct'] ?? 0;
        $data['shipping_amount'] = $data['shipping_amount'] ?? 0;
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['tax_total'] = $data['tax_total'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $data['balance_amount'] = $data['total_amount'] - ($data['paid_amount'] ?? 0);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create($data);

            if ($request->filled('lines_json')) {
                $lines = json_decode($request->input('lines_json'), true) ?? [];
                foreach ($lines as $lineData) {
                    if (!empty($lineData['description']) || !empty($lineData['amount'])) {
                        $invoice->lines()->create([
                            'description' => $lineData['description'] ?? '',
                            'qty' => $lineData['qty'] ?? 1,
                            'rate' => $lineData['rate'] ?? 0,
                            'amount' => $lineData['amount'] ?? 0,
                            'charge_code' => $lineData['charge_code'] ?? null,
                            'type' => 'AP',
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'G&A Expense created successfully.',
                    'expense' => $invoice->fresh()->load(['billTo', 'currency', 'office']),
                    'redirect' => route('accounting.ga-expense.edit', $invoice->id),
                ]);
            }

            if ($request->input('save_action') === 'save_new') {
                return redirect()->route('accounting.ga-expense.create')
                    ->with('success', 'G&A Expense created successfully. Create another one.');
            }

            return redirect()->route('accounting.ga-expense.edit', $invoice->id)
                ->with('success', 'G&A Expense created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create expense: ' . $e->getMessage()]);
        }
    }

    /**
     * Show a single G&A expense.
     */
    public function show(Invoice $ga_expense)
    {
        if ($ga_expense->type !== 'AP') {
            abort(404);
        }
        $ga_expense->load(['billTo', 'currency', 'office', 'issuer', 'lines', 'payments']);
        return view('accounting.ga-expense-show', compact('ga_expense'));
    }

    /**
     * Show the edit form.
     */
    public function edit(Invoice $ga_expense)
    {
        if ($ga_expense->type !== 'AP') {
            abort(404);
        }
        $ga_expense->load(['lines', 'documents']);
        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->get();
        $users = User::all();
        return view('accounting.create-ga-expense', compact('ga_expense', 'tradePartners', 'currencies', 'offices', 'users'));
    }

    /**
     * Update a G&A expense.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $ga_expense)
    {
        if ($ga_expense->type !== 'AP') {
            abort(404);
        }

        $data = $request->validated();
        $data['type'] = 'AP';
        $data['payable_to'] = $request->input('payable_to');
        $data['discount_pct'] = $data['discount_pct'] ?? 0;
        $data['tax_pct'] = $data['tax_pct'] ?? 0;
        $data['shipping_amount'] = $data['shipping_amount'] ?? 0;
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['tax_total'] = $data['tax_total'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $data['balance_amount'] = $data['total_amount'] - ($data['paid_amount'] ?? 0);

        DB::beginTransaction();
        try {
            $ga_expense->update($data);

            if ($request->filled('lines_json')) {
                $ga_expense->lines()->delete();
                $lines = json_decode($request->input('lines_json'), true) ?? [];
                foreach ($lines as $lineData) {
                    if (!empty($lineData['description']) || !empty($lineData['amount'])) {
                        $ga_expense->lines()->create([
                            'description' => $lineData['description'] ?? '',
                            'qty' => $lineData['qty'] ?? 1,
                            'rate' => $lineData['rate'] ?? 0,
                            'amount' => $lineData['amount'] ?? 0,
                            'charge_code' => $lineData['charge_code'] ?? null,
                            'type' => 'AP',
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'G&A Expense updated successfully.',
                    'expense' => $ga_expense->fresh()->load(['billTo', 'currency', 'office']),
                ]);
            }

            if ($request->input('save_action') === 'save_new') {
                return redirect()->route('accounting.ga-expense.create')
                    ->with('success', 'G&A Expense updated successfully. Create another one.');
            }

            return redirect()->route('accounting.ga-expense.edit', $ga_expense->id)
                ->with('success', 'G&A Expense updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update expense: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a G&A expense.
     */
    public function destroy(Invoice $ga_expense)
    {
        if ($ga_expense->type !== 'AP') {
            abort(404);
        }
        if ($ga_expense->isApproved() || in_array(strtoupper($ga_expense->status), ['POSTED', 'PARTIAL', 'PAID', 'VOID']) || in_array($ga_expense->verification_status, ['Verified', 'Cancelled'])) {
            return redirect()->back()->with('error', 'Document Control: Approved or posted expenses cannot be deleted. Cancellation is required.');
        }
        $ga_expense->delete();
        return redirect()->route('accounting.ga-expense.index')
            ->with('success', 'G&A Expense deleted successfully.');
    }

    /**
     * AJAX: Update color mark.
     */
    public function updateColor(Request $request, Invoice $ga_expense)
    {
        if ($ga_expense->type !== 'AP') {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }
        $request->validate(['color' => 'nullable|string|max:20']);
        $ga_expense->update(['color' => $request->input('color')]);
        return response()->json(['success' => true, 'message' => 'Color updated.']);
    }

    /**
     * AJAX: Bulk delete.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No expenses selected.']);
        }

        $protected = Invoice::whereIn('id', $ids)
            ->where('type', 'AP')
            ->where(function ($q) {
                $q->whereIn('status', ['POSTED', 'PARTIAL', 'PAID', 'VOID'])
                  ->orWhereIn('verification_status', ['Verified', 'Cancelled']);
            })
            ->exists();

        if ($protected) {
            return response()->json([
                'success' => false,
                'message' => 'Document Control: Approved or posted expenses cannot be deleted. Administrators must cancel them.'
            ], 403);
        }

        Invoice::whereIn('id', $ids)->where('type', 'AP')->delete();
        return response()->json(['success' => true, 'message' => count($ids) . ' expense(s) deleted.']);
    }

    /**
     * AJAX: Batch update expense status.
     */
    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required|in:DRAFT,POSTED,PAID,PARTIAL,VOID',
        ]);

        $ids = $request->input('ids');
        $status = $request->input('status');

        $updated = Invoice::whereIn('id', $ids)->where('type', 'AP')->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => $updated . ' expense(s) updated to ' . $status . '.',
        ]);
    }

    /**
     * Export G&A expenses as CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = Invoice::with(['billTo', 'currency', 'office'])
            ->where('type', 'AP');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('billTo', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $expenses = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ga_expenses_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Reference No.', 'Party', 'Amount Before Tax', 'Tax', 'Total Amount', 'Paid Amount', 'Balance', 'Post Date', 'Due Date', 'Office', 'Issued By', 'Status']);
            foreach ($expenses as $exp) {
                fputcsv($handle, [
                    $exp->invoice_no,
                    $exp->billTo->name ?? '',
                    number_format($exp->subtotal, 2),
                    number_format($exp->tax_total, 2),
                    number_format($exp->total_amount, 2),
                    number_format($exp->paid_amount, 2),
                    number_format($exp->balance_amount, 2),
                    $exp->invoice_date ? $exp->invoice_date->format('Y-m-d') : '',
                    $exp->due_date ? $exp->due_date->format('Y-m-d') : '',
                    $exp->office->code ?? '',
                    $exp->issuer->name ?? '',
                    $exp->status,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Duplicate a G&A expense — loads create form with copied data.
     */
    public function duplicate(Invoice $ga_expense)
    {
        if ($ga_expense->type !== 'AP') {
            abort(404);
        }
        $ga_expense->load(['billTo', 'currency', 'office', 'issuer', 'lines']);
        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->get();
        $users = User::all();

        $ga_expense->invoice_no = $ga_expense->invoice_no . '-COPY-' . time();

        return view('accounting.create-ga-expense', compact('ga_expense', 'tradePartners', 'currencies', 'offices', 'users'));
    }
}
