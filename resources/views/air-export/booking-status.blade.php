<x-layout>
<div class="page-content" style="background: #eef1f5; min-height: 100vh; padding: 15px;">
    <script>
        function bookingStatusModule() {
            return {
                bookingId: {{ isset($booking) ? $booking->id : 'null' }},
                op_id: '{{ isset($booking) ? $booking->op_id : '' }}',
                sales_person_id: '{{ isset($booking) ? $booking->sales_person_id : '' }}',
                internalMessage: '',
                ToolsOpen: false,
                statusLogs: [],
                userInitials: {},
                getUserInitials(userId) {
                    return this.userInitials[userId] || '?';
                },
                saveStatus() {
                    let data = { internal_message: this.internalMessage, status_code: 'STATUS_UPDATE', status_name: 'Status Updated', details: this.internalMessage };
                    if (this.op_id) data.op_id = this.op_id;
                    if (this.sales_person_id) data.sales_person_id = this.sales_person_id;

                    fetch('/air-export/booking/' + this.bookingId + '/status', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify(data)
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            this.loadHistory();
                            this.internalMessage = '';
                            alert('Status updated successfully.');
                        }
                    }).catch(() => alert('Failed to save status.'));
                },
                loadHistory() {
                    if (!this.bookingId) return;
                    fetch('/air-export/booking/' + this.bookingId + '/history')
                        .then(r => r.json()).then(data => { this.statusLogs = data; })
                        .catch(() => {});
                },
                blockShipment() {
                    if (!confirm('Block this booking?')) return;
                    fetch('/air-export/booking/' + this.bookingId + '/status', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({ status_code: 'BLOCKED', status_name: 'Booking Blocked', details: 'Booking blocked by operator.' })
                    }).then(r => r.json()).then(res => {
                        if (res.success) { this.loadHistory(); alert('Booking blocked.'); }
                    }).catch(() => alert('Failed to block booking.'));
                },
                deleteBooking() {
                    if (!confirm('Delete this booking? This action cannot be undone.')) return;
                    fetch('/air-export/booking/' + this.bookingId, {
                        method: 'DELETE',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                    }).then(r => r.json()).then(res => {
                        if (res.success || res.message) window.location.href = '/air-export/booking/list';
                    }).catch(() => { window.location.href = '/air-export/booking/list'; });
                },
                init() {
@php
    $initialsMap = [];
    foreach ($users ?? [] as $u) {
        $initialsMap[$u->id] = strtoupper(substr($u->name, 0, 1));
    }
@endphp
                    this.userInitials = @json($initialsMap);
                    if (this.bookingId) {
                        this.loadHistory();
                    }
                }
            }
        }
    </script>
    <x-form-styles />
    <style>
        [x-cloak] { display: none !important; }
        .booking-container { max-width: 100%; margin: 0; }



        .form-section-title { grid-column: 1 / -1; display: flex; align-items: center; gap: 15px; margin: 15px 0 5px; color: #3b82f6; font-size: 11px; font-weight: 600; }
        .form-section-title::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        

        .role-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; padding: 15px; }
        .role-section { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; }
        .role-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; min-height: 20px; }
        .role-row span { font-size: 10px; font-weight: 600; color: #475569; width: 50px; flex-shrink: 0; text-align: right; margin-right: 6px; }
        .name-circle { width: 18px; height: 18px; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; flex-shrink: 0; }

        .timeline-container { position: relative; padding-left: 35px; padding-bottom: 15px; }
        .timeline-container::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
        .timeline-item { position: relative; margin-bottom: 15px; }
        .timeline-dot { position: absolute; left: -27px; width: 12px; height: 12px; background: white; border: 3px solid #3b82f6; border-radius: 50%; z-index: 2; top: 2px; }
        .timeline-content-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 10px; position: relative; }
        .timeline-time { font-size: 9px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: flex; gap: 10px; }
        .timeline-title { font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
        .timeline-user { font-size: 10px; color: #64748b; font-weight: 600; }

        .footer-save { padding: 12px 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right; display: flex; justify-content: flex-end; }
    </style>

    <div class="booking-container" x-data="bookingStatusModule()">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Air Export <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Booking Status</span></li>
            </ul>        </div>

        <!-- Navigation Tabs -->
        <ul class="gf-tabs">
            <li><a href="{{ isset($booking) ? route('air-bookings.edit', $booking->id) : '/air-export/booking/entry' }}">BASIC INFO</a></li>
            <li><a href="{{ isset($booking) ? route('air-bookings.accounting', $booking->id) : '/air-export/booking/accounting' }}">ACCOUNTING</a></li>
            <li class="active"><a href="{{ isset($booking) ? route('air-bookings.status', $booking->id) : '/air-export/booking/status' }}">STATUS / TRACKING</a></li>
        </ul>

        <!-- Main Board -->
        <div class="portlet light">
            <div class="portlet-title" style="background: #f9fafb;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="caption-subject"><i class="fa fa-history mr-2"></i> Booking Status{{ isset($booking) ? ' - ' . $booking->booking_no : '' }}</span>
                    <span style="font-size: 10px; color: #64748b; font-weight: 600; margin-left: 12px;"><i class="fa fa-user-circle"></i> OPERATOR: {{ strtoupper($currentUser->name ?? auth()->user()->name ?? 'N/A') }}</span>
                </div>
                <div class="actions" style="display: flex; gap: 5px; position: relative;">
                    <button class="btn-default-gf" style="height: 22px; padding: 0 8px; font-size: 10px;" @click="ToolsOpen = !ToolsOpen"><i class="fa fa-cogs"></i> Docs <i class="fa fa-angle-down"></i></button>
                    <div x-show="ToolsOpen" @click.away="ToolsOpen = false" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #ddd; z-index: 100; min-width: 180px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                        <a href="#" @click.prevent="blockShipment()" class="dropdown-item"><i class="fa fa-lock mr-2"></i> BLOCK</a>
                        <a href="#" @click.prevent="alert('Copied.')" class="dropdown-item"><i class="fa fa-copy mr-2"></i> COPY</a>
                        <a href="#" @click.prevent="deleteBooking()" class="dropdown-item"><i class="fa fa-trash mr-2"></i> DELETE</a>
                        <div style="height: 1px; background: #eee; margin: 4px 0;"></div>
                        <a href="/air-export/create" class="dropdown-item" target="_blank"><i class="fa fa-plus mr-2"></i> CREATE NEW MAWB & HAWB</a>
                        <a href="/air-export/list" class="dropdown-item" target="_blank"><i class="fa fa-eye mr-2"></i> VIEW MAWB & HAWB</a>
                        <div style="height: 1px; background: #eee; margin: 4px 0;"></div>
                        <a href="#" @click.prevent="alert('Generating booking confirmation...')" class="dropdown-item"><i class="fa fa-file-pdf mr-2"></i> BOOKING CONFIRMATION</a>
                        <a href="#" @click.prevent="alert('Generating pickup/delivery order...')" class="dropdown-item"><i class="fa fa-truck mr-2"></i> PICKUP / DELIVERY ORDER</a>
                    </div>
                </div>
            </div>
            <div class="portlet-body" style="padding: 15px;">

            <div class="role-grid">
                <div class="role-section">
                    <h4 class="form-section-title" style="margin:0 0 10px 0;">Operational Roles</h4>
                    <div class="role-row">
                        <span>OP :</span>
                        <div class="name-circle" x-text="getUserInitials(op_id)"></div>
                        <div class="form-input-container" style="flex:1;">
                            <select class="form-control-gf" name="op_id" x-model="op_id">
                                <option value="">Select Operator...</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="role-row">
                        <span>Sales :</span>
                        <div class="name-circle" x-text="getUserInitials(sales_person_id)" style="background: #e2e8f0; color: #999;"></div>
                        <div class="form-input-container" style="flex:1;">
                            <select class="form-control-gf" name="sales_person_id" x-model="sales_person_id">
                                <option value="">Select Sales...</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="role-section">
                    <h4 class="form-section-title" style="margin:0 0 10px 0;">Internal Operations Message</h4>
                    <textarea class="form-control-gf" x-model="internalMessage" placeholder="Enter internal instructions or operational notes..." style="height:65px;"></textarea>
                </div>
            </div>

            <div style="padding: 0 15px 15px;">
                <h4 class="form-section-title">Shipment Change Log / History</h4>
                <div class="timeline-container">
                    <template x-for="(log, idx) in statusLogs" :key="idx">
                        <div class="timeline-item">
                            <div class="timeline-dot" :style="idx === 0 ? '' : 'border-color: #cbd5e1;'"></div>
                            <div class="timeline-content-box" :style="idx > 0 ? 'opacity: 0.8; border-style: dashed;' : ''">
                                <div class="timeline-time">
                                    <span><i class="fa fa-calendar-alt"></i> <span x-text="log.event_time"></span></span>
                                    <span><i class="fa fa-clock"></i> <span x-text="log.time"></span></span>
                                </div>
                                <div class="timeline-title" x-text="log.title"></div>
                                <div class="timeline-user">Operator: <span style="color: var(--primary-blue);" x-text="log.user"></span></div>
                                <div x-show="log.detail" x-text="log.detail" style="font-size:10px;color:#666;margin-top:2px;"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Floating Save Button -->
        <div class="footer-save">
            <button type="button" class="btn-freightx" @click="saveStatus()">
                <i class="fa fa-save mr-2"></i> SAVE STATUS UPDATES
            </button>
        </div>
        </div>
    </div>
</div>
</x-layout>
