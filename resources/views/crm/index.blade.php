<x-layout>
    @push('styles')
    <style>
        .page-content { padding: 20px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Open Sans', sans-serif !important; }
        .portlet.light { background-color: #fff; border: 1px solid #e7ecf1; border-radius: 4px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .portlet-title { padding: 10px 15px; border-bottom: 1px solid #eef1f5; display: flex; align-items: center; justify-content: space-between; min-height: 40px; }
        .portlet-body { padding: 15px; }
        .caption-subject { color: #4b77be; font-size: 13px; font-weight: 700; text-transform: uppercase; }
        
        .table-custom { width: 100%; border-collapse: collapse; font-size: 11px; background: #fff; }
        .table-custom thead th { text-align: left; padding: 8px 12px; background: #f1f3f6; color: #5b6e84; font-weight: 700; text-transform: uppercase; border: 1px solid #e7ecf1; }
        .table-custom tbody td { padding: 8px 12px; border: 1px solid #e7ecf1; vertical-align: middle; }
        .table-custom tr:hover { background: #f9fafb; cursor: pointer; }

        .btn-freightx { background: #4b77be; color: #fff !important; border: none; padding: 5px 12px; border-radius: 3px; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-freightx:hover { background: #3a62a4; }
        .btn-default-gf { background: #fff; border: 1px solid #ccc; color: #333; padding: 4px 10px; font-size: 11px; border-radius: 3px; cursor: pointer; }

        .form-control-gf { width: 100%; height: 28px; border: 1px solid #c2cad8; padding: 4px 8px; font-size: 11px; border-radius: 2px; }
        .form-label-gf { font-size: 11px; font-weight: 600; color: #666; display: block; margin-bottom: 4px; }
        .gf-section-title { font-size: 12px; font-weight: 700; color: #333; border-left: 3px solid #4b77be; padding-left: 8px; margin-bottom: 15px; background: #f8f9fb; padding-top: 5px; padding-bottom: 5px; }
        
        /* Badges */
        .badge-gf { padding: 2px 8px; border-radius: 2px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .badge-enable { background: #36c6d3; color: #fff; }
        .badge-disable { background: #bac3d0; color: #fff; }
    </style>
    @endpush

    <div class="page-content" x-data="tradePartnerModule()">
        
        <!-- Breadcrumbs -->
        <div style="font-size: 11px; color: #8e9eae; margin-bottom: 15px;">
            <a href="/" style="color: #8e9eae; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#337ab7';" onmouseout="this.style.color='#8e9eae';" target="_blank"><i class="fa fa-home"></i> Home</a> <i class="fa fa-angle-right" style="margin: 0 5px; opacity: 0.5;"></i> 
            <a href="/trade-partner/list" style="color: #8e9eae; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#337ab7';" onmouseout="this.style.color='#8e9eae';">Customers</a> <i class="fa fa-angle-right" style="margin: 0 5px; opacity: 0.5;"></i> 
            <span style="color: #333; font-weight: 700;">Customer List</span>
        </div>

        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h1 class="caption-subject" style="font-size: 18px;" x-text="isCreating ? 'System Partner / New' : 'Customer Directory'"></h1>
            <div style="display: flex; gap: 8px;">
                <template x-if="!isCreating">
                    <button @click="openCreateForm" class="btn-freightx"><i class="fa fa-plus"></i> NEW CUSTOMER</button>
                </template>
                <template x-if="isCreating">
                    <div style="display: flex; gap: 8px;">
                        <button @click="savePartner" class="btn-freightx"><i class="fa fa-save"></i> SAVE PARTNER</button>
                        <button @click="isCreating = false" class="btn-default-gf">CANCEL</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Directory List View -->
        <div x-show="!isCreating" class="portlet light">
            <div class="portlet-title">
                <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                    <div style="position: relative; width: 300px;">
                        <input type="text" x-model="searchQuery" placeholder="Search Partners..." class="form-control-gf" style="padding-left: 25px;">
                        <i class="fa fa-search" style="position: absolute; left: 8px; top: 8px; color: #ccc; font-size: 10px;"></i>
                    </div>
                    <select class="form-control-gf" style="width: 120px;"><option>All Status</option><option>Enable</option><option>Disable</option></select>
                </div>
                <div>
                    <button class="btn-default-gf"><i class="fa fa-file-excel-o"></i> EXPORT</button>
                </div>
            </div>
            <div class="portlet-body" style="padding: 0;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Status</th>
                            <th style="width: 100px;">Code</th>
                            <th>Partner Name</th>
                            <th>Alias</th>
                            <th style="width: 150px;">Type</th>
                            <th style="width: 120px; text-align: right;">Credit Limit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="partner in filteredPartners" :key="partner.id">
                            <tr @click="editPartner(partner)">
                                <td style="text-align: center;">
                                    <span class="badge-gf" :class="partner.status === 'Enable' ? 'badge-enable' : 'badge-disable'" x-text="partner.status"></span>
                                </td>
                                <td style="color: #4b77be; font-weight: 700;" x-text="partner.code"></td>
                                <td style="font-weight: 700; color: #333;" x-text="partner.name"></td>
                                <td x-text="partner.alias"></td>
                                <td x-text="partner.tp_type"></td>
                                <td style="text-align: right; font-weight: 700; color: #d05454;">$ 50,000.00</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div style="padding: 10px; text-align: right; background: #f9fafb; font-size: 10px; color: #888; border-top: 1px solid #e7ecf1;">
                    Total Records Found: <span x-text="filteredPartners.length"></span>
                </div>
            </div>
        </div>

        <!-- Creation/Edit Form (Metronic Style Portlets) -->
        <div x-show="isCreating" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="space-y-5">
                <!-- General Info -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-user"></i> General Profile Information</span>
                    </div>
                    <div class="portlet-body">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                            <div>
                                <label class="form-label-gf">TP Type <span style="color:red;">*</span></label>
                                <select x-model="form.tp_type" class="form-control-gf">
                                    <option>Customer</option>
                                    <option>Oversea Agent</option>
                                    <option>Carrier</option>
                                    <option>Trucker</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-gf">Code <span style="color:red;">*</span></label>
                                <input type="text" x-model="form.code" class="form-control-gf" style="color: #4b77be; font-weight: 700;">
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label class="form-label-gf">Customer Name (Legal) <span style="color:red;">*</span></label>
                            <input type="text" x-model="form.name" class="form-control-gf" style="font-weight: 700;">
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div>
                                <label class="form-label-gf">Alias</label>
                                <input type="text" x-model="form.alias" class="form-control-gf">
                            </div>
                            <div>
                                <label class="form-label-gf">Local Name</label>
                                <input type="text" x-model="form.local_name" class="form-control-gf">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Info -->
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-map-marker"></i> Office & Accounting Address</span>
                    </div>
                    <div class="portlet-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <div style="margin-bottom: 10px;">
                                    <label class="form-label-gf">Physical Address</label>
                                    <textarea x-model="form.address" rows="3" class="form-control-gf" style="height: auto;"></textarea>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div>
                                        <label class="form-label-gf">City</label>
                                        <input type="text" class="form-control-gf">
                                    </div>
                                    <div>
                                        <label class="form-label-gf">Country</label>
                                        <input type="text" class="form-control-gf">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div style="margin-bottom: 10px;">
                                    <label class="form-label-gf">Accounting Address</label>
                                    <textarea x-model="form.accounting_address" rows="3" class="form-control-gf" style="height: auto; background: #fffcf5;"></textarea>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div>
                                        <label class="form-label-gf">Tax ID / USCI No.</label>
                                        <input type="text" class="form-control-gf">
                                    </div>
                                    <div>
                                        <label class="form-label-gf">Payment Term</label>
                                        <select class="form-control-gf"><option>NET 30</option><option>NET 15</option></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Panel -->
            <div class="space-y-5">
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-cog"></i> System Settings</span>
                    </div>
                    <div class="portlet-body">
                        <div style="margin-bottom: 12px;">
                            <label class="form-label-gf">Status</label>
                            <select x-model="form.status" class="form-control-gf" style="font-weight: 700; color: #4b77be;">
                                <option>Enable</option>
                                <option>Disable</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label class="form-label-gf">Sales Rep</label>
                            <input type="text" class="form-control-gf" placeholder="Search Rep...">
                        </div>
                        <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox"> <span style="font-size: 11px;">Track 1099 Payments</span>
                        </div>
                    </div>
                </div>

                <div class="portlet light" style="background: #f8f9fb;">
                    <div class="portlet-body" style="text-align: center; color: #8e9eae; padding: 30px;">
                        <i class="fa fa-info-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <p style="font-size: 11px;">Partner history and credit details will appear here after initial save.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tradePartnerModule() {
            return {
                isCreating: false,
                searchQuery: '',
                partners: [
                    { id: 1, name: 'Samsung Electronics Global', code: 'SAM-001', alias: 'Samsung', tp_type: 'Customer', status: 'Enable' },
                    { id: 2, name: 'Pacific Star Logistics', code: 'PSL-992', alias: 'PacStar', tp_type: 'Oversea Agent', status: 'Enable' },
                    { id: 3, name: 'DHL Global Forwarding', code: 'DHL-INT', alias: 'DHL', tp_type: 'Carrier', status: 'Enable' }
                ],
                form: {
                    tp_type: 'Customer', code: '', name: '', alias: '', local_name: '', scac_iata: '', firm_code: '',
                    contact: '', type: '', group: '', address: '', accounting_address: '', city: '', state: '',
                    tax_id: '', track_payments_1099: false, zip: '', country: '', status: 'Enable',
                    account_group_name: '', credit_limit_group_name: '', corporation_no: '', account_no: '',
                    eori: '', aeo: '', opening_hours: '', sales_office: '', op: '', sales: ''
                },
                get filteredPartners() {
                    if (!this.searchQuery) return this.partners;
                    const q = this.searchQuery.toLowerCase();
                    return this.partners.filter(p => 
                        p.name.toLowerCase().includes(q) || 
                        p.code.toLowerCase().includes(q) || 
                        p.tp_type.toLowerCase().includes(q)
                    );
                },
                openCreateForm() {
                    this.isCreating = true;
                    this.resetForm();
                },
                resetForm() {
                    Object.keys(this.form).forEach(k => {
                        if (typeof this.form[k] === 'boolean') this.form[k] = false;
                        else if (k === 'tp_type') this.form[k] = 'Customer';
                        else if (k === 'status') this.form[k] = 'Enable';
                        else this.form[k] = '';
                    });
                },
                savePartner() {
                    const newPartner = {
                        id: Date.now(),
                        name: this.form.name || 'New Customer',
                        code: this.form.code || 'TP-' + Math.floor(Math.random() * 1000),
                        alias: this.form.alias || this.form.name,
                        tp_type: this.form.tp_type,
                        status: this.form.status
                    };
                    this.partners.unshift(newPartner);
                    this.isCreating = false;
                },
                editPartner(partner) {
                    this.isCreating = true;
                    this.form.name = partner.name;
                    this.form.code = partner.code;
                    this.form.tp_type = partner.tp_type;
                    this.form.status = partner.status;
                }
            }
        }
    </script>
</x-layout>
