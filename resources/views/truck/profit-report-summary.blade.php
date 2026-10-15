<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit Report - Summary - {{ $truckShipment->file_no ?? 'New Shipment' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; background: #f4f6f9; padding: 20px; }
        .document-container { max-width: 850px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .company-title { font-size: 22px; font-weight: 700; color: #1e3a8a; }
        .doc-title { font-size: 18px; font-weight: 700; color: #2563eb; text-transform: uppercase; text-align: right; }
        .summary-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; text-align: center; }
        .stat-title { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600; }
        .stat-value { font-size: 18px; font-weight: 700; margin-top: 4px; }
        .val-revenue { color: #2563eb; }
        .val-cost { color: #dc2626; }
        .val-profit { color: #16a34a; }
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
        <button onclick="window.print()" class="btn-action btn-print"><i class="fa fa-print"></i> Print Summary</button>
        <button onclick="window.close()" class="btn-action btn-close"><i class="fa fa-times"></i> Close</button>
    </div>

    <div class="document-container">
        <div class="header">
            <div>
                <div class="company-title">FREIGHTX LOGISTICS</div>
                <div style="color: #64748b; margin-top: 4px;">Trucking Profitability Report</div>
            </div>
            <div>
                <div class="doc-title">Profit Summary</div>
                <div style="text-align: right; font-weight: bold; color: #334155; margin-top: 4px;">Booking No: {{ $truckShipment->file_no ?? 'N/A' }}</div>
                <div style="text-align: right; color: #64748b; font-size: 11px;">DATE: {{ date('Y-m-d') }}</div>
            </div>
        </div>

        @php
            $revenue = $truckShipment->charges->where('type', 'AR')->sum('amount');
            $cost = $truckShipment->charges->where('type', 'AP')->sum('amount');
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
        @endphp

        <div class="summary-card">
            <div>
                <div class="stat-title">Total Revenue (AR)</div>
                <div class="stat-value val-revenue">${{ number_format($revenue, 2) }}</div>
            </div>
            <div>
                <div class="stat-title">Total Cost (AP)</div>
                <div class="stat-value val-cost">${{ number_format($cost, 2) }}</div>
            </div>
            <div>
                <div class="stat-title">Net Profit</div>
                <div class="stat-value val-profit">${{ number_format($profit, 2) }}</div>
            </div>
            <div>
                <div class="stat-title">Profit Margin</div>
                <div class="stat-value" style="color: #475569;">{{ number_format($margin, 1) }}%</div>
            </div>
        </div>

        <div style="font-weight: 700; font-size: 13px; margin-bottom: 8px; color: #1e293b;">CHARGES BREAKDOWN</div>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Charge Code / Name</th>
                    <th>Invoice / Party</th>
                    <th style="text-align: right;">Revenue ($)</th>
                    <th style="text-align: right;">Cost ($)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($truckShipment->charges ?? [] as $charge)
                    <tr>
                        <td><strong>{{ $charge->type }}</strong></td>
                        <td>{{ $charge->charge_code }} - {{ $charge->charge_name }}</td>
                        <td>{{ $charge->invoice_no ?? ($charge->party_name ?? 'N/A') }}</td>
                        <td style="text-align: right; color: #2563eb;">{{ $charge->type === 'AR' ? number_format($charge->amount, 2) : '0.00' }}</td>
                        <td style="text-align: right; color: #dc2626;">{{ $charge->type === 'AP' ? number_format($charge->amount, 2) : '0.00' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8;">No accounting charges recorded for this shipment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
