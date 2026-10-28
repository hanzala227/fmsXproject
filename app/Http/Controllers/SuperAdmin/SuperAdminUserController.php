<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\SuperAdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Apply Role Filter
        if ($request->filled('role')) {
            $role = $request->role;
            if ($role === 'Employees') {
                $query->where('role', 'Operation');
            } elseif ($role === 'Support Team') {
                $query->where('role', 'Support');
            } elseif ($role === 'Developers') {
                $query->where('role', 'Developer');
            } elseif ($role === 'Administrators') {
                $query->where('role', 'Admin');
            } elseif ($role === 'SuperAdmins') {
                $query->where('role', 'SuperAdmin');
            } else {
                $query->where('role', $role);
            }
        }

        $users = $query->with('company')->latest()->paginate(15)->withQueryString();

        return view('super-admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $user = null; // for the form
        return view('super-admin.users.form', compact('user', 'companies'));
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
            'department' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'status' => 'required|in:Enable,Disable',
            'access_expiry' => 'nullable|date',
            'mfa_enabled' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
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
        return view('super-admin.users.form', compact('user', 'companies'));
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
            'department' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'status' => 'required|in:Enable,Disable',
            'access_expiry' => 'nullable|date',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
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
        $user->load('company');
        
        $activityLogs = SuperAdminAuditLog::where('target_type', 'user')
            ->where('target_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('super-admin.users.show', compact('user', 'activityLogs'));
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
