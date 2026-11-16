<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\SuperAdminAuditLog;

class SuperAdminRoleController extends Controller
{
    public function index(?Request $request = null)
    {
        $request = $request ?? request();
        $query = Role::withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $roles = $query->latest()->paginate(15)->withQueryString();

        return view('super-admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $role = null;
        $permissionsGrouped = Permission::orderBy('module_name')->orderBy('name')->get()->groupBy('module_name');
        return view('super-admin.roles.form', [
            'role' => null,
            'permissionsGrouped' => $permissionsGrouped,
            'rolePermissions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['guard_name' => $request->input('guard_name') ?: 'web']);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'nullable|string|max:255',
        ]);
        $validated['guard_name'] = $validated['guard_name'] ?: 'web';

        $role = Role::create($validated);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        SuperAdminAuditLog::log('created_role', 'role', $role->id, ['name' => $role->name]);

        return redirect()->route('super-admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $role
            ]);
        }

        return redirect()->route('super-admin.roles.edit', $role->id);
    }

    public function edit(Role $role)
    {
        $permissionsGrouped = Permission::orderBy('module_name')->orderBy('name')->get()->groupBy('module_name');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('super-admin.roles.form', compact('role', 'permissionsGrouped', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->merge(['guard_name' => $request->input('guard_name') ?: ($role->guard_name ?: 'web')]);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'guard_name' => 'nullable|string|max:255',
        ]);
        $validated['guard_name'] = $validated['guard_name'] ?: ($role->guard_name ?: 'web');

        $role->update($validated);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        SuperAdminAuditLog::log('updated_role', 'role', $role->id, ['name' => $role->name]);

        return redirect()->route('super-admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'SuperAdmin') {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete SuperAdmin role.'], 403);
            }
            return back()->with('error', 'Cannot delete SuperAdmin role.');
        }

        $name = $role->name;
        SuperAdminAuditLog::log('deleted_role', 'role', $role->id, ['name' => $name]);
        
        $role->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => "Role '{$name}' deleted successfully."]);
        }

        return redirect()->route('super-admin.roles.index')->with('success', "Role '{$name}' deleted successfully.");
    }

    public function clone(Role $role)
    {
        $baseName = $role->name . ' (Clone)';
        $newName = $baseName;
        $counter = 1;
        while (Role::where('name', $newName)->exists()) {
            $newName = $role->name . " (Clone {$counter})";
            $counter++;
        }

        $newRole = Role::create([
            'name' => $newName,
            'guard_name' => $role->guard_name ?: 'web'
        ]);

        $newRole->syncPermissions($role->permissions);

        SuperAdminAuditLog::log('cloned_role', 'role', $newRole->id, ['from' => $role->name, 'new' => $newRole->name]);

        return back()->with('success', "Role cloned successfully as '{$newName}'.");
    }
}
