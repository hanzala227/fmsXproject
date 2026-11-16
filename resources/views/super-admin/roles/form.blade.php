@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    <div style="max-width: 800px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 14px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-{{ $role ? 'pencil' : 'plus-circle' }}" style="color: #405189;"></i>
                <span>{{ $role ? 'Edit Role: ' . $role->name : 'Create New System Role' }}</span>
                @if($role && $role->name === 'SuperAdmin')
                    <span style="font-size: 10px; background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px; font-weight: 700;">Protected System Role</span>
                @endif
            </div>
            <a href="{{ route('super-admin.roles.index') }}" style="color: #64748b; text-decoration: none; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                <i class="fa fa-arrow-left"></i> Back to roles
            </a>
        </div>

        <form action="{{ $role ? route('super-admin.roles.update', $role) : route('super-admin.roles.store') }}" method="POST" style="padding: 20px;">
            @csrf
            @if($role) @method('PUT') @endif

            @if(isset($errors) && $errors->any())
            <div style="margin-bottom: 16px; padding: 10px 14px; background: #fef2f2; border: 1px solid #f87171; border-radius: 4px; color: #991b1b; font-size: 11px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">
                        Role Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required {{ ($role && $role->name === 'SuperAdmin') ? 'readonly' : '' }}
                        style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;"
                        @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                    @if($role && $role->name === 'SuperAdmin')
                    <div style="font-size: 10px; color: #64748b; margin-top: 4px;">The SuperAdmin role name is locked to preserve master security permissions.</div>
                    @endif
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Guard Name</label>
                    <input type="text" name="guard_name" value="{{ old('guard_name', $role->guard_name ?? 'web') }}"
                        style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box; background: #f8fafc;" readonly>
                </div>
            </div>

            {{-- Permissions Selection Grouped by Module --}}
            @php
                $grouped = isset($permissionsGrouped) ? $permissionsGrouped : (isset($permissions) ? $permissions->groupBy('module_name') : collect());
                $rolePerms = $rolePermissions ?? ($role ? $role->permissions->pluck('name')->toArray() : []);
            @endphp

            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <label style="font-size: 12px; font-weight: 700; color: #1e293b;">
                        Assigned Permissions ({{ count($rolePerms) }} active)
                    </label>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="document.querySelectorAll('.perm-cb').forEach(c => c.checked = true)" style="background: none; border: none; font-size: 11px; color: #0ab39c; font-weight: 600; cursor: pointer;">
                            Select All
                        </button>
                        <span style="color: #cbd5e1;">|</span>
                        <button type="button" onclick="document.querySelectorAll('.perm-cb').forEach(c => c.checked = false)" style="background: none; border: none; font-size: 11px; color: #ef4444; font-weight: 600; cursor: pointer;">
                            Deselect All
                        </button>
                    </div>
                </div>

                @if($grouped->count() > 0)
                <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; background: #f8fafc; max-height: 420px; overflow-y: auto;">
                    @foreach($grouped as $moduleName => $perms)
                    <div style="margin-bottom: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">
                            <span style="font-size: 11px; font-weight: 700; color: #405189; text-transform: uppercase;">
                                <i class="fa fa-cube" style="margin-right: 4px;"></i> {{ $moduleName ?: 'General Platform' }} ({{ $perms->count() }})
                            </span>
                            <button type="button" onclick="this.closest('.module-group-container').querySelectorAll('.perm-cb').forEach(c => c.checked = !c.checked)" style="background: none; border: none; font-size: 10px; color: #64748b; cursor: pointer;">
                                Toggle Module
                            </button>
                        </div>
                        <div class="module-group-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 6px;">
                            @foreach($perms as $perm)
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #1e293b; cursor: pointer; padding: 4px 6px; border-radius: 3px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="perm-cb" {{ in_array($perm->name, $rolePerms) ? 'checked' : '' }} style="cursor: pointer; accent-color: #0ab39c;">
                                <span>{{ $perm->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                    <i class="fa fa-info-circle"></i> Permissions can also be fine-tuned visually across all roles simultaneously via the <a href="{{ route('super-admin.role-permissions.index') }}" style="color: #405189; font-weight: 600;">Role Permissions Matrix</a>.
                </div>
                @else
                <div style="font-size: 11px; color: #94a3b8; font-style: italic; padding: 20px; text-align: center;">No permissions defined in the system yet.</div>
                @endif
            </div>

            {{-- Actions --}}
            <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('super-admin.roles.index') }}" style="height: 34px; padding: 0 16px; display: inline-flex; align-items: center; font-size: 11px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; text-decoration: none; background: #fff;">Cancel</a>
                <button type="submit" style="height: 34px; padding: 0 22px; font-size: 11px; font-weight: 600; border: none; border-radius: 4px; background: #0ab39c; color: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-save"></i> {{ $role ? 'Update Role & Permissions' : 'Create Role' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
