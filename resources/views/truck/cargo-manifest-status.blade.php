<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Manifest Status - {{ $truckShipment->file_no ?? 'New Shipment' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; background: #f4f6f9; padding: 20px; }
        .document-container { max-width: 850px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .company-title { font-size: 22px; font-weight: 700; color: #1e3a8a; }
        .doc-title { font-size: 18px; font-weight: 700; color: #2563eb; text-transform: uppercase; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        th { background: #1e293b; color: #fff; padding: 8px 10px; font-size: 11px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; padding: 8px 10px; font-size: 11px; }
        .badge-status { padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; text-transform: uppercase; background: #e2e8f0; color: #334155; }
        .badge-active { background: #dcfce7; color: #15803d; }
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
        <button onclick="window.print()" class="btn-action btn-print"><i class="fa fa-print"></i> Print Status</button>
        <button onclick="window.close()" class="btn-action btn-close"><i class="fa fa-times"></i> Close</button>
    </div>

    <div class="document-container">
        <div class="header">
            <div>
                <div class="company-title">FREIGHTX LOGISTICS</div>
                <div style="color: #64748b; margin-top: 4px;">Cargo Manifest Tracking</div>
            </div>
            <div>
                <div class="doc-title">Manifest Status</div>
                <div style="text-align: right; font-weight: bold; color: #334155; margin-top: 4px;">Booking No: {{ $truckShipment->file_no ?? 'N/A' }}</div>
                <div style="text-align: right; color: #64748b; font-size: 11px;">DATE: {{ date('Y-m-d') }}</div>
            </div>
        </div>

        <div style="font-weight: 700; font-size: 13px; margin-bottom: 8px; color: #1e293b;">MANIFESTED CARGO LIST</div>
        <table>
            <thead>
                <tr>
                    <th>Container / Item No.</th>
                    <th>Type</th>
                    <th>Seal No.</th>
                    <th>LFD</th>
                    <th>Appointment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($truckShipment->containers ?? [] as $container)
                    <tr>
                        <td><strong>{{ $container->container_no ?? 'N/A' }}</strong></td>
                        <td>{{ $container->containerType->code ?? '-' }}</td>
                        <td>{{ $container->seal_no ?? '-' }}</td>
                        <td>{{ $container->lfd ?? '-' }}</td>
                        <td>{{ $container->appointment ?? '-' }}</td>
                        <td><span class="badge-status badge-active">Manifested</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">No containers found in manifest.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
