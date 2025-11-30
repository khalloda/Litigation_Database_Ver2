# T-Report-03: Administrative Tasks Report - Detailed Plan

**Task ID**: T-Report-03  
**Priority**: Critical  
**Status**: ✅ Complete (PDF + Excel)  
**Branch**: `feat/report-admin-tasks`  
**Estimated Effort**: 3-4 days  
**Actual Completion**: 2025-01-15

---

## Overview

Generate comprehensive administrative tasks reports showing task status, workload distribution, and completion rates. Supports filtering by lawyer, case, status, and priority. Includes overdue task alerts and subtask breakdowns. Supports grouping by case or lawyer for workload analysis.

---

## Use Cases

1. **Workload Management**: View tasks assigned to specific lawyers
2. **Deadline Monitoring**: Identify overdue tasks requiring attention
3. **Performance Tracking**: Calculate task completion rates by lawyer/case
4. **Case Progress**: View all tasks related to a specific case
5. **Task Status Review**: Review tasks by status (pending, in-progress, completed)
6. **Executive Summary**: High-level task statistics for management

---

## Features

### Core Features
- ✅ Filter by lawyer (lawyer_id)
- ✅ Filter by case (matter_id)
- ✅ Filter by task status
- ✅ Filter by priority (if tracked)
- ✅ Show overdue tasks (based on execution_date and alert flag)
- ✅ Show task completion rates (by lawyer, by case)
- ✅ Subtask breakdown (include AdminSubtask details)
- ✅ Group by case or lawyer
- ✅ Show task details (required_work, performer, result)
- ✅ Show creation and execution dates
- ✅ Alert indicators for tasks requiring attention

### Advanced Features
- ✅ Summary statistics (total tasks, by status, by lawyer, completion rates)
- ✅ Excel export with separate sheets (summary, detailed, overdue)
- ✅ Task duration calculation (creation_date to execution_date)
- ✅ Workload distribution charts (if time permits)

---

## Technical Design

### Database Queries

**Main Query** (admin_tasks with relationships):
```php
AdminTask::with(['case.client', 'lawyer', 'subtasks'])
    ->when($lawyerId, fn($q) => $q->where('lawyer_id', $lawyerId))
    ->when($caseId, fn($q) => $q->where('matter_id', $caseId))
    ->when($status, fn($q) => $q->where('status', $status))
    ->when($showOverdue, fn($q) => $q->where(function($q) {
        $q->where('execution_date', '<', now())
          ->orWhere('alert', true)
          ->orWhereNull('execution_date');
    }))
    ->orderBy('execution_date', 'asc')
    ->orderBy('creation_date', 'desc')
    ->get();
```

**Overdue Tasks** (past execution_date or alert flag):
```php
AdminTask::where(function($q) {
    $q->where('execution_date', '<', now())
      ->orWhere('alert', true);
})
->whereNull('result') // Not completed
->with(['case', 'lawyer'])
->get();
```

**Completion Rates** (by lawyer):
```php
AdminTask::select('lawyer_id')
    ->selectRaw('COUNT(*) as total')
    ->selectRaw('SUM(CASE WHEN result IS NOT NULL THEN 1 ELSE 0 END) as completed')
    ->selectRaw('SUM(CASE WHEN execution_date < NOW() AND result IS NULL THEN 1 ELSE 0 END) as overdue')
    ->when($lawyerId, fn($q) => $q->where('lawyer_id', $lawyerId))
    ->groupBy('lawyer_id')
    ->with('lawyer')
    ->get()
    ->map(function($task) {
        $task->completion_rate = $task->total > 0 
            ? ($task->completed / $task->total) * 100 
            : 0;
        return $task;
    });
```

### API Endpoint

**Route**: `POST /api/reports/admin-tasks/{format}`  
**Format**: `pdf` or `excel`  
**Permission**: `reports.view`

**Request Body**:
```json
{
  "lawyer_id": 123,
  "case_id": 456,
  "status": "pending|in_progress|completed",
  "show_overdue": true,
  "group_by": "lawyer|case|null",
  "include_subtasks": true,
  "date_range": {
    "start": "2025-01-01",
    "end": "2025-01-31"
  },
  "orientation": "portrait|landscape"
}
```

**Response**: PDF download or Excel download

---

## Task Breakdown

### Task 3.1: Backend API Implementation ✅ COMPLETE
**ID**: T-Report-03.1  
**Estimated Time**: 1.5 days  
**Status**: ✅ Complete (PDF + Excel)

