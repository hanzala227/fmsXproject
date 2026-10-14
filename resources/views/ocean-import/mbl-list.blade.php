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

    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <div class="page-content">
         <!-- Breadcrumb -->
        <div class="page-bar">
             <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/ocean-import/list">Ocean Import</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Master B/L List</span></li>
            </ul>
        </div>
 

        <div class="portlet light">

            {{-- ── TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Master B/L List</span>
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
                        <table class="grid-table" id="mbl-grid">
                            <thead>
                                <tr id="header-row">
                                         <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;left:0;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="file_no" style="width:110px;left:50px;">Booking No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="color" style="width:35px;left:160px;text-align:center;">Color</th>
                                    <th class="sticky-col sticky-col-header" data-col="mbl_no" style="width:150px;left:195px;">MB/L No.</th>

                                    <th data-col="tracking" style="width:140px;">Tracking EDI Response</th>
                                    <th data-col="sub_status" style="width:100px;">Submit Status</th>
                                    <th data-col="hbl_no" style="width:110px;">HB/L No.</th>
                                    <th data-col="ct" style="width:35px;text-align:right;">CT</th>
                                    <th data-col="cont_qty" style="width:110px;">Container/Qty</th>
                                    <th data-col="etd" style="width:75px;">ETD</th>
                                    <th data-col="eta" style="width:75px;">ETA</th>
                                    <th data-col="pol" style="width:130px;">Port of Loading</th>
                                    <th data-col="pod" style="width:130px;">Port of Discharge</th>
                                    <th data-col="oa" style="width:150px;">Oversea Agent</th>
                                    <th data-col="customer" style="width:150px;">Customer</th>
                                    <th data-col="cont_no" style="width:120px;">Container No.</th>
                                    <th data-col="vessel" style="width:80px;">Vessel</th>
                                    <th data-col="voyage" style="width:70px;">Voyage</th>
                                    <th data-col="obl" style="width:80px;">O. B/L</th>
                                    <th data-col="mbl" style="width:80px;">M. B/L</th>
                                    <th data-col="pieces" style="width:80px;text-align:right;">Total Pieces</th>
                                    <th data-col="weight" style="width:80px;text-align:right;">Total Weight</th>
                                    <th data-col="volume" style="width:80px;text-align:right;">Total Volume</th>
                                    <th data-col="frt_term" style="width:100px;">Frt. Term</th>
                                    <th data-col="post_date" style="width:75px;">Post Date</th>
                                </tr>

                                {{-- Filter Row --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:50px;"><input class="filter-input" style="width:100%;" data-param="filter_file_no" placeholder="Booking No…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td class="sticky-col" style="left:160px;"></td>
                                    <td class="sticky-col" style="left:195px;"><input class="filter-input" style="width:100%;" data-param="filter_mbl_no" placeholder="MB/L…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td colspan="4"></td>
                                    <td></td>
                                    <td><input class="filter-input" data-param="filter_etd" placeholder="ETD…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_eta" placeholder="ETA…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_pol" placeholder="POL…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_pod" placeholder="POD…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td></td>
                                    <td><input class="filter-input" data-param="filter_customer" placeholder="Customer…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td colspan="10"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @forelse($shipments as $shipment)
                                <tr id="shipment-row-{{ $shipment->id }}"
                                    data-id="{{ $shipment->id }}"
                                    data-file="{{ $shipment->file_no }}"
                                    data-mbl="{{ $shipment->mbl_no }}"
                                    data-carrier="{{ $shipment->carrier->name ?? '' }}"
                                    data-vessel="{{ ($shipment->vessel->name ?? '--') . ' / ' . ($shipment->voyage ?? '--') }}"
                                    data-pol="{{ $shipment->portOfLoading->name ?? '--' }}"
                                    data-pod="{{ $shipment->portOfDischarge->name ?? '--' }}"
                                    data-etd="{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}"
                                    data-eta="{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}"
                                    data-obl="{{ $shipment->obl_type ?? '--' }}"
                                    data-bl="{{ $shipment->bl_type ?? '--' }}"
                                    data-containers="{{ $shipment->containers->count() }}"
                                    data-hbls="{{ $shipment->hbls->count() }}"
                                    data-customer="{{ $shipment->dmCustomer->name ?? '--' }}"
                                    onclick="rowClick(event, this)">
                                    <td class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
                                        <input type="checkbox" name="ids[]" value="{{ $shipment->id }}" class="row-check" onchange="updateToolbar()">
                                    </td>
                                    <td class="sticky-col" style="left:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <i class="fa {{ $shipment->is_hold ? 'fa-lock' : 'fa-unlock' }}" 
                                           style="color:{{ $shipment->is_hold ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
                                           title="{{ $shipment->is_hold ? 'Lock' : 'Unlock' }}" 
                                           onclick="toggleLock(this)"></i>
                                    </td>
                                    <td class="sticky-col" style="left:50px;" onclick="event.stopPropagation()">
                                        <div style="display:flex;align-items:center;justify-content:space-between;">
                                            <a href="{{ route('ocean-import.edit', $shipment->id) }}" class="col-link">{{ $shipment->file_no }}</a>
                                            <i class="fa fa-external-link" style="color:#94a3b8;font-size:10px;cursor:pointer;" title="Open"></i>
                                        </div>
                                    </td>
                                    <td class="sticky-col" style="left:160px;text-align:center;">
                                        <span class="color-mark" style="background:{{ $shipment->color ?? '#94a3b8' }}" title="Click to change status color" onclick="event.stopPropagation();openColorPicker({{ $shipment->id }}, '{{ $shipment->color ?? '' }}')"></span>
                                    </td>
                                    <td class="sticky-col" style="left:195px;" onclick="event.stopPropagation()">
                                        <div style="display:flex;align-items:center;justify-content:space-between;">
                                            <span>{{ $shipment->mbl_no }}</span>
                                            <i class="fa fa-eye" style="color:#3b82f6;font-size:10px;cursor:pointer;" title="Quick view MBL" onclick="event.stopPropagation();showMbl({
                                                file_no: '{{ addslashes($shipment->file_no) }}',
                                                mbl_no: '{{ addslashes($shipment->mbl_no) }}',
                                                carrier: '{{ addslashes($shipment->carrier->name ?? '--') }}',
                                                vessel: '{{ addslashes(($shipment->vessel->name ?? '--') . ' / ' . ($shipment->voyage ?? '--')) }}',
                                                pol: '{{ addslashes($shipment->portOfLoading->name ?? '--') }}',
                                                pod: '{{ addslashes($shipment->portOfDischarge->name ?? '--') }}',
                                                etd: '{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}',
                                                eta: '{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}',
                                                obl_type: '{{ addslashes($shipment->obl_type ?? '--') }}',
                                                bl_type: '{{ addslashes($shipment->bl_type ?? '--') }}',
                                                containers: {{ $shipment->containers->count() }},
                                                hbls: {{ $shipment->hbls->count() }}
                                            })"></i>
                                        </div>
                                    </td>

                                    <td>{{ $shipment->updated_at->format('m-d-Y H:i') }}</td>
                                    <td><span class="badge-status bg-green">MATCHED</span></td>
                                    <td>
                                        @if($shipment->hbls->count())
                                            {{ $shipment->hbls->first()->hbl_no }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td style="text-align:right;">{{ $shipment->containers->count() }}</td>
                                    <td>
                                        @foreach($shipment->containers->take(2) as $c)
                                            {{ $c->containerType->code ?? '' }}*1&nbsp;
                                        @endforeach
                                    </td>
                                    <td>{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}</td>
                                    <td>{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}</td>
                                    <td>{{ $shipment->portOfLoading->name ?? '--' }}</td>
                                    <td>{{ $shipment->portOfDischarge->name ?? '--' }}</td>
                                    <td>{{ $shipment->overseaAgent->name ?? '--' }}</td>
                                    <td>{{ $shipment->dmCustomer->name ?? '--' }}</td>
                                    <td>{{ $shipment->containers->first()->container_no ?? '--' }}</td>
                                    <td>{{ $shipment->vessel->name ?? '--' }}</td>
                                    <td>{{ $shipment->voyage ?? '--' }}</td>
                                    <td>{{ $shipment->obl_type ?? '--' }}</td>
                                    <td>{{ $shipment->bl_type ?? '--' }}</td>
                                    <td style="text-align:right;">{{ $shipment->containers->sum('pkg_qty') }}</td>
                                    <td style="text-align:right;">{{ number_format($shipment->containers->sum('weight_kg'), 2) }}</td>
                                    <td style="text-align:right;">{{ number_format($shipment->containers->sum('measure_cbm'), 2) }}</td>
                                    <td>{{ $shipment->freight_term ?? '--' }}</td>
                                    <td>{{ $shipment->post_date ? $shipment->post_date->format('m-d-Y') : '--' }}</td>
                                </tr>
                                @empty
                                <tr id="empty-row">
                                    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No Master B/L found.
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
                                             Showing <span id="stat-first">{{ $shipments->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $shipments->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $shipments->total() }}</span> records
                                         </div>
                                     </div>
                                 </div>

        </div>
    </div>

    @push('scripts')
    <script>
    /* ================================================================
       EXCEL EXPORT WITHOUT HARD REFRESH
    ================================================================ */
    function exportExcel() {
        showToast('info', 'Preparing Excel export...');
        
        const url = new URL('{{ route("ocean-import.export-csv") }}', window.location.origin);
        
        // Copy search param
        const searchVal = document.getElementById('quick-search')?.value?.trim();
        if (searchVal) url.searchParams.set('search', searchVal);
        
        // Copy filter params
        document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
            const v = inp.value?.trim();
            const param = inp.dataset.param;
            if (param && v) url.searchParams.set(param, v);
        });
        
        // Create hidden iframe for download
        let iframe = document.getElementById('download-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'download-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        
        iframe.src = url.toString();
        
        setTimeout(() => {
            showToast('success', 'Excel file downloaded!');
        }, 1000);
    }

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
        ['btn-profit-s','btn-profit-d','btn-arrival','sel-op'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.disabled = n === 0;
        });

        const badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent   = n + ' selected';

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
       ROW CLICK
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
       COPY
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
       LOCK TOGGLE WITH BACKEND UPDATE
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
                el.title = locked ? 'Unlocked' : 'Locked';
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
            
            const data = await response.json();
            
            if (!response.ok || data.success === false) {
                console.error('Server error:', data);
                showToast('error', 'Server error: ' + (data.error || 'Unknown error'));
                if (data.trace) console.error('Stack trace:', data.trace);
                return;
            }
            
            if (!data.html) {
                console.error('Invalid response:', data);
                showToast('error', 'Invalid response: missing html');
                return;
            }
            
            document.getElementById('grid-body').innerHTML = data.html;
            document.getElementById('pagination-container').innerHTML = data.pagination || '';
            document.getElementById('stat-first').textContent = data.first || 0;
            document.getElementById('stat-last').textContent = data.last || 0;
            document.getElementById('stat-total').textContent = data.total || 0;
            
            updateToolbar();
        } catch (e) {
            console.error('updateGrid error:', e);
            showToast('error', 'Failed to update grid: ' + e.message);
        }
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
       CHANGE OP
    ================================================================ */
    function changeOp(sel) {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const ids = checked.map(cb => cb.value);
        if (!ids.length || !sel.value) { sel.value = ''; return; }
        fetch('{{ route('ocean-import.bulk-change-op') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids, op_id: sel.value })
        }).then(r => r.json()).then(d => {
            if (d.success) { showToast('success', d.message); updateGrid(window.location.href); }
            else showToast('error', d.message || 'Failed.');
            sel.value = '';
        }).catch(() => { showToast('error', 'OP change failed.'); sel.value = ''; });
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'file_no', 'color', 'mbl_no'];

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

    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn   = document.getElementById('btn-config');
        if (panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
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
        fetch('{{ route("ocean-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
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
        fetch('{{ route("ocean-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
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

    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn   = document.getElementById('btn-config');
        if (panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ================================================================
    /* ================================================================
       PROFIT REPORTS & ARRIVAL NOTICE
    ================================================================ */
    function profitSummary() {
        const ids = getSelectedIds();
        if (!ids.length) {
            showToast('error', 'Please select at least one shipment');
            return;
        }
        
        showToast('info', 'Opening Revenue/Cost Report...');
        
        // Navigate to revenue-cost report with selected shipment IDs
        const params = new URLSearchParams();
        ids.forEach(id => params.append('shipment_ids[]', id));
        params.set('module', 'ocean_import');
        const url = `/accounting/report/revenue-cost?${params.toString()}`;
        
        // Open in new tab
        window.open(url, '_blank');
    }

    function profitDetail() {
        const ids = getSelectedIds();
        if (!ids.length) {
            showToast('error', 'Please select at least one shipment');
            return;
        }
        
        showToast('info', 'Opening Revenue/Cost Report (Detailed)...');
        
        // Navigate to revenue-cost report with selected shipment IDs
        const params = new URLSearchParams();
        ids.forEach(id => params.append('shipment_ids[]', id));
        params.set('module', 'ocean_import');
        params.set('detailed', '1');
        const url = `/accounting/report/revenue-cost?${params.toString()}`;
        
        // Open in new tab
        window.open(url, '_blank');
    }

    function arrivalNotice() {
        const ids = getSelectedIds();
        if (!ids.length) {
            showToast('error', 'Please select at least one shipment');
            return;
        }
        
        if (ids.length > 1) {
            showToast('error', 'Please select only one shipment for Arrival Notice');
            return;
        }
        
        showToast('info', 'Opening shipment details...');
        
        // Navigate to the first selected shipment's edit page
        // User can generate/print arrival notice from there
        const url = `/ocean-import/${ids[0]}/edit`;
        window.open(url, '_blank');
    }

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
        document.getElementById('mbl-body').innerHTML = rows.map(([l, v]) =>
            `<div class="mbl-row"><span class="lbl">${l}</span><span class="val">${v ?? '--'}</span></div>`
        ).join('');
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
    </script>
    @endpush
</x-layout>
