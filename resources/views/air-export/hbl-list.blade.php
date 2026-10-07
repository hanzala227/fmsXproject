<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        /* Button Group Styling - Better Alignment */
        .btn-group {
            display: inline-flex;
            gap: 0;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .btn-group .btn-tool:not(:first-child) {
            border-left: 1px solid rgba(255,255,255,0.2);
        }
        
        .btn-group .btn-tool {
            border-radius: 0;
            margin: 0;
        }
        
        .btn-group .btn-tool:first-child {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        
        .btn-group .btn-tool:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        
        .portlet-tool {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        /* Mobile Responsive Enhancements */
        @media (max-width: 768px) {
            .page-content { 
                padding: 2px !important; 
                overflow-x: hidden !important;
            }
            .portlet.light { 
                margin: 0 !important; 
                border-radius: 0 !important; 
                overflow: hidden !important;
            }
            
            .portlet-title { 
                flex-direction: column !important; 
                align-items: flex-start !important; 
                padding: 6px !important;
                gap: 6px;
            }
            .portlet-title .caption { width: 100%; }
            .portlet-title .actions { 
                width: 100%; 
                flex-wrap: wrap; 
                gap: 3px !important;
            }
            
            .portlet-tool { 
                flex-direction: column !important; 
                align-items: flex-start !important; 
                padding: 6px !important;
                gap: 6px !important;
            }
            .portlet-tool > div { width: 100%; }
            
            .btn-group { 
                width: 100%; 
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            
            .grid-container { 
                width: 100% !important;
                overflow: hidden !important;
            }
            
            .grid-wrapper { 
                width: 100% !important;
                height: calc(100vh - 350px) !important;
                min-height: 200px !important;
                overflow-x: auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }
            
            .grid-table { 
                font-size: 8px !important;
                min-width: 1600px !important;
            }
            
            .grid-table th, .grid-table td { 
                padding: 2px 4px !important;
                height: 22px !important;
            }
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ═══════════ TOAST CONTAINER ═══════════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════ DELETE CONFIRM MODAL ═══════════ --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon" style="color:#ef4444;"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete HBL(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════ COLOR PICKER MODAL ═══════════ --}}
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

    {{-- ═══════════ CHANGE USER MODAL ═══════════ --}}
    <div class="overlay" id="change-user-overlay" onclick="if(event.target===this) closeChangeUser()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-user" style="color:#3b82f6;"></i> <span id="change-user-title">Change Sales Person</span></div>
                <button class="modal-close" onclick="closeChangeUser()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="min-width:300px;">
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Select User</label>
                    <select id="change-user-select" style="height:30px;font-size:11px;width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:0 6px;">
                        <option value="">-- Select --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-tool" onclick="closeChangeUser()">Cancel</button>
                    <button class="btn-tool green" onclick="executeChangeUser()">Update</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ MAIN PAGE ═══════════ --}}
    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Air Export <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">HAWB List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">HAWB List Overview</span>
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
                    <button class="btn-action-round white" onclick="exportCsv()" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <button class="btn-tool green" onclick="window.location.href='{{ route('air-export.create') }}'" title="Add new Air Export">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                    <div class="btn-group">
                        <select class="select-tool" id="bulk-sales-select" disabled onchange="onBulkSalesChange(this)">
                            <option value="">Change Sales</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="btn-group">
                        <select class="select-tool" id="bulk-op-select" disabled onchange="onBulkOpChange(this)">
                            <option value="">Change OP</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search…" oninput="quickSearch(this.value)" value="{{ request('search') }}">
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check"  style="width:25px;text-align:center;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="hawb_no" style="width:120px;left:50px;">HAWB No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="file_no"  style="width:120px;left:163px;">File No.</th>
                                    <th data-col="customer"   style="width:150px;">Customer</th>
                                    <th data-col="shipper"    style="width:150px;">Shipper</th>
                                    <th data-col="consignee"  style="width:150px;">Consignee</th>
                                    <th data-col="departure"  style="width:120px;">Departure</th>
                                    <th data-col="destination" style="width:120px;">Destination</th>
                                    <th data-col="gw"         style="width:70px;text-align:right;">G.W (KG)</th>
                                    <th data-col="cw"         style="width:70px;text-align:right;">C.W (KG)</th>
                                    <th data-col="sales"      style="width:90px;">Sales</th>
                                    <th data-col="op"         style="width:90px;">OP</th>
                                    <th data-col="created_at" style="width:100px;">Created</th>
                                </tr>

                                {{-- ── FILTER ROW ── --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;background:#eff6ff;"></td>
                                    <td class="sticky-col" style="left:25px;background:#eff6ff;"></td>
                                    <td class="sticky-col" style="left:50px;background:#eff6ff;"><input class="filter-input" data-col-idx="2" placeholder="HAWB…" oninput="applyFilters()" style="width:100%;"></td>
                                    <td class="sticky-col" style="left:170px;background:#eff6ff;"></td>
                                    <td class="sticky-col" style="left:163px;background:#eff6ff;"><input class="filter-input" data-col-idx="4" placeholder="File…" oninput="applyFilters()" style="width:100%;"></td>
                                    <td><input class="filter-input" data-col-idx="5"  placeholder="Customer…" oninput="applyFilters()" style="width:100%;"></td>
                                    <td><input class="filter-input" data-col-idx="6"  placeholder="Shipper…"   oninput="applyFilters()" style="width:100%;"></td>
                                    <td><input class="filter-input" data-col-idx="7"  placeholder="Consignee…" oninput="applyFilters()" style="width:100%;"></td>
                                    <td><input class="filter-input" data-col-idx="8"  placeholder="Dep…"       oninput="applyFilters()" style="width:100%;"></td>
                                    <td><input class="filter-input" data-col-idx="9"  placeholder="Dest…"      oninput="applyFilters()" style="width:100%;"></td>
                                    <td colspan="5"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                            @forelse($hbls as $hbl)
                                @php
                                    $isBlocked = $hbl->is_blocked ?? false;
                                    $statusLabel = $isBlocked ? 'Blocked' : 'Open';
                                    $badgeClass = $isBlocked ? 'bg-red' : 'bg-green';
                                @endphp
                                <tr id="hbl-row-{{ $hbl->id }}"
                                    data-id="{{ $hbl->id }}"
                                    data-hawb="{{ $hbl->hawb_no }}"
                                    onclick="rowClick(event, this)">
                                    <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="row-check" value="{{ $hbl->id }}" onchange="updateToolbar()">
                                    </td>
                                    <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <i class="fa {{ $isBlocked ? 'fa-lock' : 'fa-unlock' }}" 
                                           style="color:{{ $isBlocked ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
                                           title="{{ $isBlocked ? 'Blocked' : 'Unlocked' }}"></i>
                                    </td>
                                    <td class="sticky-col" style="left:50px;font-weight:600;" onclick="event.stopPropagation()">
                                        <a href="/air-export/{{ $hbl->air_export_id }}/edit" class="col-link">{{ $hbl->hawb_no ?: '--' }}</a>
                                    </td>
                                    <td class="sticky-col" style="left:163px;">
                                        <a href="/air-export/{{ $hbl->air_export_id }}/edit" class="col-link">{{ $hbl->airExport->file_no ?? '--' }}</a>
                                    </td>
                                    <td>{{ $hbl->customer->name ?? '--' }}</td>
                                    <td>{{ $hbl->shipper->name ?? '--' }}</td>
                                    <td>{{ $hbl->consignee->name ?? '--' }}</td>
                                    <td>{{ $hbl->airExport->depPort->name ?? '--' }}</td>
                                    <td>{{ $hbl->airExport->dstPort->name ?? '--' }}</td>
                                    <td style="text-align:right;">{{ $hbl->gross_weight ? number_format($hbl->gross_weight, 2) : '0.00' }}</td>
                                    <td style="text-align:right;">{{ $hbl->chargeable_weight ? number_format($hbl->chargeable_weight, 2) : '0.00' }}</td>
                                    <td>{{ $hbl->salesPerson->name ?? '--' }}</td>
                                    <td>{{ $hbl->op->name ?? '--' }}</td>
                                    <td>{{ $hbl->created_at ? $hbl->created_at->format('m-d-Y') : '--' }}</td>
                                </tr>
                            @empty
                                <tr id="empty-row">
                                    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No HAWBs found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── PAGINATION ── --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $hbls->links('vendor.pagination.custom') }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $hbls->firstItem() ?? 0 }}</span> &ndash; <span id="stat-last">{{ $hbls->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $hbls->total() }}</span> records
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var searchTimer, filterTimer;
    var _changeMode = 'sales';
    var _colorHblId = null;

    /* ================================================================
       TOOLBAR — checkbox management
    ================================================================ */
    function updateToolbar() {
        var checked  = [...document.querySelectorAll('.row-check:checked')];
        var all      = [...document.querySelectorAll('.row-check')];
        var n        = checked.length;
        var sa       = document.getElementById('select-all');
        sa.checked        = n === all.length && all.length > 0;
        sa.indeterminate  = n > 0 && n < all.length;

        document.getElementById('btn-delete').disabled   = n === 0;
        document.getElementById('btn-block').disabled    = n === 0;
        document.getElementById('btn-unblock').disabled  = n === 0;
        document.getElementById('bulk-sales-select').disabled = n === 0;
        document.getElementById('bulk-op-select').disabled    = n === 0;

        var badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent   = n + ' selected';

        document.querySelectorAll('#grid-body tr[data-id]').forEach(function(row) {
            var cb = row.querySelector('.row-check');
            row.classList.toggle('row-selected', cb && cb.checked);
        });
    }

    function toggleSelectAll(el) {
        document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = el.checked; });
        updateToolbar();
    }

    function rowClick(e, row) {
        var skip = ['A', 'INPUT', 'BUTTON', 'I', 'SELECT'];
        if (skip.indexOf(e.target.tagName) >= 0) return;
        var cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(function(cb) { return cb.value; });
    }

    /* ================================================================
       AJAX GRID UPDATE
    ================================================================ */
    async function updateGrid(url) {
        try {
            var response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            var text = await response.text();
            var parser = new DOMParser();
            var doc = parser.parseFromString(text, 'text/html');

            var newBody = doc.getElementById('grid-body');
            var newPagination = doc.getElementById('pagination-container');

            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            ['stat-first', 'stat-last', 'stat-total'].forEach(function(id) {
                var el = doc.getElementById(id);
                if (el) document.getElementById(id).textContent = el.textContent;
            });

            updateToolbar();
        } catch (e) {
            showToast('error', 'Failed to update grid');
        }
    }

    function quickSearch(val) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var q = val.trim();
            if (!q) url.searchParams.delete('search');
            else url.searchParams.set('search', q);
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 300);
    }

    var FILTER_MAP = {
        2: 'filter_hawb', 4: 'filter_file_no', 5: 'filter_customer',
        6: 'filter_shipper', 7: 'filter_consignee', 8: 'filter_dep', 9: 'filter_dst'
    };

    function applyFilters() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            inputs.forEach(function(inp) {
                var key = FILTER_MAP[inp.dataset.colIdx];
                if (key) {
                    var val = inp.value.trim();
                    if (val) url.searchParams.set(key, val);
                    else url.searchParams.delete(key);
                }
            });
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 300);
    }

    /* ================================================================
       FILTER ROW TOGGLE
    ================================================================ */
    var filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        document.getElementById('filter-row').style.display = filterOpen ? 'table-row' : 'none';
        document.getElementById('btn-filter').classList.toggle('active', filterOpen);
        if (!filterOpen) {
            document.querySelectorAll('.filter-input').forEach(function(i) { i.value = ''; });
            applyFilters();
        }
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'hawb_no', 'color', 'file_no'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        var open  = panel.style.display === 'none';
        panel.style.display = open ? 'block' : 'none';
        document.getElementById('btn-config').classList.toggle('active', open);
        if (open) buildConfigPanel();
    }

    function buildConfigPanel() {
        var container = document.getElementById('col-toggles');
        container.innerHTML = '';
        document.querySelectorAll('#header-row th[data-col]').forEach(function(th) {
            if (PINNED_COLS.indexOf(th.dataset.col) >= 0) return;
            var label = document.createElement('label');
            var cb    = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.onchange = function() { toggleColumn(th.dataset.col, cb.checked); };
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim());
            container.appendChild(label);
        });
    }

    function toggleColumn(colName, show) {
        var th  = document.querySelector('#header-row th[data-col="' + colName + '"]');
        var idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(function(row) {
            var cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn   = document.getElementById('btn-config');
        if (panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ================================================================
       DELETE
    ================================================================ */
    function confirmDelete() {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent = 'You are about to permanently delete ' + n + ' HBL(s).';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        fetch('/air-export/hbl-bulk-delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { showToast('success', d.message); updateGrid(window.location.href); }
            else showToast('error', d.message || 'Delete failed.');
        })
        .catch(function() { showToast('error', 'Delete failed.'); });
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        fetch('/air-export/hbl-bulk-block', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                // Update lock icons for blocked HBLs
                ids.forEach(function(id) {
                    var row = document.getElementById('hbl-row-' + id);
                    if (row) {
                        var lockCell = row.querySelector('td:nth-child(2)');
                        if (lockCell) {
                            lockCell.innerHTML = '<i class="fa fa-lock" style="color:#94a3b8;cursor:pointer;font-size:10px;" title="Blocked"></i>';
                        }
                        // Uncheck the checkbox
                        var checkbox = row.querySelector('.row-check');
                        if (checkbox) checkbox.checked = false;
                    }
                });
                updateToolbar();
                showToast('success', d.message);
            } else {
                showToast('error', d.message || 'Block failed.');
            }
        })
        .catch(function() { showToast('error', 'Block failed.'); });
    }
    
    function unblockSelected() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        fetch('/air-export/hbl-bulk-unblock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                // Update lock icons for unblocked HBLs
                ids.forEach(function(id) {
                    var row = document.getElementById('hbl-row-' + id);
                    if (row) {
                        var lockCell = row.querySelector('td:nth-child(2)');
                        if (lockCell) {
                            lockCell.innerHTML = '<i class="fa fa-unlock" style="color:#22c55e;cursor:pointer;font-size:10px;" title="Unlocked"></i>';
                        }
                        // Uncheck the checkbox
                        var checkbox = row.querySelector('.row-check');
                        if (checkbox) checkbox.checked = false;
                    }
                });
                updateToolbar();
                showToast('success', d.message);
            } else {
                showToast('error', d.message || 'Unblock failed.');
            }
        })
        .catch(function() { showToast('error', 'Unblock failed.'); });
    }

    /* ================================================================
       CHANGE SALES / OP (modal)
    ================================================================ */
    function onBulkSalesChange(el) {
        var val = el.value;
        if (!val) return;
        _changeMode = 'sales';
        document.getElementById('change-user-title').textContent = 'Change Sales Person';
        document.getElementById('change-user-overlay').classList.add('open');
        document.getElementById('change-user-select').value = val;
        el.value = '';
    }
    function onBulkOpChange(el) {
        var val = el.value;
        if (!val) return;
        _changeMode = 'op';
        document.getElementById('change-user-title').textContent = 'Change OP';
        document.getElementById('change-user-overlay').classList.add('open');
        document.getElementById('change-user-select').value = val;
        el.value = '';
    }
    function closeChangeUser() { document.getElementById('change-user-overlay').classList.remove('open'); }
    function executeChangeUser() {
        var userId = document.getElementById('change-user-select').value;
        if (!userId) { showToast('error', 'Please select a user.'); return; }
        var ids = getSelectedIds();
        if (!ids.length) { closeChangeUser(); return; }
        
        // Get the selected user name for display
        var userSelect = document.getElementById('change-user-select');
        var userName = userSelect.options[userSelect.selectedIndex].text;
        
        var url = _changeMode === 'sales' ? '/air-export/hbl-bulk-change-sales' : '/air-export/hbl-bulk-change-op';
        var body = { ids: ids };
        if (_changeMode === 'sales') body.sales_person_id = userId;
        else body.op_id = userId;
        
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body)
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                // Update the table cells without full refresh
                var columnIndex = _changeMode === 'sales' ? 13 : 14; // Sales is column 13, OP is column 14 (after adding lock column)
                ids.forEach(function(id) {
                    var row = document.getElementById('hbl-row-' + id);
                    if (row) {
                        var cells = row.querySelectorAll('td');
                        if (cells[columnIndex]) {
                            cells[columnIndex].textContent = userName;
                        }
                        // Uncheck the checkbox
                        var checkbox = row.querySelector('.row-check');
                        if (checkbox) checkbox.checked = false;
                    }
                });
                updateToolbar();
                showToast('success', d.message);
            } else {
                showToast('error', d.message || 'Update failed.');
            }
        })
        .catch(function() { showToast('error', 'Failed to update.'); });
        closeChangeUser();
    }

    /* ================================================================
       COLOR PICKER
    ================================================================ */
    var COLOR_OPTIONS = [
        { label: 'Urgent',           value: '#E08283' },
        { label: 'Ready to bill',    value: '#F3C200' },
        { label: 'Ready to close',   value: '#25A69A' },
        { label: 'Postpone',         value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' },
    ];

    function openColorPicker(id, currentColor) {
        _colorHblId = id;
        var grid = document.getElementById('color-picker-grid');
        grid.innerHTML = COLOR_OPTIONS.map(function(o) {
            var active = o.value === currentColor;
            return '<div class="color-picker-opt ' + (active ? 'active' : '') + '" onclick="selectColor(\'' + o.value + '\', this)"><span class="swatch" style="background:' + o.value + '"></span><span>' + o.label + '</span><i class="fa fa-check"></i></div>';
        }).join('');
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function selectColor(color, el) {
        document.querySelectorAll('.color-picker-opt').forEach(function(c) { c.classList.remove('active'); });
        el.classList.add('active');
        var id = _colorHblId;
        fetch('/air-export/hbl/' + id + '/color', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var span = document.querySelector('#hbl-row-' + id + ' .color-mark');
                if (span) span.style.background = color;
                showToast('success', 'Color updated');
            }
        })
        .catch(function() { showToast('error', 'Color update failed'); });
        closeColorPicker();
    }

    function closeColorPicker() { document.getElementById('color-picker-overlay').classList.remove('open'); _colorHblId = null; }

    function clearColor() {
        var id = _colorHblId;
        if (!id) return;
        fetch('/air-export/hbl/' + id + '/color', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var span = document.querySelector('#hbl-row-' + id + ' .color-mark');
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Color cleared');
            }
        })
        .catch(function() { showToast('error', 'Failed to clear'); });
        closeColorPicker();
    }

    /* ================================================================
       EXPORT CSV
    ================================================================ */
    function exportCsv() {
        var q = document.getElementById('quick-search')?.value?.trim();
        var params = [];
        if (q) params.push('search=' + encodeURIComponent(q));
        document.querySelectorAll('#filter-row .filter-input').forEach(function(inp) {
            if (inp.value.trim()) {
                var key = FILTER_MAP[inp.dataset.colIdx];
                if (key) params.push(key + '=' + encodeURIComponent(inp.value.trim()));
            }
        });
        
        // Create temporary anchor for download without page refresh
        var url = '/air-export/hbl-export-csv' + (params.length ? '?' + params.join('&') : '');
        var a = document.createElement('a');
        a.href = url;
        a.download = 'hawb-list-' + new Date().toISOString().slice(0, 10) + '.csv';
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        showToast('success', 'Export started. Your download will begin shortly.');
    }

    /* ================================================================
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(type, msg) {
        var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() { t.remove(); }, 3000);
    }

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif
    </script>
    @endpush
</x-layout>
