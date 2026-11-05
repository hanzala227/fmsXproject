<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent / Local Statement - Print</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter','Open Sans',Arial,sans-serif;font-size:11px;color:#1e293b;padding:20px;background:#fff}
        .header{text-align:center;margin-bottom:20px;border-bottom:2px solid #1e293b;padding-bottom:12px}
        .header h1{font-size:18px;font-weight:700;color:#1e293b;margin-bottom:4px}
        .header .period{font-size:12px;color:#475569}
        .meta{display:flex;justify-content:space-between;margin-bottom:16px;font-size:10px;color:#64748b}
        table{width:100%;border-collapse:collapse;margin-bottom:16px}
        th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:5px 8px;text-align:left;font-size:10px}
        td{padding:4px 8px;border:1px solid #e2e8f0;color:#334155}
        td.num{text-align:right;font-family:'Courier New',monospace}
        .subtotal td{background:#f8fafc;font-weight:700;border-top:2px solid #475569;color:#0f172a}
        .grand-total td{background:#1e293b;color:#fff;font-weight:700;font-size:11px;border:1px solid #0f172a}
        .aging-current{color:#16a34a}.aging-1-30{color:#2563eb}.aging-31-60{color:#d97706}.aging-61-90{color:#ea580c}.aging-90{color:#dc2626;font-weight:700}
        .footer{margin-top:20px;border-top:1px solid #e2e8f0;padding-top:10px;font-size:9px;color:#94a3b8;text-align:center}
        @media print{body{padding:10px}}
    </style>
</head>
<body>
    <div class="header">
        <h1>Agent / Local Statement</h1>
        <div class="period">As of: {{ $asOfDate }}</div>
    </div>
    <div class="meta">
        <span>Generated: {{ now()->format('Y-m-d H:i:s') }}</span>
        <span>Records: {{ count($results) }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:12%;">Invoice No</th>
                <th style="width:9%;">Post Date</th>
                <th style="width:9%;">Due Date</th>
                <th style="width:14%;">Partner</th>
                <th style="width:10%;">Office</th>
                <th style="width:6%;">Cur.</th>
                <th style="width:5%;">Type</th>
                <th style="width:8%;text-align:right;">DR/AR (+)</th>
                <th style="width:8%;text-align:right;">CR/AP (-)</th>
                <th style="width:8%;text-align:right;">Paid Amount</th>
                <th style="width:8%;text-align:right;">Balance</th>
                <th style="width:9%;">Last Paid Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $row)
                <tr>
                    <td>{{ $row['invoice_no'] }}</td>
                    <td>{{ $row['invoice_date'] }}</td>
                    <td>{{ $row['due_date'] }}</td>
                    <td>{{ $row['partner_name'] }}</td>
                    <td>{{ $row['office'] }}</td>
                    <td>{{ $row['currency'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td class="num">{{ number_format($row['dr_amount'], 2) }}</td>
                    <td class="num">{{ number_format($row['cr_amount'], 2) }}</td>
                    <td class="num">{{ number_format($row['paid_amount'], 2) }}</td>
                    <td class="num">{{ number_format($row['balance'], 2) }}</td>
                    <td>{{ $row['last_paid_date'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;color:#94a3b8;">No records found.</td>
                </tr>
            @endforelse
            <tr class="grand-total">
                <td colspan="7">TOTAL</td>
                <td class="num">{{ number_format($summary['total_dr'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($summary['total_cr'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($summary['total_paid'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($summary['total_balance'] ?? 0, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    @if(request('show_aging') === '1')
    <h3 style="font-size:12px;margin:12px 0 8px;color:#1e293b;">Aging Summary</h3>
    <table style="width:auto;">
        <thead>
            <tr>
                <th>Current</th>
                <th>1-30 Days</th>
                <th>31-60 Days</th>
                <th>61-90 Days</th>
                <th>90+ Days</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="num aging-current">{{ number_format($summary['total_current'] ?? 0, 2) }}</td>
                <td class="num aging-1-30">{{ number_format($summary['total_over1_30'] ?? 0, 2) }}</td>
                <td class="num aging-31-60">{{ number_format($summary['total_over31_60'] ?? 0, 2) }}</td>
                <td class="num aging-61-90">{{ number_format($summary['total_over61_90'] ?? 0, 2) }}</td>
                <td class="num aging-90">{{ number_format($summary['total_over90'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="footer">
        Agent / Local Statement &middot; As of: {{ $asOfDate }} &middot; Generated: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <script>window.onload=function(){window.print()}</script>
</body>
</html>
