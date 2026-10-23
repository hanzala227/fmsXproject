<x-layout>
<div style="padding: 20px; font-family: 'Inter', sans-serif;">
    
    <!-- Top Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-map-marker" style="color: #3b82f6;"></i> Carrier Track & Trace Center
            </h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">
                Real-time visibility across Hapag-Lloyd, Maersk Line, and global DCSA container shipments.
            </p>
        </div>

        <!-- Quick Instant Track Form -->
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" 
                   id="quick-track-input" 
                   placeholder="Enter Container No / Master B/L..." 
                   onkeyup="if(event.key === 'Enter') openQuickTracking()"
                   style="height: 36px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-family: monospace; min-width: 260px; outline: none;">
            <button type="button" 
                    onclick="openQuickTracking()"
                    style="height: 36px; padding: 0 16px; background: #3b82f6; border: none; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-search"></i> Trace Live
            </button>
        </div>
    </div>

    <!-- Metric Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Tracked</div>
            <div style="font-size: 24px; font-weight: 800; color: #1e293b; margin-top: 4px;">{{ $trackings->total() }}</div>
            <span style="font-size: 11px; color: #3b82f6;"><i class="fa fa-cubes"></i> Active Container Feed</span>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">In Transit / At Sea</div>
            <div style="font-size: 24px; font-weight: 800; color: #2563eb; margin-top: 4px;">
                {{ $trackings->where('current_status', 'in_transit')->count() }}
            </div>
            <span style="font-size: 11px; color: #64748b;"><i class="fa fa-ship"></i> Ocean Voyage Active</span>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Discharged / Port Arrived</div>
            <div style="font-size: 24px; font-weight: 800; color: #7c3aed; margin-top: 4px;">
                {{ $trackings->where('current_status', 'discharged')->count() }}
            </div>
            <span style="font-size: 11px; color: #64748b;"><i class="fa fa-anchor"></i> Ready for Pickup</span>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Delivered / Completed</div>
            <div style="font-size: 24px; font-weight: 800; color: #059669; margin-top: 4px;">
                {{ $trackings->where('current_status', 'delivered')->count() }}
            </div>
            <span style="font-size: 11px; color: #10b981;"><i class="fa fa-check-circle"></i> Closed Shipments</span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
        
        <form method="GET" action="{{ route('admin.tracking') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1;">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Filter by Container, Vessel, Port..." 
                   style="height: 32px; padding: 0 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; min-width: 220px;">

            <select name="carrier" style="height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; color: #334155;">
                <option value="">All Carriers</option>
                <option value="hapag_lloyd" {{ request('carrier') === 'hapag_lloyd' ? 'selected' : '' }}>Hapag-Lloyd</option>
                <option value="maersk" {{ request('carrier') === 'maersk' ? 'selected' : '' }}>Maersk Line</option>
                <option value="dcsa" {{ request('carrier') === 'dcsa' ? 'selected' : '' }}>Generic DCSA</option>
            </select>

            <select name="status" style="height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; color: #334155;">
                <option value="">All Statuses</option>
                <option value="booked" {{ request('status') === 'booked' ? 'selected' : '' }}>Booked</option>
                <option value="gate_in" {{ request('status') === 'gate_in' ? 'selected' : '' }}>Gate In</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                <option value="discharged" {{ request('status') === 'discharged' ? 'selected' : '' }}>Discharged</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
            </select>

            <button type="submit" style="height: 32px; padding: 0 14px; background: #405189; color: #fff; border: none; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;">
                Filter
            </button>

            @if(request()->hasAny(['search', 'carrier', 'status']))
                <a href="{{ route('admin.tracking') }}" style="font-size: 12px; color: #ef4444; text-decoration: none; margin-left: 6px;">Reset</a>
            @endif
        </form>

        <span style="font-size: 12px; color: #64748b;">
            Showing {{ $trackings->count() }} of {{ $trackings->total() }} records
        </span>
    </div>

    <!-- Active Tracking Grid -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Tracking / Container No.</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Carrier</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Routing (POL &rarr; POD)</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Vessel & Voyage</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Status & Progress</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Last Synced</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trackings as $tr)
                        @php
                            $badge = $tr->status_badge;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 16px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <strong style="font-family: monospace; font-size: 13px; color: #1e293b;">{{ $tr->tracking_number }}</strong>
                                    <span style="font-size: 9px; padding: 1px 5px; border-radius: 3px; background: #f1f5f9; color: #64748b; text-transform: uppercase;">
                                        {{ $tr->tracking_type }}
                                    </span>
                                </div>
                            </td>

                            <td style="padding: 12px 16px;">
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #eff6ff; color: #1e40af;">
                                    {{ strtoupper($tr->carrier_code) }}
                                </span>
                            </td>

                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #1e293b;">
                                    {{ $tr->origin_name ?: ($tr->origin_locode ?: 'Origin') }}
                                    <span style="color: #94a3b8; margin: 0 4px;">&rarr;</span>
                                    {{ $tr->destination_name ?: ($tr->destination_locode ?: 'Destination') }}
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    @if($tr->eta)
                                        ETA: <span style="color: #059669; font-weight: 500;">{{ $tr->eta->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            </td>

                            <td style="padding: 12px 16px; color: #334155;">
                                <div><i class="fa fa-ship" style="color: #64748b; margin-right: 4px;"></i> {{ $tr->vessel_name ?: '-' }}</div>
                                @if($tr->voyage_no)
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Voyage: {{ $tr->voyage_no }}</div>
                                @endif
                            </td>

                            <td style="padding: 12px 16px; min-width: 170px;">
                                <div style="margin-bottom: 4px;">
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                                <div style="background: #e2e8f0; height: 5px; border-radius: 3px; overflow: hidden; max-width: 140px;">
                                    <div style="background: #3b82f6; height: 100%; width: {{ $tr->progress_percentage }}%;"></div>
                                </div>
                            </td>

                            <td style="padding: 12px 16px; color: #64748b; font-size: 11px;">
                                {{ $tr->last_polled_at ? $tr->last_polled_at->diffForHumans() : 'Never' }}
                            </td>

                            <td style="padding: 12px 16px; text-align: center;">
                                <button type="button" 
                                        onclick="openTrackingModal('{{ $tr->tracking_number }}', '{{ $tr->carrier_code }}')"
                                        style="padding: 5px 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-crosshairs"></i> Live View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 32px; text-align: center; color: #94a3b8;">
                                No tracked shipments found. Enter a container number above or sync tracking inside any Ocean or Truck shipment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trackings->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0;">
                {{ $trackings->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function openTrackingModal(number, carrier) {
    if (window.openLiveTracking) {
        window.openLiveTracking({
            number: number,
            carrier: carrier
        });
    }
}

function openQuickTracking() {
    const input = document.getElementById('quick-track-input');
    const val = input ? input.value.trim() : '';
    if (!val) {
        if (typeof showToast === 'function') showToast('warning', 'Please enter a container or B/L number.');
        else alert('Please enter a container or B/L number.');
        return;
    }
    openTrackingModal(val, '');
}
</script>
</x-layout>
