# ADR-002: ID Preservation Strategy for Data Migration

**Status:** Accepted  
**Date:** 2025-01-10  
**Authors:** Development Team  
**Tags:** import, migration, data-integrity, foreign-keys

---

## Context

When migrating data from the legacy MS Access system to the new Laravel/MySQL system, we discovered that many tables have existing foreign key relationships that reference specific ID values. For example:

- `cases.lawyer_id` references specific lawyer IDs (e.g., 6, 9, 22)
- `admin_tasks.lawyer_id` references the same lawyer IDs
- `hearings.case_id` references specific case IDs
- Multiple tables reference `client_id` values

If we import data with auto-increment enabled, new IDs would be generated (1, 2, 3...), breaking all foreign key relationships across tables. This would require extensive data remapping and could introduce errors.

**The Problem:**
- Legacy system uses specific IDs (e.g., Lawyer "Ihab Hamdy" has ID 6)
- Multiple tables reference these IDs
- Auto-increment would generate new sequential IDs
- Foreign key relationships would break
- Data integrity would be compromised

---

## Decision

We will implement a **temporary ID preservation strategy** during the initial data migration. This allows us to import records with their original IDs intact, maintaining all foreign key relationships.

### Implementation Process

For each table that requires ID preservation:

1. **Disable Auto-Increment**
   - Temporarily remove `AUTO_INCREMENT` from the primary key column
   - This allows manual insertion of specific ID values

2. **Enable ID Mapping in Import Module**
   - Modify `MappingEngine` to include the `id` column for the target table
   - This makes the `id` column available in the column mapping UI

3. **Import Data with Original IDs**
   - Map source ID column (e.g., `Lawyer_ID`) to database `id` column
   - Import proceeds with original ID values preserved

4. **Re-enable Auto-Increment**
   - Restore `AUTO_INCREMENT` on the primary key column
   - Future manually-added records will get proper auto-increment IDs
   - Auto-increment starts from MAX(id) + 1

5. **Revert Mapping Changes**
   - Remove the special case from `MappingEngine`
   - Prevent `id` column from appearing in normal import operations

---

## Implementation Details

### Step 1: Disable Auto-Increment

```sql
ALTER TABLE {table_name} MODIFY id BIGINT UNSIGNED NOT NULL;
```

**Example (Lawyers):**
```sql
ALTER TABLE lawyers MODIFY id BIGINT UNSIGNED NOT NULL;
```

### Step 2: Enable ID Mapping

**File:** `clm-app/app/Services/MappingEngine.php`

**Method:** `getDbColumnsForTable()`

**Change:**
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

**Example (Lawyers):**
```php
// Special case: include 'id' column for lawyers table during import
if ($tableName === 'lawyers') {
    // Don't exclude 'id' for lawyers table
} else {
    $excludedColumns[] = 'id';
}
```

### Step 3: Import Data

1. Navigate to Import/Export module: `/import/upload`
2. Select the table (e.g., `lawyers`)
3. Upload the Excel file (e.g., `lawyers.xlsx`)
4. **Map columns including ID:**
   - `Lawyer_ID` → `id` ✅
   - Other columns as normal
5. Continue through validation
6. Execute import

### Step 4: Re-enable Auto-Increment

```sql
ALTER TABLE {table_name} MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;
```

**Verification:**
```sql
DESCRIBE {table_name};
-- Check that 'Extra' column shows 'auto_increment'
```

**Example (Lawyers):**
```sql
ALTER TABLE lawyers MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;
```

### Step 5: Revert Mapping Changes

**Revert `MappingEngine.php` to normal:**
```php
// Filter out system columns
$excludedColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];

return array_diff($columns, $excludedColumns);
```

---

## Tables Requiring ID Preservation

Based on foreign key analysis, the following tables require ID preservation during migration:

| Table | Foreign Key References | Priority |
|-------|----------------------|----------|
| `lawyers` | ✅ **Completed** - Referenced by cases, admin_tasks, hearings | Critical |
| `clients` | Referenced by cases, contacts, engagement_letters, power_of_attorneys | Critical |
| `cases` | Referenced by hearings, client_documents, contacts | Critical |
| `admin_tasks` | Referenced by admin_subtasks | High |
| `contacts` | May reference clients, cases | Medium |
| `engagement_letters` | May reference clients | Medium |
| `power_of_attorneys` | May reference clients | Medium |

---

## Import Order

To maintain referential integrity, import tables in this order:

1. **Lawyers** ✅ (Completed - 23 records imported with preserved IDs)
2. **Clients** (Many tables reference client_id)
3. **Cases** (Referenced by hearings, documents)
4. **Admin Tasks** (Referenced by admin_subtasks)
5. **Hearings** (Depends on cases)
6. **Contacts** (Depends on clients, cases)
7. **Engagement Letters** (Depends on clients)
8. **Power of Attorneys** (Depends on clients)
9. **Admin Subtasks** (Depends on admin_tasks)
10. **Client Documents** (Depends on cases, clients)

