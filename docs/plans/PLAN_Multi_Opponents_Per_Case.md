# Multi-Opponents Per Case Implementation Plan

## Overview

Enable cases to have multiple opponents, each with their own capacity. The `case_opponents` pivot table will be the single source of truth, with `cases.opponent_id` maintained as a read-only mirror of the primary opponent for backward compatibility.

## ⚠️ CRITICAL ADJUSTMENTS REQUIRED BEFORE IMPLEMENTATION

### 1. Unique Constraint vs Soft Deletes
**ISSUE**: The plan proposes a unique constraint on `(case_id, opponent_id)`. With soft deletes on the pivot, MySQL will still block re-attaching the same opponent after a soft delete because the old (deleted) row still exists.

**FIX**: 
- Drop the DB-level unique constraint
- Keep normal indexes and enforce uniqueness in the service layer with `whereNull('deleted_at')`
- MySQL lacks partial unique indexes, so this must be handled in application code

### 2. Capacity Semantics (Business Rule)
**ISSUE**: The same opponent can appear with different capacities in the same case.

**FIX**:
- Allow multiple rows and enforce uniqueness on `(case_id, opponent_id, capacity_id)` in code
- Update service validation accordingly
- Reflect the rule in README/ADR

### 3. Primary Opponent Integrity
**ENHANCEMENT**: 
- Add a generated column `is_primary_tiny` (0/1) plus a before-save guard in code
- MySQL can't express "max one = 1" per case natively, so keep it in the service
- Add a transaction around "unset old primary → set new → sync mirror"
- Cover with comprehensive tests

### 4. Mirror Fields
**CLARIFICATION**: 
- The backfill sync mentions `opponent_capacity_id` on cases
- If that legacy column doesn't exist, don't add it now — only mirror `opponent_id`
- Keep mirrors minimal to avoid future deprecation work

### 5. Indexes
**ENHANCEMENT**:
- Add: `(case_id, is_primary)` (already listed)
- Keep: `(case_id, display_order)` + `(opponent_id)`
- If capacity is frequently filtered, add `(case_id, capacity_id)`
- Avoid wide multi-column indexes unless proven by queries

### 6. Concurrency / Reordering
**ENHANCEMENT**:
- Wrap reorder and setPrimary in transactions
- Consider optimistic locking (e.g., comparing `updated_at`) or at least re-read before write
- Avoid race conditions in multi-user edits
- Log reorder operations in audit

### 7. Import Preflight Edge Cases
**ENHANCEMENT**:
- When multiple imported opponent entries normalize to the same existing opponent, decide to merge (skip dup) or warn + require confirmation
- Add this to the preflight decision matrix & CSV of decisions

### 8. UI Polish
**ENHANCEMENT**:
- Add "Set as primary" action to each row (surface it visually)
- Show capacity badge bilingual
- Ensure `dir="auto"` for names
- Confirm permissions (`cases.opponents.edit`) guard every action

### 9. Docs & ADR
**REQUIREMENT**:
- Explicitly document deprecation timeline for `cases.opponent_id` (e.g., read-only now, remove in v3.0)
- Document the exact business rule for capacity multiplicity
- Include data type hints and max lengths in the import README (e.g., VARCHAR(191), UTF-8)

### 10. Tests to Add
**REQUIREMENT**:
- Re-attach after soft-delete (should succeed)
- Duplicate attach same opponent (blocked by service)
- Duplicate attach same opponent with different capacity (allowed or blocked per the chosen rule)
- Primary switch updates mirror and unsets old primary atomically
- Import Extended with 10 opponents + companion import beyond 10

**BOTTOM LINE**: ✅ Green-light with the tweaks above. The plan is solid and production-ready once you address uniqueness vs soft deletes, clarify capacity multiplicity, and lock down transactional integrity + tests. After that, you're safe to proceed to implementation.

## Phase 1: Database Schema & Migration

### 1.1 Create case_opponents Pivot Table

**File**: `clm-app/database/migrations/YYYY_MM_DD_HHMMSS_create_case_opponents_table.php`

