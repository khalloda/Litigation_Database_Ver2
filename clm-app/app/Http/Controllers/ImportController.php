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
use App\Support\NameNormalizer;
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

        \Log::info('Preflight method called', [
            'sessionId' => $session->id,
            'column_mapping' => $session->column_mapping,
            'isEmpty' => empty($session->column_mapping)
        ]);

        if (empty($session->column_mapping)) {
            \Log::warning('Column mapping is empty, redirecting to map', [
                'sessionId' => $session->id
            ]);
            return redirect()
                ->route('import.map', $session->id)
                ->with('error', __('app.please_configure_mapping_first'));
        }

        try {
            \Log::info('Starting preflight processing', ['sessionId' => $session->id]);

            // Parse file
            $filepath = $this->importService->getSessionFilePath($session);
            \Log::info('Got file path', ['filepath' => $filepath]);

            $parsed = $this->parserService->parseFile($filepath, $session->file_type);
            \Log::info('File parsed successfully', ['rowCount' => count($parsed['rows'])]);

            // Run preflight validation
            $results = $this->preflightEngine->runPreflight(
                $parsed['rows'],
                $session->column_mapping,
                $session->table_name
            );
            \Log::info('Preflight validation completed', ['errorCount' => $results['error_count']]);

            // Opponent suggestions (only for cases table and when an incoming opponent name exists)
            $opponentSuggestions = [];
            if ($session->table_name === 'cases') {
                // Try typical columns that might contain opponent name text
                $candidateCols = ['opponent_name', 'opponent', 'opponent_and_capacity'];
                foreach ($parsed['rows'] as $i => $row) {
                    $incoming = null;
                    foreach ($candidateCols as $col) {
                        if (array_key_exists($col, $row) && !empty($row[$col])) {
                            $incoming = $row[$col];
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

            return view('import.preflight', compact('session', 'results', 'exceedsThreshold', 'opponentSuggestions', 'parsed'));
        } catch (Exception $e) {
            \Log::error('Exception in preflight method', [
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
                }

                // Cases: apply preflight opponent decisions
                $incomingOpponent = null;
                if ($session->table_name === 'cases') {
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
                }

                // Insert into database
                DB::table($session->table_name)->insert($data);
                $imported++;

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
            'errors' => array_slice($errors, 0, 100), // Limit errors
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
