# Cases Import Templates from Database Schema (REVISED)

## Overview

Generate schema-driven CSV and XLSX import templates for Cases in **two variants** (Standard & Extended) with download endpoints, artisan regeneration command with mode flags, and comprehensive documentation. Templates automatically generated from live database schema with versioning, ID-vs-Name precedence, and cPanel-friendly implementation.

---

## Key Adjustments from Original Plan

1. **Split Templates**: Standard (core fields) vs Extended (all fields)
2. **ID vs Name Precedence**: Support both, prefer ID, log conflicts
3. **Smart Validation**: Dropdowns only for short enums, not large lists
4. **cPanel Optimized**: Memory-friendly PhpSpreadsheet, verified extensions
5. **Versioning**: Template version + schema hash + generation timestamp
6. **Permission Model**: Use `import.view_template` (not `import.create`)
7. **Enhanced Docs**: Standard vs Extended sections, precedence rules
8. **README Sheet**: Include versioning, precedence rules, limitations
9. **Comprehensive Tests**: 6 test types including precedence conflict
10. **Admin Regeneration**: UI button for admins to rebuild templates

---

## Implementation Strategy

### 1. Artisan Command: `templates:generate-cases`

**File**: `app/Console/Commands/GenerateCasesTemplate.php`

**Signature**: `templates:generate-cases {--mode=all : Generate standard, extended, or all templates}`

**Modes**:
- `--mode=standard`: Generate only Standard templates (CSV + XLSX)
- `--mode=extended`: Generate only Extended templates (CSV + XLSX)
- `--mode=all`: Generate all 4 files (default)

**Responsibilities**:
- Connect to MySQL and introspect `cases` table via `INFORMATION_SCHEMA.COLUMNS`
- Calculate schema signature (MD5 hash of column names + types for versioning)
- Split columns into **Standard** (core) vs **Extended** (optional/legacy)
- Generate 4 template files with versioning metadata
- Query lookup data for short enums only
- Apply memory-friendly PhpSpreadsheet settings

**Standard Template Columns** (approximately 20-25 core fields):
- **Required**: `client_id` OR `client_name`, `matter_name_ar`, `matter_name_en`
- **Core FKs** (ID or Name): `client_id/client_name`, `court_id/court_name`, `opponent_id/opponent_name`, `matter_partner_id/matter_partner_name`
- **Short Enums** (with dropdowns): `matter_status_id`, `matter_category_id`, `matter_degree_id`, `matter_importance_id`, `client_capacity_id`, `opponent_capacity_id`
- **Key Fields**: `matter_description`, `matter_start_date`, `matter_end_date`, `matter_asked_amount`, `matter_judged_amount`
- **Notes**: `notes_1` (primary notes field)

**Extended Template Columns** (all remaining ~35 fields):
- **Legacy Text**: `matter_status`, `matter_category`, `matter_degree`, `matter_court_text`, `matter_destination`, `matter_partner`, `client_type`, `client_branch`
- **Circuit Fields**: `circuit_name_id`, `circuit_serial_id`, `circuit_shift_id`, `matter_circuit_legacy`
- **Secondary Fields**: `contract_id`, `engagement_letter_no`, `team_id`, `circuit_secretary`, `court_floor`, `court_hall`
- **Additional**: `fee_letter`, `allocated_budget`, `legal_opinion`, `financial_provision`, `current_status`, `notes_2`
- **Capacity**: `client_and_capacity`, `opponent_and_capacity`, `client_capacity_note`, `opponent_capacity_note`
- **Branch**: `matter_branch_id`, `client_type_id`
- **Other**: `matter_evaluation`, `matter_shelf`, `lawyer_a`, `lawyer_b`, `matter_select`

**ID vs Name Support**:
For each FK, support **both** columns but document precedence:
- `client_id` **OR** `client_name` (AR/EN accepted)
- `court_id` **OR** `court_name` (AR/EN accepted)
- `opponent_id` **OR** `opponent_name` (AR/EN accepted)
- `matter_partner_id` **OR** `matter_partner_name` (AR/EN accepted)
- `matter_destination_id` **OR** `matter_destination_name`
- Option value FKs: `*_id` **OR** `*_text` (e.g., `matter_status_id` OR `matter_status`)

