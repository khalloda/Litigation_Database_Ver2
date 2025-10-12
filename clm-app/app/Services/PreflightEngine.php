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
}

