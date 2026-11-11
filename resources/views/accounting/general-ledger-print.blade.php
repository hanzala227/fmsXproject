<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Ledger Report - {{ $periodFrom }} to {{ $periodTo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Open Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; padding: 24px; background: #fff; line-height: 1.4; }
        .no-print { text-align: right; margin-bottom: 12px; }
        .no-print button { padding: 6px 16px; cursor: pointer; background: #3b82f6; color: #fff; border: 1px solid #2563eb; border-radius: 2px; font-size: 11px; font-weight: 600; margin-left: 6px; }
        .no-print button:hover { background: #2563eb; }
        .company { text-align: center; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 2px solid #1e293b; }
        .company h1 { font-size: 20px; font-weight: 700; color: #2563eb; margin-bottom: 2px; letter-spacing: 1px; }
        .title { text-align: center; margin: 10px 0 4px; font-size: 15px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; }
        .subtitle { text-align: center; font-size: 11px; color: #64748b; margin-bottom: 18px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
        th { background: #475569; color: #fff; font-weight: 600; border: 1px solid #334155; padding: 5px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        th.num { text-align: right; }
        td { padding: 4px 8px; border: 1px solid #e2e8f0; color: #334155; }
        td.num { text-align: right; font-family: 'Courier New', monospace; font-weight: 500; }
        tr:nth-child(even) td { background: #fafafa; }

        .account-card { margin-bottom: 16px; border: 1px solid #cbd5e1; border-radius: 2px; overflow: hidden; page-break-inside: avoid; }
        .account-banner { background: #f1f5f9; border-bottom: 2px solid #64748b; padding: 6px 10px; display: flex; justify-content: space-between; align-items: center; }
        .account-banner .name { font-weight: 700; font-size: 12px; color: #0f172a; text-transform: uppercase; }
        .account-banner .badge { font-size: 10px; padding: 2px 6px; background: #e2e8f0; border-radius: 2px; color: #475569; font-weight: 600; text-transform: uppercase; }

        .opening-row td { background: #f8fafc !important; font-weight: 600; color: #475569; }
        .closing-row td { background: #f1f5f9 !important; font-weight: 700; border-top: 2px solid #475569; color: #0f172a; }
        .total-row td { background: #1e293b !important; color: #fff !important; font-weight: 700; font-size: 11px; border: 1px solid #0f172a; }

        .compact-account { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 2px; padding: 6px 10px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; }
        .compact-account .title { font-size: 11px; font-weight: 700; color: #334155; margin: 0; text-transform: uppercase; }
        .compact-account .metrics { color: #64748b; font-size: 10px; }
        .compact-account .metrics strong { color: #0f172a; font-family: 'Courier New', monospace; font-size: 11px; }
        .compact-account .note { font-style: italic; color: #94a3b8; font-size: 10px; margin-left: 8px; }

        .summary-card-box { display: flex; justify-content: space-between; gap: 12px; margin-top: 20px; page-break-inside: avoid; }
        .summary-pill { flex: 1; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; background: #f8fafc; }
        .summary-pill.dr { border-left: 3px solid #3b82f6; }
        .summary-pill.cr { border-left: 3px solid #ef4444; }
        .summary-pill.bal { border-left: 3px solid #22c55e; }
        .summary-pill .label { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; }
        .summary-pill .val { font-size: 13px; font-weight: 700; color: #0f172a; font-family: 'Courier New', monospace; margin-top: 3px; }

        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }

        @media print {
            body { padding: 12px; }
            .no-print { display: none; }
            .account-card { page-break-inside: avoid; }
        }
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

    <div class="title">General Ledger Report</div>
    <div class="subtitle">
        Period: {{ $periodFrom ?? 'N/A' }} ~ {{ $periodTo ?? 'N/A' }}
        &middot; Report Type: {{ strtoupper($reportType ?? 'SUMMARY') }}
        &middot; All Amounts in USD (Base Currency)
    </div>

    @if(strtoupper($reportType ?? '') === 'SUMMARY')
        {{-- ═══════════════════════ SUMMARY MODE ═══════════════════════ --}}
        <table>
            <thead>
                <tr>
                    <th style="width:10%;">Code</th>
                    <th style="width:28%;">Account Name</th>
                    <th style="width:10%;">Type</th>
                    <th class="num" style="width:17%;">Opening Balance</th>
                    <th class="num" style="width:12%;">Debit</th>
                    <th class="num" style="width:12%;">Credit</th>
                    <th class="num" style="width:17%;">Closing Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                    <tr>
                        <td><strong>{{ $account['code'] }}</strong></td>
                        <td>{{ $account['name'] }}</td>
                        <td><span style="text-transform:uppercase;font-size:10px;font-weight:600;color:#64748b;">{{ $account['type'] ?? 'debit' }}</span></td>
                        <td class="num">{{ number_format(abs($account['opening_balance']), 2) }} <span style="font-size:9px;color:#64748b;">{{ $account['opening_type'] ?? '' }}</span></td>
                        <td class="num" style="color:#2563eb;">{{ $account['total_debit'] > 0 ? number_format($account['total_debit'], 2) : '-' }}</td>
                        <td class="num" style="color:#dc2626;">{{ $account['total_credit'] > 0 ? number_format($account['total_credit'], 2) : '-' }}</td>
                        <td class="num"><strong>{{ number_format(abs($account['closing_balance']), 2) }}</strong> <span style="font-size:9px;color:#64748b;">{{ $account['closing_type'] ?? '' }}</span></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">TOTAL DEBITS &amp; CREDITS (USD)</td>
                    <td class="num">Dr {{ number_format($summary['total_opening_debit'] ?? 0, 2) }}<br><span style="font-size:9px;opacity:0.8;">Cr {{ number_format($summary['total_opening_credit'] ?? 0, 2) }}</span></td>
                    <td class="num">{{ number_format($summary['grand_total_debit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['grand_total_credit'] ?? 0, 2) }}</td>
                    <td class="num">Dr {{ number_format($summary['total_closing_debit'] ?? 0, 2) }}<br><span style="font-size:9px;opacity:0.8;">Cr {{ number_format($summary['total_closing_credit'] ?? 0, 2) }}</span></td>
                </tr>
            </tbody>
        </table>
    @else
        {{-- ═══════════════════════ DETAIL MODE ═══════════════════════ --}}
        @foreach($accounts as $account)
            @if(!empty($account['transactions']) && count($account['transactions']) > 0)
                <div class="account-card">
                    <div class="account-banner">
                        <span class="name">[{{ $account['code'] }}] {{ $account['name'] }}</span>
                        <span class="badge">{{ strtoupper($account['type'] ?? 'debit') }} NORMAL</span>
                    </div>
                    <table style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th style="width:12%;">Date</th>
                                <th style="width:14%;">Reference</th>
                                <th style="width:34%;">Description</th>
                                <th class="num" style="width:13%;">Debit</th>
                                <th class="num" style="width:13%;">Credit</th>
                                <th class="num" style="width:14%;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="opening-row">
                                <td colspan="3"><em>Opening Balance</em></td>
                                <td class="num">-</td>
                                <td class="num">-</td>
                                <td class="num"><strong>{{ number_format(abs($account['opening_balance']), 2) }}</strong> <span style="font-size:9px;color:#64748b;">{{ $account['opening_type'] ?? '' }}</span></td>
                            </tr>
                            @foreach($account['transactions'] as $txn)
                                <tr>
                                    <td>{{ $txn['date'] }}</td>
                                    <td>{{ $txn['reference'] }}</td>
                                    <td>{{ $txn['description'] }}</td>
                                    <td class="num" style="color:#2563eb;">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '' }}</td>
                                    <td class="num" style="color:#dc2626;">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '' }}</td>
                                    <td class="num">{{ number_format(abs($txn['running_balance']), 2) }} <span style="font-size:9px;color:#64748b;">{{ $txn['balance_type'] ?? '' }}</span></td>
                                </tr>
                            @endforeach
                            <tr class="closing-row">
                                <td colspan="3">Closing Balance &middot; {{ count($account['transactions']) }} transaction(s)</td>
                                <td class="num">{{ number_format($account['total_debit'], 2) }}</td>
                                <td class="num">{{ number_format($account['total_credit'], 2) }}</td>
                                <td class="num"><strong>{{ number_format(abs($account['closing_balance']), 2) }}</strong> <span style="font-size:9px;color:#64748b;">{{ $account['closing_type'] ?? '' }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Compact single line for zero-activity accounts to prevent cluttered page --}}
                <div class="compact-account">
                    <span class="title">[{{ $account['code'] }}] {{ $account['name'] }}</span>
                    <div class="metrics">
                        Opening: <strong>${{ number_format(abs($account['opening_balance']), 2) }} {{ $account['opening_type'] ?? '' }}</strong>
                        &nbsp;|&nbsp;
                        Debit: <strong>$0.00</strong>
                        &nbsp;|&nbsp;
                        Credit: <strong>$0.00</strong>
                        &nbsp;|&nbsp;
                        Closing: <strong>${{ number_format(abs($account['closing_balance']), 2) }} {{ $account['closing_type'] ?? '' }}</strong>
                        <span class="note">(No activity in period)</span>
                    </div>
                </div>
            @endif
        @endforeach

        <table>
            <tbody>
                <tr class="total-row">
                    <td colspan="3" style="width:60%;">GRAND TOTAL (USD)</td>
                    <td class="num" style="width:13%;">{{ number_format($summary['grand_total_debit'] ?? 0, 2) }}</td>
                    <td class="num" style="width:13%;">{{ number_format($summary['grand_total_credit'] ?? 0, 2) }}</td>
                    <td class="num" style="width:14%;">
                        Dr {{ number_format($summary['total_closing_debit'] ?? 0, 2) }}<br>
                        <span style="font-size:9px;opacity:0.8;">Cr {{ number_format($summary['total_closing_credit'] ?? 0, 2) }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="summary-card-box">
        <div class="summary-pill dr">
            <div class="label">Total Debit Movement</div>
            <div class="val">${{ number_format($summary['grand_total_debit'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-pill cr">
            <div class="label">Total Credit Movement</div>
            <div class="val">${{ number_format($summary['grand_total_credit'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-pill bal">
            <div class="label">Audit Status (3-Way Verification)</div>
            <div class="val" style="color: {{ ($summary['is_balanced'] ?? false) ? '#16a34a' : '#dc2626' }};">
                {{ ($summary['is_balanced'] ?? false) ? 'BALANCED' : 'UNBALANCED' }}
                @if(!($summary['is_balanced'] ?? false))
                    <span style="font-size:10px;color:#dc2626;font-weight:normal;">(Variance: ${{ number_format($summary['closing_variance'] ?? 0, 2) }})</span>
                @endif
            </div>
        </div>
    </div>

    <div class="footer">
        <p>FreightX Financial Statement &middot; General Ledger &middot; Period: {{ $periodFrom }} to {{ $periodTo }} &middot; Generated {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>