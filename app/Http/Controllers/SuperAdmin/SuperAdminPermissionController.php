<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\SuperAdminAuditLog;

class SuperAdminPermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $permissions = $query->paginate(30)->withQueryString();

        return view('super-admin.permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'module_name' => 'required|array',
            'module_name.*' => 'string|max:255',
        ]);

        $validated['module_name'] = implode(', ', $validated['module_name']);
        $validated['guard_name'] = 'web'; // Force default guard for Spatie

        $permission = Permission::create($validated);

        SuperAdminAuditLog::log('created_permission', 'permission', $permission->id, ['name' => $permission->name]);

        return back()->with('success', 'Permission created successfully.');
    }
    
    public function destroy(Permission $permission)
    {
        SuperAdminAuditLog::log('deleted_permission', 'permission', $permission->id, ['name' => $permission->name]);
        $permission->delete();
        return response()->json(['success' => true, 'message' => 'Permission deleted successfully.']);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'module_name' => 'required|array',
            'module_name.*' => 'string|max:255',
        ]);

        $validated['module_name'] = implode(', ', $validated['module_name']);
        
        $permission->update($validated);

        SuperAdminAuditLog::log('updated_permission', 'permission', $permission->id, ['name' => $permission->name]);

        return back()->with('success', 'Permission updated successfully.');
    }
}
