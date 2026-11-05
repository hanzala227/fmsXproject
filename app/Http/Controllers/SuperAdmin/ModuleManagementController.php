<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemModule;
use App\Models\ModulePackage;
use App\Models\FeatureFlag;
use App\Models\Company;
use App\Models\CompanyModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleManagementController extends Controller
{
    /**
     * Display a listing of system modules with real freight metrics and filters.
     */
    public function index(Request $request)
    {
        $query = SystemModule::with('companies');

        // Tab Filter
        $currentTab = $request->get('tab', 'all');
        if ($currentTab === 'freight') {
            $query->whereIn('category', ['Freight', 'Logistics']);
        } elseif ($currentTab === 'finance') {
            $query->where('category', 'Finance');
        } elseif ($currentTab === 'commercial') {
            $query->where('category', 'Commercial');
        } elseif ($currentTab === 'platform') {
            $query->whereIn('category', ['Core', 'Platform', 'Operations', 'Configuration']);
        }

        // Direct Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Direct Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search Filter (name, key, description, category)
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('key', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('category', 'like', $term);
            });
        }

        // Tab counts
        $counts = [
            'all'        => SystemModule::count(),
            'freight'    => SystemModule::whereIn('category', ['Freight', 'Logistics'])->count(),
            'finance'    => SystemModule::where('category', 'Finance')->count(),
            'commercial' => SystemModule::where('category', 'Commercial')->count(),
            'platform'   => SystemModule::whereIn('category', ['Core', 'Platform', 'Operations', 'Configuration'])->count(),
        ];

        // Overall Freight SaaS Stats
        $stats = [
            'total_modules'      => $counts['all'],
            'freight_modules'    => $counts['freight'],
            'total_tenants'      => Company::count(),
            'active_deployments' => CompanyModule::distinct('company_id', 'module_key')->count(),
            'health_pct'         => 100,
        ];

        // Unique Categories for dropdown
        $categories = SystemModule::select('category')->distinct()->pluck('category')->filter()->values();

        // Custom sort: Freight & Logistics first, then Finance, Commercial, Configuration, Operations, Core, Platform
        $modules = $query->orderByRaw("FIELD(category, 'Freight', 'Logistics', 'Finance', 'Commercial', 'Configuration', 'Operations', 'Core', 'Platform')")
            ->orderBy('id', 'asc')
            ->get();

        return view('super-admin.modules.index', compact(
            'modules',
            'counts',
            'stats',
            'categories',
            'currentTab'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|unique:system_modules,key',
            'category' => 'nullable|string',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['release_date'] = now();
        $data['status'] = $request->get('status', 'Active');

        SystemModule::create($data);
        return back()->with('success', 'Module created successfully.');
    }

    public function show(SystemModule $module)
    {
        $companies = $module->active_companies_list;

        return view('super-admin.modules.show', compact('module', 'companies'));
    }

    public function update(Request $request, SystemModule $module)
    {
        $request->validate([
            'name' => 'required|string',
            'category' => 'nullable|string',
            'version' => 'nullable|string',
            'status' => 'required|in:Active,Beta,Deprecated,Inactive',
            'description' => 'nullable|string',
        ]);

        $module->update($request->all());
        return back()->with('success', 'Module updated successfully.');
    }

    public function packages()
    {
        $packages = ModulePackage::all();
        $modules = SystemModule::all();
        return view('super-admin.modules.packages', compact('packages', 'modules'));
    }

    public function storePackage(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'key' => 'required|string|unique:module_packages,key',
            'price' => 'required|numeric',
            'module_keys' => 'nullable|array'
        ]);

        ModulePackage::create($request->all());
        return back()->with('success', 'Package created successfully.');
    }

    public function updatePackage(Request $request, ModulePackage $package)
    {
        $package->update($request->only(['name', 'description', 'price', 'module_keys', 'is_active']));
        return back()->with('success', 'Package updated successfully.');
    }

    public function companyModules(Request $request)
    {
        $companies = Company::with('modules')->paginate(20);
        $modules = SystemModule::where('status', '!=', 'Inactive')
            ->whereNotIn('category', ['Core', 'Operations', 'Platform'])
            ->get();
        $packages = ModulePackage::where('is_active', true)->get();
        
        $validModuleKeys = $modules->pluck('key')->toArray();

        return view('super-admin.modules.company', compact('companies', 'modules', 'packages', 'validModuleKeys'));
    }

    public function assignToCompany(Request $request, Company $company)
    {
        $request->validate([
            'module_keys' => 'nullable|array'
        ]);

        DB::transaction(function () use ($company, $request) {
            // Remove existing modules
            CompanyModule::where('company_id', $company->id)->delete();
            
            // Add new modules
            if ($request->module_keys) {
                $inserts = array_map(function($key) use ($company) {
                    return [
                        'company_id' => $company->id,
                        'module_key' => $key,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }, $request->module_keys);
                
                CompanyModule::insert($inserts);
            }
        });

        return back()->with('success', 'Modules updated for company.');
    }

    public function applyPackageToCompany(Request $request, Company $company)
    {
        $request->validate(['package_id' => 'required|exists:module_packages,id']);
        $package = ModulePackage::find($request->package_id);

        if ($package->module_keys) {
            DB::transaction(function () use ($company, $package) {
                CompanyModule::where('company_id', $company->id)->delete();
                $inserts = array_map(function($key) use ($company) {
                    return ['company_id' => $company->id, 'module_key' => $key, 'created_at' => now(), 'updated_at' => now()];
                }, $package->module_keys);
                CompanyModule::insert($inserts);
            });
        }
        
        return back()->with('success', 'Package applied successfully. Company modules updated.');
    }

    public function featureFlags()
    {
        $flags = FeatureFlag::all();
        return view('super-admin.modules.feature_flags', compact('flags'));
    }

    public function storeFeatureFlag(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'key' => 'required|string|unique:feature_flags,key',
            'is_active' => 'boolean'
        ]);
        
        FeatureFlag::create([
            'name' => $request->name,
            'key' => $request->key,
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);
        return back()->with('success', 'Feature flag created.');
    }

    public function toggleFeatureFlag(FeatureFlag $flag)
    {
        $flag->update(['is_active' => !$flag->is_active]);
        return back()->with('success', 'Feature flag toggled.');
    }
}
