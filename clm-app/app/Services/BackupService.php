<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;

class BackupService
{
    protected string $backupPath;
    protected string $driver;

    public function __construct()
    {
        $this->backupPath = config('importer.backup.path');
        $this->driver = config('importer.backup.driver', 'auto');

        // Ensure backup directory exists
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a database backup before import.
     *
     * @return array{file: string, size: int, created_at: string}
     * @throws Exception
     */
    public function createBackup(): array
    {
        if (!config('importer.backup.enabled', true)) {
            throw new Exception('Backup is disabled in configuration');
        }

        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$timestamp}.sql";
        $filepath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        try {
            // Try mysqldump first if driver is auto or mysqldump
            if ($this->driver === 'auto' || $this->driver === 'mysqldump') {
                if ($this->tryMysqldump($filepath)) {
                    return $this->getBackupInfo($filepath);
                }
            }

            // Fallback to PHP-based export
            if ($this->driver === 'auto' || $this->driver === 'php') {
                $this->exportViaPhpFallback($filepath);
                return $this->getBackupInfo($filepath);
            }

            throw new Exception('No valid backup driver available');
        } catch (Exception $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'filepath' => $filepath,
            ]);
            throw $e;
        }
    }

    /**
     * Try to create backup using mysqldump command.
     */
    protected function tryMysqldump(string $filepath): bool
    {
        $dbConfig = config('database.connections.' . config('database.default'));

        $command = sprintf(
            'mysqldump -h %s -u %s %s %s > %s 2>&1',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['username']),
            $dbConfig['password'] ? '-p' . escapeshellarg($dbConfig['password']) : '',
            escapeshellarg($dbConfig['database']),
            escapeshellarg($filepath)
        );

        // Execute command
        exec($command, $output, $returnCode);

        // Check if successful
        if ($returnCode === 0 && File::exists($filepath) && File::size($filepath) > 0) {
            // Compress if enabled
            if (config('importer.backup.compress', true)) {
                $this->compressBackup($filepath);
            }
            return true;
        }

        // Clean up failed file
        if (File::exists($filepath)) {
            File::delete($filepath);
        }

        return false;
    }

    /**
     * Export database using PHP (fallback method).
     */
    protected function exportViaPhpFallback(string $filepath): void
    {
        $tables = $this->getTablesForBackup();
        $content = "-- Database Backup\n";
        $content .= "-- Generated: " . now()->toDateTimeString() . "\n\n";
        $content .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            $content .= "-- Table: {$table}\n";
            $content .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $content .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $content .= "-- Data for table {$table}\n";
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        return is_null($value) ? 'NULL' : DB::connection()->getPdo()->quote($value);
                    }, (array) $row);

                    $content .= sprintf(
                        "INSERT INTO `%s` VALUES (%s);\n",
                        $table,
                        implode(', ', $values)
                    );
                }
                $content .= "\n";
            }
        }

        $content .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Write to file
        File::put($filepath, $content);

        // Compress if enabled
        if (config('importer.backup.compress', true)) {
            $this->compressBackup($filepath);
        }
    }

    /**
     * Get list of tables to backup.
     */
    protected function getTablesForBackup(): array
    {
        $database = config('database.connections.' . config('database.default') . '.database');
        $tables = DB::select("SHOW TABLES");
        $tableKey = "Tables_in_{$database}";

        return array_map(fn($table) => $table->{$tableKey}, $tables);
    }

    /**
     * Compress backup file using gzip.
     */
    protected function compressBackup(string $filepath): void
    {
        if (function_exists('gzencode')) {
            $content = File::get($filepath);
            $compressed = gzencode($content, 9);
            File::put($filepath . '.gz', $compressed);
            File::delete($filepath);
        }
    }

    /**
     * Get backup file information.
     */
    protected function getBackupInfo(string $filepath): array
    {
        // Check for compressed version
        if (File::exists($filepath . '.gz')) {
            $filepath .= '.gz';
        }

        if (!File::exists($filepath)) {
            throw new Exception("Backup file not found: {$filepath}");
        }

        return [
            'file' => basename($filepath),
            'size' => File::size($filepath),
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Verify backup integrity.
     */
    public function verifyBackup(string $filename): bool
    {
        $filepath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($filepath)) {
            return false;
        }

        // Basic checks
        $size = File::size($filepath);
        if ($size === 0) {
            return false;
        }

        // If compressed, try to decompress header
        if (str_ends_with($filename, '.gz')) {
            $handle = @gzopen($filepath, 'r');
            if ($handle === false) {
                return false;
            }
            gzclose($handle);
        }

        return true;
    }

    /**
     * Clean up old backups.
     */
    public function cleanupOldBackups(): int
    {
        $maxAge = config('importer.backup.max_age_days', 30);
        $cutoffDate = now()->subDays($maxAge);
        $deletedCount = 0;

        $files = File::files($this->backupPath);

        foreach ($files as $file) {
            if (File::lastModified($file->getPathname()) < $cutoffDate->timestamp) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Get backup file path.
     */
    public function getBackupPath(string $filename): string
    {
        return $this->backupPath . DIRECTORY_SEPARATOR . $filename;
    }
}

