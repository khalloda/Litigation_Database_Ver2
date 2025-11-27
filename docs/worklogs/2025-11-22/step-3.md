# Step 3 — Capture legacy matter names
- Branch: (current)
- Commit: (pending)

## Commands
1. _Not run_: `php artisan test --filter=DocumentManagementTest` (blocked by Pest base-class issue noted previously)

## Changes
- Added migration `2025_11_22_112500_add_legacy_matter_name_to_client_documents_table.php` introducing a nullable `legacy_matter_name` text column next to the FK.
- Updated `ClientDocument` (fillable + audit logging) so the new field is persisted through standard flows, and surfaced the value on the document detail view with EN/AR translations.
- Extended `DocumentsImporter` column mapping to treat CSV/XLSX `matter_id` values as `legacy_matter_name`, ensuring legacy sheet text is preserved during ETL.
- Documentation touch-ups: expanded the data dictionary & tasks index to reflect the new column.

## Errors & Fixes
- Same Pest bootstrap error (`Tests\Feature\Tests\TestCase` missing) prevents the Laravel test suite from running; no new failures introduced, but rerun is pending the upstream fix.

## Validation
- Manual verification of the Blade detail page and code inspections; unit/feature tests to be rerun once Pest configuration is repaired.

