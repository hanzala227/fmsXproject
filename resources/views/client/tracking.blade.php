@extends('layouts.userapp')
@section('title', 'Track Shipments - Client Portal')

@section('content')
<div style="padding: 12px; font-family: 'Inter', sans-serif;" x-data="clientTrackingApp()">

    <!-- Top Banner & Search -->
    <div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-radius: 10px; padding: 24px; color: #fff; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div style="max-width: 700px;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #38bdf8; display: block; margin-bottom: 6px;">
                <i class="bi bi-geo-alt-fill"></i> Live Container Tracking
            </span>
            <h2 style="font-size: 22px; font-weight: 700; margin: 0 0 8px 0; color: #ffffff;">
                Track Your Cargo Milestones
            </h2>
            <p style="font-size: 13px; color: #cbd5e1; margin: 0 0 18px 0; line-height: 1.5;">
                Enter any Container number, Ocean Bill of Lading, or Booking reference to view live vessel schedules, port departures, and gate milestones.
            </p>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('client.tracking') }}" style="display: flex; gap: 8px;">
                <div style="position: relative; flex: 1;">
                    <i class="bi bi-search" style="position: absolute; left: 14px; top: 12px; color: #94a3b8; font-size: 14px;"></i>
                    <input type="text" 
                           name="q" 
                           x-model="searchQuery" 
                           value="{{ $searchNumber }}" 
                           placeholder="e.g. MSKU1234567, HLXU9876543, or MBL Number..." 
                           style="width: 100%; height: 42px; padding: 0 16px 0 38px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.15); color: #fff; font-size: 13px; font-family: monospace; outline: none; transition: background 0.2s;"
                           onfocus="this.style.background='rgba(255,255,255,0.25)'"
                           onblur="this.style.background='rgba(255,255,255,0.15)'">
                </div>
                <button type="submit" 
                        style="height: 42px; padding: 0 22px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-arrow-right-circle"></i> Track
                </button>
            </form>
        </div>
    </div>

    <!-- Error / Warning Message if not found on Carrier API -->
    @if(isset($errorMessage) && $errorMessage)
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-size: 13px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; color: #ef4444; flex-shrink: 0;"></i>
            <div>
                <strong>Carrier API Notice:</strong> {{ $errorMessage }}
            </div>
        </div>
    @endif

    <!-- Active Tracking Dashboard & Timeline -->
    @if($activeTracking)
        @php
            $badge = $activeTracking->status_badge;
            $progress = $activeTracking->progress_percentage ?: 40;
        @endphp

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px;">
            
            <!-- Left: Journey Overview & Stepper -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <!-- Main Status & Route Card -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px;">
                        <div>
                            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Tracking Number</span>
                            <div style="font-size: 18px; font-weight: 800; font-family: monospace; color: #1e293b; margin-top: 2px;">
                                {{ $activeTracking->tracking_number }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                                {{ $badge['label'] }}
                            </span>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                Carrier: <strong>{{ strtoupper($activeTracking->carrier_code) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Origin -> Destination Visual -->
                    <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div style="max-width: 40%;">
                            <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Origin Port (POL)</span>
                            <div style="font-size: 14px; font-weight: 700; color: #1e293b;">
                                {{ $activeTracking->origin_name ?: ($activeTracking->origin_locode ?: 'Origin Terminal') }}
                            </div>
                            <span style="font-size: 11px; color: #64748b;">
                                ETD: {{ $activeTracking->etd ? $activeTracking->etd->format('M d, Y') : 'TBA' }}
                            </span>
                        </div>

                        <div style="text-align: center; flex: 1; padding: 0 16px;">
                            <div style="font-size: 11px; font-weight: 600; color: #2563eb; margin-bottom: 4px;">
                                <i class="bi bi-arrow-right"></i> Ocean Transit
                            </div>
                            <div style="background: #e2e8f0; height: 4px; border-radius: 2px; position: relative;">
                                <div style="background: #2563eb; height: 100%; width: {{ $progress }}%; border-radius: 2px;"></div>
                            </div>
                        </div>

                        <div style="max-width: 40%; text-align: right;">
                            <span style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Destination (POD)</span>
                            <div style="font-size: 14px; font-weight: 700; color: #1e293b;">
                                {{ $activeTracking->destination_name ?: ($activeTracking->destination_locode ?: 'Port of Discharge') }}
                            </div>
                            <span style="font-size: 11px; color: #059669; font-weight: 600;">
                                ETA: {{ $activeTracking->eta ? $activeTracking->eta->format('M d, Y') : 'TBA' }}
                            </span>
                        </div>
                    </div>

                    <!-- 5-Step Visual Progression -->
                    @php
                        $stepNum = $badge['step'] ?? 2;
                        $stepLabels = ['Booking Confirmed', 'Gate In', 'Loaded on Vessel', 'In Transit', 'Discharged', 'Delivered'];
                    @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; position: relative;">
                            @foreach($stepLabels as $sIdx => $sLabel)
                                <div style="display: flex; flex-direction: column; align-items: center; text-align: center; width: 64px;">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; {{ $sIdx <= $stepNum ? 'background:#2563eb; color:#fff;' : 'background:#f1f5f9; color:#94a3b8; border:1px solid #cbd5e1;' }}">
                                        @if($sIdx < $stepNum)
                                            <i class="bi bi-check"></i>
                                        @else
                                            {{ $sIdx + 1 }}
                                        @endif
                                    </div>
                                    <span style="font-size: 10px; margin-top: 6px; font-weight: 600; {{ $sIdx <= $stepNum ? 'color:#1e293b;' : 'color:#94a3b8;' }}">
                                        {{ $sLabel }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Verified Event Timeline -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h3 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-clock-history" style="color: #2563eb;"></i> Milestone History
                    </h3>

                    <div style="position: relative; padding-left: 20px;">
                        <div style="position: absolute; left: 6px; top: 6px; bottom: 6px; width: 2px; background: #e2e8f0;"></div>

                        @forelse($activeTracking->events as $event)
                            @php $cls = $event->classifier_badge; @endphp
                            <div style="position: relative; margin-bottom: 18px;">
                                <div style="position: absolute; left: -20px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: #2563eb; border: 2px solid #fff; box-shadow: 0 0 0 2px #93c5fd;"></div>
                                
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <strong style="font-size: 13px; color: #1e293b;">{{ $event->title }}</strong>
                                            <span style="font-size: 9px; padding: 1px 6px; border-radius: 3px; font-weight: 700; background: {{ $cls['bg'] }}; color: {{ $cls['color'] }};">
                                                {{ $cls['text'] }}
                                            </span>
                                        </div>
                                        <span style="font-size: 11px; color: #64748b; font-family: monospace;">
                                            {{ $event->event_date_time ? $event->event_date_time->format('M d, Y H:i') : '' }}
                                        </span>
                                    </div>

                                    @if($event->description)
                                        <div style="font-size: 12px; color: #475569; margin-top: 4px;">
                                            {{ $event->description }}
                                        </div>
                                    @endif

                                    <div style="display: flex; gap: 14px; font-size: 11px; color: #64748b; margin-top: 6px; border-top: 1px solid #f1f5f9; padding-top: 6px;">
                                        @if($event->location_name)
                                            <span><i class="bi bi-geo-alt"></i> {{ $event->location_name }}</span>
                                        @endif
                                        @if($event->facility_name)
                                            <span><i class="bi bi-building"></i> {{ $event->facility_name }}</span>
                                        @endif
                                        @if($event->vessel_name)
                                            <span><i class="bi bi-ship"></i> {{ $event->vessel_name }} {{ $event->voyage_no ? ' / ' . $event->voyage_no : '' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div style="padding: 16px; text-align: center; color: #94a3b8; font-size: 12px;">
                                Milestones are being synchronized with the carrier. Please check back shortly.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Vessel & Shipment Details -->
            <div>
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <h3 style="margin: 0 0 14px 0; font-size: 13px; font-weight: 700; color: #1e293b; text-transform: uppercase;">
                        <i class="bi bi-info-circle"></i> Vessel & Voyage
                    </h3>
                    <div style="font-size: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <div>
                            <span style="color: #64748b; font-size: 11px;">Vessel Name:</span>
                            <div style="font-weight: 600; color: #1e293b;">{{ $activeTracking->vessel_name ?: 'Not Assigned' }}</div>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 11px;">Voyage Number:</span>
                            <div style="font-weight: 600; color: #1e293b;">{{ $activeTracking->voyage_no ?: '-' }}</div>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 11px;">Status Summary:</span>
                            <div style="color: #334155; line-height: 1.4;">{{ $activeTracking->status_description ?: 'Cargo in progress.' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Live Auto-Refresh Action -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; text-align: center;">
                    <span style="font-size: 12px; color: #1e40af; display: block; margin-bottom: 10px;">
                        Need latest updates from the shipping line?
                    </span>
                    <button type="button" 
                            @click="refreshTracking('{{ $activeTracking->tracking_number }}')" 
                            :disabled="isRefreshing"
                            style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi" :class="isRefreshing ? 'bi-arrow-repeat spin' : 'bi-arrow-clockwise'"></i>
                        <span x-text="isRefreshing ? 'Syncing...' : 'Sync Live Updates'"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Customer's Active Shipments List -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">
                <i class="bi bi-boxes" style="color: #64748b;"></i> Your Recent Shipments
            </h3>
            <span style="font-size: 12px; color: #64748b;">Click "Track" on any shipment to view its milestone progress</span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">File No.</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Master B/L</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Container No.</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Route (POL &rarr; POD)</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Vessel</th>
                        <th style="padding: 10px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oceanImports as $shp)
                        @php
                            $cNo = $shp->containers->first()->container_no ?? ($shp->mbl_no ?? '');
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 10px 16px; font-weight: 700; color: #2563eb;">
                                #{{ $shp->file_no }}
                            </td>
                            <td style="padding: 10px 16px; font-family: monospace; color: #1e293b;">
                                {{ $shp->mbl_no ?: '-' }}
                            </td>
                            <td style="padding: 10px 16px; font-family: monospace; font-weight: 600; color: #1e293b;">
                                {{ $cNo ?: '-' }}
                            </td>
                            <td style="padding: 10px 16px; color: #334155;">
                                {{ $shp->portOfLoading->name ?? ($shp->pol_name ?? '-') }}
                                <span style="color: #94a3b8;">&rarr;</span>
                                {{ $shp->portOfDischarge->name ?? ($shp->pod_name ?? '-') }}
                            </td>
                            <td style="padding: 10px 16px; color: #475569;">
                                {{ $shp->vessel->name ?? ($shp->vessel_name ?? '-') }}
                            </td>
                            <td style="padding: 10px 16px; text-align: center;">
                                @if($cNo)
                                    <a href="{{ route('client.tracking', ['q' => $cNo]) }}" 
                                       style="padding: 4px 12px; background: #2563eb; color: #fff; border-radius: 4px; font-size: 11px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-geo-alt"></i> Track
                                    </a>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px;">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 24px; text-align: center; color: #94a3b8;">
                                No shipments found for your account.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function clientTrackingApp() {
    return {
        searchQuery: '{{ $searchNumber }}',
        isRefreshing: false,

        refreshTracking(number) {
            this.isRefreshing = true;
            fetch('{{ route("api.tracking.sync") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tracking_number: number })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.message || 'Tracking sync failed.');
                }
            })
            .catch(() => {
                alert('Network error.');
            })
            .finally(() => {
                this.isRefreshing = false;
            });
        }
    };
}
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endsection
