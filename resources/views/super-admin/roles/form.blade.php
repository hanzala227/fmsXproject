@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        
        <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 14px; font-weight: 600; color: #1e293b;">
                <i class="fa fa-{{ $role ? 'pencil' : 'plus' }}" style="color: #405189; margin-right: 6px;"></i>
                {{ $role ? 'Edit Role: ' . $role->name : 'Add New Role' }}
            </div>
            <a href="{{ route('super-admin.roles.index') }}" style="color: #64748b; text-decoration: none; font-size: 11px;"><i class="fa fa-arrow-left"></i> Back to list</a>
        </div>

        <form action="{{ $role ? route('super-admin.roles.update', $role) : route('super-admin.roles.store') }}" method="POST" style="padding: 20px;">
            @csrf
            @if($role) @method('PUT') @endif

            @if($errors->any())
            <div style="margin-bottom: 16px; padding: 10px 12px; background: #fef2f2; border: 1px solid #f87171; border-radius: 4px; color: #991b1b; font-size: 11px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Role Name *</label>
                <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required {{ ($role && $role->name === 'SuperAdmin') ? 'readonly' : '' }}
                    style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;"
                    @focus="$el.style.borderColor='#405189'" @blur="$el.style.borderColor='#cbd5e1'">
                @if($role && $role->name === 'SuperAdmin')
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">The SuperAdmin role name cannot be changed.</div>
                @endif
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">Guard Name *</label>
                <select name="guard_name" required style="width: 100%; height: 32px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 3px; outline: none;">
                    <option value="web" {{ old('guard_name', $role->guard_name ?? 'web') === 'web' ? 'selected' : '' }}>Web</option>
                    <option value="api" {{ old('guard_name', $role->guard_name ?? '') === 'api' ? 'selected' : '' }}>API</option>
                </select>
            </div>

            {{-- Permissions Selection --}}
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 8px;">Direct Permissions</label>
                
                @if($permissions->count() > 0)
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; max-height: 200px; overflow-y: auto; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px; background: #f8fafc;">
                    @php
                        $rolePerms = $rolePermissions ?? [];
                    @endphp
                    @foreach($permissions as $perm)
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #1e293b; cursor: pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" {{ in_array($perm->name, $rolePerms) ? 'checked' : '' }}>
                        {{ $perm->name }}
                    </label>
                    @endforeach
                </div>
                <div style="font-size: 10px; color: #64748b; margin-top: 6px;">Note: You can also manage permissions easily from the Matrix View.</div>
                @else
                <div style="font-size: 11px; color: #94a3b8; font-style: italic;">No permissions defined in the system yet.</div>
                @endif
            </div>

            {{-- Actions --}}
            <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('super-admin.roles.index') }}" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; font-size: 11px; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 3px; color: #475569; text-decoration: none; background: #fff;">Cancel</a>
                <button type="submit" style="height: 32px; padding: 0 20px; font-size: 11px; font-weight: 600; border: none; border-radius: 3px; background: #405189; color: #fff; cursor: pointer;">
                    <i class="fa fa-save" style="margin-right: 6px;"></i> {{ $role ? 'Update Role' : 'Create Role' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
