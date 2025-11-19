# DELTA — Harden Hearings Import (staging, parsing, FK resolution, QA gates)

**Goal:** Upgrade the existing pipeline to (a) import into a staging table first, (b) harden parsing/normalization (esp. `الدائرة`), (c) enforce deterministic FK resolution with review artifacts, and (d) gate final inserts with the QA checklist.

## 1) DB — Staging + indexes
- Create table `hearings_staging` mirroring import template columns:
  - Columns: `id` (INT), `matter_id` (INT NOT NULL), `lawyer_id` (INT NULL), `date` (DATE NOT NULL), `procedure` (VARCHAR(255)), `court` (VARCHAR(255)), `circuit` (VARCHAR(255)), `destination` (VARCHAR(255)), `decision` (TEXT), `short_decision` (VARCHAR(255)), `last_decision` (VARCHAR(255)), `next_hearing` (DATE NULL), `report` (TINYINT(1) DEFAULT 0), `notify_client` (TINYINT(1) DEFAULT 0), `attendee`…`attendee_4` (VARCHAR(255)), `next_attendee` (VARCHAR(255)), `evaluation` (VARCHAR(255)), `notes` (TEXT).
  - Audit columns: `source_file` (VARCHAR(255)), `source_row` (INT), `mapping_profile` (VARCHAR(128) NULL), `transform_warnings` (JSON NULL), `loaded_at` (DATETIME DEFAULT NOW()).
  - Indexes: `idx_stg_matter_id`, `idx_stg_lawyer_id`, `idx_stg_date`, `idx_stg_id_unique` (unique if feasible).

## 2) Parser/Normalizer (PHP: a single service class)
- Implement centralized helpers:
  - `cleanString(string $v): ?string` → remove `_x000D_`, ZWSP, dir marks, collapse spaces, normalize Arabic digits → ASCII.
  - `parseDate(string $v): ?string` → accept `DD/MM/YYYY`, `YYYY/MM/DD`, `YYYY-MM-DD`.
  - `parseBoolean(mixed $v): bool` → TRUE/FALSE normalization.
  - `parseCircuit(string $v, ?string $nameCol=null, ?string $notesCol=null, ?string $serialCol=null): array{circuit:?string, notes:?string, serial:?int}` with **three regex tiers**:
    1. Strict: `^(\d+)\s*-\s*(.+?)\s+([^\d]+)$`
    2. Common: `^(\d+)\s*(?:-|\s+)?\s*(?:رول\s*\((\d+)\)\s*)?(.+)$`
    3. Fallback: leading digits → serial; remainder → name.
  - Ensure Arabic numerals (٠١٢…٩) convert to 0–9 before regex.

## 3) Deterministic FK resolution
- Build lookup maps at run start:
  - Courts, Circuit Names, Lawyers (AR & EN), Cases (ids), etc. from live DB.
- Resolution order for any name→ID:
  1) Exact (case/space-insensitive)  
  2) Alias tables (if present)  
  3) Manual mapping CSV (if provided)  
  4) **Optional** fuzzy (Levenshtein ≥ 0.90) → write to `_unmatched.csv` for review; do **not** auto-assign
- Always attach `source_file`, `source_row`, and `transform_warnings` for any non-exact match.

## 4) Pipeline changes
- Write transformed rows to `hearings_staging` only.
- Add CLI: `php artisan hearings:preflight {--file=}` to:
  - Load Excel/CSV → transform → insert into staging.
  - Emit artifacts in `storage/app/imports/hearings/YYYYMMDD-HHMM/`:
    - `*_db_mappings.csv` (IDs→names),
    - `*_unmatched.csv` (per field),
    - `*_errors.json`, `*_summary.json`.
- Add CLI: `php artisan hearings:validate-staging` to run **all** QA SQL from the “Hearings Import QA Checklist” and exit **non-zero** if any high-severity issue (duplicate `id`, orphan `matter_id`, invalid dates). Use the checklist as source-of-truth for the queries.
- Add CLI: `php artisan hearings:commit-staging {--batch=2000}`:
  - Re-verify gate conditions.
  - Insert into `hearings` in batches (transaction per batch).
  - Log rows moved, timing, and a sample diff of 10 records.

## 5) Safety & backups
- Before `commit-staging`, run `mysqldump` of `hearings` (and `cases`, `lawyers` if desired) to `storage/app/backup/…`.
- On any failure, abort batch and leave staging intact.

## 6) Docs
- Update `Hearings_Compatibility_Report.md` with:
  - final regex set + examples,
  - the exact validation SQL we executed,
  - counts (inserted, skipped, unmatched).
- Update `Hearings_QA_Checklist.md` to reference new CLI commands and directories for artifacts.

## 7) Branch & commits
- Branch: `feat/hearings-import-staging-and-qa`
- Commits (suggested):
  1) `chore(db): add hearings_staging table + indexes + backups dir`
  2) `feat(import): parsing/normalization helpers (+ circuit robust regex)`
  3) `feat(import): FK dictionary loaders + deterministic resolver`
  4) `feat(cli): hearings:preflight + hearings:validate-staging + hearings:commit-staging`
  5) `docs(import): update compatibility report + QA checklist`
