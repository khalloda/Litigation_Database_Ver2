# Hearings Import QA Checklist

**Date**: 2025-01-XX  
**Prepared By**: [Your Name]  
**Import File**: `Hearings_Transformed_Sample.csv`

---

## Quick Start

**New Staging-Based Workflow**:

1. **Preflight Import**:
   ```bash
   php artisan hearings:preflight --file=/path/to/Hearings-Original.csv
   ```
   - Transforms and loads into `hearings_staging` table
   - Artifacts saved to: `storage/app/imports/hearings/YYYYMMDD-HHMM/`

2. **Validate Staging**:
   ```bash
   php artisan hearings:validate-staging
   ```
   - Runs all QA checks automatically
   - Exits non-zero if high-severity issues found

3. **Commit to Production**:
   ```bash
   php artisan hearings:commit-staging --batch=2000
   ```
   - Creates backup automatically
   - Migrates in batches with transaction safety

**Artifacts Directory**: `storage/app/imports/hearings/YYYYMMDD-HHMM/`
- `db_mappings.csv` - Lookup tables (IDs → names)
- `unmatched.csv` - Values requiring manual review
- `errors.json` - Validation errors
- `summary.json` - Import statistics

---

## Pre-Import Validation

### 1. File Format & Encoding
- [ ] File is CSV format (UTF-8 encoding)
- [ ] BOM removed (if present)
- [ ] Headers match template exactly (21 columns)
- [ ] No extra columns or missing columns
- [ ] **`id` column populated from original `hearings_id` (not empty, not auto-generated)**

### 2. ID Preservation Validation
- [ ] All `id` values are integers (preserved from `hearings_id`)
- [ ] No duplicate `id` values in transformed file
- [ ] `id` values match original `hearings_id` values
- [ ] No NULL or empty `id` values (unless original was empty)

**SQL Check**:
```sql
-- Verify ID preservation (if importing to temp table first)
SELECT 
    COUNT(*) as total_rows,
    COUNT(DISTINCT id) as distinct_ids,
    COUNT(CASE WHEN id IS NULL OR id = '' THEN 1 END) as null_ids
FROM (SELECT * FROM transformed_sample) t;
-- distinct_ids should equal total_rows (no duplicates)
-- null_ids should be 0 (or match count of empty original hearings_id)
```

### 3. ID Uniqueness Check
- [ ] No duplicate `id` values (would cause PK violation on import)

**SQL Check**:
```sql
-- Check for duplicate IDs
SELECT id, COUNT(*) as count
FROM (SELECT * FROM transformed_sample) t
WHERE id IS NOT NULL AND id != ''
GROUP BY id
HAVING count > 1;
-- Expected: Empty result set
```

### 4. Character Set & Collation
- [ ] All Arabic text displays correctly (no mojibake)
- [ ] Special characters preserved (quotes, commas, newlines)
- [ ] Database collation: `utf8mb4_unicode_ci` confirmed

### 5. Date Format Validation
- [ ] All dates in `YYYY-MM-DD` format
- [ ] No invalid dates (e.g., 2024-02-30)
- [ ] Date range reasonable (e.g., 2010-2025)
- [ ] `next_hearing` >= `date` (if both present)

**Sample Check**:
```sql
SELECT COUNT(*) as invalid_dates
FROM (SELECT * FROM transformed_sample) t
WHERE (date IS NOT NULL AND date != '' AND date NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$')
   OR (next_hearing IS NOT NULL AND next_hearing != '' AND next_hearing NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$');
-- Expected: 0
```

### 6. Foreign Key Validation

#### 6.1. `matter_id` Validation
- [ ] All `matter_id` values exist in `cases.id`
- [ ] No NULL `matter_id` values (required field)
- [ ] Count of distinct `matter_id` matches expected

**SQL Check**:
```sql
SELECT COUNT(DISTINCT t.matter_id) as distinct_matter_ids,
       COUNT(DISTINCT c.id) as matching_cases
FROM (SELECT * FROM transformed_sample) t
LEFT JOIN cases c ON t.matter_id = c.id
WHERE t.matter_id IS NOT NULL AND t.matter_id != '';
-- distinct_matter_ids should equal matching_cases
```

**Orphaned `matter_id` List**:
```sql
SELECT DISTINCT t.matter_id
FROM (SELECT * FROM transformed_sample) t
LEFT JOIN cases c ON t.matter_id = c.id
WHERE c.id IS NULL AND t.matter_id IS NOT NULL AND t.matter_id != '';
-- Expected: Empty result set
```

#### 6.2. `lawyer_id` Validation
- [ ] All non-empty `lawyer_id` values exist in `lawyers.id`
- [ ] NULL `lawyer_id` is acceptable (optional field)
- [ ] Fuzzy matching results reviewed for accuracy

