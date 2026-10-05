@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header & Actions --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Dashboard</h1>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">System overview and quick statistics</p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button onclick="window.location.reload()" style="height: 32px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <div style="position: relative;">
                <input type="date" style="height: 32px; padding: 0 12px; font-size: 11px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; cursor: pointer;">
            </div>
            <button onclick="alert('Exporting data...')" style="height: 32px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; color: #fff; background: #0ab39c; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fa fa-download"></i> Export
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.dashboard') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">Overview</a>
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Reports</a>
        <a href="{{ route('super-admin.dashboard.system-status') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">System Status</a>
    </div>

    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 32px;">
        @php
            $cards = [
                ['label' => 'Total Companies', 'value' => $stats['total_companies'], 'icon' => 'fa-building', 'color' => '#3b82f6', 'bg' => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)'],
                ['label' => 'Active', 'value' => $stats['active_companies'], 'icon' => 'fa-check-circle', 'color' => '#10b981', 'bg' => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)'],
                ['label' => 'Trial', 'value' => $stats['trial_companies'], 'icon' => 'fa-clock-o', 'color' => '#f59e0b', 'bg' => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)'],
                ['label' => 'Suspended', 'value' => $stats['suspended_companies'], 'icon' => 'fa-ban', 'color' => '#ef4444', 'bg' => 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)'],
                ['label' => 'Total Users', 'value' => $stats['total_users'], 'icon' => 'fa-users', 'color' => '#8b5cf6', 'bg' => 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%)'],
                ['label' => 'Open Bugs', 'value' => $stats['open_bugs'], 'icon' => 'fa-bug', 'color' => '#f43f5e', 'bg' => 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)'],
                ['label' => 'Support Tickets', 'value' => $stats['support_tickets'], 'icon' => 'fa-life-ring', 'color' => '#6366f1', 'bg' => 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%)'],
                ['label' => 'Monthly Revenue', 'value' => '$' . number_format($stats['monthly_revenue']), 'icon' => 'fa-line-chart', 'color' => '#059669', 'bg' => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)'],
            ];
        @endphp

        @foreach($cards as $card)
        <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; padding: 24px; display: flex; align-items: flex-start; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; cursor: default;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02)'">
            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: {{ $card['bg'] }}; border-radius: 50%; opacity: 0.6; z-index: 0; filter: blur(24px);"></div>
            <div style="z-index: 1;">
                <div style="font-size: 13px; color: #64748b; font-weight: 700; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $card['label'] }}</div>
                <div style="font-size: 36px; font-weight: 800; color: #0f172a; font-family: 'Inter', sans-serif; letter-spacing: -1px; line-height: 1;">{{ $card['value'] }}</div>
            </div>
            <div style="width: 52px; height: 52px; border-radius: 16px; background: {{ $card['bg'] }}; display: flex; align-items: center; justify-content: center; z-index: 1; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5), 0 4px 6px rgba(0,0,0,0.05);">
                <i class="fa {{ $card['icon'] }}" style="font-size: 22px; color: {{ $card['color'] }};"></i>
            </div>
        </div>
        @endforeach
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        {{-- Charts / Analytics --}}
        <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); display: flex; flex-direction: column; align-self: flex-start;">
            <div style="padding: 24px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Company Growth</h3>
                    <p style="margin: 4px 0 0; font-size: 13px; color: #64748b; font-weight: 500;">Last 6 months performance</p>
                </div>
            </div>
            <div style="padding: 32px 32px 16px 32px; flex: 1; display: flex; flex-direction: column; justify-content: flex-end;">
                {{-- Premium CSS Chart representation --}}
                <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; height: 260px; position: relative;">
                    <!-- Grid lines -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; border-top: 1px dashed #cbd5e1; opacity: 0.5;"></div>
                    <div style="position: absolute; top: 33%; left: 0; right: 0; border-top: 1px dashed #cbd5e1; opacity: 0.5;"></div>
                    <div style="position: absolute; top: 66%; left: 0; right: 0; border-top: 1px dashed #cbd5e1; opacity: 0.5;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #cbd5e1;"></div>
                    
                    @php 
                        $max = max($stats['monthly_growth']['data']) ?: 1;
                    @endphp
                    @foreach($stats['monthly_growth']['data'] as $index => $value)
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 16px; z-index: 1; position: relative;" onmouseover="this.querySelector('.tooltip').style.opacity=1; this.querySelector('.bar').style.background='linear-gradient(180deg, #818cf8 0%, #6366f1 100%)'; this.querySelector('.bar').style.transform='scaleY(1.03)'" onmouseout="this.querySelector('.tooltip').style.opacity=0; this.querySelector('.bar').style.background='linear-gradient(180deg, #6366f1 0%, #4f46e5 100%)'; this.querySelector('.bar').style.transform='scaleY(1)'">
                        <div class="tooltip" style="position: absolute; top: -35px; background: #0f172a; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; opacity: 0; transition: opacity 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); pointer-events: none; z-index: 10;">
                            {{ $value }}
                            <div style="position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); border-width: 4px 4px 0; border-style: solid; border-color: #0f172a transparent transparent transparent;"></div>
                        </div>
                        <div class="bar" style="width: 100%; max-width: 64px; background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%); border-radius: 8px 8px 0 0; height: {{ max(($value / $max) * 200, 4) }}px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3); transform-origin: bottom;">
                        </div>
                        <span style="font-size: 13px; font-weight: 600; color: #475569;">{{ $stats['monthly_growth']['labels'][$index] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 24px;">
            {{-- Recent Companies --}}
            <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                <div style="padding: 20px 24px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                    <span style="font-size: 16px; font-weight: 700; color: #0f172a;">Recent Companies</span>
                    <a href="{{ route('super-admin.companies.index') }}" style="font-size: 12px; color: #6366f1; text-decoration: none; font-weight: 700; background: #eef2ff; padding: 4px 10px; border-radius: 20px; transition: background 0.2s;" onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">View All →</a>
                </div>
                <div style="padding: 0;">
                    @forelse($stats['recent_companies'] as $company)
                    <div style="padding: 16px 24px; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; border: 1px solid #c7d2fe;">
                                {{ substr($company->name, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 3px;">{{ $company->name }}</div>
                                <div style="font-size: 12px; color: #64748b; font-weight: 500;">Joined {{ $company->created_at?->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <span style="font-size: 11px; padding: 4px 12px; border-radius: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                            {{ $company->status === 'active' ? 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;' : 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;' }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </div>
                    @empty
                    <div style="padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; font-weight: 500;">No companies yet</div>
                    @endforelse
                </div>
            </div>
            
            {{-- Recent Activity Summary --}}
            <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                <div style="padding: 20px 24px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                    <span style="font-size: 16px; font-weight: 700; color: #0f172a;">System Activity</span>
                    <a href="{{ route('super-admin.audit-log') }}" style="font-size: 12px; color: #6366f1; text-decoration: none; font-weight: 700; background: #eef2ff; padding: 4px 10px; border-radius: 20px; transition: background 0.2s;" onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">View Logs →</a>
                </div>
                <div style="padding: 0;">
                    @forelse($stats['recent_logs']->take(4) as $log)
                    <div style="padding: 16px 24px; border-bottom: 1px solid #f8fafc; display: flex; gap: 16px; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <div style="margin-top: 2px;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background: #6366f1; border: 2px solid #e0e7ff; box-shadow: 0 0 0 2px #fff;"></div>
                        </div>
                        <div>
                            <div style="font-size: 13px; color: #1e293b; font-weight: 700; line-height: 1.4; margin-bottom: 2px;">{{ str_replace('_', ' ', ucfirst($log->action)) }}</div>
                            <div style="font-size: 12px; color: #64748b; font-weight: 500;">
                                By <span style="color: #475569; font-weight: 600;">{{ $log->user->name ?? 'System' }}</span> • {{ $log->created_at?->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="padding: 40px; text-align: center; color: #94a3b8; font-size: 14px; font-weight: 500;">No activity yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', function() { showToast('success', '{{ session('success') }}'); });</script>
@endif
@endsection
