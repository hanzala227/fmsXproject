<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Air Waybill (HAWB) Print - {{ $data['hawb_number'] ?: 'New' }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#475569; font-family: Arial, Helvetica, sans-serif; font-size:11px; }

/* Top Navigation Control Toolbar */
.toolbar {
    position:fixed; top:0; left:0; right:0; z-index:10000;
    background:#1e293b; color:#fff; padding:8px 20px;
    display:flex; align-items:center; justify-content:space-between; gap:16px;
    box-shadow:0 2px 10px rgba(0,0,0,.4);
}
.toolbar-group { display:flex; align-items:center; gap:12px; }
.toolbar label { font-size:11px; color:#cbd5e1; font-weight:600; display:flex; align-items:center; gap:4px; }
.toolbar select, .toolbar button {
    font-size:11px; padding:4px 8px; border-radius:3px; border:1px solid #475569;
}
.toolbar select { background:#334155; color:#fff; outline:none; }
.toolbar button { background:#2563eb; color:#fff; border:none; cursor:pointer; font-weight:600; padding:5px 14px; }
.toolbar button:hover { background:#1d4ed8; }
.toolbar .btn-close { background:#64748b; }
.toolbar .btn-close:hover { background:#475569; }

.page-wrapper {
    margin: 55px auto 30px;
    width: 850px;
    background: #fff;
    box-shadow: 0 5px 25px rgba(0,0,0,0.3);
    padding: 15px;
    position: relative;
}

/* Document Template Container */
.awb-document {
    position: relative;
    width: 820px;
    margin: 0 auto;
    background: #fff;
}
.awb-document img.bg-template {
    width: 100%;
    height: auto;
    display: block;
}

/* Overlay Input & Interactive Field Styles */
.hawb-field {
    position: absolute;
    border: none;
    background: transparent;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9px;
    font-weight: 600;
    color: #000;
    padding: 1px 3px;
    outline: none;
    resize: none;
    line-height: 1.2;
}
.hawb-field:hover, .hawb-field:focus {
    background: rgba(239, 246, 255, 0.8);
    border: 1px dashed #3b82f6;
}
.hawb-select {
    position: absolute;
    border: 1px dashed #94a3b8;
    background: rgba(255,255,255,0.9);
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5px;
    font-weight: bold;
    color: #000;
    padding: 0 2px;
    outline: none;
    cursor: pointer;
}
.hawb-select:hover, .hawb-select:focus {
    background: #eff6ff;
    border-color: #2563eb;
}
.hawb-check-group {
    position: absolute;
    display: flex;
    align-items: center;
    gap: 2px;
    font-size: 7.5px;
    font-weight: bold;
    color: #000;
    cursor: pointer;
}
.hawb-check-group input[type="checkbox"], .hawb-check-group input[type="radio"] {
    cursor: pointer;
    margin: 0;
    width: 10px;
    height: 10px;
    accent-color: #cc0000;
}
.print-mark {
    display: none;
    font-size: 11px;
    font-weight: bold;
    color: #000;
    line-height: 1;
    text-align: center;
}
.hawb-copy-title {
    position: absolute;
    top: 2.2%;
    right: 8%;
    font-size: 11px;
    font-weight: bold;
    color: #b91c1c;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: rgba(255,255,255,0.95);
    padding: 2px 8px;
    border: 1px dashed #ef4444;
    border-radius: 3px;
    z-index: 10;
}
.iata-terms-wrapper {
    display: none;
    margin-top: 25px;
    background: #fff;
    padding: 20px 25px;
    border-top: 2px dashed #94a3b8;
    font-size: 8.5px;
    line-height: 1.35;
    color: #1e293b;
    box-shadow: 0 5px 25px rgba(0,0,0,0.3);
}
.iata-terms-wrapper h3 {
    font-size: 11px;
    text-align: center;
    margin-bottom: 10px;
    text-transform: uppercase;
    color: #0f172a;
}
.iata-terms-wrapper .terms-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Exact PDF Box Title Labels & CSS Coordinates */

/* Box: "016" (Airline 3-Digit Code) */
#hawb_airline_code       { top: 5.4%; left: 8%; width: 5.5%; height: 1.6%; text-align: center; font-size: 10px; }

/* Box: "LAX" (Airport of Departure Code) */
#hawb_departure_code     {background:white; top: 5.4%; left: 12.5%; width: 3.8%; height: 1.6%; text-align: center; font-size: 7px; }

/* Box: "2034 6045" (Air Waybill Serial Number) */
#hawb_serial_no          {top: 5.4%; left: 16.5%; width: 14%; height: 1.6%; font-size: 9px; }

/* Box: "MAH-26070001" (Air Waybill Number - Top Right) */
#hawb_number             { top: 5.4%; left: 74%; width: 15%; height: 1.6%;  font-size: 11px; }

/* Box: "Shipper's Name and Address" */
#hawb_shipper            { top: 8.7%; left: 8%; width: 19%; height: 6.8%; font-size: 9px; }

/* Box: "Shipper's Account Number" */
#hawb_shipper_account    {top: 8.2%; left: 27.5%; width: 19%; height: 1.8%; font-size: 9px; }

/* Box: "Consignee's Name and Address" */
#hawb_consignee          { top: 17.5%; left: 8%; width: 19%; height: 6.8%; font-size: 9px; }

/* Box: "Consignee's Account Number" */
#hawb_consignee_account  {top: 17%; left: 27.5%; width: 19%; height: 1.3%; font-size: 9px; }

/* Box: "Issuing Carrier's Agent Name and City" */
#hawb_issuing_agent      { top: 25.5%; left: 8%; width: 38%; height: 4.3%; font-size: 8.5px; }

/* Box: "Agent's IATA Code" */
#hawb_agent_iata         { top: 31.2%; left: 8%; width: 19%; height: 1.6%; font-size: 9px; }

/* Box: "Account No." (Agent Account Number) */
#hawb_agent_account      { top: 31.2%; left: 27.5%; width: 19%; height: 1.6%; font-size: 9px; }

/* Box: "Accounting Information" - Load from: Notify */
#hawb_load_from_notify   { top: 25%; left: 75.5%; }

/* Box: "Accounting Information" - Load from: Oversea Agent */
#hawb_load_from_oversea  { top: 25%; left: 80.5%;}

/* Box: "Accounting Information" */
#hawb_accounting_info    { top: 26.4%; left: 47%; width: 41.5%; height: 6%; font-size: 8.5px; }

/* Box: "Airport of Departure(Addr. of First Carrier) and Requested Routing" */
#hawb_airport_departure  {  top: 33.8%; left:8%; width: 38%; height: 1.8%; font-size: 9px; }

/* Box: "Booking No." (MAE-202607020182249) */
#hawb_file_no            { top: 34.1%; left: 47%; width: 14.5%; height: 1.5%; font-size: 9px; font-weight: bold; }

/* Box: "Optional Shipping Information" */
#hawb_optional_shipping  {  top: 34.1%; left: 62.2%; width: 13%; height: 1.5%; font-size: 9px; }

/* Box: "To" (Routing 1) */
#hawb_routing_to1        {   top: 36.7%; left: 8%; width: 4%; height: 1.8%; text-align: center; }

/* Box: "By First Carrier" */
#hawb_routing_by1        { top: 36.5%; left: 12.5%; width: 18%; font-size: 8px; height: 1.8%; }

/* Box: "to" (Routing 2) */
#hawb_routing_to2        {top: 36.7%; left: 31.0%; width: 4%; height: 1.8%; text-align: center; }

/* Box: "by" (Routing 2) */
#hawb_routing_by2        { top: 36.7%; left: 35.5%; width: 2.7%; height: 1.8%; }

/* Box: "to" (Routing 3) */
#hawb_routing_to3        {  top: 36.7%; left: 38.6%; width: 4%; height: 1.8%; text-align: center; }

/* Box: "by" (Routing 3) */
#hawb_routing_by3        {top: 36.7%; left: 42.9%; width: 3.6%; height: 1.8%; }

/* Box: "Airport of Destination" */
#hawb_airport_destination{ top: 39.5%; left: 8%; width: 19%; height: 1.8%; font-size: 9px; }

/* Box: "Requested Flight" (Flight Number, e.g. UA923) */
#hawb_flight_no          { top: 39.8%; left: 27.5%; width: 8.5%; height: 1.5%; font-size: 9px; }

/* Box: "Requested Date" (Flight Date, e.g. 05-20) */
#hawb_flight_date        { top: 39.8%; left: 36.5%; width: 10%; height: 1.6%; font-size: 9px; }

/* Box: "Currency" (CAD) */
#hawb_currency           {  top: 36.7%; left: 46.8%; width: 4%; height: 1.8%; text-align: center; }

/* Box: "CHGS Code" (PP) */
#hawb_chgs_code          {top: 37.3%; left: 51.0%; width: 2.3%; height: 1.2%; text-align: center; }

/* Box: "WT/VAL" (Prepaid PPD Checkbox) */
#hawb_wt_val_ppd_grp     { top: 37.5%; left: 54%; }

/* Box: "WT/VAL" (Collect COLL Checkbox) */
#hawb_wt_val_coll_grp    { top: 37.5%; left: 56.2%; }

/* Box: "Other" (Prepaid PPD Checkbox) */
#hawb_other_ppd_grp       { top: 37.5%; left: 58.5%; }

/* Box: "Other" (Collect COLL Checkbox) */
#hawb_other_coll_grp      { top: 37.5%; left: 60.5%; }

/* Box: "Declared value for Carriage" (NVD) */
#hawb_declared_carriage  { top: 36.6%; left: 62.5%; width: 12.7%; height: 1.8%; text-align: center; }

/* Box: "Declared value for Customs" (NCV) */
#hawb_declared_customs   { top: 36.6%; left: 75.8%; width: 12.7%; height: 1.8%; text-align: center; }

/* Box: "Amount of insurance" (XXX) */
#hawb_insurance_amount   { top: 39.5%; left: 47%; width:11.8%; height: 1.8%; text-align: center; }

/* Box: "Handling Information" */
#hawb_handling_info      { top: 42.7%; left: 8%; width: 68%; height: 4%; font-size: 8px; }

/* Box: "These commodities, technology or software..." (US Export Checkbox) */
#hawb_us_export_chk_grp  { display:none; top: 48.8%; left: 3.5%; }

/* Box: "Ultimate Destination" (UNITED KINGDOM) */
#hawb_ultimate_dest      {display:none; top: 48.8%; left: 42.0%; width: 17.0%; height: 1.6%; font-weight: bold; }

/* Box: "SCI" (X) */
#hawb_sci                {top: 45%; left:77.0%; width: 12.0%; height: 1.6%; text-align: center; }

/* Box: "No. of Pieces RCP" */
#hawb_pieces_rcp         { top: 50%; left: 8%; width: 4%; height: 17.5%; font-size: 9px; text-align: center; }

/* Box: "Show Mark" Checkbox */
#hawb_show_mark_grp      {display:none; top: 60.5%; left: 3.8%; }

/* Box: "GROSS WEIGHT" */
#hawb_gross_weight       { top: 50.0%; left: 13.0%; width: 6%; height: 17.5%; font-size: 9px; text-align: center; font-weight: bold; padding-top: 2px; }

/* Box: "kg/lb" (Weight Unit Dropdown) */
#hawb_weight_unit        { top:55.5%; left: 19.2%; width: 3.5%; height: 1.6%;font-size:6px }

/* Box: "Rate Class Commodity Item No." */
#hawb_rate_class         {top: 50%; left: 23.3%; width: 6.2%; height: 19.5%; font-size: 9px; text-align: center; }

/* Box: "Chargeable Weight" */
#hawb_chargeable_weight  { top: 50%; left: 31%; width: 7%; height: 19.5%; font-size: 9px; text-align: center; }

/* Box: "Rate / Charge" */
#hawb_rate_charge        { top: 50%; left: 39.5%; width: 9%; height: 19.5%;  font-size: 9px; text-align: center; }

/* Box: "Total" */
#hawb_total_charge       {  top: 50%; left: 50.1%; width: 12.7%; height: 17.5%; font-size: 9px; text-align: center; }

/* Box: "Nature and Quantity of Goods (incl. Dimensions of Volume)" */
#hawb_nature_quantity    { top: 50%; left: 64.3%; width: 24.5%; height: 19.5%; font-size: 8.5px; }

/* Box: "Prepaid Weight Charge" */
#hawb_prepaid_weight_charge {top: 70.9%; left: 8%; width: 14.8%; height: 1.2%; text-align: center; }

/* Box: "Collect Weight Charge" */
#hawb_collect_weight_charge {  top: 70.9%; left: 23%; width: 14%; height: 1.2%; text-align: center; }

/* Box: "Prepaid Valuation Charge" */
#hawb_prepaid_valuation    { top: 73.3%; left: 8%; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Collect Valuation Charge" */
#hawb_collect_valuation    {top: 73.3%; left: 23%; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Prepaid Tax" */
#hawb_prepaid_tax          { top: 76%; left:8%; width: 14.7%; height: 1.8%; text-align: center; }

/* Box: "Collect Tax" */
#hawb_collect_tax          { top: 76%; left: 23%; width: 14.5%; height: 1.8%; text-align: center; }

/* Box: "Total Other Charges Due Agent" (Prepaid) */
#hawb_due_agent_prepaid    { top:79%; left:8%; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Total Other Charges Due Agent" (Collect) */
#hawb_due_agent_collect    {  top: 79.0%; left: 23%; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Total Other Charges Due Carrier" (Prepaid) */
#hawb_due_carrier_prepaid  { top: 82%; left:8%; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Total Other Charges Due Carrier" (Collect) */
#hawb_due_carrier_collect  { top: 82%; left: 23%; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Total Prepaid" */
#hawb_total_prepaid        { top: 87.2%; left: 8%; width: 14.5%; height: 1.6%; text-align: center; font-weight: bold; }

/* Box: "Total Collect" */
#hawb_total_collect        {  top: 87.2%; left: 23%; width: 14.5%; height: 1.6%; text-align: center; font-weight: bold; }

/* Box: Certification Radio - "Agent and Shipper" */
#hawb_cert_agent_shipper_grp { top: 81.5%; left: 37.9%; }

/* Box: Certification Radio - "Shipper" */
#hawb_cert_shipper_grp       { top: 81.5%; left: 49%; }

/* Box: "Signature of Shipper or his Agent" */
#hawb_signature_shipper      { background:white; top: 82.7%; left: 37.5%;text-align:center; width: 51.5%; height: 2.2%; font-weight: bold;font-size: 14px; }

/* Box: "Currency Conversion Rates" */
#hawb_currency_conv        { top: 90%; left: 8%; width: 14.5%; height: 1.8%; text-align: center; }

/* Box: "CC Charges in Dest. Currency" */
#hawb_cc_charges_dest      { top: 90%; left: 23%; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Executed on (date)" */
#hawb_executed_date        { top: 88.5%; left: 37.5%; width: 14.5%; height: 1.6%; }

/* Box: "at (place)" */
#hawb_executed_place       {background:white; top: 88.5%; left:55%;font-size:12px; width: 14.5%; height: 1.6%; text-align: center; }

/* Box: "Signature of Issuing Carrier or its Agent" */
#hawb_signature_carrier    { top: 87.5%; left: 70%; width: 19.5%; height: 2.8%; font-size: 8.5px;overflow :hidden; text-align: center; font-weight: bold; }

/* Box: "Charges at Destination" */
#hawb_charges_at_dest      { top: 93.1%; left: 23%; width: 14%; height: 1.8%; text-align: center; }

/* Box: "Total Collect Charges" */
#hawb_total_collect_charges{ top: 93.1%; left: 37.5%; width: 16%; height: 1.6%; text-align: center; }

/* Box: "Show ORIGINAL" Checkbox */
#hawb_show_original_grp    { top: 98%; left: 40%; }

@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    body { background: #fff; }
    .toolbar { display: none !important; }
    .page-wrapper { margin: 0; padding: 0; width: 100%; box-shadow: none; }
    .awb-document { width: 100%; }
    .hawb-field { border: none !important; box-shadow: none !important; }
    .hawb-select { border: none !important; appearance: none; -webkit-appearance: none; }
    
    .hawb-check-group input[type="checkbox"],
    .hawb-check-group input[type="radio"] {
        display: none !important;
    }
    .hawb-check-group input:checked + .print-mark {
        display: inline-block !important;
        font-weight: bold;
        font-size: 11px;
        color: #000 !important;
    }

    /* Preserve custom background colors (like white background) on print */
    #hawb_signature_shipper { background: #ffffff !important; opacity: 1 !important; }

    .hawb-copy-title {
        border: none !important;
        background: transparent !important;
        color: #000 !important;
    }
    .iata-terms-wrapper {
        box-shadow: none !important;
        border-top: none !important;
        page-break-before: always;
    }
}
@page { margin: 0; size: A4 portrait; }
</style>
</head>
<body>

<!-- Top Navigation Toolbar Controls -->
<div class="toolbar" id="toolbar">
    <div class="toolbar-group">
        <label>Data Source:</label>
        <select id="dataSourceSelect">
            <option value="shipment" selected>Shipment</option>
            <option value="manual">Manual Entry</option>
        </select>

        <label>For:</label>
        <select id="forSelect">
            <option value="CONSIGNEE" selected>CONSIGNEE</option>
            <option value="SHIPPER">SHIPPER</option>
            <option value="CARRIER">CARRIER</option>
            <option value="AGENT">AGENT</option>
        </select>

        <label>Doc. Package:</label>
        <select id="docPackageSelect">
            <option value="all" selected>All parties + 1 more copy</option>
            <option value="original">Original Only</option>
        </select>
    </div>

    <div class="toolbar-group">
        <label><input type="checkbox" id="chkShowAmount" checked> Show Amount</label>
        <label><input type="checkbox" id="chkPrintTerms"> Print IATA Terms</label>
        <button onclick="window.print()">🖨 Print / Save PDF</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>
</div>

<div class="page-wrapper">
    <div class="awb-document">
        <!-- Copy Designation Title / Watermark -->
        <div id="hawb_copy_designation" class="hawb-copy-title">Original 2 (for Consignee)</div>

        <!-- Background Air Waybill Template Image (Converted PNG from templateforhawbblank/HAWB BLANK COPY.pdf) -->
        <img src="{{ file_exists(public_path('hbl-backgrounds/hawb-blank-1.png')) ? '/hbl-backgrounds/hawb-blank-1.png' : '/hbl-backgrounds/ntg-air-page-1.png' }}" alt="Air Waybill Template" class="bg-template">

        <!-- Overlay Input Fields (100% Dynamic & Editable) -->

        <!-- Box: "016" (Airline 3-Digit Code) -->
        <input type="text" id="hawb_airline_code" class="hawb-field" value="{{ $data['airline_code'] }}">
        
        <!-- Box: "LAX" (Airport of Departure Code) -->
        <input type="text" id="hawb_departure_code" class="hawb-field" value="{{ $data['departure_code'] }}">
        
        <!-- Box: "2034 6045" (Air Waybill Serial Number) -->
        <input type="text" id="hawb_serial_no" class="hawb-field" value="{{ $data['serial_no'] }}">
        
        <!-- Box: "MAH-26070001" (Air Waybill Number - Top Right) -->
        <input type="text" id="hawb_number" class="hawb-field" value="{{ $data['hawb_number'] }}">

        <!-- Box: "Shipper's Name and Address" -->
        <textarea id="hawb_shipper" class="hawb-field">{{ $data['shipper'] }}</textarea>
        
        <!-- Box: "Shipper's Account Number" -->
        <input type="text" id="hawb_shipper_account" class="hawb-field" value="{{ $data['shipper_account_no'] }}">

        <!-- Box: "Consignee's Name and Address" -->
        <textarea id="hawb_consignee" class="hawb-field">{{ $data['consignee'] }}</textarea>
        
        <!-- Box: "Consignee's Account Number" -->
        <input type="text" id="hawb_consignee_account" class="hawb-field" value="{{ $data['consignee_account_no'] }}">

        <!-- Box: "Issuing Carrier's Agent Name and City" -->
        <textarea id="hawb_issuing_agent" class="hawb-field">{{ $data['issuing_agent'] }}</textarea>
        
        <!-- Box: "Agent's IATA Code" -->
        <input type="text" id="hawb_agent_iata" class="hawb-field" value="{{ $data['agent_iata_code'] }}">
        
        <!-- Box: "Account No." (Agent Account Number) -->
        <input type="text" id="hawb_agent_account" class="hawb-field" value="{{ $data['agent_account_no'] }}">

        <!-- Box: "Accounting Information" - Load from: Notify -->
        <label class="hawb-check-group" id="hawb_load_from_notify"><input type="radio" name="load_from" value="notify"><span class="print-mark">X</span> <span class="chk-text">Notify</span></label>
        
        <!-- Box: "Accounting Information" - Load from: Oversea Agent -->
        <label class="hawb-check-group" id="hawb_load_from_oversea"><input type="radio" name="load_from" value="oversea" checked><span class="print-mark">X</span> <span class="chk-text">Oversea Agent</span></label>
        
        <!-- Box: "Accounting Information" -->
        <textarea id="hawb_accounting_info" class="hawb-field">{{ $data['accounting_info'] }}</textarea>

        <!-- Box: "Airport of Departure(Addr. of First Carrier) and Requested Routing" -->
        <input type="text" id="hawb_airport_departure" class="hawb-field" value="{{ $data['airport_departure'] }}">
        
        <!-- Box: "Booking No." (MAE-202607020182249) -->
        <input type="text" id="hawb_file_no" class="hawb-field" value="{{ $data['file_no'] }}">
        
        <!-- Box: "Optional Shipping Information" -->
        <input type="text" id="hawb_optional_shipping" class="hawb-field" value="">

        <!-- Box: "To" (Routing 1) -->
        <input type="text" id="hawb_routing_to1" class="hawb-field" value="{{ $data['routing_to1'] }}">
        
        <!-- Box: "By First Carrier" -->
        <input type="text" id="hawb_routing_by1" class="hawb-field" value="{{ $data['routing_by1'] }}">
        
        <!-- Box: "to" (Routing 2) -->
        <input type="text" id="hawb_routing_to2" class="hawb-field" value="{{ $data['routing_to2'] }}">
        
        <!-- Box: "by" (Routing 2) -->
        <input type="text" id="hawb_routing_by2" class="hawb-field" value="{{ $data['routing_by2'] }}">
        
        <!-- Box: "to" (Routing 3) -->
        <input type="text" id="hawb_routing_to3" class="hawb-field" value="{{ $data['routing_to3'] }}">
        
        <!-- Box: "by" (Routing 3) -->
        <input type="text" id="hawb_routing_by3" class="hawb-field" value="{{ $data['routing_by3'] }}">

        <!-- Box: "Airport of Destination" -->
        <input type="text" id="hawb_airport_destination" class="hawb-field" value="{{ $data['airport_destination'] }}">
        
        <!-- Box: "Requested Flight" (Flight Number, e.g. UA923) -->
        <input type="text" id="hawb_flight_no" class="hawb-field" value="{{ $data['requested_flight_no'] }}">
        
        <!-- Box: "Requested Date" (Flight Date, e.g. 05-20 / 07-24) -->
        <input type="text" id="hawb_flight_date" class="hawb-field" value="{{ $data['requested_flight_date'] }}">

        <!-- Box: "Currency" -->
        <input type="text" id="hawb_currency" class="hawb-field" value="{{ $data['currency'] }}">
        
        <!-- Box: "CHGS Code" -->
        <input type="text" id="hawb_chgs_code" class="hawb-field" value="{{ $data['chgs_code'] }}">
        
        <!-- Box: "WT/VAL" (Prepaid PPD Checkbox) -->
        <label class="hawb-check-group" id="hawb_wt_val_ppd_grp"><input type="checkbox" id="hawb_wt_val_ppd" {{ $data['wt_val_ppd'] ? 'checked' : '' }}><span class="print-mark">X</span></label>
        
        <!-- Box: "WT/VAL" (Collect COLL Checkbox) -->
        <label class="hawb-check-group" id="hawb_wt_val_coll_grp"><input type="checkbox" id="hawb_wt_val_coll" {{ $data['wt_val_coll'] ? 'checked' : '' }}><span class="print-mark">X</span></label>
        
        <!-- Box: "Other" (Prepaid PPD Checkbox) -->
        <label class="hawb-check-group" id="hawb_other_ppd_grp"><input type="checkbox" id="hawb_other_ppd" {{ $data['other_ppd'] ? 'checked' : '' }}><span class="print-mark">X</span></label>
        
        <!-- Box: "Other" (Collect COLL Checkbox) -->
        <label class="hawb-check-group" id="hawb_other_coll_grp"><input type="checkbox" id="hawb_other_coll" {{ $data['other_coll'] ? 'checked' : '' }}><span class="print-mark">X</span></label>

        <!-- Box: "Declared value for Carriage" -->
        <input type="text" id="hawb_declared_carriage" class="hawb-field" value="{{ $data['dv_carriage'] }}">
        
        <!-- Box: "Declared value for Customs" -->
        <input type="text" id="hawb_declared_customs" class="hawb-field" value="{{ $data['dv_customs'] }}">
        
        <!-- Box: "Amount of insurance" -->
        <input type="text" id="hawb_insurance_amount" class="hawb-field" value="{{ $data['insurance_amount'] }}">

        <!-- Box: "Handling Information" -->
        <textarea id="hawb_handling_info" class="hawb-field">{{ $data['handling_info'] }}</textarea>

        <!-- Box: "These commodities, technology or software were exported..." (US Export Checkbox) -->
        <label class="hawb-check-group" id="hawb_us_export_chk_grp"><input type="checkbox" id="hawb_us_export_chk" checked><span class="print-mark">X</span></label>
        
        <!-- Box: "Ultimate Destination" -->
        <input type="text" id="hawb_ultimate_dest" class="hawb-field" value="{{ $data['export_destination'] }}">
        
        <!-- Box: "SCI" -->
        <input type="text" id="hawb_sci" class="hawb-field" value="X">

        <!-- Box: "No. of Pieces RCP" -->
        <textarea id="hawb_pieces_rcp" class="hawb-field">{{ $data['pkg_qty'] }}</textarea>
        
        <!-- Box: "Show Mark" Checkbox -->
        <label class="hawb-check-group" id="hawb_show_mark_grp"><input type="checkbox" id="hawb_show_mark" checked><span class="print-mark">X</span> <span class="chk-text">Show Mark</span></label>
        
        <!-- Box: "GROSS WEIGHT" -->
        <textarea id="hawb_gross_weight" class="hawb-field" rows="4" style="text-align: center; font-weight: bold; overflow: hidden; resize: none;">{{ $data['gross_weight'] }}</textarea>
        
        <!-- Box: "kg/lb" (Weight Unit Dropdown) -->
        <select id="hawb_weight_unit" class="hawb-select">
            <option value="kg" {{ strtolower($data['weight_unit']) == 'kg' ? 'selected' : '' }}>kg</option>
            <option value="lb" {{ strtolower($data['weight_unit']) == 'lb' ? 'selected' : '' }}>lb</option>
        </select>

        <!-- Box: "Rate Class Commodity Item No." -->
        <input type="text" id="hawb_rate_class" class="hawb-field" value="{{ $data['rate_class'] }}">
        
        <!-- Box: "Chargeable Weight" -->
        <input type="text" id="hawb_chargeable_weight" class="hawb-field" value="{{ $data['chargeable_weight'] }}">
        
        <!-- Box: "Rate / Charge" -->
        <input type="text" id="hawb_rate_charge" class="hawb-field" value="{{ $data['rate_charge'] }}">
        
        <!-- Box: "Total" -->
        <input type="text" id="hawb_total_charge" class="hawb-field" value="{{ $data['total_charge'] }}">
        
        <!-- Box: "Nature and Quantity of Goods (incl. Dimensions of Volume)" -->
        <textarea id="hawb_nature_quantity" class="hawb-field">{{ $data['nature_quantity'] }}</textarea>

        <!-- Box: "Prepaid Weight Charge" -->
        <input type="text" id="hawb_prepaid_weight_charge" class="hawb-field" value="{{ $data['prepaid_weight_charge'] }}">
        
        <!-- Box: "Collect Weight Charge" -->
        <input type="text" id="hawb_collect_weight_charge" class="hawb-field" value="{{ $data['collect_weight_charge'] }}">
        
        <!-- Box: "Prepaid Valuation Charge" -->
        <input type="text" id="hawb_prepaid_valuation" class="hawb-field" value="{{ $data['prepaid_valuation'] }}">
        
        <!-- Box: "Collect Valuation Charge" -->
        <input type="text" id="hawb_collect_valuation" class="hawb-field" value="{{ $data['collect_valuation'] }}">
        
        <!-- Box: "Prepaid Tax" -->
        <input type="text" id="hawb_prepaid_tax" class="hawb-field" value="{{ $data['prepaid_tax'] }}">
        
        <!-- Box: "Collect Tax" -->
        <input type="text" id="hawb_collect_tax" class="hawb-field" value="{{ $data['collect_tax'] }}">
        
        <!-- Box: "Total Other Charges Due Agent" (Prepaid) -->
        <input type="text" id="hawb_due_agent_prepaid" class="hawb-field" value="{{ $data['due_agent_prepaid'] }}">
        
        <!-- Box: "Total Other Charges Due Agent" (Collect) -->
        <input type="text" id="hawb_due_agent_collect" class="hawb-field" value="{{ $data['due_agent_collect'] }}">
        
        <!-- Box: "Total Other Charges Due Carrier" (Prepaid) -->
        <input type="text" id="hawb_due_carrier_prepaid" class="hawb-field" value="{{ $data['due_carrier_prepaid'] }}">
        
        <!-- Box: "Total Other Charges Due Carrier" (Collect) -->
        <input type="text" id="hawb_due_carrier_collect" class="hawb-field" value="{{ $data['due_carrier_collect'] }}">
        
        <!-- Box: "Total Prepaid" -->
        <input type="text" id="hawb_total_prepaid" class="hawb-field" value="{{ $data['total_prepaid'] }}">
        
        <!-- Box: "Total Collect" -->
        <input type="text" id="hawb_total_collect" class="hawb-field" value="{{ $data['total_collect'] }}">

        <!-- Box: Certification Radio - "Agent and Shipper" -->
        <label class="hawb-check-group" id="hawb_cert_agent_shipper_grp"><input type="radio" name="cert_by" value="agent_shipper" checked><span class="print-mark">X</span> <span class="chk-text">Agent and Shipper</span></label>
        
        <!-- Box: Certification Radio - "Shipper" -->
        <label class="hawb-check-group" id="hawb_cert_shipper_grp"><input type="radio" name="cert_by" value="shipper"><span class="print-mark">X</span> <span class="chk-text">Shipper</span></label>
        
        <!-- Box: "Signature of Shipper or his Agent" -->
        <input type="text" id="hawb_signature_shipper" class="hawb-field" value="FREIGHTX">

        <!-- Box: "Currency Conversion Rates" -->
        <input type="text" id="hawb_currency_conv" class="hawb-field" value="">
        
        <!-- Box: "CC Charges in Dest. Currency" -->
        <input type="text" id="hawb_cc_charges_dest" class="hawb-field" value="">
        
        <!-- Box: "Executed on (date)" -->
        <input type="text" id="hawb_executed_date" class="hawb-field" value="{{ $data['executed_date'] }}">
        
        <!-- Box: "at (place)" -->
        <input type="text" id="hawb_executed_place" class="hawb-field" value="{{ $data['executed_place'] }}">
        
        <!-- Box: "Signature of Issuing Carrier or its Agent" -->
        <textarea id="hawb_signature_carrier" class="hawb-field">{{ $data['signature_carrier'] }}</textarea>

        <!-- Box: "Charges at Destination" -->
        <input type="text" id="hawb_charges_at_dest" class="hawb-field" value="">
        
        <!-- Box: "Total Collect Charges" -->
        <input type="text" id="hawb_total_collect_charges" class="hawb-field" value="">

        <!-- Box: "Show ORIGINAL" Checkbox -->
        <label class="hawb-check-group" id="hawb_show_original_grp"><input type="checkbox" id="hawb_show_original" checked><span class="print-mark">X</span> <span class="chk-text">Show ORIGINAL</span></label>
    </div>
</div>

<!-- Reverse Side: IATA Conditions of Contract Legal Terms Page -->
<div class="iata-terms-wrapper page-wrapper" id="iata_terms_page">
    <h3>Notice Concerning Carrier's Limitation of Liability & Conditions of Contract</h3>
    <p style="margin-bottom:12px; text-align:center; font-style:italic;">
        If the carriage involves an ultimate destination or stop in a country other than the country of departure, the Montreal Convention or the Warsaw Convention may be applicable and the Convention governs and in most cases limits the liability of carriers in respect of loss of, damage or delay to cargo.
    </p>
    <div class="terms-grid">
        <div>
            <h4 style="font-weight:bold; margin-bottom:4px; color:#0f172a;">1. DEFINITIONS</h4>
            <p style="margin-bottom:8px;">In this contract "carrier" includes the air carrier issuing this air waybill and all air carriers that carry or undertake to carry the cargo or perform any other services related to such air carriage.</p>
            
            <h4 style="font-weight:bold; margin-bottom:4px; color:#0f172a;">2. APPLICABLE CONVENTIONS</h4>
            <p style="margin-bottom:8px;">Carriage hereunder is subject to the rules relating to liability established by the Warsaw Convention or the Montreal Convention unless such carriage is not "International Carriage" as defined by said Conventions.</p>
            
            <h4 style="font-weight:bold; margin-bottom:4px; color:#0f172a;">3. CARRIER'S TARIFFS</h4>
            <p style="margin-bottom:8px;">To the extent not in conflict with the foregoing, carriage and other services performed by each carrier are subject to: (a) applicable laws; (b) government regulations; (c) provisions herein contained; and (d) applicable tariffs, rules, conditions of carriage, regulations and timetables.</p>
        </div>
        <div>
            <h4 style="font-weight:bold; margin-bottom:4px; color:#0f172a;">4. LIMITATION OF LIABILITY</h4>
            <p style="margin-bottom:8px;">In carriage to which the Montreal Convention does not apply, carrier's liability limit for loss, damage or delay shall be 22 Special Drawing Rights per kilogram unless a higher value is declared in advance by the shipper and a supplementary charge paid if required.</p>
            
            <h4 style="font-weight:bold; margin-bottom:4px; color:#0f172a;">5. INSPECTION & HANDLING</h4>
            <p style="margin-bottom:8px;">Carrier reserves the right to examine the packaging and contents of all shipments and to reject any cargo which does not comply with applicable security and dangerous goods regulations.</p>
            
            <h4 style="font-weight:bold; margin-bottom:4px; color:#0f172a;">6. CLAIMS & TIME LIMITS</h4>
            <p style="margin-bottom:8px;">Receipt by the person entitled to delivery of the cargo without complaint is prima facie evidence that the cargo has been delivered in good condition. Notice of claim must be submitted to carrier within 14 days from date of receipt for damage/shortage, and within 21 days for delay.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const defaultData = @json($data);

    const dataSourceSelect = document.getElementById('dataSourceSelect');
    const forSelect = document.getElementById('forSelect');
    const docPackageSelect = document.getElementById('docPackageSelect');
    const chkShowAmount = document.getElementById('chkShowAmount');
    const chkPrintTerms = document.getElementById('chkPrintTerms');
    const hawbCopyDesignation = document.getElementById('hawb_copy_designation');
    const iataTermsPage = document.getElementById('iata_terms_page');

    // 1. "For" dropdown dynamic copy designation update
    const copyTitles = {
        'CARRIER': 'Original 3 (for Issuing Carrier)',
        'CONSIGNEE': 'Original 2 (for Consignee)',
        'SHIPPER': 'Original 1 (for Shipper)',
        'AGENT': 'Copy 4 (for Agent / Delivery Receipt)'
    };

    function updateCopyTitle() {
        const val = forSelect ? forSelect.value : 'CONSIGNEE';
        if (hawbCopyDesignation) {
            hawbCopyDesignation.textContent = copyTitles[val] || ('Original (' + val + ')');
        }
    }
    if (forSelect) {
        forSelect.addEventListener('change', updateCopyTitle);
        updateCopyTitle(); // initialize
    }

    // 2. "Data Source" dropdown (Shipment vs Manual Entry)
    if (dataSourceSelect) {
        dataSourceSelect.addEventListener('change', function() {
            if (this.value === 'shipment') {
                // Restore all default database values
                document.getElementById('hawb_airline_code').value = defaultData.airline_code || '';
                document.getElementById('hawb_departure_code').value = defaultData.departure_code || '';
                document.getElementById('hawb_serial_no').value = defaultData.serial_no || '';
                document.getElementById('hawb_number').value = defaultData.hawb_number || '';
                document.getElementById('hawb_shipper').value = defaultData.shipper || '';
                document.getElementById('hawb_shipper_account').value = defaultData.shipper_account_no || '';
                document.getElementById('hawb_consignee').value = defaultData.consignee || '';
                document.getElementById('hawb_consignee_account').value = defaultData.consignee_account_no || '';
                document.getElementById('hawb_issuing_agent').value = defaultData.issuing_agent || '';
                document.getElementById('hawb_agent_iata').value = defaultData.agent_iata_code || '';
                document.getElementById('hawb_agent_account').value = defaultData.agent_account_no || '';
                document.getElementById('hawb_accounting_info').value = defaultData.accounting_info || '';
                document.getElementById('hawb_airport_departure').value = defaultData.airport_departure || '';
                document.getElementById('hawb_file_no').value = defaultData.file_no || '';
                document.getElementById('hawb_routing_to1').value = defaultData.routing_to1 || '';
                document.getElementById('hawb_routing_by1').value = defaultData.routing_by1 || '';
                document.getElementById('hawb_airport_destination').value = defaultData.airport_destination || '';
                document.getElementById('hawb_flight_no').value = defaultData.requested_flight_no || '';
                document.getElementById('hawb_flight_date').value = defaultData.requested_flight_date || '';
                document.getElementById('hawb_currency').value = defaultData.currency || '';
                document.getElementById('hawb_chgs_code').value = defaultData.chgs_code || '';
                document.getElementById('hawb_declared_carriage').value = defaultData.dv_carriage || '';
                document.getElementById('hawb_declared_customs').value = defaultData.dv_customs || '';
                document.getElementById('hawb_insurance_amount').value = defaultData.insurance_amount || '';
                document.getElementById('hawb_handling_info').value = defaultData.handling_info || '';
                document.getElementById('hawb_pieces_rcp').value = defaultData.pkg_qty || '';
                document.getElementById('hawb_gross_weight').value = defaultData.gross_weight || '';
                document.getElementById('hawb_rate_class').value = defaultData.rate_class || '';
                document.getElementById('hawb_chargeable_weight').value = defaultData.chargeable_weight || '';
                document.getElementById('hawb_rate_charge').value = defaultData.rate_charge || '';
                document.getElementById('hawb_total_charge').value = defaultData.total_charge || '';
                document.getElementById('hawb_nature_quantity').value = defaultData.nature_quantity || '';
                document.getElementById('hawb_prepaid_weight_charge').value = defaultData.prepaid_weight_charge || '';
                document.getElementById('hawb_total_prepaid').value = defaultData.total_prepaid || '';
                document.getElementById('hawb_executed_date').value = defaultData.executed_date || '';
                document.getElementById('hawb_executed_place').value = defaultData.executed_place || '';
                document.getElementById('hawb_signature_carrier').value = defaultData.signature_carrier || '';
            }
        });
    }

    // 3. "Show Amount" checkbox logic
    const amountFieldIds = [
        'hawb_declared_carriage', 'hawb_declared_customs', 'hawb_insurance_amount',
        'hawb_rate_charge', 'hawb_total_charge', 'hawb_prepaid_weight_charge',
        'hawb_collect_weight_charge', 'hawb_prepaid_valuation', 'hawb_collect_valuation',
        'hawb_prepaid_tax', 'hawb_collect_tax', 'hawb_due_agent_prepaid',
        'hawb_due_agent_collect', 'hawb_due_carrier_prepaid', 'hawb_due_carrier_collect',
        'hawb_total_prepaid', 'hawb_total_collect', 'hawb_charges_at_dest',
        'hawb_total_collect_charges'
    ];

    function toggleShowAmount() {
        const isShow = chkShowAmount.checked;
        amountFieldIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (!isShow) {
                    if (!el.hasAttribute('data-orig-val')) {
                        el.setAttribute('data-orig-val', el.value);
                    }
                    el.value = '';
                    el.style.opacity = '0';
                } else {
                    if (el.hasAttribute('data-orig-val')) {
                        el.value = el.getAttribute('data-orig-val');
                    }
                    el.style.opacity = '1';
                }
            }
        });
    }

    if (chkShowAmount) {
        chkShowAmount.addEventListener('change', toggleShowAmount);
    }

    // 4. "Print IATA Terms" checkbox logic
    if (chkPrintTerms) {
        chkPrintTerms.addEventListener('change', function() {
            if (iataTermsPage) {
                iataTermsPage.style.display = this.checked ? 'block' : 'none';
            }
        });
    }

    // 5. Accounting Information Radio Buttons (Notify vs Oversea Agent)
    const radioNotify = document.querySelector('input[name="load_from"][value="notify"]');
    const radioOversea = document.querySelector('input[name="load_from"][value="oversea"]');
    const hawbAccountingInfo = document.getElementById('hawb_accounting_info');

    if (radioNotify && radioOversea && hawbAccountingInfo) {
        radioNotify.addEventListener('change', function() {
            if (this.checked) {
                hawbAccountingInfo.value = defaultData.notify_info || 'NOTIFY: SAME AS CONSIGNEE';
            }
        });
        radioOversea.addEventListener('change', function() {
            if (this.checked) {
                hawbAccountingInfo.value = defaultData.oversea_info || 'OVERSEA AGENT: FREIGHTX GLOBAL NETWORK';
            }
        });
    }
});
</script>

</body>
</html>
