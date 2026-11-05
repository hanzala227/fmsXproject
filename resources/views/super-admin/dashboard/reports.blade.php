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
            <a href="{{ route('super-admin.dashboard.export') }}" style="height: 32px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #fff; background: #0ab39c; text-decoration: none; border-radius: 4px;">
                <i class="fa fa-download"></i> Export Analytics CSV
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.dashboard') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Overview</a>
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">Reports</a>
        <a href="{{ route('super-admin.dashboard.system-status') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">System Status</a>
    </div>

    {{-- Multimodal Market Share Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Ocean Share --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: #0284c7; text-transform: uppercase;">Ocean Freight Share</span>
                <i class="fa fa-ship" style="color: #0284c7; font-size: 16px;"></i>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: #0f172a;">{{ $reports['ocean_share_pct'] }}%</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $reports['ocean_total'] }} of {{ $reports['grand_total'] }} Total Shipments</div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: {{ $reports['ocean_share_pct'] }}%; height: 100%; background: #0284c7; border-radius: 3px;"></div>
            </div>
        </div>

        {{-- Air Share --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: #6366f1; text-transform: uppercase;">Air Cargo Share</span>
                <i class="fa fa-plane" style="color: #6366f1; font-size: 16px;"></i>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: #0f172a;">{{ $reports['air_share_pct'] }}%</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $reports['air_total'] }} of {{ $reports['grand_total'] }} Total Shipments</div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: {{ $reports['air_share_pct'] }}%; height: 100%; background: #6366f1; border-radius: 3px;"></div>
            </div>
        </div>

        {{-- Land Share --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: #f59e0b; text-transform: uppercase;">Land Drayage Share</span>
                <i class="fa fa-truck" style="color: #f59e0b; font-size: 16px;"></i>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: #0f172a;">{{ $reports['truck_share_pct'] }}%</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $reports['truck_total'] }} of {{ $reports['grand_total'] }} Total Shipments</div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: {{ $reports['truck_share_pct'] }}%; height: 100%; background: #f59e0b; border-radius: 3px;"></div>
            </div>
        </div>

        {{-- Total Freight Invoiced --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: #10b981; text-transform: uppercase;">Platform Invoicing</span>
                <i class="fa fa-money" style="color: #10b981; font-size: 16px;"></i>
            </div>
            <div style="font-size: 32px; font-weight: 800; color: #0f172a;">{{ $reports['invoices_count'] }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">Invoices</span></div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Vol: ${{ number_format($reports['invoices_sum'] / 1000000, 2) }}M Platform Billing</div>
            <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="width: 100%; height: 100%; background: #10b981; border-radius: 3px;"></div>
            </div>
        </div>
    </div>

    {{-- Tenant Adoption Breakdown Table --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 24px;">
        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">Tenant Activity & Seat Utilization</h3>
            <span style="font-size: 11px; color: #64748b;">Multi-tenant Breakdown</span>
        </div>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 10px 16px;">Tenant Company</th>
                        <th style="padding: 10px 16px;">Country</th>
                        <th style="padding: 10px 16px;">Subdomain</th>
                        <th style="padding: 10px 16px;">Active Users</th>
                        <th style="padding: 10px 16px;">Subscription</th>
                        <th style="padding: 10px 16px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports['company_activity'] as $co)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 16px; font-weight: 700; color: #1e293b;">{{ $co->name }}</td>
                        <td style="padding: 12px 16px; color: #64748b;">{{ $co->country ?? 'Global' }}</td>
                        <td style="padding: 12px 16px; color: #3b82f6; font-weight: 600;">{{ $co->subdomain ?? 'app' }}.fms.com</td>
                        <td style="padding: 12px 16px; font-weight: 600;">{{ $co->users_count }} Seats</td>
                        <td style="padding: 12px 16px;"><span style="background: #fef3c7; color: #d97706; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 10px;">{{ ucfirst($co->subscription_status ?? 'Trial') }}</span></td>
                        <td style="padding: 12px 16px;"><span style="background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 10px;">{{ ucfirst($co->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
