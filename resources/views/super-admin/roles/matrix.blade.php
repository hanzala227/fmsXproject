@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 2px;">
        <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 14px; font-weight: 600; color: #1e293b;"><i class="fa fa-th" style="color: #405189; margin-right: 6px;"></i> Role Permissions Matrix</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Easily assign or revoke permissions across all roles.</div>
            </div>
            
            <button form="matrix-form" type="submit" style="height: 32px; padding: 0 16px; display: inline-flex; align-items: center; font-size: 11px; font-weight: 600; background: #0ab39c; color: #fff; border: none; border-radius: 3px; cursor: pointer;">
                <i class="fa fa-save" style="margin-right: 6px;"></i> Save Matrix
            </button>
        </div>

        <form id="matrix-form" action="{{ route('super-admin.role-permissions.update') }}" method="POST">
            @csrf
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 10px 16px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; position: sticky; left: 0; background: #f8fafc; z-index: 10;">
                                Permissions \ Roles
                            </th>
                            @foreach($roles as $role)
                            <th style="padding: 10px 16px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #1e293b; min-width: 120px;">
                                {{ $role->name }}
                                <div style="font-size: 9px; color: #94a3b8; font-weight: 400; margin-top: 2px;">{{ $role->guard_name }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $perm)
                        <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                            <td style="padding: 10px 16px; border: 1px solid #f1f5f9; font-weight: 600; color: #334155; position: sticky; left: 0; background: inherit; z-index: 5;">
                                {{ $perm->name }}
                            </td>
                            
                            @foreach($roles as $role)
                                @php
                                    $hasPerm = $role->hasPermissionTo($perm->name);
                                    // SuperAdmin shouldn't really be edited, but we'll show it
                                    $isSuperAdmin = $role->name === 'SuperAdmin';
                                @endphp
                                <td style="padding: 10px; border: 1px solid #f1f5f9; text-align: center;">
                                    <input type="checkbox" name="matrix[{{ $role->id }}][{{ $perm->id }}]" value="1"
                                        {{ $hasPerm ? 'checked' : '' }} 
                                        {{ $isSuperAdmin ? 'disabled' : '' }}
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #0ab39c;">
                                    
                                    @if($isSuperAdmin && $hasPerm)
                                        <input type="hidden" name="matrix[{{ $role->id }}][{{ $perm->id }}]" value="1">
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $roles->count() + 1 }}" style="padding: 40px; text-align: center; color: #94a3b8;">
                                No permissions exist yet. Go to Permissions List to create them.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </form>
    </div>
</div>
@endsection
