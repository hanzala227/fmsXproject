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
            .portlet-tool > div { width: 100%; }
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
            .input-inline { 
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
                min-width: 1600px !important; /* Ensures horizontal scroll */
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
            .grid-table th:nth-child(5), .grid-table td:nth-child(5) {
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
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) {
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

    {{-- ═══════════════════════ MBL QUICK-VIEW MODAL ═══════════════════════ --}}
    <div class="overlay" id="mbl-overlay" onclick="if(event.target===this) closeMbl()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-ship" style="color:#3b82f6;"></i> MBL Quick View</div>
                <button class="modal-close" onclick="closeMbl()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" id="mbl-body"></div>
        </div>
    </div>

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

    {{-- ═══════════════════════ MAIN PAGE ═══════════════════════ --}}
    <div class="page-content">

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Ocean Import <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Shipment List</span></li>
            </ul>
        </div>
        


        <div class="portlet light">

            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">My Shipment List</span>
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
                    <button class="btn-action-round white" onclick="exportExcel()" title="Download as CSV/Excel" id="btn-excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('ocean-import.create') }}" title="New Shipment">
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
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                </div>
            </div>

            {{-- ── BULK-ACTION FORM + TABLE ── --}}
            <form id="bulk-form" method="POST" action="{{ route('ocean-import.bulk-delete') }}" style="margin:0;">
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
                                        <th class="sticky-col sticky-col-header" data-col="hbl"     style="width:40px;left:50px;">HB/L</th>
                                        <th class="sticky-col sticky-col-header" data-col="file_no" style="width:110px;left:90px;">Booking no</th>
                                        <th class="sticky-col sticky-col-header" data-col="mbl_no"  style="width:150px;left:200px;">MB/L No.</th>
                                        <th data-col="tracking"     style="width:140px;">Tracking EDI Response</th>
                                        <th data-col="sub_status"   style="width:100px;">Submit Status</th>
                                        <th data-col="shipment_status" style="width:120px;">
    Shipment Status
</th>
                                        <th data-col="office"       style="width:80px;">Office</th>
                                        <th data-col="ct"           style="width:35px;text-align:right;">CT</th>
                                        <th data-col="cont_qty"     style="width:100px;">Container/Qty</th>
                                        <th data-col="consignee"    style="width:150px;">Consignee</th>
                                        <th data-col="etd"          style="width:75px;">ETD</th>
                                        <th data-col="eta"          style="width:75px;">ETA</th>
                                        <th data-col="obl"          style="width:70px;">O. B/L</th>
                                        <th data-col="mbl"          style="width:70px;">M. B/L</th>
                                        <th data-col="pol"          style="width:130px;">Port of Loading</th>
                                        <th data-col="pod"          style="width:130px;">Port of Discharge</th>
                                        <th data-col="del"          style="width:130px;">Place of Delivery</th>
                                        <th data-col="fdest"        style="width:130px;">Final Destination</th>
                                        <th data-col="oa"           style="width:120px;">Oversea Agent</th>
                                        <th data-col="customer"     style="width:150px;">Customer</th>
                                        <th data-col="sales"        style="width:100px;">Sales</th>
                                        <th data-col="pieces"       style="width:80px;text-align:right;">Total Pieces</th>
                                        <th data-col="weight"       style="width:80px;text-align:right;">Total Weight</th>
                                        <th data-col="volume"       style="width:80px;text-align:right;">Total Volume</th>
                                        <th data-col="frt_mbl"      style="width:100px;">Frt. Term (MB/L)</th>
                                        <th data-col="frt_hbl"      style="width:100px;">Frt. Term (HB/L)</th>
                                        <th data-col="lfd"          style="width:75px;">LFD</th>
                                        <th data-col="vessel"       style="width:130px;">Vessel / Voyage</th>
                                        <th data-col="cont_no"      style="width:120px;">Container No.</th>
                                        <th data-col="mbl_type"     style="width:100px;">M. B/L Type</th>
                                        <th data-col="sub_bl"       style="width:100px;">Sub B/L No.</th>
                                        <th data-col="available"    style="width:75px;">Available</th>
                                        <th data-col="go_date"      style="width:75px;">G.O Date</th>
                                        <th data-col="trucker"      style="width:120px;">Trucker</th>
                                        <th data-col="entry_no"     style="width:100px;">Entry No.</th>
                                        <th data-col="pod_eta"      style="width:75px;">P.O.D ETA</th>
                                        <th data-col="do_no"        style="width:100px;">D.O. No</th>
                                        <th data-col="final_eta"    style="width:75px;">Final ETA</th>
                                        <th data-col="rel_no"       style="width:100px;">Release No</th>
                                        <th data-col="hold"         style="width:60px;text-align:center;">Hold</th>
                                        <th data-col="entry_doc"    style="width:75px;">Entry DOC Sent</th>
                                        <th data-col="ams_no"       style="width:100px;">AMS No.</th>
                                        <th data-col="isf_no"       style="width:100px;">ISF No.</th>
                                        <th data-col="incoterms"    style="width:100px;">Incoterms</th>
                                        <th data-col="post_date"    style="width:75px;">Post Date</th>
                                    </tr>
                                    
                                    {{-- ── FILTER ROW (hidden by default) ── --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td class="sticky-col" style="left:0;"></td>
                                        <td class="sticky-col" style="left:25px;"></td>
                                        <td class="sticky-col" style="left:50px;"></td>
                                        <td class="sticky-col" style="left:90px;"><input class="filter-input" data-param="filter_file_no" placeholder="Booking No..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td class="sticky-col" style="left:200px;"></td>
                                        <td class="sticky-col" style="left:200px;"><input class="filter-input" data-param="filter_mbl_no" placeholder="MB/L..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td></td>
                                        <td></td>
                                        <td><input class="filter-input" data-param="filter_office" placeholder="Office..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td>
    @php
        $shipmentStatus = $shipment->status ?? '--';
    @endphp

    <span class="badge-status">
        {{ ucfirst(str_replace('_', ' ', $shipmentStatus)) }}
    </span>
</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><input class="filter-input" data-param="filter_consignee" placeholder="Consignee..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td><input class="filter-input" data-param="filter_etd" placeholder="ETD..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td><input class="filter-input" data-param="filter_eta" placeholder="ETA..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td></td><td></td>
                                        <td><input class="filter-input" data-param="filter_pol" placeholder="POL..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td><input class="filter-input" data-param="filter_pod" placeholder="POD..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td></td><td></td><td></td>
                                        <td><input class="filter-input" data-param="filter_customer" placeholder="Customer..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td><input class="filter-input" data-param="filter_sales" placeholder="Sales..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                        <td colspan="23"></td>
                                        <td style="text-align:center;"><button class="btn-tool green" onclick="applyFilters()" style="height:18px;">Filter</button></td>
                                    </tr>
                                </thead>

                                <tbody id="grid-body">
                                @include('ocean-import.partials.list-rows')
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            {{-- ── PAGINATION ── --}}
                                 <div class="portlet-tool bottom">
                                     <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                                         <div id="pagination-container">{{ $shipments->links('vendor.pagination.custom') }}</div>
                                         <div style="font-size:10px;color:#64748b;">
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
        sa.checked        = n === all.length && all.length > 0;
        sa.indeterminate  = n > 0 && n < all.length;

        document.getElementById('btn-delete').disabled  = n === 0;
        document.getElementById('btn-copy').disabled    = n !== 1;
        document.getElementById('btn-block').disabled   = n === 0;
        document.getElementById('btn-unblock').disabled = n === 0;

        const badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent   = n + ' selected';

        // highlight selected rows
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
        fetch('{{ route("ocean-import.bulk-delete") }}', {
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
       COPY — navigate to create page with ?copy=id
    ================================================================ */
    var _isCopying = false;
    function copySelected() {
        if (_isCopying) return;
        const checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        _isCopying = true;
        const btn = document.getElementById('btn-copy');
        if (btn) btn.disabled = true;
        const row = checked[0].closest('tr');
        showToast('info', 'Copying shipment: ' + (row.dataset.file || '') + ' ...');
        window.location.href = '{{ route("ocean-import.create") }}?copy=' + row.dataset.id;
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const ids = checked.map(cb => cb.value);
        if (!ids.length) return;
        fetch('{{ route('ocean-import.bulk-block') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids })
        })
        .then(r => r.json())
        .then(d => { if (d.success) { showToast('success', d.message); updateGrid(window.location.href); } else showToast('error', d.message); })
        .catch(() => showToast('error', 'Failed to block shipment(s).'));
    }
    function unblockSelected() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const ids = checked.map(cb => cb.value);
        if (!ids.length) return;
        fetch('{{ route('ocean-import.bulk-unblock') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids })
        })
        .then(r => r.json())
        .then(d => { if (d.success) { showToast('success', d.message); updateGrid(window.location.href); } else showToast('error', d.message); })
        .catch(() => showToast('error', 'Failed to unblock shipment(s).'));
    }

    /* ================================================================
       LOCK ICON TOGGLE (per-row visual with backend update)
    ================================================================ */
    function toggleLock(el) {
        const row = el.closest('tr');
        const id = row.dataset.id;
        const locked = el.classList.contains('fa-lock');
        const action = locked ? 'unblock' : 'block';
        const url = action === 'block' 
            ? '{{ route("ocean-import.bulk-block") }}' 
            : '{{ route("ocean-import.bulk-unblock") }}';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: [id] })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                el.classList.toggle('fa-lock', !locked);
                el.classList.toggle('fa-unlock', locked);
                el.style.color = locked ? '#22c55e' : '#94a3b8';
                el.title = locked ? 'Unlock' : 'Lock';
                showToast('success', locked ? 'Shipment unlocked' : 'Shipment locked');
            } else {
                showToast('error', data.message || 'Failed to update');
            }
        }).catch(() => showToast('error', 'Failed to update lock status'));
    }

    /* ================================================================
       FILTER ROW TOGGLE
    ================================================================ */
    function toggleFilter() {
        var filterRow = document.getElementById('filter-row');
        var isVisible = filterRow.style.display === 'table-row';
        filterRow.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);
        
        if (!isVisible) {
            const params = new URLSearchParams(window.location.search);
            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                const param = inp.dataset.param;
                if (param) {
                    const val = params.get(param);
                    if (val) inp.value = val;
                }
            });
            document.querySelector('#filter-row .filter-input')?.focus();
        } else {
            document.querySelectorAll('#filter-row .filter-input').forEach(i => { i.value = ''; });
            applyFilters();
        }
    }

    /* ================================================================
        AJAX GRID UPDATE
    ================================================================ */
    async function updateGrid(url) {
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            if (!data.html) throw new Error('Invalid response: missing html');
            
            document.getElementById('grid-body').innerHTML = data.html;
            document.getElementById('pagination-container').innerHTML = data.pagination || '';
            document.getElementById('stat-first').textContent = data.first || 0;
            document.getElementById('stat-last').textContent = data.last || 0;
            document.getElementById('stat-total').textContent = data.total || 0;
            
            updateToolbar();
        } catch (e) {
            console.error('updateGrid error:', e);
            showToast('error', 'Failed to update grid');
        }
    }

    /* ================================================================
       EXCEL EXPORT WITHOUT HARD REFRESH
    ================================================================ */
    function exportExcel() {
        showToast('info', 'Preparing Excel export...');
        
        // Build URL with current filters
        const url = new URL('{{ route("ocean-import.export-csv") }}', window.location.origin);
        
        // Copy search param
        const searchVal = document.getElementById('quick-search')?.value?.trim();
        if (searchVal) url.searchParams.set('search', searchVal);
        
        // Copy all filter params
        document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
            const v = inp.value?.trim();
            const param = inp.dataset.param;
            if (param && v) url.searchParams.set(param, v);
        });
        
        // Create hidden iframe for download (no page reload)
        let iframe = document.getElementById('download-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'download-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        
        iframe.src = url.toString();
        
        // Show success after brief delay
        setTimeout(() => {
            showToast('success', 'Excel file downloaded!');
        }, 1000);
    }

    // Wire pagination links to use AJAX instead of full page loads
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a.tp-page-btn, .tp-pagination a.tp-page-btn');
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
            url.searchParams.delete('page');
            if (!q) url.searchParams.delete('search'); else url.searchParams.set('search', q);
            updateGrid(url.toString());
        }, 400);
    }

    var filterDebounce;
    
    function applyFiltersTyping() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(applyFilters, 400);
    }
    
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            var url = new URL(window.location.href);
            url.search = '';

            var searchVal = document.getElementById('quick-search')?.value?.trim();
            if (searchVal) url.searchParams.set('search', searchVal);

            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                var v = inp.value?.trim();
                var param = inp.dataset.param;
                if (param && v) url.searchParams.set(param, v);
            });

            updateGrid(url.toString());
        }, 200);
    }


    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'hbl', 'file_no', 'color', 'mbl_no'];

    function toggleConfig() {
        const panel = document.getElementById('config-panel');
        if (!panel) return;
        const open  = panel.style.display === 'none' || panel.style.display === '';
        panel.style.display = open ? 'block' : 'none';
        const btn = document.getElementById('btn-config');
        if (btn) btn.classList.toggle('active', open);
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
        const idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(row => {
            const cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

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
        fetch('{{ route("ocean-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#shipment-row-${id} .color-mark`);
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            } else {
                showToast('error', data.message || 'Failed to update color');
            }
        }).catch(err => {
            console.error('Color update error:', err);
            showToast('error', 'Failed to update color');
        });
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorShipmentId = null;
    }

    function clearColor() {
        if (!_colorShipmentId) return;
        const id = _colorShipmentId;
        fetch('{{ route("ocean-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#shipment-row-${id} .color-mark`);
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            } else {
                showToast('error', data.message || 'Failed to clear color');
            }
        }).catch(err => {
            console.error('Color clear error:', err);
            showToast('error', 'Failed to clear color');
        });
        closeColorPicker();
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
       MBL QUICK-VIEW MODAL
    ================================================================ */
    function showMbl(d) {
        const rows = [
            ['Booking No.',         d.file_no],
            ['MBL No.',          d.mbl_no],
            ['Carrier',          d.carrier],
            ['Vessel / Voyage',  d.vessel],
            ['Port of Loading',  d.pol],
            ['Port of Discharge',d.pod],
            ['ETD',              d.etd],
            ['ETA',              d.eta],
            ['O. B/L Type',      d.obl_type],
            ['M. B/L Type',      d.bl_type],
            ['Containers',       d.containers],
            ['HBLs',             d.hbls],
        ];
        let html = rows.map(([l, v]) =>
            `<div class="mbl-row"><span class="lbl">${l}</span><span class="val">${v ?? '--'}</span></div>`
        ).join('');

        if (d.routes && Array.isArray(d.routes) && d.routes.length > 0) {
            html += `<div style="margin-top:15px; border-top:1px solid #e2e8f0; padding-top:10px;">
                <div style="font-weight:bold; font-size:12px; color:#32c5d2; margin-bottom:6px;"><i class="fa fa-ship"></i> Route / Transportation</div>
                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #cbd5e1; text-align:left;">
                            <th style="padding:4px;">Type</th>
                            <th style="padding:4px;">ETA</th>
                            <th style="padding:4px;">ATA</th>
                            <th style="padding:4px;">ETD</th>
                            <th style="padding:4px;">ATD</th>
                            <th style="padding:4px;">Vessel / Flight</th>
                        </tr>
                    </thead>
                    <tbody>`;
            d.routes.forEach((r, idx) => {
                const typeLabel = idx === 0 ? 'Departure' : (idx === d.routes.length - 1 && d.routes.length > 1 ? 'Final Destination' : 'Trans ' + idx);
                html += `<tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:4px; font-weight:600;">${typeLabel}</td>
                    <td style="padding:4px;">${r.eta || '--'}</td>
                    <td style="padding:4px;">${r.ata || '--'}</td>
                    <td style="padding:4px;">${r.etd || '--'}</td>
                    <td style="padding:4px;">${r.atd || '--'}</td>
                    <td style="padding:4px;">${r.flight_no || '--'}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        document.getElementById('mbl-body').innerHTML = html;
        document.getElementById('mbl-overlay').classList.add('open');
    }
    function closeMbl() {
        document.getElementById('mbl-overlay').classList.remove('open');
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

    /* ================================================================
       INITIAL STATE - Show filters if URL has params
    ================================================================ */
    (function() {
        var params = new URLSearchParams(window.location.search);
        if (params.toString()) {
            document.getElementById('btn-filter').classList.add('active');
            document.getElementById('filter-row').style.display = 'table-row';
        }
    })();
    </script>
    @endpush
</x-layout>
