# T-Report-02: Hearing Schedule Report - Detailed Plan

**Task ID**: T-Report-02  
**Priority**: Critical  
**Status**: ✅ Complete  
**Branch**: `feat/report-hearing-schedule`  
**Estimated Effort**: 3-4 days  
**Actual Completion**: 2025-01-15

---

## Overview

Generate comprehensive hearing schedules showing upcoming and past court hearings with case details, decision summaries, and next hearing dates. Supports filtering by date range, court, case, and lawyer. Includes alerts for overdue/missed hearings and optional calendar view.

---

## Use Cases

1. **Daily Scheduling**: View all hearings scheduled for today/this week
2. **Court Preparation**: Review upcoming hearings with case context
3. **Missed Hearing Tracking**: Identify overdue hearings that need follow-up
4. **Lawyer Workload**: See all hearings assigned to specific lawyers
5. **Case Timeline**: View hearing history for a specific case
6. **Calendar Export**: Generate monthly calendar view for planning

---

## Features

### Core Features
- ✅ Date range filter (preset: today, this week, this month, custom range)
- ✅ Filter by court (court_id or court name)
- ✅ Filter by case (matter_id)
- ✅ Filter by lawyer (lawyer_id)
- ✅ Filter by case status (سارية/منتهية)
- ✅ Show case details (client, case name, description)
- ✅ Show hearing details (date, time, procedure, decision)
- ✅ Show next hearing date (from next_hearing field)
- ✅ Alert badges for overdue/missed hearings
- ✅ Alert badges for hearings requiring client notification
- ✅ Decision summaries (decision, short_decision, last_decision)
- ✅ Attendee information (attendee, attendee_1-4)

### Advanced Features
- ✅ Calendar view option (monthly grid PDF)
- ✅ Excel export with separate sheets (upcoming, past, missed)
- ✅ Summary statistics (total hearings, by status, by court)
- ✅ Group by court/case/lawyer option

---

## Technical Design

### Database Queries

**Main Query** (hearings table with relationships):
```php
Hearing::with(['case.client', 'lawyer', 'case.court'])
    ->whereBetween('date', [$startDate, $endDate])
    ->when($courtId, fn($q) => $q->whereHas('case', fn($q) => $q->where('court_id', $courtId)))
    ->when($caseId, fn($q) => $q->where('matter_id', $caseId))
    ->when($lawyerId, fn($q) => $q->where('lawyer_id', $lawyerId))
    ->orderBy('date', 'asc')
    ->orderBy('case.matter_name_ar', 'asc')
    ->get();
```

**Overdue Hearings** (past date with no decision/result):
```php
Hearing::where('date', '<', now())
    ->whereNull('decision')
    ->orWhere('decision', '')
    ->get();
```

**Upcoming Hearings** (future date):
```php
Hearing::where('date', '>=', now())
    ->orderBy('date', 'asc')
    ->get();
```

### API Endpoint

**Route**: `POST /api/reports/hearing-schedule/{format}`  
**Format**: `pdf` or `excel`  
**Permission**: `reports.view`

**Request Body**:
```json
{
  "date_range_type": "custom|today|this_week|this_month",
  "start_date": "2025-01-01",
  "end_date": "2025-01-31",
  "court_id": 123,
  "case_id": 456,
  "lawyer_id": 789,
  "case_status": "سارية|منتهية|all",
  "view_type": "list|calendar",
  "show_overdue": true,
  "group_by": "court|case|lawyer|null",
  "orientation": "portrait|landscape"
}
```

**Response**: PDF download or Excel download

---

## Task Breakdown

### Task 2.1: Backend API Implementation ✅ COMPLETE
**ID**: T-Report-02.1  
**Estimated Time**: 1.5 days  
**Status**: ✅ Complete

#### Sub-tasks:
- [x] ✅ Create `HearingScheduleReportRequest` validation class
  - Validate date ranges (start_date, end_date)
  - Validate filters (court_id, case_id, lawyer_id)
  - Validate view_type, group_by options
- [x] ✅ Extend `ReportController` with `hearingSchedulePdf()` method
  - Build query with filters
  - Calculate overdue hearings
  - Group data if needed
  - Generate PDF using Snappy
- [x] ✅ Create `hearingScheduleExcel()` method
  - Generate Excel with multiple sheets via `HearingScheduleExport` class
  - Sheet 1: Upcoming hearings
  - Sheet 2: Past hearings
  - Sheet 3: Overdue/Missed hearings
  - Sheet 4: Summary statistics
