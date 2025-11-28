# Step 3 — All-fields data integrity
- Branch: feat/show-all-fields-details
- Commit: 7e5a5116543cad4fdc24c591d11be4d340a5fda9

## Commands
1. `cmd /c "cd /d D:\Claude\...\AiStudio-CLMS2 && npm run build"`
2. `cmd /c "copy /Y D:\Claude\...\AiStudio-CLMS2\dist\assets\index-COM1VA4g.js D:\Claude\...\clm-app\public\assets\index-COM1VA4g.js"`
3. Additional `copy /Y` commands to sync CSS + `dist` artifacts and update `public/index.html`

## Changes
- API `CaseController@show` now returns `raw` (full model array) alongside the transformed DTO and schema metadata.
- React Case detail page (AiStudio + public copy) stores this raw payload and feeds it to the schema-driven table so every DB column (e.g., `engagement_letter_no`) renders correctly.
- Rebuilt the SPA bundle, deployed `index-COM1VA4g.js`, and pointed Laravel’s entry HTML to the new hash.

## Errors & Fixes
- None; build + asset sync succeeded using the prior `cmd /c` approach.

## Validation
- Pending manual: reload `/cases/1116` in the SPA (hard refresh) and verify the All Fields JSON shows non-null values like `engagement_letter_no = 181`.

