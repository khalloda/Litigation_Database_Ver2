# Step 2 — Document metadata parity & CSV readiness
- Branch: (current)
- Commit: (pending)

## Commands
1. _Not run_: `php artisan test` (blocked by existing Pest base class issue noted in Step 1)

## Changes
- Added migration `2025_11_22_101500_add_department_and_document_location_to_client_documents_table.php` to introduce `department`, `admin_staff`, `lawyer`, and `document_location` columns (with backfill from `clients.documents_location`).
- Extended `ClientDocument` model fillable/logged attributes and introduced an automatic `document_location` sync hook before persistence.
- Updated `DocumentUploadRequest`, web/API controllers, Blade create/edit/show views, and translation files to capture/display the new metadata.
- Enhanced `DocumentsImporter` mappings so both the XLSX sheet and the legacy CSV headers hydrate the additional columns.
- Strengthened `DocumentManagementTest` to ensure client document location snapshots persist, and refreshed docs (`data-dictionary.md`, `tasks-index.md`) for parity.

## Errors & Fixes
- `php artisan test` remains blocked by the existing Pest autoload issue (`Tests\Feature\Tests\TestCase` missing). Deferred full suite execution; only static analysis performed on touched files.

## Validation
- Manual review of Blade forms + schema changes.
- Test plan: rerun `php artisan test` once the upstream Pest base class fix lands to cover `DocumentManagementTest` updates.

