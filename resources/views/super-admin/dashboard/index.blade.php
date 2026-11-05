@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header & Actions --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>Dashboard</span>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;">Freight SaaS 2.0</span>
            </h1>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">Multi-tenant freight forwarding operations, tenant health, and platform infrastructure</p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button onclick="window.location.reload()" style="height: 32px; padding: 0 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <form method="GET" action="{{ route('super-admin.dashboard') }}" style="display: flex; align-items: center; gap: 4px; margin: 0;">
                <input type="date" name="date" value="{{ $stats['selected_date'] ?? '' }}" onchange="this.form.submit()" style="height: 32px; padding: 0 10px; font-size: 11px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; cursor: pointer;">
                @if(!empty($stats['selected_date']))
                <a href="{{ route('super-admin.dashboard') }}" style="height: 32px; padding: 0 8px; display: inline-flex; align-items: center; color: #ef4444; font-size: 11px; text-decoration: none;" title="Clear date filter"><i class="fa fa-times"></i></a>
                @endif
            </form>
            <a href="{{ route('super-admin.dashboard.export') }}" style="height: 32px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #fff; background: #0ab39c; text-decoration: none; border-radius: 4px; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                <i class="fa fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.dashboard') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">Overview</a>
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">Reports</a>
        <a href="{{ route('super-admin.dashboard.system-status') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">System Status</a>
    </div>

    {{-- Platform Tenant KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 20px;">
        @php
            $cards = [
                ['label' => 'Total Companies', 'value' => $stats['total_companies'], 'icon' => 'fa-building', 'color' => '#3b82f6', 'bg' => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)', 'sub' => 'Freight Tenants'],
                ['label' => 'Active Tenants', 'value' => $stats['active_companies'], 'icon' => 'fa-check-circle', 'color' => '#10b981', 'bg' => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)', 'sub' => '100% Operational'],
                ['label' => 'Trial Accounts', 'value' => $stats['trial_companies'], 'icon' => 'fa-clock-o', 'color' => '#f59e0b', 'bg' => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)', 'sub' => 'Active Trials'],
                ['label' => 'Suspended', 'value' => $stats['suspended_companies'], 'icon' => 'fa-ban', 'color' => '#ef4444', 'bg' => 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)', 'sub' => 'Zero Lockouts'],
                ['label' => 'Total Users', 'value' => $stats['total_users'], 'icon' => 'fa-users', 'color' => '#8b5cf6', 'bg' => 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%)', 'sub' => 'Across All Tenants'],
                ['label' => 'Open Bugs', 'value' => $stats['open_bugs'], 'icon' => 'fa-bug', 'color' => '#f43f5e', 'bg' => 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)', 'sub' => 'Active In System'],
                ['label' => 'Support Tickets', 'value' => $stats['support_tickets'], 'icon' => 'fa-life-ring', 'color' => '#6366f1', 'bg' => 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%)', 'sub' => 'Pending Review'],
                ['label' => 'Monthly Revenue', 'value' => ($stats['currency_symbol'] ?? '$') . number_format($stats['monthly_revenue']), 'icon' => 'fa-line-chart', 'color' => '#059669', 'bg' => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)', 'sub' => 'MRR (' . ($stats['currency_code'] ?? 'USD') . ')'],
            ];
        @endphp

        @foreach($cards as $card)
        <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 14px; padding: 18px 20px; display: flex; align-items: flex-start; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.25s ease; position: relative; overflow: hidden;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.05)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'">
            <div style="position: absolute; top: -15px; right: -15px; width: 90px; height: 90px; background: {{ $card['bg'] }}; border-radius: 50%; opacity: 0.5; z-index: 0; filter: blur(18px);"></div>
            <div style="z-index: 1;">
                <div style="font-size: 11px; color: #64748b; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $card['label'] }}</div>
                <div style="font-size: 28px; font-weight: 800; color: #0f172a; font-family: 'Inter', sans-serif; letter-spacing: -0.5px; line-height: 1;">{{ $card['value'] }}</div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 6px; font-weight: 500;">{{ $card['sub'] }}</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ $card['bg'] }}; display: flex; align-items: center; justify-content: center; z-index: 1; border: 1px solid rgba(255,255,255,0.7); box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
                <i class="fa {{ $card['icon'] }}" style="font-size: 18px; color: {{ $card['color'] }};"></i>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FREIGHT STANDARD OPERATIONS PULSE (Platform-Wide Logistics Activity) --}}
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 16px; padding: 20px 24px; color: #fff; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center; color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4);">
                    <i class="fa fa-globe" style="font-size: 16px;"></i>
                </div>
                <div>
                    <h2 style="font-size: 14px; font-weight: 700; margin: 0; color: #fff; letter-spacing: 0.5px; text-transform: uppercase;">Freight Operations Pulse (All Tenants)</h2>
                    <p style="font-size: 11px; color: #94a3b8; margin: 2px 0 0;">Live platform-wide logistics throughput across multimodal freight corridors</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 11px; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 10px; border-radius: 20px; font-weight: 600; border: 1px solid rgba(16, 185, 129, 0.3);">
                    <i class="fa fa-circle" style="font-size: 8px; margin-right: 4px;"></i> Carrier APIs Active ({{ $stats['active_carrier_apis'] }})
                </span>
                <span style="font-size: 11px; background: rgba(139, 92, 246, 0.2); color: #c084fc; padding: 4px 10px; border-radius: 20px; font-weight: 600; border: 1px solid rgba(139, 92, 246, 0.3);">
                    <i class="fa fa-bolt" style="margin-right: 4px;"></i> Frexo AI Resilient
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            {{-- Ocean Freight --}}
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px;">Ocean Freight</span>
                    <i class="fa fa-ship" style="color: #38bdf8;"></i>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #fff;">{{ $stats['ocean_shipments'] }} <span style="font-size: 11px; font-weight: 500; color: #94a3b8;">Shipments</span></div>
                <div style="font-size: 10px; color: #cbd5e1; margin-top: 6px; display: flex; justify-content: space-between;">
                    <span>Containers: <strong>{{ $stats['ocean_containers'] }} TEU</strong></span>
                    <span>HBLs: <strong>{{ $stats['ocean_hbls'] }}</strong></span>
                </div>
            </div>

            {{-- Air Cargo --}}
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: #a78bfa; text-transform: uppercase; letter-spacing: 0.5px;">Air Cargo</span>
                    <i class="fa fa-plane" style="color: #a78bfa;"></i>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #fff;">{{ $stats['air_shipments'] }} <span style="font-size: 11px; font-weight: 500; color: #94a3b8;">Shipments</span></div>
                <div style="font-size: 10px; color: #cbd5e1; margin-top: 6px; display: flex; justify-content: space-between;">
                    <span>HAWBs: <strong>{{ $stats['air_hawbs'] }}</strong></span>
                    <span>IATA Standard: <strong>1:6000</strong></span>
                </div>
            </div>

            {{-- Land Transport --}}
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.5px;">Land Transport</span>
                    <i class="fa fa-truck" style="color: #fbbf24;"></i>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #fff;">{{ $stats['truck_shipments'] }} <span style="font-size: 11px; font-weight: 500; color: #94a3b8;">Shipments</span></div>
                <div style="font-size: 10px; color: #cbd5e1; margin-top: 6px;">
                    <span>Cross-Border & Drayage Dispatch</span>
                </div>
            </div>

            {{-- Total Movements --}}
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: #34d399; text-transform: uppercase; letter-spacing: 0.5px;">Total Movements</span>
                    <i class="fa fa-exchange" style="color: #34d399;"></i>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #fff;">{{ $stats['total_freight_movements'] }} <span style="font-size: 11px; font-weight: 500; color: #94a3b8;">Jobs</span></div>
                <div style="font-size: 10px; color: #cbd5e1; margin-top: 6px;">
                    <span>Multimodal Cloud Architecture</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts and Primary Tables Grid --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
        {{-- Real Dynamic Freight & Company Growth Charts --}}
        <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); display: flex; flex-direction: column;">
            <div style="padding: 20px 24px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">Freight Volume & Tenant Growth</h3>
                    <p style="margin: 4px 0 0; font-size: 12px; color: #64748b; font-weight: 500;">Real monthly operations recorded across the platform (Last 6 Months)</p>
                </div>
                <div style="display: flex; align-items: center; gap: 14px; font-size: 11px; font-weight: 600;">
                    <span style="display: flex; align-items: center; gap: 6px; color: #0284c7;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: #0284c7;"></span> Ocean Shipments
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px; color: #6366f1;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: #6366f1;"></span> Air Cargo
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px; color: #10b981;">
                        <span style="width: 10px; height: 10px; border-radius: 2px; background: #10b981;"></span> New Tenants
                    </span>
                </div>
            </div>

            <div style="padding: 32px 32px 20px 32px; flex: 1; display: flex; flex-direction: column; justify-content: flex-end;">
                @php
                    $oceanData = $stats['freight_trends']['ocean'];
                    $airData = $stats['freight_trends']['air'];
                    $coData = $stats['monthly_growth']['data'];
                    $maxVolume = max(max($oceanData), max($airData), max($coData), 10);
                @endphp

                <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; height: 260px; position: relative;">
                    <!-- Grid lines -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; border-top: 1px dashed #e2e8f0;"></div>
                    <div style="position: absolute; top: 33%; left: 0; right: 0; border-top: 1px dashed #e2e8f0;"></div>
                    <div style="position: absolute; top: 66%; left: 0; right: 0; border-top: 1px dashed #e2e8f0;"></div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #cbd5e1;"></div>

                    @foreach($stats['monthly_growth']['labels'] as $idx => $mLabel)
                    @php
                        $oVal = $oceanData[$idx] ?? 0;
                        $aVal = $airData[$idx] ?? 0;
                        $cVal = $coData[$idx] ?? 0;
                    @endphp
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 12px; z-index: 1; position: relative;">
                        <!-- Multi-bar group -->
                        <div style="display: flex; align-items: flex-end; gap: 4px; height: 200px; width: 100%; justify-content: center;">
                            <!-- Ocean Bar -->
                            <div title="Ocean Shipments: {{ $oVal }}" style="width: 18px; background: linear-gradient(180deg, #38bdf8 0%, #0284c7 100%); border-radius: 4px 4px 0 0; height: {{ max(($oVal / $maxVolume) * 200, 4) }}px; transition: height 0.4s ease; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);"></div>
                            <!-- Air Bar -->
                            <div title="Air Shipments: {{ $aVal }}" style="width: 18px; background: linear-gradient(180deg, #818cf8 0%, #6366f1 100%); border-radius: 4px 4px 0 0; height: {{ max(($aVal / $maxVolume) * 200, 4) }}px; transition: height 0.4s ease; box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);"></div>
                            <!-- Company Bar -->
                            <div title="New Tenants: {{ $cVal }}" style="width: 14px; background: linear-gradient(180deg, #34d399 0%, #10b981 100%); border-radius: 4px 4px 0 0; height: {{ max(($cVal / $maxVolume) * 200, 4) }}px; transition: height 0.4s ease; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);"></div>
                        </div>
                        <span style="font-size: 12px; font-weight: 700; color: #475569;">{{ $mLabel }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right Column: Recent Companies & Open Tickets --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Recent Freight Forwarder Tenants --}}
            <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                <div style="padding: 16px 20px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                    <span style="font-size: 14px; font-weight: 700; color: #0f172a;">Recent Tenants</span>
                    <a href="{{ route('super-admin.companies.index') }}" style="font-size: 11px; color: #6366f1; text-decoration: none; font-weight: 700; background: #eef2ff; padding: 3px 8px; border-radius: 12px;">View All →</a>
                </div>
                <div style="padding: 0;">
                    @forelse($stats['recent_companies'] as $company)
                    <div style="padding: 12px 18px; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; border: 1px solid #c7d2fe;">
                                {{ strtoupper(substr($company->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">{{ $company->name }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $company->users_count ?? 1 }} Users • {{ $company->created_at?->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <span style="font-size: 10px; padding: 3px 8px; border-radius: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                            {{ $company->status === 'active' ? 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;' : 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;' }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </div>
                    @empty
                    <div style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">No companies yet</div>
                    @endforelse
                </div>
            </div>

            {{-- Open Support & Bugs Quick Resolution --}}
            <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                <div style="padding: 16px 20px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                    <span style="font-size: 14px; font-weight: 700; color: #0f172a;">Active Tickets & Bugs</span>
                    <a href="{{ route('super-admin.support-center.index') }}" style="font-size: 11px; color: #6366f1; text-decoration: none; font-weight: 700; background: #eef2ff; padding: 3px 8px; border-radius: 12px;">Manage →</a>
                </div>
                <div style="padding: 0;">
                    @forelse($stats['open_tickets_list'] as $ticket)
                    <div style="padding: 12px 18px; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: #1e293b;">
                                <a href="{{ route('super-admin.support-center.show', $ticket->id) }}" style="color: #1e293b; text-decoration: none;">
                                    #{{ $ticket->id }} - {{ $ticket->title }}
                                </a>
                            </div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                {{ $ticket->company->name ?? 'System' }} • {{ $ticket->module_name ?? 'General' }}
                            </div>
                        </div>
                        <span style="font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700; text-transform: uppercase;
                            {{ $ticket->priority === 'High' ? 'background: #fee2e2; color: #ef4444;' : 'background: #fef3c7; color: #d97706;' }}">
                            {{ $ticket->priority ?? 'Normal' }}
                        </span>
                    </div>
                    @empty
                    <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px;">Zero open issues</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row: Live Freight Activity Feed & System Audit Logs --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        {{-- Live Platform Freight Shipments --}}
        <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
            <div style="padding: 16px 20px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                <span style="font-size: 14px; font-weight: 700; color: #0f172a;"><i class="fa fa-cubes text-blue-500"></i> Recent Freight Movements</span>
                <span style="font-size: 11px; color: #64748b; font-weight: 500;">Live Feed</span>
            </div>
            <div style="padding: 0;">
                @forelse($stats['recent_shipments'] as $shipment)
                <div style="padding: 12px 18px; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 30px; height: 30px; border-radius: 8px; background: {{ $shipment->mode_color }}15; color: {{ $shipment->mode_color }}; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                            <i class="fa {{ $shipment->mode_icon }}"></i>
                        </div>
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: #1e293b;">{{ $shipment->ref_no ?: 'No Ref #' }}</div>
                            <div style="font-size: 10px; color: #64748b;">{{ $shipment->company_name ?? 'Tenant' }} • {{ $shipment->mode }} ({{ $shipment->ship_mode ?? 'FCL' }})</div>
                        </div>
                    </div>
                    <span style="font-size: 10px; color: #94a3b8; font-weight: 500;">
                        {{ \Carbon\Carbon::parse($shipment->created_at)->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px;">No freight shipments recorded yet</div>
                @endforelse
            </div>
        </div>

        {{-- System Audit Logs --}}
        <div style="background: #fff; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
            <div style="padding: 16px 20px; border-bottom: 1px solid rgba(226, 232, 240, 0.5); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                <span style="font-size: 14px; font-weight: 700; color: #0f172a;"><i class="fa fa-shield text-indigo-500"></i> Platform Security & Audit Logs</span>
                <a href="{{ route('super-admin.audit-log') }}" style="font-size: 11px; color: #6366f1; text-decoration: none; font-weight: 700; background: #eef2ff; padding: 3px 8px; border-radius: 12px;">Full Log →</a>
            </div>
            <div style="padding: 0;">
                @forelse($stats['recent_logs']->take(5) as $log)
                <div style="padding: 12px 18px; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: #6366f1;"></div>
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: #1e293b;">{{ str_replace('_', ' ', ucfirst($log->action)) }}</div>
                            <div style="font-size: 10px; color: #64748b;">By {{ $log->user->name ?? 'System Admin' }}</div>
                        </div>
                    </div>
                    <span style="font-size: 10px; color: #94a3b8; font-weight: 500;">
                        {{ $log->created_at?->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px;">No security logs recorded yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', function() { showToast('success', '{{ session('success') }}'); });</script>
@endif
@endsection
