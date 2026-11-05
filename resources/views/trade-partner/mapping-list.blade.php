<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .sticky-col { position: sticky; left: 0; z-index: 5; background: #fff; border-right: 1px solid #cbd5e1 !important; }
        .sticky-col-header { z-index: 15 !important; background: #f8fafc !important; }
        .grid-table tr:hover .sticky-col { background-color: #f1f5f9 !important; }
        .grid-table tr.row-selected .sticky-col { background-color: #eff6ff !important; }
        .badge-status.bg-blue { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 1px 5px; border-radius: 2px; font-size: 9px; font-weight: 600; white-space: nowrap; }
        .grid-wrapper { min-height: 250px; }
    </style>
    @endpush

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="delete-overlay" onclick="if(event.target===this) closeDeleteModal()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4 id="delete-title">Delete Mapping(s)?</h4>
            <p id="delete-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- CREATE/EDIT MODAL --}}
    <div class="overlay" id="mapping-modal-overlay" onclick="if(event.target===this) closeMappingModal()">
        <div class="confirm-box" style="width:550px;max-width:90vw;">
            <div class="confirm-icon" style="color:#3b82f6;"><i class="fa fa-code-fork"></i></div>
            <h4 id="modal-title">Add Mapping</h4>
            <div style="padding:10px 0;">
                <div class="form-group" style="margin-bottom:10px;">
                    <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:3px;">Target <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="mapping-target" class="input-inline" style="width:100%;" placeholder="e.g. FREIGHTX CO.">
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:3px;">Status</label>
                    <select id="mapping-status" class="input-inline" style="width:100%;height:26px;">
                        <option value="">— Select —</option>
                        <option value="Created by System">Created by System</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="row" style="display:flex;gap:10px;margin-bottom:10px;">
                    <div class="form-group" style="flex:1;">
                        <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:3px;">Sender ID</label>
                        <input type="text" id="mapping-sender-id" class="input-inline" style="width:100%;" placeholder="e.g. EVAL-CAD">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:3px;">Key</label>
                        <input type="text" id="mapping-key" class="input-inline" style="width:100%;" placeholder="e.g. FREIGHTX CO.">
                    </div>
                </div>
                <div class="row" style="display:flex;gap:10px;margin-bottom:10px;">
                    <div class="form-group" style="flex:1;">
                        <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:3px;">Init Target Code</label>
                        <input type="text" id="mapping-ic" class="input-inline" style="width:100%;" placeholder="e.g. TP-003674">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:3px;">Target Code</label>
                        <input type="text" id="mapping-tc" class="input-inline" style="width:100%;" placeholder="e.g. TP-003674">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label style="display:block;font-size:10px;font-weight:600;color:#475569;margin-bottom:3px;">Init Target (Customer)</label>
                    <select id="mapping-tp" class="input-inline" style="width:100%;height:26px;">
                        <option value="">— Select Customer —</option>
                        @foreach($tradePartners as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->name }} ({{ $tp->code ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeMappingModal()">Cancel</button>
                <button class="btn-tool" style="padding:0 18px;height:26px;background:#3b82f6;color:#fff;border-color:#2563eb;" onclick="saveMapping()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>

    {{-- MAIN PAGE --}}
    <div class="page-content">

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/trade-partner/list">Customers</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #1e293b; font-weight: 700;">Customer Mapping List</span></li>
            </ul>
        </div>

        <div class="portlet light">

            {{-- PORTLET TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Customer Mapping List</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" id="btn-filter" onclick="toggleFilter()" title="Toggle filter row">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <button class="btn-tool green" onclick="openCreateModal()" title="Add Mapping">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                </div>
            </div>

            {{-- TABLE --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="main-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" style="width:28px;text-align:center;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" style="left:28px;min-width:160px;">Target</th>
                                    <th style="min-width:110px;">Status</th>
                                    <th style="min-width:100px;">Sender ID</th>
                                    <th style="min-width:120px;">Key</th>
                                    <th style="min-width:110px;">Init Target Code</th>
                                    <th style="min-width:150px;">Init Target</th>
                                    <th style="min-width:110px;">Target Code</th>
                                    <th style="min-width:130px;">Created At</th>
                                    <th style="width:50px;text-align:center;">Actions</th>
                                </tr>

                                {{-- FILTER ROW --}}
                                <tr id="filter-row" style="display:none;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:28px;"><input class="filter-input" data-param="filter_target" placeholder="Target..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td>
                                        <select class="filter-input" data-param="filter_status" onchange="applyFilters()" style="height:18px;">
                                            <option value="">All</option>
                                            <option value="Created by System">Created by System</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </td>
                                    <td><input class="filter-input" data-param="filter_sender_id" placeholder="Sender ID..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_key" placeholder="Key..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td></td>
                                    <td>
                                        <select class="filter-input" data-param="filter_tp" onchange="applyFilters()" style="height:18px;">
                                            <option value="">All</option>
                                            @foreach($tradePartners as $tp)
                                                <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:center;"><button class="btn-tool green" onclick="applyFilters()" style="height:18px;font-size:10px;">Filter</button></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                            @include('trade-partner.partials.mapping-table-rows')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- PAGINATION --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $mappings->links('vendor.pagination.custom') }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $mappings->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $mappings->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $mappings->total() }}</span> records
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    /* ================================================================
       TOOLBAR — checkbox management
    ================================================================ */
    var _editId = null;

    function updateToolbar() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const all     = [...document.querySelectorAll('.row-check')];
        const n       = checked.length;
        const sa      = document.getElementById('select-all');
        sa.checked       = n === all.length && all.length > 0;
        sa.indeterminate = n > 0 && n < all.length;

        document.getElementById('btn-delete').disabled = n === 0;
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

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    /* ================================================================
       AJAX GRID UPDATE
    ================================================================ */
    async function updateGrid(url) {
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Network error');
            const data = await response.json();
            document.getElementById('grid-body').innerHTML = data.html;
            document.getElementById('pagination-container').innerHTML = data.pagination;
            document.getElementById('stat-first').textContent = data.first;
            document.getElementById('stat-last').textContent = data.last;
            document.getElementById('stat-total').textContent = data.total;
            updateToolbar();
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update grid');
        }
    }

    /* ================================================================
       PAGINATION — event delegation for AJAX
    ================================================================ */
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.tp-pagination a.tp-page-btn');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    /* ================================================================
       QUICK SEARCH
    ================================================================ */
    var searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const q = val.trim();
            const url = new URL(window.location.href);
            if (!q) url.searchParams.delete('search'); else url.searchParams.set('search', q);
            url.searchParams.delete('page');
            updateGrid(url.toString());
        }, 400);
    }

    /* ================================================================
       FILTERS
    ================================================================ */
    var filterDebounce;
    function applyFiltersTyping() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(applyFilters, 400);
    }
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(() => {
            var url = new URL(window.location.href);
            url.search = '';
            var searchVal = document.getElementById('quick-search')?.value?.trim();
            if (searchVal) url.searchParams.set('search', searchVal);
            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                var v = inp.value?.trim();
                var param = inp.dataset.param;
                if (param && v) url.searchParams.set(param, v);
            });
            updateGrid(url.toString());
        }, 200);
    }

    function toggleFilter() {
        var filterRow = document.getElementById('filter-row');
        var isVisible = filterRow.style.display === 'table-row';
        filterRow.style.display = isVisible ? 'none' : 'table-row';
        document.getElementById('btn-filter').classList.toggle('active', !isVisible);
        if (!isVisible) {
            const params = new URLSearchParams(window.location.search);
            document.querySelectorAll('#filter-row .filter-input').forEach(inp => {
                const param = inp.dataset.param;
                if (param) {
                    const val = params.get(param);
                    if (val) inp.value = val;
                }
            });
            document.querySelector('#filter-row .filter-input')?.focus();
        } else {
            document.querySelectorAll('#filter-row .filter-input').forEach(i => { i.value = ''; });
            applyFilters();
        }
    }

    /* ================================================================
       DELETE
    ================================================================ */
    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('delete-title').textContent = 'Delete Mapping(s)?';
        document.getElementById('delete-msg').textContent = 'You are about to delete ' + n + ' mapping(s). This cannot be undone.';
        document.getElementById('delete-overlay').classList.add('open');
    }
    function closeDeleteModal() {
        document.getElementById('delete-overlay').classList.remove('open');
    }
    function executeDelete() {
        closeDeleteModal();
        const ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Deleting...');
        fetch('{{ route("trade-partner.mapping.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showToast('success', data.message || 'Deleted successfully');
                updateGrid(window.location.href);
            } else {
                showToast('error', data.message || 'Failed to delete');
            }
        }).catch(() => showToast('error', 'Failed to delete'));
    }

    /* ================================================================
       CREATE / EDIT MAPPING MODAL
    ================================================================ */
    function openCreateModal() {
        _editId = null;
        document.getElementById('modal-title').textContent = 'Add Mapping';
        document.getElementById('mapping-target').value = '';
        document.getElementById('mapping-status').value = '';
        document.getElementById('mapping-sender-id').value = '';
        document.getElementById('mapping-key').value = '';
        document.getElementById('mapping-ic').value = '';
        document.getElementById('mapping-tc').value = '';
        document.getElementById('mapping-tp').value = '';
        document.getElementById('mapping-modal-overlay').classList.add('open');
    }

    function closeMappingModal() {
        document.getElementById('mapping-modal-overlay').classList.remove('open');
    }

    function editMapping(id) {
        var row = document.getElementById('map-row-' + id);
        if (!row) return;
        _editId = id;
        document.getElementById('modal-title').textContent = 'Edit Mapping';
        document.getElementById('mapping-target').value = row.dataset.target || '';
        document.getElementById('mapping-status').value = row.dataset.status || '';
        document.getElementById('mapping-sender-id').value = row.dataset.senderId || '';
        document.getElementById('mapping-key').value = row.dataset.key || '';
        document.getElementById('mapping-ic').value = row.dataset.ic || '';
        document.getElementById('mapping-tc').value = row.dataset.tc || '';
        document.getElementById('mapping-tp').value = row.dataset.tpId || '';
        document.getElementById('mapping-modal-overlay').classList.add('open');
    }

    function saveMapping() {
        var target = document.getElementById('mapping-target').value.trim();
        var status = document.getElementById('mapping-status').value;
        var senderId = document.getElementById('mapping-sender-id').value.trim();
        var key = document.getElementById('mapping-key').value.trim();
        var ic = document.getElementById('mapping-ic').value.trim();
        var tc = document.getElementById('mapping-tc').value.trim();
        var tp = document.getElementById('mapping-tp').value;

        var payload = {
            target: target || null,
            status: status || null,
            sender_id: senderId || null,
            key: key || null,
            init_target_code: ic || null,
            target_code: tc || null,
            trade_partner_id: tp || null,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        var url = _editId
            ? '/trade-partner/mapping/' + _editId
            : '/trade-partner/mapping/store';

        var method = _editId ? 'PUT' : 'POST';

        showToast('info', _editId ? 'Updating...' : 'Saving...');
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showToast('success', data.message || 'Saved successfully');
                closeMappingModal();
                updateGrid(window.location.href);
            } else {
                showToast('error', data.message || 'Failed to save');
            }
        }).catch(function(err) {
            console.error(err);
            showToast('error', 'Network error');
        });
    }

    /* ================================================================
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(type, msg) {
        const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle', warning: 'exclamation-triangle' };
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(() => t.remove(), 3500);
    }

    /* ================================================================
       FLASH MESSAGES
    ================================================================ */
    @if(session('success'))
        showToast('success', @json(session('success')));
    @endif
    @if(session('error'))
        showToast('error', @json(session('error')));
    @endif

    /* ================================================================
       INITIAL STATE
    ================================================================ */
    (function() {
        var params = new URLSearchParams(window.location.search);
        if (params.toString()) {
            var fr = document.getElementById('filter-row');
            if (fr) { fr.style.display = 'table-row'; document.getElementById('btn-filter')?.classList.add('active'); }
        }

        // Double-click on grid rows to edit
        document.getElementById('grid-body').addEventListener('dblclick', function(e) {
            var row = e.target.closest('tr[data-id]');
            if (row && row.dataset.id) {
                var skip = ['A', 'INPUT', 'BUTTON', 'SELECT'];
                if (!skip.includes(e.target.tagName)) {
                    editMapping(row.dataset.id);
                }
            }
        });
    })();
    </script>
    @endpush
</x-layout>
