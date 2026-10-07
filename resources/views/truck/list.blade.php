<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .nav-tabs-custom { margin-bottom: 8px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 4px; background: #fff; padding: 4px 8px 0 8px; border-radius: 2px 2px 0 0; }
        .nav-tabs-custom .tab-item { padding: 6px 14px; font-size: 11px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; }
        .nav-tabs-custom .tab-item:hover { color: #2563eb; background: #f8fafc; }
        .nav-tabs-custom .tab-item.active { color: #2563eb; font-weight: 700; border-bottom-color: #2563eb; background: #eff6ff; }

        .btn-group { display: inline-flex; gap: 0; border-radius: 2px; overflow: hidden; }
        .btn-group .btn-tool { border-radius: 0; margin: 0; }
        .btn-group .btn-tool:not(:first-child) { border-left: 1px solid rgba(0,0,0,0.1); }
        .btn-group .btn-tool:first-child { border-top-left-radius: 2px; border-bottom-left-radius: 2px; }
        .btn-group .btn-tool:last-child { border-top-right-radius: 2px; border-bottom-right-radius: 2px; }

        #grid-loading { display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 100; align-items: center; justify-content: center; }
        #grid-loading.show { display: flex; }
        #grid-loading .spinner { width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media print {
            th[data-col="check"], td:nth-child(1),
            th[data-col="lock"], td:nth-child(2),
                display: none !important;
            }
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
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

    {{-- MAIN PAGE --}}
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
                <li><span style="color:#333;font-weight:700;">Shipment List</span></li>
            </ul>
        </div>

        <div class="portlet light" style="position:relative;">
            {{-- LOADING SPINNER OVERLAY --}}
            <div id="grid-loading">
                <div class="spinner"></div>
            </div>

            {{-- PORTLET TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject"><i class="fa fa-truck"></i> TRUCK SHIPMENT LIST</span>
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
                    <button class="btn-action-round white" onclick="exportExcel(event)" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:nowrap;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('truck.create') }}" title="New Shipment" target="_blank">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-copy" disabled title="Copy Selected" onclick="copySelected()">
                            <i class="fa fa-files-o"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block" disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)">
                    <a href="javascript:;" id="clear-search-btn" onclick="clearSearch()" style="display:{{ request()->has('search') && request('search') ? 'inline' : 'none' }};font-size:10px;color:#3b82f6;text-decoration:none;cursor:pointer;" title="Clear search">
                        <i class="fa fa-times-circle"></i>
                    </a>
                </div>
            </div>

            {{-- TABLE --}}
            <form id="bulk-form" method="POST" action="{{ route('truck.bulk-delete') }}" style="margin:0;">
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
                                        <th class="sticky-col sticky-col-header" data-col="file_no" style="width:110px;left:50px;cursor:pointer;" onclick="toggleSort('file_no')">
                                            File No. <i class="fa fa-sort" id="sort-file_no"></i>
                                        </th>
                                        <th data-col="post_date" style="width:90px;cursor:pointer;" onclick="toggleSort('post_date')">
                                            Post Date <i class="fa fa-sort" id="sort-post_date"></i>
                                        </th>
                                        <th data-col="customer" style="width:140px;">Customer</th>
                                        <th data-col="trucker" style="width:140px;">Trucker</th>
                                        <th data-col="mbl_no" style="width:120px;cursor:pointer;" onclick="toggleSort('mbl_no')">
                                            MB/L No. <i class="fa fa-sort" id="sort-mbl_no"></i>
                                        </th>
                                        <th data-col="hbl_no" style="width:120px;">HB/L No.</th>
                                        <th data-col="pkg_qty" style="width:70px;text-align:right;cursor:pointer;" onclick="toggleSort('pkg_qty')">
                                            Package <i class="fa fa-sort" id="sort-pkg_qty"></i>
                                        </th>
                                        <th data-col="weight" style="width:80px;text-align:right;cursor:pointer;" onclick="toggleSort('weight_kg')">
                                            Weight <i class="fa fa-sort" id="sort-weight_kg"></i>
                                        </th>
                                        <th data-col="pod" style="width:130px;">Port of Discharge</th>
                                        <th data-col="fdest" style="width:140px;">Final Destination</th>
                                        <th data-col="ar_bal" style="width:100px;text-align:right;">AR Balance</th>
                                        <th data-col="do" style="width:50px;text-align:center;">D/O</th>
                                        <th data-col="action" style="width:30px;"></th>
                                    </tr>

                                    {{-- FILTER ROW --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td data-col="check" class="sticky-col" style="left:0;"></td>
                                        <td data-col="lock" class="sticky-col" style="left:25px;"></td>
                                        <td data-col="file_no" class="sticky-col" style="left:50px;"><input class="filter-input" data-col-idx="2" placeholder="File No..." oninput="applyFilters()"></td>
                                        <td data-col="post_date"><input class="filter-input" data-col-idx="4" placeholder="Post Date (Y-m-d)..." oninput="applyFilters()"></td>
                                        <td data-col="customer"><input class="filter-input" data-col-idx="5" placeholder="Customer..." oninput="applyFilters()"></td>
                                        <td data-col="trucker"><input class="filter-input" data-col-idx="6" placeholder="Trucker..." oninput="applyFilters()"></td>
                                        <td data-col="mbl_no"><input class="filter-input" data-col-idx="7" placeholder="MB/L No..." oninput="applyFilters()"></td>
                                        <td data-col="hbl_no"><input class="filter-input" data-col-idx="8" placeholder="HB/L No..." oninput="applyFilters()"></td>
                                        <td colspan="7"></td>
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

            {{-- PAGINATION --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">
                        @include('truck.partials.list-pagination', ['shipments' => $shipments])
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:10px;color:#64748b;">
                        <select class="input-inline" style="width:60px;" onchange="changePageSize(this.value)">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
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
       STATE PERSISTENCE & FILTER MAPPING
    ================================================================ */
    const STATE_KEY = 'truck_list_state';
    const FILTER_MAP = {
        2: 'filter_file_no', 4: 'filter_post_date', 5: 'filter_customer',
        6: 'filter_trucker', 7: 'filter_mbl_no', 8: 'filter_hbl_no'
    };

    /* ================================================================
       TOOLBAR — checkbox management
    ================================================================ */
    function updateToolbar() {
        const checked  = [...document.querySelectorAll('.row-check:checked')];
        const all      = [...document.querySelectorAll('.row-check')];
        const n        = checked.length;
        const sa       = document.getElementById('select-all');
        if (sa) {
            sa.checked       = n === all.length && all.length > 0;
            sa.indeterminate = n > 0 && n < all.length;
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

    function rowClick(e, row) {
        const skip = ['A', 'INPUT', 'BUTTON', 'I', 'SPAN'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    /* ================================================================
       LOADING OVERLAY
    ================================================================ */
    function showLoading() {
        const el = document.getElementById('grid-loading');
        if (el) el.classList.add('show');
    }
    function hideLoading() {
        const el = document.getElementById('grid-loading');
        if (el) el.classList.remove('show');
    }

    /* ================================================================
       EXCEL EXPORT (AJAX Stream)
    ================================================================ */
    function exportExcel(e) {
        if (e) e.preventDefault();
        
        const url = new URL('{{ route("truck.export-csv") }}');
        const current = new URL(window.location.href);
        ['search','filter_file_no','filter_post_date','filter_customer','filter_trucker','filter_mbl_no','filter_hbl_no','sort','dir'].forEach(p => {
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

    /* ================================================================
       CLEAR SEARCH
    ================================================================ */
    function clearSearch() {
        document.getElementById('quick-search').value = '';
        document.getElementById('clear-search-btn').style.display = 'none';
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.delete('page');
        updateGrid(url.toString());
    }

    /* ================================================================
       COPY
    ================================================================ */
    function copySelected() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        const row = checked[0].closest('tr');
        showToast('info', 'Copying shipment: ' + (row.dataset.file || '') + ' ...');
        setTimeout(() => {
            window.location.href = '{{ route("truck.create") }}?copy=' + row.dataset.id;
        }, 500);
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
                updateGrid(window.location.href);
            } else {
                showToast('error', data.message || 'Failed to delete');
            }
        }).catch(() => showToast('error', 'Failed to delete'));
    }

    /* ================================================================
       BLOCK / UNBLOCK (Dynamic AJAX)
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
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        const lockIcon = row.querySelector('.fa-lock, .fa-unlock');
                        if (lockIcon) {
                            lockIcon.classList.remove('fa-unlock');
                            lockIcon.classList.add('fa-lock');
                            lockIcon.style.color = '#94a3b8';
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
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        const lockIcon = row.querySelector('.fa-lock, .fa-unlock');
                        if (lockIcon) {
                            lockIcon.classList.remove('fa-lock');
                            lockIcon.classList.add('fa-unlock');
                            lockIcon.style.color = '#22c55e';
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
        const row = el.closest('tr');
        const id = row ? row.dataset.id : null;
        
        if (id) {
            const endpoint = locked ? '{{ route("truck.bulk-unblock") }}' : '{{ route("truck.bulk-block") }}';
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids: [id] })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    el.classList.toggle('fa-lock', !locked);
                    el.classList.toggle('fa-unlock', locked);
                    el.style.color = locked ? '#22c55e' : '#94a3b8';
                    el.title = locked ? 'Unlocked' : 'Blocked';
                    showToast('info', locked ? 'Shipment unblocked.' : 'Shipment blocked.');
                }
            }).catch(() => showToast('error', 'Failed to update status'));
        }
    }

    /* ================================================================
       FILTER ROW TOGGLE & RESTORE
    ================================================================ */
    let filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        const row = document.getElementById('filter-row');
        if (row) { row.style.display = filterOpen ? 'table-row' : 'none'; }
        document.getElementById('btn-filter').classList.toggle('active-filter', filterOpen);

        if (filterOpen) {
            restoreFilterRow(new URLSearchParams(window.location.search));
            document.querySelector('#filter-row .filter-input')?.focus();
        } else {
            document.querySelectorAll('#filter-row .filter-input').forEach(i => { i.value = ''; });
            applyFilters();
        }
    }

    function restoreFilterRow(params) {
        document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
            const param = FILTER_MAP[inp.dataset.colIdx];
            inp.value = param ? (params.get(param) || '') : '';
        });
    }

    function autoOpenFilterIfNeeded() {
        const params = new URLSearchParams(window.location.search);
        const hasFilter = Object.values(FILTER_MAP).some(k => params.has(k) && params.get(k));
        if (hasFilter) {
            filterOpen = true;
            const row = document.getElementById('filter-row');
            if (row) row.style.display = 'table-row';
            document.getElementById('btn-filter').classList.add('active-filter');
            restoreFilterRow(params);
        }
    }

    /* ================================================================
       AJAX GRID UPDATE
    ================================================================ */
    async function updateGrid(url, pushState = true) {
        showLoading();
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();

            if (data.html) {
                document.getElementById('grid-body').innerHTML = data.html;
            }
            if (data.pagination) {
                document.getElementById('pagination-container').innerHTML = data.pagination;
            }
            if (data.total !== undefined) {
                document.getElementById('stat-total').textContent = data.total;
                document.getElementById('stat-first').textContent = data.from || 0;
                document.getElementById('stat-last').textContent = data.to || 0;
            }

            const urlObj = new URL(url, window.location.origin);
            const sortField = urlObj.searchParams.get('sort') || 'created_at';
            const sortDir = urlObj.searchParams.get('dir') || 'desc';

            document.querySelectorAll('[id^="sort-"]').forEach(i => i.className = 'fa fa-sort');
            const sortIcon = document.getElementById('sort-' + sortField);
            if (sortIcon) {
                sortIcon.className = 'fa ' + (sortDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');
            }

            const searchVal = urlObj.searchParams.get('search') || '';
            const clearBtn = document.getElementById('clear-search-btn');
            if (clearBtn) clearBtn.style.display = searchVal ? 'inline' : 'none';

            if (filterOpen) restoreFilterRow(urlObj.searchParams);

            if (pushState) {
                window.history.pushState({}, '', url);
            }
            try { sessionStorage.setItem(STATE_KEY, url); } catch(e) {}

            updateToolbar();
            loadColumnConfig();
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update grid');
        } finally {
            hideLoading();
        }
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    window.addEventListener('popstate', function() {
        updateGrid(window.location.href, false);
    });

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    function toggleSort(field) {
        const url = new URL(window.location.href);
        let curSort = url.searchParams.get('sort') || 'created_at';
        let curDir = url.searchParams.get('dir') || 'desc';

        if (curSort === field) {
            curDir = curDir === 'asc' ? 'desc' : 'asc';
        } else {
            curSort = field;
            curDir = 'asc';
        }

        url.searchParams.set('sort', curSort);
        url.searchParams.set('dir', curDir);
        url.searchParams.delete('page');
        updateGrid(url.toString());
    }

    function refreshGrid() {
        updateGrid(window.location.href);
    }

    function changePageSize(size) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', size);
        url.searchParams.delete('page');
        updateGrid(url.toString());
    }

    let searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const url = new URL(window.location.href);
            if (!q) url.searchParams.delete('search'); else url.searchParams.set('search', q);
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 300);
    }

    let filterDebounce;
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            const url = new URL(window.location.href);
            const search = url.searchParams.get('search') || '';
            const sort = url.searchParams.get('sort') || 'created_at';
            const dir = url.searchParams.get('dir') || 'desc';

            url.search = '';
            if (search) url.searchParams.set('search', search);
            url.searchParams.set('sort', sort);
            url.searchParams.set('dir', dir);

            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                const v = inp.value.trim();
                if (!v) return;
                const param = FILTER_MAP[inp.dataset.colIdx];
                if (param) url.searchParams.set(param, v);
            });

            updateGrid(url.toString());
        }, 300);
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    const PINNED_COLS = ['check', 'lock', 'file_no', 'color'];
    const CONFIG_STORAGE_KEY = 'truck_list_column_config';

    function loadColumnConfig() {
        try {
            const saved = localStorage.getItem(CONFIG_STORAGE_KEY);
            if (!saved) return;
            const hidden = JSON.parse(saved);
            document.querySelectorAll('#header-row th[data-col]').forEach(th => {
                const col = th.dataset.col;
                if (PINNED_COLS.includes(col)) return;
                if (hidden.includes(col)) th.style.display = 'none';
            });
            document.querySelectorAll('#grid-body tr').forEach(row => {
                document.querySelectorAll('#header-row th[data-col]').forEach((th, i) => {
                    const col = th.dataset.col;
                    if (PINNED_COLS.includes(col)) return;
                    if (hidden.includes(col)) {
                        const cells = row.querySelectorAll('td, th');
                        if (cells[i]) cells[i].style.display = 'none';
                    }
                });
            });
            document.querySelectorAll('#filter-row td[data-col]').forEach(td => {
                const col = td.dataset.col;
                if (PINNED_COLS.includes(col)) return;
                if (hidden.includes(col)) td.style.display = 'none';
            });
        } catch (e) {}
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
        const hidden = getHiddenColumns();
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (PINNED_COLS.includes(th.dataset.col)) return;
            const label = document.createElement('label');
            const cb    = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = !hidden.includes(th.dataset.col);
            cb.onchange = () => toggleColumn(th.dataset.col, cb.checked);
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim());
            container.appendChild(label);
        });
    }

    function getHiddenColumns() {
        const hidden = [];
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (PINNED_COLS.includes(th.dataset.col)) return;
            if (th.style.display === 'none') hidden.push(th.dataset.col);
        });
        return hidden;
    }

    function saveColumnConfig() {
        try {
            localStorage.setItem(CONFIG_STORAGE_KEY, JSON.stringify(getHiddenColumns()));
        } catch (e) {}
    }

    function toggleColumn(colName, show) {
        const th  = document.querySelector(`#header-row th[data-col="${colName}"]`);
        if (!th) return;
        const idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr').forEach(row => {
            const cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
        const filterTd = document.querySelector(`#filter-row td[data-col="${colName}"]`);
        if (filterTd) filterTd.style.display = show ? '' : 'none';
        saveColumnConfig();
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
    const COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' },
    ];

    let _colorShipmentId = null;

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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
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
        fetch('{{ route("truck.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
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
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `<i class="fa fa-${icons[type] || 'info-circle'}"></i> <span>${msg}</span>`;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    /* ================================================================
       INIT & SESSION RECOVERY
    ================================================================ */
    (function() {
        const currentUrl = new URL(window.location.href);
        const hasParams = [...currentUrl.searchParams.keys()].length > 0;
        let savedState = null;
        try { savedState = sessionStorage.getItem(STATE_KEY); } catch(e) {}

        if (!hasParams && savedState) {
            updateGrid(savedState, false);
        } else {
            const sort = currentUrl.searchParams.get('sort') || 'created_at';
            const dir = currentUrl.searchParams.get('dir') || 'desc';
            const icon = document.getElementById('sort-' + sort);
            if (icon) { icon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc'); }
            loadColumnConfig();
            autoOpenFilterIfNeeded();
        }
    })();

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif
    </script>
    @endpush
</x-layout>
