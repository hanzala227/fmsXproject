@php use Illuminate\Support\Str; @endphp
<x-layout>
    @push('styles')
    <x-list-styles />
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this) closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Automobile(s)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <div class="overlay color-picker-overlay" id="color-picker-overlay" onclick="if(event.target===this) closeColorPicker()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-paint-brush" style="color:#3b82f6;"></i> Status Color</div>
                <button class="modal-close" onclick="closeColorPicker()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <div class="color-picker-list" id="color-picker-list"></div>
                <div class="color-clear-btn" onclick="clearColor()">Clear / No Color</div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Warehouse <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">Automobile List</span></li>
            </ul>
        </div>

        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Automobile List</span>
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
                    <a class="btn-action-round white" href="{{ route('warehouse.automobile.export-csv') }}" title="Download as CSV" target="_blank">
                        <i class="fa fa-file-excel-o"></i> Excel <i class="fa fa-angle-down"></i>
                    </a>
                </div>
            </div>

            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <button class="btn-tool green" onclick="window.location.href='{{ route('warehouse.automobile.create') }}'" title="New Automobile">
                            <i class="fa fa-plus"></i>
                        </button>
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
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                </div>
            </div>

            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="vin" style="width:130px;left:50px;">VIN No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="receipt" style="width:110px;left:180px;">WH Receipt No.</th>
                                    <th data-col="received_date" style="width:90px;cursor:pointer;" onclick="doSort('received_date')">Received Date <i class="fa" id="sort-received_date"></i></th>
                                    <th data-col="customer" style="width:160px;">Customer</th>
                                    <th data-col="maker" style="width:80px;cursor:pointer;" onclick="doSort('maker')">Maker <i class="fa" id="sort-maker"></i></th>
                                    <th data-col="year" style="width:60px;text-align:center;cursor:pointer;" onclick="doSort('year')">Year <i class="fa" id="sort-year"></i></th>
                                    <th data-col="model" style="width:100px;cursor:pointer;" onclick="doSort('model')">Model <i class="fa" id="sort-model"></i></th>
                                    <th data-col="engine_no" style="width:120px;">Engine No.</th>
                                    <th data-col="manufacture_date" style="width:90px;cursor:pointer;" onclick="doSort('manufacture_date')">Mfg Date <i class="fa" id="sort-manufacture_date"></i></th>
                                    <th data-col="title" style="width:70px;text-align:center;">Title</th>
                                    <th data-col="office" style="width:60px;">Office</th>
                                    <th data-col="received_by" style="width:100px;">Received By</th>
                                </tr>
                                <tr id="filter-row" style="display:none;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:50px;"><input class="filter-input" data-col-idx="2" placeholder="VIN..." oninput="applyFilters()"></td>
                                    <td class="sticky-col" style="left:180px;"></td>
                                    <td class="sticky-col" style="left:180px;"></td>
                                    <td><input class="filter-input" data-col-idx="5" placeholder="Date..." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="6" placeholder="Customer..." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="7" placeholder="Maker..." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="8" placeholder="Year..." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="9" placeholder="Model..." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="10" placeholder="Engine..." oninput="applyFilters()"></td>
                                    <td></td>
                                    <td></td>
                                    <td><input class="filter-input" data-col-idx="13" placeholder="Office..." oninput="applyFilters()"></td>
                                    <td style="text-align:center;"><button class="btn-tool green" onclick="applyFilters()" style="height:18px;">Filter</button></td>
                                </tr>
                            </thead>
                            <tbody id="grid-body">
                                @forelse($automobiles as $auto)
                                <tr id="auto-row-{{ $auto->id }}"
                                    data-id="{{ $auto->id }}"
                                    data-vin="{{ $auto->vin_no }}"
                                    onclick="rowClick(event, this)"
                                >
                                    <td class="sticky-col" style="width:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <input type="checkbox" name="ids[]" value="{{ $auto->id }}" class="row-check" onchange="updateToolbar()">
                                    </td>
                                    <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <i class="fa {{ $auto->is_blocked ? 'fa-lock' : 'fa-unlock' }}" style="color:{{ $auto->is_blocked ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" title="{{ $auto->is_blocked ? 'Blocked' : 'Active' }}" onclick="toggleLock(this)"></i>
                                    </td>
                                    <td class="sticky-col" style="width:130px;left:50px;">
                                        <a href="{{ route('warehouse.automobile.show', $auto) }}" class="col-link" onclick="event.stopPropagation()">{{ $auto->vin_no }}</a>
                                    </td>
                                    <td class="sticky-col" style="width:110px;left:180px;">
                                        <span>{{ $auto->wh_receipt_no ?? '--' }}</span>
                                    </td>
                                    <td>{{ $auto->received_date ? $auto->received_date->format('m-d-Y') : '--' }}</td>
                                    <td>{{ Str::limit($auto->customer->name ?? '--', 25) }}</td>
                                    <td>{{ $auto->maker ?? '--' }}</td>
                                    <td style="text-align:center;">{{ $auto->year ?? '--' }}</td>
                                    <td>{{ $auto->model ?? '--' }}</td>
                                    <td>{{ $auto->engine_no ?? '--' }}</td>
                                    <td>{{ $auto->manufacture_date ? $auto->manufacture_date->format('m-d-Y') : '--' }}</td>
                                    <td style="text-align:center;">
                                        <span class="badge-status {{ $auto->title_received ? 'bg-green' : 'bg-yellow' }}">
                                            {{ $auto->title_received ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>{{ $auto->office->code ?? '--' }}</td>
                                    <td>{{ $auto->receiver->name ?? '--' }}</td>
                                </tr>
                                @empty
                                <tr id="empty-row">
                                    <td colspan="15" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-car" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No automobiles found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $automobiles->links() }}</div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="display:flex;align-items:center;gap:3px;font-size:10px;color:#64748b;">
                            <span>Show</span>
                            <select id="page-size" class="input-inline" style="width:50px;" onchange="changePageSize(this.value)">
                                <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span>per page</span>
                        </div>
                        <div style="font-size:10px;color:#64748b;">
                            Showing <span id="stat-first">{{ $automobiles->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $automobiles->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $automobiles->total() }}</span> records
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    var COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' },
    ];
    var PINNED_COLS = ['check', 'lock', 'vin', 'color_mark', 'receipt'];
    var _autoId = null;

    function updateToolbar() {
        const checked  = [...document.querySelectorAll('.row-check:checked')];
        const all      = [...document.querySelectorAll('.row-check')];
        const n        = checked.length;
        const sa       = document.getElementById('select-all');
        sa.checked        = n === all.length && all.length > 0;
        sa.indeterminate  = n > 0 && n < all.length;

        document.getElementById('btn-delete').disabled  = n === 0;
        document.getElementById('btn-copy').disabled    = n !== 1;
        document.getElementById('btn-block').disabled   = n === 0;
        document.getElementById('btn-unblock').disabled = n === 0;

        const badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent   = n + ' selected';

        document.querySelectorAll('#grid-body tr[data-id]').forEach(row => {
            const cb = row.querySelector('.row-check');
            row.classList.toggle('row-selected', cb && cb.checked);
        });
    }

    function toggleSelectAll(el) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = el.checked);
        updateToolbar();
    }

    function rowClick(e, row) {
        const skip = ['A', 'INPUT', 'BUTTON', 'I'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('confirm-msg').textContent =
            `You are about to permanently delete ${n} automobile(s). This cannot be undone.`;
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        const ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Deleting...');
        fetch('{{ route("warehouse.automobile.bulk-delete") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
            body: JSON.stringify({ ids }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showToast('success', data.message || 'Deleted successfully');
                // Remove rows from DOM
                ids.forEach(id => {
                    const row = document.getElementById('auto-row-' + id);
                    if (row) row.remove();
                });
                // Update pagination stats
                const totalEl = document.getElementById('stat-total');
                const firstEl = document.getElementById('stat-first');
                const lastEl = document.getElementById('stat-last');
                if (totalEl) {
                    const oldTotal = parseInt(totalEl.textContent) || 0;
                    totalEl.textContent = Math.max(0, oldTotal - ids.length);
                }
                if (firstEl) firstEl.textContent = '0';
                if (lastEl) lastEl.textContent = '0';
                // If no rows left, show empty state
                if (document.querySelectorAll('#grid-body tr[data-id]').length === 0) {
                    document.getElementById('grid-body').innerHTML =
                        '<tr id="empty-row"><td colspan="15" style="text-align:center;padding:30px 10px;color:#94a3b8;">' +
                        '<i class="fa fa-car" style="font-size:28px;display:block;margin-bottom:8px;"></i>' +
                        'No automobiles found.</td></tr>';
                }
                updateToolbar();
            } else {
                showToast('error', data.message || 'Failed to delete');
            }
        }).catch(() => showToast('error', 'Failed to delete'));
    }

    function blockSelected() {
        const ids = getSelectedIds();
        if (!ids.length) return;
        fetch('{{ route('warehouse.automobile.bulk-block') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids })
        }).then(r => r.json()).then(d => {
            if (d.success) {
                showToast('success', d.message);
                // Update lock icons in the grid
                ids.forEach(id => {
                    const row = document.getElementById('auto-row-' + id);
                    if (row) {
                        const icon = row.querySelector('td:nth-child(2) .fa');
                        if (icon) {
                            icon.className = 'fa fa-lock';
                            icon.style.color = '#94a3b8';
                            icon.title = 'Blocked';
                        }
                    }
                });
            } else {
                showToast('error', d.message);
            }
        }).catch(() => showToast('error', 'Failed to block.'));
    }
    function unblockSelected() {
        const ids = getSelectedIds();
        if (!ids.length) return;
        fetch('{{ route('warehouse.automobile.bulk-unblock') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids })
        }).then(r => r.json()).then(d => {
            if (d.success) {
                showToast('success', d.message);
                // Update lock icons in the grid
                ids.forEach(id => {
                    const row = document.getElementById('auto-row-' + id);
                    if (row) {
                        const icon = row.querySelector('td:nth-child(2) .fa');
                        if (icon) {
                            icon.className = 'fa fa-unlock';
                            icon.style.color = '#22c55e';
                            icon.title = 'Active';
                        }
                    }
                });
            } else {
                showToast('error', d.message);
            }
        }).catch(() => showToast('error', 'Failed to unblock.'));
    }

    function toggleLock(el) {
        const row = el.closest('tr[data-id]');
        if (!row) return;
        const id = row.dataset.id;
        const wasLocked = el.classList.contains('fa-lock');
        const newBlocked = !wasLocked;

        // Optimistic UI update
        el.classList.toggle('fa-lock',   newBlocked);
        el.classList.toggle('fa-unlock', !newBlocked);
        el.style.color = newBlocked ? '#94a3b8' : '#22c55e';
        el.title = newBlocked ? 'Blocked' : 'Active';

        fetch('/warehouse/automobile/' + id + '/toggle-block', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_blocked: newBlocked })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showToast('success', d.message || (newBlocked ? 'Automobile blocked.' : 'Automobile unblocked.'));
            } else {
                throw new Error(d.message || 'Failed');
            }
        })
        .catch(() => {
            // Revert on failure
            el.classList.toggle('fa-lock',   wasLocked);
            el.classList.toggle('fa-unlock', !wasLocked);
            el.style.color = wasLocked ? '#94a3b8' : '#22c55e';
            el.title = wasLocked ? 'Blocked' : 'Active';
            showToast('error', 'Failed to update block status.');
        });
    }

    function toggleFilter() {
        var filterRow = document.getElementById('filter-row');
        var isVisible = filterRow.style.display === 'table-row';
        filterRow.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);
        if (!isVisible) {
            const urlParams = new URLSearchParams(window.location.search);
            document.querySelectorAll('.filter-input').forEach(inp => {
                const idx = parseInt(inp.dataset.colIdx);
                if (idx === 2) inp.value = urlParams.get('filter_vin_no') || '';
                else if (idx === 5) inp.value = urlParams.get('filter_received_date') || '';
                else if (idx === 6) inp.value = urlParams.get('filter_customer') || '';
                else if (idx === 7) inp.value = urlParams.get('filter_maker') || '';
                else if (idx === 8) inp.value = urlParams.get('filter_year') || '';
                else if (idx === 9) inp.value = urlParams.get('filter_model') || '';
                else if (idx === 10) inp.value = urlParams.get('filter_engine_no') || '';
                else if (idx === 13) inp.value = urlParams.get('filter_office') || '';
            });
            document.querySelector('.filter-input')?.focus();
        } else {
            document.querySelectorAll('.filter-input').forEach(i => { i.value = ''; });
            applyFilters();
        }
    }

    function toggleConfig() {
        const panel = document.getElementById('config-panel');
        const open  = panel.style.display === 'none';
        panel.style.display = open ? 'block' : 'none';
        document.getElementById('btn-config').classList.toggle('active', open);
        if (open) buildConfigPanel();
    }

    function buildConfigPanel() {
        const container = document.getElementById('col-toggles');
        container.innerHTML = '';
        document.querySelectorAll('#header-row th[data-col]').forEach(th => {
            if (PINNED_COLS.includes(th.dataset.col)) return;
            const label = document.createElement('label');
            const cb    = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.onchange = () => toggleColumn(th.dataset.col, cb.checked);
            label.appendChild(cb);
            label.append(' ' + th.textContent.trim());
            container.appendChild(label);
        });
    }

    function toggleColumn(colName, show) {
        const th  = document.querySelector(`#header-row th[data-col="${colName}"]`);
        const idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(row => {
            const cell = row.querySelectorAll('td, th')[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn   = document.getElementById('btn-config');
        if (panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

    function openColorPicker(id, currentColor) {
        _autoId = id;
        const list = document.getElementById('color-picker-list');
        list.innerHTML = COLOR_OPTIONS.map(o => {
            const active = o.value === currentColor;
            return `<div class="color-picker-opt ${active ? 'active' : ''}" onclick="selectColor('${o.value}', this)"><span class="swatch" style="background:${o.value}"></span><span>${o.label}</span><i class="fa fa-check"></i></div>`;
        }).join('');
        document.getElementById('color-picker-overlay').classList.add('open');
    }

    function selectColor(color, el) {
        document.querySelectorAll('.color-picker-opt').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        const id = _autoId;
        fetch('{{ route("warehouse.automobile.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ color }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#auto-row-${id} .color-mark`);
                if (span) span.style.background = color;
                showToast('success', 'Status color updated');
            }
        }).catch(() => showToast('error', 'Failed to update color'));
        closeColorPicker();
    }

    function closeColorPicker() { document.getElementById('color-picker-overlay').classList.remove('open'); _autoId = null; }
    function clearColor() {
        const id = _autoId;
        fetch('{{ route("warehouse.automobile.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ color: '' }),
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const span = document.querySelector(`#auto-row-${id} .color-mark`);
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Status color cleared');
            }
        }).catch(() => showToast('error', 'Failed to clear color'));
        closeColorPicker();
    }

    function copySelected() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        const row = checked[0].closest('tr');
        showToast('info', 'Copying automobile: ' + (row.dataset.vin || '') + ' ...');
        setTimeout(() => {
            window.location.href = '/warehouse/automobile/create?copy=' + row.dataset.id;
        }, 600);
    }

    function getSelectedIds() { return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value); }

    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.innerHTML = `<i class="fa fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }

    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif

    // Init sort icons
    (function() {
        const params = new URLSearchParams(window.location.search);
        const sort = params.get('sort') || 'created_at';
        const dir = params.get('dir') || 'desc';
        const icon = document.getElementById('sort-' + sort);
        if (icon) {
            icon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');
        }
    })();

    function doSort(field) {
        const params = new URLSearchParams(window.location.search);
        const currentSort = params.get('sort') || '';
        const currentDir = params.get('dir') || 'desc';
        let newDir = 'asc';
        if (currentSort === field) {
            newDir = currentDir === 'asc' ? 'desc' : 'asc';
        }
        params.set('sort', field);
        params.set('dir', newDir);
        updateGrid(window.location.pathname + '?' + params.toString());
    }

    function changePageSize(size) {
        const params = new URLSearchParams(window.location.search);
        params.set('limit', size);
        params.set('page', '1');
        document.getElementById('page-size').value = size;
        updateGrid(window.location.pathname + '?' + params.toString());
    }

    /* ================================================================
       AJAX GRID UPDATE (match Ocean Import pattern)
    ================================================================ */
    async function updateGrid(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newBody = doc.getElementById('grid-body');
            const newPagination = doc.getElementById('pagination-container');

            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            // Update stats
            const stats = doc.querySelector('.portlet-tool.bottom div:last-child');
            if (stats) {
                const matches = stats.textContent.match(/\d+/g);
                if (matches && matches.length >= 3) {
                    document.getElementById('stat-first').textContent = matches[0];
                    document.getElementById('stat-last').textContent = matches[1];
                    document.getElementById('stat-total').textContent = matches[2];
                }
            }

            // Update URL in address bar without full reload
            window.history.pushState({}, '', url);

            // Re-initialize sort icons
            const params = new URLSearchParams(new URL(url).search);
            const sort = params.get('sort') || 'created_at';
            const dir = params.get('dir') || 'desc';
            document.querySelectorAll('[id^="sort-"]').forEach(i => i.className = 'fa');
            const icon = document.getElementById('sort-' + sort);
            if (icon) {
                icon.className = 'fa ' + (dir === 'asc' ? 'fa-sort-asc' : 'fa-sort-desc');
            }

            updateToolbar();
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update grid');
        }
    }

    // Wire pagination links to use AJAX
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    // Override quickSearch to use updateGrid instead of full reload
    var searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const url = new URL(window.location.href);
            if (!q) url.searchParams.delete('search'); else url.searchParams.set('search', q);
            updateGrid(url.toString());
        }, 300);
    }

    var filterDebounce;
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            const inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            var url = new URL(window.location.href);
            url.search = '';
            const filterMap = { 2: 'filter_vin_no', 5: 'filter_received_date', 6: 'filter_customer', 7: 'filter_maker', 8: 'filter_year', 9: 'filter_model', 10: 'filter_engine_no', 13: 'filter_office' };
            inputs.forEach(inp => {
                const v = inp.value.trim();
                if (!v) return;
                const param = filterMap[inp.dataset.colIdx];
                if (param) url.searchParams.set(param, v);
            });
            updateGrid(url.toString());
        }, 300);
    }
    </script>
    @endpush
</x-layout>
