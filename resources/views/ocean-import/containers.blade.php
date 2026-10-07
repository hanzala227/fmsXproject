<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        /* Editable Cell Styles */
        .cell-input, .cell-select {
            width: 100%;
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 11px;
            border-radius: 3px;
            background: white;
        }

        .cell-input:focus, .cell-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        .cell-input.changed, .cell-select.changed {
            background-color: #fef3c7;
            border-color: #f59e0b;
        }

        /* Save Button */
        .save-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #22c55e;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            animation: slideUp 0.3s ease-out;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .save-bar button {
            background: white;
            border: none;
            color: #22c55e;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .save-bar button:hover {
            background: #f0f0f0;
        }

        .save-bar .cancel-btn {
            background: transparent;
            color: white;
            border: 1px solid white;
        }

        .save-bar .cancel-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Button Group Styling */
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
        
        /* Mobile Responsive */
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
            }
            
            .tp-page-btn {
                min-width: 20px !important;
                height: 18px !important;
                padding: 0 4px !important;
                font-size: 8px !important;
            }
        }
        
        @media (max-width: 480px) {
            .grid-table { 
                font-size: 7px !important; 
                min-width: 1600px !important;
            }
            
            /* Only checkbox sticky on mobile */
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) {
                position: static !important;
                left: auto !important;
            }
        }
        
        @media (hover: none) and (pointer: coarse) {
            .btn-tool, .btn-action-round, .tp-page-btn {
                min-height: 28px !important;
                touch-action: manipulation;
            }
            .filter-input, .select-tool {
                min-height: 24px !important;
            }
            input[type="checkbox"] {
                width: 18px;
                height: 18px;
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
            <h4>Delete Container(s)?</h4>
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

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/ocean-import/list">Ocean Import</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Container List</span></li>
            </ul>
        </div>

        <div class="portlet light" x-data="containerGrid()" x-cloak>

            {{-- TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Container List</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <div x-show="hasChanges" x-transition style="display:flex;gap:4px;margin-right:8px;border-right:1px solid #e2e8f0;padding-right:8px;">
                        <button type="button" class="btn-action-round red-btn" @click="cancelChanges()" title="Cancel Changes">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn-action-round green-btn" @click="saveChanges($event)" title="Save Changes">
                            <i class="fa fa-save"></i> Save
                        </button>
                    </div>
                    <button class="btn-action-round" id="btn-filter" onclick="toggleFilter()" title="Toggle filter row">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <div style="position:relative;">
                        <button class="btn-action-round" id="btn-config" onclick="toggleConfig()" title="Column visibility">
                            <i class="fa fa-cogs"></i> Config
                        </button>
                        <div class="config-panel" id="config-panel" style="display:none;">
                            <div class="config-panel-title">Column Visibility</div>
                            <div id="col-toggles"></div>
                        </div>
                    </div>
                    <button class="btn-action-round white" onclick="exportExcel()" title="Export to Excel" id="btn-excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <div class="btn-group" style="display:flex;gap:0;">
                        <button class="btn-tool" id="btn-delete" disabled title="Delete selected" onclick="confirmDelete()"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="btn-group" style="display:flex;gap:0;">
                        <button class="btn-tool" id="btn-block" disabled style="padding:0 10px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 10px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:150px;" placeholder="Quick search…" oninput="quickSearch(this.value)">
                </div>
            </div>

            {{-- BULK-ACTION FORM + TABLE --}}
            <form id="bulk-form" method="POST" action="{{ route('ocean-import.bulk-delete') }}" style="margin:0;">
                @csrf
                @method('DELETE')
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="container-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;left:0;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="flag" style="width:25px;left:25px;text-align:center;"><i class="fa fa-flag"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="file_no" style="width:135px;left:50px;">File No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="container_no" style="width:125px;left:220px;">Container No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="consignee" style="width:150px;left:345px;">Consignee</th>

                                    <th data-col="remarks" style="width:200px;">Remarks</th>
                                    <th data-col="stages" style="width:100px;">Ship Mode / Type</th>
                                    <th data-col="hbl" style="width:150px;">HB/L No.</th>
                                    <th data-col="location" style="width:130px;">CY/CFS Location</th>
                                    <th data-col="rail" style="width:70px;">Has Rail</th>
                                    <th data-col="rail_code" style="width:100px;">Rail Code</th>
                                    <th data-col="etd" style="width:75px;">ETD</th>
                                    <th data-col="eta" style="width:75px;">ETA</th>
                                    <th data-col="last_edi" style="width:120px;">Last EDI</th>
                                    
                                    <!-- Container Fields -->
                                    <th data-col="ppctf" style="width:80px;">PP/CTF</th>
                                    <th data-col="tpsz" style="width:70px;">TP/SZ</th>
                                    <th data-col="seal_no" style="width:100px;">Seal No.</th>
                                    <th data-col="seal_no2" style="width:100px;">Seal No. 2</th>
                                    <th data-col="lfd" style="width:85px;">LFD</th>
                                    <th data-col="fdd" style="width:85px;">FDD</th>
                                    <th data-col="pkg" style="width:60px;">PKG</th>
                                    <th data-col="weight_kg" style="width:80px;">Weight(KG)</th>
                                    <th data-col="weight_lb" style="width:80px;">Weight(LB)</th>
                                    <th data-col="measure_cbm" style="width:90px;">Meas(CBM)</th>
                                    <th data-col="measure_cft" style="width:90px;">Meas(CFT)</th>
                                    <th data-col="dg" style="width:50px;">D.G</th>
                                    <th data-col="unload_vessel" style="width:100px;">Unload Vessel</th>
                                    <th data-col="gate_in" style="width:85px;">Gate In</th>
                                    <th data-col="rail_start" style="width:85px;">Rail Start</th>
                                    <th data-col="pod_eta" style="width:90px;">P.O.D ETA</th>
                                    <th data-col="appt" style="width:85px;">Appt.</th>
                                    <th data-col="pickup" style="width:85px;">Pick Up</th>
                                    <th data-col="gate_out" style="width:85px;">Gate Out</th>
                                    <th data-col="fdest_eta" style="width:90px;">F.Dest ETA</th>
                                    <th data-col="eta_door" style="width:85px;">ETA Door</th>
                                    <th data-col="ata_door" style="width:85px;">ATA Door</th>
                                    <th data-col="empty_conf" style="width:95px;">Empty Conf.</th>
                                    <th data-col="empty_ret" style="width:90px;">Empty Ret.</th>
                                    <th data-col="storage_start" style="width:100px;">Storage Start</th>
                                    <th data-col="storage_end" style="width:100px;">Storage End</th>
                                    <th data-col="pick_no" style="width:80px;">Pick No.</th>
                                    <th data-col="cprs_no" style="width:80px;">CPRS No.</th>
                                    <th data-col="cnru_no" style="width:80px;">CNRU No.</th>
                                    <th data-col="carrier_rel" style="width:85px;">Carrier Rel.</th>
                                    <th data-col="yard_loc" style="width:110px;">Yard Location</th>
                                    <th data-col="avail_pickup" style="width:90px;">Avail Pickup</th>
                                    <th data-col="trucker" style="width:110px;">Trucker</th>
                                    <th data-col="chassis_days" style="width:85px;">Chassis Days</th>
                                    <th data-col="c_hold" style="width:65px;">C.Hold</th>
                                    <th data-col="an" style="width:100px;">A/N</th>
                                    <th data-col="do" style="width:100px;">D/O</th>
                                    <th data-col="cont_remarks" style="width:150px;">Cont. Remarks</th>
                                    <th data-col="complete" style="width:70px;">Complete</th>
                                    
                                    <!-- Shipment Fields -->
                                    <th data-col="mbl_no" style="width:140px;">MB/L NO.</th>
                                    <th data-col="carrier" style="width:110px;">Carrier</th>
                                    <th data-col="vessel" style="width:110px;">Vessel</th>
                                    <th data-col="pol" style="width:110px;">POL</th>
                                    <th data-col="pod" style="width:110px;">POD</th>
                                    <th data-col="del" style="width:110px;">DEL</th>
                                    <th data-col="final_dest" style="width:110px;">Final Dest.</th>
                                    <th data-col="mbl_cy" style="width:110px;">MBL CY Loc.</th>
                                    <th data-col="office" style="width:100px;">Office</th>
                                    <th data-col="sales" style="width:100px;">Sales</th>
                                    <th data-col="operator" style="width:100px;">OP/Operator</th>
                                    <th data-col="shipper" style="width:130px;">Shipper</th>
                                    <th data-col="notify" style="width:130px;">Notify</th>
                                    <th data-col="customer" style="width:130px;">Customer</th>
                                    <th data-col="voyage" style="width:80px;">Voyage</th>
                                    <th data-col="ship_mode" style="width:80px;">Ship Mode</th>
                                    <th data-col="etb" style="width:85px;">ETB</th>
                                    <th data-col="obl" style="width:80px;">OB/L</th>
                                    <th data-col="freight_term" style="width:95px;">Freight Term</th>
                                    <th data-col="sales_type" style="width:90px;">Sales Type</th>
                                    <th data-col="isf_no" style="width:100px;">ISF No.</th>
                                    <th data-col="isf_3rd" style="width:75px;">ISF 3rd</th>
                                    <th data-col="isf_matched" style="width:100px;">ISF Matched</th>
                                    <th data-col="entry_no" style="width:100px;">Entry No.</th>
                                    <th data-col="entry_doc" style="width:110px;">Entry Doc Sent</th>
                                    <th data-col="contract_no" style="width:110px;">Contract No.</th>
                                    <th data-col="receipt" style="width:110px;">Place Receipt</th>
                                    <th data-col="receipt_etd" style="width:95px;">Receipt ETD</th>
                                    
                                    <!-- HBL Fields -->
                                    <th data-col="po_no" style="width:120px;">P.O. No.</th>
                                    <th data-col="express_bl" style="width:85px;">Express B/L</th>
                                    <th data-col="freight_rel" style="width:95px;">Freight Rel.</th>
                                    <th data-col="customs_doc" style="width:100px;">Customs Doc</th>
                                    <th data-col="c_clearance" style="width:90px;">C.Clearance</th>
                                    <th data-col="delivery_loc" style="width:110px;">Delivery Loc.</th>
                                </tr>

                                {{-- Filter Row --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:50px;"><input class="filter-input" style="width:100%;" data-param="filter_file_no" placeholder="File No…" oninput="applyFiltersTyping()"></td>
                                    <td class="sticky-col" style="left:185px;"></td>
                                    <td class="sticky-col" style="left:220px;"><input class="filter-input" style="width:100%;" data-param="filter_container_no" placeholder="Container…" oninput="applyFiltersTyping()"></td>
                                    <td class="sticky-col" style="left:345px;"><input class="filter-input" style="width:100%;" data-param="filter_consignee" placeholder="Consignee…" oninput="applyFiltersTyping()"></td>
                                    <td colspan="2"></td>
                                    <td><input class="filter-input" data-param="filter_hbl_no" placeholder="HB/L…" oninput="applyFiltersTyping()"></td>
                                    <td colspan="3"></td>
                                    <td><input class="filter-input" data-param="filter_etd" placeholder="ETD…" oninput="applyFiltersTyping()"></td>
                                    <td><input class="filter-input" data-param="filter_eta" placeholder="ETA…" oninput="applyFiltersTyping()"></td>
                                    <td colspan="80"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @include('ocean-import.partials.container-list-rows')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- BOTTOM TOOLBAR WITH PAGINATION --}}
            <div class="portlet-tool bottom">
                <div style="font-size:10px;color:#64748b;">
                    Showing <span id="stat-first">{{ $containers->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $containers->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $containers->total() }}</span> records
                </div>
                <div id="pagination-wrap">
                    {{ $containers->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            </div>
            </form>

        </div>
    </div>

    {{-- Hidden iframe for Excel download --}}
    <iframe id="excel-frame" style="display:none;"></iframe>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
// Alpine.js Component for Inline Editing
document.addEventListener('alpine:init', () => {
    Alpine.data('containerGrid', () => ({
        changedRows: {},
        hasChanges: false,
        
        markChanged(containerId, field, value) {
            if (!this.changedRows[containerId]) {
                this.changedRows[containerId] = {};
            }
            this.changedRows[containerId][field] = value;
            this.hasChanges = Object.keys(this.changedRows).length > 0;
            
            // Add visual indicator
            event.target.classList.add('changed');
        },
        
        async saveChanges(event = null) {
            const saveBtn = event ? event.currentTarget : document.querySelector('[title="Save Changes"]');
            const originalText = saveBtn ? saveBtn.innerHTML : '';
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving';
                saveBtn.disabled = true;
            }
            
            try {
                const response = await fetch('/ocean-import/containers/batch-update-inline', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        containers: this.changedRows
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove changed styling
                    document.querySelectorAll('.cell-input.changed, .cell-select.changed').forEach(el => {
                        el.classList.remove('changed');
                    });
                    
                    this.changedRows = {};
                    this.hasChanges = false;
                    showToast('success', data.message || 'Changes saved successfully!');
                } else {
                    showToast('error', data.message || 'Failed to save changes');
                }
            } catch (error) {
                console.error('Save error:', error);
                showToast('error', 'Error saving changes');
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        },
        
        cancelChanges() {
            if (confirm('Discard all unsaved changes?')) {
                // Reload page to reset
                window.location.reload();
            }
        }
    }));
});

var COLOR_OPTIONS = [
    { label: 'Urgent', value: '#E08283' },
    { label: 'Ready to bill', value: '#F3C200' },
    { label: 'Ready to close', value: '#25A69A' },
    { label: 'Postpone', value: '#4B77BE' },
    { label: 'Freight Finalized', value: '#9B9B9B' },
];

var _colorShipmentId = null;

/* ================================================================
   CSRF TOKEN
================================================================ */
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
   COLOR PICKER
================================================================ */
function openColorPicker(id, currentColor) {
    _colorShipmentId = id;
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
    var id = _colorShipmentId;
    fetch('/ocean-import/' + id + '/color', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
        body: JSON.stringify({ color: color }),
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
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
    fetch('/ocean-import/' + id + '/color', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
        body: JSON.stringify({ color: '' }),
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            showToast('success', 'Status color cleared');
            updateGrid();
        }
    }).catch(function() { showToast('error', 'Failed to clear color'); });
    closeColorPicker();
}

/* ================================================================
   SAVE REMARKS
================================================================ */
function saveRemarks(containerId, remarks) {
    fetch('/ocean-import/containers/' + containerId + '/remarks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
        body: JSON.stringify({ remarks: remarks }),
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            showToast('success', 'Remarks saved');
        } else {
            showToast('error', 'Failed to save remarks');
        }
    }).catch(function() { showToast('error', 'Error saving remarks'); });
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
    
    var btns = ['btn-delete', 'btn-block', 'btn-unblock'];
    for (var i = 0; i < btns.length; i++) {
        var el = document.getElementById(btns[i]);
        if (el) el.disabled = n === 0;
    }
    
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
    if (['A', 'INPUT', 'BUTTON', 'I'].indexOf(e.target.tagName) !== -1) return;
    var cb = row.querySelector('.row-check');
    if (cb) {
        cb.checked = !cb.checked;
        updateToolbar();
    }
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
            document.getElementById('pagination-wrap').innerHTML = data.pagination;
            document.getElementById('stat-first').textContent = data.first;
            document.getElementById('stat-last').textContent = data.last;
            document.getElementById('stat-total').textContent = data.total;
            updateToolbar();
            applyColVisibility();
        } else {
            showToast('error', data.error || 'Failed to update grid');
        }
    })
    .catch(function(error) {
        console.error('updateGrid error:', error);
        showToast('error', error.message || 'Failed to update grid');
    });
}

