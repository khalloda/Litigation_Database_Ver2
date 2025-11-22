# Hearings Import Compatibility Report

**Date**: 2025-01-XX  
**Source**: `Hearings-Original.csv`  
**Target**: `Hearings_Import_Template.csv`  
**Database**: `litigation_db_ver2` (MySQL 9.1.0, utf8mb4_unicode_ci)

---

## Executive Summary

This report analyzes the compatibility between the legacy hearings export (`Hearings-Original.csv`) and the new import template (`Hearings_Import_Template.csv`), validating against the live database schema and referential integrity constraints.

**Key Findings**:
- **Total Original Columns**: 25
- **Template Columns**: 21
- **Direct Mappings**: 18
- **Compound Fields Requiring Parsing**: 1 (`الدائرة`)
- **Original-Only Columns**: 4 (to be preserved as notes or dropped)
- **FK Dependencies**: `matter_id` (required), `lawyer_id` (optional)

---

## Important Notes

### ID Handling
**Current System Behavior**: The `id` column in the database is **preserved from the original export** (`hearings_id`), not auto-generated. This means:
- The transformation pipeline must map `hearings_id` → `id`
- Duplicate `id` values in the original file will cause import errors
- Missing `hearings_id` values should be handled (either skip row or generate new ID)
- The `id` field in the template should be populated, not left empty

**Future Consideration**: If migrating to auto-generated IDs, a separate migration strategy would be needed to handle existing foreign key references.

---

## Schema Diff Report

| Template Column | Type/Format | Required? | Max Length | Enum/Domain | Source Column(s) | Transformation | DB Mapping | FK Expectations | Risk Level |
|---|---|---|---|---|---|---|---|---|---|
| `id` | integer | No | - | Preserved from original | `hearings_id` | Parse integer, preserve value | `hearings.id` | PK | Low |
| `matter_id` | integer | **Yes** | - | FK to `cases.id` | `matter_id` | Validate exists in DB | `hearings.matter_id` | FK→`cases.id` | **High** |
| `lawyer_id` | integer | No | - | FK to `lawyers.id` | None (derived) | Fuzzy match from attendee names | `hearings.lawyer_id` | FK→`lawyers.id` | **High** |
| `date` | date (YYYY-MM-DD) | **Yes** | - | Valid date | `date` | Parse DD/MM/YYYY → YYYY-MM-DD | `hearings.date` | - | Medium |
| `procedure` | string | No | 255 | - | `الإجراء` | Clean text, normalize | `hearings.procedure` | - | Low |
| `court` | string | No | 255 | - | `المحكمة` | Clean text, normalize | `hearings.court` | - | Low |
| `circuit` | string | No | 255 | - | `الدائرة` (compound) or `اسم الدائرة` | Parse compound field or use split | `hearings.circuit` | - | Medium |
| `destination` | string | No | 255 | - | `الجهة` | Clean text | `hearings.destination` | - | Low |
| `decision` | text | No | TEXT | - | `decision` | Clean text, remove `_x000D_` | `hearings.decision` | - | Low |
| `short_decision` | string | No | 255 | - | `shortDecision` | Clean text | `hearings.short_decision` | - | Low |
| `last_decision` | string | No | 255 | - | `lastDecision` | Clean text | `hearings.last_decision` | - | Low |
| `next_hearing` | date (YYYY-MM-DD) | No | - | Valid date | `nextHearing` | Parse DD/MM/YYYY → YYYY-MM-DD | `hearings.next_hearing` | - | Medium |
| `report` | boolean | No | - | TRUE/FALSE, 1/0, Yes/No | `report` | Normalize boolean | `hearings.report` | - | Low |
| `notify_client` | boolean | No | - | TRUE/FALSE, 1/0, Yes/No | `إخطار العميل بالقرار` | Normalize boolean | `hearings.notify_client` | - | Low |
| `attendee` | string | No | 255 | - | `ملاحظات الحاضر` | Clean text | `hearings.attendee` | - | Low |
| `attendee_1` | string | No | 255 | - | `حاضر 1` | Clean text | `hearings.attendee_1` | - | Low |
| `attendee_2` | string | No | 255 | - | `حاضر 2` | Clean text | `hearings.attendee_2` | - | Low |
| `attendee_3` | string | No | 255 | - | `حاضر 3` | Clean text | `hearings.attendee_3` | - | Low |
| `attendee_4` | string | No | 255 | - | `حاضر 4` | Clean text | `hearings.attendee_4` | - | Low |
| `next_attendee` | string | No | 255 | - | `حضور الجلسة القادمة` | Clean text | `hearings.next_attendee` | - | Low |
| `evaluation` | string | No | 255 | - | `صالح/ضد` | Clean text, normalize | `hearings.evaluation` | - | Low |
| `notes` | text | No | TEXT | - | `ملاحظات` | Clean text, merge with other notes | `hearings.notes` | - | Low |

