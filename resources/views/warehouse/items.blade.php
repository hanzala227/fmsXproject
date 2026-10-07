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

        .form-input-gf { width: 100%; height: 22px; border: 1px solid #cbd5e1; padding: 0 6px; font-size: 10px; border-radius: 2px; background: #fff; color: #1e293b; box-sizing: border-box; }
        .form-input-gf:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
        .modal-form-row { display: flex; flex-wrap: wrap; margin: 0 -4px; }
        .modal-form-col { padding: 0 4px; margin-bottom: 8px; box-sizing: border-box; }
        .modal-label { font-size: 10px; font-weight: 700; color: #475569; margin-bottom: 3px; display: block; }
        .modal-label .text-danger { color: #ef4444; }

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
            
            /* Hide checkboxes and color columns */
            th[data-col="check"], td:nth-child(1),
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
            /* Remove sticky from all other columns on mobile */
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
            
            /* Keep only checkbox sticky on very small screens */
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) {
                position: static !important;
                left: auto !important;
            }
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Record(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:24px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:24px;" onclick="executeDelete()"><i class="fa fa-trash"></i> Delete</button>
            </div>
        </div>
    </div>

    {{-- COLOR PICKER MODAL --}}
    <div class="overlay color-picker-overlay" id="color-picker-overlay" onclick="if(event.target===this) closeColorPicker()">
        <div class="modal-box" style="width:260px;">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-paint-brush" style="color:#3b82f6;"></i> Status Color</div>
                <button class="modal-close" onclick="closeColorPicker()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <div class="color-picker-grid" id="color-picker-grid"></div>
                <div class="color-clear-btn" onclick="clearColor()"><i class="fa fa-times-circle"></i> Clear / No Color</div>
            </div>
        </div>
    </div>

    {{-- ITEM CREATE/EDIT MODAL --}}
    <div class="overlay" id="item-modal-overlay" onclick="if(event.target===this) closeItemModal()">
        <div class="modal-box" style="width:720px;max-width:95vw;">
            <div class="modal-header">
                <div class="modal-header-title" id="item-modal-title"><i class="fa fa-cube" style="color:#3b82f6;"></i> <span id="item-modal-title-text">Create Item</span></div>
                <button class="modal-close" onclick="closeItemModal()"><i class="fa fa-times"></i></button>
            </div>
            <form id="item-form" onsubmit="saveItem(event)" enctype="multipart/form-data" style="margin:0;">
                @csrf
                <input type="hidden" id="item-id" value="">
                <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                    <div class="modal-form-row">
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label"><span class="text-danger">*</span> Customer</label>
                            <select id="item-customer_id" name="customer_id" class="form-input-gf" required>
                                <option value="">Select Customer...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label"><span class="text-danger">*</span> SKU No.</label>
                            <input type="text" id="item-sku" name="sku" class="form-input-gf" required placeholder="e.g. SKU-1001">
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label">Vendor</label>
                            <select id="item-vendor_id" name="vendor_id" class="form-input-gf">
                                <option value="">Select Vendor...</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label"><span class="text-danger">*</span> Warehouse</label>
                            <select id="item-warehouse_id" name="warehouse_id" class="form-input-gf" required>
                                <option value="">Select Warehouse...</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-form-col" style="width:100%;">
                            <label class="modal-label"><span class="text-danger">*</span> Product Name</label>
                            <input type="text" id="item-item_name" name="item_name" class="form-input-gf" required placeholder="Product Title / Name">
                        </div>
                        <div class="modal-form-col" style="width:33.33%;">
                            <label class="modal-label">UPC / EAN</label>
                            <input type="text" id="item-upc_ean" name="upc_ean" class="form-input-gf">
                        </div>
                        <div class="modal-form-col" style="width:33.33%;">
                            <label class="modal-label">MPN</label>
                            <input type="text" id="item-mpn" name="mpn" class="form-input-gf">
                        </div>
                        <div class="modal-form-col" style="width:33.33%;">
                            <label class="modal-label">HTS Code</label>
                            <input type="text" id="item-hts_code" name="hts_code" class="form-input-gf">
                        </div>
                        <div class="modal-form-col" style="width:100%;">
                            <label class="modal-label">Product Description</label>
                            <textarea id="item-description" name="description" class="form-input-gf" style="height:44px;resize:vertical;"></textarea>
                        </div>
                        <div class="modal-form-col" style="width:25%;">
                            <label class="modal-label">Inner Pack</label>
                            <input type="number" step="0.01" id="item-inner_pack" name="inner_pack" class="form-input-gf" value="0.00">
                        </div>
                        <div class="modal-form-col" style="width:25%;">
                            <label class="modal-label">Qty Unit</label>
                            <select id="item-unit_id" name="unit_id" class="form-input-gf">
                                <option value="">Select Unit...</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-form-col" style="width:25%;">
                            <label class="modal-label">On Hand Qty</label>
                            <input type="number" step="0.01" id="item-on_hand_qty" name="on_hand_qty" class="form-input-gf" value="0.00">
                        </div>
                        <div class="modal-form-col" style="width:25%;">
                            <label class="modal-label">Available Qty</label>
                            <input type="number" step="0.01" id="item-available_qty" name="available_qty" class="form-input-gf" value="0.00">
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label">Weight (KG)</label>
                            <input type="number" step="0.001" id="item-weight_kg" name="weight_kg" class="form-input-gf" value="0.000">
                        </div>
                        <div class="modal-form-col" style="width:50%;">
                            <label class="modal-label">Volume (CBM)</label>
                            <input type="number" step="0.001" id="item-volume_cbm" name="volume_cbm" class="form-input-gf" value="0.000">
                        </div>
                        <div class="modal-form-col" style="width:100%;">
                            <label class="modal-label">Dimension (L x W x H)</label>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <input type="number" step="0.01" id="item-dimension_length" name="dimension_length" class="form-input-gf" placeholder="L">
                                <span>x</span>
                                <input type="number" step="0.01" id="item-dimension_width" name="dimension_width" class="form-input-gf" placeholder="W">
                                <span>x</span>
                                <input type="number" step="0.01" id="item-dimension_height" name="dimension_height" class="form-input-gf" placeholder="H">
                                <select id="item-dimension_unit" name="dimension_unit" class="form-input-gf" style="width:75px;">
                                    <option value="cm">CM</option>
                                    <option value="inch">Inch</option>
                                    <option value="feet">Feet</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-form-col" style="width:100%;">
                            <label class="modal-label">Remark</label>
                            <textarea id="item-remark" name="remark" class="form-input-gf" style="height:36px;resize:vertical;"></textarea>
                        </div>
                        <div class="modal-form-col" style="width:33.33%;">
                            <label class="modal-label">Create Date</label>
                            <input type="date" id="item-create_date" name="create_date" class="form-input-gf">
                        </div>
                        <div class="modal-form-col" style="width:33.33%;">
                            <label class="modal-label">Status</label>
                            <select id="item-status" name="status" class="form-input-gf">
                                <option value="enable">Enable</option>
                                <option value="disable">Disable</option>
                            </select>
                        </div>
                        <div class="modal-form-col" style="width:33.33%;">
                            <label class="modal-label">Product Photo</label>
                            <input type="file" id="item-product_photo" name="product_photo" class="form-input-gf" style="padding:1px 4px;height:auto;" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-header" style="border-top:1px solid #e2e8f0;border-bottom:none;justify-content:flex-end;gap:6px;background:#f8fafc;">
                    <button type="button" class="btn-tool" onclick="closeItemModal()">Cancel</button>
                    <button type="submit" class="btn-tool green" id="btn-save-item"><i class="fa fa-save"></i> Save Item</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MAIN PAGE CONTENT --}}
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">Warehouse Items List</span></li>
            </ul>
        </div>

        {{-- TOP NAVIGATION TABS --}}
        <div class="nav-tabs-custom">
            <a href="{{ route('inventory.detail') }}" class="tab-item">
                <i class="fa fa-list-alt"></i> Inventory Detail
            </a>
            <a href="{{ route('inventory.summary') }}" class="tab-item">
                <i class="fa fa-bar-chart"></i> Inventory Summary
            </a>
            <a href="{{ route('items.index') }}" class="tab-item active">
                <i class="fa fa-cubes"></i> Warehouse Items List
            </a>
        </div>

        <div class="portlet light" style="position:relative;">
            <div id="grid-loading"><div class="spinner"></div></div>

            {{-- ── TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Warehouse Items</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" id="btn-filter" onclick="toggleFilter()" title="Toggle filter row"><i class="fa fa-filter"></i> Filter</button>
                    <div style="position:relative;display:inline-flex;align-items:center;">
                        <button class="btn-action-round" id="btn-config" onclick="toggleConfig()" title="Column visibility"><i class="fa fa-cogs"></i> Config</button>
                        <div class="config-panel" id="config-panel" style="display:none;">
                            <div class="config-panel-title">Column Visibility</div>
                            <div id="col-toggles"></div>
                        </div>
                    </div>
                    <a class="btn-action-round white" id="btn-excel" href="{{ route('items.export-csv') }}" title="Download as CSV/Excel" target="_blank"><i class="fa fa-file-excel-o"></i> Excel</a>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:nowrap;">
                    <div class="btn-group">
                        <button class="btn-tool green" onclick="openCreateModal()" title="New Item"><i class="fa fa-plus"></i></button>
                        <button class="btn-tool danger" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()"><i class="fa fa-trash"></i></button>
                        <button class="btn-tool" id="btn-copy" disabled title="Copy Selected (select 1 row)" onclick="copySelected()"><i class="fa fa-files-o"></i></button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;" placeholder="Quick search..." value="{{ request('search') }}" oninput="quickSearch(this.value)">
                    <a href="javascript:;" id="clear-search-btn" onclick="clearSearch()" style="display:{{ request()->has('search') ? 'inline' : 'none' }};font-size:10px;color:#3b82f6;text-decoration:none;cursor:pointer;" title="Clear search"><i class="fa fa-times-circle"></i></a>
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;left:0;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="warehouse" style="width:140px;left:25px;">
                                        <a href="javascript:;" onclick="toggleSort('warehouse_id')" style="color:inherit;text-decoration:none;">Warehouse <i class="fa fa-sort" id="sort-warehouse_id"></i></a>
                                    </th>
                                    <th data-col="sku" style="width:110px;">
                                        <a href="javascript:;" onclick="toggleSort('sku')" style="color:inherit;text-decoration:none;">SKU No. <i class="fa fa-sort" id="sort-sku"></i></a>
                                    </th>
                                    <th data-col="item_name" style="width:180px;">
                                        <a href="javascript:;" onclick="toggleSort('item_name')" style="color:inherit;text-decoration:none;">Product Name <i class="fa fa-sort" id="sort-item_name"></i></a>
                                    </th>
                                    <th data-col="description" style="width:180px;">Description</th>
                                    <th data-col="unit" style="width:70px;">Unit</th>
                                    <th data-col="on_hand_qty" style="width:85px;text-align:right;">On Hand</th>
                                    <th data-col="available_qty" style="width:85px;text-align:right;">Available</th>
                                    <th data-col="weight_kg" style="width:90px;text-align:right;">
                                        <a href="javascript:;" onclick="toggleSort('weight_kg')" style="color:inherit;text-decoration:none;">Weight <i class="fa fa-sort" id="sort-weight_kg"></i></a>
                                    </th>
                                    <th data-col="volume_cbm" style="width:90px;text-align:right;">
                                        <a href="javascript:;" onclick="toggleSort('volume_cbm')" style="color:inherit;text-decoration:none;">Volume <i class="fa fa-sort" id="sort-volume_cbm"></i></a>
                                    </th>
                                    <th data-col="created_at" style="width:90px;">
                                        <a href="javascript:;" onclick="toggleSort('created_at')" style="color:inherit;text-decoration:none;">Created <i class="fa fa-sort" id="sort-created_at"></i></a>
                                    </th>
                                </tr>
                                <tr id="filter-row" class="filter-row" style="display:none;">
                                    <td class="sticky-col" data-col="check" style="left:0;"></td>
                                    <td class="sticky-col" data-col="warehouse" style="left:25px;"><input class="filter-input" data-col-idx="2" placeholder="Warehouse..." oninput="applyFilters()"></td>
                                    <td data-col="sku"><input class="filter-input" data-col-idx="3" placeholder="SKU..." oninput="applyFilters()"></td>
                                    <td data-col="item_name"><input class="filter-input" data-col-idx="4" placeholder="Product Name..." oninput="applyFilters()"></td>
                                    <td data-col="description"><input class="filter-input" data-col-idx="5" placeholder="Description..." oninput="applyFilters()"></td>
                                    <td data-col="unit"><input class="filter-input" data-col-idx="6" placeholder="Unit..." oninput="applyFilters()"></td>
                                    <td data-col="on_hand_qty" colspan="5"></td>
                                </tr>
                            </thead>
                            <tbody id="grid-body">
                                @forelse($items as $item)
                                <tr id="row-{{ $item->id }}" data-id="{{ $item->id }}"
                                    data-customer-id="{{ $item->customer_id }}"
                                    data-vendor-id="{{ $item->vendor_id }}"
                                    data-warehouse-id="{{ $item->warehouse_id }}"
                                    data-sku="{{ addslashes($item->sku) }}"
                                    data-item-name="{{ addslashes($item->item_name) }}"
                                    data-description="{{ addslashes($item->description ?? '') }}"
                                    data-upc-ean="{{ addslashes($item->upc_ean ?? '') }}"
                                    data-mpn="{{ addslashes($item->mpn ?? '') }}"
                                    data-hts-code="{{ addslashes($item->hts_code ?? '') }}"
                                    data-unit-id="{{ $item->unit_id }}"
                                    data-on-hand-qty="{{ $item->on_hand_qty }}"
                                    data-available-qty="{{ $item->available_qty }}"
                                    data-weight-kg="{{ $item->weight_kg }}"
                                    data-volume-cbm="{{ $item->volume_cbm }}"
                                    data-inner-pack="{{ $item->inner_pack }}"
                                    data-dimension-length="{{ $item->dimension_length }}"
                                    data-dimension-width="{{ $item->dimension_width }}"
                                    data-dimension-height="{{ $item->dimension_height }}"
                                    data-dimension-unit="{{ $item->dimension_unit ?? 'cm' }}"
                                    data-remark="{{ addslashes($item->remark ?? '') }}"
                                    data-create-date="{{ $item->create_date ? $item->create_date->format('Y-m-d') : '' }}"
                                    data-status="{{ $item->status ?? 'enable' }}"
                                    onclick="rowClick(event, this)">
                                    <td class="sticky-col" style="width:25px;text-align:center;left:0;" onclick="event.stopPropagation()">
                                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-check" onchange="updateToolbar()">
                                    </td>
                                    <td class="sticky-col" style="width:140px;left:25px;">{{ $item->warehouse->name ?? '—' }}</td>
                                    <td data-col="sku"><a href="javascript:;" class="col-link" onclick="event.stopPropagation();openEditModal(this)">{{ $item->sku }}</a></td>
                                    <td data-col="item_name">{{ $item->item_name }}</td>
                                    <td data-col="description" style="font-size:9px;color:#64748b;">{{ Str::limit($item->description, 50) }}</td>
                                    <td data-col="unit">{{ $item->unit->name ?? '—' }}</td>
                                    <td data-col="on_hand_qty" style="text-align:right;">{{ number_format($item->on_hand_qty, 2) }}</td>
                                    <td data-col="available_qty" style="text-align:right;">{{ number_format($item->available_qty, 2) }}</td>
                                    <td data-col="weight_kg" style="text-align:right;">{{ number_format($item->weight_kg, 2) }}</td>
                                    <td data-col="volume_cbm" style="text-align:right;">{{ number_format($item->volume_cbm, 2) }}</td>
                                    <td data-col="created_at">{{ $item->created_at ? $item->created_at->format('m-d-Y') : '—' }}</td>
                                </tr>
                                @empty
                                <tr id="empty-row">
                                    <td colspan="50" style="text-align:center;padding:30px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No warehouse items found.
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
                <div style="font-size:10px;color:#64748b;">
                    Showing <span id="stat-first">{{ $items->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $items->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $items->total() }}</span> records
                </div>
                <div id="pagination-container">{{ $items->links() }}</div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const STATE_KEY = 'warehouse_items_state';

    function showLoading() { const el = document.getElementById('grid-loading'); if (el) el.classList.add('show'); }
    function hideLoading() { const el = document.getElementById('grid-loading'); if (el) el.classList.remove('show'); }

    /* TOOLBAR STATE & SELECTION */
    function updateToolbar() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const all = [...document.querySelectorAll('.row-check')];
        const n = checked.length;
        const sa = document.getElementById('select-all');
        if (sa) {
            sa.checked = n === all.length && all.length > 0;
            sa.indeterminate = n > 0 && n < all.length;
        }
        document.getElementById('btn-delete').disabled = n === 0;
        document.getElementById('btn-copy').disabled = n !== 1;
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
        const skip = ['A', 'INPUT', 'BUTTON', 'I', 'SELECT', 'TEXTAREA'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    /* COPY */
    function copySelected() {
        const ids = getSelectedIds();
        if (ids.length !== 1) return;
        const tr = document.querySelector(`tr[data-id="${ids[0]}"]`);
        if (!tr) return;
        openEditModal(tr.querySelector('.col-link'));
        document.getElementById('item-id').value = '';
        document.getElementById('item-modal-title-text').textContent = 'Copy / Create Item';
        document.getElementById('item-sku').value = (tr.dataset.sku || '') + '-COPY';
        showToast('info', 'Item data copied to modal');
    }

    /* DELETE */
    function confirmDelete() {
        const ids = getSelectedIds();
        if (!ids.length) return;
        document.getElementById('confirm-msg').textContent = `Delete ${ids.length} item(s)? This cannot be undone.`;
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }

    async function executeDelete() {
        closeConfirm();
        const ids = getSelectedIds();
        if (!ids.length) return;
        showLoading();
        try {
            const res = await fetch("{{ route('items.bulk-delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            });
            const d = await res.json();
            if (d.success) {
                showToast('success', d.message);
                updateGrid(window.location.href);
            } else {
                showToast('error', d.message || 'Failed to delete items');
            }
        } catch(e) {
            showToast('error', 'Error deleting items');
        } finally {
            hideLoading();
        }
    }

    /* ITEM MODAL (CREATE / EDIT) */
    function openCreateModal() {
        document.getElementById('item-modal-title-text').textContent = 'Create Item';
        document.getElementById('item-id').value = '';
        document.getElementById('item-form').reset();
        document.getElementById('item-on_hand_qty').value = '0.00';
        document.getElementById('item-available_qty').value = '0.00';
        document.getElementById('item-weight_kg').value = '0.00';
        document.getElementById('item-volume_cbm').value = '0.00';
        document.getElementById('item-inner_pack').value = '0.00';
        document.getElementById('item-status').value = 'enable';
        document.getElementById('item-dimension_unit').value = 'cm';
        document.getElementById('item-modal-overlay').classList.add('open');
    }

    function openEditModal(link) {
        const tr = link.closest('tr');
        if (!tr) return;
        const d = tr.dataset;
        document.getElementById('item-modal-title-text').textContent = 'Edit Item';
        document.getElementById('item-id').value = d.id;

        document.getElementById('item-customer_id').value = d.customerId || '';
        document.getElementById('item-vendor_id').value = d.vendorId || '';
        document.getElementById('item-warehouse_id').value = d.warehouseId || '';
        document.getElementById('item-sku').value = d.sku || '';
        document.getElementById('item-item_name').value = d.itemName || '';
        document.getElementById('item-upc_ean').value = d.upcEan || '';
        document.getElementById('item-mpn').value = d.mpn || '';
        document.getElementById('item-hts_code').value = d.htsCode || '';
        document.getElementById('item-description').value = d.description || '';
        document.getElementById('item-inner_pack').value = d.innerPack || '0.00';
        document.getElementById('item-unit_id').value = d.unitId || '';
        document.getElementById('item-on_hand_qty').value = d.onHandQty || '0.00';
        document.getElementById('item-available_qty').value = d.availableQty || '0.00';
        document.getElementById('item-weight_kg').value = d.weightKg || '0.00';
        document.getElementById('item-volume_cbm').value = d.volumeCbm || '0.00';
        document.getElementById('item-dimension_length').value = d.dimensionLength || '';
        document.getElementById('item-dimension_width').value = d.dimensionWidth || '';
        document.getElementById('item-dimension_height').value = d.dimensionHeight || '';
        document.getElementById('item-dimension_unit').value = d.dimensionUnit || 'cm';
        document.getElementById('item-remark').value = d.remark || '';
        document.getElementById('item-create_date').value = d.createDate || '';
        document.getElementById('item-status').value = d.status || 'enable';

        document.getElementById('item-modal-overlay').classList.add('open');
    }

    function closeItemModal() { document.getElementById('item-modal-overlay').classList.remove('open'); }

    async function saveItem(e) {
        e.preventDefault();
        const id = document.getElementById('item-id').value;
        const isEdit = !!id;
        const url = isEdit ? `/warehouse/items/${id}` : "{{ route('items.store') }}";

        const form = document.getElementById('item-form');
        const formData = new FormData(form);
        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        showLoading();
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            const d = await res.json();
            if (d.success) {
                showToast('success', d.message);
                closeItemModal();
                updateGrid(window.location.href);
            } else {
                showToast('error', d.message || 'Failed to save item');
            }
        } catch(err) {
            showToast('error', 'Network error while saving item');
        } finally {
            hideLoading();
        }
    }

    /* COLOR PICKER */
    const COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' }
    ];
    let _activeColorItemId = null;

    function openColorPicker(id, currentColor) {
        _activeColorItemId = id;
        const grid = document.getElementById('color-picker-grid');
        grid.innerHTML = COLOR_OPTIONS.map(o => {
            const active = o.value === currentColor ? 'active' : '';
            return `<div class="color-picker-opt ${active}" onclick="selectColor('${o.value}')">
                <span class="swatch" style="background:${o.value}"></span>
                <span>${o.label}</span>
                <i class="fa fa-check"></i>
            </div>`;
        }).join('');
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _activeColorItemId = null;
    }

    async function selectColor(color) {
        const id = _activeColorItemId;
        if (!id) return;
        closeColorPicker();
        showLoading();
        try {
            const res = await fetch(`/warehouse/items/${id}/color`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ color })
            });
            const d = await res.json();
            if (d.success) {
                showToast('success', 'Status color updated');
                updateGrid(window.location.href);
            } else {
                showToast('error', 'Failed to update color');
            }
        } catch(e) {
            showToast('error', 'Error updating color');
        } finally {
            hideLoading();
        }
    }

    async function clearColor() {
        const id = _activeColorItemId;
        if (!id) return;
        closeColorPicker();
        showLoading();
        try {
            const res = await fetch(`/warehouse/items/${id}/color`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ color: '' })
            });
            const d = await res.json();
            if (d.success) {
                showToast('success', 'Status color cleared');
                updateGrid(window.location.href);
            } else {
                showToast('error', 'Failed to clear color');
            }
        } catch(e) {
            showToast('error', 'Error clearing color');
        } finally {
            hideLoading();
        }
    }

    /* FILTER & SEARCH */
    let filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        const row = document.getElementById('filter-row');
        if (row) row.style.display = filterOpen ? 'table-row' : 'none';
        document.getElementById('btn-filter').classList.toggle('active', filterOpen);
        if (filterOpen) {
            const params = new URLSearchParams(window.location.search);
            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                const map = { 2: 'filter_warehouse', 3: 'filter_sku', 4: 'filter_item_name', 5: 'filter_description', 6: 'filter_unit' };
                const param = map[inp.dataset.colIdx];
                if (param) inp.value = params.get(param) || '';
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
            const sort = url.searchParams.get('sort') || 'created_at';
            const dir = url.searchParams.get('dir') || 'desc';
            url.search = '';
            if (search) url.searchParams.set('search', search);
            url.searchParams.set('sort', sort);
            url.searchParams.set('dir', dir);
            const map = { 2: 'filter_warehouse', 3: 'filter_sku', 4: 'filter_item_name', 5: 'filter_description', 6: 'filter_unit' };
            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                const v = inp.value.trim();
                if (!v) return;
                const param = map[inp.dataset.colIdx];
                if (param) url.searchParams.set(param, v);
            });
            updateGrid(url.toString());
        }, 300);
    }

    let searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const url = new URL(window.location.href);
            if (!q) url.searchParams.delete('search');
            else url.searchParams.set('search', q);
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 300);
    }

    function clearSearch() {
        document.getElementById('quick-search').value = '';
        document.getElementById('clear-search-btn').style.display = 'none';
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        updateGrid(url.toString());
    }

    function toggleSort(field) {
        const url = new URL(window.location.href);
        let curSort = url.searchParams.get('sort') || 'created_at';
        let curDir = url.searchParams.get('dir') || 'desc';
        if (curSort === field) { curDir = curDir === 'asc' ? 'desc' : 'asc'; }
        else { curSort = field; curDir = 'asc'; }
        url.searchParams.set('sort', curSort);
        url.searchParams.set('dir', curDir);
        url.searchParams.delete('page');
        updateGrid(url.toString());
    }

    function refreshGrid() { updateGrid(window.location.href); }

    function updateExcelLink() {
        const url = new URL(window.location.href);
        const btn = document.getElementById('btn-excel');
        if (btn) btn.href = "{{ route('items.export-csv') }}" + url.search;
    }

    /* AJAX GRID UPDATE & STATE PERSISTENCE */
    function saveState() {
        try { sessionStorage.setItem(STATE_KEY, JSON.stringify({ url: window.location.href })); } catch(e){}
    }

    function restoreState() {
        try {
            const saved = sessionStorage.getItem(STATE_KEY);
            if (!saved) return;
            const data = JSON.parse(saved);
            if (data.url && data.url !== window.location.href) {
                updateGrid(data.url, false);
            }
        } catch(e){}
    }

    async function updateGrid(url, pushState = true) {
        showLoading();
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Network error');
            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');

            const newBody = doc.getElementById('grid-body');
            const newPagination = doc.getElementById('pagination-container');
            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            const stats = doc.querySelector('.portlet-tool.bottom div:first-child');
            if (stats) {
                const m = stats.textContent.match(/\d+/g);
                if (m && m.length >= 3) {
                    document.getElementById('stat-first').textContent = m[0];
                    document.getElementById('stat-last').textContent = m[1];
                    document.getElementById('stat-total').textContent = m[2];
                }
            }

            const urlObj = new URL(url);
            const sort = urlObj.searchParams.get('sort') || 'created_at';
            const dir = urlObj.searchParams.get('dir') || 'desc';
            document.querySelectorAll('[id^="sort-"]').forEach(i => i.className = 'fa fa-sort');
            const sortIcon = document.getElementById('sort-' + sort);
            if (sortIcon) sortIcon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');

            const searchVal = urlObj.searchParams.get('search') || '';
            const clearBtn = document.getElementById('clear-search-btn');
            if (clearBtn) clearBtn.style.display = searchVal ? 'inline' : 'none';

            if (pushState) {
                window.history.pushState({}, '', url);
                saveState();
            }
            updateExcelLink();
            updateToolbar();
            loadColumnConfig();
        } catch (e) {
            showToast('error', 'Failed to update grid');
        } finally {
            hideLoading();
        }
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) { e.preventDefault(); updateGrid(link.href); }
    });

    window.addEventListener('popstate', function() { updateGrid(window.location.href, false); });

    /* CONFIG PANEL */
    const PINNED_COLS = ['check', 'color', 'warehouse'];
    const CONFIG_STORAGE_KEY = 'warehouse_items_column_config';
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
        } catch(e){}
    }
    function toggleConfig() {
        const panel = document.getElementById('config-panel');
        const open = panel.style.display === 'none';
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
            const cb = document.createElement('input');
            cb.type = 'checkbox';
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
    function saveColumnConfig() { try { localStorage.setItem(CONFIG_STORAGE_KEY, JSON.stringify(getHiddenColumns())); } catch(e){} }
    function toggleColumn(colName, show) {
        const th = document.querySelector('#header-row th[data-col="' + colName + '"]');
        if (!th) return;
        const idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr').forEach(row => {
            const cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
        const filterTd = document.querySelector('#filter-row td[data-col="' + colName + '"]');
        if (filterTd) filterTd.style.display = show ? '' : 'none';
        saveColumnConfig();
    }
    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn = document.getElementById('btn-config');
        if (panel && btn && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* INIT */
    document.addEventListener('DOMContentLoaded', function() {
        restoreState();
        const params = new URLSearchParams(window.location.search);
        const sort = params.get('sort') || 'created_at';
        const dir = params.get('dir') || 'desc';
        const icon = document.getElementById('sort-' + sort);
        if (icon) icon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');
        loadColumnConfig();
        updateExcelLink();
        const hasFilter = ['search', 'filter_warehouse', 'filter_sku', 'filter_item_name', 'filter_description', 'filter_unit'].some(k => params.has(k) && params.get(k));
        if (hasFilter) {
            filterOpen = true;
            document.getElementById('filter-row').style.display = 'table-row';
            document.getElementById('btn-filter').classList.add('active');
        }
    });

    window.addEventListener('pageshow', function() { restoreState(); });

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
