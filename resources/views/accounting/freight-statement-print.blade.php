<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freight Statement - Print</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter','Open Sans',Arial,sans-serif;font-size:11px;color:#1e293b;padding:20px;background:#fff}
        .company{text-align:center;margin-bottom:16px;padding-bottom:14px;border-bottom:2px solid #1e293b}
        .company h1{font-size:18px;font-weight:700;color:#2563eb;margin-bottom:4px;letter-spacing:1px}
        .company .addr{font-size:10px;color:#475569;line-height:1.5}
        .title{text-align:center;margin:14px 0;font-size:15px;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:1.5px}
        .partner-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:10px 14px;margin-bottom:14px;max-width:400px}
        .partner-box .lbl{font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px}
        .partner-box .nm{font-size:13px;font-weight:700;color:#1e293b}
        .partner-box .dt{font-size:10px;color:#475569;margin-top:2px;line-height:1.4}
        .meta{display:flex;gap:30px;margin-bottom:14px;font-size:11px;color:#475569}
        .meta span{font-weight:700;color:#1e293b}
        table{width:100%;border-collapse:collapse;margin-bottom:16px}
        th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:5px 8px;text-align:left;font-size:10px}
        td{padding:4px 8px;border:1px solid #e2e8f0;color:#334155;font-size:11px}
        td.num{text-align:right;font-family:'Courier New',monospace}
        .grand-total td{background:#1e293b;color:#fff;font-weight:700;font-size:11px;border:1px solid #0f172a}
        .currency-sum{margin:12px 0}
        .currency-sum table{width:auto;border-collapse:collapse;font-size:11px}
        .currency-sum th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:4px 10px;font-size:10px}
        .currency-sum td{padding:4px 10px;border:1px solid #e2e8f0;font-family:'Courier New',monospace;text-align:right}
        .currency-sum td.cl{text-align:left;font-weight:600;background:#f8fafc}
        .footer{margin-top:20px;border-top:1px solid #e2e8f0;padding-top:10px;font-size:9px;color:#94a3b8;text-align:center}
        @media print{body{padding:10px}}
    </style>
</head>
<body>
    <div class="company">
        <h1>FreightX</h1>
        <div class="addr">12F, No.186, Sec. 1, Fu-Xing S. Rd., Da-An District,<br>Taipei City 106, Taiwan R.O.C.<br>TEL: 886-2-2708-5068 &nbsp; FAX: 886-2-2708-5067</div>
    </div>

    <div class="title">FREIGHT STATEMENT</div>

    @if($partnerInfo)
    <div class="partner-box">
        <div class="lbl">TO:</div>
        <div class="nm">{{ $partnerInfo['name'] ?? '' }}</div>
        @php
            $addrParts = array_filter([$partnerInfo['address'] ?? '', $partnerInfo['city'] ?? '', $partnerInfo['country'] ?? '']);
        @endphp
        @if(count($addrParts))
        <div class="dt">{{ implode(', ', $addrParts) }}</div>
        @endif
    </div>
    @endif

    <div class="meta">
        <span>STATEMENT DATE:</span> {{ $asOfDate }}
        <span style="margin-left:20px;">STATEMENT PERIOD:</span> {{ $asOfDate }} (As of Today)
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:10%;">ETD</th>
                <th style="width:14%;">Booking No.</th>
                @if($showBookingNumber === '1')
                <th style="width:12%;">Booking No.</th>
                @endif
                <th style="width:14%;">MB/L No.</th>
                <th style="width:14%;">HB/L No.</th>
                <th style="width:6%;">CUR.</th>
                <th style="width:10%;text-align:right;">DRI/AR(+)</th>
                <th style="width:10%;text-align:right;">CRI/AP(-)</th>
                <th style="width:10%;text-align:right;">Paid</th>
                <th style="width:10%;text-align:right;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $row)
                <tr>
                    <td>{{ $row['etd'] }}</td>
                    <td>{{ $row['invoice_no'] }}</td>
                    @if($showBookingNumber === '1')
                    <td>{{ $row['booking_no'] }}</td>
                    @endif
                    <td>{{ $row['mbl_no'] }}</td>
                    <td>{{ $row['hbl_no'] }}</td>
                    <td>{{ $row['currency'] }}</td>
                    <td class="num">{{ $row['dr_amount'] ? number_format($row['dr_amount'], 2) : '' }}</td>
                    <td class="num">{{ $row['cr_amount'] ? number_format($row['cr_amount'], 2) : '' }}</td>
                    <td class="num">{{ $row['paid_amount'] ? number_format($row['paid_amount'], 2) : '' }}</td>
                    <td class="num">{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showBookingNumber === '1' ? 10 : 9 }}" style="text-align:center;color:#94a3b8;padding:30px;">No records found.</td>
                </tr>
            @endforelse
            <tr class="grand-total">
                <td colspan="{{ $showBookingNumber === '1' ? 5 : 4 }}" style="text-align:right;">TOTAL (BALANCE)</td>
                <td></td>
                <td class="num">{{ number_format($summary['total_dr'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($summary['total_cr'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($summary['total_paid'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($summary['total_balance'] ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if(count($currencyTotals) > 0)
    <div class="currency-sum">
        <table>
            <thead>
                <tr><th style="text-align:left;">Currency</th><th>DRI/AR(+)</th><th>CRI/AP(-)</th><th>Paid</th><th>Balance</th></tr>
            </thead>
            <tbody>
                @foreach($currencyTotals as $ct)
                <tr>
                    <td class="cl">{{ $ct['currency'] }}</td>
                    <td>{{ number_format($ct['dr'], 2) }}</td>
                    <td>{{ number_format($ct['cr'], 2) }}</td>
                    <td>{{ number_format($ct['paid'], 2) }}</td>
                    <td>{{ number_format($ct['balance'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Freight Statement &middot; As of: {{ $asOfDate }} &middot; Generated: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <script>window.onload=function(){window.print()}</script>
</body>
</html>
