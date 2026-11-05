<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    public static function generateUserId()
    {
        $maxNum = 0;
        $users = User::whereNotNull('user_id')->get();
        foreach ($users as $u) {
            if (preg_match('/USR-(\d+)/i', $u->user_id, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        
        $nextNum = max($maxNum + 1, User::count() + 1);
        return 'USR-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        $currentUser = auth()->user();

        // Backfill missing user_ids for existing users
        $usersWithoutId = User::whereNull('user_id')->orWhere('user_id', '')->orderBy('id', 'asc')->get();
        foreach ($usersWithoutId as $u) {
            $u->user_id = self::generateUserId();
            $u->save();
        }

        if ($currentUser->isSuperAdmin()) {
            $users = User::orderBy('created_at', 'desc')->get();
        } else {
            // Freight multi-tenancy & security: strictly show only users of current admin's company
            // Admin 1's employees will NEVER show to Admin 2
            $users = User::where('company_id', $currentUser->company_id)
                ->where('id', '!=', $currentUser->id)
                ->where(function ($q) use ($currentUser) {
                    $q->where('created_by_id', $currentUser->id)
                      ->orWhereNull('created_by_id');
                })
                ->whereNotIn('role', ['SuperAdmin', 'client', 'Customer'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Available modules for this company
        $allModules = config('modules', [
            'ocean-import'   => ['label' => 'Ocean Import',   'icon' => 'fa-ship'],
            'ocean-export'   => ['label' => 'Ocean Export',   'icon' => 'fa-anchor'],
            'air-import'     => ['label' => 'Air Import',     'icon' => 'fa-plane'],
            'air-export'     => ['label' => 'Air Export',     'icon' => 'fa-plane'],
            'truck'          => ['label' => 'Truck Shipment', 'icon' => 'fa-truck'],
            'warehouse'      => ['label' => 'Warehouse',      'icon' => 'fa-building'],
            'accounting'     => ['label' => 'Accounting',     'icon' => 'fa-calculator'],
            'sales'          => ['label' => 'Sales',          'icon' => 'fa-briefcase'],
            'trade-partners' => ['label' => 'Customers',      'icon' => 'fa-handshake-o'],
            'reports'        => ['label' => 'Reports',        'icon' => 'fa-bar-chart'],
            'settings'       => ['label' => 'Settings',       'icon' => 'fa-cogs'],
        ]);

        $availableModules = [];
        foreach ($allModules as $key => $mod) {
            if ($currentUser->isSuperAdmin() || ($currentUser->company && $currentUser->company->hasModule($key))) {
                $availableModules[$key] = $mod;
            }
        }

        return view('settings.user-management', compact('users', 'availableModules'));
    }

    public function store(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $userId = !empty($request->user_id) ? trim($request->user_id) : self::generateUserId();
            
            $validated = $request->validate([
                'user_id' => 'nullable|string|max:50|unique:users,user_id',
                'first_name' => 'required|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'nullable|string|min:6',
                'office_code' => 'nullable|string|max:50',
                'office_name' => 'nullable|string|max:150',
                'department_code' => 'nullable|string|max:50',
                'department_name' => 'nullable|string|max:150',
                'branch' => 'nullable|string|max:100',
                'role' => 'nullable|string|max:100',
                'status' => 'nullable|in:Enable,Disable',
            ]);

            $validated['user_id'] = $userId;
            $validated['last_name'] = $validated['last_name'] ?? '';
            $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);
            
            if (!empty($validated['office_code']) || !empty($validated['office_name'])) {
                $matchedOffice = $this->syncOfficeRecord($validated['office_code'] ?? '', $validated['office_name'] ?? '');
                if ($matchedOffice) {
                    $validated['office_code'] = $matchedOffice->code;
                    $validated['office_name'] = $matchedOffice->name;
                    if (empty($validated['branch'])) {
                        $validated['branch'] = $matchedOffice->name;
                    }
                }
            }

            $rawPassword = !empty($validated['password']) ? $validated['password'] : 'Password123!';
            $validated['password'] = Hash::make($rawPassword);
            $validated['role'] = !empty($validated['role']) ? trim($validated['role']) : 'Operation';
            $validated['status'] = $validated['status'] ?? 'Enable';
            $validated['create_date'] = now();

            // Link employee to current admin and company
            $validated['company_id'] = $currentUser->company_id;
            $validated['created_by_id'] = $currentUser->id;

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::findOrFail($id);

            // Tenant security check
            if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: User belongs to another company.'
                ], 403);
            }

            $validated = $request->validate([
                'user_id' => 'nullable|string|max:50|unique:users,user_id,' . $id,
                'first_name' => 'required|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'required|email|unique:users,email,' . $id,
                'office_code' => 'nullable|string|max:50',
                'office_name' => 'nullable|string|max:150',
                'department_code' => 'nullable|string|max:50',
                'department_name' => 'nullable|string|max:150',
                'branch' => 'nullable|string|max:100',
                'role' => 'nullable|string|max:100',
                'status' => 'nullable|in:Enable,Disable',
            ]);

            if (empty($validated['user_id'])) {
                $validated['user_id'] = $user->user_id ?? self::generateUserId();
            }

            $validated['last_name'] = $validated['last_name'] ?? '';
            $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);

            if (!empty($validated['office_code']) || !empty($validated['office_name'])) {
                $matchedOffice = $this->syncOfficeRecord($validated['office_code'] ?? '', $validated['office_name'] ?? '');
                if ($matchedOffice) {
                    $validated['office_code'] = $matchedOffice->code;
                    $validated['office_name'] = $matchedOffice->name;
                    if (empty($validated['branch'])) {
                        $validated['branch'] = $matchedOffice->name;
                    }
                }
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkSave(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $usersData = $request->input('users', []);
            $savedUsers = [];

            foreach ($usersData as $userData) {
                $id = $userData['id'] ?? null;
                $firstName = trim($userData['first_name'] ?? '');
                $lastName = trim($userData['last_name'] ?? '');
                $email = trim($userData['email'] ?? '');

                if (empty($firstName) && empty($email)) {
                    continue; // Skip empty rows
                }

                if (empty($firstName)) {
                    $firstName = 'User';
                }

                $officeCode = trim($userData['office_code'] ?? '');
                $officeName = trim($userData['office_name'] ?? '');
                $departmentCode = trim($userData['department_code'] ?? '');
                $departmentName = trim($userData['department_name'] ?? '');

                if (!empty($officeCode) || !empty($officeName)) {
                    $matchedOffice = $this->syncOfficeRecord($officeCode, $officeName);
                    if ($matchedOffice) {
                        $officeCode = $matchedOffice->code;
                        $officeName = $matchedOffice->name;
                    }
                }

                $payload = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => trim($firstName . ' ' . $lastName),
                    'email' => $email,
                    'office_code' => $officeCode,
                    'office_name' => $officeName,
                    'department_code' => $departmentCode,
                    'department_name' => $departmentName,
                    'branch' => !empty($officeName) ? $officeName : trim($userData['branch'] ?? ''),
                    'role' => !empty($userData['role']) ? trim($userData['role']) : 'Operation',
                    'status' => $userData['status'] ?? 'Enable',
                ];

                if ($id && is_numeric($id) && intval($id) > 0) {
                    $user = User::find($id);
                    if ($user) {
                        // Tenant security check
                        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
                            continue;
                        }

                        if (!empty($userData['user_id'])) {
                            $payload['user_id'] = trim($userData['user_id']);
                        }
                        $user->update($payload);
                        $savedUsers[] = $user;
                    }
                } else {
                    $payload['user_id'] = !empty($userData['user_id']) ? trim($userData['user_id']) : self::generateUserId();
                    if (empty($payload['email'])) {
                        $payload['email'] = strtolower($payload['user_id']) . '@fms.com';
                    }
                    $password = !empty($userData['password']) ? $userData['password'] : 'Password123!';
                    $payload['password'] = Hash::make($password);
                    $payload['create_date'] = now();

                    // Multi-tenancy link to current admin and company
                    $payload['company_id'] = $currentUser->company_id;
                    $payload['created_by_id'] = $currentUser->id;

                    $user = User::create($payload);
                    $savedUsers[] = $user;
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($savedUsers) . ' user(s) saved successfully',
                'users' => $savedUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::findOrFail($id);

            // Tenant security check
            if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request, $id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::findOrFail($id);

            // Tenant security check
            if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            $validated = $request->validate([
                'new_password' => ['required', 'confirmed', Password::min(6)],
            ]);

            $user->password = Hash::make($validated['new_password']);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateModuleAccess(Request $request, $id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::findOrFail($id);

            // Tenant security check: Admin 1 cannot touch Admin 2's employees
            if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: This user belongs to another company.'
                ], 403);
            }

            $modules = $request->input('modules', []);
            if (!is_array($modules)) {
                $modules = [];
            }

            // Freight standard security: Only modules subscribed by the company can be assigned
            if ($currentUser->company) {
                $modules = array_filter($modules, function ($mod) use ($currentUser) {
                    return $currentUser->company->hasModule($mod);
                });
            }

            $user->accessible_modules = array_values($modules);
            $user->save();

            // Synchronize with Spatie permissions
            $moduleToName = [
                'ocean-import'   => 'Ocean Import',
                'ocean-export'   => 'Ocean Export',
                'air-import'     => 'Air Import',
                'air-export'     => 'Air Export',
                'truck'          => 'Truck Shipment',
                'warehouse'      => 'Warehouse & Distribution',
                'accounting'     => 'Accounting',
                'sales'          => 'CRM',
                'trade-partners' => 'Customers',
                'reports'        => 'Reports',
                'settings'       => 'Settings',
            ];

            $permNames = [];
            foreach ($modules as $mKey) {
                $displayName = $moduleToName[$mKey] ?? ucfirst($mKey);
                $perm = \Spatie\Permission\Models\Permission::firstOrCreate(
                    ['name' => 'Module: ' . $displayName, 'guard_name' => 'web'],
                    ['module_name' => $displayName]
                );
                $permNames[] = $perm->name;
            }
            $user->syncPermissions($permNames);

            return response()->json([
                'success' => true,
                'message' => 'Module access updated successfully.',
                'count' => count($user->accessible_modules),
                'modules' => $user->accessible_modules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module access: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportCsv(Request $request)
    {
        $currentUser = auth()->user();
        if ($currentUser->isSuperAdmin()) {
            $users = User::orderBy('created_at', 'desc')->get();
        } else {
            $users = User::where('company_id', $currentUser->company_id)
                ->where('id', '!=', $currentUser->id)
                ->where(function ($q) use ($currentUser) {
                    $q->where('created_by_id', $currentUser->id)
                      ->orWhereNull('created_by_id');
                })
                ->whereNotIn('role', ['SuperAdmin', 'client', 'Customer'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $filename = 'users-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'User ID', 'First Name', 'Last Name', 'Email',
                'Office Code', 'Office Name', 'Department Code', 'Department Name',
                'Branch', 'Role', 'Status', 'Create Date'
            ]);

            // Data rows
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->user_id,
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $user->office_code,
                    $user->office_name,
                    $user->department_code,
                    $user->department_name,
                    $user->branch,
                    $user->role,
                    $user->status,
                    $user->create_date ? (is_string($user->create_date) ? $user->create_date : $user->create_date->format('m-d-Y')) : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper to find or automatically create an Office record when configured in settings
     */
    protected function syncOfficeRecord(string $officeCode, string $officeName = ''): ?\App\Models\Office
    {
        $officeCode = trim($officeCode);
        $officeName = trim($officeName);

        if (empty($officeCode) && empty($officeName)) {
            return null;
        }

        // Try finding existing office by code or name
        $office = \App\Models\Office::where('code', $officeCode)
            ->orWhere('name', 'like', '%' . $officeCode . '%')
            ->first();

        if (!$office && !empty($officeName)) {
            $office = \App\Models\Office::where('name', 'like', '%' . $officeName . '%')->first();
        }

        if (!$office) {
            $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $officeCode));
            if (strlen($code) > 6) {
                $code = strtoupper(substr($code, 0, 3));
            }
            if (empty($code)) {
                $code = 'OFF';
            }
            $name = !empty($officeName) ? $officeName : ucfirst($officeCode) . ' Office';

            $office = \App\Models\Office::create([
                'code' => $code,
                'name' => $name,
                'is_active' => true,
            ]);
        }

        return $office;
    }
}


