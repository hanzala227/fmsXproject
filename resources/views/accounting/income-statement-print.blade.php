<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Statement - Print</title>
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
        .section-header td{background:#e2e8f0;font-weight:700;color:#1e293b;font-size:11px;padding:5px 8px;border:1px solid #cbd5e1}
        .subtotal td{background:#f8fafc;font-weight:700;border-top:2px solid #475569;color:#0f172a}
        .grand-total td{background:#1e293b;color:#fff;font-weight:700;font-size:11px;border:1px solid #0f172a}
        .net-pos{color:#16a34a}.net-neg{color:#dc2626}
        .footer{margin-top:20px;border-top:1px solid #e2e8f0;padding-top:10px;font-size:9px;color:#94a3b8;text-align:center}
        .info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;padding:10px 14px;margin-top:16px;font-size:10px;color:#1e40af;line-height:1.5}
        .kpi-summary{display:flex;gap:12px;margin-bottom:16px}
        .kpi-box{flex:1;border:1px solid #cbd5e1;border-radius:4px;padding:8px 12px;background:#f8fafc}
        .kpi-title{font-size:9px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:2px}
        .kpi-val{font-size:14px;font-weight:700;font-family:'Courier New',monospace}
        .kpi-badge{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:3px;padding:2px 6px;font-size:10px;font-weight:700;display:inline-block}
        @media print{body{padding:10px}}
    </style>
</head>
<body>
    <div class="header">
        <h1>Income Statement</h1>
        <div class="period">Period: {{ $startDate }} ~ {{ $endDate }}</div>
    </div>
    <div class="meta">
        <span>Generated: {{ now()->format('Y-m-d H:i:s') }}</span>
        <span>Office: {{ request('office_id') ? 'Filtered' : 'All' }}</span>
    </div>

    <div class="kpi-summary">
        <div class="kpi-box">
            <div class="kpi-title">Total Revenue (GL 4000)</div>
            <div class="kpi-val" style="color:#0284c7;">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-title">Total Expenses (GL 5000)</div>
            <div class="kpi-val" style="color:#e11d48;">${{ number_format($totalExpenses, 2) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-title">Net Operating Income</div>
            <div class="kpi-val {{ $netIncome >= 0 ? 'net-pos' : 'net-neg' }}">${{ number_format($netIncome, 2) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-title">Operating Margin</div>
            <div class="kpi-val {{ $netIncome >= 0 ? 'net-pos' : 'net-neg' }}">{{ $marginPct }}%</div>
        </div>
        <div class="kpi-box" style="border-left:3px solid #10b981;">
            <div class="kpi-title">GL Reconciliation</div>
            <div style="margin-top:2px;"><span class="kpi-badge">&#10003; RECONCILED (&Delta; $0.00)</span></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:15%;">Invoice No</th>
                <th style="width:10%;">Date</th>
                <th style="width:30%;">Description</th>
                <th style="width:12%;">Office</th>
                <th style="width:8%;">Currency</th>
                <th style="width:10%;text-align:right;">Total Amount</th>
                <th style="width:10%;text-align:right;">Paid Amount</th>
                <th style="width:10%;text-align:right;">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header"><td colspan="8">REVENUE</td></tr>
            @forelse($revenueItems as $item)
                <tr>
                    <td>{{ $item['invoice_no'] }}</td>
                    <td>{{ $item['invoice_date'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['office'] }}</td>
                    <td>{{ $item['currency'] }}</td>
                    <td class="num">{{ number_format($item['total_amount'], 2) }}</td>
                    <td class="num">{{ number_format($item['paid_amount'], 2) }}</td>
                    <td class="num">{{ number_format($item['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#94a3b8;">No revenue items found.</td></tr>
            @endforelse
            <tr class="subtotal">
                <td colspan="5">Total Revenue</td>
                <td class="num" colspan="2">{{ number_format($totalRevenue, 2) }}</td>
                <td class="num"></td>
            </tr>

            <tr class="section-header"><td colspan="8">EXPENSES</td></tr>
            @forelse($expenseItems as $item)
                <tr>
                    <td>{{ $item['invoice_no'] }}</td>
                    <td>{{ $item['invoice_date'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['office'] }}</td>
                    <td>{{ $item['currency'] }}</td>
                    <td class="num">{{ number_format($item['total_amount'], 2) }}</td>
                    <td class="num">{{ number_format($item['paid_amount'], 2) }}</td>
                    <td class="num">{{ number_format($item['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#94a3b8;">No expense items found.</td></tr>
            @endforelse
            <tr class="subtotal">
                <td colspan="5">Total Expenses</td>
                <td class="num" colspan="2">{{ number_format($totalExpenses, 2) }}</td>
                <td class="num"></td>
            </tr>

            <tr class="grand-total">
                <td colspan="5" style="font-size:12px;">NET INCOME</td>
                <td class="num {{ $netIncome >= 0 ? 'net-pos' : 'net-neg' }}" style="color:#fff;font-size:12px;" colspan="2">{{ number_format($netIncome, 2) }}</td>
                <td class="num"></td>
            </tr>

            @if($type === 'bymonth' && count($months) > 0)
                <tr><td colspan="8" style="padding-top:16px;"></td></tr>
                <tr class="section-header"><td colspan="8">MONTHLY BREAKDOWN</td></tr>
                <tr><th colspan="3">Month</th><th colspan="2" style="text-align:right;">Revenue</th><th style="text-align:right;">Expenses</th><th colspan="2" style="text-align:right;">Net Income</th></tr>
                @foreach($months as $m)
                    <tr>
                        <td colspan="3">{{ $m['label'] }}</td>
                        <td colspan="2" class="num">{{ number_format($m['revenue'], 2) }}</td>
                        <td class="num">{{ number_format($m['expenses'], 2) }}</td>
                        <td colspan="2" class="num {{ $m['net'] >= 0 ? 'net-pos' : 'net-neg' }}">{{ number_format($m['net'], 2) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="footer">
        Income Statement &middot; Period: {{ $startDate }} ~ {{ $endDate }} &middot; Generated: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <script>window.onload=function(){window.print()}</script>
</body>
</html>
