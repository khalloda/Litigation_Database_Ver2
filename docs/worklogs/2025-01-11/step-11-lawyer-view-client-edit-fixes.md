# Step 11 — Lawyer View & Client Edit Form Fixes
- Branch: `mod/clients-model`
- Commit: `10c74c1`

## Commands
```bash
# Fixed lawyer view case relationship
git checkout mod/clients-model
git merge fix/lawyer-view --no-edit
git branch -D fix/lawyer-view

# Fixed client edit form
git add -A
git commit -m "feat(clients): complete client edit form with all fields"
```

## Changes
- `clm-app/app/Models/Lawyer.php` - Fixed case relationships to use lawyer_a/lawyer_b columns
- `clm-app/app/Http/Controllers/LawyersController.php` - Updated show method to load both case relationships
- `clm-app/resources/views/lawyers/show.blade.php` - Updated to use $cases variable
- `clm-app/app/Http/Controllers/ClientsController.php` - Updated edit method to load dropdown options
- `clm-app/resources/views/clients/edit.blade.php` - Completely rewrote to match create form
- `clm-app/resources/lang/en/app.php` - Added update_client, current_logo translations
- `clm-app/resources/lang/ar/app.php` - Added Arabic translations

## Errors & Fixes
- Issue: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'cases.lawyer_id'`
- Root cause: Cases table has lawyer_a and lawyer_b columns, not lawyer_id
- Fix: Updated Lawyer model with proper relationships and controller logic

- Issue: Client edit form incomplete (only showing name fields)
- Root cause: Edit controller not loading dropdown options, edit view was minimal
- Fix: Updated controller to load all options, completely rewrote edit view

## Validation
- Lawyer view now displays cases correctly without column errors
- Client edit form now shows all fields: names, dates, status, cash/pro bono, contact lawyer, logo, document locations
- All dropdowns populated with correct options
- Form pre-populated with existing client data
- Proper validation and error handling

## Technical Details

### Lawyer Case Relationships
The `cases` table structure uses `lawyer_a` and `lawyer_b` columns instead of a single `lawyer_id`. This required:
1. Creating separate relationships for each role
2. Adding a method to get all cases for a lawyer
3. Updating the controller to load both relationships
4. Updating the view to use the combined collection

### Client Edit Form Completion
The edit form was missing most fields that were present in create and view forms:
1. Updated controller to load all dropdown options (cash/pro bono, status, locations, lawyers)
2. Completely rewrote edit view to match create form structure
3. Added current logo preview functionality
4. Added proper validation and error handling
5. Added missing translation keys

## Files Modified
- `clm-app/app/Models/Lawyer.php` - Case relationships fix
- `clm-app/app/Http/Controllers/LawyersController.php` - Show method update
- `clm-app/resources/views/lawyers/show.blade.php` - View template update
- `clm-app/app/Http/Controllers/ClientsController.php` - Edit method enhancement
- `clm-app/resources/views/clients/edit.blade.php` - Complete rewrite
- `clm-app/resources/lang/en/app.php` - Translation additions
- `clm-app/resources/lang/ar/app.php` - Arabic translations
