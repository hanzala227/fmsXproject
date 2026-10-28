@forelse($companies as $company)
@php $admin = $company->users->first(); @endphp
<tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
    <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9; color: #94a3b8;">{{ $loop->iteration + ($companies->currentPage() - 1) * $companies->perPage() }}</td>
    <td style="padding: 6px 8px; border: 1px solid #f1f5f9; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $company->name }}</td>
    <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #64748b;">{{ $company->code ?? '—' }}</td>
    <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $admin?->name ?? '—' }}</td>
    <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $admin?->email ?? $company->email ?? '—' }}</td>
    <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
        <span id="status-badge-{{ $company->id }}" style="font-size: 9px; padding: 2px 8px; border-radius: 10px; font-weight: 600;
            {{ $company->status === 'active' ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }} display: block; margin-bottom: 2px;">
            {{ ucfirst($company->status) }}
        </span>
        @if($company->subscription_status)
        <span style="font-size: 8px; padding: 1px 6px; border-radius: 10px; font-weight: 600;
            {{ match($company->subscription_status) {
                'active' => 'background: #dbeafe; color: #1e40af;',
                'trial' => 'background: #fef3c7; color: #b45309;',
                'suspended', 'expired' => 'background: #fee2e2; color: #991b1b;',
                default => 'background: #f1f5f9; color: #475569;'
            } }} display: block;">
            {{ ucfirst($company->subscription_status) }}
        </span>
        @endif
    </td>
    <td style="padding: 6px 8px; border: 1px solid #f1f5f9;">
        <div style="display: flex; flex-wrap: wrap; gap: 2px;">
            @foreach($company->modules as $mod)
            <span style="font-size: 8px; padding: 1px 5px; background: #eff6ff; color: #1e40af; border-radius: 8px; white-space: nowrap;">{{ config('modules.' . $mod->module_key . '.label', $mod->module_key) }}</span>
            @endforeach
            @if($company->modules->isEmpty())
            <span style="font-size: 8px; color: #94a3b8;">None</span>
            @endif
        </div>
    </td>
    <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9; color: #64748b; font-size: 9px;">{{ $company->created_at?->format('M d, Y') }}</td>
    <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9; color: #64748b; font-size: 9px;">{{ $admin?->last_login_at ? $admin->last_login_at->diffForHumans() : '—' }}</td>
    <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
            <a href="{{ route('super-admin.companies.show', $company) }}" title="View Profile"
               style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #0ab39c; font-size: 10px; text-decoration: none; cursor: pointer;"
               onmouseover="this.style.background='#ccfbf1'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-eye"></i>
            </a>
            <a href="{{ route('super-admin.companies.edit', $company) }}" title="Edit"
               style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #3b82f6; font-size: 10px; text-decoration: none; cursor: pointer;"
               onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-pencil"></i>
            </a>
            <button onclick="toggleStatus({{ $company->id }})" title="{{ $company->status === 'active' ? 'Deactivate' : 'Activate' }}" id="toggle-btn-{{ $company->id }}"
                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; font-size: 10px; cursor: pointer;
                {{ $company->status === 'active' ? 'color: #f59e0b;' : 'color: #22c55e;' }}">
                <i class="fa {{ $company->status === 'active' ? 'fa-ban' : 'fa-check-circle' }}"></i>
            </button>
            <button onclick="openResetPasswordModal({{ $company->id }}, '{{ addslashes($company->name) }}')" title="Reset Password"
                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #ef4444; font-size: 10px; cursor: pointer;"
                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-key"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">No companies found. <a href="{{ route('super-admin.companies.create') }}" style="color: #405189;">Create one</a></td>
</tr>
@endforelse

