<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        [x-cloak] { display: none !important; }
        .form-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px 12px; }
        @media (max-width: 1400px) { .form-grid-4 { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 1100px) { .form-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .form-grid-4 { grid-template-columns: 1fr; } }

        .btn-tool { background: #fff; border: 1px solid #cbd5e1; padding: 2px 8px; font-size: 10px; color: #334155; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px; height: 22px; border-radius: 2px; transition: all 0.15s; white-space: nowrap; box-sizing: border-box; }
        .btn-tool:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool.green { background: #3b82f6 !important; color: #fff !important; border-color: #2563eb !important; font-weight: 600; }
        .btn-tool.green:hover { background: #2563eb !important; }
        .btn-tool:disabled { opacity: 0.4; cursor: not-allowed; }

        .btn-tool-icon { background: #fff; border: 1px solid #cbd5e1; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; color: #475569; border-radius: 2px; transition: all 0.2s; }
        .btn-tool-icon:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-icon-blue { background: #3b82f6; color: #fff; border: none; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 2px; font-size: 10px; }

        .action-bar { text-align: center; padding: 15px 0; margin-top: 15px; display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
        .input-number { text-align: right; }
        .is-invalid { border-color: #dc2626 !important; }
        .inline-checkbox { width: 12px !important; height: 12px !important; margin: 0; cursor: pointer; accent-color: #3b82f6; }

        .gf-tabs { display: flex; border-bottom: 1px solid #e2e8f0; list-style: none; padding: 0; margin: 0 0 10px 0; background: #ffffff; border-radius: 4px 4px 0 0; overflow-x: auto; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .gf-tabs li { margin-bottom: -1px; }
        .gf-tabs li a { padding: 8px 16px; display: block; color: #64748b; text-decoration: none; border: 1px solid transparent; cursor: pointer; font-size: 11px; font-weight: 600; transition: all 0.2s ease; }
        .gf-tabs li a:hover { color: #3b82f6; background: #f8fafc; }
        .gf-tabs li.active a { background: #ffffff; border: 1px solid #e2e8f0; border-bottom-color: #ffffff; border-top: 3px solid #3b82f6; color: #0f172a; border-radius: 4px 4px 0 0; }
        .gf-tabs li.disabled-tab a { cursor: not-allowed; opacity: 0.45; pointer-events: none; color: #94a3b8; background: #f8fafc; border-color: transparent; }

        .exchange-rate-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px 10px; margin-top: 4px; }
        .exchange-rate-item { display: flex; align-items: center; gap: 4px; }
        .exchange-rate-item .fx-label { font-weight: 400; color: #64748b; font-size: 9px; min-width: 60px; white-space: nowrap; }
        .exchange-rate-item .form-control-gf { height: 18px; font-size: 9px; }
        .exchange-rate-section { border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 8px; }

        .total-row td { background: #f8fafc !important; font-weight: 700; }
        .total-label { text-align: right; color: #3b82f6; padding-right: 10px !important; }
        .total-value { background: #fff !important; text-align: right; padding-right: 4px !important; }

        .table-custom { width: 100%; border-collapse: collapse; font-size: 10px; background: #ffffff; }
        .table-custom thead th { text-align: left; padding: 5px 6px; background: #f8fafc; color: #475569; font-weight: 700; border: 1px solid #e2e8f0; letter-spacing: 0.3px; font-size: 9px; white-space: nowrap; }
        .table-custom tbody td { padding: 4px 6px; border: 1px solid #e2e8f0; vertical-align: middle; color: #334155; font-size: 10px; }
        .table-custom tbody tr:hover { background-color: #f1f5f9; }
        .table-custom tbody tr.selected { background-color: #e0f2fe; }
        .table-responsive { width: 100%; overflow-x: auto; border-radius: 4px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }

        /* Ocean Module scrollable table */
        .invoice-table-wrapper {
            overflow: auto;
            max-height: calc(100vh - 380px);
            position: relative;
            border-bottom: none;
            margin: 0;
        }
        .invoice-table-wrapper::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .invoice-table-wrapper::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 5px;
        }
        .invoice-table-wrapper::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 5px;
        }
        .invoice-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        .grid-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 2400px;
            font-size: 10px;
            table-layout: fixed;
        }
        .grid-table th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #4a5568;
            color: #fff;
            font-weight: 600;
            border-bottom: 1px solid #5a6678;
            border-right: 1px solid #5a6678;
            border-top: 1px solid #5a6678;
            padding: 4px 6px;
            white-space: nowrap;
            height: 26px;
            text-align: left;
            user-select: none;
            font-size: 9px;
        }
        .grid-table th i.fa-sort { font-size: 8px; opacity: 0.5; margin-left: 2px; }
        .grid-table td {
            padding: 3px 6px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            white-space: nowrap;
            height: 24px;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
            color: #334155;
            font-size: 10px;
        }
        .grid-table tbody tr:hover { background-color: #f1f5f9; }
        .grid-table tbody tr.selected-row { background-color: #e0f2fe; }
        .grid-table tbody tr.selected-row td { background-color: #e0f2fe; }

        .memo-input { width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; color: #1e293b; background: #ffffff; box-sizing: border-box; font-family: 'Inter', 'Open Sans', sans-serif; transition: border-color 0.15s, box-shadow 0.15s; }
        .memo-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }
        .memo-btn-primary { background: #3b82f6; color: #fff; border: none; padding: 5px 16px; border-radius: 3px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: background 0.15s; }
        .memo-btn-primary:hover { background: #2563eb; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(2px); animation: fadeIn 0.15s ease; }
        .modal-container { background: #ffffff; width: 90%; max-width: 520px; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); overflow: hidden; border-top: 3px solid #3b82f6; animation: slideUp 0.15s ease; }
        .modal-header { padding: 10px 15px; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #0f172a; }
        .modal-body { padding: 15px; max-height: 75vh; overflow-y: auto; }
        .modal-footer { padding: 10px 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
    @endpush

    <div class="page-content" x-data="paymentMakeForm()">
        <form id="paymentForm" action="{{ $payment ? route('accounting.payment.update', $payment->id) : route('accounting.payment.store') }}" method="POST">
            @csrf
            @if($payment) @method('PUT') @endif
            <input type="hidden" name="type" value="MADE">
            <input type="hidden" name="save_action" :value="saveAction">
            <input type="hidden" name="payment_level" :value="paymentLevel">
            <input type="hidden" name="office_id" :value="officeId">
            <input type="hidden" name="bank_name" :value="bankName">
            <input type="hidden" name="bank_currency_id" :value="bankCurrencyId">
            <input type="hidden" name="has_clear" :value="clearChecked ? '1' : '0'">
            <input type="hidden" name="clear_date" :value="clearChecked ? clearDate : ''">
            <input type="hidden" name="has_void" :value="voidChecked ? '1' : '0'">
            <input type="hidden" name="void_date" :value="voidChecked ? voidDate : ''">
            <input type="hidden" name="show_party_on_check" :value="showPartyOnCheck ? '1' : '0'">
            <input type="hidden" name="check_no" :value="checkNo">

            @if($errors->any())
                <div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:12px;">
                    <strong><i class="fa fa-exclamation-circle"></i> Validation Error</strong>
                    <ul style="margin:5px 0 0 15px;padding:0;">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <!-- Breadcrumb -->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('accounting.payment-made-list') }}">Accounting</a> <i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('accounting.payment-made-list') }}">Payments</a> <i class="fa fa-angle-right"></i></li>
                    <li><span style="color:#333;font-weight:700;">{{ $payment ? 'Edit #' . $payment->payment_no : 'Make Payment' }}</span></li>
                </ul>
            </div>

            <!-- Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <h1 class="caption-subject" style="font-size:18px;margin:0;">
                    <i class="fa fa-money"></i> Make Payment — {{ $payment ? 'Edit #' . $payment->payment_no : 'New Entry' }}
                </h1>
                <div style="display:flex;gap:4px;">
                    @if($payment)
                    <a href="{{ route('accounting.payment-make') }}" class="btn-default-gf" target="_blank"><i class="fa fa-plus"></i> NEW PAYMENT</a>
                    @endif
                    <button type="button" class="btn-freightx" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> @if($payment) UPDATE @else SAVE @endif</button>
                    <a href="{{ route('accounting.payment-made-list') }}" class="btn-default-gf">BACK TO LIST</a>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="gf-tabs">
                <li :class="activeTab === 'basic' ? 'active' : ''"><a @click="activeTab = 'basic'">Basic</a></li>
                <li style="display: none !important;" :class="(activeTab === 'Tools' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'Tools' : null"><a>Doc Center</a></li>
            </ul>

            <!-- ==================== BASIC TAB ==================== -->
            <div x-show="activeTab === 'basic'">

                <!-- Main Details Grid -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-money"></i> Payment Details</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-4">
                            <!-- Column 1: Paid To & Payment Info -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Paid To</label>
                                    <div class="form-input-container">
                                        <select name="trade_partner_id" class="form-control-gf" x-model="form.trade_partner_id" required>
                                            <option value="">Select...</option>
                                            @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf" style="margin-top:-2px;margin-bottom:8px;">
                                    <label class="form-label-gf"></label>
                                    <div class="form-input-container">
                                        <label style="font-weight:normal;font-size:10px;color:#64748b;display:flex;align-items:center;gap:5px;cursor:pointer;">
                                            <input type="checkbox" class="inline-checkbox" x-model="showPartyOnCheck"> Show party on check
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Payment No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="payment_no" class="form-control-gf" x-model="form.payment_no" required>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Post Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="payment_date" class="form-control-gf" x-model="form.payment_date" required>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Amount</label>
                                    <div class="form-input-container">
                                        <input type="number" step="0.01" min="0" name="amount" class="form-control-gf input-number" x-model="form.amount" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 2: Payment Method & Reference -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Payment Method</label>
                                    <div class="form-input-container">
                                        <select name="payment_method" class="form-control-gf" x-model="form.payment_method" required>
                                            <option value="ACH">ACH</option>
                                            <option value="Bill Pay">Bill Pay</option>
                                            <option value="Cargosprint">Cargosprint</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Check">Check</option>
                                            <option value="Credit Card">Credit Card</option>
                                            <option value="FreightX Pay">FreightX Pay</option>
                                            <option value="Koverly">Koverly</option>
                                            <option value="Melio">Melio</option>
                                            <option value="Paycargo">Paycargo</option>
                                            <option value="Wire">Wire</option>
                                            <option value="Zelle">Zelle</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Check No.</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" x-model="checkNo" placeholder="Check #">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Currency</label>
                                    <div class="form-input-container">
                                        <select name="currency_id" class="form-control-gf" x-model="form.currency_id" required>
                                            <option value="">Select...</option>
                                            @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Invoice</label>
                                    <div class="form-input-container">
                                        <select name="invoice_id" class="form-control-gf" x-model="form.invoice_id" @change="onInvoiceSelected($event.target.value)">
                                            <option value="">Select invoice (optional)</option>
                                            @foreach($invoices as $inv)
                                            <option value="{{ $inv->id }}" {{ old('invoice_id', $payment?->invoice_id ?? $selectedInvoiceId ?? '') == $inv->id ? 'selected' : '' }}>{{ $inv->invoice_no }} — {{ $inv->billTo->name ?? 'N/A' }} — ${{ number_format($inv->balance_amount ?? $inv->total_amount, 2) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">MBL No.</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" style="background:#f8fafc;font-weight:600;" :value="currentInvoiceMbl" placeholder="MBL No." readonly>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">HBL No.</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" style="background:#f8fafc;font-weight:600;color:#0369a1;" :value="currentInvoiceHbl" placeholder="HBL No." readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 3: Clear, Void, Remark -->
                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Clear</label>
                                    <div class="form-input-container" style="display:flex;gap:4px;">
                                        <input type="checkbox" class="inline-checkbox" x-model="clearChecked">
                                        <input type="date" class="form-control-gf" x-model="clearDate" :disabled="!clearChecked">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Void</label>
                                    <div class="form-input-container" style="display:flex;gap:4px;">
                                        <input type="checkbox" class="inline-checkbox" x-model="voidChecked">
                                        <input type="date" class="form-control-gf" x-model="voidDate" :disabled="!voidChecked">
                                    </div>
                                </div>
                                <div class="form-group-gf" style="align-items:flex-start;">
                                    <label class="form-label-gf" style="margin-top:3px;">Remark</label>
                                    <div class="form-input-container">
                                        <textarea name="remark" class="form-control-gf" style="height:50px;resize:vertical;padding:4px;" value="{!! old('remark', $payment?->remark ?? '') !!}"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Column 4: Office, Bank, Exchange Rates -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Office</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" x-model="officeId">
                                            <option value="">Select...</option>
                                            @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->code }} — {{ $office->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Bank</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" x-model="bankName" placeholder="Bank name...">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Bank Currency</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" x-model="bankCurrencyCode">
                                            <option value="">Select...</option>
                                            @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="exchange-rate-section">
                                    <div style="font-size:9px;font-weight:700;color:#475569;margin-bottom:4px;">Exchange Rates</div>
                                    <div class="exchange-rate-grid">
                                        @foreach($currencies as $curr)
                                        <div class="exchange-rate-item">
                                            <span class="fx-label">{{ $curr->code }} =&gt; USD</span>
                                            <input type="text" class="form-control-gf input-number" value="1" placeholder="Rate">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Invoices Table - Ocean Module UI -->
                <div class="portlet light" style="margin-top:10px;">
                    <div class="portlet-title" style="display:flex;align-items:center;justify-content:space-between;padding:6px 12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <a href="{{ route('accounting.invoices.create') }}" class="btn-tool-icon" style="background:#22c55e;color:#fff;border:none;width:22px;height:22px;font-size:10px;cursor:pointer;border-radius:3px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;" title="Create New Invoice" target="_blank"><i class="fa fa-plus"></i></a>
                            <span style="font-size:12px;font-weight:700;color:#1e293b;"><i class="fa fa-list" style="color:#3b82f6;margin-right:4px;"></i> Invoice List</span>
                            <span style="font-size:9px;color:#94a3b8;margin-left:4px;">({{ $invoices->count() }} rows)</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:10px;color:#64748b;">Filtered By:</span>
                            <span style="font-size:9px;background:#3b82f6;color:#fff;padding:2px 8px;border-radius:3px;font-weight:600;">Type: {{ $type ?? 'AP Invoice' }}</span>
                            <span style="font-size:9px;background:#3b82f6;color:#fff;padding:2px 8px;border-radius:3px;font-weight:600;">Status: Outstanding</span>
                            <span style="font-size:9px;background:#3b82f6;color:#fff;padding:2px 8px;border-radius:3px;font-weight:600;">Office: {{ $payment?->office?->code ?? 'All' }}</span>
                            <button type="button" class="btn-tool" style="margin-left:6px;" @click="loadMoreInvoices()"><i class="fa fa-angle-double-down"></i> Show More Invoice(s)</button>
                            <button type="button" class="btn-tool-icon" @click="showConfigModal = true" title="Config"><i class="fa fa-cog"></i></button>
                        </div>
                    </div>
                    <div class="portlet-body" style="padding:0;">
                        @if($invoices->count() > 0)
                        <div class="invoice-table-wrapper" id="invoiceTableWrapper">
                            <table class="grid-table" id="invoices-table">
                                <thead>
                                    <tr>
                                        <th data-col="check" style="width:28px;text-align:center;position:sticky;left:0;z-index:22;background:#4a5568;border-right:1px solid #5a6678;"><input type="checkbox" class="inline-checkbox" @click="toggleAllInvoices($event)"></th>
                                        <th data-col="post_date" style="width:80px;background:#4a5568;cursor:pointer;" @click="sortBy('invoice_date')">Post Date <i class="fa" :class="sortCol === 'invoice_date' ? (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'" style="font-size:8px;opacity:0.6;"></i></th>
                                        <th data-col="invoice_date" style="width:90px;background:#4a5568;cursor:pointer;" @click="sortBy('invoice_date')">Invoice Date <i class="fa" :class="sortCol === 'invoice_date' ? (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'" style="font-size:8px;opacity:0.6;"></i></th>
                                        <th data-col="due_date" style="width:90px;background:#4a5568;cursor:pointer;" @click="sortBy('due_date')">Due Date <i class="fa" :class="sortCol === 'due_date' ? (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'" style="font-size:8px;opacity:0.6;"></i></th>
                                        <th data-col="type" style="width:70px;background:#4a5568;">Type</th>
                                        <th data-col="office" style="width:80px;background:#4a5568;">Office</th>
                                        <th data-col="paid_to" style="width:160px;background:#4a5568;cursor:pointer;" @click="sortBy('paid_to')">Paid To <i class="fa" :class="sortCol === 'paid_to' ? (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'" style="font-size:8px;opacity:0.6;"></i></th>
                                        <th data-col="invoice_no" style="width:140px;background:#4a5568;cursor:pointer;" @click="sortBy('invoice_no')">Invoice No. <i class="fa" :class="sortCol === 'invoice_no' ? (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'" style="font-size:8px;opacity:0.6;"></i></th>
                                        <th data-col="gl" style="width:80px;background:#4a5568;">G/L</th>
                                        <th data-col="currency" style="width:60px;background:#4a5568;">Currency</th>
                                        <th data-col="invoice_amt" style="width:110px;text-align:right;background:#4a5568;cursor:pointer;" @click="sortBy('total_amount')">Invoice AMT <i class="fa" :class="sortCol === 'total_amount' ? (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'" style="font-size:8px;opacity:0.6;"></i></th>
                                        <th data-col="balance_amt" style="width:110px;text-align:right;background:#4a5568;cursor:pointer;" @click="sortBy('balance_amount')">Balance AMT <i class="fa" :class="sortCol === 'balance_amount' ? (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc') : 'fa-sort'" style="font-size:8px;opacity:0.6;"></i></th>
                                        <th data-col="select" style="width:30px;text-align:center;background:#4a5568;"><i class="fa fa-check-square-o" style="font-size:9px;opacity:0.6;"></i></th>
                                        <th data-col="payment" style="width:110px;text-align:right;background:#4a5568;">Payment</th>
                                        <th data-col="payment_rmb" style="width:110px;text-align:right;background:#4a5568;">Payment (RMB)</th>
                                        <th data-col="description" style="width:150px;background:#4a5568;">Invoice Description</th>
                                        <th data-col="file_no" style="width:100px;background:#4a5568;">Booking No.</th>
                                        <th data-col="mbl_no" style="width:110px;background:#4a5568;">MBL No.</th>
                                        <th data-col="hbl_no" style="width:110px;background:#4a5568;">HBL No.</th>
                                        <th data-col="po_no" style="width:100px;background:#4a5568;">P.O. No.</th>
                                        <th data-col="op" style="width:80px;background:#4a5568;">OP</th>
                                        <th data-col="sales" style="width:100px;background:#4a5568;">Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $inv)
                                    <tr x-show="!form.trade_partner_id || form.trade_partner_id == '{{ $inv->bill_to_id }}'"
                                        x-data="{ get isRowSelected() { return selectedInvoices.some(i => Number(i.id) === {{ $inv->id }}); } }" 
                                        :class="isRowSelected ? 'selected-row' : ''">
                                        <td data-col="check" style="text-align:center;position:sticky;left:0;z-index:12;background:inherit;border-right:1px solid #e2e8f0;"><input type="checkbox" class="inline-checkbox" :checked="isRowSelected" @change="$event.target.checked ? selectInvoice({{ $inv->id }}, '{{ $inv->invoice_no }}') : deselectInvoice({{ $inv->id }})"></td>
                                        <td data-col="post_date">{{ $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : '' }}</td>
                                        <td data-col="invoice_date">{{ $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : '' }}</td>
                                        <td data-col="due_date">{{ $inv->due_date ? $inv->due_date->format('Y-m-d') : '' }}</td>
                                        <td data-col="type">{{ $inv->type ?? 'AP' }}</td>
                                        <td data-col="office">{{ $inv->office?->code ?? '' }}</td>
                                        <td data-col="paid_to">{{ $inv->billTo->name ?? '' }}</td>
                                        <td data-col="invoice_no" style="font-weight:600;color:#1e293b;">{{ $inv->invoice_no }}</td>
                                        <td data-col="gl"></td>
                                        <td data-col="currency"><span style="font-size:9px;background:#e0f2fe;color:#0369a1;padding:1px 6px;border-radius:3px;font-weight:600;">{{ $inv->currency->code ?? 'USD' }}</span></td>
                                        <td data-col="invoice_amt" style="text-align:right;font-weight:600;">{{ number_format($inv->total_amount, 2) }}</td>
                                        <td data-col="balance_amt" style="text-align:right;font-weight:600;color:#b45309;">{{ number_format($inv->balance_amount ?? $inv->total_amount, 2) }}</td>
                                        <td data-col="select" style="text-align:center;"></td>
                                        <td data-col="payment" style="text-align:right;padding-right:6px;">
                                            <input type="number" step="0.01" min="0" class="form-control-gf input-number"
                                                style="height:20px;font-size:9px;width:90px;min-width:70px;max-width:110px;text-align:right;border:1px solid #cbd5e1;border-radius:3px;padding:2px 4px;"
                                                x-model="allocationAmounts[{{ $inv->id }}]"
                                                @input="updateAllocation({{ $inv->id }})">
                                        </td>
                                        <td data-col="payment_rmb" style="text-align:right;padding-right:6px;">
                                            <input type="number" step="0.01" min="0" class="form-control-gf input-number"
                                                style="height:20px;font-size:9px;width:90px;min-width:70px;max-width:110px;text-align:right;border:1px solid #cbd5e1;border-radius:3px;padding:2px 4px;"
                                                x-model="allocationRMB[{{ $inv->id }}]">
                                        </td>
                                        <td data-col="description" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;">{{ $inv->internal_remark ?? '' }}</td>
                                        <td data-col="file_no">{{ optional($inv->invoiceable)->file_no ?? '--' }}</td>
                                        <td data-col="mbl_no" style="font-weight:600;color:#0f172a;">{{ $inv->master_bl_no ?? '--' }}</td>
                                        <td data-col="hbl_no" style="font-weight:600;color:#0369a1;">{{ $inv->house_bl_no ?? '--' }}</td>
                                        <td data-col="po_no"></td>
                                        <td data-col="op">{{ $inv->issuer?->name ?? '' }}</td>
                                        <td data-col="sales">{{ $inv->billTo->name ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Items Selected Row -->
                        <div style="padding:6px 12px;border-top:1px solid #e2e8f0;background:#fff;">
                            <span style="font-size:11px;color:#64748b;font-weight:600;" x-text="selectedInvoices.length + ' items selected'"></span>
                        </div>
                        <!-- Summary Bar -->
                        <div style="display:grid;grid-template-columns:repeat(5,1fr);border-top:2px solid #e2e8f0;background:#f8fafc;">
                            <div style="padding:8px 12px;border-right:1px solid #e2e8f0;">
                                <div style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:2px;">Total Paid Amount</div>
                                <div style="font-size:12px;font-weight:700;color:#3b82f6;" x-text="totalAllocated.toFixed(2)"></div>
                            </div>
                            <div style="padding:8px 12px;border-right:1px solid #e2e8f0;">
                                <div style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:2px;">Local Amount (<span x-text="getCurrencyCode(form.currency_id)"></span>)</div>
                                <div style="font-size:12px;font-weight:700;color:#3b82f6;" x-text="totalAllocated.toFixed(2)"></div>
                            </div>
                            <div style="padding:8px 12px;border-right:1px solid #e2e8f0;">
                                <div style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:2px;">Master Currency (<span x-text="getCurrencyCode(form.currency_id)"></span>)</div>
                                <div style="font-size:12px;font-weight:700;color:#3b82f6;" x-text="totalAllocated.toFixed(2)"></div>
                            </div>
                            <div style="padding:8px 12px;border-right:1px solid #e2e8f0;">
                                <div style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:2px;">Exchange Gain/Loss (<span x-text="getCurrencyCode(form.currency_id)"></span>)</div>
                                <div style="font-size:12px;font-weight:700;color:#3b82f6;">0.00</div>
                            </div>
                            <div style="padding:8px 12px;">
                                <div style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:2px;">Bank Currency (<span x-text="getCurrencyCode(bankCurrencyCode)"></span>)</div>
                                <div style="font-size:12px;font-weight:700;color:#3b82f6;" x-text="totalAllocated.toFixed(2)"></div>
                            </div>
                        </div>
                        @else
                        <div style="text-align:center;padding:40px 20px;color:#94a3b8;">
                            <i class="fa fa-inbox" style="font-size:36px;display:block;margin-bottom:8px;color:#cbd5e1;"></i>
                            <p style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px;">No Data Available.</p>
                            <p style="font-size:11px;">Please click <a href="{{ route('accounting.invoices.create') }}" style="color:#3b82f6;font-weight:600;">here</a> to create a new invoice.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ==================== DOC CENTER TAB ==================== -->
            <div x-show="activeTab === 'Tools'" x-cloak>
                @if(!$payment)
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-lock"></i> Doc Center</span>
                    </div>
                    <div class="portlet-body">
                        <div style="text-align:center;padding:40px 20px;color:#94a3b8;">
                            <i class="fa fa-lock" style="font-size:42px;display:block;margin-bottom:12px;color:#cbd5e1;"></i>
                            <p style="font-size:13px;font-weight:600;color:#64748b;margin-bottom:4px;">Payment Not Yet Saved</p>
                            <p style="font-size:11px;">Please save the payment first to access document management and memos.</p>
                        </div>
                    </div>
                </div>
                @else
                {{-- Documents Section --}}
                @php $docData = $payment->documents->map(fn($d) => ['id' => $d->id, 'file_name' => $d->file_name, 'file_extension' => $d->file_extension, 'file_size' => $d->file_size, 'uploader_name' => $d->uploader->name ?? 'N/A', 'created_at' => $d->created_at?->format('Y-m-d') ?? '']); @endphp
                <div class="portlet light" x-data="paymentDocCenter(@json($docData))">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-file-pdf-o"></i> Documents (<span x-text="documents.length"></span>)</span>
                        <div>
                            <label class="btn-tool" style="cursor:pointer;position:relative;">
                                <i class="fa fa-upload"></i> Upload
                                <input type="file" @change="uploadDocument($event, {{ $payment->id }})" style="position:absolute;opacity:0;width:100%;height:100%;left:0;top:0;cursor:pointer;">
                            </label>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <template x-if="documents.length > 0">
                        <div>
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>File Name</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th style="width:80px;text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(doc, idx) in documents" :key="doc.id">
                                    <tr>
                                        <td x-text="idx + 1"></td>
                                        <td><i class="fa" :class="doc.file_extension === 'pdf' ? 'fa-file-pdf-o' : (doc.file_extension === 'jpg' || doc.file_extension === 'png' ? 'fa-file-image-o' : 'fa-file-o')"></i> <span x-text="doc.file_name"></span></td>
                                        <td x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '-'"></td>
                                        <td x-text="doc.uploader_name"></td>
                                        <td x-text="doc.created_at"></td>
                                        <td style="text-align:center;">
                                            <button type="button" @click="downloadDocument(doc.id)" class="btn-tool-icon" style="color:#3b82f6;background:none;border:none;cursor:pointer;font-size:11px;" title="Download"><i class="fa fa-download"></i></button>
                                            <button type="button" @click="deleteDocument(doc.id, idx)" class="btn-tool-icon" style="color:#ef4444;background:none;border:none;cursor:pointer;font-size:11px;" title="Delete"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        </template>
                        <template x-if="documents.length === 0">
                        <div style="text-align:center;padding:25px;color:#94a3b8;">
                            <i class="fa fa-cloud-upload" style="font-size:32px;display:block;margin-bottom:6px;color:#cbd5e1;"></i>
                            <p style="font-size:11px;">No documents uploaded yet. Click "Upload" to add a document.</p>
                        </div>
                        </template>
                    </div>
                </div>

                {{-- Memos Section --}}
                @php $initMemos = $payment->memos->map(fn($m) => ['id' => $m->id, 'subject' => $m->subject, 'content' => $m->content ?? '', 'user_name' => $m->user->name ?? 'N/A', 'created_at' => $m->created_at?->format('Y-m-d') ?? '']); @endphp
                <div class="portlet light" x-data="paymentMemos(@json($initMemos))">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-sticky-note-o"></i> Memos / Notes (<span x-text="memos.length"></span>)</span>
                        <div>
                            <button type="button" class="btn-tool" @click="openAddMemo()"><i class="fa fa-plus"></i> Add Memo</button>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <template x-if="memos.length > 0">
                        <div style="max-height:350px;overflow-y:auto;">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:30px;"></th>
                                        <th>Subject</th>
                                        <th>Content</th>
                                        <th>By</th>
                                        <th>Date</th>
                                        <th style="width:70px;text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(memo, idx) in memos" :key="memo.id">
                                    <tr>
                                        <td style="text-align:center;"><i class="fa fa-sticky-note-o" style="color:#3b82f6;"></i></td>
                                        <td><strong style="font-size:10px;" x-text="memo.subject"></strong></td>
                                        <td style="max-width:250px;"><span style="font-size:10px;color:#64748b;" x-text="memo.content ? memo.content.substring(0, 60) : ''"></span></td>
                                        <td style="font-size:10px;" x-text="memo.user_name"></td>
                                        <td style="font-size:10px;" x-text="memo.created_at"></td>
                                        <td style="text-align:center;">
                                            <button type="button" @click="editMemo(memo.id, memo.subject, memo.content)" class="btn-tool-icon" style="color:#3b82f6;background:none;border:none;cursor:pointer;font-size:11px;" title="Edit"><i class="fa fa-pencil"></i></button>
                                            <button type="button" @click="deleteMemo(memo.id, idx)" class="btn-tool-icon" style="color:#ef4444;background:none;border:none;cursor:pointer;font-size:11px;" title="Delete"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        </template>
                        <template x-if="memos.length === 0">
                        <div style="text-align:center;padding:25px;color:#94a3b8;">
                            <i class="fa fa-sticky-note-o" style="font-size:32px;display:block;margin-bottom:6px;color:#cbd5e1;"></i>
                            <p style="font-size:11px;">No memos yet. Click "Add Memo" to create one.</p>
                        </div>
                        </template>

                        <!-- Memo Modal -->
                        <div x-show="showMemoModal" x-cloak x-transition class="modal-overlay" @click.away="showMemoModal = false">
                            <div class="modal-container" @click.stop>
                                <div class="modal-header">
                                    <span><i class="fa fa-sticky-note-o" style="color:#3b82f6;margin-right:6px;"></i> <span x-text="editingMemoId ? 'Edit Memo' : 'New Memo'"></span></span>
                                    <button type="button" @click="showMemoModal = false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div style="margin-bottom:14px;">
                                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Subject <span style="color:#ef4444;">*</span></label>
                                        <input type="text" x-model="memoForm.subject" placeholder="Enter memo subject..." class="memo-input" style="height:34px;padding:0 10px;">
                                    </div>
                                    <div style="margin-bottom:6px;">
                                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Content</label>
                                        <textarea x-model="memoForm.content" placeholder="Write your memo details here..." class="memo-input" style="height:120px;padding:8px 10px;resize:vertical;line-height:1.5;"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn-default-gf" @click="showMemoModal = false" style="margin-right:6px;padding:5px 14px;">Cancel</button>
                                    <button type="button" class="memo-btn-primary" @click="saveMemo({{ $payment->id }})">
                                        <i class="fa fa-save"></i> <span x-text="editingMemoId ? 'Update' : 'Save'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Config Modal - Column Visibility -->
            <div x-show="showConfigModal" x-cloak x-transition class="modal-overlay" @click.away="showConfigModal = false">
                <div class="modal-container" @click.stop>
                    <div class="modal-header">
                        <span><i class="fa fa-cog" style="color:#3b82f6;margin-right:6px;"></i> Column Configuration</span>
                        <button type="button" @click="showConfigModal = false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size:11px;color:#64748b;margin-bottom:10px;">Toggle column visibility in the Invoice List table.</p>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px;">
                            <template x-for="col in columnConfig" :key="col.key">
                                <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#334155;cursor:pointer;">
                                    <input type="checkbox" :checked="col.visible" @change="col.visible = $event.target.checked" style="accent-color:#3b82f6;">
                                    <span x-text="col.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-default-gf" @click="showConfigModal = false" style="margin-right:6px;padding:5px 14px;">Cancel</button>
                        <button type="button" class="memo-btn-primary" @click="applyColumnConfig()"><i class="fa fa-check"></i> Apply</button>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="action-bar">
                <button type="button" class="btn-tool green" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> @if($payment) UPDATE &amp; CLOSE @else SAVE &amp; CLOSE @endif</button>
                @if(!$payment)
                <button type="button" class="btn-tool" @click="handleSubmit('save_new')"><i class="fa fa-plus-circle"></i> SAVE &amp; NEW</button>
                @endif
                <a href="{{ route('accounting.payment-made-list') }}" class="btn-tool" target="_blank"><i class="fa fa-arrow-left"></i> CANCEL</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function paymentMakeForm() {
            const editPayment = @json($payment);
            return {
                activeTab: 'basic',
                saveAction: 'save_close',
                isSaved: editPayment ? true : false,
                paymentLevel: (editPayment && editPayment.payment_level) ? editPayment.payment_level : 'Level 1',
                bankName: (editPayment && editPayment.bank_name) ? editPayment.bank_name : '',
                bankCurrencyCode: (editPayment && editPayment.bank_currency_id) ? String(editPayment.bank_currency_id) : '',
                officeId: (editPayment && editPayment.office_id) ? String(editPayment.office_id) : '',
                showPartyOnCheck: editPayment && editPayment.show_party_on_check ? true : false,
                checkNo: (editPayment && editPayment.check_no) ? editPayment.check_no : '',
                clearChecked: editPayment && editPayment.clear_date ? true : true,
                clearDate: editPayment && editPayment.clear_date ? editPayment.clear_date.substring(0, 10) : new Date().toISOString().substring(0, 10),
                voidChecked: editPayment && editPayment.void_date ? true : false,
                voidDate: editPayment && editPayment.void_date ? editPayment.void_date.substring(0, 10) : '',

                form: {
                    trade_partner_id: '{{ old("trade_partner_id", $payment?->trade_partner_id ?? "") }}',
                    payment_no: '{{ old("payment_no", $paymentNo) }}',
                    payment_date: '{{ old("payment_date", $payment?->payment_date?->format("Y-m-d") ?? date("Y-m-d")) }}',
                    amount: '{{ old("amount", $payment?->amount ?? "0.00") }}',
                    payment_method: '{{ old("payment_method", $payment?->payment_method ?? "ACH") }}',
                    currency_id: '{{ old("currency_id", $payment?->currency_id ?? "") }}',
                    invoice_id: '{{ old("invoice_id", $payment?->invoice_id ?? $selectedInvoiceId ?? "") }}',
                },

                currentInvoiceMbl: '',
                currentInvoiceHbl: '',

                allocationAmounts: {},
                allocationRMB: {},
                selectedInvoices: [],
                totalInvoiceAmount: {{ $invoices->sum('total_amount') }},
                totalBalanceAmount: {{ $invoices->sum(fn($i) => $i->balance_amount ?? $i->total_amount) }},
                totalAllocated: 0,
                sortCol: '',
                sortDir: 'asc',
                showConfigModal: false,
                columnConfig: [
                    { key: 'post_date', label: 'Post Date', visible: true },
                    { key: 'invoice_date', label: 'Invoice Date', visible: true },
                    { key: 'due_date', label: 'Due Date', visible: true },
                    { key: 'type', label: 'Type', visible: true },
                    { key: 'office', label: 'Office', visible: true },
                    { key: 'paid_to', label: 'Paid To', visible: true },
                    { key: 'invoice_no', label: 'Invoice No.', visible: true },
                    { key: 'gl', label: 'G/L', visible: true },
                    { key: 'currency', label: 'Currency', visible: true },
                    { key: 'invoice_amt', label: 'Invoice AMT', visible: true },
                    { key: 'balance_amt', label: 'Balance AMT', visible: true },
                    { key: 'select', label: 'Select', visible: true },
                    { key: 'payment', label: 'Payment', visible: true },
                    { key: 'payment_rmb', label: 'Payment (RMB)', visible: true },
                    { key: 'description', label: 'Invoice Description', visible: true },
                    { key: 'file_no', label: 'Booking No.', visible: true },
                    { key: 'mbl_no', label: 'MBL No.', visible: true },
                    { key: 'hbl_no', label: 'HBL No.', visible: true },
                    { key: 'po_no', label: 'P.O. No.', visible: true },
                    { key: 'op', label: 'OP', visible: true },
                    { key: 'sales', label: 'Sales', visible: true },
                ],

                init() {
                    const allInvoices = @json($invoices);

                    // Set initial MBL and HBL from initial invoice_id if available
                    if (this.form.invoice_id) {
                        const initInv = allInvoices.find(i => String(i.id) === String(this.form.invoice_id));
                        if (initInv) {
                            this.currentInvoiceMbl = initInv.master_bl_no || '';
                            this.currentInvoiceHbl = initInv.house_bl_no || '';
                        }
                    }

                    @if($payment && $payment->invoice_id)
                    this.allocationAmounts[{{ $payment->invoice_id }}] = {{ $payment->amount ?? 0 }};
                    this.selectedInvoices.push({ id: {{ $payment->invoice_id }}, inv_no: '{{ $payment->invoice->invoice_no ?? "" }}' });
                    this.recalcTotal();
                    @endif

                    @if(!$payment && isset($selectedInvoiceIds) && !empty($selectedInvoiceIds))
                    const targetIds = @json(array_map('intval', $selectedInvoiceIds));
                    let firstInvoice = null;

                    targetIds.forEach(id => {
                        const inv = allInvoices.find(i => Number(i.id) === Number(id));
                        if (inv) {
                            if (!firstInvoice) firstInvoice = inv;
                            this.selectedInvoices.push({ id: inv.id, inv_no: inv.invoice_no });
                            const balanceAmount = parseFloat(inv.balance_amount !== null && inv.balance_amount !== undefined ? inv.balance_amount : inv.total_amount) || 0;
                            this.allocationAmounts[inv.id] = balanceAmount.toFixed(2);
                        }
                    });

                    if (firstInvoice) {
                        this.form.trade_partner_id = String(firstInvoice.bill_to_id || '');
                        this.form.currency_id = String(firstInvoice.currency_id || '');
                        if (!this.form.invoice_id) {
                            this.form.invoice_id = String(firstInvoice.id);
                            this.currentInvoiceMbl = firstInvoice.master_bl_no || '';
                            this.currentInvoiceHbl = firstInvoice.house_bl_no || '';
                        }
                    }

                    this.recalcTotal();
                    @endif
                },

                onInvoiceSelected(invoiceId) {
                    const allInvoices = @json($invoices);
                    const inv = allInvoices.find(i => String(i.id) === String(invoiceId));
                    if (inv) {
                        this.currentInvoiceMbl = inv.master_bl_no || '';
                        this.currentInvoiceHbl = inv.house_bl_no || '';
                        if (inv.bill_to_id) this.form.trade_partner_id = String(inv.bill_to_id);
                        if (inv.currency_id) this.form.currency_id = String(inv.currency_id);
                        if (!this.selectedInvoices.some(i => Number(i.id) === Number(inv.id))) {
                            this.selectInvoice(inv.id, inv.invoice_no);
                            const balance = parseFloat(inv.balance_amount !== null && inv.balance_amount !== undefined ? inv.balance_amount : inv.total_amount) || 0;
                            this.allocationAmounts[inv.id] = balance.toFixed(2);
                            this.recalcTotal();
                        }
                    } else {
                        this.currentInvoiceMbl = '';
                        this.currentInvoiceHbl = '';
                    }
                },

                getCurrencyCode(id) {
                    if (!id) return 'USD';
                    const curr = @json($currencies->map(fn($c) => ['id' => $c->id, 'code' => $c->code]));
                    const found = curr.find(c => String(c.id) === String(id));
                    return found ? found.code : 'USD';
                },

                selectInvoice(id, invNo) {
                    if (!this.selectedInvoices.some(i => i.id === id)) {
                        this.selectedInvoices.push({ id, inv_no: invNo });
                        this.allocationAmounts[id] = this.allocationAmounts[id] || 0;
                    }
                    if (!this.form.invoice_id) {
                        this.form.invoice_id = String(id);
                    }
                    const allInvoices = @json($invoices);
                    const inv = allInvoices.find(i => Number(i.id) === Number(id));
                    if (inv) {
                        this.currentInvoiceMbl = inv.master_bl_no || '';
                        this.currentInvoiceHbl = inv.house_bl_no || '';
                    }
                    this.recalcTotal();
                },
                deselectInvoice(id) {
                    this.selectedInvoices = this.selectedInvoices.filter(i => i.id !== id);
                    delete this.allocationAmounts[id];
                    this.recalcTotal();
                },
                updateAllocation(id) { this.recalcTotal(); },
                recalcTotal() {
                    let total = 0;
                    Object.values(this.allocationAmounts).forEach(val => { total += parseFloat(val) || 0; });
                    this.totalAllocated = total;
                    if (total > 0) this.form.amount = total.toFixed(2);
                },
                toggleAllInvoices(e) {
                    const checked = e.target.checked;
                    document.querySelectorAll('#invoices-table tbody tr').forEach(row => {
                        if (row.offsetParent === null) return; // skip hidden rows
                        const checkbox = row.querySelector('input[type="checkbox"]');
                        if (checkbox) { checkbox.checked = checked; checkbox.dispatchEvent(new Event('change')); }
                    });
                },

                sortBy(col) {
                    if (this.sortCol === col) {
                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortCol = col;
                        this.sortDir = 'asc';
                    }
                    const tbody = document.querySelector('#invoices-table tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr:not(.add-row)'));
                    const ths = Array.from(document.querySelector('#invoices-table thead tr').querySelectorAll('th'));
                    const colMap = { 'invoice_date': 1, 'due_date': 2, 'paid_to': 6, 'invoice_no': 7, 'total_amount': 10, 'balance_amount': 11 };
                    const colIdx = colMap[col] !== undefined ? colMap[col] : ths.findIndex(th => th.textContent.trim().toLowerCase().includes(col.replace('_', ' ')));
                    if (colIdx === -1) return;
                    rows.sort((a, b) => {
                        let aVal = a.children[colIdx]?.textContent.trim() || '';
                        let bVal = b.children[colIdx]?.textContent.trim() || '';
                        const aNum = parseFloat(aVal.replace(/,/g, ''));
                        const bNum = parseFloat(bVal.replace(/,/g, ''));
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return this.sortDir === 'asc' ? aNum - bNum : bNum - aNum;
                        }
                        return this.sortDir === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                    });
                    rows.forEach(r => tbody.appendChild(r));
                },

                loadMoreInvoices() {
                    showToast('info', 'Loading more invoices...');
                    fetch('{{ route("accounting.payment.store") }}', {
                        method: 'GET',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        showToast('success', 'All outstanding invoices are already loaded.');
                    })
                    .catch(() => {
                        showToast('info', 'All outstanding invoices are already loaded.');
                    });
                },

                addInvoiceRow() {
                    const tbody = document.querySelector('#invoices-table tbody');
                    const newIdx = tbody.querySelectorAll('tr').length + 1;
                    const row = document.createElement('tr');
                    row.setAttribute('x-data', '{ selected: false }');
                    row.classList.add('add-row');
                    row.style.backgroundColor = '#f0fdf4';
                    row.innerHTML = `
                        <td style="text-align:center;position:sticky;left:0;z-index:12;background:#f0fdf4;border-right:1px solid #e2e8f0;"><input type="checkbox" class="inline-checkbox" :checked="selected" @change="selected = $event.target.checked"></td>
                        <td><input type="date" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;"></td>
                        <td><input type="date" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;"></td>
                        <td><input type="date" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;"></td>
                        <td><input type="text" value="AP" readonly class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;background:#f0fdf4;"></td>
                        <td><input type="text" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="Office..."></td>
                        <td><input type="text" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="Paid To..."></td>
                        <td><input type="text" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="INV-..."></td>
                        <td><input type="text" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="G/L..."></td>
                        <td><select class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;"><option>USD</option></select></td>
                        <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:20px;font-size:9px;width:100%;text-align:right;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="0.00"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:20px;font-size:9px;width:100%;text-align:right;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="0.00"></td>
                        <td style="text-align:center;"><input type="checkbox" class="inline-checkbox"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:20px;font-size:9px;width:100%;text-align:right;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="0.00"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:20px;font-size:9px;width:100%;text-align:right;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="0.00"></td>
                        <td><input type="text" class="form-control-gf" style="height:20px;font-size:9px;width:100%;border:1px solid #86efac;border-radius:3px;padding:2px;" placeholder="Description..."></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    `;
                    tbody.appendChild(row);
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    showToast('success', 'New invoice row added. Fill in the details and save.');
                },

                handleSubmit(action) {
                    this.saveAction = action;
                    this.$nextTick(() => { this.saveViaAjax(); });
                },

                validateForm() {
                    let valid = true;
                    let missing = [];
                    if (!this.form.trade_partner_id) { missing.push('Paid To'); valid = false; }
                    if (!this.form.payment_no || !this.form.payment_no.trim()) { missing.push('Payment No.'); valid = false; }
                    if (!this.form.payment_date) { missing.push('Post Date'); valid = false; }
                    if (!this.form.amount || parseFloat(this.form.amount) <= 0) { missing.push('Amount'); valid = false; }
                    if (!this.form.payment_method) { missing.push('Payment Method'); valid = false; }
                    if (!this.form.currency_id) { missing.push('Currency'); valid = false; }
                    if (!valid) { showToast('error', 'Please fill in required fields: ' + missing.join(', ') + '.'); }
                    return valid;
                },

                saveViaAjax() {
                    if (!this.validateForm()) return;
                    const form = document.getElementById('paymentForm');
                    const fd = new FormData(form);

                    showToast('info', 'Saving...');
                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd,
                    })
                    .then(r => r.json().then(data => ({ status: r.status, data })))
                    .then(({ status, data }) => {
                        if (data.success) {
                            this.isSaved = true;
                            showToast('success', data.message);
                            if (this.saveAction === 'save_new') {
                                setTimeout(() => window.location.href = '{{ route("accounting.payment-make") }}', 800);
                            } else if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        } else {
                            showToast('error', data.message || 'Save failed.');
                        }
                    })
                    .catch(err => showToast('error', 'Network error: ' + (err.message || 'Unknown')));
                },

                applyColumnConfig() {
                    this.columnConfig.forEach(col => {
                        const ths = document.querySelectorAll('th[data-col="' + col.key + '"]');
                        const tds = document.querySelectorAll('td[data-col="' + col.key + '"]');
                        ths.forEach(th => th.style.display = col.visible ? '' : 'none');
                        tds.forEach(td => td.style.display = col.visible ? '' : 'none');
                    });
                    this.showConfigModal = false;
                    showToast('success', 'Column visibility updated.');
                },
            }
        }

        function paymentDocCenter(initialTools) {
            return {
                documents: initialTools || [],
                uploadDocument(e, paymentId) {
                    const file = e.target.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('file', file);
                    const btn = e.target.closest('.btn-tool');
                    const origText = btn.innerHTML;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
                    btn.style.pointerEvents = 'none';
                    fetch('/accounting/payment/' + paymentId + '/documents', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.document) {
                            const doc = data.document;
                            this.documents.push({
                                id: doc.id, file_name: doc.file_name, file_extension: doc.file_extension || '',
                                file_size: doc.file_size, uploader_name: doc.uploader?.name || '{{ auth()->user()->name ?? "N/A" }}',
                                created_at: doc.created_at ? doc.created_at.substring(0, 10) : new Date().toISOString().split('T')[0]
                            });
                            showToast('success', 'Document uploaded.');
                        } else { showToast('error', data.message || 'Upload failed.'); }
                    })
                    .catch(() => showToast('error', 'Upload error.'))
                    .finally(() => { btn.innerHTML = origText; btn.style.pointerEvents = ''; e.target.value = ''; });
                },
                deleteDocument(id, idx) {
                    if (!confirm('Delete this document?')) return;
                    fetch('/accounting/payment/documents/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(r => r.json())
                    .then(data => { if (data.success) { this.documents.splice(idx, 1); showToast('success', 'Deleted.'); } })
                    .catch(() => showToast('error', 'Delete failed.'));
                },
                downloadDocument(id) { window.location.href = '/accounting/payment/documents/' + id + '/download'; }
            }
        }

        function paymentMemos(initialMemos) {
            return {
                memos: initialMemos || [],
                showMemoModal: false,
                editingMemoId: null,
                memoForm: { subject: '', content: '' },
                openAddMemo() { this.editingMemoId = null; this.memoForm = { subject: '', content: '' }; this.showMemoModal = true; },
                editMemo(id, subject, content) { this.editingMemoId = id; this.memoForm = { subject, content }; this.showMemoModal = true; },
                saveMemo(paymentId) {
                    if (!this.memoForm.subject.trim()) { showToast('error', 'Subject is required.'); return; }
                    const url = this.editingMemoId ? '/accounting/payment/memos/' + this.editingMemoId : '/accounting/payment/' + paymentId + '/memos';
                    const method = this.editingMemoId ? 'PUT' : 'POST';
                    fetch(url, {
                        method, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.memoForm)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.memo) {
                            const m = data.memo;
                            const now = new Date().toISOString().split('T')[0];
                            if (this.editingMemoId) {
                                const idx = this.memos.findIndex(mm => mm.id === this.editingMemoId);
                                if (idx !== -1) this.memos.splice(idx, 1, { id: m.id, subject: m.subject, content: m.content || '', user_name: m.user?.name || '{{ auth()->user()->name ?? "N/A" }}', created_at: m.created_at ? m.created_at.substring(0, 10) : now });
                            } else {
                                this.memos.push({ id: m.id, subject: m.subject, content: m.content || '', user_name: m.user?.name || '{{ auth()->user()->name ?? "N/A" }}', created_at: m.created_at ? m.created_at.substring(0, 10) : now });
                            }
                            this.showMemoModal = false;
                            showToast('success', 'Memo saved.');
                        } else { showToast('error', 'Failed to save memo.'); }
                    })
                    .catch(() => showToast('error', 'Error saving memo.'));
                },
                deleteMemo(id, idx) {
                    if (!confirm('Delete this memo?')) return;
                    fetch('/accounting/payment/memos/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(r => r.json())
                    .then(data => { if (data.success) { this.memos.splice(idx, 1); showToast('success', 'Deleted.'); } })
                    .catch(() => showToast('error', 'Delete failed.'));
                }
            }
        }

        function showToast(type, msg) {
            const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
            const container = document.getElementById('toast-container') || (() => {
                const c = document.createElement('div');
                c.id = 'toast-container';
                c.style.cssText = 'position:fixed;top:56px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:6px;pointer-events:none;';
                document.body.appendChild(c);
                return c;
            })();
            const t = document.createElement('div');
            t.style.cssText = 'background:#1e293b;color:#fff;padding:8px 14px;border-radius:4px;font-size:11px;box-shadow:0 4px 16px rgba(0,0,0,0.25);display:flex;align-items:center;gap:8px;animation:toastIn 0.25s ease;pointer-events:all;border-left:3px solid ' + (type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6');
            t.innerHTML = '<i class="fa fa-' + icons[type] + '"></i> ' + msg;
            container.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
    </script>
    @endpush
</x-layout>
