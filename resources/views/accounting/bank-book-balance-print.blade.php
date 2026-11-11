<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Book Balance Report</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter','Open Sans',Arial,sans-serif;font-size:11px;color:#1e293b;padding:20px;background:#fff}
        .company{text-align:center;margin-bottom:16px;padding-bottom:14px;border-bottom:2px solid #1e293b}
        .company h1{font-size:18px;font-weight:700;color:#2563eb;margin-bottom:4px;letter-spacing:1px}
        .company .addr{font-size:10px;color:#475569;line-height:1.5}
        .title{text-align:center;margin:14px 0 6px;font-size:15px;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:1.5px}
        .subtitle{text-align:center;font-size:11px;color:#64748b;margin-bottom:16px}
        table{width:100%;border-collapse:collapse;margin-bottom:16px}
        th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:5px 8px;text-align:left;font-size:10px}
        td{padding:4px 8px;border:1px solid #e2e8f0;color:#334155;font-size:11px}
        td.num{text-align:right;font-family:'Courier New',monospace}
        .total-row td{background:#1e293b;color:#fff;font-weight:700;font-size:11px;border:1px solid #0f172a}
        .subtotal-row{background:#f0f9ff}
        .subtotal-row td{border-top:1px solid #93c5fd;color:#1e40af;font-weight:600}
        .badge-active{color:#16a34a}
        .badge-inactive{color:#dc2626}
        .footer{margin-top:20px;border-top:1px solid #e2e8f0;padding-top:10px;font-size:9px;color:#94a3b8;text-align:center}
        .no-print{text-align:right;margin-bottom:10px}
        .no-print button{padding:6px 16px;cursor:pointer;background:#3b82f6;color:#fff;border:1px solid #2563eb;border-radius:2px;font-size:11px;font-weight:600;margin-left:4px}
        .no-print button:hover{background:#2563eb}
        @media print{body{padding:10px}.no-print{display:none}}
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="company">
        <h1>FreightX</h1>
    </div>

    <div class="title">Bank Book Balance Report</div>
    <div class="subtitle">
        Period: {{ $periodFrom ?? 'N/A' }} ~ {{ $periodTo ?? 'N/A' }}
        &middot; {{ $reportType ?? 'Summary' }} Report
        &middot; All Amounts in USD (Base Currency)
    </div>

    @if(!count($rows))
        <div style="text-align:center;color:#94a3b8;padding:40px;font-size:12px;">No records found for the selected criteria.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Account No.</th>
                    <th>Currency</th>
                    <th class="num">Opening Balance (USD)</th>
                    <th class="num">Receipts (USD)</th>
                    <th class="num">Payments (USD)</th>
                    <th class="num">Closing Balance (USD)</th>
                    <th class="num">Book Balance (USD)</th>
                    <th class="num">Difference</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    @if($row['is_subtotal'] ?? false)
                    <tr class="subtotal-row">
                        <td colspan="3" style="text-align:right;font-style:italic;">{{ $row['subtotal_label'] }}</td>
                        <td class="num">{{ number_format($row['opening_balance'], 2) }}</td>
                        <td class="num">{{ number_format($row['receipts'], 2) }}</td>
                        <td class="num">{{ number_format($row['payments'], 2) }}</td>
                        <td class="num">{{ number_format($row['closing_balance'], 2) }}</td>
                        <td class="num">{{ number_format($row['book_balance'], 2) }}</td>
                        <td class="num">{{ number_format($row['difference'], 2) }}</td>
                        <td></td>
                    </tr>
                    @else
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['account_no'] }}</td>
                        <td>{{ $row['currency'] }}</td>
                        <td class="num">{{ number_format($row['opening_balance'], 2) }}</td>
                        <td class="num">{{ number_format($row['receipts'], 2) }}</td>
                        <td class="num">{{ number_format($row['payments'], 2) }}</td>
                        <td class="num">{{ number_format($row['closing_balance'], 2) }}</td>
                        <td class="num">{{ number_format($row['book_balance'], 2) }}</td>
                        <td class="num">{{ number_format($row['difference'], 2) }}</td>
                        <td><span class="{{ $row['status'] === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $row['status'] }}</span></td>
                    </tr>
                    @if(($reportType ?? 'Summary') === 'Detail' && !empty($row['transactions']))
                        @foreach($row['transactions'] as $txn)
                        <tr style="background:#f8fafc;font-size:10px;">
                            <td style="padding-left:18px;color:#64748b;">&nbsp;&nbsp;{{ $txn['date'] }}</td>
                            <td colspan="2" style="color:#334155;">{{ $txn['description'] }}</td>
                            <td class="num" style="color:#94a3b8;">--</td>
                            <td class="num" style="color:#16a34a;">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '' }}</td>
                            <td class="num" style="color:#dc2626;">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '' }}</td>
                            <td class="num" style="font-weight:600;">{{ number_format($txn['balance'], 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                        @endforeach
                    @endif
                    @endif
                @endforeach
                @if(($currencyMode ?? 'bank_currency') === 'bank_currency' && !empty($currencySubtotals))
                    @foreach($currencySubtotals as $sub)
                    <tr class="subtotal-row">
                        <td colspan="3" style="text-align:right;font-style:italic;font-weight:600;">SUBTOTAL ({{ $sub['currency'] }}) [Rate: {{ number_format($sub['rate'] ?? 1, 4) }}]</td>
                        <td class="num">{{ number_format($sub['opening_balance'], 2) }}</td>
                        <td class="num">{{ number_format($sub['receipts'], 2) }}</td>
                        <td class="num">{{ number_format($sub['payments'], 2) }}</td>
                        <td class="num" style="font-weight:600;">{{ number_format($sub['closing_balance'], 2) }}</td>
                        <td class="num">{{ number_format($sub['book_balance'], 2) }}</td>
                        <td class="num">{{ number_format($sub['difference'], 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                @endif
                <tr class="total-row">
                    <td colspan="3">{{ ($currencyMode ?? 'bank_currency') === 'bank_currency' ? 'GRAND TOTAL (USD EQUIVALENT)' : 'GRAND TOTAL (USD)' }}</td>
                    <td class="num">{{ number_format($totalsUSD['opening_balance'] ?? ($totals['opening_balance'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format($totalsUSD['receipts'] ?? ($totals['receipts'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format($totalsUSD['payments'] ?? ($totals['payments'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format($totalsUSD['closing_balance'] ?? ($totals['closing_balance'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format($totalsUSD['book_balance'] ?? ($totals['book_balance'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format($totalsUSD['difference'] ?? ($totals['difference'] ?? 0), 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="footer">
        Bank Book Balance Report &middot; Period: {{ $periodFrom ?? 'N/A' }} ~ {{ $periodTo ?? 'N/A' }}
        &middot; Generated: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        };
    </script>
</body>
</html>
