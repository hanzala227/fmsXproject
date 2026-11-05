<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        @media (max-width: 768px) {
            .page-content { padding: 2px !important; overflow-x: hidden !important; }
            .portlet.light { margin: 0 !important; border-radius: 0 !important; overflow: hidden !important; }
            .portlet-title { flex-direction: column !important; align-items: flex-start !important; padding: 6px !important; gap: 6px; }
            .portlet-title .caption { width: 100%; flex-wrap: wrap; }
            .portlet-title .actions { width: 100%; flex-wrap: wrap; gap: 3px !important; }
            .btn-action-round { font-size: 9px !important; padding: 0 6px !important; height: 18px !important; }
            .portlet-tool { flex-direction: column !important; align-items: flex-start !important; padding: 6px !important; gap: 6px !important; }
            .portlet-tool > div, .portlet-tool > form { width: 100%; }
            .btn-group { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
            .btn-tool { font-size: 8px !important; padding: 0 6px !important; height: 20px !important; flex: 0 1 auto; }
            .input-inline { width: 100% !important; font-size: 9px !important; }
            .portlet-body { padding: 0 !important; overflow: hidden !important; }
            .grid-container { width: 100% !important; overflow: hidden !important; }
            .grid-wrapper { width: 100% !important; height: calc(100vh - 350px) !important; min-height: 200px !important; overflow-x: auto !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; }
            .grid-table { font-size: 8px !important; width: auto !important; min-width: 1000px !important; }
            .grid-table th, .grid-table td { padding: 2px 4px !important; height: 22px !important; }
            .sticky-col { font-size: 8px !important; }
            .grid-table th:nth-child(3), .grid-table td:nth-child(3),
            .grid-table th:nth-child(4), .grid-table td:nth-child(4),
            .grid-table th:nth-child(5), .grid-table td:nth-child(5) { position: static !important; left: auto !important; }
            .filter-input { height: 18px !important; font-size: 8px !important; }
            .portlet-tool.bottom { flex-direction: column !important; gap: 6px; }
            .portlet-tool.bottom > div { width: 100% !important; }
        }
        @media (max-width: 480px) {
            .grid-table { font-size: 7px !important; min-width: 800px !important; }
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) { position: static !important; left: auto !important; }
        }
    </style>
    @endpush

    {{-- Toast Container --}}
    <div class="toast-container" id="toast-container"></div>

    @php
        $isAdminUser = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin());
    @endphp

    @if($isAdminUser)
    {{-- CANCELLATION MODAL (Admin Only - Document Control) --}}
    <div class="overlay" id="cancel-payment-overlay" onclick="if(event.target===this) closeCancelPaymentModal()">
        <div class="confirm-box" style="max-width:480px;text-align:left;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">
                <h4 style="margin:0;font-size:13px;font-weight:700;color:#0f172a;">
                    <i class="fa fa-ban" style="color:#ef4444;margin-right:6px;"></i> Cancel Payment Voucher <span id="cancel-payment-title"></span>
                </h4>
                <button type="button" class="btn-tool" style="padding:0 8px;height:22px;" onclick="closeCancelPaymentModal()"><i class="fa fa-times"></i></button>
            </div>
            <div style="margin-bottom:12px;background:#fef2f2;border:1px solid #fee2e2;border-radius:6px;padding:8px 10px;font-size:11px;color:#991b1b;line-height:1.4;">
                <i class="fa fa-shield" style="margin-right:4px;"></i> <strong>Document Control Policy:</strong>
                Permanent deletion of financial payment records is strictly prohibited. Cancellation voids the payment, restores the linked bill balance, and retains a permanent audit trail with your user ID and cancellation reason.
            </div>
            <form id="cancelPaymentForm" onsubmit="executeCancelPayment(event)">
                <input type="hidden" id="cancel-payment-id" value="">
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#334155;margin-bottom:4px;">
                        Cancellation Reason <span style="color:#ef4444;">*</span>
                    </label>
                    <textarea id="cancel-payment-reason" rows="3" required minlength="5" placeholder="Enter mandatory reason for cancellation (min 5 characters)..." style="width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:6px 8px;font-size:11px;resize:vertical;box-sizing:border-box;"></textarea>
                </div>
                <div class="confirm-actions" style="justify-content:flex-end;gap:8px;">
                    <button type="button" class="btn-tool" style="padding:0 14px;height:26px;" onclick="closeCancelPaymentModal()">Dismiss</button>
                    <button type="submit" id="btn-submit-payment-cancel" class="btn-tool danger" style="padding:0 14px;height:26px;background:#ef4444;color:#fff;">
                        <i class="fa fa-ban"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Color Picker Modal --}}
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
        {{-- Breadcrumbs --}}
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Payment Made List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            {{-- Title --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Payment Made List</span>
                    <span style="font-size:10px;color:#64748b;font-weight:600;">
                        Total Paid: <span style="color:#3b82f6;">${{ number_format($totalPaidAmount, 2) }}</span>
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
                    <a class="btn-action-round white" href="{{ route('accounting.payment-made-list.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" title="Download as CSV/Excel" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Excel <i class="fa fa-angle-down"></i>
                    </a>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('accounting.payment-make') }}" title="New Payment">
                            <i class="fa fa-plus"></i>
                        </a>
                        @if($isAdminUser)
                        <button class="btn-tool" id="btn-cancel-payment" disabled title="Cancel Payment (Admin Only)" onclick="openCancelPaymentModal()" style="color:#ef4444;">
                            <i class="fa fa-ban"></i> Cancel Payment
                        </button>
                        @endif
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                </div>
            </div>

            {{-- Table --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                {{-- Header Row --}}
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;left:0;text-align:center;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="date" style="width:80px;left:50px;">Date</th>
                                    <th class="sticky-col sticky-col-header" data-col="paid_to" style="width:140px;left:130px;">Paid To</th>
                                    <th data-col="type" style="width:90px;">Type</th>
                                    <th data-col="ref_no" style="width:100px;">Ref No.</th>
                                    <th data-col="bank" style="width:100px;">Bank</th>
                                    <th data-col="amount" style="width:100px;text-align:right;">Paid (USD)</th>
                                    <th data-col="bank_cur" style="width:130px;text-align:right;">Paid (Bank Cur.)</th>
                                    <th data-col="clear_date" style="width:85px;">Clear Date</th>
                                    <th data-col="void" style="width:40px;text-align:center;">Void</th>
                                    <th data-col="void_date" style="width:85px;">Void Date</th>
                                    <th data-col="office" style="width:80px;">Office</th>
                                    <th data-col="print" style="width:40px;text-align:center;">Print</th>
                                    <th data-col="remark" style="width:150px;">Remark</th>
                                </tr>

                                {{-- Filter Row --}}
                                <tr id="filter-row" style="display:none;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:50px;"></td>
                                     <td class="sticky-col" style="left:130px;"><input class="filter-input" data-col-idx="3" placeholder="Partner..." oninput="debouncedApplyFilters()"></td>
                                     <td class="sticky-col" style="left:270px;"></td>
                                     <td></td>
                                     <td><input class="filter-input" data-col-idx="6" placeholder="Ref No..." oninput="debouncedApplyFilters()"></td>
                                     <td><input class="filter-input" data-col-idx="7" placeholder="Bank..." oninput="debouncedApplyFilters()"></td>
                                     <td></td>
                                     <td></td>
                                     <td></td>
                                     <td></td>
                                     <td></td>
                                     <td><input class="filter-input" data-col-idx="13" placeholder="Office..." oninput="debouncedApplyFilters()"></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody id="grid-body">
                            @forelse($payments as $payment)
                                <tr id="payment-row-{{ $payment->id }}"
                                    data-id="{{ $payment->id }}"
                                    data-payment_no="{{ $payment->payment_no }}"
                                    data-date="{{ $payment->payment_date?->format('Y-m-d') ?? '' }}"
                                    data-partner="{{ $payment->tradePartner?->name ?? '' }}"
                                    data-amount="{{ $payment->amount }}"
                                    onclick="rowClick(event, this)"
                                >
                                    <td class="sticky-col" style="width:25px;left:0;text-align:center;" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="row-check" value="{{ $payment->id }}" onchange="updateToolbar()">
                                    </td>
                                    <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <i class="fa {{ $payment->deleted_at ? 'fa-lock' : 'fa-unlock-alt' }}" style="color:{{ $payment->deleted_at ? '#94a3b8' : '#22c55e' }};font-size:10px;"></i>
                                    </td>
                                    <td class="sticky-col" style="width:80px;left:50px;" onclick="event.stopPropagation()">
                                        <a href="{{ route('accounting.payment.edit', $payment->id) }}" class="col-link">
                                            {{ $payment->payment_date?->format('m-d-Y') ?? '' }}
                                        </a>
                                    </td>
                                    <td class="sticky-col" style="width:140px;left:130px;" onclick="event.stopPropagation()">
                                        <a href="{{ route('accounting.payment.edit', $payment->id) }}" class="col-link">
                                            {{ $payment->tradePartner?->name ?? '' }}
                                        </a>
                                    </td>
                                    <td data-col="type">{{ $payment->payment_method }}</td>
                                    <td data-col="ref_no"><a href="{{ route('accounting.payment.edit', $payment->id) }}" class="col-link" onclick="event.stopPropagation()">{{ $payment->payment_no }}</a></td>
                                    <td data-col="bank">{{ $payment->bank_name ?? '' }}</td>
                                    <td data-col="amount" style="text-align:right;font-weight:600;color:#16a34a;">${{ number_format($payment->amount, 2) }}</td>
                                    <td data-col="bank_cur" style="text-align:right;">
                                        <span style="color:#999;">{{ $payment->bankCurrency?->code ?? $payment->currency?->code ?? 'CAD' }}</span>
                                        <span style="margin-left:6px;">${{ number_format($payment->amount, 2) }}</span>
                                    </td>
                                    <td data-col="clear_date">{{ $payment->clear_date?->format('m-d-Y') ?? '' }}</td>
                                    <td data-col="void" style="text-align:center;">{!! $payment->void_date ? '<i class="fa fa-check" style="color:#ef4444;"></i>' : '' !!}</td>
                                    <td data-col="void_date">{{ $payment->void_date?->format('m-d-Y') ?? '' }}</td>
                                    <td data-col="office">{{ $payment->office?->code ?? '' }}</td>
                                    <td data-col="print" style="text-align:center;">
                                        @if($payment->show_party_on_check)
                                            <i class="fa fa-check" style="color:#22c55e;font-weight:bold;"></i>
                                        @endif
                                    </td>
                                    <td data-col="remark" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;" title="{{ $payment->remark }}">{{ $payment->remark ?? '' }}</td>
                                </tr>
                            @empty
                                <tr id="empty-row">
                                    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No payments made found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $payments->links() }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $payments->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $payments->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $payments->total() }}</span> records
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

        let selectedVoid = false;
        if (n === 1) {
            const row = checked[0].closest('tr');
            if (row && row.dataset.void === '1') {
                selectedVoid = true;
            }
        }

        const btnCancel = document.getElementById('btn-cancel-payment');
        if (btnCancel) {
            btnCancel.disabled = (n !== 1 || selectedVoid);
        }

        const badge = document.getElementById('sel-badge');
        if (badge) {
            badge.style.display = n > 0 ? 'inline' : 'none';
            badge.textContent   = n + ' selected';
        }

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
        const skip = ['A', 'INPUT', 'BUTTON', 'I'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    /* ================================================================
       ADMIN PAYMENT CANCELLATION (Mandatory Reason Popup)
    ================================================================ */
    function openCancelPaymentModal() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) {
            showToast('info', 'Please select exactly one payment to cancel.');
            return;
        }
        const row = checked[0].closest('tr');
        if (!row) return;
        if (row.dataset.void === '1') {
            showToast('info', 'This payment is already void / cancelled.');
            return;
        }
        const id = row.dataset.id;
        const refNo = row.dataset.ref_no || row.dataset.payment_no || id;
        const overlay = document.getElementById('cancel-payment-overlay');
        if (!overlay) return;
        document.getElementById('cancel-payment-id').value = id;
        document.getElementById('cancel-payment-title').textContent = '#' + refNo;
        document.getElementById('cancel-payment-reason').value = '';
        overlay.classList.add('open');
        document.getElementById('cancel-payment-reason').focus();
    }

    function closeCancelPaymentModal() {
        const overlay = document.getElementById('cancel-payment-overlay');
        if (overlay) overlay.classList.remove('open');
    }

    async function executeCancelPayment(e) {
        e.preventDefault();
        const id = document.getElementById('cancel-payment-id').value;
        const reason = document.getElementById('cancel-payment-reason').value.trim();
        if (!reason || reason.length < 5) {
            showToast('error', 'Please enter a valid cancellation reason (minimum 5 characters).');
            return;
        }
        const submitBtn = document.getElementById('btn-submit-payment-cancel');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cancelling...'; }

        try {
            const resp = await fetch(`/accounting/payment/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: reason })
            });
            const data = await resp.json();
            if (data.success) {
                closeCancelPaymentModal();
                showToast('success', data.message || 'Payment successfully cancelled.');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showToast('error', data.message || 'Failed to cancel payment.');
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa fa-ban"></i> Confirm Cancellation'; }
            }
        } catch (err) {
            showToast('error', 'Failed to cancel payment.');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa fa-ban"></i> Confirm Cancellation'; }
        }
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    /* ================================================================
       AJAX GRID UPDATE (pagination — no hard refresh)
    ================================================================ */
    function updateGrid(url) {
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            window.history.replaceState({}, '', url);
            replaceGridContent(data);
        })
        .catch(() => showToast('error', 'Failed to update grid'));
    }

    // Wire pagination links to use AJAX
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    /* ================================================================
       QUICK SEARCH
    ================================================================ */
    var searchDebounce;
    var searchController;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        if (searchController) searchController.abort();
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const basePath = window.location.origin + window.location.pathname;
            const url = new URL(basePath);
            if (q) url.searchParams.set('search', q);
            url.searchParams.set('page', '1');
            searchController = new AbortController();
            fetch(url.toString(), {
                signal: searchController.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                window.history.replaceState({}, '', url.toString());
                replaceGridContent(data);
            })
            .catch(err => { if (err.name !== 'AbortError') showToast('error', 'Search failed'); });
        }, 300);
    }

    /* ================================================================
       FILTER
    ================================================================ */
    function toggleFilter() {
        const filterRow = document.getElementById('filter-row');
        const isVisible = filterRow.style.display === 'table-row';
        filterRow.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);

        if (!isVisible) {
            const urlParams = new URLSearchParams(window.location.search);
            document.querySelectorAll('.filter-input').forEach(inp => {
                const idx = parseInt(inp.dataset.colIdx);
                const filterMap = { 3: 'filter_trade_partner', 6: 'filter_reference_no', 7: 'filter_bank', 13: 'filter_office' };
                const param = filterMap[idx];
                if (param) inp.value = urlParams.get(param) || '';
            });
        }
    }

    var filterDebounce;
    function debouncedApplyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(applyFilters, 400);
    }

    var filterController;
    function applyFilters() {
        if (filterController) filterController.abort();

        const inputs = [...document.querySelectorAll('#filter-row .filter-input')];
        const basePath = window.location.origin + window.location.pathname;
        const url = new URL(basePath);

        const filterMap = { 3: 'filter_trade_partner', 6: 'filter_reference_no', 7: 'filter_bank', 13: 'filter_office' };

        inputs.forEach(inp => {
            const v = inp.value.trim();
            if (!v) return;
            const param = filterMap[inp.dataset.colIdx];
            if (param) url.searchParams.set(param, v);
        });

        filterController = new AbortController();
        fetch(url.toString(), {
            signal: filterController.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            window.history.replaceState({}, '', url.toString());
            replaceGridContent(data);
        })
        .catch(err => { if (err.name !== 'AbortError') showToast('error', 'Filter failed'); });
    }

    /* ================================================================
       REPLACE GRID CONTENT
    ================================================================ */
    function replaceGridContent(data) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(data.table, 'text/html');
        const newBody = doc.getElementById('grid-body');
        const newPagination = parser.parseFromString(data.pagination, 'text/html').body;

        if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
        if (newPagination) {
            document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;
        }

        document.getElementById('stat-first').textContent = data.from || 0;
        document.getElementById('stat-last').textContent = data.to || 0;
        document.getElementById('stat-total').textContent = data.total || 0;

        updateToolbar();
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'date', 'paid_to', 'color'];

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

    // Close config on outside click
    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn   = document.getElementById('btn-config');
        if (panel && btn && panel.style && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
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

    var _colorPaymentId = null;

    function openColorPicker(id, currentColor) {
        _colorPaymentId = id;
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
        const id = _colorPaymentId;
        fetch('{{ route("accounting.payment-made-list.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}' },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const mark = document.querySelector(`#payment-row-${id} .color-mark`);
                if (mark) mark.style.background = color;
                showToast('success', 'Status color updated');
            }
        }).catch(() => showToast('error', 'Failed to update color'));
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorPaymentId = null;
    }

    function clearColor() {
        const id = _colorPaymentId;
        fetch('{{ route("accounting.payment-made-list.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}' },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const mark = document.querySelector(`#payment-row-${id} .color-mark`);
                if (mark) mark.style.background = '#94a3b8';
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

    /* ================================================================
       FLASH MESSAGE FROM SERVER
    ================================================================ */
    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif
    </script>
    @endpush
</x-layout>
