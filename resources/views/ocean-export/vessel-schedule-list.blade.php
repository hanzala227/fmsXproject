<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        /* Button Group Styling */
        .btn-group {
            display: inline-flex;
            gap: 0;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .portlet-tool {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        /* Mobile Responsive Enhancements - FIXED SCROLLING */
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
            .btn-action-round { 
                font-size: 9px !important; 
                padding: 0 6px !important; 
                height: 18px !important;
            }
            
            .portlet-tool { 
                flex-direction: column !important; 
                align-items: flex-start !important; 
                padding: 6px !important;
                gap: 6px !important;
            }
            .portlet-tool > div, .portlet-tool > form { width: 100%; }
            .btn-group { 
                width: 100%; 
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            .btn-tool { 
                font-size: 8px !important; 
                padding: 0 6px !important;
                height: 20px !important;
                flex: 0 1 auto;
            }
            .input-inline, .select-tool { 
                width: 100% !important; 
                font-size: 9px !important;
            }
            
            .portlet-body {
                padding: 0 !important;
                overflow: hidden !important;
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
                width: auto !important;
                min-width: 1800px !important;
            }
            
            .grid-table th, .grid-table td { 
                padding: 2px 4px !important;
                height: 22px !important;
            }
            
            /* Only 3 sticky columns on tablet */
            .sticky-col { 
                font-size: 8px !important;
            }
            
            .grid-table th:nth-child(4), .grid-table td:nth-child(4) {
                position: static !important;
                left: auto !important;
            }
            
            .filter-input { 
                height: 18px !important; 
                font-size: 8px !important;
                padding: 1px 4px !important;
            }
            
            .color-mark { 
                width: 12px !important; 
                height: 12px !important; 
            }
            
            .modal-box { 
                width: 90% !important; 
                max-width: 280px !important;
                margin: 10px !important;
            }
            
            .confirm-box { 
                width: 90% !important; 
                max-width: 260px !important;
                padding: 12px !important;
            }
        }
        
        @media (max-width: 480px) {
            .grid-wrapper { 
                height: calc(100vh - 420px) !important;
            }
            
            .grid-table { 
                min-width: 1600px !important;
            }
            
            /* Only 2 sticky columns on mobile */
            .grid-table th:nth-child(3), .grid-table td:nth-child(3),
            .grid-table th:nth-child(4), .grid-table td:nth-child(4) {
                position: static !important;
                left: auto !important;
            }
        }
        
        @media (max-width: 360px) {
            /* Only 1 sticky column on very small mobile */
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) {
                position: static !important;
                left: auto !important;
            }
        }
        
        @media (orientation: landscape) and (max-height: 600px) {
            .grid-wrapper { 
                height: calc(100vh - 200px) !important;
                min-height: 140px !important;
            }
        }
        
        @media (hover: none) and (pointer: coarse) {
            .btn-tool, .btn-action-round, input[type="checkbox"] {
                min-height: 28px !important;
            }
        }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon" style="color:#ef4444;"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Schedule(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

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
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Ocean Export <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Vessel Schedule List</span></li>
            </ul>
        </div>
        

        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Vessel Schedule List</span>
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
                    <button class="btn-action-round white" onclick="exportExcel()" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('vessel-schedules.create') }}" title="New Schedule">
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
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                </div>
            </div>

            {{-- ── TABLE FORM ── --}}

            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check"        style="width:25px;text-align:center;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="schedule_no" style="width:160px;left:25px;">Schedule No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="customer"    style="width:140px;left:213px;">Customer</th>
                                    <th data-col="office"        style="width:70px;">Office</th>
                                    <th data-col="vessel"        style="width:120px;">Vessel Name</th>
                                    <th data-col="voyage"        style="width:80px;">Voyage</th>
                                    <th data-col="etd"           style="width:85px;">ETD</th>
                                    <th data-col="eta"           style="width:85px;">ETA</th>
                                    <th data-col="pol"           style="width:140px;">Port of Loading</th>
                                    <th data-col="pod"           style="width:140px;">Port of Discharge</th>
                                    <th data-col="fdest"         style="width:140px;">Final Destination</th>
                                    <th data-col="por"           style="width:130px;">Place of Receipt</th>
                                    <th data-col="del"           style="width:130px;">Place of Delivery</th>
                                    <th data-col="carrier_bkg"   style="width:110px;">Carrier Bkg. No.</th>
                                    <th data-col="carrier"       style="width:120px;">Carrier</th>
                                    <th data-col="oversea_agent" style="width:120px;">Oversea Agent</th>
                                    <th data-col="fwd_agent"     style="width:120px;">Fwd. Agent</th>
                                    <th data-col="op"            style="width:90px;">OP</th>
                                    <th data-col="svc_from"      style="width:70px;">Svc From</th>
                                    <th data-col="svc_to"        style="width:70px;">Svc To</th>
                                    <th data-col="cargo_type"    style="width:80px;">Cargo Type</th>
                                    <th data-col="ship_mode"     style="width:80px;">Ship Mode</th>
                                    <th data-col="post_date"     style="width:85px;">Post Date</th>
                                </tr>

                                <tr id="filter-row" style="display:none;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"><input class="filter-input" data-col-idx="1" placeholder="Schedule…" oninput="applyFilters()"></td>
                                    <td class="sticky-col" style="left:185px;"></td>
                                    <td class="sticky-col" style="left:213px;"><input class="filter-input" data-col-idx="3" placeholder="Customer…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="4"  placeholder="Office…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="5"  placeholder="Vessel…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="6"  placeholder="Voyage…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="7"  placeholder="ETD…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="8"  placeholder="ETA…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="9"  placeholder="POL…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="10" placeholder="POD…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="11" placeholder="FDEST…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="12" placeholder="POR…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="13" placeholder="DEL…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="14" placeholder="Bkg No…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="15" placeholder="Carrier…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="16" placeholder="Agent…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="17" placeholder="Fwd…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="18" placeholder="OP…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="19" placeholder="Svc From…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="20" placeholder="Svc To…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="21" placeholder="Cargo…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="22" placeholder="Mode…" oninput="applyFilters()"></td>
                                    <td colspan="2"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @include('ocean-export.partials.vessel-schedule-list-rows')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── PAGINATION ── --}}
                                 <div class="portlet-tool bottom">
                                     <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                                         <div id="pagination-container">{{ $schedules->links('vendor.pagination.custom') }}</div>
                                         <div style="font-size:10px;color:#64748b;">
                                             Showing <span id="stat-first">{{ $schedules->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $schedules->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $schedules->total() }}</span> records
                                         </div>
                                     </div>
                                 </div>
        </div>
    </div>

    <iframe id="excel-frame" style="display:none;"></iframe>

    @push('scripts')
    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

    function updateToolbar() {
        var checkboxes = document.querySelectorAll('.row-check');
        var checked = [];
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                checked.push(checkboxes[i]);
            }
        }
        
        var all = [];
        for (var j = 0; j < checkboxes.length; j++) {
            all.push(checkboxes[j]);
        }
        
        var n = checked.length;
        var sa = document.getElementById('select-all');
        if (sa) {
            sa.checked = n === all.length && all.length > 0;
            sa.indeterminate = n > 0 && n < all.length;
        }

        var btnDelete = document.getElementById('btn-delete');
        var btnCopy = document.getElementById('btn-copy');
        if (btnDelete) btnDelete.disabled = n === 0;
        if (btnCopy) btnCopy.disabled = n !== 1;

        var badge = document.getElementById('sel-badge');
        if (badge) {
            badge.style.display = n > 0 ? 'inline' : 'none';
            badge.textContent = n + ' selected';
        }

        var rows = document.querySelectorAll('#grid-body tr[data-id]');
        for (var k = 0; k < rows.length; k++) {
            var cb = rows[k].querySelector('.row-check');
            if (cb && cb.checked) {
                rows[k].classList.add('row-selected');
            } else {
                rows[k].classList.remove('row-selected');
            }
        }
    }

    function toggleSelectAll(el) {
        var checkboxes = document.querySelectorAll('.row-check');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = el.checked;
        }
        updateToolbar();
    }

    function rowClick(e, row) {
        var skip = ['A', 'INPUT', 'BUTTON', 'I'];
        if (skip.indexOf(e.target.tagName) !== -1) return;
        var cb = row.querySelector('.row-check');
        if (cb) {
            cb.checked = !cb.checked;
            updateToolbar();
        }
    }

    function confirmDelete() {
        var checked = document.querySelectorAll('.row-check:checked');
        var n = checked.length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent =
            'You are about to permanently delete ' + n + ' schedule(s). This cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    
    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
    }
    
    function executeDelete() {
        closeConfirm();
        var checked = document.querySelectorAll('.row-check:checked');
        var ids = [];
        for (var i = 0; i < checked.length; i++) {
            ids.push(checked[i].value);
        }
        
        fetch('{{ route('vessel-schedules.bulk-delete') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                showToast('success', d.message);
                updateGrid(window.location.href);
            } else {
                showToast('error', d.message || 'Delete failed.');
            }
        })
        .catch(function() {
            showToast('error', 'Failed to delete schedule(s).');
        });
    }

    function copySelected() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length !== 1) return;
        var row = checked[0].closest('tr');
        var scheduleNo = row.dataset.schedule || '';
        showToast('info', 'Copying schedule: ' + scheduleNo + ' …');
        setTimeout(function() {
            window.location.href = '{{ route('vessel-schedules.create') }}?copy=' + row.dataset.id;
        }, 600);
    }

    var filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        var filterRow = document.getElementById('filter-row');
        var btnFilter = document.getElementById('btn-filter');
        
        filterRow.style.display = filterOpen ? 'table-row' : 'none';
        if (filterOpen) {
            btnFilter.classList.add('active');
        } else {
            btnFilter.classList.remove('active');
        }
        
        if (filterOpen) {
            var firstInput = document.querySelector('.filter-input');
            if (firstInput) firstInput.focus();
        } else {
            var inputs = document.querySelectorAll('.filter-input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].value = '';
            }
            applyFilters();
        }
    }

    function updateGrid(url) {
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                var gridBody = document.getElementById('grid-body');
                if (gridBody && data.html) {
                    gridBody.innerHTML = data.html;
                }
                
                var paginationContainer = document.getElementById('pagination-container');
                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }
                
                var statFirst = document.getElementById('stat-first');
                var statLast = document.getElementById('stat-last');
                var statTotal = document.getElementById('stat-total');
                
                if (statFirst) statFirst.textContent = data.first || 0;
                if (statLast) statLast.textContent = data.last || 0;
                if (statTotal) statTotal.textContent = data.total || 0;
                
                updateToolbar();
            } else {
                showToast('error', 'Failed to update grid');
            }
        })
        .catch(function(e) {
            showToast('error', 'Failed to update grid');
        });
    }

    var searchTimer;
    function quickSearch(val) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var trimmed = val.trim();
            if (trimmed) {
                url.searchParams.set('search', trimmed);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 400);
    }

    var filterTimer;
    var filterMap = {
        '1': 'filter_schedule_no', '3': 'filter_customer', '4': 'filter_office',
        '5': 'filter_vessel', '6': 'filter_voyage', '7': 'filter_etd',
        '8': 'filter_eta', '9': 'filter_pol', '10': 'filter_pod',
        '11': 'filter_fdest', '12': 'filter_por', '13': 'filter_del',
        '14': 'filter_carrier_bkg', '15': 'filter_carrier',
        '16': 'filter_oversea_agent', '17': 'filter_fwd_agent',
        '18': 'filter_op', '19': 'filter_svc_from', '20': 'filter_svc_to',
        '21': 'filter_cargo_type', '22': 'filter_ship_mode'
    };
    
    function applyFilters() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var inputs = document.querySelectorAll('#filter-row .filter-input');
            
            for (var i = 0; i < inputs.length; i++) {
                var inp = inputs[i];
                var colIdx = inp.dataset.colIdx;
                var key = filterMap[colIdx];
                if (key) {
                    var val = inp.value.trim();
                    if (val) {
                        url.searchParams.set(key, val);
                    } else {
                        url.searchParams.delete(key);
                    }
                }
            }
            
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 400);
    }

    var PINNED_COLS = ['check', 'schedule_no', 'color', 'customer'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        var btn = document.getElementById('btn-config');
        var open = panel.style.display === 'none' || panel.style.display === '';
        
        panel.style.display = open ? 'block' : 'none';
        if (open) {
            btn.classList.add('active');
            buildConfigPanel();
        } else {
            btn.classList.remove('active');
        }
    }

    function buildConfigPanel() {
        var container = document.getElementById('col-toggles');
        container.innerHTML = '';
        var headers = document.querySelectorAll('#header-row th[data-col]');
        
        for (var i = 0; i < headers.length; i++) {
            var th = headers[i];
            var colName = th.dataset.col;
            if (PINNED_COLS.indexOf(colName) !== -1) continue;
            
            var label = document.createElement('label');
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.setAttribute('data-col', colName);
            cb.onchange = function() {
                var col = this.getAttribute('data-col');
                toggleColumn(col, this.checked);
            };
            label.appendChild(cb);
            label.appendChild(document.createTextNode(' ' + th.textContent.trim()));
            container.appendChild(label);
        }
    }

    function toggleColumn(colName, show) {
        var th = document.querySelector('#header-row th[data-col="' + colName + '"]');
        if (!th) return;
        
        var children = th.parentElement.children;
        var idx = -1;
        for (var i = 0; i < children.length; i++) {
            if (children[i] === th) {
                idx = i;
                break;
            }
        }
        
        th.style.display = show ? '' : 'none';
        
        var rows = document.querySelectorAll('#grid-body tr, #filter-row');
        for (var j = 0; j < rows.length; j++) {
            var cells = rows[j].querySelectorAll('td, th');
            if (cells[idx]) {
                cells[idx].style.display = show ? '' : 'none';
            }
        }
    }

    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn = document.getElementById('btn-config');
        if (panel && btn && panel.style.display === 'block') {
            if (!panel.contains(e.target) && !btn.contains(e.target)) {
                panel.style.display = 'none';
                btn.classList.remove('active');
            }
        }
    });

    var COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' }
    ];

    var _colorScheduleId = null;

    function openColorPicker(id, currentColor) {
        _colorScheduleId = id;
        var grid = document.getElementById('color-picker-grid');
        var html = '';
        
        for (var i = 0; i < COLOR_OPTIONS.length; i++) {
            var o = COLOR_OPTIONS[i];
            var active = o.value === currentColor;
            html += '<div class="color-picker-opt ' + (active ? 'active' : '') + '" onclick="selectColor(\'' + o.value + '\', this)">';
            html += '<span class="swatch" style="background:' + o.value + '"></span>';
            html += '<span>' + o.label + '</span>';
            html += '<i class="fa fa-check"></i>';
            html += '</div>';
        }
        
        grid.innerHTML = html;
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function selectColor(color, el) {
        var opts = document.querySelectorAll('.color-picker-opt');
        for (var i = 0; i < opts.length; i++) {
            opts[i].classList.remove('active');
        }
        el.classList.add('active');
        
        var id = _colorScheduleId;
        var url = '{{ route("vessel-schedules.update-color", "ID") }}'.replace('ID', id);
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var span = document.querySelector('#schedule-row-' + id + ' .color-mark');
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            }
        })
        .catch(function() {
            showToast('error', 'Failed to update color');
        });
        
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorScheduleId = null;
    }

    function clearColor() {
        var id = _colorScheduleId;
        var url = '{{ route("vessel-schedules.update-color", "ID") }}'.replace('ID', id);
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var span = document.querySelector('#schedule-row-' + id + ' .color-mark');
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            }
        })
        .catch(function() {
            showToast('error', 'Failed to clear color');
        });
        
        closeColorPicker();
    }

    function exportExcel() {
        showToast('info', 'Preparing Excel export...');
        
        var q = document.getElementById('quick-search');
        var searchVal = q ? q.value.trim() : '';
        
        var filters = {};
        var filterInputs = document.querySelectorAll('#filter-row .filter-input');
        for (var i = 0; i < filterInputs.length; i++) {
            var inp = filterInputs[i];
            if (inp.value.trim()) {
                var colIdx = inp.dataset.colIdx;
                var colName = filterMap[colIdx];
                if (colName) {
                    filters[colName] = inp.value.trim();
                }
            }
        }
        
        var url = '{{ route('vessel-schedules.export-csv') }}';
        var params = [];
        
        if (searchVal) {
            params.push('search=' + encodeURIComponent(searchVal));
        }
        
        for (var key in filters) {
            if (filters.hasOwnProperty(key)) {
                params.push(key + '=' + encodeURIComponent(filters[key]));
            }
        }
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        var iframe = document.getElementById('excel-frame');
        iframe.src = url;
        
        setTimeout(function() {
            showToast('success', 'Excel export started');
        }, 300);
    }

    function showToast(type, msg) {
        var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        var icon = icons[type] || 'info-circle';
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + icon + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() {
            t.remove();
        }, 3000);
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
