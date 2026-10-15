<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup / Delivery Order - {{ $truckShipment->file_no ?? 'New Shipment' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; background: #f4f6f9; padding: 20px; }
        .document-container { max-width: 850px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .company-title { font-size: 22px; font-weight: 700; color: #1e3a8a; }
        .doc-title { font-size: 18px; font-weight: 700; color: #2563eb; text-transform: uppercase; text-align: right; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; background: #f8fafc; }
        .info-box-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }
        .info-row { display: flex; margin-bottom: 4px; }
        .info-label { width: 100px; font-weight: 600; color: #475569; }
        .info-value { flex: 1; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        th { background: #1e293b; color: #fff; padding: 8px 10px; font-size: 11px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; padding: 8px 10px; font-size: 11px; }
        .toolbar { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px; max-width: 850px; margin: 0 auto 15px auto; }
        .btn-action { padding: 6px 14px; border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-close { background: #64748b; color: #fff; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .document-container { box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()" class="btn-action btn-print"><i class="fa fa-print"></i> Print D/O</button>
        <button onclick="window.close()" class="btn-action btn-close"><i class="fa fa-times"></i> Close</button>
    </div>

    <div class="document-container">
        <div class="header">
            <div>
                <div class="company-title">FREIGHTX LOGISTICS</div>
                <div style="color: #64748b; margin-top: 4px;">Trucking & Transport Division</div>
            </div>
            <div>
                <div class="doc-title">Pickup / Delivery Order</div>
                <div style="text-align: right; font-weight: bold; color: #334155; margin-top: 4px;">Booking No: {{ $truckShipment->file_no ?? 'N/A' }}</div>
                <div style="text-align: right; color: #64748b; font-size: 11px;">DATE: {{ date('Y-m-d') }}</div>
            </div>
        </div>

        <div class="meta-grid">
            <div class="info-box">
                <div class="info-box-title">Pickup Details</div>
                <div class="info-row"><div class="info-label">Trucker:</div><div class="info-value">{{ $truckShipment->trucker->name ?? 'N/A' }}</div></div>
                <div class="info-row"><div class="info-label">Pickup Location:</div><div class="info-value">{{ $truckShipment->pol->name ?? 'N/A' }}</div></div>
                <div class="info-row"><div class="info-label">Pickup Date:</div><div class="info-value">{{ $truckShipment->pickup_date ?? 'N/A' }}</div></div>
                <div class="info-row"><div class="info-label">Driver/No:</div><div class="info-value">{{ $truckShipment->driver_name ?? 'N/A' }}</div></div>
            </div>

            <div class="info-box">
                <div class="info-box-title">Delivery Details</div>
                <div class="info-row"><div class="info-label">Consignee:</div><div class="info-value">{{ $truckShipment->consignee->name ?? 'N/A' }}</div></div>
                <div class="info-row"><div class="info-label">Delivery Location:</div><div class="info-value">{{ $truckShipment->pod->name ?? 'N/A' }}</div></div>
                <div class="info-row"><div class="info-label">Delivery Date:</div><div class="info-value">{{ $truckShipment->delivery_date ?? 'N/A' }}</div></div>
                <div class="info-row"><div class="info-label">Customer Ref:</div><div class="info-value">{{ $truckShipment->customer_ref ?? 'N/A' }}</div></div>
            </div>
        </div>

        <div class="info-box" style="margin-bottom: 20px;">
            <div class="info-box-title">Shipment Information</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <div><strong>Shipper:</strong> {{ $truckShipment->shipper->name ?? 'N/A' }}</div>
                <div><strong>Sales Rep:</strong> {{ $truckShipment->sales->name ?? 'N/A' }}</div>
                <div><strong>OP Rep:</strong> {{ $truckShipment->op->name ?? 'N/A' }}</div>
            </div>
        </div>

        <div style="font-weight: 700; font-size: 13px; margin-bottom: 8px; color: #1e293b;">CONTAINER & CARGO DETAILS</div>
        <table>
            <thead>
                <tr>
                    <th>Container No.</th>
                    <th>Type/Size</th>
                    <th>Seal No.</th>
                    <th style="text-align: right;">PKG</th>
                    <th style="text-align: right;">Weight (KG)</th>
                    <th style="text-align: right;">CBM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($truckShipment->containers ?? [] as $container)
                    <tr>
                        <td>{{ $container->container_no ?? 'N/A' }}</td>
                        <td>{{ $container->containerType->code ?? '-' }}</td>
                        <td>{{ $container->seal_no ?? '-' }}</td>
                        <td style="text-align: right;">{{ number_format($container->pkg ?? 0) }}</td>
                        <td style="text-align: right;">{{ number_format($container->weight ?? 0, 2) }}</td>
                        <td style="text-align: right;">{{ number_format($container->measurement ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">No containers added to this shipment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="info-box" style="margin-top: 20px;">
            <div class="info-box-title">Special Instructions</div>
            <div style="min-height: 40px; color: #475569;">
                {{ $truckShipment->remark ?? 'Handle with care. Deliver according to schedule.' }}
            </div>
        </div>

        <div style="margin-top: 40px; display: flex; justify-content: space-between;">
            <div style="width: 200px; border-top: 1px solid #334155; text-align: center; padding-top: 5px; font-weight: 600;">
                Driver Signature
            </div>
            <div style="width: 200px; border-top: 1px solid #334155; text-align: center; padding-top: 5px; font-weight: 600;">
                Receiver Signature
            </div>
        </div>
    </div>
</body>
</html>
