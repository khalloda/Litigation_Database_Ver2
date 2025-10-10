<?php

namespace App\Services;

use App\Models\ImportSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ImportService
{
    protected string $importPath;

    public function __construct()
    {
        $this->importPath = storage_path('app/imports');

        // Ensure import directory exists
        if (!File::exists($this->importPath)) {
            File::makeDirectory($this->importPath, 0755, true);
        }
    }

    /**
     * Handle file upload and create import session.
     *
     * @param UploadedFile $file
     * @param string $tableName
     * @param int $userId
     * @return ImportSession
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, string $tableName, int $userId): ImportSession
    {
        // Validate file size
        $maxSizeMb = config('importer.limits.max_upload_mb', 10);
        if ($file->getSize() > $maxSizeMb * 1024 * 1024) {
            throw new Exception("File size exceeds maximum allowed ({$maxSizeMb}MB)");
        }

        // Validate file type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            throw new Exception('Invalid file type. Allowed: xlsx, xls, csv');
        }

        // Validate table name
        $enabledTables = config('importer.enabled_tables', []);
        if (!in_array($tableName, $enabledTables)) {
            throw new Exception("Import not enabled for table: {$tableName}");
        }

        // Generate session ID
        $sessionId = Str::uuid()->toString();

        // Generate unique filename
        $storedFilename = sprintf(
            '%s_%s.%s',
            $sessionId,
            now()->format('YmdHis'),
            $extension
        );

        // Move file to imports directory
        $sessionPath = $this->getImportSessionPath($sessionId);
        if (!File::exists($sessionPath)) {
            File::makeDirectory($sessionPath, 0755, true);
        }

        $file->move($sessionPath, $storedFilename);

        $filepath = $sessionPath . DIRECTORY_SEPARATOR . $storedFilename;

        // Calculate file hash
        $fileHash = hash_file('sha256', $filepath);

        // Create import session record
        $importSession = ImportSession::create([
            'session_id' => $sessionId,
            'table_name' => $tableName,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'status' => ImportSession::STATUS_UPLOADED,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'file_hash' => $fileHash,
            'user_id' => $userId,
        ]);

        return $importSession;
    }

    /**
     * Start import process for a session.
     *
     * @param ImportSession $session
     * @return void
     */
    public function startSession(ImportSession $session): void
    {
        $session->update([
            'status' => ImportSession::STATUS_IMPORTING,
            'started_at' => now(),
        ]);
    }

    /**
     * Complete import session.
     *
     * @param ImportSession $session
     * @param array $stats
     * @return void
     */
    public function completeSession(ImportSession $session, array $stats): void
    {
        $completedAt = now();
        $durationSeconds = $session->started_at ? $completedAt->diffInSeconds($session->started_at) : 0;

        $session->update([
            'status' => ImportSession::STATUS_COMPLETED,
            'completed_at' => $completedAt,
            'duration_seconds' => $durationSeconds,
            'imported_count' => $stats['imported'] ?? 0,
            'failed_count' => $stats['failed'] ?? 0,
            'skipped_count' => $stats['skipped'] ?? 0,
            'import_errors' => $stats['errors'] ?? [],
        ]);
    }

    /**
     * Mark session as failed.
     *
     * @param ImportSession $session
     * @param string $error
     * @return void
     */
    public function failSession(ImportSession $session, string $error): void
    {
        $session->update([
            'status' => ImportSession::STATUS_FAILED,
            'completed_at' => now(),
            'import_errors' => [
                'message' => $error,
                'timestamp' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Cancel an in-progress import.
     *
     * @param ImportSession $session
     * @return void
     */
    public function cancelSession(ImportSession $session): void
    {
        if ($session->isInProgress()) {
            $session->update([
                'status' => ImportSession::STATUS_CANCELLED,
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Clean up import session files.
     *
     * @param ImportSession $session
     * @return void
     */
    public function cleanupSession(ImportSession $session): void
    {
        $sessionPath = $this->getImportSessionPath($session->session_id);

        if (File::exists($sessionPath)) {
            File::deleteDirectory($sessionPath);
        }
    }

    /**
     * Get import session directory path.
     *
     * @param string $sessionId
     * @return string
     */
    public function getImportSessionPath(string $sessionId): string
    {
        return $this->importPath . DIRECTORY_SEPARATOR . $sessionId;
    }

    /**
     * Get full file path for import session.
     *
     * @param ImportSession $session
     * @return string
     */
    public function getSessionFilePath(ImportSession $session): string
    {
        return $this->getImportSessionPath($session->session_id)
            . DIRECTORY_SEPARATOR
            . $session->stored_filename;
    }
}

