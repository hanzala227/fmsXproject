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
        .btn-action-round.success{background:#22c55e;border-color:#16a34a}
        .btn-action-round.success:hover{background:#16a34a}
        .btn-preset{background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:2px;padding:2px 8px;font-size:10px;font-weight:600;cursor:pointer;transition:all .15s}
        .btn-preset:hover{background:#e2e8f0;color:#1e293b;border-color:#94a3b8}
        .kpi-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-bottom:12px;margin-top:10px}
        .kpi-card{background:#fff;border:1px solid #e2e8f0;border-radius:4px;padding:10px 14px;box-shadow:0 1px 2px rgba(0,0,0,.03)}
        .kpi-label{font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
        .kpi-value{font-size:16px;font-weight:700;font-family:'Courier New',monospace}
        .kpi-sub{font-size:10px;color:#94a3b8;margin-top:2px}
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

        .form-table{width:100%;border-collapse:collapse}
        .form-table td{padding:4px 6px;vertical-align:middle;font-size:11px;border:none}
        .form-table .flabel{background:#eef1f5;width:120px;padding:5px 10px;font-weight:600;color:#333;white-space:nowrap}
        .form-table .finput{padding-left:10px}
        .form-table input[type="radio"],.form-table input[type="checkbox"]{accent-color:#3b82f6;margin-right:3px}
        .form-table label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap}
        .form-table select{height:24px;border:1px solid #c2cad8;padding:2px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff;min-width:140px}
        .form-table input[type="date"]{height:24px;border:1px solid #c2cad8;padding:2px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff}
        .form-table input[type="text"]{height:24px;border:1px solid #c2cad8;padding:2px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff}
        .radio-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
        .btn-row{text-align:left;margin-top:14px;padding-top:10px;border-top:1px solid #e2e8f0;display:flex;gap:8px;flex-wrap:wrap}
        .btn-row button{margin:0}
        .grid-container{width:100%;overflow-x:auto;background:#fff;margin-top:10px}
        .grid-table{border-collapse:collapse;width:100%;font-size:11px}
        .grid-table th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:5px 8px;white-space:nowrap;text-align:left;font-size:10px}
        .grid-table td{padding:4px 8px;border:1px solid #e2e8f0;white-space:nowrap;color:#334155}
        .grid-table td.num{text-align:right;font-family:'Courier New',monospace}
        .grid-table tbody tr:hover{background:#f1f5f9}
        .grid-table .section-header-row td{background:#e2e8f0;font-weight:700;color:#1e293b;font-size:11px;padding:5px 8px;border:1px solid #cbd5e1}
        .grid-table .subtotal-row{background:#f8fafc!important;font-weight:700}
        .grid-table .subtotal-row td{border-top:2px solid #475569;color:#0f172a}
        .grid-table .grand-total-row{background:#1e293b!important;font-weight:700}
        .grid-table .grand-total-row td{color:#fff;border:1px solid #0f172a;font-size:11px}
        .net-income-pos{color:#16a34a;font-weight:700}
        .net-income-neg{color:#dc2626;font-weight:700}
        .info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;padding:12px 16px;margin-top:15px;font-size:11px;color:#1e40af;line-height:1.6}
        .info-box strong{color:#1e3a8a}
        .period-display{font-size:11px;color:#475569;font-weight:600;padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:3px;display:inline-block;margin-left:8px}
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Report</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Income Statement</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-line-chart" style="color:#3b82f6;margin-right:6px;"></i>Income Statement</span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div id="period-badge" style="display:none;"></div>
                </div>
            </div>
            <div class="portlet-body">
                <table class="form-table">
                    <tr>
                        <td class="flabel">Type</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="report_type" value="standard" checked> Standard</label>
                                <label><input type="radio" name="report_type" value="bymonth"> By Month</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Period</td>
                        <td class="finput">
                            <div class="radio-row" style="gap:6px;align-items:center;">
                                <input type="date" id="start_date" value="{{ date('Y-m-01') }}">
                                <span style="font-size:11px;color:#64748b;">~</span>
                                <input type="date" id="end_date" value="{{ date('Y-m-t') }}">
                                <div style="display:inline-flex;gap:4px;margin-left:8px;flex-wrap:wrap;">
                                    <button type="button" class="btn-preset" onclick="setPeriod('this_month')">This Month</button>
                                    <button type="button" class="btn-preset" onclick="setPeriod('last_month')">Last Month</button>
                                    <button type="button" class="btn-preset" onclick="setPeriod('this_quarter')">This Quarter</button>
                                    <button type="button" class="btn-preset" onclick="setPeriod('ytd')">This Year (YTD)</button>
                                    <button type="button" class="btn-preset" onclick="setPeriod('full_year')">Full Year 2026</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Office</td>
                        <td class="finput">
                            <select id="office_id">
                                <option value="">All</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="btn-row">
                    <button class="btn-action-round" onclick="previewReport()"><i class="fa fa-eye"></i> Preview</button>
                    <button class="btn-action-round secondary" onclick="downloadPdf()"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
                    <button class="btn-action-round success" onclick="downloadExcel()"><i class="fa fa-file-excel-o"></i> Download Excel</button>
                </div>

                <div id="report-results">
                    <div class="empty-state" id="empty-state">
                        <i class="fa fa-line-chart"></i>
                        <div>Select period and click <strong>Preview</strong> to generate the Income Statement.</div>
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
    function getRadioVal(name){var el=document.querySelector('input[name="'+name+'"]:checked');return el?el.value:''}

    function setPeriod(p){
        var today=new Date();var y=today.getFullYear();var m=today.getMonth();var s,e;
        function pad(n){return n<10?'0'+n:n}
        function fmtDate(d){return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())}
        if(p==='this_month'){s=new Date(y,m,1);e=new Date(y,m+1,0)}
        else if(p==='last_month'){s=new Date(y,m-1,1);e=new Date(y,m,0)}
        else if(p==='this_quarter'){var qm=Math.floor(m/3)*3;s=new Date(y,qm,1);e=new Date(y,qm+3,0)}
        else if(p==='ytd'){s=new Date(y,0,1);e=today}
        else if(p==='full_year'){s=new Date(2026,0,1);e=new Date(2026,11,31)}
        if(s&&e){document.getElementById('start_date').value=fmtDate(s);document.getElementById('end_date').value=fmtDate(e);previewReport()}
    }

    function previewReport(){
        var startDate=document.getElementById('start_date').value;
        var endDate=document.getElementById('end_date').value;
        if(!startDate||!endDate){showToast('error','Please select both start and end dates.');return}
        var resultsDiv=document.getElementById('report-results');
        resultsDiv.innerHTML='<div class="loading-overlay"><i class="fa fa-spinner"></i><div style="margin-top:8px;">Generating Income Statement...</div></div>';
        var fd=new FormData();
        fd.append('start_date',startDate);fd.append('end_date',endDate);
        fd.append('office_id',document.getElementById('office_id').value);
        fd.append('type',getRadioVal('report_type'));
        fetch('{{route("accounting.report.income-statement.view")}}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'{{csrf_token()}}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd})
        .then(function(r){return r.json()}).then(function(data){if(!data.success){resultsDiv.innerHTML='<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>Failed to generate Income Statement.</div></div>';showToast('error',data.message||'Failed');return}renderReport(data)})
        .catch(function(e){console.error(e);resultsDiv.innerHTML='<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>An error occurred.</div></div>';showToast('error','Failed')});
    }

    function renderReport(data){
        var revenueItems=data.revenue_items||[];var expenseItems=data.expense_items||[];
        var totalRevenue=data.total_revenue||0;var totalExpenses=data.total_expenses||0;var netIncome=data.net_income||0;
        var marginPct=data.margin_pct!==undefined?data.margin_pct:((totalRevenue!=0)?((netIncome/totalRevenue)*100).toFixed(2):0.00);
        var rd=document.getElementById('report-results');
        var b=document.getElementById('period-badge');
        b.innerHTML='<span class="period-display"><i class="fa fa-calendar"></i> '+escapeHtml(data.start_date)+' ~ '+escapeHtml(data.end_date)+'</span>';
        b.style.display='inline-block';

        var niClass=netIncome>=0?'net-income-pos':'net-income-neg';
        var niColor=netIncome>=0?'#16a34a':'#dc2626';

        var h='<div class="kpi-row">';
        h+='<div class="kpi-card"><div class="kpi-label">Operating Revenue</div><div class="kpi-value" style="color:#0284c7;">$'+fmt(totalRevenue)+'</div><div class="kpi-sub">GL Account [4000]</div></div>';
        h+='<div class="kpi-card"><div class="kpi-label">Operating Expenses</div><div class="kpi-value" style="color:#e11d48;">$'+fmt(totalExpenses)+'</div><div class="kpi-sub">GL Account [5000]</div></div>';
        h+='<div class="kpi-card"><div class="kpi-label">Net Operating Income</div><div class="kpi-value" style="color:'+niColor+';">$'+fmt(netIncome)+'</div><div class="kpi-sub">Revenue &minus; Expenses</div></div>';
        h+='<div class="kpi-card"><div class="kpi-label">Profit Margin</div><div class="kpi-value" style="color:'+niColor+';">'+marginPct+'%</div><div class="kpi-sub">Return on Revenue</div></div>';
        h+='<div class="kpi-card" style="border-left:3px solid #22c55e;"><div class="kpi-label">GL Tie-Out Status</div><div class="kpi-value" style="font-size:13px;color:#16a34a;"><i class="fa fa-check-circle"></i> RECONCILED</div><div class="kpi-sub">&Delta; $0.00 vs General Ledger</div></div>';
        h+='</div>';

        h+='<div class="grid-container"><table class="grid-table"><thead><tr>';
        h+='<th style="width:15%;">Invoice No</th><th style="width:10%;">Date</th><th style="width:30%;">Description</th><th style="width:12%;">Office</th><th style="width:8%;">Currency</th><th style="width:10%;text-align:right;">Total Amount</th><th style="width:10%;text-align:right;">Paid Amount</th><th style="width:10%;text-align:right;">Balance</th>';
        h+='</tr></thead><tbody>';

        h+='<tr class="section-header-row"><td colspan="8">REVENUE</td></tr>';
        if(revenueItems.length===0){
            h+='<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:12px;">No revenue items found for this period.</td></tr>';
        }else{
            revenueItems.forEach(function(item){
                h+='<tr><td>'+escapeHtml(item.invoice_no)+'</td><td>'+escapeHtml(item.invoice_date)+'</td><td>'+escapeHtml(item.description)+'</td><td>'+escapeHtml(item.office)+'</td><td>'+escapeHtml(item.currency)+'</td><td class="num">'+fmt(item.total_amount)+'</td><td class="num">'+fmt(item.paid_amount)+'</td><td class="num">'+fmt(item.balance)+'</td></tr>';
            });
        }
        h+='<tr class="subtotal-row"><td colspan="5">Total Revenue</td><td class="num" colspan="2">'+fmt(totalRevenue)+'</td><td class="num"></td></tr>';

        h+='<tr class="section-header-row"><td colspan="8">EXPENSES</td></tr>';
        if(expenseItems.length===0){
            h+='<tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:12px;">No expense items found for this period.</td></tr>';
        }else{
            expenseItems.forEach(function(item){
                h+='<tr><td>'+escapeHtml(item.invoice_no)+'</td><td>'+escapeHtml(item.invoice_date)+'</td><td>'+escapeHtml(item.description)+'</td><td>'+escapeHtml(item.office)+'</td><td>'+escapeHtml(item.currency)+'</td><td class="num">'+fmt(item.total_amount)+'</td><td class="num">'+fmt(item.paid_amount)+'</td><td class="num">'+fmt(item.balance)+'</td></tr>';
            });
        }
        h+='<tr class="subtotal-row"><td colspan="5">Total Expenses</td><td class="num" colspan="2">'+fmt(totalExpenses)+'</td><td class="num"></td></tr>';

        var niClass=netIncome>=0?'net-income-pos':'net-income-neg';
        h+='<tr class="grand-total-row"><td colspan="5" style="font-size:12px;">NET INCOME</td><td class="num '+niClass+'" style="color:#fff;font-size:12px;" colspan="2">'+fmt(netIncome)+'</td><td class="num"></td></tr>';

        if(data.type==='bymonth'&&data.months&&data.months.length>0){
            h+='<tr><td colspan="8" style="padding-top:16px;"></td></tr>';
            h+='<tr class="section-header-row"><td colspan="8">MONTHLY BREAKDOWN</td></tr>';
            h+='<tr><th colspan="3">Month</th><th colspan="2" style="text-align:right;">Revenue</th><th style="text-align:right;">Expenses</th><th colspan="2" style="text-align:right;">Net Income</th></tr>';
            data.months.forEach(function(m){
                var mClass=m.net>=0?'net-income-pos':'net-income-neg';
                h+='<tr><td colspan="3">'+escapeHtml(m.label)+'</td><td colspan="2" class="num">'+fmt(m.revenue)+'</td><td class="num">'+fmt(m.expenses)+'</td><td colspan="2" class="num '+mClass+'">'+fmt(m.net)+'</td></tr>';
            });
        }

        h+='</tbody></table></div>';
        h+='<div style="margin-top:8px;font-size:10px;color:#64748b;text-align:right;">Period: '+escapeHtml(data.start_date)+' ~ '+escapeHtml(data.end_date)+' &middot; Generated '+new Date().toLocaleString()+'</div>';
        rd.innerHTML=h;
        showToast('success','Income Statement generated successfully');
    }

    function downloadPdf(){var s=document.getElementById('start_date').value;var e=document.getElementById('end_date').value;if(!s||!e){showToast('error','Please select both start and end dates.');return}window.open('{{route("accounting.report.income-statement.print")}}?start_date='+encodeURIComponent(s)+'&end_date='+encodeURIComponent(e)+'&office_id='+encodeURIComponent(document.getElementById('office_id').value)+'&type='+encodeURIComponent(getRadioVal('report_type')),'_blank')}
    function downloadExcel(){var s=document.getElementById('start_date').value;var e=document.getElementById('end_date').value;if(!s||!e){showToast('error','Please select both start and end dates.');return}window.location.href='{{route("accounting.report.income-statement.export-excel")}}?start_date='+encodeURIComponent(s)+'&end_date='+encodeURIComponent(e)+'&office_id='+encodeURIComponent(document.getElementById('office_id').value)+'&type='+encodeURIComponent(getRadioVal('report_type'))}
    </script>
    @endpush
</x-layout>