Create pivot table with:

- `id` (PK), `case_id` (FK → cases), `opponent_id` (FK → opponents)
- `capacity_id` (FK → option_values), `is_primary` (boolean), `display_order` (int)
- `alias_text` (varchar 191), audit fields, soft deletes
- Indexes: `(case_id, display_order)`, `(opponent_id)`, `(case_id, is_primary)`, `(case_id, capacity_id)`
- **NO unique constraint** - enforce uniqueness in service layer with `whereNull('deleted_at')`
- **Business Rule**: Same opponent can appear with different capacities in same case
- **Uniqueness**: Enforce `(case_id, opponent_id, capacity_id)` in service layer

### 1.2 Backfill Migration

**File**: `clm-app/database/migrations/YYYY_MM_DD_HHMMSS_backfill_case_opponents_from_legacy.php`

Migrate existing single opponent data:

```php
$cases = DB::table('cases')->whereNotNull('opponent_id')->get();
foreach ($cases as $case) {
    DB::table('case_opponents')->insert([
        'case_id' => $case->id,
        'opponent_id' => $case->opponent_id,
        'capacity_id' => $case->opponent_capacity_id,
        'is_primary' => 1,
        'display_order' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}
```

### 1.3 Configuration

**File**: `clm-app/config/importer.php`

Add configuration:

```php
'opponents' => [
    'max_per_case' => env('MAX_OPPONENTS_PER_CASE', 10),
    'enforce_unique_primary' => true,
],
```

## Phase 2: Models & Relationships

### 2.1 Create CaseOpponent Pivot Model

**File**: `clm-app/app/Models/Pivots/CaseOpponent.php`

```php
class CaseOpponent extends Pivot
{
    use SoftDeletes, LogsActivity;
    
    protected $table = 'case_opponents';
    protected $fillable = ['case_id', 'opponent_id', 'capacity_id', 'is_primary', 'display_order', 'alias_text'];
    
    public function case() { return $this->belongsTo(CaseModel::class, 'case_id'); }
    public function opponent() { return $this->belongsTo(Opponent::class); }
    public function capacity() { return $this->belongsTo(OptionValue::class, 'capacity_id'); }
}
```

### 2.2 Update CaseModel

**File**: `clm-app/app/Models/CaseModel.php`

Add relationships:

```php
public function opponents()
{
    return $this->belongsToMany(Opponent::class, 'case_opponents')
        ->withPivot(['capacity_id', 'is_primary', 'display_order', 'alias_text', 'id', 'deleted_at'])
        ->using(\App\Models\Pivots\CaseOpponent::class)
        ->withTimestamps()
        ->orderBy('display_order');
}

public function primaryOpponent()
{
    return $this->belongsToMany(Opponent::class, 'case_opponents')
        ->wherePivot('is_primary', 1)
        ->withPivot(['capacity_id', 'alias_text'])
        ->using(\App\Models\Pivots\CaseOpponent::class);
}
```

Mark `opponent_id` as read-only in fillable (remove from array or add guard).

### 2.3 Update Opponent Model

**File**: `clm-app/app/Models/Opponent.php`

Add inverse relationship:

```php
public function cases()
{
    return $this->belongsToMany(CaseModel::class, 'case_opponents')
        ->withPivot(['capacity_id', 'is_primary', 'display_order', 'alias_text', 'id'])
        ->withTimestamps();
}
```

## Phase 3: Service Layer

### 3.1 Create CaseOpponentService

**File**: `clm-app/app/Services/CaseOpponentService.php`

Key methods:

```php
public function attachOpponent(CaseModel $case, int $opponentId, ?int $capacityId = null, bool $isPrimary = false, ?int $order = null, ?string $alias = null): CaseOpponent

public function detachOpponent(CaseModel $case, int $opponentId): bool

public function setPrimary(CaseModel $case, int $opponentId): void
// Unsets current primary, sets new primary, updates cases.opponent_id mirror

public function reorder(CaseModel $case, array $opponentIdsInOrder): void
// Updates display_order for all opponents

public function validateMaxOpponents(CaseModel $case): void
// Throws exception if max limit exceeded

private function syncLegacyOpponentField(CaseModel $case): void
// Updates cases.opponent_id to match primary (minimal mirror)
```

