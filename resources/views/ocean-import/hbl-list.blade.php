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
        
        /* Mobile Responsive Enhancements - FIXED SCROLLING */
        @media (max-width: 768px) {
            .page-content { 
                padding: 2px !important; 
                overflow-x: hidden !important;
            }
            .portlet.light { 
                margin: 0 !important; 
                border-radius: 0 !important; 
                overflow: hidden !important;
            }
            
            /* Portlet Title - Stack on mobile */
            .portlet-title { 
                flex-direction: column !important; 
                align-items: flex-start !important; 
                padding: 6px !important;
                gap: 6px;
            }
            .portlet-title .caption { width: 100%; }
            .portlet-title .actions { 
                width: 100%; 
                flex-wrap: wrap; 
                gap: 3px !important;
            }
            .btn-action-round { 
                font-size: 9px !important; 
                padding: 0 6px !important; 
                height: 18px !important;
            }
            
            /* Toolbar - Stack on mobile */
            .portlet-tool { 
                flex-direction: column !important; 
                align-items: flex-start !important; 
                padding: 6px !important;
                gap: 6px !important;
            }
            .portlet-tool > div { width: 100%; }
            .btn-group { 
                width: 100%; 
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            .btn-tool { 
                font-size: 8px !important; 
                padding: 0 6px !important;
                height: 20px !important;
                flex: 0 1 auto;
            }
            .input-inline, .select-tool { 
                width: 100% !important; 
                font-size: 9px !important;
            }
            
            /* CRITICAL FIX: Table scrolling on mobile */
            .portlet-body {
                padding: 0 !important;
                overflow: hidden !important;
            }
            
            .grid-container { 
                width: 100% !important;
                overflow: hidden !important;
                background: #fff;
                position: relative;
            }
            
            .grid-wrapper { 
                width: 100% !important;
                height: calc(100vh - 350px) !important;
                min-height: 200px !important;
                overflow-x: auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch !important;
                position: relative;
            }
            
            .grid-table { 
                font-size: 8px !important;
                width: auto !important;
                min-width: 1800px !important;
                table-layout: auto !important;
            }
            
            .grid-table th, .grid-table td { 
                padding: 2px 4px !important;
                height: 22px !important;
                white-space: nowrap !important;
            }
            
            /* Keep only 2 sticky columns on mobile */
            .sticky-col { 
                font-size: 8px !important;
                position: sticky !important;
                z-index: 5 !important;
                background: #fff !important;
            }
            
            .grid-table th:nth-child(1), .grid-table td:nth-child(1) { 
                left: 0 !important; 
            }
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) { 
                left: 25px !important; 
            }
            /* Remove sticky from other columns */
            .grid-table th:nth-child(3), .grid-table td:nth-child(3),
            .grid-table th:nth-child(4), .grid-table td:nth-child(4),
            .grid-table th:nth-child(5), .grid-table td:nth-child(5),
            .grid-table th:nth-child(6), .grid-table td:nth-child(6) {
                position: static !important;
                left: auto !important;
            }
            
            .filter-input { 
                height: 18px !important; 
                font-size: 8px !important;
                padding: 0 3px !important;
            }
            
            /* Modals on mobile */
            .modal-box, .confirm-box { 
                margin: 10px;
                width: calc(100% - 20px);
                max-width: 100%;
                min-width: 0 !important;
            }
            .modal-body { 
                padding: 8px !important;
                min-width: 0 !important;
            }
            .confirm-box { padding: 16px !important; }
            
            /* Config Panel on mobile */
            .config-panel {
                right: 0;
                left: 0;
                top: 22px;
                max-width: 100%;
                max-height: 250px;
            }
            
            /* Pagination on mobile */
            .portlet-tool.bottom { 
                flex-direction: column !important; 
                gap: 6px;
            }
            .portlet-tool.bottom > div { width: 100% !important; }
            .pagination { 
                justify-content: center;
                font-size: 9px !important;
            }
            .tp-page-btn {
                min-width: 20px !important;
                height: 18px !important;
                padding: 0 4px !important;
                font-size: 8px !important;
            }
            
            /* Toast on mobile */
            .toast-container { 
                top: 10px; 
                right: 10px;
                left: 10px;
            }
            .toast { 
                font-size: 10px !important;
                padding: 6px 10px !important;
            }
            
            /* Breadcrumbs on mobile */
            .page-bar { 
                padding: 6px 10px !important;
                margin-bottom: 8px !important;
            }
            .page-breadcrumb li { font-size: 10px !important; }
            
            #sel-badge { font-size: 8px !important; }
        }
        
        @media (max-width: 480px) {
            .grid-table { 
                font-size: 7px !important; 
                min-width: 1600px !important;
            }
            .grid-table th, .grid-table td { 
                padding: 2px 3px !important;
                height: 20px !important;
            }
            .btn-action-round, .btn-tool { 
                font-size: 8px !important;
                padding: 0 4px !important;
            }
            
            /* Keep only checkbox sticky on very small screens */
            .grid-table th:nth-child(2), .grid-table td:nth-child(2) {
                position: static !important;
                left: auto !important;
            }
        }
        
        @media (max-width: 768px) and (orientation: landscape) {
            .grid-wrapper { 
                height: calc(100vh - 200px) !important;
            }
        }
        
        /* Touch-friendly targets */
        @media (hover: none) and (pointer: coarse) {
            .btn-tool, .btn-action-round, .tp-page-btn {
                min-height: 28px !important;
                touch-action: manipulation;
            }
            .filter-input, .select-tool {
                min-height: 24px !important;
                touch-action: manipulation;
            }
            input[type="checkbox"] {
                width: 18px;
                height: 18px;
                touch-action: manipulation;
            }
            .grid-wrapper {
                -webkit-overflow-scrolling: touch !important;
                scroll-behavior: smooth;
            }
        }
    </style>
    @endpush

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="toast-container" id="toast-container"></div>

    {{-- ═══════════════════════ DELETE CONFIRM MODAL ═══════════════════════ --}}
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


    <div class="page-content">

          <!-- Breadcrumb -->
        <div class="page-bar">
             <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li><a href="/ocean-import/list">Ocean Import</a> <i class="fa fa-angle-right"></i></li>
                <li><span style="color: #333; font-weight: 700;">House B/L List</span></li>
            </ul>
        </div>
 

        <div class="portlet light">

            {{-- ── TITLE ── --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <span class="caption-subject">House B/L List</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;position:relative;align-items:center;">
                    <button class="btn-action-round" onclick="toggleFilter()" id="btn-filter" title="Toggle filter row">
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
                    <button class="btn-action-round white" onclick="exportExcel()" title="Download as CSV/Excel" id="btn-excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                </div>
            </div>

            {{-- ── TOOLBAR ── --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="btn-group">
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
                           placeholder="Quick search..." value="{{ request('search') }}"
                           oninput="quickSearch(this.value)" onkeyup="if(event.key === 'Enter') this.blur()">
                </div>
            </div>

            {{-- ── ADVANCED FILTER ── --}}
            <div id="advanced-filter" style="display:none;background:#f0f4ff;padding:6px 8px;border-bottom:1px solid #bfdbfe;">
                <form method="GET" action="{{ route('ocean-import.hbl-list') }}" style="display:flex;flex-wrap:wrap;gap:6px;align-items:end;margin:0;">
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
                        <label style="font-size:9px;color:#475569;font-weight:600;">Sales Person</label>
                        <select name="sales_person_id" class="input-inline" style="width:100px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($salesPersons as $sp)
                                <option value="{{ $sp->id }}" {{ request('sales_person_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">POL</label>
                        <select name="pol_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('pol_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">POD</label>
                        <select name="pod_id" class="input-inline" style="width:120px;height:20px;font-size:9px;">
                            <option value="">All</option>
                            @foreach($ports as $p)
                                <option value="{{ $p->id }}" {{ request('pod_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">File No.</label>
                        <input type="text" name="file_no" class="input-inline" style="width:100px;height:20px;font-size:9px;" placeholder="File No…" value="{{ request('file_no') }}">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">HB/L No.</label>
                        <input type="text" name="hbl_no" class="input-inline" style="width:100px;height:20px;font-size:9px;" placeholder="HB/L No…" value="{{ request('hbl_no') }}">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">MB/L No.</label>
                        <input type="text" name="mbl_no" class="input-inline" style="width:100px;height:20px;font-size:9px;" placeholder="MB/L No…" value="{{ request('mbl_no') }}">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1px;">
                        <label style="font-size:9px;color:#475569;font-weight:600;">Consignee</label>
                        <input type="text" name="consignee" class="input-inline" style="width:120px;height:20px;font-size:9px;" placeholder="Consignee…" value="{{ request('consignee') }}">
                    </div>
                    <div style="display:flex;gap:4px;align-self:end;padding-bottom:1px;">
                        <button type="submit" class="btn-tool green" style="height:20px;font-size:9px;padding:0 10px;">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <a href="{{ route('ocean-import.hbl-list') }}" class="btn-tool" style="height:20px;font-size:9px;padding:0 10px;">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── TABLE ── --}}
            <div class="portlet-body">
                <div class="grid-container">
                    <div class="grid-wrapper">
                        <table class="grid-table" id="hbl-grid">
                            <thead>
                                <tr id="header-row">
                                    <th class="sticky-col sticky-col-header" data-col="check" style="width:25px;text-align:center;left:0;">
                                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)" title="Select All">
                                    </th>
                                    <th class="sticky-col sticky-col-header" data-col="lock" style="width:25px;left:25px;text-align:center;"><i class="fa fa-lock"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="flag" style="width:25px;left:50px;text-align:center;"><i class="fa fa-flag" style="color:#94a3b8;"></i></th>
                                    <th class="sticky-col sticky-col-header" data-col="file_no" style="width:120px;left:75px;">File No.</th>
                                    <th class="sticky-col sticky-col-header" data-col="hbl_no" style="width:150px;left:230px;">HB/L No.</th>
                                    <th data-col="event" style="width:110px;">Latest Event</th>
                                    <th data-col="journey" style="width:180px;">Journey</th>
                                    <th data-col="event_date" style="width:130px;">Latest Event Date</th>
                                    <th data-col="mbl_no" style="width:170px;">MB/L No.</th>
                                    <th data-col="consignee" style="width:150px;">Consignee</th>
                                    <th data-col="package" style="width:100px;">Package</th>
                                    <th data-col="weight" style="width:100px;">Weight</th>
                                    <th data-col="measure" style="width:100px;">Measurement</th>
                                    <th data-col="hold" style="width:45px;text-align:center;">Hold</th>
                                    <th data-col="it_no" style="width:90px;">IT No.</th>
                                    <th data-col="obl" style="width:80px;">OB/L</th>
                                    <th data-col="ar" style="width:100px;text-align:right;">AR Balance</th>
                                    <th data-col="ap" style="width:100px;text-align:right;">AP Balance</th>
                                    <th data-col="dc" style="width:100px;text-align:right;">DC Balance</th>
                                </tr>
                                {{-- Filter Row --}}
                                <tr id="filter-row" style="display:none;background:#eff6ff;">
                                    <td class="sticky-col" style="left:0;"></td>
                                    <td class="sticky-col" style="left:25px;"></td>
                                    <td class="sticky-col" style="left:50px;"></td>
                                    <td class="sticky-col" style="left:75px;"><input class="filter-input" style="width:100%;" data-param="filter_file_no" placeholder="File No…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td class="sticky-col" style="left:195px;"></td>
                                    <td class="sticky-col" style="left:230px;"><input class="filter-input" style="width:100%;" data-param="filter_hbl_no" placeholder="HB/L…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td colspan="3"></td>
                                    <td><input class="filter-input" data-param="filter_mbl_no" placeholder="MB/L…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td><input class="filter-input" data-param="filter_consignee" placeholder="Consignee…" oninput="applyFiltersTyping()" onkeyup="if(event.key === 'Enter') applyFilters()"></td>
                                    <td colspan="9"></td>
                                </tr>
                            </thead>
                            <tbody id="grid-body">
                                @include('ocean-import.partials.hbl-list-rows', ['hbls' => $hbls])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── PAGINATION ── --}}
                                 <div class="portlet-tool bottom">
                                     <div style="display:flex;justify-content:space-between;width:100%;align-items:center;">
                                         <div id="pagination-container">
                                             {{ $hbls->appends(request()->query())->links('vendor.pagination.custom') }}
                                         </div>
                                         <div style="font-size:10px;color:#64748b;">
                                             Showing <span id="stat-first">{{ $hbls->firstItem() ?? 0 }}</span> – <span id="stat-last">{{ $hbls->lastItem() ?? 0 }}</span> of <span id="stat-total">{{ $hbls->total() }}</span> records
                                         </div>
                                     </div>
                                 </div>

        </div>
    </div>

    @push('scripts')
    <script>
    /* ================================================================
       EXCEL EXPORT WITHOUT HARD REFRESH
    ================================================================ */
    function exportExcel() {
        showToast('info', 'Preparing Excel export...');
        var url = new URL('{{ route("ocean-import.export-csv") }}', window.location.origin);
        url.searchParams.set('type', 'hbl');
        var searchVal = document.getElementById('quick-search')?.value?.trim();
        if (searchVal) url.searchParams.set('search', searchVal);
        document.querySelectorAll('#filter-row .filter-input').forEach(function(inp) {
            var v = inp.value?.trim();
            var param = inp.dataset.param;
            if (param && v) url.searchParams.set(param, v);
        });
        var iframe = document.getElementById('download-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'download-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url.toString();
        setTimeout(function() { showToast('success', 'Excel file downloaded!'); }, 1000);
    }

    function getCSRF() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function getSelectedIds() {
        var checked = document.querySelectorAll('.row-check:checked');
        var ids = [];
        for (var i = 0; i < checked.length; i++) {
            var row = checked[i].closest('tr[data-idx]');
            if (row) ids.push(row.dataset.idx);
        }
        return ids;
    }

    function updateToolbar() {
        var checked = document.querySelectorAll('.row-check:checked');
        var all = document.querySelectorAll('.row-check');
        var n = checked.length;
        var sa = document.getElementById('select-all');
        sa.checked = n === all.length && all.length > 0;
        sa.indeterminate = n > 0 && n < all.length;
        var btns = ['btn-copy','btn-delete','btn-block','btn-unblock','btn-profit-s','btn-profit-d','btn-arrival','sel-sales','sel-op'];
        for (var i = 0; i < btns.length; i++) {
            var el = document.getElementById(btns[i]);
            if (el) el.disabled = n === 0;
        }
        document.getElementById('btn-copy').disabled = n !== 1;
        var badge = document.getElementById('sel-badge');
        badge.style.display = n > 0 ? 'inline' : 'none';
        badge.textContent = n + ' selected';
        var rows = document.querySelectorAll('#grid-body tr[data-idx]');
        for (var i = 0; i < rows.length; i++) {
            var cb = rows[i].querySelector('.row-check');
            if (cb && cb.checked) {
                rows[i].classList.add('row-selected');
            } else {
                rows[i].classList.remove('row-selected');
            }
        }
    }
    function toggleSelectAll(el) {
        var cbs = document.querySelectorAll('.row-check');
        for (var i = 0; i < cbs.length; i++) {
            cbs[i].checked = el.checked;
        }
        updateToolbar();
    }
    function rowClick(e, row) {
        if (['A','INPUT','BUTTON','I'].indexOf(e.target.tagName) >= 0) return;
        var cb = row.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }
    function toggleLock(el) {
        var row = el.closest('tr');
        var id = row.dataset.idx;
        var locked = el.classList.contains('fa-lock');
        var action = locked ? 'unblock' : 'block';
        var url = action === 'block' ? '{{ route("ocean-import.bulk-block") }}' : '{{ route("ocean-import.bulk-unblock") }}';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF(), 'Accept': 'application/json' },
            body: JSON.stringify({ ids: [id], type: 'hbl' })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                if (locked) {
                    el.classList.remove('fa-lock');
                    el.classList.add('fa-unlock');
                    el.style.color = '#22c55e';
                    el.title = 'Unlocked';
                } else {
                    el.classList.remove('fa-unlock');
                    el.classList.add('fa-lock');
                    el.style.color = '#94a3b8';
                    el.title = 'Locked';
                }
                showToast('success', locked ? 'HBL unlocked' : 'HBL locked');
            } else { showToast('error', data.message || 'Failed to update'); }
        }).catch(function() { showToast('error', 'Failed to update lock status'); });
    }
    function toggleFlag(el) {
        var computed = window.getComputedStyle(el).color;
        if (computed === 'rgb(239, 68, 68)') { el.style.color = '#e2e8f0'; } else { el.style.color = '#ef4444'; }
    }

    /* ================================================================
       COPY, DELETE, BLOCK/UNBLOCK
    ================================================================ */
    var _isCopying = false;
    function copySelected() {
        if (_isCopying) return;
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length !== 1) return;
        var row = checked[0].closest('tr');
        var shipmentId = row?.dataset?.shipmentId;
        if (!shipmentId) { showToast('error', 'Cannot determine shipment for this HBL.'); return; }
        _isCopying = true;
        var btn = document.getElementById('btn-copy');
        if (btn) btn.disabled = true;
        showToast('info', 'Duplicating shipment...');
        window.location.href = '/ocean-import/create?copy=' + shipmentId;
    }
    function confirmDelete() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        document.getElementById('confirm-msg').textContent = 'You are about to permanently delete ' + ids.length + ' HBL(s). This cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }
    function executeDelete() {
        closeConfirm();
        var ids = getSelectedIds();
        if (!ids.length) return;
        fetch('{{ route('ocean-import.bulk-delete') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCSRF(), 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ ids: ids, type: 'hbl', _method: 'DELETE' })
        }).then(function(r) { return r.json(); }).then(function(d) { showToast('success', d.message || 'Deleted.'); updateGrid(window.location.href); }).catch(function() { showToast('error', 'Delete failed.'); });
    }
    function blockSelected() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        fetch('{{ route('ocean-import.bulk-block') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCSRF(), 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ ids: ids, type: 'hbl' })
        }).then(function(r) { return r.json(); }).then(function(d) { showToast('success', d.message || 'Blocked.'); updateGrid(window.location.href); }).catch(function() { showToast('error', 'Block failed.'); });
    }
    function unblockSelected() {
        var ids = getSelectedIds();
        if (!ids.length) return;
        fetch('{{ route('ocean-import.bulk-unblock') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCSRF(), 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ ids: ids, type: 'hbl' })
        }).then(function(r) { return r.json(); }).then(function(d) { showToast('success', d.message || 'Unblocked.'); updateGrid(window.location.href); }).catch(function() { showToast('error', 'Unblock failed.'); });
    }

    /* ================================================================
       PROFIT REPORTS & ARRIVAL NOTICE
    ================================================================ */
    function profitSummary() {
        var ids = getSelectedIds();
        if (!ids.length) { showToast('error', 'Please select at least one HBL'); return; }
        showToast('info', 'Opening Revenue/Cost Report...');
        var params = new URLSearchParams();
        for (var i = 0; i < ids.length; i++) { params.append('hbl_ids[]', ids[i]); }
        params.set('module', 'ocean_import');
        var url = '/accounting/report/revenue-cost?' + params.toString();
        window.open(url, '_blank');
    }
    function profitDetail() {
        var ids = getSelectedIds();
        if (!ids.length) { showToast('error', 'Please select at least one HBL'); return; }
        showToast('info', 'Opening Revenue/Cost Report (Detailed)...');
        var params = new URLSearchParams();
        for (var i = 0; i < ids.length; i++) { params.append('hbl_ids[]', ids[i]); }
        params.set('module', 'ocean_import');
        params.set('detailed', '1');
        var url = '/accounting/report/revenue-cost?' + params.toString();
        window.open(url, '_blank');
    }
    function arrivalNotice() {
        var ids = getSelectedIds();
        if (!ids.length) { showToast('error', 'Please select at least one HBL'); return; }
        if (ids.length > 1) { showToast('error', 'Please select only one HBL for Arrival Notice'); return; }
        showToast('info', 'Opening shipment details...');
        var checked = document.querySelectorAll('.row-check:checked');
        var row = checked[0].closest('tr');
        var shipmentId = row?.dataset?.shipmentId;
        if (shipmentId) { window.open('/ocean-import/' + shipmentId + '/edit', '_blank'); } else { showToast('error', 'Cannot find shipment ID'); }
    }

    /* ================================================================
       CHANGE SALES / OP
    ================================================================ */
    function changeSales(sel) {
        var ids = getSelectedIds();
        var salesId = sel.value;
        if (!ids.length || !salesId) { sel.value = ''; return; }
        fetch('{{ route('ocean-import.bulk-change-sales') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCSRF() },
            body: JSON.stringify({ ids: ids, sales_person_id: salesId, type: 'hbl' })
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) { showToast('success', d.message); updateGrid(window.location.href); } else showToast('error', d.message || 'Failed.');
            sel.value = '';
        }).catch(function() { showToast('error', 'Sales change failed.'); sel.value = ''; });
    }
    function changeOp(sel) {
        var ids = getSelectedIds();
        var opId = sel.value;
        if (!ids.length || !opId) { sel.value = ''; return; }
        fetch('{{ route('ocean-import.bulk-change-op') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCSRF() },
            body: JSON.stringify({ ids: ids, op_id: opId, type: 'hbl' })
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) { showToast('success', d.message); updateGrid(window.location.href); } else showToast('error', d.message || 'Failed.');
            sel.value = '';
        }).catch(function() { showToast('error', 'OP change failed.'); sel.value = ''; });
    }

    /* ================================================================
       FILTER & GRID UPDATE
    ================================================================ */
    var filterOpen = false;
    function toggleFilter() {
        filterOpen = !filterOpen;
        var filterRow = document.getElementById('filter-row');
        filterRow.style.display = filterOpen ? 'table-row' : 'none';
        document.getElementById('btn-filter').classList.toggle('active', filterOpen);
        if (filterOpen) {
            var params = new URLSearchParams(window.location.search);
            var inputs = document.querySelectorAll('#filter-row .filter-input');
            for (var i = 0; i < inputs.length; i++) {
                var param = inputs[i].dataset.param;
                if (param) {
                    var val = params.get(param);
                    if (val) inputs[i].value = val;
                }
            }
            if (inputs.length > 0) inputs[0].focus();
        } else {
            var inputs = document.querySelectorAll('#filter-row .filter-input');
            for (var i = 0; i < inputs.length; i++) { inputs[i].value = ''; }
            applyFilters();
        }
    }
    async function updateGrid(url) {
        try {
            var response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            var data = await response.json();
            
            if (!response.ok || data.success === false) {
                console.error('Server error:', data);
                showToast('error', 'Server error: ' + (data.error || 'Unknown error'));
                if (data.trace) console.error('Stack trace:', data.trace);
                return;
            }
            
            if (!data.html) {
                console.error('Invalid response:', data);
                showToast('error', 'Invalid response: missing html');
                return;
            }
            
            document.getElementById('grid-body').innerHTML = data.html;
            document.getElementById('pagination-container').innerHTML = data.pagination || '';
            document.getElementById('stat-first').textContent = data.first || 0;
            document.getElementById('stat-last').textContent = data.last || 0;
            document.getElementById('stat-total').textContent = data.total || 0;
            updateToolbar();
        } catch (e) {
            console.error('updateGrid error:', e);
            showToast('error', 'Failed to update grid: ' + e.message);
        }
    }
    document.addEventListener('click', function(e) {
        var link = e.target.closest('.pagination a.tp-page-btn, .tp-pagination a.tp-page-btn');
        if (link) { e.preventDefault(); updateGrid(link.href); }
    });
    var searchDebounce;
    function quickSearch(val) {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function() {
            var q = val.trim();
            var url = new URL(window.location.href);
            url.searchParams.delete('page');
            if (!q) url.searchParams.delete('search'); else url.searchParams.set('search', q);
            updateGrid(url.toString());
        }, 400);
    }
    var filterDebounce;
    function applyFiltersTyping() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(applyFilters, 400);
    }
    function applyFilters() {
        clearTimeout(filterDebounce);
        filterDebounce = setTimeout(function() {
            var url = new URL(window.location.href);
            url.search = '';
            var searchVal = document.getElementById('quick-search')?.value?.trim();
            if (searchVal) url.searchParams.set('search', searchVal);
            var inputs = document.querySelectorAll('#filter-row .filter-input');
            for (var i = 0; i < inputs.length; i++) {
                var v = inputs[i].value?.trim();
                var param = inputs[i].dataset.param;
                if (param && v) url.searchParams.set(param, v);
            }
            updateGrid(url.toString());
        }, 200);
    }

    /* ================================================================
       CONFIG PANEL & COLOR PICKER
    ================================================================ */
    var PINNED_COLS = ['check', 'lock', 'flag', 'file_no', 'color', 'hbl_no'];
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
        var ths = document.querySelectorAll('#header-row th');
        for (var i = 0; i < ths.length; i++) {
            var th = ths[i];
            var col = th.dataset.col;
            if (!col || PINNED_COLS.indexOf(col) >= 0) continue;
            var label = document.createElement('label');
            var cb    = document.createElement('input');
            cb.type    = 'checkbox';
            cb.checked = th.style.display !== 'none';
            cb.onchange = (function(colName) { return function() { toggleColumn(colName, this.checked); }; })(col);
            label.appendChild(cb);
            label.appendChild(document.createTextNode(' ' + th.textContent.trim()));
            container.appendChild(label);
        }
    }
    function toggleColumn(colName, show) {
        var th  = document.querySelector('#header-row th[data-col="' + colName + '"]');
        if (!th) return;
        var idx = Array.prototype.indexOf.call(th.parentElement.children, th);
        th.style.display = show ? '' : 'none';
        var rows = document.querySelectorAll('#grid-body tr, #filter-row');
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].querySelectorAll('td, th');
            if (cells[idx]) cells[idx].style.display = show ? '' : 'none';
        }
    }
    document.addEventListener('click', function(e) {
        var panel = document.getElementById('config-panel');
        var btn   = document.getElementById('btn-config');
        if (panel && panel.style.display !== 'none' && !panel.contains(e.target) && !btn.contains(e.target)) {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    });
    var COLOR_OPTIONS = [
        { label: 'Urgent', value: '#E08283' },
        { label: 'Ready to bill', value: '#F3C200' },
        { label: 'Ready to close', value: '#25A69A' },
        { label: 'Postpone', value: '#4B77BE' },
        { label: 'Freight Finalized', value: '#9B9B9B' },
    ];
    var _colorShipmentId = null;
    function openColorPicker(id, currentColor) {
        _colorShipmentId = id;
        var grid = document.getElementById('color-picker-grid');
        var html = '';
        for (var i = 0; i < COLOR_OPTIONS.length; i++) {
            var o = COLOR_OPTIONS[i];
            var active = o.value === currentColor;
            html += '<div class="color-picker-opt' + (active ? ' active' : '') + '" onclick="selectColor(\'' + o.value + '\', this)"><span class="swatch" style="background:' + o.value + '"></span><span>' + o.label + '</span><i class="fa fa-check"></i></div>';
        }
        grid.innerHTML = html;
        document.getElementById('color-picker-overlay').classList.add('open');
    }
    function selectColor(color, el) {
        var opts = document.querySelectorAll('.color-picker-opt');
        for (var i = 0; i < opts.length; i++) { opts[i].classList.remove('active'); }
        el.classList.add('active');
        var id = _colorShipmentId;
        fetch('{{ route("ocean-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
            body: JSON.stringify({ color: color }),
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var marks = document.querySelectorAll('tr[data-shipment-id="' + id + '"] .color-mark');
                for (var i = 0; i < marks.length; i++) { marks[i].style.background = color; }
                showToast('success', 'Status color updated');
            }
        }).catch(function() { showToast('error', 'Failed to update color'); });
        closeColorPicker();
    }
    function closeColorPicker() {
        document.getElementById('color-picker-overlay').classList.remove('open');
        _colorShipmentId = null;
    }
    function clearColor() {
        var id = _colorShipmentId;
        fetch('{{ route("ocean-import.update-color", "ID") }}'.replace('ID', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCSRF() },
            body: JSON.stringify({ color: '' }),
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var marks = document.querySelectorAll('tr[data-shipment-id="' + id + '"] .color-mark');
                for (var i = 0; i < marks.length; i++) { marks[i].style.background = '#94a3b8'; }
                showToast('success', 'Status color cleared');
            }
        }).catch(function() { showToast('error', 'Failed to clear color'); });
        closeColorPicker();
    }

    /* ================================================================
       TOAST
    ================================================================ */
    function showToast(type, msg) {
        var icons = { success:'check-circle', error:'times-circle', info:'info-circle' };
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + icons[type] + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() { t.remove(); }, 3000);
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
    </script>
    @endpush
    
    <div class="overlay color-picker-overlay" id="color-picker-overlay" onclick="if(event.target===this) closeColorPicker()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-title"><i class="fa fa-paint-brush" style="color:#3b82f6;"></i> Status Color</div>
                <button class="modal-close" onclick="closeColorPicker()"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <div class="color-picker-grid" id="color-picker-grid">
                </div>
                <div class="color-clear-btn" onclick="clearColor()">Clear / No Color</div>
            </div>
        </div>
    </div>

</x-layout>
