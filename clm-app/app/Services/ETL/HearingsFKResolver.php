<?php

namespace App\Services\ETL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Hearings Foreign Key Resolver Service
 * 
 * Deterministic FK resolution with exact → alias → manual → fuzzy matching.
 * Writes unmatched values to review artifacts.
 */
class HearingsFKResolver
{
    private $lookupCases = [];
    private $lookupLawyers = [];
    private $lookupCourts = [];
    private $manualMappings = [];
    private $unmatched = [];
    private $warnings = [];

    /**
     * Load lookup tables from database.
     */
    public function loadLookupTables(): void
    {
        // Load cases (matter_id lookup)
        $cases = DB::table('cases')
            ->select('id', 'matter_name_ar', 'matter_name_en')
            ->get();
        foreach ($cases as $case) {
            $this->lookupCases[$case->id] = [
                'id' => $case->id,
                'name_ar' => $case->matter_name_ar,
                'name_en' => $case->matter_name_en,
            ];
        }

        // Load lawyers (lawyer_id lookup)
        $lawyers = DB::table('lawyers')
            ->whereNull('deleted_at')
            ->select('id', 'lawyer_name_ar', 'lawyer_name_en')
            ->get();
        foreach ($lawyers as $lawyer) {
            $this->lookupLawyers[$lawyer->id] = [
                'id' => $lawyer->id,
                'name_ar' => $lawyer->lawyer_name_ar,
                'name_en' => $lawyer->lawyer_name_en,
            ];
        }

        // Load courts (court name lookup)
        $courts = DB::table('courts')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->select('id', 'court_name_ar', 'court_name_en')
            ->get();
        foreach ($courts as $court) {
            $this->lookupCourts[$court->id] = [
                'id' => $court->id,
                'name_ar' => $court->court_name_ar,
                'name_en' => $court->court_name_en,
            ];
        }
    }

    /**
     * Load manual mapping CSV if provided.
     * 
     * @param string|null $csvPath Path to manual mapping CSV
     */
    public function loadManualMappings(?string $csvPath): void
    {
        if (!$csvPath || !file_exists($csvPath)) {
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return;
        }

        $headers = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            if (isset($data['Legacy_Value']) && isset($data['Canonical_Value'])) {
                $this->manualMappings[$data['Legacy_Value']] = $data['Canonical_Value'];
            }
        }