**CRITICAL ADJUSTMENTS**:

- **Uniqueness Validation**: Check for existing `(case_id, opponent_id, capacity_id)` with `whereNull('deleted_at')`
- **Business Rule**: Allow same opponent with different capacities, block same opponent with same capacity
- **Transaction Safety**: Wrap `setPrimary` and `reorder` in transactions
- **Concurrency**: Use optimistic locking with `updated_at` comparison
- **Mirror Sync**: Only sync `opponent_id`, not `opponent_capacity_id` (minimal mirror)

Ensure all methods:

- Log activity via audit trail
- Validate max opponents constraint
- Enforce single is_primary constraint
- Update legacy mirror field atomically
- **Handle soft deletes properly** (check `whereNull('deleted_at')`)
- **Enforce capacity multiplicity rule** in validation

## Phase 4: Import/Export Updates

### 4.1 Update GenerateCasesTemplate Command

**File**: `clm-app/app/Console/Commands/GenerateCasesTemplate.php`

**Standard Template**: Keep single opponent columns (becomes primary on import)

- `opponent_name`, `opponent_id`, `opponent_capacity`, `opponent_capacity_id`

**Extended Template**: Add multiple opponent columns

- `opponent1_name`, `opponent1_id`, `opponent1_capacity`, `opponent1_capacity_id`
- `opponent2_name`, `opponent2_id`, `opponent2_capacity`, `opponent2_capacity_id`
- `opponent3_name`, `opponent3_id`, `opponent3_capacity`, `opponent3_capacity_id`
- (up to 5 opponents in extended)

**README sheet**: Document that opponent1 becomes primary; additional opponents can be added via companion import.

### 4.2 Create GenerateCaseOpponentsTemplate Command

**File**: `clm-app/app/Console/Commands/GenerateCaseOpponentsTemplate.php`

Generate companion import template:

- Columns: `case_id`, `case_number`, `opponent_name`, `opponent_id`, `capacity`, `capacity_id`, `is_primary`, `display_order`, `alias_text`
- Sample data showing multiple opponents for same case
- Validation rules in README sheet

### 4.3 Update PreflightEngine

**File**: `clm-app/app/Services/PreflightEngine.php`

Add method for cases table:

```php
private function extractMultipleOpponents(array $data): array
{
    $opponents = [];
    
    // Standard template: single opponent
    if (!empty($data['opponent_name']) || !empty($data['opponent_id'])) {
        $opponents[] = [
            'name' => $data['opponent_name'] ?? null,
            'id' => $data['opponent_id'] ?? null,
            'capacity' => $data['opponent_capacity'] ?? null,
            'capacity_id' => $data['opponent_capacity_id'] ?? null,
            'is_primary' => true,
            'order' => 1
        ];
    }
    
    // Extended template: opponent1-5
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($data["opponent{$i}_name"]) || !empty($data["opponent{$i}_id"])) {
            $opponents[] = [
                'name' => $data["opponent{$i}_name"] ?? null,
                'id' => $data["opponent{$i}_id"] ?? null,
                'capacity' => $data["opponent{$i}_capacity"] ?? null,
                'capacity_id' => $data["opponent{$i}_capacity_id"] ?? null,
                'is_primary' => ($i === 1),
                'order' => $i
            ];
        }
    }
    
    return $opponents;
}
```

Validate:

- Max opponents per case (configurable limit)
- Only one is_primary per case
- Opponent name/id resolution via fuzzy matching
- Capacity values exist in option_values

### 4.4 Update ImportService

**File**: `clm-app/app/Services/ImportService.php`

After case insert/update, process opponents:

