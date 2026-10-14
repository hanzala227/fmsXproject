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

    {{-- Delete Confirm Modal --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Payment(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

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
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Payment Received List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            {{-- Title --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Payment Received List</span>
                    <span style="font-size:10px;color:#64748b;font-weight:600;">
                        Total Received: <span style="color:#3b82f6;">${{ number_format($totalReceivedAmount, 2) }}</span>
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
                    <a class="btn-action-round white" href="{{ route('accounting.payment-received-list.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" title="Download as CSV/Excel" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Excel <i class="fa fa-angle-down"></i>
                    </a>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('accounting.payment-receive') }}" title="New Payment Receipt">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i> Delete
                        </button>
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
                                    <th class="sticky-col sticky-col-header" data-col="received_from" style="width:140px;left:130px;">Received From</th>
                                    <th data-col="type" style="width:90px;">Type</th>
                                    <th data-col="ref_no" style="width:100px;">Ref No.</th>
                                    <th data-col="bank" style="width:100px;">Bank</th>
                                    <th data-col="amount" style="width:100px;text-align:right;">Received (CAD)</th>
                                    <th data-col="bank_cur" style="width:130px;text-align:right;">Received (Bank Cur.)</th>
                                    <th data-col="deposit" style="width:85px;">Deposit Date</th>
                                    <th data-col="void" style="width:40px;text-align:center;">Void</th>
                                    <th data-col="void_date" style="width:85px;">Void Date</th>
                                    <th data-col="office" style="width:80px;">Office</th>
                                    <th data-col="remark" style="width:150px;">Remark</th>
                                    <th data-col="status" style="width:80px;">Status</th>
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
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>{{ $payment->reference_no }}</td>
                                    <td>{{ $payment->bank_name ?? '' }}</td>
                                    <td style="text-align:right;">{{ number_format($payment->amount, 2) }}</td>
                                    <td style="text-align:right;">
                                        <span style="color:#999;">{{ $payment->bankCurrency?->code ?? $payment->currency?->code ?? 'CAD' }}</span>
                                        <span style="margin-left:6px;">{{ number_format($payment->amount, 2) }}</span>
                                    </td>
                                    <td>{{ $payment->clear_date?->format('m-d-Y') ?? '' }}</td>
                                    <td style="text-align:center;">{!! $payment->void_date ? '<i class="fa fa-check" style="color:#ef4444;"></i>' : '' !!}</td>
                                    <td>{{ $payment->void_date?->format('m-d-Y') ?? '' }}</td>
                                    <td>{{ $payment->office?->code ?? '' }}</td>
                                    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;">{{ $payment->remark ?? '' }}</td>
                                    <td>
                                        @if($payment->clear_date)
                                            <span class="badge-status" style="padding:1px 4px;border-radius:2px;font-size:9px;font-weight:600;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;">Cleared</span>
                                        @elseif($payment->void_date)
                                            <span class="badge-status" style="padding:1px 4px;border-radius:2px;font-size:9px;font-weight:600;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;">Void</span>
                                        @else
                                            <span class="badge-status" style="padding:1px 4px;border-radius:2px;font-size:9px;font-weight:600;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="empty-row">
                                    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No payments received found.
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

        document.getElementById('btn-delete').disabled = n === 0;

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
       DELETE — only selected records, with confirmation
    ================================================================ */
    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent =
            `You are about to permanently delete ${n} payment(s). This cannot be undone.`;
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
        fetch('{{ route("accounting.payment-received-list.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
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

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    /* ================================================================
       AJAX GRID UPDATE (pagination only — no hard refresh)
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
        // Build URL from base path to avoid url.search='' sync issues
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
       REPLACE GRID CONTENT — shared by AJAX pagination, search, filter
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
    var PINNED_COLS = ['check', 'lock', 'date', 'received_from', 'color'];

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
        fetch('{{ route("accounting.payment-received-list.update-color", "ID") }}'.replace('ID', id), {
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
        fetch('{{ route("accounting.payment-received-list.update-color", "ID") }}'.replace('ID', id), {
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
