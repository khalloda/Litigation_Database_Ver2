<?php

namespace App\Exports;

use App\Models\Hearing;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HearingScheduleExport extends BaseReportExport
{
    protected Collection $hearings;
    protected Collection $upcoming;
    protected Collection $past;
    protected Collection $overdue;
    protected ?array $dateRange;
    protected string $locale;

    public function __construct(
        Collection $hearings,
        Collection $upcoming,
        Collection $past,
        Collection $overdue,
        ?array $dateRange = null,
        string $locale = 'en'
    ) {
        parent::__construct($locale);
        $this->hearings = $hearings;
        $this->upcoming = $upcoming;
        $this->past = $past;
        $this->overdue = $overdue;
        $this->dateRange = $dateRange;
        $this->locale = $locale;
    }

    protected function getTitle(): string
    {
        return $this->translate('reports.hearing_schedule.title');
    }

    protected function getSheetNames(): array
    {
        // Use English keys for internal matching, will translate when setting title
        return [
            'upcoming',
            'past',
            'overdue',
            'summary',
        ];
    }
    
    /**
     * Get translated sheet name for display
     */
    protected function getSheetDisplayName(string $key): string
    {
        return $this->translate("reports.hearing_schedule.{$key}");
    }

    protected function getHeaders(): array
    {
        return [
            'serial' => 'reports.hearing_schedule.serial',
            'date' => 'reports.hearing_schedule.date',
            'case_name' => 'reports.hearing_schedule.case_name',
            'client_name' => 'reports.hearing_schedule.client_name',
            'court' => 'reports.hearing_schedule.court',
            'procedure' => 'reports.hearing_schedule.procedure',
            'decision' => 'reports.hearing_schedule.decision',
            'next_hearing' => 'reports.hearing_schedule.next_hearing',
            'lawyer' => 'reports.hearing_schedule.lawyer',
            'status' => 'reports.hearing_schedule.status',
        ];
    }

    protected function getData(): array
    {
        $now = Carbon::now('Africa/Cairo');
        
        // Use English keys for internal matching
        return [
            'upcoming' => $this->prepareHearingRows($this->upcoming, $now),
            'past' => $this->prepareHearingRows($this->past, $now),
            'overdue' => $this->prepareHearingRows($this->overdue, $now),
            'summary' => $this->prepareSummaryRows(),
        ];
    }

    protected function prepareHearingRows(Collection $hearings, Carbon $now): array
    {
        return $hearings->map(function (Hearing $hearing, int $index) use ($now) {
            $hearingDate = $hearing->date;
            $hasDate = $hearingDate !== null;
            
            $isOverdue = $hasDate && $hearingDate < $now->copy()->startOfDay() 
                && (empty($hearing->decision) && empty($hearing->short_decision));
            $isUpcoming = $hasDate && $hearingDate >= $now->copy()->startOfDay();

            return [
                'serial' => $index + 1,
                'date' => $hasDate ? $hearingDate->format('Y-m-d') : '—',
                'case_name' => $hearing->case?->matter_name_ar ?? $hearing->case?->matter_name_en ?? '—',
                'client_name' => $hearing->case?->client?->client_name_ar ?? $hearing->case?->client?->client_name_en ?? '—',
                'court' => $hearing->case?->court?->court_name_ar
                    ?? $hearing->case?->court?->court_name_en
                    ?? $hearing->court
                    ?? '—',
                'procedure' => $hearing->procedure ?? '—',
                'decision' => $hearing->short_decision ?? $hearing->decision ?? '—',
                'next_hearing' => ($hearing->next_hearing instanceof \Carbon\Carbon) 
                    ? $hearing->next_hearing->format('Y-m-d') 
                    : '—',
                'lawyer' => $hearing->lawyer?->lawyer_name_ar ?? $hearing->lawyer?->lawyer_name_en ?? '—',
                'status' => $this->translate($isOverdue 
                    ? 'reports.hearing_schedule.overdue'
                    : ($isUpcoming ? 'reports.hearing_schedule.upcoming' : 'reports.hearing_schedule.past')),
            ];
        })->toArray();
    }

    protected function prepareSummaryRows(): array
    {
        $now = Carbon::now('Africa/Cairo');
        
        return [
            [
                'serial' => 1,
                'date' => $this->translate('reports.hearing_schedule.total_hearings'),
                'case_name' => $this->hearings->count(),
                'client_name' => '',
                'court' => '',
                'procedure' => '',
                'decision' => '',
                'next_hearing' => '',
                'lawyer' => '',
                'status' => '',
            ],
            [
                'serial' => 2,
                'date' => $this->translate('reports.hearing_schedule.upcoming'),
                'case_name' => $this->upcoming->count(),
                'client_name' => '',
                'court' => '',
                'procedure' => '',
                'decision' => '',
                'next_hearing' => '',
                'lawyer' => '',
                'status' => '',
            ],
            [
                'serial' => 3,
                'date' => $this->translate('reports.hearing_schedule.past'),
                'case_name' => $this->past->count(),
                'client_name' => '',
                'court' => '',
                'procedure' => '',
                'decision' => '',
                'next_hearing' => '',
                'lawyer' => '',
                'status' => '',
            ],
            [
                'serial' => 4,
                'date' => $this->translate('reports.hearing_schedule.overdue'),
                'case_name' => $this->overdue->count(),
                'client_name' => '',
                'court' => '',
                'procedure' => '',
                'decision' => '',
                'next_hearing' => '',
                'lawyer' => '',
                'status' => '',
            ],
        ];
    }

    protected function getColumnWidths(): array
    {
        return [
            'serial' => 8,
            'date' => 15,
            'case_name' => 30,
            'client_name' => 25,
            'court' => 20,
            'procedure' => 30,
            'decision' => 40,
            'next_hearing' => 15,
            'lawyer' => 20,
            'status' => 15,
        ];
    }


    protected function populateSheet($sheet, array $data): void
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
            foreach ($headers as $key => $headerKey) {
                $cell = $this->getColumnLetter($col) . $row;
                $value = $dataRow[$key] ?? '';
                $sheet->setCellValue($cell, $this->formatCellValue($value, $key));
                $this->applyCellStyle($sheet, $cell, $value, $key);
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