```php
if ($tableName === 'cases' && !empty($processedData['_opponents'])) {
    $caseOpponentService = app(CaseOpponentService::class);
    foreach ($processedData['_opponents'] as $opponentData) {
        $opponentId = $this->resolveOpponent($opponentData);
        $caseOpponentService->attachOpponent(
            $case,
            $opponentId,
            $opponentData['capacity_id'],
            $opponentData['is_primary'],
            $opponentData['order'],
            $opponentData['alias'] ?? null
        );
    }
}
```

### 4.5 Create CaseOpponentsImportService

**File**: `clm-app/app/Services/CaseOpponentsImportService.php`

Dedicated service for companion import:

- Resolve case by `case_id` or `case_number`
- Resolve opponent by `opponent_id` or fuzzy match `opponent_name`
- Resolve capacity by `capacity_id` or lookup `capacity` text
- Attach via CaseOpponentService
- Support both append and replace modes

### 4.6 Add Import Routes

**File**: `clm-app/routes/web.php`

Add companion import route:

```php
Route::get('/import/case-opponents', [ImportController::class, 'uploadCaseOpponents'])
    ->name('import.case-opponents.upload');
```

## Phase 5: UI Components

### 5.1 Create Opponents Management Partial

**File**: `clm-app/resources/views/cases/partials/_opponents.blade.php`

Display opponents table with:

- Opponent name (bilingual), capacity badge, is_primary star icon
- Up/down arrow buttons for reordering
- Remove button (with confirmation)
- Add opponent button (opens modal)

Table structure:

```blade
<table class="table">
    <thead>
        <tr>
            <th>Order</th>
            <th>Opponent</th>
            <th>Capacity</th>
            <th>Primary</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($case->opponents as $opponent)
        <tr data-opponent-id="{{ $opponent->id }}">
            <td>{{ $opponent->pivot->display_order }}</td>
            <td>{{ $opponent->display_name }}</td>
            <td><span class="badge">{{ $opponent->pivot->capacity->label_ar ?? '' }}</span></td>
            <td>@if($opponent->pivot->is_primary) ⭐ @endif</td>
            <td>
                <button class="btn btn-sm" onclick="moveUp({{ $opponent->pivot->id }})">↑</button>
                <button class="btn btn-sm" onclick="moveDown({{ $opponent->pivot->id }})">↓</button>
                <button class="btn btn-sm btn-danger" onclick="removeOpponent({{ $opponent->pivot->id }})">×</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### 5.2 Create Add Opponent Modal

**File**: `clm-app/resources/views/cases/modals/_add_opponent.blade.php`

Modal with:

- Opponent search/select (with fuzzy matching)
- Capacity dropdown (from option_values where set='capacity')
- Set as primary checkbox
- Alias text input (optional)

### 5.3 Update Case Show View

**File**: `clm-app/resources/views/cases/show.blade.php`

Add opponents section after existing details:

```blade
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('app.opponents') }}</h5>
        @can('cases.edit')
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addOpponentModal">
            {{ __('app.add_opponent') }}
        </button>
        @endcan
    </div>
    <div class="card-body">
        @include('cases.partials._opponents')
    </div>
</div>
```

### 5.4 Create AJAX Controller Methods

**File**: `clm-app/app/Http/Controllers/CaseOpponentController.php`

New controller with methods:

```php
public function store(Request $request, CaseModel $case): JsonResponse
// Attach opponent via CaseOpponentService

public function destroy(CaseModel $case, int $pivotId): JsonResponse
// Detach opponent

public function setPrimary(CaseModel $case, int $opponentId): JsonResponse
// Set as primary

