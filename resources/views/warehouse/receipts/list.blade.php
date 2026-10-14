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
            /* Hide all UI elements except table */
            .page-sidebar-wrapper,
            .page-header,
            .page-bar,
            .portlet-title,
            .portlet-tool,
            .nav-tabs-custom,
            .btn-group,
            .btn-action-round,
            .actions,
            .caption-subject,
            #filter-row,
            .pagination,
            .portlet-tool.bottom,
            body > *:not(.page-wrapper):not(.page-container) {
                display: none !important;
            }
            
            /* Show only the table */
            .page-wrapper,
            .page-container,
            .page-content-wrapper,
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
            
            /* Hide checkboxes, color marks, and action columns */
            th[data-col="check"], td:nth-child(1),
            th[data-col="action"], td:last-child {
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

        /* Mobile Responsive - copied from Ocean Import */
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
            /* Remove sticky from color and all other columns on mobile */
            .grid-table th:nth-child(3), .grid-table td:nth-child(3),
            .grid-table th:nth-child(4), .grid-table td:nth-child(4),
            .grid-table th:nth-child(5), .grid-table td:nth-child(5),
            .grid-table th:nth-child(n+3), .grid-table td:nth-child(n+3) {
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
            
            /* Nav tabs on mobile */
            .nav-tabs-custom {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                flex-wrap: nowrap !important;
            }
            .nav-tabs-custom .tab-item {
                font-size: 9px !important;
                padding: 4px 8px !important;
                white-space: nowrap;
            }
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
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Warehouse Receipt(s)?</h4>
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
            <a href="{{ route('warehouse.receipts.create') }}" class="tab-item {{ request()->routeIs('warehouse.receipts.create') ? 'active' : '' }}">
                <i class="fa fa-plus-circle"></i> New Receipt
            </a>
            <a href="{{ route('warehouse.receipts.index') }}" class="tab-item {{ request()->routeIs('warehouse.receipts.index') ? 'active' : '' }}">
                <i class="fa fa-file-text-o"></i> Receipt List
            </a>
            <a href="{{ route('receiving.create') }}" class="tab-item {{ request()->routeIs('receiving.create') ? 'active' : '' }}">
                <i class="fa fa-plus-circle"></i> New Receiving
            </a>
            <a href="{{ route('receiving.list') }}" class="tab-item {{ request()->routeIs('receiving.list') || request()->routeIs('receiving.index') ? 'active' : '' }}">
                <i class="fa fa-list"></i> Receiving List
            </a>
            <a href="{{ route('shipping.create') }}" class="tab-item {{ request()->routeIs('shipping.create') ? 'active' : '' }}">
                <i class="fa fa-plus-circle"></i> New Shipping
            </a>
            <a href="{{ route('shipping.list') }}" class="tab-item {{ request()->routeIs('shipping.list') || request()->routeIs('shipping.index') ? 'active' : '' }}">
                <i class="fa fa-truck"></i> Shipping List
            </a>
        </div>

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Receipt List</span></li>
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
                    <span class="caption-subject"><i class="fa fa-file-text-o"></i> Warehouse Receipt List</span>
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
                    <a class="btn-action-round white" href="{{ route('warehouse.receipts.export-csv') }}" id="btn-excel" title="Download as CSV/Excel" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:nowrap;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('warehouse.receipts.create') }}" title="New Receipt">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-copy" disabled title="Copy Selected" onclick="copySelected()">
                            <i class="fa fa-files-o"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
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
            <form id="bulk-form" method="POST" action="{{ route('warehouse.receipts.bulk-delete') }}" style="margin:0;">
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
                                        <th class="sticky-col sticky-col-header" data-col="receipt_no" style="width:150px;left:25px;cursor:pointer;" onclick="toggleSort('receipt_no')">
                                            Receipt No. <i class="fa fa-sort" id="sort-receipt_no"></i>
                                        </th>
                                        <th data-col="receipt_date" style="width:130px;cursor:pointer;" onclick="toggleSort('receipt_date')">
                                            Received Date <i class="fa fa-sort" id="sort-receipt_date"></i>
                                        </th>
                                        <th data-col="tracking_no" style="width:130px;cursor:pointer;" onclick="toggleSort('tracking_no')">
                                            Truck B/L <i class="fa fa-sort" id="sort-tracking_no"></i>
                                        </th>
                                        <th data-col="shipper" style="width:160px;">Shipper</th>
                                        <th data-col="consignee" style="width:160px;">Consignee</th>
                                        <th data-col="customer" style="width:160px;">Customer</th>
                                        <th data-col="cargo_type" style="width:90px;cursor:pointer;" onclick="toggleSort('cargo_type')">
                                            Cargo Type <i class="fa fa-sort" id="sort-cargo_type"></i>
                                        </th>
                                        <th data-col="commodity" style="width:180px;">Commodity</th>
                                        <th data-col="location" style="width:100px;">Location</th>
                                        <th data-col="carrier" style="width:120px;">Carrier</th>
                                        <th data-col="office" style="width:80px;">Office</th>
                                        <th data-col="is_hazardous" style="width:60px;text-align:center;">Hazmat</th>
                                        <th data-col="is_heat_treated" style="width:60px;text-align:center;">HT</th>
                                        <th data-col="action" style="width:80px;text-align:center;">Action</th>
                                    </tr>

                                    {{-- FILTER ROW --}}
                                    <tr id="filter-row" style="display:none;">
                                        <td data-col="check" class="sticky-col" style="left:0;"></td>
                                        <td data-col="receipt_no" class="sticky-col" style="left:25px;"><input class="filter-input" data-col-idx="1" placeholder="Receipt No..." oninput="applyFilters()"></td>
                                        <td data-col="receipt_date"><input class="filter-input" data-col-idx="3" placeholder="Date (Y-m-d)..." oninput="applyFilters()"></td>
                                        <td data-col="tracking_no"><input class="filter-input" data-col-idx="4" placeholder="B/L..." oninput="applyFilters()"></td>
                                        <td data-col="shipper"><input class="filter-input" data-col-idx="5" placeholder="Shipper..." oninput="applyFilters()"></td>
                                        <td data-col="consignee"><input class="filter-input" data-col-idx="6" placeholder="Consignee..." oninput="applyFilters()"></td>
                                        <td data-col="customer"><input class="filter-input" data-col-idx="7" placeholder="Customer..." oninput="applyFilters()"></td>
                                        <td data-col="cargo_type">
                                            <select class="filter-input" data-col-idx="8" onchange="applyFilters()" style="height:18px;">
                                                <option value="">All</option>
                                                <option value="OTH">OTH</option>
                                                <option value="MOB">MOB</option>
                                            </select>
                                        </td>
                                        <td data-col="commodity" colspan="3"></td>
                                        <td data-col="office"><input class="filter-input" data-col-idx="12" placeholder="Office..." oninput="applyFilters()"></td>
                                        <td data-col="is_hazardous" colspan="3"></td>
                                    </tr>
                                </thead>

                                <tbody id="grid-body">
                                @forelse($receipts as $receipt)
                                    <tr id="receipt-row-{{ $receipt->id }}"
                                        data-id="{{ $receipt->id }}"
                                        data-receipt="{{ $receipt->receipt_no }}"
                                        onclick="rowClick(event, this)"
                                    >
                                        <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="ids[]" value="{{ $receipt->id }}" class="row-check" onchange="updateToolbar()">
                                        </td>
                                        <td class="sticky-col" style="left:25px;" onclick="event.stopPropagation()">
                                            <a href="{{ route('warehouse.receipts.edit', $receipt->id) }}" class="col-link">{{ $receipt->receipt_no }}</a>
                                        </td>
                                        <td>{{ $receipt->receipt_date ? $receipt->receipt_date->format('m-d-Y') : '--' }}</td>
                                        <td>{{ $receipt->tracking_no ?: '--' }}</td>
                                        <td>{{ $receipt->shipper->name ?? '--' }}</td>
                                        <td>{{ $receipt->consignee->name ?? '--' }}</td>
                                        <td>{{ $receipt->customer->name ?? '--' }}</td>
                                        <td>
                                            <span class="badge-status {{ $receipt->cargo_type === 'MOB' ? 'bg-yellow' : 'bg-gray' }}">{{ $receipt->cargo_type ?: 'OTH' }}</span>
                                        </td>
                                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;" title="{{ $receipt->commodity ?? '' }}">{{ $receipt->commodity ? \Illuminate\Support\Str::limit($receipt->commodity, 40) : '--' }}</td>
                                        <td>{{ $receipt->location_code ?: '--' }}</td>
                                        <td>{{ $receipt->carrier_name ?: '--' }}</td>
                                        <td>{{ $receipt->office->code ?? '--' }}</td>
                                        <td style="text-align:center;">
                                            @if($receipt->is_hazardous)
                                                <i class="fa fa-check" style="color:#22c55e;"></i>
                                            @else
                                                <i class="fa fa-times" style="color:#94a3b8;"></i>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            @if($receipt->is_heat_treated)
                                                <i class="fa fa-check" style="color:#22c55e;"></i>
                                            @else
                                                <i class="fa fa-times" style="color:#94a3b8;"></i>
                                            @endif
                                        </td>
                                        <td style="text-align:center;" onclick="event.stopPropagation()">
                                            <a href="{{ route('warehouse.receipts.edit', $receipt->id) }}" class="btn-tool" style="padding:1px 6px;text-decoration:none;" title="Edit"><i class="fa fa-pencil"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="16" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                            <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                            No warehouse receipts found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            {{-- PAGINATION --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $receipts->links() }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $receipts->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $receipts->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $receipts->total() }}</span> records
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
    const STATE_KEY = 'warehouse_receipts_state';
    const FILTER_MAP = {
        1: 'filter_receipt_no', 3: 'filter_date', 4: 'filter_tracking',
        5: 'filter_shipper', 6: 'filter_consignee', 7: 'filter_customer',
        8: 'filter_cargo_type', 12: 'filter_office'
    };

    /* ================================================================
       TOOLBAR — checkbox management
    ================================================================ */
    function updateToolbar() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const all     = [...document.querySelectorAll('.row-check')];
        const n       = checked.length;
        const sa      = document.getElementById('select-all');
        if (sa) {
            sa.checked       = n === all.length && all.length > 0;
            sa.indeterminate = n > 0 && n < all.length;
        }

        const delBtn = document.getElementById('btn-delete');
        if (delBtn) delBtn.disabled = n === 0;

        const copyBtn = document.getElementById('btn-copy');
        if (copyBtn) copyBtn.disabled = n !== 1;

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
       EXCEL LINK CARRY FILTERS
    ================================================================ */
    function updateExcelLink() {
        const url = new URL(window.location.href);
        const btn = document.getElementById('btn-excel');
        if (btn) btn.href = '{{ route("warehouse.receipts.export-csv") }}' + url.search;
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
        showToast('info', 'Copying receipt: ' + (row.dataset.receipt || '') + ' ...');
        setTimeout(() => {
            window.location.href = '{{ route("warehouse.receipts.create") }}?copy=' + row.dataset.id;
        }, 500);
    }

    /* ================================================================
       DELETE
    ================================================================ */
    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent =
            `You are about to permanently delete ${n} receipt(s). This cannot be undone.`;
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
        fetch('{{ route("warehouse.receipts.bulk-delete") }}', {
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
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Network response was not ok');
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newBody = doc.getElementById('grid-body');
            const newPagination = doc.getElementById('pagination-container');

            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            const stats = doc.querySelector('.portlet-tool.bottom div:last-child');
            if (stats) {
                const matches = stats.textContent.match(/\d+/g);
                if (matches && matches.length >= 3) {
                    document.getElementById('stat-first').textContent = matches[0];
                    document.getElementById('stat-last').textContent = matches[1];
                    document.getElementById('stat-total').textContent = matches[2];
                }
            }

            const urlObj = new URL(url);
            const newSort = urlObj.searchParams.get('sort') || 'created_at';
            const newDir = urlObj.searchParams.get('dir') || 'desc';

            document.querySelectorAll('[id^="sort-"]').forEach(i => i.className = 'fa fa-sort');
            const sortIcon = document.getElementById('sort-' + newSort);
            if (sortIcon) {
                sortIcon.className = 'fa ' + (newDir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');
            }

            const searchVal = urlObj.searchParams.get('search') || '';
            const clearBtn = document.getElementById('clear-search-btn');
            if (clearBtn) clearBtn.style.display = searchVal ? 'inline' : 'none';

            updateExcelLink();
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
    const PINNED_COLS = ['check', 'receipt_no', 'color'];
    const CONFIG_STORAGE_KEY = 'warehouse_receipts_column_config';

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
        { label: 'Pending', value: '#F3C200' },
        { label: 'Completed', value: '#25A69A' },
        { label: 'On Hold', value: '#4B77BE' },
        { label: 'Closed', value: '#9B9B9B' },
    ];

    let _colorReceiptId = null;

    function openColorPicker(id, currentColor) {
        _colorReceiptId = id;
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
        const id = _colorReceiptId;
        fetch('{{ route("warehouse.receipts.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#receipt-row-${id} .color-mark`);
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            }
        }).catch(() => showToast('error', 'Failed to update color'));
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorReceiptId = null;
    }

    function clearColor() {
        const id = _colorReceiptId;
        fetch('{{ route("warehouse.receipts.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#receipt-row-${id} .color-mark`);
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
            updateExcelLink();
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
