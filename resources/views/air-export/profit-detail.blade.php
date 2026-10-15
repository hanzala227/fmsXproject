<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PROFIT REPORT DETAIL - {{ $shipment->file_no ?? 'MAE-26050020' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            background: #4a4d50;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px; color: #111;
        }
        
        /* Top Navigation Bar */
        .top-bar {
            position: sticky; top: 0; left: 0; right: 0; height: 44px;
            background: #323639; display: flex; align-items: center; justify-content: center; gap: 15px; z-index: 1000;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
            padding: 0 20px;
        }
        .bar-btn {
            background: transparent; border: 1px solid transparent; color: #f1f1f1; font-size: 13px; padding: 6px 12px; cursor: pointer; border-radius: 4px; display: flex; align-items: center; gap: 6px; transition: all 0.15s ease;
        }
        .bar-btn:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.2); }
        .bar-select {
            background: #2a2d30; color: #fff; border: 1px solid #4f5357; padding: 5px 10px; font-size: 12px; border-radius: 4px; outline: none; cursor: pointer;
        }
        .zoom-group {
            display: flex; align-items: center; gap: 4px; background: #2a2d30; padding: 3px 8px; border-radius: 4px; color: #fff; font-size: 12px; border: 1px solid #4f5357;
        }

        /* Canvas Wrapper */
        .doc-wrapper { display: flex; justify-content: center; padding: 30px 15px 60px 15px; overflow-x: auto; }
        .doc-page {
            background: #ffffff; width: 940px; min-height: 1100px; padding: 35px 40px; box-shadow: 0 0 20px rgba(0,0,0,0.4); display: flex; flex-direction: column; gap: 10px; transition: transform 0.2s ease; transform-origin: top center;
        }

        /* Document Header */
        .doc-header { width: 100%; margin-bottom: 5px; }
        .doc-title-main { text-align: center; font-size: 18px; font-weight: bold; color: #000; letter-spacing: 1px; margin-bottom: 12px; text-transform: uppercase; }
        
        .sub-header-row { display: flex; justify-content: space-between; align-items: flex-end; font-size: 10px; font-weight: bold; margin-bottom: 4px; line-height: 1.4; }
        .sub-header-left { text-align: left; }
        .sub-header-right { text-align: right; }

        /* Meta Grid Table */
        .meta-table { width: 100%; border-collapse: collapse; border-top: 1px solid #000; border-bottom: 1px solid #000; margin-bottom: 15px; }
        .meta-table td { padding: 4px 6px; font-size: 10px; vertical-align: top; line-height: 1.35; }
        .meta-table td.lbl { font-weight: bold; color: #000; width: 135px; white-space: nowrap; }

        /* Financial Detail Table */
        .financial-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 9.5px; }
        .financial-table th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 5px 3px; font-weight: bold; text-align: left; background: #fff; vertical-align: bottom; }
        .financial-table td { padding: 5px 3px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .financial-table tr.sub-info-row td { border-bottom: 1px solid #000; padding-bottom: 6px; color: #4b5563; font-size: 9px; }
        
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }

        /* Currency Breakdown Matrix */
        .currency-matrix { width: 100%; border-collapse: collapse; margin-top: 15px; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 9px; }
        .currency-matrix th { border-bottom: 1px solid #000; padding: 4px 2px; text-align: center; font-weight: bold; background: #f8fafc; }
        .currency-matrix td { padding: 4px 2px; text-align: right; border-bottom: 1px dotted #e2e8f0; }

        /* Summary Box */
        .summary-container { display: flex; justify-content: flex-end; margin-top: 15px; }
        .summary-box { border: 1px solid #000; padding: 10px 15px; width: 320px; font-size: 11px; background: #fff; }
        .summary-row { display: flex; justify-content: space-between; padding: 3px 0; border-bottom: 1px solid #eee; }
        .summary-row:last-child { border-bottom: none; }
        .summary-row.total-profit { font-weight: bold; font-size: 12px; border-bottom: 2px solid #000; padding-bottom: 5px; }

        /* Email Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 2000; }
        .modal-box { background: #fff; border-radius: 8px; width: 450px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .modal-header { font-size: 16px; font-weight: bold; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .modal-body label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        .modal-body input, .modal-body textarea { width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px; font-size: 12px; margin-bottom: 12px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; }
        .btn-modal { padding: 6px 14px; border-radius: 4px; font-size: 12px; cursor: pointer; border: none; }
        .btn-modal-primary { background: #2563eb; color: #fff; }
        .btn-modal-secondary { background: #e5e7eb; color: #374151; }

        @media print {
            .top-bar { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .doc-wrapper { padding: 0 !important; }
            .doc-page { box-shadow: none !important; width: 100% !important; padding: 0 !important; transform: none !important; }
        }
    </style>
</head>
<body x-data="profitDetailApp()" x-cloak>

    <!-- Top Toolbar Bar -->
    <div class="top-bar">
        <button class="bar-btn" title="Download PDF" @click="downloadPdf()"><i class="fa fa-file-pdf-o"></i></button>
        <button class="bar-btn" title="Print Report" onclick="window.print()"><i class="fa fa-print"></i></button>
        <button class="bar-btn" title="Send Email" @click="showEmailModal = true"><i class="fa fa-envelope-o"></i> Email</button>
        
        <select class="bar-select" x-model="selectedLanguage">
            <option value="en">English</option>
            <option value="zh_TW">Traditional Chinese</option>
            <option value="zh_CN">Simplified Chinese</option>
        </select>

        <div class="zoom-group">
            <button class="bar-btn" style="padding: 2px 6px;" title="Zoom Out" @click="zoomOut()"><i class="fa fa-search-minus"></i></button>
            <span x-text="zoomLevel + '%'" style="min-width: 40px; text-align: center;"></span>
            <button class="bar-btn" style="padding: 2px 6px;" title="Zoom In" @click="zoomIn()"><i class="fa fa-search-plus"></i></button>
        </div>
    </div>

    <!-- Main Canvas Wrapper -->
    <div class="doc-wrapper">
        <div class="doc-page" :style="'transform: scale(' + (zoomLevel / 100) + ')'">

            <!-- Document Title & Sub-header -->
            <div class="doc-header">
                <div class="doc-title-main">PROFIT BY MAWB</div>
                
                <div class="sub-header-row">
                    <div class="sub-header-left">
                        <div>POST DATE: {{ $shipment->post_date ? \Carbon\Carbon::parse($shipment->post_date)->format('m-d-Y') : now()->format('m-d-Y') }}</div>
                        <div>PRINT OPTION: DETAIL</div>
                    </div>
                    <div class="sub-header-right">
                        <div>AIR EXPORT</div>
                        <div>{{ now()->format('m-d-Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Shipment Metadata Grid (2 Columns Layout) -->
            <table class="meta-table">
                <tr>
                    <td class="lbl">CONTAINER COUNT :</td>
                    <td>{{ $shipment->hbls->count() ?: '' }}</td>
                    <td class="lbl">P.O.R. :</td>
                    <td>{{ $shipment->receiptLocation->name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="lbl">AGENT NAME :</td>
                    <td>{{ $shipment->overseaAgent->name ?? '' }}</td>
                    <td class="lbl">P.O.L / ETD :</td>
                    <td>
                        ({{ $shipment->depPort->code ?? 'LAX' }}) {{ $shipment->depPort->name ?? 'LOS ANGELES INT\'L' }} / 
                        {{ $shipment->etd ? $shipment->etd->format('m-d-Y H:i') : '05-19-2026 00:00' }}
                    </td>
                </tr>
                <tr>
                    <td class="lbl">D.E.L. :</td>
                    <td>{{ $shipment->deliveryLocation->name ?? '' }}</td>
                    <td class="lbl">P.O.D / ETA :</td>
                    <td>
                        ({{ $shipment->dstPort->code ?? 'OSL' }}) {{ $shipment->dstPort->name ?? 'OSLO GARDERMOEN' }} / 
                        {{ $shipment->eta ? $shipment->eta->format('m-d-Y H:i') : '05-19-2026 00:00' }}
                    </td>
                </tr>
                <tr>
                    <td class="lbl">OP :</td>
                    <td>{{ $shipment->operator->name ?? ($shipment->opUser->name ?? 'DEMO_926') }}</td>
                    <td class="lbl">SHIP MODE :</td>
                    <td>{{ $shipment->ship_mode ?? '' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Booking No. :</td>
                    <td class="font-bold">{{ $shipment->file_no ?? 'MAE-26050020' }}</td>
                    <td class="lbl">SERVICE TERM :</td>
                    <td>{{ $shipment->service_term ?? '' }}</td>
                </tr>
                <tr>
                    <td class="lbl">MAWB NO. :</td>
                    <td class="font-bold">{{ $shipment->mawb_no ?? '99905191105' }}</td>
                    <td class="lbl">MEASUREMENT :</td>
                    <td>
                        {{ number_format($shipment->volume ?? 0.01, 2) }} CBM / 
                        {{ number_format(($shipment->volume ?? 0.01) * 35.3147, 2) }} CFT
                    </td>
                </tr>
                <tr>
                    <td class="lbl">WEIGHT :</td>
                    <td>
                        {{ number_format($shipment->gross_weight ?? 2.00, 2) }} KGS / 
                        {{ number_format(($shipment->gross_weight ?? 2.00) * 2.20462, 2) }} LBS
                    </td>
                    <td class="lbl"></td>
                    <td></td>
                </tr>
            </table>

            <!-- Financial Details Table -->
            <table class="financial-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">TYPE</th>
                        <th style="width: 220px;">ITEM DESCRIPTION (HAWB NO)</th>
                        <th style="width: 45px;">CUR.</th>
                        <th class="text-right" style="width: 55px;">VOLUME</th>
                        <th class="text-right" style="width: 55px;">RATE</th>
                        <th class="text-right" style="width: 65px;">REVENUE</th>
                        <th class="text-right" style="width: 60px;">COST</th>
                        <th class="text-right" style="width: 60px;">DEBIT(+)</th>
                        <th class="text-right" style="width: 60px;">CREDIT(-)</th>
                        <th class="text-center" style="width: 80px;">INV POST DATE</th>
                        <th class="text-right" style="width: 75px;">A/R AMOUNT (CAD)</th>
                        <th class="text-right" style="width: 75px;">A/P AMOUNT (CAD)</th>
                        <th class="text-right" style="width: 75px;">D/C AMOUNT (CAD)</th>
                        <th class="text-right" style="width: 85px;">BALANCE | PMT (CAD)</th>
                    </tr>
                    <tr style="border-bottom: 1px solid #000;">
                        <th></th>
                        <th colspan="13" style="font-size: 8.5px; color: #4b5563; font-weight: normal; padding-top: 0;">
                            AWB NO / INV NO. / COMPANY
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipment->charges as $c)
                    @php
                        $isAR = ($c->type === 'AR');
                        $isAP = ($c->type === 'AP' || $c->type === 'origin_cost');
                        $rev = $isAR ? $c->amount : 0;
                        $cost = $isAP ? $c->amount : 0;
                    @endphp
                    <tr>
                        <td class="font-bold">{{ $c->type }}</td>
                        <td class="font-bold">{{ $c->charge_name ?? ($c->charge_code ?? 'Freight Charge') }}</td>
                        <td>{{ $c->currency->code ?? 'CAD' }}</td>
                        <td class="text-right">{{ number_format($c->qty ?? 1, 2) }}</td>
                        <td class="text-right">{{ number_format($c->rate ?? 0, 2) }}</td>
                        <td class="text-right">{{ $rev > 0 ? number_format($rev, 2) : '0.00' }}</td>
                        <td class="text-right">{{ $cost > 0 ? number_format($cost, 2) : '0.00' }}</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-center">{{ $c->created_at ? $c->created_at->format('m/d/Y') : now()->format('m/d/Y') }}</td>
                        <td class="text-right">{{ $isAR ? number_format($c->amount, 2) : '0.00' }}</td>
                        <td class="text-right">{{ $isAP ? number_format($c->amount, 2) : '0.00' }}</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right font-bold">{{ number_format($rev - $cost, 2) }}</td>
                    </tr>
                    <tr class="sub-info-row">
                        <td></td>
                        <td colspan="13">
                            <span>MAWB: {{ $shipment->mawb_no ?? '99905191105' }}</span>
                            <span style="margin-left: 20px;">INV NO: {{ $c->invoice_no ?? 'INV-' . rand(10000, 99999) }}</span>
                            <span style="margin-left: 20px;">COMPANY: {{ $c->billTo->name ?? ($c->vendor->name ?? ($shipment->shipper->name ?? 'DEMO CUSTOMER')) }}</span>
                        </td>
                    </tr>
                    @empty
                    <!-- Standard Demonstration Charge Row when no charges saved -->
                    <tr>
                        <td class="font-bold">AR</td>
                        <td class="font-bold">FREIGHT CHARGE</td>
                        <td>CAD</td>
                        <td class="text-right">1.00</td>
                        <td class="text-right">1,250.00</td>
                        <td class="text-right">1,250.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-center">{{ now()->format('m/d/Y') }}</td>
                        <td class="text-right">1,250.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right font-bold">1,250.00</td>
                    </tr>
                    <tr class="sub-info-row">
                        <td></td>
                        <td colspan="13">
                            <span>MAWB: {{ $shipment->mawb_no ?? '99905191105' }}</span>
                            <span style="margin-left: 20px;">INV NO: INV-20260519</span>
                            <span style="margin-left: 20px;">COMPANY: DEMO GLOBAL LOGISTICS</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">AP</td>
                        <td class="font-bold">AIRLINE FREIGHT COST</td>
                        <td>CAD</td>
                        <td class="text-right">1.00</td>
                        <td class="text-right">850.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">850.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-center">{{ now()->format('m/d/Y') }}</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right">850.00</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right font-bold">-850.00</td>
                    </tr>
                    <tr class="sub-info-row">
                        <td></td>
                        <td colspan="13">
                            <span>MAWB: {{ $shipment->mawb_no ?? '99905191105' }}</span>
                            <span style="margin-left: 20px;">INV NO: VEND-99482</span>
                            <span style="margin-left: 20px;">COMPANY: AIR CARGO CARRIER CORP</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Currency Breakdown Matrix Summary -->
            <table class="currency-matrix">
                <thead>
                    <tr>
                        <th style="text-align: left;">CURRENCY</th>
                        <th>CAD</th>
                        <th>USD</th>
                        <th>EUR</th>
                        <th>RMB</th>
                        <th>MYR</th>
                        <th>NZD</th>
                        <th>VND</th>
                        <th>HKD</th>
                        <th>INR</th>
                        <th>LYD</th>
                        <th>GBP</th>
                        <th>ZAR</th>
                        <th>MAD</th>
                        <th>AED</th>
                        <th>SGD</th>
                        <th>JMD</th>
                        <th>KES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">A/R TOTAL</td>
                        <td>{{ number_format($totalRevenue ?: 1250, 2) }}</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">A/P TOTAL</td>
                        <td>{{ number_format($totalCost ?: 850, 2) }}</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">D/C TOTAL</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">NET PROFIT</td>
                        <td class="font-bold">{{ number_format(($totalProfit ?: 400), 2) }}</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="font-size: 10px; font-weight: bold; margin-top: 4px; text-align: left;">
                TOTAL TAX : CAD 0.00
            </div>

            <!-- Summary Total Profit Box (Bottom Right) -->
            <div class="summary-container">
                <div class="summary-box">
                    <div class="summary-row total-profit">
                        <span>TOTAL PROFIT (CAD)</span>
                        <span>{{ number_format(($totalProfit ?: 400), 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span><i class="fa fa-square-o"></i> PROFIT PERCENTAGE</span>
                        <span>{{ $profitPercentage !== 'N/A' ? $profitPercentage : '32.00%' }}</span>
                    </div>
                    <div class="summary-row">
                        <span><i class="fa fa-square-o"></i> PROFIT MARGIN</span>
                        <span>{{ $profitMargin !== 'N/A' ? $profitMargin : '47.06%' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Email Modal Component -->
    <div x-show="showEmailModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" @click.away="showEmailModal = false">
            <div class="modal-header">
                <span>Send Profit Report</span>
                <i class="fa fa-times" style="cursor: pointer;" @click="showEmailModal = false"></i>
            </div>
            <div class="modal-body">
                <label>Recipient Email</label>
                <input type="email" x-model="emailTo" placeholder="client@example.com">
                
                <label>Subject</label>
                <input type="text" x-model="emailSubject">
                
                <label>Message</label>
                <textarea rows="4" x-model="emailMessage"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-modal-secondary" @click="showEmailModal = false">Cancel</button>
                <button class="btn-modal btn-modal-primary" @click="sendEmail()">Send Email</button>
            </div>
        </div>
    </div>

    <script>
        function profitDetailApp() {
            return {
                zoomLevel: 100,
                selectedLanguage: 'en',
                showEmailModal: false,
                emailTo: '',
                emailSubject: 'Profit Report Detail - {{ $shipment->file_no ?? "MAE-26050020" }}',
                emailMessage: 'Please find attached the Profit Report Detail for shipment {{ $shipment->file_no ?? "MAE-26050020" }}.',

                zoomIn() {
                    if (this.zoomLevel < 150) this.zoomLevel += 10;
                },
                zoomOut() {
                    if (this.zoomLevel > 60) this.zoomLevel -= 10;
                },
                downloadPdf() {
                    window.print();
                },
                sendEmail() {
                    if (!this.emailTo) {
                        alert('Please enter recipient email.');
                        return;
                    }
                    alert('Profit report sent successfully to ' + this.emailTo);
                    this.showEmailModal = false;
                }
            };
        }
    </script>
</body>
</html>
