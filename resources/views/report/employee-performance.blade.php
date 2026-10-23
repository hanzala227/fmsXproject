<x-layout>
    @push('styles')
    <x-form-styles />
    <style>
        .rpt-label { background: #f0f4f8; border: 1px solid #e2e8f0; border-right: none; padding: 4px 10px; font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; display: flex; align-items: center; height: 28px; border-radius: 2px 0 0 2px; white-space: nowrap; letter-spacing: 0.3px; }
        .rpt-input-wrap { border: 1px solid #d1d5db; border-radius: 0 2px 2px 0; padding: 3px 6px; display: flex; align-items: center; min-height: 28px; background: #fff; gap: 8px; }
        .rpt-input-wrap .form-control-gf { border: none; box-shadow: none; padding: 0; height: 20px; background: transparent; }
        .rpt-input-wrap .form-control-gf:focus { box-shadow: none; border: none; outline: none; }
        .rpt-input-wrap select.form-control-gf { padding-right: 14px; }
        .rpt-row { display: flex; gap: 0; margin-bottom: 6px; }
        .rpt-row .rpt-label { min-width: 120px; max-width: 140px; flex-shrink: 0; }
        .rpt-row .rpt-input-wrap { flex: 1; min-width: 0; }
        .rpt-radio-group { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .rpt-radio-group label, .rpt-chk-group label { font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer; color: #334155; white-space: nowrap; user-select: none; }
        .rpt-radio-group input[type="radio"], .rpt-chk-group input[type="checkbox"] { width: 13px !important; height: 13px !important; accent-color: #3b82f6; cursor: pointer; }
        .rpt-chk-group { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .rpt-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; }
        .rpt-filter-section { padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .rpt-view-btn { background: #3b82f6; color: #fff; border: none; padding: 6px 24px; font-size: 11px; font-weight: 700; border-radius: 3px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 8px; transition: all 0.2s; }
        .rpt-view-btn:hover { background: #2563eb; }
        .rpt-view-btn:active { transform: translateY(1px); }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
        .kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.02); transition: all 0.2s; }
        .kpi-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.06); transform: translateY(-1px); }
        .kpi-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
        .kpi-value { font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 2px; }
        .kpi-icon { font-size: 20px; opacity: 0.2; }
        .charts-grid { display: grid; grid-template-columns: 1fr; gap: 14px; margin-bottom: 14px; }
        .chart-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 14px; min-width: 0; }
        .chart-title { font-size: 11px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .chart-empty { display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 12px; }
        .table-custom { width: 100%; border-collapse: collapse; font-size: 11px; }
        .table-custom thead th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px; border-bottom: 2px solid #e2e8f0; white-space: nowrap; cursor: pointer; user-select: none; }
        .table-custom thead th:hover { background: #e2e8f0; }
        .table-custom thead th.text-right { text-align: right; }
        .table-custom thead th.text-center { text-align: center; }
        .table-custom tbody td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .table-custom tbody td.text-right { text-align: right; }
        .table-custom tbody td.text-center { text-align: center; }
        .table-custom tbody tr:hover { background: #f8fafc; }
        .total-row td { background: #f1f5f9; font-weight: 700; color: #0f172a; border-top: 2px solid #e2e8f0; border-bottom: 2px solid #e2e8f0; }
        .sort-icon { margin-left: 3px; font-size: 9px; opacity: 0.4; }
        .sort-active { color: #3b82f6; opacity: 1 !important; }
        .eff-bar-bg { width: 60px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; display: inline-block; vertical-align: middle; margin-right: 6px; }
        .eff-bar { height: 100%; border-radius: 3px; transition: width 0.4s ease; }
        .search-box { border: 1px solid #d1d5db; border-radius: 3px; padding: 4px 8px; font-size: 11px; height: 28px; width: 220px; background: #fff; transition: all 0.2s; }
        .search-box:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.15); }
        .loading-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.75); display: flex; align-items: center; justify-content: center; z-index: 10; border-radius: 4px; backdrop-filter: blur(1px); }
        .spinner { width: 28px; height: 28px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; font-size: 10px; font-weight: 800; }
        .rank-1 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
        .rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; }
        .rank-3 { background: linear-gradient(135deg, #d97706, #b45309); color: #fff; }
        .rank-other { background: #f1f5f9; color: #64748b; }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .rpt-grid-2 { grid-template-columns: 1fr; gap: 8px; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .rpt-row { flex-direction: column; align-items: stretch; margin-bottom: 8px; }
            .rpt-row .rpt-label { min-width: 100%; max-width: 100%; border-radius: 2px 2px 0 0; border-right: 1px solid #e2e8f0; height: 24px; }
            .rpt-row .rpt-input-wrap { border-radius: 0 0 2px 2px; }
            .search-box { width: 100%; margin-top: 6px; }
            .table-custom { font-size: 10px; }
            .table-custom thead th, .table-custom tbody td { padding: 6px 8px; }
        }
    </style>
    @endpush

    <div style="background: #eef1f5; min-height: 100vh; padding: 12px;">
        <div x-data="employeePerformanceReport()" x-init="init()" style="position: relative;">
            <div x-show="loading" class="loading-overlay"><div class="spinner"></div></div>

            <div style="font-size: 11px; color: #64748b; margin-bottom: 10px;">
                <a href="/" style="color: #64748b; text-decoration: none;" target="_blank"><i class="fa fa-home"></i> Home</a>
                <i class="fa fa-angle-right" style="margin: 0 4px; opacity: 0.5;"></i>
                <a href="/report" style="color: #64748b; text-decoration: none;">Reports</a>
                <i class="fa fa-angle-right" style="margin: 0 4px; opacity: 0.5;"></i>
                <span style="color: #0f172a; font-weight: 700;">Employee Performance</span>
            </div>

            <div class="portlet light">
                <div class="portlet-title" style="flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-users" style="color: #3b82f6; font-size: 14px;"></i>
                        <span class="caption-subject" style="font-weight: 700;">Employee Performance Intelligence</span>
                    </div>
                    <div style="display: flex; gap: 6px; margin-left: auto;">
                        <button class="btn-freightx" @click="printReport()"><i class="fa fa-print"></i> PRINT</button>
                        <button class="btn-freightx" style="background: #10b981;" @click="exportExcel()"><i class="fa fa-file-excel-o"></i> EXPORT</button>
                    </div>
                </div>

                <div class="rpt-filter-section">
                    <div class="rpt-grid-2">
                        <div>
                            <div class="rpt-row">
                                <div class="rpt-label">Period</div>
                                <div class="rpt-input-wrap" style="flex-direction:column; align-items:stretch; gap:4px; padding:4px 6px;">
                                    <div class="rpt-radio-group">
                                        <label><input type="radio" name="period_type" value="post_date" x-model="filters.period_type"> Post Date</label>
                                        <label><input type="radio" name="period_type" value="etd" x-model="filters.period_type"> ETD</label>
                                        <label><input type="radio" name="period_type" value="eta" x-model="filters.period_type"> ETA</label>
                                        <label><input type="radio" name="period_type" value="create_date" x-model="filters.period_type"> Create Date</label>
                                    </div>
                                    <div style="display:flex; gap:4px; align-items:center;">
                                        <input type="date" x-model="filters.date_from" class="form-control-gf" style="flex:1; border:1px solid #d1d5db; border-radius:2px; height:22px; padding:0 4px; font-size:10px;">
                                        <span style="font-size:10px; color:#64748b;">~</span>
                                        <input type="date" x-model="filters.date_to" class="form-control-gf" style="flex:1; border:1px solid #d1d5db; border-radius:2px; height:22px; padding:0 4px; font-size:10px;">
                                    </div>
                                </div>
                            </div>
                            <div class="rpt-row">
                                <div class="rpt-label">Shipping Type</div>
                                <div class="rpt-input-wrap">
                                    <div class="rpt-chk-group">
                                        @foreach($shippingTypes as $st)
                                        <label><input type="checkbox" value="{{ $st }}" x-model="filters.shipping_types"> {{ $st }}</label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="rpt-row">
                                <div class="rpt-label">Office</div>
                                <div class="rpt-input-wrap">
                                    <select x-model="filters.office_id" class="form-control-gf" style="width:100%;">
                                        <option value="">All Offices</option>
                                        @foreach($offices as $o)
                                        <option value="{{ $o->id }}">{{ $o->code }} - {{ $o->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="rpt-row">
                                <div class="rpt-label">Sales Person</div>
                                <div class="rpt-input-wrap">
                                    <select x-model="filters.sales_person_id" class="form-control-gf" style="width:100%;">
                                        <option value="">All Sales Persons</option>
                                        @foreach($salesPersons as $sp)
                                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:center; margin-top:6px;">
                        <button class="rpt-view-btn" @click="fetchData()"><i class="fa fa-search" style="margin-right:4px;"></i> Refresh Results</button>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Net Profit</div>
                        <div class="kpi-value" :style="'color:' + (data.summary.gross_profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + formatNum(data.summary.gross_profit)"></div>
                    </div>
                    <i class="fa fa-money kpi-icon" style="color: #10b981;"></i>
                </div>
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Total Shipments</div>
                        <div class="kpi-value" x-text="formatInt(data.summary.shipment_count)"></div>
                    </div>
                    <i class="fa fa-ship kpi-icon" style="color: #3b82f6;"></i>
                </div>
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Avg Margin</div>
                        <div class="kpi-value" x-text="data.summary.margin + '%'"></div>
                    </div>
                    <i class="fa fa-pie-chart kpi-icon" style="color: #8b5cf6;"></i>
                </div>
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Top Performer</div>
                        <div class="kpi-value" style="font-size: 14px;" x-text="data.summary.top_performer"></div>
                    </div>
                    <i class="fa fa-trophy kpi-icon" style="color: #f59e0b;"></i>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid" style="display: flex; width: 100%;">
                <div class="chart-card" style="width: 50%;">
                    <div class="chart-title">
                        <span><i class="fa fa-bar-chart" style="margin-right: 4px;"></i> Profit by Employee (Top 10)</span>
                    </div>
                    <div id="profitChart" style="min-height: 380px;"></div>
                </div>
                <div class="chart-card" style="width: 50%;">
                    <div class="chart-title">
                        <span><i class="fa fa-bar-chart" style="margin-right: 4px;"></i> Revenue &amp; Profit by Shipping Type</span>
                    </div>
                    <div id="shippingTypeChart" style="min-height: 420px;"></div>
                </div>
            </div>

            <!-- Table Section -->
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 14px;">
                <div style="padding: 8px 14px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">
                            <i class="fa fa-users" style="margin-right: 4px;"></i> Employee Rankings
                        </span>
                        <span style="font-size: 10px; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 2px; font-weight: 600;" x-text="data.rows.length + ' employees'"></span>
                    </div>
                    <input type="text" class="search-box" placeholder="Search employee name..." x-model="filters.search">
                </div>

                <div style="overflow-x: auto;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">#</th>
                                <th @click="sort('employee')">
                                    Employee
                                    <i class="fa" :class="getSortIcon('employee')"></i>
                                </th>
                                <th @click="sort('quotes')" class="text-center">
                                    Quotes
                                    <i class="fa" :class="getSortIcon('quotes')"></i>
                                </th>
                                <th @click="sort('revenue')" class="text-right">
                                    Revenue (A/R)
                                    <i class="fa" :class="getSortIcon('revenue')"></i>
                                </th>
                                <th @click="sort('cost')" class="text-right">
                                    Cost (A/P)
                                    <i class="fa" :class="getSortIcon('cost')"></i>
                                </th>
                                <th @click="sort('profit')" class="text-right">
                                    Net Profit
                                    <i class="fa" :class="getSortIcon('profit')"></i>
                                </th>
                                <th @click="sort('margin')" class="text-center">
                                    Margin
                                    <i class="fa" :class="getSortIcon('margin')"></i>
                                </th>
                                <th @click="sort('volume')" class="text-right">
                                    Volume (CBM)
                                    <i class="fa" :class="getSortIcon('volume')"></i>
                                </th>
                                <th @click="sort('avg_deal')" class="text-right">
                                    Avg Deal
                                    <i class="fa" :class="getSortIcon('avg_deal')"></i>
                                </th>
                                <th @click="sort('win_rate')" class="text-center">
                                    Win Rate
                                    <i class="fa" :class="getSortIcon('win_rate')"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="data.rows.length === 0">
                                <tr><td colspan="10" style="text-align:center; padding:40px; color:#94a3b8; font-size:12px;">No employee data found for selected filters.</td></tr>
                            </template>
                            <template x-for="(row, idx) in data.rows" :key="row.employee_id">
                                <tr>
                                    <td class="text-center">
                                        <span class="rank-badge" :class="idx < 3 ? 'rank-' + (idx + 1) : 'rank-other'" x-text="idx + 1"></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;" x-text="row.employee_name"></div>
                                        <div style="font-size: 10px; color: #94a3b8;" x-text="row.email"></div>
                                    </td>
                                    <td class="text-center" style="font-weight: 600;" x-text="row.total_quotes"></td>
                                    <td class="text-right" x-text="'$' + formatNum(row.revenue)"></td>
                                    <td class="text-right" x-text="'$' + formatNum(row.cost)"></td>
                                    <td class="text-right" style="font-weight: 700;" :style="'color:' + (row.profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + formatNum(row.profit)"></td>
                                    <td class="text-center">
                                        <div>
                                            <div class="eff-bar-bg"><div class="eff-bar" :style="'width:' + Math.min(Math.max(row.margin, 0), 100) + '%; background:' + getMarginColor(row.margin)"></div></div>
                                            <span style="font-size: 10px; font-weight: 700;" :style="'color:' + getMarginColor(row.margin)" x-text="row.margin + '%'"></span>
                                        </div>
                                    </td>
                                    <td class="text-right" x-text="formatNum(row.volume_cbm)"></td>
                                    <td class="text-right" x-text="'$' + formatNum(row.avg_deal_size)"></td>
                                    <td class="text-center">
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 2px; font-size: 10px; font-weight: 700;" :style="'background:' + getWinRateBg(row.win_rate) + '; color:' + getWinRateColor(row.win_rate)" x-text="row.win_rate + '%'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot x-show="data.rows.length > 0">
                            <tr class="total-row">
                                <td></td>
                                <td style="text-transform: uppercase; letter-spacing: 0.5px;">TOTAL / AVERAGE</td>
                                <td class="text-center" x-text="data.summary.shipment_count"></td>
                                <td class="text-right" x-text="'$' + formatNum(data.summary.total_revenue)"></td>
                                <td class="text-right" x-text="'$' + formatNum(data.summary.total_cost)"></td>
                                <td class="text-right" :style="'color:' + (data.summary.gross_profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + formatNum(data.summary.gross_profit)"></td>
                                <td class="text-center" style="font-weight: 700;" x-text="data.summary.margin + '%'"></td>
                                <td class="text-right" x-text="formatNum(data.summary.total_volume)"></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
function employeePerformanceReport() {
        return {
            loading: false,
            profitChart: null,
            shippingTypeChart: null,
            _reqId: 0,
            _timer: null,
            filters: {
                date_from: '2025-01-01',
                date_to: '{{ now()->endOfMonth()->format("Y-m-d") }}',
                period_type: 'post_date',
                shipping_types: [],
                office_id: '',
                sales_person_id: '',
                search: '',
                sort_by: 'profit',
                sort_dir: 'desc',
            },
            data: {
                summary: { total_revenue: 0, total_cost: 0, gross_profit: 0, margin: 0, total_volume: 0, shipment_count: 0, employee_count: 0, top_performer: 'N/A' },
                rows: [],
                by_shipping_type: [],
            },

            init() {
                // Har filter change par data reload (x-model update hone ke BAAD chalta hai,
                // isliye pichli selection wali galti nahi hogi)
                ['period_type', 'date_from', 'date_to', 'shipping_types', 'office_id', 'sales_person_id', 'search']
                    .forEach(key => {
                        this.$watch('filters.' + key, () => this.fetchDataDebounced(key === 'search' ? 300 : 120));
                    });

                this.fetchData();

                window.addEventListener('resize', () => {
                    if (this.profitChart) this.profitChart.resize();
                    if (this.shippingTypeChart) this.shippingTypeChart.resize();
                });
            },

            fetchDataDebounced(delay = 120) {
                clearTimeout(this._timer);
                this._timer = setTimeout(() => this.fetchData(), delay);
            },

            async fetchData() {
                const reqId = ++this._reqId;
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    params.append('date_from', this.filters.date_from);
                    params.append('date_to', this.filters.date_to);
                    params.append('period_type', this.filters.period_type);
                    params.append('search', this.filters.search);
                    params.append('sort_by', this.filters.sort_by);
                    params.append('sort_dir', this.filters.sort_dir);
                    if (this.filters.office_id) params.append('office_id', this.filters.office_id);
                    if (this.filters.sales_person_id) params.append('sales_person_id', this.filters.sales_person_id);
                    [...this.filters.shipping_types].forEach(t => params.append('shipping_types[]', t));

                    const resp = await fetch('{{ route("report.employee-performance.data") }}?' + params.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();

                    // Purana response ignore karo agar naya request chal chuka hai
                    if (reqId !== this._reqId) return;

                    this.data = json;
                    this.$nextTick(() => this.renderCharts());
                } catch (e) {
                    console.error('Fetch error:', e);
                } finally {
                    if (reqId === this._reqId) this.loading = false;
                }
            },

            sort(field) {
                if (this.filters.sort_by === field) {
                    this.filters.sort_dir = this.filters.sort_dir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.filters.sort_by = field;
                    this.filters.sort_dir = 'desc';
                }
                this.fetchData();
            },

            getSortIcon(field) {
                if (this.filters.sort_by !== field) return 'fa-sort sort-icon';
                return this.filters.sort_dir === 'asc' ? 'fa-sort-asc sort-icon sort-active' : 'fa-sort-desc sort-icon sort-active';
            },

            renderCharts() {
                this.renderProfitChart();
                this.renderShippingTypeChart();
            },
renderProfitChart() {
    const el = document.getElementById('profitChart');
    if (!el) return;

    if (this.profitChart) {
        this.profitChart.destroy();
        this.profitChart = null;
    }

    const rows = (this.data.rows || []).slice(0, 10);

    if (rows.length === 0) {
        el.innerHTML = '<div class="chart-empty" style="height:380px;">No data for selected filters</div>';
        return;
    }

    el.innerHTML = '';

    const labels = rows.map(r => r.employee_name);
    const profits = rows.map(r => parseFloat(r.profit || 0));

    // Profit ke hisaab se colors
    const minProfit = Math.min(...profits);
    const maxProfit = Math.max(...profits);

    const profitRange = maxProfit - minProfit;

    const barColors = profits.map(profit => {

        // Agar sab ki value same ho
        if (profitRange === 0) {
            return '#f59e0b';
        }

        // 33% se neeche = Red
        if (profit <= minProfit + (profitRange * 0.33)) {
            return '#ef4444';
        }

        // 33% - 66% = Orange
        if (profit <= minProfit + (profitRange * 0.66)) {
            return '#f59e0b';
        }

        // 66% se upar = Green
        return '#10b981';
    });

    this.profitChart = new ApexCharts(el, {

        series: [
            {
                name: 'Net Profit',
                data: profits
            }
        ],

        chart: {
            height: 380,
            type: 'bar',
            toolbar: {
                show: false
            },
            animations: {
                enabled: true
            }
        },

        // Har bar ka apna color
        colors: barColors,

        plotOptions: {
            bar: {
                columnWidth: '50%',
                borderRadius: 5,
                distributed: true
            }
        },

        xaxis: {
            categories: labels,
            labels: {
                rotate: -30,
                trim: true,
                style: {
                    colors: '#64748b',
                    fontSize: '10px',
                    fontWeight: 600
                }
            }
        },

        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '10px',
                    fontWeight: 600
                },
                formatter: v => '$' + this.formatNum(v)
            }
        },

        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4
        },

        tooltip: {
            y: {
                formatter: v => '$' + this.formatNum(v)
            }
        },

        dataLabels: {
            enabled: true,
            formatter: v =>
                '$' +
                (
                    Math.abs(v) >= 1000
                        ? (v / 1000).toFixed(1) + 'K'
                        : this.formatNum(v)
                ),
            style: {
                fontSize: '10px'
            }
        },

    });

    this.profitChart.render();
},

           renderShippingTypeChart() {
    const el = document.getElementById('shippingTypeChart');
    if (!el) return;

    if (this.shippingTypeChart) {
        this.shippingTypeChart.destroy();
        this.shippingTypeChart = null;
    }

    const items = this.data.by_shipping_type || [];

    if (items.length === 0) {
        el.innerHTML = '<div class="chart-empty" style="height:420px;">No data for selected filters</div>';
        return;
    }

    el.innerHTML = '';

    const labels = items.map(t => t.label || 'Unknown');
    const revenues = items.map(t => parseFloat(t.revenue || 0));
    const profits = items.map(t => parseFloat(t.profit || 0));

    this.shippingTypeChart = new ApexCharts(el, {
        series: [
            {
                name: 'Total Revenue',
                data: revenues
            },
            {
                name: 'Net Profit',
                data: profits
            }
        ],

        chart: {
            height: 420,
            type: 'line',
            toolbar: {
                show: true,
                Tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                }
            },
            animations: {
                enabled: true
            }
        },

        stroke: {
            curve: 'smooth',
            width: 3
        },

        markers: {
            size: 5,
            strokeWidth: 2,
            hover: {
                size: 7
            }
        },

        colors: ['#3b82f6', '#10b981'],

        xaxis: {
            categories: labels,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '11px',
                    fontWeight: 600
                }
            }
        },

        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '10px',
                    fontWeight: 600
                },
                formatter: v => '$' + this.formatNum(v)
            }
        },

        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4
        },

        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'center',
            fontSize: '12px',
            fontWeight: 500,
            labels: {
                colors: '#334155'
            }
        },

        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: v => '$' + this.formatNum(v)
            }
        }
    });

    this.shippingTypeChart.render();
},
            getMarginColor(m) {
                if (m >= 30) return '#10b981';
                if (m >= 15) return '#3b82f6';
                if (m >= 5) return '#f59e0b';
                return '#ef4444';
            },

            getWinRateBg(r) {
                if (r >= 50) return '#ecfdf5';
                if (r >= 25) return '#eff6ff';
                return '#fef2f2';
            },

            getWinRateColor(r) {
                if (r >= 50) return '#10b981';
                if (r >= 25) return '#3b82f6';
                return '#ef4444';
            },

            formatNum(n) {
                if (n === null || n === undefined) return '0.00';
                return parseFloat(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatInt(n) {
                if (n === null || n === undefined) return '0';
                return parseInt(n).toLocaleString('en-US');
            },

            printReport() { window.print(); },

            exportExcel() {
                let csv = 'Rank,Employee,Quotes,Revenue,Cost,Net Profit,Margin,Volume (CBM),Avg Deal Size,Win Rate\n';
                this.data.rows.forEach((r, i) => {
                    csv += `${i + 1},"${r.employee_name}",${r.total_quotes},${r.revenue},${r.cost},${r.profit},${r.margin}%,${r.volume_cbm},${r.avg_deal_size},${r.win_rate}%\n`;
                });
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url; a.download = 'employee-performance-report.csv'; a.click();
                URL.revokeObjectURL(url);
            },
        };
    }
    </script>
</x-layout>