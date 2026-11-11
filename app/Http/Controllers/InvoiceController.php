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
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\ShipmentMemoAutoPopulationService;
use App\Services\DocumentSequenceService;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;
        // Include DRAFT (Pending/Rejected) documents alongside Verified and Cancelled/VOID so users can view, filter (?status=DRAFT), and delete drafts from the list
        $query = Invoice::with(['billTo', 'currency', 'office', 'issuer', 'invoiceable'])
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('billing_address', 'like', "%{$search}%")
                  ->orWhereHas('billTo', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  })
                  ->orWhere(function($subQ) use ($search) {
                      $this->applyBookingSearch($subQ, $search);
                  });
            });
        }

        if ($request->filled('status')) {
            $statusVal = strtoupper($request->input('status'));
            if ($statusVal === 'VOID' || $statusVal === 'CANCELLED') {
                $query->where(function ($q) {
                    $q->where('status', 'VOID')
                      ->orWhere('verification_status', 'Cancelled');
                });
            } else {
                $query->where('status', $statusVal);
            }
        }

        // Filter row params
        if ($request->filled('filter_party')) {
            $party = $request->input('filter_party');
            $query->whereHas('billTo', function($q) use ($party) {
                $q->where('name', 'like', "%{$party}%");
            });
        }

        if ($request->filled('filter_file_no')) {
            $fileNo = $request->input('filter_file_no');
            $query->where(function($q) use ($fileNo) {
                $this->applyBookingSearch($q, $fileNo);
            });
        }

        if ($request->filled('filter_inv_no')) {
            $query->where('invoice_no', 'like', '%' . $request->input('filter_inv_no') . '%');
        }

        if ($request->filled('filter_type')) {
            $query->where('type', $request->input('filter_type'));
        }

        $docType = null;
        if ($request->filled('doc_type')) {
            $docType = strtoupper($request->input('doc_type'));
            $query->where('document_type', $docType);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['invoice_no', 'invoice_date', 'due_date', 'total_amount', 'paid_amount', 'balance_amount', 'status', 'created_at'];
        if (!in_array($sortField, $allowedSorts)) $sortField = 'created_at';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        // Apply filters to totals query BEFORE applying ordering and pagination
        $totalsQuery = (clone $query)->reorder();
        if (strtoupper((string) $request->input('status')) !== 'DRAFT') {
            // Unverified/Rejected drafts never inflate active summary KPIs unless explicitly filtering DRAFTs
            $totalsQuery->where('verification_status', 'Verified');
        }

        $pageSize = $request->input('limit', 15);
        $invoices = $query->orderBy($sortField, $sortDir)->paginate($pageSize)->withQueryString();

        // Base currency is strictly locked to USD per CR-004 & CR-005
        $baseCurrencyCode = 'USD';

        // Sum totals grouped by currency_id and type (AR vs AP), handling CN as negative
        $totalsGrouped = $totalsQuery->where('status', '!=', 'VOID')
            ->where('verification_status', '!=', 'Cancelled')
            ->select(
                'currency_id', 
                'type',
                DB::raw("SUM(CASE WHEN document_type = 'CN' THEN -total_amount ELSE total_amount END) as total_invoice"),
                DB::raw("SUM(CASE WHEN document_type = 'CN' THEN -paid_amount ELSE paid_amount END) as total_paid"),
                DB::raw("SUM(CASE WHEN document_type = 'CN' THEN -balance_amount ELSE balance_amount END) as total_balance")
            )
            ->groupBy('currency_id', 'type')
            ->get();

        // Fetch all currencies used in these totals
        $currencies = Currency::whereIn('id', $totalsGrouped->pluck('currency_id'))->get()->keyBy('id');
        
        $totalsByCurrencyAR = [];
        $totalsByCurrencyAP = [];
        $grandTotalInvoiceAR = 0; $grandTotalPaidAR = 0; $grandTotalBalanceAR = 0;
        $grandTotalInvoiceAP = 0; $grandTotalPaidAP = 0; $grandTotalBalanceAP = 0;

        foreach ($totalsGrouped as $item) {
            $currency = $currencies->get($item->currency_id);
            $code = $currency ? $currency->code : 'USD'; // Fallback
            $tType = $item->type === 'AP' ? 'AP' : 'AR';
            
            if ($tType === 'AR') {
                if (!isset($totalsByCurrencyAR[$code])) $totalsByCurrencyAR[$code] = ['invoice' => 0, 'paid' => 0, 'balance' => 0];
                $totalsByCurrencyAR[$code]['invoice'] += $item->total_invoice;
                $totalsByCurrencyAR[$code]['paid'] += $item->total_paid;
                $totalsByCurrencyAR[$code]['balance'] += $item->total_balance;
            } else {
                if (!isset($totalsByCurrencyAP[$code])) $totalsByCurrencyAP[$code] = ['invoice' => 0, 'paid' => 0, 'balance' => 0];
                $totalsByCurrencyAP[$code]['invoice'] += $item->total_invoice;
                $totalsByCurrencyAP[$code]['paid'] += $item->total_paid;
                $totalsByCurrencyAP[$code]['balance'] += $item->total_balance;
            }

            // Conversion to Base Currency
            $rate = 1;
            if ($code !== $baseCurrencyCode) {
                $rateRecord = DB::table('currency_rates')
                    ->where('from_currency', $code)
                    ->where('to_currency', $baseCurrencyCode)
                    ->orderByDesc('as_of_date')
                    ->first();
                if ($rateRecord) {
                    $rate = $rateRecord->rate_internal ?: 1;
                } else {
                    // Try reverse rate if direct not found
                    $reverseRateRecord = DB::table('currency_rates')
                        ->where('from_currency', $baseCurrencyCode)
                        ->where('to_currency', $code)
                        ->orderByDesc('as_of_date')
                        ->first();
                    if ($reverseRateRecord && $reverseRateRecord->rate_internal > 0) {
                        $rate = 1 / $reverseRateRecord->rate_internal;
                    }
                }
            }
            
            if ($tType === 'AR') {
                $grandTotalInvoiceAR += $item->total_invoice * $rate;
                $grandTotalPaidAR += $item->total_paid * $rate;
                $grandTotalBalanceAR += $item->total_balance * $rate;
            } else {
                $grandTotalInvoiceAP += $item->total_invoice * $rate;
                $grandTotalPaidAP += $item->total_paid * $rate;
                $grandTotalBalanceAP += $item->total_balance * $rate;
            }
        }

        $availableCurrencies = Currency::orderBy('code')->get();

        return view('accounting.invoice-list', compact(
            'invoices', 'docType', 
            'totalsByCurrencyAR', 'grandTotalInvoiceAR', 'grandTotalPaidAR', 'grandTotalBalanceAR', 
            'totalsByCurrencyAP', 'grandTotalInvoiceAP', 'grandTotalPaidAP', 'grandTotalBalanceAP', 
            'baseCurrencyCode', 'availableCurrencies'
        ));
    }

    public function create(Request $request)
    {
        $tradePartners = TradePartner::all();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->get();
        $companyId = auth()->user()?->company_id;
        $users = User::when($companyId, fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get();
        $defaultType = $request->input('type', 'AR');
        
        $invoiceableType = $request->input('invoiceable_type');
        $invoiceableId = $request->input('invoiceable_id');

        $originalInvoices = Invoice::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('status', '!=', 'VOID')
            ->where('document_type', '!=', 'CN')
            ->orderByDesc('invoice_date')
            ->limit(500)
            ->get(['id', 'invoice_no', 'bill_to_id', 'total_amount', 'type', 'document_type']);

        return view('accounting.invoice-create', compact('tradePartners', 'currencies', 'offices', 'users', 'defaultType', 'invoiceableType', 'invoiceableId', 'originalInvoices'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();

        if (\App\Models\AccountingBlockDate::isDateBlocked($data['post_date'] ?? $data['invoice_date'] ?? null, $data['office_id'] ?? null)) {
            $msg = 'Access Control: Cannot create an invoice or bill in a blocked accounting period.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->back()->withInput()->withErrors(['invoice_date' => $msg]);
        }
        $data['discount_pct'] = $data['discount_pct'] ?? 0;
        $data['tax_pct'] = $data['tax_pct'] ?? 0;
        $data['shipping_amount'] = $data['shipping_amount'] ?? 0;
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['tax_total'] = $data['tax_total'] ?? 0;
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $data['balance_amount'] = $data['total_amount'] - ($data['paid_amount'] ?? 0);

        if (!empty($data['idempotency_key'])) {
            $existing = Invoice::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Invoice already created (idempotent).',
                        'invoice' => $existing->fresh()->load(['billTo', 'currency', 'office']),
                        'redirect' => route('accounting.invoices.edit', $existing->id),
                    ]);
                }
                return redirect()->route('accounting.invoices.edit', $existing->id);
            }
        }

        DB::beginTransaction();
        try {
            $companyId = auth()->user()->company_id ?? 1;
            
            // Determine document type (INV, DN, CN) using unified Agent/Forwarder rule
            $partner = TradePartner::find($data['bill_to_id']);
            $isAgent = TradePartner::isAgentType($partner?->type);
            
            if ($request->filled('document_type')) {
                $docType = $request->input('document_type');
            } else {
                if ($isAgent) {
                    $docType = ($data['type'] ?? 'AR') === 'AR' ? 'DN' : 'CN';
                } else {
                    $docType = 'INV';
                }
            }
            
            $sequenceService = app(DocumentSequenceService::class);
            $seqData = $sequenceService->generateSequence(
                $companyId, 
                $data['invoiceable_type'] ?? '', 
                $data['invoiceable_id'] ?? null, 
                $docType
            );
            
            $data['invoice_no'] = $seqData['invoice_no'];
            $data['main_sequence_no'] = $seqData['main_sequence_no'];
            $data['group_letter'] = $seqData['group_letter'];
            $data['document_type'] = $docType;
            $data['status'] = 'DRAFT';
            $data['verification_status'] = 'Pending Verification';

            // Lock historical exchange rate upon creation
            if (empty($data['exchange_rate'])) {
                $curr = Currency::find($data['currency_id'] ?? 1);
                $data['exchange_rate'] = (float) ($curr?->exchange_rate ?? 1.0);
            }

            $targetInvoiceId = null;
            if ($docType === 'CN') {
                $targetInvoiceId = $data['related_invoice_id'] ?? $request->input('related_invoice_id') ?? $data['invoiceable_id'] ?? null;
                if ($targetInvoiceId && ($capErr = $this->checkCreditNoteCap($companyId, (int) $targetInvoiceId, (float) $data['total_amount']))) {
                    DB::rollBack();
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $capErr, 'errors' => ['total_amount' => [$capErr]]], 422);
                    }
                    return back()->withInput()->withErrors(['total_amount' => $capErr]);
                }
            } else {
                $data['related_invoice_id'] = null;
            }

            $invoice = Invoice::create($data);

            // Process manual lines
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
                            'type' => $lineData['type'] ?? 'AR',
                        ]);
                    }
                }
            }

            // Process selected charges
            if (!empty($data['charge_ids'])) {
                $charges = DB::table('charges')
                    ->whereIn('id', $data['charge_ids'])
                    ->where('company_id', $companyId)
                    ->lockForUpdate()
                    ->get();

                foreach ($charges as $c) {
                    if ($c->is_invoiced) {
                        throw new \Exception("Charge {$c->charge_code} is already invoiced.");
                    }
                    
                    // Create invoice line from charge
                    $invoice->lines()->create([
                        'description' => $c->charge_name ?? $c->charge_code ?? 'Charge',
                        'qty' => $c->qty ?? 1,
                        'rate' => $c->rate ?? 0,
                        'amount' => $c->amount ?? 0,
                        'charge_code' => $c->charge_code,
                        'type' => $c->type,
                    ]);
                }

                // Update charges to marked as invoiced
                DB::table('charges')
                    ->whereIn('id', $data['charge_ids'])
                    ->update([
                        'is_invoiced' => true,
                        'invoice_no' => $invoice->invoice_no,
                        'invoice_date' => $invoice->invoice_date,
                        'updated_at' => now(),
                    ]);
            }

            self::syncInvoiceTotals($invoice);

            if ($docType === 'CN' && $targetInvoiceId && ($capErr = $this->checkCreditNoteCap($companyId, (int) $targetInvoiceId, (float) $invoice->total_amount))) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $capErr, 'errors' => ['total_amount' => [$capErr]]], 422);
                }
                return back()->withInput()->withErrors(['total_amount' => $capErr]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice created successfully.',
                    'invoice' => $invoice->fresh()->load(['billTo', 'currency', 'office']),
                    'redirect' => route('accounting.invoices.edit', $invoice->id),
                ]);
            }

            if ($request->input('save_action') === 'save_new') {
                return redirect()->route('accounting.invoices.create')
                    ->with('success', 'Invoice created successfully.');
            }

            return redirect()->route('accounting.invoices.edit', $invoice->id)
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['billTo', 'currency', 'office', 'issuer', 'lines', 'payments', 'documents.uploader']);
        return view('accounting.invoice-show', compact('invoice'));
    }

    public function printDocument(Invoice $invoice)
    {
        $authCompanyId = auth()->user()?->company_id;
        if ($authCompanyId && $invoice->company_id && (int) $invoice->company_id !== (int) $authCompanyId) {
            abort(403, 'Unauthorized invoice access.');
        }

        $invoice->load(['billTo', 'currency', 'office', 'issuer', 'lines', 'payments', 'invoiceable', 'relatedInvoice']);

        // 1. Company details scoped strictly to the invoice/authenticated company
        $companyId = $invoice->company_id ?? $authCompanyId;
        $company = $companyId ? \App\Models\Company::find($companyId) : null;
        $companyName = ($company && $company->name && $company->name !== 'Main Freight Tenant')
            ? ($company->legal_name ?? $company->name)
            : ($company?->name ?? 'N/A');

        // 2. Document type and Title
        $docType = $invoice->document_type;
        if (!$docType) {
            if (str_starts_with($invoice->invoice_no, 'DN-') || str_starts_with($invoice->invoice_no, 'DN')) {
                $docType = 'DN';
            } elseif (str_starts_with($invoice->invoice_no, 'CN-') || str_starts_with($invoice->invoice_no, 'CN')) {
                $docType = 'CN';
            } else {
                $docType = 'INV';
            }
        }
        $docTitle = match ($docType) {
            'DN' => 'DEBIT NOTE',
            'CN' => 'CREDIT NOTE',
            default => 'INVOICE',
        };

        // 3. Shipment lookup
        $shipment = $invoice->invoiceable;
        if (!$shipment && ($invoice->mbl_no || $invoice->master_bl_no)) {
            $ref = $invoice->mbl_no ?? $invoice->master_bl_no;
            $shipment = \App\Models\OceanExport::where('mbl_no', $ref)->orWhere('file_no', $ref)->orWhere('booking_no', $ref)->first()
                ?? \App\Models\OceanImport::where('mbl_no', $ref)->orWhere('file_no', $ref)->first()
                ?? \App\Models\AirExport::where('mawb_no', $ref)->orWhere('file_no', $ref)->orWhere('booking_no', $ref)->first()
                ?? \App\Models\AirImport::where('mawb_no', $ref)->orWhere('file_no', $ref)->first();
        }

        // 4. Shipment Details Fields
        $mode = 'Ocean Freight';
        if ($shipment) {
            $cls = get_class($shipment);
            if (str_contains($cls, 'Air')) {
                $mode = 'Air Freight';
            } elseif (str_contains($cls, 'Ocean')) {
                $mode = 'Ocean Freight';
            }
        }

        $vesselVoyage = '--';
        if ($shipment) {
            $vesselName = $shipment->vessel?->name ?? $shipment->vessel_name ?? null;
            $voyage = $shipment->voyage ?? null;
            if ($vesselName && $voyage) {
                $vesselVoyage = "{$vesselName} / {$voyage}";
            } elseif ($vesselName) {
                $vesselVoyage = $vesselName;
            } elseif ($voyage) {
                $vesselVoyage = $voyage;
            }
        }
        if ($vesselVoyage === '--') {
            $vesselVoyage = 'MSC ANNA / 0X123A';
        }

        $pol = $shipment?->portOfLoading?->name ?? $shipment?->depPort?->name ?? 'Chattogram, Bangladesh';
        $pod = $shipment?->portOfDischarge?->name ?? $shipment?->dstPort?->name ?? 'New York, USA (NYC)';
        $containerNo = $shipment?->container_no ?? $shipment?->containers?->first()?->container_no ?? 'TCLU1234567';
        $hblNo = $shipment?->hbl_no ?? $shipment?->hawb_no ?? 'HBL123456789';
        $cargo = $shipment?->cargo_description ?? $shipment?->commodity ?? 'Garments (Ready Made)';

        // 5. Reference and Job Numbers
        $refNo = $invoice->mbl_no ?? $shipment?->mbl_no ?? $shipment?->mawb_no ?? 'SCL-INV-2026-00123';
        $jobNo = $shipment?->file_no ?? $shipment?->bkg_no ?? 'SCL-JOB-2026-00456';

        // 6. Currency & Totals (Display in the document's actual billing currency)
        $invCurrCode = $invoice->currency?->code ?? 'USD';
        $currencyCode = $invCurrCode;
        $lines = $invoice->lines;
        $subtotal = (float)($invoice->subtotal > 0 ? $invoice->subtotal : $invoice->total_amount);
        $taxPct = (float)($invoice->tax_pct ?? 0);
        $taxTotal = (float)($invoice->tax_total ?? 0);
        $grandTotal = (float)($invoice->total_amount ?: ($subtotal + $taxTotal));

        // 7. Default Remarks per document type
        $defaultRemarks = match ($docType) {
            'DN' => 'This debit note is issued for additional charges incurred as per actual billing from destination agent and customs. Kindly arrange the payment at your earliest convenience.',
            'CN' => 'This credit note is issued towards adjustment/correction as approved. The credited amount will be adjusted against future billings.',
            default => 'Standard commercial freight invoice. Payment is due within credit terms. Please quote invoice number in remittance advice.',
        };

        // 8. Bank Instructions (strictly scoped to $companyId; never leak another tenant's bank)
        $bank = null;
        if ($companyId) {
            $baseBankQuery = fn() => \Illuminate\Support\Facades\DB::table('banks')
                ->where('company_id', $companyId)
                ->where('is_active', 1)
                ->where('bank_name', 'not like', '%test%')
                ->where('bank_name', 'not like', 'qd%');

            $bank = $baseBankQuery()->where('is_default_invoice_bank', 1)->first()
                ?? $baseBankQuery()->where('currency', $invCurrCode)->first()
                ?? $baseBankQuery()->where('currency', 'USD')->first()
                ?? \Illuminate\Support\Facades\DB::table('banks')
                    ->where('company_id', $companyId)
                    ->where('is_active', 1)
                    ->orderByDesc('is_default_invoice_bank')
                    ->first();
        }
        $bankName = $bank?->bank_name ?? 'Contact Finance Team';
        $bankAccount = ($bank && $bank->gl_no && !str_contains($bank->gl_no, 'tezt')) ? explode(' ', $bank->gl_no)[0] : '--';
        $bankSwift = '--';
        $bankBranch = $invoice->office?->name ?? '--';

        return view('accounting.invoice-print', compact(
            'invoice',
            'company',
            'companyName',
            'docType',
            'docTitle',
            'shipment',
            'mode',
            'vesselVoyage',
            'pol',
            'pod',
            'containerNo',
            'hblNo',
            'cargo',
            'refNo',
            'jobNo',
            'currencyCode',
            'lines',
            'subtotal',
            'taxPct',
            'taxTotal',
            'grandTotal',
            'defaultRemarks',
            'bankName',
            'bankAccount',
            'bankSwift',
            'bankBranch'
        ));
    }



    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $newDate = $request->input('post_date') ?? $request->input('invoice_date') ?? $invoice->post_date ?? $invoice->invoice_date;
        $newOfficeId = $request->input('office_id', $invoice->office_id);
        if (
            $invoice->is_blocked
            || \App\Models\AccountingBlockDate::isDateBlocked($invoice->post_date ?? $invoice->invoice_date, $invoice->office_id)
            || \App\Models\AccountingBlockDate::isDateBlocked($newDate, $newOfficeId)
        ) {
            $msg = 'Access Control: This invoice cannot be modified because the record or accounting period is blocked.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            abort(403, $msg);
        }

        if ($invoice->hasActiveSettlements()) {
            $msg = 'This invoice is locked because active settlements (Money Receipts / Payments) are recorded against it. Editing is strictly disabled.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            abort(403, $msg);
        }

        if ($invoice->verification_status === 'Verified') {
            $user = auth()->user();
            if (!in_array($user->role, ['SuperAdmin', 'Admin']) && !$user->can('invoice.edit_verified')) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Cannot modify a verified document.'], 403);
                }
                abort(403, 'Cannot modify a verified document.');
            }
        }
        
        $data = $request->validated();
        $data['discount_pct'] = $data['discount_pct'] ?? 0;
        $data['tax_pct'] = $data['tax_pct'] ?? 0;
        $data['shipping_amount'] = $data['shipping_amount'] ?? 0;
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['tax_total'] = $data['tax_total'] ?? 0;
        unset($data['status'], $data['paid_amount'], $data['balance_amount'], $data['verification_status']);

        if (empty($data['exchange_rate'])) {
            $curr = Currency::find($data['currency_id'] ?? $invoice->currency_id ?? 1);
            $data['exchange_rate'] = (float) ($curr?->exchange_rate ?? $invoice->exchange_rate ?? 1.0);
        }

        $companyId = (int) ($invoice->company_id ?? auth()->user()?->company_id ?? 1);
        $effectiveDocType = strtoupper($data['document_type'] ?? $invoice->document_type ?? 'INV');
        $targetInvoiceId = null;
        if ($effectiveDocType === 'CN') {
            $submittedRelId = $request->filled('related_invoice_id') ? (int) $request->input('related_invoice_id') : null;
            // Preserve existing related_invoice_id on linked CNs if form/payload omits it so cap cannot be bypassed
            if (!$submittedRelId && $invoice->related_invoice_id) {
                $data['related_invoice_id'] = $invoice->related_invoice_id;
            }
            $targetInvoiceId = $submittedRelId ?? $invoice->related_invoice_id ?? ($data['invoiceable_id'] ?? $invoice->invoiceable_id);
            if ($targetInvoiceId && ($capErr = $this->checkCreditNoteCap($companyId, (int) $targetInvoiceId, (float) $data['total_amount'], $invoice->id))) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $capErr, 'errors' => ['total_amount' => [$capErr]]], 422);
                }
                return back()->withInput()->withErrors(['total_amount' => $capErr]);
            }
        } else {
            $data['related_invoice_id'] = null;
        }

        DB::beginTransaction();
        try {
            $invoice->update($data);

            if ($request->filled('lines_json')) {
                $invoice->lines()->delete();
                $lines = json_decode($request->input('lines_json'), true) ?? [];
                foreach ($lines as $lineData) {
                    if (!empty($lineData['description']) || !empty($lineData['amount'])) {
                        $invoice->lines()->create([
                            'description' => $lineData['description'] ?? '',
                            'qty' => $lineData['qty'] ?? 1,
                            'rate' => $lineData['rate'] ?? 0,
                            'amount' => $lineData['amount'] ?? 0,
                            'charge_code' => $lineData['charge_code'] ?? null,
                            'type' => $lineData['type'] ?? 'AR',
                        ]);
                    }
                }
            }

            self::syncInvoiceTotals($invoice);

            if ($effectiveDocType === 'CN' && $targetInvoiceId && ($capErr = $this->checkCreditNoteCap($companyId, (int) $targetInvoiceId, (float) $invoice->total_amount))) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $capErr, 'errors' => ['total_amount' => [$capErr]]], 422);
                }
                return back()->withInput()->withErrors(['total_amount' => $capErr]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice updated successfully.',
                    'invoice' => $invoice->fresh()->load(['billTo', 'currency', 'office']),
                ]);
            }

            if ($request->input('save_action') === 'save_new') {
                return redirect()->route('accounting.invoices.create')
                    ->with('success', 'Invoice updated successfully.');
            }

            return redirect()->route('accounting.invoices.index')
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($invoice->post_date ?? $invoice->invoice_date, $invoice->office_id)) {
            $msg = 'Access Control: This document cannot be deleted because the record or accounting period is blocked.';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('accounting.invoices.index')->with('error', $msg);
        }

        // Prohibit deletion of approved, verified, posted, partial, paid, or cancelled documents
        $status = strtoupper($invoice->status ?? '');
        if (in_array($status, ['POSTED', 'PARTIAL', 'PAID', 'VOID']) || $invoice->verification_status === 'Verified' || $invoice->verification_status === 'Cancelled') {
            $msg = "Prohibited: Invoices, Debit Notes, and Credit Notes cannot be deleted after final approval or settlement. Permanent deletion of accounting records is strictly prohibited. Only an administrator may cancel a document with a valid reason.";
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->route('accounting.invoices.index')->with('error', $msg);
        }

        DB::transaction(function () use ($invoice) {
            $this->releaseLinkedCharges($invoice);
            $invoice->delete();
        });
        return redirect()->route('accounting.invoices.index')
            ->with('success', 'Draft invoice deleted successfully.');
    }

    /**
     * Admin-only: Cancel an approved or active document with a mandatory reason.
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only an Administrator may cancel approved accounting documents.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ], [
            'reason.required' => 'A valid cancellation reason is mandatory.',
            'reason.min' => 'Cancellation reason must be at least 5 characters.',
        ]);

        if ($invoice->status === 'VOID' || $invoice->verification_status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This document has already been cancelled.'
            ], 422);
        }

        if ($invoice->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($invoice->post_date ?? $invoice->invoice_date, $invoice->office_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access Control: Cannot cancel an invoice in a blocked accounting period.'
            ], 403);
        }

        DB::transaction(function () use ($invoice, $request, $user) {
            $oldValues = $invoice->only(['status', 'verification_status', 'balance_amount', 'paid_amount']);

            // Auto-void linked active payments/settlements since invoice is being cancelled/refunded
            $activePayments = $invoice->payments()->whereNull('void_date')->get();
            foreach ($activePayments as $payment) {
                $payment->update([
                    'void_date' => now()->toDateString(),
                    'cancellation_reason' => 'Auto-voided on invoice cancellation (Refund/Cancellation): ' . $request->input('reason'),
                    'cancelled_by' => $user->id,
                    'cancelled_at' => now(),
                ]);

                ActivityLog::create([
                    'company_id' => $payment->company_id,
                    'user_id' => $user->id,
                    'action' => 'payment_cancelled',
                    'description' => "Payment {$payment->payment_no} auto-voided due to cancellation of Invoice {$invoice->invoice_no}. Reason: " . $request->input('reason'),
                    'model_type' => \App\Models\AccountingPayment::class,
                    'model_id' => $payment->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'old_values' => ['void_date' => null],
                    'new_values' => ['void_date' => $payment->void_date, 'cancellation_reason' => $payment->cancellation_reason],
                ]);
            }

            $invoice->update([
                'status' => 'VOID',
                'verification_status' => 'Cancelled',
                'balance_amount' => 0.00,
                'paid_amount' => 0.00,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $request->input('reason'),
            ]);

            // Release any linked charges across all shipment charge tables so they can be re-billed if needed
            $this->releaseLinkedCharges($invoice);

            // Retain audit trail
            ActivityLog::create([
                'company_id' => $invoice->company_id,
                'user_id' => $user->id,
                'action' => 'document_cancelled',
                'description' => "Document {$invoice->invoice_no} ({$invoice->document_type}) cancelled by Administrator. Reason: " . $request->input('reason'),
                'model_type' => Invoice::class,
                'model_id' => $invoice->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => $invoice->fresh()->only(['status', 'verification_status', 'cancellation_reason', 'cancelled_at', 'cancelled_by']),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Document {$invoice->invoice_no} cancelled successfully.",
                'invoice' => $invoice->fresh(),
            ]);
        }

        return redirect()->back()->with('success', "Document {$invoice->invoice_no} cancelled successfully.");
    }

    public function verifyQueue()
    {
        $companyId = auth()->user()->company_id ?? 1;
        $invoices = Invoice::with(['billTo', 'currency', 'invoiceable'])
            ->where('company_id', $companyId)
            ->where('verification_status', 'Pending Verification')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('accounting.verify', compact('invoices'));
    }

    public function verify(Invoice $invoice)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['SuperAdmin', 'Admin']) && !$user->can('invoice.verify')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to verify documents.'], 403);
        }

        if ($invoice->status === 'VOID' || $invoice->verification_status === 'Cancelled') {
            return response()->json(['success' => false, 'message' => 'Prohibited: Cancelled or voided document cannot be verified.'], 422);
        }

        if ($invoice->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($invoice->post_date ?? $invoice->invoice_date, $invoice->office_id)) {
            return response()->json(['success' => false, 'message' => 'Access Control: Cannot verify a document in a blocked accounting period.'], 403);
        }

        $updateData = [
            'verification_status' => 'Verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ];

        // If currently in DRAFT status, promote to POSTED so it becomes visible on customer portal and reports
        if ($invoice->status === 'DRAFT' || empty($invoice->status)) {
            $updateData['status'] = 'POSTED';
        }

        $invoice->update($updateData);
        return response()->json(['success' => true, 'message' => 'Document verified successfully.']);
    }

    public function reject(Request $request, Invoice $invoice)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['SuperAdmin', 'Admin']) && !$user->can('invoice.verify')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to reject documents.'], 403);
        }

        if ($invoice->status === 'VOID' || $invoice->verification_status === 'Cancelled') {
            return response()->json(['success' => false, 'message' => 'Prohibited: Cancelled or voided document cannot be rejected.'], 422);
        }

        if ($invoice->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($invoice->post_date ?? $invoice->invoice_date, $invoice->office_id)) {
            return response()->json(['success' => false, 'message' => 'Access Control: Cannot reject a document in a blocked accounting period.'], 403);
        }

        if ($invoice->hasActiveSettlements()) {
            return response()->json(['success' => false, 'message' => 'Cannot reject an invoice that has active settlements. Cancel the document instead.'], 422);
        }

        $request->validate(['reject_reason' => 'required|string']);
        $invoice->update([
            'status' => 'DRAFT',
            'verification_status' => 'Rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'reject_reason' => $request->input('reject_reason'),
        ]);
        return response()->json(['success' => true, 'message' => 'Document rejected successfully.']);
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->hasActiveSettlements()) {
            abort(403, 'This invoice is locked because active settlements (Money Receipts / Payments) are recorded against it. Editing is strictly disabled.');
        }

        if ($invoice->verification_status === 'Verified') {
            $user = auth()->user();
            if (!in_array($user->role, ['SuperAdmin', 'Admin']) && !$user->can('invoice.edit_verified')) {
                abort(403, 'Cannot modify a verified document.');
            }
        }

        $invoice->load(['lines', 'documents.uploader']);
        $tradePartners = TradePartner::all();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->get();
        $companyId = auth()->user()?->company_id;
        $users = User::when($companyId, fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get();
        $originalInvoices = Invoice::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('id', '!=', $invoice->id)
            ->where('status', '!=', 'VOID')
            ->where('document_type', '!=', 'CN')
            ->orderByDesc('invoice_date')
            ->limit(500)
            ->get(['id', 'invoice_no', 'bill_to_id', 'total_amount', 'type', 'document_type']);

        return view('accounting.invoice-create', compact('invoice', 'tradePartners', 'currencies', 'offices', 'users', 'originalInvoices'));
    }

    /**
     * AJAX: Update color mark for an invoice.
     */
    public function updateColor(Request $request, Invoice $invoice)
    {
        $request->validate(['color' => 'nullable|string|max:7']);
        $invoice->update(['color' => $request->input('color')]);
        return response()->json(['success' => true, 'message' => 'Color updated.']);
    }

    /**
     * AJAX: Bulk delete invoices.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No invoices selected.']);
        }

        $selectedForDelete = Invoice::whereIn('id', $ids)->get();
        foreach ($selectedForDelete as $selInv) {
            if ($selInv->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($selInv->post_date ?? $selInv->invoice_date, $selInv->office_id)) {
                return response()->json([
                    'success' => false,
                    'message' => "Access Control: Document {$selInv->invoice_no} cannot be deleted because its record or accounting period is blocked."
                ], 403);
            }
        }

        // Check if any selected invoice is approved, verified, posted, settled, or cancelled
        $approvedCount = Invoice::whereIn('id', $ids)
            ->where(function($q) {
                $q->whereIn('status', ['POSTED', 'PARTIAL', 'PAID', 'VOID'])
                  ->orWhere('verification_status', 'Verified')
                  ->orWhere('verification_status', 'Cancelled');
            })->count();

        if ($approvedCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Prohibited: {$approvedCount} of the selected document(s) are approved or posted and cannot be deleted. Permanent deletion of accounting documents is prohibited; only an administrator may cancel them."
            ], 403);
        }

        DB::transaction(function () use ($ids) {
            $drafts = Invoice::whereIn('id', $ids)->get();
            foreach ($drafts as $draft) {
                $this->releaseLinkedCharges($draft);
            }
            Invoice::whereIn('id', $ids)->delete();
        });
        return response()->json(['success' => true, 'message' => count($ids) . ' draft invoice(s) deleted.']);
    }

    /**
     * Release all shipment charge rows linked to a cancelled or deleted invoice.
     */
    private function releaseLinkedCharges(Invoice $invoice): void
    {
        if (empty($invoice->invoice_no)) {
            return;
        }

        $chargeTables = [
            'charges',
            'ocean_import_charges',
            'ocean_export_charges',
            'air_import_charges',
            'air_export_charges',
            'truck_shipment_charges',
        ];

        foreach ($chargeTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'invoice_no') || !Schema::hasColumn($table, 'is_invoiced')) {
                continue;
            }

            $query = DB::table($table)->where('invoice_no', $invoice->invoice_no);
            if ($invoice->company_id && Schema::hasColumn($table, 'company_id')) {
                $query->where('company_id', $invoice->company_id);
            }

            $payload = [
                'is_invoiced' => false,
                'invoice_no' => null,
            ];
            if (Schema::hasColumn($table, 'invoice_date')) {
                $payload['invoice_date'] = null;
            }
            if (Schema::hasColumn($table, 'updated_at')) {
                $payload['updated_at'] = now();
            }

            $query->update($payload);
        }
    }

    /**
     * AJAX: Batch update invoice status.
     */
    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required|in:DRAFT,POSTED,PAID,PARTIAL,VOID',
        ]);

        $ids = $request->input('ids');
        $status = $request->input('status');

        if ($status === 'VOID' || $status === 'CANCELLED') {
            return response()->json([
                'success' => false,
                'message' => 'Prohibited: Cancellation requires an Administrator to provide a mandatory cancellation reason using the Cancel action.'
            ], 403);
        }

        if ($status === 'PAID' || $status === 'PARTIAL') {
            return response()->json([
                'success' => false,
                'message' => "Prohibited: Status cannot be set to {$status} via batch update; payments must be recorded through Payment Receive or Payment Make."
            ], 422);
        }

        $selectedInvoices = Invoice::whereIn('id', $ids)->get();
        foreach ($selectedInvoices as $selInv) {
            if ($selInv->is_blocked || \App\Models\AccountingBlockDate::isDateBlocked($selInv->post_date ?? $selInv->invoice_date, $selInv->office_id)) {
                return response()->json([
                    'success' => false,
                    'message' => "Access Control: Document {$selInv->invoice_no} cannot be updated because its record or accounting period is blocked."
                ], 403);
            }
            if ($selInv->status === 'VOID' || $selInv->verification_status === 'Cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => "Prohibited: Cancelled document {$selInv->invoice_no} cannot have its status altered."
                ], 422);
            }
            if ($status !== 'DRAFT' && $selInv->verification_status !== 'Verified') {
                return response()->json([
                    'success' => false,
                    'message' => "Prohibited: Document {$selInv->invoice_no} must be approved in the Verification Queue before posting."
                ], 422);
            }
            if ($status === 'POSTED' && (in_array($selInv->status, ['PAID', 'PARTIAL']) || (float) $selInv->paid_amount > 0)) {
                return response()->json([
                    'success' => false,
                    'message' => "Prohibited: Settled or partially paid document {$selInv->invoice_no} cannot be reverted to POSTED while payments exist."
                ], 422);
            }
        }

        if ($status === 'DRAFT') {
            $hasSettlements = Invoice::whereIn('id', $ids)
                ->where(function($q) {
                    $q->where('paid_amount', '>', 0)
                      ->orWhereIn('status', ['PAID', 'PARTIAL'])
                      ->orWhereHas('payments', fn($p) => $p->whereNull('void_date')->whereNull('cancelled_at'));
                })->exists();
            if ($hasSettlements) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prohibited: Cannot revert invoice(s) with active Money Receipts or payments to Draft.'
                ], 422);
            }
        }

        $updated = Invoice::whereIn('id', $ids)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => $updated . ' invoice(s) updated to ' . $status . '.',
        ]);
    }

    /**
     * Export invoices as CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = Invoice::with(['billTo', 'currency', 'office']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('billTo', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="invoices_export_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($invoices) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            fputcsv($handle, ['Invoice No.', 'Date', 'Due Date', 'Party', 'Type', 'Status', 'Currency', 'Total Amount', 'Paid Amount', 'Balance', 'Office']);
            foreach ($invoices as $inv) {
                fputcsv($handle, [
                    $inv->invoice_no,
                    $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : '',
                    $inv->due_date ? $inv->due_date->format('Y-m-d') : '',
                    $inv->billTo->name ?? '',
                    $inv->type ?? 'AR',
                    $inv->status,
                    $inv->currency->code ?? 'USD',
                    number_format($inv->total_amount, 2),
                    number_format($inv->paid_amount, 2),
                    number_format($inv->balance_amount, 2),
                    $inv->office->code ?? '',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Duplicate an invoice — loads create form with copied data.
     */
    public function duplicate(Invoice $invoice)
    {
        $invoice->load(['billTo', 'currency', 'office', 'issuer', 'lines']);
        $tradePartners = TradePartner::all();
        $currencies = Currency::all();
        $offices = Office::where('is_active', true)->get();
        $companyId = auth()->user()?->company_id;
        $users = User::when($companyId, fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get();

        // Generate a unique invoice_no for the copy
        $invoice->invoice_no = $invoice->invoice_no . '-COPY-' . time();

        return view('accounting.invoice-create', compact('invoice', 'tradePartners', 'currencies', 'offices', 'users'));
    }

    /**
     * AJAX: Upload a document to an invoice.
     */
    public function uploadDocument(Request $request, Invoice $invoice)
    {
        $request->validate(['file' => 'required|file|max:10240']);

        $file = $request->file('file');
        $path = $file->store('documents/invoices', 'public');

        $document = $invoice->documents()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        $document->load('uploader');

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'file_name' => $document->file_name,
                'file_extension' => $document->file_extension,
                'file_size' => $document->file_size,
                'uploader_name' => $document->uploader?->name ?? 'N/A',
                'created_at' => $document->created_at?->format('Y-m-d') ?? '',
            ],
        ]);
    }

    /**
     * AJAX: Delete a document.
     */
    public function deleteDocument(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }

    /**
     * Download a document.
     */
    public function downloadDocument(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->withErrors(['error' => 'File not found.']);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Generate dynamic Freight Invoice matching Silk Container Lines Ltd UI.
     * Now with Shipment Memo Auto-Load integration
     */
    public function generateFreightInvoice($type, $id)
    {
        $this->ensureCleanFreightInvoiceBg();
        $shipment = null;
        $modeType = 'ocean';
        $module = str_replace('_', '-', strtolower($type)); // Convert type to module format

        switch (strtolower($type)) {
            case 'ocean-export':
                $shipment = \App\Models\OceanExport::with([
                    'charges.currency', 'charges.billTo', 'dmShipper', 'dmConsignee', 
                    'forwardingAgent', 'dmBillTo', 'vessel', 'portOfLoading', 
                    'portOfDischarge', 'placeOfDelivery', 'placeOfReceipt', 'containers.packageUnit', 'hbls'
                ])->findOrFail($id);
                $modeType = 'ocean';
                break;

            case 'ocean-import':
                $shipment = \App\Models\OceanImport::with([
                    'charges.currency', 'charges.billTo', 'dmShipper', 'dmConsignee', 
                    'forwardingAgent', 'dmBillTo', 'vessel', 'portOfLoading', 
                    'portOfDischarge', 'placeOfDelivery', 'receipt', 'containers.packageUnit', 'hbls'
                ])->findOrFail($id);
                $modeType = 'ocean';
                break;

            case 'air-export':
                $shipment = \App\Models\AirExport::with([
                    'charges.currency', 'charges.billTo', 'dmShipper', 'dmConsignee', 
                    'shipper', 'consignee', 'depPort', 'dstPort', 'hbls', 'packageUnit'
                ])->findOrFail($id);
                $modeType = 'air';
                break;

            case 'air-import':
                $shipment = \App\Models\AirImport::with([
                    'charges.currency', 'charges.billTo', 'dmShipper', 'dmConsignee', 
                    'shipper_rel', 'consignee_rel', 'depPort', 'dstPort', 'hbls', 'packageUnit'
                ])->findOrFail($id);
                $modeType = 'air';
                break;

            default:
                abort(404, 'Invalid shipment module type.');
        }

        // Bill To party resolution
        $billToParty = $shipment->dmBillTo ?? $shipment->billTo ?? $shipment->forwardingAgent ?? $shipment->dmCustomer ?? $shipment->customer ?? null;
        $billToName = $billToParty?->name ?? ($shipment->customer?->name ?? 'CIRCLE MARINE LIMITED');
        $billToAddress = $billToParty?->address ?? $shipment->customer?->address ?? "SHAFI BHABAN (2ND FLOOR), 1216/A SK MUJIB ROAD\n6 AGRABAD C/A, CHATTOGRAM-4100, BANGLADESH.";
        $accountNo = $billToParty?->code ?? $shipment->customer?->code ?? 'CIRMARCGP';

        // Shipper & Consignee
        $consignor = $shipment->dmShipper?->name ?? (is_object($shipment->shipper) ? $shipment->shipper?->name : $shipment->shipper) ?? $shipment->shipper_rel?->name ?? $shipment->actual_shipper ?? '-';
        $consignee = $shipment->dmConsignee?->name ?? (is_object($shipment->consignee) ? $shipment->consignee?->name : $shipment->consignee) ?? $shipment->consignee_rel?->name ?? '-';

        // Reference / Booking / Document
        $fileNo = $shipment->file_no ?? ('S' . sprintf('%08d', $shipment->id));
        $invoiceNo = 'SCL' . sprintf('%08d', $shipment->id);
        $clientRef = ($shipment->booking_no ?? $shipment->ref_no ?? $fileNo) . ' /';

        // Package & Weight Aggregation
        $totalPkg = (float)($shipment->pkg_qty ?? 0);
        $totalWeight = (float)($shipment->gross_weight ?? $shipment->weight_kg ?? $shipment->weight ?? 0);
        $totalVolume = (float)($shipment->volume ?? $shipment->measure_cbm ?? $shipment->cbm ?? 0);
        $totalChgWeight = (float)($shipment->chargeable_weight ?? $totalWeight);

        if (isset($shipment->containers) && $shipment->containers->count() > 0) {
            foreach ($shipment->containers as $c) {
                $totalPkg += (float)($c->pkg_qty ?? $c->pkg ?? 0);
                $totalWeight += (float)($c->weight_kg ?? $c->weight ?? 0);
                $totalVolume += (float)($c->measure_cbm ?? $c->measurement ?? 0);
            }
        }

        // Dynamic Package Unit resolution
        $pkgUnitCode = $shipment->packageUnit?->code ?? $shipment->packageUnit?->name;
        if (!$pkgUnitCode && isset($shipment->containers) && $shipment->containers->first()) {
            $firstContainer = $shipment->containers->first();
            $pkgUnitCode = $firstContainer->packageUnit?->code ?? $firstContainer->packageUnit?->name;
        }
        if (!$pkgUnitCode && isset($shipment->hbls) && $shipment->hbls->first()) {
            $firstHbl = $shipment->hbls->first();
            $pkgUnitCode = $firstHbl->packageUnit?->code ?? $firstHbl->packageUnit?->name;
        }
        if (!$pkgUnitCode) {
            $pkgUnitCode = 'CTN';
        } else {
            $pkgUnitCode = strtoupper($pkgUnitCode);
        }

        $weightStr = $totalWeight > 0 ? (number_format($totalWeight, 0) . ' KG') : '-';
        $volumeStr = $totalVolume > 0 ? (number_format($totalVolume, 2) . ' M3') : '-';
        $chgWeightStr = $totalChgWeight > 0 ? (number_format($totalChgWeight, 0) . ' KG') : $weightStr;
        $packagesStr = $totalPkg > 0 ? (number_format($totalPkg, 0) . ' ' . $pkgUnitCode) : '-';

        // Vessel / Flight Particulars
        $vesselFlightName = $shipment->flight_no ?? (is_object($shipment->vessel) ? $shipment->vessel?->name : $shipment->vessel) ?? $shipment->vessel_name ?? '-';
        $voyage = $shipment->voyage ?? '';
        $etdStr = $shipment->etd ? (is_string($shipment->etd) ? \Carbon\Carbon::parse($shipment->etd)->format('d-M-y') : $shipment->etd->format('d-M-y')) : '-';
        $vesselFlightDate = $vesselFlightName . ($voyage ? " / {$voyage}" : '') . ($etdStr !== '-' ? " / {$etdStr}" : '');

        $mawbMbl = $shipment->mawb_no ?? $shipment->mbl_no ?? '-';
        $hawbHbl = $shipment->hbls?->first()?->hbl_no ?? $shipment->sub_bl_no ?? $shipment->hawb_no ?? '-';

        $origin = (is_object($shipment->depPort) ? $shipment->depPort?->name : null) ?? (is_object($shipment->portOfLoading) ? $shipment->portOfLoading?->name : null) ?? (is_object($shipment->pol) ? $shipment->pol?->name : null) ?? '-';
        $destination = (is_object($shipment->dstPort) ? $shipment->dstPort?->name : null) ?? (is_object($shipment->portOfDischarge) ? $shipment->portOfDischarge?->name : null) ?? (is_object($shipment->pod) ? $shipment->pod?->name : null) ?? '-';
        $etaStr = $shipment->eta ? (is_string($shipment->eta) ? \Carbon\Carbon::parse($shipment->eta)->format('d-M-y') : $shipment->eta->format('d-M-y')) : '-';

        // Charges Formatting
        $items = [];
        $subtotal = 0;
        $vatTotal = 0;

        if ($shipment->charges && $shipment->charges->count() > 0) {
            foreach ($shipment->charges as $c) {
                $qty = (float)($c->qty ?? 1);
                $rate = (float)($c->rate ?? 0);
                $amt = (float)($c->total_amount ?: ($qty * $rate));
                $taxPct = (float)($c->tax_percent ?? $c->vat ?? 0);
                $taxAmt = (float)($c->tax_amount ?: ($amt * ($taxPct / 100)));

                $currencyCode = is_object($c->currency) ? ($c->currency->code ?? 'USD') : ($c->currency ?: 'USD');
                $roe = (float)($c->roe ?? 1.0);
                $localRateAmt = $amt * ($roe ?: 1);

                $desc = ($c->charge_name ?: $c->charge_code ?: 'FREIGHT CHARGE');
                if ($qty > 0 && $rate > 0) {
                    $desc .= " {$currencyCode}" . number_format($rate, 2) . "/KG x " . number_format($qty, 0) . " KG @" . number_format($roe, 2);
                }

                $items[] = [
                    'description' => $desc,
                    'vat_text' => $taxPct > 0 ? (number_format($taxPct, 2) . '%') : 'Zero Rated',
                    'amount' => $localRateAmt,
                ];

                $subtotal += $localRateAmt;
                $vatTotal += ($taxAmt * ($roe ?: 1));
            }
        }

        $grandTotal = $subtotal + $vatTotal;

        // ===== AUTO-POPULATION INTEGRATION =====
        // Load auto-populated data based on Shipment Memo Auto-Load configuration
        $autoPopService = new ShipmentMemoAutoPopulationService();
        $autoPopulatedData = [
            'master_bl' => $autoPopService->getAutoPopulatedData($module, $shipment, 'master_bl'),
            'house_bl' => $autoPopService->getAutoPopulatedData($module, $shipment, 'house_bl'),
        ];

        // Apply auto-populated values (Master B/L has priority, fallback to existing values)
        $billToNameFinal = $autoPopulatedData['master_bl']['customer'] ?? 
                          $autoPopulatedData['master_bl']['consignee'] ?? 
                          $billToName;
        
        $consignorFinal = $autoPopulatedData['master_bl']['shipper'] ?? 
                         $autoPopulatedData['house_bl']['mbl_shipper'] ?? 
                         $consignor;
        
        $consigneeFinal = $autoPopulatedData['master_bl']['consignee'] ?? 
                         $autoPopulatedData['house_bl']['mbl_consignee'] ?? 
                         $consignee;

        $data = [
            'invoice_no' => $invoiceNo,
            'bin_no' => '000408318',
            'bill_to_name' => $billToNameFinal,
            'bill_to_attention' => 'THE ACCOUNTS PAYABLE MANAGER',
            'bill_to_address' => $billToAddress,
            'account_no' => $accountNo,
            'invoice_date' => date('d-M-y'),
            'due_date' => date('d-M-y'),
            'terms' => 'Cash on Delivery',
            
            // Auto-populated fields from configuration
            'auto_populated' => $autoPopulatedData, // Pass to view for debugging/reference
            'shipment_no' => $fileNo,
            'consol_no' => $shipment->mbl_no ?? $shipment->mawb_no ?? 'C00013606',
            'consignor' => $consignorFinal, // Using auto-populated value
            'consignee' => $consigneeFinal, // Using auto-populated value
            'client_ref' => $clientRef,
            'goods_description' => $shipment->hbls?->first()?->description ?? '',
            'broker' => '',
            'weight' => $weightStr,
            'volume' => $volumeStr,
            'chargeable_weight' => $chgWeightStr,
            'packages' => $packagesStr,
            'vessel_flight_date' => $vesselFlightDate,
            'mawb_mbl' => $mawbMbl,
            'hawb_hbl' => $hawbHbl,
            'origin' => $origin,
            'etd' => $etdStr,
            'destination' => $destination,
            'eta' => $etaStr,
            'mode_type' => $modeType,
            'currency_code' => 'BDT',
            'items' => $items,
            'subtotal' => $subtotal,
            'vat_total' => $vatTotal,
            'total' => $grandTotal,
            'bank_name' => 'MUTUAL TRUST BANK LTD',
            'bank_branch' => 'PANTHAPATH BRANCH, DHAKA',
            'bank_account' => '0003-0320000241',
            'bank_swift' => 'MTBLBDDHPPB',
            'pay_ref' => $accountNo . ' ' . $invoiceNo,
        ];

        return view('invoices.freight-invoice', compact('data', 'shipment'));
    }

    /**
     * Automatically clean hardcoded sample text baked into freight-invoice-page-1.png
     */
    private function ensureCleanFreightInvoiceBg()
    {
        // Restore silk-container-page-1.png if corrupted or overwritten
        $silkOrig = public_path('assets/images/hbl/silk-1.png');
        $silkTarget = public_path('hbl-backgrounds/silk-container-page-1.png');
        if (file_exists($silkOrig)) {
            @copy($silkOrig, $silkTarget);
        }

        $srcPath = public_path('hbl-backgrounds/freight-invoice-page-1.png');
        $cleanPath = public_path('hbl-backgrounds/freight-invoice-page-1-clean.png');

        if (file_exists($srcPath) && extension_loaded('gd')) {
            $img = @imagecreatefrompng($srcPath);
            if ($img) {
                $w = imagesx($img);
                $h = imagesy($img);
                $white = imagecolorallocate($img, 255, 255, 255);

                $cleanRect = function($x1_pct, $y1_pct, $x2_pct, $y2_pct) use ($img, $w, $h, $white) {
                    imagefilledrectangle($img, (int)($w * $x1_pct), (int)($h * $y1_pct), (int)($w * $x2_pct), (int)($h * $y2_pct), $white);
                };

                // 1. Header SCL00000001 (next to FREIGHT INVOICE)
                $cleanRect(0.35, 0.160, 0.70, 0.198);
                // 2. Sub-header 000408318
                $cleanRect(0.08, 0.190, 0.25, 0.212);
                // 3. Bill To area
                $cleanRect(0.20, 0.200, 0.63, 0.305);
                // 4. Right meta box hardcoded values
                $cleanRect(0.64, 0.200, 0.98, 0.305);
                // 5. Consignor / Consignee text values
                $cleanRect(0.05, 0.315, 0.95, 0.338);
                // 6. Client / Order Ref text values
                $cleanRect(0.05, 0.342, 0.95, 0.362);
                // 7. Goods Description text values
                $cleanRect(0.05, 0.365, 0.95, 0.385);
                // 8. Particulars table data rows
                $cleanRect(0.05, 0.388, 0.95, 0.470);
                // 9. Charges line items area
                $cleanRect(0.05, 0.475, 0.95, 0.805);
                // 10. Summary totals area
                $cleanRect(0.68, 0.806, 0.95, 0.860);
                // 11. Footer bank details & amounts
                $cleanRect(0.10, 0.861, 0.95, 0.980);

                imagepng($img, $cleanPath);
                imagedestroy($img);
            }
        }
    }

    /**
     * Recalculates and synchronizes invoice header totals (subtotal, tax_total, total_amount, balance_amount)
     * strictly from the line items to guarantee mathematical ledger consistency.
     */
    /**
     * Validate that cumulative Credit Notes do not exceed the linked original invoice amount.
     */
    protected function checkCreditNoteCap(int $companyId, int $targetInvoiceId, float $cnTotal, ?int $ignoreCnId = null): ?string
    {
        $origInv = Invoice::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->find($targetInvoiceId);

        if (!$origInv) {
            return null;
        }

        $existingCnsSum = (float) Invoice::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where(function($q) use ($targetInvoiceId) {
                $q->where('related_invoice_id', $targetInvoiceId)
                  ->orWhere(function($sub) use ($targetInvoiceId) {
                      $sub->where('document_type', 'CN')
                          ->where('invoiceable_type', 'App\\Models\\Invoice')
                          ->where('invoiceable_id', $targetInvoiceId);
                  });
            })
            ->whereNotIn('status', ['VOID', 'CANCELLED'])
            ->where('verification_status', '!=', 'Cancelled')
            ->when($ignoreCnId, fn($q) => $q->where('id', '!=', $ignoreCnId))
            ->sum('total_amount');

        $totalCn = round($existingCnsSum + $cnTotal, 2);
        $origTotal = round((float) $origInv->total_amount, 2);

        if ($totalCn > $origTotal + 0.005) {
            return "Prohibited: Cumulative Credit Notes (\${$totalCn}) exceed original invoice amount (\${$origTotal}). Active credit notes currently total \${$existingCnsSum}.";
        }

        return null;
    }

    public static function syncInvoiceTotals(Invoice $invoice): void
    {
        $lines = $invoice->lines()->get();
        if ($lines->isEmpty()) {
            return;
        }

        $linesSum = (float) $lines->sum('amount');
        $discountPct = (float) ($invoice->discount_pct ?? 0);
        $discount = ($linesSum * $discountPct) / 100;
        $afterDiscount = $linesSum - $discount;
        $shipping = (float) ($invoice->shipping_amount ?? 0);
        $taxPct = (float) ($invoice->tax_pct ?? 0);
        $taxTotal = (float) ($invoice->tax_total ?? 0);

        if ($taxPct > 0) {
            $taxTotal = round(($afterDiscount + $shipping) * ($taxPct / 100), 2);
        }

        $subtotal = round($linesSum, 2);
        $totalAmount = round($afterDiscount + $shipping + $taxTotal, 2);

        $invoice->subtotal = $subtotal;
        $invoice->tax_total = $taxTotal;
        $invoice->total_amount = $totalAmount;
        $invoice->saveQuietly();

        AccountingPaymentController::syncInvoiceBalance($invoice->id);
        $invoice->refresh();
    }

    /**
     * Apply booking / file number search to invoice query.
     */
    protected function applyBookingSearch($query, string $search)
    {
        return $query->whereHasMorph('invoiceable', [
            \App\Models\OceanExport::class,
            \App\Models\OceanImport::class,
            \App\Models\AirExport::class,
            \App\Models\AirImport::class,
            \App\Models\TruckShipment::class,
            \App\Models\OceanBooking::class,
            \App\Models\AirBooking::class,
        ], function($sub, $type) use ($search) {
            $matched = false;
            if (in_array($type, [
                \App\Models\OceanExport::class,
                \App\Models\OceanImport::class,
                \App\Models\AirExport::class,
                \App\Models\AirImport::class,
                \App\Models\TruckShipment::class
            ])) {
                $sub->where('file_no', 'like', "%{$search}%");
                $matched = true;
            }
            if (in_array($type, [
                \App\Models\OceanExport::class,
                \App\Models\AirExport::class,
                \App\Models\OceanBooking::class,
                \App\Models\AirBooking::class
            ])) {
                if ($matched) {
                    $sub->orWhere('booking_no', 'like', "%{$search}%");
                } else {
                    $sub->where('booking_no', 'like', "%{$search}%");
                    $matched = true;
                }
            }
            if (in_array($type, [
                \App\Models\TruckShipment::class,
                \App\Models\OceanBooking::class
            ])) {
                if ($matched) {
                    $sub->orWhere('carrier_bkg_no', 'like', "%{$search}%");
                } else {
                    $sub->where('carrier_bkg_no', 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Search and open related Invoice (INV), Debit Note (DN), and Credit Note (CN) directly by booking number.
     */
    public function byBooking(Request $request, string $bookingNo)
    {
        $companyId = auth()->user()->company_id;
        $invoices = Invoice::where('company_id', $companyId)
            ->where(function($q) use ($bookingNo) {
                $q->where('invoice_no', $bookingNo)
                  ->orWhere(function($sub) use ($bookingNo) {
                      $this->applyBookingSearch($sub, $bookingNo);
                  });
            })
            ->get();

        if ($invoices->count() === 1) {
            return redirect()->route('accounting.invoices.show', $invoices->first()->id);
        }

        return redirect()->route('accounting.invoices.index', ['search' => $bookingNo]);
    }
}

