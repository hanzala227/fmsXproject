<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\SuperAdminAuditLog;

class SuperAdminRoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('users');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $roles = $query->paginate(15)->withQueryString();

        return view('super-admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $role = null;
        $permissions = Permission::all();
        return view('super-admin.roles.form', compact('role', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'required|string|max:255',
        ]);

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
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('super-admin.roles.form', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'guard_name' => 'required|string|max:255',
        ]);

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
            return back()->with('error', 'Cannot delete SuperAdmin role.');
        }

        SuperAdminAuditLog::log('deleted_role', 'role', $role->id, ['name' => $role->name]);
        
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
    }

    public function clone(Role $role)
    {
        $newRole = Role::create([
            'name' => $role->name . ' (Clone)',
            'guard_name' => $role->guard_name
        ]);

        $newRole->syncPermissions($role->permissions);

        SuperAdminAuditLog::log('cloned_role', 'role', $newRole->id, ['from' => $role->name, 'new' => $newRole->name]);

        return back()->with('success', 'Role cloned successfully.');
    }
}
