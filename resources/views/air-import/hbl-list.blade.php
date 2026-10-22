<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .btn-group{display:inline-flex;gap:0;border-radius:4px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.1);}
        .btn-group .btn-tool{border-radius:0;margin:0;}
        .btn-group .btn-tool:first-child{border-top-left-radius:4px;border-bottom-left-radius:4px;}
        .btn-group .btn-tool:last-child{border-top-right-radius:4px;border-bottom-right-radius:4px;}
        .portlet-tool{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e2e8f0;}

        /* ── COMPLETE NON-STICKY & RESPONSIVE TABLE STYLING ── */
        .grid-table th,
        .grid-table td,
        .grid-table tr,
        .grid-table th.sticky-col,
        .grid-table td.sticky-col,
        .grid-table th.sticky-col-header,
        .sticky-col,
        .sticky-col-header {
            position: static !important;
            left: auto !important;
            top: auto !important;
            z-index: 1 !important;
        }

        .grid-container {
            width: 100% !important;
            overflow: hidden !important;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        .grid-wrapper {
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
            max-height: calc(100vh - 230px) !important;
            min-height: 320px !important;
            -webkit-overflow-scrolling: touch !important;
        }

        .grid-table {
            width: max-content !important;
            min-width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            table-layout: auto !important;
        }

        .grid-table th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            border-right: 1px solid #e2e8f0 !important;
            border-top: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            white-space: nowrap !important;
            height: 28px !important;
            text-align: left;
            user-select: none;
            font-size: 11px !important;
        }

        .grid-table td {
            padding: 4px 10px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-right: 1px solid #e2e8f0 !important;
            white-space: nowrap !important;
            height: 28px !important;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
            color: #334155;
            background: #fff;
            font-size: 11px !important;
        }

        .grid-table tbody tr:hover td {
            background-color: #f1f5f9 !important;
        }

        .grid-table tbody tr.row-selected td {
            background-color: #eff6ff !important;
        }

        @media(max-width:768px){
            .page-content{padding:2px!important;overflow-x:hidden!important;}
            .portlet.light{margin:0!important;border-radius:0!important;}
            .portlet-title{flex-direction:column!important;align-items:flex-start!important;padding:6px!important;gap:6px;}
            .portlet-title .actions{width:100%;flex-wrap:wrap;gap:3px!important;}
            .portlet-tool{flex-direction:column!important;align-items:flex-start!important;padding:6px!important;gap:6px!important;}
            .portlet-tool>div{width:100%;}
            .btn-group{width:100%;flex-wrap:wrap;}
            .grid-wrapper{max-height:calc(100vh - 350px)!important;min-height:200px!important;}
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon" style="color:#ef4444;"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete HBL(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- COLOR PICKER MODAL --}}
    <div class="overlay" id="color-picker-overlay" onclick="if(event.target===this) closeColorPicker()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-paint-brush" style="color:#3b82f6;"></i> Status Color</div>
                <button class="modal-close" onclick="closeColorPicker()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <div class="color-picker-grid" id="color-picker-grid"></div>
                <div class="color-clear-btn" onclick="clearColor()">Clear / No Color</div>
            </div>
        </div>
    </div>

    {{-- CHANGE USER MODAL --}}
    <div class="overlay" id="change-user-overlay" onclick="if(event.target===this) closeChangeUser()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-user" style="color:#3b82f6;"></i> <span id="change-user-title">Change Sales Person</span></div>
                <button class="modal-close" onclick="closeChangeUser()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="min-width:300px;">
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Select User</label>
                    <select id="change-user-select" style="height:30px;font-size:11px;width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:0 6px;">
                        <option value="">-- Select --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-tool" onclick="closeChangeUser()">Cancel</button>
                    <button class="btn-tool green" onclick="executeChangeUser()">Update</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN PAGE --}}
    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/air-import/list">Air Import</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">HAWB List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            {{-- PORTLET TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">HAWB List Overview</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" id="btn-filter" onclick="toggleFilter()" title="Toggle filter row">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <div style="position:relative;display:inline-flex;align-items:center;">
                        <button class="btn-action-round" id="btn-config" onclick="toggleConfig()" title="Column visibility">
                            <i class="fa fa-cogs"></i> Config
                        </button>
                        <div class="config-panel" id="config-panel" style="display:none;">
                            <div class="config-panel-title">Column Visibility</div>
                            <div id="col-toggles"></div>
                        </div>
                    </div>
                    <button class="btn-action-round white" onclick="exportCsv()" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a href="{{ route('air-import.create') }}" class="btn-tool green" title="New Shipment"><i class="fa fa-plus"></i></a>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                    <select class="select-tool" id="bulk-sales-select" disabled onchange="onBulkSalesChange(this)">
                        <option value="">Change Sales</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <select class="select-tool" id="bulk-op-select" disabled onchange="onBulkOpChange(this)">
                        <option value="">Change OP</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;" placeholder="Quick search..." oninput="quickSearch(this.value)" value="{{ request('search') }}">
                </div>
            </div>

            {{-- TABLE --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                <tr id="header-row">
                                    <th data-col="check"  style="width:35px;text-align:center;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th data-col="lock" style="width:35px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th data-col="color" style="width:40px;text-align:center;">Color</th>
                                    <th data-col="file_no" style="min-width:130px;">Booking No.</th>
                                    <th data-col="hawb_no" style="min-width:130px;font-weight:600;">HAWB No.</th>
                                    <th data-col="mawb_no" style="min-width:130px;">MAWB No.</th>
                                    <th data-col="customer" style="min-width:160px;">Customer</th>
                                    <th data-col="shipper" style="min-width:160px;">Shipper</th>
                                    <th data-col="consignee" style="min-width:160px;">Consignee</th>
                                    <th data-col="pkg" style="min-width:70px;text-align:right;">Pkg</th>
                                    <th data-col="gw" style="min-width:85px;text-align:right;">G.W (KG)</th>
                                    <th data-col="cw" style="min-width:85px;text-align:right;">C.W (KG)</th>
                                    <th data-col="etd" style="min-width:85px;">ETD</th>
                                    <th data-col="eta" style="min-width:85px;">ETA</th>
                                    <th data-col="departure" style="min-width:130px;">Departure</th>
                                    <th data-col="destination" style="min-width:130px;">Destination</th>
                                    <th data-col="sales" style="min-width:100px;">Sales</th>
                                    <th data-col="op" style="min-width:100px;">OP</th>
                                    <th data-col="created_at" style="min-width:100px;">Created</th>
                                </tr>

                                {{-- FILTER ROW --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td style="text-align:center;"></td>
                                    <td style="text-align:center;"></td>
                                    <td style="text-align:center;"></td>
                                    <td><input class="filter-input" style="width:100%;" data-param="filter_file_no" placeholder="File…" oninput="applyFiltersTyping()"></td>
                                    <td><input class="filter-input" style="width:100%;" data-param="filter_hawb" placeholder="HAWB…" oninput="applyFiltersTyping()"></td>
                                    <td><input class="filter-input" style="width:100%;" data-param="filter_mawb" placeholder="MAWB…" oninput="applyFiltersTyping()"></td>
                                    <td><input class="filter-input" style="width:100%;" data-param="filter_customer" placeholder="Customer…" oninput="applyFiltersTyping()"></td>
                                    <td><input class="filter-input" style="width:100%;" data-col-idx="7" placeholder="Shipper…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" style="width:100%;" data-col-idx="8" placeholder="Consignee…" oninput="applyFilters()"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @include('air-import.partials.hbl-list-rows', ['hbls' => $hbls])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- PAGINATION --}}
            <div class="portlet-tool bottom">
                <div style="font-size:10px;color:#64748b;">
                    Showing <span id="stat-first">{{ $hbls->firstItem() ?? 0 }}</span> &ndash; <span id="stat-last">{{ $hbls->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $hbls->total() }}</span> records
                </div>
                <div id="pagination-container">
                    {{ $hbls->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var searchTimer, filterTimer;
    var _changeMode = 'sales';
    var _colorShipmentId = null;

    /* ================================================================
       TOOLBAR — checkbox management
    ================================================================ */
    function updateToolbar() {
        var checked  = [...document.querySelectorAll('.row-check:checked')];
        var all      = [...document.querySelectorAll('.row-check')];
        var n        = checked.length;
        var sa       = document.getElementById('select-all');
        sa.checked        = n === all.length && all.length > 0;
        sa.indeterminate  = n > 0 && n < all.length;

        document.getElementById('btn-delete').disabled   = n === 0;
        document.getElementById('btn-block').disabled    = n === 0;
        document.getElementById('btn-unblock').disabled  = n === 0;
        document.getElementById('bulk-sales-select').disabled = n === 0;
        document.getElementById('bulk-op-select').disabled    = n === 0;

        var badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent   = n + ' selected';

        document.querySelectorAll('#grid-body tr[data-id]').forEach(function(row) {
            var cb = row.querySelector('.row-check');
            row.classList.toggle('row-selected', cb && cb.checked);
        });
    }

    function toggleSelectAll(el) {
        document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = el.checked; });
        updateToolbar();
    }

    function rowClick(e, row) {
        var skip = ['A', 'INPUT', 'BUTTON', 'I', 'SELECT'];
        if (skip.indexOf(e.target.tagName) >= 0) return;
        var cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(function(cb) { return cb.value; });
    }

    /* ================================================================
       AJAX GRID UPDATE
    ================================================================ */
    async function updateGrid(url) {
        try {
            var response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Response not OK');
            var data = await response.json();

            if (data.html !== undefined) {
                document.getElementById('grid-body').innerHTML = data.html;
                document.getElementById('pagination-container').innerHTML = data.pagination || '';
                document.getElementById('stat-first').textContent = data.first || 0;
                document.getElementById('stat-last').textContent = data.last || 0;
                document.getElementById('stat-total').textContent = data.total || 0;
            } else {
                var doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                var newBody = doc.getElementById('grid-body');
                var newPagination = doc.getElementById('pagination-container');
                if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
                if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;
            }

            // Re-apply column visibility toggles after AJAX update
            document.querySelectorAll('#header-row th[data-col]').forEach(function(th) {
                if (th.style.display === 'none') {
                    toggleColumn(th.dataset.col, false);
                }
            });

            updateToolbar();
        } catch (e) {
            showToast('error', 'Failed to update grid');
        }
    }

    document.addEventListener('click', function(e) {
        var link = e.target.closest('#pagination-container a, .tp-pagination a');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    function quickSearch(val) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var q = val.trim();
            if (!q) url.searchParams.delete('search');
            else url.searchParams.set('search', q);
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 300);
    }

    var FILTER_MAP = {
        2: 'filter_file_no', 3: 'filter_hawb',
        6: 'filter_shipper', 7: 'filter_consignee'
    };

    function applyFiltersTyping() { clearTimeout(filterTimer); filterTimer = setTimeout(applyFilters, 400); }

    function applyFilters() {
        var url = new URL(window.location.href);
        document.querySelectorAll('#filter-row .filter-input').forEach(function(inp) {
            var param = inp.dataset.param;
            if (param) {
                if (inp.value.trim()) url.searchParams.set(param, inp.value.trim());
                else url.searchParams.delete(param);
            }
        });
        url.searchParams.delete('page');
        updateGrid(url.toString());
    }

    /* ================================================================
       FILTER ROW TOGGLE
    ================================================================ */
    var filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        document.getElementById('filter-row').style.display = filterOpen ? 'table-row' : 'none';
        document.getElementById('btn-filter').classList.toggle('active', filterOpen);
        if (!filterOpen) {
            document.querySelectorAll('.filter-input').forEach(function(i) { i.value = ''; });
            applyFilters();
        }
    }

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'lock'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        if (!panel) return;
        var open  = panel.style.display === 'none' || panel.style.display === '';
        panel.style.display = open ? 'block' : 'none';
        var btn = document.getElementById('btn-config');
        if (btn) btn.classList.toggle('active', open);
        if (open) buildConfigPanel();
    }

    function buildConfigPanel() {
        var container = document.getElementById('col-toggles');
        container.innerHTML = '';
        document.querySelectorAll('#header-row th[data-col]').forEach(function(th) {
            if (PINNED_COLS.indexOf(th.dataset.col) >= 0) return;
            var label = document.createElement('label');
            var cb    = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.onchange = function() { toggleColumn(th.dataset.col, cb.checked); };
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim());
            container.appendChild(label);
        });
    }

    function toggleColumn(colName, show) {
        var th  = document.querySelector('#header-row th[data-col="' + colName + '"]');
        if (!th) return;
        var idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(function(row) {
            var cell = row.children[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn   = document.getElementById('btn-config');
        if (panel && btn && panel.style && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    /* ================================================================
       DELETE
    ================================================================ */
    function confirmDelete() {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent = 'You are about to permanently delete ' + n + ' HBL(s).';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Deleting...');
        fetch('/air-import/hbl-bulk-delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { showToast('success', d.message); updateGrid(window.location.href); }
            else showToast('error', d.message || 'Delete failed.');
        })
        .catch(function() { showToast('error', 'Delete failed.'); });
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected()   { bulkAction('/air-import/hbl-bulk-block',   'Blocked'); }
    function unblockSelected() { bulkAction('/air-import/hbl-bulk-unblock', 'Unblocked'); }

    function bulkAction(url, label) {
        var ids = getSelectedIds();
        if (!ids.length) return;
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { showToast('success', d.message); updateGrid(window.location.href); }
            else showToast('error', d.message || label + ' failed.');
        })
        .catch(function() { showToast('error', label + ' failed.'); });
    }

    /* ================================================================
       CHANGE SALES / OP (modal)
    ================================================================ */
    function onBulkSalesChange(el) {
        var val = el.value;
        if (!val) return;
        _changeMode = 'sales';
        document.getElementById('change-user-title').textContent = 'Change Sales Person';
        document.getElementById('change-user-overlay').classList.add('open');
        document.getElementById('change-user-select').value = val;
        el.value = '';
    }
    function onBulkOpChange(el) {
        var val = el.value;
        if (!val) return;
        _changeMode = 'op';
        document.getElementById('change-user-title').textContent = 'Change OP';
        document.getElementById('change-user-overlay').classList.add('open');
        document.getElementById('change-user-select').value = val;
        el.value = '';
    }
    function closeChangeUser() { document.getElementById('change-user-overlay').classList.remove('open'); }
    function executeChangeUser() {
        var userId = document.getElementById('change-user-select').value;
        if (!userId) { showToast('error', 'Please select a user.'); return; }
        var ids = getSelectedIds();
        if (!ids.length) { closeChangeUser(); return; }
        var url = _changeMode === 'sales' ? '/air-import/hbl-bulk-change-sales' : '/air-import/hbl-bulk-change-op';
        var body = { ids: ids };
        if (_changeMode === 'sales') body.sales_person_id = userId;
        else body.op_id = userId;
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body)
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { showToast('success', d.message); updateGrid(window.location.href); }
            else showToast('error', d.message || 'Update failed.');
        })
        .catch(function() { showToast('error', 'Failed to update.'); });
        closeChangeUser();
    }

    /* ================================================================
       COLOR PICKER
    ================================================================ */
    var COLOR_OPTIONS = [
        { label: 'Urgent',           value: '#E08283' },
        { label: 'Ready to bill',    value: '#F3C200' },
        { label: 'Ready to close',   value: '#25A69A' },
        { label: 'Postpone',         value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' },
    ];

    function openColorPicker(id, currentColor) {
        _colorShipmentId = id;
        var grid = document.getElementById('color-picker-grid');
        grid.innerHTML = COLOR_OPTIONS.map(function(o) {
            var active = o.value === currentColor;
            return '<div class="color-picker-opt ' + (active ? 'active' : '') + '" onclick="selectColor(\'' + o.value + '\', this)"><span class="swatch" style="background:' + o.value + '"></span><span>' + o.label + '</span><i class="fa fa-check"></i></div>';
        }).join('');
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function selectColor(color, el) {
        document.querySelectorAll('.color-picker-opt').forEach(function(c) { c.classList.remove('active'); });
        el.classList.add('active');
        var id = _colorShipmentId;
        fetch('/air-import/' + id + '/color', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                document.querySelectorAll('.color-mark').forEach(function(span) {
                    span.style.background = color;
                });
                showToast('success', 'Color updated');
            }
        })
        .catch(function() { showToast('error', 'Color update failed'); });
        closeColorPicker();
    }

    function closeColorPicker() { document.getElementById('color-picker-overlay').classList.remove('open'); _colorShipmentId = null; }

    function clearColor() {
        var id = _colorShipmentId;
        if (!id) return;
        fetch('/air-import/' + id + '/color', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                document.querySelectorAll('.color-mark').forEach(function(span) {
                    span.style.background = '#94a3b8';
                });
                showToast('success', 'Color cleared');
            }
        })
        .catch(function() { showToast('error', 'Failed to clear'); });
        closeColorPicker();
    }

    /* ================================================================
       EXPORT CSV
    ================================================================ */
    function exportCsv() {
        showToast('info', 'Preparing Excel export...');
        var q = document.getElementById('quick-search')?.value?.trim();
        var params = [];
        if (q) params.push('search=' + encodeURIComponent(q));
        document.querySelectorAll('#filter-row .filter-input').forEach(function(inp) {
            if (inp.value.trim()) {
                var key = FILTER_MAP[inp.dataset.colIdx] || inp.dataset.param;
                if (key) params.push(key + '=' + encodeURIComponent(inp.value.trim()));
            }
        });
        var base = '/air-import/hbl-export-csv';
        var url = params.length ? base + '?' + params.join('&') : base;
        
        var iframe = document.getElementById('download-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'download-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url;
        setTimeout(function() { showToast('success', 'Excel export started!'); }, 800);
    }

    /* ================================================================
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(type, msg) {
        var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '\"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() { t.remove(); }, 3000);
    }

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif
    </script>
    @endpush
</x-layout>