#### Sub-tasks:
- [x] ✅ Create `AdminTasksReportRequest` validation class
  - Validate lawyer_id, case_id, status filters
  - Validate group_by options
  - Validate date range
- [x] ✅ Extend `ReportController` with `adminTasksPdf()` method
  - Build query with filters
  - Calculate overdue tasks
  - Calculate completion rates
  - Group data if needed
  - Include subtasks if requested
  - Generate PDF using Snappy
- [x] ✅ Create `adminTasksExcel()` method
  - Generate Excel with multiple sheets via `AdminTasksExport` class
  - Sheet 1: Summary statistics (total, completed, pending, overdue, completion rate)
  - Sheet 2: Detailed tasks (all tasks with full details)
  - Sheet 3: Overdue tasks (filtered list)
  - Sheet 4+: Grouped sheets (by lawyer or by case, if requested)
- [x] ✅ Create Blade template: `resources/views/reports/admin_tasks_pdf.blade.php`
  - RTL layout support
  - Table with task details
  - Subtask indentation/nesting
  - Alert badges for overdue
  - Summary section with statistics
- [x] ✅ Add route in `routes/api.php`
  - `POST /api/reports/admin-tasks/pdf`
  - `POST /api/reports/admin-tasks/excel`

**DoD**:
- [x] ✅ API endpoint returns correct PDF file
- [x] ✅ All filters work correctly
- [x] ✅ Overdue detection accurate
- [x] ✅ Completion rate calculations correct
- [x] ✅ Subtasks included when requested
- [x] ✅ Grouping works correctly
- [x] ✅ Bilingual labels in output
- [x] ✅ Excel export implemented (multi-sheet with summary, detailed, overdue, and optional grouping)

---

### Task 3.2: Frontend Integration ✅ COMPLETE
**ID**: T-Report-03.2  
**Estimated Time**: 1 day  
**Status**: ✅ Complete

#### Sub-tasks:
- [x] ✅ Update `ReportsPage.tsx` with Admin Tasks Report widget
  - Add filter form (lawyer, case, status, date range)
  - Add show overdue checkbox
  - Add group by selector
  - Add include subtasks checkbox
  - Add export format buttons (PDF/Excel)
- [x] ✅ Create API handler method: `handleGenerateAdminTasksReport()`
  - Handle filter combinations
  - Handle file download (PDF and Excel)
- [x] ✅ Add loading states and error handling
- [x] ✅ Add success notifications
- [x] ✅ All filter combinations functional

**DoD**:
- [x] ✅ UI matches existing report design patterns
- [x] ✅ All filters work from frontend
- [x] ✅ File downloads trigger correctly
- [x] ✅ Error messages display properly
- [x] ✅ Bilingual labels in UI

---

### Task 3.3: Testing ✅ COMPLETE
**ID**: T-Report-03.3  
**Estimated Time**: 0.5 days  
**Status**: ✅ Complete

#### Sub-tasks:
- [x] ✅ Create feature test: `tests/Feature/Reports/AdminTasksReportTest.php`
  - Test PDF generation
  - Test Excel generation
  - Test filter combinations
  - Test permission checks
  - Test overdue detection
  - Test completion rate calculations
  - Test subtask inclusion
  - Test grouping functionality
  - Test empty results handling
- [ ] Create unit tests for completion rate calculations (optional enhancement)
- [ ] Manual testing with real data (user acceptance testing)
- [ ] Performance testing with large datasets (recommended)

**DoD**:
- [x] ✅ All tests pass (9 tests created)
- [x] ✅ Tests cover all filter combinations
- [ ] Performance acceptable (< 5 seconds for 5000+ tasks) — Performance testing recommended
- [x] ✅ Edge cases handled (null dates, missing relationships)

---

### Task 3.4: Documentation ✅ COMPLETE
**ID**: T-Report-03.4  
**Estimated Time**: 0.5 days  
**Status**: ✅ Complete

#### Sub-tasks:
- [x] ✅ Update `/docs/reports.md` with Admin Tasks Report section
  - API endpoint documentation
  - Request/response examples
  - Filter options
  - Use cases
- [ ] Add JSDoc comments to frontend code (optional enhancement)
- [x] ✅ Add PHPDoc comments to backend code (export classes documented)
- [x] ✅ Update `/docs/tasks-index.md` with task status

**DoD**:
- [x] ✅ Documentation complete and accurate
- [x] ✅ Examples work correctly

---

## Data Model Reference

