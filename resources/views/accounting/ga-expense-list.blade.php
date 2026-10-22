@php use Illuminate\Support\Str; @endphp
<x-layout>
    @push('styles')
    <x-list-styles />
    @endpush

    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Expense(s)?</h4>
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

    {{-- PAYMENT PORTAL MODAL --}}
    <div class="overlay" id="payment-portal-overlay" onclick="if(event.target===this) closePaymentPortal()">
        <div class="confirm-box" style="max-width:800px;width:95%;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h4 style="margin:0;font-size:13px;color:#1e293b;"><i class="fa fa-credit-card" style="color:#3b82f6;"></i> Payment Portal — Outstanding AP Expenses</h4>
                <button class="btn-tool" style="padding:0 10px;height:22px;" onclick="closePaymentPortal()"><i class="fa fa-times"></i></button>
            </div>
            <div style="max-height:400px;overflow-y:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:10px;">
                    <thead>
                        <tr style="background:#f1f5f9;border-bottom:2px solid #e2e8f0;">
                            <th style="padding:6px 8px;text-align:center;width:30px;"><input type="checkbox" onchange="portalSelectAll(this)"></th>
                            <th style="padding:6px 8px;text-align:left;">Reference No.</th>
                            <th style="padding:6px 8px;text-align:left;">Party</th>
                            <th style="padding:6px 8px;text-align:right;">Amount</th>
                            <th style="padding:6px 8px;text-align:right;">Balance</th>
                            <th style="padding:6px 8px;text-align:center;">Status</th>
                            <th style="padding:6px 8px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="portal-expenses-body">
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
            <h4>Batch Update Expenses</h4>
            <p>Update status for <strong id="batch-update-count">0</strong> selected expense(s).</p>
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

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">G&A Invoice / Expense List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">G&A INVOICE / EXPENSE LIST</span>
                    <span class="summary-stats">
                        Amount: <span class="val" id="stat-total-amt">{{ number_format($totalAmount, 2) }}</span>
                        Paid: <span class="val paid" id="stat-total-paid">{{ number_format($totalPaid, 2) }}</span>
                        Balance: <span class="val" id="stat-total-bal">{{ number_format($totalBalance, 2) }}</span>
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
                    <a class="btn-action-round white" href="{{ route('accounting.ga-expense.export-csv') }}" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel <i class="fa fa-angle-down"></i>
                    </a>
                    <a href="{{ route('accounting.ga-expense.create') }}" class="btn-action-round green-btn">
                        <i class="fa fa-plus"></i> New Expense
                    </a>
                </div>
            </div>

            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('accounting.ga-expense.create') }}" title="New Expense">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-copy" disabled title="Copy Selected (select 1 row)" onclick="copySelected()">
                            <i class="fa fa-files-o"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool green" onclick="makePayment()" title="Make Payment">
                            <i class="fa fa-credit-card"></i> Make Payment
                        </button>
                        <button class="btn-tool green" onclick="receivePayment()" title="Receive Payment">
                            <i class="fa fa-money"></i> Receive Payment
                        </button>
                        <button class="btn-tool green" onclick="openPaymentPortal()" title="Payment Portal">
                            Payment Portal <span style="background:#f3c200;padding:0 3px;border-radius:2px;color:white;font-size:8px;margin-left:2px;">New</span>
                        </button>
                        <button class="btn-tool" style="display:none !important;" onclick="batchUpdateExpenses()" title="Batch update Expenses">
                            Batch update
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

            <form id="bulk-form" method="POST" action="{{ route('accounting.ga-expense.bulk-delete') }}" style="margin:0;">
                @csrf
                @method('DELETE')
                <div class="portlet-body">
                    <div class="grid-container">
                        <div class="grid-wrapper">
                            <table class="grid-table" id="main-grid">
                                <thead>
                                    <tr id="header-row">
                                        <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;">
                                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                        </th>
                                        <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                        <th class="sticky-col sticky-col-header" data-col="party" style="width:150px;left:50px;">Party</th>
                                        <th class="sticky-col sticky-col-header" data-col="ref_no" style="width:120px;left:200px;">Reference No.</th>
                                        <th data-col="amt_bf_tax" style="width:110px;text-align:right;cursor:pointer;" onclick="doSort('subtotal')">Amount Before Tax <i class="fa fa-sort" id="sort-subtotal"></i></th>
                                        <th data-col="tax" style="width:90px;text-align:right;">Tax Amount</th>
                                        <th data-col="amount" style="width:110px;text-align:right;cursor:pointer;" onclick="doSort('total_amount')">Amount <i class="fa fa-sort" id="sort-total_amount"></i></th>
                                        <th data-col="paid" style="width:110px;text-align:right;cursor:pointer;" onclick="doSort('paid_amount')">Paid Amount <i class="fa fa-sort" id="sort-paid_amount"></i></th>
                                        <th data-col="last_paid" style="width:95px;">Last Paid Date</th>
                                        <th data-col="balance" style="width:110px;text-align:right;cursor:pointer;" onclick="doSort('balance_amount')">Balance <i class="fa fa-sort" id="sort-balance_amount"></i></th>
                                        <th data-col="type" style="width:40px;text-align:center;">Type</th>
                                        <th data-col="post_date" style="width:95px;cursor:pointer;" onclick="doSort('invoice_date')">Post Date <i class="fa fa-sort" id="sort-invoice_date"></i></th>
                                        <th data-col="due_date" style="width:95px;cursor:pointer;" onclick="doSort('due_date')">Due Date <i class="fa fa-sort" id="sort-due_date"></i></th>
                                        <th data-col="overdue" style="width:60px;text-align:center;">Over Due</th>
                                        <th data-col="office" style="width:65px;">Office</th>
                                        <th data-col="issued_by" style="width:95px;">Issued by</th>
                                        <th data-col="status" style="width:55px;text-align:center;cursor:pointer;" onclick="doSort('status')">Status <i class="fa fa-sort" id="sort-status"></i></th>
                                        <th data-col="inv_sent" style="width:115px;">Invoice Sent Date</th>
                                    </tr>

                                    <tr id="filter-row" style="display:none;">
                                        <td class="sticky-col" style="left:0;"></td>
                                        <td class="sticky-col" style="left:25px;"></td>
                                        <td class="sticky-col" style="left:50px;"></td>
                                        <td class="sticky-col" style="left:50px;"><input class="filter-input" data-col-idx="3" placeholder="Party..." oninput="filterOnInput()"></td>
                                        <td class="sticky-col" style="left:200px;"><input class="filter-input" data-col-idx="4" placeholder="Reference No..." oninput="filterOnInput()"></td>
                                        <td></td><td></td><td></td><td></td><td></td><td></td>
                                        <td></td>
                                        <td></td><td></td><td></td>
                                        <td><input class="filter-input" data-col-idx="15" placeholder="Office..." oninput="filterOnInput()"></td>
                                        <td></td>
                                        <td><select class="filter-input" data-col-idx="17" onchange="filterOnInput()"><option value="">All</option><option value="DRAFT">DRAFT</option><option value="POSTED">POSTED</option><option value="PAID">PAID</option><option value="PARTIAL">PARTIAL</option><option value="VOID">VOID</option></select></td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody id="grid-body">
                                @forelse($expenses as $expense)
                                    <tr id="expense-row-{{ $expense->id }}"
                                        data-id="{{ $expense->id }}"
                                        data-file="{{ $expense->invoice_no }}"
                                        onclick="rowClick(event, this)"
                                    >
                                        <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="ids[]" value="{{ $expense->id }}" class="row-check" onchange="updateToolbar()">
                                        </td>
                                        <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
                                            <i class="fa {{ $expense->status === 'POSTED' || $expense->status === 'PAID' ? 'fa-lock' : 'fa-unlock' }}" style="color:#94a3b8;cursor:pointer;font-size:10px;" title="Lock status" onclick="toggleLock(this)"></i>
                                        </td>
                                        <td class="sticky-col" style="width:150px;left:50px;">{{ $expense->billTo ? Str::limit($expense->billTo->name, 18) : '--' }}</td>
                                        <td class="sticky-col" style="width:120px;left:200px;">
                                            <a href="{{ route('accounting.ga-expense.show', $expense->id) }}" class="col-link">{{ $expense->invoice_no }}</a>
                                        </td>
                                        <td style="text-align:right;">{{ number_format($expense->subtotal, 2) }}</td>
                                        <td style="text-align:right;">{{ number_format($expense->tax_total, 2) }}</td>
                                        <td style="text-align:right;">
                                            <div class="currency-block">
                                                <span><span class="cur">{{ $expense->currency ? $expense->currency->code : 'USD' }}</span> {{ number_format($expense->total_amount, 2) }}</span>
                                            </div>
                                        </td>
                                        <td style="text-align:right;">
                                            <div class="currency-block">
                                                <span><span class="cur">{{ $expense->currency ? $expense->currency->code : 'USD' }}</span> {{ number_format($expense->paid_amount, 2) }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $expense->payments->first()?->payment_date?->format('m-d-Y') ?? '--' }}</td>
                                        <td style="text-align:right;">
                                            <div class="currency-block">
                                                <span><span class="cur">{{ $expense->currency ? $expense->currency->code : 'USD' }}</span> {{ number_format($expense->balance_amount, 2) }}</span>
                                            </div>
                                        </td>
                                        <td style="text-align:center;"><span style="font-weight:600;color:#e08283;">{{ $expense->type }}</span></td>
                                        <td>{{ $expense->invoice_date ? $expense->invoice_date->format('m-d-Y') : '--' }}</td>
                                        <td>{{ $expense->due_date ? $expense->due_date->format('m-d-Y') : '--' }}</td>
                                        <td style="text-align:center;">
                                            @php($isOverdue = $expense->due_date && $expense->due_date < now() && $expense->status !== 'PAID' && $expense->status !== 'VOID')
                                            <span class="{{ $isOverdue ? 'badge-overdue' : '' }}">{{ $isOverdue ? $expense->due_date->diffInDays(now()) : '0' }}</span>
                                        </td>
                                        <td>{{ $expense->office ? $expense->office->code : '--' }}</td>
                                        <td>{{ $expense->issuer ? $expense->issuer->name : '--' }}</td>
                                        <td style="text-align:center;">
                                            @php($statusBadge = $expense->status === 'POSTED' ? 'bg-blue' : ($expense->status === 'PAID' ? 'bg-green' : ($expense->status === 'PARTIAL' ? 'bg-yellow' : ($expense->status === 'VOID' ? 'bg-red' : 'bg-gray'))))
                                            <span class="badge-status {{ $statusBadge }}">{{ $expense->status }}</span>
                                        </td>
                                        <td>{{ $expense->internal_remark ?? $expense->created_at->format('m-d-Y') }}</td>
                                    </tr>
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="20" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No G&A expenses found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $expenses->links() }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $expenses->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $expenses->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $expenses->total() }}</span> records
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function updateToolbar() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const all = [...document.querySelectorAll('.row-check')];
        const n = checked.length;
        const sa = document.getElementById('select-all');
        sa.checked = n === all.length && all.length > 0;
        sa.indeterminate = n > 0 && n < all.length;
        document.getElementById('btn-delete').disabled = n === 0;
        document.getElementById('btn-copy').disabled = n !== 1;
        const badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent = n + ' selected';
        document.querySelectorAll('#grid-body tr[data-id]').forEach(row => {
            const cb = row.querySelector('.row-check');
            row.classList.toggle('row-selected', cb && cb.checked);
        });
    }

    function toggleSelectAll(el) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = el.checked);
        updateToolbar();
    }

    function rowClick(e, row) {
        const skip = ['A', 'INPUT', 'BUTTON', 'I', 'SPAN'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    function getSelectedExpenseData() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => {
            const row = cb.closest('tr[data-id]');
            return { id: cb.value, ref_no: row?.dataset.file || '' };
        });
    }

    /* DELETE */
    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent = `You are about to permanently delete ${n} expense(s). This cannot be undone.`;
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
        fetch('{{ route("accounting.ga-expense.bulk-delete") }}', {
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
                const url = new URL(window.location.href);
                updateGrid(url.toString());
            } else {
                showToast('error', data.message || 'Failed to delete');
            }
        }).catch(() => showToast('error', 'Failed to delete'));
    }

    /* COPY */
    function copySelected() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        const id = checked[0].value;
        showToast('info', 'Copying expense...');
        setTimeout(() => {
            window.location.href = '{{ route("accounting.ga-expense.duplicate", "ID") }}'.replace('ID', id);
        }, 400);
    }

    /* LOCK */
    function toggleLock(el) {
        const locked = el.classList.contains('fa-lock');
        el.classList.toggle('fa-lock', !locked);
        el.classList.toggle('fa-unlock', locked);
        el.title = locked ? 'Lock' : 'Unlock';
        showToast('info', locked ? 'Row unlocked.' : 'Row locked.');
    }

    /* SEARCH & STATUS FILTERS — AJAX */
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

    /* SORTING — AJAX */
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

    /* FILTER ROW */
    function toggleFilter() {
        var filterRow = document.getElementById('filter-row');
        var isVisible = filterRow.style.display === 'table-row';
        filterRow.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);
        if (!isVisible) {
            const urlParams = new URLSearchParams(window.location.search);
            document.querySelectorAll('.filter-input').forEach(inp => {
                const idx = parseInt(inp.dataset.colIdx);
                if (idx === 3) inp.value = urlParams.get('filter_party') || '';
                else if (idx === 4) inp.value = urlParams.get('filter_inv_no') || '';
                else if (idx === 15) inp.value = urlParams.get('filter_office') || '';
                else if (idx === 17) inp.value = urlParams.get('filter_status') || '';
            });
            document.querySelector('.filter-input')?.focus();
        } else {
            document.querySelectorAll('.filter-input').forEach(i => { i.value = ''; });
            filterOnInput();
        }
    }

    var filterDebounce;
    function filterOnInput() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            const inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            var url = new URL(window.location.href);
            url.search = '';
            const filterMap = { 3: 'filter_party', 4: 'filter_inv_no', 15: 'filter_office', 17: 'filter_status' };
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

    /* AJAX GRID UPDATE */
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
            const newTotalAmt = doc.getElementById('stat-total-amt');
            const newTotalPaid = doc.getElementById('stat-total-paid');
            const newTotalBal = doc.getElementById('stat-total-bal');
            if (newTotalAmt) document.getElementById('stat-total-amt').textContent = newTotalAmt.textContent;
            if (newTotalPaid) document.getElementById('stat-total-paid').textContent = newTotalPaid.textContent;
            if (newTotalBal) document.getElementById('stat-total-bal').textContent = newTotalBal.textContent;
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

    /* MAKE PAYMENT */
    function makePayment() {
        const selected = getSelectedExpenseData();
        if (selected.length === 0) {
            showToast('info', 'Please select one or more expenses to make a payment.');
            return;
        }
        if (selected.length === 1) {
            window.location.href = '{{ route("accounting.payment-make") }}?invoice_id=' + encodeURIComponent(selected[0].id);
            return;
        }
        window.location.href = '{{ route("accounting.payment-make") }}?invoice_ids=' + encodeURIComponent(selected.map(s => s.id).join(','));
    }

    /* RECEIVE PAYMENT */
    function receivePayment() {
        const selected = getSelectedExpenseData();
        if (selected.length === 0) {
            showToast('info', 'Please select one or more invoices to receive a payment.');
            return;
        }
        if (selected.length === 1) {
            window.location.href = '{{ route("accounting.payment-receive") }}?invoice_id=' + encodeURIComponent(selected[0].id);
            return;
        }
        window.location.href = '{{ route("accounting.payment-receive") }}?invoice_ids=' + encodeURIComponent(selected.map(s => s.id).join(','));
    }

    /* PAYMENT PORTAL */
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
        const container = document.getElementById('portal-expenses-body');
        if (!container) return;
        container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';
        try {
            const url = new URL('{{ route("accounting.ga-expense.index") }}');
            url.searchParams.set('status', 'POSTED');
            url.searchParams.set('limit', '50');
            const response = await fetch(url.toString());
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const rows = doc.querySelectorAll('#grid-body tr[data-id]');
            if (rows.length === 0) {
                container.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">No outstanding expenses found.</td></tr>';
                return;
            }
            let html_rows = '';
            rows.forEach(row => {
                const refNo = row.dataset.file || '';
                const cells = row.querySelectorAll('td');
                const party = cells[3] ? cells[3].textContent.trim() : '';
                const amount = cells[7] ? cells[7].textContent.trim() : '';
                const balance = cells[10] ? cells[10].textContent.trim() : '';
                html_rows += '<tr style="border-bottom:1px solid #e2e8f0;">' +
                    '<td style="padding:6px 8px;"><input type="checkbox" class="portal-check" value="' + row.dataset.id + '"></td>' +
                    '<td style="padding:6px 8px;font-weight:600;">' + refNo + '</td>' +
                    '<td style="padding:6px 8px;">' + party + '</td>' +
                    '<td style="padding:6px 8px;text-align:right;">' + amount + '</td>' +
                    '<td style="padding:6px 8px;text-align:right;color:#e08283;">' + balance + '</td>' +
                    '<td style="padding:6px 8px;text-align:center;"><span class="badge-status bg-blue">POSTED</span></td>' +
                    '<td style="padding:6px 8px;text-align:center;"><a href="{{ url("accounting/payment/make") }}?invoice_id=' + row.dataset.id + '" class="btn-tool green" style="font-size:9px;padding:2px 8px;">Pay</a></td>' +
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
        if (checked.length === 0) { showToast('info', 'Select expenses to pay.'); return; }
        if (checked.length === 1) {
            window.location.href = '{{ url("accounting/payment/make") }}?invoice_id=' + checked[0].value;
        } else {
            window.location.href = '{{ url("accounting/payment/make") }}?invoice_ids=' + checked.map(c => c.value).join(',');
        }
    }

    /* BATCH UPDATE */
    function batchUpdateExpenses() {
        const selected = getSelectedIds();
        if (selected.length === 0) {
            showToast('info', 'Please select one or more expenses to batch update.');
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
        showToast('info', 'Updating ' + ids.length + ' expense(s)...');
        try {
            const resp = await fetch('{{ route("accounting.ga-expense.batch-update-status") }}', {
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
                showToast('success', data.message || ids.length + ' expense(s) updated to ' + status + '.');
            } else {
                showToast('error', data.message || 'Failed to update expenses.');
            }
        } catch (e) {
            showToast('error', 'Failed to batch update.');
        }
        setTimeout(() => { const url = new URL(window.location.href); updateGrid(url.toString()); }, 500);
    }

    /* CONFIG PANEL */
    var PINNED_COLS = ['check', 'lock', 'color', 'party', 'ref_no'];

    function toggleConfig() {
        const panel = document.getElementById('config-panel');
        const open = panel.style.display === 'none';
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
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.onchange = () => toggleColumn(th.dataset.col, cb.checked);
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim());
            container.appendChild(label);
        });
    }
    function toggleColumn(colName, show) {
        const th = document.querySelector(`#header-row th[data-col="${colName}"]`);
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
        const btn = document.getElementById('btn-config');
        if (panel && btn && panel.style && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* COLOR PICKER */
    var COLOR_OPTIONS = [
        { label: 'Rejected', value: '#E57373' },
        { label: 'Email Sent', value: '#FBC02D' },
        { label: 'Ready to Pay', value: '#26A69A' },
        { label: 'Re-submit', value: '#5C6BC0' },
        { label: 'Approved', value: '#9E9E9E' },
    ];
    var _colorExpenseId = null;

    function openColorPicker(id, currentColor) {
        _colorExpenseId = id;
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
        const id = _colorExpenseId;
        fetch('{{ route("accounting.ga-expense.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#expense-row-${id} .color-mark`);
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            }
        }).catch(() => showToast('error', 'Failed to update color'));
        closeColorPicker();
    }
    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorExpenseId = null;
    }
    function clearColor() {
        const id = _colorExpenseId;
        fetch('{{ route("accounting.ga-expense.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#expense-row-${id} .color-mark`);
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            }
        }).catch(() => showToast('error', 'Failed to clear color'));
        closeColorPicker();
    }

    /* TOAST */
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
