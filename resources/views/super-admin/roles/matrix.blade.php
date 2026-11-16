@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Header --}}
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Role Permissions Matrix</h1>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Access Matrix</span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">
                Configure granular action and module privileges across all system roles simultaneously
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.roles.index') }}" style="height: 32px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; background: #fff; text-decoration: none;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-shield"></i> Roles List
            </a>
            <a href="{{ route('super-admin.permissions.index') }}" style="height: 32px; padding: 0 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; background: #fff; text-decoration: none;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <i class="fa fa-key"></i> Permissions List
            </a>
            <button form="matrix-form" type="submit" style="height: 32px; padding: 0 18px; display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; background: #0ab39c; color: #fff; border: none; border-radius: 4px; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                <i class="fa fa-save"></i> Save Matrix
            </button>
        </div>
    </div>

    {{-- Session Flash Notifications --}}
    @if(session('success'))
    <div style="margin-bottom: 16px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 12px; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- Matrix Card --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); overflow: hidden;">
        <form id="matrix-form" action="{{ route('super-admin.role-permissions.update') }}" method="POST">
            @csrf

            @php
                $rolePermMap = [];
                foreach ($roles as $r) {
                    $rolePermMap[$r->id] = $r->permissions->pluck('id')->flip()->toArray();
                }
            @endphp

            <div style="overflow-x: auto; max-height: 70vh;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; position: sticky; top: 0; z-index: 20;">
                            <th style="padding: 12px 16px; border: 1px solid #e2e8f0; font-weight: 700; color: #475569; position: sticky; left: 0; background: #f8fafc; z-index: 25; min-width: 260px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span>Permissions \ System Roles</span>
                                </div>
                            </th>
                            @foreach($roles as $role)
                            @php $isSuper = ($role->name === 'SuperAdmin'); @endphp
                            <th style="padding: 10px 12px; text-align: center; border: 1px solid #e2e8f0; font-weight: 700; color: #1e293b; min-width: 130px; background: #f8fafc;">
                                <div style="font-size: 12px; font-weight: 700; color: {{ $isSuper ? '#991b1b' : '#1e293b' }};">
                                    {{ $role->name }}
                                </div>
                                <div style="font-size: 9px; color: #64748b; font-weight: 500; margin-top: 2px;">
                                    {{ $role->permissions->count() }} Perms
                                </div>
                                @if(!$isSuper)
                                <div style="margin-top: 6px;">
                                    <button type="button" onclick="toggleRoleColumn({{ $role->id }})" style="background: #e2e8f0; border: none; font-size: 9px; padding: 2px 6px; border-radius: 3px; color: #475569; cursor: pointer; font-weight: 600;">
                                        Toggle All
                                    </button>
                                </div>
                                @else
                                <div style="margin-top: 6px;">
                                    <span style="font-size: 9px; background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 3px; font-weight: 700;">Full Master</span>
                                </div>
                                @endif
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissionsGrouped as $moduleName => $perms)
                        {{-- Module Header Row --}}
                        <tr style="background: #eef2ff; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                            <td colspan="{{ $roles->count() + 1 }}" style="padding: 8px 16px; font-size: 11px; font-weight: 700; color: #3730a3; position: sticky; left: 0; z-index: 10;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span>
                                        <i class="fa fa-folder-open-o" style="margin-right: 6px;"></i>
                                        Module: {{ strtoupper($moduleName ?: 'General Platform') }} ({{ $perms->count() }} actions)
                                    </span>
                                </div>
                            </td>
                        </tr>

                        @foreach($perms as $perm)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                            {{-- Permission Name --}}
                            <td style="padding: 9px 16px; border: 1px solid #f1f5f9; font-weight: 600; color: #334155; position: sticky; left: 0; background: inherit; z-index: 5;">
                                <div>{{ $perm->name }}</div>
                            </td>

                            {{-- Role Checkboxes --}}
                            @foreach($roles as $role)
                            @php
                                $isSuper = ($role->name === 'SuperAdmin');
                                $hasPerm = $isSuper || isset($rolePermMap[$role->id][$perm->id]);
                            @endphp
                            <td style="padding: 8px 10px; border: 1px solid #f1f5f9; text-align: center;">
                                @if($isSuper)
                                    <input type="checkbox" checked disabled style="cursor: not-allowed; width: 15px; height: 15px; accent-color: #ef4444;" title="SuperAdmin has unconditional master access">
                                @else
                                    <input type="checkbox" name="matrix[{{ $role->id }}][{{ $perm->id }}]" value="1"
                                        class="role-cb-{{ $role->id }}"
                                        {{ $hasPerm ? 'checked' : '' }}
                                        style="cursor: pointer; width: 15px; height: 15px; accent-color: #0ab39c;"
                                        title="{{ $role->name }}: {{ $perm->name }}">
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach

                        @empty
                        <tr>
                            <td colspan="{{ $roles->count() + 1 }}" style="padding: 40px; text-align: center; color: #94a3b8; font-size: 13px;">
                                <i class="fa fa-ban" style="font-size: 28px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                No permissions exist in the system yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Action Bar --}}
            <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-size: 11px; color: #64748b;">
                    <i class="fa fa-shield" style="margin-right: 4px;"></i> Changes take effect immediately upon saving. SuperAdmin privileges remain unconditionally active.
                </div>
                <button type="submit" style="height: 34px; padding: 0 22px; font-size: 11px; font-weight: 600; background: #0ab39c; color: #fff; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-save"></i> Save Matrix
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleRoleColumn(roleId) {
    const cbs = document.querySelectorAll('.role-cb-' + roleId);
    if (!cbs.length) return;
    const allChecked = Array.from(cbs).every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
}
</script>
@endpush
@endsection
