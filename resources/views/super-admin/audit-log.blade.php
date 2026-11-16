@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header --}}
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Platform Audit Trail & Security Logs</h1>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Compliance & Forensics</span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">
                Immutable event stream tracking user authentication, company lifecycle actions, and system infrastructure modifications
            </p>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; text-decoration: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                <i class="fa fa-download"></i> Export Audit CSV
            </a>
        </div>
    </div>

    {{-- Metric Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Audit Records</div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ number_format($counts['all']) }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">All platform events</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-history"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">User Activity Logs</div>
                <div style="font-size: 22px; font-weight: 800; color: #8b5cf6; margin-top: 4px;">{{ number_format($counts['user']) }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Logins, roles & credentials</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #f5f3ff; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-users"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Company Activity Logs</div>
                <div style="font-size: 22px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ number_format($counts['company']) }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Tenants, plans & branches</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-building-o"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">System Activity Logs</div>
                <div style="font-size: 22px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ number_format($counts['system']) }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Telemetry, cache & tickets</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #fffbeb; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-cogs"></i>
            </div>
        </div>
    </div>

    {{-- Dedicated Stream Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('super-admin.audit-log') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ $tab === 'all' ? '600' : '500' }}; color: {{ $tab === 'all' ? '#0ab39c' : '#64748b' }}; border-bottom: {{ $tab === 'all' ? '2px solid #0ab39c' : 'none' }}; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-list"></i> All Activity <span style="font-size: 10px; background: {{ $tab === 'all' ? '#0ab39c' : '#f1f5f9' }}; color: {{ $tab === 'all' ? '#fff' : '#64748b' }}; padding: 2px 6px; border-radius: 10px;">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('super-admin.audit-log', ['tab' => 'user']) }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ $tab === 'user' ? '600' : '500' }}; color: {{ $tab === 'user' ? '#0ab39c' : '#64748b' }}; border-bottom: {{ $tab === 'user' ? '2px solid #0ab39c' : 'none' }}; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-user-circle"></i> User Activity Logs <span style="font-size: 10px; background: {{ $tab === 'user' ? '#0ab39c' : '#f1f5f9' }}; color: {{ $tab === 'user' ? '#fff' : '#64748b' }}; padding: 2px 6px; border-radius: 10px;">{{ $counts['user'] }}</span>
        </a>
        <a href="{{ route('super-admin.audit-log', ['tab' => 'company']) }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ $tab === 'company' ? '600' : '500' }}; color: {{ $tab === 'company' ? '#0ab39c' : '#64748b' }}; border-bottom: {{ $tab === 'company' ? '2px solid #0ab39c' : 'none' }}; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-building"></i> Company Activity Logs <span style="font-size: 10px; background: {{ $tab === 'company' ? '#0ab39c' : '#f1f5f9' }}; color: {{ $tab === 'company' ? '#fff' : '#64748b' }}; padding: 2px 6px; border-radius: 10px;">{{ $counts['company'] }}</span>
        </a>
        <a href="{{ route('super-admin.audit-log', ['tab' => 'system']) }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ $tab === 'system' ? '600' : '500' }}; color: {{ $tab === 'system' ? '#0ab39c' : '#64748b' }}; border-bottom: {{ $tab === 'system' ? '2px solid #0ab39c' : 'none' }}; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-server"></i> System Activity Logs <span style="font-size: 10px; background: {{ $tab === 'system' ? '#0ab39c' : '#f1f5f9' }}; color: {{ $tab === 'system' ? '#fff' : '#64748b' }}; padding: 2px 6px; border-radius: 10px;">{{ $counts['system'] }}</span>
        </a>
    </div>

    {{-- Filter Toolbar --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('super-admin.audit-log') }}" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin: 0;">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div style="position: relative; flex: 1; min-width: 220px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by operator, action, IP, or details..."
                    style="height: 34px; width: 100%; padding: 0 10px 0 32px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                <i class="fa fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #94a3b8;"></i>
            </div>

            <select name="action" style="height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #475569; outline: none;">
                <option value="">All Action Types</option>
                @foreach($actions as $act)
                <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($act)) }}</option>
                @endforeach
            </select>

            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 11px; color: #64748b;">From:</span>
                <input type="date" name="start_date" value="{{ request('start_date') }}" style="height: 34px; padding: 0 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
                <span style="font-size: 11px; color: #64748b;">To:</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" style="height: 34px; padding: 0 8px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
            </div>

            <button type="submit" style="height: 34px; padding: 0 14px; font-size: 12px; font-weight: 600; color: #1e293b; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-filter"></i> Apply Filter
            </button>

            @if(request()->hasAny(['search', 'action', 'start_date', 'end_date']))
            <a href="{{ route('super-admin.audit-log', ['tab' => $tab]) }}" style="height: 34px; padding: 0 10px; font-size: 12px; color: #ef4444; display: inline-flex; align-items: center; text-decoration: none;" title="Reset filters">
                <i class="fa fa-times"></i> Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Audit Log Data Table --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">
                        <th style="padding: 10px 16px; width: 140px;">Timestamp</th>
                        <th style="padding: 10px 14px; width: 180px;">Operator / User</th>
                        <th style="padding: 10px 14px; width: 120px;">Category</th>
                        <th style="padding: 10px 14px; width: 160px;">Action Taken</th>
                        <th style="padding: 10px 14px; width: 100px;">Target</th>
                        <th style="padding: 10px 14px;">Audit Details</th>
                        <th style="padding: 10px 16px; width: 110px; text-align: right;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 10px 16px; color: #64748b; font-family: monospace; font-size: 11px;">
                            {{ $log->created_at ? $log->created_at->format('M d, Y H:i:s') : 'N/A' }}
                        </td>
                        <td style="padding: 10px 14px;">
                            @if($log->user)
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 26px; height: 26px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a; font-size: 12px;">{{ $log->user->name }}</div>
                                    <div style="font-size: 10px; color: #94a3b8;">{{ $log->user->email }}</div>
                                </div>
                            </div>
                            @else
                            <div style="display: flex; align-items: center; gap: 6px; color: #64748b;">
                                <i class="fa fa-server" style="color: #94a3b8;"></i>
                                <span style="font-weight: 500;">System Process</span>
                            </div>
                            @endif
                        </td>
                        <td style="padding: 10px 14px;">
                            @php
                                $type = strtolower($log->target_type ?? 'system');
                                $typeBadge = match(true) {
                                    in_array($type, ['user', 'role', 'permission']) => ['bg' => '#f5f3ff', 'color' => '#7c3aed', 'label' => 'USER & ACCESS'],
                                    in_array($type, ['company', 'branch', 'tenant']) => ['bg' => '#ecfdf5', 'color' => '#047857', 'label' => 'COMPANY'],
                                    default => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'label' => 'SYSTEM'],
                                };
                            @endphp
                            <span style="font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: {{ $typeBadge['bg'] }}; color: {{ $typeBadge['color'] }};">
                                {{ $typeBadge['label'] }}
                            </span>
                        </td>
                        <td style="padding: 10px 14px;">
                            @php
                                $actionColors = [
                                    'company_created' => '#10b981',
                                    'company_updated' => '#3b82f6',
                                    'branch_created' => '#059669',
                                    'status_changed' => '#f59e0b',
                                    'claimed_ticket' => '#6366f1',
                                    'cache_cleared' => '#0ab39c',
                                    'modules_updated' => '#8b5cf6',
                                    'password_reset' => '#ef4444',
                                ];
                                $actCol = $actionColors[$log->action] ?? '#475569';
                            @endphp
                            <span style="font-size: 11px; font-weight: 600; color: {{ $actCol }}; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa fa-circle" style="font-size: 6px;"></i>
                                {{ str_replace('_', ' ', ucfirst($log->action)) }}
                            </span>
                        </td>
                        <td style="padding: 10px 14px; font-family: monospace; font-size: 11px; color: #475569;">
                            @if(!empty($log->target_type) && !empty($log->target_id))
                                {{ ucfirst($log->target_type) }} #{{ $log->target_id }}
                            @elseif(!empty($log->target_type))
                                {{ ucfirst($log->target_type) }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding: 10px 14px; font-size: 11px; color: #475569;">
                            @if($log->details && is_array($log->details))
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($log->details as $k => $v)
                                    <span style="background: #f1f5f9; padding: 1px 6px; border-radius: 4px; font-size: 10px; color: #334155;">
                                        <strong>{{ $k }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}
                                    </span>
                                    @endforeach
                                </div>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">No metadata</span>
                            @endif
                        </td>
                        <td style="padding: 10px 16px; text-align: right; font-family: monospace; font-size: 11px; color: #94a3b8;">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8; font-size: 12px;">
                            <i class="fa fa-info-circle" style="font-size: 20px; margin-bottom: 6px; display: block;"></i>
                            No audit logs found for the selected activity stream and filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0; background: #fafafa;">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
