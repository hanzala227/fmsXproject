@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header --}}
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Dashboard</h1>
        <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">System overview and quick statistics</p>
    </div>

    {{-- Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.dashboard') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Overview</a>
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">Reports</a>
        <a href="{{ route('super-admin.dashboard.system-status') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">System Status</a>
    </div>

    {{-- Reports Content --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); text-align: center;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i class="fa fa-bar-chart" style="font-size: 24px; color: #94a3b8;"></i>
        </div>
        <h2 style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 8px;">Reports Coming Soon</h2>
        <p style="font-size: 13px; color: #64748b; max-width: 400px; margin: 0 auto;">
            Detailed usage analytics, module adoption rates, and subscription revenue reports will be available here once the relevant modules are completed.
        </p>

        <div style="display: flex; justify-content: center; gap: 24px; margin-top: 32px;">
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #1e293b; font-family: 'Oswald', sans-serif;">{{ $reports['total_migrations'] }}</div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 4px;">Data Migrations</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #1e293b; font-family: 'Oswald', sans-serif;">{{ $reports['active_tickets'] }}</div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 4px;">Active Tickets</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 24px; font-weight: 700; color: #1e293b; font-family: 'Oswald', sans-serif;">{{ $reports['open_bugs'] }}</div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 4px;">Open Bugs</div>
            </div>
        </div>
    </div>
</div>
@endsection
