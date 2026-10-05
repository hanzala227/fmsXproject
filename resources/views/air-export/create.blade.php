<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        .tools-menu-item {
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
        .tools-menu-item:hover:not(:disabled) {
            background: #f3f4f6;
            color: #111827;
        }
        .tools-menu-item.disabled, .tools-menu-item:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .tools-menu-divider {
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
    </style>
    @endpush

    <script>
        window.airExportModule = function() {
            return {
                showQuoteModal: false,
                quoteStep: 1,
                selectedQuote: null,
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
                showMblSection: true,
                showMblMemo: false,
                isDirectMaster: false,
                showMore: false,
                showConnectingFlight: false,
                hawbs: [],
                
                form: {
                    id: '{{ isset($airExport) ? $airExport->id : "" }}',
                    file_no: '{{ isset($airExport) ? $airExport->file_no : "MAE-" . date("YmdHis") }}',
                    mawb_no: '{{ isset($airExport) ? $airExport->mawb_no : "" }}',
                    office: '{{ isset($airExport) ? $airExport->office_id : "" }}',
                    carrier: '{{ isset($airExport) ? $airExport->carrier_id : "" }}',
                    issuing_carrier: 'FREIGHTX',
                    awb_type: 'NORMAL',
                    awb_date: '',
                    shipper: '',
                    consignee: '',
                    notify: '',
                    post_date: '{{ isset($airExport) && $airExport->post_date ? \Carbon\Carbon::parse($airExport->post_date)->format("Y-m-d") : date("Y-m-d") }}',
                    awb_acct_carrier: '{{ isset($airExport) ? $airExport->acct_carrier_id : "" }}',
                    co_loader: '{{ isset($airExport) ? $airExport->forwarding_agent_id : "" }}',
                    actual_shipper: '',
                    op: '{{ isset($airExport) ? $airExport->op_id : auth()->id() }}',
                    itn_no: '',
                    cers_no: '',
                    reference_no: '',
                    display_unit: 'BOTH',
                    departure: '{{ isset($airExport) ? $airExport->dep_port_id : "" }}',
                    destination: '{{ isset($airExport) ? $airExport->dst_port_id : "" }}',
                    flight_no: '{{ isset($airExport) ? $airExport->flight_no : "" }}',
                    cargo_ready_date: '',
                    etd: '{{ isset($airExport) && $airExport->etd ? \Carbon\Carbon::parse($airExport->etd)->format("Y-m-d") : "" }}',
                    atd: '{{ isset($airExport) && $airExport->atd ? \Carbon\Carbon::parse($airExport->atd)->format("Y-m-d") : "" }}',
                    eta: '{{ isset($airExport) && $airExport->eta ? \Carbon\Carbon::parse($airExport->eta)->format("Y-m-d") : "" }}',
                    ata: '{{ isset($airExport) && $airExport->ata ? \Carbon\Carbon::parse($airExport->ata)->format("Y-m-d") : "" }}',
                    dv_carriage: 'N.V.D.',
                    dv_customs: 'N.C.V.',
                    insurance: 'N.I.L.',
                    wt_val: 'P',
                    other_term: 'P',
                    pkg_qty: '{{ isset($airExport) ? $airExport->pkg_qty : "" }}',
                    pkg_unit_id: '{{ isset($airExport) ? $airExport->pkg_unit_id : "" }}',
                    gross_weight: '{{ isset($airExport) ? $airExport->gross_weight : "" }}',
                    chargeable_weight: '{{ isset($airExport) ? $airExport->chargeable_weight : "" }}',
                    volume: '{{ isset($airExport) ? $airExport->volume : "" }}',
                    buying_rate: '{{ isset($airExport) ? $airExport->buying_rate : "" }}',
                    selling_rate: '{{ isset($airExport) ? $airExport->selling_rate : "" }}',
                    sales_type: '{{ isset($airExport) ? $airExport->sales_type : "" }}',
                    internal_remark: '{{ isset($airExport) ? $airExport->internal_remark : "" }}',
                    routes: @json(isset($airExport) && !empty($airExport->routes) ? (is_array($airExport->routes) ? $airExport->routes : json_decode($airExport->routes, true)) : []),
                    other_charges: [],
                    charges: @json($chargesData ?? []),
                    accounting_info: [],
                    commodities: [],
                    memo: ''
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
                calculateLocalAmount(charge) {
                    let rate = parseFloat(charge.rate) || 0;
                    let qty = parseFloat(charge.qty) || 0;
                    let roe = parseFloat(charge.roe) || 1.0;
                    let vat = parseFloat(charge.vat) || 0;
                    let foreignAmount = rate * qty;
                    let localAmount = foreignAmount * roe;
                    if (vat > 0) {
                        localAmount += (localAmount * (vat / 100));
                    }
                    return localAmount;
                },
                calculateTotalCharges() {
                    if (!this.form.charges || this.form.charges.length === 0) return 0;
                    return this.form.charges.reduce((sum, charge) => {
                        return sum + this.calculateLocalAmount(charge);
                    }, 0);
                },
                calculateArCharges() {
                    if (!this.form.charges || this.form.charges.length === 0) return 0;
                    return this.form.charges.filter(c => c.pr === 'Rec').reduce((sum, charge) => {
                        return sum + this.calculateLocalAmount(charge);
                    }, 0);
                },
                calculateApCharges() {
                    if (!this.form.charges || this.form.charges.length === 0) return 0;
                    return this.form.charges.filter(c => c.pr === 'Pay').reduce((sum, charge) => {
                        return sum + this.calculateLocalAmount(charge);
                    }, 0);
                },
                updateChargeAmount(idx) {
                    this.$forceUpdate && this.$forceUpdate();
                },
                updateLocalAmount(idx) {
                    this.$forceUpdate && this.$forceUpdate();
                },
                getChargeFilters() {
                    let list = [
                        { name: 'All', value: 'All' },
                        { name: 'Revenue (A/R)', value: 'AR' },
                        { name: 'Cost (A/P)', value: 'AP' }
                    ];
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
                get filteredCharges() {
                    let list = this.form.charges || [];
                    if (this.activeChargeFilter === 'AR') {
                        list = list.filter(c => c.pr === 'Rec');
                    } else if (this.activeChargeFilter === 'AP') {
                        list = list.filter(c => c.pr === 'Pay');
                    } else if (this.activeChargeFilter === 'DC') {
                        list = list.filter(c => c.pr === 'DC');
                    }
                    if (this.manifestFilters) {
                        if (this.manifestFilters.party && this.manifestFilters.party !== 'All') {
                            list = list.filter(c => c.party === this.manifestFilters.party);
                        }
                        if (this.manifestFilters.sal && this.manifestFilters.sal !== 'All') {
                            list = list.filter(c => c.sal === this.manifestFilters.sal);
                        }
                        if (this.manifestFilters.pr && this.manifestFilters.pr !== 'All') {
                            list = list.filter(c => c.pr === this.manifestFilters.pr);
                        }
                        if (this.manifestFilters.ppc && this.manifestFilters.ppc !== 'All') {
                            list = list.filter(c => c.ppc === this.manifestFilters.ppc);
                        }
                        if (this.manifestFilters.currency && this.manifestFilters.currency !== 'All') {
                            list = list.filter(c => c.currency === this.manifestFilters.currency);
                        }
                        if (this.manifestFilters.invoiced && this.manifestFilters.invoiced !== 'All') {
                            if (this.manifestFilters.invoiced === 'Invoiced') {
                                list = list.filter(c => c.inv_no && c.inv_no.trim() !== '');
                            } else if (this.manifestFilters.invoiced === 'Uninvoiced') {
                                list = list.filter(c => !c.inv_no || c.inv_no.trim() === '');
                            }
                        }
                    }
                    return list;
                },
                shouldShowChargeRow(charge) {
                    if (this.activeChargeFilter === 'All') return true;
                    if (this.activeChargeFilter === 'AR') return charge.pr === 'Rec';
                    if (this.activeChargeFilter === 'AP') return charge.pr === 'Pay';
                    let filterVal = this.activeChargeFilter.trim().toLowerCase();
                    let mbl = (charge.mbl_no || '').trim().toLowerCase();
                    let eqBl = (charge.eq_bl_no || '').trim().toLowerCase();
                    return mbl === filterVal || eqBl === filterVal;
                },
                addNewCharge() {
                    this.addCharge();
                },
                addCharge() {
                    if (!this.form.charges) this.form.charges = [];
                    let initialPr = 'Rec';
                    if (this.activeChargeFilter === 'AP') {
                        initialPr = 'Pay';
                    } else if (this.activeChargeFilter === 'DC') {
                        initialPr = 'DC';
                    }
                    this.form.charges.push({
                        id: null,
                        selected: false,
                        expanded: false,
                        party: 'Custom',
                        party_name_id: '',
                        sal: 'Air',
                        pr: initialPr,
                        ppc: 'Colle',
                        chrg_code: '',
                        charge_name: '',
                        currency: 'USD',
                        rate: 0,
                        qty: 1,
                        qty_type: 'B/L',
                        roe: 1.0,
                        vat: 0,
                        inv_no: '',
                        financial_date: new Date().toISOString().split('T')[0],
                        eq_bl_no: '',
                        remark: false,
                        mbl_no: this.form.file_no || ''
                    });
                    const tabLabel = initialPr === 'Pay' ? 'A/P' : (initialPr === 'DC' ? 'D/C' : 'A/R');
                    showToast('success', `New ${tabLabel} charge row added.`);
                },
                deleteCharge(chargeOrIdx) {
                    if (!confirm('Delete this charge row?')) return;
                    if (typeof chargeOrIdx === 'object' && chargeOrIdx !== null) {
                        const index = (this.form.charges || []).indexOf(chargeOrIdx);
                        if (index > -1) {
                            this.form.charges.splice(index, 1);
                            showToast('success', 'Charge row removed.');
                        }
                    } else if (typeof chargeOrIdx === 'number') {
                        this.form.charges.splice(chargeOrIdx, 1);
                        showToast('success', 'Charge row removed.');
                    }
                },
                deleteSelectedCharges() {
                    const selected = (this.form.charges || []).filter(c => c.selected);
                    if (selected.length === 0) {
                        showToast('error', 'Please select charges using checkbox.');
                        return;
                    }
                    if (confirm(`Are you sure you want to delete ${selected.length} selected charge(s)?`)) {
                        this.form.charges = this.form.charges.filter(c => !c.selected);
                        showToast('success', `${selected.length} charge(s) removed.`);
                    }
                },
                deleteAllCharges() {
                    if (!this.form.charges || this.form.charges.length === 0) {
                        showToast('error', 'No charges to delete.');
                        return;
                    }
                    if (confirm('Are you sure you want to delete ALL charges?')) {
                        this.form.charges = [];
                        showToast('success', 'All charges cleared.');
                    }
                },
                duplicateSelectedCharges() {
                    const selected = (this.form.charges || []).filter(c => c.selected);
                    if (selected.length === 0) {
                        showToast('error', 'Please select at least one charge row to duplicate.');
                        return;
                    }
                    selected.forEach(c => {
                        const copy = JSON.parse(JSON.stringify(c));
                        copy.id = null;
                        copy.selected = false;
                        copy.inv_no = '';
                        this.form.charges.push(copy);
                    });
                    showToast('success', `Duplicated ${selected.length} charge(s).`);
                },
                applyChargeTemplate() {
                    const templates = [
                        { party: 'Custom', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'AIR-FRT', charge_name: 'Air Freight Charge', currency: 'USD', rate: 250.00, qty: 1, qty_type: 'B/L', roe: 1.0, vat: 0 },
                        { party: 'Custom', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'TERM-FEE', charge_name: 'Terminal Handling Fee', currency: 'USD', rate: 75.00, qty: 1, qty_type: 'B/L', roe: 1.0, vat: 0 },
                        { party: 'Custom', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'DOC-FEE', charge_name: 'Documentation Fee', currency: 'USD', rate: 50.00, qty: 1, qty_type: 'B/L', roe: 1.0, vat: 0 }
                    ];
                    if (!this.form.charges) this.form.charges = [];
                    templates.forEach(tpl => {
                        this.form.charges.push({
                            id: null,
                            selected: false,
                            expanded: false,
                            party: tpl.party,
                            party_name_id: '',
                            sal: tpl.sal,
                            pr: tpl.pr,
                            ppc: tpl.ppc,
                            chrg_code: tpl.chrg_code,
                            charge_name: tpl.charge_name,
                            currency: tpl.currency,
                            rate: tpl.rate,
                            qty: tpl.qty,
                            qty_type: tpl.qty_type,
                            roe: tpl.roe,
                            vat: tpl.vat,
                            inv_no: '',
                            financial_date: new Date().toISOString().split('T')[0],
                            eq_bl_no: '',
                            remark: false,
                            mbl_no: this.form.file_no || ''
                        });
                    });
                    showToast('success', 'Applied standard Air Export Charge Template (3 charges added).');
                },
                toggleAllCharges(e) {
                    const checked = e.target.checked;
                    (this.form.charges || []).forEach(c => c.selected = checked);
                },
                saveCharges() {
                    const formEl = document.getElementById('airExportForm') || document.querySelector('form[action*="air-export"]');
                    if (formEl) {
                        showToast('info', 'Saving charges...');
                        formEl.submit();
                    } else {
                        showToast('error', 'Form element not found.');
                    }
                },
                openCertificateModal() {
                    showToast('info', 'Certificate feature for Air Export Charges.');
                },
                setDefaultCharges() {
                    this.form.charges = [
                        { id: null, selected: false, party: 'Custom', party_name_id: '', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'AIR-FRT', charge_name: 'Air Freight Charge', currency: 'USD', rate: 250, qty: 1, qty_type: 'B/L', roe: 1.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: this.form.file_no || '' },
                        { id: null, selected: false, party: 'Custom', party_name_id: '', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'TERM-FEE', charge_name: 'Terminal Handling Fee', currency: 'USD', rate: 75, qty: 1, qty_type: 'B/L', roe: 1.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: this.form.file_no || '' },
                        { id: null, selected: false, party: 'Custom', party_name_id: '', sal: 'Air', pr: 'Rec', ppc: 'Colle', chrg_code: 'DOC-FEE', charge_name: 'Documentation Fee', currency: 'USD', rate: 50, qty: 1, qty_type: 'B/L', roe: 1.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: this.form.file_no || '' }
                    ];
                    showToast('success', 'Default Air Export charges loaded.');
                },
                reloadCharges() {
                    if (confirm('Discard unsaved changes and reload charges from database?')) {
                        window.location.reload();
                    }
                },
                createInvoice() {
                    if (!this.form.id) {
                        showToast('error', 'Please save shipment first before generating invoice.');
                        return;
                    }
                    if (!confirm('Generate Freight Invoice for this Air Export shipment?')) return;
                    fetch(`/air-export/${this.form.id}/charges/invoice`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ charges: this.form.charges || [] })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', data.message || 'Invoice generated successfully.');
                            if (data.freight_invoice_url) {
                                window.open(data.freight_invoice_url, '_blank');
                            }
                            setTimeout(() => { window.location.reload(); }, 800);
                        } else {
                            showToast('error', 'Failed to create invoice: ' + data.message);
                        }
                    })
                    .catch(err => {
                        showToast('error', 'Error generating invoice.');
                    });
                },
                generateFreightInvoice() {
                    if (!this.form.id) {
                        showToast('error', 'Please save shipment first before generating invoice.');
                        return;
                    }
                    window.open(`/shipments/air-export/${this.form.id}/freight-invoice`, '_blank');
                },
                prorataCharges() {
                    let chargeCode = prompt('Enter Charge Code to prorate across HAWBs:');
                    if (!chargeCode) return;
                    showToast('info', 'Prorating charge ' + chargeCode + ' across HAWBs...');
                },
                exportChargesToExcel() {
                    if (this.form.id) {
                        window.location.href = `/air-export/${this.form.id}/charges/export`;
                    } else {
                        let csv = "Party,Party Name,SAL,P/R,PP/C,Chrg Code,Charge Name,Currency,Rate,Qty,ROE,VAT %,Amount,Inv No\n";
                        (this.form.charges || []).forEach(c => {
                            const amt = (parseFloat(c.rate) || 0) * (parseFloat(c.qty) || 0) * (parseFloat(c.roe) || 1);
                            csv += `"${c.party || ''}","${c.party_name_id || ''}","${c.sal || ''}","${c.pr || ''}","${c.ppc || ''}","${c.chrg_code || ''}","${c.charge_name || ''}","${c.currency || 'USD'}",${c.rate || 0},${c.qty || 1},${c.roe || 1},${c.vat || 0},${amt.toFixed(2)},"${c.inv_no || ''}"\n`;
                        });
                        const blob = new Blob([csv], { type: 'text/csv' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.setAttribute('href', url);
                        a.setAttribute('download', `air_export_charges_${new Date().toISOString().split('T')[0]}.csv`);
                        a.click();
                    }
                },
                printCharges() {
                    if (this.form.id) {
                        window.open(`/air-export/${this.form.id}/charges/print`, '_blank');
                    } else {
                        let printWin = window.open('', '_blank', 'width=900,height=700');
                        let rowsHtml = '';
                        let totalAmount = 0;
                        if (this.form.charges && this.form.charges.length > 0) {
                            this.form.charges.forEach(c => {
                                let amt = (parseFloat(c.rate) || 0) * (parseFloat(c.qty) || 1) * (parseFloat(c.roe) || 1);
                                let vat = parseFloat(c.vat) || 0;
                                let tot = amt + (amt * (vat / 100));
                                totalAmount += tot;
                                rowsHtml += `<tr>
                                    <td>${c.party || '-'}</td>
                                    <td>${c.sal || '-'}</td>
                                    <td>${c.pr || '-'}</td>
                                    <td>${c.ppc || '-'}</td>
                                    <td>${c.chrg_code || '-'}</td>
                                    <td>${c.charge_name || '-'}</td>
                                    <td>${c.currency || 'USD'}</td>
                                    <td>${parseFloat(c.rate || 0).toFixed(2)}</td>
                                    <td>${c.qty || 1}</td>
                                    <td>${c.qty_type || 'B/L'}</td>
                                    <td>${parseFloat(c.roe || 1).toFixed(4)}</td>
                                    <td>$${amt.toFixed(2)}</td>
                                    <td>$${tot.toFixed(2)}</td>
                                </tr>`;
                            });
                        }
                        printWin.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Air Export Charges Statement</title>
                                <style>
                                    body { font-family: 'Helvetica Neue', Arial, sans-serif; padding: 25px; color: #333; }
                                    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #3b82f6; padding-bottom: 15px; margin-bottom: 20px; }
                                    h1 { font-size: 22px; margin: 0; color: #1e3a8a; }
                                    table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px; }
                                    th, td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; }
                                    th { background: #3b82f6; color: #fff; text-transform: uppercase; font-size: 11px; }
                                    .total-box { text-align: right; margin-top: 20px; font-size: 15px; font-weight: bold; color: #1e3a8a; }
                                </style>
                            </head>
                            <body>
                                <div class="header">
                                    <div>
                                        <h1>Air Export Charges Statement</h1>
                                        <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">File No: ${this.form.file_no || 'Draft'} | MAWB: ${this.form.mawb_no || 'N/A'}</p>
                                    </div>
                                    <div style="text-align: right; font-size: 12px; color: #666;">Date: ${new Date().toISOString().split('T')[0]}</div>
                                </div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Party</th><th>SAL</th><th>P/R</th><th>PP/C</th>
                                            <th>Code</th><th>Charge Name</th><th>Curr</th>
                                            <th>Rate</th><th>Qty</th><th>Unit</th><th>ROE</th>
                                            <th>Amount</th><th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>${rowsHtml || '<tr><td colspan="13" style="text-align:center; color:#888;">No charges added</td></tr>'}</tbody>
                                </table>
                                <div class="total-box">Total Amount (Local): $${totalAmount.toFixed(2)}</div>
                                <script>window.onload = function() { window.print(); }<\/script>
                            </body>
                            </html>
                        `);
                        printWin.document.close();
                    }
                },
                bulkUpdateCurrency() {
                    let newCurr = prompt('Enter new Currency code (e.g. USD, EUR, PKR):', 'USD');
                    if (!newCurr) return;
                    if (this.form.charges) {
                        this.form.charges.forEach(c => c.currency = newCurr.toUpperCase());
                        showToast('success', 'Updated currency for all charges to ' + newCurr.toUpperCase());
                    }
                },
                applyVatToAll() {
                    let vatPercent = prompt('Enter VAT percentage to apply to all charges:', '0');
                    if (vatPercent === null) return;
                    let num = parseFloat(vatPercent) || 0;
                    if (this.form.charges) {
                        this.form.charges.forEach(c => c.vat = num);
                        showToast('success', 'Applied ' + num + '% VAT to all charges.');
                    }
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
                saveShipment() {
                    document.getElementById('airExportForm').submit();
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
                                'booking_date' => $hbl->booking_date ?? '',
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
                                'feta' => $hbl->feta ?? '',
                                'itn_no' => $hbl->itn_no ?? '',
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
                                'commodities' => [],
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
                createInvoice(type) {
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
            <h1 class="caption-subject" style="font-size: 18px;">{{ isset($airExport) ? 'Edit Air Export Shipment' : 'Create Air Export Shipment' }}</h1>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-freightx"><i class="fa fa-save"></i> SAVE SHIPMENT</button>
                <a href="/air-export/list" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <!-- Main Tabs + Tools Dropdown (Ocean Import Pattern) -->
        <div style="display:flex; align-items:stretch; border-bottom:2px solid #e5e7eb; margin-bottom:15px; background:#fff;">
            <ul class="gf-tabs" style="border-bottom:none; margin-bottom:0; flex:1; display:flex;">
                <li :class="activeTab === 'basic' ? 'active' : ''" @click="activeTab = 'basic'"><a>Basic</a></li>
                <li :class="activeTab === 'charges' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'charges'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Charges</a></li>
                <li :class="activeTab === 'doc' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'doc'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Doc Center</a></li>
                <li :class="activeTab === 'workorder' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'workorder'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Work Order</a></li>
                <li :class="activeTab === 'status' ? 'active' : ''" @if(isset($airExport) && $airExport->id) @click="activeTab = 'status'" @else style="opacity: 0.5; cursor: not-allowed;" title="Save Basic tab first" @endif><a>Status</a></li>
            </ul>

            <!-- Divider -->
            <div style="display:flex;align-items:center;padding:0 4px;flex-shrink:0;">
                <span style="border-left:2px solid #e5e7eb;height:20px;display:inline-block;"></span>
            </div>

            <!-- Tools Options (Scrollable) -->
            <div style="flex:1;min-width:0;overflow-x:auto;white-space:nowrap;scrollbar-width:none;-ms-overflow-style:none;">
                <ul class="gf-tabs" style="border-bottom:none;margin-bottom:0;display:flex;white-space:nowrap;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;">
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? toggleBlock() : null">
                        <a><span x-text="isBlocked ? 'Unblock' : 'Block'"></span></a>
                    </li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? copyShipment() : null"><a>Copy</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? deleteShipment() : null"><a style="color:#ef4444;">Delete</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openDocPackage() : null"><a>Document Package</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? printMawb() : null"><a>MAWB Print</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openConsolidatedManifest() : null"><a>Consolidated Manifest</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openBookingConfirmation() : null"><a>Booking Confirmation</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openMawbPackageLabel() : null"><a>MAWB Package Label</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openPackageLabelList() : null"><a>Package Label List</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openPickupDeliveryOrder() : null"><a>Pickup / Delivery Order</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openSecurityEndorsement() : null"><a>Security Endorsement</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openOnHandReport() : null"><a>On Hand Report</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openScreenedCargoStatement() : null"><a>Screened Cargo Statement (K9)</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openProfitSummary() : null"><a>Profit Summary</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openProfitDetail() : null"><a>Profit Detail</a></li>
                    <li :class="!saved ? 'disabled-tab' : ''" @click="saved ? openTrackTrace() : null"><a>Track-Trace</a></li>
                </ul>
            </div>
        </div>

        <div style="padding-bottom: 50px;">
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
                            <div class="form-group-gf"><label class="form-label-gf">File No.</label><div class="form-input-container"><input type="text" name="file_no" class="form-control-gf" value="{{ isset($airExport) ? $airExport->file_no : 'MAE-' . date('YmdHis') }}" required></div></div>
                            <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*MAWB No.</label><div class="form-input-container"><input type="text" name="mawb_no" class="form-control-gf" x-model="form.mawb_no" required></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.notify"><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Co-loader</label><div class="form-input-container"><x-inline-select name="forwarding_agent_id" :options="$agents" module="trade-partner" x-model="form.co_loader" class="form-control-gf" /></div></div>

                            <!-- Row 2 -->
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Carrier</label><div class="form-input-container"><x-inline-select name="carrier_id" :options="$agents" module="trade-partner" x-model="form.carrier" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="form.awb_date"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Post Date</label><div class="form-input-container"><input type="date" name="post_date" class="form-control-gf" x-model="form.post_date"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Actual Shipper</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.actual_shipper"><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>

                            <!-- Row 3 -->
                            <div class="form-group-gf"><label class="form-label-gf">Issuing Carrier</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.issuing_carrier"><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Shipper</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.shipper"><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
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

                            <!-- Row 4 -->
                            <div class="form-group-gf"><label class="form-label-gf">AWB Type</label><div class="form-input-container"><x-inline-select name="awb_type" :options="$agents" module="trade-partner" x-model="form.awb_type" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.consignee"><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Acct. Carrier</label><div class="form-input-container"><x-inline-select name="acct_carrier_id" :options="$agents" module="trade-partner" x-model="form.awb_acct_carrier" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">ITN No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.itn_no"></div></div>
                        </div>

                        <div class="form-grid-4">
                            <div class="form-group-gf"><label class="form-label-gf">CERS No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.cers_no"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Reference No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.reference_no"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Direct Master</label><div class="form-input-container" style="justify-content: flex-start;"><input type="checkbox" x-model="isDirectMaster"></div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf"><label class="form-label-gf">Departure</label><div class="form-input-container"><x-inline-select name="dep_port_id" :options="$ports" module="port" x-model="form.departure" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Destination</label><div class="form-input-container"><x-inline-select name="dst_port_id" :options="$ports" module="port" x-model="form.destination" class="form-control-gf" /></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">Flight No.</label><div class="form-input-container"><input type="text" name="flight_no" class="form-control-gf" x-model="form.flight_no"></div></div>
                            <div></div>

                            <!-- Row 2 -->
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf" style="color:red;">*ETD</label><div class="form-input-container"><input type="date" name="etd" class="form-control-gf" x-model="form.etd" required></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">ATD</label><div class="form-input-container"><input type="date" name="atd" class="form-control-gf" x-model="form.atd"></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">ETA</label><div class="form-input-container"><input type="date" name="eta" class="form-control-gf" x-model="form.eta" style="background:#fff8e1;"></div></div>
                            <!-- Hidden per client instruction -->
                            <div class="form-group-gf" style="display: none;"><label class="form-label-gf">ATA</label><div class="form-input-container"><input type="date" name="ata" class="form-control-gf" x-model="form.ata"></div></div>

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
                            <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;">Set Dimensions</button></div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">Buying Rate</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" name="buying_rate" class="form-control-gf" style="flex:1;" x-model="form.buying_rate"> per <x-inline-select name="buying_rate_unit" :options="$agents" module="trade-partner" class="form-control-gf" /></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">AWB Gross Weight</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">KG</span> <input type="text" class="form-control-gf" style="flex:1;"> <span style="font-size:10px;">LB</span></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;">Sum Package & Weight</button></div></div>

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
                            <div class="form-group-gf"><label class="form-label-gf">D.V. Carriage</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.dv_carriage"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">D.V. Customs</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.dv_customs"></div></div>
                            <div class="form-group-gf"><label class="form-label-gf">WT/VAL</label><div class="form-input-container" style="font-size:10px; gap:4px; justify-content: flex-start; align-items:center;"><input type="radio" value="P" x-model="form.wt_val"> PPD <input type="radio" value="C" x-model="form.wt_val"> COLL</div></div>
                            <div class="form-group-gf"><label class="form-label-gf">Other</label><div class="form-input-container" style="font-size:10px; gap:4px; justify-content: flex-start; align-items:center;"><input type="radio" value="P" x-model="form.other_term"> PPD <input type="radio" value="C" x-model="form.other_term"> COLL</div></div>

                            <!-- Row 2 -->
                            <div class="form-group-gf"><label class="form-label-gf">Insurance</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="form.insurance"></div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 15px; margin-bottom: 5px; align-items: center; gap: 10px;">
                            <label class="form-label-gf" style="width:auto; margin:0;">Display Unit</label>
                            <select class="form-control-gf" style="width: 150px;" x-model="form.display_unit"><option value="BOTH">Show Both</option><option value="KG">KG Only</option><option value="LB">LB Only</option></select>
                        </div>

                        <hr style="margin: 15px 0; border-top: 1px solid #eee;">

                        <!-- MAWB Grids -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="font-size: 11px; font-weight: 600; color: #4b77be; margin: 0 0 5px 0;">Other Charges</h4>
                            <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:10px; border:1px solid #ddd;">
                                <thead>
                                    <tr style="background:#f9f9f9;">
                                        <th style="width: 30px; text-align: center; border:1px solid #ddd;"><input type="checkbox"></th>
                                        <th style="border:1px solid #ddd; padding:4px;">Charge Code</th>
                                        <th style="border:1px solid #ddd; padding:4px;">Description</th>
                                        <th style="border:1px solid #ddd; padding:4px; width:60px;">Term</th>
                                        <th style="border:1px solid #ddd; padding:4px; width:80px;">Rate</th>
                                        <th style="border:1px solid #ddd; padding:4px; width:100px;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(charge, idx) in form.other_charges" :key="idx">
                                        <tr>
                                            <td style="text-align: center; border:1px solid #ddd;"><i class="fa fa-trash" style="color:red; cursor:pointer;" @click="removeCharge(idx)"></i></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="charge.charge_code" :name="'charges['+idx+'][charge_code]'"></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="charge.description" :name="'charges['+idx+'][description]'"></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><select class="form-control-gf" x-model="charge.term" :name="'charges['+idx+'][term]'"><option value="P">PPD</option><option value="C">COLL</option></select></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="number" step="any" class="form-control-gf" x-model="charge.rate" :name="'charges['+idx+'][rate]'"></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="number" step="any" class="form-control-gf" x-model="charge.amount" :name="'charges['+idx+'][amount]'"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="form.other_charges.length === 0"><td colspan="6" style="text-align: center; color: #999; padding: 10px;">No charges added.</td></tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn-default-gf" style="margin-top: 5px;" @click="addCharge()"><i class="fa fa-plus"></i> Add Charge</button>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <h4 style="font-size: 11px; font-weight: 600; color: #4b77be; margin: 0 0 5px 0;">Accounting Information</h4>
                            <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:10px; border:1px solid #ddd;">
                                <thead>
                                    <tr style="background:#f9f9f9;">
                                        <th style="width: 30px; text-align: center; border:1px solid #ddd;"><input type="checkbox"></th>
                                        <th style="border:1px solid #ddd; padding:4px;">Information Code</th>
                                        <th style="border:1px solid #ddd; padding:4px;">Accounting Information</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(info, idx) in form.accounting_info" :key="idx">
                                        <tr>
                                            <td style="text-align: center; border:1px solid #ddd;"><i class="fa fa-trash" style="color:red; cursor:pointer;" @click="removeAcctInfo(idx)"></i></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="info.code" :name="'accounting_info['+idx+'][code]'"></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="info.info" :name="'accounting_info['+idx+'][info]'"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="form.accounting_info.length === 0"><td colspan="3" style="text-align: center; color: #999; padding: 10px;">No information added.</td></tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn-default-gf" style="margin-top: 5px;" @click="addAcctInfo()"><i class="fa fa-plus"></i> Add Info</button>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <h4 style="font-size: 11px; font-weight: 600; color: #4b77be; margin: 0 0 5px 0;">Commodity</h4>
                            <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:10px; border:1px solid #ddd;">
                                <thead>
                                    <tr style="background:#f9f9f9;">
                                        <th style="width: 30px; text-align: center; border:1px solid #ddd;"><input type="checkbox"></th>
                                        <th style="border:1px solid #ddd; padding:4px;">Description</th>
                                        <th style="border:1px solid #ddd; padding:4px; width:100px;">HTS No.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(commodity, idx) in form.commodities" :key="idx">
                                        <tr>
                                            <td style="text-align: center; border:1px solid #ddd;"><i class="fa fa-trash" style="color:red; cursor:pointer;" @click="removeCommodity(idx)"></i></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="commodity.description" :name="'commodities['+idx+'][description]'"></td>
                                            <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="commodity.hts_no" :name="'commodities['+idx+'][hts_no]'"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="form.commodities.length === 0"><td colspan="3" style="text-align: center; color: #999; padding: 10px;">No commodities added.</td></tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn-default-gf" style="margin-top: 5px;" @click="addCommodity()"><i class="fa fa-plus"></i> Add Commodity</button>
                        </div>

                        <div style="margin-top: 15px;">
                            <h4 style="font-size: 11px; font-weight: 600; color: #333; margin: 0 0 5px 0;">Internal Memo</h4>
                            <textarea class="form-control-gf" style="height: 60px; padding: 5px; resize: vertical;" x-model="form.memo"></textarea>
                        </div>
                    </div>
                </div>

                <!-- House B/L (HAWB) Section -->
                <template x-for="(hawb, index) in hawbs" :key="index">
                    <div class="portlet light" style="margin-top: 5px;">
                        <div class="portlet-title" style="background: #f2bc00; color: #fff; cursor: pointer; min-height: 24px; padding: 2px 10px;" @click="hawb.show = !hawb.show">
                            <span class="caption-subject" style="color: #fff; font-size: 11px;"><i class="fa fa-user"></i> HAWB Information <small style="color:rgba(255,255,255,0.8); margin-left: 10px; font-weight: normal;" x-text="'OP: ' + hawb.op"></small></span>
                            <div class="actions" style="display: flex; gap: 8px; align-items: center;">
                                <!-- HAWB Tools -->
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" @click.stop="printHawb(index)"
                                        style="background: #ffffff; color: #374151; border: 1px solid #d1d5db; border-radius: 3px; padding: 2px 8px; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa fa-print" style="color: #4b5563;"></i> HAWB Print
                                    </button>
                                </div>
                                <i @click.stop="removeHawb(index)" class="fa fa-times" style="font-size: 12px; opacity: 0.8; cursor: pointer;" title="Delete HAWB"></i>
                                <i class="fa fa-angle-down transition-transform" :class="hawb.show ? 'rotate-180' : ''" style="font-size: 12px;"></i>
                            </div>
                        </div>
                        <div class="portlet-body" x-show="hawb.show" style="background: #f9f9f9; padding: 10px;">
                            
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
                                <div class="form-group-gf"><label class="form-label-gf" style="color:red;">*HAWB No.</label><div class="form-input-container"><input type="text" :name="'hbls[' + index + '][hawb_no]'" class="form-control-gf" x-model="hawb.hawb_no" required></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Actual Shipper</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][shipper_id]'" :options="$agents" module="trade-partner" x-model="hawb.shipper" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Bill To</label><div class="form-input-container"><x-inline-select name="bill_to" :options="$agents" module="trade-partner" x-model="hawb.bill_to" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][notify_party_id]'" :options="$agents" module="trade-partner" x-model="hawb.notify" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                
                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Booking No.</label><div class="form-input-container"><input type="text" :name="'hbls[' + index + '][booking_no]'" class="form-control-gf" x-model="hawb.booking_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][customer_id]'" :options="$agents" module="trade-partner" x-model="hawb.customer" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][consignee_id]'" :options="$agents" module="trade-partner" x-model="hawb.consignee" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><x-inline-select name="" x-bind:name="'hbls[' + index + '][oversea_agent_id]'" :options="$agents" module="trade-partner" x-model="hawb.oversea_agent" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                
                                <!-- Row 3 -->
                                <div class="form-group-gf"><label class="form-label-gf">Quotation No.</label><div class="form-input-container"><x-inline-select name="quotation_no" :options="$agents" module="trade-partner" x-model="hawb.quotation_no" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container">
                                    <select :name="'hbls[' + index + '][sales_person_id]'" x-model="hawb.sales" class="form-control-gf">
                                        <option value="">Select...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Booking Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="hawb.booking_date"></div></div>
                                <div></div> <!-- Empty cell for grid alignment -->

                                <!-- Row 4 -->
                                <div class="form-group-gf"><label class="form-label-gf">ITN No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="hawb.itn_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="hawb.op" disabled style="background:#eee;"></div></div>
                                <div></div>
                                <div></div>
                            </div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div class="form-grid-4">
                                <!-- Row 1 -->
                                <div class="form-group-gf"><label class="form-label-gf">Departure</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="hawb.departure"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Destination</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="hawb.destination"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Final ETA</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="hawb.feta"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container"><select class="form-control-gf" x-model="hawb.cargo_type"><option value="GENERAL CARGO">GENERAL CARGO</option></select></div></div>

                                <!-- Row 2 -->
                                <div class="form-group-gf"><label class="form-label-gf">Cargo Pickup</label><div class="form-input-container"><x-inline-select name="cargo_pickup" :options="$agents" module="trade-partner" x-model="hawb.cargo_pickup" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Delivery To/Pier</label><div class="form-input-container"><x-inline-select name="delivery_to" :options="$agents" module="trade-partner" x-model="hawb.delivery_to" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Sales Type</label><div class="form-input-container"><x-inline-select name="sales_type" :options="$agents" module="trade-partner" x-model="hawb.sales_type" class="form-control-gf" /></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Ship Type</label><div class="form-input-container"><select class="form-control-gf" x-model="hawb.ship_type"><option value="NORMAL">NORMAL</option></select></div></div>
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
                                <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;">Set Dimensions</button></div></div>

                                <!-- Row 3 -->
                                <div class="form-group-gf"><label class="form-label-gf">Selling Rate</label><div class="form-input-container" style="gap:4px; align-items:center;"><input type="text" :name="'hbls[' + index + '][selling_rate]'" class="form-control-gf" style="flex:1;" x-model="hawb.selling_rate"> per <select class="form-control-gf" style="width:50px;"><option>KG</option></select></div></div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Chargeable Weight</label>
                                    <div class="form-input-container" style="gap:4px; align-items:center;">
                                        <input type="number" step="any" :name="'hbls[' + index + '][chargeable_weight]'" class="form-control-gf" style="flex:1;" x-model="hawb.chargeable_weight"> <span style="font-size:10px;">KG</span>
                                    </div>
                                </div>
                                <div class="form-group-gf"><label class="form-label-gf">Amount</label><div class="form-input-container"><input type="text" class="form-control-gf" style="text-align:right;"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf"></label><div class="form-input-container"><button type="button" class="btn-default-gf" style="width:100%;">Sum Package & Weight</button></div></div>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div style="display: flex; justify-content: flex-end; margin-top: 10px; align-items: center; gap: 10px;">
                                <label class="form-label-gf" style="width:auto; margin:0;">Display Unit</label>
                                <select class="form-control-gf" style="width: 150px;" x-model="hawb.display_unit"><option value="BOTH">Show Both</option><option value="KG">KG Only</option><option value="LB">LB Only</option></select>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <!-- Grid Lists for HAWB -->
                            <div style="margin-bottom: 10px;">
                                <h4 style="font-size: 11px; font-weight: 600; color: #333; margin: 0 0 5px 0;">Commodity / HTS No.</h4>
                                <table class="table-custom" style="width:100%; border-collapse:collapse; font-size:10px; border:1px solid #ddd;">
                                    <thead>
                                        <tr style="background:#f9f9f9;">
                                            <th style="width: 30px; text-align: center; border:1px solid #ddd;"><input type="checkbox"></th>
                                            <th style="border:1px solid #ddd; padding:4px;">Commodity Description</th>
                                            <th style="border:1px solid #ddd; padding:4px;">HTS No.</th>
                                            <th style="border:1px solid #ddd; padding:4px; width:100px;">P.O. No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(commodity, cidx) in hawb.commodities" :key="cidx">
                                            <tr>
                                                <td style="text-align: center; border:1px solid #ddd;"><i class="fa fa-trash" style="color:red; cursor:pointer;" @click="removeHawbCommodity(index, cidx)"></i></td>
                                                <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="commodity.description" :name="'hbls['+index+'][commodities]['+cidx+'][description]'"></td>
                                                <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="commodity.hts_no" :name="'hbls['+index+'][commodities]['+cidx+'][hts_no]'"></td>
                                                <td style="border:1px solid #ddd; padding:4px;"><input type="text" class="form-control-gf" x-model="commodity.po_no" :name="'hbls['+index+'][commodities]['+cidx+'][po_no]'"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="hawb.commodities.length === 0"><td colspan="4" style="text-align: center; color: #999; padding: 10px;">No commodities added.</td></tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn-default-gf" style="margin-top: 5px;" @click="addHawbCommodity(index)"><i class="fa fa-plus"></i> Add Row</button>
                            </div>

                            <hr style="margin: 10px 0; border-top: 1px solid #ddd;">

                            <div class="form-grid-4" style="grid-template-columns: repeat(2, 1fr);">
                                <div>
                                    <h4 style="font-size: 11px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Mark</h4>
                                    <textarea class="form-control-gf" style="height: 60px !important; resize: vertical; padding:5px;" x-model="hawb.mark"></textarea>
                                </div>
                                <div>
                                    <h4 style="font-size: 11px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Description</h4>
                                    <textarea class="form-control-gf" style="height: 60px !important; resize: vertical; padding:5px;" x-model="hawb.description"></textarea>
                                </div>
                            </div>
                            <div style="margin-top: 10px;">
                                <h4 style="font-size: 11px; font-weight: 600; margin: 0 0 5px 0; color: #333;">Remark</h4>
                                <textarea class="form-control-gf" style="height: 50px !important; resize: vertical; padding:5px;" x-model="hawb.remark"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end" style="margin-top: 5px; margin-bottom: 20px;">
                    <button @click="addHawb" class="btn-freightx" style="background:#f2bc00; padding: 4px 15px; font-size: 11px; border-radius: 2px;"><i class="fa fa-plus"></i> ADD HAWB</button>
                </div>
            </div>

            <!-- CHARGES TAB -->
            <div x-show="activeTab === 'charges' || activeTab === 'accounting'" class="main-grid" x-cloak style="flex-direction: column; gap: 10px;">
                <div class="portlet light" style="padding: 12px; background: #fff; border: 1px solid #e2ebf2; border-radius: 4px;">
                    <div style="color: #31708f; font-weight: bold; font-size: 13px; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa fa-folder-open-o" style="font-size: 14px;"></i> CHARGES
                    </div>
                    <div class="portlet-body">

                        <!-- Row 1: BKG, MBL, CM Total, Excel & Print Icons -->
                        <div style="background: #eef6fc; border: 1px solid #d0e1f0; border-radius: 3px; padding: 6px 10px; margin-bottom: 10px; display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span style="font-weight: 600; color: #555; font-size: 11px;">BKG :</span>
                                    <input type="text" x-model="form.file_no" class="form-control-gf" style="width: 110px; height: 22px; font-size: 10px; background: #f9f9f9;" readonly>
                                    <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 6px; font-weight: bold; color: #2b6889; border-color: #9cbacf; font-size: 10px;">GP</button>
                                    
                                    <div style="display: flex; align-items: center; margin-left: 5px;">
                                        <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 5px; background: #31708f; color: #fff; border: none; border-radius: 2px 0 0 2px;"><i class="fa fa-angle-double-left"></i></button>
                                        <input type="text" x-model="form.hawb_no" class="form-control-gf" style="width: 130px; height: 22px; font-size: 10px; border-radius: 0; text-align: center;" placeholder="ELCKSHA25120233">
                                        <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 5px; background: #31708f; color: #fff; border: none; border-radius: 0 2px 2px 0;"><i class="fa fa-angle-double-right"></i></button>
                                    </div>
                                    <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 6px; font-weight: bold; color: #2b6889; border-color: #9cbacf; font-size: 10px;">GP</button>

                                    <span style="font-weight: 600; color: #555; margin-left: 8px; font-size: 11px;">MBL :</span>
                                    <input type="text" x-model="form.mawb_no" class="form-control-gf" style="width: 110px; height: 22px; font-size: 10px; background: #f9f9f9;" readonly>
                                    <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 6px; font-weight: bold; color: #2b6889; border-color: #9cbacf; font-size: 10px;">GP</button>

                                    <button type="button" class="btn-default-gf" style="height: 22px; padding: 0 8px; font-weight: bold; color: #1c5270; border-color: #31708f; background: #fff; margin-left: 5px; font-size: 10px;">Show All of this BKG</button>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 12px; font-weight: bold; color: #0f3750;">CM : <span x-text="calculateTotalCharges().toFixed(2)">0.00</span></span>
                                </div>
                            </div>

                            <!-- Row 2: Dynamic Dropdown Filters & Route Summary -->
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; padding-top: 4px; border-top: 1px solid #e2ebf2;">
                                <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                    <select x-model="manifestFilters.party" class="form-control-gf" style="height: 22px; width: 75px; font-size: 10px; padding: 1px 3px;">
                                        <option value="All">All Parties</option>
                                        <option value="Custom">Custom</option>
                                        <option value="Shipper">Shipper</option>
                                        <option value="Consignee">Consignee</option>
                                        <option value="Agent">Agent</option>
                                    </select>

                                    <select x-model="manifestFilters.sal" class="form-control-gf" style="height: 22px; width: 60px; font-size: 10px; padding: 1px 3px;">
                                        <option value="All">All SAL</option>
                                        <option value="Air">Air</option>
                                        <option value="Ocean">Ocean</option>
                                        <option value="Truck">Truck</option>
                                    </select>

                                    <select x-model="manifestFilters.pr" class="form-control-gf" style="height: 22px; width: 60px; font-size: 10px; padding: 1px 3px;">
                                        <option value="All">All P/R</option>
                                        <option value="Rec">Rec</option>
                                        <option value="Pay">Pay</option>
                                    </select>

                                    <select x-model="manifestFilters.ppc" class="form-control-gf" style="height: 22px; width: 65px; font-size: 10px; padding: 1px 3px;">
                                        <option value="All">All PP/C</option>
                                        <option value="Colle">Colle</option>
                                        <option value="Prepaid">Prepaid</option>
                                    </select>

                                    <select x-model="manifestFilters.currency" class="form-control-gf" style="height: 22px; width: 60px; font-size: 10px; padding: 1px 3px;">
                                        <option value="All">All Curr</option>
                                        @foreach($currencies as $curr)
                                            <option value="{{ $curr->code }}">{{ $curr->code }}</option>
                                        @endforeach
                                    </select>

                                    <select x-model="manifestFilters.invoiced" class="form-control-gf" style="height: 22px; width: 65px; font-size: 10px; padding: 1px 3px;">
                                        <option value="All">All Inv</option>
                                        <option value="Invoiced">Invoiced</option>
                                        <option value="Uninvoiced">Uninvoiced</option>
                                    </select>

                                    <button type="button" @click="resetManifestFilters()" class="btn-default-gf" style="height: 22px; padding: 0 8px; background: #31708f; color: #fff; border: none; border-radius: 2px; font-size: 10px; display: flex; align-items: center; gap: 4px;" title="Reset Filters"><i class="fa fa-filter"></i> Reset</button>
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px; font-size: 10px; color: #333; flex-wrap: wrap;">
                                    <span><strong>POL :</strong> <input type="text" :value="form.dep_port_id ? '{{ $ports->where('id', $airExport->dep_port_id ?? 0)->first()->name ?? "Shanghai" }}' : 'Shanghai'" class="form-control-gf" style="width: 85px; height: 20px; font-size: 10px; display: inline-block; padding: 0 4px;" readonly></span>
                                    <span><strong>POD :</strong> <input type="text" :value="form.dst_port_id ? '{{ $ports->where('id', $airExport->dst_port_id ?? 0)->first()->name ?? "Chattogram" }}' : 'Chattogram'" class="form-control-gf" style="width: 85px; height: 20px; font-size: 10px; display: inline-block; padding: 0 4px;" readonly></span>
                                    <span><strong>FOB</strong></span>
                                    <input type="text" :value="form.referred_by_id ? 'YOUNGONE HI-TE' : 'YOUNGONE HI-TE'" class="form-control-gf" style="width: 120px; height: 20px; font-size: 10px;" readonly>
                                    <span>C:<span x-text="form.charges ? form.charges.length : 0"></span></span>
                                    <span>A:<span x-text="form.charges ? form.charges.filter(c => c.pr === 'Rec').length : 0"></span></span>
                                    <span>R:<span x-text="form.charges ? form.charges.filter(c => c.pr === 'Pay').length : 0"></span></span>
                                    <button type="button" @click="addCharge()" class="btn-default-gf" style="height: 22px; padding: 0 8px; background: #31708f; color: #fff; border: none; border-radius: 2px; font-size: 10px;" title="Add Charge Row"><i class="fa fa-plus"></i> Add Row</button>
                                </div>
                            </div>
                        </div>

                        <!-- Charge Filters Pills -->
                        <div style="display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; align-items: center;">
                            <button type="button" @click="activeChargeFilter = 'All'" :class="activeChargeFilter === 'All' ? 'btn-filter-active' : 'btn-filter'">All (<span x-text="form.charges ? form.charges.length : 0"></span>)</button>
                            <button type="button" @click="activeChargeFilter = 'AR'" :class="activeChargeFilter === 'AR' ? 'btn-filter-active' : 'btn-filter'">A/R (<span x-text="form.charges ? form.charges.filter(c => c.pr === 'Rec').length : 0"></span>)</button>
                            <button type="button" @click="activeChargeFilter = 'AP'" :class="activeChargeFilter === 'AP' ? 'btn-filter-active' : 'btn-filter'">A/P (<span x-text="form.charges ? form.charges.filter(c => c.pr === 'Pay').length : 0"></span>)</button>
                            <button type="button" @click="activeChargeFilter = 'DC'" :class="activeChargeFilter === 'DC' ? 'btn-filter-active' : 'btn-filter'">D/C (<span x-text="form.charges ? form.charges.filter(c => c.pr === 'DC').length : 0"></span>)</button>
                            <button type="button" @click="deleteSelectedCharges()" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; margin-left: auto; height: 26px; padding: 0 12px; font-size: 11px; border-radius: 3px; font-weight: 600; white-space: nowrap; cursor: pointer;" title="Delete Selected Charges"><i class="fa fa-trash"></i> Delete Selected</button>
                        </div>

                        <!-- Charges Table -->
                        <div class="table-responsive">
                            <table class="table-custom" style="font-size: 11px; width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f1f3f6; font-size: 10px; border-bottom: 2px solid #e2ebf2;">
                                        <th style="width: 30px; text-align: center;"><input type="checkbox" @change="toggleAllCharges($event)"></th>
                                        <th style="width: 40px; text-align: center;">#</th>
                                        <th style="width: 90px;">PARTY</th>
                                        <th style="width: 140px;">PARTY NAME</th>
                                        <th style="width: 55px;">SAL</th>
                                        <th style="width: 55px;">P/R</th>
                                        <th style="width: 65px;">PP/C</th>
                                        <th style="width: 90px;">CHRG CODE</th>
                                        <th style="width: 130px;">CHARGE NAME</th>
                                        <th style="width: 65px;">CURRENCY</th>
                                        <th style="width: 75px; text-align: right;">RATE</th>
                                        <th style="width: 55px; text-align: right;">QTY</th>
                                        <th style="width: 65px;">QTY TYPE</th>
                                        <th style="width: 55px; text-align: right;">ROE</th>
                                        <th style="width: 95px; text-align: right;">AMOUNT (USD)</th>
                                        <th style="width: 55px; text-align: right;">VAT %</th>
                                        <th style="width: 95px; text-align: right;">TOTAL (USD)</th>
                                        <th style="width: 90px;">INV NO.</th>
                                        <th style="width: 95px;">FINANCIAL DATE</th>
                                        <th style="width: 90px;">EQ B/L NO.</th>
                                        <th style="width: 45px; text-align: center;">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(charge, idx) in filteredCharges" :key="idx">
                                        <tr :style="charge.selected ? 'background:#fef9e7;' : ''">
                                            <td style="text-align: center;">
                                                <input type="hidden" :name="'charges['+idx+'][id]'" :value="charge.id">
                                                <input type="checkbox" x-model="charge.selected">
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                                                    <button type="button" @click="charge.expanded = !charge.expanded" class="btn-default-gf" style="padding: 0; height: 15px; width: 15px; line-height: 1; border-radius: 2px; background: #fff; border: 1px solid #ccc;">
                                                        <i :class="charge.expanded ? 'fa fa-minus' : 'fa fa-plus'" style="font-size: 8px; color: #555;"></i>
                                                    </button>
                                                    <span x-text="idx + 1" style="font-weight: bold; font-size: 10px;"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <select :name="'charges['+idx+'][party]'" x-model="charge.party" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;">
                                                    <option value="Shipper">Shipper</option>
                                                    <option value="Consignee">Consignee</option>
                                                    <option value="Custom">Custom</option>
                                                    <option value="Agent">Agent</option>
                                                    <option value="C&F/CN">C&F/CN</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select :name="'charges['+idx+'][party_name_id]'" x-model="charge.party_name_id" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;">
                                                    <option value="">Select...</option>
                                                    @foreach($agents as $agent)
                                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select :name="'charges['+idx+'][sal]'" x-model="charge.sal" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;">
                                                    <option value="Air">Air</option>
                                                    <option value="Ocean">Ocean</option>
                                                    <option value="Truck">Truck</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select :name="'charges['+idx+'][pr]'" x-model="charge.pr" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;">
                                                    <option value="Rec">Rec</option>
                                                    <option value="Pay">Pay</option>
                                                    <option value="DC">D/C</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select :name="'charges['+idx+'][ppc]'" x-model="charge.ppc" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;">
                                                    <option value="Colle">Colle</option>
                                                    <option value="Prepaid">Prepaid</option>
                                                </select>
                                            </td>
                                            <td><input type="text" :name="'charges['+idx+'][chrg_code]'" x-model="charge.chrg_code" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;" placeholder="Code"></td>
                                            <td><input type="text" :name="'charges['+idx+'][charge_name]'" x-model="charge.charge_name" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;" placeholder="Charge Name"></td>
                                            <td>
                                                <select :name="'charges['+idx+'][currency]'" x-model="charge.currency" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;">
                                                    <option value="USD">USD</option>
                                                    @foreach($currencies as $curr)
                                                        <option value="{{ $curr->code }}">{{ $curr->code }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" :name="'charges['+idx+'][rate]'" x-model="charge.rate" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px; text-align: right;" step="0.01" @input="updateChargeAmount(idx)"></td>
                                            <td><input type="number" :name="'charges['+idx+'][qty]'" x-model="charge.qty" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px; text-align: right;" step="0.01" @input="updateChargeAmount(idx)"></td>
                                            <td>
                                                <select :name="'charges['+idx+'][qty_type]'" x-model="charge.qty_type" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;">
                                                    <option value="B/L">B/L</option>
                                                    <option value="UNIT">UNIT</option>
                                                    <option value="KG">KG</option>
                                                    <option value="CBM">CBM</option>
                                                    <option value="PCS">PCS</option>
                                                    @foreach($packageUnits as $unit)
                                                        <option value="{{ $unit->code }}">{{ $unit->code }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" :name="'charges['+idx+'][roe]'" x-model="charge.roe" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px; text-align: right;" step="0.0001" @input="updateLocalAmount(idx)"></td>
                                            <td style="text-align: right; font-size: 10px;" x-text="((parseFloat(charge.rate) || 0) * (parseFloat(charge.qty) || 0) * (parseFloat(charge.roe) || 1)).toFixed(2)">0.00</td>
                                            <td><input type="number" :name="'charges['+idx+'][vat]'" x-model="charge.vat" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px; text-align: right;" step="0.01" @input="updateLocalAmount(idx)"></td>
                                            <td style="text-align: right; font-weight: bold; font-size: 10px;" x-text="(((parseFloat(charge.rate) || 0) * (parseFloat(charge.qty) || 0) * (parseFloat(charge.roe) || 1)) * (1 + (parseFloat(charge.vat) || 0) / 100)).toFixed(2)">0.00</td>
                                            <td><input type="text" :name="'charges['+idx+'][inv_no]'" x-model="charge.inv_no" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;"></td>
                                            <td><input type="date" :name="'charges['+idx+'][financial_date]'" x-model="charge.financial_date" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;"></td>
                                            <td><input type="text" :name="'charges['+idx+'][eq_bl_no]'" x-model="charge.eq_bl_no" class="form-control-gf" style="font-size: 10px; height: 20px; padding: 2px;"></td>
                                            <td style="text-align: center;">
                                                <button type="button" @click="deleteCharge(charge)" class="btn-tool-icon" style="height: 20px; width: 20px; padding: 0; color: red; border-color: red;" title="Delete">
                                                    <i class="fa fa-trash" style="font-size: 10px;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="!filteredCharges || filteredCharges.length === 0">
                                        <tr>
                                            <td colspan="21" style="text-align: center; padding: 30px; color: #999;">
                                                <i class="fa fa-inbox" style="font-size: 40px; opacity: 0.3; display: block; margin-bottom: 10px;"></i>
                                                <span x-text="form.charges && form.charges.length === 0 ? 'No charges added yet. Click &quot;+ Add Row&quot; button to start.' : 'No charges match the selected filter.'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot x-show="filteredCharges && filteredCharges.length > 0">
                                    <tr style="background: #f9fafb; font-weight: bold; font-size: 10px;">
                                        <td colspan="14" style="text-align: right; padding-right: 10px;">Total:</td>
                                        <td style="text-align: right;" x-text="filteredCharges.reduce((sum, c) => sum + ((parseFloat(c.rate) || 0) * (parseFloat(c.qty) || 0) * (parseFloat(c.roe) || 1)), 0).toFixed(2)">0.00</td>
                                        <td></td>
                                        <td style="text-align: right;" x-text="filteredCharges.reduce((sum, c) => sum + (((parseFloat(c.rate) || 0) * (parseFloat(c.qty) || 0) * (parseFloat(c.roe) || 1)) * (1 + (parseFloat(c.vat) || 0) / 100)), 0).toFixed(2)">0.00</td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Bottom Action Buttons Row -->
                        <div style="margin-top: 15px; display: flex; justify-content: flex-start; align-items: center; background: #f8fafc; padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 4px;">
                            <button type="button" class="btn-freightx" style="background: #16a34a; color: white; border: none; padding: 7px 18px; font-weight: 600; border-radius: 4px; font-size: 11px; cursor: pointer;" @click.prevent="generateFreightInvoice()">
                                Generate Freight Invoice
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- DOC CENTER TAB (Placeholder) -->
            <div x-show="activeTab === 'doc'" class="main-grid" x-cloak>
                <div class="portlet light">
                    <div class="portlet-title"><span class="caption-subject">Doc Center</span></div>
                    <div class="portlet-body">
                        <div style="text-align: center; color: #999; padding: 40px;">Doc Center module will be loaded here...</div>
                    </div>
                </div>
            </div>

            <!-- WORK ORDER TAB -->
            <div x-show="activeTab === 'workorder'" class="main-grid" x-cloak style="flex-direction: row; gap: 10px;">
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
            <div x-show="activeTab === 'status'" class="main-grid" x-cloak style="flex-direction: row; gap: 10px;">
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
                            <select class="form-control-gf" x-model="filters.pod">
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

                <div style="display: flex; justify-content: flex-end; margin-bottom: 5px;">
                    <button type="button" class="btn-tool-secondary" style="background: #67809f; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 10px; border: none;" @click="showQuoteConfig = !showQuoteConfig"><i class="fa fa-cogs"></i> Config</button>
                </div>

                <!-- Ocean Style Interactive Table -->
                <div class="table-responsive" style="margin-bottom: 10px; height: 280px; border: 1px solid #e7ecf1;">
                    <table class="table-custom">
                        <thead>
                            <tr style="background: #888; color: #fff;">
                                <th style="text-align: center;">Select</th>
                                <th>Quote No.</th>
                                <th>Valid Date <i class="fa fa-sort" style="float: right;"></i></th>
                                <th>Status <i class="fa fa-sort" style="float: right;"></i></th>
                                <th>Creation Date <i class="fa fa-sort" style="float: right;"></i></th>
                                <th>Commodity</th>
                                <th>Departure</th>
                                <th>Destination</th>
                                <th>Carrier</th>
                                <th>Sales</th>
                                <th>OP</th>
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
                                
                                <td style="text-align: center;"><input type="radio" name="quote_sel" :checked="selectedQuote && selectedQuote.quote_no === '{{ addslashes($quote->quote_no) }}'"></td>
                                <td><span style="color: #3b82f6; font-weight: 600;">{{ $quote->quote_no }}</span></td>
                                <td>{{ $quote->quote_date ? $quote->quote_date->format('m-d-Y') : '' }} ~ {{ $quote->expiry_date ? $quote->expiry_date->format('m-d-Y') : '' }}</td>
                                <td><span style="background: {{ in_array(strtoupper($quote->status), ['WON', 'ACCEPTED']) ? '#10b981' : '#64748b' }}; color: #fff; padding: 1px 4px; border-radius: 2px; font-size: 9px; font-weight: 600;">{{ $quote->status }}</span></td>
                                <td>{{ $quote->created_at ? $quote->created_at->format('Y-m-d') : '' }}</td>
                                <td>{{ $quote->commodity ?: '-' }}</td>
                                <td>{{ $quote->pol->name ?? '-' }}</td>
                                <td>{{ $quote->pod->name ?? '-' }}</td>
                                <td>{{ $quote->carrier->name ?? '-' }}</td>
                                <td>{{ $quote->salesPerson->name ?? '-' }}</td>
                                <td>{{ $quote->op->name ?? '-' }}</td>
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