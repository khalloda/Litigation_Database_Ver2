<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GenerateCaseOpponentsTemplate extends Command
{
    protected $signature = 'templates:generate-case-opponents {--format=all : Format to generate (csv|xlsx|all)}';
    protected $description = 'Generate Case Opponents Import Template (companion import for multiple opponents)';

    public function handle()
    {
        $this->info('Generating Case Opponents Import Template...');

        $format = $this->option('format');

        // Ensure templates directory exists
        $templatesDir = storage_path('app/templates');
        if (!is_dir($templatesDir)) {
            mkdir($templatesDir, 0755, true);
        }

        // Get lookup data
        $lookupData = $this->getLookupData();

        if ($format === 'all' || $format === 'csv') {
            $this->generateCsv($lookupData);
        }

        if ($format === 'all' || $format === 'xlsx') {
            $this->generateXlsx($lookupData);
        }

        $this->info('✓ Case Opponents templates generated successfully!');
    }

    private function getLookupData()
    {
        $lookupData = [];

        // Get cases for case_id/case_number lookup
        $lookupData['cases'] = DB::table('cases')
            ->select('id as case_id', 'matter_name_ar', 'matter_name_en')
            ->limit(20)
            ->get();

        // Get opponents
        $lookupData['opponents'] = DB::table('opponents')
            ->select('id as opponent_id', 'opponent_name_ar', 'opponent_name_en')
            ->where('is_active', 1)
            ->limit(50)
            ->get();

        // Get capacity options
        $lookupData['capacities'] = DB::table('option_values')
            ->join('option_sets', 'option_values.set_id', '=', 'option_sets.id')
            ->where('option_sets.set_name', 'capacity')
            ->select('option_values.id as capacity_id', 'option_values.label_ar', 'option_values.label_en')
            ->get();

        return $lookupData;
    }

    private function generateCsv($lookupData)
    {
        $filename = 'Case_Opponents_Import_Template.csv';
        $path = storage_path("app/templates/{$filename}");

        $this->info("Generating CSV: {$filename}");

        $handle = fopen($path, 'w');

        // UTF-8 BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Headers
        $headers = [
            'case_id',
            'case_number',
            'opponent_name',
            'opponent_id',
            'capacity',
            'capacity_id',
            'is_primary',
            'display_order',
            'alias_text'
        ];
        fputcsv($handle, $headers);

        // Sample rows
        $this->addSampleRows($handle, $lookupData);

        fclose($handle);

        $this->info("✓ CSV generated: {$filename}");
    }

    private function generateXlsx($lookupData)
    {
        $filename = 'Case_Opponents_Import_Template.xlsx';
        $path = storage_path("app/templates/{$filename}");

        $this->info("Generating XLSX: {$filename}");

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Template
        $this->createTemplateSheet($spreadsheet, $lookupData);

        // Sheet 2: Lookups
        $this->createLookupsSheet($spreadsheet, $lookupData);

        // Sheet 3: README
        $this->createReadmeSheet($spreadsheet);

        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->info("✓ XLSX generated: {$filename}");
    }

    private function createTemplateSheet($spreadsheet, $lookupData)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Case_Opponents_Import');

        // Headers
        $headers = [
            'case_id',
            'case_number',
            'opponent_name',
            'opponent_id',
            'capacity',
            'capacity_id',
            'is_primary',
            'display_order',
            'alias_text'
        ];

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
        $this->addSampleRowsToSheet($sheet, $lookupData, count($headers));
    }

    private function createLookupsSheet($spreadsheet, $lookupData)
    {
        $lookupSheet = $spreadsheet->createSheet();
        $lookupSheet->setTitle('Lookups');

        $row = 1;
        $col = 1;

        // Cases lookup
        $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, 'cases_list');
        $row++;
        foreach ($lookupData['cases'] as $case) {
            $displayValue = $case->matter_name_ar . ' / ' . $case->matter_name_en . ' (ID: ' . $case->case_id . ')';
            $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $displayValue);
            $row++;
        }
        $row += 2;

        // Opponents lookup
        $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, 'opponents_list');
        $row++;
        foreach ($lookupData['opponents'] as $opponent) {
            $displayValue = $opponent->opponent_name_ar . ' / ' . $opponent->opponent_name_en . ' (ID: ' . $opponent->opponent_id . ')';
            $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $displayValue);
            $row++;
        }
        $row += 2;

        // Capacities lookup
        $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, 'capacities_list');
        $row++;
        foreach ($lookupData['capacities'] as $capacity) {
            $displayValue = $capacity->label_ar . ' / ' . $capacity->label_en . ' (ID: ' . $capacity->capacity_id . ')';
            $lookupSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $displayValue);
            $row++;
        }
    }

    private function createReadmeSheet($spreadsheet)
    {
        $readmeSheet = $spreadsheet->createSheet();
        $readmeSheet->setTitle('README');

        $content = [
            'Case Opponents Import Template',
            '==============================',
            'This template is for importing multiple opponents per case.',
            'Use this as a companion import after importing cases.',
            '',
            'Template Information',
            '====================',
            'Template Type: Case Opponents (Companion Import)',
            'Generated At (UTC): ' . now()->utc()->format('Y-m-d H:i:s'),
            '',
            'Column Descriptions',
            '===================',
            'case_id: ID of the case (required)',
            'case_number: Alternative to case_id for case lookup',
            'opponent_name: Name of the opponent (for fuzzy matching)',
            'opponent_id: ID of the opponent (direct lookup)',
            'capacity: Capacity text (for fuzzy matching)',
            'capacity_id: ID of the capacity (direct lookup)',
            'is_primary: 1 if this is the primary opponent, 0 otherwise',
            'display_order: Order for display (1, 2, 3, etc.)',
            'alias_text: Optional alias for the opponent in this case',
            '',
            'Import Rules',
            '============',
            '1. Provide either case_id OR case_number (not both)',
            '2. Provide either opponent_name OR opponent_id (not both)',
            '3. Provide either capacity OR capacity_id (not both)',
            '4. Only one opponent per case can be is_primary=1',
            '5. display_order should be unique per case',
            '6. Maximum 10 opponents per case (configurable)',
            '',
            'Sample Data',
            '===========',
            'Row 1: Primary opponent for case 1',
            'Row 2: Secondary opponent for case 1',
            'Row 3: Primary opponent for case 2',
            'Row 4: Secondary opponent for case 2',
            '',
            'Validation',
            '==========',
            'Preflight will validate:',
            '- Case exists and is accessible',
            '- Opponent exists and is active',
            '- Capacity exists in option_values',
            '- Only one primary opponent per case',
            '- Maximum opponents limit not exceeded',
            '',
            'Usage',
            '=====',
            '1. Import cases first using Cases Import Template',
            '2. Use this template to add multiple opponents',
            '3. Run preflight to validate data',
            '4. Execute import to attach opponents to cases',
            '',
            'Troubleshooting',
            '==============',
            'If case lookup fails: Check case_id or case_number',
            'If opponent lookup fails: Check opponent_name or opponent_id',
            'If capacity lookup fails: Check capacity or capacity_id',
            'If primary conflict: Ensure only one is_primary=1 per case',
            'If max opponents exceeded: Reduce number of opponents per case'
        ];

        $row = 1;
        foreach ($content as $line) {
            $readmeSheet->setCellValue('A' . $row, $line);
            $row++;
        }

        // Auto-fit columns
        $readmeSheet->getColumnDimension('A')->setAutoSize(true);
    }

    private function addSampleRows($handle, $lookupData)
    {
        // Sample data showing multiple opponents for same case
        $sampleData = [
            // Case 1 - Primary opponent
            [
                '1', // case_id
                '', // case_number (empty when using case_id)
                'سبيد ميديكا', // opponent_name
                '', // opponent_id (empty when using opponent_name)
                'مدعى عليه', // capacity
                '', // capacity_id (empty when using capacity)
                '1', // is_primary
                '1', // display_order
                '' // alias_text
            ],
            // Case 1 - Secondary opponent
            [
                '1', // case_id
                '', // case_number
                'شركة التأمين', // opponent_name
                '', // opponent_id
                'مدعى عليه ثاني', // capacity
                '', // capacity_id
                '0', // is_primary
                '2', // display_order
                'شركة التأمين العامة' // alias_text
            ],
            // Case 2 - Primary opponent (using case_number instead of case_id)
            [
                '', // case_id (empty when using case_number)
                'CASE-2024-001', // case_number
                'المحكمة', // opponent_name
                '', // opponent_id
                'جهة حكومية', // capacity
                '', // capacity_id
                '1', // is_primary
                '1', // display_order
                '' // alias_text
            ],
            // Case 2 - Secondary opponent (using IDs instead of names)
            [
                '2', // case_id
                '', // case_number
                '', // opponent_name (empty when using opponent_id)
                '5', // opponent_id
                '', // capacity (empty when using capacity_id)
                '18', // capacity_id
                '0', // is_primary
                '2', // display_order
                'الخصم الثاني' // alias_text
            ]
        ];

        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
    }

    private function addSampleRowsToSheet($sheet, $lookupData, $colCount)
    {
        $sampleData = [
            // Case 1 - Primary opponent
            ['1', '', 'سبيد ميديكا', '', 'مدعى عليه', '', '1', '1', ''],
            // Case 1 - Secondary opponent
            ['1', '', 'شركة التأمين', '', 'مدعى عليه ثاني', '', '0', '2', 'شركة التأمين العامة'],
            // Case 2 - Primary opponent
            ['', 'CASE-2024-001', 'المحكمة', '', 'جهة حكومية', '', '1', '1', ''],
            // Case 2 - Secondary opponent
            ['2', '', '', '5', '', '18', '0', '2', 'الخصم الثاني']
        ];

        $row = 2;
        foreach ($sampleData as $rowData) {
            for ($col = 1; $col <= $colCount; $col++) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $rowData[$col - 1] ?? '');
            }
            $row++;
        }
    }
}
