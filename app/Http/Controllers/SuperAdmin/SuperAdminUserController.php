<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\SuperAdminAuditLog;
use App\Models\ActivityLog;
use App\Models\SystemModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuperAdminUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Apply Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply Company Filter
        if ($request->filled('company_id')) {
            if ($request->company_id === 'system') {
                $query->whereNull('company_id');
            } else {
                $query->where('company_id', $request->company_id);
            }
        }

        // Apply Role Filter
        if ($request->filled('role')) {
            $role = $request->role;
            if (in_array($role, ['Administrators', 'Admin'])) {
                $query->where('role', 'Admin');
            } elseif (in_array($role, ['Operations', 'Operation', 'Employees'])) {
                $query->where('role', 'Operation');
            } elseif (in_array($role, ['Clients', 'client', 'Customers', 'Customer'])) {
                $query->whereIn('role', ['client', 'Customer']);
            } elseif (in_array($role, ['SuperAdmins', 'SuperAdmin'])) {
                $query->where('role', 'SuperAdmin');
            } elseif ($role === 'Support Team') {
                $query->where('role', 'Support');
            } elseif ($role === 'Developers') {
                $query->where('role', 'Developer');
            } else {
                $query->where('role', $role);
            }
        }

        $users = $query->with('company')->latest()->paginate(15)->withQueryString();

        $tabCounts = [
            '' => User::count(),
            'Administrators' => User::where('role', 'Admin')->count(),
            'Operations' => User::where('role', 'Operation')->count(),
            'Clients' => User::whereIn('role', ['client', 'Customer'])->count(),
            'SuperAdmins' => User::where('role', 'SuperAdmin')->count(),
        ];

        $companies = Company::orderBy('name')->get();

        return view('super-admin.users.index', compact('users', 'tabCounts', 'companies'));
    }

    /**
     * Export users directory to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = User::with('company');

        if ($request->filled('role')) {
            $role = $request->role;
            if (in_array($role, ['Administrators', 'Admin'])) {
                $query->where('role', 'Admin');
            } elseif (in_array($role, ['Operations', 'Operation', 'Employees'])) {
                $query->where('role', 'Operation');
            } elseif (in_array($role, ['Clients', 'client', 'Customers', 'Customer'])) {
                $query->whereIn('role', ['client', 'Customer']);
            } elseif (in_array($role, ['SuperAdmins', 'SuperAdmin'])) {
                $query->where('role', 'SuperAdmin');
            } else {
                $query->where('role', $role);
            }
        }

        $users = $query->latest()->get();

        $csv = "User ID,Name,Email,Company,Role,Department,Status,Last Login At,Last Login IP,Created At\n";
        foreach ($users as $u) {
            $csv .= implode(',', [
                $u->id,
                '"' . str_replace('"', '""', $u->name) . '"',
                '"' . $u->email . '"',
                '"' . str_replace('"', '""', $u->company?->name ?? 'System / Internal') . '"',
                '"' . $u->role . '"',
                '"' . ($u->department ?? 'N/A') . '"',
                $u->status,
                $u->last_login_at ? $u->last_login_at->format('Y-m-d H:i') : 'Never',
                '"' . ($u->last_login_ip ?? 'N/A') . '"',
                $u->created_at ? $u->created_at->format('Y-m-d') : 'N/A',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-directory-' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $offices = \App\Models\Office::where('is_active', 1)->orWhereNull('is_active')->orderBy('code')->get();
        $user = null; // for the form
        return view('super-admin.users.form', compact('user', 'companies', 'offices'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|string',
            'office_code' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'status' => 'required|in:Enable,Disable',
            'access_expiry' => 'nullable|date',
            'mfa_enabled' => 'boolean',
        ]);

        $office = !empty($validated['office_code']) ? \App\Models\Office::where('code', $validated['office_code'])->first() : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'office_code' => $office ? $office->code : ($validated['office_code'] ?? null),
            'office_name' => $office ? $office->name : null,
            'branch' => $office ? $office->name : null,
            'department' => $validated['department'],
            'company_id' => $validated['company_id'],
            'status' => $validated['status'],
            'access_expiry' => $validated['access_expiry'] ?? null,
            'mfa_enabled' => $request->has('mfa_enabled'),
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->update(['profile_photo_path' => $path]);
        }

        SuperAdminAuditLog::log('created_user', 'user', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $companies = Company::orderBy('name')->get();
        $offices = \App\Models\Office::where('is_active', 1)->orWhereNull('is_active')->orderBy('code')->get();
        return view('super-admin.users.form', compact('user', 'companies', 'offices'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'office_code' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'status' => 'required|in:Enable,Disable',
            'access_expiry' => 'nullable|date',
        ]);

        $office = !empty($validated['office_code']) ? \App\Models\Office::where('code', $validated['office_code'])->first() : null;

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'office_code' => $office ? $office->code : ($validated['office_code'] ?? null),
            'office_name' => $office ? $office->name : null,
            'branch' => $office ? $office->name : null,
            'department' => $validated['department'],
            'company_id' => $validated['company_id'],
            'status' => $validated['status'],
            'access_expiry' => $validated['access_expiry'] ?? null,
            'mfa_enabled' => $request->has('mfa_enabled'),
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->update(['profile_photo_path' => $path]);
        }

        SuperAdminAuditLog::log('updated_user', 'user', $user->id, [
            'name' => $user->name,
            'role' => $user->role
        ]);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Display the specified user details.
     */
    public function show(User $user)
    {
        $user->load(['company.branches', 'company.modules', 'roles.permissions', 'permissions']);
        
        // Tab 3: Dynamic user activity from activity_logs + super_admin_audit_logs
        $userLogs = ActivityLog::where('user_id', $user->id)
            ->latest('created_at')
            ->take(15)
            ->get();

        if ($userLogs->isEmpty()) {
            $userLogs = SuperAdminAuditLog::where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere(function($sub) use ($user) {
                          $sub->where('target_type', 'user')->where('target_id', $user->id);
                      });
                })
                ->latest('created_at')
                ->take(15)
                ->get();
        }
        $activityLogs = $userLogs;

        // Tab 4: Live concurrent user sessions directly from database
        $userSessions = Schema::hasTable('sessions')
            ? DB::table('sessions')->where('user_id', $user->id)->orderByDesc('last_activity')->get()
            : collect();

        // Tab 1: Dynamic Spatie and direct permissions
        $directPermissions = $user->getAllPermissions();

        // Tab 2: Dynamic System & Freight Modules directly from system_modules table
        $iconMap = [
            'ocean_import' => ['icon' => 'fa-ship', 'color' => '#0284c7'],
            'ocean_export' => ['icon' => 'fa-ship', 'color' => '#0369a1'],
            'air_import' => ['icon' => 'fa-plane', 'color' => '#6366f1'],
            'air_export' => ['icon' => 'fa-plane', 'color' => '#4f46e5'],
            'trucking' => ['icon' => 'fa-truck', 'color' => '#f59e0b'],
            'warehouse' => ['icon' => 'fa-archive', 'color' => '#8b5cf6'],
            'accounting' => ['icon' => 'fa-calculator', 'color' => '#10b981'],
            'customs' => ['icon' => 'fa-shield', 'color' => '#059669'],
            'master_data' => ['icon' => 'fa-database', 'color' => '#64748b'],
            'trade_partners' => ['icon' => 'fa-address-book-o', 'color' => '#06b6d4'],
            'settings' => ['icon' => 'fa-cog', 'color' => '#475569'],
            'crm' => ['icon' => 'fa-users', 'color' => '#ec4899'],
            'reports' => ['icon' => 'fa-bar-chart', 'color' => '#14b8a6'],
            'roles_rules' => ['icon' => 'fa-key', 'color' => '#4338ca'],
            'data_migration' => ['icon' => 'fa-exchange', 'color' => '#0891b2'],
            'bugs_issues' => ['icon' => 'fa-bug', 'color' => '#dc2626'],
            'support_center' => ['icon' => 'fa-life-ring', 'color' => '#ea580c'],
            'module_management' => ['icon' => 'fa-puzzle-piece', 'color' => '#9333ea'],
            'integrations' => ['icon' => 'fa-plug', 'color' => '#2563eb'],
        ];

        $rawModules = Schema::hasTable('system_modules')
            ? SystemModule::where('status', 'Active')
                ->whereIn('category', ['Freight', 'Logistics', 'Finance', 'Commercial', 'Configuration', 'Operations'])
                ->get()
            : collect();

        $companyModules = $user->company ? $user->company->modules->pluck('module_key')->toArray() : [];
        $userAccessible = is_array($user->accessible_modules) 
            ? $user->accessible_modules 
            : (is_string($user->accessible_modules) ? json_decode($user->accessible_modules, true) : []);

        $freightModules = $rawModules->map(function($m) use ($iconMap, $user, $companyModules, $userAccessible) {
            $meta = $iconMap[$m->key] ?? ['icon' => 'fa-cube', 'color' => '#0ab39c'];
            $isAssigned = false;

            if ($user->role === 'SuperAdmin') {
                $isAssigned = true;
            } elseif ($user->role === 'Admin') {
                $isAssigned = true;
            } elseif (!empty($userAccessible)) {
                $isAssigned = in_array($m->key, $userAccessible);
            } elseif (!empty($companyModules)) {
                $isAssigned = in_array($m->key, $companyModules);
            } else {
                $isAssigned = true; // standard tenant assignment
            }

            return [
                'name' => $m->name,
                'key' => $m->key,
                'category' => $m->category,
                'icon' => $meta['icon'],
                'color' => $meta['color'],
                'desc' => $m->description ?: 'Operational freight forwarding and tenant workspace execution.',
                'is_active' => $isAssigned,
            ];
        });

        return view('super-admin.users.show', compact('user', 'activityLogs', 'userSessions', 'directPermissions', 'freightModules'));
    }

    /**
     * Reset user password (AJAX).
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'confirmed', Password::min(8)]
        ]);

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        SuperAdminAuditLog::log('reset_password', 'user', $user->id, [
            'user' => $user->email
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    /**
     * Toggle user status (AJAX).
     */
    public function toggleStatus(Request $request, User $user)
    {
        $user->update([
            'status' => $user->status === 'Enable' ? 'Disable' : 'Enable'
        ]);

        SuperAdminAuditLog::log('toggled_user_status', 'user', $user->id, [
            'status' => $user->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated to ' . $user->status
        ]);
    }

    /**
     * Impersonate User.
     */
    public function loginAs(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        session(['superadmin_id' => Auth::id()]);
        Auth::login($user);

        return redirect()->route('dashboard'); // Assuming main app dashboard
    }

    /**
     * Delete User.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 403);
            }
            return redirect()->back()->with('error', 'Cannot delete your own account.');
        }

        $user->delete();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return redirect()->route('super-admin.users.index')->with('success', 'User deleted successfully.');
    }
}
