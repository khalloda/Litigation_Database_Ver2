<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;

class GenerateHearingsTemplate extends Command
{
    protected $signature = 'templates:generate-hearings';
    protected $description = 'Generate Hearings import templates (CSV + XLSX)';

    private $schemaSignature;
    private $templateVersion = '1.0';

    public function handle()
    {
        $this->info("Generating Hearings import templates...");

        // Ensure templates directory exists
        $this->ensureTemplatesDirectory();

        // Get schema information
        $schemaInfo = $this->getSchemaInfo();
        $this->schemaSignature = $this->calculateSchemaSignature($schemaInfo);

        // Get lookup data
        $lookupData = $this->getLookupData();

        // Get importable columns
        $columns = $this->getImportableColumns($schemaInfo['columns']);

        // Generate CSV
        $this->generateCsv($columns, $lookupData);

        // Generate XLSX
        $this->generateXlsx($columns, $lookupData);

        $this->info('Template generation completed successfully!');
        return 0;
    }

    private function ensureTemplatesDirectory()
    {
        $path = storage_path('app/templates');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
            $this->info("Created templates directory: {$path}");
        }
    }

    private function getSchemaInfo()
    {
        $this->info('Querying database schema...');

        $columns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'hearings'
            ORDER BY ORDINAL_POSITION
        ");

        return [
            'columns' => $columns
        ];
    }

    private function calculateSchemaSignature($schemaInfo)
    {
        $columnData = array_map(function ($col) {
            return $col->COLUMN_NAME . ':' . $col->DATA_TYPE . ':' . $col->IS_NULLABLE;
        }, $schemaInfo['columns']);

        return md5(implode('|', $columnData));
    }

    private function getLookupData()
    {
        $this->info('Querying lookup data...');

        $lookups = [];

        // Get lawyers
        $lookups['lawyers'] = DB::table('lawyers')
            ->select('id', 'lawyer_name_ar', 'lawyer_name_en')
            ->orderBy('lawyer_name_en')
            ->get()
            ->toArray();

        // Get cases for matter_id lookup
        $lookups['cases'] = DB::table('cases')
            ->select('id', 'matter_name_ar', 'matter_name_en')
            ->orderBy('matter_name_ar')
            ->limit(100) // Limit for dropdown
            ->get()
            ->toArray();

        return $lookups;
    }

    private function getImportableColumns($columns)
    {
        // Exclude system fields
        $excludeFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

        return array_filter($columns, function ($col) use ($excludeFields) {
            return !in_array($col->COLUMN_NAME, $excludeFields);
        });
    }

    private function generateCsv($columns, $lookupData)
    {
        $filename = "Hearings_Import_Template.csv";
        $path = storage_path("app/templates/{$filename}");

        $this->info("Generating CSV: {$filename}");

        $handle = fopen($path, 'w');

        // UTF-8 BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        $headers = array_map(function ($col) {
            return $col->COLUMN_NAME;
        }, $columns);
        fputcsv($handle, $headers);

        // Sample rows
        $this->addSampleRows($handle, $columns);

        fclose($handle);

        $this->info("✓ CSV generated: {$filename}");
    }

    private function generateXlsx($columns, $lookupData)
    {
        $filename = "Hearings_Import_Template.xlsx";
        $path = storage_path("app/templates/{$filename}");

        $this->info("Generating XLSX: {$filename}");

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Template
        $this->createTemplateSheet($spreadsheet, $columns);

        // Sheet 2: Lookups
        $this->createLookupsSheet($spreadsheet, $lookupData);

        // Sheet 3: README
        $this->createReadmeSheet($spreadsheet);

        // Apply data validation
        $this->applyDataValidation($spreadsheet, $lookupData, $columns);

        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->info("✓ XLSX generated: {$filename}");
    }

    private function createTemplateSheet($spreadsheet, $columns)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hearings_Import_Template');

        // Headers
        $headers = array_map(function ($col) {
            return $col->COLUMN_NAME;
        }, $columns);

        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1', $header);
            $col++;
        }

        // Style headers
        $headerRange = 'A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E3F2FD');

        // Freeze header row
        $sheet->freezePane('A2');

        // Add sample rows
        $this->addSampleRowsToSheet($sheet, $columns, count($headers));
    }

    private function createLookupsSheet($spreadsheet, $lookupData)
    {
        $lookupSheet = $spreadsheet->createSheet();
        $lookupSheet->setTitle('Lookups');

        $row = 1;
        $col = 1;

        // Lawyers list
        if (!empty($lookupData['lawyers'])) {
            $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, 'lawyers_list');
            $row++;

            foreach ($lookupData['lawyers'] as $lawyer) {
                $displayValue = ($lawyer->lawyer_name_ar ?? $lawyer->lawyer_name_en ?? '') . ' (ID: ' . $lawyer->id . ')';
                $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $displayValue);
                $row++;
            }

            $row += 2; // Space
        }

        // Cases list (limited)
        if (!empty($lookupData['cases'])) {
            $col = 2; // Use column B for cases
            $row = 1;
            $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, 'cases_list');
            $row++;

            foreach ($lookupData['cases'] as $case) {
                $displayValue = ($case->matter_name_ar ?? $case->matter_name_en ?? '') . ' (ID: ' . $case->id . ')';
                $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $displayValue);
                $row++;
            }
        }
    }

    private function createReadmeSheet($spreadsheet)
    {
        $readmeSheet = $spreadsheet->createSheet();
        $readmeSheet->setTitle('README');

        $content = [
            'Hearings Import Template Information',
            '=====================================',
            'Template Version: ' . $this->templateVersion,
            'DB Schema Signature: ' . $this->schemaSignature,
            'Generated At (UTC): ' . now()->utc()->format('Y-m-d H:i:s'),
            '',
            'File Format & Encoding',
            '======================',
            'Encoding: UTF-8 (save CSV as UTF-8 with BOM)',
            'Date Format: YYYY-MM-DD (ISO 8601)',
            'Boolean: 1/0, TRUE/FALSE, or Yes/No',
            'Language: Arabic and/or English allowed in text fields',
            '',
            'Required Fields',
            '===============',
            'matter_id: Case/Matter ID (foreign key to cases table)',
            'date: Hearing date (YYYY-MM-DD)',
            '',
            'Optional Fields',
            '===============',
            'lawyer_id: Lawyer ID (foreign key to lawyers table)',
            'procedure: Procedure type (text)',
            'court: Court name (text)',
            'circuit: Circuit name (text)',
            'destination: Destination/venue (text)',
            'decision: Full decision text (text)',
            'short_decision: Short decision summary (string)',
            'last_decision: Previous decision (string)',
            'next_hearing: Next hearing date (YYYY-MM-DD)',
            'report: Report required (boolean)',
            'notify_client: Notify client of decision (boolean)',
            'attendee: Primary attendee name (string)',
            'attendee_1, attendee_2, attendee_3, attendee_4: Additional attendees',
            'next_attendee: Attendee for next hearing (string)',
            'evaluation: Evaluation (e.g., "صالح", "ضد")',
            'notes: Additional notes (text)',
            '',
            'Foreign Key Resolution',
            '======================',
            'matter_id: Must be a valid case ID',
            'lawyer_id: Can be ID or will be matched by name if provided',
            '',
            'Validation & Preflight',
            '======================',
            'Dates must be valid calendar dates',
            'Booleans accept: TRUE/FALSE, 1/0, Yes/No',
            'Foreign keys are validated against existing records',
            '',
            'Regeneration',
            '============',
            'Command: php artisan templates:generate-hearings',
            'Run after schema changes to update templates'
        ];

        $row = 1;
        foreach ($content as $line) {
            $readmeSheet->setCellValue('A' . $row, $line);
            $row++;
        }

        // Auto-fit columns
        $readmeSheet->getColumnDimension('A')->setAutoSize(true);
    }

    private function applyDataValidation($spreadsheet, $lookupData, $columns)
    {
        $templateSheet = $spreadsheet->getSheetByName('Hearings_Import_Template');
        $lookupSheet = $spreadsheet->getSheetByName('Lookups');

        // Map column names to their positions
        $columnMap = [];
        $col = 1;
        foreach ($columns as $column) {
            $columnMap[$column->COLUMN_NAME] = $col;
            $col++;
        }

        // Apply validation for lawyer_id (if lawyers list is small enough)
        if (isset($columnMap['lawyer_id']) && !empty($lookupData['lawyers']) && count($lookupData['lawyers']) < 100) {
            $colIndex = $columnMap['lawyer_id'];
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $lookupRange = $this->findLookupRange($lookupSheet, 'lawyers');

            if ($lookupRange) {
                $rangeName = 'lawyers_list';
                $spreadsheet->addNamedRange(
                    new NamedRange($rangeName, $lookupSheet, $lookupRange)
                );

                // Apply validation to column (rows 2-1000)
                for ($row = 2; $row <= 1000; $row++) {
                    $cell = $templateSheet->getCell($colLetter . $row);
                    $validation = $cell->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('=' . $rangeName);
                }
            }
        }

        // Apply validation for matter_id (if cases list is small enough)
        if (isset($columnMap['matter_id']) && !empty($lookupData['cases']) && count($lookupData['cases']) < 100) {
            $colIndex = $columnMap['matter_id'];
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $lookupRange = $this->findLookupRange($lookupSheet, 'cases');

            if ($lookupRange) {
                $rangeName = 'cases_list';
                $spreadsheet->addNamedRange(
                    new NamedRange($rangeName, $lookupSheet, $lookupRange)
                );

                // Apply validation to column (rows 2-1000)
                for ($row = 2; $row <= 1000; $row++) {
                    $cell = $templateSheet->getCell($colLetter . $row);
                    $validation = $cell->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false); // matter_id is required
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('=' . $rangeName);
                }
            }
        }
    }

    private function findLookupRange($lookupSheet, $lookupKey)
    {
        $maxRow = $lookupSheet->getHighestRow();
        $maxCol = $lookupSheet->getHighestColumn();

        $col = $lookupKey === 'lawyers' ? 1 : 2; // Lawyers in col A, Cases in col B

        for ($row = 1; $row <= $maxRow; $row++) {
            $cellValue = $lookupSheet->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row)->getValue();
            if ($cellValue === $lookupKey . '_list') {
                $startRow = $row + 1;
                $endRow = $startRow;
                for ($checkRow = $startRow; $checkRow <= $maxRow; $checkRow++) {
                    $nextCell = $lookupSheet->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $checkRow)->getValue();
                    if (empty($nextCell) || strpos($nextCell, '_list') !== false) {
                        $endRow = $checkRow - 1;
                        break;
                    }
                    $endRow = $checkRow;
                }

                if ($endRow >= $startRow) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    return $colLetter . $startRow . ':' . $colLetter . $endRow;
                }
            }
        }

        return null;
    }

    private function addSampleRows($handle, $columns)
    {
        // Row 1 (Arabic-focused)
        $row1 = $this->generateSampleRow($columns, 'arabic');
        fputcsv($handle, $row1);

        // Row 2 (English-focused)
        $row2 = $this->generateSampleRow($columns, 'english');
        fputcsv($handle, $row2);
    }

    private function addSampleRowsToSheet($sheet, $columns, $colCount)
    {
        // Row 2 (Arabic-focused)
        $row2 = $this->generateSampleRow($columns, 'arabic');
        for ($col = 1; $col <= $colCount; $col++) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '2', $row2[$col - 1] ?? '');
        }

        // Row 3 (English-focused)
        $row3 = $this->generateSampleRow($columns, 'english');
        for ($col = 1; $col <= $colCount; $col++) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3', $row3[$col - 1] ?? '');
        }
    }

    private function generateSampleRow($columns, $language)
    {
        $row = [];

        foreach ($columns as $column) {
            $columnName = $column->COLUMN_NAME;
            $value = '';

            switch ($columnName) {
                case 'matter_id':
                    $value = '64';
                    break;
                case 'lawyer_id':
                    $value = '1';
                    break;
                case 'date':
                    $value = $language === 'arabic' ? '2010-04-07' : '2024-01-15';
                    break;
                case 'procedure':
                    $value = $language === 'arabic' ? 'محكمة' : 'Court';
                    break;
                case 'court':
                    $value = $language === 'arabic' ? 'شمال القاهرة' : 'North Cairo';
                    break;
                case 'circuit':
                    $value = $language === 'arabic' ? '40 عمال' : '40 Labor';
                    break;
                case 'destination':
                    $value = $language === 'arabic' ? 'القاهرة' : 'Cairo';
                    break;
                case 'decision':
                    $value = $language === 'arabic' ? 'أول جلسة -قررت المحكمة التأجيل لجلسة 19-5-2010 لسند الكالة عن الشركة والاطلاع.' : 'First hearing - Court decided to postpone until 19-5-2010 for power of attorney and review.';
                    break;
                case 'short_decision':
                    $value = $language === 'arabic' ? 'أول جلسة' : 'First hearing';
                    break;
                case 'last_decision':
                    $value = $language === 'arabic' ? 'أول جلسة' : 'First hearing';
                    break;
                case 'next_hearing':
                    $value = $language === 'arabic' ? '2010-05-19' : '2024-02-01';
                    break;
                case 'report':
                    $value = 'TRUE';
                    break;
                case 'notify_client':
                    $value = 'FALSE';
                    break;
                case 'attendee':
                    $value = $language === 'arabic' ? 'أحمد سعيد' : 'Ahmed Saeed';
                    break;
                case 'attendee_1':
                    $value = $language === 'arabic' ? 'محمد الغرابلي' : 'Mohamed El-Gharably';
                    break;
                case 'attendee_2':
                    $value = $language === 'arabic' ? 'محمود شعبان' : 'Mahmoud Shaaban';
                    break;
                case 'attendee_3':
                    $value = '';
                    break;
                case 'attendee_4':
                    $value = '';
                    break;
                case 'next_attendee':
                    $value = $language === 'arabic' ? 'أ. أحمد إسماعيل' : 'Mr. Ahmed Ismail';
                    break;
                case 'evaluation':
                    $value = $language === 'arabic' ? 'صالح' : 'Favorable';
                    break;
                case 'notes':
                    $value = $language === 'arabic' ? 'الدعوى 1026 / 2010 مستأنفة حالياً برقم 6162 / 22ق، وما زالت سارية' : 'Case 1026/2010 is currently appealed under number 6162/22q, and still active';
                    break;
                default:
                    $value = '';
                    break;
            }

            $row[] = $value;
        }

        return $row;
    }
}

