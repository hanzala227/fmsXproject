<x-layout>
    @php
        $editMode = isset($invoice);
        $invoiceNo = $editMode ? $invoice->invoice_no : 'INV-' . date('ymdHis');
        $docData = [];
        if ($editMode) {
            $docData = $invoice->documents->map(fn($d) => [
                'id' => $d->id, 'file_name' => $d->file_name, 'file_extension' => $d->file_extension,
                'file_size' => $d->file_size, 'uploader_name' => $d->uploader->name ?? 'N/A',
                'created_at' => $d->created_at?->format('Y-m-d') ?? '',
            ])->values()->toArray() ?? [];
        }
    @endphp
    @push('styles')
    <x-form-styles />
    <style>
        [x-cloak] { display: none !important; }
        .form-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px 12px; }
        .form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px 12px; }
        @media (max-width: 1400px) { .form-grid-4 { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 1100px) { .form-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .form-grid-4, .form-grid-3 { grid-template-columns: 1fr; } }

        .btn-tool { background: #fff; border: 1px solid #cbd5e1; padding: 2px 8px; font-size: 10px; color: #334155; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px; height: 22px; border-radius: 2px; transition: all 0.15s; white-space: nowrap; box-sizing: border-box; }
        .btn-tool:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool.green { background: #3b82f6 !important; color: #fff !important; border-color: #2563eb !important; font-weight: 600; }
        .btn-tool.green:hover { background: #2563eb !important; }
        .btn-tool-icon { background: #fff; border: 1px solid #cbd5e1; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; color: #475569; border-radius: 2px; transition: all 0.2s; }
        .btn-tool-icon:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool-icon.green { background: #22c55e; color: #fff; border-color: #16a34a; }
        .btn-tool-icon.green:hover { background: #16a34a; }

        .action-bar { text-align: center; padding: 15px 0; margin-top: 15px; display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }

        .total-row { background: #f8fafc !important; font-weight: 700; font-size: 10px; color: #0f172a; }
        .total-label-cell { text-align: right; padding-right: 10px !important; color: #3b82f6; }

        .input-number { text-align: right; }
        .is-invalid { border-color: #dc2626 !important; }

        .totals-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px 12px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; }
        .total-item { padding: 4px 6px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 3px; }
        .total-item .label { font-size: 8px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .total-item .value { font-size: 13px; font-weight: 700; color: #1e293b; text-align: right; }
        .total-item .value.primary { color: #3b82f6; }

        .timeline { list-style: none; padding: 10px 0; margin: 0; position: relative; }
        .timeline:before { content: ''; position: absolute; top: 0; bottom: 0; width: 2px; background: #e2e8f0; left: 120px; }
        .timeline-log { position: relative; margin-bottom: 20px; display: flex; }
        .timeline-time { width: 110px; text-align: right; padding-right: 20px; font-size: 10px; color: #64748b; }
        .timeline-icon { width: 22px; height: 22px; background: #3b82f6; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; position: absolute; left: 110px; z-index: 5; }
        .timeline-body { flex: 1; padding-left: 28px; }
        .timeline-body h2 { font-size: 11px; font-weight: 700; margin: 0 0 3px; color: #1e293b; }
        .timeline-content { font-size: 10px; color: #64748b; background: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; border-radius: 2px; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); display: flex; align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(2px); }
        .modal-container { background: #fff; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; border-top: 3px solid #3b82f6; max-width: 500px; width: 90%; }
        .modal-header { padding: 10px 15px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #0f172a; }
        .modal-body { padding: 15px; max-height: 75vh; overflow-y: auto; }
        .modal-footer { padding: 10px 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right; }
    </style>
    @endpush

    <div class="page-content" x-data="invoiceCreateApp()">
        <form id="invoiceForm" action="{{ $editMode ? route('accounting.invoices.update', $invoice->id) : route('accounting.invoices.store') }}" method="POST">
            @csrf
            @if($editMode) @method('PUT') @endif
            <input type="hidden" name="save_action" :value="saveAction">
            <input type="hidden" name="lines_json" :value="JSON.stringify(lines)">
            <input type="hidden" name="subtotal" :value="totals.subtotal.toFixed(2)">
            <input type="hidden" name="tax_total" :value="totals.tax.toFixed(2)">
            <input type="hidden" name="total_amount" :value="totals.total.toFixed(2)">
            <input type="hidden" name="balance_amount" :value="totals.balance.toFixed(2)">
            <input type="hidden" name="invoiceable_type" :value="form.invoiceable_type">
            <input type="hidden" name="invoiceable_id" :value="form.invoiceable_id">

            @if(session('success'))
                <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:12px;">
                    <strong><i class="fa fa-exclamation-circle"></i> Validation Error</strong>
                    <ul style="margin:5px 0 0 15px;padding:0;">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <!-- Breadcrumb -->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('accounting.invoices.index') }}">Accounting</a> <i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('accounting.invoices.index') }}">Invoices</a> <i class="fa fa-angle-right"></i></li>
                    <li><span style="color:#333;font-weight:700;">{{ $editMode ? 'Edit: ' . $invoice->invoice_no : 'New Invoice' }}</span></li>
                </ul>
            </div>

            <!-- Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <h1 class="caption-subject" style="font-size:18px;margin:0;">
                    <i class="fa fa-file-text-o"></i> Invoice — {{ $editMode ? 'Edit' : 'New Entry' }}
                </h1>
                <div style="display:flex;gap:4px;">
                    <button type="button" class="btn-freightx" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> @if($editMode) UPDATE @else SAVE @endif</button>
                    <a href="{{ route('accounting.invoices.index') }}" class="btn-default-gf">BACK TO LIST</a>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="gf-tabs">
                <li :class="activeTab === 'basic' ? 'active' : ''"><a @click="activeTab = 'basic'">Basic</a></li>
                <li :class="(activeTab === 'lines' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'lines' : null">
                    <a @click="switchTab('lines')">Line Items</a>
                </li>
                <li :class="(activeTab === 'charges' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'charges' : null">
                    <a @click="switchTab('charges')">Charges</a>
                </li>
                <li :class="(activeTab === 'notes' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'notes' : null">
                    <a @click="switchTab('notes')">Notes</a>
                </li>
                <li style="display: none !important;" :class="(activeTab === 'doc' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'doc' : null">
                    <a @click="switchTab('doc')">Doc Center</a>
                </li>
            </ul>

            <!-- ==================== BASIC TAB ==================== -->
            <div x-show="activeTab === 'basic'" class="main-grid">
                <div class="portlet light">
                    <div class="portlet-title" style="cursor:pointer;">
                        <span class="caption-subject"><i class="fa fa-file-text-o"></i> Invoice Details</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-4">
                            <!-- Column 1 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Invoice No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="invoice_no" class="form-control-gf" x-model="form.invoice_no" placeholder="(Auto Generated on Save)" readonly style="background:#f1f5f9;color:#64748b;font-weight:600;">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Invoice Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="invoice_date" class="form-control-gf" x-model="form.invoice_date" required>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Due Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="due_date" class="form-control-gf" x-model="form.due_date">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Type</label>
                                    <div class="form-input-container">
                                        <select name="type" class="form-control-gf" x-model="form.type" required>
                                            <option value="AR">AR — Receivable</option>
                                            <option value="AP">AP — Payable</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Column 2 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Bill To</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="bill_to_id" :options="$tradePartners" module="trade-partner" x-model="form.bill_to_id" class="form-control-gf" />
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Currency</label>
                                    <div class="form-input-container">
                                        <select name="currency_id" class="form-control-gf" x-model="form.currency_id" required>
                                            <option value="">Select...</option>
                                            @foreach($currencies as $c)
                                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Paid Amount</label>
                                    <div class="form-input-container">
                                        <input type="number" step="0.01" name="paid_amount" class="form-control-gf input-number" x-model="form.paid_amount" @input="recalcTotals()">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Status</label>
                                    <div class="form-input-container">
                                        <select name="status" class="form-control-gf" x-model="form.status">
                                            <option value="DRAFT">Draft</option>
                                            <option value="POSTED">Posted</option>
                                            <option value="PAID">Paid</option>
                                            <option value="PARTIAL">Partial</option>
                                            <option value="VOID">Void</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Column 3 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Office</label>
                                    <div class="form-input-container">
                                        <select name="office_id" class="form-control-gf" x-model="form.office_id">
                                            <option value="">Select...</option>
                                            @foreach($offices as $o)
                                            <option value="{{ $o->id }}">{{ $o->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Issued By</label>
                                    <div class="form-input-container">
                                        <select name="issued_by" class="form-control-gf" x-model="form.issued_by">
                                            <option value="">Select...</option>
                                            @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Discount %</label>
                                    <div class="form-input-container">
                                        <input type="number" step="0.01" min="0" max="100" name="discount_pct" class="form-control-gf input-number" x-model="form.discount_pct" @input="recalcTotals()">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Tax %</label>
                                    <div class="form-input-container">
                                        <input type="number" step="0.01" min="0" max="100" name="tax_pct" class="form-control-gf input-number" x-model="form.tax_pct" @input="recalcTotals()">
                                    </div>
                                </div>
                            </div>
                            <!-- Column 4 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">MBL No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="mbl_no" class="form-control-gf" x-model="form.mbl_no" placeholder="MBL / MAWB No.">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Shipping/Other</label>
                                    <div class="form-input-container">
                                        <input type="number" step="0.01" name="shipping_amount" class="form-control-gf input-number" x-model="form.shipping_amount" @input="recalcTotals()">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Billing Address</label>
                                    <div class="form-input-container">
                                        <input type="text" name="billing_address" class="form-control-gf" x-model="form.billing_address" placeholder="Address">
                                    </div>
                                </div>
                                <div class="form-group-gf" style="align-items:flex-start;">
                                    <label class="form-label-gf" style="margin-top:3px;">Remark</label>
                                    <div class="form-input-container">
                                        <textarea name="internal_remark" class="form-control-gf" style="height:50px;resize:vertical;" x-model="form.internal_remark" placeholder="Internal notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="totals-grid">
                            <div class="total-item">
                                <div class="label">Line Subtotal</div>
                                <div class="value" x-text="totals.lineSubtotal.toFixed(2)"></div>
                            </div>
                            <div class="total-item">
                                <div class="label">Discount</div>
                                <div class="value" style="color:#e08283;" x-text="'-' + totals.discount.toFixed(2)"></div>
                            </div>
                            <div class="total-item">
                                <div class="label">Tax</div>
                                <div class="value" style="color:#e08283;" x-text="totals.tax.toFixed(2)"></div>
                            </div>
                            <div class="total-item">
                                <div class="label">Shipping</div>
                                <div class="value" x-text="totals.shipping.toFixed(2)"></div>
                            </div>
                            <div class="total-item" style="grid-column:span 2;">
                                <div class="label">Total Amount</div>
                                <div class="value primary" x-text="totals.total.toFixed(2)"></div>
                            </div>
                            <div class="total-item">
                                <div class="label">Paid</div>
                                <div class="value" x-text="totals.paid.toFixed(2)"></div>
                            </div>
                            <div class="total-item">
                                <div class="label">Balance Due</div>
                                <div class="value primary" x-text="totals.balance.toFixed(2)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== LINE ITEMS TAB ==================== -->
            <div x-show="activeTab === 'lines'" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-list"></i> Invoice Line Items</span>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <button type="button" class="btn-tool-icon green" @click="addLine()" title="Add line"><i class="fa fa-plus"></i></button>
                            <button type="button" class="btn-tool-icon" @click="copySelectedLines()" :disabled="selectedLines.length === 0" title="Copy selected"><i class="fa fa-copy"></i></button>
                            <button type="button" class="btn-tool-icon" @click="deleteSelectedLines()" :disabled="selectedLines.length === 0" title="Delete selected" style="color:#ef4444;"><i class="fa fa-trash"></i></button>
                            <span style="font-size:10px;color:#64748b;margin-left:auto;">Lines: <strong x-text="lines.length"></strong></span>
                        </div>
                        <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:3px;">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:28px;text-align:center;"><input type="checkbox" @change="toggleAllLines($event.target.checked)"></th>
                                        <th style="width:22px;text-align:center;">#</th>
                                        <th style="width:100px;">Charge Code</th>
                                        <th>Description</th>
                                        <th style="width:55px;">Type</th>
                                        <th style="width:55px;text-align:right;"><span style="color:#ef4444;">*</span> Qty</th>
                                        <th style="width:65px;text-align:right;"><span style="color:#ef4444;">*</span> Rate</th>
                                        <th style="width:65px;text-align:right;">Amount</th>
                                        <th style="width:35px;">Act.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(line, idx) in lines" :key="idx">
                                        <tr>
                                            <td style="text-align:center;"><input type="checkbox" :checked="selectedLines.includes(idx)" @change="toggleLineSelection(idx, $event.target.checked)"></td>
                                            <td style="text-align:center;" x-text="idx + 1"></td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="line.charge_code" placeholder="FREIGHT"></td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="line.description" placeholder="Description"></td>
                                            <td>
                                                <select class="form-control-gf" style="height:18px;font-size:9px;" x-model="line.type">
                                                    <option value="AR">AR</option>
                                                    <option value="AP">AP</option>
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:18px;font-size:9px;" x-model="line.qty" @input="calcLineAmount(line); recalcTotals()"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:18px;font-size:9px;" x-model="line.rate" @input="calcLineAmount(line); recalcTotals()"></td>
                                            <td style="text-align:right;font-weight:600;color:#3b82f6;" x-text="((parseFloat(line.qty)||0) * (parseFloat(line.rate)||0)).toFixed(2)"></td>
                                            <td style="text-align:center;">
                                                <button type="button" class="btn-tool-icon" style="width:18px;height:18px;font-size:8px;color:#ef4444;" @click="lines.splice(idx,1); recalcTotals()" title="Delete"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="lines.length === 0">
                                        <tr><td colspan="9" style="text-align:center;padding:20px;color:#94a3b8;font-style:italic;"><i class="fa fa-inbox" style="font-size:20px;display:block;margin-bottom:4px;"></i>No line items. Click <strong style="color:#3b82f6;cursor:pointer;" @click="addLine()">+</strong> to add.</td></tr>
                                    </template>
                                    <tr class="total-row" x-show="lines.length > 0">
                                        <td colspan="6" style="text-align:right;padding-right:10px;color:#3b82f6;">Total</td>
                                        <td style="text-align:right;" x-text="totals.lineSubtotal.toFixed(2)"></td>
                                        <td></td><td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== CHARGES TAB ==================== -->
            <div x-show="activeTab === 'charges'" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-calculator"></i> Additional Charges &amp; Summary</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-3">
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="width:80px;">Discount %</label>
                                <div class="form-input-container">
                                    <input type="number" step="0.01" class="form-control-gf input-number" x-model="form.discount_pct" @input="recalcTotals()">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="width:80px;">Tax %</label>
                                <div class="form-input-container">
                                    <input type="number" step="0.01" class="form-control-gf input-number" x-model="form.tax_pct" @input="recalcTotals()">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="width:80px;">Shipping</label>
                                <div class="form-input-container">
                                    <input type="number" step="0.01" class="form-control-gf input-number" x-model="form.shipping_amount" @input="recalcTotals()">
                                </div>
                            </div>
                        </div>
                        <div class="totals-grid" style="border-top:1px solid #e2e8f0;padding-top:10px;">
                            <div class="total-item"><div class="label">Line Subtotal</div><div class="value" x-text="totals.lineSubtotal.toFixed(2)"></div></div>
                            <div class="total-item"><div class="label">Discount</div><div class="value" style="color:#e08283;" x-text="'-' + totals.discount.toFixed(2)"></div></div>
                            <div class="total-item"><div class="label">Tax</div><div class="value" style="color:#e08283;" x-text="totals.tax.toFixed(2)"></div></div>
                            <div class="total-item"><div class="label">Shipping</div><div class="value" x-text="totals.shipping.toFixed(2)"></div></div>
                            <div class="total-item" style="grid-column:span 4;"><div class="label" style="font-size:11px;">Total Amount</div><div class="value primary" style="font-size:20px;" x-text="totals.total.toFixed(2)"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== NOTES TAB ==================== -->
            <div x-show="activeTab === 'notes'" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-sticky-note-o"></i> Notes &amp; Memos</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-group-gf" style="align-items:flex-start;">
                            <label class="form-label-gf">Billing Address</label>
                            <div class="form-input-container">
                                <textarea class="form-control-gf" style="height:50px;resize:vertical;" x-model="form.billing_address"></textarea>
                            </div>
                        </div>
                        <div class="form-group-gf" style="align-items:flex-start;">
                            <label class="form-label-gf">Internal Remark</label>
                            <div class="form-input-container">
                                <textarea class="form-control-gf" style="height:60px;resize:vertical;" x-model="form.internal_remark" placeholder="Internal notes..."></textarea>
                            </div>
                        </div>

                        <!-- Memo Section -->
                        <hr style="border:0;border-top:1px solid #e2e8f0;margin:8px 0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;padding:0 2px;">
                            <span class="caption-subject" style="font-size:11px;"><i class="fa fa-bell"></i> Memo</span>
                            <button type="button" class="btn-tool-icon" style="background:#22c55e;color:#fff;border-color:#16a34a;" @click="openMemoModal()"><i class="fa fa-plus"></i></button>
                        </div>
                        <div style="display:flex;gap:0;min-height:120px;border:1px solid #e2e8f0;border-radius:3px;">
                            <div style="flex:1;border-right:1px solid #e2e8f0;max-width:45%;">
                                <table class="table-custom" style="border:none;margin:0;">
                                    <thead><tr><th style="width:28px;"></th><th style="text-align:left;">Subject</th><th style="width:100px;">Date</th><th style="width:60px;text-align:center;">Action</th></tr></thead>
                                    <tbody>
                                        <template x-if="memos.length === 0">
                                            <tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;font-style:italic;">No memos yet.</td></tr>
                                        </template>
                                        <template x-for="(memo, idx) in memos" :key="idx">
                                            <tr @click="selectedMemo = idx" :style="selectedMemo === idx ? 'background:#eff6ff;' : ''" style="cursor:pointer;">
                                                <td style="text-align:center;"><i class="fa fa-bell" style="color:#22c55e;font-size:9px;"></i></td>
                                                <td x-text="memo.subject"></td>
                                                <td x-text="memo.date || memo.updated_at || '-'"></td>
                                                <td style="text-align:center;">
                                                    <button type="button" @click.stop="editMemo(idx)" class="btn-tool-icon" style="width:16px;height:16px;font-size:8px;" title="Edit"><i class="fa fa-pencil"></i></button>
                                                    <button type="button" @click.stop="deleteMemo(idx)" class="btn-tool-icon" style="width:16px;height:16px;font-size:8px;color:#ef4444;" title="Delete"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div style="flex:1;display:flex;flex-direction:column;">
                                <div style="padding:4px 8px;border-bottom:1px solid #e2e8f0;background:#fff;min-height:26px;display:flex;align-items:center;">
                                    <span style="font-size:10px;font-weight:600;color:#64748b;">Memo Content</span>
                                </div>
                                <div style="flex:1;padding:6px;display:flex;">
                                    <textarea class="form-control-gf" style="flex:1;min-height:80px;resize:vertical;border:none;border-radius:0;" placeholder="Select a memo to view content..." x-model="memos[selectedMemo]?.content" :disabled="selectedMemo === null"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== DOC CENTER TAB ==================== -->
            <div x-show="activeTab === 'doc'" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-folder-open"></i> Document Center</span>
                        <div style="display:flex;gap:4px;">
                            <label class="btn-freightx" style="padding:2px 8px;font-size:9px;cursor:pointer;">
                                <i class="fa fa-upload"></i> Upload
                                <input type="file" x-ref="docInput" style="display:none;" @change="uploadDocument($event)">
                            </label>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar" style="margin-bottom:8px;">
                            <button type="button" class="btn-tool-icon" @click="deleteSelectedDocs()" :disabled="selectedDocIds.length === 0" title="Delete selected"><i class="fa fa-trash" style="color:#ef4444;"></i></button>
                            <span style="font-size:10px;color:#64748b;">Documents: <strong x-text="documents.length"></strong></span>
                        </div>
                        <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:3px;">
                            <table class="table-custom">
                                <thead><tr>
                                    <th style="width:28px;text-align:center;"><input type="checkbox" @change="toggleAllDocs($event.target.checked)"></th>
                                    <th>File Name</th><th style="width:70px;">Size</th><th style="width:80px;">Type</th><th style="width:90px;">Uploaded</th><th style="width:70px;">Uploader</th><th style="width:80px;text-align:center;">Action</th>
                                </tr></thead>
                                <tbody>
                                    <template x-for="(doc, idx) in documents" :key="doc.id || idx">
                                        <tr>
                                            <td style="text-align:center;"><input type="checkbox" :value="idx" x-model="selectedDocIds"></td>
                                            <td><i class="fa fa-file-text-o" style="color:#64748b;margin-right:4px;"></i><span x-text="doc.file_name"></span></td>
                                            <td x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '-'"></td>
                                            <td x-text="doc.file_extension || '-'"></td>
                                            <td x-text="doc.created_at || '-'"></td>
                                            <td x-text="doc.uploader_name || 'N/A'"></td>
                                            <td style="text-align:center;">
                                                <a :href="'/accounting/invoice/documents/' + doc.id + '/download'" class="btn-tool-icon" style="width:18px;height:18px;font-size:9px;text-decoration:none;" title="Download"><i class="fa fa-download"></i></a>
                                                <button type="button" class="btn-tool-icon" style="width:18px;height:18px;font-size:9px;color:#ef4444;" @click="deleteDocument(doc.id, idx)" title="Delete"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="documents.length === 0">
                                        <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;"><i class="fa fa-folder-open-o" style="font-size:24px;display:block;margin-bottom:6px;"></i>No documents uploaded. Click <strong style="color:#3b82f6;cursor:pointer;" @click="$refs.docInput.click()">Upload</strong> to add files.</td></tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top:10px;padding:15px;border:1px dashed #cbd5e1;border-radius:4px;text-align:center;background:#f8fafc;"
                             @dragover.prevent="$event.dataTransfer.dropEffect = 'copy'"
                             @drop.prevent="handleDrop($event)">
                            <i class="fa fa-cloud-upload" style="font-size:18px;color:#3b82f6;display:block;margin-bottom:4px;"></i>
                            <span style="font-size:10px;color:#64748b;">Drag &amp; drop files here or <strong style="color:#3b82f6;cursor:pointer;" @click="$refs.docInput.click()">browse</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="action-bar">
                <button type="button" class="btn-tool green" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> @if($editMode) UPDATE @else SAVE @endif</button>
                @if(!$editMode)
                <button type="button" class="btn-tool" @click="handleSubmit('save_new')"><i class="fa fa-plus-circle"></i> SAVE &amp; NEW</button>
                @endif
                <a href="{{ route('accounting.invoices.index') }}" class="btn-tool" target="_blank"><i class="fa fa-arrow-left"></i> CANCEL</a>
            </div>
        </form>

        <!-- Memo Modal -->
        <div x-show="memoModalOpen" x-cloak class="modal-overlay" @click.away="memoModalOpen = false">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <span><i class="fa fa-sticky-note-o" style="color:#3b82f6;margin-right:6px;"></i> <span x-text="memoEditIndex === -1 ? 'Add Memo' : 'Edit Memo'"></span></span>
                    <button type="button" @click="memoModalOpen = false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Subject <span style="color:#ef4444;">*</span></label>
                        <input type="text" x-model="memoForm.subject" placeholder="Memo subject..." class="form-control-gf" style="height:32px;font-size:11px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Content</label>
                        <textarea x-model="memoForm.content" placeholder="Memo details..." class="form-control-gf" style="height:80px;font-size:11px;resize:vertical;padding:4px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-default-gf" @click="memoModalOpen = false" style="margin-right:6px;">Cancel</button>
                    <button type="button" class="btn-tool green" @click="saveMemo()"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function invoiceCreateApp() {
            return {
                activeTab: 'basic',
                saveAction: 'save_close',
                isSaved: {{ $editMode ? 'true' : 'false' }},

                form: {
                    invoice_no: '{{ old("invoice_no", $editMode ? $invoice->invoice_no : $invoiceNo) }}',
                    invoiceable_type: '{{ old("invoiceable_type", $editMode ? $invoice->invoiceable_type : ($invoiceableType ?? "")) }}',
                    invoiceable_id: '{{ old("invoiceable_id", $editMode ? $invoice->invoiceable_id : ($invoiceableId ?? "")) }}',
                    invoice_date: '{{ old("invoice_date", $editMode && $invoice->invoice_date ? $invoice->invoice_date->format("Y-m-d") : date("Y-m-d")) }}',
                    due_date: '{{ old("due_date", $editMode && $invoice->due_date ? $invoice->due_date->format("Y-m-d") : "") }}',
                    type: '{{ old("type", $editMode ? $invoice->type : ($defaultType ?? "AR")) }}',
                    bill_to_id: '{{ old("bill_to_id", $editMode ? $invoice->bill_to_id : "") }}',
                    currency_id: '{{ old("currency_id", $editMode ? $invoice->currency_id : "") }}',
                    office_id: '{{ old("office_id", $editMode ? $invoice->office_id : "") }}',
                    issued_by: '{{ old("issued_by", $editMode ? $invoice->issued_by : "") }}',
                    billing_address: '{!! old("billing_address", $editMode ? $invoice->billing_address : "") !!}',
                    status: '{{ old("status", $editMode ? $invoice->status : "DRAFT") }}',
                    paid_amount: '{{ old("paid_amount", $editMode ? $invoice->paid_amount : "0") }}',
                    internal_remark: '{!! old("internal_remark", $editMode ? ($invoice->internal_remark ?? "") : "") !!}',
                    discount_pct: '{{ old("discount_pct", $editMode ? $invoice->discount_pct : "0") }}',
                    tax_pct: '{{ old("tax_pct", $editMode ? $invoice->tax_pct : "0") }}',
                    shipping_amount: '{{ old("shipping_amount", $editMode ? $invoice->shipping_amount : "0") }}',
                },

                lines: [],
                selectedLines: [],
                totals: { lineSubtotal: 0, discount: 0, shipping: 0, tax: 0, subtotal: 0, total: 0, paid: 0, balance: 0 },

                documents: @json($docData),
                selectedDocIds: [],
                memos: @json($invoice->memos_data ?? []),
                selectedMemo: null,

                memoModalOpen: false,
                memoEditIndex: -1,
                memoForm: { subject: '', content: '' },

                init() {
                    @if($editMode && $invoice->lines->count() > 0)
                        const rawLines = @json($invoice->lines);
                        this.lines = rawLines.map(function(l) {
                            return {
                                charge_code: l.charge_code,
                                description: l.description,
                                type: l.type,
                                qty: parseFloat(l.qty) || 0,
                                rate: parseFloat(l.rate) || 0,
                                amount: parseFloat(l.amount) || 0,
                            };
                        });
                    @endif
                    @if($editMode && isset($invoice->memos_data) && $invoice->memos_data)
                        this.memos = @json($invoice->memos_data);
                    @endif
                    this.recalcTotals();
                    if (!this.isSaved) this.activeTab = 'basic';
                },

                handleSubmit(action) {
                    this.saveAction = action;
                    this.$nextTick(() => {
                        this.saveViaAjax();
                    });
                },

                switchTab(tab) {
                    if (tab === 'basic') { this.activeTab = 'basic'; return; }
                    if (!this.isSaved) { showToast('info', 'Please save the record first.'); return; }
                    this.activeTab = tab;
                },

                validateForm() {
                    document.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
                    if (!this.form.invoice_no || !this.form.invoice_no.trim()) { showToast('error', 'Invoice No. is required.'); return false; }
                    if (!this.form.invoice_date) { showToast('error', 'Invoice Date is required.'); return false; }
                    if (!this.form.bill_to_id) { showToast('error', 'Bill To is required.'); return false; }
                    if (!this.form.currency_id) { showToast('error', 'Currency is required.'); return false; }
                    if (!this.form.type) { showToast('error', 'Type is required.'); return false; }
                    return true;
                },

                recalcTotals() {
                    const lineSubtotal = this.lines.reduce((sum, l) => {
                        return sum + (parseFloat(l.qty) || 0) * (parseFloat(l.rate) || 0);
                    }, 0);
                    const discount = (lineSubtotal * (parseFloat(this.form.discount_pct) || 0)) / 100;
                    const afterDiscount = lineSubtotal - discount;
                    const shipping = parseFloat(this.form.shipping_amount) || 0;
                    const tax = ((afterDiscount + shipping) * (parseFloat(this.form.tax_pct) || 0)) / 100;
                    const total = afterDiscount + shipping + tax;
                    const paid = parseFloat(this.form.paid_amount) || 0;
                    this.totals = { lineSubtotal, discount, shipping, tax, subtotal: afterDiscount + shipping, total, paid, balance: total - paid };
                },

                calcLineAmount(line) { line.amount = (parseFloat(line.qty) || 0) * (parseFloat(line.rate) || 0); },

                addLine() {
                    this.lines.push({ charge_code: '', description: '', type: 'AR', qty: 1, rate: 0, amount: 0 });
                    this.recalcTotals();
                    if (this.activeTab !== 'lines') this.activeTab = 'lines';
                },
                toggleLineSelection(idx, checked) {
                    if (checked) {
                        if (!this.selectedLines.includes(idx)) this.selectedLines.push(idx);
                    } else {
                        this.selectedLines = this.selectedLines.filter(i => i !== idx);
                    }
                },
                toggleAllLines(checked) { this.selectedLines = checked ? this.lines.map((_, i) => i) : []; },
                copySelectedLines() {
                    this.selectedLines.forEach(i => { this.lines.push({ ...this.lines[i] }); });
                    this.selectedLines = [];
                    this.recalcTotals();
                },
                deleteSelectedLines() {
                    if (!this.selectedLines.length) return;
                    this.selectedLines.sort((a, b) => b - a).forEach(i => this.lines.splice(i, 1));
                    this.selectedLines = [];
                    this.recalcTotals();
                },

                /* AJAX SAVE */
                saveViaAjax() {
                    if (!this.validateForm()) return;

                    const form = document.getElementById('invoiceForm');
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
                                setTimeout(() => window.location.href = '{{ route("accounting.invoices.create") }}', 800);
                            } else if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                this.activeTab = 'basic';
                            }
                        } else {
                            showToast('error', data.message || 'Save failed.');
                        }
                    })
                    .catch(err => showToast('error', 'Network error: ' + (err.message || 'Unknown')));
                },

                toggleAllDocs(checked) { this.selectedDocIds = checked ? this.documents.map((_, i) => i) : []; },
                uploadDocument(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    @if($editMode)
                    const fd = new FormData(); fd.append('file', file);
                    fetch('{{ route("accounting.invoices.documents.store", $invoice->id) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: fd })
                        .then(r => r.json()).then(d => { if (d.success) { this.documents.push(d.document); showToast('success', 'Uploaded.'); } else showToast('error', d.message || 'Failed.'); })
                        .catch(() => showToast('error', 'Upload failed.'));
                    @else
                    showToast('info', 'Please save first.');
                    @endif
                    e.target.value = '';
                },
                handleDrop(e) {
                    const files = e.dataTransfer.files;
                    if (!files.length) return;
                    @if($editMode)
                    const fd = new FormData(); fd.append('file', files[0]);
                    fetch('{{ route("accounting.invoices.documents.store", $invoice->id) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: fd })
                        .then(r => r.json()).then(d => { if (d.success) { this.documents.push(d.document); showToast('success', 'Uploaded.'); } else showToast('error', d.message || 'Failed.'); })
                        .catch(() => showToast('error', 'Upload failed.'));
                    @else
                    showToast('info', 'Please save first.');
                    @endif
                },
                deleteDocument(id, idx) {
                    if (!id) { this.documents.splice(idx, 1); return; }
                    if (!confirm('Delete this document?')) return;
                    fetch('/accounting/invoice/documents/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                        .then(r => r.json()).then(d => { if (d.success) { this.documents.splice(idx, 1); showToast('success', 'Deleted.'); } })
                        .catch(() => showToast('error', 'Delete failed.'));
                },
                deleteSelectedDocs() {
                    if (!this.selectedDocIds.length) return;
                    if (!confirm('Delete ' + this.selectedDocIds.length + ' document(s)?')) return;
                    [...this.selectedDocIds].sort((a, b) => b - a).forEach(i => {
                        const doc = this.documents[i];
                        if (doc && doc.id) fetch('/accounting/invoice/documents/' + doc.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                        this.documents.splice(i, 1);
                    });
                    this.selectedDocIds = [];
                    showToast('success', 'Documents deleted.');
                },

                openMemoModal() {
                    this.memoEditIndex = -1;
                    this.memoForm = { subject: '', content: '' };
                    this.memoModalOpen = true;
                },
                editMemo(idx) {
                    this.memoEditIndex = idx;
                    this.memoForm = { subject: this.memos[idx].subject, content: this.memos[idx].content || '' };
                    this.memoModalOpen = true;
                },
                saveMemo() {
                    if (!this.memoForm.subject.trim()) { showToast('error', 'Subject is required.'); return; }
                    if (this.memoEditIndex === -1) {
                        this.memos.push({ subject: this.memoForm.subject, content: this.memoForm.content, date: new Date().toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' }) });
                        this.selectedMemo = this.memos.length - 1;
                    } else {
                        this.memos[this.memoEditIndex].subject = this.memoForm.subject;
                        this.memos[this.memoEditIndex].content = this.memoForm.content;
                    }
                    this.memoModalOpen = false;
                },
                deleteMemo(idx) {
                    if (!confirm('Delete this memo?')) return;
                    this.memos.splice(idx, 1);
                    if (this.selectedMemo === idx) this.selectedMemo = null;
                    else if (this.selectedMemo > idx) this.selectedMemo--;
                },
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
