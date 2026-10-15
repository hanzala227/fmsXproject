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

        .filter-section{margin-bottom:8px}
        .filter-row{display:flex;align-items:center;gap:12px;margin-bottom:6px}
        .filter-row label{font-size:11px;color:#334155;white-space:nowrap;cursor:pointer}
        .filter-row input[type="radio"],.filter-row input[type="checkbox"]{accent-color:#3b82f6;margin-right:2px;vertical-align:middle}
        .fi{height:22px;border:1px solid #c2cad8;padding:0 6px;font-size:11px;border-radius:2px;color:#333;background:#fff;outline:none;box-sizing:border-box}
        .fi:focus{border-color:#3b82f6}
        .fi-select{min-width:100px}
        .fi-date{width:120px}
        .fi-text{width:180px}
        .fi-search{width:150px}
        .filter-group{display:flex;align-items:center;gap:6px}
        .filter-group .fg-label{font-size:11px;color:#64748b;white-space:nowrap;min-width:45px}
        .date-range{display:flex;align-items:center;gap:4px}
        .date-range span{font-size:11px;color:#94a3b8}
        .radio-group{display:flex;align-items:center;gap:12px}
        .radio-group label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center}
        .check-group{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .check-group label{font-size:11px;color:#334155;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center}
        .search-row{display:flex;align-items:center;gap:12px;margin-top:8px}
        .search-field{display:flex;align-items:center;gap:4px}
        .search-field label{font-size:11px;color:#64748b;white-space:nowrap}
        .date-separator{color:#94a3b8;font-size:11px}

        .btn-search-gf{background:#3b82f6;color:#fff;border:1px solid #2563eb;border-radius:2px;padding:0 14px;height:22px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:4px;box-sizing:border-box}
        .btn-search-gf:hover{background:#2563eb;color:#fff}
        .btn-apply{background:#22c55e;color:#fff;border:1px solid #16a34a;border-radius:2px;padding:0 14px;height:22px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:4px;box-sizing:border-box}
        .btn-apply:hover{background:#16a34a;color:#fff}

        .grid-container{width:100%;overflow:hidden;background:#fff}
        .grid-wrapper{width:100%;overflow-x:auto;max-height:calc(100vh - 340px)}
        .grid-table{border-collapse:separate;border-spacing:0;width:100%;font-size:10px;table-layout:auto}
        .grid-table th{background:#f8fafc;color:#475569;font-weight:600;border-bottom:1px solid #cbd5e1;border-right:1px solid #e2e8f0;border-top:1px solid #cbd5e1;padding:3px 6px;white-space:nowrap;height:24px;position:sticky;top:0;z-index:10;text-align:left;user-select:none}
        .grid-table td{padding:3px 6px;border-bottom:1px solid #e2e8f0;border-right:1px solid #e2e8f0;white-space:nowrap;height:24px;overflow:hidden;text-overflow:ellipsis;vertical-align:middle;color:#334155}
        .grid-table tr:hover td{background-color:#f1f5f9!important}
        .grid-table tr.row-selected td{background-color:#eff6ff!important}

        .toast-container{position:fixed;top:16px;right:16px;z-index:10000;display:flex;flex-direction:column;gap:8px}
        .toast{padding:10px 16px;border-radius:4px;font-size:11px;font-weight:600;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:slideIn .3s ease}
        .toast.success{background:#22c55e}
        .toast.error{background:#e73d4a}
        @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        .loading-overlay{display:none;position:fixed;inset:0;background:rgba(255,255,255,.7);z-index:9999;justify-content:center;align-items:center}
        .loading-overlay.active{display:flex}
        .loading-spinner{width:36px;height:36px;border:3px solid #e2e8f0;border-top-color:#3b82f6;border-radius:50%;animation:spin .8s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .block-badge{padding:1px 6px;border-radius:2px;font-size:9px;font-weight:600}
        .block-badge.yes{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
        .block-badge.no{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
    </style>
    @endpush

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i>Accounting</li>
                <li><i class="fa fa-angle-right"></i>Journal</li>
                <li><i class="fa fa-angle-right"></i><span style="color:#333;font-weight:700;">Accounting Block Maintenance</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <span class="caption-subject">Accounting Block Maintenance</span>
            </div>
            <div class="portlet-body">
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="radio-group">
                            <label><input type="radio" name="record_type" value="shipment" checked onchange="toggleRecordType()"> Shipment</label>
                            <label><input type="radio" name="record_type" value="ar_ap_dc" onchange="toggleRecordType()"> AR/AP/DC</label>
                            <label><input type="radio" name="record_type" value="deposit_payment" onchange="toggleRecordType()"> Deposit/Payment</label>
                            <label><input type="radio" name="record_type" value="general_journal" onchange="toggleRecordType()"> General Journal</label>
                        </div>

                        <div style="margin-left:auto;display:flex;align-items:center;gap:16px;">
                            <div class="filter-group">
                                <span class="fg-label">Office</span>
                                <select id="officeId" class="fi fi-select">
                                    <option value="">All</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-group">
                                <span class="fg-label">Block</span>
                                <select id="isBlocked" class="fi fi-select">
                                    <option value="">No</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <span class="fg-label">Post Date</span>
                                <div class="date-range">
                                    <input type="date" id="postDateFrom" class="fi fi-date" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                                    <span>~</span>
                                    <input type="date" id="postDateTo" class="fi fi-date" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="shipmentTypesRow" class="filter-row" style="margin-top:4px;">
                        <div class="check-group">
                            <label><input type="checkbox" class="shipment-type-check" value="ocean_import" checked> Ocean Import</label>
                            <label><input type="checkbox" class="shipment-type-check" value="ocean_export" checked> Ocean Export</label>
                            <label><input type="checkbox" class="shipment-type-check" value="air_import" checked> Air Import</label>
                            <label><input type="checkbox" class="shipment-type-check" value="air_export" checked> Air Export</label>
                            <label><input type="checkbox" class="shipment-type-check" value="truck" checked> Truck Operation</label>
                            <label><input type="checkbox" class="shipment-type-check" value="misc" checked> Misc. Operation</label>
                            <label><input type="checkbox" class="shipment-type-check" value="warehouse" checked> Warehouse</label>
                        </div>
                    </div>

                    <div class="search-row">
                        <div class="search-field">
                            <label>Booking no</label>
                            <input type="text" id="fileNo" class="fi fi-search">
                        </div>
                        <div class="search-field" id="blNoField">
                            <label>MB/L No.</label>
                            <input type="text" id="blNo" class="fi fi-search">
                        </div>
                        <button type="button" class="btn-search-gf" id="btnSearch"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="portlet light">
            <div class="portlet-body" style="padding:0;">
                <div class="grid-container">
                    <div class="grid-wrapper" id="gridWrapper">
                        <table class="grid-table" id="resultsTable">
                            <thead>
                                <tr>
                                    <th style="width:30px;text-align:center;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                    <th>Type</th>
                                    <th>Office</th>
                                    <th>Post Date</th>
                                    <th>Booking no</th>
                                    <th>BL No.</th>
                                    <th>Oversea Agent</th>
                                    <th>Carrier</th>
                                    <th>CUSTOMER</th>
                                    <th>POL</th>
                                    <th>POD</th>
                                    <th>Vessel/FLT No.</th>
                                    <th style="width:60px;">Blocked</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody">
                                <tr>
                                    <td colspan="13" style="text-align:center;color:#94a3b8;padding:30px;">Click Search to load results.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="portlet-body" style="padding:10px 14px;border-top:1px solid #e2e8f0;text-align:center;">
                <button type="button" class="btn-apply" id="btnApply" onclick="applyBlock()"><i class="fa fa-check"></i> Apply</button>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>
    <div class="loading-overlay" id="loadingOverlay"><div class="loading-spinner"></div></div>

    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var currentResults = [];

    function $(sel) { return document.querySelector(sel); }
    function $$(sel) { return document.querySelectorAll(sel); }

    function toast(type, msg) {
        var c = document.getElementById('toastContainer');
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.textContent = msg;
        c.appendChild(t);
        setTimeout(function(){ t.remove(); }, 4000);
    }

    function showLoading() { document.getElementById('loadingOverlay').classList.add('active'); }
    function hideLoading() { document.getElementById('loadingOverlay').classList.remove('active'); }

    function toggleRecordType() {
        var type = document.querySelector('input[name="record_type"]:checked').value;
        document.getElementById('shipmentTypesRow').style.display = type === 'shipment' ? 'flex' : 'none';
        document.getElementById('blNoField').style.display = type === 'shipment' ? 'flex' : 'none';
    }

    function toggleSelectAll() {
        var checked = document.getElementById('selectAll').checked;
        $$('.row-check').forEach(function(cb) { cb.checked = checked; });
    }

    function doSearch() {
        var recordType = document.querySelector('input[name="record_type"]:checked').value;
        var officeId = document.getElementById('officeId').value;
        var isBlocked = document.getElementById('isBlocked').value;
        var postDateFrom = document.getElementById('postDateFrom').value;
        var postDateTo = document.getElementById('postDateTo').value;
        var fileNo = document.getElementById('fileNo').value;
        var blNo = document.getElementById('blNo').value;

        var shipmentTypes = [];
        if (recordType === 'shipment') {
            $$('.shipment-type-check:checked').forEach(function(cb) { shipmentTypes.push(cb.value); });
        }

        var payload = {
            record_type: recordType,
            office_id: officeId || null,
            is_blocked: isBlocked || null,
            post_date_from: postDateFrom || null,
            post_date_to: postDateTo || null,
            file_no: fileNo || null,
            bl_no: blNo || null,
            shipment_types: shipmentTypes.length > 0 ? shipmentTypes : null
        };

        showLoading();
        fetch('{{ route("accounting.block-maintenance.search") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            hideLoading();
            if (!resp.success) { toast('error', 'Search failed.'); return; }
            currentResults = resp.data;
            renderGrid(resp.data);
        })
        .catch(function() { hideLoading(); toast('error', 'Network error.'); });
    }

    function renderGrid(data) {
        var tbody = document.getElementById('resultsBody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="13" style="text-align:center;color:#94a3b8;padding:30px;">No records found.</td></tr>';
            return;
        }
        var html = '';
        for (var i = 0; i < data.length; i++) {
            var r = data[i];
            var blocked = r.is_blocked;
            html += '<tr data-idx="' + i + '" onclick="toggleRowSelect(this, event)">';
            html += '<td style="text-align:center;"><input type="checkbox" class="row-check" data-idx="' + i + '" onclick="event.stopPropagation()"></td>';
            html += '<td>' + esc(r.type) + '</td>';
            html += '<td>' + esc(r.office) + '</td>';
            html += '<td>' + esc(r.post_date) + '</td>';
            html += '<td>' + esc(r.file_no) + '</td>';
            html += '<td>' + esc(r.bl_no) + '</td>';
            html += '<td>' + esc(r.oversea_agent) + '</td>';
            html += '<td>' + esc(r.carrier) + '</td>';
            html += '<td>' + esc(r.customer) + '</td>';
            html += '<td>' + esc(r.pol) + '</td>';
            html += '<td>' + esc(r.pod) + '</td>';
            html += '<td>' + esc(r.vessel_flt_no) + '</td>';
            html += '<td><span class="block-badge ' + (blocked ? 'yes' : 'no') + '">' + (blocked ? 'Yes' : 'No') + '</span></td>';
            html += '</tr>';
        }
        tbody.innerHTML = html;
    }

    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function toggleRowSelect(tr, e) {
        if (e.target.type === 'checkbox') return;
        var cb = tr.querySelector('.row-check');
        if (cb) cb.checked = !cb.checked;
    }

    function applyBlock() {
        var selected = [];
        $$('.row-check:checked').forEach(function(cb) {
            var idx = parseInt(cb.getAttribute('data-idx'));
            selected.push(currentResults[idx]);
        });

        if (selected.length === 0) {
            toast('error', 'Please select at least one record.');
            return;
        }

        var allBlocked = selected.every(function(r) { return r.is_blocked; });
        var action = allBlocked ? 'unblock' : 'block';
        var actionLabel = action === 'block' ? 'Block' : 'Unblock';

        if (!confirm(actionLabel + ' ' + selected.length + ' selected record(s)?')) return;

        var table = selected[0].record_table;
        var ids = selected.map(function(r) { return r.id; });

        showLoading();
        fetch('{{ route("accounting.block-maintenance.apply") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ids: ids,
                action: action,
                record_table: table
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            hideLoading();
            if (!resp.success) { toast('error', resp.message || 'Failed.'); return; }
            toast('success', resp.message);
            doSearch();
        })
        .catch(function() { hideLoading(); toast('error', 'Network error.'); });
    }

    document.getElementById('btnSearch').addEventListener('click', doSearch);

    document.querySelectorAll('input, select').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') doSearch();
        });
    });
    </script>
</x-layout>
