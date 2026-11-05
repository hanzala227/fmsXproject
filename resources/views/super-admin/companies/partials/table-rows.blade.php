@forelse($companies as $company)
@php
    $admin = $company->users->first();
    $totShipments = $company->total_shipments ?? 0;
@endphp
<tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
    {{-- 1. Index --}}
    <td style="padding: 8px; text-align: center; border: 1px solid #f1f5f9; color: #94a3b8; font-weight: 600;">
        {{ $loop->iteration + ($companies->currentPage() - 1) * $companies->perPage() }}
    </td>

    {{-- 2. Company Name & Freight Workload --}}
    <td style="padding: 8px; border: 1px solid #f1f5f9;">
        <div style="font-weight: 700; color: #1e293b; font-size: 11px; margin-bottom: 2px;">
            <a href="{{ route('super-admin.companies.show', $company) }}" style="color: #1e293b; text-decoration: none;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#1e293b'">
                {{ $company->name }}
            </a>
        </div>
        <div style="font-size: 9px; color: #64748b; margin-bottom: 3px;">
            {{ $company->subdomain ?? 'app' }}.fms.com • {{ $company->country ?? 'Global' }}
        </div>
        @if($totShipments > 0)
        <span style="font-size: 8px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #bae6fd;">
            <i class="fa fa-cubes"></i> {{ $totShipments }} Shipments ({{ $company->ocean_count }} Sea, {{ $company->air_count }} Air, {{ $company->truck_count }} Land)
        </span>
        @else
        <span style="font-size: 8px; color: #94a3b8; display: inline-flex; align-items: center; gap: 3px;">
            <i class="fa fa-cube"></i> 0 Shipments
        </span>
        @endif
    </td>

    {{-- 3. Code --}}
    <td style="padding: 8px; border: 1px solid #f1f5f9; color: #475569; font-weight: 600;">
        {{ $company->code ?: '—' }}
    </td>

    {{-- 4. Admin User --}}
    <td style="padding: 8px; border: 1px solid #f1f5f9;">
        <div style="font-weight: 600; color: #1e293b; font-size: 10px;">{{ $admin?->name ?? '—' }}</div>
        <div style="font-size: 8px; color: #94a3b8;">Primary Administrator</div>
    </td>

    {{-- 5. Email & Contact --}}
    <td style="padding: 8px; border: 1px solid #f1f5f9;">
        <div style="color: #475569; font-size: 10px;">{{ $admin?->email ?? $company->email ?? '—' }}</div>
        @if($company->phone)
        <div style="font-size: 8px; color: #94a3b8;"><i class="fa fa-phone" style="font-size: 7px;"></i> {{ $company->phone }}</div>
        @endif
    </td>

    {{-- 6. Status & Subscription Plan --}}
    <td style="padding: 8px; text-align: center; border: 1px solid #f1f5f9;">
        <span id="status-badge-{{ $company->id }}" style="font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700; text-transform: uppercase;
            {{ $company->status === 'active' ? 'background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;' : 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;' }} display: inline-block; margin-bottom: 2px;">
            {{ ucfirst($company->status) }}
        </span>
        @if($company->subscription_status)
        <span style="font-size: 8px; padding: 1px 5px; border-radius: 4px; font-weight: 700; text-transform: uppercase;
            {{ match(strtolower($company->subscription_status)) {
                'active' => 'background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;',
                'trial' => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                'suspended', 'expired' => 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;',
                default => 'background: #f1f5f9; color: #475569;'
            } }} display: inline-block; margin-bottom: 2px;">
            {{ ucfirst($company->subscription_status) }}
        </span>
        @endif
        <div style="font-size: 8px; color: #64748b; font-weight: 500;">
            {{ $company->users->count() }} / {{ $company->user_limit }} Seats
        </div>
    </td>

    {{-- 7. Freight Modules --}}
    <td style="padding: 8px; border: 1px solid #f1f5f9;">
        <div style="display: flex; flex-wrap: wrap; gap: 3px;">
            @php
                $moduleColors = [
                    'ocean-import' => ['bg' => '#e0f2fe', 'color' => '#0369a1'],
                    'ocean-export' => ['bg' => '#e0f2fe', 'color' => '#0369a1'],
                    'air-import' => ['bg' => '#ede9fe', 'color' => '#5b21b6'],
                    'air-export' => ['bg' => '#ede9fe', 'color' => '#5b21b6'],
                    'truck' => ['bg' => '#fef3c7', 'color' => '#b45309'],
                    'warehouse' => ['bg' => '#ccfbf1', 'color' => '#0f766e'],
                    'accounting' => ['bg' => '#dcfce7', 'color' => '#15803d'],
                    'sales' => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
                    'crm' => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
                    'trade-partners' => ['bg' => '#f1f5f9', 'color' => '#334155'],
                    'reports' => ['bg' => '#f5f3ff', 'color' => '#6d28d9'],
                    'settings' => ['bg' => '#f1f5f9', 'color' => '#475569'],
                ];
            @endphp
            @foreach($company->modules as $mod)
            @php
                $col = $moduleColors[$mod->module_key] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
            @endphp
            <span style="font-size: 8px; font-weight: 600; padding: 1px 5px; background: {{ $col['bg'] }}; color: {{ $col['color'] }}; border-radius: 4px; white-space: nowrap;">
                {{ config('modules.' . $mod->module_key . '.label', ucwords(str_replace('-', ' ', $mod->module_key))) }}
            </span>
            @endforeach
            @if($company->modules->isEmpty())
            <span style="font-size: 8px; padding: 1px 5px; background: #f1f5f9; color: #94a3b8; border-radius: 4px;">Standard Core</span>
            @endif
        </div>
    </td>

    {{-- 8. Created --}}
    <td style="padding: 8px; text-align: center; border: 1px solid #f1f5f9; color: #64748b; font-size: 9px; font-weight: 500;">
        {{ $company->created_at?->format('M d, Y') }}
    </td>

    {{-- 9. Last Login --}}
    <td style="padding: 8px; text-align: center; border: 1px solid #f1f5f9; color: #64748b; font-size: 9px;">
        {{ $admin?->last_login_at ? $admin->last_login_at->diffForHumans() : '—' }}
    </td>

    {{-- 10. Actions --}}
    <td style="padding: 8px; text-align: center; border: 1px solid #f1f5f9;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
            {{-- View --}}
            <a href="{{ route('super-admin.companies.show', $company) }}" title="View Profile"
               style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #cbd5e1; background: #fff; color: #0ab39c; font-size: 10px; text-decoration: none; cursor: pointer;"
               onmouseover="this.style.background='#ccfbf1'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-eye"></i>
            </a>

            {{-- Edit --}}
            <a href="{{ route('super-admin.companies.edit', $company) }}" title="Edit Details"
               style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #cbd5e1; background: #fff; color: #3b82f6; font-size: 10px; text-decoration: none; cursor: pointer;"
               onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-pencil"></i>
            </a>

            {{-- Login As Tenant Admin --}}
            @if($admin)
            <form method="POST" action="{{ route('super-admin.users.login-as', $admin->id) }}" style="display: inline; margin: 0;">
                @csrf
                <button type="submit" title="Login As Tenant (Impersonate ERP)"
                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #c7d2fe; background: #fff; color: #4338ca; font-size: 10px; cursor: pointer;"
                    onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#fff'">
                    <i class="fa fa-sign-in"></i>
                </button>
            </form>
            @endif

            {{-- Toggle Status --}}
            <button onclick="toggleStatus({{ $company->id }})" title="{{ $company->status === 'active' ? 'Deactivate Company' : 'Activate Company' }}" id="toggle-btn-{{ $company->id }}"
                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #cbd5e1; background: #fff; font-size: 10px; cursor: pointer;
                {{ $company->status === 'active' ? 'color: #f59e0b;' : 'color: #10b981;' }}"
                onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <i class="fa {{ $company->status === 'active' ? 'fa-ban' : 'fa-check-circle' }}"></i>
            </button>

            {{-- Reset Password --}}
            <button onclick="openResetPasswordModal({{ $company->id }}, '{{ addslashes($company->name) }}')" title="Reset Admin Password"
                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #cbd5e1; background: #fff; color: #ef4444; font-size: 10px; cursor: pointer;"
                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-key"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">No companies found. <a href="{{ route('super-admin.companies.create') }}" style="color: #405189; font-weight: 600;">Create one</a></td>
</tr>
@endforelse
