@php use Illuminate\Support\Str; @endphp
<x-layout>
    @push('styles')
    <x-list-styles />
    @endpush

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- PAYMENT PORTAL MODAL --}}
    <div class="overlay" id="payment-portal-overlay" onclick="if(event.target===this) closePaymentPortal()">
        <div class="confirm-box" style="max-width:800px;width:95%;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h4 style="margin:0;font-size:13px;color:#1e293b;"><i class="fa fa-credit-card" style="color:#3b82f6;"></i> Payment Portal — Outstanding Invoices</h4>
                <button class="btn-tool" style="padding:0 10px;height:22px;" onclick="closePaymentPortal()"><i class="fa fa-times"></i></button>
            </div>
            <div style="max-height:400px;overflow-y:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:10px;">
                    <thead>
                        <tr style="background:#f1f5f9;border-bottom:2px solid #e2e8f0;">
                            <th style="padding:6px 8px;text-align:center;width:30px;"><input type="checkbox" onchange="portalSelectAll(this)"></th>
                            <th style="padding:6px 8px;text-align:left;">Invoice No.</th>
                            <th style="padding:6px 8px;text-align:left;">Party</th>
                            <th style="padding:6px 8px;text-align:right;">Amount</th>
                            <th style="padding:6px 8px;text-align:right;">Balance</th>
                            <th style="padding:6px 8px;text-align:center;">Status</th>
                            <th style="padding:6px 8px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="portal-invoices-body">
                        <tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;padding-top:10px;border-top:1px solid #e2e8f0;">
                <button class="btn-tool green" onclick="portalBulkPay()" style="padding:0 14px;height:26px;"><i class="fa fa-credit-card"></i> Pay Selected</button>
                <button class="btn-tool" onclick="closePaymentPortal()" style="padding:0 14px;height:26px;">Close</button>
            </div>
        </div>
    </div>

    {{-- BATCH UPDATE MODAL --}}
    <div class="overlay" id="batch-update-overlay" onclick="if(event.target===this) closeBatchUpdate()">
        <div class="confirm-box" style="max-width:420px;">
            <div class="confirm-icon"><i class="fa fa-pencil-square-o"></i></div>
            <h4>Batch Update Invoices</h4>
            <p>Update status for <strong id="batch-update-count">0</strong> selected invoice(s).</p>
            <div style="margin:12px 0;">
                <label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px;">New Status</label>
                <select id="batch-new-status" style="width:100%;padding:6px 10px;border:1px solid #e2e8f0;border-radius:4px;font-size:11px;">
                    <option value="">— Select Status —</option>
                    <option value="DRAFT">Draft</option>
                    <option value="POSTED">Posted</option>
                    <option value="PAID">Paid</option>
                    <option value="VOID">Void</option>
                </select>
            </div>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeBatchUpdate()">Cancel</button>
                <button class="btn-tool green" style="padding:0 18px;height:26px;" onclick="executeBatchUpdate()">
                    <i class="fa fa-check"></i> Apply
                </button>
            </div>
        </div>
    </div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Invoice(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- COLOR PICKER MODAL --}}
    <div class="overlay color-picker-overlay" id="color-picker-overlay" onclick="if(event.target===this) closeColorPicker()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-paint-brush" style="color:#3b82f6;"></i> Status Color</div>
                <button class="modal-close" onclick="closeColorPicker()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <div class="color-picker-grid" id="color-picker-grid"></div>
                <div class="color-clear-btn" onclick="clearColor()">Clear / No Color</div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">{{ $docType ?? 'INV/DN/CN' }} List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            {{-- PORTLET TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">{{ $docType ?? 'INV/DN/CN' }} LIST</span>
                    <span class="summary-stats">
                        Invoice: <span class="val" id="stat-inv-total">{{ number_format($totalInvoiceAmount, 2) }}</span>
                        Paid: <span class="val paid" id="stat-paid-total">{{ number_format($totalPaidAmount, 2) }}</span>
                        Balance: <span class="val" id="stat-bal-total">{{ number_format($totalBalanceAmount, 2) }}</span>
                    </span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" id="btn-filter" onclick="toggleFilter()" title="Toggle filter row">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <div style="position:relative;display:inline-flex;align-items:center;">
                        <button class="btn-action-round" id="btn-config" onclick="toggleConfig()" title="Column visibility">
                            <i class="fa fa-cogs"></i> Config
                        </button>
                        <div class="config-panel" id="config-panel" style="display:none;">
                            <div class="config-panel-title">Column Visibility</div>
                            <div id="col-toggles"></div>
                        </div>
                    </div>
                    <a class="btn-action-round white" href="{{ route('accounting.invoices.export-csv') }}" title="Download as CSV/Excel" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Excel <i class="fa fa-angle-down"></i>
                    </a>
                    <a href="{{ route('accounting.invoices.create') }}" class="btn-action-round green-btn">
                        <i class="fa fa-plus"></i> New Invoice
                    </a>
                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('accounting.invoices.create') }}" title="New Invoice">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-copy"   disabled title="Copy Selected (select 1 row)" onclick="copySelected()">
                            <i class="fa fa-files-o"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool green" onclick="makePayment()" title="Make or Receive Payment">
                            <i class="fa fa-credit-card"></i> Make / Receive Payment
                        </button>
                        <button class="btn-tool green" onclick="openPaymentPortal()" title="Payment Portal">
                            Payment Portal <span style="background:#f3c200;padding:0 3px;border-radius:2px;color:white;font-size:8px;margin-left:2px;">New</span>
                        </button>
                        <button class="btn-tool" onclick="batchUpdateInvoices()" title="Batch update Invoices">
                            Batch update Invoices
                        </button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                    <select class="input-inline" id="status-filter" onchange="applyStatusFilter(this.value)" style="width:90px;">
                        <option value="">All Status</option>
                        <option value="DRAFT">Draft</option>
                        <option value="POSTED">Posted</option>
                        <option value="PAID">Paid</option>
                        <option value="PARTIAL">Partial</option>
                        <option value="VOID">Void</option>
                    </select>
                    <select class="input-inline" id="page-size" onchange="applyPageSize(this.value)" style="width:60px;">
                        <option value="15" {{ request('limit', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>

            {{-- BULK FORM + TABLE --}}
            <form id="bulk-form" method="POST" action="{{ route('accounting.invoices.bulk-delete') }}" style="margin:0;">
                @csrf
                @method('DELETE')
                <div class="portlet-body">
                    <div class="grid-container">
                        <div class="grid-wrapper">
                            <table class="grid-table" id="main-grid">
                                <thead>
                                    {{-- HEADER ROW --}}
                                    <tr id="header-row">
                                        <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;">
                                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                        </th>
                                        <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                        <th class="sticky-col sticky-col-header" data-col="party" style="width:130px;left:50px;">Party</th>
                                        <th class="sticky-col sticky-col-header" data-col="file_no" style="width:90px;left:180px;">File No.</th>
                                        <th class="sticky-col sticky-col-header" data-col="inv_no" style="width:100px;left:270px;">Invoice No.</th>
                                        <th data-col="type" style="width:45px;">Type</th>
                                        <th data-col="post_date" style="width:85px;cursor:pointer;" onclick="doSort('invoice_date')">Post Date <i class="fa fa-sort" id="sort-invoice_date"></i></th>
                                        <th data-col="pub_status" style="width:90px;text-align:center;">Publication Status</th>
                                        <th data-col="inv_date" style="width:80px;cursor:pointer;" onclick="doSort('invoice_date')">Invoice Date <i class="fa fa-sort" id="sort-invoice_date2"></i></th>
                                        <th data-col="due_date" style="width:80px;cursor:pointer;" onclick="doSort('due_date')">Due Date <i class="fa fa-sort" id="sort-due_date"></i></th>
                                        <th data-col="amount" style="width:110px;cursor:pointer;" onclick="doSort('total_amount')">Amount <i class="fa fa-sort" id="sort-total_amount"></i></th>
                                        <th data-col="amount_ac" style="width:90px;text-align:right;">Amount (A.C.)</th>
                                        <th data-col="paid" style="width:110px;cursor:pointer;" onclick="doSort('paid_amount')">Paid Amount <i class="fa fa-sort" id="sort-paid_amount"></i></th>
                                        <th data-col="paid_ac" style="width:90px;text-align:right;">Paid (A.C.)</th>
                                        <th data-col="balance" style="width:110px;cursor:pointer;" onclick="doSort('balance_amount')">Balance <i class="fa fa-sort" id="sort-balance_amount"></i></th>
                                        <th data-col="balance_ac" style="width:90px;text-align:right;">Balance (A.C.)</th>
                                        <th data-col="last_paid" style="width:85px;">Last Paid Date</th>
                                        <th data-col="overdue" style="width:55px;text-align:center;">Over Due</th>
                                        <th data-col="office" style="width:55px;">Office</th>
                                        <th data-col="issuer" style="width:80px;">Issued by</th>
                                        <th data-col="issue_date" style="width:80px;">Issue Date</th>
                                        <th data-col="modified_by" style="width:90px;">Last Modified</th>
                                        <th data-col="mod_date" style="width:75px;">Mod. Date</th>
                                        <th data-col="mbl_no" style="width:110px;">MBL No.</th>
                                        <th data-col="hbl_no" style="width:110px;">HBL No.</th>
                                        <th data-col="status" style="width:65px;text-align:center;cursor:pointer;" onclick="doSort('status')">Status <i class="fa fa-sort" id="sort-status"></i></th>
                                        <th data-col="op" style="width:50px;">OP</th>
                                        <th data-col="sent_date" style="width:90px;">Sent Date</th>
                                    </tr>

                                    {{-- FILTER ROW (hidden by default) --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td class="sticky-col" style="left:0;"></td>
                                        <td class="sticky-col" style="left:25px;"></td>
                                        <td class="sticky-col" style="left:50px;"><input class="filter-input" data-col-idx="2" placeholder="Party..." oninput="filterOnInput()"></td>
                                        <td class="sticky-col" style="left:180px;"><input class="filter-input" data-col-idx="3" placeholder="File No..." oninput="filterOnInput()"></td>
                                        <td class="sticky-col" style="left:270px;"><input class="filter-input" data-col-idx="4" placeholder="Invoice No..." oninput="filterOnInput()"></td>
                                        <td class="sticky-col" style="left:370px;"></td>
                                        <td><input class="filter-input" data-col-idx="6" placeholder="Type..." oninput="filterOnInput()"></td>
                                        <td colspan="20"></td>
                                        <td style="text-align:center;"><button class="btn-tool green" onclick="filterOnInput()" style="height:18px;">Filter</button></td>
                                    </tr>
                                </thead>
                                <tbody id="grid-body">
                                @forelse($invoices as $invoice)
                                    <tr id="inv-row-{{ $invoice->id }}"
                                        data-id="{{ $invoice->id }}"
                                        data-inv-no="{{ $invoice->invoice_no }}"
                                        onclick="rowClick(event, this)"
                                    >
                                        {{-- Checkbox --}}
                                        <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="ids[]" value="{{ $invoice->id }}" class="row-check" onchange="updateToolbar()">
                                        </td>
                                        {{-- Lock --}}
                                        <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
                                            <i class="fa {{ $invoice->status === 'POSTED' || $invoice->status === 'PAID' ? 'fa-lock' : 'fa-unlock' }}" style="color:#94a3b8;cursor:pointer;font-size:10px;" title="Lock status" onclick="toggleLock(this)"></i>
                                        </td>
                                        {{-- Party --}}
                                        <td class="sticky-col" style="width:130px;left:50px;">{{ $invoice->billTo ? Str::limit($invoice->billTo->name, 18) : '' }}</td>
                                        {{-- File No. --}}
                                        <td class="sticky-col" style="width:90px;left:180px;">
                                            @if($invoice->invoiceable)
                                                <a href="#" class="col-link">{{ Str::limit(method_exists($invoice->invoiceable, 'getFileNoAttribute') ? $invoice->invoiceable->file_no : ($invoice->invoiceable->file_no ?? '--'), 12) }}</a>
                                            @endif
                                        </td>
                                        {{-- Invoice No. --}}
                                        <td class="sticky-col" style="width:100px;left:270px;">
                                            <a href="{{ route('accounting.invoices.show', $invoice) }}" class="col-link">{{ $invoice->invoice_no }}</a>
                                        </td>
                                        {{-- Scrollable columns --}}
                                        <td><span style="font-weight:600;color:{{ $invoice->type === 'AP' ? '#e08283' : '#3b82f6' }};">{{ $invoice->type ?? 'AR' }}</span></td>
                                        <td>{{ $invoice->created_at ? $invoice->created_at->format('m-d-Y') : '' }}</td>
                                        <td style="text-align:center;">
                                            @php
                                                $statusBadge = match($invoice->status) {
                                                    'DRAFT' => 'badge-draft',
                                                    'POSTED' => 'badge-posted',
                                                    'PAID' => 'badge-paid',
                                                    'PARTIAL' => 'badge-partial',
                                                    'VOID' => 'badge-void',
                                                    default => 'badge-draft',
                                                };
                                            @endphp
                                            <span class="badge-status {{ $statusBadge }}">{{ $invoice->status }}</span>
                                        </td>
                                        <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('m-d-Y') : '' }}</td>
                                        <td>{{ $invoice->due_date ? $invoice->due_date->format('m-d-Y') : '' }}</td>
                                        <td>
                                            <div class="currency-block">
                                                <span><span class="cur">{{ $invoice->currency ? $invoice->currency->code : 'USD' }}</span> {{ number_format($invoice->total_amount, 2) }}</span>
                                            </div>
                                        </td>
                                        <td style="text-align:right;vertical-align:top;">{{ number_format($invoice->total_amount, 2) }}</td>
                                        <td>
                                            <div class="currency-block">
                                                <span><span class="cur">{{ $invoice->currency ? $invoice->currency->code : 'USD' }}</span> {{ number_format($invoice->paid_amount, 2) }}</span>
                                            </div>
                                        </td>
                                        <td style="text-align:right;vertical-align:top;">{{ number_format($invoice->paid_amount, 2) }}</td>
                                        <td>
                                            <div class="currency-block">
                                                <span><span class="cur">{{ $invoice->currency ? $invoice->currency->code : 'USD' }}</span> {{ number_format($invoice->balance_amount, 2) }}</span>
                                            </div>
                                        </td>
                                        <td style="text-align:right;vertical-align:top;">{{ number_format($invoice->balance_amount, 2) }}</td>
                                        <td></td>
                                        <td style="text-align:center;">{{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'PAID' ? $invoice->due_date->diffInDays(now()) : '0' }}</td>
                                        <td>{{ $invoice->office ? $invoice->office->code : '--' }}</td>
                                        <td>{{ $invoice->issuer ? $invoice->issuer->name : '--' }}</td>
                                        <td>{{ $invoice->created_at ? $invoice->created_at->format('m-d-Y') : '' }}</td>
                                        <td>--</td>
                                        <td>{{ $invoice->updated_at ? $invoice->updated_at->format('m-d-Y') : '' }}</td>
                                        <td>{{ optional($invoice->invoiceable)->mbl_no ?? optional($invoice->invoiceable)->mawb_no ?? '--' }}</td>
                                        <td>{{ optional($invoice->invoiceable)->hbl_no ?? optional($invoice->invoiceable)->hawb_no ?? '--' }}</td>
                                        <td style="text-align:center;"><span class="badge-status {{ $statusBadge }}">{{ $invoice->status }}</span></td>
                                        <td>--</td>
                                        <td></td>
                                    </tr>
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="30" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No invoices found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            {{-- PAGINATION --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $invoices->links() }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $invoices->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $invoices->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $invoices->total() }}</span> records
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    /* ================================================================
       TOOLBAR — checkbox management
    ================================================================ */
    function updateToolbar() {
        const checked  = [...document.querySelectorAll('.row-check:checked')];
        const all      = [...document.querySelectorAll('.row-check')];
        const n        = checked.length;
        const sa       = document.getElementById('select-all');
        sa.checked        = n === all.length && all.length > 0;
        sa.indeterminate  = n > 0 && n < all.length;

        document.getElementById('btn-delete').disabled  = n === 0;
        document.getElementById('btn-copy').disabled    = n !== 1;

        const badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent   = n + ' selected';

        document.querySelectorAll('#grid-body tr[data-id]').forEach(row => {
            const cb = row.querySelector('.row-check');
            row.classList.toggle('row-selected', cb && cb.checked);
        });
    }

    function toggleSelectAll(el) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = el.checked);
        updateToolbar();
    }

    /* ================================================================
       ROW CLICK
    ================================================================ */
    function rowClick(e, row) {
        const skip = ['A', 'INPUT', 'BUTTON', 'I', 'SPAN'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    /* ================================================================
       DELETE
    ================================================================ */
    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent =
            `You are about to permanently delete ${n} invoice(s). This cannot be undone.`;
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
    }
    function executeDelete() {
        closeConfirm();
        const ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Deleting...');
        fetch('{{ route("accounting.invoices.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showToast('success', data.message || 'Deleted successfully');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showToast('error', data.message || 'Failed to delete');
            }
        }).catch(() => showToast('error', 'Failed to delete'));
    }

    /* ================================================================
       COPY — navigate to duplicate route
    ================================================================ */
    function copySelected() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        const id = checked[0].value;
        showToast('info', 'Copying invoice...');
        setTimeout(() => {
            window.location.href = '{{ route("accounting.invoices.duplicate", "ID") }}'.replace('ID', id);
        }, 400);
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    function getSelectedInvoiceData() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => {
            const row = cb.closest('tr[data-id]');
            return {
                id: cb.value,
                inv_no: row?.dataset.invNo || '',
            };
        });
    }

    /* ================================================================
       LOCK TOGGLE (visual only)
    ================================================================ */
    function toggleLock(el) {
        const locked = el.classList.contains('fa-lock');
        el.classList.toggle('fa-lock', !locked);
        el.classList.toggle('fa-unlock', locked);
        el.title = locked ? 'Lock' : 'Unlock';
        showToast('info', locked ? 'Row unlocked.' : 'Row locked.');
    }

    /* ================================================================
       SEARCH & STATUS — AJAX (no hard refresh)
    ================================================================ */
    var searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const url = new URL(window.location.href);
            if (!q) url.searchParams.delete('search'); else url.searchParams.set('search', q);
            url.searchParams.set('page', '1');
            updateGrid(url.toString());
        }, 300);
    }

    function applyStatusFilter(val) {
        const url = new URL(window.location.href);
        if (!val) url.searchParams.delete('status'); else url.searchParams.set('status', val);
        url.searchParams.set('page', '1');
        updateGrid(url.toString());
    }

    function applyPageSize(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('limit', val);
        url.searchParams.set('page', '1');
        updateGrid(url.toString());
    }

    /* ================================================================
       SORTING — AJAX (no hard refresh)
    ================================================================ */
    var sortField = '{{ request('sort', 'created_at') }}';
    var sortDir = '{{ request('dir', 'desc') }}';

    function doSort(field) {
        if (sortField === field) {
            sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            sortField = field;
            sortDir = 'asc';
        }
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortField);
        url.searchParams.set('dir', sortDir);
        url.searchParams.set('page', '1');
        updateGrid(url.toString());
    }

    /* ================================================================
       FILTER ROW
    ================================================================ */
    function toggleFilter() {
        var filterRow = document.getElementById('filter-row');
        var isVisible = filterRow.style.display === 'table-row';
        filterRow.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);

        if (!isVisible) {
            const urlParams = new URLSearchParams(window.location.search);
            document.querySelectorAll('.filter-input').forEach(inp => {
                const idx = parseInt(inp.dataset.colIdx);
                if (idx === 2) inp.value = urlParams.get('filter_party') || '';
                else if (idx === 3) inp.value = urlParams.get('filter_file_no') || '';
                else if (idx === 4) inp.value = urlParams.get('filter_inv_no') || '';
                else if (idx === 6) inp.value = urlParams.get('filter_type') || '';
            });
            document.querySelector('.filter-input')?.focus();
        } else {
            document.querySelectorAll('.filter-input').forEach(i => { i.value = ''; });
            filterOnInput();
        }
    }

    /* ================================================================
       AJAX GRID UPDATE — fetch filtered/paginated HTML without page refresh
    ================================================================ */
    async function updateGrid(url, pushState) {
        if (pushState === undefined) pushState = true;
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newBody = doc.getElementById('grid-body');
            const newPagination = doc.getElementById('pagination-container');
            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;
            const newStats = doc.querySelector('.portlet-tool.bottom div:last-child');
            if (newStats) {
                const text = newStats.textContent;
                const matches = text.match(/\d+/g);
                if (matches && matches.length >= 3) {
                    document.getElementById('stat-first').textContent = matches[0];
                    document.getElementById('stat-last').textContent = matches[1];
                    document.getElementById('stat-total').textContent = matches[2];
                }
            }
            const newInvTotal = doc.getElementById('stat-inv-total');
            const newPaidTotal = doc.getElementById('stat-paid-total');
            const newBalTotal = doc.getElementById('stat-bal-total');
            if (newInvTotal) document.getElementById('stat-inv-total').textContent = newInvTotal.textContent;
            if (newPaidTotal) document.getElementById('stat-paid-total').textContent = newPaidTotal.textContent;
            if (newBalTotal) document.getElementById('stat-bal-total').textContent = newBalTotal.textContent;
            if (pushState) window.history.pushState({ gridUrl: url }, '', url);
            updateToolbar();
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update grid');
        }
    }

    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.gridUrl) {
            updateGrid(e.state.gridUrl, false);
        }
    });

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    /* ================================================================
       FILTER ROW — auto-filter on typing via AJAX
    ================================================================ */
    var filterDebounce;
    function filterOnInput() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            const inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            var url = new URL(window.location.href);
            url.search = '';
            const filterMap = { 2: 'filter_party', 3: 'filter_file_no', 4: 'filter_inv_no', 6: 'filter_type' };
            inputs.forEach(inp => {
                const v = inp.value.trim();
                if (!v) return;
                const param = filterMap[inp.dataset.colIdx];
                if (param) url.searchParams.set(param, v);
            });
            url.searchParams.set('page', '1');
            updateGrid(url.toString());
        }, 300);
    }

    /* ================================================================
       MAKE / RECEIVE PAYMENT
    ================================================================ */
    function makePayment() {
        const selected = getSelectedInvoiceData();
        if (selected.length === 0) {
            showToast('info', 'Please select one or more invoices to record a payment.');
            return;
        }
        if (selected.length === 1) {
            window.location.href = '{{ url("accounting/payment/receive") }}?invoice_id=' + selected[0].id;
            return;
        }
        window.location.href = '{{ url("accounting/payment/receive") }}?invoice_ids=' + selected.map(s => s.id).join(',');
    }

    /* ================================================================
       PAYMENT PORTAL — modal showing outstanding invoices
    ================================================================ */
    function openPaymentPortal() {
        const overlay = document.getElementById('payment-portal-overlay');
        if (!overlay) return;
        overlay.classList.add('open');
        loadPaymentPortalData();
    }

    function closePaymentPortal() {
        const overlay = document.getElementById('payment-portal-overlay');
        if (overlay) overlay.classList.remove('open');
    }

    async function loadPaymentPortalData() {
        const container = document.getElementById('portal-invoices-body');
        if (!container) return;
        container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';
        try {
            const url = new URL('{{ route("accounting.invoices.index") }}');
            url.searchParams.set('status', 'POSTED');
            url.searchParams.set('limit', '50');
            const response = await fetch(url.toString());
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const rows = doc.querySelectorAll('#grid-body tr[data-id]');
            if (rows.length === 0) {
                container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">No outstanding invoices found.</td></tr>';
                return;
            }
            let html_rows = '';
            rows.forEach(row => {
                const invNo = row.dataset.invNo || '';
                const cells = row.querySelectorAll('td');
                const party = cells[2] ? cells[2].textContent.trim() : '';
                const amount = cells[11] ? cells[11].textContent.trim() : '';
                const balance = cells[16] ? cells[16].textContent.trim() : '';
                html_rows += '<tr style="border-bottom:1px solid #e2e8f0;">' +
                    '<td style="padding:6px 8px;"><input type="checkbox" class="portal-check" value="' + row.dataset.id + '"></td>' +
                    '<td style="padding:6px 8px;font-weight:600;">' + invNo + '</td>' +
                    '<td style="padding:6px 8px;">' + party + '</td>' +
                    '<td style="padding:6px 8px;text-align:right;">' + amount + '</td>' +
                    '<td style="padding:6px 8px;text-align:right;color:#e08283;">' + balance + '</td>' +
                    '<td style="padding:6px 8px;text-align:center;"><span class="badge-status badge-posted">POSTED</span></td>' +
                    '<td style="padding:6px 8px;text-align:center;"><a href="{{ url("accounting/payment/receive") }}?invoice_id=' + row.dataset.id + '" class="btn-tool green" style="font-size:9px;padding:2px 8px;">Pay</a></td>' +
                    '</tr>';
            });
            container.innerHTML = html_rows;
        } catch (e) {
            container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#e08283;">Failed to load data.</td></tr>';
        }
    }

    function portalSelectAll(cb) {
        document.querySelectorAll('.portal-check').forEach(c => c.checked = cb.checked);
    }

    function portalBulkPay() {
        const checked = [...document.querySelectorAll('.portal-check:checked')];
        if (checked.length === 0) { showToast('info', 'Select invoices to pay.'); return; }
        if (checked.length === 1) {
            window.location.href = '{{ url("accounting/payment/receive") }}?invoice_id=' + checked[0].value;
        } else {
            window.location.href = '{{ url("accounting/payment/receive") }}?invoice_ids=' + checked.map(c => c.value).join(',');
        }
    }

    /* ================================================================
       BATCH UPDATE INVOICES
    ================================================================ */
    function batchUpdateInvoices() {
        const selected = getSelectedIds();
        if (selected.length === 0) {
            showToast('info', 'Please select one or more invoices to batch update.');
            return;
        }
        const overlay = document.getElementById('batch-update-overlay');
        if (!overlay) return;
        document.getElementById('batch-update-count').textContent = selected.length;
        overlay.classList.add('open');
    }

    function closeBatchUpdate() {
        const overlay = document.getElementById('batch-update-overlay');
        if (overlay) overlay.classList.remove('open');
    }

    async function executeBatchUpdate() {
        const status = document.getElementById('batch-new-status').value;
        const ids = getSelectedIds();
        if (!ids.length) return;
        if (!status) { showToast('info', 'Select a target status.'); return; }
        closeBatchUpdate();
        showToast('info', 'Updating ' + ids.length + ' invoice(s)...');
        try {
            const resp = await fetch('{{ route("accounting.invoices.batch-update-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids, status: status }),
            });
            const data = await resp.json();
            if (data.success) {
                showToast('success', data.message || ids.length + ' invoice(s) updated to ' + status + '.');
            } else {
                showToast('error', data.message || 'Failed to update invoices.');
            }
        } catch (e) {
            showToast('error', 'Failed to batch update.');
        }
        setTimeout(() => { const url = new URL(window.location.href); updateGrid(url.toString()); }, 500);
    }

    /* ================================================================
       CREATE FROM AR / AP
    ================================================================ */
    function createFromAR() {
        window.location.href = '{{ route("accounting.invoices.create") }}?type=AR';
    }
    function createFromAP() {
        window.location.href = '{{ route("accounting.invoices.create") }}?type=AP';
    }

    /* ================================================================
       BATCH SEND INVOICES
    ================================================================ */
    function batchSendInvoices() {
        const selected = getSelectedInvoiceData();
        if (selected.length === 0) {
            showToast('info', 'Please select invoices to send.');
            return;
        }
        showToast('info', 'Sending ' + selected.length + ' invoice(s) via email...');
        fetch('{{ route("accounting.invoices.index") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: 'batch_send', ids: selected.map(s => s.id) }),
        }).then(r => r.json()).then(data => {
            showToast('success', selected.length + ' invoice(s) queued for sending.');
        }).catch(() => showToast('info', 'Batch send feature is being configured.'));
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'party', 'file_no', 'inv_no', 'color'];

    function toggleConfig() {
        const panel = document.getElementById('config-panel');
        const open  = panel.style.display === 'none';
        panel.style.display = open ? 'block' : 'none';
        document.getElementById('btn-config').classList.toggle('active', open);
        if (open) buildConfigPanel();
    }

    function buildConfigPanel() {
        const container = document.getElementById('col-toggles');
        container.innerHTML = '';
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (PINNED_COLS.includes(th.dataset.col)) return;
            const label = document.createElement('label');
            const cb    = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.onchange = () => toggleColumn(th.dataset.col, cb.checked);
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim());
            container.appendChild(label);
        });
    }

    function toggleColumn(colName, show) {
        const th  = document.querySelector(`#header-row th[data-col="${colName}"]`);
        if (!th) return;
        const idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(row => {
            const cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn   = document.getElementById('btn-config');
        if (panel && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ================================================================
       COLOR PICKER
    ================================================================ */
    var COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' },
    ];

    var _colorInvoiceId = null;

    function openColorPicker(id, currentColor) {
        _colorInvoiceId = id;
        const grid = document.getElementById('color-picker-grid');
        grid.innerHTML = COLOR_OPTIONS.map(o => {
            const active = o.value === currentColor;
            return `<div class="color-picker-opt ${active ? 'active' : ''}" onclick="selectColor('${o.value}', this)"><span class="swatch" style="background:${o.value}"></span><span>${o.label}</span><i class="fa fa-check"></i></div>`;
        }).join('');
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function selectColor(color, el) {
        document.querySelectorAll('.color-picker-opt').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        const id = _colorInvoiceId;
        fetch('{{ route("accounting.invoices.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#inv-row-${id} .color-mark`);
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            }
        }).catch(() => showToast('error', 'Failed to update color'));
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorInvoiceId = null;
    }

    function clearColor() {
        const id = _colorInvoiceId;
        fetch('{{ route("accounting.invoices.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#inv-row-${id} .color-mark`);
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            }
        }).catch(() => showToast('error', 'Failed to clear color'));
        closeColorPicker();
    }

    /* ================================================================
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `<i class="fa fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const statusVal = urlParams.get('status');
        if (statusVal) document.getElementById('status-filter').value = statusVal;
    });
    </script>
    @endpush
</x-layout>
