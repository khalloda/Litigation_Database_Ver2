
# Prompt — Import/Export Module with Column Mapping, Preflight, and Mandatory DB Backup

You are working on **Central Litigation Management** (Laravel 10, PHP 8.4, MySQL 9.1, Bootstrap 5, EN/AR + RTL) on a **cPanel** host (cron available; no Docker; binaries may be restricted). Build a secure **Import/Export** module that supports **XLS/XLSX/CSV** with a **column mapping UI**, **mandatory preflight validation**, **sample templates**, and **automatic full DB backup** before any import.

---

## 0) Guardrails & Scope

- **RBAC**: Only roles `super_admin` and `admin` can use this module.
- **Tables** (include by default; make togglable in config): `clients`, `cases` (or `matters`), `contacts`, `lawyers`, `hearings`, `engagement_letters`, `power_of_attorneys`, `admin_work_tasks`, `admin_work_subtasks`, `clients_matters_documents` (metadata only; file binaries are NOT imported here).
- **Exclude** from manual import: pivot tables (e.g., case ↔ lawyer). We’ll handle relationships via columns on the main table or add a dedicated wizard later.
- **File types**: `.xlsx`, `.xls`, `.csv` (UTF‑8). **Max size: 10 MB** (enforce existing system cap).
- **Execution model (cPanel-friendly)**: web UI for upload/mapping/preflight → queue job for import execution → progress via polling. Use Laravel scheduler via **cron**.

---

## 1) Data Safety — Mandatory Full DB Backup

Before starting any import job, create and verify a full database backup (`.sql`).

- Try `mysqldump` first; otherwise use a **PHP fallback exporter** that streams schema + data INSERTs to a file.  
- Store under `storage/app/backups/db-YYYYMMDD-HHmm.sql`. Record SHA1 checksum.
- If backup fails or file size is 0 → **abort import with clear error**.
- Log backup path and checksum on the **ImportSession** record and the activity log.

**Config (`config/importer.php`):**
```php
return [
  'backup' => [
    'enabled' => true,
    'driver' => env('IMPORT_BACKUP_DRIVER', 'auto'), // auto|mysqldump|php
    'max_age_days' => 30, // TTL for cleanup
  ],
  'limits' => [
    'max_upload_mb' => 10,
    'chunk_rows' => 2000,
  ],
  'enabled_tables' => [
    'clients', 'cases', 'contacts', 'lawyers', 'hearings',
    'engagement_letters', 'power_of_attorneys',
    'admin_work_tasks', 'admin_work_subtasks', 'clients_matters_documents',
  ],
];
```

---

## 2) Domain Model — Import Sessions & Artifacts

Create `import_sessions` table + model to track every run:

- `id`, `user_id`, `table`, `filename`, `filesize`, `mimetype`
- `mapping_json` (final column mapping + transforms), `options_json` (mode, FK options, etc.)
- `preflight_report_path`, `rejects_csv_path`, `summary_json`
- `backup_sql_path`, `backup_sha1`
- `status` enum(`uploaded`,`preflight_ok`,`running`,`completed`,`failed`,`aborted`)
- Timestamps + activity logging

Also create a `sample_templates` generator (see §4).

---

## 3) Import UI — Upload, Mapping, Preflight

### Upload
- Accept XLS/XLSX/CSV, enforce size + MIME, store to `storage/app/imports/{uuid}/original.{ext}`.

### Mapping UI
- Detect headers; propose auto-mapping using:
  - Case-insensitive header similarity (Levenshtein/Jaro-Winkler) to DB column names.
  - Type hints by sampling first N rows.
- For each **source column**, show a dropdown of **DB columns** for the selected table plus “Ignore”.
- Show **badges** next to DB columns: type (string/int/decimal/date/bool/enum/fk), **required** flag, **unique** flag.
- **Transform presets** per mapped column:
  - Trim, titlecase/uppercase
  - Date parse formats (dd/mm/yyyy, yyyy-mm-dd, dd-mm-yyyy, yyyy/mm/dd, etc.)
  - Boolean mapping (Yes/No/TRUE/FALSE/1/0 + Arabic equivalents “نعم/لا”)
  - Enum mapping (select from allowed values)
  - FK resolution method: **by ID** or **by natural key** (e.g., `client_name`)
- Save the proposed mapping to `mapping_json`; allow user to edit before preflight.

### Preflight (soft check; no writes)
Validate **all rows** then produce a downloadable report + on-screen summary:

- **Coverage**: required columns mapped? unmapped source columns listed?  
- **Type checks**: each mapped value convertible to target type?  
- **Constraints**: unique collisions, not-null violations, length/decimal precision, enum validity.  
- **FK resolution**: ensure referenced rows exist (based on chosen strategy).  
- **Upsert key**: resolve per-table (see §5).  
- **Row outcomes**: `will_insert`, `will_update`, `will_skip` (with reasons).  
- **Counts & examples**: first 25 error rows per rule; full details in CSV.

Output files:
- `preflight_report.md` (human summary + decisions)
- `rejects.csv` (rows with reasons; UTF-8 w/ BOM)

Status transitions: `uploaded` → `preflight_ok` only if **zero hard errors**, or user explicitly chooses **“Import valid rows, skip bad ones”** (checkbox).

---

## 4) Sample Templates & Exports

### Sample XLSX per table
- Route: “Download Sample Template”
- Generated workbook:
  - First sheet: **Headers** for that table.  
  - Row 2: **example values** (EN+AR where relevant).  
  - Row 3: **notes** (type hints, required flags, constraints).  
