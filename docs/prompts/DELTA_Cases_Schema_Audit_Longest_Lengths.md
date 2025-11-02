# Δ Prompt — Cases Schema Audit (Field Types + Longest Value Lengths)

**Mode:** Ask (read-only)  
**Scope:** Only analyze the `cases` table and produce a report. **Do not modify data or schema. Do not generate migrations.**

---

## Goal
The current Cases **View/Edit** screens do not show all data. I need a **complete table** listing, for the **`cases`** table only:

- **field_name** (column name)  
- **data_type** (from `INFORMATION_SCHEMA`)  
- **longest_value_chars** (the current maximum character length among existing rows)

**Do nothing else.** No writes, no schema changes, no non-essential output.

---

## Environment (Read-Only Access)
- DB: `litigation_db_ver2`  
- Host: `localhost`  
- Port: `3306`  
- User: `root`  
- Pass: `1234`  
- Collation: `utf8mb4_unicode_ci`

> Use a read-only connection if available; otherwise strictly avoid any write statements.

---

## Exact Steps

1) **Connect** to MySQL with the credentials above and `USE litigation_db_ver2;`

2) **Fetch the column list** and data types for `cases` from `INFORMATION_SCHEMA.COLUMNS`:

```sql
SELECT 
  COLUMN_NAME,
  DATA_TYPE,
  IS_NULLABLE,
  CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'litigation_db_ver2'
  AND TABLE_NAME   = 'cases'
ORDER BY ORDINAL_POSITION;
```
3) **Compute** longest_value_chars per column by dynamically generating a single UNION query that casts all columns to CHAR before measuring length (to handle numeric/date types). Do not select actual values.

```sql
SET SESSION group_concat_max_len = 1000000;

SELECT CONCAT(
  'SELECT ', 
  GROUP_CONCAT(
    CONCAT(
      QUOTE(COLUMN_NAME), ' AS field_name, ',
      QUOTE(DATA_TYPE),    ' AS data_type, ',
      'MAX(CHAR_LENGTH(CAST(`', COLUMN_NAME, '` AS CHAR))) AS longest_value_chars'
    )
    SEPARATOR ' FROM `cases` UNION ALL SELECT '
  ),
  ' FROM `cases`;'
) AS dyn_sql
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'litigation_db_ver2'
  AND TABLE_NAME   = 'cases';
```

4) Output

- Produce one Markdown table with exactly these columns and no extras:
  - field_name | data_type | longest_value_chars
- Sort by the original column order (ORDINAL_POSITION) if possible; otherwise by field_name.
- Ensure numbers are numeric, not quoted.

5) Constraints
- Do not print sample data values. Only lengths.
- Do not change the database.
- Do not run migrations, seeders, or formatters.
- No additional commentary beyond the final Markdown table.

## Acceptance Criteria

- The final answer is a single Markdown table with every column in cases, listing:
 - Column name
 - Data type (as in INFORMATION_SCHEMA.DATA_TYPE)
 - The maximum CHAR_LENGTH(CAST(value AS CHAR)) observed in current rows
- No schema or data modifications occurred.
- No extra text besides the table (a one-line title is OK).
> If there are permission errors or the database/table is missing, report a one-line error and stop.