# Category 1: Operational Reports - Master Plan

**Version**: 1.2  
**Date**: 2025-01-15  
**Status**: Phase 1 Complete, Phase 2 In Progress (2/4 Reports Complete)  
**Category**: High Priority Operational Reports

---

## Overview

This master plan covers the implementation of 4 critical operational reports for daily/weekly case management and administrative tracking. These reports are essential for managing court appearances, task workloads, case status overview, and document inventory.

---

## Reports in Scope

### 1. Hearing Schedule Report (T-Report-02) ✅ COMPLETE
- **Purpose**: Upcoming and past hearings with reminders
- **Priority**: Critical
- **Estimated Effort**: 3-4 days
- **Status**: ✅ Complete (PDF + Excel + Calendar View)
- **Branch**: `feat/report-hearing-schedule`

### 2. Administrative Tasks Report (T-Report-03) ✅ COMPLETE
- **Purpose**: Task status and workload by lawyer/case
- **Priority**: Critical
- **Estimated Effort**: 3-4 days
- **Status**: ✅ Complete (PDF working, Excel placeholder)
- **Branch**: `feat/report-admin-tasks`

### 3. Case Status Dashboard Report (T-Report-04)
- **Purpose**: At-a-glance case overview
- **Priority**: High
- **Estimated Effort**: 2-3 days
- **Branch**: `feat/report-case-status-dashboard`

### 4. Document Inventory Report (T-Report-05)
- **Purpose**: Document tracking and location management
- **Priority**: High
- **Estimated Effort**: 3-4 days
- **Branch**: `feat/report-document-inventory`

---

## Common Technical Requirements

### Technology Stack
- **PDF Engine**: `barryvdh/laravel-snappy` (wkhtmltopdf) - Already installed
- **Excel Export**: `phpoffice/phpspreadsheet` (v5.1) - Already installed, used directly (no maatwebsite/excel wrapper)
- **Backend**: Laravel 10.49.1, PHP 8.4
- **Frontend**: React SPA (ReportsPage.tsx)
- **Database**: MySQL 9.1.0

### Common Features Across All Reports
1. **Bilingual Support**: English/Arabic with RTL layout
2. **Permission Control**: `reports.view` permission required
3. **Date Range Filters**: Preset ranges (this week, this month, custom)
4. **Export Formats**: PDF (primary), Excel (secondary)
5. **Responsive Design**: Print-optimized PDFs, Excel-friendly exports
6. **Filtering**: Multiple filter combinations
7. **Sorting**: Configurable column sorting
8. **Pagination**: For large datasets in Excel exports

### API Endpoint Pattern
- **Base URL**: `/api/reports/{report-name}/{format}`
- **Method**: `POST` (for complex filters in request body)
- **Authentication**: `auth:sanctum` middleware
- **Authorization**: `permission:reports.view` middleware
- **Response**: PDF download or Excel download

### Frontend Integration
- Extend existing `ReportsPage.tsx` component
- Add new report widgets with filter forms
- Maintain consistent UI patterns from Client Cases Report
- Include download buttons and loading states

---

## Implementation Phases

### Phase 1: Foundation (Week 1) ✅ COMPLETE
1. ✅ Set up Excel export using PhpSpreadsheet (already installed)
2. ✅ Create base report service/helper classes (BaseReportExport.php)
3. ✅ Set up common report utilities (DateRangeHelper, ReportFormatter, ReportQueryBuilder)
4. ✅ Create shared Blade layout for reports (base_pdf.blade.php + partials)
5. ✅ Update ReportsPage.tsx with new report sections (4 new widgets added)

### Phase 2: Report Implementation (Weeks 2-3)
1. ✅ **T-Report-02**: Hearing Schedule Report — **COMPLETE**
2. ✅ **T-Report-03**: Administrative Tasks Report — **COMPLETE** (PDF, Excel placeholder)
3. **T-Report-04**: Case Status Dashboard Report — **PENDING**
4. **T-Report-05**: Document Inventory Report — **PENDING**

### Phase 3: Testing & Documentation (Week 4)
1. Feature tests for each report endpoint
2. Integration tests for filter combinations
3. Documentation updates
4. User acceptance testing

---

## Dependencies

### Packages Used
- `phpoffice/phpspreadsheet` (v5.1) - Already installed, used directly for Excel exports
- No additional packages required for Excel export functionality

### Database Considerations
- All reports use existing tables (no migrations needed)
- Ensure indexes are optimized for date range queries
- Consider adding composite indexes if performance issues arise

### Permission Requirements
- Ensure `reports.view` permission exists (already seeded)
- Consider additional granular permissions if needed (e.g., `reports.view.financial`)

---

## Success Criteria

- [x] ✅ Hearing Schedule Report generates PDF output correctly
- [x] ✅ Hearing Schedule Report supports Excel export (multi-sheet)
- [x] ✅ Administrative Tasks Report generates PDF output correctly
- [ ] Administrative Tasks Report Excel export (placeholder)
- [ ] Case Status Dashboard Report generates PDF output
- [ ] Document Inventory Report generates PDF and Excel output
- [x] ✅ All implemented reports respect permission checks
- [x] ✅ All implemented reports support bilingual output (EN/AR)
- [x] ✅ All implemented reports handle empty results gracefully
- [ ] All reports perform well with large datasets (< 5 seconds) — **Testing pending**
- [ ] All reports have comprehensive test coverage (>80%) — **Testing phase pending**
- [x] ✅ Implemented reports are documented in `/docs/reports/`
- [x] ✅ Implemented reports are integrated into React SPA ReportsPage

---

## Risk Assessment

### Technical Risks
1. **Performance**: Large date ranges may cause slow queries
   - **Mitigation**: Add date range limits, use indexes, implement caching
2. **Excel Export**: Complex formatting may be challenging
   - **Mitigation**: Start simple, iterate on formatting
3. **Date Handling**: Timezone and locale differences
   - **Mitigation**: Use Laravel Carbon with Africa/Cairo timezone consistently

### Data Risks
1. **Missing Data**: Some fields may be null/empty
   - **Mitigation**: Use null coalescing, provide "N/A" defaults
2. **Orphaned Records**: Some relationships may be missing
   - **Mitigation**: Use left joins, handle gracefully in output

---

## Documentation Structure

Each report will have:
1. Detailed task plan in `/docs/reports/T-Report-XX-{Report-Name}-Plan.md`
2. API documentation updates in `/docs/reports.md`
3. Frontend documentation in component comments
4. Test documentation in test files

---

## Related Documents

- Main Reports Documentation: `/docs/reports.md`
- Existing Report Implementation: `T-Report-01` (Client Cases Report)
- Tasks Index: `/docs/tasks-index.md`
- Data Dictionary: `/docs/data-dictionary.md`

---

**Last Updated**: 2025-01-15  
**Phase 1 Completed**: 2025-01-15  
**Phase 2 Progress**: 2 of 4 reports complete (50%)  
**Next Review**: After Phase 2 completion

