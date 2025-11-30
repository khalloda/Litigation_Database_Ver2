<?php

namespace App\Exports;

use App\Models\AdminTask;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Excel Export Class for Administrative Tasks Report
 *
 * Generates multi-sheet Excel files containing administrative task data with summary,
 * detailed, overdue, and optional grouped views (by lawyer or by case).
 *
 * @package App\Exports
 * @since 2025-01-15
 */
class AdminTasksExport extends BaseReportExport
{
    /** @var Collection All tasks to export */
    protected Collection $tasks;

    /** @var Collection Tasks that are overdue */
    protected Collection $overdue;

    /** @var Collection Tasks that are completed */
    protected Collection $completed;

    /** @var Collection Tasks that are pending */
    protected Collection $pending;

    /** @var float Completion rate percentage (0-100) */
    protected float $completionRate;

    /** @var string|null Grouping option: 'lawyer', 'case', or null */
    protected ?string $groupBy;

    /** @var bool Whether to include subtask counts */
    protected bool $includeSubtasks;

    /** @var array|null Date range filter array with 'start' and 'end' keys */
    protected ?array $dateRange;

    /** @var string Locale for translations ('en' or 'ar') */
    protected string $locale;

    /**
     * Constructor
     *
     * @param Collection $tasks All tasks to export
     * @param Collection $overdue Tasks that are overdue
     * @param Collection $completed Tasks that are completed
     * @param Collection $pending Tasks that are pending
     * @param float $completionRate Completion rate percentage (0-100)
     * @param string|null $groupBy Grouping option: 'lawyer', 'case', or null
     * @param bool $includeSubtasks Whether to include subtask counts in export
     * @param array|null $dateRange Date range filter with 'start' and 'end' keys
     * @param string $locale Locale for translations ('en' or 'ar')
     */
    public function __construct(
        Collection $tasks,
        Collection $overdue,
        Collection $completed,
        Collection $pending,
        float $completionRate,
        ?string $groupBy = null,
        bool $includeSubtasks = false,
        ?array $dateRange = null,
        string $locale = 'en'
    ) {
        parent::__construct($locale);
        $this->tasks = $tasks;
        $this->overdue = $overdue;
        $this->completed = $completed;
        $this->pending = $pending;
        $this->completionRate = $completionRate;
        $this->groupBy = $groupBy;
        $this->includeSubtasks = $includeSubtasks;
        $this->dateRange = $dateRange;
        $this->locale = $locale;
    }

    protected function getTitle(): string
    {
        return $this->translate('reports.admin_tasks.title');
    }

    protected function getSheetNames(): array
    {
        $sheets = [
            $this->translate('reports.admin_tasks.summary'),
            $this->translate('reports.admin_tasks.detailed'),
            $this->translate('reports.admin_tasks.overdue'),
        ];

        // Add grouped sheets if grouping is requested
        if ($this->groupBy === 'lawyer') {
            $sheets[] = $this->translate('reports.admin_tasks.group_by_lawyer');
        } elseif ($this->groupBy === 'case') {
            $sheets[] = $this->translate('reports.admin_tasks.group_by_case');
        }

        return $sheets;
    }

    protected function getHeaders(): array
    {
        $baseHeaders = [
            'serial' => 'reports.admin_tasks.serial',
            'required_work' => 'reports.admin_tasks.required_work',
            'case_name' => 'reports.admin_tasks.case_name',
            'lawyer_name' => 'reports.admin_tasks.lawyer_name',
            'status' => 'reports.admin_tasks.status',
            'performer' => 'reports.admin_tasks.performer',
            'creation_date' => 'reports.admin_tasks.creation_date',
            'execution_date' => 'reports.admin_tasks.execution_date',
            'result' => 'reports.admin_tasks.result',
            'alert' => 'reports.admin_tasks.alert',
        ];

        if ($this->includeSubtasks) {
            $baseHeaders['subtasks_count'] = 'reports.admin_tasks.subtasks_count';
        }

        return $baseHeaders;
    }

    protected function getData(): array
    {
        $now = Carbon::now('Africa/Cairo');

        $data = [
            $this->translate('reports.admin_tasks.summary') => $this->prepareSummaryRows(),
            $this->translate('reports.admin_tasks.detailed') => $this->prepareTaskRows($this->tasks, $now),
            $this->translate('reports.admin_tasks.overdue') => $this->prepareTaskRows($this->overdue, $now),
        ];

        // Add grouped sheets if grouping is requested
        if ($this->groupBy === 'lawyer') {
            $data[$this->translate('reports.admin_tasks.group_by_lawyer')] = $this->prepareGroupedByLawyerRows($now);
        } elseif ($this->groupBy === 'case') {
            $data[$this->translate('reports.admin_tasks.group_by_case')] = $this->prepareGroupedByCaseRows($now);
        }

        return $data;
    }

