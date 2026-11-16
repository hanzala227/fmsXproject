<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\SuperAdminAuditLog;

class SuperAdminMatrixController extends Controller
{
    /**
     * Display Role Permission Matrix with module grouping and O(1) permission lookups.
     */
    public function index()
    {
        // Eager load permissions to avoid N+1 queries in the matrix grid
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        // Group permissions by module prefix (e.g. "users.view", "accounting.vouchers", etc.)
        $groupedPermissions = $permissions->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);
            if (count($parts) > 1) {
                return ucfirst($parts[0]);
            }
            $underscoreParts = explode('_', $perm->name);
            if (count($underscoreParts) > 1) {
                return ucfirst($underscoreParts[0]);
            }
            return 'General';
        });

        // Fast O(1) lookup table for [role_id][permission_id] => bool
        $rolePermMap = [];
        foreach ($roles as $role) {
            $permIds = $role->permissions->pluck('id')->flip()->toArray();
            $rolePermMap[$role->id] = $permIds;
        }

        $permissionsGrouped = $groupedPermissions;

        return view('super-admin.roles.matrix', compact('roles', 'permissions', 'permissionsGrouped', 'rolePermMap'));
    }

    /**
     * Update role permission assignments in bulk.
     */
    public function update(Request $request)
    {
        $matrix = $request->input('matrix', []);
        $allRoles = Role::all();
        $allPermissions = Permission::all()->keyBy('id');

        foreach ($allRoles as $role) {
            // Master protection: Do not strip permissions from SuperAdmin
            if (strcasecmp($role->name, 'SuperAdmin') === 0) {
                $role->syncPermissions($allPermissions);
                continue;
            }

            if (isset($matrix[$role->id]) && is_array($matrix[$role->id])) {
                $selectedIds = array_keys($matrix[$role->id]);
                $rolePermissions = $allPermissions->only($selectedIds);
                $role->syncPermissions($rolePermissions);
            } else {
                $role->syncPermissions([]);
            }
        }

        if (class_exists(SuperAdminAuditLog::class)) {
            SuperAdminAuditLog::log('updated_role_matrix', 'role', 0, [
                'details' => 'Role permissions matrix updated across all roles',
            ]);
        }

        return back()->with('success', 'Role permissions matrix updated successfully.');
    }
}