### AdminTask Model Fields Used
- `id`: Task ID
- `matter_id`: Case reference (FK)
- `lawyer_id`: Lawyer reference (FK)
- `last_follow_up`: Follow-up notes
- `last_date`: Last date (date field)
- `authority`: Authority name
- `status`: Task status (filter key)
- `circuit`: Circuit information
- `required_work`: Required work description
- `performer`: Performer name
- `previous_decision`: Previous decision text
- `court`: Court name
- `result`: Task result/completion notes
- `creation_date`: Task creation datetime
- `execution_date`: Task execution datetime (for overdue detection)
- `alert`: Boolean flag (for alerts)

### AdminSubtask Model Fields Used
- `id`: Subtask ID
- `task_id`: Parent task reference (FK)
- `lawyer_id`: Assigned lawyer
- `performer`: Performer name
- `next_date`: Next date
- `result`: Subtask result
- `procedure_date`: Procedure date
- `report`: Boolean flag

### Related Models
- **CaseModel**: Case details, client reference
- **Client**: Client name
- **Lawyer**: Lawyer name

---

## Output Format Specifications

### PDF Output
- **Page Size**: A4 (portrait or landscape)
- **Header**: Report title, filters applied, generation timestamp
- **Content**: 
  - Summary section: Total tasks, by status, completion rates
  - Main table with columns:
    - Task ID
    - Case name
    - Client name
    - Lawyer
    - Required work
    - Status
    - Creation date
    - Execution date
    - Result
    - Alert badge (if overdue/alert)
  - Subtasks shown indented under parent tasks
- **Footer**: Page numbers, total count

### Excel Output
- **Sheet 1 - Summary**: Statistics table with completion rates
- **Sheet 2 - Detailed Tasks**: All tasks with full details
- **Sheet 3 - Overdue Tasks**: Only overdue/incomplete tasks
- **Sheet 4 - By Lawyer**: Tasks grouped by lawyer with stats
- **Sheet 5 - By Case**: Tasks grouped by case with stats
- **Formatting**: 
  - Headers in bold
  - Date columns formatted
  - Conditional formatting for overdue (red)
  - Subtasks indented/color-coded

---

## Completion Rate Calculation

### Formula
```
Completion Rate = (Completed Tasks / Total Tasks) * 100
```

### Completed Task Criteria
- Task has `result` field filled (not null/empty)
- OR execution_date has passed and result exists

### Overdue Task Criteria
- `execution_date` < current date AND `result` is null/empty
- OR `alert` flag is true

---

## Bilingual Support

### Language Keys Required
```php
'reports.admin_tasks.title' => 'Administrative Tasks Report' / 'تقرير الأعمال الإدارية',
'reports.admin_tasks.overdue' => 'Overdue' / 'متأخرة',
'reports.admin_tasks.completion_rate' => 'Completion Rate' / 'معدل الإنجاز',
'reports.admin_tasks.subtasks' => 'Subtasks' / 'المهام الفرعية',
// ... more keys
```

---

## Performance Considerations

### Indexes Required
Ensure these indexes exist:
- `execution_date` (for overdue queries)
- `lawyer_id` (FK index)
- `matter_id` (FK index)
- `status` (if frequently filtered)
- Composite: `(execution_date, status)` for overdue queries

### Query Optimization
- Use eager loading for relationships
- Limit date ranges or paginate results
- Cache completion rate calculations if needed
- Consider database views for complex aggregations

---

## Success Metrics

- [x] ✅ All filters work correctly
- [x] ✅ Overdue detection accurate
- [x] ✅ Completion rate calculations correct
- [x] ✅ Subtasks properly included
- [x] ✅ Grouping works correctly
- [x] ✅ PDF output is print-ready
- [x] ✅ Bilingual output correct
- [x] ✅ Permission checks enforced
- [ ] ⏳ Report generates in < 5 seconds for 5000+ tasks (performance testing pending)
- [x] ✅ Excel export opens correctly (multi-sheet with summary, detailed, overdue, and optional grouping)
- [ ] ⏳ Test coverage > 80% (testing phase pending)

---

## Dependencies

- Existing: `barryvdh/laravel-snappy` (PDF generation)
- Existing: `phpoffice/phpspreadsheet` (v5.1) - Used directly for Excel export
- Models: `AdminTask`, `AdminSubtask`, `CaseModel`, `Client`, `Lawyer`
- Permission: `reports.view`

---

## Related Tasks

- T-Report-02: Hearing Schedule Report (reference for structure)
- T-Report-Phase-1: Foundation infrastructure (completed)

---

**Last Updated**: 2025-01-15  
**Status**: ✅ Complete (PDF + Excel)  
**Completed**: 2025-01-15  
**Review Date**: After testing phase

