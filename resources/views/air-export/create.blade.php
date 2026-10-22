<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        .Tools-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: background 0.15s ease;
        }
        .Tools-menu-item:hover:not(:disabled) {
            background: #f3f4f6;
            color: #111827;
        }
        .Tools-menu-item.disabled, .Tools-menu-item:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .Tools-menu-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 4px 0;
        }
        .btn-filter {
            background: #fff;
            border: 1px solid #ddd;
            padding: 5px 14px;
            font-size: 12px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
            color: #4b77be;
            font-weight: 500;
        }
        .btn-filter:hover {
            background: #f5f5f5;
            border-color: #999;
        }
        .btn-filter-active {
            background: #337ab7;
            border: 1px solid #2e6da4;
            padding: 5px 14px;
            font-size: 12px;
            border-radius: 3px;
            cursor: pointer;
            color: #fff;
            font-weight: 600;
        }
        /* Override form-styles 140px min-height for compact memo/remark textareas */
        textarea.form-control-gf {
            min-height: unset !important;
            height: auto;
            line-height: 1.4;
        }
        .textarea-compact {
            height: 150px !important;
            min-height: 150px !important;
            max-height: 350px;
            resize: vertical;
        }
        .textarea-compact-sm {
            height: 100px !important;
            min-height: 100px !important;
            max-height: 250px;
            resize: vertical;
        }
    </style>
    @endpush

    <script>
        window.airExportModule = function() {
            return {
                showQuoteModal: false,
                quoteStep: 1,
                selectedQuote: null,

                // Quotation table column configuration
                showQuoteConfig: false,
                colVisibility: {
                    select: true,
                    quote_no: true,
                    valid_date: true,
                    status: true,
                    creation_date: true,
                    commodity: true,
                    pol: true,
                    pod: true,
                    carrier: true,
                    sales: true,
                    op: true
                },

                selectQuote(data) {
                    this.selectedQuote = data;
                    this.quoteForm = data;
                },
                filters: {
                    customer: '',
                    pol: '',
                    quote_no: '',
                    valid_date: '',
                    pod: '',
                    status: '',
                    commodity: '',
                    sales: '',
                    op: ''
                },
                searchFilters: {
                    customer: '',
                    pol: '',
                    quote_no: '',
                    valid_date: '',
                    pod: '',
                    status: '',
                    commodity: '',
                    sales: '',
                    op: ''
                },
                applySearch() {
                    this.searchFilters = { ...this.filters };
                },
                clearSearch() {
                    this.filters = {
                        customer: '',
                        pol: '',
                        quote_no: '',
                        valid_date: '',
                        pod: '',
                        status: '',
                        commodity: '',
                        sales: '',
                        op: ''
                    };
                    this.searchFilters = { ...this.filters };
                },
                matchFilters(quote) {
                    if (this.searchFilters.quote_no && !quote.quote_no.toLowerCase().includes(this.searchFilters.quote_no.toLowerCase())) return false;
                    if (this.searchFilters.customer && quote.customer_id != this.searchFilters.customer) return false;
                    if (this.searchFilters.pol && quote.pol_id != this.searchFilters.pol) return false;
                    if (this.searchFilters.pod && quote.pod_id != this.searchFilters.pod) return false;
                    if (this.searchFilters.status && quote.status.toUpperCase() !== this.searchFilters.status.toUpperCase()) return false;
                    if (this.searchFilters.sales && quote.sales_person_id != this.searchFilters.sales) return false;
                    if (this.searchFilters.op && quote.sales_person_id != this.searchFilters.op) return false;
                    return true;
                },
                quoteForm: {
                    mawb_no: '',
                    hawb_no: '',
                    eta: '',
                    etd: '',
                    customer: '',
                    sales: ''
                },
                quoteItems: {
                    @foreach($quotations as $q)
                    "{{ $q->quote_no }}": {!! json_encode($q->items->map(fn($i) => [
                        'id' => $i->id,
                        'charge_code' => $i->charge_code,
                        'charge_name' => $i->charge_name,
                        'qty' => (float)$i->qty,
                        'unit' => $i->unit,
                        'currency' => $i->currency ? ['code' => $i->currency->code] : ['code' => 'USD'],
                        'rate' => (float)$i->rate,
                        'amount' => (float)$i->amount,
                    ])->values()->toArray()) !!},
                    @endforeach
                },
                activeTab: 'basic',
                saved: {{ isset($airExport) && $airExport->id ? 'true' : 'false' }},
                showMblSection: true,
                showMblMemo: false,
                isDirectMaster: {{ isset($airExport) && ($airExport->is_direct_master || $airExport->dm_customer_id) ? 'true' : 'false' }},
                showMore: false,
                showConnectingFlight: false,
                hawbs: [],
                showDimensionsModal: false,
                dimensionTarget: 'mawb',
                dimensionUnit: 'CM',
                dimensionRows: [
                    { selected: false, length: '', width: '', height: '', pcs: 1 }
                ],
                
                form: {
                    id: '{{ isset($airExport) ? $airExport->id : "" }}',
                    file_no: '{{ isset($airExport) ? $airExport->file_no : "MAE-" . date("YmdHis") }}',
                    mawb_no: '{{ isset($airExport) ? $airExport->mawb_no : "" }}',
                    office: '{{ isset($airExport) ? $airExport->office_id : "" }}',
                    carrier: '{{ isset($airExport) ? $airExport->carrier_id : "" }}',
                    issuing_carrier: '{{ isset($airExport) && $airExport->issuing_carrier ? $airExport->issuing_carrier : "FREIGHTX" }}',
                    awb_type: '{{ isset($airExport) && $airExport->awb_type ? $airExport->awb_type : "NORMAL" }}',
                    awb_date: '{{ isset($airExport) && $airExport->awb_date ? \Carbon\Carbon::parse($airExport->awb_date)->format("Y-m-d") : "" }}',
                    shipper: '{{ isset($airExport) ? $airExport->shipper_id : "" }}',
                    consignee: '{{ isset($airExport) ? $airExport->consignee_id : "" }}',
                    notify: '{{ isset($airExport) ? $airExport->notify_id : "" }}',
                    post_date: '{{ isset($airExport) && $airExport->post_date ? \Carbon\Carbon::parse($airExport->post_date)->format("Y-m-d") : date("Y-m-d") }}',
                    awb_acct_carrier: '{{ isset($airExport) ? $airExport->acct_carrier_id : "" }}',
                    co_loader: '{{ isset($airExport) ? $airExport->forwarding_agent_id : "" }}',
                    actual_shipper: '{{ isset($airExport) ? $airExport->actual_shipper_id : "" }}',
                    op: '{{ isset($airExport) ? $airExport->op_id : auth()->id() }}',
                    itn_no: '{{ isset($airExport) ? $airExport->itn_no : "" }}',
                    cers_no: '{{ isset($airExport) ? $airExport->cers_no : "" }}',
                    reference_no: '{{ isset($airExport) ? $airExport->reference_no : "" }}',
                    display_unit: '{{ isset($airExport) && $airExport->display_unit ? $airExport->display_unit : "BOTH" }}',
                    departure: '{{ isset($airExport) ? $airExport->dep_port_id : "" }}',
                    destination: '{{ isset($airExport) ? $airExport->dst_port_id : "" }}',
                    flight_no: '{{ isset($airExport) ? $airExport->flight_no : "" }}',
                    cargo_ready_date: '{{ isset($airExport) && $airExport->cargo_ready_date ? \Carbon\Carbon::parse($airExport->cargo_ready_date)->format("Y-m-d") : "" }}',
                    etd: '{{ isset($airExport) && $airExport->etd ? \Carbon\Carbon::parse($airExport->etd)->format("Y-m-d") : "" }}',
                    atd: '{{ isset($airExport) && $airExport->atd ? \Carbon\Carbon::parse($airExport->atd)->format("Y-m-d") : "" }}',
                    eta: '{{ isset($airExport) && $airExport->eta ? \Carbon\Carbon::parse($airExport->eta)->format("Y-m-d") : "" }}',
                    ata: '{{ isset($airExport) && $airExport->ata ? \Carbon\Carbon::parse($airExport->ata)->format("Y-m-d") : "" }}',
                    dv_carriage: '{{ isset($airExport) && $airExport->dv_carriage ? $airExport->dv_carriage : "N.V.D." }}',
                    dv_customs: '{{ isset($airExport) && $airExport->dv_customs ? $airExport->dv_customs : "N.C.V." }}',
                    insurance: '{{ isset($airExport) && $airExport->insurance ? $airExport->insurance : "N.I.L." }}',
                    wt_val: '{{ isset($airExport) && $airExport->wt_val ? $airExport->wt_val : "P" }}',
                    other_term: '{{ isset($airExport) && $airExport->other_term ? $airExport->other_term : "P" }}',
                    pkg_qty: '{{ isset($airExport) ? $airExport->pkg_qty : "" }}',
                    pkg_unit_id: '{{ isset($airExport) ? $airExport->pkg_unit_id : "" }}',
                    gross_weight: '{{ isset($airExport) ? $airExport->gross_weight : "" }}',
                    chargeable_weight: '{{ isset($airExport) ? $airExport->chargeable_weight : "" }}',
                    volume: '{{ isset($airExport) ? $airExport->volume : "" }}',
                    buying_rate: '{{ isset($airExport) ? $airExport->buying_rate : "" }}',
                    selling_rate: '{{ isset($airExport) ? $airExport->selling_rate : "" }}',
                    sales_type: '{{ isset($airExport) ? $airExport->sales_type : "" }}',
                    internal_remark: '{{ isset($airExport) ? $airExport->internal_remark : "" }}',
                    dm_customer_id: '{{ isset($airExport) ? $airExport->dm_customer_id : "" }}',
                    dm_sales_person_id: '{{ isset($airExport) ? $airExport->dm_sales_person_id : "" }}',
                    dm_shipper_id: '{{ isset($airExport) ? $airExport->dm_shipper_id : "" }}',
                    dm_bill_to_id: '{{ isset($airExport) ? $airExport->dm_bill_to_id : "" }}',
                    dm_consignee_id: '{{ isset($airExport) ? $airExport->dm_consignee_id : "" }}',
                    dm_notify_id: '{{ isset($airExport) ? $airExport->dm_notify_id : "" }}',
                    cargo_type: '{{ isset($airExport) && $airExport->cargo_type ? $airExport->cargo_type : "GENERAL CARGO" }}',
                    routes: @json(isset($airExport) && !empty($airExport->routes) ? (is_array($airExport->routes) ? $airExport->routes : json_decode($airExport->routes, true)) : []),
                    other_charges: @json(isset($airExport) && !empty($airExport->other_charges) ? (is_array($airExport->other_charges) ? $airExport->other_charges : json_decode($airExport->other_charges, true)) : []),
                    charges: @json($chargesData ?? []),
                    accounting_info: @json(isset($airExport) && !empty($airExport->accounting_info) ? (is_array($airExport->accounting_info) ? $airExport->accounting_info : json_decode($airExport->accounting_info, true)) : []),
                    commodities: @json(isset($airExport) && !empty($airExport->commodities) ? (is_array($airExport->commodities) ? $airExport->commodities : json_decode($airExport->commodities, true)) : []),
                    memo: '{{ isset($airExport) ? $airExport->internal_remark : "" }}'
                },
                addRoute() {
                    if (!this.form.routes || !Array.isArray(this.form.routes)) {
                        this.form.routes = [];
                    }
                    this.form.routes.push({
                        airport_id: '',
                        eta: '',
                        ata: '',
                        etd: '',
                        atd: '',
                        flight_no: '',
                        carrier_id: ''
                    });
                },
                removeRoute(index) {
                    if (this.form.routes && this.form.routes[index] !== undefined) {
                        this.form.routes.splice(index, 1);
                    }
                },
                activeChargeFilter: 'All',
                showInvoiceModal: false,
                invoiceModalData: {
                    fin_date: new Date().toISOString().split('T')[0],
                    due_date: new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0],
                    currency: 'USD',
                    tax: false,
                    internal_ref: '',
                    dec: '2',
                    type: 'INV',
                    manif_amt: 0,
                    loc_amt: 0,
                    cr_days: 30,
                    cr_limit: 0,
                    bal_limit: 0,
                    group: true,
                    party_info: '',
                    remarks: '',
                    bank_details: '',
                    charges_count: 0,
                    selected_ids: [],
                    progress: 0,
                    attachment_name: '',
                    file: null
                },
                chargesList: @json($chargesData ?? []),

                calculateTotalCharges() {
                    if (!this.chargesList || this.chargesList.length === 0) return 0;
                    return this.chargesList.reduce((sum, charge) => {
                        return sum + this.calculateLocalAmount(charge);
                    }, 0);
                },

                calculateArCharges() {
                    if (!this.chargesList || this.chargesList.length === 0) return 0;
                    return this.chargesList.filter(c => c.pr === 'Rec').reduce((sum, charge) => {
                        return sum + this.calculateLocalAmount(charge);
                    }, 0);
                },

                calculateApCharges() {
                    if (!this.chargesList || this.chargesList.length === 0) return 0;
                    return this.chargesList.filter(c => c.pr === 'Pay').reduce((sum, charge) => {
                        return sum + this.calculateLocalAmount(charge);
                    }, 0);
                },

                calculateLocalAmount(charge) {
                    let foreignAmount = (parseFloat(charge.rate) || 0) * (parseFloat(charge.qty) || 0);
                    let localAmount = foreignAmount * (parseFloat(charge.roe) || 1);
                    if (charge.vat && parseFloat(charge.vat) > 0) {
                        localAmount = localAmount + (localAmount * (parseFloat(charge.vat) / 100));
                    }
                    return localAmount;
                },

                updateChargeAmount(idx) {
                    if (this.$forceUpdate) this.$forceUpdate();
                },

                updateLocalAmount(idx) {
                    if (this.$forceUpdate) this.$forceUpdate();
                },

                saveCharges() {
                    const formEl = document.querySelector('form[action*="air-export"]');
                    if (formEl) {
                        showToast('info', 'Saving charges...');
                        formEl.submit();
                    } else {
                        showToast('error', 'Form not found.');
                    }
                },

                openCertificateModal() {
                    showToast('info', 'Certificate functionality - coming soon');
                },

                toggleAllCharges(e) {
                    const isChecked = e ? e.target.checked : false;
                    this.chargesList.forEach(c => {
                        c._selected = isChecked;
                        c.selected = isChecked;
                    });
                },

                setDefaultCharges() {
                    this.chargesList = [
                        { id: null, _selected: false, selected: false, party: 'Freight', party_name: '', party_name_id: '', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'OFC', currency: 'USD', rate: 50, qty: 1, qty_type: 'B/L', roe: 1.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: '', mbl_no: this.form.file_no || '' },
                        { id: null, _selected: false, selected: false, party: 'THC', party_name: '', party_name_id: '', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'THC', currency: 'USD', rate: 10, qty: 1, qty_type: 'KG', roe: 1.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: '', mbl_no: this.form.file_no || '' }
                    ];
                    showToast('success', 'Loaded default charges.');
                },

                reloadCharges() {
                    if (confirm('Discard changes and reload charges from database?')) {
                        window.location.reload();
                    }
                },

                getChargeFilters() {
                    let list = [{ name: 'All', value: 'All' }];
                    if (this.form.file_no) {
                        list.push({ name: 'MAWB: ' + this.form.file_no, value: this.form.file_no });
                    }
                    if (this.hawbs && this.hawbs.length > 0) {
                        this.hawbs.forEach(h => {
                            if (h.hawb_no) {
                                list.push({ name: 'HAWB: ' + h.hawb_no, value: h.hawb_no });
                            }
                        });
                    }
                    return list;
                },

                shouldShowChargeRow(charge) {
                    if (this.activeChargeFilter === 'All') return true;
                    let filterVal = this.activeChargeFilter.trim().toLowerCase();
                    let mbl = (charge.mbl_no || '').trim().toLowerCase();
                    let eqBl = (charge.eq_bl_no || '').trim().toLowerCase();
                    return mbl === filterVal || eqBl === filterVal;
                },

                addNewCharge() {
                    let defaultEqBl = '';
                    let defaultMbl = '';
                    if (this.activeChargeFilter && this.activeChargeFilter !== 'All') {
                        if (this.activeChargeFilter === this.form.file_no) {
                            defaultMbl = this.form.file_no;
                        } else {
                            defaultEqBl = this.activeChargeFilter;
                        }
                    }
                    this.chargesList.push({
                        id: null,
                        _selected: false,
                        selected: false,
                        party: 'Freight',
                        party_name: '',
                        party_name_id: '',
                        sal: 'Air',
                        pr: 'Rec',
                        ppc: 'Colle',
                        chrg_code: '',
                        currency: 'USD',
                        rate: 0,
                        qty: 1,
                        qty_type: 'B/L',
                        roe: 1.0,
                        vat: 0,
                        inv_no: '',
                        financial_date: new Date().toISOString().split('T')[0],
                        eq_bl_no: defaultEqBl,
                        remark: '',
                        mbl_no: defaultMbl
                    });
                },

                removeCharge(idx) {
                    this.chargesList.splice(idx, 1);
                },

                applyTemplate() {
                    if (!this.form.id) { showToast('error', 'Please save shipment first.'); return; }
                    if (!confirm('Are you sure you want to load default template charges?')) return;
                    fetch(`/air-export/${this.form.id}/charges/template`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            showToast('error', 'Failed to apply template.');
                        }
                    });
                },

                createInvoice() {
                    if (!this.form.id) {
                        showToast('error', 'Please save shipment first.');
                        return;
                    }
                    
                    let selectedCharges = this.chargesList.filter(c => c._selected || c.selected);
                    if (selectedCharges.length === 0) {
                        showToast('error', 'Please select at least one charge using checkbox.');
                        return;
                    }

                    // 1. Check for unsaved charges (new rows that haven't been saved yet)
                    let unsavedCharges = selectedCharges.filter(c => !c.id);
                    if (unsavedCharges.length > 0) {
                        showToast('warning', 'You have ' + unsavedCharges.length + ' unsaved charge(s). Please click "Save" first before generating an invoice.');
                        return;
                    }

                    // 2. Check for already invoiced charges
                    let alreadyInvoiced = selectedCharges.filter(c => c.is_invoiced || (c.inv_no && c.inv_no.trim() !== '' && c.inv_no !== '-'));
                    let uninvoicedCharges = selectedCharges.filter(c => !c.is_invoiced && (!c.inv_no || c.inv_no.trim() === '' || c.inv_no === '-'));

                    if (uninvoicedCharges.length === 0) {
                        let invNos = alreadyInvoiced.map(c => c.inv_no || 'Invoiced').filter((v, i, a) => a.indexOf(v) === i).join(', ');
                        showToast('warning', 'Selected charge(s) are already invoiced (' + invNos + ').');
                        return;
                    }

                    if (alreadyInvoiced.length > 0) {
                        let invNos = alreadyInvoiced.map(c => c.inv_no || 'Invoiced').filter((v, i, a) => a.indexOf(v) === i).join(', ');
                        showToast('info', 'Skipping already invoiced charge(s): ' + invNos);
                    }

                    let selectedIds = uninvoicedCharges.map(c => c.id);

                    // Compute manifest amount (foreign) and local amount
                    let foreignAmt = 0;
                    let localAmt = 0;
                    let firstCurrency = 'USD';
                    let firstParty = '';

                    uninvoicedCharges.forEach(c => {
                        foreignAmt += parseFloat(c.f_amount || c.amount || 0);
                        localAmt += this.calculateLocalAmount(c);
                        if (c.currency) firstCurrency = c.currency;
                        if (!firstParty && c.party_name) firstParty = c.party_name;
                    });

                    // Prepare party address / info
                    let partyDisplay = firstParty || (this.hawbs && this.hawbs.length > 0 ? this.hawbs[0].customer_name : '') || 'General Customer';
                    if (this.form.file_no) {
                        partyDisplay += "\nRef: " + this.form.file_no;
                    }

                    this.invoiceModalData.selected_ids = selectedIds;
                    this.invoiceModalData.charges_count = selectedIds.length;
                    this.invoiceModalData.manif_amt = foreignAmt;
                    this.invoiceModalData.loc_amt = localAmt;
                    this.invoiceModalData.currency = firstCurrency;
                    this.invoiceModalData.party_info = partyDisplay;
                    this.invoiceModalData.fin_date = new Date().toISOString().split('T')[0];
                    this.invoiceModalData.due_date = new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0];
                    this.invoiceModalData.progress = 0;
                    this.invoiceModalData.attachment_name = '';
                    this.invoiceModalData.file = null;

                    this.showInvoiceModal = true;
                },

                processInvoiceModal() {
                    if (!this.invoiceModalData.selected_ids || this.invoiceModalData.selected_ids.length === 0) {
                        showToast('error', 'No charges selected.');
                        return;
                    }

                    this.invoiceModalData.progress = 30;
                    showToast('info', 'Generating invoice...');

                    fetch(`/air-export/${this.form.id}/charges/invoice`, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            ids: this.invoiceModalData.selected_ids,
                            fin_date: this.invoiceModalData.fin_date,
                            due_date: this.invoiceModalData.due_date,
                            internal_ref: this.invoiceModalData.internal_ref,
                            type: this.invoiceModalData.type,
                            remarks: this.invoiceModalData.remarks,
                            bank_details: this.invoiceModalData.bank_details
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.invoiceModalData.progress = 100;
                        if (data.success) {
                            showToast('success', 'Invoice generated successfully: ' + (data.invoice_no || ''));
                            this.showInvoiceModal = false;
                            setTimeout(() => { window.location.reload(); }, 600);
                        } else {
                            showToast('error', 'Failed to create invoice: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('error', 'Failed to create invoice.');
                    });
                },

                previewInvoiceModal() {
                    if (!this.form.id) {
                        showToast('error', 'Please save shipment first.');
                        return;
                    }
                    window.open(`/shipments/air-export/${this.form.id}/freight-invoice`, '_blank');
                },

                handleInvoiceModalFile(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.invoiceModalData.file = file;
                        if (!this.invoiceModalData.attachment_name) {
                            this.invoiceModalData.attachment_name = file.name;
                        }
                        this.invoiceModalData.progress = 10;
                    }
                },

                uploadInvoiceModalFile() {
                    if (!this.invoiceModalData.file) {
                        showToast('info', 'Please select a file to upload first.');
                        return;
                    }
                    this.invoiceModalData.progress = 60;
                    showToast('success', 'File ' + this.invoiceModalData.attachment_name + ' attached.');
                    setTimeout(() => {
                        this.invoiceModalData.progress = 100;
                    }, 400);
                },

                prorataCharges() {
                    if (!this.form.id) { showToast('error', 'Please save shipment first.'); return; }
                    let chargeId = prompt('Enter Charge ID to prorate:');
                    if (!chargeId) return;
                    let basis = prompt('Enter prorate basis (volume / weight):', 'volume');
                    if (basis !== 'volume' && basis !== 'weight') {
                        showToast('error', 'Invalid basis.');
                        return;
                    }
                    fetch(`/air-export/${this.form.id}/charges/prorata`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ charge_id: chargeId, basis: basis })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            showToast('error', 'Failed to prorate charges: ' + data.message);
                        }
                    });
                },

                exportChargesToExcel() {
                    if (!this.form.id) { showToast('error', 'Please save shipment first.'); return; }
                    window.location.href = `/air-export/${this.form.id}/charges/export`;
                },

                printCharges() {
                    if (!this.form.id) { showToast('error', 'Please save shipment first.'); return; }
                    window.open(`/air-export/${this.form.id}/charges/print`, '_blank');
                },

                deleteAllCharges() {
                    if (!confirm('Are you sure you want to delete all charges?')) return;
                    if (this.form.id) {
                        fetch(`/air-export/${this.form.id}/charges/all`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.chargesList = [];
                                showToast('success', 'All charges deleted successfully.');
                            } else {
                                showToast('error', 'Failed to delete charges.');
                            }
                        });
                    } else {
                        this.chargesList = [];
                    }
                },

                duplicateSelectedCharges() {
                    if (!this.form.id) { showToast('error', 'Please save shipment first.'); return; }
                    let ids = this.chargesList.filter(c => c._selected || c.selected).map(c => c.id);
                    if (ids.length === 0) {
                        let chargeId = prompt('Enter charge ID to duplicate:');
                        if (chargeId) ids = [chargeId];
                        else {
                            showToast('error', 'Please select charges using checkbox or enter charge ID.');
                            return;
                        }
                    }
                    fetch(`/air-export/${this.form.id}/charges/duplicate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            showToast('error', 'Failed to duplicate charges.');
                        }
                    });
                },

                bulkUpdateCurrency() {
                    let newCurrency = prompt('Enter new currency (USD/BDT/EUR/GBP):', 'USD');
                    if (newCurrency && ['USD','BDT','EUR','GBP'].includes(newCurrency.toUpperCase())) {
                        if (this.form.id) {
                            fetch(`/air-export/${this.form.id}/charges/bulk-currency`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ currency: newCurrency.toUpperCase() })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    this.chargesList.forEach(charge => charge.currency = newCurrency.toUpperCase());
                                    showToast('success', 'Currency updated successfully!');
                                } else {
                                    showToast('error', 'Failed to update currency.');
                                }
                            });
                        } else {
                            this.chargesList.forEach(charge => charge.currency = newCurrency.toUpperCase());
                            showToast('success', 'Currency updated successfully!');
                        }
                    }
                },

                applyVatToAll() {
                    let vatPercent = prompt('Enter VAT percentage to apply to all charges (e.g., 15):', '0');
                    if (vatPercent !== null && !isNaN(parseFloat(vatPercent))) {
                        if (this.form.id) {
                            fetch(`/air-export/${this.form.id}/charges/apply-vat`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ vat: parseFloat(vatPercent) })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    this.chargesList.forEach(charge => charge.vat = parseFloat(vatPercent));
                                    showToast('success', 'VAT ' + vatPercent + '% applied to all charges!');
                                } else {
                                    showToast('error', 'Failed to apply VAT.');
                                }
                            });
                        } else {
                            this.chargesList.forEach(charge => charge.vat = parseFloat(vatPercent));
                            showToast('success', 'VAT ' + vatPercent + '% applied to all charges!');
                        }
                    }
                },
                addCharge() {
                    this.form.other_charges.push({
                        charge_code: '',
                        description: '',
                        term: 'P',
                        rate: '',
                        amount: ''
                    });
                },
                removeCharge(idx) { this.form.other_charges.splice(idx, 1); },
                addAcctInfo() { this.form.accounting_info.push({ code: '', info: '' }); },
                removeAcctInfo(idx) { this.form.accounting_info.splice(idx, 1); },
                addCommodity() { this.form.commodities.push({ description: '', hts_no: '' }); },
                removeCommodity(idx) { this.form.commodities.splice(idx, 1); },
                addHawbCommodity(hawbIdx) { this.hawbs[hawbIdx].commodities.push({ description: '', hts_no: '', po_no: '' }); },
                removeHawbCommodity(hawbIdx, idx) { this.hawbs[hawbIdx].commodities.splice(idx, 1); },
                addHawb() {
                    this.hawbs.push({
                        show: true,
                        showMemo: false,
                        showMore: false,
                        hawb_no: '',
                        booking_no: '',
                        booking_date: '',
                        quotation_no: '',
                        shipper: '',
                        customer: '',
                        bill_to: '',
                        consignee: '',
                        notify: '',
                        oversea_agent: '',
                        issuing_carrier: 'FREIGHTX',
                        trucker: '',
                        sales: '',
                        op: this.form.op,
                        itn_no: '',
                        display_unit: 'BOTH',
                        departure: '',
                        destination: '',
                        cargo_pickup: '',
                        delivery_to: '',
                        cargo_type: 'GENERAL CARGO',
                        sales_type: '',
                        ship_type: 'NORMAL',
                        feta: '',
                        dv_carriage: 'N.V.D.',
                        dv_customs: 'N.C.V.',
                        freight_term: 'P',
                        other_charge_term: 'P',
                        insurance: 'N.I.L.',
                        cargo_ready_date: '',
                        pkg_qty: '',
                        pkg_unit_id: '',
                        gross_weight: '',
                        chargeable_weight: '',
                        volume: '',
                        buying_rate: '',
                        selling_rate: '',
                        commodity: '',
                        incoterms_id: '',
                        hbl_remark: '',
                        packages: [],
                        commodities: [],
                        mark: '',
                        description: '',
                        remark: '',
                        memo: ''
                    });
                    this.showMblSection = false;
                },
                removeHawb(idx) {
                    if(confirm('Are you sure you want to delete this HAWB?')) {
                        this.hawbs.splice(idx, 1);
                        if(this.hawbs.length === 0) this.showMblSection = true;
                    }
                },
                printHawb(idx) {
                    const shipmentId = this.form.id;
                    if (!shipmentId) {
                        showToast('error', 'Please save the Air Export shipment first before printing HAWB.');
                        return;
                    }
                    window.open('/air-export/' + shipmentId + '/hawb-print/' + idx, '_blank');
                },
                hawbBlockAction(idx) {
                    const hawb = this.hawbs[idx];
                    if (!hawb || !hawb.id) { showToast('warning', 'Save the HAWB first.'); return; }
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const endpoint = hawb.is_blocked ? '/air-export/hbl-bulk-unblock' : '/air-export/hbl-bulk-block';
                    fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ ids: [hawb.id] })
                    }).then(r => r.json()).then(d => {
                        if (d.success) {
                            hawb.is_blocked = !hawb.is_blocked;
                            showToast('success', hawb.is_blocked ? 'HAWB blocked.' : 'HAWB unblocked.');
                        } else {
                            showToast('error', d.message || 'Action failed.');
                        }
                    }).catch(() => showToast('error', 'Request failed.'));
                },
                hawbCopyAction(idx) {
                    const shipmentId = this.form.id;
                    const hawb = this.hawbs[idx];
                    if (!shipmentId || !hawb || !hawb.id) { showToast('warning', 'Save the shipment and HAWB first.'); return; }
                    showToast('info', 'HAWB Copy - feature coming soon.');
                },
                saveShipment() {
                    document.getElementById('airExportForm').submit();
                },
                showAiModal: false,
                aiPreviewMode: false,
                isAiProcessing: false,
                documentPreviewUrl: '',
                aiError: '',
                extractedData: [],

                // --- AI Auto-Fill Logic ---
                handleAiUpload(e) {
                    if (!e.target.files.length) return;
                    
                    const file = e.target.files[0];
                    const formData = new FormData();
                    formData.append('document', file);
                    
                    if (this.documentPreviewUrl) {
                        URL.revokeObjectURL(this.documentPreviewUrl);
                    }
                    this.documentPreviewUrl = URL.createObjectURL(file);
                    
                    this.isAiProcessing = true;
                    this.aiPreviewMode = true;
                    this.extractedData = []; // Clear old data
                    this.aiError = '';

                    fetch('/api/ai/extract/air-export', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async res => {
                        const contentType = res.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            return res.json();
                        }
                        const text = await res.text();
                        return { success: false, message: 'Server returned non-JSON response (' + res.status + ')' };
                    })
                    .then(data => {
                        this.isAiProcessing = false;
                        if (!data.success) {
                            this.aiError = data.message;
                            return;
                        }
                        
                        this.extractedData = [];
                        
                        // Parse MBL fields
                        if (data.data.mbl) {
                            const m = data.data.mbl;
                            const mblMap = [
                                { label: 'MAWB No.', key: 'mawb_no', val: m.mawb_no || m.mbl_no },
                                { label: 'Booking no', key: 'file_no', val: m.file_no || m.booking_no },
                                { label: 'AWB Date', key: 'awb_date', val: m.awb_date },
                                { label: 'Post Date', key: 'post_date', val: m.post_date },
                                { label: 'Carrier', key: 'carrier_id', val: m.carrier_id, display: m.carrier_name, nameKey: 'carrier_name' },
                                { label: 'Issuing Carrier', key: 'issuing_carrier', val: m.issuing_carrier || m.carrier_name },
                                { label: 'Flight No.', key: 'flight_no', val: m.flight_no },
                                { label: 'Port of Departure', key: 'departure_id', val: m.departure_id || m.pol_id, display: m.departure_name || m.pol_name, nameKey: 'departure_name' },
                                { label: 'Port of Destination', key: 'destination_id', val: m.destination_id || m.pod_id, display: m.destination_name || m.pod_name, nameKey: 'destination_name' },
                                { label: 'Forwarding Agent / Co-loader', key: 'forwarding_agent_id', val: m.forwarding_agent_id || m.co_loader_id, display: m.forwarding_agent_name || m.co_loader_name, nameKey: 'forwarding_agent_name' },
                                { label: 'Oversea Agent', key: 'oversea_agent_id', val: m.oversea_agent_id, display: m.oversea_agent_name, nameKey: 'oversea_agent_name' },
                                { label: 'Actual Shipper', key: 'actual_shipper_id', val: m.actual_shipper_id, display: m.actual_shipper_name, nameKey: 'actual_shipper_name' },
                                { label: 'Operator', key: 'op_id', val: m.op_id, display: m.op_name, nameKey: 'op_name' },
                                { label: 'Sales Person', key: 'sales_person_id', val: m.sales_person_id, display: m.sales_person_name, nameKey: 'sales_person_name' },
                                { label: 'Shipper', key: 'shipper_id', val: m.shipper_id, display: m.shipper_name, nameKey: 'shipper_name' },
                                { label: 'Consignee', key: 'consignee_id', val: m.consignee_id, display: m.consignee_name, nameKey: 'consignee_name' },
                                { label: 'Notify Party', key: 'notify_id', val: m.notify_id, display: m.notify_party_name, nameKey: 'notify_party_name' },
                                { label: 'Total Packages', key: 'pkg_qty', val: m.pkg_qty || m.pieces },
                                { label: 'Gross Weight (KG)', key: 'gross_weight', val: m.gross_weight },
                                { label: 'Chargeable Weight (KG)', key: 'chargeable_weight', val: m.chargeable_weight },
                                { label: 'Volume (CBM)', key: 'volume', val: m.volume },
                                { label: 'D.V. Carriage', key: 'dv_carriage', val: m.dv_carriage },
                                { label: 'D.V. Customs', key: 'dv_customs', val: m.dv_customs },
                                { label: 'WT/VAL', key: 'wt_val', val: m.wt_val },
                                { label: 'Other Term', key: 'other_term', val: m.other_term },
                                { label: 'Insurance', key: 'insurance', val: m.insurance },
                                { label: 'Cargo Description', key: 'description', val: m.description },
                                { label: 'ETA', key: 'eta', val: m.eta },
                                { label: 'ETD', key: 'etd', val: m.etd },
                            ];
                            
                            mblMap.forEach(field => {
                                if (field.val || field.display) {
                                    this.extractedData.push({
                                        label: field.label,
                                        parent: 'mbl',
                                        key: field.key,
                                        value: field.val,
                                        displayValue: field.display || field.val,
                                        nameKey: field.nameKey || null,
                                        selected: true
                                    });
                                }
                            });
                        }
                        
                        // Parse HBL fields
                        const hblList = data.data.hbls || [];
                        if (hblList.length > 0) {
                            hblList.forEach((h, hIdx) => {
                                const hPrefix = hblList.length > 1 ? `HAWB ${hIdx+1} - ` : '';
                                const hblMap = [
                                    { label: `${hPrefix}HAWB No.`, key: 'hawb_no', val: h.hawb_no || h.hbl_no },
                                    { label: `${hPrefix}Shipper`, key: 'shipper_id', val: h.shipper_id, display: h.shipper_name, nameKey: 'shipper_name' },
                                    { label: `${hPrefix}Consignee`, key: 'consignee_id', val: h.consignee_id, display: h.consignee_name, nameKey: 'consignee_name' },
                                    { label: `${hPrefix}Notify Party`, key: 'notify_id', val: h.notify_id, display: h.notify_party_name, nameKey: 'notify_party_name' },
                                    { label: `${hPrefix}Gross Weight`, key: 'gross_weight', val: h.gross_weight || h.weight },
                                    { label: `${hPrefix}Chargeable Weight`, key: 'chargeable_weight', val: h.chargeable_weight },
                                    { label: `${hPrefix}Volume Weight`, key: 'volume', val: h.volume_weight || h.volume },
                                    { label: `${hPrefix}Pieces`, key: 'pkg_qty', val: h.pieces || h.pkg_qty },
                                    { label: `${hPrefix}Departure`, key: 'departure', val: h.departure_id || h.departure_name, display: h.departure_name },
                                    { label: `${hPrefix}Destination`, key: 'destination', val: h.destination_id || h.destination_name, display: h.destination_name },
                                    { label: `${hPrefix}Nature of Goods`, key: 'description', val: h.description },
                                ];
                                
                                hblMap.forEach(field => {
                                    if (field.val || field.display) {
                                        this.extractedData.push({
                                            label: field.label,
                                            parent: 'hbl',
                                            hIndex: hIdx,
                                            key: field.key,
                                            value: field.val,
                                            displayValue: field.display || field.val,
                                            nameKey: field.nameKey || null,
                                            selected: true
                                        });
                                    }
                                });
                            });
                        }
                        
                        // Parse Routes / Flights
                        const routeList = data.data.routes || data.data.flights || [];
                        if (routeList.length > 0) {
                            routeList.forEach((r, idx) => {
                                const legLabel = `Flight Leg ${idx+1}`;
                                const polVal = r.departure_name || r.pol_name;
                                const polId = r.departure_id || r.pol_id;
                                const podVal = r.destination_name || r.pod_name;
                                const podId = r.destination_id || r.pod_id;
                                const carrierVal = r.carrier_name;
                                const carrierId = r.carrier_id;

                                const fields = [
                                    { label: `${legLabel} - Flight No`, key: 'flight_no', val: r.flight_no },
                                    { label: `${legLabel} - Carrier`, key: 'carrier_name', val: carrierVal, nameKey: 'carrier_id', idVal: carrierId },
                                    { label: `${legLabel} - Departure (POL)`, key: 'pol_name', val: polVal, nameKey: 'pol_id', idVal: polId },
                                    { label: `${legLabel} - Destination (POD)`, key: 'pod_name', val: podVal, nameKey: 'pod_id', idVal: podId },
                                    { label: `${legLabel} - ETD`, key: 'etd', val: r.etd },
                                    { label: `${legLabel} - ETA`, key: 'eta', val: r.eta }
                                ];
                                fields.forEach(f => {
                                    if (f.val) {
                                        this.extractedData.push({
                                            label: f.label,
                                            parent: 'routes',
                                            index: idx,
                                            key: f.key,
                                            value: f.idVal !== undefined ? f.idVal : f.val,
                                            displayValue: f.val,
                                            nameKey: f.nameKey || null,
                                            selected: true
                                        });
                                    }
                                });
                            });
                        }

                        this.aiPreviewMode = true;
                        e.target.value = ''; // reset file input
                    })
                    .catch(err => {
                        this.isAiProcessing = false;
                        this.aiError = 'An error occurred while connecting to the AI service.';
                        console.error(err);
                    });
                },
                applyAiData() {
                    let dataToApply = { mbl: {}, hbls: [], routes: [] };
                    let hblMap = {};
                    
                    this.extractedData.forEach(item => {
                        if (item.selected) {
                            if (item.parent === 'routes') {
                                const rIdx = item.index !== undefined ? item.index : 0;
                                if (!dataToApply.routes[rIdx]) {
                                    dataToApply.routes[rIdx] = {};
                                }
                                dataToApply.routes[rIdx][item.key] = item.value;
                                if(item.nameKey && item.displayValue) {
                                    dataToApply.routes[rIdx][item.nameKey] = item.value;
                                    dataToApply.routes[rIdx][item.key] = item.displayValue;
                                }
                            } else if (item.parent === 'mbl') {
                                dataToApply.mbl[item.key] = item.value;
                                if(item.nameKey && item.displayValue) {
                                    dataToApply.mbl[item.nameKey] = item.displayValue;
                                }
                            } else if (item.parent === 'hbl') {
                                const hIdx = item.hIndex !== undefined ? item.hIndex : 0;
                                if (!hblMap[hIdx]) {
                                    hblMap[hIdx] = {};
                                }
                                hblMap[hIdx][item.key] = item.value;
                                if(item.nameKey && item.displayValue) {
                                    hblMap[hIdx][item.nameKey] = item.displayValue;
                                }
                            }
                        }
                    });

                    Object.keys(hblMap).forEach(k => {
                        dataToApply.hbls.push(hblMap[k]);
                    });
                    
                    this.populateAiData(dataToApply);
                    this.showAiModal = false;
                    this.aiPreviewMode = false;
                    this.extractedData = [];
                    if (typeof showToast === 'function') showToast('success', 'Data applied successfully!');
                },

                populateAiData(data) {
                    const ensureOptionExists = (nameOrSelector, id, text) => {
                        if (!id) return;
                        const selector = nameOrSelector.includes('[') || nameOrSelector.includes('.') 
                            ? nameOrSelector 
                            : `[name="${nameOrSelector}"]`;
                        const elements = document.querySelectorAll(selector);
                        elements.forEach(el => {
                            let exists = false;
                            for (let i = 0; i < el.options.length; i++) {
                                if (el.options[i].value == id) { exists = true; break; }
                            }
                            if (!exists) {
                                const opt = document.createElement('option');
                                opt.value = id;
                                opt.text = text || ('ID: ' + id);
                                el.appendChild(opt);
                            }
                        });
                    };

                    const formatDateTimeLocal = (dateStr) => {
                        if (!dateStr) return '';
                        dateStr = String(dateStr).trim();
                        if (dateStr.length === 10) {
                            return dateStr + 'T00:00';
                        }
                        if (dateStr.includes(' ')) {
                            const parts = dateStr.split(' ');
                            return parts[0] + 'T' + (parts[1] ? parts[1].substring(0, 5) : '00:00');
                        }
                        return dateStr.substring(0, 16);
                    };

                    const formatDateOnly = (dateStr) => {
                        if (!dateStr) return '';
                        return String(dateStr).trim().substring(0, 10);
                    };

                    // 1. MBL Mapping
                    if (data.mbl) {
                        const m = data.mbl;
                        if (m.mawb_no) this.form.mawb_no = m.mawb_no;
                        if (m.file_no) this.form.file_no = m.file_no;
                        if (m.awb_date) this.form.awb_date = formatDateOnly(m.awb_date);
                        if (m.post_date) this.form.post_date = formatDateOnly(m.post_date);
                        if (m.flight_no) this.form.flight_no = m.flight_no;
                        if (m.etd) this.form.etd = formatDateOnly(m.etd);
                        if (m.eta) this.form.eta = formatDateOnly(m.eta);
                        if (m.atd) this.form.atd = formatDateOnly(m.atd);
                        if (m.ata) this.form.ata = formatDateOnly(m.ata);
                        if (m.issuing_carrier) this.form.issuing_carrier = m.issuing_carrier;

                        // Carrier
                        const carrierId = m.carrier_id;
                        if (carrierId) {
                            ensureOptionExists('carrier_id', carrierId, m.carrier_name);
                            this.form.carrier = carrierId;
                            this.form.carrier_id = carrierId;
                        }

                        // Departure (dep_port_id in HTML, form.departure in Alpine)
                        const depId = m.departure_id || m.pol_id;
                        const depName = m.departure_name || m.pol_name;
                        if (depId) {
                            ensureOptionExists('dep_port_id', depId, depName);
                            this.form.departure = depId;
                            this.form.departure_id = depId;
                        }

                        // Destination (dst_port_id in HTML, form.destination in Alpine)
                        const dstId = m.destination_id || m.pod_id;
                        const dstName = m.destination_name || m.pod_name;
                        if (dstId) {
                            ensureOptionExists('dst_port_id', dstId, dstName);
                            this.form.destination = dstId;
                            this.form.destination_id = dstId;
                        }

                        // Shipper
                        if (m.shipper_id) {
                            ensureOptionExists('shipper_id', m.shipper_id, m.shipper_name);
                            this.form.shipper = m.shipper_id;
                        }

                        // Consignee
                        if (m.consignee_id) {
                            ensureOptionExists('consignee_id', m.consignee_id, m.consignee_name);
                            this.form.consignee = m.consignee_id;
                        }

                        // Notify
                        if (m.notify_id) {
                            ensureOptionExists('notify_id', m.notify_id, m.notify_party_name);
                            this.form.notify = m.notify_id;
                        }

                        // Forwarding Agent / Co-loader
                        const fwdId = m.forwarding_agent_id || m.co_loader_id;
                        const fwdName = m.forwarding_agent_name || m.co_loader_name;
                        if (fwdId) {
                            ensureOptionExists('forwarding_agent_id', fwdId, fwdName);
                            this.form.co_loader = fwdId;
                            this.form.forwarding_agent_id = fwdId;
                        }

                        // Actual Shipper
                        if (m.actual_shipper_id) {
                            ensureOptionExists('actual_shipper_id', m.actual_shipper_id, m.actual_shipper_name);
                            this.form.actual_shipper = m.actual_shipper_id;
                        }

                        // OP
                        if (m.op_id) {
                            ensureOptionExists('op_id', m.op_id, m.op_name);
                            this.form.op = m.op_id;
                        }

                        // Weights & Packages
                        if (m.pkg_qty !== undefined && m.pkg_qty !== '') this.form.pkg_qty = m.pkg_qty;
                        else if (m.pieces !== undefined && m.pieces !== '') this.form.pkg_qty = m.pieces;

                        if (m.gross_weight !== undefined && m.gross_weight !== '') this.form.gross_weight = m.gross_weight;
                        if (m.chargeable_weight !== undefined && m.chargeable_weight !== '') this.form.chargeable_weight = m.chargeable_weight;
                        if (m.volume !== undefined && m.volume !== '') this.form.volume = m.volume;

                        // Terms
                        if (m.dv_carriage) this.form.dv_carriage = m.dv_carriage;
                        if (m.dv_customs) this.form.dv_customs = m.dv_customs;
                        if (m.wt_val) this.form.wt_val = m.wt_val;
                        if (m.other_term) this.form.other_term = m.other_term;
                        if (m.insurance) this.form.insurance = m.insurance;
                        if (m.description) {
                            this.form.internal_remark = m.description;
                            this.form.memo = m.description;
                        }
                    }

                    // 2. Routes Mapping
                    if (data.routes && data.routes.length > 0) {
                        if (!this.form.routes || !Array.isArray(this.form.routes)) {
                            this.form.routes = [];
                        }
                        this.form.routes = [];

                        data.routes.forEach(r => {
                            const flightNo = r.flight_no || this.form.flight_no || '';
                            const polId = r.pol_id || r.departure_id || this.form.departure || '';
                            const podId = r.pod_id || r.destination_id || this.form.destination || '';
                            const carrierId = r.carrier_id || this.form.carrier || '';
                            const etd = formatDateTimeLocal(r.etd || this.form.etd);
                            const eta = formatDateTimeLocal(r.eta || this.form.eta);

                            if (polId || etd || flightNo) {
                                this.form.routes.push({
                                    airport_id: polId,
                                    eta: '',
                                    ata: '',
                                    etd: etd,
                                    atd: '',
                                    flight_no: flightNo,
                                    carrier_id: carrierId
                                });
                            }

                            if (podId || eta) {
                                this.form.routes.push({
                                    airport_id: podId,
                                    eta: eta,
                                    ata: '',
                                    etd: '',
                                    atd: '',
                                    flight_no: flightNo,
                                    carrier_id: carrierId
                                });
                            }
                        });

                        if (this.form.routes.length > 0) {
                            if (!this.form.departure && this.form.routes[0].airport_id) {
                                this.form.departure = this.form.routes[0].airport_id;
                            }
                            const last = this.form.routes[this.form.routes.length - 1];
                            if (!this.form.destination && last.airport_id) {
                                this.form.destination = last.airport_id;
                            }
                        }
                    }

                    // 3. HBL Mapping
                    if (data.hbls && data.hbls.length > 0) {
                        if (!this.hawbs) this.hawbs = [];

                        data.hbls.forEach((h, hIdx) => {
                            const hawbNo = h.hawb_no || h.hbl_no || '';
                            const shipperId = h.shipper_id || data.mbl?.shipper_id || this.form.shipper || '';
                            const consigneeId = h.consignee_id || data.mbl?.consignee_id || this.form.consignee || '';
                            const notifyId = h.notify_id || data.mbl?.notify_id || this.form.notify || '';
                            const pieces = h.pkg_qty || h.pieces || data.mbl?.pkg_qty || data.mbl?.pieces || '';
                            const grossWeight = h.gross_weight || h.weight || data.mbl?.gross_weight || this.form.gross_weight || '';
                            const chgWeight = h.chargeable_weight || data.mbl?.chargeable_weight || this.form.chargeable_weight || '';
                            const volume = h.volume || h.volume_weight || data.mbl?.volume || this.form.volume || '';
                            const depPort = h.departure_id || h.departure || this.form.departure || '';
                            const dstPort = h.destination_id || h.destination || this.form.destination || '';

                            let targetHawb = null;
                            if (this.hawbs.length === 1 && !this.hawbs[0].hawb_no && !this.hawbs[0].shipper) {
                                targetHawb = this.hawbs[0];
                            } else if (this.hawbs[hIdx]) {
                                targetHawb = this.hawbs[hIdx];
                            } else {
                                targetHawb = {
                                    id: null,
                                    show: true,
                                    showMemo: false,
                                    showMore: false,
                                    hawb_no: '',
                                    booking_no: '',
                                    booking_date: '',
                                    quotation_no: '',
                                    shipper: '',
                                    customer: '',
                                    bill_to: '',
                                    consignee: '',
                                    notify: '',
                                    oversea_agent: '',
                                    issuing_carrier: 'FREIGHTX',
                                    trucker: '',
                                    sales: '',
                                    op: this.form.op || '',
                                    itn_no: '',
                                    display_unit: 'BOTH',
                                    departure: '',
                                    destination: '',
                                    cargo_pickup: '',
                                    delivery_to: '',
                                    cargo_type: 'GENERAL CARGO',
                                    sales_type: '',
                                    ship_type: 'NORMAL',
                                    feta: '',
                                    dv_carriage: 'N.V.D.',
                                    dv_customs: 'N.C.V.',
                                    freight_term: 'P',
                                    other_charge_term: 'P',
                                    insurance: 'N.I.L.',
                                    cargo_ready_date: '',
                                    pkg_qty: '',
                                    pkg_unit_id: '',
                                    gross_weight: '',
                                    chargeable_weight: '',
                                    volume: '',
                                    buying_rate: '',
                                    selling_rate: '',
                                    accounting_info: [],
                                    commodities: []
                                };
                                this.hawbs.push(targetHawb);
                            }

                            targetHawb.hawb_no = hawbNo;
                            targetHawb.shipper = shipperId;
                            targetHawb.customer = shipperId || consigneeId;
                            targetHawb.consignee = consigneeId;
                            targetHawb.notify = notifyId;
                            targetHawb.departure = depPort;
                            targetHawb.destination = dstPort;
                            targetHawb.pkg_qty = pieces;
                            targetHawb.gross_weight = grossWeight;
                            targetHawb.chargeable_weight = chgWeight;
                            targetHawb.volume = volume;
                            targetHawb.op = this.form.op || '';
                            targetHawb.sales_type = this.form.sales_type || '';
                            targetHawb.ship_type = this.form.ship_type || 'NORMAL';
                            if (h.description) {
                                targetHawb.commodities = [{
                                    description: h.description,
                                    pieces: pieces,
                                    weight: grossWeight,
                                    volume: volume
                                }];
                            }
                        });
                    }
                },

                init() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    
                    // Load existing HAWBs if any
                    @if(isset($airExport) && $airExport->hbls->count() > 0)
                        this.hawbs = {!! json_encode($airExport->hbls->map(function($hbl) use ($airExport) {
                            return [
                                'id' => $airExport->id ? $hbl->id : null,
                                'show' => true,
                                'showMore' => false,
                                'showMemo' => false,
                                'hawb_no' => $airExport->id ? $hbl->hawb_no : ($hbl->hawb_no ? $hbl->hawb_no . '-COPY' : ''),
                                'booking_no' => $hbl->booking_no ?? '',
                                'booking_date' => $hbl->booking_date ? \Carbon\Carbon::parse($hbl->booking_date)->format('Y-m-d') : '',
                                'shipper' => $hbl->shipper_id,
                                'customer' => $hbl->customer_id,
                                'bill_to' => $hbl->bill_to ?? '',
                                'consignee' => $hbl->consignee_id,
                                'notify' => $hbl->notify_party_id,
                                'sales' => $hbl->sales_person_id,
                                'oversea_agent' => $hbl->oversea_agent_id ?? '',
                                'sales_type' => $hbl->sales_type ?? '',
                                'departure' => $hbl->departure ?? '',
                                'destination' => $hbl->destination ?? '',
                                'cargo_pickup' => $hbl->cargo_pickup ?? '',
                                'delivery_to' => $hbl->delivery_to ?? '',
                                'cargo_type' => $hbl->cargo_type ?? 'GENERAL CARGO',
                                'ship_type' => $hbl->ship_type ?? 'NORMAL',
                                'feta' => $hbl->feta ? \Carbon\Carbon::parse($hbl->feta)->format('Y-m-d') : '',
                                'itn_no' => $hbl->itn_no ?? '',
                                'display_unit' => $hbl->display_unit ?? 'BOTH',
                                'pkg_qty' => $hbl->pkg_qty,
                                'pkg_unit_id' => $hbl->pkg_unit_id,
                                'gross_weight' => $hbl->gross_weight,
                                'chargeable_weight' => $hbl->chargeable_weight,
                                'volume' => $hbl->volume,
                                'buying_rate' => $hbl->buying_rate ?? '',
                                'selling_rate' => $hbl->selling_rate ?? '',
                                'commodity' => $hbl->commodity ?? '',
                                'incoterms_id' => $hbl->incoterms_id ?? '',
                                'freight_term' => $hbl->freight_term ?? 'P',
                                'hbl_remark' => $hbl->hbl_remark ?? '',
                                'mark' => $hbl->mark ?? '',
                                'description' => $hbl->description ?? '',
                                'remark' => $hbl->remark ?? '',
                                'commodities' => is_array($hbl->commodities) ? $hbl->commodities : (json_decode($hbl->commodities, true) ?? []),
                            ];
                        })) !!};
                    @else
                        if(this.hawbs.length === 0) this.addHawb();
                    @endif
                    
                    if(window.location.search.includes('load_from_quotation=true')) {
                        this.showQuoteModal = true;
                    }
                    
                    // Work Order Tab Logic
                    // Check URL for tab parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    const tabParam = urlParams.get('tab');
                    if (tabParam) {
                        this.activeTab = tabParam;
                    }
                    
                    // Watch for tab changes - fetch work orders EVERY TIME workorder tab opens
                    this.$watch('activeTab', (newTab) => {
                        if (newTab === 'workorder' && shipmentId) {
                            this.fetchWorkOrders();
                        }
                    });
                    
                    // If already on workorder tab on page load, fetch immediately
                    if (this.activeTab === 'workorder' && shipmentId) {
                        this.fetchWorkOrders();
                    }
                },
                closeQuoteModal() {
                    if(window.location.search.includes('load_from_quotation=true')) {
                        window.location.href = '/air-export/create';
                    } else {
                        this.showQuoteModal = false;
                    }
                },
                confirmQuoteSelection() {
                    const q = this.quoteForm;
                    
                    // MAWB
                    this.form.mawb_no = q.mawb_no || '';
                    if (q.etd) this.form.etd = q.etd.length === 10 ? q.etd + 'T00:00' : q.etd;
                    if (q.eta) this.form.eta = q.eta.length === 10 ? q.eta + 'T00:00' : q.eta;
                    if (q.pol_id) this.form.departure = q.pol_id;
                    if (q.pod_id) this.form.destination = q.pod_id;
                    if (q.op_id) this.form.op = q.op_id;
                    if (q.gross_weight_kg) this.form.gross_weight = q.gross_weight_kg;
                    if (q.volume_cbm) this.form.volume = q.volume_cbm;
                    if (q.carrier_id) this.form.carrier = q.carrier_id;
                    if (q.oversea_agent_id) this.form.co_loader = q.oversea_agent_id; // Using co-loader for forwarding agent
                    
                    // HAWB
                    if (this.hawbs.length === 0) this.addHawb();
                    this.hawbs[0].hawb_no = q.hawb_no || '';
                    if (q.customer_id) this.hawbs[0].customer = q.customer_id;
                    if (q.sales_person_id) this.hawbs[0].sales = q.sales_person_id;
                    if (q.incoterms_id) this.hawbs[0].incoterms_id = q.incoterms_id;
                    if (q.gross_weight_kg) this.hawbs[0].gross_weight = q.gross_weight_kg;
                    if (q.volume_cbm) this.hawbs[0].volume = q.volume_cbm;
                    if (q.commodity) this.hawbs[0].commodity = q.commodity;
                    if (q.quote_no) this.hawbs[0].quotation_no = q.quote_no;
                    
                    // Charges
                    if (this.selectedQuote && this.selectedQuote.items) {
                        const items = this.selectedQuote.items.filter(item => item.selected !== false);
                        items.forEach(item => {
                            this.form.other_charges.push({
                                charge_code: item.charge_code,
                                description: item.charge_name,
                                term: 'P',
                                rate: item.rate,
                                amount: item.qty
                            });
                        });
                    }

                    this.showQuoteModal = false;
                    if (typeof showToast === 'function') {
                        showToast('success', 'Quotation data loaded successfully');
                    }
                },
                createAccountingInvoice(type) {
                    // Check if shipment is saved
                    if (!{{ isset($airExport) && $airExport->id ? $airExport->id : 0 }}) {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Please save the shipment first before creating invoices');
                        }
                        return;
                    }

                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    
                    // Define routes for each invoice type
                    const routes = {
                        'revenue': `/accounting/invoice/create?type=AR&shipment_type=air_export&shipment_id=${shipmentId}`,
                        'dc_note': `/accounting/invoice/create?type=DC&shipment_type=air_export&shipment_id=${shipmentId}`,
                        'cost': `/accounting/invoice/create?type=AP&shipment_type=air_export&shipment_id=${shipmentId}`
                    };

                    // Open invoice creation page in new tab
                    if (routes[type]) {
                        window.open(routes[type], '_blank');
                    } else {
                        if (typeof showToast === 'function') {
                            showToast('info', `${type} invoice creation - Coming soon`);
                        }
                    }
                },
                
                // Work Order Management
                workOrders: [],
                selectedWorkOrders: [],
                loadingWorkOrders: false,
                
                async fetchWorkOrders() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        this.workOrders = [];
                        return;
                    }
                    
                    this.loadingWorkOrders = true;
                    try {
                        // Use the proper class name for polymorphic relationship
                        const response = await fetch(`/api/work-orders?workable_type=App\\Models\\AirExport&workable_id=${shipmentId}`);
                        if (response.ok) {
                            const data = await response.json();
                            // API returns array directly, not wrapped in data property
                            this.workOrders = Array.isArray(data) ? data : (data.data || []);
                            console.log('Work orders loaded:', this.workOrders.length);
                        } else {
                            console.error('Failed to fetch work orders:', response.status);
                            this.workOrders = [];
                        }
                    } catch (error) {
                        console.error('Error fetching work orders:', error);
                        this.workOrders = [];
                    } finally {
                        this.loadingWorkOrders = false;
                    }
                },
                
                createWorkOrder() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') {
                            showToast('error', 'Please save the shipment first before creating work orders');
                        }
                        return;
                    }
                    
                    // Build URL with shipment context - use proper class name and add source for redirect
                    const url = `/ocean-export/work-order/create?` +
                               `workable_type=App\\Models\\AirExport&` +
                               `workable_id=${shipmentId}&` +
                               `mbl_no=${encodeURIComponent(this.form.mawb_no || '')}&` +
                               `file_no=${encodeURIComponent(this.form.file_no || '')}&` +
                               `source=air_export&` +
                               `source_id=${shipmentId}`;
                    
                    window.open(url, '_blank');
                },
                
                editWorkOrder(workOrderId) {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    const url = `/ocean-export/work-order/${workOrderId}/edit?` +
                               `source=air_export&` +
                               `source_id=${shipmentId}`;
                    window.open(url, '_blank');
                },
                
                async deleteWorkOrder(workOrderId) {
                    if (!confirm('Are you sure you want to delete this work order?')) {
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/ocean-export/work-order/${workOrderId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            if (typeof showToast === 'function') {
                                showToast('success', 'Work order deleted successfully');
                            }
                            this.fetchWorkOrders();
                            // Remove from selected if it was selected
                            this.selectedWorkOrders = this.selectedWorkOrders.filter(id => id !== workOrderId);
                        } else {
                            if (typeof showToast === 'function') {
                                showToast('error', 'Failed to delete work order');
                            }
                        }
                    } catch (error) {
                        console.error('Error deleting work order:', error);
                        if (typeof showToast === 'function') {
                            showToast('error', 'Error deleting work order');
                        }
                    }
                },
                
                async bulkDeleteWorkOrders() {
                    if (this.selectedWorkOrders.length === 0) {
                        return;
                    }
                    
                    const count = this.selectedWorkOrders.length;
                    if (!confirm(`Are you sure you want to delete ${count} work order(s)?\n\nThis action cannot be undone.`)) {
                        return;
                    }
                    
                    // Show loading state
                    this.loadingWorkOrders = true;
                    if (typeof showToast === 'function') {
                        showToast('info', `Deleting ${count} work order(s)...`);
                    }
                    
                    let successCount = 0;
                    let failCount = 0;
                    const failedWorkOrders = [];
                    
                    for (const workOrderId of this.selectedWorkOrders) {
                        try {
                            const response = await fetch(`/ocean-export/work-order/${workOrderId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            });
                            
                            if (response.ok) {
                                successCount++;
                            } else {
                                failCount++;
                                failedWorkOrders.push(workOrderId);
                            }
                        } catch (error) {
                            failCount++;
                            failedWorkOrders.push(workOrderId);
                        }
                    }
                    
                    this.selectedWorkOrders = [];
                    await this.fetchWorkOrders();
                    
                    // Show results
                    if (typeof showToast === 'function') {
                        if (successCount > 0 && failCount === 0) {
                            showToast('success', `✓ Successfully deleted ${successCount} work order(s)`);
                        } else if (successCount > 0 && failCount > 0) {
                            showToast('warning', `Deleted ${successCount} work order(s), but ${failCount} failed`);
                        } else {
                            showToast('error', `Failed to delete ${failCount} work order(s)`);
                        }
                    }
                },
                
                toggleWorkOrder(workOrderId) {
                    const index = this.selectedWorkOrders.indexOf(workOrderId);
                    if (index > -1) {
                        this.selectedWorkOrders.splice(index, 1);
                    } else {
                        this.selectedWorkOrders.push(workOrderId);
                    }
                },
                
                toggleAllWorkOrders() {
                    if (this.selectedWorkOrders.length === this.workOrders.length) {
                        this.selectedWorkOrders = [];
                    } else {
                        this.selectedWorkOrders = this.workOrders.map(wo => wo.id);
                    }
                },
                
                refreshWorkOrders() {
                    if (typeof showToast === 'function') {
                        showToast('info', 'Refreshing work orders...');
                    }
                    this.fetchWorkOrders();
                },

                // Tools Dropdown & Sub-Header
                showToolsMenu: false,
                showInfoModal: false,
                isBlocked: {{ isset($airExport) && $airExport->is_blocked ? 'true' : 'false' }},
                showDocPackageModal: false,
                docPackageForm: {
                    selectedReports: ['manifest', 'mawb_print', 'local_invoice', 'credit_debit', 'hawb_print', 'commercial_invoice', 'packing_list'],
                    report_agent_type: 'master'
                },
                showConsolidatedManifestModal: false,
                manifestForm: {
                    agent_type: 'master'
                },
                showBookingConfirmationModal: false,
                bookingConfirmationForm: {
                    agent_type: 'master'
                },
                get masterAgentName() {
                    return '{{ isset($airExport) && $airExport->overseaAgent ? addslashes($airExport->overseaAgent->name) : "SHAWON LOGISTICS CO., LTD" }}';
                },
                get subAgentName() {
                    return '{{ isset($airExport) && $airExport->forwardingAgent ? addslashes($airExport->forwardingAgent->name) : "SUB AGENT LOGISTICS INC." }}';
                },
                portsMap: {
                    @foreach($ports as $port)
                    "{{ $port->id }}": "{!! addslashes($port->name) !!}",
                    @endforeach
                },
                get depPortName() {
                    return this.portsMap[this.form.departure] || '{{ isset($airExport) && $airExport->depPort ? addslashes($airExport->depPort->name) : "LOS ANGELES INT\'L" }}';
                },
                get dstPortName() {
                    return this.portsMap[this.form.destination] || '{{ isset($airExport) && $airExport->dstPort ? addslashes($airExport->dstPort->name) : "AMSTERDAM AIRPORT" }}';
                },
                get etdDisplay() {
                    if (this.form.etd) {
                        let parts = this.form.etd.split('T')[0].split('-');
                        if (parts.length === 3) return `${parts[1]}-${parts[2]}-${parts[0]}`;
                    }
                    return '{{ isset($airExport) && $airExport->etd ? \Carbon\Carbon::parse($airExport->etd)->format("m-d-Y") : date("m-d-Y") }}';
                },
                get etaDisplay() {
                    if (this.form.eta) {
                        let parts = this.form.eta.split('T')[0].split('-');
                        if (parts.length === 3) return `${parts[1]}-${parts[2]}-${parts[0]}`;
                    }
                    return '{{ isset($airExport) && $airExport->eta ? \Carbon\Carbon::parse($airExport->eta)->format("m-d-Y") : date("m-d-Y") }}';
                },
                get mawbDisplay() {
                    return this.form.mawb_no || this.form.file_no || 'MAE-NEW';
                },
                async toggleBlock() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('error', 'Please save shipment first');
                        return;
                    }
                    const action = this.isBlocked ? 'unblock' : 'block';
                    try {
                        const res = await fetch(`/air-export/bulk-${action}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ ids: [shipmentId] })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.isBlocked = !this.isBlocked;
                            if (typeof showToast === 'function') showToast('success', data.message || `Shipment ${action}ed successfully`);
                        } else {
                            if (typeof showToast === 'function') showToast('error', data.message || 'Failed to update block status');
                        }
                    } catch (e) {
                        if (typeof showToast === 'function') showToast('error', 'Failed to update block status');
                    }
                },
                copyShipment() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : (request('copy') ? request('copy') : 0) }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('error', 'Please save shipment first before copying');
                        return;
                    }
                    if (typeof showToast === 'function') showToast('info', 'Loading copied shipment data into fresh form...');
                    window.location.href = `/air-export/create?copy=${shipmentId}`;
                },
                copyToAirImport() {
                    if (typeof showToast === 'function') showToast('warning', 'Copy to Air Import is currently disabled.');
                },
                async deleteShipment() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('error', 'Shipment is not saved yet');
                        return;
                    }
                    if (!confirm('Are you sure you want to delete this Air Export shipment? This action cannot be undone.')) return;
                    try {
                        const res = await fetch(`/air-export/${shipmentId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (res.ok) {
                            if (typeof showToast === 'function') showToast('success', data.message || 'Shipment deleted successfully');
                            setTimeout(() => {
                                window.location.href = '/air-export/list';
                            }, 500);
                        } else {
                            if (typeof showToast === 'function') showToast('error', data.message || 'Failed to delete shipment');
                        }
                    } catch (e) {
                        if (typeof showToast === 'function') showToast('error', 'Error deleting shipment');
                    }
                },
                printMawb() {
                    if (typeof showToast === 'function') showToast('info', 'Opening MAWB Print view...');
                    window.print();
                },
                openDocPackage() { 
                    this.showDocPackageModal = true; 
                },
                selectAllDocReports() {
                    this.docPackageForm.selectedReports = ['manifest', 'mawb_print', 'local_invoice', 'credit_debit', 'hawb_print', 'commercial_invoice', 'packing_list'];
                },
                clearAllDocReports() {
                    this.docPackageForm.selectedReports = [];
                },
                submitDocPackage() {
                    if (this.docPackageForm.selectedReports.length === 0) {
                        if (typeof showToast === 'function') showToast('warning', 'Please select at least one report type.');
                        return;
                    }
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const reportsParam = this.docPackageForm.selectedReports.join(',');
                    const agentParam = this.docPackageForm.report_agent_type;
                    const url = `/air-export/${shipmentId}/document-package?reports=${reportsParam}&agent_type=${agentParam}`;
                    this.showDocPackageModal = false;
                    window.open(url, '_blank');
                },
                openConsolidatedManifest() { 
                    this.showConsolidatedManifestModal = true; 
                },
                submitConsolidatedManifest() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const agentParam = this.manifestForm.agent_type;
                    const url = `/air-export/${shipmentId}/consolidated-manifest?agent_type=${agentParam}`;
                    this.showConsolidatedManifestModal = false;
                    window.open(url, '_blank');
                },
                openBookingConfirmation() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const url = `/air-export/${shipmentId}/booking-confirmation`;
                    window.open(url, '_blank');
                },
                openMawbPackageLabel() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const url = `/air-export/${shipmentId}/mawb-package-label`;
                    window.open(url, '_blank');
                },
                openPackageLabelList() {
                    const shipmentId = {{ isset($airExport) && $airExport->id ? $airExport->id : 0 }};
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const url = `/air-export/${shipmentId}/package-label-list`;
                    window.open(url, '_blank');
                },
                openPickupDeliveryOrder() { if (typeof showToast === 'function') showToast('info', 'Opening Pickup / Delivery Order...'); },
                openSecurityEndorsement() { if (typeof showToast === 'function') showToast('info', 'Opening Security Endorsement...'); },
                openOnHandReport() { if (typeof showToast === 'function') showToast('info', 'Opening On Hand Report...'); },
                openScreenedCargoStatement() { if (typeof showToast === 'function') showToast('info', 'Opening Screened Cargo Statement (K9)...'); },
                openProfitSummary() {
                    const shipmentId = '{{ isset($airExport) && $airExport->id ? $airExport->id : '' }}';
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('warning', 'Please save the shipment first');
                        return;
                    }
                    window.open(`/air-export/${shipmentId}/profit-summary`, '_blank');
                },
                sumPackageWeight() {
                    if (!this.hawbs || this.hawbs.length === 0) {
                        if (typeof showToast === 'function') showToast('warning', 'No HAWBs to sum.');
                        return;
                    }
                    let totalPkg = 0;
                    let totalWeightKg = 0;
                    this.hawbs.forEach(hawb => {
                        totalPkg += parseFloat(hawb.pkg_qty || 0);
                        totalWeightKg += parseFloat(hawb.gross_weight || 0);
                    });
                    this.form.pkg_qty = totalPkg;
                    this.form.gross_weight = totalWeightKg.toFixed(2);
                    if (typeof showToast === 'function') showToast('success', 'Package & Weight summed from ' + this.hawbs.length + ' HAWB(s)');
                },
                openDimensionsModal(target) {
                    this.dimensionTarget = target;
                    this.showDimensionsModal = true;
                    if (!this.dimensionRows || this.dimensionRows.length === 0) {
                        this.dimensionRows = [{ selected: false, length: '', width: '', height: '', pcs: 1, gross_weight: '' }];
                    }
                },
                closeDimensionsModal() {
                    this.showDimensionsModal = false;
                },
                addDimensionRow() {
                    this.dimensionRows.push({ selected: false, length: '', width: '', height: '', pcs: 1, gross_weight: '' });
                },
                deleteSelectedDimensions() {
                    this.dimensionRows = this.dimensionRows.filter(r => !r.selected);
                    if (this.dimensionRows.length === 0) {
                        this.addDimensionRow();
                    }
                },
                calcRowPcs(row) {
                    return parseFloat(row.pcs) || 0;
                },
                calcRowCbm(row) {
                    let l = parseFloat(row.length) || 0;
                    let w = parseFloat(row.width) || 0;
                    let h = parseFloat(row.height) || 0;
                    let pcs = parseFloat(row.pcs) || 0;
                    if (l <= 0 || w <= 0 || h <= 0 || pcs <= 0) return 0;
                    if (this.dimensionUnit === 'CM') return (l * w * h * pcs) / 1000000;
                    if (this.dimensionUnit === 'IN' || this.dimensionUnit === 'Inch') return (l * 2.54 * w * 2.54 * h * 2.54 * pcs) / 1000000;
                    if (this.dimensionUnit === 'Feet') return (l * 30.48 * w * 30.48 * h * 30.48 * pcs) / 1000000;
                    return 0;
                },
                calcRowCft(row) {
                    return this.calcRowCbm(row) * 35.3147;
                },
                calcRowVolKg(row) {
                    return this.calcRowCbm(row) * 166.6667;
                },
                calcRowVolLb(row) {
                    return this.calcRowVolKg(row) * 2.20462;
                },
                calcRowGrossLb(row) {
                    return (parseFloat(row.gross_weight) || 0) * 2.20462;
                },
                get totalDimPcs() {
                    return this.dimensionRows.reduce((sum, r) => sum + (parseFloat(r.pcs) || 0), 0);
                },
                get totalDimCbm() {
                    return this.dimensionRows.reduce((sum, r) => sum + this.calcRowCbm(r), 0);
                },
                get totalDimCft() {
                    return this.totalDimCbm * 35.3147;
                },
                get totalDimVolKg() {
                    return this.totalDimCbm * 166.6667;
                },
                get totalDimVolLb() {
                    return this.totalDimVolKg * 2.20462;
                },
                get totalDimGrossKg() {
                    return this.dimensionRows.reduce((sum, r) => sum + (parseFloat(r.gross_weight) || 0), 0);
                },
                applyDimensions() {
                    const cbm = this.totalDimCbm;
                    const volKg = this.totalDimVolKg;
                    const pcs = this.totalDimPcs;
                    const grossKg = this.totalDimGrossKg;
                    const chargeableKg = Math.max(grossKg, volKg);
                    
                    if (this.dimensionTarget === 'mawb') {
                        this.form.pkg_qty = pcs;
                        this.form.gross_weight = grossKg.toFixed(2);
                        this.form.chargeable_weight = chargeableKg.toFixed(2);
                        this.form.volume = cbm.toFixed(2);
                    } else {
                        const index = parseInt(this.dimensionTarget);
                        if (!isNaN(index) && this.hawbs[index]) {
                            this.hawbs[index].pkg_qty = pcs;
                            this.hawbs[index].gross_weight = grossKg.toFixed(2);
                            this.hawbs[index].chargeable_weight = chargeableKg.toFixed(2);
                            this.hawbs[index].volume = cbm.toFixed(2);
                        }
                    }
                    this.closeDimensionsModal();
                },
                openProfitDetail() {
                    const shipmentId = '{{ isset($airExport) && $airExport->id ? $airExport->id : '' }}';
                    if (!shipmentId) {
                        if (typeof showToast === 'function') showToast('warning', 'Please save the shipment first');
                        return;
                    }
                    window.open(`/air-export/${shipmentId}/profit-detail`, '_blank');
                },
                openTrackTrace() {
                    const mawb = (this.form && this.form.mawb_no) ? this.form.mawb_no : (this.hawbs && this.hawbs.length > 0 ? this.hawbs[0].hawb_no : '');
                    window.openTrackTrace({ type: 'aircargo', number: mawb });
                }
            };
        };
    </script>
