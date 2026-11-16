<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReleaseManagementController extends Controller
{
    /**
     * Show Releases, Version Changelogs, and Database Migration Status
     */
    public function index()
    {
        $totalMigrations = DB::table('migrations')->count();
        $lastBatch = DB::table('migrations')->max('batch');
        $recentMigrations = DB::table('migrations')->latest('id')->take(8)->get();

        $versionInfo = [
            'version' => 'v2.4.0',
            'edition' => 'FreightX SaaS Enterprise Cloud',
            'channel' => 'Stable Production (LTS)',
            'release_date' => 'November 12, 2026',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'total_migrations' => $totalMigrations,
            'last_migration_batch' => $lastBatch,
        ];

        $changelog = [
            [
                'version' => 'v2.4.0',
                'title' => 'Unified Multi-Carrier Gateway & Universal AI Engine',
                'release_date' => 'Nov 12, 2026',
                'badge' => 'CURRENT RELEASE',
                'badge_color' => '#10b981',
                'highlights' => [
                    'Integrated ShipmentBeat multi-carrier tracking covering 18 major ocean lines (MSC, Maersk, COSCO, Hapag-Lloyd, ONE, Evergreen).',
                    'Universal Google Gemini 3.5 AI Engine configuration with live interactive chat & document parsing tester.',
                    'Central API Keys & Webhook Telemetry Console with real-time probe verification and payload inspection.',
                    'International Multi-Branch Freight Station Management conforming to IATA, UN/LOCODE, and customs regulations.',
                    'Knowledge Base & Freight Forwarding SOP Library replacing legacy placeholders with 6 standard operating procedures.',
                    'Overview snapshot date filtering across all tenant financial and multimodal logistics metrics.'
                ]
            ],
            [
                'version' => 'v2.3.8',
                'title' => 'Dual-Entry Freight Accounting & GL Integrity',
                'release_date' => 'Oct 28, 2026',
                'badge' => 'SECURITY & COMPLIANCE',
                'badge_color' => '#3b82f6',
                'highlights' => [
                    'Automated real-time balance sheet ledger generation with date-range filters.',
                    'Strict chart of accounts validation preventing zero-amount unbalanced journal entries.',
                    'Tenant billing currency auto-conversion based on company base currency settings.',
                    'Multi-branch operational voucher numbering with prefix separation.'
                ]
            ],
            [
                'version' => 'v2.3.0',
                'title' => 'Multimodal Operations & Carrier EDI Ingestion',
                'release_date' => 'Sep 15, 2026',
                'badge' => 'MAJOR FEATURE',
                'badge_color' => '#6366f1',
                'highlights' => [
                    'Ocean, Air, and Land transport modules linked to central freight operations pulse.',
                    'Container seal verification, ISO 6346 equipment check-digit validation.',
                    'Master Air Waybill (MAWB) & House Air Waybill (HAWB) automated documentation.'
                ]
            ],
        ];

        return view('super-admin.system.releases', compact('versionInfo', 'changelog', 'recentMigrations'));
    }

    /**
     * Non-destructive Cache Flush with SuperAdmin Audit Log
     */
    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');

        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    $msg = 'Configuration cache successfully cleared.';
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    $msg = 'Compiled Blade views cache successfully cleared.';
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    $msg = 'Route cache successfully cleared.';
                    break;
                default:
                    Artisan::call('cache:clear');
                    Artisan::call('view:clear');
                    $msg = 'Application and Blade view caches successfully flushed.';
                    break;
            }

            if (class_exists(SuperAdminAuditLog::class)) {
                SuperAdminAuditLog::log('cache_cleared', 'system', 0, ['cache_type' => $type, 'message' => $msg]);
            }

            return back()->with('success', $msg);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }
}
