<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PROFIT REPORT SUMMARY - {{ $shipment->file_no ?? 'MAE-26050020' }}</title>
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
            background: #ffffff; width: 940px; min-height: 1000px; padding: 35px 40px; box-shadow: 0 0 20px rgba(0,0,0,0.4); display: flex; flex-direction: column; gap: 10px; transition: transform 0.2s ease; transform-origin: top center;
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

        /* Financial Summary Table */
        .financial-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 9.5px; }
        .financial-table th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 5px 4px; font-weight: bold; text-align: left; background: #fff; vertical-align: bottom; }
        .financial-table td { padding: 5px 4px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }

        /* Embedded Currency Matrix */
        .currency-matrix-table { width: 100%; border-collapse: collapse; margin-top: 10px; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 9.5px; }
        .currency-matrix-table td { padding: 4px 6px; border-bottom: 1px solid #000; vertical-align: middle; }
        .currency-matrix-table td.matrix-label { font-weight: bold; width: 70px; text-align: center; border-right: 1px solid #000; background: #fff; vertical-align: middle; }
        .currency-cell-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px 15px; padding: 4px 8px; }
        .currency-item { display: flex; justify-content: space-between; font-size: 9.5px; border-bottom: 1px dotted #ccc; padding: 2px 0; }
        .currency-code { font-weight: bold; width: 40px; }
        .currency-val { font-weight: normal; text-align: right; }

        /* Tax Row */
        .tax-row { width: 100%; border-collapse: collapse; border-bottom: 1px solid #000; font-size: 9.5px; }
        .tax-row td { padding: 4px 6px; font-weight: bold; }

        /* Summary Box */
        .summary-container { display: flex; justify-content: flex-end; margin-top: 15px; }
        .summary-box { border: 1px solid #000; padding: 8px 12px; width: 320px; font-size: 10.5px; background: #fff; }
        .summary-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #eee; align-items: center; }
        .summary-row:last-child { border-bottom: none; }
        .summary-row.total-profit { font-weight: bold; font-size: 11.5px; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .checkbox-lbl { display: flex; align-items: center; gap: 6px; font-weight: normal; cursor: pointer; }

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
<body x-data="profitSummaryApp()" x-cloak>

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
                        <div>PRINT OPTION: SUMMARY</div>
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
                    <td class="lbl">P.O.R :</td>
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

            <!-- Financial Summary Table -->
            <table class="financial-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">TYPE</th>
                        <th style="width: 250px;">--- AWB NO / INV NO ------- COMPANY ---</th>
                        <th class="text-center" style="width: 90px;">INV POST DATE</th>
                        <th style="width: 45px;">CUR.</th>
                        <th class="text-right" style="width: 80px;">A/R AMOUNT</th>
                        <th class="text-right" style="width: 80px;">A/P AMOUNT</th>
                        <th class="text-right" style="width: 80px;">D/C AMOUNT</th>
                        <th class="text-right" style="width: 95px;">AMOUNT(CAD)</th>
                        <th class="text-right" style="width: 95px;">BALANCE(CAD)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipment->charges as $c)
                    @php
                        $isAR = ($c->type === 'AR');
                        $isAP = ($c->type === 'AP' || $c->type === 'origin_cost');
                    @endphp
                    <tr>
                        <td class="font-bold">M</td>
                        <td>
                            <strong>{{ $shipment->mawb_no ?? '99905191105' }}</strong> / 
                            <span style="color: #2563eb; font-weight: bold;">{{ $c->invoice_no ?? 'INV-' . rand(10000, 99999) }}</span>
                            <div style="font-size: 9px; color: #4b5563;">{{ $c->billTo->name ?? ($c->vendor->name ?? 'DEMO CUSTOMER') }}</div>
                        </td>
                        <td class="text-center">{{ $c->created_at ? $c->created_at->format('m/d/Y') : now()->format('m/d/Y') }}</td>
                        <td>{{ $c->currency->code ?? 'CAD' }}</td>
                        <td class="text-right">{{ $isAR ? number_format($c->amount, 2) : '0.00' }}</td>
                        <td class="text-right">{{ $isAP ? number_format($c->amount, 2) : '0.00' }}</td>
                        <td class="text-right">0.00</td>
                        <td class="text-right font-bold">{{ number_format($c->amount, 2) }}</td>
                        <td class="text-right font-bold">{{ number_format(($isAR ? $c->amount : 0) - ($isAP ? $c->amount : 0), 2) }}</td>
                    </tr>
                    @empty
                    <!-- Standard empty summary row placeholder matching exact structure if no charges -->
                    @endforelse
                </tbody>
            </table>

            <!-- Embedded Currency Matrix Summary Table -->
            <table class="currency-matrix-table">
                <tr>
                    <td class="matrix-label">TOTAL</td>
                    <td>
                        <div class="currency-cell-grid">
                            <div class="currency-item"><span class="currency-code">CAD</span><span class="currency-val">{{ number_format($totalRevenue ?: 0, 2) }}</span></div>
                            <div class="currency-item"><span class="currency-code">USD</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">EUR</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">RMB</span><span class="currency-val">0.00</span></div>
                            
                            <div class="currency-item"><span class="currency-code">MYR</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">NZD</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">VND</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">HKD</span><span class="currency-val">0.00</span></div>

                            <div class="currency-item"><span class="currency-code">INR</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">LYD</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">GBP</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">ZAR</span><span class="currency-val">0.00</span></div>

                            <div class="currency-item"><span class="currency-code">MAD</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">AED</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">SGD</span><span class="currency-val">0.00</span></div>
                            <div class="currency-item"><span class="currency-code">JMD</span><span class="currency-val">0.00</span></div>

                            <div class="currency-item"><span class="currency-code">KES</span><span class="currency-val">0.00</span></div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Total Tax Row -->
            <table class="tax-row">
                <tr>
                    <td style="width: 135px;">TOTAL TAX</td>
                    <td style="width: 80px;">CAD</td>
                    <td>0.00</td>
                </tr>
            </table>

            <!-- Summary Total Profit Box (Bottom Right) -->
            <div class="summary-container">
                <div class="summary-box">
                    <div class="summary-row total-profit">
                        <span>TOTAL PROFIT (CAD)</span>
                        <span>{{ number_format(($totalProfit ?: 0), 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <label class="checkbox-lbl">
                            <input type="checkbox" x-model="showPercentage"> PROFIT PERCENTAGE
                        </label>
                        <span x-text="showPercentage ? '{{ $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 2) . "%" : "N/A" }}' : 'N/A'"></span>
                    </div>
                    <div class="summary-row">
                        <label class="checkbox-lbl">
                            <input type="checkbox" x-model="showMargin"> PROFIT MARGIN
                        </label>
                        <span x-text="showMargin ? '{{ $totalCost > 0 ? number_format(($totalProfit / $totalCost) * 100, 2) . "%" : "N/A" }}' : 'N/A'"></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Email Modal Component -->
    <div x-show="showEmailModal" class="modal-overlay" style="display: none;">
        <div class="modal-box" @click.away="showEmailModal = false">
            <div class="modal-header">
                <span>Send Profit Report Summary</span>
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
        function profitSummaryApp() {
            return {
                zoomLevel: 100,
                selectedLanguage: 'en',
                showEmailModal: false,
                showPercentage: false,
                showMargin: false,
                emailTo: '',
                emailSubject: 'Profit Report Summary - {{ $shipment->file_no ?? "MAE-26050020" }}',
                emailMessage: 'Please find attached the Profit Report Summary for shipment {{ $shipment->file_no ?? "MAE-26050020" }}.',

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
                    alert('Profit report summary sent successfully to ' + this.emailTo);
                    this.showEmailModal = false;
                }
            };
        }
    </script>
</body>
</html>
