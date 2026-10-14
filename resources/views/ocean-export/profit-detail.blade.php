<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PROFIT REPORT DETAIL - {{ $shipment->file_no ?? 'MOE-25100001' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            background: #525659;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px; color: #333;
        }
        .top-bar {
            position: sticky; top: 0; left: 0; right: 0; height: 44px;
            background: #323639; display: flex; align-items: center; justify-content: center; gap: 20px; z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .bar-btn {
            background: transparent; border: none; color: #f1f1f1; font-size: 16px; padding: 6px 12px; cursor: pointer; border-radius: 3px; display: flex; align-items: center; gap: 6px;
        }
        .bar-btn:hover { background: rgba(255,255,255,0.15); }
        .doc-wrapper { display: flex; justify-content: center; padding: 30px 15px; }
        .doc-page {
            background: #ffffff; width: 880px; min-height: 1100px; padding: 35px 40px; box-shadow: 0 0 15px rgba(0,0,0,0.5); display: flex; flex-direction: column; gap: 12px;
        }
        .doc-title-main { text-align: center; font-size: 16px; font-weight: bold; color: #000; letter-spacing: 0.5px; margin-bottom: 10px; }
        .meta-table { width: 100%; border-collapse: collapse; border-top: 2px solid #000; border-bottom: 2px solid #000; margin-bottom: 10px; }
        .meta-table td { padding: 4px 6px; font-size: 10px; vertical-align: top; }
        .meta-table td.lbl { font-weight: bold; color: #333; width: 130px; }
        .financial-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .financial-table th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 6px 4px; font-size: 9px; font-weight: bold; text-align: left; background: #f8fafc; }
        .financial-table td { padding: 6px 4px; font-size: 10px; border-bottom: 1px solid #e2e8f0; }
        @media print {
            .top-bar { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .doc-wrapper { padding: 0 !important; }
            .doc-page { box-shadow: none !important; width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body x-data="profitDetailApp()" x-cloak>
    <div class="top-bar">
        <button class="bar-btn" title="Print" @click="window.print()"><i class="fa fa-print"></i></button>
        <button class="bar-btn" title="Send Email" @click="openEmailModal()"><i class="fa fa-envelope"></i> Email</button>
    </div>

    <div class="doc-wrapper">
        <div class="doc-page">
            <div class="doc-title-main">PROFIT REPORT - DETAIL</div>

            <table class="meta-table">
                <tr>
                    <td class="lbl">Booking No. :</td>
                    <td>{{ $shipment->file_no ?? 'MOE-25100001' }}</td>
                    <td class="lbl">MASTER B/L NO. :</td>
                    <td>{{ $shipment->mbl_no ?? 'MBL55555' }}</td>
                </tr>
                <tr>
                    <td class="lbl">P.O.L / ETD :</td>
                    <td>{{ $shipment->portOfLoading->name ?? 'CHITTAGONG' }} / {{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '10-01-2025' }}</td>
                    <td class="lbl">P.O.D / ETA :</td>
                    <td>{{ $shipment->portOfDischarge->name ?? 'HAMBURG' }} / {{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '10-07-2025' }}</td>
                </tr>
            </table>

            <table class="financial-table">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th>PARTY</th>
                        <th>TYPE</th>
                        <th>CURRENCY</th>
                        <th style="text-align:right;">RATE</th>
                        <th style="text-align:right;">QTY</th>
                        <th style="text-align:right;">AMOUNT (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipment->charges as $c)
                    <tr>
                        <td>{{ $c->charge_code ?? 'FREIGHT' }}</td>
                        <td>{{ $c->billTo->name ?? ($c->vendor->name ?? 'TRADE PARTNER') }}</td>
                        <td>{{ $c->type }}</td>
                        <td>{{ $c->currency->code ?? 'USD' }}</td>
                        <td style="text-align:right;">{{ number_format($c->rate, 2) }}</td>
                        <td style="text-align:right;">{{ $c->qty }}</td>
                        <td style="text-align:right; font-weight:bold;">{{ number_format($c->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td>FREIGHT CHARGE</td>
                        <td>3M COMPANY</td>
                        <td>AR</td>
                        <td>USD</td>
                        <td style="text-align:right;">125.00</td>
                        <td style="text-align:right;">1</td>
                        <td style="text-align:right; font-weight:bold;">125.00</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function profitDetailApp() {
            return {
                openEmailModal() { alert('Email feature initiated.'); }
            }
        }
    </script>
</body>
</html>
