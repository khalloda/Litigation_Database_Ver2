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
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Collection\CellsFactory;

class GenerateCasesTemplate extends Command
{
    protected $signature = 'templates:generate-cases {--mode=all : Generate standard, extended, or all templates}';
    protected $description = 'Generate Cases import templates (CSV + XLSX) from database schema';

    private $schemaSignature;
    private $templateVersion = '1.0';

    public function handle()
    {
        $mode = $this->option('mode');

        if (!in_array($mode, ['standard', 'extended', 'all'])) {
            $this->error('Invalid mode. Use: standard, extended, or all');
            return 1;
        }

        $this->info("Generating Cases import templates (mode: {$mode})...");

        // Set memory-friendly PhpSpreadsheet settings
        // Note: Cache settings can be configured in config if needed

        // Ensure templates directory exists
        $this->ensureTemplatesDirectory();

        // Get schema information
        $schemaInfo = $this->getSchemaInfo();
        $this->schemaSignature = $this->calculateSchemaSignature($schemaInfo);

        // Get lookup data for enums
        $lookupData = $this->getLookupData();

        // Generate templates based on mode
        if ($mode === 'standard' || $mode === 'all') {
            $this->generateStandardTemplates($schemaInfo, $lookupData);
        }

        if ($mode === 'extended' || $mode === 'all') {
            $this->generateExtendedTemplates($schemaInfo, $lookupData);
        }

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
            WHERE TABLE_SCHEMA = 'litigation_db_ver2'
            AND TABLE_NAME = 'cases'
            ORDER BY ORDINAL_POSITION
        ");

        $foreignKeys = DB::select("
            SELECT kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
            WHERE kcu.TABLE_SCHEMA = 'litigation_db_ver2'
            AND kcu.TABLE_NAME = 'cases'
            AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ");

        return [
            'columns' => $columns,
            'foreign_keys' => $foreignKeys
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

        // Short enums (for dropdowns)
        $lookups['status'] = $this->getOptionValues('case.status');
        $lookups['category'] = $this->getOptionValues('case.category');
        $lookups['degree'] = $this->getOptionValues('case.degree');
        $lookups['importance'] = $this->getOptionValues('case.importance');
        $lookups['branch'] = $this->getOptionValues('case.branch');
        $lookups['capacity'] = $this->getOptionValues('capacity.type');
        $lookups['client_type'] = $this->getOptionValues('client.cash_or_probono');
        $lookups['circuit_shift'] = $this->getOptionValues('circuit.shift');

        // Large lists (for free text + preflight)
        $lookups['courts'] = $this->getCourts();
        $lookups['opponents'] = $this->getOpponents();
        $lookups['lawyers'] = $this->getLawyers();

        return $lookups;
    }

    private function getOptionValues($optionSetKey)
    {
        return DB::table('option_values')
            ->join('option_sets', 'option_values.set_id', '=', 'option_sets.id')
            ->where('option_sets.key', $optionSetKey)
            ->select('option_values.id', 'option_values.label_en', 'option_values.label_ar')
            ->orderBy('option_values.position')
            ->get()
            ->toArray();
    }

    private function getCourts()
    {
        return DB::table('courts')
            ->select('id', 'court_name_en', 'court_name_ar')
            ->orderBy('court_name_en')
            ->get()
            ->toArray();
    }

    private function getOpponents()
    {
        return DB::table('opponents')
            ->select('id', 'opponent_name_en', 'opponent_name_ar')
            ->orderBy('opponent_name_en')
            ->get()
            ->toArray();
    }

    private function getLawyers()
    {
        return DB::table('lawyers')
            ->join('option_values as titles', 'lawyers.title_id', '=', 'titles.id')
            ->whereIn('titles.label_en', ['Managing Partner', 'Senior Partner', 'Partner', 'Junior Partner'])
            ->select('lawyers.id', 'lawyers.lawyer_name_en', 'lawyers.lawyer_name_ar')
            ->orderBy('lawyers.lawyer_name_en')
            ->get()
            ->toArray();
    }

    private function generateStandardTemplates($schemaInfo, $lookupData)
    {
        $this->info('Generating Standard templates...');

        // Define Standard columns (core fields only)
        $standardColumns = $this->getStandardColumns($schemaInfo['columns']);

        // Generate CSV
        $this->generateCsv('Standard', $standardColumns, $lookupData);

        // Generate XLSX
        $this->generateXlsx('Standard', $standardColumns, $lookupData);
    }

    private function generateExtendedTemplates($schemaInfo, $lookupData)
    {
        $this->info('Generating Extended templates...');

        // Define Extended columns (all fields)
        $extendedColumns = $this->getExtendedColumns($schemaInfo['columns']);

        // Generate CSV
        $this->generateCsv('Extended', $extendedColumns, $lookupData);

        // Generate XLSX
        $this->generateXlsx('Extended', $extendedColumns, $lookupData);
    }

    private function getStandardColumns($columns)
    {
        // Core fields needed for a basic case
        $coreFields = [
            'client_id',
            'client_name',
            'client_in_case_name',
            'matter_name_ar',
            'matter_name_en',
            'matter_description',
            'matter_status_id',
            'matter_status',
            'matter_category_id',
            'matter_category',
            'matter_degree_id',
            'matter_degree',
            'matter_importance_id',
            'matter_importance',
            'court_id',
            'court_name',
            'opponent_id',
            'opponent_name',
            'client_capacity_id',
            'opponent_capacity_id',
            'matter_partner_id',
            'matter_partner_name',
            'matter_start_date',
            'matter_end_date',
            'matter_asked_amount',
            'matter_judged_amount',
            'notes_1'
        ];

        return array_filter($columns, function ($col) use ($coreFields) {
            return in_array($col->COLUMN_NAME, $coreFields);
        });
    }

    private function getExtendedColumns($columns)
    {
        // All importable columns (exclude system fields)
        $excludeFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

        $filteredColumns = array_filter($columns, function ($col) use ($excludeFields) {
            return !in_array($col->COLUMN_NAME, $excludeFields);
        });

        // Add multiple opponent columns for Extended template
        $multiOpponentColumns = [];
        for ($i = 1; $i <= 5; $i++) {
            $multiOpponentColumns[] = (object)[
                'COLUMN_NAME' => "opponent{$i}_name",
                'DATA_TYPE' => 'varchar',
                'IS_NULLABLE' => 'YES',
                'COLUMN_DEFAULT' => null
            ];
            $multiOpponentColumns[] = (object)[
                'COLUMN_NAME' => "opponent{$i}_id",
                'DATA_TYPE' => 'bigint',
                'IS_NULLABLE' => 'YES',
                'COLUMN_DEFAULT' => null
            ];
            $multiOpponentColumns[] = (object)[
                'COLUMN_NAME' => "opponent{$i}_capacity",
                'DATA_TYPE' => 'varchar',
                'IS_NULLABLE' => 'YES',
                'COLUMN_DEFAULT' => null
            ];
            $multiOpponentColumns[] = (object)[
                'COLUMN_NAME' => "opponent{$i}_capacity_id",
                'DATA_TYPE' => 'bigint',
                'IS_NULLABLE' => 'YES',
                'COLUMN_DEFAULT' => null
            ];
        }

        return array_merge($filteredColumns, $multiOpponentColumns);
    }

    private function generateCsv($type, $columns, $lookupData)
    {
        $filename = "Cases_Import_Template_{$type}.csv";
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
        $this->addSampleRows($handle, $columns, $type);

        fclose($handle);

        $this->info("✓ CSV generated: {$filename}");
    }

    private function generateXlsx($type, $columns, $lookupData)
    {
        $filename = "Cases_Import_Template_{$type}.xlsx";
        $path = storage_path("app/templates/{$filename}");

        $this->info("Generating XLSX: {$filename}");

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Template
        $this->createTemplateSheet($spreadsheet, $columns, $type);

        // Sheet 2: Lookups
        $this->createLookupsSheet($spreadsheet, $lookupData);

        // Sheet 3: README
        $this->createReadmeSheet($spreadsheet, $type);

        // Apply data validation for short enums
        $this->applyDataValidation($spreadsheet, $lookupData, $columns);

        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->info("✓ XLSX generated: {$filename}");
    }

    private function createTemplateSheet($spreadsheet, $columns, $type)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cases_Import_Template');

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
        $this->addSampleRowsToSheet($sheet, $columns, $type, count($headers));
    }

    private function createLookupsSheet($spreadsheet, $lookupData)
    {
        $lookupSheet = $spreadsheet->createSheet();
        $lookupSheet->setTitle('Lookups');

        $row = 1;
        $col = 1;

        // Short enums (for dropdowns)
        foreach ($lookupData as $key => $values) {
            if (in_array($key, ['courts', 'opponents', 'lawyers'])) {
                continue; // Skip large lists
            }

            $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $key . '_list');
            $row++;

            foreach ($values as $value) {
                $displayValue = $value->label_en ?? $value->label_ar ?? $value->name_en ?? $value->name_ar ?? $value->id;
                $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $displayValue);
                $row++;
            }

            $row += 2; // Space between lists
        }
    }

    private function createReadmeSheet($spreadsheet, $type)
    {
        $readmeSheet = $spreadsheet->createSheet();
        $readmeSheet->setTitle('README');

        $content = [
            'Template Information',
            '====================',
            'Template Type: ' . $type,
            'Template Version: ' . $this->templateVersion,
            'DB Schema Signature: ' . $this->schemaSignature,
            'Generated At (UTC): ' . now()->utc()->format('Y-m-d H:i:s'),
            '',
            'File Format & Encoding',
            '======================',
            'Encoding: UTF-8 (save CSV as UTF-8 with BOM)',
            'Date Format: YYYY-MM-DD (ISO 8601)',
            'Decimal Separator: Dot (e.g., 125000.50)',
            'Boolean: 1/0 or true/false',
            'Language: Arabic and/or English allowed in text fields',
            '',
            'Required Fields',
            '===============',
            'client_id OR client_name (at least one required)',
            'matter_name_ar (required)',
            'matter_name_en (required)',
            'All other fields optional unless marked NOT NULL in database',
            '',
            'Foreign Key Resolution: ID vs Name Precedence',
            '=============================================',
            'Rule: For any FK (client, court, opponent, partner), you can provide EITHER:',
            '- ID column (e.g., client_id): Direct lookup, fastest',
            '- Name column (e.g., client_name): Fuzzy match with AR/EN normalization',
            'Precedence: If BOTH provided, ID wins (Name ignored)',
            'Conflict Warning: If ID and Name disagree, preflight logs a WARNING',
            'Fuzzy Matching: Name columns use bilingual normalization + similarity scoring',
            '',
            'Validation & Preflight',
            '======================',
            'Short enums have dropdowns in XLSX (status, category, degree, importance, capacity)',
            'Large lists (courts, opponents) use free text; preflight suggests closest matches',
            'Bilingual fuzzy matching for opponent names (existing OpponentSuggestionService)',
            'Error threshold: 15% (stops import if exceeded)',
            '',
            'Limitations',
            '===========',
            'Dropdowns only for short enumerations (<50 values)',
            'Large vocabularies (courts, opponents) rely on preflight name matching',
            'Excel row limit: 1,048,576 (should be sufficient for most imports)',
            '',
            'Standard vs Extended',
            '====================',
            'Standard: Core fields needed to create a basic case (~25 columns)',
            'Extended: All optional/legacy/advanced fields (~35 additional columns)',
            'Use Standard for most imports; use Extended when migrating legacy data',
            '',
            'Multiple Opponents (Extended Template Only)',
            '==========================================',
            'Extended template includes opponent1-5 columns for multiple opponents:',
            '- opponent1_name, opponent1_id, opponent1_capacity, opponent1_capacity_id',
            '- opponent2_name, opponent2_id, opponent2_capacity, opponent2_capacity_id',
            '- ... up to opponent5',
            'opponent1 becomes the primary opponent (is_primary=1)',
            'Additional opponents can be added via companion import (Case_Opponents_Import)',
            'Maximum opponents per case: 10 (configurable)',
            '',
            'Regeneration',
            '============',
            'Command: php artisan templates:generate-cases --mode=all',
            'Run after schema changes to update templates',
            'Admin users can regenerate via UI button'
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
        $templateSheet = $spreadsheet->getSheetByName('Cases_Import_Template');
        $lookupSheet = $spreadsheet->getSheetByName('Lookups');

        // Map column names to their positions
        $columnMap = [];
        $col = 1;
        foreach ($columns as $column) {
            $columnMap[$column->COLUMN_NAME] = $col;
            $col++;
        }

        // Apply validation to short enum columns
        $enumColumns = [
            'matter_status_id' => 'status',
            'matter_category_id' => 'category',
            'matter_degree_id' => 'degree',
            'matter_importance_id' => 'importance',
            'matter_branch_id' => 'branch',
            'client_capacity_id' => 'capacity',
            'opponent_capacity_id' => 'capacity',
            'client_type_id' => 'client_type',
            'circuit_shift_id' => 'circuit_shift'
        ];

        foreach ($enumColumns as $columnName => $lookupKey) {
            if (isset($columnMap[$columnName]) && isset($lookupData[$lookupKey]) && count($lookupData[$lookupKey]) < 50) {
                $colIndex = $columnMap[$columnName];
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

                // Find the range for this lookup in the Lookups sheet
                $lookupRange = $this->findLookupRange($lookupSheet, $lookupKey);

                if ($lookupRange) {
                    // Create named range
                    $rangeName = $lookupKey . '_list';
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
        }
    }

    private function findLookupRange($lookupSheet, $lookupKey)
    {
        // Find the range for the lookup key in the Lookups sheet
        $maxRow = $lookupSheet->getHighestRow();
        $maxCol = $lookupSheet->getHighestColumn();

        for ($row = 1; $row <= $maxRow; $row++) {
            $cellValue = $lookupSheet->getCell('A' . $row)->getValue();
            if ($cellValue === $lookupKey . '_list') {
                // Find the end of this list
                $startRow = $row + 1;
                $endRow = $startRow;
                for ($checkRow = $startRow; $checkRow <= $maxRow; $checkRow++) {
                    $nextCell = $lookupSheet->getCell('A' . $checkRow)->getValue();
                    if (empty($nextCell) || strpos($nextCell, '_list') !== false) {
                        $endRow = $checkRow - 1;
                        break;
                    }
                    $endRow = $checkRow;
                }

                if ($endRow >= $startRow) {
                    return 'A' . $startRow . ':A' . $endRow;
                }
            }
        }

        return null;
    }

    private function addSampleRows($handle, $columns, $type)
    {
        // Row 1 (Arabic-focused)
        $row1 = $this->generateSampleRow($columns, 'arabic');
        fputcsv($handle, $row1);

        // Row 2 (English-focused)
        $row2 = $this->generateSampleRow($columns, 'english');
        fputcsv($handle, $row2);
    }

    private function addSampleRowsToSheet($sheet, $columns, $type, $colCount)
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
                case 'client_id':
                    $value = '123';
                    break;
                case 'client_name':
                    $value = $language === 'arabic' ? 'شركة سبيد ميديكال' : 'Speed Medical SAE';
                    break;
                case 'client_in_case_name':
                    $value = $language === 'arabic' ? 'شركة سبيد ميديكال ش.م.م' : 'Speed Medical SAE';
                    break;
                case 'matter_name_ar':
                    $value = $language === 'arabic' ? 'مطالبة مالية ضد سبيد ميديكا' : 'Financial Claim vs Speed Medical';
                    break;
                case 'matter_name_en':
                    $value = $language === 'arabic' ? 'Financial Claim vs Speed Medical' : 'Breach of Contract';
                    break;
                case 'matter_description':
                    $value = $language === 'arabic' ? 'دعوى مطالبة مالية ضد شركة سبيد ميديكا' : 'Contract breach case against Speed Medical';
                    break;
                case 'matter_status_id':
                    $value = '1';
                    break;
                case 'matter_status':
                    $value = $language === 'arabic' ? 'سارية' : 'Active';
                    break;
                case 'matter_category_id':
                    $value = '2';
                    break;
                case 'matter_category':
                    $value = $language === 'arabic' ? 'تجاري' : 'Commercial';
                    break;
                case 'matter_degree_id':
                    $value = '8';
                    break;
                case 'matter_degree':
                    $value = $language === 'arabic' ? 'ابتدائي' : 'First Instance';
                    break;
                case 'matter_importance_id':
                    $value = '2';
                    break;
                case 'matter_importance':
                    $value = $language === 'arabic' ? 'عاجل' : 'Urgent';
                    break;
                case 'court_id':
                    $value = '5';
                    break;
                case 'court_name':
                    $value = $language === 'arabic' ? 'القاهرة الاقتصادية' : 'Cairo Economic Court';
                    break;
                case 'opponent_id':
                    $value = '8';
                    break;
                case 'opponent_name':
                    $value = $language === 'arabic' ? 'سبيد ميديكا' : 'Speed Medical';
                    break;
                case 'client_capacity_id':
                    $value = '15';
                    break;
                case 'opponent_capacity_id':
                    $value = '18';
                    break;
                case 'matter_partner_id':
                    $value = '1';
                    break;
                case 'matter_partner_name':
                    $value = $language === 'arabic' ? 'كمال حلمي' : 'Kamal Helmy';
                    break;
                case 'matter_start_date':
                    $value = $language === 'arabic' ? '2024-11-20' : '2023-06-01';
                    break;
                case 'matter_end_date':
                    $value = $language === 'arabic' ? '' : '2024-12-31';
                    break;
                case 'matter_asked_amount':
                    $value = $language === 'arabic' ? '250000.00' : '125000.50';
                    break;
                case 'matter_judged_amount':
                    $value = $language === 'arabic' ? '' : '10000.00';
                    break;
                case 'notes_1':
                    $value = $language === 'arabic' ? 'قضية مستعجلة؛ متابعة دورية' : 'Ongoing case; quarterly review';
                    break;
                // Multiple opponents for Extended template
                case 'opponent1_name':
                    $value = $language === 'arabic' ? 'سبيد ميديكا' : 'Speed Medical';
                    break;
                case 'opponent1_id':
                    $value = '8';
                    break;
                case 'opponent1_capacity':
                    $value = $language === 'arabic' ? 'مدعى عليه' : 'Defendant';
                    break;
                case 'opponent1_capacity_id':
                    $value = '18';
                    break;
                case 'opponent2_name':
                    $value = $language === 'arabic' ? 'شركة التأمين' : 'Insurance Company';
                    break;
                case 'opponent2_id':
                    $value = '9';
                    break;
                case 'opponent2_capacity':
                    $value = $language === 'arabic' ? 'مدعى عليه ثاني' : 'Second Defendant';
                    break;
                case 'opponent2_capacity_id':
                    $value = '19';
                    break;
                case 'opponent3_name':
                    $value = $language === 'arabic' ? 'المحكمة' : 'Court';
                    break;
                case 'opponent3_id':
                    $value = '10';
                    break;
                case 'opponent3_capacity':
                    $value = $language === 'arabic' ? 'جهة حكومية' : 'Government Entity';
                    break;
                case 'opponent3_capacity_id':
                    $value = '20';
                    break;
                case 'opponent4_name':
                    $value = $language === 'arabic' ? 'البنك' : 'Bank';
                    break;
                case 'opponent4_id':
                    $value = '11';
                    break;
                case 'opponent4_capacity':
                    $value = $language === 'arabic' ? 'مدعى عليه ثالث' : 'Third Defendant';
                    break;
                case 'opponent4_capacity_id':
                    $value = '21';
                    break;
                case 'opponent5_name':
                    $value = $language === 'arabic' ? 'الشركة المصرية' : 'Egyptian Company';
                    break;
                case 'opponent5_id':
                    $value = '12';
                    break;
                case 'opponent5_capacity':
                    $value = $language === 'arabic' ? 'مدعى عليه رابع' : 'Fourth Defendant';
                    break;
                case 'opponent5_capacity_id':
                    $value = '22';
                    break;
                default:
                    // For other columns, provide appropriate sample data
                    if (str_contains($columnName, '_id')) {
                        $value = '1';
                    } elseif (str_contains($columnName, '_date')) {
                        $value = $language === 'arabic' ? '2024-11-20' : '2023-06-01';
                    } elseif (str_contains($columnName, '_amount') || str_contains($columnName, 'fee_')) {
                        $value = '1000.00';
                    } elseif (str_contains($columnName, 'note') || str_contains($columnName, 'description')) {
                        $value = $language === 'arabic' ? 'ملاحظة' : 'Note';
                    } else {
                        $value = '';
                    }
                    break;
            }

            $row[] = $value;
        }

        return $row;
    }
}