<div x-data="window.airExportModule()" x-init="init()" x-cloak>
        <div class="page-content">
        <form id="airExportForm" action="{{ isset($airExport) && $airExport->id ? route('air-export.update', $airExport->id) : route('air-export.store') }}" method="POST">
            @csrf
            @if(isset($airExport) && $airExport->id) @method('PUT') @endif
        <!-- Breadcrumbs -->
        <div style="font-size: 11px; color: #8e9eae; margin-bottom: 15px;">
            <a href="/" style="color: #8e9eae; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#337ab7';" onmouseout="this.style.color='#8e9eae';" target="_blank"><i class="fa fa-home"></i> Home</a> <i class="fa fa-angle-right" style="margin: 0 5px;"></i> 
            <a href="/air-export/list" style="color: #8e9eae; text-decoration: none; transition: color 0.15s;" onmouseover="this.style.color='#337ab7';" onmouseout="this.style.color='#8e9eae';">Air Export</a> <i class="fa fa-angle-right" style="margin: 0 5px;"></i> 
            <span style="color: #333; font-weight: 700;">{{ isset($airExport) ? 'Edit Shipment' : 'New Shipment' }}</span>
        </div>

        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h1 class="caption-subject" style="font-size: 18px;">{{ isset($airExport) ? 'AIR EXPORT SHIPMENT' : 'Create Air Export Shipment' }}</h1>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" @click="showAiModal = true" class="frexo-trigger-btn" title="Auto-fill shipment details using Frexo AI">
                    <i class="fa fa-bolt"></i> Frexo AI
                </button>
                <button type="submit" class="btn-freightx"><i class="fa fa-save"></i> SAVE SHIPMENT</button>
                <a href="/air-export/list" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <!-- Main Tabs + Tools Dropdown (Ocean Import Pattern) -->
        <div style="display:flex; align-items:stretch; border-bottom:2px solid #e5e7eb; margin-bottom:15px; background:#fff;">
            <ul class="gf-tabs" style="border-bottom:none; margin-bottom:0; flex:1; display:flex;">
                <li :class="activeTab === 'basic' ? 'active' : ''" @click="activeTab = 'basic'"><a>Basic</a></li>
                <li :class="activeTab === 'charges' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'charges'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Charges</a></li>
                <li style="display: none !important;" :class="activeTab === 'doc' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'doc'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Doc Center</a></li>
                <li style="display: none !important;" :class="activeTab === 'workorder' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'workorder'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Work Order</a></li>
                <li :class="activeTab === 'status' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'status'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Status</a></li>
                <li :class="activeTab === 'doc' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'doc'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a></a></li>
                <li :class="activeTab === 'workorder' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'workorder'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a></a></li>
            </ul>

            <!-- Divider -->
            <div style="display:flex;align-items:center;padding:0 4px;flex-shrink:0;">
                <span style="border-left:2px solid #e5e7eb;height:20px;display:inline-block;"></span>
            </div>

            <!-- Tools Options (Dropdown) -->
            <div style="display:flex; align-items:center; padding:0 10px; margin-left: auto;">
                <div style="position: relative;" x-data="{ showToolsMenu: false }" @click.away="showToolsMenu = false" x-show="saved">
                    <button type="button" @click.stop.prevent="showToolsMenu = !showToolsMenu" class="btn-default-gf" :class="!saved ? 'disabled-tab' : ''" :disabled="!saved" style="display:flex; align-items:center; gap:5px; height: 28px; background: #fff;">
                        <i class="fa fa-wrench"></i> Docs <i class="fa fa-angle-down"></i>
                    </button>
                    <div x-show="showToolsMenu" style="position: absolute; top: 100%; right: 0; margin-top: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 50; min-width: 220px; display: none; max-height: 400px; overflow-y: auto;">
                        <ul style="list-style: none; padding: 4px 0; margin: 0;">
                            <li><button type="button" @click="saved ? toggleBlock() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa" :class="isBlocked ? 'fa-unlock' : 'fa-lock'" style="width:14px; text-align:center; margin-right:4px;"></i> <span x-text="isBlocked ? 'Unblock' : 'Block'"></span></button></li>
                            <li><button type="button" @click="saved ? copyShipment() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-copy" style="width:14px; text-align:center; margin-right:4px;"></i> Copy</button></li>
                            <li><button type="button" @click="saved ? deleteShipment() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer; color:#ef4444;"><i class="fa fa-trash-o" style="width:14px; text-align:center; margin-right:4px; color:#ef4444;"></i> Delete</button></li>
                            <li><button type="button" @click="saved ? openDocPackage() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-folder-open-o" style="width:14px; text-align:center; margin-right:4px;"></i> Document Package</button></li>
                            <li><button type="button" @click="saved ? printMawb() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-print" style="width:14px; text-align:center; margin-right:4px;"></i> MAWB Print</button></li>
                            <li><button type="button" @click="saved ? openConsolidatedManifest() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-file-text-o" style="width:14px; text-align:center; margin-right:4px;"></i> Consolidated Manifest</button></li>
                            <li><button type="button" @click="saved ? openBookingConfirmation() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-check-square-o" style="width:14px; text-align:center; margin-right:4px;"></i> Booking Confirmation</button></li>
                            <li><button type="button" @click="saved ? openMawbPackageLabel() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-tags" style="width:14px; text-align:center; margin-right:4px;"></i> MAWB Package Label</button></li>
                            <li><button type="button" @click="saved ? openPackageLabelList() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-list-alt" style="width:14px; text-align:center; margin-right:4px;"></i> Package Label List</button></li>
                            <li><button type="button" @click="saved ? openPickupDeliveryOrder() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-truck" style="width:14px; text-align:center; margin-right:4px;"></i> Pickup / Delivery Order</button></li>
                            <li><button type="button" @click="saved ? openSecurityEndorsement() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-shield" style="width:14px; text-align:center; margin-right:4px;"></i> Security Endorsement</button></li>
                            <li><button type="button" @click="saved ? openOnHandReport() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-hand-paper-o" style="width:14px; text-align:center; margin-right:4px;"></i> On Hand Report</button></li>
                            <li><button type="button" @click="saved ? openScreenedCargoStatement() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-file-o" style="width:14px; text-align:center; margin-right:4px;"></i> Screened Cargo Statement (K9)</button></li>
                            <li><button type="button" @click="saved ? openProfitSummary() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-bar-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Summary</button></li>
                            <li><button type="button" @click="saved ? openProfitDetail() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-pie-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Detail</button></li>
                            <li><button type="button" @click="saved ? openTrackTrace() : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-map-marker" style="width:14px; text-align:center; margin-right:4px;"></i> Track-Trace</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding-bottom: 50px;">
            <!-- BASIC TAB -->
            <div x-show="activeTab === 'basic'" :style="activeTab === 'basic' ? '' : 'display: none !important;'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div @click="showMblSection = !showMblSection" class="portlet-title" style="cursor: pointer; background: #f9fafb;">
                        <span class="caption-subject"><i class="fa" :class="showMblSection ? 'fa-minus-square-o' : 'fa-plus-square-o'"></i> MAWB</span>
                        <div class="actions">
                            <i class="fa fa-angle-down transition-transform" :class="showMblSection ? 'rotate-180' : ''"></i>
                        </div>
                    </div>
                    <div class="portlet-body" x-show="showMblSection">
                        <!-- Reminder Section for MAWB -->
                        <div class="memo-section" style="margin-bottom: 10px;">
                            <div class="memo-header" @click="showMblMemo = !showMblMemo">
                                <span>Note</span>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <button class="btn-default-gf" @click.stop="">Document (0) <i class="fa fa-external-link"></i></button>
                                    <i class="fa" :class="showMblMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                                </div>
                            </div>
                            <div class="memo-body" x-show="showMblMemo">
                                <div style="display: flex; gap: 10px;">
                                    <div style="flex: 2; border: 1px solid #dcdcdc; min-height: 50px; background: #fff; padding: 10px; color: #999; text-align: center;">No records found.</div>
                                    <div style="flex: 1;"><textarea class="form-control-gf" style="height: 80px; resize: none;" placeholder="Reminder content..."></textarea></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Booking No.</label><div class="form-input-container"><input type="text" name="file_no" class="form-control-gf" readonly value="{{ isset($airExport) ? $airExport->file_no : 'MAE-' . date('YmdHis') }}" required></div></div>
                            <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*MAWB No.</label><div class="form-input-container"><input type="text" name="mawb_no" class="form-control-gf" x-model="form.mawb_no" required></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><x-inline-select name="notify_id" :options="$agents" module="trade-partner" x-model="form.notify" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'notify_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Co-loader</label><div class="form-input-container"><x-inline-select name="forwarding_agent_id" :options="$agents" module="trade-partner" x-model="form.co_loader" class="form-control-gf" /></div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">AWB Date</label><div class="form-input-container"><input type="date" name="awb_date" class="form-control-gf" x-model="form.awb_date"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Post Date</label><div class="form-input-container"><input type="date" name="post_date" class="form-control-gf" x-model="form.post_date"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Actual Shipper</label><div class="form-input-container"><x-inline-select name="actual_shipper_id" :options="$agents" module="trade-partner" x-model="form.actual_shipper" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'actual_shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Issuing Carrier</label><div class="form-input-container"><input type="text" name="issuing_carrier" class="form-control-gf" x-model="form.issuing_carrier"><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf"><label class="form-label-gf">Shipper</label><div class="form-input-container"><x-inline-select name="shipper_id" :options="$agents" module="trade-partner" x-model="form.shipper" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*Office</label><div class="form-input-container">
                                <select name="office_id" class="form-control-gf" required x-model="form.office">
                                    <option value="">Select Office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div></div>
                            <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container">
                                <select name="op_id" class="form-control-gf" x-model="form.op">
                                    <option value="">Select Operator...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Type</label><div class="form-input-container"><select name="awb_type" class="form-control-gf" x-model="form.awb_type"><option value="NORMAL">NORMAL</option><option value="DIRECT">DIRECT</option></select></div></div>

                            <!-- Row 4 -->
                            <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><x-inline-select name="consignee_id" :options="$agents" module="trade-partner" x-model="form.consignee" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'consignee_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Acct. Carrier</label><div class="form-input-container"><x-inline-select name="acct_carrier_id" :options="$agents" module="trade-partner" x-model="form.awb_acct_carrier" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">ITN No.</label><div class="form-input-container"><input type="text" name="itn_no" class="form-control-gf" x-model="form.itn_no"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">CERS No.</label><div class="form-input-container"><input type="text" name="cers_no" class="form-control-gf" x-model="form.cers_no"></div></div>
                        </div>

                        <div class="form-grid-4">
                            <div class="form-group-gf"><label class="form-label-gf">Reference No.</label><div class="form-input-container"><input type="text" name="reference_no" class="form-control-gf" x-model="form.reference_no"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Direct Master</label><div class="form-input-container" style="justify-content: flex-start;"><input type="checkbox" name="is_direct_master" value="1" x-model="isDirectMaster"><input type="hidden" name="is_direct_master" :value="isDirectMaster ? 1 : 0"></div></div>
                            <div></div>
                            <div></div>
                        </div>

                        <!-- Direct Master Extra Fields -->
                        <div class="form-grid-4" x-show="isDirectMaster" x-cloak>
                            <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container"><x-inline-select name="dm_customer_id" :options="$agents" module="trade-partner" x-model="form.dm_customer_id" class="form-control-gf" /> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_customer_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Shipper</label><div class="form-input-container"><x-inline-select name="dm_shipper_id" :options="$agents" module="trade-partner" x-model="form.dm_shipper_id" class="form-control-gf" /> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><x-inline-select name="dm_consignee_id" :options="$agents" module="trade-partner" x-model="form.dm_consignee_id" class="form-control-gf" /> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_consignee_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><x-inline-select name="dm_notify_id" :options="$agents" module="trade-partner" x-model="form.dm_notify_id" class="form-control-gf" /> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_notify_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                        </div>
                        
                        <div class="form-grid-4" x-show="isDirectMaster" x-cloak>
                            <div class="form-group-gf"><label class="form-label-gf">Bill To</label><div class="form-input-container"><x-inline-select name="dm_bill_to_id" :options="$agents" module="trade-partner" x-model="form.dm_bill_to_id" class="form-control-gf" /> <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_bill_to_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container"><select name="dm_sales_person_id" class="form-control-gf" x-model="form.dm_sales_person_id"><option value="">Select...</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container"><select name="cargo_type" class="form-control-gf" x-model="form.cargo_type"><option value="">Select...</option><option value="GENERAL CARGO">GENERAL CARGO</option><option value="HAZARDOUS">HAZARDOUS</option><option value="REEFER">REEFER</option><option value="DANGEROUS">DANGEROUS</option><option value="OVERSIZE">OVERSIZE</option></select></div></div>
                            <div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Departure</label><div class="form-input-container"><x-inline-select name="dep_port_id" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}" :options="$ports" module="port" x-model="form.departure" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Destination</label><div class="form-input-container"><x-inline-select name="dst_port_id" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}" :options="$ports" module="port" x-model="form.destination" class="form-control-gf" /></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Flight No.</label><div class="form-input-container"><input type="text" name="flight_no" class="form-control-gf" x-model="form.flight_no"></div></div>
                            <div></div>

                            <!-- Row 2 -->
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*ETD</label><div class="form-input-container"><input type="date" name="etd" class="form-control-gf" x-model="form.etd" required></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf"><label class="form-label-gf">ATD</label><div class="form-input-container"><input type="date" name="atd" class="form-control-gf" x-model="form.atd"></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf"><label class="form-label-gf">ETA</label><div class="form-input-container"><input type="date" name="eta" class="form-control-gf" x-model="form.eta" style="background:#fff8e1;"></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf"><label class="form-label-gf">ATA</label><div class="form-input-container"><input type="date" name="ata" class="form-control-gf" x-model="form.ata"></div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf"><label class="form-label-gf">Cargo Ready Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="form.cargo_ready_date"></div></div>
                        </div>

                        <!-- DYNAMIC ROUTE / TRANSPORTATION SECTION -->
                        <div class="portlet light bordered" style="margin-bottom: 15px;">
                            <div class="portlet-title" style="min-height: 30px; border-bottom: 1px solid #eee; margin-bottom: 0;">
                                <div class="caption" style="padding: 5px 0;">
                                    <span class="caption-subject" style="font-size: 13px; font-weight: bold; color: #3b82f6;"><i class="fa fa-route"></i> Route / Transportation</span>
                                </div>
                            </div>
                            <div class="portlet-body" style="padding: 10px 0;">
                                <div style="overflow-x: auto;">
                                    <table class="table-custom" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%;"></th>
                                                <th style="width: 15%;">Airport</th>
                                                <th style="width: 12%;">ETA</th>
                                                <th style="width: 12%;">ATA</th>
                                                <th style="width: 12%;">ETD</th>
                                                <th style="width: 12%;">ATD</th>
                                                <th style="width: 12%;">Flight No.</th>
                                                <th style="width: 10%;">Carrier</th>
                                                <th style="width: 5%; text-align: center;">
                                                    <button type="button" @click="addRoute" class="btn-tool-icon" style="color: #3b82f6;" title="Add Route Row"><i class="fa fa-plus"></i></button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(route, idx) in form.routes" :key="idx">
                                                <tr>
                                                    <td style="font-weight: 600; color: #475569;" x-text="idx === 0 ? 'Departure' : (idx === form.routes.length - 1 && form.routes.length > 1 ? 'Final Destination' : 'Trans ' + idx)"></td>
                                                    <td style="padding: 2px;">
                                                        <div style="display:flex;">
                                                            <select :name="'routes['+idx+'][airport_id]'" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"class="form-control-gf" x-model="route.airport_id" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                                <option value="">Select...</option>
                                                                @foreach($ports as $port)
                                                                    <option value="{{ $port->id }}">{{ $port->code ? $port->code . ' - ' : '' }}{{ $port->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="datetime-local" :name="'routes['+idx+'][eta]'" class="form-control-gf" x-model="route.eta" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="datetime-local" :name="'routes['+idx+'][ata]'" class="form-control-gf" x-model="route.ata" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="datetime-local" :name="'routes['+idx+'][etd]'" class="form-control-gf" x-model="route.etd" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="datetime-local" :name="'routes['+idx+'][atd]'" class="form-control-gf" x-model="route.atd" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="text" :name="'routes['+idx+'][flight_no]'" class="form-control-gf" x-model="route.flight_no" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <div style="display:flex;">
                                                            <select :name="'routes['+idx+'][carrier_id]'" class="form-control-gf" x-model="route.carrier_id" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                                <option value="">Select...</option>
                                                                @foreach($carriers as $carrier)
                                                                    <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td style="padding: 2px; text-align: center;">
                                                        <button type="button" @click="removeRoute(idx)" class="btn-tool-icon" style="color: #ef4444;" title="Remove Route"><i class="fa fa-trash-o"></i></button>
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-if="!form.routes || form.routes.length === 0">
                                                <tr>
                                                    <td colspan="9" style="text-align: center; color: #999; padding: 10px;">No routes added. Click + to add a route.</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Weight Row -->
                        <div style="height: 15px;"></div>
                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Package</label>
                                <div class="form-input-container" style="gap:2px;">
                                    <input type="number" step="any" name="pkg_qty" class="form-control-gf" style="width:40%;" x-model="form.pkg_qty">
                                    <select name="pkg_unit_id" class="form-control-gf" style="width:60%;" x-model="form.pkg_unit_id">
                                        <option value="">Select...</option>
                                        @foreach($packageUnits as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Gross Weight</label>
                                <div class="form-input-container" style="gap:4px; align-items:center;">
                                    <input type="number" step="any" name="gross_weight" class="form-control-gf" style="flex:1;" x-model="form.gross_weight"> <span style="font-size:10px;">KG</span>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;" @click="openDimensionsModal('mawb')">Set Dimensions</button></div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">Buying Rate</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" name="buying_rate" class="form-control-gf" style="flex:1;" x-model="form.buying_rate"> per <x-inline-select name="buying_rate_unit" :options="$agents" module="trade-partner" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Gross Weight</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">KG</span> <input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">LB</span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;" @click="sumPackageWeight()">Sum Package & Weight</button></div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf"><label class="form-label-gf">Selling Rate</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" name="selling_rate" class="form-control-gf" style="flex:1;" x-model="form.selling_rate"> per <x-inline-select name="selling_rate_unit" :options="$agents" module="trade-partner" class="form-control-gf" /></div></div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Chargeable Weight</label>
                                <div class="form-input-container" style="gap:4px; align-items:center;">
                                    <input type="number" step="any" name="chargeable_weight" class="form-control-gf" style="flex:1;" x-model="form.chargeable_weight"> <span style="font-size:10px;">KG</span>
                                </div>
                            </div>
                            <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                            <div></div>

                            <!-- Row 4 -->
                            <div class="form-group-gf"><label class="form-label-gf">Volume</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="number" step="any" name="volume" class="form-control-gf" style="flex:1;" x-model="form.volume"> <span style="font-size:10px;">CBM</span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Chargeable Wt</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">KG</span> <input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">LB</span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                            <div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">D.V. Carriage</label><div class="form-input-container"><input type="text" name="dv_carriage" class="form-control-gf" x-model="form.dv_carriage"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">D.V. Customs</label><div class="form-input-container"><input type="text" name="dv_customs" class="form-control-gf" x-model="form.dv_customs"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">WT/VAL</label><div class="form-input-container" style="font-size:10px; gap:4px; justify-content: flex-start; align-items:center;"><input type="radio" name="wt_val" value="P" x-model="form.wt_val"> PPD <input type="radio" name="wt_val" value="C" x-model="form.wt_val"> COLL</div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Other</label><div class="form-input-container" style="font-size:10px; gap:4px; justify-content: flex-start; align-items:center;"><input type="radio" name="other_term" value="P" x-model="form.other_term"> PPD <input type="radio" name="other_term" value="C" x-model="form.other_term"> COLL</div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">Insurance</label><div class="form-input-container"><input type="text" name="insurance" class="form-control-gf" x-model="form.insurance"></div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 15px; margin-bottom: 5px; align-items: center; gap: 10px;">
                            <label class="form-label-gf" style="width:auto; margin:0;">Display Unit</label>
                            <select name="display_unit" class="form-control-gf" style="width: 150px;" x-model="form.display_unit"><option value="BOTH">Show Both</option><option value="KG">KG Only</option><option value="LB">LB Only</option></select>
                        </div>

                        <hr style="margin: 15px 0; border-top: 1px solid #eee;">

                        <!-- MAWB Grids -->
                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <h4 style="font-size: 11px; font-weight: 700; color: #4b77be; margin: 0; text-transform: uppercase;">Other Charges</h4>
                                <button type="button" class="btn-default-gf" style="padding: 2px 8px; font-size: 10px; height: 22px;" @click="addCharge()"><i class="fa fa-plus"></i> Add Charge</button>
                            </div>
                            <div class="table-responsive" style="border: 1px solid #e5e7eb; border-radius: 3px; background: #fff;">
                                <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom: 0;">
                                    <colgroup>
                                        <col style="width: 36px;">
                                        <col style="width: 130px;">
                                        <col>
                                        <col style="width: 100px;">
                                        <col style="width: 110px;">
                                        <col style="width: 120px;">
                                    </colgroup>
                                    <thead>
                                        <tr style="background:#f8fafc; border-bottom: 1px solid #e5e7eb;">
                                            <th style="text-align: center; padding: 6px 4px; color: #475569; font-weight: 600;">#</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Charge Code</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Description</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Term</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Rate</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(charge, idx) in form.other_charges" :key="idx">
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="text-align: center; padding: 4px; vertical-align: middle;">
                                                    <i class="fa fa-trash text-red-500 hover:text-red-700 cursor-pointer" style="font-size: 12px;" title="Delete Row" @click="removeCharge(idx)"></i>
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <input type="text" class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px;" placeholder="Code" x-model="charge.charge_code" :name="'other_charges['+idx+'][charge_code]'">
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <textarea class="form-control-gf" style="width: 100%; height: 150px !important; min-height: 150px !important; resize: vertical; padding: 6px 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="Description..." x-model="charge.description" :name="'other_charges['+idx+'][description]'"></textarea>
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <select class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px;" x-model="charge.term" :name="'other_charges['+idx+'][term]'">
                                                        <option value="P">PPD</option>
                                                        <option value="C">COLL</option>
                                                    </select>
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <input type="number" step="any" class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px; text-align: right;" placeholder="0.00" x-model="charge.rate" :name="'other_charges['+idx+'][rate]'">
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <input type="number" step="any" class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px; text-align: right;" placeholder="0.00" x-model="charge.amount" :name="'other_charges['+idx+'][amount]'">
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="!form.other_charges || form.other_charges.length === 0">
                                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 12px; font-style: italic;">No other charges added. Click "+ Add Charge" to add rows.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <h4 style="font-size: 11px; font-weight: 700; color: #4b77be; margin: 0; text-transform: uppercase;">Accounting Information</h4>
                                <button type="button" class="btn-default-gf" style="padding: 2px 8px; font-size: 10px; height: 22px;" @click="addAcctInfo()"><i class="fa fa-plus"></i> Add Info</button>
                            </div>
                            <div class="table-responsive" style="border: 1px solid #e5e7eb; border-radius: 3px; background: #fff;">
                                <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom: 0;">
                                    <colgroup>
                                        <col style="width: 36px;">
                                        <col style="width: 180px;">
                                        <col>
                                    </colgroup>
                                    <thead>
                                        <tr style="background:#f8fafc; border-bottom: 1px solid #e5e7eb;">
                                            <th style="text-align: center; padding: 6px 4px; color: #475569; font-weight: 600;">#</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Information Code</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Accounting Information</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(info, idx) in form.accounting_info" :key="idx">
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="text-align: center; padding: 4px; vertical-align: middle;">
                                                    <i class="fa fa-trash text-red-500 hover:text-red-700 cursor-pointer" style="font-size: 12px;" title="Delete Row" @click="removeAcctInfo(idx)"></i>
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <input type="text" class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px;" placeholder="Code" x-model="info.code" :name="'accounting_info['+idx+'][code]'">
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <textarea class="form-control-gf" style="width: 100%; height: 150px !important; min-height: 150px !important; resize: vertical; padding: 6px 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="Accounting information details..." x-model="info.info" :name="'accounting_info['+idx+'][info]'"></textarea>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="!form.accounting_info || form.accounting_info.length === 0">
                                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 12px; font-style: italic;">No accounting information added.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <h4 style="font-size: 11px; font-weight: 700; color: #4b77be; margin: 0; text-transform: uppercase;">Commodity</h4>
                                <button type="button" class="btn-default-gf" style="padding: 2px 8px; font-size: 10px; height: 22px;" @click="addCommodity()"><i class="fa fa-plus"></i> Add Commodity</button>
                            </div>
                            <div class="table-responsive" style="border: 1px solid #e5e7eb; border-radius: 3px; background: #fff;">
                                <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom: 0;">
                                    <colgroup>
                                        <col style="width: 36px;">
                                        <col>
                                        <col style="width: 160px;">
                                    </colgroup>
                                    <thead>
                                        <tr style="background:#f8fafc; border-bottom: 1px solid #e5e7eb;">
                                            <th style="text-align: center; padding: 6px 4px; color: #475569; font-weight: 600;">#</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Description</th>
                                            <th style="padding: 6px 8px; color: #475569; font-weight: 600;">HTS No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(commodity, idx) in form.commodities" :key="idx">
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="text-align: center; padding: 4px; vertical-align: middle;">
                                                    <i class="fa fa-trash text-red-500 hover:text-red-700 cursor-pointer" style="font-size: 12px;" title="Delete Row" @click="removeCommodity(idx)"></i>
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <textarea class="form-control-gf" style="width: 100%; height: 150px !important; min-height: 150px !important; resize: vertical; padding: 6px 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="Commodity description..." x-model="commodity.description" :name="'commodities['+idx+'][description]'"></textarea>
                                                </td>
                                                <td style="padding: 4px 6px; vertical-align: top;">
                                                    <input type="text" class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px;" placeholder="HTS No." x-model="commodity.hts_no" :name="'commodities['+idx+'][hts_no]'">
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="!form.commodities || form.commodities.length === 0">
                                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 12px; font-style: italic;">No commodities added.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div style="margin-top: 15px;">
                            <label class="form-label-gf" style="margin-bottom: 4px;">Internal Memo</label>
                            <div class="form-input-container">
                                <textarea class="form-control-gf textarea-compact" style="width: 100%; height: 150px !important; min-height: 150px !important; padding: 6px 8px; resize: vertical; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="Internal memo / remarks..." x-model="form.internal_remark" name="internal_remark"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- House B/L (HAWB) Section -->
                <template x-for="(hawb, index) in hawbs" :key="index">
                    <div class="portlet light" x-show="activeTab === 'basic'" :style="activeTab === 'basic' ? '' : 'display: none !important;'" style="margin-top: 5px;">
                        <div class="portlet-title" style="background: #f2bc00; color: #fff; cursor: pointer; min-height: 24px; padding: 2px 10px;" @click="hawbs[index].show = !hawbs[index].show">
                            <span class="caption-subject" style="color: #fff; font-size: 11px;"><i class="fa fa-user"></i> HAWB Information <small style="color:rgba(255,255,255,0.8); margin-left: 10px; font-weight: normal;" x-text="'OP: ' + hawb.op"></small></span>
                            <div class="actions" style="display: flex; gap: 8px; align-items: center;" @click.stop>
                                <!-- HAWB Tools Dropdown -->
                                <div style="position: relative;" x-data="{ openHawbMenu: false }" @click.away="openHawbMenu = false">
                                    <button type="button" @click="openHawbMenu = !openHawbMenu"
                                        style="display: flex; align-items: center; gap: 4px; background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(0,0,0,0.1); border-radius: 3px; padding: 2px 8px; font-size: 11px; font-weight: 600; color: #374151; cursor: pointer; height: 22px;">
                                        <i class="fa fa-wrench" style="color: #4b5563; font-size: 10px;"></i> Docs <i class="fa fa-angle-down" style="font-size: 10px;"></i>
                                    </button>
                                    <div x-show="openHawbMenu" style="position: absolute; top: 100%; right: 0; margin-top: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15); z-index: 100; min-width: 180px; text-align: left; display: none;">
                                        <ul style="list-style: none; padding: 4px 0; margin: 0;">
                                            <li><button type="button" @click="printHawb(index); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-print" style="width:14px; text-align:center; margin-right:4px;"></i> HAWB Print</button></li>
                                            <li><button type="button" @click="saved ? hawbBlockAction(index) : null; openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-lock" style="width:14px; text-align:center; margin-right:4px;"></i> Block</button></li>
                                            <li><button type="button" @click="saved ? hawbCopyAction(index) : null; openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-copy" style="width:14px; text-align:center; margin-right:4px;"></i> Copy</button></li>
                                            <li><button type="button" @click="showToast('info', 'Copy To - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-share" style="width:14px; text-align:center; margin-right:4px;"></i> Copy To</button></li>
                                            <li><button type="button" @click="showToast('info', 'Pre-Alert - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-bell-o" style="width:14px; text-align:center; margin-right:4px;"></i> Pre-Alert</button></li>
                                            <li><button type="button" @click="showToast('info', 'Dangerous Goods - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-warning" style="width:14px; text-align:center; margin-right:4px; color:#e55a00;"></i> Dangerous Goods</button></li>
                                            <li><button type="button" @click="saved && hawbs[index] && hawbs[index].id ? window.open('/air-export/' + form.id + '/hawb-profit-report/' + hawbs[index].id, '_blank') : showToast('info', 'Save HAWB first.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-bar-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Report</button></li>
                                        </ul>
                                    </div>
                                </div>
                                <i @click.stop="removeHawb(index)" class="fa fa-times" style="font-size: 12px; opacity: 0.8; cursor: pointer; color: #fff; margin-left: 4px;" title="Delete HAWB"></i>
                                <i class="fa fa-angle-down transition-transform" :class="hawbs[index].show ? 'rotate-180' : ''" style="font-size: 12px; color: #fff;" @click="hawbs[index].show = !hawbs[index].show"></i>
                            </div>
                        </div>
                        <div class="portlet-body" x-show="hawbs[index].show" style="background: #f9f9f9; padding: 10px;">
                            
                            <div class="memo-section" style="margin-bottom: 15px;">
                                <div class="memo-header" @click="hawb.showMemo = !hawb.showMemo" style="background:#fff; border:1px solid #eef1f5; padding:4px 10px; display:flex; justify-content:space-between; align-items:center; cursor:pointer;">
                                    <span style="font-size:11px; font-weight:600; color:#666;">Note</span>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <button class="btn-default-gf" style="background:#eee; border:1px solid #ccc; font-size:10px; padding:1px 8px;" @click.stop="">Document (0) <i class="fa fa-external-link"></i></button>
                                        <i class="fa" :class="hawb.showMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                                    </div>
                                </div>
                                <div class="memo-body" x-show="hawb.showMemo" style="border:1px solid #eef1f5; border-top:none; background:#fff; padding:10px;">
                                    <div style="display: flex; gap: 10px;">
                                        <div style="flex: 2; border: 1px solid #dcdcdc; min-height: 40px; background: #fff; padding: 5px; color: #999; text-align: center;">No records found.</div>
                                        <div style="flex: 1;"><textarea class="form-control-gf" style="height: 60px; resize: none;" placeholder="Reminder content..."></textarea></div>
                                    </div>
                                </div>
                            </div>

                            <div style="padding: 5px 0;">
                                <input type="hidden" :name="'hbls[' + index + '][id]'" :value="hawb.id">
                            <div class="form-grid-4">
                                <!-- Row 1 -->
                                <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*HAWB No.</label><div class="form-input-container"><input type="text" :name="'hbls[' + index + '][hawb_no]'" class="form-control-gf" x-model="hawb.hawb_no" :required="!isDirectMaster"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Actual Shipper</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][shipper_id]'" :options="$agents" module="trade-partner" x-model="hawb.shipper" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Bill To</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][bill_to]'" :options="$agents" module="trade-partner" x-model="hawb.bill_to" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][notify_party_id]'" :options="$agents" module="trade-partner" x-model="hawb.notify" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                
                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Booking No.</label><div class="form-input-container"><input type="text" :name="'hbls[' + index + '][booking_no]'" class="form-control-gf" x-model="hawb.booking_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][customer_id]'" :options="$agents" module="trade-partner" x-model="hawb.customer" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][consignee_id]'" :options="$agents" module="trade-partner" x-model="hawb.consignee" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][oversea_agent_id]'" :options="$agents" module="trade-partner" x-model="hawb.oversea_agent" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                
                                <!-- Row 3 -->
                                <div class="form-group-gf"><label class="form-label-gf">Quotation No.</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][quotation_no]'" :options="$agents" module="trade-partner" x-model="hawb.quotation_no" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container">
                                    <select :name="'hbls[' + index + '][sales_person_id]'" x-model="hawb.sales" class="form-control-gf">
                                        <option value="">Select...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Booking Date</label><div class="form-input-container"><input type="date" :name="'hbls[' + index + '][booking_date]'" class="form-control-gf" x-model="hawb.booking_date"></div></div>
                                <div></div> <!-- Empty cell for grid alignment -->

                                <!-- Row 4 -->
                                <div class="form-group-gf"><label class="form-label-gf">ITN No.</label><div class="form-input-container"><input type="text" :name="'hbls[' + index + '][itn_no]'" class="form-control-gf" x-model="hawb.itn_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="hawb.op" disabled style="background:#eee;"></div></div>
                                <div></div>
                                <div></div>
                            </div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div class="form-grid-4">
                                <!-- Row 1 -->
                            
