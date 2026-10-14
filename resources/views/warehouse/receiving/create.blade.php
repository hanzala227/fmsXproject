<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        [x-cloak] { display: none !important; }
        .form-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px 12px; }
        .form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px 12px; }
        @media (max-width: 1400px) { .form-grid-4 { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 1100px) { .form-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .form-grid-4, .form-grid-3 { grid-template-columns: 1fr; } }

        .btn-tool { background: #fff; border: 1px solid #cbd5e1; padding: 2px 8px; font-size: 10px; color: #334155; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px; height: 22px; border-radius: 2px; transition: all 0.15s; white-space: nowrap; box-sizing: border-box; }
        .btn-tool:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool.green { background: #3b82f6 !important; color: #fff !important; border-color: #2563eb !important; font-weight: 600; }
        .btn-tool.green:hover { background: #2563eb !important; }

        .btn-tool-icon { background: #fff; border: 1px solid #cbd5e1; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; color: #475569; border-radius: 2px; transition: all 0.2s; }
        .btn-tool-icon:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-tool-icon-blue { background: #3b82f6; color: #fff; border-color: #3b82f6; }
        .btn-tool-icon-blue:hover { background: #2563eb; }

        .action-bar { text-align: center; padding: 15px 0; margin-top: 15px; display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }

        .total-row { background: #f8fafc !important; font-weight: 700; font-size: 11px; color: #0f172a; }
        .total-label-cell { text-align: right; padding-right: 10px !important; color: #3b82f6; }

        .timeline { list-style: none; padding: 10px 0; margin: 0; position: relative; }
        .timeline:before { content: ''; position: absolute; top: 0; bottom: 0; width: 2px; background: #e2e8f0; left: 120px; }
        .timeline-log { position: relative; margin-bottom: 20px; display: flex; }
        .timeline-time { width: 110px; text-align: right; padding-right: 20px; font-size: 10px; color: #64748b; }
        .timeline-icon { width: 22px; height: 22px; background: #3b82f6; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; position: absolute; left: 110px; z-index: 5; }
        .timeline-body { flex: 1; padding-left: 28px; }
        .timeline-body h2 { font-size: 11px; font-weight: 700; margin: 0 0 3px; color: #1e293b; }
        .timeline-content { font-size: 10px; color: #64748b; background: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; border-radius: 2px; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); display: flex; align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(2px); }
        .modal-container { background: #fff; border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden; border-top: 3px solid #3b82f6; max-width: 500px; width: 90%; }
        .modal-header { padding: 10px 15px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #0f172a; }
        .modal-body { padding: 15px; max-height: 75vh; overflow-y: auto; }
        .modal-footer { padding: 10px 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right; }
        .nav-tabs-custom { margin-bottom: 8px; border-bottom: 2px solid #e2e8f0; display: flex; gap: 4px; background: #fff; padding: 4px 8px 0 8px; border-radius: 2px 2px 0 0; }
        .nav-tabs-custom .tab-item { padding: 6px 14px; font-size: 11px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; }
        .nav-tabs-custom .tab-item:hover { color: #2563eb; background: #f8fafc; }
        .nav-tabs-custom .tab-item.active { color: #2563eb; font-weight: 700; border-bottom-color: #2563eb; background: #eff6ff; }
    </style>
    @endpush

    <div class="page-content" x-data="receivingCreateApp()">
        {{-- TOP TABS --}}
        <div class="nav-tabs-custom">
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

        <form id="receivingForm" action="{{ isset($receiving) ? route('receiving.update', $receiving->id) : route('receiving.store') }}" method="POST">
            @csrf
            @if(isset($receiving)) @method('PUT') @endif
            <input type="hidden" name="save_action" :value="saveAction">

            @if(session('success'))
                <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:12px;">
                    <strong><i class="fa fa-exclamation-circle"></i> Validation Error</strong>
                    <ul style="margin:5px 0 0 15px;padding:0;">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <!-- Breadcrumb -->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('receiving.index') }}">Warehouse Receiving</a> <i class="fa fa-angle-right"></i></li>
                    <li><span style="color:#333;font-weight:700;">{{ isset($receiving) ? 'Edit: ' . ($receiving->receipt->receipt_no ?? 'RV-' . $receiving->id) : 'New Receiving' }}</span></li>
                </ul>
            </div>

            <!-- Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <h1 class="caption-subject" style="font-size:18px;margin:0;">
                    <i class="fa fa-truck"></i> Warehouse Receiving — {{ isset($receiving) ? 'Edit' : 'New Entry' }}
                </h1>
                <div style="display:flex;gap:4px;">
                    <button type="button" class="btn-freightx" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> @if(isset($receiving)) UPDATE @else SAVE @endif</button>
                    <a href="{{ route('receiving.index') }}" class="btn-default-gf">BACK TO LIST</a>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="gf-tabs">
                <li :class="activeTab === 'basic' ? 'active' : ''"><a @click="activeTab = 'basic'">Basic</a></li>
                <li :class="(activeTab === 'accounting' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'accounting' : null">
                    <a @click="switchTab('accounting')">Accounting</a>
                </li>
                <li :class="(activeTab === 'status' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'status' : null">
                    <a @click="switchTab('status')">Status</a>
                </li>
                <li style="display: none !important;" :class="(activeTab === 'doc' ? 'active' : '') + (isSaved ? '' : ' disabled-tab')" @click="isSaved ? activeTab = 'doc' : null">
                    <a @click="switchTab('doc')">Doc Center</a>
                </li>
            </ul>

            <!-- ==================== BASIC TAB ==================== -->
            <div x-show="activeTab === 'basic'" class="main-grid">
                <div class="portlet light">
                    <div class="portlet-title" style="cursor:pointer;">
                        <span class="caption-subject"><i class="fa fa-file-text-o"></i> Receiving Information</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-4">
                            <!-- Column 1 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Receipt</label>
                                    <div class="form-input-container">
                                        <select name="warehouse_receipt_id" class="form-control-gf" x-model="form.warehouse_receipt_id" required>
                                            <option value="">Select Receipt...</option>
                                            @foreach($receipts as $r)
                                                <option value="{{ $r->id }}" {{ (isset($receiving) && $receiving->warehouse_receipt_id == $r->id) ? 'selected' : '' }}>{{ $r->receipt_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Post Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="post_date" class="form-control-gf" x-model="form.post_date">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Office</label>
                                    <div class="form-input-container">
                                        <select name="office_id" class="form-control-gf" x-model="form.office_id">
                                            <option value="">Select...</option>
                                            @foreach($offices as $o)
                                                <option value="{{ $o->id }}" {{ (isset($receiving) && $receiving->office_id == $o->id) ? 'selected' : '' }}>{{ $o->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Quotation No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="quotation_no" class="form-control-gf" x-model="form.quotation_no" placeholder="e.g. Q-2026-001">
                                    </div>
                                </div>
                            </div>
                            <!-- Column 2 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> Customer</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="customer_id" :options="$tradePartners" module="trade-partner" x-model="form.customer_id" class="form-control-gf" />
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Bill To</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="bill_to_id" :options="$tradePartners" module="trade-partner" x-model="form.bill_to_id" class="form-control-gf" />
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Ship From</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="ship_from_id" :options="$tradePartners" module="trade-partner" x-model="form.ship_from_id" class="form-control-gf" />
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">B/L No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="bl_no" class="form-control-gf" x-model="form.bl_no" placeholder="e.g. MEDU1234567">
                                    </div>
                                </div>
                            </div>
                            <!-- Column 3 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Trucker</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="trucker_id" :options="$tradePartners" module="trade-partner" x-model="form.trucker_id" class="form-control-gf" />
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Container No.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="container_no" class="form-control-gf" x-model="form.container_no" placeholder="e.g. MSCU1234567">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Pallet</label>
                                    <div class="form-input-container">
                                        <input type="text" name="pallet" class="form-control-gf" x-model="form.pallet" placeholder="Pallet info">
                                    </div>
                                </div>
                            </div>
                            <!-- Column 4 -->
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf"><span style="color:red;">*</span> In Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="receiving_date" class="form-control-gf" x-model="form.receiving_date" required>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Order Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="order_date" class="form-control-gf" x-model="form.order_date">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Expect Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="expect_date" class="form-control-gf" x-model="form.expect_date">
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Expiration Date</label>
                                    <div class="form-input-container">
                                        <input type="date" name="expiration_date" class="form-control-gf" x-model="form.expiration_date">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Internal Remark -->
                        <div class="form-group-gf" style="margin-top:8px;align-items:flex-start;">
                            <label class="form-label-gf" style="margin-top:3px;">Remark</label>
                            <div class="form-input-container">
                                <textarea name="internal_remark" class="form-control-gf" style="height:40px;resize:vertical;" x-model="form.internal_remark" placeholder="Internal notes..."></textarea>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <hr style="border:0;border-top:1px solid #e2e8f0;margin:8px 0;">
                        <div style="display:flex;gap:4px;margin-bottom:8px;align-items:center;">
                            <button type="button" class="btn-tool-icon" style="background:#22c55e;color:#fff;border-color:#16a34a;" @click="addItem()"><i class="fa fa-plus"></i></button>
                            <button type="button" class="btn-default-gf" style="padding:2px 6px;" @click="deleteSelectedItems()" :disabled="selectedItems.length === 0"><i class="fa fa-trash"></i></button>
                            <span style="font-size:9px;color:#64748b;margin-left:4px;">Items: <strong x-text="items.length"></strong></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:28px;"><input type="checkbox" @change="toggleAllItems($event.target.checked)"></th>
                                        <th style="width:22px;">#</th>
                                        <th><span style="color:#ef4444;">*</span> SKU No.</th>
                                        <th>Customer P.O.</th>
                                        <th>Product Description</th>
                                        <th>Order P.O. No.</th>
                                        <th style="width:60px;text-align:right;">Order Qty</th>
                                        <th style="width:60px;text-align:right;"><span style="color:#ef4444;">*</span> Qty</th>
                                        <th style="width:60px;">Qty Unit</th>
                                        <th style="width:40px;">PCS</th>
                                        <th style="width:40px;">Pack</th>
                                        <th style="width:60px;">Pack Unit</th>
                                        <th style="width:40px;">Pallet</th>
                                        <th style="width:70px;text-align:right;">Weight</th>
                                        <th style="width:80px;text-align:right;">Measurement</th>
                                        <th style="width:100px;">Inventory</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, idx) in items" :key="idx">
                                        <tr>
                                            <td style="text-align:center;"><input type="checkbox" :value="idx" x-model="selectedItems"></td>
                                            <td style="text-align:center;" x-text="idx + 1"></td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.sku_no" placeholder="SKU"></td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.customer_po" placeholder="P.O."></td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.description" placeholder="Description"></td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.order_po_no" placeholder="Order P.O."></td>
                                            <td><input type="number" step="0.01" class="form-control-gf" style="height:18px;font-size:9px;text-align:right;" x-model="item.order_qty"></td>
                                            <td><input type="number" step="0.01" class="form-control-gf" style="height:18px;font-size:9px;text-align:right;" x-model="item.qty"></td>
                                            <td>
                                                <select class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.qty_unit">
                                                    <option value="">Select...</option>
                                                    @foreach($packageUnits as $unit)
                                                        <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="text-align:center;font-weight:600;color:#64748b;" x-text="item.qty || 0"></td>
                                            <td style="text-align:center;font-weight:600;color:#64748b;" x-text="item.pack || 0"></td>
                                            <td>
                                                <select class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.pack_unit">
                                                    <option value="">Select...</option>
                                                    @foreach($packageUnits as $unit)
                                                        <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.pallet" placeholder="Pallet"></td>
                                            <td><input type="number" step="0.01" class="form-control-gf" style="height:18px;font-size:9px;text-align:right;" x-model="item.weight_kg" placeholder="KG"></td>
                                            <td><input type="number" step="0.01" class="form-control-gf" style="height:18px;font-size:9px;text-align:right;" x-model="item.measure_cbm" placeholder="CBM"></td>
                                            <td><input type="text" class="form-control-gf" style="height:18px;font-size:9px;" x-model="item.inventory" placeholder="Loc"></td>
                                        </tr>
                                    </template>
                                    <template x-if="items.length === 0">
                                        <tr><td colspan="16" style="text-align:center;padding:20px;color:#94a3b8;font-style:italic;">No items added. Click <span style="color:#32c5d2;cursor:pointer;" @click="addItem()">+ Add Item</span></td></tr>
                                    </template>
                                    <tr class="total-row" x-show="items.length > 0">
                                        <td colspan="6" style="text-align:right;padding-right:15px;color:#3b82f6;">Total</td>
                                        <td style="text-align:right;" x-text="itemTotals.order_qty"></td>
                                        <td style="text-align:right;" x-text="itemTotals.qty"></td>
                                        <td></td>
                                        <td style="text-align:center;" x-text="itemTotals.qty"></td>
                                        <td style="text-align:center;" x-text="itemTotals.pack"></td>
                                        <td></td>
                                        <td style="text-align:center;" x-text="itemTotals.pallet"></td>
                                        <td style="text-align:right;" x-text="itemTotals.weight"></td>
                                        <td style="text-align:right;" x-text="itemTotals.measure"></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="items_json" :value="JSON.stringify(items)">
                        <input type="hidden" name="memos_json" :value="JSON.stringify(memos)">
                    </div>
                </div>
            </div>

            <!-- ==================== ACCOUNTING TAB ==================== -->
            <div x-show="activeTab === 'accounting'" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-calculator"></i> Invoices & Costs</span>
                        <div style="display:flex;gap:4px;">
                            <button type="button" class="btn-freightx" style="padding:2px 8px;font-size:9px;" @click="createInvoice()"><i class="fa fa-file-text-o"></i> Create Invoice</button>
                            <button type="button" class="btn-default-gf" style="padding:2px 8px;font-size:9px;" @click="createDcNote()"><i class="fa fa-exchange"></i> Create D/C Note</button>
                            <button type="button" class="btn-default-gf" style="padding:2px 8px;font-size:9px;" @click="createCost()"><i class="fa fa-dollar"></i> Create Cost</button>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <label style="font-size:10px;font-weight:600;color:#475569;display:flex;align-items:center;gap:4px;cursor:pointer;">
                                <input type="checkbox" checked disabled> Include Draft Amount
                            </label>
                        </div>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th style="text-align:left;">Reference No.</th>
                                        <th style="text-align:left;">Party</th>
                                        <th style="text-align:right;width:100px;">Revenue</th>
                                        <th style="text-align:right;width:100px;">Cost</th>
                                        <th style="text-align:right;width:100px;">Balance</th>
                                        <th style="text-align:center;width:80px;">Status</th>
                                        <th style="text-align:right;width:90px;">Post Date</th>
                                        <th style="text-align:right;width:90px;">Invoice Date</th>
                                        <th style="text-align:center;width:50px;">Email</th>
                                        <th style="text-align:center;width:80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="11" style="text-align:center;padding:30px;color:#94a3b8;font-style:italic;">
                                            <i class="fa fa-file-text-o" style="font-size:20px;display:block;margin-bottom:6px;"></i>
                                            No invoices or costs yet. Use the buttons above to create.
                                        </td>
                                    </tr>
                                    <tr class="total-row">
                                        <td colspan="3" style="text-align:right;padding-right:15px;color:#3b82f6;">Total</td>
                                        <td style="text-align:right;">0.00</td>
                                        <td style="text-align:right;">0.00</td>
                                        <td style="text-align:right;">0.00</td>
                                        <td colspan="5"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== STATUS TAB ==================== -->
            <div x-show="activeTab === 'status'" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-info-circle"></i> Role & Status</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-3">
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Operator</label>
                                    <div class="form-input-container">
                                        <select name="operator_id" class="form-control-gf" x-model="form.operator_id">
                                            <option value="">Select Operator...</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ (isset($receiving) && $receiving->operator_id == $u->id) ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Status</label>
                                    <div class="form-input-container">
                                        <select name="status" class="form-control-gf" x-model="form.status">
                                            <option value="Pre-Receiving">Pre-Receiving</option>
                                            <option value="Receiving">Receiving</option>
                                            <option value="Received">Received</option>
                                            <option value="In Storage">In Storage</option>
                                            <option value="Released">Released</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div style="grid-column:span 2;">
                                <div class="form-group-gf" style="align-items:flex-start;">
                                    <label class="form-label-gf" style="margin-top:3px;">Internal Message</label>
                                    <div class="form-input-container">
                                        <textarea class="form-control-gf" style="height:50px;resize:vertical;" x-model="form.internal_remark"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History / Change Log -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-history"></i> Change Log</span>
                    </div>
                    <div class="portlet-body">
                        @if(isset($receiving) && $receiving->created_at)
                        <ul class="timeline">
                            <li class="timeline-log">
                                <div class="timeline-time">{{ $receiving->created_at->format('m-d-Y') }}<br><span style="font-size:9px;">{{ $receiving->created_at->format('H:i') }}</span></div>
                                <div class="timeline-icon"><i class="fa fa-plus"></i></div>
                                <div class="timeline-body">
                                    <h2>Receiving Record Created</h2>
                                    <div class="timeline-content">{{ $receiving->operator->name ?? 'System' }}</div>
                                </div>
                            </li>
                            @if($receiving->updated_at && $receiving->updated_at->gt($receiving->created_at))
                            <li class="timeline-log">
                                <div class="timeline-time">{{ $receiving->updated_at->format('m-d-Y') }}<br><span style="font-size:9px;">{{ $receiving->updated_at->format('H:i') }}</span></div>
                                <div class="timeline-icon"><i class="fa fa-pencil"></i></div>
                                <div class="timeline-body">
                                    <h2>Record Updated</h2>
                                    <div class="timeline-content">Last modified by {{ auth()->user()->name ?? 'System' }}</div>
                                </div>
                            </li>
                            @endif
                        </ul>
                        @else
                        <div style="text-align:center;padding:20px;color:#94a3b8;">
                            <i class="fa fa-history" style="font-size:20px;display:block;margin-bottom:6px;"></i>
                            Change log will appear after saving.
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Memo Section (inside Status tab) -->
                <div class="portlet light" style="margin-top:10px;">
                    <div class="portlet-title" style="cursor:pointer;">
                        <span class="caption-subject"><i class="fa fa-sticky-note-o"></i> Memo</span>
                        <div style="display:flex;gap:4px;">
                            <button type="button" class="btn-tool-icon" style="background:#22c55e;color:#fff;border-color:#16a34a;" @click="openMemoModal()"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="portlet-body" style="padding:0;">
                        <div style="display:flex;gap:0;min-height:120px;">
                            <div style="flex:1;border-right:1px solid #e2e8f0;max-width:45%;">
                                <table class="table-custom" style="border:none;margin:0;">
                                    <thead>
                                        <tr>
                                            <th style="width:28px;"></th>
                                            <th style="text-align:left;">Subject</th>
                                            <th style="width:100px;">Date</th>
                                            <th style="width:60px;text-align:center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="memos.length === 0">
                                            <tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;font-style:italic;">No memos yet.</td></tr>
                                        </template>
                                        <template x-for="(memo, idx) in memos" :key="idx">
                                            <tr @click="selectedMemo = idx" :style="selectedMemo === idx ? 'background:#eff6ff;' : ''" style="cursor:pointer;">
                                                <td style="text-align:center;"><i class="fa fa-bell" style="color:#22c55e;font-size:9px;"></i></td>
                                                <td x-text="memo.subject"></td>
                                                <td x-text="memo.date || memo.updated_at || '-'"></td>
                                                <td style="text-align:center;">
                                                    <button type="button" @click.stop="editMemo(idx)" class="btn-tool-icon" style="width:16px;height:16px;font-size:8px;" title="Edit"><i class="fa fa-pencil"></i></button>
                                                    <button type="button" @click.stop="deleteMemo(idx)" class="btn-tool-icon" style="width:16px;height:16px;font-size:8px;color:#ef4444;" title="Delete"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div style="flex:1;display:flex;flex-direction:column;">
                                <div style="padding:4px 8px;border-bottom:1px solid #e2e8f0;background:#fff;min-height:26px;display:flex;align-items:center;">
                                    <span style="font-size:10px;font-weight:600;color:#64748b;">Memo Content</span>
                                </div>
                                <div style="flex:1;padding:6px;display:flex;">
                                    <textarea class="form-control-gf" style="flex:1;min-height:80px;resize:vertical;border:none;border-radius:0;" placeholder="Select a memo to view content..." x-model="memos[selectedMemo]?.content" :disabled="selectedMemo === null"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════ DOC CENTER TAB ═══════════════════════ --}}
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
                        <div class="table-toolbar" style="margin-bottom:8px;display:flex;gap:4px;align-items:center;flex-wrap:wrap;background:#f8fafc;padding:4px 8px;border:1px solid #e2e8f0;border-radius:3px;">
                            <button type="button" class="btn-tool-icon" @click="deleteSelectedDocs()" :disabled="selectedDocIds.length === 0" title="Delete selected"><i class="fa fa-trash" style="color:#ef4444;"></i></button>
                            <span style="font-size:10px;color:#64748b;">Documents: <strong x-text="documents.length"></strong></span>
                        </div>
                        <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:3px;">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:28px;text-align:center;"><input type="checkbox" @change="toggleAllDocs($event.target.checked)"></th>
                                        <th>File Name</th>
                                        <th style="width:70px;">Size</th>
                                        <th style="width:80px;">Type</th>
                                        <th style="width:90px;">Uploaded</th>
                                        <th style="width:70px;">Uploader</th>
                                        <th style="width:80px;text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(doc, idx) in documents" :key="doc.id || idx">
                                        <tr>
                                            <td style="text-align:center;"><input type="checkbox" :value="idx" x-model="selectedDocIds"></td>
                                            <td>
                                                <i class="fa fa-file-text-o" style="color:#64748b;margin-right:4px;"></i>
                                                <span x-text="doc.file_name"></span>
                                            </td>
                                            <td x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '-'"></td>
                                            <td x-text="doc.file_extension || '-'"></td>
                                            <td x-text="doc.created_at || '-'"></td>
                                            <td x-text="doc.uploader_name || 'N/A'"></td>
                                            <td style="text-align:center;">
                                                <a :href="'/warehouse/receiving/documents/' + doc.id + '/download'" class="btn-tool-icon" style="width:18px;height:18px;font-size:9px;text-decoration:none;" title="Download"><i class="fa fa-download"></i></a>
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

            <!-- Footer Actions -->
            <div class="action-bar">
                <button type="button" class="btn-tool green" @click="handleSubmit('save_close')"><i class="fa fa-save"></i> @if(isset($receiving)) UPDATE @else SAVE @endif</button>
                @if(!isset($receiving))
                <button type="button" class="btn-tool" @click="handleSubmit('save_new')"><i class="fa fa-plus-circle"></i> SAVE &amp; NEW</button>
                @endif
                <a href="{{ route('receiving.index') }}" class="btn-tool" target="_blank"><i class="fa fa-arrow-left"></i> CANCEL</a>
            </div>
        </form>

        <!-- Memo Modal -->
        <div x-show="memoModalOpen" x-cloak class="modal-overlay" @click.away="memoModalOpen = false">
            <div class="modal-container" @click.stop>
                <div class="modal-header">
                    <span><i class="fa fa-sticky-note-o" style="color:#3b82f6;margin-right:6px;"></i> <span x-text="memoEditIndex === -1 ? 'Add Memo' : 'Edit Memo'"></span></span>
                    <button type="button" @click="memoModalOpen = false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Subject <span style="color:#ef4444;">*</span></label>
                        <input type="text" x-model="memoForm.subject" placeholder="Memo subject..." class="form-control-gf" style="height:32px;font-size:11px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#334155;margin-bottom:4px;">Content</label>
                        <textarea x-model="memoForm.content" placeholder="Memo details..." class="form-control-gf" style="height:80px;font-size:11px;resize:vertical;padding:4px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-default-gf" @click="memoModalOpen = false" style="margin-right:6px;">Cancel</button>
                    <button type="button" class="btn-tool green" @click="saveMemo()"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function receivingCreateApp() {
            return {
                activeTab: 'basic',
                saveAction: 'save_close',
                isSaved: {{ isset($receiving) ? 'true' : 'false' }},

                form: {
                    warehouse_receipt_id: '{{ old("warehouse_receipt_id", $receiving->warehouse_receipt_id ?? "") }}',
                    office_id: '{{ old("office_id", $receiving->office_id ?? "") }}',
                    customer_id: '{{ old("customer_id", $receiving->customer_id ?? "") }}',
                    bill_to_id: '{{ old("bill_to_id", $receiving->bill_to_id ?? "") }}',
                    ship_from_id: '{{ old("ship_from_id", $receiving->ship_from_id ?? "") }}',
                    quotation_no: '{{ old("quotation_no", $receiving->quotation_no ?? "") }}',
                    bl_no: '{{ old("bl_no", $receiving->bl_no ?? "") }}',
                    trucker_id: '{{ old("trucker_id", $receiving->trucker_id ?? "") }}',
                    container_no: '{{ old("container_no", $receiving->container_no ?? "") }}',
                    receiving_date: '{{ old("receiving_date", isset($receiving) && $receiving->receiving_date ? $receiving->receiving_date->format("Y-m-d") : date("Y-m-d")) }}',
                    post_date: '{{ old("post_date", isset($receiving) && $receiving->post_date ? $receiving->post_date->format("Y-m-d") : "") }}',
                    order_date: '{{ old("order_date", isset($receiving) && $receiving->order_date ? $receiving->order_date->format("Y-m-d") : "") }}',
                    expect_date: '{{ old("expect_date", isset($receiving) && $receiving->expect_date ? $receiving->expect_date->format("Y-m-d") : "") }}',
                    expiration_date: '{{ old("expiration_date", isset($receiving) && $receiving->expiration_date ? $receiving->expiration_date->format("Y-m-d") : "") }}',
                    status: '{{ old("status", $receiving->status ?? "Pre-Receiving") }}',
                    pallet: '{{ old("pallet", $receiving->pallet ?? "") }}',
                    operator_id: '{{ old("operator_id", $receiving->operator_id ?? "") }}',
                    internal_remark: '{!! old("internal_remark", $receiving->internal_remark ?? "") !!}',
                },

                items: [],
                selectedItems: [],

                documents: @json($docData ?? []),
                selectedDocIds: [],
                memos: @json($memoData ?? []),
                selectedMemo: null,

                memoModalOpen: false,
                memoEditIndex: -1,
                memoForm: { subject: '', content: '' },

                init() {
                    @if(isset($itemData) && count($itemData) > 0)
                        this.items = @json($itemData);
                    @endif
                    @if(isset($receiving) && $receiving->memos_data)
                        this.memos = @json($receiving->memos_data);
                    @endif
                    if (!this.isSaved) {
                        this.activeTab = 'basic';
                    }
                },

                validateForm() {
                    if (!this.form.warehouse_receipt_id) {
                        showToast('error', 'Please select a Receipt.');
                        return false;
                    }
                    if (!this.form.receiving_date) {
                        showToast('error', 'In Date (Receiving Date) is required.');
                        return false;
                    }
                    return true;
                },

                switchTab(tab) {
                    if (tab === 'basic') {
                        this.activeTab = 'basic';
                        return;
                    }
                    if (!this.isSaved) {
                        showToast('info', 'Please save the record first before accessing this section.');
                        return;
                    }
                    this.activeTab = tab;
                },

                get itemTotals() {
                    const t = { order_qty: 0, qty: 0, pack: 0, pallet: 0, weight: 0, measure: 0 };
                    this.items.forEach(item => {
                        t.order_qty += parseFloat(item.order_qty) || 0;
                        t.qty += parseFloat(item.qty) || 0;
                        t.pack += parseInt(item.pack) || 0;
                        t.pallet += parseInt(item.pallet) || 0;
                        t.weight += parseFloat(item.weight_kg) || 0;
                        t.measure += parseFloat(item.measure_cbm) || 0;
                    });
                    return {
                        order_qty: t.order_qty.toFixed(2),
                        qty: t.qty.toFixed(2),
                        pack: t.pack,
                        pallet: t.pallet,
                        weight: t.weight.toFixed(2),
                        measure: t.measure.toFixed(2),
                    };
                },

                addItem() {
                    this.items.push({ sku_no: '', customer_po: '', description: '', order_po_no: '', order_qty: 0, qty: 0, qty_unit: '', pack: 0, pack_unit: '', pallet: '', weight_kg: 0, measure_cbm: 0, inventory: '' });
                },

                toggleAllItems(checked) {
                    this.selectedItems = checked ? this.items.map((_, i) => i) : [];
                },

                deleteSelectedItems() {
                    if (!this.selectedItems.length) return;
                    if (!confirm('Delete ' + this.selectedItems.length + ' selected item(s)?')) return;
                    this.selectedItems.sort((a, b) => b - a).forEach(i => this.items.splice(i, 1));
                    this.selectedItems = [];
                },

                toggleAllDocs(checked) {
                    this.selectedDocIds = checked ? this.documents.map((_, i) => i) : [];
                },

                uploadDocument(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    @if(isset($receiving))
                    const formData = new FormData();
                    formData.append('file', file);
                    fetch('{{ route("receiving.documents.store", $receiving->id) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.documents.push(data.document);
                            showToast('success', 'Document uploaded.');
                        }
                    })
                    .catch(() => {
                        showToast('error', 'Upload failed.');
                    });
                    @else
                    showToast('info', 'Please save first, then upload documents.');
                    @endif
                    e.target.value = '';
                },

                deleteDocument(id, idx) {
                    if (!id) { this.documents.splice(idx, 1); return; }
                    if (!confirm('Delete this document?')) return;
                    fetch('/warehouse/receiving/documents/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.documents.splice(idx, 1);
                            showToast('success', 'Document deleted.');
                        }
                    })
                    .catch(() => {
                        showToast('error', 'Delete failed.');
                    });
                },

                deleteSelectedDocs() {
                    if (!this.selectedDocIds.length) return;
                    if (!confirm('Delete ' + this.selectedDocIds.length + ' selected document(s)?')) return;
                    const ids = [...this.selectedDocIds].sort((a, b) => b - a);
                    ids.forEach(i => {
                        const doc = this.documents[i];
                        if (doc && doc.id) {
                            fetch('/warehouse/receiving/documents/' + doc.id, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            });
                        }
                        this.documents.splice(i, 1);
                    });
                    this.selectedDocIds = [];
                    showToast('success', 'Documents deleted.');
                },

                createInvoice() {
                    @if(isset($receiving))
                    window.open('{{ route('accounting.invoices.create') }}?customer_id={{ $receiving->customer_id ?? '' }}&bill_to_id={{ $receiving->bill_to_id ?? '' }}&type=AR&source=receiving', '_blank');
                    @else
                    showToast('info', 'Please save the receiving record first, then create an invoice.');
                    @endif
                },

                createDcNote() {
                    @if(isset($receiving))
                    window.open('{{ route('accounting.invoices.create') }}?customer_id={{ $receiving->customer_id ?? '' }}&bill_to_id={{ $receiving->bill_to_id ?? '' }}&type=AP&note=dc&source=receiving', '_blank');
                    @else
                    showToast('info', 'Please save the receiving record first, then create a D/C Note.');
                    @endif
                },

                createCost() {
                    @if(isset($receiving))
                    window.open('{{ route('accounting.invoices.create') }}?customer_id={{ $receiving->customer_id ?? '' }}&bill_to_id={{ $receiving->bill_to_id ?? '' }}&type=AP&note=cost&source=receiving', '_blank');
                    @else
                    showToast('info', 'Please save the receiving record first, then create a cost entry.');
                    @endif
                },

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
                    const now = new Date().toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
                    if (this.memoEditIndex === -1) {
                        this.memoForm.date = now;
                        this.memoForm.updated_at = now;
                        this.memos.push({ ...this.memoForm });
                        this.selectedMemo = this.memos.length - 1;
                    } else {
                        this.memoForm.updated_at = now;
                        this.memos[this.memoEditIndex] = { ...this.memoForm };
                    }
                    this.memoModalOpen = false;
                },

                deleteMemo(idx) {
                    if (!confirm('Delete this memo?')) return;
                    this.memos.splice(idx, 1);
                    if (this.selectedMemo === idx) this.selectedMemo = null;
                    else if (this.selectedMemo > idx) this.selectedMemo--;
                },

                handleSubmit(saveType) {
                    this.saveAction = saveType;
                    if (!this.validateForm()) return;
                    document.getElementById('receivingForm').submit();
                },
            }
        }

        function showToast(type, msg) {
            const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
            const container = document.getElementById('toast-container') || (() => {
                const c = document.createElement('div');
                c.id = 'toast-container';
                c.style.cssText = 'position:fixed;top:56px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:6px;pointer-events:none;';
                document.body.appendChild(c);
                return c;
            })();
            const t = document.createElement('div');
            t.style.cssText = 'background:#1e293b;color:#fff;padding:8px 14px;border-radius:4px;font-size:11px;box-shadow:0 4px 16px rgba(0,0,0,0.25);display:flex;align-items:center;gap:8px;animation:toastIn 0.25s ease;pointer-events:all;border-left:3px solid ' + (type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6');
            t.innerHTML = '<i class="fa fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle') + '"></i> ' + msg;
            container.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
    </script>
    @endpush
</x-layout>
