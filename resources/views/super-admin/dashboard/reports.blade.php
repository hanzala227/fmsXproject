@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>Operations & Financial Reports</span>
                <span style="font-size: 10px; font-weight: 700; background: #ecfdf5; color: #047857; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Realtime Analytics</span>
            </h1>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">Platform-wide logistics throughput, modal market shares, and billing performance</p>
        </div>
        <div>
            <a href="{{ route('super-admin.dashboard.export', $reports['filters']) }}" style="height: 34px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; text-decoration: none; border-radius: 6px; box-shadow: 0 1px 2px rgba(10,179,156,0.25);">
                <i class="fa fa-download"></i> Export Analytics CSV
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.dashboard') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Overview</a>
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">Reports</a>
    </div>

    {{-- Filter Bar --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 22px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <form method="GET" action="{{ route('super-admin.dashboard.reports') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;">
            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 11px; font-weight: 600; color: #475569;">From:</label>
                    <input type="date" name="start_date" value="{{ $reports['filters']['start_date'] ?? '' }}" style="height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px; color: #1e293b;">
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 11px; font-weight: 600; color: #475569;">To:</label>
                    <input type="date" name="end_date" value="{{ $reports['filters']['end_date'] ?? '' }}" style="height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px; color: #1e293b;">
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 11px; font-weight: 600; color: #475569;">Company:</label>
                    <select name="company_id" style="height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 11px; color: #1e293b; min-width: 180px;">
                        <option value="">All Companies</option>
                        @foreach($reports['all_companies'] as $comp)
                            <option value="{{ $comp->id }}" {{ ($reports['filters']['company_id'] ?? '') == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} {{ $comp->code ? '(' . $comp->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" style="height: 32px; padding: 0 16px; background: #405189; color: #fff; border: none; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-filter"></i> Apply Filter
                </button>

                @if(!empty($reports['filters']['start_date']) || !empty($reports['filters']['end_date']) || !empty($reports['filters']['company_id']))
                    <a href="{{ route('super-admin.dashboard.reports') }}" style="font-size: 11px; color: #ef4444; text-decoration: none; font-weight: 600; margin-left: 4px;">
                        <i class="fa fa-times"></i> Reset
                    </a>
                @endif
            </div>

            <div style="font-size: 11px; color: #64748b; font-weight: 500;">
                Showing metrics for: <strong>{{ !empty($reports['filters']['company_id']) ? 'Selected Company' : 'All Tenants' }}</strong>
            </div>
        </form>
    </div>

    {{-- Multimodal Market Share Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Ocean Share --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.05em;">Ocean Freight</span>
                <i class="fa fa-ship" style="color: #0284c7; font-size: 16px;"></i>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a;">{{ $reports['ocean_total'] }} <span style="font-size: 13px; font-weight: 600; color: #64748b;">({{ $reports['ocean_share_pct'] }}%)</span></div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $reports['ocean_imports'] }} Imports &bull; {{ $reports['ocean_exports'] }} Exports</div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: {{ $reports['ocean_share_pct'] }}%; height: 100%; background: #0284c7; border-radius: 3px;"></div>
            </div>
        </div>

        {{-- Air Share --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 0.05em;">Air Cargo</span>
                <i class="fa fa-plane" style="color: #6366f1; font-size: 16px;"></i>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a;">{{ $reports['air_total'] }} <span style="font-size: 13px; font-weight: 600; color: #64748b;">({{ $reports['air_share_pct'] }}%)</span></div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $reports['air_imports'] }} Imports &bull; {{ $reports['air_exports'] }} Exports</div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: {{ $reports['air_share_pct'] }}%; height: 100%; background: #6366f1; border-radius: 3px;"></div>
            </div>
        </div>

        {{-- Land Share --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.05em;">Land Drayage</span>
                <i class="fa fa-truck" style="color: #f59e0b; font-size: 16px;"></i>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a;">{{ $reports['truck_total'] }} <span style="font-size: 13px; font-weight: 600; color: #64748b;">({{ $reports['truck_share_pct'] }}%)</span></div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Active Road Movements</div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: {{ $reports['truck_share_pct'] }}%; height: 100%; background: #f59e0b; border-radius: 3px;"></div>
            </div>
        </div>

        {{-- Platform Invoicing --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">Platform Invoicing</span>
                <i class="fa fa-money" style="color: #10b981; font-size: 16px;"></i>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #0f172a;">{{ $reports['invoices_count'] }} <span style="font-size: 13px; font-weight: 600; color: #64748b;">Invoices</span></div>
            <div style="font-size: 11px; color: #059669; font-weight: 600; margin-top: 4px;">
                ${{ number_format($reports['invoices_sum'], 2) }}
            </div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: 100%; height: 100%; background: #10b981; border-radius: 3px;"></div>
            </div>
        </div>
    </div>

    {{-- Interactive Analytics Charts --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px;">
        {{-- 6-Month Volume Trend Bar Chart --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 0;">Multi-Modal Freight Movements (6-Month Trend)</h3>
                <span style="font-size: 11px; color: #64748b;">Ocean vs Air vs Land</span>
            </div>
            <div style="height: 240px; position: relative;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- Mode Distribution Doughnut Chart --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 0;">Modal Market Share</h3>
                <span style="font-size: 11px; color: #64748b;">Shipments Breakdown</span>
            </div>
            <div style="height: 240px; position: relative; display: flex; align-items: center; justify-content: center;">
                <canvas id="shareChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Tenant Throughput & Seat Breakdown Table --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 24px;">
        <div style="padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div>
                <h3 style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 0;">Tenant Throughput & Commercial Activity Breakdown</h3>
                <span style="font-size: 11px; color: #64748b;">Freight movements and billing volume per tenant</span>
            </div>
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">
                Total Tenants: {{ $reports['company_activity']->total() }}
            </span>
        </div>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0; font-size: 10px; text-transform: uppercase;">
                        <th style="padding: 10px 16px;">Tenant Company</th>
                        <th style="padding: 10px 16px;">Code</th>
                        <th style="padding: 10px 16px;">Country</th>
                        <th style="padding: 10px 16px; text-align: center;">Ocean</th>
                        <th style="padding: 10px 16px; text-align: center;">Air</th>
                        <th style="padding: 10px 16px; text-align: center;">Land</th>
                        <th style="padding: 10px 16px; text-align: center;">Total Movements</th>
                        <th style="padding: 10px 16px; text-align: right;">Invoiced Volume</th>
                        <th style="padding: 10px 16px; text-align: center;">Users</th>
                        <th style="padding: 10px 16px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports['company_activity'] as $co)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 12px 16px; font-weight: 700; color: #1e293b;">
                            {{ $co->name }}
                        </td>
                        <td style="padding: 12px 16px; color: #64748b; font-family: monospace;">
                            {{ $co->code ?: '-' }}
                        </td>
                        <td style="padding: 12px 16px; color: #64748b;">
                            {{ $co->country ?? 'Global' }}
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="font-weight: 600; color: #0284c7;">{{ $co->ocean_count }}</span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="font-weight: 600; color: #6366f1;">{{ $co->air_count }}</span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="font-weight: 600; color: #f59e0b;">{{ $co->truck_count }}</span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="background: #eff6ff; color: #1e40af; padding: 2px 8px; border-radius: 9999px; font-weight: 700; font-size: 11px;">
                                {{ $co->total_shipments }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 700; color: #0f172a;">
                            ${{ number_format($co->invoiced_sum, 2) }}
                        </td>
                        <td style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569;">
                            {{ $co->users_count }}
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="background: {{ $co->status === 'active' ? '#d1fae5' : '#fee2e2' }}; color: {{ $co->status === 'active' ? '#065f46' : '#991b1b' }}; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 10px; text-transform: uppercase;">
                                {{ $co->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="padding: 24px; text-align: center; color: #94a3b8;">
                            No company activities match the selected filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports['company_activity']->hasPages())
        <div style="padding: 12px 18px; border-top: 1px solid #e2e8f0;">
            {{ $reports['company_activity']->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Chart.js CDN and initialization --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    const ctxTrend = document.getElementById('trendChart');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: @json($reports['trend_months']),
                datasets: [
                    {
                        label: 'Ocean Freight',
                        data: @json($reports['trend_ocean']),
                        backgroundColor: '#0284c7',
                        borderRadius: 4
                    },
                    {
                        label: 'Air Cargo',
                        data: @json($reports['trend_air']),
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    },
                    {
                        label: 'Land Drayage',
                        data: @json($reports['trend_truck']),
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    // Share Doughnut Chart
    const ctxShare = document.getElementById('shareChart');
    if (ctxShare) {
        new Chart(ctxShare, {
            type: 'doughnut',
            data: {
                labels: ['Ocean (' + {{ $reports['ocean_total'] }} + ')', 'Air (' + {{ $reports['air_total'] }} + ')', 'Land (' + {{ $reports['truck_total'] }} + ')'],
                datasets: [{
                    data: [{{ $reports['ocean_total'] }}, {{ $reports['air_total'] }}, {{ $reports['truck_total'] }}],
                    backgroundColor: ['#0284c7', '#6366f1', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endsection