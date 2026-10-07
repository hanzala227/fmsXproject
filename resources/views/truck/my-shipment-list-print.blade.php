<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truck Shipment List - Print</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
            background: #fff;
        }
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4b77be;
        }
        .print-header h1 {
            font-size: 22px;
            color: #4b77be;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .print-header .subtitle {
            font-size: 12px;
            color: #64748b;
        }
        .print-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 10px;
            color: #64748b;
        }
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .print-table th {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }
        .print-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 6px;
            font-size: 10px;
            color: #475569;
        }
        .print-table tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .color-mark {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            border: 1px solid #e2e8f0;
        }
        .lock-icon {
            font-size: 9px;
        }
        .locked { color: #ef4444; }
        .unlocked { color: #22c55e; }
        .print-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }
        @media print {
            body {
                padding: 10px;
            }
            .print-header {
                margin-bottom: 10px;
                padding-bottom: 10px;
            }
            .print-table th,
            .print-table td {
                padding: 4px 3px;
            }
        }
        @page {
            size: landscape;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h1>🚛 TRUCK SHIPMENT LIST</h1>
        <div class="subtitle">Complete Shipment Records</div>
    </div>

    <div class="print-info">
        <div>
            <strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}
        </div>
        <div>
            <strong>Total Records:</strong> {{ $shipments->total() }}
        </div>
        <div>
            @if(request('search'))
                <strong>Search:</strong> {{ request('search') }}
            @endif
            @if(request('filter_file_no'))
                <strong>File No:</strong> {{ request('filter_file_no') }}
            @endif
        </div>
    </div>

    <table class="print-table">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Status</th>
                <th>File No.</th>
                <th class="text-center">Color</th>
                <th>Post Date</th>
                <th>Customer</th>
                <th>Trucker</th>
                <th>MB/L No.</th>
                <th>HB/L No.</th>
                <th class="text-right">PKG</th>
                <th class="text-right">Weight (KG)</th>
                <th>Port of Discharge</th>
                <th>Final Destination</th>
                <th class="text-right">AR Balance</th>
                <th class="text-center">D/O</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shipments as $index => $shipment)
                <tr>
                    <td class="text-center">{{ $shipments->firstItem() + $index }}</td>
                    <td class="text-center">
                        @php
                            $isBlocked = $shipment->is_blocked ?? false;
                        @endphp
                        <span class="lock-icon {{ $isBlocked ? 'locked' : 'unlocked' }}">
                            {{ $isBlocked ? '🔒' : '🔓' }}
                        </span>
                    </td>
                    <td><strong>{{ $shipment->file_no }}</strong></td>
                    <td>{{ $shipment->post_date ? $shipment->post_date->format('m-d-Y') : '--' }}</td>
                    <td>{{ $shipment->customer->name ?? '--' }}</td>
                    <td>{{ $shipment->trucker->name ?? '--' }}</td>
                    <td>{{ $shipment->mbl_no ?? '--' }}</td>
                    <td>{{ $shipment->hbl_no ?? '--' }}</td>
                    <td class="text-right">{{ $shipment->pkg_qty ?? 0 }}</td>
                    <td class="text-right">{{ number_format($shipment->weight_kg ?? 0, 2) }}</td>
                    <td>{{ $shipment->pod->name ?? '--' }}</td>
                    <td>{{ $shipment->finalDestination->name ?? '--' }}</td>
                    <td class="text-right">
                        @php
                            $arTotal = $shipment->charges->where('type', 'AR')->sum('amount');
                        @endphp
                        {{ $arTotal > 0 ? number_format($arTotal, 2) : 'N/A' }}
                    </td>
                    <td class="text-center">{{ $shipment->is_delivered ? 'Y' : 'N' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding:30px;">
                        No shipments found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-footer">
        Truck Shipment List Report • Generated by Freight Management System • {{ config('app.name') }}
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
