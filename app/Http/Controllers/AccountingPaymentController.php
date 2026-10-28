<?php

namespace App\Http\Controllers;

use App\Models\AccountingPayment;
use App\Models\TradePartner;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\PaymentDocument;
use App\Models\PaymentMemo;
use App\Models\Office;
use App\Http\Requests\StoreAccountingPaymentRequest;
use App\Http\Requests\UpdateAccountingPaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AccountingPaymentController extends Controller
{
    public function index()
    {
        $payments = AccountingPayment::with(['tradePartner', 'currency', 'invoice'])->latest()->paginate(15);
        return response()->json($payments);
    }

    public function show($id)
    {
        $payment = AccountingPayment::with(['tradePartner', 'currency', 'invoice', 'office', 'bankCurrency'])->findOrFail($id);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $payment
            ]);
        }

        return redirect()->route('accounting.payment.edit', $payment->id);
    }

    public function destroy($id)
    {
        $payment = AccountingPayment::findOrFail($id);
        $invoiceId = $payment->invoice_id;
        
        $payment->delete();
        self::syncInvoiceBalance($invoiceId);

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted and invoice ledger synchronized successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Payment deleted successfully.');
    }

    public function receivedList(Request $request)
    {
        $query = AccountingPayment::where('type', 'RECEIVED')->with(['tradePartner', 'currency', 'invoice', 'office', 'bankCurrency']);

        // Quick search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('tradePartner', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter parameters (matching Ocean Import pattern)
        if ($request->filled('filter_payment_no')) {
            $query->where('payment_no', 'like', '%' . $request->input('filter_payment_no') . '%');
        }
        if ($request->filled('filter_trade_partner')) {
            $query->whereHas('tradePartner', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('filter_trade_partner') . '%');
            });
        }
        if ($request->filled('filter_reference_no')) {
            $query->where('reference_no', 'like', '%' . $request->input('filter_reference_no') . '%');
        }
        if ($request->filled('filter_bank')) {
            $query->where('bank_name', 'like', '%' . $request->input('filter_bank') . '%');
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('office', function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->input('filter_office') . '%');
            });
        }

        $payments = $query->latest()->paginate($request->input('limit', 50))->withQueryString();
        $totalReceivedAmount = AccountingPayment::where('type', 'RECEIVED')->sum('amount');

        // If AJAX request, return only the table + pagination partial
        if ($request->ajax()) {
            return response()->json([
                'table' => view('accounting.payment-received-list-partial', compact('payments'))->render(),
                'pagination' => (string) $payments->links(),
                'total' => $payments->total(),
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem(),
            ]);
        }

        return view('accounting.payment-received-list', compact('payments', 'totalReceivedAmount'));
    }

    public function updateColor(Request $request, $id)
    {
        $payment = AccountingPayment::findOrFail($id);
        $request->validate(['color' => 'nullable|string|max:20']);
        $payment->update(['color' => $request->input('color')]);
        return response()->json(['success' => true]);
    }

    public function exportReceivedList(Request $request)
    {
        $query = AccountingPayment::where('type', 'RECEIVED')->with(['tradePartner', 'currency', 'invoice', 'office', 'bankCurrency']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('tradePartner', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('filter_trade_partner')) {
            $query->whereHas('tradePartner', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('filter_trade_partner') . '%');
            });
        }
        if ($request->filled('filter_reference_no')) {
            $query->where('reference_no', 'like', '%' . $request->input('filter_reference_no') . '%');
        }
        if ($request->filled('filter_bank')) {
            $query->where('bank_name', 'like', '%' . $request->input('filter_bank') . '%');
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('office', function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->input('filter_office') . '%');
            });
        }

        $payments = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payment-received-list.csv"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            // Header row
            fputcsv($file, ['Date', 'Received From', 'Type', 'Ref No.', 'Bank', 'Amount (CAD)', 'Amount (Bank Cur.)', 'Deposit Date', 'Void', 'Office', 'Remark']);
            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->payment_date?->format('Y-m-d') ?? '',
                    $p->tradePartner?->name ?? '',
                    $p->payment_method ?? '',
                    $p->reference_no ?? '',
                    $p->bank_name ?? '',
                    number_format($p->amount, 2),
                    ($p->bankCurrency?->code ?? '') . ' ' . number_format($p->amount, 2),
                    $p->clear_date?->format('Y-m-d') ?? '',
                    $p->void_date ? 'Yes' : '',
                    $p->office?->code ?? '',
                    $p->remark ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDeleteReceived(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected.']);
        }

        $affectedInvoiceIds = AccountingPayment::whereIn('id', $ids)->pluck('invoice_id')->filter()->unique()->toArray();
        $count = AccountingPayment::whereIn('id', $ids)->where('type', 'RECEIVED')->delete();

        foreach ($affectedInvoiceIds as $invId) {
            self::syncInvoiceBalance($invId);
        }

        return response()->json(['success' => true, 'message' => "{$count} payment(s) deleted successfully."]);
    }

    public function madeList(Request $request)
    {
        $query = AccountingPayment::where('type', 'MADE')->with(['tradePartner', 'currency', 'office', 'bankCurrency']);

        // Quick search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('tradePartner', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter parameters
        if ($request->filled('filter_trade_partner')) {
            $query->whereHas('tradePartner', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('filter_trade_partner') . '%');
            });
        }
        if ($request->filled('filter_reference_no')) {
            $query->where('reference_no', 'like', '%' . $request->input('filter_reference_no') . '%');
        }
        if ($request->filled('filter_bank')) {
            $query->where('bank_name', 'like', '%' . $request->input('filter_bank') . '%');
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('office', function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->input('filter_office') . '%');
            });
        }

        $payments = $query->latest()->paginate($request->input('limit', 50))->withQueryString();
        $totalPaidAmount = AccountingPayment::where('type', 'MADE')->sum('amount');

        // If AJAX request, return only the table + pagination partial
        if ($request->ajax()) {
            return response()->json([
                'table' => view('accounting.payment-made-list-partial', compact('payments'))->render(),
                'pagination' => (string) $payments->links(),
                'total' => $payments->total(),
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem(),
            ]);
        }

        return view('accounting.payment-made-list', compact('payments', 'totalPaidAmount'));
    }

    public function exportMadeList(Request $request)
    {
        $query = AccountingPayment::where('type', 'MADE')->with(['tradePartner', 'currency', 'office', 'bankCurrency']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('tradePartner', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('filter_trade_partner')) {
            $query->whereHas('tradePartner', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('filter_trade_partner') . '%');
            });
        }
        if ($request->filled('filter_reference_no')) {
            $query->where('reference_no', 'like', '%' . $request->input('filter_reference_no') . '%');
        }
        if ($request->filled('filter_bank')) {
            $query->where('bank_name', 'like', '%' . $request->input('filter_bank') . '%');
        }
        if ($request->filled('filter_office')) {
            $query->whereHas('office', function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->input('filter_office') . '%');
            });
        }

        $payments = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payment-made-list.csv"',
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Paid To', 'Type', 'Ref No.', 'Bank', 'Amount (CAD)', 'Amount (Bank Cur.)', 'Clear Date', 'Void', 'Office', 'Print', 'Remark']);
            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->payment_date?->format('Y-m-d') ?? '',
                    $p->tradePartner?->name ?? '',
                    $p->payment_method ?? '',
                    $p->reference_no ?? '',
                    $p->bank_name ?? '',
                    number_format($p->amount, 2),
                    ($p->bankCurrency?->code ?? '') . ' ' . number_format($p->amount, 2),
                    $p->clear_date?->format('Y-m-d') ?? '',
                    $p->void_date ? 'Yes' : '',
                    $p->office?->code ?? '',
                    $p->show_party_on_check ? 'Yes' : 'No',
                    $p->remark ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDeleteMade(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected.']);
        }

        $affectedInvoiceIds = AccountingPayment::whereIn('id', $ids)->pluck('invoice_id')->filter()->unique()->toArray();
        $count = AccountingPayment::whereIn('id', $ids)->where('type', 'MADE')->delete();

        foreach ($affectedInvoiceIds as $invId) {
            self::syncInvoiceBalance($invId);
        }

        return response()->json(['success' => true, 'message' => "{$count} payment(s) deleted successfully."]);
    }

    public function create(Request $request)
    {
        $type = $request->segment(3) === 'make' ? 'MADE' : 'RECEIVED';
        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        
        // Filter invoices based on payment type and status
        $invoiceType = $type === 'RECEIVED' ? 'AR' : 'AP';
        $invoiceQuery = Invoice::with(['billTo', 'currency', 'office', 'invoiceable'])
            ->where('type', $invoiceType)
            ->where('status', '!=', 'PAID');
        
        // Apply office filter if provided
        if ($request->filled('office_id')) {
            $invoiceQuery->where('office_id', $request->input('office_id'));
        }
        
        $invoices = $invoiceQuery->orderBy('invoice_date', 'desc')->get();

        $prefix = $type === 'MADE' ? 'PAY' : 'RCV';
        $paymentNo = $prefix . '-' . date('ymdHis');

        $viewName = $type === 'MADE' ? 'accounting.payment-make' : 'accounting.payment-receive';
        $payment = null;
        $selectedInvoiceId = $request->input('invoice_id');
        $selectedInvoiceIds = [];
        if ($request->filled('invoice_ids')) {
            $selectedInvoiceIds = array_filter(array_map('trim', explode(',', $request->input('invoice_ids'))));
        } elseif ($selectedInvoiceId) {
            $selectedInvoiceIds = [(string) $selectedInvoiceId];
        }
        $selectedOfficeId = $request->input('office_id');
        
        return view($viewName, compact('payment', 'type', 'tradePartners', 'currencies', 'offices', 'invoices', 'paymentNo', 'selectedInvoiceId', 'selectedInvoiceIds', 'selectedOfficeId'));
    }

    public function store(StoreAccountingPaymentRequest $request)
    {
        $data = $request->validated();
        $data['payment_no'] = $data['payment_no'] ?? 'PAY-' . date('ymdHis');
        $data['reference_no'] = $data['reference_no'] ?? '';
        $data['remark'] = $data['remark'] ?? '';
        $data['amount'] = $data['amount'] ?? 0;
        $data['show_party_on_check'] = $request->boolean('show_party_on_check');
        if (!$request->filled('clear_date')) $data['clear_date'] = null;
        if (!$request->filled('void_date')) $data['void_date'] = null;

        // Guard against overpayments exceeding remaining invoice balance
        if (!empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);
            if ($invoice) {
                $remainingBalance = max(0, round((float) $invoice->balance_amount, 2));
                if ((float) $data['amount'] > $remainingBalance + 0.001) {
                    $msg = "Payment amount (\${$data['amount']}) exceeds remaining invoice balance (\${$remainingBalance}). Overpayments must be logged as Customer Advance.";
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg, 'errors' => ['amount' => [$msg]]], 422);
                    }
                    return back()->withInput()->withErrors(['amount' => $msg]);
                }
            }
        }

        $payment = AccountingPayment::create($data);

        if ($payment->invoice_id) {
            self::syncInvoiceBalance($payment->invoice_id);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment saved successfully.',
                'payment' => $payment->fresh(),
                'redirect' => route('accounting.payment.edit', $payment->id),
            ]);
        }

        $saveAction = $request->input('save_action', 'save_close');
        if ($saveAction === 'save_new') {
            $redirectRoute = $payment->type === 'MADE' ? 'accounting.payment-make' : 'accounting.payment-receive';
            return redirect()->route($redirectRoute)
                ->with('success', 'Payment recorded successfully. Create another.');
        }

        return redirect()->route('accounting.payment.edit', $payment->id)
            ->with('success', 'Payment saved successfully. You can now manage documents and memos.');
    }

    public function edit(AccountingPayment $payment)
    {
        $payment->load(['tradePartner', 'currency', 'invoice', 'documents', 'memos.user', 'office', 'bankCurrency']);

        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $invoices = Invoice::with(['billTo', 'currency', 'office', 'invoiceable'])->where('status', '!=', 'PAID')
            ->orWhere('id', $payment->invoice_id)
            ->orderBy('invoice_date', 'desc')->get();

        $paymentNo = $payment->payment_no;
        $type = $payment->type;

        $viewName = $payment->type === 'MADE' ? 'accounting.payment-make' : 'accounting.payment-receive';
        return view($viewName, compact('payment', 'type', 'tradePartners', 'currencies', 'offices', 'invoices', 'paymentNo'));
    }

    public function update(Request $request, AccountingPayment $payment)
    {
        $data = $request->validate([
            'payment_no' => 'required|string|max:255|unique:payments,payment_no,' . $payment->id,
            'payment_date' => 'required|date',
            'trade_partner_id' => 'required|exists:trade_partners,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'reference_no' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_level' => 'nullable|string|max:20',
            'show_party_on_check' => 'nullable|boolean',
            'check_no' => 'nullable|string|max:100',
            'clear_date' => 'nullable|date',
            'void_date' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id',
            'bank_name' => 'nullable|string|max:200',
            'bank_currency_id' => 'nullable|exists:currencies,id',
        ]);

        $data['reference_no'] = $data['reference_no'] ?? '';
        $data['remark'] = $data['remark'] ?? '';
        $data['amount'] = $data['amount'] ?? 0;
        $data['show_party_on_check'] = $request->boolean('show_party_on_check');

        // If clear/void checkboxes are unchecked, null the dates
        if (!$request->boolean('has_clear')) $data['clear_date'] = null;
        if (!$request->boolean('has_void')) $data['void_date'] = null;

        $oldInvoiceId = $payment->invoice_id;

        DB::transaction(function() use ($payment, $data, $oldInvoiceId) {
            $payment->update($data);

            if ($payment->invoice_id) {
                self::syncInvoiceBalance($payment->invoice_id);
            }

            if ($oldInvoiceId && $oldInvoiceId != $payment->invoice_id) {
                self::syncInvoiceBalance($oldInvoiceId);
            }
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully.',
                'payment' => $payment->fresh(),
            ]);
        }

        return redirect()->route('accounting.payment.edit', $payment->id)
            ->with('success', 'Payment updated successfully.');
    }

    // === Document Methods ===

    public function uploadDocument(Request $request, AccountingPayment $payment)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('payments/documents', 'public');

        $doc = PaymentDocument::create([
            'payment_id' => $payment->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'document' => $doc->load('uploader'),
        ]);
    }

    public function deleteDocument($id)
    {
        $doc = PaymentDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['success' => true]);
    }

    public function downloadDocument($id)
    {
        $doc = PaymentDocument::findOrFail($id);
        return Storage::disk('public')->download($doc->file_path, $doc->file_name);
    }

    // === Memo Methods ===

    public function addMemo(Request $request, AccountingPayment $payment)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $memo = PaymentMemo::create([
            'payment_id' => $payment->id,
            'subject' => $request->subject,
            'content' => $request->content,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'memo' => $memo->load('user'),
        ]);
    }

    public function updateMemo(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $memo = PaymentMemo::findOrFail($id);
        $memo->update([
            'subject' => $request->subject,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'memo' => $memo->load('user'),
        ]);
    }

    public function deleteMemo($id)
    {
        $memo = PaymentMemo::findOrFail($id);
        $memo->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Recalculates and synchronizes invoice paid_amount, balance_amount, and status
     * based on the exact sum of active payments in the database.
     */
    public static function syncInvoiceBalance(?int $invoiceId): void
    {
        if (!$invoiceId) return;

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) return;

        $totalPaid = (float) DB::table('payments')
            ->where('invoice_id', $invoice->id)
            ->whereNull('deleted_at')
            ->sum('amount');

        $invoice->paid_amount = $totalPaid;
        $invoice->balance_amount = max(0, round($invoice->total_amount - $totalPaid, 2));

        if ($invoice->balance_amount <= 0) {
            $invoice->status = 'PAID';
        } elseif ($totalPaid > 0) {
            $invoice->status = 'PARTIAL';
        } else {
            $invoice->status = 'POSTED';
        }

        $invoice->save();
    }
}
