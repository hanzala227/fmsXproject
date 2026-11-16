<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\SuperAdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * List all freight branches across companies with search & filters
     */
    public function index(Request $request)
    {
        $query = Branch::with('company');

        // Company filter
        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        // Branch type filter
        if ($type = $request->input('branch_type')) {
            $query->where('branch_type', $type);
        }

        // Status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $statusVal = $request->input('status');
            if ($statusVal === 'active') {
                $query->where('is_active', true);
            } elseif ($statusVal === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search query
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('iata_code', 'like', "%{$search}%")
                  ->orWhere('unlocode', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $branches = $query->latest()->paginate(15)->withQueryString();

        // Summary KPI counts
        $baseQuery = Branch::query();
        if ($companyId) {
            $baseQuery->where('company_id', $companyId);
        }

        $kpis = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'head_offices' => (clone $baseQuery)->where('is_head_office', true)->count(),
            'air_stations' => (clone $baseQuery)->where('branch_type', 'Airport Station')->count(),
            'sea_stations' => (clone $baseQuery)->where('branch_type', 'Seaport Station')->count(),
            'regional_hubs' => (clone $baseQuery)->where('branch_type', 'Regional Hub')->count(),
        ];

        $companies = Company::orderBy('name')->get(['id', 'name', 'code', 'country']);

        $branchTypes = [
            'Head Office',
            'Regional Hub',
            'Airport Station',
            'Seaport Station',
            'CFS / Bonded Warehouse',
            'Land Transport Hub',
            'Sales Office',
        ];

        return view('super-admin.branches.index', compact('branches', 'companies', 'kpis', 'branchTypes'));
    }

    /**
     * Show create branch form
     */
    public function create(Request $request)
    {
        $companies = Company::orderBy('name')->get(['id', 'name', 'code', 'country']);
        $selectedCompanyId = $request->input('company_id');

        $branchTypes = [
            'Head Office',
            'Regional Hub',
            'Airport Station',
            'Seaport Station',
            'CFS / Bonded Warehouse',
            'Land Transport Hub',
            'Sales Office',
        ];

        $timezones = [
            'UTC' => 'UTC (GMT+0)',
            'Asia/Karachi' => 'Asia/Karachi (PKT +05:00)',
            'Asia/Dubai' => 'Asia/Dubai (GST +04:00)',
            'Asia/Singapore' => 'Asia/Singapore (SGT +08:00)',
            'Asia/Shanghai' => 'Asia/Shanghai (CST +08:00)',
            'Europe/London' => 'Europe/London (GMT/BST)',
            'Europe/Frankfurt' => 'Europe/Frankfurt (CET/CEST)',
            'Europe/Amsterdam' => 'Europe/Amsterdam (CET/CEST)',
            'America/New_York' => 'America/New_York (EST/EDT)',
            'America/Chicago' => 'America/Chicago (CST/CDT)',
            'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)',
        ];

        $currencies = ['USD', 'PKR', 'AED', 'EUR', 'GBP', 'CAD', 'CNY', 'SGD'];

        return view('super-admin.branches.form', [
            'branch' => null,
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'branchTypes' => $branchTypes,
            'timezones' => $timezones,
            'currencies' => $currencies,
        ]);
    }

    /**
     * Store new branch
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches')->where(fn ($query) => $query->where('company_id', $request->company_id)->whereNull('deleted_at')),
            ],
            'name' => 'required|string|max:255',
            'branch_type' => 'required|string|max:50',
            'is_head_office' => 'nullable|boolean',
            'iata_code' => 'nullable|string|max:10',
            'unlocode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'manager_name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'tax_number' => 'nullable|string|max:100',
            'customs_code' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        if (!empty($validated['iata_code'])) {
            $validated['iata_code'] = strtoupper(trim($validated['iata_code']));
        }
        if (!empty($validated['unlocode'])) {
            $validated['unlocode'] = strtoupper(trim($validated['unlocode']));
        }

        $validated['is_head_office'] = $request->boolean('is_head_office');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        // If this branch is set as head office, unset previous head office for this company
        if ($validated['is_head_office']) {
            Branch::where('company_id', $validated['company_id'])
                ->where('is_head_office', true)
                ->update(['is_head_office' => false]);
        }

        $branch = Branch::create($validated);

        $company = Company::find($validated['company_id']);
        SuperAdminAuditLog::log('branch_created', 'branch', $branch->id, [
            'name' => $branch->name,
            'code' => $branch->code,
            'company' => $company ? $company->name : 'N/A',
            'type' => $branch->branch_type,
        ]);

        return redirect()->route('super-admin.branches.show', $branch)
            ->with('success', "Branch '{$branch->name}' ({$branch->code}) registered successfully.");
    }

    /**
     * Show branch profile & station details
     */
    public function show(Branch $branch)
    {
        $branch->load('company');
        return view('super-admin.branches.show', compact('branch'));
    }

    /**
     * Edit branch form
     */
    public function edit(Branch $branch)
    {
        $companies = Company::orderBy('name')->get(['id', 'name', 'code', 'country']);

        $branchTypes = [
            'Head Office',
            'Regional Hub',
            'Airport Station',
            'Seaport Station',
            'CFS / Bonded Warehouse',
            'Land Transport Hub',
            'Sales Office',
        ];

        $timezones = [
            'UTC' => 'UTC (GMT+0)',
            'Asia/Karachi' => 'Asia/Karachi (PKT +05:00)',
            'Asia/Dubai' => 'Asia/Dubai (GST +04:00)',
            'Asia/Singapore' => 'Asia/Singapore (SGT +08:00)',
            'Asia/Shanghai' => 'Asia/Shanghai (CST +08:00)',
            'Europe/London' => 'Europe/London (GMT/BST)',
            'Europe/Frankfurt' => 'Europe/Frankfurt (CET/CEST)',
            'Europe/Amsterdam' => 'Europe/Amsterdam (CET/CEST)',
            'America/New_York' => 'America/New_York (EST/EDT)',
            'America/Chicago' => 'America/Chicago (CST/CDT)',
            'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)',
        ];

        $currencies = ['USD', 'PKR', 'AED', 'EUR', 'GBP', 'CAD', 'CNY', 'SGD'];

        return view('super-admin.branches.form', [
            'branch' => $branch,
            'companies' => $companies,
            'selectedCompanyId' => $branch->company_id,
            'branchTypes' => $branchTypes,
            'timezones' => $timezones,
            'currencies' => $currencies,
        ]);
    }

    /**
     * Update branch
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches')->where(fn ($query) => $query->where('company_id', $request->company_id)->whereNull('deleted_at'))->ignore($branch->id),
            ],
            'name' => 'required|string|max:255',
            'branch_type' => 'required|string|max:50',
            'is_head_office' => 'nullable|boolean',
            'iata_code' => 'nullable|string|max:10',
            'unlocode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'manager_name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'tax_number' => 'nullable|string|max:100',
            'customs_code' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        if (!empty($validated['iata_code'])) {
            $validated['iata_code'] = strtoupper(trim($validated['iata_code']));
        }
        if (!empty($validated['unlocode'])) {
            $validated['unlocode'] = strtoupper(trim($validated['unlocode']));
        }

        $validated['is_head_office'] = $request->boolean('is_head_office');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        // If newly designated as head office, unset previous
        if ($validated['is_head_office'] && !$branch->is_head_office) {
            Branch::where('company_id', $validated['company_id'])
                ->where('id', '!=', $branch->id)
                ->where('is_head_office', true)
                ->update(['is_head_office' => false]);
        }

        $branch->update($validated);

        SuperAdminAuditLog::log('branch_updated', 'branch', $branch->id, [
            'name' => $branch->name,
            'code' => $branch->code,
            'is_active' => $branch->is_active,
            'type' => $branch->branch_type,
        ]);

        return redirect()->route('super-admin.branches.show', $branch)
            ->with('success', "Branch '{$branch->name}' updated successfully.");
    }

    /**
     * Delete branch
     */
    public function destroy(Branch $branch)
    {
        $name = $branch->name;
        $code = $branch->code;
        $companyId = $branch->company_id;

        $branch->delete();

        SuperAdminAuditLog::log('branch_deleted', 'branch', $branch->id, [
            'name' => $name,
            'code' => $code,
            'company_id' => $companyId,
        ]);

        return redirect()->route('super-admin.branches.index')
            ->with('success', "Branch '{$name}' ({$code}) has been removed.");
    }

    /**
     * Quick toggle active / inactive status
     */
    public function toggleStatus(Branch $branch)
    {
        $branch->is_active = !$branch->is_active;
        $branch->save();

        SuperAdminAuditLog::log('branch_status_toggled', 'branch', $branch->id, [
            'name' => $branch->name,
            'is_active' => $branch->is_active,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $branch->is_active,
                'message' => "Branch status changed to " . ($branch->is_active ? 'Active' : 'Inactive'),
            ]);
        }

        return back()->with('success', "Branch status changed to " . ($branch->is_active ? 'Active' : 'Inactive'));
    }
}
