<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Balance - {{ $periodFrom }} to {{ $periodTo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 11px; color: #1e293b; padding: 20px; background: #fff; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .header h1 { font-size: 16px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .header .period { font-size: 11px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { text-align: left; border-bottom: 2px solid #1e293b; padding: 4px 8px; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        th.num { text-align: right; }
        td { padding: 3px 8px; border-bottom: 1px solid #e2e8f0; }
        td.num { text-align: right; font-family: 'Courier New', monospace; }
        .group-header td { font-weight: 700; font-size: 11px; text-transform: uppercase; background: #f0f9ff; border-bottom: 1px solid #93c5fd; padding: 6px 8px; }
        .subgroup-total td { font-weight: 600; border-top: 1px solid #93c5fd; background: #f0f9ff; }
        .total-row td { font-weight: 700; border-top: 2px solid #1e293b; font-size: 12px; }
        .indent td:nth-child(2) { padding-left: 20px; }
        .summary { margin-top: 20px; border-top: 2px solid #1e293b; padding-top: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .summary-label { font-weight: 600; }
        .summary-value { font-family: 'Courier New', monospace; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>Trial Balance</h1>
        <div class="period">Period: {{ $periodFrom }} to {{ $periodTo }}</div>
    </div>

    @php $isDC = ($format === 'debit_credit'); @endphp
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Account Name</th>
                <th>Group</th>
                @if($isDC)
                    <th class="num">Opening (Dr)</th>
                    <th class="num">Opening (Cr)</th>
                    <th class="num">Debit</th>
                    <th class="num">Credit</th>
                    <th class="num">Closing (Dr)</th>
                    <th class="num">Closing (Cr)</th>
                @else
                    <th class="num">Opening Balance</th>
                    <th class="num">Debit</th>
                    <th class="num">Credit</th>
                    <th class="num">Closing Balance</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if($groupBySub && !empty($grouped))
                @foreach($grouped as $grp)
                    <tr class="group-header">
                        <td colspan="{{ $isDC ? 9 : 7 }}">{{ strtoupper($grp['group']) }} / {{ $grp['sub_group'] }}</td>
                    </tr>

                    @foreach($grp['accounts'] as $acc)
                        <tr class="indent">
                            <td>{{ $acc['code'] }}</td>
                            <td>{{ $acc['name'] }}</td>
                            <td>{{ $acc['group'] }}</td>
                            @if($isDC)
                                <td class="num">{{ number_format($acc['opening_debit'] ?? 0, 2) }}</td>
                                <td class="num">{{ number_format($acc['opening_credit'] ?? 0, 2) }}</td>
                                <td class="num">{{ number_format($acc['debit'], 2) }}</td>
                                <td class="num">{{ number_format($acc['credit'], 2) }}</td>
                                <td class="num">{{ number_format($acc['closing_debit'] ?? 0, 2) }}</td>
                                <td class="num">{{ number_format($acc['closing_credit'] ?? 0, 2) }}</td>
                            @else
                                <td class="num">{{ number_format($acc['opening_balance'], 2) }}</td>
                                <td class="num">{{ number_format($acc['debit'], 2) }}</td>
                                <td class="num">{{ number_format($acc['credit'], 2) }}</td>
                                <td class="num">{{ number_format($acc['closing_balance'], 2) }}</td>
                            @endif
                        </tr>
                    @endforeach

                    <tr class="subgroup-total">
                        <td colspan="2">Subtotal {{ $grp['sub_group'] }}</td>
                        <td></td>
                        @if($isDC)
                            <td class="num">{{ number_format($grp['opening_debit'] ?? 0, 2) }}</td>
                            <td class="num">{{ number_format($grp['opening_credit'] ?? 0, 2) }}</td>
                            <td class="num">{{ number_format($grp['debit'], 2) }}</td>
                            <td class="num">{{ number_format($grp['credit'], 2) }}</td>
                            <td class="num">{{ number_format($grp['closing_debit'] ?? 0, 2) }}</td>
                            <td class="num">{{ number_format($grp['closing_credit'] ?? 0, 2) }}</td>
                        @else
                            <td class="num">{{ number_format($grp['opening_balance'], 2) }}</td>
                            <td class="num">{{ number_format($grp['debit'], 2) }}</td>
                            <td class="num">{{ number_format($grp['credit'], 2) }}</td>
                            <td class="num">{{ number_format($grp['closing_balance'], 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                @foreach($accounts as $acc)
                    <tr class="indent">
                        <td>{{ $acc['code'] }}</td>
                        <td>{{ $acc['name'] }}</td>
                        <td>{{ $acc['group'] }}</td>
                        @if($isDC)
                            <td class="num">{{ number_format($acc['opening_debit'] ?? 0, 2) }}</td>
                            <td class="num">{{ number_format($acc['opening_credit'] ?? 0, 2) }}</td>
                            <td class="num">{{ number_format($acc['debit'], 2) }}</td>
                            <td class="num">{{ number_format($acc['credit'], 2) }}</td>
                            <td class="num">{{ number_format($acc['closing_debit'] ?? 0, 2) }}</td>
                            <td class="num">{{ number_format($acc['closing_credit'] ?? 0, 2) }}</td>
                        @else
                            <td class="num">{{ number_format($acc['opening_balance'], 2) }}</td>
                            <td class="num">{{ number_format($acc['debit'], 2) }}</td>
                            <td class="num">{{ number_format($acc['credit'], 2) }}</td>
                            <td class="num">{{ number_format($acc['closing_balance'], 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            @endif

            <tr class="total-row">
                <td colspan="3">GRAND TOTAL</td>
                @if($isDC)
                    <td class="num">{{ number_format($summary['total_opening_debit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_opening_credit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_debit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_credit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_closing_debit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_closing_credit'] ?? 0, 2) }}</td>
                @else
                    <td class="num">{{ number_format($summary['total_opening_balance'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_debit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_credit'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($summary['total_closing_balance'] ?? 0, 2) }}</td>
                @endif
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Debit:</span>
            <span class="summary-value">{{ number_format($summary['total_debit'] ?? 0, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Credit:</span>
            <span class="summary-value">{{ number_format($summary['total_credit'] ?? 0, 2) }}</span>
        </div>
        <div class="summary-row" style="border-top:1px solid #1e293b;padding-top:4px;margin-top:4px;">
            <span class="summary-label">Balanced:</span>
            <span class="summary-value">{{ ($summary['is_balanced'] ?? false) ? 'Yes' : 'No' }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Trial Balance Report &middot; Generated {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>This is a computer-generated report. No signature required.</p>
    </div>

    <script>window.onload = function() { window.print(); };</script>
</body>
</html>