/* ================================================================
   SEARCH & FILTER
================================================================ */
var _searchTimer = null;
function quickSearch(value) {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(function() {
        var url = new URL(window.location.href);
        if (value) {
            url.searchParams.set('search', value);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('page');
        window.history.pushState({}, '', url);
        updateGrid();
    }, 400);
}

var _filterTimer = null;
function applyFiltersTyping() {
    clearTimeout(_filterTimer);
    _filterTimer = setTimeout(function() {
        applyFilters();
    }, 400);
}

function applyFilters() {
    var url = new URL(window.location.href);
    var filterInputs = document.querySelectorAll('.filter-input[data-param]');
    
    for (var i = 0; i < filterInputs.length; i++) {
        var inp = filterInputs[i];
        var param = inp.getAttribute('data-param');
        if (inp.value) {
            url.searchParams.set(param, inp.value);
        } else {
            url.searchParams.delete(param);
        }
    }
    
    url.searchParams.delete('page');
    window.history.pushState({}, '', url);
    updateGrid();
}

var _filterOpen = false;
function toggleFilter() {
    _filterOpen = !_filterOpen;
    var row = document.getElementById('filter-row');
    row.style.display = _filterOpen ? '' : 'none';
    var btn = document.getElementById('btn-filter');
    if (_filterOpen) {
        btn.style.background = '#3b82f6';
        btn.style.borderColor = '#2563eb';
        btn.style.color = '#fff';
    } else {
        btn.style.background = '';
        btn.style.borderColor = '';
        btn.style.color = '';
    }
}

/* ================================================================
   PAGINATION CLICK HANDLER
================================================================ */
document.addEventListener('click', function(e) {
    var link = e.target.closest('#pagination-wrap a');
    if (link && link.href) {
        e.preventDefault();
        window.history.pushState({}, '', link.href);
        updateGrid();
    }
});

/* ================================================================
   BULK OPERATIONS
================================================================ */
function confirmDelete() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;
    
    document.getElementById('confirm-msg').textContent = 
        'You are about to permanently delete ' + ids.length + ' container(s). This cannot be undone.';
    document.getElementById('confirm-overlay').classList.add('open');
}

