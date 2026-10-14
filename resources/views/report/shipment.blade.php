<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
            cursor: pointer;
            position: absolute;
            right: 0;
            width: 22px;
            height: 100%;
        }
        .date-input-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .date-input-wrap .fa-calendar {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 10px;
            color: #94a3b8;
            pointer-events: none;
        }
        .form-group-gf {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            width: 100%;
            min-height: 24px;
            gap: 0;
        }
        .form-label-gf {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            display: inline-block;
            width: 110px;
            min-width: 110px;
            text-align: right;
            margin-right: 8px;
            white-space: nowrap;
            line-height: 20px;
        }
        .form-input-container {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
            min-width: 0;
        }
        
        /* Toast Notification Styles */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            padding: 12px 18px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 500;
            z-index: 9999;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .toast-notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast-notification i {
            font-size: 16px;
        }
        
        .toast-notification.toast-success {
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .toast-notification.toast-success i {
            color: #10b981;
        }
        
        .toast-notification.toast-error {
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .toast-notification.toast-error i {
            color: #ef4444;
        }
        
        .toast-notification.toast-info {
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        
        .toast-notification.toast-info i {
            color: #3b82f6;
        }
    </style>
    @endpush

    <div x-data="shipmentReport()" x-init="init()" class="page-content">

        {{-- Page Bar --}}
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><a href="#" target="_blank"><i class="fa fa-home"></i></a></li>
                <li><i class="fa fa-angle-right"></i></li>
                <li><a href="/report" style="color:#337ab7;">Reports</a></li>
                <li><i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333; font-weight:700;">Shipment Report</span></li>
            </ul>
        </div>

        {{-- Portlet --}}
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject"><i class="fa fa-ship"></i> Shipment Report</span>
                </div>
            </div>

            <div class="portlet-body">

                <div class="section-card">
                    {{-- Report Type --}}
                    <div class="form-group-gf">
                        <label class="form-label-gf">Report Type</label>
                        <div class="form-input-container">
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="report_type" value="shipment" x-model="form.report_type"> Shipment Based
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="report_type" value="container" x-model="form.report_type"> Container Based
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Trade Partner --}}
                    <div class="form-group-gf">
                        <label class="form-label-gf">Trade Partner</label>
                        <div class="form-input-container">
                            <div style="position:relative; width:320px;" @click.outside="partnerDropdown=false">
                                <input type="text" class="form-control-gf" x-model="partnerSearch" @input.debounce.300ms="searchPartners()" @focus="partnerDropdown=true; if(!partnerSearch) filteredPartners=partners.slice(0,20);" placeholder="Type to search trade partner...">
                                <div style="position:absolute; right:4px; top:50%; transform:translateY(-50%); display:flex; gap:3px; z-index:2;">
                                    <i class="fa fa-times" style="cursor:pointer; font-size:9px; color:#94a3b8;" @click="clearPartner(); partnerDropdown=false;"></i>
                                    <i class="fa fa-angle-down" style="cursor:pointer; font-size:9px; color:#94a3b8;" @click="partnerDropdown=!partnerDropdown; if(!partnerSearch) filteredPartners=partners.slice(0,20);"></i>
                                </div>
                                <div x-show="partnerDropdown" x-cloak x-transition style="position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #cbd5e1; border-radius:2px; max-height:200px; overflow-y:auto; z-index:100; box-shadow:0 4px 8px rgba(0,0,0,0.12);">
                                    <div style="padding:6px 8px; font-size:10px; color:#94a3b8;" x-show="filteredPartners.length === 0">No results found</div>
                                    <template x-for="p in filteredPartners" :key="p.id">
                                        <div @click="selectPartner(p)" style="padding:5px 8px; font-size:10px; cursor:pointer; border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                                            <span x-text="p.name" style="font-weight:600; color:#1e293b;"></span>
                                            <span style="color:#94a3b8; margin-left:4px; font-size:9px;" x-text="p.type ? '(' + p.type + ')' : ''"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Period --}}
                    <div class="form-group-gf">
                        <label class="form-label-gf">Period</label>
                        <div class="form-input-container">
                            <div class="radio-group" style="margin-right:10px; gap:12px;">
                                <label class="radio-label">
                                    <input type="radio" name="date_type" value="post_date" x-model="form.date_type"> Post Date
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="date_type" value="etd" x-model="form.date_type"> ETD
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="date_type" value="eta" x-model="form.date_type"> ETA
                                </label>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div class="date-input-wrap">
                                    <input type="date" class="form-control-gf" x-model="form.date_from" style="width:140px; padding-right:20px;">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <span style="color:#94a3b8; font-size:10px;">to</span>
                                <div class="date-input-wrap">
                                    <input type="date" class="form-control-gf" x-model="form.date_to" style="width:140px; padding-right:20px;">
                                    <i class="fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Shipment Type --}}
                    <div class="form-group-gf">
                        <label class="form-label-gf">Shipment Type</label>
                        <div class="form-input-container">
                            <select class="form-control-gf" x-model="form.ship_type" style="width:220px;">
                                <option value="">All Types</option>
                                <option value="ocean_import">Ocean Import</option>
                                <option value="ocean_export">Ocean Export</option>
                                <option value="air_import">Air Import</option>
                                <option value="air_export">Air Export</option>
                                <option value="truck">Truck</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:6px; align-items:center; margin-top:12px;">
                    <button class="btn-freightx" :disabled="!hasInput" :style="!hasInput ? 'opacity:0.5; cursor:not-allowed; pointer-events:none;' : ''" @click="downloadReport()">
                        <i class="fa fa-download"></i> Download
                    </button>
                    <button class="btn-default-gf" @click="resetForm()">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function shipmentReport() {
        return {
            partnerDropdown: false,
            partnerSearch: '',
            partners: [],
            filteredPartners: [],
            form: {
                report_type: 'shipment',
                trade_partner_id: '',
                date_type: 'eta',
                date_from: '',
                date_to: '',
                ship_type: '',
            },

            get hasInput() {
                return !!(this.form.trade_partner_id || this.form.date_from || this.form.date_to || this.form.ship_type);
            },

            init() {
                this.partners = @json($partners);
                this.filteredPartners = this.partners.slice(0, 20);
            },

            searchPartners() {
                if (!this.partnerSearch) {
                    this.filteredPartners = this.partners.slice(0, 20);
                    return;
                }
                const s = this.partnerSearch.toLowerCase();
                this.filteredPartners = this.partners.filter(p =>
                    (p.name || '').toLowerCase().includes(s) ||
                    (p.type || '').toLowerCase().includes(s)
                ).slice(0, 20);
                this.partnerDropdown = true;
            },

            selectPartner(p) {
                this.form.trade_partner_id = p.id;
                this.partnerSearch = p.name;
                this.partnerDropdown = false;
            },

            clearPartner() {
                this.form.trade_partner_id = '';
                this.partnerSearch = '';
                this.filteredPartners = this.partners.slice(0, 20);
            },

            async downloadReport() {
                if (!this.hasInput) return;
                
                // Show loading toast
                this.showToast('Generating report...', 'info');
                
                try {
                    const params = new URLSearchParams();
                    params.append('ship_type', this.form.ship_type);
                    params.append('date_field', this.form.date_type);
                    params.append('date_from', this.form.date_from);
                    params.append('date_to', this.form.date_to);
                    params.append('trade_partner_id', this.form.trade_partner_id);
                    params.append('report_type', this.form.report_type);
                    
                    const response = await fetch('{{ route("report.shipment.download") }}?' + params.toString(), {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'text/csv'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Download failed');
                    }
                    
                    // Get the blob from response
                    const blob = await response.blob();
                    
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'shipment-report-' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    
                    // Cleanup
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    this.showToast('Report downloaded successfully!', 'success');
                    
                } catch (error) {
                    console.error('Download error:', error);
                    this.showToast('Failed to download report. Please try again.', 'error');
                }
            },

            showToast(message, type = 'info') {
                // Remove existing toasts
                const existingToast = document.querySelector('.toast-notification');
                if (existingToast) existingToast.remove();

                // Create toast element
                const toast = document.createElement('div');
                toast.className = 'toast-notification toast-' + type;
                toast.innerHTML = `
                    <i class="fa fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                `;
                document.body.appendChild(toast);

                // Show toast with animation
                setTimeout(() => toast.classList.add('show'), 10);

                // Auto hide after 3 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            },

            resetForm() {
                this.form.report_type = 'shipment';
                this.form.trade_partner_id = '';
                this.form.date_type = 'eta';
                this.form.date_from = '';
                this.form.date_to = '';
                this.form.ship_type = '';
                this.partnerSearch = '';
                this.filteredPartners = this.partners.slice(0, 20);
            },
        };
    }
    </script>
    @endpush
</x-layout>
