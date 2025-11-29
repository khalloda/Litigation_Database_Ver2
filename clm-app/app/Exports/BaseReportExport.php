<?php

namespace App\Exports;

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

        // Create sheets and populate data
        foreach ($sheetNames as $index => $sheetName) {
            if ($index === 0 && count($sheetNames) === 1) {
                $sheet = $this->spreadsheet->getActiveSheet();
            } else {
                $sheet = new Worksheet($this->spreadsheet, $sheetName);
                $this->spreadsheet->addSheet($sheet);
            }

            $sheet->setTitle($sheetName);
            
            // For single-sheet exports, data is array of rows directly
            // For multi-sheet exports, data is keyed by sheet name
            $sheetData = count($sheetNames) === 1 ? $data : ($data[$sheetName] ?? []);
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
     */
    protected function translate(string $key): string
    {
        // Try to get translation from language files
        $translation = __($key, [], $this->locale);
        
        // If translation not found, return the key
        return $translation !== $key ? $translation : $key;
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