**Precedence Rule** (enforced in preflight):
1. If both `*_id` AND `*_name` provided → **Prefer ID**
2. If they disagree → **Log WARNING** in preflight report
3. If only `*_name` → Resolve via fuzzy matching (AR/EN normalization)

**Sample Rows** (adjusted for both templates):

Standard Template Row 1 (Arabic):
```csv
client_name,client_in_case_name,matter_name_ar,matter_name_en,matter_description,matter_status_id,matter_category_id,matter_degree_id,matter_importance_id,court_name,opponent_name,client_capacity_id,opponent_capacity_id,matter_partner_name,matter_start_date,matter_end_date,matter_asked_amount,matter_judged_amount,notes_1
شركة سبيد ميديكال,شركة سبيد ميديكال ش.م.م,مطالبة مالية,Financial Claim,"دعوى مطالبة مالية ضد...",1,2,8,2,القاهرة الاقتصادية,سبيد ميديكا,15,18,كمال حلمي,2024-11-20,,250000.00,,"قضية مستعجلة؛ متابعة دورية"
```

Standard Template Row 2 (English):
```csv
client_name,client_in_case_name,matter_name_ar,matter_name_en,matter_description,matter_status_id,matter_category_id,matter_degree_id,matter_importance_id,court_name,opponent_name,client_capacity_id,opponent_capacity_id,matter_partner_name,matter_start_date,matter_end_date,matter_asked_amount,matter_judged_amount,notes_1
Speed Medical SAE,Speed Medical SAE,خرق العقد,Breach of Contract,"Contract breach case...",1,2,8,1,Cairo Economic Court,Speed Medical,15,18,Kamal Helmy,2023-06-01,2024-12-31,125000.50,10000.00,"Ongoing case; quarterly review"
```

Extended Template: Same headers + all additional columns (legacy, circuit, etc.)

**CSV Generation** (4 files):
- `storage/app/templates/Cases_Import_Template_Standard.csv`
- `storage/app/templates/Cases_Import_Template_Extended.csv`
- Encoding: UTF-8 **with BOM** (`\xEF\xBB\xBF`) for Windows Excel compatibility
- Headers + 2 sample rows (AR + EN)

**XLSX Generation** (4 files using `PhpOffice\PhpSpreadsheet`):
- `storage/app/templates/Cases_Import_Template_Standard.xlsx`
- `storage/app/templates/Cases_Import_Template_Extended.xlsx`

**Memory-Friendly Settings**:
```php
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Collection\Memory;

// Use memory cache with GZip compression
Settings::setCache(new Memory\MemoryGZip());

// Avoid loading large arrays at once
// Write validation lists only for short enums
```

**XLSX Sheet 1: Cases_Import_Template**
- Row 1: Column headers (bold, frozen)
- Rows 2-3: Sample data (Arabic + English)
- Data validation **ONLY** for short enums (see below)

**XLSX Sheet 2: Lookups** (with Named Ranges)
- Format: One section per enum with header + values
- Named ranges created for each list

**Short Enums** (include dropdowns):
- `status_list`: 3 values (سارية, منتهية, موقوفة)
- `category_list`: 27 values (جنايات, مدني, تجاري, etc.)
- `degree_list`: 21 values (ابتدائي, استئناف, نقض, etc.)
- `importance_list`: 6 values (حرجة, عاجل, عادي, etc.)
- `capacity_list`: 32 values (مدعي, مدعى عليه, etc.)
- `branch_list`: Values from case.branch
- `client_type_list`: Values from client.cash_or_probono
- `circuit_shift_list`: Circuit shift options (if short)

**Large Lists** (NO dropdowns - free text with preflight fuzzy matching):
- Courts (~50-200 records): Use `court_name` column, preflight suggests matches
- Opponents (~300+ records): Use `opponent_name` column, preflight suggests matches
- Lawyers (~14 records): Could include dropdown but prefer free text for consistency
- Circuit names/serials: If long lists, use free text

**Named Range Creation**:
```php
$spreadsheet->addNamedRange(
    new \PhpOffice\PhpSpreadsheet\NamedRange(
        'status_list',
        $lookupsSheet,
        'B2:B4' // Adjust to actual range
    )
);
```