### Original-Only Columns (No Template Destination)

| Original Column | Action | Rationale |
|---|---|---|
| `hearings_id` | **Map to `id`** | **Preserved from original export (not auto-generated)** |
| `تاريخ تبليغ القرار` | Merge to `notes` | Decision notification date (not in template) |
| `ملاحظات الدائرة` | Merge to `notes` | Circuit notes (not in template) |
| `مسلسل الدائرة` | Parse from `الدائرة` | Circuit serial (extracted during parsing) |

---

## DB Referential Integrity Check

### Foreign Key: `matter_id` → `cases.id`

**Status**: ✅ Required  
**Validation**: Must exist in `cases` table  
**Lookup Table**: See `Hearings_DB_Mappings.csv` (Cases section)

**Action Items**:
- Extract all distinct `matter_id` values from original CSV
- Cross-reference with `cases.id` in DB dump
- Flag orphaned `matter_id` values for manual review
- **Fallback**: Skip row with error log if `matter_id` not found

### Foreign Key: `lawyer_id` → `lawyers.id`

**Status**: ⚠️ Optional but recommended  
**Validation**: If provided, must exist in `lawyers` table  
**Lookup Table**: See `Hearings_DB_Mappings.csv` (Lawyers section)

**Resolution Strategy**:
1. **Option A (Recommended)**: Fuzzy match attendee names (`attendee`, `attendee_1-4`, `next_attendee`) against `lawyers.lawyer_name_ar` and `lawyers.lawyer_name_en`
2. **Option B**: Manual mapping file for ambiguous cases
3. **Fallback**: Set `lawyer_id = NULL` if no match found (acceptable per schema)

**Lawyer Matching Logic**:
- Normalize names: remove titles (د., أ., Mr., Ms.), trim whitespace
- Compare against both Arabic and English names
- Use Levenshtein distance for fuzzy matching (threshold: 85% similarity)
- Flag ambiguous matches for manual review

---

## Parsing & Normalization Specifications

### 1. Compound Field: `الدائرة` (Circuit)

**Pattern Examples**:
- `2 - رول (36) شق مستعجل` → Serial: `2`, Notes: `رول (36)`, Name: `شق مستعجل`
- `40 عمال` → Serial: `40`, Notes: ``, Name: `عمال`
- `5 اقتصادي` → Serial: `5`, Notes: ``, Name: `اقتصادي`

**Final Regex Set (Three-Tier Approach)**:

**Tier 1 - Strict Regex**:
```regex
/^(\d+)\s*-\s*(.+?)\s+([^\d]+)$/
```
- Group 1: Serial number
- Group 2: Notes (between `-` and last word)
- Group 3: Circuit name (last word/phrase)
- Example: `"2 - رول (36) شق مستعجل"` → Serial: 2, Notes: "رول (36)", Name: "شق مستعجل"

**Tier 2 - Common Regex**:
```regex
/^(\d+)\s*(?:-|\s+)?\s*(?:رول\s*\((\d+)\)\s*)?(.+)$/
```
- Group 1: Serial number
- Group 2: Roll number (optional, in parentheses)
- Group 3: Circuit name
- Example: `"5 - رول (36) اقتصادي"` → Serial: 5, Notes: "رول (36)", Name: "اقتصادي"
- Example: `"40 عمال"` → Serial: 40, Notes: null, Name: "عمال"

**Tier 3 - Fallback Regex**:
```regex
/^(\d+)\s*(.*)$/
```
- Group 1: Leading digits (serial)
- Group 2: Remaining text (name)
- Example: `"5 اقتصادي"` → Serial: 5, Name: "اقتصادي"

**Priority**: If `اسم الدائرة`, `ملاحظات الدائرة`, and `مسلسل الدائرة` columns exist and are populated, use them directly. Otherwise, parse `الدائرة`.

