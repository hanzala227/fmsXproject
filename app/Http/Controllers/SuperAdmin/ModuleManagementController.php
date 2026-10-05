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
    public function index()
    {
        $modules = SystemModule::orderBy('category')->orderBy('name')->get();
        return view('super-admin.modules.index', compact('modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|unique:system_modules,key',
            'category' => 'nullable|string',
            'version' => 'nullable|string',
        ]);

        SystemModule::create($request->all());
        return back()->with('success', 'Module created successfully.');
    }

    public function show(SystemModule $module)
    {
        $companies = Company::whereHas('modules', function ($query) use ($module) {
            $query->where('module_key', $module->key);
        })->get();

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
