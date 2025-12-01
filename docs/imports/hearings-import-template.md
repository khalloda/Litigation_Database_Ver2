# Hearings Import Template

This document describes the import template format for importing hearing records into the Central Litigation Management system.

## Supported Formats
- **CSV** (`.csv`) - Comma-separated values
- **Excel** (`.xlsx`, `.xls`) - Microsoft Excel format

## Required Headers

The import file must include the following column headers (in any order). Headers can be in English or Arabic:

### Required Fields

| Excel Column (English) | Excel Column (Arabic) | Database Column | Type | Required | Description |
|---|---|---|---|---|---|
| `matter_id` | `matter_id` | `matter_id` | integer | **Yes** | Case/Matter ID (foreign key to `cases` table) |
| `date` | `date` | `date` | date | **Yes** | Hearing date (YYYY-MM-DD) |

### Optional Fields

| Excel Column (English) | Excel Column (Arabic) | Database Column | Type | Description |
|---|---|---|---|---|
| `lawyer_id` | `lawyer_id` | `lawyer_id` | integer | Lawyer ID (foreign key to `lawyers` table) |
| `procedure` | `الإجراء` | `procedure` | string | Procedure type (e.g., "محكمة", "Court") |
| `court` | `المحكمة` | `court` | string | Court name |
| `circuit` | `الدائرة` | `circuit` | string | Circuit name |
| `destination` | `الجهة` | `destination` | string | Destination/venue |
| `decision` | `decision` | `decision` | text | Full decision text |
| `short_decision` | `shortDecision` | `short_decision` | string | Short decision summary |
| `last_decision` | `lastDecision` | `last_decision` | string | Previous decision |
| `next_hearing` | `nextHearing` | `next_hearing` | date | Next hearing date (YYYY-MM-DD) |
| `report` | `report` | `report` | boolean | Report required (TRUE/FALSE, 1/0, Yes/No) |
| `notify_client` | `إخطار العميل بالقرار` | `notify_client` | boolean | Notify client of decision (TRUE/FALSE, 1/0, Yes/No) |
| `attendee` | `الحاضر` | `attendee` | string | Primary attendee name |
| `attendee_1` | `حاضر 1` | `attendee_1` | string | First additional attendee |
| `attendee_2` | `حاضر 2` | `attendee_2` | string | Second additional attendee |
| `attendee_3` | `حاضر 3` | `attendee_3` | string | Third additional attendee |
| `attendee_4` | `حاضر 4` | `attendee_4` | string | Fourth additional attendee |
| `next_attendee` | `حضور الجلسة القادمة` | `next_attendee` | string | Attendee for next hearing |
| `evaluation` | `صالح/ضد` | `evaluation` | string | Evaluation (e.g., "صالح", "ضد", "Favorable", "Against") |
| `notes` | `ملاحظات` | `notes` | text | Additional notes |

### Legacy/Alternative Headers

The system also recognizes these alternative column names from MS Access exports:

- `hearings_id` → `id` (will be ignored on import; new ID auto-generated)
- `decision` → `decision`
- `shortDecision` → `short_decision`
- `lastDecision` → `last_decision`
- `nextHearing` → `next_hearing`

## Data Format Requirements

### Dates
- Format: `YYYY-MM-DD` (e.g., `2024-01-15`)
- Alternative formats accepted: `DD/MM/YYYY`, `MM/DD/YYYY`, `YYYY-MM-DD HH:MM:SS`
- Empty cells are accepted for optional date fields

### Boolean Fields (`report`, `notify_client`)
- Accepted values: `TRUE`, `FALSE`, `1`, `0`, `Yes`, `No`, `Y`, `N`
- Case-insensitive
- Empty cells default to `FALSE`

### Foreign Keys (`matter_id`, `lawyer_id`)
- Must be valid integer IDs that exist in the referenced tables
- `matter_id` is required; `lawyer_id` is optional
- If `lawyer_id` is provided but doesn't exist, the import will fail

### Text Fields
- Maximum lengths:
  - `procedure`, `court`, `circuit`, `destination`: 255 characters
  - `short_decision`, `last_decision`: 255 characters
  - `attendee` fields: 255 characters each
  - `evaluation`: 255 characters
  - `decision`, `notes`: TEXT (unlimited, but recommended < 65,535 characters)
- Special characters are preserved
- Newlines in text fields are supported

## Import Process

1. **Preflight Validation**: The system validates all rows before importing
   - Checks foreign key references (`matter_id`, `lawyer_id`)
   - Validates date formats
   - Checks required fields
   - Identifies duplicates and conflicts

2. **Fuzzy Matching**: For text fields, the system can suggest matches for:
   - `lawyer_id` (by lawyer name)
   - Other text fields can be matched against existing values

3. **Error Resolution**: Any validation errors must be resolved before import proceeds

4. **Batch Import**: All valid records are imported in a single transaction

## Sample CSV Format

```csv
matter_id,date,procedure,court,circuit,destination,decision,short_decision,last_decision,next_hearing,report,notify_client,attendee,attendee_1,attendee_2,attendee_3,attendee_4,next_attendee,evaluation,notes
64,2010-04-07,محكمة,شمال القاهرة,40 عمال,القاهرة,أول جلسة -قررت المحكمة التأجيل لجلسة 19-5-2010 لسند الكالة عن الشركة والاطلاع.,أول جلسة,أول جلسة,2010-05-19,TRUE,FALSE,أحمد سعيد,محمد الغرابلي,محمود شعبان,,,,صالح,ملاحظات إضافية
```

## Sample Excel Format

| matter_id | date | procedure | court | circuit | decision | short_decision | next_hearing | report | notify_client | attendee |
|-----------|------|-----------|-------|---------|----------|----------------|--------------|--------|---------------|----------|
| 64 | 2010-04-07 | محكمة | شمال القاهرة | 40 عمال | أول جلسة... | أول جلسة | 2010-05-19 | TRUE | FALSE | أحمد سعيد |

## Validation Rules

- **Required Fields**: `matter_id`, `date`
- **Foreign Key Constraints**: `matter_id` must exist in `cases` table
- **Date Validation**: Dates must be valid calendar dates
- **Duplicate Detection**: Same `matter_id` + `date` combinations may trigger warnings

## Notes

- The `id` field is auto-generated; do not include it in import files
- Audit fields (`created_by`, `updated_by`, `created_at`, `updated_at`) are automatically set
- Soft deletes are supported; deleted records are not included in exports by default
- Multiple attendees can be specified using `attendee`, `attendee_1`, `attendee_2`, etc.

## Export Compatibility

The export template matches this import format, so exported files can be edited and re-imported.


