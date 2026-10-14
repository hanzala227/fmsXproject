<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document Package - {{ $shipment->file_no ?? 'Air Export' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            color: #1e293b; 
            margin: 0; 
            padding: 20px 30px; 
            font-size: 11px; 
            line-height: 1.4; 
            background: #f8fafc;
        }
        .report-page {
            max-width: 1050px; 
            margin: 0 auto 40px auto; 
            background: #fff;
            padding: 35px 40px;
            border-radius: 4px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            page-break-after: always;
        }

        .controls-bar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #ffffff;
            border-bottom: 2px solid #3b82f6;
            padding: 12px 24px;
            margin: -20px -30px 25px -30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .controls-left {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 12px;
            color: #334155;
        }

        .controls-left select {
            padding: 4px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 12px;
            background: #fff;
            color: #1e293b;
            font-weight: 600;
        }

        .controls-left label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-toolbar {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            background: #3b82f6; 
            color: #fff; 
            border: none; 
            padding: 7px 15px; 
            border-radius: 4px; 
            font-weight: 600; 
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(59,130,246,0.3);
            transition: all 0.2s;
        }
        .btn-action:hover { background: #2563eb; }

        /* Report Layout Components */
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .company-info h2 {
            margin: 0 0 4px 0;
            font-size: 20px;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .company-info p {
            margin: 1px 0;
            color: #475569;
            font-size: 11px;
        }

        .manifest-title-box {
            border: 2px solid #0f172a;
            color: #0f172a;
            font-size: 18px;
            font-weight: 800;
            padding: 10px 45px;
            text-align: center;
            letter-spacing: 1px;
            background: #f8fafc;
        }

        .to-from-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .address-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            overflow: hidden;
        }
        .address-card-header {
            background: #f1f5f9;
            padding: 6px 12px;
            font-weight: 700;
            color: #334155;
            font-size: 11px;
            border-bottom: 1px solid #cbd5e1;
        }
        .address-card-body {
            padding: 10px 12px;
            min-height: 70px;
            color: #0f172a;
            line-height: 1.5;
            font-size: 11px;
        }

        /* Tables */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            font-size: 11px;
        }
        .info-table td.label {
            background-color: #f1f5f9;
            font-weight: 700;
            width: 18%;
            color: #334155;
        }
        .info-table td.value {
            width: 32%;
            background-color: #ffffff;
            color: #0f172a;
            font-weight: 600;
        }

        .cargo-table-wrapper {
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            overflow: hidden;
        }

        .table-options-bar {
            background: #f1f5f9;
            padding: 8px 12px;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #334155;
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-options-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cargo-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cargo-table th {
            background: #e2e8f0;
            color: #1e293b;
            padding: 8px 6px;
            font-size: 10px;
            font-weight: 700;
            text-align: left;
            border-bottom: 1.5px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
        }
        .cargo-table th:last-child { border-right: none; }
        .cargo-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }
        .cargo-table td:last-child { border-right: none; }
        .cargo-table tr:hover { background-color: #f8fafc; }

        .remark-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px 14px;
            background: #ffffff;
            min-height: 60px;
        }
        .remark-title {
            font-weight: 700;
            color: #475569;
            margin-bottom: 4px;
            font-size: 10px;
        }

        .section-divider {
            border-top: 2px dashed #94a3b8;
            margin: 40px 0;
            position: relative;
        }
        .section-divider-tag {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #64748b;
            color: #fff;
            padding: 2px 14px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        @media print {
            .controls-bar { display: none !important; }
            body { padding: 0; background: #fff; }
            .report-page { 
                box-shadow: none; 
                border: none; 
                padding: 0; 
                margin: 0;
                max-width: 100%;
            }
            .section-divider { display: none; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

<div x-data="docPackageApp()" x-cloak>
    <!-- Controls Bar (Ocean Import UI Standard) -->
    <div class="controls-bar">
        <div class="controls-left">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-weight: 700; color: #475569;">Data Source:</span>
                <select x-model="dataSource">
                    <option value="shipment">Shipment</option>
                </select>
            </div>

            <label>
                <input type="checkbox" x-model="showAddressInfo">
                <span>Show Address & Contact Info</span>
            </label>

            <label>
                <input type="checkbox" x-model="showAgentInfo">
                <span>Show Agent Info</span>
            </label>
        </div>

        <div class="btn-toolbar">
            <button class="btn-action" @click="window.print()"><i class="fa fa-print"></i> Print</button>
            <button class="btn-action" @click="window.print()"><i class="fa fa-file-pdf-o"></i> Save PDF</button>
        </div>
    </div>

    <!-- MAIN REPORT PAGES CONTAINER -->
    <div style="max-width: 1050px; margin: 0 auto;">

        <!-- 1. MAWB EXPORT MANIFEST -->
        <template x-if="selectedReports.includes('manifest')">
            <div class="report-page">
                <!-- Header Top -->
                <div class="header-top">
                    <div class="company-info">
                        <h2>FREIGHTX</h2>
                        <div x-show="showAddressInfo">
                            <p>9149 WILKERSON MEWS SUITE 546</p>
                            <p>NEW VALERIEVIEW, VI 34553-1977</p>
                            <p>TEL: 045-085-5813x845 FAX: 045-085-5813x845</p>
                        </div>
                        <p style="margin-top: 6px; font-weight: 700; color: #3b82f6;">
                            Prepared by {{ auth()->user()->name ?? 'DEMO_925' }} {{ date('m-d-Y H:i') }} (PDT)
                        </p>
                    </div>
                    <div class="manifest-title-box">
                        CARGO MANIFEST
                    </div>
                </div>

                <!-- TO / FROM Section -->
                <div class="to-from-grid">
                    <div class="address-card" x-show="showAgentInfo">
                        <div class="address-card-header">
                            TO: <span x-text="agentType === 'master' ? 'MASTER AGENT' : 'SUB AGENT'"></span>
                        </div>
                        <div class="address-card-body">
                            <strong x-text="agentType === 'master' ? '{{ addslashes($shipment->overseaAgent->name ?? "SHAWON LOGISTICS CO., LTD") }}' : '{{ addslashes($shipment->forwardingAgent->name ?? "SUB AGENT LOGISTICS INC.") }}'"></strong><br>
                            <div x-show="showAddressInfo" style="margin-top: 4px; color: #475569;">
                                123 FREIGHT TOWER, SUITE 800<br>
                                INTERNATIONAL LOGISTICS PARK<br>
                                TEL: +1 800 555 0199 FAX: +1 800 555 0198
                            </div>
                        </div>
                    </div>

                    <div class="address-card">
                        <div class="address-card-header">FROM: FREIGHTX LOGISTICS</div>
                        <div class="address-card-body">
                            <strong>FREIGHTX LOGISTICS INC.</strong><br>
                            <div x-show="showAddressInfo" style="margin-top: 4px; color: #475569;">
                                9149 WILKERSON MEWS SUITE 546<br>
                                NEW VALERIEVIEW, VI 34553-1977<br>
                                TEL: 045-085-5813x845 FAX: 045-085-5813x845
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipment Master Info Grid -->
                <table class="info-table">
                    <tr>
                        <td class="label">MAWB NO.</td>
                        <td class="value">{{ $shipment->mawb_no ?? '02605203306' }}</td>
                        <td class="label">Booking No.</td>
                        <td class="value" style="color: #2563eb; font-weight: 800;">{{ $shipment->file_no ?? 'AE-2026001' }}</td>
                    </tr>
                    <tr>
                        <td class="label">FLIGHT NO.</td>
                        <td class="value">{{ $shipment->flight_no ?? 'CI006' }}</td>
                        <td class="label">CARRIER</td>
                        <td class="value">{{ $shipment->carrier->name ?? 'CHINA AIRLINES' }}</td>
                    </tr>
                    <tr>
                        <td class="label">DEPARTURE</td>
                        <td class="value">{{ $shipment->depPort->name ?? 'TAIPEI (TPE)' }}</td>
                        <td class="label">ETD</td>
                        <td class="value">{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : date('m-d-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">DESTINATION</td>
                        <td class="value">{{ $shipment->dstPort->name ?? 'LOS ANGELES (LAX)' }}</td>
                        <td class="label">ETA</td>
                        <td class="value">{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : date('m-d-Y') }}</td>
                    </tr>
                </table>

                <!-- HAWB Details Table Section -->
                <div class="cargo-table-wrapper">
                    <div class="table-options-bar">
                        <div class="table-options-group">
                            <span style="font-weight: 700; color: #1e293b;">Shipper Info:</span>
                            <label><input type="checkbox" x-model="showShipperPhone"> Phone</label>
                            <label><input type="checkbox" x-model="showShipperFax"> Fax</label>
                            <label><input type="checkbox" x-model="showShipperContact"> Contact</label>
                        </div>
                        <div class="table-options-group">
                            <span style="font-weight: 700; color: #1e293b;">Consignee Info:</span>
                            <label><input type="checkbox" x-model="showConsigneePhone"> Phone</label>
                            <label><input type="checkbox" x-model="showConsigneeFax"> Fax</label>
                            <label><input type="checkbox" x-model="showConsigneeContact"> Contact</label>
                        </div>
                        <div>
                            <label style="font-weight: 700; color: #2563eb;"><input type="checkbox" x-model="showMeasurement"> Show Measurement</label>
                        </div>
                    </div>

                    <table class="cargo-table">
                        <thead>
                            <tr>
                                <th style="width: 13%;">HAWB NO</th>
                                <th style="width: 7%; text-align: center;">PCS</th>
                                <th style="width: 10%; text-align: right;">WEIGHT</th>
                                <th style="width: 10%; text-align: right;" x-show="showMeasurement">MEASUREMENT</th>
                                <th style="width: 25%;">SHIPPERS NAME & ADDRESS</th>
                                <th style="width: 25%;">CONSIGNEE NAME & ADDRESS</th>
                                <th style="width: 12%;">NATURE OF GOODS</th>
                                <th style="width: 8%; text-align: center;">TERM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipment->hbls as $hbl)
                                <tr>
                                    <td style="font-weight: 700; color: #0f172a;">{{ $hbl->hawb_no ?? 'HAWB-'. $hbl->id }}</td>
                                    <td style="text-align: center; font-weight: 600;">{{ $hbl->pkg_qty ?? 1 }}</td>
                                    <td style="text-align: right; font-weight: 600;">
                                        {{ number_format((float)($hbl->gross_weight ?? 100), 2) }} KG
                                    </td>
                                    <td style="text-align: right; font-weight: 600; color: #2563eb;" x-show="showMeasurement">
                                        {{ number_format((float)($hbl->volume ?? 0.5), 2) }} CBM
                                    </td>
                                    <td>
                                        <strong>{{ $hbl->shipper->name ?? 'GLOBAL EXPORT CORP' }}</strong>
                                        <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                            INDUSTRIAL ZONE 5, TAIPEI TAIWAN
                                            <div style="margin-top: 2px;">
                                                <span x-show="showShipperPhone">TEL: 886-2-2345678 </span>
                                                <span x-show="showShipperFax">FAX: 886-2-2345679 </span>
                                                <span x-show="showShipperContact">ATTN: JOHN CHIANG</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $hbl->consignee->name ?? 'PACIFIC IMPORT DISTRIBUTORS' }}</strong>
                                        <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                            100 HARBOR BLVD, LOS ANGELES CA
                                            <div style="margin-top: 2px;">
                                                <span x-show="showConsigneePhone">TEL: 1-310-5550122 </span>
                                                <span x-show="showConsigneeFax">FAX: 1-310-5550123 </span>
                                                <span x-show="showConsigneeContact">ATTN: MARY SMITH</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $hbl->commodity ?? 'ELECTRONIC PARTS' }}</td>
                                    <td style="text-align: center; font-weight: 700; color: #059669;">{{ $hbl->freight_term ?? 'Prepaid' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td style="font-weight: 700; color: #0f172a;">HAWB881923</td>
                                    <td style="text-align: center; font-weight: 600;">25</td>
                                    <td style="text-align: right; font-weight: 600;">450.00 KG</td>
                                    <td style="text-align: right; font-weight: 600; color: #2563eb;" x-show="showMeasurement">1.85 CBM</td>
                                    <td>
                                        <strong>TAIWAN HIGH TECH CORP.</strong>
                                        <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                            NO. 88 SCIENCE PARK ROAD, HSINCHU
                                            <div style="margin-top: 2px;">
                                                <span x-show="showShipperPhone">TEL: +886 3 578 9000 </span>
                                                <span x-show="showShipperFax">FAX: +886 3 578 9001 </span>
                                                <span x-show="showShipperContact">ATTN: KEVIN CHEN</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>CALIFORNIA LOGISTICS HUB INC.</strong>
                                        <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                            450 LOGISTICS WAY, COMPTON CA 90220
                                            <div style="margin-top: 2px;">
                                                <span x-show="showConsigneePhone">TEL: +1 310 600 4000 </span>
                                                <span x-show="showConsigneeFax">FAX: +1 310 600 4001 </span>
                                                <span x-show="showConsigneeContact">ATTN: DAVID MILLER</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>COMPUTER COMPONENTS</td>
                                    <td style="text-align: center; font-weight: 700; color: #059669;">Prepaid</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Remark Box -->
                <div class="remark-box">
                    <div class="remark-title">REMARK</div>
                    <div style="color: #334155; font-size: 11px;">
                        PLEASE RELEASE CARGO ONLY AGAINST ORIGINAL HAWB OR AGENT AUTHORIZATION. ALL CHARGES PREPAID AS ARRANGED.
                    </div>
                </div>
            </div>
        </template>


        <!-- 2. MAWB PRINT -->
        <template x-if="selectedReports.includes('mawb_print')">
            <div>
                <div class="section-divider" x-show="selectedReports.includes('manifest')"><span class="section-divider-tag">DOCUMENT 2: MAWB PRINT</span></div>
                <div class="report-page">
                    <div class="header-top">
                        <div class="company-info">
                            <h2>CHINA AIRLINES</h2>
                            <p>MASTER AIR WAYBILL - {{ $shipment->mawb_no ?? '02605203306' }}</p>
                        </div>
                        <div class="manifest-title-box" style="font-size: 15px; padding: 6px 20px;">
                            ORIGINAL MAWB
                        </div>
                    </div>
                    <table class="info-table">
                        <tr>
                            <td class="label">SHIPPER</td>
                            <td class="value">{{ $shipment->shipper->name ?? 'FREIGHTX LOGISTICS INC.' }}</td>
                            <td class="label">MAWB NO.</td>
                            <td class="value" style="font-weight: 800; color: #2563eb;">{{ $shipment->mawb_no ?? '02605203306' }}</td>
                        </tr>
                        <tr>
                            <td class="label">CONSIGNEE</td>
                            <td class="value" x-text="agentType === 'master' ? '{{ addslashes($shipment->overseaAgent->name ?? "SHAWON LOGISTICS CO., LTD") }}' : '{{ addslashes($shipment->forwardingAgent->name ?? "SUB AGENT LOGISTICS INC.") }}'"></td>
                            <td class="label">AIRPORT DEPARTURE</td>
                            <td class="value">{{ $shipment->depPort->name ?? 'TAIPEI (TPE)' }}</td>
                        </tr>
                        <tr>
                            <td class="label">ISSUING CARRIER</td>
                            <td class="value">{{ $shipment->carrier->name ?? 'CHINA AIRLINES' }}</td>
                            <td class="label">AIRPORT DESTINATION</td>
                            <td class="value">{{ $shipment->dstPort->name ?? 'LOS ANGELES (LAX)' }}</td>
                        </tr>
                    </table>
                    <div style="border: 1px solid #cbd5e1; padding: 20px; text-align: center; color: #64748b; background: #f8fafc; border-radius: 4px;">
                        <i class="fa fa-file-text-o" style="font-size: 32px; margin-bottom: 10px; color: #3b82f6;"></i>
                        <div style="font-size: 14px; font-weight: 700; color: #1e293b;">STANDARD IATA AIR WAYBILL FORMAT</div>
                        <div>ISSUED BY CHINA AIRLINES - CARRIER CODE 026</div>
                    </div>
                </div>
            </div>
        </template>


        <!-- 3. LOCAL INVOICE -->
        <template x-if="selectedReports.includes('local_invoice')">
            <div>
                <div class="section-divider" x-show="selectedReports.length > 1"><span class="section-divider-tag">DOCUMENT 3: LOCAL INVOICE</span></div>
                <div class="report-page">
                    <div class="header-top">
                        <div class="company-info">
                            <h2>FREIGHTX</h2>
                            <p>INVOICE NO: INV-AE2026-001</p>
                            <p>DATE: {{ date('m-d-Y') }}</p>
                        </div>
                        <div class="manifest-title-box" style="background: #eff6ff; color: #1d4ed8; border-color: #3b82f6;">
                            LOCAL INVOICE
                        </div>
                    </div>
                    <table class="info-table">
                        <tr>
                            <td class="label">BILL TO</td>
                            <td class="value"><strong>{{ $shipment->dmCustomer->name ?? 'PACIFIC LOGISTICS CORP' }}</strong></td>
                            <td class="label">Booking No.</td>
                            <td class="value">{{ $shipment->file_no ?? 'AE-2026001' }}</td>
                        </tr>
                        <tr>
                            <td class="label">MAWB NO.</td>
                            <td class="value">{{ $shipment->mawb_no ?? '02605203306' }}</td>
                            <td class="label">FLIGHT / ETD</td>
                            <td class="value">{{ $shipment->flight_no ?? 'CI006' }} / {{ $shipment->etd ? $shipment->etd->format('m-d-Y') : date('m-d-Y') }}</td>
                        </tr>
                    </table>
                    <table class="cargo-table" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>DESCRIPTION</th>
                                <th style="text-align: center;">RATE</th>
                                <th style="text-align: center;">QTY</th>
                                <th style="text-align: right;">AMOUNT (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>AIR FREIGHT CHARGE</td>
                                <td style="text-align: center;">2.50</td>
                                <td style="text-align: center;">450 KG</td>
                                <td style="text-align: right; font-weight: 700;">$1,125.00</td>
                            </tr>
                            <tr>
                                <td>FUEL SURCHARGE (FSC)</td>
                                <td style="text-align: center;">0.65</td>
                                <td style="text-align: center;">450 KG</td>
                                <td style="text-align: right; font-weight: 700;">$292.50</td>
                            </tr>
                            <tr>
                                <td>SECURITY CHARGE (SCC)</td>
                                <td style="text-align: center;">0.15</td>
                                <td style="text-align: center;">450 KG</td>
                                <td style="text-align: right; font-weight: 700;">$67.50</td>
                            </tr>
                            <tr>
                                <td>DOCUMENTATION FEE</td>
                                <td style="text-align: center;">75.00</td>
                                <td style="text-align: center;">1 SET</td>
                                <td style="text-align: right; font-weight: 700;">$75.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f1f5f9; font-weight: 800; font-size: 12px;">
                                <td colspan="3" style="text-align: right;">TOTAL INVOICE AMOUNT:</td>
                                <td style="text-align: right; color: #1d4ed8;">$1,560.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </template>


        <!-- 4. CREDIT/DEBIT NOTE -->
        <template x-if="selectedReports.includes('credit_debit')">
            <div>
                <div class="section-divider"><span class="section-divider-tag">DOCUMENT 4: CREDIT / DEBIT NOTE</span></div>
                <div class="report-page">
                    <div class="header-top">
                        <div class="company-info">
                            <h2>FREIGHTX</h2>
                            <p>NOTE NO: CDN-2026-088</p>
                        </div>
                        <div class="manifest-title-box">
                            CREDIT / DEBIT NOTE
                        </div>
                    </div>
                    <div style="padding: 15px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; margin-top: 15px;">
                        <div style="font-weight: 700; color: #334155; margin-bottom: 8px;">AGENT STATEMENT SUMMARY</div>
                        <table class="cargo-table">
                            <thead>
                                <tr>
                                    <th>TYPE</th>
                                    <th>DESCRIPTION</th>
                                    <th style="text-align: right;">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="color: #dc2626; font-weight: 700;">DEBIT</td>
                                    <td>AIR FREIGHT PREPAID COMMISSION</td>
                                    <td style="text-align: right; font-weight: 700;">$320.00</td>
                                </tr>
                                <tr>
                                    <td style="color: #059669; font-weight: 700;">CREDIT</td>
                                    <td>PROFIT SHARE (50/50)</td>
                                    <td style="text-align: right; font-weight: 700;">$185.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>


        <!-- 5. HAWB PRINT -->
        <template x-if="selectedReports.includes('hawb_print')">
            <div>
                <div class="section-divider"><span class="section-divider-tag">DOCUMENT 5: HAWB PRINT</span></div>
                <div class="report-page">
                    <div class="header-top">
                        <div class="company-info">
                            <h2>HOUSE AIR WAYBILL</h2>
                            <p>HAWB NO: {{ $shipment->hbls->first()->hawb_no ?? 'HAWB881923' }}</p>
                        </div>
                        <div class="manifest-title-box">
                            HAWB PRINT
                        </div>
                    </div>
                    <div style="border: 1px solid #cbd5e1; padding: 25px; text-align: center; color: #475569; background: #f8fafc; border-radius: 4px;">
                        <i class="fa fa-plane" style="font-size: 36px; color: #3b82f6; margin-bottom: 10px;"></i>
                        <div style="font-size: 15px; font-weight: 700; color: #0f172a;">HOUSE AIR WAYBILL COPY</div>
                        <div>CONSIGNEE: PACIFIC IMPORT DISTRIBUTORS</div>
                    </div>
                </div>
            </div>
        </template>


        <!-- 6. COMMERCIAL INVOICE -->
        <template x-if="selectedReports.includes('commercial_invoice')">
            <div>
                <div class="section-divider"><span class="section-divider-tag">DOCUMENT 6: COMMERCIAL INVOICE</span></div>
                <div class="report-page">
                    <div class="header-top">
                        <div class="company-info">
                            <h2>COMMERCIAL INVOICE</h2>
                            <p>INVOICE NO: CI-TAIWAN-991</p>
                        </div>
                        <div class="manifest-title-box">
                            COMMERCIAL INVOICE
                        </div>
                    </div>
                    <div style="padding: 15px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px;">
                        <p><strong>SHIPPER:</strong> TAIWAN HIGH TECH CORP.</p>
                        <p><strong>CONSIGNEE:</strong> CALIFORNIA LOGISTICS HUB INC.</p>
                        <p><strong>GOODS DESCRIPTION:</strong> COMPUTER COMPONENTS & ACCESSORIES</p>
                        <p><strong>TOTAL VALUE:</strong> USD $48,500.00</p>
                    </div>
                </div>
            </div>
        </template>


        <!-- 7. PACKING LIST -->
        <template x-if="selectedReports.includes('packing_list')">
            <div>
                <div class="section-divider"><span class="section-divider-tag">DOCUMENT 7: PACKING LIST</span></div>
                <div class="report-page">
                    <div class="header-top">
                        <div class="company-info">
                            <h2>PACKING LIST</h2>
                            <p>REF NO: PL-TAIWAN-991</p>
                        </div>
                        <div class="manifest-title-box">
                            PACKING LIST
                        </div>
                    </div>
                    <table class="cargo-table" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>CARTON NO</th>
                                <th>CONTENTS</th>
                                <th style="text-align: center;">QTY</th>
                                <th style="text-align: right;">GROSS WT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CTN 01 - 10</td>
                                <td>MAIN BOARD UNITS</td>
                                <td style="text-align: center;">10 CTNS</td>
                                <td style="text-align: right;">180.00 KG</td>
                            </tr>
                            <tr>
                                <td>CTN 11 - 25</td>
                                <td>POWER SUPPLY UNITS</td>
                                <td style="text-align: center;">15 CTNS</td>
                                <td style="text-align: right;">270.00 KG</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>


    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('docPackageApp', () => ({
            dataSource: 'shipment',
            showAddressInfo: true,
            showAgentInfo: true,
            showShipperPhone: true,
            showShipperFax: true,
            showShipperContact: true,
            showConsigneePhone: true,
            showConsigneeFax: true,
            showConsigneeContact: true,
            showMeasurement: false,
            selectedReports: @json($selectedReports),
            agentType: '{{ $agentType }}'
        }));
    });
</script>
</body>
</html>
