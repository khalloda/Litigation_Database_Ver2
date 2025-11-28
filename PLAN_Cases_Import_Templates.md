# Cases Import Templates from Database Schema

## Overview

Generate schema-driven CSV and XLSX import templates for the Cases module with download endpoints, artisan regeneration command, and comprehensive documentation. Templates will be automatically generated from the live database schema to ensure accuracy and maintainability.

## Implementation Strategy

### 1. Artisan Command: `templates:generate-cases-from-db`

**File**: `app/Console/Commands/GenerateCasesTemplateFromDb.php`

**Responsibilities**:
- Connect to MySQL and introspect `cases` table schema via `INFORMATION_SCHEMA.COLUMNS`
- Query foreign key relationships from `INFORMATION_SCHEMA.KEY_COLUMN_USAGE`
- Apply inclusion rules to determine importable columns
- Determine required vs optional fields based on `IS_NULLABLE` and `COLUMN_DEFAULT`
- Generate CSV template (UTF-8 with BOM, headers + 2 sample rows)
- Generate XLSX workbook with 3 sheets
- Ensure `storage/app/templates/` directory exists

**Inclusion Rules**:
- **Exclude**: `id` (auto-increment PK), `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`
- **Include**: All business data columns (58 total from current schema)

**Key Columns Discovered** (from live database):
- **Required** (NOT NULL, no default): `client_id`, `matter_name_ar`, `matter_name_en`, `matter_select`
- **Foreign Keys**: `client_id`, `contract_id`, `court_id`, `opponent_id`, `matter_partner_id`, `matter_destination_id`, plus 11 option_value FKs
- **Text Fields**: `client_in_case_name`, `opponent_in_case_name`, `matter_description`, `notes_1`, `notes_2`, `allocated_budget`, `legal_opinion`, `financial_provision`, `current_status`
- **Dates**: `matter_start_date`, `matter_end_date`
- **Decimals**: `matter_asked_amount`, `matter_judged_amount`, `fee_letter`
- **Integers**: `team_id`, `court_floor`, `court_hall`
- **Legacy Text** (for backward compatibility): `matter_status`, `matter_category`, `matter_degree`, `matter_court_text`, `matter_destination`, `matter_partner`, `client_type`, `client_branch`, `client_and_capacity`, `opponent_and_capacity`

**Sample Rows** (2 rows with realistic data):

Row 1 (Arabic-focused):
```
123,شركة سبيد ميديكال,المدعي ضدهم,NULL,EL-2024-123,مطالبة مالية ضد سبيد ميديكا,Financial Claim vs Speed Medical,"دعوى مطالبة مالية...",NULL,1,NULL,2,NULL,5,NULL,NULL,NULL,221,NULL,NULL,NULL,NULL,8,NULL,NULL,NULL,NULL,2024-11-20,NULL,250000.00,NULL,A-15,NULL,1,كمال حلمي,خالد سيد,NULL,NULL,NULL,NULL,NULL,"مخصصات مالية...","رأي قانوني...","الوضع الحالي...","ملاحظات 1...","ملاحظات 2...","شركة سبيد - مدعى عليه",15,NULL,8,18,NULL,3,NULL,12,1
```

Row 2 (English-focused):
```
124,Speed Medical SAE,Defendants,NULL,EL-2023-456,Breach of Contract,خرق العقد ضد سبيد,"Contract breach case...",NULL,1,NULL,2,NULL,5,NULL,NULL,NULL,221,NULL,NULL,NULL,NULL,8,NULL,NULL,NULL,NULL,2023-06-01,NULL,125000.50,10000.00,B-22,NULL,2,Kamal Helmy,Khaled Sayed,NULL,NULL,NULL,NULL,NULL,"Budget allocation...","Legal opinion...","Current status...","Notes 1...","Notes 2...","Speed Medical - Defendant",15,NULL,8,18,NULL,3,NULL,12,1
```