**Data Validation Application**:
```php
$validation = $templateSheet->getCell('E2')->getDataValidation();
$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
$validation->setAllowBlank(true);
$validation->setShowDropDown(true);
$validation->setFormula1('=status_list');
// Apply to entire column: E2:E1000
```

**XLSX Sheet 3: README**

Content blocks:
1. **Template Information**
   - Template Type: Standard / Extended
   - Template Version: 1.0
   - DB Schema Signature: `md5(column_names + types)` (e.g., `a3f2c9b8d1e4...`)
   - Generated At (UTC): 2025-01-24 15:30:00

2. **File Format & Encoding**
   - Encoding: UTF-8 (save CSV as UTF-8 with BOM)
   - Date Format: YYYY-MM-DD (ISO 8601)
   - Decimal Separator: Dot (e.g., 125000.50)
   - Boolean: 1/0 or true/false
   - Language: Arabic and/or English allowed in text fields

3. **Required Fields**
   - client_id OR client_name (at least one required)
   - matter_name_ar (required)
   - matter_name_en (required)
   - All other fields optional unless marked NOT NULL in database

4. **Foreign Key Resolution: ID vs Name Precedence**
   - **Rule**: For any FK (client, court, opponent, partner), you can provide EITHER:
     - ID column (e.g., `client_id`): Direct lookup, fastest
     - Name column (e.g., `client_name`): Fuzzy match with AR/EN normalization
   - **Precedence**: If BOTH provided, **ID wins** (Name ignored)
   - **Conflict Warning**: If ID and Name disagree, preflight logs a WARNING
   - **Fuzzy Matching**: Name columns use bilingual normalization + similarity scoring

5. **Validation & Preflight**
   - Short enums have dropdowns in XLSX (status, category, degree, importance, capacity)
   - Large lists (courts, opponents) use free text; preflight suggests closest matches
   - Bilingual fuzzy matching for opponent names (existing OpponentSuggestionService)
   - Error threshold: 15% (stops import if exceeded)

6. **Limitations**
   - Dropdowns only for short enumerations (<50 values)
   - Large vocabularies (courts, opponents) rely on preflight name matching
   - Excel row limit: 1,048,576 (should be sufficient for most imports)

7. **Standard vs Extended**
   - **Standard**: Core fields needed to create a basic case (~20-25 columns)
   - **Extended**: All optional/legacy/advanced fields (~35 additional columns)
   - Use Standard for most imports; use Extended when migrating legacy data

8. **Regeneration**
   - Command: `php artisan templates:generate-cases --mode=all`
   - Run after schema changes to update templates
   - Admin users can regenerate via UI button

**cPanel / Environment Requirements**:
- PHP Extensions: `ext-zip`, `ext-mbstring`, `ext-iconv` (required)
- Optional: `ext-gd` or `ext-imagick` (for advanced XLSX features)
- Memory: 256MB minimum (512MB recommended for large lookups)
- PhpSpreadsheet: Already installed via composer

---

### 2. Download Routes

**File**: `routes/web.php`

Add in import routes section:

```php
// Cases Import Templates (Standard & Extended)
Route::middleware(['auth', 'permission:import.view_template'])->group(function () {
    // Standard Templates
    Route::get('/cases/import/template/standard/csv', [ImportController::class, 'downloadCaseTemplateStandardCsv'])
        ->name('cases.template.standard.csv');
    
    Route::get('/cases/import/template/standard/xlsx', [ImportController::class, 'downloadCaseTemplateStandardXlsx'])
        ->name('cases.template.standard.xlsx');
    
    // Extended Templates
    Route::get('/cases/import/template/extended/csv', [ImportController::class, 'downloadCaseTemplateExtendedCsv'])
        ->name('cases.template.extended.csv');
    
    Route::get('/cases/import/template/extended/xlsx', [ImportController::class, 'downloadCaseTemplateExtendedXlsx'])
        ->name('cases.template.extended.xlsx');
});

// Admin: Regenerate Templates
Route::middleware(['auth', 'permission:admin.tools.manage'])->group(function () {
    Route::post('/admin/templates/regenerate-cases', [ImportController::class, 'regenerateCaseTemplates'])
        ->name('admin.templates.regenerate-cases');
});
```

