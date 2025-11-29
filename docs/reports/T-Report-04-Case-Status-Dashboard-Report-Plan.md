# T-Report-04: Case Status Dashboard Report - Detailed Plan

**Task ID**: T-Report-04  
**Priority**: High  
**Status**: Planning  
**Branch**: `feat/report-case-status-dashboard`  
**Estimated Effort**: 2-3 days

---

## Overview

Generate a single-page dashboard report providing at-a-glance case overview with summary statistics, status breakdown, and cases requiring attention. Supports filtering by status, category, court, and lawyer. Includes recent activity indicators (last hearing, last task update).

---

## Use Cases

1. **Weekly Status Review**: Quick overview of all cases for management meetings
2. **Status Monitoring**: Track active vs. closed cases
3. **Attention Required**: Identify cases needing immediate attention
4. **Category Analysis**: See case distribution by category
5. **Lawyer Workload**: View cases assigned to specific lawyers
6. **Executive Summary**: High-level statistics for stakeholders

---

## Features

### Core Features
- ✅ Filter by case status (سارية/منتهية/all)
- ✅ Filter by case category (matter_category_id)
- ✅ Filter by court (court_id)
- ✅ Filter by lawyer (lawyer_a, lawyer_b)
- ✅ Summary statistics:
  - Total cases
  - Active cases count
  - Closed cases count
  - Cases by category
  - Cases by court
- ✅ Cases requiring attention:
  - Cases with alerts (from admin_tasks.alert)
  - Cases with missing critical data
  - Cases with overdue tasks
  - Cases with upcoming hearings
- ✅ Recent activity indicators:
  - Last hearing date (from hearings table)
  - Last task update (from admin_tasks.updated_at)
- ✅ Visual dashboard layout with key metrics cards

---

## Technical Design

### Database Queries

**Summary Statistics**:
```php
$stats = [
    'total' => CaseModel::count(),
    'active' => CaseModel::where('matter_status', 'سارية')->count(),
    'closed' => CaseModel::where('matter_status', 'منتهية')->count(),
    'by_category' => CaseModel::selectRaw('matter_category_id, COUNT(*) as count')
        ->groupBy('matter_category_id')
        ->with('matterCategory')
        ->get(),
    'by_court' => CaseModel::selectRaw('court_id, COUNT(*) as count')
        ->groupBy('court_id')
        ->with('court')
        ->get(),
    'by_lawyer' => CaseModel::selectRaw('lawyer_a as lawyer_id, COUNT(*) as count')
        ->whereNotNull('lawyer_a')
        ->groupBy('lawyer_a')
        ->union(
            CaseModel::selectRaw('lawyer_b as lawyer_id, COUNT(*) as count')
                ->whereNotNull('lawyer_b')
                ->groupBy('lawyer_b')
        )
        ->with('lawyer')
        ->get(),
];
```

**Cases Requiring Attention**:
```php
// Cases with overdue tasks
$casesWithOverdueTasks = CaseModel::whereHas('adminTasks', function($q) {
    $q->where('execution_date', '<', now())
      ->whereNull('result')
      ->orWhere('alert', true);
})->get();

// Cases with upcoming hearings (within 7 days)
$casesWithUpcomingHearings = CaseModel::whereHas('hearings', function($q) {
    $q->where('date', '>=', now())
      ->where('date', '<=', now()->addDays(7));
})->get();

// Cases with missing critical data
$casesWithMissingData = CaseModel::where(function($q) {
    $q->whereNull('matter_description')
      ->orWhereNull('matter_start_date')
      ->orWhereNull('client_id');
})->get();
```

**Recent Activity**:
```php
CaseModel::with([
    'latestHearing' => fn($q) => $q->orderBy('date', 'desc')->limit(1),
    'latestTask' => fn($q) => $q->orderBy('updated_at', 'desc')->limit(1),
])
->get()
->map(function($case) {
    $case->last_activity_date = max(
        $case->latestHearing?->date,
        $case->latestTask?->updated_at?->toDateString()
    );
    return $case;
});
```

### API Endpoint

**Route**: `POST /api/reports/case-status-dashboard/pdf`  
**Format**: `pdf` only (single-page dashboard)  
**Permission**: `reports.view`

**Request Body**:
```json
{
  "status": "سارية|منتهية|all",
  "category_id": 123,
  "court_id": 456,
  "lawyer_id": 789,
  "show_attention_required": true,
  "show_recent_activity": true,
  "orientation": "portrait|landscape"
}
```

**Response**: PDF download (single page)

---

## Task Breakdown

### Task 4.1: Backend API Implementation
**ID**: T-Report-04.1  
**Estimated Time**: 1 day

#### Sub-tasks:
- [ ] Create `CaseStatusDashboardRequest` validation class
  - Validate status, category_id, court_id, lawyer_id filters
- [ ] Extend `ReportController` with `caseStatusDashboardPdf()` method
  - Build summary statistics query
  - Build cases requiring attention query
  - Build recent activity query
  - Apply filters
  - Generate single-page PDF using Snappy
- [ ] Create Blade template: `resources/views/reports/case_status_dashboard_pdf.blade.php`
  - Dashboard layout with metric cards
  - Summary statistics section
  - Cases requiring attention table
  - Recent activity table
  - RTL layout support
- [ ] Add route in `routes/api.php`
  - `POST /api/reports/case-status-dashboard/pdf`

