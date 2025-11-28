# Δ Prompt — Implement Reusable Import Profiles + Searchable Choices + Preflight Auto-Apply (Generic Across Fields)

**Branch:** `feat/import-profiles-and-choices`  
**Scope:** Persist, search, and auto-apply import decisions (**match / alias / capacity / ignore**) for **any matched field** in any table (e.g., `court_id`, `lawyer_id`, `client_id`, `opponent_id`, `hearing_room_id`, etc.). Integrate into **preflight**; add Admin UI to manage profiles/choices.

This extends the import pipeline so that **any column** participating in entity matching can leverage saved choices. Use a small **resolver registry** to map a column (or semantic key) to the target Eloquent model and its lookup strategy.

---

## 0) Key Guarantees

- Works for **any matched field**: `court_id`, `lawyer_id`, `opponent_id`, `client_id`, etc.  
- Choices lookup is based on `(table_name, column, normalized_value)` and returns an **action**:
  - `match` → bind to `{entity_model, entity_id}`
  - `alias` → store alias text for the resolved entity (if provided)
  - `capacity` → pivot/attribute decision (where applicable)
  - `ignore` → skip/clear field
- **ID > Name** precedence still applies (if the file supplies a valid ID, it wins).  
- Applicable in **Arabic** and **English** with the existing normalization rules.

---

## 1) Database — Migrations (MySQL 9.1, utf8mb4_unicode_ci)

Create two tables: `import_profiles` and `import_choices`. Keep per-batch decisions inside `import_sessions` JSON for history.

### 1.1 `import_profiles`
- `id` BIGINT PK  
- `name` VARCHAR(128) unique not null  
- `table_name` VARCHAR(64) not null                    // e.g., 'cases', 'clients'  
- `header_hash` CHAR(64) nullable                      // SHA-256 of **sorted** header signature  
- `is_active` TINYINT(1) default 1  
- `settings_json` JSON nullable                        // conflict strategy, thresholds, toggles  
- `created_by` BIGINT nullable                         // users.id  
- `updated_by` BIGINT nullable  
- timestamps

**Indexes:** (`table_name`,`header_hash`), (`is_active`)

### 1.2 `import_choices`
- `id` BIGINT PK  
- `profile_id` BIGINT not null FK → import_profiles(id) ON DELETE CASCADE  
- `table_name` VARCHAR(64) not null                    // redundancy for faster queries  
- `column` VARCHAR(128) not null                       // == Field (e.g., 'court_name', 'lawyer_name')  
- `raw_value` VARCHAR(255) nullable                    // exact user-provided text  
- `normalized_value` VARCHAR(255) not null  
- `action` ENUM('match','alias','capacity','ignore') not null  
- `entity_model` VARCHAR(128) nullable                 // for action=match (e.g., App\Models\Court)  
- `entity_id` BIGINT nullable                          // for action=match  
- `metadata_json` JSON nullable                        // { alias_text, capacity_id, notes, score_band, extra }  
- `is_active` TINYINT(1) default 1  
- `created_by` BIGINT nullable  
- `updated_by` BIGINT nullable  
- timestamps

**Indexes:**  
- (`profile_id`)  
- (`table_name`,`column`,`normalized_value`,`is_active`)  
- (`action`,`entity_model`,`entity_id`)

> No unique constraints beyond PK; we evolve choices via `is_active` and admin edits.

---

## 2) Eloquent Models

### 2.1 `app/Models/ImportProfile.php`
- Fillable: `name, table_name, header_hash, is_active, settings_json`  
- Relations: `choices(): hasMany(ImportChoice::class)`  
- Scopes: `active()`, `forTable($name)`, `withHeaderHashOrNull($hash)`

### 2.2 `app/Models/ImportChoice.php`
- Fillable: `profile_id, table_name, column, raw_value, normalized_value, action, entity_model, entity_id, metadata_json, is_active`  
- BelongsTo: `profile()`  
- Casts: `metadata_json` → array  
- Scopes: `active()`, `forTable($name)`, `forColumn($column)`, `forValue($normalized)`

---

## 3) Resolver Registry (Generic Matching Engine)

Create `app/Services/Import/ResolverRegistry.php` to map **columns** (or semantic keys) to resolver strategies:

```php
final class ResolverRegistry
{
    /** @return array<string, callable(array $row, string $rawValue): ?array> */
    public static function map(): array
    {
        return [
            // Examples for cases import
            'court_name'    => [\App\Services\Import\Resolvers\CourtResolver::class, 'resolve'],
            'lawyer_name'   => [\App\Services\Import\Resolvers\LawyerResolver::class, 'resolve'],
            'client_name'   => [\App\Services\Import\Resolvers\ClientResolver::class, 'resolve'],
            'opponent_name' => [\App\Services\Import\Resolvers\OpponentResolver::class, 'resolve'],
            // add more as needed (judge_name, chamber_name, etc.)
        ];
    }
}
```

> Each resolver returns either null or:
- ['entity_model' => Court::class, 'entity_id' => 123, 'normalized_value' => '...']
> Resolvers should handle ID > Name precedence (if row has a valid *_id, prefer it and exit early).

---

## 4) Normalization Helper
App\Services\Import\NameNormalizer (existing or new):
- Arabic: trim, Tatweel/diacritics removal, Hamza unify, collapse whitespace, do not swap ة↔ه.
- English: lowercase, Unicode NFKC, collapse whitespace.
- API: normalize(string $value, string $locale='auto'): string
> Store both raw_value and normalized_value in import_choices (raw makes the UI searchable; normalized drives matching).