function closeConfirm() {
    document.getElementById('confirm-overlay').classList.remove('open');
}

function executeDelete() {
    closeConfirm();
    var ids = getSelectedIds();
    if (ids.length === 0) return;
    
    var deleted = 0;
    var failed = 0;
    var promises = [];
    
    for (var i = 0; i < ids.length; i++) {
        var id = ids[i];
        var promise = fetch('/ocean-import/containers/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': getCSRF(), 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) { 
            if (d.success) deleted++; 
            else failed++; 
        })
        .catch(function() { failed++; });
        
        promises.push(promise);
    }
    
    Promise.all(promises).then(function() {
        if (deleted > 0) showToast('success', deleted + ' container(s) deleted.');
        if (failed > 0) showToast('error', failed + ' container(s) failed to delete.');
        setTimeout(function() { updateGrid(); }, 800);
    });
}

function blockSelected() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;
    
    var containers = [];
    for (var i = 0; i < ids.length; i++) {
        containers.push({ id: parseInt(ids[i]), is_customs_hold: true });
    }
    
    fetch('/ocean-import/containers/batch-update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRF(),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ containers: containers })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        showToast('success', d.message || 'Containers blocked.');
        setTimeout(function() { updateGrid(); }, 800);
    })
    .catch(function() {
        showToast('error', 'Block operation failed.');
    });
}

