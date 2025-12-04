<?php

namespace App\Http\Controllers\Api;

use App\Exports\AdminTasksExport;
use App\Exports\DocumentInventoryExport;
use App\Exports\HearingScheduleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\HearingScheduleReportRequest;
use App\Http\Requests\AdminTasksReportRequest;
use App\Http\Requests\CaseStatusDashboardReportRequest;
use App\Http\Requests\DocumentInventoryReportRequest;
use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\ClientDocument;
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
     * Normalize the admin task status filter value.
     *
     * Treats "all"/"الكل" (or null/empty) as "no filter".
     */
    protected function normalizeTaskStatusFilter(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || in_array($trimmed, ['all', 'الكل'], true)) {
            return null;
        }

        return $trimmed;
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
        try {
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
        } catch (\Exception $e) {
            \Log::error('Hearing Schedule Excel Export Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to generate Excel export',
                'message' => config('app.debug') ? $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() : 'An error occurred while generating the report.',
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
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

        // Normalize status filter (treat "all"/"الكل" as no filter)
        $statusFilter = $this->normalizeTaskStatusFilter($validated['status'] ?? null);

        // Apply filters
        if (!empty($validated['lawyer_id'])) {
            $query->where('lawyer_id', $validated['lawyer_id']);
        }

        if (!empty($validated['case_id'])) {
            $query->where('matter_id', $validated['case_id']);
        }

        if ($statusFilter !== null) {
            $query->where('status', $statusFilter);
        }

        // Apply date range filter if provided (match by creation_date OR execution_date)
        if ($dateRange) {
            $query->where(function ($q) use ($dateRange) {
                $q->whereBetween('creation_date', [
                    $dateRange['start'],
                    $dateRange['end'],
                ])->orWhereBetween('execution_date', [
                    $dateRange['start'],
                    $dateRange['end'],
                ]);
            });
        }

        // Filter overdue tasks if requested
        if (!empty($validated['show_overdue'])) {
            $today = Carbon::now('Africa/Cairo')->startOfDay();
            $query->where(function ($q) use ($today) {
                // Overdue = has a due date strictly before today and no result yet
                $q->whereNotNull('execution_date')
                    ->where('execution_date', '<', $today)
                    ->whereNull('result');
            });
        }

        // Get tasks
        $tasks = $query->orderBy('execution_date', 'asc')
            ->orderBy('creation_date', 'desc')
            ->get();

        // Categorize tasks
        $today = Carbon::now('Africa/Cairo')->startOfDay();
        $overdue = $tasks->filter(function ($task) use ($today) {
            return $task->execution_date
                && $task->execution_date < $today
                && empty($task->result);
        });

        $completed = $tasks->filter(fn ($task) => !empty($task->result));
        $pending = $tasks->filter(function ($task) use ($today) {
            // Pending = no result and due date is today or in the future (or not set)
            if (!empty($task->result)) {
                return false;
            }

            if ($task->execution_date) {
                return $task->execution_date >= $today;
            }

            return true;
        });

        // Calculate completion rates
        $completionRate = $tasks->count() > 0 
            ? ($completed->count() / $tasks->count()) * 100 
            : 0;

        // Group by if requested
        $groupBy = $validated['group_by'] ?? null;
        $groupedData = null;
        if (in_array($groupBy, ['lawyer', 'case', 'court'], true)) {
            $groupedData = $tasks->groupBy(function ($task) use ($groupBy) {
                return match ($groupBy) {
                    'lawyer' => $task->lawyer?->id ?? 'unassigned',
                    'case' => $task->case?->id ?? 'uncategorized',
                    'court' => $task->case?->court?->id ?? 'uncourted',
                };
            });
        }

        // Prepare rows for display (domain‑friendly columns)
        $rows = $tasks->map(function (AdminTask $task, int $index) use ($today) {
            $case = $task->case;
            $isOverdue = $task->execution_date
                && $task->execution_date < $today
                && empty($task->result);

            // Client print name + capacity
            $clientName = $case?->client?->client_print_name
                ?? $case?->client?->client_name_ar
                ?? $case?->client?->client_name_en
                ?? null;
            $clientCapacity = $case?->clientCapacity?->label_ar
                ?? $case?->clientCapacity?->label_en
                ?? $case?->client_capacity_note
                ?? null;

            // Opponent print name + capacity (using legacy single opponent mirror)
            $opponentName = $case?->opponent?->opponent_print_name
                ?? $case?->opponent?->opponent_name_ar
                ?? $case?->opponent?->opponent_name_en
                ?? null;
            $opponentCapacity = $case?->opponentCapacity?->label_ar
                ?? $case?->opponentCapacity?->label_en
                ?? $case?->opponent_capacity_note
                ?? null;

            $clientRole = trim(collect([$clientName, $clientCapacity])->filter()->implode(' - ')) ?: '—';
            $opponentRole = trim(collect([$opponentName, $opponentCapacity])->filter()->implode(' - ')) ?: '—';

            // Status + age in days (relative to today, based on execution_date if present, otherwise creation_date)
            $status = $task->status ?? '—';
            $ageLabel = null;
            $referenceDate = $task->execution_date ?: $task->creation_date;
            if ($referenceDate) {
                $isFuture = $referenceDate->greaterThanOrEqualTo($today);
                $days = $referenceDate->diffInDays($today);
                if ($days === 0) {
                    $ageLabel = 'اليوم';
                } else {
                    $prefix = $isFuture ? 'بعد' : 'من';
                    $ageLabel = $prefix . ' ' . $days . ' يوم';
                }
            }

            return [
                'serial' => $index + 1,
                'case_name' => $case?->matter_name_ar ?? $case?->matter_name_en ?? '—',
                'lawyer_name' => $task->lawyer?->lawyer_name_ar
                    ?? $task->lawyer?->lawyer_name_en
                    ?? '—',
                'court' => $case?->court?->court_name_ar
                    ?? $case?->court?->court_name_en
                    ?? $case?->matter_court_text
                    ?? $task->court
                    ?? '—',
                'circuit' => $case?->circuitName?->label_ar
                    ?? $case?->circuitName?->label_en
                    ?? $case?->matter_circuit_legacy
                    ?? $task->circuit
                    ?? '—',
                'client_role' => $clientRole,
                'opponent_role' => $opponentRole,
                'latest_decision' => $case?->latest_decision ?? $case?->current_status ?? '—',
                'required_work' => $task->required_work ?? '—',
                'status' => $status,
                'age_label' => $ageLabel,
                'last_follow_up' => $task->last_follow_up?->format('Y-m-d') ?? '—',
                'result' => $task->result ?? '—',
                'status_badge' => $isOverdue ? 'overdue' : (!empty($task->result) ? 'completed' : 'pending'),
            ];
        });

        $orientation = $validated['orientation'] ?? 'portrait';
        $includeSubtasks = $validated['include_subtasks'] ?? false;

        // For court grouping in the PDF view, provide rows grouped by court name
        $groupedRows = null;
        if ($groupBy === 'court') {
            $groupedRows = $rows->groupBy('court');
        }

        $pdf = SnappyPdf::loadView('reports.admin_tasks_pdf', [
            'tasks' => $tasks,
            'rows' => $rows,
            'groupedRows' => $groupedRows,
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
     *
     * Creates a multi-sheet Excel export containing:
     * - Summary sheet with statistics (total, completed, pending, overdue, completion rate)
     * - Detailed sheet with all tasks
     * - Overdue tasks sheet
     * - Optional grouped sheets (by lawyer or by case, if requested)
     *
     * @param AdminTasksReportRequest $request Validated request with filters
     * @return \Symfony\Component\HttpFoundation\StreamedResponse Excel file download
     * @throws \Exception If Excel generation fails
     */
    public function adminTasksExcel(AdminTasksReportRequest $request)
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

        // Build query (same logic as PDF)
        $query = AdminTask::with([
            'case.client',
            'lawyer',
            'subtasks',
        ]);

        // Normalize status filter (treat "all"/"الكل" as no filter)
        $statusFilter = $this->normalizeTaskStatusFilter($validated['status'] ?? null);

        // Apply filters
        if (!empty($validated['lawyer_id'])) {
            $query->where('lawyer_id', $validated['lawyer_id']);
        }

        if (!empty($validated['case_id'])) {
            $query->where('matter_id', $validated['case_id']);
        }

        if ($statusFilter !== null) {
            $query->where('status', $statusFilter);
        }

        // Apply date range filter if provided (match by creation_date OR execution_date)
        if ($dateRange) {
            $query->where(function ($q) use ($dateRange) {
                $q->whereBetween('creation_date', [
                    $dateRange['start'],
                    $dateRange['end'],
                ])->orWhereBetween('execution_date', [
                    $dateRange['start'],
                    $dateRange['end'],
                ]);
            });
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

        // Get grouping option
        $groupBy = $validated['group_by'] ?? null;
        $includeSubtasks = $validated['include_subtasks'] ?? false;

        // Generate Excel export
        $locale = App::getLocale();
        $export = new AdminTasksExport(
            $tasks,
            $overdue,
            $completed,
            $pending,
            $completionRate,
            $groupBy,
            $includeSubtasks,
            $dateRange,
            $locale
        );

        return $export->export();
    }

    /**
     * Generate Case Status Dashboard Report as PDF.
     */
    public function caseStatusDashboardPdf(CaseStatusDashboardReportRequest $request)
    {
        $validated = $request->validated();

        // Build query
        $query = CaseModel::with([
            'client',
            'court',
            'matterCategory',
            'latestHearing',
            'adminTasks' => function ($q) {
                $q->orderBy('updated_at', 'desc')->limit(1);
            },
        ]);

        // Apply filters
        $statusFilter = $this->resolveStatusFilter($validated['status'] ?? null);
        if ($statusFilter !== null) {
            $query->where('matter_status', $statusFilter);
        }

        if (!empty($validated['category_id'])) {
            $query->where('matter_category_id', $validated['category_id']);
        }

        if (!empty($validated['court_id'])) {
            $query->where('court_id', $validated['court_id']);
        }

        if (!empty($validated['lawyer_id'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('lawyer_a', $validated['lawyer_id'])
                    ->orWhere('lawyer_b', $validated['lawyer_id']);
            });
        }

        $cases = $query->get();

        // Helper function to build base query with filters
        $buildStatsQuery = function () use ($statusFilter, $validated) {
            $q = CaseModel::query();
            if ($statusFilter !== null) {
                $q->where('matter_status', $statusFilter);
            }
            if (!empty($validated['category_id'])) {
                $q->where('matter_category_id', $validated['category_id']);
            }
            if (!empty($validated['court_id'])) {
                $q->where('court_id', $validated['court_id']);
            }
            if (!empty($validated['lawyer_id'])) {
                $q->where(function ($q2) use ($validated) {
                    $q2->where('lawyer_a', $validated['lawyer_id'])
                        ->orWhere('lawyer_b', $validated['lawyer_id']);
                });
            }
            return $q;
        };

        // Calculate summary statistics (respecting filters)
        $baseQuery = $buildStatsQuery();
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('matter_status', 'سارية')->count(),
            'closed' => (clone $baseQuery)->where('matter_status', 'منتهية')->count(),
            'by_category' => (clone $baseQuery)
                ->selectRaw('matter_category_id, COUNT(*) as count')
                ->whereNotNull('matter_category_id')
                ->groupBy('matter_category_id')
                ->with('matterCategory')
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->matterCategory?->label_ar ?? $item->matterCategory?->label_en ?? 'غير محدد',
                        'count' => $item->count,
                    ];
                }),
            'by_court' => (clone $baseQuery)
                ->selectRaw('court_id, COUNT(*) as count')
                ->whereNotNull('court_id')
                ->groupBy('court_id')
                ->with('court')
                ->get()
                ->map(function ($item) {
                    return [
                        'court' => $item->court?->court_name_ar ?? $item->court?->court_name_en ?? 'غير محدد',
                        'count' => $item->count,
                    ];
                }),
        ];

        // Helper to apply filters to a query
        $applyFiltersToQuery = function ($query) use ($statusFilter, $validated) {
            if ($statusFilter !== null) {
                $query->where('matter_status', $statusFilter);
            }
            if (!empty($validated['category_id'])) {
                $query->where('matter_category_id', $validated['category_id']);
            }
            if (!empty($validated['court_id'])) {
                $query->where('court_id', $validated['court_id']);
            }
            if (!empty($validated['lawyer_id'])) {
                $query->where(function ($q) use ($validated) {
                    $q->where('lawyer_a', $validated['lawyer_id'])
                        ->orWhere('lawyer_b', $validated['lawyer_id']);
                });
            }
            return $query;
        };

        // Cases requiring attention
        $attentionRequired = collect();
        if ($validated['show_attention_required'] ?? true) {
            $now = Carbon::now('Africa/Cairo');

            // Cases with overdue tasks
            $casesWithOverdueTasks = $applyFiltersToQuery(CaseModel::whereHas('adminTasks', function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    $q2->where('execution_date', '<', $now)
                        ->whereNull('result')
                        ->orWhere('alert', true);
                });
            }))
                ->with(['client', 'latestHearing'])
                ->limit(30)
                ->get()
                ->map(function ($case) {
                    return [
                        'case' => $case->matter_name_ar ?? $case->matter_name_en,
                        'client' => $case->client?->client_name_ar ?? $case->client?->client_name_en,
                        'reason' => 'overdue_task',
                    ];
                });

            // Cases with missing critical data
            $casesWithMissingData = $applyFiltersToQuery(CaseModel::where(function ($q) {
                $q->whereNull('matter_description')
                    ->orWhereNull('matter_start_date')
                    ->orWhereNull('client_id');
            }))
                ->with(['client'])
                ->limit(30)
                ->get()
                ->map(function ($case) {
                    return [
                        'case' => $case->matter_name_ar ?? $case->matter_name_en,
                        'client' => $case->client?->client_name_ar ?? $case->client?->client_name_en,
                        'reason' => 'missing_data',
                    ];
                });

            // Cases with upcoming hearings (within 7 days)
            $casesWithUpcomingHearings = $applyFiltersToQuery(CaseModel::whereHas('hearings', function ($q) use ($now) {
                $q->where('date', '>=', $now)
                    ->where('date', '<=', $now->copy()->addDays(7));
            }))
                ->with(['client', 'latestHearing'])
                ->limit(30)
                ->get()
                ->map(function ($case) {
                    return [
                        'case' => $case->matter_name_ar ?? $case->matter_name_en,
                        'client' => $case->client?->client_name_ar ?? $case->client?->client_name_en,
                        'reason' => 'upcoming_hearing',
                    ];
                });

            $attentionRequired = collect()
                ->concat($casesWithOverdueTasks)
                ->concat($casesWithMissingData)
                ->concat($casesWithUpcomingHearings)
                ->unique(function ($item) {
                    return $item['case'];
                });
        }

        // Recent activity (limit to top 20)
        $recentActivity = collect();
        if ($validated['show_recent_activity'] ?? true) {
            $recentActivity = $applyFiltersToQuery(CaseModel::with([
                'client',
                'latestHearing',
                'adminTasks' => function ($q) {
                    $q->orderBy('updated_at', 'desc')->limit(1);
                },
            ]))
                ->get()
                ->map(function ($case) {
                    $lastHearing = $case->latestHearing?->date;
                    $lastTask = $case->adminTasks->first()?->updated_at?->toDateString();

                    return [
                        'case' => $case->matter_name_ar ?? $case->matter_name_en,
                        'client' => $case->client?->client_name_ar ?? $case->client?->client_name_en,
                        'last_hearing' => $lastHearing?->format('Y-m-d'),
                        'last_task' => $lastTask,
                        'last_activity' => max($lastHearing?->format('Y-m-d'), $lastTask ?? ''),
                    ];
                })
                ->filter(fn ($item) => !empty($item['last_activity']))
                ->sortByDesc('last_activity')
                ->take(20);
        }

        $orientation = $validated['orientation'] ?? 'portrait';

        $pdf = SnappyPdf::loadView('reports.case_status_dashboard_pdf', [
            'cases' => $cases,
            'stats' => $stats,
            'attentionRequired' => $attentionRequired,
            'recentActivity' => $recentActivity,
            'filters' => $validated,
            'generatedAt' => now('Africa/Cairo'),
        ])
        ->setPaper('a4', $orientation === 'landscape' ? 'landscape' : 'portrait')
        ->setOption('margin-top', '15mm')
        ->setOption('margin-bottom', '15mm')
        ->setOption('footer-left', 'Page [page] of [toPage]')
        ->setOption('footer-font-size', 9)
        ->setOption('footer-spacing', 5);

        $fileName = 'case-status-dashboard-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Generate Document Inventory Report as PDF.
     */
    public function documentInventoryPdf(DocumentInventoryReportRequest $request)
    {
        $validated = $request->validated();

        // Build query
        $query = ClientDocument::with(['client', 'case']);

        // Apply filters
        if (!empty($validated['client_id'])) {
            $query->where('client_id', $validated['client_id']);
        }

        if (!empty($validated['case_id'])) {
            $query->where('matter_id', $validated['case_id']);
        }

        if (!empty($validated['document_type'])) {
            $query->where('document_type', $validated['document_type']);
        }

        if (!empty($validated['location'])) {
            $query->where('document_location', 'like', '%' . $validated['location'] . '%');
        }

        if (!empty($validated['storage_type']) && $validated['storage_type'] !== 'all') {
            $query->where('document_storage_type', $validated['storage_type']);
        }

        $documents = $query->orderBy('client_id', 'asc')
            ->orderBy('matter_id', 'asc')
            ->orderBy('deposit_date', 'desc')
            ->get();

        // Calculate summary statistics
        $stats = [
            'total' => ClientDocument::count(),
            'physical' => ClientDocument::where('document_storage_type', 'physical')->count(),
            'digital' => ClientDocument::where('document_storage_type', 'digital')->count(),
            'both' => ClientDocument::where('document_storage_type', 'both')->count(),
            'by_location' => ClientDocument::selectRaw('document_location, COUNT(*) as count')
                ->whereNotNull('document_location')
                ->groupBy('document_location')
                ->orderBy('count', 'desc')
                ->get(),
            'by_client' => ClientDocument::selectRaw('client_id, COUNT(*) as total_documents')
                ->groupBy('client_id')
                ->with('client')
                ->get()
                ->map(function ($item) {
                    return [
                        'client' => $item->client?->client_name_ar ?? $item->client?->client_name_en ?? 'غير محدد',
                        'count' => $item->total_documents,
                    ];
                }),
        ];

        // Missing documents (cases without documents)
        $missingDocuments = collect();
        if ($validated['show_missing'] ?? false) {
            $missingDocuments = CaseModel::whereDoesntHave('documents')
                ->whereNotNull('matter_description')
                ->with('client')
                ->limit(50)
                ->get()
                ->map(function ($case) {
                    return [
                        'case' => $case->matter_name_ar ?? $case->matter_name_en,
                        'client' => $case->client?->client_name_ar ?? $case->client?->client_name_en,
                    ];
                });
        }

        $orientation = $validated['orientation'] ?? 'portrait';
        $groupBy = $validated['group_by'] ?? null;

        $pdf = SnappyPdf::loadView('reports.document_inventory_pdf', [
            'documents' => $documents,
            'stats' => $stats,
            'missingDocuments' => $missingDocuments,
            'filters' => $validated,
            'groupBy' => $groupBy,
            'generatedAt' => now('Africa/Cairo'),
        ])
        ->setPaper('a4', $orientation === 'landscape' ? 'landscape' : 'portrait')
        ->setOption('margin-top', '20mm')
        ->setOption('margin-bottom', '20mm')
        ->setOption('footer-left', 'Page [page] of [toPage]')
        ->setOption('footer-font-size', 9)
        ->setOption('footer-spacing', 5);

        $fileName = 'document-inventory-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Generate Document Inventory Report as Excel.
     */
    /**
     * Generate Document Inventory Report as Excel.
     *
     * Creates a multi-sheet Excel export containing:
     * - Summary sheet with document counts by storage type
     * - Detailed inventory sheet with all documents
     * - By Location sheet (grouped by physical location)
     * - By Client sheet (grouped by client)
     * - By Case sheet (grouped by case)
     * - Missing Documents sheet (if requested and applicable)
     *
     * @param DocumentInventoryReportRequest $request Validated request with filters
     * @return \Symfony\Component\HttpFoundation\StreamedResponse Excel file download
     * @throws \Exception If Excel generation fails
     */
    public function documentInventoryExcel(DocumentInventoryReportRequest $request)
    {
        $validated = $request->validated();

        // Build query (same logic as PDF)
        $query = ClientDocument::with(['client', 'case']);

        // Apply filters
        if (!empty($validated['client_id'])) {
            $query->where('client_id', $validated['client_id']);
        }

        if (!empty($validated['case_id'])) {
            $query->where('matter_id', $validated['case_id']);
        }

        if (!empty($validated['document_type'])) {
            $query->where('document_type', $validated['document_type']);
        }

        if (!empty($validated['location'])) {
            $query->where('document_location', 'like', '%' . $validated['location'] . '%');
        }

        if (!empty($validated['storage_type']) && $validated['storage_type'] !== 'all') {
            $query->where('document_storage_type', $validated['storage_type']);
        }

        $documents = $query->orderBy('client_id', 'asc')
            ->orderBy('matter_id', 'asc')
            ->orderBy('deposit_date', 'desc')
            ->get();

        // Calculate summary statistics
        $stats = [
            'total' => ClientDocument::count(),
            'physical' => ClientDocument::where('document_storage_type', 'physical')->count(),
            'digital' => ClientDocument::where('document_storage_type', 'digital')->count(),
            'both' => ClientDocument::where('document_storage_type', 'both')->count(),
        ];

        // Missing documents (cases without documents)
        $missingDocuments = collect();
        if ($validated['show_missing'] ?? false) {
            $missingDocuments = CaseModel::whereDoesntHave('documents')
                ->whereNotNull('matter_description')
                ->with('client')
                ->limit(50)
                ->get()
                ->map(function ($case) {
                    return [
                        'case' => $case->matter_name_ar ?? $case->matter_name_en,
                        'client' => $case->client?->client_name_ar ?? $case->client?->client_name_en,
                    ];
                });
        }

        $groupBy = $validated['group_by'] ?? null;

        // Generate Excel export
        $locale = App::getLocale();
        $export = new DocumentInventoryExport(
            $documents,
            $stats,
            $missingDocuments,
            $groupBy,
            $locale
        );

        return $export->export();
    }
}

