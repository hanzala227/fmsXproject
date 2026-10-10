<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .nav-tabs-custom { margin-bottom: 8px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 4px; background: #fff; padding: 4px 8px 0 8px; border-radius: 2px 2px 0 0; }
        .nav-tabs-custom .tab-item { padding: 6px 14px; font-size: 11px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; }
        .nav-tabs-custom .tab-item:hover { color: #2563eb; background: #f8fafc; }
        .nav-tabs-custom .tab-item.active { color: #2563eb; font-weight: 700; border-bottom-color: #2563eb; background: #eff6ff; }
    </style>
    @endpush

    {{-- ═══════ TOAST CONTAINER ═══════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════ DELETE CONFIRM MODAL ═══════ --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Shipment(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════ COLOR PICKER MODAL ═══════ --}}
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

    {{-- ═══════ MAIN PAGE ═══════ --}}
    <div class="page-content">
        {{-- TOP TABS --}}
        <div class="nav-tabs-custom">
            <a href="{{ route('truck.create') }}" class="tab-item {{ request()->routeIs('truck.create') ? 'active' : '' }}">
                <i class="fa fa-plus-circle"></i> New Shipment
            </a>
            <a href="{{ route('truck.index') }}" class="tab-item {{ request()->routeIs('truck.index') ? 'active' : '' }}">
                <i class="fa fa-list"></i> Shipment List
            </a>
            <a href="{{ route('truck.my-shipment-list') }}" class="tab-item {{ request()->routeIs('truck.my-shipment-list') ? 'active' : '' }}">
                <i class="fa fa-user"></i> My Shipment List
            </a>
        </div>

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Truck <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">My Shipment List</span></li>
            </ul>
        </div>

        <div class="portlet light">

            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">MY SHIPMENT LIST</span>
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
                    <button class="btn-action-round white" onclick="printReport()" title="Print this page">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <button class="btn-action-round white" onclick="exportExcel(event)" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('truck.create') }}" title="New Shipment" target="_blank">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-copy"   disabled title="Copy Selected (select 1 row)" onclick="copySelected()">
                            <i class="fa fa-files-o"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button class="btn-tool" title="Refresh" onclick="updateGrid(window.location.href)">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;margin:0;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" name="search" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)">
                    @if(request()->has('search'))
                        <a href="#" onclick="quickSearch('');document.getElementById('quick-search').value=''" style="font-size:10px;color:#3b82f6;text-decoration:none;">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- ── BULK-ACTION FORM + TABLE ── --}}
            <form id="bulk-form" method="POST" action="{{ route('truck.bulk-delete') }}" style="margin:0;">
                @csrf
                @method('DELETE')
                <div class="portlet-body">
                    <div class="grid-container">
                        <div class="grid-wrapper">
                            <table class="grid-table" id="main-grid">
                                <thead>
                                    {{-- ── HEADER ROW ── --}}
                                    <tr id="header-row">
                                        <th class="sticky-col sticky-col-header" data-col="check"   style="width:25px;text-align:center;">
                                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                        </th>
                                        <th class="sticky-col sticky-col-header" data-col="lock"    style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                        <th class="sticky-col sticky-col-header" data-col="file_no" style="width:110px;left:50px;">File No.</th>
                                        <th data-col="post_date" style="width:90px;cursor:pointer;" onclick="sortGrid('post_date')">Post Date <i class="fa fa-sort"></i></th>
                                        <th data-col="customer"  style="width:140px;">Customer</th>
                                        <th data-col="trucker"   style="width:140px;">Trucker</th>
                                        <th data-col="mbl_no"    style="width:120px;cursor:pointer;" onclick="sortGrid('mbl_no')">MB/L No. <i class="fa fa-sort"></i></th>
                                        <th data-col="hbl_no"    style="width:120px;">HB/L No.</th>
                                        <th data-col="pkg_qty"   style="width:70px;text-align:right;cursor:pointer;" onclick="sortGrid('pkg_qty')">Package <i class="fa fa-sort"></i></th>
                                        <th data-col="weight"    style="width:80px;text-align:right;cursor:pointer;" onclick="sortGrid('weight_kg')">Weight <i class="fa fa-sort"></i></th>
                                        <th data-col="pod"       style="width:130px;">Port of Discharge</th>
                                        <th data-col="fdest"     style="width:140px;">Final Destination</th>
                                        <th data-col="ar_bal"    style="width:100px;text-align:right;">AR Balance</th>
                                        <th data-col="do"        style="width:50px;text-align:center;">D/O</th>
                                        <th data-col="action"    style="width:30px;"></th>
                                    </tr>

                                    {{-- ── FILTER ROW (hidden by default) ── --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td class="sticky-col" style="left:0;"></td>
                                        <td class="sticky-col" style="left:25px;"></td>
                                        <td class="sticky-col" style="left:50px;"><input class="filter-input" data-col-idx="2" placeholder="File No..." oninput="applyFilters()"></td>
                                        <td class="sticky-col" style="left:160px;"></td>
                                        <td><input class="filter-input" data-col-idx="4" placeholder="Post Date..." oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="5" placeholder="Customer..."  oninput="applyFilters()"></td>
                                        <td colspan="10"></td>
                                    </tr>
                                </thead>

                                <tbody id="grid-body">
                                    @include('truck.partials.list-rows', ['shipments' => $shipments])
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            {{-- ── PAGINATION ── --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $shipments->links() }}</div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:10px;color:#64748b;">
                        <select class="input-inline" style="width:60px;" onchange="changePageSize(this.value)">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span>records</span>
                        Showing <span id="stat-first">{{ $shipments->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $shipments->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $shipments->total() }}</span> records
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
        if (sa) {
            sa.checked        = n === all.length && all.length > 0;
            sa.indeterminate  = n > 0 && n < all.length;
        }

        const delBtn = document.getElementById('btn-delete');
        const copyBtn = document.getElementById('btn-copy');
        const blockBtn = document.getElementById('btn-block');
        const unblockBtn = document.getElementById('btn-unblock');
        if (delBtn) delBtn.disabled = n === 0;
        if (copyBtn) copyBtn.disabled = n !== 1;
        if (blockBtn) blockBtn.disabled = n === 0;
        if (unblockBtn) unblockBtn.disabled = n === 0;

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

    /* ================================================================
       ROW CLICK — click row to toggle checkbox
    ================================================================ */
    function rowClick(e, row) {
        const skip = ['A', 'INPUT', 'BUTTON', 'I'];
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
            `You are about to permanently delete ${n} shipment(s). This cannot be undone.`;
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
        fetch('{{ route("truck.bulk-delete") }}', {
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
                setTimeout(() => updateGrid(window.location.href), 600);
            } else {
                showToast('error', data.message || 'Failed to delete');
            }
        }).catch(() => showToast('error', 'Failed to delete'));
    }

    /* ================================================================
       COPY — navigate to create page with ?copy=id
    ================================================================ */
    function copySelected() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        const row = checked[0].closest('tr');
        showToast('info', 'Copying shipment: ' + (row.dataset.file || '') + ' ...');
        setTimeout(() => {
            window.location.href = '{{ route("truck.create") }}?copy=' + row.dataset.id;
        }, 600);
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected() {
        const ids = getSelectedIds();
        if (!ids.length) return;
        
        showToast('info', 'Blocking shipment(s)...');
        
        fetch('{{ route("truck.bulk-block") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids })
        })
        .then(r => r.json())
        .then(d => { 
            if (d.success) { 
                showToast('success', d.message);
                // Update lock icons for blocked shipments
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        const lockIcon = row.querySelector('.fa-lock, .fa-unlock');
                        if (lockIcon) {
                            lockIcon.classList.remove('fa-unlock');
                            lockIcon.classList.add('fa-lock');
                            lockIcon.style.color = '#ef4444'; // Red for blocked
                            lockIcon.title = 'Blocked';
                        }
                    }
                });
            } else {
                showToast('error', d.message);
            }
        })
        .catch(() => showToast('error', 'Failed to block shipment(s).'));
    }
    
    function unblockSelected() {
        const ids = getSelectedIds();
        if (!ids.length) return;
        
        showToast('info', 'Unblocking shipment(s)...');
        
        fetch('{{ route("truck.bulk-unblock") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids })
        })
        .then(r => r.json())
        .then(d => { 
            if (d.success) { 
                showToast('success', d.message);
                // Update lock icons for unblocked shipments
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        const lockIcon = row.querySelector('.fa-lock, .fa-unlock');
                        if (lockIcon) {
                            lockIcon.classList.remove('fa-lock');
                            lockIcon.classList.add('fa-unlock');
                            lockIcon.style.color = '#22c55e'; // Green for unblocked
                            lockIcon.title = 'Unlocked';
                        }
                    }
                });
            } else {
                showToast('error', d.message);
            }
        })
        .catch(() => showToast('error', 'Failed to unblock shipment(s).'));
    }

    /* ================================================================
       LOCK ICON TOGGLE (per-row visual)
    ================================================================ */
    function toggleLock(el) {
        const locked = el.classList.contains('fa-lock');
        el.classList.toggle('fa-lock',   !locked);
        el.classList.toggle('fa-unlock',  locked);
        el.style.color = locked ? '#22c55e' : '#94a3b8';
        el.title = locked ? 'Unlock' : 'Lock';
        showToast('info', locked ? 'Row unlocked.' : 'Row locked.');
    }

    /* ================================================================
       FILTER ROW TOGGLE
    ================================================================ */
    function toggleFilter() {
        var filterRow = document.getElementById('filter-row');
        if (!filterRow) return;
        var isVisible = filterRow.style.display === 'table-row';
        filterRow.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);

        if (!isVisible) {
            // Load filters from URL params (row was hidden, now showing)
            const urlParams = new URLSearchParams(window.location.search);
            document.querySelectorAll('.filter-input').forEach(inp => {
                const idx = parseInt(inp.dataset.colIdx);
                if (idx === 2) inp.value = urlParams.get('filter_file_no') || '';
                else if (idx === 4) inp.value = urlParams.get('filter_post_date') || '';
                else if (idx === 5) inp.value = urlParams.get('filter_customer') || '';
            });
            document.querySelector('.filter-input')?.focus();
        } else {
            // clear filters when closing
            document.querySelectorAll('.filter-input').forEach(i => { i.value = ''; });
            applyFilters();
        }
    }

    /* ================================================================
        AJAX GRID UPDATE
    ================================================================ */
    async function updateGrid(url) {
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

            const stats = doc.querySelector('.portlet-tool.bottom div:last-child');
            if (stats) {
                const text = stats.textContent;
                const matches = text.match(/\d+/g);
                if (matches && matches.length >= 3) {
                    document.getElementById('stat-first').textContent = matches[0];
                    document.getElementById('stat-last').textContent = matches[1];
                    document.getElementById('stat-total').textContent = matches[2];
                }
            }

            updateToolbar();
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update grid');
        }
    }

    // Wire pagination links to use AJAX instead of full page loads
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    var searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const url = new URL(window.location.href);
            if (!q) url.searchParams.delete('search'); else url.searchParams.set('search', q);
            updateGrid(url.toString());
        }, 300);
    }

    var filterDebounce;
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            const inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            var url = new URL(window.location.href);
            url.search = ''; // clear all existing params

            const filterMap = {
                2: 'filter_file_no', 4: 'filter_post_date', 5: 'filter_customer'
            };

            inputs.forEach(inp => {
                const v = inp.value.trim();
                if (!v) return;
                const param = filterMap[inp.dataset.colIdx];
                if (param) url.searchParams.set(param, v);
            });

            updateGrid(url.toString());
        }, 300);
    }

    function sortGrid(field) {
        const url = new URL(window.location.href);
        const currentDir = url.searchParams.get('dir') || 'desc';
        const currentField = url.searchParams.get('sort') || '';
        const newDir = (field === currentField && currentDir === 'asc') ? 'desc' : 'asc';
        url.searchParams.set('sort', field);
        url.searchParams.set('dir', newDir);
        updateGrid(url.toString());
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'file_no', 'color'];
    var COL_CONFIG_KEY = 'truck_my_shipment_cols';

    function loadColumnConfig() {
        try {
            const saved = localStorage.getItem(COL_CONFIG_KEY);
            if (!saved) return;
            const hidden = JSON.parse(saved);
            if (!Array.isArray(hidden)) return;
            hidden.forEach(colName => {
                const th = document.querySelector(`#header-row th[data-col="${colName}"]`);
                if (!th || PINNED_COLS.includes(colName)) return;
                const idx = [...th.parentElement.children].indexOf(th);
                th.style.display = 'none';
                document.querySelectorAll('#grid-body tr, #filter-row').forEach(row => {
                    if (!row) return;
                    const cell = row.querySelectorAll('td, th')[idx];
                    if (cell) cell.style.display = 'none';
                });
            });
        } catch (e) { /* ignore */ }
    }

    function saveColumnConfig() {
        const hidden = [];
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (!PINNED_COLS.includes(th.dataset.col) && th.style.display === 'none') {
                hidden.push(th.dataset.col);
            }
        });
        try { localStorage.setItem(COL_CONFIG_KEY, JSON.stringify(hidden)); } catch (e) { /* ignore */ }
    }

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
            cb.onchange = () => { toggleColumn(th.dataset.col, cb.checked); saveColumnConfig(); };
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
            if (!row) return;
            const cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    // Close config on outside click
    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn   = document.getElementById('btn-config');
        if (panel && panel.style.display !== 'none' && !panel.contains(e.target) && btn && !btn.contains(e.target)) {
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

    var _colorShipmentId = null;

    function openColorPicker(id, currentColor) {
        _colorShipmentId = id;
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
        const id = _colorShipmentId;
        fetch('{{ route("truck.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#shipment-row-${id} .color-mark`);
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            }
        }).catch(() => showToast('error', 'Failed to update color'));
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorShipmentId = null;
    }

    function clearColor() {
        const id = _colorShipmentId;
        if (!id) return;
        fetch('{{ route("truck.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#shipment-row-${id} .color-mark`);
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            }
        }).catch(() => showToast('error', 'Failed to clear color'));
        closeColorPicker();
    }

    /* ================================================================
       PAGE SIZE
    ================================================================ */
    function exportExcel(e) {
        if (e) e.preventDefault();
        
        const url = new URL('{{ route("truck.export-csv") }}');
        const current = new URL(window.location.href);
        ['search','filter_file_no','filter_post_date','filter_customer','sort','dir'].forEach(p => {
            if (current.searchParams.has(p)) url.searchParams.set(p, current.searchParams.get(p));
        });
        
        showToast('info', 'Preparing Excel export...');
        
        fetch(url.toString())
            .then(response => {
                if (!response.ok) throw new Error('Export failed');
                return response.blob();
            })
            .then(blob => {
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = 'truck-shipments-' + new Date().toISOString().split('T')[0] + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(downloadUrl);
                showToast('success', 'Excel file downloaded successfully');
            })
            .catch(err => {
                console.error(err);
                showToast('error', 'Failed to export Excel file');
            });
        
        return false;
    }
    
    function printReport() {
        const url = new URL('{{ route("truck.my-shipment-list-print") }}');
        const current = new URL(window.location.href);
        ['search','filter_file_no','filter_post_date','filter_customer','sort','dir'].forEach(p => {
            if (current.searchParams.has(p)) url.searchParams.set(p, current.searchParams.get(p));
        });
        
        showToast('info', 'Opening print view...');
        window.open(url.toString(), '_blank');
    }

    function changePageSize(size) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', size);
        url.searchParams.delete('page');
        updateGrid(url.toString());
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

    // Init: load column config from localStorage
    document.addEventListener('DOMContentLoaded', loadColumnConfig);
    </script>
    @endpush
</x-layout>