function unblockSelected() {
    var ids = getSelectedIds();
    if (ids.length === 0) return;
    
    var containers = [];
    for (var i = 0; i < ids.length; i++) {
        containers.push({ id: parseInt(ids[i]), is_customs_hold: false });
    }
    
    fetch('/ocean-import/containers/batch-update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRF(),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ containers: containers })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        showToast('success', d.message || 'Containers unblocked.');
        setTimeout(function() { updateGrid(); }, 800);
    })
    .catch(function() {
        showToast('error', 'Unblock operation failed.');
    });
}

/* ================================================================
   EXCEL EXPORT - NO HARD REFRESH
================================================================ */
function exportExcel() {
    var url = new URL(window.location.origin + '/ocean-import/containers-export-csv');
    
    var params = new URLSearchParams(window.location.search);
    var keys = ['search', 'filter_file_no', 'filter_container_no', 'filter_consignee', 'filter_hbl_no', 'filter_etd', 'filter_eta'];
    for (var i = 0; i < keys.length; i++) {
        if (params.has(keys[i])) {
            url.searchParams.set(keys[i], params.get(keys[i]));
        }
    }
    
    var iframe = document.getElementById('excel-frame');
    iframe.src = url.toString();
    
    showToast('success', 'Downloading Excel file...');
}

