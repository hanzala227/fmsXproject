<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $docTitle }} - {{ $invoice->invoice_no }}</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    @page {
        size: A4 portrait;
        margin: 8mm 10mm;
    }
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.5px;
        color: #233645;
        background: #525659;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Screen-only top toolbar */
    .screen-toolbar {
        width: 794px;
        margin: 12px auto 8px auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #0f172a;
        padding: 8px 16px;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .screen-toolbar .title {
        color: #f8fafc;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .screen-toolbar .actions {
        display: flex;
        gap: 8px;
    }
    .btn-action {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 5px 14px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background 0.15s;
    }
    .btn-action:hover { background: #1d4ed8; }
    .btn-action.secondary {
        background: #334155;
        color: #e2e8f0;
    }
    .btn-action.secondary:hover { background: #475569; }

    /* A4 Document Container */
    .document-page {
        width: 794px;
        min-height: 1115px;
        margin: 0 auto 20px auto;
        background: #ffffff;
        padding: 24px 28px 20px 28px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    /* ── 1. Top Header ── */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .header-table td {
        vertical-align: top;
    }
    .brand-logo-txt {
        font-size: 26px;
        font-weight: 900;
        color: #113e6f;
        line-height: 1;
        letter-spacing: -0.5px;
    }
    .brand-tagline {
        font-size: 8px;
        font-weight: 800;
        color: #113e6f;
        letter-spacing: 1px;
        margin-top: 3px;
        line-height: 1.15;
    }
    .company-info-block {
        text-align: center;
        font-size: 8.5px;
        color: #233645;
        line-height: 1.35;
    }
    .company-info-block .comp-name {
        font-size: 11.5px;
        font-weight: 800;
        color: #233645;
        margin-bottom: 2px;
    }
    .scope-info-block {
        text-align: right;
        font-size: 8.5px;
        font-weight: 800;
        color: #233645;
        line-height: 1.35;
        letter-spacing: 0.3px;
    }

    /* ── 2. Title & Metadata Bar ── */
    .title-meta-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .title-meta-table td {
        vertical-align: middle;
    }
    .doc-main-title {
        font-size: 24px;
        font-weight: 900;
        color: #113e6f;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        line-height: 1;
    }
    .meta-box-table {
        width: 250px;
        border-collapse: collapse;
        border: 1px solid #b8c8d5;
        font-size: 8.5px;
        margin-left: auto;
    }
    .meta-box-table td {
        border: 1px solid #b8c8d5;
        padding: 3px 6px;
        line-height: 1.25;
    }
    .meta-box-table td.meta-label {
        background: #e7f1f9;
        color: #233645;
        font-weight: 700;
        width: 105px;
    }
    .meta-box-table td.meta-val {
        color: #233645;
        font-weight: 600;
        background: #ffffff;
    }

    /* ── 3. Three-Column Cards ── */
    .details-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #b8c8d5;
        margin-bottom: 12px;
        table-layout: fixed;
    }
    .details-table th {
        background: #e7f1f9;
        color: #113e6f;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 5px 8px;
        border: 1px solid #b8c8d5;
        text-align: left;
        letter-spacing: 0.3px;
    }
    .details-table td {
        border: 1px solid #b8c8d5;
        padding: 6px 8px;
        vertical-align: top;
        font-size: 8.5px;
        line-height: 1.35;
        color: #233645;
        background: #ffffff;
    }
    .details-table .party-title {
        font-size: 10px;
        font-weight: 800;
        color: #233645;
        margin-bottom: 2px;
    }
    .details-table .field-row {
        margin-bottom: 2px;
    }
    .details-table .field-label {
        font-weight: 700;
        color: #233645;
    }
    .checkbox-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-right: 8px;
        font-size: 8.5px;
        color: #233645;
    }
    .box-check {
        display: inline-block;
        font-size: 9px;
        line-height: 1;
        color: #233645;
    }

    /* ── 4. Charges Table ── */
    .charges-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #b8c8d5;
        margin-bottom: 12px;
    }
    .charges-table th {
        background: #113e6f;
        color: #ffffff;
        font-size: 8.5px;
        font-weight: 800;
        padding: 6px 6px;
        border: 1px solid #113e6f;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .charges-table td {
        border: 1px solid #b8c8d5;
        padding: 5px 6px;
        font-size: 8.5px;
        color: #233645;
    }
    .charges-table tr.blank-row td {
        height: 24px;
    }

    /* ── 5. Remarks & Totals ── */
    .bottom-split-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .bottom-split-table td {
        vertical-align: top;
    }
    .remarks-card {
        border: 1px solid #b8c8d5;
        padding: 6px 8px;
        background: #e7f1f9;
        min-height: 68px;
    }
    .remarks-card .title {
        font-size: 8.5px;
        font-weight: 800;
        color: #233645;
        text-transform: uppercase;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }
    .remarks-card .content {
        font-size: 8.5px;
        color: #233645;
        line-height: 1.35;
    }

    .totals-box-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #b8c8d5;
        font-size: 8.5px;
    }
    .totals-box-table td {
        border: 1px solid #b8c8d5;
        padding: 4px 8px;
    }
    .totals-box-table tr.grand-row td {
        background: #113e6f;
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        border-color: #113e6f;
    }

    /* ── 6. Payment & Signatures Container ── */
    .footer-box-container {
        border: 1px solid #b8c8d5;
        background: #f6f9fb;
        padding: 8px 10px 6px 10px;
        margin-bottom: 8px;
    }
    .footer-split-table {
        width: 100%;
        border-collapse: collapse;
    }
    .footer-split-table td {
        vertical-align: top;
        font-size: 8px;
        line-height: 1.35;
        color: #233645;
    }
    .foot-col-title {
        font-size: 8.5px;
        font-weight: 800;
        color: #233645;
        text-transform: uppercase;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }
    .sig-line {
        border-bottom: 1px solid #233645;
        width: 160px;
        height: 24px;
        margin-bottom: 4px;
    }

    /* Slogan */
    .bottom-slogan {
        text-align: center;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 1.2px;
        color: #113e6f;
        text-transform: uppercase;
        padding-top: 4px;
    }

    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .screen-toolbar {
            display: none !important;
        }
        .document-page {
            width: 100% !important;
            min-height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
</head>
<body>

{{-- TOP SCREEN TOOLBAR (Hidden on print) --}}
<div class="screen-toolbar">
    <div class="title">
        <i class="fa fa-print" style="color:#60a5fa;"></i>
        <span>Print Preview &mdash; {{ $docTitle }} #{{ $invoice->invoice_no }}</span>
    </div>
    <div class="actions">
        <a href="javascript:window.print()" class="btn-action">
            <i class="fa fa-print"></i> Print Document
        </a>
        <a href="{{ route('accounting.invoices.show', $invoice) }}" class="btn-action secondary">
            <i class="fa fa-arrow-left"></i> Back to Invoice
        </a>
    </div>
</div>

{{-- A4 PRINT PAGE --}}
<div class="document-page">
    <div>
        {{-- HEADER --}}
        <table class="header-table">
            <tr>
                <td style="width:28%;">
                    <div class="brand-logo-txt">FreitX</div>
                    <div class="brand-tagline">FREIGHT FORWARDING<br>ERP</div>
                </td>
                <td style="width:44%;">
                    <div class="company-info-block">
                        <div class="comp-name">{{ $companyName }}</div>
                        <div>{{ $company->address ?: 'House 12, Road 5, Block A, Mirpur-11' }}</div>
                        <div>{{ $company->city ?: 'Dhaka 1216, Bangladesh' }}</div>
                        <div>Tel: {{ $company->phone ?: '+880 2 9000000' }} | {{ $company->email ?: 'info@silkcontainer.com' }}</div>
                        <div>{{ $company->website ?: 'www.silkcontainer.com' }}</div>
                    </div>
                </td>
                <td style="width:28%;">
                    <div class="scope-info-block">
                        <div>OCEAN AIR LAND / RORO</div>
                        <div>WAREHOUSING CUSTOMS</div>
                        <div>CLEARANCE</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- TITLE & METADATA TABLE --}}
        <table class="title-meta-table">
            <tr>
                <td style="vertical-align:middle;">
                    <div class="doc-main-title">{{ $docTitle }}</div>
                </td>
                <td style="text-align:right;">
                    <table class="meta-box-table">
                        <tr>
                            <td class="meta-label">{{ $docTitle }} No.</td>
                            <td class="meta-val" style="font-weight:800;color:#113e6f;">{{ $invoice->invoice_no }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">{{ $docTitle }} Date</td>
                            <td class="meta-val">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : date('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Reference No.</td>
                            <td class="meta-val">{{ $refNo }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Job No.</td>
                            <td class="meta-val">{{ $jobNo }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Currency</td>
                            <td class="meta-val" style="font-weight:700;">{{ $currencyCode }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 3-COLUMN DETAILS SECTION --}}
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width:33%;">{{ $invoice->type === 'AP' ? 'PAYABLE TO' : 'BILL TO (CUSTOMER)' }}</th>
                    <th style="width:37%;">SHIPMENT DETAILS</th>
                    <th style="width:30%;">{{ $docType === 'INV' ? 'DOCUMENT REFERENCE' : 'INVOICE REFERENCE' }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    {{-- Column 1: Party --}}
                    <td>
                        <div class="party-title">{{ $invoice->billTo?->name ?? 'ABC Trading Co. Ltd.' }}</div>
                        <div style="color:#233645;margin-bottom:4px;">{{ $invoice->billing_address ?: ($invoice->billTo?->address ?: '123 Business Avenue, Gulshan, Dhaka 1212, Bangladesh') }}</div>
                        <div class="field-row"><span class="field-label">Contact:</span> {{ $invoice->billTo?->contact_person ?? 'Mr. Rahman' }}</div>
                        <div class="field-row"><span class="field-label">Phone:</span> {{ $invoice->billTo?->phone ?? '+880 1711 111111' }}</div>
                        <div class="field-row"><span class="field-label">Email:</span> {{ $invoice->billTo?->email ?? 'rahman@abctrading.com' }}</div>
                        <div class="field-row"><span class="field-label">Customer Code:</span> {{ $invoice->billTo?->code ?? ('CUST-' . str_pad($invoice->bill_to_id ?? 1, 6, '0', STR_PAD_LEFT)) }}</div>
                    </td>

                    {{-- Column 2: Shipment --}}
                    <td>
                        <div class="field-row"><span class="field-label">Mode:</span> {{ $mode }}</div>
                        <div class="field-row"><span class="field-label">Vessel / Voyage:</span> {{ $vesselVoyage }}</div>
                        <div class="field-row"><span class="field-label">POL:</span> {{ $pol }}</div>
                        <div class="field-row"><span class="field-label">POD:</span> {{ $pod }}</div>
                        <div class="field-row"><span class="field-label">Container No.:</span> {{ $containerNo }}</div>
                        <div class="field-row"><span class="field-label">HBL / HAWBL No.:</span> {{ $hblNo }}</div>
                        <div class="field-row"><span class="field-label">Cargo:</span> {{ $cargo }}</div>
                    </td>

                    {{-- Column 3: Reference & Types --}}
                    <td>
                        <div class="field-row"><span class="field-label">Invoice No.:</span> {{ $invoice->relatedInvoice?->invoice_no ?? $invoice->invoice_no }}</div>
                        <div class="field-row"><span class="field-label">Invoice Date:</span> {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : date('d M Y') }}</div>
                        <div class="field-row"><span class="field-label">Job No.:</span> {{ $jobNo }}</div>
                        <div style="margin-top:6px;padding-top:4px;border-top:1px dashed #b8c8d5;">
                            <div style="font-weight:800;color:#233645;margin-bottom:3px;font-size:8.5px;">{{ $docTitle }} Type</div>
                            @if($docType === 'DN')
                                <div>
                                    <span class="checkbox-pill"><span class="box-check">&#9632;</span> Additional Charges</span>
                                </div>
                                <div style="margin-top:2px;">
                                    <span class="checkbox-pill"><span class="box-check">&#9633;</span> Correction</span>
                                    <span class="checkbox-pill"><span class="box-check">&#9633;</span> Re-issue</span>
                                </div>
                            @elseif($docType === 'CN')
                                <div>
                                    <span class="checkbox-pill"><span class="box-check">&#9632;</span> Rate Correction</span>
                                </div>
                                <div style="margin-top:2px;">
                                    <span class="checkbox-pill"><span class="box-check">&#9633;</span> Cargo Claim</span>
                                    <span class="checkbox-pill"><span class="box-check">&#9633;</span> Discount</span>
                                </div>
                            @else
                                <div>
                                    <span class="checkbox-pill"><span class="box-check">&#9632;</span> Commercial Invoice</span>
                                </div>
                                <div style="margin-top:2px;">
                                    <span class="checkbox-pill"><span class="box-check">&#9633;</span> Freight Charge</span>
                                    <span class="checkbox-pill"><span class="box-check">&#9633;</span> Original</span>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- 4. CHARGES TABLE --}}
        <table class="charges-table">
            <thead>
                <tr>
                    <th style="width:35px;text-align:center;">SL</th>
                    <th style="text-align:left;">CHARGE DESCRIPTION</th>
                    <th style="width:65px;text-align:center;">CODE</th>
                    <th style="width:95px;text-align:center;">UNIT</th>
                    <th style="width:45px;text-align:center;">QTY</th>
                    <th style="width:85px;text-align:right;">RATE ({{ $currencyCode }})</th>
                    <th style="width:95px;text-align:right;">AMOUNT ({{ $currencyCode }})</th>
                </tr>
            </thead>
            <tbody>
                @php $sl = 1; @endphp
                @forelse($lines as $line)
                    <tr>
                        <td style="text-align:center;font-weight:700;">{{ $sl++ }}</td>
                        <td>{{ $line->description ?: 'Freight & Logistics Charges' }}</td>
                        <td style="text-align:center;font-weight:600;">{{ $line->charge_code ?: 'FRT' }}</td>
                        <td style="text-align:center;color:#475569;">{{ $line->unit ?: 'Per Shipment' }}</td>
                        <td style="text-align:center;font-weight:600;">{{ number_format((float)($line->qty ?: 1), 0) }}</td>
                        <td style="text-align:right;">{{ number_format((float)($line->rate ?: $line->amount), 2) }}</td>
                        <td style="text-align:right;font-weight:700;">{{ number_format((float)$line->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="text-align:center;font-weight:700;">1</td>
                        <td>Freight & Logistics Handling Charges</td>
                        <td style="text-align:center;font-weight:600;">FRT</td>
                        <td style="text-align:center;color:#475569;">Per Shipment</td>
                        <td style="text-align:center;font-weight:600;">1</td>
                        <td style="text-align:right;">{{ number_format((float)$invoice->total_amount, 2) }}</td>
                        <td style="text-align:right;font-weight:700;">{{ number_format((float)$invoice->total_amount, 2) }}</td>
                    </tr>
                @endforelse

                {{-- Blank rows to ensure consistent document height matching sample --}}
                @for($b = count($lines); $b < 5; $b++)
                    <tr class="blank-row">
                        <td style="text-align:center;color:#b8c8d5;">{{ $sl++ }}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        {{-- 5. REMARKS & TOTALS SUMMARY --}}
        <table class="bottom-split-table">
            <tr>
                <td style="width:58%;padding-right:12px;">
                    <div class="remarks-card">
                        <div class="title">REMARKS</div>
                        <div class="content">{{ $invoice->remarks ?: $defaultRemarks }}</div>
                    </div>
                </td>
                <td style="width:42%;">
                    <table class="totals-box-table">
                        <tr>
                            <td style="color:#233645;font-weight:700;">Subtotal ({{ $currencyCode }})</td>
                            <td style="text-align:right;font-weight:700;width:110px;">{{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="color:#233645;font-weight:700;">Tax ({{ $taxPct }}%)</td>
                            <td style="text-align:right;font-weight:700;">{{ number_format($taxTotal, 2) }}</td>
                        </tr>
                        <tr class="grand-row">
                            <td>GRAND TOTAL ({{ $currencyCode }})</td>
                            <td style="text-align:right;font-size:11px;">{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- 6. BOTTOM SIGNATURES & INSTRUCTIONS IN CONTAINER BOX --}}
    <div>
        <div class="footer-box-container">
            <table class="footer-split-table">
                <tr>
                    {{-- Payment Instructions --}}
                    <td style="width:36%;padding-right:10px;">
                        <div class="foot-col-title">PAYMENT INSTRUCTIONS</div>
                        <div><span style="font-weight:700;">Bank Name:</span> {{ $bankName }}</div>
                        <div><span style="font-weight:700;">Account Name:</span> {{ $companyName }}</div>
                        <div><span style="font-weight:700;">Account No.:</span> {{ $bankAccount }}</div>
                        <div><span style="font-weight:700;">SWIFT Code:</span> {{ $bankSwift }}</div>
                        <div><span style="font-weight:700;">Currency:</span> {{ $currencyCode }}</div>
                        <div><span style="font-weight:700;">Branch:</span> {{ $bankBranch }}</div>
                    </td>

                    {{-- Prepared By --}}
                    <td style="width:32%;padding-right:10px;">
                        <div class="foot-col-title">PREPARED BY</div>
                        <div class="sig-line"></div>
                        <div><span style="font-weight:700;">Name:</span> {{ $invoice->issuer?->name ?? 'Sardar Admin' }}</div>
                        <div><span style="font-weight:700;">Date:</span> {{ $invoice->created_at ? $invoice->created_at->format('d M Y') : date('d M Y') }}</div>
                    </td>

                    {{-- Authorized Signature --}}
                    <td style="width:32%;">
                        <div class="foot-col-title">AUTHORIZED SIGNATURE</div>
                        <div class="sig-line"></div>
                        <div><span style="font-weight:700;">Name:</span> __________________________</div>
                        <div><span style="font-weight:700;">Date:</span> __________________________</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Slogan --}}
        <div class="bottom-slogan">
            CONNECTING BUSINESSES ACROSS THE WORLD
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        @if(request('auto') !== '0')
        setTimeout(function() {
            window.print();
        }, 350);
        @endif
    });
</script>
</body>
</html>
