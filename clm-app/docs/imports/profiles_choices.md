# Import Profiles & Saved Choices

This feature lets you save import decisions (match/alias/capacity/ignore) and auto‑apply them during preflight.

## Concepts
- Profile = named set of choices for a table, optionally tied to a header hash (sha256 of sorted headers).
- Choice key = (table_name, column, normalized_value). Normalization collapses whitespace and strips diacritics.

## Workflow
1. Preflight auto‑selects a matching profile by (`table_name`, `header_hash`).
2. Choices are applied before validation to reduce errors.
3. On preflight, you can tick:
   - "Remember these resolutions for the next imports"
   - "Save as named profile"
4. On import, resolved items are persisted into the selected/new profile.

## Admin
- Go to Admin → Import → Profiles to manage profiles.
- Open a profile to view/add/edit choices, filter by field/action, and export JSON.
- Import JSON back into a profile to bulk load choices.

## Safety
- Additive DB changes; no destructive operations.
- Profiles are namespaced by table; you can disable choices by deactivating them.

## Notes
- Thresholds live in `config/fuzzy.php`. You can store overrides in `settings_json` on the profile for future use.


