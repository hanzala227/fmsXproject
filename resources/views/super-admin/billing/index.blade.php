@extends('super-admin.layout')

@section('content')
<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 65px);">

    {{-- Top Title & Actions Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
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
                        Tenant commercial tiers, monthly recurring revenue (MRR), subscription lifecycles, and operational quotas
                    </p>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; background: #0ab39c; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; transition: background 0.15s;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                <i class="fa fa-download"></i> Export Billing CSV
            </a>
            <a href="{{ route('super-admin.modules.packages') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #405189; text-decoration: none;">
                <i class="fa fa-sliders"></i> Configure Packages
            </a>
            <a href="{{ route('super-admin.billing.index') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 12px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; text-decoration: none;">
                <i class="fa fa-refresh"></i> Refresh
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-check-circle" style="color: #059669; margin-right: 6px;"></i> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- Sub-Navigation Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('super-admin.billing.index', ['tab' => 'subscriptions']) }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ $tab !== 'invoices' ? '600' : '500' }}; color: {{ $tab !== 'invoices' ? '#0ab39c' : '#64748b' }}; border-bottom: {{ $tab !== 'invoices' ? '2px solid #0ab39c' : 'none' }}; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-credit-card"></i> Tenant Subscriptions <span style="font-size: 10px; background: {{ $tab !== 'invoices' ? '#0ab39c' : '#f1f5f9' }}; color: {{ $tab !== 'invoices' ? '#fff' : '#64748b' }}; padding: 2px 6px; border-radius: 10px;">{{ $stats['total_tenants'] }}</span>
        </a>
        <a href="{{ route('super-admin.modules.packages') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-cubes"></i> Commercial Packages <span style="font-size: 10px; background: #f1f5f9; color: #64748b; padding: 2px 6px; border-radius: 10px;">{{ $plans->count() }}</span>
        </a>
        <a href="{{ route('super-admin.billing.index', ['tab' => 'invoices']) }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: {{ $tab === 'invoices' ? '600' : '500' }}; color: {{ $tab === 'invoices' ? '#0ab39c' : '#64748b' }}; border-bottom: {{ $tab === 'invoices' ? '2px solid #0ab39c' : 'none' }}; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-file-text-o"></i> Tenant Freight Invoices
        </a>
    </div>

    {{-- Financial KPI Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Card 1: MRR --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Monthly Recurring (MRR)</div>
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
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Annualized Run Rate (ARR)</div>
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $stats['currency_symbol'] }}{{ number_format($stats['arr']) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Forecasted 12-Month Inflow</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-line-chart"></i>
            </div>
        </div>

        {{-- Card 3: Paid Tenants --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Commercial Tenants</div>
                <div style="font-size: 24px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ $stats['active_paid'] }} / {{ $stats['total_tenants'] }}</div>
                <div style="font-size: 11px; color: #0284c7; margin-top: 2px; font-weight: 600;">Paying Subscribers</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-building-o"></i>
            </div>
        </div>

        {{-- Card 4: Evaluation Trials & Suspended --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Trials / Suspended</div>
                <div style="font-size: 24px; font-weight: 800; color: #f59e0b; margin-top: 4px;">{{ $stats['active_trials'] }} <span style="font-size: 14px; color: #94a3b8;">/ {{ $stats['suspended_tenants'] }}</span></div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ $stats['active_trials'] }} Active Trials • {{ $stats['suspended_tenants'] }} Suspended</div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #fffbeb; color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
    </div>

    @if($tab === 'invoices')
    {{-- Invoices Table View --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="padding: 16px 20px; background: #fff; border-bottom: 1px solid #f1f5f9;">
            <form method="GET" action="{{ route('super-admin.billing.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 0;">
                <input type="hidden" name="tab" value="invoices">

                <div style="flex: 1 1 240px; min-width: 200px; position: relative;">
                    <i class="fa fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search invoice number or company name..." 
                           style="width: 100%; height: 36px; padding: 0 12px 0 34px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                </div>

                <select name="invoice_status" onchange="this.form.submit()" style="height: 36px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;">
                    <option value="">All Invoice Statuses</option>
                    <option value="PAID" {{ request('invoice_status') === 'PAID' ? 'selected' : '' }}>PAID</option>
                    <option value="POSTED" {{ request('invoice_status') === 'POSTED' ? 'selected' : '' }}>POSTED</option>
                    <option value="CANCELLED" {{ request('invoice_status') === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                </select>

                <button type="submit" style="height: 36px; padding: 0 16px; background: #405189; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    <i class="fa fa-filter"></i> Filter
                </button>

                @if(request('search') || request('invoice_status'))
                <a href="{{ route('super-admin.billing.index', ['tab' => 'invoices']) }}" style="height: 36px; padding: 0 12px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa fa-times"></i> Clear
                </a>
                @endif
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">
                        <th style="padding: 12px 18px;">Invoice #</th>
                        <th style="padding: 12px 18px;">Tenant Forwarder</th>
                        <th style="padding: 12px 18px;">Date</th>
                        <th style="padding: 12px 18px;">Total Amount</th>
                        <th style="padding: 12px 18px;">Paid</th>
                        <th style="padding: 12px 18px;">Balance</th>
                        <th style="padding: 12px 18px; text-align: right;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if($invoices && $invoices->count() > 0)
                        @foreach($invoices as $inv)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 18px; font-family: monospace; font-weight: 700; color: #1e293b;">
                                {{ $inv->invoice_no }}
                            </td>
                            <td style="padding: 12px 18px; font-weight: 600; color: #0f172a;">
                                {{ $inv->company_name ?? 'Tenant #' . $inv->id }}
                            </td>
                            <td style="padding: 12px 18px; color: #64748b;">
                                {{ $inv->invoice_date ? date('M d, Y', strtotime($inv->invoice_date)) : 'N/A' }}
                            </td>
                            <td style="padding: 12px 18px; font-weight: 700; color: #0f172a;">
                                ${{ number_format((float) $inv->total_amount, 2) }}
                            </td>
                            <td style="padding: 12px 18px; color: #10b981; font-weight: 600;">
                                ${{ number_format((float) $inv->paid_amount, 2) }}
                            </td>
                            <td style="padding: 12px 18px; color: #64748b;">
                                ${{ number_format((float) $inv->balance_amount, 2) }}
                            </td>
                            <td style="padding: 12px 18px; text-align: right;">
                                <span style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; {{ $inv->status === 'PAID' ? 'background: #dcfce7; color: #166534;' : ($inv->status === 'POSTED' ? 'background: #eff6ff; color: #1d4ed8;' : 'background: #fee2e2; color: #991b1b;') }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8;">No invoices found matching criteria.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($invoices && $invoices->hasPages())
        <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

    @else
    {{-- Subscriptions Table View (Default) --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
        
        {{-- Search & Filters --}}
        <div style="padding: 16px 20px; background: #fff; border-bottom: 1px solid #f1f5f9;">
            <form method="GET" action="{{ route('super-admin.billing.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 0;">
                <input type="hidden" name="tab" value="subscriptions">

                <div style="flex: 1 1 240px; min-width: 200px; position: relative;">
                    <i class="fa fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search tenant organization, company code, email..." 
                           style="width: 100%; height: 36px; padding: 0 12px 0 34px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
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
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Commercial Plan</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Subscription Status</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Quotas & AI</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Renewal / Lifecycle</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody style="divide-y divide-gray-100;">
                    @forelse($companies as $comp)
                    @php
                        $assignedPlan = $comp->plan ?? ($plans->firstWhere('id', $comp->plan_id));
                        $statusMap = [
                            'active'    => ['label' => 'Active', 'bg' => '#ecfdf5', 'color' => '#047857', 'border' => '#a7f3d0'],
                            'trial'     => ['label' => 'Trial', 'bg' => '#fffbeb', 'color' => '#b45309', 'border' => '#fde68a'],
                            'suspended' => ['label' => 'Suspended', 'bg' => '#fef2f2', 'color' => '#b91c1c', 'border' => '#fecaca'],
                            'expired'   => ['label' => 'Expired', 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1'],
                        ];
                        $st = $statusMap[$comp->subscription_status ?? 'trial'] ?? $statusMap['trial'];
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        {{-- 1. Tenant Org --}}
                        <td style="padding: 14px 18px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; border: 1px solid #e2e8f0;">
                                    {{ strtoupper(substr($comp->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 700; color: #0f172a;">
                                        {{ $comp->name }}
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 1px;">
                                        Code: <strong>{{ $comp->code }}</strong> • {{ $comp->country ?? 'Global' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- 2. Assigned Plan --}}
                        <td style="padding: 14px 18px; vertical-align: middle;">
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
                        <td style="padding: 14px 18px; vertical-align: middle;">
                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 12px; background: {{ $st['bg'] }}; color: {{ $st['color'] }}; border: 1px solid {{ $st['border'] }}; font-size: 11px; font-weight: 700;">
                                {{ $st['label'] }}
                            </span>
                        </td>

                        {{-- 4. Quotas & AI --}}
                        <td style="padding: 14px 18px; vertical-align: middle; font-size: 11px; color: #475569;">
                            <div>
                                <i class="fa fa-users" style="color: #94a3b8; width: 14px;"></i>
                                Users: <strong>{{ $comp->users_count }}</strong> / {{ $comp->user_limit ?? 25 }} Max
                            </div>
                            <div style="margin-top: 2px;">
                                <i class="fa fa-database" style="color: #94a3b8; width: 14px;"></i>
                                Storage: {{ $comp->storage_limit ?? 50 }} GB
                            </div>
                            @if($comp->frexo_ai_unlimited)
                            <div style="margin-top: 2px;">
                                <span style="font-size: 10px; font-weight: 700; background: #f3e8ff; color: #7e22ce; padding: 1px 6px; border-radius: 4px;">
                                    <i class="fa fa-bolt"></i> AI UNLIMITED
                                </span>
                            </div>
                            @endif
                        </td>

                        {{-- 5. Renewal --}}
                        <td style="padding: 14px 18px; vertical-align: middle; font-size: 11px; color: #475569;">
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
                        <td style="padding: 14px 18px; vertical-align: middle; text-align: right;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <button type="button" onclick="openSubscriptionModal({{ $comp->id }}, '{{ addslashes($comp->name) }}', '{{ $comp->plan_id }}', '{{ $comp->subscription_status }}', '{{ $comp->user_limit }}', '{{ $comp->storage_limit }}', '{{ $comp->subscription_ends_at ? $comp->subscription_ends_at->format('Y-m-d') : '' }}', {{ $comp->frexo_ai_unlimited ? 'true' : 'false' }}, '{{ $comp->frexo_ai_credits ?? 0 }}')" 
                                        style="padding: 6px 12px; background: #fff; border: 1px solid #cbd5e1; color: #405189; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.15s;"
                                        onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#3b82f6'" onmouseout="this.style.background='#fff'; this.style.borderColor='#cbd5e1'">
                                    <i class="fa fa-pencil"></i> Manage Tier
                                </button>

                                <form method="POST" action="{{ route('super-admin.billing.toggle-status', $comp) }}" onsubmit="return confirm('Change status for {{ addslashes($comp->name) }}?');" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    @if($comp->subscription_status === 'suspended')
                                    <button type="submit" style="padding: 6px 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;" title="Activate Tenant">
                                        <i class="fa fa-play"></i> Activate
                                    </button>
                                    @else
                                    <button type="submit" style="padding: 6px 10px; background: #fff; border: 1px solid #fecaca; color: #dc2626; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;" title="Suspend Tenant">
                                        <i class="fa fa-ban"></i> Suspend
                                    </button>
                                    @endif
                                </form>
                            </div>
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
            {{ $companies->links() }}
        </div>
        @endif

    </div>
    @endif
</div>

{{-- Enhanced Manage Subscription Modal --}}
<div id="subscriptionModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden;">
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
                <select name="plan_id" id="modalPlanId" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                    <option value="">No Plan Assigned (Custom Quotas)</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format($p->price) }}/mo)</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Subscription Status</label>
                <select name="subscription_status" id="modalStatus" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff; box-sizing: border-box;">
                    <option value="active">🟢 Commercial Active</option>
                    <option value="trial">🟡 Evaluation Trial</option>
                    <option value="suspended">🔴 Suspended</option>
                    <option value="expired">⚪ Expired</option>
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

            {{-- Expiry Date & Quick Extend --}}
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">Subscription Expiry Date</label>
                <input type="date" name="subscription_ends_at" id="modalEndsAt" style="width: 100%; height: 36px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;">
                
                <div style="display: flex; gap: 6px; margin-top: 6px;">
                    <button type="button" onclick="extendDays(30)" style="padding: 3px 8px; font-size: 10px; font-weight: 600; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; cursor: pointer;">+30 Days</button>
                    <button type="button" onclick="extendDays(90)" style="padding: 3px 8px; font-size: 10px; font-weight: 600; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; cursor: pointer;">+90 Days</button>
                    <button type="button" onclick="extendDays(365)" style="padding: 3px 8px; font-size: 10px; font-weight: 600; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; cursor: pointer;">+1 Year</button>
                </div>
            </div>

            {{-- Frexo AI Engine Controls --}}
            <div style="padding: 12px 14px; background: #fdf4ff; border: 1px solid #f0abfc; border-radius: 8px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <label style="font-size: 12px; font-weight: 700; color: #86198f; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="checkbox" name="frexo_ai_unlimited" id="modalAiUnlimited" value="1" style="width: 15px; height: 15px;">
                        <span>Grant Unlimited AI Engine Access</span>
                    </label>
                </div>
                <div>
                    <label style="display: block; font-size: 10px; font-weight: 700; color: #86198f; text-transform: uppercase; margin-bottom: 4px;">Top-Up AI Credits</label>
                    <input type="number" name="frexo_ai_credits" id="modalAiCredits" min="0" placeholder="0" style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #e879f9; border-radius: 4px; outline: none; background: #fff; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                <button type="button" onclick="document.getElementById('subscriptionModal').style.display='none'" style="height: 36px; padding: 0 16px; border: 1px solid #cbd5e1; background: #fff; color: #475569; border-radius: 6px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" style="height: 36px; padding: 0 18px; border: none; background: #059669; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                    Save Subscription Tier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openSubscriptionModal(id, name, planId, status, userLimit, storageLimit, endsAt, aiUnlimited, aiCredits) {
    document.getElementById('modalCompanyTitle').innerText = 'Manage Tier: ' + name;
    document.getElementById('subscriptionForm').action = '/super-admin/billing/' + id + '/update-subscription';
    document.getElementById('modalPlanId').value = planId || '';
    document.getElementById('modalStatus').value = status || 'active';
    document.getElementById('modalUserLimit').value = userLimit || '25';
    document.getElementById('modalStorageLimit').value = storageLimit || '50';
    document.getElementById('modalEndsAt').value = endsAt || '';
    document.getElementById('modalAiUnlimited').checked = !!aiUnlimited;
    document.getElementById('modalAiCredits').value = aiCredits || '0';
    document.getElementById('subscriptionModal').style.display = 'flex';
}

function extendDays(days) {
    const input = document.getElementById('modalEndsAt');
    let base = input.value ? new Date(input.value) : new Date();
    base.setDate(base.getDate() + days);
    input.value = base.toISOString().split('T')[0];
}
</script>
@endsection
