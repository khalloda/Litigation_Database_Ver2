# Step 4 — Replace `_x000D_` in Case Names
- Branch: `fix/client-details-cases`
- Commit: _(pending)_

## Commands
```
php artisan migrate
```

## Changes
- Added migration `2025_11_24_150500_replace_x000d_in_case_names.php` to replace the `_x000D_` artifacts in `cases.matter_name_ar` and `cases.matter_name_en` with a simple hyphen.
- Added migration `2025_11_24_152200_replace_x000d_in_case_notes.php` to apply the same cleanup to `notes_1`, `notes_2`, and `financial_provision`.

## Errors & Fixes
- None.

## Validation
- `php artisan migrate` — ✅

