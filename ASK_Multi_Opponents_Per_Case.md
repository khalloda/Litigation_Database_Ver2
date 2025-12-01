# Request: Support Multiple Opponents per Case (each with its own Capacity) — Design, Migration, Import/Export, and Impact Review

**Project**: Central Litigation Management  
**Tech**: Laravel 10 / PHP 8.4 / MySQL 9.1 / Bootstrap 5  
**DB**: `litigation_db_ver2` (utf8mb4_unicode_ci)  
**Current Source of Truth**: latest schema dump and current codebase (Trash bundles, Import/Export module, fuzzy/normalization preflight)

**Branch**: `feat/cases-multi-opponents`

---

## Goals

1) Enable **multiple opponents per case**, where **each opponent has its own capacity** (e.g., شخص طبيعي/شركة/مدعى عليه/مدعى عليه ثاني… or English equivalents).  
2) Preserve backward compatibility for existing data and flows.  
3) Minimize disruption to other modules (Hearings, Documents, Tasks, Engagement Letters, POAs, Audit/Trash).  
4) Update **Import/Export** and **preflight fuzzy match** to handle multi-opponent input/validation.  
5) Provide a full **impact analysis** + **migration/backfill** + **rollback** plan.

---

## Deliverables

- **DB design** (tables, columns, indexes, constraints, cascade/null rules).
- **Migrations** + **backfill script** from current single-opponent data to new relation.
- **Eloquent models/relations** with pivot attributes (capacity, is_primary, order, alias notes).
- **Repository/Service methods** for CRUD and attach/detach with audit.
- **Import/Export** changes (templates + validators + preflight fuzzy suggestions).
- **UI changes** (Case view “Opponents” panel/tab with capacities & ordering).
- **Trash/Audit** integration: include pivot items in deletion bundles and restore.
- **Permissions/Policies** for attaching/detaching opponents.
- **Tests** (unit/feature) and **ADR** documenting decisions.
- **Docs** (README updates + API docs + Import template docs).

---

## Constraints & Direction

- Keep **utf8mb4_unicode_ci** and Arabic/English support with normalization (existing preflight rules).
- **Capacity** values should come from our existing taxonomy (if present) or a new lookup table.  
- Ensure **soft delete**/cascade policies remain consistent with our Trash bundles strategy.

---

## Proposed Data Model (suggest and confirm)

### New / Adjusted Tables

1) `opponents`  
   - `id` (PK, BIGINT)  
   - `name` (VARCHAR(191)) — bilingual text allowed  
   - Optional metadata (e.g., `national_id`, `registration_no`, `type`) if already present elsewhere (reuse if we have table)  
   - Timestamps, soft deletes

2) `case_opponents` (pivot)  
   - `id` (PK, BIGINT) — prefer explicit PK to support audit/trash per pivot row  
   - `case_id` (FK → cases.id, ON DELETE CASCADE)  
   - `opponent_id` (FK → opponents.id, ON DELETE RESTRICT or CASCADE — justify choice)  
   - `capacity_id` (FK → option_values.id or `capacities` table; ON DELETE SET NULL)  
   - `is_primary` (TINYINT(1), default 0)  
   - `display_order` (INT, nullable)  
   - `alias_text` (VARCHAR(191), nullable) — free-text alias as seen in filings  
   - Timestamps, soft deletes  
   - Indexes: (`case_id`,`display_order`), (`case_id`,`opponent_id`), (`opponent_id`)  
   - Unique guard: (`case_id`,`opponent_id`,`capacity_id`) if we want to block exact duplicates

3) `capacities` (if not already in taxonomy)  
   - `id` (PK)  
   - `code` (e.g., DEFENDANT_1, DEFENDANT_2…)  
   - `name_en`, `name_ar`  
   - Seed minimal starter set; if we already have `option_values`, map there instead.

> If the existing schema already includes `opponents` and/or a taxonomy table for capacities, reuse them. Otherwise, create as above.

### Cases Table (existing)
- If we currently store a single opponent field (e.g., `opponent_name` or FK), **deprecate** it:
  - Keep the column temporarily for backward compatibility.
  - Add a **backfill** that converts existing data to `opponents` + `case_opponents` and sets the deprecated column to NULL or keeps it synchronized to the primary pivot for a transition period.
  - Mark in docs as **deprecated**.

---

## Migration & Backfill

1) Create new tables (`opponents`, `case_opponents`, optionally `capacities` or link to `option_values`).  
2) **Backfill script**:
   - Scan each case row: if old opponent column(s) exist, normalize and fuzzy-match to `opponents` (by name).  
   - If a match ≥ threshold: link via pivot (`case_opponents`) and set `is_primary=1`.  
   - Else: create a new `opponents` record and link it.  
   - Migrate any per-case “capacity” if such a column exists; otherwise default to a sensible capacity or leave NULL.
