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
        .btn-action-round{background:#3b82f6;color:#fff;border:1px solid #2563eb;border-radius:2px;padding:0 12px;height:26px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;gap:4px;cursor:pointer;text-decoration:none;transition:all .15s;box-sizing:border-box}
        .btn-action-round:hover{background:#2563eb;color:#fff}
        .btn-action-round.secondary{background:#64748b;border-color:#475569}
        .btn-action-round.secondary:hover{background:#475569}
        .empty-state{text-align:center;padding:40px 20px;color:#94a3b8}
        .empty-state i{font-size:36px;display:block;margin-bottom:12px}
        .loading-overlay{text-align:center;padding:30px;color:#64748b}
        .loading-overlay i{font-size:20px;animation:spin 1s linear infinite}
        @keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
        .toast-container{position:fixed;top:56px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:6px;pointer-events:none}
        .toast{background:#1e293b;color:#fff;padding:8px 14px;border-radius:4px;font-size:11px;box-shadow:0 4px 16px rgba(0,0,0,.25);display:flex;align-items:center;gap:8px;animation:toastIn .25s ease;pointer-events:all}
        .toast.success{border-left:3px solid #22c55e}
        .toast.error{border-left:3px solid #ef4444}
        .toast.info{border-left:3px solid #3b82f6}
        @keyframes toastIn{from{transform:translateX(40px);opacity:0}to{transform:translateX(0);opacity:1}}
        .balance-indicator{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:4px;font-size:11px;font-weight:700}
        .balance-indicator.balanced{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}

        .form-table{width:100%;border-collapse:collapse}
        .form-table td{padding:3px 4px;vertical-align:middle;font-size:11px;border:none}
        .form-table .flabel{background:#eef1f5;width:145px;padding:4px 8px;font-weight:600;color:#333;white-space:nowrap}
        .form-table .finput{padding-left:8px}
        .form-table input[type="radio"],.form-table input[type="checkbox"]{accent-color:#3b82f6;margin-right:3px}
        .form-table label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap}
        .form-table select{height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;color:#333;background:#fff;max-width:160px}
        .form-table input[type="date"]{height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;color:#333;background:#fff}
        .radio-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
        .check-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
        .section-header{font-size:11px;font-weight:600;color:#475569;border-top:1px solid #e2e8f0;padding-top:6px;margin-top:4px}
        .btn-row{text-align:center;margin-top:14px;padding-top:10px;border-top:1px solid #e2e8f0}
        .btn-row button{margin:0 4px}
        .grid-container{width:100%;overflow-x:auto;background:#fff;margin-top:10px}
        .grid-table{border-collapse:collapse;width:100%;font-size:11px}
        .grid-table th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:4px 6px;white-space:nowrap;text-align:left;font-size:10px}
        .grid-table td{padding:3px 6px;border:1px solid #e2e8f0;white-space:nowrap;color:#334155}
        .grid-table td.num{text-align:right;font-family:'Courier New',monospace}
        .grid-table tbody tr:hover{background:#f1f5f9}
        .grid-table .total-row{background:#f8fafc!important;font-weight:700}
        .grid-table .total-row td{border-top:2px solid #475569;color:#0f172a}
        .aging-current{color:#16a34a}.aging-1-30{color:#2563eb}.aging-31-60{color:#d97706}.aging-61-90{color:#ea580c}.aging-90{color:#dc2626;font-weight:700}
        .summary-row{display:flex;gap:12px;margin-top:15px;flex-wrap:wrap}
        .summary-card{flex:1;min-width:140px;background:#fff;border:1px solid #e2e8f0;border-radius:4px;padding:10px 14px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .summary-card .label{font-size:10px;color:#64748b;text-transform:uppercase;font-weight:600;margin-bottom:4px}
        .summary-card .value{font-size:14px;font-weight:700;color:#0f172a;font-family:'Courier New',monospace}
        .summary-card.current{border-left:3px solid #22c55e}
        .summary-card.over30{border-left:3px solid #2563eb}
        .summary-card.over60{border-left:3px solid #d97706}
        .summary-card.over90{border-left:3px solid #dc2626}
        .summary-card.total{border-left:3px solid #8b5cf6}
        .info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;padding:12px 16px;margin-top:15px;font-size:11px;color:#1e40af;line-height:1.6}
        .info-box strong{color:#1e3a8a}
        .table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:8px;padding:6px 8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:3px}
        .table-toolbar .left{display:flex;align-items:center;gap:8px}
        .table-toolbar .right{display:flex;align-items:center;gap:6px}
        .input-search{height:22px;border:1px solid #c2cad8;padding:1px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff;width:160px}
        .input-search:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 1px rgba(59,130,246,.2)}
        .config-panel{position:absolute;right:0;top:24px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;box-shadow:0 6px 20px rgba(0,0,0,.12);z-index:200;min-width:200px;max-height:320px;overflow-y:auto;padding:8px;font-size:10px;text-align:left}
        .config-panel-title{font-weight:700;color:#475569;margin-bottom:6px;font-size:10px;text-transform:uppercase;letter-spacing:.5px;padding-bottom:4px;border-bottom:1px solid #e2e8f0}
        .config-panel label{display:flex;align-items:center;gap:6px;padding:3px 4px;cursor:pointer;border-radius:2px;color:#334155}
        .config-panel label:hover{background:#f1f5f9}
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Report</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Aging Report</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-clock-o" style="color:#3b82f6;margin-right:6px;"></i>Aging Report</span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <label style="font-size:11px;color:#334155;display:flex;align-items:center;gap:4px;cursor:pointer;">
                        <input type="checkbox" style="accent-color:#3b82f6;"> Send weekly E-mails automatically
                    </label>
                    <div id="balance-badge" style="display:none;"></div>
                </div>
            </div>
            <div class="portlet-body">
                <table class="form-table">
                    <tr>
                        <td class="flabel"><span class="text-danger">*</span>Aging Report Type</td>
                        <td class="finput" colspan="3">
                            <div class="check-row">
                                <label><input type="checkbox" id="type_ar" checked> A/R</label>
                                <label><input type="checkbox" id="type_ap" checked> A/P</label>
                                <label><input type="checkbox" id="type_dc"> D/C</label>
                            </div>
                        </td>
                        <td class="flabel">Payment and Journal Data</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="payment_data" value="include" checked> Include</label>
                                <label><input type="radio" name="payment_data" value="exclude"> Exclude</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Trade Partner Type</td>
                        <td class="finput" colspan="5">
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                <div class="radio-row">
                                    <label><input type="radio" name="tp_type" value="all" checked> All</label>
                                    <label><input type="radio" name="tp_type" value="oversea_agent"> Oversea Agent</label>
                                    <label><input type="radio" name="tp_type" value="exclude_oversea_agent"> Exclude Oversea Agent</label>
                                </div>
                                <div class="radio-row">
                                    <label><input type="radio" name="tp_type" value="customer"> Customer</label>
                                    <label><input type="radio" name="tp_type" value="ocean_carrier"> Ocean Carrier</label>
                                    <label><input type="radio" name="tp_type" value="air_carrier"> Air Carrier</label>
                                </div>
                                <div class="radio-row">
                                    <label><input type="radio" name="tp_type" value="account_group"> Account Group</label>
                                    <select id="account_group" style="width:140px;" disabled>
                                        <option value="">Select...</option>
                                        @foreach($accountGroups as $group)
                                            <option value="{{ $group }}">{{ $group }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel"><span class="text-danger">*</span>Ending Date</td>
                        <td class="finput">
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                <div class="radio-row">
                                    <label><input type="radio" name="ending_date_type" value="post_date"> Post Date</label>
                                    <label><input type="radio" name="ending_date_type" value="invoice_date" checked> Invoice Date</label>
                                    <label><input type="radio" name="ending_date_type" value="etd"> ETD</label>
                                    <label><input type="radio" name="ending_date_type" value="eta"> ETA</label>
                                </div>
                                <div class="radio-row">
                                    <select id="ending_date_mode" style="width:85px;">
                                        <option value="as_of">As of date</option>
                                    </select>
                                    <input type="date" id="as_of_date" value="{{ date('Y-m-d') }}">
                                    <label><input type="checkbox" id="include_prepaid" style="accent-color:#3b82f6;"> Include Prepaid</label>
                                </div>
                            </div>
                        </td>
                        <td class="flabel">Sales Person (Invoice)</td>
                        <td class="finput">
                            <select id="sales_person" style="width:140px;">
                                <option value="">All</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Last Paid Date</td>
                        <td class="finput">
                            <div class="radio-row">
                                <input type="date" id="last_paid_date">
                                <label><input type="checkbox" id="last_paid_as_of" style="accent-color:#3b82f6;"> As of Date</label>
                            </div>
                        </td>
                        <td class="flabel">OP</td>
                        <td class="finput">
                            <select id="op_person" style="width:140px;">
                                <option value="">All</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Office</td>
                        <td class="finput">
                            <select id="office_id">
                                <option value="">Select...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="flabel">Group by</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="group_by" value="trade_partner" checked> Trade Partner</label>
                                <label><input type="radio" name="group_by" value="sales_invoice"> Sales (Invoice)</label>
                                <label><input type="radio" name="group_by" value="account_group"> Account Group</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">View Option</td>
                        <td class="finput" colspan="3">
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                <div class="check-row">
                                    <label><input type="checkbox" id="opt_cargo_released"> Cargo Released Only</label>
                                    <label><input type="checkbox" id="opt_hide_overpaid"> Hide Overpaid Items</label>
                                    <label><input type="checkbox" id="opt_hide_zero_bal"> Hide 0 balance for Each Currency</label>
                                    <label><input type="checkbox" id="opt_hide_zero_overdue"> Hide 0 overdue for Each Currency</label>
                                </div>
                                <div class="check-row">
                                    <label><input type="checkbox" id="opt_hide_negative"> Hide Negative</label>
                                    <label><input type="checkbox" id="opt_filter_credit"> Filter by Credit Only</label>
                                </div>
                            </div>
                        </td>
                        <td class="flabel">Sort by</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="sort_by" value="due_date" checked> Due Date</label>
                                <label><input type="radio" name="sort_by" value="etd"> ETD</label>
                                <label><input type="radio" name="sort_by" value="eta"> ETA</label>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="section-header" style="margin-top:10px;">Options for printing and emailing only</div>
                <table class="form-table">
                    <tr>
                        <td class="flabel">Report Type</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="report_type" value="summary" checked> Summary</label>
                                <label><input type="radio" name="report_type" value="detail"> Detail</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">View Option</td>
                        <td class="finput">
                            <div class="check-row">
                                <label><input type="checkbox" id="opt_show_alias"> Show Trade Partner Alias</label>
                                <label><input type="checkbox" id="opt_show_name_print"> Show Trade Partner Name instead of Print Name</label>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="btn-row">
                    <button class="btn-action-round" onclick="viewReport()"><i class="fa fa-search"></i> Search</button>
                    <button class="btn-action-round secondary" onclick="printReport()"><i class="fa fa-print"></i> Print</button>
                    <button class="btn-action-round secondary" onclick="exportExcel()"><i class="fa fa-envelope"></i> Batch Email</button>
                </div>

                <div class="table-toolbar" id="table-toolbar" style="display:none;">
                    <div class="left">
                        <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                        <input type="text" id="quick-search" class="input-search" placeholder="Quick search..." oninput="quickSearch(this.value)">
                    </div>
                    <div class="right">
                        <div style="position:relative;display:inline-flex;align-items:center;">
                            <button class="btn-action-round secondary" id="btn-config" onclick="toggleConfig()" title="Column visibility">
                                <i class="fa fa-cogs"></i> Config
                            </button>
                            <div class="config-panel" id="config-panel" style="display:none;">
                                <div class="config-panel-title">Column Visibility</div>
                                <div id="col-toggles"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="report-results">
                    <div class="empty-state" id="empty-state">
                        <i class="fa fa-clock-o"></i>
                        <div>Select report options and click <strong>Search</strong> to generate the Aging Report.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function showToast(type,msg){var icons={success:'check-circle',error:'times-circle',info:'info-circle'};var t=document.createElement('div');t.className='toast '+type;t.innerHTML='<i class="fa fa-'+(icons[type]||'info-circle')+'"></i> '+msg;document.getElementById('toast-container').appendChild(t);setTimeout(function(){t.remove()},3000)}
    function fmt(val){if(val===undefined||val===null)return'0.00';return parseFloat(val).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}
    function escapeHtml(text){if(!text)return'';var div=document.createElement('div');div.textContent=text;return div.innerHTML}
    function getAgingType(){var ar=document.getElementById('type_ar').checked;var ap=document.getElementById('type_ap').checked;var dc=document.getElementById('type_dc').checked;var t=[];if(ar)t.push('ar');if(ap)t.push('ap');if(dc)t.push('dc');return t.length>0?t.join(','):'ar,ap'}
    function getRadioVal(name){var el=document.querySelector('input[name="'+name+'"]:checked');return el?el.value:''}
    document.querySelectorAll('input[name="tp_type"]').forEach(function(r){r.addEventListener('change',function(){document.getElementById('account_group').disabled=this.value!=='account_group'})});

    var _lastReportData=null;

    function viewReport(){
        var asOfDate=document.getElementById('as_of_date').value;
        var officeId=document.getElementById('office_id').value;
        if(!asOfDate){showToast('error','Please select an As of Date.');return}
        var resultsDiv=document.getElementById('report-results');
        resultsDiv.innerHTML='<div class="loading-overlay"><i class="fa fa-spinner"></i><div style="margin-top:8px;">Generating Aging Report...</div></div>';
        var fd=new FormData();
        fd.append('as_of_date',asOfDate);fd.append('office_id',officeId);
        fd.append('report_type',getRadioVal('report_type'));fd.append('aging_type',getAgingType());
        fd.append('trade_partner_type',getRadioVal('tp_type'));fd.append('group_by',getRadioVal('group_by'));
        fd.append('sort_by',getRadioVal('sort_by'));fd.append('ending_date_type',getRadioVal('ending_date_type'));
        fd.append('payment_data',getRadioVal('payment_data'));
        fd.append('include_prepaid',document.getElementById('include_prepaid').checked?'1':'0');
        fd.append('opt_hide_overpaid',document.getElementById('opt_hide_overpaid').checked?'1':'0');
        fd.append('opt_hide_zero_bal',document.getElementById('opt_hide_zero_bal').checked?'1':'0');
        fd.append('opt_hide_negative',document.getElementById('opt_hide_negative').checked?'1':'0');
        fd.append('opt_filter_credit',document.getElementById('opt_filter_credit').checked?'1':'0');
        fetch('{{route("accounting.report.aging-report.view")}}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'{{csrf_token()}}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd})
        .then(function(r){return r.json()}).then(function(data){if(!data.success){resultsDiv.innerHTML='<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>Failed to generate Aging Report.</div></div>';showToast('error',data.message||'Failed');return}_lastReportData=data;renderReport(data)})
        .catch(function(e){console.error(e);resultsDiv.innerHTML='<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>An error occurred.</div></div>';showToast('error','Failed')});
    }

    function renderReport(data){
        var results=data.results||[];var summary=data.summary||{};var rd=document.getElementById('report-results');
        var b=document.getElementById('balance-badge');b.innerHTML='<span class="balance-indicator balanced"><i class="fa fa-check-circle"></i> '+(summary.partner_count||0)+' Partners</span>';b.style.display='inline-block';
        document.getElementById('table-toolbar').style.display='flex';
        if(!results.length){rd.innerHTML='<div class="empty-state"><i class="fa fa-inbox"></i><div>No outstanding balances found.</div></div>';return}
        var cols=['name','sales_tp','last_follow_up','sales_invoice','terms','cur','avg_aging','current_bal','current_co','past_due_bal','past_due_cnt','over_1_30','over_31_60','over_61_90','over_90','balance_amt','credit_limit'];
        var colLabels=['Name','Sales (TP)','Last Follow Up','Sales (Invoice)','Terms','Cur.','Average Aging','Current Balance','Current Co...','Past Due Bala...','Past Due Count','Over 1-30...','Over 31-60 Da...','Over 61-90 Da...','Over 90 Days','Balance A...','Credit Limit'];
        var h='<div class="grid-container"><table class="grid-table" id="aging-grid"><thead><tr id="header-row">';
        for(var ci=0;ci<cols.length;ci++){
            h+='<th data-col="'+cols[ci]+'">'+colLabels[ci]+'</th>';
        }
        h+='</tr></thead><tbody id="grid-body">';
        results.forEach(function(row){
            var pastDue=row.over1_30+row.over31_60+row.over61_90+row.over90;
            var pastDueCount=pastDue>0?Math.ceil(pastDue/row.total*30):0;
            h+='<tr>';
            h+='<td data-col="name">'+escapeHtml(row.partner_name)+'</td>';
            h+='<td data-col="sales_tp"></td>';
            h+='<td data-col="last_follow_up"></td>';
            h+='<td data-col="sales_invoice">'+escapeHtml(row.partner_name)+'</td>';
            h+='<td data-col="terms"></td>';
            h+='<td data-col="cur">'+escapeHtml(row.currency)+'</td>';
            h+='<td data-col="avg_aging" class="num">'+fmt(row.total>0?row.total/30:0)+'</td>';
            h+='<td data-col="current_bal" class="num aging-current">'+fmt(row.current)+'</td>';
            h+='<td data-col="current_co" class="num aging-current">'+fmt(row.current)+'</td>';
            h+='<td data-col="past_due_bal" class="num aging-1-30">'+fmt(pastDue)+'</td>';
            h+='<td data-col="past_due_cnt" class="num">'+pastDueCount+'</td>';
            h+='<td data-col="over_1_30" class="num aging-1-30">'+fmt(row.over1_30)+'</td>';
            h+='<td data-col="over_31_60" class="num aging-31-60">'+fmt(row.over31_60)+'</td>';
            h+='<td data-col="over_61_90" class="num aging-61-90">'+fmt(row.over61_90)+'</td>';
            h+='<td data-col="over_90" class="num aging-90">'+fmt(row.over90)+'</td>';
            h+='<td data-col="balance_amt" class="num">'+fmt(row.total)+'</td>';
            h+='<td data-col="credit_limit" class="num"></td>';
            h+='</tr>';
        });
        h+='<tr class="total-row">';
        h+='<td data-col="name">TOTAL</td><td data-col="sales_tp"></td><td data-col="last_follow_up"></td><td data-col="sales_invoice"></td><td data-col="terms"></td><td data-col="cur"></td><td data-col="avg_aging"></td>';
        h+='<td data-col="current_bal" class="num aging-current">'+fmt(summary.total_current||0)+'</td>';
        h+='<td data-col="current_co" class="num aging-current">'+fmt(summary.total_current||0)+'</td>';
        h+='<td data-col="past_due_bal" class="num aging-1-30">'+fmt((summary.total_over1_30||0)+(summary.total_over31_60||0)+(summary.total_over61_90||0)+(summary.total_over90||0))+'</td>';
        h+='<td data-col="past_due_cnt" class="num"></td>';
        h+='<td data-col="over_1_30" class="num aging-1-30">'+fmt(summary.total_over1_30||0)+'</td>';
        h+='<td data-col="over_31_60" class="num aging-31-60">'+fmt(summary.total_over31_60||0)+'</td>';
        h+='<td data-col="over_61_90" class="num aging-61-90">'+fmt(summary.total_over61_90||0)+'</td>';
        h+='<td data-col="over_90" class="num aging-90">'+fmt(summary.total_over90||0)+'</td>';
        h+='<td data-col="balance_amt" class="num">'+fmt(summary.grand_total||0)+'</td>';
        h+='<td data-col="credit_limit" class="num"></td>';
        h+='</tr>';
        h+='</tbody></table></div>';
        h+='<div style="margin-top:8px;font-size:10px;color:#64748b;text-align:right;">As of '+escapeHtml(data.as_of_date)+' &middot; Generated '+new Date().toLocaleString()+'</div>';
        rd.innerHTML=h;
        buildConfigPanel();
        showToast('success','Aging Report generated successfully');
    }

    var searchTimer;
    function quickSearch(val){
        clearTimeout(searchTimer);
        searchTimer=setTimeout(function(){
            var q=val.trim().toLowerCase();
            var rows=document.querySelectorAll('#grid-body tr');
            var totalVisible=0;
            rows.forEach(function(tr){
                if(tr.classList.contains('total-row')){tr.style.display='none';return}
                var text=tr.textContent.toLowerCase();
                var match=!q||text.indexOf(q)!==-1;
                tr.style.display=match?'':'none';
                if(match)totalVisible++;
            });
            var totalRow=document.querySelector('.total-row');
            if(totalRow)totalRow.style.display=q?'none':'';
        },300);
    }

    var PINNED_COLS=['name'];
    function toggleConfig(){
        var panel=document.getElementById('config-panel');
        var open=panel.style.display==='none';
        panel.style.display=open?'block':'none';
        document.getElementById('btn-config').classList.toggle('active',open);
        if(open)buildConfigPanel();
    }
    function buildConfigPanel(){
        var container=document.getElementById('col-toggles');
        container.innerHTML='';
        document.querySelectorAll('#header-row th[data-col]').forEach(function(th){
            if(PINNED_COLS.indexOf(th.dataset.col)!==-1)return;
            var label=document.createElement('label');
            var cb=document.createElement('input');
            cb.type='checkbox';
            cb.checked=th.style.display!=='none';
            cb.onchange=function(){toggleColumn(th.dataset.col,cb.checked)};
            label.appendChild(cb);
            label.append(' '+th.textContent.trim());
            container.appendChild(label);
        });
    }
    function toggleColumn(colName,show){
        var th=document.querySelector('#header-row th[data-col="'+colName+'"]');
        if(!th)return;
        var idx=[].indexOf.call(th.parentElement.children,th);
        th.style.display=show?'':'none';
        document.querySelectorAll('#grid-body tr').forEach(function(row){
            var cells=row.querySelectorAll('td, th');
            if(cells[idx])cells[idx].style.display=show?'':'none';
        });
    }
    document.addEventListener('click',function(e){
        var panel=document.getElementById('config-panel');
        var btn=document.getElementById('btn-config');
        if(panel && btn && panel.style && panel.style.display!=='none' && !panel.contains(e.target) && !btn.contains(e.target)){
            panel.style.display='none';
            btn.classList.remove('active');
        }
    });

    function printReport(){var d=document.getElementById('as_of_date').value;if(!d){showToast('error','Please select an As of Date.');return}window.open('{{route("accounting.report.aging-report.print")}}?as_of_date='+encodeURIComponent(d)+'&office_id='+encodeURIComponent(document.getElementById('office_id').value)+'&aging_type='+encodeURIComponent(getAgingType())+'&trade_partner_type='+encodeURIComponent(getRadioVal('tp_type'))+'&group_by='+encodeURIComponent(getRadioVal('group_by'))+'&sort_by='+encodeURIComponent(getRadioVal('sort_by'))+'&report_type='+encodeURIComponent(getRadioVal('report_type'))+'&ending_date_type='+encodeURIComponent(getRadioVal('ending_date_type')),'_blank')}
    function exportExcel(){var d=document.getElementById('as_of_date').value;if(!d){showToast('error','Please select an As of Date.');return}window.location.href='{{route("accounting.report.aging-report.export-excel")}}?as_of_date='+encodeURIComponent(d)+'&office_id='+encodeURIComponent(document.getElementById('office_id').value)+'&aging_type='+encodeURIComponent(getAgingType())+'&trade_partner_type='+encodeURIComponent(getRadioVal('tp_type'))+'&group_by='+encodeURIComponent(getRadioVal('group_by'))+'&sort_by='+encodeURIComponent(getRadioVal('sort_by'))+'&report_type='+encodeURIComponent(getRadioVal('report_type'))+'&ending_date_type='+encodeURIComponent(getRadioVal('ending_date_type'))}
    </script>
    @endpush
</x-layout>