- Save a copy under `storage/app/import-templates/{table}.xlsx` for reuse.

### Export
- Per table export to **XLSX** or **CSV** with filters (date range, search, sort).  
- Optionally include related display columns (e.g., **Cases** export can include **Client Name**).  
- Exports are streamed; files saved to `storage/app/exports/{uuid}.{ext}`; log audit event `export.generated`.

---

## 5) Import Execution — Modes, FK Rules, Chunking

### Modes (per run)
- **Insert only**, **Update only**, **Upsert (insert+update)**, **Skip duplicates**.

### Upsert keys (default suggestions; make configurable per table)
- `clients`: unique by (`client_name_en` or `client_name_ar`) + optional country
- `cases`: case number (or a composite natural key if defined); otherwise ID-only
- `contacts`: email or phone (if unique), else name+client
- `lawyers`: email or bar_number
- `hearings`: case_id + hearing_date + location (if unique in your data)
- others: choose the most stable natural key available; fall back to ID when provided

### Foreign keys
- Resolution method per mapped FK column:
  - **by ID** (fastest, strict)
  - **by natural key** (lookup using a unique column, e.g., client name)
- On unresolved FK: default **reject row**; alternatives: set NULL (if allowed) or skip with warning (configurable).

### Chunking & transactions
- Process in chunks of `chunk_rows` (default 2000).  
- Wrap each chunk in a DB transaction.  
- Option `all_or_nothing`: if true, rollback entire job on any error; else continue and summarize failures.

### Queue & progress
- Import is a **queued job**; progress stored in `summary_json` (rows processed, inserts, updates, rejects, elapsed).  
- UI polls every few seconds; show a live progress bar and link to rejects CSV when done.

---

## 6) Controllers, Routes, Views (Bootstrap 5; EN/AR)

- Sidebar: **Import/Export** module with two tabs: **Import** | **Export**.
- **Import flow views**: Upload → Mapping → Preflight → Start Import → Live Progress → Summary.
- EN/AR copies and RTL-friendly layout.
- Buttons: “Download Sample Template”, “Download Preflight Report”, “Download Rejects CSV”, “Download Backup SQL” (RBAC-protected).

---

## 7) Services & Jobs

- `ImportService`: parse files (use **Spout** or **PhpSpreadsheet** for XLS/XLSX; native PHP for CSV); build mapping plan; preflight; schedule job.
- `BackupService`: `tryMysqldump()` then `exportViaPhpFallback()`; compute SHA1; return path.
- `ImportJob`: executes with chunking and transactions; writes summary; generates rejects CSV.
- `ExportService`: filtered exports to XLSX/CSV; sample template generator.

---

## 8) Security, Logging, and Auditing

- Validate uploads: MIME + extension; size; optionally AV-scan.  
- Signed, short-lived routes for downloading artifacts (reports, rejects, backups, exports).  
- Activity log events: `import.started`, `import.completed`, `import.failed`, `import.aborted`, `export.generated`.  
- Store all config/mapping used in `import_sessions.mapping_json` to reproduce runs.

---

## 9) Tests (Pest)

- **Unit**: mapping resolver, type coercion, boolean/date parsing, enum mapping, FK lookup (by ID & by natural key).  
- **Feature**: end-to-end small fixtures for 3 representative tables (Clients, Cases, Documents metadata).  
- **Backup**: fallback exporter used when `mysqldump` is unavailable; file exists and non-empty.  
- **Preflight**: rejects for null required fields, type errors, duplicate unique keys, unresolved FKs.  
- **Queue**: chunked upsert + progress JSON updates.  
- **Security**: RBAC enforced; signed downloads only.

---

## 10) Documentation

- `/docs/runbooks/Import_Export_Runbook.md`: step-by-step guide, screenshots, troubleshooting.  
- `/docs/adr/ADR-YYYYMMDD-Import-Strategy.md`: why preflight, upsert keys, FK resolution, and backup approach (cPanel constraints).  
- `/docs/tasks-index.md`: add tasks (below) and keep statuses updated.

---

## 11) Branch & Commit Plan

1. `feat/import-sessions-schema` — migrations, model, config, policies/permissions  
2. `feat/import-ui-mapping-preflight` — upload, mapping UI, preflight engine + reports  
3. `feat/import-backup-service` — mysqldump + PHP fallback, verification, wiring  
4. `feat/import-job-execution` — queue job, chunking, progress, rejects CSV  
5. `feat/export-and-templates` — per-table export + sample XLSX generator  
6. `test/import-export` — Pest coverage & fixtures  
7. `docs/import-export-runbook` — runbook + ADR + tasks-index updates

**Commit style (Conventional):**
```
feat(import): add mapping UI and preflight checks for clients table
```
Include a short body with the table, key decisions, and artifacts written.

---

## 12) Definition of Done (DoD)

- Only `super_admin|admin` can access the module.  
- Upload → Mapping → Preflight → Import works for XLSX/CSV, ≤10 MB.  
- **Backup `.sql` is created and verified** before import (aborts if missing).  
- Preflight report and rejects CSV downloadable and localized (EN/AR).  
- Upsert/insert/update modes honored; FK resolved per options.  
- Chunked import executes on queue with progress and final summary.  
- Sample XLSX templates available for each enabled table.  
- Exports produce XLSX/CSV with optional related display columns.  
- Activity log records all major events.  
- Tests green; docs updated.

---

**Now begin with branch `feat/import-sessions-schema` and ask me to create it. Propose the branch name and confirm before executing.**
