<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cargo Manifest - {{ $shipment->file_no ?? $shipment->mawb_no ?? 'FreightX' }}</title>
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

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .company-info h2 {
            margin: 0 0 4px 0;
            font-size: 22px;
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

        .cargo-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cargo-table th {
            background: #e2e8f0;
            color: #1e293b;
            padding: 8px 8px;
            font-size: 10px;
            font-weight: 700;
            text-align: left;
            border-bottom: 1.5px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .cargo-table th:last-child { border-right: none; }
        .cargo-table td {
            padding: 8px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }
        .cargo-table td:last-child { border-right: none; }
        .cargo-table tr:hover { background-color: #f8fafc; }

        .cargo-table-footer {
            background: #f1f5f9;
            border-top: 2px solid #cbd5e1;
            padding: 10px 14px;
            font-weight: 700;
            color: #1e293b;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

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

        .sub-options-inline {
            display: inline-flex;
            gap: 8px;
            margin-top: 2px;
            font-weight: normal;
            font-size: 10px;
            color: #475569;
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
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

@php
    $hbls = $shipment->hbls ?? collect([]);
    $totalPcs = $hbls->sum('pkg_qty') ?: 10;
    $totalWeightKg = $hbls->sum('gross_weight') ?: 450.00;
    $totalCbm = $hbls->sum('volume') ?: 1.85;
    $hblCount = count($hbls) ?: 1;
@endphp

<div x-data="manifestApp()" x-cloak>
    <!-- Controls Bar (Ocean Import Standard) -->
    <div class="controls-bar">
        <div class="controls-left">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-weight: 700; color: #475569;">Data Source:</span>
                <select x-model="dataSource">
                    <option value="shipment">Shipment</option>
                    <option value="master">Master</option>
                    <option value="house">House</option>
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
            <button class="btn-action" @click="printReport()"><i class="fa fa-print"></i> Print</button>
            <button class="btn-action" @click="printReport()"><i class="fa fa-file-pdf-o"></i> Save PDF</button>
        </div>
    </div>

    <!-- MAIN REPORT PAGE -->
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
                    TO: <span x-text="agentType === 'sub' ? 'SUB AGENT' : 'MASTER AGENT'"></span>
                </div>
                <div class="address-card-body">
                    <strong x-text="agentType === 'sub' ? '{{ addslashes($shipment->forwardingAgent->name ?? "SUB AGENT LOGISTICS INC.") }}' : '{{ addslashes($shipment->overseaAgent->name ?? "SHAWON LOGISTICS CO., LTD") }}'"></strong><br>
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
                <td class="value" style="color: #2563eb; font-weight: 800;">{{ $shipment->file_no ?? 'MAE-26050015' }}</td>
            </tr>
            <tr>
                <td class="label">FLIGHT NO.</td>
                <td class="value">{{ $shipment->flight_no ?? 'CA222' }}</td>
                <td class="label">CARRIER</td>
                <td class="value">{{ $shipment->carrier->name ?? $shipment->carrier_name ?? 'CHINA AIRLINES' }}</td>
            </tr>
            <tr>
                <td class="label">DEPARTURE</td>
                <td class="value">{{ $shipment->depPort->name ?? '(LAX) LOS ANGELES INT\'L' }}</td>
                <td class="label">ETD</td>
                <td class="value">{{ $shipment->etd ? (is_string($shipment->etd) ? $shipment->etd : $shipment->etd->format('m-d-Y H:i')) : '05-26-2026 00:00' }}</td>
            </tr>
            <tr>
                <td class="label">DESTINATION</td>
                <td class="value">{{ $shipment->dstPort->name ?? '(AMS) AMSTERDAM AIRPORT SCHIPHOL' }}</td>
                <td class="label">ETA</td>
                <td class="value">{{ $shipment->eta ? (is_string($shipment->eta) ? $shipment->eta : $shipment->eta->format('m-d-Y H:i')) : '05-26-2026 00:00' }}</td>
            </tr>
        </table>

        <!-- HAWB Cargo Details Table Section -->
        <div class="cargo-table-wrapper">
            <table class="cargo-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">HAWB NO / PCS</th>
                        <th style="width: 12%;">
                            GROSS WEIGHT
                            <select x-model="weightUnit" style="padding: 1px 4px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 10px; background: #fff; color: #1e293b; font-weight: 700; margin-left: 4px;">
                                <option value="KGS">KGS</option>
                                <option value="LBS">LBS</option>
                            </select>
                        </th>
                        <th style="width: 27%;">
                            SHIPPERS NAME & ADDRESS
                            <div class="sub-options-inline">
                                Show:
                                <label><input type="checkbox" x-model="shipperPhone"> Phone</label>
                                <label><input type="checkbox" x-model="shipperFax"> Fax</label>
                                <label><input type="checkbox" x-model="shipperContact"> Contact</label>
                            </div>
                        </th>
                        <th style="width: 27%;">
                            CONSIGNEE NAME & ADDRESS
                            <div class="sub-options-inline">
                                Show:
                                <label><input type="checkbox" x-model="consigneePhone"> Phone</label>
                                <label><input type="checkbox" x-model="consigneeFax"> Fax</label>
                                <label><input type="checkbox" x-model="consigneeContact"> Contact</label>
                            </div>
                        </th>
                        <th style="width: 13%;">NATURE OF GOODS</th>
                        <th style="width: 7%; text-align: center;">TERM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hbls as $hbl)
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;">
                                {{ $hbl->hawb_no ?? 'HAWB-'. $hbl->id }}
                                <div style="font-size: 10px; color: #475569; font-weight: normal;">{{ $hbl->pkg_qty ?? 10 }} PCS</div>
                            </td>
                            <td style="font-weight: 600; color: #0f172a;">
                                <span x-text="formatWeight({{ $hbl->gross_weight ?? 100 }})"></span>
                            </td>
                            <td>
                                <strong>{{ $hbl->shipper->name ?? 'GLOBAL EXPORT CORP' }}</strong>
                                <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    INDUSTRIAL ZONE 5, TAIPEI TAIWAN
                                    <div style="margin-top: 2px;">
                                        <span x-show="shipperPhone">TEL: 886-2-2345678 </span>
                                        <span x-show="shipperFax">FAX: 886-2-2345679 </span>
                                        <span x-show="shipperContact">ATTN: JOHN CHIANG</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $hbl->consignee->name ?? 'PACIFIC IMPORT DISTRIBUTORS' }}</strong>
                                <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    100 HARBOR BLVD, LOS ANGELES CA
                                    <div style="margin-top: 2px;">
                                        <span x-show="consigneePhone">TEL: 1-310-5550122 </span>
                                        <span x-show="consigneeFax">FAX: 1-310-5550123 </span>
                                        <span x-show="consigneeContact">ATTN: MARY SMITH</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $hbl->commodity ?? $hbl->description ?? 'ELECTRONIC PARTS' }}</td>
                            <td style="text-align: center; font-weight: 700; color: #059669;">{{ $hbl->freight_term ?? 'PPD' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td style="font-weight: 700; color: #0f172a;">
                                HAWB881923
                                <div style="font-size: 10px; color: #475569; font-weight: normal;">10 PCS</div>
                            </td>
                            <td style="font-weight: 600; color: #0f172a;">
                                <span x-text="formatWeight(450)"></span>
                            </td>
                            <td>
                                <strong>TAIWAN HIGH TECH CORP.</strong>
                                <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    NO. 88 SCIENCE PARK ROAD, HSINCHU
                                    <div style="margin-top: 2px;">
                                        <span x-show="shipperPhone">TEL: +886 3 578 9000 </span>
                                        <span x-show="shipperFax">FAX: +886 3 578 9001 </span>
                                        <span x-show="shipperContact">ATTN: KEVIN CHEN</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>CALIFORNIA LOGISTICS HUB INC.</strong>
                                <div x-show="showAddressInfo" style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    450 LOGISTICS WAY, COMPTON CA 90220
                                    <div style="margin-top: 2px;">
                                        <span x-show="consigneePhone">TEL: +1 310 600 4000 </span>
                                        <span x-show="consigneeFax">FAX: +1 310 600 4001 </span>
                                        <span x-show="consigneeContact">ATTN: DAVID MILLER</span>
                                    </div>
                                </div>
                            </td>
                            <td>COMPUTER COMPONENTS</td>
                            <td style="text-align: center; font-weight: 700; color: #059669;">PPD</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Cargo Table Footer / Totals -->
            <div class="cargo-table-footer">
                <div>
                    TOTAL {{ $totalPcs }} (PCS) &nbsp;|&nbsp;
                    <span x-text="formatTotalWeight({{ $totalWeightKg }})"></span>
                    <span x-show="showMeasurement"> &nbsp;|&nbsp; {{ number_format($totalCbm, 2) }} CBM / {{ number_format($totalCbm * 35.3147, 2) }} CFT</span>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: 600; color: #2563eb;">
                        <input type="checkbox" x-model="showMeasurement">
                        <span>Show Measurement</span>
                    </label>
                    <span style="color: #64748b;">{{ $hblCount }} AWB(S)</span>
                </div>
            </div>
        </div>

        <!-- Remark Box -->
        <div class="remark-box">
            <div class="remark-title">REMARK</div>
            <textarea x-model="remarkText" rows="2" style="width: 100%; border: none; outline: none; font-family: inherit; font-size: 11px; color: #334155; resize: vertical; background: transparent;"></textarea>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('manifestApp', () => ({
            dataSource: 'shipment',
            showAddressInfo: true,
            showAgentInfo: true,
            weightUnit: 'KGS',
            shipperPhone: true,
            shipperFax: true,
            shipperContact: true,
            consigneePhone: true,
            consigneeFax: true,
            consigneeContact: true,
            showMeasurement: false,
            agentType: '{{ $agentType ?? "master" }}',
            remarkText: '{{ addslashes($shipment->remark ?? "PLEASE RELEASE CARGO ONLY AGAINST ORIGINAL HAWB OR AGENT AUTHORIZATION. ALL CHARGES PREPAID AS ARRANGED.") }}',

            formatWeight(kgVal) {
                const val = parseFloat(kgVal) || 0;
                if (this.weightUnit === 'LBS') {
                    return (val * 2.20462).toFixed(2) + ' LBS';
                }
                return val.toFixed(2) + ' KGS';
            },

            formatTotalWeight(kgVal) {
                const val = parseFloat(kgVal) || 0;
                const lbsVal = (val * 2.20462).toFixed(2);
                const kgsVal = val.toFixed(2);
                return `${kgsVal} KGS / ${lbsVal} LBS`;
            },

            printReport() {
                window.print();
            }
        }));
    });
</script>
</body>
</html>
