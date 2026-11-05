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
        .form-table{width:100%;border-collapse:collapse}
        .form-table td{padding:3px 4px;vertical-align:middle;font-size:11px;border:none}
        .form-table .flabel{background:#eef1f5;width:180px;padding:4px 8px;font-weight:600;color:#333;white-space:nowrap}
        .form-table .finput{padding-left:8px}
        .form-table input[type="radio"]{accent-color:#3b82f6;margin-right:3px}
        .form-table label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap;margin-right:14px}
        .form-table select{height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;color:#333;background:#fff;min-width:200px}
        .form-table input[type="date"]{height:22px;border:1px solid #c2cad8;padding:1px 4px;font-size:11px;border-radius:2px;color:#333;background:#fff;min-width:160px}
        .btn-search-gf{background:#4CAF50;color:#fff;border:1px solid #388E3C;border-radius:2px;padding:0 16px;height:26px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:4px}
        .btn-search-gf:hover{background:#388E3C;color:#fff}
        .section-title{font-size:12px;font-weight:700;color:#1e293b;text-transform:uppercase;margin-bottom:8px;padding-bottom:4px;border-bottom:1px solid #e2e8f0}
        .block-table{width:100%;border-collapse:collapse;font-size:11px}
        .block-table th{background:#f8fafc;color:#475569;font-weight:600;border:1px solid #cbd5e1;padding:4px 8px;text-align:left}
        .block-table td{border:1px solid #e2e8f0;padding:4px 8px;color:#334155}
        .loading-overlay{display:none;position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:9999;justify-content:center;align-items:center}
        .loading-overlay.active{display:flex}
        .loading-spinner{width:36px;height:36px;border:3px solid #e2e8f0;border-top-color:#3b82f6;border-radius:50%;animation:spin .8s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .toast-container{position:fixed;top:16px;right:16px;z-index:10000;display:flex;flex-direction:column;gap:8px}
        .toast{padding:10px 16px;border-radius:4px;font-size:11px;font-weight:600;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:slideIn .3s ease}
        .toast.success{background:#22c55e}
        .toast.error{background:#e73d4a}
        @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
    </style>
    @endpush

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Journal</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Accounting Block / Unblock</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject">Accounting Block / Unblock</span>
            </div>
            <div class="portlet-body">
                <table class="form-table">
                    <tr>
                        <td class="flabel">Office</td>
                        <td class="finput">
                            <select id="officeId">
                                <option value="">All</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->code }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Block/Unblock</td>
                        <td class="finput">
                            <label><input type="radio" name="block_action" value="BLOCK" checked> Block</label>
                            <label><input type="radio" name="block_action" value="UNBLOCK"> Unblock</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="flabel">Block All Data Before (Including)</td>
                        <td class="finput">
                            <input type="date" id="blockDate">
                        </td>
                    </tr>
                </table>
                <div style="text-align:center;margin-top:12px;padding-top:10px;border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn-search-gf" id="btnApply"><i class="fa fa-check"></i> Apply</button>
                </div>
            </div>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject">LAST BLOCK DATE</span>
            </div>
            <div class="portlet-body">
                <table class="block-table">
                    <thead>
                        <tr>
                            <th style="width:35%;">Office</th>
                            <th>Block Date</th>
                            <th>Status</th>
                            <th>Updated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($latestRecords ?? $lastBlockDates ?? []) as $record)
                            <tr>
                                <td><strong>{{ $record->office?->code ?? 'All Offices' }}</strong></td>
                                <td>{{ $record->block_date?->format('m-d-Y') }}</td>
                                <td>
                                    @if(($record->action ?? 'BLOCK') === 'BLOCK')
                                        <span style="color:#ef4444;font-weight:700;"><i class="fa fa-lock"></i> BLOCKED</span>
                                    @else
                                        <span style="color:#16a34a;font-weight:700;"><i class="fa fa-unlock"></i> UNBLOCKED</span>
                                    @endif
                                </td>
                                <td style="color:#64748b;font-size:10px;">{{ $record->creator?->name ?? 'Admin' }} ({{ $record->created_at?->format('m-d-Y H:i') }})</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center;color:#94a3b8;padding:16px;">No block records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <div class="loading-overlay" id="ldg"><div class="loading-spinner"></div></div>

    <script>
    (function(){
        var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function $(sel) { return document.querySelector(sel); }

        function toast(type, msg) {
            var c = document.getElementById('toastContainer');
            var t = document.createElement('div');
            t.className = 'toast ' + type;
            t.textContent = msg;
            c.appendChild(t);
            setTimeout(function(){ t.remove(); }, 4000);
        }

        function showLdg() { document.getElementById('ldg').classList.add('active'); }
        function hideLdg() { document.getElementById('ldg').classList.remove('active'); }

        $('#btnApply').addEventListener('click', function() {
            var officeId = $('#officeId').value;
            var action = document.querySelector('input[name="block_action"]:checked').value;
            var blockDate = $('#blockDate').value;

            if (!blockDate) {
                toast('error', 'Please select a date.');
                return;
            }

            showLdg();
            fetch('{{ route("accounting.journal.block.apply") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    office_id: officeId || null,
                    action: action,
                    block_date: blockDate
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                hideLdg();
                if (!resp.success) { toast('error', resp.message || 'Failed.'); return; }
                toast('success', resp.message || 'Applied!');
                setTimeout(function() { window.location.reload(); }, 1000);
            })
            .catch(function() { hideLdg(); toast('error', 'Network error.'); });
        });
    })();
    </script>
</x-layout>