**CSV Generation**:
- Path: `storage/app/templates/Cases_Import_Template.csv`
- Encoding: UTF-8 with BOM (`\xEF\xBB\xBF`)
- Format: Headers row + 2 sample rows
- Delimiter: comma
- Quoting: double quotes for text containing commas/newlines

**XLSX Generation** (using `PhpOffice\PhpSpreadsheet`):
- Path: `storage/app/templates/Cases_Import_Template.xlsx`

**Sheet 1: Cases_Import_Template**
- Row 1: Column headers (frozen)
- Rows 2-3: Sample data (Arabic + English)
- Styling: Bold headers, alternating row colors

**Sheet 2: Lookups**
- Format: `field_name | allowed_values`
- Content (queried from database):
  - `matter_category_id`: 27 values from option_values where option_set.key='case.category'
  - `matter_degree_id`: 21 values from option_values where option_set.key='case.degree'
  - `matter_status_id`: 3 values from option_values where option_set.key='case.status'
  - `matter_importance_id`: 6 values from option_values where option_set.key='case.importance'
  - `matter_branch_id`: Values from option_values where option_set.key='case.branch'
  - `client_capacity_id`, `opponent_capacity_id`: 32 values from option_values where option_set.key='capacity.type'
  - `client_type_id`: Values from option_values where option_set.key='client.cash_or_probono'
  - `circuit_name_id`, `circuit_serial_id`, `circuit_shift_id`: Circuit option values
  - `court_id`: All courts (court_name_ar / court_name_en)
  - `opponent_id`: All opponents (opponent_name_ar / opponent_name_en)
  - `matter_partner_id`: Lawyers filtered by partner titles only

**Sheet 3: README**
- Section 1: File Format & Encoding (UTF-8, dates YYYY-MM-DD, decimals with dot)
- Section 2: Required Fields (4 fields marked)
- Section 3: Foreign Key Resolution (ID vs name-based lookup)
- Section 4: Validation & Preflight (bilingual fuzzy matching for opponents)
- Section 5: Tips (avoid Excel auto-formatting, save CSV as UTF-8)
- Section 6: Regeneration Command (`php artisan templates:generate-cases-from-db`)

**Excel Data Validation**:
Apply dropdown lists on these columns (pointing to Lookups sheet):
- `matter_category_id`, `matter_degree_id`, `matter_status_id`, `matter_importance_id`, `matter_branch_id`
- `client_capacity_id`, `opponent_capacity_id`, `client_type_id`
- `circuit_name_id`, `circuit_shift_id`

### 2. Download Routes

**File**: `routes/web.php`

Add in the import routes section (after existing import routes):

```php
// Cases Import Templates
Route::middleware(['auth', 'permission:import.create'])->group(function () {
    Route::get('/cases/import/template/csv', [ImportController::class, 'downloadCaseTemplateCsv'])
        ->name('cases.template.csv');
    
    Route::get('/cases/import/template/xlsx', [ImportController::class, 'downloadCaseTemplateXlsx'])
        ->name('cases.template.xlsx');
});
```

### 3. Controller Methods

**File**: `app/Http/Controllers/ImportController.php`

Add two new public methods:

```php
/**
 * Download Cases CSV import template
 */
public function downloadCaseTemplateCsv()
{
    $this->authorize('create', ImportSession::class);
    
    $path = storage_path('app/templates/Cases_Import_Template.csv');
    
    if (!file_exists($path)) {
        abort(404, 'Template file not found. Run: php artisan templates:generate-cases-from-db');
    }
    
    return response()->download(
        $path,
        'Cases_Import_Template.csv',
        ['Content-Type' => 'text/csv; charset=UTF-8']
    );
}

/**
 * Download Cases XLSX import template
 */
public function downloadCaseTemplateXlsx()
{
    $this->authorize('create', ImportSession::class);
    
    $path = storage_path('app/templates/Cases_Import_Template.xlsx');
    
    if (!file_exists($path)) {
        abort(404, 'Template file not found. Run: php artisan templates:generate-cases-from-db');
    }
    
    return response()->download(
        $path,
        'Cases_Import_Template.xlsx',
        ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
    );
}
```

