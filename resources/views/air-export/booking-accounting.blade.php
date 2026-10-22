<x-layout>
<div class="page-content" style="background: #eef1f5; min-height: 100vh; padding: 15px;">
    <script>
        function bookingAccountingModule() {
            return {
                ToolsOpen: false,
                showChargeModal: false,
                chargeModalType: 'AR',
                includeDraft: true,
                chargesList: [],
                memos: [],
                bookingId: {{ isset($booking) ? $booking->id : 'null' }},
                editingChargeIdx: null,
                chargeForm: {
                    charge_code: '',
                    charge_name: '',
                    rate: '',
                    qty: '1',
                    amount: '',
                    currency_id: '',
                    pc: 'COLLECT',
                    vendor_id: '',
                    bill_to_id: '',
                    remark: ''
                },
                openChargeModal(type, idx) {
                    this.chargeModalType = type;
                    this.editingChargeIdx = idx !== undefined ? idx : null;
                    if (idx !== undefined && this.chargesList[idx]) {
                        let c = this.chargesList[idx];
                        this.chargeForm = {
                            charge_code: c.charge_code || '',
                            charge_name: c.charge_name || '',
                            rate: c.rate || '',
                            qty: c.qty || '1',
                            amount: c.amount || '',
                            currency_id: c.currency_id || '',
                            pc: c.pc || 'COLLECT',
                            vendor_id: c.vendor_id || '',
                            bill_to_id: c.bill_to_id || '',
                            remark: c.remark || ''
                        };
                    } else {
                        this.editingChargeIdx = null;
                        this.chargeForm = {
                            charge_code: '', charge_name: '', rate: '', qty: '1', amount: '',
                            currency_id: '', pc: 'COLLECT', vendor_id: '', bill_to_id: '', remark: ''
                        };
                    }
                    this.showChargeModal = true;
                },
                closeChargeModal() {
                    this.showChargeModal = false;
                    this.editingChargeIdx = null;
                },
                saveCharge() {
                    if (!this.chargeForm.charge_code) { alert('Charge code is required.'); return; }
                    let amount = (parseFloat(this.chargeForm.rate) || 0) * (parseFloat(this.chargeForm.qty) || 1);
                    this.chargeForm.amount = amount;

                    if (this.editingChargeIdx !== null) {
                        let charge = this.chargesList[this.editingChargeIdx];
                        fetch('/air-export/booking/charges/' + charge.id, {
                            method: 'PUT',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify(this.chargeForm)
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                this.chargesList[this.editingChargeIdx] = data.charge;
                            }
                        }).catch(() => alert('Failed to update charge.'));
                    } else {
                        this.chargeForm.type = this.chargeModalType === 'DC' ? 'DC_NOTE' : this.chargeModalType;
                        fetch('/air-export/booking/' + this.bookingId + '/charges', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: JSON.stringify(this.chargeForm)
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                this.chargesList.push(data.charge);
                            }
                        }).catch(() => alert('Failed to save charge.'));
                    }
                    this.closeChargeModal();
                },
                deleteCharge(idx) {
                    if (!confirm('Delete this charge?')) return;
                    let charge = this.chargesList[idx];
                    fetch('/air-export/booking/charges/' + charge.id, {
                        method: 'DELETE',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                    }).then(r => r.json()).then(data => {
                        if (data.success) this.chargesList.splice(idx, 1);
                    }).catch(() => alert('Failed to delete charge.'));
                },
                deleteAllCharges() {
                    if (!confirm('Delete all charges?')) return;
                    fetch('/air-export/booking/' + this.bookingId + '/charges/all', {
                        method: 'DELETE',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                    }).then(r => r.json()).then(data => {
                        if (data.success) this.chargesList = [];
                    }).catch(() => alert('Failed to delete all charges.'));
                },
                get totalAr() { return this.chargesList.filter(c => c.type === 'AR' || c.type === 'DC_NOTE').reduce((s, c) => s + (parseFloat(c.amount) || 0), 0); },
                get totalAp() { return this.chargesList.filter(c => c.type === 'AP').reduce((s, c) => s + (parseFloat(c.amount) || 0), 0); },
                get totalBalance() { return this.totalAr - this.totalAp; },
                createInvoice(type) {
                    // Check if booking is saved
                    if (!this.bookingId) {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Please save the booking first before creating invoices');
                        } else {
                            alert('Please save the booking first before creating invoices');
                        }
                        return;
                    }
                    
                    // Define routes for each invoice type
                    const routes = {
                        'AR': `/accounting/invoice/create?type=AR&shipment_type=air_booking&shipment_id=${this.bookingId}`,
                        'DC': `/accounting/invoice/create?type=DC&shipment_type=air_booking&shipment_id=${this.bookingId}`,
                        'AP': `/accounting/invoice/create?type=AP&shipment_type=air_booking&shipment_id=${this.bookingId}`
                    };

                    // Open invoice creation page in new tab
                    if (routes[type]) {
                        window.open(routes[type], '_blank');
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('info', `${type} invoice creation - Coming soon`);
                        } else {
                            alert(`${type} invoice creation - Coming soon`);
                        }
                    }
                },
                init() {
                    if (this.bookingId) {
                        fetch('/air-export/booking/' + this.bookingId + '/charges')
                            .then(r => r.json()).then(data => { this.chargesList = data; })
                            .catch(() => {});
                        fetch('/air-export/booking/' + this.bookingId + '/history')
                            .then(r => r.json()).then(data => { this.memos = data; })
                            .catch(() => {});
                    }
                }
            }
        }
    </script>
    <x-form-styles />
    <style>
        [x-cloak] { display: none !important; }
        .booking-container { max-width: 100%; margin: 0; }



        .form-section-title { display: flex; align-items: center; gap: 15px; margin: 15px 0 5px; color: #3b82f6; font-size: 11px; font-weight: 600; }
        .form-section-title::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        .accounting-toolbar { padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .table-custom { width: 100%; border-collapse: collapse; font-size: 10px; }
        .table-custom th { background: #f8fafc; padding: 4px; font-size: 10px; font-weight: 600; color: #475569; border: 1px solid #e2e8f0; }
        .table-custom td { padding: 4px; border: 1px solid #e2e8f0; font-size: 10px; background: #fff; }
        .table-custom tr.total-row td { background: #f8fafc; font-weight: 700; }
        .profit-table { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; margin-top: 10px; }
        .profit-table th { background: #f8fafc; padding: 4px; font-size: 10px; font-weight: 600; border: 1px solid #e2e8f0; text-align: right; }
        .profit-table td { padding: 4px; border: 1px solid #e2e8f0; font-size: 10px; text-align: right; font-weight: 700; }



        .memo-content { padding: 10px; display: flex; gap: 10px; background: white; }
        .memo-text { flex: 1; border: 1px solid #cbd5e1; border-radius: 2px; padding: 4px 6px; height: 80px; resize: none; font-size: 10px; outline: none; font-family: inherit; }
        .dropdown-item { display: flex; align-items: center; padding: 6px 14px; font-size: 10px; font-weight: 600; color: #334155; text-decoration: none; }
        .dropdown-item:hover { background: #f8fafc; color: #3b82f6; }
    </style>

    <div class="booking-container" x-data="bookingAccountingModule()" x-init="init()">
        <!-- Breadcrumb -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><a href="/">HOME</a></li>
                <li><i class="fa fa-chevron-right"></i> AIR EXPORT</li>
                <li><i class="fa fa-chevron-right"></i> BOOKING</li>
                <li><i class="fa fa-chevron-right"></i> <span style="color: #333;">ACCOUNTING{{ isset($booking) ? ' - ' . $booking->booking_no : '' }}</span></li>
            </ul>
            @if(isset($booking))
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted);">REF: {{ $booking->booking_no }}</div>
            @endif
        </div>

        <!-- Navigation Tabs -->
        <ul class="gf-tabs">
            <li><a href="{{ isset($booking) ? route('air-bookings.edit', $booking->id) : '/air-export/booking/entry' }}">BASIC INFO</a></li>
            <li class="active"><a href="{{ isset($booking) ? route('air-bookings.accounting', $booking->id) : '/air-export/booking/accounting' }}">ACCOUNTING</a></li>
            <li><a href="{{ isset($booking) ? route('air-bookings.status', $booking->id) : '/air-export/booking/status' }}">STATUS / TRACKING</a></li>
        </ul>

        <!-- Main Board -->
        <div class="portlet light">
            <div class="portlet-title" style="background: #f9fafb;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="caption-subject"><i class="fa fa-file-invoice-dollar mr-2"></i> Booking Accounting{{ isset($booking) ? ' - ' . $booking->booking_no : '' }}</span>
                    <span style="font-size: 10px; color: #64748b; font-weight: 600; margin-left: 12px;"><i class="fa fa-user-circle"></i> OPERATOR: {{ strtoupper($currentUser->name ?? auth()->user()->name ?? 'N/A') }}</span>
                </div>
                <div class="actions" style="display: flex; gap: 5px; position: relative;">
                    <button class="btn-default-gf" style="height: 22px; padding: 0 8px; font-size: 10px;" @click="ToolsOpen = !ToolsOpen"><i class="fa fa-cogs"></i> Docs <i class="fa fa-angle-down"></i></button>
                    <div x-show="ToolsOpen" @click.away="ToolsOpen = false" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #ddd; z-index: 100; min-width: 200px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                        <a href="{{ isset($booking) ? route('shipments.freight-invoice', ['type' => 'air-export', 'id' => $booking->id]) : '#' }}" target="_blank" class="dropdown-item"><i class="fa fa-file-invoice mr-2" style="color: #3b82f6;"></i> GENERATE FREIGHT INVOICE</a>
                        <div style="height: 1px; background: #eee; margin: 4px 0;"></div>
                        <a href="#" @click.prevent="alert('Booking confirmation sent.')" class="dropdown-item"><i class="fa fa-file-pdf mr-2"></i> BOOKING CONFIRMATION</a>
                        <a href="#" @click.prevent="alert('Generating pickup/delivery order...')" class="dropdown-item"><i class="fa fa-truck mr-2"></i> PICKUP / DELIVERY ORDER</a>
                        <div style="height: 1px; background: #eee; margin: 4px 0;"></div>
                        <a href="#" @click.prevent="deleteAllCharges()" class="dropdown-item"><i class="fa fa-trash mr-2"></i> DELETE ALL CHARGES</a>
                    </div>
                </div>
            </div>
            <div class="portlet-body" style="padding: 0;">

            <div class="accounting-toolbar">
                <button type="button" @click.prevent="createInvoice('AR')" class="btn-freightx" style="background: #32c5d2; border: none; color: white; padding: 6px 12px; border-radius: 3px; font-size: 11px; cursor: pointer; transition: all 0.2s;">
                    <i class="fa fa-plus"></i> ORIGIN REVENUE (INVOICE/AR)
                </button>
                <button type="button" @click.prevent="createInvoice('DC')" class="btn-freightx" style="background: #32c5d2; border: none; color: white; padding: 6px 12px; border-radius: 3px; font-size: 11px; cursor: pointer; transition: all 0.2s;">
                    <i class="fa fa-plus"></i> DESTINATION REVENUE/COST (D/C NOTE)
                </button>
                <button type="button" @click.prevent="createInvoice('AP')" class="btn-freightx" style="background: #32c5d2; border: none; color: white; padding: 6px 12px; border-radius: 3px; font-size: 11px; cursor: pointer; transition: all 0.2s;">
                    <i class="fa fa-plus"></i> ORIGIN COST (AP)
                </button>
                <div style="flex: 1;"></div>
                <label style="font-size: 10px; font-weight: 600; color: #666; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                    <input type="checkbox" x-model="includeDraft"> INCLUDE DRAFT AMOUNT
                </label>
            </div>

            <div style="padding: 10px;">
                <template x-if="chargesList.length > 0">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 40px;"></th>
                                <th style="width: 120px;">Charge Code</th>
                                <th>Description</th>
                                <th>Currency</th>
                                <th style="width: 100px; text-align: right;">Revenue</th>
                                <th style="width: 100px; text-align: right;">Cost</th>
                                <th style="width: 100px; text-align: right;">Balance</th>
                                <th style="width: 80px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(charge, idx) in chargesList" :key="charge.id">
                                <tr>
                                    <td style="text-align:center;"><input type="checkbox" x-model="charge.selected"></td>
                                    <td x-text="charge.charge_code"></td>
                                    <td x-text="charge.charge_name || charge.charge_code"></td>
                                    <td x-text="charge.currency ? charge.currency.code : ''"></td>
                                    <td style="text-align:right;color:#32c5d2;" x-text="(charge.type === 'AR' || charge.type === 'DC_NOTE') ? (parseFloat(charge.amount) || 0).toFixed(2) : '0.00'"></td>
                                    <td style="text-align:right;color:#e7505a;" x-text="charge.type === 'AP' ? (parseFloat(charge.amount) || 0).toFixed(2) : '0.00'"></td>
                                    <td style="text-align:right;" x-text="(charge.type === 'AR' || charge.type === 'DC_NOTE' ? 1 : charge.type === 'AP' ? -1 : 0) * (parseFloat(charge.amount) || 0)"></td>
                                    <td style="text-align:center;">
                                        <i class="fa fa-pencil" style="cursor:pointer;color:#4b77be;margin-right:6px;" @click="openChargeModal(charge.type === 'DC_NOTE' ? 'DC' : charge.type, idx)"></i>
                                        <i class="fa fa-times" style="cursor:pointer;color:#e7505a;" @click="deleteCharge(idx)"></i>
                                    </td>
                                </tr>
                            </template>
                            <tr class="total-row">
                                <td colspan="4" style="text-align:right;">TOTAL</td>
                                <td style="text-align:right;" x-text="totalAr.toFixed(2)"></td>
                                <td style="text-align:right;" x-text="totalAp.toFixed(2)"></td>
                                <td style="text-align:right;color:var(--primary-blue);" x-text="totalBalance.toFixed(2)"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </template>
                <template x-if="chargesList.length === 0">
                    <div style="text-align:center;padding:15px;color:#999;font-size:11px;">No charges added. Click an invoice button above to add charges.</div>
                </template>

                <table class="profit-table">
                    <thead>
                        <tr>
                            <th style="width:55%;text-align:left;">HB/L PROFIT</th>
                            <th style="width:15%;">AMOUNT</th>
                            <th style="width:15%;">PROFIT %</th>
                            <th style="width:15%;">MARGIN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:700;color:#444;text-align:left;"></td>
                            <td x-text="totalBalance.toFixed(2)"></td>
                            <td x-text="totalAp > 0 ? ((totalBalance / totalAp) * 100).toFixed(2) + '%' : 'N/A'"></td>
                            <td x-text="(totalAr + totalAp) > 0 ? ((totalBalance / (totalAr + totalAp)) * 100).toFixed(2) + '%' : 'N/A'"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Charge Modal -->
            <template x-if="showChargeModal">
                <div class="modal-overlay" @click.away="closeChargeModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <span class="caption-subject" x-text="'Add ' + (chargeModalType === 'AR' ? 'Revenue' : chargeModalType === 'DC' ? 'D/C Note' : 'Cost') + ' Charge'"></span>
                            <button @click="closeChargeModal()" style="background:none;border:none;cursor:pointer;font-size:16px;">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-grid" style="padding:0;grid-template-columns:repeat(2,1fr);gap:8px;">
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Charge Code</label>
                                    <div class="form-input-container"><input type="text" class="form-control-gf" x-model="chargeForm.charge_code"></div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Charge Name</label>
                                    <div class="form-input-container"><input type="text" class="form-control-gf" x-model="chargeForm.charge_name"></div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Rate</label>
                                    <div class="form-input-container"><input type="number" step="any" class="form-control-gf" x-model="chargeForm.rate"></div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Qty</label>
                                    <div class="form-input-container"><input type="number" step="any" class="form-control-gf" x-model="chargeForm.qty"></div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Pay/Collect</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" x-model="chargeForm.pc">
                                            <option value="PREPAID">PREPAID</option>
                                            <option value="COLLECT">COLLECT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Currency</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" x-model="chargeForm.currency_id">
                                            <option value="">Select Currency...</option>
                                            @foreach($currencies ?? [] as $c)
                                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Vendor</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" x-model="chargeForm.vendor_id">
                                            <option value="">Select Vendor...</option>
                                            @foreach($tradePartners ?? [] as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Bill To</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" x-model="chargeForm.bill_to_id">
                                            <option value="">Select Party...</option>
                                            @foreach($tradePartners ?? [] as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Amount</label>
                                    <div class="form-input-container"><input type="number" step="any" class="form-control-gf" x-model="chargeForm.amount"></div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Remark</label>
                                    <div class="form-input-container"><input type="text" class="form-control-gf" x-model="chargeForm.remark"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button @click="closeChargeModal()" style="background:#fff;border:1px solid #ccc;color:#666;padding:2px 10px;font-size:10px;cursor:pointer;">Cancel</button>
                            <button @click="saveCharge()" style="background:#4b77be;color:white;border:none;padding:2px 10px;font-size:10px;cursor:pointer;">Save Charge</button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="memo-section">
                <div class="memo-header">
                    <span>Memo Overview</span>
                    <button class="btn-default-gf" style="height:auto;">Document (0) <i class="fa fa-external-link-alt"></i></button>
                </div>
                <div class="memo-content">
                    <div class="memo-table">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:30px;"><i class="fa fa-bell"></i></th>
                                    <th>Subject</th>
                                    <th style="text-align:center;">Last Modified</th>
                                    <th style="text-align:center;">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" style="text-align:center;padding:15px;color:#adb5bd;">No memo records found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <textarea class="memo-text" placeholder="Select a memo to view or edit..."></textarea>
                </div>
            </div></div>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>
<script>
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle', warning: 'exclamation-triangle' };
        const container = document.getElementById('toast-container') || (() => {
            const c = document.createElement('div');
            c.id = 'toast-container';
            c.className = 'toast-container';
            document.body.appendChild(c);
            return c;
        })();
        
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
        container.appendChild(t);
        setTimeout(() => t.remove(), 7000);
    }

    @if(session('success'))
        showToast('success', '{{ session('success') }}');
    @endif
    @if(session('error'))
        showToast('error', '{!! addslashes(session('error')) !!}');
    @endif
    @if(session('warning'))
        showToast('warning', '{{ session('warning') }}');
    @endif
    @if($errors->any())
        @foreach($errors->all() as $error)
            showToast('error', '{!! addslashes($error) !!}');
        @endforeach
    @endif
</script>

<style>
    .toast-container {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .toast {
        min-width: 280px;
        padding: 14px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out, fadeOut 0.5s ease-in 6.5s forwards;
        pointer-events: all;
    }
    .toast i { font-size: 16px; }
    .toast.success { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); }
    .toast.error { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
    .toast.warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
    .toast.info { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
    
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; transform: translateX(400px); }
    }
</style>
</x-layout>
