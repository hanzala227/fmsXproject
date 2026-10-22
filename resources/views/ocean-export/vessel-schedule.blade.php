<x-layout title="Vessel Schedule">
    @push('styles')
    <x-form-styles />
    <style>
        .right-sidebar { position: fixed; right: 20px; top: 110px; width: 170px; z-index: 1000; }
        .sidebar-card { background: #fff; border: 1px solid #e2e8f0; padding: 8px; margin-bottom: 8px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.2s ease; }
        .sidebar-card:hover { border-color: #3b82f6; transform: translateY(-1px); }
        .sidebar-btn { background: #f8fafc; border: 1px solid #cbd5e1; width: 100%; padding: 6px 10px; text-align: center; font-weight: 700; margin-bottom: 6px; cursor: pointer; font-size: 11px; border-radius: 3px; color: #334155; transition: background 0.15s; }
        .sidebar-btn:hover { background: #e2e8f0; color: #1e293b; }
        .booking-info-card { background: #f59e0b; color: #fff; padding: 10px; border-radius: 4px; position: relative; overflow: hidden; min-height: 100px; cursor: pointer; transition: transform 0.15s ease; }
        .booking-info-card:hover { transform: scale(1.02); }
        .booking-info-card.active { ring: 2px solid #3b82f6; box-shadow: 0 0 8px rgba(59, 130, 246, 0.5); }
        .booking-info-card i.anchor-bg { position: absolute; right: -10px; bottom: -10px; font-size: 60px; opacity: 0.2; transform: rotate(-15deg); }

        .shipment-status-badge { background: #3b82f6; color: #fff; padding: 2px 6px; border-radius: 10px; font-size: 9px; font-weight: 700; margin-right: 5px; text-transform: uppercase; }
        .well-gf { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; margin-bottom: 8px; border-radius: 3px; }
        .table-accounting { width: 100%; border-collapse: collapse; font-size: 11px; background: #fff; border: 1px solid #e2e8f0; }
        .table-accounting th { background: #f8fafc; color: #475569; font-weight: 700; text-align: center; border: 1px solid #e2e8f0; padding: 5px 8px; }
        .table-accounting td { border: 1px solid #e2e8f0; padding: 5px 8px; vertical-align: middle; }
        .btn-accounting { background: #3b82f6; color: #fff; border: 1px solid #2563eb; padding: 4px 12px; font-size: 11px; cursor: pointer; border-radius: 3px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; font-weight: 600; }
        .btn-accounting:hover { background: #2563eb; }
        .accounting-toolbar { display: flex; align-items: center; justify-content: space-between; padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }

        .pdo-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 20px 0; }
        .pdo-modal { background: #fff; width: 920px; max-width: 95%; border: 1px solid #cbd5e1; box-shadow: 0 5px 20px rgba(0,0,0,0.4); border-radius: 4px; overflow: hidden; }
        .pdo-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 30px; }
        .pdo-title { font-size: 22px; font-weight: bold; color: #1e293b; margin: 0; }
        .pdo-address { font-size: 11px; line-height: 1.4; color: #334155; margin-top: 8px; }
        .pdo-top-table { border: 2px solid #475569; width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .pdo-top-table td { border: 1px solid #475569; padding: 5px; font-size: 10px; font-weight: bold; }
        .pdo-body { padding: 0 30px 30px 30px; }
        .pdo-layout { display: flex; gap: 12px; }
        .pdo-left { flex: 4; display: flex; flex-direction: column; gap: 6px; }
        .pdo-right { flex: 6; display: flex; flex-direction: column; gap: 6px; }
        .pdo-block { border: 1px solid #3b82f6; border-radius: 2px; padding: 6px; position: relative; }
        .pdo-block-title { font-size: 10px; font-weight: bold; font-style: italic; color: #334155; display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; text-transform: uppercase; }
        .pdo-input { width: 100%; border: 1px solid #cbd5e1; font-size: 11px; padding: 2px 5px; margin-bottom: 4px; background: #fff; height: 22px; box-sizing: border-box; border-radius: 2px; }
        .pdo-textarea { width: 100%; border: 1px solid #cbd5e1; font-size: 11px; padding: 5px; resize: vertical; min-height: 70px; box-sizing: border-box; border-radius: 2px; }
        .pdo-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .pdo-table td { border: 1px solid #3b82f6; padding: 4px 6px; }
        .pdo-table .td-label { font-weight: bold; color: #334155; text-transform: uppercase; font-size: 9px; }
        .pdo-toolbar { background: #f8fafc; padding: 10px 15px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10001; }
        
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 8px; }
        .toast-msg { background: #1e293b; color: #fff; padding: 10px 16px; border-radius: 4px; font-size: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 8px; animation: fadeIn 0.3s ease; }
        .toast-msg.success { background: #10b981; }
        .toast-msg.error { background: #ef4444; }
        .toast-msg.info { background: #3b82f6; }
        .toast-msg.warning { background: #f59e0b; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        
        .po-tag { display: inline-flex; align-items: center; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-right: 4px; margin-bottom: 4px; font-weight: 500; }
        .po-tag button { background: none; border: none; color: #0369a1; margin-left: 4px; cursor: pointer; font-size: 12px; line-height: 1; padding: 0; }
        .po-tag button:hover { color: #ef4444; }
    </style>
    @endpush

    <div class="page-content" x-data="vesselScheduleModule()">
        <!-- Toast Container -->
        <div class="toast-container">
            <template x-for="t in toasts" :key="t.id">
                <div :class="'toast-msg ' + t.type">
                    <i :class="t.type === 'success' ? 'fa fa-check-circle' : (t.type === 'error' ? 'fa fa-exclamation-circle' : 'fa fa-info-circle')"></i>
                    <span x-text="t.text"></span>
                </div>
            </template>
        </div>

        <!-- Right Floating Sidebar -->
        <div class="right-sidebar">
            <div style="display:flex; flex-direction:column; gap:5px; margin-bottom: 10px;">
                <button class="sidebar-btn" @click="addBooking" style="background: #3b82f6; color: #fff; border-color: #2563eb;">+ Add Booking</button>
                <div style="font-size: 10px; color: #64748b; display:flex; justify-content:space-between; align-items:center;">
                   <span>Sort: <select style="font-size: 9px; border:1px solid #cbd5e1; background:#fff; padding: 1px 3px; border-radius: 2px;"><option>Create Date</option></select></span>
                   <span style="color:#3b82f6; cursor:pointer; font-weight: 700;">ASC <i class="fa fa-long-arrow-up"></i></span>
                </div>
            </div>
            
            <template x-for="(hbl, index) in bookings" :key="index">
                <div class="booking-info-card" :class="activeBookingIdx === index ? 'active' : ''" @click="activeBookingIdx = index" style="margin-bottom: 8px; padding: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); min-height: 90px;">
                    <div style="font-weight: 700; font-size: 12px; display:flex; justify-content:space-between; align-items:center;">
                        <span><i class="fa fa-info-circle"></i> <span x-text="hbl.booking_no || 'New Booking'"></span></span>
                        <i class="fa fa-times" style="cursor:pointer; font-size: 11px; opacity:0.8;" @click.stop="deleteBooking(index)" title="Remove booking"></i>
                    </div>
                    <div style="font-size: 10px; margin-top: 6px; opacity: 0.9;">
                        <div>Carrier: <span x-text="hbl.carrier_bkg_no || '--'"></span></div>
                        <div>ETA: <span x-text="hbl.eta || '--'"></span></div>
                    </div>
                    <i class="fa fa-anchor anchor-bg"></i>
                </div>
            </template>
        </div>

        <div style="margin-right: 190px;">
            <!-- Top Actions Toolbar -->
            <div style="background: #fff; border: 1px solid #e2e8f0; padding: 10px 15px; border-radius: 4px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn-tool" style="background:#10b981; border-color:#059669; color:#fff; padding: 6px 18px; font-weight:600;" @click="submitMainForm"><i class="fa fa-save"></i> Save Schedule</button>
                    <a href="{{ route('vessel-schedules.index') }}" class="btn-default-gf" style="padding: 6px 15px; font-size: 11px; font-weight:600;"><i class="fa fa-arrow-left"></i> Back to List</a>
                    <button type="button" class="btn-default-gf" @click="exportCsv" x-show="saved"><i class="fa fa-file-excel-o"></i> Excel</button>
                    <button type="button" class="btn-default-gf" @click="printSchedule" x-show="saved"><i class="fa fa-print"></i> Print</button>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 600; color: #475569;">Schedule Status:</span>
                    <span class="shipment-status-badge" style="background:#32c5d2; font-size:11px; padding: 3px 8px;" x-text="saved ? 'OPEN' : 'NEW DRAFT'"></span>
                </div>
            </div>

            <!-- Breadcrumbs -->
            <div style="font-size: 11px; color: #64748b; margin-bottom: 10px; display:flex; align-items:center; gap: 5px;">
                <i class="fa fa-home"></i> <span>Home</span> <i class="fa fa-angle-right"></i> <span>Ocean Export</span> <i class="fa fa-angle-right"></i> 
                <span style="color: #1e293b; font-weight: 700;">{{ isset($schedule) ? 'Edit Vessel Schedule: ' . $schedule->schedule_no : 'New Vessel Schedule' }}</span>
            </div>

            <!-- Main Tabs -->
            <ul class="gf-tabs">
                <li :class="activeTab === 'basic' ? 'active' : ''" @click="activeTab = 'basic'"><a>Basic</a></li>
                <li :class="(activeTab === 'accounting' ? 'active' : '') + (saved ? '' : ' disabled-tab')" @click="saved ? activeTab = 'accounting' : showToast('warning', 'Save schedule first to access Accounting')"><a>Accounting</a></li>
                <li style="display: none !important;" :class="(activeTab === 'document' ? 'active' : '') + (saved ? '' : ' disabled-tab')" @click="saved ? activeTab = 'document' : showToast('warning', 'Save schedule first to access Doc Center')"><a>Doc Center</a></li>
                <li style="display: none !important;" :class="(activeTab === 'workorder' ? 'active' : '') + (saved ? '' : ' disabled-tab')" @click="saved ? activeTab = 'workorder' : showToast('warning', 'Save schedule first to access Work Order')"><a>Work Order</a></li>
                <li :class="(activeTab === 'status' ? 'active' : '') + (saved ? '' : ' disabled-tab')" @click="saved ? activeTab = 'status' : showToast('warning', 'Save schedule first to access Status')"><a>Status</a></li>
            </ul>

            <!-- ================= BASIC TAB ================= -->
            <div x-show="activeTab === 'basic'">
                <form action="{{ isset($schedule) ? route('vessel-schedules.update', $schedule->id) : route('vessel-schedules.store') }}" method="POST" id="vesselScheduleForm" x-ref="mainForm" x-on:submit.prevent="validateAndSubmit">
                    @csrf
                    @if(isset($schedule))
                        @method('PUT')
                    @endif
                    <input type="hidden" name="bookings_json" id="bookings-json" value="[]">
                    <input type="hidden" name="containers_json" id="containers-json" value="[]">
                    <input type="hidden" name="memos_json" id="memos-json" value="[]">
                    
                    @if(session('success'))
                        <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;padding:10px 15px;border-radius:4px;margin-bottom:12px;display:flex;align-items:center;gap:8px;"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:12px;display:flex;align-items:center;gap:8px;"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}</div>
                    @endif
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger" style="background:#fce4e4;border:1px solid #e57373;color:#c62828;padding:10px 15px;border-radius:4px;margin-bottom:12px;"><strong>Validation Error</strong><ul style="margin: 5px 0 0 15px; padding:0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                <!-- Master Vessel Schedule Portlet -->
                <div class="portlet light">
                    <div class="portlet-title" style="background: #2f353b; color: #fff; min-height: 28px; padding: 4px 12px; display: flex; justify-content: space-between; align-items: center; cursor: pointer;" @click="hideVessel = !hideVessel">
                        <span class="caption-subject" style="color: #fff; font-size: 12px; font-weight: 600; display:flex; align-items:center; gap:8px;">
                            <span class="shipment-status-badge" style="background:#32c5d2;">Open</span> Vessel Schedule Information
                        </span>
                        <div class="actions"><i class="fa fa-chevron-down" :class="hideVessel ? '' : 'rotate-180'" style="transition: transform 0.2s;"></i></div>
                    </div>
                    <div class="portlet-body" x-show="!hideVessel">
                        <div class="well-gf">
                            <div class="form-grid-4">
                                <div>
                                    <div class="form-group-gf">
                                        <label class="form-label-gf" style="color:#ef4444; font-weight:600;">* Vessel Sched No.</label>
                                        <div class="form-input-container">
                                            <input type="text" name="schedule_no" class="form-control-gf" value="{{ old('schedule_no', $schedule->schedule_no ?? '') }}" placeholder="AUTO">
                                        </div>
                                    </div>
                                    <div class="form-group-gf"><label class="form-label-gf">Carrier Bkg. No.</label><div class="form-input-container"><input type="text" name="carrier_bkg_no" class="form-control-gf" value="{{ old('carrier_bkg_no', $schedule->carrier_bkg_no ?? '') }}"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Shipping Agent</label><div class="form-input-container"><input type="text" name="shipping_agent" class="form-control-gf" value="{{ old('shipping_agent', $schedule->shipping_agent ?? '') }}"><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')" title="Add Trade Partner"><i class="fa fa-plus"></i></button></div></div>
                                </div>
                                <div>
                                    <div class="form-group-gf"><label class="form-label-gf" style="color:#ef4444; font-weight:600;">* Office</label><div class="form-input-container"><select name="office_id" class="form-control-gf"><option value="">Select Office</option>@foreach($offices as $office)<option value="{{ $office->id }}" {{ old('office_id', $schedule->office_id ?? '') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>@endforeach</select></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">ITN No.</label><div class="form-input-container"><input type="text" name="itn_no" class="form-control-gf" value="{{ old('itn_no', $schedule->itn_no ?? '') }}"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Oversea Agent</label><div class="form-input-container"><select name="oversea_agent_id" class="form-control-gf"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" {{ old('oversea_agent_id', $schedule->oversea_agent_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                </div>
                                <div>
                                    <div class="form-group-gf"><label class="form-label-gf">B/L Type</label><div class="form-input-container"><select name="bl_type" class="form-control-gf"><option value="">Select</option><option value="NORMAL" {{ old('bl_type', $schedule->bl_type ?? '') == 'NORMAL' ? 'selected' : '' }}>NORMAL</option><option value="SEAWAY BILL" {{ old('bl_type', $schedule->bl_type ?? '') == 'SEAWAY BILL' ? 'selected' : '' }}>SEAWAY BILL</option><option value="SURRENDERED" {{ old('bl_type', $schedule->bl_type ?? '') == 'SURRENDERED' ? 'selected' : '' }}>SURRENDERED</option></select></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Notify</label><div class="form-input-container"><select name="notify_id" class="form-control-gf"><option value="">Select...</option>@foreach($tradePartners as $partner)<option value="{{ $partner->id }}" {{ old('notify_id', $schedule->notify_id ?? '') == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf" style="justify-content: flex-end; font-size: 10px; font-weight: bold; color: #64748b; padding-top: 4px;">OP: {{ isset($schedule) && $schedule->op ? $schedule->op->name : (optional($loggedUser)->name ?? 'System') }} ({{ isset($schedule) && $schedule->op ? $schedule->op->email : (optional($loggedUser)->email ?? '') }})</div>
                                    <input type="hidden" name="op_id" value="{{ old('op_id', $schedule->op_id ?? optional($loggedUser)->id ?? '') }}">
                                </div>
                                <div>
                                    <div class="form-group-gf"><label class="form-label-gf">Post Date</label><div class="form-input-container"><input type="date" name="post_date" class="form-control-gf" value="{{ old('post_date', isset($schedule) && $schedule->post_date ? \Carbon\Carbon::parse($schedule->post_date)->format('Y-m-d') : date('Y-m-d')) }}"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Forwarding Agent</label><div class="form-input-container"><select name="forwarding_agent_id" class="form-control-gf"><option value="">Select...</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" {{ old('forwarding_agent_id', $schedule->forwarding_agent_id ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid-4" style="margin-top: 10px;">
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf" style="color:#ef4444; font-weight:600;">* Vessel</label><div class="form-input-container"><select name="vessel_id" class="form-control-gf"><option value="">Select</option>@foreach($vessels as $vessel)<option value="{{ $vessel->id }}" {{ old('vessel_id', $schedule->vessel_id ?? '') == $vessel->id ? 'selected' : '' }}>{{ $vessel->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('vessel')"><i class="fa fa-plus"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf" style="color:#ef4444; font-weight:600;">* Port of Loading</label><div class="form-input-container"><select name="pol_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('pol_id', $schedule->pol_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf" style="color:#ef4444; font-weight:600;">* Port Discharge</label><div class="form-input-container"><select name="pod_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('pod_id', $schedule->pod_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Final Destination</label><div class="form-input-container"><select name="fdest_id" class="form-control-gf"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('fdest_id', $schedule->fdest_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                            </div>
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Voyage</label><div class="form-input-container"><input type="text" name="voyage" class="form-control-gf" value="{{ old('voyage', $schedule->voyage ?? '') }}"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf" style="color:#ef4444; font-weight:600;">* ETD</label><div class="form-input-container"><input type="date" name="etd" class="form-control-gf" value="{{ old('etd', isset($schedule) && $schedule->etd ? \Carbon\Carbon::parse($schedule->etd)->format('Y-m-d') : '') }}"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">ETA</label><div class="form-input-container"><input type="date" name="eta" class="form-control-gf" value="{{ old('eta', isset($schedule) && $schedule->eta ? \Carbon\Carbon::parse($schedule->eta)->format('Y-m-d') : '') }}"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Final ETA</label><div class="form-input-container"><input type="date" name="final_eta" class="form-control-gf" value="{{ old('final_eta', isset($schedule) && $schedule->final_eta ? \Carbon\Carbon::parse($schedule->final_eta)->format('Y-m-d') : '') }}"></div></div>
                            </div>
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Delivery To/Pier</label><div class="form-input-container"><select name="delivery_to_pier" class="form-control-gf"><option value="">Select...</option>@foreach($tradePartners as $tp)<option value="{{ $tp->name }}" {{ old('delivery_to_pier', $schedule->delivery_to_pier ?? '') == $tp->name ? 'selected' : '' }}>{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Place of Receipt</label><div class="form-input-container"><select name="por_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('por_id', $schedule->por_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Place of Deliv(DEL)</label><div class="form-input-container"><select name="del_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}" {{ old('del_id', $schedule->del_id ?? '') == $port->id ? 'selected' : '' }}>{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                            </div>
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Empty Pickup</label><div class="form-input-container"><select name="empty_pickup" class="form-control-gf"><option value="">Select...</option>@foreach($truckers as $t)<option value="{{ $t->name }}" {{ old('empty_pickup', $schedule->empty_pickup ?? '') == $t->name ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">POR ETD</label><div class="form-input-container"><input type="date" name="por_etd" class="form-control-gf" value="{{ old('por_etd', isset($schedule) && $schedule->por_etd ? \Carbon\Carbon::parse($schedule->por_etd)->format('Y-m-d') : '') }}"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">DEL ETA</label><div class="form-input-container"><input type="date" name="del_eta" class="form-control-gf" value="{{ old('del_eta', isset($schedule) && $schedule->del_eta ? \Carbon\Carbon::parse($schedule->del_eta)->format('Y-m-d') : '') }}"></div></div>
                            </div>
                        </div>

                        <div class="form-grid-4" style="margin-top: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px;">
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Freight</label><div class="form-input-container"><select name="freight" class="form-control-gf"><option value="">Select</option><option value="COLLECT" {{ old('freight', $schedule->freight ?? '') == 'COLLECT' ? 'selected' : '' }}>COLLECT</option><option value="PREPAID" {{ old('freight', $schedule->freight ?? '') == 'PREPAID' ? 'selected' : '' }}>PREPAID</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">OB/L Type</label><div class="form-input-container"><select name="obl_type" class="form-control-gf"><option value="">Select</option><option value="EXPRESS BILL" {{ old('obl_type', $schedule->obl_type ?? '') == 'EXPRESS BILL' ? 'selected' : '' }}>EXPRESS BILL</option><option value="ORIGINAL" {{ old('obl_type', $schedule->obl_type ?? '') == 'ORIGINAL' ? 'selected' : '' }}>ORIGINAL</option><option value="TELEX" {{ old('obl_type', $schedule->obl_type ?? '') == 'TELEX' ? 'selected' : '' }}>TELEX</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">On Board Date</label><div class="form-input-container"><input type="date" name="on_board_date" class="form-control-gf" value="{{ old('on_board_date', isset($schedule) && $schedule->on_board_date ? \Carbon\Carbon::parse($schedule->on_board_date)->format('Y-m-d') : '') }}"></div></div>
                            </div>
                            <div>
                                <div class="form-group-gf"><label class="form-label-gf">Ship Mode</label><div class="form-input-container"><select name="ship_mode" class="form-control-gf"><option value="">Select</option><option value="FCL" {{ old('ship_mode', $schedule->ship_mode ?? '') == 'FCL' ? 'selected' : '' }}>FCL</option><option value="LCL" {{ old('ship_mode', $schedule->ship_mode ?? '') == 'LCL' ? 'selected' : '' }}>LCL</option></select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Doc Cut-Off</label><div class="form-input-container"><input type="date" name="doc_cutoff" class="form-control-gf" value="{{ old('doc_cutoff', isset($schedule) && $schedule->doc_cutoff ? \Carbon\Carbon::parse($schedule->doc_cutoff)->format('Y-m-d') : '') }}"></div></div>
                            </div>
                            <div class="col-span-2">
                                <div class="form-group-gf"><label class="form-label-gf">SVC Term</label><div class="form-input-container"><select name="svc_term_from_id" class="form-control-gf"><option value="">Select</option>@foreach($serviceTerms as $st)<option value="{{ $st->id }}" {{ old('svc_term_from_id', $schedule->svc_term_from_id ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>@endforeach</select> <span style="margin: 0 4px; font-weight: bold;">~</span> <select name="svc_term_to_id" class="form-control-gf"><option value="">Select</option>@foreach($serviceTerms as $st)<option value="{{ $st->id }}" {{ old('svc_term_to_id', $schedule->svc_term_to_id ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>@endforeach</select></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Port Cut-Off</label><div class="form-input-container"><input type="date" name="port_cutoff" class="form-control-gf" value="{{ old('port_cutoff', isset($schedule) && $schedule->port_cutoff ? \Carbon\Carbon::parse($schedule->port_cutoff)->format('Y-m-d') : '') }}"></div></div>
                                <div class="form-group-gf"><label class="form-label-gf">Rail Cut-Off</label><div class="form-input-container"><input type="date" name="rail_cutoff" class="form-control-gf" value="{{ old('rail_cutoff', isset($schedule) && $schedule->rail_cutoff ? \Carbon\Carbon::parse($schedule->rail_cutoff)->format('Y-m-d') : '') }}"></div></div>
                            </div>
                        </div>

                        <!-- Schedule Master Container List -->
                        <div style="margin-top: 15px; border-top: 1px solid #cbd5e1; padding-top: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <div style="font-weight: 700; font-size: 12px; color: #334155; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-cubes"></i> Container List
                                    <span style="font-weight: normal; font-size: 11px; color: #64748b;" x-text="'(' + containers.length + ' container(s))'"></span>
                                </div>
                                <div style="display:flex; gap:6px;">
                                    <button type="button" class="btn-tool" @click="addContainer"><i class="fa fa-plus"></i> Add</button>
                                    <button type="button" class="btn-default-gf" @click="addContainers(5)"><i class="fa fa-plus-circle"></i> +5</button>
                                    <button type="button" class="btn-default-gf" @click="createMbl"><i class="fa fa-plus-square"></i> Create MB/L</button>
                                    <button type="button" class="btn-default-gf" style="color:#ef4444;" @click="deleteSelectedContainers"><i class="fa fa-trash"></i> Delete Selected</button>
                                </div>
                            </div>
                            
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width:25px; text-align:center;"><input type="checkbox" :checked="containers.length > 0 && containers.every(c => c.selected)" @click="toggleSelectAll"></th>
                                        <th style="width:30px; text-align:center;">#</th>
                                        <th>Container No.</th>
                                        <th>TP/SZ</th>
                                        <th>Seal No.</th>
                                        <th>Booking No.</th>
                                        <th style="text-align:right;">PKG</th>
                                        <th style="text-align:right;">Weight (KG)</th>
                                        <th style="text-align:right;">Measure (CBM)</th>
                                        <th>MB/L</th>
                                        <th style="text-align:center; width:50px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(c, i) in containers" :key="i">
                                        <tr :style="c.selected ? 'background:#eff6ff;' : ''">
                                            <td style="text-align: center;"><input type="checkbox" x-model="c.selected"></td>
                                            <td style="text-align: center;" x-text="i + 1"></td>
                                            <td><input type="text" x-model="c.container_no" class="form-control-gf" style="width:100%;" placeholder="Container No."></td>
                                            <td>
                                                <select x-model="c.type_size" class="form-control-gf" style="width:100%;">
                                                    <option value="">Select</option>
                                                    <option value="20GP">20GP</option>
                                                    <option value="40GP">40GP</option>
                                                    <option value="40HQ">40HQ</option>
                                                    <option value="45HQ">45HQ</option>
                                                    <option value="20RF">20RF</option>
                                                    <option value="40RF">40RF</option>
                                                    <option value="20OT">20OT</option>
                                                    <option value="40OT">40OT</option>
                                                    <option value="20FR">20FR</option>
                                                    <option value="40FR">40FR</option>
                                                </select>
                                            </td>
                                            <td><input type="text" x-model="c.seal_no" class="form-control-gf" style="width:100%;" placeholder="Seal No."></td>
                                            <td><input type="text" x-model="c.booking_no" class="form-control-gf" style="width:100%;" placeholder="Booking No."></td>
                                            <td><input type="number" x-model="c.pkg" class="form-control-gf" style="width:100%;text-align:right;" @input="calcContainerTotals"></td>
                                            <td><input type="number" step="0.01" x-model="c.weight" class="form-control-gf" style="width:100%;text-align:right;" @input="calcContainerTotals"></td>
                                            <td><input type="number" step="0.01" x-model="c.measure" class="form-control-gf" style="width:100%;text-align:right;" @input="calcContainerTotals"></td>
                                            <td style="text-align: center; font-size: 11px; font-weight:600;" x-text="c.mbl_no || '--'"></td>
                                            <td style="text-align: center;">
                                                <button type="button" @click="containers.splice(i, 1); calcContainerTotals()" class="btn-default-gf" style="color:#ef4444;padding:2px 6px;" title="Delete row"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="containers.length === 0">
                                        <td colspan="11" style="text-align:center; color:#94a3b8; padding: 15px; font-style:italic;">No containers added. Click "+ Add" to create containers.</td>
                                    </tr>
                                    <tr style="background:#f8fafc; font-weight:700;">
                                        <td colspan="6" style="text-align:right; color:#475569;">Total:</td>
                                        <td style="text-align:right; color:#0284c7;" x-text="containerTotals.pkg"></td>
                                        <td style="text-align:right; color:#0284c7;" x-text="containerTotals.weight"></td>
                                        <td style="text-align:right; color:#0284c7;" x-text="containerTotals.measure"></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Booking Boards (HBL style Yellow Portlet) -->
                <template x-for="(hbl, index) in bookings" :key="index">
                    <div class="portlet light" style="margin-top: 15px; border-top: 3px solid #f59e0b;" x-show="activeBookingIdx === index || activeBookingIdx === -1">
                        <div class="portlet-title" style="background: #f59e0b; color: #fff; min-height: 30px; padding: 4px 12px; display:flex; justify-content:space-between; align-items:center;">
                            <span class="caption-subject" style="color: #fff; font-size: 12px; font-weight:600;" x-text="'Booking ' + (index + 1) + ': ' + (hbl.booking_no || 'New Booking')"></span>
                            <div class="actions" style="display:flex; gap:10px; align-items:center;">
                                <button type="button" class="btn-default-gf" style="border:none; background:rgba(255,255,255,0.2); color:#fff; font-size:10px;" @click="duplicateBooking(index)"><i class="fa fa-copy"></i> Duplicate</button>
                                <i class="fa fa-times" style="font-size:13px; cursor:pointer; opacity:0.9;" @click="deleteBooking(index)" title="Remove Booking Card"></i>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="form-grid-4">
                                <!-- Col 1 -->
                                <div class="well-gf">
                                    <div class="form-group-gf">
                                        <label class="form-label-gf" style="color:#ef4444; font-weight:600;">* Booking No.</label>
                                        <div class="form-input-container">
                                            <label style="margin-right:4px;"><input type="checkbox" x-model="hbl.auto_booking_no" @change="if(hbl.auto_booking_no) hbl.booking_no = 'BKG-' + Date.now().toString().slice(-6)"></label>
                                            <input type="text" x-model="hbl.booking_no" class="form-control-gf" placeholder="Booking No.">
                                        </div>
                                    </div>
                                    <div class="form-group-gf"><label class="form-label-gf" style="color:#ef4444; font-weight:600;">* Booking Date</label><div class="form-input-container"><input type="date" x-model="hbl.booking_date" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">HB/L No.</label><div class="form-input-container"><input type="text" x-model="hbl.hbl_no" class="form-control-gf" placeholder="Auto HB/L No."></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Quotation No.</label><div class="form-input-container"><input type="text" x-model="hbl.quotation_no" class="form-control-gf"><button type="button" class="btn-default-gf"><i class="fa fa-search"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">ITN No.</label><div class="form-input-container"><input type="text" x-model="hbl.itn_no" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Sales</label><div class="form-input-container"><select x-model="hbl.sales_person_id" class="form-control-gf"><option value="">Select</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div></div>
                                    <div style="font-size: 10px; font-weight: bold; color: #64748b; margin-top: 8px;">OP: {{ optional($loggedUser)->name ?? 'System' }} ({{ optional($loggedUser)->email ?? '' }})</div>
                                </div>
                                <!-- Col 2 -->
                                <div>
                                    <div class="form-group-gf"><label class="form-label-gf">Reference No.</label><div class="form-input-container"><input type="text" x-model="hbl.reference_no" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Carrier Bkg No.</label><div class="form-input-container"><input type="text" x-model="hbl.carrier_bkg_no" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Carrier</label><div class="form-input-container"><select x-model="hbl.carrier_id" class="form-control-gf"><option value="">Select...</option>@foreach($carriers as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Ship Mode</label><div class="form-input-container"><select x-model="hbl.ship_mode" class="form-control-gf"><option value="">Select</option><option value="FCL">FCL</option><option value="LCL">LCL</option></select></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Service Term</label><div class="form-input-container"><select x-model="hbl.svc_term_from_id" class="form-control-gf"><option value="">Select</option>@foreach($serviceTerms as $st)<option value="{{ $st->id }}">{{ $st->name }}</option>@endforeach</select> <span style="margin: 0 2px;">~</span> <select x-model="hbl.svc_term_to_id" class="form-control-gf"><option value="">Select</option>@foreach($serviceTerms as $st)<option value="{{ $st->id }}">{{ $st->name }}</option>@endforeach</select></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Incoterms</label><div class="form-input-container"><select x-model="hbl.incoterms" class="form-control-gf"><option value="">Select</option><option value="FOB">FOB</option><option value="CIF">CIF</option><option value="EXW">EXW</option><option value="DDP">DDP</option><option value="DAP">DAP</option></select></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Actual Shipper</label><div class="form-input-container"><select x-model="hbl.actual_shipper_id" class="form-control-gf"><option value="">Select...</option>@foreach($tradePartners as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Customer</label><div class="form-input-container"><select x-model="hbl.customer_id" class="form-control-gf"><option value="">Select...</option>@foreach($customers as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Bill To</label><div class="form-input-container"><select x-model="hbl.bill_to_id" class="form-control-gf"><option value="">Select...</option>@foreach($tradePartners as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Consignee</label><div class="form-input-container"><select x-model="hbl.consignee_id" class="form-control-gf"><option value="">Select...</option>@foreach($tradePartners as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                </div>
                                <!-- Col 3 -->
                                <div>
                                    <div class="form-group-gf"><label class="form-label-gf">Vessel</label><div class="form-input-container"><input type="text" x-model="hbl.vessel" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Voyage</label><div class="form-input-container"><input type="text" x-model="hbl.voyage" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Pickup No.</label><div class="form-input-container"><input type="text" x-model="hbl.pickup_no" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Place Receipt</label><div class="form-input-container"><select x-model="hbl.por_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}">{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Port of Loading</label><div class="form-input-container"><select x-model="hbl.pol_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}">{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">ETD</label><div class="form-input-container"><input type="date" x-model="hbl.etd" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Port Discharge</label><div class="form-input-container"><select x-model="hbl.pod_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}">{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">ETA</label><div class="form-input-container"><input type="date" x-model="hbl.eta" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Place Deliv(DEL)</label><div class="form-input-container"><select x-model="hbl.del_id" class="form-control-gf" data-ajax-search="true"
    data-search-url="{{ route('ports.search') }}"><option value="">Select...</option>@foreach($ports as $port)<option value="{{ $port->id }}">{{ $port->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('port')"><i class="fa fa-plus"></i></button></div></div>
                                </div>
                                <!-- Col 4 -->
                                <div>
                                    <div class="form-group-gf"><label class="form-label-gf">Cargo Type</label><div class="form-input-container"><select x-model="hbl.cargo_type" class="form-control-gf"><option value="">Select</option><option value="GENERAL CARGO">GENERAL CARGO</option><option value="DANGEROUS GOODS">DANGEROUS GOODS</option><option value="REEFER">REEFER</option></select></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Referred By</label><div class="form-input-container"><select x-model="hbl.referred_by_id" class="form-control-gf"><option value="">Select...</option>@foreach($tradePartners as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Cargo Pickup</label><div class="form-input-container"><input type="text" x-model="hbl.cargo_pickup" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Trucker</label><div class="form-input-container"><select x-model="hbl.trucker_id" class="form-control-gf"><option value="">Select...</option>@foreach($truckers as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select><button type="button" class="btn-default-gf" @click="openQuickAdd('trade-partner')"><i class="fa fa-plus"></i></button></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Deliv To/Pier</label><div class="form-input-container"><input type="text" x-model="hbl.delivery_to_pier" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Cargo Ready</label><div class="form-input-container"><input type="date" x-model="hbl.cargo_ready" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Empty Pickup</label><div class="form-input-container"><input type="text" x-model="hbl.empty_pickup" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">Doc Cut-Off</label><div class="form-input-container"><input type="date" x-model="hbl.doc_cutoff" class="form-control-gf"></div></div>
                                    <div class="form-group-gf"><label class="form-label-gf">VGM Cut-Off</label><div class="form-input-container"><input type="date" x-model="hbl.vgm_cutoff" class="form-control-gf"></div></div>
                                </div>
                            </div>

                            <!-- P.O. Number Tagging -->
                            <div style="border-top: 1px solid #e2e8f0; margin: 10px 0; padding-top: 8px;">
                                <div class="form-group-gf" style="align-items: center;">
                                    <label class="form-label-gf" style="font-weight:700;">P.O. No.</label>
                                    <div class="form-input-container" style="align-items: center; gap: 6px;">
                                        <input type="text" x-model="newPoInput[index]" @keydown.enter.prevent="addPoNumber(index)" class="form-control-gf" style="width: 220px;" placeholder="Add P.O. No and press Enter...">
                                        <button type="button" class="btn-tool" @click="addPoNumber(index)"><i class="fa fa-plus"></i> Add PO</button>
                                    </div>
                                    <div style="margin-left: auto; display: flex; gap: 12px; font-size: 11px;">
                                        <label style="cursor:pointer;"><input type="radio" x-model="hbl.po_map" value="container"> Container based</label>
                                        <label style="cursor:pointer;"><input type="radio" x-model="hbl.po_map" value="item"> Item based</label>
                                    </div>
                                </div>
                                <div style="margin-top: 6px; display:flex; flex-wrap:wrap;">
                                    <template x-for="(po, pIdx) in (hbl.po_numbers || [])" :key="pIdx">
                                        <span class="po-tag">
                                            <span x-text="po"></span>
                                            <button type="button" @click="removePoNumber(index, pIdx)" title="Remove PO">&times;</button>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Booking Receiving Totals & WH Integration -->
                            <div style="background:#f1f5f9; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px;">
                                <div style="display: flex; gap: 15px; margin-bottom: 8px; align-items: center;">
                                    <label style="font-size: 11px; font-weight: 700; cursor:pointer;"><input type="radio" x-model="hbl.total_mode" value="booking"> Booking Total</label>
                                    <label style="font-size: 11px; font-weight: 700; cursor:pointer;"><input type="radio" x-model="hbl.total_mode" value="receiving"> Receiving Total</label>
                                    <button type="button" class="btn-default-gf" style="color:#0284c7; border-color:#0284c7;" @click="openWarehouseLoadModal(index)"><i class="fa fa-download"></i> Load from Warehouse</button>
                                </div>
                                <table class="table-custom">
                                    <thead>
                                        <tr><th style="width:120px;">Source</th><th colspan="2">PKG</th><th colspan="2">Weight (KG)</th><th colspan="2">Measurement (CBM)</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-weight:700; text-align:center;" x-text="hbl.total_mode === 'booking' ? 'Booking Total' : 'Receiving Total'"></td>
                                            <td colspan="2" style="text-align:center; font-weight:600;" x-text="calcBookingCommodityTotals(index).pkg"></td>
                                            <td colspan="2" style="text-align:right; font-weight:600;" x-text="calcBookingCommodityTotals(index).weight + ' KG'"></td>
                                            <td colspan="2" style="text-align:right; font-weight:600;" x-text="calcBookingCommodityTotals(index).measure + ' CBM'"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Dynamic Commodity Table -->
                            <div style="margin-top: 12px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 5px;">
                                    <div class="caption-subject" style="font-weight:700; font-size:12px; color:#334155;">Commodities</div>
                                    <button type="button" class="btn-tool" @click="addCommodityToBooking(index)"><i class="fa fa-plus"></i> Add Commodity</button>
                                </div>
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th style="width:25px; text-align:center;">#</th>
                                            <th>Description</th>
                                            <th>HTS Code</th>
                                            <th style="text-align:right;">PKG</th>
                                            <th style="text-align:right;">PCS</th>
                                            <th style="text-align:right;">Gross Wt</th>
                                            <th style="text-align:right;">Price</th>
                                            <th style="text-align:right;">Amount</th>
                                            <th>Container</th>
                                            <th style="text-align:center; width:40px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(comm, cIdx) in (hbl.commodities || [])" :key="cIdx">
                                            <tr>
                                                <td style="text-align:center;" x-text="cIdx + 1"></td>
                                                <td><input type="text" x-model="comm.description" class="form-control-gf" style="width:100%;" placeholder="Description"></td>
                                                <td><input type="text" x-model="comm.hts_code" class="form-control-gf" style="width:100%;" placeholder="HTS Code"></td>
                                                <td><input type="number" x-model="comm.pkg" class="form-control-gf" style="width:100%; text-align:right;"></td>
                                                <td><input type="number" x-model="comm.pcs" class="form-control-gf" style="width:100%; text-align:right;" @input="comm.amount = ((parseFloat(comm.pcs)||0)*(parseFloat(comm.price)||0)).toFixed(2)"></td>
                                                <td><input type="number" step="0.01" x-model="comm.weight" class="form-control-gf" style="width:100%; text-align:right;"></td>
                                                <td><input type="number" step="0.01" x-model="comm.price" class="form-control-gf" style="width:100%; text-align:right;" @input="comm.amount = ((parseFloat(comm.pcs)||0)*(parseFloat(comm.price)||0)).toFixed(2)"></td>
                                                <td><input type="number" step="0.01" x-model="comm.amount" class="form-control-gf" style="width:100%; text-align:right;" readonly></td>
                                                <td>
                                                    <select x-model="comm.container_no" class="form-control-gf" style="width:100%;">
                                                        <option value="">Select Container</option>
                                                        <template x-for="c in containers" :key="c.container_no">
                                                            <option :value="c.container_no" x-text="c.container_no || 'Unassigned'"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                                <td style="text-align:center;">
                                                    <button type="button" @click="hbl.commodities.splice(cIdx, 1)" class="btn-default-gf" style="color:#ef4444; padding:2px 5px;"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="!hbl.commodities || hbl.commodities.length === 0">
                                            <td colspan="10" style="text-align:center; color:#94a3b8; padding: 12px; font-style:italic;">No commodities added. Click "+ Add Commodity" to append item details.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-grid-4" style="margin-top: 10px;">
                                <div class="col-span-2">
                                    <label class="form-label-gf" style="text-align:left; font-weight:700;">Mark</label>
                                    <textarea x-model="hbl.mark" class="form-control-gf" style="height: 45px; margin-top: 2px;"></textarea>
                                </div>
                                <div class="col-span-2">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <label class="form-label-gf" style="text-align:left; font-weight:700;">Description</label> 
                                        <div style="font-size:10px;">
                                            Copy: 
                                            <button type="button" class="btn-default-gf" style="padding:1px 5px;" @click="copyPoToDesc(index)">P.O.</button> 
                                            <button type="button" class="btn-default-gf" style="padding:1px 5px;" @click="copyCommToDesc(index)">Comm</button>
                                        </div>
                                    </div>
                                    <textarea x-model="hbl.description" class="form-control-gf" style="height: 45px; margin-top: 2px;"></textarea>
                                </div>
                            </div>

                            <div style="margin-top: 10px;">
                                <div style="display:flex; gap:2px; border-bottom:1px solid #cbd5e1;">
                                    <button type="button" class="btn-default-gf" :style="hbl.instruction_tab === 'booking' ? 'background:#3b82f6; color:#fff; border-color:#3b82f6;' : ''" @click="hbl.instruction_tab = 'booking'">Booking Instruction</button>
                                    <button type="button" class="btn-default-gf" :style="hbl.instruction_tab === 'shipping' ? 'background:#3b82f6; color:#fff; border-color:#3b82f6;' : ''" @click="hbl.instruction_tab = 'shipping'">Shipping Instruction</button>
                                    <button type="button" class="btn-default-gf" :style="hbl.instruction_tab === 'manifest' ? 'background:#3b82f6; color:#fff; border-color:#3b82f6;' : ''" @click="hbl.instruction_tab = 'manifest'">Manifest by Vessel</button>
                                </div>
                                <textarea x-model="hbl.instructions[hbl.instruction_tab || 'booking']" class="form-control-gf" style="height: 50px; border-top:none;" placeholder="Enter instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Memo Section -->
                <div class="portlet light" style="margin-top: 15px;">
                    <div class="portlet-title" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:6px 12px;">
                        <span style="font-weight:700; color:#334155; font-size:12px;"><i class="fa fa-sticky-note-o"></i> Schedule Memos</span>
                    </div>
                    <div class="portlet-body" style="padding: 10px;">
                        <div style="display:flex; gap:12px;">
                            <div style="flex:2;">
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th style="width:30px; text-align:center;"><button type="button" @click="addMemo" class="btn-tool" style="padding:1px 5px;" title="Add Memo"><i class="fa fa-plus"></i></button></th>
                                            <th>Subject</th>
                                            <th>Last Modified</th>
                                            <th>Created</th>
                                            <th style="text-align:center; width:50px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(m, i) in memos" :key="i">
                                            <tr @click="selectMemo(i)" :style="selectedMemoIdx === i ? 'background:#eff6ff;' : ''" style="cursor:pointer;">
                                                <td style="text-align:center;"><i class="fa fa-file-text-o"></i></td>
                                                <td x-text="m.subject || '(No subject)'" style="font-weight:600;"></td>
                                                <td x-text="m.updated_at ? new Date(m.updated_at).toLocaleString() : '--'"></td>
                                                <td x-text="m.created_at ? new Date(m.created_at).toLocaleString() : '--'"></td>
                                                <td style="text-align:center;">
                                                    <button type="button" @click.stop="deleteMemo(i)" class="btn-default-gf" style="color:#ef4444;padding:1px 5px;" title="Delete"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="memos.length === 0">
                                            <td colspan="5" style="text-align:center; color:#94a3b8; padding:12px; font-style:italic;">No memos found. Click "+" to create a memo.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div style="flex:1; background:#f8fafc; border:1px solid #e2e8f0; padding:10px; border-radius:4px;">
                                <div style="font-weight:700; font-size:11px; margin-bottom:5px; color:#334155;">Memo Subject & Content</div>
                                <input type="text" x-model="memoSubject" class="form-control-gf" style="margin-bottom:6px;" placeholder="Memo subject..." :disabled="selectedMemoIdx < 0">
                                <textarea x-model="memoContent" class="form-control-gf" style="height:70px;" placeholder="Memo details..." :disabled="selectedMemoIdx < 0"></textarea>
                                <div style="margin-top:6px; text-align:right;" x-show="selectedMemoIdx >= 0">
                                    <button type="button" @click="saveMemo" class="btn-tool" style="padding:3px 10px;">Save</button>
                                    <button type="button" @click="selectedMemoIdx = -1; memoContent = ''; memoSubject = ''" class="btn-default-gf" style="padding:3px 10px; margin-left:4px;">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Toolbar -->
                <div style="display: flex; justify-content: center; gap: 12px; margin-top: 25px; padding-bottom: 40px;">
                    <button type="submit" class="btn-tool" style="background:#10b981; border-color:#059669; color:#fff; padding: 8px 36px; font-size: 13px; font-weight:bold; border-radius: 4px;"><i class="fa fa-check"></i> SAVE VESSEL SCHEDULE</button>
                    <a href="{{ route('vessel-schedules.index') }}" class="btn-default-gf" style="padding: 8px 24px; font-size: 13px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">CANCEL</a>
                </div>
                </form>
            </div>

            <!-- ================= ACCOUNTING TAB ================= -->
            <div x-show="activeTab === 'accounting'" style="display: none;">
                <div class="portlet light" style="margin-bottom: 12px !important;">
                    <div class="portlet-title" style="background: #2f353b; color: #fff; min-height: 32px; padding: 4px 12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size: 13px; font-weight: 600;">Vessel Schedule Charges - {{ $schedule->schedule_no ?? 'New' }}</span>
                        <div class="actions" style="display:flex; gap:6px;">
                            <button type="button" class="btn-default-gf" style="background: rgba(255,255,255,0.2); border: none; color: #fff;" @click="applyChargeTemplate"><i class="fa fa-magic"></i> Charge Template</button>
                        </div>
                    </div>
                    <div class="portlet-body" style="padding: 0;">
                        <div class="accounting-toolbar">
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn-accounting" style="background:#10b981; border-color:#059669;" @click="openChargeModal('AR')"><i class="fa fa-plus"></i> Origin Revenue (AR)</button>
                                <button type="button" class="btn-accounting" style="background:#3b82f6; border-color:#2563eb;" @click="openChargeModal('DC')"><i class="fa fa-plus"></i> D/C Note (DC)</button>
                                <button type="button" class="btn-accounting" style="background:#ef4444; border-color:#dc2626;" @click="openChargeModal('AP')"><i class="fa fa-plus"></i> Origin Cost (AP)</button>
                            </div>
                        </div>
                        
                        <div style="padding: 12px;">
                            <table class="table-accounting">
                                <thead>
                                    <tr>
                                        <th style="width: 25px;">#</th>
                                        <th style="text-align: left;">Charge Code / Name</th>
                                        <th style="text-align: left;">Party (Vendor/Bill To)</th>
                                        <th style="text-align: right;">Revenue</th>
                                        <th style="text-align: right;">Cost</th>
                                        <th style="text-align: right;">Balance</th>
                                        <th style="text-align: center;">Type</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(c, i) in charges" :key="c.id">
                                        <tr>
                                            <td style="text-align: center;" x-text="i + 1"></td>
                                            <td x-text="(c.charge_code || '') + ' - ' + (c.charge_name || '')" style="font-weight:600;"></td>
                                            <td x-text="c.bill_to?.name || c.vendor?.name || '--'"></td>
                                            <td style="text-align: right; color: #10b981; font-weight:600;" x-text="c.type === 'AR' ? '$' + parseFloat(c.total_amount || c.amount || 0).toFixed(2) : '$0.00'"></td>
                                            <td style="text-align: right; color: #ef4444; font-weight:600;" x-text="c.type === 'AP' ? '$' + parseFloat(c.total_amount || c.amount || 0).toFixed(2) : '$0.00'"></td>
                                            <td style="text-align: right; font-weight:600;" x-text="'$' + parseFloat(c.total_amount || c.amount || 0).toFixed(2)"></td>
                                            <td style="text-align: center;"><span class="shipment-status-badge" :style="c.type === 'AR' ? 'background:#10b981;' : (c.type === 'AP' ? 'background:#ef4444;' : 'background:#3b82f6;')" x-text="c.type"></span></td>
                                            <td style="text-align: center;">
                                                <button type="button" @click="editChargeModal(c)" class="btn-default-gf" style="padding: 2px 6px;"><i class="fa fa-pencil"></i></button>
                                                <button type="button" @click="deleteCharge(c.id)" class="btn-default-gf" style="padding: 2px 6px; color: #ef4444;"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="charges.length === 0">
                                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 20px; font-style:italic;">No charges recorded. Click buttons above to add Revenue or Cost charges.</td>
                                    </tr>
                                    <tr style="background:#f8fafc; font-weight:bold;">
                                        <td colspan="3" style="text-align: right;">Total Summary:</td>
                                        <td style="text-align: right; color: #10b981;" x-text="'$' + chargesAr().toFixed(2)"></td>
                                        <td style="text-align: right; color: #ef4444;" x-text="'$' + chargesAp().toFixed(2)"></td>
                                        <td style="text-align: right; color: #1e293b;" x-text="'$' + chargesTotal().toFixed(2)"></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table-accounting" style="margin-top: 15px;">
                                <thead>
                                    <tr>
                                        <th>Profit Indicator</th>
                                        <th style="text-align: right;">Amount</th>
                                        <th style="text-align: right;">Profit Margin %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-weight: 700;">Schedule Net Profit</td>
                                        <td style="text-align: right; color: #10b981; font-weight: 700; font-size:13px;" x-text="'$' + (chargesAr() - chargesAp()).toFixed(2)"></td>
                                        <td style="text-align: right; color: #10b981; font-weight: 700; font-size:13px;" x-text="chargesAr() > 0 ? ((chargesAr() - chargesAp()) / chargesAr() * 100).toFixed(1) + '%' : '0%'"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= DOC CENTER TAB ================= -->
            <div x-show="activeTab === 'document'" style="display: none;">
                <div class="portlet light" style="margin-bottom: 12px !important;">
                    <div class="portlet-title" style="background: #2f353b; color: #fff; min-height: 32px; padding: 4px 12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size: 13px; font-weight: 600;">Doc Center - {{ $schedule->schedule_no ?? 'New' }}</span>
                    </div>
                    <div class="portlet-body" style="padding: 12px;">
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 250px; max-width: 320px;">
                                <div style="border: 2px dashed #cbd5e1; border-radius: 4px; padding: 25px 15px; text-align: center; background: #f8fafc;">
                                    <i class="fa fa-cloud-upload" style="font-size: 32px; color: #94a3b8; margin-bottom: 8px;"></i>
                                    <div style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Select or Drop File to Upload</div>
                                    <input type="file" style="font-size: 11px; margin-top: 5px;" @change="uploadDocument">
                                </div>
                            </div>
                            <div style="flex: 3; min-width: 400px;">
                                <table class="table-custom" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Uploaded Date</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th style="text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="doc in scheduleDocuments" :key="doc.id">
                                            <tr>
                                                <td><a :href="VESSEL_SCHEDULE_ROUTES.documentDownload(doc.id)" x-text="doc.file_name" style="font-weight:600; color:#3b82f6;" target="_blank"></a></td>
                                                <td x-text="new Date(doc.created_at).toLocaleDateString()"></td>
                                                <td x-text="doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '--'"></td>
                                                <td x-text="doc.file_extension || 'file'"></td>
                                                <td style="text-align: center;">
                                                    <a :href="VESSEL_SCHEDULE_ROUTES.documentDownload(doc.id)" class="btn-default-gf" style="padding: 2px 6px; text-decoration: none;" target="_blank"><i class="fa fa-download"></i></a>
                                                    <button type="button" @click="deleteDocument(doc.id)" class="btn-default-gf" style="padding: 2px 6px; color: #ef4444;"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="scheduleDocuments.length === 0">
                                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px; font-style:italic;">No documents uploaded yet.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= WORK ORDER TAB ================= -->
            <div x-show="activeTab === 'workorder'" style="display: none;">
                <div class="portlet light" style="margin-bottom: 12px !important;">
                    <div class="portlet-title" style="background: #2f353b; color: #fff; min-height: 32px; padding: 4px 12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size: 13px; font-weight: 600;">Work Orders / Delivery Orders</span>
                        <button type="button" class="btn-tool" style="background: #10b981; border-color:#059669; color:#fff;" @click="pdoType = 'mbl'; showPdoModal = true"><i class="fa fa-plus"></i> + Add Work Order (PDO)</button>
                    </div>
                    <div class="portlet-body" style="padding: 12px;">
                        <table class="table-custom" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 30px; text-align: center;">#</th>
                                    <th>Work Order No.</th>
                                    <th>Subject / Instructions</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(wo, i) in scheduleWorkOrders" :key="wo.id">
                                    <tr>
                                        <td style="text-align: center;" x-text="i + 1"></td>
                                        <td style="font-weight:600;" x-text="wo.work_order_no || 'PDO-' + wo.id"></td>
                                        <td x-text="wo.subject || wo.instructions || 'Pickup Delivery Order'"></td>
                                        <td style="text-align:center;"><span class="shipment-status-badge" style="background:#3b82f6;" x-text="wo.status || 'PENDING'"></span></td>
                                        <td x-text="wo.created_at ? new Date(wo.created_at).toLocaleDateString() : '--'"></td>
                                        <td style="text-align: center;">
                                            <button type="button" @click="deleteWorkOrder(wo.id)" class="btn-default-gf" style="padding: 2px 6px; color: #ef4444;"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="scheduleWorkOrders.length === 0">
                                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px; font-style:italic;">No work orders created. Click "+ Add Work Order" to issue a Delivery Order.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= STATUS TAB ================= -->
            <div x-show="activeTab === 'status'" style="display: none;">
                <div class="portlet light" style="margin-bottom: 12px !important;">
                    <div class="portlet-title" style="background: #2f353b; color: #fff; min-height: 32px; padding: 4px 12px;">
                        <span style="font-size: 13px; font-weight: 600;">Status & Change Logs</span>
                    </div>
                    <div class="portlet-body" style="padding: 15px;">
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div style="flex: 0 0 30%;">
                                <h4 style="font-size: 13px; font-weight: 700; margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; color:#334155;">Assigned Operator</h4>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <select name="op_id" class="form-control-gf" style="flex: 1;"><option value="">Select Operator</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ old('op_id', $schedule->op_id ?? $loggedUser->id ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach</select>
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="font-size: 13px; font-weight: 700; margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; color:#334155;">Internal Log Message</h4>
                                <textarea x-model="internalMessage" class="form-control-gf" style="width: 100%; min-height: 60px; resize: vertical;" placeholder="Type status update or internal note..."></textarea>
                                <button type="button" @click="saveStatus" class="btn-tool" style="margin-top: 6px; padding: 4px 15px;"><i class="fa fa-save"></i> Save Status Log</button>
                            </div>
                        </div>

                        <h4 style="font-size: 13px; font-weight: 700; margin-top: 15px; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; color:#334155;">Activity & Change Log</h4>
                        <div style="background: #f8fafc; padding: 15px; border-left: 3px solid #3b82f6; border-radius: 2px;">
                            <template x-for="(log, i) in statusLogs" :key="log.id || i">
                                <div style="display: flex; gap: 15px; margin-bottom: 12px; align-items: flex-start;">
                                    <div style="flex: 0 0 90px; font-size: 10px; color: #64748b; text-align: right;">
                                        <div x-text="new Date(log.event_time || log.created_at).toLocaleDateString()" style="font-weight:600;"></div>
                                        <div x-text="new Date(log.event_time || log.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})"></div>
                                    </div>
                                    <div style="flex: 0 0 28px; height: 28px; background: #3b82f6; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px;" x-text="(log.user_name?.[0] || 'S').toUpperCase()"></div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 12px; font-weight: 700; color: #1e293b;" x-text="log.status_name || 'System Event'"></div>
                                        <div style="font-size: 11px; color: #475569; margin-top: 2px;" x-text="log.details || ''"></div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="statusLogs.length === 0" style="color: #94a3b8; font-size: 11px; text-align: center; padding: 10px; font-style:italic;">No status logs recorded yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDO Modal Wrapper -->
        <div class="pdo-overlay" x-show="showPdoModal" style="display: none;" x-transition>
            <div class="pdo-modal" @click.away="showPdoModal = false">
                <div class="pdo-toolbar">
                    <div style="font-size: 13px; font-weight: bold; color: #1e293b;"><i class="fa fa-truck"></i> Pickup / Delivery Order</div>
                    <div>
                        <button type="button" class="btn-tool" style="background: #10b981; border-color:#059669;" @click="submitWorkOrder"><i class="fa fa-save"></i> Save Work Order</button>
                        <button type="button" class="btn-default-gf" @click="showPdoModal = false"><i class="fa fa-times"></i> Close</button>
                    </div>
                </div>
                
                <div class="pdo-header">
                    <div style="flex: 1;">
                         <div class="pdo-title">{{ optional($loggedUser)->name ?? 'System User' }}</div>
                         <div class="pdo-address">
                             {{ optional($loggedUser)->email ? 'Email: ' . optional($loggedUser)->email : '' }}<br>
                             <strong>Prepared by {{ optional($loggedUser)->name ?? 'System User' }} {{ now()->format('m-d-Y H:i') }}</strong>
                        </div>
                    </div>
                    <div style="width: 240px; text-align: right;">
                        <select class="pdo-input" style="height: 26px; font-size: 11px; font-weight:bold;">
                            <option>PICKUP & DELIVERY ORDER</option>
                        </select>
                    </div>
                </div>

                <div class="pdo-body">
                    <table class="pdo-top-table">
                        <tr>
                            <td width="15%">ISSUED AT:</td>
                            <td width="35%" style="font-weight: normal;">{{ now()->format('m-d-Y') }}</td>
                            <td width="15%">ISSUED BY:</td>
                            <td width="35%" style="font-weight: normal;">{{ optional($loggedUser)->name ?? 'System User' }}</td>
                        </tr>
                    </table>

                    <div class="pdo-layout">
                        <!-- Left Column -->
                        <div class="pdo-left">
                            <div class="pdo-block">
                                <div class="pdo-block-title">TRUCKER <select style="width: 110px; font-size: 10px;"><option>Select Trucker</option>@foreach($truckers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
                                <textarea class="pdo-textarea" style="min-height: 50px;"></textarea>
                            </div>
                            
                            <div class="pdo-block">
                                <div class="pdo-block-title"><label style="cursor:pointer;"><input type="checkbox" checked style="margin:0;"> EMPTY PICK UP LOCATION</label></div>
                                <textarea class="pdo-textarea" style="min-height: 45px;"></textarea>
                            </div>
                            
                            <div class="pdo-block">
                                <div class="pdo-block-title">FREIGHT PICK UP LOCATION</div>
                                <textarea class="pdo-textarea" style="min-height: 45px;"></textarea>
                            </div>

                            <div class="pdo-block">
                                <div class="pdo-block-title"><label style="cursor:pointer;"><input type="checkbox" checked style="margin:0;"> LOADED RETURN/DELIVERY TO</label></div>
                                <textarea class="pdo-textarea" style="min-height: 45px;"></textarea>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="pdo-right">
                            <table class="pdo-table">
                                <tr>
                                    <td width="50%"><div class="td-label">MB/L NO.</div><div style="font-weight:bold;">{{ $schedule->schedule_no ?? 'AUTO' }}</div></td>
                                    <td width="50%"><div class="td-label">CARRIER BKG NO.</div><input type="text" class="pdo-input" value="{{ $schedule->carrier_bkg_no ?? '' }}"></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><div class="td-label">VESSEL INFO</div><div style="font-weight:bold;">{{ $schedule->vessel->name ?? '' }} {{ $schedule->voyage ?? '' }}</div></td>
                                </tr>
                                <tr>
                                    <td><div class="td-label">PORT OF LOADING</div><div style="font-weight:bold;">{{ $schedule->pol->name ?? '' }}</div></td>
                                    <td><div class="td-label">ETD</div><div style="font-weight:bold;">{{ isset($schedule->etd) ? \Carbon\Carbon::parse($schedule->etd)->format('m-d-Y') : '' }}</div></td>
                                </tr>
                                <tr>
                                    <td><div class="td-label">PORT OF DISCHARGE</div><div style="font-weight:bold;">{{ $schedule->pod->name ?? '' }}</div></td>
                                    <td><div class="td-label">ETA</div><div style="font-weight:bold;">{{ isset($schedule->eta) ? \Carbon\Carbon::parse($schedule->eta)->format('m-d-Y') : '' }}</div></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="td-label">GROSS WEIGHT / MEASUREMENT</div>
                                        <div style="display: flex; gap: 8px; margin-top: 3px;">
                                            <input type="text" value="0.00 KGS" class="pdo-input" style="flex:1;">
                                            <input type="text" value="0.00 CBM" class="pdo-input" style="flex:1;">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <table class="pdo-table" style="margin-top: 10px;">
                        <tr>
                            <td width="100%">
                                <div class="td-label">DESCRIPTION / INSTRUCTIONS</div>
                                <textarea class="pdo-textarea" style="min-height: 55px; margin-top: 4px;"></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Charge Modal Wrapper -->
        <div class="pdo-overlay" x-show="showChargeModal" style="display: none;" x-transition>
            <div class="pdo-modal" style="width: 650px;" @click.away="showChargeModal = false">
                <div class="pdo-toolbar">
                    <div style="font-size: 13px; font-weight: bold; color: #1e293b;">Add / Edit Charge</div>
                    <div>
                        <button type="button" class="btn-tool" style="background: #10b981; border-color:#059669;" @click="saveChargeModal"><i class="fa fa-save"></i> Save Charge</button>
                        <button type="button" class="btn-default-gf" @click="showChargeModal = false"><i class="fa fa-times"></i> Close</button>
                    </div>
                </div>
                
                <div style="padding: 15px;">
                    <div class="form-grid-4">
                        <div class="form-group-gf">
                            <label class="form-label-gf">Type</label>
                            <div class="form-input-container">
                                <select x-model="chargeModalForm.type" class="form-control-gf">
                                    <option value="AR">AR (Revenue)</option>
                                    <option value="AP">AP (Cost)</option>
                                    <option value="DC">DC (Destination)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Charge Code</label>
                            <div class="form-input-container"><input type="text" x-model="chargeModalForm.charge_code" class="form-control-gf" placeholder="Code (e.g. OFT)"></div>
                        </div>
                        <div class="col-span-2">
                            <div class="form-group-gf">
                                <label class="form-label-gf">Charge Name</label>
                                <div class="form-input-container"><input type="text" x-model="chargeModalForm.charge_name" class="form-control-gf" placeholder="Full name"></div>
                            </div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Rate ($)</label>
                            <div class="form-input-container"><input type="number" step="0.01" x-model="chargeModalForm.rate" class="form-control-gf" @input="calcChargeModalAmount"></div>
                        </div>
                        <div class="form-group-gf">
                            <label class="form-label-gf">Qty</label>
                            <div class="form-input-container"><input type="number" x-model="chargeModalForm.qty" class="form-control-gf" @input="calcChargeModalAmount"></div>
                        </div>
                        <div class="col-span-2">
                            <div class="form-group-gf">
                                <label class="form-label-gf">Total Amount ($)</label>
                                <div class="form-input-container"><input type="number" step="0.01" x-model="chargeModalForm.amount" class="form-control-gf" style="font-weight:bold;" readonly></div>
                            </div>
                        </div>
                        <div class="col-span-2">
                            <div class="form-group-gf">
                                <label class="form-label-gf">Vendor / Bill To</label>
                                <div class="form-input-container">
                                    <select x-model="chargeModalForm.bill_to_id" class="form-control-gf">
                                        <option value="">Select Partner</option>
                                        @foreach($tradePartners as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-add-new-modal />

    <script>
        const VESSEL_SCHEDULE_ROUTES = {
            status: (id) => `/ocean-export/vessel-schedule/${id}/status`,
            statusSave: (id) => `/ocean-export/vessel-schedule/${id}/status`,
            chargesList: (id) => `/ocean-export/vessel-schedule/${id}/charges`,
            chargeStore: (id) => `/ocean-export/vessel-schedule/${id}/charges`,
            chargeUpdate: (id) => `/ocean-export/vessel-schedule/charges/${id}`,
            chargeDelete: (id) => `/ocean-export/vessel-schedule/charges/${id}`,
            documentsStore: (id) => `/ocean-export/vessel-schedule/${id}/documents`,
            documentDelete: (id) => `/ocean-export/vessel-schedule/documents/${id}`,
            documentDownload: (id) => `/ocean-export/vessel-schedule/documents/${id}/download`,
            workOrderStore: '{{ route("ocean-export.work-order.store") }}',
            workOrderDelete: (id) => `/ocean-export/work-order/${id}`
        };

        function vesselScheduleModule() {
            const scheduleId = {{ isset($schedule) ? $schedule->id : 'null' }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            return {
                activeTab: 'basic',
                activeBookingIdx: 0,
                hideVessel: false,
                saved: @json(isset($schedule) ? true : false),
                toasts: [],
                
                showPdoModal: false,
                pdoType: 'mbl',
                
                showChargeModal: false,
                chargeModalForm: { type: 'AR', charge_code: '', charge_name: '', rate: 0, qty: 1, amount: 0, bill_to_id: '' },
                editingChargeId: null,

                bookings: [],
                containers: [],
                memos: [],
                newPoInput: {},
                
                selectedMemoIdx: -1,
                memoSubject: '',
                memoContent: '',
                
                internalMessage: '{{ isset($schedule) ? addslashes($schedule->internal_message) : '' }}',
                statusLogs: [],
                charges: [],
                scheduleDocuments: [],
                scheduleWorkOrders: [],

                containerTotals: { pkg: 0, weight: '0.00', measure: '0.00' },

                showToast(type, text) {
                    const id = Date.now();
                    this.toasts.push({ id, type, text });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 4000);
                },

                init() {
                    // Populate initial container data if available
                    @if(isset($schedule) && $schedule->containers_data)
                        this.containers = @json($schedule->containers_data);
                    @else
                        this.containers = [];
                    @endif

                    // Populate initial memo data if available
                    @if(isset($schedule) && $schedule->memos_data)
                        this.memos = @json($schedule->memos_data);
                    @else
                        this.memos = [];
                    @endif

                    // Add at least 1 booking if empty
                    this.addBooking();

                    this.calcContainerTotals();

                    if (scheduleId) {
                        this.loadCharges();
                        this.loadDocuments();
                        this.loadWorkOrders();
                        this.loadStatusLogs();
                    }
                },

                // ===== BOOKINGS =====
                emptyBooking() {
                    return {
                        auto_booking_no: true,
                        booking_no: 'BKG-' + Date.now().toString().slice(-6),
                        booking_date: new Date().toISOString().split('T')[0],
                        hbl_no: '', quotation_no: '', itn_no: '',
                        sales_person_id: '', reference_no: '', carrier_bkg_no: '',
                        carrier_id: '', ship_mode: 'FCL', svc_term_from_id: '', svc_term_to_id: '',
                        incoterms: '', actual_shipper_id: '', customer_id: '', bill_to_id: '',
                        consignee_id: '', notify_id: '', vessel: '', voyage: '', pickup_no: '',
                        por_id: '', pol_id: '', etd: '', pod_id: '', eta: '', del_id: '',
                        fdest_id: '', final_eta: '', cargo_type: 'GENERAL CARGO', referred_by_id: '',
                        cargo_pickup: '', trucker_id: '', delivery_to_pier: '', cargo_ready: '',
                        empty_pickup: '', wh_cutoff: '', doc_cutoff: '', port_cutoff: '',
                        vgm_cutoff: '', office_id: '', op_id: '',
                        po_numbers: [],
                        po_map: 'container',
                        total_mode: 'booking',
                        commodities: [],
                        mark: '', description: '',
                        instruction_tab: 'booking',
                        instructions: { booking: '', shipping: '', manifest: '' }
                    };
                },
                addBooking() {
                    this.bookings.push(this.emptyBooking());
                    this.activeBookingIdx = this.bookings.length - 1;
                },
                duplicateBooking(idx) {
                    const cloned = JSON.parse(JSON.stringify(this.bookings[idx]));
                    cloned.booking_no = 'BKG-' + Date.now().toString().slice(-6);
                    this.bookings.push(cloned);
                    this.activeBookingIdx = this.bookings.length - 1;
                    this.showToast('info', 'Booking duplicated');
                },
                deleteBooking(idx) {
                    if (this.bookings.length <= 1) {
                        this.showToast('warning', 'At least 1 booking required');
                        return;
                    }
                    this.bookings.splice(idx, 1);
                    if (this.activeBookingIdx >= this.bookings.length) {
                        this.activeBookingIdx = this.bookings.length - 1;
                    }
                    this.showToast('info', 'Booking removed');
                },

                // ===== PO NUMBERS & COMMODITIES =====
                addPoNumber(bIdx) {
                    const val = (this.newPoInput[bIdx] || '').trim();
                    if (!val) return;
                    if (!this.bookings[bIdx].po_numbers) this.bookings[bIdx].po_numbers = [];
                    this.bookings[bIdx].po_numbers.push(val);
                    this.newPoInput[bIdx] = '';
                },
                removePoNumber(bIdx, pIdx) {
                    this.bookings[bIdx].po_numbers.splice(pIdx, 1);
                },
                addCommodityToBooking(bIdx) {
                    if (!this.bookings[bIdx].commodities) this.bookings[bIdx].commodities = [];
                    this.bookings[bIdx].commodities.push({
                        description: '', hts_code: '', pkg: 1, pcs: 1, weight: 0, price: 0, amount: 0, container_no: ''
                    });
                },
                calcBookingCommodityTotals(bIdx) {
                    const comms = this.bookings[bIdx].commodities || [];
                    const pkg = comms.reduce((s, c) => s + (parseFloat(c.pkg) || 0), 0);
                    const weight = comms.reduce((s, c) => s + (parseFloat(c.weight) || 0), 0);
                    const measure = comms.reduce((s, c) => s + (parseFloat(c.measure) || 0), 0);
                    return { pkg, weight: weight.toFixed(2), measure: measure.toFixed(2) };
                },
                copyPoToDesc(bIdx) {
                    const pos = this.bookings[bIdx].po_numbers || [];
                    if (!pos.length) return this.showToast('warning', 'No PO numbers to copy');
                    const text = 'PO#: ' + pos.join(', ');
                    this.bookings[bIdx].description = (this.bookings[bIdx].description ? this.bookings[bIdx].description + '\n' : '') + text;
                    this.showToast('success', 'PO numbers copied to description');
                },
                copyCommToDesc(bIdx) {
                    const comms = this.bookings[bIdx].commodities || [];
                    if (!comms.length) return this.showToast('warning', 'No commodities to copy');
                    const text = comms.map(c => c.description).filter(Boolean).join('\n');
                    this.bookings[bIdx].description = (this.bookings[bIdx].description ? this.bookings[bIdx].description + '\n' : '') + text;
                    this.showToast('success', 'Commodities copied to description');
                },

                // ===== CONTAINERS =====
                emptyContainer() {
                    return { container_no: '', type_size: '40HQ', seal_no: '', booking_no: '', pkg: 0, weight: 0, measure: 0, mbl_no: '', selected: false };
                },
                addContainer() {
                    this.containers.push(this.emptyContainer());
                    this.calcContainerTotals();
                },
                addContainers(count) {
                    for (let i = 0; i < count; i++) {
                        this.containers.push(this.emptyContainer());
                    }
                    this.calcContainerTotals();
                    this.showToast('info', count + ' containers added');
                },
                toggleSelectAll() {
                    const allSelected = this.containers.length > 0 && this.containers.every(c => c.selected);
                    this.containers.forEach(c => c.selected = !allSelected);
                },
                deleteSelectedContainers() {
                    const selected = this.containers.filter(c => c.selected);
                    if (!selected.length) return this.showToast('warning', 'Select at least one container');
                    this.containers = this.containers.filter(c => !c.selected);
                    this.calcContainerTotals();
                    this.showToast('info', selected.length + ' container(s) deleted');
                },
                createMbl() {
                    const selected = this.containers.filter(c => c.selected);
                    if (!selected.length) return this.showToast('warning', 'Select at least one container');
                    const mblNo = 'MBL-' + new Date().toISOString().slice(0,10).replace(/-/g,'') + '-' + String(Date.now()).slice(-4);
                    selected.forEach(c => { c.mbl_no = mblNo; c.selected = false; });
                    this.showToast('success', 'MB/L ' + mblNo + ' created for selected container(s)');
                },
                calcContainerTotals() {
                    const pkg = this.containers.reduce((s, c) => s + (parseFloat(c.pkg) || 0), 0);
                    const weight = this.containers.reduce((s, c) => s + (parseFloat(c.weight) || 0), 0);
                    const measure = this.containers.reduce((s, c) => s + (parseFloat(c.measure) || 0), 0);
                    this.containerTotals = { pkg, weight: weight.toFixed(2), measure: measure.toFixed(2) };
                },

                // ===== MEMOS =====
                addMemo() {
                    this.memos.push({ id: null, subject: 'New Memo', content: '', created_at: new Date().toISOString(), updated_at: new Date().toISOString() });
                    this.selectedMemoIdx = this.memos.length - 1;
                    this.memoSubject = 'New Memo';
                    this.memoContent = '';
                },
                selectMemo(idx) {
                    this.selectedMemoIdx = idx;
                    this.memoSubject = this.memos[idx].subject || '';
                    this.memoContent = this.memos[idx].content || '';
                },
                saveMemo() {
                    if (this.selectedMemoIdx < 0 || this.selectedMemoIdx >= this.memos.length) return;
                    this.memos[this.selectedMemoIdx].subject = this.memoSubject;
                    this.memos[this.selectedMemoIdx].content = this.memoContent;
                    this.memos[this.selectedMemoIdx].updated_at = new Date().toISOString();
                    this.selectedMemoIdx = -1;
                    this.memoSubject = '';
                    this.memoContent = '';
                    this.showToast('success', 'Memo saved');
                },
                deleteMemo(idx) {
                    this.memos.splice(idx, 1);
                    if (this.selectedMemoIdx === idx) {
                        this.selectedMemoIdx = -1;
                        this.memoSubject = '';
                        this.memoContent = '';
                    }
                    this.showToast('info', 'Memo deleted');
                },

                // ===== FORM SUBMISSION =====
                validateAndSubmit() {
                    const scheduleNoInput = document.querySelector('[name="schedule_no"]');
                    const officeInput = document.querySelector('[name="office_id"]');
                    const vesselInput = document.querySelector('[name="vessel_id"]');
                    const polInput = document.querySelector('[name="pol_id"]');
                    const podInput = document.querySelector('[name="pod_id"]');
                    const etdInput = document.querySelector('[name="etd"]');

                    if (!officeInput || !officeInput.value) return this.showToast('error', 'Please select Office');
                    if (!vesselInput || !vesselInput.value) return this.showToast('error', 'Please select Vessel');
                    if (!polInput || !polInput.value) return this.showToast('error', 'Please select Port of Loading');
                    if (!podInput || !podInput.value) return this.showToast('error', 'Please select Port of Discharge');
                    if (!etdInput || !etdInput.value) return this.showToast('error', 'Please enter ETD');

                    document.getElementById('bookings-json').value = JSON.stringify(this.bookings);
                    document.getElementById('containers-json').value = JSON.stringify(this.containers);
                    document.getElementById('memos-json').value = JSON.stringify(this.memos);

                    this.$refs.mainForm.submit();
                },
                submitMainForm() {
                    this.validateAndSubmit();
                },

                // ===== CHARGES =====
                loadCharges() {
                    if (!scheduleId) return;
                    fetch(VESSEL_SCHEDULE_ROUTES.chargesList(scheduleId), { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
                        .then(r => r.json())
                        .then(d => { if (d.success) this.charges = d.charges || []; })
                        .catch(() => {});
                },
                chargesTotal() { return this.charges.reduce((s, c) => s + parseFloat(c.total_amount || c.amount || 0), 0); },
                chargesAr() { return this.charges.filter(c => c.type === 'AR').reduce((s, c) => s + parseFloat(c.total_amount || c.amount || 0), 0); },
                chargesAp() { return this.charges.filter(c => c.type === 'AP').reduce((s, c) => s + parseFloat(c.total_amount || c.amount || 0), 0); },
                openChargeModal(type) {
                    this.chargeModalForm = { type: type, charge_code: '', charge_name: '', rate: 0, qty: 1, amount: 0, bill_to_id: '' };
                    this.editingChargeId = null;
                    this.showChargeModal = true;
                },
                editChargeModal(charge) {
                    this.chargeModalForm = { ...charge };
                    this.editingChargeId = charge.id;
                    this.showChargeModal = true;
                },
                calcChargeModalAmount() {
                    this.chargeModalForm.amount = ((parseFloat(this.chargeModalForm.rate) || 0) * (parseFloat(this.chargeModalForm.qty) || 1)).toFixed(2);
                },
                saveChargeModal() {
                    if (!scheduleId) return this.showToast('warning', 'Save schedule first');
                    if (!this.chargeModalForm.charge_code || !this.chargeModalForm.charge_name) {
                        return this.showToast('error', 'Charge code and name are required');
                    }
                    this.calcChargeModalAmount();
                    const url = this.editingChargeId ? VESSEL_SCHEDULE_ROUTES.chargeUpdate(this.editingChargeId) : VESSEL_SCHEDULE_ROUTES.chargeStore(scheduleId);
                    const method = this.editingChargeId ? 'PUT' : 'POST';

                    fetch(url, {
                        method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(this.chargeModalForm)
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            this.loadCharges();
                            this.showChargeModal = false;
                            this.showToast('success', 'Charge saved successfully');
                        }
                    })
                    .catch(() => this.showToast('error', 'Error saving charge'));
                },
                deleteCharge(id) {
                    if (!confirm('Delete this charge?')) return;
                    fetch(VESSEL_SCHEDULE_ROUTES.chargeDelete(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
                    .then(r => r.json())
                    .then(d => { if (d.success) { this.loadCharges(); this.showToast('info', 'Charge deleted'); } });
                },
                applyChargeTemplate() {
                    if (!scheduleId) return this.showToast('warning', 'Save schedule first');
                    fetch(`/vessel-schedules/${scheduleId}/charges/template`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } })
                    .then(r => r.json())
                    .then(d => { if (d.success) { this.loadCharges(); this.showToast('success', 'Charge template applied'); } });
                },

                // ===== DOCUMENTS =====
                loadDocuments() {
                    if (!scheduleId) return;
                    this.scheduleDocuments = @json(isset($schedule) ? $schedule->documents : []);
                },
                uploadDocument(e) {
                    if (!scheduleId) return this.showToast('warning', 'Save schedule first');
                    const file = e.target.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('file', file);
                    fetch(VESSEL_SCHEDULE_ROUTES.documentsStore(scheduleId), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData })
                    .then(r => r.json())
                    .then(d => { if (d.success) { this.loadDocuments(); e.target.value = ''; this.showToast('success', 'Document uploaded'); } });
                },
                deleteDocument(id) {
                    if (!confirm('Delete document?')) return;
                    fetch(VESSEL_SCHEDULE_ROUTES.documentDelete(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
                    .then(r => r.json())
                    .then(d => { if (d.success) { this.loadDocuments(); this.showToast('info', 'Document deleted'); } });
                },

                // ===== WORK ORDERS & PDO =====
                loadWorkOrders() {
                    if (!scheduleId) return;
                    this.scheduleWorkOrders = @json(isset($schedule) ? $schedule->workOrders : []);
                },
                submitWorkOrder() {
                    if (!scheduleId) return this.showToast('warning', 'Save schedule first');
                    const woNo = 'WO-' + Date.now().toString().slice(-6);
                    fetch(VESSEL_SCHEDULE_ROUTES.workOrderStore, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            work_order_no: woNo,
                            workable_type: 'App\\Models\\Schedule',
                            workable_id: scheduleId,
                            subject: 'Pickup / Delivery Order',
                            status: 'PENDING'
                        })
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.loadWorkOrders();
                        this.showPdoModal = false;
                        this.showToast('success', 'Work order issued');
                    })
                    .catch(() => this.showToast('error', 'Error creating work order'));
                },
                deleteWorkOrder(id) {
                    if (!confirm('Delete work order?')) return;
                    fetch(VESSEL_SCHEDULE_ROUTES.workOrderDelete(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
                    .then(r => r.json())
                    .then(d => { if (d.success) { this.loadWorkOrders(); this.showToast('info', 'Work order deleted'); } });
                },

                // ===== STATUS LOGS =====
                loadStatusLogs() {
                    if (!scheduleId) return;
                    fetch(VESSEL_SCHEDULE_ROUTES.status(scheduleId), { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
                        .then(r => r.json())
                        .then(d => { this.statusLogs = Array.isArray(d) ? d : []; })
                        .catch(() => {});
                },
                saveStatus() {
                    if (!scheduleId) return this.showToast('warning', 'Save schedule first');
                    const opSelect = document.querySelector('[name="op_id"]');
                    fetch(VESSEL_SCHEDULE_ROUTES.statusSave(scheduleId), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ internal_message: this.internalMessage, op_id: opSelect ? opSelect.value : '' })
                    })
                    .then(r => r.json())
                    .then(d => { if (d.success) { this.loadStatusLogs(); this.showToast('success', 'Status log saved'); } });
                },

                // ===== UTILS =====
                openQuickAdd(module) {
                    if (module === 'trade-partner') {
                        window.open('/trade-partner/create', '_blank');
                    } else if (module === 'vessel') {
                        window.dispatchEvent(new CustomEvent('open-add-new-modal', {
                            detail: { module: 'vessel', targetModel: '', targetSelect: 'vessel_id' }
                        }));
                    } else if (module === 'port') {
                        window.dispatchEvent(new CustomEvent('open-add-new-modal', {
                            detail: { module: 'port', targetModel: '', targetSelect: 'pol_id' }
                        }));
                    }
                },
                openWarehouseLoadModal(bIdx) {
                    this.showToast('info', 'Loading warehouse receipts...');
                },
                exportCsv() {
                    window.location.href = '/ocean-export/vessel-schedule/export-csv';
                },
                printSchedule() {
                    window.print();
                }
            }
        }
    </script>
</x-layout>
