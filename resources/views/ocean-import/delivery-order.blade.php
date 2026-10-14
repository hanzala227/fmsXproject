<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery Order - {{ $shipment->file_no ?? 'MOI-25100001' }}</title>
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
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
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
            width: 860px;
            min-height: 1100px;
            padding: 35px 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            gap: 12px;
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

        .doc-title-box {
            border: 2px solid #1e3a8a;
            padding: 16px 28px;
            text-align: center;
            width: 320px;
            background: #fff;
            color: #1e3a8a;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Sub Header Meta */
        .meta-header-grid {
            display: grid;
            grid-template-columns: 240px 180px 1fr;
            gap: 10px;
            align-items: center;
        }

        .meta-box {
            border: 1px solid #777;
            padding: 4px 6px;
            background: #fff;
        }
        .meta-label {
            font-size: 9px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
        }
        .meta-input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 11px;
            font-weight: bold;
            font-family: inherit;
        }

        /* Main Form Layout Grid */
        .main-form-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 12px;
        }

        /* Left Side Partner Boxes */
        .left-boxes {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .partner-box {
            border: 1px solid #777;
            padding: 6px;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .partner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .partner-select {
            width: 100%;
            border: 1px solid #cbd5e1;
            padding: 2px 4px;
            font-size: 10px;
            outline: none;
        }

        .partner-textarea {
            width: 100%;
            height: 60px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            font-size: 10px;
            font-family: inherit;
            resize: none;
            outline: none;
        }

        /* Right Side Data Table */
        .right-info-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #777;
        }
        .right-info-table td {
            border: 1px solid #777;
            padding: 4px 6px;
            font-size: 10px;
        }
        .right-info-table td.label {
            background: #f8fafc;
            font-weight: bold;
            color: #334155;
            width: 90px;
            text-transform: uppercase;
        }

        .cell-input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 10px;
            font-family: inherit;
            background: transparent;
        }

        /* Inner Container Subtable */
        .cntr-subtable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .cntr-subtable th, .cntr-subtable td {
            border: 1px solid #cbd5e1;
            padding: 4px;
            font-size: 9px;
            text-align: left;
        }
        .cntr-subtable th {
            background: #f1f5f9;
            font-weight: bold;
        }

        /* Cargo Table */
        .cargo-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #777;
        }
        .cargo-table th, .cargo-table td {
            border: 1px solid #777;
            padding: 6px;
            font-size: 10px;
            text-align: left;
            vertical-align: top;
        }
        .cargo-table th {
            background: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Footer Grid */
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }

        .footer-box {
            border: 1px solid #777;
            padding: 8px;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        @media print {
            .top-bar { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .doc-wrapper { padding: 0 !important; }
            .doc-page { box-shadow: none !important; width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body x-data="deliveryOrderApp()" x-cloak>

    <!-- Top Toolbar -->
    <div class="top-bar">
        <button class="bar-btn" title="Download PDF" @click="window.print()"><i class="fa fa-file-pdf-o"></i></button>
        <button class="bar-btn" title="Print" @click="window.print()"><i class="fa fa-print"></i></button>
        <button class="bar-btn" title="Send Email" @click="openEmailModal()"><i class="fa fa-envelope"></i> Email</button>
        
        <div style="display:flex; align-items:center; gap:6px; color:#fff; font-size:12px;">
            <span>Data Source :</span>
            <select class="top-select" x-model="dataSource">
                <option value="Shipment">Shipment</option>
                <option value="House BL">House BL</option>
            </select>
        </div>

        <label style="display:flex; align-items:center; gap:6px; color:red; font-weight:bold; font-size:12px; cursor:pointer;">
            <input type="checkbox" x-model="showContainerRider"> Show Container Rider
        </label>
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

                <div class="doc-title-box">
                    DELIVERY ORDER
                </div>
            </div>

            <!-- Sub Header Meta -->
            <div class="meta-header-grid">
                <div class="meta-box">
                    <div class="meta-label">DATE</div>
                    <input type="text" class="meta-input" value="{{ date('m-d-Y') }}">
                </div>

                <div class="meta-box">
                    <div class="meta-label">OUR Booking No.</div>
                    <input type="text" class="meta-input" value="{{ $shipment->file_no ?? 'MOI-25100001' }}">
                </div>

                <div style="font-size:9px; font-weight:bold; color:#444; border:1px solid #777; padding:6px; background:#f8fafc;">
                    THE MERCHANDISE DESCRIBED BELOW WILL BE ENTERED AND/OR FORWARDED AS FOLLOWS:
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="main-form-grid">
                
                <!-- Left Side Partner Inputs -->
                <div class="left-boxes">
                    
                    <!-- Truker -->
                    <div class="partner-box">
                        <div class="partner-header">
                            <span style="font-weight:bold; color:#555; font-size:9px;">TRUCKER</span>
                            <select class="partner-select" style="width:140px;" x-model="truckerId" @change="updateTrucker()">
                                <option value="">Select...</option>
                                <template x-for="p in partners" :key="p.id">
                                    <option :value="p.id" x-text="p.name"></option>
                                </template>
                            </select>
                        </div>
                        <textarea class="partner-textarea" x-model="truckerText" placeholder="Trucker address..."></textarea>
                    </div>

                    <!-- Pickup -->
                    <div class="partner-box">
                        <div class="partner-header">
                            <span style="font-weight:bold; color:#555; font-size:9px;">PICKUP</span>
                            <div style="display:flex; gap:4px; align-items:center;">
                                <select class="partner-select" style="width:115px;" x-model="pickupId" @change="updatePickup()">
                                    <option value="">Select...</option>
                                    <template x-for="p in partners" :key="p.id">
                                        <option :value="p.id" x-text="p.name"></option>
                                    </template>
                                </select>
                                <button type="button" style="border:1px solid #cbd5e1; background:#2563eb; color:white; border-radius:2px; padding:1px 4px; font-size:10px; cursor:pointer;"><i class="fa fa-pencil"></i></button>
                            </div>
                        </div>
                        <textarea class="partner-textarea" x-model="pickupText" placeholder="Pickup location..."></textarea>
                    </div>

                    <!-- Delivery -->
                    <div class="partner-box">
                        <div class="partner-header">
                            <span style="font-weight:bold; color:#555; font-size:9px;">DELIVERY</span>
                            <select class="partner-select" style="width:140px;" x-model="deliveryId" @change="updateDelivery()">
                                <option value="">Select...</option>
                                <template x-for="p in partners" :key="p.id">
                                    <option :value="p.id" x-text="p.name"></option>
                                </template>
                            </select>
                        </div>
                        <textarea class="partner-textarea" x-model="deliveryText" placeholder="Delivery destination..."></textarea>
                    </div>

                    <!-- Route -->
                    <div class="partner-box">
                        <span style="font-weight:bold; color:#555; font-size:9px;">ROUTE</span>
                        <textarea class="partner-textarea" style="height:45px;" x-model="routeText" placeholder="Routing details..."></textarea>
                    </div>

                    <!-- Bill To -->
                    <div class="partner-box">
                        <div class="partner-header">
                            <label style="display:flex; align-items:center; gap:4px; font-weight:bold; color:#555; font-size:9px; cursor:pointer;">
                                <input type="checkbox" checked style="accent-color:#2563eb;"> BILL TO
                            </label>
                            <select class="partner-select" style="width:140px;" x-model="billToId" @change="updateBillTo()">
                                <option value="">Select...</option>
                                <template x-for="p in partners" :key="p.id">
                                    <option :value="p.id" x-text="p.name"></option>
                                </template>
                            </select>
                        </div>
                        <textarea class="partner-textarea" style="height:65px;" x-model="billToText"></textarea>
                    </div>

                </div>

                <!-- Right Side Table Info -->
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <table class="right-info-table">
                        <tr>
                            <td class="label">MB/L NO.</td>
                            <td class="val"><input type="text" class="cell-input" style="font-weight:bold;" value="{{ $shipment->mbl_no ?? 'MBL55555' }}"></td>
                            <td class="label">HB/L NO.</td>
                            <td class="val"><input type="text" class="cell-input" style="font-weight:bold;" value="HBL2525/5"></td>
                        </tr>
                        <tr>
                            <td class="label">VESSEL</td>
                            <td class="val"><input type="text" class="cell-input" value="{{ $shipment->vessel->name ?? '. ABHIJEET' }}"></td>
                            <td class="label">VOYAGE</td>
                            <td class="val"><input type="text" class="cell-input" value="{{ $shipment->voyage ?? 'FFFFF342' }}"></td>
                        </tr>
                        <tr>
                            <td class="label">AMS B/L NO.</td>
                            <td class="val"><input type="text" class="cell-input" value=""></td>
                            <td class="label">PO NO.</td>
                            <td class="val"><input type="text" class="cell-input" value=""></td>
                        </tr>
                        <tr>
                            <td class="label" colspan="3">CARRIER : <input type="text" class="cell-input" style="width:220px; font-weight:bold; display:inline-block;" value="Carrier Name : CMA CGM (CANADA)"></td>
                            <td class="val">DEL ETA : <input type="text" class="cell-input" style="width:60px; display:inline-block;" value=""></td>
                        </tr>
                        <tr>
                            <td class="label">ORIGIN PORT</td>
                            <td class="val">CHITTAGONG (BANGLADESH)</td>
                            <td class="label">DESTINATION PORT</td>
                            <td class="val">HAMBURG (GERMANY)</td>
                        </tr>
                        <tr>
                            <td class="val" colspan="4">
                                <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                    <input type="checkbox"> Show FINAL DESTINATION
                                </label>
                            </td>
                        </tr>
                    </table>

                    <!-- Container Info Table -->
                    <div style="border:1px solid #777; padding:6px; background:#fff;">
                        <div style="font-weight:bold; font-size:9px; color:#555; text-transform:uppercase; margin-bottom:4px;">CONTAINER INFORMATION</div>
                        <table class="cntr-subtable">
                            <thead>
                                <tr>
                                    <th style="width:25px; text-align:center;"><input type="checkbox" checked></th>
                                    <th>CONTAINER NO.</th>
                                    <th>TYPE</th>
                                    <th>SEAL NO.</th>
                                    <th>WEIGHT</th>
                                    <th>PICKUP NO.</th>
                                    <th>LFD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(c, idx) in containerList" :key="idx">
                                    <tr>
                                        <td style="text-align:center;"><input type="checkbox" x-model="c.selected"></td>
                                        <td><input type="text" class="cell-input" style="font-weight:bold;" x-model="c.container_no"></td>
                                        <td><input type="text" class="cell-input" x-model="c.type"></td>
                                        <td><input type="text" class="cell-input" x-model="c.seal_no"></td>
                                        <td><input type="text" class="cell-input" x-model="c.weight"></td>
                                        <td><input type="text" class="cell-input" x-model="c.pickup_no"></td>
                                        <td><input type="text" class="cell-input" x-model="c.lfd"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Remark Section -->
                    <div style="border:1px solid #777; padding:6px; background:#fff; display:grid; grid-template-columns: 1fr 160px; gap:8px;">
                        <div>
                            <div style="font-weight:bold; font-size:9px; color:#555; text-transform:uppercase; margin-bottom:4px;">REMARK</div>
                            <textarea style="width:100%; height:80px; border:1px solid #cbd5e1; padding:4px; font-size:10px; resize:none; outline:none; font-family:inherit;"></textarea>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:4px; font-size:9px;">
                            <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Appointment needed</label>
                            <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Lift gate needed</label>
                            <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Residential delivery</label>
                            <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Commercial delivery</label>
                            <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Special delivery needed</label>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Cargo Table -->
            <table class="cargo-table">
                <thead>
                    <tr>
                        <th style="width:20%;">MARK</th>
                        <th style="width:35%;">DESCRIPTION</th>
                        <th style="width:15%;">PKGS</th>
                        <th style="width:15%;">WEIGHT</th>
                        <th style="width:15%;">MEASUREMENT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><textarea style="width:100%; height:70px; border:none; outline:none; resize:none; font-size:10px; font-family:inherit;"></textarea></td>
                        <td><textarea style="width:100%; height:70px; border:none; outline:none; resize:none; font-size:10px; font-family:inherit;"></textarea></td>
                        <td><input type="text" class="cell-input" style="font-weight:bold;" value="34 CARTON(S)"></td>
                        <td>
                            <input type="text" class="cell-input" style="font-weight:bold;" value="12,590.00 KGS"><br>
                            <input type="text" class="cell-input" value="27,756.20 LBS">
                        </td>
                        <td>
                            <input type="text" class="cell-input" style="font-weight:bold;" value="128.00 CBM"><br>
                            <input type="text" class="cell-input" value="4,520.27 CFT">
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Footer Grid -->
            <div class="footer-grid">
                
                <!-- Left Box -->
                <div class="footer-box">
                    <div style="font-weight:bold; font-size:9px; color:#333;">P.O.D REQUIRED WITH BILLING INVOICE</div>
                    <div style="font-size:9px; color:#555;">PLEASE FAX PROOF OF DELIVERY TO 999-000-5555</div>
                    <div style="margin-top:10px;">
                        <label style="display:flex; align-items:center; gap:6px; font-weight:bold; font-size:11px; cursor:pointer;">
                            <input type="checkbox"> DO NOT BREAK DOWN PALLET
                        </label>
                    </div>
                </div>

                <!-- Right Box -->
                <div class="footer-box">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:red; font-weight:bold; font-size:10px;">ORIGINAL DELIVERY ORDER</span>
                        <div style="display:flex; align-items:center; gap:4px; font-weight:bold; font-size:10px;">
                            <span>INLAND FREIGHT :</span>
                            <select style="border:1px solid #cbd5e1; font-size:10px; padding:1px 4px; outline:none;">
                                <option value="PREPAID">PREPAID</option>
                                <option value="COLLECT">COLLECT</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-top:4px; font-weight:bold; font-size:11px;">FREIGHTX</div>
                    
                    <div style="display:flex; gap:15px; font-size:10px; margin-top:2px;">
                        <div>PREPARED BY : <input type="text" value="DEMO_925" style="border:none; border-bottom:1px solid #777; width:80px; outline:none; font-size:10px;"></div>
                        <div>DATE : <input type="text" value="{{ date('m-d-Y') }}" style="border:none; border-bottom:1px solid #777; width:70px; outline:none; font-size:10px;"></div>
                    </div>

                    <div style="margin-top:8px; font-size:9px; display:flex; flex-direction:column; gap:4px;">
                        <div>CARRIER SIGNATURE / DATE</div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>CARRIER : ____________________</span>
                            <span>DATE : _________</span>
                        </div>
                    </div>

                    <div style="margin-top:4px; font-size:9px; display:flex; flex-direction:column; gap:4px;">
                        <div>RECEIVED IN GOOD ORDER / DATE</div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>BY : ____________________</span>
                            <span>DATE : _________</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Batch Email Modal Integration -->
    <div x-show="showBatchEmailModal" x-cloak style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:99999; display:flex; justify-content:center; align-items:center; margin:0; padding:0;" @click.self="showBatchEmailModal=false">
        <div style="background:#ffffff; width:980px; max-width:92vw; max-height:90vh; border-radius:6px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); display:flex; flex-direction:column; overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; margin:auto;" @click.stop>
            
            <!-- Modal Header -->
            <div style="padding:12px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                <h3 style="font-size:15px; font-weight:600; color:#1e293b; margin:0;">Send Delivery Order Email</h3>
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
        function deliveryOrderApp() {
            return {
                dataSource: 'Shipment',
                showContainerRider: false,
                containerList: {!! json_encode($containerList) !!},
                partners: {!! json_encode($partners) !!},

                truckerId: '',
                truckerText: '',
                pickupId: '',
                pickupText: '',
                deliveryId: '',
                deliveryText: '',
                routeText: '',
                billToId: '',
                billToText: "FREIGHTX\n71246 FISHER ESTATE APT. 970\nNORTH KELSEY, KS 97983-3382\nUNITED STATES",

                showBatchEmailModal: false,
                isSending: false,
                emailForm: {
                    from: 'demo@freightx.com',
                    to: '',
                    subject: '[FREIGHTX] DELIVERY ORDER - {{ $shipment->file_no ?? "MOI-25100001" }}',
                    body: 'Please find attached the Delivery Order for shipment {{ $shipment->file_no ?? "MOI-25100001" }}.\n\nThank you,\nFreightX Team'
                },

                updateTrucker() {
                    let p = this.partners.find(item => item.id == this.truckerId);
                    if (p) this.truckerText = `${p.name}\n${p.billing_address || p.local_address || ''} ${p.city || ''} ${p.state || ''} ${p.zip_code || ''}`;
                },
                updatePickup() {
                    let p = this.partners.find(item => item.id == this.pickupId);
                    if (p) this.pickupText = `${p.name}\n${p.billing_address || p.local_address || ''} ${p.city || ''} ${p.state || ''} ${p.zip_code || ''}`;
                },
                updateDelivery() {
                    let p = this.partners.find(item => item.id == this.deliveryId);
                    if (p) this.deliveryText = `${p.name}\n${p.billing_address || p.local_address || ''} ${p.city || ''} ${p.state || ''} ${p.zip_code || ''}`;
                },
                updateBillTo() {
                    let p = this.partners.find(item => item.id == this.billToId);
                    if (p) this.billToText = `${p.name}\n${p.billing_address || p.local_address || ''} ${p.city || ''} ${p.state || ''} ${p.zip_code || ''}`;
                },

                openEmailModal() {
                    this.showBatchEmailModal = true;
                },

                sendEmail() {
                    this.isSending = true;
                    setTimeout(() => {
                        this.isSending = false;
                        alert('Delivery Order Email sent successfully!');
                        this.showBatchEmailModal = false;
                    }, 800);
                }
            }
        }
    </script>
</body>
</html>
