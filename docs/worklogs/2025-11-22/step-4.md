# Step 4 — CSV staging + dry run tooling
- Branch: (current)
- Commit: (pending)

## Commands
1. _Not run_: `php artisan test --filter=DocumentManagementTest` (blocked by Pest base-class issue noted earlier)

## Changes
- Created `client_documents_staging` table and `ClientDocumentStaging` model to hold raw CSV rows.
- Added migrations to introduce `legacy_document_id` (unique) on `client_documents`, ensuring deterministic upserts and disabling auto-increment so `id` == legacy `document_id` during backfills.
- Built two Artisan commands:
  - `documents:staging-import` — loads `DocumentsImport/Documents-Original.csv` into the staging table (handles Excel serial dates & booleans, supports append mode).
  - `documents:staging-process` — validates staged rows (dry run by default) and optionally persists them into `client_documents` (using `legacy_document_id` or `(client_id, deposit_date, document_description)` as fallback).
- Extended `ClientDocument`/`DocumentsImporter`, Blade UI, and translations to surface `legacy_document_id`, plus documented the workflow in the data dictionary/tasks index.

## Errors & Fixes
- Pest bootstrap issue (`Tests\Feature\Tests\TestCase` missing) still blocks automated test execution; rerun pending upstream fix.

## Validation
- Manual review of staging commands + schema changes. Plan to execute: `php artisan documents:staging-import`, `php artisan documents:staging-process --dry-run`, followed by `--execute` once dry run reports zero issues.

