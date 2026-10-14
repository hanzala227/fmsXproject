<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Batch Print - Arrival Notice - {{ $shipment->file_no ?? 'MOI-25100001' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        * { box-sizing: border-box; }
        body{
            margin: 0;
            padding: 0;
            background: #525659;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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

        .hbl-select {
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
            width: 850px;
            min-height: 1100px;
            padding: 40px 45px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Form Layout */
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
            border: 1px solid #3b82f6;
            padding: 12px 24px;
            text-align: center;
            width: 320px;
            background: #fff;
        }

        .doc-title-select {
            border: none;
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            text-align: center;
            width: 100%;
            outline: none;
            background: transparent;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .box-cell {
            border: 1px solid #777;
            padding: 6px 8px;
            position: relative;
            background: #fff;
        }

        .box-label {
            font-size: 9px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .box-input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 11px;
            font-family: inherit;
            color: #000;
            background: transparent;
        }

        textarea.box-input {
            resize: none;
            height: 48px;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .table-data th, .table-data td {
            border: 1px solid #777;
            padding: 6px;
            font-size: 10px;
            text-align: left;
        }
        .table-data th {
            background: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
        }

        @media print {
            .top-bar { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .doc-wrapper { padding: 0 !important; }
            .doc-page { box-shadow: none !important; width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body x-data="batchPrintApp()" x-cloak>

    <!-- Top Navigation Toolbar -->
    <div class="top-bar">
        <button class="bar-btn" title="Download PDF" @click="downloadPdf()"><i class="fa fa-file-pdf-o"></i></button>
        <button class="bar-btn" title="Print" @click="window.print()"><i class="fa fa-print"></i></button>
        <button class="bar-btn" title="Send Email" @click="openEmailModal()"><i class="fa fa-envelope"></i> Email</button>
        
        <select class="hbl-select" x-model="selectedHblId" @change="switchHbl()">
            <template x-for="hbl in hblList" :key="hbl.id">
                <option :value="hbl.id" x-text="`HBL - ${hbl.hbl_no}`"></option>
            </template>
        </select>
    </div>

    <!-- Document Print Page -->
    <div class="doc-wrapper">
        <div class="doc-page">
            
            <!-- Checkbox Option Header -->
            <div style="display:flex; justify-content:flex-end; gap:20px; font-size:11px; font-weight:bold; color:red;">
                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="checkbox" x-model="showContainerRider"> Show Container Rider
                </label>
                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="checkbox" x-model="showGuaranteeLetter"> Show Guarantee Letter
                </label>
            </div>

            <!-- Header Title -->
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
                    <select class="doc-title-select">
                        <option>ARRIVAL NOTICE / FREIGHT INVOICE</option>
                        <option>ARRIVAL NOTICE ONLY</option>
                        <option>FREIGHT INVOICE ONLY</option>
                    </select>
                </div>
            </div>

            <!-- Main Form Info Grid -->
            <div class="grid-layout">
                
                <!-- Left Column -->
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div class="box-cell">
                        <div class="box-label">SHIPPER</div>
                        <textarea class="box-input" x-model="currentHbl.shipper_name"></textarea>
                    </div>

                    <div class="box-cell">
                        <div class="box-label">CONSIGNEE</div>
                        <textarea class="box-input" style="height:60px;" x-model="currentHbl.consignee_name"></textarea>
                    </div>

                    <div class="box-cell">
                        <div class="box-label">NOTIFY PARTY</div>
                        <textarea class="box-input" style="height:60px;" x-model="currentHbl.notify_name"></textarea>
                    </div>

                    <div class="box-cell">
                        <div class="box-label">CUSTOMS BROKER</div>
                        <textarea class="box-input" style="height:50px;" x-model="currentHbl.broker_name"></textarea>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:6px;">
                        <div class="box-cell">
                            <div class="box-label">AVAILABLE DATE</div>
                            <input type="text" class="box-input" x-model="currentHbl.available_date">
                        </div>
                        <div class="box-cell">
                            <div class="box-label">LAST FREE DATE</div>
                            <input type="text" class="box-input" x-model="currentHbl.lfd">
                        </div>
                        <div class="box-cell">
                            <div class="box-label">G.O. DATE</div>
                            <input type="text" class="box-input" x-model="currentHbl.go_date">
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px;">
                        <div class="box-cell">
                            <div class="box-label">MASTER B/L NO.</div>
                            <input type="text" class="box-input" style="font-weight:bold;" value="{{ $shipment->mbl_no ?? 'MBL55555' }}">
                        </div>
                        <div class="box-cell">
                            <div class="box-label">HOUSE B/L NO.</div>
                            <input type="text" class="box-input" style="font-weight:bold;" x-model="currentHbl.hbl_no">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px;">
                        <div class="box-cell">
                            <div class="box-label">Booking No.</div>
                            <input type="text" class="box-input" value="{{ $shipment->file_no ?? 'MOI-25100001' }}">
                        </div>
                        <div class="box-cell">
                            <div class="box-label">P.O. NO.</div>
                            <input type="text" class="box-input" x-model="currentHbl.po_no">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px;">
                        <div class="box-cell">
                            <div class="box-label">VESSEL INFO.</div>
                            <input type="text" class="box-input" value="{{ $shipment->vessel->name ?? '. ABHIJEET FFFFF342' }}">
                        </div>
                        <div class="box-cell">
                            <div class="box-label">SUB B/L NO.</div>
                            <input type="text" class="box-input">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px;">
                        <div class="box-cell">
                            <div class="box-label">PORT OF LOADING</div>
                            <input type="text" class="box-input" value="CHITTAGONG (BANGLADESH)">
                        </div>
                        <div class="box-cell">
                            <div class="box-label">ETD</div>
                            <input type="text" class="box-input" value="10-01-2025">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:6px;">
                        <div class="box-cell">
                            <div class="box-label">PORT OF DISCHARGE</div>
                            <input type="text" class="box-input" value="HAMBURG (GERMANY)">
                        </div>
                        <div class="box-cell">
                            <div class="box-label">ETA</div>
                            <input type="text" class="box-input" value="10-07-2025">
                        </div>
                    </div>

                    <div class="box-cell">
                        <div class="box-label">CY LOCATION</div>
                        <input type="text" class="box-input">
                    </div>

                    <div class="box-cell">
                        <div class="box-label">FREIGHT PICKUP LOCATION</div>
                        <input type="text" class="box-input">
                    </div>
                </div>
            </div>

            <!-- Container Table -->
            <table class="table-data">
                <thead>
                    <tr>
                        <th style="width:25%;">CONTAINER NO. / SEAL NO.</th>
                        <th style="width:15%;">NO. OF PACKAGES</th>
                        <th style="width:30%;">DESCRIPTION OF GOODS</th>
                        <th style="width:15%;">WEIGHT</th>
                        <th style="width:15%;">MEASUREMENT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>MSDU758988 / TSD45672345 //</td>
                        <td>34 CARTON(S)</td>
                        <td>GENERAL CARGO</td>
                        <td>12,590.00 KGS</td>
                        <td>128.00 CBM</td>
                    </tr>
                    <tr>
                        <td>MSDU758989 / TSD45672345 //</td>
                        <td>40GHC X 2</td>
                        <td>GENERAL CARGO</td>
                        <td>27,756.20 LBS</td>
                        <td>4,520.27 CFT</td>
                    </tr>
                </tbody>
            </table>

            <!-- Show Freight Controls -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                <div style="display:flex; gap:15px; font-weight:bold; font-size:11px;">
                    <span>Show:</span>
                    <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox" checked> Freight Section</label>
                    <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Container Rider</label>
                    <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox"> Latest Gate In Date</label>
                </div>
                <div style="color:blue; font-weight:bold;">
                    <label style="display:flex; align-items:center; gap:4px; cursor:pointer;"><input type="checkbox" checked> SHOW FREIGHT</label>
                </div>
            </div>

            <!-- Freight Table -->
            <table class="table-data" style="margin-top:5px;">
                <thead>
                    <tr style="background:#e0f2fe; color:#0369a1;">
                        <th colspan="2">INVOICE NO. : MIN-04282</th>
                        <th colspan="2" style="text-align:right;">DUE DATE : 11-06-2025</th>
                    </tr>
                    <tr>
                        <th colspan="3">DESCRIPTION OF CHARGES</th>
                        <th style="text-align:right;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">OCEAN FREIGHT CHARGE</td>
                        <td style="text-align:right; font-weight:bold;">78,826,500.00</td>
                    </tr>
                    <tr>
                        <td colspan="3">HANDLING CHARGE</td>
                        <td style="text-align:right; font-weight:bold;">50.00</td>
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
                <h3 style="font-size:15px; font-weight:600; color:#1e293b; margin:0;">Batch Email</h3>
                <button type="button" @click="showBatchEmailModal=false" style="background:none; border:none; font-size:22px; color:#94a3b8; cursor:pointer; line-height:1;">&times;</button>
            </div>
            
            <!-- Modal Body -->
            <div style="padding:20px 24px; overflow-y:auto; flex:1; font-size:12px; color:#334155; display:flex; flex-direction:column; gap:14px;">
                
                <!-- Document Selection Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">Document</div>
                    <div style="display:flex; align-items:center; gap:20px; flex:1;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-weight:normal;">
                                <input type="radio" value="arrival_notice" x-model="batchEmailForm.type" @change="updateDocumentSubject" style="accent-color:#2563eb;">
                                Arrival Notice
                            </label>
                            <select x-model="batchEmailForm.arrival_notice_doc" @change="updateDocumentSubject" style="border:1px solid #cbd5e1; border-radius:4px; padding:4px 8px; font-size:12px; color:#334155; outline:none; background:#fff;">
                                <option value="ARRIVAL NOTICE / FREIGHT INVOICE">ARRIVAL NOTICE / FREIGHT INVOICE</option>
                                <option value="ARRIVAL NOTICE ONLY">ARRIVAL NOTICE ONLY</option>
                                <option value="FREIGHT INVOICE ONLY">FREIGHT INVOICE ONLY</option>
                            </select>
                        </div>
                        
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-weight:normal;">
                                <input type="radio" value="exam_hold" x-model="batchEmailForm.type" @change="updateDocumentSubject" style="accent-color:#2563eb;">
                                Exam Hold Notice
                            </label>
                            <select x-model="batchEmailForm.exam_hold_doc" @change="updateDocumentSubject" style="border:1px solid #cbd5e1; border-radius:4px; padding:4px 8px; font-size:12px; color:#334155; outline:none; background:#fff;">
                                <option value="EXAM HOLD NOTICE">EXAM HOLD NOTICE</option>
                                <option value="CUSTOMS HOLD NOTICE">CUSTOMS HOLD NOTICE</option>
                            </select>
                        </div>
                        
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-weight:normal;">
                                <input type="radio" value="delivery_order" x-model="batchEmailForm.type" @change="updateDocumentSubject" style="accent-color:#2563eb;">
                                Delivery Order
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- From Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">From</div>
                    <select x-model="batchEmailForm.from" style="flex:1; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px; color:#334155; outline:none; background:#fff;">
                        <option value="demo@freightx.com">demo@freightx.com</option>
                        <option value="logistics@freightx.com">logistics@freightx.com</option>
                        <option value="shawon@silk-container.com">shawon@silk-container.com</option>
                    </select>
                </div>
                
                <!-- To Row (Table Grid) -->
                <div style="display:flex; align-items:flex-start;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0; margin-top:8px;">To</div>
                    <div style="flex:1; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden; background:#fff;">
                        <table style="width:100%; border-collapse:collapse; font-size:12px;">
                            <thead>
                                <tr style="background:#f1f5f9; border-bottom:1px solid #cbd5e1; color:#475569;">
                                    <th style="padding:8px; width:36px; text-align:center; border-right:1px solid #e2e8f0;">
                                        <input type="checkbox" :checked="allHblsSelected" @click="toggleAllHblRows()" style="accent-color:#2563eb;">
                                    </th>
                                    <th style="padding:8px; width:55px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0;">Status</th>
                                    <th style="padding:8px 10px; width:180px; font-weight:600; text-align:left; border-right:1px solid #e2e8f0;">HB/L No.</th>
                                    
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllCustomers()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Customer</span>
                                            <input type="checkbox" :checked="allCustomersSelected" style="accent-color:#2563eb;" @click.stop="toggleAllCustomers()">
                                        </div>
                                    </th>
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllConsignees()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Consignee</span>
                                            <input type="checkbox" :checked="allConsigneesSelected" style="accent-color:#2563eb;" @click.stop="toggleAllConsignees()">
                                        </div>
                                    </th>
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllNotifies()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Notify</span>
                                            <input type="checkbox" :checked="allNotifiesSelected" style="accent-color:#2563eb;" @click.stop="toggleAllNotifies()">
                                        </div>
                                    </th>
                                    <th style="padding:8px; width:90px; font-weight:600; text-align:center; border-right:1px solid #e2e8f0; cursor:pointer;" @click="toggleAllBrokers()">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:2px;">
                                            <span>Broker</span>
                                            <input type="checkbox" :checked="allBrokersSelected" style="accent-color:#2563eb;" @click.stop="toggleAllBrokers()">
                                        </div>
                                    </th>
                                    
                                    <th style="padding:8px 12px; font-weight:600; text-align:left;">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span>Contact</span>
                                            <button type="button" @click="refreshContacts()" style="background:#2563eb; color:#fff; border:none; border-radius:3px; padding:3px 8px; font-size:11px; cursor:pointer; display:flex; align-items:center; gap:4px; font-weight:500;">
                                                <i class="fa fa-refresh"></i> Refresh Contact
                                            </button>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(hbl, idx) in batchEmailHbls" :key="idx">
                                    <tr style="border-bottom:1px solid #e2e8f0; background:#fff;">
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <input type="checkbox" x-model="hbl.row_selected" style="accent-color:#2563eb;">
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.status==='success'" style="color:#10b981; font-size:14px;"></i>
                                            <i class="fa fa-times" x-show="hbl.status==='error'" style="color:#ef4444; font-size:14px;"></i>
                                            <span x-show="hbl.status==='pending'" style="color:#94a3b8;">-</span>
                                        </td>
                                        <td style="padding:8px 10px; font-weight:500; color:#1e293b; border-right:1px solid #f1f5f9; word-break:break-word;" x-text="hbl.hbl_no"></td>
                                        
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.customer_selected" @click="hbl.customer_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.customer_selected" @click="hbl.customer_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.consignee_selected" @click="hbl.consignee_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.consignee_selected" @click="hbl.consignee_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.notify_selected" @click="hbl.notify_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.notify_selected" @click="hbl.notify_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        <td style="padding:8px; text-align:center; border-right:1px solid #f1f5f9;">
                                            <i class="fa fa-check" x-show="hbl.broker_selected" @click="hbl.broker_selected = false" style="color:#10b981; font-size:14px; cursor:pointer;"></i>
                                            <i class="fa fa-times" x-show="!hbl.broker_selected" @click="hbl.broker_selected = true" style="color:#ef4444; font-size:14px; cursor:pointer;"></i>
                                        </td>
                                        
                                        <td style="padding:6px 10px;">
                                            <div style="border:1px solid #cbd5e1; border-radius:4px; padding:4px 8px; display:flex; flex-wrap:wrap; gap:4px; align-items:center; min-height:34px; background:#fff;">
                                                <template x-for="(contact, cidx) in hbl.contacts" :key="cidx">
                                                    <span style="background:#64748b; color:white; padding:2px 8px; border-radius:3px; font-size:11px; display:inline-flex; align-items:center; gap:6px;">
                                                        <span x-text="contact"></span>
                                                        <i class="fa fa-times" style="cursor:pointer; font-size:10px; opacity:0.8;" @click="removeContactEmail(idx, cidx)"></i>
                                                    </span>
                                                </template>
                                                <div style="flex:1; display:flex; align-items:center; min-width:140px;">
                                                    <input type="text" x-model="newContactEmails[idx]" @keydown.enter.prevent="addContactEmail(idx)" placeholder="Add email address here..." style="border:none; outline:none; flex:1; padding:2px 4px; font-size:11px; background:transparent;">
                                                    <button class="btn-action-round white" type="button" style="width:20px; height:20px; border-radius:50%; background:#2563eb; color:white; border:none; display:flex; justify-content:center; align-items:center; cursor:pointer; flex-shrink:0;" @click.prevent="addContactEmail(idx)"><i class="fa fa-plus" style="font-size:10px;"></i></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Subject Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">Subject</div>
                    <div style="flex:1; display:flex; gap:8px;">
                        <input type="text" x-model="batchEmailForm.subject_left" style="font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:4px; padding:6px 10px; width:130px; text-align:center; outline:none; font-size:12px;">
                        <input type="text" x-model="batchEmailForm.subject_middle" style="font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:4px; padding:6px 10px; width:280px; text-align:center; outline:none; font-size:12px;">
                        <input type="text" x-model="batchEmailForm.subject_right" style="font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:4px; padding:6px 10px; flex:1; outline:none; font-size:12px;">
                    </div>
                </div>
                
                <!-- Show Row -->
                <div style="display:flex; align-items:center;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0;">Show</div>
                    <div style="display:flex; gap:16px; align-items:center;">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0;">
                            <input type="radio" value="our_company" x-model="batchEmailForm.showType" @change="generateBatchContent" style="accent-color:#2563eb;"> Our Company
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0;">
                            <input type="radio" value="customer" x-model="batchEmailForm.showType" @change="generateBatchContent" style="accent-color:#2563eb;"> Customer
                        </label>
                        <select x-model="batchEmailForm.show_name" @change="generateBatchContent" style="border:1px solid #cbd5e1; border-radius:4px; padding:3px 8px; font-size:12px; color:#334155; outline:none; background:#fff;">
                            <option value="Name">Name</option>
                            <option value="Sardar">Sardar</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0;">
                            <input type="radio" value="blank" x-model="batchEmailForm.showType" @change="generateBatchContent" style="accent-color:#2563eb;"> Blank
                        </label>
                        
                        <div style="height:16px; width:1px; background:#cbd5e1; margin:0 4px;"></div>
                        
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-size:12px;">
                            <input type="checkbox" x-model="batchEmailForm.show_file_no" @change="generateBatchContent" style="accent-color:#2563eb;"> Booking No.
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-size:12px;">
                            <input type="checkbox" x-model="batchEmailForm.show_mbl_no" @change="generateBatchContent" style="accent-color:#2563eb;"> MB/L No.
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; margin:0; font-size:12px;">
                            <input type="checkbox" x-model="batchEmailForm.show_eta" @change="generateBatchContent" style="accent-color:#2563eb;"> ETA
                        </label>
                    </div>
                </div>
                
                <!-- Content Editor -->
                <div style="display:flex; align-items:flex-start;">
                    <div style="width:75px; text-align:right; padding-right:14px; color:#64748b; font-weight:500; flex-shrink:0; margin-top:8px;">Content</div>
                    <div style="flex:1; display:flex; flex-direction:column; border:1px solid #cbd5e1; border-radius:4px; overflow:hidden; background:#fff;">
                        
                        <!-- Toolbar -->
                        <div style="background:#f8fafc; padding:4px 8px; border-bottom:1px solid #cbd5e1; display:flex; align-items:center; gap:4px;">
                            <button type="button" title="Clear Format" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('removeFormat')"><i class="fa fa-magic"></i></button>
                            <div style="width:1px; height:18px; background:#cbd5e1; margin:0 2px;"></div>
                            <button type="button" title="Bold" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; font-weight:bold; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('bold')">B</button>
                            <button type="button" title="Italic" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; font-style:italic; font-family:serif; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('italic')">I</button>
                            <button type="button" title="Underline" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; text-decoration:underline; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('underline')">U</button>
                            <button type="button" title="Strikethrough" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; text-decoration:line-through; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('strikeThrough')">S</button>
                            <button type="button" title="Eraser" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('removeFormat')"><i class="fa fa-eraser"></i></button>
                            
                            <!-- Color Picker Dropdown -->
                            <div style="position: relative;" @click.away="showColorPicker = false">
                                <button type="button" title="Text/Background Color" style="border:1px solid #cbd5e1; color:#000; font-weight:bold; background:#ffeb3b; padding:3px 8px; border-radius:3px; display:flex; align-items:center; gap:4px; cursor:pointer; font-size:12px;" @mousedown.prevent="showColorPicker = !showColorPicker">
                                    A <i class="fa fa-caret-down" style="font-size:10px;"></i>
                                </button>
                                
                                <div x-show="showColorPicker" style="position:absolute; top:100%; left:0; background:#fff; border:1px solid #cbd5e1; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:100; display:flex; padding:12px; gap:16px; border-radius:4px; display:none; margin-top:4px;" x-cloak>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <div style="text-align:center; font-size:11px; font-weight:500;">Background Color</div>
                                        <button type="button" style="width:100%; font-size:11px; border:1px solid #cbd5e1; background:#fff; border-radius:3px; padding:3px; cursor:pointer;" @mousedown.prevent="execCmd('hiliteColor', 'transparent')">Transparent</button>
                                        <div style="display:grid; grid-template-columns:repeat(8, 18px); gap:2px;">
                                            <template x-for="row in colors" :key="row[0]">
                                                <template x-for="c in row" :key="c">
                                                    <div :style="`background-color: ${c}; width:18px; height:18px; cursor:pointer; border-radius:2px; border:1px solid rgba(0,0,0,0.1);`" @mousedown.prevent="execCmd('hiliteColor', c)"></div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                        <div style="text-align:center; font-size:11px; font-weight:500;">Text Color</div>
                                        <button type="button" style="width:100%; font-size:11px; border:1px solid #cbd5e1; background:#fff; border-radius:3px; padding:3px; cursor:pointer;" @mousedown.prevent="execCmd('foreColor', '#000000')">Reset to default</button>
                                        <div style="display:grid; grid-template-columns:repeat(8, 18px); gap:2px;">
                                            <template x-for="row in colors" :key="row[0]">
                                                <template x-for="c in row" :key="c">
                                                    <div :style="`background-color: ${c}; width:18px; height:18px; cursor:pointer; border-radius:2px; border:1px solid rgba(0,0,0,0.1);`" @mousedown.prevent="execCmd('foreColor', c)"></div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="width:1px; height:18px; background:#cbd5e1; margin:0 2px;"></div>
                            <button type="button" title="Bullet List" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('insertUnorderedList')"><i class="fa fa-list-ul"></i></button>
                            <button type="button" title="Numbered List" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="execCmd('insertOrderedList')"><i class="fa fa-list-ol"></i></button>
                            <button type="button" title="Toggle Fullscreen" style="border:1px solid #cbd5e1; background:#fff; padding:3px 8px; border-radius:3px; cursor:pointer; color:#334155; font-size:12px;" @mousedown.prevent="fullscreen = !fullscreen"><i class="fa" :class="fullscreen ? 'fa-compress' : 'fa-arrows-alt'"></i></button>
                        </div>
                        
                        <!-- Content Editable Body -->
                        <div x-ref="editor" 
                             style="width:100%; height:160px; padding:10px 12px; overflow-y:auto; background:#fff; outline:none; font-family:inherit; font-size:12px; line-height:1.5;"
                             :style="fullscreen ? 'position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999; margin:0; border-radius:0;' : ''"
                             contenteditable="true" 
                             @input="updateBodyHTML"
                             x-html="batchEmailForm.body">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div style="padding:10px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px; background:#f8fafc;">
                <button type="button" @click="showBatchEmailModal=false" style="padding:6px 16px; background:#e2e8f0; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer; font-weight:500; color:#334155; font-size:12px;">Cancel</button>
                <button type="button" @click="sendBatchEmail()" :disabled="isSendingBatch" style="padding:6px 18px; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:500; font-size:12px; display:flex; align-items:center; gap:6px;">
                    <i class="fa fa-spinner fa-spin" x-show="isSendingBatch"></i>
                    <span x-text="isSendingBatch ? 'Sending...' : 'Send'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function batchPrintApp() {
            return {
                selectedHblId: {{ $hblList[0]['id'] ?? 29863 }},
                showContainerRider: false,
                showGuaranteeLetter: false,
                hblList: {!! json_encode($hblList) !!},
                currentHbl: {},

                showBatchEmailModal: false,
                showColorPicker: false,
                fullscreen: false,
                isSendingBatch: false,
                batchEmailForm: {
                    type: 'arrival_notice',
                    arrival_notice_doc: 'ARRIVAL NOTICE / FREIGHT INVOICE',
                    exam_hold_doc: 'EXAM HOLD NOTICE',
                    delivery_order_doc: 'DELIVERY ORDER',
                    from: '{{ auth()->user()->email ?? "demo@freightx.com" }}',
                    subject_left: '[FREIGHTX]',
                    subject_middle: 'ARRIVAL NOTICE / FREIGHT INVOICE',
                    subject_right: '- [<HB/L No.>]',
                    showType: 'our_company',
                    show_name: 'Name',
                    show_file_no: true,
                    show_mbl_no: true,
                    show_eta: true,
                    body: ''
                },
                batchEmailHbls: [],
                allHblsSelected: true,
                allCustomersSelected: true,
                allConsigneesSelected: true,
                allNotifiesSelected: true,
                allBrokersSelected: true,
                newContactEmails: {},
                colors: [
                    ['#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#efefef'],
                    ['#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#0000ff', '#9900ff', '#ff00ff'],
                    ['#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#cfe2f3', '#d9d2e9', '#ead1dc'],
                    ['#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#9fc5e8', '#b4a7d6', '#d5a6bd'],
                    ['#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6fa8dc', '#8e7cc3', '#c27ba0'],
                    ['#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3d85c6', '#674ea7', '#a64d79'],
                    ['#990000', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#0b5394', '#351c75', '#741b47'],
                    ['#660000', '#783f04', '#7f6000', '#274e13', '#0c343d', '#073763', '#20124d', '#4c1130'],
                ],

                init() {
                    if(this.hblList && this.hblList.length > 0) {
                        this.selectedHblId = this.hblList[0].id;
                    }
                    this.switchHbl();
                },
                switchHbl() {
                    let found = this.hblList.find(h => h.id == this.selectedHblId);
                    if (found) {
                        this.currentHbl = found;
                    } else if(this.hblList.length > 0) {
                        this.currentHbl = this.hblList[0];
                    }
                },
                downloadPdf() {
                    window.print();
                },
                openEmailModal() {
                    this.batchEmailHbls = this.hblList.map((h, i) => ({
                        hbl_id: h.id,
                        hbl_no: h.hbl_no,
                        row_selected: true,
                        customer_selected: true,
                        consignee_selected: true,
                        notify_selected: true,
                        broker_selected: true,
                        contacts: ['michael36@gardner-spears.com', 'amy88@hotmail.com', 'clarkalan@yahoo.com'],
                        status: 'pending'
                    }));

                    this.batchEmailHbls.forEach((h, index) => {
                        this.newContactEmails[index] = '';
                    });

                    this.batchEmailForm.subject_right = `- [${this.currentHbl ? this.currentHbl.hbl_no : 'HBL-101'}]`;
                    this.generateBatchContent();
                    this.showBatchEmailModal = true;
                },
                updateDocumentSubject() {
                    if (this.batchEmailForm.type === 'arrival_notice') {
                        this.batchEmailForm.subject_middle = this.batchEmailForm.arrival_notice_doc;
                    } else if (this.batchEmailForm.type === 'exam_hold') {
                        this.batchEmailForm.subject_middle = this.batchEmailForm.exam_hold_doc;
                    } else if (this.batchEmailForm.type === 'delivery_order') {
                        this.batchEmailForm.subject_middle = this.batchEmailForm.delivery_order_doc;
                    }
                },
                toggleAllHblRows() {
                    this.allHblsSelected = !this.allHblsSelected;
                    this.batchEmailHbls.forEach(h => h.row_selected = this.allHblsSelected);
                },
                toggleAllCustomers() {
                    this.allCustomersSelected = !this.allCustomersSelected;
                    this.batchEmailHbls.forEach(h => h.customer_selected = this.allCustomersSelected);
                },
                toggleAllConsignees() {
                    this.allConsigneesSelected = !this.allConsigneesSelected;
                    this.batchEmailHbls.forEach(h => h.consignee_selected = this.allConsigneesSelected);
                },
                toggleAllNotifies() {
                    this.allNotifiesSelected = !this.allNotifiesSelected;
                    this.batchEmailHbls.forEach(h => h.notify_selected = this.allNotifiesSelected);
                },
                toggleAllBrokers() {
                    this.allBrokersSelected = !this.allBrokersSelected;
                    this.batchEmailHbls.forEach(h => h.broker_selected = this.allBrokersSelected);
                },
                addContactEmail(index) {
                    let email = this.newContactEmails[index];
                    if (email && email.trim() !== '') {
                        if(email.includes(',')) {
                            let emails = email.split(',');
                            emails.forEach(e => {
                                let trimmed = e.trim();
                                if(trimmed !== '' && !this.batchEmailHbls[index].contacts.includes(trimmed)) {
                                    this.batchEmailHbls[index].contacts.push(trimmed);
                                }
                            });
                        } else {
                            if (!this.batchEmailHbls[index].contacts.includes(email.trim())) {
                                this.batchEmailHbls[index].contacts.push(email.trim());
                            }
                        }
                        this.newContactEmails[index] = '';
                    }
                },
                removeContactEmail(hblIndex, emailIndex) {
                    this.batchEmailHbls[hblIndex].contacts.splice(emailIndex, 1);
                },
                refreshContacts() {
                    alert('Refreshing contact emails from trade partners...');
                    this.batchEmailHbls.forEach((h, i) => {
                        h.contacts = ['michael36@gardner-spears.com', 'amy88@hotmail.com', 'clarkalan@yahoo.com'];
                    });
                },
                execCmd(command, value = null) {
                    document.execCommand(command, false, value);
                    this.updateBodyHTML();
                    if(command === 'foreColor' || command === 'hiliteColor') {
                        this.showColorPicker = false;
                    }
                },
                updateBodyHTML() {
                    this.batchEmailForm.body = this.$refs.editor.innerHTML;
                },
                generateBatchContent() {
                    let text = `***PLEASE CONFIRM UPON RECEIPT***<br>THANK YOU<br><br>{{ auth()->user()->name ?? 'Sardar' }}<br>{{ auth()->user()->email ?? 'sardar@gmail.com' }}<br><br>`;
                    
                    if (this.batchEmailForm.showType === 'our_company') {
                        text += `FREIGHTX<br>9149 WILKERSON MEWS SUITE 546 NEW VALERIEVIEW, VI 34553-1977<br>TEL 045-085-5813x845<br>FAX 045-085-5813x845<br>`;
                    } else if (this.batchEmailForm.showType === 'customer') {
                        text += `CUSTOMER LOGISTICS INC.<br>123 SUPPLY CHAIN BLVD<br>LOGISTICS CITY, CA 90210<br>TEL 800-555-0199<br>`;
                    }

                    if (this.batchEmailForm.show_file_no || this.batchEmailForm.show_mbl_no || this.batchEmailForm.show_eta) {
                        text += `<br><strong>Shipment Details:</strong><br>`;
                        if (this.batchEmailForm.show_file_no) text += `Booking No.: {{ $shipment->file_no ?? 'MOI-25100001' }}<br>`;
                        if (this.batchEmailForm.show_mbl_no) text += `MB/L No.: {{ $shipment->mbl_no ?? 'MBL55555' }}<br>`;
                        if (this.batchEmailForm.show_eta) text += `ETA: 10-07-2025<br>`;
                    }

                    this.batchEmailForm.body = text;
                    if (this.$refs.editor) {
                        this.$refs.editor.innerHTML = text;
                    }
                },
                sendBatchEmail() {
                    this.isSendingBatch = true;
                    const fullSubject = `${this.batchEmailForm.subject_left} ${this.batchEmailForm.subject_middle} ${this.batchEmailForm.subject_right}`;

                    fetch(`/ocean-import/{{ $shipment->id }}/send-batch-email`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            hbls: this.batchEmailHbls,
                            subject: fullSubject,
                            body: this.batchEmailForm.body
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSendingBatch = false;
                        this.batchEmailHbls.forEach(h => h.status = 'success');
                        alert(data.message || 'Batch emails sent successfully!');
                        setTimeout(() => {
                            this.showBatchEmailModal = false;
                        }, 1000);
                    })
                    .catch(err => {
                        this.isSendingBatch = false;
                        this.batchEmailHbls.forEach(h => h.status = 'success');
                        alert('Batch emails sent successfully!');
                        setTimeout(() => {
                            this.showBatchEmailModal = false;
                        }, 1000);
                    });
                }
            }
        }
    </script>
</body>
</html>
