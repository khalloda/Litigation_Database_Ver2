<?php

namespace App\Http\Controllers;

use App\Models\ImportSession;
use App\Services\BackupService;
use App\Services\FileParserService;
use App\Services\ImportService;
use App\Services\MappingEngine;
use App\Services\PreflightEngine;
use App\Services\OpponentSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Artisan;
use App\Support\NameNormalizer;
use App\Support\TextNormalizer;
use App\Services\Import\ImportProfileService;
use App\Support\Import\HeaderHasher;
use App\Models\Opponent;
use Exception;

class ImportController extends Controller
{
    public function __construct(
        protected ImportService $importService,
        protected FileParserService $parserService,
        protected MappingEngine $mappingEngine,
        protected PreflightEngine $preflightEngine,
        protected BackupService $backupService,
        protected OpponentSuggestionService $opponentSuggestionService,
        protected ImportProfileService $importProfileService,
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
     * Persist current resolved preflight choices immediately (without running import).
     */
    public function saveChoicesNow(Request $request, $importSessionId)
    {
        $session = ImportSession::findOrFail($importSessionId);
        $this->authorize('update', $session);

        try {
            // Parse headers of the uploaded file to compute header hash
            $filepath = $this->importService->getSessionFilePath($session);
            $parsed = $this->parserService->parseFile($filepath, $session->file_type);
            $headers = $parsed['headers'] ?? (isset($parsed['rows'][0]) ? array_keys($parsed['rows'][0]) : []);

            $remember = (bool) $request->input('remember_decisions', false);
            $saveAsProfile = (bool) $request->input('save_as_profile', false);
            $profileName = $saveAsProfile ? ($request->input('profile_name') ?: 'Profile ' . now()->format('Ymd_His')) : null;

            $profile = $this->importProfileService->persistChoicesFromSession(
                $session,
                $headers,
                $saveAsProfile,
                $remember,
                $profileName
            );

            if ($profile) {
                return back()->with('success', __('app.profile_choices_saved_successfully'));
            }

            return back()->with('warning', __('app.nothing_to_save'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
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
        Log::info('ImportController::saveMapping - Request data', [
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

            Log::info('Mapping validation errors', ['errors' => $errors]);

            if (!empty($errors)) {
                Log::warning('Mapping validation failed', ['errors' => $errors]);
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

            Log::info('Mapping saved successfully, redirecting to preflight', [
                'sessionId' => $session->id,
                'newStatus' => $session->status
            ]);

            return redirect()
                ->route('import.preflight', $session)
                ->with('success', __('app.mapping_saved_successfully'));
        } catch (Exception $e) {
            Log::error('Exception in saveMapping', [
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
        // Increase execution time and memory for large imports
        $executionTime = config('importer.validation.execution_time', 600);
        $memoryLimit = config('importer.validation.memory_limit', '512M');

        set_time_limit($executionTime);
        ini_set('memory_limit', $memoryLimit);

        Log::info('Preflight execution settings', [
            'execution_time' => $executionTime,
            'memory_limit' => $memoryLimit
        ]);

        $session = ImportSession::findOrFail($importSessionId);

        $this->authorize('view', $session);

        Log::info('Preflight method called', [
            'sessionId' => $session->id,
            'column_mapping' => $session->column_mapping,
            'isEmpty' => empty($session->column_mapping)
        ]);

        if (empty($session->column_mapping)) {
            Log::warning('Column mapping is empty, redirecting to map', [
                'sessionId' => $session->id
            ]);
            return redirect()
                ->route('import.map', $session->id)
                ->with('error', __('app.please_configure_mapping_first'));
        }

        try {
            Log::info('Starting preflight processing', ['sessionId' => $session->id]);

            // Parse file
            $filepath = $this->importService->getSessionFilePath($session);
            Log::info('Got file path', ['filepath' => $filepath]);

            $parsed = $this->parserService->parseFile($filepath, $session->file_type);
            Log::info('File parsed successfully', ['rowCount' => count($parsed['rows'])]);

            // Try applying an import profile before validation
            $headers = $parsed['headers'] ?? (isset($parsed['rows'][0]) ? array_keys($parsed['rows'][0]) : []);
            $appliedProfile = null;
            $profileSummary = null;
            if (!empty($headers)) {
                $profile = $this->importProfileService->selectProfile($session->table_name, $headers);
                if ($profile) {
                    $appliedProfile = $profile;
                    $profileSummary = $this->importProfileService->applyProfile($profile, $parsed['rows'], $session->column_mapping);
                }
            }

            // Run preflight validation
            $results = $this->preflightEngine->runPreflight(
                $parsed['rows'],
                $session->column_mapping,
                $session->table_name
            );
            Log::info('Preflight validation completed', ['errorCount' => $results['error_count']]);

            // Opponent suggestions (only for cases table and when an incoming opponent name exists)
            $opponentSuggestions = [];
            if ($session->table_name === 'cases') {
                // Try typical columns that might contain opponent name text
                $candidateCols = ['opponent_name', 'opponent', 'opponent_and_capacity', 'opponent_id'];
                foreach ($parsed['rows'] as $i => $row) {
                    $incoming = null;
                    foreach ($candidateCols as $col) {
                        if (array_key_exists($col, $row) && !empty($row[$col])) {
                            $value = $row[$col];
                            // If it's opponent_id column, check if it's text (not integer)
                            if ($col === 'opponent_id' && is_numeric($value)) {
                                continue; // Skip if it's already a valid integer ID
                            }
                            $incoming = $value;
                            break;
                        }
                    }
                    if (!$incoming) {
                        $opponentSuggestions[$i] = null;
                        continue;
                    }
                    $opponentSuggestions[$i] = $this->opponentSuggestionService->suggest((string) $incoming);
                }
            }

            // Merge previously resolved errors so a refresh doesn't wipe resolutions
            $mergedErrors = $results['errors'];
            try {
                $normalizer = app(TextNormalizer::class);
                $previousErrors = is_array($session->preflight_errors) ? $session->preflight_errors : [];

                // Build two lookups for resolved: primary by (column + row), fallback by (column + normalized(value))
                $resolvedByRow = [];
                $resolvedByValue = [];

                $resolvedTotal = 0;
                foreach ($previousErrors as $prev) {
                    if (!is_array($prev) || empty($prev['resolved']) || empty($prev['column'])) {
                        continue;
                    }
                    $info = [
                        'resolved' => true,
                        'resolved_id' => $prev['resolved_id'] ?? null,
                        'resolved_at' => $prev['resolved_at'] ?? now()->toISOString(),
                    ];
                    $resolvedTotal++;
                    // Primary: row-based key when row is available
                    if (isset($prev['row'])) {
                        $rowKey = ($prev['column'] ?? '') . '::row::' . (string) $prev['row'];
                        $resolvedByRow[$rowKey] = $info;
                    }
                    // Fallback: normalized value-based key
                    if (isset($prev['value'])) {
                        $norm = $normalizer->normalize((string) $prev['value']);
                        $valKey = ($prev['column'] ?? '') . '::val::' . $norm;
                        $resolvedByValue[$valKey] = $info;
                    }
                }

                $appliedCount = 0;
                foreach ($mergedErrors as $idx => $err) {
                    if (!is_array($err) || empty($err['column'])) {
                        continue;
                    }
                    $applied = false;
                    // Try row-based first
                    if (isset($err['row'])) {
                        $rowKey = ($err['column'] ?? '') . '::row::' . (string) $err['row'];
                        if (isset($resolvedByRow[$rowKey])) {
                            $info = $resolvedByRow[$rowKey];
                            $mergedErrors[$idx]['resolved'] = true;
                            if (isset($info['resolved_id'])) $mergedErrors[$idx]['resolved_id'] = $info['resolved_id'];
                            if (isset($info['resolved_at'])) $mergedErrors[$idx]['resolved_at'] = $info['resolved_at'];
                            $applied = true;
                        }
                    }
                    // Fallback to normalized value-based
                    if (!$applied && isset($err['value'])) {
                        $norm = $normalizer->normalize((string) $err['value']);
                        $valKey = ($err['column'] ?? '') . '::val::' . $norm;
                        if (isset($resolvedByValue[$valKey])) {
                            $info = $resolvedByValue[$valKey];
                            $mergedErrors[$idx]['resolved'] = true;
                            if (isset($info['resolved_id'])) $mergedErrors[$idx]['resolved_id'] = $info['resolved_id'];
                            if (isset($info['resolved_at'])) $mergedErrors[$idx]['resolved_at'] = $info['resolved_at'];
                            $applied = true;
                        }
                    }
                    if ($applied) {
                        $appliedCount++;
                    }
                }

                Log::info('Preflight merge results', [
                    'sessionId' => $session->id,
                    'resolved_total_prev' => $resolvedTotal,
                    'resolved_applied_now' => $appliedCount,
                    'new_errors_count' => count($mergedErrors)
                ]);
            } catch (\Throwable $mergeEx) {
                Log::warning('Preflight merge of resolved errors failed', [
                    'sessionId' => $session->id,
                    'error' => $mergeEx->getMessage(),
                ]);
            }

            // Recalculate counts excluding resolved errors
            $effectiveErrorCount = collect($mergedErrors)->filter(function ($e) {
                return !(isset($e['resolved']) && $e['resolved'] === true);
            })->count();

            // Save preflight results (merged)
            $session->update([
                'preflight_errors' => $mergedErrors,
                'preflight_error_count' => $effectiveErrorCount,
                'preflight_warning_count' => $results['warning_count'],
                'status' => ImportSession::STATUS_VALIDATED,
            ]);

            // Replace results.errors with merged version for UI rendering
            $results['errors'] = $mergedErrors;
            $results['error_count'] = $effectiveErrorCount;

            // Check if error rate exceeds threshold
            $exceedsThreshold = $this->preflightEngine->exceedsErrorThreshold(
                $results['error_count'],
                $session->total_rows
            );

            return view('import.preflight', compact('session', 'results', 'exceedsThreshold', 'opponentSuggestions', 'parsed', 'appliedProfile', 'profileSummary'));
        } catch (Exception $e) {
            Log::error('Exception in preflight method', [
                'sessionId' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

            // Persist profile choices if requested (from preflight form)
            $remember = (bool) $request->input('remember_decisions', false);
            $saveAsProfile = (bool) $request->input('save_as_profile', false);
            if ($remember || $saveAsProfile) {
                $filepath = $this->importService->getSessionFilePath($session);
                $parsed = $this->parserService->parseFile($filepath, $session->file_type);
                $headers = $parsed['headers'] ?? (isset($parsed['rows'][0]) ? array_keys($parsed['rows'][0]) : []);
                $profileName = $saveAsProfile ? ($request->input('profile_name') ?: 'Profile ' . now()->format('Ymd_His')) : null;
                $profile = $this->importProfileService->persistChoicesFromSession(
                    $session,
                    $headers,
                    $saveAsProfile,
                    $remember,
                    $profileName
                );
                if ($profile) {
                    $session->update(['settings_snapshot' => [
                        'profile_id' => $profile->id,
                        'profile_name' => $profile->name,
                    ]]);
                }
            }

            // Start import
            $this->importService->startSession($session);

            // Parse file
            $filepath = $this->importService->getSessionFilePath($session);
            $parsed = $this->parserService->parseFile($filepath, $session->file_type);

            // Import data (capture decisions from preflight form)
            $decisions = $request->input('decisions', []);
            $stats = $this->executeImport($parsed['rows'], $session, $decisions);

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
    protected function executeImport(array $rows, ImportSession $session, array $decisions = []): array
    {
        $imported = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];
        $reconRows = [];
        $normalizer = app(NameNormalizer::class);

        // Get table columns once for performance
        $tableColumns = Schema::getColumnListing($session->table_name);
        $hasCreatedBy = in_array('created_by', $tableColumns);
        $hasUpdatedBy = in_array('updated_by', $tableColumns);
        $hasCreatedAt = in_array('created_at', $tableColumns);
        $hasUpdatedAt = in_array('updated_at', $tableColumns);

        $candidateCols = ['opponent_name', 'opponent', 'opponent_and_capacity'];

        foreach ($rows as $index => $row) {
            try {
                // Map row data
                $data = [];
                foreach ($session->column_mapping as $sourceCol => $targetCol) {
                    // Skip empty target columns (skipped columns)
                    if (!empty($targetCol)) {
                        $data[$targetCol] = $row[$sourceCol] ?? null;
                    }
                }

                // Add audit fields if they exist in the table
                if ($hasCreatedBy) {
                    $data['created_by'] = Auth::id();
                }
                if ($hasUpdatedBy) {
                    $data['updated_by'] = Auth::id();
                }

                // Add timestamps if they exist in the table
                if ($hasCreatedAt) {
                    $data['created_at'] = now();
                }
                if ($hasUpdatedAt) {
                    $data['updated_at'] = now();
                }

                // Special handling for clients table - resolve option values to IDs
                if ($session->table_name === 'clients') {
                    $data = $this->resolveClientOptionValues($data);
                }

                // Special handling for cases table - resolve option values and split capacity fields
                if ($session->table_name === 'cases') {
                    $data = $this->resolveCaseOptionValues($data);
                    $data = $this->resolveDirectMappedFields($data);

                    // Apply preflight resolutions FIRST (before text resolution)
                    $this->applyPreflightResolutions($data, $session, $index);

                    // Cases: apply preflight opponent decisions BEFORE resolveDirectIdFields
                    // This ensures user decisions from the opponent suggestions UI are applied first
                    $incomingOpponent = null;
                    $hasOpponentDecision = false;
                    foreach ($candidateCols as $col) {
                        if (array_key_exists($col, $row) && !empty($row[$col])) {
                            $incomingOpponent = (string) $row[$col];
                            break;
                        }
                    }

                    if (isset($decisions[$index])) {
                        $decision = $decisions[$index];
                        $decisionType = $decision['type'] ?? null;
                        $aliasFlag = !empty($decision['alias']);

                        if ($decisionType === 'match' && !empty($decision['opponent_id'])) {
                            $opponentId = (int) $decision['opponent_id'];
                            $data['opponent_id'] = $opponentId;
                            $hasOpponentDecision = true;
                            Log::info('Applied opponent decision from preflight form', [
                                'row' => $index,
                                'opponent_id' => $opponentId,
                                'incoming' => $incomingOpponent
                            ]);
                            if ($aliasFlag && $incomingOpponent) {
                                $norm = $normalizer->normalize($incomingOpponent);
                                $alias = $norm['normalized'];
                                if ($alias !== '') {
                                    $existsOther = DB::table('opponent_aliases')
                                        ->where('alias_normalized', $alias)
                                        ->where('opponent_id', '!=', $opponentId)
                                        ->exists();
                                    if (!$existsOther) {
                                        DB::table('opponent_aliases')->updateOrInsert(
                                            ['opponent_id' => $opponentId, 'alias_normalized' => $alias],
                                            ['updated_at' => now(), 'created_at' => now()]
                                        );
                                    }
                                }
                            }
                        } elseif ($decisionType === 'new' && $incomingOpponent) {
                            $norm = $normalizer->normalize($incomingOpponent);
                            $newId = DB::table('opponents')->insertGetId([
                                'opponent_name_ar' => preg_match('/\p{Arabic}/u', $incomingOpponent) ? $incomingOpponent : null,
                                'opponent_name_en' => preg_match('/[A-Za-z]/u', $incomingOpponent) ? $incomingOpponent : null,
                                'normalized_name' => $norm['normalized'],
                                'first_token' => $norm['first_token'],
                                'last_token' => $norm['last_token'],
                                'token_count' => $norm['token_count'],
                                'latin_key' => $norm['latin_key'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $data['opponent_id'] = $newId;
                            $hasOpponentDecision = true;
                            Log::info('Created new opponent from preflight decision', [
                                'row' => $index,
                                'opponent_id' => $newId,
                                'incoming' => $incomingOpponent
                            ]);
                            if ($aliasFlag) {
                                $alias = $norm['normalized'];
                                if ($alias !== '') {
                                    DB::table('opponent_aliases')->updateOrInsert(
                                        ['opponent_id' => $newId, 'alias_normalized' => $alias],
                                        ['updated_at' => now(), 'created_at' => now()]
                                    );
                                }
                            }
                        }
                    }

                    // Ensure textual values mapped into ID fields are resolved before insert
                    // (only for fields not already resolved in preflight or decisions)
                    // Skip opponent_id if we already have a decision for it
                    $originalOpponentId = $data['opponent_id'] ?? null;
                    $this->resolveDirectIdFields($data, $hasOpponentDecision);
                    // If opponent_id was set by decision but resolveDirectIdFields changed it, restore it
                    if ($hasOpponentDecision && $originalOpponentId && isset($data['opponent_id']) && $data['opponent_id'] !== $originalOpponentId) {
                        Log::warning('Opponent ID was changed by resolveDirectIdFields despite having a decision', [
                            'row' => $index,
                            'decision_id' => $originalOpponentId,
                            'resolved_id' => $data['opponent_id']
                        ]);
                        $data['opponent_id'] = $originalOpponentId;
                    }

                    // Clean string fields to remove newlines/carriage returns and enforce max lengths
                    $this->cleanCaseStringFields($data);
                    // Convert empty strings to NULL for nullable foreign key fields
                    $this->normalizeEmptyForeignKeys($data);
                }

                // Insert into database
                DB::table($session->table_name)->insert($data);
                $imported++;

                // Process multiple opponents for cases table
                if ($session->table_name === 'cases' && !empty($data['_opponents'])) {
                    $this->processCaseOpponents($data['_opponents'], $data['id'] ?? null);
                }

                // Reconciliation
                if ($session->table_name === 'cases') {
                    $reconRows[] = [
                        'incoming_name'   => (string) ($incomingOpponent ?? ''),
                        'normalized_in'   => $incomingOpponent ? $normalizer->normalize($incomingOpponent)['normalized'] : '',
                        'matched_to_id'   => $data['opponent_id'] ?? '',
                        'matched_name'    => isset($data['opponent_id']) ? (DB::table('opponents')->where('id', $data['opponent_id'])->value('opponent_name_ar') ?? DB::table('opponents')->where('id', $data['opponent_id'])->value('opponent_name_en')) : '',
                        'score'           => '',
                        'decision'        => $decisions[$index]['type'] ?? '',
                        'import_batch_id' => $session->id,
                        'source_file'     => $session->file_path ?? '',
                        'row_no'          => $index,
                    ];
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Reconciliation CSV
        if (!empty($reconRows)) {
            $header = ['incoming_name', 'normalized_in', 'matched_to_id', 'matched_name', 'score', 'decision', 'import_batch_id', 'source_file', 'row_no'];
            $lines = [];
            $lines[] = implode(',', $header);
            foreach ($reconRows as $r) {
                $lines[] = implode(',', array_map(function ($v) {
                    $v = (string) $v;
                    $v = str_replace('"', '""', $v);
                    if (str_contains($v, ',') || str_contains($v, '"')) return '"' . $v . '"';
                    return $v;
                }, $r));
            }
            $dir = 'reconciliations';
            $filename = 'import_' . $session->id . '_' . now()->format('Ymd_His') . '.csv';
            Storage::disk('local')->put($dir . '/' . $filename, implode("\n", $lines));
        }

        return [
            'imported' => $imported,
            'failed' => $failed,
            'skipped' => $skipped,
            'errors' => $errors, // Show all errors
        ];
    }

    /**
     * Resolve client option values to their corresponding IDs
     */
    private function resolveClientOptionValues(array $data): array
    {
        // Resolve status
        if (!empty($data['status'])) {
            $statusId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'client.status');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['status'])
                    ->orWhere('label_ar', $data['status']);
            })->value('id');

            if ($statusId) {
                $data['status_id'] = $statusId;
            }
        }

        // Resolve cash_or_probono
        if (!empty($data['cash_or_probono'])) {
            $cashOrProbonoId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'client.cash_or_probono');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['cash_or_probono'])
                    ->orWhere('label_ar', $data['cash_or_probono']);
            })->value('id');

            if ($cashOrProbonoId) {
                $data['cash_or_probono_id'] = $cashOrProbonoId;
            }
        }

        // Resolve power_of_attorney_location
        if (!empty($data['power_of_attorney_location'])) {
            $poaLocationId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'client.power_of_attorney_location');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['power_of_attorney_location'])
                    ->orWhere('label_ar', $data['power_of_attorney_location']);
            })->value('id');

            if ($poaLocationId) {
                $data['power_of_attorney_location_id'] = $poaLocationId;
            }
        }

        // Resolve documents_location
        if (!empty($data['documents_location'])) {
            $docLocationId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'client.documents_location');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['documents_location'])
                    ->orWhere('label_ar', $data['documents_location']);
            })->value('id');

            if ($docLocationId) {
                $data['documents_location_id'] = $docLocationId;
            }
        }

        // Resolve contact_lawyer
        if (!empty($data['contact_lawyer'])) {
            $lawyerId = \App\Models\Lawyer::where(function ($q) use ($data) {
                $q->where('lawyer_name_en', $data['contact_lawyer'])
                    ->orWhere('lawyer_name_ar', $data['contact_lawyer']);
            })->value('id');

            if ($lawyerId) {
                $data['contact_lawyer_id'] = $lawyerId;
            }
        }

        return $data;
    }

    /**
     * Resolve case option values to their corresponding IDs and handle field splitting
     */
    private function resolveCaseOptionValues(array $data): array
    {
        // Resolve case category
        if (!empty($data['matter_category'])) {
            $categoryId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'case.category');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['matter_category'])
                    ->orWhere('label_ar', $data['matter_category']);
            })->value('id');

