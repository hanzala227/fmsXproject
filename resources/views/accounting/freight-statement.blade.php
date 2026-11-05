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
        .table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:8px;padding:6px 8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:3px}
        .table-toolbar .left{display:flex;align-items:center;gap:8px}
        .table-toolbar .right{display:flex;align-items:center;gap:6px}
        .input-search{height:22px;border:1px solid #c2cad8;padding:1px 6px;font-size:11px;border-radius:2px;color:#333;background:#fff;width:180px}
        .grid-container{width:100%;overflow-x:auto;background:#fff;margin-top:0}
        .grid-table{border-collapse:collapse;width:100%;font-size:11px}
        .grid-table th{background:#4a5568;color:#fff;font-weight:600;border:1px solid #2d3748;padding:4px 6px;white-space:nowrap;text-align:left;font-size:10px}
        .grid-table td{padding:3px 6px;border:1px solid #e2e8f0;white-space:nowrap;color:#334155}
        .grid-table td.num{text-align:right;font-family:'Courier New',monospace}
        .grid-table tbody tr:hover{background:#f1f5f9}
        .grid-table .total-row{background:#f8fafc!important;font-weight:700}
        .grid-table .total-row td{border-top:2px solid #475569;color:#0f172a}
        .toast-container{position:fixed;top:16px;right:16px;z-index:10000;display:flex;flex-direction:column;gap:8px}
        .toast{padding:10px 16px;border-radius:4px;font-size:11px;font-weight:600;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:slideIn .3s ease}
        .toast.success{background:#22c55e}
        .toast.error{background:#e73d4a}
        @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        .partner-card{background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;padding:8px 12px;margin-bottom:10px;font-size:11px;color:#1e40af}
    </style>
    @endpush

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Report</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Freight Statement</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject">Freight Statement Filters</span>
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
                            <select id="billToId" class="gf-select" style="width:240px;">
                                <option value="">-- All Partners --</option>
                                @foreach($tradePartners as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach
                            </select>
                            <select id="accountGroupId" class="gf-select" style="width:240px;display:none;">
                                <option value="">-- All Account Groups --</option>
                                @foreach($accountGroups as $ag)<option value="{{ $ag }}">{{ $ag }}</option>@endforeach
                            </select>
                        </td>
                        <td class="flabel">Report by</td>
                        <td class="finput" colspan="2">
                            <div class="check-row">
                                <label><input type="checkbox" id="transTypeAll" checked> All</label>
                                <label><input type="checkbox" class="trans-type-check" value="debit" checked> Debit</label>
                                <label><input type="checkbox" class="trans-type-check" value="credit" checked> Credit</label>
                                <label><input type="checkbox" class="trans-type-check" value="ar" checked> A/R</label>
                                <label><input type="checkbox" class="trans-type-check" value="ap" checked> A/P</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel"><span class="text-danger">*</span>Period</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row">
                                <select id="periodType" class="gf-select" style="width:100px;">
                                    <option value="post_date">Post Date</option>
                                </select>
                                <input type="date" id="asOfDate" value="{{ date('Y-m-d') }}" style="width:130px;height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;">
                                <label><input type="radio" name="period_mode" value="as_of_today" checked> As of Today</label>
                            </div>
                        </td>
                        <td class="flabel">Office</td>
                        <td class="finput" colspan="2">
                            <select id="officeId" class="gf-select" style="width:220px;">
                                <option value="">-- All Offices --</option>
                                @foreach($offices as $office)<option value="{{ $office->id }}">{{ $office->name }}</option>@endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Filter by</td>
                        <td class="finput" colspan="2">
                            <div class="radio-row">
                                <label><input type="radio" name="payment_status" value="all" checked> All</label>
                                <label><input type="radio" name="payment_status" value="open"> Open</label>
                                <label><input type="radio" name="payment_status" value="paid"> Paid</label>
                            </div>
                        </td>
                        <td class="flabel">More Options</td>
                        <td class="finput" colspan="2">
                            <div class="check-row" style="flex-wrap:wrap;">
                                <label><input type="checkbox" id="hideOverpaid"> Do Not Show Overpaid Invoice(s)</label>
                                <label><input type="checkbox" id="invoiceLocalReceived"> Invoice(Local A/R) Received Only</label>
                                <label><input type="checkbox" id="showBookingNumber" checked> Show Booking Number</label>
                            </div>
                        </td>
                    </tr>
                </table>
                <div style="text-align:center;margin-top:12px;padding-top:10px;border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn-search-gf" id="btnSearch"><i class="fa fa-search"></i> View Statement</button>
                </div>
            </div>
        </div>

        {{-- Results Section --}}
        <div id="resultsSection" style="display:none;">
            <div id="partnerInfoBox" class="partner-card" style="display:none;">
                <div style="font-weight:700;font-size:12px;" id="piName"></div>
                <div id="piDetails" style="font-size:10px;color:#475569;margin-top:2px;"></div>
            </div>

            <div class="table-toolbar">
                <div class="left">
                    <span style="font-size:11px;font-weight:700;color:#1e293b;">Statement Details:</span>
                    <span id="recordCount" style="font-size:11px;color:#64748b;font-weight:600;"></span>
                </div>
                <div class="right">
                    <input type="text" class="input-search" id="quickSearch" placeholder="Quick search statement...">
                    <button class="btn-action-round secondary" type="button" id="btnPrint" title="Print Statement"><i class="fa fa-print"></i> Print</button>
                    <button class="btn-action-round secondary" type="button" id="btnExport" title="Export Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                </div>
            </div>

            <div class="grid-container">
                <table class="grid-table" id="resultsTable">
                    <thead>
                        <tr>
                            <th style="width:8%;">Post Date</th>
                            <th style="width:8%;">ETD</th>
                            <th style="width:11%;">Invoice No.</th>
                            <th style="width:11%;" class="col-booking-no">Booking No.</th>
                            <th style="width:11%;">MB/L No.</th>
                            <th style="width:10%;">HB/L No.</th>
                            <th style="width:14%;">Partner</th>
                            <th style="width:5%;">Cur.</th>
                            <th style="width:8%;text-align:right;">DRI/AR (+)</th>
                            <th style="width:8%;text-align:right;">CRI/AP (-)</th>
                            <th style="width:8%;text-align:right;">Paid</th>
                            <th style="width:8%;text-align:right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody"></tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" style="text-align:right;font-weight:700;">TOTAL (BALANCE)</td>
                            <td class="col-booking-no"></td>
                            <td colspan="4"></td>
                            <td id="totalDR" class="num" style="font-weight:700;">0.00</td>
                            <td id="totalCR" class="num" style="font-weight:700;">0.00</td>
                            <td id="totalPaid" class="num" style="font-weight:700;">0.00</td>
                            <td id="totalBalance" class="num" style="font-weight:700;">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Per-Currency Summary Breakdown --}}
            <div id="currencySummaryBox" style="margin-top:14px;background:#fff;border:1px solid #cbd5e1;padding:10px;border-radius:2px;">
                <div style="font-size:11px;font-weight:700;color:#1e293b;text-transform:uppercase;margin-bottom:6px;">Currency Summary</div>
                <table class="grid-table" style="width:auto;min-width:480px;">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th style="text-align:right;">DRI/AR (+)</th>
                            <th style="text-align:right;">CRI/AP (-)</th>
                            <th style="text-align:right;">Paid</th>
                            <th style="text-align:right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody id="currencySummaryBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        var partnerTypeAC = document.getElementById('partnerTypeAC');
        var partnerTypeAG = document.getElementById('partnerTypeAG');
        var billToIdSel = document.getElementById('billToId');
        var accountGroupIdSel = document.getElementById('accountGroupId');
        var showBookingNumberCb = document.getElementById('showBookingNumber');

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
        });
        for (var i = 0; i < transChecks.length; i++) {
            transChecks[i].addEventListener('change', function() {
                var allChecked = true;
                for (var j = 0; j < transChecks.length; j++) { if (!transChecks[j].checked) { allChecked = false; break; } }
                transTypeAll.checked = allChecked;
            });
        }

        var periodToday = document.querySelector('input[name="period_mode"][value="as_of_today"]');
        if (periodToday) {
            periodToday.addEventListener('change', function() {
                document.getElementById('asOfDate').value = '{{ date("Y-m-d") }}';
            });
        }

        function showToast(type, msg) {
            var container = document.getElementById('toastContainer');
            var toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.textContent = msg;
            container.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 4000);
        }

        function formatNumber(num) {
            return (parseFloat(num) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function buildParams() {
            var params = new URLSearchParams();
            params.set('as_of_date', document.getElementById('asOfDate').value);
            var pt = partnerTypeAG.checked ? 'account_group' : 'agent_customer';
            params.set('partner_type', pt);
            if (pt === 'account_group') { var ag = accountGroupIdSel.value; if (ag) params.set('account_group', ag); }
            else { var bt = billToIdSel.value; if (bt) params.set('bill_to_id', bt); }
            var ps = document.querySelector('input[name="payment_status"]:checked');
            params.set('payment_status', ps ? ps.value : 'all');
            params.set('hide_overpaid', document.getElementById('hideOverpaid').checked ? '1' : '0');
            params.set('invoice_local_received', document.getElementById('invoiceLocalReceived').checked ? '1' : '0');
            params.set('show_booking_number', showBookingNumberCb.checked ? '1' : '0');
            var oe = document.getElementById('officeId');
            if (oe.value) params.set('office_id', oe.value);
            var st = [];
            for (var i = 0; i < transChecks.length; i++) { if (transChecks[i].checked) st.push(transChecks[i].value); }
            params.set('trans_type', (st.length > 0 && !transTypeAll.checked) ? st.join(',') : 'all');
            return params;
        }

        var statementRows = [];

        function renderRows(data) {
            var tbody = document.getElementById('resultsBody');
            tbody.innerHTML = '';
            var showBkg = showBookingNumberCb.checked;

            // Toggle booking column visibility in table header and total
            var bkgCols = document.querySelectorAll('.col-booking-no');
            bkgCols.forEach(function(el) { el.style.display = showBkg ? '' : 'none'; });

            if (!data || data.length === 0) {
                var colspan = showBkg ? 12 : 11;
                tbody.innerHTML = '<tr><td colspan="' + colspan + '" style="text-align:center;padding:25px;color:#94a3b8;">No statement records found for the selected criteria.</td></tr>';
                return;
            }

            data.forEach(function(row) {
                var tr = document.createElement('tr');
                tr.innerHTML = 
                    '<td>' + (row.invoice_date || '') + '</td>' +
                    '<td>' + (row.etd || '') + '</td>' +
                    '<td><strong>' + (row.invoice_no || '') + '</strong></td>' +
                    '<td class="col-booking-no" style="' + (showBkg ? '' : 'display:none;') + '">' + (row.booking_no || '') + '</td>' +
                    '<td>' + (row.mbl_no || '') + '</td>' +
                    '<td>' + (row.hbl_no || '') + '</td>' +
                    '<td>' + (row.partner_name || '') + '</td>' +
                    '<td>' + (row.currency || 'USD') + '</td>' +
                    '<td class="num">' + (row.dr_amount > 0 ? formatNumber(row.dr_amount) : '-') + '</td>' +
                    '<td class="num">' + (row.cr_amount > 0 ? formatNumber(row.cr_amount) : '-') + '</td>' +
                    '<td class="num">' + (row.paid_amount > 0 ? formatNumber(row.paid_amount) : '-') + '</td>' +
                    '<td class="num" style="font-weight:700;' + (row.balance < 0 ? 'color:#ef4444;' : '') + '">' + formatNumber(row.balance) + '</td>';
                tbody.appendChild(tr);
            });
        }

        function renderCurrencySummary(totals) {
            var tbody = document.getElementById('currencySummaryBody');
            tbody.innerHTML = '';
            if (!totals || totals.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94a3b8;">No currency breakdown.</td></tr>';
                return;
            }
            totals.forEach(function(c) {
                var tr = document.createElement('tr');
                tr.innerHTML = 
                    '<td><strong>' + c.currency + '</strong></td>' +
                    '<td class="num">' + formatNumber(c.dr) + '</td>' +
                    '<td class="num">' + formatNumber(c.cr) + '</td>' +
                    '<td class="num">' + formatNumber(c.paid) + '</td>' +
                    '<td class="num" style="font-weight:700;">' + formatNumber(c.balance) + '</td>';
                tbody.appendChild(tr);
            });
        }

        document.getElementById('btnSearch').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';

            fetch('{{ route("accounting.report.freight-statement.view") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: buildParams().toString()
            })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function(json) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-search"></i> View Statement';

                if (!json.success) {
                    showToast('error', json.message || 'Error generating statement.');
                    return;
                }

                document.getElementById('resultsSection').style.display = 'block';

                // Partner info card
                var piBox = document.getElementById('partnerInfoBox');
                if (json.partner_info && json.partner_info.name) {
                    piBox.style.display = 'block';
                    document.getElementById('piName').textContent = 'Partner: ' + json.partner_info.name;
                    var parts = [json.partner_info.address, json.partner_info.city, json.partner_info.country].filter(Boolean);
                    document.getElementById('piDetails').textContent = parts.join(', ');
                } else {
                    piBox.style.display = 'none';
                }

                statementRows = json.results || [];
                document.getElementById('recordCount').textContent = '(' + statementRows.length + ' records)';
                renderRows(statementRows);

                // Update summary footer
                if (json.summary) {
                    document.getElementById('totalDR').textContent = formatNumber(json.summary.total_dr);
                    document.getElementById('totalCR').textContent = formatNumber(json.summary.total_cr);
                    document.getElementById('totalPaid').textContent = formatNumber(json.summary.total_paid);
                    document.getElementById('totalBalance').textContent = formatNumber(json.summary.total_balance);
                }

                renderCurrencySummary(json.currency_totals || []);

                // Smooth scroll to results
                document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
                showToast('success', 'Freight statement generated successfully.');
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-search"></i> View Statement';
                showToast('error', 'Error: ' + err.message);
            });
        });

        // Live Quick Search
        document.getElementById('quickSearch').addEventListener('input', function() {
            var term = this.value.toLowerCase().trim();
            if (!term) {
                renderRows(statementRows);
                return;
            }
            var filtered = statementRows.filter(function(r) {
                return (r.invoice_no && r.invoice_no.toLowerCase().includes(term)) ||
                       (r.booking_no && r.booking_no.toLowerCase().includes(term)) ||
                       (r.mbl_no && r.mbl_no.toLowerCase().includes(term)) ||
                       (r.hbl_no && r.hbl_no.toLowerCase().includes(term)) ||
                       (r.partner_name && r.partner_name.toLowerCase().includes(term));
            });
            renderRows(filtered);
        });

        // Print & Export
        document.getElementById('btnPrint').addEventListener('click', function() {
            window.open('{{ route("accounting.report.freight-statement.print") }}?' + buildParams().toString(), '_blank');
        });

        document.getElementById('btnExport').addEventListener('click', function() {
            window.location = '{{ route("accounting.report.freight-statement.export-excel") }}?' + buildParams().toString();
        });
    });
    </script>
</x-layout>
