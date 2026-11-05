<x-layout>
    @push('styles')
    <x-list-styles />
    <style>
        .gj-badge { padding:1px 7px;border-radius:10px;font-size:9px;font-weight:700;display:inline-block; }
        .badge-posted { background:#dcfce7;color:#15803d; }
        .badge-draft  { background:#fef3c7;color:#92400e; }
        .badge-voided { background:#fee2e2;color:#b91c1c; }
        .col-link { color:#2563eb;text-decoration:none;font-weight:600; }
        .col-link:hover { text-decoration:underline; }
        .grid-table thead th { position:sticky;top:0;z-index:5;background:#f8fafc;color:#475569;font-size:10px;font-weight:700;border-bottom:1px solid #cbd5e1;border-right:1px solid #e2e8f0;padding:5px 7px;white-space:nowrap;text-align:left; }
        .grid-table tbody td { padding:4px 7px;border-bottom:1px solid #e2e8f0;border-right:1px solid #e2e8f0;font-size:10px;vertical-align:middle;color:#334155; }
        .grid-table tbody tr:hover td { background:#f8fafc; }
        .grid-table tbody tr.row-selected td { background:#eff6ff; }
        .portlet-tool { display:flex;align-items:center;justify-content:space-between;padding:4px 8px;background:#fff;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;gap:6px; }
        .portlet-tool.bottom { border-top:1px solid #e2e8f0;border-bottom:none;background:#f8fafc; }
        .filter-input { height:20px;padding:0 5px;border:1px solid #bfdbfe;font-size:9px;border-radius:2px;background:#fff;width:100%;box-sizing:border-box; }
        .filter-input:focus { outline:none;border-color:#3b82f6; }
        #filter-row td { padding:3px 4px; }
        .btn-tool { background:#64748b;color:#fff;border:none;padding:0 8px;height:22px;font-size:10px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px;border-radius:2px;transition:all 0.15s; }
        .btn-tool:hover { background:#475569; }
        .btn-tool.danger { background:#ef4444; }
        .btn-tool.danger:hover { background:#dc2626; }
        .btn-tool.green { background:#22c55e; }
        .btn-tool.green:hover { background:#16a34a; }
        .btn-tool:disabled { opacity:0.4;cursor:not-allowed; }
        @media (max-width:768px) {
            .portlet-tool { flex-direction:column;align-items:flex-start; }
            .gf-toolbar-right { width:100%; }
        }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    {{-- Delete Confirm Modal --}}
    <div class="overlay" id="confirm-overlay" onclick="if(event.target===this)closeConfirm()">
        <div class="confirm-box">
            <div class="confirm-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <h4>Delete Journal Entry(ies)?</h4>
            <p id="confirm-msg">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="btn-tool" style="padding:0 18px;height:28px;" onclick="closeConfirm()">Cancel</button>
                <button class="btn-tool danger" style="padding:0 18px;height:28px;" onclick="executeDelete()">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <div class="page-content">
        {{-- Breadcrumbs --}}
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a> <i class="fa fa-angle-right"></i></li>
                <li>Accounting <i class="fa fa-angle-right"></i></li>
                <li>Journal <i class="fa fa-angle-right"></i></li>
                <li><span style="color:#333;font-weight:700;">General Journal</span></li>
            </ul>
        </div>

        <div class="portlet light">
            {{-- Portlet Title --}}
            <div class="portlet-title">
                <div class="caption" style="display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-book" style="color:#3b82f6;"></i>
                    <span class="caption-subject">General Journal</span>
                    <span id="sel-badge" style="display:none;font-size:10px;color:#3b82f6;font-weight:600;background:#eff6ff;padding:1px 6px;border-radius:10px;border:1px solid #bfdbfe;"></span>
                </div>
                <div class="actions" style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                    <button class="btn-action-round" id="btn-filter" onclick="toggleFilter()" title="Toggle column filters">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <div style="position:relative;">
                        <button class="btn-action-round" id="btn-config" onclick="toggleConfig()" title="Column visibility">
                            <i class="fa fa-cogs"></i> Config
                        </button>
                        <div class="config-panel" id="config-panel" style="display:none;">
                            <div class="config-panel-title">Column Visibility</div>
                            <div id="col-toggles"></div>
                        </div>
                    </div>
                    <button class="btn-action-round white" onclick="exportExcel()" title="Export to Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                    <a href="{{ route('accounting.general-journal.print') }}" target="_blank" class="btn-action-round white" title="Print Report">
                        <i class="fa fa-print"></i> Print
                    </a>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="portlet-tool">
                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <div class="btn-group" style="display:flex;gap:0;">
                        <a class="btn-tool green" href="{{ route('accounting.journal.entry') }}" title="New Journal Entry" style="text-decoration:none;padding:0 10px;">
                            <i class="fa fa-plus"></i> New Entry
                        </a>
                    </div>
                    <div class="btn-group" style="display:flex;gap:0;">
                        <button class="btn-tool danger" id="btn-delete" disabled title="Delete Selected" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="gf-toolbar-right" style="display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-search" style="font-size:10px;color:#94a3b8;"></i>
                    <input type="text" id="quick-search" class="input-inline" style="width:180px;"
                           placeholder="Search entry no, description, remark…"
                           value="{{ request('search') }}"
                           oninput="quickSearch(this.value)">
                </div>
            </div>

            {{-- Table --}}
            <div style="width:100%;overflow-x:auto;">
                <table class="grid-table" id="main-grid" style="min-width:900px;width:100%;border-collapse:collapse;">
                    <thead>
                        <tr id="header-row">
                            <th data-col="check" style="width:28px;text-align:center;">
                                <input type="checkbox" id="select-all" onclick="toggleAll(this)">
                            </th>
                            <th data-col="status_icon" style="width:26px;text-align:center;" title="Status">
                                <i class="fa fa-circle" style="font-size:9px;color:#94a3b8;"></i>
                            </th>
                            <th data-col="post_date" style="width:90px;">Post Date</th>
                            <th data-col="seq" style="width:46px;text-align:center;">Seq</th>
                            <th data-col="entry_no" style="width:140px;">Entry No</th>
                            <th data-col="remark" style="min-width:200px;">Remark / Description</th>
                            <th data-col="debit" style="width:100px;text-align:right;">Debit ($)</th>
                            <th data-col="credit" style="width:100px;text-align:right;">Credit ($)</th>
                            <th data-col="balanced" style="width:40px;text-align:center;" title="Balanced">Bal.</th>
                            <th data-col="status" style="width:70px;text-align:center;">Status</th>
                            <th data-col="lines" style="width:44px;text-align:center;">Lines</th>
                            <th data-col="issued_by" style="width:110px;">Issued By</th>
                            <th data-col="office" style="width:70px;">Office</th>
                            <th data-col="actions" style="width:50px;text-align:center;">Act.</th>
                        </tr>
                        {{-- Filter Row --}}
                        <tr id="filter-row" style="display:none;background:#eff6ff;">
                            <td colspan="2"></td>
                            <td>
                                <input class="filter-input" id="filter-from-date" type="date" data-param="from_date"
                                       value="{{ request('from_date') }}" onchange="applyFilters()" title="From Date">
                            </td>
                            <td></td>
                            <td>
                                <input class="filter-input" data-param="search" placeholder="Entry No…"
                                       value="{{ request('search') }}" oninput="applyFiltersTyping()">
                            </td>
                            <td>
                                <input class="filter-input" id="filter-remark" data-param="remark_filter" placeholder="Remark…"
                                       oninput="applyFiltersTyping()">
                            </td>
                            <td colspan="2">
                                <input class="filter-input" id="filter-to-date" type="date" data-param="to_date"
                                       value="{{ request('to_date') }}" onchange="applyFilters()" title="To Date">
                            </td>
                            <td></td>
                            <td>
                                <select class="filter-input" data-param="status" onchange="applyFilters()" style="padding:0 2px;">
                                    <option value="">All</option>
                                    <option value="POSTED" {{ request('status') === 'POSTED' ? 'selected' : '' }}>Posted</option>
                                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                    <option value="VOIDED" {{ request('status') === 'VOIDED' ? 'selected' : '' }}>Voided</option>
                                </select>
                            </td>
                            <td></td>
                            <td></td>
                            <td>
                                <select class="filter-input" data-param="office_id" onchange="applyFilters()" style="padding:0 2px;">
                                    <option value="">All</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                            {{ $office->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="text-align:center;">
                                <button class="btn-tool" style="height:18px;font-size:9px;padding:0 6px;" onclick="clearFilters()">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </thead>
                    <tbody id="grid-body">
                        @include('accounting.partials.general-journal-rows', ['entries' => $entries])
                    </tbody>
                </table>
            </div>

            {{-- Bottom Pagination --}}
            <div class="portlet-tool bottom">
                <div style="font-size:10px;color:#64748b;">
                    Showing <span id="stat-first">{{ $entries->firstItem() ?? 0 }}</span>
                    – <span id="stat-last">{{ $entries->lastItem() ?? 0 }}</span>
                    of <span id="stat-total">{{ $entries->total() }}</span> records
                </div>
                <div id="pagination-wrap">
                    {{ $entries->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    <iframe id="excel-frame" style="display:none;"></iframe>

    @push('scripts')
    <script>
    /* ── CSRF ── */
    function getCSRF() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    /* ── TOAST ── */
    function showToast(type, msg) {
        var icons = { success:'check-circle', error:'times-circle', info:'info-circle', warning:'exclamation-circle' };
        var t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
        document.getElementById('toast-container').appendChild(t);
        setTimeout(function() { t.remove(); }, 4000);
    }

    /* ── TOOLBAR SELECTION BADGE ── */
    function updateToolbar() {
        var checked = document.querySelectorAll('.row-check:checked');
        var all     = document.querySelectorAll('.row-check');
        var n       = checked.length;
        var sa      = document.getElementById('select-all');
        if (sa) {
            sa.checked       = n === all.length && all.length > 0;
            sa.indeterminate = n > 0 && n < all.length;
        }
        var hasPosted = false;
        checked.forEach(function(cb) {
            var row = cb.closest('tr');
            if (row && (row.dataset.status === 'POSTED' || row.dataset.status === 'APPROVED')) {
                hasPosted = true;
            }
        });
        var delBtn = document.getElementById('btn-delete');
        if (delBtn) {
            delBtn.disabled = (n === 0 || hasPosted);
            delBtn.title = hasPosted ? 'Document Control: Posted journal entries cannot be deleted.' : 'Delete Selected';
        }

        var badge = document.getElementById('sel-badge');
        if (badge) {
            badge.style.display = n > 0 ? 'inline' : 'none';
            badge.textContent   = n + ' selected';
        }
        document.querySelectorAll('#grid-body tr[data-id]').forEach(function(tr) {
            var cb = tr.querySelector('.row-check');
            if (cb) tr.classList.toggle('row-selected', cb.checked);
        });
    }

    function toggleAll(el) {
        document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = el.checked; });
        updateToolbar();
    }

    function rowClick(e, tr) {
        if (['INPUT','A','BUTTON','I'].indexOf(e.target.tagName) !== -1) return;
        var cb = tr.querySelector('.row-check');
        if (cb) { cb.checked = !cb.checked; updateToolbar(); }
    }

    /* ── GRID URL ── */
    function getGridUrl() {
        var url = new URL(window.location.href);
        // Collect filter-row inputs
        document.querySelectorAll('.filter-input[data-param]').forEach(function(el) {
            var p = el.getAttribute('data-param');
            if (el.value) url.searchParams.set(p, el.value);
            else url.searchParams.delete(p);
        });
        // Quick search
        var qs = document.getElementById('quick-search');
        if (qs && qs.value) url.searchParams.set('search', qs.value);
        else url.searchParams.delete('search');
        url.searchParams.delete('page');
        return url;
    }

    /* ── AJAX GRID UPDATE ── */
    function updateGrid(url) {
        if (!url) url = getGridUrl();
        var fetchUrl = new URL(url.toString());
        fetchUrl.searchParams.set('ajax', '1');

        fetch(fetchUrl.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { showToast('error', data.error || 'Failed to update grid'); return; }
            document.getElementById('grid-body').innerHTML       = data.html;
            document.getElementById('pagination-wrap').innerHTML  = data.pagination;
            document.getElementById('stat-first').textContent    = data.first;
            document.getElementById('stat-last').textContent     = data.last;
            document.getElementById('stat-total').textContent    = data.total;
            updateToolbar();
        })
        .catch(function(e) { showToast('error', 'Network error'); console.error(e); });
    }

    /* ── SEARCH ── */
    var _searchTimer = null;
    function quickSearch(val) {
        clearTimeout(_searchTimer);
        _searchTimer = setTimeout(function() {
            var url = getGridUrl();
            window.history.pushState({}, '', url);
            updateGrid(url);
        }, 350);
    }

    /* ── FILTERS ── */
    var _filterTimer = null;
    function applyFiltersTyping() {
        clearTimeout(_filterTimer);
        _filterTimer = setTimeout(function() { applyFilters(); }, 350);
    }

    function applyFilters() {
        var url = getGridUrl();
        window.history.pushState({}, '', url);
        updateGrid(url);
    }

    var _filterOpen = false;
    function toggleFilter() {
        _filterOpen = !_filterOpen;
        var row = document.getElementById('filter-row');
        row.style.display = _filterOpen ? '' : 'none';
        var btn = document.getElementById('btn-filter');
        if (_filterOpen) {
            btn.style.background = '#3b82f6';
            btn.style.borderColor = '#2563eb';
            btn.style.color = '#fff';
        } else {
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.style.color = '';
        }
    }

    function clearFilters() {
        document.querySelectorAll('.filter-input').forEach(function(el) { el.value = ''; });
        document.getElementById('quick-search').value = '';
        var url = new URL(window.location.origin + window.location.pathname);
        window.history.pushState({}, '', url);
        updateGrid(url);
    }

    /* ── PAGINATION ── */
    document.addEventListener('click', function(e) {
        var link = e.target.closest('#pagination-wrap a');
        if (link && link.href) {
            e.preventDefault();
            var url = new URL(link.href);
            window.history.pushState({}, '', url);
            updateGrid(url);
        }
    });

    /* ── DELETE ── */
    var _deleteIds = [];
    function confirmDelete() {
        var checked = [...document.querySelectorAll('.row-check:checked')];
        if (!checked.length) { showToast('error', 'Please select row(s) to delete.'); return; }
        var hasPosted = checked.some(function(cb) {
            var row = cb.closest('tr');
            return row && (row.dataset.status === 'POSTED' || row.dataset.status === 'APPROVED');
        });
        if (hasPosted) {
            showToast('error', 'Document Control: Posted journal entries cannot be deleted. Reversal or cancellation is required.');
            return;
        }
        _deleteIds = checked.map(function(cb) { return cb.value; });
        document.getElementById('confirm-msg').textContent = 'Delete ' + _deleteIds.length + ' entry(ies)? This action cannot be undone.';
        document.getElementById('confirm-overlay').classList.add('open');
    }
    function closeConfirm() {
        document.getElementById('confirm-overlay').classList.remove('open');
    }
    function executeDelete() {
        closeConfirm();
        fetch('{{ route("accounting.general-journal.delete") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': getCSRF(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: _deleteIds })
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success) {
                showToast('success', resp.message || 'Deleted!');
                setTimeout(function() { updateGrid(); }, 300);
            } else {
                showToast('error', resp.message || 'Delete failed.');
            }
        })
        .catch(function() { showToast('error', 'Network error during delete.'); });
    }

    /* ── EXCEL EXPORT — fetch + Blob (no page navigation, works through auth) ── */
    function exportExcel() {
        var url = new URL(window.location.origin + '{{ route("accounting.general-journal.export") }}');
        var params = new URLSearchParams(window.location.search);
        ['search','from_date','to_date','office_id','status'].forEach(function(k) {
            if (params.has(k)) url.searchParams.set(k, params.get(k));
        });

        // Also pull active filter-row values
        document.querySelectorAll('.filter-input[data-param]').forEach(function(el) {
            var p = el.getAttribute('data-param');
            if (el.value) url.searchParams.set(p, el.value);
        });

        showToast('info', 'Preparing Excel export…');

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/csv,application/octet-stream,*/*'
            }
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Export failed — HTTP ' + response.status);
            var disposition = response.headers.get('Content-Disposition') || '';
            var match = disposition.match(/filename[^;=\n]*=["']?([^"';\n]+)/i);
            var filename = match ? match[1].trim() : 'general-journal-' + new Date().toISOString().slice(0, 10) + '.csv';
            return response.blob().then(function(blob) { return { blob: blob, filename: filename }; });
        })
        .then(function(result) {
            var objectUrl = window.URL.createObjectURL(result.blob);
            var a = document.createElement('a');
            a.href = objectUrl;
            a.download = result.filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(objectUrl);
            showToast('success', 'Excel downloaded successfully!');
        })
        .catch(function(err) {
            console.error('Excel export error:', err);
            showToast('error', 'Excel export failed. Please try again.');
        });
    }

    /* ── COLUMN VISIBILITY ── */
    var COL_DEFAULTS = {
        check:true, status_icon:true, post_date:true, seq:true, entry_no:true,
        remark:true, debit:true, credit:true, balanced:true, status:true,
        lines:true, issued_by:true, office:true, actions:true
    };

    function loadColPrefs() {
        var prefs = Object.assign({}, COL_DEFAULTS);
        try {
            var saved = JSON.parse(localStorage.getItem('gjCols'));
            if (saved) Object.assign(prefs, saved);
        } catch(e) {}
        return prefs;
    }

    function applyColVisibility() {
        var prefs = loadColPrefs();
        Object.keys(prefs).forEach(function(key) {
            document.querySelectorAll('[data-col="' + key + '"]').forEach(function(el) {
                el.style.display = prefs[key] ? '' : 'none';
            });
        });
    }

    var _configOpen = false;
    function toggleConfig() {
        _configOpen = !_configOpen;
        var panel = document.getElementById('config-panel');
        if (_configOpen) {
            var prefs = loadColPrefs();
            var html = '';
            Object.keys(COL_DEFAULTS).forEach(function(key) {
                if (key === 'check') return;
                var label = key.replace(/_/g,' ').replace(/\b\w/g,function(l){return l.toUpperCase();});
                html += '<label><input type="checkbox" ' + (prefs[key] ? 'checked' : '') + ' onchange="toggleCol(\'' + key + '\',this)"> ' + label + '</label>';
            });
            document.getElementById('col-toggles').innerHTML = html;
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    }

    function toggleCol(name, checkbox) {
        var prefs = loadColPrefs();
        prefs[name] = checkbox.checked;
        localStorage.setItem('gjCols', JSON.stringify(prefs));
        applyColVisibility();
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#btn-config') && !e.target.closest('#config-panel')) {
            document.getElementById('config-panel').style.display = 'none';
            _configOpen = false;
        }
    });

    /* ── INIT ── */
    document.addEventListener('DOMContentLoaded', function() {
        applyColVisibility();
        updateToolbar();
    });
    </script>
    @endpush
</x-layout>
