@extends('super-admin.layout')

@section('content')
<div style="padding: 24px; background: #f8fafc; min-height: calc(100vh - 65px);">

    {{-- Top Title & Actions Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: linear-gradient(135deg, #059669 0%, #047857 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.25);">
                    <i class="fa fa-credit-card" style="font-size: 18px;"></i>
                </div>
                <div>
                    <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                        Freight SaaS Billing & Subscriptions
                    </h1>
                    <p style="font-size: 13px; color: #64748b; margin: 3px 0 0 0;">
                        Tenant commercial tiers, monthly recurring revenue (MRR), subscription lifecycles, and operational quotas.
                    </p>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('super-admin.modules.packages') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #405189; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <i class="fa fa-sliders"></i> Configure Plans
            </a>
            <a href="{{ route('super-admin.billing.index') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <i class="fa fa-refresh"></i> Refresh
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
        <i class="fa fa-check-circle" style="color: #059669; font-size: 16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Financial KPI Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Card 1: MRR --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Monthly Recurring (MRR)</div>
                <div style="font-size: 24px; font-weight: 800; color: #059669; margin-top: 4px;">{{ $stats['currency_symbol'] }}{{ number_format($stats['mrr']) }}</div>
                <div style="font-size: 11px; color: #059669; margin-top: 2px; font-weight: 600;">Active SaaS Inflow ({{ $stats['currency_code'] }})</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-dollar"></i>
            </div>
        </div>

        {{-- Card 2: ARR --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Annualized Run Rate</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $stats['currency_symbol'] }}{{ number_format($stats['arr']) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Forecasted 12M</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-line-chart"></i>
            </div>
        </div>

        {{-- Card 3: Paid Tenants --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Commercial Accounts</div>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ $stats['active_paid'] }} / {{ $stats['total_tenants'] }}</div>
                <div style="font-size: 11px; color: #0284c7; margin-top: 2px; font-weight: 600;">Paying Subscribers</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-building-o"></i>
            </div>
        </div>

        {{-- Card 4: Evaluation Trials --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Active Trials</div>
                <div style="font-size: 24px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ $stats['active_trials'] }}</div>
                <div style="font-size: 11px; color: #f59e0b; margin-top: 2px; font-weight: 600;">Sales Pipeline</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #fffbeb; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        
        {{-- Search & Filters --}}
        <div style="padding: 16px 20px; background: #fff; border-bottom: 1px solid #f1f5f9;">
            <form method="GET" action="{{ route('super-admin.billing.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 0;">
                <div style="flex: 1 1 240px; min-width: 200px; position: relative;">
                    <i class="fa fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search tenant organization, company code, email..." 
                           style="width: 100%; height: 36px; padding: 0 12px 0 34px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
                </div>

                <div style="flex: 0 0 auto;">
                    <select name="status" onchange="this.form.submit()" style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                        <option value="">All Subscription Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>🟢 Commercial Active</option>
                        <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>🟡 Evaluation Trial</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>🔴 Suspended</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>⚪ Expired</option>
                    </select>
                </div>

                <div style="flex: 0 0 auto;">
                    <select name="plan_id" onchange="this.form.submit()" style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                        <option value="">All Subscription Plans</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (${{ number_format($plan->price) }}/mo)
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" style="height: 36px; padding: 0 16px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    <i class="fa fa-filter"></i> Filter
                </button>

                @if(request('search') || request('status') || request('plan_id'))
                <a href="{{ route('super-admin.billing.index') }}" style="height: 36px; padding: 0 14px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa fa-times"></i> Clear
                </a>
                @endif
            </form>
        </div>

        {{-- Subscriptions Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Tenant Organization</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Assigned Plan</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Subscription Status</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Quotas & Provisioning</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Renewal / Expiry</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $comp)
                    @php
                        $assignedPlan = $plans->firstWhere('id', $comp->plan_id);
                        $statusStyles = [
                            'active'    => ['bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0', 'label' => 'Commercial Active'],
                            'trial'     => ['bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a', 'label' => 'Evaluation Trial'],
                            'suspended' => ['bg' => '#fef2f2', 'color' => '#991b1b', 'border' => '#fecaca', 'label' => 'Suspended'],
                            'expired'   => ['bg' => '#f8fafc', 'color' => '#475569', 'border' => '#e2e8f0', 'label' => 'Expired'],
                        ];
                        $st = $statusStyles[$comp->subscription_status] ?? $statusStyles['trial'];
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fbfcfd';" onmouseout="this.style.background='#fff';">
                        
                        {{-- 1. Tenant Org --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0e7ff; color: #3730a3; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center;">
                                    {{ strtoupper(substr($comp->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                        <span>{{ $comp->name }}</span>
                                        @if($comp->code)
                                        <span style="font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 4px; background: #f1f5f9; color: #475569;">
                                            {{ $comp->code }}
                                        </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        {{ $comp->email ?? 'No billing email' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- 2. Assigned Plan --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            @if($assignedPlan)
                                <div style="font-size: 12px; font-weight: 700; color: #1e293b;">
                                    {{ $assignedPlan->name }}
                                </div>
                                <div style="font-size: 11px; color: #059669; font-weight: 600; margin-top: 2px;">
                                    {{ $stats['currency_symbol'] }}{{ number_format($assignedPlan->price * ($stats['exchange_rate'] ?? 1.0)) }} / Month
                                </div>
                            @else
                                <span style="font-size: 11px; color: #94a3b8; font-style: italic;">Custom / Unassigned</span>
                            @endif
                        </td>

                        {{-- 3. Status --}}
                        <td style="padding: 14px 18px; vertical-align: top;">
                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 12px; background: {{ $st['bg'] }}; color: {{ $st['color'] }}; border: 1px solid {{ $st['border'] }}; font-size: 11px; font-weight: 700;">
                                {{ $st['label'] }}
                            </span>
                        </td>

                        {{-- 4. Quotas --}}
                        <td style="padding: 14px 18px; vertical-align: top; font-size: 11px; color: #475569;">
                            <div>
                                <i class="fa fa-users" style="color: #94a3b8; width: 14px;"></i>
                                Users: <strong>{{ $comp->users_count }}</strong> / {{ $comp->user_limit ?? 25 }} Max
                            </div>
                            <div style="margin-top: 3px;">
                                <i class="fa fa-database" style="color: #94a3b8; width: 14px;"></i>
                                Storage: {{ $comp->storage_limit ?? 50 }} GB
                            </div>
                        </td>

                        {{-- 5. Renewal --}}
                        <td style="padding: 14px 18px; vertical-align: top; font-size: 11px; color: #475569;">
                            @if($comp->subscription_ends_at)
                                <div style="font-weight: 600; color: #0f172a;">
                                    {{ $comp->subscription_ends_at->format('M d, Y') }}
                                </div>
                                <div style="font-size: 10px; color: #059669; margin-top: 2px;">
                                    {{ $comp->subscription_ends_at->diffForHumans() }}
                                </div>
                            @else
                                <span style="color: #94a3b8;">14-Day Free Evaluation</span>
                            @endif
                        </td>

                        {{-- 6. Actions --}}
                        <td style="padding: 14px 18px; vertical-align: top; text-align: center;">
                            <button type="button" onclick="openSubscriptionModal({{ $comp->id }}, '{{ addslashes($comp->name) }}', '{{ $comp->plan_id }}', '{{ $comp->subscription_status }}', '{{ $comp->user_limit }}', '{{ $comp->storage_limit }}')" 
                                    style="padding: 6px 12px; background: #fff; border: 1px solid #cbd5e1; color: #405189; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.15s;"
                                    onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#3b82f6'" onmouseout="this.style.background='#fff'; this.style.borderColor='#cbd5e1'">
                                <i class="fa fa-pencil"></i> Manage Tier
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8; font-size: 13px;">
                            No tenant organizations match the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            {{ $companies->links('pagination::simple-tailwind') }}
        </div>
        @endif

    </div>
</div>

{{-- Manage Subscription Modal --}}
<div id="subscriptionModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 480px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;" id="modalCompanyTitle">Manage Subscription Tier</h3>
            <button type="button" onclick="document.getElementById('subscriptionModal').style.display='none'" style="background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer;">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <form id="subscriptionForm" method="POST" action="" style="padding: 20px;">
            @csrf
            @method('PATCH')

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Commercial Plan</label>
                <select name="plan_id" id="modalPlanId" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                    <option value="">No Plan Assigned</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format($p->price) }}/mo)</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Subscription Status</label>
                <select name="subscription_status" id="modalStatus" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                    <option value="active">Commercial Active</option>
                    <option value="trial">Evaluation Trial</option>
                    <option value="suspended">Suspended</option>
                    <option value="expired">Expired</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">User Limit</label>
                    <input type="number" name="user_limit" id="modalUserLimit" min="1" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Storage Limit (GB)</label>
                    <input type="number" name="storage_limit" id="modalStorageLimit" min="1" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                <button type="button" onclick="document.getElementById('subscriptionModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 6px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 18px; border: none; background: #059669; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                    Save Subscription
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openSubscriptionModal(id, name, planId, status, userLimit, storageLimit) {
    document.getElementById('modalCompanyTitle').innerText = 'Manage Tier: ' + name;
    document.getElementById('subscriptionForm').action = '/super-admin/billing/' + id + '/update-subscription';
    document.getElementById('modalPlanId').value = planId || '';
    document.getElementById('modalStatus').value = status || 'active';
    document.getElementById('modalUserLimit').value = userLimit || '25';
    document.getElementById('modalStorageLimit').value = storageLimit || '50';
    document.getElementById('subscriptionModal').style.display = 'flex';
}
</script>
@endsection

