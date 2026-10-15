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
        
        /* Mobile Responsive Enhancements - OPTIMIZED FOR SMOOTH SCROLLING */
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
                min-width: 1600px !important;
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

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ═══════════════════════ TOAST CONTAINER ═══════════════════════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════════════════ MBL QUICK-VIEW MODAL ═══════════════════════ --}}
    <div class="overlay" id="mbl-overlay" onclick="if(event.target===this) closeMbl()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-plane" style="color:#3b82f6;"></i> MAWB Quick View</div>
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
                    <button class="btn-action-round white" onclick="exportCsv()" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('air-export.create') }}" title="New Shipment">
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
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;" placeholder="Quick search…" oninput="quickSearch(this.value)">
                </div>
            </div>

            {{-- ── ADVANCED FILTER FORM ── --}}
            <div id="advanced-filter" style="display:none;background:#f0f4ff;padding:6px 8px;border-bottom:1px solid #bfdbfe;">
                <form method="GET" action="{{ route('air-export.index') }}" style="display:flex;flex-wrap:wrap;gap:6px;align-items:end;margin:0;">
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
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}" {{ request('carrier_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Departure</label>
                        <select name="dep_port_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All Dep</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('dep_port_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Destination</label>
                        <select name="dst_port_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All Dst</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('dst_port_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
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
                        <a href="{{ route('air-export.index') }}" class="btn-tool" style="height:20px;font-size:9px;padding:0 10px;" target="_blank">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── BULK-ACTION FORM + TABLE ── --}}
            <form id="bulk-form" method="POST" action="{{ route('air-export.bulk-delete') }}" style="margin:0;">
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
                                        <th class="sticky-col sticky-col-header" data-col="file_no" style="width:110px;left:50px;">Booking No.</th>
                                        <th class="sticky-col sticky-col-header" data-col="color"   style="width:35px;left:160px;text-align:center;">CR</th>
                                        <th class="sticky-col sticky-col-header" data-col="mawb_no" style="width:150px;left:195px;">MAWB No.</th>
                                        <th data-col="status"     style="width:70px;">Status</th>
                                        <th data-col="flight_no"  style="width:80px;">Flight No.</th>
                                        <th data-col="hbl_count"  style="width:40px;text-align:right;">HBL</th>
                                        <th data-col="office"     style="width:80px;">Office</th>
                                        <th data-col="shipper"    style="width:150px;">Shipper</th>
                                        <th data-col="etd"        style="width:75px;">ETD</th>
                                        <th data-col="atd"        style="width:75px;">ATD</th>
                                        <th data-col="eta"        style="width:75px;">ETA</th>
                                        <th data-col="ata"        style="width:75px;">ATA</th>
                                        <th data-col="departure"  style="width:130px;">Departure</th>
                                        <th data-col="destination" style="width:130px;">Destination</th>
                                        <th data-col="oa"         style="width:150px;">Oversea Agent</th>
                                        <th data-col="customer"   style="width:150px;">Customer</th>
                                        <th data-col="operator"   style="width:100px;">Operator</th>
                                        <th data-col="gw"         style="width:70px;text-align:right;">G.W (KG)</th>
                                        <th data-col="cw"         style="width:70px;text-align:right;">C.W (KG)</th>
                                        <th data-col="frt_term"   style="width:90px;">Frt. Term</th>
                                        <th data-col="post_date"  style="width:75px;">Post Date</th>
                                        <th data-col="created_at" style="width:75px;">Created</th>
                                    </tr>

                                    {{-- ── FILTER ROW (hidden by default) ── --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td class="sticky-col" style="left:0;"></td>
                                        <td class="sticky-col" style="left:25px;"></td>
                                        <td class="sticky-col" style="left:50px;"><input class="filter-input" data-col-idx="2" placeholder="Booking No.." oninput="applyFilters()"></td>
                                        <td class="sticky-col" style="left:160px;"></td>
                                        <td class="sticky-col" style="left:160px;"><input class="filter-input" data-col-idx="4" placeholder="MAWB.." oninput="applyFilters()"></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><input class="filter-input" data-col-idx="8" placeholder="Office.." oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="9" placeholder="Shipper.." oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="10" placeholder="ETD.." oninput="applyFilters()"></td>
                                        <td></td>
                                        <td><input class="filter-input" data-col-idx="12" placeholder="ETA.." oninput="applyFilters()"></td>
                                        <td></td>
                                        <td><input class="filter-input" data-col-idx="14" placeholder="Dep.." oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="15" placeholder="Dst.." oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="16" placeholder="OA.." oninput="applyFilters()"></td>
                                        <td><input class="filter-input" data-col-idx="17" placeholder="Customer.." oninput="applyFilters()"></td>
                                        <td colspan="6"></td>
                                    </tr>
                                </thead>

                                <tbody id="grid-body">
                                @forelse($shipments as $shipment)
                                    @php
                                        $isBlocked = $shipment->is_blocked ?? false;
                                        $statusLabel = $isBlocked ? 'Blocked' : 'Open';
                                        $badgeClass = $isBlocked ? 'bg-red' : 'bg-green';
                                    @endphp
                                    <tr id="shipment-row-{{ $shipment->id }}"
                                        data-id="{{ $shipment->id }}"
                                        data-file="{{ $shipment->file_no }}"
                                        data-mawb="{{ $shipment->mawb_no }}"
                                        data-carrier="{{ $shipment->carrier->name ?? '' }}"
                                        data-flight="{{ $shipment->flight_no ?? '' }}"
                                        data-dep="{{ $shipment->depPort->name ?? '--' }}"
                                        data-dst="{{ $shipment->dstPort->name ?? '--' }}"
                                        data-etd="{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}"
                                        data-eta="{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}"
                                        data-shipper="{{ $shipment->shipper->name ?? '--' }}"
                                        data-oa="{{ $shipment->overseaAgent->name ?? '--' }}"
                                        data-customer="{{ $shipment->dmCustomer->name ?? '--' }}"
                                        data-operator="{{ $shipment->operator->name ?? '--' }}"
                                        data-hbls="{{ $shipment->hbls->count() }}"
                                        onclick="rowClick(event, this)">
                                        <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="ids[]" value="{{ $shipment->id }}" class="row-check" onchange="updateToolbar()">
                                        </td>
                                        <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
                                            <i class="fa {{ $isBlocked ? 'fa-lock' : 'fa-unlock' }}" 
                                               style="color:{{ $isBlocked ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
                                               title="{{ $isBlocked ? 'Lock' : 'Unlock' }}" 
                                               onclick="toggleLock(this)"></i>
                                        </td>
                                        <td class="sticky-col" style="left:50px;" onclick="event.stopPropagation()">
                                            <a href="{{ route('air-export.edit', $shipment->id) }}" class="col-link">{{ $shipment->file_no }}</a>
                                        </td>
                                        <td class="sticky-col" style="left:160px;" onclick="event.stopPropagation()">
                                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                                <span>{{ $shipment->mawb_no ?: '--' }}</span>
                                                <i class="fa fa-eye"
                                                   style="color:#3b82f6;cursor:pointer;font-size:10px;"
                                                   title="Quick View MAWB"
                                                   onclick="event.stopPropagation();showMbl({
                                                       file_no:    '{{ addslashes($shipment->file_no) }}',
                                                       mawb_no:    '{{ addslashes($shipment->mawb_no ?? '--') }}',
                                                       carrier:    '{{ addslashes($shipment->carrier->name ?? '--') }}',
                                                       flight_no:  '{{ addslashes($shipment->flight_no ?? '--') }}',
                                                       dep:        '{{ addslashes($shipment->depPort->name ?? '--') }}',
                                                       dst:        '{{ addslashes($shipment->dstPort->name ?? '--') }}',
                                                       etd:        '{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}',
                                                       eta:        '{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}',
                                                       shipper:    '{{ addslashes($shipment->shipper->name ?? '--') }}',
                                                       oa:         '{{ addslashes($shipment->overseaAgent->name ?? '--') }}',
                                                       customer:   '{{ addslashes($shipment->dmCustomer->name ?? '--') }}',
                                                       operator:   '{{ addslashes($shipment->operator->name ?? '--') }}',
                                                       hbls:       {{ $shipment->hbls->count() }},
                                                       routes:     @json($shipment->routes ?? [])
                                                   })"></i>
                                            </div>
                                        </td>
                                        <td><span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                                        <td><span class="badge-status bg-blue">{{ $shipment->flight_no ?: '--' }}</span></td>
                                        <td style="text-align:right;">{{ $shipment->hbls->count() }}</td>
                                        <td>{{ $shipment->office->code ?? '--' }}</td>
                                        <td>{{ $shipment->shipper->name ?? '--' }}</td>
                                        <td>{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}</td>
                                        <td>{{ $shipment->atd ? $shipment->atd->format('m-d-Y') : '--' }}</td>
                                        <td>{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}</td>
                                        <td>{{ $shipment->ata ? $shipment->ata->format('m-d-Y') : '--' }}</td>
                                        <td>{{ $shipment->depPort->name ?? '--' }}</td>
                                        <td>{{ $shipment->dstPort->name ?? '--' }}</td>
                                        <td>{{ $shipment->overseaAgent->name ?? '--' }}</td>
                                        <td>{{ $shipment->dmCustomer->name ?? '--' }}</td>
                                        <td>{{ $shipment->operator->name ?? '--' }}</td>
                                        <td style="text-align:right;">{{ number_format($shipment->gross_weight ?? 0, 2) }}</td>
                                        <td style="text-align:right;">{{ number_format($shipment->chargeable_weight ?? 0, 2) }}</td>
                                        <td>{{ $shipment->freight_term ?? '--' }}</td>
                                        <td>{{ $shipment->post_date ? $shipment->post_date->format('m-d-Y') : '--' }}</td>
                                        <td>{{ $shipment->created_at ? $shipment->created_at->format('m-d-Y') : '--' }}</td>
                                    </tr>
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No shipments found.
                                        </td>
                                    </tr>
                                @endforelse
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
                        Showing <span id="stat-first">{{ $shipments->firstItem() ?? 0 }}</span> &ndash; <span id="stat-last">{{ $shipments->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $shipments->total() }}</span> records
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var searchTimer, filterTimer;
    var _colorId = null;

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

        document.getElementById('btn-delete').disabled  = n === 0;
        document.getElementById('btn-copy').disabled    = n !== 1;
        document.getElementById('btn-block').disabled   = n === 0;
        document.getElementById('btn-unblock').disabled = n === 0;

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
        var skip = ['A', 'INPUT', 'BUTTON', 'I'];
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
            var response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            var html = await response.text();
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');

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
            console.error(e);
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
        2: 'filter_file_no', 4: 'filter_mawb_no', 8: 'filter_office',
        9: 'filter_shipper', 10: 'filter_etd', 12: 'filter_eta',
        14: 'filter_dep', 15: 'filter_dst', 16: 'filter_oa', 17: 'filter_customer'
    };

    function applyFilters() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            Object.values(FILTER_MAP).forEach(function(p) { url.searchParams.delete(p); });
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
       FILTER TOGGLE
    ================================================================ */
    var filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        var row = document.getElementById('filter-row');
        if (row) {
            row.style.display = filterOpen ? 'table-row' : 'none';
            document.getElementById('btn-filter').classList.toggle('active', filterOpen);
        }
        if (!filterOpen) {
            document.querySelectorAll('.filter-input').forEach(function(i) { i.value = ''; });
            applyFilters();
        } else {
            document.querySelector('.filter-input')?.focus();
        }
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'file_no', 'color', 'mawb_no'];

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
        document.getElementById('confirm-msg').textContent = 'Delete ' + n + ' shipment(s)? This cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Deleting...');
        fetch('{{ route("air-export.bulk-delete") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { showToast('success', d.message); setTimeout(function() { updateGrid(window.location.href); }, 600); }
            else showToast('error', d.message || 'Delete failed.');
        })
        .catch(function() { showToast('error', 'Delete failed.'); });
    }

    /* ================================================================
       COPY
    ================================================================ */
    function copySelected() {
        var checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        var row = checked[0].closest('tr[data-id]');
        if (!row) return;
        showToast('info', 'Copying shipment: ' + (row.dataset.file || '') + ' ...');
        setTimeout(function() {
            window.location.href = '{{ route("air-export.create") }}?copy=' + row.dataset.id;
        }, 600);
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected()   { bulkAction('{{ route("air-export.bulk-block") }}', 'Blocked'); }
    function unblockSelected() { bulkAction('{{ route("air-export.bulk-unblock") }}', 'Unblocked'); }

    function bulkAction(url, label) {
        var ids = getSelectedIds();
        if (!ids.length) return;
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { 
                showToast('success', d.message); 
                // Update lock icons for affected rows
                ids.forEach(function(id) {
                    var row = document.getElementById('shipment-row-' + id);
                    if (row) {
                        var lockIcon = row.querySelector('.fa-lock, .fa-unlock');
                        if (lockIcon) {
                            var shouldLock = label === 'Blocked';
                            lockIcon.classList.toggle('fa-lock', shouldLock);
                            lockIcon.classList.toggle('fa-unlock', !shouldLock);
                            lockIcon.style.color = shouldLock ? '#94a3b8' : '#22c55e';
                            lockIcon.title = shouldLock ? 'Lock' : 'Unlock';
                        }
                        // Update status badge
                        var statusBadge = row.querySelector('.badge-status');
                        if (statusBadge) {
                            statusBadge.textContent = shouldLock ? 'Blocked' : 'Open';
                            statusBadge.className = 'badge-status ' + (shouldLock ? 'bg-red' : 'bg-green');
                        }
                    }
                });
                updateToolbar();
            }
            else showToast('error', d.message || label + ' failed.');
        })
        .catch(function() { showToast('error', label + ' failed.'); });
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
            ? '{{ route("air-export.bulk-block") }}' 
            : '{{ route("air-export.bulk-unblock") }}';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: [id] })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                el.classList.toggle('fa-lock', !locked);
                el.classList.toggle('fa-unlock', locked);
                el.style.color = locked ? '#22c55e' : '#94a3b8';
                el.title = locked ? 'Unlock' : 'Lock';
                // Update status badge
                var statusBadge = row.querySelector('.badge-status');
                if (statusBadge) {
                    statusBadge.textContent = locked ? 'Open' : 'Blocked';
                    statusBadge.className = 'badge-status ' + (locked ? 'bg-green' : 'bg-red');
                }
                showToast('success', locked ? 'Shipment unlocked' : 'Shipment locked');
            } else {
                showToast('error', data.message || 'Failed to update');
            }
        }).catch(function() { showToast('error', 'Failed to update lock status'); });
    }

    /* ================================================================
       MBL QUICK-VIEW MODAL
    ================================================================ */
    function showMbl(d) {
        var rows = [
            ['Booking No.',     d.file_no],
            ['MAWB No.',     d.mawb_no],
            ['Carrier',      d.carrier],
            ['Flight No.',   d.flight_no],
            ['Departure',    d.dep],
            ['Destination',  d.dst],
            ['ETD',          d.etd],
            ['ETA',          d.eta],
            ['Shipper',      d.shipper],
            ['Oversea Agent',d.oa],
            ['Customer',     d.customer],
            ['Operator',     d.operator],
            ['HBLs',         d.hbls],
        ];
        var html = rows.map(function(r) {
            return '<div class="mbl-row"><span class="lbl">' + r[0] + '</span><span class="val">' + (r[1] ?? '--') + '</span></div>';
        }).join('');

        if (d.routes && Array.isArray(d.routes) && d.routes.length > 0) {
            html += '<div style="margin-top:15px; border-top:1px solid #e2e8f0; padding-top:10px;">';
            html += '<div style="font-weight:bold; font-size:12px; color:#32c5d2; margin-bottom:6px;"><i class="fa fa-plane"></i> Route / Flight Details</div>';
            html += '<table style="width:100%; border-collapse:collapse; font-size:11px;">';
            html += '<thead><tr style="background:#f8fafc; border-bottom:1px solid #cbd5e1; text-align:left;">';
            html += '<th style="padding:4px;">Type</th><th style="padding:4px;">ETA</th><th style="padding:4px;">ATA</th><th style="padding:4px;">ETD</th><th style="padding:4px;">ATD</th><th style="padding:4px;">Flight No.</th>';
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
                html += '<td style="padding:4px;">' + (r.flight_no || '--') + '</td>';
                html += '</tr>';
            }
            html += '</tbody></table></div>';
        }

        document.getElementById('mbl-body').innerHTML = html;
        document.getElementById('mbl-overlay').classList.add('open');
    }
    function closeMbl() { document.getElementById('mbl-overlay').classList.remove('open'); }

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
        _colorId = id;
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
        var id = _colorId;
        fetch('{{ route("air-export.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var span = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (span) span.style.background = color;
                showToast('success', 'Color updated');
            }
        })
        .catch(function() { showToast('error', 'Color update failed'); });
        closeColorPicker();
    }

    function closeColorPicker() { document.getElementById('color-picker-overlay').classList.remove('open'); _colorId = null; }

    function clearColor() {
        var id = _colorId;
        if (!id) return;
        fetch('{{ route("air-export.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var span = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Color cleared');
            }
        })
        .catch(function() { showToast('error', 'Failed to clear'); });
        closeColorPicker();
    }

    /* ================================================================
       CSV EXPORT (respects current filters)
    ================================================================ */
    function exportCsv() {
        var url = new URL('{{ route("air-export.export-csv") }}');
        // Copy current page's query params (search, filters, etc.)
        var params = new URL(window.location.href).searchParams;
        params.forEach(function(val, key) {
            url.searchParams.set(key, val);
        });
        
        // Show toast notification
        showToast('info', 'Preparing Excel export...');
        
        // Trigger download without page refresh using hidden iframe
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url.toString();
        document.body.appendChild(iframe);
        
        // Remove iframe after download starts (5 seconds)
        setTimeout(function() {
            document.body.removeChild(iframe);
            showToast('success', 'Excel export downloaded');
        }, 5000);
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
