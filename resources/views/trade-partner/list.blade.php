<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .tp-list-page .grid-wrapper { height: calc(100vh - 210px); min-height: 300px; }
        .tp-list-page .sticky-col { position: sticky; left: 0; z-index: 5; background: #fff; border-right: 1px solid #cbd5e1 !important; }
        .tp-list-page .sticky-col-header { z-index: 15 !important; background: #f8fafc !important; }
        .tp-list-page .grid-table tr:hover .sticky-col { background-color: #f1f5f9 !important; }
        .tp-list-page .grid-table tr.row-selected .sticky-col { background-color: #eff6ff !important; }
        .tp-list-page .status-active { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 1px 5px; border-radius: 2px; font-size: 9px; font-weight: 600; }
        .tp-list-page .status-inactive { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 1px 5px; border-radius: 2px; font-size: 9px; font-weight: 600; }
        .tp-list-page .status-default { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 1px 5px; border-radius: 2px; font-size: 9px; font-weight: 600; }

        /* Pagination — Ocean Module theme */
        .tp-list-page .tp-pagination { display: flex; align-items: center; gap: 2px; }
        .tp-list-page .tp-page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 20px; padding: 0 6px; border: 1px solid #cbd5e1; background: #fff; color: #334155; font-size: 10px; font-weight: 400; text-decoration: none; border-radius: 2px; cursor: pointer; transition: all 0.15s; line-height: 1; font-family: inherit; }
        .tp-list-page .tp-page-btn:hover { background: #f1f5f9; border-color: #94a3b8; color: #1e293b; }
        .tp-list-page .tp-page-btn.active { background: #3b82f6; color: #fff; border-color: #2563eb; font-weight: 600; }
        .tp-list-page .tp-page-btn.disabled { opacity: 0.4; cursor: not-allowed; background: #f8fafc; color: #94a3b8; }
        .tp-list-page .tp-page-btn i { font-size: 8px; }
    </style>
    @endpush

    {{-- TOAST CONTAINER --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- DELETE CONFIRM MODAL --}}
    <div class="overlay" id="delete-overlay" onclick="if(event.target===this) closeDeleteModal()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4 id="delete-title">Delete Trade Partner(s)?</h4>
            <p id="delete-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:26px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- RESTORE CONFIRM MODAL --}}
    <div class="overlay" id="restore-overlay" onclick="if(event.target===this) closeRestoreModal()">
        <div class="confirm-box">
            <div class="confirm-icon" style="color:#3b82f6;"><i class="fa fa-undo"></i></div>
            <h4>Restore Trade Partner(s)?</h4>
            <p id="restore-msg">Selected trade partners will be restored.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:26px;" onclick="closeRestoreModal()">Cancel</button>
                <button class="btn-tool" style="padding:0 18px;height:26px;background:#3b82f6;color:#fff;border-color:#2563eb;" onclick="executeRestore()">
                    <i class="fa fa-undo"></i> Restore
                </button>
            </div>
        </div>
    </div>

    {{-- MAIN PAGE --}}
    <div class="page-content tp-list-page">

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/trade-partner/list">Trade Partner</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #1e293b; font-weight: 700;">Trade Partner List</span></li>
            </ul>
        </div>

        <div class="portlet light">

            {{-- PORTLET TITLE --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">Trade Partner List</span>
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
                    <a class="btn-action-round white" href="{{ route('trade-partner.export-csv') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" title="Download as CSV/Excel" id="btn-excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </a>
                </div>
            </div>

            {{-- TOOLBAR --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('trade-partner.create') }}" title="New Trade Partner" target="_blank">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button class="btn-tool" id="btn-restore" disabled title="Restore Selected" onclick="confirmRestore()" style="display:none;">
                            <i class="fa fa-undo"></i> Restore
                        </button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    @if(request('trashed'))
                        <a href="{{ route('trade-partner.index') }}" class="btn-action-round" style="font-size:10px;height:20px;padding:0 8px;background:#ef4444;color:#fff;border-color:#dc2626;" title="Show Active">
                            <i class="fa fa-eye-slash"></i> Viewing Trashed
                        </a>
                    @else
                        <a href="{{ route('trade-partner.index', ['trashed' => 1]) }}" class="btn-action-round white" style="font-size:10px;height:20px;padding:0 8px;" title="Show Deleted">
                            <i class="fa fa-trash-o"></i> Trash
                        </a>
                    @endif
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
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:28px;text-align:center;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="name" style="left:28px;width:160px;">Name</th>
                                    <th data-col="type" style="width:60px;">Type</th>
                                    <th data-col="status" style="width:70px;">Status</th>
                                    <th data-col="local_name" style="width:120px;">Local Name</th>
                                    <th data-col="scac" style="width:80px;">SCAC / IATA</th>
                                    <th data-col="firm_code" style="width:80px;">Firm Code</th>
                                    <th data-col="alias" style="width:90px;">Alias</th>
                                    <th data-col="contact" style="width:110px;">Contact</th>
                                    <th data-col="group" style="width:100px;">Group</th>
                                    <th data-col="address" style="width:140px;">Address</th>
                                    <th data-col="remark" style="width:110px;">Remark</th>
                                    <th data-col="acct_address" style="width:140px;">Accounting Address</th>
                                    <th data-col="city" style="width:90px;">City</th>
                                    <th data-col="state" style="width:70px;">State</th>
                                    <th data-col="tax_id" style="width:100px;">Tax ID / USCI No.</th>
                                    <th data-col="track_1099" style="width:65px;text-align:center;">1099</th>
                                    <th data-col="zip" style="width:70px;">Zip</th>
                                    <th data-col="country" style="width:90px;">Country</th>
                                    <th data-col="sales_person" style="width:100px;">Sales Person</th>
                                    <th data-col="op_assigned" style="width:100px;">OP Assigned</th>
                                    <th data-col="payment_type" style="width:80px;">Payment Type</th>
                                    <th data-col="credit_terms" style="width:100px;">Credit Terms</th>
                                    <th data-col="make_payment" style="width:110px;">Default Make Payment</th>
                                    <th data-col="recv_payment" style="width:110px;">Default Receive Payment</th>
                                </tr>

                                {{-- FILTER ROW --}}
                                <tr id="filter-row" style="display:none;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:28px;"><input class="filter-input" data-param="filter_name" placeholder="Name..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td>
                                        <select class="filter-input" data-param="filter_type" onchange="applyFilters()" style="height:18px;">
                                            <option value="">All</option>
                                            @foreach(['CS'=>'Client','CN'=>'Consignee','KS'=>'Shipper(K)','SH'=>'Shipper(U)','PR'=>'Agent','CR'=>'Carrier','AC'=>'Air Carrier','FR'=>'Forwarder','CB'=>'Customs Broker','TK'=>'Trucker','WH'=>'Warehouse','VR'=>'Vendor','BK'=>'Bank','BW'=>'Booking Window','CF'=>'CFS','CY'=>'CY','EM'=>'Employee','FB'=>'FBA Warehouse','GV'=>'Government','MF'=>'Manufacturer','OE'=>'Office Expense','OT'=>'Other','RL'=>'Rail Company','RC'=>'Ramp Location','TM'=>'Terminal'] as $code => $label)
                                                <option value="{{ $code }}" {{ request('filter_type') == $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="filter-input" data-param="filter_status" onchange="applyFilters()" style="height:18px;">
                                            <option value="">All</option>
                                            @foreach(['ACTIVE','INACTIVE','BUSINESS','PROSPECT'] as $s)
                                                <option value="{{ $s }}" {{ request('filter_status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input class="filter-input" data-param="filter_local_name" placeholder="Local Name..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_scac" placeholder="SCAC..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_firm" placeholder="Firm Code..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_alias" placeholder="Alias..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td></td>
                                    <td></td>
                                    <td><input class="filter-input" data-param="filter_address" placeholder="Address..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td></td>
                                    <td></td>
                                    <td><input class="filter-input" data-param="filter_city" placeholder="City..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_state" placeholder="State..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_tax" placeholder="Tax ID..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td></td>
                                    <td><input class="filter-input" data-param="filter_zip" placeholder="Zip..." oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td>
                                        <select class="filter-input" data-param="filter_country" onchange="applyFilters()" style="height:18px;">
                                            <option value="">All</option>
                                            @foreach($countries as $c)
                                                <option value="{{ $c->id }}" {{ request('filter_country') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="filter-input" data-param="filter_sales_person" onchange="applyFilters()" style="height:18px;">
                                            <option value="">All</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ request('filter_sales_person') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td></td>
                                    <td>
                                        <select class="filter-input" data-param="filter_payment_type" onchange="applyFilters()" style="height:18px;">
                                            <option value="">All</option>
                                            @foreach(['COD','PREPAID','COLLECT','CREDIT'] as $pt)
                                                <option value="{{ $pt }}" {{ request('filter_payment_type') == $pt ? 'selected' : '' }}>{{ $pt }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:center;"><button class="btn-tool green" onclick="applyFilters()" style="height:18px;">Filter</button></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                            @forelse($partners as $p)
                                <tr id="tp-row-{{ $p->id }}" data-id="{{ $p->id }}" onclick="rowClick(event, this)">
                                    <td class="sticky-col" style="width:28px;text-align:center;" onclick="event.stopPropagation()">
                                        <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="row-check" onchange="updateToolbar()">
                                    </td>
                                    <td class="sticky-col" style="left:28px;width:160px;" onclick="event.stopPropagation()">
                                        <a href="{{ route('trade-partner.edit', $p->id) }}" class="col-link">{{ $p->name }}</a>
                                    </td>
                                    <td><span class="badge-status bg-blue">{{ $p->type }}</span></td>
                                    <td>
                                        @if($p->status)
                                            <span class="{{ in_array(strtolower($p->status), ['active','business']) ? 'status-active' : (strtolower($p->status) == 'inactive' ? 'status-inactive' : 'status-default') }}">{{ $p->status }}</span>
                                        @else --
                                        @endif
                                    </td>
                                    <td>{{ $p->local_name ?? '--' }}</td>
                                    <td>{{ $p->scac_code ?: ($p->iata_code ?: '--') }}</td>
                                    <td>{{ $p->firms_code ?? '--' }}</td>
                                    <td>{{ $p->alias ?? '--' }}</td>
                                    <td>{{ $p->contacts->first()->email_name ?? ($p->phone ?? '--') }}</td>
                                    <td>{{ $p->accountGroup->name ?? '--' }}</td>
                                    <td>{{ $p->billing_address ?? '--' }}</td>
                                    <td>{{ $p->remark ?? '--' }}</td>
                                    <td>{{ $p->billing_address ?? '--' }}</td>
                                    <td>{{ $p->city ?? '--' }}</td>
                                    <td>{{ $p->state ?? '--' }}</td>
                                    <td>{{ $p->tax_id ?? '--' }}</td>
                                    <td style="text-align:center;">{!! $p->track_1099 ? '<i class="fa fa-check" style="color:#16a34a;"></i>' : '<i class="fa fa-times" style="color:#cbd5e1;"></i>' !!}</td>
                                    <td>{{ $p->zip_code ?? '--' }}</td>
                                    <td>{{ $p->country->name ?? '--' }}</td>
                                    <td>{{ $p->salesPerson->name ?? '--' }}</td>
                                    <td>{{ $p->csPerson->name ?? '--' }}</td>
                                    <td>{{ $p->payment_type ?? '--' }}</td>
                                    <td>{{ ($p->credit_term_days ?: 0) . ' ' . ($p->credit_term_unit ?? 'Days') }}</td>
                                    <td>--</td>
                                    <td>--</td>
                                </tr>
                            @empty
                                <tr id="empty-row">
                                    <td colspan="25" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No trade partners found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- PAGINATION --}}
            <div class="portlet-tool bottom">
                <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                    <div id="pagination-container">{{ $partners->links('vendor.pagination.custom') }}</div>
                    <div style="font-size:10px;color:#64748b;">
                        Showing <span id="stat-first">{{ $partners->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $partners->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $partners->total() }}</span> records
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
    function updateToolbar() {
        const checked = [...document.querySelectorAll('.row-check:checked')];
        const all     = [...document.querySelectorAll('.row-check')];
        const n       = checked.length;
        const sa      = document.getElementById('select-all');
        sa.checked       = n === all.length && all.length > 0;
        sa.indeterminate = n > 0 && n < all.length;

        document.getElementById('btn-delete').disabled  = n === 0;
        const restoreBtn = document.getElementById('btn-restore');
        if (restoreBtn) restoreBtn.disabled = n === 0;

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

    /* ================================================================
       ROW CLICK — click row to toggle checkbox
    ================================================================ */
    function rowClick(e, row) {
        const skip = ['A', 'INPUT', 'BUTTON', 'I', 'SELECT'];
        if (skip.includes(e.target.tagName)) return;
        const cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    /* ================================================================
       DELETE
    ================================================================ */
    function confirmDelete() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('delete-title').textContent = 'Delete Trade Partner(s)?';
        document.getElementById('delete-msg').textContent = 'You are about to delete ' + n + ' trade partner(s). This can be undone from Trash.';
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
        fetch('{{ route("trade-partner.bulk-delete") }}', {
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
       RESTORE
    ================================================================ */
    function confirmRestore() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        document.getElementById('restore-msg').textContent = 'You are about to restore ' + n + ' trade partner(s).';
        document.getElementById('restore-overlay').classList.add('open');
    }
    function closeRestoreModal() {
        document.getElementById('restore-overlay').classList.remove('open');
    }
    function executeRestore() {
        closeRestoreModal();
        const ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Restoring...');
        fetch('{{ route("trade-partner.bulk-restore") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showToast('success', data.message || 'Restored successfully');
                updateGrid(window.location.href);
            } else {
                showToast('error', data.message || 'Failed to restore');
            }
        }).catch(() => showToast('error', 'Failed to restore'));
    }

    /* ================================================================
       FILTER ROW TOGGLE
    ================================================================ */
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
       AJAX GRID UPDATE
    ================================================================ */
    async function updateGrid(url) {
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Network response was not ok');
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newBody = doc.getElementById('grid-body');
            const newPagination = doc.getElementById('pagination-container');

            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            const stats = doc.querySelector('.portlet-tool.bottom div:last-child');
            if (stats) {
                const text = stats.textContent;
                const matches = text.match(/\d+/g);
                if (matches && matches.length >= 3) {
                    document.getElementById('stat-first').textContent = matches[0];
                    document.getElementById('stat-last').textContent = matches[1];
                    document.getElementById('stat-total').textContent = matches[2];
                }
            }

            updateToolbar();
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update grid');
        }
    }

    // Wire pagination links to AJAX
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.tp-pagination a.tp-page-btn');
        if (link) {
            e.preventDefault();
            updateGrid(link.href);
        }
    });

    function getSelectedIds() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

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

    /* ================================================================
       CONFIG PANEL — column visibility
    ================================================================ */
    var PINNED_COLS = ['check', 'name'];

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
        if (!th) return;
        const idx = [...th.parentElement.children].indexOf(th);
        th.style.display = show ? '' : 'none';
        document.querySelectorAll('#grid-body tr, #filter-row').forEach(row => {
            const cells = row.querySelectorAll('td, th');
            const cell = cells[idx];
            if (cell) cell.style.display = show ? '' : 'none';
        });
    }

    // Close config on outside click
    document.addEventListener('click', e => {
        const panel = document.getElementById('config-panel');
        const btn   = document.getElementById('btn-config');
        if (panel && btn && panel.style && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });

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
       FLASH MESSAGE FROM SERVER
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
            document.getElementById('btn-filter').classList.add('active');
            document.getElementById('filter-row').style.display = 'table-row';
        }
    })();
    </script>
    @endpush
</x-layout>
