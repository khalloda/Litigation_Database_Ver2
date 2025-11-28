# Δ Prompt — Revamp `Cases` Views to Show **All Fields** (+ Arabic/RTL, Bootstrap 5)

**Branch:** `feat/cases-views-revamp-full-fields`  
**Scope:** Analyze DB + current code and fully rewrite `cases/show.blade.php` and `cases/edit.blade.php` to (a) cover **every column** in `cases` and (b) present them in a clean, sectioned, bilingual UI (Bootstrap 5, RTL-ready), with helpers/partials and minimal controller tweaks (eager loads only).

---

## Inputs to Inspect
1) Database schema/length stats:
- `CasesTableData.csv`
- 

2) Current blades + controller:
- `show.blade.php`
- `edit.blade.php`
- `create.blade.php` (reference layout patterns only; don’t change)
- `CasesController.php`

> Use these files to detect **which columns are not yet rendered** and to carry over existing routes/authorization.

---

## Design Requirements (Bootstrap 5, Arabic/RTL-safe)

- Keep layout consistent with the app; use `.container-fluid` + `.row.g-3` patterns.
- Provide **Tabs** (desktop) with **Accordion** fallback (mobile) for these sections:
  1) **Overview** (IDs, names AR/EN, client, lawyers A/B, team, dates)
  2) **Parties** (client capacity, opponent(s) capacity, in-case names, notes)
  3) **Court & Circuit** (court, destination, circuit name/serial/shift, secretary, floor, hall)
  4) **Status & Progress** (matter_degree, status, importance, category, current_status, evaluation)
  5) **Financials** (allocated_budget, asked_amount, judged_amount, financial_provision, client_type, fee_letter/contract)
  6) **Documents & Hearings** (counts + compact list with “View all”)
  7) **Meta & Audit** (created_by/updated_by, created_at/updated_at, team_id, matter_shelf, branch, description, notes_1/notes_2)
- **Show every column** in `cases`. For foreign keys, display both a **linked label** and the raw ID (muted) if present.
- Long text (e.g., `matter_description`, `financial_provision`) → collapsed preview (2–3 lines) with “Show more”.
- Bilingual/RTL:
  - Add `dir="auto"` on value containers.
  - Keep date/time in `Y-m-d` by default; if locale=ar and you have a preferred format, add a helper for localized display.
- Safe widths: wrap long strings; preserve whitespace in large notes using `.text-wrap` and `.text-break`.

---

## Implementation Plan

### 1) Create a **Field Map** (one place to rule them all)
Add `app/Support/Cases/FieldMap.php` returning a structured map for **all columns** with label keys and tiny formatters:

