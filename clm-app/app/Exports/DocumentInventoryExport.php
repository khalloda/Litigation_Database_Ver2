<?php

namespace App\Exports;

use App\Models\ClientDocument;
use App\Models\CaseModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Excel Export Class for Document Inventory Report
 *
 * Generates multi-sheet Excel files containing document inventory data with summary,
 * detailed inventory, grouped views (by location, client, or case), and missing documents.
 *
 * @package App\Exports
 * @since 2025-01-15
 */
class DocumentInventoryExport extends BaseReportExport
{
    /** @var Collection All documents to export */
    protected Collection $documents;

    /** @var array Summary statistics (total, physical, digital, both counts) */
    protected array $stats;

    /** @var Collection Cases without documents (if requested) */
    protected Collection $missingDocuments;

    /** @var string|null Grouping option: 'client', 'case', 'location', or null */
    protected ?string $groupBy;

    /** @var string Locale for translations ('en' or 'ar') */
    protected string $locale;

    /**
     * Constructor
     *
     * @param Collection $documents All documents to export
     * @param array $stats Summary statistics array with keys: total, physical, digital, both
     * @param Collection $missingDocuments Cases without documents (if requested)
     * @param string|null $groupBy Grouping option: 'client', 'case', 'location', or null
     * @param string $locale Locale for translations ('en' or 'ar')
     */
    public function __construct(
        Collection $documents,
        array $stats,
        Collection $missingDocuments,
        ?string $groupBy = null,
        string $locale = 'en'
    ) {
        parent::__construct($locale);
        $this->documents = $documents;
        $this->stats = $stats;
        $this->missingDocuments = $missingDocuments;
        $this->groupBy = $groupBy;
        $this->locale = $locale;
    }

    protected function getTitle(): string
    {
        return $this->translate('reports.document_inventory.title');
    }

    protected function getSheetNames(): array
    {
        // Use English keys for internal matching, will translate when setting title
        $sheets = [
            'summary',
            'detailed_inventory',
        ];

        // Add grouped sheets if grouping is requested
        if ($this->groupBy === 'location' || $this->groupBy === null) {
            $sheets[] = 'by_location';
        }
        if ($this->groupBy === 'client' || $this->groupBy === null) {
            $sheets[] = 'by_client';
        }
        if ($this->groupBy === 'case' || $this->groupBy === null) {
            $sheets[] = 'by_case';
        }

        // Add missing documents sheet if there are missing documents
        if ($this->missingDocuments->isNotEmpty()) {
            $sheets[] = 'missing_documents';
        }

        return $sheets;
    }
    
    /**
     * Get translated sheet name for display
     */
    protected function getSheetDisplayName(string $key): string
    {
        return $this->translate("reports.document_inventory.{$key}");
    }

    protected function getHeaders(): array
    {
        return [
            'serial' => 'reports.document_inventory.serial',
            'client_name' => 'reports.document_inventory.client_name',
            'case_name' => 'reports.document_inventory.case_name',
            'document_name' => 'reports.document_inventory.document_name',
            'document_type' => 'reports.document_inventory.type',
            'deposit_date' => 'reports.document_inventory.deposit_date',
            'location' => 'reports.document_inventory.location',
            'storage_type' => 'reports.document_inventory.storage_type',
            'movement_card' => 'reports.document_inventory.movement_card',
            'file_size' => 'reports.document_inventory.file_size',
        ];
    }

    protected function getData(): array
    {
        // Use English keys for internal matching
        $data = [
            'summary' => $this->prepareSummaryRows(),
            'detailed_inventory' => $this->prepareDocumentRows($this->documents),
        ];

        // Add grouped sheets
        if ($this->groupBy === 'location' || $this->groupBy === null) {
            $data['by_location'] = $this->prepareByLocationRows();
        }

        if ($this->groupBy === 'client' || $this->groupBy === null) {
            $data['by_client'] = $this->prepareByClientRows();
        }

        if ($this->groupBy === 'case' || $this->groupBy === null) {
            $data['by_case'] = $this->prepareByCaseRows();
        }

        // Add missing documents sheet if applicable
        if ($this->missingDocuments->isNotEmpty()) {
            $data['missing_documents'] = $this->prepareMissingDocumentsRows();
        }

        return $data;
    }

    protected function prepareSummaryRows(): array
    {
        return [
            [
                'serial' => 1,
                'client_name' => $this->translate('reports.document_inventory.total_documents'),
                'case_name' => $this->stats['total'] ?? 0,
                'document_name' => '',
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ],
            [
                'serial' => 2,
                'client_name' => $this->translate('reports.document_inventory.physical'),
                'case_name' => $this->stats['physical'] ?? 0,
                'document_name' => '',
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ],
            [
                'serial' => 3,
                'client_name' => $this->translate('reports.document_inventory.digital'),
                'case_name' => $this->stats['digital'] ?? 0,
                'document_name' => '',
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ],
            [
                'serial' => 4,
                'client_name' => $this->translate('reports.document_inventory.both'),
                'case_name' => $this->stats['both'] ?? 0,
                'document_name' => '',
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ],
        ];
    }

