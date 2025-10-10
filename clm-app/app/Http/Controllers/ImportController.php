<?php

namespace App\Http\Controllers;

use App\Models\ImportSession;
use App\Services\BackupService;
use App\Services\FileParserService;
use App\Services\ImportService;
use App\Services\MappingEngine;
use App\Services\PreflightEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ImportController extends Controller
{
    public function __construct(
        protected ImportService $importService,
        protected FileParserService $parserService,
        protected MappingEngine $mappingEngine,
        protected PreflightEngine $preflightEngine,
        protected BackupService $backupService,
    ) {}

    /**
     * Show import sessions list.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ImportSession::class);

        $query = ImportSession::with('user:id,name,email')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by table
        if ($request->filled('table')) {
            $query->where('table_name', $request->table);
        }

        $sessions = $query->paginate(25);

        $enabledTables = config('importer.enabled_tables', []);

        return view('import.index', compact('sessions', 'enabledTables'));
    }

    /**
     * Show upload form.
     */
    public function upload()
    {
        $this->authorize('create', ImportSession::class);

        $enabledTables = config('importer.enabled_tables', []);

        return view('import.upload', compact('enabledTables'));
    }

    /**
     * Process file upload.
     */
    public function processUpload(Request $request)
    {
        $this->authorize('create', ImportSession::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:' . (config('importer.limits.max_upload_mb') * 1024),
            'table_name' => 'required|string|in:' . implode(',', config('importer.enabled_tables', [])),
        ]);

        try {
            DB::beginTransaction();

            // Upload file and create session
            $session = $this->importService->uploadFile(
                $request->file('file'),
                $request->table_name,
                Auth::id()
            );

            // Parse file to get headers and row count
            $filepath = $this->importService->getSessionFilePath($session);
            $parsed = $this->parserService->parseFile($filepath, $session->file_type);

            // Update session with parsed data
            $session->update([
                'total_rows' => $parsed['total_rows'],
            ]);

            // Auto-map columns
            $mapping = $this->mappingEngine->autoMapColumns(
                $parsed['headers'],
                $session->table_name
            );

            DB::commit();

            return redirect()
                ->route('import.map', $session)
                ->with('success', __('app.file_uploaded_successfully'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show mapping configuration page.
     */
    public function map($importSessionId)
    {
        // Manually resolve the ImportSession instead of relying on route model binding
        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('view', $session);

        // Get parsed data
        $filepath = $this->importService->getSessionFilePath($session);
        $parsed = $this->parserService->parseFile($filepath, $session->file_type);

        // Get auto-mapped columns
        $autoMapping = $this->mappingEngine->autoMapColumns(
            $parsed['headers'],
            $session->table_name
        );

        // Get available target columns
        $dbColumns = $this->mappingEngine->getDbColumnsForTable($session->table_name);

        // Get available transforms
        $availableTransforms = config('importer.mapping.transforms', []);

        // Get column stats
        $columnStats = [];
        foreach ($parsed['headers'] as $header) {
            $columnStats[$header] = $this->parserService->getColumnStats($parsed['rows'], $header);
        }

        return view('import.map', compact(
            'session',
            'parsed',
            'autoMapping',
            'dbColumns',
            'availableTransforms',
            'columnStats'
        ));
    }

    /**
     * Save mapping configuration.
     */
    public function saveMapping(Request $request, $importSessionId)
    {
        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('update', $session);

        // Debug: Log the incoming request data
        \Log::info('ImportController::saveMapping - Request data', [
            'sessionId' => $importSessionId,
            'mapping' => $request->mapping,
            'transforms' => $request->transforms,
            'allInput' => $request->all()
        ]);

        $request->validate([
            'mapping' => 'required|array',
            'transforms' => 'nullable|array',
        ]);

        try {
            // Validate mapping
            $errors = $this->mappingEngine->validateMapping(
                $request->mapping,
                $session->table_name
            );

            \Log::info('Mapping validation errors', ['errors' => $errors]);

            if (!empty($errors)) {
                \Log::warning('Mapping validation failed', ['errors' => $errors]);
                return back()
                    ->withInput()
                    ->with('error', 'Mapping validation failed: ' . implode(', ', $errors));
            }

            // Save mapping to session
            $session->update([
                'column_mapping' => $request->mapping,
                'transforms' => $request->transforms ?? [],
                'status' => ImportSession::STATUS_MAPPED,
            ]);

            \Log::info('Mapping saved successfully, redirecting to preflight', [
                'sessionId' => $session->id,
                'newStatus' => $session->status
            ]);

            return redirect()
                ->route('import.preflight', $session)
                ->with('success', __('app.mapping_saved_successfully'));
        } catch (Exception $e) {
            \Log::error('Exception in saveMapping', [
                'sessionId' => $importSessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Run preflight validation.
     */
    public function preflight($importSessionId)
    {
        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('view', $session);

        if (empty($session->column_mapping)) {
            return redirect()
                ->route('import.map', $session)
                ->with('error', __('app.please_configure_mapping_first'));
        }

        try {
            // Parse file
            $filepath = $this->importService->getSessionFilePath($session);
            $parsed = $this->parserService->parseFile($filepath, $session->file_type);

            // Run preflight validation
            $results = $this->preflightEngine->runPreflight(
                $parsed['rows'],
                $session->column_mapping,
                $session->table_name
            );

            // Save preflight results
            $session->update([
                'preflight_errors' => $results['errors'],
                'preflight_error_count' => $results['error_count'],
                'preflight_warning_count' => $results['warning_count'],
                'status' => ImportSession::STATUS_VALIDATED,
            ]);

            // Check if error rate exceeds threshold
            $exceedsThreshold = $this->preflightEngine->exceedsErrorThreshold(
                $results['error_count'],
                $session->total_rows
            );

            return view('import.preflight', compact('session', 'results', 'exceedsThreshold'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Execute the import.
     */
    public function runImport(Request $request, $importSessionId)
    {
        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('update', $session);

        if ($session->status !== ImportSession::STATUS_VALIDATED) {
            return back()->with('error', __('app.import_must_be_validated_first'));
        }

        try {
            DB::beginTransaction();

            // Create backup if enabled
            if (config('importer.backup.enabled', true)) {
                $backup = $this->backupService->createBackup();
                $session->update([
                    'backup_file' => $backup['file'],
                    'backup_size' => $backup['size'],
                    'backup_created_at' => $backup['created_at'],
                ]);
            }

            // Start import
            $this->importService->startSession($session);

            // Parse file
            $filepath = $this->importService->getSessionFilePath($session);
            $parsed = $this->parserService->parseFile($filepath, $session->file_type);

            // Import data
            $stats = $this->executeImport($parsed['rows'], $session);

            // Complete session
            $this->importService->completeSession($session, $stats);

            DB::commit();

            return redirect()
                ->route('import.show', $session)
                ->with('success', __('app.import_completed_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            $this->importService->failSession($session, $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Execute the actual import process.
     */
    protected function executeImport(array $rows, ImportSession $session): array
    {
        $imported = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                // Map row data
                $data = [];
                foreach ($session->column_mapping as $sourceCol => $targetCol) {
                    $data[$targetCol] = $row[$sourceCol] ?? null;
                }

                // Add audit fields
                $data['created_by'] = Auth::id();
                $data['updated_by'] = Auth::id();

                // Insert into database
                DB::table($session->table_name)->insert($data);
                $imported++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'failed' => $failed,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 100), // Limit errors
        ];
    }

    /**
     * Show import session details.
     */
    public function show($importSessionId)
    {
        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('view', $session);

        return view('import.show', compact('session'));
    }

    /**
     * Cancel an import session.
     */
    public function cancel($importSessionId)
    {
        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('cancel', $session);

        $this->importService->cancelSession($session);

        return redirect()
            ->route('import.index')
            ->with('success', __('app.import_cancelled_successfully'));
    }

    /**
     * Delete an import session.
     */
    public function destroy($importSessionId)
    {
        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('delete', $session);

        $this->importService->cleanupSession($session);
        $session->delete();

        return redirect()
            ->route('import.index')
            ->with('success', __('app.import_deleted_successfully'));
    }
}
