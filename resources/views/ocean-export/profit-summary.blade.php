<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PROFIT BY MASTER B/L - {{ $shipment->file_no ?? 'MOE-25100001' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: #525659;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .top-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            height: 44px;
            background: #323639;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        .bar-btn {
            background: transparent;
            border: none;
            color: #f1f1f1;
            font-size: 16px;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .bar-btn:hover {
            background: rgba(255,255,255,0.15);
        }

        .top-select {
            background: #fff;
            color: #333;
            border: 1px solid #ccc;
            padding: 3px 12px;
            border-radius: 3px;
            font-size: 12px;
            outline: none;
        }

        .doc-wrapper {
            display: flex;
            justify-content: center;
            padding: 30px 15px;
        }

        .doc-page {
            background: #ffffff;
            width: 880px;
            min-height: 1100px;
            padding: 35px 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .doc-title-main {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .sub-header-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: bold;
            color: #444;
            margin-bottom: 5px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 10px;
            vertical-align: top;
        }
        .meta-table td.lbl {
            font-weight: bold;
            color: #333;
            width: 130px;
        }

        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .financial-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: left;
            background: #f8fafc;
        }
        .financial-table td {
            padding: 6px 4px;
            font-size: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .inv-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .currency-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 10px;
        }
        .currency-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 10px;
        }
        .currency-table td.lbl {
            font-weight: bold;
            background: #fff;
            width: 80px;
            text-align: center;
        }
        .currency-table td.val {
            text-align: right;
            font-weight: bold;
            width: 120px;
        }

        .profit-box-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .profit-box {
            width: 300px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .profit-box td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: bold;
        }
        .profit-box td.title {
            text-align: right;
            background: #fff;
        }
        .profit-box td.val {
            text-align: right;
            width: 120px;
        }

        @media print {
            .top-bar { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .doc-wrapper { padding: 0 !important; }
            .doc-page { box-shadow: none !important; width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body x-data="profitSummaryApp()" x-cloak>

    <div class="top-bar">
        <button class="bar-btn" title="Download PDF" @click="window.print()"><i class="fa fa-file-pdf-o"></i></button>
        <button class="bar-btn" title="Print" @click="window.print()"><i class="fa fa-print"></i></button>
        <button class="bar-btn" title="Send Email" @click="openEmailModal()"><i class="fa fa-envelope"></i> Email</button>
        
        <select class="top-select">
            <option>English</option>
        </select>
    </div>

    <div class="doc-wrapper">
        <div class="doc-page">
            
            <div class="doc-title-main">PROFIT BY MASTER B/L</div>

            <div class="sub-header-row">
                <div>
                    <div>POST DATE : {{ date('m-d-Y') }}</div>
                    <div>PRINT OPTION : SUMMARY</div>
                </div>
                <div style="text-align:right;">
                    <div>OCEAN EXPORT</div>
                    <div>{{ date('m-d-Y') }}</div>
                </div>
            </div>

            <table class="meta-table">
                <tr>
                    <td class="lbl">CONTAINER COUNT :</td>
                    <td><strong>{{ $shipment->containers->count() ? $shipment->containers->count() . ' Container(s)' : '40GHC X 2' }}</strong></td>
                    <td class="lbl">P.O.R :</td>
                    <td>{{ $shipment->placeOfReceipt->name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="lbl">AGENT NAME :</td>
                    <td><strong>{{ $shipment->overseaAgent->name ?? '"BIRLISHEN ULAG ULGAMY" ECONOMIC SOCIETY' }}</strong></td>
                    <td class="lbl">P.O.L / ETD :</td>
                    <td>{{ $shipment->portOfLoading->name ?? 'CHITTAGONG' }} / {{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '10-01-2025' }}</td>
                </tr>
                <tr>
                    <td class="lbl">D.E.L :</td>
                    <td>{{ $shipment->placeOfDelivery->name ?? '' }}</td>
                    <td class="lbl">P.O.D / ETA :</td>
                    <td>{{ $shipment->portOfDischarge->name ?? 'HAMBURG' }} / {{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '10-07-2025' }}</td>
                </tr>
                <tr>
                    <td class="lbl">CONTAINER NO.:</td>
                    <td>{{ $shipment->containers->pluck('container_no')->filter()->implode(', ') ?: 'MSDU758988, MSDU758989' }}</td>
                    <td class="lbl">OP :</td>
                    <td>{{ $shipment->operator->name ?? 'DEMO_925' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Booking No. :</td>
                    <td>{{ $shipment->file_no ?? 'MOE-25100001' }}</td>
                    <td class="lbl">SHIP MODE :</td>
                    <td>{{ $shipment->ship_mode ?? 'FCL' }}</td>
                </tr>
                <tr>
                    <td class="lbl">MASTER B/L NO. :</td>
                    <td>{{ $shipment->mbl_no ?? 'MBL55555' }}</td>
                    <td class="lbl">SERVICE TERM :</td>
                    <td>CY - CY</td>
                </tr>
            </table>

            <table class="financial-table">
                <thead>
                    <tr>
                        <th style="width:35px;">TYPE</th>
                        <th style="width:140px;">B/L NO<br>INV NO</th>
                        <th style="width:200px;">COMPANY</th>
                        <th style="width:85px;">INV POST DATE</th>
                        <th style="width:45px;">CUR.</th>
                        <th style="width:80px; text-align:right;">A/R AMOUNT</th>
                        <th style="width:80px; text-align:right;">A/P AMOUNT</th>
                        <th style="width:80px; text-align:right;">D/C AMOUNT</th>
                        <th style="width:100px; text-align:right;">AMOUNT(USD)</th>
                        <th style="width:100px; text-align:right;">BALANCE(USD)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipment->charges as $c)
                    <tr>
                        <td>M</td>
                        <td>
                            <strong>{{ $shipment->mbl_no ?? 'MBL55555' }}</strong><br>
                            <a href="#" class="inv-link">{{ $c->invoice_no ?? 'INV-001' }}</a>
                        </td>
                        <td>{{ $c->billTo->name ?? ($c->vendor->name ?? 'TRADE PARTNER') }}</td>
                        <td>{{ $c->invoice_date ? $c->invoice_date->format('m-d-Y') : date('m-d-Y') }}</td>
                        <td>{{ $c->currency->code ?? 'USD' }}</td>
                        <td style="text-align:right;">{{ $c->type === 'AR' ? number_format($c->amount, 2) : '0.00' }}</td>
                        <td style="text-align:right;">{{ $c->type === 'AP' ? number_format($c->amount, 2) : '0.00' }}</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right; font-weight:bold;">{{ number_format($c->amount, 2) }}</td>
                        <td style="text-align:right; font-weight:bold;">{{ number_format($c->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td>M</td>
                        <td>
                            <strong>{{ $shipment->mbl_no ?? 'MBL55555' }}</strong><br>
                            <a href="#" class="inv-link">MIN-04281</a>
                        </td>
                        <td>3M COMPANY</td>
                        <td>10-07-2025</td>
                        <td>USD</td>
                        <td style="text-align:right;">125.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right; font-weight:bold;">125.00</td>
                        <td style="text-align:right; font-weight:bold;">125.00</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="currency-table">
                <tr>
                    <td class="lbl" rowspan="2" style="vertical-align:middle; font-weight:bold;">TOTAL</td>
                    <td class="lbl">USD</td>
                    <td class="val">125.00</td>
                    <td class="lbl">EUR</td>
                    <td class="val">0.00</td>
                    <td class="lbl">GBP</td>
                    <td class="val">0.00</td>
                </tr>
            </table>

            <div class="profit-box-container">
                <table class="profit-box">
                    <tr>
                        <td class="title">TOTAL PROFIT (USD)</td>
                        <td class="val">125.00</td>
                    </tr>
                    <tr>
                        <td class="title">PROFIT PERCENTAGE</td>
                        <td class="val">100.00%</td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

    <div x-show="showBatchEmailModal" x-cloak style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center; margin:0; padding:0;" @click.self="showBatchEmailModal=false">
        <div style="background:#ffffff; width:980px; max-width:92vw; max-height:90vh; border-radius:6px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; margin:auto;" @click.stop>
            <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <h3 style="font-size:15px; font-weight:600; color:#1e293b; margin:0;">Send Profit Summary Report Email</h3>
                <button type="button" @click="showBatchEmailModal=false" style="background:none; border:none; font-size:22px; color:#94a3b8; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <div style="padding:20px 24px; overflow-y:auto; flex:1; font-size:12px; color:#334155; display:flex; flex-direction:column; gap:14px;">
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">From</div>
                    <select x-model="emailForm.from" style="flex:1; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; color:#334155; outline:none; background:#fff;">
                        <option value="demo@freightx.com">demo@freightx.com</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">To</div>
                    <input type="text" x-model="emailForm.to" placeholder="Enter recipient email..." style="flex:1; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; outline:none;">
                </div>
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">Subject</div>
                    <input type="text" x-model="emailForm.subject" style="flex:1; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; font-weight:bold; outline:none;">
                </div>
                <div style="display:flex; align-items:flex-start;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0; margin-top:8px;">Content</div>
                    <textarea x-model="emailForm.body" style="flex:1; height:180px; padding:10px; border:1px solid #cbd5e1; border-radius:4px; font-family:inherit; font-size:12px; outline:none; resize:none;"></textarea>
                </div>
            </div>
            <div style="padding:10px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc;">
                <button type="button" @click="showBatchEmailModal=false" style="padding:6px 16px; background:#e2e8f0; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer; font-weight:500; color:#334155; font-size:12px;">Cancel</button>
                <button type="button" @click="sendEmail()" :disabled="isSending" style="padding:6px 18px; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:500; font-size:12px;">
                    <span x-text="isSending ? 'Sending...' : 'Send'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function profitSummaryApp() {
            return {
                showBatchEmailModal: false,
                isSending: false,
                emailForm: {
                    from: 'demo@freightx.com',
                    to: '',
                    subject: '[FREIGHTX] PROFIT REPORT - {{ $shipment->file_no ?? "MOE-25100001" }}',
                    body: 'Please find attached the Profit Report Summary for shipment {{ $shipment->file_no ?? "MOE-25100001" }}.\n\nThank you,\nFreightX Team'
                },
                openEmailModal() { this.showBatchEmailModal = true; },
                sendEmail() {
                    this.isSending = true;
                    setTimeout(() => {
                        this.isSending = false;
                        alert('Profit Summary Report Email sent successfully!');
                        this.showBatchEmailModal = false;
                    }, 800);
                }
            }
        }
    </script>
</body>
</html>