**Permission Changes**:
- Change from `import.create` to `import.view_template`
- Return **403 Forbidden** (not 404) when unauthorized
- Admin regeneration requires `admin.tools.manage`

---

### 3. Controller Methods

**File**: `app/Http/Controllers/ImportController.php`

Add **6 new methods**:

```php
/**
 * Download Cases Standard CSV template
 */
public function downloadCaseTemplateStandardCsv()
{
    if (!Gate::allows('import.view_template')) {
        abort(403, 'Unauthorized to view import templates.');
    }
    
    $path = storage_path('app/templates/Cases_Import_Template_Standard.csv');
    
    if (!file_exists($path)) {
        abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=standard');
    }
    
    return response()->download(
        $path,
        'Cases_Import_Template_Standard.csv',
        ['Content-Type' => 'text/csv; charset=UTF-8']
    );
}

/**
 * Download Cases Standard XLSX template
 */
public function downloadCaseTemplateStandardXlsx()
{
    if (!Gate::allows('import.view_template')) {
        abort(403, 'Unauthorized to view import templates.');
    }
    
    $path = storage_path('app/templates/Cases_Import_Template_Standard.xlsx');
    
    if (!file_exists($path)) {
        abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=standard');
    }
    
    return response()->download($path, 'Cases_Import_Template_Standard.xlsx');
}

/**
 * Download Cases Extended CSV template
 */
public function downloadCaseTemplateExtendedCsv()
{
    if (!Gate::allows('import.view_template')) {
        abort(403, 'Unauthorized to view import templates.');
    }
    
    $path = storage_path('app/templates/Cases_Import_Template_Extended.csv');
    
    if (!file_exists($path)) {
        abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=extended');
    }
    
    return response()->download($path, 'Cases_Import_Template_Extended.csv');
}

/**
 * Download Cases Extended XLSX template
 */
public function downloadCaseTemplateExtendedXlsx()
{
    if (!Gate::allows('import.view_template')) {
        abort(403, 'Unauthorized to view import templates.');
    }
    
    $path = storage_path('app/templates/Cases_Import_Template_Extended.xlsx');
    
    if (!file_exists($path)) {
        abort(404, 'Template not found. Run: php artisan templates:generate-cases --mode=extended');
    }
    
    return response()->download($path, 'Cases_Import_Template_Extended.xlsx');
}

/**
 * Regenerate all Cases import templates (Admin only)
 */
public function regenerateCaseTemplates(Request $request)
{
    if (!Gate::allows('admin.tools.manage')) {
        abort(403, 'Unauthorized to regenerate templates.');
    }
    
    try {
        Artisan::call('templates:generate-cases', ['--mode' => 'all']);
        
        return back()->with('success', __('app.template_regenerated_successfully'));
    } catch (\Exception $e) {
        return back()->with('error', 'Template regeneration failed: ' . $e->getMessage());
    }
}
```

---

### 4. UI Integration

**File**: `resources/views/import/upload.blade.php`

Add template download section with **4 download buttons**:

```blade
@if(request('table') === 'cases' || old('table_name') === 'cases')
<div class="card border-primary mb-4">
    <div class="card-header bg-light">
        <i class="bi bi-file-earmark-spreadsheet text-primary"></i>
        <strong>{{ __('app.cases_import_templates') }}</strong>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            {{ __('app.download_template_generated_from_schema') }}
        </p>
        
        <!-- Standard Templates -->
        <div class="mb-3">
            <div class="d-flex align-items-center mb-2">
                <strong class="me-2">{{ __('app.standard_template') }}</strong>
                <span class="badge bg-success">{{ __('app.recommended') }}</span>
            </div>
            <p class="small text-muted mb-2">
                {{ __('app.standard_template_description') }}
            </p>
            <div class="btn-group">
                <a href="{{ route('cases.template.standard.csv') }}" 
                   class="btn btn-outline-primary btn-sm"
                   title="{{ __('app.download_csv_template') }}">
                    <i class="bi bi-file-earmark-text"></i> CSV
                </a>
                <a href="{{ route('cases.template.standard.xlsx') }}" 
                   class="btn btn-outline-success btn-sm"
                   title="{{ __('app.download_xlsx_template') }}">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </div>
        
        <hr>
        
        <!-- Extended Templates -->
        <div>
            <div class="d-flex align-items-center mb-2">
                <strong class="me-2">{{ __('app.extended_template') }}</strong>
                <span class="badge bg-secondary">{{ __('app.advanced') }}</span>
            </div>
            <p class="small text-muted mb-2">
                {{ __('app.extended_template_description') }}
            </p>
            <div class="btn-group">
                <a href="{{ route('cases.template.extended.csv') }}" 
                   class="btn btn-outline-secondary btn-sm"
                   title="{{ __('app.download_csv_template') }}">
                    <i class="bi bi-file-earmark-text"></i> CSV
                </a>
                <a href="{{ route('cases.template.extended.xlsx') }}" 
                   class="btn btn-outline-secondary btn-sm"
                   title="{{ __('app.download_xlsx_template') }}">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </div>
    </div>
</div>
@endif
```

**Admin Tools Page** (new or existing):

```blade
@can('admin.tools.manage')
<div class="card">
    <div class="card-header">{{ __('app.import_templates') }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.templates.regenerate-cases') }}">
            @csrf
            <p>{{ __('app.regenerate_templates_description') }}</p>
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-arrow-clockwise"></i>
                {{ __('app.regenerate_cases_templates') }}
            </button>
        </form>
    </div>
</div>
@endcan
```

---

### 5. Preflight Engine Enhancement

**File**: `app/Services/PreflightEngine.php`

Add **ID vs Name precedence logic**:

```php
/**
 * Resolve FK with ID vs Name precedence
 * 
 * @param array $row
 * @param string $idColumn (e.g., 'client_id')
 * @param string $nameColumn (e.g., 'client_name')
 * @param string $model (e.g., App\Models\Client::class)
 * @param array $nameFields (e.g., ['client_name_ar', 'client_name_en'])
 * @return array ['resolved_id' => int|null, 'warnings' => array]
 */
protected function resolveFkWithPrecedence($row, $idColumn, $nameColumn, $model, $nameFields)
{
    $id = $row[$idColumn] ?? null;
    $name = $row[$nameColumn] ?? null;
    $warnings = [];
    
    // If both provided, check for conflict
    if ($id && $name) {
        $record = $model::find($id);
        if ($record) {
            $actualName = $record->{$nameFields[0]} ?? $record->{$nameFields[1]};
            if ($actualName && $actualName !== $name) {
                $warnings[] = "ID/Name conflict: {$idColumn}={$id} points to '{$actualName}' but {$nameColumn}='{$name}' provided. Using ID.";
            }
        }
        return ['resolved_id' => $id, 'warnings' => $warnings];
    }
    
    // If only ID provided
    if ($id) {
        return ['resolved_id' => $id, 'warnings' => []];
    }
    
    // If only Name provided, fuzzy match
    if ($name) {
        $fuzzyResult = $this->fuzzyMatchName($name, $model, $nameFields);
        if ($fuzzyResult['match']) {
            return ['resolved_id' => $fuzzyResult['id'], 'warnings' => $fuzzyResult['warnings']];
        } else {
            $warnings[] = "No match found for {$nameColumn}='{$name}'. Please provide valid {$idColumn} or exact name.";
            return ['resolved_id' => null, 'warnings' => $warnings];
        }
    }
    
    // Neither provided
    return ['resolved_id' => null, 'warnings' => []];
}
```

Update `runPreflight()` to call this for all FKs:
- `client_id/client_name`
- `court_id/court_name`
- `opponent_id/opponent_name`
- `matter_partner_id/matter_partner_name`
- Option value FKs: `*_id/*_text`

---

### 6. Translation Keys

**File**: `resources/lang/en/app.php`

