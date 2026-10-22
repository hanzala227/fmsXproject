<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .nav-tabs-custom { margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 4px; background: #fff; padding: 4px 8px 0 8px; border-radius: 4px 4px 0 0; }
        .nav-tabs-custom .tab-item { padding: 8px 16px; font-size: 11px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .nav-tabs-custom .tab-item:hover { color: #2563eb; background: #f8fafc; }
        .nav-tabs-custom .tab-item.active { color: #2563eb; font-weight: 700; border-bottom-color: #2563eb; background: #eff6ff; }

        .summary-cards { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
        .summary-card { background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; padding: 10px 16px; flex: 1; min-width: 140px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        .summary-card .card-label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
        .summary-card .card-value { font-size: 18px; font-weight: 700; color: #1e293b; margin-top: 2px; line-height: 1.2; }
        .summary-card .card-icon { float: right; font-size: 22px; color: #cbd5e1; margin-top: 4px; }

        .table-wrapper { overflow: auto; max-height: calc(100vh - 310px); position: relative; }
        .table-grid { border-collapse: separate; border-spacing: 0; width: 1960px; table-layout: fixed; font-size: 10px; }
        .table-grid thead tr:first-child th { position: sticky; top: 0; z-index: 20; background: #f8fafc; color: #475569; font-weight: 600; border-bottom: 1px solid #cbd5e1; border-right: 1px solid #e2e8f0; padding: 3px 6px; white-space: nowrap; height: 26px; text-align: left; user-select: none; box-sizing: border-box; }
        .table-grid thead tr:first-child th.sortable { cursor: pointer; }
        .table-grid thead tr:first-child th.sortable:hover { background: #e2e8f0; }

        /* Sticky Columns */
        .table-grid th.pin-0, .table-grid td.pin-0 { position: sticky; left: 0; z-index: 13; width: 36px; text-align: center; }
        .table-grid th.pin-1, .table-grid td.pin-1 { position: sticky; left: 36px; z-index: 13; width: 65px; text-align: center; }
        .table-grid th.pin-2, .table-grid td.pin-2 { position: sticky; left: 101px; z-index: 13; width: 130px; }
        .table-grid th.pin-3, .table-grid td.pin-3 { position: sticky; left: 231px; z-index: 13; width: 140px; }
        .table-grid th.pin-3 { border-right: 2px solid #cbd5e1 !important; }
        .table-grid td.pin-3 { border-right: 2px solid #cbd5e1 !important; }

        .table-grid thead tr:first-child th.pin-0, .table-grid thead tr:first-child th.pin-1, .table-grid thead tr:first-child th.pin-2, .table-grid thead tr:first-child th.pin-3 { background: #f8fafc; z-index: 23; }
        .table-grid tbody td.pin-0, .table-grid tbody td.pin-1, .table-grid tbody td.pin-2, .table-grid tbody td.pin-3 { background: #fff; z-index: 12; }
        .table-grid tbody tr:nth-of-type(even) td.pin-0, .table-grid tbody tr:nth-of-type(even) td.pin-1, .table-grid tbody tr:nth-of-type(even) td.pin-2, .table-grid tbody tr:nth-of-type(even) td.pin-3 { background-color: #fafbfc; }
        .table-grid tbody tr:hover td.pin-0, .table-grid tbody tr:hover td.pin-1, .table-grid tbody tr:hover td.pin-2, .table-grid tbody tr:hover td.pin-3 { background-color: #f1f5f9 !important; }

        .table-grid tbody td { padding: 3px 6px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; height: 24px; color: #334155; font-size: 10px; }
        .table-grid tbody tr:hover td { background-color: #f1f5f9 !important; }
        .table-grid tbody tr:nth-of-type(even) td { background-color: #fafbfc; }

        /* Sticky Footer */
        .table-grid tfoot tr td { position: sticky; bottom: 0; z-index: 15; background: #f1f5f9; font-weight: 700; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; color: #1e293b; height: 26px; padding: 4px 6px; }
        .table-grid tfoot tr td.pin-0, .table-grid tfoot tr td.pin-1, .table-grid tfoot tr td.pin-2, .table-grid tfoot tr td.pin-3 { z-index: 25; background: #f1f5f9; }

        .btn-action-icon { background: none; border: none; padding: 2px 4px; color: #64748b; cursor: pointer; font-size: 11px; border-radius: 2px; }
        .btn-action-icon:hover { color: #2563eb; background: #e2e8f0; }
        .btn-action-icon.danger:hover { color: #ef4444; background: #fee2e2; }

        .filter-row td { background: #eff6ff !important; padding: 2px 3px; border-bottom: 1px solid #bfdbfe; }
        .filter-row td input.filter-input { width: 100%; height: 18px; border: 1px solid #93c5fd; font-size: 9px; border-radius: 2px; padding: 0 3px; box-sizing: border-box; outline: none; background: #fff; }
        .filter-row td input.filter-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px rgba(59,130,246,0.2); }

        #grid-loading { display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.7); z-index: 50; align-items: center; justify-content: center; }
        #grid-loading.show { display: flex; }
        #grid-loading .spinner { width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* MODAL STYLES */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-card { background: #fff; border-radius: 6px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); width: 100%; max-width: 680px; overflow: hidden; border: 1px solid #e2e8f0; animation: modalSlide 0.2s ease-out; }
        @keyframes modalSlide { from { transform: translateY(-12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { background: #1e293b; color: #fff; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; font-weight: 700; font-size: 13px; }
        .modal-body { padding: 16px; max-height: calc(85vh - 100px); overflow-y: auto; font-size: 11px; }
        .modal-footer { padding: 10px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px; }

        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .form-group { display: flex; flex-direction: column; gap: 3px; }
        .form-group.full-width { grid-column: span 2; }
        .form-group label { font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.3px; }
        .form-group label .req { color: #ef4444; }
        .form-control-custom { height: 26px; border: 1px solid #cbd5e1; border-radius: 3px; padding: 0 8px; font-size: 11px; color: #1e293b; background: #fff; outline: none; transition: border-color 0.15s; width: 100%; box-sizing: border-box; }
        .form-control-custom:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li>Inventory <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">Summary</span></li>
            </ul>
        </div>

        {{-- TOP NAVIGATION TABS --}}
        <div class="nav-tabs-custom">
            <a href="{{ route('inventory.detail') }}" class="tab-item">
                <i class="fa fa-list-alt"></i> Inventory Detail
            </a>
            <a href="{{ route('inventory.summary') }}" class="tab-item active">
                <i class="fa fa-bar-chart"></i> Inventory Summary
            </a>
            <a href="{{ route('items.index') }}" class="tab-item">
                <i class="fa fa-cubes"></i> Warehouse Items List
            </a>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <span class="card-icon"><i class="fa fa-cubes"></i></span>
                <div class="card-label">Total Items</div>
                <div class="card-value" id="stat-total-items">{{ number_format($stats['total_items']) }}</div>
            </div>
            <div class="summary-card">
                <span class="card-icon"><i class="fa fa-cube"></i></span>
                <div class="card-label">On Hand Qty</div>
                <div class="card-value" id="stat-on-hand">{{ number_format($stats['total_on_hand'], 2) }}</div>
            </div>
            <div class="summary-card">
                <span class="card-icon"><i class="fa fa-check-circle"></i></span>
                <div class="card-label">Available Qty</div>
                <div class="card-value" id="stat-available">{{ number_format($stats['total_available'], 2) }}</div>
            </div>
            <div class="summary-card">
                <span class="card-icon"><i class="fa fa-balance-scale"></i></span>
                <div class="card-label">Total Weight</div>
                <div class="card-value" id="stat-weight">{{ number_format($stats['total_weight'], 2) }} KG</div>
            </div>
            <div class="summary-card">
                <span class="card-icon"><i class="fa fa-arrows"></i></span>
                <div class="card-label">Total Volume</div>
                <div class="card-value" id="stat-volume">{{ number_format($stats['total_volume'], 2) }} CBM</div>
            </div>
        </div>

        <div class="portlet light" style="position:relative;">
            <div id="grid-loading"><div class="spinner"></div></div>

            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Inventory Summary</span>
                    <span id="result-count" style="font-size:10px;color:#64748b;font-weight:400;">({{ $items->total() }} records)</span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" style="background:#16a34a;color:#fff;border-color:#16a34a;" onclick="openCreateModal()">
                        <i class="fa fa-plus"></i> New Item
                    </button>
                    <button class="btn-action-round white danger" id="btn-bulk-delete" style="display:none;color:#ef4444;border-color:#fca5a5;" onclick="confirmBulkDelete()">
                        <i class="fa fa-trash"></i> Delete Selected (<span id="selected-count">0</span>)
                    </button>
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
                    <button class="btn-action-round" onclick="refreshGrid()" title="Refresh"><i class="fa fa-refresh"></i></button>
                    <button class="btn-action-round" onclick="window.print()" title="Print"><i class="fa fa-print"></i></button>
                    <a class="btn-action-round white" id="btn-excel" href="{{ route('inventory.summary.export-csv') }}" title="Download as CSV" target="_blank"><i class="fa fa-file-excel-o"></i> Excel</a>
                </div>
            </div>

            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">&nbsp;</div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:180px;" placeholder="Search SKU, product, UPC..." value="{{ request('search') }}" oninput="quickSearch(this.value)">
                    <button id="clear-search" class="btn-tool" style="display:none;padding:0 6px;height:18px;font-size:9px;" onclick="clearSearch()" title="Clear search"><i class="fa fa-times-circle"></i></button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-wrapper">
                    <table class="table-grid" id="grid-table">
                        <thead>
                            <tr id="header-row">
                                <th class="pin-0"><input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"></th>
                                <th class="pin-1">Action</th>
                                <th class="pin-2" data-col="customer">Customer</th>
                                <th class="pin-3" data-col="warehouse">
                                    <a href="javascript:;" onclick="toggleSort('warehouse_id')" style="color:inherit;text-decoration:none;">Warehouse <i class="fa fa-sort" id="sort-warehouse_id"></i></a>
                                </th>
                                <th data-col="sku" style="width:105px;">
                                    <a href="javascript:;" onclick="toggleSort('sku')" style="color:inherit;text-decoration:none;">SKU No. <i class="fa fa-sort" id="sort-sku"></i></a>
                                </th>
                                <th data-col="po" style="width:110px;">Customer P.O.</th>
                                <th data-col="item_name" style="width:180px;">
                                    <a href="javascript:;" onclick="toggleSort('item_name')" style="color:inherit;text-decoration:none;">Product Description <i class="fa fa-sort" id="sort-item_name"></i></a>
                                </th>
                                <th data-col="bl_no" style="width:100px;">B/L No.</th>
                                <th data-col="office" style="width:70px;">Office</th>
                                <th data-col="upc" style="width:80px;">UPC/EAN</th>
                                <th data-col="on_hand_qty" style="width:100px;text-align:right;">
                                    <a href="javascript:;" onclick="toggleSort('on_hand_qty')" style="color:inherit;text-decoration:none;">On Hand Qty <i class="fa fa-sort" id="sort-on_hand_qty"></i></a>
                                </th>
                                <th data-col="allocated" style="width:100px;text-align:right;">Allocated Qty</th>
                                <th data-col="available_qty" style="width:100px;text-align:right;">
                                    <a href="javascript:;" onclick="toggleSort('available_qty')" style="color:inherit;text-decoration:none;">Available Qty <i class="fa fa-sort" id="sort-available_qty"></i></a>
                                </th>
                                <th data-col="unit" style="width:70px;">Qty Unit</th>
                                <th data-col="weight_kg" style="width:100px;text-align:right;">
                                    <a href="javascript:;" onclick="toggleSort('weight_kg')" style="color:inherit;text-decoration:none;">Weight <i class="fa fa-sort" id="sort-weight_kg"></i></a>
                                </th>
                                <th data-col="volume_cbm" style="width:100px;text-align:right;">
                                    <a href="javascript:;" onclick="toggleSort('volume_cbm')" style="color:inherit;text-decoration:none;">Measurement <i class="fa fa-sort" id="sort-volume_cbm"></i></a>
                                </th>
                                <th data-col="status" style="width:80px;">Status</th>
                            </tr>
                            <tr id="filter-row" class="filter-row" style="display:none;">
                                <td class="pin-0"></td>
                                <td class="pin-1"></td>
                                <td class="pin-2" data-col="customer"><input class="filter-input" data-col-idx="0" placeholder="Customer..." oninput="applyFilters()"></td>
                                <td class="pin-3" data-col="warehouse"><input class="filter-input" data-col-idx="1" placeholder="Warehouse..." oninput="applyFilters()"></td>
                                <td data-col="sku"><input class="filter-input" data-col-idx="2" placeholder="SKU..." oninput="applyFilters()"></td>
                                <td data-col="po"></td>
                                <td data-col="item_name"><input class="filter-input" data-col-idx="3" placeholder="Product..." oninput="applyFilters()"></td>
                                <td data-col="bl_no"></td>
                                <td data-col="office"></td>
                                <td data-col="upc"></td>
                                <td data-col="on_hand_qty"></td>
                                <td data-col="allocated"></td>
                                <td data-col="available_qty"></td>
                                <td data-col="unit"></td>
                                <td data-col="weight_kg"></td>
                                <td data-col="volume_cbm"></td>
                                <td data-col="status"></td>
                            </tr>
                        </thead>
                        <tbody id="grid-body">
                            @forelse($items as $item)
                            <tr data-id="{{ $item->id }}"
                                data-warehouse_id="{{ $item->warehouse_id }}"
                                data-customer_id="{{ $item->customer_id }}"
                                data-sku="{{ $item->sku }}"
                                data-item_name="{{ $item->item_name }}"
                                data-description="{{ $item->description }}"
                                data-upc_ean="{{ $item->upc_ean }}"
                                data-unit_id="{{ $item->unit_id }}"
                                data-on_hand_qty="{{ $item->on_hand_qty }}"
                                data-available_qty="{{ $item->available_qty }}"
                                data-weight_kg="{{ $item->weight_kg }}"
                                data-volume_cbm="{{ $item->volume_cbm }}"
                                data-status="{{ $item->status }}">
                                <td class="pin-0"><input type="checkbox" class="row-checkbox" value="{{ $item->id }}" onclick="updateRowSelection()"></td>
                                <td class="pin-1">
                                    <button class="btn-action-icon" title="Edit Item" onclick="openEditModal({{ $item->id }})"><i class="fa fa-pencil"></i></button>
                                    <button class="btn-action-icon danger" title="Delete Item" onclick="deleteSingleItem({{ $item->id }})"><i class="fa fa-trash"></i></button>
                                </td>
                                <td class="pin-2">{{ $item->customer?->name ?? $item->latestReceivingItem?->receiving?->customer?->name ?? '—' }}</td>
                                <td class="pin-3">{{ $item->warehouse->name ?? '—' }}</td>
                                <td data-col="sku"><a href="javascript:;" class="col-link" onclick="openEditModal({{ $item->id }})">{{ $item->sku }}</a></td>
                                <td data-col="po">{{ $item->latestReceivingItem?->customer_po ?? '—' }}</td>
                                <td data-col="item_name">{{ $item->item_name }}</td>
                                <td data-col="bl_no">{{ $item->latestReceivingItem?->receiving?->bl_no ?? '—' }}</td>
                                <td data-col="office">{{ $item->latestReceivingItem?->receiving?->office?->code ?? '—' }}</td>
                                <td data-col="upc">{{ $item->upc_ean ?? '—' }}</td>
                                <td data-col="on_hand_qty" style="text-align:right;">{{ number_format($item->on_hand_qty ?? 0, 2) }}</td>
                                <td data-col="allocated" style="text-align:right;">0.00</td>
                                <td data-col="available_qty" style="text-align:right;">{{ number_format($item->available_qty ?? 0, 2) }}</td>
                                <td data-col="unit">{{ $item->unit->name ?? 'CTN' }}</td>
                                <td data-col="weight_kg" style="text-align:right;">{{ number_format($item->weight_kg ?? 0, 2) }} KG</td>
                                <td data-col="volume_cbm" style="text-align:right;">{{ number_format($item->volume_cbm ?? 0, 2) }} CBM</td>
                                <td data-col="status"><span class="badge {{ $item->status === 'enable' ? 'badge-success' : 'badge-secondary' }}">{{ strtoupper($item->status ?? 'enable') }}</span></td>
                            </tr>
                            @empty
                            <tr id="empty-row">
                                <td colspan="17" style="text-align:center;padding:30px;color:#94a3b8;">
                                    <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                    No inventory summary records found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="pin-0"></td>
                                <td class="pin-1">TOTAL</td>
                                <td class="pin-2" data-col="customer"></td>
                                <td class="pin-3" data-col="warehouse"></td>
                                <td data-col="sku"></td>
                                <td data-col="po"></td>
                                <td data-col="item_name"></td>
                                <td data-col="bl_no"></td>
                                <td data-col="office"></td>
                                <td data-col="upc"></td>
                                <td data-col="on_hand_qty" style="text-align:right;" id="foot-on-hand">{{ number_format($stats['total_on_hand'], 2) }}</td>
                                <td data-col="allocated" style="text-align:right;">0.00</td>
                                <td data-col="available_qty" style="text-align:right;" id="foot-available">{{ number_format($stats['total_available'], 2) }}</td>
                                <td data-col="unit"></td>
                                <td data-col="weight_kg" style="text-align:right;" id="foot-weight">{{ number_format($stats['total_weight'], 2) }} KG</td>
                                <td data-col="volume_cbm" style="text-align:right;" id="foot-volume">{{ number_format($stats['total_volume'], 2) }} CBM</td>
                                <td data-col="status"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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

    {{-- CREATE / EDIT MODAL --}}
    <div class="modal-overlay" id="summary-modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <span id="modal-title"><i class="fa fa-cube"></i> New Inventory Summary Item</span>
                <button type="button" style="background:none;border:none;color:#fff;font-size:16px;cursor:pointer;" onclick="closeSummaryModal()">&times;</button>
            </div>
            <form id="summary-form" onsubmit="saveSummaryItem(event)">
                @csrf
                <input type="hidden" id="summary-item-id" value="">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Warehouse <span class="req">*</span></label>
                            <select id="modal-warehouse_id" class="form-control-custom" required>
                                <option value="">Select Warehouse..</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>SKU No. <span class="req">*</span></label>
                            <input type="text" id="modal-sku" class="form-control-custom" required placeholder="SKU-10001">
                        </div>

                        <div class="form-group full-width">
                            <label>Product Description <span class="req">*</span></label>
                            <input type="text" id="modal-item_name" class="form-control-custom" required placeholder="Product Name / Description">
                        </div>

                        <div class="form-group">
                            <label>Customer Partner</label>
                            <select id="modal-customer_id" class="form-control-custom">
                                <option value="">Select Customer (Optional)..</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>UPC / EAN Barcode</label>
                            <input type="text" id="modal-upc_ean" class="form-control-custom" placeholder="UPC Code">
                        </div>

                        <div class="form-group">
                            <label>Quantity Unit</label>
                            <select id="modal-unit_id" class="form-control-custom">
                                <option value="">Select Unit..</option>
                                @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select id="modal-status" class="form-control-custom">
                                <option value="enable">Enable (Active)</option>
                                <option value="disable">Disable (Inactive)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>On Hand Qty <span class="req">*</span></label>
                            <input type="number" step="0.01" id="modal-on_hand_qty" class="form-control-custom" required value="0.00">
                        </div>

                        <div class="form-group">
                            <label>Available Qty <span class="req">*</span></label>
                            <input type="number" step="0.01" id="modal-available_qty" class="form-control-custom" required value="0.00">
                        </div>

                        <div class="form-group">
                            <label>Weight (KG)</label>
                            <input type="number" step="0.01" id="modal-weight_kg" class="form-control-custom" value="0.00">
                        </div>

                        <div class="form-group">
                            <label>Volume (CBM)</label>
                            <input type="number" step="0.01" id="modal-volume_cbm" class="form-control-custom" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action-round white" onclick="closeSummaryModal()">Cancel</button>
                    <button type="submit" class="btn-action-round" style="background:#2563eb;color:#fff;" id="btn-save-summary"><i class="fa fa-save"></i> Save Item</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const FILTER_MAP = { 0: 'filter_customer', 1: 'filter_warehouse', 2: 'filter_sku', 3: 'filter_item_name' };
    const STATE_KEY = 'warehouse_inventory_summary_state';

    function showLoading() { const el = document.getElementById('grid-loading'); if (el) el.classList.add('show'); }
    function hideLoading() { const el = document.getElementById('grid-loading'); if (el) el.classList.remove('show'); }

    /* STATE PERSISTENCE */
    function saveState() {
        const state = { url: window.location.href };
        try { sessionStorage.setItem(STATE_KEY, JSON.stringify(state)); } catch(e){}
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

    function updateExcelLink() {
        const btn = document.getElementById('btn-excel');
        if (!btn) return;
        const url = new URL(window.location.href);
        btn.href = "{{ route('inventory.summary.export-csv') }}" + url.search;
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
    function toggleFilter() {
        const row = document.getElementById('filter-row');
        const isVisible = row.style.display === 'table-row';
        row.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);

        if (!isVisible) {
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
        url.searchParams.delete('page');
        updateGrid(url.toString());
    }

    /* MULTI-SELECT & BULK DELETE */
    function toggleSelectAll(master) {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = master.checked);
        updateRowSelection();
    }

    function updateRowSelection() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const count = checked.length;
        const btn = document.getElementById('btn-bulk-delete');
        const badge = document.getElementById('selected-count');
        if (btn && badge) {
            btn.style.display = count > 0 ? 'inline-flex' : 'none';
            badge.textContent = count;
        }
        const master = document.getElementById('select-all');
        const all = document.querySelectorAll('.row-checkbox');
        if (master && all.length > 0) {
            master.checked = count === all.length;
        }
    }

    async function confirmBulkDelete() {
        const checked = [...document.querySelectorAll('.row-checkbox:checked')].map(cb => cb.value);
        if (!checked.length) return;
        if (!confirm(`Are you sure you want to delete ${checked.length} selected item(s)?`)) return;

        showLoading();
        try {
            const res = await fetch("{{ route('inventory.summary.bulk-delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: checked })
            });
            const data = await res.json();
            if (data.success) {
                showToast('success', data.message);
                refreshGrid();
            } else {
                showToast('error', data.message || 'Failed to delete items.');
            }
        } catch(e) {
            showToast('error', 'Network error while deleting items.');
        } finally {
            hideLoading();
        }
    }

    async function deleteSingleItem(id) {
        if (!confirm('Are you sure you want to delete this inventory summary item?')) return;
        showLoading();
        try {
            const res = await fetch(`/warehouse/inventory/summary/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showToast('success', data.message);
                refreshGrid();
            } else {
                showToast('error', data.message || 'Failed to delete item.');
            }
        } catch(e) {
            showToast('error', 'Network error while deleting item.');
        } finally {
            hideLoading();
        }
    }

    /* MODAL CREATE / EDIT */
    function openCreateModal() {
        document.getElementById('modal-title').innerHTML = '<i class="fa fa-cube"></i> New Inventory Summary Item';
        document.getElementById('summary-item-id').value = '';
        document.getElementById('summary-form').reset();
        document.getElementById('modal-on_hand_qty').value = '0.00';
        document.getElementById('modal-available_qty').value = '0.00';
        document.getElementById('modal-weight_kg').value = '0.00';
        document.getElementById('modal-volume_cbm').value = '0.00';
        document.getElementById('modal-status').value = 'enable';
        document.getElementById('summary-modal-overlay').classList.add('show');
    }

    function openEditModal(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;
        document.getElementById('modal-title').innerHTML = '<i class="fa fa-pencil"></i> Edit Inventory Summary Item';
        document.getElementById('summary-item-id').value = id;

        document.getElementById('modal-warehouse_id').value = row.dataset.warehouse_id || '';
        document.getElementById('modal-sku').value = row.dataset.sku || '';
        document.getElementById('modal-item_name').value = row.dataset.item_name || '';
        document.getElementById('modal-customer_id').value = row.dataset.customer_id || '';
        document.getElementById('modal-upc_ean').value = row.dataset.upc_ean || '';
        document.getElementById('modal-unit_id').value = row.dataset.unit_id || '';
        document.getElementById('modal-on_hand_qty').value = row.dataset.on_hand_qty || '0.00';
        document.getElementById('modal-available_qty').value = row.dataset.available_qty || '0.00';
        document.getElementById('modal-weight_kg').value = row.dataset.weight_kg || '0.00';
        document.getElementById('modal-volume_cbm').value = row.dataset.volume_cbm || '0.00';
        document.getElementById('modal-status').value = row.dataset.status || 'enable';

        document.getElementById('summary-modal-overlay').classList.add('show');
    }

    function closeSummaryModal() {
        document.getElementById('summary-modal-overlay').classList.remove('show');
    }

    async function saveSummaryItem(e) {
        e.preventDefault();
        const id = document.getElementById('summary-item-id').value;
        const isEdit = !!id;
        const url = isEdit ? `/warehouse/inventory/summary/${id}` : "{{ route('inventory.summary.store') }}";
        const method = isEdit ? 'PUT' : 'POST';

        const payload = {
            warehouse_id: document.getElementById('modal-warehouse_id').value,
            sku: document.getElementById('modal-sku').value,
            item_name: document.getElementById('modal-item_name').value,
            customer_id: document.getElementById('modal-customer_id').value || null,
            upc_ean: document.getElementById('modal-upc_ean').value || null,
            unit_id: document.getElementById('modal-unit_id').value || null,
            on_hand_qty: parseFloat(document.getElementById('modal-on_hand_qty').value) || 0,
            available_qty: parseFloat(document.getElementById('modal-available_qty').value) || 0,
            weight_kg: parseFloat(document.getElementById('modal-weight_kg').value) || 0,
            volume_cbm: parseFloat(document.getElementById('modal-volume_cbm').value) || 0,
            status: document.getElementById('modal-status').value
        };

        showLoading();
        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast('success', data.message);
                closeSummaryModal();
                refreshGrid();
            } else {
                showToast('error', data.message || 'Failed to save summary item.');
            }
        } catch(err) {
            showToast('error', 'Network error while saving item.');
        } finally {
            hideLoading();
        }
    }

    /* AJAX GRID UPDATE */
    async function updateGrid(url, pushState = true) {
        showLoading();
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network error');
            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');

            const newBody = doc.getElementById('grid-body');
            const newPagination = doc.getElementById('pagination-container');
            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            ['stat-total-items', 'stat-on-hand', 'stat-available', 'stat-weight', 'stat-volume'].forEach(id => {
                const newVal = doc.getElementById(id);
                const oldVal = document.getElementById(id);
                if (newVal && oldVal) oldVal.textContent = newVal.textContent;
            });

            ['foot-on-hand', 'foot-available', 'foot-weight', 'foot-volume'].forEach(id => {
                const newVal = doc.getElementById(id);
                const oldVal = document.getElementById(id);
                if (newVal && oldVal) oldVal.textContent = newVal.textContent;
            });

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

            const params = new URLSearchParams(new URL(url).search);
            const sort = params.get('sort') || 'created_at';
            const dir = params.get('dir') || 'desc';
            document.querySelectorAll('[id^="sort-"]').forEach(i => i.className = 'fa fa-sort');
            const sortIcon = document.getElementById('sort-' + sort);
            if (sortIcon) sortIcon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');

            if (pushState) {
                window.history.pushState({}, '', url);
                saveState();
            }
            updateExcelLink();
            updateClearSearch();
            loadColumnConfig();
            updateRowSelection();
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

    /* CONFIG */
    const PINNED = ['customer', 'warehouse', 'sku'];
    const STORAGE_KEY = 'inventory_summary_column_config';
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
            document.querySelectorAll('#grid-table tfoot td[data-col]').forEach(td => {
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
        const footTd = document.querySelector(`#grid-table tfoot td[data-col="${colName}"]`);
        if (footTd) footTd.style.display = show ? '' : 'none';
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
        updateClearSearch();
    });

    window.addEventListener('pageshow', function() { restoreState(); });
    window.addEventListener('popstate', function() { updateGrid(window.location.href, false); });

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
