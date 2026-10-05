<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MigrationProject;
use App\Models\Company;
use Illuminate\Http\Request;

class DataMigrationController extends Controller
{
    /**
     * Step 1: Migration Dashboard
     */
    public function index()
    {
        $projects = MigrationProject::with('company')->latest()->get();
        return view('super-admin.data-migration.index', compact('projects'));
    }

    /**
     * Step 2: New Migration - Connect Data Source
     */
    public function create()
    {
        $companies = Company::all();
        return view('super-admin.data-migration.create', compact('companies'));
    }

    /**
     * Store new project and connection
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'project_code' => 'required|unique:migration_projects,project_code',
            'source_system' => 'nullable|string',
            'source_type' => 'required|in:mysql,sqlsrv,csv,api',
            
            // For DB
            'db_host' => 'required_if:source_type,mysql,sqlsrv',
            'db_port' => 'required_if:source_type,mysql,sqlsrv',
            'db_database' => 'required_if:source_type,mysql,sqlsrv',
            'db_username' => 'required_if:source_type,mysql,sqlsrv',
        ]);

        $project = MigrationProject::create([
            'company_id' => $request->company_id,
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

        return redirect()->route('super-admin.data-migration.scan', $project->id)
                         ->with('success', 'Project created. Testing connection...');
    }

    /**
     * Step 3: Database Scanner
     */
    public function scan(MigrationProject $project)
    {
        // For now, load view. Later, we'll actually scan the DB and use AI.
        return view('super-admin.data-migration.scan', compact('project'));
    }
}
