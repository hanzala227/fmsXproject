@extends('customer-portal.layout')

@section('content')
<div style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: #1e293b; font-family: 'Oswald', sans-serif;">My Shipments</h2>
    </div>

    <div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px 16px; font-weight: 600; color: #475569;">Booking No</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: #475569;">Routing (POL &rarr; POD)</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: #475569;">Vessel / Voyage</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: #475569;">MBL / HBL</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: #475569;">Dates (ETD &rarr; ETA)</th>
                        <th style="padding: 12px 16px; font-weight: 600; color: #475569;">Status</th>
                    </tr>
                </thead>
                <tbody id="shipment-list">
                    @include('customer-portal.partials.shipment-rows', ['shipments' => $shipments ?? []])
                </tbody>
            </table>
        </div>
        
        @if(isset($shipments) && method_exists($shipments, 'links'))
            <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0;">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
