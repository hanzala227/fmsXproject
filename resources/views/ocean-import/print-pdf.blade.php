<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cargo Manifest - {{ $shipment->file_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            color: #333; 
            margin: 0; 
            padding: 30px; 
            font-size: 10px; 
            line-height: 1.4; 
            background: #fff;
        }
        .container { max-width: 1000px; margin: auto; }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        .company-info h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #1a365d;
        }
        .company-info p {
            margin: 0;
            color: #555;
            font-size: 10px;
        }
        .manifest-title {
            border: 1px solid #1a365d;
            color: #1a365d;
            font-size: 18px;
            font-weight: bold;
            padding: 8px 60px;
            text-align: center;
        }

        .main-info {
            display: flex;
            gap: 2%;
            margin-bottom: 20px;
        }
        .left-panel {
            width: 65%;
        }
        .right-panel {
            width: 33%;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            border: 1px solid #666;
            padding: 6px;
            font-size: 10px;
        }
        .info-table td.label {
            background-color: #f3f4f6;
            font-weight: bold;
            width: 25%;
            color: #333;
        }
        .info-table td.value {
            width: 25%;
            background-color: #f9fafb;
            color: #111;
        }

        .container-box {
            border: 1px solid #666;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .container-box-header {
            background-color: #f3f4f6;
            padding: 6px;
            font-weight: bold;
            border-bottom: 1px solid #666;
            font-size: 10px;
        }
        .container-box-body {
            background-color: #f9fafb;
            padding: 6px;
            flex-grow: 1;
            min-height: 125px;
        }
        .container-item {
            margin-bottom: 4px;
        }

        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .bottom-table th, .bottom-table td {
            padding: 6px 4px;
            text-align: left;
            font-size: 10px;
        }
        .bottom-table th {
            border-top: 1.5px solid #333;
            border-bottom: 1.5px solid #333;
            font-weight: bold;
        }
        .bottom-table td {
            border-bottom: 1px solid #eee;
        }
        .bottom-table .total-row td {
            border-top: 1.5px solid #333;
            border-bottom: 1.5px solid #333;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }

        .btn-print { 
            background: #3b82f6; 
            color: #fff; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 4px; 
            font-weight: bold; 
            cursor: pointer; 
            position: absolute;
            top: 10px;
            right: 10px;
        }
        @media print {
            .btn-print { display: none; }
            body { padding: 10px; }
        }
        
        .sub-row {
            color: #555;
            font-size: 9px;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
    
        .editable-input {
            width: 100%;
            border: 1px dashed transparent;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            padding: 2px;
            margin: -2px;
        }
        .editable-input:hover, .editable-input:focus {
            border-color: #ccc;
            background: #fffdf0;
            outline: none;
        }
        .editable-input.text-right { text-align: right; }
        .sub-row .editable-input { width: calc(100% - 20px); }
        .btn-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 50;
        }
        .btn-action {
            background: #3b82f6; 
            color: #fff; 
            border: 1px solid #2563eb; 
            padding: 6px 12px; 
            border-radius: 4px; 
            font-weight: bold; 
            cursor: pointer;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-action:hover { background: #2563eb; }
        .btn-action-green { background: #10b981; border-color: #059669; }
        .btn-action-green:hover { background: #059669; }
        
        @media print {
            .btn-toolbar { display: none !important; }
            .editable-input { border: none !important; background: transparent !important; margin: 0; padding: 0; }
        }

        
        .color-picker-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 100;
            display: flex;
            padding: 10px;
            gap: 15px;
            border-radius: 4px;
        }
        .color-section {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .color-title { text-align: center; font-size: 11px; margin-bottom: 2px; }
        .color-btn-wide {
            width: 100%; text-align: center; font-size: 10px; border: 1px solid #ccc; background: #fff; padding: 2px 0; cursor: pointer;
        }
        .color-btn-wide:hover { background: #eee; }
        .color-grid {
            display: grid;
            grid-template-columns: repeat(8, 16px);
            gap: 1px;
        }
        .color-cell {
            width: 16px; height: 16px; border: 1px solid transparent; cursor: pointer;
        }
        .color-cell:hover { border: 1px solid #000; }
        .rich-textarea {
            width: 100%; height: 200px; border: 1px solid #ccc; border-radius: 0 0 3px 3px;
            padding: 10px; font-family: inherit; font-size: 12px; overflow-y: auto; background: #fff;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); z-index: 1000;
            display: flex; justify-content: center; align-items: center;
        }
        .modal-content {
            background: #fff; width: 800px; max-width: 95%;
            border-radius: 4px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex; flex-direction: column;
            max-height: 95vh;
        }
        .modal-header {
            padding: 12px 16px; border-bottom: 1px solid #ddd;
            display: flex; justify-content: space-between; align-items: center;
            font-weight: bold; font-size: 14px;
        }
        .modal-close { cursor: pointer; color: #888; font-size: 16px; }
        .modal-close:hover { color: #333; }
        .modal-body { padding: 16px; overflow-y: auto; font-size: 12px; }
        .modal-footer {
            padding: 12px 16px; border-top: 1px solid #ddd;
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .form-group {
            display: flex; margin-bottom: 10px; align-items: center;
        }
        .form-label { width: 80px; color: #555; text-align: right; margin-right: 15px; }
        .form-control-wrap { flex: 1; display: flex; align-items: center; gap: 5px; }
        .form-input {
            width: 100%; padding: 6px 8px; border: 1px solid #ccc; border-radius: 3px; font-size: 12px;
        }
        .btn-plus {
            background: #3b82f6; color: #fff; border: none; width: 24px; height: 24px;
            border-radius: 50%; cursor: pointer; display: flex; justify-content: center; align-items: center;
        }
        .btn-plus:hover { background: #2563eb; }
        .btn-upload { background: #14b8a6; color: white; border: none; padding: 4px 10px; border-radius: 3px; cursor: pointer; }
        .btn-doc { background: #0d9488; color: white; border: none; padding: 4px 10px; border-radius: 3px; cursor: pointer; }
        .attachment-tag { background: #94a3b8; color: white; padding: 4px 8px; border-radius: 3px; display: flex; align-items: center; gap: 5px; font-size: 11px;}
        .attachment-tag i { cursor: pointer; }
        .rich-text-toolbar {
            border: 1px solid #ccc; border-bottom: none; padding: 5px; display: flex; gap: 5px; background: #f9f9f9;
            border-radius: 3px 3px 0 0;
        }
        .rt-btn { background: #fff; border: 1px solid #ddd; padding: 4px 8px; cursor: pointer; font-size: 12px; }
        .rt-btn:hover { background: #eee; }
        .rich-textarea {
            width: 100%; height: 200px; border: 1px solid #ccc; border-radius: 0 0 3px 3px;
            padding: 10px; font-family: inherit; font-size: 12px; resize: vertical;
        }
        .btn-default { background: #e5e7eb; border: 1px solid #d1d5db; padding: 6px 16px; border-radius: 3px; cursor: pointer; }
        .btn-send { background: #60a5fa; color: white; border: 1px solid #3b82f6; padding: 6px 16px; border-radius: 3px; cursor: pointer; }

    </style>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>

<div x-data="manifestApp()" x-cloak>
    <div class="btn-toolbar">
        <button class="btn-action" @click="window.print()"><i class="fa fa-print"></i> Print</button>
        <button class="btn-action" @click="window.print()"><i class="fa fa-file-pdf-o"></i> Save PDF</button>
        <button class="btn-action btn-action-green" @click="showEmailModal = true"><i class="fa fa-envelope"></i> Email</button>
    </div>

    <div class="container">
        
        <div class="header-top">
            <div class="company-info">
                <h2>FREIGHTX</h2>
                <p>9149 WILKERSON MEWS SUITE 546</p>
                <p>NEW VALERIEVIEW, VI 34553-1977</p>
                <p>TEL: 045-085-5813x845 FAX: 045-085-5813x845</p>
                <p style="margin-top:5px;font-weight:bold;">Prepared by {{ auth()->user()->name ?? 'DEMO_925' }} {{ date('m-d-Y H:i') }}</p>
            </div>
            <div class="manifest-title">
                CARGO MANIFEST
            </div>
        </div>

        <div class="main-info">
            <div class="left-panel">
                <table class="info-table">
                    <tr>
                        <td class="label">MASTER B/L NO.</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->mbl_no ?? '' }}"></td>
                        <td class="label">Booking No.</td>
                        <td class="value"><strong><input class="editable-input" type="text" style="font-weight:bold;" value="{{ $shipment->file_no ?? '' }}"></strong></td>
                    </tr>
                    <tr>
                        <td class="label">VESSEL</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->vessel->name ?? '' }}"></td>
                        <td class="label">VOYAGE</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->voyage ?? '' }}"></td>
                    </tr>
                    <tr>
                        <td class="label">P.O.L</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->portOfLoading->name ?? '' }}"></td>
                        <td class="label">ETD</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '' }}"></td>
                    </tr>
                    <tr>
                        <td class="label">P.O.D</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->portOfDischarge->name ?? '' }}"></td>
                        <td class="label">ETA</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '' }}"></td>
                    </tr>
                    <tr>
                        <td class="label">PICKUP LOCATION</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->placeOfDelivery->name ?? '' }}"></td>
                        <td class="label">ETA</td>
                        <td class="value"><input class="editable-input" type="text" value="{{ $shipment->final_eta ? $shipment->final_eta->format('m-d-Y') : '' }}"></td>
                    </tr>
                </table>
            </div>
            <div class="right-panel">
                <div class="container-box">
                    <div class="container-box-header">
                        CONTAINER NO. / SEAL# / SIZE
                    </div>
                    <div class="container-box-body">
                        @forelse($shipment->containers as $c)
                            <div class="container-item">
                                <input class="editable-input" type="text" value="{{ $c->container_no }} / {{ $c->seal_no }} / {{ $c->containerType->code ?? '' }}">
                            </div>
                        @empty
                            <div style="color:#aaa; font-style:italic;">No containers</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <table class="bottom-table">
            <thead>
                <tr>
                    <th style="width: 5%">NO.</th>
                    <th style="width: 15%">H B/L NO.<br><div class="sub-row">SHIPPER</div></th>
                    <th style="width: 15%">CONSIGNEE<br><div class="sub-row">NOTIFY</div></th>
                    <th style="width: 15%">F.DESTINATION<br><div class="sub-row">F.ETA</div></th>
                    <th style="width: 20%">COMMODITY<br><div class="sub-row">PKG UNIT</div></th>
                    <th style="width: 10%" class="text-right">KGS<br><div class="sub-row" style="justify-content: flex-end;">LBS</div></th>
                    <th style="width: 10%" class="text-right">CBM<br><div class="sub-row" style="justify-content: flex-end;">CFT</div></th>
                    <th style="width: 10%" class="text-right">TERM<br><div class="sub-row" style="justify-content: flex-end;">AMOUNT</div></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPkg = 0;
                    $totalKgs = 0;
                    $totalLbs = 0;
                    $totalCbm = 0;
                    $totalCft = 0;
                    $totalAmount = 0;
                @endphp
                @forelse($shipment->hbls as $index => $hbl)
                    @php
                        $totalPkg += (float)($hbl->pkg_qty ?? 0);
                        $totalKgs += (float)($hbl->weight_kg ?? 0);
                        $totalLbs += (float)($hbl->weight_lbs ?? 0);
                        $totalCbm += (float)($hbl->measure_cbm ?? 0);
                        $totalCft += (float)($hbl->measure_cft ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong><input class="editable-input" type="text" style="font-weight:bold; width: 100%;" value="{{ $hbl->hbl_no }}"></strong>
                            <div class="sub-row">
                                <input type="checkbox" checked style="margin:0 2px 0 0;"> <input class="editable-input" type="text" value="{{ $hbl->shipper->name ?? 'SHIPPER' }}">
                            </div>
                        </td>
                        <td>
                            <input class="editable-input" type="text" value="{{ $hbl->consignee->name ?? '' }}">
                            <div class="sub-row">
                                <input type="checkbox" checked style="margin:0 2px 0 0;"> <input class="editable-input" type="text" value="{{ $hbl->dmNotify->name ?? 'NOTIFY' }}">
                            </div>
                        </td>
                        <td>
                            <input class="editable-input" type="text" value="{{ $hbl->deliveryLocation->name ?? '' }}">
                            <div class="sub-row">
                                <input class="editable-input" type="text" value="{{ $hbl->final_eta ? $hbl->final_eta->format('m-d-Y') : '' }}">
                            </div>
                        </td>
                        <td>
                            <input class="editable-input" type="text" value="{{ $hbl->commodity ?? '' }}">
                            <div class="sub-row">
                                <input class="editable-input" type="text" value="{{ $hbl->pkg_qty }} {{ $hbl->packageUnit->code ?? 'PCS' }}">
                            </div>
                        </td>
                        <td class="text-right">
                            <input class="editable-input text-right" type="text" value="{{ number_format((float)($hbl->weight_kg ?? 0), 2) }}">
                            <div class="sub-row" style="justify-content: flex-end;">
                                <input class="editable-input text-right" type="text" value="{{ number_format((float)($hbl->weight_lbs ?? 0), 2) }}">
                            </div>
                        </td>
                        <td class="text-right">
                            <input class="editable-input text-right" type="text" value="{{ number_format((float)($hbl->measure_cbm ?? 0), 2) }}">
                            <div class="sub-row" style="justify-content: flex-end;">
                                <input class="editable-input text-right" type="text" value="{{ number_format((float)($hbl->measure_cft ?? 0), 2) }}">
                            </div>
                        </td>
                        <td class="text-right">
                            <input class="editable-input text-right" type="text" value="{{ $hbl->freight_term ?? '' }}">
                            <div class="sub-row" style="justify-content: flex-end;">
                                <input class="editable-input text-right" type="text" value="0.00">
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #777;">No House Bills attached.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td></td>
                    <td colspan="2">{{ $shipment->hbls->count() }} H B/L(s)</td>
                    <td>{{ $totalPkg }} PCS</td>
                    <td class="text-right">
                        {{ number_format($totalKgs, 2) }}
                        <div class="sub-row" style="justify-content: flex-end; font-weight:bold; color:#333;">
                            {{ number_format($totalLbs, 2) }}
                        </div>
                    </td>
                    <td class="text-right">
                        {{ number_format($totalCbm, 2) }}
                        <div class="sub-row" style="justify-content: flex-end; font-weight:bold; color:#333;">
                            {{ number_format($totalCft, 2) }}
                        </div>
                    </td>
                    <td class="text-right">
                        <br>
                        <div class="sub-row" style="justify-content: flex-end; font-weight:bold; color:#333;">
                            {{ number_format($totalAmount, 2) }}
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Email Modal -->
    <div class="modal-overlay" x-show="showEmailModal" style="display: none;" x-cloak>
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <div>Send Email</div>
                <div class="modal-close" @click="showEmailModal = false">&times;</div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="form-label">From</div>
                    <div class="form-control-wrap">
                        <select class="form-input" x-model="emailForm.from">
                            <option value="{{ auth()->user()->email ?? 'demo@freightx.com' }}">{{ auth()->user()->email ?? 'demo@freightx.com' }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-label">To</div>
                    <div class="form-control-wrap">
                        <input type="text" class="form-input" x-model="emailForm.to" placeholder="Add email address here...">
                        <button class="btn-plus" @click="emailForm.to += (emailForm.to && !emailForm.to.endsWith(',') ? ', ' : '')"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-label">CC</div>
                    <div class="form-control-wrap">
                        <input type="text" class="form-input" x-model="emailForm.cc" placeholder="Add email address here...">
                        <button class="btn-plus" @click="emailForm.cc += (emailForm.cc && !emailForm.cc.endsWith(',') ? ', ' : '')"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-label">BCC</div>
                    <div class="form-control-wrap" style="flex-wrap: wrap;">
                        <input type="text" class="form-input" style="width: calc(100% - 35px);" x-model="emailForm.bcc" placeholder="Add email address here...">
                        <button class="btn-plus" @click="emailForm.bcc += (emailForm.bcc && !emailForm.bcc.endsWith(',') ? ', ' : '')"><i class="fa fa-plus"></i></button>
                        <div style="width: 100%; margin-top: 5px; padding-left: 5px;">
                            <label style="display:flex; align-items:center; gap:5px; color:#555;">
                                <input type="checkbox" x-model="includeMyself"> Include myself
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-label">Attachment</div>
                    <div class="form-control-wrap">
                        <input type="file" x-ref="fileInput" multiple style="display:none;" @change="handleFileUpload">
                        <button class="btn-upload" @click.prevent="$refs.fileInput.click()"><i class="fa fa-plus"></i> Upload</button>
                        <button class="btn-doc" @click.prevent="showDocModal = true"><i class="fa fa-folder-open"></i> Doc Center</button>
                        
                        <template x-for="att in emailForm.attachments" :key="att.id">
                            <div class="attachment-tag">
                                <span x-text="att.name"></span> 
                                <i class="fa fa-times" @click="removeAttachment(att.id)"></i>
                            </div>
                        </template>

                    </div>
                </div>
                <div class="form-group" style="margin-top:-5px; margin-bottom:15px;">
                    <div class="form-label"></div>
                    <div style="color:#888; font-size:10px;"><i class="fa fa-info-circle"></i> Total attachment size: 145 KB+</div>
                </div>
                <div class="form-group">
                    <div class="form-label">Subject</div>
                    <div class="form-control-wrap" style="gap: 10px;">
                        <input type="text" class="form-input" style="font-weight: bold; background: #f3f4f6;" readonly x-model="emailForm.subject_left">
                        <input type="text" class="form-input" style="font-weight: bold;" x-model="emailForm.subject_right">
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-label">Show</div>
                    <div class="form-control-wrap" style="gap: 15px; color: #555;">
                        <label><input type="radio" name="show_type" value="our_company" x-model="showType" @change="generateContent"> Our Company</label>
                        <label><input type="radio" name="show_type" value="customer" x-model="showType" @change="generateContent"> Customer</label>
                        <select style="border:1px solid #ccc; border-radius:3px; padding:2px;"><option>Name</option></select>
                        <label><input type="radio" name="show_type" value="blank" x-model="showType" @change="generateContent"> Blank</label>
                        
                        <span style="font-weight:bold; margin-left:10px;">More Info</span>
                        <label><input type="checkbox" value="file_no" x-model="showInfo" @change="generateContent"> Booking No.</label>
                        <label><input type="checkbox" value="mbl_no" x-model="showInfo" @change="generateContent"> MB/L No.</label>
                        <label><input type="checkbox" value="eta" x-model="showInfo" @change="generateContent"> ETA</label>
                    </div>
                </div>
                <div class="form-group" style="align-items: flex-start;">
                    <div class="form-label" style="margin-top: 10px;">Content</div>
                    <div class="form-control-wrap" style="flex-direction: column; align-items: stretch;">
                        <div class="rich-text-toolbar">
                            <button class="rt-btn" @mousedown.prevent="execCmd('removeFormat')"><i class="fa fa-magic"></i></button>
                            <div style="border-left:1px solid #ccc; width:1px; margin: 0 2px;"></div>
                            <button class="rt-btn" @mousedown.prevent="execCmd('bold')"><b>B</b></button>
                            <button class="rt-btn" @mousedown.prevent="execCmd('italic')"><i>I</i></button>
                            <button class="rt-btn" @mousedown.prevent="execCmd('underline')"><u>U</u></button>
                            <button class="rt-btn" @mousedown.prevent="execCmd('strikeThrough')"><strike>S</strike></button>
                            <button class="rt-btn" @mousedown.prevent="execCmd('removeFormat')"><i class="fa fa-eraser"></i></button>
                            
                            <!-- Color Picker Dropdown -->
                            <div style="position: relative;" @click.away="showColorPicker = false">
                                <button class="rt-btn" style="color: #000; font-weight: bold; background: #ffeb3b; padding: 4px 8px; display:flex; align-items:center; gap:2px;" @mousedown.prevent="showColorPicker = !showColorPicker">
                                    A <i class="fa fa-caret-down"></i>
                                </button>
                                
                                <div x-show="showColorPicker" class="color-picker-dropdown" style="display: none;" x-cloak>
                                    <!-- Background Color -->
                                    <div class="color-section">
                                        <div class="color-title">Background Color</div>
                                        <button class="color-btn-wide" @mousedown.prevent="execCmd('hiliteColor', 'transparent')">Transparent</button>
                                        <div class="color-grid">
                                            <template x-for="row in colors" :key="row[0]">
                                                <template x-for="c in row" :key="c">
                                                    <div class="color-cell" :style="`background-color: ${c}`" @mousedown.prevent="execCmd('hiliteColor', c)"></div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <!-- Text Color -->
                                    <div class="color-section">
                                        <div class="color-title">Text Color</div>
                                        <button class="color-btn-wide" @mousedown.prevent="execCmd('foreColor', '#000000')">Reset to default</button>
                                        <div class="color-grid">
                                            <template x-for="row in colors" :key="row[0]">
                                                <template x-for="c in row" :key="c">
                                                    <div class="color-cell" :style="`background-color: ${c}`" @mousedown.prevent="execCmd('foreColor', c)"></div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="border-left:1px solid #ccc; width:1px; margin: 0 2px;"></div>
                            <button class="rt-btn" @mousedown.prevent="execCmd('insertUnorderedList')"><i class="fa fa-list-ul"></i></button>
                            <button class="rt-btn" @mousedown.prevent="execCmd('insertOrderedList')"><i class="fa fa-list-ol"></i></button>
                            <button class="rt-btn" @click.prevent="fullscreen = !fullscreen"><i class="fa" :class="fullscreen ? 'fa-compress' : 'fa-arrows-alt'"></i></button>
                        </div>
                        
                        <!-- Content Editable Body -->
                        <div x-ref="editor" 
                             class="rich-textarea" 
                             :style="fullscreen ? 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999; margin: 0; border-radius: 0;' : ''"
                             contenteditable="true" 
                             @input="updateBodyHTML"
                             x-html="emailForm.body">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-default" @click="showEmailModal = false">Cancel</button>
                <button class="btn-send" @click="sendEmail" x-text="sending ? 'Sending...' : 'Send'" :disabled="sending"></button>
            </div>
        </div>
    </div>


    <!-- Doc Center Modal -->
    <div class="modal-overlay" x-show="showDocModal" style="display: none; z-index: 1010;" x-cloak>
        <div class="modal-content" @click.stop style="width: 500px; padding: 0; overflow: hidden; border-radius: 4px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            <div style="background: #475569; color: white; padding: 12px 16px; font-weight: bold; font-size: 13px; text-transform: uppercase;">
                PLEASE SELECT FILE
                <div class="modal-close" @click="showDocModal = false" style="color: white; float: right;">&times;</div>
            </div>
            <div style="max-height: 300px; overflow-y: auto; background: #64748b; color: white;">
                <template x-for="doc in availableTools" :key="doc.id">
                    <div style="padding: 10px 16px; border-bottom: 1px solid #475569; display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" :value="doc.id" x-model="selectedDocIds">
                        <span x-text="doc.original_name || doc.file_name"></span>
                    </div>
                </template>
                <div x-show="availableTools.length === 0" style="padding: 20px; color: #cbd5e1; text-align: center;">
                    No documents found in Doc Center.
                </div>
            </div>
            <button style="width: 100%; background: #2dd4bf; color: white; border: none; padding: 12px; font-weight: bold; cursor: pointer;" @click="applyTools">Apply</button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity.duration.300ms style="position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 12px 24px; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999; display: none;">
        <i class="fa fa-check-circle" style="margin-right: 8px;"></i>
        <span x-text="toast.message"></span>
    </div>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('manifestApp', () => ({
                showEmailModal: false,
                showDocModal: false,
                showColorPicker: false,
                fullscreen: false,
                sending: false,
                toast: {
                    show: false,
                    message: ''
                },
                availableTools: @json($shipment->documents ?? []),
                selectedDocIds: [],
                includeMyself: false,
                showType: 'our_company',
                showInfo: ['file_no'],
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
                emailForm: {
                    from: '{{ auth()->user()->email ?? "demo@freightx.com" }}',
                    to: 'shawon@silk-container.com',
                    cc: '',
                    bcc: '',
                    subject: '',
                    subject_left: '[FREIGHTX][Ocean-{{ $shipment->file_no }}]',
                    subject_right: '',
                    body: `***PLEASE CONFIRM UPON RECEIPT***<br>THANK YOU<br><br>{{ auth()->user()->name ?? 'DEMO_925' }}<br>{{ auth()->user()->email ?? 'shawon@silk-container.com' }}<br><br>FREIGHTX<br>9149 WILKERSON MEWS SUITE 546 NEW VALERIEVIEW, VI 34553-1977<br>TEL 045-085-5813x845<br>FAX 045-085-5813x845<br><br>Sent from FreightX`,
                    attachments: [
                        { id: 'manifest', name: 'Cargo Manifest - {{ $shipment->file_no }}.pdf' }
                    ]
                },
                
                applyTools() {
                    this.availableTools.forEach(doc => {
                        if (this.selectedDocIds.includes(doc.id.toString()) || this.selectedDocIds.includes(doc.id)) {
                            if (!this.emailForm.attachments.find(a => a.id === doc.id)) {
                                this.emailForm.attachments.push({
                                    id: doc.id,
                                    name: doc.original_name || doc.file_name
                                });
                            }
                        }
                    });
                    this.showDocModal = false;
                },
                
                removeAttachment(id) {
                    this.emailForm.attachments = this.emailForm.attachments.filter(a => a.id !== id);
                },

                init() {
                    this.$watch('includeMyself', value => {
                        const myEmail = '{{ auth()->user()->email ?? "demo@freightx.com" }}';
                        if(value) {
                            this.emailForm.bcc = this.emailForm.bcc ? this.emailForm.bcc + (this.emailForm.bcc.endsWith(',') ? ' ' : ', ') + myEmail : myEmail;
                        } else {
                            this.emailForm.bcc = this.emailForm.bcc.replace(myEmail, '').replace(/,\s*,/g, ',').replace(/^,|,$/g, '').trim();
                        }
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
                    this.emailForm.body = this.$refs.editor.innerHTML;
                },

                handleFileUpload(e) {
                    const files = e.target.files;
                    for(let i = 0; i < files.length; i++) {
                        const file = files[i];
                        this.emailForm.attachments.push({
                            id: 'local_' + Date.now() + '_' + i,
                            name: file.name
                        });
                    }
                    e.target.value = ''; // Reset input
                },

                generateContent() {
                    let text = `***PLEASE CONFIRM UPON RECEIPT***<br>THANK YOU<br><br>`;
                    
                    if (this.showInfo.includes('file_no')) text += `Booking No: {{ $shipment->file_no }}<br>`;
                    if (this.showInfo.includes('mbl_no')) text += `MB/L No: {{ $shipment->mbl_no }}<br>`;
                    if (this.showInfo.includes('eta')) text += `ETA: {{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '' }}<br>`;
                    
                    text += `<br>{{ auth()->user()->name ?? 'DEMO_925' }}<br>{{ auth()->user()->email ?? 'shawon@silk-container.com' }}<br><br>`;
                    
                    if (this.showType === 'our_company') {
                        text += `FREIGHTX<br>9149 WILKERSON MEWS SUITE 546 NEW VALERIEVIEW, VI 34553-1977<br>TEL 045-085-5813x845<br>FAX 045-085-5813x845<br><br>Sent from FreightX`;
                    } else if (this.showType === 'customer') {
                        text += `CUSTOMER LOGISTICS<br>123 SUPPLY CHAIN BLVD<br>LOGISTICS CITY, CA 90210<br>TEL 800-555-0199<br><br>Sent via FreightX`;
                    } else if (this.showType === 'blank') {
                        text += `Sent from FreightX`;
                    }
                    
                    this.emailForm.body = text;
                    if (this.$refs.editor) {
                        this.$refs.editor.innerHTML = text;
                    }
                },
                
                sendEmail() {
                    if(!this.emailForm.to) {
                        alert('Please enter at least one recipient in the "To" field.');
                        return;
                    }
                    
                    this.sending = true;
                    this.emailForm.subject = this.emailForm.subject_left + ' ' + this.emailForm.subject_right;
                    
                    fetch(`/ocean-import/{{ $shipment->id }}/send-manifest-email`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.emailForm)
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.sending = false;
                        this.showEmailModal = false;
                        this.showToast(data.message || 'Email sent successfully!');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.sending = false;
                        alert('Failed to send email. Check your SMTP configuration in .env');
                    });
                },
                
                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 3000);
                }
            }));
        });
    </script>

</body>
</html>