    protected function prepareDocumentRows(Collection $documents): array
    {
        return $documents->map(function (ClientDocument $document, int $index) {
            $fileSize = $document->file_size 
                ? $this->formatFileSize($document->file_size) 
                : '—';

            return [
                'serial' => $index + 1,
                'client_name' => $document->client?->client_name_ar ?? $document->client?->client_name_en ?? '—',
                'case_name' => $document->case?->matter_name_ar ?? $document->case?->matter_name_en ?? '—',
                'document_name' => $document->document_name ?? $document->document_description ?? '—',
                'document_type' => $document->document_type ?? '—',
                'deposit_date' => $document->deposit_date?->format('Y-m-d') ?? '—',
                'location' => $document->document_location ?? '—',
                'storage_type' => $this->translate('reports.document_inventory.storage_type_' . ($document->document_storage_type ?? 'digital')),
                'movement_card' => $document->movement_card 
                    ? $this->translate('common.yes') 
                    : $this->translate('common.no'),
                'file_size' => $fileSize,
            ];
        })->toArray();
    }

    protected function prepareByLocationRows(): array
    {
        $grouped = $this->documents->groupBy('document_location');
        $rows = [];
        $globalIndex = 1;

        foreach ($grouped as $location => $locationDocuments) {
            // Add group header row
            $rows[] = [
                'serial' => '',
                'client_name' => $this->translate('reports.document_inventory.location') . ': ' . ($location ?? '—'),
                'case_name' => '(' . $locationDocuments->count() . ' ' . $this->translate('reports.document_inventory.documents') . ')',
                'document_name' => '',
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ];

            // Add documents for this location
            foreach ($locationDocuments as $document) {
                $fileSize = $document->file_size 
                    ? $this->formatFileSize($document->file_size) 
                    : '—';

                $rows[] = [
                    'serial' => $globalIndex++,
                    'client_name' => $document->client?->client_name_ar ?? $document->client?->client_name_en ?? '—',
                    'case_name' => $document->case?->matter_name_ar ?? $document->case?->matter_name_en ?? '—',
                    'document_name' => $document->document_name ?? $document->document_description ?? '—',
                    'document_type' => $document->document_type ?? '—',
                    'deposit_date' => $document->deposit_date?->format('Y-m-d') ?? '—',
                    'location' => $document->document_location ?? '—',
                    'storage_type' => $this->translate('reports.document_inventory.storage_type_' . ($document->document_storage_type ?? 'digital')),
                    'movement_card' => $document->movement_card 
                        ? $this->translate('common.yes') 
                        : $this->translate('common.no'),
                    'file_size' => $fileSize,
                ];
            }

            // Add empty row between groups
            $rows[] = array_fill_keys(array_keys($this->getHeaders()), '');
        }

        return $rows;
    }

    protected function prepareByClientRows(): array
    {
        $grouped = $this->documents->groupBy('client_id');
        $rows = [];
        $globalIndex = 1;

        foreach ($grouped as $clientId => $clientDocuments) {
            $client = $clientDocuments->first()?->client;
            $clientName = $client?->client_name_ar ?? $client?->client_name_en ?? '—';

            // Add group header row
            $rows[] = [
                'serial' => '',
                'client_name' => $this->translate('reports.document_inventory.client_name') . ': ' . $clientName,
                'case_name' => '(' . $clientDocuments->count() . ' ' . $this->translate('reports.document_inventory.documents') . ')',
                'document_name' => '',
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ];

            // Add documents for this client
            foreach ($clientDocuments as $document) {
                $fileSize = $document->file_size 
                    ? $this->formatFileSize($document->file_size) 
                    : '—';

                $rows[] = [
                    'serial' => $globalIndex++,
                    'client_name' => $document->client?->client_name_ar ?? $document->client?->client_name_en ?? '—',
                    'case_name' => $document->case?->matter_name_ar ?? $document->case?->matter_name_en ?? '—',
                    'document_name' => $document->document_name ?? $document->document_description ?? '—',
                    'document_type' => $document->document_type ?? '—',
                    'deposit_date' => $document->deposit_date?->format('Y-m-d') ?? '—',
                    'location' => $document->document_location ?? '—',
                    'storage_type' => $this->translate('reports.document_inventory.storage_type_' . ($document->document_storage_type ?? 'digital')),
                    'movement_card' => $document->movement_card 
                        ? $this->translate('common.yes') 
                        : $this->translate('common.no'),
                    'file_size' => $fileSize,
                ];
            }

            // Add empty row between groups
            $rows[] = array_fill_keys(array_keys($this->getHeaders()), '');
        }

        return $rows;
    }

