# Step 3 — Client Case Status/Role Source Fix
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
- `ClientController@show` now eager-loads `cases` with `matter_status`, `client_capacity_id`, notes, and the `clientCapacity` relation.
- SPA `Case` types extended to include `matter_status`, `client_capacity_id`, etc. (mirrored in `clm-app/public/types.ts`).
- `ClientDetailPage` reads status strictly from `matter_status` and role text from the option value labels (or fallback notes).
- Rebuilt SPA assets and synced to Laravel public folder.

## Errors & Fixes
- None (Vite chunk warning persists, expected).

## Validation
- `npm run build` — ✅
- Manual QA: Client Associated Cases now display real status text (Arabic) and capacity labels.

