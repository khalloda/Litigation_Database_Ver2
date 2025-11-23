# Step 1 — Client PDF Report
- Branch: current (`import/documents`)
- Date: 2025-11-23

## Commands
- `php artisan documents:staging-import --path=../DocumentsImport/Documents-Original.csv`
- `php artisan documents:staging-process`
- `php artisan documents:staging-process --execute`
- `php artisan tinker --execute="var_export(DB::table('client_documents')->count());"`
- `php artisan test --filter=ClientCasesReportTest` *(fails due to legacy Pest bootstrap issue — documented in status)*

## Changes
- Added Snappy config + composer dependency for wkhtmltopdf.
- New API route/controller (`ReportController@clientCasesPdf`) with `reports.view` permission.
- Blade template replicating Toyota report layout with conditional columns/totals.
- React reports page now offers client dropdown, column toggles, and PDF download workflow.
- Feature test for client PDF (Snappy facade mocked) + documentation (`docs/reports.md`, tasks index, this worklog).

## Notes & Risks
- `wkhtmltopdf` must exist on hosts; `.env` variables documented.
- Full test suite currently blocked by Pest bootstrap (“`Tests\Feature\Tests\TestCase` not found”).
- UI download relies on Axios `blob` response; ensure auth tokens available.
- Additional styling tweaks (logos/fonts) can be applied by dropping assets under `public/` if desired.

