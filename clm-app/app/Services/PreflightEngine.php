<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreflightEngine
{
    protected MappingEngine $mappingEngine;

    public function __construct(MappingEngine $mappingEngine)
    {
        $this->mappingEngine = $mappingEngine;
    }

    /**
     * Run preflight validation on import data.
     *
     * @param array $rows
     * @param array $mapping Source => Target column mapping
     * @param string $tableName
     * @return array{errors: array, warnings: array, error_count: int, warning_count: int}
     */
    public function runPreflight(array $rows, array $mapping, string $tableName): array
    {
        $errors = [];
        $warnings = [];
        $batchSize = config('importer.validation.preflight_batch_size', 200);
        $maxErrors = config('importer.validation.max_errors', 2000);
        $maxWarnings = config('importer.validation.max_warnings', 1000);

        $batches = array_chunk($rows, $batchSize, true);
        $totalBatches = count($batches);

        \Log::info('Starting preflight validation', [
            'total_rows' => count($rows),
            'batch_size' => $batchSize,
            'total_batches' => $totalBatches
        ]);

        foreach ($batches as $batchIndex => $batch) {
            \Log::info('Processing batch', [
                'batch' => $batchIndex + 1,
                'total_batches' => $totalBatches,
                'batch_size' => count($batch)
            ]);

            foreach ($batch as $rowIndex => $row) {
                $rowErrors = $this->validateRow($row, $mapping, $tableName, $rowIndex);

                if (!empty($rowErrors['errors'])) {
                    $errors = array_merge($errors, $rowErrors['errors']);
                }

                if (!empty($rowErrors['warnings'])) {
                    $warnings = array_merge($warnings, $rowErrors['warnings']);
                }

                // Stop processing if we hit the limit to prevent memory issues
                if (count($errors) >= $maxErrors) {
                    \Log::warning('Error limit reached, stopping validation', [
                        'error_count' => count($errors),
                        'max_errors' => $maxErrors
                    ]);
                    break 2; // Break out of both loops
                }
            }

            // Force garbage collection after each batch
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        \Log::info('Preflight validation completed', [
            'error_count' => count($errors),
            'warning_count' => count($warnings)
        ]);

        return [
            'errors' => array_slice($errors, 0, $maxErrors),
            'warnings' => array_slice($warnings, 0, $maxWarnings),
            'error_count' => count($errors),
            'warning_count' => count($warnings),
        ];
    }

    /**
     * Validate a single row.
     */
    protected function validateRow(array $row, array $mapping, string $tableName, int $rowIndex): array
    {
        $errors = [];
        $warnings = [];
        $mappedData = [];

        // Map source columns to target columns
        foreach ($mapping as $sourceCol => $targetCol) {
            $value = $row[$sourceCol] ?? null;
            $mappedData[$targetCol] = $value;
        }

        // Apply automatic resolution for cases table before validation
        if ($tableName === 'cases') {
            $mappedData = $this->resolveCaseOptionValues($mappedData);
            $mappedData = $this->applyIdNamePrecedence($mappedData);
            $mappedData = $this->resolveDirectMappedFields($mappedData);
            
            // Apply direct ID field resolution for text values
            $this->resolveDirectIdFields($mappedData);

            // Extract multiple opponents for Extended template
            $opponents = $this->extractMultipleOpponents($mappedData);
            if (!empty($opponents)) {
                $mappedData['_opponents'] = $opponents;

                // Validate opponents
                $opponentErrors = $this->validateMultipleOpponents($opponents, $rowIndex);
                $errors = array_merge($errors, $opponentErrors['errors']);
                $warnings = array_merge($warnings, $opponentErrors['warnings']);
            }
        }

        // Get column metadata for validation
        foreach ($mappedData as $column => $value) {
            $metadata = $this->mappingEngine->getColumnMetadata($tableName, $column);

            if (!$metadata) {
                continue;
            }

            // Check nullable constraint
            if (!$metadata['nullable'] && ($value === null || $value === '')) {
                $errors[] = [
                    'row' => $rowIndex,
                    'column' => $column,
                    'value' => $value,
                    'type' => 'not_null',
                    'message' => "Column '{$column}' cannot be null",
                ];
                continue;
            }

            // Check data type compatibility
            if ($value !== null && $value !== '') {
                // Skip type validation for opponent_id when it contains text (will be handled by fuzzy matching)
                if ($column === 'opponent_id' && !is_numeric($value) && $tableName === 'cases') {
                    // This will be processed by fuzzy matching, skip type validation
                    continue;
                }

                $typeError = $this->checkType($value, $metadata['type'], $column, $rowIndex);
                if ($typeError) {
                    $errors[] = $typeError;
                }
            }

            // Check string length for varchar columns
            if (preg_match('/varchar\((\d+)\)/i', $metadata['type'], $matches)) {
                $maxLength = (int) $matches[1];
                if (strlen($value) > $maxLength) {
                    $warnings[] = [
                        'row' => $rowIndex,
                        'column' => $column,
                        'value' => $value,
                        'type' => 'length',
                        'message' => "Value exceeds maximum length of {$maxLength} characters (will be truncated)",
                    ];
                }
            }
        }

        // Check foreign key constraints
        $fkErrors = $this->checkConstraints($mappedData, $tableName, $rowIndex);
        if (!empty($fkErrors)) {
            $errors = array_merge($errors, $fkErrors);
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Check if value matches expected database type.
     */
    protected function checkType($value, string $dbType, string $column, int $rowIndex): ?array
    {
        $dbType = strtolower($dbType);

        // Integer types
        if (preg_match('/^(int|integer|tinyint|smallint|mediumint|bigint)/', $dbType)) {
            if (!is_numeric($value) || floor($value) != $value) {
                // Check if this is a field that should have fuzzy matching
                $suggestions = $this->getFuzzySuggestions($column, $value);
                $message = "Expected integer, got '{$value}'";

                if (!empty($suggestions)) {
                    $message .= " - Suggestions: " . implode(', ', $suggestions);
                }

                return [
                    'row' => $rowIndex,
                    'column' => $column,
                    'value' => $value,
                    'type' => 'type_mismatch',
                    'message' => $message,
                    'suggestions' => $suggestions,
                ];
            }
        }

        // Decimal/Float types
        if (preg_match('/^(decimal|float|double|numeric)/', $dbType)) {
            if (!is_numeric($value)) {
                return [
                    'row' => $rowIndex,
                    'column' => $column,
                    'value' => $value,
                    'type' => 'type_mismatch',
                    'message' => "Expected numeric value, got '{$value}'",
                ];
            }
        }

        // Date/DateTime types
        if (preg_match('/^(date|datetime|timestamp)/', $dbType)) {
            if (!$this->isValidDate($value)) {
                return [
                    'row' => $rowIndex,
                    'column' => $column,
                    'value' => $value,
                    'type' => 'type_mismatch',
                    'message' => "Expected valid date, got '{$value}'",
                ];
            }
        }

        // Email validation for email columns
        if (stripos($column, 'email') !== false) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return [
                    'row' => $rowIndex,
                    'column' => $column,
                    'value' => $value,
                    'type' => 'type_mismatch',
                    'message' => "Invalid email format",
                ];
            }
        }

        return null;
    }

    /**
     * Check foreign key constraints.
     */
    protected function checkConstraints(array $data, string $tableName, int $rowIndex): array
    {
        $errors = [];
        $fkConfig = config('importer.foreign_keys', []);

        // Common FK columns
        $fkColumns = [
            'client_id' => 'clients',
            'lawyer_id' => 'lawyers',
            'case_id' => 'cases',
            'user_id' => 'users',
        ];

        foreach ($data as $column => $value) {
            if (!isset($fkColumns[$column]) || $value === null || $value === '') {
                continue;
            }

            $referencedTable = $fkColumns[$column];

            // Check if referenced record exists
            $exists = DB::table($referencedTable)->where('id', $value)->exists();

            if (!$exists) {
                // Try to resolve by lookup columns if configured
                $resolved = $this->resolveForeignKey($value, $referencedTable, $fkConfig);

                if (!$resolved) {
                    $errors[] = [
                        'row' => $rowIndex,
                        'column' => $column,
                        'value' => $value,
                        'type' => 'foreign_key',
                        'message' => "Referenced record not found in '{$referencedTable}' (ID: {$value})",
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * Try to resolve foreign key by lookup columns.
     */
    protected function resolveForeignKey($value, string $table, array $fkConfig): bool
    {
        if (!isset($fkConfig[$table]['lookup_columns'])) {
            return false;
        }

        $lookupColumns = $fkConfig[$table]['lookup_columns'];

        foreach ($lookupColumns as $column) {
            $exists = DB::table($table)->where($column, $value)->exists();
            if ($exists) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if value is a valid date.
     */
    protected function isValidDate($value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Try common date formats
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'Y-m-d H:i:s',
            'd-m-Y',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return true;
            }
        }

        // Try strtotime as fallback
        return strtotime($value) !== false;
    }

    /**
     * Calculate error rate.
     */
    public function calculateErrorRate(int $errorCount, int $totalRows): float
    {
        if ($totalRows === 0) {
            return 0;
        }

        return round(($errorCount / $totalRows) * 100, 2);
    }

    /**
     * Check if error rate exceeds threshold.
     */
    public function exceedsErrorThreshold(int $errorCount, int $totalRows): bool
    {
        $maxErrorRate = config('importer.validation.max_error_rate', 0.15);
        $errorRate = $this->calculateErrorRate($errorCount, $totalRows) / 100;

        return $errorRate > $maxErrorRate;
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

        // Also resolve if matter_category_id field contains text (direct mapping case)
        if (!empty($data['matter_category_id']) && !is_numeric($data['matter_category_id'])) {
            $categoryId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'case.category');
            })->where(function ($q) use ($data) {
                $q->where('label_en', $data['matter_category_id'])
                    ->orWhere('label_ar', $data['matter_category_id']);
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

        // Handle direct ID field resolution for fields that might contain text instead of IDs
        $this->resolveDirectIdFields($data);

        // Also resolve if opponent_id field contains text (direct mapping case)
        if (!empty($data['opponent_id']) && !is_numeric($data['opponent_id'])) {
            $opponentId = \App\Models\Opponent::where(function ($q) use ($data) {
                $q->where('opponent_name_en', $data['opponent_id'])
                    ->orWhere('opponent_name_ar', $data['opponent_id']);
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
     * Apply ID vs Name precedence logic.
     * Rule: If both ID and Name provided, ID wins and Name is ignored.
     * Logs warnings for conflicts.
     */
    private function applyIdNamePrecedence(array $data): array
    {
        $conflicts = [];

        // Define ID/Name pairs to check
        $idNamePairs = [
            'client_id' => 'client_name',
            'court_id' => 'court_name',
            'opponent_id' => 'opponent_name',
            'matter_partner_id' => 'matter_partner_name',
            'matter_destination_id' => 'matter_destination'
        ];

        foreach ($idNamePairs as $idField => $nameField) {
            $hasId = !empty($data[$idField]) && is_numeric($data[$idField]);
            $hasName = !empty($data[$nameField]);

            if ($hasId && $hasName) {
                // ID wins - clear the name field
                $data[$nameField] = null;
                $conflicts[] = "ID precedence: {$idField} provided, {$nameField} ignored";
            }
        }

        // Log conflicts as warnings (this would need to be passed to the calling method)
        if (!empty($conflicts)) {
            \Log::warning('ID vs Name precedence conflicts detected', [
                'conflicts' => $conflicts,
                'data_keys' => array_keys($data)
            ]);
        }

        return $data;
    }

    /**
     * Extract multiple opponents from Extended template data.
     */
    private function extractMultipleOpponents(array $data): array
    {
        $opponents = [];

        // Standard template: single opponent
        if (!empty($data['opponent_name']) || !empty($data['opponent_id'])) {
            $opponents[] = [
                'name' => $data['opponent_name'] ?? null,
                'id' => $data['opponent_id'] ?? null,
                'capacity' => $data['opponent_capacity'] ?? null,
                'capacity_id' => $data['opponent_capacity_id'] ?? null,
                'is_primary' => true,
                'order' => 1
            ];
        }

        // Extended template: opponent1-5
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($data["opponent{$i}_name"]) || !empty($data["opponent{$i}_id"])) {
                $opponents[] = [
                    'name' => $data["opponent{$i}_name"] ?? null,
                    'id' => $data["opponent{$i}_id"] ?? null,
                    'capacity' => $data["opponent{$i}_capacity"] ?? null,
                    'capacity_id' => $data["opponent{$i}_capacity_id"] ?? null,
                    'is_primary' => ($i === 1),
                    'order' => $i
                ];
            }
        }

        return $opponents;
    }

    /**
     * Validate multiple opponents data.
     */
    private function validateMultipleOpponents(array $opponents, int $rowIndex): array
    {
        $errors = [];
        $warnings = [];

        $maxOpponents = config('importer.opponents.max_per_case', 10);

        // Check max opponents limit
        if (count($opponents) > $maxOpponents) {
            $errors[] = [
                'row' => $rowIndex,
                'column' => '_opponents',
                'value' => count($opponents),
                'type' => 'max_opponents',
                'message' => "Maximum {$maxOpponents} opponents allowed per case. Found: " . count($opponents),
            ];
        }

        // Check for multiple primary opponents
        $primaryCount = array_sum(array_column($opponents, 'is_primary'));
        if ($primaryCount > 1) {
            $errors[] = [
                'row' => $rowIndex,
                'column' => '_opponents',
                'value' => $primaryCount,
                'type' => 'multiple_primary',
                'message' => "Only one opponent can be primary. Found: {$primaryCount}",
            ];
        }

        // Validate each opponent
        foreach ($opponents as $index => $opponent) {
            $opponentPrefix = "opponent" . ($index + 1);

            // Check if both name and ID are provided (conflict)
            if (!empty($opponent['name']) && !empty($opponent['id'])) {
                $warnings[] = [
                    'row' => $rowIndex,
                    'column' => $opponentPrefix,
                    'value' => $opponent['name'],
                    'type' => 'id_name_conflict',
                    'message' => "Both name and ID provided for {$opponentPrefix}. ID will be used.",
                ];
            }

            // Check if neither name nor ID is provided
            if (empty($opponent['name']) && empty($opponent['id'])) {
                $errors[] = [
                    'row' => $rowIndex,
                    'column' => $opponentPrefix,
                    'value' => '',
                    'type' => 'missing_opponent',
                    'message' => "Either name or ID must be provided for {$opponentPrefix}",
                ];
            }

            // Check capacity validation
            if (!empty($opponent['capacity']) && !empty($opponent['capacity_id'])) {
                $warnings[] = [
                    'row' => $rowIndex,
                    'column' => $opponentPrefix . '_capacity',
                    'value' => $opponent['capacity'],
                    'type' => 'id_name_conflict',
                    'message' => "Both capacity name and ID provided for {$opponentPrefix}. ID will be used.",
                ];
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Resolve direct ID fields that might contain text instead of IDs.
     */
    private function resolveDirectIdFields(array &$data): void
    {
        \Log::info('Starting direct ID field resolution', [
            'fields_with_text' => array_filter($data, function($value, $key) {
                return !is_numeric($value) && !empty($value) && 
                       in_array($key, ['court_id', 'client_capacity_id', 'opponent_capacity_id', 'matter_partner_id', 'circuit_secretary', 'circuit_name_id']);
            }, ARRAY_FILTER_USE_BOTH)
        ]);

        // Resolve court_id if it contains text
        if (!empty($data['court_id']) && !is_numeric($data['court_id'])) {
            $courtId = \App\Models\Court::where(function ($q) use ($data) {
                $q->where('court_name_en', 'like', '%' . $data['court_id'] . '%')
                    ->orWhere('court_name_ar', 'like', '%' . $data['court_id'] . '%');
            })->value('id');

            if ($courtId) {
                \Log::info('Court ID resolved', [
                    'original' => $data['court_id'],
                    'resolved_id' => $courtId
                ]);
                $data['court_id'] = $courtId;
            } else {
                \Log::warning('Court ID not found', [
                    'search_value' => $data['court_id']
                ]);
            }
        }

        // Resolve client_capacity_id if it contains text
        if (!empty($data['client_capacity_id']) && !is_numeric($data['client_capacity_id'])) {
            $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'capacity.type');
            })->where(function ($q) use ($data) {
                $q->where('label_en', 'like', '%' . $data['client_capacity_id'] . '%')
                    ->orWhere('label_ar', 'like', '%' . $data['client_capacity_id'] . '%');
            })->value('id');

            if ($capacityId) {
                $data['client_capacity_id'] = $capacityId;
            }
        }

        // Resolve opponent_capacity_id if it contains text
        if (!empty($data['opponent_capacity_id']) && !is_numeric($data['opponent_capacity_id'])) {
            $capacityId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'capacity.type');
            })->where(function ($q) use ($data) {
                $q->where('label_en', 'like', '%' . $data['opponent_capacity_id'] . '%')
                    ->orWhere('label_ar', 'like', '%' . $data['opponent_capacity_id'] . '%');
            })->value('id');

            if ($capacityId) {
                $data['opponent_capacity_id'] = $capacityId;
            }
        }

        // Resolve matter_partner_id if it contains text (lawyer name)
        if (!empty($data['matter_partner_id']) && !is_numeric($data['matter_partner_id'])) {
            $lawyerId = \App\Models\Lawyer::where(function ($q) use ($data) {
                $q->where('lawyer_name_en', 'like', '%' . $data['matter_partner_id'] . '%')
                    ->orWhere('lawyer_name_ar', 'like', '%' . $data['matter_partner_id'] . '%');
            })->value('id');

            if ($lawyerId) {
                $data['matter_partner_id'] = $lawyerId;
            }
        }

        // Resolve circuit_secretary if it contains text (lawyer name)
        if (!empty($data['circuit_secretary']) && !is_numeric($data['circuit_secretary'])) {
            $lawyerId = \App\Models\Lawyer::where(function ($q) use ($data) {
                $q->where('lawyer_name_en', 'like', '%' . $data['circuit_secretary'] . '%')
                    ->orWhere('lawyer_name_ar', 'like', '%' . $data['circuit_secretary'] . '%');
            })->value('id');

            if ($lawyerId) {
                $data['circuit_secretary'] = $lawyerId;
            }
        }

        // Resolve circuit_name_id if it contains text
        if (!empty($data['circuit_name_id']) && !is_numeric($data['circuit_name_id'])) {
            $circuitId = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                $q->where('key', 'circuit.name');
            })->where(function ($q) use ($data) {
                $q->where('label_en', 'like', '%' . $data['circuit_name_id'] . '%')
                    ->orWhere('label_ar', 'like', '%' . $data['circuit_name_id'] . '%');
            })->value('id');

            if ($circuitId) {
                $data['circuit_name_id'] = $circuitId;
            }
        }
    }

    /**
     * Get fuzzy matching suggestions for a given column and value.
     */
    private function getFuzzySuggestions(string $column, string $value): array
    {
        $suggestions = [];

        switch ($column) {
            case 'court_id':
                $suggestions = \App\Models\Court::where(function ($q) use ($value) {
                    $q->where('court_name_en', 'like', '%' . $value . '%')
                        ->orWhere('court_name_ar', 'like', '%' . $value . '%');
                })->limit(5)->pluck('court_name_en', 'id')->toArray();
                break;

            case 'client_capacity_id':
            case 'opponent_capacity_id':
                $suggestions = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                    $q->where('key', 'capacity.type');
                })->where(function ($q) use ($value) {
                    $q->where('label_en', 'like', '%' . $value . '%')
                        ->orWhere('label_ar', 'like', '%' . $value . '%');
                })->limit(5)->pluck('label_en', 'id')->toArray();
                break;

            case 'matter_partner_id':
            case 'circuit_secretary':
                $suggestions = \App\Models\Lawyer::where(function ($q) use ($value) {
                    $q->where('lawyer_name_en', 'like', '%' . $value . '%')
                        ->orWhere('lawyer_name_ar', 'like', '%' . $value . '%');
                })->limit(5)->pluck('lawyer_name_en', 'id')->toArray();
                break;

            case 'circuit_name_id':
                $suggestions = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
                    $q->where('key', 'circuit.name');
                })->where(function ($q) use ($value) {
                    $q->where('label_en', 'like', '%' . $value . '%')
                        ->orWhere('label_ar', 'like', '%' . $value . '%');
                })->limit(5)->pluck('label_en', 'id')->toArray();
                break;
        }

        return $suggestions;
    }
}
