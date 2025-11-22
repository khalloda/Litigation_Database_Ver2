# Hearings Export Template

This document describes the export format used when exporting hearing records from the Central Litigation Management system.

## Export Formats
- **CSV** (`.csv`) - Comma-separated values
- **Excel** (`.xlsx`) - Microsoft Excel format

## Column Headers

Exports use English column headers matching the database column names:

| Column Header | Type | Description |
|---|---|---|
| `id` | integer | Unique hearing ID (primary key) |
| `matter_id` | integer | Case/Matter ID (foreign key to `cases` table) |
| `lawyer_id` | integer | Lawyer ID (foreign key to `lawyers` table), nullable |
| `date` | date | Hearing date (YYYY-MM-DD) |
| `procedure` | string | Procedure type |
| `court` | string | Court name |
| `circuit` | string | Circuit name |
| `destination` | string | Destination/venue |
| `decision` | text | Full decision text |
| `short_decision` | string | Short decision summary |
| `last_decision` | string | Previous decision |
| `next_hearing` | date | Next hearing date (YYYY-MM-DD), nullable |
| `report` | boolean | Report required (TRUE/FALSE) |
| `notify_client` | boolean | Notify client of decision (TRUE/FALSE) |
| `attendee` | string | Primary attendee name |
| `attendee_1` | string | First additional attendee |
| `attendee_2` | string | Second additional attendee |
| `attendee_3` | string | Third additional attendee |
| `attendee_4` | string | Fourth additional attendee |
| `next_attendee` | string | Attendee for next hearing |
| `evaluation` | string | Evaluation (e.g., "صالح", "ضد") |
| `notes` | text | Additional notes |
| `created_at` | datetime | Record creation timestamp |
| `updated_at` | datetime | Last update timestamp |

## Data Format

### Dates
- Format: `YYYY-MM-DD` (e.g., `2024-01-15`)
- Empty dates are exported as empty cells

### Boolean Fields
- Format: `TRUE` or `FALSE`
- Empty values are exported as empty cells

### Foreign Keys
- Exported as integer IDs
- Empty values (NULL) are exported as empty cells

### Text Fields
- Preserved exactly as stored in database
- Newlines and special characters are included
- Empty values are exported as empty cells

## Export Filters

Exports can be filtered by:
- **Case/Matter ID**: Export hearings for specific cases
- **Date Range**: Export hearings within a date range
- **Lawyer ID**: Export hearings for specific lawyers
- **Next Hearing Date**: Export hearings with upcoming dates

## Sample Export (CSV)

```csv
id,matter_id,lawyer_id,date,procedure,court,circuit,destination,decision,short_decision,last_decision,next_hearing,report,notify_client,attendee,attendee_1,attendee_2,attendee_3,attendee_4,next_attendee,evaluation,notes,created_at,updated_at
1,64,,2010-04-07,محكمة,شمال القاهرة,40 عمال,القاهرة,أول جلسة -قررت المحكمة التأجيل لجلسة 19-5-2010 لسند الكالة عن الشركة والاطلاع.,أول جلسة,أول جلسة,2010-05-19,TRUE,FALSE,أحمد سعيد,محمد الغرابلي,محمود شعبان,,,,,صالح,ملاحظات إضافية,2024-01-15 10:30:00,2024-01-15 10:30:00
```

## Import Compatibility

Exported files can be edited and re-imported using the Hearings Import Template. When re-importing:

- The `id` column will be ignored (new IDs auto-generated)
- `created_at` and `updated_at` will be reset to current timestamp
- All other columns follow the same validation rules as imports

## Related Records

When exporting, you may want to also export:
- **Cases**: To get full case context for each hearing
- **Lawyers**: To get lawyer details for `lawyer_id` references

Use the respective export templates for those entities.

## Notes

- Deleted records (soft deletes) are excluded from exports by default
- Large text fields (`decision`, `notes`) may be truncated in Excel display but full data is preserved
- Multi-line text fields maintain newlines in CSV format (may require proper CSV handling)