    protected function prepareByCaseRows(): array
    {
        $grouped = $this->documents->groupBy('matter_id');
        $rows = [];
        $globalIndex = 1;

        foreach ($grouped as $caseId => $caseDocuments) {
            $case = $caseDocuments->first()?->case;
            $caseName = $case?->matter_name_ar ?? $case?->matter_name_en ?? '—';

            // Add group header row
            $rows[] = [
                'serial' => '',
                'client_name' => $this->translate('reports.document_inventory.case_name') . ': ' . $caseName,
                'case_name' => '(' . $caseDocuments->count() . ' ' . $this->translate('reports.document_inventory.documents') . ')',
                'document_name' => '',
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ];

            // Add documents for this case
            foreach ($caseDocuments as $document) {
                $fileSize = $document->file_size 
                    ? $this->formatFileSize($document->file_size) 
                    : '—';

                $rows[] = [
                    'serial' => $globalIndex++,
                    'client_name' => $document->client?->client_name_ar ?? $document->client?->client_name_en ?? '—',
                    'case_name' => $document->case?->matter_name_ar ?? $document->case?->matter_name_en ?? '—',
                    'document_name' => $document->document_name ?? $document->document_description ?? '—',
                    'document_type' => $document->document_type ?? '—',
                    'deposit_date' => $document->deposit_date?->format('Y-m-d') ?? '—',
                    'location' => $document->document_location ?? '—',
                    'storage_type' => $this->translate('reports.document_inventory.storage_type_' . ($document->document_storage_type ?? 'digital')),
                    'movement_card' => $document->movement_card 
                        ? $this->translate('common.yes') 
                        : $this->translate('common.no'),
                    'file_size' => $fileSize,
                ];
            }

            // Add empty row between groups
            $rows[] = array_fill_keys(array_keys($this->getHeaders()), '');
        }

        return $rows;
    }

    protected function prepareMissingDocumentsRows(): array
    {
        return $this->missingDocuments->map(function ($case, int $index) {
            return [
                'serial' => $index + 1,
                'client_name' => $case['client'] ?? '—',
                'case_name' => $case['case'] ?? '—',
                'document_name' => $this->translate('reports.document_inventory.reason_no_documents'),
                'document_type' => '',
                'deposit_date' => '',
                'location' => '',
                'storage_type' => '',
                'movement_card' => '',
                'file_size' => '',
            ];
        })->toArray();
    }

    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function getColumnWidths(): array
    {
        return [
            'serial' => 8,
            'client_name' => 25,
            'case_name' => 30,
            'document_name' => 30,
            'document_type' => 20,
            'deposit_date' => 15,
            'location' => 25,
            'storage_type' => 15,
            'movement_card' => 12,
            'file_size' => 12,
        ];
    }

    protected function populateSheet($sheet, array $data): void
    {
        $headers = $this->getHeaders();
        $row = 1;

        // Add title
        if (!empty($this->getTitle())) {
            $sheet->setCellValue('A1', $this->getTitle());
            $sheet->mergeCells('A1:' . $this->getColumnLetter(count($headers)) . '1');
            $this->applyTitleStyle($sheet, 'A1');
            $row = 2;
        }

        // Add headers
        $headerRow = $row;
        $col = 1;
        foreach ($headers as $key => $headerKey) {
            $cell = $this->getColumnLetter($col) . $headerRow;
            $sheet->setCellValue($cell, $this->translate($headerKey));
            $this->applyHeaderStyle($sheet, $cell);
            $col++;
        }
        $row++;

        // Add data rows
        foreach ($data as $dataRow) {
            $col = 1;
            $isGroupHeader = empty($dataRow['serial']) && (!empty($dataRow['client_name']) || !empty($dataRow['case_name']));

            foreach ($headers as $key => $headerKey) {
                $cell = $this->getColumnLetter($col) . $row;
                $value = $dataRow[$key] ?? '';

                if ($isGroupHeader && ($key === 'client_name' || $key === 'case_name')) {
                    // Style group headers differently
                    $sheet->setCellValue($cell, $value);
                    $sheet->getStyle($cell)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                    ]);
                } else {
                    $sheet->setCellValue($cell, $this->formatCellValue($value, $key));
                    $this->applyCellStyle($sheet, $cell, $value, $key);
                }

                $col++;
            }
            $row++;
        }

        // Auto-size columns
        $columnWidths = $this->getColumnWidths();
        $colIndex = 1;
        foreach ($headers as $headerKeyName => $headerKey) {
            $columnLetter = $this->getColumnLetter($colIndex);
            
            if (isset($columnWidths[$headerKeyName])) {
                $sheet->getColumnDimension($columnLetter)->setWidth($columnWidths[$headerKeyName]);
            } else {
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
            $colIndex++;
        }

        // Freeze header row
        if ($row > $headerRow + 1) {
            $sheet->freezePane('A' . ($headerRow + 1));
        }
    }
}

