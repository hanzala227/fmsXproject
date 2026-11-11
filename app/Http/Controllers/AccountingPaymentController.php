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
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;

class AccountingPaymentController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $payments = AccountingPayment::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->with(['tradePartner', 'currency', 'invoice'])
            ->latest()
            ->paginate(15);
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

        // Permanent deletion of accounting payments (Money Receipts / Vouchers) is strictly prohibited
        $msg = "Prohibited: Permanent deletion of accounting payments is prohibited by financial compliance. Only an administrator may cancel/void a payment with a valid reason.";
        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 403);
        }

        return redirect()->back()->with('error', $msg);
    }

    /**
     * Admin-only: Cancel/void an accounting payment with a mandatory reason.
     */
    public function cancel(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only an Administrator may cancel accounting payments.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ], [
            'reason.required' => 'A valid cancellation reason is mandatory.',
            'reason.min' => 'Cancellation reason must be at least 5 characters.',
        ]);

        $payment = AccountingPayment::findOrFail($id);

        if ($payment->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($payment->payment_date, $payment->office_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access Control: Cannot cancel a payment in a blocked accounting period.'
            ], 403);
        }

        if ($payment->void_date) {
            return response()->json([
                'success' => false,
                'message' => 'This payment is already voided/cancelled.'
            ], 422);
        }

        DB::transaction(function () use ($payment, $request, $user) {
            $oldValues = $payment->only(['void_date', 'amount', 'type', 'invoice_id']);

            $payment->update([
                'void_date' => now()->toDateString(),
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $request->input('reason'),
            ]);

            // Synchronize the parent invoice balance atomically so the balance is restored
            if ($payment->invoice_id) {
                self::syncInvoiceBalance($payment->invoice_id);
            }

            // Retain audit trail
            ActivityLog::create([
                'company_id' => $payment->company_id,
                'user_id' => $user->id,
                'action' => 'payment_cancelled',
                'description' => "Payment {$payment->payment_no} ({$payment->type}) cancelled by Administrator. Reason: " . $request->input('reason'),
                'model_type' => AccountingPayment::class,
                'model_id' => $payment->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => $payment->fresh()->only(['void_date', 'cancellation_reason', 'cancelled_at', 'cancelled_by']),
            ]);
        });

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Payment {$payment->payment_no} cancelled successfully and invoice ledger updated.",
                'payment' => $payment->fresh(),
            ]);
        }

        return redirect()->back()->with('success', "Payment {$payment->payment_no} cancelled successfully.");
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
        return response()->json([
            'success' => false,
            'message' => "Prohibited: Permanent deletion of payment receipts (MR) is prohibited by financial compliance. Only an administrator may cancel/void a payment with a mandatory reason."
        ], 403);
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
        return response()->json([
            'success' => false,
            'message' => "Prohibited: Permanent deletion of payment vouchers is prohibited by financial compliance. Only an administrator may cancel/void a payment with a mandatory reason."
        ], 403);
    }

    public function create(Request $request)
    {
        $type = $request->segment(3) === 'make' ? 'MADE' : 'RECEIVED';
        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        
        // Filter invoices based on payment type and status
        $invoiceType = $type === 'RECEIVED' ? 'AR' : 'AP';
        $selectedInvoiceId = $request->input('invoice_id');
        $selectedInvoiceIds = [];
        if ($request->filled('invoice_ids')) {
            $selectedInvoiceIds = array_filter(array_map('trim', explode(',', $request->input('invoice_ids'))));
        } elseif ($selectedInvoiceId) {
            $selectedInvoiceIds = [(string) $selectedInvoiceId];
        }

        $selectedInvoice = null;
        if ($selectedInvoiceId) {
            $selectedInvoice = Invoice::with(['billTo', 'currency', 'office', 'invoiceable'])->find($selectedInvoiceId);
        }
        
        $targetPartnerId = $selectedInvoice?->bill_to_id ?? $request->input('trade_partner_id');

        $invoiceQuery = Invoice::with(['billTo', 'currency', 'office', 'invoiceable']);

        if ($targetPartnerId) {
            $invoiceQuery->where(function($q) use ($targetPartnerId, $invoiceType, $selectedInvoiceIds) {
                $q->where('bill_to_id', $targetPartnerId);
                if (!empty($selectedInvoiceIds)) {
                    $q->orWhereIn('id', $selectedInvoiceIds);
                }
                $q->orWhere(function($sub) use ($invoiceType) {
                    $sub->where('type', $invoiceType)
                        ->where('status', '!=', 'PAID');
                });
            });
        } else {
            $invoiceQuery->where(function($q) use ($invoiceType, $selectedInvoiceIds) {
                $q->where('type', $invoiceType);
                if (!empty($selectedInvoiceIds)) {
                    $q->orWhereIn('id', $selectedInvoiceIds);
                }
            });
        }
        
        // Apply office filter if provided
        if ($request->filled('office_id')) {
            $invoiceQuery->where('office_id', $request->input('office_id'));
        }
        
        $invoices = $invoiceQuery->orderBy('invoice_date', 'desc')->get();

        $prefix = $type === 'MADE' ? 'PAY' : 'RCV';
        $paymentNo = $prefix . '-' . date('ymdHis');

        $companyId = auth()->user()?->company_id ?? 1;
        $banks = DB::table('banks')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        $defaultBank = $type === 'MADE'
            ? $banks->firstWhere('cost_default', 1)
            : $banks->firstWhere('revenue_default', 1);
        $defaultBankName = $defaultBank?->bank_name ?? '';

        $viewName = $type === 'MADE' ? 'accounting.payment-make' : 'accounting.payment-receive';
        $payment = null;
        $selectedOfficeId = $request->input('office_id');
        
        return view($viewName, compact(
            'payment', 'type', 'tradePartners', 'currencies', 'offices',
            'invoices', 'paymentNo', 'selectedInvoiceId', 'selectedInvoiceIds',
            'selectedInvoice', 'selectedOfficeId', 'banks', 'defaultBankName'
        ));
    }

    public function store(StoreAccountingPaymentRequest $request)
    {
        $data = $request->validated();
        $data['payment_no'] = $data['payment_no'] ?? 'PAY-' . date('ymdHis');
        $data['reference_no'] = $data['reference_no'] ?? '';
        $data['remark'] = $data['remark'] ?? '';
        $data['amount'] = $data['amount'] ?? 0;
        $data['tds_tax'] = (float) ($request->input('tds_tax', 0));
        $data['discount'] = (float) ($request->input('discount', 0));
        $data['show_party_on_check'] = $request->boolean('show_party_on_check');
        if (!$request->filled('clear_date')) $data['clear_date'] = null;
        if (!$request->filled('void_date')) $data['void_date'] = null;

        // Lock historical exchange rate upon payment recording
        if (empty($data['exchange_rate'])) {
            $curr = Currency::find($data['currency_id'] ?? 1);
            $data['exchange_rate'] = (float) ($curr?->exchange_rate ?? 1.0);
        }

        $companyId = auth()->user()->company_id ?? 1;
        $data['company_id'] = $companyId;

        if (\App\Models\AccountingBlockDate::isDateBlocked($data['payment_date'] ?? null, $data['office_id'] ?? null, $companyId)) {
            $msg = 'Access Control: Cannot record a payment in a blocked accounting period.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->withInput()->withErrors(['payment_date' => $msg]);
        }

        // Concurrency Lock and Duplicate/Overpayment Prevention inside an isolated DB transaction
        $allocations = collect($request->input('allocations', []))
            ->filter(fn ($row) => !empty($row['invoice_id']) && (
                (float) ($row['amount'] ?? 0) + (float) ($row['tds_tax'] ?? 0) + (float) ($row['discount'] ?? 0) > 0
            ))
            ->values();

        if ($allocations->count() === 1) {
            $singleAlloc = $allocations->first();
            $data['invoice_id'] = (int) $singleAlloc['invoice_id'];
            $data['amount'] = (float) ($singleAlloc['amount'] ?? $data['amount']);
            $data['tds_tax'] = (float) ($singleAlloc['tds_tax'] ?? $data['tds_tax']);
            $data['discount'] = (float) ($singleAlloc['discount'] ?? $data['discount']);
        }

        $txResult = DB::transaction(function () use ($request, $data, $companyId, $allocations) {
            if ($allocations->count() > 1) {
                $baseNo = $data['payment_no'];
                $rowPayloads = [];

                foreach ($allocations as $idx => $alloc) {
                    $rowNo = $baseNo . '-' . ($idx + 1);
                    while (AccountingPayment::withoutGlobalScopes()->withTrashed()->where('payment_no', $rowNo)->exists()) {
                        $rowNo .= '-' . random_int(10, 99);
                    }

                    $rowPayload = array_merge($data, [
                        'payment_no' => $rowNo,
                        'invoice_id' => (int) $alloc['invoice_id'],
                        'amount' => (float) ($alloc['amount'] ?? 0),
                        'tds_tax' => (float) ($alloc['tds_tax'] ?? 0),
                        'discount' => (float) ($alloc['discount'] ?? 0),
                    ]);

                    if ($err = $this->guardInvoiceSettlement($companyId, $rowPayload)) {
                        return ['error' => $err];
                    }

                    $rowPayloads[] = $rowPayload;
                }

                $createdPayments = collect();
                foreach ($rowPayloads as $rowPayload) {
                    $created = AccountingPayment::create($rowPayload);
                    self::syncInvoiceBalance($created->invoice_id);
                    $createdPayments->push($created);
                }

                return ['payments' => $createdPayments];
            }

            if ($err = $this->guardInvoiceSettlement($companyId, $data)) {
                return ['error' => $err];
            }

            $payment = AccountingPayment::create($data);

            if ($payment->invoice_id) {
                self::syncInvoiceBalance($payment->invoice_id);
            }

            return ['payments' => collect([$payment])];
        });

        if (isset($txResult['error'])) {
            [$field, $msg] = $txResult['error'];
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$field => [$msg]]], 422);
            }
            return back()->withInput()->withErrors([$field => $msg]);
        }

        $createdPayments = $txResult['payments'];
        $payment = $createdPayments->first();
        $isBatch = $createdPayments->count() > 1;
        $successMsg = $isBatch
            ? "Payment recorded across {$createdPayments->count()} invoices successfully."
            : 'Payment saved successfully.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'payment' => $payment->fresh(),
                'payment_ids' => $createdPayments->pluck('id')->values(),
                'redirect' => route('accounting.payment.edit', $payment->id),
            ]);
        }

        $saveAction = $request->input('save_action', 'save_close');
        if ($saveAction === 'save_new') {
            $redirectRoute = $payment->type === 'MADE' ? 'accounting.payment-make' : 'accounting.payment-receive';
            return redirect()->route($redirectRoute)
                ->with('success', $successMsg . ' Create another.');
        }

        return redirect()->route('accounting.payment.edit', $payment->id)
            ->with('success', $successMsg);
    }

    public function edit(AccountingPayment $payment)
    {
        $payment->load(['tradePartner', 'currency', 'invoice.invoiceable', 'documents', 'memos.user', 'office', 'bankCurrency']);

        $tradePartners = TradePartner::orderBy('name')->get();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        $invoiceType = $payment->type === 'MADE' ? 'AP' : 'AR';
        $selectedInvoiceId = $payment->invoice_id;
        $selectedInvoiceIds = $selectedInvoiceId ? [(string) $selectedInvoiceId] : [];
        $selectedInvoice = $payment->invoice;
        $targetPartnerId = $payment->trade_partner_id ?? $selectedInvoice?->bill_to_id;

        $invoiceQuery = Invoice::with(['billTo', 'currency', 'office', 'invoiceable']);

        if ($targetPartnerId) {
            $invoiceQuery->where(function($q) use ($targetPartnerId, $invoiceType, $selectedInvoiceIds) {
                $q->where('bill_to_id', $targetPartnerId);
                if (!empty($selectedInvoiceIds)) {
                    $q->orWhereIn('id', $selectedInvoiceIds);
                }
                $q->orWhere(function($sub) use ($invoiceType) {
                    $sub->where('type', $invoiceType)
                        ->where('status', '!=', 'PAID');
                });
            });
        } else {
            $invoiceQuery->where('type', $invoiceType);
        }

        $invoices = $invoiceQuery->orderBy('invoice_date', 'desc')->get();

        $paymentNo = $payment->payment_no;
        $type = $payment->type;

        $companyId = auth()->user()?->company_id ?? 1;
        $banks = DB::table('banks')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        if ($payment->bank_name && !$banks->contains('bank_name', $payment->bank_name)) {
            $existingBank = DB::table('banks')
                ->where('company_id', $companyId)
                ->where('bank_name', $payment->bank_name)
                ->first();
            if ($existingBank) {
                $banks->push($existingBank);
            } else {
                $banks->push((object)[
                    'id' => null,
                    'bank_name' => $payment->bank_name,
                    'currency' => $payment->bankCurrency?->code ?? 'USD',
                    'gl_no' => '',
                    'is_active' => true,
                ]);
            }
        }

        $defaultBank = $payment->type === 'MADE'
            ? $banks->firstWhere('cost_default', 1)
            : $banks->firstWhere('revenue_default', 1);
        $defaultBankName = $payment->bank_name ?: ($defaultBank?->bank_name ?? '');

        $viewName = $payment->type === 'MADE' ? 'accounting.payment-make' : 'accounting.payment-receive';
        return view($viewName, compact(
            'payment', 'type', 'tradePartners', 'currencies', 'offices',
            'invoices', 'paymentNo', 'selectedInvoiceId', 'selectedInvoiceIds', 'selectedInvoice',
            'banks', 'defaultBankName'
        ));
    }

    public function update(Request $request, AccountingPayment $payment)
    {
        $newDate = $request->input('payment_date', $payment->payment_date);
        $newOfficeId = $request->input('office_id', $payment->office_id);
        if (
            $payment->is_blocked
            || \App\Models\AccountingBlockDate::isDateBlocked($payment->payment_date, $payment->office_id)
            || \App\Models\AccountingBlockDate::isDateBlocked($newDate, $newOfficeId)
        ) {
            $msg = 'Access Control: This payment cannot be modified because the record or accounting period is blocked.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            abort(403, $msg);
        }

        $companyId = auth()->user()?->company_id ?? 1;
        $cleanMethod = strtoupper(str_replace([' ', '_', '-'], '', (string)$request->input('payment_method')));
        $isCash = $cleanMethod === 'CASH';

        $bankRule = [$isCash ? 'nullable' : 'required', 'string', 'max:200'];
        if ($request->filled('bank_name')) {
            $bankRule[] = Rule::exists('banks', 'bank_name')->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        $data = $request->validate([
            'payment_no' => 'required|string|max:255|unique:payments,payment_no,' . $payment->id,
            'payment_date' => 'required|date',
            'trade_partner_id' => 'required|exists:trade_partners,id',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'nullable|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'tds_tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
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
            'bank_name' => $bankRule,
            'bank_currency_id' => 'nullable|exists:currencies,id',
        ], [
            'bank_name.required' => 'Bank selection is mandatory from Settings for this payment method.',
            'bank_name.exists' => 'The selected Bank is invalid or not registered in Settings. Please select a valid bank.',
        ]);

        $data['reference_no'] = $data['reference_no'] ?? '';
        $data['remark'] = $data['remark'] ?? '';
        $data['amount'] = $data['amount'] ?? 0;
        $data['tds_tax'] = (float) ($data['tds_tax'] ?? $request->input('tds_tax', 0));
        $data['discount'] = (float) ($data['discount'] ?? $request->input('discount', 0));
        $data['show_party_on_check'] = $request->boolean('show_party_on_check');

        if (empty($data['exchange_rate'])) {
            $curr = Currency::find($data['currency_id'] ?? $payment->currency_id ?? 1);
            $data['exchange_rate'] = (float) ($curr?->exchange_rate ?? 1.0);
        }

        // If clear/void checkboxes are unchecked, null the dates (while preserving explicit void_date when sent without checkbox flag)
        if ($request->has('has_clear') && !$request->boolean('has_clear')) $data['clear_date'] = null;
        if ($request->has('has_void') && !$request->boolean('has_void')) $data['void_date'] = null;

        $oldInvoiceId = $payment->invoice_id;

        $txResult = DB::transaction(function () use ($request, $payment, $data, $oldInvoiceId, $companyId) {
            if ($err = $this->guardInvoiceSettlement($companyId, $data, $payment->id)) {
                return $err;
            }

            $payment->update($data);

            if ($payment->invoice_id) {
                self::syncInvoiceBalance($payment->invoice_id);
            }

            if ($oldInvoiceId && $oldInvoiceId != $payment->invoice_id) {
                self::syncInvoiceBalance($oldInvoiceId);
            }

            return null;
        });

        if ($txResult !== null) {
            [$field, $msg] = $txResult;
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'errors' => [$field => [$msg]]], 422);
            }
            return back()->withInput()->withErrors([$field => $msg]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully.',
                'payment' => $payment->fresh(),
            ]);
        }

        return redirect()->route('accounting.payment.edit', $payment->id)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Guard against duplicate reference+amount and cash+TDS+discount overpayment on invoice settlement.
     */
    private function guardInvoiceSettlement(int $companyId, array $data, ?int $ignorePaymentId = null): ?array
    {
        if (empty($data['invoice_id']) || !empty($data['void_date'])) {
            return null;
        }

        $invoice = Invoice::withoutGlobalScopes()
            ->where('id', $data['invoice_id'])
            ->where('company_id', $companyId)
            ->lockForUpdate()
            ->first();

        if (!$invoice) {
            return ['invoice_id', 'The selected invoice is invalid or does not belong to your organization.'];
        }

        if (!in_array(strtoupper($invoice->status), ['POSTED', 'PARTIAL', 'PARTIALLY_PAID']) || $invoice->verification_status !== 'Verified') {
            return ['invoice_id', "Invoice {$invoice->invoice_no} must be verified and posted before payments can be applied."];
        }

        if (isset($data['type'])) {
            if ($data['type'] === 'RECEIVED' && $invoice->type === 'AP') {
                return ['type', "Cannot record a RECEIVED payment against an Accounts Payable bill ({$invoice->invoice_no})."];
            }
            if ($data['type'] === 'MADE' && $invoice->type === 'AR') {
                return ['type', "Cannot record a MADE payment against an Accounts Receivable invoice ({$invoice->invoice_no})."];
            }
        }

        if (!empty($data['currency_id']) && $invoice->currency_id && (int) $data['currency_id'] !== (int) $invoice->currency_id) {
            return ['currency_id', "Payment currency must match invoice currency ({$invoice->currency?->code})."];
        }

        if (!empty($data['reference_no'])) {
            $duplicate = AccountingPayment::where('company_id', $companyId)
                ->where('invoice_id', $invoice->id)
                ->where('reference_no', $data['reference_no'])
                ->where('amount', $data['amount'])
                ->whereNull('void_date')
                ->whereNull('cancelled_at')
                ->when($ignorePaymentId, fn($q) => $q->where('id', '!=', $ignorePaymentId))
                ->exists();

            if ($duplicate) {
                return [
                    'reference_no',
                    "Duplicate payment rejected: A payment with reference '{$data['reference_no']}' for \${$data['amount']} has already been processed for this invoice.",
                ];
            }
        }

        if ((float) $invoice->total_amount > 0) {
            $otherSettled = (float) (DB::table('payments')
                ->where('invoice_id', $invoice->id)
                ->whereNull('deleted_at')
                ->whereNull('void_date')
                ->whereNull('cancelled_at')
                ->when($ignorePaymentId, fn($q) => $q->where('id', '!=', $ignorePaymentId))
                ->selectRaw('COALESCE(SUM(amount + COALESCE(tds_tax, 0) + COALESCE(discount, 0)), 0) as settled')
                ->value('settled') ?? 0);

            $remainingBalance = max(0, round((float) $invoice->total_amount - $otherSettled, 2));
            $totalSettlement = (float) $data['amount'] + (float) ($data['tds_tax'] ?? 0) + (float) ($data['discount'] ?? 0);

            if ($totalSettlement > $remainingBalance + 0.001) {
                return [
                    'amount',
                    "Total settlement (\${$totalSettlement}) exceeds remaining invoice balance (\${$remainingBalance}). Overpayments must be logged as Customer Advance.",
                ];
            }
        }

        return null;
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

        $invoice = Invoice::withoutGlobalScopes()->find($invoiceId);
        if (!$invoice || strtoupper($invoice->status ?? '') === 'VOID') return;

        $paymentsQuery = DB::table('payments')
            ->where('invoice_id', $invoice->id)
            ->whereNull('deleted_at')
            ->whereNull('void_date')
            ->whereNull('cancelled_at');

        $totalPaid = (float) (clone $paymentsQuery)->sum('amount');
        $totalTds = (float) (clone $paymentsQuery)->sum('tds_tax');
        $totalDiscount = (float) (clone $paymentsQuery)->sum('discount');
        $totalSettled = $totalPaid + $totalTds + $totalDiscount;

        $invoice->paid_amount = $totalPaid;
        $invoice->balance_amount = max(0, round($invoice->total_amount - $totalSettled, 2));

        if ($invoice->balance_amount <= 0 && $invoice->total_amount > 0) {
            $invoice->status = 'PAID';
        } elseif ($totalSettled > 0) {
            $invoice->status = 'PARTIAL';
        } elseif (strtoupper($invoice->status ?? '') !== 'DRAFT') {
            $invoice->status = 'POSTED';
        }

        $invoice->saveQuietly();
    }
}
