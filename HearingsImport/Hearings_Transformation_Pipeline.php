<?php

/**
 * Hearings Import Transformation Pipeline
 * 
 * Transforms legacy Hearings-Original.csv to Hearings_Import_Template.csv format
 * with referential integrity validation against litigation_db_ver2 database.
 * 
 * Note: The 'id' column is preserved from original 'hearings_id' (not auto-generated).
 * 
 * Usage:
 *   php Hearings_Transformation_Pipeline.php [--sample=50] [--output=output.csv]
 * 
 * Requirements:
 *   - Laravel application bootstrapped (for DB access)
 *   - CSV files in HearingsImport/ directory
 */

require __DIR__ . '/../clm-app/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../clm-app/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class HearingsTransformationPipeline
{
    private $lookupCases = [];
    private $lookupLawyers = [];
    private $lookupCourts = [];
    private $errors = [];
    private $stats = [
        'total_rows' => 0,
        'valid_rows' => 0,
        'skipped_rows' => 0,
        'orphaned_matter_ids' => 0,
        'duplicate_ids' => 0,
        'lawyer_matches' => 0,
        'lawyer_ambiguous' => 0,
        'lawyer_unmatched' => 0,
    ];
    private $seenIds = []; // Track duplicate IDs

    public function __construct()
    {
        $this->loadLookupTables();
    }

    /**
     * Load lookup tables from database
     */
    private function loadLookupTables(): void
    {
        echo "Loading lookup tables from database...\n";

        // Load cases
        $cases = DB::table('cases')->select('id', 'matter_name_ar', 'matter_name_en')->get();
        foreach ($cases as $case) {
            $this->lookupCases[$case->id] = [
                'name_ar' => $case->matter_name_ar,
                'name_en' => $case->matter_name_en,
            ];
        }
        echo "Loaded " . count($this->lookupCases) . " cases\n";

        // Load lawyers
        $lawyers = DB::table('lawyers')
            ->whereNull('deleted_at')
            ->select('id', 'lawyer_name_ar', 'lawyer_name_en')
            ->get();
        foreach ($lawyers as $lawyer) {
            $this->lookupLawyers[$lawyer->id] = [
                'name_ar' => $lawyer->lawyer_name_ar,
                'name_en' => $lawyer->lawyer_name_en,
            ];
        }
        echo "Loaded " . count($this->lookupLawyers) . " lawyers\n";

        // Load courts
        $courts = DB::table('courts')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->select('id', 'court_name_ar', 'court_name_en')
            ->get();
        foreach ($courts as $court) {
            $this->lookupCourts[$court->id] = [
                'name_ar' => $court->court_name_ar,
                'name_en' => $court->court_name_en,
            ];
        }
        echo "Loaded " . count($this->lookupCourts) . " courts\n";
    }

    /**
     * Parse circuit compound field
     */
    private function parseCircuit(?string $circuitValue, ?string $nameCol = null, ?string $notesCol = null, ?string $serialCol = null): array
    {
        // Priority 1: Use split columns if available
        if ($nameCol && trim($nameCol)) {
            return [
                'circuit' => $this->cleanString($nameCol),
                'circuit_notes' => $this->cleanString($notesCol),
                'circuit_serial' => $this->parseInt($serialCol),
            ];
        }

        // Priority 2: Parse compound field
        if (empty($circuitValue)) {
            return ['circuit' => null, 'circuit_notes' => null, 'circuit_serial' => null];
        }

        // Pattern: "2 - رول (36) شق مستعجل"
        if (preg_match('/^(\d+)\s*-\s*(.+?)\s+([^\d]+)$/u', $circuitValue, $matches)) {
            return [
                'circuit' => $this->cleanString($matches[3]),
                'circuit_notes' => $this->cleanString($matches[2]),
                'circuit_serial' => (int)$matches[1],
            ];
        }

        // Pattern: "40 عمال" or "5 اقتصادي"
        if (preg_match('/^(\d+)\s*(.*)$/u', $circuitValue, $matches)) {
            return [
                'circuit' => $this->cleanString($matches[2] ?: $circuitValue),
                'circuit_notes' => '',
                'circuit_serial' => (int)$matches[1],
            ];
        }

        // Fallback: return as-is
        return [
            'circuit' => $this->cleanString($circuitValue),
            'circuit_notes' => '',
            'circuit_serial' => null,
        ];
    }

    /**
     * Parse date from various formats to YYYY-MM-DD
     */
    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // Try DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Try YYYY/MM/DD
        if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        // Try YYYY-MM-DD (already normalized)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return null;
    }

    /**
     * Clean and normalize text string
     */
    private function cleanString(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Remove Excel artifacts
        $value = str_replace('_x000D_', '', $value);

        // Remove zero-width and direction marks
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{200E}\x{200F}]/u', '', $value);

        // Normalize Arabic digits to English
        $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $value = str_replace($arabicDigits, $englishDigits, $value);

        // Collapse whitespace
        $value = preg_replace('/\s+/', ' ', $value);

        // Trim
        $value = trim($value);

        return $value ?: null;
    }

    /**
     * Parse boolean value
     */
    private function parseBoolean($value): bool
    {
        if (empty($value)) {
            return false;
        }

        $normalized = strtolower(trim((string)$value));
        $truthy = ['true', '1', 'yes', 'y'];

        return in_array($normalized, $truthy);
    }

    /**
     * Parse integer value
     */
    private function parseInt($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        $value = trim((string)$value);
        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int !== false ? $int : null;
    }

    /**
     * Fuzzy match lawyer name against lookup table
     */
    private function findLawyerId(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

        $name = $this->cleanString($name);
        $normalizedName = $this->normalizeLawyerName($name);

        $bestMatch = null;
        $bestScore = 0;

        foreach ($this->lookupLawyers as $id => $lawyer) {
            $arNormalized = $this->normalizeLawyerName($lawyer['name_ar']);
            $enNormalized = $this->normalizeLawyerName($lawyer['name_en']);

            $arScore = $this->similarity($normalizedName, $arNormalized);
            $enScore = $this->similarity($normalizedName, $enNormalized);

            $score = max($arScore, $enScore);

            if ($score > $bestScore && $score >= 0.85) {
                $bestScore = $score;
                $bestMatch = $id;
            }
        }

        if ($bestMatch) {
            $this->stats['lawyer_matches']++;
            return $bestMatch;
        }

        $this->stats['lawyer_unmatched']++;
        return null;
    }

    /**
     * Normalize lawyer name for matching (remove titles, trim)
     */
    private function normalizeLawyerName(string $name): string
    {
        // Remove common titles
        $titles = ['د.', 'أ.', 'Mr.', 'Ms.', 'Dr.', 'محامي', 'أستاذ', 'أ.'];
        $name = str_ireplace($titles, '', $name);
        $name = trim($name);

        return mb_strtolower($name, 'UTF-8');
    }

    /**
     * Calculate similarity between two strings (Levenshtein-based)
     */
    private function similarity(string $str1, string $str2): float
    {
        $len1 = mb_strlen($str1, 'UTF-8');
        $len2 = mb_strlen($str2, 'UTF-8');

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $maxLen = max($len1, $len2);
        $distance = levenshtein($str1, $str2);

        return 1.0 - ($distance / $maxLen);
    }

    /**
     * Transform a single row
     */
    private function transformRow(array $row): ?array
    {
        $this->stats['total_rows']++;

        // Parse hearings_id (preserve from original, not auto-generated)
        $hearingId = $this->parseInt($row['hearings_id'] ?? null);
        
        // Check for duplicate IDs
        if ($hearingId && isset($this->seenIds[$hearingId])) {
            $this->errors[] = [
                'row' => $this->stats['total_rows'],
                'field' => 'hearings_id',
                'value' => $hearingId,
                'error' => 'Duplicate ID (already seen in row ' . $this->seenIds[$hearingId] . ')',
            ];
            $this->stats['duplicate_ids']++;
            $this->stats['skipped_rows']++;
            return null;
        }
        
        if ($hearingId) {
            $this->seenIds[$hearingId] = $this->stats['total_rows'];
        }

        // Validate matter_id
        $matterId = $this->parseInt($row['matter_id'] ?? null);
        if (!$matterId || !isset($this->lookupCases[$matterId])) {
            $this->errors[] = [
                'row' => $this->stats['total_rows'],
                'field' => 'matter_id',
                'value' => $row['matter_id'] ?? null,
                'error' => 'Orphaned matter_id or invalid',
            ];
            $this->stats['orphaned_matter_ids']++;
            $this->stats['skipped_rows']++;
            return null;
        }

        // Parse circuit
        $circuitData = $this->parseCircuit(
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
                $lawyerId = $this->findLawyerId($attendee);
                if ($lawyerId) {
                    break;
                }
            }
        }

        // Build notes (merge original-only columns)
        $notes = [];
        if (!empty($row['ملاحظات'] ?? null)) {
            $notes[] = $this->cleanString($row['ملاحظات']);
        }
        if (!empty($row['تاريخ تبليغ القرار'] ?? null)) {
            $notes[] = 'تاريخ تبليغ القرار: ' . $this->cleanString($row['تاريخ تبليغ القرار']);
        }
        if (!empty($circuitData['circuit_notes'])) {
            $notes[] = 'ملاحظات الدائرة: ' . $circuitData['circuit_notes'];
        }
        $notesText = implode(' | ', $notes);

        // Build transformed row
        $transformed = [
            'id' => $hearingId ?: '', // Preserve original hearings_id (not auto-generated)
            'matter_id' => $matterId,
            'lawyer_id' => $lawyerId ?: '',
            'date' => $this->parseDate($row['date'] ?? null) ?: '',
            'procedure' => $this->cleanString($row['الإجراء'] ?? null) ?: '',
            'court' => $this->cleanString($row['المحكمة'] ?? null) ?: '',
            'circuit' => $circuitData['circuit'] ?: '',
            'destination' => $this->cleanString($row['الجهة'] ?? null) ?: '',
            'decision' => $this->cleanString($row['decision'] ?? null) ?: '',
            'short_decision' => $this->cleanString($row['shortDecision'] ?? null) ?: '',
            'last_decision' => $this->cleanString($row['lastDecision'] ?? null) ?: '',
            'next_hearing' => $this->parseDate($row['nextHearing'] ?? null) ?: '',
            'report' => $this->parseBoolean($row['report'] ?? false) ? 'TRUE' : 'FALSE',
            'notify_client' => $this->parseBoolean($row['إخطار العميل بالقرار'] ?? false) ? 'TRUE' : 'FALSE',
            'attendee' => $this->cleanString($row['ملاحظات الحاضر'] ?? null) ?: '',
            'attendee_1' => $this->cleanString($row['حاضر 1'] ?? null) ?: '',
            'attendee_2' => $this->cleanString($row['حاضر 2'] ?? null) ?: '',
            'attendee_3' => $this->cleanString($row['حاضر 3'] ?? null) ?: '',
            'attendee_4' => $this->cleanString($row['حاضر 4'] ?? null) ?: '',
            'next_attendee' => $this->cleanString($row['حضور الجلسة القادمة'] ?? null) ?: '',
            'evaluation' => $this->cleanString($row['صالح/ضد'] ?? null) ?: '',
            'notes' => $notesText ?: '',
        ];

        // Truncate strings to max length
        $transformed['procedure'] = mb_substr($transformed['procedure'], 0, 255);
        $transformed['court'] = mb_substr($transformed['court'], 0, 255);
        $transformed['circuit'] = mb_substr($transformed['circuit'], 0, 255);
        $transformed['destination'] = mb_substr($transformed['destination'], 0, 255);
        $transformed['short_decision'] = mb_substr($transformed['short_decision'], 0, 255);
        $transformed['last_decision'] = mb_substr($transformed['last_decision'], 0, 255);
        foreach (['attendee', 'attendee_1', 'attendee_2', 'attendee_3', 'attendee_4', 'next_attendee', 'evaluation'] as $field) {
            $transformed[$field] = mb_substr($transformed[$field], 0, 255);
        }

        $this->stats['valid_rows']++;
        return $transformed;
    }

    /**
     * Process CSV file
     */
    public function process(string $inputFile, string $outputFile, ?int $sampleSize = null): void
    {
        $inputHandle = fopen($inputFile, 'r');
        if (!$inputHandle) {
            throw new \RuntimeException("Cannot open input file: {$inputFile}");
        }

        $outputHandle = fopen($outputFile, 'w');
        if (!$outputHandle) {
            throw new \RuntimeException("Cannot open output file: {$outputFile}");
        }

        // Read headers
        $headers = fgetcsv($inputHandle);
        if (!$headers) {
            throw new \RuntimeException("Cannot read headers from input file");
        }

        // Write output headers (template format)
        $outputHeaders = [
            'id', 'matter_id', 'lawyer_id', 'date', 'procedure', 'court', 'circuit', 'destination',
            'decision', 'short_decision', 'last_decision', 'next_hearing', 'report', 'notify_client',
            'attendee', 'attendee_1', 'attendee_2', 'attendee_3', 'attendee_4', 'next_attendee',
            'evaluation', 'notes',
        ];
        fputcsv($outputHandle, $outputHeaders);

        // Process rows
        $rowCount = 0;
        while (($row = fgetcsv($inputHandle)) !== false) {
            if ($sampleSize && $rowCount >= $sampleSize) {
                break;
            }

            $rowData = array_combine($headers, $row);
            $transformed = $this->transformRow($rowData);

            if ($transformed) {
                fputcsv($outputHandle, array_values($transformed));
            }

            $rowCount++;
        }

        fclose($inputHandle);
        fclose($outputHandle);

        // Write error log
        $errorLogFile = str_replace('.csv', '_errors.json', $outputFile);
        file_put_contents($errorLogFile, json_encode($this->errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Print statistics
        echo "\n=== Transformation Statistics ===\n";
        echo "Total rows processed: {$this->stats['total_rows']}\n";
        echo "Valid rows: {$this->stats['valid_rows']}\n";
        echo "Skipped rows: {$this->stats['skipped_rows']}\n";
        echo "Duplicate IDs: {$this->stats['duplicate_ids']}\n";
        echo "Orphaned matter_ids: {$this->stats['orphaned_matter_ids']}\n";
        echo "Lawyer matches: {$this->stats['lawyer_matches']}\n";
        echo "Lawyer unmatched: {$this->stats['lawyer_unmatched']}\n";
        echo "\nErrors logged to: {$errorLogFile}\n";
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['sample:', 'output:']);
    $sampleSize = isset($options['sample']) ? (int)$options['sample'] : null;
    $outputFile = $options['output'] ?? 'Hearings_Transformed_Sample.csv';

    $inputFile = __DIR__ . '/Hearings-Original.csv';

    try {
        $pipeline = new HearingsTransformationPipeline();
        $pipeline->process($inputFile, __DIR__ . '/' . $outputFile, $sampleSize);
        echo "\n✓ Transformation complete!\n";
    } catch (\Exception $e) {
        echo "\n✗ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

