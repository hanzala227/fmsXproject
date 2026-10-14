<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .btn-group { display:inline-flex;gap:0;border-radius:4px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.1); }
        .btn-group .btn-tool { border-radius:0;margin:0; }
        .btn-group .btn-tool:first-child { border-top-left-radius:4px;border-bottom-left-radius:4px; }
        .btn-group .btn-tool:last-child  { border-top-right-radius:4px;border-bottom-right-radius:4px; }
        .portlet-tool { display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e2e8f0; }
        @media(max-width:768px){
            .page-content{padding:2px!important;overflow-x:hidden!important;}
            .portlet.light{margin:0!important;border-radius:0!important;}
            .portlet-title{flex-direction:column!important;align-items:flex-start!important;padding:6px!important;gap:6px;}
            .portlet-title .actions{width:100%;flex-wrap:wrap;gap:3px!important;}
            .portlet-tool{flex-direction:column!important;align-items:flex-start!important;padding:6px!important;gap:6px!important;}
            .portlet-tool>div{width:100%;}
            .btn-group{width:100%;flex-wrap:wrap;}
            .btn-tool{font-size:8px!important;padding:0 6px!important;height:20px!important;}
            .input-inline,.select-tool{width:100%!important;font-size:9px!important;}
            .portlet-body{padding:0!important;overflow:hidden!important;}
            .grid-wrapper{width:100%!important;height:calc(100vh - 350px)!important;min-height:200px!important;overflow-x:auto!important;overflow-y:auto!important;-webkit-overflow-scrolling:touch!important;}
            .grid-table{font-size:8px!important;width:auto!important;min-width:1600px!important;table-layout:auto!important;}
            .grid-table th,.grid-table td{padding:2px 4px!important;height:22px!important;white-space:nowrap!important;}
            .filter-input{height:18px!important;font-size:8px!important;padding:0 3px!important;}
        }
        @media(max-width:480px){
            .grid-table{font-size:7px!important;min-width:1400px!important;}
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
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

    {{-- COLOR PICKER --}}
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

    {{-- CHANGE OP MODAL --}}
    <div class="overlay" id="change-op-overlay" onclick="if(event.target===this) closeChangeOp()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-user" style="color:#3b82f6;"></i> Change OP</div>
                <button class="modal-close" onclick="closeChangeOp()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="min-width:300px;">
                <div style="margin-bottom:12px;">
                    <label style="font-size:11px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Select User</label>
                    <select id="change-op-select" style="height:30px;font-size:11px;width:100%;border:1px solid #cbd5e1;border-radius:4px;padding:0 6px;">
                        <option value="">-- Select --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-tool" onclick="closeChangeOp()">Cancel</button>
                    <button class="btn-tool green" onclick="executeChangeOp()">Update</button>
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
            {{-- TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">MAWB List</span>
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
                        <button class="btn-tool" id="btn-copy" disabled title="Copy Selected (select 1 row)" onclick="copySelected()"><i class="fa fa-files-o"></i></button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-profit-s" disabled onclick="openProfitSummary()"><i class="fa fa-file-text-o"></i> Profit – Summary</button>
                        <button class="btn-tool" id="btn-profit-d" disabled onclick="openProfitDetail()"><i class="fa fa-file-text-o"></i> Profit – Detail</button>
                    </div>
                    <div class="btn-group">
                        <select class="select-tool" id="sel-op" disabled onchange="onOpChange(this)">
                            <option value="">Change OP</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;" placeholder="Quick search…" oninput="quickSearch(this.value)" value="{{ request('search') }}">
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
                                    <th class="sticky-col sticky-col-header" data-col="file_no" style="width:125px;left:53px;">File No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="mawb_no" style="width:130px;left:178px;">MAWB No.</th>
                                    <th data-col="oversea_agent" style="width:150px;">Oversea Agent</th>
                                    <th data-col="shipper" style="width:150px;">Shipper</th>
                                    <th data-col="hawb_shipper" style="width:150px;">HAWB Shipper</th>
                                    <th data-col="carrier" style="width:130px;">Carrier</th>
                                    <th data-col="destination" style="width:130px;">Destination</th>
                                    <th data-col="departure" style="width:130px;">Departure</th>
                                    <th data-col="eta" style="width:85px;">ETA</th>
                                    <th data-col="ata" style="width:85px;">ATA</th>
                                    <th data-col="etd" style="width:85px;">ETD</th>
                                    <th data-col="atd" style="width:85px;">ATD</th>
                                    <th data-col="hawb_no" style="width:130px;">HAWB No.</th>
                                    <th data-col="flight_no" style="width:100px;">Flight No.</th>
                                    <th data-col="sales" style="width:100px;">Sales</th>
                                    <th data-col="op" style="width:100px;">OP</th>
                                    <th data-col="post_date" style="width:85px;">Post Date</th>
                                </tr>

                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:53px;"><input class="filter-input" style="width:100%;" data-param="filter_file_no" placeholder="File…" oninput="applyFiltersTyping()"></td>
                                    <td class="sticky-col" style="left:178px;"><input class="filter-input" style="width:100%;" data-param="filter_mawb" placeholder="MAWB…" oninput="applyFiltersTyping()"></td>
                                    <td><input class="filter-input" style="width:100%;" data-param="filter_agent" placeholder="Agent…" oninput="applyFiltersTyping()"></td>
                                    <td><input class="filter-input" style="width:100%;" data-param="filter_shipper" placeholder="Shipper…" oninput="applyFiltersTyping()"></td>
                                    <td colspan="8"></td>
                                    <td><input class="filter-input" style="width:100%;" data-param="filter_hawb" placeholder="HAWB…" oninput="applyFiltersTyping()"></td>
                                    <td colspan="4"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @include('air-import.partials.list-rows', ['shipments' => $shipments])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $shipments->links('vendor.pagination.custom') }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $shipments->firstItem() ?? 0 }}</span>
                        – <span id="stat-last">{{ $shipments->lastItem() ?? 0 }}</span>
                        of <span id="stat-total">{{ $shipments->total() }}</span> records
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var _colorShipmentId = null;
    var searchTimer, filterTimer;
    var PINNED_COLS = ['check','lock','file_no','color','mawb_no'];

    var COLOR_OPTIONS = [
        { label:'Urgent',            value:'#E08283' },
        { label:'Ready to bill',     value:'#F3C200' },
        { label:'Ready to close',    value:'#25A69A' },
        { label:'Postpone',          value:'#4B77BE' },
        { label:'Freight Finalized', value:'#9B9B9B' },
    ];

    /* ── TOOLBAR ── */
    function updateToolbar() {
        var checked = [...document.querySelectorAll('.row-check:checked')];
        var all     = [...document.querySelectorAll('.row-check')];
        var n       = checked.length;
        var sa      = document.getElementById('select-all');
        if (sa) { sa.checked = n===all.length && all.length>0; sa.indeterminate = n>0 && n<all.length; }
        ['btn-delete','btn-block','btn-unblock'].forEach(id => {
            var el = document.getElementById(id); if (el) el.disabled = n===0;
        });
        var cp = document.getElementById('btn-copy'); if (cp) cp.disabled = n!==1;
        ['btn-profit-s','btn-profit-d','sel-op'].forEach(id => {
            var el = document.getElementById(id); if (el) el.disabled = n===0;
        });
        var badge = document.getElementById('sel-badge');
        if (badge) { badge.style.display = n>0?'inline':'none'; badge.textContent = n+' selected'; }
        document.querySelectorAll('#grid-body tr[data-id]').forEach(row => {
            var cb = row.querySelector('.row-check');
            row.classList.toggle('row-selected', cb && cb.checked);
        });
    }

    /* ── PROFIT REPORTS ── */
    function openProfitSummary() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        if (ids.length > 1) { showToast('info', 'Please select only 1 shipment'); return; }
        window.open('/air-import/' + ids[0] + '/profit-summary', '_blank');
    }

    function openProfitDetail() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        if (ids.length > 1) { showToast('info', 'Please select only 1 shipment'); return; }
        window.open('/air-import/' + ids[0] + '/profit-detail', '_blank');
    }

    function toggleSelectAll(el) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = el.checked);
        updateToolbar();
    }

    function rowClick(e, row) {
        if (['A','INPUT','BUTTON','I','SELECT'].includes(e.target.tagName)) return;
        var cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    /* ── AJAX GRID UPDATE ── */
    async function updateGrid(url) {
        try {
            var res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Bad response');
            var data = await res.json();
            if (data.html !== undefined) {
                document.getElementById('grid-body').innerHTML = data.html;
                document.getElementById('pagination-container').innerHTML = data.pagination || '';
                document.getElementById('stat-first').textContent = data.first || 0;
                document.getElementById('stat-last').textContent = data.last || 0;
                document.getElementById('stat-total').textContent = data.total || 0;
            } else {
                var doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                var nb = doc.getElementById('grid-body');
                var np = doc.getElementById('pagination-container');
                if (nb) document.getElementById('grid-body').innerHTML = nb.innerHTML;
                if (np) document.getElementById('pagination-container').innerHTML = np.innerHTML;
            }
            updateToolbar();
        } catch(e) { showToast('error','Failed to refresh grid'); }
    }

    document.addEventListener('click', function(e) {
        var link = e.target.closest('#pagination-container a, .tp-pagination a');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    /* ── SEARCH & FILTER ── */
    function quickSearch(val) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            var url = new URL(window.location.href);
            if (val.trim()) url.searchParams.set('search', val.trim());
            else url.searchParams.delete('search');
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 300);
    }

    function applyFiltersTyping() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(applyFilters, 400);
    }

    function applyFilters() {
        var url = new URL(window.location.href);
        document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
            var param = inp.dataset.param;
            if (param) {
                if (inp.value.trim()) url.searchParams.set(param, inp.value.trim());
                else url.searchParams.delete(param);
            }
        });
        url.searchParams.delete('page');
        updateGrid(url.toString());
    }

    var filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        document.getElementById('filter-row').style.display = filterOpen ? 'table-row' : 'none';
        document.getElementById('btn-filter').classList.toggle('active', filterOpen);
        if (filterOpen) {
            var params = new URLSearchParams(window.location.search);
            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                if (inp.dataset.param) inp.value = params.get(inp.dataset.param) || '';
            });
        } else {
            document.querySelectorAll('#filter-row .filter-input').forEach(i => i.value='');
            applyFilters();
        }
    }

    /* ── COLUMN CONFIG ── */
    function toggleConfig() {
        var panel = document.getElementById('config-panel');
        var open = panel.style.display==='none';
        panel.style.display = open ? 'block' : 'none';
        document.getElementById('btn-config').classList.toggle('active', open);
        if (open) buildConfigPanel();
    }

    function buildConfigPanel() {
        var cont = document.getElementById('col-toggles'); cont.innerHTML='';
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (PINNED_COLS.includes(th.dataset.col)) return;
            var label = document.createElement('label');
            var cb = document.createElement('input'); cb.type='checkbox'; cb.checked=th.style.display!=='none';
            cb.onchange = () => toggleColumn(th.dataset.col, cb.checked);
            label.appendChild(cb); label.append(' '+th.textContent.trim()); cont.appendChild(label);
        });
    }

    function toggleColumn(col, show) {
        var th = document.querySelector('#header-row th[data-col="'+col+'"]');
        var idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show?'':'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(row => {
            var cell = row.querySelectorAll('td,th')[idx];
            if (cell) cell.style.display = show?'':'none';
        });
    }

    document.addEventListener('click', e => {
        var panel = document.getElementById('config-panel');
        var btn = document.getElementById('btn-config');
        if (panel && panel.style.display!=='none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display='none'; btn.classList.remove('active');
        }
    });

    /* ── LOCK TOGGLE ── */
    function toggleLock(el) {
        var row = el.closest('tr');
        var id = row.dataset.id;
        var locked = el.classList.contains('fa-lock');
        var url = locked ? '{{ route("air-import.bulk-unblock") }}' : '{{ route("air-import.bulk-block") }}';
        fetch(url, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({ids:[id]})
        }).then(r=>r.json()).then(d => {
            if (d.success) {
                el.classList.toggle('fa-lock', !locked);
                el.classList.toggle('fa-unlock', locked);
                el.style.color = locked ? '#22c55e' : '#94a3b8';
                el.title = locked ? 'Unlocked' : 'Locked';
                showToast('success', locked ? 'Shipment unlocked' : 'Shipment locked');
            } else showToast('error', d.message||'Failed');
        }).catch(() => showToast('error','Failed to update lock'));
    }

    /* ── DELETE ── */
    function confirmDelete() {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent = 'You are about to permanently delete '+n+' shipment(s).';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds(); if (!ids.length) return;
        showToast('info','Deleting...');
        fetch('{{ route("air-import.bulk-delete") }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({ids})
        }).then(r=>r.json()).then(d => {
            if (d.success) { showToast('success',d.message); updateGrid(window.location.href); }
            else showToast('error',d.message||'Delete failed');
        }).catch(()=>showToast('error','Delete failed'));
    }

    /* ── COPY ── */
    var _isCopying = false;
    function copySelected() {
        if (_isCopying) return;
        var ids = getSelectedIds(); if (ids.length!==1) return;
        _isCopying = true;
        var cp = document.getElementById('btn-copy'); if (cp) cp.disabled = true;
        showToast('info', 'Copying shipment...');
        window.location.href = '{{ route("air-import.create") }}?copy='+ids[0];
    }

    /* ── BLOCK / UNBLOCK ── */
    function blockSelected()   { bulkAction('{{ route("air-import.bulk-block") }}',   'Blocked'); }
    function unblockSelected() { bulkAction('{{ route("air-import.bulk-unblock") }}', 'Unblocked'); }
    function bulkAction(url, label) {
        var ids = getSelectedIds(); if (!ids.length) return;
        showToast('info', label+'...');
        fetch(url, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({ids})
        }).then(r=>r.json()).then(d => {
            if (d.success) { showToast('success',d.message); updateGrid(window.location.href); }
            else showToast('error',d.message||label+' failed');
        }).catch(()=>showToast('error',label+' failed'));
    }

    /* ── CHANGE OP ── */
    function onOpChange(el) {
        if (!el.value) return;
        document.getElementById('change-op-select').value = el.value;
        document.getElementById('change-op-overlay').classList.add('open');
        el.value='';
    }
    function closeChangeOp() { document.getElementById('change-op-overlay').classList.remove('open'); }
    function executeChangeOp() {
        var userId = document.getElementById('change-op-select').value;
        if (!userId) { showToast('error','Select a user'); return; }
        var ids = getSelectedIds(); if (!ids.length) { closeChangeOp(); return; }
        fetch('{{ route("air-import.bulk-change-op") }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({ids, op_id: userId})
        }).then(r=>r.json()).then(d => {
            if (d.success) { showToast('success',d.message); updateGrid(window.location.href); }
            else showToast('error',d.message||'Update failed');
        }).catch(()=>showToast('error','Update failed'));
        closeChangeOp();
    }

    /* ── COLOR PICKER ── */
    function openColorPicker(id, current) {
        _colorShipmentId = id;
        var grid = document.getElementById('color-picker-grid');
        grid.innerHTML = COLOR_OPTIONS.map(o => {
            const active = o.value === current;
            return `<div class="color-picker-opt ${active ? 'active' : ''}" onclick="selectColor('${o.value}', this)"><span class="swatch" style="background:${o.value}"></span><span>${o.label}</span><i class="fa fa-check"></i></div>`;
        }).join('');
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function selectColor(color, el) {
        document.querySelectorAll('.color-picker-opt').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        const id = _colorShipmentId;
        fetch('{{ route("air-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#shipment-row-${id} .color-mark`);
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            } else {
                showToast('error', data.message || 'Failed to update color');
            }
        }).catch(err => {
            console.error('Color update error:', err);
            showToast('error', 'Failed to update color');
        });
        closeColorPicker();
    }

    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorShipmentId = null;
    }

    function clearColor() {
        if (!_colorShipmentId) return;
        const id = _colorShipmentId;
        fetch('{{ route("air-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#shipment-row-${id} .color-mark`);
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            } else {
                showToast('error', data.message || 'Failed to clear color');
            }
        }).catch(err => {
            console.error('Color clear error:', err);
            showToast('error', 'Failed to clear color');
        });
        closeColorPicker();
    }

    /* ── EXPORT ── */
    function exportCsv() {
        showToast('info', 'Preparing Excel export...');
        var url = new URL('/air-import/export-csv', window.location.origin);
        var q = document.getElementById('quick-search')?.value?.trim();
        if (q) url.searchParams.set('search', q);
        document.querySelectorAll('#filter-row .filter-input').forEach(function(inp) {
            if (inp.value.trim() && inp.dataset.param) url.searchParams.set(inp.dataset.param, inp.value.trim());
        });
        
        var iframe = document.getElementById('download-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'download-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url.toString();
        setTimeout(function() { showToast('success', 'Excel export started!'); }, 800);
    }

    /* ── TOAST ── */
    function showToast(type, msg) {
        var icons = {success:'check-circle',error:'times-circle',info:'info-circle'};
        var t = document.createElement('div');
        t.className='toast '+type;
        t.innerHTML='<i class="fa fa-'+(icons[type]||'info-circle')+'"></i> '+msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(()=>t.remove(),3500);
    }

    @if(session('success')) showToast('success', @json(session('success'))); @endif
    @if(session('error'))   showToast('error',   @json(session('error')));   @endif
    </script>
    @endpush
</x-layout>
