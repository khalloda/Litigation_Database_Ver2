# Courts Many-to-Many Pivot Tables Refactor Plan

## Overview
Refactor courts module to support many-to-many relationships for circuits, secretaries, floors, and halls using proper pivot tables instead of single foreign keys.

---

## Phase 1: Rollback and Cleanup

### 1. Rollback Migrations
```bash
php artisan migrate:rollback --step=3
```
This will rollback:
- `2025_10_13_102617_modify_cases_table_for_court_relationship.php`
- `2025_10_13_102546_create_court_option_sets.php`
- `2025_10_13_102453_create_courts_table.php`

### 2. Delete Old Migration Files
- Delete: `2025_10_13_102453_create_courts_table.php`
- Delete: `2025_10_13_102546_create_court_option_sets.php`
- Delete: `2025_10_13_102617_modify_cases_table_for_court_relationship.php`

---

## Phase 2: Create New Database Structure

### 3. Create Courts Table (Revised)
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_courts_table.php`

**Structure**:
- `id` (INT, no auto-increment)
- `court_name_ar` (VARCHAR 255, nullable)
- `court_name_en` (VARCHAR 255, nullable)
- `is_active` (BOOLEAN, default true)
- `created_by`, `updated_by` (FK to users)
- `created_at`, `updated_at`, `deleted_at`
- Seed 52 courts from CSV

**NO FK columns** - relationships handled via pivot tables

### 4. Create 4 Option Sets
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_court_option_sets.php`

Same as before - create 4 empty option sets.

### 5. Create 4 Pivot Tables
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_court_pivot_tables.php`

**Tables**:
```sql
-- Court Circuits
court_circuit (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  court_id BIGINT FK to courts.id,
  option_value_id BIGINT FK to option_values.id,
  created_at TIMESTAMP,
  UNIQUE(court_id, option_value_id)
)

-- Court Secretaries
court_secretary (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  court_id BIGINT FK to courts.id,
  option_value_id BIGINT FK to option_values.id,
  created_at TIMESTAMP,
  UNIQUE(court_id, option_value_id)
)

-- Court Floors
court_floor (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  court_id BIGINT FK to courts.id,
  option_value_id BIGINT FK to option_values.id,
  created_at TIMESTAMP,
  UNIQUE(court_id, option_value_id)
)

-- Court Halls
court_hall (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  court_id BIGINT FK to courts.id,
  option_value_id BIGINT FK to option_values.id,
  created_at TIMESTAMP,
  UNIQUE(court_id, option_value_id)
)
```

### 6. Modify Cases Table
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_modify_cases_table_for_court_relationship.php`

**Changes**:
- Rename `matter_court` → `matter_court_text` (for import)
- Add `court_id` FK to courts table
- Convert 4 fields to FKs (same as before):
  - `matter_circuit` → FK to option_values
  - `circuit_secretary` → FK to option_values
  - `court_floor` → FK to option_values
  - `court_hall` → FK to option_values

**Note**: Cases still store ONE value per field, but they pick from the court's MANY options.

---

## Phase 3: Update Models

### 7. Update Court Model
**File**: `app/Models/Court.php`

**Replace single relationships with many-to-many**:
```php
// OLD (remove):
public function courtCircuit() { return $this->belongsTo(OptionValue::class, 'court_circuit'); }

// NEW (add):
public function circuits()
{
    return $this->belongsToMany(OptionValue::class, 'court_circuit', 'court_id', 'option_value_id')
                ->withTimestamps();
}

public function secretaries()
{
    return $this->belongsToMany(OptionValue::class, 'court_secretary', 'court_id', 'option_value_id')
                ->withTimestamps();
}

public function floors()
{
    return $this->belongsToMany(OptionValue::class, 'court_floor', 'court_id', 'option_value_id')
                ->withTimestamps();
}

public function halls()
{
    return $this->belongsToMany(OptionValue::class, 'court_hall', 'court_id', 'option_value_id')
                ->withTimestamps();
}
```

