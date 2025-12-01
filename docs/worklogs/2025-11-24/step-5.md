# Step 5 — Documents List Enhancements
- Branch: `fix/client-details-cases`
- Commit: _(pending)_

## Commands
```
cd AiStudio-CLMS2 && npm run build
Remove-Item clm-app\public\assets\* -Recurse -Force
robocopy AiStudio-CLMS2\dist\assets clm-app\public\assets /E
Copy-Item AiStudio-CLMS2\dist\index.html clm-app\public\index.html -Force
```

## Changes
- Fixed initial blank state by loading the full documents dataset via `fetchAllPages` and ensuring the memo re-runs when the source records change.
- Added Department / Admin Staff / Lawyer fields to the documents list & detail views, wired them into filters, and exposed translations.
- Renamed the Type column to Storage Type (based on `document_storage_type`), surfaced Description in the list, and kept detail view aligned.
- Introduced sortable columns (document name, deposit date, client, case) with UI controls.
- Added new filters for department/admin staff/lawyer/responsible lawyer plus sorting translations in EN/AR.

## Errors & Fixes
- None.

## Validation
- `npm run build` — ✅

