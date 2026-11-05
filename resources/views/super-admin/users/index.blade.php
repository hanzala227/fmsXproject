@extends('super-admin.layout')

@section('content')
<div style="padding: 8px 12px;" x-data="userList()">
    {{-- Portlet Container --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        {{-- Top Toolbar --}}
        <div style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-users" style="color: #405189; margin-right: 4px;"></i> User Management & Freight Access Control
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                {{-- Company Filter Dropdown --}}
                <select x-model="companyFilter" @change="applyFilters()" style="height: 26px; padding: 0 6px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none; background: #fff; color: #475569;">
                    <option value="">All Tenant Companies</option>
                    <option value="system">System / Internal (Super Admins)</option>
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                    @endforeach
                </select>

                {{-- Search --}}
                <div style="position: relative;">
                    <input type="text" x-model="search" @input.debounce.400ms="applyFilters()" placeholder="Search users by name, email, dept..."
                        style="height: 26px; width: 220px; padding: 0 8px 0 26px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                    <i class="fa fa-search" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #94a3b8;"></i>
                </div>

                {{-- Action Buttons --}}
                <a href="{{ route('super-admin.users.create') }}"
                   style="height: 26px; padding: 0 10px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 3px; text-decoration: none; cursor: pointer;">
                    <i class="fa fa-plus"></i> New User
                </a>
                <a href="{{ route('super-admin.users.export-csv', ['role' => request('role'), 'company_id' => request('company_id')]) }}"
                   style="height: 26px; padding: 0 8px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; cursor: pointer; color: #475569; text-decoration: none;">
                    <i class="fa fa-file-excel-o" style="color: #22c55e;"></i> Excel
                </a>
            </div>
        </div>

        {{-- Filter Tabs with Dynamic Real Counts --}}
        <div style="padding: 0 10px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 16px; overflow-x: auto;">
            @php
                $currentRole = request('role', '');
                $tabs = [
                    '' => 'All Users',
                    'Administrators' => 'Tenant Admins',
                    'Operations' => 'Freight Operations',
                    'Clients' => 'Clients & Shippers',
                    'SuperAdmins' => 'Super Admins',
                ];
            @endphp
            @foreach($tabs as $val => $label)
            @php
                $count = $tabCounts[$val] ?? 0;
                $isActiveTab = ($currentRole === $val);
            @endphp
            <button @click="roleFilter = '{{ $val }}'; applyFilters()"
                style="padding: 8px 4px; font-size: 11px; font-weight: 600; border: none; background: transparent; cursor: pointer; border-bottom: 2px solid {{ $isActiveTab ? '#0ab39c' : 'transparent' }}; color: {{ $isActiveTab ? '#0ab39c' : '#64748b' }}; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                <span>{{ $label }}</span>
                <span style="font-size: 9px; padding: 1px 6px; border-radius: 9999px; background: {{ $isActiveTab ? '#0ab39c25' : '#f1f5f9' }}; color: {{ $isActiveTab ? '#089784' : '#64748b' }}; font-weight: 700;">
                    {{ $count }}
                </span>
            </button>
            @endforeach
        </div>

        {{-- Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 45px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">ID</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">User & Contact</th>
                        <th style="width: 160px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Tenant Company</th>
                        <th style="width: 130px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Role & Freight Access</th>
                        <th style="width: 75px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Status</th>
                        <th style="width: 110px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Last Login / IP</th>
                        <th style="width: 85px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Created</th>
                        <th style="width: 130px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        {{-- 1. ID --}}
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9; color: #94a3b8; font-weight: 600;">
                            #{{ $user->id }}
                        </td>

                        {{-- 2. Name & Email --}}
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @if($user->profile_photo_path)
                                    <img src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" style="width: 26px; height: 26px; border-radius: 50%; object-fit: cover;">
                                @else
                                    <div style="width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; border: 1px solid #c7d2fe;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 11px;">
                                        <a href="{{ route('super-admin.users.show', $user) }}" style="color: #1e293b; text-decoration: none;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#1e293b'">
                                            {{ $user->name }}
                                        </a>
                                    </div>
                                    <div style="font-size: 9px; color: #64748b;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- 3. Tenant Company --}}
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9;">
                            @if($user->company)
                                <div style="font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-building" style="color: #405189; font-size: 9px;"></i>
                                    <span>{{ $user->company->name }}</span>
                                </div>
                                <div style="font-size: 9px; color: #64748b;">{{ $user->company->subdomain ?? 'app' }}.fms.com</div>
                            @else
                                <span style="font-size: 9px; font-weight: 700; background: #f3e8ff; color: #7e22ce; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #e9d5ff;">
                                    <i class="fa fa-shield"></i> System / Platform
                                </span>
                            @endif
                        </td>

                        {{-- 4. Role & Freight Access --}}
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9;">
                            @php
                                $roleUpper = strtoupper($user->role);
                            @endphp
                            @if($roleUpper === 'SUPERADMIN')
                                <span style="font-size: 9px; font-weight: 700; background: #ede9fe; color: #6b21a8; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #d8b4fe;">
                                    <i class="fa fa-star"></i> Super Admin
                                </span>
                            @elseif($roleUpper === 'ADMIN')
                                <span style="font-size: 9px; font-weight: 700; background: #e0e7ff; color: #3730a3; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #c7d2fe;">
                                    <i class="fa fa-briefcase"></i> Tenant Admin
                                </span>
                            @elseif(in_array($roleUpper, ['OPERATION', 'OPERATOR', 'EMPLOYEES']))
                                <span style="font-size: 9px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #bae6fd;">
                                    <i class="fa fa-cogs"></i> Freight Operations
                                </span>
                            @else
                                <span style="font-size: 9px; font-weight: 700; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #fde68a;">
                                    <i class="fa fa-user"></i> Shipper / Consignee
                                </span>
                            @endif
                            @if($user->department)
                                <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Dept: {{ $user->department }}</div>
                            @endif
                        </td>

                        {{-- 5. Status --}}
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <span id="status-badge-{{ $user->id }}" style="font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700; text-transform: uppercase;
                                {{ $user->status === 'Enable' ? 'background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;' : 'background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;' }}">
                                {{ $user->status === 'Enable' ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        {{-- 6. Last Login / IP --}}
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <div style="color: #475569; font-weight: 500;">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </div>
                            <div style="font-size: 8px; color: #94a3b8;">
                                {{ $user->last_login_ip ?: '—' }}
                            </div>
                        </td>

                        {{-- 7. Created --}}
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9; color: #64748b; font-size: 9px;">
                            {{ $user->created_at?->format('M d, Y') }}
                        </td>

                        {{-- 8. Actions --}}
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                                {{-- View Profile --}}
                                <a href="{{ route('super-admin.users.show', $user) }}" title="View User Profile"
                                   style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #0ab39c; font-size: 10px; text-decoration: none; cursor: pointer;"
                                   onmouseover="this.style.background='#ccfbf1'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-eye"></i>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('super-admin.users.edit', $user) }}" title="Edit User"
                                   style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #3b82f6; font-size: 10px; text-decoration: none; cursor: pointer;"
                                   onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-pencil"></i>
                                </a>

                                {{-- Toggle Status --}}
                                <button onclick="toggleStatus({{ $user->id }})" title="{{ $user->status === 'Enable' ? 'Deactivate Account' : 'Activate Account' }}"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; font-size: 10px; cursor: pointer;
                                    {{ $user->status === 'Enable' ? 'color: #f59e0b;' : 'color: #10b981;' }}"
                                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                                    <i class="fa {{ $user->status === 'Enable' ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                </button>

                                {{-- Reset Password --}}
                                <button onclick="openResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Reset Password"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #ef4444; font-size: 10px; cursor: pointer;"
                                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-key"></i>
                                </button>

                                {{-- Login As (Impersonate) --}}
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('super-admin.users.login-as', $user) }}" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" title="Login as {{ $user->name }} (Impersonate)"
                                        style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #c7d2fe; background: #fff; color: #4338ca; font-size: 10px; cursor: pointer;"
                                        onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#fff'">
                                        <i class="fa fa-sign-in"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">No users found matching your filters. <a href="{{ route('super-admin.users.create') }}" style="color: #405189; font-weight: 600;">Create one</a></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div style="padding: 8px 10px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: #64748b;">
            <span>Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</span>
            <div style="display: flex; gap: 2px;">
                @if($users->onFirstPage())
                    <span style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 3px; color: #cbd5e1; font-size: 10px;">‹</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">‹</a>
                @endif
                @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid {{ $page == $users->currentPage() ? '#405189' : '#cbd5e1' }}; border-radius: 3px; font-size: 10px; text-decoration: none;
                        {{ $page == $users->currentPage() ? 'background: #405189; color: #fff; font-weight: 600;' : 'color: #475569;' }}">{{ $page }}</a>
                @endforeach
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">›</a>
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
            <p id="reset-user-name" style="font-size: 11px; color: #64748b; margin: 0 0 12px;"></p>
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
function userList() {
    return {
        search: new URLSearchParams(window.location.search).get('search') || '',
        roleFilter: new URLSearchParams(window.location.search).get('role') || '',
        companyFilter: new URLSearchParams(window.location.search).get('company_id') || '',
        applyFilters() {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.roleFilter) params.set('role', this.roleFilter);
            if (this.companyFilter) params.set('company_id', this.companyFilter);
            window.location.href = '{{ route('super-admin.users.index') }}?' + params.toString();
        }
    };
}

function toggleStatus(userId) {
    if (!confirm('Are you sure you want to change this user\'s status?')) return;
    fetch('/super-admin/users/' + userId + '/toggle-status', {
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

function openResetPasswordModal(userId, userName) {
    document.getElementById('reset-password-form').action = '/super-admin/users/' + userId + '/reset-password';
    document.getElementById('reset-user-name').textContent = 'Reset password for: ' + userName;
    document.getElementById('reset-password-modal').style.display = 'flex';
}

function closeResetPasswordModal() {
    document.getElementById('reset-password-modal').style.display = 'none';
}

// Close modal on backdrop click
document.getElementById('reset-password-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeResetPasswordModal();
});
</script>
@endpush
@endsection