- [x] ✅ Create Blade template: `resources/views/reports/hearing_schedule_pdf.blade.php`
  - RTL layout support
  - Table with hearing details
  - Alert badges for overdue/missed
  - Summary section
- [x] ✅ Create Blade template: `resources/views/reports/hearing_schedule_calendar_pdf.blade.php`
  - Monthly grid layout
  - Hearing dates marked on calendar
  - Legend for hearing types
  - Color-coded status indicators
  - Sidebar with hearing details
- [x] ✅ Add route in `routes/api.php`
  - `POST /api/reports/hearing-schedule/pdf`
  - `POST /api/reports/hearing-schedule/excel`

**DoD**:
- [x] ✅ API endpoints return correct PDF/Excel files
- [x] ✅ All filters work correctly
- [x] ✅ Overdue detection logic accurate
- [x] ✅ Bilingual labels in output
- [x] ✅ Route secured with permission middleware

---

### Task 2.2: Excel Export Library Setup ✅ COMPLETE (Phase 1)
**ID**: T-Report-02.2  
**Estimated Time**: 0.5 days  
**Status**: ✅ Complete (completed in Phase 1)

#### Sub-tasks:
- [x] ✅ Use `phpoffice/phpspreadsheet` directly (already installed, v5.1)
- [x] ✅ Create base Excel export class: `app/Exports/BaseReportExport.php`
  - Common formatting methods
  - Header/footer setup
  - Bilingual cell formatting
  - Multi-sheet support
- [x] ✅ Create `app/Exports/HearingScheduleExport.php`
  - Extends base export class
  - Multiple sheets implementation
  - Formatting for dates, Arabic text

**DoD**:
- [x] ✅ Base export class created (Phase 1)
- [x] ✅ Can generate Excel file with multiple sheets (completed in Task 2.1)
- [x] ✅ Arabic text renders correctly

---

### Task 2.3: Frontend Integration ✅ COMPLETE
**ID**: T-Report-02.3  
**Estimated Time**: 1 day  
**Status**: ✅ Complete

#### Sub-tasks:
- [x] ✅ Update `ReportsPage.tsx` with Hearing Schedule Report widget
  - Add filter form (date range, court, case, lawyer)
  - Add view type selector (list/calendar)
  - Add orientation selector
  - Add export format buttons (PDF/Excel)
- [x] ✅ Create API service method: `handleGenerateHearingScheduleReport()`
  - Handle date range presets
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

### Task 2.4: Testing ✅ COMPLETE
**ID**: T-Report-02.4  
**Estimated Time**: 0.5 days  
**Status**: ✅ Complete

#### Sub-tasks:
- [x] ✅ Create feature test: `tests/Feature/Reports/HearingScheduleReportTest.php`
  - Test PDF generation
  - Test Excel generation
  - Test filter combinations
  - Test permission checks
  - Test overdue detection
  - Test empty results handling
- [ ] Create unit tests for query building logic (optional enhancement)
- [ ] Manual testing with real data (user acceptance testing)
- [ ] Performance testing with large datasets (recommended)

**DoD**:
- [x] ✅ All tests pass (6 tests created)
- [x] ✅ Tests cover all filter combinations
- [ ] Performance acceptable (< 5 seconds for 1000+ hearings) — Performance testing recommended
- [x] ✅ Edge cases handled (null dates, missing relationships)

---

### Task 2.5: Documentation ✅ COMPLETE
**ID**: T-Report-02.5  
**Estimated Time**: 0.5 days  
**Status**: ✅ Complete

#### Sub-tasks:
- [x] ✅ Update `/docs/reports.md` with Hearing Schedule Report section
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
- [ ] Screenshots included (if applicable) — Optional

---

## Data Model Reference

### Hearing Model Fields Used
- `id`: Hearing ID
- `matter_id`: Case reference (FK)
- `lawyer_id`: Lawyer reference (FK)
- `date`: Hearing date (filter key)
- `procedure`: Procedure description
- `court`: Court name (text)
- `circuit`: Circuit information
- `destination`: Destination
- `decision`: Full decision text
- `short_decision`: Short decision summary
- `last_decision`: Last decision
- `next_hearing`: Next hearing date (important for scheduling)
- `report`: Boolean flag
- `notify_client`: Boolean flag (for alerts)
- `attendee`, `attendee_1-4`: Attendee names
- `evaluation`: Evaluation notes
- `notes`: Additional notes

