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
            .input-inline { 
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
                min-width: 2000px !important;
            }
            
            .grid-table th, .grid-table td { 
                padding: 2px 4px !important;
                height: 22px !important;
            }
            
            /* Only 2 sticky columns on tablet */
            .sticky-col { 
                font-size: 8px !important;
            }
            
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
                min-width: 1800px !important;
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

    {{-- ═══════════════════════ MAIN PAGE ═══════════════════════ --}}
    <div class="page-content">

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Ocean Export <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Shipment List</span></li>
            </ul>
        </div>
        

        <div class="portlet light">

            {{-- ── PORTLET TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">My Ocean Export List</span>
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
                        <a class="btn-tool green" href="{{ route('ocean-export.create') }}" title="New Shipment">
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
                <form method="GET" action="{{ route('ocean-export.index') }}" style="display:flex;align-items:center;gap:6px;margin:0;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" name="search" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)">
                    @if(request()->has('search'))
                        <a href="{{ route('ocean-export.index') }}" style="font-size:10px;color:#3b82f6;text-decoration:none;" target="_blank">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    @endif
                    <button type="submit" style="display:none;">Search</button>
                </form>
            </div>

            {{-- ── ADVANCED FILTER FORM ── --}}
            <div id="advanced-filter" style="display:none;background:#f0f4ff;padding:6px 8px;border-bottom:1px solid #bfdbfe;">
                <form method="GET" action="{{ route('ocean-export.index') }}" style="display:flex;flex-wrap:wrap;gap:6px;align-items:end;margin:0;">
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Office</label>
                        <select name="office_id" class="input-inline" style="width:100px;height:20px;font-size:9px;">
                            <option value="">All Offices</option>
                            @foreach($offices as $o)
                                <option value="{{ $o->id }}" {{ request('office_id') == $o->id ? 'selected' : '' }}>{{ $o->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Operator</label>
                        <select name="op_id" class="input-inline" style="width:100px;height:20px;font-size:9px;">
                            <option value="">All Ops</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('op_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Carrier</label>
                        <select name="carrier_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All Carriers</option>
                            @foreach($agents->where('type', 'CARRIER') as $c)
                                <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">POL</label>
                        <select name="pol_id" class="input-inline" style="width:120px;height:20px;font-size:9px;" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}">
                            <option value="">All POL</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('pol_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">POD</label>
                        <select name="pod_id" class="input-inline" style="width:120px;height:20px;font-size:9px;" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}">
                            <option value="">All POD</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('pod_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Customer</label>
                        <select name="dm_customer_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All Customers</option>
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}" {{ request('dm_customer_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
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
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">ETA From</label>
                        <input type="date" name="eta_from" class="input-inline" style="width:110px;height:20px;font-size:9px;" value="{{ request('eta_from') }}">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">ETA To</label>
                        <input type="date" name="eta_to" class="input-inline" style="width:110px;height:20px;font-size:9px;" value="{{ request('eta_to') }}">
                    </div>
                    <div style="display:flex;gap:4px;align-self:end;padding-bottom:1px;">
                        <button type="submit" class="btn-tool green" style="height:20px;font-size:9px;padding:0 10px;">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <a href="{{ route('ocean-export.index') }}" class="btn-tool" style="height:20px;font-size:9px;padding:0 10px;" target="_blank">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── BULK-ACTION FORM + TABLE ── --}}
            <form id="bulk-form" method="POST" action="{{ route('ocean-export.bulk-delete') }}" style="margin:0;">
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
                                        <th class="sticky-col sticky-col-header" data-col="mbl_no"  style="width:130px;left:200px;">MB/L No.</th>
                                        <th data-col="tracking"     style="width:140px;">Tracking EDI Response</th>
                                        <th data-col="sub_status"   style="width:100px;">Submit Status</th>
                                        <th data-col="office"       style="width:80px;">Office</th>
                                        <th data-col="hbl_no"       style="width:120px;">HB/L No.</th>
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
                                        <td class="sticky-col" style="left:90px;"><input class="filter-input" data-col-idx="3" placeholder="File No…" oninput="applyFilters()"></td>
                                        <td class="sticky-col" style="left:200px;"><input class="filter-input" data-col-idx="4" placeholder="MB/L…" oninput="applyFilters()"></td>
                                        <td></td>
                                        <td></td>
                                        <td><input class="filter-input" data-col-idx="7"  placeholder="Office…"    oninput="applyFilters()"></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><input class="filter-input" data-col-idx="12" placeholder="Consignee…" oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="13" placeholder="ETD…"       oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="14" placeholder="ETA…"       oninput="applyFilters()"></td>
                                        <td></td><td></td>
                                        <td><input class="filter-input" data-col-idx="17" placeholder="POL…"       oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="18" placeholder="POD…"       oninput="applyFilters()"></td>
                                        <td></td><td></td><td></td>
                                        <td><input class="filter-input" data-col-idx="22" placeholder="Customer…"  oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="23" placeholder="Sales…"     oninput="applyFilters()"></td>
                                        <td colspan="24"></td>
                                    </tr>
                                </thead>

                                <tbody id="grid-body">
                                @include('ocean-export.partials.export-list-rows')
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
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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

        
        document.getElementById('btn-delete').disabled  = n === 0;
        document.getElementById('btn-copy').disabled    = n !== 1;
        document.getElementById('btn-block').disabled   = n === 0;
        document.getElementById('btn-unblock').disabled = n === 0;

        var badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent   = n + ' selected';

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

    function getSelectedIds() {
        var checked = document.querySelectorAll('.row-check:checked');
        var ids = [];
        for (var i = 0; i < checked.length; i++) {
            ids.push(checked[i].value);
        }
        return ids;
    }

    /* ================================================================
       ROW CLICK — click row to toggle checkbox
    ================================================================ */
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
        var n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent =
            'You are about to permanently delete ' + n + ' shipment(s). This cannot be undone.';
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
        fetch('{{ route("ocean-export.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids }),
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                showToast('success', data.message || 'Deleted successfully');
                setTimeout(function() { updateGrid(); }, 600);
            } else {
                showToast('error', data.message || 'Failed to delete');
            }
        }).catch(function() { showToast('error', 'Failed to delete'); });
    }

    /* ================================================================
       COPY — navigate to create page with ?copy=id
    ================================================================ */
    function copySelected() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length !== 1) return;
        var row = checked[0].closest('tr');
        showToast('info', 'Copying shipment: ' + (row.dataset.shipment || '') + ' ...');
        setTimeout(function() {
            window.location.href = '{{ route("ocean-export.create") }}?copy=' + row.dataset.id;
        }, 600);
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        
        showToast('info', 'Blocking shipment(s)...');
        
        fetch('{{ route("ocean-export.bulk-block") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) { 
            if (d.success) {
                // Update lock icons immediately
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
                showToast('success', d.message || 'Shipment(s) blocked');
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
        
        showToast('info', 'Unblocking shipment(s)...');
        
        fetch('{{ route("ocean-export.bulk-unblock") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) { 
            if (d.success) {
                // Update lock icons immediately
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
                showToast('success', d.message || 'Shipment(s) unblocked');
                updateToolbar();
            } else {
                showToast('error', d.message || 'Failed to unblock');
            }
        })
        .catch(function() { showToast('error', 'Unblock operation failed'); });
    }

    /* ================================================================
       LOCK ICON TOGGLE (per-row visual with backend update)
    ================================================================ */
    function toggleLock(el) {
        var row = el.closest('tr');
        var id = row.dataset.id;
        var locked = el.classList.contains('fa-lock');
        var action = locked ? 'unblock' : 'block';
        var url = action === 'block' 
            ? '{{ route("ocean-export.bulk-block") }}' 
            : '{{ route("ocean-export.bulk-unblock") }}';
        
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
                showToast('success', locked ? 'Shipment unlocked' : 'Shipment locked');
            } else {
                showToast('error', data.message || 'Failed to update');
            }
        }).catch(function() { showToast('error', 'Failed to update lock status'); });
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
            window.history.replaceState({}, '', url.toString());
            updateGrid();
        }, 400);
    }

    /* ================================================================
       FILTER — inline row
    ================================================================ */
    var filterDebounce;
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(function() {
            var inputs = document.querySelectorAll('#filter-row .filter-input');
            var url = new URL(window.location.href);
            
            var filterMap = {
                '3': 'filter_file_no', '5': 'filter_mbl_no', '8': 'filter_office',
                '12': 'filter_consignee', '13': 'filter_etd', '14': 'filter_eta',
                '17': 'filter_pol', '18': 'filter_pod', '22': 'filter_customer', '23': 'filter_sales'
            };
            
            for (var k in filterMap) {
                url.searchParams.delete(filterMap[k]);
            }
            
            for (var i = 0; i < inputs.length; i++) {
                var inp = inputs[i];
                var v = inp.value.trim();
                if (!v) continue;
                var param = filterMap[inp.dataset.colIdx];
                if (param) url.searchParams.set(param, v);
            }
            
            window.history.replaceState({}, '', url.toString());
            updateGrid();
        }, 400);
    }

    var _filterOpen = false;
    function toggleFilter() {
        _filterOpen = !_filterOpen;
        var row = document.getElementById('filter-row');
        if (row) {
            row.style.display = _filterOpen ? 'table-row' : 'none';
            document.getElementById('btn-filter').classList.toggle('active-filter', _filterOpen);
        }
    }
    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'hbl', 'file_no', 'color', 'mbl_no'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        if (!panel) return;
        var open  = panel.style.display === 'none' || panel.style.display === '';
        panel.style.display = open ? 'block' : 'none';
        var btn = document.getElementById('btn-config');
        if (btn) btn.classList.toggle('active', open);
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
            var cb    = document.createElement('input');
            cb.type    = 'checkbox';
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
        var th  = document.querySelector('#header-row th[data-col="' + colName + '"]');
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
        var btn   = document.getElementById('btn-config');
        if (panel && btn && panel.style && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ================================================================
       MBL QUICK-VIEW MODAL
    ================================================================ */
    function showMbl(d) {
        var rows = [
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
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var lbl = rows[i][0];
            var val = rows[i][1] || '--';
            html += '<div class="mbl-row"><span class="lbl">' + lbl + '</span><span class="val">' + val + '</span></div>';
        }

        if (d.routes && Array.isArray(d.routes) && d.routes.length > 0) {
            html += '<div style="margin-top:15px; border-top:1px solid #e2e8f0; padding-top:10px;">';
            html += '<div style="font-weight:bold; font-size:12px; color:#32c5d2; margin-bottom:6px;"><i class="fa fa-ship"></i> Route / Transportation</div>';
            html += '<table style="width:100%; border-collapse:collapse; font-size:11px;">';
            html += '<thead><tr style="background:#f8fafc; border-bottom:1px solid #cbd5e1; text-align:left;">';
            html += '<th style="padding:4px;">Type</th><th style="padding:4px;">ETA</th><th style="padding:4px;">ATA</th><th style="padding:4px;">ETD</th><th style="padding:4px;">ATD</th><th style="padding:4px;">Vessel / Flight</th>';
            html += '</tr></thead><tbody>';
            for (var rIdx = 0; rIdx < d.routes.length; rIdx++) {
                var r = d.routes[rIdx];
                var typeLabel = rIdx === 0 ? 'Departure' : (rIdx === d.routes.length - 1 && d.routes.length > 1 ? 'Final Destination' : 'Trans ' + rIdx);
                html += '<tr style="border-bottom:1px solid #f1f5f9;">';
                html += '<td style="padding:4px; font-weight:600;">' + typeLabel + '</td>';
                html += '<td style="padding:4px;">' + (r.eta || '--') + '</td>';
                html += '<td style="padding:4px;">' + (r.ata || '--') + '</td>';
                html += '<td style="padding:4px;">' + (r.etd || '--') + '</td>';
                html += '<td style="padding:4px;">' + (r.atd || '--') + '</td>';
                html += '<td style="padding:4px;">' + (r.vessel_name ? (r.vessel_name + (r.voyage_no ? ' / ' + r.voyage_no : '')) : (r.flight_no || '--')) + '</td>';
                html += '</tr>';
            }
            html += '</tbody></table></div>';
        }

        document.getElementById('mbl-body').innerHTML = html;
        document.getElementById('mbl-overlay').classList.add('open');
    }
    function closeMbl() {
        document.getElementById('mbl-overlay').classList.remove('open');
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
                var span = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
                updateGrid();
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
                var span = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
                updateGrid();
            }
        }).catch(function() { showToast('error', 'Failed to clear color'); });
        closeColorPicker();
    }

    /* ================================================================
       EXCEL EXPORT - NO HARD REFRESH
    ================================================================ */
    function exportExcel() {
        showToast('info', 'Preparing Excel export...');
        
        var baseUrl = '/ocean-export/export-csv';
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
        
        var filterInputs = document.querySelectorAll('.filter-input[data-col-idx]');
        var params = new URLSearchParams(window.location.search);
        
        var filterMap = {
            '3': 'filter_file_no', '5': 'filter_mbl_no', '8': 'filter_office',
            '12': 'filter_consignee', '13': 'filter_etd', '14': 'filter_eta',
            '17': 'filter_pol', '18': 'filter_pod', '22': 'filter_customer', '23': 'filter_sales'
        };
        
        for (var i = 0; i < filterInputs.length; i++) {
            var inp = filterInputs[i];
            var param = filterMap[inp.dataset.colIdx];
            if (param && params.has(param)) {
                inp.value = params.get(param);
            }
        }
        
        if (params.has('search')) {
            document.getElementById('quick-search').value = params.get('search');
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
