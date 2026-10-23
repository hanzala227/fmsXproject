<x-layout>
    @push('styles')
    <x-list-styles />
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ═══════════ TOAST CONTAINER ═══════════ --}}
    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════ MBL QUICK-VIEW MODAL ═══════════ --}}
    <div class="overlay" id="mbl-overlay" onclick="if(event.target===this) closeMbl()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-plane" style="color:#3b82f6;"></i> MAWB Quick View</div>
                <button class="modal-close" onclick="closeMbl()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" id="mbl-body"></div>
        </div>
    </div>

    {{-- ═══════════ DELETE CONFIRM MODAL ═══════════ --}}
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

    {{-- ═══════════ COLOR PICKER MODAL ═══════════ --}}
    <div class="overlay color-picker-overlay" id="color-picker-overlay" onclick="if(event.target===this) closeColorPicker()">
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

    {{-- ═══════════ CHANGE USER MODAL ═══════════ --}}
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

    {{-- ═══════════ MAIN PAGE ═══════════ --}}
    <div class="page-content">
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/air-export/list">Air Export</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">MAWB List</span></li>
            </ul>
        </div>

        <div class="portlet light">

            {{-- ── TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">MAWB List Overview</span>
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
                    <button class="btn-action-round white" onclick="mblExportCsv()" title="Download as CSV/Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
                        <a class="btn-tool green" href="{{ route('air-export.create') }}" title="New Shipment">
                            <i class="fa fa-plus"></i>
                        </a>
                        <button class="btn-tool" id="btn-copy"   disabled title="Copy Selected (select 1 row)" onclick="copySelected()"><i class="fa fa-files-o"></i></button>
                        <button class="btn-tool" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()"><i class="fa fa-trash"></i></button>
                    </div>
                    <div class="btn-group">
                        <button class="btn-tool" id="btn-block"   disabled style="padding:0 12px;" onclick="blockSelected()">Block</button>
                        <button class="btn-tool" id="btn-unblock" disabled style="padding:0 12px;" onclick="unblockSelected()">Unblock</button>
                    </div>
                    <div class="btn-group">
                        <select class="select-tool" id="sel-op" disabled onchange="changeOp(this)">
                            <option value="">Change OP</option>
                            @foreach($operators as $op)
                                <option value="{{ $op->id }}">{{ $op->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:160px;"
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)">
                </div>
            </div>

            {{-- ── ADVANCED FILTER ── --}}
            <div id="advanced-filter" style="display:none;background:#f0f4ff;padding:6px 8px;border-bottom:1px solid #bfdbfe;">
                <form method="GET" action="{{ route('air-export.mbl-list') }}" style="display:flex;flex-wrap:wrap;gap:6px;align-items:end;margin:0;">
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Office</label>
                        <select name="office_id" class="input-inline" style="width:100px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($offices as $o)
                                <option value="{{ $o->id }}" {{ request('office_id') == $o->id ? 'selected' : '' }}>{{ $o->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Operator</label>
                        <select name="op_id" class="input-inline" style="width:100px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($operators as $op)
                                <option value="{{ $op->id }}" {{ request('op_id') == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Carrier</label>
                        <select name="carrier_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($carriers as $c)
                                <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Departure</label>
                        <select name="dep_port_id" class="input-inline" style="width:120px;height:20px;font-size:9px;" data-ajax-search="true" data-search-url="{{ route('ports.search') }}">
                            <option value="">All</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('dep_port_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Destination</label>
                        <select name="dst_port_id" class="input-inline" style="width:120px;height:20px;font-size:9px;" data-ajax-search="true" data-search-url="{{ route('ports.search') }}">
                            <option value="">All</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('dst_port_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">ETD From</label>
                        <input type="date" name="etd_from" class="input-inline" style="width:110px;height:20px;font-size:9px;" value="{{ request('etd_from') }}">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">ETD To</label>
                        <input type="date" name="etd_to" class="input-inline" style="width:110px;height:20px;font-size:9px;" value="{{ request('etd_to') }}">
                    </div>
                    <div style="display:flex;gap:4px;align-self:end;padding-bottom:1px;">
                        <button type="submit" class="btn-tool green" style="height:20px;font-size:9px;padding:0 10px;">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <a href="{{ route('air-export.mbl-list') }}" class="btn-tool" style="height:20px;font-size:9px;padding:0 10px;" target="_blank">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── TABLE ── --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="mbl-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;left:0;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="file_no" style="width:110px;left:50px;">Booking No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="mawb_no" style="width:150px;left:160px;">MAWB No.</th>

                                    <th data-col="status" style="width:70px;">Status</th>
                                    <th data-col="flight_no" style="width:90px;">Flight No.</th>
                                    <th data-col="hbl_count" style="width:45px;text-align:right;">HBL</th>
                                    <th data-col="etd" style="width:75px;">ETD</th>
                                    <th data-col="eta" style="width:75px;">ETA</th>
                                    <th data-col="departure" style="width:130px;">Departure</th>
                                    <th data-col="destination" style="width:130px;">Destination</th>
                                    <th data-col="shipper" style="width:150px;">Shipper</th>
                                    <th data-col="oa" style="width:150px;">Oversea Agent</th>
                                    <th data-col="customer" style="width:150px;">Customer</th>
                                    <th data-col="operator" style="width:100px;">Operator</th>
                                    <th data-col="gw" style="width:70px;text-align:right;">G.W (KG)</th>
                                    <th data-col="cw" style="width:70px;text-align:right;">C.W (KG)</th>
                                    <th data-col="volume" style="width:70px;text-align:right;">Volume</th>
                                    <th data-col="frt_term" style="width:90px;">Frt. Term</th>
                                    <th data-col="post_date" style="width:75px;">Post Date</th>
                                    <th data-col="created_at" style="width:75px;">Created</th>
                                </tr>

                                {{-- Filter Row --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:50px;"><input class="filter-input" style="width:100%;" data-col-idx="2" placeholder="Booking No.." oninput="applyFilters()"></td>
                                    <td class="sticky-col" style="left:160px;"><input class="filter-input" style="width:100%;" data-col-idx="3" placeholder="MAWB.." oninput="applyFilters()"></td>
                                    <td colspan="3"></td>
                                    <td><input class="filter-input" data-col-idx="7" placeholder="ETD.." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="8" placeholder="ETA.." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="9" placeholder="Dep.." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="10" placeholder="Dest.." oninput="applyFilters()"></td>
                                    <td><input class="filter-input" data-col-idx="11" placeholder="Shipper.." oninput="applyFilters()"></td>
                                    <td></td>
                                    <td><input class="filter-input" data-col-idx="13" placeholder="Customer.." oninput="applyFilters()"></td>
                                    <td colspan="7"></td>
                                </tr>
                            </thead>

                            <tbody id="grid-body">
                                @forelse($shipments as $shipment)
                                    @php
                                        $isBlocked = $shipment->is_blocked ?? false;
                                        $statusLabel = $isBlocked ? 'Blocked' : 'Open';
                                        $badgeClass = $isBlocked ? 'bg-red' : 'bg-green';
                                    @endphp
                                <tr id="shipment-row-{{ $shipment->id }}"
                                    data-id="{{ $shipment->id }}"
                                    data-file="{{ $shipment->file_no }}"
                                    data-mawb="{{ $shipment->mawb_no }}"
                                    data-carrier="{{ $shipment->carrier->name ?? '' }}"
                                    data-flight="{{ $shipment->flight_no ?? '' }}"
                                    data-dep="{{ $shipment->depPort->name ?? '--' }}"
                                    data-dst="{{ $shipment->dstPort->name ?? '--' }}"
                                    data-etd="{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}"
                                    data-eta="{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}"
                                    data-shipper="{{ $shipment->shipper->name ?? '--' }}"
                                    data-oa="{{ $shipment->overseaAgent->name ?? '--' }}"
                                    data-customer="{{ $shipment->dmCustomer->name ?? '--' }}"
                                    data-operator="{{ $shipment->operator->name ?? '--' }}"
                                    data-hbls="{{ $shipment->hbls->count() }}"
                                    onclick="rowClick(event, this)">
                                    <td class="sticky-col" style="left:0;text-align:center;" onclick="event.stopPropagation()">
                                        <input type="checkbox" class="row-check" value="{{ $shipment->id }}" onchange="updateToolbar()">
                                    </td>
                                    <td class="sticky-col" style="width:25px;left:25px;text-align:center;" onclick="event.stopPropagation()">
                                        <i class="fa {{ $isBlocked ? 'fa-lock' : 'fa-unlock' }}" 
                                           style="color:{{ $isBlocked ? '#94a3b8' : '#22c55e' }};cursor:pointer;font-size:10px;" 
                                           title="{{ $isBlocked ? 'Lock' : 'Unlock' }}" 
                                           onclick="toggleLock(this)"></i>
                                    </td>
                                    <td class="sticky-col" style="left:50px;" onclick="event.stopPropagation()">
                                        <div style="display:flex;align-items:center;justify-content:space-between;">
                                            <a href="{{ route('air-export.edit', $shipment->id) }}" class="col-link">{{ $shipment->file_no }}</a>
                                            <i class="fa fa-external-link" style="color:#94a3b8;font-size:10px;cursor:pointer;" title="Open" onclick="event.stopPropagation();window.location.href='{{ route('air-export.edit', $shipment->id) }}'"></i>
                                        </div>
                                    </td>
                                    <td class="sticky-col" style="left:160px;" onclick="event.stopPropagation()">
                                        <div style="display:flex;align-items:center;justify-content:space-between;">
                                            <span>{{ $shipment->mawb_no ?: '--' }}</span>
                                            <i class="fa fa-eye" style="color:#3b82f6;font-size:10px;cursor:pointer;" title="Quick view MAWB" onclick="event.stopPropagation();showMbl({
                                                file_no: '{{ addslashes($shipment->file_no) }}',
                                                mawb_no: '{{ addslashes($shipment->mawb_no ?? '--') }}',
                                                carrier: '{{ addslashes($shipment->carrier->name ?? '--') }}',
                                                flight_no: '{{ addslashes($shipment->flight_no ?? '--') }}',
                                                dep: '{{ addslashes($shipment->depPort->name ?? '--') }}',
                                                dst: '{{ addslashes($shipment->dstPort->name ?? '--') }}',
                                                etd: '{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}',
                                                eta: '{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}',
                                                shipper: '{{ addslashes($shipment->shipper->name ?? '--') }}',
                                                oa: '{{ addslashes($shipment->overseaAgent->name ?? '--') }}',
                                                customer: '{{ addslashes($shipment->dmCustomer->name ?? '--') }}',
                                                operator: '{{ addslashes($shipment->operator->name ?? '--') }}',
                                                freight_term: '{{ addslashes($shipment->freight_term ?? '--') }}',
                                                hbls: {{ $shipment->hbls->count() }}
                                            })"></i>
                                        </div>
                                    </td>

                                    <td><span class="badge-status {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                                    <td><span class="badge-status bg-blue">{{ $shipment->flight_no ?: '--' }}</span></td>
                                    <td style="text-align:right;">{{ $shipment->hbls->count() }}</td>
                                    <td>{{ $shipment->etd ? $shipment->etd->format('m-d-Y') : '--' }}</td>
                                    <td>{{ $shipment->eta ? $shipment->eta->format('m-d-Y') : '--' }}</td>
                                    <td>{{ $shipment->depPort->name ?? '--' }}</td>
                                    <td>{{ $shipment->dstPort->name ?? '--' }}</td>
                                    <td>{{ $shipment->shipper->name ?? '--' }}</td>
                                    <td>{{ $shipment->overseaAgent->name ?? '--' }}</td>
                                    <td>{{ $shipment->dmCustomer->name ?? '--' }}</td>
                                    <td>{{ $shipment->operator->name ?? '--' }}</td>
                                    <td style="text-align:right;">{{ number_format($shipment->gross_weight ?? 0, 2) }}</td>
                                    <td style="text-align:right;">{{ number_format($shipment->chargeable_weight ?? 0, 2) }}</td>
                                    <td style="text-align:right;">{{ number_format($shipment->volume ?? 0, 3) }}</td>
                                    <td>{{ $shipment->freight_term ?? '--' }}</td>
                                    <td>{{ $shipment->post_date ? $shipment->post_date->format('m-d-Y') : '--' }}</td>
                                    <td>{{ $shipment->created_at ? $shipment->created_at->format('m-d-Y') : '--' }}</td>
                                </tr>
                                @empty
                                <tr id="empty-row">
                                    <td colspan="50" style="text-align:center;padding:30px 10px;color:#94a3b8;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No MAWB records found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── PAGINATION FOOTER ── --}}
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
    var _colorId = null;
    var _changeMode = 'op';

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
        document.getElementById('btn-copy').disabled     = n !== 1;
        document.getElementById('btn-block').disabled    = n === 0;
        document.getElementById('btn-unblock').disabled  = n === 0;
        document.getElementById('sel-op').disabled       = n === 0;

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
            var response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            var html = await response.text();
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');

            var newBody = doc.getElementById('grid-body');
            var newPagination = doc.getElementById('pagination-container');

            if (newBody) document.getElementById('grid-body').innerHTML = newBody.innerHTML;
            if (newPagination) document.getElementById('pagination-container').innerHTML = newPagination.innerHTML;

            ['stat-first', 'stat-last', 'stat-total'].forEach(function(id) {
                var el = doc.getElementById(id);
                if (el) document.getElementById(id).textContent = el.textContent;
            });

            updateToolbar();
        } catch (e) {
            console.error(e);
            showToast('error', 'Failed to update grid');
        }
    }

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
        2: 'filter_file_no', 3: 'filter_mawb_no',
        7: 'filter_etd', 8: 'filter_eta',
        9: 'filter_dep', 10: 'filter_dst',
        11: 'filter_shipper', 13: 'filter_customer'
    };

    function applyFilters() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            var inputs = [...document.querySelectorAll('#filter-row .filter-input')];
            Object.values(FILTER_MAP).forEach(function(p) { url.searchParams.delete(p); });
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
    var PINNED_COLS = ['check', 'lock', 'file_no', 'mawb_no'];

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
        document.getElementById('confirm-msg').textContent = 'Delete ' + n + ' MAWB shipment(s)? This cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        showToast('info', 'Deleting...');
        fetch('{{ route("air-export.bulk-delete") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { showToast('success', d.message); setTimeout(function() { updateGrid(window.location.href); }, 600); }
            else showToast('error', d.message || 'Delete failed.');
        })
        .catch(function() { showToast('error', 'Delete failed.'); });
    }

    /* ================================================================
       COPY
    ================================================================ */
    function copySelected() {
        var checked = [...document.querySelectorAll('.row-check:checked')];
        if (checked.length !== 1) return;
        var row = checked[0].closest('tr[data-id]');
        if (!row) return;
        showToast('info', 'Copying shipment: ' + (row.dataset.file || '') + ' ...');
        setTimeout(function() {
            window.location.href = '{{ route("air-export.create") }}?copy=' + row.dataset.id;
        }, 600);
    }

    /* ================================================================
       BLOCK / UNBLOCK
    ================================================================ */
    function blockSelected()   { bulkAction('{{ route("air-export.bulk-block") }}', 'Blocked'); }
    function unblockSelected() { bulkAction('{{ route("air-export.bulk-unblock") }}', 'Unblocked'); }

    function bulkAction(url, label) {
        var ids = getSelectedIds();
        if (!ids.length) return;
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { 
                showToast('success', d.message); 
                // Update lock icons for affected rows
                ids.forEach(function(id) {
                    var row = document.getElementById('shipment-row-' + id);
                    if (row) {
                        var lockIcon = row.querySelector('.fa-lock, .fa-unlock');
                        if (lockIcon) {
                            var shouldLock = label === 'Blocked';
                            lockIcon.classList.toggle('fa-lock', shouldLock);
                            lockIcon.classList.toggle('fa-unlock', !shouldLock);
                            lockIcon.style.color = shouldLock ? '#94a3b8' : '#22c55e';
                            lockIcon.title = shouldLock ? 'Lock' : 'Unlock';
                        }
                        // Update status badge
                        var statusBadge = row.querySelector('.badge-status');
                        if (statusBadge) {
                            statusBadge.textContent = shouldLock ? 'Blocked' : 'Open';
                            statusBadge.className = 'badge-status ' + (shouldLock ? 'bg-red' : 'bg-green');
                        }
                    }
                });
                updateToolbar();
            }
            else showToast('error', d.message || label + ' failed.');
        })
        .catch(function() { showToast('error', label + ' failed.'); });
    }

    /* ================================================================
       LOCK ICON TOGGLE (per-row visual with backend update)
    ================================================================ */
    function toggleLock(el) {
        var row = el.closest('tr');
        var id = row.dataset.id;
        var locked = el.classList.contains('fa-lock');
        var action = locked ? 'unblock' : 'block';
        var url = action === 'block' 
            ? '{{ route("air-export.bulk-block") }}' 
            : '{{ route("air-export.bulk-unblock") }}';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: [id] })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                el.classList.toggle('fa-lock', !locked);
                el.classList.toggle('fa-unlock', locked);
                el.style.color = locked ? '#22c55e' : '#94a3b8';
                el.title = locked ? 'Unlock' : 'Lock';
                // Update status badge
                var statusBadge = row.querySelector('.badge-status');
                if (statusBadge) {
                    statusBadge.textContent = locked ? 'Open' : 'Blocked';
                    statusBadge.className = 'badge-status ' + (locked ? 'bg-green' : 'bg-red');
                }
                showToast('success', locked ? 'Shipment unlocked' : 'Shipment locked');
            } else {
                showToast('error', data.message || 'Failed to update');
            }
        }).catch(function() { showToast('error', 'Failed to update lock status'); });
    }

    /* ================================================================
       CHANGE OP
    ================================================================ */
    function changeOp(sel) {
        var val = sel.value;
        if (!val) return;
        document.getElementById('change-user-title').textContent = 'Change OP';
        document.getElementById('change-user-overlay').classList.add('open');
        document.getElementById('change-user-select').value = val;
        sel.value = '';
    }

    function closeChangeUser() { document.getElementById('change-user-overlay').classList.remove('open'); }

    function executeChangeUser() {
        var userId = document.getElementById('change-user-select').value;
        if (!userId) { showToast('error', 'Please select a user.'); return; }
        var ids = getSelectedIds();
        if (!ids.length) { closeChangeUser(); return; }
        var url = '{{ route("air-export.bulk-change-op") }}';
        var body = { ids: ids, op_id: userId };
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
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
        _colorId = id;
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
        var id = _colorId;
        fetch('{{ route("air-export.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: color })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var span = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (span) span.style.background = color;
                showToast('success', 'Color updated');
            }
        })
        .catch(function() { showToast('error', 'Color update failed'); });
        closeColorPicker();
    }

    function closeColorPicker() { document.getElementById('color-picker-overlay').classList.remove('open'); _colorId = null; }

    function clearColor() {
        var id = _colorId;
        if (!id) return;
        fetch('{{ route("air-export.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ color: '' })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var span = document.querySelector('#shipment-row-' + id + ' .color-mark');
                if (span) span.style.background = '#94a3b8';
                showToast('success', 'Color cleared');
            }
        })
        .catch(function() { showToast('error', 'Failed to clear'); });
        closeColorPicker();
    }

    /* ================================================================
       MBL QUICK-VIEW MODAL
    ================================================================ */
    function showMbl(d) {
        var rows = [
            ['Booking No.',         d.file_no],
            ['MAWB No.',         d.mawb_no],
            ['Carrier',          d.carrier],
            ['Flight No.',       d.flight_no],
            ['Departure',        d.dep],
            ['Destination',      d.dst],
            ['ETD',              d.etd],
            ['ETA',              d.eta],
            ['Shipper',          d.shipper],
            ['Oversea Agent',    d.oa],
            ['Customer',         d.customer],
            ['Operator',         d.operator],
            ['Freight Term',     d.freight_term],
            ['HBLs',             d.hbls],
        ];
        document.getElementById('mbl-body').innerHTML = rows.map(function(r) {
            return '<div class="mbl-row"><span class="lbl">' + r[0] + '</span><span class="val">' + (r[1] ?? '--') + '</span></div>';
        }).join('');
        document.getElementById('mbl-overlay').classList.add('open');
    }
    function closeMbl() { document.getElementById('mbl-overlay').classList.remove('open'); }

    /* ================================================================
       EXPORT CSV
    ================================================================ */
    function mblExportCsv() {
        var params = new URLSearchParams();
        var q = document.getElementById('quick-search')?.value?.trim();
        if (q) params.set('search', q);
        ['office_id', 'op_id', 'carrier_id', 'dep_port_id', 'dst_port_id', 'etd_from', 'etd_to'].forEach(function(name) {
            var input = document.querySelector('[name="' + name + '"]');
            if (input && input.value) params.set(name, input.value);
        });
        document.querySelectorAll('#filter-row .filter-input').forEach(function(inp) {
            if (inp.value.trim()) {
                var key = FILTER_MAP[inp.dataset.colIdx];
                if (key) params.set(key, inp.value.trim());
            }
        });
        var url = '/air-export/mbl-export-csv?' + params.toString();
        
        // Show toast notification
        showToast('info', 'Preparing Excel export...');
        
        // Trigger download without page refresh using hidden iframe
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);
        
        // Remove iframe after download starts (5 seconds)
        setTimeout(function() {
            document.body.removeChild(iframe);
            showToast('success', 'Excel export downloaded');
        }, 5000);
    }

    /* ================================================================
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(type, msg) {
        var icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
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
