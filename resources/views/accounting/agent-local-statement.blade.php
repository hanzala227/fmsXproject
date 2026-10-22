<x-layout>
    @push('styles')
    <style>
        .page-content{padding:8px 12px;background:#eef1f5;min-height:calc(100vh - 50px);font-family:'Inter','Open Sans',sans-serif!important}
        .portlet.light{background-color:#fff;border:1px solid #cbd5e1;border-radius:2px;margin-bottom:10px!important;box-shadow:0 1px 2px rgba(0,0,0,.05)}
        .portlet-title{padding:4px 10px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;min-height:28px;background:#f8fafc}
        .portlet-body{padding:12px 14px}
        .caption-subject{color:#1e293b;font-size:12px;font-weight:700;text-transform:uppercase}
        .page-bar{background-color:#fff;padding:8px 20px;margin-bottom:15px;border:1px solid #e9ebec;border-radius:4px}
        .page-breadcrumb{list-style:none;padding:0;margin:0;display:flex;align-items:center}
        .page-breadcrumb li{font-size:12px;color:#888;display:flex;align-items:center}
        .page-breadcrumb li a{color:#337ab7;text-decoration:none}
        .page-breadcrumb li i{margin:0 8px;font-size:10px;opacity:.5}
        .text-danger{color:#e73d4a}
        .form-table{width:100%;border-collapse:collapse}
        .form-table td{padding:3px 4px;vertical-align:middle;font-size:11px;border:none}
        .form-table .flabel{background:#eef1f5;width:130px;padding:4px 8px;font-weight:600;color:#333;white-space:nowrap}
        .form-table .finput{padding-left:8px}
        .form-table input[type="radio"],.form-table input[type="checkbox"]{accent-color:#3b82f6;margin-right:3px}
        .form-table label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap}
        .form-table select,.gf-select{height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;color:#333;background:#fff}
        .form-table input[type="date"]{height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;color:#333;background:#fff}
        .radio-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
        .check-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
        .btn-search-gf{background:#4CAF50;color:#fff;border:1px solid #388E3C;border-radius:2px;padding:0 16px;height:26px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:4px}
        .btn-search-gf:hover{background:#388E3C;color:#fff}
        .btn-action-round{background:#3b82f6;color:#fff;border:1px solid #2563eb;border-radius:2px;padding:0 12px;height:26px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;gap:4px;cursor:pointer;text-decoration:none;transition:all .15s;box-sizing:border-box}
        .btn-action-round:hover{background:#2563eb;color:#fff}
        .btn-action-round.secondary{background:#64748b;border-color:#475569}
        .btn-action-round.secondary:hover{background:#475569}
        .toggle-switch{position:relative;display:inline-block;width:36px;height:20px}
        .toggle-switch input{opacity:0;width:0;height:0}
        .toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#ccc;transition:.3s;border-radius:20px}
        .toggle-slider:before{position:absolute;content:"";height:16px;width:16px;left:2px;bottom:2px;background:#fff;transition:.3s;border-radius:50%}
        input:checked+.toggle-slider{background:#3b82f6}
        input:checked+.toggle-slider:before{transform:translateX(16px)}
        .table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:8px;padding:6px 8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:3px}
        .table-toolbar .left{display:flex;align-items:center;gap:8px}
        .table-toolbar .right{display:flex;align-items:center;gap:6px}
        .input-search{height:22px;border:1px solid #c2cad8;padding:1px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff;width:160px}
        .input-search:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 1px rgba(59,130,246,.2)}
        .grid-container{width:100%;overflow-x:auto;background:#fff;margin-top:0}
        .grid-table{border-collapse:collapse;width:100%;font-size:11px}
        .grid-table th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:4px 6px;white-space:nowrap;text-align:left;font-size:10px;cursor:pointer;user-select:none}
        .grid-table th:hover{background:#5a6578}
        .grid-table td{padding:3px 6px;border:1px solid #e2e8f0;white-space:nowrap;color:#334155}
        .grid-table td.num{text-align:right;font-family:'Courier New',monospace}
        .grid-table tbody tr:hover{background:#f1f5f9}
        .grid-table .total-row{background:#f8fafc!important;font-weight:700}
        .grid-table .total-row td{border-top:2px solid #475569;color:#0f172a}
        .sort-icon{font-size:9px;opacity:.6;margin-left:2px}
        .filter-icon{font-size:9px;color:#93c5fd;margin-left:2px}
        .doc-list-header{background:#2c3e50;color:#fff;padding:6px 12px;display:flex;align-items:center;justify-content:space-between;border-radius:2px 2px 0 0}
        .doc-list-header h6{margin:0;font-size:12px;font-weight:700;letter-spacing:.5px}
        .pagination-sm{display:flex;gap:2px;list-style:none;padding:0;margin:0}
        .pagination-sm li span{display:block;padding:3px 8px;font-size:11px;border:1px solid #dee2e6;border-radius:2px;color:#337ab7}
        .pagination-sm li.active span{background:#3b82f6;color:#fff;border-color:#3b82f6}
        .pagination-sm li.disabled span{color:#6c757d;cursor:not-allowed;background:#f8f9fa}
        .pagination-sm li{display:flex;align-items:center}
        .config-panel{position:absolute;right:0;top:32px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;box-shadow:0 6px 20px rgba(0,0,0,.12);z-index:200;min-width:200px;max-height:320px;overflow-y:auto;padding:8px;font-size:10px;text-align:left;display:none}
        .config-panel-title{font-weight:700;color:#475569;margin-bottom:6px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;padding-bottom:4px;border-bottom:1px solid #e2e8f0}
        .config-panel label{display:flex;align-items:center;gap:6px;padding:3px 4px;cursor:pointer;border-radius:2px;color:#334155}
        .config-panel label:hover{background:#f1f5f9}
        .btn-action-sm{background:#3b82f6;color:#fff;border:none;border-radius:2px;padding:2px 8px;height:20px;font-size:10px;font-weight:600;cursor:pointer}
        .btn-action-sm:hover{background:#2563eb}
        .action-dropdown{position:relative;display:inline-block}
        .action-dropdown-menu{display:none;position:absolute;right:0;top:100%;background:#fff;border:1px solid #cbd5e1;border-radius:3px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:100;min-width:140px;padding:4px 0;margin-top:2px}
        .action-dropdown-menu.show{display:block}
        .action-dropdown-menu a,.action-dropdown-menu button{display:block;width:100%;padding:5px 12px;font-size:11px;color:#334155;background:none;border:none;text-align:left;cursor:pointer;white-space:nowrap;text-decoration:none}
        .action-dropdown-menu a:hover,.action-dropdown-menu menu button:hover{background:#f1f5f9}
        .amt-confirmed-cb{cursor:pointer;accent-color:#3b82f6}
    </style>
    @endpush

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Report</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Agent / Local Statement</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject">Agent / Local Statement</span>
            </div>
            <div class="portlet-body">
                <table class="form-table">
                    <tr>
                        <td class="flabel"><span class="text-danger">*</span>Partner</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row" style="margin-bottom:3px;">
                                <label><input type="radio" name="partner_type" value="agent_customer" checked id="partnerTypeAC"> Agent/Customer</label>
                                <label><input type="radio" name="partner_type" value="account_group" id="partnerTypeAG"> Account Group</label>
                            </div>
                            <select id="billToId" class="gf-select" style="width:220px;">
                                <option value="">Select...</option>
                                @foreach($tradePartners as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach
                            </select>
                            <select id="accountGroupId" class="gf-select" style="width:220px;display:none;">
                                <option value="">Select...</option>
                                @foreach($accountGroups as $ag)<option value="{{ $ag }}">{{ $ag }}</option>@endforeach
                            </select>
                        </td>
                        <td class="flabel">Accounting mode</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row">
                                <label><input type="radio" name="acct_mode" value="invoice" checked> Invoice mode</label>
                                <label><input type="radio" name="acct_mode" value="shipment"> Shipment mode</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel"><span class="text-danger">*</span>Period</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row" style="margin-bottom:3px;">
                                <select id="periodType" class="gf-select" style="width:100px;">
                                    <option value="post_date">Post Date</option>
                                </select>
                                <span style="font-size:11px;color:#334155;">Show on report:</span>
                                <label><input type="checkbox" id="showETD"> ETD</label>
                                <label><input type="checkbox" id="showETA"> ETA</label>
                            </div>
                            <div class="radio-row">
                                <label><input type="radio" name="period_mode" value="range" id="periodRange"></label>
                                <input type="date" id="asOfDate" value="{{ date('Y-m-d') }}" style="width:150px;height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;">
                                <label><input type="radio" name="period_mode" value="as_of_today" checked> As of Today</label>
                            </div>
                        </td>
                        <td class="flabel">Transaction Type</td>
                        <td class="finput" colspan="2">
                            <div class="check-row">
                                <label><input type="checkbox" id="transTypeAll" checked> All</label>
                                <label><input type="checkbox" class="trans-type-check" value="debit" checked> Debit</label>
                                <label><input type="checkbox" class="trans-type-check" value="credit" checked> Credit</label>
                                <label><input type="checkbox" class="trans-type-check" value="ar" checked> A/R</label>
                                <label><input type="checkbox" class="trans-type-check" value="ap" checked> A/P</label>
                                <label><input type="checkbox" class="trans-type-check" value="ga_ar" checked> G&A AR</label>
                                <label><input type="checkbox" class="trans-type-check" value="ga_ap" checked> G&A AP</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Invoice Office</td>
                        <td class="finput" colspan="2">
                            <select id="officeId" class="gf-select" style="width:220px;">
                                <option value="">Select...</option>
                                @foreach($offices as $office)<option value="{{ $office->id }}">{{ $office->name }}</option>@endforeach
                            </select>
                        </td>
                        <td class="flabel">Payment Status</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row">
                                <label><input type="radio" name="payment_status" value="all"> All</label>
                                <label><input type="radio" name="payment_status" value="open" checked> Open</label>
                                <label><input type="radio" name="payment_status" value="paid"> Paid</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Block / Unblock</td>
                        <td class="finput" colspan="2">
                            <div class="check-row">
                                <label><input type="checkbox" id="blockCheck" checked> Block</label>
                                <label><input type="checkbox" id="unblockCheck" checked> Unblock</label>
                            </div>
                        </td>
                        <td class="flabel">D/C Format</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row">
                                <label><input type="radio" name="dc_format" value="combined" checked> Combined</label>
                                <label><input type="radio" name="dc_format" value="separated"> Separated</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Display Currency</td>
                        <td class="finput" colspan="2">
                            <select id="displayCurrency" class="gf-select" style="width:220px;">
                                <option value="all">All</option>
                                @foreach($currencies as $cur)<option value="{{ $cur->code }}">{{ $cur->name }} ({{ $cur->code }})</option>@endforeach
                            </select>
                        </td>
                        <td class="flabel">Amount Confirmed</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row">
                                <label><input type="radio" name="amount_confirmed" value="all" checked> All</label>
                                <label><input type="radio" name="amount_confirmed" value="yes"> Yes</label>
                                <label><input type="radio" name="amount_confirmed" value="no"> No</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Freight Currency</td>
                        <td class="finput" colspan="2">
                            <select id="freightCurrency" class="gf-select" style="width:220px;">
                                <option value="">Select...</option>
                                @foreach($currencies as $cur)<option value="{{ $cur->code }}">{{ $cur->name }} ({{ $cur->code }})</option>@endforeach
                            </select>
                        </td>
                        <td class="flabel">Sales Person</td>
                        <td class="finput" colspan="2">
                            <select id="salesPersonId" class="gf-select" style="width:220px;">
                                <option value="">All</option>
                                @foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">More Options</td>
                        <td class="finput" colspan="5">
                            <div class="check-row" style="flex-wrap:wrap;">
                                <label><input type="checkbox" id="groupDepartment" checked> Group by Department</label>
                                <label><input type="checkbox" id="showPayment" checked> Show payment detail</label>
                                <label><input type="checkbox" id="showAging" checked> Show Aging Information</label>
                                <label><input type="checkbox" id="hideOverpaid"> Do Not Show Overpaid Invoice(s)</label>
                                <label><input type="checkbox" id="invoiceLocalReceived"> Invoice(Local A/R) Received Only</label>
                                <label><input type="checkbox" id="showCreditLimit" checked> Show Credit Limit</label>
                            </div>
                        </td>
                    </tr>
                </table>
                <div style="text-align:center;margin-top:12px;padding-top:10px;border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn-search-gf" id="btnSearch"><i class="fa fa-search"></i> Search</button>
                </div>
            </div>
        </div>

        <div id="resultsSection" style="display:none;">
            <div class="table-toolbar">
                <div class="left">
                    <span style="font-size:11px;font-weight:600;color:#334155;">Show balanced:</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="showBalancedToggle" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span id="showBalancedLabel" style="font-size:11px;font-weight:600;color:#334155;">ON</span>
                </div>
                <div class="right">
                    <input type="text" class="input-search" id="quickSearch" placeholder="Quick search...">
                    <button class="btn-action-round" type="button" id="btnQuickSearch"><i class="fa fa-search"></i></button>
                    <div style="position:relative;display:inline-flex;align-items:center;">
                        <button class="btn-action-round secondary" type="button" id="btnConfig"><i class="fa fa-cogs"></i> Config</button>
                        <div class="config-panel" id="config-panel">
                            <div class="config-panel-title">Column Visibility</div>
                            <div id="col-toggles"></div>
                        </div>
                    </div>
                    <button class="btn-action-round secondary" type="button" id="btnPrint"><i class="fa fa-print"></i></button>
                    <button class="btn-action-round secondary" type="button" id="btnExport"><i class="fa fa-file-excel-o"></i></button>
                </div>
            </div>

            <div class="grid-container">
                <table class="grid-table" id="resultsTable">
                    <thead>
                        <tr>
                            <th style="width:25px;text-align:center;background:#374151;"><input type="checkbox" id="selectAll"></th>
                            <th data-col="office">Shipment Office <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="invoice_date">Post Date <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="invoice_no">Invoice No. <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="mbl_no">MB/L No. <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="hbl_no">HB/L No. <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="partner_name">Partner <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="pol">POL <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="pod">POD <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="etd" class="col-etd" style="display:none;">ETD <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="eta" class="col-eta" style="display:none;">ETA <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="currency">Cur. <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="dr_amount" class="col-dr">DR/AR (+) <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="cr_amount" class="col-cr" style="display:none;">CR/AP (-) <i class="fa fa-sort sort-icon"></i></th>
                            <th>Paid Amount</th>
                            <th>Balance</th>
                            <th data-col="last_paid_date">Last Paid Date <i class="fa fa-sort sort-icon"></i></th>
                            <th class="col-aging">Current</th>
                            <th class="col-aging">1-30 Days</th>
                            <th class="col-aging">31-60 Days</th>
                            <th class="col-aging">61-90 Days</th>
                            <th class="col-aging">90+ Days</th>
                            <th data-col="type">Type <i class="fa fa-sort sort-icon"></i></th>
                            <th data-col="sales_person">Sales Person <i class="fa fa-sort sort-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody"></tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td></td>
                            <td colspan="5" style="text-align:right;font-weight:700;">Total</td>
                            <td colspan="3"></td>
                            <td class="col-etd" style="display:none;"></td>
                            <td class="col-eta" style="display:none;"></td>
                            <td></td>
                            <td id="totalDR" class="col-dr num" style="text-align:right;font-weight:700;">0.00</td>
                            <td id="totalCR" class="col-cr num" style="text-align:right;font-weight:700;display:none;">0.00</td>
                            <td id="totalPaid" class="num" style="text-align:right;font-weight:700;">0.00</td>
                            <td id="totalBalance" class="num" style="text-align:right;font-weight:700;">0.00</td>
                            <td></td>
                            <td id="totalCurrent" class="col-aging num" style="text-align:right;font-weight:700;">0.00</td>
                            <td id="totalOver1_30" class="col-aging num" style="text-align:right;font-weight:700;">0.00</td>
                            <td id="totalOver31_60" class="col-aging num" style="text-align:right;font-weight:700;">0.00</td>
                            <td id="totalOver61_90" class="col-aging num" style="text-align:right;font-weight:700;">0.00</td>
                            <td id="totalOver90" class="col-aging num" style="text-align:right;font-weight:700;">0.00</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:8px;gap:12px;align-items:flex-start;">
                <span style="font-size:11px;font-weight:700;color:#334155;line-height:24px;">Selected Total</span>
                <table class="grid-table" style="width:auto;min-width:300px;">
                    <thead>
                        <tr><th>Currency</th><th>Amount</th><th>Paid Amount</th><th>Balance</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td id="selTotalCur"></td>
                            <td id="selTotalAmount" class="num" style="text-align:right;">0.00</td>
                            <td id="selTotalPaid" class="num" style="text-align:right;">0.00</td>
                            <td id="selTotalBalance" class="num" style="text-align:right;">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px;">
                <div class="doc-list-header">
                    <h6>GENERATED DOCUMENT LIST</h6>
                    <button class="btn-action-round" type="button" id="btnFilterGenerated"><i class="fa fa-filter"></i> Filter</button>
                </div>
                <div class="grid-container" style="border:1px solid #e2e8f0;border-top:none;">
                    <table class="grid-table" id="generatedTable">
                        <thead>
                            <tr>
                                <th style="width:25px;text-align:center;background:#374151;"><input type="checkbox" id="selectAllGenerated"></th>
                                <th>Document ID</th>
                                <th>Operation</th>
                                <th>Partner</th>
                                <th>Data Type</th>
                                <th>Period</th>
                                <th>Invoice Office</th>
                                <th>Transaction Type</th>
                                <th>Payment Status</th>
                                <th>Invoice Count</th>
                                <th>Total Amount</th>
                                <th>Balance</th>
                                <th>Amount Confirmed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="genFilterRow" style="display:none;">
                            <tr style="background:#f1f5f9;">
                                <td style="text-align:center;"><button class="btn-action-round" style="padding:0 6px;height:18px;font-size:9px;" onclick="applyGenFilters()"><i class="fa fa-search"></i></button></td>
                                <td><input class="gen-filter-input" data-col="1" placeholder="Doc ID..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td><input class="gen-filter-input" data-col="2" placeholder="Operation..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td><input class="gen-filter-input" data-col="3" placeholder="Partner..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td><input class="gen-filter-input" data-col="4" placeholder="Type..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td><input class="gen-filter-input" data-col="5" placeholder="Period..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td><input class="gen-filter-input" data-col="6" placeholder="Office..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td><input class="gen-filter-input" data-col="7" placeholder="Trans Type..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td><input class="gen-filter-input" data-col="8" placeholder="Status..." style="width:100%;border:1px solid #c2cad8;border-radius:2px;font-size:10px;padding:1px 3px;height:20px;" oninput="applyGenFilters()"></td>
                                <td></td><td></td><td></td><td></td><td></td>
                            </tr>
                        </tbody>
                        <tbody id="generatedBody"></tbody>
                    </table>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:0 0 2px 2px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <ul class="pagination-sm" id="generatedPagination">
                            <li class="disabled"><span>&laquo;</span></li>
                            <li class="disabled"><span>&lsaquo;</span></li>
                            <li class="active"><span>1</span></li>
                            <li class="disabled"><span>&rsaquo;</span></li>
                            <li class="disabled"><span>&raquo;</span></li>
                        </ul>
                        <select id="generatedPerPage" style="height:22px;font-size:11px;width:50px;border:1px solid #c2cad8;border-radius:2px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span style="font-size:11px;color:#64748b;">records</span>
                    </div>
                    <div style="font-size:11px;color:#64748b;" id="generatedInfo">Showing 0 to 0 of 0 records</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var allResults = [];
        var allGenerated = [];
        var sortCol = '';
        var sortAsc = true;
        var quickSearchTerm = '';
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var searchTimer = null;
        var currentOptions = {};

        var partnerTypeAC = document.getElementById('partnerTypeAC');
        var partnerTypeAG = document.getElementById('partnerTypeAG');
        var billToIdSel = document.getElementById('billToId');
        var accountGroupIdSel = document.getElementById('accountGroupId');

        function togglePartnerType() {
            if (partnerTypeAG.checked) {
                billToIdSel.style.display = 'none';
                billToIdSel.value = '';
                accountGroupIdSel.style.display = '';
            } else {
                accountGroupIdSel.style.display = 'none';
                accountGroupIdSel.value = '';
                billToIdSel.style.display = '';
            }
        }
        partnerTypeAC.addEventListener('change', togglePartnerType);
        partnerTypeAG.addEventListener('change', togglePartnerType);

        var transTypeAll = document.getElementById('transTypeAll');
        var transChecks = document.querySelectorAll('.trans-type-check');
        transTypeAll.addEventListener('change', function() {
            for (var i = 0; i < transChecks.length; i++) transChecks[i].checked = this.checked;
            triggerAutoSearch();
        });
        for (var i = 0; i < transChecks.length; i++) {
            transChecks[i].addEventListener('change', function() {
                var allChecked = true;
                for (var j = 0; j < transChecks.length; j++) { if (!transChecks[j].checked) { allChecked = false; break; } }
                transTypeAll.checked = allChecked;
                triggerAutoSearch();
            });
        }

        document.getElementById('showBalancedToggle').addEventListener('change', function() {
            document.getElementById('showBalancedLabel').textContent = this.checked ? 'ON' : 'OFF';
            triggerAutoSearch();
        });

        document.getElementById('periodRange').addEventListener('change', function() {
            document.getElementById('asOfDate').disabled = false;
            triggerAutoSearch();
        });
        var periodToday = document.querySelector('input[name="period_mode"][value="as_of_today"]');
        if (periodToday) {
            periodToday.addEventListener('change', function() {
                document.getElementById('asOfDate').disabled = true;
                document.getElementById('asOfDate').value = '{{ date("Y-m-d") }}';
                triggerAutoSearch();
            });
        }

        function triggerAutoSearch() {
            if (searchTimer) clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { document.getElementById('btnSearch').click(); }, 350);
        }

        var autoSearchEls = [billToIdSel, accountGroupIdSel, document.getElementById('displayCurrency'), document.getElementById('freightCurrency'), document.getElementById('officeId'), document.getElementById('salesPersonId'), document.getElementById('asOfDate')];
        for (var i = 0; i < autoSearchEls.length; i++) { if (autoSearchEls[i]) autoSearchEls[i].addEventListener('change', triggerAutoSearch); }
        var autoSearchRadios = document.querySelectorAll('input[name="payment_status"], input[name="amount_confirmed"], input[name="dc_format"], input[name="acct_mode"]');
        for (var i = 0; i < autoSearchRadios.length; i++) autoSearchRadios[i].addEventListener('change', triggerAutoSearch);
        var autoSearchChecks = [document.getElementById('showAging'), document.getElementById('showPayment'), document.getElementById('hideOverpaid'), document.getElementById('blockCheck'), document.getElementById('unblockCheck'), document.getElementById('groupDepartment'), document.getElementById('showCreditLimit'), document.getElementById('invoiceLocalReceived'), document.getElementById('showETD'), document.getElementById('showETA')];
        for (var i = 0; i < autoSearchChecks.length; i++) { if (autoSearchChecks[i]) autoSearchChecks[i].addEventListener('change', triggerAutoSearch); }

        function buildSearchFD() {
            var fd = new FormData();
            fd.append('as_of_date', document.getElementById('asOfDate').value);
            var pt = partnerTypeAG.checked ? 'account_group' : 'agent_customer';
            fd.append('partner_type', pt);
            if (pt === 'account_group') { var ag = accountGroupIdSel.value; if (ag) fd.append('account_group', ag); }
            else { var bt = billToIdSel.value; if (bt) fd.append('bill_to_id', bt); }
            fd.append('display_currency', document.getElementById('displayCurrency').value || 'all');
            var ps = document.querySelector('input[name="payment_status"]:checked');
            fd.append('payment_status', ps ? ps.value : 'all');
            var dc = document.querySelector('input[name="dc_format"]:checked');
            fd.append('dc_format', dc ? dc.value : 'combined');
            var am = document.querySelector('input[name="acct_mode"]:checked');
            fd.append('acct_mode', am ? am.value : 'invoice');
            fd.append('show_aging', document.getElementById('showAging').checked ? '1' : '0');
            fd.append('show_payment', document.getElementById('showPayment').checked ? '1' : '0');
            fd.append('hide_overpaid', document.getElementById('hideOverpaid').checked ? '1' : '0');
            fd.append('show_balanced', document.getElementById('showBalancedToggle').checked ? '1' : '0');
            var fc = document.getElementById('freightCurrency').value;
            if (fc) fd.append('freight_currency', fc);
            var ac = document.querySelector('input[name="amount_confirmed"]:checked');
            fd.append('amount_confirmed', ac ? ac.value : 'all');
            var sp = document.getElementById('salesPersonId').value;
            if (sp) fd.append('sales_person_id', sp);
            var oe = document.getElementById('officeId');
            if (oe.value) fd.append('office_id', oe.value);
            fd.append('block', document.getElementById('blockCheck').checked ? '1' : '0');
            fd.append('unblock', document.getElementById('unblockCheck').checked ? '1' : '0');
            fd.append('group_department', document.getElementById('groupDepartment').checked ? '1' : '0');
            fd.append('show_credit_limit', document.getElementById('showCreditLimit').checked ? '1' : '0');
            fd.append('invoice_local_received', document.getElementById('invoiceLocalReceived').checked ? '1' : '0');
            fd.append('show_etd', document.getElementById('showETD').checked ? '1' : '0');
            fd.append('show_eta', document.getElementById('showETA').checked ? '1' : '0');
            var st = [];
            for (var i = 0; i < transChecks.length; i++) { if (transChecks[i].checked) st.push(transChecks[i].value); }
            fd.append('trans_type', (st.length > 0 && !transTypeAll.checked) ? st.join(',') : 'all');
            return fd;
        }

        document.getElementById('btnSearch').addEventListener('click', function() {
            var fd = buildSearchFD();
            fetch('{{ route("accounting.report.agent-local-statement.view") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) { console.error('Search failed:', data); return; }
                allResults = data.results || [];
                allGenerated = data.generated_list || [];
                currentOptions = data.options || {};
                quickSearchTerm = '';
                document.getElementById('quickSearch').value = '';
                renderTable();
                renderGeneratedTable();
                applyColumnVisibility();
                document.getElementById('resultsSection').style.display = 'block';
            })
            .catch(function(err) { console.error('Search error:', err); });
        });

        document.getElementById('btnPrint').addEventListener('click', function() {
            var params = new URLSearchParams();
            params.set('as_of_date', document.getElementById('asOfDate').value);
            params.set('show_aging', document.getElementById('showAging').checked ? '1' : '0');
            window.open('{{ route("accounting.report.agent-local-statement.print") }}?' + params.toString(), '_blank');
        });

        document.getElementById('btnExport').addEventListener('click', function() {
            var params = new URLSearchParams();
            params.set('as_of_date', document.getElementById('asOfDate').value);
            params.set('show_aging', document.getElementById('showAging').checked ? '1' : '0');
            window.location = '{{ route("accounting.report.agent-local-statement.export-excel") }}?' + params.toString();
        });

        document.querySelectorAll('#resultsTable .sortable').forEach(function(th) {
            th.addEventListener('click', function() {
                var col = this.getAttribute('data-col');
                if (!col) return;
                if (sortCol === col) sortAsc = !sortAsc; else { sortCol = col; sortAsc = true; }
                renderTable();
            });
        });

        var qsTimer;
        var quickSearchInput = document.getElementById('quickSearch');
        quickSearchInput.addEventListener('input', function() {
            clearTimeout(qsTimer);
            var val = this.value;
            qsTimer = setTimeout(function() { quickSearchTerm = val.trim().toLowerCase(); renderTable(); }, 300);
        });
        quickSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); clearTimeout(qsTimer); quickSearchTerm = this.value.trim().toLowerCase(); renderTable(); }
        });

        document.getElementById('btnConfig').addEventListener('click', function(e) {
            e.stopPropagation();
            var panel = document.getElementById('config-panel');
            var isOpen = panel.style.display === 'block';
            panel.style.display = isOpen ? 'none' : 'block';
            if (!isOpen) buildConfigPanel();
        });
        document.addEventListener('click', function(e) {
            var panel = document.getElementById('config-panel');
            var btn = document.getElementById('btnConfig');
            if (panel && btn && panel.style && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) panel.style.display = 'none';
        });

        function buildConfigPanel() {
            var container = document.getElementById('col-toggles');
            container.innerHTML = '';
            var ths = document.querySelectorAll('#resultsTable thead th[data-col]');
            for (var i = 0; i < ths.length; i++) {
                var th = ths[i];
                var colName = th.getAttribute('data-col');
                if (!colName) continue;
                var label = document.createElement('label');
                var cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.checked = th.style.display !== 'none';
                (function(idx, el) {
                    cb.addEventListener('change', function() { toggleColumn(idx, this.checked); });
                })(i, th);
                label.appendChild(cb);
                label.appendChild(document.createTextNode(' ' + th.textContent.replace(/[▲▼]/g, '').trim()));
                container.appendChild(label);
            }
        }

        function toggleColumn(idx, show) {
            var ths = document.querySelectorAll('#resultsTable thead th');
            if (ths[idx]) ths[idx].style.display = show ? '' : 'none';
            var rows = document.querySelectorAll('#resultsTable tbody tr, #resultsTable tfoot tr');
            for (var r = 0; r < rows.length; r++) {
                var cells = rows[r].querySelectorAll('td');
                if (cells[idx]) cells[idx].style.display = show ? '' : 'none';
            }
        }

        function applyColumnVisibility() {
            var showAging = currentOptions.show_aging === '1';
            var showEtd = currentOptions.show_etd === '1';
            var showEta = currentOptions.show_eta === '1';
            var dcFormat = currentOptions.dc_format || 'combined';

            document.querySelectorAll('.col-aging').forEach(function(el) { el.style.display = showAging ? '' : 'none'; });
            document.querySelectorAll('.col-etd').forEach(function(el) { el.style.display = showEtd ? '' : 'none'; });
            document.querySelectorAll('.col-eta').forEach(function(el) { el.style.display = showEta ? '' : 'none'; });
            document.querySelectorAll('.col-cr').forEach(function(el) { el.style.display = dcFormat === 'separated' ? '' : 'none'; });
        }

        function fmt(n) { return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
        function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.appendChild(document.createTextNode(String(s))); return d.innerHTML; }

        function renderTable() {
            var rows = allResults.slice();
            if (quickSearchTerm) {
                rows = rows.filter(function(r) {
                    return [r.office, r.invoice_date, r.mbl_no, r.hbl_no, r.pol, r.pod, r.etd, r.eta, r.currency, r.dr_amount, r.cr_amount, r.paid_amount, r.balance, r.last_paid_date, r.invoice_no, r.partner_name, r.type, r.sales_person].join(' ').toLowerCase().indexOf(quickSearchTerm) !== -1;
                });
            }
            if (sortCol) {
                rows.sort(function(a, b) {
                    var va = a[sortCol] || '', vb = b[sortCol] || '';
                    if (typeof va === 'number' && typeof vb === 'number') return sortAsc ? va - vb : vb - va;
                    return sortAsc ? String(va).localeCompare(String(vb)) : String(vb).localeCompare(String(va));
                });
            }
            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                html += '<tr>';
                html += '<td style="text-align:center;"><input type="checkbox" class="row-select" value="' + i + '"></td>';
                html += '<td>' + esc(r.office) + '</td>';
                html += '<td>' + esc(r.invoice_date) + '</td>';
                html += '<td>' + esc(r.invoice_no) + '</td>';
                html += '<td>' + esc(r.mbl_no) + '</td>';
                html += '<td>' + esc(r.hbl_no) + '</td>';
                html += '<td>' + esc(r.partner_name) + '</td>';
                html += '<td>' + esc(r.pol) + '</td>';
                html += '<td>' + esc(r.pod) + '</td>';
                html += '<td class="col-etd" style="display:none;">' + esc(r.etd) + '</td>';
                html += '<td class="col-eta" style="display:none;">' + esc(r.eta) + '</td>';
                html += '<td>' + esc(r.currency) + '</td>';
                html += '<td class="num col-dr">' + (r.dr_amount ? fmt(r.dr_amount) : '') + '</td>';
                html += '<td class="num col-cr" style="display:none;">' + (r.cr_amount ? fmt(r.cr_amount) : '') + '</td>';
                html += '<td class="num">' + (r.paid_amount ? fmt(r.paid_amount) : '') + '</td>';
                html += '<td class="num">' + fmt(r.balance) + '</td>';
                html += '<td>' + esc(r.last_paid_date) + '</td>';
                html += '<td class="num col-aging">' + fmt(r.current) + '</td>';
                html += '<td class="num col-aging">' + fmt(r.over1_30) + '</td>';
                html += '<td class="num col-aging">' + fmt(r.over31_60) + '</td>';
                html += '<td class="num col-aging">' + fmt(r.over61_90) + '</td>';
                html += '<td class="num col-aging">' + fmt(r.over90) + '</td>';
                html += '<td>' + esc(r.type) + '</td>';
                html += '<td>' + esc(r.sales_person) + '</td>';
                html += '</tr>';
            }
            document.getElementById('resultsBody').innerHTML = html;

            var tDR=0, tCR=0, tP=0, tB=0, tC=0, t1=0, t2=0, t3=0, t4=0;
            for (var i = 0; i < rows.length; i++) { tDR+=rows[i].dr_amount||0; tCR+=rows[i].cr_amount||0; tP+=rows[i].paid_amount||0; tB+=rows[i].balance||0; tC+=rows[i].current||0; t1+=rows[i].over1_30||0; t2+=rows[i].over31_60||0; t3+=rows[i].over61_90||0; t4+=rows[i].over90||0; }
            document.getElementById('totalDR').textContent = fmt(tDR);
            document.getElementById('totalCR').textContent = fmt(tCR);
            document.getElementById('totalPaid').textContent = fmt(tP);
            document.getElementById('totalBalance').textContent = fmt(tB);
            document.getElementById('totalCurrent').textContent = fmt(tC);
            document.getElementById('totalOver1_30').textContent = fmt(t1);
            document.getElementById('totalOver31_60').textContent = fmt(t2);
            document.getElementById('totalOver61_90').textContent = fmt(t3);
            document.getElementById('totalOver90').textContent = fmt(t4);

            rebindSortListeners();
            updateSelectedTotal();
        }

        function rebindSortListeners() {
            document.querySelectorAll('#resultsTable .sortable').forEach(function(th) {
                th.onclick = function() {
                    var col = this.getAttribute('data-col');
                    if (!col) return;
                    if (sortCol === col) sortAsc = !sortAsc; else { sortCol = col; sortAsc = true; }
                    renderTable();
                };
            });
        }

        function updateSelectedTotal() {
            var sel = document.querySelectorAll('.row-select:checked');
            var tA=0, tP=0, tB=0, cur='';
            sel.forEach(function(cb) {
                var r = allResults[parseInt(cb.value)];
                if (r) { tA+=r.dr_amount||r.cr_amount||0; tP+=r.paid_amount||0; tB+=r.balance||0; if (!cur) cur=r.currency; }
            });
            document.getElementById('selTotalCur').textContent = cur;
            document.getElementById('selTotalAmount').textContent = fmt(tA);
            document.getElementById('selTotalPaid').textContent = fmt(tP);
            document.getElementById('selTotalBalance').textContent = fmt(tB);
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('row-select')) updateSelectedTotal();
        });

        function renderGeneratedTable() {
            var html = '';
            for (var i = 0; i < allGenerated.length; i++) {
                var g = allGenerated[i];
                html += '<tr>';
                html += '<td style="text-align:center;"><input type="checkbox" class="gen-select" value="' + i + '"></td>';
                html += '<td>' + esc(g.document_id || '') + '</td>';
                html += '<td>' + esc(g.operation) + '</td>';
                html += '<td>' + esc(g.partner) + '</td>';
                html += '<td>' + esc(g.data_type) + '</td>';
                html += '<td>' + esc(g.period) + '</td>';
                html += '<td>' + esc(g.invoice_office) + '</td>';
                html += '<td>' + esc(g.transaction_type) + '</td>';
                html += '<td>' + esc(g.payment_status) + '</td>';
                html += '<td style="text-align:center;">' + g.invoice_count + '</td>';
                html += '<td class="num">' + esc(g.total_amount_str) + '</td>';
                html += '<td class="num">' + esc(g.balance_str) + '</td>';
                html += '<td style="text-align:center;"><input type="checkbox" class="amt-confirmed-cb" data-idx="' + i + '"' + (g.amount_confirmed ? ' checked' : '') + '></td>';
                html += '<td><div class="action-dropdown"><button class="btn-action-sm gen-action-btn" data-idx="' + i + '">Action &#9662;</button>';
                html += '<div class="action-dropdown-menu" id="actionMenu_' + i + '">';
                html += '<a href="#" class="gen-download-pdf" data-idx="' + i + '"><i class="fa fa-file-pdf-o"></i> Download PDF</a>';
                html += '<button class="gen-reload-report" data-idx="' + i + '"><i class="fa fa-refresh"></i> Reload Report</button>';
                html += '</div></div></td>';
                html += '</tr>';
            }
            document.getElementById('generatedBody').innerHTML = html;
            var len = allGenerated.length;
            document.getElementById('generatedInfo').textContent = 'Showing ' + (len > 0 ? 1 : 0) + ' to ' + len + ' of ' + len + ' records';

            document.querySelectorAll('.gen-action-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var idx = this.getAttribute('data-idx');
                    document.querySelectorAll('.action-dropdown-menu').forEach(function(m) { m.classList.remove('show'); });
                    document.getElementById('actionMenu_' + idx).classList.toggle('show');
                });
            });

            document.querySelectorAll('.gen-download-pdf').forEach(function(a) {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    var idx = parseInt(this.getAttribute('data-idx'));
                    var g = allGenerated[idx];
                    if (!g) return;
                    var params = new URLSearchParams();
                    params.set('as_of_date', currentOptions.as_of_date || document.getElementById('asOfDate').value);
                    params.set('filter_partner', g.partner || '');
                    params.set('filter_office', g.invoice_office || '');
                    window.open('{{ route("accounting.report.agent-local-statement.print") }}?' + params.toString(), '_blank');
                    document.querySelectorAll('.action-dropdown-menu').forEach(function(m) { m.classList.remove('show'); });
                });
            });

            document.querySelectorAll('.gen-reload-report').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var idx = parseInt(this.getAttribute('data-idx'));
                    var g = allGenerated[idx];
                    if (!g) return;
                    document.querySelectorAll('.action-dropdown-menu').forEach(function(m) { m.classList.remove('show'); });
                    var fd = buildSearchFD();
                    fd.set('filter_partner', g.partner || '');
                    fd.set('filter_office', g.invoice_office || '');
                    fetch('{{ route("accounting.report.agent-local-statement.view") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!data.success) { console.error('Reload failed:', data); return; }
                        allResults = data.results || [];
                        allGenerated = data.generated_list || [];
                        currentOptions = data.options || {};
                        quickSearchTerm = '';
                        document.getElementById('quickSearch').value = '';
                        renderTable();
                        renderGeneratedTable();
                        applyColumnVisibility();
                        document.getElementById('resultsSection').style.display = 'block';
                    })
                    .catch(function(err) { console.error('Reload error:', err); });
                });
            });

            document.querySelectorAll('.amt-confirmed-cb').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var idx = parseInt(this.getAttribute('data-idx'));
                    var g = allGenerated[idx];
                    if (!g) return;
                    g.amount_confirmed = this.checked;
                    var status = this.checked ? 'CONFIRMED' : 'UNCONFIRMED';
                    alert('Amount Confirmed for ' + g.partner + ' (' + g.invoice_office + '): ' + status + '\nTransaction Type: ' + g.transaction_type + '\nTotal Amount: ' + g.total_amount_str + '\nBalance: ' + g.balance_str);
                });
            });

            document.addEventListener('click', function() {
                document.querySelectorAll('.action-dropdown-menu').forEach(function(m) { m.classList.remove('show'); });
            });
        }

        document.getElementById('btnFilterGenerated').addEventListener('click', function() {
            var row = document.getElementById('genFilterRow');
            row.style.display = row.style.display === 'none' ? '' : 'none';
        });

        window.applyGenFilters = function() {
            var inputs = document.querySelectorAll('.gen-filter-input');
            var filters = {};
            for (var i = 0; i < inputs.length; i++) {
                var val = inputs[i].value.trim().toLowerCase();
                var col = parseInt(inputs[i].getAttribute('data-col'));
                if (val) filters[col] = val;
            }
            var tbody = document.getElementById('generatedBody');
            var rows = tbody.querySelectorAll('tr');
            var visible = 0;
            for (var r = 0; r < rows.length; r++) {
                var cells = rows[r].querySelectorAll('td');
                var show = true;
                for (var c in filters) {
                    var cellText = (cells[parseInt(c)] ? cells[parseInt(c)].textContent : '').toLowerCase();
                    if (cellText.indexOf(filters[c]) === -1) { show = false; break; }
                }
                rows[r].style.display = show ? '' : 'none';
                if (show) visible++;
            }
            document.getElementById('generatedInfo').textContent = 'Showing ' + visible + ' of ' + allGenerated.length + ' records';
        };

        document.getElementById('selectAll').addEventListener('change', function() {
            var cbs = document.querySelectorAll('.row-select');
            for (var i = 0; i < cbs.length; i++) cbs[i].checked = this.checked;
            updateSelectedTotal();
        });
        document.getElementById('selectAllGenerated').addEventListener('change', function() {
            var cbs = document.querySelectorAll('.gen-select');
            for (var i = 0; i < cbs.length; i++) cbs[i].checked = this.checked;
        });
    });
    </script>
</x-layout>
