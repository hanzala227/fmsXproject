<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\SuperAdminAuditLog;

class SuperAdminMatrixController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        
        // Group permissions by their prefix/module if possible (e.g. "users.create", "companies.view")
        // For simplicity, we just pass all to the view, the view can handle categorization
        
        return view('super-admin.roles.matrix', compact('roles', 'permissions'));
    }

    public function update(Request $request)
    {
        $matrix = $request->input('matrix', []);
        
        $roles = Role::all();
        foreach ($roles as $role) {
            if (isset($matrix[$role->id])) {
                $permissionIds = array_keys($matrix[$role->id]);
                // Convert IDs to permission names or pass objects
                $perms = Permission::whereIn('id', $permissionIds)->get();
                $role->syncPermissions($perms);
            } else {
                $role->syncPermissions([]);
            }
        }

        SuperAdminAuditLog::log('updated_role_matrix', 'system', null, ['details' => 'Role permissions matrix updated']);

        return back()->with('success', 'Role permissions updated successfully.');
    }
}
