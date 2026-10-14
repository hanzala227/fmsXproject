<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PROFIT BY MASTER B/L (DETAIL) - {{ $shipment->file_no ?? 'MOI-25100001' }}</title>
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

        /* Top Header Bar */
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

        /* Document Wrapper */
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

        /* Document Header */
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

        /* Shipment Meta Data Table */
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

        /* Financial Data Table */
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .financial-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px 3px;
            font-size: 8.5px;
            font-weight: bold;
            text-align: left;
            background: #f8fafc;
            text-transform: uppercase;
        }
        .financial-table td {
            padding: 4px 3px;
            font-size: 9.5px;
        }

        .inv-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .item-row-top td {
            color: #475569;
        }
        .item-row-bottom td {
            border-bottom: 1px solid #cbd5e1;
            font-weight: bold;
        }

        /* Currency Summary Grid Table */
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

        /* Bottom Profit Box */
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
<body x-data="profitDetailApp()" x-cloak>

    <!-- Top Toolbar -->
    <div class="top-bar">
        <button class="bar-btn" title="Download PDF" @click="window.print()"><i class="fa fa-file-pdf-o"></i></button>
        <button class="bar-btn" title="Print" @click="window.print()"><i class="fa fa-print"></i></button>
        <button class="bar-btn" title="Send Email" @click="openEmailModal()"><i class="fa fa-envelope"></i> Email</button>
        
        <select class="top-select">
            <option>English</option>
        </select>
    </div>

    <!-- Main Print Page -->
    <div class="doc-wrapper">
        <div class="doc-page">
            
            <!-- Main Title -->
            <div class="doc-title-main">PROFIT BY MASTER B/L</div>

            <!-- Sub Header Meta -->
            <div class="sub-header-row">
                <div>
                    <div>POST DATE : {{ date('m-d-Y') }}</div>
                    <div>PRINT OPTION : DETAIL</div>
                </div>
                <div style="text-align:right;">
                    <div>OCEAN IMPORT</div>
                    <div>{{ date('m-d-Y') }}</div>
                </div>
            </div>

            <!-- Shipment Info Grid -->
            <table class="meta-table">
                <tr>
                    <td class="lbl">CONTAINER COUNT :</td>
                    <td><strong>40GHC X 2</strong></td>
                    <td class="lbl">P.O.R :</td>
                    <td></td>
                </tr>
                <tr>
                    <td class="lbl">AGENT NAME :</td>
                    <td><strong>"BIRLISHEN ULAG ULGAMY" ECONOMIC SOCIETY</strong></td>
                    <td class="lbl">P.O.L / ETD :</td>
                    <td>CHITTAGONG (BANGLADESH) / 10-01-2025</td>
                </tr>
                <tr>
                    <td class="lbl">D.E.L :</td>
                    <td></td>
                    <td class="lbl">P.O.D / ETA :</td>
                    <td>HAMBURG (GERMANY) / 10-07-2025</td>
                </tr>
                <tr>
                    <td class="lbl">CONTAINER NO.:</td>
                    <td>MSDU758988, MSDU758989</td>
                    <td class="lbl">OP :</td>
                    <td>DEMO_925</td>
                </tr>
                <tr>
                    <td class="lbl">Booking No. :</td>
                    <td>{{ $shipment->file_no ?? 'MOI-25100001' }}</td>
                    <td class="lbl">SHIP MODE :</td>
                    <td>FCL</td>
                </tr>
                <tr>
                    <td class="lbl">MASTER B/L NO. :</td>
                    <td>{{ $shipment->mbl_no ?? 'MBL55555' }}</td>
                    <td class="lbl">SERVICE TERM :</td>
                    <td>CY - CY</td>
                </tr>
                <tr>
                    <td class="lbl">WEIGHT :</td>
                    <td>12,590.00 KGS / 27,756.20 LBS</td>
                    <td class="lbl">MEASUREMENT :</td>
                    <td>128.00 CBM / 4,520.27 CFT</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">
                        <label style="display:flex; align-items:center; gap:4px; font-weight:bold; cursor:pointer;">
                            <input type="checkbox"> CUSTOMER REF. NO.:
                        </label>
                    </td>
                </tr>
            </table>

            <!-- Detailed Itemized Financial Grid Table -->
            <table class="financial-table">
                <thead>
                    <tr>
                        <th style="width:25px;">TYPE</th>
                        <th style="width:180px;">ITEM DESCRIPTION (HB/L NO)<br>B/L NO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; INV NO.</th>
                        <th style="width:35px;">CUR.</th>
                        <th style="width:45px; text-align:right;">VOLUME</th>
                        <th style="width:50px; text-align:right;">RATE</th>
                        <th style="width:55px; text-align:right;">REVENUE</th>
                        <th style="width:45px; text-align:right;">COST</th>
                        <th style="width:45px; text-align:right;">DEBIT(+)</th>
                        <th style="width:50px; text-align:right;">CREDIT(-)</th>
                        <th style="width:65px; text-align:center;">INV POST DATE</th>
                        <th style="width:70px; text-align:right;">A/R AMOUNT (CAD)</th>
                        <th style="width:70px; text-align:right;">A/P AMOUNT (CAD)</th>
                        <th style="width:70px; text-align:right;">D/C AMOUNT (CAD)</th>
                        <th style="width:85px; text-align:right;">BALANCE | PMT (CAD)</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <!-- Item 1 -->
                    <tr class="item-row-top">
                        <td></td>
                        <td colspan="1" style="font-weight:bold;">FCL / FCL</td>
                        <td>USD</td>
                        <td style="text-align:right;">1.00</td>
                        <td style="text-align:right;">125.00</td>
                        <td style="text-align:right;">125.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                        <td style="text-align:right;">3,284,437.50</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="item-row-bottom">
                        <td>M</td>
                        <td>
                            <strong>{{ $shipment->mbl_no ?? 'MBL55555' }}</strong> &nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="inv-link">MIN-04281</a>
                        </td>
                        <td>CAD</td>
                        <td colspan="6"><strong>3M COMPANY</strong></td>
                        <td style="text-align:center;">10-07-2025</td>
                        <td style="text-align:right;">3,284,437.50</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">3,284,437.50 N</td>
                    </tr>

                    <!-- Item 2 -->
                    <tr class="item-row-top">
                        <td></td>
                        <td colspan="1" style="font-weight:bold;">TEST / AIR FREIGHT CHARGE</td>
                        <td>CAD</td>
                        <td style="text-align:right;">1.00</td>
                        <td style="text-align:right;">25.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">25.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">25.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="item-row-bottom">
                        <td>M</td>
                        <td>
                            <strong>{{ $shipment->mbl_no ?? 'MBL55555' }}</strong> &nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="inv-link">NP-00114</a>
                        </td>
                        <td>CAD</td>
                        <td colspan="6"><strong>"BIRLISHEN ULAG ULGAMY" ECONOMIC SOCIETY</strong></td>
                        <td style="text-align:center;">10-07-2025</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">25.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">-25.00 N</td>
                    </tr>

                    <!-- Item 3 -->
                    <tr class="item-row-top">
                        <td></td>
                        <td colspan="1" style="font-weight:bold;">OI01O/F / OCEAN FREIGHT CHARGE</td>
                        <td>USD</td>
                        <td style="text-align:right;">1.00</td>
                        <td style="text-align:right;">2,500.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">2,500.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">65,688,750.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="item-row-bottom">
                        <td>M</td>
                        <td>
                            <strong>{{ $shipment->mbl_no ?? 'MBL55555' }}</strong> &nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="inv-link">NP-00117</a>
                        </td>
                        <td>CAD</td>
                        <td colspan="6"><strong>CMA CGM (CANADA)</strong></td>
                        <td style="text-align:center;">10-07-2025</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">65,688,750.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">-65,688,750.00 N</td>
                    </tr>

                    <!-- Item 4 (House B/L block) -->
                    <tr class="item-row-top">
                        <td></td>
                        <td colspan="1" style="font-weight:bold;">OI01O/F / OCEAN FREIGHT CHARGE</td>
                        <td>USD</td>
                        <td style="text-align:right;">1.00</td>
                        <td style="text-align:right;">3,000.00</td>
                        <td style="text-align:right;">3,000.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                        <td style="text-align:right;">78,826,500.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="item-row-top">
                        <td></td>
                        <td colspan="1" style="font-weight:bold;">OI06H/C / HANDLING CHARGE</td>
                        <td>CAD</td>
                        <td style="text-align:right;">1.00</td>
                        <td style="text-align:right;">50.00</td>
                        <td style="text-align:right;">50.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                        <td style="text-align:right;">50.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td></td>
                    </tr>
                    <tr class="item-row-bottom">
                        <td>H</td>
                        <td>
                            <strong>HBL2525/5</strong> &nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="#" class="inv-link">MIN-04282</a>
                        </td>
                        <td>CAD</td>
                        <td colspan="6"><strong>3M COMPANY</strong></td>
                        <td style="text-align:center;">10-07-2025</td>
                        <td style="text-align:right;">78,826,550.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">78,826,550.00 N</td>
                    </tr>

                    <tr style="border-top:1px solid #000; font-size:10px;">
                        <td colspan="4"><strong>O B/L : Origin</strong></td>
                        <td colspan="10" style="text-align:right;"><strong>Consignee: 3M COMPANY</strong></td>
                    </tr>
                </tbody>
            </table>

            <!-- Multi-Currency Summary Breakdown -->
            <table class="currency-table">
                <tr>
                    <td class="lbl" rowspan="5" style="vertical-align:middle; font-weight:bold;">TOTAL</td>
                    <td class="lbl">CAD</td>
                    <td class="val">25.00</td>
                    <td class="lbl">USD</td>
                    <td class="val">625.00</td>
                    <td class="lbl">EUR</td>
                    <td class="val">0.00</td>
                    <td class="lbl">RMB</td>
                    <td class="val">0.00</td>
                </tr>
                <tr>
                    <td class="lbl">MYR</td>
                    <td class="val">0.00</td>
                    <td class="lbl">NZD</td>
                    <td class="val">0.00</td>
                    <td class="lbl">VND</td>
                    <td class="val">0.00</td>
                    <td class="lbl">HKD</td>
                    <td class="val">0.00</td>
                </tr>
                <tr>
                    <td class="lbl">INR</td>
                    <td class="val">0.00</td>
                    <td class="lbl">LYD</td>
                    <td class="val">0.00</td>
                    <td class="lbl">GBP</td>
                    <td class="val">0.00</td>
                    <td class="lbl">ZAR</td>
                    <td class="val">0.00</td>
                </tr>
                <tr>
                    <td class="lbl">MAD</td>
                    <td class="val">0.00</td>
                    <td class="lbl">AED</td>
                    <td class="val">0.00</td>
                    <td class="lbl">SGD</td>
                    <td class="val">0.00</td>
                    <td class="lbl">JMD</td>
                    <td class="val">0.00</td>
                </tr>
                <tr>
                    <td class="lbl">KES</td>
                    <td class="val">0.00</td>
                    <td colspan="6"></td>
                </tr>
                <tr>
                    <td class="lbl">TOTAL TAX</td>
                    <td class="lbl">CAD</td>
                    <td class="val">0.00</td>
                    <td colspan="6"></td>
                </tr>
            </table>

            <!-- Profit Summary Calculation Box -->
            <div class="profit-box-container">
                <table class="profit-box">
                    <tr>
                        <td class="title">TOTAL PROFIT (CAD)</td>
                        <td class="val">16,422,212.50</td>
                    </tr>
                    <tr>
                        <td class="title">
                            <label style="display:flex; align-items:center; gap:6px; justify-content:flex-end; cursor:pointer;">
                                <input type="checkbox"> PROFIT PERCENTAGE
                            </label>
                        </td>
                        <td class="val">25.00%</td>
                    </tr>
                    <tr>
                        <td class="title">
                            <label style="display:flex; align-items:center; gap:6px; justify-content:flex-end; cursor:pointer;">
                                <input type="checkbox"> PROFIT MARGIN
                            </label>
                        </td>
                        <td class="val">20.00%</td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

    <!-- Batch Email Modal Integration -->
    <div x-show="showBatchEmailModal" x-cloak style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center; margin:0; padding:0;" @click.self="showBatchEmailModal=false">
        <div style="background:#ffffff; width:980px; max-width:92vw; max-height:90vh; border-radius:6px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; margin:auto;" @click.stop>
            
            <!-- Modal Header -->
            <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <h3 style="font-size:15px; font-weight:600; color:#1e293b; margin:0;">Send Profit Detail Report Email</h3>
                <button type="button" @click="showBatchEmailModal=false" style="background:none; border:none; font-size:22px; color:#94a3b8; cursor:pointer; line-height:1;">&times;</button>
            </div>
            
            <!-- Modal Body -->
            <div style="padding:20px 24px; overflow-y:auto; flex:1; font-size:12px; color:#334155; display:flex; flex-direction:column; gap:14px;">
                
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">From</div>
                    <select x-model="emailForm.from" style="flex:1; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; color:#334155; outline:none; background:#fff;">
                        <option value="demo@freightx.com">demo@freightx.com</option>
                        <option value="shawon@silk-container.com">shawon@silk-container.com</option>
                    </select>
                </div>

                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">To</div>
                    <input type="text" x-model="emailForm.to" placeholder="Enter recipient email addresses separated by commas..." style="flex:1; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; outline:none;">
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
            
            <!-- Modal Footer -->
            <div style="padding:10px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc;">
                <button type="button" @click="showBatchEmailModal=false" style="padding:6px 16px; background:#e2e8f0; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer; font-weight:500; color:#334155; font-size:12px;">Cancel</button>
                <button type="button" @click="sendEmail()" :disabled="isSending" style="padding:6px 18px; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:500; font-size:12px;">
                    <span x-text="isSending ? 'Sending...' : 'Send'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function profitDetailApp() {
            return {
                showBatchEmailModal: false,
                isSending: false,
                emailForm: {
                    from: 'demo@freightx.com',
                    to: 'michael36@gardner-spears.com, amy88@hotmail.com',
                    subject: '[FREIGHTX] PROFIT REPORT (DETAIL) - {{ $shipment->file_no ?? "MOI-25100001" }}',
                    body: 'Please find attached the Profit Report Detail for shipment {{ $shipment->file_no ?? "MOI-25100001" }}.\n\nThank you,\nFreightX Team'
                },

                openEmailModal() {
                    this.showBatchEmailModal = true;
                },

                sendEmail() {
                    this.isSending = true;
                    setTimeout(() => {
                        this.isSending = false;
                        alert('Profit Detail Report Email sent successfully!');
                        this.showBatchEmailModal = false;
                    }, 800);
                }
            }
        }
    </script>
</body>
</html>
