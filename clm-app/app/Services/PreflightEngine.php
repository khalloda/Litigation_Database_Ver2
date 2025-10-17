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
        $batchSize = config('importer.validation.preflight_batch_size', 500);

        $batches = array_chunk($rows, $batchSize, true);

        foreach ($batches as $batch) {
            foreach ($batch as $rowIndex => $row) {
                $rowErrors = $this->validateRow($row, $mapping, $tableName, $rowIndex);

                if (!empty($rowErrors['errors'])) {
                    $errors = array_merge($errors, $rowErrors['errors']);
                }

                if (!empty($rowErrors['warnings'])) {
                    $warnings = array_merge($warnings, $rowErrors['warnings']);
                }
            }
        }

        return [
            'errors' => array_slice($errors, 0, 1000), // Limit to prevent memory issues
            'warnings' => array_slice($warnings, 0, 1000),
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
                return [
                    'row' => $rowIndex,
                    'column' => $column,
                    'value' => $value,
                    'type' => 'type_mismatch',
                    'message' => "Expected integer, got '{$value}'",
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
}

