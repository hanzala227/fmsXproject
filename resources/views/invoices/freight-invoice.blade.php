<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FREIGHT INVOICE {{ $data['invoice_no'] }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    @page {
        size: A4 portrait;
        margin: 0;
    }
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9.5px;
        color: #000;
        background: #525659;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Top Toolbar (No-Print Controls) */
    .top-bar {
        width: 794px;
        margin: 10px auto 5px auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #1e293b;
        padding: 8px 15px;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }
    .top-bar .title-txt {
        color: #ffffff;
        font-size: 13px;
        font-weight: bold;
    }
    .top-bar .btn-group {
        display: flex;
        gap: 10px;
    }
    .btn-action {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 6px 16px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-action:hover {
        background: #1d4ed8;
    }

    /* Template Container with PDF Background */
    #freight-invoice-template {
        position: relative;
        width: 794px;
        height: 1123px;
        margin: 0 auto 20px auto;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    #freight-invoice-template img.bg-doc {
        width: 100%;
        height: 100%;
        display: block;
        pointer-events: none;
    }

    /* Overlay Input & Textarea Fields */
    .inv-field {
        position: absolute;
        background: transparent;
        border: 1px solid transparent;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9.5px;
        font-weight: bold;
        color: #000000;
        padding: 1px 3px;
        outline: none;
        box-sizing: border-box;
        transition: all 0.15s ease-in-out;
        resize: none;
    }
    .inv-field:hover, .inv-field:focus {
        background: rgba(255, 243, 205, 0.85);
        border: 1px solid #f59e0b;
        border-radius: 2px;
        z-index: 100;
    }

    /* Absolute Field Positions (Exact PDF Image Alignments) */
    #inv_no             { background: #ffffff !important; top: 16.8%; left: 36.8%; width: 30%; height: 3.2%; font-size: 28px; font-weight: 900; line-height: 1; border: none; }
    #bin_no             { top: 19.5%; left: 9.8%; width: 18%; height: 1.8%; }
    
    #bill_to_block      {display:none; background: #ffffff !important; top: 20.5%; left: 24.5%; width: 50%; height: 8.0%; font-size: 9.5px; }
    #account_no         { background: #ffffff !important; top: 20.5%; left: 79.8%; font-size: 11px; width: 17.5%; height: 1.8%; }
    #invoice_date       { background: #ffffff !important; top: 23.1%; left: 80.5%; font-size: 11px; width: 17.5%; height: 1.8%; }
    #due_date           { background: #ffffff !important; top: 24.6%; left: 80.3%; font-size: 11px; width: 17.5%; height: 1.8%; }
    #terms              { background: #ffffff !important; top: 26%; left: 79.6%; font-size: 11px; width: 17.5%; height: 1.5%; }
    #shipment_no        { background: #ffffff !important; top: 27.2%; left: 80.3%; font-size: 10px; width: 17.5%; height: 1.5%; }
    #consol_no          { background: #ffffff !important; top: 28.8%; left: 80.2%; font-size: 10px; width: 17.5%; height: 1.5%; }

    #consignor          { top: 31.7%; left: 5.5%; width: 44%; height: 1.8%; }
    #consignee          { top: 31.7%; left: 50.5%; width: 44%; height: 1.8%; }
    #client_ref         { top: 34.2%; left: 5.5%; width: 89%; height: 1.8%; }
    #goods_desc         { top: 36.5%; left: 5.5%; width: 89%; height: 1.8%; }

    #vessel_flight_date { top: 38.9%; left: 5.5%; width: 29.5%; height: 1.8%; }
    #weight             { top: 38.9%; left: 36.8%; width: 13.5%; height: 1.8%; }
    #volume             { top: 38.9%; left: 51.5%; width: 13.0%; height: 1.8%; }
    #chargeable_weight  { top: 38.9%; left: 65.5%; width: 14.5%; height: 1.8%; }
    #packages           { top: 38.9%; left: 81.0%; width: 13.5%; height: 1.8%; }

    #origin             { top: 41.3%; left: 5.5%; width: 44.5%; height: 1.8%; }
    #mawb_mbl           { top: 41.3%; left: 50.2%; width: 22.5%; height: 1.8%; }
    #hawb_hbl           { top: 41.3%; left: 73%; width: 20.5%; height: 1.8%; }

    #etd                { top: 42.5%; left: 43%; width: 11.5%; height: 1.8%; }
    #destination        { top: 43.6%; left: 50.5%; width: 28.5%; height: 1.8%; }
    #eta                { top: 43.6%; left: 80.5%; width: 13.0%; height: 1.8%; }

    /* Line Items Area Overlay */
    .line-item-desc   { left: 5.5%; width: 60%; height: 2.0%; }
    .line-item-vat    { left: 67.0%; width: 16%; height: 2.0%; text-align: center; }
    .line-item-amount { left: 84.0%; width: 10.5%; height: 2.0%; text-align: right; }

    /* Summary Totals Overlay */
    #subtotal_val     { top: 80.9%; left: 70.0%; width: 24.5%; height: 1.8%; text-align: right; }
    #add_vat_val      { top: 82%; left: 70.0%; width: 24.5%; height: 1.8%; text-align: right; }
    #grand_total_val  { top: 84.0%; left: 69.0%; width: 25.0%; height: 2.2%; text-align: right; font-size: 11px; font-weight: 900; }

    /* Footer Payment Overlay (Color-Coded Visual Identification) */
    #eft_payments_to  { top: 86.4%; left: 17%; width:13.0%; height: 2.0%; font-weight: bold;  } /* CYAN: EFT PAYMENTS TO */
    #swift_code       { top: 87.2%; left: 35.5%; width: 15.0%; height: 1.6%; font-weight: bold;  } /* GREEN: SWIFT CODE */
    #mail_payments_to { top: 88%; left: 51%; width: 38.0%; height: 5.5%; font-weight: bold; }
    #bank_account     { top: 89%; left: 13%; width: 25.0%; height: 1.6%; font-weight: bold; }
    #pay_ref          { top: 94.0%; left: 13.5%; width: 35.0%; height: 1.6%; font-weight: bold;}
    #due_amount       { top: 95.6%; left: 10.5%; width: 45.0%; height: 1.6%; font-weight: bold; }

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        .no-print, .top-bar { display: none !important; }
        body { background: #ffffff !important; margin: 0 !important; }
        #freight-invoice-template { 
            margin: 0 !important; 
            box-shadow: none !important; 
            width: 100% !important; 
            height: 100vh !important;
        }
        .inv-field {
            border: none !important;
            color: #000000 !important;
        }
        #inv_no, #account_no, #invoice_date, #due_date, #terms, #shipment_no, #consol_no, #bill_to_block {
            background: #ffffff !important;
            background-color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
    }