```php
// Template Downloads
'cases_import_templates' => 'Cases Import Templates',
'download_template_generated_from_schema' => 'Download CSV or Excel templates generated from the current database schema.',
'standard_template' => 'Standard Template',
'extended_template' => 'Extended Template',
'recommended' => 'Recommended',
'advanced' => 'Advanced',
'standard_template_description' => 'Core fields only (~25 columns). Use for most imports.',
'extended_template_description' => 'All fields including legacy/optional (~60 columns). Use for complete data migration.',
'download_csv_template' => 'Download CSV template',
'download_xlsx_template' => 'Download Excel template',
'template_regenerated_successfully' => 'Template files regenerated successfully.',
'regenerate_templates_description' => 'Regenerate import templates from the current database schema. Run this after schema changes.',
'regenerate_cases_templates' => 'Regenerate Cases Templates',
'import_templates' => 'Import Templates',
```

**File**: `resources/lang/ar/app.php`

```php
// Template Downloads
'cases_import_templates' => 'قوالب استيراد القضايا',
'download_template_generated_from_schema' => 'تنزيل قوالب CSV أو Excel تم إنشاؤها من مخطط قاعدة البيانات الحالية.',
'standard_template' => 'القالب القياسي',
'extended_template' => 'القالب الموسع',
'recommended' => 'موصى به',
'advanced' => 'متقدم',
'standard_template_description' => 'الحقول الأساسية فقط (~25 عمود). استخدم لمعظم عمليات الاستيراد.',
'extended_template_description' => 'جميع الحقول بما في ذلك القديمة/الاختيارية (~60 عمود). استخدم لترحيل البيانات الكامل.',
'download_csv_template' => 'تنزيل قالب CSV',
'download_xlsx_template' => 'تنزيل قالب Excel',
'template_regenerated_successfully' => 'تم إعادة إنشاء ملفات القوالب بنجاح.',
'regenerate_templates_description' => 'إعادة إنشاء قوالب الاستيراد من مخطط قاعدة البيانات الحالي. قم بتشغيل هذا بعد تغييرات المخطط.',
'regenerate_cases_templates' => 'إعادة إنشاء قوالب القضايا',
'import_templates' => 'قوالب الاستيراد',
```

---

### 7. Documentation

**File**: `docs/imports/cases_template.md`

Structure:

1. **Overview**: Standard vs Extended templates
2. **Standard Template Columns** (table with ~25 columns)
3. **Extended Template Columns** (table with ~35 additional columns)
4. **Required Fields**: NOT NULL columns without defaults
5. **ID vs Name Precedence**: Detailed explanation with examples
6. **Foreign Key Resolution**: Fuzzy matching for names (AR/EN)
7. **Enum Values**: Short lists with dropdowns
8. **Large Lists**: Courts/Opponents use free text + preflight
9. **File Formats**: UTF-8 BOM, date/decimal formats
10. **Bilingual Support**: Arabic & English examples
11. **Download Links**: 4 download routes
12. **Regeneration**: Command with --mode flag
13. **Versioning**: Template version, schema signature, timestamp
14. **Testing**: How to validate templates before full import

---

### 8. Permission Seeder

**File**: `database/seeders/PermissionsSeeder.php`

Add new permission:

```php
Permission::create(['name' => 'import.view_template']);
```

Assign to roles:
- `super_admin`: all permissions
- `admin`: import.view_template
- `lawyer`: import.view_template (optional, based on policy)

---

### 9. Tests

