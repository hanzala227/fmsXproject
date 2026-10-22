<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .nav-tabs-custom { margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 4px; background: #fff; padding: 4px 8px 0 8px; border-radius: 4px 4px 0 0; }
        .nav-tabs-custom .tab-item { padding: 8px 16px; font-size: 11px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .nav-tabs-custom .tab-item:hover { color: #2563eb; background: #f8fafc; }
        .nav-tabs-custom .tab-item.active { color: #2563eb; font-weight: 700; border-bottom-color: #2563eb; background: #eff6ff; }
        
        .grid-table tbody tr:nth-of-type(even) td { background-color: #fafbfc; }
        .grid-table tbody tr:hover td { background-color: #f1f5f9 !important; }
        .text-right { text-align: right !important; }
        .totals-row td { background: #f1f5f9 !important; font-weight: 700; border-top: 2px solid #cbd5e1; color: #1e293b; }
        
        #grid-loading { display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.75); z-index: 50; align-items: center; justify-content: center; }
        #grid-loading.show { display: flex; }
        #grid-loading .spinner { width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .filter-row td { background: #eff6ff !important; padding: 2px 3px; }
        .filter-row td input.filter-input { width: 100%; height: 20px; border: 1px solid #93c5fd; font-size: 9px; border-radius: 2px; padding: 0 4px; box-sizing: border-box; outline: none; background: #fff; }
        .filter-row td input.filter-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px rgba(59,130,246,0.2); }
        
        .badge-status { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .status-pre-receiving { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
        .status-received { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .status-shipped { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .status-default { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        .form-input-gf { width: 100%; height: 24px; border: 1px solid #cbd5e1; padding: 0 6px; font-size: 10px; border-radius: 2px; background: #fff; color: #1e293b; box-sizing: border-box; }
        .form-input-gf:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
        .modal-form-row { display: flex; flex-wrap: wrap; margin: 0 -6px; }
        .modal-form-col { padding: 0 6px; margin-bottom: 8px; box-sizing: border-box; }
        .modal-label { font-size: 10px; font-weight: 700; color: #475569; margin-bottom: 3px; display: block; }
        .modal-label .text-danger { color: #ef4444; }
        .modal-box { min-width: 450px; max-width: 750px; }
        .modal-body { max-height: 70vh; overflow-y: auto; padding: 12px 16px; }

        @media print {
            /* Hide all UI elements except table */
            .page-sidebar-wrapper,
            .page-header,
            .page-bar,
            .portlet-title,
            .portlet-tool:not(.bottom),
            .nav-tabs-custom,
            .btn-group,
            .btn-action-round,
            .actions,
            .caption-subject,
            .filter-row,
            .pagination,
            .toast-container,
            .overlay,
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
            
            body { background: #fff; }
            .page-content { background: #fff; padding: 0; }
            .portlet.light { border: none; box-shadow: none; }
            
            /* Table styling for print */
            .grid-table {
                border-collapse: collapse !important;
                font-size: 9px !important;
                min-width: auto !important;
            }
            
            .grid-table th,
            .grid-table td {
                border: 1px solid #ddd !important;
                padding: 2px 4px !important;
                text-align: left !important;
            }
            
            .grid-table th {
                background: #f3f4f6 !important;
                font-weight: bold !important;
                color: #000 !important;
            }
            
            /* Remove alternating row colors for print */
            .grid-table tbody tr:nth-of-type(even) td {
                background-color: #fff !important;
            }
            
            .grid-table tbody tr:hover td {
                background-color: #fff !important;
            }
            
            /* Totals row */
            .totals-row td {
                background: #f3f4f6 !important;
                border-top: 2px solid #000 !important;
            }
            
            /* Status badges */
            .badge-status {
                border: 1px solid #000 !important;
                padding: 2px 4px !important;
                font-size: 8px !important;
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
                height: calc(100vh - 280px) !important;
                min-height: 200px !important;
                overflow-x: auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                position: relative;
            }
            
            .grid-table { 
                font-size: 8px !important;
                width: auto !important;
                min-width: 1400px !important; /* Ensures horizontal scroll */
                table-layout: auto !important;
            }
            
            .grid-table th, .grid-table td { 
                padding: 2px 4px !important;
                height: 22px !important;
                white-space: nowrap !important;
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
            
            #sel-badge, #result-count { font-size: 8px !important; }
            
            /* Nav tabs on mobile */
            .nav-tabs-custom {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                flex-wrap: nowrap !important;
                margin-bottom: 8px;
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
                min-width: 1200px !important;
            }
            .grid-table th, .grid-table td { 
                padding: 2px 3px !important;
                height: 20px !important;
            }
            .btn-action-round, .btn-tool { 
                font-size: 8px !important;
                padding: 0 4px !important;
            }
        }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Inventory Detail Record(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()"><i class="fa fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>

    {{-- CREATE / EDIT DETAIL MODAL --}}
    <div class="overlay" id="detail-modal-overlay" onclick="if(event.target===this) closeDetailModal()">
        <div class="modal-box" style="min-width:650px;">
            <div class="modal-header">
                <div class="modal-header-title" id="detail-modal-title"><i class="fa fa-list-alt" style="color:#3b82f6;"></i> <span id="detail-modal-title-text">New Inventory Detail</span></div>
                <button class="modal-close" onclick="closeDetailModal()"><i class="fa fa-times"></i></button>
            </div>
            <form id="detail-form" onsubmit="handleFormSubmit(event)" style="margin:0;">
                @csrf
                <input type="hidden" id="detail-form-id" value="">
                <input type="hidden" id="detail-form-method" value="POST">
                <div class="modal-body">
                    <div class="modal-form-row">
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label">Warehouse Receiving Record</label>
                            <select id="detail-warehouse_receiving_id" class="form-input-gf">
                                <option value="">Select Receiving Record (Optional)...</option>
                                @foreach($receivings as $r)
                                    <option value="{{ $r->id }}">{{ $r->bl_no ? 'BL: ' . $r->bl_no : 'Rec #' . $r->id }} - {{ $r->customer->name ?? 'No Customer' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label"><span class="text-danger">*</span> SKU No.</label>
                            <input type="text" id="detail-sku_no" class="form-input-gf" placeholder="e.g. SKU-1002" required>
                        </div>
                        <div class="modal-form-col" style="width:100%;">
                            <label class="modal-label"><span class="text-danger">*</span> Product Description</label>
                            <input type="text" id="detail-description" class="form-input-gf" placeholder="Product name or description" required>
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label">Customer P.O.</label>
                            <input type="text" id="detail-customer_po" class="form-input-gf" placeholder="Customer P.O.">
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label">Order P.O. No.</label>
                            <input type="text" id="detail-order_po_no" class="form-input-gf" placeholder="Order P.O. No.">
                        </div>
                        <div class="modal-form-col" style="width:33%;">
                            <label class="modal-label"><span class="text-danger">*</span> Qty</label>
                            <input type="number" step="0.01" id="detail-qty" class="form-input-gf" value="1.00" required>
                        </div>
                        <div class="modal-form-col" style="width:33%;">
                            <label class="modal-label">Qty Unit</label>
                            <input type="text" id="detail-qty_unit" class="form-input-gf" placeholder="e.g. CTN, PCS">
                        </div>
                        <div class="modal-form-col" style="width:34%;">
                            <label class="modal-label">PCS / Pack</label>
                            <input type="number" step="0.01" id="detail-pack" class="form-input-gf" value="0.00">
                        </div>
                        <div class="modal-form-col" style="width:33%;">
                            <label class="modal-label">Weight (KG)</label>
                            <input type="number" step="0.001" id="detail-weight_kg" class="form-input-gf" value="0.00">
                        </div>
                        <div class="modal-form-col" style="width:33%;">
                            <label class="modal-label">Measurement (CBM)</label>
                            <input type="number" step="0.001" id="detail-measure_cbm" class="form-input-gf" value="0.00">
                        </div>
                        <div class="modal-form-col" style="width:34%;">
                            <label class="modal-label">Pallet</label>
                            <input type="text" id="detail-pallet" class="form-input-gf" placeholder="Pallet ID / Location">
                        </div>
                    </div>
                </div>
                <div class="modal-header" style="border-top:1px solid #e2e8f0;border-bottom:none;justify-content:flex-end;gap:6px;padding:8px 16px;">
                    <button type="button" class="btn-tool" onclick="closeDetailModal()">Cancel</button>
                    <button type="submit" class="btn-tool green" id="btn-save-detail"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li>Inventory <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">Detail</span></li>
            </ul>
        </div>

        {{-- TOP NAVIGATION TABS --}}
        <div class="nav-tabs-custom">
            <a href="{{ route('inventory.detail') }}" class="tab-item active">
                <i class="fa fa-list-alt"></i> Inventory Detail
            </a>
            <a href="{{ route('inventory.summary') }}" class="tab-item">
                <i class="fa fa-bar-chart"></i> Inventory Summary
            </a>
            <a href="{{ route('items.index') }}" class="tab-item">
                <i class="fa fa-cubes"></i> Warehouse Items List
            </a>
        </div>

        <div class="portlet light" style="position:relative;">
            <div id="grid-loading"><div class="spinner"></div></div>

            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Inventory Detail</span>
                    <span id="result-count" style="font-size:10px;color:#64748b;font-weight:400;">({{ $items->total() }} records)</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#2563eb;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
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
                    <a class="btn-action-round white" id="btn-excel" href="{{ route('inventory.detail.export-csv') }}" title="Download as CSV/Excel" target="_blank"><i class="fa fa-file-excel-o"></i> Excel</a>
                </div>
            </div>

            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:nowrap;">
                    <div class="btn-group">
                        <button class="btn-tool green" onclick="openCreateModal()" title="New Inventory Detail Record"><i class="fa fa-plus"></i> New Record</button>
                        <button class="btn-tool danger" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()"><i class="fa fa-trash"></i> Delete Selected</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:180px;" placeholder="Search SKU, product, PO..." value="{{ request('search') }}" oninput="quickSearch(this.value)">
                    <button id="clear-search" class="btn-tool" style="display:none;padding:0 6px;height:18px;font-size:9px;" onclick="clearSearch()" title="Clear search"><i class="fa fa-times-circle"></i></button>
                </div>
            </div>

            <div class="portlet-body" style="overflow-x:auto;">
                <table class="grid-table" id="main-grid">
                    <thead>
                        <tr id="header-row">
                            <th data-col="check" style="width:25px;text-align:center;"><input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All"></th>
                            <th data-col="date" style="width:65px;">
                                <a href="javascript:;" onclick="toggleSort('created_at')" style="color:inherit;text-decoration:none;">Date <i class="fa fa-sort" id="sort-created_at"></i></a>
                            </th>
                            <th data-col="customer" style="width:110px;">Customer</th>
                            <th data-col="file_no" style="width:80px;">Booking no</th>
                            <th data-col="office" style="width:55px;">Office</th>
                            <th data-col="trucker" style="width:80px;">Trucker</th>
                            <th data-col="from_to" style="width:80px;">From / To</th>
                            <th data-col="sku" style="width:85px;">
                                <a href="javascript:;" onclick="toggleSort('sku_no')" style="color:inherit;text-decoration:none;">SKU No. <i class="fa fa-sort" id="sort-sku_no"></i></a>
                            </th>
                            <th data-col="po" style="width:80px;">Customer P.O.</th>
                            <th data-col="product" style="width:150px;">
                                <a href="javascript:;" onclick="toggleSort('description')" style="color:inherit;text-decoration:none;">Description <i class="fa fa-sort" id="sort-description"></i></a>
                            </th>
                            <th data-col="order_po" style="width:80px;">Order P.O.</th>
                            <th data-col="qty" style="width:70px;text-align:right;">
                                <a href="javascript:;" onclick="toggleSort('qty')" style="color:inherit;text-decoration:none;">Qty <i class="fa fa-sort" id="sort-qty"></i></a>
                            </th>
                            <th data-col="pcs" style="width:60px;text-align:right;">PCS</th>
                            <th data-col="weight" style="width:90px;text-align:right;">
                                <a href="javascript:;" onclick="toggleSort('weight_kg')" style="color:inherit;text-decoration:none;">Weight <i class="fa fa-sort" id="sort-weight_kg"></i></a>
                            </th>
                            <th data-col="measurement" style="width:90px;text-align:right;">
                                <a href="javascript:;" onclick="toggleSort('measure_cbm')" style="color:inherit;text-decoration:none;">Measurement <i class="fa fa-sort" id="sort-measure_cbm"></i></a>
                            </th>
                            <th data-col="status" style="width:70px;">Status</th>
                            <th data-col="actions" style="width:60px;text-align:center;">Action</th>
                        </tr>
                        <tr id="filter-row" class="filter-row" style="display:none;">
                            <td data-col="check"></td>
                            <td data-col="date"><input class="filter-input" data-col-idx="0" placeholder="Date..." oninput="applyFilters()"></td>
                            <td data-col="customer"><input class="filter-input" data-col-idx="1" placeholder="Customer..." oninput="applyFilters()"></td>
                            <td data-col="file_no"></td>
                            <td data-col="office"><input class="filter-input" data-col-idx="3" placeholder="Office..." oninput="applyFilters()"></td>
                            <td data-col="trucker"></td>
                            <td data-col="from_to"></td>
                            <td data-col="sku"><input class="filter-input" data-col-idx="6" placeholder="SKU..." oninput="applyFilters()"></td>
                            <td data-col="po"></td>
                            <td data-col="product"><input class="filter-input" data-col-idx="8" placeholder="Description..." oninput="applyFilters()"></td>
                            <td data-col="order_po"></td>
                            <td data-col="qty"></td>
                            <td data-col="pcs"></td>
                            <td data-col="weight"></td>
                            <td data-col="measurement"></td>
                            <td data-col="status"></td>
                            <td data-col="actions"></td>
                        </tr>
                    </thead>
                    <tbody id="grid-body">
                    @forelse($items as $item)
                        <tr id="row-{{ $item->id }}" data-id="{{ $item->id }}"
                            data-receiving-id="{{ $item->warehouse_receiving_id }}"
                            data-sku="{{ addslashes($item->sku_no) }}"
                            data-description="{{ addslashes($item->description) }}"
                            data-customer-po="{{ addslashes($item->customer_po ?? '') }}"
                            data-order-po="{{ addslashes($item->order_po_no ?? '') }}"
                            data-qty="{{ $item->qty }}"
                            data-qty-unit="{{ addslashes($item->qty_unit ?? '') }}"
                            data-pack="{{ $item->pack }}"
                            data-pack-unit="{{ addslashes($item->pack_unit ?? '') }}"
                            data-weight="{{ $item->weight_kg }}"
                            data-measure="{{ $item->measure_cbm }}"
                            data-pallet="{{ addslashes($item->pallet ?? '') }}"
                            onclick="rowClick(event, this)">
                            <td style="text-align:center;" onclick="event.stopPropagation()">
                                <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-check" onchange="updateToolbar()">
                            </td>
                            <td>{{ $item->receiving?->receiving_date?->format('m-d-Y') ?? $item->created_at?->format('m-d-Y') ?? '' }}</td>
                            <td>{{ $item->receiving?->customer?->name ?? $item->receiving?->receipt?->customer?->name ?? '' }}</td>
                            <td>{{ $item->receiving?->bl_no ?? '' }}</td>
                            <td>{{ $item->receiving?->office?->code ?? '' }}</td>
                            <td>{{ $item->receiving?->trucker?->name ?? '' }}</td>
                            <td>{{ $item->receiving?->shipFrom?->name ?? '' }}</td>
                            <td><a href="javascript:;" class="col-link" onclick="event.stopPropagation();openEditModal(this)">{{ $item->sku_no }}</a></td>
                            <td>{{ $item->customer_po ?? '' }}</td>
                            <td style="overflow:hidden;text-overflow:ellipsis;">{{ $item->description }}</td>
                            <td>{{ $item->order_po_no ?? '' }}</td>
                            <td style="text-align:right;">{{ number_format($item->qty ?? 0, 2) }}</td>
                            <td style="text-align:right;">{{ number_format($item->pack ?? 0, 2) }}</td>
                            <td style="text-align:right;">{{ number_format($item->weight_kg ?? 0, 2) }} KG</td>
                            <td style="text-align:right;">{{ number_format($item->measure_cbm ?? 0, 2) }} CBM</td>
                            <td>
                                @php $status = $item->receiving?->status ?? ''; @endphp
                                @if($status)
                                    <span class="badge-status {{ $status === 'Pre-Receiving' ? 'status-pre-receiving' : ($status === 'Received' ? 'status-received' : ($status === 'Shipped' ? 'status-shipped' : 'status-default')) }}">{{ $status }}</span>
                                @else
                                    <span class="badge-status status-received">In Stock</span>
                                @endif
                            </td>
                            <td style="text-align:center;" onclick="event.stopPropagation()">
                                <button class="btn-tool" onclick="openEditModal(this)" title="Edit"><i class="fa fa-pencil" style="color:#2563eb;"></i></button>
                                <button class="btn-tool danger" onclick="deleteSingleItem({{ $item->id }})" title="Delete"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row">
                            <td colspan="17" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                No inventory detail records found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if($items->count() > 0)
                    <tfoot>
                        <tr class="totals-row">
                            <td colspan="11" style="text-align:right;">TOTAL</td>
                            <td style="text-align:right;">{{ number_format($totals['qty'] ?? 0, 2) }}</td>
                            <td style="text-align:right;">&mdash;</td>
                            <td style="text-align:right;">{{ number_format($totals['weight'] ?? 0, 2) }} KG</td>
                            <td style="text-align:right;">{{ number_format($totals['measure'] ?? 0, 2) }} CBM</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $items->links() }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $items->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $items->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $items->total() }}</span> records
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const FILTER_MAP = { 0: 'filter_date', 1: 'filter_customer', 3: 'filter_office', 6: 'filter_sku', 8: 'filter_item_name' };
    const STATE_KEY = 'warehouse_inventory_detail_state';

    function showLoading() { const el = document.getElementById('grid-loading'); if (el) el.classList.add('show'); }
    function hideLoading() { const el = document.getElementById('grid-loading'); if (el) el.classList.remove('show'); }

    function updateExcelLink() {
        const btn = document.getElementById('btn-excel');
        if (!btn) return;
        const url = new URL(window.location.href);
        btn.href = url.pathname + '?' + url.searchParams.toString();
    }

    function updateClearSearch() {
        const btn = document.getElementById('clear-search');
        const input = document.getElementById('quick-search');
        if (btn && input) { btn.style.display = input.value.trim() ? 'inline-flex' : 'none'; }
    }
    function clearSearch() {
        const input = document.getElementById('quick-search');
        if (input) { input.value = ''; quickSearch(''); }
    }

    /* ROW SELECTION & TOOLBAR */
    function updateToolbar() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const all = [...document.querySelectorAll('.row-check')];
        const n = checked.length;
        const sa = document.getElementById('select-all');
        if (sa) {
            sa.checked = n === all.length && all.length > 0;
            sa.indeterminate = n > 0 && n < all.length;
        }
        const btnDel = document.getElementById('btn-delete');
        if (btnDel) btnDel.disabled = n === 0;
        const badge = document.getElementById('sel-badge');
        if (badge) {
            badge.style.display = n > 0 ? 'inline' : 'none';
            badge.textContent = n + ' selected';
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
        const skip = ['A', 'INPUT', 'BUTTON', 'I'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    /* SEARCH */
    let searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const url = new URL(window.location.href);
            if (!q) url.searchParams.delete('search');
            else url.searchParams.set('search', q);
            url.searchParams.delete('page');
            updateClearSearch();
            updateGrid(url.toString());
        }, 300);
    }

    /* FILTER */
    let filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        const row = document.getElementById('filter-row');
        row.style.display = filterOpen ? 'table-row' : 'none';
        document.getElementById('btn-filter').classList.toggle('active', filterOpen);

        if (filterOpen) {
            const urlParams = new URLSearchParams(window.location.search);
            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                const param = FILTER_MAP[inp.dataset.colIdx];
                if (param) inp.value = urlParams.get(param) || '';
            });
            document.querySelector('#filter-row .filter-input')?.focus();
        } else {
            document.querySelectorAll('#filter-row .filter-input').forEach(i => { i.value = ''; });
            applyFilters();
        }
    }

    let filterDebounce;
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            const url = new URL(window.location.href);
            const search = url.searchParams.get('search') || '';
            const sort = url.searchParams.get('sort') || '';
            const dir = url.searchParams.get('dir') || '';
            url.search = '';
            if (search) url.searchParams.set('search', search);
            if (sort) url.searchParams.set('sort', sort);
            if (dir) url.searchParams.set('dir', dir);

            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                const v = inp.value.trim();
                if (!v) return;
                const param = FILTER_MAP[inp.dataset.colIdx];
                if (param) url.searchParams.set(param, v);
            });
            updateGrid(url.toString());
        }, 300);
    }

    /* SORT */
    function toggleSort(field) {
        const params = new URLSearchParams(window.location.search);
        const currentSort = params.get('sort') || 'created_at';
        const currentDir = params.get('dir') || 'desc';
        let newDir = 'asc';
        if (currentSort === field) { newDir = currentDir === 'asc' ? 'desc' : 'asc'; }
        const url = new URL(window.location.href);
        url.searchParams.set('sort', field);
        url.searchParams.set('dir', newDir);
        updateGrid(url.toString());
    }

    /* STATE PERSISTENCE (BUG FIX ON RELOAD) */
    function saveState(url) {
        try {
            const searchParams = new URL(url).search;
            sessionStorage.setItem(STATE_KEY, searchParams);
        } catch(e){}
    }

    function restoreStateOnLoad() {
        try {
            const currentUrl = new URL(window.location.href);
            const savedSearch = sessionStorage.getItem(STATE_KEY);
            if (savedSearch && !currentUrl.search) {
                const targetUrl = currentUrl.pathname + savedSearch;
                updateGrid(targetUrl);
            } else if (currentUrl.search) {
                sessionStorage.setItem(STATE_KEY, currentUrl.search);
                const params = currentUrl.searchParams;
                const hasFilter = ['filter_date', 'filter_customer', 'filter_office', 'filter_sku', 'filter_item_name'].some(k => params.has(k) && params.get(k));
                if (hasFilter) {
                    filterOpen = true;
                    document.getElementById('filter-row').style.display = 'table-row';
                    document.getElementById('btn-filter').classList.add('active');
                    document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                        const param = FILTER_MAP[inp.dataset.colIdx];
                        if (param) inp.value = params.get(param) || '';
                    });
                }
            }
        } catch(e){}
    }

    /* AJAX GRID */
    async function updateGrid(url) {
        showLoading();
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Network error');
            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');

            const newBody = doc.getElementById('grid-body');
            const newPagination = doc.getElementById('pagination-container');
            const newTfoot = doc.querySelector('#main-grid tfoot');
            const oldTfoot = document.querySelector('#main-grid tfoot');

            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            if (newTfoot) {
                if (oldTfoot) oldTfoot.innerHTML = newTfoot.innerHTML;
                else document.querySelector('#main-grid').appendChild(newTfoot.cloneNode(true));
            } else if (oldTfoot) {
                oldTfoot.remove();
            }

            const newCount = doc.getElementById('result-count');
            const oldCount = document.getElementById('result-count');
            if (newCount && oldCount) oldCount.textContent = newCount.textContent;

            const stats = doc.querySelector('.portlet-tool.bottom div:last-child');
            if (stats) {
                const nums = stats.textContent.match(/[\d,]+/g);
                if (nums && nums.length >= 3) {
                    document.getElementById('stat-first').textContent = nums[0];
                    document.getElementById('stat-last').textContent = nums[1];
                    document.getElementById('stat-total').textContent = nums[2];
                }
            }

            document.querySelectorAll('[id^="sort-"]').forEach(i => i.className = 'fa fa-sort');
            const params = new URLSearchParams(new URL(url).search);
            const sort = params.get('sort') || 'created_at';
            const dir = params.get('dir') || 'desc';
            const sortIcon = document.getElementById('sort-' + sort);
            if (sortIcon) sortIcon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');

            window.history.pushState({}, '', url);
            saveState(url);
            updateToolbar();
            updateExcelLink();
            updateClearSearch();
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
        if (link) { e.preventDefault(); updateGrid(link.href); }
    });

    function refreshGrid() { updateGrid(window.location.href); }

    /* CREATE / EDIT MODAL & FORM CRUD */
    function openCreateModal() {
        document.getElementById('detail-modal-title-text').textContent = 'New Inventory Detail Record';
        document.getElementById('detail-form-id').value = '';
        document.getElementById('detail-form-method').value = 'POST';
        document.getElementById('detail-warehouse_receiving_id').value = '';
        document.getElementById('detail-sku_no').value = '';
        document.getElementById('detail-description').value = '';
        document.getElementById('detail-customer_po').value = '';
        document.getElementById('detail-order_po_no').value = '';
        document.getElementById('detail-qty').value = '1.00';
        document.getElementById('detail-qty_unit').value = 'CTN';
        document.getElementById('detail-pack').value = '1.00';
        document.getElementById('detail-weight_kg').value = '0.00';
        document.getElementById('detail-measure_cbm').value = '0.00';
        document.getElementById('detail-pallet').value = '';
        document.getElementById('detail-modal-overlay').classList.add('open');
    }

    function openEditModal(el) {
        const tr = el.closest('tr');
        if (!tr) return;
        const d = tr.dataset;
        document.getElementById('detail-modal-title-text').textContent = 'Edit Inventory Detail Record';
        document.getElementById('detail-form-id').value = d.id;
        document.getElementById('detail-form-method').value = 'PUT';
        document.getElementById('detail-warehouse_receiving_id').value = d.receivingId || '';
        document.getElementById('detail-sku_no').value = d.sku || '';
        document.getElementById('detail-description').value = d.description || '';
        document.getElementById('detail-customer_po').value = d.customerPo || '';
        document.getElementById('detail-order_po_no').value = d.orderPo || '';
        document.getElementById('detail-qty').value = d.qty || '0';
        document.getElementById('detail-qty_unit').value = d.qtyUnit || '';
        document.getElementById('detail-pack').value = d.pack || '0';
        document.getElementById('detail-weight_kg').value = d.weight || '0';
        document.getElementById('detail-measure_cbm').value = d.measure || '0';
        document.getElementById('detail-pallet').value = d.pallet || '';
        document.getElementById('detail-modal-overlay').classList.add('open');
    }

    function closeDetailModal() {
        document.getElementById('detail-modal-overlay').classList.remove('open');
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('detail-form-id').value;
        const method = document.getElementById('detail-form-method').value;
        const url = method === 'PUT' ? `/warehouse/inventory/detail/${id}` : '{{ route("inventory.detail.store") }}';
        
        const payload = {
            warehouse_receiving_id: document.getElementById('detail-warehouse_receiving_id').value || null,
            sku_no: document.getElementById('detail-sku_no').value,
            description: document.getElementById('detail-description').value,
            customer_po: document.getElementById('detail-customer_po').value || null,
            order_po_no: document.getElementById('detail-order_po_no').value || null,
            qty: document.getElementById('detail-qty').value || 0,
            qty_unit: document.getElementById('detail-qty_unit').value || null,
            pack: document.getElementById('detail-pack').value || 0,
            weight_kg: document.getElementById('detail-weight_kg').value || 0,
            measure_cbm: document.getElementById('detail-measure_cbm').value || 0,
            pallet: document.getElementById('detail-pallet').value || null,
        };

        const btn = document.getElementById('btn-save-detail');
        btn.disabled = true;
        showLoading();

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.success) {
                closeDetailModal();
                showToast('success', data.message);
                updateGrid(window.location.href);
            } else {
                showToast('error', data.message || 'Validation failed');
            }
        } catch(err) {
            console.error(err);
            showToast('error', 'Failed to save inventory detail record');
        } finally {
            btn.disabled = false;
            hideLoading();
        }
    }

    /* DELETE ACTIONS */
    function deleteSingleItem(id) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = (cb.value == id));
        updateToolbar();
        confirmDelete();
    }

    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent = `Are you sure you want to delete ${n} inventory detail record(s)?`;
        document.getElementById('confirm-overlay').classList.add('open');
    }

    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
    }

    async function executeDelete() {
        closeConfirm();
        const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
        if (!ids.length) return;
        showLoading();
        try {
            const response = await fetch('{{ route("inventory.detail.bulk-delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids })
            });
            const data = await response.json();
            if (data.success) {
                showToast('success', data.message);
                updateGrid(window.location.href);
            } else {
                showToast('error', data.message || 'Failed to delete records');
            }
        } catch(err) {
            showToast('error', 'Failed to delete records');
        } finally {
            hideLoading();
        }
    }

    /* CONFIG */
    const PINNED = ['check', 'date'];
    const STORAGE_KEY = 'inventory_detail_column_config';
    function loadColumnConfig() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (!saved) return;
            const hidden = JSON.parse(saved);
            document.querySelectorAll('#header-row th[data-col]').forEach(th => {
                if (PINNED.includes(th.dataset.col)) return;
                if (hidden.includes(th.dataset.col)) th.style.display = 'none';
            });
            document.querySelectorAll('#grid-body tr').forEach(row => {
                const cells = row.querySelectorAll('td');
                document.querySelectorAll('#header-row th[data-col]').forEach((th, i) => {
                    if (PINNED.includes(th.dataset.col)) return;
                    if (hidden.includes(th.dataset.col) && cells[i]) cells[i].style.display = 'none';
                });
            });
            document.querySelectorAll('#filter-row td[data-col]').forEach(td => {
                if (hidden.includes(td.dataset.col)) td.style.display = 'none';
            });
        } catch(e) {}
    }
    function toggleConfig() {
        const panel = document.getElementById('config-panel');
        if (!panel) return;
        const open = panel.style.display === 'none' || panel.style.display === '';
        panel.style.display = open ? 'block' : 'none';
        const btn = document.getElementById('btn-config');
        if (btn) btn.classList.toggle('active', open);
        if (open) buildConfigPanel();
    }
    function buildConfigPanel() {
        const container = document.getElementById('col-toggles');
        container.innerHTML = '';
        const hidden = getHiddenColumns();
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (PINNED.includes(th.dataset.col)) return;
            const label = document.createElement('label');
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.checked = !hidden.includes(th.dataset.col);
            cb.onchange = () => toggleColumn(th.dataset.col, cb.checked);
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim().replace(/[0-9]/g, '').trim());
            container.appendChild(label);
        });
    }
    function getHiddenColumns() {
        const hidden = [];
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (PINNED.includes(th.dataset.col)) return;
            if (th.style.display === 'none') hidden.push(th.dataset.col);
        });
        return hidden;
    }
    function toggleColumn(colName, show) {
        const th = document.querySelector(`#header-row th[data-col="${colName}"]`);
        if (!th) return;
        const idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells[idx]) cells[idx].style.display = show ? '' : 'none';
        });
        const filterTd = document.querySelector(`#filter-row td[data-col="${colName}"]`);
        if (filterTd) filterTd.style.display = show ? '' : 'none';
        saveColumnConfig();
    }
    function saveColumnConfig() { try { localStorage.setItem(STORAGE_KEY, JSON.stringify(getHiddenColumns())); } catch(e) {} }
    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn = document.getElementById('btn-config');
        if (panel && btn && panel.style && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* INIT & PAGE RESTORE */
    (function() {
        restoreStateOnLoad();
        const params = new URLSearchParams(window.location.search);
        const sort = params.get('sort') || 'created_at';
        const dir = params.get('dir') || 'desc';
        const icon = document.getElementById('sort-' + sort);
        if (icon) icon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');
        loadColumnConfig();
        updateExcelLink();
        updateClearSearch();
        updateToolbar();
    })();

    window.addEventListener('popstate', function() { updateGrid(window.location.href); });
    window.addEventListener('pageshow', function() { restoreStateOnLoad(); });

    /* TOAST */
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `<i class="fa fa-${icons[type] || 'info-circle'}"></i> <span>${msg}</span>`;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    @if(session('success')) showToast('success', @json(session('success'))); @endif
    @if(session('error')) showToast('error', @json(session('error'))); @endif
    </script>
    @endpush
</x-layout>
