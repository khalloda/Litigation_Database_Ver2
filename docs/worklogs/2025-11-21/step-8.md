# Step 8 — Hearings schema-driven detail views
- Branch: feat/spa-all-entities
- Commit: 667d799425cd76754940b9f899c5bf44603d28c2

## Commands
1. `cmd /c "cd /d D:\Claude\...\AiStudio-CLMS2 && npm run build"`
2. `cmd /c "copy /Y D:\Claude\...\AiStudio-CLMS2\dist\assets\index-BzUyWJ2L.js D:\Claude\...\clm-app\public\assets\index-BzUyWJ2L.js"`
3. Additional `copy /Y` commands to sync CSS + `public/dist` assets and update `public/index.html`

## Changes
- Added audit relationships to `Hearing`, expanded API `HearingController@show` to load case/lawyer/audit, emit schema + raw payload, and exposed `/api/hearings/{hearing}/schema`.
- Updated hearing services (AiStudio + Laravel copy) with `fetchHearingSchema`.
- Hearing detail pages in both SPAs now track schema/raw state, include an “All Fields” tab powered by `AllFieldsTable`, and show friendly FK labels (case, lawyer).
- Rebuilt SPA bundle (`index-BzUyWJ2L.js`), refreshed Laravel assets, and logged this step.

## Errors & Fixes
- None; schema fetch and asset sync completed cleanly.

## Validation
- Pending manual: hard refresh `/hearings/{id}` in Blade and SPA to confirm the All Fields tab/table renders all columns with FK labels and developer tools show the raw JSON.

