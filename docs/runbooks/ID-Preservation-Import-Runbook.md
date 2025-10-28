# ID Preservation Import - Quick Runbook

> **Use Case:** When importing data that has existing foreign key relationships that must be preserved.

---

## Quick Reference Card

| Step | Action | Time | Risk |
|------|--------|------|------|
| 1 | Disable auto-increment | 1 min | Low |
| 2 | Modify MappingEngine | 2 min | Low |
| 3 | Import data | 5-10 min | Medium |
| 4 | Re-enable auto-increment | 1 min | Low |
| 5 | Revert MappingEngine | 2 min | Low |
| 6 | Verify & Commit | 3 min | Low |

**Total Time:** ~15-20 minutes per table

---

## Before You Start

### Prerequisites
✅ Backup database  
✅ Have Excel file ready  
✅ Know source and target table names  
✅ Verify table is empty or truncated  
✅ Identify ID column name in Excel (e.g., `Lawyer_ID`, `Client_ID`, `Case_ID`)

### Required Access
- Database access (MySQL)
- Laravel Tinker access
- Code editor access
- Git access

---

## Step-by-Step Process

### STEP 1: Disable Auto-Increment (1 min)

**Command:**
```bash
cd clm-app
php artisan tinker --execute="DB::statement('ALTER TABLE {table_name} MODIFY id BIGINT UNSIGNED NOT NULL');"
```

**Example:**
```bash
php artisan tinker --execute="DB::statement('ALTER TABLE clients MODIFY id BIGINT UNSIGNED NOT NULL');"
```

**Verify:**
```bash
php artisan tinker --execute="print_r(DB::select('DESCRIBE {table_name}')[0]);"
```

**Expected:** `Extra` column should be empty (no `auto_increment`)

---

### STEP 2: Modify MappingEngine (2 min)

**File:** `clm-app/app/Services/MappingEngine.php`

**Line:** ~90

**Find:**
```php
// Filter out system columns
$excludedColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

return array_diff($columns, $excludedColumns);
```

**Replace with:**
```php
// Filter out system columns
$excludedColumns = ['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

// Special case: include 'id' column for {table_name} during import
if ($tableName === '{table_name}') {
    // Don't exclude 'id' for this table
} else {
    $excludedColumns[] = 'id';
}

return array_diff($columns, $excludedColumns);
```

**Example (for clients table):**
```php
if ($tableName === 'clients') {
    // Don't exclude 'id' for clients table
} else {
    $excludedColumns[] = 'id';
}
```

**Verify:**
```bash
php artisan tinker --execute="print_r((new App\Services\MappingEngine)->getDbColumnsForTable('{table_name}'));"
```

**Expected:** Output should include `id` as first element

---

### STEP 3: Import Data via UI (5-10 min)

**URL:** `http://127.0.0.1:8000/import/upload`

1. **Upload File**
   - Select table: `{table_name}`
   - Upload: `{filename}.xlsx`
   - Click: "Upload and Continue"

2. **Map Columns**
   - **CRITICAL:** Map source ID column to `id`
     - Example: `Lawyer_ID` → `id`
     - Example: `Client_ID` → `id`
     - Example: `Case_ID` → `id`
   - Map other columns normally
   - Click: "Continue to Validation"

3. **Validate**
   - Review validation results
   - Check for errors
   - Fix any issues in source file if needed
   - Click: "Start Import"

4. **Execute**
   - Wait for import to complete
   - Verify success message
   - Note: Imported count, failed count, errors

---

### STEP 4: Re-enable Auto-Increment (1 min)

**Command:**
```bash
php artisan tinker --execute="DB::statement('ALTER TABLE {table_name} MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');"
```

**Example:**
```bash
php artisan tinker --execute="DB::statement('ALTER TABLE clients MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');"
```

**Verify:**
```bash
php artisan tinker --execute="print_r(DB::select('DESCRIBE {table_name}')[0]);"
```

**Expected:** `Extra` column should show `auto_increment`

---

### STEP 5: Revert MappingEngine (2 min)

**File:** `clm-app/app/Services/MappingEngine.php`

**Line:** ~90

**Find:**
```php
// Filter out system columns
$excludedColumns = ['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

// Special case: include 'id' column for {table_name} during import
if ($tableName === '{table_name}') {
    // Don't exclude 'id' for this table
} else {
    $excludedColumns[] = 'id';
}

return array_diff($columns, $excludedColumns);
```

**Replace with:**
```php
// Filter out system columns
$excludedColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

return array_diff($columns, $excludedColumns);
```

**Verify:**
```bash
php artisan tinker --execute="print_r((new App\Services\MappingEngine)->getDbColumnsForTable('{table_name}'));"
```

**Expected:** Output should NOT include `id`

---

### STEP 6: Verify & Commit (3 min)

**Verify Import:**
```bash
php artisan tinker --execute="
echo '{table_name} count: ' . DB::table('{table_name}')->count();
echo ' Sample IDs: ';
print_r(DB::table('{table_name}')->select('id')->limit(10)->pluck('id')->toArray());
"
```