public function reorder(Request $request, CaseModel $case): JsonResponse
// Update display_order for all opponents
```

All methods:

- Validate permissions (`cases.opponents.edit`)
- Use CaseOpponentService
- Return JSON with success/error messages
- Log activity

### 5.5 Add JavaScript for Opponents Management

**File**: `clm-app/resources/js/case-opponents.js`

Functions:

```javascript
function addOpponent(caseId, opponentId, capacityId, isPrimary, alias) { /* AJAX POST */ }
function removeOpponent(caseId, pivotId) { /* AJAX DELETE */ }
function setPrimary(caseId, opponentId) { /* AJAX POST */ }
function moveUp(pivotId) { /* Get current order, swap with previous */ }
function moveDown(pivotId) { /* Get current order, swap with next */ }
function reorderOpponents(caseId, orderedIds) { /* AJAX POST with array */ }
```

## Phase 6: Trash & Audit Integration

### 6.1 Update CaseCollector

**File**: `clm-app/app/Support/DeletionBundles/Collectors/CaseCollector.php`

Add to `collect()` method:

```php
// Case opponents (pivot rows)
$snapshot['case_opponents'] = DB::table('case_opponents')
    ->where('case_id', $model->id)
    ->get()
    ->map(fn($co) => ['attributes' => (array) $co])
    ->toArray();
