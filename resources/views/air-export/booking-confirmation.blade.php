<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Confirmation - {{ $shipment->file_no ?? $shipment->mawb_no ?? 'FreightX' }}</title>
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
            margin-bottom: 20px;
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

        .booking-title-box {
            border: 2px solid #0f172a;
            color: #0f172a;
            font-size: 18px;
            font-weight: 800;
            padding: 12px 40px;
            text-align: center;
            letter-spacing: 1px;
            background: #f8fafc;
        }

        .mawb-bar {
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            padding: 6px 12px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            margin-bottom: 15px;
            border-radius: 3px;
        }

        .mawb-input-inline {
            display: inline-block;
            border: 1px solid #cbd5e1;
            padding: 2px 14px;
            background: #fff;
            font-weight: 800;
            color: #0f172a;
            margin-left: 8px;
            border-radius: 3px;
        }

        .two-col-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .card-box {
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            overflow: hidden;
            background: #fff;
        }

        .card-header {
            background: #f1f5f9;
            padding: 5px 10px;
            font-weight: 700;
            color: #334155;
            font-size: 10px;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 8px 10px;
            min-height: 55px;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
        }

        .table-custom td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 11px;
        }

        .table-custom td.label {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #334155;
            width: 28%;
            font-size: 10px;
            text-transform: uppercase;
        }

        .table-custom td.value {
            background-color: #ffffff;
            color: #0f172a;
            font-weight: 600;
        }

        .section-header-bar {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            font-weight: 700;
            color: #334155;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: -1px;
        }

        .terms-box {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 9px;
            color: #475569;
            line-height: 1.35;
            background: #fff;
            height: 100%;
            text-align: justify;
        }

        .radio-inline {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 12px;
            cursor: pointer;
            font-weight: 600;
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
    $totalPcs = $hbls->sum('pkg_qty') ?: 1;
    $totalWeightKg = $hbls->sum('gross_weight') ?: 1.00;
    $totalCbm = $hbls->sum('volume') ?: 0.00;
@endphp

<div x-data="bookingApp()" x-cloak>
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

            <div style="display: flex; align-items: center; gap: 6px; margin-left: 10px;">
                <span style="font-weight: 700; color: #475569;">Weight Unit:</span>
                <select x-model="weightUnit" style="padding: 2px 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px;">
                    <option value="KGS">KGS</option>
                    <option value="LBS">LBS</option>
                </select>
            </div>
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
                    <p>EMAIL: 1812001@silk-container.com</p>
                </div>
                <p style="margin-top: 6px; font-weight: 700; color: #3b82f6;">
                    Prepared by {{ auth()->user()->name ?? 'DEMO_925' }} {{ date('m-d-Y H:i') }} (PDT)
                </p>
            </div>
            <div class="booking-title-box">
                BOOKING CONFIRMATION
            </div>
        </div>

        <!-- MAWB Header Bar -->
        <div class="mawb-bar">
            MAWB NO. : <span class="mawb-input-inline">{{ $shipment->mawb_no ?? '02605203306' }}</span>
        </div>

        <!-- Shipper & Booking Info Grid -->
        <div class="two-col-grid">
            <!-- Shipper Box -->
            <div class="card-box">
                <div class="card-header">SHIPPER</div>
                <div class="card-body">
                    <strong>{{ $shipment->shipper->name ?? 'PLAZA INLAND ESTATE API #72' }}</strong>
                    <div x-show="showAddressInfo" style="margin-top: 4px; color: #475569;">
                        NORTH BEACH, NY 11762-3342<br>
                        UNITED STATES
                    </div>
                </div>
            </div>

            <!-- Booking Info Box -->
            <div class="card-box" style="border: none;">
                <div class="card-header" style="border: 1px solid #cbd5e1;">BOOKING INFORMATION</div>
                <table class="table-custom" style="margin-top: -1px;">
                    <tr>
                        <td class="label">Booking No.</td>
                        <td class="value" style="color: #2563eb; font-weight: 800;">{{ $shipment->file_no ?? 'MAE-26050015' }}</td>
                    </tr>
                    <tr>
                        <td class="label">BOOKING DATE</td>
                        <td class="value">{{ $shipment->booking_date ? (is_string($shipment->booking_date) ? $shipment->booking_date : $shipment->booking_date->format('m-d-Y')) : '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">PO NO.</td>
                        <td class="value">{{ $shipment->po_no ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">ITN NO.</td>
                        <td class="value">{{ $shipment->itn_no ?? 'NO EEI 30.37(a)' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Consignee, Agent & Notify Grid -->
        <div class="two-col-grid">
            <!-- Consignee Box -->
            <div class="card-box">
                <div class="card-header">CONSIGNEE</div>
                <div class="card-body" style="min-height: 100px;">
                    <strong>{{ $shipment->consignee->name ?? 'DEMO CONSIGNEE LLC' }}</strong>
                    <div x-show="showAddressInfo" style="margin-top: 4px; color: #475569;">
                        100 HARBOR BLVD, SUITE 500<br>
                        LOS ANGELES, CA 90012<br>
                        UNITED STATES
                    </div>
                </div>
            </div>

            <!-- Right Column: Agent & Notify -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Agent Box -->
                <div class="card-box" x-show="showAgentInfo">
                    <div class="card-header">AGENT</div>
                    <div class="card-body" style="min-height: 35px;">
                        <strong x-text="agentType === 'sub' ? '{{ addslashes($shipment->forwardingAgent->name ?? "SUB AGENT LOGISTICS INC.") }}' : '{{ addslashes($shipment->overseaAgent->name ?? "SHAWON LOGISTICS CO., LTD") }}'"></strong>
                        <div x-show="showAddressInfo" style="margin-top: 2px; color: #475569;">
                            123 FREIGHT TOWER, SUITE 800
                        </div>
                    </div>
                </div>

                <!-- Notify Box -->
                <div class="card-box" style="flex: 1;">
                    <div class="card-header">NOTIFY</div>
                    <div class="card-body" style="min-height: 35px;">
                        <strong>{{ $shipment->notifyParty->name ?? 'SAME AS CONSIGNEE' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flight & Route Information Section -->
        <div style="margin-bottom: 15px;">
            <div class="section-header-bar">FLIGHT & ROUTE INFORMATION</div>
            <table class="table-custom">
                <tr>
                    <td class="label">FLIGHT NO.</td>
                    <td class="value" style="width: 32%;">{{ $shipment->flight_no ?? 'CA222' }}</td>
                    <td class="label">CARRIER</td>
                    <td class="value" style="width: 32%;">{{ $shipment->carrier->name ?? $shipment->carrier_name ?? 'CHINA AIRLINES' }}</td>
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
        </div>

        <!-- Cargo Information Section -->
        <div style="margin-bottom: 15px;">
            <div class="section-header-bar">CARGO INFORMATION</div>
            <table class="table-custom">
                <tr>
                    <td class="label">COMMODITY</td>
                    <td class="value" colspan="3">{{ $shipment->commodity ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">WEIGHT</td>
                    <td class="value" style="width: 32%;">
                        <span x-text="formatWeight({{ $totalWeightKg }})"></span>
                    </td>
                    <td class="label">DANGEROUS</td>
                    <td class="value" style="width: 32%;">
                        <label class="radio-inline">
                            <input type="radio" value="YES" x-model="dangerous"> YES
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="NO" x-model="dangerous"> NO
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="label">MEASUREMENT</td>
                    <td class="value">
                        <span x-text="formatMeasurement({{ $totalCbm }})"></span>
                    </td>
                    <td class="label">L/C</td>
                    <td class="value">
                        <label class="radio-inline">
                            <input type="radio" value="YES" x-model="lc"> YES
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="NO" x-model="lc"> NO
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="label">PKG</td>
                    <td class="value">{{ $totalPcs }} CARTON(S)</td>
                    <td class="label">STACKABLE</td>
                    <td class="value">
                        <label class="radio-inline">
                            <input type="radio" value="YES" x-model="stackable"> YES
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="NO" x-model="stackable"> NO
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Delivery, Pick Up & Trucker Section -->
        <div class="two-col-grid">
            <!-- Left Box: Delivery To / Pick Up -->
            <div class="card-box">
                <div class="card-header">DELIVERY TO / PICK UP</div>
                <div class="card-body" style="min-height: 80px;">
                    {{ $shipment->delivery_to ?? '' }}
                </div>
            </div>

            <!-- Right Column: Cargo Pick Up & Trucker -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Cargo Pick Up Box -->
                <div class="card-box">
                    <div class="card-header">CARGO PICK UP</div>
                    <div class="card-body" style="min-height: 35px;">
                        {{ $shipment->cargo_pickup ?? '' }}
                    </div>
                </div>

                <!-- Trucker Box -->
                <div class="card-box" style="flex: 1;">
                    <div class="card-header">TRUCKER</div>
                    <div class="card-body" style="min-height: 35px;">
                        {{ $shipment->trucker ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Remark & Terms & Conditions Section -->
        <div class="two-col-grid" style="align-items: stretch; margin-bottom: 0;">
            <!-- Remark Box -->
            <div class="card-box" style="display: flex; flex-direction: column;">
                <div class="card-header">REMARK</div>
                <div class="card-body" style="flex: 1;">
                    <textarea x-model="remarkText" rows="6" style="width: 100%; height: 100%; border: none; outline: none; font-family: inherit; font-size: 11px; color: #334155; resize: none; background: transparent;"></textarea>
                </div>
            </div>

            <!-- Terms & Conditions Box -->
            <div class="card-box" style="display: flex; flex-direction: column;">
                <div class="card-header">TERMS & CONDITIONS</div>
                <div class="terms-box">
                    Each as agent of the Carrier of the Non-Vessel Operating Common Carrier (NVOCC) hereby confirms booking of cargo under the terms & conditions of the Carrier. Freight & charges are prepaid or collect as shown above. Cargo is subject to security checks prior to loading on aircraft. Failure to comply may result in non-usage fee building or amended itinerary. You can file your EEI electronically through AES Direct website or let us process electronic file by the day of cut-off prior to cargo loading. Any fine incurred due to lack of AES filing compliance will be for the account of shipper/forwarder. All shipments to Hong Kong, Taiwan, South Africa, Australia, etc. palletized status are used (ISPM15) comply with the IPPC notification or non-wooden pallets. Wooden and non-wooden packaging is subject to US Customs inspection.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bookingApp', () => ({
            dataSource: 'shipment',
            showAddressInfo: true,
            showAgentInfo: true,
            weightUnit: 'KGS',
            dangerous: 'NO',
            lc: 'NO',
            stackable: 'YES',
            agentType: '{{ $agentType ?? "master" }}',
            remarkText: '{{ addslashes($shipment->remark ?? "") }}',

            formatWeight(kgVal) {
                const val = parseFloat(kgVal) || 1.00;
                const kgsStr = val.toFixed(2) + ' KGS';
                const lbsStr = (val * 2.20462).toFixed(2) + ' LBS';
                return `${kgsStr}  ${lbsStr}`;
            },

            formatMeasurement(cbmVal) {
                const val = parseFloat(cbmVal) || 0.00;
                return val.toFixed(2) + ' CBM';
            },

            printReport() {
                window.print();
            }
        }));
    });
</script>
</body>
</html>
