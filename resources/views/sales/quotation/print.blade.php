<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freight Quotation - {{ $quotation->quote_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .quote-sheet {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px 40px;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }
        .company-brand h1 {
            margin: 0 0 4px 0;
            font-size: 22px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.5px;
        }
        .company-brand p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
        }
        .quote-badge {
            text-align: right;
        }
        .quote-badge h2 {
            margin: 0 0 6px 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .quote-badge .pill {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 14px 16px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .info-block h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #2563eb;
            letter-spacing: 0.5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 11px;
        }
        .info-label {
            width: 110px;
            color: #64748b;
            font-weight: 600;
        }
        .info-val {
            flex: 1;
            color: #0f172a;
            font-weight: 600;
        }
        .route-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }
        .route-item {
            display: flex;
            flex-direction: column;
        }
        .route-item .lbl {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .route-item .val {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
        }
        .table-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1e3a8a;
            margin: 18px 0 8px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        table.quote-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 15px;
        }
        table.quote-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            text-align: left;
        }
        table.quote-table td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            color: #334155;
        }
        table.quote-table tr:nth-child(even) td {
            background: #fafafa;
        }
        .terms-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 10px;
            color: #92400e;
            margin-top: 20px;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        .terms-box h4 {
            margin: 0 0 6px 0;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #b45309;
        }
        .terms-box ul {
            margin: 0;
            padding-left: 16px;
        }
        .sign-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
            padding-top: 15px;
        }
        .sign-block {
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        .action-toolbar {
            max-width: 850px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 8px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .quote-sheet { box-shadow: none; border: none; padding: 0; max-width: 100%; }
            .action-toolbar { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="action-toolbar">
        <a href="{{ route('sales.quotations.list') }}" class="btn-back"><i class="fa fa-arrow-left"></i> Back to Quotations</a>
        <button type="button" class="btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print Quotation</button>
    </div>

    <div class="quote-sheet">
        <!-- Header -->
        <div class="header-bar">
            <div class="company-brand">
                <h1>{{ $quotation->office?->name ?? 'FREITX GLOBAL LOGISTICS' }}</h1>
                <p>Freight Forwarding, Customs Brokerage & Multi-Modal Supply Chain Solutions</p>
                <p>Tel: +1 (800) 555-FMS2 | Email: sales@freitx.com | Web: www.freitx.com</p>
            </div>
            <div class="quote-badge">
                <h2>FREIGHT QUOTATION</h2>
                <div class="pill">REF: {{ $quotation->quote_no }}</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Status: <strong>{{ strtoupper($quotation->status ?? 'DRAFT') }}</strong></div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-block">
                <h3><i class="fa fa-building-o"></i> Customer / Client Details</h3>
                <div class="info-row"><span class="info-label">Customer Name:</span><span class="info-val">{{ $quotation->customer?->name ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Contact Person:</span><span class="info-val">{{ $quotation->customer?->contact_person ?? '--' }}</span></div>
                <div class="info-row"><span class="info-label">Phone / Mobile:</span><span class="info-val">{{ $quotation->customer?->phone ?? '--' }}</span></div>
                <div class="info-row"><span class="info-label">Email Address:</span><span class="info-val">{{ $quotation->customer?->email ?? '--' }}</span></div>
            </div>
            <div class="info-block">
                <h3><i class="fa fa-calendar-check-o"></i> Quotation Specifications</h3>
                <div class="info-row"><span class="info-label">Quotation Date:</span><span class="info-val">{{ $quotation->quote_date ? $quotation->quote_date->format('M d, Y') : ($quotation->created_at ? $quotation->created_at->format('M d, Y') : '--') }}</span></div>
                <div class="info-row"><span class="info-label">Valid Until:</span><span class="info-val" style="color: #dc2626;">{{ $quotation->expiry_date ? $quotation->expiry_date->format('M d, Y') : '--' }}</span></div>
                <div class="info-row"><span class="info-label">Sales Executive:</span><span class="info-val">{{ $quotation->salesPerson?->name ?? 'House Account' }}</span></div>
                <div class="info-row"><span class="info-label">Prepared By (OP):</span><span class="info-val">{{ $quotation->op?->name ?? ($quotation->createdBy?->name ?? 'System') }}</span></div>
            </div>
        </div>

        <!-- Route & Cargo Specifications Strip -->
        <div class="route-strip">
            <div class="route-item">
                <span class="lbl">Transport Mode</span>
                <span class="val">{{ $quotation->transport_mode ?? ($quotation->shipping_type ?? 'Ocean Export') }}</span>
            </div>
            <div class="route-item">
                <span class="lbl">Port of Loading (POL)</span>
                <span class="val">{{ $quotation->pol?->name ?? ($quotation->departure ?? '--') }}</span>
            </div>
            <div class="route-item">
                <span class="lbl">Port of Discharge (POD)</span>
                <span class="val">{{ $quotation->pod?->name ?? ($quotation->destination ?? '--') }}</span>
            </div>
            <div class="route-item">
                <span class="lbl">Carrier / Shipping Line</span>
                <span class="val">{{ $quotation->carrier?->name ?? ($quotation->liner_code ?? 'Any Carrier') }}</span>
            </div>
        </div>

        <div class="route-strip" style="margin-top: -10px;">
            <div class="route-item">
                <span class="lbl">Service Term</span>
                <span class="val">{{ $quotation->service_term ?? 'CY / CY' }}</span>
            </div>
            <div class="route-item">
                <span class="lbl">Incoterms</span>
                <span class="val">{{ $quotation->incoterms_id ?? 'FOB' }}</span>
            </div>
            <div class="route-item">
                <span class="lbl">Commodity</span>
                <span class="val">{{ $quotation->commodity ?: 'General Cargo (FAK)' }}</span>
            </div>
            <div class="route-item">
                <span class="lbl">Weight & Volume</span>
                <span class="val">{{ $quotation->weight_kg ? number_format($quotation->weight_kg, 2) . ' KG' : '--' }} / {{ $quotation->volume_cbm ? number_format($quotation->volume_cbm, 2) . ' CBM' : '--' }}</span>
            </div>
        </div>

        <!-- Freight Charges Table -->
        <div class="table-title"><i class="fa fa-ship"></i> Ocean / Air Freight Tariff</div>
        <table class="quote-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Charge Description</th>
                    <th style="width: 15%;">Carrier</th>
                    <th style="width: 12%;">Currency</th>
                    <th style="width: 15%;">20' GP Rate</th>
                    <th style="width: 15%;">40' GP Rate</th>
                    <th style="width: 18%;">40' HC Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($freightItems as $item)
                @php $rem = json_decode($item->remark ?? '{}', true) ?: []; @endphp
                <tr>
                    <td><strong>{{ $item->charge_name ?: 'Ocean Freight' }}</strong></td>
                    <td>{{ $item->vendor?->name ?? ($quotation->carrier?->name ?? '-') }}</td>
                    <td>{{ $item->currency?->code ?? 'USD' }}</td>
                    <td>{{ !empty($rem['rate_20gp']) ? number_format($rem['rate_20gp'], 2) : '--' }}</td>
                    <td>{{ !empty($rem['rate_40gp']) ? number_format($rem['rate_40gp'], 2) : '--' }}</td>
                    <td>{{ !empty($rem['rate_40hc']) ? number_format($rem['rate_40hc'], 2) : '--' }}</td>
                </tr>
                @empty
                <tr>
                    <td><strong>Ocean Freight Rate</strong></td>
                    <td>{{ $quotation->carrier?->name ?? 'Standard Carrier' }}</td>
                    <td>USD</td>
                    <td>As per agreement</td>
                    <td>As per agreement</td>
                    <td>As per agreement</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Origin / Destination Local Charges Table -->
        <div class="table-title"><i class="fa fa-list-alt"></i> Local / Destination Surcharges & Handling</div>
        <table class="quote-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Item / Surcharge Name</th>
                    <th style="width: 15%;">Billing Unit</th>
                    <th style="width: 15%;">Currency</th>
                    <th style="width: 20%; text-align: right;">Rate / Amount</th>
                    <th style="width: 20%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($localItems as $item)
                @php $rem = json_decode($item->remark ?? '{}', true) ?: []; @endphp
                <tr>
                    <td>{{ $item->charge_name ?: ($rem['freight_code'] ?? 'Local Charge') }}</td>
                    <td>{{ $item->unit ?? ($rem['unit'] ?? 'Per Container') }}</td>
                    <td>{{ $item->currency?->code ?? 'USD' }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->amount ?: $item->rate, 2) }}</td>
                    <td>{{ $rem['row_remark'] ?? ($rem['remark'] ?? '-') }}</td>
                </tr>
                @empty
                <tr>
                    <td>Terminal Handling Charges (THC) / Documentation</td>
                    <td>Per Container / B/L</td>
                    <td>USD / Local</td>
                    <td style="text-align: right;">At Actuals</td>
                    <td>Standard port tariff applies</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Standard Freight Terms & Conditions -->
        <div class="terms-box">
            <h4><i class="fa fa-exclamation-triangle"></i> Standard Terms & Conditions of Carriage</h4>
            <ul>
                <li>Rates quoted are strictly valid until the expiry date stated above and are subject to equipment and vessel/airline space availability at time of booking.</li>
                <li>Port Demurrage and Container Detention are subject to standard carrier tariffs after the expiration of stipulated free days.</li>
                <li>Bunker Adjustment Factor (BAF), Currency Adjustment Factor (CAF), Peak Season Surcharge (PSS), and War Risk surcharges are subject to prevailing carrier adjustments at time of shipment.</li>
                <li>All business is undertaken in accordance with the Standard Trading Conditions of the Freight Forwarders Association. Goods are carried under carrier bill of lading liabilities unless cargo insurance is explicitly requested.</li>
            </ul>
        </div>

        <!-- Customer Acceptance Signature Block -->
        <div class="sign-grid">
            <div class="sign-block">
                <strong>{{ $quotation->office?->name ?? 'FREITX GLOBAL LOGISTICS' }}</strong><br>
                <span>Authorized Freight Representative Signature & Stamp</span>
            </div>
            <div class="sign-block">
                <strong>CUSTOMER ACCEPTANCE & CONFIRMATION</strong><br>
                <span>Authorized Signatory, Stamp & Date (Sign here to accept booking)</span>
            </div>
        </div>
    </div>

</body>
</html>

