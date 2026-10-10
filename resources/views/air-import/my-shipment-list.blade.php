<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        /* Button Group Styling - Better Alignment */
        .btn-group {
            display: inline-flex;
            gap: 0;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .btn-group .btn-tool:not(:first-child) {
            border-left: 1px solid rgba(255,255,255,0.2);
        }
        
        .btn-group .btn-tool {
            border-radius: 0;
            margin: 0;
        }
        
        .btn-group .btn-tool:first-child {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        
        .btn-group .btn-tool:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        
        .portlet-tool {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
    </style>
    @endpush


    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="toast-container" id="toast-container"></div>

    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon" style="color:#ef4444;"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Shipment(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

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

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="#">Air Import</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">MAWB List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">MY SHIPMENT LIST</span>
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

            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a href="/air-import/create" class="btn-tool green"><i class="fa fa-plus"></i></a>
                        <button class="btn-tool" id="btn-copy" disabled title="Copy Selected (select 1 row)" onclick="copySelected()">
                            <i class="fa fa-files-o"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block" disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;" placeholder="Quick search..." oninput="quickSearch(this.value)" value="{{ request('search') }}">
                </div>
            </div>

            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;left:0;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:28px;text-align:center;left:25px;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="file_no" style="width:120px;left:53px;">File No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="color" style="width:40px;text-align:center;left:173px;">Color</th>
                                    <th class="sticky-col sticky-col-header" data-col="mawb_no" style="width:130px;left:213px;">MAWB No.</th>
                                    
                                    <th data-col="oversea_agent" style="width:150px;">Oversea Agent</th>
                                    <th data-col="shipper" style="width:150px;">Shipper</th>
                                    <th data-col="hawb_shipper" style="width:150px;">HAWB Shipper</th>
                                    <th data-col="carrier" style="width:130px;">Carrier</th>
                                    <th data-col="destination" style="width:130px;">Destination</th>
                                    <th data-col="departure" style="width:130px;">Departure</th>
                                    <th data-col="eta" style="width:85px;">ETA <i class="fa fa-info-circle" style="color:#94a3b8;"></i></th>
                                    <th data-col="ata" style="width:85px;">ATA</th>
                                    <th data-col="etd" style="width:85px;">ETD <i class="fa fa-info-circle" style="color:#94a3b8;"></i></th>
                                    <th data-col="atd" style="width:85px;">ATD</th>
                                    <th data-col="hawb_no" style="width:130px;">HAWB No.</th>
                                    <th data-col="flight_no" style="width:100px;">Flight No.</th>
                                    <th data-col="ar_balance" style="width:100px;text-align:right;">AR Balance</th>
                                    <th data-col="ap_balance" style="width:100px;text-align:right;">A/P Balance</th>
                                    <th data-col="dc_balance" style="width:100px;text-align:right;">D/C Balance</th>
                                    <th data-col="sales" style="width:100px;">Sales</th>
                                    <th data-col="op" style="width:100px;">OP</th>
                                    <th data-col="status" style="width:100px;">Status</th>
                                </tr>

                                <tr id="filter-row" style="display:none;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:53px;"><input class="filter-input" data-col-idx="2" placeholder="File…" oninput="applyFilters()"></td>
                                    <td class="sticky-col" style="left:173px;"></td>
                                    <td class="sticky-col" style="left:213px;"><input class="filter-input" data-col-idx="4" placeholder="MAWB…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="5" placeholder="Agent…" oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="6" placeholder="Shipper…" oninput="applyFilters()"></td>
                                    <td colspan="8"></td>
                                    <td><input class="filter-input" data-col-idx="15" placeholder="HAWB…" oninput="applyFilters()"></td>
                                    <td colspan="7"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @include('air-import.partials.my-shipment-rows', ['shipments' => $shipments])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $shipments->links('vendor.pagination.custom') }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $shipments->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $shipments->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $shipments->total() }}</span> records
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var searchTimer, filterTimer;
    var _changeMode = 'op';
    var _colorShipmentId = null;

    function updateToolbar() {
        var checked  = [...document.querySelectorAll('.row-check:checked')];
        var all      = [...document.querySelectorAll('.row-check')];
        var n        = checked.length;
        var sa       = document.getElementById('select-all');
        if (sa) {
            sa.checked        = n === all.length && all.length > 0;
            sa.indeterminate  = n > 0 && n < all.length;
        }

        document.getElementById('btn-delete').disabled   = n === 0;
        document.getElementById('btn-copy').disabled     = n !== 1;
        document.getElementById('btn-block').disabled    = n === 0;
        document.getElementById('btn-unblock').disabled  = n === 0;
        document.getElementById('bulk-op-select').disabled = n === 0;

        var badge = document.getElementById('sel-badge');
        if (badge) {
            badge.style.display = n > 0 ? 'inline' : 'none';
            badge.textContent   = n + ' selected';
        }

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

    function toggleLock(el) { el.classList.toggle('fa-lock'); el.classList.toggle('fa-unlock'); }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(function(cb) { return cb.value; });
    }

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
        2: 'filter_file_no', 4: 'filter_mawb',
        5: 'filter_agent', 6: 'filter_shipper',
        15: 'filter_hawb'
    };

    function applyFilters() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            inputs.forEach(function(inp) {
                var key = FILTER_MAP[inp.dataset.colIdx];
                if (key) {
                    var val = inp.value.trim();
                    if (val) url.searchParams.set(key, val);
                    else url.searchParams.delete(key);
                }
            });
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 300);
    }

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

    var PINNED_COLS = ['check', 'lock', 'file_no', 'color', 'mawb_no'];

    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        var open  = panel.style.display === 'none';
        panel.style.display = open ? 'block' : 'none';
        document.getElementById('btn-config').classList.toggle('active', open);
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
        var idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(function(row) {
            var cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn   = document.getElementById('btn-config');
        if (panel && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    function confirmDelete() {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent = 'You are about to permanently delete ' + n + ' Shipment(s).';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Deleting...');
        fetch('/air-import/bulk-delete', {
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

    var _isCopying = false;
    function copySelected() {
        if (_isCopying) return;
        var ids = getSelectedIds();
        if (ids.length !== 1) return;
        _isCopying = true;
        var cp = document.getElementById('btn-copy'); if (cp) cp.disabled = true;
        showToast('info', 'Copying shipment...');
        window.location.href = '/air-import/create?copy=' + ids[0];
    }

    function blockSelected()   { bulkAction('/air-import/bulk-block',   'Blocked'); }
    function unblockSelected() { bulkAction('/air-import/bulk-unblock', 'Unblocked'); }

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
        var url = '/air-import/bulk-change-op';
        var body = { ids: ids, op_id: userId };
        
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
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var mark = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (mark) mark.style.background = color;
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
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var mark = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (mark) mark.style.background = '#fff';
                showToast('success', 'Color cleared');
            }
        })
        .catch(function() { showToast('error', 'Failed to clear'); });
        closeColorPicker();
    }

    function exportCsv() {
        showToast('info', 'Preparing Excel export...');
        var q = document.getElementById('quick-search')?.value?.trim();
        var params = [];
        if (q) params.push('search=' + encodeURIComponent(q));
        document.querySelectorAll('#filter-row .filter-input').forEach(function(inp) {
            if (inp.value.trim()) {
                var key = FILTER_MAP[inp.dataset.colIdx];
                if (key) params.push(key + '=' + encodeURIComponent(inp.value.trim()));
            }
        });
        var base = '/air-import/export-csv';
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
