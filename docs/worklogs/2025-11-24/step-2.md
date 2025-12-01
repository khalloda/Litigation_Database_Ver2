# Step 2 — Fix Client Associated Cases
- Branch: `feat/report-filter`
- Commit: _(pending)_

## Commands
```
cd AiStudio-CLMS2 && npm run build
Remove-Item clm-app\public\assets\* -Recurse -Force
robocopy AiStudio-CLMS2\dist\assets clm-app\public\assets /E
Copy-Item AiStudio-CLMS2\dist\index.html clm-app\public\index.html -Force
```

## Changes
- `AiStudio-CLMS2/pages/ClientDetailPage.tsx` (and mirrored SPA file) now fall back to `matter_name`, `matter_status`, and role notes.
- Added `status.undefined` + `client_page.role_unknown` translations (EN/AR JSON + mirrored public JSON).
- Rebuilt SPA assets and synced to `clm-app/public`.

## Errors & Fixes
- None (Vite build still warns about >500 KB chunk, unchanged).

## Validation
- `npm run build` — ✅
- Manual verification: associated cases now show names/status/roles with graceful fallbacks.