**Verify Foreign Keys (if applicable):**
```bash
# Example: Check if case.lawyer_id references exist in lawyers table
php artisan tinker --execute="
\$missing = DB::table('cases')
    ->whereNotNull('lawyer_id')
    ->whereNotExists(function(\$q) {
        \$q->select(DB::raw(1))
          ->from('lawyers')
          ->whereColumn('lawyers.id', 'cases.lawyer_id');
    })
    ->count();
echo 'Missing lawyer references: ' . \$missing;
"
```

**Commit Changes:**
```bash
git add clm-app/app/Services/MappingEngine.php

# First commit (enabling ID mapping)
git commit -m "feat(import): allow id column mapping for {table_name}

- Modified getDbColumnsForTable() to include 'id' column for {table_name}
- This allows {source_id_column} from Excel to be mapped to database id
- Preserves original IDs to maintain foreign key relationships"

# Second commit (reverting)
git commit -m "revert(import): restore normal id column exclusion after {table_name} import

- Reverted MappingEngine to exclude 'id' column for all tables
- {table_name} import complete with {count} records
- System back to normal operation"
```

---

## Troubleshooting

### Issue: "Unknown column '' in 'field list'"
**Cause:** Empty target column in mapping  
**Fix:** Ensure all skipped columns are NOT mapped to `id`

### Issue: "Duplicate entry '6' for key 'PRIMARY'"
**Cause:** Table not empty or duplicate IDs in source  
**Fix:** Truncate table; check source file for duplicates

### Issue: Auto-increment not working after import
**Cause:** Forgot to re-enable auto-increment  
**Fix:** Run STEP 4 again

### Issue: Cannot map to `id` column
**Cause:** MappingEngine not modified  
**Fix:** Verify STEP 2 was completed correctly

### Issue: Foreign key constraint violations
**Cause:** Importing in wrong order  
**Fix:** Import parent tables before child tables (see import order below)

---

## Import Order Reference

Import tables in this order to avoid foreign key issues:

1. ✅ **lawyers** (Completed - 23 records)
2. ⏳ **clients**
3. ⏳ **cases** (depends on: clients, lawyers)
4. ⏳ **admin_tasks** (depends on: lawyers)
5. ⏳ **hearings** (depends on: cases)
6. ⏳ **contacts** (depends on: clients, cases)
7. ⏳ **engagement_letters** (depends on: clients)
8. ⏳ **power_of_attorneys** (depends on: clients)
9. ⏳ **admin_subtasks** (depends on: admin_tasks)
10. ⏳ **client_documents** (depends on: cases)

---

## Quick Copy-Paste Templates

### For Clients Table
```bash
# Step 1
php artisan tinker --execute="DB::statement('ALTER TABLE clients MODIFY id BIGINT UNSIGNED NOT NULL');"

# Step 2 - Edit MappingEngine.php
# if ($tableName === 'clients') {

# Step 3 - Map: Client_ID → id

# Step 4
php artisan tinker --execute="DB::statement('ALTER TABLE clients MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');"

# Step 6 - Verify
php artisan tinker --execute="echo 'clients count: ' . DB::table('clients')->count();"
```

### For Cases Table
```bash
# Step 1
php artisan tinker --execute="DB::statement('ALTER TABLE cases MODIFY id BIGINT UNSIGNED NOT NULL');"

# Step 2 - Edit MappingEngine.php
# if ($tableName === 'cases') {

# Step 3 - Map: Case_ID → id

# Step 4
php artisan tinker --execute="DB::statement('ALTER TABLE cases MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');"

# Step 6 - Verify
php artisan tinker --execute="echo 'cases count: ' . DB::table('cases')->count();"
```

### For Admin Tasks Table
```bash
# Step 1
php artisan tinker --execute="DB::statement('ALTER TABLE admin_tasks MODIFY id BIGINT UNSIGNED NOT NULL');"

# Step 2 - Edit MappingEngine.php
# if ($tableName === 'admin_tasks') {

# Step 3 - Map: Task_ID → id

# Step 4
php artisan tinker --execute="DB::statement('ALTER TABLE admin_tasks MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');"

# Step 6 - Verify
php artisan tinker --execute="echo 'admin_tasks count: ' . DB::table('admin_tasks')->count();"
```

---

## Safety Checklist

Before starting:
- [ ] Database backup created
- [ ] Working on correct branch (`feat/import-export`)
- [ ] Laravel dev server running
- [ ] Excel file validated and ready

After each table:
- [ ] Auto-increment re-enabled
- [ ] MappingEngine reverted
- [ ] Changes committed
- [ ] Record count verified
- [ ] Foreign keys spot-checked

---

## Contact & Support

**Documentation:**
- Full ADR: `docs/adr/ADR-20250110-002-ID-Preservation-Import-Strategy.md`
- Config: `clm-app/config/importer.php`
- Import Service: `clm-app/app/Services/ImportService.php`

**Completed Imports:**
- ✅ Lawyers (2025-01-10, 23 records, IDs preserved)

---

**Last Updated:** 2025-01-10  
**Version:** 1.0