### 4. UI Integration

**File**: `resources/views/import/upload.blade.php`

Add template download alert at the top of the form (before file upload input), with conditional display:

```blade
@if(request('table') === 'cases' || old('table_name') === 'cases')
<div class="alert alert-light border d-flex align-items-center gap-3 mb-4">
    <div class="flex-grow-1">
        <div class="fw-semibold mb-1">
            <i class="bi bi-file-earmark-spreadsheet text-primary"></i>
            {{ __('app.cases_import_templates') }}
        </div>
        <div class="small text-muted">
            {{ __('app.download_template_generated_from_schema') }}
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('cases.template.csv') }}" 
           class="btn btn-outline-primary btn-sm"
           title="{{ __('app.download_csv_template') }}">
            <i class="bi bi-file-earmark-text"></i> 
            {{ __('app.download_csv') }}
        </a>
        <a href="{{ route('cases.template.xlsx') }}" 
           class="btn btn-outline-secondary btn-sm"
           title="{{ __('app.download_xlsx_template') }}">
            <i class="bi bi-file-earmark-excel"></i> 
            {{ __('app.download_xlsx') }}
        </a>
    </div>
</div>
@endif
```

### 5. Translation Keys

**File**: `resources/lang/en/app.php`

Add keys:
```php
'cases_import_templates' => 'Cases Import Templates',
'download_template_generated_from_schema' => 'Download CSV or Excel template generated from the current database schema.',
'download_csv' => 'CSV',
'download_xlsx' => 'Excel',
'download_csv_template' => 'Download CSV template',
'download_xlsx_template' => 'Download Excel template',
'template_regenerated_successfully' => 'Template files regenerated successfully.',
```

**File**: `resources/lang/ar/app.php`

Add keys:
```php
'cases_import_templates' => 'قوالب استيراد القضايا',
'download_template_generated_from_schema' => 'تنزيل قالب CSV أو Excel تم إنشاؤه من مخطط قاعدة البيانات الحالية.',
'download_csv' => 'CSV',
'download_xlsx' => 'Excel',
'download_csv_template' => 'تنزيل قالب CSV',
'download_xlsx_template' => 'تنزيل قالب Excel',
'template_regenerated_successfully' => 'تم إعادة إنشاء ملفات القوالب بنجاح.',
```

### 6. Documentation

**File**: `docs/imports/cases_template.md`

Create comprehensive documentation including:

1. **Overview**: Purpose and usage
2. **Column Specifications**: Table of all 58 columns with data types, nullable, description
3. **Required Fields**: List of NOT NULL fields
4. **Foreign Key Resolution**: How IDs vs names are handled
5. **Enum Values**: Complete lists from Lookups sheet
6. **Date/Number Formats**: YYYY-MM-DD, decimal with dot
7. **UTF-8 Encoding**: Importance and how to ensure
8. **Bilingual Support**: AR/EN allowed in all text fields
9. **Fuzzy Matching**: Integration with opponent preflight suggestions
10. **Regeneration**: Command and when to run it
11. **Download Links**: Routes to CSV/XLSX
12. **Validation**: Preflight checks performed

## Technical Details

### Dependencies
- Existing: `PhpOffice\PhpSpreadsheet` (already installed for import module)
- Database: MySQL 9.1 with `litigation_db_ver2` schema
- Laravel: 10.x with Eloquent and Schema facades

### Integration Points
- **MappingEngine**: Re-use `getDbColumnsForTable('cases')` for column discovery
- **Option Values**: Query from existing `option_sets` and `option_values` tables
- **Courts/Lawyers/Opponents**: Query from existing tables
- **Import Flow**: Templates work seamlessly with upload → map → preflight → execute
- **Fuzzy Matching**: Compatible with `OpponentSuggestionService` for opponent resolution

