<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        /* Filter Select Styling (not in list-styles) */
        .filter-select {
            width: 100%;
            height: 18px;
            border: 1px solid #93c5fd;
            font-size: 9px;
            border-radius: 2px;
            padding: 0 3px;
            box-sizing: border-box;
            outline: none;
            background: #fff;
            color: #334155;
        }
        .filter-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px rgba(59,130,246,0.2);
        }
        
        /* Action Icons Styling */
        .action-icon {
            font-size: 11px;
            cursor: pointer;
            margin: 0 4px;
            color: #64748b;
            transition: color 0.2s, transform 0.2s;
        }
        .action-icon:hover {
            color: #3b82f6;
            transform: scale(1.15);
        }
        .action-icon.danger:hover {
            color: #ef4444;
        }
        
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
        
        /* Improved Grid Container */
        .grid-container {
            width: 100%;
            overflow: visible;
        }
        
        .grid-wrapper {
            width: 100%;
            height: calc(100vh - 320px);
            min-height: 400px;
            overflow-x: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .grid-table {
            min-width: 1400px;
            width: 100%;
        }
        
        /* Print Styles - Only print table data */
        @media print {
            /* Hide everything except table */
            .page-bar,
            .portlet-title,
            .portlet-tool,
            .btn-group,
            .btn-action-round,
            .actions,
            .caption-subject,
            #filter-row,
            .pagination,
            .portlet-tool.bottom,
            body > *:not(.page-content),
            .page-content > *:not(.portlet) {
                display: none !important;
            }
            
            /* Show only the table */
            .page-content,
            .portlet,
            .portlet-body,
            .grid-container,
            .grid-wrapper,
            .grid-table {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            
            /* Remove sticky positioning for print */
            .sticky-col {
                position: static !important;
                left: auto !important;
            }
            
            /* Hide action icons and checkboxes */
            .action-icon,
            .row-check,
            #select-all,
            th[data-col="check"],
            td:first-child,
            th[data-col="actions"],
            td:last-child {
                display: none !important;
            }
            
            /* Table styling for print */
            .grid-table {
                border-collapse: collapse !important;
                font-size: 10px !important;
                min-width: auto !important;
            }
            
            .grid-table th,
            .grid-table td {
                border: 1px solid #ddd !important;
                padding: 4px 6px !important;
                text-align: left !important;
            }
            
            .grid-table th {
                background: #f3f4f6 !important;
                font-weight: bold !important;
                color: #000 !important;
            }
            
            /* Color marks - print as colored circles */
            .color-mark {
                display: inline-block !important;
                width: 12px !important;
                height: 12px !important;
                border-radius: 50% !important;
                border: 1px solid #ccc !important;
            }
            
            /* Status badges */
            .badge-status {
                border: 1px solid #000 !important;
                padding: 2px 4px !important;
                font-size: 9px !important;
            }
            
            /* Links - show as normal text */
            a {
                color: #000 !important;
                text-decoration: none !important;
            }
            
            /* Page breaks */
            tr {
                page-break-inside: avoid !important;
            }
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
                min-width: 1200px !important;
            }
            
            .grid-table th, .grid-table td { 
                padding: 2px 4px !important;
                height: 22px !important;
            }
            
            .action-icon {
                font-size: 10px;
                margin: 0 2px;
            }
        }
    </style>
    @endpush

    {{-- ═══════════════════════ TOAST CONTAINER ═══════════════════════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════════════════ DELETE CONFIRM MODAL ═══════════════════════ --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete MAWB Stock?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ COLOR PICKER MODAL ═══════════════════════ --}}
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

    {{-- ═══════════════════════ MAIN PAGE ═══════════════════════ --}}
    <div class="page-content">

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Air Export <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">MAWB Stock List</span></li>
            </ul>
        </div>

        <div class="portlet light">

            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">MAWB Stock List</span>
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
                    <a class="btn-action-round white" href="/air-export/export-csv" title="Download as CSV/Excel" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="/air-export/create" title="New MAWB">
                            <i class="fa fa-plus"></i>
                        </a>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search…" value="{{ request('search') }}"
                           oninput="quickSearch(this.value)">
                </div>
            </div>

            {{-- ── TABLE ── --}}
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
                                    <th class="sticky-col sticky-col-header" data-col="status"  style="width:90px;left:50px;">Status</th>
                                    <th class="sticky-col sticky-col-header" data-col="prefix"  style="width:50px;left:140px;">Prefix</th>
                                    <th class="sticky-col sticky-col-header" data-col="waybill" style="width:120px;left:190px;">Waybill No.</th>
                                    <th data-col="carrier" style="width:180px;">Carrier</th>
                                    <th data-col="file"    style="width:130px;">File No.</th>
                                    <th data-col="office"  style="width:90px;">Office</th>
                                    <th data-col="date"    style="width:130px;">Created Date</th>
                                    <th data-col="actions" style="width:70px;">Actions</th>
                                </tr>

                                {{-- ── FILTER ROW (hidden by default) ── --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;background:#eff6ff;"></td>
                                    <td class="sticky-col" style="left:25px;background:#eff6ff;"></td>
                                    <td class="sticky-col" style="left:50px;background:#eff6ff;">
                                        <select class="filter-select" data-col-idx="2" onchange="applyFilters()" style="width:100%;">
                                            <option value="">All</option>
                                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                            <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                        </select>
                                    </td>
                                    <td class="sticky-col" style="left:140px;background:#eff6ff;"></td>
                                    <td class="sticky-col" style="left:190px;background:#eff6ff;">
                                        <input class="filter-input" data-col-idx="4" placeholder="Waybill…" oninput="applyFiltersTyping()" onkeyup="if(event.key==='Enter') applyFilters()" style="width:100%;">
                                    </td>
                                    <td class="sticky-col" style="left:310px;background:#eff6ff;"></td>
                                    <td>
                                        <select class="filter-select" data-col-idx="6" onchange="applyFilters()" style="width:100%;">
                                            <option value="">All Carriers</option>
                                            @foreach($carriers as $c)
                                                <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input class="filter-input" data-col-idx="7" placeholder="File No…" oninput="applyFiltersTyping()" onkeyup="if(event.key==='Enter') applyFilters()" style="width:100%;"></td>
                                    <td>
                                        <select class="filter-select" data-col-idx="8" onchange="applyFilters()" style="width:100%;">
                                            <option value="">All Offices</option>
                                            @foreach($offices as $o)
                                                <option value="{{ $o->id }}" {{ request('office_id') == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @forelse($stocks as $stock)
                                @php
                                    $isBlocked = $stock->is_blocked ?? false;
                                    $isAssigned = !$isBlocked && !empty($stock->file_no);
                                    $isAvailable = !$isBlocked && empty($stock->file_no);
                                    if ($isBlocked) { $badgeClass = 'bg-red'; $statusLabel = 'BLOCKED'; }
                                    elseif ($isAssigned) { $badgeClass = 'bg-orange'; $statusLabel = 'ASSIGNED'; }
                                    else { $badgeClass = 'bg-green'; $statusLabel = 'AVAILABLE'; }
                                @endphp
                                <tr id="stock-row-{{ $stock->id }}"
                                    data-id="{{ $stock->id }}"
                                    data-file="{{ $stock->file_no ?? '' }}"
                                    onclick="rowClick(event, this)">
                                    {{-- Checkbox --}}
                                    <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <input type="checkbox" name="ids[]" value="{{ $stock->id }}" class="row-check" onchange="updateToolbar()">
                                    </td>
                                    {{-- Lock --}}
                                    <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <i class="fa {{ $isBlocked ? 'fa-lock' : 'fa-unlock' }}" 
                                           style="color:{{ $isBlocked ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
                                           title="{{ $isBlocked ? 'Blocked' : 'Unlocked' }}"></i>
                                    </td>
                                    {{-- Status --}}
                                    <td class="sticky-col" style="width:90px;left:50px;">
                                        <span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    {{-- Prefix --}}
                                    <td class="sticky-col" style="width:50px;left:140px;">{{ $stock->mawb_no ? substr($stock->mawb_no, 0, 3) : '---' }}</td>
                                    {{-- Waybill No. --}}
                                    <td class="sticky-col" style="width:120px;left:190px;font-weight:600;">
                                        <a href="/air-export/{{ $stock->id }}/edit" class="col-link">{{ $stock->mawb_no ? substr($stock->mawb_no, 4) : '--' }}</a>
                                    </td>
                                    {{-- Carrier --}}
                                    <td>{{ $stock->carrier->name ?? '--' }}</td>
                                    {{-- File No. --}}
                                    <td><a href="/air-export/{{ $stock->id }}/edit" class="col-link">{{ $stock->file_no ?? '--' }}</a></td>
                                    {{-- Office --}}
                                    <td>{{ $stock->office->name ?? '-' }}</td>
                                    {{-- Created Date --}}
                                    <td>{{ $stock->created_at ? $stock->created_at->format('m-d-Y H:i') : '--' }}</td>
                                    {{-- Actions --}}
                                    <td onclick="event.stopPropagation()">
                                        <i class="fa fa-copy action-icon" title="Copy" onclick="copyRow({{ $stock->id }})"></i>
                                        <i class="fa fa-trash-o action-icon danger" title="Delete" onclick="deleteRow({{ $stock->id }})"></i>
                                    </td>
                                </tr>
                                @empty
                                <tr id="empty-row">
                                    <td colspan="11" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No MAWB stocks found.
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
                    <div id="pagination-container">{{ $stocks->links('vendor.pagination.custom') }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $stocks->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $stocks->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $stocks->total() }}</span> records
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var searchDebounce;
    var filterDebounce;
    var _deleteId = null;
    var _colorId = null;

    /* ---------- AJAX Grid ---------- */
    async function updateGrid(url) {
        try {
            var resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!resp.ok) { showToast('error', 'Failed to load data'); return; }
            var html = await resp.text();
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var newBody = doc.getElementById('grid-body');
            var newPagination = doc.getElementById('pagination-container');
            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;
            var stats = doc.querySelector('.portlet-tool.bottom div:last-child');
            if (stats) {
                var text = stats.textContent;
                var matches = text.match(/\d+/g);
                if (matches && matches.length >= 3) {
                    document.getElementById('stat-first').textContent = matches[0];
                    document.getElementById('stat-last').textContent = matches[1];
                    document.getElementById('stat-total').textContent = matches[2];
                }
            }
            bindPaginationClicks();
            updateToolbar();
        } catch(e) {
            showToast('error', 'Search failed: ' + e.message);
        }
    }

    function bindPaginationClicks() {
        document.querySelectorAll('.pagination a').forEach(function(link) {
            link.removeEventListener('click', paginationHandler);
            link.addEventListener('click', paginationHandler);
        });
    }

    function paginationHandler(e) {
        e.preventDefault();
        updateGrid(this.href);
        var qs = document.getElementById('quick-search');
        if (qs) {
            var url = new URL(this.href);
            if (qs.value) url.searchParams.set('search', qs.value);
            window.history.replaceState({}, '', url.toString());
        }
    }

    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function() {
            var url = new URL(window.location.href);
            url.searchParams.set('search', val);
            url.searchParams.delete('page');
            window.history.replaceState({}, '', url.toString());
            updateGrid(url.toString());
        }, 300);
    }

    bindPaginationClicks();

    /* ---------- Toast ---------- */
    function showToast(type, msg) {
        var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> <span>' + msg + '</span>';
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() { t.remove(); }, 3000);
    }

    /* ---------- Checkbox / Bulk Toolbar ---------- */
    function updateToolbar() {
        var checked = document.querySelectorAll('.row-check:checked');
        var all = document.querySelectorAll('.row-check');
        var n = checked.length;
        var sa = document.getElementById('select-all');
        if (sa) { sa.checked = n === all.length && all.length > 0; sa.indeterminate = n > 0 && n < all.length; }
        document.getElementById('btn-delete').disabled = n === 0;
        document.getElementById('btn-block').disabled = n === 0;
        document.getElementById('btn-unblock').disabled = n === 0;
        var badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent = n + ' selected';
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
        if (skip.includes(e.target.tagName)) return;
        var cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.row-check:checked')).map(function(cb) { return cb.value; });
    }

    /* ---------- Filter ---------- */
    function toggleFilter() {
        var row = document.getElementById('filter-row');
        var btn = document.getElementById('btn-filter');
        var isVisible = row.style.display === 'table-row';
        row.style.display = isVisible ? 'none' : 'table-row';
        btn.classList.toggle('active', !isVisible);
        if (!isVisible) {
            // Load current request params into filter inputs
            var params = new URLSearchParams(window.location.search);
            var selects = row.querySelectorAll('select.filter-select');
            selects.forEach(function(sel) {
                var idx = parseInt(sel.dataset.colIdx);
                if (idx === 2) sel.value = params.get('status') || '';
                else if (idx === 6) sel.value = params.get('carrier_id') || '';
                else if (idx === 8) sel.value = params.get('office_id') || '';
            });
            var inputs = row.querySelectorAll('input.filter-input');
            inputs.forEach(function(inp) {
                var idx = parseInt(inp.dataset.colIdx);
                if (idx === 4) inp.value = params.get('search') || '';
                else if (idx === 7) inp.value = params.get('search') || '';
            });
        } else {
            document.querySelectorAll('#filter-row input').forEach(function(i) { i.value = ''; });
            document.querySelectorAll('#filter-row select').forEach(function(s) { s.value = ''; });
            applyFilters();
        }
    }

    function applyFiltersTyping() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(function() {
            applyFilters();
        }, 500);
    }
    
    function applyFilters() {
        clearTimeout(filterDebounce);
        var url = new URL(window.location.href);
        url.search = '';
        
        // Collect filter values
        var search = document.getElementById('quick-search')?.value?.trim() || '';
        if (search) url.searchParams.set('search', search);
        
        var status = document.querySelector('#filter-row select[data-col-idx="2"]')?.value || '';
        if (status) url.searchParams.set('status', status);
        
        var waybill = document.querySelector('#filter-row input[data-col-idx="4"]')?.value?.trim() || '';
        if (waybill) url.searchParams.set('search', waybill); // Use 'search' param for waybill
        
        var carrier = document.querySelector('#filter-row select[data-col-idx="6"]')?.value || '';
        if (carrier) url.searchParams.set('carrier_id', carrier);
        
        var fileNo = document.querySelector('#filter-row input[data-col-idx="7"]')?.value?.trim() || '';
        if (fileNo) url.searchParams.set('search', fileNo); // Use 'search' param for file_no
        
        var office = document.querySelector('#filter-row select[data-col-idx="8"]')?.value || '';
        if (office) url.searchParams.set('office_id', office);
        
        window.history.replaceState({}, '', url.toString());
        updateGrid(url.toString());
    }

    /* ---------- Config ---------- */
    var PINNED_COLS = ['check', 'lock', 'status', 'prefix', 'waybill', 'color'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        var btn = document.getElementById('btn-config');
        var open = panel.style.display !== 'none';
        panel.style.display = open ? 'none' : 'block';
        btn.classList.toggle('active', !open);
        if (!open) buildConfigPanel();
    }

    function buildConfigPanel() {
        var container = document.getElementById('col-toggles');
        container.innerHTML = '';
        document.querySelectorAll('#header-row th[data-col]').forEach(function(th) {
            if (PINNED_COLS.includes(th.dataset.col)) return;
            var label = document.createElement('label');
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.onchange = function() { toggleColumn(th.dataset.col, cb.checked); };
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim());
            container.appendChild(label);
        });
    }

    function toggleColumn(colName, show) {
        var th = document.querySelector('#header-row th[data-col="' + colName + '"]');
        if (!th) return;
        var idx = Array.from(th.parentElement.children).indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(function(row) {
            var cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    // Close config on outside click
    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn = document.getElementById('btn-config');
        if (panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ---------- Color Picker ---------- */
    var COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' }
    ];

    function openColorPicker(id, cur) {
        _colorId = id;
        var grid = document.getElementById('color-picker-grid');
        grid.innerHTML = COLOR_OPTIONS.map(function(o) {
            var active = o.value === cur;
            return '<div class="color-picker-opt ' + (active ? 'active' : '') + '" onclick="selectColor(\'' + o.value + '\',this)"><span class="swatch" style="background:' + o.value + '"></span><span>' + o.label + '</span><i class="fa fa-check"></i></div>';
        }).join('');
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function selectColor(color, el) {
        document.querySelectorAll('.color-picker-opt').forEach(function(c) { c.classList.remove('active'); });
        el.classList.add('active');
        var id = _colorId;
        fetch('/air-export/' + id + '/color', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                showToast('success', 'Color updated');
                var dot = document.querySelector('#stock-row-' + id + ' .color-mark');
                if (dot) dot.style.background = color;
            }
        })
        .catch(function() { showToast('error', 'Color update failed'); });
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorId = null;
    }

    function clearColor() {
        if (!_colorId) return;
        var id = _colorId;
        fetch('/air-export/' + id + '/color', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                showToast('success', 'Color cleared');
                var dot = document.querySelector('#stock-row-' + id + ' .color-mark');
                if (dot) dot.style.background = '#94a3b8';
            }
        })
        .catch(function() { showToast('error', 'Failed to clear'); });
        closeColorPicker();
    }

    /* ---------- Delete ---------- */
    function deleteRow(id) {
        _deleteId = id;
        document.getElementById('confirm-msg').textContent = 'Delete MAWB stock? This action cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }

    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
        _deleteId = null;
    }

    function executeDelete() {
        var id = _deleteId;
        var ids = getSelectedIds();
        closeConfirm();
        // Single row delete
        if (id) {
            fetch('/air-export/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF }
            })
            .then(function(r) {
                if (r.redirected) { window.location.href = r.url; return; }
                return r.json();
            })
            .then(function(d) {
                if (!d || d.success !== false) {
                    showToast('success', 'MAWB stock deleted.');
                    var row = document.getElementById('stock-row-' + id);
                    if (row) row.remove();
                    updateToolbar();
                }
            })
            .catch(function() { window.location.reload(); });
        // Bulk delete
        } else if (ids.length > 0) {
            fetch('/air-export/bulk-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ ids: ids })
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    showToast('success', d.message || 'Items deleted.');
                    updateGrid(window.location.href);
                } else {
                    showToast('error', d.message || 'Delete failed.');
                }
            })
            .catch(function() { showToast('error', 'Delete request failed.'); });
        }
    }

    function confirmDelete() {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent = 'You are about to permanently delete ' + n + ' MAWB stock(s). This cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }

    /* ---------- Bulk Delete / Block / Unblock ---------- */
    function blockSelected() {
        var ids = getSelectedIds();
        if (ids.length === 0) { showToast('error', 'No items selected'); return; }
        
        fetch('/air-export/bulk-block', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) {
            if (!r.ok) { throw new Error('Server returned ' + r.status); }
            return r.json();
        })
        .then(function(d) {
            if (d.success) {
                // Update lock icons and status badges for blocked items
                ids.forEach(function(id) {
                    var row = document.getElementById('stock-row-' + id);
                    if (row) {
                        // Update lock icon (2nd column)
                        var lockCell = row.querySelector('td:nth-child(2)');
                        if (lockCell) {
                            lockCell.innerHTML = '<i class="fa fa-lock" style="color:#94a3b8;cursor:pointer;font-size:10px;" title="Blocked"></i>';
                        }
                        // Update status badge (3rd column)
                        var statusCell = row.querySelector('td:nth-child(3)');
                        if (statusCell) {
                            statusCell.innerHTML = '<span class="badge-status bg-red">BLOCKED</span>';
                        }
                        // Uncheck the checkbox
                        var checkbox = row.querySelector('.row-check');
                        if (checkbox) checkbox.checked = false;
                    }
                });
                updateToolbar();
                showToast('success', d.message || 'Items blocked.');
            } else {
                showToast('error', d.message || 'Block failed.');
            }
        })
        .catch(function(e) { showToast('error', 'Block request failed: ' + e.message); });
    }
    
    function unblockSelected() {
        var ids = getSelectedIds();
        if (ids.length === 0) { showToast('error', 'No items selected'); return; }
        
        fetch('/air-export/bulk-unblock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) {
            if (!r.ok) { throw new Error('Server returned ' + r.status); }
            return r.json();
        })
        .then(function(d) {
            if (d.success) {
                // Update lock icons and status badges for unblocked items
                ids.forEach(function(id) {
                    var row = document.getElementById('stock-row-' + id);
                    if (row) {
                        // Update lock icon to unlocked (2nd column)
                        var lockCell = row.querySelector('td:nth-child(2)');
                        if (lockCell) {
                            lockCell.innerHTML = '<i class="fa fa-unlock" style="color:#22c55e;cursor:pointer;font-size:10px;" title="Unlocked"></i>';
                        }
                        // Update status badge (3rd column)
                        var statusCell = row.querySelector('td:nth-child(3)');
                        var fileNo = row.getAttribute('data-file');
                        if (statusCell) {
                            // Check if assigned or available
                            if (fileNo && fileNo.trim() !== '') {
                                statusCell.innerHTML = '<span class="badge-status bg-orange">ASSIGNED</span>';
                            } else {
                                statusCell.innerHTML = '<span class="badge-status bg-green">AVAILABLE</span>';
                            }
                        }
                        // Uncheck the checkbox
                        var checkbox = row.querySelector('.row-check');
                        if (checkbox) checkbox.checked = false;
                    }
                });
                updateToolbar();
                showToast('success', d.message || 'Items unblocked.');
            } else {
                showToast('error', d.message || 'Unblock failed.');
            }
        })
        .catch(function(e) { showToast('error', 'Unblock request failed: ' + e.message); });
    }

    /* ---------- Copy Row ---------- */
    function copyRow(id) {
        showToast('info', 'Copying to new shipment...');
        setTimeout(function() {
            window.location.href = '/air-export/create?copy=' + id;
        }, 400);
    }

    /* ---------- Session flash toasts ---------- */
    @if(session('success')) showToast('success', @json(session('success'))); @endif
    @if(session('error')) showToast('error', @json(session('error'))); @endif
    </script>
    @endpush
</x-layout>