```php
<?php
namespace App\Support\Cases;

class FieldMap {
  public static function all(): array {
    return [
      // Overview
      'id' => ['section' => 'overview', 'label' => 'ID', 'format' => 'raw'],
      'matter_name_ar' => ['section'=>'overview','label'=>'Matter (AR)','format'=>'text'],
      'matter_name_en' => ['section'=>'overview','label'=>'Matter (EN)','format'=>'text'],
      'team_id'        => ['section'=>'overview','label'=>'Team','format'=>'fk:team'],
      'matter_start_date' => ['section'=>'overview','label'=>'Start Date','format'=>'date'],
      'matter_end_date'   => ['section'=>'overview','label'=>'End Date','format'=>'date'],

      // Parties
      'client_id'           => ['section'=>'parties','label'=>'Client','format'=>'fk:client'],
      'client_in_case_name' => ['section'=>'parties','label'=>'Client in Case Name','format'=>'text'],
      'client_capacity_id'  => ['section'=>'parties','label'=>'Client Capacity','format'=>'fk:ref:client_capacity'],
      'client_capacity_note'=> ['section'=>'parties','label'=>'Client Capacity Note','format'=>'text'],
      'opponent_id'         => ['section'=>'parties','label'=>'Opponent','format'=>'fk:opponent'],
      'opponent_in_case_name'=>['section'=>'parties','label'=>'Opponent in Case Name','format'=>'text'],
      'opponent_capacity_id'=> ['section'=>'parties','label'=>'Opponent Capacity','format'=>'fk:ref:opponent_capacity'],
      'opponent_capacity_note'=>['section'=>'parties','label'=>'Opponent Capacity Note','format'=>'text'],
      'lawyer_a'            => ['section'=>'parties','label'=>'Lawyer A','format'=>'person'],
      'lawyer_b'            => ['section'=>'parties','label'=>'Lawyer B','format'=>'person'],

      // Court & Circuit
      'court_id'            => ['section'=>'court','label'=>'Court','format'=>'fk:court'],
      'matter_destination_id'=>['section'=>'court','label'=>'Matter Destination','format'=>'fk:court'],
      'circuit_name_id'     => ['section'=>'court','label'=>'Circuit Name','format'=>'fk:ref:circuit_name'],
      'circuit_serial_id'   => ['section'=>'court','label'=>'Circuit Serial','format'=>'fk:ref:circuit_serial'],
      'circuit_shift_id'    => ['section'=>'court','label'=>'Circuit Shift','format'=>'fk:ref:circuit_shift'],
      'circuit_secretary'   => ['section'=>'court','label'=>'Circuit Secretary','format'=>'text'],
      'court_floor_id'      => ['section'=>'court','label'=>'Court Floor','format'=>'fk:ref:court_floor'],
      'court_hall_id'       => ['section'=>'court','label'=>'Court Hall','format'=>'fk:ref:court_hall'],

      // Status & Progress
      'matter_degree_id'    => ['section'=>'status','label'=>'Degree','format'=>'fk:ref:matter_degree'],
      'matter_status_id'    => ['section'=>'status','label'=>'Status','format'=>'fk:ref:matter_status'],
      'matter_importance_id'=> ['section'=>'status','label'=>'Importance','format'=>'fk:ref:matter_importance'],
      'matter_category_id'  => ['section'=>'status','label'=>'Category','format'=>'fk:ref:matter_category'],
      'current_status'      => ['section'=>'status','label'=>'Current Status','format'=>'text'],
      'matter_evaluation'   => ['section'=>'status','label'=>'Evaluation','format'=>'text'],

      // Financials
      'client_type_id'      => ['section'=>'financials','label'=>'Client Type','format'=>'fk:ref:client_type'],
      'allocated_budget'    => ['section'=>'financials','label'=>'Allocated Budget','format'=>'money'],
      'matter_asked_amount' => ['section'=>'financials','label'=>'Asked Amount','format'=>'money'],
      'matter_judged_amount'=> ['section'=>'financials','label'=>'Judged Amount','format'=>'money'],
      'financial_provision' => ['section'=>'financials','label'=>'Financial Provision','format'=>'longtext'],
      'fee_letter'          => ['section'=>'financials','label'=>'Fee Letter','format'=>'text'],
      'contract_id'         => ['section'=>'financials','label'=>'Contract','format'=>'text'],

      // Meta & Audit
      'matter_shelf'        => ['section'=>'meta','label'=>'Shelf','format'=>'text'],
      'client_branch'       => ['section'=>'meta','label'=>'Client Branch','format'=>'text'],
      'matter_destination'  => ['section'=>'meta','label'=>'Matter Destination (legacy)','format'=>'text'],
      'matter_category'     => ['section'=>'meta','label'=>'Category (legacy)','format'=>'text'],
      'matter_degree'       => ['section'=>'meta','label'=>'Degree (legacy)','format'=>'text'],
      'matter_status'       => ['section'=>'meta','label'=>'Status (legacy)','format'=>'text'],
      'matter_description'  => ['section'=>'meta','label'=>'Description','format'=>'longtext'],
      'notes_1'             => ['section'=>'meta','label'=>'Notes 1','format'=>'longtext'],
      'notes_2'             => ['section'=>'meta','label'=>'Notes 2','format'=>'longtext'],
      'created_by'          => ['section'=>'meta','label'=>'Created By','format'=>'fk:user'],
      'updated_by'          => ['section'=>'meta','label'=>'Updated By','format'=>'fk:user'],
      'created_at'          => ['section'=>'meta','label'=>'Created At','format'=>'datetime'],
      'updated_at'          => ['section'=>'meta','label'=>'Updated At','format'=>'datetime'],
    ];
  }
}
```
> Adjust keys to match actual column names from CasesTableData.*. Include every column.

