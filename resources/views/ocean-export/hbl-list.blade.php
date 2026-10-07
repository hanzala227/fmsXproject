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
            
            /* Portlet Title - Stack on mobile */
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
            
            /* Toolbar - Stack on mobile */
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
            
            /* CRITICAL FIX: Table scrolling on mobile */
            .portlet-body {
                padding: 0 !important;
                overflow: hidden !important;
            }
            
            .grid-container { 
                width: 100% !important;
                overflow: hidden !important;
                background: #fff;
                position: relative;
            }
            
            .grid-wrapper { 
                width: 100% !important;
                height: calc(100vh - 350px) !important;
                min-height: 200px !important;
                overflow-x: auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                position: relative;
            }
            
            .grid-table { 
                font-size: 8px !important;
                width: auto !important;
                min-width: 1800px !important;
                table-layout: auto !important;
            }
            
            .grid-table th, .grid-table td { 
                padding: 2px 4px !important;
                height: 22px !important;
                white-space: nowrap !important;
            }
            
            /* Keep only 2 sticky columns on mobile */
            .sticky-col { 
                font-size: 8px !important;
                position: sticky !important;
                z-index: 5 !important;
                background: #fff !important;
            }
            
            .grid-table th:nth-child(1), .grid-table td:nth-child(1) { 
                left: 0 !important; 
            }
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) { 
                left: 25px !important; 
            }
            /* Remove sticky from other columns */
            .grid-table th:nth-child(3), .grid-table td:nth-child(3),
            .grid-table th:nth-child(4), .grid-table td:nth-child(4),
            .grid-table th:nth-child(5), .grid-table td:nth-child(5),
            .grid-table th:nth-child(6), .grid-table td:nth-child(6) {
                position: static !important;
                left: auto !important;
            }
            
            .filter-input { 
                height: 18px !important; 
                font-size: 8px !important;
                padding: 0 3px !important;
            }
            
            /* Modals on mobile */
            .modal-box, .confirm-box { 
                margin: 10px;
                width: calc(100% - 20px);
                max-width: 100%;
                min-width: 0 !important;
            }
            .modal-body { 
                padding: 8px !important;
                min-width: 0 !important;
            }
            .confirm-box { padding: 16px !important; }
            
            /* Config Panel on mobile */
            .config-panel {
                right: 0;
                left: 0;
                top: 22px;
                max-width: 100%;
                max-height: 250px;
            }
            
            /* Pagination on mobile */
            .portlet-tool.bottom { 
                flex-direction: column !important; 
                gap: 6px;
            }
            .portlet-tool.bottom > div { width: 100% !important; }
            .pagination { 
                justify-content: center;
                font-size: 9px !important;
            }
            .tp-page-btn {
                min-width: 20px !important;
                height: 18px !important;
                padding: 0 4px !important;
                font-size: 8px !important;
            }
            
            /* Toast on mobile */
            .toast-container { 
                top: 10px; 
                right: 10px;
                left: 10px;
            }
            .toast { 
                font-size: 10px !important;
                padding: 6px 10px !important;
            }
            
            /* Breadcrumbs on mobile */
            .page-bar { 
                padding: 6px 10px !important;
                margin-bottom: 8px !important;
            }
            .page-breadcrumb li { font-size: 10px !important; }
            
            #sel-badge { font-size: 8px !important; }
        }
        
        @media (max-width: 480px) {
            .grid-table { 
                font-size: 7px !important; 
                min-width: 1600px !important;
            }
            .grid-table th, .grid-table td { 
                padding: 2px 3px !important;
                height: 20px !important;
            }
            .btn-action-round, .btn-tool { 
                font-size: 8px !important;
                padding: 0 4px !important;
            }
            
            /* Keep only checkbox sticky on very small screens */
            .grid-table th:nth-child(2), .grid-table td:nth-child(2),
            .grid-table th:nth-child(3), .grid-table td:nth-child(3) {
                position: static !important;
                left: auto !important;
            }
        }
        
        @media (max-width: 768px) and (orientation: landscape) {
            .grid-wrapper { 
                height: calc(100vh - 200px) !important;
            }
        }
        
        /* Touch-friendly targets */
        @media (hover: none) and (pointer: coarse) {
            .btn-tool, .btn-action-round, .tp-page-btn {
                min-height: 28px !important;
                touch-action: manipulation;
            }
            .filter-input, .select-tool {
                min-height: 24px !important;
                touch-action: manipulation;
            }
            input[type="checkbox"] {
                width: 18px;
                height: 18px;
                touch-action: manipulation;
            }
            .grid-wrapper {
                -webkit-overflow-scrolling: touch !important;
                scroll-behavior: smooth;
            }
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════════════════ DELETE CONFIRM MODAL ═══════════════════════ --}}
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

    <div class="page-content">

          <!-- Breadcrumb -->
        <div class="page-bar">
             <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/ocean-export/list">Ocean Export</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">House B/L List</span></li>
            </ul>
        </div>
 

        <div class="portlet light">

            {{-- ── TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">House B/L List</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" onclick="toggleFilter()" id="btn-filter" title="Toggle filter row">
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
                    <button class="btn-action-round white" onclick="exportExcel()" title="Download as CSV/Excel" id="btn-excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-copy"   disabled title="Copy Selected (select 1 row)" onclick="copySelected()">
                            <i class="fa fa-files-o"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete"   disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                </div>
                <form method="GET" action="{{ route('ocean-export.hbl-list') }}" style="display:flex;align-items:center;gap:6px;margin:0;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" name="search" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                    @if(request()->has('search'))
                        <a href="{{ route('ocean-export.hbl-list') }}" style="font-size:10px;color:#3b82f6;text-decoration:none;">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    @endif
                    <button type="submit" style="display:none;">Search</button>
                </form>
            </div>

            {{-- ── ADVANCED FILTER ── --}}
            <div id="advanced-filter" style="display:none;background:#f0f4ff;padding:6px 8px;border-bottom:1px solid #bfdbfe;">
                <form method="GET" action="{{ route('ocean-export.hbl-list') }}" style="display:flex;flex-wrap:wrap;gap:6px;align-items:end;margin:0;">
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Operator</label>
                        <select name="op_id" class="input-inline" style="width:100px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($operators as $id => $name)
                                <option value="{{ $id }}" {{ request('op_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Sales Person</label>
                        <select name="sales_person_id" class="input-inline" style="width:100px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($salesPersons as $id => $name)
                                <option value="{{ $id }}" {{ request('sales_person_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">POL</label>
                        <select name="pol_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('pol_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">POD</label>
                        <select name="pod_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('pod_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">ETD From</label>
                        <input type="date" name="etd_from" class="input-inline" style="width:110px;height:20px;font-size:9px;" value="{{ request('etd_from') }}">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">ETD To</label>
                        <input type="date" name="etd_to" class="input-inline" style="width:110px;height:20px;font-size:9px;" value="{{ request('etd_to') }}">
                    </div>
                    <div style="display:flex;gap:4px;align-self:end;padding-bottom:1px;">
                        <button type="submit" class="btn-tool green" style="height:20px;font-size:9px;padding:0 10px;">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <a href="{{ route('ocean-export.hbl-list') }}" class="btn-tool" style="height:20px;font-size:9px;padding:0 10px;">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── TABLE ── --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="hbl-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;left:0;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="flag" style="width:25px;left:50px;text-align:center;"><i class="fa fa-flag" style="color:#94a3b8;"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="file_no" style="width:120px;left:75px;">File No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="hbl_no" style="width:150px;left:230px;">HB/L No.</th>
                                    <th data-col="event" style="width:110px;">Latest Event</th>
                                    <th data-col="journey" style="width:180px;">Journey</th>
                                    <th data-col="event_date" style="width:130px;">Latest Event Date</th>
                                    <th data-col="mbl_no" style="width:170px;">MB/L No.</th>
                                    <th data-col="consignee" style="width:150px;">Consignee</th>
                                    <th data-col="package" style="width:100px;">Package</th>
                                    <th data-col="weight" style="width:100px;">Weight</th>
                                    <th data-col="measure" style="width:100px;">Measurement</th>
                                    <th data-col="hold" style="width:45px;text-align:center;">Hold</th>
                                    <th data-col="it_no" style="width:90px;">IT No.</th>
                                    <th data-col="obl" style="width:80px;">OB/L</th>
                                    <th data-col="ar" style="width:100px;text-align:right;">AR Balance</th>
                                    <th data-col="ap" style="width:100px;text-align:right;">AP Balance</th>
                                    <th data-col="dc" style="width:100px;text-align:right;">DC Balance</th>
                                </tr>
                                {{-- Filter Row --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:50px;"></td>
                                    <td class="sticky-col" style="left:75px;"><input class="input-inline" style="width:100%;height:18px;font-size:9px;" data-col-idx="3" placeholder="File No…" oninput="applyFilters()"></td>
                                    <td class="sticky-col" style="left:195px;"></td>
                                    <td class="sticky-col" style="left:230px;"><input class="input-inline" style="width:100%;height:18px;font-size:9px;" data-col-idx="5" placeholder="HB/L…"   oninput="applyFilters()"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input class="input-inline" style="width:100%;height:18px;font-size:9px;" data-col-idx="9"  placeholder="MB/L…"       oninput="applyFilters()"></td>
                                    <td><input class="input-inline" style="width:100%;height:18px;font-size:9px;" data-col-idx="10" placeholder="Consignee…"  oninput="applyFilters()"></td>
                                    <td colspan="9"></td>
                                </tr>
                            </thead>
                            <tbody id="grid-body">
                                @include('ocean-export.partials.hbl-list-rows')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── PAGINATION ── --}}
                                 <div class="portlet-tool bottom">
                                     <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                                         <div id="pagination-container">
                                             {{ $hbls->appends(request()->query())->links('vendor.pagination.custom') }}
                                         </div>
                                         <div style="font-size:10px;color:#64748b;">
                                             Showing <span id="stat-first">{{ $hbls->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $hbls->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $hbls->total() }}</span> records
                                         </div>
                                     </div>
                                 </div>

        </div>
    </div>

    {{-- Hidden iframe for Excel download --}}
    <iframe id="excel-frame" style="display:none;"></iframe>

    @push('scripts')
    <script>
    var COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' },
    ];
    var _colorShipmentId = null;

    function getCSRF() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
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

    /* ================================================================
       GET SELECTED IDS
    ================================================================ */
    function getSelectedIds() {
        var checked = document.querySelectorAll('.row-check:checked');
        var ids = [];
        for (var i = 0; i < checked.length; i++) {
            var row = checked[i].closest('tr[data-id]');
            if (row && row.dataset.id) {
                ids.push(row.dataset.id);
            }
        }
        return ids;
    }

    /* ================================================================
       UPDATE GRID - AJAX REFRESH
    ================================================================ */
    function updateGrid() {
        var url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        
        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            if (!response.ok) {
                return response.json().then(function(err) {
                    throw new Error(err.error || 'HTTP ' + response.status);
                });
            }
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                document.getElementById('grid-body').innerHTML = data.html;
                document.getElementById('pagination-container').innerHTML = data.pagination;
                document.getElementById('stat-first').textContent = data.first;
                document.getElementById('stat-last').textContent = data.last;
                document.getElementById('stat-total').textContent = data.total;
                updateToolbar();
            } else {
                showToast('error', data.message || 'Failed to refresh');
            }
        })
        .catch(function(err) {
            console.error(err);
            showToast('error', 'Failed to update grid');
        });
    }

    /* ================================================================
       TOOLBAR & SELECTION
    ================================================================ */
    function updateToolbar() {
        var checked = document.querySelectorAll('.row-check:checked');
        var all = document.querySelectorAll('.row-check');
        var n = checked.length;
        var sa = document.getElementById('select-all');
        
        if (sa) {
            sa.checked = n === all.length && all.length > 0;
            sa.indeterminate = n > 0 && n < all.length;
        }
        
        var btns = ['btn-delete', 'btn-copy', 'btn-block', 'btn-unblock', 'btn-profit', 'btn-arrival', 'sel-sales', 'sel-op'];
        for (var i = 0; i < btns.length; i++) {
            var el = document.getElementById(btns[i]);
            if (el) el.disabled = n === 0;
        }
        
        var copyBtn = document.getElementById('btn-copy');
        if (copyBtn) copyBtn.disabled = n !== 1;
        
        var badge = document.getElementById('sel-badge');
        if (badge) {
            badge.style.display = n > 0 ? 'inline' : 'none';
            badge.textContent = n + ' selected';
        }
        
        var rows = document.querySelectorAll('#grid-body tr[data-id]');
        for (var i = 0; i < rows.length; i++) {
            var cb = rows[i].querySelector('.row-check');
            if (cb && cb.checked) {
                rows[i].classList.add('row-selected');
            } else {
                rows[i].classList.remove('row-selected');
            }
        }
    }

    function toggleSelectAll(el) {
        var cbs = document.querySelectorAll('.row-check');
        for (var i = 0; i < cbs.length; i++) {
            cbs[i].checked = el.checked;
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

    /* ================================================================
       LOCK/FLAG TOGGLES
    ================================================================ */
    function toggleLock(el) {
        var row = el.closest('tr');
        var id = row.dataset.id;
        var locked = el.classList.contains('fa-lock');
        var action = locked ? 'unblock' : 'block';
        var url = action === 'block' 
            ? '{{ route("ocean-export.hbl-bulk-block") }}' 
            : '{{ route("ocean-export.hbl-bulk-unblock") }}';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: [id] })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                el.classList.toggle('fa-lock', !locked);
                el.classList.toggle('fa-unlock', locked);
                el.style.color = locked ? '#22c55e' : '#94a3b8';
                el.title = locked ? 'Unlock' : 'Lock';
                showToast('success', locked ? 'HBL unlocked' : 'HBL locked');
            } else {
                showToast('error', data.message || 'Failed to update');
            }
        }).catch(function() { showToast('error', 'Failed to update lock status'); });
    }

    function toggleFlag(el, id) {
        var isRed = el.style.color === 'rgb(239, 68, 68)' || el.style.color === '#ef4444';
        el.style.color = isRed ? '#e2e8f0' : '#ef4444';
        el.title = isRed ? 'Regular' : 'E-commerce';
        showToast('info', isRed ? 'Flag removed' : 'Flag added');
    }

    /* ================================================================
       DELETE
    ================================================================ */
    function copySelected() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length !== 1) return;
        var row = checked[0].closest('tr');
        var hblId = row.dataset.id;
        var shipmentId = row.dataset.shipmentId;
        var hblNo = row.dataset.hblNo;
        showToast('info', 'Copying HBL: ' + (hblNo || '') + '...');
        setTimeout(function() {
            window.location.href = '{{ route("ocean-export.create") }}?copy_hbl=' + hblId + '&shipment_id=' + shipmentId;
        }, 600);
    }

    function confirmDelete() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        document.getElementById('confirm-msg').textContent =
            'You are about to permanently delete ' + ids.length + ' HBL(s). This cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    
    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
    }
    
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        showToast('info', 'Deleting...');
        fetch('{{ route("ocean-export.hbl-bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, _method: 'DELETE' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                showToast('success', d.message || 'Deleted successfully');
                setTimeout(function() { updateGrid(); }, 600);
            } else {
                showToast('error', d.message || 'Failed to delete');
            }
        })
        .catch(function() { showToast('error', 'Delete failed'); });
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        showToast('info', 'Blocking HBL(s)...');
        
        fetch('{{ route("ocean-export.hbl-bulk-block") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                for (var i = 0; i < ids.length; i++) {
                    var row = document.querySelector('tr[data-id="' + ids[i] + '"]');
                    if (row) {
                        var icon = row.querySelector('td:nth-child(2) i');
                        if (icon) {
                            icon.classList.remove('fa-unlock');
                            icon.classList.add('fa-lock');
                            icon.style.color = '#94a3b8';
                            icon.title = 'Lock';
                        }
                    }
                }
                showToast('success', d.message || 'HBL(s) blocked');
                updateToolbar();
            } else {
                showToast('error', d.message || 'Failed to block');
            }
        })
        .catch(function() { showToast('error', 'Block operation failed'); });
    }

    function unblockSelected() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        showToast('info', 'Unblocking HBL(s)...');
        
        fetch('{{ route("ocean-export.hbl-bulk-unblock") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                for (var i = 0; i < ids.length; i++) {
                    var row = document.querySelector('tr[data-id="' + ids[i] + '"]');
                    if (row) {
                        var icon = row.querySelector('td:nth-child(2) i');
                        if (icon) {
                            icon.classList.remove('fa-lock');
                            icon.classList.add('fa-unlock');
                            icon.style.color = '#22c55e';
                            icon.title = 'Unlock';
                        }
                    }
                }
                showToast('success', d.message || 'HBL(s) unblocked');
                updateToolbar();
            } else {
                showToast('error', d.message || 'Failed to unblock');
            }
        })
        .catch(function() { showToast('error', 'Unblock operation failed'); });
    }

    /* ================================================================
       PROFIT / ARRIVAL
    ================================================================ */
    function profitReport() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        window.location.href = '/report/volume-profit?ids=' + ids.join(',');
    }

    function arrivalNotice() {
        var ids = getSelectedIds();
        if (!ids.length) {
            showToast('error', 'Please select at least one HBL');
            return;
        }
        if (ids.length > 1) {
            showToast('error', 'Please select only one HBL for Arrival Notice');
            return;
        }
        showToast('info', 'Opening HBL details...');
        var checked = document.querySelectorAll('.row-check:checked');
        var row = checked[0].closest('tr');
        var shipmentId = row.dataset.shipmentId;
        if (shipmentId) {
            var url = '/ocean-export/' + shipmentId + '/edit';
            window.open(url, '_blank');
        } else {
            showToast('error', 'Shipment ID not found');
        }
    }

    /* ================================================================
       CHANGE SALES / OP
    ================================================================ */
    function changeSales(selectEl) {
        var ids = getSelectedIds();
        var sales_person_id = selectEl.value;
        if (!ids.length || !sales_person_id) return;
        
        showToast('info', 'Changing sales person...');
        
        fetch('{{ route("ocean-export.bulk-change-sales") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, sales_person_id: sales_person_id })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast('success', d.message || 'Sales changed');
            setTimeout(function() { updateGrid(); }, 600);
            selectEl.selectedIndex = 0;
        })
        .catch(function() {
            showToast('error', 'Failed to change sales');
            selectEl.selectedIndex = 0;
        });
    }
    
    function changeOp(selectEl) {
        var ids = getSelectedIds();
        var op_id = selectEl.value;
        if (!ids.length || !op_id) return;
        
        showToast('info', 'Changing operator...');
        
        fetch('{{ route("ocean-export.bulk-change-op") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, op_id: op_id, type: 'hbl', hbl_ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast('success', d.message || 'Operator changed');
            setTimeout(function() { updateGrid(); }, 600);
            selectEl.selectedIndex = 0;
        })
        .catch(function() {
            showToast('error', 'Failed to change operator');
            selectEl.selectedIndex = 0;
        });
    }

    /* ================================================================
       QUICK SEARCH
    ================================================================ */
    var searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function() {
            var q = val.trim();
            var url = new URL(window.location.href);
            if (!q) {
                url.searchParams.delete('search');
            } else {
                url.searchParams.set('search', q);
            }
            url.searchParams.delete('page');
            window.history.replaceState({}, '', url.toString());
            updateGrid();
        }, 400);
    }

    /* ================================================================
       FILTER
    ================================================================ */
    var _filterOpen = false;
    function toggleFilter() {
        _filterOpen = !_filterOpen;
        var panel = document.getElementById('advanced-filter');
        if (panel) {
            panel.style.display = _filterOpen ? 'block' : 'none';
            document.getElementById('btn-filter').classList.toggle('active-filter', _filterOpen);
        }
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'flag', 'file_no', 'color', 'hbl_no'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        var open = panel.style.display === 'none' || panel.style.display === '';
        panel.style.display = open ? 'block' : 'none';
        document.getElementById('btn-config').classList.toggle('active', open);
        if (open) buildConfigPanel();
    }
    
    function buildConfigPanel() {
        var container = document.getElementById('col-toggles');
        container.innerHTML = '';
        var headers = document.querySelectorAll('#header-row th[data-col]');
        for (var i = 0; i < headers.length; i++) {
            var th = headers[i];
            if (PINNED_COLS.indexOf(th.dataset.col) !== -1) continue;
            var label = document.createElement('label');
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.dataset.col = th.dataset.col;
            cb.onchange = function() {
                toggleColumn(this.dataset.col, this.checked);
            };
            label.appendChild(cb);
            label.appendChild(document.createTextNode(' ' + th.textContent.trim()));
            container.appendChild(label);
        }
    }
    
    function toggleColumn(colName, show) {
        var th = document.querySelector('#header-row th[data-col="' + colName + '"]');
        var allCells = th.parentElement.children;
        var idx = -1;
        for (var i = 0; i < allCells.length; i++) {
            if (allCells[i] === th) {
                idx = i;
                break;
            }
        }
        th.style.display = show ? '' : 'none';
        var allRows = document.querySelectorAll('#grid-body tr, #filter-row');
        for (var i = 0; i < allRows.length; i++) {
            var cells = allRows[i].querySelectorAll('td, th');
            if (cells[idx]) cells[idx].style.display = show ? '' : 'none';
        }
    }
    
    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn = document.getElementById('btn-config');
        if (panel && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ================================================================
       HBL QUICK VIEW
    ================================================================ */
    function showHblQuickView(id, hblNo) {
        showToast('info', 'HBL Quick View: ' + hblNo);
    }

    /* ================================================================
       COLOR PICKER
    ================================================================ */
    function openColorPicker(id, currentColor) {
        _colorShipmentId = id;
        var grid = document.getElementById('color-picker-grid');
        var html = '';
        for (var i = 0; i < COLOR_OPTIONS.length; i++) {
            var o = COLOR_OPTIONS[i];
            var active = o.value === currentColor;
            html += '<div class="color-picker-opt ' + (active ? 'active' : '') + '" onclick="selectColor(\'' + o.value + '\', this)">';
            html += '<span class="swatch" style="background:' + o.value + '"></span>';
            html += '<span>' + o.label + '</span>';
            html += '<i class="fa fa-check"></i></div>';
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
        var id = _colorShipmentId;
        fetch('{{ route("ocean-export.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
            body: JSON.stringify({ color: color }),
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var rows = document.querySelectorAll('tr[data-shipment-id="' + id + '"]');
                for (var i = 0; i < rows.length; i++) {
                    var span = rows[i].querySelector('.color-mark');
                    if (span) span.style.background = color;
                }
                showToast('success', 'Status color updated');
            }
        }).catch(function() { showToast('error', 'Failed to update color'); });
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorShipmentId = null;
    }

    function clearColor() {
        var id = _colorShipmentId;
        fetch('{{ route("ocean-export.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
            body: JSON.stringify({ color: '' }),
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var rows = document.querySelectorAll('tr[data-shipment-id="' + id + '"]');
                for (var i = 0; i < rows.length; i++) {
                    var span = rows[i].querySelector('.color-mark');
                    if (span) span.style.background = '#94a3b8';
                }
                showToast('success', 'Status color cleared');
            }
        }).catch(function() { showToast('error', 'Failed to clear color'); });
        closeColorPicker();
    }

    /* ================================================================
       EXCEL EXPORT
    ================================================================ */
    function exportExcel() {
        showToast('info', 'Preparing Excel export...');
        var baseUrl = '{{ route("ocean-export.export-csv") }}';
        var params = new URLSearchParams(window.location.search);
        var queryString = params.toString();
        var url = baseUrl + (queryString ? '?' + queryString : '');
        var iframe = document.getElementById('excel-frame');
        if (iframe) {
            iframe.src = url;
            setTimeout(function() {
                showToast('success', 'Excel file download started');
            }, 500);
        } else {
            showToast('error', 'Excel frame not found');
        }
    }

    /* ================================================================
       INITIALIZE ON PAGE LOAD
    ================================================================ */
    document.addEventListener('DOMContentLoaded', function() {
        updateToolbar();
        
        // Populate sales select
        var salesSelect = document.getElementById('sel-sales');
        if (salesSelect && !salesSelect.dataset.populated) {
            salesSelect.dataset.populated = '1';
            var sales = @json($salesPersons ?? []);
            salesSelect.innerHTML = '<option value="">Change Sales</option>';
            for (var id in sales) {
                if (sales.hasOwnProperty(id)) {
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = sales[id];
                    salesSelect.appendChild(option);
                }
            }
            salesSelect.onchange = function() { changeSales(this); };
        }
        
        // Populate OP select
        var opSelect = document.getElementById('sel-op');
        if (opSelect && !opSelect.dataset.populated) {
            opSelect.dataset.populated = '1';
            var ops = @json($operators ?? []);
            opSelect.innerHTML = '<option value="">Change OP</option>';
            for (var id in ops) {
                if (ops.hasOwnProperty(id)) {
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = ops[id];
                    opSelect.appendChild(option);
                }
            }
            opSelect.onchange = function() { changeOp(this); };
        }
        
        // Attach button handlers
        var profitBtn = document.getElementById('btn-profit');
        if (profitBtn) profitBtn.onclick = profitReport;
        
        var arrivalBtn = document.getElementById('btn-arrival');
        if (arrivalBtn) arrivalBtn.onclick = arrivalNotice;
        
        if (document.getElementById('quick-search')) {
            var params = new URLSearchParams(window.location.search);
            if (params.has('search')) {
                document.getElementById('quick-search').value = params.get('search');
            }
        }
    });

    /* ================================================================
       SESSION MESSAGES
    ================================================================ */
    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif
    </script>
    {{-- ═══════════════════════ COLOR PICKER MODAL ═══════════════════════ --}}
    <div class="overlay color-picker-overlay" id="color-picker-overlay" onclick="if(event.target===this) closeColorPicker()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-paint-brush" style="color:#3b82f6;"></i> Status Color</div>
                <button class="modal-close" onclick="closeColorPicker()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <div class="color-picker-grid" id="color-picker-grid">
                </div>
                <div class="color-clear-btn" onclick="clearColor()">Clear / No Color</div>
            </div>
        </div>
    </div>
    @endpush
</x-layout>