**Transformation**:
```php
function parseCircuit($circuitValue, $nameCol = null, $notesCol = null, $serialCol = null) {
    // Priority 1: Use split columns if available
    if ($nameCol && $notesCol && $serialCol) {
        return [
            'circuit' => cleanString($nameCol),
            'circuit_notes' => cleanString($notesCol),
            'circuit_serial' => parseInt($serialCol)
        ];
    }
    
    // Priority 2: Parse compound field
    if (preg_match('/^(\d+)\s*-\s*(.+?)\s+([^\d]+)$/', $circuitValue, $matches)) {
        return [
            'circuit' => cleanString($matches[3]),
            'circuit_notes' => cleanString($matches[2]),
            'circuit_serial' => (int)$matches[1]
        ];
    }
    
    // Fallback: Extract leading number
    if (preg_match('/^(\d+)\s*(.*)$/', $circuitValue, $matches)) {
        return [
            'circuit' => cleanString($matches[2] ?: $circuitValue),
            'circuit_notes' => '',
            'circuit_serial' => (int)$matches[1]
        ];
    }
    
    return [
        'circuit' => cleanString($circuitValue),
        'circuit_notes' => '',
        'circuit_serial' => null
    ];
}
```

### 2. Date Normalization

**Input Formats**:
- `DD/MM/YYYY` (e.g., `31/10/2017`)
- `YYYY-MM-DD` (already normalized)
- `YYYY/MM/DD` (e.g., `2017/10/10`)

**Output Format**: `YYYY-MM-DD`

**Parsing Logic**:
```php
function parseDate($value) {
    if (empty($value)) return null;
    
    // Remove whitespace
    $value = trim($value);
    
    // Try DD/MM/YYYY
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }
    
    // Try YYYY/MM/DD
    if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $value, $matches)) {
        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }
    
    // Try YYYY-MM-DD (already normalized)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }
    
    return null; // Invalid date
}
```

### 3. Text Cleanup

**Rules**:
1. Remove `_x000D_` (carriage return artifacts)
2. Remove zero-width characters (U+200B, U+200C, U+200D, U+FEFF)
3. Remove direction marks (U+200E, U+200F)
4. Collapse multiple whitespace to single space
5. Trim leading/trailing whitespace
6. Normalize Arabic digits (٠-٩) to English (0-9)

**Implementation**:
```php
function cleanString($value) {
    if (empty($value)) return null;
    
    // Remove Excel artifacts
    $value = str_replace('_x000D_', '', $value);
    
    // Remove zero-width and direction marks
    $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{200E}\x{200F}]/u', '', $value);
    
    // Normalize Arabic digits to English
    $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $value = str_replace($arabicDigits, $englishDigits, $value);
    
    // Collapse whitespace
    $value = preg_replace('/\s+/', ' ', $value);
    
    // Trim
    $value = trim($value);
    
    return $value ?: null;
}
```

### 4. Boolean Normalization

**Accepted Inputs**: `TRUE`, `FALSE`, `1`, `0`, `Yes`, `No`, `Y`, `N` (case-insensitive)

**Output**: `TRUE` or `FALSE` (boolean)

**Implementation**:
```php
function parseBoolean($value) {
    if (empty($value)) return false;
    
    $normalized = strtolower(trim($value));
    
    $truthy = ['true', '1', 'yes', 'y'];
    return in_array($normalized, $truthy);
}
```

---

## Transformation Pipeline (Deterministic, Ordered)

### Step 1: Load Source CSV
- Read `Hearings-Original.csv` with UTF-8 encoding
- Detect column headers (handle BOM if present)
- Validate required columns exist

### Step 2: Column Mapping & Rename
- Map original column names to template column names (see column mapping table)
- Handle legacy aliases (`shortDecision` → `short_decision`, etc.)
- **Map `hearings_id` → `id` (preserve original ID)**

### Step 3: Parse Compound Fields
- Parse `الدائرة` → `circuit`, `circuit_notes`, `circuit_serial`
- Merge `circuit_notes` into `notes` if template doesn't have separate column

### Step 4: Type Coercions
- **IDs**: `hearings_id` → `id` (preserve integer value)
- **Dates**: `date`, `next_hearing` → `YYYY-MM-DD`
- **Booleans**: `report`, `notify_client` → boolean
- **Integers**: `matter_id`, `lawyer_id` → integer (validate)

### Step 5: Text Normalization
- Apply `cleanString()` to all text fields
- Remove special characters, normalize digits, collapse whitespace

### Step 6: FK Resolution
- **`matter_id`**: Validate against `cases.id` lookup table
  - If not found: log error, skip row (or flag for review)
- **`lawyer_id`**: Fuzzy match attendee names against `lawyers` lookup table
  - If match found: set `lawyer_id`
  - If ambiguous: flag for manual review, set `NULL`
  - If no match: set `NULL` (acceptable)

### Step 7: Merge Original-Only Columns
- `تاريخ تبليغ القرار` → append to `notes`
- `ملاحظات الدائرة` → append to `notes`