</style>
</head>
<body>

<!-- Top Navigation Toolbar -->
<div class="top-bar no-print">
    <div class="title-txt">📄 FREIGHT INVOICE DOCUMENT OVERLAY (DYNAMIC DB FETCH)</div>
    <div class="btn-group">
        <button class="btn-action" onclick="recalculateTotals()">🔄 Recalculate Totals</button>
        <button class="btn-action" onclick="window.print()">🖨 Print / Download PDF</button>
        
        <x-email-document-modal 
            documentType="freight-invoice" 
            documentId="{{ $shipment->id ?? 0 }}" 
            documentUrl="{{ url()->full() }}" 
            documentName="Freight_Invoice_{{ $data['invoice_no'] }}" 
        />
    </div>
</div>

<!-- PDF Background Template Container -->
<div id="freight-invoice-template">
    <!-- Cleaned Original PDF Background Image -->
    <img class="bg-doc" src="{{ file_exists(public_path('hbl-backgrounds/freight-invoice-page-1-clean.png')) ? '/hbl-backgrounds/freight-invoice-page-1-clean.png' : '/hbl-backgrounds/freight-invoice-page-1.png' }}?v={{ time() }}" alt="Freight Invoice Background">

    <!-- OVERLAY EDITABLE INPUTS & TEXTAREAS -->

    <!-- Header & Title Identifiers -->
    <input type="text" id="inv_no" class="inv-field" value="{{ $data['invoice_no'] }}">
    <input type="text" id="bin_no" class="inv-field" value="{{ $data['bin_no'] ?? '000408318' }}">

    <!-- Bill To Address -->
    <textarea id="bill_to_block" class="inv-field">{{ $data['bill_to_name'] }}&#10;@if(!empty($data['bill_to_attention']))ATTENTION: {{ $data['bill_to_attention'] }}&#10;@endif{{ $data['bill_to_address'] }}</textarea>

    <!-- Meta Details -->
    <input type="text" id="account_no" class="inv-field" value="{{ $data['account_no'] }}">
    <input type="text" id="invoice_date" class="inv-field" value="{{ $data['invoice_date'] }}">
    <input type="text" id="due_date" class="inv-field" value="{{ $data['due_date'] }}">
    <input type="text" id="terms" class="inv-field" value="{{ $data['terms'] }}">
    <input type="text" id="shipment_no" class="inv-field" value="{{ $data['shipment_no'] }}">
    <input type="text" id="consol_no" class="inv-field" value="{{ $data['consol_no'] }}">

    <!-- Particulars Row 1, 2, 3 -->
    <input type="text" id="consignor" class="inv-field" value="{{ $data['consignor'] }}">
    <input type="text" id="consignee" class="inv-field" value="{{ $data['consignee'] }}">
    <input type="text" id="client_ref" class="inv-field" value="{{ $data['client_ref'] }}">
    <input type="text" id="goods_desc" class="inv-field" value="{{ $data['goods_description'] }}">

    <!-- Particulars Row 4 -->
    <input type="text" id="vessel_flight_date" class="inv-field" value="{{ $data['vessel_flight_date'] }}">
    <input type="text" id="weight" class="inv-field" value="{{ $data['weight'] }}">
    <input type="text" id="volume" class="inv-field" value="{{ $data['volume'] }}">
    <input type="text" id="chargeable_weight" class="inv-field" value="{{ $data['chargeable_weight'] }}">
    <input type="text" id="packages" class="inv-field" value="{{ $data['packages'] }}">

    <!-- Particulars Row 5 & 6 -->
    <input type="text" id="origin" class="inv-field" value="{{ $data['origin'] }}">
    <input type="text" id="mawb_mbl" class="inv-field" value="{{ $data['mawb_mbl'] }}">
    <input type="text" id="hawb_hbl" class="inv-field" value="{{ $data['hawb_hbl'] }}">

    <input type="text" id="etd" class="inv-field" value="{{ $data['etd'] }}">
    <input type="text" id="destination" class="inv-field" value="{{ $data['destination'] }}">
    <input type="text" id="eta" class="inv-field" value="{{ $data['eta'] }}">

    <!-- DYNAMIC CHARGES LINE ITEMS OVERLAY -->
    @php
        $startTop = 47.8;
        $rowHeight = 2.4;
    @endphp

    @forelse($data['items'] as $index => $item)
        @php $currentTop = $startTop + ($index * $rowHeight); @endphp
        <input type="text" id="item_desc_{{ $index }}" class="inv-field line-item-desc" style="top: {{ $currentTop }}%;" value="{{ $item['description'] }}">
        <input type="text" id="item_vat_{{ $index }}" class="inv-field line-item-vat" style="top: {{ $currentTop }}%;" value="{{ $item['vat_text'] }}">
        <input type="text" id="item_amount_{{ $index }}" class="inv-field line-item-amount item-amount-input" style="top: {{ $currentTop }}%;" value="{{ $item['amount'] > 0 ? number_format($item['amount'], 2) : '' }}" oninput="recalculateTotals()">
    @empty
        <input type="text" id="item_desc_0" class="inv-field line-item-desc" style="top: 47.8%;" value="AIR FREIGHT USD5.60/KG x 105 KG @BDT122.00">
        <input type="text" id="item_vat_0" class="inv-field line-item-vat" style="top: 47.8%;" value="Zero Rated">
        <input type="text" id="item_amount_0" class="inv-field line-item-amount item-amount-input" style="top: 47.8%;" value="71,736.00" oninput="recalculateTotals()">

        <input type="text" id="item_desc_1" class="inv-field line-item-desc" style="top: 50.2%;" value="AIR AMS & OTHERS CHARGES USD40.00">
        <input type="text" id="item_vat_1" class="inv-field line-item-vat" style="top: 50.2%;" value="Zero Rated">
        <input type="text" id="item_amount_1" class="inv-field line-item-amount item-amount-input" style="top: 50.2%;" value="4,880.00" oninput="recalculateTotals()">
    @endforelse

    <!-- SUMMARY TOTALS OVERLAY -->
    <input type="text" id="subtotal_val" class="inv-field" value="{{ number_format($data['subtotal'], 2) }}">
    <input type="text" id="add_vat_val" class="inv-field" value="{{ $data['vat_total'] > 0 ? number_format($data['vat_total'], 2) : '.00' }}">
    <input type="text" id="grand_total_val" class="inv-field" value="{{ number_format($data['total'], 2) }}">

    <!-- FOOTER PAYMENT DETAILS OVERLAY -->
    <input type="text" id="eft_payments_to" class="inv-field" value="{{ $data['eft_payments_to'] ?? '' }}">
    <input type="text" id="swift_code" class="inv-field" value="{{ $data['bank_swift'] }}">
    <textarea id="mail_payments_to" class="inv-field"></textarea>
    <input type="text" id="bank_account" class="inv-field" value="{{ $data['bank_account'] }}">
    <input type="text" id="pay_ref" class="inv-field" value="{{ $data['pay_ref'] }}">
    <input type="text" id="due_amount" class="inv-field" value="{{ $data['currency_code'] }} {{ number_format($data['total'], 2) }}        Invoiced: {{ $data['currency_code'] }} {{ number_format($data['total'], 2) }}">

</div>

<script>
    function recalculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-amount-input').forEach(input => {
            let val = parseFloat(input.value.replace(/,/g, '')) || 0;
            subtotal += val;
        });
        
        let vatInput = document.getElementById('add_vat_val');
        let vatVal = parseFloat(vatInput.value.replace(/,/g, '')) || 0;
        let grandTotal = subtotal + vatVal;
        
        let formattedSubtotal = subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        let formattedTotal = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        document.getElementById('subtotal_val').value = formattedSubtotal;
        document.getElementById('grand_total_val').value = formattedTotal;
        document.getElementById('due_amount').value = 'BDT ' + formattedTotal + '        Invoiced: BDT ' + formattedTotal;
    }
</script>

</body>
</html>