**File**: `tests/Feature/CasesTemplateTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CasesTemplateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function template_headers_match_database_schema()
    {
        // Run command
        $this->artisan('templates:generate-cases --mode=standard');
        
        // Read CSV headers
        $csv = storage_path('app/templates/Cases_Import_Template_Standard.csv');
        $this->assertFileExists($csv);
        
        $handle = fopen($csv, 'r');
        $headers = fgetcsv($handle);
        fclose($handle);
        
        // Assert expected columns present
        $this->assertContains('client_id', $headers);
        $this->assertContains('client_name', $headers);
        $this->assertContains('matter_name_ar', $headers);
        $this->assertContains('matter_name_en', $headers);
        
        // Assert excluded columns not present
        $this->assertNotContains('id', $headers);
        $this->assertNotContains('created_at', $headers);
        $this->assertNotContains('deleted_at', $headers);
    }

    /** @test */
    public function xlsx_has_data_validation_on_enum_columns()
    {
        $this->artisan('templates:generate-cases --mode=standard');
        
        $xlsx = storage_path('app/templates/Cases_Import_Template_Standard.xlsx');
        $this->assertFileExists($xlsx);
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($xlsx);
        $sheet = $spreadsheet->getSheetByName('Cases_Import_Template');
        
        // Check validation on status column (adjust column letter)
        $validation = $sheet->getCell('E2')->getDataValidation();
        $this->assertEquals(
            \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST,
            $validation->getType()
        );
    }

    /** @test */
    public function csv_has_utf8_bom_and_preserves_arabic()
    {
        $this->artisan('templates:generate-cases --mode=standard');
        
        $csv = storage_path('app/templates/Cases_Import_Template_Standard.csv');
        $content = file_get_contents($csv);
        
        // Assert UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        
        // Assert Arabic characters preserved
        $this->assertStringContainsString('مطالبة', $content);
    }

    /** @test */
    public function preflight_prefers_id_over_name()
    {
        // Create test client
        $client = Client::factory()->create([
            'client_name_ar' => 'شركة الاختبار',
            'client_name_en' => 'Test Company'
        ]);
        
        // Row with conflicting ID and Name
        $row = [
            'client_id' => $client->id,
            'client_name' => 'Different Company', // Conflict!
        ];
        
        $result = $this->preflightEngine->resolveFkWithPrecedence(
            $row, 'client_id', 'client_name', Client::class, ['client_name_ar', 'client_name_en']
        );
        
        // Assert ID wins
        $this->assertEquals($client->id, $result['resolved_id']);
        // Assert warning logged
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('conflict', strtolower($result['warnings'][0]));
    }

    /** @test */
    public function unauthorized_users_cannot_download_templates()
    {
        // No auth
        $response = $this->get(route('cases.template.standard.csv'));
        $response->assertStatus(403);
        
        // Auth but no permission
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('cases.template.standard.csv'));
        $response->assertStatus(403);
    }

    /** @test */
    public function authorized_users_can_download_templates()
    {
        $this->artisan('templates:generate-cases --mode=all');
        
        $user = User::factory()->create();
        $user->givePermissionTo('import.view_template');
        $this->actingAs($user);
        
        $response = $this->get(route('cases.template.standard.csv'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
```

---

## Acceptance Criteria (Updated)

1. ✅ Four template files exist and download correctly:
   - `Cases_Import_Template_Standard.csv`
   - `Cases_Import_Template_Standard.xlsx`
   - `Cases_Import_Template_Extended.csv`
   - `Cases_Import_Template_Extended.xlsx`

2. ✅ XLSX files use named ranges and validation **only for short enums** (<50 values)

3. ✅ README sheet shows:
   - Template Version
   - DB Schema Signature (MD5 hash)
   - Generation Timestamp (UTC)
   - ID vs Name precedence rules
   - Limitations (no dropdowns for large lists)

4. ✅ Preflight enforces **ID>Name precedence** and logs conflicts

5. ✅ CSVs are UTF-8 **with BOM**; Arabic displays correctly in Excel

6. ✅ Command `templates:generate-cases --mode=all` rebuilds all 4 files

7. ✅ Permissions enforced with `import.view_template` (returns 403, not 404)

8. ✅ Docs clearly explain:
   - Standard vs Extended column differences
   - ID vs Name mapping rules with examples
   - Short enum dropdowns vs large list free text

9. ✅ UI shows 4 download buttons with helpful descriptions

10. ✅ Admin regeneration button works (CSRF protected, permission checked)

11. ✅ All 6 tests pass:
    - Headers parity
    - XLSX validation presence
    - UTF-8 BOM + Arabic preservation
    - ID>Name precedence
    - Unauthorized 403
    - Authorized 200

---

## Commit Strategy

### Commit 1: Command with Standard/Extended Split
```
feat(templates): split into Standard/Extended; add --mode flag to generator

- Create GenerateCasesTemplate command with --mode=standard|extended|all
- Split columns: Standard (~25 core) vs Extended (~35 optional/legacy)
- Support ID and Name columns for all FKs (client, court, opponent, partner)
- Generate 4 files: Standard/Extended × CSV/XLSX
- Add versioning: template version, schema signature (MD5), timestamp
- Memory-friendly PhpSpreadsheet settings (MemoryGZip cache)

DoD: Command generates 4 template files with versioning metadata
```

