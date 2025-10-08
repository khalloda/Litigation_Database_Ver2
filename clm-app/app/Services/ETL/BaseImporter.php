<?php

namespace App\Services\ETL;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

abstract class BaseImporter
{
    protected $successCount = 0;
    protected $failedCount = 0;
    protected $rejectedRows = [];
    protected $stats = [];

    /**
     * Get the Excel file path.
     */
    abstract protected function getFilePath(): string;

    /**
     * Get the sheet name to import.
     */
    abstract protected function getSheetName(): string;

    /**
     * Process a single row.
     */
    abstract protected function processRow(array $row): void;

    /**
     * Get column mapping (Excel column name => database field name).
     */
    abstract protected function getColumnMapping(): array;

    /**
     * Run the import process.
     */
    public function import(): array
    {
        // Increase memory limit for large files
        ini_set('memory_limit', '512M');
        
        $this->successCount = 0;
        $this->failedCount = 0;
        $this->rejectedRows = [];

        $filePath = storage_path('app/imports/' . $this->getFilePath());

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Excel file not found: {$filePath}");
        }

        Log::info("Starting import", ['file' => $this->getFilePath()]);

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getSheetByName($this->getSheetName());

        if (!$worksheet) {
            throw new \RuntimeException("Sheet not found: {$this->getSheetName()}");
        }

        $rows = $worksheet->toArray(null, true, true, true);
        $headers = array_shift($rows); // First row is headers

        $totalRows = count($rows);
        $processed = 0;

        foreach ($rows as $rowNumber => $row) {
            $processed++;
            
            try {
                $mappedRow = $this->mapRow($row, $headers);
                $this->processRow($mappedRow);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->failedCount++;
                $this->rejectedRows[] = [
                    'row' => $rowNumber,
                    'data' => $row,
                    'error' => $e->getMessage(),
                ];
                
                Log::warning("Row import failed", [
                    'file' => $this->getFilePath(),
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ]);
            }

            // Progress logging every 100 rows
            if ($processed % 100 === 0) {
                Log::info("Import progress", [
                    'file' => $this->getFilePath(),
                    'processed' => $processed,
                    'total' => $totalRows,
                ]);
            }
        }

        $this->stats = [
            'file' => $this->getFilePath(),
            'total' => $totalRows,
            'success' => $this->successCount,
            'failed' => $this->failedCount,
            'success_rate' => $totalRows > 0 ? round(($this->successCount / $totalRows) * 100, 2) : 0,
        ];

        $this->writeRejectLog();

        Log::info("Import completed", $this->stats);

        return $this->stats;
    }

    /**
     * Map Excel row to database fields.
     */
    protected function mapRow(array $row, array $headers): array
    {
        $mapping = $this->getColumnMapping();
        $mapped = [];

        foreach ($headers as $colIndex => $headerName) {
            $dbField = $mapping[$headerName] ?? null;

            if ($dbField) {
                $mapped[$dbField] = $row[$colIndex] ?? null;
            }
        }

        return $mapped;
    }

    /**
     * Parse date from Excel (handles multiple formats).
     */
    protected function parseDate($value, bool $required = false): ?string
    {
        if (empty($value) || $value === '' || $value === null) {
            return $required ? null : null;
        }

        // If it's a numeric Excel date
        if (is_numeric($value)) {
            try {
                $date = ExcelDate::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Excel date conversion failed", ['value' => $value]);
                return null;
            }
        }

        // If it's a string date
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("String date parsing failed", ['value' => $value]);
            return null;
        }
    }

    /**
     * Parse datetime from Excel.
     */
    protected function parseDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $date = ExcelDate::excelToDateTimeObject($value);
                return $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse boolean from Excel.
     */
    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['true', '1', 'yes', 'on']);
        }

        return (bool) $value;
    }

    /**
     * Clean string value.
     */
    protected function cleanString($value): ?string
    {
        if (empty($value) || $value === '') {
            return null;
        }

        // Remove special Excel characters
        $cleaned = str_replace(['_x000D_', '_x000d_'], "\n", (string) $value);
        $cleaned = trim($cleaned);

        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * Parse decimal/float value.
     */
    protected function parseDecimal($value): ?float
    {
        if (empty($value) || $value === '') {
            return null;
        }

        return (float) $value;
    }

    /**
     * Parse integer value.
     */
    protected function parseInt($value): ?int
    {
        if (empty($value) || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Write reject log to storage.
     */
    protected function writeRejectLog(): void
    {
        if (empty($this->rejectedRows)) {
            return;
        }

        $logPath = storage_path('app/imports/rejects/' . pathinfo($this->getFilePath(), PATHINFO_FILENAME) . '_rejects_' . date('Ymd_His') . '.json');
        
        $dir = dirname($logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($logPath, json_encode($this->rejectedRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Log::info("Reject log written", ['path' => $logPath, 'count' => count($this->rejectedRows)]);
    }

    /**
     * Get import statistics.
     */
    public function getStats(): array
    {
        return $this->stats;
    }
}

