<?php

namespace App\Http\Controllers\Api;

use App\Exports\HearingScheduleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\HearingScheduleReportRequest;
use App\Http\Requests\AdminTasksReportRequest;
use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Hearing;
use App\Support\Reports\DateRangeHelper;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    protected array $columnOrder = [
        'serial',
        'matter',
        'court',
        'clientRole',
        'opponentRole',
        'subject',
        'latestDecision',
        'evaluation',
        'financialProvision',
    ];

    public function clientCasesPdf(Request $request)
    {
        $payload = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'columns' => ['array'],
            'columns.*' => ['boolean'],
            'orientation' => ['nullable', 'in:portrait,landscape'],
            'status_filter' => ['nullable', 'string', 'in:الكل,سارية,منتهية,all,active,closed'],
        ]);

        $columns = $this->normalizeColumns($payload['columns'] ?? []);
        $client = Client::with('documentsLocation')->findOrFail($payload['client_id']);
        $statusFilter = $this->resolveStatusFilter($payload['status_filter'] ?? null);

        $casesQuery = CaseModel::with([
            'client',
            'court',
            'clientCapacity',
            'opponentCapacity',
            'latestHearing',
        ])
            ->where('client_id', $client->id);

        if ($statusFilter !== null) {
            $casesQuery->where('matter_status', $statusFilter);
        }

        $cases = $casesQuery
            ->orderBy('matter_name_ar')
            ->get();

        $rows = $cases->map(function (CaseModel $case, int $index) {
            return [
                'serial' => $index + 1,
                'matter' => $case->matter_name_ar ?? $case->matter_name_en ?? '—',
                'court' => $case->court?->court_name_ar
                    ?? $case->court?->court_name_en
                    ?? $case->matter_court_text
                    ?? '—',
                'clientRole' => $this->joinParts([
                    $case->client_in_case_name ?? $case->client?->client_name_ar ?? $case->client?->client_name_en,
                    $case->clientCapacity?->label_ar ?? $case->clientCapacity?->label_en ?? $case->client_capacity_note,
                ]),
                'opponentRole' => $this->joinParts([
                    $case->opponent_in_case_name,
                    $case->opponentCapacity?->label_ar ?? $case->opponentCapacity?->label_en ?? $case->opponent_capacity_note,
                ]),
                'subject' => $case->matter_description ?? '—',
                'latestDecision' => $case->latestHearing?->decision
                    ?? $case->current_status
                    ?? '—',
                'evaluation' => $case->matter_evaluation ?? '—',
                'financialProvision' => $case->financial_provision ?? '—',
            ];
        });

        $visibleColumnLabels = collect($this->columnLabels())
            ->filter(fn ($label, $key) => $columns[$key] ?? false)
            ->toArray();

        $orientation = $payload['orientation'] ?? 'portrait';

        $firmLogoPath = public_path('uploads/logos/logo.png');
        $firmLogoPath = file_exists($firmLogoPath) ? $firmLogoPath : null;

        $clientLogoPath = null;
        if (!empty($client->logo)) {
            $normalizedLogo = ltrim($client->logo, '/');
            $candidatePath = public_path($normalizedLogo);
            if (file_exists($candidatePath)) {
                $clientLogoPath = $candidatePath;
            }
        }

        $pdf = SnappyPdf::loadView('reports.client_cases_pdf', [
            'client' => $client,
            'rows' => $rows,
            'columns' => $columns,
            'columnLabels' => $visibleColumnLabels,
            'generatedAt' => now('Africa/Cairo'),
            'totalCases' => $rows->count(),
        ])
        ->setPaper('a4', $orientation === 'landscape' ? 'landscape' : 'portrait')
        ->setOption('header-html', view('reports.partials.client_cases_header', [
            'clientName' => $client->client_name_ar ?? $client->client_name_en,
            'firmLogoPath' => $firmLogoPath,
            'clientLogoPath' => $clientLogoPath,
        ])->render())
        ->setOption('margin-top', '40mm')
        ->setOption('margin-bottom', '25mm')
        ->setOption('header-spacing', 6)
        ->setOption('footer-left', 'Page [page] of [toPage]')
        ->setOption('footer-font-size', 9)
        ->setOption('footer-spacing', 5);

        $fileName = Str::slug($client->client_name_en ?? $client->client_name_ar ?? 'client-report') . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    protected function normalizeColumns(array $input): array
    {
        $columns = [];
        foreach ($this->columnOrder as $key) {
            $columns[$key] = array_key_exists($key, $input) ? (bool) $input[$key] : true;
        }

        return $columns;
    }

    protected function joinParts(array $parts): string
    {
        $filtered = array_values(array_filter(array_map(function ($value) {
            return $value !== null && $value !== '' ? trim($value) : null;
        }, $parts)));

        return !empty($filtered) ? implode(' - ', $filtered) : '—';
    }

    protected function columnLabels(): array
    {
        return [
            'serial' => 'م/#',
            'matter' => 'رقم الدعوى',
            'court' => 'المحكمة',
            'clientRole' => 'الموكل وصفته',
            'opponentRole' => 'الخصم وصفته',
            'subject' => 'موضوع الدعوى',
            'latestDecision' => 'آخر موقف',
            'evaluation' => 'التقييم',
            'financialProvision' => 'المخصص المالي',
        ];
    }

    protected function resolveStatusFilter(?string $value): ?string
    {
        if ($value === null || in_array($value, ['الكل', 'all'], true)) {
            return null;
        }

        return match ($value) {
            'سارية', 'active' => 'سارية',
            'منتهية', 'closed' => 'منتهية',
            default => null,
        };
    }

    /**
     * Generate Hearing Schedule Report as PDF.
     */
    public function hearingSchedulePdf(HearingScheduleReportRequest $request)
    {
        $validated = $request->validated();

        // Resolve date range
        $dateRange = DateRangeHelper::resolveDateRange(
            $validated['date_range_type'] ?? null,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        // Build query
        $query = Hearing::with([
            'case.client',
            'case.court',
            'lawyer',
        ]);

        // Apply date range filter
        if ($dateRange) {
            $query->whereBetween('date', [
                $dateRange['start'],
                $dateRange['end'],
            ]);
        }

        // Apply filters
        if (!empty($validated['court_id'])) {
            $query->whereHas('case', function ($q) use ($validated) {
                $q->where('court_id', $validated['court_id']);
            });
        }

        if (!empty($validated['case_id'])) {
            $query->where('matter_id', $validated['case_id']);
        }

        if (!empty($validated['lawyer_id'])) {
            $query->where('lawyer_id', $validated['lawyer_id']);
        }

        $caseStatusFilter = $this->resolveStatusFilter($validated['case_status'] ?? null);
        if ($caseStatusFilter !== null) {
            $query->whereHas('case', function ($q) use ($caseStatusFilter) {
                $q->where('matter_status', $caseStatusFilter);
            });
        }

        // Get hearings
        $hearings = $query->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Categorize hearings
        $now = Carbon::now('Africa/Cairo');
        $upcoming = $hearings->filter(fn ($h) => $h->date >= $now->copy()->startOfDay());
        $past = $hearings->filter(fn ($h) => $h->date < $now->copy()->startOfDay());
        $overdue = $hearings->filter(function ($h) use ($now) {
            return $h->date < $now->copy()->startOfDay() 
                && (empty($h->decision) && empty($h->short_decision));
        });

        // Prepare rows for display
        $rows = $hearings->map(function (Hearing $hearing, int $index) use ($now) {
            $isOverdue = $hearing->date < $now->copy()->startOfDay() 
                && (empty($hearing->decision) && empty($hearing->short_decision));
            $isUpcoming = $hearing->date >= $now->copy()->startOfDay();

            return [
                'serial' => $index + 1,
                'date' => $hearing->date->format('Y-m-d'),
                'case_name' => $hearing->case?->matter_name_ar ?? $hearing->case?->matter_name_en ?? '—',
                'client_name' => $hearing->case?->client?->client_name_ar ?? $hearing->case?->client?->client_name_en ?? '—',
                'court' => $hearing->case?->court?->court_name_ar
                    ?? $hearing->case?->court?->court_name_en
                    ?? $hearing->court
                    ?? '—',
                'procedure' => $hearing->procedure ?? '—',
                'decision' => $hearing->short_decision ?? $hearing->decision ?? '—',
                'next_hearing' => $hearing->next_hearing?->format('Y-m-d') ?? '—',
                'lawyer' => $hearing->lawyer?->lawyer_name_ar ?? $hearing->lawyer?->lawyer_name_en ?? '—',
                'status' => $isOverdue ? 'overdue' : ($isUpcoming ? 'upcoming' : 'past'),
            ];
        });

        $orientation = $validated['orientation'] ?? 'portrait';
        $viewType = $validated['view_type'] ?? 'list';

        // Use base PDF layout
        $template = $viewType === 'calendar' 
            ? 'reports.hearing_schedule_calendar_pdf' 
            : 'reports.hearing_schedule_pdf';

        $pdf = SnappyPdf::loadView($template, [
            'hearings' => $hearings,
            'rows' => $rows,
            'upcoming' => $upcoming,
            'past' => $past,
            'overdue' => $overdue,
            'dateRange' => $dateRange,
            'filters' => [
                'court_id' => $validated['court_id'] ?? null,
                'case_id' => $validated['case_id'] ?? null,
                'lawyer_id' => $validated['lawyer_id'] ?? null,
                'case_status' => $validated['case_status'] ?? null,
            ],
            'generatedAt' => now('Africa/Cairo'),
            'totalHearings' => $hearings->count(),
        ])
        ->setPaper('a4', $orientation === 'landscape' ? 'landscape' : 'portrait')
        ->setOption('margin-top', '20mm')
        ->setOption('margin-bottom', '20mm')
        ->setOption('footer-left', 'Page [page] of [toPage]')
        ->setOption('footer-font-size', 9)
        ->setOption('footer-spacing', 5);

        $fileName = 'hearing-schedule-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Generate Hearing Schedule Report as Excel.
     */
    public function hearingScheduleExcel(HearingScheduleReportRequest $request)
    {
        $validated = $request->validated();

        // Resolve date range (same logic as PDF)
        $dateRange = DateRangeHelper::resolveDateRange(
            $validated['date_range_type'] ?? null,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        // Build query (same logic as PDF)
        $query = Hearing::with([
            'case.client',
            'case.court',
            'lawyer',
        ]);

        if ($dateRange) {
            $query->whereBetween('date', [
                $dateRange['start'],
                $dateRange['end'],
            ]);
        }

        if (!empty($validated['court_id'])) {
            $query->whereHas('case', function ($q) use ($validated) {
                $q->where('court_id', $validated['court_id']);
            });
        }

        if (!empty($validated['case_id'])) {
            $query->where('matter_id', $validated['case_id']);
        }

        if (!empty($validated['lawyer_id'])) {
            $query->where('lawyer_id', $validated['lawyer_id']);
        }

        $caseStatusFilter = $this->resolveStatusFilter($validated['case_status'] ?? null);
        if ($caseStatusFilter !== null) {
            $query->whereHas('case', function ($q) use ($caseStatusFilter) {
                $q->where('matter_status', $caseStatusFilter);
            });
        }

        $hearings = $query->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Categorize hearings (same logic as PDF)
        $now = Carbon::now('Africa/Cairo');
        $upcoming = $hearings->filter(fn ($h) => $h->date >= $now->copy()->startOfDay());
        $past = $hearings->filter(fn ($h) => $h->date < $now->copy()->startOfDay());
        $overdue = $hearings->filter(function ($h) use ($now) {
            return $h->date < $now->copy()->startOfDay() 
                && (empty($h->decision) && empty($h->short_decision));
        });

        // Generate Excel export
        $locale = App::getLocale();
        $export = new HearingScheduleExport(
            $hearings,
            $upcoming,
            $past,
            $overdue,
            $dateRange,
            $locale
        );

        return $export->export();
    }

    /**
     * Generate Administrative Tasks Report as PDF.
     */
    public function adminTasksPdf(AdminTasksReportRequest $request)
    {
        $validated = $request->validated();

        // Resolve date range if provided
        $dateRange = null;
        if (!empty($validated['date_range_type'])) {
            $dateRange = DateRangeHelper::resolveDateRange(
                $validated['date_range_type'],
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );
        }

        // Build query
        $query = AdminTask::with([
            'case.client',
            'lawyer',
            'subtasks',
        ]);

        // Apply filters
        if (!empty($validated['lawyer_id'])) {
            $query->where('lawyer_id', $validated['lawyer_id']);
        }

        if (!empty($validated['case_id'])) {
            $query->where('matter_id', $validated['case_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Apply date range filter if provided
        if ($dateRange) {
            $query->whereBetween('execution_date', [
                $dateRange['start'],
                $dateRange['end'],
            ]);
        }

        // Filter overdue tasks if requested
        if (!empty($validated['show_overdue'])) {
            $now = Carbon::now('Africa/Cairo');
            $query->where(function ($q) use ($now) {
                $q->where('execution_date', '<', $now)
                    ->whereNull('result')
                    ->orWhere('alert', true);
            });
        }

        // Get tasks
        $tasks = $query->orderBy('execution_date', 'asc')
            ->orderBy('creation_date', 'desc')
            ->get();

        // Categorize tasks
        $now = Carbon::now('Africa/Cairo');
        $overdue = $tasks->filter(function ($task) use ($now) {
            return ($task->execution_date && $task->execution_date < $now && empty($task->result))
                || $task->alert;
        });

        $completed = $tasks->filter(fn ($task) => !empty($task->result));
        $pending = $tasks->filter(fn ($task) => empty($task->result) && (!$task->execution_date || $task->execution_date >= $now));

        // Calculate completion rates
        $completionRate = $tasks->count() > 0 
            ? ($completed->count() / $tasks->count()) * 100 
            : 0;

        // Group by if requested
        $groupBy = $validated['group_by'] ?? null;
        $groupedData = null;
        if ($groupBy === 'lawyer' || $groupBy === 'case') {
            $groupedData = $tasks->groupBy(function ($task) use ($groupBy) {
                return $groupBy === 'lawyer' 
                    ? ($task->lawyer_id ?? 'unassigned')
                    : ($task->matter_id ?? 'uncategorized');
            });
        }

        // Prepare rows for display
        $rows = $tasks->map(function (AdminTask $task, int $index) use ($now) {
            $isOverdue = ($task->execution_date && $task->execution_date < $now && empty($task->result))
                || $task->alert;

            return [
                'serial' => $index + 1,
                'required_work' => $task->required_work ?? '—',
                'case_name' => $task->case?->matter_name_ar ?? $task->case?->matter_name_en ?? '—',
                'lawyer_name' => $task->lawyer?->lawyer_name_ar ?? $task->lawyer?->lawyer_name_en ?? '—',
                'status' => $task->status ?? '—',
                'performer' => $task->performer ?? '—',
                'creation_date' => $task->creation_date?->format('Y-m-d') ?? '—',
                'execution_date' => $task->execution_date?->format('Y-m-d') ?? '—',
                'result' => $task->result ?? '—',
                'alert' => $task->alert,
                'status_badge' => $isOverdue ? 'overdue' : (!empty($task->result) ? 'completed' : 'pending'),
            ];
        });

        $orientation = $validated['orientation'] ?? 'portrait';
        $includeSubtasks = $validated['include_subtasks'] ?? false;

        $pdf = SnappyPdf::loadView('reports.admin_tasks_pdf', [
            'tasks' => $tasks,
            'rows' => $rows,
            'overdue' => $overdue,
            'completed' => $completed,
            'pending' => $pending,
            'completionRate' => $completionRate,
            'groupedData' => $groupedData,
            'groupBy' => $groupBy,
            'includeSubtasks' => $includeSubtasks,
            'dateRange' => $dateRange,
            'filters' => [
                'lawyer_id' => $validated['lawyer_id'] ?? null,
                'case_id' => $validated['case_id'] ?? null,
                'status' => $validated['status'] ?? null,
            ],
            'generatedAt' => now('Africa/Cairo'),
            'totalTasks' => $tasks->count(),
        ])
        ->setPaper('a4', $orientation === 'landscape' ? 'landscape' : 'portrait')
        ->setOption('margin-top', '20mm')
        ->setOption('margin-bottom', '20mm')
        ->setOption('footer-left', 'Page [page] of [toPage]')
        ->setOption('footer-font-size', 9)
        ->setOption('footer-spacing', 5);

        $fileName = 'admin-tasks-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Generate Administrative Tasks Report as Excel.
     */
    public function adminTasksExcel(AdminTasksReportRequest $request)
    {
        // TODO: Implement Excel export using AdminTasksExport class
        return response()->json(['message' => 'Excel export not yet implemented'], 501);
    }
}

