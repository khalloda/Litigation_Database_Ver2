# Step 1 — Report Status Filter
- Branch: `fix/display-count`
- Commit: _(pending)_

## Commands
```
cd AiStudio-CLMS2 && npm run build
robocopy AiStudio-CLMS2\dist clm-app\public /MIR
```

## Changes
- `app/Http/Controllers/Api/ReportController.php`
- `public/pages/ReportsPage.tsx` (mirrored in `AiStudio-CLMS2`)
- `public/locales/*.json` (EN/AR + AiStudio counterparts)
- `resources/lang/{en,ar}/app.php`
- `docs/reports.md`, `docs/tasks-index.md`
- `tests/Feature/Reports/ClientCasesReportTest.php`
- SPA build artifacts under `clm-app/public`

## Errors & Fixes
- None. SPA build emitted a size warning (>500 kB) which is expected and unchanged.

## Validation
- `npm run build` (Vite) — ✅
- `php artisan test` — ⚠️ still blocked by existing `Tests\Feature\Tests\TestCase` issue (unchanged)