---

## 5) Profile Selection & Preflight Application

Create app/Services/Import/ImportProfileService.php:
- selectProfile(string $table, array $headers): ?ImportProfile
  - header_hash = sha256(json_encode(sort(headers)))
  - Prefer exact (table_name, header_hash, is_active=1), fallback (table_name, header_hash IS NULL, is_active=1)
- applyProfile(ImportProfile $profile, array $rows, array $options=[]): AppliedChoicesResult
  - For each row[column]: normalize value; lookup choice by (table_name,column,normalized_value,is_active=1)
  - On hit:
   - action=match → set {entity_model, entity_id} on the row’s resolved data
   - action=alias → stage alias application (once entity known)
   - action=capacity → stash capacity_id for pivot write
   - action=ignore → clear/skip field
  - Return {hits, misses, decisions} for UI banner & modal
- persistChoicesFromPreflight(ImportProfile $profile, array $decisions, User $actor)
  - Upsert choices when user ticks “Remember these decisions”.

Wire into preflight (Cases first):
1. Parse headers → compute header_hash → selectProfile('cases', headers)
2. If profile exists, run applyProfile(); show banner “Profile applied: {name} (hits: N, misses: M)”
3. On confirm, persistChoicesFromPreflight() when “Remember decisions” is checked

Decision priority (per field):
1. Profile choice (if present)
2. Exact ID present in file (ID > Name)
3. ResolverRegistry (fuzzy/aliases)
4. Manual choice in UI

---

## 6) Admin UI — Searchable Saved Choices
Routes under admin/import with can:import.manage:
```php
Route::prefix('admin/import')->middleware(['auth','can:import.manage'])->group(function(){
  Route::resource('profiles', Admin\\ImportProfilesController::class)->only(['index','create','store','edit','update','destroy']);
  Route::get('profiles/{profile}/choices', [Admin\\ImportChoicesController::class,'index'])->name('profiles.choices.index');
  Route::post('profiles/{profile}/choices', [Admin\\ImportChoicesController::class,'store'])->name('profiles.choices.store');
  Route::patch('profiles/{profile}/choices/{choice}', [Admin\\ImportChoicesController::class,'update'])->name('profiles.choices.update');
  Route::delete('profiles/{profile}/choices/{choice}', [Admin\\ImportChoicesController::class,'destroy'])->name('profiles.choices.destroy');
  Route::get('profiles/{profile}/export', [Admin\\ImportProfilesController::class,'export'])->name('profiles.export');
  Route::post('profiles/{profile}/import', [Admin\\ImportProfilesController::class,'import'])->name('profiles.import');
});
```
Blade list (resources/views/admin/import/choices/index.blade.php):
- Filters: table_name, column, action (match|alias|capacity|ignore), search by raw_value/normalized_value
- Table columns (your requested mental model):
  - Field (column)
  - original_from_import (raw_value) — tooltip shows normalized
  - choice_type (action) badge
  - choice_value (entity display or capacity label or —)
  - Profile • Active toggle • Updated_at
- Actions: Edit, Activate/Deactivate, Delete, Export JSON
- Pagination + summary
RTL-safe, Bootstrap 5 (.table.table-sm.table-hover.align-middle, etc.).

---

## 7) Permissions / Audit

- Ability: import.manage for Admin UI
- Track created_by / updated_by on profiles and choices
- Prefer soft deactivation (is_active=0), only hard delete for admins

---

## 8) Tests (Pest)

- Migrations: tables + indexes
- Profile selection: exact header hash vs fallback
- Apply profile: match/alias/capacity/ignore actions reflected; hits/misses counts
- Persist choices: upsert + inactive ignored
- Admin UI: filters/search; export/import JSON
- Preflight: choice priority order; ID > Name enforced
- Arabic normalization: ة not swapped; diacritics/tatweel removed
- Any field coverage: sample for court_name, lawyer_name

---

## 9) Seeders (Optional)

- Default cases profile (headerless).
- Example choices:
 - column=court_name, normalized=القاهرة الاقتصادية, action=match, entity_model=App\\Models\\Court, entity_id=101
 - column=lawyer_name, normalized=أحمد سامي, action=match, entity_model=App\\Models\\Lawyer, entity_id=55
 
---

## 10) Acceptance Criteria

- Admin can create a profile and manage choices for any column (court, lawyer, opponent, client, etc.).
- During preflight, matching profile is auto-selected; choices auto-apply.
- User can remember new decisions from preflight to the selected profile.
- Saved Choices page is searchable and editable.
- Bilingual normalization supported.
- Tests pass; docs updated (docs/imports/profiles_choices.md).

## 11) Commit Plan

1. feat(db): migrations for import_profiles/choices; models; indexes
2. feat(service): NameNormalizer; ResolverRegistry; ImportProfileService (select/apply/persist)
3. feat(preflight): apply profile before fuzzy; remember decisions checkbox
4. feat(admin): controllers/routes + Blade list for choices; profile CRUD; export/import JSON
5. test: selection/apply; UI filters; normalization; any-field coverage
6. docs: profiles & choices usage; resolver how-to; screenshots

---

Ask me after each commit whether to continue. For any new feature/bug/fix, ask me to create a new branch and propose the branch name.