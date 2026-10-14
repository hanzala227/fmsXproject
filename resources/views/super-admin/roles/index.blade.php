@extends('super-admin.layout')

@section('content')
<div style="padding: 8px 12px;">
    {{-- Portlet Container --}}
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        {{-- Top Toolbar --}}
        <div style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 12px; font-weight: 600; color: #1e293b;"><i class="fa fa-shield" style="color: #405189; margin-right: 4px;"></i> Role Management</span>
            </div>
            <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                {{-- Search --}}
                <form method="GET" action="{{ route('super-admin.roles.index') }}" style="position: relative; margin: 0;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search roles..."
                        style="height: 26px; width: 200px; padding: 0 8px 0 26px; font-size: 10px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                        onfocus="this.style.borderColor='#405189'" onblur="this.style.borderColor='#cbd5e1'">
                    <i class="fa fa-search" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); font-size: 10px; color: #94a3b8;"></i>
                </form>

                {{-- Action Buttons --}}
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
                        <th style="width: 60px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">ID</th>
                        <th style="width: 200px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Role Name</th>
                        <th style="width: 150px; padding: 6px 8px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Guard</th>
                        <th style="width: 100px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">User Count</th>
                        <th style="width: 120px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Created Date</th>
                        <th style="width: 120px; padding: 6px 8px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #94a3b8;">#{{ $role->id }}</td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; font-weight: 600; color: #1e293b;">
                            {{ $role->name }}
                            @if($role->name === 'SuperAdmin')
                                <i class="fa fa-star" style="color: #f59e0b; margin-left: 4px;" title="System Default"></i>
                            @endif
                        </td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; color: #64748b;">
                            <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 9px; border: 1px solid #e2e8f0;">{{ $role->guard_name }}</span>
                        </td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; text-align: center;">
                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #eff6ff; color: #3b82f6; font-weight: 600; border-radius: 50%;">
                                {{ $role->users_count }}
                            </div>
                        </td>
                        <td style="padding: 6px 8px; border: 1px solid #f1f5f9; text-align: center; color: #64748b;">{{ $role->created_at->format('M d, Y') }}</td>
                        <td style="padding: 6px 8px; text-align: center; border: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <a href="{{ route('super-admin.roles.edit', $role) }}" title="Edit Role"
                                   style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #3b82f6; font-size: 10px; text-decoration: none; cursor: pointer;"
                                   onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                
                                <form action="{{ route('super-admin.roles.clone', $role) }}" method="POST" style="margin: 0; display: inline-block;">
                                    @csrf
                                    <button type="submit" title="Clone Role"
                                        style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; border: 1px solid #cbd5e1; background: #fff; color: #8b5cf6; font-size: 10px; cursor: pointer;"
                                        onmouseover="this.style.background='#f3e8ff'" onmouseout="this.style.background='#fff'">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </form>

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
                        <td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8; font-size: 12px;">No roles found.</td>
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