/* ================================================================
   COLUMN VISIBILITY
================================================================ */
var COL_DEFAULTS = {
    check: true, flag: true, file_no: true, color: true, container_no: true, consignee: true,
    remarks: true, stages: true, hbl: true, location: true, rail: true, rail_code: true,
    etd: true, eta: true, last_edi: true,
    ppctf: false, tpsz: false, seal_no: false, seal_no2: false, lfd: false, fdd: false,
    pkg: false, weight_kg: false, weight_lb: false, measure_cbm: false, measure_cft: false,
    dg: false, unload_vessel: false, gate_in: false, rail_start: false, pod_eta: false,
    appt: false, pickup: false, gate_out: false, fdest_eta: false, eta_door: false,
    ata_door: false, empty_conf: false, empty_ret: false, storage_start: false, storage_end: false,
    pick_no: false, cprs_no: false, cnru_no: false, carrier_rel: false, yard_loc: false,
    avail_pickup: false, trucker: false, chassis_days: false, c_hold: false, an: false,
    do: false, cont_remarks: false, complete: false,
    mbl_no: false, carrier: false, vessel: false, pol: false, pod: false, del: false,
    final_dest: false, mbl_cy: false, office: false, sales: false, operator: false,
    shipper: false, notify: false, customer: false, voyage: false, ship_mode: false,
    etb: false, obl: false, freight_term: false, sales_type: false, isf_no: false,
    isf_3rd: false, isf_matched: false, entry_no: false, entry_doc: false, contract_no: false,
    receipt: false, receipt_etd: false,
    po_no: false, express_bl: false, freight_rel: false, customs_doc: false, c_clearance: false,
    delivery_loc: false
};

