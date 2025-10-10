<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use Exception;

class FileParserService
{
    /**
     * Parse uploaded file and extract data.
     *
     * @param string $filepath
     * @param string $fileType
     * @param int $headerRow
     * @return array{headers: array, rows: array, total_rows: int}
     * @throws Exception
     */
    public function parseFile(string $filepath, string $fileType, int $headerRow = 1): array
    {
        if (!file_exists($filepath)) {
            throw new Exception("File not found: {$filepath}");
        }

        return match ($fileType) {
            'xlsx' => $this->parseXlsx($filepath, $headerRow),
            'xls' => $this->parseXls($filepath, $headerRow),
            'csv' => $this->parseCsv($filepath, $headerRow),
            default => throw new Exception("Unsupported file type: {$fileType}"),
        };
    }

    /**
     * Parse XLSX file.
     */
    protected function parseXlsx(string $filepath, int $headerRow): array
    {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filepath);
            
            return $this->extractSheetData($spreadsheet->getActiveSheet(), $headerRow);
        } catch (Exception $e) {
            throw new Exception("Failed to parse XLSX file: " . $e->getMessage());
        }
    }

    /**
     * Parse XLS file.
     */
    protected function parseXls(string $filepath, int $headerRow): array
    {
        try {
            $reader = IOFactory::createReader('Xls');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filepath);
            
            return $this->extractSheetData($spreadsheet->getActiveSheet(), $headerRow);
        } catch (Exception $e) {
            throw new Exception("Failed to parse XLS file: " . $e->getMessage());
        }
    }

    /**
     * Parse CSV file.
     */
    protected function parseCsv(string $filepath, int $headerRow): array
    {
        try {
            // Detect delimiter and encoding
            $delimiter = $this->detectDelimiter($filepath);
            $encoding = $this->detectEncoding($filepath);

            $reader = new CsvReader();
            $reader->setDelimiter($delimiter);
            $reader->setInputEncoding($encoding);
            $reader->setReadDataOnly(true);
            
            $spreadsheet = $reader->load($filepath);
            
            return $this->extractSheetData($spreadsheet->getActiveSheet(), $headerRow);
        } catch (Exception $e) {
            throw new Exception("Failed to parse CSV file: " . $e->getMessage());
        }
    }

    /**
     * Extract data from spreadsheet.
     */
    protected function extractSheetData($worksheet, int $headerRow): array
    {
        $data = $worksheet->toArray(null, true, true, true);
        
        // Remove empty rows
        $data = array_filter($data, function ($row) {
            return !empty(array_filter($row, fn($cell) => $cell !== null && $cell !== ''));
        });

        if (empty($data)) {
            throw new Exception('File contains no data');
        }

        // Extract headers
        $headers = [];
        if (isset($data[$headerRow])) {
            $headers = array_values(array_filter(
                array_map(fn($header) => (string) $header, $data[$headerRow]),
                fn($header) => $header !== null && $header !== ''
            ));
        }

        if (empty($headers)) {
            throw new Exception('No headers found in specified row');
        }

        // Extract rows (skip header row)
        $rows = [];
        $rowIndex = 0;
        foreach ($data as $rowNum => $row) {
            if ($rowNum <= $headerRow) {
                continue;
            }

            // Convert row to associative array using headers
            $rowData = [];
            $colIndex = 0;
            foreach ($row as $cell) {
                if ($colIndex < count($headers)) {
                    $rowData[$headers[$colIndex]] = $cell;
                    $colIndex++;
                }
            }

            if (!empty(array_filter($rowData, fn($val) => $val !== null && $val !== ''))) {
                $rowData['__row_number__'] = $rowNum;
                $rows[] = $rowData;
                $rowIndex++;
            }
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'total_rows' => count($rows),
        ];
    }

    /**
     * Detect CSV delimiter.
     */
    protected function detectDelimiter(string $filepath): string
    {
        $delimiters = [',', ';', "\t", '|'];
        $handle = fopen($filepath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        arsort($counts);
        return array_key_first($counts);
    }

    /**
     * Detect file encoding.
     */
    protected function detectEncoding(string $filepath): string
    {
        $handle = fopen($filepath, 'r');
        $sample = fread($handle, 8192);
        fclose($handle);

        $encoding = mb_detect_encoding($sample, ['UTF-8', 'ISO-8859-1', 'Windows-1256', 'ASCII'], true);
        
        return $encoding ?: 'UTF-8';
    }

    /**
     * Infer data type from value.
     */
    public function inferType($value): string
    {
        if ($value === null || $value === '') {
            return 'null';
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? 'decimal' : 'integer';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return 'date';
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $value)) {
            return 'date';
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return 'url';
        }

        if (in_array(strtolower($value), ['yes', 'no', 'true', 'false', '1', '0'])) {
            return 'boolean';
        }

        return 'string';
    }

    /**
     * Get column statistics from parsed data.
     */
    public function getColumnStats(array $rows, string $columnName): array
    {
        $values = array_column($rows, $columnName);
        $nonEmpty = array_filter($values, fn($val) => $val !== null && $val !== '');

        $types = array_map(fn($val) => $this->inferType($val), $nonEmpty);
        $typeCounts = array_count_values($types);

        return [
            'total' => count($values),
            'non_empty' => count($nonEmpty),
            'empty' => count($values) - count($nonEmpty),
            'types' => $typeCounts,
            'dominant_type' => count($typeCounts) > 0 ? array_key_first($typeCounts) : 'null',
            'sample_values' => array_slice(array_unique($nonEmpty), 0, 5),
        ];
    }
}

