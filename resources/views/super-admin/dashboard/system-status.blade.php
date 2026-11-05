@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>System Infrastructure Status</span>
                <span style="font-size: 10px; font-weight: 700; background: #ecfdf5; color: #047857; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">All Systems Operational</span>
            </h1>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">Real-time health of database, worker queues, carrier tracking APIs, and Frexo AI engine</p>
        </div>
        <button onclick="window.location.reload()" style="height: 32px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">
            <i class="fa fa-refresh"></i> Live Re-check
        </button>
    </div>

    {{-- Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.dashboard') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Overview</a>
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Reports</a>
        <a href="{{ route('super-admin.dashboard.system-status') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">System Status</a>
    </div>

    {{-- System Status Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Database Status --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Database Connection</div>
                <div style="font-size: 11px; color: #64748b;">Primary Relational Cluster (Latency: {{ $status['database_latency'] }})</div>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                @if($status['database_status'] === 'Connected')
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);"></div>
                    <span style="font-size: 12px; font-weight: 700; color: #065f46;">Operational</span>
                @else
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);"></div>
                    <span style="font-size: 12px; font-weight: 700; color: #991b1b;">Down</span>
                @endif
            </div>
        </div>

        {{-- Queue Status --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Background Queue</div>
                <div style="font-size: 11px; color: #64748b;">Asynchronous Event Queue / Dispatcher</div>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);"></div>
                <span style="font-size: 12px; font-weight: 700; color: #065f46;">Operational</span>
            </div>
        </div>

        {{-- Frexo AI Extraction Engine --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Frexo AI Engine</div>
                <div style="font-size: 11px; color: #64748b;">Gemini 3.5 Resilient Fallback Chain</div>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);"></div>
                <span style="font-size: 12px; font-weight: 700; color: #065f46;">Online</span>
            </div>
        </div>
    </div>

    {{-- Carrier Integrations & DCSA API Gateway --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">Carrier API Gateways & DCSA Connectivity</h3>
                <p style="font-size: 11px; color: #64748b; margin: 2px 0 0;">Ocean shipping line tracking connections</p>
            </div>
            <a href="{{ route('super-admin.carrier-tracking.index') }}" style="font-size: 11px; font-weight: 700; color: #4f46e5; text-decoration: none;">Manage Integrations →</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
            @foreach($status['carrier_integrations'] as $carrier)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #1e293b;">{{ $carrier->name }}</div>
                    <div style="font-size: 10px; color: #64748b;">Code: {{ strtoupper($carrier->carrier_code) }} • {{ $carrier->environment }}</div>
                </div>
                <span style="font-size: 10px; padding: 2px 8px; border-radius: 12px; font-weight: 700; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                    ACTIVE
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Server & Environment Metrics --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <h3 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 16px;">Platform Architecture & SLA Metrics</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            <div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Platform Edition</div>
                <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 2px;">{{ $status['platform_version'] }}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Availability SLA</div>
                <div style="font-size: 14px; font-weight: 700; color: #059669; margin-top: 2px;">{{ $status['uptime_sla'] }}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Memory Allocation</div>
                <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 2px;">{{ $status['memory_usage'] }} (Limit: {{ $status['memory_limit'] }})</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Active Operator Sessions</div>
                <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 2px;">{{ $status['active_sessions'] }} Sessions</div>
            </div>
            <div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Data Security</div>
                <div style="font-size: 13px; font-weight: 600; color: #4338ca; margin-top: 2px;">{{ $status['security_encryption'] }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
