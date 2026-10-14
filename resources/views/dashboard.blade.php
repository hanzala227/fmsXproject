<x-layout>
    @push('styles')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        /* ========== DASHBOARD CUSTOM STYLES ========== */
        .page-content {
            padding: 20px;
            background: #eef1f5;
            min-height: 100vh;
        }

        .portlet.light {
            background-color: #fff;
            border: 1px solid #e7ecf1;
            border-radius: 6px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .portlet-title {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f3f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            background-color: #fafbfc;
            border-radius: 6px 6px 0 0;
            color: #2d3748;
            font-weight: 800;
            font-size: 12px;
        }

        .portlet-title .caption {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .portlet-title .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dashboard-stat2 {
            background: #fff;
            border: 1px solid #e7ecf1;
            border-top: 3px solid #4b77be;
            padding: 20px 15px;
            border-radius: 6px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .dashboard-stat2:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.15);
        }

        /* Responsive KPI Row */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        @media (max-width: 1200px) {
            .kpi-row { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .kpi-row { grid-template-columns: 1fr; }
        }

        /* Charts Row - Improved Layout */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        @media (max-width: 1200px) {
            .charts-row { 
                grid-template-columns: 1fr; 
            }
        }

        .chart-container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Table Styles */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            background: #fff;
            min-width: 600px;
        }

        .table-custom thead th {
            text-align: left;
            padding: 10px 12px;
            background: #fafbfc;
            color: #4b77be;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 2px solid #eef1f5;
            letter-spacing: 0.3px;
            font-size: 10px;
        }

        .table-custom tbody tr {
            transition: background-color 0.2s ease;
        }

        .table-custom tbody tr:hover {
            background-color: #f9fbfd !important;
        }

        .table-custom tbody td {
            padding: 12px;
            border-bottom: 1px solid #f1f3f6;
            vertical-align: middle;
            color: #4a5568;
            font-size: 11px;
        }

        .table-custom tbody td a {
            color: #4b77be;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .table-custom tbody td a:hover {
            color: #3a62a4;
            text-decoration: underline;
        }

        /* Status Badges */
        .badge-status {
            padding: 3px 10px;
            border-radius: 12px;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            display: inline-block;
            white-space: nowrap;
        }

        .bg-pre-alert { background: #00827f; }
        .bg-arrival { background: #4b77be; }
        .bg-invoice { background: #d05454; }

        .section-header {
            font-size: 11px;
            font-weight: 900;
            color: #1a202c;
            text-transform: uppercase;
            margin: 25px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.8px;
        }

        .section-header i {
            color: #4b77be;
            background: rgba(75, 119, 190, 0.1);
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-freightx {
            background: #4b77be;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(75, 119, 190, 0.1);
        }

        .btn-freightx:hover {
            background: #3a62a4;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(75, 119, 190, 0.2);
        }

        .btn-outline {
            background: #fff;
            color: #4b77be;
            border: 1px solid #4b77be;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: #4b77be;
            color: #fff;
        }

        /* Chart stats row */
        .chart-stats-row { 
            display: flex; 
            justify-content: center; 
            gap: 40px; 
            padding: 15px; 
            border-bottom: 1px solid #f1f3f6; 
            margin-bottom: 10px; 
            background: #fcfdfe; 
            flex-wrap: wrap;
        }
        
        .chart-stat-item { 
            text-align: center; 
            min-width: 100px;
        }
        
        .chart-stat-item h4 { 
            margin: 0; 
            font-size: 20px; 
            font-weight: 700; 
            color: #2d3748; 
        }
        
        .chart-stat-item h4.text-primary { 
            color: #0ab39c; 
        }
        
        .chart-stat-item span { 
            font-size: 11px; 
            color: #718096; 
            text-transform: uppercase; 
            font-weight: 600; 
            display: block; 
            margin-top: 4px; 
        }

        /* Badge styling */
        .badge-subtle-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; display: inline-block; }
        .badge-subtle-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; display: inline-block; }
        .badge-subtle-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; display: inline-block; }
        .badge-subtle-info { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; display: inline-block; }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f4f6;
            border-top-color: #4b77be;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        /* Refresh Button */
        .btn-refresh {
            background: transparent;
            border: none;
            color: #718096;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .btn-refresh:hover {
            background: rgba(75, 119, 190, 0.1);
            color: #4b77be;
        }

        .btn-refresh.loading {
            pointer-events: none;
        }

        /* Avatar */
        .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Select Dropdown */
        .select-period {
            font-weight: 700;
            color: #2d3748;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .select-period:hover {
            border-color: #4b77be;
        }

        .select-period:focus {
            outline: none;
            border-color: #4b77be;
            box-shadow: 0 0 0 2px rgba(75, 119, 190, 0.1);
        }
    </style>
    @endpush

    <div class="page-content" x-data="dashboardApp()" x-init="init()">
        <!-- ═══ 1. KPI ROW ═══ -->
        <div class="kpi-row">
            @php
                $kpiConfig = [
                    ['label' => 'TOTAL PROFIT', 'val' => '$ ' . number_format($kpis['totalProfit'], 0), 'color' => '#4b77be', 'change' => $kpis['profitChangePercent'], 'key' => 'profit'],
                    ['label' => 'TOTAL VOLUME', 'val' => number_format($kpis['totalVolume']), 'color' => '#00827f', 'change' => $kpis['volumeChangePercent'], 'key' => 'volume'],
                    ['label' => 'NO. OF ACTIVE CUSTOMERS', 'val' => number_format($kpis['activeCustomers']), 'color' => '#d05454', 'change' => null, 'key' => 'customers'],
                    ['label' => 'NO. OF LOST CUSTOMERS', 'val' => number_format($kpis['lostCustomers']), 'color' => '#f2bc00', 'change' => null, 'key' => 'lost'],
                ];
            @endphp
            @foreach($kpiConfig as $kpi)
            <div class="dashboard-stat2" style="border-top-color: {{ $kpi['color'] }};" @click="handleKpiClick('{{ $kpi['key'] }}')">
                <div style="text-align: center;">
                    <small style="color: #8e9eae; font-weight: 700; font-size: 10px;">{{ $kpi['label'] }}</small>
                    <h3 style="color: {{ $kpi['color'] }}; font-size: 28px; font-weight: 700; margin: 8px 0;">{{ $kpi['val'] }}</h3>
                    <div style="font-size: 11px; color: #8e9eae;">
                        @if($kpi['change'] !== null)
                            <span style="color: {{ $kpi['change'] >= 0 ? '#0ab39c' : '#ef4444' }}; font-weight: 700;">
                                {{ $kpi['change'] >= 0 ? '↑' : '↓' }} {{ abs($kpi['change']) }}%
                            </span>
                            <br> 
                            <small style="font-size: 10px;">vs. Previous Period</small>
                        @else
                            <span style="color: #cbd5e1;">--</span>
                            <br>
                            <small style="font-size: 10px;">Last 6 Months</small>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- ═══ 2. TO DO LIST ═══ -->
        <div class="section-header">
            <i class="fa fa-list"></i> TO DO LIST
            <div style="margin-left: auto; display: flex; gap: 8px;">
                <button class="btn-refresh" @click="refreshTodos()" :class="{ 'loading': todosLoading }">
                    <i class="fa fa-refresh" :class="{ 'fa-spin': todosLoading }"></i>
                </button>
            </div>
        </div>
        <div class="portlet light" style="padding:0; border-top: 3px solid #4b77be;">
            <div class="table-responsive" style="min-height: 200px; position: relative;">
                <div x-show="todosLoading" style="position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 10;">
                    <div class="loading-spinner" style="width: 32px; height: 32px; border-width: 3px;"></div>
                </div>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width:120px;">TASK</th>
                            <th>BOOKING NO.</th>
                            <th>ETA</th>
                            <th>MB/L</th>
                            <th>HB/L</th>
                            <th>POD</th>
                            <th>CONSIGNEE</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todos as $todo)
@php
    $typeSlug = strtolower(str_replace('Import', '-import', str_replace('Export', '-export', $todo['shipment_type'])));
    $editUrl  = '/' . $typeSlug . '/' . $todo['id'] . '/edit';   // partial ke mutabiq badalna ho to yahan
@endphp
<tr style="cursor: pointer;" @click="handleTodoClick('{{ $editUrl }}')">
    <td><span class="badge-status {{ $todo['badgeClass'] }}">{{ $todo['task'] }}</span></td>
    <td><a href="{{ $editUrl }}" @click.stop style="color:#4b77be; font-weight: 600;">{{ $todo['file_no'] }}</a></td>
    <td style="font-weight: 500;">{{ $todo['eta'] }}</td>
    <td>{{ $todo['mbl'] }}</td>
    <td>{{ $todo['hbl'] }}</td>
    <td>{{ $todo['pod'] }}</td>
    <td>{{ $todo['consignee'] }}</td>
    <td style="color:#8e9eae; font-size:10px;">{{ $todo['status'] }}</td>
</tr>

    @empty
        <tr>
            <td colspan="8" class="empty-state">
                <i class="fa fa-inbox"></i>
                No tasks found.
            </td>
        </tr>
    @endforelse
</tbody>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- ═══ 3. INTERACTIVE CHARTS ═══ -->
        <div class="section-header">
            <i class="fa fa-line-chart"></i> ANALYTICS DASHBOARD
        </div>
        
        <div class="charts-row">
            <!-- Balance Overview -->
            <div class="portlet light chart-container relative overflow-visible" style="border-top: 3px solid #3b82f6;">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-line-chart"></i>
                        <span>BALANCE OVERVIEW</span>
                    </div>
                    <div class="actions">
                        <div class="relative" x-data="{ open: false, selectedPeriod: 6, selectedLabel: 'Last 6 Months' }">
                            <button @click="open = !open" type="button" class="flex items-center space-x-1.5 px-3 py-1 bg-white border border-gray-300 rounded-md text-[11px] font-bold text-gray-700 hover:border-blue-500 shadow-sm transition-all">
                                <i class="fa fa-calendar text-blue-500 text-[10px]"></i>
                                <span x-text="selectedLabel">Last 6 Months</span>
                                <i class="fa fa-angle-down text-gray-400 text-[11px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-md shadow-xl py-1 z-[999]" x-cloak>
                                <template x-for="opt in [
                                    {val: 3, label: 'Last 3 Months'},
                                    {val: 6, label: 'Last 6 Months'},
                                    {val: 12, label: 'Last 12 Months'},
                                    {val: 24, label: 'Last 24 Months'}
                                ]">
                                    <button @click="selectedPeriod = opt.val; selectedLabel = opt.label; open = false; reloadBalanceChart(opt.val)"
                                            type="button"
                                            class="w-full text-left px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center justify-between transition-colors">
                                        <span x-text="opt.label"></span>
                                        <i x-show="selectedPeriod === opt.val" class="fa fa-check text-blue-600 text-[10px]"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chart-stats-row" id="balance-stats-row">
                    <div class="chart-stat-item">
                        <h4 class="text-primary">${{ number_format($balanceChart['totalRevenue'] / 1000, 1) }}k</h4>
                        <span>Revenue</span>
                    </div>
                    <div class="chart-stat-item" style="border-left: 1px solid #edf2f7; padding-left: 30px;">
                        <h4>${{ number_format($balanceChart['totalExpenses'] / 1000, 1) }}k</h4>
                        <span>Expenses</span>
                    </div>
                    <div class="chart-stat-item" style="border-left: 1px solid #edf2f7; padding-left: 30px;">
                        <h4 style="color:#f59e0b;">{{ $balanceChart['profitRatio'] }}%</h4>
                        <span>Profit Ratio</span>
                    </div>
                </div>
                <div style="padding: 15px; flex: 1;">
                    <div id="balance-overview-chart" style="min-height: 300px; height: 300px;"></div>
                </div>
            </div>

            <!-- Sales Forecast -->
            <div class="portlet light chart-container relative overflow-visible" style="border-top: 3px solid #8b5cf6;">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-bar-chart"></i>
                        <span>SALES FORECAST</span>
                    </div>
                    <div class="actions">
                        <div class="relative" x-data="{ open: false, selectedPeriod: 12, selectedLabel: 'Last 12 Months' }">
                            <button @click="open = !open" type="button" class="flex items-center space-x-1.5 px-3 py-1 bg-white border border-gray-300 rounded-md text-[11px] font-bold text-gray-700 hover:border-purple-500 shadow-sm transition-all">
                                <i class="fa fa-calendar text-purple-500 text-[10px]"></i>
                                <span x-text="selectedLabel">Last 12 Months</span>
                                <i class="fa fa-angle-down text-gray-400 text-[11px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-md shadow-xl py-1 z-[999]" x-cloak>
                                <template x-for="opt in [
                                    {val: 3, label: 'Last 3 Months'},
                                    {val: 6, label: 'Last 6 Months'},
                                    {val: 12, label: 'Last 12 Months'},
                                    {val: 24, label: 'Last 24 Months'}
                                ]">
                                    <button @click="selectedPeriod = opt.val; selectedLabel = opt.label; open = false; reloadForecastChart(opt.val)"
                                            type="button"
                                            class="w-full text-left px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center justify-between transition-colors">
                                        <span x-text="opt.label"></span>
                                        <i x-show="selectedPeriod === opt.val" class="fa fa-check text-purple-600 text-[10px]"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="padding: 15px 15px 0 15px; flex: 1; overflow: hidden;">
                    <div id="sales-forecast-chart" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- ═══ 4. TASKS STATUS TABLE ═══ -->
        <div class="section-header">
            <i class="fa fa-check-circle"></i> TASKS STATUS
            <div style="margin-left: auto; display: flex; gap: 8px;">
                <button class="btn-refresh" @click="refreshTasks()" :class="{ 'loading': tasksLoading }">
                    <i class="fa fa-refresh" :class="{ 'fa-spin': tasksLoading }"></i>
                </button>
            </div>
        </div>
        <div class="portlet light" style="padding:0; border-top: 3px solid #10b981; margin-bottom: 30px;">
            <div class="table-responsive" style="min-height: 200px; position: relative;">
                <div x-show="tasksLoading" style="position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 10;">
                    <div class="loading-spinner" style="width: 32px; height: 32px; border-width: 3px;"></div>
                </div>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Last Contacted</th>
                            <th>Sales Representative</th>
                            <th>Status</th>
                            <th>Deal Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr style="cursor: pointer;" @click="handleTaskClick('{{ $task['name'] }}')">
                            <td style="font-weight: 600; color:#2d3748;">{{ $task['name'] }}</td>
                            <td style="font-weight: 500;">{{ $task['last_contacted'] }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($task['sales_rep_name']) }}&background=4f46e5&color=fff&size=28" alt="{{ $task['sales_rep_name'] }}" class="avatar">
                                    <span style="font-weight:500;">{{ $task['sales_rep_name'] }}</span>
                                </div>
                            </td>
                            <td><span class="{{ $task['badgeClass'] }}">{{ $task['status'] }}</span></td>
                            <td style="font-weight: 700; color: {{ $task['dealColor'] }}; font-size: 12px;">
                                ${{ number_format($task['deal_value'] / 1000, 1) }}K
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fa fa-inbox"></i>
                                No leads found. Create a lead to see tasks here.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function dashboardApp() {
            return {
                todosLoading: false,
                tasksLoading: false,
                balanceChart: null,
                forecastChart: null,

                init() {
                    this.initCharts();
                    window.addEventListener('resize', () => {
                        if (this.balanceChart) try { this.balanceChart.render(); } catch(e) {}
                        if (this.forecastChart) try { this.forecastChart.render(); } catch(e) {}
                    });
                    window.addEventListener('sidebar-toggled', () => {
                        setTimeout(() => {
                            window.dispatchEvent(new Event('resize'));
                        }, 250);
                    });
                },

                handleKpiClick(key) {
                    console.log('KPI clicked:', key);
                    // Add navigation or drill-down functionality here
                },

                handleTodoClick(fileNo, type) {
                    console.log('Todo clicked:', fileNo, type);
                    window.location.href = `/${type}/list`;
                },

                handleTaskClick(name) {
                    console.log('Task clicked:', name);
                    // Navigate to quotation or customer detail
                },

                async refreshTodos() {
                    this.todosLoading = true;
                    try {
                        // Simulate API call - replace with actual endpoint
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        location.reload(); // Temporary - replace with AJAX
                    } catch (error) {
                        console.error('Error refreshing todos:', error);
                    } finally {
                        this.todosLoading = false;
                    }
                },

                async refreshTasks() {
                    this.tasksLoading = true;
                    try {
                        // Simulate API call - replace with actual endpoint
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        location.reload(); // Temporary - replace with AJAX
                    } catch (error) {
                        console.error('Error refreshing tasks:', error);
                    } finally {
                        this.tasksLoading = false;
                    }
                },

                initCharts() {
                    const renderAll = () => {
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.initBalanceChart();
                                this.initForecastChart();
                            }, 50);
                        });
                    };

                    if (typeof ApexCharts !== 'undefined') {
                        renderAll();
                    } else {
                        let attempts = 0;
                        const timer = setInterval(() => {
                            attempts++;
                            if (typeof ApexCharts !== 'undefined') {
                                clearInterval(timer);
                                renderAll();
                            } else if (attempts > 50) {
                                clearInterval(timer);
                                console.error('ApexCharts script failed to load.');
                            }
                        }, 100);
                    }
                },

                initBalanceChart() {
                    var chartElement = document.querySelector("#balance-overview-chart");
                    if (!chartElement || typeof ApexCharts === 'undefined') return;

                    if (this.balanceChart) {
                        try { this.balanceChart.destroy(); } catch(e) {}
                    }
                    chartElement.innerHTML = '';

                    var options = {
                        series: [{
                            name: 'Revenue',
                            data: [{{ implode(',', $balanceChart['revenueData']) }}]
                        }, {
                            name: 'Expenses',
                            data: [{{ implode(',', $balanceChart['expenseData']) }}]
                        }],
                        chart: {
                            id: 'balance-chart',
                            height: 300,
                            type: 'area',
                            toolbar: { show: false },
                            fontFamily: 'Inter, -apple-system, sans-serif',
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                dynamicAnimation: { speed: 300 }
                            }
                        },
                        colors: ['#0ab39c', '#f06548'],
                        dataLabels: { enabled: false },
                        stroke: { 
                            curve: 'smooth', 
                            width: 3 
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        xaxis: {
                            categories: [{!! "'" . implode("','", $balanceChart['months']) . "'" !!}],
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    colors: '#718096'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function (val) {
                                    return "$" + val.toFixed(1) + "k";
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    colors: '#718096'
                                }
                            }
                        },
                        grid: {
                            borderColor: '#f1f3f6',
                            strokeDashArray: 3,
                            padding: {
                                top: 0,
                                right: 10,
                                bottom: 0,
                                left: 10
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return "$" + val.toFixed(2) + "k";
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            fontSize: '12px',
                            fontWeight: 600,
                            markers: {
                                width: 12,
                                height: 12,
                                radius: 3
                            }
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: { height: 240 },
                                legend: { position: 'bottom', horizontalAlign: 'center' },
                                xaxis: { labels: { rotate: -45, style: { fontSize: '9px' } } }
                            }
                        }, {
                            breakpoint: 480,
                            options: {
                                chart: { height: 200 },
                                legend: { fontSize: '10px' }
                            }
                        }]
                    };

                    this.balanceChart = new ApexCharts(document.querySelector("#balance-overview-chart"), options);
                    this.balanceChart.render();
                },

                initForecastChart() {
                    // Destroy existing chart if it exists
                    if (this.forecastChart) {
                        this.forecastChart.destroy();
                    }

                    var goalValue = {{ $forecastChart['goal'] }};
                    var pendingValue = {{ $forecastChart['pendingForecast'] }};
                    var revenueValue = {{ $forecastChart['revenue'] }};

                    var options = {
                        series: [{
                            name: 'Goal',
                            data: [goalValue]
                        }, {
                            name: 'Pending Forecast',
                            data: [pendingValue]
                        }, {
                            name: 'Revenue',
                            data: [revenueValue]
                        }],
                        chart: {
                            id: 'forecast-chart',
                            height: 350,
                            type: 'bar',
                            toolbar: { show: false },
                            fontFamily: 'Inter, -apple-system, sans-serif',
                            parentHeightOffset: 0
                        },
                        colors: ['#405189', '#0ab39c', '#f7b84b'],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '70%',
                                endingShape: 'rounded',
                                borderRadius: 6
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            show: false
                        },
                        xaxis: {
                            categories: ['Total Forecasted Value'],
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: {
                                    fontSize: '12px',
                                    fontWeight: 600,
                                    colors: '#64748b'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function (val) {
                                    if (val === 0) return '$0';
                                    return "$" + (val / 1000).toFixed(0) + "k";
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    colors: '#94a3b8'
                                }
                            }
                        },
                        fill: { 
                            opacity: 1
                        },
                        grid: {
                            show: true,
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4,
                            xaxis: {
                                lines: { show: false }
                            },
                            padding: {
                                top: 0,
                                right: 15,
                                bottom: 0,
                                left: 10
                            }
                        },
                        legend: {
                            show: true,
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '12px',
                            fontWeight: 600,
                            markers: {
                                width: 12,
                                height: 12,
                                radius: 3
                            },
                            itemMargin: {
                                horizontal: 12,
                                vertical: 8
                            }
                        },
                        tooltip: {
                            enabled: true,
                            y: {
                                formatter: function (val) {
                                    return "$" + val.toLocaleString();
                                },
                                title: {
                                    formatter: function (seriesName) {
                                        return seriesName + ': ';
                                    }
                                }
                            },
                            marker: {
                                show: true
                            }
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: { height: 260 },
                                plotOptions: { bar: { columnWidth: '85%' } }
                            }
                        }, {
                            breakpoint: 480,
                            options: {
                                chart: { height: 220 },
                                legend: { fontSize: '10px' }
                            }
                        }]
                    };

                    var chartElement = document.querySelector("#sales-forecast-chart");
                    if (chartElement) {
                        // Clear any existing content
                        chartElement.innerHTML = '';
                        this.forecastChart = new ApexCharts(chartElement, options);
                        this.forecastChart.render();
                    }
                },

                async reloadBalanceChart(period) {
                    try {
                        const response = await fetch(`/dashboard/chart-data?type=balance&period=${period}`);
                        if (!response.ok) throw new Error('Failed to fetch chart data');
                        
                        const data = await response.json();
                        
                        ApexCharts.exec('balance-chart', 'updateOptions', {
                            series: [
                                { name: 'Revenue', data: data.revenueData },
                                { name: 'Expenses', data: data.expenseData }
                            ],
                            xaxis: { categories: data.months }
                        });

                        // Update stats row
                        const statsRow = document.getElementById('balance-stats-row');
                        if (statsRow) {
                            statsRow.innerHTML = `
                                <div class="chart-stat-item">
                                    <h4 class="text-primary">$${(data.totalRevenue / 1000).toFixed(1)}k</h4>
                                    <span>Revenue</span>
                                </div>
                                <div class="chart-stat-item" style="border-left:1px solid #edf2f7;padding-left:30px;">
                                    <h4>$${(data.totalExpenses / 1000).toFixed(1)}k</h4>
                                    <span>Expenses</span>
                                </div>
                                <div class="chart-stat-item" style="border-left:1px solid #edf2f7;padding-left:30px;">
                                    <h4 style="color:#f59e0b;">${data.profitRatio}%</h4>
                                    <span>Profit Ratio</span>
                                </div>
                            `;
                        }
                    } catch (error) {
                        console.error('Error reloading balance chart:', error);
                    }
                },

                async reloadForecastChart(period) {
                    try {
                        const response = await fetch(`/dashboard/chart-data?type=forecast&period=${period}`);
                        if (!response.ok) throw new Error('Failed to fetch chart data');
                        
                        const data = await response.json();
                        
                        ApexCharts.exec('forecast-chart', 'updateOptions', {
                            series: [
                                { name: 'Goal', data: [data.goal] },
                                { name: 'Pending Forecast', data: [data.pendingForecast] },
                                { name: 'Revenue', data: [data.revenue] }
                            ]
                        });
                    } catch (error) {
                        console.error('Error reloading forecast chart:', error);
                    }
                }
            };
        }
    </script>
    @endpush
</x-layout>