### Related Models
- **CaseModel**: `matter_name_ar/en`, `matter_status`, `client_id`
- **Client**: `client_name_ar/en`
- **Lawyer**: `lawyer_name_ar/en`
- **Court**: `court_name_ar/en` (via case relationship)

---

## Output Format Specifications

### PDF Output (List View)
- **Page Size**: A4 (portrait or landscape)
- **Header**: Report title, date range, filters applied, generation timestamp
- **Content**: Table with columns:
  - Serial number
  - Hearing date & time
  - Case name (Arabic/English)
  - Client name
  - Court
  - Procedure
  - Decision summary
  - Next hearing date
  - Status badges (upcoming/overdue/missed)
- **Footer**: Page numbers, total hearings count
- **Summary Section**: Statistics (total, by status, by court)

### PDF Output (Calendar View)
- **Page Size**: A4 landscape
- **Layout**: Monthly grid calendar
- **Content**: Calendar with hearing dates marked
  - Color coding for hearing types
  - Popup/legend showing hearing details
- **Sidebar**: List of hearings for the month with details

### Excel Output
- **Sheet 1 - Upcoming Hearings**: Future hearings sorted by date
- **Sheet 2 - Past Hearings**: Past hearings sorted by date (descending)
- **Sheet 3 - Overdue/Missed**: Past hearings without decisions
- **Sheet 4 - Summary**: Statistics and charts
- **Formatting**: 
  - Headers in bold with background color
  - Date columns formatted as dates
  - Arabic text columns right-aligned
  - Conditional formatting for overdue items (red)

---

## Bilingual Support

### Language Keys Required
Add to `resources/lang/en/reports.php` and `resources/lang/ar/reports.php`:

```php
// Hearing Schedule Report
'reports.hearing_schedule.title' => 'Hearing Schedule Report' / 'تقرير جدول الجلسات',
'reports.hearing_schedule.date_range' => 'Date Range' / 'الفترة الزمنية',
'reports.hearing_schedule.upcoming' => 'Upcoming' / 'قادمة',
'reports.hearing_schedule.overdue' => 'Overdue' / 'متأخرة',
'reports.hearing_schedule.missed' => 'Missed' / 'فاتت',
// ... more keys
```

---

## Performance Considerations

### Indexes Required
Ensure these indexes exist on `hearings` table:
- `date` (already exists via migration)
- `matter_id` (FK index)
- `lawyer_id` (FK index)
- Composite: `(date, matter_id)` for common queries

### Query Optimization
- Use eager loading to avoid N+1 queries
- Limit date ranges (max 1 year) or paginate
- Cache court/lawyer lists for dropdowns
- Use database views for complex aggregations if needed

---

## Rollback Plan

If issues arise:
1. Revert route additions in `routes/api.php`
2. Remove controller methods from `ReportController`
3. Remove Blade templates
4. Remove frontend changes in `ReportsPage.tsx`
5. Revert any package installations if not needed

**Rollback Command**:
```bash
git revert <commit-hash>
# Or
git reset --hard <previous-commit>
```

---

## Success Metrics

- [x] ✅ All filters work correctly
- [x] ✅ Overdue detection is accurate
- [x] ✅ PDF output is print-ready
- [x] ✅ Excel export opens correctly in Excel/LibreOffice (multi-sheet)
- [x] ✅ Bilingual output correct
- [x] ✅ Permission checks enforced
- [x] ✅ Calendar view functional
- [ ] ⏳ Report generates in < 5 seconds for 1000+ hearings (performance testing pending)
- [ ] ⏳ Test coverage > 80% (testing phase pending)

---

## Dependencies

- Existing: `barryvdh/laravel-snappy` (PDF generation)
- Existing: `phpoffice/phpspreadsheet` (v5.1) - Used directly for Excel export
- Models: `Hearing`, `CaseModel`, `Client`, `Lawyer`, `Court`
- Permission: `reports.view`

---

## Related Tasks

- T-Report-01: Client Cases Report (reference implementation)
- T-Report-Phase-1: Foundation infrastructure (completed)

---

**Last Updated**: 2025-01-15  
**Status**: ✅ Complete (PDF + Excel + Calendar View + Frontend)  
**Completed**: 2025-01-15  
**Review Date**: After testing phase