**Update fillable**: Remove the 4 FK fields (they're in pivot tables now)

### 8. CaseModel - No Changes Needed
CaseModel relationships remain the same (still stores single values).

---

## Phase 4: Update Controllers

### 9. Update CourtsController
**File**: `app/Http/Controllers/CourtsController.php`

#### `create()` and `edit()`:
Load option values as before (no change).

#### `store()` and `update()`:
```php
public function store(CourtRequest $request)
{
    $court = Court::create([
        'court_name_ar' => $request->court_name_ar,
        'court_name_en' => $request->court_name_en,
        'is_active' => $request->is_active ?? true,
        'created_by' => auth()->id(),
        'updated_by' => auth()->id(),
    ]);
    
    // Sync many-to-many relationships
    if ($request->filled('court_circuits')) {
        $court->circuits()->sync($request->court_circuits);
    }
    if ($request->filled('court_secretaries')) {
        $court->secretaries()->sync($request->court_secretaries);
    }
    if ($request->filled('court_floors')) {
        $court->floors()->sync($request->court_floors);
    }
    if ($request->filled('court_halls')) {
        $court->halls()->sync($request->court_halls);
    }
    
    return redirect()->route('courts.show', $court)
        ->with('success', __('app.court_created_success'));
}
```

#### `show()`:
```php
$court->load(['circuits', 'secretaries', 'floors', 'halls']);
```

### 10. Update CasesController AJAX Endpoint
**File**: `app/Http/Controllers/CasesController.php`

**Method**: `getCourtDetails(Court $court)`

**Change from**:
```php
return response()->json([
    'circuit' => $court->courtCircuit ? [...] : null,
    // Single value
]);
```

**To**:
```php
$court->load(['circuits', 'secretaries', 'floors', 'halls']);

return response()->json([
    'circuits' => $court->circuits->map(function($circuit) {
        return [
            'id' => $circuit->id,
            'label' => app()->getLocale() === 'ar' ? $circuit->label_ar : $circuit->label_en,
        ];
    }),
    'secretaries' => $court->secretaries->map(...),
    'floors' => $court->floors->map(...),
    'halls' => $court->halls->map(...),
]);
```

**Returns arrays instead of single values**.

---

## Phase 5: Update Views

### 11. Update Court Create/Edit Forms
**Files**: `resources/views/courts/create.blade.php`, `resources/views/courts/edit.blade.php`

**Change from single-select to multi-select**:
```blade
<!-- OLD: Single select -->
<select name="court_circuit" id="court_circuit">
  <option value="1">Circuit 1</option>
</select>

<!-- NEW: Multi-select -->
<select name="court_circuits[]" id="court_circuits" class="select2-multi" multiple>
  <option value="1">Circuit 1</option>
  <option value="2">Circuit 2</option>
</select>
```

**JavaScript**:
```javascript
$('.select2-multi').select2({
    theme: 'bootstrap-5',
    multiple: true,
    allowClear: true,
    placeholder: 'Select multiple...'
});
```

### 12. Update Court Show View
**File**: `resources/views/courts/show.blade.php`

**Display as tags/badges**:
```blade
<p><strong>Circuits:</strong>
  @foreach($court->circuits as $circuit)
    <span class="badge bg-primary">{{ app()->getLocale() === 'ar' ? $circuit->label_ar : $circuit->label_en }}</span>
  @endforeach
</p>
```

### 13. Update Cases Create/Edit Forms
**Files**: `resources/views/cases/create.blade.php`, `resources/views/cases/edit.blade.php`

**Update JavaScript AJAX handler**:
```javascript
success: function(data) {
    // Populate circuit dropdown with MULTIPLE options
    $('#matter_circuit').empty().prop('disabled', false);
    $('#matter_circuit').append(new Option('{{ __("app.select_option") }}', ''));
    
    data.circuits.forEach(function(circuit) {
        $('#matter_circuit').append(new Option(circuit.label, circuit.id));
    });
    
    // Same for secretaries, floors, halls
    // ...
}
```

**User picks ONE from the court's MANY options**.

### 14. Update Cases Show View
**File**: `resources/views/cases/show.blade.php`

No changes needed - still displays single values.

---

## Phase 6: Update Validation

### 15. Update CourtRequest
**File**: `app/Http/Requests/CourtRequest.php`

**Change from**:
```php
'court_circuit' => 'nullable|exists:option_values,id',
```

**To**:
```php
'court_circuits' => 'nullable|array',
'court_circuits.*' => 'exists:option_values,id',
'court_secretaries' => 'nullable|array',
'court_secretaries.*' => 'exists:option_values,id',
'court_floors' => 'nullable|array',
'court_floors.*' => 'exists:option_values,id',
'court_halls' => 'nullable|array',
'court_halls.*' => 'exists:option_values,id',
```

### 16. CaseRequest - No Changes
Cases still validate single values.

---

## Phase 7: Update Translations

### 17. Add New Translation Keys

**English**:
```php
'select_multiple' => 'Select multiple...',
'court_circuits' => 'Court Circuits',
'court_secretaries' => 'Circuit Secretaries',
'court_floors' => 'Court Floors',
'court_halls' => 'Court Halls',
```

**Arabic**:
```php
'select_multiple' => 'اختر متعدد...',
'court_circuits' => 'دوائر المحكمة',
'court_secretaries' => 'أمناء الدوائر',
'court_floors' => 'طوابق المحكمة',
'court_halls' => 'قاعات المحكمة',
```

---

## Phase 8: Testing

### 18. Migration and Testing
```bash
php artisan migrate
php artisan optimize:clear
```

**Test Checklist**:
- [ ] Courts CRUD works
- [ ] Can assign multiple circuits to a court
- [ ] Can assign multiple secretaries to a court
- [ ] Can assign multiple floors to a court
- [ ] Can assign multiple halls to a court
- [ ] Court show displays all assigned values as badges
- [ ] Cases create: selecting court shows only that court's options
- [ ] Cases create: can pick one circuit from court's list
- [ ] Cases edit: same as create
- [ ] Cases show: displays selected values correctly
- [ ] Import works with court mapping

---

## Implementation Order

1. ✅ Rollback migrations (3 steps)
2. ✅ Delete old migration files (3 files)
3. ✅ Create courts table migration (with seed)
4. ✅ Create option sets migration
5. ✅ Create pivot tables migration (4 tables)
6. ✅ Create cases table modification migration
7. ✅ Update Court model (many-to-many relationships)
8. ✅ Update CourtsController (sync logic)
9. ✅ Update CasesController AJAX (return arrays)
10. ✅ Update court create/edit views (multi-select)
11. ✅ Update court show view (badges)
12. ✅ Update cases create/edit JavaScript (handle arrays)
13. ✅ Update CourtRequest validation (arrays)
14. ✅ Add translation keys
15. ✅ Run migrations
16. ✅ Test end-to-end

---

## Key Differences from Current Implementation

### Current (Single FK):
```
Court → has ONE circuit (FK in courts table)
Case form → shows that ONE circuit
```

### New (Many-to-Many):
```
Court → has MANY circuits (pivot table)
Case form → shows ALL court's circuits, user picks ONE
```

### Example:
**Cairo Court of Appeals** has:
- Circuits: 1, 5, 12
- Secretaries: A, B
- Floors: 2, 3
- Halls: 201, 202, 203

When user creates case for "Cairo Court of Appeals":
- Circuit dropdown shows: [Circuit 1, Circuit 5, Circuit 12] ← user picks one
- Secretary dropdown shows: [Secretary A, Secretary B] ← user picks one
- Floor dropdown shows: [Floor 2, Floor 3] ← user picks one
- Hall dropdown shows: [Hall 201, Hall 202, Hall 203] ← user picks one

---

## Estimated Time: 2 hours

---

## Notes
- Cases table structure remains the same (stores single values)
- Only courts table changes to many-to-many
- Cascading dropdown logic updates to handle arrays
- Admin can assign multiple options to each court
- Users pick one from the court's available options

