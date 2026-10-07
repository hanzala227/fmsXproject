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
                min-width: 1400px !important;
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

    {{-- ═══════════════════════ TOAST CONTAINER ═══════════════════════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════════════════ DELETE CONFIRM MODAL ═══════════════════════ --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon" style="color:#ef4444;"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Booking(s)?</h4>
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

    {{-- ═══════════════════════ CONVERT TO SHIPMENT MODAL ═══════════════════════ --}}
    <div class="overlay" id="convert-overlay" onclick="if(event.target===this) closeConvert()">
        <div class="confirm-box">
            <div class="confirm-icon" style="color:#3b82f6;"><i class="fa fa-ship"></i></div>
            <h4>Convert to Shipment?</h4>
            <p id="convert-msg">Selected booking(s) will be converted to ocean export shipments.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConvert()">Cancel</button>
                <button class="btn-tool green" style="padding:0 18px;height:26px;" onclick="executeConvert()">
                    <i class="fa fa-ship"></i> Convert
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ CHANGE SALES/OP MODAL ═══════════════════════ --}}
    <div class="overlay" id="change-user-overlay" onclick="if(event.target===this) closeChangeUser()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-user" style="color:#3b82f6;"></i> <span id="change-user-title">Change Sales Person</span></div>
                <button class="modal-close" onclick="closeChangeUser()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="min-width:300px;">
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Select User</label>
                    <select id="change-user-select" class="form-control" style="height:30px;font-size:11px;">
                        <option value="">-- Select --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-right" style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-tool" onclick="closeChangeUser()">Cancel</button>
                    <button class="btn-tool green" onclick="executeChangeUser()">Update</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ MAIN PAGE ═══════════════════════ --}}
    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Ocean Export <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Booking List</span></li>
            </ul>
        </div>
        

        <div class="portlet light">
            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Booking List</span>
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

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('ocean-bookings.create') }}" title="New Booking">
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
                        <button class="btn-tool" id="btn-convert" disabled style="padding:0 12px;" onclick="confirmConvert()">
                            <i class="fa fa-ship"></i> Convert to shipment
                        </button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <form id="bulk-form" method="POST" action="{{ route('ocean-bookings.bulk-delete') }}" style="margin:0;">
                @csrf
                <div class="portlet-body">
                    <div class="grid-container">
                        <div class="grid-wrapper">
                            <table class="grid-table" id="main-grid">
                                <thead>
                                    <tr id="header-row">
                                        <th class="sticky-col sticky-col-header" data-col="check"       style="width:25px;text-align:center;">
                                            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                        </th>
                                        <th class="sticky-col sticky-col-header" data-col="booking_no"  style="width:130px;left:25px;">Booking No.</th>
                                        <th class="sticky-col sticky-col-header" data-col="customer"    style="width:140px;left:183px;">Customer</th>
                                        <th data-col="office"        style="width:70px;">Office</th>
                                        <th data-col="carrier"       style="width:120px;">Carrier</th>
                                        <th data-col="carrier_bkg"   style="width:120px;">Carrier Bkg. No.</th>
                                        <th data-col="agent"        style="width:120px;">Agent</th>
                                        <th data-col="vessel"       style="width:120px;">Vessel Name</th>
                                        <th data-col="voyage"       style="width:80px;">Voyage</th>
                                        <th data-col="etd"          style="width:85px;">ETD</th>
                                        <th data-col="eta"          style="width:85px;">ETA</th>
                                        <th data-col="pol"          style="width:140px;">Port of Loading</th>
                                        <th data-col="pod"          style="width:140px;">Port of Discharge</th>
                                        <th data-col="por"          style="width:130px;">Place of Receipt</th>
                                        <th data-col="del"          style="width:130px;">Place of Delivery</th>
                                        <th data-col="op"           style="width:90px;">OP</th>
                                        <th data-col="sales"        style="width:90px;">Sales</th>
                                        <th data-col="status"       style="width:80px;">Status</th>
                                        <th data-col="booking_date" style="width:85px;">Booking Date</th>
                                        <th data-col="incoterms"    style="width:80px;">Incoterms</th>
                                        <th data-col="container"    style="width:100px;">Container</th>
                                        <th data-col="pkg_qty"      style="width:70px;text-align:right;">Pkg Qty</th>
                                        <th data-col="weight"       style="width:70px;text-align:right;">Weight</th>
                                        <th data-col="measure"      style="width:70px;text-align:right;">Measure</th>
                                    </tr>

                                    {{-- ── FILTER ROW ── --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td class="sticky-col" style="left:0;"></td>
                                        <td class="sticky-col" style="left:25px;"><input class="filter-input" data-col-idx="1" placeholder="Booking…" oninput="applyFilters()"></td>
                                        <td class="sticky-col" style="left:155px;"></td>
                                        <td class="sticky-col" style="left:183px;"><input class="filter-input" data-col-idx="3" placeholder="Customer…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="4"  placeholder="Office…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="5"  placeholder="Carrier…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="6"  placeholder="Bkg No…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="7"  placeholder="Agent…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="8"  placeholder="Vessel…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="9"  placeholder="Voyage…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="10" placeholder="ETD…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="11" placeholder="ETA…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="12" placeholder="POL…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="13" placeholder="POD…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="14" placeholder="POR…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="15" placeholder="DEL…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="16" placeholder="OP…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="17" placeholder="Sales…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="18" placeholder="Status…" oninput="applyFilters()"></td>
                                        <td colspan="6"></td>
                                    </tr>
                                </thead>

                                <tbody id="grid-body">
                                    @include('ocean-export.partials.booking-list-rows')
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            {{-- ── PAGINATION ── --}}
                                 <div class="portlet-tool bottom">
                                     <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                                         <div id="pagination-container">{{ $bookings->links('vendor.pagination.custom') }}</div>
                                         <div style="font-size:10px;color:#64748b;">
                                             Showing <span id="stat-first">{{ $bookings->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $bookings->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $bookings->total() }}</span> records
                                         </div>
                                     </div>
                                 </div>
        </div>
    </div>


    {{-- Hidden iframe for Excel download --}}
    <iframe id="excel-frame" style="display:none;"></iframe>

    @push('scripts')
    <script>
    /* ================================================================
       GLOBAL VARIABLES & HELPERS
    ================================================================ */
    var COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' }
    ];
    var _colorBookingId = null;
    var _changeMode = 'sales';
    var filterOpen = false;
    var searchDebounce = null;
    var filterDebounce = null;

    function getCSRF() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

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
       AJAX GRID UPDATE
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
        
        document.getElementById('btn-delete').disabled = n === 0;
        document.getElementById('btn-copy').disabled = n !== 1;
        document.getElementById('btn-convert').disabled = n === 0;
        document.getElementById('bulk-sales-select').disabled = n === 0;
        document.getElementById('bulk-op-select').disabled = n === 0;
        
        var badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent = n + ' selected';
        
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
       DELETE
    ================================================================ */
    function confirmDelete() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        document.getElementById('confirm-msg').textContent =
            'You are about to permanently delete ' + ids.length + ' booking(s). This cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    
    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
    }
    
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        fetch('{{ route("ocean-bookings.bulk-delete") }}', {
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
                showToast('success', d.message);
                setTimeout(function() { updateGrid(); }, 600);
            } else {
                showToast('error', d.message || 'Delete failed.');
            }
        })
        .catch(function() { showToast('error', 'Failed to delete booking(s).'); });
    }

    /* ================================================================
       COPY — navigate to create page with ?copy=id
    ================================================================ */
    function copySelected() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length !== 1) return;
        var row = checked[0].closest('tr');
        showToast('info', 'Copying booking: ' + (row.dataset.booking || '') + ' ...');
        setTimeout(function() {
            window.location.href = '{{ route("ocean-bookings.create") }}?copy=' + row.dataset.id;
        }, 600);
    }

    /* ================================================================
       CONVERT TO SHIPMENT
    ================================================================ */
    function confirmConvert() {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('convert-msg').textContent =
            'Convert ' + n + ' booking(s) to ocean export shipment(s)?';
        document.getElementById('convert-overlay').classList.add('open');
    }
    
    function closeConvert() {
        document.getElementById('convert-overlay').classList.remove('open');
    }
    
    function executeConvert() {
        closeConvert();
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        fetch('{{ route("ocean-bookings.convert-to-shipment") }}', {
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
                showToast('success', d.message);
                if (d.ids && d.ids.length === 1) {
                    setTimeout(function() {
                        window.location.href = '/ocean-export/' + d.ids[0] + '/edit';
                    }, 800);
                } else {
                    setTimeout(function() { updateGrid(); }, 600);
                }
            } else {
                showToast('error', d.message || 'Conversion failed.');
            }
        })
        .catch(function() { showToast('error', 'Failed to convert booking(s).'); });
    }

    /* ================================================================
       CHANGE SALES / OP
    ================================================================ */
    function onBulkSalesChange(el) {
        var val = el.value;
        if (!val) return;
        _changeMode = 'sales';
        document.getElementById('change-user-title').textContent = 'Change Sales Person';
        document.getElementById('change-user-overlay').classList.add('open');
        var sel = document.getElementById('change-user-select');
        sel.value = val;
        el.value = '';
    }

    function onBulkOpChange(el) {
        var val = el.value;
        if (!val) return;
        _changeMode = 'op';
        document.getElementById('change-user-title').textContent = 'Change OP';
        document.getElementById('change-user-overlay').classList.add('open');
        var sel = document.getElementById('change-user-select');
        sel.value = val;
        el.value = '';
    }

    function closeChangeUser() {
        document.getElementById('change-user-overlay').classList.remove('open');
    }

    function executeChangeUser() {
        var userId = document.getElementById('change-user-select').value;
        if (!userId) { showToast('error', 'Please select a user.'); return; }
        
        var ids = getSelectedIds();
        if (!ids.length) { closeChangeUser(); return; }
        
        var url = _changeMode === 'sales'
            ? '{{ route("ocean-bookings.bulk-change-sales") }}'
            : '{{ route("ocean-bookings.bulk-change-op") }}';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, user_id: userId })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                showToast('success', d.message);
                setTimeout(function() { updateGrid(); }, 600);
            } else {
                showToast('error', d.message || 'Update failed.');
            }
        })
        .catch(function() { showToast('error', 'Failed to update.'); });
        closeChangeUser();
    }

    /* ================================================================
       FILTER ROW TOGGLE
    ================================================================ */
    function toggleFilter() {
        filterOpen = !filterOpen;
        document.getElementById('filter-row').style.display = filterOpen ? 'table-row' : 'none';
        document.getElementById('btn-filter').classList.toggle('active', filterOpen);
        if (filterOpen) {
            var first = document.querySelector('.filter-input');
            if (first) first.focus();
        } else {
            var inputs = document.querySelectorAll('.filter-input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].value = '';
            }
            applyFilters();
        }
    }

    /* ================================================================
       QUICK SEARCH
    ================================================================ */
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function() {
            var url = new URL(window.location.href);
            if (val.trim()) {
                url.searchParams.set('search', val.trim());
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page');
            window.history.replaceState({}, '', url.toString());
            updateGrid();
        }, 400);
    }

    /* ================================================================
       FILTER INPUTS
    ================================================================ */
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(function() {
            var url = new URL(window.location.href);
            var inputs = document.querySelectorAll('#filter-row .filter-input');
            
            for (var i = 0; i < inputs.length; i++) {
                var inp = inputs[i];
                var idx = inp.dataset.colIdx;
                var val = inp.value.trim();
                var paramMap = {
                    '1': 'filter_booking_no', '3': 'filter_customer', '4': 'filter_office',
                    '5': 'filter_carrier', '6': 'filter_carrier_bkg_no', '7': 'filter_agent',
                    '8': 'filter_vessel', '9': 'filter_voyage', '10': 'filter_etd',
                    '11': 'filter_eta', '12': 'filter_pol', '13': 'filter_pod',
                    '14': 'filter_por', '15': 'filter_del', '16': 'filter_op',
                    '17': 'filter_sales', '18': 'filter_status'
                };
                var param = paramMap[idx];
                if (param) {
                    if (val) {
                        url.searchParams.set(param, val);
                    } else {
                        url.searchParams.delete(param);
                    }
                }
            }
            
            url.searchParams.delete('page');
            window.history.replaceState({}, '', url.toString());
            updateGrid();
        }, 400);
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'booking_no', 'color', 'customer'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        var open = panel.style.display === 'none';
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
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].querySelectorAll('td, th');
            if (cells[idx]) {
                cells[idx].style.display = show ? '' : 'none';
            }
        }
    }

    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn = document.getElementById('btn-config');
        if (panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ================================================================
       COLOR PICKER
    ================================================================ */
    function openColorPicker(id, currentColor) {
        _colorBookingId = id;
        var grid = document.getElementById('color-picker-grid');
        var html = '';
        
        for (var i = 0; i < COLOR_OPTIONS.length; i++) {
            var opt = COLOR_OPTIONS[i];
            var active = opt.value === currentColor ? ' active' : '';
            html += '<div class="color-picker-opt' + active + '" onclick="selectColor(\'' + opt.value + '\', this)">' +
                    '<span class="swatch" style="background:' + opt.value + '"></span>' +
                    '<span>' + opt.label + '</span>' +
                    '<i class="fa fa-check"></i></div>';
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
        
        var id = _colorBookingId;
        var url = '{{ route("ocean-bookings.color", "ID") }}'.replace('ID', id);
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF()
            },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var span = document.querySelector('#booking-row-' + id + ' .color-mark');
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            }
        })
        .catch(function() { showToast('error', 'Failed to update color'); });
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorBookingId = null;
    }

    function clearColor() {
        var id = _colorBookingId;
        var url = '{{ route("ocean-bookings.color", "ID") }}'.replace('ID', id);
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRF()
            },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var span = document.querySelector('#booking-row-' + id + ' .color-mark');
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            }
        })
        .catch(function() { showToast('error', 'Failed to clear color'); });
        closeColorPicker();
    }

    /* ================================================================
       EXCEL EXPORT
    ================================================================ */
    function exportExcel() {
        showToast('info', 'Preparing Excel export...');
        var baseUrl = '{{ route("ocean-bookings.export-csv") }}';
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
       INIT
    ================================================================ */
    document.addEventListener('DOMContentLoaded', function() {
        updateToolbar();
        
        var searchInput = document.getElementById('quick-search');
        if (searchInput && searchInput.value) {
            searchInput.focus();
        }
    });

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif
    </script>
    @endpush
</x-layout>