<div class="form-group-gf">
    <label class="form-label-gf">Departure</label>

    <div class="form-input-container">
        <select
            class="form-control-gf"
            :name="'hbls[' + index + '][departure]'"
            x-model="hawb.departure"
            data-ajax-search="true"
            data-search-url="{{ route('ports.search') }}"
        >
            <option value="">Select Departure Port</option>

            @foreach ($ports as $port)
                <option value="{{ $port->id }}">
                    {{ $port->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group-gf">
    <label class="form-label-gf">Destination</label>

    <div class="form-input-container">
        <select
            class="form-control-gf"
            :name="'hbls[' + index + '][destination]'"
            x-model="hawb.destination"
            data-ajax-search="true"
            data-search-url="{{ route('ports.search') }}"
        >
            <option value="">Select Destination Port</option>

            @foreach ($ports as $port)
                <option value="{{ $port->id }}">
                    {{ $port->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group-gf">
    <label class="form-label-gf">Destination</label>

    <div class="form-input-container">
        <select
            class="form-control-gf"
            :name="'hbls[' + index + '][destination]'"
            x-model="hawb.destination"
            data-ajax-search="true"
            data-ajax-value="name"
            data-search-url="{{ route('ports.search') }}"
        >
            <option value="">Select Destination Port</option>

            @foreach ($ports as $port)
                <option value="{{ $port->name }}">
                    {{ $port->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<script>
    const etd = document.querySelector('[name="etd"]');

console.log({
    value: etd?.value,
    visible: !!etd?.getClientRects().length,
    display: etd ? getComputedStyle(etd).display : null
});
</script>
<div class="form-group-gf"><label class="form-label-gf">Final ETA</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="hawb.feta"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container"><select class="form-control-gf" x-model="hawb.cargo_type"><option value="GENERAL CARGO">GENERAL CARGO</option></select></div></div>

                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Cargo Pickup</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][cargo_pickup]'" :options="$agents" module="trade-partner" x-model="hawb.cargo_pickup" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Delivery To/Pier</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][delivery_to]'" :options="$agents" module="trade-partner" x-model="hawb.delivery_to" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Sales Type</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][sales_type]'" :options="$agents" module="trade-partner" x-model="hawb.sales_type" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Ship Type</label><div class="form-input-container"><select :name="'hbls[' + index + '][ship_type]'" class="form-control-gf" x-model="hawb.ship_type"><option value="NORMAL">NORMAL</option><option value="DIRECT">DIRECT</option></select></div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">
                            
                            <div class="form-grid-4">
                                <!-- Row 1 -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Package</label>
                                    <div class="form-input-container" style="gap:2px;">
                                        <input type="number" step="any" :name="'hbls[' + index + '][pkg_qty]'" class="form-control-gf" style="width:40%;" x-model="hawb.pkg_qty">
                                        <select :name="'hbls[' + index + '][pkg_unit_id]'" class="form-control-gf" style="width:60%;" x-model="hawb.pkg_unit_id">
                                            <option value="">Select...</option>
                                            @foreach($packageUnits as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Gross Weight (SHPR)</label>
                                    <div class="form-input-container" style="gap:4px; align-items:center;">
                                        <input type="number" step="any" :name="'hbls[' + index + '][gross_weight]'" class="form-control-gf" style="flex:1;" x-model="hawb.gross_weight"> <span style="font-size:10px;">KG</span>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Volume Weight</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="number" step="any" :name="'hbls[' + index + '][volume]'" class="form-control-gf" style="flex:1;" x-model="hawb.volume"> <span style="font-size:10px;">CBM</span></div></div>

                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Buying Rate</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" :name="'hbls[' + index + '][buying_rate]'" class="form-control-gf" style="flex:1;" x-model="hawb.buying_rate"> per <select class="form-control-gf" style="width:50px;"><option>KG</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Gross Weight (CNEE)</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">KG</span> <input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">LB</span></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;" @click="openDimensionsModal(index)">Set Dimensions</button></div></div>

                                <!-- Row 3 -->
                                <div class="form-group-gf"><label class="form-label-gf">Selling Rate</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" :name="'hbls[' + index + '][selling_rate]'" class="form-control-gf" style="flex:1;" x-model="hawb.selling_rate"> per <select class="form-control-gf" style="width:50px;"><option>KG</option></select></div></div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Chargeable Weight</label>
                                    <div class="form-input-container" style="gap:4px; align-items:center;">
                                        <input type="number" step="any" :name="'hbls[' + index + '][chargeable_weight]'" class="form-control-gf" style="flex:1;" x-model="hawb.chargeable_weight"> <span style="font-size:10px;">KG</span>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;" @click="if(typeof showToast === 'function') showToast('info', 'Sum Package & Weight coming soon')">Sum Package & Weight</button></div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div style="display: flex; justify-content: flex-end; margin-top: 10px; align-items: center; gap: 10px;">
                                <label class="form-label-gf" style="width:auto; margin:0;">Display Unit</label>
                                <select :name="'hbls[' + index + '][display_unit]'" class="form-control-gf" style="width: 150px;" x-model="hawb.display_unit"><option value="BOTH">Show Both</option><option value="KG">KG Only</option><option value="LB">LB Only</option></select>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <!-- Grid Lists for HAWB -->
                            <div style="margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <h4 style="font-size: 11px; font-weight: 700; color: #4b77be; margin: 0; text-transform: uppercase;">Commodity / HTS No.</h4>
                                    <button type="button" class="btn-default-gf" style="padding: 2px 8px; font-size: 10px; height: 22px;" @click="addHawbCommodity(index)"><i class="fa fa-plus"></i> Add Row</button>
                                </div>
                                <div class="table-responsive" style="border: 1px solid #e5e7eb; border-radius: 3px; background: #fff;">
                                    <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom: 0;">
                                        <colgroup>
                                            <col style="width: 36px;">
                                            <col>
                                            <col style="width: 140px;">
                                            <col style="width: 140px;">
                                        </colgroup>
                                        <thead>
                                            <tr style="background:#f8fafc; border-bottom: 1px solid #e5e7eb;">
                                                <th style="text-align: center; padding: 6px 4px; color: #475569; font-weight: 600;">#</th>
                                                <th style="padding: 6px 8px; color: #475569; font-weight: 600;">Commodity Description</th>
                                                <th style="padding: 6px 8px; color: #475569; font-weight: 600;">HTS No.</th>
                                                <th style="padding: 6px 8px; color: #475569; font-weight: 600;">P.O. No.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(commodity, cidx) in hawb.commodities" :key="cidx">
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="text-align: center; padding: 4px; vertical-align: middle;">
                                                        <i class="fa fa-trash text-red-500 hover:text-red-700 cursor-pointer" style="font-size: 12px;" title="Delete Row" @click="removeHawbCommodity(index, cidx)"></i>
                                                    </td>
                                                    <td style="padding: 4px 6px; vertical-align: top;">
                                                        <textarea class="form-control-gf" style="width: 100%; height: 150px !important; min-height: 150px !important; resize: vertical; padding: 6px 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="Description..." x-model="commodity.description" :name="'hbls['+index+'][commodities]['+cidx+'][description]'"></textarea>
                                                    </td>
                                                    <td style="padding: 4px 6px; vertical-align: top;">
                                                        <input type="text" class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px;" placeholder="HTS No." x-model="commodity.hts_no" :name="'hbls['+index+'][commodities]['+cidx+'][hts_no]'">
                                                    </td>
                                                    <td style="padding: 4px 6px; vertical-align: top;">
                                                        <input type="text" class="form-control-gf" style="width: 100%; height: 26px; padding: 2px 6px; font-size: 11px;" placeholder="P.O. No." x-model="commodity.po_no" :name="'hbls['+index+'][commodities]['+cidx+'][po_no]'">
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="!hawb.commodities || hawb.commodities.length === 0">
                                                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 12px; font-style: italic;">No commodities added. Click "+ Add Row" to add.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <hr style="margin: 12px 0; border-top: 1px solid #e5e7eb;">

                            <!-- Mark & Description Grid -->
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 12px;">
                                <div>
                                    <label class="form-label-gf" style="margin-bottom: 4px; font-weight: 600; color: #374151;">Mark</label>
                                    <div class="form-input-container">
                                        <textarea class="form-control-gf textarea-compact" style="width: 100%; height: 150px !important; min-height: 150px !important; resize: vertical; padding: 6px 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="Marks & Numbers..." x-model="hawb.mark" :name="'hbls['+index+'][mark]'"></textarea>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label-gf" style="margin-bottom: 4px; font-weight: 600; color: #374151;">Description</label>
                                    <div class="form-input-container">
                                        <textarea class="form-control-gf textarea-compact" style="width: 100%; height: 150px !important; min-height: 150px !important; resize: vertical; padding: 6px 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="Goods Description..." x-model="hawb.description" :name="'hbls['+index+'][description]'"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Remark Box -->
                            <div style="margin-top: 8px;">
                                <label class="form-label-gf" style="margin-bottom: 4px; font-weight: 600; color: #374151;">Remark</label>
                                <div class="form-input-container">
                                    <textarea class="form-control-gf textarea-compact" style="width: 100%; height: 150px !important; min-height: 150px !important; resize: vertical; padding: 6px 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px;" placeholder="HAWB Remark..." x-model="hawb.remark" :name="'hbls['+index+'][remark]'"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end" x-show="activeTab === 'basic'" :style="activeTab === 'basic' ? '' : 'display: none !important;'" style="margin-top: 5px; margin-bottom: 20px;">
                    <button type="button" @click="addHawb" class="btn-freightx" style="background:#f2bc00; padding: 4px 15px; font-size: 11px; border-radius: 2px;"><i class="fa fa-plus"></i> ADD HAWB</button>
                </div>
            </div>

            <!-- CHARGES TAB -->
            <div x-show="activeTab === 'charges'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div class="portlet-body">
                        @include('partials.charges-invoice-modal')

                        <script>
                            if (!window.tradePartnersList) {
                                window.tradePartnersList = {!! json_encode(collect($agents ?? [])->map(function($tp) { return ['id' => $tp->id, 'name' => $tp->name, 'label' => $tp->name . ' (' . ($tp->type ?? 'Partner') . ')']; })->toArray()) !!};
                            }
                        </script>



                        <!-- Filter Row -->
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; padding: 6px 8px; background: #fff; border: 1px solid #e7ecf1; border-radius: 4px; align-items: center;">
                            <template x-for="(filter, idx) in getChargeFilters()">
                                <span style="background: #f1f3f6; padding: 2px 10px; border-radius: 3px; font-size: 11px; font-weight: 500; cursor: pointer;" :class="activeChargeFilter === filter.value ? 'bg-green' : ''" @click="activeChargeFilter = filter.value" x-text="filter.name"></span>
                            </template>
                            <div style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 11px; color: #666;">A/R :</span>
                                <span style="font-size: 11px; font-weight: 600; color: #16a34a;" x-text="calculateArCharges().toFixed(2)">0.00</span>
                                <span style="font-size: 11px; color: #666;">A/P :</span>
                                <span style="font-size: 11px; font-weight: 600; color: #dc2626;" x-text="calculateApCharges().toFixed(2)">0.00</span>
                                <span style="font-size: 11px; color: #666;">Total :</span>
                                <span style="font-size: 11px; font-weight: 700;" x-text="calculateTotalCharges().toFixed(2)">0.00</span>
                            </div>
                        </div>

                        <!-- Charges Table -->
                        <div class="table-responsive charges-table-container" style="margin-bottom: 15px; min-height: 250px;">
                            <table class="table-custom" style="font-size: 11px; min-width: 1200px;">
                                <thead>
                                    <tr style="background: #f1f3f6;">
                                        <th style="padding: 6px 8px; width: 30px; text-align: center;">
                                            <input type="checkbox" @change="toggleAllCharges($event)" style="width: 14px; height: 14px; cursor: pointer; accent-color: #3b82f6;" title="Select All">
                                        </th>
                                        <th style="padding: 6px 8px;">Party</th>
                                        <th style="padding: 6px 8px;">Party Name</th>
                                        <th style="padding: 6px 8px;">SAL</th>
                                        <th style="padding: 6px 8px;">P/R</th>
                                        <th style="padding: 6px 8px;">PP/C</th>
                                        <th style="padding: 6px 8px;">Chrg</th>
                                        <th style="padding: 6px 8px;">Curr.</th>
                                        <th style="padding: 6px 8px;">Rate</th>
                                        <th style="padding: 6px 8px;">Qty</th>
                                        <th style="padding: 6px 8px;">Q.Type</th>
                                        <th style="padding: 6px 8px;">F.Amnt</th>
                                        <th style="padding: 6px 8px;">ROE</th>
                                        <th style="padding: 6px 8px;">VAT</th>
                                        <th style="padding: 6px 8px;">L.Amnt</th>
                                        <th style="padding: 6px 8px;">Inv/Dr/Cr/No</th>
                                        <th style="padding: 6px 8px;">Fin. Dt</th>
                                        <th style="padding: 6px 8px;">MBL#</th>
                                        <th style="padding: 6px 8px;">Remark</th>
                                        <th style="padding: 6px 8px; width: 30px; text-align: center;">
                                            <button type="button" @click="addNewCharge" class="btn-tool-icon-blue" style="border:none; padding: 2px 6px; border-radius: 2px; cursor: pointer; display: inline-block;" title="Add Row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(charge, idx) in chargesList" :key="idx">
                                        <tr x-show="shouldShowChargeRow(charge)" :class="{ 'bg-blue-50': charge._selected }">
                                            <td style="padding: 5px 8px; text-align: center;">
                                                <input type="checkbox" x-model="charge._selected" style="width: 14px; height: 14px; cursor: pointer; accent-color: #3b82f6; display: block; margin: 0 auto;" title="Select for Invoicing">
                                                <input type="hidden" :name="'charges[' + idx + '][id]'" :value="charge.id">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <select class="form-control-gf" style="width: 80px; font-size: 10px;" x-model="charge.party" :name="'charges[' + idx + '][party]'">
                                                    <option value="Freight">Freight</option>
                                                    <option value="THC">THC</option>
                                                    <option value="Handling">Handling</option>
                                                    <option value="Documentation">Documentation</option>
                                                    <option value="Customs">Customs</option>
                                                </select>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <div x-data="{ 
                                                    open: false, 
                                                    search: charge.party_name || '',
                                                    get filteredAgents() {
                                                        let list = window.tradePartnersList || [];
                                                        let q = (this.search || '').toLowerCase();
                                                        if (q === '') return list;
                                                        return list.filter(tp => (tp.label || '').toLowerCase().includes(q));
                                                    }
                                                }" @click.away="open = false" style="position: relative;">
                                                    <div style="position: relative;">
                                                        <input type="text" class="form-control-gf" style="width: 120px; font-size: 10px; padding-right: 20px;" 
                                                            x-model="search" 
                                                            @input="charge.party_name = search; open = true"
                                                            @focus="open = true"
                                                            @click="open = true"
                                                            :name="'charges[' + idx + '][party_name]'" 
                                                            placeholder="Search Party..." 
                                                            autocomplete="off">
                                                        <input type="hidden" :name="'charges[' + idx + '][party_name_id]'" x-model="charge.party_name_id">
                                                        <i class="fa fa-caret-down" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); color: #888; cursor: pointer;" @click="open = !open"></i>
                                                    </div>
                                                    
                                                    <div x-show="open" style="display: none; position: absolute; z-index: 9999; background: white; border: 1px solid #ccc; max-height: 150px; overflow-y: auto; width: 180px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 2px;">
                                                        <template x-for="tp in filteredAgents" :key="tp.name">
                                                            <div style="padding: 6px 10px; font-size: 10px; cursor: pointer; border-bottom: 1px solid #eee;" 
                                                                 @click="charge.party_name = tp.name; charge.party_name_id = tp.id; search = tp.name; open = false"
                                                                 @mouseover="$el.style.backgroundColor='#f3f4f6'"
                                                                 @mouseout="$el.style.backgroundColor='transparent'"
                                                                 x-text="tp.label"></div>
                                                        </template>
                                                        <div x-show="filteredAgents.length === 0" style="padding: 6px 10px; font-size: 10px; color: #999;">No match found</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <select class="form-control-gf" style="width: 50px;" x-model="charge.sal" :name="'charges[' + idx + '][sal]'">
                                                    <option value="Sea">Sea</option>
                                                    <option value="Air">Air</option>
                                                    <option value="Land">Land</option>
                                                </select>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <select class="form-control-gf" style="width: 55px;" x-model="charge.pr" :name="'charges[' + idx + '][pr]'">
                                                    <option value="Rec">Rec</option>
                                                    <option value="Pay">Pay</option>
                                                </select>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <select class="form-control-gf" style="width: 60px;" x-model="charge.ppc" :name="'charges[' + idx + '][ppc]'">
                                                    <option value="Colle">Colle</option>
                                                    <option value="Proj">Proj</option>
                                                    <option value="Prepaid">Prepaid</option>
                                                </select>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="text" class="form-control-gf" style="width: 60px;" x-model="charge.chrg_code" :name="'charges[' + idx + '][chrg_code]'" placeholder="Code">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <select class="form-control-gf" style="width: 65px;" x-model="charge.currency" :name="'charges[' + idx + '][currency]'">
                                                    <option value="">Select...</option>
                                                    @foreach($currencies as $currency)
                                                        <option value="{{ $currency->code }}">{{ $currency->code }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="number" class="form-control-gf" style="width: 60px; text-align: right;" x-model="charge.rate" :name="'charges[' + idx + '][rate]'" @input="updateChargeAmount(idx)">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="number" class="form-control-gf" style="width: 50px; text-align: right;" x-model="charge.qty" :name="'charges[' + idx + '][qty]'" @input="updateChargeAmount(idx)">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <select class="form-control-gf" style="width: 65px;" x-model="charge.qty_type" :name="'charges[' + idx + '][qty_type]'">
                                                    <option value="">Select...</option>
                                                    @foreach($packageUnits as $unit)
                                                        <option value="{{ $unit->code }}">{{ $unit->code }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <span x-text="((parseFloat(charge.rate) || 0) * (parseFloat(charge.qty) || 0)).toFixed(2)" style="display: inline-block; min-width: 50px; text-align: right;">0.00</span>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="number" class="form-control-gf" style="width: 55px; text-align: right;" x-model="charge.roe" :name="'charges[' + idx + '][roe]'" @input="updateLocalAmount(idx)">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="number" class="form-control-gf" style="width: 50px; text-align: right;" x-model="charge.vat" :name="'charges[' + idx + '][vat]'" @input="updateLocalAmount(idx)">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <span x-text="calculateLocalAmount(charge).toFixed(2)" style="display: inline-block; min-width: 65px; text-align: right; font-weight: 500;">0.00</span>
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <span x-text="charge.inv_no || '-'" style="display: inline-block; min-width: 90px; color: #3b82f6; font-weight: bold;"></span>
                                                <input type="hidden" :name="'charges[' + idx + '][inv_no]'" :value="charge.inv_no">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="date" class="form-control-gf" style="width: 85px;" x-model="charge.financial_date" :name="'charges[' + idx + '][financial_date]'">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="text" class="form-control-gf" style="width: 80px;" x-model="charge.mbl_no" :name="'charges[' + idx + '][mbl_no]'" placeholder="MBL#">
                                            </td>
                                            <td style="padding: 5px 8px;">
                                                <input type="text" class="form-control-gf" style="width: 140px;" x-model="charge.remark" :name="'charges[' + idx + '][remark]'" placeholder="Remark">
                                            </td>
                                            <td style="padding: 5px 8px; text-align: center;">
                                                <button type="button" @click="removeCharge(idx)" class="btn-tool-icon" style="color: #ef4444; border:none; padding: 2px; background: transparent; cursor: pointer; display: inline-block;" title="Delete Row">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Buttons Row - Exactly as per Ocean Import -->
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-start; align-items: center; padding: 10px 0; border-top: 1px solid #e7ecf1;">
                            <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="addNewCharge">Parcs</button>
                            <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="openCertificateModal">Certificate</button>
                            <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="applyTemplate">Template</button>
                            <button type="button" class="btn-freightx" style="background: #16a34a; color: white; border: none; padding: 6px 14px; font-weight: 600; border-radius: 3px; font-size: 11px; cursor: pointer;" @click="createInvoice">Generate Invoice</button>

                            <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="prorataCharges">Prorata</button>
                            <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="setDefaultCharges">Default</button>
                            <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="reloadCharges">Reload</button>
                            <button type="button" class="btn-freightx" style="background: #4b77be; padding: 6px 20px; font-size: 11px;" @click="saveCharges">Save</button>
                        </div>

                        <!-- Dropdown for Multiple Options -->
                        <div style="position: relative; margin-top: 10px; display: flex; justify-content: flex-end;">
                            <div class="dropdown" x-data="{ open: false }">
                                <button type="button" @click="open = !open" class="btn-default-gf" style="background: #f1f3f6; border: 1px solid #ccc; padding: 5px 12px; font-size: 11px; display: flex; align-items: center; gap: 5px;">
                                    More Actions <i class="fa fa-angle-down"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" style="position: absolute; bottom: 100%; right: 0; margin-bottom: 5px; background: #fff; border: 1px solid #ccc; border-radius: 4px; min-width: 180px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 100;">
                                    <ul style="list-style: none; margin: 0; padding: 5px 0;">
                                        <li><button type="button" class="dropdown-item" style="width: 100%; text-align: left; padding: 6px 12px; border: none; background: none; font-size: 11px; cursor: pointer;" @click="exportChargesToExcel">Export to Excel</button></li>
                                        <li><button type="button" class="dropdown-item" style="width: 100%; text-align: left; padding: 6px 12px; border: none; background: none; font-size: 11px; cursor: pointer;" @click="printCharges">Print Charges</button></li>
                                        <li><hr style="margin: 4px 0;"></li>
                                        <li><button type="button" class="dropdown-item" style="width: 100%; text-align: left; padding: 6px 12px; border: none; background: none; font-size: 11px; cursor: pointer;" @click="deleteAllCharges">Delete All Charges</button></li>
                                        <li><button type="button" class="dropdown-item" style="width: 100%; text-align: left; padding: 6px 12px; border: none; background: none; font-size: 11px; cursor: pointer;" @click="duplicateSelectedCharges">Duplicate Selected</button></li>
                                        <li><hr style="margin: 4px 0;"></li>
                                        <li><button type="button" class="dropdown-item" style="width: 100%; text-align: left; padding: 6px 12px; border: none; background: none; font-size: 11px; cursor: pointer;" @click="bulkUpdateCurrency">Bulk Update Currency</button></li>
                                        <li><button type="button" class="dropdown-item" style="width: 100%; text-align: left; padding: 6px 12px; border: none; background: none; font-size: 11px; cursor: pointer;" @click="applyVatToAll">Apply VAT to All</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- DOC CENTER TAB (Placeholder) -->
            <div x-show="activeTab === 'doc'" :style="activeTab === 'doc' ? 'display: flex !important;' : 'display: none !important;'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title"><span class="caption-subject">Doc Center</span></div>
                    <div class="portlet-body">
                        <div style="text-align: center; color: #999; padding: 40px;">Doc Center module will be loaded here...</div>
                    </div>
                </div>
            </div>

            <!-- WORK ORDER TAB -->
            <div x-show="activeTab === 'workorder'" :style="activeTab === 'workorder' ? 'display: flex !important;' : 'display: none !important;'" class="main-grid" x-cloak style="flex-direction: row; gap: 10px;">
                <!-- Main Work Order Area (col-10) -->
                <div style="flex: 5;">
                    <div class="portlet light">
                        <div class="portlet-title" style="background: #666; color: #fff;">
                            <div class="caption">
                                <span style="font-size: 11px; margin-right: 5px;">MAWB</span>
                                <span class="caption-subject" style="color: #fff;" x-text="form.mawb_no"></span>
                            </div>
                            <div class="actions" style="display: flex; gap: 5px; align-items: center;">
                                <button type="button" class="btn-default-gf dark" @click="refreshWorkOrders"><i class="fa fa-refresh"></i> Refresh</button>
                            </div>
                        </div>
                        <div class="portlet-body" style="padding: 10px;">
                            <div style="background: #eef1f5; padding: 5px; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center;">
                                <div class="btn-group" style="display: flex; gap: 5px;">
                                    <button type="button" class="btn-freightx" style="background: #32c5d2; padding: 6px 12px; border-radius: 3px; font-size: 11px;" @click="createWorkOrder">
                                        <i class="fa fa-plus"></i> New Work Order
                                    </button>
                                    <button type="button" 
                                            class="btn-default-gf" 
                                            style="padding: 6px 12px; border-radius: 3px; font-size: 11px; transition: all 0.2s;"
                                            :style="selectedWorkOrders.length === 0 ? 'opacity: 0.5; cursor: not-allowed; background: #f5f5f5; border: 1px solid #ddd; color: #999;' : 'background: #e74c3c; color: white; border: 1px solid #c0392b; cursor: pointer;'" 
                                            :disabled="selectedWorkOrders.length === 0"
                                            @click="bulkDeleteWorkOrders">
                                        <i class="fa fa-trash"></i> Delete Selected <span x-show="selectedWorkOrders.length > 0" x-text="`(${selectedWorkOrders.length})`"></span>
                                    </button>
                                </div>
                                <div style="font-size: 10px; color: #666; font-weight: 500;">
                                    <span x-show="workOrders.length > 0" x-text="`Total: ${workOrders.length} work order(s)`"></span>
                                    <span x-show="selectedWorkOrders.length > 0" style="color: #e74c3c; font-weight: 600;" x-text="` | ${selectedWorkOrders.length} selected`"></span>
                                </div>
                            </div>
                            
                            <div x-show="loadingWorkOrders" style="text-align: center; padding: 40px;">
                                <i class="fa fa-spinner fa-spin" style="font-size: 24px; color: #32c5d2;"></i>
                                <div style="margin-top: 10px; color: #666; font-size: 11px;">Loading work orders...</div>
                            </div>

                            <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 10px;" x-show="!loadingWorkOrders">
                                <thead>
                                    <tr style="background: #a0a8b3; color: #fff;">
                                        <th style="width: 40px; text-align: center; border: 1px solid #e7ecf1; padding: 8px;">
                                            <input type="checkbox" 
                                                   @change="toggleAllWorkOrders" 
                                                   :checked="selectedWorkOrders.length === workOrders.length && workOrders.length > 0"
                                                   style="cursor: pointer; width: 14px; height: 14px;"
                                                   title="Select All">
                                        </th>
                                        <th style="text-align: center; border: 1px solid #e7ecf1;">W/O No.</th>
                                        <th style="text-align: center; border: 1px solid #e7ecf1;">Subject</th>
                                        <th style="border: 1px solid #e7ecf1;">Freight Pickup</th>
                                        <th style="border: 1px solid #e7ecf1;">Delivery</th>
                                        <th style="border: 1px solid #e7ecf1;">Vendor/Trucker</th>
                                        <th style="text-align: center; border: 1px solid #e7ecf1;">Issue Date</th>
                                        <th style="text-align: center; border: 1px solid #e7ecf1;">Last Modified</th>
                                        <th style="text-align: center; border: 1px solid #e7ecf1; width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="workOrders.length === 0">
                                        <tr>
                                            <td colspan="9" style="text-align: center; color: #999; padding: 40px;">
                                                <i class="fa fa-inbox" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                                                No work orders found. Click "New Work Order" to create one.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="(wo, index) in workOrders" :key="wo.id">
                                        <tr style="border-bottom: 1px solid #e7ecf1; transition: all 0.2s;" 
                                            :style="selectedWorkOrders.includes(wo.id) ? 'background: #e8f4fd; border-left: 3px solid #3b82f6;' : 'background: white;'"
                                            @mouseenter="$el.style.backgroundColor = selectedWorkOrders.includes(wo.id) ? '#d6ebff' : '#f9fafb'"
                                            @mouseleave="$el.style.backgroundColor = selectedWorkOrders.includes(wo.id) ? '#e8f4fd' : 'white'">
                                            <td style="text-align: center; border: 1px solid #e7ecf1; padding: 8px;">
                                                <input type="checkbox" 
                                                       :value="wo.id" 
                                                       :checked="selectedWorkOrders.includes(wo.id)" 
                                                       @change="toggleWorkOrder(wo.id)"
                                                       style="cursor: pointer; width: 14px; height: 14px;">
                                            </td>
                                            <td style="text-align: center; border: 1px solid #e7ecf1; padding: 8px;">
                                                <a :href="`/ocean-export/work-order/${wo.id}/edit?source=air_export&source_id={{ isset($airExport) ? $airExport->id : '' }}`" 
                                                   target="_blank"
                                                   style="color: #4b77be; text-decoration: none; font-weight: 600;"
                                                   x-text="wo.work_order_no">
                                                </a>
                                            </td>
                                            <td style="border: 1px solid #e7ecf1; padding: 8px;" x-text="wo.subject || 'N/A'"></td>
                                            <td style="border: 1px solid #e7ecf1; padding: 8px;">
                                                <div style="font-size: 9px; color: #666; margin-bottom: 2px;" x-text="wo.freight_pickup_location_name || 'N/A'"></div>
                                                <div style="font-size: 9px; color: #999;" x-text="wo.freight_pickup_date || ''"></div>
                                            </td>
                                            <td style="border: 1px solid #e7ecf1; padding: 8px;">
                                                <div style="font-size: 9px; color: #666; margin-bottom: 2px;" x-text="wo.empty_return_location_name || 'N/A'"></div>
                                                <div style="font-size: 9px; color: #999;" x-text="wo.empty_return_date || ''"></div>
                                            </td>
                                            <td style="border: 1px solid #e7ecf1; padding: 8px;">
                                                <span style="font-size: 9px; color: #666;" x-text="wo.vendor_name || 'N/A'"></span>
                                            </td>
                                            <td style="text-align: center; border: 1px solid #e7ecf1; padding: 8px;">
                                                <span style="font-size: 9px; color: #666;" x-text="wo.issue_date || 'N/A'"></span>
                                            </td>
                                            <td style="text-align: center; border: 1px solid #e7ecf1; padding: 8px;">
                                                <span style="font-size: 9px; color: #999;" x-text="wo.updated_at || 'N/A'"></span>
                                            </td>
                                            <td style="text-align: center; border: 1px solid #e7ecf1; padding: 8px;">
                                                <div style="display: flex; gap: 4px; justify-content: center;">
                                                    <button type="button" @click="editWorkOrder(wo.id)" 
                                                            class="btn-tool" 
                                                            style="background: #3b82f6; color: #fff; border: none; padding: 4px 8px; border-radius: 2px; cursor: pointer; font-size: 9px;"
                                                            title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button type="button" @click="deleteWorkOrder(wo.id)" 
                                                            class="btn-tool" 
                                                            style="background: #e74c3c; color: #fff; border: none; padding: 4px 8px; border-radius: 2px; cursor: pointer; font-size: 9px;"
                                                            title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- HBL Sidebar (col-2) -->
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <button type="button" class="btn-default-gf" style="width: 100%; padding: 6px; font-weight: 600; font-size: 11px; margin-bottom: 10px; justify-content: center;" @click="activeTab='basic'; showMblSection=false; addHawb()">+ Add HAWB</button>
                    <hr style="margin: 0 0 10px 0; border-top: 1px solid #ddd;">
                    <div style="background: #fff; border: 1px solid #e7ecf1; border-radius: 4px; padding: 10px; display: flex; flex-direction: column; gap: 5px; flex: 1; height: 100%;">
                        <template x-for="(hawb, index) in hawbs" :key="index">
                            <div style="background: #f1f3f6; border: 1px solid #dcdcdc; border-left: 3px solid #f2bc00; padding: 8px; border-radius: 2px; cursor: pointer;">
                                <div style="font-weight: 700; color: #4b77be; font-size: 11px;">HAWB No.</div>
                                <div style="font-size: 10px; color: #666; margin-top: 2px;" x-text="hawb.hawb_no || 'TBD'"></div>
                            </div>
                        </template>
                        <div x-show="hawbs.length === 0" style="text-align: center; color: #999; font-size: 10px; padding: 10px;">No HAWB created.</div>
                    </div>
                </div>
            </div>

            <!-- STATUS TAB -->
            <div x-show="activeTab === 'status'" :style="activeTab === 'status' ? 'display: flex !important;' : 'display: none !important;'" class="main-grid" x-cloak style="flex-direction: row; gap: 10px;">
                <!-- Main Status Area (col-10) -->
                <div style="flex: 5;">
                    <div class="portlet light">
                        <div class="portlet-body" style="padding: 20px;">
                            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                                <div style="flex: 1;">
                                    <h4 style="font-size: 13px; font-weight: 700; color: #333; margin: 0 0 10px 0;">Role</h4>
                                    <div style="display: flex; align-items: center; gap: 10px; font-size: 11px; color: #333;">
                                        <span>OP :</span>
                                        <div style="width: 20px; height: 20px; border-radius: 50% !important; background: #3598dc; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                            <i class="fa fa-user"></i>
                                        </div>
                                        <select class="form-control-gf" style="width: 200px;" x-model="form.op">
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div style="flex: 2;">
                                    <h4 style="font-size: 13px; font-weight: 700; color: #333; margin: 0 0 10px 0;">Internal Message</h4>
                                    <textarea class="form-control-gf" style="width: 100%; height: 55px; resize: none;" x-model="form.internal_remark"></textarea>
                                </div>
                            </div>

                            <h4 style="font-size: 13px; font-weight: 700; color: #333; margin: 0 0 15px 0;">Change Log</h4>
                            <div style="position: relative; padding-left: 100px;">
                                <!-- Timeline Line -->
                                <div style="position: absolute; left: 135px; top: 0; bottom: 0; width: 4px; background: #e5e5e5;"></div>

                                <!-- Log Item 1 -->
                                <div style="position: relative; margin-bottom: 20px;">
                                    <div style="position: absolute; left: -100px; width: 80px; text-align: right; color: #999; font-size: 10px;">
                                        <div>05-15-2026</div>
                                        <div>03:01</div>
                                    </div>
                                    <div style="position: absolute; left: 21px; top: 0; width: 32px; height: 32px; border-radius: 50% !important; background: #88939b; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; z-index: 2; border: 4px solid #fff;">
                                        D
                                    </div>
                                    <div style="margin-left: 70px; background: #f5f6fa; border-radius: 2px; padding: 10px; position: relative;">
                                        <div style="position: absolute; left: -6px; top: 12px; width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 6px solid #f5f6fa;"></div>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                            <div>
                                                <div style="font-size: 12px; font-weight: 600; color: #333;">Other Charges Updated</div>
                                                <div style="font-size: 10px; color: #999; margin-top: 5px; font-style: italic;">DEMO_925 (DEMO_925)</div>
                                            </div>
                                            <button style="background: #3598dc; color: #fff; border: none; padding: 3px 8px; font-size: 10px; border-radius: 2px; cursor: pointer;">
                                                More Detail <i class="fa fa-arrow-circle-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Log Item 2 -->
                                <div style="position: relative; margin-bottom: 20px;">
                                    <div style="position: absolute; left: -100px; width: 80px; text-align: right; color: #999; font-size: 10px;">
                                        <div>05-15-2026</div>
                                        <div>03:01</div>
                                    </div>
                                    <div style="position: absolute; left: 21px; top: 0; width: 32px; height: 32px; border-radius: 50% !important; background: #88939b; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; z-index: 2; border: 4px solid #fff;">
                                        D
                                    </div>
                                    <div style="margin-left: 70px; background: #f5f6fa; border-radius: 2px; padding: 10px; position: relative;">
                                        <div style="position: absolute; left: -6px; top: 12px; width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 6px solid #f5f6fa;"></div>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                            <div>
                                                <div style="font-size: 12px; font-weight: 600; color: #333;">Other Charges Updated</div>
                                                <div style="font-size: 10px; color: #999; margin-top: 5px; font-style: italic;">DEMO_925 (DEMO_925)</div>
                                            </div>
                                            <button style="background: #3598dc; color: #fff; border: none; padding: 3px 8px; font-size: 10px; border-radius: 2px; cursor: pointer;">
                                                More Detail <i class="fa fa-arrow-circle-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Log Item 3 -->
                                <div style="position: relative; margin-bottom: 20px;">
                                    <div style="position: absolute; left: -100px; width: 80px; text-align: right; color: #999; font-size: 10px;">
                                        <div>05-15-2026</div>
                                        <div>03:01</div>
                                    </div>
                                    <div style="position: absolute; left: 21px; top: 0; width: 32px; height: 32px; border-radius: 50% !important; background: #88939b; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; z-index: 2; border: 4px solid #fff;">
                                        D
                                    </div>
                                    <div style="margin-left: 70px; background: #f5f6fa; border-radius: 2px; padding: 10px; position: relative;">
                                        <div style="position: absolute; left: -6px; top: 12px; width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 6px solid #f5f6fa;"></div>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                            <div>
                                                <div style="font-size: 12px; font-weight: 600; color: #333;">Master B/L Created</div>
                                                <div style="font-size: 10px; color: #999; margin-top: 5px; font-style: italic;">DEMO_925 (DEMO_925)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HBL Sidebar (col-2) -->
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <button class="btn-default-gf" style="width: 100%; padding: 6px; font-weight: 600; font-size: 11px; margin-bottom: 10px; justify-content: center;" @click="activeTab='basic'; showMblSection=false; addHawb()">+ Add HAWB</button>
                    <hr style="margin: 0 0 10px 0; border-top: 1px solid #ddd;">
                    <div style="background: #fff; border: 1px solid #e7ecf1; border-radius: 4px; padding: 10px; display: flex; flex-direction: column; gap: 5px; flex: 1; height: 100%;">
                        <template x-for="(hawb, index) in hawbs" :key="index">
                            <div style="background: #f1f3f6; border: 1px solid #dcdcdc; border-left: 3px solid #f2bc00; padding: 8px; border-radius: 2px; cursor: pointer;">
                                <div style="font-weight: 700; color: #4b77be; font-size: 11px;">HAWB No.</div>
                                <div style="font-size: 10px; color: #666; margin-top: 2px;" x-text="hawb.hawb_no || 'TBD'"></div>
                            </div>
                        </template>
                        <div x-show="hawbs.length === 0" style="text-align: center; color: #999; font-size: 10px; padding: 10px;">No HAWB created.</div>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <!-- QUOTE MODAL -->
        <template x-teleport="body">
            <!-- QUOTE MODAL (Ocean Export UI Style) -->
<div x-show="showQuoteModal" class="modal-overlay" style="display:none; z-index: 999999;" x-cloak>
    <div class="modal-container" style="max-width: 950px; display: flex; flex-direction: column;">
        <!-- Ocean Style Header -->
        <div class="modal-header">
            <span><i class="fa fa-file-text-o text-blue-500"></i> Load Quotation Data</span>
            <i class="fa fa-times cursor-pointer text-gray-500 hover:text-gray-700" @click="closeQuoteModal()"></i>
        </div>

        <div class="modal-body hide-scrollbar">
            <style>
                .hide-scrollbar::-webkit-scrollbar { display: none; }
                .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                .wizard-circle { width: 18px; height: 18px; min-width: 18px; min-height: 18px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 10px; font-weight: bold; }
            </style>
            
            <!-- Wizard Steps Header -->
            <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 5px;">
                    <div class="wizard-circle" :style="quoteStep >= 1 ? 'background: #3b82f6;' : 'background: #cbd5e1;'">
                        <template x-if="quoteStep > 1"><i class="fa fa-check"></i></template>
                        <template x-if="quoteStep === 1"><span>1</span></template>
                    </div>
                    <span :style="quoteStep >= 1 ? 'color: #1e293b; font-size: 10px; font-weight: 600;' : 'color: #94a3b8; font-size: 10px;'">Select Quotation</span>
                </div>
                <div style="height: 1px; width: 20px; background: #e2e8f0;"></div>

                <div style="display: flex; align-items: center; gap: 5px;">
                    <div class="wizard-circle" :style="quoteStep >= 2 ? 'background: #3b82f6;' : 'background: #cbd5e1;'">
                        <template x-if="quoteStep > 2"><i class="fa fa-check"></i></template>
                        <template x-if="quoteStep <= 2"><span>2</span></template>
                    </div>
                    <span :style="quoteStep >= 2 ? 'color: #1e293b; font-size: 10px; font-weight: 600;' : 'color: #94a3b8; font-size: 10px;'">Fill in shipment data</span>
                </div>
                <div style="height: 1px; width: 20px; background: #e2e8f0;"></div>

                <div style="display: flex; align-items: center; gap: 5px;">
                    <div class="wizard-circle" :style="quoteStep >= 3 ? 'background: #3b82f6;' : 'background: #cbd5e1;'">
                        <span>3</span>
                    </div>
                    <span :style="quoteStep >= 3 ? 'color: #1e293b; font-size: 10px; font-weight: 600;' : 'color: #94a3b8; font-size: 10px;'">Select invoice items</span>
                </div>
            </div>

            <!-- Step 1 Content -->
            <div x-show="quoteStep === 1">
                @php
                    $agents = \App\Models\TradePartner::orderBy('name')->get();
                    $ports = \App\Models\Port::orderBy('name')->get();
                    $users = \App\Models\User::orderBy('name')->get();
                @endphp
                <!-- Ocean Style Grid -->
                <div class="form-grid-4" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="main-grid">
                        <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container">
                            <x-inline-select name="customer" :options="$agents" module="trade-partner" x-model="filters.customer" class="form-control-gf" />
                        </div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Departure</label><div class="form-input-container">
                            <x-inline-select name="pol" :options="$agents" module="trade-partner" x-model="filters.pol" class="form-control-gf" />
                        </div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Quote No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="filters.quote_no"></div></div>
                    </div>
                    <div class="main-grid">
                        <div class="form-group-gf"><label class="form-label-gf">Valid Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="filters.valid_date"></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Destination</label><div class="form-input-container">
                            <select class="form-control-gf" x-model="filters.pod" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}">
                                <option value="">Select...</option>
                                @foreach($ports as $port)
                                    <option value="{{ $port->id }}">{{ $port->name }}</option>
                                @endforeach
                            </select>
                        </div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Status</label><div class="form-input-container">
                            <select class="form-control-gf" x-model="filters.status">
                                <option value="">Select...</option>
                                <option value="Won">Won</option>
                                <option value="Draft">Draft</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div></div>
                    </div>
                    <div class="main-grid">
                        <div class="form-group-gf"><label class="form-label-gf">Commodity</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="filters.commodity"></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container">
                            <select class="form-control-gf" x-model="filters.sales">
                                <option value="">Select...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div></div>
                        <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container">
                            <select class="form-control-gf" x-model="filters.op">
                                <option value="">Select...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div></div>
                    </div>
                </div>

                <!-- Ocean Style Search Buttons -->
                <div style="display: flex; justify-content: center; gap: 8px; margin: 10px 0;">
                    <button type="button" class="btn-default-gf" @click="clearSearch()">Clear</button>
                    <button type="button" class="btn-freightx" @click="applySearch()"><i class="fa fa-search"></i> Search</button>
                </div>

                <hr style="border-top: 1px solid #e2e8f0; margin: 10px 0;">

                <div style="display: flex; justify-content: flex-end; margin-bottom: 5px; position: relative;">
                    <button type="button"
                            class="btn-tool-secondary"
                            style="background: #67809f; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 10px; border: none; cursor: pointer;"
                            @click.stop="showQuoteConfig = !showQuoteConfig">
                        <i class="fa fa-cogs"></i> Config
                    </button>
                </div>

                <!-- Quotation Column Configuration -->
                <div x-show="showQuoteConfig"
                     x-cloak
                     @click.outside="showQuoteConfig = false"
                     style="background: #fff; border: 1px solid #d9e2ec; border-radius: 4px; padding: 18px 10px; margin: 0 0 8px auto; width: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.12); position: relative; z-index: 20;">
                    <div style="font-size: 15px; font-weight: 700; color: #475569; margin-bottom: 7px;">Show / Hide Columns</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px 12px; align-items: center;">
                        <template x-for="(label, key) in {
                            select: 'Select',
                            quote_no: 'Quote No.',
                            valid_date: 'Valid Date',
                            status: 'Status',
                            creation_date: 'Creation Date',
                            commodity: 'Commodity',
                            pol: 'Departure',
                            pod: 'Destination',
                            carrier: 'Carrier',
                            sales: 'Sales',
                            op: 'OP'
                        }" :key="key">
                            <label style="display: flex; align-items: center; gap: 4px; font-size: 10px; cursor: pointer; color: #374151; white-space: nowrap;">
                                <input type="checkbox" x-model="colVisibility[key]" style="width: 12px; height: 12px; margin: 0; cursor: pointer;">
                                <span x-text="label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Ocean Style Interactive Table -->
                <div class="table-responsive" style="margin-bottom: 10px; height: 280px; border: 1px solid #e7ecf1;">
                    <table class="table-custom">
                        <thead>
                            <tr style="background: #888; color: #fff;">
                                <th x-show="colVisibility.select" style="text-align: center;">Select</th>
                                <th x-show="colVisibility.quote_no">Quote No.</th>
                                <th x-show="colVisibility.valid_date">Valid Date <i class="fa fa-sort" style="float: right;"></i></th>
                                <th x-show="colVisibility.status">Status <i class="fa fa-sort" style="float: right;"></i></th>
                                <th x-show="colVisibility.creation_date">Creation Date <i class="fa fa-sort" style="float: right;"></i></th>
                                <th x-show="colVisibility.commodity">Commodity</th>
                                <th x-show="colVisibility.pol">Departure</th>
                                <th x-show="colVisibility.pod">Destination</th>
                                <th x-show="colVisibility.carrier">Carrier</th>
                                <th x-show="colVisibility.sales">Sales</th>
                                <th x-show="colVisibility.op">OP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotations as $quote)
                            <tr class="cursor-pointer hover:bg-blue-50"
                                :style="selectedQuote && selectedQuote.quote_no === '{{ addslashes($quote->quote_no) }}' ? 'background-color: #eff6ff;' : ''"
                                @click="selectQuote({
                                    quote_no: '{{ addslashes($quote->quote_no) }}',
                                    mawb_no: 'MAWB-{{ addslashes($quote->quote_no) }}',
                                    hawb_no: 'HAWB-{{ addslashes($quote->quote_no) }}',
                                    eta: '{{ $quote->expiry_date ? $quote->expiry_date->format('Y-m-d') : '' }}',
                                    etd: '{{ $quote->quote_date ? $quote->quote_date->format('Y-m-d') : '' }}',
                                    customer: '{{ addslashes($quote->customer->name ?? '') }}',
                                    customer_id: '{{ $quote->customer_id }}',
                                    sales: '{{ addslashes($quote->salesPerson->name ?? '') }}',
                                    sales_person_id: '{{ $quote->sales_person_id }}',
                                    op: '{{ addslashes($quote->op->name ?? '') }}',
                                    op_id: '{{ $quote->op_id }}',
                                    pol_name: '{{ addslashes($quote->pol->name ?? '') }}',
                                    pod_name: '{{ addslashes($quote->pod->name ?? '') }}',
                                    pol_id: '{{ $quote->pol_id }}',
                                    pod_id: '{{ $quote->pod_id }}',
                                    carrier_name: '{{ addslashes($quote->carrier->name ?? '') }}',
                                    carrier_id: '{{ $quote->carrier_id }}',
                                    oversea_agent_id: '{{ $quote->agent_id }}',
                                    service_term: '{{ addslashes($quote->service_term ?? '') }}',
                                    incoterms_id: '{{ $quote->incoterms_id }}',
                                    commodity: '{{ addslashes($quote->commodity ?? '') }}',
                                    gross_weight_kg: '{{ $quote->weight_kg ?? '' }}',
                                    gross_weight_lb: '{{ $quote->weight_lb ?? '' }}',
                                    volume_cbm: '{{ $quote->volume_cbm ?? '' }}',
                                    chargeable_weight_kg: '{{ $quote->chargeable_weight ?? '' }}',
                                    ship_mode: '{{ addslashes($quote->ship_mode ?? '') }}',
                                    items: (quoteItems && quoteItems['{{ $quote->quote_no }}']) ? quoteItems['{{ $quote->quote_no }}'].map(i => ({...i, selected: true})) : []
                                })"
                                x-show="matchFilters({quote_no: '{{ addslashes($quote->quote_no) }}', customer_id: '{{ $quote->customer_id }}', pol_id: '{{ $quote->pol_id }}', pod_id: '{{ $quote->pod_id }}', status: '{{ $quote->status }}', sales_person_id: '{{ $quote->sales_person_id }}', op: '{{ $quote->op_id }}', commodity: '{{ addslashes($quote->commodity ?? '') }}'})">
                                
                                <td x-show="colVisibility.select" style="text-align: center;"><input type="radio" name="quote_sel" :checked="selectedQuote && selectedQuote.quote_no === '{{ addslashes($quote->quote_no) }}'"></td>
                                <td x-show="colVisibility.quote_no"><span style="color: #3b82f6; font-weight: 600;">{{ $quote->quote_no }}</span></td>
                                <td x-show="colVisibility.valid_date">{{ $quote->quote_date ? $quote->quote_date->format('m-d-Y') : '' }} ~ {{ $quote->expiry_date ? $quote->expiry_date->format('m-d-Y') : '' }}</td>
                                <td x-show="colVisibility.status"><span style="background: {{ in_array(strtoupper($quote->status), ['WON', 'ACCEPTED']) ? '#10b981' : '#64748b' }}; color: #fff; padding: 1px 4px; border-radius: 2px; font-size: 9px; font-weight: 600;">{{ $quote->status }}</span></td>
                                <td x-show="colVisibility.creation_date">{{ $quote->created_at ? $quote->created_at->format('Y-m-d') : '' }}</td>
                                <td x-show="colVisibility.commodity">{{ $quote->commodity ?: '-' }}</td>
                                <td x-show="colVisibility.pol">{{ $quote->pol->name ?? '-' }}</td>
                                <td x-show="colVisibility.pod">{{ $quote->pod->name ?? '-' }}</td>
                                <td x-show="colVisibility.carrier">{{ $quote->carrier->name ?? '-' }}</td>
                                <td x-show="colVisibility.sales">{{ $quote->salesPerson->name ?? '-' }}</td>
                                <td x-show="colVisibility.op">{{ $quote->op->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Step 2 Content -->
            <div x-show="quoteStep === 2" x-cloak>
                <div class="hbl-header">Route Information</div>
                <div class="table-responsive" style="margin-bottom: 20px;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">Select</th>
                                <th>Departure</th>
                                <th>Destination</th>
                                <th>Final Destination</th>
                                <th>Carrier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-blue-50" x-show="selectedQuote">
                                <td style="text-align: center;"><input type="radio" checked></td>
                                <td x-text="selectedQuote ? selectedQuote.pol_name : ''"></td>
                                <td x-text="selectedQuote ? selectedQuote.pod_name : ''"></td>
                                <td x-text="selectedQuote ? selectedQuote.pod_name : ''"></td>
                                <td x-text="selectedQuote && selectedQuote.carrier_name ? selectedQuote.carrier_name : '-'"></td>
                            </tr>
                            <tr x-show="!selectedQuote">
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 15px;">No route information in this quotation</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="hbl-header">Fill in the Shipment Information</div>
                <div class="form-grid-4" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="main-grid">
                        <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*MAWB No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.mawb_no" placeholder="MAWB-..."></div></div>
                        <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*Departure Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="quoteForm.etd"></div></div>
                        <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*Customer</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.customer" readonly style="background-color: #f8fafc;"></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><span x-text="quoteForm.oversea_agent_id ? 'Has Agent' : '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Gross Weight</label><div class="form-input-container"><span style="font-size: 10px; color: #334155;"><span x-text="quoteForm.gross_weight_kg || '0.00'"></span> KG</span></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Chargeable Weight</label><div class="form-input-container"><span style="font-size: 10px; color: #334155;"><span x-text="quoteForm.chargeable_weight_kg || '0.00'"></span> KG</span></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><span x-text="quoteForm.op_id ? 'Has OP' : '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                    </div>
                    <div class="main-grid">
                        <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*HAWB No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.hawb_no"></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Arrival Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="quoteForm.eta"></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><span x-text="quoteForm.service_term || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><span x-text="quoteForm.incoterms_id ? 'Has Incoterm' : '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Volume Weight</label><div class="form-input-container"><span style="font-size: 10px; color: #334155;"><span x-text="quoteForm.volume_cbm || '0.00'"></span> CBM</span></div></div>
                        <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container"><span x-text="quoteForm.sales || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                    </div>
                </div>
            </div>

            <!-- Step 3 Content -->
            <div x-show="quoteStep === 3" x-cloak>
                <div class="hbl-header">Select Freight Item(s)</div>
                <div style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <input type="checkbox" style="margin: 0; width: 12px; height: 12px; cursor: pointer; accent-color: #3b82f6;">
                        <span style="font-size: 10px; color: #475569; font-weight: 600;">Save as a draft invoice</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px; font-size: 10px;">
                        <span style="font-weight: 600;">Applied Unit</span> <i class="fa fa-info-circle" style="color: #4b77be;"></i>
                        <select class="form-control-gf" style="width: 100px; height: 22px;">
                            <option value="">Select...</option>
                            @foreach($packageUnits as $unit)
                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive" style="margin-bottom: 10px;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Select</th>
                                <th>Freight Code</th>
                                <th>Freight Description</th>
                                <th>Unit</th>
                                <th>Currency</th>
                                <th>Volume</th>
                                <th>Rate</th>
                                <th style="text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="selectedQuote && selectedQuote.items && selectedQuote.items.length > 0">
                                <template x-for="(item, index) in selectedQuote.items" :key="index">
                                    <tr class="hover:bg-gray-50">
                                        <td style="text-align: center;"><input type="checkbox" x-model="item.selected"></td>
                                        <td x-text="item.charge_code || '-'"></td>
                                        <td x-text="item.charge_name || '-'"></td>
                                        <td x-text="item.unit || '-'"></td>
                                        <td x-text="item.currency ? item.currency.code : 'USD'"></td>
                                        <td x-text="item.qty || '1'"></td>
                                        <td x-text="Number(item.rate || 0).toFixed(2)"></td>
                                        <td style="text-align: right; font-weight: 600; color: #3b82f6;" x-text="Number(item.amount || 0).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </template>
                            <tr x-show="!selectedQuote || !selectedQuote.items || selectedQuote.items.length === 0">
                                <td colspan="8" style="text-align: center; color: #94a3b8; font-size: 11px; padding: 20px;">No charge items in this quotation. Items can be added after shipment creation.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ocean Style Footer -->
        <div class="modal-footer">
            <button type="button" class="btn-default-gf" @click="closeQuoteModal()">Cancel</button>
            <button type="button" x-show="quoteStep > 1" class="btn-default-gf" @click="quoteStep--">Back</button>

            <button type="button" x-show="quoteStep < 3"
                    :class="((quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mawb_no || !quoteForm.hawb_no || !quoteForm.customer || !quoteForm.etd))) ? 'btn-freightx opacity-50 cursor-not-allowed' : 'btn-freightx'"
                    :disabled="(quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mawb_no || !quoteForm.hawb_no || !quoteForm.customer || !quoteForm.etd))"
                    @click="quoteStep++">Next <i class="fa fa-arrow-right"></i></button>

            <button type="button" x-show="quoteStep === 3" class="btn-freightx" @click="confirmQuoteSelection()"><i class="fa fa-check"></i> Confirm</button>
        </div>
    </div>
</div>
        </template>

        <!-- Document Package Modal (Report Type) -->
        <div class="modal-overlay" x-show="showDocPackageModal" style="display: none; z-index: 100000; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;" x-cloak :style="showDocPackageModal ? 'display: flex;' : 'display: none;'">
            <div class="modal-content" @click.stop style="width: 480px; max-width: 95%; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; background: #fff;">
                <div style="background: #ffffff; padding: 14px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #111827;">Report Type</h3>
                    <div class="modal-close" @click="showDocPackageModal = false" style="cursor: pointer; font-size: 20px; color: #9ca3af; line-height: 1;">&times;</div>
                </div>
                
                <div class="modal-body" style="padding: 16px 20px; color: #374151; font-size: 13px;">
                    <!-- Action buttons: All & Clear -->
                    <div style="display: flex; gap: 8px; margin-bottom: 14px;">
                        <button type="button" @click="selectAllDocReports()" style="background: #26a69a; color: white; border: none; padding: 5px 16px; border-radius: 3px; font-weight: 600; font-size: 12px; cursor: pointer;">All</button>
                        <button type="button" @click="clearAllDocReports()" style="background: #26a69a; color: white; border: none; padding: 5px 16px; border-radius: 3px; font-weight: 600; font-size: 12px; cursor: pointer;">Clear</button>
                    </div>

                    <!-- Checkboxes List -->
                    <div style="display: flex; flex-direction: column; gap: 7px; margin-bottom: 18px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" value="manifest" x-model="docPackageForm.selectedReports">
                            <span>1. MAWB Export Manifest</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" value="mawb_print" x-model="docPackageForm.selectedReports">
                            <span>2. MAWB Print</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" value="local_invoice" x-model="docPackageForm.selectedReports">
                            <span>3. Local Invoice</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" value="credit_debit" x-model="docPackageForm.selectedReports">
                            <span>4. Credit/Debit Note</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" value="hawb_print" x-model="docPackageForm.selectedReports">
                            <span>5. HAWB Print</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" value="commercial_invoice" x-model="docPackageForm.selectedReports">
                            <span>6. Commercial Invoice</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" value="packing_list" x-model="docPackageForm.selectedReports">
                            <span>7. Packing List</span>
                        </label>
                    </div>

                    <!-- Input & Selection Fields -->
                    <div style="display: flex; flex-direction: column; gap: 10px; border-top: 1px solid #f3f4f6; padding-top: 14px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="width: 90px; text-align: right; font-weight: 600; color: #4b5563; font-size: 12px;">MAWB No.</label>
                            <input type="text" readonly :value="form.mawb_no || '02605203306'" style="flex: 1; padding: 5px 10px; border: 1px solid #d1d5db; border-radius: 4px; background: #eef1f5; color: #374151; font-size: 12px;">
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <label style="width: 90px; text-align: right; font-weight: 600; color: #4b5563; font-size: 12px; margin-top: 5px;">Master Agent</label>
                            <textarea readonly :value="masterAgentName" rows="2" style="flex: 1; padding: 5px 10px; border: 1px solid #d1d5db; border-radius: 4px; background: #eef1f5; color: #374151; font-size: 11px; resize: none;"></textarea>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="width: 90px; text-align: right; font-weight: 600; color: #4b5563; font-size: 12px;">Report</label>
                            <div style="display: flex; gap: 18px;">
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px;">
                                    <input type="radio" name="doc_report_agent" value="master" x-model="docPackageForm.report_agent_type">
                                    <span>Master Agent</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px;">
                                    <input type="radio" name="doc_report_agent" value="sub" x-model="docPackageForm.report_agent_type">
                                    <span>Sub Agent</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 10px 20px 14px 20px; background: #ffffff; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" @click="showDocPackageModal = false" style="background: #ffffff; color: #374151; border: 1px solid #d1d5db; padding: 5px 16px; border-radius: 4px; font-weight: 500; font-size: 12px; cursor: pointer;">Cancel</button>
                    <button type="button" @click="submitDocPackage()" style="background: #26a69a; color: white; border: none; padding: 5px 20px; border-radius: 4px; font-weight: 600; font-size: 12px; cursor: pointer;">View</button>
                </div>
            </div>
        </div>

        <!-- Consolidated Cargo Manifest Modal -->
        <div class="modal-overlay" x-show="showConsolidatedManifestModal" style="display: none; z-index: 100000; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;" x-cloak :style="showConsolidatedManifestModal ? 'display: flex;' : 'display: none;'">
            <div class="modal-content" @click.stop style="width: 480px; max-width: 95%; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; background: #fff;">
                <div style="background: #ffffff; padding: 14px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #111827;">Cargo Manifest</h3>
                    <div class="modal-close" @click="showConsolidatedManifestModal = false" style="cursor: pointer; font-size: 20px; color: #9ca3af; line-height: 1;">&times;</div>
                </div>
                
                <div class="modal-body" style="padding: 16px 20px; color: #374151; font-size: 13px;">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="width: 90px; text-align: right; font-weight: 600; color: #4b5563; font-size: 12px;">MAWB No.</label>
                            <input type="text" readonly :value="form.mawb_no || '02605203306'" style="flex: 1; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 4px; background: #eef1f5; color: #374151; font-size: 12px;">
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <label style="width: 90px; text-align: right; font-weight: 600; color: #4b5563; font-size: 12px; margin-top: 5px;" x-text="manifestForm.agent_type === 'sub' ? 'Sub Agent' : 'Master Agent'"></label>
                            <textarea readonly :value="manifestForm.agent_type === 'sub' ? subAgentName : masterAgentName" rows="3" style="flex: 1; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 4px; background: #eef1f5; color: #374151; font-size: 11px; resize: none;"></textarea>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="width: 90px; text-align: right; font-weight: 600; color: #4b5563; font-size: 12px;">Report</label>
                            <div style="display: flex; gap: 18px;">
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px;">
                                    <input type="radio" name="manifest_agent_type" value="master" x-model="manifestForm.agent_type">
                                    <span>Master Agent</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px;">
                                    <input type="radio" name="manifest_agent_type" value="sub" x-model="manifestForm.agent_type">
                                    <span>Sub Agent</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 10px 20px 14px 20px; background: #ffffff; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" @click="showConsolidatedManifestModal = false" style="background: #ffffff; color: #374151; border: 1px solid #d1d5db; padding: 6px 18px; border-radius: 4px; font-weight: 500; font-size: 12px; cursor: pointer;">Cancel</button>
                    <button type="button" @click="submitConsolidatedManifest()" style="background: #0ea5e9; color: white; border: none; padding: 6px 22px; border-radius: 4px; font-weight: 600; font-size: 12px; cursor: pointer;">View</button>
                </div>
            </div>
        </div>
        
        <!-- Dimensions Modal -->
        <div x-show="showDimensionsModal" 
             style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999999; display: flex !important; align-items: center; justify-content: center;"
             @click.self="closeDimensionsModal()" x-cloak>
            <div style="background: white; border-radius: 4px; width: 750px; max-width: 95%; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); font-size: 11px;">
                <!-- Modal Header -->
                <div style="background: #ffffff; padding: 14px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #111827;">Volume & Gross Weight Calculator</h3>
                    <div class="modal-close" @click="closeDimensionsModal()" style="cursor: pointer; font-size: 20px; color: #9ca3af; line-height: 1;">&times;</div>
                </div>

            <!-- Modal Body -->
            <div style="padding: 15px 18px;">
                <!-- Toolbar & Unit Switcher -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; gap: 4px;">
                        <button type="button" @click="addDimensionRow()" style="background: #26a69a; color: #fff; border: none; width: 26px; height: 26px; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button type="button" @click="deleteSelectedDimensions()" style="background: #fff; color: #888; border: 1px solid #ddd; width: 26px; height: 26px; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                    <div style="display: flex; gap: 15px; align-items: center; color: #666; font-size: 11px;">
                        <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; margin: 0;">
                            <input type="radio" x-model="dimensionUnit" value="CM" style="margin: 0;"> CM
                        </label>
                        <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; margin: 0;">
                            <input type="radio" x-model="dimensionUnit" value="Inch" style="margin: 0;"> Inch
                        </label>
                        <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; margin: 0;">
                            <input type="radio" x-model="dimensionUnit" value="Feet" style="margin: 0;"> Feet
                        </label>
                    </div>
                </div>

                <!-- Calculator Table -->
                <div style="border: 1px solid #ccc; max-height: 250px; overflow-y: auto;">
                    <table class="table-custom" style="width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 11px;">
                        <thead>
                            <tr style="background: #888; color: #fff;">
                                <th style="width: 30px; text-align: center; background: #888; color: #fff; border-right: 1px solid #999; padding: 6px 4px;">
                                    <input type="checkbox" @change="dimensionRows.forEach(r => r.selected = $event.target.checked)">
                                </th>
                                <th style="background: #888; color: #fff; border-right: 1px solid #999; text-align: center; padding: 6px 4px;">Length</th>
                                <th style="background: #888; color: #fff; border-right: 1px solid #999; text-align: center; padding: 6px 4px;">Width</th>
                                <th style="background: #888; color: #fff; border-right: 1px solid #999; text-align: center; padding: 6px 4px;">Height</th>
                                <th style="background: #888; color: #fff; border-right: 1px solid #999; text-align: center; padding: 6px 4px;">PCS</th>
                                <th colspan="2" style="background: #888; color: #fff; border-right: 1px solid #999; text-align: center; padding: 4px;">
                                    Gross Weight
                                    <div style="display: flex; justify-content: space-around; border-top: 1px solid #aaa; margin-top: 2px; padding-top: 2px; font-weight: normal;">
                                        <span style="width: 50%;">KGS</span>
                                        <span style="width: 50%;">LBS</span>
                                    </div>
                                </th>
                                <th colspan="2" style="background: #888; color: #fff; border-right: 1px solid #999; text-align: center; padding: 4px;">
                                    Volume Weight
                                    <div style="display: flex; justify-content: space-around; border-top: 1px solid #aaa; margin-top: 2px; padding-top: 2px; font-weight: normal;">
                                        <span style="width: 50%;">KGS</span>
                                        <span style="width: 50%;">LBS</span>
                                    </div>
                                </th>
                                <th colspan="2" style="background: #888; color: #fff; text-align: center; padding: 4px;">
                                    Measurement
                                    <div style="display: flex; justify-content: space-around; border-top: 1px solid #aaa; margin-top: 2px; padding-top: 2px; font-weight: normal;">
                                        <span style="width: 50%;">CBM</span>
                                        <span style="width: 50%;">CFT</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, idx) in dimensionRows" :key="idx">
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="text-align: center; padding: 4px;">
                                        <input type="checkbox" x-model="row.selected">
                                    </td>
                                    <td style="padding: 2px 4px;"><input type="number" step="0.01" class="form-control-gf" style="height: 22px; padding: 2px; text-align: right;" x-model="row.length" placeholder="0"></td>
                                    <td style="padding: 2px 4px;"><input type="number" step="0.01" class="form-control-gf" style="height: 22px; padding: 2px; text-align: right;" x-model="row.width" placeholder="0"></td>
                                    <td style="padding: 2px 4px;"><input type="number" step="0.01" class="form-control-gf" style="height: 22px; padding: 2px; text-align: right;" x-model="row.height" placeholder="0"></td>
                                    <td style="padding: 2px 4px;"><input type="number" step="1" class="form-control-gf" style="height: 22px; padding: 2px; text-align: right;" x-model="row.pcs" placeholder="1"></td>
                                    
                                    <!-- Gross Weight -->
                                    <td style="padding: 2px 4px;"><input type="number" step="0.01" class="form-control-gf" style="height: 22px; padding: 2px; text-align: right;" x-model="row.gross_weight" placeholder="0.00"></td>
                                    <td style="text-align: right; padding: 4px;" x-text="calcRowGrossLb(row).toFixed(2)"></td>
                                    
                                    <!-- Volume Weight -->
                                    <td style="text-align: right; padding: 4px;" x-text="calcRowVolKg(row).toFixed(2)"></td>
                                    <td style="text-align: right; padding: 4px;" x-text="calcRowVolLb(row).toFixed(2)"></td>
                                    
                                    <!-- Measurement -->
                                    <td style="text-align: right; padding: 4px;" x-text="calcRowCbm(row).toFixed(2)"></td>
                                    <td style="text-align: right; padding: 4px;" x-text="calcRowCft(row).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr style="background: #fafafa; font-weight: bold; border-top: 2px solid #ccc;">
                                <td colspan="4" style="text-align: right; padding: 6px;">Total</td>
                                <td style="text-align: right; padding: 6px;" x-text="totalDimPcs"></td>
                                <td style="text-align: right; padding: 6px;" x-text="totalDimGrossKg.toFixed(2)"></td>
                                <td style="text-align: right; padding: 6px;" x-text="(totalDimGrossKg * 2.20462).toFixed(2)"></td>
                                <td style="text-align: right; padding: 6px;" x-text="totalDimVolKg.toFixed(2)"></td>
                                <td style="text-align: right; padding: 6px;" x-text="totalDimVolLb.toFixed(2)"></td>
                                <td style="text-align: right; padding: 6px;" x-text="totalDimCbm.toFixed(2)"></td>
                                <td style="text-align: right; padding: 6px;" x-text="totalDimCft.toFixed(2)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 12px 18px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 8px; background: #fff;">
                <button type="button" @click="closeDimensionsModal()" class="btn-default-gf" style="padding: 5px 15px; font-size: 12px; background: #e5e5e5; border: none; color: #333; border-radius: 2px;">
                    Cancel
                </button>
                <button type="button" @click="applyDimensions()" class="btn-tool" style="padding: 5px 15px; font-size: 12px; background: #31708f; border: none; color: #fff; border-radius: 2px;">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>
        @include('partials.ai-modal')
<div id="toast-container" class="toast-container"></div>
<script>
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle', warning: 'exclamation-triangle' };
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
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

    // FIELD DIAGNOSTIC - Console Output
    @if(session('diagnostic'))
        const diagnostic = @json(session('diagnostic'));
        console.log('%c╔══════════════════════════════════════════════════════════════╗', 'color: #0066cc; font-weight: bold;');
        console.log('%c║     AIR EXPORT FIELD DIAGNOSTIC REPORT                      ║', 'color: #0066cc; font-weight: bold;');
        console.log('%c╚══════════════════════════════════════════════════════════════╝', 'color: #0066cc; font-weight: bold;');
        console.log('');
        console.log(`%c📊 Total Fields Tracked: ${diagnostic.total_fields}`, 'font-size: 13px; font-weight: bold;');
        console.log(`%c✅ Filled: ${diagnostic.filled_count} (${diagnostic.percentage_filled}%)`, 'color: #22c55e; font-size: 13px; font-weight: bold;');
        console.log(`%c❌ Empty: ${diagnostic.empty_count}`, 'color: #ef4444; font-size: 13px; font-weight: bold;');
        console.log('');
        console.log('%c╔═══ FILLED FIELDS ═══════════════════════════════════════════╗', 'color: #22c55e; font-weight: bold;');
        diagnostic.filled_fields.forEach((field, idx) => {
            console.log(`%c  ${idx + 1}. ✓ ${field}`, 'color: #22c55e;');
        });
        console.log('%c╚═════════════════════════════════════════════════════════════╝', 'color: #22c55e;');
        console.log('');
        console.log('%c╔═══ EMPTY FIELDS ═══════════════════════════════════════════╗', 'color: #ef4444; font-weight: bold;');
        diagnostic.empty_fields.forEach((field, idx) => {
            console.log(`%c  ${idx + 1}. ✗ ${field}`, 'color: #ef4444;');
        });
        console.log('%c╚═════════════════════════════════════════════════════════════╝', 'color: #ef4444;');
        console.log('');
        console.log('%c💡 Tip: Check storage/logs/laravel.log for detailed field values', 'color: #64748b; font-style: italic;');
        console.log('');
    @endif
</script>
</x-layout>