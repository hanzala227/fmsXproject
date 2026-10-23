<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        .tp-create-page .tab-content { background: #fff; border: 1px solid #e2e8f0; border-top: none; padding: 12px; }
        .tp-create-page .main-grid { display: grid; grid-template-columns: 1.35fr 1fr; gap: 12px; align-items: start; }
        .tp-create-page .form-row { display: grid; grid-template-columns: repeat(12, 1fr); gap: 8px 10px; margin-bottom: 6px; align-items: start; }
        .tp-create-page .form-group { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .tp-create-page .form-group label { font-size: 11px; color: #475569; font-weight: 600; line-height: 1.3; }
        .tp-create-page .required:after { content: " *"; color: #ef4444; }
        .tp-create-page input, .tp-create-page select, .tp-create-page textarea {
            height: 26px; padding: 2px 8px; font-size: 11px; font-weight: 500;
            border: 1px solid #cbd5e1; border-radius: 3px; width: 100%;
            background-color: #fff; color: #1e293b;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.01);
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .tp-create-page input:focus, .tp-create-page select:focus, .tp-create-page textarea:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
        }
        .tp-create-page textarea { height: auto; min-height: 48px; padding: 5px 8px; resize: vertical; line-height: 1.4; }
        .tp-create-page input:disabled, .tp-create-page select:disabled, .tp-create-page textarea:disabled { background-color: #f1f5f9 !important; cursor: not-allowed; color: #94a3b8; }
        .tp-create-page .select-custom-arrow {
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: right 6px center; background-size: 9px; padding-right: 20px !important;
        }
        .tp-create-page .section-header-blue {
            background: #2563eb !important; color: #fff; padding: 6px 12px;
            font-weight: 700; text-transform: uppercase; font-size: 11px;
            display: flex; justify-content: space-between; align-items: center;
            border-radius: 4px 4px 0 0;
        }
        .tp-create-page .party-row-header { background: #f8fafc; padding: 6px 12px; border: 1px solid #e2e8f0; border-top: none; display: flex; align-items: center; gap: 8px; }
        .tp-create-page .party-row-header span { font-weight: 700; font-size: 11px; color: #334155; min-width: 150px; }
        .tp-create-page .checkbox-container { display: flex; flex-wrap: wrap; gap: 10px; padding: 8px 10px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 4px; }
        .tp-create-page .checkbox-item { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500; color: #475569; }
        .tp-create-page .checkbox-item input[type="checkbox"] { width: 13px; height: 13px; margin: 0; }
        .tp-create-page .btn-sm { padding: 3px 10px; font-size: 10px; height: 24px; border-radius: 3px; font-weight: 600; text-transform: uppercase; cursor: pointer; border: none; }
        .tp-create-page .btn-blue { background: #2563eb; color: #fff; }
        .tp-create-page .btn-blue:hover { background: #1d4ed8; }
        .tp-create-page .btn-green { background: #10b981; color: #fff; }
        .tp-create-page .btn-green:hover { background: #059669; }
        .tp-create-page .tp-save-btn { background: #2563eb; color: #fff; padding: 6px 50px; border-radius: 20px; font-weight: 700; text-transform: uppercase; font-size: 12px; border: none; cursor: pointer; transition: all 0.2s; }
        .tp-create-page .tp-save-btn:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(37,99,235,0.25); }

        @media (max-width: 1280px) {
            .tp-create-page .main-grid { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 768px) {
            .tp-create-page .form-row { display: flex !important; flex-direction: column !important; gap: 6px !important; }
            .tp-create-page .form-row > .form-group { grid-column: span 12 !important; width: 100% !important; }
        }
    </style>
    @endpush


    <script data-turbo-eval="yes">
        // View cache breaker: 1
        function showToast(type, msg) {
            var icons = { success: 'check-circle', error: 'times-circle', warning: 'exclamation-triangle', info: 'info-circle' };
            var t = document.createElement('div');
            t.className = 'toast ' + type;
            t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
            document.getElementById('toast-container').appendChild(t);
            setTimeout(function() { t.remove(); }, 7000);
        }
        function showConfirm(title, msg) {
            return new Promise(function(resolve) {
                var overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:100000;display:flex;align-items:center;justify-content:center;';
                overlay.innerHTML = '<div style="background:#fff;border-radius:6px;padding:20px;max-width:380px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.3);text-align:center;">' +
                    '<div style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:8px;">' + title + '</div>' +
                    '<div style="font-size:12px;color:#64748b;margin-bottom:18px;">' + msg + '</div>' +
                    '<div style="display:flex;gap:8px;justify-content:center;">' +
                    '<button id="confirm-yes" style="background:#ef4444;color:#fff;border:none;padding:6px 20px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;">Yes, Delete</button>' +
                    '<button id="confirm-no" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:6px 20px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;">Cancel</button>' +
                    '</div></div>';
                document.body.appendChild(overlay);
                document.getElementById('confirm-yes').onclick = function() { overlay.remove(); resolve(true); };
                document.getElementById('confirm-no').onclick = function() { overlay.remove(); resolve(false); };
                overlay.onclick = function(e) { if (e.target === overlay) { overlay.remove(); resolve(false); } };
            });
        }
        window.tradePartnerForm = function() {
            return {
                activeTab: 'basic',
                firstTabSaved: {{ isset($tradePartner) && $tradePartner->id ? 'true' : 'false' }},
                tradePartyOpen: true,
                ToolsOpen: false,

                // Settings / Configs / Permissions based on user criteria:
                vm: {
                    tpIsPhoneMandatory: true, 
                    enable_global_local_tp: true, 
                    enableMagayaConfig: true,
                    notAllowChangeTypeToOverseaAgent: 'TP Type cannot be changed to Oversea Agent for active Customer accounts.',
                    user_permissions: {
                        hasEditPerm: true,
                        hasAnyEditInfoPerm: true,
                        hasEditContactPersonInfoPerm: true,
                        hasEditAddressPerm: true,
                        hasEditTradePartyPerm: true,
                        canEditSalesPerson: true,
                        canEditCreditLimitGroup: true,
                        hasEditBasicInfoPerm: true
                    }
                },

                user_editable_tp_types: [
                    null, 'CS', 'CR', 'AC', 'CF', 'CY', 'EM', 'FR', 'GV', 'MF', 'OE', 'PR', 'CB', 'TK', 'RC', 'WH', 'BK', 'OT', 'TM', 'KS', 'SH', 'RL', 'CN', 'VR', 'BW', 'FB'
                ],

                tpTypes: [
                    { code: 'AC', name: 'AIR CARRIER' },
                    { code: 'BK', name: 'BANK' },
                    { code: 'BW', name: 'BOOKING WINDOW' },
                    { code: 'CF', name: 'CFS' },
                    { code: 'CN', name: 'CONSIGNEE' },
                    { code: 'CS', name: 'CUSTOMER' },
                    { code: 'CB', name: 'CUSTOMS BROKER' },
                    { code: 'CY', name: 'CY' },
                    { code: 'EM', name: 'EMPLOYEE' },
                    { code: 'FB', name: 'FBA WAREHOUSE' },
                    { code: 'FR', name: 'FORWARDER' },
                    { code: 'GV', name: 'GOVERNMENT' },
                    { code: 'MF', name: 'MANUFACTURER' },
                    { code: 'CR', name: 'OCEAN CARRIER' },
                    { code: 'OE', name: 'OFFICE EXPENSE' },
                    { code: 'OT', name: 'OTHERS' },
                    { code: 'PR', name: 'OVERSEA AGENT' },
                    { code: 'RL', name: 'RAIL COMPANY' },
                    { code: 'RC', name: 'RAMP LOCATION' },
                    { code: 'KS', name: 'SHIPPER (KNOWN)' },
                    { code: 'SH', name: 'SHIPPER (UNKNOWN)' },
                    { code: 'TM', name: 'TERMINAL' },
                    { code: 'TK', name: 'TRUCKER' },
                    { code: 'VR', name: 'VENDOR' },
                    { code: 'WH', name: 'WAREHOUSE' }
                ],

                originalType: '{{ $tradePartner->type ?? "" }}',

                partyTypes: [
                    { db: 'BILL_TO', label: 'Bill To' },
                    { db: 'CUSTOMS_BROKER', label: 'Customs Broker' },
                    { db: 'TRUCKER', label: 'Trucker' },
                    { db: 'PICKUP_DELIVERY_LOCATION', label: 'Pickup / Delivery Location' },
                    { db: 'CONSIGNEE', label: 'Consignee' },
                    { db: 'SHIPPER', label: 'Shipper' },
                    { db: 'NOTIFY', label: 'Notify' },
                    { db: 'VENDOR', label: 'Vendor' }
                ],

                loggedInUser: '{{ auth()->user()->name }}',
                checkingBond: false,
                validationErrors: [],
                logs: [],
                documents: {!! json_encode($tradePartner->documents ?? []) !!},
                selectedDocIds: [],
                isDragging: false,
                countries: @json($countries->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),
                portalEmailTouched: false,

                init() {
                    this.$watch('activeTab', (value) => {
                        sessionStorage.setItem('tpActiveTab', value);
                        if (value === 'status') {
                            this.fetchLogs();
                        }
                    });

                    const savedTab = sessionStorage.getItem('tpActiveTab');
                    const urlParams = new URLSearchParams(window.location.search);
                    const tabParam = urlParams.get('tab');
                    if (tabParam) {
                        this.activeTab = tabParam;
                    } else if (savedTab && (this.firstTabSaved || savedTab === 'basic')) {
                        this.activeTab = savedTab;
                    }

                    // Initialize previews
                    this.updatePreviews();

                    // Watch email for portal sync
                    this.$watch('form.email', (value) => {
                        if (!this.form.id && !this.portalEmailTouched) {
                            this.form.portal_email = value;
                        }
                    });

                    // Watchers for live preview update
                    this.$watch('form.name', () => this.updatePreviews());
                    this.$watch('form.local_address', () => this.updatePreviews());
                    this.$watch('form.city', () => this.updatePreviews());
                    this.$watch('form.zip_code', () => this.updatePreviews());
                    this.$watch('form.country_id', () => this.updatePreviews());
                    this.$watch('form.phone', () => this.updatePreviews());
                    this.$watch('form.fax', () => this.updatePreviews());
                    this.$watch('form.print_address_use_default', () => this.updatePreviews());
                    this.$watch('form.print_address_show_name', () => this.updatePreviews());
                    this.$watch('form.print_address_show_address', () => this.updatePreviews());
                    this.$watch('form.print_address_show_contact', () => this.updatePreviews());
                },

                form: {
                    id: '{{ $tradePartner->id ?? "" }}',
                    type: '{{ $tradePartner->type ?? "CS" }}',
                    code: '{{ $tradePartner->code ?? "" }}',
                    alias: '{{ $tradePartner->alias ?? "" }}',
                    account_group_id: '{{ $tradePartner->account_group_id ?? "" }}',
                    credit_limit_group_id: '{{ $tradePartner->credit_limit_group_id ?? "" }}',
                    aeo: '{{ $tradePartner->aeo ?? "" }}',
                    credit_term_unit: '{{ $tradePartner->credit_term_unit ?? "Days" }}',
                    print_address_use_default: {{ ($tradePartner->print_address_use_default ?? true) ? 'true' : 'false' }},
                    print_address_show_name: {{ ($tradePartner->print_address_show_name ?? true) ? 'true' : 'false' }},
                    print_address_show_address: {{ ($tradePartner->print_address_show_address ?? true) ? 'true' : 'false' }},
                    print_address_show_contact: {{ ($tradePartner->print_address_show_contact ?? true) ? 'true' : 'false' }},
                    print_address_preview: '',
                    additional_addresses: {!! json_encode($tradePartner->additional_addresses ?? []) !!},
                    name: '{{ $tradePartner->name ?? "" }}',
                    print_name: '{{ $tradePartner->print_name ?? "" }}',
                    local_name: '{{ $tradePartner->local_name ?? "" }}',
                    local_address: {!! json_encode($tradePartner->local_address ?? '') !!},
                    city: '{{ $tradePartner->city ?? "" }}',
                    state: '{{ $tradePartner->state ?? "" }}',
                    zip_code: '{{ $tradePartner->zip_code ?? "" }}',
                    country_id: '{{ $tradePartner->country_id ?? "" }}',
                    iata_code: '{{ $tradePartner->iata_code ?? "" }}',
                    corporation_no: '{{ $tradePartner->corporation_no ?? "" }}',
                    sita_profile: '{{ $tradePartner->sita_profile ?? "" }}',
                    account_no: '{{ $tradePartner->account_no ?? "" }}',
                    scac_code: '{{ $tradePartner->scac_code ?? "" }}',
                    firms_code: '{{ $tradePartner->firms_code ?? "" }}',
                    cbsa_carrier_code: '{{ $tradePartner->cbsa_carrier_code ?? "" }}',
                    phone: '{{ $tradePartner->phone ?? "" }}',
                    fax: '{{ $tradePartner->fax ?? "" }}',
                    url: '{{ $tradePartner->url ?? "" }}',
                    email: '{{ $tradePartner->email ?? "" }}',
                    portal_email: '{{ $tradePartner->user ? $tradePartner->user->email : "" }}',
                    portal_password: '',
                    status: '{{ $tradePartner->status ?? "BUSINESS" }}',
                    sales_office_id: '{{ $tradePartner->sales_office_id ?? "" }}',
                    sales_person_id: '{{ $tradePartner->sales_person_id ?? "" }}',
                    cs_person_id: '{{ $tradePartner->cs_person_id ?? "" }}',
                    billing_address: {!! json_encode($tradePartner->billing_address ?? '') !!},
                    tax_id: '{{ $tradePartner->tax_id ?? "" }}',
                    payment_type: '{{ $tradePartner->payment_type ?? "COD" }}',
                    track_1099: {{ ($tradePartner->track_1099 ?? false) ? 'true' : 'false' }},
                    bill_to_agent: {{ ($tradePartner->bill_to_agent ?? false) ? 'true' : 'false' }},
                    clm_id: '{{ $tradePartner->clm_id ?? "" }}',
                    credit_term_days: '{{ $tradePartner->credit_term_days ?? "" }}',
                    credit_limit: '{{ $tradePartner->credit_limit ?? "" }}',
                    accountant_name: '{{ $tradePartner->accountant_name ?? "" }}',
                    bank_account_name_1: '{{ $tradePartner->bank_account_name_1 ?? "" }}',
                    bank_account_no_1: '{{ $tradePartner->bank_account_no_1 ?? "" }}',
                    bank_currency_1_id: '{{ $tradePartner->bank_currency_1_id ?? "1" }}',
                    bank_account_name_2: '{{ $tradePartner->bank_account_name_2 ?? "" }}',
                    bank_account_no_2: '{{ $tradePartner->bank_account_no_2 ?? "" }}',
                    bank_currency_2_id: '{{ $tradePartner->bank_currency_2_id ?? "1" }}',
                    profit_share_percent: '{{ $tradePartner->profit_share_percent ?? "0.00" }}',
                    popup_tips: {
                        door_to_door: false,
                        bad_customer: false,
                        import_only: false,
                        export_only: false,
                        co_loader: false,
                        custom_clear: false,
                        warehouse: false,
                        isf_charges: false,
                        free_hand_cargo: false,
                        nomination: false,
                        see_memo_remark: false,
                        ...{!! json_encode($tradePartner->popup_tips ?? []) !!}
                    },
                    remark: {!! json_encode($tradePartner->remark ?? '') !!}
                },

                contacts: {!! json_encode($tradePartner->contacts ?? []) !!},
                memos: {!! json_encode($tradePartner->memos ?? []) !!},
                defaultFreights: {!! json_encode($tradePartner->defaultFreights ?? []) !!},
                commodities: {!! json_encode($tradePartner->commodities ?? []) !!},
                filingSetting: {
                    isf_submission_name: '{{ optional($tradePartner->filingSettings)->isf_submission_name ?? "" }}',
                    isf_submission_state: '{{ optional($tradePartner->filingSettings)->isf_submission_state ?? "" }}',
                    isf_zip_code: '{{ optional($tradePartner->filingSettings)->isf_zip_code ?? "" }}',
                    importer_code: '{{ optional($tradePartner->filingSettings)->importer_code ?? "" }}',
                    importer_no: '{{ optional($tradePartner->filingSettings)->importer_no ?? "" }}'
                },
                relatedParties: {!! json_encode($tradePartner->relatedParties ?? []) !!},

                copyFromLocalAddress() {
                    this.form.billing_address = this.form.local_address;
                },

                computePreviewText(showName, showAddress, showContact) {
                    let parts = [];
                    if (showName && this.form.name) {
                        parts.push(this.form.name);
                    }
                    if (showAddress) {
                        if (this.form.local_address) {
                            parts.push(this.form.local_address);
                        }
                        let cityZip = [];
                        if (this.form.city) {
                            cityZip.push(this.form.city);
                        }
                        if (this.form.zip_code) {
                            cityZip.push(this.form.zip_code);
                        }
                        if (cityZip.length > 0) {
                            parts.push(cityZip.join(', '));
                        }
                        if (this.form.country_id) {
                            let c = this.countries.find(x => x.id == this.form.country_id);
                            if (c) {
                                parts.push(c.name);
                            }
                        }
                    }
                    if (showContact) {
                        let contactParts = [];
                        if (this.form.phone) {
                            contactParts.push('Tel: ' + this.form.phone);
                        }
                        if (this.form.fax) {
                            contactParts.push('Fax: ' + this.form.fax);
                        }
                        if (contactParts.length > 0) {
                            parts.push(contactParts.join('  '));
                        }
                    }
                    return parts.join('\n');
                },

                updatePreviews() {
                    if (this.form.print_address_use_default === true || this.form.print_address_use_default === 'true') {
                        this.form.print_address_preview = this.computePreviewText(
                            this.form.print_address_show_name,
                            this.form.print_address_show_address,
                            this.form.print_address_show_contact
                        );
                    }
                    if (this.form.additional_addresses) {
                        this.form.additional_addresses.forEach((addr) => {
                            if (addr.use_default === true || addr.use_default === 'true') {
                                addr.preview = this.computePreviewText(
                                    addr.show_name,
                                    addr.show_address,
                                    addr.show_contact
                                );
                            }
                        });
                    }
                },

                addAddressBlock() {
                    this.form.additional_addresses.push({
                        use_default: true,
                        show_name: true,
                        show_address: true,
                        show_contact: true,
                        preview: ''
                    });
                    this.$nextTick(() => {
                        this.updatePreviews();
                    });
                },

                removeAddressBlock(index) {
                    this.form.additional_addresses.splice(index, 1);
                },

                addContact() {
                    this.contacts.push({
                        is_representative: false,
                        email_name: '',
                        title: '',
                        division: '',
                        cell_phone: '',
                        phone: '',
                        fax: '',
                        email: '',
                        remark: ''
                    });
                },
                removeContact(index) {
                    this.contacts.splice(index, 1);
                },

                addMemo() {
                    this.memos.push({
                        subject: '',
                        content: '',
                        user_id: '{{ auth()->id() ?? 1 }}'
                    });
                },
                removeMemo(index) {
                    this.memos.splice(index, 1);
                },

                addDefaultFreight(transportMode, sectionTitle, sectionPc, sectionType) {
                    this.defaultFreights.push({
                        transport_mode: transportMode,
                        section: sectionTitle,
                        ship_mode: 'All',
                        freight_code: '',
                        description: '',
                        pc: sectionPc || 'COLLECT',
                        type: sectionType || 'Our Sales',
                        unit: 'UNIT',
                        currency_id: '1',
                        volume: 1,
                        rate: 0,
                        amount: 0,
                        agent_amount: 0
                    });
                },
                removeDefaultFreight(originalIndex) {
                    this.defaultFreights.splice(originalIndex, 1);
                },

                addCommodity() {
                    this.commodities.push({
                        description: '',
                        package_unit_id: '',
                        hts_code: '',
                        pcs: '',
                        net_weight: '',
                        net_weight_unit: 'KG',
                        gross_weight: '',
                        gross_weight_unit: 'KG',
                        measurement: '',
                        measurement_unit: 'CBM',
                        unit_price: 0,
                        amount: 0,
                        details: ''
                    });
                },
                removeCommodity(index) {
                    this.commodities.splice(index, 1);
                },

                addRelatedParty(partyTypeDb) {
                    this.relatedParties.push({
                        party_type: partyTypeDb,
                        related_partner_id: '',
                        is_default: false,
                        description: ''
                    });
                },
                removeRelatedParty(index) {
                    this.relatedParties.splice(index, 1);
                },

                handleDrop(e) {
                    this.isDragging = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.uploadFiles(files);
                    }
                },
                handleFileSelect(e) {
                    const files = e.target.files;
                    if (files.length > 0) {
                        this.uploadFiles(files);
                    }
                },
                uploadFiles(files) {
                    if (!this.form.id) {
                        showToast('warning', 'Please save the Trade Partner first before uploading documents.');
                        return;
                    }
                    for (let i = 0; i < files.length; i++) {
                        const formData = new FormData();
                        formData.append('file', files[i]);
                        
                        fetch(`/trade-partner/${this.form.id}/documents`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.documents.push(data.document);
                                showToast('success', 'Document uploaded successfully.');
                            } else {
                                showToast('error', 'Failed to upload file.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            showToast('error', 'An error occurred during upload.');
                        });
                    }
                },
                async deleteDocument(id) {
                    var confirmed = await showConfirm('Delete Document', 'Are you sure you want to delete this document?');
                    if (!confirmed) return;
                    fetch(`/trade-partner/${this.form.id}/documents/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.documents = this.documents.filter(d => d.id !== id);
                            this.selectedDocIds = this.selectedDocIds.filter(x => x !== id);
                            showToast('success', 'Document deleted successfully.');
                        } else {
                            showToast('error', 'Failed to delete document.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('error', 'An error occurred during deletion.');
                    });
                },
                emailDocument(id) {
                    fetch(`/trade-partner/${this.form.id}/documents/${id}/email`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        showToast('success', data.message || 'Email sent successfully.');
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('error', 'An error occurred while sending email.');
                    });
                },
                toggleSelectAllTools(e) {
                    if (e.target.checked) {
                        this.selectedDocIds = this.documents.map(d => d.id);
                    } else {
                        this.selectedDocIds = [];
                    }
                },
                async deleteSelectedDocuments() {
                    if (this.selectedDocIds.length === 0) return;
                    var confirmed = await showConfirm('Delete Documents', 'Are you sure you want to delete ' + this.selectedDocIds.length + ' selected document(s)?');
                    if (!confirmed) return;
                    this.selectedDocIds.forEach(id => {
                        this.deleteDocument(id);
                    });
                },
                emailSelectedDocuments() {
                    if (this.selectedDocIds.length === 0) return;
                    showToast('info', 'Sending email for ' + this.selectedDocIds.length + ' document(s)...');
                    this.selectedDocIds.forEach(id => {
                        this.emailDocument(id);
                    });
                },
                checkBondStatus() {
                    if (!this.form.id) {
                        showToast('warning', 'Please save the Trade Partner first before checking bond status.');
                        return;
                    }
                    this.checkingBond = true;
                    fetch(`/trade-partner/${this.form.id}/check-bond`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        showToast('success', data.message || 'Bond status check complete.');
                        this.checkingBond = false;
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('error', 'Failed to check bond status.');
                        this.checkingBond = false;
                    });
                },
                grantPortalAccess() {
                    const email = document.getElementById('portal_email').value;
                    const password = document.getElementById('portal_password').value;
                    
                    if (!email || !password) {
                        showToast('warning', 'Please enter email and password for portal access.');
                        return;
                    }

                    fetch(`/trade-partner/${this.form.id}/grant-access`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email, password })
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (res.ok && data.success) {
                            showToast('success', 'Portal access granted/updated successfully.');
                            document.getElementById('portal_password').value = '';
                        } else {
                            showToast('error', data.message || (data.errors && data.errors[0]) || 'Failed to grant portal access.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast('error', 'An error occurred while granting portal access.');
                    });
                },
                fetchLogs() {
                    if (!this.form.id) return;
                    fetch(`/trade-partner/${this.form.id}/activity-logs`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.logs) {
                            this.logs = data.logs;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                    });
                },

                copyTradePartner() {
                    this.ToolsOpen = false;
                    if (this.form.id) {
                        showToast('info', 'Copying Trade Partner details...');
                        this.form.id = '';
                        this.form.code = '';
                        if (this.form.name && !this.form.name.includes('(COPY)')) {
                            this.form.name = this.form.name + ' (COPY)';
                        }
                        if (this.form.print_name && !this.form.print_name.includes('(COPY)')) {
                            this.form.print_name = this.form.print_name + ' (COPY)';
                        }
                        this.firstTabSaved = false;
                        if (window.history && window.history.pushState) {
                            window.history.pushState({}, '', '/trade-partner/create');
                        }
                        showToast('success', 'Copied to new Trade Partner form. Click SAVE TRADE PARTNER to persist.');
                    } else {
                        if (this.form.name && !this.form.name.includes('(COPY)')) {
                            this.form.name = this.form.name + ' (COPY)';
                        }
                        if (this.form.print_name && !this.form.print_name.includes('(COPY)')) {
                            this.form.print_name = this.form.print_name + ' (COPY)';
                        }
                        showToast('info', 'Form details copied.');
                    }
                },

                async deleteTradePartner() {
                    this.ToolsOpen = false;
                    if (!this.form.id) {
                        showToast('warning', 'Cannot delete an unsaved Trade Partner.');
                        return;
                    }
                    var confirmed = await showConfirm('Delete Trade Partner', 'Are you sure you want to delete Trade Partner "' + (this.form.name || '') + '"?');
                    if (!confirmed) return;
                    
                    fetch(`/trade-partner/${this.form.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success || data.message) {
                            showToast('success', 'Trade Partner deleted successfully.');
                            setTimeout(() => {
                                window.location.href = '/trade-partner/list';
                            }, 600);
                        } else {
                            showToast('error', data.message || 'Failed to delete Trade Partner.');
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        showToast('error', 'Failed to delete Trade Partner.');
                    });
                },

                submitForm() {
                    let errors = [];
                    if (!this.form.type) errors.push('TP Type is required.');
                    if (!this.form.name) errors.push('Name is required.');
                    if (!this.form.print_name) errors.push('Print Name is required.');
                    if (!this.form.country_id) errors.push('Country is required.');

                    if (this.vm.tpIsPhoneMandatory && !this.form.phone) {
                        errors.push('Phone number is mandatory under company settings.');
                    }
                    if (this.vm.enable_global_local_tp && !this.form.sales_office_id) {
                        errors.push('Sales Office is mandatory when Global/Local Trade Partner settings are enabled.');
                    }
                    if (this.originalType === 'CS' && this.form.type === 'PR') {
                        errors.push(this.vm.notAllowChangeTypeToOverseaAgent);
                    }

                    if (errors.length > 0) {
                        errors.forEach(function(e) { showToast('error', e); });
                        return;
                    }

                    const clean = (v) => (v === '' || v === undefined || v === null) ? null : v;
                    const payload = {
                        ...this.form,
                        account_group_id: clean(this.form.account_group_id),
                        credit_limit_group_id: clean(this.form.credit_limit_group_id),
                        sales_office_id: clean(this.form.sales_office_id),
                        sales_person_id: clean(this.form.sales_person_id),
                        cs_person_id: clean(this.form.cs_person_id),
                        country_id: clean(this.form.country_id),
                        credit_term_days: clean(this.form.credit_term_days),
                        credit_limit: clean(this.form.credit_limit),
                        profit_share_percent: clean(this.form.profit_share_percent),
                        bank_currency_1_id: clean(this.form.bank_currency_1_id),
                        bank_currency_2_id: clean(this.form.bank_currency_2_id),
                        additional_addresses: this.form.additional_addresses,
                        contacts: this.contacts,
                        memos: this.memos,
                        defaultFreights: this.defaultFreights,
                        commodities: this.commodities,
                        filingSetting: this.filingSetting,
                        relatedParties: this.relatedParties
                    };

                    let url = this.form.id ? `/trade-partner/${this.form.id}` : '/trade-partner';
                    let method = this.form.id ? 'PUT' : 'POST';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: method,
                            ...payload
                        })
                    })
                    .then(response => {
                        return response.json().then(data => {
                            if (!response.ok) {
                                throw data;
                            }
                            return data;
                        });
                    })
                    .then(data => {
                        this.firstTabSaved = true;
                        showToast('success', data.message || 'Trade Partner saved successfully.');
                        sessionStorage.setItem('tpActiveTab', this.activeTab);
                        const targetId = data.id || this.form.id;
                        if (targetId) {
                            this.form.id = targetId;
                            setTimeout(() => {
                                window.location.href = `/trade-partner/${targetId}/edit`;
                            }, 500);
                        } else if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 500);
                        } else {
                            if (this.activeTab === 'status') {
                                this.fetchLogs();
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Save error:', err);
                        if (err && err.errors) {
                            let msgs = Object.values(err.errors).flat();
                            msgs.forEach(function(m) { showToast('error', m); });
                        } else {
                            showToast('error', err.message || 'An error occurred while saving the Trade Partner.');
                        }
                    });
                }
            };
        }
    </script>

    <div id="toast-container" class="toast-container"></div>
    <div class="page-content tp-create-page" x-data="window.tradePartnerForm()" x-cloak>
        <!-- Breadcrumb -->
        <div class="page-bar" style="margin-bottom: 15px;">
            <ul class="page-breadcrumb">
                <li><a href="/" style="transition: color 0.15s;" onmouseover="this.style.color='#337ab7';" onmouseout="this.style.color='#888';"><i class="fa fa-home"></i> Home</a><i class="fa fa-angle-right" style="margin: 0 8px; font-size: 10px;"></i></li>
                <li><a href="/trade-partner/list" style="transition: color 0.15s;" onmouseover="this.style.color='#337ab7';" onmouseout="this.style.color='#888';">Trade Partner</a><i class="fa fa-angle-right" style="margin: 0 8px; font-size: 10px;"></i></li>
                <li style="color: #333; font-weight: 700;" x-text="form.id ? 'Edit Trade Partner' : 'New Trade Partner'">New Trade Partner</li>
            </ul>
        </div>

        <!-- Ocean Module Style Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h1 class="caption-subject" style="font-size: 18px;" x-text="form.id ? 'Edit Trade Partner' : 'New Trade Partner'">New Trade Partner</h1>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-freightx" @click.prevent="submitForm()" x-show="vm.user_permissions.hasEditPerm && vm.user_permissions.hasAnyEditInfoPerm"><i class="fa fa-save"></i> SAVE TRADE PARTNER</button>

                <!-- Tools Dropdown Header -->
                <div style="position: relative; display: inline-block;">
                    <button type="button" class="btn-default-gf" style="padding:6px 14px;" @click.stop="ToolsOpen = !ToolsOpen">
                        <i class="fa fa-cogs"></i> Docs <i class="fa fa-angle-down"></i>
                    </button>
                    <div x-show="ToolsOpen" @click.away="ToolsOpen = false" x-cloak
                         style="position: absolute; right: 0; top: 100%; margin-top: 4px; z-index: 1050; min-width: 160px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); padding: 4px 0;">
                        <button type="button" @click="copyTradePartner()" 
                                style="display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #334155; background: transparent; border: none; cursor: pointer; transition: background 0.15s ease;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <i class="fa fa-copy" style="color: #475569; font-size: 13px; width: 14px;"></i> Copy
                        </button>
                        <div style="height: 1px; background: #e2e8f0; margin: 4px 0;"></div>
                        <button type="button" @click="deleteTradePartner()" 
                                :disabled="!firstTabSaved"
                                :style="firstTabSaved ? 'display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #ef4444; background: transparent; border: none; cursor: pointer; transition: background 0.15s ease;' : 'display: flex; align-items: center; gap: 10px; width: 100%; text-align: left; padding: 8px 14px; font-size: 13px; font-weight: 500; color: #cbd5e1; background: transparent; border: none; cursor: not-allowed; opacity: 0.6;'"
                                onmouseover="if(firstTabSaved) this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                            <i class="fa fa-trash-o" :style="firstTabSaved ? 'color: #ef4444; font-size: 13px; width: 14px;' : 'color: #cbd5e1; font-size: 13px; width: 14px;'"></i> Delete
                        </button>
                    </div>
                </div>

                <a href="/trade-partner/list" class="btn-default-gf">BACK TO LIST</a>
            </div>
        </div>

        <ul class="gf-tabs">
            <li :class="{ 'active': activeTab === 'basic' }"><a @click="activeTab = 'basic'">Basic</a></li>
            <li :class="{ 'active': activeTab === 'accounting', 'disabled-tab': !firstTabSaved }"><a @click="if(firstTabSaved){ activeTab = 'accounting' }">Accounting Setting</a></li>
            <li :class="{ 'active': activeTab === 'commodity', 'disabled-tab': !firstTabSaved }"><a @click="if(firstTabSaved){ activeTab = 'commodity' }">Commodity Setting</a></li>
            <li :class="{ 'active': activeTab === 'filing', 'disabled-tab': !firstTabSaved }"><a @click="if(firstTabSaved){ activeTab = 'filing' }">Filing Setting</a></li>
            <li style="display: none !important;" :class="{ 'active': activeTab === 'doc', 'disabled-tab': !firstTabSaved }"><a @click="if(firstTabSaved){ activeTab = 'doc' }">Doc Center</a></li>
            <li :class="{ 'active': activeTab === 'status', 'disabled-tab': !firstTabSaved }"><a @click="if(firstTabSaved){ activeTab = 'status' }">Status</a></li>
        </ul>

            <div class="tab-content">
                <div x-show="activeTab === 'basic'" x-cloak>
                    <div class="main-grid">
                        <!-- Left: TP Information -->
                    <div class="portlet light">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-building"></i> Trade Partner Information</span>
                        </div>
                            <div class="portlet-body">
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 3">
                                        <label class="required">TP Type</label>
                                        <select class="select-custom-arrow" x-model="form.type">
                                            <option value="">Select Type...</option>
                                            <option value="AC">AIR CARRIER</option>
                                            <option value="BK">BANK</option>
                                            <option value="BW">BOOKING WINDOW</option>
                                            <option value="CF">CFS</option>
                                            <option value="CN">CONSIGNEE</option>
                                            <option value="CS">CUSTOMER</option>
                                            <option value="CB">CUSTOMS BROKER</option>
                                            <option value="CY">CY</option>
                                            <option value="EM">EMPLOYEE</option>
                                            <option value="FB">FBA WAREHOUSE</option>
                                            <option value="FR">FORWARDER</option>
                                            <option value="GV">GOVERNMENT</option>
                                            <option value="MF">MANUFACTURER</option>
                                            <option value="CR">OCEAN CARRIER</option>
                                            <option value="OE">OFFICE EXPENSE</option>
                                            <option value="OT">OTHERS</option>
                                            <option value="PR">OVERSEA AGENT</option>
                                            <option value="RL">RAIL COMPANY</option>
                                            <option value="RC">RAMP LOCATION</option>
                                            <option value="KS">SHIPPER (KNOWN)</option>
                                            <option value="SH">SHIPPER (UNKNOWN)</option>
                                            <option value="TM">TERMINAL</option>
                                            <option value="TK">TRUCKER</option>
                                            <option value="VR">VENDOR</option>
                                            <option value="WH">WAREHOUSE</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Code</label>
                                        <input type="text" x-model="form.code" placeholder="Auto-generated" disabled>
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Alias</label>
                                        <input type="text" x-model="form.alias" :disabled="!vm.user_permissions.hasEditBasicInfoPerm">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Account Group Name</label>
                                        <select class="select-custom-arrow" :disabled="!vm.user_permissions.hasEditBasicInfoPerm" x-model="form.account_group_id">
                                            <option value="">Select Group...</option>
                                            @foreach($accountGroups as $group)
                                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 3">
                                        <label class="required">Name</label>
                                        <input type="text" x-model="form.name" :disabled="!vm.user_permissions.hasEditBasicInfoPerm">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label class="required">Print Name</label>
                                        <input type="text" x-model="form.print_name" :disabled="!vm.user_permissions.hasEditBasicInfoPerm">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Credit Limit Group Name</label>
                                        <select class="select-custom-arrow" :disabled="!vm.user_permissions.canEditCreditLimitGroup" x-model="form.credit_limit_group_id">
                                            <option value="">Select...</option>
                                            @foreach($creditLimitGroups as $group)
                                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Local Name</label>
                                        <input type="text" x-model="form.local_name" :disabled="!vm.user_permissions.hasEditBasicInfoPerm">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 6">
                                        <label class="required">Local Address</label>
                                        <textarea x-model="form.local_address" style="min-height: 130px; height: 130px;" :disabled="!vm.user_permissions.hasEditAddressPerm"></textarea>
                                    </div>
                                    <div class="form-group" style="grid-column: span 6">
                                        <div class="form-row" style="margin-bottom: 6px;">
                                            <div class="form-group" style="grid-column: span 6">
                                                <label>IATA Code</label>
                                                <input type="text" x-model="form.iata_code">
                                            </div>
                                            <div class="form-group" style="grid-column: span 6">
                                                <label>Corporation No.</label>
                                                <input type="text" x-model="form.corporation_no">
                                            </div>
                                        </div>
                                        <div class="form-row" style="margin-bottom: 6px;">
                                            <div class="form-group" style="grid-column: span 6">
                                                <label>SITA Profile</label>
                                                <input type="text" x-model="form.sita_profile">
                                            </div>
                                            <div class="form-group" style="grid-column: span 6">
                                                <label>Account No.</label>
                                                <input type="text" x-model="form.account_no">
                                            </div>
                                        </div>
                                        <div class="form-row" style="margin-bottom: 0;">
                                            <div class="form-group" style="grid-column: span 6">
                                                <label>SCAC</label>
                                                <input type="text" x-model="form.scac_code">
                                            </div>
                                            <div class="form-group" style="grid-column: span 6">
                                                <label>Firms Code</label>
                                                <input type="text" x-model="form.firms_code">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>City</label>
                                        <input type="text" x-model="form.city" :disabled="!vm.user_permissions.hasEditAddressPerm">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>State</label>
                                        <input type="text" x-model="form.state" :disabled="!vm.user_permissions.hasEditAddressPerm">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Zip Code</label>
                                        <input type="text" x-model="form.zip_code" :disabled="!vm.user_permissions.hasEditAddressPerm">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>CBSA Carrier Code</label>
                                        <input type="text" x-model="form.cbsa_carrier_code">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 3">
                                        <label class="required">Country</label>
                                        <select class="select-custom-arrow" x-model="form.country_id" :disabled="!vm.user_permissions.hasEditAddressPerm">
                                            <option value="">Select Country...</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label :class="{ 'required': vm.tpIsPhoneMandatory }">Phone</label>
                                        <input type="text" x-model="form.phone">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Fax</label>
                                        <input type="text" x-model="form.fax">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Email</label>
                                        <input type="email" x-model="form.email" placeholder="email@example.com">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 6">
                                        <label>URL</label>
                                        <input type="text" x-model="form.url">
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>Duty Payment Type</label>
                                        <select class="select-custom-arrow" x-model="form.payment_type">
                                            <option value="COD">COD</option>
                                            <option value="CREDIT">CREDIT</option>
                                            <option value="PREPAID">PREPAID</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="grid-column: span 3">
                                        <label>AEO</label>
                                        <input type="text" x-model="form.aeo">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>Print Address Options</label>
                                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 10px; min-height: 28px;">
                                            <div style="display: flex; gap: 8px; align-items: center; border-right: 1px solid #cbd5e1; padding-right: 10px;">
                                                <label style="margin:0; font-weight: 500; font-size: 11px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                                    <input type="radio" name="pa" :value="true" x-model="form.print_address_use_default" @change="updatePreviews()" style="width:13px; height:13px; margin:0;"> Default
                                                </label>
                                                <label style="margin:0; font-weight: 500; font-size: 11px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                                    <input type="radio" name="pa" :value="false" x-model="form.print_address_use_default" @change="updatePreviews()" style="width:13px; height:13px; margin:0;"> Alternate
                                                </label>
                                            </div>
                                            <div style="display: flex; gap: 10px; align-items: center;">
                                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.print_address_show_name" @change="updatePreviews()" style="width:13px; height:13px;"> Name</label>
                                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.print_address_show_address" @change="updatePreviews()" style="width:13px; height:13px;"> Address</label>
                                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.print_address_show_contact" @change="updatePreviews()" style="width:13px; height:13px;"> Contact Info</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" style="grid-column: span 3;">
                                        <label>Status</label>
                                        <select class="select-custom-arrow" x-model="form.status">
                                            <option value="BUSINESS">BUSINESS</option>
                                            <option value="PRE_BUSINESS">PRE BUSINESS</option>
                                            <option value="INACTIVE">INACTIVE</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="grid-column: span 3;">
                                        <label :class="{ 'required': vm.enable_global_local_tp }">Sales Office</label>
                                        <select class="select-custom-arrow" x-model="form.sales_office_id">
                                            <option value="">Select Office...</option>
                                            @foreach($offices as $office)
                                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 12;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                            <label style="margin: 0;">Address Preview Box</label>
                                            <button type="button" class="btn btn-sm btn-link" style="padding: 0; color: #2563eb; font-weight: 600; font-size: 11px; text-decoration: none;" @click="addAddressBlock()">
                                                <i class="fa fa-plus-circle"></i> Add Additional Address
                                            </button>
                                        </div>
                                        <textarea x-model="form.print_address_preview" :readonly="form.print_address_use_default === true || form.print_address_use_default === 'true'" :style="(form.print_address_use_default === true || form.print_address_use_default === 'true') ? 'min-height: 70px; background-color: #f8fafc;' : 'min-height: 70px; background-color: #ffffff;'" placeholder="Live Address Preview..."></textarea>
                                    </div>
                                </div>
                                
                                <template x-for="(addr, index) in form.additional_addresses" :key="index">
                                    <div style="border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 8px; margin-bottom: 8px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                            <strong style="font-size: 11px; color: #2563eb;" x-text="'Print Address ' + (index + 2)"></strong>
                                            <button type="button" class="btn btn-sm btn-link" style="color: #ef4444; padding: 0; font-weight: 600; font-size: 11px; text-decoration: none;" @click="removeAddressBlock(index)"><i class="fa fa-trash"></i> Remove</button>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group" style="grid-column: span 12;">
                                                <label>Print Address Option</label>
                                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4px 10px; min-height: 28px;">
                                                    <div style="display: flex; gap: 8px; align-items: center; border-right: 1px solid #cbd5e1; padding-right: 10px;">
                                                        <label style="margin:0; font-weight: 500; font-size: 11px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                                            <input type="radio" :name="'pa_' + index" :value="true" x-model="addr.use_default" @change="updatePreviews()" style="width:13px; height:13px; margin:0;"> Default
                                                        </label>
                                                        <label style="margin:0; font-weight: 500; font-size: 11px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                                                            <input type="radio" :name="'pa_' + index" :value="false" x-model="addr.use_default" @change="updatePreviews()" style="width:13px; height:13px; margin:0;"> Alternate
                                                        </label>
                                                    </div>
                                                    <div style="display: flex; gap: 10px; align-items: center;">
                                                        <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="addr.show_name" @change="updatePreviews()" style="width:13px; height:13px;"> Name</label>
                                                        <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="addr.show_address" @change="updatePreviews()" style="width:13px; height:13px;"> Address</label>
                                                        <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="addr.show_contact" @change="updatePreviews()" style="width:13px; height:13px;"> Contact Info</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group" style="grid-column: span 12;">
                                                <label>Address Preview Box</label>
                                                <textarea x-model="addr.preview" :readonly="addr.use_default === true || addr.use_default === 'true'" :style="(addr.use_default === true || addr.use_default === 'true') ? 'min-height: 70px; background-color: #f8fafc;' : 'min-height: 70px; background-color: #ffffff;'" placeholder="Live Address Preview..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>Remark</label>
                                        <textarea x-model="form.remark" style="height: 56px; min-height: 56px; resize: vertical;"></textarea>
                                    </div>
                                    <div class="form-group" style="grid-column: span 6;">
                                        <div class="form-row" style="margin-bottom: 0;">
                                            <div class="form-group" style="grid-column: span 6;">
                                                <label>Sales Person</label>
                                                <select class="select-custom-arrow" x-model="form.sales_person_id" :disabled="!vm.user_permissions.canEditSalesPerson">
                                                    <option value="">Select...</option>
                                                    @foreach($users as $u)
                                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group" style="grid-column: span 6;">
                                                <label>CP</label>
                                                <select class="select-custom-arrow" x-model="form.cs_person_id">
                                                    <option value="">Select...</option>
                                                    @foreach($users as $u)
                                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Accounting Setting -->
                        <div class="portlet light">
                            <div class="portlet-title">
                                <span class="caption-subject"><i class="fa fa-calculator"></i> Accounting Setting</span>
                                <button type="button" class="btn-freightx" @click="copyFromLocalAddress()" style="font-size: 10px; padding: 3px 8px;"><i class="fa fa-copy"></i> Copy from Local Address</button>
                            </div>
                            <div class="portlet-body">
                                <div class="form-group" style="margin-bottom: 8px;">
                                    <label>Billing Address</label>
                                    <textarea x-model="form.billing_address" style="min-height: 75px; height: 75px;"></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>Tax ID / USCI No.</label>
                                        <input type="text" x-model="form.tax_id">
                                    </div>
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>Payment Type</label>
                                        <select class="select-custom-arrow" x-model="form.payment_type">
                                            <option value="COD">COD</option>
                                            <option value="CREDIT">CREDIT</option>
                                            <option value="PREPAID">PREPAID</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>Billing Options</label>
                                        <div style="display: flex; flex-direction: column; gap: 4px; padding: 5px 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; min-height: 26px; justify-content: center;">
                                            <label class="checkbox-item" style="cursor: pointer; font-size: 11px;"><input type="checkbox" x-model="form.track_1099" style="width: 13px; height: 13px;"> 1099 Report</label>
                                            <label class="checkbox-item" style="cursor: pointer; font-size: 11px;"><input type="checkbox" x-model="form.bill_to_agent" style="width: 13px; height: 13px;"> Bill To Agent</label>
                                        </div>
                                    </div>
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>CLM ID / No.</label>
                                        <input type="text" x-model="form.clm_id" placeholder="Enter CLM ID">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>Credit Term</label>
                                        <div style="display: flex; gap: 4px;">
                                            <select class="select-custom-arrow" x-model="form.credit_term_unit" style="flex: 1.4;">
                                                <option value="Days">Days</option>
                                                <option value="Months">Months</option>
                                                <option value="Years">Years</option>
                                            </select>
                                            <input type="text" x-model="form.credit_term_days" style="flex: 1;" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="form-group" style="grid-column: span 6;">
                                        <label>Credit Limit</label>
                                        <input type="text" x-model="form.credit_limit">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="grid-column: span 12;">
                                        <label>Accountant Name</label>
                                        <input type="text" x-model="form.accountant_name">
                                    </div>
                                </div>

                                <!-- Bank Account 1 Box -->
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; margin-bottom: 8px;">
                                    <div style="font-weight: 700; font-size: 11px; color: #2563eb; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                                        <i class="fa fa-university"></i> Bank Account 1
                                    </div>
                                    <div class="form-row" style="margin-bottom: 6px;">
                                        <div class="form-group" style="grid-column: span 8;">
                                            <label>Account Name 1</label>
                                            <input type="text" x-model="form.bank_account_name_1">
                                        </div>
                                        <div class="form-group" style="grid-column: span 4;">
                                            <label>Currency</label>
                                            <select class="select-custom-arrow" x-model="form.bank_currency_1_id">
                                                @foreach($currencies as $curr)
                                                    <option value="{{ $curr->id }}">{{ $curr->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row" style="margin-bottom: 0;">
                                        <div class="form-group" style="grid-column: span 12;">
                                            <label>Account No. 1</label>
                                            <input type="text" x-model="form.bank_account_no_1">
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Account 2 Box -->
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; margin-bottom: 8px;">
                                    <div style="font-weight: 700; font-size: 11px; color: #2563eb; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                                        <i class="fa fa-university"></i> Bank Account 2
                                    </div>
                                    <div class="form-row" style="margin-bottom: 6px;">
                                        <div class="form-group" style="grid-column: span 8;">
                                            <label>Account Name 2</label>
                                            <input type="text" x-model="form.bank_account_name_2">
                                        </div>
                                        <div class="form-group" style="grid-column: span 4;">
                                            <label>Currency</label>
                                            <select class="select-custom-arrow" x-model="form.bank_currency_2_id">
                                                @foreach($currencies as $curr)
                                                    <option value="{{ $curr->id }}">{{ $curr->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row" style="margin-bottom: 0;">
                                        <div class="form-group" style="grid-column: span 12;">
                                            <label>Account No. 2</label>
                                            <input type="text" x-model="form.bank_account_no_2">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row" style="margin-bottom: 0;">
                                    <div class="form-group" style="grid-column: span 12;">
                                        <label>Profit Share (%)</label>
                                        <input type="text" x-model="form.profit_share_percent" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pop-up Tips -->
                    <div class="portlet light" style="margin-top: 10px;">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-lightbulb-o"></i> Pop-up Tips</span>
                        </div>
                        <div class="portlet-body" style="padding: 10px;">
                            <div class="checkbox-container" style="display: flex; flex-wrap: wrap; gap: 8px 16px; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.door_to_door" style="width: 13px; height: 13px;"> Door to Door</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.bad_customer" style="width: 13px; height: 13px;"> Bad Customer</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.import_only" style="width: 13px; height: 13px;"> Import Only</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.export_only" style="width: 13px; height: 13px;"> Export Only</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.co_loader" style="width: 13px; height: 13px;"> Co-loader</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.custom_clear" style="width: 13px; height: 13px;"> Custom Clear</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.warehouse" style="width: 13px; height: 13px;"> Warehouse</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.isf_charges" style="width: 13px; height: 13px;"> ISF Charges</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.free_hand_cargo" style="width: 13px; height: 13px;"> Free Hand Cargo</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.nomination" style="width: 13px; height: 13px;"> Nomination</label>
                                <label class="checkbox-item" style="cursor: pointer;"><input type="checkbox" x-model="form.popup_tips.see_memo_remark" style="width: 13px; height: 13px;"> See Memo Remark</label>
                            </div>
                        </div>
                    <!-- Portal Access -->
                    <div class="portlet light" style="margin-top: 10px; border: 1px solid #93c5fd; background-color: #f0f7ff;">
                        <div class="portlet-title" style="background: #e0f2fe; border-bottom: 1px solid #bfdbfe;">
                            <span class="caption-subject" style="color: #1d4ed8; font-weight: 700;"><i class="fa fa-lock" style="color: #2563eb;"></i> Customer Portal Access (Optional)</span>
                        </div>
                        <div class="portlet-body" style="padding: 10px 12px;">
                            <div class="form-row" style="margin-bottom: 4px; align-items: flex-end;">
                                <div class="form-group" style="grid-column: span 5;">
                                    <label style="color: #1e293b; font-weight: 600;">Email Address for Login</label>
                                    <input type="email" x-model="form.portal_email" id="portal_email" @input="portalEmailTouched = true" placeholder="customer@example.com" style="border: 1px solid #94a3b8; background: #fff;">
                                </div>
                                <div class="form-group" style="grid-column: span 5;">
                                    <label style="color: #1e293b; font-weight: 600;">Password <span style="font-weight: normal; color: #64748b;" x-show="form.id">(Leave blank to keep current)</span></label>
                                    <input type="password" x-model="form.portal_password" id="portal_password" placeholder="Min 6 characters" style="border: 1px solid #94a3b8; background: #fff;">
                                </div>
                                <div class="form-group" style="grid-column: span 2;" x-show="form.id">
                                    <button type="button" class="btn-freightx" style="width: 100%; height: 26px; justify-content: center;" @click="grantPortalAccess()">
                                        <i class="fa fa-key"></i> Update Access
                                    </button>
                                </div>
                            </div>
                            <div style="margin-top: 6px; font-size: 11px; color: #475569;">
                                <i class="fa fa-info-circle" style="color: #2563eb;"></i> <strong>Note:</strong> If you are creating a new Trade Partner, simply fill in the email and password above and hit Save. The portal access will be created automatically. 
                            </div>
                        </div>
                    </div>

                    <!-- Memo Table -->
                    <div class="portlet light">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-sticky-note"></i> Memo</span>
                            <button type="button" class="btn-tool" @click="addMemo()"><i class="fa fa-plus"></i> Add Memo</button>
                        </div>
                        <div class="portlet-body" style="padding: 0;">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Action</th>
                                        <th>Subject</th>
                                        <th>Content</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(memo, index) in memos">
                                        <tr>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn-tool-icon" style="background:#ef4444; color:#fff; border-color:#ef4444;" @click="removeMemo(index)"><i class="fa fa-trash"></i></button>
                                            </td>
                                            <td><input type="text" x-model="memo.subject" style="height: 26px;"></td>
                                            <td><input type="text" x-model="memo.content" style="height: 26px;"></td>
                                            <td style="color: #888;" x-text="memo.user ? memo.user.name : loggedInUser"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="memos.length === 0">
                                        <td colspan="4" style="text-align: center; color: #999; padding: 15px;">No Memo Available</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Contact Person -->
                    <div class="portlet">
                        <div class="section-header-blue">
                            <span><i class="fa fa-users"></i> Contact Person Information</span>
                            <button type="button" class="btn-tool" @click="addContact()"><i class="fa fa-plus"></i> Add Contact</button>
                        </div>
                        <div class="portlet-body" style="padding: 0;">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 25px;"><input type="checkbox"></th>
                                        <th>Rep.</th>
                                        <th>Email Name</th>
                                        <th>Title</th>
                                        <th>Division</th>
                                        <th>Cell Phone</th>
                                        <th>Phone</th>
                                        <th>Fax</th>
                                        <th>Email Address</th>
                                        <th>Remark</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(contact, index) in contacts">
                                        <tr>
                                            <td style="text-align: center;"><input type="checkbox"></td>
                                            <td style="text-align: center;"><input type="checkbox" x-model="contact.is_representative" style="width: 13px; height: 13px;"></td>
                                            <td><input type="text" x-model="contact.email_name" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td><input type="text" x-model="contact.title" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td><input type="text" x-model="contact.division" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td><input type="text" x-model="contact.cell_phone" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td><input type="text" x-model="contact.phone" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td><input type="text" x-model="contact.fax" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td><input type="email" x-model="contact.email" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td><input type="text" x-model="contact.remark" style="height: 26px;" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"></td>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn-tool-icon" style="background:#ef4444; color:#fff; border-color:#ef4444;" @click="removeContact(index)" :disabled="!vm.user_permissions.hasEditContactPersonInfoPerm"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="contacts.length === 0">
                                        <td colspan="11" style="text-align: center; color: #999; padding: 20px;">No Data Available. Please click Add Contact above to add a new row.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Trade Party Accordion -->
                    <div class="portlet">
                        <div class="section-header-blue" @click="tradePartyOpen = !tradePartyOpen">
                            <span>Trade Party</span>
                            <i class="fa" :class="tradePartyOpen ? 'fa-angle-up' : 'fa-angle-down'"></i>
                        </div>
                        <div x-show="tradePartyOpen" x-collapse>
                            <template x-for="pt in partyTypes" :key="pt.db">
                                <div>
                                    <div class="party-row-header">
                                        <span x-text="pt.label"></span>
                                        <button type="button" class="btn-tool-icon" style="background:#3b82f6; color:#fff; border-color:#3b82f6;" @click="addRelatedParty(pt.db)" :disabled="!vm.user_permissions.hasEditTradePartyPerm"><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div style="padding: 5px 12px 15px;">
                                        <table class="table-custom" style="background: #fff;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 25px;"><input type="checkbox"></th>
                                                    <th style="width: 60px;">Default</th>
                                                    <th>Company Name / Related Partner</th>
                                                    <th>Description</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(party, idx) in relatedParties.filter(p => p.party_type === pt.db)">
                                                    <tr>
                                                        <td style="text-align: center;"><input type="checkbox"></td>
                                                        <td style="text-align: center;"><input type="checkbox" x-model="party.is_default" style="width:13px; height:13px;"></td>
                                                        <td>
                                                            <select class="select-custom-arrow" x-model="party.related_partner_id" style="height: 26px;" :disabled="!vm.user_permissions.hasEditTradePartyPerm">
                                                                <option value="">Select Partner...</option>
                                                                @foreach($allTradePartners as $tp)
                                                                    <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td><input type="text" x-model="party.description" style="height: 26px;" :disabled="!vm.user_permissions.hasEditTradePartyPerm"></td>
                                                        <td style="text-align: center;">
                                                            <button type="button" class="btn-tool-icon" style="background:#ef4444; color:#fff; border-color:#ef4444;" @click="removeRelatedParty(relatedParties.indexOf(party))" :disabled="!vm.user_permissions.hasEditTradePartyPerm"><i class="fa fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <tr x-show="relatedParties.filter(p => p.party_type === pt.db).length === 0">
                                                    <td colspan="5" style="text-align: center; color: #aaa; padding: 10px;">No Data Available. Click the + button to add a relation.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Accounting Setting Tab -->
                <div x-show="activeTab === 'accounting'" x-cloak x-data="{ transportMode: 'ocean-import' }">
                    <div class="portlet light">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-truck"></i> Default Freight Setting</span>
                        </div>
                        <div class="portlet-body" style="padding: 0;">
                            <!-- Transport Mode Sub-Tabs -->
                            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 2px; padding: 4px 6px 0;">
                                <template x-for="mode in ['ocean-import', 'ocean-export', 'air-import', 'air-export', 'truck', 'misc', 'warehouse']">
                                    <button
                                        @click="transportMode = mode"
                                        class="btn-sm"
                                        :style="transportMode === mode ? 'background: #fff; border: 1px solid #e2e8f0; border-bottom: none; color: #0f172a; margin-bottom: -1px; height: 26px; border-top: 2px solid #3b82f6;' : 'background: #f1f5f9; border: 1px solid #e2e8f0; color: #64748b; height: 26px; border-top: 2px solid transparent;'"
                                        style="padding: 0 12px; font-weight: 600; font-size: 10px; cursor: pointer; transition: all 0.15s;"
                                        x-text="mode.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')"
                                    ></button>
                                </template>
                            </div>

                            <div style="padding: 10px; background: #fff;">
                                <template x-for="section in [
                                    { title: 'Invoice (A/R) Default', pc: 'COLLECT', type: 'Our Sales' },
                                    { title: 'AP Default', pc: 'PREPAID', type: 'Our Cost' },
                                    { title: 'D/C Note Default', pc: 'COLLECT', type: 'Debit (Origin Revenue)' }
                                ]">
                                    <div style="margin-bottom: 16px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <h5 style="font-size: 11px; font-weight: 700; color: #3b82f6; text-transform: none; margin: 0;" x-text="section.title"></h5>
                                            <button type="button" class="btn-tool" @click="addDefaultFreight(transportMode, section.title, section.pc, section.type)"><i class="fa fa-plus"></i> Add Row</button>
                                        </div>
                                        <div class="charges-table-container">
                                            <table>
                                                <thead>
                                                    <tr style="background: #f1f5f9; color: #475569;">
                                                        <th style="width: 35px; padding: 2px;">Action</th>
                                                        <th style="width: 25px; padding: 2px;"><input type="checkbox" style="width:12px; height:12px;"></th>
                                                        <th x-show="['ocean-import', 'ocean-export'].includes(transportMode)" style="width: 140px; padding: 2px;">Ship Mode</th>
                                                        <th style="width: 120px; padding: 2px;">Freight Code</th>
                                                        <th style="padding: 2px;">Freight Description</th>
                                                        <th style="width: 70px; padding: 2px;">P/C</th>
                                                        <th style="width: 100px; padding: 2px;">Type</th>
                                                        <th style="width: 60px; padding: 2px;">Unit</th>
                                                        <th style="width: 60px; padding: 2px;">CUR.</th>
                                                        <th style="width: 45px; padding: 2px;">Vol.</th>
                                                        <th style="width: 65px; padding: 2px;">Rate</th>
                                                        <th style="width: 65px; padding: 2px;">Amount</th>
                                                        <th style="width: 65px; padding: 2px;">Agent Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(df, index) in defaultFreights.filter(f => f.transport_mode === transportMode && f.section === section.title)">
                                                        <tr style="height: 28px;">
                                                            <td style="padding: 2px; text-align: center;">
                                                                <button type="button" class="btn-tool-icon" style="background:#ef4444; color:#fff; border-color:#ef4444;" @click="removeDefaultFreight(defaultFreights.indexOf(df))"><i class="fa fa-trash"></i></button>
                                                            </td>
                                                            <td style="padding: 2px; text-align: center;"><input type="checkbox" style="width:12px; height:12px;"></td>
                                                            <td x-show="['ocean-import', 'ocean-export'].includes(transportMode)" style="padding: 2px;">
                                                                <select class="select-custom-arrow form-control-gf" x-model="df.ship_mode">
                                                                    <option value="All">All</option>
                                                                    <option value="FCL">FCL</option>
                                                                    <option value="LCL">LCL</option>
                                                                    <option value="FAK">FAK</option>
                                                                </select>
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <input type="text" class="form-control-gf" x-model="df.freight_code" placeholder="Code..." style="text-align: center;">
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <input type="text" class="form-control-gf" x-model="df.description" placeholder="Detail Description..." style="text-align: center;">
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <select class="select-custom-arrow form-control-gf" x-model="df.pc">
                                                                    <option value="COLLECT">COLLECT</option>
                                                                    <option value="PREPAID">PREPAID</option>
                                                                </select>
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <select class="select-custom-arrow form-control-gf" x-model="df.type">
                                                                    <option value="Our Sales">Our Sales</option>
                                                                    <option value="Our Cost">Our Cost</option>
                                                                    <option value="Debit (Origin Revenue)">Debit (Origin Revenue)</option>
                                                                </select>
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <select class="select-custom-arrow form-control-gf" x-model="df.unit">
                                                                    <option value="UNIT">UNIT</option>
                                                                    <option value="CBM">CBM</option>
                                                                    <option value="KG">KG</option>
                                                                </select>
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <select class="select-custom-arrow form-control-gf" x-model="df.currency_id">
                                                                    @foreach($currencies as $curr)
                                                                        <option value="{{ $curr->id }}">{{ $curr->code }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <input type="text" class="form-control-gf" x-model="df.volume" style="text-align: center;">
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <input type="text" class="form-control-gf" x-model="df.rate" style="text-align: center;">
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <input type="text" class="form-control-gf" x-model="df.amount" style="text-align: center;">
                                                            </td>
                                                            <td style="padding: 2px;">
                                                                <input type="text" class="form-control-gf" x-model="df.agent_amount" style="text-align: center;">
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <tr x-show="defaultFreights.filter(f => f.transport_mode === transportMode && f.section === section.title).length === 0">
                                                        <td colspan="13" style="text-align: center; color: #94a3b8; padding: 10px; font-size: 10px;">No default freights configured. Click Add Row above to define default fees.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commodity Setting Tab -->
                <div x-show="activeTab === 'commodity'" x-cloak>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-cube"></i> Shipper Commodity Setting</span>
                            <button type="button" class="btn-tool" @click="addCommodity()"><i class="fa fa-plus"></i> Add Commodity</button>
                        </div>
                        <div class="portlet-body">
                            <div class="charges-table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th style="width: 30px; text-align: center;"><input type="checkbox" style="width:12px; height:12px;"></th>
                                            <th style="width: 220px; text-align: center;"><span style="color: #ef4444;">*</span>Commodity Description</th>
                                            <th style="width: 150px; text-align: center;">PKG</th>
                                            <th style="width: 110px; text-align: center;">HTS Code</th>
                                            <th style="width: 80px; text-align: center;">PCS</th>
                                            <th style="width: 95px; text-align: center;">Net Weight</th>
                                            <th style="width: 95px; text-align: center;">Gross Weight</th>
                                            <th style="width: 95px; text-align: center;">Measurement</th>
                                            <th style="width: 90px; text-align: center;">Unit Price</th>
                                            <th style="width: 90px; text-align: center;">Amount</th>
                                            <th style="width: 50px; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(commodity, index) in commodities">
                                            <tr>
                                                <td style="text-align: center;"><input type="checkbox" style="width:12px; height:12px;"></td>
                                                <td><input type="text" class="form-control-gf" x-model="commodity.description" style="text-align: center;"></td>
                                                <td>
                                                    <select class="select-custom-arrow form-control-gf" x-model="commodity.package_unit_id">
                                                        <option value="">Select PKG...</option>
                                                        @foreach(\App\Models\PackageUnit::all() as $pkg)
                                                            <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control-gf" x-model="commodity.hts_code" style="text-align: center;"></td>
                                                <td><input type="text" class="form-control-gf" x-model="commodity.pcs" style="text-align: center;"></td>
                                                <td>
                                                    <div style="display: flex; gap: 2px;">
                                                        <input type="text" class="form-control-gf" x-model="commodity.net_weight" style="flex: 1; text-align: center;">
                                                        <select class="select-custom-arrow form-control-gf" x-model="commodity.net_weight_unit" style="width: 50px;">
                                                            <option value="KG">KG</option>
                                                            <option value="LB">LB</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 2px;">
                                                        <input type="text" class="form-control-gf" x-model="commodity.gross_weight" style="flex: 1; text-align: center;">
                                                        <select class="select-custom-arrow form-control-gf" x-model="commodity.gross_weight_unit" style="width: 50px;">
                                                            <option value="KG">KG</option>
                                                            <option value="LB">LB</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 2px;">
                                                        <input type="text" class="form-control-gf" x-model="commodity.measurement" style="flex: 1; text-align: center;">
                                                        <select class="select-custom-arrow form-control-gf" x-model="commodity.measurement_unit" style="width: 55px;">
                                                            <option value="CBM">CBM</option>
                                                            <option value="CFT">CFT</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" class="form-control-gf" x-model="commodity.unit_price" style="text-align: center;"></td>
                                                <td><input type="text" class="form-control-gf" x-model="commodity.amount" style="text-align: center;"></td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="btn-tool-icon" style="background:#ef4444; color:#fff; border-color:#ef4444;" @click="removeCommodity(index)"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="commodities.length === 0">
                                            <td colspan="11" style="text-align: center; color: #94a3b8; padding: 15px;">No commodities configured. Click Add Commodity to get started.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filing Setting Tab -->
                <div x-show="activeTab === 'filing'" x-cloak>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-folder-open"></i> Filing Setting</span>
                        </div>
                        <div class="portlet-body">
                            <ul class="gf-tabs" style="margin-bottom: 20px;">
                                <li class="active"><a href="javascript:void(0)">ISF</a></li>
                            </ul>
                            <div class="section-card" style="max-width: 500px;">
                                <div class="form-grid-4" style="grid-template-columns: 1fr 1fr;">
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">ISF Submission Name</label>
                                        <input type="text" x-model="filingSetting.isf_submission_name" class="form-control-gf" style="text-align: center;">
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">ISF Submission State</label>
                                        <select class="select-custom-arrow form-control-gf" x-model="filingSetting.isf_submission_state" style="text-align: center;">
                                            <option value="Encamp">Encamp</option>
                                            <option value="Draft">Draft</option>
                                            <option value="Filed">Filed</option>
                                        </select>
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">ISF Zip Code</label>
                                        <input type="text" x-model="filingSetting.isf_zip_code" class="form-control-gf" style="text-align: center;">
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Importer Code</label>
                                        <select class="select-custom-arrow form-control-gf" x-model="filingSetting.importer_code" style="text-align: center;">
                                            <option value="N/A">N/A</option>
                                            <option value="EIN">EIN</option>
                                            <option value="SSN">SSN</option>
                                        </select>
                                    </div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf">Importer No.</label>
                                        <input type="text" x-model="filingSetting.importer_no" class="form-control-gf" style="text-align: center;">
                                    </div>
                                </div>
                                <div style="margin-top: 15px;" x-show="vm.enableMagayaConfig">
                                    <button type="button" @click="checkBondStatus()" :disabled="checkingBond" class="btn-tool" x-text="checkingBond ? 'Checking...' : 'Check Bond Status'"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doc Center Tab -->
                <div x-show="activeTab === 'doc'" x-cloak>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-file-text-o"></i> Document Center</span>
                        </div>
                        <div class="portlet-body">
                            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 20px;">
                                <!-- Left: Select Files -->
                                <div class="section-card" style="padding: 15px;"
                                     @dragover.prevent="isDragging = true"
                                     @dragleave.prevent="isDragging = false"
                                     @drop.prevent="handleDrop($event)"
                                     :style="isDragging ? 'border: 2px dashed #3b82f6; background: #eff6ff;' : ''">
                                    <h5 style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 15px; display: flex; align-items: center; gap: 5px;">
                                        <i class="fa fa-cog" style="color: #94a3b8;"></i> SELECT FILES
                                    </h5>
                                    <div style="border: 2px dashed #cbd5e1; height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; font-size: 11px; margin-bottom: 15px; text-align: center; padding: 10px; border-radius: 4px;">
                                        <i class="fa fa-cloud-upload" style="font-size: 24px; margin-bottom: 5px; color: #cbd5e1;"></i>
                                        <span>Drag and drop file(s) here...</span>
                                    </div>
                                    <div style="font-size: 11px;">
                                        <input type="file" @change="handleFileSelect($event)" multiple style="border: none; background: transparent; height: auto; padding: 0;">
                                    </div>
                                </div>

                                <!-- Right: Document List -->
                                <div class="section-card" style="padding: 15px;">
                                    <h5 style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 15px; display: flex; align-items: center; gap: 5px;">
                                        <i class="fa fa-cog" style="color: #94a3b8;"></i> DOCUMENT LIST <span style="color: #94a3b8; font-weight: 400; margin-left: 5px;" x-text="documents.length + ' file(s)'">0 file(s)</span>
                                    </h5>
                                    <div style="margin-bottom: 10px; display: flex; gap: 4px;" x-show="documents.length > 0">
                                        <button type="button" class="btn-tool" @click="emailSelectedDocuments()" title="Email Selected"><i class="fa fa-envelope"></i> Email</button>
                                        <button type="button" class="btn-tool" style="background: #ef4444; border-color: #ef4444;" @click="deleteSelectedDocuments()" title="Delete Selected"><i class="fa fa-trash"></i> Delete</button>
                                    </div>
                                    <div class="charges-table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th style="width: 25px; text-align: center;"><input type="checkbox" @change="toggleSelectAllTools($event)" style="width:12px; height:12px;"></th>
                                                    <th style="text-align: center;">NAME</th>
                                                    <th style="width: 80px; text-align: center;">DATE</th>
                                                    <th style="width: 60px; text-align: center;">SIZE</th>
                                                    <th style="width: 80px; text-align: center;">TYPE</th>
                                                    <th style="width: 140px; text-align: center;">ACTIONS</th>
                                                    <th style="width: 100px; text-align: center;">CREATOR</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="doc in documents" :key="doc.id">
                                                    <tr>
                                                        <td style="text-align: center;"><input type="checkbox" :value="doc.id" x-model="selectedDocIds" style="width:12px; height:12px;"></td>
                                                        <td><a :href="'/storage/' + doc.file_path" class="link-blue" x-text="doc.file_name"></a></td>
                                                        <td style="text-align: center;" x-text="new Date(doc.created_at).toLocaleDateString()"></td>
                                                        <td style="text-align: center;" x-text="(doc.file_size / 1024).toFixed(1) + ' KB'"></td>
                                                        <td style="text-align: center;" x-text="doc.file_extension || 'FILE'"></td>
                                                        <td style="text-align: center;">
                                                            <div style="display: flex; gap: 4px; justify-content: center;">
                                                                <button type="button" @click="emailDocument(doc.id)" class="btn-tool" style="font-size: 9px; padding: 0 6px;">Email</button>
                                                                <button type="button" @click="deleteDocument(doc.id)" class="btn-tool" style="background:#ef4444; border-color:#ef4444; font-size:9px; padding: 0 6px;">Delete</button>
                                                            </div>
                                                        </td>
                                                        <td style="text-align: center;" x-text="doc.uploader ? doc.uploader.name : 'System'"></td>
                                                    </tr>
                                                </template>
                                                <tr x-show="documents.length === 0">
                                                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 20px;">No Documents uploaded yet.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Tab -->
                <div x-show="activeTab === 'status'" x-cloak>
                    <div class="portlet light">
                        <div class="portlet-title">
                            <span class="caption-subject"><i class="fa fa-history"></i> Trade Partner Status</span>
                        </div>
                        <div class="portlet-body">
                            <h5 style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 20px;">Change Log</h5>

                            <!-- Timeline -->
                            <div style="position: relative; padding-left: 150px;">
                                <!-- Vertical Line -->
                                <div style="position: absolute; left: 135px; top: 0; bottom: 0; width: 4px; background: #e2e8f0; border-radius: 2px;"></div>

                                <template x-for="log in logs">
                                    <div style="position: relative; margin-bottom: 30px; display: flex; align-items: flex-start;">
                                        <!-- Time column -->
                                        <div style="position: absolute; left: -140px; text-align: right; width: 100px; font-size: 10px; color: #64748b;">
                                            <div style="font-weight: 400;" x-text="log.date"></div>
                                            <div style="font-weight: 600; font-size: 11px;" x-text="log.time"></div>
                                        </div>

                                        <!-- Icon bubble -->
                                        <div style="position: absolute; left: -26px; width: 28px; height: 28px; background: #3b82f6; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 11px; z-index: 10; border: 2px solid #fff;" x-text="log.icon">
                                        </div>

                                        <!-- Content card -->
                                        <div class="section-card" style="padding: 10px 15px; flex: 1; margin-left: 20px; position: relative;">
                                            <!-- Arrow for card -->
                                            <div style="position: absolute; left: -8px; top: 8px; width: 0; height: 0; border-top: 6px solid transparent; border-bottom: 6px solid transparent; border-right: 8px solid #fff;"></div>

                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                                <span style="font-weight: 700; color: #1e293b; font-size: 11px;" x-text="log.action"></span>
                                            </div>
                                            <div style="color: #94a3b8; font-size: 10px; margin-bottom: 4px;" x-text="log.user"></div>
                                            <template x-if="log.info">
                                                <div style="font-size: 10px; color: #3b82f6;">
                                                    <span style="color: #64748b;">Info: </span> <span x-text="log.info"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="logs.length === 0" style="text-align: center; color: #94a3b8; padding: 20px;">
                                    No change logs available.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>

</x-layout>
