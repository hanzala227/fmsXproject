
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
                        };
                    });
                })(),
                form: {
                    id: @json(isset($oceanExport) ? $oceanExport->id : null),
                    file_no: getFormValue('file_no', @json(isset($oceanExport) ? $oceanExport->file_no : 'MOE-' . date('ymdHis'))),
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
                    // Filing tab partner fields (not in fillable but used in the UI)
                    shipper_id: getFormValue('shipper_id', @json(isset($oceanExport) ? $oceanExport->shipper_id : '')),
                    bill_to_id: getFormValue('bill_to_id', @json(isset($oceanExport) ? $oceanExport->bill_to_id : '')),
                    consignee_id: getFormValue('consignee_id', @json(isset($oceanExport) ? $oceanExport->consignee_id : '')),
                    notify_id: getFormValue('notify_id', @json(isset($oceanExport) ? $oceanExport->notify_id : '')),
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
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
                    let dataToApply = { mbl: {}, hbl: {}, hbls: [], containers: [] };
                    
                    this.extractedData.forEach(item => {
                        if (item.selected) {
                            if (item.isContainer || item.parent === 'containers') {
                                dataToApply.containers.push(item.originalData);
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
                        if (data.mbl.file_no && !this.saved) this.form.file_no = data.mbl.file_no;
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
                    }

                    // Containers Mapping
                    if (data.containers && data.containers.length > 0) {
                        data.containers.forEach((cont, index) => {
                            if (this.form.containers.length <= index) {
                                this.addContainer();
                            }
                            if (cont.container_no) this.form.containers[index].container_no = cont.container_no;
                            if (cont.seal_no) this.form.containers[index].seal_no = cont.seal_no;
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
                    if (!shipmentId && !['block','unblock','copy','delete','batch_email','batch_print'].includes(action)) {
                        showToast('warning', 'Please save the shipment first.');
                        return;
                    }
                    const actions = {
                        unblock: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/ocean-export/bulk-unblock', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                                body: JSON.stringify({ ids: [shipmentId], type: 'mbl' })
                            }).then(r => r.json()).then(d => {
                                this.form.is_blocked = false;
                                showToast(d.success ? 'success' : 'error', d.message || (d.success ? 'Shipment unblocked.' : 'Unblock failed.'));
                            }).catch(() => showToast('error', 'Unblock request failed.'));
                        },
                        block: () => {
                            if (!shipmentId) { showToast('warning', 'Save the shipment first.'); return; }
                            fetch('/ocean-export/bulk-block', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                                body: JSON.stringify({ ids: [shipmentId], type: 'mbl' })
                            }).then(r => r.json()).then(d => {
                                this.form.is_blocked = true;
                                showToast(d.success ? 'success' : 'error', d.message || (d.success ? 'Shipment blocked.' : 'Block failed.'));
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
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                                body: JSON.stringify({ _method: 'DELETE' })
                            }).then(r => r.json()).then(d => {
                                showToast('success', 'Shipment deleted successfully.');
                                setTimeout(() => window.location.href = '/ocean-export/list', 800);
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


                // ============ CHARGES SECTION FUNCTIONS ============
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
                        showToast('error', 'Please select at least one charge.');
                        return;
                    }

                    let alreadyInvoiced = selectedCharges.filter(c => c.is_invoiced || (c.inv_no && c.inv_no.trim() !== '' && c.inv_no !== '-'));
                    if (alreadyInvoiced.length > 0) {
                        let invNos = alreadyInvoiced.map(c => c.inv_no || 'Already Invoiced').filter((v, i, a) => a.indexOf(v) === i).join(', ');
                        showToast('warning', 'Invoice already created for selected charge(s): ' + invNos);
                    }

                    let uninvoicedCharges = selectedCharges.filter(c => !c.is_invoiced && (!c.inv_no || c.inv_no.trim() === '' || c.inv_no === '-'));
                    let selectedIds = uninvoicedCharges.filter(c => c.id).map(c => c.id);

                    if (selectedIds.length === 0) {
                        return;
                    }

                    if (!confirm('Generate Invoice for ' + selectedIds.length + ' selected uninvoiced charge(s)?')) return;
                    fetch(`/ocean-export/${this.form.id}/charges/invoice`, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', 'Invoice generated successfully: ' + (data.invoice_no || ''));
                            setTimeout(() => { window.location.reload(); }, 800);
                    });
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