        fclose($handle);
    }

    /**
     * Resolve matter_id (case ID).
     * 
     * @param mixed $value
     * @param string $sourceFile
     * @param int $sourceRow
     * @return int|null
     */
    public function resolveMatterId($value, string $sourceFile, int $sourceRow): ?int
    {
        $id = is_numeric($value) ? (int)$value : null;
        
        if ($id && isset($this->lookupCases[$id])) {
            return $id;
        }

        // Not found - add to unmatched
        $this->addUnmatched('matter_id', $value, $sourceFile, $sourceRow, 'Case ID not found in database');
        return null;
    }

    /**
     * Resolve lawyer_id from name (fuzzy matching).
     * 
     * Resolution order:
     * 1. Exact match (case/space-insensitive)
     * 2. Manual mapping CSV
     * 3. Fuzzy match (Levenshtein ≥ 0.90) - but don't auto-assign, write to review
     * 
     * @param string|null $name Lawyer name
     * @param string $sourceFile
     * @param int $sourceRow
     * @return int|null
     */
    public function resolveLawyerId(?string $name, string $sourceFile, int $sourceRow): ?int
    {
        if (empty($name)) {
            return null;
        }

        $normalizedName = $this->normalizeName($name);

        // 1. Exact match (case/space-insensitive)
        foreach ($this->lookupLawyers as $lawyer) {
            $arNormalized = $this->normalizeName($lawyer['name_ar']);
            $enNormalized = $this->normalizeName($lawyer['name_en']);

            if ($normalizedName === $arNormalized || $normalizedName === $enNormalized) {
                return $lawyer['id'];
            }
        }

        // 2. Manual mapping CSV
        if (isset($this->manualMappings[$name])) {
            $mappedId = $this->parseIdFromMapping($this->manualMappings[$name]);
            if ($mappedId && isset($this->lookupLawyers[$mappedId])) {
                $this->addWarning($sourceFile, $sourceRow, "Lawyer matched via manual mapping: {$name} → ID {$mappedId}");
                return $mappedId;
            }
        }

        // 3. Fuzzy match (≥ 0.90) - but don't auto-assign, write to review
        $bestMatch = null;
        $bestScore = 0;

        foreach ($this->lookupLawyers as $lawyer) {
            $arNormalized = $this->normalizeName($lawyer['name_ar']);
            $enNormalized = $this->normalizeName($lawyer['name_en']);

            $arScore = $this->similarity($normalizedName, $arNormalized);
            $enScore = $this->similarity($normalizedName, $enNormalized);
            $score = max($arScore, $enScore);

            if ($score > $bestScore && $score >= 0.90) {
                $bestScore = $score;
                $bestMatch = [
                    'id' => $lawyer['id'],
                    'name_ar' => $lawyer['name_ar'],
                    'name_en' => $lawyer['name_en'],
                    'score' => $score,
                ];
            }
        }

        if ($bestMatch) {
            // Found fuzzy match but don't auto-assign - write to review
            $this->addUnmatched(
                'lawyer_id',
                $name,
                $sourceFile,
                $sourceRow,
                "Fuzzy match found (score: {$bestMatch['score']}) but requires review: {$bestMatch['name_ar']} (ID: {$bestMatch['id']})"
            );
            return null; // Don't auto-assign
        }

        // No match found
        $this->addUnmatched('lawyer_id', $name, $sourceFile, $sourceRow, 'No match found');
        return null;
    }

    /**
     * Resolve court name to ID.
     * 
     * @param string|null $name Court name
     * @param string $sourceFile
     * @param int $sourceRow
     * @return int|null
     */
    public function resolveCourtId(?string $name, string $sourceFile, int $sourceRow): ?int
    {
        if (empty($name)) {
            return null;
        }

        $normalizedName = $this->normalizeName($name);

        // Exact match
        foreach ($this->lookupCourts as $court) {
            $arNormalized = $this->normalizeName($court['name_ar']);
            $enNormalized = $this->normalizeName($court['name_en']);

            if ($normalizedName === $arNormalized || $normalizedName === $enNormalized) {
                return $court['id'];
            }
        }

        // Manual mapping
        if (isset($this->manualMappings[$name])) {
            $mappedId = $this->parseIdFromMapping($this->manualMappings[$name]);
            if ($mappedId && isset($this->lookupCourts[$mappedId])) {
                $this->addWarning($sourceFile, $sourceRow, "Court matched via manual mapping: {$name} → ID {$mappedId}");
                return $mappedId;
            }
        }

        // No match - add to unmatched (courts are text fields, so this is just for tracking)
        $this->addUnmatched('court', $name, $sourceFile, $sourceRow, 'Court name not found in database');
        return null;
    }

    /**
     * Normalize name for matching (remove titles, trim, lowercase).
     * 
     * @param string $name
     * @return string
     */
    private function normalizeName(string $name): string
    {
        // Remove common titles
        $titles = ['د.', 'أ.', 'Mr.', 'Ms.', 'Dr.', 'محامي', 'أستاذ', 'أ.'];
        $name = str_ireplace($titles, '', $name);
        $name = trim($name);

        // Normalize whitespace and case
        $name = preg_replace('/\s+/', ' ', $name);
        return mb_strtolower($name, 'UTF-8');
    }

    /**
     * Calculate similarity between two strings (Levenshtein-based).
     * 
     * @param string $str1
     * @param string $str2
     * @return float Similarity score (0.0 to 1.0)
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
     * Parse ID from mapping value (e.g., "Ahmed Said (ID: 5)" → 5).
     * 
     * @param string $mappingValue
     * @return int|null
     */
    private function parseIdFromMapping(string $mappingValue): ?int
    {
        if (preg_match('/\(ID:\s*(\d+)\)/', $mappingValue, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    /**
     * Add unmatched value to review list.
     * 
     * @param string $field
     * @param mixed $value
     * @param string $sourceFile
     * @param int $sourceRow
     * @param string $reason
     */
    private function addUnmatched(string $field, $value, string $sourceFile, int $sourceRow, string $reason): void
    {
        $this->unmatched[] = [
            'field' => $field,
            'value' => $value,
            'source_file' => $sourceFile,
            'source_row' => $sourceRow,
            'reason' => $reason,
        ];
    }

    /**
     * Add transform warning.
     * 
     * @param string $sourceFile
     * @param int $sourceRow
     * @param string $message
     */
    private function addWarning(string $sourceFile, int $sourceRow, string $message): void
    {
        $this->warnings[] = [
            'source_file' => $sourceFile,
            'source_row' => $sourceRow,
            'message' => $message,
        ];
    }

    /**
     * Get unmatched values for export.
     * 
     * @return array
     */
    public function getUnmatched(): array
    {
        return $this->unmatched;
    }

    /**
     * Get transform warnings.
     * 
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get lookup tables for export (IDs → names).
     * 
     * @return array
     */
    public function getLookupTables(): array
    {
        return [
            'cases' => $this->lookupCases,
            'lawyers' => $this->lookupLawyers,
            'courts' => $this->lookupCourts,
        ];
    }
}

