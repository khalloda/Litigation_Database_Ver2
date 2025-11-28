Request: Generate Cases Import Templates (CSV + XLSX) From the Current Database Schema + Download Links

Context

App: Central Litigation Management (Laravel 10, PHP 8.4, MySQL 9.1, Bootstrap 5)

DB: litigation_db_ver2 (utf8mb4_unicode_ci)

Host: localhost:3306 | user: root | pass: 1234

Import Module: has preflight + bilingual normalization + fuzzy suggestions

Goal: Build Cases import templates (CSV + XLSX) that exactly match the current database schema, and make them downloadable from the Cases Import page. Use the attached SQL dump to discover columns and constraints if connecting to the DB is not possible.

Inputs available

MySQL dump: "Litigation_Database_Ver2\DB_DUMPlitigation_db_ver2 (23Oct2025-230PM).sql" (use this to introspect if you cannot connect to MySQL)

Branch: "feat/import-templates-cases-from-db" created and based on the current "feat/import-fuzzy-matching-bilingual"

What to Generate (Deliverables)

Schema-driven CSV template (UTF-8)

Path: storage/app/templates/Cases_Import_Template.csv

Columns: exactly the columns of the cases table (ordered as in schema), excluding purely internal/managed fields (id, timestamps if auto-managed, soft delete deleted_at if present, audit fields created_by/updated_by unless required by business rules).

Include 2 sample rows (AR + EN examples).

If any NOT NULL column has no default and is not auto-generated, mark it “Required” in the spec and leave a reasonable sample value.

Schema-driven XLSX template

Path: storage/app/templates/Cases_Import_Template.xlsx

Sheet Cases_Import_Template with the same columns and sample rows as CSV.

Sheet Lookups (enumerations inferred from foreign-key lookup tables or known enums like status, case_type, currency, stage).

Sheet README (spec text below).

Add basic data validation lists on enum fields (pointing to Lookups).

Download routes & UI buttons on the Cases Import page (Blade)

CSV: /cases/import/template/csv

XLSX: /cases/import/template/xlsx

Artisan command to regenerate both files from the live schema (or from the SQL dump if DB not reachable):

php artisan templates:generate-cases-from-db

Docs: docs/imports/cases_template.md explaining the columns, required fields, how the file was inferred from schema, where to download, and how to regenerate.

How to Infer the Columns (Use the DB or the SQL dump)

Primary approach (preferred): connect to MySQL and read information_schema:

Columns:
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLUMN_KEY, COLLATION_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA='litigation_db_ver2' AND TABLE_NAME='cases'
ORDER BY ORDINAL_POSITION;
FKs:
SELECT
  kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME,
  rc.UPDATE_RULE, rc.DELETE_RULE
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
  ON rc.CONSTRAINT_SCHEMA=kcu.CONSTRAINT_SCHEMA AND rc.CONSTRAINT_NAME=kcu.CONSTRAINT_NAME
WHERE kcu.TABLE_SCHEMA='litigation_db_ver2' AND kcu.TABLE_NAME='cases' AND kcu.REFERENCED_TABLE_NAME IS NOT NULL;
Fallback (if DB connection is not available): parse /mnt/data/litigation_db_ver2.sql

Extract CREATE TABLE \cases` ...block, list column lines, identify keys, and anyCONSTRAINT ... FOREIGN KEY ... REFERENCES ... ON UPDATE/DELETE ...`.

Inclusion rule:

Include columns that users can/should provide on import (business data).

Exclude:

Auto PK (id)

Auto timestamps if handled automatically (created_at, updated_at) unless business requires them

Soft delete column (deleted_at)

System-only or protected fields (e.g., internal counters, hash, derived columns)

Mark as Required any NOT NULL column with no default, unless flagged as auto-increment or computed.
If a column has a default (including CURRENT_TIMESTAMP) and can be omitted, mark it “Optional”.

Normalization & Enums

Try to infer enumerations:

If status, case_type, stage, currency exist, standardize allowed values:

status: open | stayed | closed | appeal | execution (override if DB uses a different set)

case_type: Civil | Commercial | Criminal | Administrative (override if DB uses a different set)

stage: First Instance | Appeal | Cassation | Execution (override if DB uses a different set)

currency: EGP | USD | EUR | GBP | SAR | AED (override if DB uses a different set)

For foreign keys (e.g., client_id, court_id, etc.), show either the FK id or a name field in the template if present in functional requirements. If you only accept ids, call that out in the README and in the preflight generate suggestions for name-based mapping where possible.

Fallback (if DB connection is not available): parse /mnt/data/litigation_db_ver2.sql

Extract CREATE TABLE \cases` ...block, list column lines, identify keys, and anyCONSTRAINT ... FOREIGN KEY ... REFERENCES ... ON UPDATE/DELETE ...`.

Inclusion rule:

Include columns that users can/should provide on import (business data).

Exclude:

Auto PK (id)

Auto timestamps if handled automatically (created_at, updated_at) unless business requires them

Soft delete column (deleted_at)

System-only or protected fields (e.g., internal counters, hash, derived columns)

Mark as Required any NOT NULL column with no default, unless flagged as auto-increment or computed.
If a column has a default (including CURRENT_TIMESTAMP) and can be omitted, mark it “Optional”.

Normalization & Enums

Try to infer enumerations:

If status, case_type, stage, currency exist, standardize allowed values:

status: open | stayed | closed | appeal | execution (override if DB uses a different set)

case_type: Civil | Commercial | Criminal | Administrative (override if DB uses a different set)

