<?php

namespace App\Exports;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseReportExport
{
    protected Spreadsheet $spreadsheet;
    protected string $locale = 'en';

    public function __construct(string $locale = 'en')
    {
        $this->locale = $locale;
        $this->spreadsheet = new Spreadsheet();
    }

    /**
     * Get the report data to export.
     * For multi-sheet exports, return array keyed by sheet name.
     * For single-sheet exports, return array of rows.
     */
    abstract protected function getData(): array;

    /**
     * Get the sheet names for multi-sheet exports.
     */
    protected function getSheetNames(): array
    {
        return ['Sheet1'];
    }

    /**
     * Get headers for the export.
     */
    abstract protected function getHeaders(): array;

    /**
     * Get column widths (optional, can override).
     */
    protected function getColumnWidths(): array
    {
        return [];
    }

    /**
     * Get the report title.
     */
    abstract protected function getTitle(): string;

    /**
     * Export to Excel and return as download response.
     */
    public function export(): StreamedResponse
    {
        $this->buildSpreadsheet();

        $filename = $this->getFilename();

        return new StreamedResponse(function () {
            $writer = new Xlsx($this->spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Build the complete spreadsheet with all sheets.
     */
    protected function buildSpreadsheet(): void
    {
        $sheetNames = $this->getSheetNames();
        $data = $this->getData();

        // Remove default sheet if we're creating multiple sheets
        if (count($sheetNames) > 1) {
            $this->spreadsheet->removeSheetByIndex(0);
        }

        // Track used sheet names to ensure uniqueness
        $usedNames = [];
        
        // Create sheets and populate data
        foreach ($sheetNames as $index => $sheetKey) {
            // Get display name for sheet title (translated)
            $displayName = method_exists($this, 'getSheetDisplayName') 
                ? $this->getSheetDisplayName($sheetKey)
                : $sheetKey;
            
            // Sanitize sheet name for Excel (max 31 chars, no invalid chars)
            $excelSafeName = $this->sanitizeSheetName($displayName, $usedNames);
            $usedNames[] = $excelSafeName;
            
            if ($index === 0 && count($sheetNames) === 1) {
                $sheet = $this->spreadsheet->getActiveSheet();
            } else {
                $sheet = new Worksheet($this->spreadsheet, $excelSafeName);
                $this->spreadsheet->addSheet($sheet);
            }

            $sheet->setTitle($excelSafeName);
            
            // For single-sheet exports, data is array of rows directly
            // For multi-sheet exports, data is keyed by sheet key (internal key, not translated)
            $sheetData = count($sheetNames) === 1 ? $data : ($data[$sheetKey] ?? []);
            $this->populateSheet($sheet, $sheetData);
        }

        // Set first sheet as active
        $this->spreadsheet->setActiveSheetIndex(0);
    }

    /**
     * Populate a single sheet with data.
     */
    protected function populateSheet(Worksheet $sheet, array $data): void
    {
        $headers = $this->getHeaders();
        $row = 1;

        // Add title (if applicable)
        if (!empty($this->getTitle())) {
            $sheet->setCellValue('A1', $this->getTitle());
            $sheet->mergeCells('A1:' . $this->getColumnLetter(count($headers)) . '1');
            $this->applyTitleStyle($sheet, 'A1');
            $row = 2;
        }

        // Add headers
        $headerRow = $row;
        $col = 1;
        foreach ($headers as $header) {
            $cell = $this->getColumnLetter($col) . $headerRow;
            $sheet->setCellValue($cell, $this->translate($header));
            $this->applyHeaderStyle($sheet, $cell);
            $col++;
        }
        $row++;

        // Add data rows
        foreach ($data as $dataRow) {
            $col = 1;
            foreach ($headers as $key => $header) {
                $cell = $this->getColumnLetter($col) . $row;
                $value = $dataRow[$key] ?? $dataRow[$header] ?? '';
                $sheet->setCellValue($cell, $this->formatCellValue($value, $key));
                $this->applyCellStyle($sheet, $cell, $value, $key);
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        $columnWidths = $this->getColumnWidths();
        foreach ($headers as $index => $header) {
            $col = $index + 1;
            $columnLetter = $this->getColumnLetter($col);
            
            if (isset($columnWidths[$header])) {
                $sheet->getColumnDimension($columnLetter)->setWidth($columnWidths[$header]);
            } else {
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        }

        // Freeze header row
        $sheet->freezePane('A' . ($headerRow + 1));
    }

    /**
     * Apply title style to the first row.
     */
    protected function applyTitleStyle(Worksheet $sheet, string $cell): void
    {
        $sheet->getStyle($cell)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8E8E8'],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);
    }

    /**
     * Apply header style to header cells.
     */
    protected function applyHeaderStyle(Worksheet $sheet, string $cell): void
    {
        $sheet->getStyle($cell)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => $this->isRTL() ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'], // Blue header
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    /**
     * Apply cell style to data cells.
     */
    protected function applyCellStyle(Worksheet $sheet, string $cell, $value, string $key): void
    {
        $isRTL = $this->isRTL();
        $alignment = $isRTL ? Alignment::HORIZONTAL_RIGHT : Alignment::HORIZONTAL_LEFT;

        // Right-align numbers and dates
        if (is_numeric($value) || $value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
            $alignment = Alignment::HORIZONTAL_RIGHT;
        }

        $sheet->getStyle($cell)->applyFromArray([
            'alignment' => [
                'horizontal' => $alignment,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);
    }

    /**
     * Format cell value based on type.
     */
    protected function formatCellValue($value, string $key)
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Handle Carbon dates
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        // Handle DateTime objects
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    /**
     * Get column letter from column number (1 = A, 27 = AA, etc.).
     */
    protected function getColumnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }

    /**
     * Check if current locale is RTL (Arabic).
     */
    protected function isRTL(): bool
    {
        return $this->locale === 'ar';
    }

    /**
     * Translate a key to the current locale.
     * 
     * Translations are stored in resources/lang/{locale}/app.php.
     * Keys like 'reports.document_inventory.title' should be accessed
     * as 'app.reports.document_inventory.title' when using Lang::get().
     */
    protected function translate(string $key): string
    {
        // Store current locale
        $originalLocale = App::getLocale();
        
        try {
            // Set the locale temporarily to load translations
            App::setLocale($this->locale);
            
            // Access translations from app.php with explicit 'app.' prefix
            $fullKey = 'app.' . $key;
            $translation = Lang::get($fullKey, [], $this->locale);
            
            // If translation not found (returns the key), try without prefix as fallback
            if ($translation === $fullKey) {
                $translation = Lang::get($key, [], $this->locale);
                // If still not found, return original key
                if ($translation === $key) {
                    App::setLocale($originalLocale);
                    return $key;
                }
            }
            
            // Restore original locale
            App::setLocale($originalLocale);
            
            return $translation;
        } catch (\Exception $e) {
            // Restore original locale on error
            App::setLocale($originalLocale);
            // Return key as fallback
            return $key;
        }
    }

    /**
     * Get the filename for the export.
     */
    protected function getFilename(): string
    {
        $title = $this->getTitle();
        $slug = \Illuminate\Support\Str::slug($title);
        $timestamp = now()->format('Ymd_His');
        
        return "{$slug}-{$timestamp}.xlsx";
    }

    /**
     * Sanitize sheet name for Excel compatibility.
     * Excel sheet names must be <= 31 characters and cannot contain: / \ ? * [ ]
     * 
     * @param string $name The sheet name to sanitize
     * @param array $usedNames Array of already used sheet names to ensure uniqueness
     * @return string Sanitized and unique sheet name
     */
    protected function sanitizeSheetName(string $name, array $usedNames = []): string
    {
        // Remove invalid characters
        $name = str_replace(['/', '\\', '?', '*', '[', ']'], '', $name);
        
        // Truncate to 31 characters (Excel limit)
        $baseName = mb_strlen($name) > 31 ? mb_substr($name, 0, 31) : $name;
        
        // Ensure not empty
        if (empty($baseName)) {
            $baseName = 'Sheet';
        }
        
        // Ensure uniqueness by appending number if needed
        $finalName = $baseName;
        $counter = 1;
        while (in_array($finalName, $usedNames, true)) {
            // Truncate base name to leave room for counter (e.g., " (1)")
            $maxBaseLength = 31 - strlen(" ({$counter})");
            $truncatedBase = mb_strlen($baseName) > $maxBaseLength 
                ? mb_substr($baseName, 0, $maxBaseLength) 
                : $baseName;
            $finalName = $truncatedBase . " ({$counter})";
            $counter++;
            
            // Safety check to prevent infinite loop
            if ($counter > 100) {
                $finalName = 'Sheet' . $counter;
                break;
            }
        }
        
        return $finalName;
    }

    /**
     * Clean up resources.
     */
    public function __destruct()
    {
        if (isset($this->spreadsheet)) {
            $this->spreadsheet->disconnectWorksheets();
            unset($this->spreadsheet);
        }
    }
}