```

### 6.2 Update Restoration Logic

**File**: `clm-app/app/Services/DeletionBundleService.php`

In restore method, after restoring case, restore pivot rows:

```php
if (!empty($snapshot['case_opponents'])) {
    foreach ($snapshot['case_opponents'] as $pivotData) {
        DB::table('case_opponents')->insert($pivotData['attributes']);
    }
    // Sync legacy opponent_id field
    $service->syncLegacyOpponentField($restoredCase);
}
```

### 6.3 Activity Logging

Ensure CaseOpponent pivot model logs all changes:

- Attach/detach events
- Capacity changes
- Primary flag changes
- Reordering

## Phase 7: Permissions & Policies

### 7.1 Add Permissions

**File**: `clm-app/database/seeders/PermissionsSeeder.php`

Add:

```php
'cases.opponents.view',
'cases.opponents.edit',
'cases.opponents.attach',
'cases.opponents.detach',
```

### 7.2 Update CasePolicy

**File**: `clm-app/app/Policies/CasePolicy.php`

Add methods:

```php
public function manageOpponents(User $user, CaseModel $case): bool
{
    return $user->can('cases.opponents.edit');
}
```

## Phase 8: Translations

### 8.1 Add Translation Keys

**Files**: `clm-app/resources/lang/en/app.php`, `clm-app/resources/lang/ar/app.php`

Add keys:

```php
'opponents' => 'Opponents' / 'الخصوم',
'add_opponent' => 'Add Opponent' / 'إضافة خصم',
'remove_opponent' => 'Remove Opponent' / 'إزالة خصم',
'set_as_primary' => 'Set as Primary' / 'تعيين كأساسي',
'primary_opponent' => 'Primary Opponent' / 'الخصم الأساسي',
'opponent_capacity' => 'Capacity' / 'الصفة',
'opponent_alias' => 'Alias' / 'الاسم البديل',
'max_opponents_exceeded' => 'Maximum {max} opponents allowed per case' / 'الحد الأقصى {max} خصوم لكل قضية',
'case_opponents_import' => 'Case Opponents Import' / 'استيراد خصوم القضايا',
```

## Phase 9: Testing

### 9.1 Unit Tests

**File**: `clm-app/tests/Unit/CaseOpponentServiceTest.php`

Test:

- attachOpponent: creates pivot row, respects max limit
- detachOpponent: removes pivot row
- setPrimary: enforces single primary, syncs legacy field
- reorder: updates display_order correctly
- validateMaxOpponents: throws when exceeded

**CRITICAL TEST CASES**:

- **Re-attach after soft-delete**: Should succeed (no unique constraint blocking)
- **Duplicate attach same opponent**: Blocked by service validation
- **Duplicate attach same opponent with different capacity**: Allowed per business rule
- **Primary switch**: Updates mirror and unsets old primary atomically
- **Concurrency**: Race conditions in multi-user edits
- **Soft delete handling**: `whereNull('deleted_at')` in uniqueness checks

### 9.2 Feature Tests

**File**: `clm-app/tests/Feature/CaseOpponentsTest.php`

Test:

- Import with single opponent (Standard template)
- Import with multiple opponents (Extended template)
- Companion import (case_opponents table)
- Deletion bundle captures pivot rows
- Restoration recreates pivot rows
- UI actions (add/remove/reorder via AJAX)
- Permissions enforcement

**CRITICAL FEATURE TEST CASES**:

- **Import Extended with 10 opponents**: Test max limit enforcement
- **Companion import beyond 10**: Test max limit validation
- **Preflight edge cases**: Multiple imported entries normalize to same opponent
- **Transaction integrity**: Primary switch in concurrent scenarios
- **Soft delete scenarios**: Re-attach after soft delete
- **Capacity multiplicity**: Same opponent with different capacities

### 9.3 Integration Tests

**File**: `clm-app/tests/Feature/CaseOpponentBackfillTest.php`

Test:

- Backfill migration creates correct pivot rows
- Legacy opponent_id remains in sync
- No data loss during migration

## Phase 10: Documentation

### 10.1 Create ADR

**File**: `docs/adr/ADR-YYYYMMDD-multi-opponents-per-case.md`

Document:

- Context: need for multiple opponents with individual capacities
- Decision: pivot table as source of truth, legacy field as read-only mirror
- Consequences: backward compatibility, dual-write complexity
- Alternatives considered: fully remove legacy field, JSON column

**CRITICAL ADR REQUIREMENTS**:

- **Deprecation Timeline**: Document `cases.opponent_id` deprecation (read-only now, remove in v3.0)
- **Business Rule**: Document exact capacity multiplicity rule (same opponent, different capacities allowed)
- **Data Types**: Include VARCHAR(191), UTF-8 encoding requirements
- **Uniqueness Strategy**: Document service-layer uniqueness vs DB constraints
- **Concurrency**: Document transaction safety and optimistic locking approach

### 10.2 Update Data Dictionary

**File**: `docs/data-dictionary.md`

Add `case_opponents` table documentation with all columns and relationships.

### 10.3 Update Import Documentation

**File**: `docs/imports/cases_template.md`

Add section on:

- Multiple opponents in Extended template
- Companion import for unlimited opponents
- Primary opponent behavior
- Max opponents limit

**CRITICAL DOCUMENTATION REQUIREMENTS**:

- **Data Type Hints**: VARCHAR(191), UTF-8 encoding, max lengths
- **Business Rules**: Capacity multiplicity (same opponent, different capacities)
- **Preflight Edge Cases**: Multiple entries normalize to same opponent
- **Deprecation Timeline**: Legacy field deprecation schedule
- **Concurrency Notes**: Multi-user edit considerations

### 10.4 Create Runbook

**File**: `docs/runbooks/case-opponents-management.md`

Operational guide covering:

- How to change primary opponent
- How to bulk import opponents
- Troubleshooting sync issues
- Rollback procedure

### 10.5 Update ERD

**File**: `docs/erd.md`

Add `case_opponents` entity and relationship lines.

## Phase 11: Rollback & Deprecation Plan

### 11.1 Deprecation Notice

Add warning in code comments:

```php
// @deprecated cases.opponent_id will be removed in v3.0
// Use $case->primaryOpponent() instead
```

### 11.2 Rollback Migration

**File**: `clm-app/database/migrations/YYYY_MM_DD_HHMMSS_rollback_multi_opponents.php`

If needed, provide migration to:

- Copy primary opponent from pivot back to cases.opponent_id
- Drop case_opponents table

Timeline: deprecate in v2.x, remove in v3.0 (after 2 stable releases)

## Success Criteria

- A case can have 0-10 opponents (configurable)
- Each opponent has individual capacity and optional alias
- Only one opponent can be primary at a time
- Primary opponent syncs to cases.opponent_id automatically
- Standard template imports single opponent as primary
- Extended template imports up to 5 opponents
- Companion import supports unlimited opponents
- UI allows add/remove/reorder with simple arrows
- Deletion bundles capture and restore pivot rows
- All changes are audited
- Existing single-opponent cases work unchanged
- Tests cover all scenarios
- Documentation is complete