stage: First Instance | Appeal | Cassation | Execution (override if DB uses a different set)

currency: EGP | USD | EUR | GBP | SAR | AED (override if DB uses a different set)

For foreign keys (e.g., client_id, court_id, etc.), show either the FK id or a name field in the template if present in functional requirements. If you only accept ids, call that out in the README and in the preflight generate suggestions for name-based mapping where possible.

Sample Rows (include in both CSV & XLSX)

One Arabic-heavy and one English-heavy example. Adapt field names to the actual columns you discover (below are examples—replace as needed to match the real cases schema):
LEGACY-1001,"2024/مدني/12345","مطالبة مالية ضد سبيد ميديكا",,"C-778","شركة سبيد ميديكال ش.م.م","سبيد ميديكا",,"القاهرة الاقتصادية",Commercial,Healthcare,2024-11-20,2024-11-15,,open,"First Instance",EGP,250000.00,0.00,"khelmy@sarieldin.com","litigation,urgent","EL-2024-09-15-003","\\\\fileserver\\clients\\speed\\case_12345","\\\\fileserver\\clients\\speed\\poa","قضية مستعجلة؛ تأكيد صحة الاسم عند الاستيراد عبر التطابق التقريبي."
LEGACY-1002,"2023/تجاري/9876","Breach of Contract vs Speed Medical SAE",,, "Speed Medical SAE","Speed Med.",,Cairo Economic Court,Commercial,Contracts,2023-06-01,2023-05-20,,open,"First Instance",EGP,125000.50,10000.00,"lawyer@example.com","litigation",,"s3://clm-bucket/docs/cases/9876",,"English naming; verify fuzzy match of opponent during preflight."

Replace headers and values to align exactly with the live cases columns and any renamed fields (e.g., poa_location vs power_of_attorney_location, documents_location vs docs_location etc.).
Implementation Tasks
A) Artisan command

php artisan templates:generate-cases-from-db

Connect to DB (or read the SQL dump) and gather:

Column list with types, nullability, defaults, extra (auto-increment), collation

PK, indexes, FKs (table, column, on-delete/on-update rules)

Build column metadata → decide Required/Optional using the inclusion rules above.

Write CSV: headers + 2 sample rows.

Write XLSX:

Sheet Cases_Import_Template: headers + 2 sample rows

Sheet Lookups: rows like field,allowed_values for each inferred enum

Sheet README: paste the spec below (updated to real columns)

Add data validation lists for enum columns

Ensure directory storage/app/templates/ exists.

B) Routes
Route::get('/cases/import/template/csv', function () {
    $path = storage_path('app/templates/Cases_Import_Template.csv');
    abort_unless(file_exists($path), 404);
    return response()->download($path, 'Cases_Import_Template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
})->name('cases.template.csv');

Route::get('/cases/import/template/xlsx', function () {
    $path = storage_path('app/templates/Cases_Import_Template.xlsx');
    abort_unless(file_exists($path), 404);
    return response()->download($path, 'Cases_Import_Template.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
})->name('cases.template.xlsx');
C) Blade UI
<div class="alert alert-light border d-flex align-items-center gap-3">
  <div>
    <div class="fw-semibold mb-1">Cases Import Templates</div>
    <div class="small text-muted">Download CSV or Excel template generated from the current database schema.</div>
  </div>
  <div class="ms-auto btn-group">
    <a href="{{ route('cases.template.csv') }}" class="btn btn-outline-primary btn-sm">Download CSV</a>
    <a href="{{ route('cases.template.xlsx') }}" class="btn btn-outline-secondary btn-sm">Download XLSX</a>
  </div>
</div>
D) Docs
docs/imports/cases_template.md: paste the exact column list with types, Required/Optional, and any enum values pulled from the DB. Include a note on regenerating:

php artisan templates:generate-cases-from-db

README / Spec (paste into XLSX README sheet & docs)

Encoding: UTF-8
Date format: YYYY-MM-DD (ISO)
Decimals: dot separator (e.g., 125000.50)
Language: Arabic and/or English allowed
Requirement rule: Any NOT NULL non-defaulted column in the cases table template is required.

Validation & Preflight

Datatype + required checks.

Foreign-key assistance: for client_id, court_id, etc., importer offers lookups or fuzzy suggestions when names are supplied instead of IDs (when supported).

Arabic/English normalization and fuzzy suggestions for party names (e.g., opponent_name) and (optionally) court_name.

Unknown enumerations are flagged with suggestions from Lookups.

Tips

Keep headers exactly as generated.

Avoid Excel auto-formatting of IDs (prefix with ' if needed).

Save CSVs as UTF-8.

Testing / Acceptance Criteria

/cases/import/template/csv → downloads a CSV with headers exactly matching current cases columns (excluding internal/system columns) + 2 sample rows.

/cases/import/template/xlsx → downloads a workbook with 3 sheets (Cases_Import_Template, Lookups, README).

XLSX has data validation lists on enum-like columns (status, case_type, stage, currency or others discovered in DB).

Template regeneration picks up future schema changes automatically via the command.

Docs reflect the real columns (names, data types, required/optional) and match the template.

Commit Plan (small PRs)

feat(templates): add templates:generate-cases-from-db (introspect schema or parse SQL dump)

feat(routes): add download endpoints for cases templates

feat(ui): add template download buttons to cases import page

docs(imports): add cases_template.md (schema-driven)

(optional) chore(seeds): generate templates on fresh install

After each commit: ask to continue. For new features/bugs/fixes, prompt me to create a new branch with a suitable name.