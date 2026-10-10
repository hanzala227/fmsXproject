<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        .nav-tabs-custom { margin-bottom: 8px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 4px; background: #fff; padding: 4px 8px 0 8px; border-radius: 2px 2px 0 0; }
        .nav-tabs-custom .tab-item { padding: 6px 14px; font-size: 11px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; }
        .nav-tabs-custom .tab-item:hover { color: #2563eb; background: #f8fafc; }
        .nav-tabs-custom .tab-item.active { color: #2563eb; font-weight: 700; border-bottom-color: #2563eb; background: #eff6ff; }
        [x-cloak] { display: none !important; }
        .form-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px 12px; }
        .form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px 12px; }
        @media (max-width: 1400px) { .form-grid-4 { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 1100px) { .form-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .form-grid-4, .form-grid-3 { grid-template-columns: 1fr; } }
        .table-custom tbody input, .table-custom tbody select { height: 20px; font-size: 9px; padding: 0 3px; border: 1px solid #cbd5e1; border-radius: 2px; background: #fff; box-sizing: border-box; }
        .table-custom tbody input:focus, .table-custom tbody select:focus { border-color: #3b82f6; outline: none; }
        .btn-tool-icon { background: #fff; border: 1px solid #cbd5e1; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; color: #475569; border-radius: 2px; transition: all 0.2s; }
        .btn-tool-icon:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool-icon.green { background: #22c55e; color: #fff; border-color: #16a34a; }
        .action-bar { text-align: center; padding: 15px 0; margin-top: 15px; display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
        .input-number { text-align: right; }
    </style>
    @endpush

    <div class="page-content" x-data="warehouseReceiptCreateApp()">
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
        <form id="receiptForm" action="{{ isset($receipt) ? route('warehouse.receipts.update', $receipt->id) : route('warehouse.receipts.store') }}" method="POST">
            @csrf
            @if(isset($receipt)) @method('PUT') @endif
            <input type="hidden" name="save_action" x-model="saveAction">
            <input type="hidden" name="auto_gen" :value="autoGen ? '1' : '0'">
            <input type="hidden" name="items_json" :value="JSON.stringify(items)">
            <input type="hidden" name="charges_json" :value="JSON.stringify(charges)">
            <input type="hidden" name="memos_json" :value="JSON.stringify(memos)">

            @if(session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;padding:10px 15px;border-radius:4px;margin-bottom:15px;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:11px;">
                    <strong>Validation Error</strong>
                    <ul style="margin:5px 0 0 15px;padding:0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <!-- Breadcrumb -->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('warehouse.receipts.index') }}">Warehouse</a> <i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('warehouse.receipts.index') }}">Receipts</a> <i class="fa fa-angle-right"></i></li>
                    <li><span style="color:#333;font-weight:700;">{{ isset($receipt) ? 'Edit: ' . $receipt->receipt_no : 'New Receipt' }}</span></li>
                </ul>
            </div>

            <!-- Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <h1 class="caption-subject" style="font-size:16px;color:#1e293b;margin:0;">
                    <i class="fa fa-file-text-o"></i> Warehouse Receipt — {{ isset($receipt) ? 'Edit' : 'New Entry' }}
                </h1>
                <div style="display:flex;gap:4px;">
                    <button type="submit" class="btn-freightx" @click="saveAction='save_close'; if(!validateForm()) $event.preventDefault();"><i class="fa fa-save"></i> @if(isset($receipt)) UPDATE @else SAVE @endif</button>
                    <a href="{{ route('warehouse.receipts.index') }}" class="btn-default-gf">BACK TO LIST</a>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="gf-tabs">
                <li :class="activeTab === 'basic' ? 'active' : ''"><a @click="activeTab = 'basic'">Basic</a></li>
                <li style="display: none !important;" :class="activeTab === 'doc' ? 'active' : ''" class="@if(!isset($receipt)) disabled-tab @endif">
                    <a @if(isset($receipt)) @click="activeTab = 'doc'" @endif>
                        Doc Center
                        @if(!isset($receipt)) <span style="font-size:8px;color:#94a3b8;margin-left:3px;">(Save first)</span> @endif
                    </a>
                </li>
            </ul>

            <!-- ═══════════════════════ BASIC TAB ═══════════════════════ -->
            <div x-show="activeTab === 'basic'" class="main-grid">
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-file-text-o"></i> Receipt Information</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-4">
                            <!-- Column 1 -->
                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span style="color:red;">*</span> Receipt No.</label>
                                    <div class="form-input-container">
                                        <div style="display:flex;gap:2px;width:100%;">
                                            <div style="padding:2px 4px;border:1px solid #cbd5e1;border-right:none;border-radius:2px 0 0 2px;display:flex;align-items:center;background:#f8fafc;">
                                                <input type="checkbox" x-model="autoGen" @change="if(autoGen) generateReceiptNo()" style="margin:0;">
                                            </div>
                                            <input type="text" name="receipt_no" class="form-control-gf" x-model="form.receipt_no" :readonly="autoGen" required style="border-radius:0 2px 2px 0;">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span style="color:red;">*</span> Received Date</label>
                                    <div class="form-input-container">
                                        <input type="datetime-local" name="receipt_date" class="form-control-gf" x-model="form.receipt_date" required>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Received By</label>
                                    <div class="form-input-container">
                                        <select name="operator_id" class="form-control-gf" x-model="form.operator_id">
                                            <option value="">Select...</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ (isset($receipt) && $receipt->operator_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">B/L No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="tracking_no" class="form-control-gf" x-model="form.tracking_no" placeholder="Truck B/L">
                                    </div>
                                </div>
                            </div>
                            <!-- Column 2 -->
                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Location Code</label>
                                    <div class="form-input-container">
                                        <input type="text" name="location_code" class="form-control-gf" x-model="form.location_code" placeholder="e.g. MEO-W1">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Loaded Date</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" disabled x-model="loadedDateTime">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Maker</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" disabled x-model="makerName">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Shipper</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="shipper_id" :options="$shippers" module="trade-partner" x-model="form.shipper_id" class="form-control-gf" />
                                    </div>
                                </div>
                            </div>
                            <!-- Column 3 -->
                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Consignee</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="consignee_id" :options="$consignees" module="trade-partner" x-model="form.consignee_id" class="form-control-gf" />
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Delivered Carrier</label>
                                    <div class="form-input-container">
                                        <input type="text" name="carrier_name" class="form-control-gf" x-model="form.carrier_name">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Delivered By</label>
                                    <div class="form-input-container">
                                        <input type="text" name="delivered_by" class="form-control-gf" x-model="form.delivered_by" placeholder="Driver name">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Freight Charges</label>
                                    <div class="form-input-container">
                                        <select name="freight_charge_type" class="form-control-gf" x-model="form.freight_charge_type">
                                            <option value="Prepaid">Prepaid</option>
                                            <option value="Collect">Collect</option>
                                            <option value="Third Party">Third Party</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Column 4 -->
                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Freight Amount</label>
                                    <div class="form-input-container">
                                        <input type="number" step="0.01" name="freight_amount" class="form-control-gf input-number" x-model="form.freight_amount">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Check No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="check_no" class="form-control-gf" x-model="form.check_no">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Cargo Type</label>
                                    <div class="form-input-container">
                                        <div style="display:flex;gap:8px;">
                                            <label style="font-size:10px;font-weight:600;display:flex;align-items:center;gap:3px;"><input type="radio" value="OTH" x-model="form.cargo_type"> Others</label>
                                            <label style="font-size:10px;font-weight:600;display:flex;align-items:center;gap:3px;"><input type="radio" value="MOB" x-model="form.cargo_type"> Auto</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span style="color:red;">*</span> Office</label>
                                    <div class="form-input-container">
                                        <select name="office_id" class="form-control-gf" x-model="form.office_id">
                                            <option value="">Select...</option>
                                            @foreach($offices as $office)
                                                <option value="{{ $office->id }}" {{ (isset($receipt) && $receipt->office_id == $office->id) ? 'selected' : '' }}>{{ $office->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid-4" style="margin-top:4px;">
                            <div class="form-group-gf">
                                <label class="form-label-gf">Customer</label>
                                <div class="form-input-container">
                                    <x-inline-select name="customer_id" :options="$customers" module="trade-partner" x-model="form.customer_id" class="form-control-gf" x-show="form.cargo_type === 'OTH'" />
                                    <x-inline-select name="customer_id" :options="$customers" module="trade-partner" x-model="form.customer_id" class="form-control-gf" x-show="form.cargo_type !== 'OTH'" />
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Warehouse</label>
                                <div class="form-input-container">
                                    <x-inline-select name="warehouse_id" :options="$warehouses" module="trade-partner" x-model="form.warehouse_id" class="form-control-gf" />
                                </div>
                            </div>
                            <div class="form-group-gf" style="display:flex;align-items:center;gap:10px;">
                                <label style="font-size:10px;font-weight:700;display:flex;align-items:center;gap:3px;cursor:pointer;">
                                    <input type="hidden" name="is_hazardous" value="0">
                                    <input type="checkbox" name="is_hazardous" value="1" x-model="form.is_hazardous"> HAZMAT
                                </label>
                                <label style="font-size:10px;font-weight:700;display:flex;align-items:center;gap:3px;cursor:pointer;">
                                    <input type="hidden" name="is_heat_treated" value="0">
                                    <input type="checkbox" name="is_heat_treated" value="1" x-model="form.is_heat_treated"> HEAT TREATED
                                </label>
                            </div>
                        </div>

                        <div class="form-grid-3" style="margin-top:4px;">
                            <div class="form-group-gf" style="align-items:flex-start;">
                                <label class="form-label-gf" style="margin-top:3px;">Commodity</label>
                                <div class="form-input-container">
                                    <textarea name="commodity" class="form-control-gf" style="height:40px;resize:vertical;padding:3px 4px;" x-model="form.commodity" placeholder="Describe the commodity..."></textarea>
                                </div>
                            </div>
                            <div class="form-group-gf" style="align-items:flex-start;">
                                <label class="form-label-gf" style="margin-top:3px;">P.O. No.</label>
                                <div class="form-input-container">
                                    <textarea name="po_no" class="form-control-gf" style="height:40px;resize:vertical;padding:3px 4px;" x-model="form.po_no" placeholder="Purchase order numbers..."></textarea>
                                </div>
                            </div>
                            <div class="form-group-gf" style="align-items:flex-start;">
                                <label class="form-label-gf" style="margin-top:3px;">Internal Remark</label>
                                <div class="form-input-container">
                                    <textarea name="internal_remark" class="form-control-gf" style="height:40px;resize:vertical;padding:3px 4px;" x-model="form.internal_remark"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <hr style="border:0;border-top:1px solid #e2e8f0;margin:8px 0;">
                        <div style="display:flex;gap:4px;margin-bottom:6px;align-items:center;flex-wrap:wrap;">
                            <button type="button" class="btn-tool-icon green" @click="addItem()" title="Add item"><i class="fa fa-plus"></i></button>
                            <button type="button" class="btn-tool-icon" @click="copySelectedItems()" :disabled="selectedItems.length === 0" title="Copy selected"><i class="fa fa-copy"></i></button>
                            <button type="button" class="btn-tool-icon" @click="deleteSelectedItems()" :disabled="selectedItems.length === 0" title="Delete selected" style="color:#ef4444;"><i class="fa fa-trash"></i></button>
                            <span style="font-size:9px;color:#64748b;margin-left:auto;">Items: <strong x-text="items.length"></strong></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:24px;text-align:center;"><input type="checkbox" @change="toggleAllItems($event.target.checked)"></th>
                                        <th style="width:18px;text-align:center;">#</th>
                                        <th style="width:65px;">Date</th>
                                        <th style="width:40px;">Length</th>
                                        <th style="width:40px;">Width</th>
                                        <th style="width:40px;">Height</th>
                                        <th style="width:50px;">Dimension</th>
                                        <th style="width:40px;">PKG</th>
                                        <th style="width:45px;">Unit</th>
                                        <th>SKU/P.O.</th>
                                        <th style="width:35px;">Pallet</th>
                                        <th style="width:40px;text-align:right;">PCS</th>
                                        <th style="width:45px;text-align:right;">KG</th>
                                        <th style="width:45px;text-align:right;">LBS</th>
                                        <th style="width:50px;text-align:right;">CBM</th>
                                        <th style="width:45px;text-align:right;">CFT</th>
                                        <th style="width:45px;text-align:right;">Act KG</th>
                                        <th style="width:45px;text-align:right;">Act LBS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, idx) in items" :key="idx">
                                    <tr>
                                        <td style="text-align:center;"><input type="checkbox" :value="idx" x-model="selectedItems"></td>
                                        <td style="text-align:center;font-weight:600;color:#94a3b8;" x-text="idx + 1"></td>
                                        <td><input type="date" class="form-control-gf" style="height:18px;width:80px;" x-model="item.item_date"></td>
                                        <td><input type="number" step="0.01" class="form-control-gf" style="height:18px;" x-model="item.length" @input="calcVolume(item)"></td>
                                        <td><input type="number" step="0.01" class="form-control-gf" style="height:18px;" x-model="item.width" @input="calcVolume(item)"></td>
                                        <td><input type="number" step="0.01" class="form-control-gf" style="height:18px;" x-model="item.height" @input="calcVolume(item)"></td>
                                        <td style="text-align:center;font-size:9px;" x-text="item.dimension || '-'"></td>
                                        <td><input type="number" step="1" class="form-control-gf input-number" style="height:18px;" x-model="item.pkg_qty"></td>
                                        <td>
                                            <select class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.unit">
                                                <option value="">Sel</option>
                                                @foreach($packageUnits as $unit)
                                                    <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control-gf" style="height:18px;" x-model="item.sku_po" placeholder="SKU"></td>
                                        <td><input type="number" step="1" class="form-control-gf input-number" style="height:18px;" x-model="item.pallet_qty"></td>
                                        <td style="text-align:right;font-weight:600;" x-text="item.pkg_qty || 0"></td>
                                        <td style="text-align:right;" x-text="(item.weight_kg || 0).toFixed(2)"></td>
                                        <td style="text-align:right;" x-text="(item.weight_lbs || 0).toFixed(2)"></td>
                                        <td style="text-align:right;" x-text="(item.volume_cbm || 0).toFixed(3)"></td>
                                        <td style="text-align:right;" x-text="(item.volume_cft || 0).toFixed(3)"></td>
                                        <td style="text-align:right;" x-text="(item.act_weight_kg || 0).toFixed(2)"></td>
                                        <td style="text-align:right;" x-text="(item.act_weight_lbs || 0).toFixed(2)"></td>
                                    </tr>
                                    </template>
                                    <tr class="total-row" style="background:#f8fafc;font-weight:700;" x-show="items.length > 0">
                                        <td colspan="7" style="text-align:right;color:#3b82f6;padding-right:10px;">Total</td>
                                        <td style="text-align:center;" x-text="totals.pkg"></td>
                                        <td></td>
                                        <td></td>
                                        <td style="text-align:center;" x-text="totals.pallet"></td>
                                        <td style="text-align:center;" x-text="totals.pcs"></td>
                                        <td style="text-align:right;" x-text="totals.weight_kg.toFixed(2)"></td>
                                        <td style="text-align:right;" x-text="totals.weight_lbs.toFixed(2)"></td>
                                        <td style="text-align:right;" x-text="totals.volume_cbm.toFixed(3)"></td>
                                        <td style="text-align:right;" x-text="totals.volume_cft.toFixed(3)"></td>
                                        <td style="text-align:right;" x-text="totals.act_weight_kg.toFixed(2)"></td>
                                        <td style="text-align:right;" x-text="totals.act_weight_lbs.toFixed(2)"></td>
                                    </tr>
                                    <template x-if="items.length === 0">
                                    <tr>
                                        <td colspan="18" style="text-align:center;padding:20px;color:#94a3b8;">
                                            <i class="fa fa-cube" style="font-size:18px;display:block;margin-bottom:4px;"></i>
                                            No items added. Click <strong style="color:#3b82f6;cursor:pointer;" @click="addItem()">+</strong> to add.
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Memo Section -->
                        <hr style="border:0;border-top:1px solid #e2e8f0;margin:8px 0;">
                        <div style="display:flex;gap:4px;margin-bottom:6px;align-items:center;">
                            <button type="button" class="btn-tool-icon green" @click="openMemoModal()" title="Add memo"><i class="fa fa-plus"></i></button>
                            <span style="font-size:9px;color:#64748b;">Memos: <strong x-text="memos.length"></strong></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:24px;"></th>
                                        <th style="text-align:left;">Subject</th>
                                        <th style="width:90px;">Last Modified</th>
                                        <th style="width:80px;text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="memos.length === 0">
                                    <tr><td colspan="4" style="text-align:center;padding:15px;color:#94a3b8;">No memos yet.</td></tr>
                                    </template>
                                    <template x-for="(memo, idx) in memos" :key="idx">
                                    <tr @click="selectedMemo = idx" :style="selectedMemo === idx ? 'background:#eff6ff;' : ''" style="cursor:pointer;">
                                        <td style="text-align:center;"><i class="fa fa-bell" style="color:#22c55e;font-size:9px;"></i></td>
                                        <td x-text="memo.subject"></td>
                                        <td x-text="memo.updated_at || memo.created_at || '-'"></td>
                                        <td style="text-align:center;">
                                            <button type="button" @click.stop="editMemo(idx)" class="btn-tool-icon" style="width:16px;height:16px;font-size:8px;"><i class="fa fa-pencil"></i></button>
                                            <button type="button" @click.stop="deleteMemo(idx)" class="btn-tool-icon" style="width:16px;height:16px;font-size:8px;color:#ef4444;"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════ DOC CENTER TAB ═══════════════════════ -->
            <div x-show="activeTab === 'doc'" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-folder-open"></i> Document Center</span>
                        <div style="display:flex;gap:4px;">
                            <label class="btn-freightx" style="padding:2px 8px;font-size:9px;cursor:pointer;">
                                <i class="fa fa-upload"></i> Upload
                                <input type="file" x-ref="docInput" style="display:none;" @change="uploadDocument($event)">
                            </label>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div style="display:flex;gap:4px;margin-bottom:8px;align-items:center;flex-wrap:wrap;background:#f8fafc;padding:4px 8px;border:1px solid #e2e8f0;border-radius:3px;">
                            <button type="button" class="btn-tool-icon" @click="deleteSelectedDocs()" :disabled="selectedDocIds.length === 0" title="Delete selected"><i class="fa fa-trash" style="color:#ef4444;"></i></button>
                            <span style="font-size:10px;color:#64748b;">Documents: <strong x-text="documents.length"></strong></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:24px;text-align:center;"><input type="checkbox" @change="toggleAllDocs($event.target.checked)"></th>
                                        <th>File Name</th>
                                        <th style="width:70px;">Size</th>
                                        <th style="width:60px;">Type</th>
                                        <th style="width:80px;">Uploaded</th>
                                        <th style="width:70px;">Uploader</th>
                                        <th style="width:70px;text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(doc, idx) in documents" :key="doc.id || idx">
                                    <tr>
                                        <td style="text-align:center;"><input type="checkbox" :value="idx" x-model="selectedDocIds"></td>
                                        <td><i class="fa fa-file-text-o" style="color:#64748b;margin-right:4px;"></i><span x-text="doc.file_name"></span></td>
                                        <td x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '-'"></td>
                                        <td x-text="doc.file_extension || '-'"></td>
                                        <td x-text="doc.created_at || '-'"></td>
                                        <td x-text="doc.uploader_name || 'N/A'"></td>
                                        <td style="text-align:center;">
                                            <a :href="'/warehouse/receipt/documents/' + doc.id + '/download'" class="btn-tool-icon" style="width:18px;height:18px;font-size:9px;text-decoration:none;" title="Download"><i class="fa fa-download"></i></a>
                                            <button type="button" class="btn-tool-icon" style="width:18px;height:18px;font-size:9px;color:#ef4444;" @click="deleteDocument(doc.id, idx)" title="Delete"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    </template>
                                    <template x-if="documents.length === 0">
                                    <tr>
                                        <td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">
                                            <i class="fa fa-folder-open-o" style="font-size:24px;display:block;margin-bottom:6px;"></i>
                                            No documents uploaded. Click <strong style="color:#3b82f6;cursor:pointer;" @click="$refs.docInput.click()">Upload</strong> to add files.
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top:10px;padding:15px;border:1px dashed #cbd5e1;border-radius:4px;text-align:center;background:#f8fafc;">
                            <i class="fa fa-cloud-upload" style="font-size:18px;color:#3b82f6;display:block;margin-bottom:4px;"></i>
                            <span style="font-size:10px;color:#64748b;">Drag &amp; drop files here or <strong style="color:#3b82f6;cursor:pointer;" @click="$refs.docInput.click()">browse</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <button type="submit" class="btn-tool green" @click="saveAction='save_close'; if(!validateForm()) $event.preventDefault();"><i class="fa fa-save"></i> @if(isset($receipt)) UPDATE @else SAVE @endif</button>
                @if(!isset($receipt))
                <button type="submit" class="btn-tool" @click="saveAction='save_new'; if(!validateForm()) $event.preventDefault();"><i class="fa fa-plus-circle"></i> SAVE &amp; NEW</button>
                @endif
                <a href="{{ route('warehouse.receipts.index') }}" class="btn-tool" target="_blank"><i class="fa fa-arrow-left"></i> CANCEL</a>
            </div>
        </form>

        <!-- Memo Modal -->
        <div x-show="memoModalOpen" x-cloak class="modal-overlay" @click.away="memoModalOpen = false">
            <div class="modal-container" @click.stop style="max-width:500px;">
                <div class="modal-header">
                    <span><i class="fa fa-sticky-note-o" style="color:#3b82f6;margin-right:6px;"></i> <span x-text="memoEditIndex === -1 ? 'Add Memo' : 'Edit Memo'"></span></span>
                    <button type="button" @click="memoModalOpen = false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Subject <span style="color:#ef4444;">*</span></label>
                        <input type="text" x-model="memoForm.subject" placeholder="Memo subject..." class="form-control-gf" style="height:32px;font-size:11px;">
                    </div>
                    <div style="margin-bottom:6px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Content</label>
                        <textarea x-model="memoForm.content" placeholder="Memo details..." class="form-control-gf" style="height:80px;font-size:11px;resize:vertical;padding:4px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding:10px 15px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:right;">
                    <button type="button" class="btn-default-gf" @click="memoModalOpen = false" style="margin-right:6px;">Cancel</button>
                    <button type="button" class="btn-tool green" @click="saveMemo()"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </div>

        <!-- Item Preview Modal -->
        <div x-show="itemPreviewOpen" x-cloak class="modal-overlay" @click.away="itemPreviewOpen = false">
            <div class="modal-container" @click.stop style="max-width:600px;">
                <div class="modal-header">
                    <span><i class="fa fa-cube" style="color:#3b82f6;margin-right:6px;"></i> Cargo Item Summary</span>
                    <button type="button" @click="itemPreviewOpen = false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div><strong style="font-size:10px;color:#475569;">Total PKG:</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.pkg"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Total Pallets:</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.pallet"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Total PCS:</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.pcs"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Weight (KG):</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.weight_kg.toFixed(2)"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Weight (LBS):</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.weight_lbs.toFixed(2)"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Volume (CBM):</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.volume_cbm.toFixed(3)"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Volume (CFT):</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.volume_cft.toFixed(3)"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Act. Weight (KG):</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.act_weight_kg.toFixed(2)"></span></div>
                        <div><strong style="font-size:10px;color:#475569;">Act. Weight (LBS):</strong> <span style="font-size:12px;font-weight:600;" x-text="totals.act_weight_lbs.toFixed(2)"></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-default-gf" @click="itemPreviewOpen = false">Close</button>
                </div>
            </div>
        </div>

        <style>
            .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(2px); animation: fadeIn 0.15s ease; }
            .modal-container { background: #ffffff; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); overflow: hidden; border-top: 3px solid #3b82f6; animation: slideUp 0.15s ease; }
            .modal-header { padding: 10px 15px; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #0f172a; }
            .modal-body { padding: 15px; max-height: 75vh; overflow-y: auto; }
            .modal-footer { padding: 10px 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right; }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes slideUp { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        </style>
    </div>

    @push('scripts')
    <script>
        function warehouseReceiptCreateApp() {
            return {
                activeTab: 'basic',

                form: {
                    receipt_no: '{{ $nextNo ?? old("receipt_no", $receipt->receipt_no ?? "") }}',
                    receipt_date: '{{ old("receipt_date", isset($receipt) && $receipt->receipt_date ? \Carbon\Carbon::parse($receipt->receipt_date)->format("Y-m-d\TH:i") : date("Y-m-d\TH:i")) }}',
                    operator_id: '{{ old("operator_id", $receipt->operator_id ?? "") }}',
                    warehouse_id: '{{ old("warehouse_id", $receipt->warehouse_id ?? "") }}',
                    customer_id: '{{ old("customer_id", $receipt->customer_id ?? "") }}',
                    shipper_id: '{{ old("shipper_id", $receipt->shipper_id ?? "") }}',
                    consignee_id: '{{ old("consignee_id", $receipt->consignee_id ?? "") }}',
                    office_id: '{{ old("office_id", $receipt->office_id ?? "") }}',
                    tracking_no: '{{ old("tracking_no", $receipt->tracking_no ?? "") }}',
                    carrier_name: '{{ old("carrier_name", $receipt->carrier_name ?? "") }}',
                    cargo_type: '{{ old("cargo_type", $receipt->cargo_type ?? "OTH") }}',
                    is_hazardous: {{ old('is_hazardous', $receipt->is_hazardous ?? false) ? 'true' : 'false' }},
                    is_heat_treated: {{ old('is_heat_treated', $receipt->is_heat_treated ?? false) ? 'true' : 'false' }},
                    commodity: '{{ old("commodity", $receipt->commodity ?? "") }}',
                    po_no: '{{ old("po_no", $receipt->po_no ?? "") }}',
                    location_code: '{{ old("location_code", $receipt->location_code ?? "") }}',
                    delivered_by: '{{ old("delivered_by", $receipt->delivered_by ?? "") }}',
                    freight_charge_type: '{{ old("freight_charge_type", $receipt->freight_charge_type ?? "Prepaid") }}',
                    freight_amount: '{{ old("freight_amount", $receipt->freight_amount ?? "0") }}',
                    check_no: '{{ old("check_no", $receipt->check_no ?? "") }}',
                    internal_remark: '{{ old("internal_remark", $receipt->internal_remark ?? "") }}',
                },

                autoGen: true,
                saveAction: 'save_close',
                loadedDateTime: '{{ date("Y-m-d H:i") }}',
                makerName: '{{ auth()->user()->name ?? "System" }}',

                // Items
                items: [],
                selectedItems: [],

                // Documents
                documents: @json($docData ?? []),
                selectedDocIds: [],

                // Charges (kept for JSON serialization)
                charges: [],

                // Memos
                memos: @json($memoData ?? []),
                selectedMemo: null,
                memoModalOpen: false,
                memoEditIndex: -1,
                memoForm: { subject: '', content: '' },

                // Preview
                itemPreviewOpen: false,

                get totals() {
                    let t = { pkg: 0, pallet: 0, pcs: 0, weight_kg: 0, weight_lbs: 0, volume_cbm: 0, volume_cft: 0, act_weight_kg: 0, act_weight_lbs: 0 };
                    this.items.forEach(item => {
                        t.pkg += parseFloat(item.pkg_qty) || 0;
                        t.pallet += parseFloat(item.pallet_qty) || 0;
                        t.pcs += parseFloat(item.pkg_qty) || 0;
                        t.weight_kg += parseFloat(item.weight_kg) || 0;
                        t.weight_lbs += parseFloat(item.weight_lbs) || 0;
                        t.volume_cbm += parseFloat(item.volume_cbm) || 0;
                        t.volume_cft += parseFloat(item.volume_cft) || 0;
                        t.act_weight_kg += parseFloat(item.act_weight_kg) || 0;
                        t.act_weight_lbs += parseFloat(item.act_weight_lbs) || 0;
                    });
                    return t;
                },

                init() {
                    @if(isset($receipt) && $receipt->items && $receipt->items->count() > 0)
                        const rawItems = @json($receipt->items);
                        this.items = rawItems.map(i => ({
                            length: i.length_cm || '',
                            width: i.width_cm || '',
                            height: i.height_cm || '',
                            dimension: i.dimension || '',
                            pkg_qty: i.pkg_qty || '',
                            unit: i.unit || '',
                            sku_po: i.sku_po || '',
                            pallet_qty: i.pallet_qty || '',
                            weight_kg: parseFloat(i.weight_kg) || 0,
                            weight_lbs: parseFloat(i.weight_lbs) || 0,
                            volume_cbm: parseFloat(i.volume_cbm) || 0,
                            volume_cft: parseFloat(i.volume_cft) || 0,
                            act_weight_kg: parseFloat(i.act_weight_kg) || 0,
                            act_weight_lbs: parseFloat(i.act_weight_lbs) || 0,
                            item_date: i.item_date || '',
                        }));
                    @endif
                    @if(isset($receipt) && $receipt->charges_data)
                        this.charges = @json($receipt->charges_data);
                    @endif
                    @if(isset($receipt) && $receipt->memos_data)
                        this.memos = @json($receipt->memos_data);
                    @endif
                },

                async generateReceiptNo() {
                    try {
                        const res = await fetch('{{ route("warehouse.receipts.generate-no") }}');
                        const data = await res.json();
                        this.form.receipt_no = data.receipt_no;
                    } catch(e) {
                        this.form.receipt_no = 'WR-' + Date.now().toString().slice(-6);
                    }
                },

                // Items
                addItem() {
                    this.items.push({ length: '', width: '', height: '', dimension: '', pkg_qty: '', unit: '', sku_po: '', pallet_qty: '', weight_kg: 0, weight_lbs: 0, volume_cbm: 0, volume_cft: 0, act_weight_kg: 0, act_weight_lbs: 0, item_date: '' });
                },

                calcVolume(item) {
                    const l = parseFloat(item.length) || 0;
                    const w = parseFloat(item.width) || 0;
                    const h = parseFloat(item.height) || 0;
                    item.dimension = l && w && h ? `${l}x${w}x${h}` : (l && w ? `${l}x${w}` : (l ? `${l}` : ''));
                    if (l && w && h) {
                        item.volume_cbm = (l * w * h) / 1000000;
                        item.volume_cft = item.volume_cbm * 35.315;
                    } else {
                        item.volume_cbm = 0;
                        item.volume_cft = 0;
                    }
                    item.weight_kg = item.volume_cbm * 250;
                    item.weight_lbs = item.weight_kg * 2.20462;
                },

                toggleAllItems(checked) {
                    this.selectedItems = checked ? this.items.map((_, i) => i) : [];
                },

                copySelectedItems() {
                    const toCopy = this.selectedItems.map(i => ({...this.items[i]}));
                    toCopy.forEach(item => this.items.push({...item}));
                    this.selectedItems = [];
                },

                deleteSelectedItems() {
                    if (!this.selectedItems.length) return;
                    if (!confirm('Delete ' + this.selectedItems.length + ' selected item(s)?')) return;
                    this.selectedItems.sort((a,b) => b - a).forEach(i => this.items.splice(i, 1));
                    this.selectedItems = [];
                },

                // Documents
                toggleAllDocs(checked) {
                    this.selectedDocIds = checked ? this.documents.map((_, i) => i) : [];
                },

                uploadDocument(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    @if(isset($receipt))
                    const formData = new FormData();
                    formData.append('file', file);
                    fetch('{{ route("warehouse.receipts.documents.store", $receipt->id) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.documents.push(data.document);
                            if (window.showToast) showToast('success', 'Document uploaded.');
                        }
                    })
                    .catch(() => {
                        if (window.showToast) showToast('error', 'Upload failed.');
                    });
                    @else
                    if (window.showToast) showToast('info', 'Please save first, then upload documents.');
                    @endif
                    e.target.value = '';
                },

                deleteDocument(id, idx) {
                    if (!id) { this.documents.splice(idx, 1); return; }
                    if (!confirm('Delete this document?')) return;
                    fetch('/warehouse/receipt/documents/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { this.documents.splice(idx, 1); if (window.showToast) showToast('success', 'Document deleted.'); }
                    })
                    .catch(() => { if (window.showToast) showToast('error', 'Delete failed.'); });
                },

                deleteSelectedDocs() {
                    if (!this.selectedDocIds.length) return;
                    if (!confirm('Delete ' + this.selectedDocIds.length + ' selected document(s)?')) return;
                    const ids = [...this.selectedDocIds].sort((a, b) => b - a);
                    ids.forEach(i => {
                        const doc = this.documents[i];
                        if (doc && doc.id) { fetch('/warehouse/receipt/documents/' + doc.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }); }
                        this.documents.splice(i, 1);
                    });
                    this.selectedDocIds = [];
                    if (window.showToast) showToast('success', 'Documents deleted.');
                },

                // Memos
                openMemoModal() {
                    this.memoEditIndex = -1;
                    this.memoForm = { subject: '', content: '' };
                    this.memoModalOpen = true;
                },

                editMemo(idx) {
                    this.memoEditIndex = idx;
                    this.memoForm = { ...this.memos[idx] };
                    this.memoModalOpen = true;
                },

                saveMemo() {
                    if (!this.memoForm.subject.trim()) return;
                    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    if (this.memoEditIndex === -1) {
                        this.memoForm.created_at = now;
                        this.memoForm.updated_at = now;
                        this.memos.push({ ...this.memoForm });
                    } else {
                        this.memoForm.updated_at = now;
                        this.memos[this.memoEditIndex] = { ...this.memoForm };
                    }
                    this.memoModalOpen = false;
                    this.selectedMemo = null;
                },

                deleteMemo(idx) {
                    if (!confirm('Delete this memo?')) return;
                    this.memos.splice(idx, 1);
                },

                // Validation
                validateForm() {
                    let errors = [];
                    if (!this.form.receipt_no || this.form.receipt_no.trim() === '') errors.push('Receipt No. is required');
                    if (!this.form.receipt_date) errors.push('Received Date/Time is required');
                    if (errors.length > 0) {
                        alert('Please fix the following errors:\n\n' + errors.join('\n'));
                        return false;
                    }
                    return true;
                },
            }
        }
    </script>
    @endpush
</x-layout>
