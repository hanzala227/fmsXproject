@extends('customer-portal.layout')

@section('content')
<div style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('customer.shipments.index') }}" style="color: #64748b; text-decoration: none; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; transition: background 0.2s;">
                <i class="fa fa-arrow-left"></i>
            </a>
            <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: #1e293b; font-family: 'Oswald', sans-serif;">
                Shipment Details <span style="color: #3b82f6;">#{{ $shipment->file_no }}</span>
            </h2>
        </div>
        <span style="display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            {{ $shipment->status ?? 'Pending' }}
        </span>
    </div>

    <!-- MAIN INFO GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
        
        <!-- General Info -->
        <div style="background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #3b82f6;">
            <h3 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #334155; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <i class="fa fa-info-circle" style="color: #64748b; margin-right: 5px;"></i> General Information
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">MBL Number</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 600; margin-top: 2px;">{{ $shipment->mbl_no ?: '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">HBL Number</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 600; margin-top: 2px;">{{ $shipment->hbl_no ?: '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Vessel</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 500; margin-top: 2px;">{{ $shipment->vessel->name ?? ($shipment->vessel_name ?? '-') }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Voyage</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 500; margin-top: 2px;">{{ $shipment->voyage ?: '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Routing Info -->
        <div style="background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #10b981;">
            <h3 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #334155; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                <i class="fa fa-map" style="color: #64748b; margin-right: 5px;"></i> Routing Details
            </h3>
            
            <div style="position: relative; padding-left: 20px;">
                <!-- Vertical Line -->
                <div style="position: absolute; left: 6px; top: 15px; bottom: 15px; width: 2px; background: #e2e8f0;"></div>
                
                <div style="margin-bottom: 15px; position: relative;">
                    <div style="position: absolute; left: -21px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: #3b82f6; border: 2px solid #fff; box-shadow: 0 0 0 1px #3b82f6;"></div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Port of Loading (POL)</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 600; margin-top: 2px;">{{ $shipment->portOfLoading->name ?? ($shipment->pol_name ?? '-') }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><i class="fa fa-calendar-o"></i> ETD: <span style="color:#1e293b; font-weight:500;">{{ isset($shipment->etd) ? \Carbon\Carbon::parse($shipment->etd)->format('M d, Y') : 'TBA' }}</span></div>
                </div>

                <div style="position: relative;">
                    <div style="position: absolute; left: -21px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: #ef4444; border: 2px solid #fff; box-shadow: 0 0 0 1px #ef4444;"></div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Port of Discharge (POD)</div>
                    <div style="font-size: 13px; color: #1e293b; font-weight: 600; margin-top: 2px;">{{ $shipment->portOfDischarge->name ?? ($shipment->pod_name ?? '-') }}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><i class="fa fa-calendar-o"></i> ETA: <span style="color:#1e293b; font-weight:500;">{{ isset($shipment->eta) ? \Carbon\Carbon::parse($shipment->eta)->format('M d, Y') : 'TBA' }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTAINERS TABLE -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #334155;"><i class="fa fa-cubes" style="color: #64748b; margin-right: 6px;"></i> Containers</h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #fff; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Container No.</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Type / Size</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Seal No.</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Weight</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Volume (CBM)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipment->containers ?? [] as $container)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 16px; color: #1e293b; font-weight: 600;">{{ $container->container_no ?: '-' }}</td>
                            <td style="padding: 12px 16px; color: #475569;">{{ $container->containerType->code ?? ($container->container_type ?? '-') }}</td>
                            <td style="padding: 12px 16px; color: #475569;">{{ $container->seal_no ?: '-' }}</td>
                            <td style="padding: 12px 16px; color: #475569;">{{ $container->weight ?: '-' }} kg</td>
                            <td style="padding: 12px 16px; color: #475569;">{{ $container->measurement ?: '-' }} CBM</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 13px;">
                                <i class="fa fa-cube" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                                No containers associated with this shipment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- LIVE CARRIER TRACKING MILESTONES -->
    @if(isset($tracking) && $tracking)
        @php
            $badge = $tracking->status_badge;
            $stepNum = $badge['step'] ?? 2;
            $stepLabels = ['Booking Confirmed', 'Gate In', 'Loaded on Vessel', 'In Transit', 'Discharged', 'Delivered'];
        @endphp
        <div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px;">
            <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #334155;">
                    <i class="fa fa-map-marker" style="color: #3b82f6; margin-right: 6px;"></i> Live Carrier Tracking Milestones
                </h3>
                <span style="display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                    {{ $badge['label'] }} ({{ strtoupper($tracking->carrier_code) }})
                </span>
            </div>

            <div style="padding: 20px;">
                <!-- Stepper -->
                <div style="display: flex; justify-content: space-between; position: relative; margin-bottom: 24px;">
                    <div style="position: absolute; left: 16px; right: 16px; top: 12px; height: 3px; background: #e2e8f0; z-index: 1;"></div>
                    <div style="position: absolute; left: 16px; top: 12px; height: 3px; background: #3b82f6; z-index: 2; width: {{ ($stepNum / 5) * 100 }}%;"></div>

                    @foreach($stepLabels as $sIdx => $sLabel)
                        <div style="display: flex; flex-direction: column; align-items: center; text-align: center; width: 68px; position: relative; z-index: 3;">
                            <div style="width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; {{ $sIdx <= $stepNum ? 'background:#3b82f6; color:#fff;' : 'background:#fff; border:2px solid #cbd5e1; color:#94a3b8;' }}">
                                @if($sIdx < $stepNum)
                                    <i class="fa fa-check"></i>
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

                <!-- Event Timeline List -->
                <div style="position: relative; padding-left: 20px;">
                    <div style="position: absolute; left: 6px; top: 6px; bottom: 6px; width: 2px; background: #e2e8f0;"></div>

                    @forelse($tracking->events as $evt)
                        @php $cls = $evt->classifier_badge; @endphp
                        <div style="position: relative; margin-bottom: 14px;">
                            <div style="position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: #3b82f6; border: 2px solid #fff; box-shadow: 0 0 0 1px #3b82f6;"></div>
                            
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <strong style="font-size: 12px; color: #1e293b;">{{ $evt->title }}</strong>
                                        <span style="font-size: 9px; padding: 1px 5px; border-radius: 3px; font-weight: 700; background: {{ $cls['bg'] }}; color: {{ $cls['color'] }}; margin-left: 6px;">
                                            {{ $cls['text'] }}
                                        </span>
                                    </div>
                                    <span style="font-size: 11px; color: #64748b; font-family: monospace;">
                                        {{ $evt->event_date_time ? $evt->event_date_time->format('M d, Y H:i') : '' }}
                                    </span>
                                </div>
                                @if($evt->description)
                                    <div style="font-size: 11px; color: #475569; margin-top: 3px;">
                                        {{ $evt->description }}
                                    </div>
                                @endif
                                <div style="display: flex; gap: 12px; font-size: 10px; color: #64748b; margin-top: 4px;">
                                    @if($evt->location_name)
                                        <span><i class="fa fa-map-marker"></i> {{ $evt->location_name }}</span>
                                    @endif
                                    @if($evt->facility_name)
                                        <span><i class="fa fa-building-o"></i> {{ $evt->facility_name }}</span>
                                    @endif
                                    @if($evt->vessel_name)
                                        <span><i class="fa fa-ship"></i> {{ $evt->vessel_name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="font-size: 12px; color: #94a3b8; text-align: center; padding: 16px;">
                            No milestone events reported yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
