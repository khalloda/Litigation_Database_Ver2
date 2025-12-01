# Request: Adjust Cases Import Templates Plan (CSV + XLSX) — Final Refinements

**Branch**: `feat/import-templates-cases-refinements`  
**Scope**: Update the in-progress plan/branch for Cases templates to include the adjustments below so the solution fits our DB scale, Excel limits, and cPanel constraints.

---

## 1) Split Templates: Standard vs Extended
**Goal**: Reduce friction for users while keeping full coverage available.
- Generate **two templates**:
  - **Standard** — only core fields needed to create a viable case (IDs or names for required FKs, title, status, dates, key classifiers). Keep this short.
  - **Extended** — all remaining optional/legacy/less-used fields.
- Place both in `storage/app/templates/`:
  - `Cases_Import_Template_Standard.csv` / `.xlsx`
  - `Cases_Import_Template_Extended.csv` / `.xlsx`
- Import page UI: show two download buttons per format (CSV/XLSX) and a short tooltip describing each template.

---

## 2) FK Mapping: ID vs Name — Precedence & Conflict Handling
**Rule**: For any foreign key (e.g., client, court, opponent, lawyer), support **either** an ID column **or** a Name column.
- If both are present:
  - **Prefer the ID** (assume the user is being explicit).
  - Log a **warning** in the preflight report when ID and Name disagree.
- Document this in the README sheet and in `docs/imports/cases_template.md`.
- Add a preflight check that suggests closest matches when only Name is provided (Arabic/English normalization + fuzzy match).

**Naming** (example — adapt to actual schema):
- `client_id` **or** `client_name`
- `court_id` **or** `court_name`
- `opponent_id` **or** `opponent_name`
- `assigned_lawyer_id` **or** `assigned_lawyer_email` (if email mapping is supported)

---

## 3) Excel Data Validation: Use Only for Short Enums
**Do** add dropdowns for **short, stable** enumerations (named ranges):
- `status`, `case_type`, `stage`, `currency`, `importance`, `degree`, `branch`, `capacity` (adjust to actual DB).
**Don’t** add dropdowns for **large vocabularies** (e.g., courts, opponents). They don’t scale in Excel.
- For large lists: provide **free text** columns (`*_name`) and rely on preflight suggestions.

**XLSX details**:
- Create a `Lookups` sheet with a vertical list per enum.
- Define **named ranges** (e.g., `status_list`, `case_type_list`).
- Apply data validation to the corresponding columns in the Template sheet referencing the named ranges.

---

## 4) cPanel / PhpSpreadsheet Constraints
- Verify **extensions**: `ext-zip`, `ext-mbstring`, `ext-iconv` (and `ext-gd` or `ext-imagick` optional).
- Use **PhpSpreadsheet** with memory-friendly settings (avoid huge in-memory arrays):
  - Prefer **Xlsx** writer with cell caching (`MemoryGZip` if available).
  - Keep validation lists small; avoid thousands of data-validations.
- CSV: write with **UTF-8 + BOM** for Windows Excel compatibility.

---

## 5) Regeneration & Versioning
- Command: rename/extend to `php artisan templates:generate-cases --mode=standard|extended|all` (default `all`).
- In each generated file’s README:
  - Include **Template-Version**, **DB-Schema-Signature** (hash of `information_schema` cols for `cases`), and **Generated-At (UTC)**.
- Add an **Admin-only** button “Regenerate Templates” on the import page (or in an Admin tools page). Protect with CSRF + permissions.

---

## 6) Routes & Permissions
- Keep download endpoints but ensure permission names match our policy:
  - Use a view-level permission, e.g., `import.view_template` (not `import.create`).
- Return 403 when user lacks permission, not 404 (so auth errors aren’t masked).

---

## 7) Documentation Enhancements
- Update `docs/imports/cases_template.md`:
  - Two sections: **Standard** and **Extended** columns table (name, type, required, notes).
  - **ID vs Name precedence** rule called out with examples.
  - Arabic/English examples; note **UTF-8** requirement.
  - State that large lists (courts/opponents) are **not** dropdowns—preflight will suggest matches.

---

## 8) README Sheet Content (for both XLSX variants)
Include these blocks at the top (adjust to real columns):
- **Encoding/Date/Decimal** rules.
- **Required field definition**: “Required if column is NOT NULL without default in DB, unless auto-generated.”
- **ID vs Name Precedence**: If both present, ID wins; disagreement logs a warning.
- **Normalization**: Arabic/English normalization and fuzzy match used for party/court names.
- **Limitations**: Only short enums get dropdowns; big vocabularies rely on preflight.
- **Versioning**: Template-Version, DB-Schema-Signature, Generated-At.

---

## 9) Tests (must add)
- **Header Parity Test**: assert CSV/XLSX headers match computed columns from `information_schema` (given a test fixture).
- **Validation Presence Test**: assert data validation exists for expected enum columns in XLSX.
- **ID>NAME Precedence Test**: simulate a row with conflicting `*_id` and `*_name`; ensure preflight prefers ID and logs warning.
- **UTF-8 BOM Test**: ensure CSV includes BOM and Arabic characters persist.
- **Permissions Test**: unauthenticated/unauthorized users cannot download templates (403).

---

## 10) Acceptance Criteria (update)
- Four downloads exist and work: Standard & Extended in **CSV** and **XLSX**.
- XLSX uses named ranges and validation only for short enums.
- README sheet shows version + schema hash + generation time.
- Preflight enforces ID>NAME precedence and logs conflicts.
- CSVs are UTF-8 with BOM; Arabic displays correctly in Excel.
- Command `templates:generate-cases --all` rebuilds all four files.
- Permissions enforced with `import.view_template`.
- Docs clearly explain Standard vs Extended and mapping rules.

---

## Commit Plan
1. `feat(templates): split into Standard/Extended; add --mode flag to generator`  
2. `feat(xlsx): named ranges + validation only for short enums; README with version/schema hash/timestamp`  
3. `feat(ui): add four download buttons with helper text; admin regenerate action`  
4. `refactor(permissions): use import.view_template; return 403 on unauthorized`  
5. `docs(imports): expand with Standard vs Extended; ID vs Name precedence; i18n notes`  
6. `test(templates): headers parity, validations presence, UTF-8 BOM, precedence conflict, permissions`