### Commit 2: XLSX Named Ranges + Validation
```
feat(xlsx): named ranges + validation only for short enums; README with version/schema/timestamp

- Create Lookups sheet with named ranges (status, category, degree, importance, capacity)
- Apply data validation ONLY to short enums (<50 values)
- NO validation for large lists (courts, opponents) - use free text + preflight
- README sheet with 8 sections: versioning, formats, precedence, limitations
- UTF-8 BOM for CSV compatibility with Windows Excel

DoD: XLSX has dropdowns for short enums; README comprehensive
```

### Commit 3: Routes, Controller, UI
```
feat(ui): add four download buttons with helper text; admin regenerate action

- Add 4 download routes (Standard/Extended × CSV/XLSX)
- Add 4 controller methods with permission checks (import.view_template)
- Add admin regeneration route + controller method (admin.tools.manage)
- UI: 4 download buttons in upload view with Standard (recommended) / Extended (advanced)
- Admin tools page: Regenerate button with CSRF protection
- Return 403 (not 404) on unauthorized access

DoD: UI shows 4 downloads; admin can regenerate; permissions enforced
```

### Commit 4: Preflight ID vs Name Precedence
```
refactor(preflight): add ID vs Name precedence with conflict warnings

- Add resolveFkWithPrecedence() method in PreflightEngine
- Support both *_id and *_name columns for all FKs
- Precedence: ID wins if both provided; log WARNING on conflict
- Fuzzy matching for Name columns (AR/EN normalization)
- Update runPreflight() to use precedence for client, court, opponent, partner

DoD: Preflight resolves FKs correctly; logs warnings on conflicts
```

### Commit 5: Permissions + Seeder
```
refactor(permissions): use import.view_template; return 403 on unauthorized

- Add import.view_template permission to PermissionsSeeder
- Assign to super_admin, admin, lawyer roles
- Update all download routes to use new permission
- Update controller methods to return 403 (not 404) on unauthorized

DoD: Permission seeded; routes protected; 403 on unauthorized
```

### Commit 6: Documentation
```
docs(imports): expand with Standard vs Extended; ID vs Name precedence; i18n notes

- Create docs/imports/cases_template.md
- Section 1: Overview (Standard vs Extended)
- Section 2: Standard columns table (~25 fields)
- Section 3: Extended columns table (~35 fields)
- Section 4: ID vs Name precedence with examples
- Section 5: FK resolution (fuzzy matching AR/EN)
- Section 6: Enum dropdowns vs large list free text
- Section 7: File formats, UTF-8 BOM, bilingual examples
- Section 8: Download links, regeneration command, versioning

DoD: Documentation comprehensive and accurate
```

### Commit 7: Tests
```
test(templates): headers parity, validations, UTF-8 BOM, precedence, permissions

- Test: Template headers match DB schema (exclude id, timestamps, deleted_at)
- Test: XLSX has data validation on short enum columns
- Test: CSV has UTF-8 BOM and preserves Arabic characters
- Test: Preflight prefers ID over Name; logs warning on conflict
- Test: Unauthorized users get 403 (not 404)
- Test: Authorized users can download templates (200 OK)

DoD: 6 tests passing; coverage >80%
```

---

## To-dos

- [ ] Create GenerateCasesTemplate command with --mode flag and Standard/Extended split
- [ ] Add XLSX named ranges and validation for short enums only; comprehensive README sheet
- [ ] Add 4 download routes and controller methods with import.view_template permission
- [ ] Add UI with 4 download buttons (Standard/Extended × CSV/XLSX) and admin regenerate action
- [ ] Implement ID vs Name precedence logic in PreflightEngine with conflict warnings
- [ ] Add import.view_template permission to seeder and assign to roles
- [ ] Create comprehensive docs/imports/cases_template.md with Standard vs Extended sections
- [ ] Write 6 comprehensive tests (headers, validation, BOM, precedence, permissions)

