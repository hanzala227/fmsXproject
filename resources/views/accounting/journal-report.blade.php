<x-layout>
    @push('styles')
    <style>
        .page-content { padding: 8px 12px; background: #eef1f5; min-height: calc(100vh - 50px); font-family: 'Inter', 'Open Sans', sans-serif !important; }
        .portlet.light { background-color: #fff; border: 1px solid #cbd5e1; border-radius: 2px; margin-bottom: 10px !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .portlet-title { padding: 6px 12px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; min-height: 32px; background: #f8fafc; }
        .portlet-body { padding: 12px 14px; }
        .caption-subject { color: #1e293b; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        .page-bar { background-color: #fff; padding: 8px 20px; margin-bottom: 12px; border: 1px solid #e9ebec; border-radius: 4px; }
        .page-breadcrumb { list-style: none; padding: 0; margin: 0; display: flex; align-items: center; }
        .page-breadcrumb li { font-size: 11px; color: #64748b; display: flex; align-items: center; }
        .page-breadcrumb li a { color: #3b82f6; text-decoration: none; font-weight: 500; }
        .page-breadcrumb li i { margin: 0 8px; font-size: 10px; opacity: 0.5; }

        /* Navigation Tabs matching Ocean Import */
        .nav-tabs-custom { display: flex; gap: 2px; border-bottom: 2px solid #3b82f6; margin-bottom: 14px; padding: 0; list-style: none; }
        .nav-tabs-custom li { margin: 0; }
        .nav-tabs-custom li a { display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; font-size: 11px; font-weight: 600; color: #64748b; background: #f1f5f9; border: 1px solid #cbd5e1; border-bottom: none; border-radius: 4px 4px 0 0; text-decoration: none; cursor: pointer; transition: all 0.15s; }
        .nav-tabs-custom li a:hover { background: #e2e8f0; color: #1e293b; }
        .nav-tabs-custom li.active a { background: #3b82f6; color: #fff; border-color: #3b82f6; }

        /* Form Grid Layout */
        .form-label-box { background: #eef1f5; width: 130px; padding: 4px 8px; font-size: 11px; color: #333; min-height: 26px; display: flex; align-items: center; flex-shrink: 0; font-weight: 600; border: 1px solid #cbd5e1; border-right: none; }
        .form-input-box { flex-grow: 1; padding: 2px 8px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px; border: 1px solid #cbd5e1; background: #fff; min-height: 26px; }
        .form-group-row { display: flex; align-items: stretch; margin-bottom: 8px; }
        .form-control-gf { height: 24px; border: 1px solid #c2cad8; padding: 2px 6px; font-size: 11px; border-radius: 2px !important; width: 100%; color: #333; outline: none; background: #fff; }
        .form-control-gf:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
        select.form-control-gf { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 4px center; background-size: 8px; padding-right: 16px; }

        .report-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; }
        @media (max-width: 900px) { .report-grid { grid-template-columns: 1fr; } }

        /* Enterprise Action Buttons */
        .button-row { display: flex; justify-content: center; gap: 8px; margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
        .btn-action-round { background: #64748b; color: #fff; border: 1px solid #475569; border-radius: 2px; padding: 0 12px; height: 24px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; text-decoration: none; transition: all 0.15s; box-sizing: border-box; }
        .btn-action-round:hover { background: #475569; color: #fff; }
        .btn-action-round.primary { background: #3b82f6; border-color: #2563eb; }
        .btn-action-round.primary:hover { background: #2563eb; }
        .btn-action-round.green { background: #22c55e; border-color: #16a34a; }
        .btn-action-round.green:hover { background: #16a34a; }
        .btn-action-round.danger { background: #ef4444; border-color: #dc2626; }
        .btn-action-round.danger:hover { background: #dc2626; }
        .btn-action-round:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Report Results Grid */
        .grid-container { width: 100%; overflow-x: auto; background: #fff; margin-top: 14px; border: 1px solid #cbd5e1; border-radius: 2px; }
        .grid-table { border-collapse: collapse; width: 100%; font-size: 10.5px; }
        .grid-table th { background: #f8fafc; color: #475569; font-weight: 600; border: 1px solid #e2e8f0; padding: 5px 6px; white-space: nowrap; text-align: left; }
        .grid-table td { padding: 4px 6px; border: 1px solid #e2e8f0; white-space: nowrap; color: #334155; }
        .grid-table td.num { text-align: right; font-family: 'Consolas', 'Courier New', monospace; font-weight: 500; }
        .grid-table tbody tr:hover { background: #f1f5f9; }
        .grid-table .total-row { background: #1e293b !important; color: #fff; font-weight: 700; }
        .grid-table .total-row td { border-color: #0f172a; color: #fff; }

        /* Summary KPI Cards */
        .summary-row { display: flex; gap: 12px; margin-top: 14px; flex-wrap: wrap; }
        .summary-card { flex: 1; min-width: 140px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        .summary-card .label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px; }
        .summary-card .value { font-size: 15px; font-weight: 700; color: #0f172a; font-family: 'Consolas', 'Courier New', monospace; }
        .summary-card.debit { border-left: 4px solid #3b82f6; }
        .summary-card.credit { border-left: 4px solid #ef4444; }
        .summary-card.balanced { border-left: 4px solid #22c55e; }
        .summary-card.unbalanced { border-left: 4px solid #f59e0b; }

        /* State Badges */
        .badge-status { padding: 2px 8px; border-radius: 10px; font-size: 9.5px; font-weight: 600; text-transform: uppercase; display: inline-block; }
        .badge-status.posted { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-status.draft { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
        .badge-status.voided { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Loading & Empty States */
        .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; background: #fff; border: 1px solid #cbd5e1; margin-top: 14px; }
        .empty-state i { font-size: 32px; display: block; margin-bottom: 8px; color: #cbd5e1; }

        /* Modal Overlay */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px; backdrop-filter: blur(2px); }
        .modal-container { background: #fff; border-radius: 4px; width: 100%; max-width: 900px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); border: 1px solid #cbd5e1; }
        .modal-header { padding: 10px 16px; background: #1e293b; color: #fff; font-weight: 700; font-size: 13px; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 16px; }

        /* Toast Container */
        .toast-container { position: fixed; top: 56px; right: 16px; z-index: 99999; display: flex; flex-direction: column; gap: 6px; pointer-events: none; }
        .toast { background: #1e293b; color: #fff; padding: 8px 14px; border-radius: 4px; font-size: 11px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 8px; animation: toastIn 0.25s ease; pointer-events: all; }
        .toast.success { border-left: 4px solid #22c55e; }
        .toast.error   { border-left: 4px solid #ef4444; }
        .toast.info    { border-left: 4px solid #3b82f6; }
        @keyframes toastIn { from { transform: translateX(40px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
    @endpush

    <div class="toast-container" id="toast-container"></div>

    <div class="page-content" x-data="journalReportApp()" x-init="initApp()">
        {{-- Breadcrumb --}}
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a href="/">Home</a></li>
                <li><i class="fa fa-angle-right"></i> Accounting</li>
                <li><i class="fa fa-angle-right"></i> Report</li>
                <li><i class="fa fa-angle-right"></i> <span style="color:#333;font-weight:700;">Journal Report</span></li>
            </ul>
        </div>

        {{-- Nav Tabs --}}
        <ul class="nav-tabs-custom">
            <li :class="{ 'active': activeTab === 'report' }">
                <a @click="switchTab('report')"><i class="fa fa-pie-chart"></i> Journal Report</a>
            </li>
            <li :class="{ 'active': activeTab === 'entries' }">
                <a @click="switchTab('entries')"><i class="fa fa-list-alt"></i> Journal Entries List</a>
            </li>
            <li :class="{ 'active': activeTab === 'create' }">
                <a @click="switchTab('create')"><i class="fa fa-plus-circle"></i> New Journal Entry</a>
            </li>
        </ul>

        {{-- TAB 1: JOURNAL REPORT --}}
        <div x-show="activeTab === 'report'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title">
                    <span class="caption-subject"><i class="fa fa-book" style="color:#3b82f6;margin-right:6px;"></i>Journal Report Filter & Options</span>
                    <span x-show="isBalanced !== null" class="badge-status" :class="isBalanced ? 'posted' : 'draft'" x-text="isBalanced ? 'BALANCED' : 'UNBALANCED'"></span>
                </div>
                <div class="portlet-body">
                    <div class="report-grid">
                        {{-- LEFT COLUMN --}}
                        <div>
                            <div class="form-group-row">
                                <div class="form-label-box">Period From</div>
                                <div class="form-input-box">
                                    <input type="date" class="form-control-gf" x-model="filters.period_from" @change="saveState()">
                                </div>
                            </div>
                            <div class="form-group-row">
                                <div class="form-label-box">Office</div>
                                <div class="form-input-box">
                                    <select class="form-control-gf" x-model="filters.office_id" @change="saveState()">
                                        <option value="">All Offices</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-row">
                                <div class="form-label-box">G/L Account From</div>
                                <div class="form-input-box">
                                    <select class="form-control-gf" x-model="filters.gl_from" @change="saveState()">
                                        <option value="">-- All Accounts --</option>
                                        @foreach($glAccounts as $acc)
                                            <option value="{{ $acc->code }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN --}}
                        <div>
                            <div class="form-group-row">
                                <div class="form-label-box">Period To</div>
                                <div class="form-input-box">
                                    <input type="date" class="form-control-gf" x-model="filters.period_to" @change="saveState()">
                                </div>
                            </div>
                            <div class="form-group-row">
                                <div class="form-label-box">Currency</div>
                                <div class="form-input-box">
                                    <select class="form-control-gf" x-model="filters.currency_id" @change="saveState()">
                                        <option value="">All Currencies</option>
                                        @foreach($currencies as $cur)
                                            <option value="{{ $cur->id }}">{{ $cur->code }} - {{ $cur->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-row">
                                <div class="form-label-box">G/L Account To</div>
                                <div class="form-input-box">
                                    <select class="form-control-gf" x-model="filters.gl_to" @change="saveState()">
                                        <option value="">-- All Accounts --</option>
                                        @foreach($glAccounts as $acc)
                                            <option value="{{ $acc->code }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Search Input Row --}}
                    <div class="form-group-row" style="margin-top:4px;">
                        <div class="form-label-box">Quick Search</div>
                        <div class="form-input-box">
                            <input type="text" class="form-control-gf" placeholder="Search by Ref. No, Description, or Company..." x-model="filters.search" @input.debounce.300ms="saveState()">
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="button-row">
                        <button type="button" class="btn-action-round primary" @click="fetchReportData()">
                            <i class="fa fa-search"></i> Preview
                        </button>
                        <button type="button" class="btn-action-round" @click="printReport()">
                            <i class="fa fa-print"></i> Print
                        </button>
                        <button type="button" class="btn-action-round green" @click="exportExcel()">
                            <i class="fa fa-file-excel-o"></i> Download Excel
                        </button>
                    </div>
                </div>
            </div>

            {{-- Summary KPI Row --}}
            <div class="summary-row" x-show="hasLoaded">
                <div class="summary-card debit">
                    <div class="label">Total Local Debit</div>
                    <div class="value" x-text="formatMoney(summary.total_debit)"></div>
                </div>
                <div class="summary-card credit">
                    <div class="label">Total Local Credit</div>
                    <div class="value" x-text="formatMoney(summary.total_credit)"></div>
                </div>
                <div class="summary-card" :class="isBalanced ? 'balanced' : 'unbalanced'">
                    <div class="label">Balance Status</div>
                    <div class="value" x-text="isBalanced ? 'Balanced' : 'Unbalanced'"></div>
                </div>
                <div class="summary-card">
                    <div class="label">Total Records</div>
                    <div class="value" x-text="summary.total_records"></div>
                </div>
            </div>

            {{-- Results Grid --}}
            <div x-show="loading" class="empty-state">
                <i class="fa fa-spinner fa-spin"></i>
                <div>Fetching Journal Report data...</div>
            </div>

            <div x-show="!loading && hasLoaded && results.length > 0" class="grid-container">
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th style="width:75px;">Date</th>
                            <th style="width:70px;">G/L No.</th>
                            <th style="width:140px;">G/L Description</th>
                            <th style="width:60px;">Source</th>
                            <th style="width:100px;">Ref No.</th>
                            <th style="width:90px;">Office</th>
                            <th style="width:130px;">Company</th>
                            <th style="width:180px;">Description</th>
                            <th style="width:90px;text-align:right;">Debit</th>
                            <th style="width:90px;text-align:right;">Credit</th>
                            <th style="width:90px;text-align:right;">Foreign Amt</th>
                            <th style="width:40px;text-align:center;">Cur</th>
                            <th style="width:50px;text-align:right;">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in results" :key="row.id">
                            <tr>
                                <td x-text="row.date_formatted"></td>
                                <td x-text="row.gl_no" style="font-weight:600;color:#1e40af;"></td>
                                <td x-text="row.gl_desc"></td>
                                <td x-text="row.source" style="text-align:center;color:#64748b;"></td>
                                <td x-text="row.ref_no" style="font-weight:600;"></td>
                                <td x-text="row.office"></td>
                                <td x-text="row.company"></td>
                                <td x-text="row.description"></td>
                                <td class="num" x-text="row.debit > 0 ? formatMoney(row.debit) : ''"></td>
                                <td class="num" x-text="row.credit > 0 ? formatMoney(row.credit) : ''"></td>
                                <td class="num" x-text="row.foreign_amount > 0 ? formatMoney(row.foreign_amount) : ''"></td>
                                <td style="text-align:center;" x-text="row.cur"></td>
                                <td class="num" x-text="row.rate != 1 ? row.rate.toFixed(4) : ''"></td>
                            </tr>
                        </template>
                        <tr class="total-row">
                            <td colspan="8" style="text-align:right;">TOTAL</td>
                            <td class="num" x-text="formatMoney(summary.total_debit)"></td>
                            <td class="num" x-text="formatMoney(summary.total_credit)"></td>
                            <td colspan="3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div x-show="!loading && hasLoaded && results.length === 0" class="empty-state">
                <i class="fa fa-folder-open-o"></i>
                <div>No journal lines match the selected period and criteria.</div>
            </div>

            <div x-show="!hasLoaded && !loading" class="empty-state">
                <i class="fa fa-search"></i>
                <div>Set filter parameters and click <strong>Preview</strong> to display the Journal Report.</div>
            </div>
        </div>

        {{-- TAB 2: JOURNAL ENTRIES LIST --}}
        <div x-show="activeTab === 'entries'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title">
                    <span class="caption-subject"><i class="fa fa-list" style="color:#3b82f6;margin-right:6px;"></i>Journal Entries Database Records</span>
                    <div style="display:flex;gap:6px;">
                        <input type="text" class="form-control-gf" style="width:200px;" placeholder="Search entries..." x-model="entriesSearch" @input.debounce.300ms="fetchJournalEntries()">
                        <button class="btn-action-round primary" @click="fetchJournalEntries()"><i class="fa fa-refresh"></i> Refresh</button>
                    </div>
                </div>
                <div class="portlet-body" style="padding:0;">
                    <div class="grid-container" style="margin:0;border:none;">
                        <table class="grid-table">
                            <thead>
                                <tr>
                                    <th style="width:110px;">Entry No.</th>
                                    <th style="width:85px;">Date</th>
                                    <th style="width:100px;">Office</th>
                                    <th>Description</th>
                                    <th style="width:80px;text-align:center;">Status</th>
                                    <th style="width:100px;">Created By</th>
                                    <th style="width:80px;text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="entry in journalEntries" :key="entry.id">
                                    <tr>
                                        <td x-text="entry.entry_no" style="font-weight:700;color:#2563eb;"></td>
                                        <td x-text="entry.entry_date"></td>
                                        <td x-text="entry.office ? entry.office.name : '-'"></td>
                                        <td x-text="entry.description || '-'"></td>
                                        <td style="text-align:center;">
                                            <span class="badge-status" :class="(entry.status || 'POSTED').toLowerCase()" x-text="entry.status || 'POSTED'"></span>
                                        </td>
                                        <td x-text="entry.creator ? entry.creator.name : 'System'"></td>
                                        <td style="text-align:center;">
                                            <button class="btn-action-round" style="height:20px;font-size:9px;" @click="viewEntryDetail(entry.id)"><i class="fa fa-eye"></i> View</button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="journalEntries.length === 0">
                                    <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">No journal entries found.</td></tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: NEW JOURNAL ENTRY --}}
        <div x-show="activeTab === 'create'" x-cloak>
            <div class="portlet light">
                <div class="portlet-title">
                    <span class="caption-subject"><i class="fa fa-plus-circle" style="color:#22c55e;margin-right:6px;"></i>Post New Journal Entry</span>
                    <span class="badge-status" :class="newEntryIsBalanced ? 'posted' : 'draft'" x-text="newEntryIsBalanced ? 'BALANCED' : 'UNBALANCED'"></span>
                </div>
                <div class="portlet-body">
                    <div class="report-grid">
                        <div>
                            <div class="form-group-row">
                                <div class="form-label-box">Entry No.</div>
                                <div class="form-input-box">
                                    <input type="text" class="form-control-gf" x-model="newEntry.entry_no" readonly style="background:#f1f5f9;font-weight:700;color:#2563eb;">
                                </div>
                            </div>
                            <div class="form-group-row">
                                <div class="form-label-box">Entry Date</div>
                                <div class="form-input-box">
                                    <input type="date" class="form-control-gf" x-model="newEntry.entry_date">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="form-group-row">
                                <div class="form-label-box">Office</div>
                                <div class="form-input-box">
                                    <select class="form-control-gf" x-model="newEntry.office_id">
                                        <option value="">Select Office</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-row">
                                <div class="form-label-box">Description</div>
                                <div class="form-input-box">
                                    <input type="text" class="form-control-gf" placeholder="Entry description..." x-model="newEntry.description">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Lines Table --}}
                    <div style="margin-top:14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <div style="font-weight:700;font-size:11px;color:#1e293b;">Journal Entry Lines</div>
                            <button type="button" class="btn-action-round primary" @click="addEntryLine()"><i class="fa fa-plus"></i> Add Line</button>
                        </div>
                        <div class="grid-container" style="margin:0;">
                            <table class="grid-table">
                                <thead>
                                    <tr>
                                        <th style="width:30px;">#</th>
                                        <th style="width:200px;">G/L Account *</th>
                                        <th style="width:160px;">Customer</th>
                                        <th>Line Description</th>
                                        <th style="width:110px;text-align:right;">Local Debit ($)</th>
                                        <th style="width:110px;text-align:right;">Local Credit ($)</th>
                                        <th style="width:35px;text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(line, index) in newEntry.lines" :key="index">
                                        <tr>
                                            <td x-text="index + 1" style="text-align:center;font-weight:600;color:#64748b;"></td>
                                            <td>
                                                <select class="form-control-gf" x-model="line.gl_account_id">
                                                    <option value="">-- Select GL Account --</option>
                                                    @foreach($glAccounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control-gf" x-model="line.trade_partner_id">
                                                    <option value="">-- None --</option>
                                                    @foreach($partners as $p)
                                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control-gf" placeholder="Line description..." x-model="line.description">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" class="form-control-gf num" x-model.number="line.local_debit" @input="line.local_credit = 0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" class="form-control-gf num" x-model.number="line.local_credit" @input="line.local_debit = 0">
                                            </td>
                                            <td style="text-align:center;">
                                                <button type="button" class="btn-action-round danger" style="padding:0 5px;height:18px;" @click="removeEntryLine(index)" :disabled="newEntry.lines.length <= 1">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr class="total-row">
                                        <td colspan="4" style="text-align:right;">TOTAL</td>
                                        <td class="num" x-text="formatMoney(newEntryTotalDebit)"></td>
                                        <td class="num" x-text="formatMoney(newEntryTotalCredit)"></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="button-row">
                        <button type="button" class="btn-action-round green" @click="submitJournalEntry()" :disabled="!newEntryIsBalanced || posting">
                            <i class="fa fa-check-circle"></i> <span x-text="posting ? 'Posting...' : 'Post Journal Entry'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ENTRY DETAIL MODAL --}}
        <div x-show="detailModalOpen" class="modal-overlay" x-cloak @click.self="detailModalOpen = false">
            <div class="modal-container" x-show="detailModalOpen">
                <div class="modal-header">
                    <span><i class="fa fa-file-text-o"></i> Journal Entry Details - <span x-text="selectedEntry?.entry_no"></span></span>
                    <button type="button" @click="detailModalOpen = false" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;">&times;</button>
                </div>
                <template x-if="selectedEntry">
                    <div class="modal-body">
                        <div class="report-grid" style="margin-bottom:12px;">
                            <div>
                                <div><strong>Entry Date:</strong> <span x-text="selectedEntry?.entry_date || '-'"></span></div>
                                <div><strong>Office:</strong> <span x-text="selectedEntry?.office ? selectedEntry.office.name : '-'"></span></div>
                            </div>
                            <div>
                                <div><strong>Status:</strong> <span class="badge-status posted" x-text="selectedEntry?.status || '-'"></span></div>
                                <div><strong>Description:</strong> <span x-text="selectedEntry?.description || '-'"></span></div>
                            </div>
                        </div>

                        <div class="grid-container" style="margin:0;">
                            <table class="grid-table">
                                <thead>
                                    <tr>
                                        <th>G/L Account</th>
                                        <th>Customer</th>
                                        <th>Description</th>
                                        <th style="text-align:right;">Debit</th>
                                        <th style="text-align:right;">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(line, idx) in (selectedEntry?.lines || [])" :key="line.id || idx">
                                        <tr>
                                            <td x-text="(line.gl_account ? line.gl_account.code + ' - ' + line.gl_account.name : '-')"></td>
                                            <td x-text="line.trade_partner ? line.trade_partner.name : '-'"></td>
                                            <td x-text="line.description || '-'"></td>
                                            <td class="num" x-text="line.local_debit > 0 ? formatMoney(line.local_debit) : ''"></td>
                                            <td class="num" x-text="line.local_credit > 0 ? formatMoney(line.local_credit) : ''"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function journalReportApp() {
        return {
            activeTab: 'report',
            loading: false,
            hasLoaded: false,
            posting: false,
            detailModalOpen: false,
            selectedEntry: null,
            results: [],
            journalEntries: [],
            entriesSearch: '',
            isBalanced: null,
            summary: {
                total_debit: 0,
                total_credit: 0,
                total_records: 0
            },
            filters: {
                period_from: '{{ date("Y-m-d", strtotime("first day of this month")) }}',
                period_to: '{{ date("Y-m-d") }}',
                office_id: '',
                gl_from: '',
                gl_to: '',
                currency_id: '',
                search: ''
            },
            newEntry: {
                entry_no: '{{ $nextEntryNo }}',
                entry_date: '{{ date("Y-m-d") }}',
                office_id: '',
                description: '',
                lines: [
                    { gl_account_id: '', trade_partner_id: '', description: '', local_debit: 0, local_credit: 0 },
                    { gl_account_id: '', trade_partner_id: '', description: '', local_debit: 0, local_credit: 0 }
                ]
            },

            initApp() {
                // Restore state from sessionStorage if available
                const savedState = sessionStorage.getItem('journal_report_state');
                if (savedState) {
                    try {
                        const parsed = JSON.parse(savedState);
                        if (parsed.filters) this.filters = { ...this.filters, ...parsed.filters };
                        if (parsed.activeTab) this.activeTab = parsed.activeTab;
                    } catch (e) {
                        console.error('Error parsing saved state', e);
                    }
                }

                // Attach pageshow handler to handle BF Cache reloads
                window.addEventListener('pageshow', (event) => {
                    if (event.persisted) {
                        this.initApp();
                    }
                });

                // Auto-fetch initial report data
                this.fetchReportData();
            },

            saveState() {
                sessionStorage.setItem('journal_report_state', JSON.stringify({
                    filters: this.filters,
                    activeTab: this.activeTab
                }));
            },

            switchTab(tab) {
                this.activeTab = tab;
                this.saveState();
                if (tab === 'entries' && this.journalEntries.length === 0) {
                    this.fetchJournalEntries();
                }
            },

            formatMoney(val) {
                if (val === undefined || val === null || isNaN(val)) return '0.00';
                return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            showToast(type, msg) {
                const icons = { success: 'check-circle', error: 'times-circle', info: 'info-circle' };
                const t = document.createElement('div');
                t.className = 'toast ' + type;
                t.innerHTML = '<i class="fa fa-' + (icons[type] || 'info-circle') + '"></i> ' + msg;
                document.getElementById('toast-container').appendChild(t);
                setTimeout(() => { t.remove(); }, 3500);
            },

            fetchReportData() {
                this.loading = true;
                this.saveState();

                const formData = new FormData();
                formData.append('period_from', this.filters.period_from);
                formData.append('period_to', this.filters.period_to);
                if (this.filters.office_id) formData.append('office_id', this.filters.office_id);
                if (this.filters.gl_from) formData.append('gl_from', this.filters.gl_from);
                if (this.filters.gl_to) formData.append('gl_to', this.filters.gl_to);
                if (this.filters.currency_id) formData.append('currency_id', this.filters.currency_id);
                if (this.filters.search) formData.append('search', this.filters.search);

                fetch('{{ route("accounting.report.journal-report.view") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    this.hasLoaded = true;
                    if (data.success) {
                        this.results = data.results || [];
                        this.summary = {
                            total_debit: data.total_debit || 0,
                            total_credit: data.total_credit || 0,
                            total_records: data.total_records || 0
                        };
                        this.isBalanced = data.is_balanced;
                    } else {
                        this.showToast('error', data.message || 'Failed to load report data');
                    }
                })
                .catch(err => {
                    this.loading = false;
                    console.error(err);
                    this.showToast('error', 'Error fetching Journal Report data');
                });
            },

            printReport() {
                const params = new URLSearchParams({
                    period_from: this.filters.period_from,
                    period_to: this.filters.period_to,
                    office_id: this.filters.office_id || '',
                    gl_from: this.filters.gl_from || '',
                    gl_to: this.filters.gl_to || '',
                    currency_id: this.filters.currency_id || '',
                    search: this.filters.search || ''
                });
                window.open('{{ route("accounting.report.journal-report.print") }}?' + params.toString(), '_blank');
            },

            exportExcel() {
                const params = new URLSearchParams({
                    period_from: this.filters.period_from,
                    period_to: this.filters.period_to,
                    office_id: this.filters.office_id || '',
                    gl_from: this.filters.gl_from || '',
                    gl_to: this.filters.gl_to || '',
                    currency_id: this.filters.currency_id || '',
                    search: this.filters.search || ''
                });
                window.location.href = '{{ route("accounting.report.journal-report.export-excel") }}?' + params.toString();
            },

            fetchJournalEntries() {
                const params = new URLSearchParams({
                    search: this.entriesSearch || ''
                });
                fetch('{{ route("accounting.journal.list") }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.journalEntries = data.data || [];
                })
                .catch(err => console.error('Error fetching journal list', err));
            },

            viewEntryDetail(id) {
                fetch('/accounting/journal/' + id, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.selectedEntry = data;
                    this.detailModalOpen = true;
                });
            },

            // TAB 3: CREATE JOURNAL ENTRY METHODS
            addEntryLine() {
                this.newEntry.lines.push({
                    gl_account_id: '',
                    trade_partner_id: '',
                    description: '',
                    local_debit: 0,
                    local_credit: 0
                });
            },

            removeEntryLine(index) {
                if (this.newEntry.lines.length > 1) {
                    this.newEntry.lines.splice(index, 1);
                }
            },

            get newEntryTotalDebit() {
                return this.newEntry.lines.reduce((sum, line) => sum + (parseFloat(line.local_debit) || 0), 0);
            },

            get newEntryTotalCredit() {
                return this.newEntry.lines.reduce((sum, line) => sum + (parseFloat(line.local_credit) || 0), 0);
            },

            get newEntryIsBalanced() {
                const diff = Math.abs(this.newEntryTotalDebit - this.newEntryTotalCredit);
                return diff < 0.01 && this.newEntryTotalDebit > 0;
            },

            submitJournalEntry() {
                if (!this.newEntryIsBalanced) {
                    this.showToast('error', 'Total debit must equal total credit and be greater than 0.');
                    return;
                }

                const validLines = this.newEntry.lines.filter(l => l.gl_account_id && (l.local_debit > 0 || l.local_credit > 0));
                if (validLines.length === 0) {
                    this.showToast('error', 'Please complete at least one line with a GL account and amount.');
                    return;
                }

                this.posting = true;

                fetch('{{ route("accounting.journal.entry.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        entry_date: this.newEntry.entry_date,
                        office_id: this.newEntry.office_id || null,
                        description: this.newEntry.description,
                        lines: validLines
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.posting = false;
                    if (data.success) {
                        this.showToast('success', 'Journal Entry ' + data.entry_no + ' posted successfully!');
                        // Reset form & fetch next entry no
                        this.resetNewEntryForm();
                        // Refresh report & entries list
                        this.fetchReportData();
                        if (this.journalEntries.length > 0) this.fetchJournalEntries();
                        // Switch to Report Tab
                        this.switchTab('report');
                    } else {
                        this.showToast('error', data.message || 'Failed to post Journal Entry');
                    }
                })
                .catch(err => {
                    this.posting = false;
                    console.error(err);
                    this.showToast('error', 'Error posting Journal Entry');
                });
            },

            resetNewEntryForm() {
                fetch('{{ route("accounting.next-entry-no") }}')
                    .then(r => r.json())
                    .then(data => {
                        this.newEntry.entry_no = data.entry_no;
                    });
                this.newEntry.description = '';
                this.newEntry.lines = [
                    { gl_account_id: '', trade_partner_id: '', description: '', local_debit: 0, local_credit: 0 },
                    { gl_account_id: '', trade_partner_id: '', description: '', local_debit: 0, local_credit: 0 }
                ];
            }
        };
    }
    </script>
    @endpush
</x-layout>
