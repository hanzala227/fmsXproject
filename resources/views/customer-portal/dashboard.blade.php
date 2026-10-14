@extends('customer-portal.layout')

@section('content')
<div style="padding: 20px;">
    <h2 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600; color: #1e293b; font-family: 'Oswald', sans-serif;">Overview Dashboard</h2>
    
    <!-- STATS CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Total Shipments Card -->
        <div style="background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #8b5cf6; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div>
                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Shipments</p>
                <h3 style="margin: 5px 0 0 0; font-size: 28px; font-weight: 800; color: #1e293b;">{{ $totalShipments ?? 0 }}</h3>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa fa-cubes"></i>
            </div>
        </div>

        <!-- Ocean Imports Card -->
        <div style="background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #3b82f6; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div>
                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Ocean Imports</p>
                <h3 style="margin: 5px 0 0 0; font-size: 28px; font-weight: 800; color: #1e293b;">{{ $oceanImports ?? 0 }}</h3>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa fa-ship"></i>
            </div>
        </div>

        <!-- Air Exports Card -->
        <div style="background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #0ea5e9; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div>
                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Air Exports</p>
                <h3 style="margin: 5px 0 0 0; font-size: 28px; font-weight: 800; color: #1e293b;">{{ $airExports ?? 0 }}</h3>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(14, 165, 233, 0.1); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa fa-plane"></i>
            </div>
        </div>
    </div>

    <!-- RECENT SHIPMENTS -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #334155;"><i class="fa fa-clock-o" style="color: #64748b; margin-right: 6px;"></i> Recent Shipments</h3>
            <a href="/portal/shipments" style="font-size: 12px; color: #3b82f6; text-decoration: none; font-weight: 600;">View All &rarr;</a>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #fff; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Booking No</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Routing (POL &rarr; POD)</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Vessel / Voyage</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Dates (ETD &rarr; ETA)</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentShipments ?? [] as $shipment)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s; cursor: pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" onclick="window.location.href='/portal/shipments/{{ $shipment->id }}'">
                            <td style="padding: 12px 16px; color: #1e293b; font-weight: 500;">
                                <div style="font-size: 13px;">{{ $shipment->file_no ?? '-' }}</div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Ocean Import</div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="color: #334155; font-size: 12px; font-weight: 500;"><i class="fa fa-arrow-circle-up" style="color:#3b82f6; width:14px;"></i> {{ $shipment->portOfLoading->name ?? ($shipment->pol_name ?? 'Unknown POL') }}</div>
                                <div style="color: #334155; font-size: 12px; font-weight: 500; margin-top: 4px;"><i class="fa fa-map-marker" style="color:#ef4444; width:14px;"></i> {{ $shipment->portOfDischarge->name ?? ($shipment->pod_name ?? 'Unknown POD') }}</div>
                            </td>
                            <td style="padding: 12px 16px; color: #475569; font-size: 12px;">
                                <div style="font-weight: 600; color: #1e293b;">{{ $shipment->vessel->name ?? ($shipment->vessel_name ?? 'TBA') }}</div>
                                <div style="margin-top: 2px;">Voy: {{ $shipment->voyage ?? 'TBA' }}</div>
                            </td>
                            <td style="padding: 12px 16px; color: #475569; font-size: 12px;">
                                <div style="color: #1e293b;"><span style="color:#64748b; font-size:10px;">ETD:</span> {{ isset($shipment->etd) ? \Carbon\Carbon::parse($shipment->etd)->format('M d, Y') : '-' }}</div>
                                <div style="margin-top: 2px; color: #1e293b;"><span style="color:#64748b; font-size:10px;">ETA:</span> {{ isset($shipment->eta) ? \Carbon\Carbon::parse($shipment->eta)->format('M d, Y') : '-' }}</div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                    {{ $shipment->status ?? 'Pending' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 13px;">
                                <i class="fa fa-cubes" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                                No recent shipments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
