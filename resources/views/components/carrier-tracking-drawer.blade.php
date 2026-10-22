<!-- Live Dynamic Carrier Tracking Drawer / Modal -->
<div x-data="carrierTrackingDrawer()" 
     x-init="initDrawer()" 
     @open-carrier-tracking.window="openTracking($event.detail)"
     x-cloak>

    <!-- Overlay Backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px); z-index: 99999;"
         @click="close()">
    </div>

    <!-- Sliding Drawer Panel -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         style="position: fixed; top: 0; right: 0; bottom: 0; width: 680px; max-width: 95vw; background: #ffffff; z-index: 100000; box-shadow: -10px 0 35px -5px rgba(0,0,0,0.3); display: flex; flex-direction: column; font-family: 'Inter', sans-serif;">

        <!-- Drawer Header -->
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fa fa-ship"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <span>Track & Trace Center</span>
                        <span style="font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600;" 
                              :style="'background:' + statusBadge.bg + '; color:' + statusBadge.color + ';'" 
                              x-text="statusBadge.label || 'TRACKING'">
                        </span>
                    </h3>
                    <div style="font-size: 12px; color: #64748b; font-family: monospace; display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                        <span x-text="trackingNumber || 'No Number'"></span>
                        <button type="button" @click="copyNumber()" title="Copy tracking number" style="background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0 4px;">
                            <i class="fa fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" @click="close()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; line-height: 1; padding: 4px;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#94a3b8'">&times;</button>
        </div>

        <!-- Quick Selector / Sync Action Bar -->
        <div style="padding: 12px 20px; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
            
            <div style="display: flex; gap: 8px; align-items: center; flex: 1; min-width: 260px;">
                <!-- Carrier dropdown -->
                <select x-model="selectedCarrier" style="height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px; background: #fff; color: #334155; font-weight: 600;">
                    <option value="">Auto Detect Carrier</option>
                    <option value="hapag_lloyd">Hapag-Lloyd</option>
                    <option value="maersk">Maersk Line</option>
                    <option value="dcsa">Generic DCSA</option>
                </select>

                <!-- Input or Active Containers List -->
                <template x-if="availableContainers.length > 1">
                    <select x-model="trackingNumber" @change="syncLiveTracking()" style="height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px; background: #fff; font-family: monospace; font-weight: 600; color: #1e293b;">
                        <template x-for="c in availableContainers" :key="c">
                            <option :value="c" x-text="c"></option>
                        </template>
                    </select>
                </template>

                <template x-if="availableContainers.length <= 1">
                    <input type="text" 
                           x-model="trackingNumber" 
                           placeholder="Container No / Master B/L" 
                           @keyup.enter="syncLiveTracking()" 
                           style="height: 32px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; font-family: monospace; flex: 1; min-width: 140px;">
                </template>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="button" 
                        @click="syncLiveTracking()" 
                        :disabled="isSyncing || !trackingNumber"
                        style="height: 32px; padding: 0 14px; background: #3b82f6; border: none; color: #fff; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s;">
                    <i class="fa" :class="isSyncing ? 'fa-spinner fa-spin' : 'fa-refresh'"></i>
                    <span x-text="isSyncing ? 'Syncing...' : 'Sync Live Tracking'"></span>
                </button>

                <button type="button" 
                        @click="showManualModal = true"
                        title="Add manual milestone or note"
                        style="height: 32px; padding: 0 10px; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">
                    <i class="fa fa-plus"></i> Add Note
                </button>
            </div>
        </div>

        <!-- Scrollable Body -->
        <div style="flex: 1; overflow-y: auto; padding: 20px;" class="custom-scrollbar">

            <!-- Route & Vessel Card -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Origin (POL)</span>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;" x-text="trackingData.origin_name || 'Origin Terminal'"></div>
                        <span style="font-size: 11px; color: #64748b;" x-text="trackingData.etd ? 'ETD: ' + formatDate(trackingData.etd) : ''"></span>
                    </div>

                    <div style="text-align: center; padding: 0 16px;">
                        <i class="fa fa-arrow-right" style="color: #94a3b8; font-size: 16px;"></i>
                        <div style="font-size: 11px; font-weight: 600; color: #3b82f6; margin-top: 2px;" x-text="trackingData.carrier_code ? trackingData.carrier_code.toUpperCase() : 'CARRIER'"></div>
                    </div>

                    <div style="text-align: right;">
                        <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Destination (POD)</span>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;" x-text="trackingData.destination_name || 'Port of Discharge'"></div>
                        <span style="font-size: 11px; color: #059669; font-weight: 600;" x-text="trackingData.eta ? 'ETA: ' + formatDate(trackingData.eta) : ''"></span>
                    </div>
                </div>

                <div style="border-top: 1px dashed #cbd5e1; padding-top: 10px; display: flex; justify-content: space-between; font-size: 11px; color: #475569;">
                    <div>
                        <i class="fa fa-ship" style="color: #64748b;"></i> 
                        <strong x-text="trackingData.vessel_name || 'Vessel TBA'"></strong>
                        <span x-text="trackingData.voyage_no ? ' / Voyage ' + trackingData.voyage_no : ''"></span>
                    </div>
                    <div>
                        <span style="color: #64748b;">Last Updated:</span>
                        <span style="font-weight: 600;" x-text="trackingData.last_polled_at ? formatTime(trackingData.last_polled_at) : 'Just now'"></span>
                    </div>
                </div>
            </div>

            <!-- 5-Stage Visual Stepper -->
            <div style="margin-bottom: 24px;">
                <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 12px;">Milestone Progress</label>
                
                <div style="position: relative; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <!-- Track Line Behind -->
                    <div style="position: absolute; left: 16px; right: 16px; top: 14px; height: 3px; background: #e2e8f0; z-index: 1;"></div>
                    <!-- Active Fill Line -->
                    <div style="position: absolute; left: 16px; top: 14px; height: 3px; background: #3b82f6; z-index: 2; transition: width 0.4s ease;" 
                         :style="'width: calc(' + (currentStepProgress()) + '% - 32px)'">
                    </div>

                    <!-- Steps -->
                    <template x-for="(st, idx) in steps" :key="idx">
                        <div style="position: relative; z-index: 3; display: flex; flex-direction: column; align-items: center; width: 68px; text-align: center;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; transition: all 0.2s;"
                                 :style="idx <= currentStepIndex() ? 'background:#3b82f6; color:#fff; box-shadow: 0 0 0 3px #bfdbfe;' : 'background:#fff; border:2px solid #cbd5e1; color:#94a3b8;'">
                                <i class="fa" :class="idx < currentStepIndex() ? 'fa-check' : (idx === currentStepIndex() ? 'fa-circle' : st.icon)"></i>
                            </div>
                            <span style="font-size: 10px; margin-top: 6px; font-weight: 600; line-height: 1.2;" 
                                  :style="idx <= currentStepIndex() ? 'color:#1e293b;' : 'color:#94a3b8;'" 
                                  x-text="st.label">
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- DCSA Chronological Timeline -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Verified Event Timeline</label>
                    <span style="font-size: 11px; color: #64748b;" x-text="events.length + ' milestones recorded'"></span>
                </div>

                <div style="position: relative; padding-left: 20px;">
                    <!-- Vertical Timeline Line -->
                    <div style="position: absolute; left: 6px; top: 8px; bottom: 8px; width: 2px; background: #e2e8f0;"></div>

                    <template x-for="(evt, idx) in events" :key="idx">
                        <div style="position: relative; margin-bottom: 16px;">
                            <!-- Node Dot -->
                            <div style="position: absolute; left: -20px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #ffffff; border: 2px solid;"
                                 :style="'border-color:' + (evt.event_classifier === 'ACT' ? '#10b981' : '#3b82f6') + '; box-shadow: 0 0 0 2px rgba(59,130,246,0.15);'">
                            </div>

                            <!-- Event Card -->
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <strong style="font-size: 12px; color: #1e293b;" x-text="evt.title"></strong>
                                        <span style="font-size: 9px; padding: 1px 6px; border-radius: 3px; font-weight: 700;"
                                              :style="evt.event_classifier === 'ACT' ? 'background:#dcfce7; color:#166534;' : 'background:#fef9c3; color:#854d0e;'"
                                              x-text="evt.event_classifier === 'ACT' ? 'ACTUAL' : 'ESTIMATED'">
                                        </span>
                                        <template x-if="evt.is_manual">
                                            <span style="font-size: 9px; padding: 1px 6px; border-radius: 3px; font-weight: 700; background: #e0e7ff; color: #4338ca;">OPERATOR</span>
                                        </template>
                                    </div>
                                    <span style="font-size: 11px; color: #64748b; font-family: monospace;" x-text="formatDateTime(evt.event_date_time)"></span>
                                </div>

                                <div style="font-size: 11px; color: #475569; margin-bottom: 6px;" x-text="evt.description || 'Status update logged.'"></div>

                                <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 10px; color: #64748b; border-top: 1px solid #f8fafc; padding-top: 6px;">
                                    <template x-if="evt.location_name">
                                        <span><i class="fa fa-map-marker" style="color: #ef4444;"></i> <span x-text="evt.location_name + (evt.location_locode ? ' (' + evt.location_locode + ')' : '')"></span></span>
                                    </template>
                                    <template x-if="evt.facility_name">
                                        <span><i class="fa fa-building-o"></i> <span x-text="evt.facility_name"></span></span>
                                    </template>
                                    <template x-if="evt.vessel_name">
                                        <span><i class="fa fa-ship"></i> <span x-text="evt.vessel_name + (evt.voyage_no ? ' / ' + evt.voyage_no : '')"></span></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="events.length === 0">
                        <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px; background: #f8fafc; border-radius: 6px; border: 1px dashed #cbd5e1;">
                            No milestone events found yet. Click <strong>Sync Live Tracking</strong> to query the carrier API.
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Manual Event Modal -->
        <div x-show="showManualModal" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100001; display: flex; align-items: center; justify-content: center;" @click.self="showManualModal = false">
            <div style="background: #fff; width: 440px; max-width: 90vw; border-radius: 8px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
                <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #1e293b;">Add Manual Tracking Event</h4>
                    <button type="button" @click="showManualModal = false" style="background:none; border:none; font-size:18px; color:#94a3b8; cursor:pointer;">&times;</button>
                </div>
                <div style="padding: 18px; font-size: 12px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Milestone Title</label>
                        <input type="text" x-model="manualForm.title" placeholder="e.g. Customs Clearance Approved" style="width: 100%; height: 32px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Description / Operator Note</label>
                        <textarea x-model="manualForm.description" rows="2" placeholder="Enter additional details..." style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Date & Time</label>
                            <input type="datetime-local" x-model="manualForm.event_date_time" style="width: 100%; height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Status Type</label>
                            <select x-model="manualForm.event_classifier" style="width: 100%; height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px;">
                                <option value="ACT">Actual (Completed)</option>
                                <option value="EST">Estimated (Planned)</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-weight: 600; color: #475569; margin-bottom: 4px;">Location / Terminal</label>
                        <input type="text" x-model="manualForm.location_name" placeholder="e.g. Los Angeles Pier 400" style="width: 100%; height: 32px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;">
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" @click="showManualModal = false" style="padding: 6px 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; cursor: pointer;">Cancel</button>
                        <button type="button" @click="submitManualEvent()" :disabled="!manualForm.title" style="padding: 6px 16px; background: #3b82f6; border: none; color: #fff; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;">Save Milestone</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function carrierTrackingDrawer() {
    return {
        isOpen: false,
        isSyncing: false,
        trackingNumber: '',
        selectedCarrier: '',
        shipmentId: null,
        shipmentType: null,
        availableContainers: [],
        trackingData: {},
        statusBadge: {},
        events: [],
        showManualModal: false,
        manualForm: {
            title: '',
            description: '',
            event_date_time: new Date().toISOString().slice(0, 16),
            event_classifier: 'ACT',
            location_name: ''
        },

        steps: [
            { label: 'Booking', icon: 'fa-file-text-o' },
            { label: 'Gate In', icon: 'fa-truck' },
            { label: 'Loaded', icon: 'fa-ship' },
            { label: 'In Transit', icon: 'fa-compass' },
            { label: 'Discharged', icon: 'fa-anchor' },
            { label: 'Delivered', icon: 'fa-check-circle' }
        ],

        initDrawer() {
            window.carrierTrackingDrawerInstance = this;
            window.openLiveTracking = (opts) => this.openTracking(opts);
        },

        openTracking(opts = {}) {
            this.trackingNumber = (opts.number || opts.trackingNumber || '').trim();
            this.selectedCarrier = opts.carrier || '';
            this.shipmentId = opts.shipmentId || null;
            this.shipmentType = opts.shipmentType || null;
            this.availableContainers = opts.containers || (this.trackingNumber ? [this.trackingNumber] : []);
            
            this.isOpen = true;

            if (this.trackingNumber || this.shipmentId) {
                this.loadDetails();
            }
        },

        close() {
            this.isOpen = false;
        },

        currentStepIndex() {
            const status = (this.trackingData.current_status || '').toLowerCase();
            return matchStep(status);

            function matchStep(s) {
                if (s === 'delivered') return 5;
                if (s === 'gate_out' || s === 'discharged') return 4;
                if (s === 'in_transit') return 3;
                if (s === 'loaded') return 2;
                if (s === 'gate_in') return 1;
                return 0;
            }
        },

        currentStepProgress() {
            const idx = this.currentStepIndex();
            return (idx / (this.steps.length - 1)) * 100;
        },

        loadDetails() {
            this.isSyncing = true;
            fetch('{{ route("api.tracking.details") }}?tracking_number=' + encodeURIComponent(this.trackingNumber) + '&shipment_id=' + (this.shipmentId || ''))
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.tracking) {
                        this.trackingData = res.tracking;
                        this.statusBadge = res.status_badge || {};
                        this.events = res.events || [];
                    } else {
                        // If not found in DB, auto-trigger live carrier sync
                        this.syncLiveTracking();
                    }
                })
                .catch(() => {
                    this.syncLiveTracking();
                })
                .finally(() => {
                    this.isSyncing = false;
                });
        },

        syncLiveTracking() {
            if (!this.trackingNumber) return;
            this.isSyncing = true;

            if (typeof showToast === 'function') {
                showToast('info', 'Connecting to carrier API for ' + this.trackingNumber + '...');
            }

            fetch('{{ route("api.tracking.sync") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tracking_number: this.trackingNumber,
                    carrier_code: this.selectedCarrier,
                    shipment_id: this.shipmentId,
                    shipment_type: this.shipmentType
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.tracking) {
                    this.trackingData = res.tracking;
                    this.statusBadge = res.status_badge || {};
                    this.events = res.events || [];
                    if (typeof showToast === 'function') {
                        showToast('success', res.message || 'Tracking synchronized successfully.');
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast('error', res.message || 'Tracking sync failed.');
                    }
                }
            })
            .catch(err => {
                if (typeof showToast === 'function') {
                    showToast('error', 'Error syncing tracking.');
                }
            })
            .finally(() => {
                this.isSyncing = false;
            });
        },

        submitManualEvent() {
            if (!this.trackingData.id) {
                alert('Please sync tracking first.');
                return;
            }

            fetch('{{ route("api.tracking.manual-event") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    shipment_tracking_id: this.trackingData.id,
                    ...this.manualForm
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    this.events = res.events || [];
                    this.showManualModal = false;
                    this.manualForm.title = '';
                    this.manualForm.description = '';
                    if (typeof showToast === 'function') {
                        showToast('success', 'Milestone note logged.');
                    }
                }
            });
        },

        copyNumber() {
            if (this.trackingNumber) {
                navigator.clipboard.writeText(this.trackingNumber);
                if (typeof showToast === 'function') showToast('success', 'Copied to clipboard');
            }
        },

        formatDate(val) {
            if (!val) return '';
            try { return new Date(val).toLocaleDateString(); } catch(e) { return val; }
        },

        formatDateTime(val) {
            if (!val) return '';
            try { 
                const d = new Date(val);
                return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch(e) { return val; }
        },

        formatTime(val) {
            if (!val) return '';
            try { return new Date(val).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); } catch(e) { return val; }
        }
    };
}
</script>

