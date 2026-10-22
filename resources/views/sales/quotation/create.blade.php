<x-layout>
@php
    $currentUser = auth()->user()->name ?? '';
    $saved = $isEdit || $isCopy;
@endphp
    @push('scripts')
<script>
    function showToast(type, message) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        const colors = { success: '#10b981', error: '#ef4444', info: '#3b82f6', warning: '#f59e0b' };
        const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle', warning: 'fa-warning' };
        toast.style.cssText = `background:${colors[type] || '#333'};color:#fff;padding:12px 20px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;min-width:280px;animation:slideIn 0.3s ease;`;
        toast.innerHTML = `<i class="fa ${icons[type] || 'fa-info-circle'}"></i> <span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3000);
    }

    function showConfirm(title, message) {
        return new Promise((resolve) => {
            let modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;';
            modal.innerHTML = `
                <div style="background:#fff;border-radius:8px;padding:20px;width:380px;box-shadow:0 10px 25px rgba(0,0,0,0.2);">
                    <h4 style="margin:0 0 10px 0;font-size:15px;font-weight:600;color:#1e293b;">${title}</h4>
                    <p style="margin:0 0 20px 0;font-size:13px;color:#64748b;">${message}</p>
                    <div style="display:flex;justify-content:flex-end;gap:10px;">
                        <button id="confirm-cancel" style="padding:6px 14px;border:1px solid #cbd5e1;background:#fff;border-radius:4px;font-size:12px;cursor:pointer;">Cancel</button>
                        <button id="confirm-ok" style="padding:6px 14px;border:none;background:#ef4444;color:#fff;border-radius:4px;font-size:12px;cursor:pointer;">Delete</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            modal.querySelector('#confirm-cancel').onclick = () => { modal.remove(); resolve(false); };
            modal.querySelector('#confirm-ok').onclick = () => { modal.remove(); resolve(true); };
        });
    }

    window.oldInputData = @json(request()->old());

    if (!window.quotationPageShowHandlerAdded) {
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
        window.quotationPageShowHandlerAdded = true;
    }

    window.getFormValue = function(field, defaultValue = '') {
        if (window.oldInputData && window.oldInputData[field] !== undefined && window.oldInputData[field] !== null && window.oldInputData[field] !== '') {
            return window.oldInputData[field];
        }
        return defaultValue;
    };

    function quotationForm() {
        return {
            saved: @json($saved),
            isEdit: @json($isEdit),
            editId: @json($q?->id),
            activeTab: 'basic',
            moreFields: true,
            statusMenuOpen: false,
            saving: false,
            successMsg: '',
            errorMsg: '',
            errorList: [],
            status: { label: @json($q?->status ?? 'Draft'), color: @json($statusColors[$q?->status ?? 'Draft'] ?? '#888') },
            statuses: @json($statuses),

            shipping_type: window.getFormValue('shipping_type', @json($q?->transport_mode ?? '')),
            customer_id: String(window.getFormValue('customer_id', @json((string)($q?->customer_id ?? '')))),
            quote_no: window.getFormValue('quote_no', @json($q?->quote_no ?? '')),
            auto_gen: {{ ($isEdit || $isCopy) ? 'false' : (request()->old('quote_no') ? 'false' : 'true') }},
            valid_date: window.getFormValue('valid_date', @json(($q?->expiry_date ?? $q?->valid_date)?->format('Y-m-d') ?? '')),
            enable_valid: {{ (($isEdit || $isCopy) && ($q?->expiry_date || $q?->valid_date)) || request()->old('valid_date') ? 'true' : 'false' }},
            create_date: window.getFormValue('create_date', @json(($q?->quote_date ?? $q?->create_date ?? now())->format('Y-m-d'))),
            office_id: String(window.getFormValue('office_id', @json((string)($q?->office_id ?? optional($offices->first())->id ?? '')))),
            agent_id: String(window.getFormValue('agent_id', @json((string)($q?->agent_id ?? '')))),
            ship_mode: window.getFormValue('ship_mode', @json($q?->ship_mode ?? '')),
            service_term_origin: window.getFormValue('service_term_origin', @json($serviceTermParts[0] ?? 'CY')),
            service_term_dest: window.getFormValue('service_term_dest', @json($serviceTermParts[1] ?? 'CY')),
            incoterms_id: window.getFormValue('incoterms_id', @json($q?->incoterms_id ?? '')),
            country_of_origin: window.getFormValue('country_of_origin', @json($q?->country_of_origin ?? '')),
            sales_person_id: String(window.getFormValue('sales_person_id', @json((string)($q?->sales_person_id ?? '')))),
            op_id: String(window.getFormValue('op_id', @json((string)($q?->op_id ?? '')))),
            booking_no: window.getFormValue('booking_no', @json($q?->booking_no ?? '')),
            po_no: window.getFormValue('po_no', @json($q?->po_no ?? '')),
            commodity: window.getFormValue('commodity', @json($q?->commodity ?? '')),
            hts_code: window.getFormValue('hts_code', @json($q?->hts_code ?? '')),
            pkg_qty: window.getFormValue('pkg_qty', @json($q?->pkg_qty ?? '')),
            pkg_unit: window.getFormValue('pkg_unit', @json($q?->pkg_unit ?? 'CARTON(S)')),
            weight_kg: window.getFormValue('weight_kg', @json($q?->weight_kg ?? '')),
            weight_lb: window.getFormValue('weight_lb', @json($q?->weight_lb ?? '')),
            volume_cbm: window.getFormValue('volume_cbm', @json($q?->volume_cbm ?? '')),
            volume_cft: window.getFormValue('volume_cft', @json($q?->volume_cft ?? '')),
            carrier_id: String(window.getFormValue('carrier_id', @json((string)($q?->carrier_id ?? '')))),
            via: window.getFormValue('via', @json($q?->via ?? '')),
            tt: window.getFormValue('tt', @json($q?->tt ?? '')),
            departure: window.getFormValue('departure', @json($q?->departure ?? '')),
            destination: window.getFormValue('destination', @json($q?->destination ?? '')),
            liner_code: window.getFormValue('liner_code', @json($q?->liner_code ?? '')),
            final_destination: window.getFormValue('final_destination', @json($q?->final_destination ?? '')),
            place_of_receipt: window.getFormValue('place_of_receipt', @json($q?->place_of_receipt ?? '')),
            place_of_delivery: window.getFormValue('place_of_delivery', @json($q?->place_of_delivery ?? '')),
            schedule_id: String(window.getFormValue('schedule_id', @json((string)($q?->schedule_id ?? '')))),
            pol_id: String(window.getFormValue('pol_id', @json((string)($q?->pol_id ?? '')))),
            pod_id: String(window.getFormValue('pod_id', @json((string)($q?->pod_id ?? '')))),
            description: window.getFormValue('description', @json($q?->description ?? '')),
            remark: window.getFormValue('remark', @json($q?->quotation_remark ?? '')),
            internal_remark: window.getFormValue('internal_remark', @json($q?->internal_remark ?? '')),

            customers: @json($customers),
            ports: @json($ports),
            currencies: @json($currencies),
            carriers: @json($carriers),
            offices: @json($offices),
            salesPersons: @json($salesPersons),
            users: @json($users),
            agents: @json($agents),
            schedules: @json($schedules),
            currentUser: @json($currentUser),

            freightRows: {!! $freightsJson !== '[]' ? $freightsJson : '[ { "selected": false, "pol": "", "pod": "", "carrier": "", "currency_id": "' . ($currencies->first()->id ?? '') . '", "rate_20gp": "", "rate_40gp": "", "rate_40hc": "" } ]' !!},
            destRows: {!! $destJson !== '[]' ? $destJson : '[ { "show": true, "freight_code": "", "unit": "", "currency_id": "' . ($currencies->first()->id ?? '') . '", "all_qty": "1", "all_rate": "", "separate_qty": "1", "separate_rate": "", "rate_20gp_qty": "1", "rate_20gp": "", "rate_40gp_qty": "1", "rate_40gp": "", "rate_40hc_qty": "1", "rate_40hc": "", "remark": "" } ]' !!},

            documents: @json($q ? $q->documents ?? [] : []),

            get totalFreight() { return this.freightRows.reduce((s, r) => s + (parseFloat(r.rate_20gp) || 0) + (parseFloat(r.rate_40gp) || 0) + (parseFloat(r.rate_40hc) || 0), 0); },
            get total20GP() { return this.freightRows.reduce((s, r) => s + (parseFloat(r.rate_20gp) || 0), 0); },
            get total40GP() { return this.freightRows.reduce((s, r) => s + (parseFloat(r.rate_40gp) || 0), 0); },
            get total40HC() { return this.freightRows.reduce((s, r) => s + (parseFloat(r.rate_40hc) || 0), 0); },
            get totalDest() {
                return this.destRows.reduce((s, r) => {
                    return s + (parseFloat(r.all_rate)||0)*(parseFloat(r.all_qty)||1) + (parseFloat(r.separate_rate)||0)*(parseFloat(r.separate_qty)||1) + (parseFloat(r.rate_20gp)||0)*(parseFloat(r.rate_20gp_qty)||1) + (parseFloat(r.rate_40gp)||0)*(parseFloat(r.rate_40gp_qty)||1) + (parseFloat(r.rate_40hc)||0)*(parseFloat(r.rate_40hc_qty)||1);
                }, 0);
            },

            addFreightRow() { this.freightRows.push({ selected: false, pol: '', pod: '', carrier: '', currency_id: @json($currencies->first()->id ?? ''), rate_20gp: '', rate_40gp: '', rate_40hc: '' }); },
            addFreightRows(n) { for (let i = 0; i < n; i++) this.addFreightRow(); },
            removeSelectedFreight() { for (let i = this.freightRows.length - 1; i >= 0; i--) { if (this.freightRows[i].selected) this.freightRows.splice(i, 1); } },

            addDestRow() { this.destRows.push({ show: true, freight_code: '', unit: '', currency_id: '', all_qty: '1', all_rate: '', separate_qty: '1', separate_rate: '', rate_20gp_qty: '1', rate_20gp: '', rate_40gp_qty: '1', rate_40gp: '', rate_40hc_qty: '1', rate_40hc: '', remark: '' }); },
            addDestRows(n) { for (let i = 0; i < n; i++) this.addDestRow(); },
            removeSelectedDest() { for (let i = this.destRows.length - 1; i >= 0; i--) { if (this.destRows[i].show) this.destRows.splice(i, 1); } },

            ToolsOpen: false,
            previewOpen: false,
            publishModalOpen: false,
            isFavorite: false,
            showFreightTotals: false,
            showDestTotals: false,
            dimensionsOpen: false,
            dimPkgQty: @json($q?->pkg_qty ?? ''),
            dimPkgUnit: @json($q?->pkg_unit ?? 'CARTON(S)'),
            dimWeightKg: @json($q?->weight_kg ?? ''),
            dimWeightLb: @json($q?->weight_lb ?? ''),
            dimCbm: @json($q?->volume_cbm ?? ''),
            dimCft: @json($q?->volume_cft ?? ''),

            get quoteDisplayNo() { return this.quote_no || 'Auto-generated'; },

            copyQuotation() {
                this.ToolsOpen = false;
                if (this.editId || this.isEdit) {
                    showToast('info', 'Copying Quotation details...');
                    this.isEdit = false;
                    this.saved = false;
                    this.editId = null;
                    this.quote_no = '';
                    this.auto_gen = true;
                    if (window.history && window.history.pushState) {
                        window.history.pushState({}, '', '/sales/quotation/create');
                    }
                    showToast('success', 'Copied to new Quotation form. Click SAVE BASIC to persist.');
                } else {
                    this.quote_no = '';
                    this.auto_gen = true;
                    showToast('info', 'Form details ready for new Quotation.');
                }
            },

            async deleteQuotation() {
                this.ToolsOpen = false;
                if (!this.editId || !this.saved) {
                    showToast('warning', 'Cannot delete an unsaved Quotation.');
                    return;
                }
                var confirmed = await showConfirm('Delete Quotation', 'Are you sure you want to delete Quotation ' + (this.quoteDisplayNo || '') + '?');
                if (!confirmed) return;
                
                try {
                    const resp = await fetch('/sales/quotations/' + this.editId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });
                    const data = await resp.json();
                    if (data.success || resp.ok) {
                        showToast('success', 'Quotation deleted successfully.');
                        setTimeout(() => { window.location.href = '/sales/quotation/list'; }, 600);
                    } else {
                        showToast('error', data.message || 'Failed to delete quotation.');
                    }
                } catch (e) {
                    console.error('Delete error:', e);
                    showToast('error', 'Failed to delete quotation.');
                }
            },

            printQuotation() {
                this.ToolsOpen = false;
                this.previewOpen = true;
            },

            toggleFavorite() {
                this.ToolsOpen = false;
                this.isFavorite = !this.isFavorite;
                showToast('success', this.isFavorite ? 'Quotation added to favorites!' : 'Removed from favorites.');
            },

            publishCustomerPortal() {
                this.ToolsOpen = false;
                this.publishModalOpen = true;
            },

            async confirmPublishPortal() {
                this.publishModalOpen = false;
                showToast('success', 'Quotation successfully published to Customer Portal!');
            },

            openDimensions() {
                this.dimPkgQty = this.pkg_qty; this.dimPkgUnit = this.pkg_unit;
                this.dimWeightKg = this.weight_kg; this.dimWeightLb = this.weight_lb;
                this.dimCbm = this.volume_cbm; this.dimCft = this.volume_cft;
                this.dimensionsOpen = true;
            },
            convertKgToLb() { const kg = parseFloat(this.dimWeightKg); if (kg) this.dimWeightLb = (kg * 2.20462).toFixed(2); },
            convertLbToKg() { const lb = parseFloat(this.dimWeightLb); if (lb) this.dimWeightKg = (lb / 2.20462).toFixed(2); },
            convertCbmToCft() { const cbm = parseFloat(this.dimCbm); if (cbm) this.dimCft = (cbm * 35.315).toFixed(2); },
            convertCftToCbm() { const cft = parseFloat(this.dimCft); if (cft) this.dimCbm = (cft / 35.315).toFixed(2); },
            applyDimensions() {
                this.pkg_qty = this.dimPkgQty; this.pkg_unit = this.dimPkgUnit;
                this.weight_kg = this.dimWeightKg; this.weight_lb = this.dimWeightLb;
                this.volume_cbm = this.dimCbm; this.volume_cft = this.dimCft;
                this.dimensionsOpen = false;
            },

            clearForm() {
                if (!confirm('Clear all form fields?')) return;
                this.shipping_type = ''; this.customer_id = ''; this.quote_no = ''; this.auto_gen = true;
                this.valid_date = ''; this.enable_valid = false; this.create_date = '';
                this.office_id = ''; this.agent_id = ''; this.ship_mode = '';
                this.service_term_origin = 'CY'; this.service_term_dest = 'CY';
                this.incoterms_id = ''; this.country_of_origin = '';
                this.sales_person_id = ''; this.op_id = '';
                this.booking_no = ''; this.po_no = ''; this.commodity = ''; this.hts_code = '';
                this.pkg_qty = ''; this.pkg_unit = 'CARTON(S)';
                this.weight_kg = ''; this.weight_lb = ''; this.volume_cbm = ''; this.volume_cft = '';
                this.carrier_id = ''; this.via = ''; this.tt = '';
                this.departure = ''; this.destination = ''; this.liner_code = ''; this.final_destination = '';
                this.place_of_receipt = ''; this.place_of_delivery = ''; this.schedule_id = '';
                this.pol_id = ''; this.pod_id = '';
                this.description = ''; this.remark = ''; this.internal_remark = '';
                this.freightRows = [{ selected: false, pol: '', pod: '', carrier: '', currency_id: @json($currencies->first()->id ?? ''), rate_20gp: '', rate_40gp: '', rate_40hc: '' }];
                this.destRows = [{ show: true, freight_code: '', unit: '', currency_id: '', all_qty: '1', all_rate: '', separate_qty: '1', separate_rate: '', rate_20gp_qty: '1', rate_20gp: '', rate_40gp_qty: '1', rate_40gp: '', rate_40hc_qty: '1', rate_40hc: '', remark: '' }];
            },

            buildFormData() {
                const fd = new FormData();
                fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
                if (this.isEdit) fd.append('_method', 'PUT');
                const fields = ['shipping_type','customer_id','office_id','agent_id','ship_mode','incoterms_id','country_of_origin','sales_person_id','op_id','booking_no','po_no','commodity','hts_code','pkg_qty','pkg_unit','weight_kg','weight_lb','volume_cbm','volume_cft','carrier_id','via','tt','departure','destination','liner_code','final_destination','place_of_receipt','place_of_delivery','schedule_id','pol_id','pod_id','description','internal_remark'];
                fields.forEach(f => fd.append(f, this[f] ?? ''));
                fd.append('quote_no', this.auto_gen ? '' : this.quote_no);
                fd.append('valid_date', this.enable_valid ? this.valid_date : '');
                fd.append('create_date', this.create_date);
                fd.append('service_term_origin', this.service_term_origin);
                fd.append('service_term_dest', this.service_term_dest);
                fd.append('remark', this.remark);
                fd.append('status', this.status.label);
                fd.append('freight_rows', JSON.stringify(this.freightRows));
                fd.append('dest_rows', JSON.stringify(this.destRows));
                return fd;
            },

            async saveMainTab() {
                if (this.saving) return;
                if (!this.shipping_type) { this.errorMsg = 'Shipping Type is required.'; showToast('error', this.errorMsg); return; }
                if (!this.customer_id) { this.errorMsg = 'Customer is required.'; showToast('error', this.errorMsg); return; }
                this.saving = true; this.errorMsg = ''; this.errorList = [];
                try {
                    const fd = this.buildFormData();
                    const url = this.isEdit ? '/sales/quotations/' + this.editId : '/sales/quotations';
                    const resp = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                    const data = await resp.json();
                    this.saving = false;
                    if (!resp.ok) {
                        if (data.errors) { this.errorList = Object.values(data.errors).flat(); this.errorMsg = this.errorList[0]; }
                        else { this.errorMsg = data.message || 'Save failed.'; }
                        showToast('error', this.errorMsg);
                        return;
                    }
                    if (data.success) {
                        if (!this.isEdit && data.id) {
                            showToast('success', 'Quotation created successfully.');
                            setTimeout(() => { window.location.href = '/sales/quotation/' + data.id + '/edit'; }, 600);
                            return;
                        }
                        this.successMsg = data.message || 'Saved successfully.';
                        showToast('success', this.successMsg);
                        setTimeout(() => this.successMsg = '', 3000);
                    }
                } catch (e) {
                    this.saving = false;
                    this.errorMsg = e.message || 'Network error.';
                    showToast('error', this.errorMsg);
                }
            },

            async saveAllTabs() {
                if (this.saving) return;
                this.saving = true; this.errorMsg = ''; this.errorList = [];
                try {
                    const fd = this.buildFormData();
                    const url = '/sales/quotations/' + this.editId;
                    const resp = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                    const data = await resp.json();
                    this.saving = false;
                    if (!resp.ok) {
                        if (data.errors) { this.errorList = Object.values(data.errors).flat(); this.errorMsg = this.errorList[0]; }
                        else { this.errorMsg = data.message || 'Save failed.'; }
                        showToast('error', this.errorMsg);
                        return;
                    }
                    if (data.success) {
                        this.successMsg = data.message || 'Updated successfully.';
                        showToast('success', this.successMsg);
                        setTimeout(() => this.successMsg = '', 3000);
                    }
                } catch (e) {
                    this.saving = false;
                    this.errorMsg = e.message || 'Network error.';
                    showToast('error', this.errorMsg);
                }
            },

            async uploadDocument(event) {
                const files = event.target.files;
                if (!files.length || !this.editId) return;
                for (const file of files) {
                    const fd = new FormData();
                    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
                    fd.append('file', file);
                    fd.append('documentable_type', 'App\\Models\\Quotation');
                    fd.append('documentable_id', this.editId);
                    try {
                        const resp = await fetch('/sales/quotations/' + this.editId + '/documents', { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                        const data = await resp.json();
                        if (data.success && data.document) {
                            this.documents.push(data.document);
                            showToast('success', 'File uploaded successfully.');
                        }
                    } catch (e) {
                        console.error('Upload failed', e);
                        showToast('error', 'Failed to upload document.');
                    }
                }
                event.target.value = '';
            },

            async deleteDocument(docId) {
                var confirmed = await showConfirm('Delete Document', 'Are you sure you want to delete this document?');
                if (!confirmed) return;
                try {
                    const resp = await fetch('/sales/quotations/documents/' + docId, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } });
                    const data = await resp.json();
                    if (data.success) {
                        this.documents = this.documents.filter(d => d.id !== docId);
                        showToast('success', 'Document deleted successfully.');
                    }
                } catch (e) {
                    console.error('Delete failed', e);
                    showToast('error', 'Failed to delete document.');
                }
            },

            init() {
                this.$watch('activeTab', val => {
                    if (this.saved) {
                        sessionStorage.setItem('quotationActiveTab', val);
                    } else {
                        sessionStorage.setItem('quotationActiveTab', 'basic');
                    }
                    try {
                        const u = new URL(window.location.href);
                        u.searchParams.set('tab', val);
                        window.history.replaceState({}, '', u);
                    } catch(e) {}
                });

                const urlTab = new URLSearchParams(window.location.search).get('tab');
                const savedTab = sessionStorage.getItem('quotationActiveTab');

                if (urlTab) {
                    this.activeTab = (urlTab === 'Tools' && !this.saved) ? 'basic' : urlTab;
                } else if (savedTab && this.saved) {
                    this.activeTab = savedTab;
                } else {
                    this.activeTab = 'basic';
                }

                if (this.agent_id || this.booking_no || this.po_no || this.country_of_origin || this.via || this.tt || this.liner_code || this.departure || this.destination || this.final_destination || this.place_of_receipt || this.place_of_delivery || this.schedule_id) {
                    this.moreFields = true;
                }
            }
        };
    }
