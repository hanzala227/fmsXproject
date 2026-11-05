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

        .radio-item { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #333; cursor: pointer; }
        .radio-item input[type="radio"], .radio-item input[type="checkbox"] { margin: 0; cursor: pointer; width: 12px; height: 12px; accent-color: #3b82f6; }

        /* Report Table */
        .grid-container { width: 100%; overflow-x: auto; background: #fff; margin-top: 15px; }
        .grid-table { border-collapse: collapse; width: 100%; font-size: 11px; }
        .grid-table th { background: #f8fafc; color: #475569; font-weight: 600; border: 1px solid #e2e8f0; padding: 4px 6px; white-space: nowrap; text-align: left; }
        .grid-table td { padding: 3px 6px; border: 1px solid #e2e8f0; white-space: nowrap; color: #334155; }
        .grid-table td.num { text-align: right; font-family: 'Courier New', monospace; }
        .grid-table tbody tr:hover { background: #f1f5f9; }
        .grid-table .total-row { background: #f8fafc !important; font-weight: 700; }
        .grid-table .total-row td { border-top: 2px solid #475569; color: #0f172a; }
        .grid-table .group-header td { background: #f0f9ff !important; font-weight: 700; color: #1e40af; font-size: 11px; text-transform: uppercase; }
        .grid-table .subgroup-total td { background: #f0f9ff !important; font-weight: 600; color: #1e40af; border-top: 1px solid #93c5fd; }

        /* Balance indicator */
        .balance-indicator { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .balance-indicator.balanced { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .balance-indicator.unbalanced { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* Info box */
        .info-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 12px 16px; margin-top: 15px; font-size: 11px; color: #1e40af; line-height: 1.6; }
        .info-box strong { color: #1e3a8a; }

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

        /* Summary Cards */
        .summary-row { display: flex; gap: 12px; margin-top: 15px; flex-wrap: wrap; }
        .summary-card { flex: 1; min-width: 140px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        .summary-card .label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px; }
        .summary-card .value { font-size: 14px; font-weight: 700; color: #0f172a; font-family: 'Courier New', monospace; }
        .summary-card.debit { border-left: 3px solid #3b82f6; }
        .summary-card.credit { border-left: 3px solid #ef4444; }
        .summary-card.total { border-left: 3px solid #22c55e; }
        .summary-card.count { border-left: 3px solid #8b5cf6; }
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
                <li><i class="fa fa-angle-right"></i>Report</li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">Trial Balance</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-columns" style="color:#3b82f6;margin-right:6px;"></i>Trial Balance</span>
                <div id="balance-badge" style="display:none;"></div>
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
                            <div class="form-label-box">Office</div>
                            <div class="form-input-box">
                                <select id="office_id" class="form-control-gf">
                                    <option value="">All Offices</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
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
                            <div class="form-label-box">Format</div>
                            <div class="form-input-box">
                                <label class="radio-item"><input type="radio" name="format" value="standard" checked> Standard Style</label>
                                <label class="radio-item"><input type="radio" name="format" value="debit_credit"> Display Balance by Debit/Credit</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:4px;">
                    <div class="form-group-row">
                        <div class="form-label-box">More Options</div>
                        <div class="form-input-box">
                            <label class="radio-item"><input type="checkbox" id="group_by_sub" checked> Group by Sub G/L Code</label>
                            <label class="radio-item" style="margin-left:12px;"><input type="checkbox" id="hide_zero"> Hide Zero Balance</label>
                        </div>
                    </div>
                </div>

                <div class="button-row">
                    <button class="btn-action-round primary" onclick="viewReport()"><i class="fa fa-search"></i> Preview</button>
                    <button class="btn-action-round" onclick="printReport()"><i class="fa fa-print"></i> Print</button>
                    <button class="btn-action-round green" onclick="exportExcel()"><i class="fa fa-file-excel-o"></i> Download Excel</button>
                </div>

                {{-- ═══════════════════════ REPORT RESULTS ═══════════════════════ --}}
                <div id="report-results">
                    <div class="empty-state" id="empty-state">
                        <i class="fa fa-columns"></i>
                        <div>Select report options and click <strong>Preview</strong> to generate the Trial Balance.</div>
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

        if (!periodFrom || !periodTo) {
            showToast('error', 'Please select both Period From and Period To dates.');
            return;
        }
        if (periodFrom > periodTo) {
            showToast('error', 'Period To must be after or equal to Period From.');
            return;
        }

        const officeId   = document.getElementById('office_id').value;
        const format     = document.querySelector('input[name="format"]:checked')?.value || 'standard';
        const groupBySub = document.getElementById('group_by_sub').checked;
        const hideZero   = document.getElementById('hide_zero').checked;

        const resultsDiv = document.getElementById('report-results');
        resultsDiv.innerHTML = '<div class="loading-overlay"><i class="fa fa-spinner"></i><div style="margin-top:8px;">Generating Trial Balance...</div></div>';

        const formData = new FormData();
        formData.append('period_from', periodFrom);
        formData.append('period_to', periodTo);
        formData.append('office_id', officeId);
        formData.append('format', format);
        formData.append('group_by_sub', groupBySub ? '1' : '0');
        formData.append('hide_zero', hideZero ? '1' : '0');

        fetch('{{ route("accounting.report.trial-balance.view") }}', {
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
                resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>Failed to generate Trial Balance.</div></div>';
                showToast('error', data.message || 'Failed to generate Trial Balance');
                return;
            }
            renderReport(data);
        })
        .catch(err => {
            console.error(err);
            resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>An error occurred while generating the Trial Balance.</div></div>';
            showToast('error', 'Failed to generate Trial Balance');
        });
    }

    /* ================================================================
       RENDER REPORT TABLE
    ================================================================ */
    var _lastReportData = null;

    function renderReport(data) {
        _lastReportData = data;
        const accounts = data.accounts || [];
        const grouped  = data.grouped || [];
        const summary  = data.summary || {};
        const groupBySub = data.group_by_sub;
        const format     = data.format;

        const resultsDiv = document.getElementById('report-results');

        // Balance badge
        const badge = document.getElementById('balance-badge');
        if (summary.is_balanced) {
            badge.innerHTML = '<span class="balance-indicator balanced"><i class="fa fa-check-circle"></i> Balanced</span>';
        } else {
            badge.innerHTML = '<span class="balance-indicator unbalanced"><i class="fa fa-exclamation-triangle"></i> Unbalanced</span>';
        }
        badge.style.display = 'inline-block';

        if (!accounts.length) {
            resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-inbox"></i><div>No accounts found for the selected criteria.</div></div>';
            return;
        }

        let html = '<div class="grid-container"><table class="grid-table">';

        // Table Header
        const isDC = (format === 'debit_credit');
        html += '<thead><tr>';
        html += '<th>Code</th>';
        html += '<th>Account Name</th>';
        html += '<th>Group</th>';
        if (isDC) {
            html += '<th class="num">Opening (Dr)</th>';
            html += '<th class="num">Opening (Cr)</th>';
            html += '<th class="num">Debit</th>';
            html += '<th class="num">Credit</th>';
            html += '<th class="num">Closing (Dr)</th>';
            html += '<th class="num">Closing (Cr)</th>';
        } else {
            html += '<th class="num">Opening Balance</th>';
            html += '<th class="num">Debit</th>';
            html += '<th class="num">Credit</th>';
            html += '<th class="num">Closing Balance</th>';
        }
        html += '</tr></thead><tbody>';

        if (groupBySub && grouped.length) {
            // Grouped rendering
            grouped.forEach(grp => {
                html += '<tr class="group-header">';
                html += `<td colspan="${isDC ? 9 : 7}">${escapeHtml(grp.group)} / ${escapeHtml(grp.sub_group)}</td>`;
                html += '</tr>';

                grp.accounts.forEach(acc => {
                    html += '<tr>';
                    html += `<td>${escapeHtml(acc.code)}</td>`;
                    html += `<td class="fw-600" style="padding-left:20px;">${escapeHtml(acc.name)}</td>`;
                    html += `<td>${escapeHtml(acc.group)}</td>`;
                    if (isDC) {
                        html += `<td class="num">${acc.opening_debit > 0 ? fmt(acc.opening_debit) : '0.00'}</td>`;
                        html += `<td class="num">${acc.opening_credit > 0 ? fmt(acc.opening_credit) : '0.00'}</td>`;
                        html += `<td class="num" style="color:#3b82f6;">${acc.debit > 0 ? fmt(acc.debit) : '0.00'}</td>`;
                        html += `<td class="num" style="color:#ef4444;">${acc.credit > 0 ? fmt(acc.credit) : '0.00'}</td>`;
                        html += `<td class="num fw-600">${acc.closing_debit > 0 ? fmt(acc.closing_debit) : '0.00'}</td>`;
                        html += `<td class="num fw-600">${acc.closing_credit > 0 ? fmt(acc.closing_credit) : '0.00'}</td>`;
                    } else {
                        html += `<td class="num">${fmt(acc.opening_balance)}</td>`;
                        html += `<td class="num" style="color:#3b82f6;">${acc.debit > 0 ? fmt(acc.debit) : '0.00'}</td>`;
                        html += `<td class="num" style="color:#ef4444;">${acc.credit > 0 ? fmt(acc.credit) : '0.00'}</td>`;
                        html += `<td class="num fw-600">${fmt(acc.closing_balance)}</td>`;
                    }
                    html += '</tr>';
                });

                html += '<tr class="subgroup-total">';
                html += `<td colspan="2">Subtotal ${escapeHtml(grp.sub_group)}</td>`;
                html += `<td></td>`;
                if (isDC) {
                    html += `<td class="num">${fmt(grp.opening_debit)}</td>`;
                    html += `<td class="num">${fmt(grp.opening_credit)}</td>`;
                    html += `<td class="num">${fmt(grp.debit)}</td>`;
                    html += `<td class="num">${fmt(grp.credit)}</td>`;
                    html += `<td class="num">${fmt(grp.closing_debit)}</td>`;
                    html += `<td class="num">${fmt(grp.closing_credit)}</td>`;
                } else {
                    html += `<td class="num">${fmt(grp.opening_balance)}</td>`;
                    html += `<td class="num">${fmt(grp.debit)}</td>`;
                    html += `<td class="num">${fmt(grp.credit)}</td>`;
                    html += `<td class="num">${fmt(grp.closing_balance)}</td>`;
                }
                html += '</tr>';
            });
        } else {
            // Flat rendering
            accounts.forEach(acc => {
                html += '<tr>';
                html += `<td>${escapeHtml(acc.code)}</td>`;
                html += `<td class="fw-600">${escapeHtml(acc.name)}</td>`;
                html += `<td>${escapeHtml(acc.group)}</td>`;
                if (isDC) {
                    html += `<td class="num">${acc.opening_debit > 0 ? fmt(acc.opening_debit) : '0.00'}</td>`;
                    html += `<td class="num">${acc.opening_credit > 0 ? fmt(acc.opening_credit) : '0.00'}</td>`;
                    html += `<td class="num" style="color:#3b82f6;">${acc.debit > 0 ? fmt(acc.debit) : '0.00'}</td>`;
                    html += `<td class="num" style="color:#ef4444;">${acc.credit > 0 ? fmt(acc.credit) : '0.00'}</td>`;
                    html += `<td class="num fw-600">${acc.closing_debit > 0 ? fmt(acc.closing_debit) : '0.00'}</td>`;
                    html += `<td class="num fw-600">${acc.closing_credit > 0 ? fmt(acc.closing_credit) : '0.00'}</td>`;
                } else {
                    html += `<td class="num">${fmt(acc.opening_balance)}</td>`;
                    html += `<td class="num" style="color:#3b82f6;">${acc.debit > 0 ? fmt(acc.debit) : '0.00'}</td>`;
                    html += `<td class="num" style="color:#ef4444;">${acc.credit > 0 ? fmt(acc.credit) : '0.00'}</td>`;
                    html += `<td class="num fw-600">${fmt(acc.closing_balance)}</td>`;
                }
                html += '</tr>';
            });
        }

        // Grand Total Row
        html += '<tr class="total-row">';
        html += '<td colspan="3">GRAND TOTAL</td>';
        if (isDC) {
            html += `<td class="num">${fmt(summary.total_opening_debit)}</td>`;
            html += `<td class="num">${fmt(summary.total_opening_credit)}</td>`;
            html += `<td class="num">${fmt(summary.total_debit)}</td>`;
            html += `<td class="num">${fmt(summary.total_credit)}</td>`;
            html += `<td class="num">${fmt(summary.total_closing_debit)}</td>`;
            html += `<td class="num">${fmt(summary.total_closing_credit)}</td>`;
        } else {
            html += `<td class="num">${fmt(summary.total_opening_balance)}</td>`;
            html += `<td class="num">${fmt(summary.total_debit)}</td>`;
            html += `<td class="num">${fmt(summary.total_credit)}</td>`;
            html += `<td class="num">${fmt(summary.total_closing_balance)}</td>`;
        }
        html += '</tr>';

        html += '</tbody></table></div>';

        // Footer info
        html += `<div style="margin-top:8px;font-size:10px;color:#64748b;text-align:right;">
            Period ${escapeHtml(data.period_from)} to ${escapeHtml(data.period_to)} &middot; ${summary.account_count || 0} account(s) &middot; Generated ${new Date().toLocaleString()}
        </div>`;

        resultsDiv.innerHTML = html;
        showToast('success', 'Trial Balance generated successfully');
    }

    /* ================================================================
       PRINT
    ================================================================ */
    function printReport() {
        const periodFrom = document.getElementById('period_from').value;
        const periodTo   = document.getElementById('period_to').value;

        if (!periodFrom || !periodTo) {
            showToast('error', 'Please select both Period From and Period To dates.');
            return;
        }

        const officeId   = document.getElementById('office_id').value;
        const format     = document.querySelector('input[name="format"]:checked')?.value || 'standard';
        const groupBySub = document.getElementById('group_by_sub').checked;
        const hideZero   = document.getElementById('hide_zero').checked;

        const url = '{{ route("accounting.report.trial-balance.print") }}'
            + '?period_from=' + encodeURIComponent(periodFrom)
            + '&period_to=' + encodeURIComponent(periodTo)
            + '&office_id=' + encodeURIComponent(officeId)
            + '&format=' + encodeURIComponent(format)
            + '&group_by_sub=' + (groupBySub ? '1' : '0')
            + '&hide_zero=' + (hideZero ? '1' : '0');

        window.open(url, '_blank');
    }

    /* ================================================================
       EXPORT EXCEL
    ================================================================ */
    function exportExcel() {
        const periodFrom = document.getElementById('period_from').value;
        const periodTo   = document.getElementById('period_to').value;

        if (!periodFrom || !periodTo) {
            showToast('error', 'Please select both Period From and Period To dates.');
            return;
        }

        const officeId   = document.getElementById('office_id').value;
        const format     = document.querySelector('input[name="format"]:checked')?.value || 'standard';
        const groupBySub = document.getElementById('group_by_sub').checked;
        const hideZero   = document.getElementById('hide_zero').checked;

        const url = '{{ route("accounting.report.trial-balance.export-excel") }}'
            + '?period_from=' + encodeURIComponent(periodFrom)
            + '&period_to=' + encodeURIComponent(periodTo)
            + '&office_id=' + encodeURIComponent(officeId)
            + '&format=' + encodeURIComponent(format)
            + '&group_by_sub=' + (groupBySub ? '1' : '0')
            + '&hide_zero=' + (hideZero ? '1' : '0');

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
    </script>
    @endpush
</x-layout>
