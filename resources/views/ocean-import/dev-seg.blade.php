<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DEV/SEG - Container Devanning Guide - {{ $shipment->file_no ?? 'MOI-25100001' }}</title>
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
            gap: 15px;
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

        .container-select {
            background: #fff;
            color: #333;
            border: 1px solid #ccc;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            outline: none;
            cursor: pointer;
        }

        /* Document Container */
        .doc-wrapper {
            display: flex;
            justify-content: center;
            padding: 30px 15px;
        }

        .doc-page {
            background: #ffffff;
            width: 860px;
            min-height: 1100px;
            padding: 35px 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-title h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }
        .company-title p {
            margin: 2px 0;
            font-size: 10px;
            color: #444;
        }

        /* Title Box */
        .title-box-container {
            width: 320px;
            border: 1px solid #1e3a8a;
            border-radius: 2px;
            overflow: hidden;
        }
        .title-box-header {
            background: #ffffff;
            color: #1e3a8a;
            font-weight: bold;
            font-size: 12px;
            padding: 6px;
            text-align: center;
            border-bottom: 1px solid #1e3a8a;
            letter-spacing: 0.5px;
        }
        .title-box-meta {
            display: flex;
            background: #fff;
            font-size: 10px;
        }
        .title-box-meta-item {
            flex: 1;
            padding: 4px 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .title-box-meta-item:first-child {
            border-right: 1px solid #1e3a8a;
        }

        /* Main Form Section */
        .main-form-grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 15px;
        }

        .left-boxes {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .box-container {
            border: 1px solid #475569;
            padding: 6px 8px;
            background: #fff;
        }

        .box-label {
            font-size: 9px;
            font-weight: bold;
            color: #475569;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .box-input-area {
            width: 100%;
            height: 90px;
            border: none;
            outline: none;
            resize: none;
            font-size: 11px;
            font-family: inherit;
        }

        /* Right Table Grid */
        .grid-info-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #475569;
        }
        .grid-info-table td {
            border: 1px solid #475569;
            padding: 5px 8px;
            font-size: 10px;
        }
        .grid-info-table td.label {
            background: #f8fafc;
            font-weight: bold;
            color: #334155;
            width: 110px;
            text-transform: uppercase;
        }
        .grid-info-table td.val {
            background: #fff;
            color: #000;
        }

        /* Cargo Items Table */
        .cargo-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .cargo-table th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 6px 4px;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            background: #fff;
        }
        .cargo-table td {
            padding: 8px 4px;
            font-size: 10px;
            vertical-align: top;
            border-bottom: 1px solid #cbd5e1;
        }

        .summary-row td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            padding: 8px 4px;
        }

        .cell-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            padding: 2px 4px;
            font-size: 10px;
            border-radius: 2px;
        }

        @media print {
            .top-bar { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .doc-wrapper { padding: 0 !important; }
            .doc-page { box-shadow: none !important; width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body x-data="devSegApp()" x-cloak>

    <!-- Top Toolbar -->
    <div class="top-bar">
        <button class="bar-btn" title="Download PDF" @click="window.print()"><i class="fa fa-file-pdf-o"></i></button>
        <button class="bar-btn" title="Print" @click="window.print()"><i class="fa fa-print"></i></button>
        <button class="bar-btn" title="Send Email" @click="openEmailModal()"><i class="fa fa-envelope"></i> Email</button>
        
        <select class="container-select" x-model="selectedContainerId" @change="switchContainer()">
            <template x-for="c in containerList" :key="c.id">
                <option :value="c.id" x-text="`DEV/SEG - ${c.container_no}`"></option>
            </template>
        </select>
    </div>

    <!-- Main Print Page -->
    <div class="doc-wrapper">
        <div class="doc-page">
            
            <!-- Company Header -->
            <div class="header-section">
                <div class="company-title">
                    <h1>FREIGHTX</h1>
                    <p>9149 WILKERSON MEWS SUITE 546</p>
                    <p>NEW VALERIEVIEW, VI 34553-1977</p>
                    <p>TEL: 045-085-5813x845 FAX: 045-085-5813x845</p>
                    <p>EMAIL: shawon@silk-container.com</p>
                    <p style="margin-top:4px; font-weight:bold;">Prepared by DEMO_925 {{ date('m-d-Y H:i') }} (PDT)</p>
                </div>

                <div class="title-box-container">
                    <div class="title-box-header">CONTAINER DEVANNING GUIDE</div>
                    <div class="title-box-meta">
                        <div class="title-box-meta-item">
                            <strong style="color:#64748b;">DATE :</strong>
                            <input type="text" value="{{ date('m-d-Y') }}" style="border:1px solid #cbd5e1; width:75px; font-size:10px; padding:1px 3px;">
                        </div>
                        <div class="title-box-meta-item">
                            <strong style="color:#64748b;">FROM :</strong>
                            <input type="text" value="DEMO_925" style="border:1px solid #cbd5e1; width:75px; font-size:10px; padding:1px 3px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Layout Grid -->
            <div class="main-form-grid">
                
                <!-- Left Textarea Boxes -->
                <div class="left-boxes">
                    <div class="box-container">
                        <div class="box-label">TO :</div>
                        <textarea class="box-input-area" placeholder="Enter recipient details..."></textarea>
                    </div>

                    <div class="box-container">
                        <div class="box-label">FREIGHT LOCATION (CY) :</div>
                        <textarea class="box-input-area" placeholder="Enter CY location..."></textarea>
                    </div>
                </div>

                <!-- Right Table Grid -->
                <table class="grid-info-table">
                    <tr>
                        <td class="label">Booking No.</td>
                        <td class="val" style="font-weight:bold;">{{ $shipment->file_no ?? 'MOI-25100001' }}</td>
                        <td class="label">MB/L NO.</td>
                        <td class="val" style="font-weight:bold;">{{ $shipment->mbl_no ?? 'MBL55555' }}</td>
                    </tr>
                    <tr>
                        <td class="label">AMS B/L NO.</td>
                        <td class="val"><input type="text" style="border:none; width:100%; outline:none;" value=""></td>
                        <td class="label">IT NO.</td>
                        <td class="val"><input type="text" style="border:none; width:100%; outline:none;" value=""></td>
                    </tr>
                    <tr>
                        <td class="label">VESSEL & VOYAGE</td>
                        <td class="val">{{ $shipment->vessel->name ?? '. ABHIJEET FFFFF342' }}</td>
                        <td class="label">CARRIER</td>
                        <td class="val">{{ $shipment->carrier->name ?? 'CMA CGM (CANADA)' }}</td>
                    </tr>
                    <tr>
                        <td class="label">POL</td>
                        <td class="val">CHITTAGONG (BANGLADESH)</td>
                        <td class="label">ETD</td>
                        <td class="val">10-01-2025</td>
                    </tr>
                    <tr>
                        <td class="label">POD</td>
                        <td class="val">HAMBURG (GERMANY)</td>
                        <td class="label">ETA</td>
                        <td class="val">10-07-2025</td>
                    </tr>
                    <tr>
                        <td class="label">PLACE OF DELIVERY</td>
                        <td class="val"><input type="text" style="border:none; width:100%; outline:none;" value=""></td>
                        <td class="label">DELIVERY ETA</td>
                        <td class="val"><input type="text" style="border:none; width:100%; outline:none;" value=""></td>
                    </tr>
                    <tr>
                        <td class="label">CNTR & SEAL#</td>
                        <td class="val" colspan="3" style="font-weight:bold;" x-text="`${currentContainer.container_no} / ${currentContainer.seal_no} / ${currentContainer.type}`"></td>
                    </tr>
                    <tr>
                        <td class="label">INSTRUCTION</td>
                        <td class="val" colspan="3">
                            <textarea style="width:100%; height:45px; border:none; outline:none; resize:none; font-family:inherit; font-size:10px;"></textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Checkbox Control -->
            <div style="margin-top:5px; font-weight:bold; font-size:11px; color:#2563eb;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                    <input type="checkbox" x-model="showShipperConsignee" style="accent-color:#2563eb;">
                    Show Shipper and Consignee Information
                </label>
            </div>

            <!-- Cargo Table Grid -->
            <table class="cargo-table">
                <thead>
                    <tr>
                        <th style="width:30px;">#</th>
                        <th style="width:140px;">HB/L NO.<br>AMS B/L NO.</th>
                        <th style="width:180px;" x-show="showShipperConsignee">SHIPPER<br>CONSIGNEE</th>
                        <th style="width:110px;">F.DEST<br>I.T. NO.</th>
                        <th style="width:200px;">COMMODITY MARK<br>PACKAGES</th>
                        <th style="width:90px; text-align:right;">KGS<br>LBS</th>
                        <th style="width:90px; text-align:right;">CBM<br>CFT</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(hbl, idx) in hblList" :key="idx">
                        <tr>
                            <td x-text="idx + 1"></td>
                            <td>
                                <strong x-text="hbl.hbl_no"></strong><br>
                                <span style="color:#64748b;" x-text="hbl.ams_bl_no || '-'"></span>
                                <div style="margin-top:6px; color:#475569; font-weight:bold;">CY/CY</div>
                            </td>
                            <td x-show="showShipperConsignee">
                                <div style="font-weight:bold;" x-text="hbl.shipper_name"></div>
                                <div style="margin-top:8px;" x-text="hbl.consignee_name"></div>
                            </td>
                            <td>
                                <input type="text" class="cell-input" x-model="hbl.fdest" style="margin-bottom:4px;">
                                <input type="text" class="cell-input" x-model="hbl.it_no">
                            </td>
                            <td>
                                <input type="text" class="cell-input" x-model="hbl.commodity" style="margin-bottom:4px;">
                                <input type="text" class="cell-input" x-model="hbl.mark" style="margin-bottom:4px;">
                                <div style="font-weight:bold; margin-top:4px;" x-text="hbl.packages"></div>
                            </td>
                            <td style="text-align:right;">
                                <div style="font-weight:bold;" x-text="Number(hbl.weight_kgs).toFixed(2)"></div>
                                <div style="color:#64748b; font-size:9px;">KGS</div>
                                <div style="margin-top:4px;" x-text="Number(hbl.weight_lbs).toFixed(2)"></div>
                                <div style="color:#64748b; font-size:9px;">LBS</div>
                            </td>
                            <td style="text-align:right;">
                                <div style="font-weight:bold;" x-text="Number(hbl.measure_cbm).toFixed(2)"></div>
                                <div style="color:#64748b; font-size:9px;">CBM</div>
                                <div style="margin-top:4px;" x-text="Number(hbl.measure_cft).toFixed(2)"></div>
                                <div style="color:#64748b; font-size:9px;">CFT</div>
                            </td>
                        </tr>
                    </template>

                    <!-- Total Summary Row -->
                    <tr class="summary-row">
                        <td>TOTAL</td>
                        <td><span x-text="`${hblList.length} HBL(S)`"></span></td>
                        <td x-show="showShipperConsignee"></td>
                        <td></td>
                        <td>0 PCS</td>
                        <td style="text-align:right;">
                            <div x-text="totalKgs.toFixed(2)"></div>
                            <div style="font-size:9px; font-weight:normal; color:#475569;">KGS</div>
                            <div x-text="totalLbs.toFixed(2)"></div>
                            <div style="font-size:9px; font-weight:normal; color:#475569;">LBS</div>
                        </td>
                        <td style="text-align:right;">
                            <div x-text="totalCbm.toFixed(2)"></div>
                            <div style="font-size:9px; font-weight:normal; color:#475569;">CBM</div>
                            <div x-text="totalCft.toFixed(2)"></div>
                            <div style="font-size:9px; font-weight:normal; color:#475569;">CFT</div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    <!-- Batch Email Modal Integration -->
    <div x-show="showBatchEmailModal" x-cloak style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center; margin:0; padding:0;" @click.self="showBatchEmailModal=false">
        <div style="background:#ffffff; width:980px; max-width:92vw; max-height:90vh; border-radius:6px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; margin:auto;" @click.stop>
            
            <!-- Modal Header -->
            <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <h3 style="font-size:15px; font-weight:600; color:#1e293b; margin:0;">Send Container Devanning Guide Email</h3>
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
        function devSegApp() {
            return {
                showShipperConsignee: true,
                selectedContainerId: {{ $containerList[0]['id'] ?? 1 }},
                containerList: {!! json_encode($containerList) !!},
                hblList: {!! json_encode($hblList) !!},
                currentContainer: {},

                showBatchEmailModal: false,
                isSending: false,
                emailForm: {
                    from: 'demo@freightx.com',
                    to: '',
                    subject: '[FREIGHTX] CONTAINER DEVANNING GUIDE - {{ $shipment->file_no ?? "MOI-25100001" }}',
                    body: 'Please find attached the Container Devanning Guide for shipment {{ $shipment->file_no ?? "MOI-25100001" }}.\n\nThank you,\nFreightX Team'
                },

                init() {
                    if(this.containerList && this.containerList.length > 0) {
                        this.selectedContainerId = this.containerList[0].id;
                    }
                    this.switchContainer();
                },

                switchContainer() {
                    let found = this.containerList.find(c => c.id == this.selectedContainerId);
                    if (found) {
                        this.currentContainer = found;
                    } else if(this.containerList.length > 0) {
                        this.currentContainer = this.containerList[0];
                    }
                },

                openEmailModal() {
                    this.emailForm.subject = `[FREIGHTX] CONTAINER DEVANNING GUIDE - ${this.currentContainer.container_no || 'MSDU758988'}`;
                    this.showBatchEmailModal = true;
                },

                sendEmail() {
                    this.isSending = true;
                    setTimeout(() => {
                        this.isSending = false;
                        alert('Container Devanning Guide Email sent successfully!');
                        this.showBatchEmailModal = false;
                    }, 800);
                },

                get totalKgs() {
                    return this.hblList.reduce((sum, h) => sum + Number(h.weight_kgs || 0), 0);
                },
                get totalLbs() {
                    return this.hblList.reduce((sum, h) => sum + Number(h.weight_lbs || 0), 0);
                },
                get totalCbm() {
                    return this.hblList.reduce((sum, h) => sum + Number(h.measure_cbm || 0), 0);
                },
                get totalCft() {
                    return this.hblList.reduce((sum, h) => sum + Number(h.measure_cft || 0), 0);
                }
            }
        }
    </script>
</body>
</html>