    protected function prepareSummaryRows(): array
    {
        return [
            [
                'serial' => 1,
                'required_work' => $this->translate('reports.admin_tasks.total_tasks'),
                'case_name' => $this->tasks->count(),
                'lawyer_name' => '',
                'status' => '',
                'performer' => '',
                'creation_date' => '',
                'execution_date' => '',
                'result' => '',
                'alert' => '',
                ...($this->includeSubtasks ? ['subtasks_count' => ''] : []),
            ],
            [
                'serial' => 2,
                'required_work' => $this->translate('reports.admin_tasks.completed_tasks'),
                'case_name' => $this->completed->count(),
                'lawyer_name' => '',
                'status' => '',
                'performer' => '',
                'creation_date' => '',
                'execution_date' => '',
                'result' => '',
                'alert' => '',
                ...($this->includeSubtasks ? ['subtasks_count' => ''] : []),
            ],
            [
                'serial' => 3,
                'required_work' => $this->translate('reports.admin_tasks.pending_tasks'),
                'case_name' => $this->pending->count(),
                'lawyer_name' => '',
                'status' => '',
                'performer' => '',
                'creation_date' => '',
                'execution_date' => '',
                'result' => '',
                'alert' => '',
                ...($this->includeSubtasks ? ['subtasks_count' => ''] : []),
            ],
            [
                'serial' => 4,
                'required_work' => $this->translate('reports.admin_tasks.overdue_tasks'),
                'case_name' => $this->overdue->count(),
                'lawyer_name' => '',
                'status' => '',
                'performer' => '',
                'creation_date' => '',
                'execution_date' => '',
                'result' => '',
                'alert' => '',
                ...($this->includeSubtasks ? ['subtasks_count' => ''] : []),
            ],
            [
                'serial' => 5,
                'required_work' => $this->translate('reports.admin_tasks.completion_rate'),
                'case_name' => number_format($this->completionRate, 2) . '%',
                'lawyer_name' => '',
                'status' => '',
                'performer' => '',
                'creation_date' => '',
                'execution_date' => '',
                'result' => '',
                'alert' => '',
                ...($this->includeSubtasks ? ['subtasks_count' => ''] : []),
            ],
        ];
    }

    protected function prepareTaskRows(Collection $tasks, Carbon $now): array
    {
        return $tasks->map(function (AdminTask $task, int $index) use ($now) {
            $isOverdue = ($task->execution_date && $task->execution_date < $now && empty($task->result))
                || $task->alert;

            $row = [
                'serial' => $index + 1,
                'required_work' => $task->required_work ?? '—',
                'case_name' => $task->case?->matter_name_ar ?? $task->case?->matter_name_en ?? '—',
                'lawyer_name' => $task->lawyer?->lawyer_name_ar ?? $task->lawyer?->lawyer_name_en ?? '—',
                'status' => $task->status ?? '—',
                'performer' => $task->performer ?? '—',
                'creation_date' => $task->creation_date?->format('Y-m-d') ?? '—',
                'execution_date' => $task->execution_date?->format('Y-m-d') ?? '—',
                'result' => $task->result ?? '—',
                'alert' => $task->alert ? $this->translate('common.yes') : $this->translate('common.no'),
            ];

            if ($this->includeSubtasks) {
                $row['subtasks_count'] = $task->subtasks->count() ?? 0;
            }

            return $row;
        })->toArray();
    }