3) Optionally **sync-back** first/primary opponent to the legacy case column for temporary compatibility (documented), or mark legacy read-only.
4) Provide **rollback** migration and a backout path that re-populates the legacy field from the primary pivot.

---

## Eloquent & Services

- `CaseModel`  
  ```php
  public function opponents()
  {
      return $this->belongsToMany(Opponent::class, 'case_opponents')
          ->withPivot(['capacity_id','is_primary','display_order','alias_text'])
          ->using(\App\Models\Pivots\CaseOpponent::class)
          ->withTimestamps();
  }
  ```
- `Opponent`  
  ```php
  public function cases()
{
    return $this->belongsToMany(CaseModel::class, 'case_opponents')
        ->withPivot(['capacity_id','is_primary','display_order','alias_text'])
        ->withTimestamps();
}

  ```
  
  Add a small service layer:

attachOpponentToCase($case, $opponentInput, $capacity, $isPrimary=false, $order=null)

Handles normalization/fuzzy-lookup, prevents duplicates, updates order, sets primary atomically.
Import/Export Changes
Templates

Standard Cases template: keep one opponent slot (primary) with opponent_name (or opponent_id) + opponent_capacity.

Extended Cases template: add multiple opponent columns, e.g.:

opponent1_name, opponent1_capacity

opponent2_name, opponent2_capacity

opponent3_name, opponent3_capacity
(Keep reasonable max, e.g., 3–5; document how to add more via companion file.)

Companion relation import (recommended): create Case_Opponents_Import template (CSV/XLSX) with:

case_external_id (or case_number)

opponent_name (or opponent_id)

capacity (enum/lookup)

is_primary, display_order, alias_text
This scales for >5 opponents and stays clean.

Preflight & Fuzzy

Extend current Arabic/English normalization and fuzzy match to handle multiple opponent fields.

Conflict rules:

If *_id and *_name exist and disagree → prefer ID, log warning.

If multiple opponents normalize to the same target opponent → merge or warn per config.

Validation:

Enforce max opponents per case configurable; warn if exceeded.

UI Changes

Case view: Opponents tab/panel

List with capacity badges, is_primary star, drag to reorder.

Add/remove opponent modal with fuzzy search + capacity select.

Audit trail snippet on changes.

Trash & Audit

Ensure case_opponents pivot rows are included in DeletionBundle capture and restore (the trait/service must include pivot graph).

Audit: log attach/detach/update capacity/order events.

Policies/Permissions

Add granular permissions: case.opponents.view, case.opponents.edit.

Import permissions unchanged; ensure import path can attach opponents under correct roles.

Tests

Migration/backfill: single → multi mapping, idempotency.

Attach/detach: pivot uniqueness & ordering.

Import preflight: multiple opponents, mixed id/name, Arabic/English normalization.

UI: reorder primary and capacity change flows.

Trash restore: pivot rows restored with correct order/capacity.

Performance: N+1 avoided; indexes used.

Performance & Indexing

Add indexes on case_opponents(case_id, display_order), case_opponents(opponent_id).

If search by name is frequent, consider opponents(name) index; keep collation utf8mb4_unicode_ci; for LIKE searches, prefix indexing strategies where applicable.

Impact Analysis (what to inspect and report back)

Cases: legacy opponent columns → deprecated or synchronized?

Hearings/Tasks/Documents: check if any reference single opponent; adapt to read primary opponent for legacy UI where needed.

Import/Export: ensure old imports still work; new companion import added.

Trash/Audit: confirm pivot capture/restore implemented.

Reports: update any report that assumed single opponent.

Docs: update API, user docs, and ADR.

Acceptance Criteria

A case can have ≥1 opponents, each with capacity and order, with one optional is_primary.

Backfill completes with warnings ≤ configured threshold.

Import (Standard/Extended/Companion) supports multi-opponent and fuzzy suggestions.

UI shows/manage opponents with minimal clicks; actions audited.

Trash restore brings back the full opponent set correctly.

All docs/tests/ADR updated.

Commit Plan (small, reviewable steps)

feat(db): add opponents + case_opponents (+ capacities or map to option_values); migrations & seeders

feat(backfill): migrate legacy opponent fields to pivot; compatibility mode

feat(model): add relations + service layer for attach/detach/reorder/setPrimary

feat(import): extend preflight & templates; add Case_Opponents_Import companion

feat(ui): case opponents panel (list/reorder/assign capacity)

feat(trash): include pivot rows in bundles; restore logic

test(+docs+adr): coverage, README updates, ADR for multi-opponents