### File Locations
- **Command**: `app/Console/Commands/GenerateCasesTemplateFromDb.php`
- **Templates**: `storage/app/templates/` (created if missing)
- **Routes**: `routes/web.php` (import section)
- **Controller**: `app/Http/Controllers/ImportController.php` (new methods)
- **View**: `resources/views/import/upload.blade.php` (updated)
- **Docs**: `docs/imports/cases_template.md` (new)

### Error Handling
- Template not found: 404 with helpful message
- DB connection issues: Command fails gracefully with error message
- Missing directories: Auto-create `storage/app/templates/`
- Permission checks: Existing `import.create` permission enforced

## Validation & Testing

### Acceptance Criteria
1. ✅ Command `php artisan templates:generate-cases-from-db` runs successfully
2. ✅ CSV file created with UTF-8 BOM, 58 column headers, 2 sample rows
3. ✅ XLSX file created with 3 sheets (Template, Lookups, README)
4. ✅ XLSX has data validation on enum columns
5. ✅ Route `/cases/import/template/csv` downloads valid CSV
6. ✅ Route `/cases/import/template/xlsx` downloads valid XLSX
7. ✅ UI shows download buttons only when cases table selected
8. ✅ Templates compatible with existing import flow (can be uploaded, mapped, validated)
9. ✅ Documentation accurate and comprehensive
10. ✅ Translation keys work in EN/AR

### Manual Testing Steps
1. Run command: `php artisan templates:generate-cases-from-db`
2. Verify files created in `storage/app/templates/`
3. Open CSV in text editor → verify UTF-8 encoding, 58 columns, 2 rows
4. Open XLSX in Excel → verify 3 sheets, dropdowns work, README readable
5. Login to app → navigate to Import → select Cases table
6. Verify download buttons appear
7. Click CSV button → verify file downloads
8. Click Excel button → verify file downloads
9. Upload downloaded template → verify mapping auto-detects columns
10. Test in both EN and AR locales

## Commit Strategy

### Commit 1: Command Implementation
```
feat(templates): add GenerateCasesTemplateFromDb command with CSV/XLSX generation

- Create artisan command to introspect cases table schema
- Generate CSV template (UTF-8, headers + 2 sample rows)
- Generate XLSX workbook (3 sheets: Template, Lookups, README)
- Query option values, courts, lawyers, opponents for Lookups
- Add Excel data validation on enum columns
- Store templates in storage/app/templates/

DoD: Command runs and creates both template files
```

### Commit 2: Routes & Controller
```
feat(routes): add download endpoints for cases import templates

- Add routes for CSV/XLSX template downloads
- Add ImportController methods: downloadCaseTemplateCsv, downloadCaseTemplateXlsx
- Enforce import.create permission
- Return 404 with helpful message if templates missing

DoD: Routes accessible and download files correctly
```

### Commit 3: UI Integration
```
feat(ui): integrate template download buttons in import upload view

- Add conditional alert with download buttons in upload.blade.php
- Show only when cases table selected
- Add translation keys (EN/AR) for UI text
- Style with Bootstrap 5 and icons

DoD: UI shows download buttons, translations work
```

### Commit 4: Documentation
```
docs(imports): add comprehensive cases_template.md documentation

- Document all 58 importable columns with specifications
- Explain foreign key resolution strategies
- List all enum values from Lookups
- Provide regeneration instructions
- Link to download routes

DoD: Documentation complete and accurate
```

## To-dos

- [ ] Create GenerateCasesTemplateFromDb artisan command with schema introspection and file generation
- [ ] Add download routes and controller methods for CSV/XLSX templates
- [ ] Add template download UI to import upload view with conditional display
- [ ] Add translation keys for template download UI (EN/AR)
- [ ] Create comprehensive cases_template.md with column specifications

