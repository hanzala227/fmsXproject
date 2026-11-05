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
        .form-table .flabel{background:#eef1f5;width:140px;padding:5px 10px;font-weight:600;color:#333;white-space:nowrap}
        .form-table .finput{padding-left:10px}
        .form-table input[type="radio"],.form-table input[type="checkbox"]{accent-color:#3b82f6;margin-right:3px}
        .form-table label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap}
        .form-table select{height:24px;border:1px solid #c2cad8;padding:2px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff;min-width:140px}
        .form-table input[type="date"]{height:24px;border:1px solid #c2cad8;padding:2px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff}
        .radio-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
        .check-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
        .section-header{font-size:11px;font-weight:600;color:#475569;border-top:1px solid #e2e8f0;padding-top:6px;margin-top:4px}
        .btn-row{text-align:left;margin-top:14px;padding-top:10px;border-top:1px solid #e2e8f0;display:flex;gap:8px;flex-wrap:wrap}
        .btn-row button{margin:0}
        .grid-container{width:100%;overflow-x:auto;background:#fff;margin-top:10px}
        .grid-table{border-collapse:collapse;width:100%;font-size:11px}
        .grid-table th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:5px 8px;white-space:nowrap;text-align:left;font-size:10px}
        .grid-table td{padding:4px 8px;border:1px solid #e2e8f0;white-space:nowrap;color:#334155}
        .grid-table td.num{text-align:right;font-family:'Courier New',monospace}
        .grid-table tbody tr:hover{background:#f1f5f9}
        .grid-table .subtotal-row{background:#f8fafc!important;font-weight:700}
        .grid-table .subtotal-row td{border-top:2px solid #475569;color:#0f172a}
        .grid-table .grand-total-row{background:#1e293b!important;font-weight:700}
        .grid-table .grand-total-row td{color:#fff;border:1px solid #0f172a;font-size:11px}
        .status-paid{color:#16a34a;font-weight:600}
        .status-not-paid{color:#dc2626;font-weight:600}
        .status-partial{color:#d97706;font-weight:600}
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
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Revenue / Cost Report</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-bar-chart" style="color:#3b82f6;margin-right:6px;"></i>Revenue / Cost Report</span>
            </div>
            <div class="portlet-body">
                <table class="form-table">
                    <tr>
                        <td class="flabel">Type</td>
                        <td class="finput" colspan="3">
                            <select id="type" style="min-width:160px;">
                                <option value="revenue">Revenue(All)</option>
                                <option value="cost">Cost(All)</option>
                                <option value="all">All</option>
                            </select>
                        </td>
                        <td class="flabel">Payment Status</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="payment_status" value="all" checked> All</label>
                                <label><input type="radio" name="payment_status" value="paid"> Paid</label>
                                <label><input type="radio" name="payment_status" value="not_paid"> Not paid</label>
                                <label><input type="radio" name="payment_status" value="partial"> Partial / Over Paid</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Shipping Type</td>
                        <td class="finput" colspan="5">
                            <div class="check-row">
                                <label><input type="checkbox" id="st_all" checked onchange="toggleAllShipping(this)"> All</label>
                                <label><input type="checkbox" class="st-check" value="Ocean Import"> Ocean Import</label>
                                <label><input type="checkbox" class="st-check" value="Ocean Export"> Ocean Export</label>
                                <label><input type="checkbox" class="st-check" value="Air Import"> Air Import</label>
                                <label><input type="checkbox" class="st-check" value="Air Export"> Air Export</label>
                                <label><input type="checkbox" class="st-check" value="Truck Operation"> Truck Operation</label>
                                <label><input type="checkbox" class="st-check" value="Misc. Operation"> Misc. Operation</label>
                                <label><input type="checkbox" class="st-check" value="Warehouse Operation"> Warehouse Operation</label>
                                <label><input type="checkbox" class="st-check" value="Other Operation"> Other Operation</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Period</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="period_type" value="post_date"> Post Date</label>
                                <label><input type="radio" name="period_type" value="paid_date"> Paid Date</label>
                                <label><input type="radio" name="period_type" value="invoice_date" checked> Invoice Date</label>
                            </div>
                        </td>
                        <td class="flabel" style="width:auto;"></td>
                        <td class="finput" colspan="3">
                            <div class="radio-row" style="gap:6px;">
                                <input type="date" id="start_date" value="{{ date('Y-m-01') }}">
                                <span style="font-size:11px;color:#64748b;">~</span>
                                <input type="date" id="end_date" value="{{ date('Y-m-t') }}">
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
                        <td class="flabel">By Agent / Customer</td>
                        <td class="finput">
                            <select id="bill_to_id">
                                <option value="">All</option>
                                @foreach($tradePartners as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">By Sales Person</td>
                        <td class="finput">
                            <select id="sales_person_id">
                                <option value="">All</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="flabel">Report Type</td>
                        <td class="finput">
                            <div class="radio-row">
                                <label><input type="radio" name="report_type" value="summary" checked> Summary</label>
                                <label><input type="radio" name="report_type" value="detail"> Detail</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel"></td>
                        <td class="finput" colspan="5">
                            <label style="font-size:11px;color:#334155;cursor:pointer;">
                                <input type="checkbox" id="include_local_tp" style="accent-color:#3b82f6;"> Include local customer data
                            </label>
                        </td>
                    </tr>
                </table>

                <div class="btn-row">
                    <button class="btn-action-round" onclick="printReport()"><i class="fa fa-print"></i> Print</button>
                </div>

                <div style="margin-top:12px;padding:20px;text-align:center;color:#94a3b8;font-size:11px;">
                    <i class="fa fa-info-circle" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                    Select filters and click <strong>Print</strong> to generate the Revenue / Cost Report.
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

    function toggleAllShipping(cb){
        document.querySelectorAll('.st-check').forEach(function(c){c.checked=cb.checked});
    }
    document.querySelectorAll('.st-check').forEach(function(c){
        c.addEventListener('change',function(){
            var allCb=document.getElementById('st_all');
            var checks=document.querySelectorAll('.st-check');
            var allChecked=true;
            checks.forEach(function(ch){if(!ch.checked)allChecked=false});
            allCb.checked=allChecked;
        });
    });

    function getSelectedShippingTypes(){
        var allCb=document.getElementById('st_all');
        if(allCb.checked)return'all';
        var types=[];
        document.querySelectorAll('.st-check:checked').forEach(function(c){types.push(c.value)});
        return types.length>0?types.join(','):'all';
    }

    function printReport(){
        var startDate=document.getElementById('start_date').value;
        var endDate=document.getElementById('end_date').value;
        if(!startDate||!endDate){showToast('error','Please select both start and end dates.');return}
        var qs='start_date='+encodeURIComponent(startDate)+'&end_date='+encodeURIComponent(endDate)+'&office_id='+encodeURIComponent(document.getElementById('office_id').value)+'&type='+encodeURIComponent(document.getElementById('type').value)+'&shipping_types='+encodeURIComponent(getSelectedShippingTypes())+'&payment_status='+encodeURIComponent(getRadioVal('payment_status'))+'&period_type='+encodeURIComponent(getRadioVal('period_type'))+'&bill_to_id='+encodeURIComponent(document.getElementById('bill_to_id').value)+'&sales_person_id='+encodeURIComponent(document.getElementById('sales_person_id').value)+'&report_type='+encodeURIComponent(getRadioVal('report_type'));
        window.open('{{route("accounting.report.revenue-cost.print")}}?'+qs,'_blank');
    }

    </script>
    @endpush
</x-layout>