    protected function prepareGroupedByLawyerRows(Carbon $now): array
    {
        $grouped = $this->tasks->groupBy('lawyer_id');
        $rows = [];
        $globalIndex = 1;

        foreach ($grouped as $lawyerId => $lawyerTasks) {
            $lawyer = $lawyerTasks->first()?->lawyer;
            $lawyerName = $lawyer?->lawyer_name_ar ?? $lawyer?->lawyer_name_en ?? '—';

            // Add group header row
            $rows[] = [
                'serial' => '',
                'required_work' => $this->translate('reports.admin_tasks.lawyer') . ': ' . $lawyerName,
                'case_name' => '(' . $lawyerTasks->count() . ' ' . $this->translate('reports.admin_tasks.tasks') . ')',
                'lawyer_name' => '',
                'status' => '',
                'performer' => '',
                'creation_date' => '',
                'execution_date' => '',
                'result' => '',
                'alert' => '',
                ...($this->includeSubtasks ? ['subtasks_count' => ''] : []),
            ];

            // Add tasks for this lawyer
            foreach ($lawyerTasks as $task) {
                $isOverdue = ($task->execution_date && $task->execution_date < $now && empty($task->result))
                    || $task->alert;

                $row = [
                    'serial' => $globalIndex++,
                    'required_work' => $task->required_work ?? '—',
                    'case_name' => $task->case?->matter_name_ar ?? $task->case?->matter_name_en ?? '—',
                    'lawyer_name' => $task->lawyer?->lawyer_name_ar ?? $task->lawyer?->lawyer_name_en ?? '—',
                    'status' => $task->status ?? '—',
                    'performer' => $task->performer ?? '—',
                    'creation_date' => $task->creation_date?->format('Y-m-d') ?? '—',
                    'execution_date' => $task->execution_date?->format('Y-m-d') ?? '—',
                    'result' => $task->result ?? '—',
                    'alert' => $task->alert ? $this->translate('common.yes') : $this->translate('common.no'),
                ];

                if ($this->includeSubtasks) {
                    $row['subtasks_count'] = $task->subtasks->count() ?? 0;
                }

                $rows[] = $row;
            }

            // Add empty row between groups
            $rows[] = array_fill_keys(array_keys($this->getHeaders()), '');
        }

        return $rows;
    }

    protected function prepareGroupedByCaseRows(Carbon $now): array
    {
        $grouped = $this->tasks->groupBy('matter_id');
        $rows = [];
        $globalIndex = 1;

        foreach ($grouped as $caseId => $caseTasks) {
            $case = $caseTasks->first()?->case;
            $caseName = $case?->matter_name_ar ?? $case?->matter_name_en ?? '—';

            // Add group header row
            $rows[] = [
                'serial' => '',
                'required_work' => $this->translate('reports.admin_tasks.case') . ': ' . $caseName,
                'case_name' => '(' . $caseTasks->count() . ' ' . $this->translate('reports.admin_tasks.tasks') . ')',
                'lawyer_name' => '',
                'status' => '',
                'performer' => '',
                'creation_date' => '',
                'execution_date' => '',
                'result' => '',
                'alert' => '',
                ...($this->includeSubtasks ? ['subtasks_count' => ''] : []),
            ];

            // Add tasks for this case
            foreach ($caseTasks as $task) {
                $isOverdue = ($task->execution_date && $task->execution_date < $now && empty($task->result))
                    || $task->alert;

                $row = [
                    'serial' => $globalIndex++,
                    'required_work' => $task->required_work ?? '—',
                    'case_name' => $task->case?->matter_name_ar ?? $task->case?->matter_name_en ?? '—',
                    'lawyer_name' => $task->lawyer?->lawyer_name_ar ?? $task->lawyer?->lawyer_name_en ?? '—',
                    'status' => $task->status ?? '—',
                    'performer' => $task->performer ?? '—',
                    'creation_date' => $task->creation_date?->format('Y-m-d') ?? '—',
                    'execution_date' => $task->execution_date?->format('Y-m-d') ?? '—',
                    'result' => $task->result ?? '—',
                    'alert' => $task->alert ? $this->translate('common.yes') : $this->translate('common.no'),
                ];

                if ($this->includeSubtasks) {
                    $row['subtasks_count'] = $task->subtasks->count() ?? 0;
                }

                $rows[] = $row;
            }

            // Add empty row between groups
            $rows[] = array_fill_keys(array_keys($this->getHeaders()), '');
        }

        return $rows;
    }

    protected function getColumnWidths(): array
    {
        return [
            'serial' => 8,
            'required_work' => 35,
            'case_name' => 30,
            'lawyer_name' => 25,
            'status' => 15,
            'performer' => 20,
            'creation_date' => 15,
            'execution_date' => 15,
            'result' => 40,
            'alert' => 10,
            'subtasks_count' => 12,
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
            $isGroupHeader = empty($dataRow['serial']) && !empty($dataRow['required_work']) && str_contains($dataRow['required_work'] ?? '', ':');

            foreach ($headers as $key => $headerKey) {
                $cell = $this->getColumnLetter($col) . $row;
                $value = $dataRow[$key] ?? '';

                if ($isGroupHeader && $key === 'required_work') {
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
        foreach ($headers as $index => $headerKey) {
            $col = $index + 1;
            $columnLetter = $this->getColumnLetter($col);
            $headerKeyName = array_keys($headers)[$index];
            
            if (isset($columnWidths[$headerKeyName])) {
                $sheet->getColumnDimension($columnLetter)->setWidth($columnWidths[$headerKeyName]);
            } else {
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        }

        // Freeze header row
        if ($row > $headerRow + 1) {
            $sheet->freezePane('A' . ($headerRow + 1));
        }
    }
}

