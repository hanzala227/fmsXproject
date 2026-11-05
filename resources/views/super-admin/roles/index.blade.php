@extends('super-admin.layout')

@section('content')
<div style="padding: 8px 12px;">
    {{-- Portlet Container --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        {{-- Top Toolbar --}}
        <div style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-shield" style="color: #405189; margin-right: 4px;"></i> Role Management & Freight Access Control
                </span>
                <span style="font-size: 9px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 6px; border-radius: 9999px;">
                    Spatie RBAC
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                {{-- Search --}}
                <form method="GET" action="{{ route('super-admin.roles.index') }}" style="position: relative; margin: 0;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles by title..."
                        style="height: 26px; width: 200px; padding: 0 8px 0 26px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        onfocus="this.style.borderColor='#405189'" onblur="this.style.borderColor='#cbd5e1'">
                    <i class="fa fa-search" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #94a3b8;"></i>
                </form>

                {{-- Action Buttons --}}
                <a href="{{ route('super-admin.role-permissions.index') }}"
                   style="height: 26px; padding: 0 8px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; cursor: pointer; color: #475569; text-decoration: none;">
                    <i class="fa fa-th" style="color: #6366f1;"></i> Permission Matrix
                </a>
                <a href="{{ route('super-admin.roles.create') }}"
                   style="height: 26px; padding: 0 10px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 3px; text-decoration: none; cursor: pointer;">
                    <i class="fa fa-plus"></i> New Role
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 10px; table-layout: fixed;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 45px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">ID</th>
                        <th style="width: 280px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Role & Freight Domain</th>
                        <th style="width: 140px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Permissions Granted</th>
                        <th style="width: 100px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">User Count</th>
                        <th style="width: 100px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Created Date</th>
                        <th style="width: 110px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $roleMeta = [
                            'SuperAdmin' => ['icon' => 'fa-star', 'color' => '#6b21a8', 'bg' => '#f3e8ff', 'border' => '#d8b4fe', 'desc' => 'Multi-Tenant Platform SaaS Operator'],
                            'Admin' => ['icon' => 'fa-briefcase', 'color' => '#3730a3', 'bg' => '#e0e7ff', 'border' => '#c7d2fe', 'desc' => 'Freight Forwarder Tenant Administrator'],
                            'Operation' => ['icon' => 'fa-cogs', 'color' => '#0369a1', 'bg' => '#e0f2fe', 'border' => '#bae6fd', 'desc' => 'Freight Operations Manager (Multimodal)'],
                            'Ocean Freight Operator' => ['icon' => 'fa-ship', 'color' => '#0284c7', 'bg' => '#e0f2fe', 'border' => '#bae6fd', 'desc' => 'FCL/LCL Container Shipments & B/L Execution'],
                            'Air Cargo Specialist' => ['icon' => 'fa-plane', 'color' => '#5b21b6', 'bg' => '#ede9fe', 'border' => '#ddd6fe', 'desc' => 'MAWB/HAWB Flight Bookings & IATA 1:6000 Rules'],
                            'Customs Broker' => ['icon' => 'fa-file-text-o', 'color' => '#b45309', 'bg' => '#fef3c7', 'border' => '#fde68a', 'desc' => 'Customs Clearance & Regulatory Compliance'],
                            'Freight Accountant' => ['icon' => 'fa-money', 'color' => '#15803d', 'bg' => '#dcfce7', 'border' => '#bbf7d0', 'desc' => 'Invoicing, Financial AP/AR & RoE Locking'],
                            'Customer' => ['icon' => 'fa-user', 'color' => '#92400e', 'bg' => '#fef3c7', 'border' => '#fde68a', 'desc' => 'Client / Shipper Self-Service Portal'],
                        ];
                    @endphp

                    @forelse($roles as $role)
                    @php
                        $meta = $roleMeta[$role->name] ?? ['icon' => 'fa-shield', 'color' => '#475569', 'bg' => '#f1f5f9', 'border' => '#e2e8f0', 'desc' => 'Standard Security Role'];
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        {{-- 1. ID --}}
                        <td style="padding: 8px; text-align: center; border: 1px solid #f1f5f9; color: #94a3b8; font-weight: 600;">
                            #{{ $role->id }}
                        </td>

                        {{-- 2. Role Name & Domain Scope --}}
                        <td style="padding: 8px; border: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                                <div style="width: 24px; height: 24px; border-radius: 4px; background: {{ $meta['bg'] }}; color: {{ $meta['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 11px; border: 1px solid {{ $meta['border'] }};">
                                    <i class="fa {{ $meta['icon'] }}"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #1e293b; font-size: 11px;">
                                        <a href="{{ route('super-admin.roles.edit', $role) }}" style="color: #1e293b; text-decoration: none;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#1e293b'">
                                            {{ $role->name }}
                                        </a>
                                    </div>
                                    <div style="font-size: 9px; color: #64748b;">{{ $meta['desc'] }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- 3. Permissions Granted --}}
                        <td style="padding: 8px; border: 1px solid #f1f5f9; text-align: center;">
                            @if($role->permissions_count > 0)
                                <span style="font-size: 9px; font-weight: 700; background: #ecfdf5; color: #047857; padding: 2px 8px; border-radius: 12px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-check-circle" style="font-size: 8px;"></i> {{ $role->permissions_count }} Permissions
                                </span>
                            @else
                                <span style="font-size: 9px; color: #94a3b8; background: #f8fafc; padding: 2px 8px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                    0 Permissions
                                </span>
                            @endif
                        </td>

                        {{-- 4. User Count --}}
                        <td style="padding: 8px; border: 1px solid #f1f5f9; text-align: center;">
                            @if($role->users_count > 0)
                                <a href="{{ route('super-admin.users.index', ['role' => $role->name === 'Admin' ? 'Administrators' : ($role->name === 'Operation' ? 'Operations' : ($role->name === 'Customer' ? 'Clients' : $role->name))]) }}"
                                   style="display: inline-flex; align-items: center; justify-content: center; padding: 2px 8px; background: #eff6ff; color: #1d4ed8; font-weight: 700; border-radius: 12px; border: 1px solid #bfdbfe; font-size: 9px; text-decoration: none;" title="View users with this role">
                                    <i class="fa fa-users" style="font-size: 8px; margin-right: 3px;"></i> {{ $role->users_count }}
                                </a>
                            @else
                                <span style="display: inline-flex; align-items: center; justify-content: center; padding: 2px 8px; background: #f8fafc; color: #94a3b8; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 9px;">
                                    0
                                </span>
                            @endif
                        </td>

                        {{-- 6. Created Date --}}
                        <td style="padding: 8px; border: 1px solid #f1f5f9; text-align: center; color: #64748b; font-size: 9px;">
                            {{ $role->created_at?->format('M d, Y') }}
                        </td>

                        {{-- 7. Actions --}}
                        <td style="padding: 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                                {{-- Edit --}}
                                <a href="{{ route('super-admin.roles.edit', $role) }}" title="Edit Role & Permissions"
                                   style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #3b82f6; font-size: 10px; text-decoration: none; cursor: pointer;"
                                   onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                
                                {{-- Clone --}}
                                <form action="{{ route('super-admin.roles.clone', $role) }}" method="POST" style="margin: 0; display: inline-block;">
                                    @csrf
                                    <button type="submit" title="Clone Role"
                                        style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #8b5cf6; font-size: 10px; cursor: pointer;"
                                        onmouseover="this.style.background='#f3e8ff'" onmouseout="this.style.background='#fff'">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                @if($role->name !== 'SuperAdmin')
                                <button onclick="deleteRole({{ $role->id }})" title="Delete Role"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #ef4444; font-size: 10px; cursor: pointer;"
                                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">No roles found. <a href="{{ route('super-admin.roles.create') }}" style="color: #405189; font-weight: 600;">Create one</a></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($roles->hasPages())
        <div style="padding: 8px 10px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: #64748b;">
            <span>Showing {{ $roles->firstItem() }}–{{ $roles->lastItem() }} of {{ $roles->total() }}</span>
            <div style="display: flex; gap: 2px;">
                @if($roles->onFirstPage())
                    <span style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 3px; color: #cbd5e1; font-size: 10px;">‹</span>
                @else
                    <a href="{{ $roles->previousPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">‹</a>
                @endif
                @foreach($roles->getUrlRange(max(1, $roles->currentPage()-2), min($roles->lastPage(), $roles->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid {{ $page == $roles->currentPage() ? '#405189' : '#cbd5e1' }}; border-radius: 3px; font-size: 10px; text-decoration: none;
                        {{ $page == $roles->currentPage() ? 'background: #405189; color: #fff; font-weight: 600;' : 'color: #475569;' }}">{{ $page }}</a>
                @endforeach
                @if($roles->hasMorePages())
                    <a href="{{ $roles->nextPageUrl() }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; font-size: 10px; text-decoration: none;">›</a>
                @else
                    <span style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; border-radius: 3px; color: #cbd5e1; font-size: 10px;">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function deleteRole(id) {
    if (!confirm('Are you sure you want to delete this role? This cannot be undone.')) return;
    fetch('/super-admin/roles/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            showToast('success', data.message);
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('error', data.message || 'Error deleting role');
        }
    })
    .catch(() => showToast('error', 'Network error occurred.'));
}
</script>
@endpush
@endsection