function loadColPrefs() {
    var defaults = {};
    for (var k in COL_DEFAULTS) {
        defaults[k] = COL_DEFAULTS[k];
    }
    var saved = null;
    try {
        saved = JSON.parse(localStorage.getItem('containerCols'));
    } catch (e) {
        saved = null;
    }
    if (saved) {
        for (var k in saved) {
            defaults[k] = saved[k];
        }
    }
    return defaults;
}

function saveColPrefs(cols) {
    localStorage.setItem('containerCols', JSON.stringify(cols));
}

function applyColVisibility() {
    var cols = loadColPrefs();
    for (var key in cols) {
        var visible = cols[key];
        var elements = document.querySelectorAll('[data-col="' + key + '"]');
        for (var i = 0; i < elements.length; i++) {
            if (visible) {
                elements[i].style.display = '';
            } else {
                elements[i].style.display = 'none';
            }
        }
    }
}

var _configOpen = false;
function toggleConfig() {
    _configOpen = !_configOpen;
    var panel = document.getElementById('config-panel');
    
    if (_configOpen) {
        var cols = loadColPrefs();
        var html = '';
        for (var key in COL_DEFAULTS) {
            if (key === 'check') continue;
            var checked = cols[key] ? 'checked' : '';
            var label = key.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
            html += '<label><input type="checkbox" ' + checked + ' onchange="toggleCol(\'' + key + '\', this)"> ' + label + '</label>';
        }
        document.getElementById('col-toggles').innerHTML = html;
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

function toggleCol(name, checkbox) {
    var cols = loadColPrefs();
    cols[name] = checkbox.checked;
    saveColPrefs(cols);
    applyColVisibility();
}

/* ================================================================
   INITIALIZE ON PAGE LOAD
================================================================ */
document.addEventListener('DOMContentLoaded', function() {
    applyColVisibility();
    updateToolbar();
    
    var filterInputs = document.querySelectorAll('.filter-input[data-param]');
    var params = new URLSearchParams(window.location.search);
    
    for (var i = 0; i < filterInputs.length; i++) {
        var inp = filterInputs[i];
        var param = inp.getAttribute('data-param');
        if (params.has(param)) {
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
</x-layout>
