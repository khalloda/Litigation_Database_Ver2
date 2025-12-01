# Client Case PDF Report

This document explains how to generate the Toyota-style client case report that mirrors the provided sample.

## Overview

- **Route (API)**: `POST /api/reports/client-cases/pdf`
- **Permission**: `reports.view`
- **PDF Engine**: [`barryvdh/laravel-snappy`](https://github.com/barryvdh/laravel-snappy) using `wkhtmltopdf`
- **Front-end**: React `ReportsPage` exposes a client selector, column toggles, and generates the PDF via Axios.

## Server Requirements

1. **Install wkhtmltopdf on the host OS:**
   - **Windows**: Download from [wkhtmltopdf.org](https://wkhtmltopdf.org/downloads.html) and install. Add the installation directory (typically `C:\Program Files\wkhtmltopdf\bin`) to your system PATH, or set the binary path in `.env`.
   - **Linux**: `sudo apt-get install wkhtmltopdf` (Debian/Ubuntu) or `sudo yum install wkhtmltopdf` (RHEL/CentOS).
   - **macOS**: `brew install wkhtmltopdf` (via Homebrew).

2. **Set the binaries in `.env` if they differ from defaults:**
   ```env
   WKHTMLTOPDF_BINARY=C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe
   WKHTMLTOIMAGE_BINARY=C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe
   ```
   On Linux/macOS, use Unix-style paths:
   ```env
   WKHTMLTOPDF_BINARY=/usr/local/bin/wkhtmltopdf
   WKHTMLTOIMAGE_BINARY=/usr/local/bin/wkhtmltoimage
   ```

3. **Install PHP dependencies:**
   ```bash
   composer install
   ```
   This will pull `barryvdh/laravel-snappy` and its dependencies.

4. **Grant permissions:**
   ```bash
   php artisan db:seed --class=PermissionsSeeder
   ```
   This ensures the `reports.view` permission exists and is assigned to `super_admin`.

## Usage Steps

1. Ensure the authenticated user has the `reports.view` permission (seed via `PermissionsSeeder` or assign manually).
2. Open **Reports** page in the SPA. The "Client Case Report" widget should appear at the top of the page.
3. Select a client, optionally toggle columns, pick the **Matter Status** filter (`الكل`, `سارية`, or `منتهية`), and click **Generate PDF**. The browser automatically downloads the PDF.
4. The backend fetches the client's cases, latest hearing decisions, evaluation, and financial provisions, then renders the PDF using `resources/views/reports/client_cases_pdf.blade.php`.

### Filtering by Matter Status

- Use the status dropdown in the SPA to limit the report to **Active (`سارية`)**, **Closed (`منتهية`)**, or **All** cases.
- The API accepts the `status_filter` field with either the Arabic labels above or their English equivalents (`all`, `active`, `closed`).
- Filtering happens against the legacy `matter_status` column on the `cases` table to match the data source requested by the user.

## API Example

```bash
curl -X POST /api/reports/client-cases/pdf \
     -H "Authorization: Bearer <token>" \
     -H "Accept: application/pdf" \
     -d '{"client_id":123,"columns":{"serial":true,"subject":false},"status_filter":"سارية"}' \
     --output client-report.pdf
```

## Troubleshooting

- **Blank PDF / 500**: Confirm wkhtmltopdf is installed and executable by the web user.
- **403 Forbidden**: Assign the `reports.view` permission to the user/role.
- **Font rendering issues**: Install an Arabic font like `Cairo` or `Noto Kufi Arabic` on the server; wkhtmltopdf leverages system fonts.

## Files Touched

- `app/Http/Controllers/Api/ReportController.php`
- `resources/views/reports/client_cases_pdf.blade.php`
- `public/pages/ReportsPage.tsx`
- `config/snappy.php`
- `docs/reports.md` (this file)

---

# Operational Reports

This section documents the four operational reports implemented for daily/weekly case management and administrative tracking. All reports support both PDF and Excel export formats, with bilingual output (English/Arabic) and RTL layout support.

## Common Requirements

All operational reports share the following:

- **Permission**: `reports.view` required
- **Authentication**: Bearer token via `auth:sanctum` middleware
- **PDF Engine**: `barryvdh/laravel-snappy` (wkhtmltopdf)
- **Excel Engine**: `phpoffice/phpspreadsheet` (v5.1) - used directly
- **Bilingual Support**: English/Arabic with RTL layout
- **Base URL Pattern**: `/api/reports/{report-name}/{format}`
- **Method**: `POST` (for complex filters in request body)

---

## 1. Hearing Schedule Report

**Purpose**: Track upcoming and past hearings with reminders and overdue detection.

### Endpoints

- **PDF**: `POST /api/reports/hearing-schedule/pdf`
- **Excel**: `POST /api/reports/hearing-schedule/excel`

### Request Parameters

```json
{
  "date_range_type": "this_week|this_month|custom|all",
  "start_date": "2025-01-01",  // Required if date_range_type is "custom"
  "end_date": "2025-01-31",    // Required if date_range_type is "custom"
  "court_id": 123,              // Optional: Filter by court
  "case_id": 456,               // Optional: Filter by case
  "lawyer_id": 789,             // Optional: Filter by lawyer
  "status": "upcoming|past|all", // Optional: Filter by status
  "show_overdue": true,         // Optional: Show only overdue hearings
  "calendar_view": false,       // Optional: PDF only - monthly calendar format
  "orientation": "portrait|landscape" // Optional: PDF orientation
}
```

### Excel Output

The Excel export includes multiple sheets:

1. **Upcoming Hearings**: All hearings scheduled for future dates
2. **Past Hearings**: All completed hearings
3. **Overdue Hearings**: Hearings that are past due without decisions
4. **Summary**: Statistics and counts

### Example Request

```bash
curl -X POST /api/reports/hearing-schedule/excel \
     -H "Authorization: Bearer <token>" \
     -H "Content-Type: application/json" \
     -d '{
       "date_range_type": "this_month",
       "show_overdue": true
     }' \
     --output hearing-schedule.xlsx
```

### Response

- **PDF**: Direct download (`Content-Type: application/pdf`)
- **Excel**: Direct download (`Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`)

---

## 2. Administrative Tasks Report

**Purpose**: Track task status, workload distribution, and completion rates by lawyer/case.

### Endpoints

- **PDF**: `POST /api/reports/admin-tasks/pdf`
- **Excel**: `POST /api/reports/admin-tasks/excel`

### Request Parameters

```json
{
  "date_range_type": "this_week|this_month|custom|all",
  "start_date": "2025-01-01",  // Required if date_range_type is "custom"
  "end_date": "2025-01-31",    // Required if date_range_type is "custom"
  "lawyer_id": 123,             // Optional: Filter by lawyer
  "case_id": 456,               // Optional: Filter by case
  "status": "pending|completed|all", // Optional: Filter by status
  "show_overdue": true,         // Optional: Show only overdue tasks
  "group_by": "lawyer|case",    // Optional: Group results
  "include_subtasks": true,     // Optional: Include subtask details
  "orientation": "portrait|landscape" // Optional: PDF orientation
}
```

### Excel Output

The Excel export includes multiple sheets:

1. **Summary**: Total tasks, completed, pending, overdue, completion rate
2. **Detailed**: All tasks with full details (serial, required work, case, lawyer, status, dates, result)
3. **Overdue**: Tasks that are past due or flagged with alerts
4. **By Lawyer** (if `group_by: "lawyer"`): Tasks grouped by assigned lawyer
5. **By Case** (if `group_by: "case"`): Tasks grouped by case

### Example Request

```bash
curl -X POST /api/reports/admin-tasks/excel \
     -H "Authorization: Bearer <token>" \
     -H "Content-Type: application/json" \
     -d '{
       "date_range_type": "this_month",
       "lawyer_id": 123,
       "show_overdue": true,
       "group_by": "lawyer",
       "include_subtasks": true
     }' \
     --output admin-tasks.xlsx
```

### Response

- **PDF**: Direct download with task details, subtasks (if requested), completion rates, and statistics
- **Excel**: Direct download with multiple sheets as described above

---

## 3. Case Status Dashboard Report

**Purpose**: At-a-glance case overview with summary statistics and attention-required indicators.

### Endpoints

- **PDF**: `POST /api/reports/case-status-dashboard/pdf`
- **Excel**: Not available (dashboard format optimized for single-page PDF)

### Request Parameters

```json
{
  "status": "all|active|closed|سارية|منتهية", // Optional: Filter by status
  "category_id": 123,          // Optional: Filter by case category
  "court_id": 456,             // Optional: Filter by court
  "lawyer_id": 789,            // Optional: Filter by lawyer
  "show_attention_required": true,  // Optional: Highlight cases needing attention
  "show_recent_activity": true,     // Optional: Show recent activity indicators
  "orientation": "portrait|landscape" // Optional: PDF orientation
}
```

### PDF Output

Single-page dashboard layout with:

1. **Summary Cards**: Total cases, active cases, closed cases, attention required count
2. **Cases Requiring Attention**: Cases with overdue tasks, missing data, or upcoming hearings
3. **Recent Activity**: Cases with recent hearings or task updates

### Example Request

```bash
curl -X POST /api/reports/case-status-dashboard/pdf \
     -H "Authorization: Bearer <token>" \
     -H "Content-Type: application/json" \
     -d '{
       "status": "active",
       "show_attention_required": true,
       "show_recent_activity": true,
       "orientation": "landscape"
     }' \
     --output case-status-dashboard.pdf
```

### Response

- **PDF**: Direct download with single-page dashboard layout

---

## 4. Document Inventory Report

**Purpose**: Comprehensive document tracking and location management for physical and digital documents.

### Endpoints

- **PDF**: `POST /api/reports/document-inventory/pdf`
- **Excel**: `POST /api/reports/document-inventory/excel`

### Request Parameters

```json
{
  "client_id": 123,             // Optional: Filter by client
  "case_id": 456,               // Optional: Filter by case
  "document_type": "Contract",  // Optional: Filter by document type (string search)
  "location": "Vault A",        // Optional: Filter by location (string search)
  "storage_type": "physical|digital|both", // Optional: Filter by storage type
  "show_missing": true,         // Optional: Include cases without documents
  "group_by": "client|case|location", // Optional: Group results
  "orientation": "portrait|landscape" // Optional: PDF orientation
}
```

### Excel Output

The Excel export includes multiple sheets:

1. **Summary**: Total documents, physical, digital, both counts
2. **Detailed Inventory**: All documents with full details (client, case, name, type, deposit date, location, storage type, movement card, file size)
3. **By Location**: Documents grouped by physical location
4. **By Client**: Documents grouped by client
5. **By Case**: Documents grouped by case
6. **Missing Documents**: Cases without documents (if `show_missing: true`)

### Example Request

```bash
curl -X POST /api/reports/document-inventory/excel \
     -H "Authorization: Bearer <token>" \
     -H "Content-Type: application/json" \
     -d '{
       "storage_type": "physical",
       "show_missing": true,
       "group_by": "location"
     }' \
     --output document-inventory.xlsx
```

### Response

- **PDF**: Direct download with document details, grouping (if requested), and missing documents section
- **Excel**: Direct download with multiple sheets as described above

---

## Troubleshooting

### Common Issues

1. **403 Forbidden**: Ensure user has `reports.view` permission
2. **500 Internal Server Error**:
   - Check server logs for detailed error messages
   - Verify wkhtmltopdf is installed for PDF exports
   - Verify PhpSpreadsheet is properly installed for Excel exports
3. **Empty Results**: Check filter parameters and ensure data exists matching criteria
4. **Slow Performance**: 
   - Review date range filters (avoid very large ranges)
   - Check database indexes on filtered columns
   - Consider pagination for Excel exports with very large datasets
5. **RTL Layout Issues**: Ensure Arabic fonts are installed on the server for PDF generation

### Performance Optimization

- Use specific filters to reduce dataset size
- Limit date ranges to reasonable periods
- Excel exports may take longer for large datasets (5000+ records)
- PDF generation is optimized for single-page or paginated output

---

## Files Structure

### Backend Files

- `app/Http/Controllers/Api/ReportController.php` - Main report controller
- `app/Http/Requests/*ReportRequest.php` - Validation classes for each report
- `app/Exports/*Export.php` - Excel export classes extending `BaseReportExport`
- `resources/views/reports/*_pdf.blade.php` - PDF Blade templates
- `app/Support/Reports/DateRangeHelper.php` - Date range utility
- `app/Support/Reports/ReportFormatter.php` - Report formatting utility
- `app/Support/Reports/ReportQueryBuilder.php` - Query building utility

### Frontend Files

- `public/pages/ReportsPage.tsx` - React component with all report widgets
- `public/locales/en.json` / `ar.json` - Frontend translations

### Documentation Files

- `/docs/reports/` - Detailed plan files for each report
- `/docs/reports.md` - This API documentation file
- `/docs/tasks-index.md` - Task tracking index

