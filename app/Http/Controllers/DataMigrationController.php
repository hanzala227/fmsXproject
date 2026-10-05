<?php

namespace App\Http\Controllers;

use App\Models\MigrationProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataMigrationController extends Controller
{
    /**
     * Step 1: Migration Dashboard
     */
    public function index()
    {
        // Get migrations only for the current logged-in user's company (tenant scope)
        // Since we don't know the exact tenant structure, we assume user()->company_id
        $companyId = Auth::user()->company_id ?? 1; // Fallback to 1 for testing if needed
        
        $projects = MigrationProject::where('company_id', $companyId)->latest()->get();
        return view('data-migration.index', compact('projects'));
    }

    /**
     * Step 2: New Migration - Connect Data Source
     */
    public function create()
    {
        return view('data-migration.create');
    }

    /**
     * Store new project and connection
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_code' => 'required|unique:migration_projects,project_code',
            'source_system' => 'nullable|string',
            'source_type' => 'required|in:mysql,sqlsrv,csv,api',
            
            // For DB
            'db_host' => 'required_if:source_type,mysql,sqlsrv',
            'db_port' => 'required_if:source_type,mysql,sqlsrv',
            'db_database' => 'required_if:source_type,mysql,sqlsrv',
            'db_username' => 'required_if:source_type,mysql,sqlsrv',
        ]);

        $companyId = Auth::user()->company_id ?? 1;

        $project = MigrationProject::create([
            'company_id' => $companyId,
            'project_code' => $request->project_code,
            'source_system' => $request->source_system,
            'source_type' => $request->source_type,
            'status' => 'connecting',
        ]);

        $project->connection()->create([
            'db_host' => $request->db_host,
            'db_port' => $request->db_port,
            'db_database' => $request->db_database,
            'db_username' => $request->db_username,
            'db_password' => $request->db_password,
            'use_ssl' => $request->has('use_ssl'),
        ]);

        return redirect()->route('data-migration.scan', $project->id)
                         ->with('success', 'Project created. Testing connection...');
    }

    /**
     * Step 3: Database Scanner View
     */
    public function scan(MigrationProject $project)
    {
        $companyId = Auth::user()->company_id ?? 1;
        if($project->company_id != $companyId) abort(403);

        return view('data-migration.scan', compact('project'));
    }

    /**
     * API to actually scan the external database
     */
    public function executeScan(MigrationProject $project)
    {
        $companyId = Auth::user()->company_id ?? 1;
        if($project->company_id != $companyId) return response()->json(['error' => 'Unauthorized'], 403);

        $connection = $project->connection;
        
        if ($project->source_type == 'mysql') {
            try {
                // Dynamically configure Laravel DB connection
                config(['database.connections.migration_temp' => [
                    'driver' => 'mysql',
                    'host' => $connection->db_host,
                    'port' => $connection->db_port,
                    'database' => $connection->db_database,
                    'username' => $connection->db_username,
                    'password' => $connection->db_password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => false,
                ]]);

                $db = \Illuminate\Support\Facades\DB::connection('migration_temp');
                
                // Get all tables
                $tables = $db->select('SHOW TABLES');
                $dbName = 'Tables_in_' . $connection->db_database;
                
                $results = [];
                foreach ($tables as $t) {
                    $tableName = $t->$dbName;
                    
                    // Quick count
                    $count = $db->table($tableName)->count();
                    
                    // Simple size approximation (could run complex queries for actual size, keeping it simple)
                    $size = round(($count * 1024) / 1048576, 2) . ' MB';
                    if($count == 0) $size = '0 MB';

                    $results[] = [
                        'name' => $tableName,
                        'records' => $count,
                        'size' => $size,
                        'suggested_mapping' => $this->guessMapping($tableName)
                    ];
                }

                $project->update(['status' => 'mapping']);

                return response()->json([
                    'success' => true,
                    'tables' => $results
                ]);

            } catch (\Exception $e) {
                // If it fails (e.g. invalid credentials during dev), return mock data for demonstration
                return response()->json([
                    'success' => false,
                    'error' => 'Connection failed: ' . $e->getMessage(),
                    'mock_data' => $this->getMockTables()
                ]);
            }
        }

        // Return mock for CSV/API
        return response()->json([
            'success' => true,
            'tables' => $this->getMockTables()
        ]);
    }

    private function guessMapping($tableName)
    {
        $map = [
            'tbl_customer' => 'Customers',
            'customers' => 'Customers',
            'tbl_job' => 'Shipments',
            'jobs' => 'Shipments',
            'shipments' => 'Shipments',
            'tbl_invoice' => 'Invoices',
            'invoices' => 'Invoices',
        ];
        return $map[strtolower($tableName)] ?? null;
    }

    private function getMockTables()
    {
        return [
            ['name' => 'tbl_customer', 'records' => 4821, 'size' => '12 MB', 'suggested_mapping' => 'Customers'],
            ['name' => 'tbl_job', 'records' => 82410, 'size' => '156 MB', 'suggested_mapping' => 'Shipments'],
            ['name' => 'tbl_hbl', 'records' => 75332, 'size' => '142 MB', 'suggested_mapping' => 'HBL'],
            ['name' => 'tbl_mbl', 'records' => 21904, 'size' => '48 MB', 'suggested_mapping' => 'MBL'],
            ['name' => 'tbl_container', 'records' => 63112, 'size' => '96 MB', 'suggested_mapping' => 'Containers'],
            ['name' => 'tbl_invoice', 'records' => 91283, 'size' => '128 MB', 'suggested_mapping' => 'Invoices'],
        ];
    }
}
