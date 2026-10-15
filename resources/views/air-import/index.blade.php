<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        .btn-filter {
            background: #fff;
            border: 1px solid #ddd;
            padding: 6px 15px;
            font-size: 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            color: #666;
        }
        .btn-filter:hover {
            background: #f5f5f5;
            border-color: #999;
        }
        .btn-filter-active {
            background: #2563eb;
            border: 1px solid #2563eb;
            padding: 6px 15px;
            font-size: 12px;
            border-radius: 4px;
            cursor: pointer;
            color: #fff;
            font-weight: 600;
        }
        /* Ocean Import Consistent Theme Overrides */
        .btn-gf-inline {
            background: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #2563eb !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            transition: all 0.15s ease-in-out !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            text-decoration: none !important;
        }
        .btn-gf-inline:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
        }
        .btn-default-gf, .btn-default-gf.dark {
            background: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
            padding: 4px 10px !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            transition: all 0.15s ease-in-out !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03) !important;
            text-decoration: none !important;
        }
        .btn-default-gf:hover, .btn-default-gf.dark:hover {
            background: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }
        .oi-tools-item {
            display:flex;align-items:center;gap:8px;width:100%;padding:7px 16px;
            font-size:12px;color:#374151;background:transparent;border:none;cursor:pointer;text-align:left;
            transition:background 0.15s;
        }
        .oi-tools-item:hover {
            background:#f1f5f9;
        }
        h4, .portlet-title .caption-subject {
            color: #2563eb !important;
        }
    </style>
    @endpush

    @if(session('success'))
        <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;padding:10px 15px;border-radius:4px;margin-bottom:15px;display:flex;align-items:center;gap:8px;">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:15px;display:flex;align-items:center;gap:8px;">
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:15px;">
            <strong>Validation Errors:</strong>
            <ul style="margin:5px 0 0 15px;padding:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <script>
        console.log('🔵 Air Import Script Loading...');
        
        function airImportModule() {
            console.log('🟢 airImportModule() function called');
            return {
                saved: {{ isset($airImport) ? 'true' : 'false' }},
                isSaving: false,
                showAiModal: false,
                isAiProcessing: false,
                aiPreviewMode: false,
                aiError: '',
                documentPreviewUrl: null,
                extractedData: [],
                activeTab: 'basic',
                showConnectingFlight: false,
                activeChargeFilter: 'All',
                manifestFilters: {
                    party: 'All',
                    sal: 'All',
                    pr: 'All',
                    ppc: 'All',
                    currency: 'All',
                    invoiced: 'All'
                },
                resetManifestFilters() {
                    this.manifestFilters = {
                        party: 'All',
                        sal: 'All',
                        pr: 'All',
                        ppc: 'All',
                        currency: 'All',
                        invoiced: 'All'
                    };
                    this.activeChargeFilter = 'All';
                },

                showMblSection: true,
                showMblMemo: false,
                isDirectMaster: {{ isset($airImport) && $airImport->is_direct_master ? 'true' : 'false' }},
                showMore: false,
                showClipboardModal: false,
                showQuoteModal: false,
                showQuoteConfig: false,
                quoteConfig: {
                    quoteNo: true,
                    validDate: true,
                    status: true,
                    creationDate: true,
                    commodity: true,
                    departure: true,
                    destination: true,
                    carrier: true,
                    sales: true,
                    op: true
                },
                showWrModal: false,
                activeHblForReceipts: null,
                wrSearchQuery: '',
                wrSearchResults: [],
                showDocModal: false,
                showDimensionsModal: false,
                dimensionTarget: 'mawb',
                newStatusMessage: '',
                dimensions: {
                    length: '',
                    width: '',
                    height: '',
                    pieces: 1,
                    unit: 'CM'
                },
                quoteStep: 1,
                selectedQuote: null,
                
                // Container management
                containers: @json($airImport->containers ?? []),
                
                // Calculate sum from containers
                sumPackageWeight() {
                    if (!this.containers || this.containers.length === 0) {
                        alert('No containers to sum. Please add containers first.');
                        return;
                    }
                    
                    let totalPkg = 0;
                    let totalWeightKg = 0;
                    
                    this.containers.forEach(cont => {
                        totalPkg += parseFloat(cont.pkg_qty || 0);
                        totalWeightKg += parseFloat(cont.weight_kg || 0);
                    });
                    
                    // Update form fields
                    document.querySelector('[name="pkg_qty"]').value = totalPkg;
                    document.querySelector('[name="gross_weight_kg"]').value = totalWeightKg.toFixed(2);
                    
                    // Calculate LB (1 KG = 2.20462 LB)
                    let totalWeightLb = totalWeightKg * 2.20462;
                    document.querySelector('[name="gross_weight_lb"]').value = totalWeightLb.toFixed(2);
                    
                    alert('Package & Weight summed from ' + this.containers.length + ' container(s)');
                },
                
                // Open dimensions modal
                dimensionUnit: 'CM',
                dimensionRows: [
                    { selected: false, length: '', width: '', height: '', pcs: 1 }
                ],
                
                openDimensionsModal(target = 'mawb') {
                    console.log('🟡 openDimensionsModal() called with target:', target);
                    this.dimensionTarget = target;
                    console.log('showDimensionsModal before:', this.showDimensionsModal);
                    this.showDimensionsModal = true;
                    console.log('showDimensionsModal after:', this.showDimensionsModal);
                    
                    // Check if modal element exists in DOM
                    setTimeout(() => {
                        const modal = document.querySelector('[x-show="showDimensionsModal"]');
                        console.log('Modal element found:', modal);
                        if (modal) {
                            console.log('Modal computed style:', window.getComputedStyle(modal).display);
                            console.log('Modal inline style:', modal.style.display);
                        }
                    }, 100);
                    
                    if (!this.dimensionRows || this.dimensionRows.length === 0) {
                        this.dimensionRows = [{ selected: false, length: '', width: '', height: '', pcs: 1 }];
                    }
                },
                
                closeDimensionsModal() {
                    this.showDimensionsModal = false;
                },
                
                addDimensionRow() {
                    this.dimensionRows.push({ selected: false, length: '', width: '', height: '', pcs: 1 });
                },
                
                deleteSelectedDimensions() {
                    this.dimensionRows = this.dimensionRows.filter(r => !r.selected);
                    if (this.dimensionRows.length === 0) {
                        this.addDimensionRow();
                    }
                },
                
                calcRowGrossLb(row) {
                    return (parseFloat(row.gross_weight) || 0) * 2.20462;
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
                    
                    if (this.dimensionUnit === 'CM') {
                        return (l * w * h * pcs) / 1000000;
                    } else if (this.dimensionUnit === 'IN' || this.dimensionUnit === 'Inch') {
                        return (l * 2.54 * w * 2.54 * h * 2.54 * pcs) / 1000000;
                    } else if (this.dimensionUnit === 'Feet') {
                        return (l * 30.48 * w * 30.48 * h * 30.48 * pcs) / 1000000;
                    }
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
                    const totalPcs = this.totalDimPcs;
                    const grossKg = this.totalDimGrossKg;
                    const chargeableKg = Math.max(grossKg, volKg);

                    if (this.dimensionTarget === 'mawb') {
                        this.updateDOMInput('volume_cbm', cbm.toFixed(2));
                        this.updateDOMInput('volume_weight_kg', volKg.toFixed(2));
                        this.updateDOMInput('gross_weight_kg', grossKg.toFixed(2));
                        this.updateDOMInput('gross_weight_lb', (grossKg * 2.20462).toFixed(2));
                        this.updateDOMInput('chargeable_weight_kg', chargeableKg.toFixed(2));
                        this.updateDOMInput('chargeable_weight_lb', (chargeableKg * 2.20462).toFixed(2));
                        if (totalPcs > 0) {
                            const pkgInput = document.querySelector('[name="pkg_qty"]');
                            if (pkgInput && (!pkgInput.value || parseFloat(pkgInput.value) === 0)) {
                                pkgInput.value = totalPcs;
                                pkgInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        }
                    } else {
                        const index = parseInt(this.dimensionTarget);
                        if (!isNaN(index) && this.hawbs[index]) {
                            this.hawbs[index].volume_cbm = cbm.toFixed(2);
                            this.hawbs[index].volume_weight_kg = volKg.toFixed(2);
                            this.hawbs[index].gross_weight_kg = grossKg.toFixed(2);
                            this.hawbs[index].gross_weight_lb = (grossKg * 2.20462).toFixed(2);
                            this.hawbs[index].chargeable_weight_kg = chargeableKg.toFixed(2);
                            this.hawbs[index].chargeable_weight_lb = (chargeableKg * 2.20462).toFixed(2);
                            if (totalPcs > 0 && (!this.hawbs[index].pkg_qty || parseFloat(this.hawbs[index].pkg_qty) === 0)) {
                                this.hawbs[index].pkg_qty = totalPcs;
                            }
                        }
                    }
                    this.closeDimensionsModal();
                },

                updateDOMInput(name, value) {
                    const el = document.querySelector(`[name="${name}"]`);
                    if (el) {
                        el.value = value;
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                },
                // ============ CHARGES SECTION FUNCTIONS ============
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
                    let foreignAmount = (charge.rate || 0) * (charge.qty || 0);
                    let localAmount = foreignAmount * (charge.roe || 1);
                    if (charge.vat && charge.vat > 0) {
                        localAmount = localAmount + (localAmount * (charge.vat / 100));
                    }
                    return localAmount;
                },

                updateChargeAmount(idx) {
                    if (typeof this.$forceUpdate === 'function') this.$forceUpdate();
                },

                updateLocalAmount(idx) {
                    if (typeof this.$forceUpdate === 'function') this.$forceUpdate();
                },

                saveCharges() {
                    if (typeof this.validateForm === 'function' && !this.validateForm()) return;
                    const formEl = document.querySelector('form[action*="air-import"]');
                    if (formEl) {
                        formEl.submit();
                    }
                },

                openCertificateModal() {
                    if (typeof showToast === 'function') showToast('info', 'Certificate functionality - coming soon');
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
                        { id: null, _selected: false, selected: false, party: 'Freight', party_name: '', party_name_id: '', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'OFC', currency: 'USD', rate: 50, qty: 1, qty_type: 'B/L', roe: 120.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: '' },
                        { id: null, _selected: false, selected: false, party: 'THC', party_name: '', party_name_id: '', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'THC', currency: 'USD', rate: 10, qty: 1, qty_type: 'CBM', roe: 120.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: '' }
                    ];
                },

                reloadCharges() {
                    if (confirm('Discard changes and reload charges from database?')) {
                        window.location.reload();
                    }
                },

                getChargeFilters() {
                    let list = [{ name: 'All', value: 'All' }];
                    if (this.form.file_no) {
                        list.push({ name: 'MBL: ' + this.form.file_no, value: this.form.file_no });
                    }
                    this.hawbs.forEach(h => {
                        if (h.hawb_no) {
                            list.push({ name: 'HAWB: ' + h.hawb_no, value: h.hawb_no });
                        }
                    });
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
                        selected: false,
                        party: 'Freight',
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
                        remark: false,
                        mbl_no: defaultMbl
                    });
                },

                removeCharge(idx) {
                    this.chargesList.splice(idx, 1);
                },

                applyTemplate() {
                    if (!confirm('Are you sure you want to load the default template charges?')) return;
                    fetch(`/air-import/${this.form.id}/charges/template`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            this.chargesList = data.charges;
                            if (typeof showToast === 'function') showToast('success', 'Template charges applied successfully.');
                        } else {
                            if (typeof showToast === 'function') showToast('error', data.message || 'Failed to apply template.');
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

                    // Calculate totals
                    let foreignAmt = 0;
                    let localAmt = 0;
                    let firstCurrency = 'USD';
                    let firstParty = '';

                    uninvoicedCharges.forEach(c => {
                        let f = (parseFloat(c.rate) || 0) * (parseFloat(c.qty) || 0);
                        let l = this.calculateLocalAmount(c);
                        foreignAmt += f;
                        localAmt += l;
                        if (c.currency) firstCurrency = c.currency;
                        if (!firstParty && c.party_name) firstParty = c.party_name;
                    });

                    let partyDisplay = firstParty || 'General Customer';
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

                    fetch(`/air-import/${this.form.id}/charges/invoice`, {
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
                    window.open(`/shipments/air-import/${this.form.id}/freight-invoice`, '_blank');
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
                    let amount = prompt("Enter amount to prorata across HAWBs:");
                    if (!amount || isNaN(amount)) return;
                    
                    if (!this.form.id) {
                        if (typeof showToast === 'function') showToast('error', 'Please save shipment first.');
                        return;
                    }
                    
                    fetch(`/air-import/${this.form.id}/charges/prorata`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ amount: parseFloat(amount) })
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            this.chargesList = data.charges;
                            if (typeof showToast === 'function') showToast('success', 'Charges prorated successfully.');
                        } else {
                            if (typeof showToast === 'function') showToast('error', 'Failed to prorate charges: ' + data.message);
                        }
                    });
                },

                exportChargesToExcel() {
                    window.location.href = `/air-import/${this.form.id}/charges/export`;
                },

                printCharges() {
                    window.open(`/air-import/${this.form.id}/charges/print`, '_blank');
                },

                deleteAllCharges() {
                    if (!confirm('Are you sure you want to delete all charges?')) return;
                    if (this.form.id) {
                        fetch(`/air-import/${this.form.id}/charges/all`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        }).then(res => res.json()).then(data => {
                            if (data.success) {
                                this.chargesList = [];
                                if (typeof showToast === 'function') showToast('success', 'All charges deleted successfully.');
                            } else {
                                if (typeof showToast === 'function') showToast('error', 'Failed to delete charges.');
                            }
                        });
                    } else {
                        this.chargesList = [];
                        if (typeof showToast === 'function') showToast('success', 'All unsaved charges deleted.');
                    }
                },

                duplicateSelectedCharges() {
                    let ids = this.chargesList.filter(c => c.selected).map(c => c.id);
                    if (ids.length === 0) {
                        let enteredId = prompt("Enter Charge ID to duplicate:");
                        if (enteredId) ids.push(enteredId);
                        else {
                            if (typeof showToast === 'function') showToast('error', 'Please select charges using checkbox or enter charge ID.');
                            return;
                        }
                    }
                    fetch(`/air-import/${this.form.id}/charges/duplicate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ids: ids })
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            this.chargesList = data.charges;
                            if (typeof showToast === 'function') showToast('success', 'Charges duplicated successfully.');
                        } else {
                            if (typeof showToast === 'function') showToast('error', 'Failed to duplicate charges.');
                        }
                    });
                },

                bulkUpdateCurrency() {
                    let curr = prompt("Enter new currency code (e.g. BDT):");
                    if (!curr) return;
                    let ids = this.chargesList.filter(c => c.selected).map(c => c.id);
                    if (ids.length === 0) {
                        if (typeof showToast === 'function') showToast('error', 'Select charges to update first.');
                        return;
                    }
                    fetch(`/air-import/${this.form.id}/charges/bulk-currency`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ currency: curr, ids: ids })
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            this.chargesList = data.charges;
                            if (typeof showToast === 'function') showToast('success', 'Currency updated successfully.');
                        } else {
                            if (typeof showToast === 'function') showToast('error', 'Failed to update currency.');
                        }
                    });
                },

                applyVatToAll() {
                    let vat = prompt("Enter VAT % to apply to all charges:");
                    if (!vat || isNaN(vat)) return;
                    fetch(`/air-import/${this.form.id}/charges/bulk-vat`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ vat: parseFloat(vat) })
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            this.chargesList = data.charges;
                            if (typeof showToast === 'function') showToast('success', `Applied ${vat}% VAT to all charges.`);
                        } else {
                            if (typeof showToast === 'function') showToast('error', 'Failed to apply VAT.');
                        }
                    });
                },


                
                // History management
                addStatusLog() {
                    if (!this.newStatusMessage.trim()) {
                        alert('Please enter a status message');
                        return;
                    }
                    this.form.history.push({
                        date: new Date().toISOString().slice(0, 16).replace('T', ' '),
                        user: '{{ auth()->user()->name ?? "User" }}',
                        status: 'UPDATE',
                        details: this.newStatusMessage
                    });
                    this.newStatusMessage = '';
                    alert('Status log added successfully!');
                },
                
                // --- AI Auto-Fill Logic ---
                handleAiUpload(e) {
                    if (!e.target.files.length) return;
                    
                    const file = e.target.files[0];
                    const formData = new FormData();
                    formData.append('document', file);
                    
                    // Create preview URL
                    if (this.documentPreviewUrl) {
                        URL.revokeObjectURL(this.documentPreviewUrl);
                    }
                    this.documentPreviewUrl = URL.createObjectURL(file);
                    
                    this.isAiProcessing = true;
                    this.aiPreviewMode = true;
                    this.extractedData = []; // Clear old data
                    this.aiError = '';

                    fetch('/api/ai/extract/air-import', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isAiProcessing = false;
                        if (data.success && data.data) {
                            this.extractedData = [];
                            
                            if (data.data.mbl) {
                                const m = data.data.mbl;
                                const mblMap = [
                                    { label: 'MAWB No.', key: 'mawb_no', val: m.mbl_no },
                                    { label: 'Booking No.', key: 'booking_no', val: m.booking_no },
                                    { label: 'Booking no', key: 'file_no', val: m.file_no },
                                    { label: 'Post Date', key: 'post_date', val: m.post_date },
                                    { label: 'Carrier', key: 'carrier_id', val: m.carrier_id, display: m.carrier_name, nameKey: 'carrier_name' },
                                    { label: 'Port of Loading', key: 'pol_id', val: m.pol_id, display: m.pol_name, nameKey: 'pol_name' },
                                    { label: 'Port of Discharge', key: 'pod_id', val: m.pod_id, display: m.pod_name, nameKey: 'pod_name' },
                                    { label: 'Flight No.', key: 'flight_no', val: m.flight_no },
                                    { label: 'Forwarding Agent', key: 'forwarding_agent_id', val: m.forwarding_agent_id, display: m.forwarding_agent_name, nameKey: 'forwarding_agent_name' },
                                    { label: 'Co-Loader', key: 'co_loader_id', val: m.co_loader_id, display: m.co_loader_name, nameKey: 'co_loader_name' },
                                    { label: 'Oversea Agent', key: 'oversea_agent_id', val: m.oversea_agent_id, display: m.oversea_agent_name, nameKey: 'oversea_agent_name' },
                                    { label: 'Operator', key: 'op_id', val: m.op_id, display: m.op_name, nameKey: 'op_name' },
                                    { label: 'Sales Person', key: 'dm_sales_person_id', val: m.dm_sales_person_id, display: m.dm_sales_person_name, nameKey: 'dm_sales_person_name' },
                                    { label: 'Agent Ref No.', key: 'agent_ref_no', val: m.agent_ref_no },
                                    { label: 'Contract No.', key: 'contract_no', val: m.contract_no },
                                    { label: 'Sales Type', key: 'sales_type', val: m.sales_type },
                                    { label: 'B/L Type', key: 'bl_type', val: m.bl_type },
                                    { label: 'Cargo Type', key: 'cargo_type', val: m.cargo_type },
                                    { label: 'Description', key: 'description', val: m.description },
                                    { label: 'ETA', key: 'eta', val: m.eta },
                                    { label: 'ETD', key: 'etd', val: m.etd },
                                    { label: 'ATA', key: 'ata', val: m.ata },
                                    { label: 'ATD', key: 'atd', val: m.atd }
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
                            
                            if (data.data.hbls && data.data.hbls.length > 0) {
                                const h = data.data.hbls[0];
                                const hblMap = [
                                    { label: 'HAWB No.', key: 'hawb_no', val: h.hbl_no },
                                    { label: 'Shipper', key: 'shipper_id', val: h.shipper_id, display: h.shipper_name, nameKey: 'shipper_name' },
                                    { label: 'Consignee', key: 'consignee_id', val: h.consignee_id, display: h.consignee_name, nameKey: 'consignee_name' },
                                    { label: 'Notify Party', key: 'notify_id', val: h.notify_id, display: h.notify_party_name, nameKey: 'notify_party_name' },
                                    { label: 'Weight', key: 'weight', val: h.weight },
                                    { label: 'Volume Weight', key: 'volume', val: h.volume },
                                    { label: 'Pieces', key: 'pkg_qty', val: h.pieces },
                                    { label: 'Description', key: 'description', val: h.description }
                                ];
                                
                                hblMap.forEach(field => {
                                    if (field.val || field.display) {
                                        this.extractedData.push({
                                            label: field.label,
                                            parent: 'hbl',
                                            key: field.key,
                                            value: field.val,
                                            displayValue: field.display || field.val,
                                            nameKey: field.nameKey || null,
                                            selected: true
                                        });
                                    }
                                });
                            }
                            
                            // Parse Flights
                            if (data.data.flights && data.data.flights.length > 0) {
                                data.data.flights.forEach((r, idx) => {
                                    const legLabel = `Flight Leg ${idx+1}`;
                                    const fields = [
                                        { label: `${legLabel} - Flight No.`, key: 'flight_no', val: r.flight_no },
                                        { label: `${legLabel} - POL`, key: 'pol_name', val: r.pol_name, nameKey: 'pol_id', idVal: r.pol_id },
                                        { label: `${legLabel} - POD`, key: 'pod_name', val: r.pod_name, nameKey: 'pod_id', idVal: r.pod_id },
                                        { label: `${legLabel} - ETD`, key: 'etd', val: r.etd },
                                        { label: `${legLabel} - ETA`, key: 'eta', val: r.eta }
                                    ];
                                    fields.forEach(f => {
                                        if (f.val) {
                                            this.extractedData.push({
                                                label: f.label,
                                                parent: 'routes', // Using 'routes' to match frontend naming for transport legs
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
                        } else {
                            this.aiError = data.message || 'Failed to extract data.';
                        }
                        e.target.value = ''; // reset file input
                    })
                    .catch(err => {
                        this.isAiProcessing = false;
                        this.aiError = 'An error occurred while connecting to the AI service.';
                        console.error(err);
                    });
                },
                applyAiData() {
                    const ensureOptionExists = (selectName, id, text) => {
                        if (!id) return;
                        const el = document.querySelector(`[name="${selectName}"]`);
                        if (el) {
                            let exists = false;
                            for (let i = 0; i < el.options.length; i++) {
                                if (el.options[i].value == id) { exists = true; break; }
                            }
                            if (!exists) {
                                const opt = document.createElement('option');
                                opt.value = id;
                                opt.text = text;
                                el.appendChild(opt);
                            }
                        }
                    };

                    let dataToApply = { mbl: {}, hbl: {}, routes: [] };
                    
                    this.extractedData.forEach(item => {
                        if (item.selected) {
                            if (item.parent === 'routes') {
                                if (!dataToApply.routes[item.index]) {
                                    dataToApply.routes[item.index] = {};
                                }
                                dataToApply.routes[item.index][item.key] = item.value;
                                if(item.nameKey && item.displayValue) {
                                    dataToApply.routes[item.index][item.nameKey] = item.displayValue;
                                }
                            } else if (item.parent === 'mbl') {
                                dataToApply.mbl[item.key] = item.value;
                                if(item.nameKey && item.displayValue) {
                                    dataToApply.mbl[item.nameKey] = item.displayValue;
                                }
                            } else if (item.parent === 'hbl') {
                                dataToApply.hbl[item.key] = item.value;
                                if(item.nameKey && item.displayValue) {
                                    dataToApply.hbl[item.nameKey] = item.displayValue;
                                }
                            }
                        }
                    });
                    
                    // Map data to the form
                    if(dataToApply.mbl.mawb_no) this.form.mawb_no = dataToApply.mbl.mawb_no;
                    if(dataToApply.mbl.carrier_id) { 
                        ensureOptionExists('carrier_id', dataToApply.mbl.carrier_id, dataToApply.mbl.carrier_name || 'Carrier');
                        this.form.carrier_id = dataToApply.mbl.carrier_id; 
                        this.form.carrier_name = dataToApply.mbl.carrier_name; 
                    }
                    if(dataToApply.mbl.pol_id) { 
                        ensureOptionExists('pol_id', dataToApply.mbl.pol_id, dataToApply.mbl.pol_name || 'POL');
                        this.form.pol_id = dataToApply.mbl.pol_id; 
                        this.form.pol_name = dataToApply.mbl.pol_name; 
                    }
                    if(dataToApply.mbl.pod_id) { 
                        ensureOptionExists('pod_id', dataToApply.mbl.pod_id, dataToApply.mbl.pod_name || 'POD');
                        this.form.pod_id = dataToApply.mbl.pod_id; 
                        this.form.pod_name = dataToApply.mbl.pod_name; 
                    }
                    if(dataToApply.mbl.flight_no) this.form.flight_no = dataToApply.mbl.flight_no;
                    if(dataToApply.mbl.eta) this.form.eta = dataToApply.mbl.eta;
                    if(dataToApply.mbl.etd) this.form.etd = dataToApply.mbl.etd;

                    // Add Route / Transportation
                    if (dataToApply.routes && dataToApply.routes.length > 0) {
                        dataToApply.routes.forEach((route, index) => {
                            if (!this.form.routes || this.form.routes.length <= index) {
                                this.addRoute();
                            }
                            const r = this.form.routes[index];
                            if (route.eta) r.eta = route.eta;
                            if (route.etd) r.etd = route.etd;
                            
                            if (route.pol_id) {
                                ensureOptionExists(`routes[${index}][airport_id]`, route.pol_id, route.pol_name || 'POL');
                                r.airport_id = route.pol_id;
                            }
                            
                            if (route.pod_id) {
                                ensureOptionExists(`routes[${index}][pod_id]`, route.pod_id, route.pod_name || 'POD');
                                r.pod_id = route.pod_id;
                            }
                            
                            if (route.flight_no) {
                                r.flight_no = route.flight_no;
                            }
                        });
                        this.form.routes = [...this.form.routes];
                    } else if (dataToApply.mbl.eta || dataToApply.mbl.etd || dataToApply.mbl.pol_id || dataToApply.mbl.pod_id || dataToApply.mbl.flight_no) {
                        if (!this.form.routes || this.form.routes.length === 0) {
                            this.addRoute();
                        }
                        const r = this.form.routes[0];
                        if (dataToApply.mbl.eta) r.eta = dataToApply.mbl.eta;
                        if (dataToApply.mbl.etd) r.etd = dataToApply.mbl.etd;
                        if (dataToApply.mbl.ata) r.ata = dataToApply.mbl.ata;
                        if (dataToApply.mbl.atd) r.atd = dataToApply.mbl.atd;
                        
                        if (dataToApply.mbl.pol_id) {
                            ensureOptionExists('routes[0][airport_id]', dataToApply.mbl.pol_id, dataToApply.mbl.pol_name || 'POL');
                            r.airport_id = dataToApply.mbl.pol_id;
                        }
                        
                        if (dataToApply.mbl.flight_no) r.flight_no = dataToApply.mbl.flight_no;
                        
                        if (dataToApply.mbl.carrier_id) {
                            ensureOptionExists('routes[0][carrier_id]', dataToApply.mbl.carrier_id, dataToApply.mbl.carrier_name || 'Carrier');
                            r.carrier_id = dataToApply.mbl.carrier_id;
                        }

                        // Force Alpine reactivity
                        this.form.routes = [...this.form.routes];
                    }
                    
                    // Ensure hawbs array has at least one item
                    if (this.hawbs.length === 0) {
                        this.addHbl();
                    }
                    
                    if(dataToApply.hbl.hawb_no) this.hawbs[0].hbl_no = dataToApply.hbl.hawb_no;
                    if(dataToApply.hbl.shipper_id) { this.hawbs[0].shipper_id = dataToApply.hbl.shipper_id; }
                    if(dataToApply.hbl.consignee_id) { this.hawbs[0].consignee_id = dataToApply.hbl.consignee_id; }
                    if(dataToApply.hbl.notify_id) { this.hawbs[0].notify_id = dataToApply.hbl.notify_id; }
                    if(dataToApply.hbl.weight) this.hawbs[0].gross_weight_kg = dataToApply.hbl.weight;
                    if(dataToApply.hbl.volume) this.hawbs[0].volume_cbm = dataToApply.hbl.volume;
                    if(dataToApply.hbl.pkg_qty) this.hawbs[0].pkg_qty = dataToApply.hbl.pkg_qty;
                    if(dataToApply.hbl.description) this.hawbs[0].description = dataToApply.hbl.description;
                    
                    this.showAiModal = false;
                    this.aiPreviewMode = false;
                    this.extractedData = [];
                    if (typeof showToast === 'function') showToast('success', 'Data applied successfully!');
                },
                
                searchWarehouseList() {
                    this.wrLoading = true;
                    setTimeout(() => {
                        this.wrSearchResults = [
                            { receipt_no: 'WR-2405001', description: 'Electronics Parts', total_pcs: 100, available_pcs: 100, actual_weight: 500, measurement: 2.5, selected: false },
                            { receipt_no: 'WR-2405002', description: 'Apparel', total_pcs: 50, available_pcs: 20, actual_weight: 150, measurement: 1.2, selected: false }
                        ];
                        this.wrLoading = false;
                    }, 500);
                },
                loadSelectedWarehouse() {
                    this.showWrModal = false;
                },
                selectQuote(data) {
                    this.selectedQuote = data;
                    this.quoteForm.mawb_no = data.mawb_no;
                    this.quoteForm.hawb_no = data.hawb_no;
                    this.quoteForm.eta = data.eta;
                    this.quoteForm.etd = data.etd;
                    this.quoteForm.customer = data.customer;
                    this.quoteForm.customer_id = data.customer_id;
                    this.quoteForm.sales = data.sales;
                    this.quoteForm.sales_person_id = data.sales_person_id;
                    this.quoteForm.oversea_agent = data.oversea_agent;
                    this.quoteForm.service_term = data.service_term;
                    this.quoteForm.op = data.op;
                    this.quoteForm.incoterms = data.incoterms;
                    this.quoteForm.detail = data.detail;
                    this.quoteForm.ship_mode = data.ship_mode;
                    this.quoteForm.pol_id = data.pol_id;
                    this.quoteForm.pod_id = data.pod_id;
                    this.quoteForm.quote_no = data.quote_no;
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
                    etd: '',
                    eta: '',
                    customer: '',
                    customer_id: '',
                    sales_person_id: '',
                    pol_id: '',
                    pod_id: '',
                    quote_no: '',
                    ship_mode: '',
                    oversea_agent: '',
                    service_term: '',
                    sales: '',
                    op: '',
                    incoterms: '',
                    detail: ''
                },
                hawbs: @json(old('hbls', isset($airImport) && $airImport->hbls->count() ? $airImport->hbls : [])),
                hawb: {
                    hbl_no: '',
                    shipper_id: '',
                    consignee_id: '',
                    notify_id: '',
                    bill_to_id: '',
                    customer_id: '',
                    sales_person_id: '',
                    customs_broker_id: '',
                    freight_location_id: '',
                    final_destination_id: '',
                    delivery_location_id: '',
                    trucker_id: '',
                    pkg_unit_id: '',
                    incoterm_id: '',
                    service_term_from_id: '',
                    service_term_to_id: '',
                    hsn: '',
                    last_free_day: '',
                    final_eta: '',
                    storage_start_date: '',
                    freight_term: '',
                    freight: '',
                    transportation: '',
                    sales_type: '',
                    pkg_qty: '',
                    gross_weight_kg: '',
                    gross_weight_lb: '',
                    chargeable_weight_kg: '',
                    chargeable_weight_lb: '',
                    volume_weight_kg: '',
                    volume_cbm: '',
                    entry_no: '',
                    class_of_entry: '',
                    released_by_id: '',
                    cargo_released_to: '',
                    frt_released: false,
                    frt_released_date: '',
                    c_released_date: '',
                    door_delivered_date: '',
                    ship_type: '',
                    is_ecommerce: false,
                    display_unit: 'BOTH',
                    showMore: false,
                    subHawbs: []
                },
                form: {
                    id: @json(isset($airImport) ? $airImport->id : null),
                    file_no: '{{ old('file_no', $airImport->file_no ?? 'MAI-'.date('ymd').'-'.str_pad((\App\Models\AirImport::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT)) }}',
                    mawb_number: '',
                    office_id: '',
                    post_date: '',
                    voyage: '',
                    etd: '',
                    eta: '',
                    containers: [],
                    routes: @json(old('routes', isset($airImport) && !empty($airImport->routes) ? (is_array($airImport->routes) ? $airImport->routes : json_decode($airImport->routes, true)) : [])),
                    charges: @json(old('charges', $chargesData ?? [])),
                    history: []
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
                addContainer(count = 1) {
                    const c = (typeof count === 'number') ? count : 1;
                    for(let i=0; i<c; i++) {
                        this.form.containers.push({
                            selected: false,
                            number: '',
                            pp_ctf: '',
                            type: "40'HQ",
                            seal: '',
                            seal2: '',
                            lfd: '',
                            fdd: '',
                            pkg: 0,
                            weight: 0,
                            measure: 0,
                            expanded: false
                        });
                    }
                },
                deleteSelectedContainers() {
                    this.form.containers = this.form.containers.filter(c => !c.selected);
                },
                toggleAllContainers(e) {
                    this.form.containers.forEach(c => c.selected = e.target.checked);
                },
                calculateTotal(field) {
                    return this.form.containers.reduce((sum, c) => sum + (parseFloat(c[field]) || 0), 0);
                },
                addHbl() { 
                    this.hawbs.push({ 
                        show: true, 
                        showMore: false, 
                        showMemo: false, 
                        hbl_no: '',
                        subHawbs: [],
                        commodities: []
                    }); 
                    this.showMblSection = false;
                },
                removeHbl(idx) { this.hawbs.splice(idx, 1); },
                closeQuoteModal() {
                    if (window.location.search.includes('load_from_quotation=true') || '{{ $page ?? "" }}' === 'air-import.create-quote') {
                        window.location.href = '/air-import/create';
                    } else {
                        this.showQuoteModal = false;
                    }
                },
                confirmQuoteSelection() {
                    const q = this.quoteForm;

                    // ── HAWB reactive fields ──
                    this.hawb.hbl_no       = q.hawb_no   || '';
                    this.hawb.customer_id  = q.customer_id || '';
                    this.hawb.sales_person_id = q.sales_person_id || '';
                    this.hawb.incoterm_id  = q.incoterms || '';

                    // ── Sync to hawbs[0] if it exists ──
                    if (this.hawbs.length === 0) this.addHbl();
                    this.hawbs[0].hbl_no         = q.hawb_no   || '';
                    this.hawbs[0].customer_id    = q.customer_id || '';
                    this.hawbs[0].sales_person_id = q.sales_person_id || '';
                    this.hawbs[0].quotation_no   = q.quote_no  || '';

                    // ── Fill static MAWB form name= fields via DOM ──
                    const setVal = (name, val) => {
                        const el = document.querySelector(`[name="${name}"]`);
                        if (el && val) {
                            if (el.tagName === 'SELECT') {
                                el.value = String(val);
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            } else {
                                el.value = val;
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        }
                    };

                    // MAWB No
                    setVal('mawb_no', q.mawb_no || '');

                    // ETD — convert YYYY-MM-DD to datetime-local
                    if (q.etd) setVal('etd', q.etd.length === 10 ? q.etd + 'T00:00' : q.etd);

                    // ETA — convert YYYY-MM-DD to datetime-local
                    if (q.eta) setVal('eta', q.eta.length === 10 ? q.eta + 'T00:00' : q.eta);

                    // Departure (dep_port_id) & Destination (dst_port_id)
                    if (q.pol_id) setVal('dep_port_id', q.pol_id);
                    if (q.pod_id) setVal('dst_port_id', q.pod_id);

                    // Oversea Agent
                    if (q.oversea_agent_id) setVal('oversea_agent_id', q.oversea_agent_id);

                    // Sales (dm_sales_person_id on MAWB)
                    if (q.sales_person_id) setVal('dm_sales_person_id', q.sales_person_id);

                    // HAWB No (top bar input)
                    setVal('hbl_no', q.hawb_no || '');

                    // Customer (HAWB section)
                    if (q.customer_id) setVal('customer_id', q.customer_id);

                    // OP (hidden input op_id)
                    if (q.op_id) setVal('op_id', q.op_id);

                    // Incoterms
                    if (q.incoterms_id) setVal('incoterm_id', q.incoterms_id);

                    // Weights & Volume
                    if (q.gross_weight_kg) setVal('gross_weight_kg', q.gross_weight_kg);
                    if (q.gross_weight_lb) setVal('gross_weight_lb', q.gross_weight_lb);
                    if (q.volume_cbm)      setVal('volume_cbm', q.volume_cbm);

                    // Airline Carrier
                    if (q.carrier_id) setVal('carrier_id', q.carrier_id);
                    if (q.commodity) setVal('commodity', q.commodity);
                    if (q.commodity) this.hawb.commodity = q.commodity;

                    // HAWB reactive weight fields
                    if (q.gross_weight_kg) this.hawb.gross_weight_kg = q.gross_weight_kg;
                    if (q.gross_weight_lb) this.hawb.gross_weight_lb = q.gross_weight_lb;
                    if (q.volume_cbm)      this.hawb.volume_cbm = q.volume_cbm;
                    if (q.incoterms_id)    this.hawb.incoterm_id = q.incoterms_id;

                    // ── Load selected charge items ──
                    if (this.selectedQuote && this.selectedQuote.items) {
                        const items = this.selectedQuote.items.filter(item => item.selected !== false);
                        items.forEach(item => {
                            this.chargesList.push({
                                id: null,
                                selected: false,
                                party: 'Custom',
                                party_name_id: '',
                                sal: 'Air',
                                pr: 'Rec',
                                ppc: 'Colle',
                                chrg_code: item.charge_code,
                                charge_name: item.charge_name,
                                currency: item.currency ? (item.currency.code || item.currency) : 'USD',
                                rate: item.rate,
                                qty: item.qty || 1,
                                qty_type: item.unit || 'UNIT',
                                roe: 1.0,
                                vat: 0,
                                inv_no: '',
                                financial_date: new Date().toISOString().split('T')[0],
                                eq_bl_no: '',
                                remark: false,
                                mbl_no: ''
                            });
                        });
                    }

                    this.showQuoteModal = false;
                    if (typeof showToast === 'function') {
                        showToast('success', 'Quotation data loaded successfully');
                    }
                },
                init() {
                    console.log('Alpine JS initialized. Route page:', '{{ $page ?? "" }}');
                    console.log('Location href:', window.location.href);
                    
                    if (this.hawbs.length === 0) {
                        this.addHbl();
                    }
                    if (window.location.search.includes('load_from_quotation=true') || '{{ $page ?? "" }}' === 'air-import.create-quote' || window.location.href.includes('create-quote')) {
                        this.showQuoteModal = true;
                    }
                    
                    if (this.chargesList && Array.isArray(this.chargesList)) {
                        this.chargesList = this.chargesList.map(c => {
                            return {
                                id: c.id || null,
                                _selected: false,
                                selected: false,
                                party: c.party || (c.type === 'AP' ? 'Agent' : 'Custom'),
                                party_name: c.party_name || '',
                                party_name_id: c.type === 'AP' ? (c.vendor_id || '') : (c.bill_to_id || ''),
                                sal: c.sal || 'Air',
                                pr: c.type === 'AP' ? 'Pay' : 'Rec',
                                ppc: c.pc === 'PREPAID' ? 'Prepaid' : 'Colle',
                                chrg_code: c.charge_code || '',
                                currency: (c.currency && c.currency.code) ? c.currency.code : (c.currency || 'USD'),
                                rate: parseFloat(c.rate) || 0,
                                qty: parseFloat(c.qty) || 1,
                                qty_type: c.unit || 'B/L',
                                roe: parseFloat(c.roe) || 1.0,
                                vat: parseFloat(c.vat) || 0,
                                inv_no: c.invoice_no || '',
                                financial_date: c.invoice_date ? (typeof c.invoice_date === 'string' ? c.invoice_date.substring(0, 10) : c.invoice_date) : new Date().toISOString().split('T')[0],
                                eq_bl_no: c.remark || '',
                                remark: !!c.remark,
                                mbl_no: c.mbl_no || '',
                                is_invoiced: !!c.is_invoiced
                            };
                        });
                    }
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
                toolsAction(action) {
                    const shipmentId = this.form ? this.form.id : null;
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const actions = {
                        block: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/air-import/bulk-block', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ ids: [shipmentId] })
                            }).then(r => r.json()).then(d => {
                                showToast(d.success ? 'success' : 'error', d.message || (d.success ? 'Shipment blocked.' : 'Block failed.'));
                            }).catch(() => showToast('error', 'Block request failed.'));
                        },
                        unblock: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/air-import/bulk-unblock', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ ids: [shipmentId] })
                            }).then(r => r.json()).then(d => {
                                showToast(d.success ? 'success' : 'error', d.message || (d.success ? 'Shipment unblocked.' : 'Unblock failed.'));
                            }).catch(() => showToast('error', 'Unblock request failed.'));
                        },
                        copy: () => {
                            if (!shipmentId) return;
                            window.location.href = `/air-import/create?copy=${shipmentId}`;
                        },
                        delete: () => {
                            if (!shipmentId) return;
                            if (!confirm('Are you sure you want to delete this shipment? This action cannot be undone.')) return;
                            fetch(`/air-import/${shipmentId}`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ _method: 'DELETE' })
                            }).then(() => {
                                showToast('success', 'Shipment deleted.');
                                setTimeout(() => window.location.href = '/air-import/list', 800);
                            }).catch(() => showToast('error', 'Delete failed.'));
                        },
                        profit_summary: () => { if (shipmentId) window.open(`/air-import/${shipmentId}/profit-summary`, '_blank'); },
                        profit_detail:  () => { if (shipmentId) window.open(`/air-import/${shipmentId}/profit-detail`, '_blank'); },
                        manifest: () => showToast('info', 'Manifest - feature coming soon.'),
                    };
                    if (actions[action]) {
                        actions[action]();
                    } else {
                        showToast('info', 'Feature coming soon.');
                    }
                },
                hawbBlockAction(idx) {
                    const hawb = this.hawbs[idx];
                    if (!hawb || !hawb.id) { showToast('warning', 'Save the HAWB first.'); return; }
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const endpoint = hawb.is_blocked ? '/air-import/hbl-bulk-unblock' : '/air-import/hbl-bulk-block';
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
                openTrackTrace() {
                    const mawb = document.querySelector('[name="mawb_no"]')?.value || (this.hawbs && this.hawbs.length > 0 ? this.hawbs[0].hbl_no : '');
                    window.openTrackTrace({ type: 'aircargo', number: mawb });
                }
            }
        }
    </script>

    <div class="page-content" x-data="airImportModule()" x-init="init()" style="display: flex; width: 100%;">
        <form action="{{ isset($airImport) ? route('air-import.update', $airImport->id) : route('air-import.store') }}" method="POST" id="air-import-form" style="width: 100%;" novalidate>
            @csrf
            @if(isset($airImport)) @method('PUT') @endif

        <!-- Breadcrumbs -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/air-import/list">Air Import</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">{{ isset($airImport) ? 'Edit Shipment: ' . $airImport->file_no : 'New Shipment' }}</span></li>
            </ul>
        </div>

        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h1 class="caption-subject" style="font-size: 18px; text-transform: uppercase;">{{ isset($airImport) ? 'AIR IMPORT SHIPMENT' : 'New Air Import Shipment' }}</h1>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" @click="showAiModal = true" class="btn-freightx" style="background:#337ab7; border-color:#286090; color:white; font-weight:bold;">
                    <i class="fa fa-magic"></i> AI Auto-Fill
                </button>
                <button type="submit" form="air-import-form" class="btn-gf-inline"><i class="fa fa-save"></i> SAVE SHIPMENT</button>
                <a href="/air-import/list" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <!-- Main Tabs + Tools Tabs -->
        <div style="display:flex;align-items:stretch;border-bottom:2px solid #e5e7eb;margin-bottom:12px;width:100%;">
            <!-- Sticky Main Tabs (Fixed on left) -->
            <ul class="gf-tabs" style="border-bottom:none;margin-bottom:0;display:flex;flex-shrink:0;">
                <li :class="activeTab === 'basic' ? 'active' : ''" @click="activeTab = 'basic'"><a>Main</a></li>
                <li :class="[activeTab === 'charges' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'charges' : null"><a>Charges</a></li>
                <li :class="[activeTab === 'history' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'history' : null"><a>History</a></li>
                <li :class="[activeTab === 'filing' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'filing' : null"><a>Filing</a></li>
            </ul>

            <!-- Divider -->
            <div style="display:flex;align-items:center;padding:0 4px;flex-shrink:0;">
                <span style="border-left:2px solid #e5e7eb;height:20px;display:inline-block;"></span>
            </div>

            <!-- Tools Options (Dropdown) -->
            <div style="display:flex; align-items:center; padding:0 10px; margin-left: auto;">
                <div style="position: relative;" x-data="{ showToolsMenu: false }" @click.away="showToolsMenu = false" x-show="saved">
                    <button type="button" @click="showToolsMenu = !showToolsMenu" class="btn-default-gf" :class="!saved ? 'disabled-tab' : ''" style="display:flex; align-items:center; gap:5px; height: 28px; background: #fff;">
                        <i class="fa fa-wrench"></i> Tools <i class="fa fa-angle-down"></i>
                    </button>
                    <div x-show="showToolsMenu" style="position: absolute; top: 100%; right: 0; margin-top: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 50; min-width: 200px; display: none;">
                        <ul style="list-style: none; padding: 4px 0; margin: 0;">
                            <li><button type="button" @click="saved ? toolsAction('block') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-lock" style="width:14px; text-align:center; margin-right:4px;"></i> Block</button></li>
                            <li><button type="button" @click="saved ? toolsAction('copy') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-copy" style="width:14px; text-align:center; margin-right:4px;"></i> Copy</button></li>
                            <li><button type="button" @click="saved ? toolsAction('delete') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer; color:#ef4444;"><i class="fa fa-trash-o" style="width:14px; text-align:center; margin-right:4px; color:#ef4444;"></i> Delete</button></li>
                            <li><button type="button" @click="saved ? toolsAction('manifest') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-file-text-o" style="width:14px; text-align:center; margin-right:4px;"></i> Manifest</button></li>
                            <li><button type="button" @click="saved ? toolsAction('profit_summary') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-bar-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Summary</button></li>
                            <li><button type="button" @click="saved ? toolsAction('profit_detail') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-pie-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Report</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding-bottom: 50px; width: 100%;">
            <!-- BASIC TAB -->
            <div x-show="activeTab === 'basic'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div @click="showMblSection = !showMblSection" class="portlet-title" style="cursor: pointer; background: #f9fafb;">
                        <span class="caption-subject"><i class="fa" :class="showMblSection ? 'fa-minus-square-o' : 'fa-plus-square-o'"></i> MAWB</span>
                        <div class="actions">
                            <i class="fa fa-angle-down transition-transform" :class="showMblSection ? 'rotate-180' : ''"></i>
                        </div>
                    </div>
                    <div class="portlet-body" x-show="showMblSection">
                        <!-- Reminder Section for MAWB -->
                        <div class="memo-section" style="margin-bottom: 10px; display: none;">
                            <div class="memo-header" @click="showMblMemo = !showMblMemo">
                                <span>Note</span>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <button type="button" class="btn-memo-doc" @click.stop="showDocModal = true">Document (0) <i class="fa fa-external-link"></i></button>
                                    <i class="fa" :class="showMblMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                                </div>
                            </div>
                            <div class="memo-body" x-show="showMblMemo">
                                <div style="display: flex; gap: 10px;">
                                    <div style="flex: 2;">
                                        <table class="memo-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 30px; background: #32c5d2; border: none; text-align: center;"><i class="fa fa-plus"></i></th>
                                                    <th><i class="fa fa-bell"></i> Subject <i class="fa fa-sort float-right opacity-50"></i></th>
                                                    <th>Last Modified <i class="fa fa-sort float-right opacity-50"></i></th>
                                                    <th>Created <i class="fa fa-sort float-right opacity-50"></i></th>
                                                    <th>Action / TP</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" style="text-align: center; color: #999; padding: 10px;">No records found.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div style="flex: 1;">
                                        <textarea class="memo-content-area" placeholder="Reminder content..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Booking no</label><div class="form-input-container"><input type="text" name="file_no" class="form-control-gf" value="{{ $airImport->file_no ?? 'MAI-'.date('ymd').'-'.str_pad((\App\Models\AirImport::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT) }}" readonly style="background:#f5f5f5;"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*MAWB No.</label><div class="form-input-container"><input type="text" name="mawb_no" class="form-control-gf" value="{{ $airImport->mawb_no ?? '' }}" required></div></div>
                            <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*Office</label><div class="form-input-container"><select name="office_id" class="form-control-gf" required><option value="">Select Office...</option>@foreach($offices as $office)<option value="{{ $office->id }}" {{ (isset($airImport) && $airImport->office_id == $office->id) ? 'selected' : '' }}>{{ $office->code }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Type</label><div class="form-input-container"><select name="awb_type" class="form-control-gf"><option value="NORMAL" {{ (isset($airImport) && $airImport->awb_type == 'NORMAL') ? 'selected' : 'selected' }}>NORMAL</option><option value="DIRECT" {{ (isset($airImport) && $airImport->awb_type == 'DIRECT') ? 'selected' : '' }}>DIRECT</option></select></div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">Post Date</label><div class="form-input-container"><input type="date" name="post_date" class="form-control-gf" value="{{ old('post_date', isset($airImport->post_date) ? (is_string($airImport->post_date) ? substr($airImport->post_date, 0, 10) : $airImport->post_date->format('Y-m-d')) : date('Y-m-d')) }}" readonly style="background:#f5f5f5;"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><select name="oversea_agent_id" class="form-control-gf"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" {{ old('oversea_agent_id', $airImport->oversea_agent_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Carrier</label><div class="form-input-container"><select name="carrier_id" class="form-control-gf"><option value="">Select...</option>@foreach($carriers as $carrier)<option value="{{ $carrier->id }}" {{ old('carrier_id', $airImport->carrier_id ?? '') == $carrier->id ? 'selected' : '' }}>{{ $carrier->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">AWB Acct. Carrier</label><div class="form-input-container"><select name="acct_carrier_id" class="form-control-gf"><option value="">Select...</option>@foreach($carriers as $carrier)<option value="{{ $carrier->id }}" {{ old('acct_carrier_id', $airImport->acct_carrier_id ?? '') == $carrier->id ? 'selected' : '' }}>{{ $carrier->name }}</option>@endforeach</select></div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Co-loader</label><div class="form-input-container"><select name="coloader_id" class="form-control-gf"><option value="">Select...</option>@foreach($coloaders as $cl)<option value="{{ $cl->id }}" {{ old('coloader_id', $airImport->coloader_id ?? '') == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><input type="text" name="op_name" class="form-control-gf" value="{{ old('op_name', isset($airImport) ? ($airImport->operator->name ?? auth()->user()->name) : auth()->user()->name) }}" readonly style="background:#f5f5f5;"><input type="hidden" name="op_id" value="{{ old('op_id', isset($airImport) ? ($airImport->op_id ?? auth()->id()) : auth()->id()) }}"></div></div>
                            <div></div>
                            <div></div>

                            <!-- Row 4 (Direct Master) -->
                            <div class="form-group-gf"><label class="form-label-gf">Direct Master</label><div class="form-input-container" style="justify-content: flex-start;"><input type="checkbox" name="is_direct_master" value="1" {{ (isset($airImport) && $airImport->is_direct_master) ? 'checked' : '' }} x-model="isDirectMaster"></div></div>
                            <div class="form-group-gf" x-show="isDirectMaster" x-cloak><label class="form-label-gf">Customer</label><div class="form-input-container"><select name="dm_customer_id" class="form-control-gf"><option value="">Select...</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" {{ old('dm_customer_id', $airImport->dm_customer_id ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" x-show="isDirectMaster" x-cloak><label class="form-label-gf">Shipper</label><div class="form-input-container"><select name="dm_shipper_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ old('dm_shipper_id', $airImport->dm_shipper_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" x-show="isDirectMaster" x-cloak><label class="form-label-gf">Consignee</label><div class="form-input-container"><select name="dm_consignee_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ old('dm_consignee_id', $airImport->dm_consignee_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>

                            <!-- Row 5 (Direct Master) -->
                            <div class="form-group-gf" x-show="isDirectMaster" x-cloak><label class="form-label-gf">Notify</label><div class="form-input-container"><select name="dm_notify_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ old('dm_notify_id', $airImport->dm_notify_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" x-show="isDirectMaster" x-cloak><label class="form-label-gf">Bill To</label><div class="form-input-container"><select name="dm_bill_to_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ old('dm_bill_to_id', $airImport->dm_bill_to_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" x-show="isDirectMaster" x-cloak><label class="form-label-gf">Sales</label><div class="form-input-container"><select name="dm_sales_person_id" class="form-control-gf"><option value="">Select...</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ old('dm_sales_person_id', $airImport->dm_sales_person_id ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" x-show="isDirectMaster" x-cloak><label class="form-label-gf">Sales Type</label><div class="form-input-container"><select name="dm_sales_type" class="form-control-gf"><option value="">Select...</option><option value="NOMINATED" {{ old('dm_sales_type', $airImport->dm_sales_type ?? '') == 'NOMINATED' ? 'selected' : '' }}>NOMINATED</option><option value="FREE HAND" {{ old('dm_sales_type', $airImport->dm_sales_type ?? '') == 'FREE HAND' ? 'selected' : '' }}>FREE HAND</option><option value="DIRECT" {{ old('dm_sales_type', $airImport->dm_sales_type ?? '') == 'DIRECT' ? 'selected' : '' }}>DIRECT</option></select></div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Departure</label><div class="form-input-container"><select name="dep_port_id" class="form-control-gf"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('dep_port_id', $airImport->dep_port_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->code }} - {{ $port->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Destination</label><div class="form-input-container"><select name="dst_port_id" class="form-control-gf"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('dst_port_id', $airImport->dst_port_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->code }} - {{ $port->name }}</option>@endforeach</select></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Flight No.</label><div class="form-input-container"><input type="text" name="flight_no" class="form-control-gf" value="{{ old('flight_no', $airImport->flight_no ?? '') }}"></div></div>
                            <div></div>

                            <!-- Row 2 -->
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">ETD</label><div class="form-input-container"><input type="datetime-local" name="etd" class="form-control-gf" value="{{ old('etd', isset($airImport) && $airImport->etd ? $airImport->etd->format('Y-m-d\TH:i') : '') }}"></div></div>
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">ATD</label><div class="form-input-container"><input type="datetime-local" name="atd" class="form-control-gf" value="{{ old('atd', isset($airImport) && $airImport->atd ? $airImport->atd->format('Y-m-d\TH:i') : '') }}"></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf" style="color:red;">*ETA</label><div class="form-input-container"><input type="datetime-local" name="eta" class="form-control-gf" value="{{ old('eta', isset($airImport) && $airImport->eta ? $airImport->eta->format('Y-m-d\TH:i') : '') }}" required></div></div>
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">ATA</label><div class="form-input-container"><input type="datetime-local" name="ata" class="form-control-gf" value="{{ old('ata', isset($airImport) && $airImport->ata ? $airImport->ata->format('Y-m-d\TH:i') : '') }}"></div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf"><label class="form-label-gf">Freight Location</label><div class="form-input-container"><select name="freight_location_id" class="form-control-gf"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('freight_location_id', $airImport->freight_location_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Storage Start Date</label><div class="form-input-container"><input type="date" name="storage_start_date" class="form-control-gf" value="{{ old('storage_start_date', $airImport->storage_start_date ?? '') }}"></div></div>
                            <div></div>
                            <div></div>
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
                                                            <select :name="'routes['+idx+'][airport_id]'" class="form-control-gf" x-model="route.airport_id" style="width:100%; border:none; height:24px; padding:2px 4px;">
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
                                                                @foreach($agents as $agent)
                                                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
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

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Package</label>
                                <div class="form-input-container" style="gap: 2px;">
                                    <input type="number" name="pkg_qty" class="form-control-gf" style="flex:1; min-width:0;" value="{{ old('pkg_qty', $airImport->pkg_qty ?? '') }}" step="1" min="0">
                                    <select name="pkg_unit_id" class="form-control-gf" style="flex:1; min-width:0;"><option value="">Select...</option>@foreach($packageUnits as $unit)<option value="{{ $unit->id }}" {{ old('pkg_unit_id', $airImport->pkg_unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>@endforeach</select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Chargeable Weight</label>
                                <div class="form-input-container" style="gap: 4px; align-items:center;">
                                    <input type="number" name="chargeable_weight_kg" class="form-control-gf" style="flex:1; min-width:0;" value="{{ old('chargeable_weight_kg', $airImport->chargeable_weight_kg ?? '') }}" step="0.01" min="0"> <span style="font-size:10px; color:#555;">KG</span>
                                    <input type="number" name="chargeable_weight_lb" class="form-control-gf" style="flex:1; min-width:0;" value="{{ old('chargeable_weight_lb', $airImport->chargeable_weight_lb ?? '') }}" step="0.01" min="0"> <span style="font-size:10px; color:#555;">LB</span>
                                </div>
                            </div>
                            <div class="form-group-gf" style="grid-column: span 2;">
                                <label class="form-label-gf">Volume Weight</label>
                                <div class="form-input-container" style="gap: 8px; align-items:center;">
                                    <button type="button" @click="console.log('🔴 Button clicked!'); openDimensionsModal('mawb')" class="btn-tool" style="background:#5c9bd1; border:none; padding:2px 8px; flex-shrink:0;">Set Dimensions</button>
                                    <input type="number" name="volume_weight_kg" class="form-control-gf" style="flex:1; min-width:0;" value="{{ old('volume_weight_kg', $airImport->volume_weight_kg ?? '') }}" step="0.01" min="0"> <span style="font-size:10px; color:#555;">KG</span>
                                    <input type="number" name="volume_cbm" class="form-control-gf" style="flex:1; min-width:0;" value="{{ old('volume_cbm', $airImport->volume_cbm ?? '') }}" step="0.001" min="0"> <span style="font-size:10px; color:#555;">CBM</span>
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Gross Weight</label>
                                <div class="form-input-container" style="gap: 4px; align-items:center;">
                                    <input type="number" name="gross_weight_kg" class="form-control-gf" style="flex:1; min-width:0;" value="{{ old('gross_weight_kg', $airImport->gross_weight_kg ?? '') }}" step="0.01" min="0"> <span style="font-size:10px; color:#555;">KG</span>
                                    <input type="number" name="gross_weight_lb" class="form-control-gf" style="flex:1; min-width:0;" value="{{ old('gross_weight_lb', $airImport->gross_weight_lb ?? '') }}" step="0.01" min="0"> <span style="font-size:10px; color:#555;">LB</span>
                                </div>
                            </div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Freight</label><div class="form-input-container"><select name="freight_term" class="form-control-gf"><option value="">Select...</option><option value="PREPAID" {{ old('freight_term', $airImport->freight_term ?? '') == 'PREPAID' ? 'selected' : '' }}>PREPAID</option><option value="COLLECT" {{ old('freight_term', $airImport->freight_term ?? '') == 'COLLECT' ? 'selected' : '' }}>COLLECT</option></select></div></div>

                            <div class="form-group-gf"><label class="form-label-gf">Transportation</label><div class="form-input-container"><input type="text" name="transportation" class="form-control-gf" value="{{ old('transportation', $airImport->transportation ?? '') }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><select name="incoterm_id" class="form-control-gf"><option value="">Select...</option>@foreach($incoterms as $inco)<option value="{{ $inco->id }}" {{ old('incoterm_id', $airImport->incoterm_id ?? '') == $inco->id ? 'selected' : '' }}>{{ $inco->code }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><select name="svc_term_from_id" class="form-control-gf" style="width:45%;"><option value="">Select...</option>@foreach($serviceTerms as $term)<option value="{{ $term->id }}" {{ old('svc_term_from_id', $airImport->svc_term_from_id ?? '') == $term->id ? 'selected' : '' }}>{{ $term->code }}</option>@endforeach</select><span class="mx-1">~</span><select name="svc_term_to_id" class="form-control-gf" style="width:45%;"><option value="">Select...</option>@foreach($serviceTerms as $term)<option value="{{ $term->id }}" {{ old('svc_term_to_id', $airImport->svc_term_to_id ?? '') == $term->id ? 'selected' : '' }}>{{ $term->code }}</option>@endforeach</select></div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">Business Referred By</label><div class="form-input-container"><select name="referred_by_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ old('referred_by_id', $airImport->referred_by_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Stackable</label><div class="form-input-container" style="font-size:10px; gap:4px; justify-content: flex-start; align-items:center;"><input type="radio" name="stackable" value="1" {{ old('stackable', $airImport->stackable ?? 1) == 1 ? 'checked' : '' }}> Yes <input type="radio" name="stackable" value="0" {{ old('stackable', $airImport->stackable ?? 1) == 0 ? 'checked' : '' }}> No</div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container"><select name="cargo_type" class="form-control-gf"><option value="">Select...</option><option value="GENERAL CARGO" {{ old('cargo_type', $airImport->cargo_type ?? '') == 'GENERAL CARGO' ? 'selected' : '' }}>GENERAL CARGO</option><option value="DANGEROUS GOODS" {{ old('cargo_type', $airImport->cargo_type ?? '') == 'DANGEROUS GOODS' ? 'selected' : '' }}>DANGEROUS GOODS</option><option value="PERISHABLE" {{ old('cargo_type', $airImport->cargo_type ?? '') == 'PERISHABLE' ? 'selected' : '' }}>PERISHABLE</option></select></div></div>
                            <div></div>
                        </div>

                        <div style="height: 5px;"></div>
                        <div style="margin-bottom: 10px;">
                            <button type="button" @click="showMore = !showMore" class="btn-default-gf" style="border:none; color:#00827f; font-weight:700;">
                                <span x-text="showMore ? 'More [-]' : 'More [+]'"></span>
                            </button>
                        </div>

                        <div class="form-grid-4" x-show="showMore">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">E-Commerce</label><div class="form-input-container" style="justify-content: flex-start; align-items:center;"><input type="checkbox" name="is_ecommerce" value="1" {{ old('is_ecommerce', $airImport->is_ecommerce ?? false) ? 'checked' : '' }} style="width: 14px; height: 14px;"></div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>

                        <div style="display: none; justify-content: flex-end; margin-top: 15px; margin-bottom: 5px; align-items: center; gap: 10px;">
                            <label class="form-label-gf" style="width:auto; margin:0;">Display Unit</label>
                            <select class="form-control-gf" style="width: 150px;"><option>Show Both</option><option>show KG/CBM</option><option>show LB/CFT</option></select>
                        </div>
                    </div>
                </div>
                <!-- End of Main MAWB Form -->

                <!-- House B/L (HAWB) Section -->
                <template x-for="(hawb, index) in hawbs" :key="index">
                    <div class="portlet light" style="margin-top: 5px;">
                        <div class="portlet-title" style="background: #f2bc00; color: #fff; cursor: pointer; min-height: 24px; padding: 2px 10px;" @click="hawb.show = !hawb.show">
                            <span class="caption-subject" style="color: #fff; font-size: 11px;"><i class="fa fa-user"></i> HAWB Information <small style="color:rgba(255,255,255,0.8); margin-left: 10px; font-weight: normal;">OP : {{ auth()->user()->name ?? 'DEMO_USER' }}</small></span>
                            <div class="actions" style="display: flex; gap: 8px; align-items: center;" @click.stop>
                                <!-- HAWB Tools Dropdown -->
                                <div style="position: relative;" x-data="{ openHawbMenu: false }" @click.away="openHawbMenu = false">
                                    <button type="button" @click="openHawbMenu = !openHawbMenu"
                                        style="display: flex; align-items: center; gap: 4px; background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(0,0,0,0.1); border-radius: 3px; padding: 2px 8px; font-size: 11px; font-weight: 600; color: #374151; cursor: pointer; height: 22px;">
                                        <i class="fa fa-wrench" style="color: #4b5563; font-size: 10px;"></i> Tools <i class="fa fa-angle-down" style="font-size: 10px;"></i>
                                    </button>
                                    <div x-show="openHawbMenu" style="position: absolute; top: 100%; right: 0; margin-top: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15); z-index: 100; min-width: 190px; text-align: left; display: none;">
                                        <ul style="list-style: none; padding: 4px 0; margin: 0;">
                                            <li><button type="button" @click="saved && hawb && hawb.id ? hawbBlockAction(index) : showToast('info', 'Save HAWB first.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-lock" style="width:14px; text-align:center; margin-right:4px;"></i> Block</button></li>
                                            <li><button type="button" @click="showToast('info', 'Copy - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-copy" style="width:14px; text-align:center; margin-right:4px;"></i> Copy</button></li>
                                            <li><button type="button" @click="removeHbl(index); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#ef4444; cursor:pointer;"><i class="fa fa-trash-o" style="width:14px; text-align:center; margin-right:4px; color:#ef4444;"></i> Delete</button></li>
                                            <li><button type="button" @click="showToast('info', 'Copy To - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-share" style="width:14px; text-align:center; margin-right:4px;"></i> Copy To</button></li>
                                            <li><button type="button" @click="showToast('info', 'Move To - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-arrow-right" style="width:14px; text-align:center; margin-right:4px;"></i> Move to</button></li>
                                            <li><button type="button" @click="showToast('info', 'Arrival Notice - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-envelope-o" style="width:14px; text-align:center; margin-right:4px;"></i> Arrivel Notice</button></li>
                                            <li><button type="button" @click="showToast('info', 'Delivery Order - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-truck" style="width:14px; text-align:center; margin-right:4px;"></i> Delevery Order</button></li>
                                            <li><button type="button" @click="showToast('info', 'Release Order - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-unlock-alt" style="width:14px; text-align:center; margin-right:4px;"></i> Release order</button></li>
                                            <li><button type="button" @click="showToast('info', 'Carrier Certificate - feature coming soon.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-certificate" style="width:14px; text-align:center; margin-right:4px;"></i> Carrier Certificate</button></li>
                                            <li><button type="button" @click="saved && hawb && hawb.id ? window.open('/air-import/' + form.id + '/profit-detail', '_blank') : showToast('info', 'Save HAWB first.'); openHawbMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-pie-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Report</button></li>
                                        </ul>
                                    </div>
                                </div>
                                <i @click.stop="removeHbl(index)" class="fa fa-times" style="font-size: 12px; opacity: 0.8; cursor: pointer; color: #fff; margin-left: 4px;"></i>
                                <i class="fa fa-angle-down transition-transform" :class="hawb.show ? 'rotate-180' : ''" style="font-size: 12px; color: #fff;"></i>
                            </div>
                        </div>
                        <div class="portlet-body" x-show="hawb.show" style="background: #f9f9f9; padding: 10px;">
                            
                            <div class="memo-section" style="margin-bottom: 15px; display: none;">
                                <div class="memo-header" @click="hawb.showMemo = !hawb.showMemo" style="background:#fff; border:1px solid #eef1f5; padding:4px 10px; display:flex; justify-content:space-between; align-items:center; cursor:pointer;">
                                    <span style="font-size:11px; font-weight:600; color:#666;">Note</span>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <button type="button" class="btn-memo-doc" style="background:#eee; border:1px solid #ccc; font-size:10px; padding:1px 8px;" @click.stop="showDocModal = true">Document (0) <i class="fa fa-external-link"></i></button>
                                        <i class="fa" :class="hawb.showMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                                    </div>
                                </div>
                                <div class="memo-body" x-show="hawb.showMemo" style="border:1px solid #eef1f5; border-top:none; background:#fff; padding:10px;">
                                    <div style="display: flex; gap: 10px;">
                                        <div style="flex: 2;">
                                            <table class="memo-table" style="width:100%; border-collapse:collapse; font-size:11px;">
                                                <thead>
                                                    <tr style="background:#f9f9f9;">
                                                        <th style="width: 30px; background: #32c5d2; border: none; text-align: center; color:#fff;"><i class="fa fa-plus"></i></th>
                                                        <th style="border:1px solid #eee; padding:5px; text-align:left;"><i class="fa fa-bell"></i> Subject</th>
                                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">Last Modified</th>
                                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">Created</th>
                                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">Action / TP</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="5" style="text-align: center; color: #999; padding: 10px; border:1px solid #eee;">No records found.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div style="flex: 1;">
                                            <textarea style="width:100%; height:80px; font-size:11px; padding:5px; border:1px solid #eee; resize:none;" placeholder="Reminder content..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid-4">
                                <!-- Row 1 -->
                                <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*HAWB No.</label><div class="form-input-container"><input type="text" class="form-control-gf" name="hbl_no" x-model="hawb.hbl_no" required></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Quotation No.</label><div class="form-input-container"><select class="form-control-gf" disabled style="background:#eee;"><option value="">Select...</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">HSN</label><div class="form-input-container"><input type="text" class="form-control-gf" :name="'hbls['+index+'][hsn]'" x-model="hawb.hsn"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][customer_id]'" x-model="hawb.customer_id"><option value="">Select...</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select></div></div>

                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Shipper</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][shipper_id]'" x-model="hawb.shipper_id"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-edit"></i></button><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-external-link-square"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][consignee_id]'" x-model="hawb.consignee_id"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-edit"></i></button><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-external-link-square"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][notify_party_id]'" x-model="hawb.notify_id"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-edit"></i></button><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-external-link-square"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><input type="text" class="form-control-gf" value="{{ auth()->user()->name ?? 'DEMO_USER' }}" disabled style="background:#eee;"></div></div>

                                <!-- Row 3 -->
                                <div class="form-group-gf"><label class="form-label-gf">Bill To</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][bill_to_id]'" x-model="hawb.bill_to_id"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-external-link-square"></i></button></div></div>
                                <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Customs Broker</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][customs_broker_id]'" x-model="hawb.customs_broker_id"><option value="">Select...</option>@foreach($brokers as $broker)<option value="{{ $broker->id }}">{{ $broker->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-external-link-square"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][sales_person_id]'" x-model="hawb.sales_person_id"><option value="">Select...</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div></div>
                                <div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div class="form-grid-4">
                                <!-- Row 1 -->
                                <div class="form-group-gf"><label class="form-label-gf">Freight Location</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][freight_location_id]'" x-model="hawb.freight_location_id"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}">{{ $port->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Final Destination</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][final_destination_id]'" x-model="hawb.final_destination_id"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}">{{ $port->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Final ETA</label><div class="form-input-container"><input type="date" class="form-control-gf" :name="'hbls['+index+'][final_eta]'" x-model="hawb.final_eta"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Delivery Location</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][delivery_location_id]'" x-model="hawb.delivery_location_id"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-external-link-square"></i></button></div></div>

                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Trucker</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][trucker_id]'" x-model="hawb.trucker_id"><option value="">Select...</option>@foreach($truckers as $trucker)<option value="{{ $trucker->id }}">{{ $trucker->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" style="height:20px; padding:0 4px;"><i class="fa fa-external-link-square"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Last Free Day</label><div class="form-input-container"><input type="date" class="form-control-gf" :name="'hbls['+index+'][last_free_day]'" x-model="hawb.last_free_day"></div></div>
                                <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Storage Start Date</label><div class="form-input-container"><input type="date" class="form-control-gf" :name="'hbls['+index+'][storage_start_date]'" x-model="hawb.storage_start_date"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Freight</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][freight_term]'" x-model="hawb.freight_term"><option value="">Select...</option><option value="COLLECT">COLLECT</option><option value="PREPAID">PREPAID</option></select></div></div>

                                <div class="form-group-gf"><label class="form-label-gf">Transportation</label><div class="form-input-container"><input type="text" class="form-control-gf" :name="'hbls['+index+'][transportation]'" x-model="hawb.transportation"></div></div>
                            </div>
                            <div class="form-grid-4">
                                <div class="form-group-gf"><label class="form-label-gf">Sales Type</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][sales_type]'" x-model="hawb.sales_type"><option value="">Select...</option><option value="CO-LOAD">CO-LOAD</option><option value="FREE CARGO">FREE CARGO</option><option value="NOMI">NOMI</option></select></div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div class="form-grid-4">
                                <!-- Row 1 -->
                                <div class="form-group-gf"><label class="form-label-gf">Package</label><div class="form-input-container" style="gap:2px;"><input type="text" class="form-control-gf" style="width:40%;" :name="'hbls['+index+'][pkg_qty]'" x-model="hawb.pkg_qty"><select class="form-control-gf" style="width:60%;" :name="'hbls['+index+'][pkg_unit_id]'" x-model="hawb.pkg_unit_id"><option value="">Select...</option>@foreach($packageUnits as $unit)<option value="{{ $unit->id }}">{{ $unit->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Chargeable Weight</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" class="form-control-gf" style="flex:1;" :name="'hbls['+index+'][chargeable_weight_kg]'" x-model="hawb.chargeable_weight_kg"> <span style="font-size:10px;">KG</span> <input type="text" class="form-control-gf" style="flex:1;" :name="'hbls['+index+'][chargeable_weight_lb]'" x-model="hawb.chargeable_weight_lb"> <span style="font-size:10px;">LB</span></div></div>
                                <div class="form-group-gf" style="grid-column: span 2;"><label class="form-label-gf">Volume Weight</label><div class="form-input-container" style="gap:8px; align-items:center;"><button type="button" class="btn-tool" style="background:#5c9bd1; border:none; padding:2px 8px; flex-shrink:0;" @click="openDimensionsModal(index)">Set Dimensions</button><input type="text" class="form-control-gf" style="flex:1;" :name="'hbls['+index+'][volume_weight_kg]'" x-model="hawb.volume_weight_kg"> <span style="font-size:10px;">KG</span> <input type="text" class="form-control-gf" style="flex:1;" :name="'hbls['+index+'][volume_cbm]'" x-model="hawb.volume_cbm"> <span style="font-size:10px;">CBM</span></div></div>

                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Gross Weight</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" class="form-control-gf" style="flex:1;" :name="'hbls['+index+'][gross_weight_kg]'" x-model="hawb.gross_weight_kg"> <span style="font-size:10px;">KG</span> <input type="text" class="form-control-gf" style="flex:1;" :name="'hbls['+index+'][gross_weight_lb]'" x-model="hawb.gross_weight_lb"> <span style="font-size:10px;">LB</span></div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div class="form-grid-4">
                                <!-- Row 1 -->
                                <div class="form-group-gf"><label class="form-label-gf">Entry No.</label><div class="form-input-container"><input type="text" class="form-control-gf" name="entry_no" x-model="hawb.entry_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Class of Entry</label><div class="form-input-container"><input type="text" class="form-control-gf" name="class_of_entry" x-model="hawb.class_of_entry"></div></div>
                                <div class="form-group-gf">
    <label class="form-label-gf">Frt. Released</label>

    <div class="form-input-container" style="gap:5px; align-items:center;">
        <input
            type="checkbox"
            name="frt_released"
            x-model="hawb.frt_released"
            @change="if (!hawb.frt_released) hawb.frt_released_date = null"
        >

        <input
            type="date"
            class="form-control-gf"
            name="frt_released_date"
            x-model="hawb.frt_released_date"
            :disabled="!hawb.frt_released"
        >
    </div>
</div>
                                <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><select class="form-control-gf" style="width:45%;" name="service_term_from_id" x-model="hawb.service_term_from_id"><option value="">Select...</option>@foreach($serviceTerms as $term)<option value="{{ $term->id }}">{{ $term->code }}</option>@endforeach</select><span class="mx-1">~</span><select class="form-control-gf" style="width:45%;" name="service_term_to_id" x-model="hawb.service_term_to_id"><option value="">Select...</option>@foreach($serviceTerms as $term)<option value="{{ $term->id }}">{{ $term->code }}</option>@endforeach</select></div></div>

                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Released By</label><div class="form-input-container"><select class="form-control-gf" disabled :name="'hbls['+index+'][released_by_id]'" x-model="hawb.released_by_id"><option value="">Select...</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Cargo Released To</label><div class="form-input-container"><input type="text" class="form-control-gf" :name="'hbls['+index+'][cargo_released_to]'" x-model="hawb.cargo_released_to"></div></div>
                                <div class="form-group-gf" style="display: none;"><label class="form-label-gf">C. Released Date</label><div class="form-input-container"><input type="date" class="form-control-gf" :name="'hbls['+index+'][c_released_date]'" x-model="hawb.c_released_date"></div></div>
                                <div></div>

                                <!-- Row 3 -->
                                <div class="form-group-gf"><label class="form-label-gf">Door Delivered</label><div class="form-input-container"><input type="date" class="form-control-gf" :name="'hbls['+index+'][door_delivered_date]'" x-model="hawb.door_delivered_date"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Ship Type</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][ship_type]'" x-model="hawb.ship_type"><option value="">Select...</option><option value="NORMAL">NORMAL</option><option value="S/W">S/W</option><option value="T/S">T/S</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><select class="form-control-gf" :name="'hbls['+index+'][incoterm_id]'" x-model="hawb.incoterm_id"><option value="">Select...</option>@foreach($incoterms as $inco)<option value="{{ $inco->id }}">{{ $inco->code }}</option>@endforeach</select></div></div>
                                <div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div style="margin-bottom: 10px;">
                                <button type="button" @click="hawb.showMore = !hawb.showMore" class="btn-default-gf" style="border:none; color:#00827f; font-weight:700;">
                                    <span x-text="hawb.showMore ? 'More [-]' : 'More [+]'"></span>
                                </button>
                            </div>

                            <div class="form-grid-4" x-show="hawb.showMore">
                                <div class="form-group-gf"><label class="form-label-gf">E-Commerce</label><div class="form-input-container" style="justify-content: flex-start; align-items:center;"><input type="checkbox" style="width: 14px; height: 14px;" name="is_ecommerce" x-model="hawb.is_ecommerce"></div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                <span style="font-size: 11px; font-weight: 600; color: #444;">Sub HAWB</span>
                                <div style="display: flex; gap: 5px;">
                                    <button type="button" class="btn-tool-icon" style="background:#26c281; color:#fff; border-color:#26c281; padding:0 6px;" @click="hawb.subHawbs.push({no:'', description:'', pcs:'', unit:'CARTON(S)', amount:'', selected:false})"><i class="fa fa-plus"></i></button>
                                    <button type="button" class="btn-tool-icon" :disabled="hawb.subHawbs.filter(s=>s.selected).length === 0" :style="hawb.subHawbs.filter(s=>s.selected).length === 0 ? 'opacity:0.4; cursor:not-allowed;' : 'color:red; border-color:red;'" style="padding:0 6px;" @click="hawb.subHawbs = hawb.subHawbs.filter(s=>!s.selected)"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:10px;">
                                <thead>
                                    <tr style="background:#f9f9f9;">
                                        <th style="width: 30px; text-align: center; border:1px solid #eee;"><input type="checkbox" @change="hawb.subHawbs.forEach(s => s.selected = $event.target.checked)"></th>
                                        <th style="border:1px solid #eee; padding:5px; text-align:left;"><span style="color:red;">*</span>Sub HAWB</th>
                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">Description / IT No.</th>
                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">PCS</th>
                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">PKG Unit</th>
                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(sub, si) in hawb.subHawbs" :key="si">
                                        <tr :style="sub.selected ? 'background:#fef9e7;' : ''">
                                            <td style="text-align: center; border:1px solid #eee;"><input type="checkbox" x-model="sub.selected"></td>
                                            <td style="border:1px solid #eee; padding:2px;"><input type="text" class="form-control-gf" x-model="sub.no" placeholder="Sub HAWB No." style="border:none;"></td>
                                            <td style="border:1px solid #eee; padding:2px;"><input type="text" class="form-control-gf" x-model="sub.description" style="border:none;"></td>
                                            <td style="border:1px solid #eee; padding:2px;"><input type="number" class="form-control-gf" x-model="sub.pcs" style="border:none; width:60px;"></td>
                                            <td style="border:1px solid #eee; padding:2px;"><select class="form-control-gf" x-model="sub.unit" style="border:none;"><option>CARTON(S)</option><option>PALLET(S)</option><option>PIECE(S)</option></select></td>
                                            <td style="border:1px solid #eee; padding:2px;"><input type="number" class="form-control-gf" x-model="sub.amount" style="border:none;"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="hawb.subHawbs.length === 0"><td colspan="6" style="text-align: center; color: #999; padding: 10px; border:1px solid #eee;">No records found. Click <strong style="color:#26c281; cursor:pointer;" @click="hawb.subHawbs.push({no:'',description:'',pcs:'',unit:'CARTON(S)',amount:'',selected:false})">+ Add</strong> to create one.</td></tr>
                                </tbody>
                            </table>

                            <div style="display: none; justify-content: flex-end; margin-top: 10px; align-items: center; gap: 10px;">
                                <label class="form-label-gf" style="width:auto; margin:0;">Display Unit</label>
                                <select class="form-control-gf" style="width: 150px;" name="display_unit" x-model="hawb.display_unit"><option value="BOTH">Show Both</option><option value="KG_CBM">Show KG / CBM</option><option value="LB_CFT">Show LB / CFT</option></select>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div style="margin-bottom: 10px; display: none;">
                                <h4 style="font-size: 11px; font-weight: 600; color: #333; margin: 0 0 5px 0;">Customer Reference / P.O. No. <span style="font-weight: normal; font-size: 10px; color: #777;">Please list down P.O. No. for this HAWB</span></h4>
                                <div style="border: 1px solid #ccc; padding: 5px; min-height: 30px; background: #fff;">
                                    <input type="text" placeholder="Add P.O. here..." style="border: none; outline: none; font-size: 11px; width: 100%;">
                                </div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                <span style="font-size: 11px; font-weight: 600; color: #444;">Commodity</span>
                                <div style="display: flex; gap: 5px;">
                                    <button type="button" class="btn-tool-icon" style="background:#26c281; color:#fff; border-color:#26c281; padding:0 6px;" @click="hawb.commodities.push({description:'', po_no:'', selected:false})"><i class="fa fa-plus"></i></button>
                                    <button type="button" class="btn-tool-icon" :disabled="hawb.commodities.filter(c=>c.selected).length === 0" :style="hawb.commodities.filter(c=>c.selected).length === 0 ? 'opacity:0.4; cursor:not-allowed;' : 'color:red; border-color:red;'" style="padding:0 6px;" @click="hawb.commodities = hawb.commodities.filter(c=>!c.selected)"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:10px;">
                                <thead>
                                    <tr style="background:#f9f9f9;">
                                        <th style="width: 30px; text-align: center; border:1px solid #eee;"><input type="checkbox" @change="hawb.commodities.forEach(c => c.selected = $event.target.checked)"></th>
                                        <th style="border:1px solid #eee; padding:5px; text-align:left;"><span style="color:red;">*</span>Commodity Description</th>
                                        <th style="border:1px solid #eee; padding:5px; text-align:left;">P.O. No.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(com, ci) in hawb.commodities" :key="ci">
                                        <tr :style="com.selected ? 'background:#fef9e7;' : ''">
                                            <td style="text-align: center; border:1px solid #eee;"><input type="checkbox" x-model="com.selected"></td>
                                            <td style="border:1px solid #eee; padding:2px;"><textarea class="form-control-gf" x-model="com.description" style="border:none; min-height: 60px !important; height: 60px !important; width:100%; resize:vertical;" placeholder="Commodity description..."></textarea></td>
                                            <td style="border:1px solid #eee; padding:2px;"><input type="text" class="form-control-gf" x-model="com.po_no" style="border:none;"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="hawb.commodities.length === 0"><td colspan="3" style="text-align: center; color: #999; padding: 10px; border:1px solid #eee;">No Data Available. Click <strong style="color:#4b77be; cursor:pointer;" @click="hawb.commodities.push({description:'',po_no:'',selected:false})">here</strong> to add a new row.</td></tr>
                                </tbody>
                            </table>

                            <!-- Warehouse Receipt List (Hidden per request) -->
                            <div style="display: none !important;">
                                <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <span style="font-size: 11px; font-weight: 600; color: #444;">Warehouse Receipt List</span>
                                    <div style="display: flex; gap: 5px;">
                                        <select class="form-control-gf" style="width: 150px; height: 20px;"><option>Warehouse Receipt</option></select>
                                        <button type="button" class="btn-tool-icon" disabled style="padding:0 6px;"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                                <div style="overflow-x:auto;">
                                    <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:10px; min-width:800px;">
                                        <thead>
                                            <tr style="background:#f9f9f9;">
                                                <th style="width: 30px; text-align: center; border:1px solid #eee;"><input type="checkbox"></th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Receipt No.</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Length</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Width</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Height</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Dimension</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">TOTAL PCS</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Available PCS</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Allocated PCS</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Unit</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Gross Weight</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Volume</th>
                                                <th style="border:1px solid #eee; padding:5px; text-align:left;">Measurement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="13" style="padding: 10px; border:1px solid #eee;">
                                                    <button type="button" class="btn-tool" style="background:#32c5d2; color:#fff; border:none; padding:4px 10px; margin-bottom: 5px; font-size:10px;" @click="showWrModal = true; searchWarehouseList()"><i class="fa fa-external-link-square"></i> Load from Warehouse</button>
                                                    <div style="display: flex; gap: 15px; font-size: 10px; align-items: center;">
                                                        <label style="display:none; align-items:center; gap:5px;"><input type="checkbox" checked> Auto-sync package, weight and measurements</label>
                                                        <label style="display:flex; align-items:center; gap:5px;"><input type="checkbox"> Auto-sync dimensions</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div class="form-grid-4" style="grid-template-columns: repeat(2, 1fr);">
                                <div class="flex flex-col">
                                    <h4 style="font-size: 11px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Mark</h4>
                                    <textarea class="form-control-gf" style="height: 60px !important; resize: vertical; padding:5px;"></textarea>
                                </div>
                                <div class="flex flex-col">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                        <h4 style="font-size: 11px; font-weight: 600; margin: 0; color: #333;">Description</h4>
                                        <div style="display: flex; gap: 3px; align-items: center;">
                                            <span style="font-size: 9px; color: #555;">Copy:</span>
                                            <button type="button" class="btn-default-gf" style="font-size: 9px; padding: 0 4px; height: 18px;">P.O.</button>
                                            <button type="button" class="btn-default-gf" style="font-size: 9px; padding: 0 4px; height: 18px;">Commodity</button>
                                            <button type="button" class="btn-default-gf" style="font-size: 9px; padding: 0 4px; height: 18px;">Commodity & HTS</button>
                                        </div>
                                    </div>
                                    <textarea class="form-control-gf" style="height: 60px !important; resize: vertical; padding:5px;"></textarea>
                                </div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div>
                                <h4 style="font-size: 11px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Remark</h4>
                                <textarea class="form-control-gf" style="height: 50px !important; resize: vertical; padding:5px;"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end" style="margin-top: 5px; margin-bottom: 20px;">
                    <button type="button" @click="addHbl" class="btn-freightx" style="background:#f2bc00; padding: 4px 15px; font-size: 11px; border-radius: 2px;"><i class="fa fa-plus"></i> ADD HAWB</button>
                </div>
            </div>

            <!-- Include Generate Invoice Popup Modal -->
            @include('partials.charges-invoice-modal')

            <!-- CHARGES TAB -->
            <div x-show="activeTab === 'charges'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-money"></i> Charge Manifestation</span>
                    </div>
                    <div class="portlet-body">
                        <script>
                            if (!window.tradePartnersList) {
                                window.tradePartnersList = {!! json_encode(collect($allAgents ?? ($agents ?? []))->map(function($tp) { return ['id' => $tp->id, 'name' => $tp->name, 'label' => $tp->name . ' (' . ($tp->type ?? 'Partner') . ')']; })->toArray()) !!};
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

            <!-- HISTORY TAB -->
            <div x-show="activeTab === 'history'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-history"></i> History</span>
                    </div>
                    <div class="portlet-body">
                        <div style="margin-bottom: 12px; font-weight: bold; font-size: 11px; color: #4b77be; text-transform: uppercase;">
                            <i class="fa fa-list-alt"></i> Shipment Status Logs
                        </div>

                        <!-- Add Status Form -->
                        @if(isset($airImport))
                        <div style="margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                            <div style="display: flex; gap: 10px; align-items: end;">
                                <div style="flex: 1;">
                                    <label style="display: block; font-size: 11px; font-weight: 600; color: #555; margin-bottom: 5px;">Status Message</label>
                                    <input type="text" x-model="newStatusMessage" class="form-control-gf" placeholder="Enter status update message...">
                                </div>
                                <button type="button" @click="addStatusLog()" class="btn-freightx" style="padding: 6px 20px; height: 30px;">
                                    <i class="fa fa-plus"></i> Add Status
                                </button>
                            </div>
                        </div>
                        @endif

                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Date</th>
                                    <th style="width: 150px;">User</th>
                                    <th style="width: 150px;">Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($airImport) && $airImport->statusLogs && $airImport->statusLogs->count() > 0)
                                    @foreach($airImport->statusLogs->sortByDesc('created_at') as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $log->user->name ?? 'System' }}</td>
                                        <td><span style="background: #4b77be; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 600;">{{ $log->status_name ?? 'UPDATE' }}</span></td>
                                        <td>{{ $log->details ?? $log->status_message ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ date('Y-m-d H:i') }}</td>
                                        <td>{{ auth()->user()->name ?? 'System' }}</td>
                                        <td><span style="background: #26c281; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 600;">CREATED</span></td>
                                        <td>Shipment record initialized.</td>
                                    </tr>
                                @endif
                                <template x-for="(status, idx) in form.history" :key="idx">
                                    <tr>
                                        <td x-text="status.date"></td>
                                        <td x-text="status.user"></td>
                                        <td><span style="background: #4b77be; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 600;" x-text="status.status"></span></td>
                                        <td x-text="status.details"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- FILING TAB -->
            <div x-show="activeTab === 'filing'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-file-text-o"></i> Filing Details</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Shipper</label><div class="form-input-container"><select name="shipper_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ (isset($airImport) && $airImport->shipper_id == $agent->id) ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><select name="consignee_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ (isset($airImport) && $airImport->consignee_id == $agent->id) ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><select name="notify_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ (isset($airImport) && $airImport->notify_id == $agent->id) ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AMS No.</label><div class="form-input-container"><input type="text" name="ams_no" class="form-control-gf" value="{{ $airImport->ams_no ?? '' }}"></div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">Bill To</label><div class="form-input-container"><select name="bill_to_id" class="form-control-gf"><option value="">Select...</option>@foreach($allAgents as $agent)<option value="{{ $agent->id }}" {{ (isset($airImport) && $airImport->bill_to_id == $agent->id) ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Sub B/L No.</label><div class="form-input-container"><input type="text" name="sub_bl_no" class="form-control-gf" value="{{ $airImport->sub_bl_no ?? '' }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><input type="text" class="form-control-gf" value="{{ isset($airImport) ? ($airImport->operator->name ?? auth()->user()->name) : auth()->user()->name }}" disabled style="background:#f5f5f5;"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">ISF No.</label><div class="form-input-container"><input type="text" name="isf_no" class="form-control-gf" value="{{ $airImport->isf_no ?? '' }}"></div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><select name="oversea_agent_id" class="form-control-gf"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" {{ (isset($airImport) && $airImport->oversea_agent_id == $agent->id) ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">CY/CFS Loc.</label><div class="form-input-container"><input type="text" name="cy_cfs_loc" class="form-control-gf" value="{{ $airImport->cy_cfs_loc ?? '' }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Available</label><div class="form-input-container"><input type="date" name="last_free_day" class="form-control-gf" value="{{ isset($airImport) && $airImport->last_free_day ? $airImport->last_free_day->format('Y-m-d') : '' }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">ISF Matched</label><div class="form-input-container"><input type="date" name="isf_matched_date" class="form-control-gf" value="{{ isset($airImport) && $airImport->isf_matched_date ? $airImport->isf_matched_date->format('Y-m-d') : '' }}"></div></div>

                            <!-- Row 4 -->
                            <div class="form-group-gf"><label class="form-label-gf">Trucker</label><div class="form-input-container"><select name="trucker_id" class="form-control-gf"><option value="">Select...</option>@foreach($truckers as $trucker)<option value="{{ $trucker->id }}" {{ (isset($airImport) && $airImport->trucker_id == $trucker->id) ? 'selected' : '' }}>{{ $trucker->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Final Dest.</label><div class="form-input-container"><select name="final_destination_id" class="form-control-gf"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ (isset($airImport) && $airImport->final_destination_id == $port->id) ? 'selected' : '' }}>{{ $port->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Final ETA</label><div class="form-input-container"><input type="date" name="final_eta" class="form-control-gf" value="{{ isset($airImport) && $airImport->final_eta ? $airImport->final_eta->format('Y-m-d') : '' }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">ISF 3rd Party</label><div class="form-input-container" style="justify-content: flex-start;"><input type="checkbox" name="isf_3rd_party" value="1" {{ (isset($airImport) && $airImport->isf_3rd_party) ? 'checked' : '' }}></div></div>

                            <!-- Row 5 -->
                            <div class="form-group-gf"><label class="form-label-gf">P.O.D ETA</label><div class="form-input-container"><input type="date" name="pod_eta" class="form-control-gf" value="{{ isset($airImport) && $airImport->pod_eta ? $airImport->pod_eta->format('Y-m-d') : '' }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Freight</label><div class="form-input-container"><select name="freight_term" class="form-control-gf"><option value="">Select...</option><option value="PREPAID" {{ (isset($airImport) && $airImport->freight_term == 'PREPAID') ? 'selected' : '' }}>PREPAID</option><option value="COLLECT" {{ (isset($airImport) && $airImport->freight_term == 'COLLECT') ? 'selected' : '' }}>COLLECT</option></select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">LFD</label><div class="form-input-container"><input type="date" name="last_free_day" class="form-control-gf" value="{{ isset($airImport) && $airImport->last_free_day ? $airImport->last_free_day->format('Y-m-d') : '' }}"></div></div>
                            <div></div>

                            <!-- Row 6 -->
                            <div class="form-group-gf"><label class="form-label-gf">Ship Mode</label><div class="form-input-container"><select name="ship_mode" class="form-control-gf"><option value="AIR" {{ (isset($airImport) && $airImport->ship_mode == 'AIR') ? 'selected' : 'selected' }}>AIR</option><option value="EXPRESS" {{ (isset($airImport) && $airImport->ship_mode == 'EXPRESS') ? 'selected' : '' }}>EXPRESS</option></select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Expiry Date</label><div class="form-input-container"><input type="date" name="expiry_date" class="form-control-gf" value="{{ isset($airImport) && $airImport->expiry_date ? $airImport->expiry_date->format('Y-m-d') : '' }}"></div></div>
                            <div></div>
                            <div></div>

                            <!-- Row 7 -->
                            <div class="form-group-gf"><label class="form-label-gf">G.O Date</label><div class="form-input-container"><input type="date" name="go_date" class="form-control-gf" value="{{ isset($airImport) && $airImport->go_date ? $airImport->go_date->format('Y-m-d') : '' }}"></div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Sales Type</label><div class="form-input-container"><select name="sales_type" class="form-control-gf"><option value="">Select...</option><option value="NOMINATED" {{ (isset($airImport) && $airImport->sales_type == 'NOMINATED') ? 'selected' : '' }}>NOMINATED</option><option value="FREE HAND" {{ (isset($airImport) && $airImport->sales_type == 'FREE HAND') ? 'selected' : '' }}>FREE HAND</option></select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><select name="incoterm_id" class="form-control-gf"><option value="">Select...</option>@foreach($incoterms as $inco)<option value="{{ $inco->id }}" {{ (isset($airImport) && $airImport->incoterm_id == $inco->id) ? 'selected' : '' }}>{{ $inco->code }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container"><select name="cargo_type" class="form-control-gf"><option value="">Select...</option><option value="GENERAL CARGO" {{ (isset($airImport) && $airImport->cargo_type == 'GENERAL CARGO') ? 'selected' : '' }}>GENERAL CARGO</option><option value="DANGEROUS GOODS" {{ (isset($airImport) && $airImport->cargo_type == 'DANGEROUS GOODS') ? 'selected' : '' }}>DANGEROUS GOODS</option></select></div></div>
                            <div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">C. Released</label><div class="form-input-container"><input type="date" name="c_released_date" class="form-control-gf" value="{{ isset($airImport) && $airImport->c_released_date ? $airImport->c_released_date->format('Y-m-d') : '' }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><select name="service_term_from" class="form-control-gf" style="width:45%;"><option value="">Select...</option>@foreach($serviceTerms as $term)<option value="{{ $term->code }}" {{ (isset($airImport) && $airImport->service_term_from == $term->code) ? 'selected' : '' }}>{{ $term->code }}</option>@endforeach</select>~<select name="service_term_to" class="form-control-gf" style="width:45%;"><option value="">Select...</option>@foreach($serviceTerms as $term)<option value="{{ $term->code }}" {{ (isset($airImport) && $airImport->service_term_to == $term->code) ? 'selected' : '' }}>{{ $term->code }}</option>@endforeach</select></div></div>
                            <div></div>
                            <div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf"><label class="form-label-gf">Entry No.</label><div class="form-input-container"><input type="text" name="entry_no" class="form-control-gf" value="{{ $airImport->entry_no ?? '' }}"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Entry DOC Sent</label><div class="form-input-container"><input type="date" name="entry_doc_sent_date" class="form-control-gf" value="{{ isset($airImport) && $airImport->entry_doc_sent_date ? $airImport->entry_doc_sent_date->format('Y-m-d') : '' }}"></div></div>
                            <div></div>
                            <div></div>

                            <!-- Row 4 -->
                            <div class="form-group-gf"><label class="form-label-gf">ROR</label><div class="form-input-container"><input type="checkbox" name="ror" value="1" {{ (isset($airImport) && $airImport->ror) ? 'checked' : '' }}></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Hold</label><div class="form-input-container"><input type="checkbox" name="hold" value="1" {{ (isset($airImport) && $airImport->hold) ? 'checked' : '' }}></div></div>
                            <div></div>
                            <div></div>

                            <!-- Row 5 -->
                            <div class="form-group-gf"><label class="form-label-gf">Released By</label><div class="form-input-container"><select name="released_by_id" class="form-control-gf"><option value="">Select...</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ (isset($airImport) && $airImport->released_by_id == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach</select></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Door Deliv.</label><div class="form-input-container"><input type="date" name="door_delivered_date" class="form-control-gf" value="{{ isset($airImport) && $airImport->door_delivered_date ? $airImport->door_delivered_date->format('Y-m-d') : '' }}"></div></div>
                            <div></div>
                            <div></div>

                            <!-- Row 6 -->
                            <div class="form-group-gf"><label class="form-label-gf">DO Sent</label><div class="form-input-container"><input type="checkbox" name="do_sent" value="1" {{ (isset($airImport) && $airImport->do_sent) ? 'checked' : '' }}> <input type="date" name="do_sent_date" class="form-control-gf" value="{{ isset($airImport) && $airImport->do_sent_date ? $airImport->do_sent_date->format('Y-m-d') : '' }}"></div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div>
            </div> <!-- End of filing tab -->
        @include('partials.ai-modal')

        </form>

        <!-- Dimensions Modal -->
    <!-- Volume & Gross Weight Calculator Modal -->
    <div x-show="showDimensionsModal" 
         style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999;"
         x-cloak>
         <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;" @click.self="closeDimensionsModal()">
        <div style="background: white; border-radius: 4px; width: 750px; max-width: 95%; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); font-size: 11px;">
            <!-- Modal Header -->
            <div style="padding: 12px 18px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 15px; font-weight: 500; color: #555;">
                    Volume & Gross Weight Calculator
                </h3>
                <button type="button" @click="closeDimensionsModal()" style="background: none; border: none; font-size: 18px; color: #ccc; cursor: pointer; padding: 0;">
                    <i class="fa fa-times"></i>
                </button>
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
                                    <td style="text-align: right; padding: 4px;" x-text="calcRowVolKg(row).toFixed(2)"></td>
                                    <td style="text-align: right; padding: 4px;" x-text="calcRowVolLb(row).toFixed(2)"></td>
                                    
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
                                <td style="text-align: right; padding: 6px;" x-text="totalDimVolKg.toFixed(2)"></td>
                                <td style="text-align: right; padding: 6px;" x-text="totalDimVolLb.toFixed(2)"></td>
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
    <!-- Load Quotation Data Modal -->
        <template x-teleport="body">
    <!-- QUOTE MODAL (Ocean Export UI Style for Air Import) -->
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
                    <!-- Ocean Style Grid -->
                    <div class="form-grid-4" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="main-grid">
                            <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container">
                                <select class="form-control-gf" x-model="filters.customer" @change="applySearch()">
                                    <option value="">Select...</option>
                                    @foreach($allAgents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Departure</label><div class="form-input-container">
                                <select class="form-control-gf" x-model="filters.pol" @change="applySearch()">
                                    <option value="">Select...</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->id }}">{{ $port->name }}</option>
                                    @endforeach
                                </select>
                            </div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Quote No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="filters.quote_no" @keyup="applySearch()" placeholder="Search..."></div></div>
                        </div>
                        
                        <div class="main-grid">
                            <div class="form-group-gf"><label class="form-label-gf">Valid Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="filters.valid_date" @change="applySearch()"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Destination</label><div class="form-input-container">
                                <select class="form-control-gf" x-model="filters.pod" @change="applySearch()">
                                    <option value="">Select...</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->id }}">{{ $port->name }}</option>
                                    @endforeach
                                </select>
                            </div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Status</label><div class="form-input-container">
                                <select class="form-control-gf" x-model="filters.status" @change="applySearch()">
                                    <option value="">Select...</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Sent">Sent</option>
                                    <option value="Won">Won</option>
                                    <option value="Lost">Lost</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Cancelled">Cancelled</option>
                                    <option value="Ghosted">Ghosted</option>
                                </select>
                            </div></div>
                        </div>

                        <div class="main-grid">
                            <div class="form-group-gf"><label class="form-label-gf">Commodity</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="filters.commodity" @keyup="applySearch()" placeholder="Search..."></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container">
                                <select class="form-control-gf" x-model="filters.sales" @change="applySearch()">
                                    <option value="">Select...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div></div>
                            <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container">
                                <select class="form-control-gf" x-model="filters.op" @change="applySearch()">
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
                    
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 5px;">
                        <button type="button"
        @click="showQuoteConfig = !showQuoteConfig"
        class="btn-tool-secondary">
    <i class="fa fa-cogs"></i> Config
</button>
                    </div>
                    
                    <!-- Quote Table Config Modal -->
                    <div x-show="showQuoteConfig" x-cloak @click.self="showQuoteConfig = false" style="inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center;">
                        <div @click.stop style="width: 100%; background: #fff; overflow: hidden;">

                            <div style="padding:12px 14px;">
                                <div style="display:flex; flex-wrap: wrap; gap:7px 12px;">
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.quoteNo"> Quote No.</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.validDate"> Valid Date</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.status"> Status</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.creationDate"> Creation Date</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.commodity"> Commodity</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.departure"> Departure</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.destination"> Destination</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.carrier"> Carrier</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.sales"> Sales</label>
                                    <label style="font-size:11px; cursor:pointer;"><input type="checkbox" x-model="quoteConfig.op"> OP</label>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                    <!-- Ocean Style Interactive Table -->
                    <div class="table-responsive" style="margin-bottom: 10px; height: 280px; border: 1px solid #e7ecf1;">
                        <table class="table-custom">
                            <thead>
                                <tr style="background: #888; color: #fff;">
                                    <th style="text-align: center;">Select</th>
                                    <th x-show="quoteConfig.quoteNo">Quote No.</th>
                                    <th x-show="quoteConfig.validDate">Valid Date <i class="fa fa-sort" style="float: right;"></i></th>
                                    <th x-show="quoteConfig.status">Status <i class="fa fa-sort" style="float: right;"></i></th>
                                    <th x-show="quoteConfig.creationDate">Creation Date <i class="fa fa-sort" style="float: right;"></i></th>
                                    <th x-show="quoteConfig.commodity">Commodity</th>
                                    <th x-show="quoteConfig.departure">Departure</th>
                                    <th x-show="quoteConfig.destination">Destination</th>
                                    <th x-show="quoteConfig.carrier">Carrier</th>
                                    <th x-show="quoteConfig.sales">Sales</th>
                                    <th x-show="quoteConfig.op">OP</th>
                                </tr>
                            </thead>
                            <tbody>
                                 @foreach($quotations as $quote)
                                 <tr class="cursor-pointer hover:bg-blue-50"
                                     :style="selectedQuote && selectedQuote.quote_no === '{{ addslashes($quote->quote_no) }}' ? 'background-color: #eff6ff;' : ''"
                                     x-show="matchFilters({quote_no: '{{ addslashes($quote->quote_no) }}', customer_id: '{{ $quote->customer_id }}', pol_id: '{{ $quote->pol_id }}', pod_id: '{{ $quote->pod_id }}', status: '{{ $quote->status }}', sales_person_id: '{{ $quote->sales_person_id }}', op: '{{ $quote->op_id }}', commodity: '{{ addslashes($quote->commodity ?? '') }}'})">
                                     <td x-show="quoteConfig.quoteNo" style="text-align: center;"><input type="radio" name="quote_sel" :checked="selectedQuote && selectedQuote.quote_no === '{{ $quote->quote_no }}'" 
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
                                            ship_mode: '{{ addslashes($quote->ship_mode ?? '') }}',
                                            items: (quoteItems && quoteItems['{{ $quote->quote_no }}']) ? quoteItems['{{ $quote->quote_no }}'].map(i => ({...i, selected: true})) : []
                                        })"></td>
                                     <td><span style="color: #3b82f6; font-weight: 600;">{{ $quote->quote_no }}</span></td>
                                     <td x-show="quoteConfig.validDate">{{ $quote->quote_date ? $quote->quote_date->format('m-d-Y') : '' }} ~ {{ $quote->expiry_date ? $quote->expiry_date->format('m-d-Y') : '' }}</td>
                                     <td x-show="quoteConfig.status"><span style="background: {{ in_array(strtoupper($quote->status), ['WON', 'ACCEPTED']) ? '#10b981' : (in_array(strtoupper($quote->status), ['LOST', 'EXPIRED', 'CANCELLED']) ? '#ef4444' : '#64748b') }}; color: #fff; padding: 1px 4px; border-radius: 2px; font-size: 9px; font-weight: 600;">{{ $quote->status }}</span></td>
                                     <td x-show="quoteConfig.creationDate">{{ $quote->created_at ? $quote->created_at->format('Y-m-d') : '' }}</td>
                                     <td x-show="quoteConfig.commodity">{{ $quote->commodity ?: '-' }}</td>
                                     <td x-show="quoteConfig.departure">{{ $quote->pol->name ?? '-' }}</td>
                                     <td x-show="quoteConfig.destination">{{ $quote->pod->name ?? '-' }}</td>
                                     <td x-show="quoteConfig.carrier">{{ $quote->carrier->name ?? '-' }}</td>
                                     <td x-show="quoteConfig.sales">{{ $quote->salesPerson->name ?? '-' }}</td>
                                     <td x-show="quoteConfig.op">{{ $quote->op->name ?? '-' }}</td>
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
                    <!-- Ocean Style Form Grid for Shipment Information -->
                    <div class="form-grid-4" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="main-grid">
                            <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*MAWB No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.mawb_no"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Departure Date/Time</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="quoteForm.etd"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*Customer</label><div class="form-input-container flex">
                                <input type="text" class="form-control-gf flex-1" x-model="quoteForm.customer" readonly style="background-color: #f8fafc;">
                                <div style="display: flex; background: #fff; border-left: none;">
                                    <button type="button" class="btn-default-gf" style="border-left: 1px solid #e2e8f0; padding: 0 4px; border-radius: 0; background: #fff;"><i class="fa fa-external-link" style="color: #3b82f6; font-size: 10px;"></i></button>
                                    <button type="button" class="btn-default-gf" style="border-left: 1px solid #e2e8f0; padding: 0 4px; border-radius: 0; background: #fff;"><i class="fa fa-angle-down" style="color: #94a3b8;"></i></button>
                                </div>
                            </div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><span x-text="quoteForm.oversea_agent || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Gross Weight</label><div class="form-input-container"><span style="font-size: 10px; color: #334155;">-</span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Chargeable Weight</label><div class="form-input-container"><span style="font-size: 10px; color: #334155;">-</span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><span x-text="quoteForm.op || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                        </div>
                        <div class="main-grid">
                            <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*HAWB No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.hawb_no"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*Arrival Date/Time</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="quoteForm.eta"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><span x-text="quoteForm.service_term || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><span x-text="quoteForm.incoterms || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Volume Weight</label><div class="form-input-container"><span style="font-size: 10px; color: #334155;">-</span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container"><span x-text="quoteForm.sales || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 Content -->
                <div x-show="quoteStep === 3" x-cloak>
                    <div class="hbl-header">Select Freight Item(s)</div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <input type="checkbox" id="saveAsDraft" x-model="saveAsDraft" style="margin: 0; width: 12px; height: 12px; cursor: pointer; accent-color: #3b82f6;">
                            <label for="saveAsDraft" style="font-size: 10px; color: #475569; font-weight: 600; cursor: pointer; margin: 0;">Save as a draft invoice</label>
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px; font-size: 10px;">
                            <span style="font-weight: 600;">Applied Unit</span> <i class="fa fa-info-circle text-gray-400"></i>
                            <select class="form-control-gf" style="width: 120px; height: 22px;">
                                <option>Min</option>
                                <option>-45K</option>
                                <option>+45K</option>
                                <option>+100K</option>
                                <option>+300K</option>
                                <option>+500K</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="margin-bottom: 10px;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 60px;"><input type="checkbox" disabled> Select</th>
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
                                            <td x-text="item.unit || 'UNIT'"></td>
                                            <td x-text="item.currency ? item.currency.code : 'USD'"></td>
                                            <td style="text-align: right;"><input type="text" class="form-control-gf" x-model="item.qty" style="text-align: right; width: 60px;"></td>
                                            <td style="text-align: right;"><input type="text" class="form-control-gf" x-model="item.rate" style="text-align: right; width: 80px;"></td>
                                            <td style="text-align: right; font-weight: 600; color: #3b82f6;" x-text="(parseFloat(item.qty || 0) * parseFloat(item.rate || 0)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                                        </tr>
                                    </template>
                                </template>
                                <template x-if="!selectedQuote || !selectedQuote.items || selectedQuote.items.length === 0">
                                    <tr>
                                        <td colspan="8" style="text-align: center; color: #94a3b8; font-size: 11px; padding: 20px;">No items found for this quotation.</td>
                                    </tr>
                                </template>
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
                        :class="((quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mawb_no || !quoteForm.hawb_no || !quoteForm.customer || !quoteForm.eta))) ? 'btn-freightx opacity-50 cursor-not-allowed' : 'btn-freightx'"
                        :disabled="(quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mawb_no || !quoteForm.hawb_no || !quoteForm.customer || !quoteForm.eta))"
                        @click="quoteStep++">Next <i class="fa fa-arrow-right"></i></button>

                <button type="button" x-show="quoteStep === 3" class="btn-freightx" @click="confirmQuoteSelection()"><i class="fa fa-check"></i> Confirm</button>
            </div>
        </div>
    </div>
</template>
    </div>

    
</div>
</div>

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
</script>

</x-layout>