**DoD**:
- [ ] API endpoint returns correct PDF file
- [ ] All filters work correctly
- [ ] Statistics are accurate
- [ ] Attention-required detection works
- [ ] Recent activity shows correctly
- [ ] Single-page layout fits on A4
- [ ] Bilingual labels in output

---

### Task 4.2: Frontend Integration
**ID**: T-Report-04.2  
**Estimated Time**: 0.5 days

#### Sub-tasks:
- [ ] Update `ReportsPage.tsx` with Case Status Dashboard widget
  - Add filter form (status, category, court, lawyer)
  - Add show attention required checkbox
  - Add show recent activity checkbox
  - Add PDF download button
- [ ] Create API service method: `fetchCaseStatusDashboardReport()`
  - Handle filter combinations
  - Handle file download
- [ ] Add loading states and error handling
- [ ] Add success notifications

**DoD**:
- [ ] UI matches existing report design patterns
- [ ] All filters work from frontend
- [ ] File download triggers correctly
- [ ] Error messages display properly
- [ ] Bilingual labels in UI

---

### Task 4.3: Testing
**ID**: T-Report-04.3  
**Estimated Time**: 0.5 days

#### Sub-tasks:
- [ ] Create feature test: `tests/Feature/Reports/CaseStatusDashboardReportTest.php`
  - Test PDF generation
  - Test filter combinations
  - Test permission checks
  - Test statistics calculations
  - Test attention-required detection
  - Test recent activity queries
- [ ] Manual testing with real data
- [ ] Verify single-page layout

**DoD**:
- [ ] All tests pass (>80% coverage)
- [ ] Statistics calculations accurate
- [ ] Performance acceptable (< 3 seconds)

---

### Task 4.4: Documentation
**ID**: T-Report-04.4  
**Estimated Time**: 0.5 days

#### Sub-tasks:
- [ ] Update `/docs/reports.md` with Case Status Dashboard section
- [ ] Add code comments
- [ ] Update `/docs/tasks-index.md`

**DoD**:
- [ ] Documentation complete

---

## Data Model Reference

### CaseModel Fields Used
- `id`: Case ID
- `client_id`: Client reference
- `matter_name_ar/en`: Case name
- `matter_status`: Status (سارية/منتهية)
- `matter_category_id`: Category FK
- `court_id`: Court FK
- `lawyer_a`, `lawyer_b`: Lawyer assignments
- `matter_description`: Case description
- `matter_start_date`: Start date
- `current_status`: Current status text

### Relationships Used
- `client`: Client details
- `matterCategory`: Category details
- `court`: Court details
- `hearings`: Latest hearing
- `adminTasks`: Latest task, overdue tasks

---

## Output Format Specifications

### PDF Output (Single Page Dashboard)
- **Page Size**: A4 (portrait or landscape)
- **Layout**: Dashboard-style with sections:
  1. **Header**: Report title, filters, timestamp
  2. **Summary Cards**: Key metrics in card layout
     - Total Cases (large number)
     - Active Cases (green)
     - Closed Cases (gray)
     - Cases by Category (pie chart or list)
     - Cases by Court (bar chart or list)
  3. **Cases Requiring Attention**: Compact table with:
     - Case name
     - Client name
     - Reason (overdue task, missing data, etc.)
     - Priority indicator
  4. **Recent Activity**: Compact table with:
     - Case name
     - Last hearing date
     - Last task update
     - Days since last activity
- **Footer**: Generation timestamp

**Design Notes**:
- Use compact font sizes to fit on one page
- Use color coding for status indicators
- Use icons/symbols for quick scanning
- Ensure RTL layout works correctly

---

## Attention-Required Criteria

A case requires attention if ANY of the following:
1. Has admin_tasks with `alert = true`
2. Has admin_tasks with `execution_date < now()` AND `result IS NULL`
3. Has missing critical data:
   - `matter_description` is null/empty
   - `matter_start_date` is null
   - `client_id` is null
4. Has upcoming hearing within 7 days (optional flag)

---

## Bilingual Support

### Language Keys Required
```php
'reports.case_status_dashboard.title' => 'Case Status Dashboard' / 'لوحة حالة القضايا',
'reports.case_status_dashboard.total_cases' => 'Total Cases' / 'إجمالي القضايا',
'reports.case_status_dashboard.active_cases' => 'Active Cases' / 'القضايا السارية',
'reports.case_status_dashboard.attention_required' => 'Attention Required' / 'تتطلب الاهتمام',
// ... more keys
```

---

## Performance Considerations

### Optimization
- Use database aggregations for statistics (avoid counting in PHP)
- Cache statistics if dashboard is frequently generated
- Limit cases requiring attention to top 20-30
- Use eager loading for all relationships
- Consider materialized views for complex aggregations

### Indexes
- Ensure indexes on `matter_status`, `matter_category_id`, `court_id`
- Ensure indexes on `lawyer_a`, `lawyer_b`

---

## Success Metrics

- [ ] Report generates in < 3 seconds
- [ ] All statistics accurate
- [ ] Single page fits on A4
- [ ] Attention-required detection works
- [ ] Recent activity shows correctly
- [ ] Bilingual output correct
- [ ] Permission checks enforced
- [ ] Test coverage > 80%

---

## Dependencies

- Existing: `barryvdh/laravel-snappy`
- Models: `CaseModel`, `Client`, `Hearing`, `AdminTask`
- Permission: `reports.view`

---

## Related Tasks

- T-Report-02: Hearing Schedule Report (reference)
- T-Report-03: Administrative Tasks Report (for attention-required logic)

---

**Last Updated**: 2025-01-15  
**Assigned To**: TBD

