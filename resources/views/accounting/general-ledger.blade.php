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

        /* Report Table */
        .grid-container { width: 100%; overflow-x: auto; background: #fff; margin-top: 15px; }
        .grid-table { border-collapse: collapse; width: 100%; font-size: 11px; }
        .grid-table th { background: #f8fafc; color: #475569; font-weight: 600; border: 1px solid #e2e8f0; padding: 4px 6px; white-space: nowrap; text-align: left; }
        .grid-table td { padding: 3px 6px; border: 1px solid #e2e8f0; white-space: nowrap; color: #334155; }
        .grid-table td.num { text-align: right; font-family: 'Courier New', monospace; }
        .grid-table tbody tr:hover { background: #f1f5f9; }
        .grid-table .total-row { background: #f8fafc !important; font-weight: 700; }
        .grid-table .total-row td { border-top: 2px solid #475569; color: #0f172a; }
        .grid-table .account-header td { background: #eff6ff !important; font-weight: 700; color: #1e40af; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #93c5fd; }
        .grid-table .account-header td:first-child { padding-left: 6px; }
        .grid-table .opening-row td { background: #f0f9ff !important; color: #1e40af; font-weight: 600; font-style: italic; border-bottom: 1px solid #bfdbfe; }
        .grid-table .closing-row td { background: #f0f9ff !important; font-weight: 700; color: #1e40af; border-top: 2px solid #93c5fd; }
        .grid-table .txn-row td:first-child { padding-left: 20px; }

        /* Summary Cards */
        .summary-row { display: flex; gap: 12px; margin-top: 15px; flex-wrap: wrap; }
        .summary-card { flex: 1; min-width: 140px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        .summary-card .label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px; }
        .summary-card .value { font-size: 14px; font-weight: 700; color: #0f172a; font-family: 'Courier New', monospace; }
        .summary-card.debit { border-left: 3px solid #3b82f6; }
        .summary-card.credit { border-left: 3px solid #ef4444; }
        .summary-card.balanced { border-left: 3px solid #22c55e; }
        .summary-card.txns { border-left: 3px solid #8b5cf6; }

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

        /* Balance indicator */
        .balance-indicator { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .balance-indicator.balanced { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .balance-indicator.unbalanced { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Report</li>
                <li><i class="fa fa-angle-right"></i><span style="color: #333; font-weight: 700;">General Ledger Report</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject"><i class="fa fa-book" style="color:#3b82f6;margin-right:6px;"></i>General Ledger Report</span>
                <div id="balance-badge" style="display:none;"></div>
            </div>
            <div class="portlet-body">
                <div class="report-grid">
                    {{-- LEFT COLUMN --}}
                    <div>
                        <div class="form-group-row">
                            <div class="form-label-box">Report Type</div>
                            <div class="form-input-box">
                                <select id="report_type" class="form-control-gf">
                                    <option value="summary">Summary</option>
                                    <option value="detail">Detail</option>
                                    <option value="trade_partner">Customer Summary</option>
                                    <option value="ga_expense">G&A Expense</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">G/L No. From</div>
                            <div class="form-input-box">
                                <select id="gl_from" class="form-control-gf">
                                    <option value="">-- All --</option>
                                    @foreach($glAccounts as $acc)
                                        <option value="{{ $acc['code'] }}">{{ $acc['code'] }} - {{ $acc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">Period From</div>
                            <div class="form-input-box">
                                <input type="date" id="period_from" class="form-control-gf" value="{{ date('Y-m-d', strtotime('first day of this month')) }}">
                            </div>
                        </div>
                    </div>
                    {{-- RIGHT COLUMN --}}
                    <div>
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
                        <div class="form-group-row">
                            <div class="form-label-box">G/L No. To</div>
                            <div class="form-input-box">
                                <select id="gl_to" class="form-control-gf">
                                    <option value="">-- All --</option>
                                    @foreach($glAccounts as $acc)
                                        <option value="{{ $acc['code'] }}">{{ $acc['code'] }} - {{ $acc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <div class="form-label-box">Period To</div>
                            <div class="form-input-box">
                                <input type="date" id="period_to" class="form-control-gf" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-row">
                    <button class="btn-action-round primary" onclick="viewReport()"><i class="fa fa-search"></i> Preview</button>
                    <button class="btn-action-round" onclick="printReport()"><i class="fa fa-print"></i> Print</button>
                    <button class="btn-action-round green" onclick="exportExcel()"><i class="fa fa-file-excel-o"></i> Download Excel</button>
                </div>

                <div id="report-results">
                    <div class="empty-state" id="empty-state">
                        <i class="fa fa-book"></i>
                        <div>Select report options and click <strong>Preview</strong> to generate the General Ledger Report.</div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() { t.remove(); }, 3000);
    }

    function fmt(val) {
        if (val === undefined || val === null) return '0.00';
        return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function viewReport() {
        var periodFrom = document.getElementById('period_from').value;
        var periodTo   = document.getElementById('period_to').value;
        var officeId   = document.getElementById('office_id').value;
        var reportType = document.getElementById('report_type').value;
        var glFrom     = document.getElementById('gl_from').value;
        var glTo       = document.getElementById('gl_to').value;

        if (!periodFrom || !periodTo) {
            showToast('error', 'Please select both Period From and Period To dates.');
            return;
        }

        var resultsDiv = document.getElementById('report-results');
        resultsDiv.innerHTML = '<div class="loading-overlay"><i class="fa fa-spinner"></i><div style="margin-top:8px;">Generating General Ledger Report...</div></div>';

        var formData = new FormData();
        formData.append('period_from', periodFrom);
        formData.append('period_to', periodTo);
        formData.append('office_id', officeId);
        formData.append('report_type', reportType);
        formData.append('gl_from', glFrom);
        formData.append('gl_to', glTo);

        fetch('{{ route("accounting.report.general-ledger.view") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>Failed to generate General Ledger Report.</div></div>';
                showToast('error', data.message || 'Failed to generate General Ledger Report');
                return;
            }
            renderReport(data);
        })
        .catch(function(err) {
            console.error(err);
            resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-triangle" style="color:#ef4444;"></i><div>An error occurred while generating the General Ledger Report.</div></div>';
            showToast('error', 'Failed to generate General Ledger Report');
        });
    }

    function renderReport(data) {
        var accounts = data.accounts || [];
        var summary  = data.summary || {};
        var resultsDiv = document.getElementById('report-results');

        // Balance badge
        var badge = document.getElementById('balance-badge');
        if (summary.is_balanced) {
            badge.innerHTML = '<span class="balance-indicator balanced"><i class="fa fa-check-circle"></i> Balanced</span>';
        } else {
            badge.innerHTML = '<span class="balance-indicator unbalanced"><i class="fa fa-exclamation-triangle"></i> Unbalanced</span>';
        }
        badge.style.display = 'inline-block';

        if (!accounts.length) {
            resultsDiv.innerHTML = '<div class="empty-state"><i class="fa fa-inbox"></i><div>No data found for the selected criteria.</div></div>';
            return;
        }

        var html = '<div class="grid-container"><table class="grid-table">';

        html += '<thead><tr>';
        html += '<th style="width:14%;">Date</th>';
        html += '<th style="width:12%;">Reference</th>';
        html += '<th style="width:30%;">Description</th>';
        html += '<th style="width:14%;text-align:right;">Debit</th>';
        html += '<th style="width:14%;text-align:right;">Credit</th>';
        html += '<th style="width:16%;text-align:right;">Balance</th>';
        html += '</tr></thead><tbody>';

        accounts.forEach(function(account) {
            // Account Header
            html += '<tr class="account-header"><td colspan="6">[' + escapeHtml(account.code) + '] ' + escapeHtml(account.name) + '</td></tr>';

            // Opening Balance
            html += '<tr class="opening-row"><td colspan="3">Opening Balance</td><td class="num">' + fmt(account.opening_balance) + '</td><td></td><td class="num">' + fmt(account.opening_balance) + '</td></tr>';

            if (!account.transactions || account.transactions.length === 0) {
                html += '<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:10px;font-style:italic;">No transactions in this period</td></tr>';
            } else {
                account.transactions.forEach(function(txn) {
                    html += '<tr class="txn-row">';
                    html += '<td>' + escapeHtml(txn.date) + '</td>';
                    html += '<td>' + escapeHtml(txn.reference) + '</td>';
                    html += '<td>' + escapeHtml(txn.description) + '</td>';
                    html += '<td class="num">' + (txn.debit > 0 ? fmt(txn.debit) : '') + '</td>';
                    html += '<td class="num">' + (txn.credit > 0 ? fmt(txn.credit) : '') + '</td>';
                    html += '<td class="num">' + fmt(txn.running_balance) + '</td>';
                    html += '</tr>';
                });
            }

            // Account Total
            html += '<tr class="closing-row">';
            html += '<td colspan="3">Closing Balance</td>';
            html += '<td class="num">' + fmt(account.total_debit) + '</td>';
            html += '<td class="num">' + fmt(account.total_credit) + '</td>';
            html += '<td class="num">' + fmt(account.closing_balance) + '</td>';
            html += '</tr>';

            // Spacer
            html += '<tr><td colspan="6" style="padding:0;height:8px;border:none;"></td></tr>';
        });

        // Grand Total
        html += '<tr class="total-row"><td colspan="3">GRAND TOTAL</td>';
        html += '<td class="num">' + fmt(summary.grand_total_debit || 0) + '</td>';
        html += '<td class="num">' + fmt(summary.grand_total_credit || 0) + '</td>';
        html += '<td class="num">' + fmt(summary.grand_closing || 0) + '</td></tr>';

        html += '</tbody></table></div>';

        // Summary cards
        html += '<div class="summary-row">';
        html += '<div class="summary-card debit"><div class="label">Total Debit</div><div class="value">' + fmt(summary.grand_total_debit || 0) + '</div></div>';
        html += '<div class="summary-card credit"><div class="label">Total Credit</div><div class="value">' + fmt(summary.grand_total_credit || 0) + '</div></div>';
        html += '<div class="summary-card balanced"><div class="label">Is Balanced</div><div class="value">' + (summary.is_balanced ? 'Yes' : 'No') + '</div></div>';
        html += '<div class="summary-card txns"><div class="label">Total Transactions</div><div class="value">' + (summary.total_transactions || 0) + '</div></div>';
        html += '</div>';

        html += '<div style="margin-top:8px;font-size:10px;color:#64748b;text-align:right;">';
        html += 'Period: ' + escapeHtml(data.period_from) + ' to ' + escapeHtml(data.period_to) + ' &middot; Generated ' + new Date().toLocaleString();
        html += '</div>';

        resultsDiv.innerHTML = html;
        showToast('success', 'General Ledger Report generated successfully');
    }

    function printReport() {
        var periodFrom = document.getElementById('period_from').value;
        var periodTo   = document.getElementById('period_to').value;
        var officeId   = document.getElementById('office_id').value;
        var reportType = document.getElementById('report_type').value;
        var glFrom     = document.getElementById('gl_from').value;
        var glTo       = document.getElementById('gl_to').value;

        if (!periodFrom || !periodTo) {
            showToast('error', 'Please select both Period From and Period To dates.');
            return;
        }

        var url = '{{ route("accounting.report.general-ledger.print") }}'
            + '?period_from=' + encodeURIComponent(periodFrom)
            + '&period_to=' + encodeURIComponent(periodTo)
            + '&office_id=' + encodeURIComponent(officeId)
            + '&report_type=' + encodeURIComponent(reportType)
            + '&gl_from=' + encodeURIComponent(glFrom)
            + '&gl_to=' + encodeURIComponent(glTo);

        window.open(url, '_blank');
    }

    function exportExcel() {
        var periodFrom = document.getElementById('period_from').value;
        var periodTo   = document.getElementById('period_to').value;
        var officeId   = document.getElementById('office_id').value;
        var reportType = document.getElementById('report_type').value;
        var glFrom     = document.getElementById('gl_from').value;
        var glTo       = document.getElementById('gl_to').value;

        if (!periodFrom || !periodTo) {
            showToast('error', 'Please select both Period From and Period To dates.');
            return;
        }

        var url = '{{ route("accounting.report.general-ledger.export-excel") }}'
            + '?period_from=' + encodeURIComponent(periodFrom)
            + '&period_to=' + encodeURIComponent(periodTo)
            + '&office_id=' + encodeURIComponent(officeId)
            + '&report_type=' + encodeURIComponent(reportType)
            + '&gl_from=' + encodeURIComponent(glFrom)
            + '&gl_to=' + encodeURIComponent(glTo);

        window.location.href = url;
    }
    </script>
    @endpush
</x-layout>