---

## Consequences

### Positive
✅ **Data Integrity Preserved** - All foreign key relationships remain intact  
✅ **No Manual Remapping** - No need to update foreign keys after import  
✅ **Audit Trail** - Original IDs maintained for historical reference  
✅ **Reduced Risk** - Lower chance of data corruption during migration  
✅ **Faster Migration** - No post-import data reconciliation needed

### Negative
⚠️ **Manual Process** - Requires careful step-by-step execution for each table  
⚠️ **Schema Changes** - Temporary modification of table structure  
⚠️ **Code Changes** - Temporary modification of MappingEngine  
⚠️ **ID Gaps** - Auto-increment will have gaps (e.g., 1, 2, 6, 9, 10, 15...)  

### Neutral
ℹ️ **One-Time Operation** - Only needed during initial migration  
ℹ️ **Reversible** - All changes are temporary and fully reversible  
ℹ️ **Well-Documented** - Process is documented and repeatable

---

## Risks and Mitigations

| Risk | Mitigation |
|------|------------|
| Forgetting to re-enable auto-increment | ✅ Include verification step in checklist; document in commit message |
| ID collisions with existing data | ✅ Clear tables before import; verify table is empty |
| Forgetting to revert MappingEngine | ✅ Commit revert immediately after import; include in checklist |
| Import errors leaving table in inconsistent state | ✅ Backup database before each import; use transaction-safe operations |
| Foreign key constraint violations | ✅ Import in correct order (parents before children) |

---

## Alternatives Considered

### Alternative 1: Post-Import ID Remapping
**Description:** Import all data with new auto-increment IDs, then update all foreign keys.

**Rejected Because:**
- High complexity - requires tracking old ID → new ID mapping
- Error-prone - easy to miss foreign key references
- Time-consuming - multiple UPDATE queries across many tables
- Risk of data corruption if mapping is incorrect

### Alternative 2: Use UUID Instead of Auto-Increment IDs
**Description:** Change all primary keys to UUIDs to avoid ID conflicts.

**Rejected Because:**
- Major schema change - requires migration of entire database
- Performance impact - UUIDs are larger and slower to index
- Breaking change - would require application refactoring
- Not suitable for mid-project change

### Alternative 3: Import with Offset IDs
**Description:** Import with auto-increment starting at 10000 to avoid collisions.

**Rejected Because:**
- Still breaks foreign key relationships
- Requires same remapping work as Alternative 1
- Creates confusion with very large ID gaps
- Doesn't solve the core problem

---

## Verification Checklist

After completing ID preservation import for a table:

- [ ] Table imported successfully with original IDs
- [ ] Foreign key relationships verified (spot check)
- [ ] Auto-increment re-enabled on `id` column
- [ ] Auto-increment counter set correctly (starts after max ID)
- [ ] MappingEngine reverted to normal operation
- [ ] Code changes committed with clear commit messages
- [ ] Import session marked as completed in import_sessions table
- [ ] Record count matches source data

---

## Example: Lawyers Table Import (Completed 2025-01-10)

### Pre-Import State
- Table: `lawyers` (empty after truncate)
- Source: `lawyers.xlsx` (23 records)
- Original IDs: 1, 2, 5, 6, 9, 10, 11, 15, 16, 22, etc.

### Steps Executed
1. ✅ Disabled auto-increment: `ALTER TABLE lawyers MODIFY id BIGINT UNSIGNED NOT NULL;`
2. ✅ Modified MappingEngine to include `id` for lawyers table
3. ✅ Imported 23 records with original IDs preserved
4. ✅ Re-enabled auto-increment: `ALTER TABLE lawyers MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;`
5. ✅ Reverted MappingEngine changes
6. ✅ Verified: `DESCRIBE lawyers` shows `auto_increment` in Extra column

### Results
- ✅ 23 lawyers imported successfully
- ✅ Original IDs preserved (e.g., Ihab Hamdy = ID 6)
- ✅ Foreign key relationships intact (cases.lawyer_id, admin_tasks.lawyer_id)
- ✅ Auto-increment enabled for future manual entries

### Commits
- `feat(import): allow id column mapping for lawyers table` (7f03064)
- `revert(import): restore normal id column exclusion after lawyers import` (6096ee5)

---

## References

- **Import/Export Module:** `clm-app/app/Services/ImportService.php`
- **Mapping Engine:** `clm-app/app/Services/MappingEngine.php`
- **Database Schema:** `docs/data-dictionary.md`
- **Foreign Keys Config:** `clm-app/config/importer.php`

---

## Notes

- This is a **migration-time strategy**, not a permanent feature
- After all data is migrated, normal auto-increment behavior resumes
- ID gaps (e.g., 1, 2, 6, 9...) are acceptable and normal in production databases
- The Import/Export module will continue to work for future imports without ID preservation
- This approach maintains backward compatibility with the legacy Access database

---

**Last Updated:** 2025-01-10  
**Next Review:** After completion of all table imports