## 2)	Add small format helpers
Create app/Support/View/Format. php with methods like t ext () , longtext(), date(), datetime(),
money (), person(),and fk() (resolves label + link). Ensure dir="auto" and safe escaping.
## 3)	Rewrite resources/views/cases/show.blade.php
-	Keep header actions (Back/Edit/Delete) and permissions.
-	Replace the single table block with Bootstrap tabs ( nav nav-tabs , .tab-content ) for the sections above.
-	For each section, loop the Fieldrtap:: all() filtered by section , render via a reusable partial:
  - cases/partials/_field_row.blade.php (label + value; value uses Format helper).
-	Preserve current related blocks (hearings, tasks, documents) but place under Documents & Hearings tab with counts and 'View all" links.
-	Collapse long text with a .collapse and "Show more/less'.
## 4)	Rewrite resources/views/cases/edit .blade.php
-	Use accordion sections mirroring the tabs (Overview, Parties, Court & Circuit Status & Progress, Financials, Meta & Audit).
-	Bootstrap 5 forms: floating labels only for short forms; else standard labels + help text.
-	FK selectors: make them search-capable (plain Bootstrap + datalist for now; if you already have Select2/TomSelect, wire it).
-	Validation feedback: .is-invalid + .invalid-feedback.
-	Respect dir="auto" on all textareas/inputs.
-	For large textareas (description/notes/financial_provision) show remaining character count if you enforce max lengths.
## 5)	Controller minimal changes (no breaking logic)
-	In Cases Controllers how and @edit , eager load every relation used in Format helper to avoid N+1 (courts, destination, refs, client/opponent capacities, users, etc.).
-	Do not change store/update rules here (out of scope), just ensure the views have what they need to render.
## 6)	Partials to add
-	cases/partials/_tabs.blade.php (tab headers)
-	cases/partials/_tab_section. blade, php (w 'I'sa section)
-	cases/partials/_field_row. blade, php (label/value row; table-like or <dl> responsive)
-   cases/partials/_longtext.blade.php (collapsible presenter)
-   Keep cases/partials/_opponents.blade.php as-is, but move to Parties tab.

## 7) Edge Cases & Lengths
-	If longest_value_chars for a column exceeds a threshold (e.g., > 120), default it to the long-text presenter.
-	For nullable FKs: show a muted - and, if a legacy text column exists (e.g., matter_destination ), show that in parentheses.
## Acceptance Criteria
1.	All columns in cases are rendered across the tabs/accordions (no field left behind).
2.	No N+1 on show/edit; relations eager-loaded.
3.	All values safe-escaoed: dir="auto" applied to textual content; RTL looks correct.
4.	Long text cc Ask ChatGPT jates have consistent formatting.
5.	FK fields show name + link (if model exists) and raw ID muted.
6.	Related panels (hearings, documents, tasks) appear in the Documents & Hearings tab with counts, compact list and links.
7.	Zero breaking changes to store/update; only view layer + eager loading.
## Commit Plan
1.	feat(cases): add FieldKap + Format helpers
2.	 feat(cases): revamp show view with tabs + full-field rendering
3.	 feat(cases): revamp edit view with accordions + full-field inputs
4.	chore(cases): add view partials + collapse longtext
5.	perf(cases): eager load relations in controller show/edit
6.	test(cases): smoke tests to ensure every column is present in the DOM
After Merge (optional QoL)
-	Add per-field tooltips (help icons) if you have an internal glossary.
-	Add copy-to-clipboard for IDs and case numbers.
-	Add "Open in admin" links for FK models.