### Step 8: Validation Checkpoints
- **ID uniqueness**: Check for duplicate `id` values (would cause PK violation)
- Required fields: `matter_id`, `date` must be present and valid
- FK constraints: `matter_id` must exist in DB
- Date formats: All dates must be valid `YYYY-MM-DD`
- Text lengths: Truncate if exceeds max length (255 for strings, TEXT for text fields)

### Step 9: Generate Output
- Write to `Hearings_Transformed_Sample.csv` (first 50 rows)
- Include error log for skipped/invalid rows
- Generate summary statistics

---

## Risk Assessment & Mitigation

| Risk | Level | Mitigation |
|---|---|---|
| Duplicate `id` values (from `hearings_id`) | **High** | Pre-import validation: check for duplicate IDs, resolve conflicts before import |
| Orphaned `matter_id` values | **High** | Pre-import validation: generate list of missing `matter_id`, resolve before import |
| Ambiguous `lawyer_id` matching | **Medium** | Fuzzy matching with manual review queue for ambiguous cases |
| Date parsing failures | **Medium** | Comprehensive date parsing with fallback to `NULL` + error log |
| Compound field parsing errors | **Low** | Use split columns if available, regex fallback, preserve original in notes if parsing fails |
| Text encoding issues | **Low** | UTF-8 validation, BOM removal, character normalization |

---

## Implementation Status

### ✅ Completed
1. ✅ Staging table (`hearings_staging`) created with audit columns
2. ✅ `HearingsDataNormalizer` service with 3-tier circuit regex
3. ✅ `HearingsFKResolver` service with deterministic resolution
4. ✅ CLI commands: `hearings:preflight`, `hearings:validate-staging`, `hearings:commit-staging`
5. ✅ QA validation SQL implemented in validate-staging command
6. ✅ Backup functionality before commit

### 📋 Usage

**Step 1: Preflight Import**
```bash
php artisan hearings:preflight --file=/path/to/Hearings-Original.csv
```
- Loads CSV/Excel → transforms → inserts into `hearings_staging`
- Generates artifacts in `storage/app/imports/hearings/YYYYMMDD-HHMM/`:
  - `db_mappings.csv` (IDs → names)
  - `unmatched.csv` (values requiring review)
  - `errors.json` (validation errors)
  - `summary.json` (import statistics)

**Step 2: Validate Staging**
```bash
php artisan hearings:validate-staging
```
- Runs all QA SQL checks from checklist
- Exits non-zero if high-severity issues found
- Checks: duplicate IDs, orphaned matter_ids, invalid dates, etc.

**Step 3: Commit to Production**
```bash
php artisan hearings:commit-staging --batch=2000
```
- Creates backup of `hearings`, `cases`, `lawyers` tables
- Migrates from staging to `hearings` in batches
- Transaction-safe (aborts on failure, leaves staging intact)

### Validation SQL Executed

The `hearings:validate-staging` command executes the following SQL checks:

1. **ID Uniqueness**:
```sql
SELECT id, COUNT(*) as count
FROM hearings_staging
WHERE id IS NOT NULL AND id != ''
GROUP BY id
HAVING count > 1;
```

2. **Required Fields**:
```sql
SELECT COUNT(*) FROM hearings_staging
WHERE matter_id IS NULL OR matter_id = '' OR date IS NULL OR date = '';
```

3. **Date Formats**:
```sql
SELECT COUNT(*) FROM hearings_staging
WHERE (date IS NOT NULL AND date != '' AND date NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$')
   OR (next_hearing IS NOT NULL AND next_hearing != '' AND next_hearing NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$');
```

4. **Matter ID Integrity**:
```sql
SELECT COUNT(*) FROM hearings_staging stg
LEFT JOIN cases c ON stg.matter_id = c.id
WHERE c.id IS NULL AND stg.matter_id IS NOT NULL AND stg.matter_id != '';
```

5. **Lawyer ID Integrity**:
```sql
SELECT COUNT(*) FROM hearings_staging stg
LEFT JOIN lawyers l ON stg.lawyer_id = l.id
WHERE l.id IS NULL AND stg.lawyer_id IS NOT NULL AND stg.lawyer_id != '';
```

6. **String Lengths**:
```sql
SELECT MAX(CHAR_LENGTH(procedure)) as max_procedure,
       MAX(CHAR_LENGTH(court)) as max_court,
       MAX(CHAR_LENGTH(circuit)) as max_circuit
FROM hearings_staging;
```

7. **Duplicate Rows**:
```sql
SELECT matter_id, date, COUNT(*) as count
FROM hearings_staging
GROUP BY matter_id, date
HAVING count > 1;
```

---

**Document Version**: 1.1  
**Last Updated**: 2025-01-XX  
**Change Log**: Updated ID handling to reflect preservation from original export (not auto-generated)