**SQL Check**:
```sql
SELECT COUNT(DISTINCT t.lawyer_id) as distinct_lawyer_ids,
       COUNT(DISTINCT l.id) as matching_lawyers
FROM (SELECT * FROM transformed_sample) t
LEFT JOIN lawyers l ON t.lawyer_id = l.id
WHERE t.lawyer_id IS NOT NULL AND t.lawyer_id != '';
-- distinct_lawyer_ids should equal matching_lawyers
```

**Orphaned `lawyer_id` List**:
```sql
SELECT DISTINCT t.lawyer_id, t.attendee
FROM (SELECT * FROM transformed_sample) t
LEFT JOIN lawyers l ON t.lawyer_id = l.id
WHERE t.lawyer_id IS NOT NULL AND t.lawyer_id != '' AND l.id IS NULL;
-- Expected: Empty result set (or review list)
```

### 7. Data Type Validation

#### 7.1. Boolean Fields
- [ ] `report` values: `TRUE` or `FALSE` only
- [ ] `notify_client` values: `TRUE` or `FALSE` only
- [ ] No empty strings or NULL values (default to `FALSE`)

**SQL Check**:
```sql
SELECT COUNT(*) as invalid_booleans
FROM (SELECT * FROM transformed_sample) t
WHERE (report NOT IN ('TRUE', 'FALSE') AND report != '')
   OR (notify_client NOT IN ('TRUE', 'FALSE') AND notify_client != '');
-- Expected: 0
```

#### 7.2. String Length Validation
- [ ] `procedure`, `court`, `circuit`, `destination` <= 255 chars
- [ ] `short_decision`, `last_decision` <= 255 chars
- [ ] `attendee` fields <= 255 chars each
- [ ] `evaluation` <= 255 chars
- [ ] `decision`, `notes` are TEXT (no length limit, but check for reasonable size)

**SQL Check**:
```sql
SELECT 
    MAX(CHAR_LENGTH(procedure)) as max_procedure,
    MAX(CHAR_LENGTH(court)) as max_court,
    MAX(CHAR_LENGTH(circuit)) as max_circuit,
    MAX(CHAR_LENGTH(destination)) as max_destination,
    MAX(CHAR_LENGTH(short_decision)) as max_short_decision,
    MAX(CHAR_LENGTH(last_decision)) as max_last_decision,
    MAX(CHAR_LENGTH(attendee)) as max_attendee,
    MAX(CHAR_LENGTH(evaluation)) as max_evaluation
FROM (SELECT * FROM transformed_sample) t;
-- All max_* values should be <= 255
```

### 8. Required Fields Validation
- [ ] `matter_id` present in all rows (non-empty, non-NULL)
- [ ] `date` present in all rows (non-empty, non-NULL)
- [ ] `id` present in all rows (non-empty, non-NULL) - **preserved from original**

**SQL Check**:
```sql
SELECT COUNT(*) as missing_required
FROM (SELECT * FROM transformed_sample) t
WHERE matter_id IS NULL OR matter_id = '' 
   OR date IS NULL OR date = ''
   OR id IS NULL OR id = '';
-- Expected: 0
```

### 9. Data Quality Checks

#### 9.1. Text Cleanup Validation
- [ ] No `_x000D_` artifacts in text fields
- [ ] No zero-width characters (U+200B-U+200D, U+FEFF)
- [ ] No direction marks (U+200E, U+200F)
- [ ] Whitespace normalized (no double spaces, trimmed)

**Sample Check** (manual review):
- Open CSV in text editor, search for `_x000D_` → should find 0 results
- Check for invisible characters → should be clean

#### 9.2. Circuit Parsing Validation
- [ ] `circuit` field contains only circuit name (no serial, no notes)
- [ ] Circuit serial extracted correctly (if applicable)
- [ ] Circuit notes merged into `notes` field (if applicable)

**Sample Check**:
```sql
-- Check for serial numbers in circuit field (should be none)
SELECT id, circuit
FROM (SELECT * FROM transformed_sample) t
WHERE circuit REGEXP '^[0-9]+';
-- Expected: Empty or review list
```

#### 9.3. Boolean Normalization
- [ ] All boolean values normalized to `TRUE`/`FALSE`
- [ ] No legacy values (`Yes`, `No`, `1`, `0`, etc.)

### 10. Unmatched Values Review

#### 10.1. Unmatched Lawyer Names
**Action**: Review fuzzy matching results, create manual mapping file if needed

**Query**:
```sql
-- Find rows where lawyer_id is NULL but attendee fields are populated
SELECT DISTINCT 
    id, attendee, attendee_1, attendee_2, attendee_3, attendee_4, next_attendee
FROM (SELECT * FROM transformed_sample) t
WHERE (lawyer_id IS NULL OR lawyer_id = '')
  AND (attendee != '' OR attendee_1 != '' OR attendee_2 != '' 
       OR attendee_3 != '' OR attendee_4 != '' OR next_attendee != '');
```

