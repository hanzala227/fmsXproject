<x-layout>
    @push('styles')
    <style>
        .page-content { padding: 8px 12px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Inter', 'Open Sans', sans-serif !important; }
        .portlet.light { background-color: #fff; border: 1px solid #cbd5e1; border-radius: 2px; margin-bottom: 10px !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .portlet-title { padding: 4px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; min-height: 28px; background: #f8fafc; }
        .portlet-title .title-left { display: flex; align-items: center; gap: 10px; }
        .portlet-title .title-right { display: flex; align-items: center; gap: 6px; }
        .portlet-body { padding: 12px 14px; }
        .caption-subject { color: #1e293b; font-size: 12px; font-weight: 700; text-transform: uppercase; }

        .page-bar { background-color: #fff; padding: 8px 20px; margin-bottom: 15px; border: 1px solid #e9ebec; border-radius: 4px; }
        .page-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
        .page-breadcrumb li { font-size: 12px; color: #888; display: flex; align-items: center; }
        .page-breadcrumb li a { color: #337ab7; text-decoration: none; }
        .page-breadcrumb li i { margin: 0 8px; font-size: 10px; opacity: 0.5; }

        .filter-section { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 10px; align-items: flex-start; }
        .filter-section .filter-group { display: flex; flex-direction: column; gap: 4px; }
        .filter-section .filter-group .group-label { font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 2px; }

        .form-label-box { background: #eef1f5; width: 100px; padding: 4px 8px; font-size: 11px; color: #333; min-height: 24px; display: flex; align-items: center; flex-shrink: 0; font-weight: 600; }
        .form-input-box { flex-grow: 1; padding-left: 10px; display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
        .form-group-row { display: flex; align-items: center; margin-bottom: 8px; }
        .form-control-gf { height: 24px; border: 1px solid #c2cad8; padding: 2px 6px; font-size: 11px; border-radius: 2px !important; width: 100%; max-width: 200px; color: #333; outline: none; background: #fff; }
        .form-control-gf:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
        select.form-control-gf { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 4px center; background-size: 8px; padding-right: 16px; }

        .radio-item { display: inline-flex; align-items: center; gap: 3px; font-size: 11px; color: #333; cursor: pointer; }
        .radio-item input[type="radio"], .radio-item input[type="checkbox"] { margin: 0; cursor: pointer; width: 12px; height: 12px; accent-color: #3b82f6; }

        .inline-fields { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

        .button-row { display: flex; justify-content: center; gap: 8px; margin: 12px 0; }
        .btn-action-round { background: #64748b; color: #fff; border: 1px solid #475569; border-radius: 2px; padding: 0 10px; height: 22px; font-size: 10px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; text-decoration: none; transition: all 0.15s; box-sizing: border-box; }
        .btn-action-round:hover { background: #475569; color: #fff; }
        .btn-action-round.primary { background: #3b82f6; border-color: #2563eb; }
        .btn-action-round.primary:hover { background: #2563eb; }
        .btn-action-round.green { background: #22c55e; border-color: #16a34a; }
        .btn-action-round.green:hover { background: #16a34a; }

        .text-danger { color: #e73d4a; }
        .fw-600 { font-weight: 600; }
        .num { text-align: right; font-family: 'Courier New', monospace; }

        .sort-by-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 2px; padding: 10px 14px; margin-top: 10px; }
        .sort-by-title { font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
        .sort-by-row { display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-start; margin-bottom: 6px; }
        .sort-by-group { display: flex; align-items: center; gap: 10px; }
        .sort-by-label { font-size: 10px; font-weight: 600; color: #64748b; text-transform: uppercase; min-width: 55px; }

        .grid-container { width: 100%; overflow-x: auto; background: #fff; margin-top: 8px; }
        .grid-table { border-collapse: collapse; width: 100%; font-size: 11px; }
        .grid-table th { background: #f8fafc; color: #475569; font-weight: 600; border: 1px solid #e2e8f0; padding: 4px 6px; white-space: nowrap; text-align: left; }
        .grid-table th.num { text-align: right; }
        .grid-table td { padding: 3px 6px; border: 1px solid #e2e8f0; white-space: nowrap; color: #334155; }
        .grid-table td.num { text-align: right; font-family: 'Courier New', monospace; }
        .grid-table tbody tr:hover { background: #f1f5f9; cursor: pointer; }
        .grid-table .total-row { background: #f8fafc !important; font-weight: 700; }
        .grid-table .total-row td { border-top: 2px solid #475569; color: #0f172a; }
        .grid-table .detail-link { color: #3b82f6; cursor: pointer; text-decoration: underline; font-size: 10px; }
        .grid-table .detail-link:hover { color: #2563eb; }

        .table-header-bar { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding: 4px 0; }
        .table-header-info { font-size: 11px; color: #475569; font-weight: 600; }
        .table-header-right { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #475569; }
        .table-header-right select { height: 20px; font-size: 10px; border: 1px solid #c2cad8; border-radius: 2px; padding: 0 4px; }

        .pagination-bar { display: flex; justify-content: flex-end; align-items: center; gap: 4px; margin-top: 6px; padding: 4px 0; font-size: 10px; }
        .pagination-bar button { background: #fff; border: 1px solid #c2cad8; color: #475569; padding: 2px 8px; cursor: pointer; border-radius: 2px; font-size: 10px; }
        .pagination-bar button:hover { background: #f1f5f9; }
        .pagination-bar button.active { background: #3b82f6; color: #fff; border-color: #2563eb; }
        .pagination-bar button:disabled { opacity: 0.4; cursor: default; }

        .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; }
        .empty-state i { font-size: 36px; display: block; margin-bottom: 12px; }
        .loading-overlay { text-align: center; padding: 30px; color: #64748b; }
        .loading-overlay i { font-size: 20px; animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .toast-container { position: fixed; top: 56px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 6px; pointer-events: none; }
        .toast { background: #1e293b; color: #fff; padding: 8px 14px; border-radius: 4px; font-size: 11px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 8px; animation: toastIn 0.25s ease; pointer-events: all; }
        .toast.success { border-left: 3px solid #22c55e; }
        .toast.error   { border-left: 3px solid #ef4444; }
        .toast.info    { border-left: 3px solid #3b82f6; }
        @keyframes toastIn { from { transform: translateX(40px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .detail-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 2px; margin-top: 10px; }
        .detail-panel .detail-header { padding: 6px 10px; font-size: 11px; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px; }
        .detail-panel .detail-header i { color: #3b82f6; }

        .status-badge { display: inline-block; padding: 1px 5px; border-radius: 2px; font-size: 9px; font-weight: 600; text-transform: uppercase; }
        .status-cleared { background: #dcfce7; color: #166534; }
        .status-outstanding { background: #fef9c3; color: #854d0e; }
        .status-void { background: #fee2e2; color: #991b1b; }

        .vendor-select-wrapper { position: relative; display: flex; align-items: center; gap: 4px; }
        .vendor-select-wrapper select { max-width: 180px; }
        .vendor-select-wrapper .vendor-actions { display: flex; gap: 2px; }
        .vendor-select-wrapper .vendor-actions button { background: none; border: 1px solid #c2cad8; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 2px; font-size: 9px; color: #64748b; }
        .vendor-select-wrapper .vendor-actions button:hover { background: #f1f5f9; color: #333; }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Bank</li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">Check/Deposit Report</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <div class="title-left">
                    <span class="caption-subject"><i class="fa fa-list-alt" style="color:#3b82f6;margin-right:6px;"></i>Check/Deposit Report</span>
                </div>
                <div class="title-right">
                    <button class="btn-action-round" onclick="printReport()" title="Print"><i class="fa fa-print"></i> Print</button>
                </div>
            </div>
            <div class="portlet-body">

                {{-- Filter Grid --}}
                <div class="filter-section">
                    {{-- Report Type --}}
                    <div class="filter-group">
                        <div class="group-label">Report Type</div>
                        <div class="inline-fields">
                            <label class="radio-item"><input type="checkbox" id="report_type_checks" checked> Check Journal</label>
                            <label class="radio-item"><input type="checkbox" id="report_type_deposits" checked> Deposit Journal</label>
                        </div>
                    </div>

                    {{-- Office --}}
                    <div class="filter-group">
                        <div class="group-label">Office</div>
                        <select id="office_id" class="form-control-gf" style="max-width:140px;">
                            <option value="">All</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->code }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vendor --}}
                    <div class="filter-group">
                        <div class="group-label">Vendor</div>
                        <div class="vendor-select-wrapper">
                            <select id="vendor_id" class="form-control-gf" style="max-width:180px;">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <div class="vendor-actions">
                                <button type="button" title="Edit" onclick="editVendor()"><i class="fa fa-pencil"></i></button>
                                <button type="button" title="Clear" onclick="document.getElementById('vendor_id').value='';"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        <label class="radio-item" style="margin-top:3px;"><input type="checkbox" id="include_local_tp"> Include local customer data</label>
                    </div>

                    {{-- Payment Type --}}
                    <div class="filter-group">
                        <div class="group-label">Payment Type</div>
                        <select id="payment_type" class="form-control-gf" style="max-width:140px;">
                            <option value="ALL">All</option>
                            <option value="BANK_TRANSFER">Bank Transfer</option>
                            <option value="CHECK">Check</option>
                            <option value="CASH">Cash</option>
                            <option value="CREDIT_CARD">Credit Card</option>
                        </select>
                    </div>

                    {{-- Period --}}
                    <div class="filter-group">
                        <div class="group-label">Period</div>
                        <div class="inline-fields">
                            <label class="radio-item"><input type="radio" name="period_type" value="post_date" checked> Post Date</label>
                            <label class="radio-item"><input type="radio" name="period_type" value="bank_date"> Bank Date</label>
                            <input type="date" id="period_date" class="form-control-gf" style="max-width:130px;" value="{{ date('Y-m-d') }}">
                            <label class="radio-item"><input type="checkbox" id="as_of_today"> As of Today</label>
                        </div>
                    </div>

                    {{-- Options --}}
                    <div class="filter-group">
                        <div class="group-label">Options</div>
                        <label class="radio-item"><input type="checkbox" id="deposit_clear_only"> Deposit / Clear Only</label>
                    </div>
                </div>

                {{-- Sort By Section --}}
                <div class="sort-by-section">
                    <div class="sort-by-title">Sort by</div>
                    <div class="sort-by-row">
                        <div class="sort-by-group">
                            <span class="sort-by-label">Summary</span>
                            <label class="radio-item"><input type="radio" name="summary_sort" value="bank" checked> Bank</label>
                            <label class="radio-item"><input type="radio" name="summary_sort" value="vendor_customer"> Vendor/Customer</label>
                            <label class="radio-item"><input type="radio" name="summary_sort" value="date"> Date</label>
                        </div>
                    </div>
                    <div class="sort-by-row">
                        <div class="sort-by-group">
                            <span class="sort-by-label">Detail</span>
                            <label class="radio-item"><input type="radio" name="detail_sort" value="date" checked> Date</label>
                            <label class="radio-item"><input type="radio" name="detail_sort" value="check_no"> Check No.</label>
                            <label class="radio-item"><input type="radio" name="detail_sort" value="amount"> Amount</label>
                        </div>
                        <div class="sort-by-group" style="margin-left:16px;">
                            <label class="radio-item"><input type="checkbox" id="show_remark"> Show Remark</label>
                            <label class="radio-item"><input type="checkbox" id="show_detail"> Show Detail</label>
                        </div>
                    </div>
                </div>

                {{-- View Button --}}
                <div class="button-row">
                    <button class="btn-action-round primary" onclick="viewReport()"><i class="fa fa-search"></i> View</button>
                </div>

                {{-- Report Results --}}
                <div id="report-results">
                    <div class="empty-state" id="empty-state">
                        <i class="fa fa-inbox"></i>
                        <div>Select report options and click <strong>View</strong> to generate the report.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    var currentReportData = null;

    function showToast(type, msg) {
        var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() { t.remove(); }, 3000);
    }

    function fmt(val) {
        if (val === undefined || val === null) return '0.00';
        return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esc(text) {
        if (!text) return '--';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function editVendor() {
        var vendorId = document.getElementById('vendor_id').value;
        if (!vendorId) {
            showToast('error', 'Please select a vendor first.');
            return;
        }
        window.open('/trade-partner/' + vendorId + '/edit', '_blank');
    }

    function getStatusBadge(status) {
        var cls = 'status-outstanding';
        if (status === 'Cleared') cls = 'status-cleared';
        else if (status === 'Void') cls = 'status-void';
        return '<span class="status-badge ' + cls + '">' + esc(status) + '</span>';
    }

    function getFormData(page) {
        var periodType = document.querySelector('input[name="period_type"]:checked').value;
        var summarySort = document.querySelector('input[name="summary_sort"]:checked').value;
        var detailSort = document.querySelector('input[name="detail_sort"]:checked').value;

        return {
            report_type_checks: document.getElementById('report_type_checks').checked ? '1' : '0',
            report_type_deposits: document.getElementById('report_type_deposits').checked ? '1' : '0',
            office_id: document.getElementById('office_id').value,
            vendor_id: document.getElementById('vendor_id').value,
            payment_type: document.getElementById('payment_type').value,
            period_type: periodType,
            period_date: document.getElementById('period_date').value,
            as_of_today: document.getElementById('as_of_today').checked ? '1' : '0',
            deposit_clear_only: document.getElementById('deposit_clear_only').checked ? '1' : '0',
            summary_sort: summarySort,
            detail_sort: detailSort,
            show_remark: document.getElementById('show_remark').checked ? '1' : '0',
            show_detail: document.getElementById('show_detail').checked ? '1' : '0',
            per_page: 25,
            page: page || 1,
        };
    }

    function viewReport(page) {
        page = page || 1;
        var formData = getFormData(page);

        var resultsDiv = document.getElementById('report-results');
        resultsDiv.innerHTML = '<div class="loading-overlay"><i class="fa fa-spinner"></i><div style="margin-top:8px;">Generating report...</div></div>';

        var body = new FormData();
        for (var key in formData) {
            body.append(key, formData[key]);
        }

        fetch('{{ route("accounting.bank.check-deposit-report.view") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body,
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>' + esc(data.message || 'Failed to generate report') + '</div></div>';
                showToast('error', data.message || 'Failed to generate report');
                return;
            }
            currentReportData = data;
            renderReport(data);
        })
        .catch(function(err) {
            console.error(err);
            resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>An error occurred while generating the report.</div></div>';
            showToast('error', 'Failed to generate report');
        });
    }

    function renderReport(data) {
        var resultsDiv = document.getElementById('report-results');
        var rows = data.summary_rows || [];
        var gt = data.grand_total || {};

        var totalLabel = data.period_type === 'bank_date' ? 'Bank Date' : 'Post Date';
        var sortLabel = data.summary_sort === 'vendor_customer' ? 'Vendor/Customer' : (data.summary_sort === 'date' ? 'Date' : 'Bank');

        var html = '';

        // Table header bar
        html += '<div class="table-header-bar">';
        html += '<div class="table-header-info">Check/Deposit, ' + totalLabel + ', Sort by ' + sortLabel + '</div>';
        html += '<div class="table-header-right">';
        html += '<select onchange="changePerPage(this.value)" style="height:20px;font-size:10px;border:1px solid #c2cad8;border-radius:2px;padding:0 4px;">';
        var perPageOptions = [10, 25, 50, 100];
        for (var p = 0; p < perPageOptions.length; p++) {
            var selected = perPageOptions[p] == data.per_page ? ' selected' : '';
            html += '<option value="' + perPageOptions[p] + '"' + selected + '>' + perPageOptions[p] + '</option>';
        }
        html += '</select>';
        html += '<span>records</span>';
        html += '</div></div>';

        // Main table
        html += '<div class="grid-container"><table class="grid-table"><thead><tr>';
        html += '<th>Bank Name</th>';
        html += '<th class="num">Record(s)</th>';
        html += '<th class="num">Deposit</th>';
        html += '<th class="num">Check Paid</th>';
        html += '<th class="num">Total</th>';
        html += '<th>Detail</th>';
        html += '</tr></thead><tbody>';

        if (rows.length === 0) {
            html += '<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:20px;">No records found.</td></tr>';
        }

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            html += '<tr>';
            html += '<td class="fw-600">' + esc(row.bank_name) + '</td>';
            html += '<td class="num">' + row.record_count + ' Record(s).</td>';
            html += '<td class="num" style="color:#16a34a;">' + fmt(row.deposit) + '</td>';
            html += '<td class="num" style="color:#dc2626;">' + fmt(row.check_paid) + '</td>';
            var totalVal = parseFloat(row.total);
            html += '<td class="num fw-600" style="color:' + (totalVal < 0 ? '#dc2626' : totalVal > 0 ? '#16a34a' : '#475569') + ';">' + fmt(row.total) + '</td>';
            html += '<td><span class="detail-link" onclick="toggleDetail(\'' + esc(row.bank_name) + '\')"><i class="fa fa-plus-circle"></i></span></td>';
            html += '</tr>';
        }

        // Grand Total row
        html += '<tr class="total-row">';
        html += '<td>Grand Total</td>';
        html += '<td class="num">' + (gt.record_count || 0) + ' Record(s).</td>';
        html += '<td class="num">' + fmt(gt.deposit) + '</td>';
        html += '<td class="num">' + fmt(gt.check_paid) + '</td>';
        var gtNet = parseFloat(gt.total);
        html += '<td class="num" style="color:' + (gtNet < 0 ? '#dc2626' : gtNet > 0 ? '#16a34a' : '#475569') + ';">' + fmt(gt.total) + '</td>';
        html += '<td></td>';
        html += '</tr>';

        html += '</tbody></table></div>';

        // Pagination
        html += renderPagination(data);

        // Detail panel placeholder
        html += '<div id="detail-panel"></div>';

        resultsDiv.innerHTML = html;
        showToast('success', 'Report generated successfully');
    }

    function renderPagination(data) {
        var totalPages = data.total_pages || 1;
        var currentPage = data.current_page || 1;
        if (totalPages <= 1) return '';

        var html = '<div class="pagination-bar">';
        html += '<button ' + (currentPage <= 1 ? 'disabled' : '') + ' onclick="viewReport(1)" title="First">&laquo;</button> ';
        html += '<button ' + (currentPage <= 1 ? 'disabled' : '') + ' onclick="viewReport(' + (currentPage - 1) + ')" title="Previous">&lsaquo;</button> ';

        var start = Math.max(1, currentPage - 2);
        var end = Math.min(totalPages, currentPage + 2);
        for (var p = start; p <= end; p++) {
            var cls = p === currentPage ? ' active' : '';
            html += '<button class="' + cls + '" onclick="viewReport(' + p + ')">' + p + '</button> ';
        }

        html += '<button ' + (currentPage >= totalPages ? 'disabled' : '') + ' onclick="viewReport(' + (currentPage + 1) + ')" title="Next">&rsaquo;</button> ';
        html += '<button ' + (currentPage >= totalPages ? 'disabled' : '') + ' onclick="viewReport(' + totalPages + ')" title="Last">&raquo;</button>';
        html += '</div>';
        return html;
    }

    function changePerPage(val) {
        currentReportData.per_page = parseInt(val);
        viewReport(1);
    }

    function toggleDetail(bankName) {
        var panel = document.getElementById('detail-panel');
        if (panel.innerHTML.trim() !== '' && panel.getAttribute('data-bank') === bankName) {
            panel.innerHTML = '';
            panel.removeAttribute('data-bank');
            return;
        }
        panel.setAttribute('data-bank', bankName);
        panel.innerHTML = '<div class="loading-overlay"><i class="fa fa-spinner"></i><div style="margin-top:8px;">Loading details...</div></div>';

        var formData = getFormData(1);
        formData.show_detail = '1';
        formData.detail_per_page = '9999';

        var body = new FormData();
        for (var key in formData) {
            body.append(key, formData[key]);
        }

        fetch('{{ route("accounting.bank.check-deposit-report.view") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body,
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success || !data.detail_rows) {
                panel.innerHTML = '<div class="empty-state"><i class="fa fa-inbox"></i><div>No detail records.</div></div>';
                return;
            }
            renderDetailPanel(panel, bankName, data.detail_rows);
        })
        .catch(function(err) {
            panel.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>Failed to load details.</div></div>';
        });
    }

    function renderDetailPanel(panel, bankName, rows) {
        var bankRows = rows.filter(function(r) { return r.bank_name === bankName; });

        var showRemark = document.getElementById('show_remark').checked;

        var html = '<div class="detail-panel">';
        html += '<div class="detail-header"><i class="fa fa-list"></i> Detail: ' + esc(bankName) + ' (' + bankRows.length + ' records) ';
        html += '<span class="detail-link" onclick="document.getElementById(\'detail-panel\').innerHTML=\'\';" style="margin-left:auto;"><i class="fa fa-times"></i> Close</span>';
        html += '</div>';

        html += '<div class="grid-container"><table class="grid-table"><thead><tr>';
        html += '<th>Payment No.</th><th>Date</th><th>Type</th><th>Check No.</th><th>Paid To / Received From</th><th>Ref No.</th><th>Cur</th><th class="num">Amount</th><th>Office</th><th>Clear Date</th><th>Status</th>';
        if (showRemark) html += '<th>Remark</th>';
        html += '</tr></thead><tbody>';

        var depositSum = 0;
        var checkSum = 0;

        for (var i = 0; i < bankRows.length; i++) {
            var r = bankRows[i];
            var rowStyle = r.color ? 'background:' + r.color + '15;' : '';
            html += '<tr style="' + rowStyle + '">';
            html += '<td>' + esc(r.payment_no) + '</td>';
            html += '<td>' + esc(r.payment_date) + '</td>';
            html += '<td>' + (r.type === 'RECEIVED' ? '<span style="color:#16a34a;">DEPOSIT</span>' : '<span style="color:#dc2626;">CHECK</span>') + '</td>';
            html += '<td>' + esc(r.check_no) + '</td>';
            html += '<td>' + esc(r.party_name) + '</td>';
            html += '<td>' + esc(r.reference_no) + '</td>';
            html += '<td>' + esc(r.currency) + '</td>';
            html += '<td class="num fw-600">' + fmt(r.amount) + '</td>';
            html += '<td>' + esc(r.office) + '</td>';
            html += '<td>' + esc(r.clear_date) + '</td>';
            html += '<td>' + getStatusBadge(r.status) + '</td>';
            if (showRemark) html += '<td>' + esc(r.remark) + '</td>';
            html += '</tr>';

            if (r.type === 'RECEIVED') depositSum += r.amount;
            else checkSum += r.amount;
        }

        html += '<tr class="total-row">';
        html += '<td colspan="7">TOTAL: ' + esc(bankName) + '</td>';
        html += '<td class="num">' + fmt(depositSum - checkSum) + '</td>';
        html += '<td colspan="3"></td>';
        if (showRemark) html += '<td></td>';
        html += '</tr>';

        html += '</tbody></table></div></div>';
        panel.innerHTML = html;
    }

    function printReport() {
        var formData = getFormData(1);
        var params = [];
        for (var key in formData) {
            if (key === 'per_page' || key === 'page') continue;
            params.push(key + '=' + encodeURIComponent(formData[key]));
        }
        var url = '{{ route("accounting.bank.check-deposit-report.print") }}' + '?' + params.join('&');
        window.open(url, '_blank');
    }

    function exportExcel() {
        var formData = getFormData(1);
        var params = [];
        for (var key in formData) {
            if (key === 'per_page' || key === 'page') continue;
            params.push(key + '=' + encodeURIComponent(formData[key]));
        }
        var url = '{{ route("accounting.bank.check-deposit-report.export-excel") }}' + '?' + params.join('&');
        window.location.href = url;
    }
    </script>
    @endpush
</x-layout>
