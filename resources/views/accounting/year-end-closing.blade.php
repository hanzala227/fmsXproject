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
        .fi{height:22px;border:1px solid #c2cad8;padding:0 6px;font-size:11px;border-radius:2px;color:#333;background:#fff;outline:none;box-sizing:border-box}
        .fi:focus{border-color:#3b82f6}
        .radio-group{display:flex;align-items:center;gap:12px}
        .radio-group label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center}
        .radio-group input[type="radio"]{accent-color:#3b82f6;margin-right:2px;vertical-align:middle}
        .btn-blue{background:#3b82f6;color:#fff;border:1px solid #2563eb;border-radius:2px;padding:0 14px;height:22px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:4px;box-sizing:border-box}
        .btn-blue:hover{background:#2563eb;color:#fff}

        .status-msg{background:#e8f5e9;border:1px solid #a5d6a7;border-radius:2px;padding:8px 12px;font-size:11px;color:#2e7d32;margin-bottom:10px}
        .warn-msg{background:#fff3e0;border:1px solid #ffcc80;border-radius:2px;padding:8px 12px;font-size:11px;color:#e65100;margin-top:8px}

        .log-card{margin-top:14px;padding:10px 14px}
        .log-card h3{font-size:12px;font-weight:700;color:#1e293b;margin:0 0 10px;padding-bottom:4px;border-bottom:1px solid #e2e8f0;text-transform:uppercase}

        .timeline{position:relative;padding-left:28px}
        .timeline::before{content:'';position:absolute;left:10px;top:4px;bottom:4px;width:1px;background:#cbd5e1}
        .tl-item{position:relative;margin-bottom:14px}
        .tl-icon{position:absolute;left:-22px;top:2px;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff;z-index:1}
        .tl-icon.icon-d{background:#e74c3c}
        .tl-icon.icon-j{background:#3b82f6}
        .tl-icon.icon-g{background:#22c55e}
        .tl-date{font-size:10px;color:#94a3b8;margin-bottom:2px}
        .tl-action{font-size:11px;color:#1e293b;font-weight:500}
        .tl-user{font-size:10px;color:#64748b;margin-top:1px}
        .tl-detail{font-size:9px;color:#3b82f6;cursor:pointer;text-decoration:none}
        .tl-detail:hover{text-decoration:underline}

        .uncleared-table{width:100%;border-collapse:collapse;margin-top:8px;font-size:10px}
        .uncleared-table th{background:#f8fafc;color:#475569;font-weight:600;border:1px solid #cbd5e1;padding:4px 8px;text-align:left}
        .uncleared-table td{border:1px solid #e2e8f0;padding:4px 8px;color:#334155}

        .toast-container{position:fixed;top:16px;right:16px;z-index:10000;display:flex;flex-direction:column;gap:8px}
        .toast{padding:10px 16px;border-radius:4px;font-size:11px;font-weight:600;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:slideIn .3s ease}
        .toast.info{background:#3b82f6}
        .toast.success{background:#22c55e}
        .toast.error{background:#e73d4a}
        @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        .loading-overlay{display:none;position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:9999;justify-content:center;align-items:center}
        .loading-overlay.active{display:flex}
        .loading-spinner{width:36px;height:36px;border:3px solid #e2e8f0;border-top-color:#3b82f6;border-radius:50%;animation:spin .8s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .dot-loading{display:inline-flex;gap:4px;align-items:center;margin-left:6px}
        .dot-loading span{width:5px;height:5px;border-radius:50%;background:#3b82f6;animation:dotPulse 1s ease-in-out infinite}
        .dot-loading span:nth-child(2){animation-delay:.2s}
        .dot-loading span:nth-child(3){animation-delay:.4s}
        @keyframes dotPulse{0%,100%{opacity:.3}50%{opacity:1}}
        .uncleared-section{display:none;margin-top:12px}
        .uncleared-section.active{display:block}
    </style>
    @endpush

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Journal</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Year End Closing</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject">Year-End Closing</span>
            </div>
            <div class="portlet-body">
                <div class="status-msg" id="statusMsg">
                    <i class="fa fa-info-circle"></i>
                    @if($lastClosing)
                        Last Year-End Closing: {{ $lastClosing->fiscal_year }} on {{ $lastClosing->created_at->format('m-d-Y') }}
                    @else
                        No Year-End Closing Has Been Done Before.
                    @endif
                </div>

                <div class="radio-group" style="margin-bottom:6px;">
                    @if($canPerform)
                    <label><input type="radio" name="action_type" value="perform" checked onchange="toggleAction()"> Perform {{ $performYear }} Year-End Closing</label>
                    @endif
                    @if($canCancel)
                    <label><input type="radio" name="action_type" value="cancel" onchange="toggleAction()"> Cancel Year-End Closing</label>
                    @endif
                </div>

                <div id="dateRange" style="font-size:11px;color:#64748b;margin-bottom:8px;">
                    <span id="dateRangeText">{{ $performYear ? '01-01-'.$performYear.' - 12-31-'.$performYear : '' }}</span>
                </div>

                <button type="button" class="btn-blue" id="btnApply"><i class="fa fa-check"></i> Apply</button>
                <div class="dot-loading" id="dotLoading"><span></span><span></span><span></span></div>

                <div class="uncleared-section" id="unclearedSection">
                    <div class="warn-msg" id="unclearedMsg"></div>
                    <table class="uncleared-table" id="unclearedTable">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Post Date</th>
                                <th>Payable To</th>
                                <th>Check No.</th>
                                <th>Bank</th>
                                <th>Paid Amount</th>
                                <th>Clear/Deposit Date</th>
                            </tr>
                        </thead>
                        <tbody id="unclearedBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject">Change Log</span>
            </div>
            <div class="portlet-body">
                @if($changeLog->count() > 0)
                <div class="timeline">
                    @foreach($changeLog as $log)
                    @php
                        $icon = $log->action === 'CANCEL' ? 'D' : ($log->action === 'CLOSE' ? 'J' : 'G');
                        $iconClass = $log->action === 'CANCEL' ? 'icon-d' : ($log->action === 'CLOSE' ? 'icon-j' : 'icon-g');
                    @endphp
                    <div class="tl-item">
                        <div class="tl-icon {{ $iconClass }}">{{ $icon }}</div>
                        <div class="tl-date">{{ $log->created_at->format('m-d-Y H:i') }}</div>
                        <div class="tl-action">{{ $log->action === 'CANCEL' ? 'Cancel' : 'Perform' }} Year-End Closing - {{ $log->closing_date->format('m-d-Y') }}</div>
                        <div class="tl-user">{{ $log->creator?->name ?? 'Unknown' }} ({{ $log->creator?->email ?? '' }})</div>
                        <a class="tl-detail" onclick="showDetail({{ $log->id }})">More Detail</a>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="text-align:center;color:#94a3b8;padding:16px;font-size:11px;">No year-end closing records found.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>
    <div class="loading-overlay" id="loadingOverlay"><div class="loading-spinner"></div></div>

    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function $(sel) { return document.querySelector(sel); }

    function toast(type, msg) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.textContent = msg;
        c.appendChild(t);
        setTimeout(function(){ t.remove(); }, 4000);
        return t;
    }

    var infoToast = null;

    function showLoading() { document.getElementById('loadingOverlay').classList.add('active'); }
    function hideLoading() { document.getElementById('loadingOverlay').classList.remove('active'); }
    function showDots() { document.getElementById('dotLoading').style.display = 'inline-flex'; }
    function hideDots() { document.getElementById('dotLoading').style.display = 'none'; }

    function toggleAction() {
        var action = document.querySelector('input[name="action_type"]:checked')?.value;
        var performYear = {{ $performYear ?? 0 }};
        var cancelYear = {{ $cancelYear ?? 0 }};
        var year = action === 'cancel' ? cancelYear : performYear;
        document.getElementById('dateRangeText').textContent = '01-01-' + year + ' - 12-31-' + year;
        document.getElementById('unclearedSection').classList.remove('active');
    }

    function formatUsDate(dateStr) {
        if (!dateStr) return '';
        var parts = dateStr.split('-');
        return parts[1] + '-' + parts[2] + '-' + parts[0];
    }

    $('#btnApply').addEventListener('click', function() {
        var action = document.querySelector('input[name="action_type"]:checked')?.value;
        if (!action) { toast('error', 'No action available.'); return; }

        var year = action === 'cancel' ? {{ $cancelYear ?? 0 }} : {{ $performYear ?? 0 }};
        if (!year) { toast('error', 'No valid year for this action.'); return; }

        if (action === 'cancel') {
            if (!confirm('Cancel Year-End Closing for ' + year + '? This will void the closing entries.')) return;
            showLoading();
            fetch('{{ route("accounting.year-end-closing.cancel") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ fiscal_year: year })
            })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                hideLoading();
                if (!resp.success) { toast('error', resp.message); return; }
                toast('success', resp.message);
                setTimeout(function() { window.location.reload(); }, 1500);
            })
            .catch(function() { hideLoading(); toast('error', 'Network error.'); });
        } else {
            showDots();
            if (infoToast) infoToast.remove();
            infoToast = toast('info', 'Analyzing... Performing Year-End Closing. Please do not close the window.');
            fetch('{{ route("accounting.year-end-closing.check-uncleared") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ fiscal_year: year })
            })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                hideDots();
                if (infoToast) { infoToast.remove(); infoToast = null; }
                if (!resp.success) { toast('error', 'Check failed.'); return; }
                if (resp.uncleared_count > 0) {
                    showUncleared(resp.uncleared_payments, year);
                } else {
                    doPerform(year);
                }
            })
            .catch(function() { hideDots(); if (infoToast) { infoToast.remove(); infoToast = null; } toast('error', 'Network error.'); });
        }
    });

    function showUncleared(payments, year) {
        var section = document.getElementById('unclearedSection');
        section.classList.add('active');
        document.getElementById('unclearedMsg').innerHTML = '<i class="fa fa-exclamation-triangle"></i> Please clear/deposit following payment(s) before performing year-end';
        var tbody = document.getElementById('unclearedBody');
        var html = '';
        for (var i = 0; i < payments.length; i++) {
            var p = payments[i];
            html += '<tr>';
            html += '<td>' + (p.payment_type || '') + '</td>';
            html += '<td>' + formatUsDate(p.post_date) + '</td>';
            html += '<td>' + (p.vendor || '') + '</td>';
            html += '<td>' + (p.check_no || '') + '</td>';
            html += '<td>' + (p.bank_name || '') + '</td>';
            html += '<td style="text-align:right;">' + (p.paid_amount ? parseFloat(p.paid_amount).toFixed(2) : '0.00') + '</td>';
            html += '<td>' + formatUsDate(p.clear_deposit_date) + '</td>';
            html += '</tr>';
        }
        tbody.innerHTML = html;
    }

    function doPerform(year) {
        showLoading();
        fetch('{{ route("accounting.year-end-closing.perform") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ fiscal_year: year })
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            hideLoading();
            if (!resp.success) { toast('error', resp.message); return; }
            toast('success', resp.message);
            setTimeout(function() { window.location.reload(); }, 1500);
        })
        .catch(function() { hideLoading(); toast('error', 'Network error.'); });
    }

    function showDetail(id) {
        fetch('/accounting/year-end-closing/detail/' + id, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (!resp.success) { toast('error', 'Failed to load details.'); return; }
            var c = resp.closing;
            var html = 'Year: ' + c.fiscal_year + '\nAction: ' + c.action + '\nDate: ' + c.closing_date + '\nEntries: ' + c.entries_created;
            if (c.summary) {
                html += '\n\nSummary:\n';
                if (c.summary.total_revenue !== undefined) html += 'Total Revenue: ' + parseFloat(c.summary.total_revenue).toFixed(2) + '\n';
                if (c.summary.total_expense !== undefined) html += 'Total Expense: ' + parseFloat(c.summary.total_expense).toFixed(2) + '\n';
                if (c.summary.net_income !== undefined) html += 'Net Income: ' + parseFloat(c.summary.net_income).toFixed(2) + '\n';
                if (c.summary.entries_created !== undefined) html += 'Entries Created: ' + c.summary.entries_created + '\n';
                if (c.summary.voided_entries !== undefined) html += 'Voided Entries: ' + c.summary.voided_entries + '\n';
            }
            if (resp.entries && resp.entries.length > 0) {
                html += '\nJournal Entries:\n';
                for (var i = 0; i < resp.entries.length; i++) {
                    html += resp.entries[i].entry_no + ' - ' + resp.entries[i].description + ' [' + resp.entries[i].status + ']\n';
                }
            }
            alert(html);
        })
        .catch(function() { toast('error', 'Network error.'); });
    }

    toggleAction();
    </script>
</x-layout>
