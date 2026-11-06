<x-layout>
    @push('styles')
    <style>
        .page-content { padding: 8px 12px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Inter', 'Open Sans', sans-serif !important; }
        .portlet.light { background-color: #fff; border: 1px solid #cbd5e1; border-radius: 2px; margin-bottom: 10px !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .portlet-title { padding: 4px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; min-height: 28px; background: #f8fafc; }
        .portlet-body { padding: 12px 14px; }
        .caption-subject { color: #1e293b; font-size: 12px; font-weight: 700; text-transform: uppercase; }

        .page-bar { background-color: #fff; padding: 8px 20px; margin-bottom: 15px; border: 1px solid #e9ebec; border-radius: 4px; }
        .page-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
        .page-breadcrumb li { font-size: 12px; color: #888; display: flex; align-items: center; }
        .page-breadcrumb li a { color: #337ab7; text-decoration: none; }
        .page-breadcrumb li i { margin: 0 8px; font-size: 10px; opacity: 0.5; }

        .form-label-box { background: #eef1f5; width: 130px; padding: 4px 8px; font-size: 11px; color: #333; min-height: 24px; display: flex; align-items: center; flex-shrink: 0; font-weight: 600; }
        .form-input-box { flex-grow: 1; padding-left: 10px; display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
        .form-group-row { display: flex; align-items: center; margin-bottom: 8px; }
        .form-control-gf { height: 24px; border: 1px solid #c2cad8; padding: 2px 6px; font-size: 11px; border-radius: 2px !important; width: 100%; max-width: 220px; color: #333; outline: none; background: #fff; }
        .form-control-gf:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
        select.form-control-gf { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 4px center; background-size: 8px; padding-right: 16px; }

        .radio-item { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #333; cursor: pointer; }
        .radio-item input[type="radio"], .radio-item input[type="checkbox"] { margin: 0; cursor: pointer; width: 12px; height: 12px; accent-color: #3b82f6; }

        .report-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 40px; }
        @media (max-width: 900px) { .report-grid { grid-template-columns: 1fr; } }

        .button-row { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
        .btn-action-round { background: #64748b; color: #fff; border: 1px solid #475569; border-radius: 2px; padding: 0 8px; height: 20px; font-size: 10px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; text-decoration: none; transition: all 0.15s; box-sizing: border-box; }
        .btn-action-round:hover { background: #475569; color: #fff; }
        .btn-action-round.primary { background: #3b82f6; border-color: #2563eb; }
        .btn-action-round.primary:hover { background: #2563eb; }
        .btn-action-round.green { background: #22c55e; border-color: #16a34a; }
        .btn-action-round.green:hover { background: #16a34a; }
        .btn-action-round:disabled { opacity: 0.5; cursor: not-allowed; }

        .text-danger { color: #e73d4a; }
        .text-muted { color: #94a3b8; }
        .fw-600 { font-weight: 600; }

        /* Report Table */
        .grid-container { width: 100%; overflow-x: auto; background: #fff; margin-top: 15px; }
        .grid-table { border-collapse: collapse; width: 100%; font-size: 11px; }
        .grid-table th { background: #f8fafc; color: #475569; font-weight: 600; border: 1px solid #e2e8f0; padding: 4px 6px; white-space: nowrap; text-align: left; }
        .grid-table td { padding: 3px 6px; border: 1px solid #e2e8f0; white-space: nowrap; color: #334155; }
        .grid-table td.num { text-align: right; font-family: 'Courier New', monospace; }
        .grid-table tbody tr:hover { background: #f1f5f9; }
        .grid-table .total-row { background: #f8fafc !important; font-weight: 700; }
        .grid-table .total-row td { border-top: 2px solid #475569; color: #0f172a; }
        .grid-table .subtotal-row { background: #f0f9ff !important; font-weight: 600; }
        .grid-table .subtotal-row td { border-top: 1px solid #93c5fd; color: #1e40af; }

        .badge-status { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-active { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-inactive { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* Empty State */
        .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; }
        .empty-state i { font-size: 36px; display: block; margin-bottom: 12px; }

        /* Loading */
        .loading-overlay { text-align: center; padding: 30px; color: #64748b; }
        .loading-overlay i { font-size: 20px; animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Toast */
        .toast-container { position: fixed; top: 56px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 6px; pointer-events: none; }
        .toast { background: #1e293b; color: #fff; padding: 8px 14px; border-radius: 4px; font-size: 11px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 8px; animation: toastIn 0.25s ease; pointer-events: all; }
        .toast.success { border-left: 3px solid #22c55e; }
        .toast.error   { border-left: 3px solid #ef4444; }
        .toast.info    { border-left: 3px solid #3b82f6; }
        @keyframes toastIn { from { transform: translateX(40px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .btn-tool { background: #fff; border: 1px solid #cbd5e1; padding: 2px 8px; font-size: 10px; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; height: 22px; border-radius: 2px; transition: all 0.15s; }
        .btn-tool:hover { background: #f1f5f9; border-color: #94a3b8; }
    </style>
    @endpush

    {{-- ═══════════════════════ TOAST CONTAINER ═══════════════════════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════════════════ MAIN PAGE ═══════════════════════ --}}
    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Bank</li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">Bank Book Balance</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-balance-scale" style="color:#3b82f6;margin-right:6px;"></i>Bank Book Balance Report</span>
            </div>
            <div class="portlet-body">
                <div class="report-grid">
                    {{-- LEFT COLUMN --}}
                    <div>
                        <div class="form-group-row">
                            <div class="form-label-box"><span class="text-danger">*</span>Period From</div>
                            <div class="form-input-box">
                                <input type="date" id="period_from" class="form-control-gf" value="{{ date('Y-m-01') }}">
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">Bank</div>
                            <div class="form-input-box">
                                <select id="bank_account_id" class="form-control-gf" style="max-width:220px;">
                                    <option value="">All Banks</option>
                                    @foreach($bankAccounts as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->bank_name ?? $bank->name }}{{ !empty($bank->gl_no) ? ' (' . $bank->gl_no . ')' : (!empty($bank->account_no) ? ' (' . $bank->account_no . ')' : '') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">Status</div>
                            <div class="form-input-box">
                                <label class="radio-item"><input type="radio" name="status" value="all" checked> All</label>
                                <label class="radio-item"><input type="radio" name="status" value="active"> Active</label>
                                <label class="radio-item"><input type="radio" name="status" value="inactive"> Inactive</label>
                            </div>
                        </div>
                    </div>
                    {{-- RIGHT COLUMN --}}
                    <div>
                        <div class="form-group-row">
                            <div class="form-label-box"><span class="text-danger">*</span>Period To</div>
                            <div class="form-input-box">
                                <input type="date" id="period_to" class="form-control-gf" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">Type</div>
                            <div class="form-input-box">
                                <label class="radio-item"><input type="radio" name="type" value="all"> All</label>
                                <label class="radio-item"><input type="radio" name="type" value="Bank" checked> Bank</label>
                                <label class="radio-item"><input type="radio" name="type" value="Book"> Book</label>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">Report Type</div>
                            <div class="form-input-box">
                                <label class="radio-item"><input type="radio" name="report_type" value="Summary" checked> Summary</label>
                                <label class="radio-item"><input type="radio" name="report_type" value="Detail"> Detail</label>
                                <label class="radio-item" style="margin-left:8px;"><input type="checkbox" id="hide_subtotal"> Hide Subtotal</label>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">Currency</div>
                            <div class="form-input-box">
                                <label class="radio-item"><input type="radio" name="currency" value="bank_currency" checked> Bank Currency</label>
                                <label class="radio-item"><input type="radio" name="currency" value="main_currency"> Main Currency</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-row">
                    <button class="btn-action-round primary" onclick="viewReport()" id="btnViewReport"><i class="fa fa-search"></i> Search / View</button>
                    <button class="btn-action-round" onclick="printReport()"><i class="fa fa-print"></i> Print</button>
                    <button class="btn-action-round green" onclick="exportExcel()"><i class="fa fa-file-excel-o"></i> Download Excel</button>
                </div>

                {{-- ═══════════════════════ REPORT RESULTS ═══════════════════════ --}}
                <div id="report-results">
                    <div class="empty-state" id="empty-state">
                        <i class="fa fa-line-chart"></i>
                        <div>Select report options and click <strong>View</strong> to generate the Bank Book Balance report.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    /* ================================================================
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `<i class="fa fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }

    /* ================================================================
       FORMAT NUMBER
    ================================================================ */
    function fmt(val) {
        if (val === undefined || val === null) return '0.00';
        return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /* ================================================================
       VIEW REPORT — AJAX
    ================================================================ */
    function viewReport() {
        const periodFrom = document.getElementById('period_from').value;
        const periodTo   = document.getElementById('period_to').value;
        const currencyVal   = document.querySelector('input[name="currency"]:checked')?.value;
        const reportTypeVal = document.querySelector('input[name="report_type"]:checked')?.value;

        // Validate
        if (!periodFrom || !periodTo) {
            showToast('error', 'Please select both Period From and Period To dates.');
            return;
        }
        if (periodFrom > periodTo) {
            showToast('error', 'Period To must be after or equal to Period From.');
            return;
        }
        if (!currencyVal) {
            showToast('error', 'Please select a currency option.');
            return;
        }
        if (!reportTypeVal) {
            showToast('error', 'Please select a report type (Summary or Detail).');
            return;
        }

        const status      = document.querySelector('input[name="status"]:checked')?.value || 'all';
        const type        = document.querySelector('input[name="type"]:checked')?.value || 'Bank';
        const hideSubtotal = document.getElementById('hide_subtotal').checked;
        const bankId      = document.getElementById('bank_account_id').value;

        const resultsDiv = document.getElementById('report-results');
        resultsDiv.innerHTML = '<div class="loading-overlay"><i class="fa fa-spinner"></i><div style="margin-top:8px;">Generating report...</div></div>';

        const formData = new FormData();
        formData.append('period_from', periodFrom);
        formData.append('period_to', periodTo);
        formData.append('bank_account_id', bankId);
        formData.append('status', status);
        formData.append('type', type);
        formData.append('report_type', reportTypeVal);
        formData.append('hide_subtotal', hideSubtotal ? '1' : '0');
        formData.append('currency', currencyVal);

        fetch('{{ route("accounting.bank.book-balance.view") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>Failed to generate report.</div></div>';
                showToast('error', data.message || 'Failed to generate report');
                return;
            }
            renderReport(data);
        })
        .catch(err => {
            console.error(err);
            resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>An error occurred while generating the report.</div></div>';
            showToast('error', 'Failed to generate report');
        });
    }

    /* ================================================================
       RENDER REPORT TABLE
    ================================================================ */
    var _lastReportData = null;

    function renderReport(data) {
        _lastReportData = data;
        const rows = data.rows || [];
        const totals = data.totals || {};
        const hideSubtotal = data.hide_subtotal;
        const reportType = data.report_type;

        const resultsDiv = document.getElementById('report-results');

        if (!rows.length) {
            resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-inbox"></i><div>No records found for the selected criteria.</div></div>';
            return;
        }

        let html = '<div class="grid-container"><table class="grid-table">';
        
        // Table Header
        html += '<thead><tr>';
        html += '<th>Bank Name</th>';
        html += '<th>Account No.</th>';
        html += '<th>Currency</th>';
        html += '<th class="num">Opening Balance</th>';
        html += '<th class="num">Receipts</th>';
        html += '<th class="num">Payments</th>';
        html += '<th class="num">Closing Balance</th>';
        html += '<th class="num">Book Balance</th>';
        html += '<th class="num">Difference</th>';
        html += '<th>Status</th>';
        html += '</tr></thead><tbody>';

        // Data Rows
        let hasSubtotals = false;

        rows.forEach((row) => {
            if (row.is_subtotal) {
                hasSubtotals = true;
                html += '<tr class="subtotal-row">';
                html += `<td colspan="3" style="text-align:right;font-style:italic;">${escapeHtml(row.subtotal_label)}</td>`;
                html += `<td class="num">${fmt(row.opening_balance)}</td>`;
                html += `<td class="num">${fmt(row.receipts)}</td>`;
                html += `<td class="num">${fmt(row.payments)}</td>`;
                html += `<td class="num">${fmt(row.closing_balance)}</td>`;
                html += `<td class="num">${fmt(row.book_balance)}</td>`;
                const diff = parseFloat(row.difference);
                html += `<td class="num" style="color:${diff < 0 ? '#dc2626' : diff > 0 ? '#16a34a' : '#475569'};">${fmt(row.difference)}</td>`;
                html += '<td></td>';
                html += '</tr>';
                return;
            }

            html += '<tr>';
            html += `<td class="fw-600">${escapeHtml(row.name)}</td>`;
            html += `<td>${escapeHtml(row.account_no)}</td>`;
            html += `<td>${escapeHtml(row.currency)}</td>`;
            html += `<td class="num">${fmt(row.opening_balance)}</td>`;
            html += `<td class="num" style="color:#16a34a;">${fmt(row.receipts)}</td>`;
            html += `<td class="num" style="color:#dc2626;">${fmt(row.payments)}</td>`;
            html += `<td class="num fw-600">${fmt(row.closing_balance)}</td>`;
            html += `<td class="num">${fmt(row.book_balance)}</td>`;
            const diff = parseFloat(row.difference);
            html += `<td class="num" style="color:${diff < 0 ? '#dc2626' : diff > 0 ? '#16a34a' : '#475569'};">${fmt(row.difference)}</td>`;
            html += `<td><span class="badge-status ${row.status === 'active' ? 'badge-active' : 'badge-inactive'}">${row.status}</span></td>`;
            html += '</tr>';

            // Detail transactions for this bank
            if (reportType === 'Detail' && row.transactions && row.transactions.length) {
                row.transactions.forEach(txn => {
                    html += '<tr style="background:#f8fafc;border-left:3px solid #3b82f6;">';
                    html += `<td style="padding-left:20px;font-size:10px;color:#64748b;">&nbsp;&nbsp;&nbsp;${escapeHtml(txn.date)}</td>`;
                    html += `<td colspan="2" style="font-size:10px;color:#334155;">${escapeHtml(txn.description)}</td>`;
                    html += `<td class="num" style="font-size:10px;color:#94a3b8;">--</td>`;
                    html += `<td class="num" style="font-size:10px;color:#16a34a;">${txn.debit > 0 ? fmt(txn.debit) : ''}</td>`;
                    html += `<td class="num" style="font-size:10px;color:#dc2626;">${txn.credit > 0 ? fmt(txn.credit) : ''}</td>`;
                    html += `<td class="num fw-600" style="font-size:10px;">${fmt(txn.balance)}</td>`;
                    html += '<td colspan="3"></td>';
                    html += '</tr>';
                });
            }
        });

        // Totals Row
        html += '<tr class="total-row">';
        html += '<td colspan="3">GRAND TOTAL</td>';
        html += `<td class="num">${fmt(totals.opening_balance)}</td>`;
        html += `<td class="num">${fmt(totals.receipts)}</td>`;
        html += `<td class="num">${fmt(totals.payments)}</td>`;
        html += `<td class="num">${fmt(totals.closing_balance)}</td>`;
        html += `<td class="num">${fmt(totals.book_balance)}</td>`;
        const totalDiff = parseFloat(totals.difference);
        html += `<td class="num" style="color:${totalDiff < 0 ? '#dc2626' : totalDiff > 0 ? '#16a34a' : '#475569'};">${fmt(totals.difference)}</td>`;
        html += '<td></td>';
        html += '</tr>';

        html += '</tbody></table></div>';

        // Summary info
        html += `<div style="margin-top:8px;font-size:10px;color:#64748b;text-align:right;">
            Report: ${data.report_type} &middot; ${rows.length} bank account(s) &middot; Generated ${new Date().toLocaleString()}
        </div>`;

        resultsDiv.innerHTML = html;
        showToast('success', 'Report generated successfully');
    }

    /* ================================================================
       PRINT
    ================================================================ */
    function printReport() {
        const periodFrom = document.getElementById('period_from').value;
        const periodTo   = document.getElementById('period_to').value;
        const bankId     = document.getElementById('bank_account_id').value;
        const status     = document.querySelector('input[name="status"]:checked')?.value || 'all';
        const type       = document.querySelector('input[name="type"]:checked')?.value || 'Bank';
        const reportType = document.querySelector('input[name="report_type"]:checked')?.value || 'Summary';
        const hideSubtotal = document.getElementById('hide_subtotal').checked;
        const currency   = document.querySelector('input[name="currency"]:checked')?.value || 'bank_currency';

        const url = '{{ route("accounting.bank.book-balance.print") }}'
            + '?period_from=' + encodeURIComponent(periodFrom)
            + '&period_to=' + encodeURIComponent(periodTo)
            + '&bank_account_id=' + encodeURIComponent(bankId)
            + '&status=' + encodeURIComponent(status)
            + '&type=' + encodeURIComponent(type)
            + '&report_type=' + encodeURIComponent(reportType)
            + '&hide_subtotal=' + (hideSubtotal ? '1' : '0')
            + '&currency=' + encodeURIComponent(currency);

        window.open(url, '_blank');
    }

    /* ================================================================
       EXPORT EXCEL
    ================================================================ */
    function exportExcel() {
        const periodFrom = document.getElementById('period_from').value;
        const periodTo   = document.getElementById('period_to').value;
        const bankId     = document.getElementById('bank_account_id').value;
        const status     = document.querySelector('input[name="status"]:checked')?.value || 'all';
        const type       = document.querySelector('input[name="type"]:checked')?.value || 'Bank';
        const reportType = document.querySelector('input[name="report_type"]:checked')?.value || 'Summary';
        const hideSubtotal = document.getElementById('hide_subtotal').checked;
        const currency   = document.querySelector('input[name="currency"]:checked')?.value || 'bank_currency';

        const url = '{{ route("accounting.bank.book-balance.export-excel") }}'
            + '?period_from=' + encodeURIComponent(periodFrom)
            + '&period_to=' + encodeURIComponent(periodTo)
            + '&bank_account_id=' + encodeURIComponent(bankId)
            + '&status=' + encodeURIComponent(status)
            + '&type=' + encodeURIComponent(type)
            + '&report_type=' + encodeURIComponent(reportType)
            + '&hide_subtotal=' + (hideSubtotal ? '1' : '0')
            + '&currency=' + encodeURIComponent(currency);

        window.location.href = url;
    }

    /* ================================================================
       ESCAPE HTML
    ================================================================ */
    function escapeHtml(text) {
        if (!text) return '--';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
        viewReport();
    });
    </script>
    @endpush
</x-layout>
