<?php

namespace App\Console\Commands;

use App\Services\ETL\HearingsDataNormalizer;
use App\Services\ETL\HearingsFKResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HearingsPreflightCommand extends Command
{
    protected $signature = 'hearings:preflight {--file=}';
    protected $description = 'Load Excel/CSV → transform → insert into hearings_staging table';

    private $normalizer;
    private $resolver;
    private $artifactsDir;
    private $stats = [
        'total_rows' => 0,
        'valid_rows' => 0,
        'skipped_rows' => 0,
        'date_null_count' => 0,
        'next_null_count' => 0,
        'external_id_null_count' => 0,
        'external_id_duplicate_count' => 0,
        'errors' => [],
    ];
    private $externalIds = []; // Track external_id duplicates

    public function __construct()
    {
        parent::__construct();
        $this->normalizer = new HearingsDataNormalizer();
        $this->resolver = new HearingsFKResolver();
    }

    public function handle(): int
    {
        // Set UTF-8 encoding
        mb_internal_encoding('UTF-8');

        $filePath = $this->option('file');
        if (!$filePath) {
            $this->error('Please provide --file option');
            return 1;
        }

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Create artifacts directory
        $timestamp = date('Ymd-His');
        $this->artifactsDir = storage_path("app/imports/hearings/{$timestamp}");
        if (!is_dir($this->artifactsDir)) {
            mkdir($this->artifactsDir, 0755, true);
        }

        $this->info("Starting preflight import from: {$filePath}");
        $this->info("Artifacts directory: {$this->artifactsDir}");

        // Load lookup tables
        $this->info("Loading lookup tables...");
        $this->resolver->loadLookupTables();

        // Load manual mappings if exists
        $mappingFile = storage_path('app/imports/hearings/manual_mappings.csv');
        if (file_exists($mappingFile)) {
            $this->resolver->loadManualMappings($mappingFile);
            $this->info("Loaded manual mappings from: {$mappingFile}");
        }

        // Process file
        try {
            $this->processFile($filePath);
        } catch (\Exception $e) {
            $this->error("Preflight failed: {$e->getMessage()}");
            return 1;
        }

        // Write artifacts
        $this->writeArtifacts();

        // Display summary
        $this->displaySummary();

        return 0;
    }

    private function processFile(string $filePath): void
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $sourceFileName = basename($filePath);

        if ($extension === 'csv') {
            $this->processCsv($filePath, $sourceFileName);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $this->processExcel($filePath, $sourceFileName);
        } else {
            throw new \RuntimeException("Unsupported file format: {$extension}");
        }
    }

    private function processCsv(string $filePath, string $sourceFileName): void
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Cannot open CSV file: {$filePath}");
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new \RuntimeException("Cannot read CSV headers");
        }

        $rowNumber = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $this->stats['total_rows']++;

            $rowData = array_combine($headers, $row);
            $this->processRow($rowData, $sourceFileName, $rowNumber);
        }

        fclose($handle);
    }

    private function processExcel(string $filePath, string $sourceFileName): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);
        $headers = array_shift($rows);

        $rowNumber = 0;
        foreach ($rows as $row) {
            $rowNumber++;
            $this->stats['total_rows']++;

            $rowData = [];
            foreach ($headers as $colIndex => $header) {
                $rowData[$header] = $row[$colIndex] ?? null;
            }

            $this->processRow($rowData, $sourceFileName, $rowNumber);
        }
    }

    private function processRow(array $row, string $sourceFile, int $sourceRow): void
    {
        try {
            // Parse external_id from source file (id column)
            $externalId = $this->normalizer->parseExternalId($row['id'] ?? $row['hearings_id'] ?? null);
            if (!$externalId) {
                $this->stats['external_id_null_count']++;
            } else {
                // Track duplicates
                if (isset($this->externalIds[$externalId])) {
                    $this->stats['external_id_duplicate_count']++;
                    $this->externalIds[$externalId]++;
                } else {
                    $this->externalIds[$externalId] = 1;
                }
            }

            // Parse legacy hearings_id (kept for compatibility)
            $hearingId = $this->normalizer->parseInt($row['hearings_id'] ?? null);

            // Validate matter_id
            $matterId = $this->resolver->resolveMatterId(
                $row['matter_id'] ?? null,
                $sourceFile,
                $sourceRow
            );

            if (!$matterId) {
                $this->stats['skipped_rows']++;
                $this->stats['errors'][] = [
                    'row' => $sourceRow,
                    'field' => 'matter_id',
                    'error' => 'Orphaned matter_id or invalid',
                ];
                return;
            }

            // Parse dates with raw values
            $dateData = $this->normalizer->parseDateWithRaw($row['date'] ?? null);
            if (!$dateData['date']) {
                $this->stats['date_null_count']++;
            }

            $nextHearingData = $this->normalizer->parseDateWithRaw($row['nextHearing'] ?? null);
            if (!$nextHearingData['date']) {
                $this->stats['next_null_count']++;
            }

            // Parse circuit
            $circuitData = $this->normalizer->parseCircuit(
                $row['الدائرة'] ?? null,
                $row['اسم الدائرة'] ?? null,
                $row['ملاحظات الدائرة'] ?? null,
                $row['مسلسل الدائرة'] ?? null
            );

            // Try to find lawyer_id from attendee names
            $lawyerId = null;
            $attendeeFields = [
                $row['ملاحظات الحاضر'] ?? null,
                $row['حاضر 1'] ?? null,
                $row['حاضر 2'] ?? null,
                $row['حاضر 3'] ?? null,
                $row['حاضر 4'] ?? null,
                $row['حضور الجلسة القادمة'] ?? null,
            ];

            foreach ($attendeeFields as $attendee) {
                if ($attendee) {
                    $lawyerId = $this->resolver->resolveLawyerId($attendee, $sourceFile, $sourceRow);
                    if ($lawyerId) {
                        break;
                    }
                }
            }

            // Build notes (merge original-only columns)
            $notes = [];
            if (!empty($row['ملاحظات'] ?? null)) {
                $notes[] = $this->normalizer->cleanString($row['ملاحظات']);
            }
            if (!empty($row['تاريخ تبليغ القرار'] ?? null)) {
                $notes[] = 'تاريخ تبليغ القرار: ' . $this->normalizer->cleanString($row['تاريخ تبليغ القرار']);
            }
            if (!empty($circuitData['notes'])) {
                $notes[] = 'ملاحظات الدائرة: ' . $circuitData['notes'];
            }
            $notesText = implode(' | ', $notes);

            // Build staging row
            $stagingData = [
                'external_id' => $externalId,
                'id' => $hearingId, // Legacy column
                'matter_id' => $matterId,
                'lawyer_id' => $lawyerId,
                'date' => $dateData['date'],
                'date_raw' => $dateData['raw'],
                'next_hearing' => $nextHearingData['date'],
                'next_hearing_raw' => $nextHearingData['raw'],
                'procedure' => $this->normalizer->cleanString($row['الإجراء'] ?? null),
                'court' => $this->normalizer->cleanString($row['المحكمة'] ?? null),
                'circuit' => $circuitData['circuit'],
                'destination' => $this->normalizer->cleanString($row['الجهة'] ?? null),
                'decision' => $this->normalizer->cleanString($row['decision'] ?? null),
                'short_decision' => $this->normalizer->cleanString($row['shortDecision'] ?? null),
                'last_decision' => $this->normalizer->cleanString($row['lastDecision'] ?? null),
                'report' => $this->normalizer->parseBoolean($row['report'] ?? false),
                'notify_client' => $this->normalizer->parseBoolean($row['إخطار العميل بالقرار'] ?? false),
                'attendee' => $this->normalizer->cleanString($row['ملاحظات الحاضر'] ?? null),
                'attendee_1' => $this->normalizer->cleanString($row['حاضر 1'] ?? null),
                'attendee_2' => $this->normalizer->cleanString($row['حاضر 2'] ?? null),
                'attendee_3' => $this->normalizer->cleanString($row['حاضر 3'] ?? null),
                'attendee_4' => $this->normalizer->cleanString($row['حاضر 4'] ?? null),
                'next_attendee' => $this->normalizer->cleanString($row['حضور الجلسة القادمة'] ?? null),
                'evaluation' => $this->normalizer->cleanString($row['صالح/ضد'] ?? null),
                'notes' => $notesText ?: null,
                'source_file' => $sourceFile,
                'source_row' => $sourceRow,
                'mapping_profile' => null,
                'transform_warnings' => json_encode($this->resolver->getWarnings()),
                'loaded_at' => now(),
            ];

            // Truncate strings to max length
            foreach (['procedure', 'court', 'circuit', 'destination', 'short_decision', 'last_decision', 
                     'attendee', 'attendee_1', 'attendee_2', 'attendee_3', 'attendee_4', 'next_attendee', 'evaluation'] as $field) {
                if (isset($stagingData[$field]) && $stagingData[$field]) {
                    $stagingData[$field] = mb_substr($stagingData[$field], 0, 255);
                }
            }

            // Insert into staging
            DB::table('hearings_staging')->insert($stagingData);
            $this->stats['valid_rows']++;

        } catch (\Exception $e) {
            $this->stats['skipped_rows']++;
            $this->stats['errors'][] = [
                'row' => $sourceRow,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Write UTF-8 safe CSV with BOM for Excel compatibility.
     */
    private function writeCsvWithBom(string $filePath, array $headers, array $rows): void
    {
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            throw new \RuntimeException("Cannot open file for writing: {$filePath}");
        }
        
        // Write UTF-8 BOM for Excel on Windows
        fwrite($handle, "\xEF\xBB\xBF");
        
        // Write headers
        $safeHeaders = array_map(function ($h) {
            return iconv('UTF-8', 'UTF-8//IGNORE', (string)$h);
        }, $headers);
        fputcsv($handle, $safeHeaders);
        
        // Write rows
        foreach ($rows as $row) {
            $safeRow = array_map(function ($v) {
                return iconv('UTF-8', 'UTF-8//IGNORE', (string)$v);
            }, $row);
            fputcsv($handle, $safeRow);
        }
        
        fclose($handle);
    }

    private function writeArtifacts(): void
    {
        // Write DB mappings CSV (UTF-8 with BOM)
        $lookupTables = $this->resolver->getLookupTables();
        $mappingsFile = "{$this->artifactsDir}/db_mappings.csv";
        $mappingsRows = [];
        foreach ($lookupTables as $type => $items) {
            foreach ($items as $item) {
                $mappingsRows[] = [
                    $type,
                    $item['id'],
                    $item['name_ar'] ?? '',
                    $item['name_en'] ?? '',
                ];
            }
        }
        $this->writeCsvWithBom($mappingsFile, ['Lookup_Type', 'ID', 'Name_AR', 'Name_EN'], $mappingsRows);
        $this->info("DB mappings written to: {$mappingsFile}");

        // Write unmatched CSV (UTF-8 with BOM)
        $unmatched = $this->resolver->getUnmatched();
        if (!empty($unmatched)) {
            $unmatchedFile = "{$this->artifactsDir}/unmatched.csv";
            $unmatchedRows = [];
            foreach ($unmatched as $item) {
                $unmatchedRows[] = [
                    $item['field'],
                    $item['value'],
                    $item['source_file'],
                    $item['source_row'],
                    $item['reason'],
                ];
            }
            $this->writeCsvWithBom($unmatchedFile, ['field', 'value', 'source_file', 'source_row', 'reason'], $unmatchedRows);
            $this->warn("Unmatched values written to: {$unmatchedFile}");
        }

        // Write errors JSON (UTF-8)
        if (!empty($this->stats['errors'])) {
            $errorsFile = "{$this->artifactsDir}/errors.json";
            $json = json_encode($this->stats['errors'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            file_put_contents($errorsFile, $json);
            $this->warn("Errors written to: {$errorsFile}");
        }

        // Write summary JSON (UTF-8)
        $summary = [
            'total_rows' => $this->stats['total_rows'],
            'valid_rows' => $this->stats['valid_rows'],
            'skipped_rows' => $this->stats['skipped_rows'],
            'date_null_count' => $this->stats['date_null_count'],
            'next_null_count' => $this->stats['next_null_count'],
            'external_id_null_count' => $this->stats['external_id_null_count'],
            'external_id_duplicate_count' => $this->stats['external_id_duplicate_count'],
            'unmatched_count' => count($unmatched),
            'errors_count' => count($this->stats['errors']),
            'timestamp' => now()->toIso8601String(),
        ];
        $summaryFile = "{$this->artifactsDir}/summary.json";
        $json = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($summaryFile, $json);
        $this->info("Summary written to: {$summaryFile}");
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== Preflight Summary ===');
        $this->line("Total rows processed: {$this->stats['total_rows']}");
        $this->line("<fg=green>Valid rows: {$this->stats['valid_rows']}</>");
        $this->line("<fg=red>Skipped rows: {$this->stats['skipped_rows']}</>");
        $this->line("Date NULL count: {$this->stats['date_null_count']}");
        $this->line("Next hearing NULL count: {$this->stats['next_null_count']}");
        $this->line("External ID NULL count: {$this->stats['external_id_null_count']}");
        $this->line("External ID duplicates: {$this->stats['external_id_duplicate_count']}");
        $this->line("Unmatched values: " . count($this->resolver->getUnmatched()));
        $this->line("Errors: " . count($this->stats['errors']));
    }
}

