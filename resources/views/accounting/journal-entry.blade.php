<x-layout>
    @push('styles')
    <x-form-styles />
    <x-list-styles />
    <style>
        .je-table-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; margin-bottom: 10px; }
        .je-table { width: 100%; min-width: 1200px; border-collapse: collapse; font-size: 11px; }
        .je-table th { background: #f8fafc; color: #475569; font-weight: 700; border-bottom: 1px solid #cbd5e1; border-right: 1px solid #e2e8f0; padding: 6px; white-space: nowrap; height: 26px; text-align: left; position: sticky; top: 0; z-index: 5; }
        .je-table td { padding: 3px 4px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; white-space: nowrap; vertical-align: middle; color: #334155; }
        .je-table tr:hover td { background: #f1f5f9; }
        .je-table tr.row-selected td { background: #eff6ff; }
        .je-table input[type="text"], .je-table select { height: 22px; font-size: 10px; padding: 0 4px; border: 1px solid #cbd5e1; border-radius: 2px; width: 100%; box-sizing: border-box; background: #fff; }
        .je-table input[type="text"]:focus, .je-table select:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,0.15); }
        .je-table .num { font-family: 'Consolas', 'Courier New', monospace; text-align: right; }
        .je-table tfoot td { background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1; }

        .balance-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; }
        .balance-pill.balanced { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .balance-pill.unbalanced { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .badge-status-gf { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; display: inline-block; }
        .badge-status-gf.posted { background: #dcfce7; color: #15803d; }
        .badge-status-gf.draft { background: #fef3c7; color: #92400e; }

        @media (max-width: 768px) {
            .form-grid-4 { grid-template-columns: 1fr !important; }
            .action-header-toolbar { flex-direction: column; align-items: flex-start !important; gap: 10px; }
            .action-header-toolbar .btn-group-actions { width: 100%; display: flex; flex-wrap: wrap; gap: 6px; }
            .portlet-title { flex-direction: column; align-items: flex-start !important; gap: 6px; }
        }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="page-content" x-data="journalEntryApp()" x-init="init()">
        <!-- Breadcrumbs -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Accounting <i class="fa fa-angle-right"></i></li>
                <li>Journal <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Journal Entry</span></li>
            </ul>
        </div>

        <!-- Top Header & Action Buttons Toolbar -->
        <div class="action-header-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <h1 class="caption-subject" style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; text-transform: uppercase;">
                    <i class="fa fa-book" style="color:#3b82f6;"></i> General Journal Entry
                </h1>
                <span class="badge-status-gf posted" x-text="form.status"></span>
            </div>
            <div class="btn-group-actions" style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="btn-freightx" @click="saveEntry(false)">
                    <i class="fa fa-save"></i> <span x-text="form.id ? 'UPDATE JOURNAL' : 'SAVE JOURNAL'"></span>
                </button>
                <button type="button" class="btn-default-gf" @click="saveEntry(true)">
                    <i class="fa fa-plus-circle"></i> SAVE &amp; ANOTHER
                </button>
                <button type="button" class="btn-default-gf" @click="resetForm()">
                    <i class="fa fa-refresh"></i> NEW ENTRY
                </button>
                <a href="{{ route('accounting.general-journal') }}" class="btn-default-gf" style="text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                    <i class="fa fa-list"></i> GENERAL JOURNAL
                </a>
                <button type="button" class="btn-default-gf" @click="exportExcel()">
                    <i class="fa fa-file-excel-o"></i> EXCEL
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="gf-tabs">
            <li :class="activeTab === 'entry' ? 'active' : ''" @click="activeTab = 'entry'">
                <a><i class="fa fa-pencil-square-o"></i> Main Journal Form</a>
            </li>
            <li :class="activeTab === 'list' ? 'active' : ''" @click="switchToListTab()">
                <a><i class="fa fa-list"></i> Journal Entries History</a>
            </li>
        </ul>

        <!-- ==================== FORM TAB ==================== -->
        <div x-show="activeTab === 'entry'" class="main-grid">
            <!-- Header Metadata Section -->
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-info-circle" style="color:#3b82f6;"></i>
                        <span class="caption-subject" x-text="form.id ? 'Edit Entry: ' + form.entry_no : 'New Entry Details'"></span>
                    </div>
                    <div class="actions">
                        <span style="font-size:11px;font-weight:700;color:#64748b;" x-text="'Entry No: ' + form.entry_no"></span>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="form-grid-4">
                        <div class="form-group-gf">
                            <label class="form-label-gf"><span style="color:#ef4444;">*</span> Entry Date:</label>
                            <div class="form-input-container">
                                <input type="date" class="form-control-gf" x-model="form.entry_date">
                            </div>
                        </div>

                        <div class="form-group-gf">
                            <label class="form-label-gf">Entry No:</label>
                            <div class="form-input-container">
                                <input type="text" class="form-control-gf" x-model="form.entry_no" readonly>
                            </div>
                        </div>

                        <div class="form-group-gf">
                            <label class="form-label-gf">Office:</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="form.office_id">
                                    <option value="">-- Select Office --</option>
                                    <template x-for="o in offices" :key="o.id">
                                        <option :value="o.id" x-text="o.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="form-group-gf">
                            <label class="form-label-gf">Status:</label>
                            <div class="form-input-container">
                                <select class="form-control-gf" x-model="form.status">
                                    <option value="POSTED">POSTED</option>
                                    <option value="DRAFT">DRAFT</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid-4" style="margin-top: 6px;">
                        <div class="form-group-gf" style="grid-column: span 2;">
                            <label class="form-label-gf">Description:</label>
                            <div class="form-input-container">
                                <input type="text" class="form-control-gf" x-model="form.description" placeholder="Enter general journal entry description...">
                            </div>
                        </div>

                        <div class="form-group-gf" style="grid-column: span 2;">
                            <label class="form-label-gf">Remark:</label>
                            <div class="form-input-container">
                                <input type="text" class="form-control-gf" x-model="form.remark" placeholder="Internal remarks...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items Table Section -->
            <div class="portlet light">
                <div class="portlet-title" style="background:#f8fafc;">
                    <div class="caption">
                        <i class="fa fa-table" style="color:#3b82f6;"></i>
                        <span class="caption-subject">Journal Lines</span>
                    </div>
                    <div class="actions" style="display:flex;align-items:center;gap:10px;">
                        <span class="balance-pill" :class="isBalanced ? 'balanced' : 'unbalanced'">
                            <i class="fa" :class="isBalanced ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                            <span x-text="isBalanced ? 'Balanced' : 'Out of Balance (Diff: $' + formatMoney(Math.abs(totals.local_debit - totals.local_credit)) + ')'"></span>
                        </span>
                    </div>
                </div>

                <div class="container-toolbar" style="margin: 6px; padding: 6px 10px;">
                    <button type="button" class="btn-freightx" @click="addLine()">
                        <i class="fa fa-plus"></i> Add Line
                    </button>
                    <button type="button" class="btn-default-gf" @click="addBalancedPair()">
                        <i class="fa fa-plus-circle"></i> + Dr/Cr Pair
                    </button>
                    <button type="button" class="btn-default-gf" style="color:#ef4444;" @click="deleteSelectedLines()" :disabled="selectedLineIndices().length === 0">
                        <i class="fa fa-trash"></i> Delete Selected (<span x-text="selectedLineIndices().length"></span>)
                    </button>
                    <div style="margin-left:auto; font-size:11px; color:#64748b; font-weight:600;" x-text="lines.length + ' line' + (lines.length !== 1 ? 's' : '')"></div>
                </div>

                <div class="portlet-body">
                    <div class="je-table-wrapper">
                        <table class="je-table">
                            <thead>
                                <tr>
                                    <th style="width:30px;text-align:center;"><input type="checkbox" x-model="selectAll" @change="toggleSelectAll()"></th>
                                    <th style="width:36px;text-align:center;">No.</th>
                                    <th style="width:190px;">G/L Account <span class="text-danger">*</span></th>
                                    <th style="width:60px;">Sub</th>
                                    <th style="width:90px;">Type</th>
                                    <th style="width:140px;">Entity / Partner</th>
                                    <th style="width:160px;">Description</th>
                                    <th style="width:90px;">Office</th>
                                    <th style="width:100px;text-align:right;">Local Dr ($)</th>
                                    <th style="width:100px;text-align:right;">Local Cr ($)</th>
                                    <th style="width:75px;">Currency</th>
                                    <th style="width:70px;text-align:right;">Rate</th>
                                    <th style="width:100px;text-align:right;">Foreign Dr</th>
                                    <th style="width:100px;text-align:right;">Foreign Cr</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(line, idx) in lines" :key="idx">
                                    <tr :class="line.selected ? 'row-selected' : ''">
                                        <td style="text-align:center;"><input type="checkbox" x-model="line.selected"></td>
                                        <td style="text-align:center;font-weight:600;color:#64748b;" x-text="idx + 1"></td>
                                        <td>
                                            <select x-model="line.gl_account_id" class="form-control-gf">
                                                <option value="">-- Select G/L Account --</option>
                                                <template x-for="gl in glAccounts" :key="gl.id">
                                                    <option :value="gl.id" x-text="gl.code + ' - ' + gl.name"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control-gf" x-model="line.sub" maxlength="50"></td>
                                        <td>
                                            <select class="form-control-gf" x-model="line.entity_type">
                                                <option value="COMPANY">COMPANY</option>
                                                <option value="BANK">BANK</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control-gf" x-model="line.trade_partner_id">
                                                <option value="">-- Select Partner --</option>
                                                <template x-for="p in partners" :key="p.id">
                                                    <option :value="p.id" x-text="p.name"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control-gf" x-model="line.description" placeholder="Line detail..."></td>
                                        <td>
                                            <select class="form-control-gf" x-model="line.office_id">
                                                <option value="">-- Office --</option>
                                                <template x-for="o in offices" :key="o.id">
                                                    <option :value="o.id" x-text="o.code || o.name"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control-gf num" x-model="line.local_debit" @input="recalcLine(line, 'ld')"></td>
                                        <td><input type="text" class="form-control-gf num" x-model="line.local_credit" @input="recalcLine(line, 'lc')"></td>
                                        <td>
                                            <select class="form-control-gf" x-model="line.currency_id">
                                                <option value="">-- Cur --</option>
                                                <template x-for="c in currencies" :key="c.id">
                                                    <option :value="c.id" x-text="c.code"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control-gf num" x-model="line.foreign_rate" @input="recalcLine(line, 'rate')"></td>
                                        <td><input type="text" class="form-control-gf num" x-model="line.foreign_debit" @input="recalcLine(line, 'fd')"></td>
                                        <td><input type="text" class="form-control-gf num" x-model="line.foreign_credit" @input="recalcLine(line, 'fc')"></td>
                                    </tr>
                                </template>
                                <template x-if="lines.length === 0">
                                    <tr>
                                        <td colspan="14" class="empty-msg">
                                            No line items added yet. Click <strong><i class="fa fa-plus"></i> Add Line</strong> or <strong><i class="fa fa-plus-circle"></i> + Dr/Cr Pair</strong> to start.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8" style="text-align:right;font-weight:700;padding-right:10px;color:#3b82f6;">TOTAL:</td>
                                    <td class="num" style="color:#2563eb;font-weight:700;" x-text="formatMoney(totals.local_debit)"></td>
                                    <td class="num" style="color:#2563eb;font-weight:700;" x-text="formatMoney(totals.local_credit)"></td>
                                    <td colspan="2"></td>
                                    <td class="num" style="font-weight:700;" x-text="formatMoney(totals.foreign_debit)"></td>
                                    <td class="num" style="font-weight:700;" x-text="formatMoney(totals.foreign_credit)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer Save Actions Bar -->
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px; margin-bottom:30px;">
                <button type="button" class="btn-default-gf" @click="resetForm()"><i class="fa fa-undo"></i> RESET</button>
                <button type="button" class="btn-default-gf" @click="saveEntry(true)"><i class="fa fa-plus-circle"></i> SAVE &amp; ANOTHER</button>
                <button type="button" class="btn-freightx" @click="saveEntry(false)"><i class="fa fa-save"></i> <span x-text="form.id ? 'UPDATE ENTRY' : 'SAVE ENTRY'"></span></button>
            </div>
        </div>

        <!-- ==================== HISTORY / LIST TAB ==================== -->
        <div x-show="activeTab === 'list'">
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-history" style="color:#3b82f6;"></i>
                        <span class="caption-subject">Journal Entries History</span>
                    </div>
                    <div class="actions" style="display:flex;gap:6px;">
                        <button class="btn-default-gf" @click="exportExcel()"><i class="fa fa-file-excel-o"></i> Excel Export</button>
                        <button class="btn-freightx" @click="activeTab = 'entry'; resetForm();"><i class="fa fa-plus"></i> New Entry</button>
                    </div>
                </div>

                <!-- Filters -->
                <div style="padding:10px 14px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <div class="form-group-gf" style="width:200px;">
                        <label class="form-label-gf" style="width:60px;">Search:</label>
                        <div class="form-input-container">
                            <input type="text" class="form-control-gf" x-model="listFilters.search" @input.debounce.300ms="loadList()" placeholder="Entry No, Description...">
                        </div>
                    </div>

                    <div class="form-group-gf" style="width:180px;">
                        <label class="form-label-gf" style="width:70px;">From Date:</label>
                        <div class="form-input-container">
                            <input type="date" class="form-control-gf" x-model="listFilters.from_date" @change="loadList()">
                        </div>
                    </div>

                    <div class="form-group-gf" style="width:180px;">
                        <label class="form-label-gf" style="width:60px;">To Date:</label>
                        <div class="form-input-container">
                            <input type="date" class="form-control-gf" x-model="listFilters.to_date" @change="loadList()">
                        </div>
                    </div>

                    <div class="form-group-gf" style="width:160px;">
                        <label class="form-label-gf" style="width:50px;">Status:</label>
                        <div class="form-input-container">
                            <select class="form-control-gf" x-model="listFilters.status" @change="loadList()">
                                <option value="">All Statuses</option>
                                <option value="POSTED">POSTED</option>
                                <option value="DRAFT">DRAFT</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn-default-gf" @click="clearListFilters()"><i class="fa fa-eraser"></i> Clear</button>
                </div>

                <div class="portlet-body">
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width:110px;">Entry No</th>
                                    <th style="width:90px;">Date</th>
                                    <th>Description / Remark</th>
                                    <th style="width:100px;">Office</th>
                                    <th style="width:100px;text-align:right;">Total Debit ($)</th>
                                    <th style="width:100px;text-align:right;">Total Credit ($)</th>
                                    <th style="width:60px;text-align:center;">Lines</th>
                                    <th style="width:80px;text-align:center;">Status</th>
                                    <th style="width:120px;text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in listData" :key="item.id">
                                    <tr>
                                        <td><strong style="color:#2563eb;" x-text="item.entry_no"></strong></td>
                                        <td x-text="item.entry_date"></td>
                                        <td>
                                            <div style="font-weight:600;color:#1e293b;" x-text="item.description || 'No description'"></div>
                                            <div style="font-size:10px;color:#64748b;" x-text="item.remark" x-show="item.remark"></div>
                                        </td>
                                        <td x-text="item.office_name"></td>
                                        <td style="text-align:right;font-weight:600;color:#2563eb;" x-text="formatMoney(item.total_debit)"></td>
                                        <td style="text-align:right;font-weight:600;color:#2563eb;" x-text="formatMoney(item.total_credit)"></td>
                                        <td style="text-align:center;" x-text="item.lines_count"></td>
                                        <td style="text-align:center;">
                                            <span class="badge-status-gf posted" x-text="item.status"></span>
                                        </td>
                                        <td style="text-align:center;">
                                            <div style="display:flex;gap:4px;justify-content:center;">
                                                <button class="btn-default-gf" style="padding:2px 6px;font-size:10px;" @click="editEntry(item.id)" title="Edit Entry"><i class="fa fa-pencil"></i> Edit</button>
                                                <button class="btn-default-gf" style="padding:2px 6px;font-size:10px;color:#ef4444;" @click="deleteEntry(item.id, item.entry_no)" title="Delete Entry"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="listData.length === 0">
                                    <tr>
                                        <td colspan="9" class="empty-msg">No journal entries found matching criteria.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div style="padding:10px 0 0 0; display:flex; justify-content:between; align-items:center;" x-show="pagination.total > 0">
                        <span style="font-size:11px;color:#64748b;" x-text="'Showing ' + pagination.from + ' to ' + pagination.to + ' of ' + pagination.total + ' entries'"></span>
                        <div style="display:flex;gap:4px;">
                            <button class="btn-default-gf" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">Previous</button>
                            <span style="font-size:11px;font-weight:600;padding:4px 8px;" x-text="pagination.current_page + ' / ' + pagination.last_page"></span>
                            <button class="btn-default-gf" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function journalEntryApp() {
        return {
            activeTab: 'entry',
            offices: @json($offices),
            currencies: @json($currencies),
            partners: @json($partners),
            glAccounts: @json($glAccounts),
            nextEntryNo: @json($nextEntryNo),
            
            form: {
                id: null,
                entry_no: @json($nextEntryNo),
                entry_date: new Date().toISOString().split('T')[0],
                office_id: @json(count($offices) ? $offices[0]['id'] : null),
                description: '',
                remark: '',
                status: 'POSTED'
            },

            lines: [],
            selectAll: false,

            // List State
            listData: [],
            listFilters: {
                search: '',
                from_date: '',
                to_date: '',
                status: ''
            },
            pagination: {
                current_page: 1,
                last_page: 1,
                from: 0,
                to: 0,
                total: 0
            },

            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const editId = urlParams.get('id') || urlParams.get('entry_id');
                if (editId) {
                    this.editEntry(editId);
                } else if (this.lines.length === 0) {
                    this.addBalancedPair();
                }
            },

            formatMoney(val) {
                const num = parseFloat(val) || 0;
                return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            get totals() {
                let ld = 0, lc = 0, fd = 0, fc = 0;
                this.lines.forEach(l => {
                    ld += parseFloat(l.local_debit) || 0;
                    lc += parseFloat(l.local_credit) || 0;
                    fd += parseFloat(l.foreign_debit) || 0;
                    fc += parseFloat(l.foreign_credit) || 0;
                });
                return { local_debit: ld, local_credit: lc, foreign_debit: fd, foreign_credit: fc };
            },

            get isBalanced() {
                return Math.abs(this.totals.local_debit - this.totals.local_credit) < 0.01 && this.lines.length > 0;
            },

            selectedLineIndices() {
                return this.lines.filter(l => l.selected);
            },

            toggleSelectAll() {
                this.lines.forEach(l => l.selected = this.selectAll);
            },

            addLine(defaults = {}) {
                const defaultOffice = this.form.office_id || (this.offices.length ? this.offices[0].id : '');
                const defaultCurrency = this.currencies.length ? this.currencies[0].id : '';

                this.lines.push({
                    selected: false,
                    gl_account_id: defaults.gl_account_id || '',
                    sub: defaults.sub || '',
                    entity_type: defaults.entity_type || 'COMPANY',
                    trade_partner_id: defaults.trade_partner_id || '',
                    description: defaults.description || '',
                    office_id: defaults.office_id || defaultOffice,
                    local_debit: defaults.local_debit !== undefined ? defaults.local_debit : '0.00',
                    local_credit: defaults.local_credit !== undefined ? defaults.local_credit : '0.00',
                    currency_id: defaults.currency_id || defaultCurrency,
                    foreign_rate: defaults.foreign_rate !== undefined ? defaults.foreign_rate : '1.000000',
                    foreign_debit: defaults.foreign_debit !== undefined ? defaults.foreign_debit : '0.00',
                    foreign_credit: defaults.foreign_credit !== undefined ? defaults.foreign_credit : '0.00'
                });
            },

            addBalancedPair() {
                this.addLine({ local_debit: '0.00', local_credit: '0.00' });
                this.addLine({ local_debit: '0.00', local_credit: '0.00' });
            },

            deleteSelectedLines() {
                if (this.selectedLineIndices().length === 0) return;
                this.lines = this.lines.filter(l => !l.selected);
                this.selectAll = false;
                if (typeof showToast === 'function') {
                    showToast('info', 'Deleted selected line(s)');
                }
            },

            recalcLine(line, field) {
                const rate = parseFloat(line.foreign_rate) || 1;
                
                if (field === 'ld') {
                    const ld = parseFloat(line.local_debit) || 0;
                    if (ld > 0) line.local_credit = '0.00';
                    line.foreign_debit = (ld * rate).toFixed(2);
                } else if (field === 'lc') {
                    const lc = parseFloat(line.local_credit) || 0;
                    if (lc > 0) line.local_debit = '0.00';
                    line.foreign_credit = (lc * rate).toFixed(2);
                } else if (field === 'rate') {
                    const ld = parseFloat(line.local_debit) || 0;
                    const lc = parseFloat(line.local_credit) || 0;
                    line.foreign_debit = (ld * rate).toFixed(2);
                    line.foreign_credit = (lc * rate).toFixed(2);
                } else if (field === 'fd') {
                    const fd = parseFloat(line.foreign_debit) || 0;
                    if (fd > 0) line.foreign_credit = '0.00';
                    line.local_debit = rate > 0 ? (fd / rate).toFixed(2) : fd.toFixed(2);
                } else if (field === 'fc') {
                    const fc = parseFloat(line.foreign_credit) || 0;
                    if (fc > 0) line.foreign_debit = '0.00';
                    line.local_credit = rate > 0 ? (fc / rate).toFixed(2) : fc.toFixed(2);
                }
            },

            async fetchNextEntryNo() {
                try {
                    const res = await fetch('/api/next-entry-no', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        return data.entry_no;
                    }
                } catch (e) {
                    console.error('Failed to fetch next entry no:', e);
                }
                return 'JE-' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '-0001';
            },

            async resetForm() {
                const nextNo = await this.fetchNextEntryNo();
                this.form = {
                    id: null,
                    entry_no: nextNo,
                    entry_date: new Date().toISOString().split('T')[0],
                    office_id: this.offices.length ? this.offices[0].id : null,
                    description: '',
                    remark: '',
                    status: 'POSTED'
                };
                this.lines = [];
                this.addBalancedPair();
                this.selectAll = false;
            },

            async saveEntry(createAnother = false) {
                if (!this.lines.length) {
                    if (typeof showToast === 'function') showToast('error', 'Please add at least one line.');
                    return;
                }

                let missingIndex = -1;
                this.lines.forEach((l, i) => {
                    if (!l.gl_account_id) missingIndex = i;
                });
                if (missingIndex !== -1) {
                    if (typeof showToast === 'function') showToast('error', 'Please select a G/L account for line ' + (missingIndex + 1));
                    return;
                }

                if (!this.isBalanced) {
                    if (typeof showToast === 'function') showToast('error', 'Total debit must equal total credit.');
                    return;
                }

                const payload = {
                    entry_id: this.form.id,
                    entry_date: this.form.entry_date,
                    description: this.form.description,
                    remark: this.form.remark,
                    office_id: this.form.office_id,
                    lines: this.lines.map(l => ({
                        gl_account_id: l.gl_account_id,
                        sub: l.sub,
                        entity_type: l.entity_type,
                        trade_partner_id: l.trade_partner_id,
                        description: l.description,
                        office_id: l.office_id,
                        local_debit: parseFloat(l.local_debit) || 0,
                        local_credit: parseFloat(l.local_credit) || 0,
                        currency_id: l.currency_id,
                        foreign_rate: parseFloat(l.foreign_rate) || 1,
                        foreign_debit: parseFloat(l.foreign_debit) || 0,
                        foreign_credit: parseFloat(l.foreign_credit) || 0,
                    }))
                };

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const response = await fetch('{{ route("accounting.journal.entry.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        if (typeof showToast === 'function') {
                            showToast('success', data.message || 'Journal Entry saved successfully');
                        }

                        if (createAnother) {
                            await this.resetForm();
                        } else {
                            this.form.id = data.entry_id;
                            this.form.entry_no = data.entry_no;
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('error', data.message || 'Failed to save journal entry');
                        }
                    }
                } catch (e) {
                    console.error('Save failed:', e);
                    if (typeof showToast === 'function') showToast('error', 'Network or server error while saving');
                }
            },

            // ===== LIST TAB METHODS =====
            switchToListTab() {
                this.activeTab = 'list';
                this.loadList();
            },

            clearListFilters() {
                this.listFilters = { search: '', from_date: '', to_date: '', status: '' };
                this.loadList(1);
            },

            async loadList(page = 1) {
                try {
                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (this.listFilters.search) params.append('search', this.listFilters.search);
                    if (this.listFilters.from_date) params.append('from_date', this.listFilters.from_date);
                    if (this.listFilters.to_date) params.append('to_date', this.listFilters.to_date);
                    if (this.listFilters.status) params.append('status', this.listFilters.status);

                    const response = await fetch(`/accounting/journal/list?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (response.ok) {
                        const res = await response.json();
                        this.listData = res.data || [];
                        this.pagination = {
                            current_page: res.current_page || 1,
                            last_page: res.last_page || 1,
                            from: res.from || 0,
                            to: res.to || 0,
                            total: res.total || 0
                        };
                    }
                } catch (e) {
                    console.error('Failed to load list:', e);
                }
            },

            changePage(page) {
                if (page >= 1 && page <= this.pagination.last_page) {
                    this.loadList(page);
                }
            },

            async editEntry(id) {
                try {
                    const response = await fetch(`/accounting/journal/${id}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (response.ok) {
                        const entry = await response.json();
                        this.form = {
                            id: entry.id,
                            entry_no: entry.entry_no,
                            entry_date: entry.entry_date ? entry.entry_date.substr(0,10) : '',
                            office_id: entry.office_id || '',
                            description: entry.description || '',
                            remark: entry.remark || '',
                            status: entry.status || 'POSTED'
                        };

                        this.lines = (entry.lines || []).map(l => ({
                            selected: false,
                            gl_account_id: l.gl_account_id || '',
                            sub: l.sub || '',
                            entity_type: l.entity_type || 'COMPANY',
                            trade_partner_id: l.trade_partner_id || '',
                            description: l.description || '',
                            office_id: l.office_id || '',
                            local_debit: parseFloat(l.local_debit || 0).toFixed(2),
                            local_credit: parseFloat(l.local_credit || 0).toFixed(2),
                            currency_id: l.currency_id || '',
                            foreign_rate: parseFloat(l.foreign_rate || 1).toFixed(6),
                            foreign_debit: parseFloat(l.foreign_debit || 0).toFixed(2),
                            foreign_credit: parseFloat(l.foreign_credit || 0).toFixed(2)
                        }));

                        this.activeTab = 'entry';
                        if (typeof showToast === 'function') {
                            showToast('info', 'Loaded Entry ' + entry.entry_no + ' for editing');
                        }
                    }
                } catch (e) {
                    console.error('Failed to fetch entry details:', e);
                }
            },

            async deleteEntry(id, entryNo) {
                if (!confirm(`Are you sure you want to delete Journal Entry ${entryNo}?`)) return;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const response = await fetch(`/accounting/journal/entry/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json'
                        }
                    });

                    const res = await response.json();
                    if (response.ok && res.success) {
                        if (typeof showToast === 'function') showToast('success', 'Journal Entry deleted successfully');
                        this.loadList(this.pagination.current_page);
                    } else {
                        if (typeof showToast === 'function') showToast('error', res.message || 'Failed to delete entry');
                    }
                } catch (e) {
                    console.error('Delete failed:', e);
                }
            },

            exportExcel() {
                const params = new URLSearchParams();
                if (this.listFilters.search) params.append('search', this.listFilters.search);
                if (this.listFilters.from_date) params.append('from_date', this.listFilters.from_date);
                if (this.listFilters.to_date) params.append('to_date', this.listFilters.to_date);
                if (this.listFilters.status) params.append('status', this.listFilters.status);

                if (typeof showToast === 'function') showToast('info', 'Preparing Excel export...');
                window.location.href = `/accounting/journal/export-excel?${params.toString()}`;
            }
        };
    }
    </script>
</x-layout>
