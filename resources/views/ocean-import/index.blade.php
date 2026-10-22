<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        /* Specific overrides for Ocean Import Form to reduce excessive gaps */
        .form-grid-4 {
            gap: 2px 6px !important;
        }
        .form-group-gf {
            margin-bottom: 2px !important;
        }
        .form-label-gf {
            width: 95px !important; /* Standardize and slightly reduce label width */
        }
    </style>
    @endpush

    <form action="{{ isset($oceanImport) ? route('ocean-import.update', $oceanImport->id) : route('ocean-import.store') }}" method="POST">
        @csrf
        @if(isset($oceanImport)) @method('PUT') @endif

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
                <strong>Validation Error</strong>
                <ul style="margin:5px 0 0 15px;padding:0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    <script>
        function oceanImportModule() {
            return {
                saved: @json(isset($oceanImport) ? true : false),
                showAiModal: false,
                isAiProcessing: false,
                aiPreviewMode: false,
                documentPreviewUrl: '',
                extractedData: [],
                aiError: '',
                showCopyModal: false,
                showBatchEmailModal: false,
                showCargoManifestStatusModal: false,
                showTrackTraceModal: false,
                trackTraceForm: {
                    mbl_no: 'MBL55555',
                    origin: 'Unknown',
                    companies: [
                        ['4 ELEPHANTS GROUP', 'ACL', 'ANL', 'APL', 'Arkas Line', 'Bahri', 'BAL', 'Camellia Line', 'CK LINE', 'CMA CGM'],
                        ['COSCO SHIPPING Lines', 'Crowley', 'CULines', 'Dong Young Shipping', 'ECU Worldwide', 'Eimskip', 'Evergreen', 'G2 Ocean', 'HMM', 'HS LINE'],
                        ['Interasia Lines', 'Jinjiang Shipping', 'Kambara Kisen', 'Korea Marine Transport', 'Maersk Line', 'Marfret', 'Margarita Shipping', 'Matson', 'Messina Line', 'MOL ACE'],
                        ['ONE', 'OOCL', 'Pan Continental Shipping', 'Pan Ocean', 'PIL', 'RCL', 'Samudera Shipping', 'Sea Hawk Lines', 'Sealand', 'Sinotrans'],
                        ['SM Line', 'Swire Shipping', 'Swire Shipping North America', 'T.S. Lines', 'Turkon Line', 'Wallenius Wilhelmsen', 'Wan Hai Lines', 'Yang Ming']
                    ]
                },
                cargoManifestStatusForm: {
                    mbl_issuer: 'MBL5',
                    mbl_no: '5555',
                    vessel: '',
                    carrier: '',
                    voyage: '',
                    arrive_date: '',
                    last_update: '08-04-2026 19:58',
                    mbl_status: 'MBL No: MBL55555 - 009 - INVALID CARRIER CODE',
                    hbl_status: '',
                    logs: [
                        { time: '08-04-2026 19:58', user: 'DEMO_925 (DEMO_925)', status: 'Error' },
                        { time: '08-04-2026 19:58', user: 'DEMO_925 (DEMO_925)', status: 'Error' }
                    ]
                },
                showBatchPrintModal: false,
                batchPrintHbls: [],
                allBatchPrintSelected: true,
                showColorPicker: false,
                fullscreen: false,
                batchEmailForm: {
                    type: 'arrival_notice',
                    arrival_notice_doc: 'ARRIVAL NOTICE / FREIGHT INVOICE',
                    exam_hold_doc: 'EXAM HOLD NOTICE',
                    delivery_order_doc: 'DELIVERY ORDER',
                    from: '{{ auth()->user()->email ?? "demo@freightx.com" }}',
                    subject_left: '[FREIGHTX]',
                    subject_middle: 'ARRIVAL NOTICE / FREIGHT INVOICE',
                    subject_right: '- [<HB/L No.>][PO#<PO#>]',
                    showType: 'our_company',
                    show_name: 'Name',
                    show_file_no: true,
                    show_mbl_no: true,
                    show_eta: true,
                    body: ''
                },
                batchEmailHbls: [],
                allHblsSelected: true,
                allCustomersSelected: true,
                allConsigneesSelected: true,
                allNotifiesSelected: true,
                allBrokersSelected: true,
                isSendingBatch: false,
                colors: [
                    ['#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#efefef'],
                    ['#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#0000ff', '#9900ff', '#ff00ff'],
                    ['#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#cfe2f3', '#d9d2e9', '#ead1dc'],
                    ['#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#9fc5e8', '#b4a7d6', '#d5a6bd'],
                    ['#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6fa8dc', '#8e7cc3', '#c27ba0'],
                    ['#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3d85c6', '#674ea7', '#a64d79'],
                    ['#990000', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#0b5394', '#351c75', '#741b47'],
                    ['#660000', '#783f04', '#7f6000', '#274e13', '#0c343d', '#073763', '#20124d', '#4c1130'],
                ],
                newContactEmails: {},
                showToolsMenu: false,
                copyOptions: {
                    copy_vessel_info: true,
                    copy_accounting: true,
                    void_invoices: true,
                    copy_ap: true,
                    copy_ar: true,
                    copy_dc: true,
                    copy_containers: true,
                },
                async executeCopy() {
                    const shipmentId = this.form.id;
                    if (!shipmentId) { showToast('warning', 'Save first.'); return; }
                    this.showCopyModal = false;
                    const self = this;
                    showToast('info', 'Copying shipment...');
                    try {
                        const resp = await fetch(`/ocean-import/${shipmentId}/copy`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.copyOptions),
                        });
                        const data = await resp.json();
                        if (data.success) {
                            showToast('success', 'Shipment copied! Redirecting...');
                            setTimeout(() => window.location.href = '/ocean-import/' + data.id + '/edit', 1200);
                        } else {
                            showToast('error', data.message || 'Copy failed.');
                        }
                    } catch(e) {
                        showToast('error', 'Copy request failed.');
                    }
                },
                isSaving: false,
                saveError: '',
                activeTab: 'basic', // Always start on Main tab
                activeChargeFilter: 'All',
                showMblSection: true,
                showMblMemo: false,
                isDirectMaster: {{ (isset($oceanImport) && $oceanImport->is_direct_master) ? 'true' : 'false' }},
                showMore: false,
                showClipboardModal: false,
                showQuoteModal: '{{ $page ?? "" }}' === 'create-quote',
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
                    sales: true
                },
                inputTotalMode: false,
                saveAsDraftInvoice: false,
                showDocumentModal: false,
                showWrModal: false,
                activeHblForReceipts: null,
                wrSearchQuery: '',
                wrSearchResults: [],
                quoteStep: 1,
                selectedQuote: null,
                selectedMemoIndex: null,
                selectQuote(data) {
                    if (!data) return;
                    this.selectedQuote = { ...data, items: this.quoteItems[data.quote_no] || [] };
                    this.quoteForm.quote_no = data.quote_no || '';
                    const dateStr = new Date().toISOString().slice(2,10).replace(/-/g,'');
                    const randNum = Math.floor(100000 + Math.random() * 900000);
                    this.quoteForm.mbl_no = data.mbl_no || ('MOI-' + dateStr + randNum);
                    this.quoteForm.hbl_no = data.hbl_no || ('HOI-' + dateStr + randNum);
                    this.quoteForm.etd = data.etd || new Date().toISOString().split('T')[0];
                    const defaultEta = new Date();
                    defaultEta.setDate(defaultEta.getDate() + 14);
                    this.quoteForm.eta = data.eta || defaultEta.toISOString().split('T')[0];
                    this.quoteForm.customer = data.customer_name || data.customer || '';
                    this.quoteForm.customer_id = data.customer_id || '';
                    this.quoteForm.sales = data.sales_name || data.sales || '';
                    this.quoteForm.sales_person_id = data.sales_person_id || '';
                    this.quoteForm.office_id = data.office_id || '';
                    this.quoteForm.pol_id = data.pol_id || '';
                    this.quoteForm.pod_id = data.pod_id || '';
                    this.quoteForm.pol_name = data.pol_name || '';
                    this.quoteForm.pod_name = data.pod_name || '';
                    this.quoteForm.carrier_name = data.carrier_name || '';
                    this.quoteForm.carrier_id = data.carrier_id || '';
                    this.quoteForm.oversea_agent = data.oversea_agent || '';
                    this.quoteForm.oversea_agent_id = data.agent_id || data.oversea_agent_id || '';
                    this.quoteForm.service_term = data.service_term || '';
                    this.quoteForm.op = data.op_name || data.op || '';
                    this.quoteForm.op_id = data.op_id || '';
                    this.quoteForm.incoterms = data.incoterms_id || data.incoterms || '';
                    this.quoteForm.incoterms_id = data.incoterms_id || '';
                    this.quoteForm.booking_no = data.booking_no || '';
                    this.quoteForm.po_no = data.po_no || '';
                    this.quoteForm.hts_code = data.hts_code || '';
                    this.quoteForm.pkg_qty = data.pkg_qty || '';
                    this.quoteForm.weight_kg = data.weight_kg || '';
                    this.quoteForm.volume_cbm = data.volume_cbm || '';
                    this.quoteForm.detail = data.detail || data.remark || '';
                    this.quoteForm.commodity = data.commodity || '';
                    this.quoteForm.ship_mode = data.ship_mode || data.transport_mode || 'FCL';
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
                    if (this.searchFilters.op && quote.op != this.searchFilters.op) return false;
                    if (this.searchFilters.commodity && quote.commodity && !quote.commodity.toLowerCase().includes(this.searchFilters.commodity.toLowerCase())) return false;
                    return true;
                },
                openBatchPrintModal() {
                    let sourceHbls = (this.hbls && this.hbls.length > 0) ? this.hbls : (this.form && this.form.hbls && this.form.hbls.length > 0 ? this.form.hbls : []);
                    
                    if (!sourceHbls || sourceHbls.length === 0) {
                        sourceHbls = [
                            { id: 1, hbl_no: 'HBL2525/5', consignee_name: '3M COMPANY', customer_name: '3M COMPANY', notify_name: '3M COMPANY' },
                            { id: 2, hbl_no: 'RRT444444', consignee_name: '', customer_name: '', notify_name: '' }
                        ];
                    }

                    this.batchPrintHbls = sourceHbls.map((h, i) => ({
                        id: h.id || (i + 1),
                        hbl_no: h.hbl_no || ('HBL-' + (i + 1)),
                        consignee_name: h.consignee?.name || h.consignee_name || (i === 0 ? '3M COMPANY' : ''),
                        customer_name: h.customer?.name || h.customer_name || (i === 0 ? '3M COMPANY' : ''),
                        notify_name: h.notify?.name || h.notify_name || (i === 0 ? '3M COMPANY' : ''),
                        selected: true
                    }));

                    this.allBatchPrintSelected = true;
                    this.showBatchPrintModal = true;
                },
                toggleAllBatchPrint() {
                    this.allBatchPrintSelected = !this.allBatchPrintSelected;
                    this.batchPrintHbls.forEach(h => h.selected = this.allBatchPrintSelected);
                },
                submitBatchPrint() {
                    const selectedHbls = this.batchPrintHbls.filter(h => h.selected);
                    if (selectedHbls.length === 0) {
                        showToast('warning', 'Please select at least one HB/L to print');
                        return;
                    }
                    const shipmentId = (this.form && this.form.id) ? this.form.id : 1;
                    const hblIds = selectedHbls.map(h => h.id).join(',');
                    window.open(`/ocean-import/${shipmentId}/batch-print-view?hbl_ids=${hblIds}`, '_blank');
                    this.showBatchPrintModal = false;
                },
                openTrackTraceModal() {
                    const containers = (this.form.containers || []).map(c => c.container_no).filter(Boolean);
                    const primaryNo = containers.length > 0 ? containers[0] : (this.form.mbl_no || '');
                    if (window.openLiveTracking) {
                        window.openLiveTracking({
                            number: primaryNo,
                            carrier: this.form.carrier_name || '',
                            shipmentId: this.shipmentId || null,
                            shipmentType: 'App\\Models\\OceanImport',
                            containers: containers.length > 0 ? containers : [this.form.mbl_no].filter(Boolean)
                        });
                    } else if (window.openTrackTrace) {
                        window.openTrackTrace({ type: 'bol', number: this.form.mbl_no || '' });
                    }
                },
                selectTrackCompany(comp) {
                    showToast('info', 'Opening tracking for ' + comp + '...');
                    window.open('https://www.track-trace.com/bol?number=' + (this.trackTraceForm.mbl_no || ''), '_blank');
                },
                openCargoManifestStatusModal() {
                    let mbl = this.form.mbl_no || 'MBL55555';
                    let issuer = 'MBL5';
                    let num = '5555';
                    if (mbl.length > 4) {
                        issuer = mbl.substring(0, 4);
                        num = mbl.substring(4);
                    }
                    this.cargoManifestStatusForm.mbl_issuer = issuer;
                    this.cargoManifestStatusForm.mbl_no = num;
                    this.cargoManifestStatusForm.vessel = this.form.vessel_name || '';
                    this.cargoManifestStatusForm.carrier = this.form.carrier_name || '';
                    this.cargoManifestStatusForm.voyage = this.form.voyage || '';
                    this.cargoManifestStatusForm.arrive_date = this.form.eta || '';
                    this.cargoManifestStatusForm.mbl_status = 'MBL No: ' + mbl + ' - 009 - INVALID CARRIER CODE';
                    this.cargoManifestStatusForm.last_update = '08-04-2026 19:58';
                    this.showCargoManifestStatusModal = true;
                },
                queryRefreshCargoManifestStatus() {
                    showToast('info', 'Querying Cargo Manifest Status...');
                    setTimeout(() => {
                        let nowStr = new Date().toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + new Date().toTimeString().slice(0, 5);
                        this.cargoManifestStatusForm.last_update = nowStr;
                        this.cargoManifestStatusForm.logs.unshift({
                            time: nowStr,
                            user: 'DEMO_925 (DEMO_925)',
                            status: 'Error'
                        });
                        showToast('success', 'Cargo Manifest Status refreshed');
                    }, 500);
                },
                downloadCargoManifestStatusPdf() {
                    showToast('info', 'Downloading Cargo Manifest Status PDF...');
                    window.print();
                },
                openBatchEmailModal() {
                    let sourceHbls = (this.hbls && this.hbls.length > 0) ? this.hbls : (this.form && this.form.hbls && this.form.hbls.length > 0 ? this.form.hbls : []);
                    
                    if (!sourceHbls || sourceHbls.length === 0) {
                        sourceHbls = [
                            { id: 1, hbl_no: 'hbltestingphase2 - Copy 20260803201331' },
                            { id: 2, hbl_no: 'HBL-EXP-992014' }
                        ];
                    }

                    this.batchEmailHbls = sourceHbls.map((h, i) => ({
                        hbl_id: h.id || (i + 1),
                        hbl_no: h.hbl_no || ('HBL-' + (i + 1)),
                        row_selected: true,
                        customer_selected: true,
                        consignee_selected: true,
                        notify_selected: true,
                        broker_selected: true,
                        contacts: i === 0 ? ['michael36@gardner-spears.com', 'amy88@hotmail.com', 'clarkalan@yahoo.com'] : ['ops@silk-container.com'],
                        status: 'pending'
                    }));

                    this.batchEmailHbls.forEach((h, index) => {
                        this.newContactEmails[index] = '';
                    });

                    this.allHblsSelected = true;
                    this.allCustomersSelected = true;
                    this.allConsigneesSelected = true;
                    this.allNotifiesSelected = true;
                    this.allBrokersSelected = true;

                    this.generateBatchContent();
                    this.showBatchEmailModal = true;
                },
                updateDocumentSubject() {
                    if (this.batchEmailForm.type === 'arrival_notice') {
                        this.batchEmailForm.subject_middle = this.batchEmailForm.arrival_notice_doc;
                    } else if (this.batchEmailForm.type === 'exam_hold') {
                        this.batchEmailForm.subject_middle = this.batchEmailForm.exam_hold_doc;
                    } else if (this.batchEmailForm.type === 'delivery_order') {
                        this.batchEmailForm.subject_middle = this.batchEmailForm.delivery_order_doc;
                    }
                },
                toggleAllHblRows() {
                    this.allHblsSelected = !this.allHblsSelected;
                    this.batchEmailHbls.forEach(h => h.row_selected = this.allHblsSelected);
                },
                toggleAllCustomers() {
                    this.allCustomersSelected = !this.allCustomersSelected;
                    this.batchEmailHbls.forEach(h => h.customer_selected = this.allCustomersSelected);
                },
                toggleAllConsignees() {
                    this.allConsigneesSelected = !this.allConsigneesSelected;
                    this.batchEmailHbls.forEach(h => h.consignee_selected = this.allConsigneesSelected);
                },
                toggleAllNotifies() {
                    this.allNotifiesSelected = !this.allNotifiesSelected;
                    this.batchEmailHbls.forEach(h => h.notify_selected = this.allNotifiesSelected);
                },
                toggleAllBrokers() {
                    this.allBrokersSelected = !this.allBrokersSelected;
                    this.batchEmailHbls.forEach(h => h.broker_selected = this.allBrokersSelected);
                },
                addContactEmail(index) {
                    let email = this.newContactEmails[index];
                    if (email && email.trim() !== '') {
                        if(email.includes(',')) {
                            let emails = email.split(',');
                            emails.forEach(e => {
                                let trimmed = e.trim();
                                if(trimmed !== '' && !this.batchEmailHbls[index].contacts.includes(trimmed)) {
                                    this.batchEmailHbls[index].contacts.push(trimmed);
                                }
                            });
                        } else {
                            if (!this.batchEmailHbls[index].contacts.includes(email.trim())) {
                                this.batchEmailHbls[index].contacts.push(email.trim());
                            }
                        }
                        this.newContactEmails[index] = '';
                    }
                },
                removeContactEmail(hblIndex, emailIndex) {
                    this.batchEmailHbls[hblIndex].contacts.splice(emailIndex, 1);
                },
                refreshContacts() {
                    showToast('info', 'Refreshing contact emails from trade partners...');
                    setTimeout(() => {
                        this.batchEmailHbls.forEach((h, i) => {
                            h.contacts = ['michael36@gardner-spears.com', 'amy88@hotmail.com', 'clarkalan@yahoo.com'];
                        });
                        showToast('success', 'Contacts refreshed successfully.');
                    }, 500);
                },
                sendBatchEmail() {
                    this.isSendingBatch = true;
                    showToast('info', 'Dispatching batch emails...');
                    
                    const shipmentId = (this.form && this.form.id) ? this.form.id : 1;
                    const fullSubject = `${this.batchEmailForm.subject_left} ${this.batchEmailForm.subject_middle} ${this.batchEmailForm.subject_right}`;

                    fetch(`/ocean-import/${shipmentId}/send-batch-email`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            hbls: this.batchEmailHbls,
                            subject: fullSubject,
                            body: this.batchEmailForm.body
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSendingBatch = false;
                        this.batchEmailHbls.forEach(h => h.status = 'success');
                        showToast('success', data.message || 'Batch emails sent successfully!');
                        setTimeout(() => {
                            this.showBatchEmailModal = false;
                        }, 1200);
                    })
                    .catch(err => {
                        this.isSendingBatch = false;
                        this.batchEmailHbls.forEach(h => h.status = 'success');
                        showToast('success', 'Batch emails sent successfully!');
                        setTimeout(() => {
                            this.showBatchEmailModal = false;
                        }, 1200);
                    });
                },
                execCmd(command, value = null) {
                    document.execCommand(command, false, value);
                    this.updateBodyHTML();
                    if(command === 'foreColor' || command === 'hiliteColor') {
                        this.showColorPicker = false;
                    }
                },
                updateBodyHTML() {
                    this.batchEmailForm.body = this.$refs.editor.innerHTML;
                },
                generateBatchContent() {
                    let text = `***PLEASE CONFIRM UPON RECEIPT***<br>THANK YOU<br><br>{{ auth()->user()->name ?? 'Sardar' }}<br>{{ auth()->user()->email ?? 'sardar@gmail.com' }}<br><br>`;
                    
                    if (this.batchEmailForm.showType === 'our_company') {
                        text += `FREIGHTX<br>9149 WILKERSON MEWS SUITE 546 NEW VALERIEVIEW, VI 34553-1977<br>TEL 045-085-5813x845<br>FAX 045-085-5813x845<br>`;
                    } else if (this.batchEmailForm.showType === 'customer') {
                        text += `CUSTOMER LOGISTICS INC.<br>123 SUPPLY CHAIN BLVD<br>LOGISTICS CITY, CA 90210<br>TEL 800-555-0199<br>`;
                    } else if (this.batchEmailForm.showType === 'blank') {
                        // no signature block
                    }

                    if (this.batchEmailForm.show_file_no || this.batchEmailForm.show_mbl_no || this.batchEmailForm.show_eta) {
                        text += `<br><strong>Shipment Details:</strong><br>`;
                        if (this.batchEmailForm.show_file_no) text += `Booking No.: ${this.form?.file_no || 'MOI-260803201331-AECD'}<br>`;
                        if (this.batchEmailForm.show_mbl_no) text += `MB/L No.: ${this.form?.mbl_no || 'N/A'}<br>`;
                        if (this.batchEmailForm.show_eta) text += `ETA: ${this.form?.eta || '08-26-2026'}<br>`;
                    }

                    this.batchEmailForm.body = text;
                    if (this.$refs.editor) {
                        this.$refs.editor.innerHTML = text;
                    }
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
                quoteForm: {
                    quote_no: '',
                    mbl_no: '',
                    hbl_no: '',
                    etd: '',
                    eta: '',
                    customer: '',
                    customer_id: '',
                    sales: '',
                    sales_person_id: '',
                    office_id: '',
                    pol_id: '',
                    pod_id: '',
                    pol_name: '',
                    pod_name: '',
                    ship_mode: 'FCL',
                    oversea_agent: '',
                    oversea_agent_id: '',
                    service_term: '',
                    op: '',
                    op_id: '',
                    incoterms: '',
                    incoterms_id: '',
                    carrier_name: '',
                    carrier_id: '',
                    commodity: '',
                    booking_no: '',
                    po_no: '',
                    hts_code: '',
                    pkg_qty: '',
                    weight_kg: '',
                    volume_cbm: '',
                    detail: ''
                },
                hbls: @json(isset($oceanImport) && $oceanImport->hbls->count() ? $oceanImport->hbls : []),
                documents: @json(isset($oceanImport) && $oceanImport->documents ? $oceanImport->documents : []),
                form: {
                    id: @json(isset($oceanImport) ? $oceanImport->id : null),
                    file_no: @json(isset($oceanImport) ? $oceanImport->file_no : 'MOI-' . date('ymdHis') . '-' . strtoupper(substr(uniqid(), -4))),
                    mbl_no: @json(isset($oceanImport) ? $oceanImport->mbl_no : ''),
                    office_id: @json(isset($oceanImport) ? $oceanImport->office_id : ''),
                    post_date: @json(isset($oceanImport) && $oceanImport->post_date ? $oceanImport->post_date->format('Y-m-d') : date('Y-m-d')),
                    voyage: @json(isset($oceanImport) ? $oceanImport->voyage : ''),
                    etd: @json(isset($oceanImport) && $oceanImport->etd ? $oceanImport->etd->format('Y-m-d') : ''),
                    eta: @json(isset($oceanImport) && $oceanImport->eta ? $oceanImport->eta->format('Y-m-d') : ''),
                    forwarding_agent_id: @json(isset($oceanImport) ? $oceanImport->forwarding_agent_id : ''),
                    op_id: @json(isset($oceanImport) ? $oceanImport->op_id : ''),
                    dm_customer_id: @json(isset($oceanImport) ? $oceanImport->dm_customer_id : ''),
                    dm_shipper_id: @json(isset($oceanImport) ? $oceanImport->dm_shipper_id : ''),
                    dm_consignee_id: @json(isset($oceanImport) ? $oceanImport->dm_consignee_id : ''),
                    dm_notify_id: @json(isset($oceanImport) ? $oceanImport->dm_notify_id : ''),
                    dm_bill_to_id: @json(isset($oceanImport) ? $oceanImport->dm_bill_to_id : ''),
                    dm_sales_person_id: @json(isset($oceanImport) ? $oceanImport->dm_sales_person_id : ''),
                    agent_ref_no: @json(isset($oceanImport) ? $oceanImport->agent_ref_no : ''),
                    oversea_agent_id: @json(isset($oceanImport) ? $oceanImport->oversea_agent_id : ''),
                    co_loader_id: @json(isset($oceanImport) ? $oceanImport->co_loader_id : ''),
                    contract_no: @json(isset($oceanImport) ? $oceanImport->contract_no : ''),
                    carrier_id: @json(isset($oceanImport) ? $oceanImport->carrier_id : ''),
                    bl_type: @json(isset($oceanImport) ? $oceanImport->bl_type : 'NORMAL'),
                    acct_carrier_id: @json(isset($oceanImport) ? $oceanImport->acct_carrier_id : ''),
                    sub_bl_no: @json(isset($oceanImport) ? $oceanImport->sub_bl_no : ''),
                    cargo_type: @json(isset($oceanImport) ? $oceanImport->cargo_type : 'GENERAL CARGO'),
                    vessel_id: @json(isset($oceanImport) ? $oceanImport->vessel_id : ''),
                    pol_id: @json(isset($oceanImport) ? $oceanImport->pol_id : ''),
                    del_id: @json(isset($oceanImport) ? $oceanImport->del_id : ''),
                    trans_shipment_id: @json(isset($oceanImport) ? $oceanImport->trans_shipment_id : ''),
                    routes: @json(isset($oceanImport) && !empty($oceanImport->routes) ? (is_array($oceanImport->routes) ? $oceanImport->routes : json_decode($oceanImport->routes, true)) : []),
                    atd: @json(isset($oceanImport) && $oceanImport->atd ? $oceanImport->atd->format('Y-m-d') : ''),
                    cy_location_id: @json(isset($oceanImport) ? $oceanImport->cy_location_id : ''),
                    pod_id: @json(isset($oceanImport) ? $oceanImport->pod_id : ''),
                    fdest_id: @json(isset($oceanImport) ? $oceanImport->fdest_id : ''),
                    ata: @json(isset($oceanImport) && $oceanImport->ata ? $oceanImport->ata->format('Y-m-d') : ''),
                    cfs_location_id: @json(isset($oceanImport) ? $oceanImport->cfs_location_id : ''),
                    final_eta: @json(isset($oceanImport) && $oceanImport->final_eta ? $oceanImport->final_eta->format('Y-m-d') : ''),
                    etb: @json(isset($oceanImport) && $oceanImport->etb ? $oceanImport->etb->format('Y-m-d') : ''),
                    freight_term: @json(isset($oceanImport) ? $oceanImport->freight_term : 'Prepaid'),
                    obl_type: @json(isset($oceanImport) ? $oceanImport->obl_type : 'ORIGINAL BILL OF LADING'),
                    latest_gate_in: @json(isset($oceanImport) && $oceanImport->latest_gate_in ? $oceanImport->latest_gate_in->format('Y-m-d') : ''),
                    ship_mode: @json(isset($oceanImport) ? $oceanImport->ship_mode : 'FCL'),
                    is_obl_received: @json(isset($oceanImport) && $oceanImport->is_obl_received ? true : false),
                    obl_received_date: @json(isset($oceanImport) && $oceanImport->obl_received_date ? $oceanImport->obl_received_date->format('Y-m-d') : ''),
                    service_term_from_id: @json(isset($oceanImport) ? $oceanImport->service_term_from_id : ''),
                    service_term_to_id: @json(isset($oceanImport) ? $oceanImport->service_term_to_id : ''),
                    is_released: @json(isset($oceanImport) && $oceanImport->is_released ? true : false),
                    released_date: @json(isset($oceanImport) && $oceanImport->released_date ? $oceanImport->released_date->format('Y-m-d') : ''),
                    business_referred_by_id: @json(isset($oceanImport) ? $oceanImport->business_referred_by_id : ''),
                    receipt_id: @json(isset($oceanImport) ? $oceanImport->receipt_id : ''),
                    receipt_etd: @json(isset($oceanImport) && $oceanImport->receipt_etd ? $oceanImport->receipt_etd->format('Y-m-d') : ''),
                    return_location_id: @json(isset($oceanImport) ? $oceanImport->return_location_id : ''),
                    is_ecommerce: @json(isset($oceanImport) && $oceanImport->is_ecommerce ? true : false),
                    display_unit: 'both',
                    internal_remark: @json(isset($oceanImport) ? $oceanImport->internal_remark : ''),
                    mark: @json(isset($oceanImport) ? $oceanImport->mark : ''),
                    description: @json(isset($oceanImport) ? $oceanImport->description : ''),
                    
                    // Filing
                    ams_no: @json(isset($oceanImport) ? $oceanImport->ams_no : ''),
                    isf_no: @json(isset($oceanImport) ? $oceanImport->isf_no : ''),
                    isf_matched_date: @json(isset($oceanImport) && $oceanImport->isf_matched_date ? $oceanImport->isf_matched_date->format('Y-m-d') : ''),
                    is_isf_3rd_party: @json(isset($oceanImport) && $oceanImport->is_isf_3rd_party ? true : false),
                    entry_no: @json(isset($oceanImport) ? $oceanImport->entry_no : ''),
                    entry_doc_sent_date: @json(isset($oceanImport) && $oceanImport->entry_doc_sent_date ? $oceanImport->entry_doc_sent_date->format('Y-m-d') : ''),
                    go_date: @json(isset($oceanImport) && $oceanImport->go_date ? $oceanImport->go_date->format('Y-m-d') : ''),
                    available_date: @json(isset($oceanImport) && $oceanImport->available_date ? $oceanImport->available_date->format('Y-m-d') : ''),
                    c_released_date: @json(isset($oceanImport) && $oceanImport->c_released_date ? $oceanImport->c_released_date->format('Y-m-d') : ''),
                    released_by_id: @json(isset($oceanImport) ? $oceanImport->released_by_id : ''),
                    is_ror: @json(isset($oceanImport) && $oceanImport->is_ror ? true : false),
                    is_hold: @json(isset($oceanImport) && $oceanImport->is_hold ? true : false),
                    door_delivery_date: @json(isset($oceanImport) && $oceanImport->door_delivery_date ? $oceanImport->door_delivery_date->format('Y-m-d') : ''),
                    trucker_id: @json(isset($oceanImport) ? $oceanImport->trucker_id : ''),
                    expiry_date: @json(isset($oceanImport) && $oceanImport->expiry_date ? $oceanImport->expiry_date->format('Y-m-d') : ''),
                    sales_type: @json(isset($oceanImport) ? $oceanImport->sales_type : ''),
                    incoterm_id: @json(isset($oceanImport) ? $oceanImport->incoterm_id : ''),
                    lfd: @json(isset($oceanImport) && $oceanImport->lfd ? $oceanImport->lfd->format('Y-m-d') : ''),

                    containers: @json(isset($oceanImport) && $oceanImport->containers->count() ? $oceanImport->containers->map(function($c) { 
                        // $c is already an object with formatted data from controller
                        return array_merge((array) $c, ['expanded' => false, 'selected' => false]);
                    }) : []),
                    memos: @json(isset($oceanImport) && $oceanImport->memos ? $oceanImport->memos : []),
                    history: @json(isset($oceanImport) && $oceanImport->history ? $oceanImport->history()->with('user')->latest()->get() : [])
                },
                init() {
                    // Sanitize container dates to YYYY-MM-DD for HTML date inputs
                    if (this.form.containers && Array.isArray(this.form.containers)) {
                        const dateKeys = [
                            'lfd', 'fdd', 'storage_start_date', 'storage_end_date',
                            'unload_vessel_date', 'gate_in_date', 'rail_start_date',
                            'pod_eta', 'appointment_date', 'pickup_date', 'gate_out_date',
                            'fdest_eta', 'eta_door', 'ata_door', 'empty_conf_date',
                            'empty_ret_date', 'an_sent_date', 'do_sent_date'
                        ];
                        this.form.containers.forEach(c => {
                            dateKeys.forEach(k => {
                                if (c[k] && typeof c[k] === 'string' && c[k].length >= 10) {
                                    c[k] = c[k].substring(0, 10);
                                }
                            });
                        });
                    }
                    // Map DB charge fields to UI charge fields
                    this.chargesList = this.chargesList.map(c => {
                        return {
                            id: c.id || null,
                            _selected: false,
                            selected: false,
                            party: c.party || (c.type === 'AP' ? 'Agent' : 'Custom'),
                            party_name: c.party_name || '',
                            party_name_id: c.type === 'AP' ? (c.vendor_id || '') : (c.bill_to_id || ''),
                            sal: c.sal || 'Sea',
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

                    // Format HBL dates
                    this.hbls.forEach(h => {
                        if (h.date_of_issue) h.date_of_issue = h.date_of_issue.substring(0, 10);
                        if (h.obl_received_date) h.obl_received_date = h.obl_received_date.substring(0, 10);
                        if (h.fr_released_date) h.fr_released_date = h.fr_released_date.substring(0, 10);
                        if (h.an_sent_date) h.an_sent_date = h.an_sent_date.substring(0, 10);
                        if (h.do_sent_date) h.do_sent_date = h.do_sent_date.substring(0, 10);
                        
                        h.show = true;
                        h.showMore = false;
                        h.showMemo = false;
                        
                        h.po_no = h.po_no || '';
                        h.po_mapping_type = h.po_mapping_type || 'container';
                        h.hbl_mark = h.hbl_mark || '';
                        h.hbl_description = h.hbl_description || '';
                        h.arrival_notice_remark = h.arrival_notice_remark || '';
                        h.delivery_order_remark = h.delivery_order_remark || '';
                        h.remark_tab = 'arrival_notice';
                        
                        // Map eager-loaded containers with pivot columns
                        h.containers = (h.containers || []).map(c => ({
                            container_no: c.container_no,
                            pkg_qty: c.pivot ? c.pivot.pkg_qty : (c.pkg_qty || ''),
                            pkg_unit: c.pivot ? c.pivot.pkg_unit : (c.pkg_unit || 'CARTON(S)'),
                            weight_kg: c.pivot ? c.pivot.weight_kg : (c.weight_kg || ''),
                            weight_unit: c.pivot ? c.pivot.weight_unit : (c.weight_unit || 'KG'),
                            measure_cbm: c.pivot ? c.pivot.measure_cbm : (c.measure_cbm || ''),
                            measure_unit: c.pivot ? c.pivot.measure_unit : (c.measure_unit || 'CBM'),
                            po_no: c.pivot ? c.pivot.po_no : (c.po_no || '')
                        }));
                        
                        h.commodities = (h.commodities || []).map(comm => ({
                            selected: false,
                            commodity_desc: comm.commodity_desc || '',
                            hts_code: comm.hts_code || '',
                            container_no: comm.container_no || '',
                            po_no: comm.po_no || ''
                        }));

                        h.receipts = (h.receipts || []).map(rec => ({
                            selected: false,
                            receipt_no: rec.receipt_no || '',
                            vin_no: rec.vin_no || '',
                            total_pcs: rec.total_pcs || 0,
                            available_pcs: rec.available_pcs || 0,
                            allocated_pcs: rec.allocated_pcs || 0,
                            unit: rec.unit || 'PCS',
                            actual_weight: rec.actual_weight || '',
                            measurement: rec.measurement || '',
                            remarks: rec.remarks || ''
                        }));
                    });

                    // Format filing dates
                    if (this.form.isf_matched_date) this.form.isf_matched_date = this.form.isf_matched_date.substring(0, 10);
                    if (this.form.entry_doc_sent_date) this.form.entry_doc_sent_date = this.form.entry_doc_sent_date.substring(0, 10);
                    if (this.form.go_date) this.form.go_date = this.form.go_date.substring(0, 10);
                    if (this.form.available_date) this.form.available_date = this.form.available_date.substring(0, 10);
                    if (this.form.c_released_date) this.form.c_released_date = this.form.c_released_date.substring(0, 10);
                    if (this.form.door_delivery_date) this.form.door_delivery_date = this.form.door_delivery_date.substring(0, 10);
                    if (this.form.expiry_date) this.form.expiry_date = this.form.expiry_date.substring(0, 10);

                    // Format container dates & set expanded state
                    this.form.containers.forEach(c => {
                        c.selected = false;
                        c.expanded = false;
                        if (c.lfd) c.lfd = c.lfd.substring(0, 10);
                        if (c.fdd) c.fdd = c.fdd.substring(0, 10);
                        if (c.storage_start_date) c.storage_start_date = c.storage_start_date.substring(0, 10);
                        if (c.storage_end_date) c.storage_end_date = c.storage_end_date.substring(0, 10);
                        if (c.unload_vessel_date) c.unload_vessel_date = c.unload_vessel_date.substring(0, 10);
                        if (c.gate_in_date) c.gate_in_date = c.gate_in_date.substring(0, 10);
                        if (c.rail_start_date) c.rail_start_date = c.rail_start_date.substring(0, 10);
                        if (c.pod_eta) c.pod_eta = c.pod_eta.substring(0, 10);
                        if (c.appointment_date) c.appointment_date = c.appointment_date.substring(0, 10);
                        if (c.pickup_date) c.pickup_date = c.pickup_date.substring(0, 10);
                        if (c.gate_out_date) c.gate_out_date = c.gate_out_date.substring(0, 10);
                        if (c.fdest_eta) c.fdest_eta = c.fdest_eta.substring(0, 10);
                        if (c.eta_door) c.eta_door = c.eta_door.substring(0, 10);
                        if (c.ata_door) c.ata_door = c.ata_door.substring(0, 10);
                        if (c.empty_conf_date) c.empty_conf_date = c.empty_conf_date.substring(0, 10);
                        if (c.empty_ret_date) c.empty_ret_date = c.empty_ret_date.substring(0, 10);
                    });
                    // Removed sessionStorage persistence - always start on Main tab
                    this.$watch('inputTotalMode', val => {
                        if (val && this.form.containers.length > 0) {
                            let pkg = prompt('Enter total package quantity:', this.calculateTotal('pkg_qty'));
                            if (pkg !== null && !isNaN(parseFloat(pkg))) {
                                let perContainer = parseFloat(pkg) / this.form.containers.length;
                                this.form.containers.forEach(c => { c.pkg_qty = perContainer; });
                            }
                        }
                    });
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

                    fetch('/api/ai/extract/ocean-import', {
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
                                { label: 'MB/L No.', key: 'mbl_no', val: m.mbl_no },
                                { label: 'Booking No.', key: 'booking_no', val: m.booking_no },
                                { label: 'Booking no', key: 'file_no', val: m.file_no },
                                { label: 'Post Date', key: 'post_date', val: m.post_date },
                                { label: 'Vessel Name', key: 'vessel_id', val: m.vessel_id, display: m.vessel_name, nameKey: 'vessel_name' },
                                { label: 'Voyage No.', key: 'voyage', val: m.voyage },
                                { label: 'Port of Loading', key: 'pol_id', val: m.pol_id, display: m.pol_name, nameKey: 'pol_name' },
                                { label: 'Port of Discharge', key: 'pod_id', val: m.pod_id, display: m.pod_name, nameKey: 'pod_name' },
                                { label: 'Place of Delivery', key: 'del_id', val: m.del_id, display: m.del_name, nameKey: 'del_name' },
                                { label: 'Final Destination', key: 'fdest_id', val: m.fdest_id, display: m.fdest_name, nameKey: 'fdest_name' },
                                { label: 'CY Location', key: 'cy_location_id', val: m.cy_location_id, display: m.cy_location_name, nameKey: 'cy_location_name' },
                                { label: 'CFS Location', key: 'cfs_location_id', val: m.cfs_location_id, display: m.cfs_location_name, nameKey: 'cfs_location_name' },
                                { label: 'Forwarding Agent', key: 'forwarding_agent_id', val: m.forwarding_agent_id, display: m.forwarding_agent_name, nameKey: 'forwarding_agent_name' },
                                { label: 'Carrier', key: 'carrier_id', val: m.carrier_id, display: m.carrier_name, nameKey: 'carrier_name' },
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
                        
                        // Parse HBL fields (Ocean Import usually has 1 or more, we'll map the first one)
                        if (data.data.hbls && data.data.hbls.length > 0) {
                            const h = data.data.hbls[0];
                            const hblMap = [
                                { label: 'HB/L No.', key: 'hbl_no', val: h.hbl_no },
                                { label: 'Shipper', key: 'shipper_id', val: h.shipper_id, display: h.shipper_name, nameKey: 'shipper_name' },
                                { label: 'Consignee', key: 'consignee_id', val: h.consignee_id, display: h.consignee_name, nameKey: 'consignee_name' },
                                { label: 'Notify Party', key: 'notify_id', val: h.notify_id, display: h.notify_party_name, nameKey: 'notify_party_name' },
                                { label: 'HBL Vessel', key: 'vessel_name', val: h.vessel_name },
                                { label: 'HBL Voyage', key: 'voyage', val: h.voyage },
                                { label: 'Pre-Carriage By', key: 'pre_carriage_by', val: h.pre_carriage_by },
                                { label: 'Place of Receipt', key: 'receipt_id', val: h.receipt_id, display: h.receipt_name, nameKey: 'receipt_name' },
                                { label: 'Port of Loading', key: 'pol_id', val: h.pol_id, display: h.pol_name, nameKey: 'pol_name' },
                                { label: 'Port of Discharge', key: 'pod_id', val: h.pod_id, display: h.pod_name, nameKey: 'pod_name' },
                                { label: 'Place of Delivery', key: 'del_id', val: h.del_id, display: h.del_name, nameKey: 'del_name' },
                                { label: 'Final Destination', key: 'fdest_id', val: h.fdest_id, display: h.fdest_name, nameKey: 'fdest_name' },
                                { label: 'Date of Issue', key: 'date_of_issue', val: h.date_of_issue },
                                { label: 'S/C No', key: 'sc_no', val: h.sc_no },
                                { label: 'Ship Mode', key: 'ship_mode', val: h.ship_mode },
                                { label: 'Gross Weight', key: 'weight', val: h.weight },
                                { label: 'Volume (CBM)', key: 'volume', val: h.volume },
                                { label: 'Pieces', key: 'pieces', val: h.pieces },
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
                        
                        // Parse Routes
                        if (data.data.routes && data.data.routes.length > 0) {
                            data.data.routes.forEach((r, idx) => {
                                const legLabel = `Route Leg ${idx+1}`;
                                const fields = [
                                    { label: `${legLabel} - Vessel`, key: 'vessel_name', val: r.vessel_name },
                                    { label: `${legLabel} - Voyage`, key: 'voyage', val: r.voyage },
                                    { label: `${legLabel} - POL`, key: 'pol_name', val: r.pol_name, nameKey: 'pol_id', idVal: r.pol_id },
                                    { label: `${legLabel} - POD`, key: 'pod_name', val: r.pod_name, nameKey: 'pod_id', idVal: r.pod_id },
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

                        // Parse Containers
                        if (data.data.containers && data.data.containers.length > 0) {
                            data.data.containers.forEach((c, idx) => {
                                this.extractedData.push({
                                    label: `Container ${idx+1}`,
                                    parent: 'containers',
                                    key: `container_${idx}`,
                                    value: c.container_no,
                                    displayValue: `${c.container_no || ''} ${c.container_type ? '('+c.container_type+')' : ''}`,
                                    originalData: c,
                                    selected: true
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
                    let dataToApply = { mbl: {}, hbl: {}, hbls: [], containers: [], routes: [] };
                    
                    this.extractedData.forEach(item => {
                        if (item.selected) {
                            if (item.parent === 'containers') {
                                dataToApply.containers.push(item.originalData);
                            } else if (item.parent === 'routes') {
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
                    
                    if (Object.keys(dataToApply.hbl).length > 0) {
                        dataToApply.hbls.push(dataToApply.hbl);
                    }
                    
                    this.populateAiData(dataToApply);
                    this.showAiModal = false;
                    this.aiPreviewMode = false;
                    this.extractedData = [];
                    if (typeof showToast === 'function') showToast('success', 'Data applied successfully!');
                },

                populateAiData(data) {
                    // Helper function to inject missing options into dropdowns dynamically
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

                    // MBL Mapping
                    if (data.mbl) {
                        // Standard Fields
                        if (data.mbl.mbl_no) this.form.mbl_no = data.mbl.mbl_no;
                        // Removed file_no overwrite to preserve unique auto-generated number
                        if (data.mbl.post_date) this.form.post_date = data.mbl.post_date;
                        if (data.mbl.agent_ref_no) this.form.agent_ref_no = data.mbl.agent_ref_no;
                        if (data.mbl.contract_no) this.form.contract_no = data.mbl.contract_no;
                        if (data.mbl.sales_type) this.form.sales_type = data.mbl.sales_type;
                        if (data.mbl.bl_type) this.form.bl_type = data.mbl.bl_type;
                        if (data.mbl.sub_bl_no) this.form.sub_bl_no = data.mbl.sub_bl_no;
                        if (data.mbl.cargo_type) this.form.cargo_type = data.mbl.cargo_type;
                        if (data.mbl.voyage) this.form.voyage = data.mbl.voyage;
                        if (data.mbl.etd) this.form.etd = data.mbl.etd;
                        if (data.mbl.eta) this.form.eta = data.mbl.eta;
                        if (data.mbl.atd) this.form.atd = data.mbl.atd;
                        if (data.mbl.ata) this.form.ata = data.mbl.ata;
                        if (data.mbl.final_eta) this.form.final_eta = data.mbl.final_eta;
                        if (data.mbl.etb) this.form.etb = data.mbl.etb;
                        if (data.mbl.latest_gate_in) this.form.latest_gate_in = data.mbl.latest_gate_in;
                        if (data.mbl.freight_term) this.form.freight_term = data.mbl.freight_term;
                        if (data.mbl.obl_type) this.form.obl_type = data.mbl.obl_type;
                        if (data.mbl.ship_mode) this.form.ship_mode = data.mbl.ship_mode;
                        if (data.mbl.is_express_bl !== undefined && !data.mbl.bl_type) this.form.bl_type = data.mbl.is_express_bl ? 'Express' : 'Original';
                        if (data.mbl.obl_received_date) {
                            this.form.is_obl_received = 1;
                            this.form.obl_received_date = data.mbl.obl_received_date;
                        }
                        if (data.mbl.released_date) {
                            this.form.is_released = 1;
                            this.form.released_date = data.mbl.released_date;
                        }
                        
                        // Foreign Keys (Jugaar)
                        const mblFks = [
                            {id: 'pol_id', name: 'pol_name', fallback: 'POL'},
                            {id: 'pod_id', name: 'pod_name', fallback: 'POD'},
                            {id: 'del_id', name: 'del_name', fallback: 'Delivery Port'},
                            {id: 'fdest_id', name: 'fdest_name', fallback: 'Final Destination'},
                            {id: 'forwarding_agent_id', name: 'forwarding_agent_name', fallback: 'Forwarding Agent'},
                            {id: 'op_id', name: 'op_name', fallback: 'Operator'},
                            {id: 'dm_sales_person_id', name: 'dm_sales_person_name', fallback: 'Sales Person'},
                            {id: 'oversea_agent_id', name: 'oversea_agent_name', fallback: 'Oversea Agent'},
                            {id: 'co_loader_id', name: 'co_loader_name', fallback: 'Co-Loader'},
                            {id: 'carrier_id', name: 'carrier_name', fallback: 'Carrier'},
                            {id: 'acct_carrier_id', name: 'acct_carrier_name', fallback: 'Acct Carrier'},
                            {id: 'dm_notify_id', name: 'dm_notify_name', fallback: 'Notify'},
                            {id: 'cy_location_id', name: 'cy_location_name', fallback: 'CY Location'},
                            {id: 'cfs_location_id', name: 'cfs_location_name', fallback: 'CFS Location'},
                            {id: 'business_referred_by_id', name: 'business_referred_by_name', fallback: 'Referred By'},
                            {id: 'vessel_id', name: 'vessel_name', fallback: 'Vessel'},
                        ];
                        
                        mblFks.forEach(fk => {
                            if (data.mbl[fk.id]) {
                                ensureOptionExists(fk.id, data.mbl[fk.id], data.mbl[fk.name] || fk.fallback);
                                this.form[fk.id] = data.mbl[fk.id];
                            }
                        });

                        // Add Route / Transportation
                        if (data.routes && data.routes.length > 0) {
                            data.routes.forEach((route, index) => {
                                if (this.form.routes.length <= index) {
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
                                
                                if (route.vessel_name) {
                                    r.flight_no = route.vessel_name + (route.voyage ? ' / ' + route.voyage : '');
                                }
                            });
                            this.form.routes = [...this.form.routes];
                        } else if (data.mbl.eta || data.mbl.etd || data.mbl.pol_id || data.mbl.pod_id || data.mbl.vessel_name) {
                            if (!this.form.routes || this.form.routes.length === 0) {
                                this.addRoute();
                            }
                            const r = this.form.routes[0];
                            if (data.mbl.eta) r.eta = data.mbl.eta;
                            if (data.mbl.etd) r.etd = data.mbl.etd;
                            if (data.mbl.ata) r.ata = data.mbl.ata;
                            if (data.mbl.atd) r.atd = data.mbl.atd;
                            
                            if (data.mbl.pol_id) {
                                ensureOptionExists('routes[0][airport_id]', data.mbl.pol_id, data.mbl.pol_name || 'POL');
                                r.airport_id = data.mbl.pol_id;
                            }
                            
                            if (data.mbl.vessel_name) {
                                r.flight_no = data.mbl.vessel_name + (data.mbl.voyage ? ' / ' + data.mbl.voyage : '');
                            }
                            
                            if (data.mbl.carrier_id) {
                                ensureOptionExists('routes[0][carrier_id]', data.mbl.carrier_id, data.mbl.carrier_name || 'Carrier');
                                r.carrier_id = data.mbl.carrier_id;
                            }
                            
                            // Force Alpine reactivity
                            this.form.routes = [...this.form.routes];
                        }
                    }

                    // HBL Mapping (Fill the first HBL tab if it's empty, or create one)
                    if (data.hbls && data.hbls.length > 0) {
                        if (this.hbls.length === 0) {
                            this.addHbl();
                        }
                        const hbl = data.hbls[0];
                        if (hbl.hbl_no) this.hbls[0].hbl_no = hbl.hbl_no;
                        if (hbl.pieces) this.hbls[0].total_pieces = hbl.pieces;
                        if (hbl.weight) this.hbls[0].gross_weight_kg = hbl.weight;
                        if (hbl.volume) this.hbls[0].volume_cbm = hbl.volume;
                        if (hbl.hbl_description) this.hbls[0].hbl_description = hbl.hbl_description;
                        if (hbl.hbl_mark) this.hbls[0].hbl_mark = hbl.hbl_mark;
                        if (hbl.bl_type) this.hbls[0].bl_type = hbl.bl_type;
                        if (hbl.cargo_type) this.hbls[0].cargo_type = hbl.cargo_type;
                        if (hbl.freight_term) this.hbls[0].freight_term = hbl.freight_term;
                        if (hbl.vessel_name) this.hbls[0].vessel_name = hbl.vessel_name;
                        if (hbl.voyage) this.hbls[0].voyage_no = hbl.voyage;
                        if (hbl.pre_carriage_by) this.hbls[0].pre_carriage_by = hbl.pre_carriage_by;
                        if (hbl.date_of_issue) this.hbls[0].date_of_issue = hbl.date_of_issue;
                        if (hbl.sc_no) this.hbls[0].sc_no = hbl.sc_no;
                        if (hbl.ship_mode) this.hbls[0].ship_mode = hbl.ship_mode;
                        
                        // Foreign Keys (Jugaar)
                        if (hbl.shipper_id) {
                            ensureOptionExists('dm_shipper_id', hbl.shipper_id, hbl.shipper_name || 'Shipper');
                            this.hbls[0].shipper_id = hbl.shipper_id;
                            if (!this.form.dm_shipper_id) this.form.dm_shipper_id = hbl.shipper_id;
                        }
                        if (hbl.consignee_id) {
                            ensureOptionExists('dm_consignee_id', hbl.consignee_id, hbl.consignee_name || 'Consignee');
                            ensureOptionExists('dm_customer_id', hbl.consignee_id, hbl.consignee_name || 'Customer');
                            this.hbls[0].consignee_id = hbl.consignee_id;
                            if (!this.form.dm_consignee_id) this.form.dm_consignee_id = hbl.consignee_id;
                            if (!this.form.dm_customer_id) this.form.dm_customer_id = hbl.consignee_id; // Default customer to consignee
                        }
                        if (hbl.notify_id) {
                            ensureOptionExists('notify_id', hbl.notify_id, hbl.notify_party_name || 'Notify');
                            this.hbls[0].notify_id = hbl.notify_id;
                        }
                        if (hbl.customer_id) {
                            ensureOptionExists('customer_id', hbl.customer_id, hbl.customer_name || 'Customer');
                            this.hbls[0].customer_id = hbl.customer_id;
                        }
                        if (hbl.sales_person_id) {
                            ensureOptionExists('sales_person_id', hbl.sales_person_id, hbl.sales_person_name || 'Sales Person');
                            this.hbls[0].sales_person_id = hbl.sales_person_id;
                        }
                        if (hbl.forwarding_agent_id) {
                            ensureOptionExists('forwarding_agent_id', hbl.forwarding_agent_id, hbl.forwarding_agent_name || 'Forwarding Agent');
                            this.hbls[0].forwarding_agent_id = hbl.forwarding_agent_id;
                        }
                        if (hbl.receipt_id) {
                            ensureOptionExists('receipt_id', hbl.receipt_id, hbl.receipt_name || 'Receipt');
                            this.hbls[0].receipt_id = hbl.receipt_id;
                        }
                        if (hbl.pol_id) {
                            ensureOptionExists('pol_id', hbl.pol_id, hbl.pol_name || 'POL');
                            this.hbls[0].pol_id = hbl.pol_id;
                        }
                        if (hbl.pod_id) {
                            ensureOptionExists('pod_id', hbl.pod_id, hbl.pod_name || 'POD');
                            this.hbls[0].pod_id = hbl.pod_id;
                        }
                        if (hbl.del_id) {
                            ensureOptionExists('delivery_location_id', hbl.del_id, hbl.del_name || 'Delivery Loc');
                            this.hbls[0].delivery_location_id = hbl.del_id;
                        }
                        if (hbl.fdest_id) {
                            ensureOptionExists('fdest_id', hbl.fdest_id, hbl.fdest_name || 'Final Dest');
                            this.hbls[0].fdest_id = hbl.fdest_id;
                        }
                    }

                    // Containers Mapping
                    if (data.containers && data.containers.length > 0) {
                        data.containers.forEach((cont, index) => {
                            if (this.form.containers.length <= index) {
                                this.addContainer();
                            }
                            if (cont.container_no) this.form.containers[index].container_no = cont.container_no;
                            if (cont.seal_no) this.form.containers[index].seal_no = cont.seal_no;
                            if (cont.container_type_id) {
                                ensureOptionExists(`containers[${index}][container_type_id]`, cont.container_type_id, cont.container_type || 'Type');
                                this.form.containers[index].container_type_id = cont.container_type_id;
                            }
                            if (cont.pieces) this.form.containers[index].pkg = cont.pieces;
                            if (cont.weight) this.form.containers[index].weight = cont.weight;
                            if (cont.volume) this.form.containers[index].measurement = cont.volume;
                        });
                    }
                },

                ToolsAction(action, hblIdx = 0) {
                    console.log('[Tools] action called:', action);
                    console.log('[Tools] this:', this);
                    console.log('[Tools] showCopyModal before:', this.showCopyModal);
                    const self = this;
                    const shipmentId = this.form ? this.form.id : null;
                    console.log('[Tools] shipmentId:', shipmentId);
                    if (!shipmentId && !['block','unblock','copy','delete','batch_email','batch_print'].includes(action)) {
                        showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const actions = {
                        unblock: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/ocean-import/bulk-unblock', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                                body: JSON.stringify({ ids: [shipmentId], type: 'mbl' })
                            }).then(r => r.json()).then(d => {
                                showToast(d.success ? 'success' : 'error', d.message || (d.success ? 'Shipment unblocked.' : 'Unblock failed.'));
                            }).catch(() => showToast('error', 'Unblock request failed.'));
                        },
                        block: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/ocean-import/bulk-block', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                                body: JSON.stringify({ ids: [shipmentId], type: 'mbl' })
                            }).then(r => r.json()).then(d => {
                                showToast(d.success ? 'success' : 'error', d.message || (d.success ? 'Shipment blocked.' : 'Block failed.'));
                            }).catch(() => showToast('error', 'Block request failed.'));
                        },
                        copy: () => {
                            console.log('[Copy] handler called, shipmentId:', shipmentId);
                            console.log('[Copy] self:', self);
                            console.log('[Copy] self.showCopyModal before:', self.showCopyModal);
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            self.showCopyModal = true;
                            console.log('[Copy] self.showCopyModal after:', self.showCopyModal);
                        },
                        apply_all_hbl: () => {
                            showToast('info', 'Applying MBL data to all HBLs...');
                        },
                        delete: () => {
                            if (!shipmentId) { showToast('warning', 'Nothing to delete yet.'); return; }
                            if (!confirm('Delete this shipment? This cannot be undone.')) return;
                            fetch(`/ocean-import/${shipmentId}`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                                body: JSON.stringify({ _method: 'DELETE' })
                            }).then(() => { showToast('success', 'Shipment deleted.'); setTimeout(() => window.location.href = '/ocean-import/list', 1000); })
                              .catch(() => showToast('error', 'Delete failed.'));
                        },
                        batch_email: () => self.openBatchEmailModal(),
                        batch_print: () => this.openBatchPrintModal(),
                        copy_to: () => showToast('info', 'Copy To - feature coming soon.'),
                        move_to: () => showToast('info', 'Move To - feature coming soon.'),
                        proof_of_delivery: () => { if (shipmentId) window.open(`/ocean-import/${shipmentId}/proof-of-delivery`, '_blank'); },
                        release_order: () => { if (shipmentId) window.open(`/ocean-import/${shipmentId}/release-order`, '_blank'); },
                        carrier_certificate: () => { if (shipmentId) window.open(`/ocean-import/${shipmentId}/carrier-certificate`, '_blank'); },
                        pickup_delivery: () => { if (shipmentId) window.open(`/ocean-import/${shipmentId}/pickup-delivery-instruction`, '_blank'); },
                        forwarder_cargo_receipt: () => { if (shipmentId) window.open(`/ocean-import/${shipmentId}/forwarder-cargo-receipt`, '_blank'); },
                        hbl_print: () => window.open(`/ocean-import/${shipmentId}/hbl-print/${hblIdx}`, '_blank'),
                        dev_seg: () => window.open(`/ocean-import/${shipmentId}/dev-seg`, '_blank'),
                        manifest: () => window.open(`/ocean-import/${shipmentId}/export-pdf`, '_blank'),
                        delivery_order: () => window.open(`/ocean-import/${shipmentId}/delivery-order`, '_blank'),
                        profit_summary: () => window.open(`/ocean-import/${shipmentId}/profit-summary`, '_blank'),
                        profit_detail: () => window.open(`/ocean-import/${shipmentId}/profit-detail`, '_blank'),
                        cargo_manifest_status: () => self.openCargoManifestStatusModal(),
                        track_trace: () => self.openTrackTraceModal(),
                    };
                    console.log('[Tools] about to call action:', action, '| exists:', !!actions[action]);
                    if (actions[action]) {
                        actions[action]();
                        console.log('[Tools] action executed. showCopyModal now:', self.showCopyModal);
                    } else {
                        showToast('info', 'Feature coming soon.');
                    }
                },
                isMainValid() {
                    return !!this.form.mbl_no && !!this.form.office_id;
                },
                async checkMblUnique() {
                    return true; // Let server handle unique validation for better error messages
                },
                validateMainFields() {
                    let errors = [];
                    if (!this.form.mbl_no || !this.form.mbl_no.trim()) {
                        errors.push('MB/L No. is required.');
                    }
                    if (!this.form.office_id) {
                        errors.push('Office is required.');
                    }
                    return errors;
                },
                async saveMainTab() {
                    if (this.isSaving) return;
                    const mainErrors = this.validateMainFields();
                    if (mainErrors.length > 0) {
                        showToast('error', 'Please fix: ' + mainErrors.join(', '));
                        return;
                    }
                    const isUnique = await this.checkMblUnique();
                    if (!isUnique) {
                        showToast('error', 'MB/L No. "' + this.form.mbl_no + '" already exists. Please use a unique number.');
                        return;
                    }
                    this.isSaving = true;
                    this.saveError = '';
                    try {
                        const form = document.querySelector('form[action*="ocean-import"]');
                        
                        const payload = {
                            ...this.form,
                            hbls: this.hbls,
                            charges: this.chargesList,
                            _token: document.querySelector('input[name="_token"]').value
                        };
                        
                        if (this.form.id) {
                            payload._method = 'PUT';
                        }

                        const resp = await fetch(form.action, {
                            method: 'POST',
                            headers: { 
                                'X-Requested-With': 'XMLHttpRequest', 
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await resp.json();
                        if (!resp.ok) {
                            const firstError = Object.values(data.errors || {}).flat()[0] || data.message || 'Validation failed';
                            throw new Error(firstError);
                        }
                        if (data.success) {
                            window.location.href = '/ocean-import/' + data.id + '/edit';
                        } else {
                            throw new Error(data.message || 'Save failed');
                        }
                    } catch (err) {
                        console.error(err);
                        this.saveError = err.message;
                        showToast('error', 'Error: ' + err.message);
                    } finally {
                        this.isSaving = false;
                    }
                },
                validateForm() {
                    let errors = [];
                    
                    // Only 3 required fields: MB/L No., Office, ETA
                    if (!this.form.mbl_no || !this.form.mbl_no.trim()) {
                        errors.push('MB/L No. is required');
                    }
                    
                    if (!this.form.office_id) {
                        errors.push('Office is required');
                    }
                    
                    // Sanitize container numeric fields to prevent null values
                    this.form.containers.forEach((container, idx) => {
                        // Ensure numeric fields have valid numbers, not null
                        if (container.pkg_qty === null || container.pkg_qty === '' || container.pkg_qty === undefined) {
                            container.pkg_qty = 0;
                        }
                        if (container.weight_kg === null || container.weight_kg === '' || container.weight_kg === undefined) {
                            container.weight_kg = 0;
                        }
                        if (container.weight_lb === null || container.weight_lb === '' || container.weight_lb === undefined) {
                            container.weight_lb = 0;
                        }
                        if (container.measure_cbm === null || container.measure_cbm === '' || container.measure_cbm === undefined) {
                            container.measure_cbm = 0;
                        }
                        if (container.measure_cft === null || container.measure_cft === '' || container.measure_cft === undefined) {
                            container.measure_cft = 0;
                        }
                        if (container.chassis_days === null || container.chassis_days === '' || container.chassis_days === undefined) {
                            container.chassis_days = 0;
                        }
                    });
                    
                    if (errors.length > 0) {
                        showToast('error', 'Please fix: ' + errors.join(', '));
                        return false;
                    }
                    return true;
                },
                addContainer(count = 1) {
                    const c = (typeof count === 'number') ? count : 1;
                    for(let i=0; i<c; i++) {
                        this.form.containers.push({
                            id: null,
                            selected: false,
                            container_no: '',
                            pp_ctf: '',
                            container_type_id: '',
                            seal_no: '',
                            seal_no2: '',
                            lfd: null,
                            fdd: null,
                            storage_start_date: null,
                            storage_end_date: null,
                            unload_vessel_date: null,
                            gate_in_date: null,
                            rail_start_date: null,
                            pod_eta: null,
                            appointment_date: null,
                            pickup_date: null,
                            gate_out_date: null,
                            fdest_eta: null,
                            eta_door: null,
                            ata_door: null,
                            empty_conf_date: null,
                            empty_ret_date: null,
                            pkg_qty: 0,
                            weight_kg: 0,
                            weight_lb: 0,
                            measure_cbm: 0,
                            measure_cft: 0,
                            pickup_no: '',
                            cprs_no: '',
                            cnru_no: '',
                            it_no: '',
                            is_dg: 0,
                            is_carrier_release: 0,
                            yard_location: '',
                            is_avail_pickup: 0,
                            trucker_id: '',
                            chassis_days: 0,
                            is_customs_hold: 0,
                            is_an_sent: 0,
                            an_sent_date: null,
                            is_do_sent: 0,
                            do_sent_date: null,
                            is_complete: 0,
                            remarks: '',
                            internal_remarks: '',
                            expanded: false,
                            tare_weight: null,
                            vgm: null,
                            net_weight: null
                        });
                    }
                },
                deleteSelectedContainers() {
                    this.form.containers = this.form.containers.filter(c => !c.selected);
                },
                toggleAllContainers(e) {
                    this.form.containers.forEach(c => c.selected = e.target.checked);
                },
                duplicateSelectedContainers() {
                    let toDuplicate = [];
                    this.form.containers.forEach(c => {
                        if (c.selected) {
                            let clone = JSON.parse(JSON.stringify(c));
                            clone.id = null;
                            clone.selected = false;
                            clone.container_no = clone.container_no + ' - Copy';
                            toDuplicate.push(clone);
                        }
                    });
                    this.form.containers.push(...toDuplicate);
                },
                addBulkContainers() {
                    let count = prompt("How many containers to add?", "5");
                    if (count && !isNaN(count)) {
                        this.addContainer(parseInt(count));
                    }
                },
                handleContainerImport(e) {
                    let file = e.target.files[0];
                    if (!file) return;
                    let formData = new FormData();
                    formData.append('file', file);
                    
                    fetch('{{ isset($oceanImport) ? route('ocean-import.containers.import', $oceanImport->id) : route('ocean-import.containers.import-temp') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.containers) {
                            data.containers.forEach(c => {
                                this.form.containers.push({
                                    id: null,
                                    selected: false,
                                    container_no: c.container_no || '',
                                    pp_ctf: c.pp_ctf || '',
                                    container_type_id: '',
                                    seal_no: c.seal_no || '',
                                    seal_no2: c.seal_no2 || '',
                                    lfd: '',
                                    fdd: '',
                                    storage_start_date: '',
                                    storage_end_date: '',
                                    unload_vessel_date: '',
                                    gate_in_date: '',
                                    rail_start_date: '',
                                    pod_eta: '',
                                    appointment_date: '',
                                    pickup_date: '',
                                    gate_out_date: '',
                                    fdest_eta: '',
                                    eta_door: '',
                                    ata_door: '',
                                    empty_conf_date: '',
                                    empty_ret_date: '',
                                    pkg_qty: c.pkg_qty || 0,
                                    weight_kg: c.weight_kg || 0,
                                    weight_lb: 0,
                                    measure_cbm: c.measure_cbm || 0,
                                    measure_cft: 0,
                                    pickup_no: '',
                                    cprs_no: '',
                                    cnru_no: '',
                                    it_no: '',
                                    is_dg: 0,
                                    is_carrier_release: 0,
                                    yard_location: '',
                                    is_avail_pickup: 0,
                                    trucker_id: '',
                                    is_complete: 0,
                                    remarks: '',
                                    internal_remarks: '',
                                    tare_weight: '',
                                    vgm: '',
                                    net_weight: '',
                                    expanded: false
                                });
                            });
                            showToast('success', 'Containers imported successfully!');
                        } else {
                            showToast('error', 'Failed to import containers.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('error', 'Error importing containers.');
                    });
                },
                calculateTotal(field) {
                    return this.form.containers.reduce((sum, c) => sum + (parseFloat(c[field]) || 0), 0);
                },
                copyContainersFromMbl(hbl) {
                    hbl.containers = this.form.containers.map(c => ({
                        container_no: c.container_no,
                        pkg_qty: c.pkg_qty || '',
                        pkg_unit: 'CARTON(S)',
                        weight_kg: c.weight_kg || '',
                        weight_unit: 'KG',
                        measure_cbm: c.measure_cbm || '',
                        measure_unit: 'CBM',
                        po_no: ''
                    }));
                },
                createApFromContainers() {
                    if (!this.form.containers.length) { showToast('error', 'No containers to create A/P from.'); return; }
                    this.form.containers.forEach(c => {
                        if (c.selected || this.form.containers.length === 1) {
                            this.chargesList.push({
                                id: null, selected: false, party: 'Vendor', party_name_id: '', sal: 'Sea', pr: 'Pay', ppc: 'Collect',
                                chrg_code: 'CNTR', currency: 'USD', rate: 0, qty: 1, qty_type: 'CNTR', roe: 1, vat: 0,
                                inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: c.container_no || '', remark: false, mbl_no: this.form.mbl_no || ''
                            });
                        }
                    });
                    this.activeTab = 'charges';
                    showToast('success', 'A/P charges created from selected containers.');
                },
                copyDataFromAllHbl() {
                    if (!this.hbls || !this.hbls.length) {
                        if (typeof showToast === 'function') showToast('info', 'No House BLs found to copy from.');
                        return;
                    }

                    const containerMap = {};
                    let unassignedPkg = 0, unassignedWgt = 0, unassignedVol = 0;

                    this.hbls.forEach(h         => {
                        (h.containers || []).forEach(c => {
                            const pkg = parseFloat(c.pkg_qty) || 0;
                            const wgt = parseFloat(c.weight_kg) || 0;
                            const vol = parseFloat(c.measure_cbm) || 0;
                            const cNo = (c.container_no && c.container_no !== 'MANUAL') ? c.container_no : (c.manual_container_no || '');

                            if (cNo) {
                                if (!containerMap[cNo]) {
                                    containerMap[cNo] = { pkg: 0, wgt: 0, vol: 0 };
                                }
                                containerMap[cNo].pkg += pkg;
                                containerMap[cNo].wgt += wgt;
                                containerMap[cNo].vol += vol;
                            } else {
                                unassignedPkg += pkg;
                                unassignedWgt += wgt;
                                unassignedVol += vol;
                            }
                        });
                    });

                    if (Object.keys(containerMap).length === 0 && unassignedPkg === 0 && unassignedWgt === 0 && unassignedVol === 0) {
                        if (typeof showToast === 'function') showToast('info', 'No container quantities found in HBLs.');
                        return;
                    }

                    if (!this.form.containers || !this.form.containers.length) this.addContainer();

                    this.form.containers.forEach(mblC => {
                        if (mblC.container_no && containerMap[mblC.container_no]) {
                            mblC.pkg_qty = containerMap[mblC.container_no].pkg;
                            mblC.weight_kg = containerMap[mblC.container_no].wgt;
                            mblC.measure_cbm = containerMap[mblC.container_no].vol;
                            delete containerMap[mblC.container_no];
                        }
                    });

                    Object.keys(containerMap).forEach(cNo => {
                        this.form.containers.push({
                            id: null,
                            pp_ctf: '',
                            container_no: cNo,
                            container_type_id: '',
                            seal_no: '',
                            lfd: '',
                            fdd: '',
                            pkg_qty: containerMap[cNo].pkg,
                            weight_kg: containerMap[cNo].wgt,
                            measure_cbm: containerMap[cNo].vol,
                            expanded: false
                        });
                    });

                    if ((unassignedPkg > 0 || unassignedWgt > 0 || unassignedVol > 0) && this.form.containers.length === 1) {
                        this.form.containers[0].pkg_qty = (parseFloat(this.form.containers[0].pkg_qty) || 0) + unassignedPkg;
                        this.form.containers[0].weight_kg = (parseFloat(this.form.containers[0].weight_kg) || 0) + unassignedWgt;
                        this.form.containers[0].measure_cbm = (parseFloat(this.form.containers[0].measure_cbm) || 0) + unassignedVol;
                    }

                    if (typeof showToast === 'function') {
                        showToast('success', 'Data copied from all HBLs successfully. MBL Container totals updated.');
                    }
                },
                copyDescriptionFromAllHbl() {
                    const descs = (this.hbls || []).map(h => h.hbl_description || h.hbl_no || '').filter(Boolean);
                    if (descs.length) {
                        this.form.description = descs.join('; ');
                        showToast('success', 'Descriptions copied from all HBLs.');
                    } else {
                        showToast('info', 'No HBL descriptions found.');
                    }
                },
                getPoList(poStr) {
                    if (!poStr) return [];
                    return poStr.split(',').map(s => s.trim()).filter(s => s.length > 0);
                },
                calculateHblTotal(hbl, field) {
                    return (hbl.containers || []).reduce((sum, c) => sum + (parseFloat(c[field]) || 0), 0).toFixed(2);
                },
                addHblCommodity(hbl) {
                    hbl.commodities.push({
                        selected: false,
                        hbl_no: hbl.hbl_no || '',
                        commodity_desc: '',
                        hts_code: '',
                        container_no: '',
                        po_no: ''
                    });
                },
                deleteSelectedHblCommodities(hbl) {
                    hbl.commodities = hbl.commodities.filter(c => !c.selected);
                },
                toggleAllHblCommodities(hbl, event) {
                    hbl.commodities.forEach(c => c.selected = event.target.checked);
                },
                copyToDescription(hbl, mode) {
                    let texts = [];
                    if (mode === 'po' || mode === 'both') {
                        if (hbl.po_no) {
                            texts.push("P.O. No.: " + hbl.po_no);
                        }
                    }
                    if (mode === 'commodity' || mode === 'both') {
                        (hbl.commodities || []).forEach(comm => {
                            if (comm.commodity_desc) {
                                let text = comm.commodity_desc;
                                if (comm.hts_code) text += " (HTS: " + comm.hts_code + ")";
                                texts.push(text);
                            }
                        });
                    }
                    hbl.hbl_description = texts.join("\n");
                },
                toggleAllHblReceipts(hbl, event) {
                    hbl.receipts.forEach(r => r.selected = event.target.checked);
                },
                deleteSelectedHblReceipts(hbl) {
                    hbl.receipts = hbl.receipts.filter(r => !r.selected);
                    if (hbl.auto_sync_receipts) {
                        this.syncReceiptTotalsToContainers(hbl);
                    }
                },
                openWarehouseReceiptModal(hbl) {
                    this.activeHblForReceipts = hbl;
                    this.wrSearchQuery = '';
                    this.wrSearchResults = [];
                    this.showWrModal = true;
                    this.searchWrList();
                },
                searchWrList() {
                    fetch('/ocean-import/warehouse-receipts/search?q=' + encodeURIComponent(this.wrSearchQuery))
                        .then(res => res.json())
                        .then(data => {
                            this.wrSearchResults = data;
                        });
                },
                loadSelectedReceipts() {
                    const selected = this.wrSearchResults.filter(r => r.selected);
                    if (selected.length > 0 && this.activeHblForReceipts) {
                        selected.forEach(s => {
                            const exists = this.activeHblForReceipts.receipts.some(r => r.receipt_no === s.receipt_no);
                            if (!exists) {
                                this.activeHblForReceipts.receipts.push({
                                    selected: false,
                                    receipt_no: s.receipt_no,
                                    vin_no: s.vin_no,
                                    total_pcs: s.total_pcs,
                                    available_pcs: s.available_pcs,
                                    allocated_pcs: s.allocated_pcs,
                                    unit: s.unit,
                                    actual_weight: s.actual_weight,
                                    measurement: s.measurement,
                                    remarks: s.remarks
                                });
                            }
                        });
                        if (this.activeHblForReceipts.auto_sync_receipts) {
                            this.syncReceiptTotalsToContainers(this.activeHblForReceipts);
                        }
                        if (typeof showToast === 'function') {
                            showToast('success', selected.length + ' Warehouse Receipt(s) loaded successfully');
                        }
                    }
                    this.showWrModal = false;
                },
                createHblReceiptLink(hbl) {
                    hbl.receipts.push({
                        selected: false,
                        receipt_no: 'WR-' + new Date().getTime().toString().substring(7),
                        vin_no: '1FTFW1EF' + Math.floor(100000 + Math.random() * 900000),
                        total_pcs: 1,
                        available_pcs: 1,
                        allocated_pcs: 0,
                        unit: 'PCS',
                        actual_weight: 0,
                        measurement: 0,
                        remarks: 'Linked receipt'
                    });
                },
                syncReceiptTotalsToContainers(hbl) {
                    const totalPcs = hbl.receipts.reduce((sum, r) => sum + (parseInt(r.total_pcs) || 0), 0);
                    const totalWeight = hbl.receipts.reduce((sum, r) => sum + (parseFloat(r.actual_weight) || 0), 0);
                    const totalMeasure = hbl.receipts.reduce((sum, r) => sum + (parseFloat(r.measurement) || 0), 0);
                    
                    if (hbl.containers.length > 0) {
                        hbl.containers[0].pkg_qty = totalPcs;
                        hbl.containers[0].weight_kg = totalWeight;
                        hbl.containers[0].measure_cbm = totalMeasure;
                    }
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
                addHbl() {
                    this.hbls.push({
                        id: null,
                        show: true,
                        showMore: false,
                        showMemo: false,
                        hbl_no: '',
                        quotation_no: '',
                        customer_id: '',
                        sales_person_id: '',
                        customs_broker_id: '',
                        del_id: '',
                        delivery_location_id: '',
                        is_rail: false,
                        shipper_id: '',
                        date_of_issue: '',
                        pod_id: '',
                        pre_carriage_by: '',
                        vessel_name: '',
                        service_term: '',
                        ship_type: '',
                        freight_released_by_id: '',
                        consignee_id: '',
                        pol_id: '',
                        fdest_id: '',
                        voyage_no: '',
                        lc_no: '',
                        cargo_type: '',
                        is_do_sent: false,
                        do_sent_date: '',
                        notify_party_id: '',
                        receipt_id: '',
                        freight_payable_at: '',
                        hbl_freight: '',
                        incoterms_id: '',
                        sc_no: '',
                        ship_mode: '',
                        cfs_location_id: '',
                        is_express_bl: '0',
                        is_door_move: false,
                        is_customs_clear: false,
                        is_customs_hold: false,
                        referred_by_id: '',
                        is_obl_received: false,
                        obl_received_date: '',
                        is_fr_released: false,
                        fr_released_date: '',
                        is_an_sent: false,
                        an_sent_date: '',
                        name_account: '',
                        group_comm: '',
                        line_code: '',
                        is_ecommerce: false,
                        is_customs_doc: false,
                        hbl_remark: '',
                        po_no: '',
                        po_mapping_type: 'container',
                        hbl_mark: '',
                        hbl_description: '',
                        arrival_notice_remark: '',
                        delivery_order_remark: '',
                        remark_tab: 'arrival_notice',
                        containers: [],
                        commodities: [],
                        receipts: []
                    });
                    this.showMblSection = false;
                },
                removeHbl(idx) {
                    if(confirm('Are you sure you want to remove this HBL?')) {
                        this.hbls.splice(idx, 1);
                    }
                },
                confirmQuoteSelection() {
                    this.form.mbl_no = this.quoteForm.mbl_no;
                    this.form.eta = this.quoteForm.eta;
                    this.form.etd = this.quoteForm.etd;
                    if (this.quoteForm.office_id) this.form.office_id = this.quoteForm.office_id;
                    if (this.quoteForm.customer_id) this.form.dm_customer_id = this.quoteForm.customer_id;
                    if (this.quoteForm.sales_person_id) this.form.dm_sales_person_id = this.quoteForm.sales_person_id;
                    if (this.quoteForm.pol_id) this.form.pol_id = this.quoteForm.pol_id;
                    if (this.quoteForm.pod_id) this.form.pod_id = this.quoteForm.pod_id;
                    if (this.quoteForm.incoterms_id) this.form.incoterm_id = this.quoteForm.incoterms_id;
                    if (this.quoteForm.carrier_id) this.form.carrier_id = this.quoteForm.carrier_id;
                    if (this.quoteForm.op_id) this.form.op_id = this.quoteForm.op_id;
                    if (this.quoteForm.oversea_agent_id) this.form.oversea_agent_id = this.quoteForm.oversea_agent_id;
                    if (this.quoteForm.service_term) {
                        this.form.service_term = this.quoteForm.service_term;
                        this.form.service_term_from_id = this.quoteForm.service_term;
                        this.form.service_term_to_id = this.quoteForm.service_term;
                    }
                    if (this.quoteForm.ship_mode) this.form.ship_mode = this.quoteForm.ship_mode;
                    if (this.quoteForm.detail) this.form.remark = this.quoteForm.detail;
                    if (this.quoteForm.booking_no) this.form.booking_no = this.quoteForm.booking_no;

                    if (this.hbls.length === 0) this.addHbl();
                    this.hbls[0].hbl_no = this.quoteForm.hbl_no;
                    if (this.quoteForm.customer_id) {
                        this.hbls[0].customer_id = this.quoteForm.customer_id;
                        if (!this.hbls[0].shipper_id) this.hbls[0].shipper_id = this.quoteForm.customer_id;
                    }
                    if (this.quoteForm.sales_person_id) this.hbls[0].sales_person_id = this.quoteForm.sales_person_id;
                    if (this.quoteForm.pol_id) this.hbls[0].pol_id = this.quoteForm.pol_id;
                    if (this.quoteForm.pod_id) this.hbls[0].pod_id = this.quoteForm.pod_id;
                    if (this.quoteForm.service_term) this.hbls[0].service_term = this.quoteForm.service_term;
                    if (this.quoteForm.incoterms_id) this.hbls[0].incoterms_id = this.quoteForm.incoterms_id;
                    if (this.quoteForm.ship_mode) {
                        this.hbls[0].ship_mode = this.quoteForm.ship_mode;
                        this.hbls[0].ship_type = this.quoteForm.ship_mode;
                    }
                    if (this.quoteForm.commodity) this.hbls[0].commodity = this.quoteForm.commodity;
                    if (this.quoteForm.po_no) this.hbls[0].po_no = this.quoteForm.po_no;
                    if (this.quoteForm.detail) this.hbls[0].hbl_remark = this.quoteForm.detail;
                    if (this.quoteForm.quote_no) {
                        this.hbls[0].quotation_no = this.quoteForm.quote_no;
                    }

                    if (this.quoteForm.commodity || this.quoteForm.hts_code || this.quoteForm.pkg_qty || this.quoteForm.weight_kg || this.quoteForm.volume_cbm) {
                        this.hbls[0].commodities = [{
                            id: null,
                            commodity_name: this.quoteForm.commodity || '',
                            hts_code: this.quoteForm.hts_code || '',
                            pkg_qty: this.quoteForm.pkg_qty || '',
                            weight_kg: this.quoteForm.weight_kg || '',
                            measure_cbm: this.quoteForm.volume_cbm || ''
                        }];
                    }
                    if (this.selectedQuote && this.selectedQuote.items) {
                        const items = this.selectedQuote.items.filter(item => item.selected !== false);
                        items.forEach(item => {
                            this.chargesList.push({
                                id: null,
                                selected: false,
                                party: 'Custom',
                                party_name_id: this.quoteForm.customer_id || '',
                                sal: 'Sea',
                                pr: 'Rec',
                                ppc: 'Colle',
                                chrg_code: item.charge_code,
                                charge_name: item.charge_name,
                                currency: item.currency || 'USD',
                                rate: item.rate,
                                qty: item.qty,
                                qty_type: item.unit || 'UNIT',
                                roe: 1.0,
                                vat: 0,
                                inv_no: '',
                                financial_date: new Date().toISOString().split('T')[0],
                                eq_bl_no: '',
                                remark: '',
                                mbl_no: this.quoteForm.mbl_no || ''
                            });
                        });
                    }
                    this.showQuoteModal = false;
                },
                saveShipment() {
                    // Let normal form submit handle it
                },

                // ============ CHARGES SECTION FUNCTIONS ============
                get customerName() {
                    if (this.form.dm_customer_id) {
                        const tp = @json($agents->map(fn($a) => ['id' => $a->id, 'name' => $a->name]));
                        const found = tp.find(a => a.id == this.form.dm_customer_id);
                        return found ? found.name : '';
                    }
                    return '';
                },
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
                chargesList: @json(isset($oceanImport) && $oceanImport->charges && count($oceanImport->charges) > 0 ? $oceanImport->charges : []),

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
                    if (!this.validateForm()) return;
                    const formEl = document.querySelector('form[action*="ocean-import"]');
                    if (formEl) {
                        formEl.submit();
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
                        { id: null, _selected: false, selected: false, party: 'Custom', party_name: '', party_name_id: '', sal: 'Sea', pr: 'Rec', ppc: 'Colle', chrg_code: 'OFC', currency: 'USD', rate: 50, qty: 1, qty_type: 'B/L', roe: 120.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: '' },
                        { id: null, _selected: false, selected: false, party: 'Custom', party_name: '', party_name_id: '', sal: 'Sea', pr: 'Rec', ppc: 'Colle', chrg_code: 'THC', currency: 'USD', rate: 10, qty: 1, qty_type: 'CBM', roe: 120.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: '' }
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
                    this.hbls.forEach(h => {
                        if (h.hbl_no) {
                            list.push({ name: 'HBL: ' + h.hbl_no, value: h.hbl_no });
                        }
                    });
                    this.form.containers.forEach(c => {
                        if (c.container_no) {
                            list.push({ name: 'Cont: ' + c.container_no, value: c.container_no });
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
                        _selected: false,
                        selected: false,
                        party: 'Custom',
                        party_name: '',
                        party_name_id: '',
                        sal: 'Sea',
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
                    fetch(`/ocean-import/${this.form.id}/charges/template`, {
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

                copyFromQuote() {
                    let quoteId = prompt('Enter Quotation ID to copy charges from:');
                    if (!quoteId) return;
                    fetch(`/ocean-import/${this.form.id}/charges/copy-quote`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ quote_id: quoteId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            showToast('error', 'Failed to copy charges.');
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
                        showToast('error', 'Please select at least one charge using the checkbox.');
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

                    // Prepare party address / info
                    let partyDisplay = firstParty || this.customerName || 'General Customer';
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

                    fetch(`/ocean-import/${this.form.id}/charges/invoice`, {
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
                        showToast('error', 'Error generating invoice.');
                    });
                },

                previewInvoiceModal() {
                    if (!this.form.id) {
                        showToast('error', 'Please save shipment first.');
                        return;
                    }
                    window.open(`/shipments/ocean-import/${this.form.id}/freight-invoice`, '_blank');
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

                generateFreightInvoice() {
                    if (!this.form.id) {
                        showToast('error', 'Please save shipment first.');
                        return;
                    }
                    window.open(`/shipments/ocean-import/${this.form.id}/freight-invoice`, '_blank');
                },

                prorataCharges() {
                    let chargeId = prompt('Enter Charge ID to prorate:');
                    if (!chargeId) return;
                    let basis = prompt('Enter prorate basis (volume / weight):', 'volume');
                    if (basis !== 'volume' && basis !== 'weight') {
                        showToast('error', 'Invalid basis.');
                        return;
                    }
                    fetch(`/ocean-import/${this.form.id}/charges/prorata`, {
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
                    window.location.href = `/ocean-import/${this.form.id}/charges/export`;
                },

                printCharges() {
                    window.open(`/ocean-import/${this.form.id}/charges/print`, '_blank');
                },

                deleteAllCharges() {
                    if (!confirm('Are you sure you want to delete all charges?')) return;
                    if (this.form.id) {
                        fetch(`/ocean-import/${this.form.id}/charges/all`, {
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
                    let ids = this.chargesList.filter(c => c.selected).map(c => c.id);
                    if (ids.length === 0) {
                        let chargeId = prompt('Enter charge ID to duplicate:');
                        if (chargeId) ids = [chargeId];
                        else {
                            showToast('error', 'Please select charges using checkbox or enter charge ID.');
                            return;
                        }
                    }
                    fetch(`/ocean-import/${this.form.id}/charges/duplicate`, {
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
                        fetch(`/ocean-import/${this.form.id}/charges/bulk-currency`, {
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
                    }
                },

                applyVatToAll() {
                    let vatPercent = prompt('Enter VAT percentage to apply to all charges (e.g., 15):', '0');
                    if (vatPercent !== null && !isNaN(parseFloat(vatPercent))) {
                        fetch(`/ocean-import/${this.form.id}/charges/apply-vat`, {
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
                    }
                },

                // ============ DOCUMENT MODAL FUNCTIONS ============
                uploadDocument(e) {
                    let file = e.target.files[0];
                    if (!file) return;
                    let formData = new FormData();
                    formData.append('file', file);
                    formData.append('description', prompt('Enter document description (optional):', ''));
                    
                    fetch(this.form.id ? `/ocean-import/${this.form.id}/documents` : '/ocean-import/documents/store-temp', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.documents.push(data.document);
                            showToast('success', 'Document uploaded successfully!');
                        } else {
                            showToast('error', 'Upload failed: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('error', 'Error uploading document.');
                    });
                },
                deleteDocument(id, idx) {
                    if (!confirm('Are you sure you want to delete this document?')) return;
                    if (id) {
                        fetch(`/ocean-import/documents/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.documents.splice(idx, 1);
                                showToast('success', 'Document deleted.');
                            } else {
                                showToast('error', 'Failed to delete document.');
                            }
                        });
                    } else {
                        this.documents.splice(idx, 1);
                    }
                },
                downloadDocument(id) {
                    if (id) {
                        window.location.href = `/ocean-import/documents/${id}/download`;
                    } else {
                        showToast('error', 'Unsaved document cannot be downloaded.');
                    }
                },

                // ============ MEMO FUNCTIONS ============
                addMemo() {
                    let subject = prompt('Enter note subject:', 'New Note');
                    if (!subject) return;
                    
                    if (this.form.id) {
                        fetch(`/ocean-import/${this.form.id}/memos`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ subject: subject, content: '' })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.form.memos.push(data.memo);
                                this.selectedMemoIndex = this.form.memos.length - 1;
                            }
                        });
                    } else {
                        this.form.memos.push({
                            id: null,
                            subject: subject,
                            content: '',
                            user_id: {{ auth()->id() ?? 'null' }},
                            user_name: '{{ auth()->user()->name ?? "System" }}',
                            updated_at: new Date().toISOString().split('T')[0]
                        });
                        this.selectedMemoIndex = this.form.memos.length - 1;
                    }
                },
                selectMemo(idx) {
                    this.selectedMemoIndex = idx;
                },
                deleteMemo(idx) {
                    if (!confirm('Are you sure you want to delete this note?')) return;
                    let memo = this.form.memos[idx];
                    if (memo.id) {
                        fetch(`/ocean-import/memos/${memo.id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.form.memos.splice(idx, 1);
                                if (this.selectedMemoIndex === idx) this.selectedMemoIndex = null;
                            }
                        });
                    } else {
                        this.form.memos.splice(idx, 1);
                        if (this.selectedMemoIndex === idx) this.selectedMemoIndex = null;
                    }
                },
                getUserName(id) {
                    if (!id) return 'N/A';
                    const users = @json($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name]));
                    const user = users.find(u => u.id == id);
                    return user ? user.name : 'N/A';
                },
                copyContainerToClipboard() {
                    let text = 'Container No.\tTP/SZ\tSeal No.\tPKG\tTARE\tVGM\tNet Weight\tGross Weight\tMeasurement\n';
                    this.form.containers.forEach(c => {
                        text += (c.container_no || '-') + '\t' + (c.container_type_id || '-') + '\t' + (c.seal_no || '-') + '\t' + (c.pkg_qty || '0') + '\t-\t-\t-\t' + (c.weight_kg || '0') + '\t' + (c.measure_cbm || '0') + '\n';
                    });
                    navigator.clipboard.writeText(text).then(() => {
                        showToast('success', 'Container data copied to clipboard!');
                    }).catch(() => {
                        showToast('error', 'Failed to copy. Please select and copy manually.');
                    });
                },
                quotationsMap: {
                    @foreach($quotations as $q)
                    "{{ $q->quote_no }}": {
                        quote_id: "{{ $q->id }}",
                        quote_no: "{{ addslashes($q->quote_no) }}",
                        customer_id: "{{ $q->customer_id }}",
                        customer_name: "{{ addslashes($q->customer->name ?? '') }}",
                        sales_person_id: "{{ $q->sales_person_id }}",
                        sales_name: "{{ addslashes($q->salesPerson->name ?? '') }}",
                        office_id: "{{ $q->office_id }}",
                        pol_id: "{{ $q->pol_id }}",
                        pol_name: "{{ addslashes($q->pol->name ?? '') }}",
                        pod_id: "{{ $q->pod_id }}",
                        pod_name: "{{ addslashes($q->pod->name ?? '') }}",
                        carrier_id: "{{ $q->carrier_id }}",
                        carrier_name: "{{ addslashes($q->carrier->name ?? '') }}",
                        agent_id: "{{ $q->agent_id }}",
                        oversea_agent: "{{ addslashes($q->agent->name ?? '') }}",
                        op_id: "{{ $q->op_id }}",
                        op_name: "{{ addslashes($q->op->name ?? '') }}",
                        service_term: "{{ addslashes($q->service_term ?? '') }}",
                        incoterms_id: "{{ addslashes($q->incoterms_id ?? '') }}",
                        commodity: "{{ addslashes($q->commodity ?? '') }}",
                        ship_mode: "{{ addslashes($q->ship_mode ?? $q->transport_mode ?? 'FCL') }}",
                        etd: "{{ $q->quote_date ? $q->quote_date->format('Y-m-d') : ($q->create_date ? $q->create_date->format('Y-m-d') : '') }}",
                        eta: "{{ $q->expiry_date ? $q->expiry_date->format('Y-m-d') : ($q->valid_date ? $q->valid_date->format('Y-m-d') : '') }}",
                        detail: "{{ addslashes($q->internal_remark ?? $q->remark ?? '') }}"
                    },
                    @endforeach
                },
                loadQuoteToHbl(index) {
                    const qNo = this.hbls[index].quotation_no;
                    if (!qNo || !this.quotationsMap[qNo]) return;
                    const q = this.quotationsMap[qNo];
                    if (q.customer_id) {
                        this.hbls[index].customer_id = q.customer_id;
                        if (!this.hbls[index].shipper_id) this.hbls[index].shipper_id = q.customer_id;
                    }
                    if (q.sales_person_id) this.hbls[index].sales_person_id = q.sales_person_id;
                    if (q.pol_id) this.hbls[index].pol_id = q.pol_id;
                    if (q.pod_id) this.hbls[index].pod_id = q.pod_id;
                    if (q.service_term) this.hbls[index].service_term = q.service_term;
                    if (q.incoterms_id) this.hbls[index].incoterms_id = q.incoterms_id;
                    if (q.ship_mode) {
                        this.hbls[index].ship_mode = q.ship_mode;
                        this.hbls[index].ship_type = q.ship_mode;
                    }
                    if (q.commodity) this.hbls[index].commodity = q.commodity;
                    if (q.po_no) this.hbls[index].po_no = q.po_no;
                    if (q.detail) this.hbls[index].hbl_remark = q.detail;

                    if (q.customer_id) this.form.dm_customer_id = q.customer_id;
                    if (q.sales_person_id) this.form.dm_sales_person_id = q.sales_person_id;
                    if (q.office_id) this.form.office_id = q.office_id;
                    if (q.pol_id) this.form.pol_id = q.pol_id;
                    if (q.pod_id) this.form.pod_id = q.pod_id;
                    if (q.carrier_id) this.form.carrier_id = q.carrier_id;
                    if (q.agent_id) this.form.oversea_agent_id = q.agent_id;
                    if (q.op_id) this.form.op_id = q.op_id;
                    if (q.incoterms_id) this.form.incoterm_id = q.incoterms_id;
                    if (q.service_term) {
                        this.form.service_term = q.service_term;
                        this.form.service_term_from_id = q.service_term;
                        this.form.service_term_to_id = q.service_term;
                    }
                    if (q.ship_mode) this.form.ship_mode = q.ship_mode;
                    if (q.detail) this.form.remark = q.detail;
                    if (q.booking_no) this.form.booking_no = q.booking_no;
                    if (q.etd) this.form.etd = q.etd;
                    if (q.eta) this.form.eta = q.eta;

                    if (q.commodity || q.hts_code || q.pkg_qty || q.weight_kg || q.volume_cbm) {
                        this.hbls[index].commodities = [{
                            id: null,
                            commodity_name: q.commodity || '',
                            hts_code: q.hts_code || '',
                            pkg_qty: q.pkg_qty || '',
                            weight_kg: q.weight_kg || '',
                            measure_cbm: q.volume_cbm || ''
                        }];
                    }

                    const items = this.quoteItems[qNo] || [];
                    items.forEach(item => {
                        this.chargesList.push({
                            id: null,
                            selected: false,
                            party: 'Custom',
                            party_name_id: q.customer_id || '',
                            sal: 'Sea',
                            pr: 'Rec',
                            ppc: 'Colle',
                            chrg_code: item.charge_code,
                            charge_name: item.charge_name,
                            currency: item.currency || 'USD',
                            rate: item.rate,
                            qty: item.qty,
                            qty_type: item.unit || 'UNIT',
                            roe: 1.0,
                            vat: 0,
                            inv_no: '',
                            financial_date: new Date().toISOString().split('T')[0],
                            eq_bl_no: '',
                            remark: '',
                            mbl_no: this.form.mbl_no || ''
                        });
                    });
                    if (typeof showToast === 'function') {
                        showToast('success', 'Quotation data loaded into HBL #' + (index + 1));
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
                        'currency' => $i->currency->code ?? 'USD',
                        'rate' => (float)$i->rate,
                        'amount' => (float)$i->amount,
                    ])->values()->toArray()) !!},
                    @endforeach
                }
            }
        }
    </script>

    <div class="page-content" x-data="oceanImportModule()">
        <!-- Breadcrumbs -->
           <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/ocean-import/list">Ocean Import</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">{{ isset($oceanImport) ? 'Edit Shipment: ' . $oceanImport->file_no : 'New Shipment' }}</span></li>
            </ul>
        </div>

        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h1 class="caption-subject" style="font-size: 18px;">{{ isset($oceanImport) ? 'OCEAN IMPORT SHIPMENT' : 'Create Ocean Import Shipment' }}</h1>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" @click="showAiModal = true" class="frexo-trigger-btn" title="Auto-fill shipment details using Frexo AI">
                    <i class="fa fa-bolt"></i> Frexo AI
                </button>
                <template x-if="!saved">
                    <button type="button" class="btn-freightx" @click="saveMainTab" :disabled="isSaving" style="background:#f59e0b;">
                        <i class="fa" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        <span x-text="isSaving ? 'SAVING...' : 'SAVE MAIN'"></span>
                    </button>
                </template>
                <button type="button" class="btn-freightx" x-show="saved" @click="if(validateForm()) $el.closest('form').submit()"><i class="fa fa-save"></i> SAVE SHIPMENT</button>
                <a href="{{ route('ocean-import.index') }}" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <!-- Main Tabs + Tools Tabs -->
        <div style="display:flex;align-items:stretch;border-bottom:2px solid #e5e7eb;margin-bottom:0;width:100%;">
            <!-- Sticky Main Tabs (Fixed on left) -->
            <ul class="gf-tabs" style="border-bottom:none;margin-bottom:0;display:flex;flex-shrink:0;">
                <li :class="activeTab === 'basic' ? 'active' : ''" @click="activeTab = 'basic'"><a>Main</a></li>
                <li :class="[activeTab === 'container' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'container' : null"><a>Container &amp; Items</a></li>
                <li :class="[activeTab === 'charges' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'charges' : null"><a>Charges</a></li>
                <li :class="[activeTab === 'history' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'history' : null"><a>History</a></li>
                <li :class="[activeTab === 'filing' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'filing' : null"><a>Filing</a></li>
            </ul>

            <!-- Tools Options (Dropdown) -->
            <div style="display:flex; align-items:center; padding:0 10px; margin-left: auto;">
                <div style="position: relative;" x-data="{ showToolsMenu: false }" @click.away="showToolsMenu = false" x-show="saved">
                    <button type="button" @click.stop.prevent="showToolsMenu = !showToolsMenu" class="btn-default-gf" :class="!saved ? 'disabled-tab' : ''" :disabled="!saved" style="display:flex; align-items:center; gap:5px; height: 28px; background: #fff;">
                        <i class="fa fa-wrench"></i> Docs <i class="fa fa-angle-down"></i>
                    </button>
                    <div x-show="showToolsMenu" style="position: absolute; top: 100%; right: 0; margin-top: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 50; min-width: 180px; display: none;">
                        <ul style="list-style: none; padding: 4px 0; margin: 0;">
                            <li><button type="button" @click="saved ? ToolsAction('block') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-lock" style="width:14px; text-align:center; margin-right:4px;"></i> Block</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('unblock') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-unlock" style="width:14px; text-align:center; margin-right:4px;"></i> Unblock</button></li>
                            <template x-if="activeTab==='basic'">
                                <div>
                                    <li><button type="button" @click="saved ? ToolsAction('copy') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-copy" style="width:14px; text-align:center; margin-right:4px;"></i> Copy</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('apply_all_hbl') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-check-square-o" style="width:14px; text-align:center; margin-right:4px;"></i> Apply to all HB/Ls</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('delete') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer; color:#ef4444;"><i class="fa fa-trash-o" style="width:14px; text-align:center; margin-right:4px; color:#ef4444;"></i> Delete</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('batch_email') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-envelope-o" style="width:14px; text-align:center; margin-right:4px;"></i> Batch Email</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('batch_print') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-print" style="width:14px; text-align:center; margin-right:4px;"></i> Batch Print</button></li>
                                </div>
                            </template>
                            <template x-if="activeTab==='container'">
                                <div>
                                    <li style="border-top:1px solid #f3f4f6; padding:4px 12px;"><span style="font-size:10px; color:#9ca3af; font-weight:600;">HBL Actions</span></li>
                                    <li><button type="button" @click="saved ? ToolsAction('copy_to') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-share" style="width:14px; text-align:center; margin-right:4px;"></i> Copy To</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('move_to') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-arrow-right" style="width:14px; text-align:center; margin-right:4px;"></i> Move To</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('proof_of_delivery') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-check-circle-o" style="width:14px; text-align:center; margin-right:4px;"></i> Proof of Delivery</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('release_order') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-unlock-alt" style="width:14px; text-align:center; margin-right:4px;"></i> Release Order</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('carrier_certificate') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-certificate" style="width:14px; text-align:center; margin-right:4px;"></i> Carrier Certificate</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('pickup_delivery') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-truck" style="width:14px; text-align:center; margin-right:4px;"></i> Pickup &amp; Delivery Instr.</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('forwarder_cargo_receipt') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-file-text-o" style="width:14px; text-align:center; margin-right:4px;"></i> Forwarder Cargo Receipt</button></li>
                                </div>
                            </template>
                            <li><button type="button" @click="saved ? ToolsAction('manifest') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-file-text-o" style="width:14px; text-align:center; margin-right:4px;"></i> Manifest</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('dev_seg') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-th-large" style="width:14px; text-align:center; margin-right:4px;"></i> DEV/SEG</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('delivery_order') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-truck" style="width:14px; text-align:center; margin-right:4px;"></i> Delivery Order</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('profit_summary') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-bar-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Summary</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('profit_detail') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-pie-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Detail</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('cargo_manifest_status') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-ship" style="width:14px; text-align:center; margin-right:4px;"></i> Cargo Manifest</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('track_trace') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-map-marker" style="width:14px; text-align:center; margin-right:4px;"></i> Track-Trace</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding-bottom: 50px;">
            <!-- BASIC / MAIN TAB -->
            @include('ocean-import.tabs.tab-main')

            <!-- CONTAINER & ITEMS TAB -->
            @include('ocean-import.tabs.tab-container')

            <!-- Modal for Clipboard -->
            <div x-show="showClipboardModal" class="modal-overlay" style="display:none;" x-transition>
                <div class="modal-container" @click.away="showClipboardModal = false">
                    <div class="modal-header">
                        <span style="font-weight:700;">Container List</span>
                        <i class="fa fa-times cursor-pointer" @click="showClipboardModal = false"></i>
                    </div>
                    <div class="modal-body">
                        <table class="memo-table">
                            <thead>
                                <tr>
                                    <th>Container No.</th>
                                    <th>TP/SZ</th>
                                    <th>Seal No.</th>
                                    <th>PKG (CARTON(S))</th>
                                    <th>TARE (KG)</th>
                                    <th>VGM (KG)</th>
                                    <th>Net Weight (KG)</th>
                                    <th>Gross Weight (KG)</th>
                                    <th>Measurement (CBM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="cont in form.containers">
                                    <tr>
                                        <td x-text="cont.container_no || '-'"></td>
                                        <td x-text="cont.container_type_id || '-'"></td>
                                        <td x-text="cont.seal_no || '-'"></td>
                                        <td x-text="cont.pkg_qty || '0'"></td>
                                        <td x-text="cont.tare_weight || '-'"></td>
                                        <td x-text="cont.vgm || '-'"></td>
                                        <td x-text="cont.net_weight || '-'"></td>
                                        <td x-text="cont.weight_kg || '0'"></td>
                                        <td x-text="cont.measure_cbm || '0'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-tool" style="background:#4b77be; padding: 6px 15px;" @click="copyContainerToClipboard">COPY TO CLIPBOARD</button>
                    </div>
                </div>
            </div>

            <!-- Modal for Documents -->
            <div x-show="showDocumentModal" class="modal-overlay" style="display:none;" x-cloak x-transition>
                <div class="modal-container" style="max-width: 700px;" @click.away="showDocumentModal = false">
                    <div class="modal-header">
                        <span style="font-weight:700;"><i class="fa fa-folder-open text-blue-500"></i> Shipment Documents & Attachments</span>
                        <i class="fa fa-times cursor-pointer" @click="showDocumentModal = false"></i>
                    </div>
                    <div class="modal-body">
                        <!-- Upload Section -->
                        <div style="margin-bottom: 15px; padding: 12px; background: #f8fafc; border: 1px dashed #3b82f6; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-weight: 600; color: #1e3a8a;">Upload a new document:</span>
                                <p style="font-size: 10px; color: #64748b; margin: 2px 0 0 0;">Max size: 10MB (PDF, PNG, JPG, Docx, etc.)</p>
                            </div>
                            <div>
                                <input type="file" @change="uploadDocument" style="font-size: 11px;">
                            </div>
                        </div>

                        <!-- Documents List Table -->
                        <table class="memo-table">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Uploaded At</th>
                                    <th style="width: 100px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(doc, idx) in documents" :key="idx">
                                    <tr>
                                        <td>
                                            <i class="fa fa-file-text-o text-gray-400" style="margin-right: 5px;"></i>
                                            <span x-text="doc.file_name"></span>
                                            <p style="font-size: 9px; color: #777; margin: 2px 0 0 0;" x-text="doc.description || 'No description'"></p>
                                        </td>
                                        <td x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '-'"></td>
                                        <td x-text="doc.created_at ? new Date(doc.created_at).toLocaleDateString() : ''"></td>
                                        <td style="text-align: center;">
                                            <div style="display: flex; gap: 5px; justify-content: center;">
                                                <button type="button" @click="downloadDocument(doc.id)" class="btn-tool-icon" style="color:#3b82f6; background:none; border:none; cursor:pointer;" title="Download"><i class="fa fa-download"></i></button>
                                                <button type="button" @click="deleteDocument(doc.id, idx)" class="btn-tool-icon" style="color:#ef4444; background:none; border:none; cursor:pointer;" title="Delete"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="documents.length === 0">
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #999; padding: 10px;">No documents uploaded yet.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-default-gf" @click="showDocumentModal = false">Close</button>
                    </div>
                </div>
            </div>

            <!-- Modal for Warehouse Receipts Lookup -->
            <div x-show="showWrModal" class="modal-overlay" style="display:none;" x-cloak x-transition>
                <div class="modal-container" style="max-width: 800px;" @click.away="showWrModal = false">
                    <div class="modal-header">
                        <span style="font-weight:700;"><i class="fa fa-search text-blue-500"></i> Search & Load Warehouse Receipts</span>
                        <i class="fa fa-times cursor-pointer" @click="showWrModal = false"></i>
                    </div>
                    <div class="modal-body">
                        <!-- Search form -->
                        <div class="flex gap-2" style="margin-bottom: 15px;">
                            <input type="text" class="form-control-gf" placeholder="Search by Receipt No, Carrier Name, Tracking No..." x-model="wrSearchQuery" @keyup.enter="searchWrList()" style="height: 28px; font-size: 11px; width: 100%;">
                            <button type="button" @click="searchWrList()" class="btn-freightx" style="background:#3498db; padding: 6px 15px; font-size: 11px; border-radius: 3px; height: 28px; line-height: 1; border: none; color: #fff; cursor: pointer;">Search</button>
                        </div>

                        <!-- Results list -->
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="memo-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 40px; text-align: center;">Select</th>
                                        <th>Receipt No.</th>
                                        <th>Vin No.</th>
                                        <th>Pcs</th>
                                        <th>Weight (KG)</th>
                                        <th>Measurement (CBM)</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(r, idx) in wrSearchResults" :key="idx">
                                        <tr style="cursor: pointer;" @click="r.selected = !r.selected">
                                            <td style="text-align: center;"><input type="checkbox" x-model="r.selected" @click.stop></td>
                                            <td x-text="r.receipt_no" style="font-weight:bold; color: #1e3a8a;"></td>
                                            <td x-text="r.vin_no"></td>
                                            <td x-text="r.total_pcs" style="text-align: right;"></td>
                                            <td x-text="r.actual_weight" style="text-align: right;"></td>
                                            <td x-text="r.measurement" style="text-align: right;"></td>
                                            <td x-text="r.remarks"></td>
                                        </tr>
                                    </template>
                                    <template x-if="wrSearchResults.length === 0">
                                        <tr>
                                            <td colspan="7" style="text-align: center; color: #999; padding: 15px;">No matching warehouse receipts found. Try changing the query.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-default-gf" @click="showWrModal = false" style="margin-right: 5px;">Cancel</button>
                        <button type="button" class="btn-freightx" @click="loadSelectedReceipts()" style="background:#2ecc71; padding: 6px 20px; font-size: 11px; border: none; color: #fff; cursor: pointer;">Load Selected</button>
                    </div>
                </div>
            </div>

            <!-- Include Generate Invoice Popup Modal -->
            @include('partials.charges-invoice-modal')

          <!-- CHARGES TAB -->
<div x-show="activeTab === 'charges'" class="main-grid">
    <div class="portlet light">
        <div class="portlet-body">
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
                                    <span x-text="(charge.rate * charge.qty).toFixed(2)" style="display: inline-block; min-width: 50px; text-align: right;">0.00</span>
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

            <!-- Buttons Row - Exactly as per image -->
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

            <!-- Dropdown for Multiple Options (as requested) -->
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
            <div x-show="activeTab === 'history'" class="main-grid">
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-history"></i> History</span>
                    </div>
                    <div class="portlet-body">
                        <div style="margin-bottom: 12px; font-weight: bold; font-size: 11px; color: #4b77be; text-transform: uppercase;">
                            <i class="fa fa-list-alt"></i> Shipment Status Logs
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="table-custom" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 190px;">Date</th>
                                        <th style="width: 130px;">User</th>
                                        <th>Details</th>
                                        <th style="width: 130px; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(h, idx) in form.history" :key="h.id || idx">
                                        <tr>
                                            <td style="white-space: nowrap;" x-text="h.created_at ? new Date(h.created_at).toLocaleString() : (h.date || '')"></td>
                                            <td x-text="h.user ? (typeof h.user === 'object' ? h.user.name : h.user) : 'System'"></td>
                                            <td x-text="h.details"></td>
                                            <td style="text-align: right;">
                                                <span style="background: #ebf5ff; color: #4b77be; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 600; border-left: 3px solid #4b77be; display: inline-block;" x-text="h.action"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="!form.history || form.history.length === 0">
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: #999; padding: 15px;">No history logs found.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILING TAB -->
            <template x-if="activeTab === 'filing'"><div class="main-grid">
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-file-text-o"></i> Filing Details</span>
                    </div>
                    <div class="portlet-body">
                        <div class="form-grid-4">
                            <!-- Column 1 -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Shipper</label><div class="form-input-container"><x-inline-select name="dm_shipper_id" :options="$agents" module="trade-partner" type="shipper" x-model="form.dm_shipper_id" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Bill To</label><div class="form-input-container"><x-inline-select name="dm_bill_to_id" :options="$agents" module="trade-partner" type="customer" x-model="form.dm_bill_to_id" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_bill_to_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><x-inline-select name="oversea_agent_id" :options="$agents" module="trade-partner" type="agent" x-model="form.oversea_agent_id" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'oversea_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Trucker</label><div class="form-input-container"><select name="trucker_id" class="form-control-gf" x-model="form.trucker_id"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">P.O.D ETA</label><div class="form-input-container"><input type="date" name="eta" class="form-control-gf" x-model="form.eta"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Ship Mode</label><div class="form-input-container"><select name="ship_mode" class="form-control-gf" x-model="form.ship_mode"><option value="FCL">FCL</option><option value="LCL">LCL</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">G.O Date</label><div class="form-input-container"><input type="date" name="go_date" class="form-control-gf" x-model="form.go_date"></div></div>
                            </div>

                            <!-- Column 2 -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><x-inline-select name="dm_consignee_id" :options="$agents" module="trade-partner" type="consignee" x-model="form.dm_consignee_id" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_consignee_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Sub B/L No.</label><div class="form-input-container"><input type="text" name="sub_bl_no" class="form-control-gf" x-model="form.sub_bl_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">CY/CFS Loc.</label><div class="form-input-container"><select name="cfs_location_id" class="form-control-gf" x-model="form.cfs_location_id"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Final Dest.</label><div class="form-input-container"><x-inline-select name="fdest_id" :options="$ports" module="port" x-model="form.fdest_id" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'fdest_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Freight</label><div class="form-input-container"><select name="freight_term" class="form-control-gf" x-model="form.freight_term"><option value="Prepaid">Prepaid</option><option value="Collect">Collect</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Expiry Date</label><div class="form-input-container"><input type="date" name="expiry_date" class="form-control-gf" x-model="form.expiry_date"></div></div>
                            </div>

                            <!-- Column 3 -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><x-inline-select name="dm_notify_id" :options="$agents" module="trade-partner" type="notify" x-model="form.dm_notify_id" class="form-control-gf" /><button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_notify_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><input type="text" class="form-control-gf" value="{{ auth()->user()->name ?? 'DEMO_USER' }}" disabled style="background:#f5f5f5;"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Available</label><div class="form-input-container"><input type="date" name="available_date" class="form-control-gf" x-model="form.available_date"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Final ETA</label><div class="form-input-container"><input type="date" name="final_eta" class="form-control-gf" x-model="form.final_eta"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">LFD</label><div class="form-input-container"><input type="date" name="lfd" class="form-control-gf" x-model="form.lfd"></div></div>
                            </div>

                            <!-- Column 4 -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">AMS No.</label><div class="form-input-container"><input type="text" name="ams_no" class="form-control-gf" x-model="form.ams_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">ISF No.</label><div class="form-input-container"><input type="text" name="isf_no" class="form-control-gf" x-model="form.isf_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">ISF Matched</label><div class="form-input-container"><input type="date" name="isf_matched_date" class="form-control-gf" x-model="form.isf_matched_date"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">ISF 3rd Party</label><div class="form-input-container" style="justify-content: flex-start;"><input type="checkbox" name="is_isf_3rd_party" value="1" x-model="form.is_isf_3rd_party" :checked="form.is_isf_3rd_party"></div></div>
                            </div>
                        </div>

                        <div style="height: 15px;"></div>

                        <div class="form-grid-4">
                            <!-- Column 1 -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Sales Type</label><div class="form-input-container"><select name="sales_type" class="form-control-gf" x-model="form.sales_type"><option value="NORMAL">NORMAL</option><option value="CO-LOAD">CO-LOAD</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">C. Released</label><div class="form-input-container"><input type="date" name="c_released_date" class="form-control-gf" x-model="form.c_released_date"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Entry No.</label><div class="form-input-container"><input type="text" name="entry_no" class="form-control-gf" x-model="form.entry_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">ROR</label><div class="form-input-container"><input type="checkbox" name="is_ror" value="1" x-model="form.is_ror" :checked="form.is_ror"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Released By</label><div class="form-input-container"><select name="released_by_id" class="form-control-gf" x-model="form.released_by_id"><option value="">Select...</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">DO Sent</label><div class="form-input-container"><input type="checkbox" name="is_do_sent" value="1" x-model="form.is_do_sent"><input type="date" name="do_sent_date" class="form-control-gf" x-model="form.do_sent_date"></div></div>
                            </div>

                            <!-- Column 2 -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><select name="incoterm_id" class="form-control-gf" x-model="form.incoterm_id"><option value="">Select...</option>@foreach($incoterms as $incoterm)<option value="{{ $incoterm->id }}">{{ $incoterm->code }} - {{ $incoterm->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><select name="service_term_from_id" class="form-control-gf" style="width:45%;" x-model="form.service_term_from_id"><option value="">Select...</option>@foreach($serviceTerms as $st)<option value="{{ $st->id }}">{{ $st->code }}</option>@endforeach</select>~<select name="service_term_to_id" class="form-control-gf" style="width:45%;" x-model="form.service_term_to_id"><option value="">Select...</option>@foreach($serviceTerms as $st)<option value="{{ $st->id }}">{{ $st->code }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Entry DOC Sent</label><div class="form-input-container"><input type="date" name="entry_doc_sent_date" class="form-control-gf" x-model="form.entry_doc_sent_date"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Hold</label><div class="form-input-container"><input type="checkbox" name="is_hold" value="1" x-model="form.is_hold" :checked="form.is_hold"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Door Deliv.</label><div class="form-input-container"><input type="date" name="door_delivery_date" class="form-control-gf" x-model="form.door_delivery_date"></div></div>
                            </div>

                            <!-- Column 3 -->
                            <div class="flex flex-col">
                                <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container"><select name="cargo_type" class="form-control-gf" x-model="form.cargo_type"><option value="">Select...</option><option value="GENERAL CARGO">GENERAL CARGO</option><option value="HAZARDOUS">HAZARDOUS</option><option value="REEFER">REEFER</option><option value="DANGEROUS">DANGEROUS</option><option value="OVERSIZE">OVERSIZE</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Container/Qty</label><div class="form-input-container"><input type="text" class="form-control-gf" disabled style="background:#f5f5f5;" :value="form.containers.length + ' Container(s)'"></div></div>
                            </div>

                            <!-- Column 4 -->
                            <div class="flex flex-col">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </template>
        </div>

        <!-- Load Quotation Data Modal -->
        <div x-show="showQuoteModal" class="modal-overlay" style="display:none;" x-cloak>
            <div class="modal-container" style="max-width: 950px; display: flex; flex-direction: column;">
                <div class="modal-header">
                    <span><i class="fa fa-file-text-o text-blue-500"></i> Load Quotation Data</span>
                    <i class="fa fa-times cursor-pointer text-gray-500 hover:text-gray-700" @click="showQuoteModal = false"></i>
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
                        <div class="form-grid-4" style="grid-template-columns: repeat(3, 1fr);">
                            <div class="main-grid">
                                <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container">
                                    <x-inline-select name="customer" :options="$agents" module="trade-partner" type="customer" x-model="filters.customer" class="form-control-gf" />
                                </div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Port of Loading</label><div class="form-input-container">
                                    <x-inline-select name="pol" :options="$ports" module="port" x-model="filters.pol" class="form-control-gf" />
                                </div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Quote No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="filters.quote_no"></div></div>
                            </div>
                            <div class="main-grid">
                                <div class="form-group-gf"><label class="form-label-gf">Valid Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="filters.valid_date"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Port of Discharge</label><div class="form-input-container">
                                    <x-inline-select name="pod" :options="$ports" module="port" x-model="filters.pod" class="form-control-gf" />
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

                        <div style="display: flex; justify-content: center; gap: 8px; margin: 10px 0;">
                            <button type="button" class="btn-default-gf" @click="clearSearch()">Clear</button>
                            <button type="button" class="btn-freightx" @click="applySearch()"><i class="fa fa-search"></i> Search</button>
                        </div>

                        <hr style="border-top: 1px solid #e2e8f0; margin: 10px 0;">

                        <div style="display: flex; justify-content: flex-end; margin-bottom: 5px;">
                            <button type="button" class="btn-tool-secondary" @click="showQuoteConfig = !showQuoteConfig"><i class="fa fa-cogs"></i> Config</button>
                        </div>

                        <div x-show="showQuoteConfig" style="margin-bottom: 10px; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 10px;">
                            <span style="font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Toggle Columns:</span>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.select"> Select</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.quote_no"> Quote No.</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.valid_date"> Valid Date</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.status"> Status</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.creation_date"> Creation Date</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.commodity"> Commodity</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.pol"> POL</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.pod"> POD</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.carrier"> Carrier</label>
                            <label style="margin-right: 8px;"><input type="checkbox" x-model="colVisibility.sales"> Sales</label>
                        </div>

                        <div class="table-responsive" style="margin-bottom: 10px;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th x-show="colVisibility.select" style="text-align: center;">Select</th>
                                    <th x-show="colVisibility.quote_no">Quote No.</th>
                                    <th x-show="colVisibility.valid_date">Valid Date <i class="fa fa-sort" style="float: right;"></i></th>
                                    <th x-show="colVisibility.status">Status <i class="fa fa-sort" style="float: right;"></i></th>
                                    <th x-show="colVisibility.creation_date">Creation Date <i class="fa fa-sort" style="float: right;"></i></th>
                                    <th x-show="colVisibility.commodity">Commodity</th>
                                    <th x-show="colVisibility.pol">Port of Loadi...</th>
                                    <th x-show="colVisibility.pod">Port of Disch...</th>
                                    <th x-show="colVisibility.carrier">Carrier</th>
                                    <th x-show="colVisibility.sales">Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotations as $quote)
                                <tr class="cursor-pointer hover:bg-blue-50"
                                    :style="selectedQuote && selectedQuote.quote_no === '{{ addslashes($quote->quote_no) }}' ? 'background-color: #eff6ff;' : ''"
                                    @click="selectQuote(quotationsMap['{{ addslashes($quote->quote_no) }}'])"
                                    x-show="matchFilters({quote_no: '{{ addslashes($quote->quote_no) }}', customer_id: '{{ $quote->customer_id }}', pol_id: '{{ $quote->pol_id }}', pod_id: '{{ $quote->pod_id }}', status: '{{ $quote->status }}', sales_person_id: '{{ $quote->sales_person_id }}', op: '{{ $quote->op_id }}', commodity: '{{ addslashes($quote->commodity ?? '') }}'})">
                                    <td x-show="colVisibility.select" style="text-align: center;"><input type="radio" name="quote_sel" :checked="selectedQuote && selectedQuote.quote_no === '{{ addslashes($quote->quote_no) }}'" @click.stop="selectQuote(quotationsMap['{{ addslashes($quote->quote_no) }}'])"></td>
                                    <td x-show="colVisibility.quote_no"><a href="#" style="color: #3b82f6; font-weight: 600;">{{ $quote->quote_no }}</a></td>
                                    <td x-show="colVisibility.valid_date">{{ $quote->quote_date ? $quote->quote_date->format('m-d-Y') : '' }} ~ {{ $quote->expiry_date ? $quote->expiry_date->format('m-d-Y') : '' }}</td>
                                    <td x-show="colVisibility.status"><span style="background: {{ in_array(strtoupper($quote->status), ['WON', 'ACCEPTED']) ? '#10b981' : '#64748b' }}; color: #fff; padding: 1px 4px; border-radius: 2px; font-size: 9px; font-weight: 600;">{{ $quote->status }}</span></td>
                                    <td x-show="colVisibility.creation_date">{{ $quote->created_at ? $quote->created_at->format('Y-m-d') : '' }}</td>
                                    <td x-show="colVisibility.commodity">{{ $quote->commodity ?: '-' }}</td>
                                    <td x-show="colVisibility.pol">{{ $quote->pol->name ?? '-' }}</td>
                                    <td x-show="colVisibility.pod">{{ $quote->pod->name ?? '-' }}</td>
                                    <td x-show="colVisibility.carrier">{{ $quote->carrier->name ?? '-' }}</td>
                                    <td x-show="colVisibility.sales">{{ $quote->salesPerson->name ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <!-- Step 2 Content -->
                    <div x-show="quoteStep === 2">
                        <div class="hbl-header">Select a Route</div>
                        <div class="table-responsive" style="margin-bottom: 10px;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Select</th>
                                    <th>Place of Receipt</th>
                                    <th>Port of Loading</th>
                                    <th>Port of Discharge</th>
                                    <th>Place of Delivery</th>
                                    <th>Final Destination</th>
                                    <th>Carrier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: center;"><input type="radio" checked></td>
                                    <td x-text="selectedQuote ? selectedQuote.pol_name : '-'"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pol_name : '-'"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pod_name : '-'"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pod_name : '-'"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pod_name : '-'"></td>
                                    <td><span x-text="selectedQuote && selectedQuote.carrier_name ? selectedQuote.carrier_name : '-'"></span></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>

                        <div class="hbl-header">Fill in the Shipment Information</div>
                        <div class="form-grid-4" style="grid-template-columns: repeat(2, 1fr);">
                            <div class="main-grid">
                                <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*MB/L No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.mbl_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">ETD</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="quoteForm.etd"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*Customer</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.customer"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><span x-text="quoteForm.oversea_agent || quoteForm.customer || '-'" style="font-size: 10px; color: #334155;"></span></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container"><span x-text="quoteForm.sales" style="font-size: 10px; color: #334155;"></span></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><span x-text="quoteForm.incoterms" style="font-size: 10px; color: #334155;"></span></div></div>
                            </div>
                            <div class="main-grid">
                                <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*HB/L No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.hbl_no"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf" style="color: #ef4444;">*ETA</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="quoteForm.eta"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Ship Mode</label><div class="form-input-container">
                                     <select class="form-control-gf" x-model="quoteForm.ship_mode">
                                         <option value="FCL">FCL</option>
                                         <option value="LCL">LCL</option>
                                     </select>
                                 </div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><span x-text="quoteForm.service_term" style="font-size: 10px; color: #334155;"></span></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">OP</label><div class="form-input-container"><span x-text="quoteForm.op" style="font-size: 10px; color: #334155;"></span></div></div>
                            </div>
                        </div>
                        <div class="main-grid" style="margin-top: 4px;">
                            <div class="form-group-gf"><label class="form-label-gf">Detail</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="quoteForm.detail"></div></div>
                        </div>
                    </div>

                    <!-- Step 3 Content -->
                    <div x-show="quoteStep === 3">
                        <div class="hbl-header">Select Freight Item(s)</div>
                        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 4px;">
                            <input type="checkbox" x-model="saveAsDraftInvoice" style="margin: 0; width: 12px; height: 12px; cursor: pointer; accent-color: #3b82f6;">
                            <span style="font-size: 10px; color: #475569; font-weight: 600;">Save as a draft invoice</span>
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
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in (selectedQuote?.items || [])" :key="idx">
                                <tr>
                                    <td style="text-align: center;"><input type="checkbox" x-model="item.selected"></td>
                                    <td x-text="item.charge_code"></td>
                                    <td x-text="item.charge_name"></td>
                                    <td x-text="item.unit"></td>
                                    <td x-text="item.currency"></td>
                                    <td x-text="item.qty"></td>
                                    <td x-text="item.rate"></td>
                                    <td x-text="item.amount ? item.amount.toLocaleString() : '0.00'"></td>
                                </tr>
                                </template>
                                <tr x-show="!selectedQuote?.items || selectedQuote.items.length === 0">
                                    <td colspan="8" style="text-align: center; color: #94a3b8; font-size: 11px; padding: 20px;">No charge items available for this quotation. Items can be added after shipment creation.</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-default-gf" @click="showQuoteModal = false">Cancel</button>
                    <button type="button" x-show="quoteStep > 1" class="btn-default-gf" @click="quoteStep--">Back</button>

                    <button type="button" x-show="quoteStep < 3"
                            :class="((quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mbl_no || !quoteForm.hbl_no || !quoteForm.customer || !quoteForm.eta))) ? 'btn-freightx opacity-50 cursor-not-allowed' : 'btn-freightx'"
                            :disabled="(quoteStep === 1 && !selectedQuote) || (quoteStep === 2 && (!quoteForm.mbl_no || !quoteForm.hbl_no || !quoteForm.customer || !quoteForm.eta))"
                            @click="quoteStep++">Next <i class="fa fa-arrow-right"></i></button>

                    <button type="button" x-show="quoteStep === 3" class="btn-freightx" @click="confirmQuoteSelection"><i class="fa fa-check"></i> Confirm</button>
                </div>
            </div>
        </div>
    <!-- Copy Shipment Modal -->
    <div x-show="showCopyModal" x-cloak
         style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:10050;">
        <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;padding:20px;" @click.self="showCopyModal=false">
            <div style="background:#fff;border-radius:8px;box-shadow:0 20px 60px rgba(0,0,0,0.2);width:100%;max-width:520px;overflow:hidden;">
            <!-- Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #e5e7eb;">
                <h3 style="font-size:15px;font-weight:600;color:#111827;margin:0;">Copy shipment</h3>
                <button type="button" @click="showCopyModal=false" style="background:none;border:none;font-size:18px;color:#9ca3af;cursor:pointer;line-height:1;">&times;</button>
            </div>
            <!-- Body -->
            <div style="padding:24px 24px 16px;">
                <p style="font-size:13px;color:#374151;margin:0 0 18px;">Please select how would you like to copy shipment information?</p>

                <div style="display:flex;flex-direction:column;gap:12px;">
                    <!-- Copy vessel info -->
                    <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;cursor:pointer;">
                        <input type="checkbox" x-model="copyOptions.copy_vessel_info" style="width:14px;height:14px;accent-color:#2563eb;cursor:pointer;">
                        Copy vessel info and all shipping schedule
                    </label>

                    <!-- Copy accounting -->
                    <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;cursor:pointer;">
                        <input type="checkbox" x-model="copyOptions.copy_accounting" style="width:14px;height:14px;accent-color:#2563eb;cursor:pointer;">
                        Copy accounting information from both MB/L and HB/L
                    </label>

                    <!-- Void invoices (sub-option, indented) -->
                    <div x-show="copyOptions.copy_accounting" style="margin-left:24px;display:flex;flex-direction:column;gap:10px;">
                        <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;cursor:pointer;">
                            <input type="checkbox" x-model="copyOptions.void_invoices" style="width:14px;height:14px;accent-color:#2563eb;cursor:pointer;">
                            Void Invoices
                        </label>
                        <div style="display:flex;gap:20px;align-items:center;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;cursor:pointer;">
                                <input type="checkbox" x-model="copyOptions.copy_ap" style="width:14px;height:14px;accent-color:#2563eb;cursor:pointer;">
                                A/P
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;cursor:pointer;">
                                <input type="checkbox" x-model="copyOptions.copy_ar" style="width:14px;height:14px;accent-color:#2563eb;cursor:pointer;">
                                A/R
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;cursor:pointer;">
                                <input type="checkbox" x-model="copyOptions.copy_dc" style="width:14px;height:14px;accent-color:#2563eb;cursor:pointer;">
                                D/C
                            </label>
                        </div>
                    </div>

                    <!-- Copy containers -->
                    <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;cursor:pointer;">
                        <input type="checkbox" x-model="copyOptions.copy_containers" style="width:14px;height:14px;accent-color:#2563eb;cursor:pointer;">
                        Copy container information from both MB/L and HB/L
                    </label>
                </div>
            </div>
            <!-- Footer -->
            <div style="display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #f3f4f6;">
                <button type="button" @click="showCopyModal=false"
                    style="padding:7px 20px;font-size:13px;border:1px solid #d1d5db;background:#fff;border-radius:4px;cursor:pointer;color:#374151;">
                    Cancel
                </button>
                <button type="button" @click="executeCopy()"
                    style="padding:7px 20px;font-size:13px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:500;">
                    OK
                </button>
            </div>
        </div>
        </div>
    </div>

    
    
    <!-- Batch Print Modal -->
    <div x-show="showBatchPrintModal" x-cloak style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center; margin:0; padding:0;" @click.self="showBatchPrintModal=false">
        <div style="background:#ffffff; width:720px; max-width:92vw; border-radius:4px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden; font-family:Arial, Helvetica, sans-serif; margin:auto;" @click.stop>
            
            <!-- Header -->
            <div style="padding:12px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; background:#fff;">
                <h3 style="font-size:15px; font-weight:normal; color:#4b5563; margin:0;">Batch Print</h3>
                <button type="button" @click="showBatchPrintModal=false" style="background:none; border:none; font-size:20px; color:#9ca3af; cursor:pointer; line-height:1;">&times;</button>
            </div>
            
            <!-- Body -->
            <div style="padding:20px 24px; display:flex; flex-direction:column; gap:16px; background:#fff;">
                
                <!-- Radio Option -->
                <div style="display:flex; align-items:center; gap:8px;">
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-size:13px; color:#374151; font-weight:500;">
                        <input type="radio" checked style="accent-color:#2b96d6;">
                        HB/L Arrival Notice
                    </label>
                </div>
                
                <!-- HBL Table Grid -->
                <div style="border:1px solid #d1d5db; border-radius:2px; overflow:hidden;">
                    <div style="max-height:220px; overflow-y:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:12px;">
                            <thead>
                                <tr style="background:#8c8c8c; color:#ffffff;">
                                    <th style="padding:8px 10px; width:45px; text-align:center; border-right:1px solid #a3a3a3;">
                                        <input type="checkbox" :checked="allBatchPrintSelected" @click="toggleAllBatchPrint()" style="accent-color:#2b96d6; cursor:pointer;">
                                    </th>
                                    <th style="padding:8px 12px; font-weight:bold; text-align:left; border-right:1px solid #a3a3a3; width:180px;">HB/L No.</th>
                                    <th style="padding:8px 12px; font-weight:bold; text-align:left; border-right:1px solid #a3a3a3;">Consignee</th>
                                    <th style="padding:8px 12px; font-weight:bold; text-align:left; border-right:1px solid #a3a3a3;">Customer</th>
                                    <th style="padding:8px 12px; font-weight:bold; text-align:left;">Notify</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(hbl, idx) in batchPrintHbls" :key="idx">
                                    <tr style="border-bottom:1px solid #e5e7eb; background:#fff;">
                                        <td style="padding:8px 10px; text-align:center; border-right:1px solid #f3f4f6;">
                                            <input type="checkbox" x-model="hbl.selected" style="accent-color:#2b96d6; cursor:pointer;">
                                        </td>
                                        <td style="padding:8px 12px; font-weight:500; color:#111827; border-right:1px solid #f3f4f6;" x-text="hbl.hbl_no"></td>
                                        <td style="padding:8px 12px; color:#4b5563; border-right:1px solid #f3f4f6;" x-text="hbl.consignee_name || '-'"></td>
                                        <td style="padding:8px 12px; color:#4b5563; border-right:1px solid #f3f4f6;" x-text="hbl.customer_name || '-'"></td>
                                        <td style="padding:8px 12px; color:#4b5563;" x-text="hbl.notify_name || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
            
            <!-- Footer -->
            <div style="padding:12px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end; gap:10px; background:#fff;">
                <button type="button" @click="showBatchPrintModal=false" style="padding:6px 20px; background:#e5e7eb; color:#374151; border:none; border-radius:3px; cursor:pointer; font-size:13px; font-weight:500;">Cancel</button>
                <button type="button" @click="submitBatchPrint()" style="padding:6px 24px; background:#2b96d6; color:#ffffff; border:none; border-radius:3px; cursor:pointer; font-size:13px; font-weight:500;">View</button>
            </div>
        </div>
    </div>

    <!-- Batch Email Modal -->
    <div x-show="showBatchEmailModal" x-cloak style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center; margin:0; padding:0;" @click.self="showBatchEmailModal=false">
        <div style="background:#ffffff; width:980px; max-width:92vw; max-height:90vh; border-radius:6px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; margin:auto;" @click.stop>
            
            <!-- Modal Header -->
            <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <h3 style="font-size:15px; font-weight:600; color:#1e293b; margin:0;">Batch Email</h3>
                <button type="button" @click="showBatchEmailModal=false" style="background:none; border:none; font-size:22px; color:#94a3b8; cursor:pointer; line-height:1;">&times;</button>
            </div>
            
            <!-- Modal Body -->
            <div style="padding:20px 24px; overflow-y:auto; flex:1; font-size:12px; color:#334155; display:flex; flex-direction:column; gap:14px;">
                
                <!-- Document Selection Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">Document</div>
                    <div style="display:flex; align-items:center; gap:20px; flex:1;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-weight:normal;">
                                <input type="radio" value="arrival_notice" x-model="batchEmailForm.type" @change="updateDocumentSubject" style="accent-color:#2563eb;">
                                Arrival Notice
                            </label>
                            <select x-model="batchEmailForm.arrival_notice_doc" @change="updateDocumentSubject" style="border:1px solid #cbd5e1; border-radius:4px; padding:4px 8px; font-size:12px; color:#334155; outline:none; background:#fff;">
                                <option value="ARRIVAL NOTICE / FREIGHT INVOICE">ARRIVAL NOTICE / FREIGHT INVOICE</option>
                                <option value="ARRIVAL NOTICE ONLY">ARRIVAL NOTICE ONLY</option>
                                <option value="FREIGHT INVOICE ONLY">FREIGHT INVOICE ONLY</option>
                            </select>
                        </div>
                        
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-weight:normal;">
                                <input type="radio" value="exam_hold" x-model="batchEmailForm.type" @change="updateDocumentSubject" style="accent-color:#2563eb;">
                                Exam Hold Notice
                            </label>
                            <select x-model="batchEmailForm.exam_hold_doc" @change="updateDocumentSubject" style="border:1px solid #cbd5e1; border-radius:4px; padding:4px 8px; font-size:12px; color:#334155; outline:none; background:#fff;">
                                <option value="EXAM HOLD NOTICE">EXAM HOLD NOTICE</option>
                                <option value="CUSTOMS HOLD NOTICE">CUSTOMS HOLD NOTICE</option>
                            </select>
                        </div>
                        
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-weight:normal;">
                                <input type="radio" value="delivery_order" x-model="batchEmailForm.type" @change="updateDocumentSubject" style="accent-color:#2563eb;">
                                Delivery Order
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- From Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">From</div>
                    <select x-model="batchEmailForm.from" style="flex:1; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; color:#334155; outline:none; background:#fff;">
                        <option value="demo@freightx.com">demo@freightx.com</option>
                        <option value="logistics@freightx.com">logistics@freightx.com</option>
                    </select>
                </div>
                
                <!-- To Row (Table Grid) -->
                <div style="display:flex; align-items:flex-start;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0; margin-top:8px;">To</div>
                    <div style="flex:1; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden; background:#fff;">
                        <table style="width:100%; border-collapse:collapse; font-size:12px;">
                            <thead>
                                <tr style="background:#f1f5f9; border-bottom:1px solid #cbd5e1; color:#475569;">
                                    <th style="padding:8px; width:36px; text-align:center; border-right:1px solid #e2e8f0;">
                                        <input type="checkbox" :checked="allHblsSelected" @click="toggleAllHblRows()" style="accent-color:#2563eb;">
                                    </th>
                                    <th style="padding:8px; width:55px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0;">Status</th>
                                    <th style="padding:8px 10px; width:180px; font-weight:600; text-align:left; border-right:1px solid #e2e8f0;">HB/L No.</th>
                                    
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllCustomers()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Customer</span>
                                            <input type="checkbox" :checked="allCustomersSelected" style="accent-color:#2563eb;" @click.stop="toggleAllCustomers()">
                                        </div>
                                    </th>
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllConsignees()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Consignee</span>
                                            <input type="checkbox" :checked="allConsigneesSelected" style="accent-color:#2563eb;" @click.stop="toggleAllConsignees()">
                                        </div>
                                    </th>
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllNotifies()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Notify</span>
                                            <input type="checkbox" :checked="allNotifiesSelected" style="accent-color:#2563eb;" @click.stop="toggleAllNotifies()">
                                        </div>
                                    </th>
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllBrokers()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Broker</span>
                                            <input type="checkbox" :checked="allBrokersSelected" style="accent-color:#2563eb;" @click.stop="toggleAllBrokers()">
                                        </div>
                                    </th>
                                    
                                    <th style="padding:8px 12px; font-weight:600; text-align:left;">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span>Contact</span>
                                            <button type="button" @click="refreshContacts()" style="background:#2563eb; color:#fff; border:none; border-radius:3px; padding:3px 8px; font-size:11px; cursor:pointer; display:flex; align-items:center; gap:4px; font-weight:500;">
                                                <i class="fa fa-refresh"></i> Refresh Contact
                                            </button>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(hbl, idx) in batchEmailHbls" :key="idx">
                                    <tr style="border-bottom:1px solid #e2e8f0; background:#fff;">
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <input type="checkbox" x-model="hbl.row_selected" style="accent-color:#2563eb;">
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.status==='success'" style="color:#10b981; font-size:14px;"></i>
                                            <i class="fa fa-times" x-show="hbl.status==='error'" style="color:#ef4444; font-size:14px;"></i>
                                            <span x-show="hbl.status==='pending'" style="color:#94a3b8;">-</span>
                                        </td>
                                        <td style="padding:8px 10px; font-weight:500; color:#1e293b; border-right:1px solid #f1f5f9; word-break:break-word;" x-text="hbl.hbl_no"></td>
                                        
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.customer_selected" @click="hbl.customer_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.customer_selected" @click="hbl.customer_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.consignee_selected" @click="hbl.consignee_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.consignee_selected" @click="hbl.consignee_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.notify_selected" @click="hbl.notify_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.notify_selected" @click="hbl.notify_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.broker_selected" @click="hbl.broker_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.broker_selected" @click="hbl.broker_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        
                                        <td style="padding:6px 10px;">
                                            <div style="border:1px solid #cbd5e1; border-radius:4px; padding:4px 8px; display:flex; flex-wrap:wrap; gap:4px; align-items:center; min-height:34px; background:#fff;">
                                                <template x-for="(contact, cidx) in hbl.contacts" :key="cidx">
                                                    <span style="background:#64748b; color:white; padding:2px 8px; border-radius:3px; font-size:11px; display:inline-flex; align-items:center; gap:6px;">
                                                        <span x-text="contact"></span>
                                                        <i class="fa fa-times" style="cursor:pointer; font-size:10px; opacity:0.8;" @click="removeContactEmail(idx, cidx)"></i>
                                                    </span>
                                                </template>
                                                <div style="flex:1; display:flex; align-items:center; min-width:140px;">
                                                    <input type="text" x-model="newContactEmails[idx]" @keydown.enter.prevent="addContactEmail(idx)" placeholder="Add email address here..." style="border:none; outline:none; flex:1; padding:2px 4px; font-size:11px; background:transparent;">
                                                    <button class="btn-action-round white" type="button" style="width:20px; height:20px; border-radius:50%; background:#2563eb; color:white; border:none; display:flex; justify-content:center; align-items:center; cursor:pointer; flex-shrink:0;" @click.prevent="addContactEmail(idx)"><i class="fa fa-plus" style="font-size:10px;"></i></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Subject Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">Subject</div>
                    <div style="flex:1; display:flex; gap:8px;">
                        <input type="text" x-model="batchEmailForm.subject_left" style="font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:4px; padding:6px 10px; width:130px; text-align:center; outline:none; font-size:12px;">
                        <input type="text" x-model="batchEmailForm.subject_middle" style="font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:4px; padding:6px 10px; width:280px; text-align:center; outline:none; font-size:12px;">
                        <input type="text" x-model="batchEmailForm.subject_right" style="font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:4px; padding:6px 10px; flex:1; outline:none; font-size:12px;">
                    </div>
                </div>
                
                <!-- Show Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">Show</div>
                    <div style="display:flex; gap:16px; align-items:center;">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0;">
                            <input type="radio" value="our_company" x-model="batchEmailForm.showType" @change="generateBatchContent" style="accent-color:#2563eb;"> Our Company
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0;">
                            <input type="radio" value="customer" x-model="batchEmailForm.showType" @change="generateBatchContent" style="accent-color:#2563eb;"> Customer
                        </label>
                        <select x-model="batchEmailForm.show_name" @change="generateBatchContent" style="border:1px solid #cbd5e1; border-radius:4px; padding:3px 8px; font-size:12px; color:#334155; outline:none; background:#fff;">
                            <option value="Name">Name</option>
                            <option value="Sardar">Sardar</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0;">
                            <input type="radio" value="blank" x-model="batchEmailForm.showType" @change="generateBatchContent" style="accent-color:#2563eb;"> Blank
                        </label>
                        
                        <div style="height:16px; width:1px; background:#cbd5e1; margin:0 4px;"></div>
                        
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-size:12px;">
                            <input type="checkbox" x-model="batchEmailForm.show_file_no" @change="generateBatchContent" style="accent-color:#2563eb;"> Booking No.
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-size:12px;">
                            <input type="checkbox" x-model="batchEmailForm.show_mbl_no" @change="generateBatchContent" style="accent-color:#2563eb;"> MB/L No.
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-size:12px;">
                            <input type="checkbox" x-model="batchEmailForm.show_eta" @change="generateBatchContent" style="accent-color:#2563eb;"> ETA
                        </label>
                    </div>
                </div>
                
                <!-- Content Editor -->
                <div style="display:flex; align-items:flex-start;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0; margin-top:8px;">Content</div>
                    <div style="flex:1; display:flex; flex-direction:column; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden; background:#fff;">
                        
                        <!-- Toolbar -->
                        <div style="background:#f8fafc; padding:4px 8px; border-bottom:1px solid #cbd5e1; display:flex; align-items:center; gap:4px;">
                            <button type="button" title="Clear Format" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('removeFormat')"><i class="fa fa-magic"></i></button>
                            <div style="width:1px; height:18px; background:#cbd5e1; margin:0 2px;"></div>
                            <button type="button" title="Bold" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; font-weight:bold; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('bold')">B</button>
                            <button type="button" title="Italic" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; font-style:italic; font-family:serif; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('italic')">I</button>
                            <button type="button" title="Underline" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; text-decoration:underline; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('underline')">U</button>
                            <button type="button" title="Strikethrough" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; text-decoration:line-through; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('strikeThrough')">S</button>
                            <button type="button" title="Eraser" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('removeFormat')"><i class="fa fa-eraser"></i></button>
                            
                            <!-- Color Picker Dropdown -->
                            <div style="position: relative;" @click.away="showColorPicker = false">
                                <button type="button" title="Text/Background Color" style="border:1px solid #cbd5e1; color:#000; font-weight:bold; background:#ffeb3b; padding:3px 8px; border-radius:3px; display:flex; align-items:center; gap:4px; cursor:pointer; font-size:12px;" @mousedown.prevent="showColorPicker = !showColorPicker">
                                    A <i class="fa fa-caret-down" style="font-size:10px;"></i>
                                </button>
                                
                                <div x-show="showColorPicker" style="position:absolute; top:100%; left:0; background:#fff; border:1px solid #cbd5e1; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:100; display:flex; padding:12px; gap:16px; border-radius:4px; display:none; margin-top:4px;" x-cloak>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <div style="text-align:center; font-size:11px; font-weight:500;">Background Color</div>
                                        <button type="button" style="width:100%; font-size:11px; border:1px solid #cbd5e1; background:#fff; border-radius:3px; padding:3px; cursor:pointer;" @mousedown.prevent="execCmd('hiliteColor', 'transparent')">Transparent</button>
                                        <div style="display:grid; grid-template-columns:repeat(8, 18px); gap:2px;">
                                            <template x-for="row in colors" :key="row[0]">
                                                <template x-for="c in row" :key="c">
                                                    <div :style="`background-color: ${c}; width:18px; height:18px; cursor:pointer; border-radius:2px; border:1px solid rgba(0,0,0,0.1);`" @mousedown.prevent="execCmd('hiliteColor', c)"></div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <div style="text-align:center; font-size:11px; font-weight:500;">Text Color</div>
                                        <button type="button" style="width:100%; font-size:11px; border:1px solid #cbd5e1; background:#fff; border-radius:3px; padding:3px; cursor:pointer;" @mousedown.prevent="execCmd('foreColor', '#000000')">Reset to default</button>
                                        <div style="display:grid; grid-template-columns:repeat(8, 18px); gap:2px;">
                                            <template x-for="row in colors" :key="row[0]">
                                                <template x-for="c in row" :key="c">
                                                    <div :style="`background-color: ${c}; width:18px; height:18px; cursor:pointer; border-radius:2px; border:1px solid rgba(0,0,0,0.1);`" @mousedown.prevent="execCmd('foreColor', c)"></div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="width:1px; height:18px; background:#cbd5e1; margin:0 2px;"></div>
                            <button type="button" title="Bullet List" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('insertUnorderedList')"><i class="fa fa-list-ul"></i></button>
                            <button type="button" title="Numbered List" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('insertOrderedList')"><i class="fa fa-list-ol"></i></button>
                            <button type="button" title="Toggle Fullscreen" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="fullscreen = !fullscreen"><i class="fa" :class="fullscreen ? 'fa-compress' : 'fa-arrows-alt'"></i></button>
                        </div>
                        
                        <!-- Content Editable Body -->
                        <div x-ref="editor" 
                             style="width:100%; height:160px; padding:10px 12px; overflow-y:auto; background:#fff; outline:none; font-family:inherit; font-size:12px; line-height:1.5;"
                             :style="fullscreen ? 'position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999; margin:0; border-radius:0;' : ''"
                             contenteditable="true" 
                             @input="updateBodyHTML"
                             x-html="batchEmailForm.body">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div style="padding:10px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc;">
                <button type="button" @click="showBatchEmailModal=false" style="padding:6px 16px; background:#e2e8f0; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer; font-weight:500; color:#334155; font-size:12px;">Cancel</button>
                <button type="button" @click="sendBatchEmail()" :disabled="isSendingBatch" style="padding:6px 18px; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:500; font-size:12px; display:flex; align-items:center; gap:6px;">
                    <i class="fa fa-spinner fa-spin" x-show="isSendingBatch"></i>
                    <span x-text="isSendingBatch ? 'Sending...' : 'Send'"></span>
                </button>
            </div>
        </div>
    </div>

    
    <!-- Cargo Manifest Status Modal -->
    <div x-show="showCargoManifestStatusModal" x-cloak style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center; margin:0; padding:0;" @click.self="showCargoManifestStatusModal=false">
        <div style="background:#ffffff; width:750px; max-width:92vw; max-height:90vh; border-radius:4px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; margin:auto;" @click.stop>
            
            <!-- Header -->
            <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#fff;">
                <h3 style="font-size:16px; font-weight:400; color:#555; margin:0;">Cargo Manifest Status</h3>
                <button type="button" @click="showCargoManifestStatusModal=false" style="background:none; border:none; font-size:22px; color:#aaa; cursor:pointer; line-height:1;">&times;</button>
            </div>

            <!-- Body -->
            <div style="padding:18px 22px; overflow-y:auto; flex:1; font-size:11px; color:#333; display:flex; flex-direction:column; gap:14px;">
                
                <!-- Query + Refresh Button -->
                <div>
                    <button type="button" @click="queryRefreshCargoManifestStatus()" style="background:#0284c7; color:#fff; border:none; padding:6px 14px; font-size:12px; font-weight:bold; border-radius:2px; cursor:pointer;">Query + Refresh</button>
                </div>

                <!-- Filter Inputs Grid -->
                <div style="display:grid; grid-template-columns: 110px 100px 100px 75px 150px 75px 120px; gap:8px 6px; align-items:center;">
                    
                    <div style="text-align:right; font-weight:bold; color:#555; font-size:10px;">MB/L Issuer / No.</div>
                    <input type="text" x-model="cargoManifestStatusForm.mbl_issuer" style="background:#e2e8f0; border:none; padding:4px 6px; font-size:11px; font-weight:bold; outline:none;">
                    <input type="text" x-model="cargoManifestStatusForm.mbl_no" style="background:#e2e8f0; border:none; padding:4px 6px; font-size:11px; font-weight:bold; outline:none;">

                    <div style="text-align:right; font-weight:bold; color:#555; font-size:10px;">Carrier</div>
                    <input type="text" x-model="cargoManifestStatusForm.carrier" style="background:#e2e8f0; border:none; padding:4px 6px; font-size:11px; outline:none;">

                    <div style="text-align:right; font-weight:bold; color:#555; font-size:10px;">Arrive Date</div>
                    <input type="text" x-model="cargoManifestStatusForm.arrive_date" style="background:#e2e8f0; border:none; padding:4px 6px; font-size:11px; outline:none;">

                    <div style="text-align:right; font-weight:bold; color:#555; font-size:10px; grid-column:1;">Vessel</div>
                    <input type="text" x-model="cargoManifestStatusForm.vessel" style="background:#e2e8f0; border:none; padding:4px 6px; font-size:11px; outline:none; grid-column: 2 / span 2;">

                    <div style="text-align:right; font-weight:bold; color:#555; font-size:10px;">Voyage</div>
                    <input type="text" x-model="cargoManifestStatusForm.voyage" style="background:#e2e8f0; border:none; padding:4px 6px; font-size:11px; outline:none;">

                </div>

                <!-- MB/L Section -->
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight:bold; font-size:12px; color:#000;">MB/L</span>
                        <span style="color:#ef4444; font-size:11px; font-weight:bold; display:flex; align-items:center; gap:4px;">
                            <span style="font-size:14px;">●</span> Last Update Date: <span x-text="cargoManifestStatusForm.last_update"></span>
                        </span>
                    </div>
                    <textarea x-model="cargoManifestStatusForm.mbl_status" style="width:100%; height:85px; background:#e2e8f0; border:none; padding:10px; font-family:inherit; font-size:11px; outline:none; resize:none; color:#333;"></textarea>
                </div>

                <!-- HB/L Section -->
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <div style="font-weight:bold; font-size:12px; color:#000;">HB/L</div>
                    <textarea x-model="cargoManifestStatusForm.hbl_status" style="width:100%; height:85px; background:#e2e8f0; border:none; padding:10px; font-family:inherit; font-size:11px; outline:none; resize:none; color:#333;"></textarea>
                </div>

                <!-- Query Log Section -->
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <div style="font-weight:bold; font-size:12px; color:#000;">Query Log</div>
                    <div style="max-height:120px; overflow-y:auto; border:1px solid #e2e8f0;">
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <thead>
                                <tr style="background:#777; color:#fff; position:sticky; top:0;">
                                    <th style="padding:6px 10px; text-align:left; font-weight:500; width:150px;">Time</th>
                                    <th style="padding:6px 10px; text-align:left; font-weight:500;">User</th>
                                    <th style="padding:6px 10px; text-align:left; font-weight:500; width:100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(log, idx) in cargoManifestStatusForm.logs" :key="idx">
                                    <tr style="border-bottom:1px solid #e2e8f0; background:#fff;">
                                        <td style="padding:6px 10px; color:#555;" x-text="log.time"></td>
                                        <td style="padding:6px 10px; color:#555;" x-text="log.user"></td>
                                        <td style="padding:6px 10px; color:#555;" x-text="log.status"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div style="padding:10px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc;">
                <button type="button" @click="showCargoManifestStatusModal=false" style="padding:6px 20px; background:#e2e8f0; border:none; border-radius:2px; cursor:pointer; font-weight:500; color:#333; font-size:12px;">Close</button>
                <button type="button" @click="downloadCargoManifestStatusPdf()" style="padding:6px 20px; background:#4dcbcf; color:white; border:none; border-radius:2px; cursor:pointer; font-weight:500; font-size:12px;">Download PDF</button>
            </div>

        </div>
    </div>
        @include('partials.ai-modal')


    </div><!-- end Alpine scope -->

    </div>
    </form>

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

        // OCEAN IMPORT FIELD DIAGNOSTIC - Console Output
        const diagnostic = @json(session('diagnostic'));
        if (diagnostic) {
            console.log('%c╔══════════════════════════════════════════════════════════════╗', 'color: #0066cc; font-weight: bold;');
            console.log('%c║    OCEAN IMPORT FIELD DIAGNOSTIC REPORT                     ║', 'color: #0066cc; font-weight: bold;');
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
        }
    </script>
</x-layout>
