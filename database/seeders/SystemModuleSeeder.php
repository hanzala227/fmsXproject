<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemModule;
use App\Models\ModulePackage;
use App\Models\FeatureFlag;

class SystemModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['name' => 'Dashboard', 'key' => 'dashboard', 'category' => 'Core'],
            ['name' => 'Companies', 'key' => 'companies', 'category' => 'Core'],
            ['name' => 'Accounts & Access', 'key' => 'accounts_access', 'category' => 'Core'],
            ['name' => 'Roles & Rules', 'key' => 'roles_rules', 'category' => 'Operations'],
            ['name' => 'Data Migration', 'key' => 'data_migration', 'category' => 'Operations'],
            ['name' => 'Bugs & Issues', 'key' => 'bugs_issues', 'category' => 'Operations'],
            ['name' => 'Support Center', 'key' => 'support_center', 'category' => 'Operations'],
            ['name' => 'Module Management', 'key' => 'module_management', 'category' => 'Platform'],
            ['name' => 'Integrations', 'key' => 'integrations', 'category' => 'Platform'],
            ['name' => 'Ocean Import', 'key' => 'ocean_import', 'category' => 'Freight'],
            ['name' => 'Ocean Export', 'key' => 'ocean_export', 'category' => 'Freight'],
            ['name' => 'Air Import', 'key' => 'air_import', 'category' => 'Freight'],
            ['name' => 'Air Export', 'key' => 'air_export', 'category' => 'Freight'],
            ['name' => 'Trucking', 'key' => 'trucking', 'category' => 'Logistics'],
            ['name' => 'Warehouse', 'key' => 'warehouse', 'category' => 'Logistics'],
            ['name' => 'Accounting', 'key' => 'accounting', 'category' => 'Finance'],
            ['name' => 'Master Data', 'key' => 'master_data', 'category' => 'Configuration']
        ];

        foreach ($modules as $mod) {
            SystemModule::updateOrCreate(
                ['key' => $mod['key']],
                [
                    'name' => $mod['name'],
                    'category' => $mod['category'],
                    'status' => 'Active',
                    'version' => '1.0.0',
                    'release_date' => now()
                ]
            );
        }

        ModulePackage::updateOrCreate(
            ['key' => 'basic'],
            [
                'name' => 'Basic Package',
                'description' => 'Essential freight operations.',
                'price' => 100.00,
                'module_keys' => ['dashboard', 'ocean_import', 'ocean_export', 'master_data']
            ]
        );

        ModulePackage::updateOrCreate(
            ['key' => 'pro'],
            [
                'name' => 'Pro Package',
                'description' => 'Full suite including air freight and accounting.',
                'price' => 300.00,
                'module_keys' => ['dashboard', 'ocean_import', 'ocean_export', 'air_import', 'air_export', 'trucking', 'warehouse', 'accounting', 'master_data']
            ]
        );
    }
}
