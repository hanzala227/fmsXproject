@extends('super-admin.layout')

@section('content')
<div style="padding: 8px 12px;" x-data="userList()">
    {{-- Portlet Container --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        {{-- Top Toolbar --}}
        <div style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 600; color: #1e293b;"><i class="fa fa-users" style="color: #405189; margin-right: 4px;"></i> User Management</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                {{-- Search --}}
                <div style="position: relative;">
                    <input type="text" x-model="search" @input.debounce.400ms="applyFilters()" placeholder="Search users..."
                        style="height: 26px; width: 200px; padding: 0 8px 0 26px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                    <i class="fa fa-search" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #94a3b8;"></i>
                </div>

                {{-- Action Buttons --}}
                <a href="{{ route('super-admin.users.create') }}"
                   style="height: 26px; padding: 0 10px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 3px; text-decoration: none; cursor: pointer;">
                    <i class="fa fa-plus"></i> New User
                </a>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div style="padding: 0 10px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 16px;">
            @php
                $currentRole = request('role', '');
                $tabs = [
                    '' => 'All Users',
                    'Support Team' => 'Support Team',
                    'Developers' => 'Developers',
                    'Administrators' => 'Administrators',
                    'SuperAdmins' => 'Super Admins',
                ];
            @endphp
            @foreach($tabs as $val => $label)
            <button @click="roleFilter = '{{ $val }}'; applyFilters()"
                style="padding: 8px 4px; font-size: 11px; font-weight: 600; border: none; background: transparent; cursor: pointer; border-bottom: 2px solid {{ $currentRole === $val ? '#0ab39c' : 'transparent' }}; color: {{ $currentRole === $val ? '#0ab39c' : '#64748b' }};">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 60px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">ID</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Name</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Email</th>
                        <th style="width: 150px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Company</th>
                        <th style="width: 120px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Role / Dept</th>
                        <th style="width: 80px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Status</th>
                        <th style="width: 120px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Last Login / IP</th>
                        <th style="width: 100px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Created</th>
                        <th style="width: 180px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #94a3b8;">#{{ $user->id }}</td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; font-weight: 600; color: #1e293b;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                @if($user->profile_photo_path)
                                    <img src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                @else
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 600;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px;">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $user->email }}</td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $user->company?->name ?? 'System / Internal' }}</td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9;">
                            <div style="font-weight: 600; color: #3b82f6;">{{ $user->role }}</div>
                            <div style="font-size: 9px; color: #94a3b8;">{{ $user->department ?? '—' }}</div>
                        </td>
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <span id="status-badge-{{ $user->id }}" style="font-size: 9px; padding: 2px 8px; border-radius: 10px; font-weight: 600;
                                {{ $user->status === 'Enable' ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;' }}">
                                {{ $user->status === 'Enable' ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <div style="color: #64748b;">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</div>
                            <div style="font-size: 9px; color: #94a3b8;">{{ $user->last_login_ip ?? '—' }}</div>
                        </td>
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9; color: #64748b;">{{ $user->created_at?->format('M d, Y') }}</td>
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                                <a href="{{ route('super-admin.users.show', $user) }}" title="View Profile"
                                   style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #0ab39c; font-size: 10px; text-decoration: none; cursor: pointer;"
                                   onmouseover="this.style.background='#ccfbf1'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.users.edit', $user) }}" title="Edit"
                                   style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #3b82f6; font-size: 10px; text-decoration: none; cursor: pointer;"
                                   onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <button onclick="toggleStatus({{ $user->id }})" title="{{ $user->status === 'Enable' ? 'Deactivate' : 'Activate' }}"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; font-size: 10px; cursor: pointer;
                                    {{ $user->status === 'Enable' ? 'color: #f59e0b;' : 'color: #22c55e;' }}">
                                    <i class="fa {{ $user->status === 'Enable' ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                </button>
                                <button onclick="openResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Reset Password"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #ef4444; font-size: 10px; cursor: pointer;"
                                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-key"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('super-admin.users.login-as', $user) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" title="Login as {{ $user->name }}"
                                        style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #8b5cf6; font-size: 10px; cursor: pointer;"
                                        onmouseover="this.style.background='#f3e8ff'" onmouseout="this.style.background='#fff'">
                                        <i class="fa fa-sign-in"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">No users found.</td>
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
        applyFilters() {
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.roleFilter) params.set('role', this.roleFilter);
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
