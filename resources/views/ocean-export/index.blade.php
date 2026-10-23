<x-layout>
    @push('styles')
    <x-form-styles />
    @endpush

    <form action="{{ isset($oceanExport) && $oceanExport->id ? route('ocean-export.update', $oceanExport->id) : route('ocean-export.store') }}" method="POST">
        @csrf
        @if(isset($oceanExport) && $oceanExport->id) @method('PUT') @endif

        @if(session('success'))
            <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;padding:10px 15px;border-radius:4px;margin-bottom:15px;display:flex;align-items:center;gap:8px;">
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

    <script>
        // Store old input data globally (use window to avoid redeclaration with Turbo/Livewire)
        window.oldInputData = @json(request()->old());
        
        // Debug: Log if we have old data
        console.log('=== OCEAN EXPORT FORM DEBUG ===');
        console.log('Old input data:', window.oldInputData);
        console.log('Has old data?', window.oldInputData && Object.keys(window.oldInputData).length > 0);
        
        // Force reload on browser back/forward navigation to fix cached Alpine state
        if (!window.oceanExportPageShowHandlerAdded) {
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    // Page was loaded from cache (bfcache), force reload
                    window.location.reload();
                }
            });
            window.oceanExportPageShowHandlerAdded = true;
        }
        
        // Helper function to get value: old() > $oceanExport > default
        window.getFormValue = function(field, defaultValue = '') {
            if (window.oldInputData && window.oldInputData[field] !== undefined && window.oldInputData[field] !== null) {
                return window.oldInputData[field];
            }
            return defaultValue;
        };

        function oceanExportModule() {
            return {
                saved: @json(isset($oceanExport) && $oceanExport->id ? true : false),
                activeTab: 'basic',
                showMblSection: true,
                showMblMemo: false,
                isDirectMaster: {{ (isset($oceanExport) && $oceanExport->is_direct_master) ? 'true' : 'false' }},
                showMore: false,
                showClipboardModal: false,
                showToolsMenu: false,
                showTrackTraceModal: false,
                showAiModal: false,
                aiLoading: false,
                aiPreviewMode: false,
                documentPreviewUrl: '',
                extractedData: [],
                
                // Initialize saved state based on whether we have an ID
                init() {
                    console.log('🔧 Alpine init() called');
                    console.log('form.id:', this.form.id);
                    console.log('saved (before):', this.saved);
                    
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

                    // Check if form has ID (means we're editing)
                    if (this.form.id) {
                        this.saved = true;
                        console.log('✅ Set saved = true (form.id exists)');
                    } else {
                        console.log('⚠️ form.id is null/undefined, keeping saved = false');
                    }
                    
                    // Map DB charge fields to UI charge fields
                    if (this.chargesList && Array.isArray(this.chargesList)) {
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
                    }

                    console.log('saved (after):', this.saved);
                },
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
                quoteStep: 1,
                selectedQuote: null,
                selectQuote(data) {
                    if (!data) return;
                    this.selectedQuote = { ...data, items: this.quoteItems[data.quote_no] || [] };
                    this.quoteForm.quote_no = data.quote_no || '';
                    const dateStr = new Date().toISOString().slice(2,10).replace(/-/g,'');
                    const randNum = Math.floor(100000 + Math.random() * 900000);
                    this.quoteForm.mbl_no = data.mbl_no || ('MOE-' + dateStr + randNum);
                    this.quoteForm.hbl_no = data.hbl_no || ('HOE-' + dateStr + randNum);
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
                    if (this.searchFilters.op && quote.sales_person_id != this.searchFilters.op) return false;
                    return true;
                },
                quoteForm: {
                    quote_no: '', mbl_no: '', hbl_no: '', etd: '', eta: '',
                    customer: '', customer_id: '', sales: '', sales_person_id: '', office_id: '',
                    pol_id: '', pod_id: '', pol_name: '', pod_name: '',
                    ship_mode: 'FCL', oversea_agent: '', oversea_agent_id: '', service_term: '', op: '', op_id: '',
                    incoterms: '', incoterms_id: '', carrier_name: '', carrier_id: '', commodity: '',
                    booking_no: '', po_no: '', hts_code: '', pkg_qty: '', weight_kg: '', volume_cbm: '', detail: ''
                },
                hbls: (function() {
                    let raw = getFormValue('hbls', @json(isset($oceanExport) && $oceanExport->hbls->count() ? $oceanExport->hbls : []));
                    if (!Array.isArray(raw)) return [];
                    return raw.map(function(hbl) {
                        function fmtDate(d) {
                            if (!d) return '';
                            if (typeof d === 'string') return d.substring(0, 10);
                            return '';
                        }
                        function toBool(v) {
                            return v === true || v === 1 || v === '1' || v === 'true';
                        }
                        return {
                            id: hbl.id || null,
                            show: true,
                            showMore: false,
                            showMemo: false,
                            hbl_no: hbl.hbl_no || '',
                            quotation_no: hbl.quotation_no || '',
                            customer_id: hbl.customer_id || '',
                            sales_person_id: hbl.sales_person_id || '',
                            shipper_id: hbl.shipper_id || '',
                            consignee_id: hbl.consignee_id || '',
                            notify_party_id: hbl.notify_party_id || '',
                            customs_broker_id: hbl.customs_broker_id || '',
                            delivery_location_id: hbl.delivery_location_id || '',
                            referred_by_id: hbl.referred_by_id || '',
                            pol_id: hbl.pol_id || '',
                            pod_id: hbl.pod_id || '',
                            del_id: hbl.del_id || '',
                            fdest_id: hbl.fdest_id || '',
                            receipt_id: hbl.receipt_id || '',
                            vessel_name: hbl.vessel_name || '',
                            voyage_no: hbl.voyage_no || '',
                            pre_carriage_by: hbl.pre_carriage_by || '',
                            is_rail: toBool(hbl.is_rail),
                            service_term: hbl.service_term || 'CY',
                            ship_mode: hbl.ship_mode || 'FCL',
                            ship_type: hbl.ship_type || 'NORMAL',
                            cargo_type: hbl.cargo_type || 'GENERAL CARGO',
                            incoterms_id: hbl.incoterms_id || '',
                            date_of_issue: fmtDate(hbl.date_of_issue),
                            lc_no: hbl.lc_no || '',
                            sc_no: hbl.sc_no || '',
                            freight_payable_at: hbl.freight_payable_at || '',
                            is_express_bl: hbl.is_express_bl == 1 || hbl.is_express_bl === true ? 1 : 0,
                            is_door_move: toBool(hbl.is_door_move),
                            is_customs_clear: toBool(hbl.is_customs_clear),
                            is_customs_hold: toBool(hbl.is_customs_hold),
                            is_obl_received: toBool(hbl.is_obl_received),
                            obl_received_date: fmtDate(hbl.obl_received_date),
                            is_fr_released: toBool(hbl.is_fr_released),
                            fr_released_date: fmtDate(hbl.fr_released_date),
                            is_an_sent: toBool(hbl.is_an_sent),
                            an_sent_date: fmtDate(hbl.an_sent_date),
                            is_do_sent: toBool(hbl.is_do_sent),
                            do_sent_date: fmtDate(hbl.do_sent_date),
                            name_account: hbl.name_account || '',
                            group_comm: hbl.group_comm || '',
                            line_code: hbl.line_code || '',
                            is_ecommerce: toBool(hbl.is_ecommerce),
                            is_customs_doc: toBool(hbl.is_customs_doc),
                            hbl_remark: hbl.hbl_remark || '',
                            cfs_location_id: hbl.cfs_location_id || '',
                            freight_released_by_id: hbl.freight_released_by_id || '',
                            po_no: hbl.po_no || '',
                            po_mapping_type: hbl.po_mapping_type || 'container',
                            hbl_mark: hbl.hbl_mark || '',
                            hbl_description: hbl.hbl_description || '',
                            show_containers: true,
                            containers: (hbl.containers || []).map(function(c) {
                                return {
                                    container_no: c.container_no || '',
                                    manual_container_no: '',
                                    pkg_qty: c.pivot ? c.pivot.pkg_qty : (c.pkg_qty || ''),
                                    pkg_unit: c.pivot ? c.pivot.pkg_unit : (c.pkg_unit || 'CARTON(S)'),
                                    weight_kg: c.pivot ? c.pivot.weight_kg : (c.weight_kg || ''),
                                    weight_unit: c.pivot ? c.pivot.weight_unit : (c.weight_unit || 'KG'),
                                    measure_cbm: c.pivot ? c.pivot.measure_cbm : (c.measure_cbm || ''),
                                    measure_unit: c.pivot ? c.pivot.measure_unit : (c.measure_unit || 'CBM'),
                                    po_no: c.pivot ? c.pivot.po_no : (c.po_no || '')
                                };
                            }),
                            commodities: (hbl.commodities || []).map(function(comm) {
                                return {
                                    id: comm.id || null,
                                    selected: false,
                                    hbl_no: comm.hbl_no || hbl.hbl_no || '',
                                    commodity_desc: comm.commodity_desc || '',
                                    hts_code: comm.hts_code || '',
                                    container_no: comm.container_no || '',
                                    po_no: comm.po_no || ''
                                };
                            })
                        };
                    });
                })(),
                form: {
                    id: @json(isset($oceanExport) ? $oceanExport->id : null),
                    file_no: window.getFormValue('file_no', @json(isset($oceanExport) ? $oceanExport->file_no : 'MOE-' . date('ymdHis') . '-' . strtoupper(substr(uniqid(), -4)))),
                    mbl_no: getFormValue('mbl_no', @json(isset($oceanExport) ? $oceanExport->mbl_no : '')),
                    booking_no: getFormValue('booking_no', @json(isset($oceanExport) ? $oceanExport->booking_no : '')),
                    office_id: getFormValue('office_id', @json(isset($oceanExport) ? $oceanExport->office_id : '')),
                    post_date: getFormValue('post_date', @json(isset($oceanExport) && $oceanExport->post_date ? $oceanExport->post_date->format('Y-m-d') : date('Y-m-d'))),
                    voyage: getFormValue('voyage', @json(isset($oceanExport) ? $oceanExport->voyage : '')),
                    etd: getFormValue('etd', @json(isset($oceanExport) && $oceanExport->etd ? $oceanExport->etd->format('Y-m-d') : '')),
                    eta: getFormValue('eta', @json(isset($oceanExport) && $oceanExport->eta ? $oceanExport->eta->format('Y-m-d') : '')),
                    forwarding_agent_id: getFormValue('forwarding_agent_id', @json(isset($oceanExport) ? $oceanExport->forwarding_agent_id : '')),
                    op_id: getFormValue('op_id', @json(isset($oceanExport) ? $oceanExport->op_id : '')),
                    agent_ref_no: getFormValue('agent_ref_no', @json(isset($oceanExport) ? $oceanExport->agent_ref_no : '')),
                    dm_customer_id: getFormValue('dm_customer_id', @json(isset($oceanExport) ? $oceanExport->dm_customer_id : '')),
                    dm_sales_person_id: getFormValue('dm_sales_person_id', @json(isset($oceanExport) ? $oceanExport->dm_sales_person_id : '')),
                    oversea_agent_id: getFormValue('oversea_agent_id', @json(isset($oceanExport) ? $oceanExport->oversea_agent_id : '')),
                    co_loader_id: getFormValue('co_loader_id', @json(isset($oceanExport) ? $oceanExport->co_loader_id : '')),
                    contract_no: getFormValue('contract_no', @json(isset($oceanExport) ? $oceanExport->contract_no : '')),
                    dm_shipper_id: getFormValue('dm_shipper_id', @json(isset($oceanExport) ? $oceanExport->dm_shipper_id : '')),
                    dm_bill_to_id: getFormValue('dm_bill_to_id', @json(isset($oceanExport) ? $oceanExport->dm_bill_to_id : '')),
                    carrier_id: getFormValue('carrier_id', @json(isset($oceanExport) ? $oceanExport->carrier_id : '')),
                    bl_type: getFormValue('bl_type', @json(isset($oceanExport) ? $oceanExport->bl_type : 'NORMAL')),
                    acct_carrier_id: getFormValue('acct_carrier_id', @json(isset($oceanExport) ? $oceanExport->acct_carrier_id : '')),
                    sub_bl_no: getFormValue('sub_bl_no', @json(isset($oceanExport) ? $oceanExport->sub_bl_no : '')),
                    dm_notify_id: getFormValue('dm_notify_id', @json(isset($oceanExport) ? $oceanExport->dm_notify_id : '')),
                    cargo_type: getFormValue('cargo_type', @json(isset($oceanExport) ? $oceanExport->cargo_type : 'GENERAL CARGO')),
                    vessel_id: getFormValue('vessel_id', @json(isset($oceanExport) ? $oceanExport->vessel_id : '')),
                    pol_id: getFormValue('pol_id', @json(isset($oceanExport) ? $oceanExport->pol_id : '')),
                    del_id: getFormValue('del_id', @json(isset($oceanExport) ? $oceanExport->del_id : '')),
                    atd: getFormValue('atd', @json(isset($oceanExport) && $oceanExport->atd ? $oceanExport->atd->format('Y-m-d') : '')),
                    cy_location_id: getFormValue('cy_location_id', @json(isset($oceanExport) ? $oceanExport->cy_location_id : '')),
                    pod_id: getFormValue('pod_id', @json(isset($oceanExport) ? $oceanExport->pod_id : '')),
                    fdest_id: getFormValue('fdest_id', @json(isset($oceanExport) ? $oceanExport->fdest_id : '')),
                    ata: getFormValue('ata', @json(isset($oceanExport) && $oceanExport->ata ? $oceanExport->ata->format('Y-m-d') : '')),
                    cfs_location_id: getFormValue('cfs_location_id', @json(isset($oceanExport) ? $oceanExport->cfs_location_id : '')),
                    final_eta: getFormValue('final_eta', @json(isset($oceanExport) && $oceanExport->final_eta ? $oceanExport->final_eta->format('Y-m-d') : '')),
                    etb: getFormValue('etb', @json(isset($oceanExport) && $oceanExport->etb ? $oceanExport->etb->format('Y-m-d') : '')),
                    freight_term: getFormValue('freight_term', @json(isset($oceanExport) ? $oceanExport->freight_term : 'Prepaid')),
                    freight: getFormValue('freight', @json(isset($oceanExport) ? $oceanExport->freight : '')),
                    routes: getFormValue('routes', @json(isset($oceanExport) && !empty($oceanExport->routes) ? (is_array($oceanExport->routes) ? $oceanExport->routes : json_decode($oceanExport->routes, true)) : [])),
                    obl_type: getFormValue('obl_type', @json(isset($oceanExport) ? $oceanExport->obl_type : 'ORIGINAL BILL OF LADING')),
                    latest_gate_in: getFormValue('latest_gate_in', @json(isset($oceanExport) && $oceanExport->latest_gate_in ? $oceanExport->latest_gate_in->format('Y-m-d') : '')),
                    ship_mode: getFormValue('ship_mode', @json(isset($oceanExport) ? $oceanExport->ship_mode : 'FCL')),
                    is_obl_received: getFormValue('is_obl_received', @json(isset($oceanExport) ? (bool)$oceanExport->is_obl_received : false)),
                    obl_received_date: getFormValue('obl_received_date', @json(isset($oceanExport) && $oceanExport->obl_received_date ? $oceanExport->obl_received_date->format('Y-m-d') : '')),
                    service_term_from_id: getFormValue('service_term_from_id', @json(isset($oceanExport) && $oceanExport->service_term_from_id ? $oceanExport->service_term_from_id : ($serviceTerms?->first()?->id ?? ''))),
                    service_term_to_id: getFormValue('service_term_to_id', @json(isset($oceanExport) && $oceanExport->service_term_to_id ? $oceanExport->service_term_to_id : ($serviceTerms?->first()?->id ?? ''))),
                    is_released: getFormValue('is_released', @json(isset($oceanExport) ? (bool)$oceanExport->is_released : false)),
                    released_date: getFormValue('released_date', @json(isset($oceanExport) && $oceanExport->released_date ? $oceanExport->released_date->format('Y-m-d') : '')),
                    business_referred_by_id: getFormValue('business_referred_by_id', @json(isset($oceanExport) ? $oceanExport->business_referred_by_id : '')),
                    receipt_id: getFormValue('receipt_id', @json(isset($oceanExport) ? $oceanExport->receipt_id : '')),
                    receipt_etd: getFormValue('receipt_etd', @json(isset($oceanExport) && $oceanExport->receipt_etd ? $oceanExport->receipt_etd->format('Y-m-d') : '')),
                    return_location_id: getFormValue('return_location_id', @json(isset($oceanExport) ? $oceanExport->return_location_id : '')),
                    dm_consignee_id: getFormValue('dm_consignee_id', @json(isset($oceanExport) ? $oceanExport->dm_consignee_id : '')),
                    sales_type: getFormValue('sales_type', @json(isset($oceanExport) ? $oceanExport->sales_type : 'NORMAL')),
                    internal_remark: getFormValue('internal_remark', @json(isset($oceanExport) ? $oceanExport->internal_remark : '')),
                    is_blocked: getFormValue('is_blocked', @json(isset($oceanExport) ? (bool)$oceanExport->is_blocked : false)),
                    ams_no: getFormValue('ams_no', @json(isset($oceanExport) ? $oceanExport->ams_no : '')),
                    isf_no: getFormValue('isf_no', @json(isset($oceanExport) ? $oceanExport->isf_no : '')),
                    isf_matched_date: getFormValue('isf_matched_date', @json(isset($oceanExport) && $oceanExport->isf_matched_date ? $oceanExport->isf_matched_date->format('Y-m-d') : '')),
                    is_isf_3rd_party: getFormValue('is_isf_3rd_party', @json(isset($oceanExport) ? (bool)$oceanExport->is_isf_3rd_party : false)),
                    entry_no: getFormValue('entry_no', @json(isset($oceanExport) ? $oceanExport->entry_no : '')),
                    entry_doc_sent_date: getFormValue('entry_doc_sent_date', @json(isset($oceanExport) && $oceanExport->entry_doc_sent_date ? $oceanExport->entry_doc_sent_date->format('Y-m-d') : '')),
                    go_date: getFormValue('go_date', @json(isset($oceanExport) && $oceanExport->go_date ? $oceanExport->go_date->format('Y-m-d') : '')),
                    available_date: getFormValue('available_date', @json(isset($oceanExport) && $oceanExport->available_date ? $oceanExport->available_date->format('Y-m-d') : '')),
                    c_released_date: getFormValue('c_released_date', @json(isset($oceanExport) && $oceanExport->c_released_date ? $oceanExport->c_released_date->format('Y-m-d') : '')),
                    released_by_id: getFormValue('released_by_id', @json(isset($oceanExport) ? $oceanExport->released_by_id : '')),
                    is_ror: getFormValue('is_ror', @json(isset($oceanExport) ? (bool)$oceanExport->is_ror : false)),
                    is_hold: getFormValue('is_hold', @json(isset($oceanExport) ? (bool)$oceanExport->is_hold : false)),
                    door_delivery_date: getFormValue('door_delivery_date', @json(isset($oceanExport) && $oceanExport->door_delivery_date ? $oceanExport->door_delivery_date->format('Y-m-d') : '')),
                    trucker_id: getFormValue('trucker_id', @json(isset($oceanExport) ? $oceanExport->trucker_id : '')),
                    expiry_date: getFormValue('expiry_date', @json(isset($oceanExport) && $oceanExport->expiry_date ? $oceanExport->expiry_date->format('Y-m-d') : '')),
                    incoterm_id: getFormValue('incoterm_id', @json(isset($oceanExport) ? $oceanExport->incoterm_id : '')),
                    lfd: getFormValue('lfd', @json(isset($oceanExport) && $oceanExport->lfd ? $oceanExport->lfd->format('Y-m-d') : '')),
                    is_do_sent: getFormValue('is_do_sent', @json(isset($oceanExport) ? (bool)$oceanExport->is_do_sent : false)),
                    do_sent_date: getFormValue('do_sent_date', @json(isset($oceanExport) && $oceanExport->do_sent_date ? $oceanExport->do_sent_date->format('Y-m-d') : '')),
                    mark: getFormValue('mark', @json(isset($oceanExport) ? $oceanExport->mark : '')),
                    description: getFormValue('description', @json(isset($oceanExport) ? $oceanExport->description : '')),
                    is_ecommerce: getFormValue('is_ecommerce', @json(isset($oceanExport) ? (bool)$oceanExport->is_ecommerce : false)),
                    containers: getFormValue('containers', @json(isset($oceanExport) && $oceanExport->containers->count() ? $oceanExport->containers : [])),
                    charges: [],
                history: @json($history ?? [])
            },
            activeChargeFilter: 'All',
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
                            lfd: '',
                            fdd: '',
                            pkg_qty: 0,
                            pkg_unit_id: '',
                            weight_kg: 0,
                            weight_lb: 0,
                            measure_cbm: 0,
                            measure_cft: 0,
                            expanded: false,
                            pickup_no: '',
                            cprs_no: '',
                            cnru_no: '',
                            it_no: '',
                            chassis_days: 0,
                            is_customs_hold: false,
                            is_an_sent: false,
                            an_sent_date: '',
                            is_do_sent: false,
                            do_sent_date: '',
                            is_dg: false,
                            is_carrier_release: false,
                            yard_location: '',
                            is_avail_pickup: false,
                            trucker_id: '',
                            is_complete: false,
                            remarks: '',
                            internal_remarks: '',
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
                        });
                    }
                },
                deleteSelectedContainers() {
                    this.form.containers = this.form.containers.filter(c => !c.selected);
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
                    if (toDuplicate.length === 0) {
                        if (this.form.containers.length > 0) {
                            let clone = JSON.parse(JSON.stringify(this.form.containers[0]));
                            clone.id = null;
                            clone.selected = false;
                            clone.container_no = clone.container_no + ' - Copy';
                            toDuplicate.push(clone);
                        }
                    }
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
                    let route = this.form.id ? '/ocean-export/' + this.form.id + '/containers/import' : '/ocean-export/containers/import-temp';
                    fetch(route, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.containers) {
                            data.containers.forEach(c => {
                                this.form.containers.push({
                                    id: null, selected: false,
                                    container_no: c.container_no || '', pp_ctf: c.pp_ctf || '',
                                    container_type_id: '', seal_no: c.seal_no || '',
                                    seal_no2: c.seal_no2 || '', lfd: '', fdd: '',
                                    pkg_qty: c.pkg_qty || 0, pkg_unit_id: '',
                                    weight_kg: c.weight_kg || 0, weight_lb: 0,
                                    measure_cbm: c.measure_cbm || 0, measure_cft: 0,
                                    expanded: false, pickup_no: '', cprs_no: '',
                                    cnru_no: '', it_no: '', chassis_days: 0,
                                    is_customs_hold: false, is_an_sent: false,
                                    an_sent_date: '', is_do_sent: false, do_sent_date: '',
                                    is_dg: false, is_carrier_release: false,
                                    yard_location: '', is_avail_pickup: false,
                                    trucker_id: '', is_complete: false,
                                    remarks: '', internal_remarks: '',
                                    storage_start_date: '', storage_end_date: '',
                                    unload_vessel_date: '', gate_in_date: '',
                                    rail_start_date: '', pod_eta: '',
                                    appointment_date: '', pickup_date: '',
                                    gate_out_date: '', fdest_eta: '',
                                    eta_door: '', ata_door: '',
                                    empty_conf_date: '', empty_ret_date: '',
                                });
                            });
                            alert(data.containers.length + ' container(s) imported.');
                        } else {
                            alert('Import failed.');
                        }
                    })
                    .catch(() => alert('Import error.'));
                    e.target.value = '';
                },
                copyDataFromAllHbl() {
                    if (!this.hbls || !this.hbls.length) {
                        if (typeof showToast === 'function') showToast('info', 'No House BLs found to copy from.');
                        return;
                    }

                    const containerMap = {};
                    let unassignedPkg = 0, unassignedWgt = 0, unassignedVol = 0;

                    this.hbls.forEach(h => {
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
                toggleAllContainers(e) {
                    this.form.containers.forEach(c => c.selected = e.target.checked);
                },
                calculateTotal(field) {
                    return this.form.containers.reduce((sum, c) => sum + (parseFloat(c[field]) || 0), 0);
                },
                copyContainersToClipboard() {
                    let text = 'Container No.\tTP/SZ\tSeal No.\tPKG\tTARE (KG)\tVGM (KG)\tNet Weight (KG)\tGross Weight (KG)\tMeasurement (CBM)\n';
                    this.form.containers.forEach(c => {
                        text += (c.container_no || '-') + '\t' + (c.container_type_id || '-') + '\t' + (c.seal_no || '-') + '\t' + (c.pkg_qty || '0') + '\t-\t-\t-\t' + (c.weight_kg || '0') + '\t' + (c.measure_cbm || '0') + '\n';
                    });
                    navigator.clipboard.writeText(text).then(() => {
                        alert('Container data copied to clipboard!');
                        this.showClipboardModal = false;
                    }).catch(() => {
                        prompt('Copy manually:', text);
                    });
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
                uploadAiDocument() {
                    const fileInput = document.getElementById('aiDocumentFile');
                    if (!fileInput.files.length) {
                        if (typeof showToast === 'function') showToast('error', 'Please select a file first.');
                        else alert('Please select a file first.');
                        return;
                    }
                    
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('document', file);
                    
                    // Create preview URL
                    if (this.documentPreviewUrl) {
                        URL.revokeObjectURL(this.documentPreviewUrl);
                    }
                    this.documentPreviewUrl = URL.createObjectURL(file);
                    
                    this.aiLoading = true;
                    this.aiPreviewMode = true;
                    this.extractedData = []; // Clear old data
                    
                    fetch('/api/ai/extract/ocean-export', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            return response.json();
                        }
                        const text = await response.text();
                        return { success: false, message: 'Server returned non-JSON response (' + response.status + ')' };
                    })
                    .then(result => {
                        this.aiLoading = false;
                        if (result.success && result.data) {
                            this.extractedData = [];
                            
                            // Parse MBL fields
                            if (result.data.mbl) {
                                const m = result.data.mbl;
                                const mblMap = [
                                    { label: 'MB/L No.', key: 'mbl_no', val: m.mbl_no },
                                    { label: 'Vessel Name', key: 'vessel_name', val: m.vessel_name },
                                    { label: 'Voyage No.', key: 'voyage', val: m.voyage },
                                    { label: 'Port of Loading', key: 'pol_id', val: m.pol_id, display: m.pol_name, nameKey: 'pol_name' },
                                    { label: 'Port of Discharge', key: 'pod_id', val: m.pod_id, display: m.pod_name, nameKey: 'pod_name' },
                                    { label: 'Place of Delivery', key: 'del_id', val: m.del_id, display: m.del_name, nameKey: 'del_name' },
                                    { label: 'Final Destination', key: 'fdest_id', val: m.fdest_id, display: m.fdest_name, nameKey: 'fdest_name' },
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
                            
                            // Parse HBL fields (Ocean Export usually has 1 or more, we'll map the first one)
                            if (result.data.hbls && result.data.hbls.length > 0) {
                                const h = result.data.hbls[0];
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
                            if (result.data.routes && result.data.routes.length > 0) {
                                result.data.routes.forEach((r, idx) => {
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
                            if (result.data.containers && result.data.containers.length > 0) {
                                result.data.containers.forEach((c, idx) => {
                                    this.extractedData.push({
                                        label: `Container ${idx+1}`,
                                        parent: 'containers',
                                        key: `container_${idx}`,
                                        value: c.container_no,
                                        displayValue: `${c.container_no || ''} ${c.container_type ? '('+c.container_type+')' : ''}`,
                                        originalData: c,
                                        isContainer: true,
                                        selected: true
                                    });
                                });
                            }
                            
                            this.aiPreviewMode = true;
                        } else {
                            if (typeof showToast === 'function') showToast('error', result.message || 'Failed to extract data.');
                        }
                    })
                    .catch(error => {
                        this.aiLoading = false;
                        console.error('Error:', error);
                        if (typeof showToast === 'function') showToast('error', 'An error occurred during extraction.');
                    });
                },
                applyAiData() {
                    let dataToApply = { mbl: {}, hbl: {}, hbls: [], containers: [], routes: [] };
                    
                    this.extractedData.forEach(item => {
                        if (item.selected) {
                            if (item.isContainer || item.parent === 'containers') {
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
                    console.log('Populating data:', data);
                    
                    const ensureOptionExists = (selectName, value, text) => {
                        if (!value) return;
                        const selectEl = document.querySelector(`select[name="${selectName}"]`);
                        if (selectEl) {
                            let exists = false;
                            for (let i = 0; i < selectEl.options.length; i++) {
                                if (selectEl.options[i].value == value) {
                                    exists = true;
                                    break;
                                }
                            }
                            if (!exists) {
                                const newOption = new Option(text, value, false, false);
                                selectEl.add(newOption);
                            }
                        }
                    };

                    // MBL Mapping
                    if (data.mbl) {
                        if (data.mbl.mbl_no) this.form.mbl_no = data.mbl.mbl_no;
                        if (data.mbl.booking_no) this.form.booking_no = data.mbl.booking_no;
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
                        if (data.mbl.receipt_etd) this.form.receipt_etd = data.mbl.receipt_etd;
                        if (data.mbl.port_cutoff) this.form.port_cutoff = data.mbl.port_cutoff;
                        if (data.mbl.doc_cutoff) this.form.doc_cutoff = data.mbl.doc_cutoff;
                        if (data.mbl.vgm_cutoff) this.form.vgm_cutoff = data.mbl.vgm_cutoff;
                        if (data.mbl.rail_cutoff) this.form.rail_cutoff = data.mbl.rail_cutoff;
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
                            {id: 'receipt_id', name: 'receipt_name', fallback: 'Place of Receipt'},
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
                            {id: 'return_location_id', name: 'return_location_name', fallback: 'Return Location'},
                            {id: 'empty_pickup_id', name: 'empty_pickup_name', fallback: 'Empty Pickup'},
                            {id: 'delivery_location_id', name: 'delivery_location_name', fallback: 'Delivery Location'}
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

                    // HBL Mapping
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
                            if (!this.form.dm_customer_id) this.form.dm_customer_id = hbl.consignee_id; 
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
                removeRoute(index) {
                    if (this.form.routes && this.form.routes[index] !== undefined) {
                        this.form.routes.splice(index, 1);
                    }
                },
                copyDescriptionFromAllHbl() {
                    const descs = (this.hbls || []).flatMap(h => (h.commodities || [])).map(c => c.commodity_desc || '').filter(Boolean);
                    if (descs.length) {
                        this.form.description = descs.join('; ');
                        if (typeof showToast === 'function') showToast('success', 'Descriptions copied from all HBLs.');
                    } else {
                        if (typeof showToast === 'function') showToast('info', 'No HBL descriptions found.');
                    }
                },
                getPoList(poStr) {
                    if (!poStr) return [];
                    return poStr.split(',').map(s => s.trim()).filter(s => s.length > 0);
                },
                addHblCommodity(hbl) {
                    if (!hbl.commodities) hbl.commodities = [];
                    hbl.commodities.push({
                        id: null,
                        selected: false,
                        hbl_no: hbl.hbl_no || '',
                        commodity_desc: '',
                        hts_code: '',
                        container_no: '',
                        po_no: ''
                    });
                },
                deleteSelectedHblCommodities(hbl) {
                    if (!hbl.commodities) return;
                    hbl.commodities = hbl.commodities.filter(c => !c.selected);
                },
                toggleAllHblCommodities(hbl, event) {
                    if (!hbl.commodities) return;
                    let checked = event.target.checked;
                    hbl.commodities.forEach(c => c.selected = checked);
                },
                copyContainersFromMbl(hbl) {
                    hbl.containers = (this.form.containers || []).map(c => ({
                        container_no: c.container_no || '',
                        manual_container_no: '',
                        pkg_qty: c.pkg_qty || '',
                        pkg_unit: 'CARTON(S)',
                        weight_kg: c.weight_kg || '',
                        weight_unit: 'KG',
                        measure_cbm: c.measure_cbm || '',
                        measure_unit: 'CBM',
                        po_no: ''
                    }));
                },
                calculateHblTotal(hbl, field) {
                    return (hbl.containers || []).reduce((sum, c) => sum + (parseFloat(c[field]) || 0), 0).toFixed(2);
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
                addHbl() {
                    this.hbls.push({
                        id: null,
                        show: true,
                        showMore: false,
                        showMemo: false,
                        show_containers: true,
                        hbl_no: '',
                        quotation_no: '',
                        customer_id: '',
                        sales_person_id: '',
                        shipper_id: '',
                        consignee_id: '',
                        notify_party_id: '',
                        customs_broker_id: '',
                        delivery_location_id: '',
                        referred_by_id: '',
                        pol_id: '',
                        pod_id: '',
                        del_id: '',
                        fdest_id: '',
                        receipt_id: '',
                        vessel_name: '',
                        voyage_no: '',
                        pre_carriage_by: '',
                        service_term: 'CY',
                        ship_mode: 'FCL',
                        ship_type: 'NORMAL',
                        cargo_type: 'GENERAL CARGO',
                        incoterms_id: '',
                        date_of_issue: '',
                        lc_no: '',
                        sc_no: '',
                        freight_payable_at: '',
                        is_express_bl: false,
                        is_door_move: false,
                        is_customs_clear: false,
                        is_customs_hold: false,
                        is_obl_received: false,
                        obl_received_date: '',
                        is_fr_released: false,
                        fr_released_date: '',
                        is_an_sent: false,
                        an_sent_date: '',
                        is_do_sent: false,
                        do_sent_date: '',
                        name_account: '',
                        group_comm: '',
                        line_code: '',
                        is_ecommerce: false,
                        is_customs_doc: false,
                        hbl_remark: '',
                        cfs_location_id: '',
                        freight_released_by_id: '',
                        po_no: '',
                        po_mapping_type: 'container',
                        hbl_mark: '',
                        hbl_description: '',
                        containers: [],
                        commodities: []
                    });
                    this.showMblSection = false;
                },
                removeHbl(idx) {
                    if(confirm('Are you sure you want to remove this HBL?')) {
                        this.hbls.splice(idx, 1);
                    }
                },
                validateForm() {
                    let errors = [];
                    
                    if (!this.form.mbl_no || !this.form.mbl_no.trim()) {
                        errors.push('MB/L No. is required');
                    }
                    
                    if (!this.form.office_id) {
                        errors.push('Office is required');
                    }
                    

                    this.form.containers.forEach((container, idx) => {
                        if (container.pkg_qty === null || container.pkg_qty === '' || container.pkg_qty === undefined) {
                            container.pkg_qty = 0;
                        }
                        if (container.weight_kg === null || container.weight_kg === '' || container.weight_kg === undefined) {
                            container.weight_kg = 0;
                        }
                        if (container.measure_cbm === null || container.measure_cbm === '' || container.measure_cbm === undefined) {
                            container.measure_cbm = 0;
                        }
                    });

                    if (errors.length > 0) {
                        errors.forEach(err => showToast('error', err));
                        return false;
                    }
                    return true;
                },
                saveShipment() {
                    // Handled by the standard form submission, triggered via @click in template.
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
                                id: null, selected: false, party: 'Custom', party_name_id: this.quoteForm.customer_id || '',
                                sal: 'Sea', pr: 'Rec', ppc: 'Colle',
                                chrg_code: item.charge_code, charge_name: item.charge_name,
                                currency: item.currency || 'USD', rate: item.rate,
                                qty: item.qty, qty_type: item.unit || 'UNIT',
                                roe: 1.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0],
                                eq_bl_no: '', remark: '', mbl_no: this.quoteForm.mbl_no || ''
                            });
                        });
                    }
                    this.showQuoteModal = false;
                    if (typeof showToast === 'function') {
                        showToast('success', 'Quotation data successfully loaded into shipment!');
                    }
                },
                saveShipment() {
                    // Let normal form submit handle it
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

                ToolsAction(action, hblIdx = 0) {
                    const shipmentId = this.form ? this.form.id : null;
                    if (!shipmentId && !['copy','batch_email','batch_print'].includes(action)) {
                        showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const headers = {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    };
                    const actions = {
                        unblock: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/ocean-export/bulk-unblock', {
                                method: 'POST',
                                headers: headers,
                                body: JSON.stringify({ ids: [shipmentId], type: 'mbl' })
                            }).then(async r => {
                                const d = await r.json().catch(() => ({}));
                                if (r.ok && (d.success !== false)) {
                                    this.form.is_blocked = false;
                                    this.form.is_hold = false;
                                    showToast('success', d.message || 'Shipment unblocked.');
                                    if (this.form.history) {
                                        this.form.history.unshift({
                                            date: new Date().toLocaleString(),
                                            user: '{{ auth()->user()->name ?? "User" }}',
                                            action: 'UNBLOCKED',
                                            details: 'Shipment unblocked via Tools menu.'
                                        });
                                    }
                                } else {
                                    showToast('error', d.message || 'Unblock failed.');
                                }
                            }).catch(() => showToast('error', 'Unblock request failed.'));
                        },
                        block: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/ocean-export/bulk-block', {
                                method: 'POST',
                                headers: headers,
                                body: JSON.stringify({ ids: [shipmentId], type: 'mbl' })
                            }).then(async r => {
                                const d = await r.json().catch(() => ({}));
                                if (r.ok && (d.success !== false)) {
                                    this.form.is_blocked = true;
                                    this.form.is_hold = true;
                                    showToast('success', d.message || 'Shipment blocked.');
                                    if (this.form.history) {
                                        this.form.history.unshift({
                                            date: new Date().toLocaleString(),
                                            user: '{{ auth()->user()->name ?? "User" }}',
                                            action: 'BLOCKED',
                                            details: 'Shipment blocked via Tools menu.'
                                        });
                                    }
                                } else {
                                    showToast('error', d.message || 'Block failed.');
                                }
                            }).catch(() => showToast('error', 'Block request failed.'));
                        },
                        copy: () => {
                            if (!shipmentId) return;
                            window.location.href = `/ocean-export/create?copy=${shipmentId}`;
                        },
                        copy_oi: () => {
                            if (!shipmentId) return;
                            window.location.href = `/ocean-import/create?copy_oe=${shipmentId}`;
                        },
                        delete: () => {
                            if (!shipmentId) return;
                            if (!confirm('Are you sure you want to delete this shipment? This action cannot be undone.')) return;
                            fetch(`/ocean-export/${shipmentId}`, {
                                method: 'DELETE',
                                headers: headers,
                                body: JSON.stringify({})
                            }).then(async r => {
                                const d = await r.json().catch(() => ({}));
                                if (r.ok && (d.success !== false)) {
                                    showToast('success', d.message || 'Shipment deleted successfully.');
                                    setTimeout(() => window.location.href = '/ocean-export/list', 800);
                                } else {
                                    showToast('error', d.message || 'Delete failed.');
                                }
                            }).catch(() => showToast('error', 'Delete failed.'));
                        },
                        edi: () => showToast('info', 'Opening FreightX EDI export...'),
                        doc_package: () => window.open(`/ocean-export/${shipmentId}/batch-print-view`, '_blank'),
                        hbl_print: () => window.open(`/ocean-export/${shipmentId}/hbl-print/${hblIdx}`, '_blank'),
                        mbl_print: () => window.open(`/ocean-export/${shipmentId}/mbl-print`, '_blank'),
                        delivery_order: () => window.open(`/ocean-export/${shipmentId}/delivery-order`, '_blank'),
                        manifest: () => window.open(`/ocean-export/${shipmentId}/dev-seg`, '_blank'),
                        profit_summary: () => window.open(`/ocean-export/${shipmentId}/profit-summary`, '_blank'),
                        profit_detail: () => window.open(`/ocean-export/${shipmentId}/profit-detail`, '_blank'),
                        telex_release: () => showToast('info', 'Telex Release - feature coming soon.'),
                        track_trace: () => {
                            window.openTrackTrace({ type: 'container', number: this.form.mbl_no || '' });
                        }
                    };
                    if (actions[action]) {
                        actions[action]();
                    } else {
                        showToast('info', 'Feature coming soon.');
                    }
                },

                hblBlockAction(idx) {
                    const hbl = this.hbls[idx];
                    if (!hbl || !hbl.id) { showToast('warning', 'Save the HB/L first.'); return; }
                    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    const endpoint = hbl.is_blocked ? '/ocean-export/hbl-bulk-unblock' : '/ocean-export/hbl-bulk-block';
                    fetch(endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ ids: [hbl.id] })
                    }).then(r => r.json()).then(d => {
                        if (d.success) {
                            hbl.is_blocked = !hbl.is_blocked;
                            showToast('success', hbl.is_blocked ? 'HB/L blocked.' : 'HB/L unblocked.');
                        } else {
                            showToast('error', d.message || 'Action failed.');
                        }
                    }).catch(() => showToast('error', 'Request failed.'));
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
                chargesList: @json($chargesList ?? []),
                saveAsDraftInvoice: false,
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
                        booking_no: "{{ addslashes($q->booking_no ?? '') }}",
                        po_no: "{{ addslashes($q->po_no ?? '') }}",
                        hts_code: "{{ addslashes($q->hts_code ?? '') }}",
                        pkg_qty: "{{ $q->pkg_qty ?? '' }}",
                        weight_kg: "{{ $q->weight_kg ?? '' }}",
                        volume_cbm: "{{ $q->volume_cbm ?? '' }}",
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
                        'selected' => true,
                    ])->values()->toArray()) !!},
                    @endforeach
                },

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

                toggleAllCharges(e) {
                    const isChecked = e ? e.target.checked : false;
                    this.chargesList.forEach(c => {
                        c._selected = isChecked;
                        c.selected = isChecked;
                    });
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

                openCertificateModal() {
                    alert('Certificate functionality - coming soon');
                },

                applyTemplate() {
                    if (!confirm('Are you sure you want to load default template charges?')) return;
                    if (this.form.id) {
                        fetch(`/ocean-export/${this.form.id}/charges/template`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Failed to apply template.');
                            }
                        });
                    } else {
                        this.setDefaultCharges();
                    }
                },

                copyFromQuote() {
                    let quoteId = prompt('Enter Quotation ID to copy charges from:');
                    if (!quoteId) return;
                    fetch(`/ocean-export/${this.form.id}/charges/copy-quote`, {
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
                            alert('Failed to copy charges.');
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

                    fetch(`/ocean-export/${this.form.id}/charges/invoice`, {
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
                            showToast('error', data.message || 'Failed to generate invoice');
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
                    window.open(`/shipments/ocean-export/${this.form.id}/freight-invoice`, '_blank');
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
                        alert('Please save shipment first.');
                        return;
                    }
                    window.open(`/shipments/ocean-export/${this.form.id}/freight-invoice`, '_blank');
                },

                prorataCharges() {
                    let chargeId = prompt('Enter Charge ID to prorate:');
                    if (!chargeId) return;
                    let basis = prompt('Enter prorate basis (volume / weight):', 'volume');
                    if (basis !== 'volume' && basis !== 'weight') {
                        alert('Invalid basis.');
                        return;
                    }
                    fetch(`/ocean-export/${this.form.id}/charges/prorata`, {
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
                            alert('Failed to prorate charges: ' + data.message);
                        }
                    });
                },

                setDefaultCharges() {
                    this.chargesList = [
                        { id: null, _selected: false, selected: false, party: 'Custom', party_name: '', sal: 'Sea', pr: 'Rec', ppc: 'Colle', chrg_code: 'OFC', currency: 'USD', rate: 50, qty: 1, qty_type: 'B/L', roe: 120.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: '' },
                        { id: null, _selected: false, selected: false, party: 'Custom', party_name: '', sal: 'Sea', pr: 'Rec', ppc: 'Colle', chrg_code: 'THC', currency: 'USD', rate: 10, qty: 1, qty_type: 'CBM', roe: 120.0, vat: 0, inv_no: '', financial_date: new Date().toISOString().split('T')[0], eq_bl_no: '', remark: false, mbl_no: '' }
                    ];
                },

                reloadCharges() {
                    if (confirm('Discard changes and reload charges from database?')) {
                        window.location.reload();
                    }
                },

                saveCharges() {
                    const formEl = document.querySelector('form[action*="ocean-export"]');
                    if (formEl) {
                        formEl.submit();
                    }
                },

                exportChargesToExcel() {
                    if (this.form.id) {
                        window.location.href = `/ocean-export/${this.form.id}/charges/export`;
                    } else {
                        alert('Save the shipment first before exporting charges.');
                    }
                },

                printCharges() {
                    if (this.form.id) {
                        window.open(`/ocean-export/${this.form.id}/charges/print`, '_blank');
                    } else {
                        alert('Save the shipment first before printing charges.');
                    }
                },

                deleteAllCharges() {
                    if (!confirm('Are you sure you want to delete all charges?')) return;
                    if (this.form.id) {
                        fetch(`/ocean-export/${this.form.id}/charges/all`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.chargesList = [];
                                alert('All charges deleted successfully.');
                            } else {
                                alert('Failed to delete charges.');
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
                            alert('Please select charges using checkbox or enter charge ID.');
                            return;
                        }
                    }
                    fetch(`/ocean-export/${this.form.id}/charges/duplicate`, {
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
                            alert('Failed to duplicate charges.');
                        }
                    });
                },

                bulkUpdateCurrency() {
                    let newCurrency = prompt('Enter new currency (USD/BDT/EUR/GBP):', 'USD');
                    if (newCurrency && ['USD','BDT','EUR','GBP'].includes(newCurrency.toUpperCase())) {
                        fetch(`/ocean-export/${this.form.id}/charges/bulk-currency`, {
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
                                alert('Currency updated successfully!');
                            } else {
                                alert('Failed to update currency.');
                            }
                        });
                    }
                },

                applyVatToAll() {
                    let vatPercent = prompt('Enter VAT percentage to apply to all charges (e.g., 15):', '0');
                    if (vatPercent !== null && !isNaN(parseFloat(vatPercent))) {
                        fetch(`/ocean-export/${this.form.id}/charges/apply-vat`, {
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
                                alert(`VAT ${vatPercent}% applied to all charges!`);
                            } else {
                                alert('Failed to apply VAT.');
                            }
                        });
                    }
                }
            }
        }
    </script>

    <div class="page-content" x-data="oceanExportModule()">
        <!-- Breadcrumbs -->
           <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/ocean-export/list">Ocean Export</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">{{ isset($oceanExport) ? 'Edit Shipment: ' . $oceanExport->file_no : 'New Shipment' }}</span></li>
            </ul>
        </div>

        <!-- Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h1 class="caption-subject" style="font-size: 18px;">{{ isset($oceanExport) ? 'OCEAN EXPORT SHIPMENT' : 'Create Ocean Export Shipment' }}</h1>
            <div style="display: flex; gap: 8px; align-items: center;">
               <button type="button" @click="showAiModal = true" class="frexo-trigger-btn" title="Auto-fill shipment details using Frexo AI">
                   <i class="fa fa-bolt"></i> Frexo AI
               </button>
               <button type="button" class="btn-freightx" @click="if(validateForm()) $el.closest('form').submit()"><i class="fa fa-save"></i> <span x-text="saved ? 'SAVE SHIPMENT' : 'SAVE MAIN'"></span></button>
                <a href="{{ route('ocean-export.index') }}" class="btn-default-gf">BACK TO LIST</a>
            </div>
        @include('partials.ai-modal', [
    'loadingVar' => 'aiLoading',
    'uploadMethod' => 'uploadAiDocument',
    'triggerMethod' => 'uploadAiDocument()',
    'fileInputRef' => '',
    'fileInputId' => 'aiDocumentFile'
])

           <!-- End AI Modal -->
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
                                    <li><button type="button" @click="saved ? ToolsAction('copy_oi') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-share" style="width:14px; text-align:center; margin-right:4px;"></i> Copy to OI</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('delete') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer; color:#ef4444;"><i class="fa fa-trash-o" style="width:14px; text-align:center; margin-right:4px; color:#ef4444;"></i> Delete</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('doc_package') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-folder-open-o" style="width:14px; text-align:center; margin-right:4px;"></i> Doc Package</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('mbl_print') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-print" style="width:14px; text-align:center; margin-right:4px;"></i> MB/L Print</button></li>
                                    <li><button type="button" @click="saved ? ToolsAction('telex_release') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-file-text" style="width:14px; text-align:center; margin-right:4px;"></i> Telex Release</button></li>
                                </div>
                            </template>
                            <li><button type="button" @click="saved ? ToolsAction('manifest') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-file-text-o" style="width:14px; text-align:center; margin-right:4px;"></i> Manifest</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('dev_seg') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-th-large" style="width:14px; text-align:center; margin-right:4px;"></i> DEV/SEG</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('delivery_order') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-truck" style="width:14px; text-align:center; margin-right:4px;"></i> Delivery Order</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('profit_summary') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-bar-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Summary</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('profit_detail') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-pie-chart" style="width:14px; text-align:center; margin-right:4px;"></i> Profit Detail</button></li>
                            <li><button type="button" @click="saved ? ToolsAction('track_trace') : null; showToolsMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; cursor:pointer;"><i class="fa fa-map-marker" style="width:14px; text-align:center; margin-right:4px;"></i> Track-Trace</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding-bottom: 50px;">
            <!-- BASIC TAB -->
            <div x-show="activeTab === 'basic'" class="main-grid">
                <div class="portlet light">
                    <div @click="showMblSection = !showMblSection" class="portlet-title" style="cursor: pointer; background: #f9fafb;">
                        <span class="caption-subject"><i class="fa" :class="showMblSection ? 'fa-minus-square-o' : 'fa-plus-square-o'"></i> MB/L</span>
                        <div class="actions">
                            <i class="fa fa-angle-down transition-transform" :class="showMblSection ? 'rotate-180' : ''"></i>
                        </div>
                    </div>
                    <div class="portlet-body" x-show="showMblSection" x-collapse>
                        <!-- Reminder Section for MBL (Hidden per client instruction) -->
                        <div class="memo-section" style="margin-bottom: 10px; display: none;">
                            <div class="memo-header" @click="showMblMemo = !showMblMemo">
                                <span>Note</span>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                   <button class="btn-memo-doc" @click.stop="" style="display: none;">Document (0) <i class="fa fa-external-link"></i></button>
                                    <i class="fa" :class="showMblMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                                </div>
                            </div>
                            <div class="memo-body" x-show="showMblMemo" x-collapse>
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
                        <!-- Hidden Fields Preserved for Backend & Form Submission -->
                        <div style="display: none;">
                            <input type="hidden" name="contract_no" x-model="form.contract_no">
                            <input type="hidden" name="carrier_id" x-model="form.carrier_id">
                            <input type="hidden" name="agent_ref_no" x-model="form.agent_ref_no">
                            <input type="hidden" name="acct_carrier_id" x-model="form.acct_carrier_id">
                            <input type="hidden" name="vessel_id" x-model="form.vessel_id">
                            <input type="hidden" name="voyage" x-model="form.voyage">
                            <input type="hidden" name="etd" x-model="form.etd">
                            <input type="hidden" name="eta" x-model="form.eta">
                            <input type="hidden" name="atd" x-model="form.atd">
                            <input type="hidden" name="ata" x-model="form.ata">
                        </div>

                        <!-- MBL CORE FIELDS GRID (Balanced 4-Column Layout) -->
                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Booking No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="file_no" class="form-control-gf" x-model="form.file_no" readonly style="background:#f5f5f5;">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="color:red;">* MB/L No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="mbl_no" class="form-control-gf" x-model="form.mbl_no" required>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="color:red;">* Office</label>
                                <div class="form-input-container">
                                    <select name="office_id" class="form-control-gf" x-model="form.office_id" required>
                                        <option value="">Select...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">B/L Type</label>
                                <div class="form-input-container">
                                    <select name="bl_type" class="form-control-gf" x-model="form.bl_type">
                                        <option value="NORMAL">NORMAL</option>
                                        <option value="MEMO">MEMO</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Post Date</label>
                                <div class="form-input-container">
                                    <input type="date" name="post_date" class="form-control-gf" x-model="form.post_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Booking No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="booking_no" class="form-control-gf" x-model="form.booking_no">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Sub B/L No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="sub_bl_no" class="form-control-gf" x-model="form.sub_bl_no">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Direct Master</label>
                                <div class="form-input-container">
                                    <input type="checkbox" name="is_direct_master" value="1" x-model="isDirectMaster">
                                    <input type="hidden" name="is_direct_master" :value="isDirectMaster ? 1 : 0">
                                </div>
                            </div>

                            <!-- Row 3 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Forwarding Agent</label>
                                <div class="form-input-container">
                                    <x-inline-select name="forwarding_agent_id" :options="$agents" module="trade-partner" x-model="form.forwarding_agent_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'forwarding_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Oversea Agent</label>
                                <div class="form-input-container">
                                    <x-inline-select name="oversea_agent_id" :options="$agents" module="trade-partner" x-model="form.oversea_agent_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'oversea_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Co-loader</label>
                                <div class="form-input-container">
                                    <x-inline-select name="co_loader_id" :options="$agents" module="trade-partner" x-model="form.co_loader_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'co_loader_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">OP</label>
                                <div class="form-input-container">
                                    <select name="op_id" class="form-control-gf" x-model="form.op_id">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- DIRECT MASTER CONDITIONAL FIELDS -->
                        <div x-show="isDirectMaster" x-transition style="margin-top: 4px; padding-top: 6px; border-top: 1px dashed #cbd5e1;">
                            <div class="form-grid-4">
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Customer</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="dm_customer_id" :options="$agents" module="trade-partner" x-model="form.dm_customer_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_customer_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Shipper</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="dm_shipper_id" :options="$agents" module="trade-partner" x-model="form.dm_shipper_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Consignee</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="dm_consignee_id" :options="$agents" module="trade-partner" x-model="form.dm_consignee_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_consignee_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Notify</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="dm_notify_id" :options="$agents" module="trade-partner" x-model="form.dm_notify_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_notify_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>

                                <div class="form-group-gf">
                                    <label class="form-label-gf">Customer Ref.</label>
                                    <div class="form-input-container">
                                        <input type="text" name="agent_ref_no" class="form-control-gf" x-model="form.agent_ref_no">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Sales</label>
                                    <div class="form-input-container">
                                        <select name="dm_sales_person_id" class="form-control-gf" x-model="form.dm_sales_person_id">
                                            <option value="">Select...</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Bill To</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="dm_bill_to_id" :options="$agents" module="trade-partner" x-model="form.dm_bill_to_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_bill_to_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Sales Type</label>
                                    <div class="form-input-container">
                                        <select name="sales_type" class="form-control-gf" x-model="form.sales_type">
                                            <option value="NORMAL">NORMAL</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group-gf">
                                    <label class="form-label-gf">Cargo Type</label>
                                    <div class="form-input-container">
                                        <select name="cargo_type" class="form-control-gf" x-model="form.cargo_type">
                                            <option value="GENERAL CARGO">GENERAL CARGO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"></div>
                                <div class="form-group-gf"></div>
                                <div class="form-group-gf"></div>
                            </div>
                        </div>

                        <!-- LOGISTICS & ROUTING GRID (Balanced 4-Column Layout) -->
                        <div class="form-grid-4" style="margin-top: 4px; padding-top: 4px; border-top: 1px solid #e2e8f0;">
                            <!-- Row 1: Ports -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Port of Loading</label>
                                <div class="form-input-container">
                                    <x-inline-select name="pol_id" :options="$ports" module="port" x-model="form.pol_id" class="form-control-gf" data-ajax-search="true" data-search-url="{{ route('ports.search') }}" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'pol_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Port of Discharge</label>
                                <div class="form-input-container">
                                    <x-inline-select name="pod_id" :options="$ports" module="port" x-model="form.pod_id" class="form-control-gf" data-ajax-search="true" data-search-url="{{ route('ports.search') }}" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'pod_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Place of Delivery</label>
                                <div class="form-input-container">
                                    <x-inline-select name="del_id" :options="$ports" module="port" x-model="form.del_id" class="form-control-gf" data-ajax-search="true" data-search-url="{{ route('ports.search') }}" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'del_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Final Destination</label>
                                <div class="form-input-container">
                                    <x-inline-select name="fdest_id" :options="$ports" module="port" x-model="form.fdest_id" class="form-control-gf" data-ajax-search="true" data-search-url="{{ route('ports.search') }}" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'fdest_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>

                            <!-- Row 2: Locations & Dates -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">CY Location</label>
                                <div class="form-input-container">
                                    <select name="cy_location_id" class="form-control-gf" x-model="form.cy_location_id">
                                        <option value="">Select...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">CFS Location</label>
                                <div class="form-input-container">
                                    <select name="cfs_location_id" class="form-control-gf" x-model="form.cfs_location_id">
                                        <option value="">Select...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" @click="openAddNewModal('trade-partner', 'cfs_location_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Final ETA</label>
                                <div class="form-input-container">
                                    <input type="date" name="final_eta" class="form-control-gf" x-model="form.final_eta">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">ETB</label>
                                <div class="form-input-container">
                                    <input type="date" name="etb" class="form-control-gf" x-model="form.etb">
                                </div>
                            </div>
                        </div>

                        <div style="height: 15px;"></div>

                        <!-- DYNAMIC ROUTE / TRANSPORTATION SECTION -->
                        <div class="portlet light bordered" style="margin-bottom: 15px;">
                            <div class="portlet-title" style="min-height: 30px; border-bottom: 1px solid #eee; margin-bottom: 0;">
                                <div class="caption" style="padding: 5px 0;">
                                    <span class="caption-subject" style="font-size: 13px; font-weight: bold; color: #32c5d2;"><i class="fa fa-ship"></i> Route / Transportation</span>
                                </div>
                            </div>
                            <div class="portlet-body" style="padding: 10px 0;">
                                <div style="overflow-x: auto;">
                                    <table class="table-custom" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%;"></th>
                                                <th style="width: 15%;">Port</th>
                                                <th style="width: 12%;">ETA</th>
                                                <th style="width: 12%;">ATA</th>
                                                <th style="width: 12%;">ETD</th>
                                                <th style="width: 12%;">ATD</th>
                                                <th style="width: 12%;">Vessel / Voyage</th>
                                                <th style="width: 10%;">Carrier</th>
                                                <th style="width: 5%; text-align: center;">
                                                    <button type="button" @click="addRoute" class="btn-tool-icon" style="color: #32c5d2;" title="Add Route Row"><i class="fa fa-plus"></i></button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(route, idx) in form.routes" :key="idx">
                                                <tr>
                                                    <td style="font-weight: 600; color: #475569;" x-text="idx === 0 ? 'Departure' : (idx === form.routes.length - 1 && form.routes.length > 1 ? 'Final Destination' : 'Trans ' + idx)"></td>
                                                    <td style="padding: 2px;">
    <div style="display:flex;">
        <select
            :name="'routes['+idx+'][airport_id]'"
            class="form-control-gf"
            x-model="route.airport_id"
            data-ajax-search="true"
            data-search-url="{{ route('ports.search') }}"
            style="width:100%; border:none; height:24px; padding:2px 4px;"
        >
            <option value="">Select...</option>

            @foreach($ports as $port)
                <option value="{{ $port->id }}">
                    {{ $port->code ? $port->code . ' - ' : '' }}{{ $port->name }}
                </option>
            @endforeach
        </select>
    </div>
</td>
                                                    <td style="padding: 2px;">
                                                        <input type="date" :name="'routes['+idx+'][eta]'" class="form-control-gf" x-model="route.eta" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="date" :name="'routes['+idx+'][ata]'" class="form-control-gf" x-model="route.ata" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="date" :name="'routes['+idx+'][etd]'" class="form-control-gf" x-model="route.etd" style="width:100%; border:none; height:24px; padding:2px 4px;">
                                                    </td>
                                                    <td style="padding: 2px;">
                                                        <input type="date" :name="'routes['+idx+'][atd]'" class="form-control-gf" x-model="route.atd" style="width:100%; border:none; height:24px; padding:2px 4px;">
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

                        <!-- Hidden Freight Fields Preserved for Backend -->
                        <div style="display: none;">
                            <input type="hidden" name="latest_gate_in" x-model="form.latest_gate_in">
                            <input type="hidden" name="business_referred_by_id" x-model="form.business_referred_by_id">
                        </div>

                        <!-- FREIGHT & SERVICE TERMS GRID (Balanced 4-Column Layout) -->
                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Freight</label>
                                <div class="form-input-container">
                                    <select name="freight_term" class="form-control-gf" x-model="form.freight_term">
                                        <option value="Prepaid">Prepaid</option>
                                        <option value="Collect">Collect</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Ship Mode</label>
                                <div class="form-input-container">
                                    <select name="ship_mode" class="form-control-gf" x-model="form.ship_mode">
                                        <option value="FCL">FCL</option>
                                        <option value="LCL">LCL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Service Term</label>
                                <div class="form-input-container">
                                    <select name="service_term_from_id" class="form-control-gf" style="width: 45%;" x-model="form.service_term_from_id">
                                        <option value="">Select...</option>
                                        @foreach($serviceTerms as $st)
                                            <option value="{{ $st->id }}">{{ $st->code }}</option>
                                        @endforeach
                                    </select>
                                    <span class="mx-1">~</span>
                                    <select name="service_term_to_id" class="form-control-gf" style="width: 45%;" x-model="form.service_term_to_id">
                                        <option value="">Select...</option>
                                        @foreach($serviceTerms as $st)
                                            <option value="{{ $st->id }}">{{ $st->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Container/Qty</label>
                                <div class="form-input-container">
                                    <input type="text" class="form-control-gf" :value="form.containers.length + ' Container(s)'" readonly style="background: #f8fafc;">
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">OB/L Type</label>
                                <div class="form-input-container">
                                    <select name="obl_type" class="form-control-gf" x-model="form.obl_type">
                                        <option value="ORIGINAL BILL OF LADING">ORIGINAL BILL OF LADING</option>
                                        <option value="SEA WAYBILL">SEA WAYBILL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="display:flex; align-items:center; justify-content:flex-end; gap:3px;">
                                    <input type="checkbox" name="is_obl_received" value="1" x-model="form.is_obl_received" class="mr-1">
                                    <span>OB/L Received</span>
                                </label>
                                <div class="form-input-container">
                                    <input type="date" name="obl_received_date" class="form-control-gf" x-model="form.obl_received_date">
                                    <i class="fa fa-calendar text-[10px] text-gray-400 cursor-pointer" @click="$el.previousElementSibling.showPicker()"></i>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="display:flex; align-items:center; justify-content:flex-end; gap:3px;">
                                    <input
                                        type="checkbox"
                                        name="is_released"
                                        value="1"
                                        x-model="form.is_released"
                                        @change="if (!form.is_released) form.released_date = null"
                                        class="mr-1"
                                    >
                                    <span>Released Date</span>
                                </label>
                                <div class="form-input-container">
                                    <input
                                        type="date"
                                        name="released_date"
                                        class="form-control-gf"
                                        x-model="form.released_date"
                                        :disabled="!form.is_released"
                                    >
                                    <i
                                        class="fa fa-calendar text-[10px] text-gray-400 cursor-pointer"
                                        @click="if (form.is_released) $el.previousElementSibling.showPicker()"
                                    ></i>
                                </div>
                            </div>
                            <div class="form-group-gf"></div>
                        </div>

<!-- Hidden per client instruction: More toggle, Place of Receipt, Place of Receipt ETD, Return Location, E-Commerce, Internal Remark -->
                        <div style="display: none;">
                            <div style="margin-bottom: 10px;">
                               <button type="button" @click="showMore = !showMore" class="btn-default-gf" style="border:none; color:#4b77be; font-weight:700;">
                                    <span x-text="showMore ? 'More [-]' : 'More [+]'"></span>
                                </button>
                            </div>

                            <div class="form-grid-4" x-show="showMore" x-transition>
                               <div class="form-group-gf">
    <label class="form-label-gf">Place of Receipt</label>
    <div class="form-input-container">
        <x-inline-select
            name="receipt_id"
            :options="$ports"
            module="port"
            x-model="form.receipt_id"
            class="form-control-gf"
            data-ajax-search="true"
            data-search-url="{{ route('ports.search') }}"
        />

        <button
            type="button"
            class="btn-default-gf"
            style="height:18px; padding:0 4px;"
            @click="openAddNewModal('port', 'receipt_id')"
        >
            <i class="fa fa-external-link" style="font-size:9px;"></i>
        </button>
    </div>
</div>
                                <div class="form-group-gf"><label class="form-label-gf">Place of Receipt ETD</label><div class="form-input-container"><input type="date" name="receipt_etd" class="form-control-gf" x-model="form.receipt_etd"> <i class="fa fa-calendar text-[10px] text-gray-400 cursor-pointer" @click="$el.previousElementSibling.showPicker()"></i></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Return Location</label><div class="form-input-container"><select name="return_location_id" class="form-control-gf" x-model="form.return_location_id"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">E-Commerce</label><div class="form-input-container" style="justify-content: flex-start;"><input type="checkbox" name="is_ecommerce" value="1" x-model="form.is_ecommerce" style="width: 14px; height: 14px;"></div></div>
                            </div>

                            <div style="margin-top: 10px; display: flex; gap: 20px;">
                                <div style="flex: 1;">
                                    <label class="form-label-gf">Internal Remark</label>
                                    <textarea name="internal_remark" class="form-control-gf" style="height: 60px;" x-model="form.internal_remark"></textarea>
                                </div>
                            </div>
                        </div>

                        </div>
                    </div>

                <!-- House B/L (HB/L) Section -->
             <template x-for="(hbl, index) in hbls" :key="index">
    <div
        class="portlet light"
        style="margin-top: 5px;"
        x-init="$nextTick(() => {
            if (typeof window.enhanceAllSelects === 'function') {
                window.enhanceAllSelects($el);
            }
        })"
    >
                        <div class="portlet-title" style="background: #f2bc00; color: #fff; cursor: pointer; min-height: 28px; padding: 2px 10px; display: flex; align-items: center; justify-content: space-between;" @click="hbl.show = !hbl.show">
                            <span class="caption-subject" style="color: #fff; font-size: 11px; font-weight: 600;"><i class="fa fa-user"></i> HB/L Information <small style="color:rgba(255,255,255,0.85); margin-left: 10px; font-weight: normal;">OP : {{ auth()->user()->name ?? 'DEMO_USER' }}</small></span>
                            <div class="actions" style="display: flex; gap: 8px; align-items: center;" @click.stop>
                                <!-- HBL Tools Dropdown -->
                                <div style="position: relative;" x-data="{ openHblMenu: false }" @click.away="openHblMenu = false">
                                    <button type="button" @click="openHblMenu = !openHblMenu"
                                        style="display: flex; align-items: center; gap: 4px; background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(0,0,0,0.1); border-radius: 3px; padding: 2px 8px; font-size: 11px; font-weight: 600; color: #374151; cursor: pointer; height: 22px;">
                                        <i class="fa fa-wrench" style="color: #4b5563; font-size: 10px;"></i> Docs <i class="fa fa-angle-down" style="font-size: 10px;"></i>
                                    </button>
                                    <div x-show="openHblMenu" style="position: absolute; top: 100%; right: 0; margin-top: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15); z-index: 100; min-width: 190px; text-align: left; display: none;">
                                        <ul style="list-style: none; padding: 4px 0; margin: 0;">
                                            <li><button type="button" @click="saved && hbls[index] && hbls[index].id ? hblBlockAction(index) : showToast('info', 'Save HB/L first.'); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-lock" style="width:14px; text-align:center; margin-right:4px;"></i> Block</button></li>
                                            <li><button type="button" @click="showToast('info', 'Copy - feature coming soon.'); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-copy" style="width:14px; text-align:center; margin-right:4px;"></i> Copy</button></li>
                                            <li><button type="button" @click="removeHbl(index); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#ef4444; cursor:pointer;"><i class="fa fa-trash-o" style="width:14px; text-align:center; margin-right:4px; color:#ef4444;"></i> Delete</button></li>
                                            <li><button type="button" @click="showToast('info', 'Copy To - feature coming soon.'); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-share" style="width:14px; text-align:center; margin-right:4px;"></i> Copy To</button></li>
                                            <li><button type="button" @click="ToolsAction('hbl_print', index); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-print" style="width:14px; text-align:center; margin-right:4px;"></i> HBL Print</button></li>
                                            <li><button type="button" @click="showToast('info', 'Pre-Alert - feature coming soon.'); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-bell-o" style="width:14px; text-align:center; margin-right:4px;"></i> Pre Alert</button></li>
                                            <li><button type="button" @click="showToast('info', 'Booking Confirmation - feature coming soon.'); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-calendar-check-o" style="width:14px; text-align:center; margin-right:4px;"></i> Booking Confirmation</button></li>
                                            <li><button type="button" @click="showToast('info', 'Forwarder Cargo Receipt - feature coming soon.'); openHblMenu = false" class="dropdown-item" style="width:100%; text-align:left; padding:6px 12px; border:none; background:none; font-size:11px; color:#374151; cursor:pointer;"><i class="fa fa-file-text-o" style="width:14px; text-align:center; margin-right:4px;"></i> Forwarder Cargo receipt</button></li>
                                        </ul>
                                    </div>
                                </div>
                                <i @click="removeHbl(index)" class="fa fa-times" style="font-size: 12px; opacity: 0.9; cursor: pointer; color: #fff;" title="Remove HB/L"></i>
                                <i class="fa fa-angle-down transition-transform" :class="hbl.show ? 'rotate-180' : ''" style="font-size: 12px; cursor: pointer; color: #fff;" @click="hbl.show = !hbl.show"></i>
                            </div>
                        </div>
                        <div class="portlet-body" x-show="hbl.show" x-collapse>
                            <!-- Reminder Section for HBL (Hidden per client instruction) -->
                            <div class="memo-section" style="margin-bottom: 10px; display: none;">
                                <div class="memo-header" @click="hbl.showMemo = !hbl.showMemo">
                                    <span>Note</span>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                       <button class="btn-memo-doc" @click.stop="" style="display: none;">Document (0) <i class="fa fa-external-link"></i></button>
                                        <i class="fa" :class="hbl.showMemo ? 'fa-angle-up' : 'fa-angle-down'"></i>
                                    </div>
                                </div>
                                <div class="memo-body" x-show="hbl.showMemo" x-collapse>
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
                                <input type="hidden" :name="'hbls['+index+'][id]'" :value="hbl.id">

                                <!-- Row 1: Identifiers & Terms -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf" style="color:red;">*HB/L No.</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][hbl_no]'" class="form-control-gf" x-model="hbl.hbl_no" required>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Quotation No.</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][quotation_no]'" class="form-control-gf" x-model="hbl.quotation_no" @change="loadQuoteToHbl(index)">
                                            <option value="">Select...</option>
                                            @foreach($quotations as $q)
                                                <option value="{{ $q->quote_no }}">{{ $q->quote_no }} - {{ $q->customer->name ?? '' }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" onclick="window.open('/sales/quotation/create', '_blank')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Service Term</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][service_term]'" class="form-control-gf" x-model="hbl.service_term">
                                            <option value="">Select...</option>
                                            @foreach($serviceTerms as $st)
                                                <option value="{{ $st->code }}">{{ $st->code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Ship Type</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][ship_type]'" class="form-control-gf" x-model="hbl.ship_type">
                                            <option value="">Select...</option>
                                            <option value="FCL">FCL</option>
                                            <option value="LCL">LCL</option>
                                            <option value="NORMAL">NORMAL</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 2: Main Parties -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Customer</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="" x-bind:name="'hbls['+index+'][customer_id]'" :options="$agents" module="trade-partner" x-model="hbl.customer_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][customer_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Shipper</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="" x-bind:name="'hbls['+index+'][shipper_id]'" :options="$agents" module="trade-partner" x-model="hbl.shipper_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][shipper_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Consignee</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="" x-bind:name="'hbls['+index+'][consignee_id]'" :options="$agents" module="trade-partner" x-model="hbl.consignee_id" class="form-control-gf" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][consignee_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Notify Party</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][notify_party_id]'" class="form-control-gf" x-model="hbl.notify_party_id">
                                            <option value="">Select...</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][notify_party_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>

                                <!-- Row 3: Operations & Agents -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Sales</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][sales_person_id]'" class="form-control-gf" x-model="hbl.sales_person_id">
                                            <option value="">Select...</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Customs Broker</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][customs_broker_id]'" class="form-control-gf" x-model="hbl.customs_broker_id">
                                            <option value="">Select...</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][customs_broker_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Delivery Loc.</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][delivery_location_id]'" class="form-control-gf" x-model="hbl.delivery_location_id">
                                            <option value="">Select...</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][delivery_location_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Released By</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][freight_released_by_id]'" class="form-control-gf" x-model="hbl.freight_released_by_id">
                                            <option value="">Select...</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 4: Ports Sequence -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Place of Receipt</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="" x-bind:name="'hbls['+index+'][receipt_id]'" :options="$ports" module="port" x-model="hbl.receipt_id" class="form-control-gf" data-ajax-search="true" data-search-url="{{ route('ports.search') }}" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][receipt_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Port of Loading</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][pol_id]'" class="form-control-gf" x-model="hbl.pol_id" data-ajax-search="true" data-search-url="{{ route('ports.search') }}">
                                            <option value="">Select...</option>
                                            @foreach($ports as $port)
                                                <option value="{{ $port->id }}">{{ $port->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Port of Discharge</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][pod_id]'" class="form-control-gf" x-model="hbl.pod_id" data-ajax-search="true" data-search-url="{{ route('ports.search') }}">
                                            <option value="">Select...</option>
                                            @foreach($ports as $port)
                                                <option value="{{ $port->id }}">{{ $port->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Place of Delivery</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="" x-bind:name="'hbls['+index+'][del_id]'" :options="$ports" module="port" x-model="hbl.del_id" class="form-control-gf" data-ajax-search="true" data-search-url="{{ route('ports.search') }}" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'hbls['+index+'][del_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>

                                <!-- Row 5: Destination & Vessel Transit -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Final Destination</label>
                                    <div class="form-input-container">
                                        <x-inline-select name="" x-bind:name="'hbls['+index+'][fdest_id]'" :options="$ports" module="port" x-model="hbl.fdest_id" class="form-control-gf" data-ajax-search="true" data-search-url="{{ route('ports.search') }}" />
                                        <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'fdest_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">CY/CFS Loc.</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][cfs_location_id]'" class="form-control-gf" x-model="hbl.cfs_location_id">
                                            <option value="">Select...</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" @click="openAddNewModal('trade-partner', 'hbls['+index+'][cfs_location_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">VESSEL</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][vessel_name]'" class="form-control-gf" x-model="hbl.vessel_name">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">VOYAGE NO</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][voyage_no]'" class="form-control-gf" x-model="hbl.voyage_no">
                                    </div>
                                </div>

                                <!-- Row 6: Commercial & Classification -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">INCOTERMS</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][incoterms_id]'" class="form-control-gf" x-model="hbl.incoterms_id">
                                            <option value="">Select...</option>
                                            @foreach($incoterms as $incoterm)
                                                <option value="{{ $incoterm->code }}">{{ $incoterm->code }} - {{ $incoterm->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">CARGO TYPE</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][cargo_type]'" class="form-control-gf" x-model="hbl.cargo_type">
                                            <option value="">Select...</option>
                                            <option value="GENERAL CARGO">GENERAL CARGO</option>
                                            <option value="HAZARDOUS">HAZARDOUS</option>
                                            <option value="REEFER">REEFER</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">HBL Freight</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][hbl_freight]'" class="form-control-gf" x-model="hbl.hbl_freight">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Freight Payable</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][freight_payable_at]'" class="form-control-gf" x-model="hbl.freight_payable_at">
                                    </div>
                                </div>

                                <!-- Row 7: Contracts, Status & Tracking -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">S/C No</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][sc_no]'" class="form-control-gf" x-model="hbl.sc_no">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">L/C No</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][lc_no]'" class="form-control-gf" x-model="hbl.lc_no">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Date of Issue</label>
                                    <div class="form-input-container">
                                        <input type="date" :name="'hbls['+index+'][date_of_issue]'" class="form-control-gf" x-model="hbl.date_of_issue">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf" style="display:flex; align-items:center; justify-content:flex-end; gap:3px;">
                                        <input type="checkbox" :name="'hbls['+index+'][is_do_sent]'" value="1" x-model="hbl.is_do_sent">
                                        <span>DO Sent</span>
                                    </label>
                                    <div class="form-input-container">
                                        <input type="date" :name="'hbls['+index+'][do_sent_date]'" class="form-control-gf" x-model="hbl.do_sent_date">
                                    </div>
                                </div>

                                <!-- Row 8: Pre-carriage & Rail -->
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Pre-Carriage</label>
                                    <div class="form-input-container">
                                        <input type="text" :name="'hbls['+index+'][pre_carriage_by]'" class="form-control-gf" x-model="hbl.pre_carriage_by">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf" style="display:flex; align-items:center; justify-content:flex-end; gap:3px;">
                                        <input type="checkbox" :name="'hbls['+index+'][is_rail]'" value="1" x-model="hbl.is_rail">
                                        <span>Rail</span>
                                    </label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][pre_carriage_by]'" class="form-control-gf" x-model="hbl.pre_carriage_by">
                                            <option value="">Select...</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->name }}">{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Ship Mode</label>
                                    <div class="form-input-container">
                                        <select :name="'hbls['+index+'][ship_mode]'" class="form-control-gf" x-model="hbl.ship_mode">
                                            <option value="">Select...</option>
                                            <option value="FCL">FCL</option>
                                            <option value="LCL">LCL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf"></div>
                            </div>

                            <div style="display:flex; justify-content:flex-end; margin-top: 10px;">
                                <button type="button" @click="hbl.showMore = !hbl.showMore" class="btn-default-gf" style="border:none; color:#00827f; font-weight:700; height:18px; padding:0;">
                                    More <i class="fa" :class="hbl.showMore ? 'fa-minus-square' : 'fa-plus-square'"></i>
                                </button>
                            </div>

                            <!-- More Section for HBL -->
                            <div x-show="hbl.showMore" x-transition style="margin-top: 5px; padding-top: 5px; border-top: 1px solid #eee;">
                                <div class="form-grid-4">
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Name Account</label>
                                        <div class="form-input-container">
                                            <input type="text" :name="'hbls['+index+'][name_account]'" class="form-control-gf" x-model="hbl.name_account">
                                        </div>
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Group Comm</label>
                                        <div class="form-input-container">
                                            <input type="text" :name="'hbls['+index+'][group_comm]'" class="form-control-gf" x-model="hbl.group_comm">
                                        </div>
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Line Code</label>
                                        <div class="form-input-container">
                                            <input type="text" :name="'hbls['+index+'][line_code]'" class="form-control-gf" x-model="hbl.line_code">
                                        </div>
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">E-Commerce</label>
                                        <div class="form-input-container">
                                            <input type="checkbox" :name="'hbls['+index+'][is_ecommerce]'" value="1" x-model="hbl.is_ecommerce">
                                        </div>
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Customs Doc</label>
                                        <div class="form-input-container">
                                            <input type="checkbox" :name="'hbls['+index+'][is_customs_doc]'" value="1" x-model="hbl.is_customs_doc">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                </template>

                <div class="flex justify-end" style="margin-top: 5px;">
                   <button type="button" @click="addHbl" class="btn-freightx" style="background:#f2bc00; padding: 4px 15px; font-size: 11px; border-radius: 2px;"><i class="fa fa-plus"></i> ADD HB/L</button>
                </div>
            </div>

            <!-- CONTAINER & ITEMS TAB -->
            <div x-show="activeTab === 'container'" class="main-grid">
                <div class="portlet light">
                    <div class="portlet-title">
                        <span class="caption-subject"><i class="fa fa-cube"></i> Container List</span>
                    </div>
                    <div class="portlet-body">
                        <div class="container-toolbar">
                            <div style="display: flex; gap: 4px; align-items: center;">
                               <button type="button" @click="addContainer()" class="btn-tool" title="Add Row"><i class="fa fa-plus"></i> Add Row</button>
                               <button type="button" @click="addContainer(5)" class="btn-tool" style="background:#64748b; color:#fff;" title="Add 5 Rows"><i class="fa fa-plus"></i> Add 5 Rows</button>
                               <button type="button" @click="addBulkContainers" class="btn-tool-icon" title="Add Bulk"><i class="fa fa-plus-square"></i></button>
                               <button type="button" @click="duplicateSelectedContainers" class="btn-tool-icon" title="Duplicate"><i class="fa fa-copy"></i></button>
                               <button type="button" @click="deleteSelectedContainers" class="btn-tool-icon" style="color:red; border-color:red;" title="Delete Selected"><i class="fa fa-trash"></i></button>
                                                      <!-- Hidden per client instruction: Import Container, Create A/P, Copy Data from All HB/L, Container info to clipboard, purple button -->
                            <div style="display: none;">
                                <button type="button" @click="document.getElementById('container-import-input').click()" class="btn-tool"><i class="fa fa-cloud-upload"></i> Import Container</button>
                                <input type="file" id="container-import-input" accept=".csv,.xlsx,.xls" @change="handleContainerImport" style="display:none;">
                                <button type="button" class="btn-tool-outline">Create A/P <i class="fa fa-angle-down"></i></button>
                                <button type="button" @click="copyDataFromAllHbl" class="btn-tool-outline" style="color:#4b77be; border-color:#4b77be;">Copy Data from All HB/L</button>
                                <button type="button" @click="showClipboardModal = true" class="btn-tool-outline">Container info to clipboard <i class="fa fa-external-link"></i></button>
                                <button type="button" class="btn-tool-secondary" style="background: #9b59b6;"><i class="fa fa-sign-out"></i></button>
                            </div>
                        </div>
                    </div>

                        <div class="table-responsive">
                            <table class="container-table">
                                <colgroup>
                                    <col style="width: 30px;">
                                    <col style="width: 60px;">
                                    <col style="width: 160px;">
                                    <col style="width: 80px;">
                                    <col style="width: 100px;">
                                    <col style="width: 100px;">
                                    <col style="width: 100px;">
                                    <col style="width: 120px;">
                                    <col style="width: 100px;">
                                    <col style="width: 100px;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" @change="toggleAllContainers"></th>
                                        <th>PP/CTF</th>
                                        <th>Container No.</th>
                                        <th>TP/SZ</th>
                                        <th>Seal No.</th>
                                        <th>LFD</th>
                                        <th>FDD</th>
                                        <th>
                                            <div class="header-split">
                                                <div class="header-top">PKG</div>
                                                <div class="header-bottom">CARTON(S)</div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="header-split">
                                                <div class="header-top">Weight</div>
                                                <div class="header-bottom">KG</div>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="header-split">
                                                <div class="header-top" style="font-size:9px;">Measurement</div>
                                                <div class="header-bottom">CBM</div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <template x-for="(cont, idx) in form.containers" :key="idx">
                                    <tbody style="border:none;">
                                        <tr class="row-main">
                                            <input type="hidden" :name="'containers['+idx+'][id]'" :value="cont.id">
                                            <td style="width:30px;"><input type="checkbox" x-model="cont.selected" style="display:block; margin:auto;"></td>
                                            <td style="width:60px;"><input type="text" :name="'containers['+idx+'][pp_ctf]'" class="form-control-gf" x-model="cont.pp_ctf"></td>
                                            <td style="width:160px;">
                                                <div class="flex items-center gap-1">
                                                    <input type="text" :name="'containers['+idx+'][container_no]'" class="form-control-gf" x-model="cont.container_no">
                                                   <button type="button" @click.stop="cont.expanded = !cont.expanded" class="btn-default-gf" style="padding: 0 4px; height: 16px; line-height: 1; min-width: 16px; border-radius:0; background:#fff; border:1px solid #ccc; display: none;">
                                                        <i class="fa fa-minus text-gray-500" style="font-size:10px;"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td style="width:80px;"><select :name="'containers['+idx+'][container_type_id]'" class="form-control-gf" x-model="cont.container_type_id"><option value="">Select...</option>@foreach($containerTypes as $ct)<option value="{{ $ct->id }}">{{ $ct->code }}</option>@endforeach</select></td>
                                            <td style="width:100px;"><input type="text" :name="'containers['+idx+'][seal_no]'" class="form-control-gf" x-model="cont.seal_no"></td>
                                            <td style="width:100px;"><input type="date" :name="'containers['+idx+'][lfd]'" class="form-control-gf" x-model="cont.lfd"></td>
                                            <td style="width:100px;"><input type="date" :name="'containers['+idx+'][fdd]'" class="form-control-gf" x-model="cont.fdd"></td>
                                            <td style="width:120px;"><input type="number" :name="'containers['+idx+'][pkg_qty]'" class="form-control-gf" x-model="cont.pkg_qty" style="text-align:right;"></td>
                                            <td style="width:100px;"><input type="number" :name="'containers['+idx+'][weight_kg]'" class="form-control-gf" x-model="cont.weight_kg" step="0.01" style="text-align:right;"></td>
                                            <td style="width:100px;"><input type="number" :name="'containers['+idx+'][measure_cbm]'" class="form-control-gf" x-model="cont.measure_cbm" step="0.01" style="text-align:right;"></td>
                                        </tr>
                                        <tr style="display: none !important;" class="expanded-row">
                                            <td colspan="1" style="border-right: 1px solid #dcdcdc; background:#fff !important;"></td>
                                            <td colspan="9">
                                                <div class="expanded-container">
                                                    <!-- Group 1: Occupies space of Col 3-4 (60+160 = 220px) -->
                                                    <div class="expanded-col" style="width: 220px;">
                                                        <div class="form-group-gf"><label class="form-label-gf">Seal No2.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][seal_no2]'" class="form-control-gf" x-model="cont.seal_no2"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">Pick Up No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][pickup_no]'" class="form-control-gf" x-model="cont.pickup_no"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">CPRS No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][cprs_no]'" class="form-control-gf" x-model="cont.cprs_no"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">CNRU No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][cnru_no]'" class="form-control-gf" x-model="cont.cnru_no"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">IT No.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][it_no]'" class="form-control-gf" x-model="cont.it_no"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">D.G</label><div class="form-input-container"><select :name="'containers['+idx+'][is_dg]'" class="form-control-gf" x-model="cont.is_dg"><option value="0">No</option><option value="1">Yes</option></select></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">Storage Start</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][storage_start_date]'" class="form-control-gf" x-model="cont.storage_start_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">Storage End</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][storage_end_date]'" class="form-control-gf" x-model="cont.storage_end_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">Weight LB</label><div class="form-input-container"><input type="number" :name="'containers['+idx+'][weight_lb]'" class="form-control-gf" x-model="cont.weight_lb" step="0.01"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf">Measure CFT</label><div class="form-input-container"><input type="number" :name="'containers['+idx+'][measure_cft]'" class="form-control-gf" x-model="cont.measure_cft" step="0.01"></div></div>
                                                        <div class="mt-2">
                                                            <div class="text-[9px] font-bold text-gray-500">Remarks</div>
                                                            <textarea :name="'containers['+idx+'][remarks]'" class="form-control-gf" x-model="cont.remarks"></textarea>
                                                        </div>
                                                        <div class="mt-1">
                                                            <div class="text-[9px] font-bold text-gray-500">Internal Remarks</div>
                                                            <textarea :name="'containers['+idx+'][internal_remarks]'" class="form-control-gf" x-model="cont.internal_remarks"></textarea>
                                                        </div>
                                                    </div>
                                                    <!-- Group 2: Occupies space of Col 5-6 (80+100 = 180px) -->
                                                    <div class="expanded-col" style="width: 180px;">
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Carrier rel.</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_carrier_release]'" value="1" x-model="cont.is_carrier_release" style="width:12px;height:12px;"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Yard Loc.</label><div class="form-input-container"><input type="text" :name="'containers['+idx+'][yard_location]'" class="form-control-gf" x-model="cont.yard_location"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Unload Vessel</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][unload_vessel_date]'" class="form-control-gf" x-model="cont.unload_vessel_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Gate In</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][gate_in_date]'" class="form-control-gf" x-model="cont.gate_in_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Rail Start</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][rail_start_date]'" class="form-control-gf" x-model="cont.rail_start_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">P.O.D ETA</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][pod_eta]'" class="form-control-gf" x-model="cont.pod_eta"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Avail Pickup</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_avail_pickup]'" value="1" x-model="cont.is_avail_pickup" style="width:12px;height:12px;"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Appt.</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][appointment_date]'" class="form-control-gf" x-model="cont.appointment_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Trucker</label><div class="form-input-container"><select :name="'containers['+idx+'][trucker_id]'" class="form-control-gf" x-model="cont.trucker_id"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select> <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" @click="openAddNewModal('trade-partner', 'containers['+idx+'][trucker_id]')"><i class="fa fa-external-link" style="font-size:9px;"></i></button></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Pick Up</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][pickup_date]'" class="form-control-gf" x-model="cont.pickup_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Gate Out</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][gate_out_date]'" class="form-control-gf" x-model="cont.gate_out_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">F.Dest ETA</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][fdest_eta]'" class="form-control-gf" x-model="cont.fdest_eta"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">ETA Door</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][eta_door]'" class="form-control-gf" x-model="cont.eta_door"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">ATA Door</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][ata_door]'" class="form-control-gf" x-model="cont.ata_door"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Empty Conf.</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][empty_conf_date]'" class="form-control-gf" x-model="cont.empty_conf_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Empty Ret.</label><div class="form-input-container"><input type="date" :name="'containers['+idx+'][empty_ret_date]'" class="form-control-gf" x-model="cont.empty_ret_date"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Chassis days</label><div class="form-input-container"><input type="number" :name="'containers['+idx+'][chassis_days]'" class="form-control-gf" x-model="cont.chassis_days" step="0.1"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">C.Hold</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_customs_hold]'" value="1" x-model="cont.is_customs_hold" style="width:12px;height:12px;"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">A/N</label><div class="form-input-container" style="gap:2px;"><input type="checkbox" :name="'containers['+idx+'][is_an_sent]'" value="1" x-model="cont.is_an_sent" style="width:12px;height:12px;flex-shrink:0;"><input type="date" :name="'containers['+idx+'][an_sent_date]'" class="form-control-gf" x-model="cont.an_sent_date" style="flex:1;"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">D/O</label><div class="form-input-container" style="gap:2px;"><input type="checkbox" :name="'containers['+idx+'][is_do_sent]'" value="1" x-model="cont.is_do_sent" style="width:12px;height:12px;flex-shrink:0;"><input type="date" :name="'containers['+idx+'][do_sent_date]'" class="form-control-gf" x-model="cont.do_sent_date" style="flex:1;"></div></div>
                                                        <div class="form-group-gf"><label class="form-label-gf" style="width:65px;">Complete</label><div class="form-input-container"><input type="checkbox" :name="'containers['+idx+'][is_complete]'" value="1" x-model="cont.is_complete" style="width:12px;height:12px;"></div></div>
                                                    </div>
                                                    <!-- Group 3: Occupies space of Col 7-11 (100+100+120+100+100 = 520px) -->
                                                    <div class="expanded-col flex-1" style="background: #fff; border-right:none;">
                                                        <div class="hbl-header">HB/L No.</div>
                                                        <div style="border: 1px solid #eee; min-height: 200px; background: #fff;">
                                                            <!-- HB/L List Content -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </template>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="5" style="border:none;"></td>
                                        <td colspan="2" class="total-label-cell">Total</td>
                                        <td class="total-val-cell"><span x-text="calculateTotal('pkg_qty')"></span></td>
                                        <td class="total-val-cell"><span x-text="calculateTotal('weight_kg').toFixed(2)"></span></td>
                                        <td class="total-val-cell"><span x-text="calculateTotal('measure_cbm').toFixed(2)"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px; font-size: 11px;">
                            <span style="color:#333;">Total</span>
                            <div class="flex items-center gap-1">
                                <input type="checkbox" id="input-total-new">
                                <label for="input-total-new" style="font-weight:normal; color:#555;">Input total number</label>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 5px; align-items: center; gap: 10px;">
                            <label class="form-label-gf">Display Unit</label>
                            <select class="form-control-gf" style="width: 150px;"><option>Show Both</option></select>
                        </div>

                        <div style="display: flex; gap: 20px; margin-top: 15px;">
                            <div style="flex: 1;">
                                <label class="caption-subject" style="font-size: 11px; margin-bottom: 5px; display: block;">Mark</label>
                                <textarea name="mark" class="form-control-gf" style="height: 80px;" x-model="form.mark"></textarea>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <label class="caption-subject" style="font-size: 11px;">Description</label>
                                   <button type="button" @click="copyDescriptionFromAllHbl" class="btn-tool" style="padding: 2px 8px; font-size: 10px;">Copy from All HB/L</button>
                                </div>
                                <textarea name="description" class="form-control-gf" style="height: 80px;" x-model="form.description"></textarea>
                            </div>
                        </div>
                </div>
            </div>

            <!-- HBL Containers & Items Sections -->
            <template x-for="(hbl, hblIdx) in hbls" :key="hblIdx">
                <div class="portlet light" style="margin-top: 15px; border: 1px solid #f2bc00;">
                    <!-- Theme Header Bar -->
                    <div class="portlet-title" style="background: #f2bc00; color: #fff; cursor: pointer; min-height: 28px; padding: 4px 10px; display: flex; justify-content: space-between; align-items: center;" @click="hbl.show_containers = !hbl.show_containers">
                        <span class="caption-subject" style="color: #fff; font-size: 12px; font-weight: bold;">
                            <i class="fa fa-cube"></i> HB/L: <span x-text="hbl.hbl_no || 'Draft HBL'"></span> | Containers & Items
                        </span>
                        <div class="actions" style="display: flex; gap: 10px; align-items: center;">
                            <i class="fa fa-angle-down" :class="hbl.show_containers ? 'rotate-180' : ''" style="font-size: 14px; color: #fff; transition: transform 0.2s;"></i>
                        </div>
                    </div>

                    <div class="portlet-body" x-show="hbl.show_containers !== false" x-collapse style="padding: 12px;">
                        <!-- Customer Reference / P.O. No. -->
                        <div class="flex justify-between items-center" style="margin-bottom: 12px; gap: 20px;">
                            <div style="flex: 1;">
                                <label class="form-label-gf" style="font-weight: bold; margin-bottom: 4px; display: block;">Customer Reference / P.O. No. <span style="font-weight: normal; color: #666;">(Please list down P.O. No. for this HB/L)</span></label>
                                <input type="text" :name="'hbls['+hblIdx+'][po_no]'" class="form-control-gf" placeholder="Add P.O. here..." x-model="hbl.po_no" style="width: 100%; height: 22px;">
                            </div>
                            <div style="width: 200px; text-align: right;">
                                <span style="font-weight: bold; font-size: 11px; display: block; margin-bottom: 4px;">P.O. Mapping</span>
                                <div class="flex gap-4 justify-end" style="font-size: 11px;">
                                    <label class="flex items-center gap-1 cursor-pointer" style="font-weight: normal;">
                                        <input type="radio" :name="'hbls['+hblIdx+'][po_mapping_type]'" value="container" x-model="hbl.po_mapping_type"> Container based
                                    </label>
                                    <label class="flex items-center gap-1 cursor-pointer" style="font-weight: normal;">
                                        <input type="radio" :name="'hbls['+hblIdx+'][po_mapping_type]'" value="item" x-model="hbl.po_mapping_type"> Item based
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Container List -->
                        <div style="margin-bottom: 15px;">
                            <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                                <span style="font-weight: bold; font-size: 12px; color: #333;">Container List</span>
                                <button type="button" @click="copyContainersFromMbl(hbl)" class="btn-tool-outline" style="color:#f2bc00; border-color:#f2bc00; padding: 2px 10px; font-size: 11px;">
                                    <i class="fa fa-copy"></i> Copy Value from MB/L
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="container-table" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">#</th>
                                            <th>Container No.</th>
                                            <th style="width: 150px;">PKG</th>
                                            <th style="width: 150px;">Weight</th>
                                            <th style="width: 150px;">Measurement</th>
                                            <th x-show="hbl.po_mapping_type === 'container'">P.O. No.</th>
                                            <th style="width: 40px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(c, cIdx) in hbl.containers" :key="cIdx">
                                            <tr>
                                                <td style="text-align: center; font-weight: bold;" x-text="cIdx + 1"></td>
                                                <td>
                                                    <select class="form-control-gf" x-model="c.container_no" :name="c.container_no !== 'MANUAL' ? 'hbls['+hblIdx+'][containers]['+cIdx+'][container_no]' : ''">
                                                        <option value="">Select MBL Container...</option>
                                                        <template x-for="mblC in form.containers">
                                                            <option :value="mblC.container_no" x-text="mblC.container_no"></option>
                                                        </template>
                                                        <option value="MANUAL">+ Enter Manual</option>
                                                    </select>
                                                    <input type="text" class="form-control-gf mt-1" placeholder="Enter Container No." x-show="c.container_no === 'MANUAL'" x-model="c.manual_container_no" :name="c.container_no === 'MANUAL' ? 'hbls['+hblIdx+'][containers]['+cIdx+'][container_no]' : ''">
                                                </td>
                                                <td>
                                                    <div class="flex gap-1">
                                                        <input type="number" class="form-control-gf" style="width: 60px; text-align: right;" x-model="c.pkg_qty" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][pkg_qty]'">
                                                        <select class="form-control-gf" style="flex: 1;" x-model="c.pkg_unit" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][pkg_unit]'">
                                                            @foreach($packageUnits as $pu)
                                                                <option value="{{ $pu->code }}">{{ $pu->code }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="flex gap-1">
                                                        <input type="number" class="form-control-gf" style="width: 60px; text-align: right;" step="0.01" x-model="c.weight_kg" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][weight_kg]'">
                                                        <select class="form-control-gf" style="flex: 1;" x-model="c.weight_unit" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][weight_unit]'">
                                                            <option value="KG">KG</option>
                                                            <option value="LBS">LBS</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="flex gap-1">
                                                        <input type="number" class="form-control-gf" style="width: 60px; text-align: right;" step="0.01" x-model="c.measure_cbm" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][measure_cbm]'">
                                                        <select class="form-control-gf" style="flex: 1;" x-model="c.measure_unit" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][measure_unit]'">
                                                            <option value="CBM">CBM</option>
                                                            <option value="CFT">CFT</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td x-show="hbl.po_mapping_type === 'container'">
                                                    <!-- P.O. Selector for Container-based Mapping -->
                                                    <select class="form-control-gf" x-model="c.po_no" :name="'hbls['+hblIdx+'][containers]['+cIdx+'][po_no]'">
                                                        <option value="">Select PO...</option>
                                                        <template x-for="po in getPoList(hbl.po_no)">
                                                            <option :value="po" x-text="po"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                                <td style="text-align: center;">
                                                    <button type="button" @click="hbl.containers.splice(cIdx, 1)" class="btn-tool-icon" style="color: red; border:none; background:none;" title="Delete row">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="!hbl.containers || hbl.containers.length === 0">
                                            <tr>
                                                <td :colspan="hbl.po_mapping_type === 'container' ? 7 : 6" style="text-align: center; color: #999; padding: 10px;">No containers assigned yet. Click "Copy Value from MB/L" or add a row.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row" style="background: #fdfaf0;">
                                            <td colspan="2" style="font-weight: bold; text-align: right;">Total</td>
                                            <td style="font-weight: bold; text-align: right;" x-text="calculateHblTotal(hbl, 'pkg_qty')"></td>
                                            <td style="font-weight: bold; text-align: right;" x-text="calculateHblTotal(hbl, 'weight_kg')"></td>
                                            <td style="font-weight: bold; text-align: right;" x-text="calculateHblTotal(hbl, 'measure_cbm')"></td>
                                            <td :colspan="hbl.po_mapping_type === 'container' ? 2 : 1"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-2">
                                <button type="button" @click="hbl.containers.push({container_no:'', manual_container_no:'', pkg_qty:'', pkg_unit:'CARTON(S)', weight_kg:'', weight_unit:'KG', measure_cbm:'', measure_unit:'CBM', po_no:''})" class="btn-tool" style="background-color: #3b82f6; color: #fff; border-color: #3b82f6;" title="Add Container"><i class="fa fa-plus"></i> Add Container</button>
                            </div>
                        </div>

                        <!-- Commodity / Manifest Commodity -->
                        <div style="margin-bottom: 15px;">
                            <div class="flex justify-between items-center" style="margin-bottom: 6px;">
                                <span style="font-weight: bold; font-size: 12px; color: #333;">Commodity / Manifest Commodity</span>
                                <div class="flex gap-1">
                                    <button type="button" @click="addHblCommodity(hbl)" class="btn-tool-icon btn-tool-icon-blue" title="Add Row"><i class="fa fa-plus"></i></button>
                                    <button type="button" @click="deleteSelectedHblCommodities(hbl)" class="btn-tool-icon" style="color: red; border-color: red;" title="Delete Selected"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="container-table" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;"><input type="checkbox" @change="toggleAllHblCommodities(hbl, $event)"></th>
                                            <th style="width: 150px;">HB/L No.</th>
                                            <th>* Commodity Description</th>
                                            <th style="width: 200px;">HTS Code</th>
                                            <th style="width: 200px;">Container</th>
                                            <th x-show="hbl.po_mapping_type === 'item'">P.O. No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(comm, commIdx) in hbl.commodities" :key="commIdx">
                                            <tr>
                                                <td><input type="checkbox" x-model="comm.selected"></td>
                                                <td>
                                                    <select :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][hbl_no]'" class="form-control-gf" x-model="comm.hbl_no">
                                                        <option value="">Select HBL...</option>
                                                        <template x-for="h in hbls">
                                                            <option :value="h.hbl_no" x-text="h.hbl_no"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                                <td>
                                                    <textarea :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][commodity_desc]'" class="form-control-gf" placeholder="Description..." x-model="comm.commodity_desc" required style="min-height: 80px !important; height: 80px !important; width: 100%; resize: vertical; padding: 6px;"></textarea>
                                                </td>
                                                <td>
                                                    <input type="text" :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][hts_code]'" class="form-control-gf" placeholder="HTS Code..." x-model="comm.hts_code">
                                                </td>
                                                <td>
                                                    <select :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][container_no]'" class="form-control-gf" x-model="comm.container_no">
                                                        <option value="">Select Container...</option>
                                                        <template x-for="c in (hbl.containers && hbl.containers.length ? hbl.containers : form.containers)">
                                                            <option :value="c.container_no" x-text="c.container_no"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                                <td x-show="hbl.po_mapping_type === 'item'">
                                                    <!-- P.O. Selector for Item-based Mapping -->
                                                    <select :name="'hbls['+hblIdx+'][commodities]['+commIdx+'][po_no]'" class="form-control-gf" x-model="comm.po_no">
                                                        <option value="">Select PO...</option>
                                                        <template x-for="po in getPoList(hbl.po_no)">
                                                            <option :value="po" x-text="po"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="!hbl.commodities || hbl.commodities.length === 0">
                                            <tr>
                                                <td :colspan="hbl.po_mapping_type === 'item' ? 6 : 5" style="text-align: center; color: #999; padding: 10px;">
                                                    No commodities added yet. Click <span class="text-blue-500 cursor-pointer" @click="addHblCommodity(hbl)">here</span> to add a new row.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mark & Description -->
                        <div class="form-grid-2" style="margin-bottom: 15px;">
                            <div>
                                <label class="form-label-gf" style="font-weight: bold; margin-bottom: 4px; display: block;">Mark</label>
                                <textarea :name="'hbls['+hblIdx+'][hbl_mark]'" class="form-control-gf" style="height: 140px; width:100%;" x-model="hbl.hbl_mark"></textarea>
                            </div>
                            <div>
                                <div class="flex justify-between items-center" style="margin-bottom: 4px;">
                                    <label class="form-label-gf" style="font-weight: bold; margin-bottom: 0;">Description</label>
                                    <div class="flex gap-1">
                                        <span style="font-size:10px; color:#555; align-self: center; margin-right: 4px;">Copy:</span>
                                        <button type="button" @click="copyToDescription(hbl, 'po')" class="btn-tool" style="padding: 1px 6px; font-size: 10px;">P.O.</button>
                                        <button type="button" @click="copyToDescription(hbl, 'commodity')" class="btn-tool" style="padding: 1px 6px; font-size: 10px;">Commodity</button>
                                        <button type="button" @click="copyToDescription(hbl, 'both')" class="btn-tool" style="padding: 1px 6px; font-size: 10px;">Commodity & HTS</button>
                                    </div>
                                </div>
                                <textarea :name="'hbls['+hblIdx+'][hbl_description]'" class="form-control-gf" style="height: 140px; width:100%;" x-model="hbl.hbl_description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            </div>

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
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td x-text="cont.weight_kg || '0'"></td>
                                        <td x-text="cont.measure_cbm || '0'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                       <button type="button" @click="copyContainersToClipboard" class="btn-tool" style="background:#4b77be; padding: 6px 15px;">COPY TO CLIPBOARD</button>
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
            <div class="table-responsive" style="margin-bottom: 15px; min-height: 250px;">
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
               <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="copyFromQuote">Copy From</button>
               
               <button type="button" class="btn-freightx" style="background: #16a34a; color: white; border: none; padding: 6px 14px; font-weight: 600; border-radius: 3px; font-size: 11px; cursor: pointer;" @click="createInvoice">Generate Invoice</button>

               <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="prorataCharges">Prorata</button>
               <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="setDefaultCharges">Default</button>
               <button type="button" class="btn-default-gf" style="background: #fff; border: 1px solid #ccc; padding: 6px 15px; font-size: 11px;" @click="reloadCharges">Reload</button>
               <button type="button" class="btn-freightx" style="background: #4b77be; padding: 6px 20px; font-size: 11px;" @click="saveCharges">Save</button>
            </div>

            <!-- Dropdown for Multiple Options (as requested) -->
            <div style="position: relative; margin-top: 10px; display: flex; justify-content: flex-end;">
                <div class="dropdown" x-data="{ open: false }">
                   <button @click="open = !open" class="btn-default-gf" style="background: #f1f3f6; border: 1px solid #ccc; padding: 5px 12px; font-size: 11px; display: flex; align-items: center; gap: 5px;">
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
                                    <template x-for="(log, idx) in form.history" :key="log.id || idx">
                                        <tr>
                                            <td style="white-space: nowrap; font-weight: 500; color: #334155;" x-text="log.date || (log.created_at ? new Date(log.created_at).toLocaleString() : '')"></td>
                                            <td style="font-weight: 600; color: #1e293b;" x-text="log.user ? (typeof log.user === 'object' ? log.user.name : log.user) : 'System'"></td>
                                            <td style="color: #475569; line-height: 1.4;" x-text="log.details"></td>
                                            <td style="text-align: right;">
                                                <span :style="log.action === 'CREATED' ? 'background: #dcfce7; color: #15803d; border-left: 3px solid #16a34a;' : (log.action === 'DELETED' ? 'background: #fee2e2; color: #b91c1c; border-left: 3px solid #dc2626;' : 'background: #ebf5ff; color: #1d4ed8; border-left: 3px solid #2563eb;')"
                                                      style="padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 700; display: inline-block; letter-spacing: 0.5px;"
                                                      x-text="log.action"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!form.history || !form.history.length">
                                        <td colspan="4" style="text-align: center; color: #999; padding: 15px;">No history records found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                        <!-- FILING GRID 1: Parties & Identifiers (Balanced 4-Column Layout) -->
                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Shipper</label>
                                <div class="form-input-container">
                                    <x-inline-select name="dm_shipper_id" :options="$agents" module="trade-partner" x-model="form.dm_shipper_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_shipper_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Consignee</label>
                                <div class="form-input-container">
                                    <x-inline-select name="dm_consignee_id" :options="$agents" module="trade-partner" x-model="form.dm_consignee_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_consignee_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Notify</label>
                                <div class="form-input-container">
                                    <x-inline-select name="dm_notify_id" :options="$agents" module="trade-partner" x-model="form.dm_notify_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_notify_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">AMS No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="ams_no" class="form-control-gf" x-model="form.ams_no">
                                    <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Bill To</label>
                                <div class="form-input-container">
                                    <x-inline-select name="dm_bill_to_id" :options="$agents" module="trade-partner" x-model="form.dm_bill_to_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'dm_bill_to_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Sub B/L No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="sub_bl_no" class="form-control-gf" x-model="form.sub_bl_no">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">OP</label>
                                <div class="form-input-container">
                                    <input type="text" class="form-control-gf" value="{{ auth()->user()->name ?? 'DEMO_USER' }}" disabled style="background:#f5f5f5;">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">ISF No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="isf_no" class="form-control-gf" x-model="form.isf_no">
                                </div>
                            </div>

                            <!-- Row 3 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Oversea Agent</label>
                                <div class="form-input-container">
                                    <x-inline-select name="oversea_agent_id" :options="$agents" module="trade-partner" x-model="form.oversea_agent_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('trade-partner', 'oversea_agent_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">CY/CFS Loc.</label>
                                <div class="form-input-container">
                                    <select name="cy_location_id" class="form-control-gf" x-model="form.cy_location_id">
                                        <option value="">Select...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Available</label>
                                <div class="form-input-container">
                                    <input type="date" name="available_date" class="form-control-gf" x-model="form.available_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">ISF Matched</label>
                                <div class="form-input-container">
                                    <input type="date" name="isf_matched_date" class="form-control-gf" x-model="form.isf_matched_date">
                                </div>
                            </div>

                            <!-- Row 4 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Trucker</label>
                                <div class="form-input-container">
                                    <select name="trucker_id" class="form-control-gf" x-model="form.trucker_id">
                                        <option value="">Select...</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn-default-gf" style="height:18px; padding: 0 4px;" @click="openAddNewModal('trade-partner', 'trucker_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Final Dest.</label>
                                <div class="form-input-container">
                                    <x-inline-select name="fdest_id" :options="$ports" module="port" x-model="form.fdest_id" class="form-control-gf" />
                                    <button type="button" class="btn-default-gf" style="height:18px; padding:0 4px;" @click="openAddNewModal('port', 'fdest_id')"><i class="fa fa-external-link" style="font-size:9px;"></i></button>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Final ETA</label>
                                <div class="form-input-container">
                                    <input type="date" name="final_eta" class="form-control-gf" x-model="form.final_eta">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">ISF 3rd Party</label>
                                <div class="form-input-container" style="justify-content: flex-start;">
                                    <input type="checkbox" name="is_isf_3rd_party" value="1" x-model="form.is_isf_3rd_party">
                                </div>
                            </div>

                            <!-- Row 5 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Ship Mode</label>
                                <div class="form-input-container">
                                    <select name="ship_mode" class="form-control-gf" x-model="form.ship_mode">
                                        <option value="FCL">FCL</option>
                                        <option value="LCL">LCL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Freight</label>
                                <div class="form-input-container">
                                    <select name="freight_term" class="form-control-gf" x-model="form.freight_term">
                                        <option value="Prepaid">PREPAID</option>
                                        <option value="Collect">COLLECT</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">LFD</label>
                                <div class="form-input-container">
                                    <input type="date" name="lfd" class="form-control-gf" x-model="form.lfd">
                                </div>
                            </div>
                            <div class="form-group-gf"></div>

                            <!-- Row 6 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">G.O Date</label>
                                <div class="form-input-container">
                                    <input type="date" name="go_date" class="form-control-gf" x-model="form.go_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Expiry Date</label>
                                <div class="form-input-container">
                                    <input type="date" name="expiry_date" class="form-control-gf" x-model="form.expiry_date">
                                </div>
                            </div>
                            <div class="form-group-gf"></div>
                            <div class="form-group-gf"></div>
                        </div>

                        <div style="height: 15px;"></div>

                        <!-- FILING GRID 2: Status & Logistics Details (Balanced 4-Column Layout) -->
                        <div class="form-grid-4">
                            <!-- Row 1 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Sales Type</label>
                                <div class="form-input-container">
                                    <select name="sales_type" class="form-control-gf" x-model="form.sales_type">
                                        <option value="">Select...</option>
                                        <option value="NORMAL">NORMAL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Incoterms</label>
                                <div class="form-input-container">
                                    <select name="incoterm_id" class="form-control-gf" x-model="form.incoterm_id">
                                        <option value="">Select...</option>
                                        @foreach($incoterms as $incoterm)
                                            <option value="{{ $incoterm->code }}">{{ $incoterm->code }} - {{ $incoterm->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Cargo Type</label>
                                <div class="form-input-container">
                                    <select name="cargo_type" class="form-control-gf" x-model="form.cargo_type">
                                        <option value="GENERAL CARGO">GENERAL CARGO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Container/Qty</label>
                                <div class="form-input-container">
                                    <input type="text" class="form-control-gf" :value="form.containers.length + ' Container(s)'" disabled style="background:#f5f5f5;">
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">C. Released</label>
                                <div class="form-input-container">
                                    <input type="date" name="c_released_date" class="form-control-gf" x-model="form.c_released_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Service Term</label>
                                <div class="form-input-container">
                                    <select name="service_term_from_id" class="form-control-gf" style="width:45%;" x-model="form.service_term_from_id">
                                        <option value="">Select...</option>
                                        @foreach($serviceTerms as $st)
                                            <option value="{{ $st->id }}">{{ $st->code }}</option>
                                        @endforeach
                                    </select>
                                    <span class="mx-1">~</span>
                                    <select name="service_term_to_id" class="form-control-gf" style="width:45%;" x-model="form.service_term_to_id">
                                        <option value="">Select...</option>
                                        @foreach($serviceTerms as $st)
                                            <option value="{{ $st->id }}">{{ $st->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Door Deliv.</label>
                                <div class="form-input-container">
                                    <input type="date" name="door_delivery_date" class="form-control-gf" x-model="form.door_delivery_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Released By</label>
                                <div class="form-input-container">
                                    <select name="released_by_id" class="form-control-gf" x-model="form.released_by_id">
                                        <option value="">Select...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Row 3 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf">Entry No.</label>
                                <div class="form-input-container">
                                    <input type="text" name="entry_no" class="form-control-gf" x-model="form.entry_no">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Entry DOC Sent</label>
                                <div class="form-input-container">
                                    <input type="date" name="entry_doc_sent_date" class="form-control-gf" x-model="form.entry_doc_sent_date">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">Hold</label>
                                <div class="form-input-container" style="justify-content: flex-start;">
                                    <input type="checkbox" name="is_hold" value="1" x-model="form.is_hold">
                                </div>
                            </div>
                            <div class="form-group-gf">
                                <label class="form-label-gf">ROR</label>
                                <div class="form-input-container" style="justify-content: flex-start;">
                                    <input type="checkbox" name="is_ror" value="1" x-model="form.is_ror">
                                </div>
                            </div>

                            <!-- Row 4 -->
                            <div class="form-group-gf">
                                <label class="form-label-gf" style="display:flex; align-items:center; justify-content:flex-end; gap:3px;">
                                    <input type="checkbox" name="is_do_sent" value="1" x-model="form.is_do_sent">
                                    <span>DO Sent</span>
                                </label>
                                <div class="form-input-container">
                                    <input type="date" name="do_sent_date" class="form-control-gf" x-model="form.do_sent_date">
                                </div>
                            </div>
                            <div class="form-group-gf"></div>
                            <div class="form-group-gf"></div>
                            <div class="form-group-gf"></div>
                        </div>                    </div>
                </div>
            </div>
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
                                    <x-inline-select name="customer" :options="$agents" module="trade-partner" x-model="filters.customer" class="form-control-gf" />
                                </div></div>
                               <div class="form-group-gf">
    <label class="form-label-gf">Port of Loading</label>
    <div class="form-input-container">
        <x-inline-select
            name="pol"
            :options="$ports"
            module="port"
            x-model="filters.pol"
            class="form-control-gf"
            data-ajax-search="true"
            data-search-url="{{ route('ports.search') }}"
        />
    </div>
</div>
                                <div class="form-group-gf"><label class="form-label-gf">Quote No.</label><div class="form-input-container"><input type="text" class="form-control-gf" x-model="filters.quote_no"></div></div>
                            </div>
                            <div class="main-grid">
                                <div class="form-group-gf"><label class="form-label-gf">Valid Date</label><div class="form-input-container"><input type="date" class="form-control-gf" x-model="filters.valid_date"></div></div>
                              <div class="form-group-gf">
    <label class="form-label-gf">Port of Discharge</label>
    <div class="form-input-container">
        <x-inline-select
            name="pod"
            :options="$ports"
            module="port"
            x-model="filters.pod"
            class="form-control-gf"
            data-ajax-search="true"
            data-search-url="{{ route('ports.search') }}"
        />
    </div>
</div>
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
                        <!-- config working -->
                        <div x-show="showQuoteConfig" style="margin-bottom: 10px; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 10px;">
                            <div class="form-grid-4" style="grid-template-columns: repeat(4, 1fr);">
                                <template x-for="(label, key) in {select: 'Select', quote_no: 'Quote No.', valid_date: 'Valid Date', status: 'Status', creation_date: 'Creation Date', commodity: 'Commodity', pol: 'Port of Loading', pod: 'Port of Discharge', carrier: 'Carrier', sales: 'Sales'}" :key="key">
                                    <label style="display: flex; align-items: center; gap: 4px; font-size: 10px; cursor: pointer;">
                                        <input type="checkbox" x-model="colVisibility[key]" style="width: 12px; height: 12px; margin: 0;">
                                        <span x-text="label"></span>
                                    </label>
                                </template>
                            </div>
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
                                    <td x-text="selectedQuote ? selectedQuote.pol_name : ''"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pol_name : 'SHANGHAI'"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pod_name : 'LONDON'"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pod_name : ''"></td>
                                    <td x-text="selectedQuote ? selectedQuote.pod_name : ''"></td>
                                    <td><span x-text="selectedQuote ? selectedQuote.carrier_name : 'MAERSK A/S'"></span></td>
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
                                <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><span x-text="quoteForm.oversea_agent" style="font-size: 10px; color: #334155;"></span></div></div>
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
                                    <th style="text-align: right;">Amount</th>
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
                                    <td style="text-align: right;" x-text="item.amount ? item.amount.toLocaleString() : '0.00'"></td>
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
    </div>

    </div>
    </form>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>



    <style>
        .oi-Tools-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 7px 16px;
            font-size: 12px;
            color: #374151;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: background 0.15s;
        }
        .oi-Tools-item:hover {
            background: #f3f4f6;
        }
    </style>

    <script>
        function showToast(type, msg) {
            const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle', warning: 'exclamation-triangle' };
            const t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
            document.getElementById('toast-container').appendChild(t);
            setTimeout(() => t.remove(), 7000);
        }

        // Show session messages as toasts
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