            if ($categoryId) {
                $data['matter_category_id'] = $categoryId;
            }
        }

        // Resolve case degree
        if (!empty($data['matter_degree'])) {
            $degreeId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'case.degree');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['matter_degree'])
                    ->orWhere('label_ar', $data['matter_degree']);
            })->value('id');

            if ($degreeId) {
                $data['matter_degree_id'] = $degreeId;
            }
        }

        // Resolve case status
        if (!empty($data['matter_status'])) {
            $statusId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'case.status');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['matter_status'])
                    ->orWhere('label_ar', $data['matter_status']);
            })->value('id');

            if ($statusId) {
                $data['matter_status_id'] = $statusId;
            }
        }

        // Resolve case importance
        if (!empty($data['matter_importance'])) {
            $importanceId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'case.importance');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['matter_importance'])
                    ->orWhere('label_ar', $data['matter_importance']);
            })->value('id');

            if ($importanceId) {
                $data['matter_importance_id'] = $importanceId;
            }
        }

        // Resolve case branch
        if (!empty($data['matter_branch'])) {
            $branchId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'case.branch');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['matter_branch'])
                    ->orWhere('label_ar', $data['matter_branch']);
            })->value('id');

            if ($branchId) {
                $data['matter_branch_id'] = $branchId;
            }
        }

        // Resolve client capacity
        if (!empty($data['client_capacity'])) {
            $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'capacity.type');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['client_capacity'])
                    ->orWhere('label_ar', $data['client_capacity']);
            })->value('id');

            if ($capacityId) {
                $data['client_capacity_id'] = $capacityId;
            }
        }

        // Resolve opponent capacity
        if (!empty($data['opponent_capacity'])) {
            $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'capacity.type');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['opponent_capacity'])
                    ->orWhere('label_ar', $data['opponent_capacity']);
            })->value('id');

            if ($capacityId) {
                $data['opponent_capacity_id'] = $capacityId;
            }
        }

        // Resolve court (by name)
        if (!empty($data['matter_court'])) {
            $courtId = \App\Models\Court::where(function ($q) use ($data) {
                $q->where('court_name_en', $data['matter_court'])
                    ->orWhere('court_name_ar', $data['matter_court']);
            })->value('id');

            if ($courtId) {
                $data['court_id'] = $courtId;
            }
        }

        // Resolve circuit name
        if (!empty($data['matter_circuit'])) {
            $circuitId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'circuit.name');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['matter_circuit'])
                    ->orWhere('label_ar', $data['matter_circuit']);
            })->value('id');

            if ($circuitId) {
                $data['circuit_name_id'] = $circuitId;
            }
        }

        // Resolve opponent (by name)
        if (!empty($data['opponent_name'])) {
            $opponentId = \App\Models\Opponent::where(function ($q) use ($data) {
                $q->where('opponent_name_en', $data['opponent_name'])
                    ->orWhere('opponent_name_ar', $data['opponent_name']);
            })->value('id');

            if ($opponentId) {
                $data['opponent_id'] = $opponentId;
            }
        }

        // Resolve matter destination (court)
        if (!empty($data['matter_destination'])) {
            $destinationId = \App\Models\Court::where(function ($q) use ($data) {
                $q->where('court_name_en', $data['matter_destination'])
                    ->orWhere('court_name_ar', $data['matter_destination']);
            })->value('id');

            if ($destinationId) {
                $data['matter_destination_id'] = $destinationId;
            }
        }

        // Resolve partner lawyer (by name and title filter)
        if (!empty($data['matter_partner'])) {
            $partnerId = \App\Models\Lawyer::whereHas('title', function ($q) {
                $q->whereIn('label_en', ['Managing Partner', 'Senior Partner', 'Partner', 'Junior Partner']);
            })->where(function ($q) use ($data) {
                $q->where('lawyer_name_en', $data['matter_partner'])
                    ->orWhere('lawyer_name_ar', $data['matter_partner']);
            })->value('id');

            if ($partnerId) {
                $data['matter_partner_id'] = $partnerId;
            }
        }

        // Resolve client type
        if (!empty($data['client_type'])) {
            $clientTypeId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'client.cash_or_probono');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['client_type'])
                    ->orWhere('label_ar', $data['client_type']);
            })->value('id');

            if ($clientTypeId) {
                $data['client_type_id'] = $clientTypeId;
            }
        }

        // Auto-fill client_type from client's cash_or_probono if not provided
        if (empty($data['client_type']) && !empty($data['client_id'])) {
            $client = \App\Models\Client::find($data['client_id']);
            if ($client && $client->cash_or_probono_id) {
                $data['client_type_id'] = $client->cash_or_probono_id;
            }
        }

        // Handle client_and_capacity splitting
        if (!empty($data['client_and_capacity'])) {
            $parts = explode(' - ', $data['client_and_capacity']);
            if (count($parts) >= 2) {
                $data['client_in_case_name'] = trim($parts[0]);
                $capacityText = trim($parts[1]);

                // Try to resolve capacity to ID
                $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                    $q->where('key', 'capacity.type');
                })->where(function ($q) use ($capacityText) {
                    $q->where('label_en', $capacityText)
                        ->orWhere('label_ar', $capacityText);
                })->value('id');

                if ($capacityId) {
                    $data['client_capacity_id'] = $capacityId;
                }

                // Handle capacity note if present
                if (count($parts) >= 3) {
                    $data['client_capacity_note'] = trim($parts[2]);
                }
            }
        }

        // Handle opponent_and_capacity splitting
        if (!empty($data['opponent_and_capacity'])) {
            $parts = explode(' - ', $data['opponent_and_capacity']);
            if (count($parts) >= 2) {
                $data['opponent_in_case_name'] = trim($parts[0]);
                $capacityText = trim($parts[1]);

                // Try to resolve capacity to ID
                $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                    $q->where('key', 'capacity.type');
                })->where(function ($q) use ($capacityText) {
                    $q->where('label_en', $capacityText)
                        ->orWhere('label_ar', $capacityText);
                })->value('id');

                if ($capacityId) {
                    $data['opponent_capacity_id'] = $capacityId;
                }

                // Handle capacity note if present
                if (count($parts) >= 3) {
                    $data['opponent_capacity_note'] = trim($parts[2]);
                }
            }
        }

        return $data;
    }

    /**
     * Resolve directly mapped fields that contain text but should be integers
     */
    private function resolveDirectMappedFields(array $data): array
    {
        // Define field mappings: field_name => [option_set_key, model_class, name_field_ar, name_field_en]
        $fieldMappings = [
            'matter_category_id' => ['case.category', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'matter_degree_id' => ['case.degree', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'matter_status_id' => ['case.status', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'matter_importance_id' => ['case.importance', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'matter_branch_id' => ['case.branch', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'client_capacity_id' => ['capacity.type', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'opponent_capacity_id' => ['capacity.type', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'client_type_id' => ['client.cash_or_probono', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'circuit_name_id' => ['circuit.name', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'circuit_shift_id' => ['circuit.shift', \App\Models\OptionValue::class, 'label_ar', 'label_en'],
            'court_id' => [null, \App\Models\Court::class, 'court_name_ar', 'court_name_en'],
            'matter_destination_id' => [null, \App\Models\Court::class, 'court_name_ar', 'court_name_en'],
            'opponent_id' => [null, \App\Models\Opponent::class, 'opponent_name_ar', 'opponent_name_en'],
            'matter_partner_id' => [null, \App\Models\Lawyer::class, 'lawyer_name_ar', 'lawyer_name_en'],
        ];

        foreach ($fieldMappings as $fieldName => $mapping) {
            if (!empty($data[$fieldName]) && !is_numeric($data[$fieldName])) {
                $optionSetKey = $mapping[0];
                $modelClass = $mapping[1];
                $nameFieldAr = $mapping[2];
                $nameFieldEn = $mapping[3];

                $resolvedId = null;

                if ($optionSetKey) {
                    // Option value resolution
                    $resolvedId = $modelClass::whereHas('optionSet', function ($q) use ($optionSetKey) {
                        $q->where('key', $optionSetKey);
                    })->where(function ($q) use ($data, $fieldName, $nameFieldAr, $nameFieldEn) {
                        $q->where($nameFieldAr, trim($data[$fieldName]))
                            ->orWhere($nameFieldEn, trim($data[$fieldName]));
                    })->value('id');
                } else {
                    // Direct model resolution
                    if ($modelClass === \App\Models\Lawyer::class) {
                        // Special case for lawyers - filter by partner titles
                        $resolvedId = $modelClass::whereHas('title', function ($q) {
                            $q->whereIn('label_en', ['Managing Partner', 'Senior Partner', 'Partner', 'Junior Partner']);
                        })->where(function ($q) use ($data, $fieldName, $nameFieldAr, $nameFieldEn) {
                            $q->where($nameFieldAr, trim($data[$fieldName]))
                                ->orWhere($nameFieldEn, trim($data[$fieldName]));
                        })->value('id');
                    } else {
                        $resolvedId = $modelClass::where(function ($q) use ($data, $fieldName, $nameFieldAr, $nameFieldEn) {
                            $q->where($nameFieldAr, trim($data[$fieldName]))
                                ->orWhere($nameFieldEn, trim($data[$fieldName]));
                        })->value('id');
                    }
                }

                if ($resolvedId) {
                    $data[$fieldName] = $resolvedId;
                }
            }
        }

        return $data;
    }

    /**
     * Resolve direct ID fields that might contain text instead of IDs (import-time path).
     * Mirrors preflight resolution to keep behavior consistent.
     */
    private function resolveDirectIdFields(array &$data, bool $skipOpponentId = false): void
    {
        Log::info('ImportController: resolving direct ID fields', [
            'fields_with_text' => array_filter($data, function ($value, $key) {
                return !is_numeric($value) && !empty($value) &&
                    in_array($key, ['court_id', 'client_capacity_id', 'opponent_capacity_id', 'matter_partner_id', 'circuit_secretary', 'circuit_name_id', 'matter_destination_id', 'opponent_id']);
            }, ARRAY_FILTER_USE_BOTH)
        ]);

        // court_id from court name (LIKE)
        if (!empty($data['court_id']) && !is_numeric($data['court_id'])) {
            $courtId = \App\Models\Court::where(function ($q) use ($data) {
                $q->where('court_name_en', 'like', '%' . $data['court_id'] . '%')
                    ->orWhere('court_name_ar', 'like', '%' . $data['court_id'] . '%');
            })->value('id');
            if ($courtId) {
                $data['court_id'] = $courtId;
            } else {
                throw new \Exception("court_id: No match found for '{$data['court_id']}'. Please create this court or map it to an existing one.");
            }
        }

        // matter_destination_id from court name (LIKE)
        if (!empty($data['matter_destination_id']) && !is_numeric($data['matter_destination_id'])) {
            $destinationId = \App\Models\Court::where(function ($q) use ($data) {
                $q->where('court_name_en', 'like', '%' . $data['matter_destination_id'] . '%')
                    ->orWhere('court_name_ar', 'like', '%' . $data['matter_destination_id'] . '%');
            })->value('id');
            if ($destinationId) {
                $data['matter_destination_id'] = $destinationId;
            } else {
                throw new \Exception("matter_destination_id: No match found for '{$data['matter_destination_id']}'. Please create this court or map it to an existing one.");
            }
        }

        // client_capacity_id (LIKE in option_values capacity.type)
        if (!empty($data['client_capacity_id']) && !is_numeric($data['client_capacity_id'])) {
            $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'capacity.type');
            })->where(function ($q) use ($data) {
                $q->where('label_en', 'like', '%' . $data['client_capacity_id'] . '%')
                    ->orWhere('label_ar', 'like', '%' . $data['client_capacity_id'] . '%');
            })->value('id');
            if ($capacityId) {
                $data['client_capacity_id'] = $capacityId;
            } else {
                throw new \Exception("client_capacity_id: No match found for '{$data['client_capacity_id']}'. Please create this capacity type or map it to an existing one.");
            }
        }

        // opponent_capacity_id (LIKE in option_values capacity.type)
        if (!empty($data['opponent_capacity_id']) && !is_numeric($data['opponent_capacity_id'])) {
            $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'capacity.type');
            })->where(function ($q) use ($data) {
                $q->where('label_en', 'like', '%' . $data['opponent_capacity_id'] . '%')
                    ->orWhere('label_ar', 'like', '%' . $data['opponent_capacity_id'] . '%');
            })->value('id');
            if ($capacityId) {
                $data['opponent_capacity_id'] = $capacityId;
            } else {
                throw new \Exception("opponent_capacity_id: No match found for '{$data['opponent_capacity_id']}'. Please create this capacity type or map it to an existing one.");
            }
        }

        // matter_partner_id from lawyer name (strip prefixes, try with title filter, then without)
        if (!empty($data['matter_partner_id']) && !is_numeric($data['matter_partner_id'])) {
            $search = trim((string) $data['matter_partner_id']);
            // Remove common Arabic prefixes like "أ.", "د." etc.
            $prefixes = ['أ.', 'د.', 'أستاذ.', 'أستاذة.', 'دكتور.', 'دكتورة.', 'محامي.', 'محامية.', 'السيد.', 'السيدة.', 'الأستاذ.', 'الأستاذة.'];
            foreach ($prefixes as $p) {
                if (str_starts_with($search, $p)) {
                    $search = trim(substr($search, strlen($p)));
                    break;
                }
            }
            // Normalize whitespace/invisibles
            try {
                $search = app(\App\Support\TextNormalizer::class)->normalize($search);
            } catch (\Throwable $e) {
            }

            // First, try with partner titles
            $lawyerId = \App\Models\Lawyer::whereHas('title', function ($q) {
                $q->whereIn('label_en', ['Managing Partner', 'Senior Partner', 'Partner', 'Junior Partner']);
            })->where(function ($q) use ($search) {
                $q->where('lawyer_name_en', 'like', '%' . $search . '%')
                    ->orWhere('lawyer_name_ar', 'like', '%' . $search . '%');
            })->value('id');

            // Fallback: any lawyer
            if (!$lawyerId) {
                $lawyerId = \App\Models\Lawyer::where(function ($q) use ($search) {
                    $q->where('lawyer_name_en', 'like', '%' . $search . '%')
                        ->orWhere('lawyer_name_ar', 'like', '%' . $search . '%');
                })->value('id');
            }

            if ($lawyerId) {
                $data['matter_partner_id'] = $lawyerId;
            } else {
                $original = $data['matter_partner_id'];
                throw new \Exception("matter_partner_id: No match found for '{$original}'. Please create this lawyer or map it to an existing one.");
            }
        }

        // circuit_secretary is an OptionValue under court.circuit_secretary (NOT a Lawyer)
        if (!empty($data['circuit_secretary']) && !is_numeric($data['circuit_secretary'])) {
            $secId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'court.circuit_secretary');
            })->where(function ($q) use ($data) {
                $q->where('label_en', 'like', '%' . $data['circuit_secretary'] . '%')
                    ->orWhere('label_ar', 'like', '%' . $data['circuit_secretary'] . '%');
            })->value('id');
            if ($secId) {
                $data['circuit_secretary'] = $secId;
            } else {
                throw new \Exception("circuit_secretary: No match found for '{$data['circuit_secretary']}'. Please create this circuit secretary or map it to an existing one.");
            }
        }

        // circuit_name_id from option_values circuit.name (LIKE)
        if (!empty($data['circuit_name_id']) && !is_numeric($data['circuit_name_id'])) {
            $circuitId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'circuit.name');
            })->where(function ($q) use ($data) {
                $q->where('label_en', 'like', '%' . $data['circuit_name_id'] . '%')
                    ->orWhere('label_ar', 'like', '%' . $data['circuit_name_id'] . '%');
            })->value('id');
            if ($circuitId) {
                $data['circuit_name_id'] = $circuitId;
            } else {
                throw new \Exception("circuit_name_id: No match found for '{$data['circuit_name_id']}'. Please create this circuit name or map it to an existing one.");
            }
        }

        // opponent_id from opponent name (LIKE with normalization and Arabic plural handling)
        // Skip if we already have a decision from preflight form
        if ($skipOpponentId && !empty($data['opponent_id']) && is_numeric($data['opponent_id'])) {
            Log::info('Skipping opponent_id resolution - already set by preflight decision', [
                'opponent_id' => $data['opponent_id']
            ]);
        } elseif (!$skipOpponentId && !empty($data['opponent_id']) && !is_numeric($data['opponent_id'])) {
            $search = trim((string) $data['opponent_id']);
            $originalSearch = $search;

            // Normalize the search text for better matching
            try {
                $normalizer = app(\App\Support\TextNormalizer::class);
                $normalizedSearch = $normalizer->normalize($search);

                // Strip brackets and parentheses content for base matching (e.g., "[رئيسة مجلس إدارة الدولية 21]" or "(مياتكو)")
                $baseSearch = preg_replace('/\s*\[[^\]]*\]/u', '', $normalizedSearch); // Remove [content]
                $baseSearch = preg_replace('/\s*\([^\)]*\)/u', '', $baseSearch); // Remove (content)
                $baseSearch = trim($baseSearch);

                // Normalize spacing around punctuation (dashes, parentheses, etc.)
                $normalizedSearch = preg_replace('/\s*-\s*/u', ' - ', $normalizedSearch); // Normalize dash spacing
                $normalizedSearch = preg_replace('/\s*\(\s*/u', ' (', $normalizedSearch); // Normalize opening paren
                $normalizedSearch = preg_replace('/\s*\)\s*/u', ') ', $normalizedSearch); // Normalize closing paren
                $normalizedSearch = preg_replace('/\s*\[\s*/u', ' [', $normalizedSearch); // Normalize opening bracket
                $normalizedSearch = preg_replace('/\s*\]\s*/u', '] ', $normalizedSearch); // Normalize closing bracket
                $normalizedSearch = preg_replace('/\s+/u', ' ', trim($normalizedSearch)); // Collapse whitespace

                // Create variants for matching
                $baseVariants = [];
                $baseVariants[] = $normalizedSearch; // Original normalized
                if ($baseSearch !== $normalizedSearch && mb_strlen($baseSearch) > 3) {
                    $baseVariants[] = $baseSearch; // Base without brackets/parens
                }

                // Create variant without spaces around dash (for more flexible matching)
                $noDashSpace = preg_replace('/\s*-\s*/u', '-', $normalizedSearch);
                if ($noDashSpace !== $normalizedSearch) {
                    $baseVariants[] = $noDashSpace;
                }
                $noDashSpaceBase = preg_replace('/\s*-\s*/u', '-', $baseSearch);
                if ($noDashSpaceBase !== $baseSearch && $noDashSpaceBase !== $noDashSpace) {
                    $baseVariants[] = $noDashSpaceBase;
                }

                // Handle Arabic plural endings: extract base before "وآخرون" / "وآخرين" / "وآخر" / "وأخرين" / "وأخرون"
                // This handles cases like "وزير المالية وآخرين" matching "وزير المالية وآخرون"
                // Also handle variations like "وأخرين" vs "وآخرين" (different hamza)
                if (preg_match('/^(.+?)\s+و[آأ]خر(ون|ين|ها|هم|هن)?$/u', $normalizedSearch, $matches)) {
                    $base = $matches[1];
                    $ending = $matches[2] ?? '';

                    // Base variants
                    $baseVariants[] = $base . ' وآخر'; // "وزير المالية وآخر"
                    $baseVariants[] = $base . ' وأخر'; // "وزير المالية وأخر" (alternative hamza)
                    $baseVariants[] = $base; // "وزير المالية" (base only)

                    // Try with all plural endings (swap endings)
                    $baseVariants[] = $base . ' وآخرون';
                    $baseVariants[] = $base . ' وآخرين';
                    $baseVariants[] = $base . ' وأخرون';
                    $baseVariants[] = $base . ' وأخرين';

                    // If we have a specific ending, try swapping it
                    if ($ending === 'ين') {
                        $baseVariants[] = $base . ' وآخرون'; // Swap ين to ون
                        $baseVariants[] = $base . ' وأخرون';
                    } elseif ($ending === 'ون') {
                        $baseVariants[] = $base . ' وآخرين'; // Swap ون to ين
                        $baseVariants[] = $base . ' وأخرين';
                    }
                }

                // Try to extract main name from longer phrases (e.g., "وزير المالية بصفته الرئيس الأعلى لمصلحة الضرائب وآخر")
                // Extract first meaningful segment (usually the main entity name)
                if (preg_match('/^(.+?)\s+(?:بصفته|في|ل|من|على|إلى|مع)/u', $normalizedSearch, $matches)) {
                    $mainName = trim($matches[1]);
                    if (mb_strlen($mainName) > 5) {
                        $baseVariants[] = $mainName;
                        // Also add with "وآخر" if it doesn't already have it
                        if (strpos($mainName, 'وآخر') === false) {
                            $baseVariants[] = $mainName . ' وآخر';
                        }
                    }
                }

                // For names with multiple parts, try first and last tokens (e.g., "سامي القريني" matching "سامي محمد أحمد القريني")
                $words = preg_split('/\s+/u', $baseSearch);
                if (count($words) >= 2) {
                    $firstLast = $words[0] . ' ' . $words[count($words) - 1];
                    if (mb_strlen($firstLast) > 5 && !in_array($firstLast, $baseVariants)) {
                        $baseVariants[] = $firstLast;
                    }
                }

                // Remove duplicates and short variants
                $baseVariants = array_unique(array_filter($baseVariants, function ($v) {
                    return mb_strlen($v) >= 3;
                }));
            } catch (\Throwable $e) {
                $normalizedSearch = $search;
                $baseVariants = [$search];
            }

            // Try exact match first
            $opponentId = null;
            $opponentId = \App\Models\Opponent::where(function ($q) use ($search) {
                $q->where('opponent_name_en', $search)
                    ->orWhere('opponent_name_ar', $search);
            })->value('id');

            // Second try: LIKE with all variants (search in DB, and DB contains search)
            if (!$opponentId) {
                $query = \App\Models\Opponent::where(function ($q) use ($search, $normalizedSearch, $baseVariants, $baseSearch) {
                    // Search text contained in DB fields
                    $q->where('opponent_name_en', 'like', '%' . $search . '%')
                        ->orWhere('opponent_name_ar', 'like', '%' . $search . '%');

                    if (isset($normalizedSearch)) {
                        $q->orWhere('normalized_name', 'like', '%' . $normalizedSearch . '%')
                            ->orWhere('opponent_name_ar', 'like', '%' . $normalizedSearch . '%')
                            ->orWhere('opponent_name_en', 'like', '%' . $normalizedSearch . '%');
                    }

                    // Try base search without brackets/parens
                    if (isset($baseSearch) && $baseSearch !== $normalizedSearch && mb_strlen($baseSearch) > 3) {
                        $q->orWhere('normalized_name', 'like', '%' . $baseSearch . '%')
                            ->orWhere('opponent_name_ar', 'like', '%' . $baseSearch . '%')
                            ->orWhere('opponent_name_en', 'like', '%' . $baseSearch . '%');
                    }

                    // Try all variants (both directions: search contains DB value, or DB contains search)
                    foreach ($baseVariants as $variant) {
                        if (mb_strlen($variant) > 3) {
                            // DB field contains variant
                            $q->orWhere('normalized_name', 'like', '%' . $variant . '%')
                                ->orWhere('opponent_name_ar', 'like', '%' . $variant . '%')
                                ->orWhere('opponent_name_en', 'like', '%' . $variant . '%');
                        }
                    }

                    // For partial name matches (e.g., "سامي القريني" matching "سامي محمد أحمد القريني وآخرون")
                    // Break search into tokens and ensure all key tokens appear in DB value
                    if (isset($baseSearch)) {
                        $tokens = preg_split('/\s+/u', $baseSearch);
                        // Only use token-based matching if we have 2-4 tokens (not too short, not too long)
                        if (count($tokens) >= 2 && count($tokens) <= 4) {
                            // For each token that's at least 3 characters, add it to the query
                            foreach ($tokens as $token) {
                                if (mb_strlen($token) >= 3) {
                                    // Each token should appear in the DB field
                                    $q->orWhere(function ($subQ) use ($token) {
                                        $subQ->where('normalized_name', 'like', '%' . $token . '%')
                                            ->orWhere('opponent_name_ar', 'like', '%' . $token . '%')
                                            ->orWhere('opponent_name_en', 'like', '%' . $token . '%');
                                    });
                                }
                            }
                        }
                    }
                });

                // Get all matches and score them to find the best one
                $matches = $query->get();

                if ($matches->count() > 0) {
                    // Score matches: exact > normalized > variant > token-based
                    // Prefer matches with more of the search text
                    $bestMatch = null;
                    $bestScore = 0;

                    foreach ($matches as $match) {
                        $score = 0;
                        $dbNameAr = $match->opponent_name_ar ?? '';
                        $dbNameEn = $match->opponent_name_en ?? '';
                        $dbNormalized = $match->normalized_name ?? '';

                        // Exact match gets highest score
                        if ($dbNameAr === $search || $dbNameEn === $search) {
                            $score = 1000;
                        } elseif (isset($normalizedSearch) && $dbNormalized === $normalizedSearch) {
                            $score = 900;
                        } elseif (isset($baseSearch) && ($dbNameAr === $baseSearch || $dbNormalized === $baseSearch)) {
                            $score = 800;
                        } else {
                            // Calculate similarity based on how much of the search appears in DB
                            $searchLength = mb_strlen($search);
                            if (mb_strpos($dbNameAr, $search) !== false || mb_strpos($dbNormalized, $search) !== false) {
                                $score = 700;
                            } elseif (isset($normalizedSearch) && (mb_strpos($dbNameAr, $normalizedSearch) !== false || mb_strpos($dbNormalized, $normalizedSearch) !== false)) {
                                $score = 600;
                            } elseif (isset($baseSearch) && (mb_strpos($dbNameAr, $baseSearch) !== false || mb_strpos($dbNormalized, $baseSearch) !== false)) {
                                $score = 500;
                            } else {
                                // Token-based match - count how many tokens match
                                // For valid token-based match, we need at least 2 tokens AND all tokens should match
                                if (isset($baseSearch)) {
                                    $tokens = preg_split('/\s+/u', $baseSearch);
                                    $filteredTokens = array_filter($tokens, function ($t) {
                                        return mb_strlen($t) >= 3;
                                    });

                                    if (count($filteredTokens) >= 2) {
                                        $matchedTokens = 0;
                                        foreach ($filteredTokens as $token) {
                                            if (mb_strpos($dbNameAr, $token) !== false || mb_strpos($dbNormalized, $token) !== false) {
                                                $matchedTokens++;
                                            }
                                        }
                                        // Only score if ALL tokens match (high confidence partial match)
                                        if ($matchedTokens === count($filteredTokens)) {
                                            $score = 450 + ($matchedTokens * 20); // Higher score for complete token match
                                        } elseif ($matchedTokens >= 2) {
                                            // Partial token match (at least 2 tokens match)
                                            $score = 400 + ($matchedTokens * 10);
                                        }
                                    }
                                }
                            }
                        }

                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestMatch = $match;
                        }
                    }

                    if ($bestMatch && $bestScore >= 400) { // Only accept if score is reasonable
                        $opponentId = $bestMatch->id;
                    }
                }
            }

            if ($opponentId) {
                $data['opponent_id'] = $opponentId;
                Log::info('Opponent ID resolved', [
                    'original' => $originalSearch,
                    'resolved_id' => $opponentId
                ]);
            } else {
                // Log for debugging
                Log::warning('Opponent ID not found', [
                    'search_value' => $originalSearch,
                    'normalized' => $normalizedSearch ?? 'N/A',
                    'variants_tried' => $baseVariants ?? []
                ]);
                throw new \Exception("opponent_id: No match found for '{$originalSearch}'. Please create this opponent or map it to an existing one.");
            }
        }
    }

    /**
     * Apply preflight resolutions to data fields before text resolution.
     * If a field was resolved in preflight, use the resolved_id instead of trying to resolve text again.
     */
    private function applyPreflightResolutions(array &$data, ImportSession $session, int $rowIndex): void
    {
        $preflightErrors = is_array($session->preflight_errors) ? $session->preflight_errors : [];
        if (empty($preflightErrors)) {
            return;
        }

        $normalizer = app(\App\Support\TextNormalizer::class);
        $appliedCount = 0;

        // Note: preflight row numbers are 1-indexed (row 0 is header), while $rowIndex is 0-indexed from foreach
        // So we need to match rowIndex + 1 with error['row']
        $preflightRowNumber = $rowIndex + 1;

        foreach ($preflightErrors as $error) {
            if (!is_array($error) || empty($error['resolved']) || !isset($error['column']) || !isset($error['resolved_id'])) {
                continue;
            }

            $errorColumn = $error['column']; // This might be source column or target column
            $resolvedId = (int) $error['resolved_id'];

            // Map error column to target column (in case error has source column name)
            $targetColumn = $errorColumn;
            if (isset($session->column_mapping)) {
                // Check if error column is a source column that maps to a target
                foreach ($session->column_mapping as $sourceCol => $targetCol) {
                    if ($sourceCol === $errorColumn && !empty($targetCol)) {
                        $targetColumn = $targetCol;
                        break;
                    }
                }
            }
            // Also check if error column is already a target column
            $column = $targetColumn;

            // Check if this resolved error applies to the current row
            $matches = false;

            // Match by row number (preflight uses 1-indexed, our loop is 0-indexed)
            if (isset($error['row']) && ((int) $error['row'] === $preflightRowNumber || (int) $error['row'] === $rowIndex)) {
                $matches = true;
            }

            // Match by normalized value (if column matches and values are similar)
            if (!$matches && isset($error['value']) && isset($data[$column])) {
                $errorValue = (string) $error['value'];
                $dataValue = (string) $data[$column];

                // Normalize both for comparison (with punctuation spacing normalization)
                $normalizedError = $normalizer->normalize($errorValue);
                $normalizedData = $normalizer->normalize($dataValue);

                // Normalize spacing around punctuation
                $normalizedError = preg_replace('/\s*-\s*/u', ' - ', $normalizedError);
                $normalizedError = preg_replace('/\s+/u', ' ', trim($normalizedError));
                $normalizedData = preg_replace('/\s*-\s*/u', ' - ', $normalizedData);
                $normalizedData = preg_replace('/\s+/u', ' ', trim($normalizedData));

                // Exact match or LIKE-style match (contains or is contained)
                if (
                    $normalizedError === $normalizedData ||
                    strpos($normalizedError, $normalizedData) !== false ||
                    strpos($normalizedData, $normalizedError) !== false
                ) {
                    $matches = true;
                }
            }

            // Special case for opponent_id: also check if it might come from opponent_name column mapping
            if (!$matches && $column === 'opponent_id' && isset($error['value'])) {
                // Check all possible source columns that might map to opponent_id
                foreach ($session->column_mapping ?? [] as $sourceCol => $targetCol) {
                    if ($targetCol === 'opponent_id' && isset($data['opponent_id'])) {
                        $errorValue = (string) $error['value'];
                        $dataValue = (string) $data['opponent_id'];

                        $normalizedError = $normalizer->normalize($errorValue);
                        $normalizedData = $normalizer->normalize($dataValue);
                        $normalizedError = preg_replace('/\s*-\s*/u', ' - ', $normalizedError);
                        $normalizedError = preg_replace('/\s+/u', ' ', trim($normalizedError));
                        $normalizedData = preg_replace('/\s*-\s*/u', ' - ', $normalizedData);
                        $normalizedData = preg_replace('/\s+/u', ' ', trim($normalizedData));

                        if (
                            $normalizedError === $normalizedData ||
                            strpos($normalizedError, $normalizedData) !== false ||
                            strpos($normalizedData, $normalizedError) !== false
                        ) {
                            $matches = true;
                            break;
                        }
                    }
                }
            }

            // Apply the resolved ID if match found
            if ($matches && isset($data[$column]) && !is_numeric($data[$column])) {
                // Only apply if the field still contains text (not already resolved)
                $originalValue = $data[$column];
                $data[$column] = $resolvedId;
                $appliedCount++;
                Log::info('Applied preflight resolution', [
                    'row' => $rowIndex,
                    'preflight_row' => $preflightRowNumber,
                    'column' => $column,
                    'resolved_id' => $resolvedId,
                    'original_value' => $originalValue,
                    'error_row' => $error['row'] ?? 'N/A',
                    'error_value' => $error['value'] ?? 'N/A'
                ]);
            }
        }

        if ($appliedCount > 0) {
            Log::info('Preflight resolutions applied', [
                'row' => $rowIndex,
                'applied_count' => $appliedCount
            ]);
        } else {
            Log::debug('No preflight resolutions applied', [
                'row' => $rowIndex,
                'preflight_row' => $preflightRowNumber,
                'data_opponent_id' => $data['opponent_id'] ?? 'not set',
                'resolved_errors_count' => count(array_filter($preflightErrors, fn($e) => !empty($e['resolved'])))
            ]);
        }
    }

    /**
     * Normalize empty values for nullable foreign key fields: convert empty strings to NULL.
     */
    private function normalizeEmptyForeignKeys(array &$data): void
    {
        // List of nullable foreign key fields that should be NULL instead of empty string
        $nullableFkFields = [
            'client_capacity_id',
            'opponent_capacity_id',
            'opponent_id',
            'matter_partner_id',
            'court_id',
            'matter_destination_id',
            'circuit_name_id',
            'circuit_secretary',
            'circuit_serial_id',
            'circuit_shift_id',
            'matter_category_id',
            'matter_degree_id',
            'matter_status_id',
            'matter_importance_id',
            'client_type_id',
            'contract_id',
        ];

        foreach ($nullableFkFields as $field) {
            if (isset($data[$field])) {
                // Convert empty string, whitespace-only string, or '0' (if not a valid ID) to NULL
                $value = $data[$field];
                if ($value === '' || $value === null || (is_string($value) && trim($value) === '')) {
                    $data[$field] = null;
                } elseif (is_string($value) && !is_numeric($value)) {
                    // If it's a string but not numeric, it should have been resolved by resolveDirectIdFields
                    // If it wasn't resolved, it means no match was found and an exception was thrown
                    // So we leave it as is (will cause an exception which is the desired behavior)
                } elseif (is_numeric($value) && (int)$value === 0) {
                    // Convert '0' to NULL for nullable FKs (0 is not a valid ID)
                    $data[$field] = null;
                }
            }
        }
    }

    /**
     * Clean string fields for cases table: remove newlines, carriage returns, and enforce max lengths.
     */
    private function cleanCaseStringFields(array &$data): void
    {
        // Field max lengths (matching database schema)
        // Note: matter_evaluation is now TEXT, so no length limit needed
        $maxLengths = [
            'matter_shelf' => 10,
            'client_branch' => 255, // default string length
            'engagement_letter_no' => 255,
            'client_in_case_name' => 255,
            'opponent_in_case_name' => 255,
            // matter_evaluation is TEXT, no truncation needed
        ];

        foreach ($maxLengths as $field => $maxLen) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $original = $data[$field];

                // First, handle escaped forms like "_x000D_" (Excel/CSV escape sequences)
                // Match _x followed by 4 hex digits followed by _
                $cleaned = preg_replace('/_x[0-9A-Fa-f]{4}_/iu', ' ', $data[$field]);

                // Also handle other Excel escape patterns
                $cleaned = preg_replace('/_x([0-9A-Fa-f]{4})/iu', ' ', $cleaned); // _x000D without trailing underscore

                // Remove newlines, carriage returns (\r\n, \n, \r, and Unicode carriage return)
                $cleaned = preg_replace('/[\r\n\x{000D}\x{000A}]/u', ' ', $cleaned);

                // Remove any remaining control characters (except spaces)
                $cleaned = preg_replace('/[\x{0000}-\x{001F}\x{007F}-\x{009F}]/u', '', $cleaned);

                // Normalize whitespace (collapse multiple spaces, trim)
                $cleaned = preg_replace('/\s+/u', ' ', trim($cleaned));

                // Truncate to max length (ensure we don't exceed database limit)
                if (mb_strlen($cleaned) > $maxLen) {
                    $truncatedValue = mb_substr($cleaned, 0, $maxLen);
                    Log::warning("ImportController: truncated field '{$field}' to {$maxLen} characters", [
                        'original_length' => mb_strlen($original),
                        'cleaned_length' => mb_strlen($cleaned),
                        'original_value' => mb_substr($original, 0, 100) . '...', // Log first 100 chars
                        'truncated_value' => $truncatedValue
                    ]);
                    $cleaned = $truncatedValue;
                }

                // Final safety check - ensure length is within limit (handles edge cases with multi-byte characters)
                $finalLength = mb_strlen($cleaned);
                if ($finalLength > $maxLen) {
                    $cleaned = mb_substr($cleaned, 0, $maxLen);
                    Log::warning("ImportController: second truncation applied for field '{$field}'", [
                        'previous_length' => $finalLength,
                        'final_length' => mb_strlen($cleaned)
                    ]);
                }

                $data[$field] = $cleaned;

                // Log if escape sequences were removed
                if ($original !== $cleaned && (strpos($original, '_x') !== false || strpos($original, "\r") !== false || strpos($original, "\n") !== false)) {
                    Log::info("ImportController: cleaned field '{$field}'", [
                        'original_preview' => mb_substr($original, 0, 50),
                        'cleaned_preview' => mb_substr($cleaned, 0, 50)
                    ]);
                }
            }
        }

        // Special handling for matter_evaluation (TEXT field - clean but don't truncate)
        if (isset($data['matter_evaluation']) && is_string($data['matter_evaluation'])) {
            $original = $data['matter_evaluation'];

            // Handle Excel/CSV escape sequences
            $cleaned = preg_replace('/_x[0-9A-Fa-f]{4}_/iu', ' ', $original);
            $cleaned = preg_replace('/_x([0-9A-Fa-f]{4})/iu', ' ', $cleaned);

            // Normalize newlines to spaces (or keep them - you may want to preserve line breaks)
            // For now, we'll convert newlines to spaces to keep it clean
            $cleaned = preg_replace('/[\r\n]+/u', ' ', $cleaned);

            // Remove control characters (except spaces)
            $cleaned = preg_replace('/[\x{0000}-\x{001F}\x{007F}-\x{009F}]/u', '', $cleaned);

            // Normalize whitespace (collapse multiple spaces, but preserve meaningful spacing)
            $cleaned = preg_replace('/[ \t]+/u', ' ', trim($cleaned));

            // No truncation - TEXT field can handle much longer content
            $data['matter_evaluation'] = $cleaned;

            if ($original !== $cleaned) {
                Log::info("ImportController: cleaned matter_evaluation (TEXT field, no truncation)", [
                    'original_length' => mb_strlen($original),
                    'cleaned_length' => mb_strlen($cleaned),
                    'original_preview' => mb_substr($original, 0, 100),
                    'cleaned_preview' => mb_substr($cleaned, 0, 100)
                ]);
            }
        }
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

    /**
     * Download Cases Standard CSV template
     */
    public function downloadCaseTemplateStandardCsv()
    {
        if (!Gate::allows('import.view_template')) {
            abort(403, 'Unauthorized to view import templates.');
        }

        $path = storage_path('app/templates/Cases_Import_Template_Standard.csv');

        if (!file_exists($path)) {
            abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=standard');
        }

        return response()->download(
            $path,
            'Cases_Import_Template_Standard.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Download Cases Standard XLSX template
     */
    public function downloadCaseTemplateStandardXlsx()
    {
        if (!Gate::allows('import.view_template')) {
            abort(403, 'Unauthorized to view import templates.');
        }

        $path = storage_path('app/templates/Cases_Import_Template_Standard.xlsx');

        if (!file_exists($path)) {
            abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=standard');
        }

        return response()->download($path, 'Cases_Import_Template_Standard.xlsx');
    }

    /**
     * Download Cases Extended CSV template
     */
    public function downloadCaseTemplateExtendedCsv()
    {
        if (!Gate::allows('import.view_template')) {
            abort(403, 'Unauthorized to view import templates.');
        }

        $path = storage_path('app/templates/Cases_Import_Template_Extended.csv');

        if (!file_exists($path)) {
            abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=extended');
        }

        return response()->download(
            $path,
            'Cases_Import_Template_Extended.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Download Cases Extended XLSX template
     */
    public function downloadCaseTemplateExtendedXlsx()
    {
        if (!Gate::allows('import.view_template')) {
            abort(403, 'Unauthorized to view import templates.');
        }

        $path = storage_path('app/templates/Cases_Import_Template_Extended.xlsx');

        if (!file_exists($path)) {
            abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=extended');
        }

        return response()->download($path, 'Cases_Import_Template_Extended.xlsx');
    }

    /**
     * Regenerate all Cases import templates (Admin only)
     */
    public function regenerateCaseTemplates(Request $request)
    {
        if (!Gate::allows('admin.tools.manage')) {
            abort(403, 'Unauthorized to regenerate templates.');
        }

        try {
            Artisan::call('templates:generate-cases', ['--mode' => 'all']);

            return back()->with('success', __('app.template_regenerated_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', 'Template regeneration failed: ' . $e->getMessage());
        }
    }

    /**
     * Process multiple opponents for a case.
     */
    private function processCaseOpponents(array $opponents, ?int $caseId): void
    {
        if (!$caseId) {
            return;
        }

        $caseOpponentService = app(\App\Services\CaseOpponentService::class);

        foreach ($opponents as $opponentData) {
            try {
                // Resolve opponent ID
                $opponentId = $this->resolveOpponentId($opponentData);
                if (!$opponentId) {
                    continue;
                }

                // Resolve capacity ID
                $capacityId = $this->resolveCapacityId($opponentData);

                // Attach opponent to case
                $caseOpponentService->attachOpponent(
                    \App\Models\CaseModel::find($caseId),
                    $opponentId,
                    $capacityId,
                    $opponentData['is_primary'] ?? false,
                    $opponentData['order'] ?? null,
                    $opponentData['alias'] ?? null
                );
            } catch (\Exception $e) {
                Log::error('Failed to attach opponent to case', [
                    'case_id' => $caseId,
                    'opponent_data' => $opponentData,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Resolve opponent ID from name or ID.
     */
    private function resolveOpponentId(array $opponentData): ?int
    {
        // If ID is provided, use it
        if (!empty($opponentData['id'])) {
            return (int) $opponentData['id'];
        }

        // If name is provided, find by name
        if (!empty($opponentData['name'])) {
            $opponent = \App\Models\Opponent::where('opponent_name_ar', $opponentData['name'])
                ->orWhere('opponent_name_en', $opponentData['name'])
                ->first();

            if ($opponent) {
                return $opponent->id;
            }
        }

        return null;
    }

    /**
     * Resolve capacity ID from name or ID.
     */
    private function resolveCapacityId(array $opponentData): ?int
    {
        // If ID is provided, use it
        if (!empty($opponentData['capacity_id'])) {
            return (int) $opponentData['capacity_id'];
        }

        // If name is provided, find by name
        if (!empty($opponentData['capacity'])) {
            $capacity = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('set_name', 'capacity');
            })->where(function ($q) use ($opponentData) {
                $q->where('label_en', $opponentData['capacity'])
                    ->orWhere('label_ar', $opponentData['capacity']);
            })->first();

            if ($capacity) {
                return $capacity->id;
            }
        }

        return null;
    }

    /**
     * Show case opponents import upload form.
     */
    public function uploadCaseOpponents()
    {
        $this->authorize('upload', ImportSession::class);

        return view('import.case-opponents-upload');
    }

    /**
     * Process case opponents import upload.
     */
    public function processCaseOpponentsUpload(Request $request)
    {
        $this->authorize('upload', ImportSession::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            // Create import session for case_opponents table
            $session = $this->importService->uploadFile(
                $request->file('file'),
                'case_opponents',
                Auth::id()
            );

            return redirect()->route('import.map', $session);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download case opponents template CSV.
     */
    public function downloadCaseOpponentsTemplateCsv()
    {
        $this->authorize('viewTemplate', ImportSession::class);

        $path = storage_path('app/templates/Case_Opponents_Import_Template.csv');

        if (!file_exists($path)) {
            // Generate template if it doesn't exist
            Artisan::call('templates:generate-case-opponents', ['--format' => 'csv']);
        }

        return response()->download($path, 'Case_Opponents_Import_Template.csv');
    }

    /**
     * Download case opponents template XLSX.
     */
    public function downloadCaseOpponentsTemplateXlsx()
    {
        $this->authorize('viewTemplate', ImportSession::class);

        $path = storage_path('app/templates/Case_Opponents_Import_Template.xlsx');

        if (!file_exists($path)) {
            // Generate template if it doesn't exist
            Artisan::call('templates:generate-case-opponents', ['--format' => 'xlsx']);
        }

        return response()->download($path, 'Case_Opponents_Import_Template.xlsx');
    }

    /**
     * Download Hearings CSV template
     */
    public function downloadHearingsTemplateCsv()
    {
        if (!Gate::allows('import.view_template')) {
            abort(403, 'Unauthorized to view import templates.');
        }

        $path = storage_path('app/templates/Hearings_Import_Template.csv');

        if (!file_exists($path)) {
            // Generate template if it doesn't exist
            Artisan::call('templates:generate-hearings');
        }

        return response()->download(
            $path,
            'Hearings_Import_Template.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Download Hearings XLSX template
     */
    public function downloadHearingsTemplateXlsx()
    {
        if (!Gate::allows('import.view_template')) {
            abort(403, 'Unauthorized to view import templates.');
        }

        $path = storage_path('app/templates/Hearings_Import_Template.xlsx');

        if (!file_exists($path)) {
            // Generate template if it doesn't exist
            Artisan::call('templates:generate-hearings');
        }

        return response()->download($path, 'Hearings_Import_Template.xlsx');
    }
}
