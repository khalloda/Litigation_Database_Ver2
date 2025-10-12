<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MappingEngine
{
    /**
     * Auto-map source columns to database columns using similarity matching.
     *
     * @param array $sourceColumns
     * @param string $tableName
     * @return array{mappings: array, confidence: array, unmapped: array}
     */
    public function autoMapColumns(array $sourceColumns, string $tableName): array
    {
        // Debug: Log the source columns to see what we're working with
        \Log::info('MappingEngine::autoMapColumns - Source columns', [
            'sourceColumns' => $sourceColumns,
            'sourceColumnsTypes' => array_map('gettype', $sourceColumns),
            'tableName' => $tableName
        ]);

        $dbColumns = $this->getDbColumnsForTable($tableName);
        $threshold = config('importer.mapping.similarity_threshold', 0.65);

        $mappings = [];
        $confidence = [];
        $unmapped = [];

        foreach ($sourceColumns as $sourceCol) {
            $bestMatch = null;
            $bestScore = 0;

            // Debug: Log each source column being processed
            \Log::info('Processing source column', [
                'sourceCol' => $sourceCol,
                'sourceColType' => gettype($sourceCol)
            ]);

            foreach ($dbColumns as $dbCol) {
                try {
                    $score = $this->calculateSimilarity($sourceCol, $dbCol);

                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestMatch = $dbCol;
                    }
                } catch (\Exception $e) {
                    \Log::error('Error calculating similarity', [
                        'sourceCol' => $sourceCol,
                        'dbCol' => $dbCol,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            if ($bestScore >= $threshold && $bestMatch) {
                $mappings[$sourceCol] = $bestMatch;
                $confidence[$sourceCol] = round($bestScore * 100, 2);
            } else {
                $unmapped[] = $sourceCol;
            }
        }

        return [
            'mappings' => $mappings,
            'confidence' => $confidence,
            'unmapped' => $unmapped,
        ];
    }

    /**
     * Get database columns for a table.
     */
    public function getDbColumnsForTable(string $tableName): array
    {
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        $columns = Schema::getColumnListing($tableName);

        // Filter out system columns
        $excludedColumns = ['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

        // Include 'id' column for tables that need ID preservation during import
        // All core tables have auto-increment disabled for ID preservation
        $idPreservationTables = [
            'lawyers', 
            'clients', 
            'cases', 
            'hearings', 
            'engagement_letters', 
            'contacts', 
            'power_of_attorneys', 
            'admin_tasks', 
            'admin_subtasks', 
            'client_documents',
            'option_sets',
            'option_values'
        ];
        if (!in_array($tableName, $idPreservationTables)) {
            $excludedColumns[] = 'id';
        }

        return array_diff($columns, $excludedColumns);
    }

    /**
     * Calculate similarity between two column names.
     */
    protected function calculateSimilarity(string $source, string $target): float
    {
        // Ensure both parameters are strings
        $source = is_string($source) ? $source : (string) $source;
        $target = is_string($target) ? $target : (string) $target;

        // Normalize column names
        $source = $this->normalizeColumnName($source);
        $target = $this->normalizeColumnName($target);

        // Exact match
        if ($source === $target) {
            return 1.0;
        }

        // Use simpler similarity calculation to avoid string offset issues
        $levenshteinScore = $this->levenshteinSimilarity($source, $target);

        // Simple substring matching as fallback
        $substringScore = $this->substringSimilarity($source, $target);

        // Weighted combination
        return ($levenshteinScore * 0.7) + ($substringScore * 0.3);
    }

    /**
     * Normalize column name for comparison.
     */
    protected function normalizeColumnName(string $name): string
    {
        // Ensure input is a string
        $name = is_string($name) ? $name : (string) $name;

        // Convert to lowercase
        $name = strtolower($name);

        // Replace common separators with underscore
        $name = preg_replace('/[\s\-\.]+/', '_', $name);

        // Remove special characters
        $name = preg_replace('/[^a-z0-9_]/', '', $name);

        // Remove multiple underscores
        $name = preg_replace('/_+/', '_', $name);

        // Trim underscores
        $name = trim($name, '_');

        return $name;
    }

    /**
     * Calculate Levenshtein similarity (0.0 to 1.0).
     */
    protected function levenshteinSimilarity(string $str1, string $str2): float
    {
        $maxLen = max(strlen($str1), strlen($str2));

        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($str1, $str2);

        return 1.0 - ($distance / $maxLen);
    }

    /**
     * Calculate simple substring similarity (0.0 to 1.0).
     */
    protected function substringSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        if ($str1 === '' || $str2 === '') {
            return 0.0;
        }

        // Check if one string contains the other
        if (strpos($str1, $str2) !== false || strpos($str2, $str1) !== false) {
            return 0.8;
        }

        // Check for common words
        $words1 = explode('_', $str1);
        $words2 = explode('_', $str2);

        $commonWords = array_intersect($words1, $words2);
        $totalWords = count(array_unique(array_merge($words1, $words2)));

        if ($totalWords === 0) {
            return 0.0;
        }

        return count($commonWords) / $totalWords;
    }

    /**
     * Calculate Jaro-Winkler similarity (0.0 to 1.0).
     */
    protected function jaroWinklerSimilarity(string $str1, string $str2): float
    {
        // Ensure both inputs are valid strings and not empty
        $str1 = trim((string) $str1);
        $str2 = trim((string) $str2);

        if ($str1 === '' && $str2 === '') {
            return 1.0;
        }

        if ($str1 === '' || $str2 === '') {
            return 0.0;
        }

        $len1 = strlen($str1);
        $len2 = strlen($str2);

        // Calculate Jaro similarity
        $matchDistance = max($len1, $len2) / 2 - 1;
        $str1Matches = array_fill(0, $len1, false);
        $str2Matches = array_fill(0, $len2, false);

        $matches = 0;
        $transpositions = 0;

        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchDistance);
            $end = min($i + $matchDistance + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($str2Matches[$j] || $str1[$i] !== $str2[$j]) {
                    continue;
                }

                $str1Matches[$i] = true;
                $str2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (!$str1Matches[$i]) {
                continue;
            }

            while (!$str2Matches[$k]) {
                $k++;
            }

            if ($str1[$i] !== $str2[$k]) {
                $transpositions++;
            }

            $k++;
        }

        $jaro = (
            ($matches / $len1) +
            ($matches / $len2) +
            (($matches - $transpositions / 2) / $matches)
        ) / 3;

        // Apply Winkler modification
        $prefix = 0;
        for ($i = 0; $i < min($len1, $len2, 4); $i++) {
            if ($str1[$i] === $str2[$i]) {
                $prefix++;
            } else {
                break;
            }
        }

        return $jaro + ($prefix * 0.1 * (1 - $jaro));
    }

    /**
     * Suggest transforms for a column based on its type.
     */
    public function suggestTransforms(string $columnName, string $columnType): array
    {
        $transforms = [];

        // Always suggest trim
        $transforms[] = 'trim';

        // Type-specific transforms
        switch ($columnType) {
            case 'date':
                $transforms[] = 'date_dmy';
                $transforms[] = 'date_ymd';
                break;

            case 'boolean':
                $transforms[] = 'boolean_yn';
                $transforms[] = 'boolean_10';
                break;

            case 'decimal':
                $transforms[] = 'decimal_comma';
                break;

            case 'string':
                if (Str::contains(strtolower($columnName), ['phone', 'mobile', 'tel'])) {
                    $transforms[] = 'phone_normalize';
                }
                if (Str::contains(strtolower($columnName), ['name', 'title'])) {
                    $transforms[] = 'title_case';
                }
                break;
        }

        return $transforms;
    }

    /**
     * Get column metadata from database.
     */
    public function getColumnMetadata(string $tableName, string $columnName): ?array
    {
        try {
            $column = DB::select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = ?", [$columnName]);

            if (empty($column)) {
                return null;
            }

            $col = $column[0];

            return [
                'name' => $col->Field,
                'type' => $col->Type,
                'nullable' => $col->Null === 'YES',
                'default' => $col->Default,
                'extra' => $col->Extra ?? '',
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate mapping configuration.
     */
    public function validateMapping(array $mapping, string $tableName): array
    {
        $errors = [];
        $dbColumns = $this->getDbColumnsForTable($tableName);

        foreach ($mapping as $sourceCol => $targetCol) {
            // Skip validation for empty target columns (skipped columns)
            if (empty($targetCol)) {
                continue;
            }

            if (!in_array($targetCol, $dbColumns)) {
                $errors[] = "Target column '{$targetCol}' does not exist in table '{$tableName}'";
            }
        }

        return $errors;
    }
}
