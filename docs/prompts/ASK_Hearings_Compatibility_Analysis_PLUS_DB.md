Goal: Compare the legacy Hearings-Original.xlsx with the Hearings_Import_Template.xlsx, and also validate against the live database dump to ensure 100% compatibility and referential integrity (Clients, Lawyers, Courts, Cases, Hearings, and related lookup tables). Produce a precise remediation plan and sample transformed output.

Files to Analyze (use these exact paths)

Original (legacy export):
D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\HearingsImport\Hearings-Original.xlsx

Target Template (new system import):
D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\HearingsImport\Hearings_Import_Template.xlsx

Live Database Dump (MySQL):
D:\Claude\Litigation_Database_Ver2\Litigation_Database_Ver2\DB_DUMP\litigation_db_ver2.sql

Scope of DB Cross-Check

From the SQL dump, infer schema, PK/FK, indexes, and controlled vocabularies for at least:

clients, lawyers, courts, cases, hearings

Any reference/lookup tables used by the hearings importer (e.g., circuit names, hearing types/status, halls/shifts, matter degrees/status, destinations).

Build a data dictionary for the template-required columns and confirm the following against the DB schema:

Expected data types, nullability, max lengths, enum/domain constraints.

Foreign key relationships: verify that values to be imported will resolve to integer IDs (e.g., court_id, lawyer_id, case_id, circuit_name_id).

Character set/collation expectations: utf8mb4_unicode_ci.

Known Legacy “Merged Field” Example (and how to split)

The original file includes compound values in certain columns. Example (Arabic):

Column الدائرة might contain: 2 - رول (36) شق مستعجل
This must be split into three target columns:

اسم الدائرة → شق مستعجل

ملاحظات الدائرة → رول (36)

مسلسل الدائرة → 2 (as integer)

Your tasks:

Identify all columns that contain compound/merged semantics and specify a deterministic parsing strategy (regex/masks) for each.

Produce ready-to-use regex and/or parsing functions (pseudocode acceptable), including Arabic numerals handling and whitespace/ZWSP cleanup.

Deliverables

Schema Diff Report (Table)

For each template column:

Template column name

Expected type/format, required?, max length, enum/domain

Matched source column(s) in Original

Transformation required (Yes/No + how)

DB validation: which table/column it maps to, FK expectations, index/constraints

Risk level (Low/Med/High)

List original-only columns (no destination) and whether to drop or store as notes.

DB Referential Integrity Check

For columns that map to FK IDs (e.g., courts, circuits, hearing types/status, hall/shift, case references, lawyer references):

Build lookup tables from the DB dump (IDs → names).

For each distinct original value: indicate whether it matches a DB value; if not, propose mapping (legacy → DB value) and flag “Needs confirmation” where ambiguous.

Include counts of valid matches, missing, ambiguous, and proposed mappings.

Parsing & Normalization Spec

For every compound field (like الدائرة), provide:

Regex/logic (with examples) to extract subcomponents.

Type coercions, date/time parsing masks, Arabic/English digit normalization (١٢٣ → 123).

Cleanup rules: trim, collapse whitespace, remove _x000D_, zero-width characters, direction marks.

Include validation checkpoints (row counts, distinct values, error buckets).

Transformation Pipeline (Deterministic, Ordered)

Column rename/reorder

Value mappings (legacy → DB canonical)

Splitting compound fields into multiple columns

Type coercions and format normalization

FK resolution (name → ID lookups); specify fallback behavior (skip row vs. send to review list)

Final template-compliant dataframe/sheet

Outputs to Produce

Hearings_Compatibility_Report.md — full analysis and remediation plan.

Hearings_DB_Mappings.csv — canonical lookup tables extracted from DB (IDs → names) and proposed value mappings (legacy → canonical).

Hearings_Transformed_Sample.xlsx — run pipeline on first 50 rows, meeting 100% template & DB FK constraints.

Hearings_QA_Checklist.md — pre-import QA (collation, date masks, FK counts, unmatched list).

Constraints & Quality Bar

Do not change code/migrations; this step is analysis + transformation plan.

Assume MySQL 9.1, utf8mb4_unicode_ci.

Datetime formats: confirm from template; prefer YYYY-MM-DD or YYYY-MM-DD HH:MM (24h).

Be explicit with Arabic content; do not transliterate; when displaying examples, prefer dir="auto".

Provide Option A/B where multiple strategies exist, and choose a recommended one.

Nice-to-Have

A short PHP (Laravel) or Python snippet to implement the pipeline (Original → Template + FK resolution using DB lookups built from the dump).

Begin by loading both Excel files and the SQL dump, deriving the DB dictionaries (IDs/names), and then produce the deliverables above. Focus on correctness and determinism of the conversion rules.