#### 10.2. Unmatched Court Names
**Action**: Review court name normalization, propose mappings

**Query**:
```sql
-- Find distinct court names that don't match DB
SELECT DISTINCT court
FROM (SELECT * FROM transformed_sample) t
WHERE court != ''
  AND court NOT IN (SELECT court_name_ar FROM courts WHERE deleted_at IS NULL)
  AND court NOT IN (SELECT court_name_en FROM courts WHERE deleted_at IS NULL);
```

### 11. Row Count Validation
- [ ] Total rows in transformed file matches expected (or sample size)
- [ ] No duplicate rows (same `matter_id` + `date`)
- [ ] Skipped rows documented in error log

**SQL Check**:
```sql
-- Check for duplicates (same matter_id + date)
SELECT matter_id, date, COUNT(*) as count
FROM (SELECT * FROM transformed_sample) t
GROUP BY matter_id, date
HAVING count > 1;
-- Expected: Empty result set (or review list)

-- Check for duplicate IDs (should be none)
SELECT id, COUNT(*) as count
FROM (SELECT * FROM transformed_sample) t
GROUP BY id
HAVING count > 1;
-- Expected: Empty result set
```

### 12. Sample Review (First 50 Rows)
- [ ] Manually review first 10 rows for accuracy
- [ ] Verify `id` preservation from `hearings_id`
- [ ] Verify date parsing correct
- [ ] Verify circuit parsing correct
- [ ] Verify lawyer matching correct
- [ ] Verify notes merging correct
- [ ] Verify boolean normalization correct

---

## Post-Import Validation

After importing to database:

### 13. Database Integrity Checks
- [ ] All rows imported successfully (no constraint violations)
- [ ] Row count matches transformed file
- [ ] Foreign keys valid (no orphaned records)
- [ ] Indexes created correctly
- [ ] **ID values preserved correctly (match original `hearings_id`)**

**SQL Check**:
```sql
-- Verify imported data
SELECT COUNT(*) as imported_count FROM hearings WHERE created_at >= '2025-01-XX';
-- Should match transformed file row count

-- Verify FK integrity
SELECT COUNT(*) as orphaned_matter_ids
FROM hearings h
LEFT JOIN cases c ON h.matter_id = c.id
WHERE c.id IS NULL;
-- Expected: 0

SELECT COUNT(*) as orphaned_lawyer_ids
FROM hearings h
LEFT JOIN lawyers l ON h.lawyer_id = l.id
WHERE h.lawyer_id IS NOT NULL AND l.id IS NULL;
-- Expected: 0

-- Verify ID preservation (spot check)
SELECT h.id, h.matter_id, h.date
FROM hearings h
WHERE h.created_at >= '2025-01-XX'
ORDER BY h.id
LIMIT 10;
-- Compare with original hearings_id values
```

### 14. Data Accuracy Spot Checks
- [ ] Random sample of 10 rows verified against original source
- [ ] IDs match original `hearings_id` values
- [ ] Dates match original (converted correctly)
- [ ] Text fields match original (cleaned but not altered)
- [ ] Boolean fields match original (normalized correctly)

---

## Sign-Off

- [ ] **Preflight Complete**: `hearings:preflight` executed successfully
- [ ] **Validation Passed**: `hearings:validate-staging` exited with code 0
- [ ] **Unmatched Values Reviewed**: Manual mappings created if needed
- [ ] **Backup Verified**: Backup file exists before commit
- [ ] **Commit Complete**: `hearings:commit-staging` executed successfully
- [ ] **Post-Import QA Complete**: Database integrity verified

**QA Reviewer**: _________________  
**Date**: _________________  
**Status**: ☐ Approved | ☐ Needs Revision | ☐ Rejected

---

## Notes

- **ID Handling**: Remember that `id` values are preserved from original `hearings_id`, not auto-generated. Ensure no duplicates before import.
- **Staging Workflow**: All imports now go through staging table first for validation
- **Artifacts**: Review `unmatched.csv` and `errors.json` in artifacts directory
- **Manual Mappings**: Create `storage/app/imports/hearings/manual_mappings.csv` with format:
  ```csv
  Legacy_Value,Canonical_Value
  "محمد الغرابلي","Mohamed Abd El-Aziz (ID: 4)"
  ```
- **Dry Run**: Use `--dry-run` flag with `hearings:commit-staging` to test without making changes
- **Backups**: Automatic backups created in `storage/app/backup/` before commit
- Document any manual corrections made during QA process

---

**Checklist Version**: 2.0  
**Last Updated**: 2025-01-XX  
**Change Log**: 
- v2.0: Updated for staging-based workflow with CLI commands
- v1.1: Added ID preservation validation sections

