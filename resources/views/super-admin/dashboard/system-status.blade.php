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
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Reports</a>
        <a href="{{ route('super-admin.dashboard.system-status') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">System Status</a>
    </div>

    {{-- System Status Content --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
        {{-- Database Status --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 4px;">Database Connection</div>
                <div style="font-size: 11px; color: #64748b;">MySQL Server</div>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                @if($status['database_status'] === 'Connected')
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);"></div>
                    <span style="font-size: 12px; font-weight: 600; color: #065f46;">Operational</span>
                @else
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);"></div>
                    <span style="font-size: 12px; font-weight: 600; color: #991b1b;">Down</span>
                @endif
            </div>
        </div>

        {{-- Queue Status --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 4px;">Background Queue</div>
                <div style="font-size: 11px; color: #64748b;">Laravel Horizon / Worker</div>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);"></div>
                <span style="font-size: 12px; font-weight: 600; color: #065f46;">Operational</span>
            </div>
        </div>

        {{-- Server Info --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; grid-column: 1 / -1;">
            <div>
                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 4px;">Server Environment</div>
                <div style="font-size: 11px; color: #64748b;">{{ $status['server_os'] }}</div>
            </div>
            <div style="display: flex; gap: 24px;">
                <div style="text-align: right;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 2px;">PHP Version</div>
                    <div style="font-size: 14px; font-weight: 600; color: #1e293b;">{{ $status['php_version'] }}</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 2px;">Laravel Version</div>
                    <div style="font-size: 14px; font-weight: 600; color: #1e293b;">v{{ $status['laravel_version'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