</script>
@endpush
    <x-form-styles />
    @push('styles')
    <style>
        .section-heading { font-size: 10px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; margin-bottom: 8px; }
        .grid-header { background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 3px 8px; font-size: 9px; font-weight: 700; color: #475569; display: flex; letter-spacing: 0.3px; text-transform: uppercase; min-height: 18px; align-items: center; }
        .grid-row { border-bottom: 1px solid #e2e8f0; padding: 2px 8px; display: flex; align-items: center; background: #fff; min-height: 20px; }
        .grid-row:hover { background: #f8fafc; }
        .grid-row .form-control-gf { height: 18px !important; font-size: 9px !important; padding: 0 3px !important; }
        .grid-row select.form-control-gf { background-size: 6px !important; padding-right: 10px !important; background-position: right 2px center !important; }
        .footer-actions { position: sticky; bottom: 0; background: #fff; padding: 6px 15px; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; gap: 8px; z-index: 100; margin-top: 8px; }
        .input-group-addon { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0 5px; font-size: 9px; color: #475569; border-radius: 2px; height: 20px; display: flex; align-items: center; gap: 3px; }
        .doc-center-wrapper { display: flex; gap: 15px; padding: 15px; background: #fff; }
        .upload-zone { width: 300px; border-right: 1px solid #e2e8f0; padding-right: 15px; }
        .dropzone-box { border: 1px dashed #cbd5e1; border-radius: 4px; padding: 30px 20px; text-align: center; color: #94a3b8; font-size: 11px; margin-bottom: 15px; background: #fafafa; }
        .doc-list-zone { flex: 1; }
        .doc-table { width: 100%; border-collapse: collapse; }
        .doc-table th { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 6px 8px; font-size: 9px; color: #64748b; text-transform: uppercase; text-align: left; }
        .doc-table td { padding: 8px; border-bottom: 1px solid #f8fafc; font-size: 11px; color: #334155; }
        .text-muted { color: #94a3b8 !important; }
        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; border-radius: 2px; font-size: 10px; font-weight: 700; text-transform: uppercase; color: #fff; cursor: pointer; user-select: none; transition: all 0.2s ease; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 1px 2px rgba(0,0,0,0.06); line-height: 16px; }
        .status-badge:hover { filter: brightness(1.1); transform: translateY(-1px); }
        .status-badge .fa-angle-down { font-size: 9px; transition: transform 0.25s ease; }
        .status-badge.open .fa-angle-down { transform: rotate(180deg); }
        .status-dropdown-menu { position: absolute; top: 100%; left: 0; background: #fff; border: 1px solid #cbd5e1; box-shadow: 0 6px 18px -4px rgba(0,0,0,0.12); z-index: 1050; min-width: 150px; margin-top: 4px; border-radius: 3px; padding: 4px 0; }
        .status-dropdown-menu::before { content: ''; position: absolute; top: -5px; left: 16px; transform: rotate(45deg); width: 8px; height: 8px; background: #fff; border-left: 1px solid #cbd5e1; border-top: 1px solid #cbd5e1; }
        .status-item { padding: 6px 12px; display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 10px; font-weight: 600; color: #334155; transition: all 0.15s ease; position: relative; }
        .status-item:hover { background: #f1f5f9; color: #0f172a; }
        .status-item .fa { width: 12px; text-align: center; font-size: 9px; }
        .status-item .status-check { margin-left: auto; color: #3b82f6; opacity: 0; transition: opacity 0.15s; }
        .status-item.active-status .status-check { opacity: 1; }
        
        .section-card { background: #ffffff; border: 1px solid #cbd5e1; padding: 10px 12px; border-radius: 3px; margin-bottom: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
    </style>
    @endpush

    <div class="page-content" x-data="quotationForm()" x-init="init()">
        @if($errors->any())
            <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:15px;">
                <strong><i class="fa fa-exclamation-circle"></i> Validation Error</strong>
                <ul style="margin:5px 0 0 15px;padding:0;">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
        <div>
            <template x-if="successMsg">
                <div class="alert alert-success" style="position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:99999;padding:8px 24px;border-radius:6px;background:#26c281;color:#fff;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(38,194,129,0.35);white-space:nowrap;" x-text="successMsg"></div>
            </template>
            <template x-if="errorMsg && errorList.length > 0">
                <div style="position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:99999;background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;box-shadow:0 4px 20px rgba(229,115,115,0.35);max-width:80vw;">
                    <strong style="display:flex;align-items:center;gap:6px;font-size:12px;"><i class="fa fa-exclamation-circle"></i> Validation Error</strong>
                    <ul style="margin:5px 0 0 15px;padding:0;font-size:11px;">
                        <template x-for="err in errorList" :key="err"><li x-text="err"></li></template>
                    </ul>
                </div>
            </template>
            <template x-if="errorMsg && errorList.length === 0">
                <div class="alert alert-danger" style="position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:99999;padding:8px 24px;border-radius:6px;background:#fce4e4;border:1px solid #e57373;color:#c62828;font-size:12px;font-weight:600;box-shadow:0 4px 20px rgba(229,115,115,0.35);max-width:90vw;white-space:normal;text-align:center;" x-text="errorMsg"></div>
            </template>
        </div>

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i> <a href="/sales/quotation/list">Sales Quotation</a></li>
                <li><i class="fa fa-angle-right"></i> <span style="color: #333; font-weight: 700;" x-text="isEdit ? 'Edit Quotation: ' + quoteDisplayNo : 'New Quotation'"></span></li>
            </ul>
        </div>

        <!-- Toolbar (Ocean Import Style) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <h1 class="caption-subject" style="font-size: 18px; font-weight: 700; margin: 0; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-file-text-o" style="color: #3b82f6;"></i>
                    <span x-text="isEdit ? 'Edit Sales Quotation: ' + quoteDisplayNo : 'Create Sales Quotation'"></span>
                </h1>

                {{-- Status Badge Dropdown --}}
                <div style="position: relative;">
                    <div class="status-badge" :class="{ 'open': statusMenuOpen }" @click="statusMenuOpen = !statusMenuOpen" :style="'background:' + status.color">
                        <span x-text="status.label"></span> <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="status-dropdown-menu" x-show="statusMenuOpen" @click.away="statusMenuOpen = false" style="display: none;">
                        <template x-for="s in statuses" :key="s.label">
                            <div class="status-item" :class="{ 'active-status': s.label === status.label }" @click="status = s; statusMenuOpen = false">
                                <i class="fa fa-circle" :style="'color:' + s.color"></i>
                                <span x-text="s.label"></span>
                                <i class="fa fa-check status-check"></i>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                <template x-if="!saved">
                    <button type="button" class="btn-freightx" style="background:#f59e0b;" @click="saveMainTab()" :disabled="saving">
                        <i class="fa" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        <span x-text="saving ? 'SAVING...' : 'SAVE BASIC'"></span>
                    </button>
                </template>
                <template x-if="saved">
                    <button type="button" class="btn-freightx" @click="saveAllTabs()" :disabled="saving">
                        <i class="fa" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        <span x-text="saving ? 'SAVING...' : 'SAVE CHANGES'"></span>
                    </button>
                </template>

                <a href="/sales/quotation/list" class="btn-default-gf"><i class="fa fa-arrow-left"></i> BACK TO LIST</a>
            </div>
        </div>

        <!-- Main Tabs + Tools Dropdown Bar (Ocean Import Style) -->
        <div style="display:flex;align-items:stretch;border-bottom:2px solid #e5e7eb;margin-bottom:15px;">
            <ul class="gf-tabs" style="border-bottom:none;margin-bottom:0;flex:1;display:flex;">
                <li :class="(activeTab === 'basic' || activeTab === 'main') ? 'active' : ''" @click="activeTab = 'basic'"><a>Basic</a></li>
                <li style="display: none !important;" :class="[activeTab === 'Tools' ? 'active' : '', !saved ? 'disabled-tab' : '']" @click="saved ? activeTab = 'Tools' : null"><a>Doc Center</a></li>
            </ul>

            <!-- Tools Dropdown on Right Side of Tabs -->
            <div style="position:relative;display:flex;align-items:center;padding:0 8px;" @click.away="ToolsOpen = false">
                <button type="button" @click="ToolsOpen = !ToolsOpen"
                    style="display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #d1d5db;border-radius:4px;padding:4px 12px;font-size:12px;color:#374151;cursor:pointer;white-space:nowrap;height:26px;">
                    <i class="fa fa-cogs" style="color:#6b7280;"></i> Docs
                    <i class="fa fa-angle-down" style="font-size:10px;color:#9ca3af;" :style="ToolsOpen ? 'transform:rotate(180deg)' : ''"></i>
                </button>
                <div x-show="ToolsOpen" x-transition style="position:absolute;right:8px;top:calc(100% + 4px);background:#fff;border:1px solid #e5e7eb;border-radius:6px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:9999;min-width:200px;padding:6px 0;" x-cloak>
                    {{-- Tools for Basic Tab --}}
                    <template x-if="activeTab === 'basic' || activeTab === 'main'">
                        <div>
                            <button type="button" class="oi-Tools-item" @click="copyQuotation(); ToolsOpen=false">
                                <i class="fa fa-copy" style="color: #3b82f6; width: 16px;"></i> Copy
                            </button>
                            <button type="button" class="oi-Tools-item" @click="deleteQuotation(); ToolsOpen=false" :disabled="!saved"
                                    :style="saved ? '' : 'color: #cbd5e1; cursor: not-allowed; opacity: 0.6;'">
                                <i class="fa fa-trash-o" :style="saved ? 'color: #ef4444; width: 16px;' : 'color: #cbd5e1; width: 16px;'"></i> Delete
                            </button>
                            <div style="height: 1px; background: #f1f5f9; margin: 4px 0;"></div>
                            <button type="button" class="oi-Tools-item" @click="printQuotation(); ToolsOpen=false">
                                <i class="fa fa-print" style="color: #64748b; width: 16px;"></i> Print
                            </button>
                            <button type="button" class="oi-Tools-item" @click="toggleFavorite(); ToolsOpen=false">
                                <i class="fa" :class="isFavorite ? 'fa-star text-amber-500' : 'fa-star-o'" style="color: #f59e0b; width: 16px;"></i>
                                <span x-text="isFavorite ? 'Remove from Favorite' : 'Add to Favorite'"></span>
                            </button>
                            <button type="button" class="oi-Tools-item" @click="publishCustomerPortal(); ToolsOpen=false">
                                <i class="fa fa-globe" style="color: #0284c7; width: 16px;"></i> Publish to Customer Portal
                            </button>
                        </div>
                    </template>

                    {{-- Tools for Doc Center Tab --}}
                    <template x-if="activeTab === 'Tools'">
                        <div>
                            <button type="button" class="oi-Tools-item" @click="printQuotation(); ToolsOpen=false">
                                <i class="fa fa-print" style="color: #64748b; width: 16px;"></i> Print
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <style>
        .oi-Tools-item {
            display:flex;align-items:center;gap:8px;width:100%;padding:7px 16px;
            font-size:12px;color:#374151;background:transparent;border:none;cursor:pointer;text-align:left;
            transition:background 0.15s;
        }
        .oi-Tools-item:hover:not(:disabled) { background:#f3f4f6; }
        </style>

        <div class="portlet light" style="padding-top: 5px;">
            <div class="portlet-body">
                {{-- ======================== BASIC / MAIN TAB ======================== --}}
                <div x-show="activeTab === 'basic' || activeTab === 'main'" class="main-grid" style="display: block;">
                    <div class="section-card" style="margin-top: 2px;">
                        <div class="section-heading">Shipping Type</div>
                        <div class="form-group-gf" style="margin-bottom: 0;">
                            <label class="form-label-gf"><span style="color:#ef4444;margin-right:2px;">*</span>Shipping Type</label>
                            <div class="form-input-container" style="max-width: 300px;">
                                <select class="form-control-gf" name="shipping_type" x-model="shipping_type">
                                    <option value="">Select...</option>
                                    <option value="Ocean Import">Ocean Import</option>
                                    <option value="Ocean Export">Ocean Export</option>
                                    <option value="Air Import">Air Import</option>
                                    <option value="Air Export">Air Export</option>
                                    <option value="Truck">Truck</option>
                                    <option value="Warehouse">Warehouse</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-heading" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Basic Information</span>
                            <div style="font-size: 10px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                                <span>More fields</span>
                                <input type="checkbox" x-model="moreFields">
                            </div>
                        </div>
                        <div class="form-grid-4">
                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span style="color:#ef4444;margin-right:2px;">*</span>Customer</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="customer_id" x-model="customer_id">
                                            <option value="">Select...</option>
                                            @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span style="color:#ef4444;margin-right:2px;">*</span>Quote No.</label>
                                    <div class="form-input-container">
                                        <span class="input-group-addon"><input type="checkbox" x-model="auto_gen"></span>
                                        <input type="text" class="form-control-gf" :readonly="auto_gen" name="quote_no" x-model="quote_no" placeholder="Auto-gen">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Valid Date</label>
                                    <div class="form-input-container">
                                        <span class="input-group-addon"><input type="checkbox" x-model="enable_valid"></span>
                                        <input type="date" class="form-control-gf" :disabled="!enable_valid" name="valid_date" x-model="valid_date">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Create Date</label>
                                    <div class="form-input-container">
                                        <input type="date" class="form-control-gf" name="create_date" x-model="create_date">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Office</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="office_id" x-model="office_id">
                                            <option value="">Select...</option>
                                            @foreach($offices as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Agent</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="agent_id" x-model="agent_id">
                                            <option value="">Select...</option>
                                            @foreach($agents as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Booking No.</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="booking_no" x-model="booking_no" placeholder="Booking #">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">P.O. No.</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="po_no" x-model="po_no" placeholder="P.O. #">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Ship Mode</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="ship_mode" x-model="ship_mode">
                                            <option value="">Select...</option>
                                            <option value="FCL">FCL</option>
                                            <option value="LCL">LCL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Service Term</label>
                                    <div class="form-input-container" style="display:flex;align-items:center;gap:3px;">
                                        <select class="form-control-gf" name="service_term_origin" x-model="service_term_origin">
                                            <option value="CY">CY</option><option value="CFS">CFS</option><option value="Door">Door</option>
                                        </select>
                                        <span style="font-size:10px;color:#64748b;">~</span>
                                        <select class="form-control-gf" name="service_term_dest" x-model="service_term_dest">
                                            <option value="CY">CY</option><option value="CFS">CFS</option><option value="Door">Door</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Incoterms</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="incoterms_id" x-model="incoterms_id">
                                            <option value="">Select...</option>
                                            <option value="EXW">EXW</option><option value="FOB">FOB</option><option value="CIF">CIF</option><option value="DAP">DAP</option><option value="DDP">DDP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Sales</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="sales_person_id" x-model="sales_person_id">
                                            <option value="">Select...</option>
                                            @foreach($salesPersons as $sp)<option value="{{ $sp->id }}">{{ $sp->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">OP</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="op_id" x-model="op_id">
                                            <option value="">Select...</option>
                                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Created by</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" readonly x-model="currentUser">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Country of Origin</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="country_of_origin" x-model="country_of_origin">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span style="color:#ef4444;margin-right:2px;">*</span>POL</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="pol_id" x-model="pol_id">
                                            <option value="">Select...</option>
                                            @foreach($ports as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf"><span style="color:#ef4444;margin-right:2px;">*</span>POD</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="pod_id" x-model="pod_id">
                                            <option value="">Select...</option>
                                            @foreach($ports as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Carrier</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="carrier_id" x-model="carrier_id">
                                            <option value="">Select...</option>
                                            @foreach($carriers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Commodity</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="commodity" x-model="commodity">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">HTS Code</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="hts_code" x-model="hts_code" placeholder="HTS Code...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Via</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="via" x-model="via" placeholder="Via...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">T/T</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="tt" x-model="tt" placeholder="Transit Time...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Liner Code</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="liner_code" x-model="liner_code" placeholder="Liner Code...">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Package</label>
                                    <div class="form-input-container" style="display:flex;gap:3px;">
                                        <input type="text" class="form-control-gf text-right" style="width:50px;flex:none;" name="pkg_qty" x-model="pkg_qty">
                                        <select class="form-control-gf" name="pkg_unit" x-model="pkg_unit">
                                            <option value="CARTON(S)">CARTON(S)</option><option value="PALLET(S)">PALLET(S)</option><option value="PIECES">PIECES</option><option value="CRATES">CRATES</option><option value="BAGS">BAGS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Weight</label>
                                    <div class="form-input-container" style="display:flex;gap:3px;align-items:center;">
                                        <input type="text" class="form-control-gf text-right" name="weight_kg" x-model="weight_kg">
                                        <span style="font-size:9px;color:#64748b;width:22px;">KGS</span>
                                        <input type="text" class="form-control-gf text-right" name="weight_lb" x-model="weight_lb">
                                        <span style="font-size:9px;color:#64748b;width:22px;">LBS</span>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Measurement</label>
                                    <div class="form-input-container" style="display:flex;gap:3px;align-items:center;">
                                        <input type="text" class="form-control-gf text-right" name="volume_cbm" x-model="volume_cbm">
                                        <span style="font-size:9px;color:#64748b;width:22px;">CBM</span>
                                        <input type="text" class="form-control-gf text-right" name="volume_cft" x-model="volume_cft">
                                        <span style="font-size:9px;color:#64748b;width:22px;">CFT</span>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <div class="form-label-gf"></div>
                                    <div class="form-input-container">
                                        <button class="btn-freightx" style="flex:1;height:20px;padding:2px 6px;" type="button" @click="openDimensions()"><i class="fa fa-calculator"></i> Set Dimensions</button>
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Departure</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="departure" x-model="departure" placeholder="Departure...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Destination</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="destination" x-model="destination" placeholder="Destination...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Final Destination</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="final_destination" x-model="final_destination" placeholder="Final Destination...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Place of Receipt</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="place_of_receipt" x-model="place_of_receipt" placeholder="Place of Receipt...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Place of Delivery</label>
                                    <div class="form-input-container">
                                        <input type="text" class="form-control-gf" name="place_of_delivery" x-model="place_of_delivery" placeholder="Place of Delivery...">
                                    </div>
                                </div>
                                <div class="form-group-gf">
                                    <label class="form-label-gf">Schedule</label>
                                    <div class="form-input-container">
                                        <select class="form-control-gf" name="schedule_id" x-model="schedule_id">
                                            <option value="">Select...</option>
                                            @foreach($schedules as $s)<option value="{{ $s->id }}">{{ ($s->schedule_no ?? '') . ($s->vessel_name ? ' - ' . $s->vessel_name : '') }}</option>@endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-heading" style="display:flex;justify-content:space-between;align-items:center;">
                            <span>Freight Rate</span>
                            <div style="display:flex;gap:4px;">
                                <button class="btn-freightx" @click="addFreightRow()" type="button"><i class="fa fa-plus"></i></button>
                                <button class="btn-default-gf" @click="addFreightRows(5)" type="button">+5</button>
                                <button class="btn-default-gf" @click="removeSelectedFreight()" type="button"><i class="fa fa-trash"></i></button>
                                <button class="btn-default-gf" type="button" @click="showFreightTotals = !showFreightTotals"><i class="fa fa-calculator"></i> Calculator</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <div class="grid-header">
                                <div style="width:30px;"><i class="fa fa-bars"></i></div>
                                <div style="width:35px;">Sel</div>
                                <div style="width:150px;">Port of Loading</div>
                                <div style="width:150px;">Port of Discharge</div>
                                <div style="width:130px;">Carrier</div>
                                <div style="width:85px;">Currency</div>
                                <div style="width:130px;">20GP</div>
                                <div style="width:130px;">40GP</div>
                                <div style="width:130px;">40HC</div>
                            </div>
                            <template x-for="(fr, idx) in freightRows" :key="idx">
                                <div class="grid-row">
                                    <div style="width:30px;"><i class="fa fa-bars" style="color:#ccc;"></i></div>
                                    <div style="width:35px;"><input type="checkbox" x-model="fr.selected"></div>
                                    <div style="width:150px;">
                                        <select class="form-control-gf" x-model="fr.pol">
                                            <option value="">Select...</option>
                                            @foreach($ports as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div style="width:150px;">
                                        <select class="form-control-gf" x-model="fr.pod">
                                            <option value="">Select...</option>
                                            @foreach($ports as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div style="width:130px;">
                                        <select class="form-control-gf" x-model="fr.carrier">
                                            <option value="">Select...</option>
                                            @foreach($carriers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div style="width:85px;">
                                        <select class="form-control-gf" x-model="fr.currency_id">
                                            <option value="">Cur</option>
                                            @foreach($currencies as $c)<option value="{{ $c->id }}">{{ $c->code }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div style="width:130px;"><input type="text" class="form-control-gf text-right" x-model="fr.rate_20gp" placeholder="Rate"></div>
                                    <div style="width:130px;"><input type="text" class="form-control-gf text-right" x-model="fr.rate_40gp" placeholder="Rate"></div>
                                    <div style="width:130px;"><input type="text" class="form-control-gf text-right" x-model="fr.rate_40hc" placeholder="Rate"></div>
                                </div>
                            </template>
                        </div>
                        <template x-if="showFreightTotals && freightRows.length > 0">
                            <div style="background:#f1f5f9;border:1px solid #e2e8f0;border-top:none;padding:4px 10px;display:flex;align-items:center;font-size:10px;color:#475569;border-radius:0 0 4px 4px;">
                                <span style="font-weight:700;color:#3b82f6;margin-right:20px;">TOTAL</span>
                                <span style="margin-right:15px;"><span style="color:#94a3b8;">20GP:</span> <strong x-text="total20GP.toFixed(2)"></strong></span>
                                <span style="margin-right:15px;"><span style="color:#94a3b8;">40GP:</span> <strong x-text="total40GP.toFixed(2)"></strong></span>
                                <span><span style="color:#94a3b8;">40HC:</span> <strong x-text="total40HC.toFixed(2)"></strong></span>
                            </div>
                        </template>
                    </div>

                    <div class="section-card">
                        <div class="section-heading" style="display:flex;justify-content:space-between;align-items:center;">
                            <span>Destination Charges</span>
                            <div style="display:flex;gap:4px;">
                                <button class="btn-freightx" @click="addDestRow()" type="button"><i class="fa fa-plus"></i></button>
                                <button class="btn-default-gf" @click="addDestRows(5)" type="button">+5</button>
                                <button class="btn-default-gf" @click="removeSelectedDest()" type="button"><i class="fa fa-trash"></i></button>
                                <button class="btn-default-gf" type="button" @click="showDestTotals = !showDestTotals"><i class="fa fa-calculator"></i> Calculator</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <div class="grid-header" style="border-bottom:none;">
                                <div style="width:28px;text-align:center;"><i class="fa fa-bars" style="font-size:9px;"></i></div>
                                <div style="width:40px;">Show</div>
                                <div style="width:120px;">Freight Code</div>
                                <div style="width:85px;">Unit</div>
                                <div style="width:85px;">Currency</div>
                                <div style="flex:1;display:flex;border-left:1px solid #e2e8f0;">
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;">All</div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;">Separate</div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;">20GP</div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;">40GP</div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;">40HC</div>
                                    <div style="min-width:65px;text-align:center;padding:0 4px;">Rmks</div>
                                </div>
                            </div>
                            <div class="grid-header" style="border-top:none;padding:2px 8px;min-height:16px;">
                                <div style="width:28px;"></div><div style="width:40px;"></div><div style="width:120px;"></div><div style="width:85px;"></div><div style="width:85px;"></div>
                                <div style="flex:1;display:flex;">
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;"><span style="color:#94a3b8;font-weight:400;">QTY | Price</span></div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;"><span style="color:#94a3b8;font-weight:400;">QTY | Price</span></div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;"><span style="color:#94a3b8;font-weight:400;">QTY | Price</span></div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;"><span style="color:#94a3b8;font-weight:400;">QTY | Price</span></div>
                                    <div style="min-width:95px;text-align:center;padding:0 4px;border-right:1px solid #e2e8f0;"><span style="color:#94a3b8;font-weight:400;">QTY | Price</span></div>
                                    <div style="min-width:65px;text-align:center;padding:0 4px;"></div>
                                </div>
                            </div>
                            <template x-for="(dr, idx) in destRows" :key="idx">
                                <div class="grid-row">
                                    <div style="width:28px;text-align:center;"><i class="fa fa-bars" style="color:#ccc;font-size:10px;"></i></div>
                                    <div style="width:40px;text-align:center;"><input type="checkbox" x-model="dr.show" style="margin:0;"></div>
                                    <div style="width:120px;">
                                        <select class="form-control-gf" x-model="dr.freight_code">
                                            <option value="">Code</option>
                                            <option value="THC">THC</option><option value="DOC">DOC</option><option value="CUSTOMS">Customs</option><option value="CLEANING">Cleaning</option><option value="SEAL">Seal</option><option value="CHASSIS">Chassis</option><option value="STORAGE">Storage</option><option value="OTHER">Other</option>
                                        </select>
                                    </div>
                                    <div style="width:85px;">
                                        <select class="form-control-gf" x-model="dr.unit">
                                            <option value=""></option>
                                            <option value="PER B/L">PER B/L</option><option value="PER CONTAINER">PER CONTAINER</option><option value="PER CBM">PER CBM</option><option value="PER TON">PER TON</option><option value="PER KG">PER KG</option><option value="LUMPSUM">LUMPSUM</option>
                                        </select>
                                    </div>
                                    <div style="width:85px;">
                                        <select class="form-control-gf" x-model="dr.currency_id">
                                            <option value="">Cur</option>
                                            @foreach($currencies as $c)<option value="{{ $c->id }}">{{ $c->code }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div style="flex:1;display:flex;">
                                        <div style="min-width:95px;padding:0 4px;border-right:1px solid #e2e8f0;display:flex;gap:4px;align-items:center;">
                                            <input type="text" class="form-control-gf text-right" style="width:38px;" x-model="dr.all_qty" placeholder="Qty">
                                            <input type="text" class="form-control-gf text-right" style="width:45px;" x-model="dr.all_rate" placeholder="Rate">
                                        </div>
                                        <div style="min-width:95px;padding:0 4px;border-right:1px solid #e2e8f0;display:flex;gap:4px;align-items:center;">
                                            <input type="text" class="form-control-gf text-right" style="width:38px;" x-model="dr.separate_qty" placeholder="Qty">
                                            <input type="text" class="form-control-gf text-right" style="width:45px;" x-model="dr.separate_rate" placeholder="Rate">
                                        </div>
                                        <div style="min-width:95px;padding:0 4px;border-right:1px solid #e2e8f0;display:flex;gap:4px;align-items:center;">
                                            <input type="text" class="form-control-gf text-right" style="width:38px;" x-model="dr.rate_20gp_qty" placeholder="Qty">
                                            <input type="text" class="form-control-gf text-right" style="width:45px;" x-model="dr.rate_20gp" placeholder="Rate">
                                        </div>
                                        <div style="min-width:95px;padding:0 4px;border-right:1px solid #e2e8f0;display:flex;gap:4px;align-items:center;">
                                            <input type="text" class="form-control-gf text-right" style="width:38px;" x-model="dr.rate_40gp_qty" placeholder="Qty">
                                            <input type="text" class="form-control-gf text-right" style="width:45px;" x-model="dr.rate_40gp" placeholder="Rate">
                                        </div>
                                        <div style="min-width:95px;padding:0 4px;border-right:1px solid #e2e8f0;display:flex;gap:4px;align-items:center;">
                                            <input type="text" class="form-control-gf text-right" style="width:38px;" x-model="dr.rate_40hc_qty" placeholder="Qty">
                                            <input type="text" class="form-control-gf text-right" style="width:45px;" x-model="dr.rate_40hc" placeholder="Rate">
                                        </div>
                                        <div style="min-width:65px;padding:0 4px;">
                                            <input type="text" class="form-control-gf" style="width:100%;" x-model="dr.remark" placeholder="Remark">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <template x-if="showDestTotals && destRows.length > 0">
                            <div style="background:#f1f5f9;border:1px solid #e2e8f0;border-top:none;padding:4px 10px;display:flex;align-items:center;font-size:10px;color:#475569;border-radius:0 0 4px 4px;">
                                <span style="font-weight:700;color:#3b82f6;margin-right:20px;">TOTAL</span>
                                <span><span style="color:#94a3b8;">All Charges:</span> <strong x-text="totalDest.toFixed(2)"></strong></span>
                            </div>
                        </template>
                    </div>

                    <div class="section-card">
                        <div class="section-heading">Summary by Container</div>
                        <table class="table-custom" style="margin-bottom:0;">
                            <thead><tr><th>Port of Loading</th><th>Port of Discharge</th><th>Carrier</th><th>Currency</th><th class="text-right">20GP</th><th class="text-right">40GP</th><th class="text-right">40HC</th></tr></thead>
                            <tbody>
                                <template x-for="(fr, idx) in freightRows" :key="'s'+idx">
                                    <tr>
                                        <td x-text="ports.find(p => p.id == fr.pol)?.name || '-'"></td>
                                        <td x-text="ports.find(p => p.id == fr.pod)?.name || '-'"></td>
                                        <td x-text="carriers.find(c => c.id == fr.carrier)?.name || '-'"></td>
                                        <td x-text="currencies.find(c => c.id == fr.currency_id)?.code || '-'"></td>
                                        <td class="text-right" x-text="fr.rate_20gp ? parseFloat(fr.rate_20gp).toFixed(2) : '-'"></td>
                                        <td class="text-right" x-text="fr.rate_40gp ? parseFloat(fr.rate_40gp).toFixed(2) : '-'"></td>
                                        <td class="text-right" x-text="fr.rate_40hc ? parseFloat(fr.rate_40hc).toFixed(2) : '-'"></td>
                                    </tr>
                                </template>
                                <template x-if="freightRows.length > 0">
                                    <tr style="font-weight:700;background:#f8fafc;">
                                        <td colspan="4" class="text-right"><strong>TOTAL</strong></td>
                                        <td class="text-right" x-text="total20GP.toFixed(2)"></td>
                                        <td class="text-right" x-text="total40GP.toFixed(2)"></td>
                                        <td class="text-right" x-text="total40HC.toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                        <div class="section-card" style="margin-bottom:0;">
                            <div class="section-heading">Description</div>
                            <textarea class="form-control-gf" style="height:50px;resize:vertical;" name="description" x-model="description"></textarea>
                        </div>
                        <div class="section-card" style="margin-bottom:0;">
                            <div class="section-heading">Quotation Remark</div>
                            <textarea class="form-control-gf" style="height:50px;resize:vertical;" name="remark" x-model="remark"></textarea>
                        </div>
                        <div class="section-card" style="margin-bottom:0;">
                            <div class="section-heading">Internal Remark</div>
                            <textarea class="form-control-gf" style="height:50px;resize:vertical;" name="internal_remark" x-model="internal_remark"></textarea>
                        </div>
                    </div>
                </div>

                {{-- ======================== DOC CENTER TAB ======================== --}}
                <div x-show="activeTab === 'Tools'" class="main-grid" style="display: none;">
                    <div class="section-card">
                        <div class="section-heading">Document Center</div>
                        <div style="display:flex;gap:15px;">
                            <div style="width:300px;border-right:1px solid #e2e8f0;padding-right:15px;">
                                <div class="dropzone-box" id="dropzone" style="cursor:pointer;" onclick="document.getElementById('doc-file-input').click();">
                                    <i class="fa fa-cloud-upload" style="font-size:28px;display:block;margin-bottom:8px;color:#94a3b8;"></i>
                                    Click or drag files here to upload
                                </div>
                                <input type="file" id="doc-file-input" style="display:none;" multiple @change="uploadDocument($event)">
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <span style="font-size:10px;color:#64748b;font-weight:600;">DOCUMENT LIST <span class="text-muted" x-text="'(' + documents.length + ' files)'"></span></span>
                                </div>
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th style="width:200px;">NAME</th>
                                            <th style="width:120px;">DATE</th>
                                            <th style="width:80px;">SIZE</th>
                                            <th style="width:100px;">TYPE</th>
                                            <th>ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="doc in documents" :key="doc.id">
                                            <tr>
                                                <td x-text="doc.file_name"></td>
                                                <td x-text="new Date(doc.created_at).toLocaleDateString()"></td>
                                                <td x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '-'"></td>
                                                <td x-text="doc.file_extension || '-'"></td>
                                                <td>
                                                    <a :href="doc.download_url || ('/sales/quotations/documents/' + doc.id + '/download')" class="btn-default-gf" style="font-size:9px;padding:2px 5px;text-decoration:none;" target="_blank"><i class="fa fa-download"></i></a>
                                                    <button type="button" class="btn-default-gf" style="font-size:9px;padding:2px 5px;color:#e74c3c;" @click="deleteDocument(doc.id)"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="documents.length === 0">
                                            <tr><td colspan="5" style="text-align:center;padding:30px;color:#94a3b8;font-style:italic;">No documents uploaded yet.</td></tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Set Dimensions Modal --}}
        <div x-show="dimensionsOpen" class="modal-overlay" style="display:none;" x-cloak @click.away="dimensionsOpen = false">
            <div class="modal-container" style="max-width:450px;">
                <div class="modal-header">
                    <span><i class="fa fa-calculator"></i> Set Dimensions</span>
                    <button type="button" @click="dimensionsOpen = false" style="background:none;border:none;cursor:pointer;font-size:18px;">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group-gf">
                        <label class="form-label-gf">Package</label>
                        <div class="form-input-container" style="display:flex;gap:3px;">
                            <input type="text" class="form-control-gf text-right" style="width:60px;flex:none;" x-model="dimPkgQty" placeholder="Qty">
                            <select class="form-control-gf" x-model="dimPkgUnit">
                                <option value="CARTON(S)">CARTON(S)</option><option value="PALLET(S)">PALLET(S)</option><option value="PIECES">PIECES</option><option value="CRATES">CRATES</option><option value="BAGS">BAGS</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Weight</label>
                        <div class="form-input-container" style="display:flex;gap:3px;align-items:center;">
                            <input type="text" class="form-control-gf text-right" x-model="dimWeightKg" @input.debounce="convertKgToLb()" placeholder="KG">
                            <span style="font-size:9px;color:#64748b;width:22px;">KGS</span>
                            <input type="text" class="form-control-gf text-right" x-model="dimWeightLb" @input.debounce="convertLbToKg()" placeholder="LB">
                            <span style="font-size:9px;color:#64748b;width:22px;">LBS</span>
                        </div>
                    </div>
                    <div class="form-group-gf">
                        <label class="form-label-gf">Measurement</label>
                        <div class="form-input-container" style="display:flex;gap:3px;align-items:center;">
                            <input type="text" class="form-control-gf text-right" x-model="dimCbm" @input.debounce="convertCbmToCft()" placeholder="CBM">
                            <span style="font-size:9px;color:#64748b;width:25px;">CBM</span>
                            <input type="text" class="form-control-gf text-right" x-model="dimCft" @input.debounce="convertCftToCbm()" placeholder="CFT">
                            <span style="font-size:9px;color:#64748b;width:25px;">CFT</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-default-gf" @click="dimensionsOpen = false">Cancel</button>
                    <button class="btn-freightx" @click="applyDimensions()"><i class="fa fa-check"></i> Apply to Form</button>
                </div>
            </div>
        </div>

        {{-- Preview & Print Modal --}}
        <div x-show="previewOpen" class="modal-overlay" style="display:none;" x-cloak @click.away="previewOpen = false">
            <div class="modal-container" style="max-width:700px;">
                <div class="modal-header">
                    <span>Preview &mdash; <span x-text="quoteDisplayNo"></span></span>
                    <button type="button" @click="previewOpen = false" style="background:none;border:none;cursor:pointer;font-size:18px;">&times;</button>
                </div>
                <div class="modal-body" style="font-size:11px;">
                    <div class="section-heading">Basic Information</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:10px;">
                        <div><span style="color:#64748b;">Status:</span> <strong x-text="status.label"></strong></div>
                        <div><span style="color:#64748b;">Shipping Type:</span> <strong x-text="shipping_type || '-'"></strong></div>
                        <div><span style="color:#64748b;">Customer:</span> <strong x-text="customers.find(c=>c.id==customer_id)?.name || '-'"></strong></div>
                        <div><span style="color:#64748b;">Office:</span> <strong x-text="offices.find(o=>o.id==office_id)?.name || '-'"></strong></div>
                        <div><span style="color:#64748b;">Sales:</span> <strong x-text="salesPersons.find(s=>s.id==sales_person_id)?.name || '-'"></strong></div>
                        <div><span style="color:#64748b;">POL:</span> <strong x-text="ports.find(p=>p.id==pol_id)?.name || '-'"></strong></div>
                        <div><span style="color:#64748b;">POD:</span> <strong x-text="ports.find(p=>p.id==pod_id)?.name || '-'"></strong></div>
                        <div><span style="color:#64748b;">Carrier:</span> <strong x-text="carriers.find(c=>c.id==carrier_id)?.name || '-'"></strong></div>
                        <div><span style="color:#64748b;">Commodity:</span> <strong x-text="commodity || '-'"></strong></div>
                    </div>
                    <template x-if="description || remark">
                        <div>
                            <div class="section-heading">Notes</div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div x-show="description" style="background:#f8fafc;padding:8px;border-radius:4px;border:1px solid #e2e8f0;">
                                    <div style="font-weight:700;color:#475569;font-size:10px;margin-bottom:4px;">Description</div>
                                    <div x-text="description" style="font-size:10px;color:#334155;"></div>
                                </div>
                                <div x-show="remark" style="background:#f8fafc;padding:8px;border-radius:4px;border:1px solid #e2e8f0;">
                                    <div style="font-weight:700;color:#475569;font-size:10px;margin-bottom:4px;">Remark</div>
                                    <div x-text="remark" style="font-size:10px;color:#334155;"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer" style="display:flex;justify-content:space-between;">
                    <button class="btn-freightx" @click="window.print()"><i class="fa fa-print"></i> Print Document</button>
                    <button class="btn-default-gf" @click="previewOpen = false">Close</button>
                </div>
            </div>
        </div>

        {{-- Publish to Customer Portal Modal --}}
        <div x-show="publishModalOpen" class="modal-overlay" style="display:none;" x-cloak @click.away="publishModalOpen = false">
            <div class="modal-container" style="max-width:500px;">
                <div class="modal-header">
                    <span><i class="fa fa-globe"></i> Publish to Customer Portal</span>
                    <button type="button" @click="publishModalOpen = false" style="background:none;border:none;cursor:pointer;font-size:18px;">&times;</button>
                </div>
                <div class="modal-body" style="font-size:12px;color:#334155;">
                    <p style="margin-bottom:12px;">Are you sure you want to publish <strong>Quotation <span x-text="quoteDisplayNo"></span></strong> to the Customer Portal?</p>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:4px;font-size:11px;">
                        <div><strong style="color:#64748b;">Target Customer:</strong> <span x-text="customers.find(c=>c.id==customer_id)?.name || 'Not Selected'"></span></div>
                        <div><strong style="color:#64748b;">Shipping Type:</strong> <span x-text="shipping_type || 'N/A'"></span></div>
                        <div><strong style="color:#64748b;">Status:</strong> <span x-text="status.label"></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-default-gf" @click="publishModalOpen = false">Cancel</button>
                    <button class="btn-freightx" @click="confirmPublishPortal()"><i class="fa fa-check"></i> Publish Now</button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
