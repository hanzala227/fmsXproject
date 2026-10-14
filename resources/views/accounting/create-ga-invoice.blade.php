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

        .btn-tool-icon { background: #fff; border: 1px solid #cbd5e1; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; color: #475569; border-radius: 2px; transition: all 0.2s; }
        .btn-tool-icon:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool-icon-blue { background: #22c55e; color: #fff; border-color: #16a34a; }
        .btn-tool-icon-blue:hover { background: #16a34a; }

        .action-bar { text-align: center; padding: 15px 0; margin-top: 15px; display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }

        .input-number { text-align: right; }
        .is-invalid { border-color: #dc2626 !important; }

        .total-row td { background: #f8fafc !important; font-weight: 700; font-size: 10px; color: #0f172a; }
        .total-label { text-align: right; padding-right: 10px !important; color: #3b82f6; }
        .total-value { background: #fff !important; text-align: right; padding-right: 4px !important; font-size: 10px; color: #1e293b; }
    </style>
    @endpush

    <div class="page-content" x-data="gaInvoiceForm()">
        <form id="gaInvoiceForm" action="{{ route('accounting.invoices.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="AR">
            <input type="hidden" name="save_action" :value="saveAction">
            <input type="hidden" name="issued_by" value="{{ auth()->id() }}">
            <input type="hidden" name="lines_json" :value="JSON.stringify(lines)">

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
                    <li><a href="{{ route('accounting.invoices.index') }}">Invoice</a> <i class="fa fa-angle-right"></i></li>
                    <li><span style="color:#333;font-weight:700;">New Entry</span></li>
                </ul>
            </div>

            <!-- Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <h1 class="caption-subject" style="font-size:18px;margin:0;">
                    <i class="fa fa-file-text-o"></i> General and Administrative Invoice (A/R) — New Entry
                </h1>
                <div style="display:flex;gap:4px;">
                    <button type="button" class="btn-freightx" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> SAVE</button>
                    <a href="{{ route('accounting.invoices.index') }}" class="btn-default-gf">BACK TO LIST</a>
                </div>
            </div>

            <!-- Main Details -->
            <div class="portlet light">
                <div class="portlet-title" style="cursor:pointer;">
                    <span class="caption-subject"><i class="fa fa-file-text-o"></i> Invoice Details</span>
                </div>
                <div class="portlet-body">
                    <div class="form-grid-4">
                        <!-- Column 1 -->
                        <div>
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Bill To</label>
                                <div class="form-input-container">
                                    <select name="bill_to_id" class="form-control-gf" x-model="form.bill_to_id" required>
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Invoice No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="invoice_no" class="form-control-gf" x-model="form.invoice_no" required>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Attention to</label>
                                <div class="form-input-container">
                                    <input type="text" name="attention_to" class="form-control-gf" value="{{ old('attention_to') }}" placeholder="Optional">
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Booking No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="file_no" class="form-control-gf" value="{{ old('file_no') }}" placeholder="Optional">
                                </div>
                            </div>
                        </div>
                        <!-- Column 2 -->
                        <div>
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Post Date</label>
                                <div class="form-input-container">
                                    <input type="date" name="invoice_date" class="form-control-gf" x-model="form.invoice_date" required>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Due Date</label>
                                <div class="form-input-container">
                                    <input type="date" name="due_date" class="form-control-gf" x-model="form.due_date">
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">MB/L No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="mbl_no" class="form-control-gf" value="{{ old('mbl_no') }}" placeholder="Optional">
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">HB/L No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="hbl_no" class="form-control-gf" value="{{ old('hbl_no') }}" placeholder="Optional">
                                </div>
                            </div>
                        </div>
                        <!-- Column 3 -->
                        <div>
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Office</label>
                                <div class="form-input-container">
                                    <select name="office_id" class="form-control-gf" x-model="form.office_id" required>
                                        <option value="">Select...</option>
                                        @foreach($offices as $o)
                                        <option value="{{ $o->id }}">{{ $o->code }} — {{ $o->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Currency</label>
                                <div class="form-input-container">
                                    <select name="currency_id" class="form-control-gf" x-model="form.currency_id" required>
                                        <option value="">Select...</option>
                                        @foreach($currencies as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Amount</label>
                                <div class="form-input-container">
                                    <input type="number" step="0.01" name="total_amount" class="form-control-gf input-number" x-model="form.total_amount" @input="recalcSubtotal()" required>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Tax Amount</label>
                                <div class="form-input-container">
                                    <input type="number" step="0.01" name="tax_total" class="form-control-gf input-number" x-model="form.tax_total" @input="recalcSubtotal()">
                                </div>
                            </div>
                        </div>
                        <!-- Column 4 -->
                        <div>
                            <div class="form-group-gf"><label class="form-label-gf">Subtotal</label>
                                <div class="form-input-container">
                                    <input type="number" step="0.01" name="subtotal" class="form-control-gf input-number" :value="computedSubtotal" readonly>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Discount %</label>
                                <div class="form-input-container">
                                    <input type="number" step="0.01" min="0" max="100" name="discount_pct" class="form-control-gf input-number" x-model="form.discount_pct">
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Billing Address</label>
                                <div class="form-input-container">
                                    <input type="text" name="billing_address" class="form-control-gf" value="{{ old('billing_address') }}" placeholder="Optional">
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Remark</label>
                                <div class="form-input-container">
                                    <input type="text" name="internal_remark" class="form-control-gf" value="{!! old('internal_remark') !!}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Lines Table -->
            <div class="portlet light">
                <div class="portlet-title">
                    <span class="caption-subject"><i class="fa fa-list"></i> Invoice Lines</span>
                </div>
                <div class="portlet-body">
                    <div style="display:flex;gap:4px;margin-bottom:8px;align-items:center;">
                        <button type="button" class="btn-tool-icon" style="background:#22c55e;color:#fff;border-color:#16a34a;" @click="addLine()" title="Add line"><i class="fa fa-plus"></i></button>
                        <button type="button" class="btn-default-gf" style="padding:2px 6px;" @click="addLines(5)" title="Add 5 lines">+5</button>
                        <button type="button" class="btn-default-gf" style="padding:2px 6px;" @click="copySelectedLines()" :disabled="selectedLines.length === 0"><i class="fa fa-copy"></i></button>
                        <button type="button" class="btn-default-gf" style="padding:2px 6px;" @click="deleteSelectedLines()" :disabled="selectedLines.length === 0"><i class="fa fa-trash"></i></button>
                        <select class="form-control-gf" style="width:auto;height:22px;font-size:9px;" x-model="selectedCurrency">
                            <option value="">All Currencies</option>
                            @foreach($currencies as $currency)
                            <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                            @endforeach
                        </select>
                        <span style="font-size:9px;color:#64748b;margin-left:4px;">Lines: <strong x-text="lines.length"></strong></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width:28px;text-align:center;"><input type="checkbox" @change="toggleAllLines($event.target.checked)"></th>
                                    <th style="width:22px;text-align:center;">#</th>
                                    <th style="width:100px;"><span style="color:#ef4444;">*</span> Billing Code</th>
                                    <th>Description</th>
                                    <th style="width:50px;">Unit</th>
                                    <th style="width:60px;">Currency</th>
                                    <th style="width:55px;text-align:right;"><span style="color:#ef4444;">*</span> Qty</th>
                                    <th style="width:65px;text-align:right;"><span style="color:#ef4444;">*</span> Rate</th>
                                    <th style="width:50px;text-align:right;">Tax %</th>
                                    <th style="width:65px;text-align:right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(line, idx) in lines" :key="idx">
                                    <tr>
                                        <td style="text-align:center;"><input type="checkbox" :checked="selectedLines.includes(idx)" @change="toggleLineSelection(idx, $event.target.checked)"></td>
                                        <td style="text-align:center;" x-text="idx + 1"></td>
                                        <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="line.charge_code" placeholder="Code..."></td>
                                        <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="line.description" placeholder="Description..."></td>
                                        <td>
                                            <select class="form-control-gf" style="height:18px;font-size:9px;" x-model="line.unit">
                                                <option value="UNIT">UNIT</option>
                                                <option value="HRS">HRS</option>
                                                <option value="PCS">PCS</option>
                                                <option value="KG">KG</option>
                                                <option value="LTR">LTR</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control-gf" style="height:18px;font-size:9px;" x-model="line.currency">
                                                @foreach($currencies as $currency)
                                                <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:18px;font-size:9px;" x-model="line.qty" @input="calcLineAmount(idx)"></td>
                                        <td><input type="number" step="0.01" min="0" class="form-control-gf input-number" style="height:18px;font-size:9px;" x-model="line.rate" @input="calcLineAmount(idx)"></td>
                                        <td><input type="number" step="0.01" min="0" max="100" class="form-control-gf input-number" style="height:18px;font-size:9px;" x-model="line.tax_pct" @input="calcLineAmount(idx)"></td>
                                        <td style="text-align:right;font-weight:600;color:#3b82f6;" x-text="formatAmount(line.amount)"></td>
                                    </tr>
                                </template>
                                <template x-if="lines.length === 0">
                                    <tr><td colspan="10" style="text-align:center;padding:20px;color:#94a3b8;font-style:italic;"><i class="fa fa-inbox" style="font-size:20px;display:block;margin-bottom:4px;"></i>No invoice lines. Click <span style="color:#22c55e;cursor:pointer;" @click="addLine()">+ Add Line</span></td></tr>
                                </template>
                                <tr class="total-row" x-show="lines.length > 0">
                                    <td colspan="8" style="text-align:right;padding-right:10px;color:#3b82f6;">Total Amount:</td>
                                    <td style="text-align:right;color:#1e293b;" x-text="formatAmount(totalAmount)"></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Memo Section -->
            <div class="portlet light">
                <div class="portlet-title" style="cursor:pointer;" @click="memoOpen = !memoOpen">
                    <span class="caption-subject"><i class="fa fa-sticky-note-o"></i> Memo</span>
                    <div style="display:flex;gap:4px;align-items:center;">
                        <button type="button" class="btn-tool-icon" style="background:#22c55e;color:#fff;border-color:#16a34a;" @click.stop="openMemoModal()"><i class="fa fa-plus"></i></button>
                        <i class="fa" :class="memoOpen ? 'fa-angle-up' : 'fa-angle-down'"></i>
                    </div>
                </div>
                <div class="portlet-body" x-show="memoOpen" x-collapse>
                    <div style="display:flex;gap:0;min-height:120px;">
                        <div style="flex:1;border-right:1px solid #e2e8f0;max-width:45%;">
                            <table class="table-custom" style="border:none;margin:0;">
                                <thead>
                                    <tr>
                                        <th style="width:28px;"></th>
                                        <th style="text-align:left;">Subject</th>
                                        <th style="width:100px;">Date</th>
                                        <th style="width:60px;text-align:center;">Action</th>
                                    </tr>
                                </thead>
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

            <!-- Footer Actions -->
            <div class="action-bar">
                <button type="button" class="btn-tool green" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> SAVE &amp; CLOSE</button>
                <button type="button" class="btn-tool" @click="handleSubmit('save_new')"><i class="fa fa-plus-circle"></i> SAVE &amp; NEW</button>
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
        function gaInvoiceForm() {
            return {
                memoOpen: true,
                selectedMemo: null,
                selectedCurrency: '',
                lines: [],
                selectedLines: [],

                memoModalOpen: false,
                memoEditIndex: -1,
                memoForm: { subject: '', content: '' },

                memos: [],

                saveAction: 'save_close',

                form: {
                    bill_to_id: '{{ old("bill_to_id", "") }}',
                    invoice_no: '{{ old("invoice_no", "INF-" . date("ymdHis")) }}',
                    invoice_date: '{{ old("invoice_date", date("Y-m-d")) }}',
                    due_date: '{{ old("due_date", date("Y-m-d", strtotime("+30 days"))) }}',
                    office_id: '{{ old("office_id", "") }}',
                    currency_id: '{{ old("currency_id", "") }}',
                    total_amount: '{{ old("total_amount", "0") }}',
                    tax_total: '{{ old("tax_total", "0") }}',
                    discount_pct: '{{ old("discount_pct", "0") }}',
                },

                get totalAmount() {
                    return this.lines.reduce((sum, line) => sum + (parseFloat(line.amount) || 0), 0);
                },
                get computedSubtotal() {
                    const total = parseFloat(this.form.total_amount) || 0;
                    const tax = parseFloat(this.form.tax_total) || 0;
                    return (total - tax).toFixed(2);
                },

                recalcSubtotal() {},

                formatAmount(val) {
                    return parseFloat(val || 0).toFixed(2);
                },

                calcLineAmount(idx) {
                    const line = this.lines[idx];
                    const baseAmount = (parseFloat(line.qty) || 0) * (parseFloat(line.rate) || 0);
                    const tax = (parseFloat(line.tax_pct) || 0) / 100;
                    line.amount = baseAmount * (1 + tax);
                },

                addLine() {
                    this.lines.push({ charge_code: '', description: '', unit: 'UNIT', currency: 'USD', qty: 1, rate: 0, tax_pct: 0, amount: 0, selected: false });
                },
                addLines(count) {
                    for (let i = 0; i < count; i++) this.addLine();
                },
                copySelectedLines() {
                    this.selectedLines.forEach(i => { this.lines.push({ ...this.lines[i], selected: false }); });
                    this.selectedLines = [];
                },
                deleteSelectedLines() {
                    if (!this.selectedLines.length) return;
                    this.selectedLines.sort((a, b) => b - a).forEach(i => this.lines.splice(i, 1));
                    this.selectedLines = [];
                },
                toggleLineSelection(idx, checked) {
                    if (checked) {
                        if (!this.selectedLines.includes(idx)) this.selectedLines.push(idx);
                    } else {
                        this.selectedLines = this.selectedLines.filter(i => i !== idx);
                    }
                },
                toggleAllLines(checked) { this.selectedLines = checked ? this.lines.map((_, i) => i) : []; },

                handleSubmit(action) {
                    this.saveAction = action;
                    this.$nextTick(() => {
                        this.saveViaAjax();
                    });
                },

                validateForm() {
                    let valid = true;
                    let missing = [];
                    if (!this.form.bill_to_id) { missing.push('Bill To'); valid = false; }
                    if (!this.form.invoice_no || !this.form.invoice_no.trim()) { missing.push('Invoice No.'); valid = false; }
                    if (!this.form.invoice_date) { missing.push('Post Date'); valid = false; }
                    if (!this.form.total_amount || parseFloat(this.form.total_amount) <= 0) { missing.push('Amount'); valid = false; }
                    if (!this.form.office_id) { missing.push('Office'); valid = false; }
                    if (!this.form.currency_id) { missing.push('Currency'); valid = false; }
                    if (!valid) { showToast('error', 'Please fill in required fields: ' + missing.join(', ') + '.'); }
                    return valid;
                },

                saveViaAjax() {
                    if (!this.validateForm()) return;

                    const form = document.getElementById('gaInvoiceForm');
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
                            showToast('success', data.message);
                            if (this.saveAction === 'save_new') {
                                setTimeout(() => window.location.href = '{{ route("accounting.invoices.create") }}', 800);
                            } else if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        } else {
                            showToast('error', data.message || 'Save failed.');
                        }
                    })
                    .catch(err => showToast('error', 'Network error: ' + (err.message || 'Unknown')));
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
