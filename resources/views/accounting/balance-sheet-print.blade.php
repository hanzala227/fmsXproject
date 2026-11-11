<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Balance Sheet - {{ $asOfDate }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 11px; color: #1e293b; padding: 20px; background: #fff; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .header h1 { font-size: 16px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .header .subtitle { font-size: 11px; color: #64748b; font-style: italic; }
        .header .date { font-size: 11px; color: #475569; margin-top: 4px; }
        .header .currency { font-size: 10px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { text-align: left; border-bottom: 2px solid #1e293b; padding: 4px 8px; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        th:last-child { text-align: right; }
        td { padding: 3px 8px; border-bottom: 1px solid #e2e8f0; }
        td.num { text-align: right; font-family: 'Courier New', monospace; }
        .section-header td { font-weight: 700; font-size: 12px; text-transform: uppercase; background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 6px 8px; }
        .group-header td { font-weight: 600; font-style: italic; color: #475569; padding-left: 16px; }
        .group-total td { font-weight: 600; border-top: 1px solid #93c5fd; background: #f0f9ff; padding-left: 16px; }
        .total-row td { font-weight: 700; border-top: 2px solid #1e293b; font-size: 12px; }
        .indent td:first-child { padding-left: 24px; }
        .summary { margin-top: 20px; border-top: 2px solid #1e293b; padding-top: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .summary-label { font-weight: 600; }
        .summary-value { font-family: 'Courier New', monospace; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Management Balance Sheet</h1>
        <div class="subtitle">(based on invoices and payments, unaudited)</div>
        <div class="date">As of {{ $asOfDate }}</div>
        <div class="currency">Currency: USD Equivalent</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Amount (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sections as $section)
                <tr class="section-header">
                    <td colspan="2">{{ strtoupper($section['title']) }}</td>
                </tr>

                @foreach($section['groups'] as $group)
                    <tr class="group-header">
                        <td>{{ $group['name'] }}</td>
                        <td></td>
                    </tr>

                    @foreach($group['lines'] as $line)
                        <tr class="indent" @if(!empty($line['is_alert'])) style="background:#fef2f2;color:#dc2626;font-weight:600;" @elseif(!empty($line['is_sub'])) style="background:#f0f9ff;color:#1e40af;" @endif>
                            <td>
                                {{ $line['label'] }}
                                @if(!empty($line['detail'])) <span style="color:#64748b;font-size:10px;">({{ $line['detail'] }})</span>@endif
                            </td>
                            <td class="num">{{ number_format($line['amount'], 2) }}</td>
                        </tr>
                    @endforeach

                    <tr class="group-total">
                        <td>Total {{ $group['name'] }}</td>
                        <td class="num">{{ number_format($group['total'], 2) }}</td>
                    </tr>
                @endforeach

                <tr class="total-row">
                    <td>Total {{ $section['title'] }}</td>
                    <td class="num">{{ number_format($section['total'], 2) }}</td>
                </tr>
            @endforeach

            <tr style="background:#f8fafc;border-top:2px solid #0f172a;">
                <td style="font-weight:700;font-size:12px;">TOTAL LIABILITIES & EQUITY</td>
                <td class="num" style="font-weight:700;font-size:12px;">{{ number_format($summary['total_liabilities_and_equity'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Assets (USD):</span>
            <span class="summary-value">{{ number_format($summary['total_assets'] ?? 0, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Liabilities (USD):</span>
            <span class="summary-value">{{ number_format($summary['total_liabilities'] ?? 0, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Equity (USD):</span>
            <span class="summary-value">{{ number_format($summary['total_equity'] ?? 0, 2) }}</span>
        </div>
        <div class="summary-row" style="border-top:1px solid #1e293b;padding-top:4px;margin-top:4px;">
            <span class="summary-label">Total Liabilities & Equity (USD):</span>
            <span class="summary-value">{{ number_format($summary['total_liabilities_and_equity'] ?? 0, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Equation Variance (USD):</span>
            <span class="summary-value" style="{{ abs($summary['variance'] ?? 0) > 0.01 ? 'color:#dc2626;font-weight:700;' : 'color:#16a34a;' }}">${{ number_format($summary['variance'] ?? 0, 2) }}</span>
        </div>
    </div>

    @if(!empty($reconciliationChecks))
    <div style="margin-top:20px;">
        <div style="font-weight:700;margin-bottom:8px;font-size:11px;">SUBLEDGER RECONCILIATION CHECKS:</div>
        <table style="width:100%;border:1px solid #cbd5e1;font-size:10px;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:4px 6px;">Check</th>
                    <th style="text-align:right;padding:4px 6px;">BS Amount</th>
                    <th style="text-align:right;padding:4px 6px;">Ref Amount</th>
                    <th style="text-align:right;padding:4px 6px;">Variance</th>
                    <th style="text-align:center;padding:4px 6px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reconciliationChecks as $c)
                <tr>
                    <td style="padding:4px 6px;">{{ $c['label'] }}</td>
                    <td class="num" style="padding:4px 6px;">${{ number_format($c['bs_amount'], 2) }}</td>
                    <td class="num" style="padding:4px 6px;">${{ number_format($c['ref_amount'], 2) }}</td>
                    <td class="num" style="padding:4px 6px;{{ !$c['is_match'] ? 'color:#dc2626;font-weight:700;' : '' }}">${{ number_format($c['variance'], 2) }}</td>
                    <td style="text-align:center;padding:4px 6px;font-weight:700;color:{{ $c['is_match'] ? '#16a34a' : '#dc2626' }};">{{ $c['is_match'] ? 'PASS' : 'VARIANCE' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Management Balance Sheet &middot; Generated {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>This is an unaudited subledger-based management report. No balancing plugs have been inserted.</p>
    </div>

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
