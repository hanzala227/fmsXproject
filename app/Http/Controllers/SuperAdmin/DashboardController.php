<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SuperAdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Overview tab (default)
     */
    public function index()
    {
        $stats = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('subscription_status', 'active')->count(),
            'trial_companies' => Company::where('subscription_status', 'trial')->count(),
            'suspended_companies' => Company::where('subscription_status', 'suspended')->count(),
            'total_users' => User::where('role', '!=', 'SuperAdmin')->count(),
            'open_bugs' => 0, // Placeholder
            'support_tickets' => 0, // Placeholder
            'active_migrations' => 0, // Placeholder
            'monthly_revenue' => 0, // Placeholder
            
            'recent_companies' => Company::latest()->take(5)->get(),
            'recent_logs' => SuperAdminAuditLog::with('user')->latest('created_at')->take(10)->get(),
            
            // Placeholder for charts
            'monthly_growth' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => [12, 19, 3, 5, 2, 3],
            ]
        ];

        return view('super-admin.dashboard.index', compact('stats'));
    }

    /**
     * Reports tab
     */
    public function reports()
    {
        // Aggregate usage data
        $reports = [
            'total_migrations' => 0, // Placeholder until migrations module is built
            'active_tickets' => 0,   // Placeholder
            'open_bugs' => 0,        // Placeholder
        ];
        
        return view('super-admin.dashboard.reports', compact('reports'));
    }

    /**
     * System Status tab
     */
    public function systemStatus()
    {
        // Get basic server status
        $status = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_os' => php_uname('s'),
            'database_status' => 'Connected',
            'queue_status' => 'Running',
        ];

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $status['database_status'] = 'Disconnected';
        }

        return view('super-admin.dashboard.system-status', compact('status'));
    }
}
