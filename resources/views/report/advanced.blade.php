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
        .rpt-row .rpt-label { min-width: 120px; max-width: 160px; flex-shrink: 0; }
        .rpt-row .rpt-input-wrap { flex: 1; min-width: 0; }
        .rpt-chk-group { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .rpt-chk-group label { font-size: 10px; display: flex; align-items: center; gap: 4px; cursor: pointer; color: #334155; white-space: nowrap; user-select: none; }
        .rpt-chk-group input[type="checkbox"] { width: 13px !important; height: 13px !important; accent-color: #3b82f6; cursor: pointer; }
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
        .chart-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 14px; min-width: 0; }
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .chart-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #f1f5f9; }
        .chart-empty { display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 12px; }
        .tab-nav { display: flex; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; gap: 2px; }
        .tab-btn { background: none; border: none; padding: 8px 16px; font-size: 11px; font-weight: 600; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s; }
        .tab-btn:hover { color: #0f172a; background: #f1f5f9; }
        .tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; background: #fff; }
        .margin-bar { display: inline-flex; align-items: center; gap: 5px; }
        .margin-bar-track { width: 40px; height: 4px; background: #f0f3f8; border-radius: 2px; overflow: hidden; }
        .margin-bar-fill { height: 100%; border-radius: 2px; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        .loading-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.75); display: flex; align-items: center; justify-content: center; z-index: 10; border-radius: 4px; backdrop-filter: blur(1px); }
        .spinner { width: 28px; height: 28px; border: 3px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .table-custom { width: 100%; border-collapse: collapse; font-size: 11px; }
        .table-custom thead th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-weight: 700; color: #475569; text-transform: uppercase; font-size: 10px; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
        .table-custom tbody td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .table-custom tbody tr:hover { background: #f8fafc; }
        .total-row td { background: #f1f5f9; font-weight: 700; color: #0f172a; border-top: 2px solid #e2e8f0; border-bottom: 2px solid #e2e8f0; }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .rpt-grid-2 { grid-template-columns: 1fr; gap: 8px; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .rpt-row { flex-direction: column; align-items: stretch; margin-bottom: 8px; }
            .rpt-row .rpt-label { min-width: 100%; max-width: 100%; border-radius: 2px 2px 0 0; border-right: 1px solid #e2e8f0; height: 24px; }
            .rpt-row .rpt-input-wrap { border-radius: 0 0 2px 2px; }
            .tab-btn { padding: 6px 10px; font-size: 10px; }
            .table-custom { font-size: 10px; }
            .table-custom thead th, .table-custom tbody td { padding: 6px 8px; }
        }

        .print-header { display: none; }
        .print-section-title { display: none; }

        /* Print Media Styles */
        @media print {
            aside, header, nav, .sidebar, .top-navbar, .page-bar, .portlet-title, .rpt-filter-section, .tab-nav, .loading-overlay, button, .btn-freightx, .rpt-view-btn, .no-print, [onclick*="sidebar"] {
                display: none !important;
            }

            html, body, .app-wrapper, .main-content-wrapper, main {
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }

            .portlet.light {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .print-header {
                display: block !important;
                margin-bottom: 16px !important;
            }

            .print-section-title {
                display: block !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                color: #0f172a !important;
                text-transform: uppercase !important;
                margin-top: 16px !important;
                margin-bottom: 6px !important;
                border-bottom: 1.5px solid #cbd5e1 !important;
                padding-bottom: 3px !important;
            }

            .tab-panel {
                display: block !important;
                margin-bottom: 20px !important;
                page-break-inside: avoid !important;
            }

            .kpi-grid {
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 8px !important;
                margin-bottom: 15px !important;
                page-break-inside: avoid !important;
            }

            .kpi-card {
                border: 1px solid #94a3b8 !important;
                box-shadow: none !important;
                padding: 6px 8px !important;
                background: #fff !important;
            }

            .kpi-icon {
                display: none !important;
            }

            .chart-card {
                border: none !important;
                padding: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
            }

            .table-custom {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 9px !important;
            }

            .table-custom th, .table-custom td {
                border: 1px solid #cbd5e1 !important;
                padding: 5px 7px !important;
                color: #000 !important;
            }

            .table-custom thead th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .margin-bar {
                display: none !important;
            }
        }
    </style>
    @endpush

    <div style="background: #eef1f5; min-height: 100vh; padding: 12px;">
        <div x-data="advancedReport()" x-init="init()" style="position: relative;">
            <div x-show="loading" class="loading-overlay no-print"><div class="spinner"></div></div>

            <!-- Print Header (Visible only when printing) -->
            <div class="print-header">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px;">
                    <div>
                        <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a; text-transform: uppercase;">FreightX - Advanced Analytical Report</h2>
                        <div style="font-size: 10px; color: #475569; margin-top: 4px;">
                            <strong>Analysis Period:</strong> <span x-text="filters.date_from + ' ~ ' + filters.date_to"></span> |
                            <strong>Office:</strong> <span x-text="getOfficeName(filters.office_id)"></span> |
                            <strong>Currency:</strong> Report Currency: USD
                        </div>
                    </div>
                    <div style="text-align: right; font-size: 9px; color: #64748b;">
                        <div>Printed Date: {{ date('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <div style="font-size: 11px; color: #64748b; margin-bottom: 10px;" class="no-print">
                <a href="/" style="color: #64748b; text-decoration: none;" target="_blank"><i class="fa fa-home"></i> Home</a>
                <i class="fa fa-angle-right" style="margin: 0 4px; opacity: 0.5;"></i>
                <a href="/report" style="color: #64748b; text-decoration: none;">Reports</a>
                <i class="fa fa-angle-right" style="margin: 0 4px; opacity: 0.5;"></i>
                <span style="color: #0f172a; font-weight: 700;">Advanced Report</span>
            </div>

            <div class="portlet light">
                <div class="portlet-title no-print" style="flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-gears" style="color: #3b82f6; font-size: 14px;"></i>
                        <span class="caption-subject" style="font-weight: 700;">Advanced Analytical Report</span>
                    </div>
                    <div style="display: flex; gap: 6px; margin-left: auto;">
                        <button class="btn-freightx" @click="printReport()"><i class="fa fa-print"></i> PRINT</button>
                        <button class="btn-freightx" style="background: #10b981;" @click="exportExcel()"><i class="fa fa-file-excel-o"></i> EXPORT</button>
                    </div>
                </div>

                <div class="rpt-filter-section no-print">
                    <div class="rpt-grid-2">
                        <div>
                            <div class="rpt-row">
                                <div class="rpt-label">Shipment Type</div>
                                <div class="rpt-input-wrap">
                                    <div class="rpt-chk-group">
                                        @foreach($shippingTypes as $st)
                                        <label><input type="checkbox" value="{{ $st }}" x-model="filters.shipping_types"> {{ $st }}</label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="rpt-row">
                                <div class="rpt-label">Analysis Period</div>
                                <div class="rpt-input-wrap" style="flex-direction:column; align-items:stretch; gap:4px; padding:4px 6px;">
                                    <div style="display:flex; gap:4px; align-items:center;">
                                        <input type="date" x-model="filters.date_from" class="form-control-gf" style="flex:1; border:1px solid #d1d5db; border-radius:2px; height:22px; padding:0 4px; font-size:10px;">
                                        <span style="font-size:10px; color:#64748b;">~</span>
                                        <input type="date" x-model="filters.date_to" class="form-control-gf" style="flex:1; border:1px solid #d1d5db; border-radius:2px; height:22px; padding:0 4px; font-size:10px;">
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
                                <div class="rpt-label">Currency</div>
                                <div class="rpt-input-wrap">
                                    <select x-model="filters.currency_id" class="form-control-gf" style="width:100%;">
                                        <option value="">Report Currency: USD</option>
                                        @foreach($currencies as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="rpt-row" style="align-items:center;">
                                <div class="rpt-label">&nbsp;</div>
                                <div class="rpt-input-wrap">
                                    <label style="font-size:10px; display:flex; align-items:center; gap:4px; cursor:pointer; color:#334155; user-select:none;">
                                        <input type="checkbox" x-model="filters.include_internal" style="width:13px !important; height:13px !important; accent-color: #3b82f6;"> Include Internal Profit
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:center; margin-top:6px;">
                        <button class="rpt-view-btn" @click="fetchData()"><i class="fa fa-search" style="margin-right:4px;"></i> Refresh Analysis</button>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Total Revenue</div>
                        <div class="kpi-value" x-text="'$' + fmt(data.summary.total_revenue)"></div>
                    </div>
                    <i class="fa fa-line-chart kpi-icon" style="color: #3b82f6;"></i>
                </div>
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Total Cost</div>
                        <div class="kpi-value" x-text="'$' + fmt(data.summary.total_cost)"></div>
                    </div>
                    <i class="fa fa-money kpi-icon" style="color: #ef4444;"></i>
                </div>
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Net Profit</div>
                        <div class="kpi-value" :style="'color:' + (data.summary.gross_profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(data.summary.gross_profit)"></div>
                    </div>
                    <i class="fa fa-balance-scale kpi-icon" style="color: #10b981;"></i>
                </div>
                <div class="kpi-card">
                    <div>
                        <div class="kpi-label">Total Shipments</div>
                        <div class="kpi-value" x-text="fmtInt(data.summary.total_count)"></div>
                    </div>
                    <i class="fa fa-ship kpi-icon" style="color: #f59e0b;"></i>
                </div>
            </div>

            <!-- Charts Section (4 charts) -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-title" style="color:#f97316;"><i class="fa fa-line-chart" style="margin-right:4px;"></i> Net Profit Average Trend</div>
                    <div id="avgLineChart" style="min-height: 340px;"></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title" style="color:#3b82f6;"><i class="fa fa-pie-chart" style="margin-right:4px;"></i> Shipping Type Comparison (Radar, score 0-100)</div>
                    <div id="shippingRadarChart" style="min-height: 340px;"></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title" style="color:#10b981;"><i class="fa fa-bar-chart" style="margin-right:4px;"></i> Top 10 Customers by Profit</div>
                    <div id="partnerBarChart" style="min-height: 340px;"></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title" style="color:#ef4444;"><i class="fa fa-building" style="margin-right:4px;"></i> Office-wise Revenue, Cost &amp; Profit</div>
                    <div id="officeColumnChart" style="min-height: 340px;"></div>
                </div>
            </div>

            <!-- Multi-Tab Table Panel -->
            <div class="chart-card" style="margin-bottom: 14px;">
                <div class="tab-nav">
                    <button class="tab-btn" :class="{ active: activeTab === 'shipping' }" @click="switchTab('shipping')">By Shipping Type</button>
                    <button class="tab-btn" :class="{ active: activeTab === 'partner' }" @click="switchTab('partner')">By Customer</button>
                    <button class="tab-btn" :class="{ active: activeTab === 'office' }" @click="switchTab('office')">By Office</button>
                    <button class="tab-btn" :class="{ active: activeTab === 'sales' }" @click="switchTab('sales')">By Sales Person</button>
                </div>

                <div class="tab-panel" :class="{ active: activeTab === 'shipping' }">
                    <div class="print-section-title">Breakdown by Shipping Type</div>
                    <div style="overflow-x: auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Shipping Type</th>
                                    <th style="text-align:right;">Revenue</th>
                                    <th style="text-align:right;">Cost</th>
                                    <th style="text-align:right;">Profit</th>
                                    <th style="text-align:center;">Margin</th>
                                    <th style="text-align:right;">Volume (CBM)</th>
                                    <th style="text-align:center;">Shipments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in data.by_shipping_type" :key="idx">
                                    <tr>
                                        <td><span style="background:#eff6ff;color:#3b82f6;padding:2px 6px;border-radius:2px;font-weight:700;font-size:10px;" x-text="row.shipping_type"></span></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.revenue)"></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.cost)"></td>
                                        <td style="text-align:right;font-weight:700;" :style="'color:' + (row.profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(row.profit)"></td>
                                        <td style="text-align:center;">
                                            <div class="margin-bar">
                                                <div class="margin-bar-track"><div class="margin-bar-fill" :style="'width:' + Math.min(Math.abs(row.margin), 100) + '%; background:' + (row.margin >= 0 ? '#10b981' : '#ef4444')"></div></div>
                                                <span style="font-size:10px;font-weight:700;" x-text="row.margin + '%'"></span>
                                            </div>
                                        </td>
                                        <td style="text-align:right;" x-text="fmt(row.volume)"></td>
                                        <td style="text-align:center;font-weight:600;" x-text="row.count"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td class="total-label-cell" style="text-align:left;">TOTAL</td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_revenue)"></td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_cost)"></td>
                                    <td class="total-val-cell" style="text-align:right;" :style="'color:' + (data.summary.gross_profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(data.summary.gross_profit)"></td>
                                    <td class="total-val-cell" style="text-align:center;" x-text="data.summary.margin + '%'"></td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="fmt(data.summary.total_volume)"></td>
                                    <td class="total-val-cell" style="text-align:center;" x-text="data.summary.total_count"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="tab-panel" :class="{ active: activeTab === 'partner' }">
                    <div class="print-section-title">Breakdown by Customer</div>
                    <div style="overflow-x: auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Customer</th>
                                    <th style="text-align:right;">Revenue</th>
                                    <th style="text-align:right;">Cost</th>
                                    <th style="text-align:right;">Profit</th>
                                    <th style="text-align:center;">Margin</th>
                                    <th style="text-align:right;">Volume (CBM)</th>
                                    <th style="text-align:center;">Shipments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in data.by_partner" :key="idx">
                                    <tr>
                                        <td style="font-weight:700;color:#3b82f6;" x-text="row.partner"></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.revenue)"></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.cost)"></td>
                                        <td style="text-align:right;font-weight:700;" :style="'color:' + (row.profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(row.profit)"></td>
                                        <td style="text-align:center;">
                                            <div class="margin-bar">
                                                <div class="margin-bar-track"><div class="margin-bar-fill" :style="'width:' + Math.min(Math.abs(row.margin), 100) + '%; background:' + (row.margin >= 0 ? '#10b981' : '#ef4444')"></div></div>
                                                <span style="font-size:10px;font-weight:700;" x-text="row.margin + '%'"></span>
                                            </div>
                                        </td>
                                        <td style="text-align:right;" x-text="fmt(row.volume)"></td>
                                        <td style="text-align:center;font-weight:600;" x-text="row.count"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td class="total-label-cell" style="text-align:left;">TOTAL</td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_revenue)"></td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_cost)"></td>
                                    <td class="total-val-cell" style="text-align:right;" :style="'color:' + (data.summary.gross_profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(data.summary.gross_profit)"></td>
                                    <td class="total-val-cell" style="text-align:center;" x-text="data.summary.margin + '%'"></td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="fmt(data.summary.total_volume)"></td>
                                    <td class="total-val-cell" style="text-align:center;" x-text="data.summary.total_count"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="tab-panel" :class="{ active: activeTab === 'office' }">
                    <div class="print-section-title">Breakdown by Office</div>
                    <div style="overflow-x: auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Office</th>
                                    <th style="text-align:right;">Revenue</th>
                                    <th style="text-align:right;">Cost</th>
                                    <th style="text-align:right;">Profit</th>
                                    <th style="text-align:right;">Volume (CBM)</th>
                                    <th style="text-align:center;">Shipments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in data.by_office" :key="idx">
                                    <tr>
                                        <td style="font-weight:700;color:#3b82f6;" x-text="row.office"></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.revenue)"></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.cost)"></td>
                                        <td style="text-align:right;font-weight:700;" :style="'color:' + (row.profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(row.profit)"></td>
                                        <td style="text-align:right;" x-text="fmt(row.volume)"></td>
                                        <td style="text-align:center;font-weight:600;" x-text="row.count"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td class="total-label-cell" style="text-align:left;">TOTAL</td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_revenue)"></td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_cost)"></td>
                                    <td class="total-val-cell" style="text-align:right;" :style="'color:' + (data.summary.gross_profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(data.summary.gross_profit)"></td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="fmt(data.summary.total_volume)"></td>
                                    <td class="total-val-cell" style="text-align:center;" x-text="data.summary.total_count"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="tab-panel" :class="{ active: activeTab === 'sales' }">
                    <div class="print-section-title">Breakdown by Sales Person</div>
                    <div style="overflow-x: auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Sales Person</th>
                                    <th style="text-align:right;">Revenue</th>
                                    <th style="text-align:right;">Cost</th>
                                    <th style="text-align:right;">Profit</th>
                                    <th style="text-align:center;">Shipments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in data.by_sales_person" :key="idx">
                                    <tr>
                                        <td style="font-weight:700;color:#3b82f6;" x-text="row.sales"></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.revenue)"></td>
                                        <td style="text-align:right;" x-text="'$' + fmt(row.cost)"></td>
                                        <td style="text-align:right;font-weight:700;" :style="'color:' + (row.profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(row.profit)"></td>
                                        <td style="text-align:center;font-weight:600;" x-text="row.count"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td class="total-label-cell" style="text-align:left;">TOTAL</td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_revenue)"></td>
                                    <td class="total-val-cell" style="text-align:right;" x-text="'$' + fmt(data.summary.total_cost)"></td>
                                    <td class="total-val-cell" style="text-align:right;" :style="'color:' + (data.summary.gross_profit >= 0 ? '#10b981' : '#ef4444')" x-text="'$' + fmt(data.summary.gross_profit)"></td>
                                    <td class="total-val-cell" style="text-align:center;" x-text="data.summary.total_count"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    function advancedReport() {
        const COLORS = { orange: '#f97316', red: '#ef4444', green: '#10b981', blue: '#3b82f6' };

        return {
            loading: false,
            activeTab: 'shipping',
            _reqId: 0,
            _timer: null,
            charts: {},
            filters: {
                date_from: '2025-01-01',
                date_to: '{{ now()->endOfMonth()->format("Y-m-d") }}',
                shipping_types: [],
                office_id: '',
                currency_id: '',
                include_internal: false,
            },
            offices: @json($offices),
            currencies: @json($currencies),
            data: {
                summary: { total_revenue: 0, total_cost: 0, gross_profit: 0, margin: 0, total_volume: 0, total_count: 0 },
                by_shipping_type: [],
                by_office: [],
                by_partner: [],
                by_sales_person: [],
            },

            init() {
                // Filter badalte hi (x-model update hone ke BAAD) data reload hoga
                ['shipping_types', 'date_from', 'date_to', 'office_id', 'currency_id', 'include_internal']
                    .forEach(key => this.$watch('filters.' + key, () => this.fetchDataDebounced()));

                this.fetchData();

                window.addEventListener('resize', () => {
                    Object.values(this.charts).forEach(c => c && c.resize && c.resize());
                });
            },

            fetchDataDebounced() {
                clearTimeout(this._timer);
                this._timer = setTimeout(() => this.fetchData(), 120);
            },

            getOfficeName(officeId) {
                if (!officeId) return 'All Offices';
                const off = this.offices.find(o => o.id == officeId);
                return off ? `${off.code} - ${off.name}` : 'All Offices';
            },

            async fetchData() {
                const reqId = ++this._reqId;
                this.loading = true;
                try {
                    const p = new URLSearchParams();
                    p.append('date_from', this.filters.date_from);
                    p.append('date_to', this.filters.date_to);
                    p.append('currency_id', this.filters.currency_id);
                    p.append('include_internal', this.filters.include_internal ? '1' : '');
                    if (this.filters.office_id) p.append('office_id', this.filters.office_id);
                    [...this.filters.shipping_types].forEach(t => p.append('shipping_types[]', t));

                    const resp = await fetch('{{ route("report.advanced.data") }}?' + p.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();

                    // Purana response ignore karo agar naya request chal chuka hai
                    if (reqId !== this._reqId) return;

                    this.data = {
                        summary: json.summary || this.data.summary,
                        by_shipping_type: json.by_shipping_type || [],
                        by_office: json.by_office || [],
                        by_partner: json.by_partner || [],
                        by_sales_person: json.by_sales_person || [],
                    };
                    this.$nextTick(() => this.renderCharts());
                } catch (e) {
                    console.error('Fetch error:', e);
                } finally {
                    if (reqId === this._reqId) this.loading = false;
                }
            },

            switchTab(tab) { this.activeTab = tab; },

            /* ---------------- Charts ---------------- */

            renderCharts() {
                this.renderAvgLine();
                this.renderShippingRadar();
                this.renderPartnerBar();
                this.renderOfficeColumn();
            },

            mount(key, elId, options, hasData) {
                const el = document.getElementById(elId);
                if (!el) return;
                if (this.charts[key]) { this.charts[key].destroy(); this.charts[key] = null; }
                if (!hasData) {
                    el.innerHTML = '<div class="chart-empty" style="height:340px;">No data for selected filters</div>';
                    return;
                }
                el.innerHTML = '';
                this.charts[key] = new ApexCharts(el, options);
                this.charts[key].render();
            },

            money(v) { return '$' + this.fmt(v); },

            moneyShort(v) {
                v = parseFloat(v || 0);
                const a = Math.abs(v);
                if (a >= 1000000) return '$' + (v / 1000000).toFixed(1) + 'M';
                if (a >= 1000) return '$' + (v / 1000).toFixed(1) + 'K';
                return '$' + v.toFixed(0);
            },

            // 1) Smooth line (area) - Net Profit Average
            renderAvgLine() {
                const s = this.data.summary || {};
                const vals = [
                    Number(s.daily_average ?? 0),
                    Number(s.weekly_average ?? 0),
                    Number(s.monthly_average ?? 0),
                    Number(s.yearly_average ?? 0),
                ];
                const hasData = vals.some(v => v !== 0);

                this.mount('avg', 'avgLineChart', {
                    series: [{ name: 'Average Net Profit', data: vals }],
                    chart: { type: 'area', height: 340, toolbar: { show: false }, zoom: { enabled: false } },
                    colors: [COLORS.orange],
                    stroke: { curve: 'smooth', width: 4, lineCap: 'round' },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 95, 100] } },
                    markers: { size: 6, colors: ['#fff'], strokeColors: COLORS.orange, strokeWidth: 3, hover: { size: 8 } },
                    dataLabels: { enabled: true, offsetY: -8, formatter: v => this.moneyShort(v), style: { fontSize: '10px', colors: ['#c2410c'] }, background: { enabled: false } },
                    xaxis: { categories: ['Daily', 'Weekly', 'Monthly', 'Yearly'], labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } } },
                    yaxis: { labels: { style: { colors: '#64748b', fontSize: '10px' }, formatter: v => this.moneyShort(v) } },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                    tooltip: { y: { formatter: v => this.money(v) } },
                }, hasData);
            },

            // 2) Radar - Shipping types compared on 5 metrics (0-100 score)
            renderShippingRadar() {
                const items = [...(this.data.by_shipping_type || [])]
                    .sort((a, b) => parseFloat(b.revenue || 0) - parseFloat(a.revenue || 0))
                    .slice(0, 4);

                const metrics = [
                    { label: 'Revenue',   key: 'revenue', fmt: v => this.money(v) },
                    { label: 'Profit',    key: 'profit',  fmt: v => this.money(v) },
                    { label: 'Margin',    key: 'margin',  fmt: v => this.fmt(v) + '%' },
                    { label: 'Volume',    key: 'volume',  fmt: v => this.fmt(v) + ' CBM' },
                    { label: 'Shipments', key: 'count',   fmt: v => this.fmtInt(v) },
                ];

                // Har metric ko 0-100 score mein badlo (us metric ki sabse badi value = 100)
                const maxes = metrics.map(m => Math.max(0, ...items.map(i => parseFloat(i[m.key] || 0))));
                const series = items.map(i => ({
                    name: i.shipping_type || 'Unknown',
                    data: metrics.map((m, idx) => {
                        const v = Math.max(0, parseFloat(i[m.key] || 0));
                        return maxes[idx] > 0 ? Math.round((v / maxes[idx]) * 100) : 0;
                    })
                }));

                this.mount('radar', 'shippingRadarChart', {
                    series: series,
                    chart: { type: 'radar', height: 340, toolbar: { show: false }, dropShadow: { enabled: false } },
                    colors: [COLORS.orange, COLORS.blue, COLORS.green, COLORS.red],
                    labels: metrics.map(m => m.label),
                    stroke: { width: 3 },
                    fill: { opacity: 0.22 },
                    markers: { size: 5, strokeWidth: 1, hover: { size: 8 } },
                    dataLabels: { enabled: false },
                    plotOptions: { radar: { size: 125, polygons: { strokeColors: '#e5e7eb', connectorColors: '#e5e7eb', fill: { colors: ['#ffffff', '#f8fafc'] } } } },
                    xaxis: { labels: { style: { colors: Array(metrics.length).fill('#475569'), fontSize: '11px', fontWeight: 700 } } },
                    yaxis: { min: 0, max: 100, tickAmount: 5, labels: { style: { colors: ['#94a3b8'], fontSize: '9px' }, formatter: v => Math.round(v) } },
                    legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '12px', markers: { width: 22, height: 10, radius: 2 }, labels: { colors: '#475569' } },
                    tooltip: {
                        y: {
                            // Hover par asli value dikhao, score nahi
                            formatter: (val, opts) => {
                                const m = metrics[opts.dataPointIndex];
                                const it = items[opts.seriesIndex];
                                return m.fmt(it ? it[m.key] : 0) + '  (score ' + val + ')';
                            }
                        }
                    },
                }, items.length > 0);
            },

            // 3) Horizontal colorful bars - Top 10 customers by profit
            renderPartnerBar() {
                const rows = [...(this.data.by_partner || [])]
                    .sort((a, b) => parseFloat(b.profit || 0) - parseFloat(a.profit || 0))
                    .slice(0, 10);

                const cycle = [COLORS.blue, COLORS.green, COLORS.orange];
                const colors = rows.map((r, i) => parseFloat(r.profit || 0) < 0 ? COLORS.red : cycle[i % cycle.length]);

                this.mount('partner', 'partnerBarChart', {
                    series: [{ name: 'Profit', data: rows.map(r => parseFloat(r.profit || 0)) }],
                    chart: { type: 'bar', height: 340, toolbar: { show: false } },
                    colors: colors,
                    plotOptions: { bar: { horizontal: true, distributed: true, borderRadius: 5, barHeight: '65%' } },
                    dataLabels: { enabled: true, formatter: v => this.moneyShort(v), style: { fontSize: '10px' } },
                    legend: { show: false },
                    xaxis: { categories: rows.map(r => r.partner || 'Unknown'), labels: { style: { colors: '#64748b', fontSize: '10px' }, formatter: v => this.moneyShort(v) } },
                    yaxis: { labels: { maxWidth: 140, style: { colors: '#334155', fontSize: '10px', fontWeight: 600 } } },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                    tooltip: { y: { formatter: v => this.money(v) } },
                }, rows.length > 0);
            },

            // 4) Grouped columns - Office wise revenue / cost / profit
            renderOfficeColumn() {
                const rows = this.data.by_office || [];

                this.mount('office', 'officeColumnChart', {
                    series: [
                        { name: 'Revenue', data: rows.map(r => parseFloat(r.revenue || 0)) },
                        { name: 'Cost',    data: rows.map(r => parseFloat(r.cost || 0)) },
                        { name: 'Profit',  data: rows.map(r => parseFloat(r.profit || 0)) },
                    ],
                    chart: { type: 'bar', height: 340, toolbar: { show: false } },
                    colors: [COLORS.blue, COLORS.orange, COLORS.green],
                    plotOptions: { bar: { columnWidth: '60%', borderRadius: 5 } },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    dataLabels: { enabled: false },
                    xaxis: { categories: rows.map(r => r.office || 'Unknown'), labels: { trim: true, style: { colors: '#64748b', fontSize: '10px', fontWeight: 600 } } },
                    yaxis: { labels: { style: { colors: '#64748b', fontSize: '10px' }, formatter: v => this.moneyShort(v) } },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                    legend: { position: 'top', horizontalAlign: 'center', fontSize: '12px', markers: { width: 12, height: 12, radius: 3 } },
                    tooltip: { shared: true, intersect: false, y: { formatter: v => this.money(v) } },
                }, rows.length > 0);
            },

            /* ---------------- Helpers ---------------- */

            fmt(v) {
                if (v === null || v === undefined) return '0.00';
                return parseFloat(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            fmtInt(v) {
                if (v === null || v === undefined) return '0';
                return parseInt(v).toLocaleString('en-US');
            },

            printReport() { window.print(); },

            exportExcel() {
                let csv = 'Shipping Type,Revenue,Cost,Profit,Margin,Volume,Shipments\n';
                this.data.by_shipping_type.forEach(r => {
                    csv += `"${r.shipping_type}",${r.revenue},${r.cost},${r.profit},${r.margin},${r.volume},${r.count}\n`;
                });
                csv += `"TOTAL",${this.data.summary.total_revenue},${this.data.summary.total_cost},${this.data.summary.gross_profit},${this.data.summary.margin},${this.data.summary.total_volume},${this.data.summary.total_count}\n`;
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url; a.download = 'advanced-report.csv'; a.click();
                URL.revokeObjectURL(url);
            },
        };
    }
    </script>
</x-layout>