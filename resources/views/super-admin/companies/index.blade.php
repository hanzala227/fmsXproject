@extends('super-admin.layout')

@section('content')
<div style="padding: 8px 12px;" x-data="companyList()">
    {{-- Portlet Container --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        {{-- Top Toolbar --}}
        <div style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 600; color: #1e293b;"><i class="fa fa-building" style="color: #405189; margin-right: 4px;"></i> Company Management</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                {{-- Search --}}
                <div style="position: relative;">
                    <input type="text" x-model="search" @input.debounce.400ms="applyFilters()" placeholder="Search companies..."
                        style="height: 26px; width: 200px; padding: 0 8px 0 26px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                    <i class="fa fa-search" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #94a3b8;"></i>
                </div>

                {{-- Action Buttons --}}
                <a href="{{ route('super-admin.companies.create') }}"
                   style="height: 26px; padding: 0 10px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 3px; text-decoration: none; cursor: pointer;">
                    <i class="fa fa-plus"></i> New Company
                </a>
                <button onclick="exportExcel()"
                    style="height: 26px; padding: 0 8px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; cursor: pointer; color: #475569;">
                    <i class="fa fa-file-excel-o" style="color: #22c55e;"></i> Excel
                </button>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div style="padding: 0 10px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 16px;">
            @php
                $currentStatus = request('status', '');
                $tabs = [
                    '' => 'All Companies',
                    'active' => 'Active',
                    'trial' => 'Trial',
                    'suspended' => 'Suspended',
                    'expired' => 'Expired',
                    'inactive' => 'Inactive'
                ];
            @endphp
            @foreach($tabs as $val => $label)
            <button @click="statusFilter = '{{ $val }}'; applyFilters()"
                style="padding: 8px 4px; font-size: 11px; font-weight: 600; border: none; background: transparent; cursor: pointer; border-bottom: 2px solid {{ $currentStatus === $val ? '#0ab39c' : 'transparent' }}; color: {{ $currentStatus === $val ? '#0ab39c' : '#64748b' }};">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 40px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">#</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Company</th>
                        <th style="width: 80px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Code</th>
                        <th style="width: 140px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Admin User</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Email</th>
                        <th style="width: 80px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Status</th>
                        <th style="width: 200px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Modules</th>
                        <th style="width: 90px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Created</th>
                        <th style="width: 90px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Last Login</th>
                        <th style="width: 140px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($companies->hasPages())
        <div style="padding: 8px 10px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: #64748b;">
            <span>Showing {{ $companies->firstItem() }}–{{ $companies->lastItem() }} of {{ $companies->total() }}</span>
            <div style="display: flex; gap: 2px;">
                @if($companies->onFirstPage())
                    <span style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 3px; color: #cbd5e1; font-size: 10px;">‹</span>
                @else
                    <a href="{{ $companies->previousPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">‹</a>
                @endif
                @foreach($companies->getUrlRange(max(1, $companies->currentPage()-2), min($companies->lastPage(), $companies->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid {{ $page == $companies->currentPage() ? '#405189' : '#cbd5e1' }}; border-radius: 3px; font-size: 10px; text-decoration: none;
                        {{ $page == $companies->currentPage() ? 'background: #405189; color: #fff; font-weight: 600;' : 'color: #475569;' }}">{{ $page }}</a>
                @endforeach
                @if($companies->hasMorePages())
                    <a href="{{ $companies->nextPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">›</a>
                @else
                    <span style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 3px; color: #cbd5e1; font-size: 10px;">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Reset Password Modal --}}
<div id="reset-password-modal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 6px; width: 400px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="padding: 14px 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 12px; font-weight: 600; color: #1e293b;"><i class="fa fa-key" style="color: #ef4444; margin-right: 6px;"></i> Reset Password</span>
            <button onclick="closeResetPasswordModal()" style="background: none; border: none; color: #94a3b8; font-size: 14px; cursor: pointer;">×</button>
        </div>
        <form id="reset-password-form" method="POST" style="padding: 16px;">
            @csrf
            <p id="reset-company-name" style="font-size: 11px; color: #64748b; margin: 0 0 12px;"></p>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 10px; font-weight: 600; color: #475569; margin-bottom: 4px;">New Password</label>
                <input type="password" name="new_password" required minlength="8"
                    style="width: 100%; height: 32px; padding: 0 10px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 10px; font-weight: 600; color: #475569; margin-bottom: 4px;">Confirm Password</label>
                <input type="password" name="new_password_confirmation" required minlength="8"
                    style="width: 100%; height: 32px; padding: 0 10px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="closeResetPasswordModal()" style="height: 30px; padding: 0 14px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; background: #fff; color: #475569; cursor: pointer;">Cancel</button>
                <button type="submit" style="height: 30px; padding: 0 14px; font-size: 10px; border: none; border-radius: 3px; background: #ef4444; color: #fff; font-weight: 600; cursor: pointer;">Reset Password</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function companyList() {
    return {
        search: new URLSearchParams(window.location.search).get('search') || '',
        statusFilter: new URLSearchParams(window.location.search).get('status') || '',
        applyFilters() {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.statusFilter) params.set('status', this.statusFilter);
            window.location.href = '{{ route('super-admin.companies.index') }}?' + params.toString();
        }
    };
}

function toggleStatus(companyId) {
    if (!confirm('Are you sure you want to change this company\'s status?')) return;
    fetch('/super-admin/companies/' + companyId + '/toggle-status', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(() => showToast('error', 'Failed to update status'));
}

function openResetPasswordModal(companyId, companyName) {
    document.getElementById('reset-password-form').action = '/super-admin/companies/' + companyId + '/reset-password';
    document.getElementById('reset-company-name').textContent = 'Reset password for: ' + companyName;
    document.getElementById('reset-password-modal').style.display = 'flex';
}

function closeResetPasswordModal() {
    document.getElementById('reset-password-modal').style.display = 'none';
}

function exportExcel() {
    showToast('info', 'Preparing export...');
    fetch('{{ route('super-admin.companies.export-csv') }}')
        .then(r => r.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'companies-' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
            showToast('success', 'Export complete');
        })
        .catch(() => showToast('error', 'Export failed'));
}

// Close modal on backdrop click
document.getElementById('reset-password-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeResetPasswordModal();
});
</script>
@endpush
@endsection
