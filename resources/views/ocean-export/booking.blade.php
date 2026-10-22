<x-layout title="Ocean Export Booking">
    @push('styles')
    <x-form-styles />
    @endpush

    <div x-data="window.oceanExportBookingModule()" x-cloak>
    <script>
        window.oceanExportBookingModule = function() {
            return {
                activeTab: new URLSearchParams(window.location.search).get('tab') || 'basic',
                workOrders: [],

                async syncWorkOrders() {
                    const bookingId = @json($booking->id ?? null);
                    if (!bookingId) {
                        this.workOrders = [];
                        return;
                    }
                    try {
                        const response = await fetch(`/api/work-orders?workable_type=App%5CModels%5COceanBooking&workable_id=${bookingId}`);
                        if (response.ok) {
                            this.workOrders = await response.json();
                        }
                    } catch (e) {
                        console.error('Failed to sync work orders:', e);
                        this.workOrders = [];
                    }
                },
                clearWorkOrders() {
                    this.showToast('info', 'Use the individual Delete button on each row to remove work orders.');
                },
                async downloadWorkOrder(wo) {
                    this.showToast('info', 'Preparing Excel download...');
                    try {
                        const response = await fetch(`/ocean-export/work-order/${wo.id}/export-excel`);
                        if (!response.ok) throw new Error('Download failed');
                        
                        const blob = await response.blob();
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = `WorkOrder_${wo.work_order_no}.csv`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(downloadUrl);
                        
                        this.showToast('success', 'Excel file downloaded successfully');
                    } catch (e) {
                        console.error('Failed to download Excel:', e);
                        this.showToast('error', 'Failed to export Excel file');
                    }
                },
                async deleteWorkOrder(id) {
                    if (confirm('Are you sure you want to delete this work order?')) {
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                            const response = await fetch(`/ocean-export/work-order/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken || '',
                                    'Accept': 'application/json'
                                }
                            });
                            if (response.ok) {
                                this.syncWorkOrders();
                            }
                        } catch (e) {
                            console.error('Failed to delete work order:', e);
                        }
                    }
                },
                saved: @json(isset($booking) ? true : false),
                containerTypes: @json($containerTypes->map(function($ct) { return ['id' => $ct->id, 'code' => $ct->code]; })),
                showTpSzCard: false,
                tpSzForm: { type_id: '', qty: 1 },
                init() {
                    console.log('Ocean Export Booking Module Started');
                    this.syncWorkOrders();
                    
                    setInterval(() => {
                        this.syncWorkOrders();
                    }, 2000);
                    
                    this.$watch('form.containers', () => {
                        this.form.container_no = this.form.containers.map(c => c.code + ' x ' + c.qty).join(', ');
                    });
                    if (this.form.container_no && this.form.containers.length === 0) {
                        this.form.container_no.split(', ').forEach(part => {
                            const m = part.match(/^(\S+)\s*x\s*(\d+)$/);
                            if (m) {
                                const ct = this.containerTypes.find(t => t.code === m[1]);
                                if (ct) {
                                    this.form.containers.push({ type_id: ct.id, code: ct.code, qty: parseInt(m[2]) });
                                }
                            }
                        });
                    }
                },
                addTpSz() {
                    if (!this.tpSzForm.type_id || !this.tpSzForm.qty) return;
                    const ct = this.containerTypes.find(t => t.id == this.tpSzForm.type_id);
                    if (!ct) return;
                    const existing = this.form.containers.find(c => c.type_id == this.tpSzForm.type_id);
                    if (existing) {
                        existing.qty += parseInt(this.tpSzForm.qty);
                    } else {
                        this.form.containers.push({ type_id: this.tpSzForm.type_id, code: ct.code, qty: parseInt(this.tpSzForm.qty) });
                    }
                    this.tpSzForm = { type_id: '', qty: 1 };
                    this.showTpSzCard = false;
                },
                removeTpSz(idx) {
                    this.form.containers.splice(idx, 1);
                },
                form: {
                    booking_no: @json(old('booking_no', $booking->booking_no ?? 'OBE-' . rand(100000, 999999))),
                    booking_date: @json(old('booking_date', isset($booking) && $booking->booking_date ? $booking->booking_date->format('Y-m-d') : date('Y-m-d'))),
                    quotation_no: @json(old('quotation_no', $booking->quotation_no ?? '')),
                    itn_no: @json(old('itn_no', $booking->itn_no ?? '')),
                    sales_person_id: @json(old('sales_person_id', $booking->sales_person_id ?? '')),
                    op_id: @json(old('op_id', $booking->op_id ?? auth()->id())),
                    ref_no: @json(old('ref_no', $booking->ref_no ?? '')),
                    carrier_bkg_no: @json(old('carrier_bkg_no', $booking->carrier_bkg_no ?? '')),
                    carrier_id: @json(old('carrier_id', $booking->carrier_id ?? '')),
                    ship_mode: @json(old('ship_mode', $booking->ship_mode ?? 'FCL')),
                    svc_term_from_id: @json(old('svc_term_from_id', $booking->svc_term_from_id ?? '')),
                    svc_term_to_id: @json(old('svc_term_to_id', $booking->svc_term_to_id ?? '')),
                    incoterms: @json(old('incoterms', $booking->incoterms ?? 'FOB')),
                    actual_shipper_id: @json(old('actual_shipper_id', $booking->actual_shipper_id ?? '')),
                    customer_id: @json(old('customer_id', $booking->customer_id ?? '')),
                    bill_to_id: @json(old('bill_to_id', $booking->bill_to_id ?? '')),
                    consignee_id: @json(old('consignee_id', $booking->consignee_id ?? '')),
                    notify_id: @json(old('notify_id', $booking->notify_id ?? '')),
                    shipping_agent: @json(old('shipping_agent', $booking->shipping_agent ?? '')),
                    hbl_agent_id: @json(old('hbl_agent_id', $booking->hbl_agent_id ?? '')),
                    forwarding_agent_id: @json(old('forwarding_agent_id', $booking->forwarding_agent_id ?? '')),
                    co_loader_id: @json(old('co_loader_id', $booking->co_loader_id ?? '')),
                    vessel_id: @json(old('vessel_id', $booking->vessel_id ?? '')),
                    voyage: @json(old('voyage', $booking->voyage ?? '')),
                    por_id: @json(old('por_id', $booking->por_id ?? '')),
                    pol_id: @json(old('pol_id', $booking->pol_id ?? '')),
                    etd: @json(old('etd', isset($booking) && $booking->etd ? $booking->etd->format('Y-m-d') : '')),
                    pod_id: @json(old('pod_id', $booking->pod_id ?? '')),
                    eta: @json(old('eta', isset($booking) && $booking->eta ? $booking->eta->format('Y-m-d') : '')),
                    del_id: @json(old('del_id', $booking->del_id ?? '')),
                    fdest_id: @json(old('fdest_id', $booking->fdest_id ?? '')),
                    office_id: @json(old('office_id', $booking->office_id ?? '')),
                    cargo_type: @json(old('cargo_type', $booking->cargo_type ?? 'GENERAL CARGO')),
                    trucker_id: @json(old('trucker_id', $booking->trucker_id ?? '')),
                    container_no: @json(old('container_no', $booking->container_no ?? '')),
                    marks: @json(old('marks', $booking->marks ?? '')),
                    description: @json(old('description', $booking->description ?? '')),
                    remarks: @json(old('remarks', $booking->remarks ?? '')),
                    pkg_qty: @json(old('pkg_qty', $booking->pkg_qty ?? 0)),
                    weight_kg: @json(old('weight_kg', $booking->weight_kg ?? 0)),
                    measure_cbm: @json(old('measure_cbm', $booking->measure_cbm ?? 0)),
                    status: @json(old('status', $booking->status ?? 'OPEN')),
                    bl_cancelled: false,
                    bl_cancelled_date: '',
                    bl_cancelled_reason: '',
                    is_hold: @json(old('is_hold', $booking->is_hold ?? false)),
                    hold_by_id: @json(old('hold_by_id', $booking->hold_by_id ?? '')),
                    is_freight_released: @json(old('is_freight_released', $booking->is_freight_released ?? false)),
                    freight_released_by_id: @json(old('freight_released_by_id', $booking->freight_released_by_id ?? '')),
                    buying_freight: @json(old('buying_freight', $booking->buying_freight ?? '')),
                    selling_freight: @json(old('selling_freight', $booking->selling_freight ?? '')),
                    customs_broker_id: @json(old('customs_broker_id', $booking->customs_broker_id ?? '')),
                    por_etd: @json(old('por_etd', isset($booking) && $booking->por_etd ? $booking->por_etd->format('Y-m-d') : '')),
                    sales_type: @json(old('sales_type', $booking->sales_type ?? '')),
                    on_board_date: @json(old('on_board_date', isset($booking) && $booking->on_board_date ? $booking->on_board_date->format('Y-m-d') : '')),
                    lc_no: @json(old('lc_no', $booking->lc_no ?? '')),
                    lc_issue_bank: @json(old('lc_issue_bank', $booking->lc_issue_bank ?? '')),
                    lc_issue_date: @json(old('lc_issue_date', isset($booking) && $booking->lc_issue_date ? $booking->lc_issue_date->format('Y-m-d') : '')),
                    is_express_bl: @json(old('is_express_bl', $booking->is_express_bl ?? false)),
                    show_preferences: @json(old('show_preferences', $booking->show_preferences ?? false)),
                    containers: [],
                    commodities: [],
                    warehouse_receipts: []
                },
                showPreferencesModal: false,
                memoExpand: true,
                saveBooking() {
                    if (this.validateForm()) {
                        document.getElementById('oceanBookingForm').submit();
                    }
                },
                showToast(type, message) {
                    if(typeof window.showToast === 'function') {
                        window.showToast(type, message);
                    } else {
                        alert(type.toUpperCase() + ': ' + message);
                    }
                },
                validateForm() {
                    this.form.booking_no = this.form.booking_no ? this.form.booking_no.trim() : '';
                    if (!this.form.booking_no) {
                        this.showToast('error', 'Booking No. is required');
                        return false;
                    }
                    if (!this.form.booking_date) {
                        this.showToast('error', 'Booking Date is required');
                        return false;
                    }
                    if (!this.form.office_id) {
                        this.showToast('error', 'Office is required');
                        return false;
                    }
                    return true;
                },
                openAddNewModal(module, selectName) {
                    if (module === 'trade-partner') {
                        window.open('/trade-partner/create', '_blank');
                    } else if (module === 'port') {
                        window.open('/port/create', '_blank');
                    } else if (module === 'vessel') {
                        window.open('/vessel/create', '_blank');
                    }
                },
                addCommodity() {
                    this.form.commodities.push({
                        description: '',
                        pkg: 0,
                        weight: 0,
                        amount: 0
                    });
                },
                removeCommodity(idx) {
                    if (confirm('Are you sure you want to remove this commodity?')) {
                        this.form.commodities.splice(idx, 1);
                        this.calculateTotals();
                    }
                },
                calculateTotals() {
                    let totalPkg = 0;
                    let totalWeight = 0;
                    let totalAmount = 0;
                    this.form.commodities.forEach(c => {
                        totalPkg += parseFloat(c.pkg) || 0;
                        totalWeight += parseFloat(c.weight) || 0;
                        totalAmount += parseFloat(c.amount) || 0;
                    });
                    this.form.pkg_qty = totalPkg;
                    this.form.weight_kg = totalWeight.toFixed(2);
                    this.form.measure_cbm = totalAmount.toFixed(4);
                },

                // ===== WAREHOUSE RECEIPT MODAL =====
                showWrModal: false,
                wrSearchQuery: '',
                wrSearchResults: [],
                wrLoading: false,

                openWarehouseModal() {
                    this.wrSearchQuery = '';
                    this.wrSearchResults = [];
                    this.showWrModal = true;
                    this.searchWarehouseList();
                },

                searchWarehouseList() {
                    this.wrLoading = true;
                    fetch('/ocean-import/warehouse-receipts/search?q=' + encodeURIComponent(this.wrSearchQuery))
                        .then(res => res.json())
                        .then(data => {
                            this.wrSearchResults = (Array.isArray(data) ? data : (data.data || [])).map(r => ({ ...r, selected: false }));
                            this.wrLoading = false;
                        })
                        .catch(() => { this.wrLoading = false; });
                },

                loadSelectedWarehouseReceipts() {
                    const selected = this.wrSearchResults.filter(r => r.selected);
                    if (!selected.length) { this.showToast('error', 'Please select at least one warehouse receipt.'); return; }
                    selected.forEach(s => {
                        const exists = this.form.warehouse_receipts.some(r => r.receipt_no === s.receipt_no);
                        if (!exists) {
                            this.form.warehouse_receipts.push({
                                selected: false,
                                receipt_no: s.receipt_no || '',
                                description: s.description || '',
                                total_pcs: s.total_pcs || 0,
                                available_pcs: s.available_pcs || 0,
                                actual_weight: s.actual_weight || 0,
                                measurement: s.measurement || 0,
                                remarks: s.remarks || ''
                            });
                            // auto-sync totals to Booking row
                            this.form.pkg_qty = (parseFloat(this.form.pkg_qty) || 0) + (parseFloat(s.total_pcs) || 0);
                            this.form.weight_kg = ((parseFloat(this.form.weight_kg) || 0) + (parseFloat(s.actual_weight) || 0)).toFixed(2);
                            this.form.measure_cbm = ((parseFloat(this.form.measure_cbm) || 0) + (parseFloat(s.measurement) || 0)).toFixed(4);
                        }
                    });
                    this.showWrModal = false;
                },

                createItemAndLink() {
                    const newNo = 'WR-' + Date.now().toString().slice(-6);
                    this.form.warehouse_receipts.push({
                        selected: false,
                        receipt_no: newNo,
                        description: '',
                        total_pcs: 1,
                        available_pcs: 1,
                        actual_weight: 0,
                        measurement: 0,
                        remarks: 'Manually created'
                    });
                    this.showToast('success', 'New item "' + newNo + '" created and linked. Fill in the details in the Receiving table.');
                },

                // ===== ACCOUNTING DUMMY STATE =====
                @php
                $invoiceData = [];
                if(isset($booking) && $booking->invoices) {
                    $invoiceData = $booking->invoices->map(fn($inv) => [
                        'id' => $inv->id,
                        'no' => $inv->invoice_no,
                        'type' => $inv->type,
                        'party' => $inv->billTo ? $inv->billTo->name : 'TBD',
                        'revenue' => in_array($inv->type, ['AR', 'DC']) ? $inv->total_amount : 0,
                        'cost' => in_array($inv->type, ['AP', 'DC']) ? $inv->total_amount : 0,
                        'balance' => $inv->balance_amount,
                        'status' => $inv->status,
                        'post_date' => $inv->created_at ? $inv->created_at->format('Y-m-d') : '',
                        'invoice_date' => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : ''
                    ])->toArray();
                }
                @endphp
                invoices: @json($invoiceData),
                addInvoice(type) {
                    if(!this.saved) {
                        this.showToast('warning', 'Please save the booking first.');
                        return;
                    }
                    const baseUrl = '{{ route("accounting.invoices.create") }}';
                    const params = `?type=${type}&invoiceable_type=App\\Models\\OceanBooking&invoiceable_id={{ $booking->id ?? "" }}`;
                    window.open(baseUrl + params, '_blank');
                },
                deleteInvoice(id) {
                    if(!confirm('Are you sure you want to delete this invoice?')) return;
                    fetch('/accounting/invoice/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            this.showToast('success', 'Invoice deleted.');
                            this.invoices = this.invoices.filter(i => i.id !== id);
                        } else {
                            this.showToast('error', data.message || 'Failed to delete invoice.');
                        }
                    });
                },
                get totalRevenue() {
                    return this.invoices.reduce((sum, inv) => sum + parseFloat(inv.revenue || 0), 0).toFixed(2);
                },
                get totalCost() {
                    return this.invoices.reduce((sum, inv) => sum + parseFloat(inv.cost || 0), 0).toFixed(2);
                },
                get totalBalance() {
                    return this.invoices.reduce((sum, inv) => sum + parseFloat(inv.balance || 0), 0).toFixed(2);
                }
            }
        };
    </script>

    <div class="page-content">
        <form action="{{ isset($booking) ? route('ocean-bookings.update', $booking->id) : route('ocean-bookings.store') }}" method="POST" id="oceanBookingForm">
            @csrf
            @if(isset($booking))
                @method('PUT')
            @endif
            @if(session('success'))
                <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;padding:10px 15px;border-radius:4px;margin-bottom:15px;"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:15px;"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:15px;"><strong>Validation Error</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <input type="hidden" name="status" :value="form.status">

        <!-- Breadcrumb -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li> Sales <i class="fa fa-angle-right"></i></li>
                <li> New Booking</li>
            </ul>
        </div>



        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h1 class="caption-subject" style="font-size: 18px;">{{ isset($booking) ? 'Edit' : 'Create' }} Ocean Export Booking</h1>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-freightx" @click="saveBooking" style="background:#f59e0b;">
                    <i class="fa fa-save"></i>
                    <span>SAVE BOOKING</span>
                </button>
                <a href="{{ route('ocean-bookings.index') }}" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="gf-tabs">
            <li :class="activeTab === 'basic' ? 'active' : ''" @click="activeTab = 'basic'"><a>Basic</a></li>
            <li :class="[activeTab === 'accounting' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'accounting' : null"
                style="display: flex; align-items: center; gap: 8px;">
                <a>Accounting</a>
                <button type="button" class="btn-gf-tool" style="height: 14px; border: none; background: transparent; padding: 0;"><i
                        class="fa fa-sliders"></i></button>
            </li>
            <li style="display: none !important;" :class="[activeTab === 'doc' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'doc' : null"><a>Doc Center</a></li>
            <li style="display: none !important;" :class="[activeTab === 'work' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'work' : null"><a>Work Order</a></li>
            <li :class="[activeTab === 'status' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'status' : null"><a>Status</a></li>
        </ul>

        <div x-show="activeTab === 'basic'">
            <div class="portlet light">
                <div class="portlet-title" style="background: #f9fafb;">
                    <div style="display: flex; align-items: center;">
                        <span class="color-remark-tag"></span>
                        <span class="caption-subject">Booking Entry</span>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 8px; font-size: 10px;" @click="showPreferencesModal = true"><i
                                class="fa fa-sliders"></i> Preference</button>
                    </div>
                </div>
                <div class="portlet-body" style="padding: 15px;">
                    <div class="form-grid-4">
                        <!-- Column 1: Basic -->
                        <div class="flex flex-col">
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red">*</span>
                                    Booking No.</label>
                                <div class="form-input-container">
                                    <input type="hidden" name="booking_no" :value="form.booking_no">
                                    <input type="text" class="form-control-gf" x-model="form.booking_no" disabled style="background:#f5f5f5;">
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red">*</span>
                                    Booking Date</label>
                                <div class="form-input-container"><input type="date" name="booking_date" class="form-control-gf"
                                        x-model="form.booking_date"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Quotation No.</label>
                                <div class="form-input-container"><input type="text" class="form-control-gf"
                                        name="quotation_no" x-model="form.quotation_no" placeholder="Enter quotation no"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">ITN No.</label>
                                <div class="form-input-container"><input type="text" class="form-control-gf"
                                        name="itn_no" x-model="form.itn_no"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Sales</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="sales_person_id" x-model="form.sales_person_id">
                                        <option value="">Select...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">OP</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="op_id" x-model="form.op_id">
                                        <option value="">Select...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select></div>
                            </div>
                            <hr style="margin: 10px 0;">
                            <div class="form-group-gf"><label class="form-label-gf">BL Cancelled</label>
                                <div class="form-input-container"><input type="checkbox" name="bl_cancelled" value="1" x-model="form.bl_cancelled"> <input type="date"
                                        class="form-control-gf" name="bl_cancelled_date" x-model="form.bl_cancelled_date" :disabled="!form.bl_cancelled"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Reason</label>
                                <div class="form-input-container"><select class="form-control-gf" name="bl_cancelled_reason" x-model="form.bl_cancelled_reason" :disabled="!form.bl_cancelled">
                                        <option value="">Select...</option>
                                        <option>Customer Request</option>
                                        <option>Schedule Change</option>
                                        <option>Duplicate Booking</option>
                                        <option>Rate Issue</option>
                                        <option>Other</option>
                                    </select></div>
                            </div>
                        </div>

                        <!-- Column 2: Partner -->
                        <div class="flex flex-col">
                            <div class="form-group-gf"><label class="form-label-gf">Ref No.</label>
                                <div class="form-input-container"><input type="text" class="form-control-gf"
                                        name="ref_no" x-model="form.ref_no" placeholder="Enter reference no"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Carrier Bkg No.</label>
                                <div class="form-input-container"><input type="text" class="form-control-gf"
                                        name="carrier_bkg_no" x-model="form.carrier_bkg_no"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Carrier</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="carrier_id" x-model="form.carrier_id">
                                        <option value="">Select...</option>
                                        @if($carriers->count())
                                            @foreach($carriers as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach($tradePartners as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        @endif
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'carrier_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Ship Mode</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="ship_mode" x-model="form.ship_mode">
                                        <option>FCL</option>
                                        <option>LCL</option>
                                    </select></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Svc Term</label>
                                <div class="form-input-container"><select class="form-control-gf" style="width: 48%;"
                                        name="svc_term_from_id" x-model="form.svc_term_from_id">
                                        <option value="">Select...</option>
                                        @foreach($serviceTerms as $st)
                                            <option value="{{ $st->id }}">{{ $st->code }}</option>
                                        @endforeach
                                    </select> ~ <select class="form-control-gf" style="width: 48%;"
                                        name="svc_term_to_id" x-model="form.svc_term_to_id">
                                        <option value="">Select...</option>
                                        @foreach($serviceTerms as $st)
                                            <option value="{{ $st->id }}">{{ $st->code }}</option>
                                        @endforeach
                                    </select></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Incoterms</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="incoterms" x-model="form.incoterms">
                                        <option value="">Select...</option>
                                        @foreach($incoterms as $inc)
                                            <option value="{{ $inc->code }}">{{ $inc->code }}</option>
                                        @endforeach
                                    </select></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Actual Shipper</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="actual_shipper_id" x-model="form.actual_shipper_id">
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'actual_shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button> </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Customer</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="customer_id" x-model="form.customer_id">
                                        <option value="">Select...</option>
                                        @if($customers->count())
                                            @foreach($customers as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach($tradePartners as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        @endif
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'customer_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Bill To</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="bill_to_id" x-model="form.bill_to_id">
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'bill_to_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Consignee</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="consignee_id" x-model="form.consignee_id">
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'consignee_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Notify</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="notify_id" x-model="form.notify_id">
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'notify_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Shipping Agent</label>
                                <div class="form-input-container"><input type="text" class="form-control-gf"
                                        name="shipping_agent" x-model="form.shipping_agent" placeholder="Enter shipping agent"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">HBL Agent</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="hbl_agent_id" x-model="form.hbl_agent_id">
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbl_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Forwarding Agent</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="forwarding_agent_id" x-model="form.forwarding_agent_id">
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'forwarding_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Co-Loader</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="co_loader_id" x-model="form.co_loader_id">
                                        <option value="">Select...</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'co_loader_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                        </div>

                        <!-- Column 3: Route -->
                        <div class="flex flex-col">
                            <div class="form-group-gf"><label class="form-label-gf">ETD</label>
                                <div class="form-input-container"><input type="date" name="etd" class="form-control-gf"
                                        x-model="form.etd" style="text-align: center;"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">ETA</label>
                                <div class="form-input-container"><input type="date" name="eta" class="form-control-gf"
                                        x-model="form.eta" style="text-align: center;"></div>
                            </div>
                                 <div class="form-group-gf"><label class="form-label-gf">W/H Cut-off</label>
                                <div class="form-input-container"><input type="datetime-local"
                                        class="form-control-gf" name="wh_cutoff" style="text-align: center;"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Doc Cut-off</label>
                                <div class="form-input-container"><input type="datetime-local"
                                        class="form-control-gf" name="doc_cutoff" style="text-align: center;"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Port Cut-off</label>
                                <div class="form-input-container"><input type="datetime-local"
                                        class="form-control-gf" name="port_cutoff" style="text-align: center;"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">VGM Cut-off</label>
                                <div class="form-input-container"><input type="datetime-local"
                                        class="form-control-gf" name="vgm_cutoff" style="text-align: center;"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Rail Cut-off</label>
                                <div class="form-input-container"><input type="datetime-local"
                                        class="form-control-gf" name="rail_cutoff" style="text-align: center;"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Early Return</label>
                                <div class="form-input-container"><input type="datetime-local"
                                        class="form-control-gf" name="early_return" style="text-align: center;"></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Vessel</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="vessel_id" x-model="form.vessel_id">
                                        <option value="">Select...</option>
                                        @foreach($vessels as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('vessel', 'vessel_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Voyage</label>
                                <div class="form-input-container"><input type="text" name="voyage" class="form-control-gf"
                                        x-model="form.voyage"></div>
                            </div>
                            <div class="form-group-gf" style="position: relative;"><label class="form-label-gf">Container TP/SZ</label>
                                <div class="form-input-container">
                                    <input type="text" class="form-control-gf" name="container_no"
                                        x-model="form.container_no" readonly style="background:#f5f5f5;">
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="showTpSzCard = !showTpSzCard"><i class="fa fa-plus" style="font-size:9px;"></i></button>
                                </div>
                                <div x-show="showTpSzCard" @click.away="showTpSzCard = false"
                                    style="position: absolute; top: 100%; left: 110px; right: 0; z-index: 1000; background: #fff; border: 1px solid #ccc; border-radius: 3px; padding: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-top: 2px;">
                                    <div style="font-size: 11px; font-weight: 700; color: #4b77be; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Add TP/SZ</div>
                                    <div style="display: flex; gap: 8px; align-items: end;">
                                        <div style="flex: 1;">
                                            <label style="font-size: 10px; color: #888; display: block; margin-bottom: 2px;">TP/SZ</label>
                                            <select x-model="tpSzForm.type_id" style="width:100%;height:26px;font-size:11px;border:1px solid #ccc;border-radius:2px;padding:2px 4px;">
                                                <option value="">Select...</option>
                                                @foreach($containerTypes as $ct)
                                                <option value="{{ $ct->id }}">{{ $ct->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div style="width: 70px;">
                                            <label style="font-size: 10px; color: #888; display: block; margin-bottom: 2px;">Qty</label>
                                            <input type="number" x-model="tpSzForm.qty" min="1" max="99" value="1" style="width:100%;height:26px;font-size:11px;border:1px solid #ccc;border-radius:2px;padding:2px 4px;">
                                        </div>
                                        <div>
                                            <button type="button" class="btn-freightx" @click="addTpSz()" style="height:26px; padding:0 10px; font-size:11px;">Add</button>
                                        </div>
                                    </div>
                                    <div style="margin-top: 6px;">
                                        <button type="button" class="btn-default-gf" @click="showTpSzCard = false" style="font-size:10px; padding: 2px 6px;">Close</button>
                                    </div>
                                </div>
                                <div x-show="form.containers.length" style="margin-top: 3px; display: flex; flex-wrap: wrap; gap: 4px;">
                                    <template x-for="(c, idx) in form.containers" :key="idx">
                                        <span style="display:inline-flex;align-items:center;gap:3px;background:#e8f0fe;border:1px solid #c4d7f5;border-radius:3px;padding:1px 6px;font-size:10px;color:#333;">
                                            <span x-text="c.code + ' x ' + c.qty"></span>
                                            <button type="button" @click="removeTpSz(idx)" style="border:none;background:none;cursor:pointer;padding:0;color:#999;font-size:12px;line-height:1;">&times;</button>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">POR</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="por_id" x-model="form.por_id">
                                        <option value="">Select...</option>
                                        @foreach($ports as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'por_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">POL</label>
                                <div class="form-input-container"><select class="form-control-gf" name="pol_id" x-model="form.pol_id">
                                        <option value="">Select...</option>
                                        @foreach($ports as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'pol_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">POD</label>
                                <div class="form-input-container"><select class="form-control-gf" name="pod_id" x-model="form.pod_id">
                                        <option value="">Select...</option>
                                        @foreach($ports as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'pod_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">DEL</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="del_id" x-model="form.del_id">
                                        <option value="">Select...</option>
                                        @foreach($ports as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'del_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">F. Dest</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="fdest_id" x-model="form.fdest_id">
                                        <option value="">Select...</option>
                                        @foreach($ports as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'fdest_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                        </div>

                        <!-- Column 4: Delivery/Dates -->
                        <div class="flex flex-col">
                            <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="cargo_type" x-model="form.cargo_type">
                                        <option value="">Select...</option>
                                        <option>GENERAL CARGO</option>
                                        <option>HAZARDOUS</option>
                                        <option>REFRIGERATED</option>
                                        <option>OVERSIZED</option>
                                        <option>PERISHABLE</option>
                                    </select></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Trucker</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="trucker_id" x-model="form.trucker_id">
                                        <option value="">Select...</option>
                                        @if($truckers->count())
                                            @foreach($truckers as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach($tradePartners as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        @endif
                                    </select> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'trucker_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div>
                            </div>
                       
                            <div class="form-group-gf"><label class="form-label-gf"><span style="color:red">*</span>
                                    Office</label>
                                <div class="form-input-container"><select class="form-control-gf"
                                        name="office_id" x-model="form.office_id">
                                        <option value="">Select...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->name ?? $office->code }}</option>
                                        @endforeach
                                    </select></div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Stackable</label>
                                <div class="form-input-container"><input type="radio" name="stack" checked> Yes
                                    <input type="radio" name="stack" style="margin-left:10px;"> No</div>
                            </div>
                        </div>
                    </div>

                    <div style="height: 15px;"></div>

                    <!-- Preferences Block -->
                    <div x-show="form.show_preferences" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <span class="color-remark-tag" style="background: #eab308; display: inline-block; width: 4px; height: 14px; margin-right: 8px;"></span>
                            <span style="font-size: 13px; font-weight: 700; color: #333;">Additional Preferences</span>
                        </div>
                        <input type="hidden" name="show_preferences" :value="form.show_preferences ? 1 : 0">
                        <div class="form-grid-4">
                            <!-- Hold & Freight Released -->
                            <div class="flex flex-col" style="gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <input type="hidden" name="is_hold" value="0">
                                    <input type="checkbox" id="is_hold" name="is_hold" value="1" x-model="form.is_hold">
                                    <label for="is_hold" style="font-size:12px; margin:0; cursor:pointer;">Hold</label>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Hold By</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="hold_by_id" x-model="form.hold_by_id">
                                            <option value="">-- Select --</option>
                                            @foreach($users ?? [] as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col" style="gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <input type="hidden" name="is_freight_released" value="0">
                                    <input type="checkbox" id="is_freight_released" name="is_freight_released" value="1" x-model="form.is_freight_released">
                                    <label for="is_freight_released" style="font-size:12px; margin:0; cursor:pointer;">Freight Released</label>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Released By</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="freight_released_by_id" x-model="form.freight_released_by_id">
                                            <option value="">-- Select --</option>
                                            @foreach($users ?? [] as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Freight Settings -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Buying Freight</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="buying_freight" x-model="form.buying_freight">
                                            <option value="">-- Select --</option>
                                            <option value="COLLECT">COLLECT</option>
                                            <option value="PREPAID">PREPAID</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Selling Freight</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="selling_freight" x-model="form.selling_freight">
                                            <option value="">-- Select --</option>
                                            <option value="COLLECT">COLLECT</option>
                                            <option value="PREPAID">PREPAID</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Customs Broker</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="customs_broker_id" x-model="form.customs_broker_id">
                                            <option value="">-- Select --</option>
                                            @foreach($tradePartners as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- L/C & Express B/L -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">L/C No.</label>
                                    <div class="form-input-container"><input type="text" name="lc_no" class="form-control-gf" x-model="form.lc_no"></div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">L/C Issue Bank</label>
                                    <div class="form-input-container"><input type="text" name="lc_issue_bank" class="form-control-gf" x-model="form.lc_issue_bank"></div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">L/C Issue Date</label>
                                    <div class="form-input-container"><input type="date" name="lc_issue_date" class="form-control-gf" x-model="form.lc_issue_date"></div>
                                </div>
                            </div>

                            <!-- Dates & Settings -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Place of Receipt ETD</label>
                                    <div class="form-input-container"><input type="date" name="por_etd" class="form-control-gf" x-model="form.por_etd"></div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">On Board Date</label>
                                    <div class="form-input-container"><input type="date" name="on_board_date" class="form-control-gf" x-model="form.on_board_date"></div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Sales Type</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="sales_type" x-model="form.sales_type">
                                            <option value="">-- Select --</option>
                                            <option value="NOMINATION">NOMINATION</option>
                                            <option value="FREEHAND">FREEHAND</option>
                                        </select>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 5px; margin-top:15px; margin-left:5px;">
                                    <input type="hidden" name="is_express_bl" value="0">
                                    <input type="checkbox" id="is_express_bl" name="is_express_bl" value="1" x-model="form.is_express_bl">
                                    <label for="is_express_bl" style="font-size:12px; margin:0; cursor:pointer;">Express B/L (Yes/No)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PO & Containers -->
                    <div>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; margin-bottom: 10px; padding-bottom: 5px;">
                            <span style="font-size: 13px; font-weight: 700; color: #32c5d2;">P.O. No.</span>
                            <div style="font-size: 11px;">
                                <label style="margin-right: 15px;"><input type="radio" name="po_map" checked>
                                    Container based</label>
                                <label><input type="radio" name="po_map"> Item based</label>
                            </div>
                        </div>
                        <input type="text" class="form-control-gf" name="po_no" placeholder="Add P.O. here..."
                            style="height: 30px; margin-bottom: 15px;">

                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="font-size: 13px; font-weight: 700; color: #32c5d2;">Container List</span>
                            <label style="font-size: 11px;"><input type="checkbox"> Add pallet info</label>
                        </div>
                        <table class="table-gf">
                            <thead>
                                <tr>
                                    <th style="width: 30px; text-align: center;">#</th>
                                    <th>Total</th>
                                    <th style="text-align: right;">PKG</th>
                                    <th style="text-align: right;">Weight (KG)</th>
                                    <th style="text-align: right;">Measure (CBM)</th>
                                    <th>P.O. No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background: #fffdf2;">
                                    <td style="text-align: center;"><input type="radio" checked></td>
                                    <td>Booking</td>
                                    <td style="text-align: right;"><input type="text" class="form-control-gf"
                                            style="text-align: right; width: 60px;" name="pkg_qty" x-model="form.pkg_qty"></td>
                                    <td style="text-align: right;"><input type="text" class="form-control-gf"
                                            style="text-align: right; width: 80px;" name="weight_kg" x-model="form.weight_kg"></td>
                                    <td style="text-align: right;"><input type="text" class="form-control-gf"
                                            style="text-align: right; width: 80px;" name="measure_cbm" x-model="form.measure_cbm"></td>
                                    <td></td>
                                </tr>
                                <tr style="display: none !important;">
                                    <td style="text-align: center;"><input type="radio" disabled></td>
                                    <td>Receiving</td>
                                    <td colspan="4">
                                        <button type="button" class="btn-default-gf"
                                            style="height: 22px; padding: 0 8px; font-size: 10px; background: #4b77be; color: #fff; border: none;"
                                            @click="openWarehouseModal()">
                                            <i class="fa fa-download"></i> Load from Warehouse Receipt List</button>
                                        <button type="button" class="btn-default-gf"
                                            style="height: 22px; padding: 0 8px; font-size: 10px; background: #26c281; color: #fff; border: none; margin-left: 5px;"
                                            @click="createItemAndLink()">
                                            <i class="fa fa-plus-circle"></i> Create Item and Link</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="height: 15px;"></div>

                    <!-- Commodity -->
                    <div>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="font-size: 13px; font-weight: 700; color: #32c5d2;">Commodity</span>
                            <div style="display: flex; gap: 5px;">
                                <button type="button" class="btn-freightx" style="padding: 2px 6px; font-size: 10px;" @click="addCommodity"><i class="fa fa-plus"></i> Add</button>
                                <button type="button" class="btn-default-gf" style="padding: 2px 6px; font-size: 10px; color: #e7505a;" disabled><i class="fa fa-trash"></i> Remove</button>
                            </div>
                        </div>
                        <table class="table-gf">
                            <thead>
                                <tr>
                                    <th style="width: 30px; text-align: center;"><input type="checkbox"></th>
                                    <th>Commodity Description</th>
                                    <th>PKG</th>
                                    <th>PCS</th>
                                    <th style="text-align: right;">Net Weight (KG)</th>
                                    <th style="text-align: right;">Gross Weight (KG)</th>
                                    <th>Measure (CBM)</th>
                                    <th>Container</th>
                                    <th style="width: 30px; text-align: center;">Act</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="form.commodities.length === 0">
                                    <tr>
                                        <td colspan="9" style="text-align: center; color: #999; padding: 10px;">No
                                            Data Available. Please click <strong style="color: #337ab7; cursor: pointer;"
                                                @click="addCommodity">Add</strong> to add a new row.</td>
                                    </tr>
                                </template>
                                <template x-for="(comm, index) in form.commodities" :key="index">
                                    <tr>
                                        <td style="text-align: center;"><input type="checkbox"></td>
                                        <td><input type="text" class="form-control-gf" :name="'commodities['+index+'][description]'" x-model="comm.description">
                                        </td>
                                        <td><input type="text" class="form-control-gf" :name="'commodities['+index+'][pkg]'" x-model="comm.pkg" @input="calculateTotals"></td>
                                        <td><input type="text" class="form-control-gf" :name="'commodities['+index+'][pcs]'"></td>
                                        <td><input type="text" class="form-control-gf" :name="'commodities['+index+'][net_weight]'" style="text-align: right;">
                                        </td>
                                        <td><input type="text" class="form-control-gf" :name="'commodities['+index+'][gross_weight]'" x-model="comm.weight" @input="calculateTotals"
                                                style="text-align: right;"></td>
                                        <td><input type="text" class="form-control-gf" :name="'commodities['+index+'][amount]'" x-model="comm.amount" @input="calculateTotals"
                                                style="text-align: right;"></td>
                                        <td><select class="form-control-gf" :name="'commodities['+index+'][container]'">
                                                <option value="">Select...</option>
                                                <template x-for="c in form.containers">
                                                    <option :value="c.code" x-text="c.code + ' x ' + c.qty"></option>
                                                </template>
                                            </select></td>
                                        <td style="text-align: center;"><i class="fa fa-times" style="cursor:pointer;color:#e7505a;" @click="removeCommodity(index)"></i></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div style="height: 15px;"></div>

                    <!-- Marks & Remarks -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <span
                                style="font-size: 13px; font-weight: 700; color: #32c5d2; display: block; margin-bottom: 5px;">Mark</span>
                            <textarea class="form-control-gf" style="height: 100px; resize: none;" name="marks" x-model="form.marks"></textarea>
                        </div>
                        <div>
                            <span
                                style="font-size: 13px; font-weight: 700; color: #32c5d2; display: block; margin-bottom: 5px;">Description</span>
                            <textarea class="form-control-gf" style="height: 100px; resize: none;" name="description" x-model="form.description"></textarea>
                        </div>
                    </div>

                    <div style="height: 15px;"></div>

                    <div class="flex flex-col">
                        <span
                            style="font-size: 13px; font-weight: 700; color: #32c5d2; display: block; margin-bottom: 5px;">Booking
                            Remarks</span>
                        <textarea class="form-control-gf" style="height: 60px; resize: none;" name="remarks" x-model="form.remarks"></textarea>
                    </div>
                </div>
            </div>


        </div>
        <div x-show="activeTab === 'accounting'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title" style="background: #f9fafb;">
                    <div style="display: flex; align-items: center;">
                        <span class="color-remark-tag"></span>
                        <span class="caption-subject" x-text="'Booking ' + form.booking_no"></span>
                    </div>
                    <div class="actions">
                        <hc-accounting-instruction-video-btn><button type="button" class="btn btn-default btn-sm" onclick="showToast('info', 'Instruction video not available.')"
                                style="padding: 2px 8px; height: 24px;"><i
                                    class="fa fa-info"></i></button></hc-accounting-instruction-video-btn>
                        <div class="btn-group" style="display: inline-block; margin-left: 5px;">
                            <button type="button" class="btn-default-gf" style="height: 24px; padding: 0 10px; font-size: 11px;" onclick="showToast('info', 'Docs menu is disabled in Booking mode.')"><i
                                    class="fa fa-cogs"></i> Docs <i class="fa fa-angle-down"></i></button>
                        </div>
                    </div>
                </div>
                <div class="portlet-body" style="padding: 15px; background: #fdfdfd;">
                    <div style="display: flex; gap: 5px; margin-bottom: 15px; flex-wrap: wrap;">
                        <button type="button" class="btn-freightx" @click="addInvoice('AR')"
                            style="background: #4b77be; border-radius: 2px !important; font-size: 11px;">Origin Revenue
                            (Invoice/AR)</button>
                        <button type="button" class="btn-freightx" @click="addInvoice('DC')"
                            style="background: #4b77be; border-radius: 2px !important; font-size: 11px;">Destination
                            Revenue/Cost (D/C Note)</button>
                        <div style="display: flex;">
                            <button type="button" class="btn-freightx" @click="addInvoice('AP')"
                                style="background: #4b77be; border-radius: 2px 0 0 2px !important; font-size: 11px;">Origin
                                Cost (AP)</button>
                            <button type="button" class="btn-freightx"
                                style="background: #4b77be; border-radius: 0 2px 2px 0 !important; border-left: 1px solid rgba(255,255,255,0.2); padding: 4px 6px; font-size: 11px;"><i
                                    class="fa fa-angle-down"></i></button>
                        </div>
                        <div style="margin-left: auto; display: flex; align-items: center;">
                            <label style="font-size: 11px; color: #666; cursor: pointer;"><input type="checkbox"
                                    checked style="vertical-align: middle;"> Include Draft Amount</label>
                        </div>
                    </div>

                    <table class="table-gf" style="background: #fff; margin-bottom: 15px;">
                        <thead>
                            <tr>
                                <th style="width: 70px;"></th>
                                <th style="width: 30px;"></th>
                                <th>Invoice No.</th>
                                <th>Party</th>
                                <th style="text-align: right;">Revenue</th>
                                <th style="text-align: right;">Cost</th>
                                <th style="text-align: right;">Balance</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: right;">Post Date</th>
                                <th style="text-align: right;">Invoice Date</th>
                                <th style="text-align: center;">Email</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr x-show="invoices.length === 0">
                                <td colspan="12"
                                    style="text-align: center; padding: 25px; color: #999; font-style: italic;">No
                                    invoice data found for this booking.</td>
                            </tr>
                            <template x-for="(inv, index) in invoices" :key="index">
                                <tr>
                                    <td style="text-align: center;"><i class="fa fa-file-text-o" style="color: #337ab7;"></i></td>
                                    <td style="text-align: center;"><input type="checkbox"></td>
                                    <td><a :href="'/accounting/invoice/' + inv.id + '/edit'" target="_blank" style="color: #32c5d2; font-weight: 600;" x-text="inv.no"></a></td>
                                    <td x-text="inv.party"></td>
                                    <td style="text-align: right;" x-text="inv.revenue"></td>
                                    <td style="text-align: right;" x-text="inv.cost"></td>
                                    <td style="text-align: right; font-weight: bold;" :style="inv.balance >= 0 ? 'color: #26c281;' : 'color: #e7505a;'" x-text="inv.balance"></td>
                                    <td style="text-align: center;"><span class="label label-sm label-info" x-text="inv.status"></span></td>
                                    <td style="text-align: right;" x-text="inv.post_date"></td>
                                    <td style="text-align: right;" x-text="inv.invoice_date"></td>
                                    <td style="text-align: center;"><button type="button" class="btn btn-xs btn-default"><i class="fa fa-envelope-o"></i></button></td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn btn-xs btn-default" @click="window.open('/accounting/invoice/' + inv.id + '/edit', '_blank')"><i class="fa fa-pencil"></i></button>
                                        <button type="button" class="btn btn-xs btn-danger" @click="deleteInvoice(inv.id)"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f9fafb; font-weight: bold;">
                                <td colspan="4" style="text-align: right; color: #555;">Total (USD)</td>
                                <td style="text-align: right; color: #337ab7;" x-text="totalRevenue">0.00</td>
                                <td style="text-align: right; color: #337ab7;" x-text="totalCost">0.00</td>
                                <td style="text-align: right; color: #337ab7;" :style="totalBalance >= 0 ? 'color: #337ab7;' : 'color: #e7505a;'" x-text="totalBalance">0.00</td>
                                <td colspan="5"></td>
                            </tr>
                            <tr style="background: #f9fafb; font-weight: bold;">
                                <td colspan="4" style="text-align: right; color: #555;">Amount (USD)</td>
                                <td style="text-align: right; color: #337ab7;" colspan="2" x-text="totalBalance">0.00</td>
                                <td colspan="6"></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div style="display: flex; justify-content: flex-end;">
                        <table class="table-gf" style="width: 45%;">
                            <thead>
                                <tr>
                                    <th style="width: 40%;"></th>
                                    <th style="text-align: right;">Amount</th>
                                    <th style="text-align: right;">Profit Percentage</th>
                                    <th style="text-align: right;">Profit Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background: #f9fafb;">
                                    <td style="font-weight: bold; color: #555;">HB/L Profit</td>
                                    <td style="text-align: right; font-weight: bold;" :style="totalBalance >= 0 ? 'color: #337ab7;' : 'color: #e7505a;'" x-text="totalBalance">0.00</td>
                                    <td style="text-align: right; font-weight: bold; color: #337ab7;" x-text="totalRevenue > 0 ? ((totalBalance / totalRevenue) * 100).toFixed(2) + '%' : 'N/A'">N/A</td>
                                    <td style="text-align: right; font-weight: bold; color: #337ab7;" x-text="totalRevenue > 0 ? ((totalBalance / totalRevenue) * 100).toFixed(2) + '%' : 'N/A'">N/A</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="height: 25px;"></div>

                    <!-- Memo Section Transposed from Angular -->
                    <div class="memo-section">
                        <div class="memo-header" @click="memoExpand = !memoExpand">
                            <span>Memo</span>
                            <div class="actions" style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" class="btn-default-gf"
                                    style="height: 18px; padding: 0 5px; font-size: 10px;">Document (0) <i
                                        class="fa fa-external-link"></i></button>
                                <i :class="memoExpand ? 'fa fa-chevron-up' : 'fa fa-chevron-down'"></i>
                            </div>
                        </div>
                        <div class="memo-body" x-show="memoExpand" x-transition>
                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                                <div>
                                    <div style="margin-bottom: 10px;">
                                        <button type="button" class="btn-gf-tool"
                                            style="background: #26c281; color: #fff; border: none; height: 24px; padding: 0 10px; border-radius: 2px !important;"><i
                                                class="fa fa-plus"></i> New</button>
                                    </div>
                                    <table class="memo-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 30px; text-align: center;"><i class="fa fa-bell"
                                                        title="Pop-up Alert"></i></th>
                                                <th>Subject</th>
                                                <th colspan="2" style="text-align: center;">Last Modified</th>
                                                <th colspan="2" style="text-align: center;">Created</th>
                                                <th style="text-align: center;">Action / TP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7"
                                                    style="text-align: center; padding: 15px; color: #999;">No records
                                                    found. Click "New" to create a memo.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <textarea class="memo-content-area" style="height: 150px;" placeholder="Select a memo to view content..." disabled></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div x-show="activeTab === 'work'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title" style="background: #f9fafb;">
                    <div style="display: flex; align-items: center;">
                        <span class="color-remark-tag"></span>
                        <span class="caption-subject" x-text="'Booking ' + form.booking_no"></span>
                    </div>
                    <div class="actions">
                        <div class="btn-group" style="display: inline-block;">
                            <button type="button" class="btn-default-gf" style="height: 24px; padding: 0 10px; font-size: 11px;" onclick="showToast('info', 'Docs menu is disabled in Booking mode.')"><i
                                    class="fa fa-cogs"></i> Docs <i class="fa fa-angle-down"></i></button>
                        </div>
                    </div>
                </div>
                <div class="portlet-body" style="padding: 15px; background: #fdfdfd;">
                    <div class="portlet-tool"
                        style="margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <div class="btn-group">
                            @if(isset($booking))
                            <button type="button" class="btn green"
                                style="background: #32c5d2; color: #fff; border: none; height: 26px; padding: 0 12px; border-radius: 2px !important;"
                                title="New"
                                onclick="window.open('{{ route('ocean-export.work-order.create', ['workable_type' => 'App\Models\OceanBooking', 'workable_id' => $booking->id, 'source' => 'ocean_booking', 'source_id' => $booking->id]) }}', '_self')"><i
                                    class="fa fa-plus"></i></button>
                            @else
                            <button type="button" class="btn green" disabled
                                style="background: #e1e5ec; color: #777; border: none; height: 26px; padding: 0 12px; border-radius: 2px !important; cursor: not-allowed;"
                                title="Please save booking first"
                                onclick="showToast('warning', 'Please save the booking first to create a work order.')"><i
                                    class="fa fa-plus"></i></button>
                            @endif
                            <button type="button" class="btn btn-default"
                                style="height: 26px; padding: 0 12px; border: 1px solid #ccc; background: #fff; color: #666; margin-left: 5px; border-radius: 2px !important;"
                                @click="clearWorkOrders()" title="Clear Data"><i class="fa fa-trash"></i></button>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-xs btn-default" @click="syncWorkOrders()"
                                style="font-size: 10px;"><i class="fa fa-refresh"></i> Sync Data</button>
                        </div>
                    </div>

                    <table class="table-gf" style="background: #fff; border: 1px solid #ddd;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 3%; text-align: center;"><input type="checkbox" disabled
                                        style="margin:0;"></th>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="width: 10%; text-align: center;">D/O Type</th>
                                <th style="width: 25%;">Freight Pickup</th>
                                <th style="width: 20%;">Delivery</th>
                                <th style="width: 15%;">Trucker</th>
                                <th style="width: 12%; text-align: center;">Last Modified</th>
                                <th style="width: 15%; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="wo in workOrders" :key="wo.id">
                                <tr>
                                    <td style="text-align: center;"><input type="checkbox" style="margin:0;"></td>
                                    <td style="text-align: center;" x-text="wo.work_order_no"></td>
                                    <td style="text-align: center;" x-text="wo.subject"></td>
                                    <td x-text="wo.freight_pickup_location_name || '-'"></td>
                                    <td x-text="wo.empty_return_location_name || '-'"></td>
                                    <td x-text="wo.vendor_name || '-'"></td>
                                    <td style="text-align: center;" x-text="wo.updated_at || wo.created_at || '-'"></td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            <button type="button" class="btn btn-xs"
                                                style="background: #39cccc; color: #fff; border: none; height: 22px; padding: 0 8px; font-size: 11px; white-space: nowrap; display: flex; align-items: center; gap: 4px;"
                                                @click="downloadWorkOrder(wo)"><i class="fa fa-file-excel-o"></i> Excel</button>
                                            <button type="button" class="btn btn-xs btn-primary"
                                                style="height: 22px; padding: 0 8px; font-size: 11px; white-space: nowrap; display: flex; align-items: center; gap: 4px;"
                                                @click="window.open('/ocean-export/work-order/' + wo.id + '/edit?source=ocean_booking&source_id=' + {{ $booking->id ?? 'null' }}, '_self')"><i
                                                    class="fa fa-pencil"></i> Edit</button>
                                            <button type="button" class="btn btn-xs btn-danger"
                                                style="background: #ec7063; color: #fff; border: none; height: 22px; padding: 0 8px; font-size: 11px; white-space: nowrap; display: flex; align-items: center; gap: 4px;"
                                                @click="deleteWorkOrder(wo.id)"><i
                                                    class="fa fa-trash"></i> Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="workOrders.length === 0">
                                <td colspan="8"
                                    style="text-align: center; padding: 40px; color: #999; font-style: italic; background: #fff;">
                                    No work order data found. Click the "+" button to create a new delivery order.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'doc'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title" style="background: #f9fafb;">
                    <div style="display: flex; align-items: center;">
                        <span class="color-remark-tag"></span>
                        <span class="caption-subject" x-text="'Booking ' + form.booking_no"></span>
                    </div>
                </div>
                <div class="portlet-body" style="padding: 15px; background: #fdfdfd;">
                    <div style="margin-bottom: 15px; display: flex; gap: 8px;">
                        <button type="button" class="btn-freightx" style="background: #4b77be;" onclick="showToast('info', 'Document upload is disabled in Booking mode.')"><i class="fa fa-upload"></i>
                            Upload Document</button>
                        <button type="button" class="btn-default-gf" onclick="showToast('info', 'Downloading selected files...')"><i class="fa fa-download"></i> Batch Download</button>
                        <button type="button" class="btn-default-gf" onclick="showToast('info', 'Email Composer is disabled in Booking mode.')"><i class="fa fa-envelope-o"></i> Email</button>
                    </div>

                    <table class="table-gf" style="background: #fff; border: 1px solid #ddd;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 30px; text-align: center;"><input type="checkbox"
                                        style="margin:0;"></th>
                                <th style="width: 40px;"></th>
                                <th>Document Name</th>
                                <th>Category</th>
                                <th>Remark</th>
                                <th>File Name</th>
                                <th style="text-align: right;">Size</th>
                                <th style="text-align: right;">Upload Date</th>
                                <th>Uploader</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10"
                                    style="text-align: center; padding: 50px; color: #999; font-style: italic; background: #fff;">
                                    <i class="fa fa-folder-open-o"
                                        style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                                    No documents uploaded yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'status'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title" style="background: #f9fafb;">
                    <div style="display: flex; align-items: center;">
                        <span class="color-remark-tag"></span>
                        <span class="caption-subject" x-text="'Booking ' + form.booking_no"></span>
                    </div>
                </div>
                <div class="portlet-body" style="padding: 15px; background: #fdfdfd;">
                    <table class="table-gf" style="background: #fff; border: 1px solid #ddd;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 250px;">Milestone</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th style="text-align: right; width: 180px;">Date/Time</th>
                                <th>Updated By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="background: #fcfcfc; font-weight: 600;">Booking Received</td>
                                <td><span class="label label-sm label-success"
                                        style="background: #26c281; font-size: 10px;">COMPLETED</span></td>
                                <td>MEO OFFICE</td>
                                <td style="text-align: right;" x-text="form.booking_date + ' 10:00'"></td>
                                <td>DEMO_925</td>
                            </tr>
                            <tr>
                                <td style="background: #fcfcfc; font-weight: 600;">Cargo Picked Up</td>
                                <td><span class="label label-sm label-default"
                                        style="background: #abb7b7; font-size: 10px;">PENDING</span></td>
                                <td>-</td>
                                <td style="text-align: right;">-</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td style="background: #fcfcfc; font-weight: 600;">Vessel ETD</td>
                                <td><span class="label label-sm label-default"
                                        style="background: #abb7b7; font-size: 10px;">SCHEDULED</span></td>
                                <td x-text="form.pol"></td>
                                <td style="text-align: right;" x-text="form.etd"></td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td style="background: #fcfcfc; font-weight: 600;">Vessel ETA</td>
                                <td><span class="label label-sm label-default"
                                        style="background: #abb7b7; font-size: 10px;">SCHEDULED</span></td>
                                <td x-text="form.pod"></td>
                                <td style="text-align: right;" x-text="form.eta"></td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td style="background: #fcfcfc; font-weight: 600;">Cargo Delivered</td>
                                <td><span class="label label-sm label-default"
                                        style="background: #abb7b7; font-size: 10px;">PENDING</span></td>
                                <td x-text="form.fdest"></td>
                                <td style="text-align: right;">-</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </form>

        <!-- Warehouse Receipt Modal -->
        <div x-show="showWrModal" style="position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5);" @click.self="showWrModal = false">
            <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; pointer-events: none;">
            <div style="background: #fff; width: 700px; max-height: 80vh; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.2); pointer-events: all;">
                <!-- Modal Header -->
                <div style="padding: 10px 15px; background: #4b77be; color: #fff; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 13px; font-weight: 700;"><i class="fa fa-archive"></i> Load from Warehouse Receipt List</span>
                    <button type="button" @click="showWrModal = false" style="background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;">&times;</button>
                </div>
                <!-- Search Bar -->
                <div style="padding: 10px 15px; border-bottom: 1px solid #eee; display: flex; gap: 8px;">
                    <input type="text" class="form-control-gf" x-model="wrSearchQuery" placeholder="Search by receipt no, description..." style="flex: 1;" @keyup.enter="searchWarehouseList()">
                    <button type="button" class="btn-freightx" style="padding: 0 12px; font-size: 11px;" @click="searchWarehouseList()">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
                <!-- Results -->
                <div style="flex: 1; overflow-y: auto; padding: 10px 15px;">
                    <div x-show="wrLoading" style="text-align: center; padding: 20px; color: #888;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
                    <div x-show="!wrLoading && wrSearchResults.length === 0" style="text-align: center; padding: 20px; color: #aaa; font-size: 12px;">No warehouse receipts found.</div>
                    <table class="table-gf" x-show="!wrLoading && wrSearchResults.length > 0">
                        <thead>
                            <tr>
                                <th style="width: 30px; text-align: center;"><input type="checkbox" @change="wrSearchResults.forEach(r => r.selected = $event.target.checked)"></th>
                                <th>Receipt No.</th>
                                <th>Description</th>
                                <th style="text-align: right;">Total PCS</th>
                                <th style="text-align: right;">Avail PCS</th>
                                <th style="text-align: right;">Weight (KG)</th>
                                <th style="text-align: right;">Measure (CBM)</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(r, i) in wrSearchResults" :key="i">
                                <tr :style="r.selected ? 'background:#e8f0fe;' : ''">
                                    <td style="text-align: center;"><input type="checkbox" x-model="r.selected"></td>
                                    <td style="font-weight: 600; color: #337ab7;" x-text="r.receipt_no"></td>
                                    <td x-text="r.description || '-'"></td>
                                    <td style="text-align: right;" x-text="r.total_pcs || 0"></td>
                                    <td style="text-align: right;" x-text="r.available_pcs || 0"></td>
                                    <td style="text-align: right;" x-text="r.actual_weight || 0"></td>
                                    <td style="text-align: right;" x-text="r.measurement || 0"></td>
                                    <td x-text="r.remarks || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <!-- Footer -->
                <div style="padding: 10px 15px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 8px; background: #f8f9fa;">
                    <button type="button" class="btn-default-gf" style="padding: 4px 12px; font-size: 11px;" @click="showWrModal = false">Cancel</button>
                    <button type="button" class="btn-freightx" style="padding: 4px 12px; font-size: 11px;" @click="loadSelectedWarehouseReceipts()">
                        <i class="fa fa-check"></i> Load Selected
                    </button>
                </div>
            </div>  {{-- close modal card --}}
            </div>  {{-- close flex centering wrapper --}}
        </div>  {{-- close overlay --}}

        <!-- Preferences Modal -->
        <div class="modal-overlay" x-show="showPreferencesModal" style="display: none;" @click.self="showPreferencesModal = false">
            <div class="modal-content" style="max-width: 450px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <div class="modal-header" style="background: #3b82f6; padding: 12px 20px; color: white;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 500; display:flex; align-items:center; gap:8px;">
                        <i class="fa fa-sliders"></i> Booking Preferences
                    </h3>
                    <button type="button" @click="showPreferencesModal = false" style="background: none; border: none; color: white; cursor: pointer; font-size: 18px;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 15px; align-items:center; text-align:center;">
                        <i class="fa fa-question-circle" style="font-size: 36px; color: #3b82f6;"></i>
                        <p style="margin: 0; font-size: 14px; color: #4b5563;">Would you like to show additional preferences and fields for this booking (Hold, Freight, L/C, etc)?</p>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 12px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" @click="form.show_preferences = false; showPreferencesModal = false" style="padding: 6px 12px; border: 1px solid #d1d5db; background: white; border-radius: 4px; font-size: 13px; cursor: pointer;">No, Hide Them</button>
                    <button type="button" @click="form.show_preferences = true; showPreferencesModal = false" style="padding: 6px 12px; border: none; background: #3b82f6; color: white; border-radius: 4px; font-size: 13px; cursor: pointer;">Yes, Show Fields</button>
                </div>
            </div>
        </div>

    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        function showToast(type, msg) {
            const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle', warning: 'exclamation-triangle' };
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
            document.getElementById('toast-container').appendChild(t);
            setTimeout(() => {
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, 3000);
        }
    </script>
</x-layout>
