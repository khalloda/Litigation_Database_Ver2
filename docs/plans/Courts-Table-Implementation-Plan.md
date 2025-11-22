# Courts Table Implementation Plan

## Overview
Create a dedicated `courts` table as a standalone model with its own fields and relationships, replacing the previous option-based approach. The courts table will have specific court details and will propagate data to the cases table through foreign key relationships.

---

## Database Structure

### 1. Courts Table Migration
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_courts_table.php`

**Columns**:
- `id` (INT, Primary Key) - Manual ID for import compatibility
- `court_name_ar` (VARCHAR 255, nullable) - Court name in Arabic
- `court_name_en` (VARCHAR 255, nullable) - Court name in English  
- `court_circuit` (Foreign Key to option_values, nullable) - Court circuit (dropdown)
- `court_circuit_secretary` (Foreign Key to option_values, nullable) - Circuit secretary (dropdown)
- `court_floor` (Foreign Key to option_values, nullable) - Floor number (dropdown)
- `court_hall` (Foreign Key to option_values, nullable) - Hall number (dropdown)
- `is_active` (BOOLEAN, default true)
- `created_by` (UNSIGNED BIGINT, nullable, FK to users)
- `updated_by` (UNSIGNED BIGINT, nullable, FK to users)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `deleted_at` (TIMESTAMP, nullable) - Soft deletes

**Indexes**:
- Index on `court_name_ar`
- Index on `court_name_en`
- Index on `is_active`

**Constraints**:
- At least one court name (AR or EN) must be provided
- Disable auto-increment on `id` for import compatibility

**Initial Seed**: 52 courts from the CSV file (court names only, dropdown fields will be populated later)

---

### 2. Cases Table Modification Migration
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_court_fk_to_cases_table.php`

**Changes to `cases` table**:
1. Rename `matter_court` → `matter_court_text` (preserve for import mapping)
2. Add `court_id` (Foreign Key to courts.id, nullable, nullOnDelete) after `matter_category`
3. Convert existing text fields to foreign keys:
   - `matter_circuit` → Foreign Key to option_values (court circuit options)
   - `circuit_secretary` → Foreign Key to option_values (secretary options)
   - `court_floor` → Foreign Key to option_values (floor options)
   - `court_hall` → Foreign Key to option_values (hall options)

**Note**: These dropdowns in the case form will be **filtered/cascaded** to show only the values from the selected court's data.

---

### 3. Option Sets for Court Dropdowns
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_court_option_sets.php`

Create 4 option sets (user will provide seed data later):
1. **`court.circuit`** - Court circuits
2. **`court.circuit_secretary`** - Circuit secretaries  
3. **`court.floor`** - Floor numbers
4. **`court.hall`** - Hall numbers

---

## Model Implementation

### 4. Court Model
**File**: `app/Models/Court.php`

**Features**:
- Soft deletes
- Activity logging
- Audit fields (created_by, updated_by)
- Fillable: court_name_ar, court_name_en, court_circuit, court_circuit_secretary, court_floor, court_hall
- Casts: is_active → boolean
- **Relationships**:
  - `courtCircuit()` → belongsTo(OptionValue)
  - `courtCircuitSecretary()` → belongsTo(OptionValue)
  - `courtFloor()` → belongsTo(OptionValue)
  - `courtHall()` → belongsTo(OptionValue)
  - `cases()` → hasMany(CaseModel)
  - `createdBy()` → belongsTo(User)
  - `updatedBy()` → belongsTo(User)
- **Accessor**: `getCourtNameAttribute()` returns locale-aware name

---

### 5. Update CaseModel  
**File**: `app/Models/CaseModel.php`

**Changes**:
- Add `court_id` and `matter_court_text` to `$fillable`
- Update existing fields to be foreign keys: `matter_circuit`, `circuit_secretary`, `court_floor`, `court_hall`
- Add `court()` relationship → `belongsTo(Court::class)`
- Add relationships for the new FKs:
  - `matterCircuit()` → `belongsTo(OptionValue::class, 'matter_circuit')`
  - `circuitSecretaryRef()` → `belongsTo(OptionValue::class, 'circuit_secretary')`
  - `courtFloorRef()` → `belongsTo(OptionValue::class, 'court_floor')`
  - `courtHallRef()` → `belongsTo(OptionValue::class, 'court_hall')`

---

## Controllers

### 6. CourtsController (CRUD)
**File**: `app/Http/Controllers/CourtsController.php`

**Methods**:
- `index()` - List all courts with pagination, search, filters
- `create()` - Show create form with dropdown options
- `store()` - Validate and create court
- `show()` - Display court details with:
  - Related cases (paginated)
  - Hearings placeholder
  - Tasks placeholder
- `edit()` - Show edit form
- `update()` - Validate and update court
- `destroy()` - Soft delete (check for related cases first)

---

### 7. Update CasesController
**File**: `app/Http/Controllers/CasesController.php`

**Changes**:
- `create()` - Load courts for dropdown: `Court::where('is_active', true)->orderBy('court_name_ar')->get()`
- `edit()` - Same as create
- `show()` - Eager load `court` relationship and related option values
- **AJAX Endpoint** - Add new method `getCourtDetails(Court $court)` to return court's circuit/secretary/floor/hall options as JSON for cascading dropdowns

---

## Views

### 8. Courts CRUD Views
**Files**: 
- `resources/views/courts/index.blade.php` - List with search/filter
- `resources/views/courts/create.blade.php` - Create form with Select2 dropdowns
- `resources/views/courts/edit.blade.php` - Edit form
- `resources/views/courts/show.blade.php` - Detail view with cases, hearings (placeholder), tasks (placeholder)

**Form Fields**:
- Court Name (Arabic) - text input
- Court Name (English) - text input
- Court Circuit - Select2 dropdown (from option_values)
- Circuit Secretary - Select2 dropdown (from option_values)
- Court Floor - Select2 dropdown (from option_values)
- Court Hall - Select2 dropdown (from option_values)
- Active Status - checkbox

---

### 9. Update Cases Forms
**Files**: `resources/views/cases/create.blade.php`, `resources/views/cases/edit.blade.php`

**Changes**:
- Replace `matter_court` text input with Select2 dropdown for `court_id`
- Display court name (locale-aware) in dropdown options
- Convert `matter_circuit`, `circuit_secretary`, `court_floor`, `court_hall` to **cascading Select2 dropdowns**:
  - Initially disabled until a court is selected
  - When court is selected, make AJAX call to fetch that court's options
  - Populate each dropdown with the court's specific values
  - Use JavaScript to handle the cascading behavior
  
**JavaScript Logic**:
```javascript
$('#court_id').on('change', function() {
    const courtId = $(this).val();
    if (courtId) {
        // Fetch court details via AJAX
        $.get(`/api/courts/${courtId}/details`, function(data) {
            // Populate circuit dropdown
            populateDropdown('#matter_circuit', data.circuit);
            // Populate secretary dropdown
            populateDropdown('#circuit_secretary', data.secretary);
            // Populate floor dropdown
            populateDropdown('#court_floor', data.floor);
            // Populate hall dropdown
            populateDropdown('#court_hall', data.hall);
            
            // Enable all dropdowns
            $('#matter_circuit, #circuit_secretary, #court_floor, #court_hall').prop('disabled', false);
        });
    } else {
        // Clear and disable all cascading dropdowns
        $('#matter_circuit, #circuit_secretary, #court_floor, #court_hall').val('').prop('disabled', true);
    }
});
```

---

### 10. Cases Show View Link
**File**: `resources/views/cases/show.blade.php`

- Display court as clickable link to `routes.courts.show`
- Display circuit/secretary/floor/hall with their locale-aware labels from option_values

---

## Routes

### 11. Court Routes
**File**: `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('courts', CourtsController::class);
    
    // AJAX endpoint for cascading dropdowns
    Route::get('/api/courts/{court}/details', [CasesController::class, 'getCourtDetails'])->name('courts.details');
});
```

---

## Form Requests

### 12. CourtRequest Validation
**File**: `app/Http/Requests/CourtRequest.php`

**Rules**:
- At least one of `court_name_ar` or `court_name_en` required
- `court_circuit` - nullable|exists:option_values,id
- `court_circuit_secretary` - nullable|exists:option_values,id
- `court_floor` - nullable|exists:option_values,id
- `court_hall` - nullable|exists:option_values,id
- `is_active` - boolean

---

### 13. Update CaseRequest
**File**: `app/Http/Requests/CaseRequest.php`

**Changes**:
- Add `court_id` validation: `nullable|exists:courts,id`
- Update existing field validations to foreign keys:
  - `matter_circuit` → `nullable|exists:option_values,id`
  - `circuit_secretary` → `nullable|exists:option_values,id`
  - `court_floor` → `nullable|exists:option_values,id`
  - `court_hall` → `nullable|exists:option_values,id`

---

## Translations

### 14. Translation Keys
**Files**: `resources/lang/en/app.php`, `resources/lang/ar/app.php`

**New Keys**:
- `courts` => 'Courts' / 'المحاكم'
- `court` => 'Court' / 'المحكمة'
- `court_name_ar` => 'Court Name (Arabic)' / 'اسم المحكمة (عربي)'
- `court_name_en` => 'Court Name (English)' / 'اسم المحكمة (إنجليزي)'
- `court_circuit` => 'Court Circuit' / 'دائرة المحكمة'
- `court_circuit_secretary` => 'Circuit Secretary' / 'أمين الدائرة'
- `court_floor` => 'Court Floor' / 'طابق المحكمة'
- `court_hall` => 'Court Hall' / 'قاعة المحكمة'
- `select_court` => 'Select Court' / 'اختر المحكمة'
- `court_details` => 'Court Details' / 'تفاصيل المحكمة'
- `create_court` => 'Create Court' / 'إنشاء محكمة'
- `edit_court` => 'Edit Court' / 'تعديل المحكمة'
- `auto_filled_from_court` => '(Auto-filled from court)' / '(يملأ تلقائيا من المحكمة)'

---

## Import Compatibility

### 15. Import Mapping Strategy
- `matter_court_text` column stores the original text value from Excel
- Import process will:
  1. Try to match `matter_court_text` against `court_name_ar` or `court_name_en`
  2. If match found, set `court_id`
  3. For circuit/secretary/floor/hall fields:
     - If Excel has text values, resolve them to option_value IDs
     - If Excel has numeric IDs, map directly to the FK columns
- The MappingEngine already supports ID preservation for courts table

---

## Navigation

### 16. Add Courts to Menu
**File**: `resources/views/layouts/app.blade.php`

Add "Courts" link in appropriate location (likely in Admin dropdown or main navigation)

---

## Policies

### 17. Court Policy
**File**: `app/Policies/CourtPolicy.php`

**Methods**:
- `viewAny()` - Can list courts
- `view()` - Can view single court
- `create()` - Can create court (admin only)
- `update()` - Can edit court (admin only)
- `delete()` - Can delete court (admin only, check for related cases)

---

## Testing Checklist

- [ ] Create courts table migration with correct structure
- [ ] Seed 52 initial courts from CSV
- [ ] Create 4 option sets for court dropdowns (empty for now)
- [ ] Modify cases table to add court_id FK
- [ ] Create Court model with all relationships
- [ ] Update CaseModel with court relationship and option value relationships
- [ ] Create CourtRequest validation
- [ ] Update CaseRequest validation
- [ ] Create CourtsController with CRUD operations
- [ ] Update CasesController to load courts
- [ ] Create court CRUD views
- [ ] Update cases forms with court dropdown
- [ ] Implement cascading dropdowns with AJAX for circuit/secretary/floor/hall
- [ ] Add AJAX endpoint for fetching court details
- [ ] Update cases show view with court link
- [ ] Add court routes
- [ ] Create court policy
- [ ] Add translation keys
- [ ] Add courts to navigation menu
- [ ] Run migrations successfully
- [ ] Test court CRUD operations
- [ ] Test case creation with court selection
- [ ] Verify cascading dropdowns populate correctly when court is selected
- [ ] Test import with court mapping
- [ ] Verify court view shows related cases

---

## Implementation Order

1. Database (migrations + seed)
2. Models (Court, update CaseModel)
3. Form Requests (CourtRequest, update CaseRequest)
4. Controllers (CourtsController, update CasesController)
5. Views (courts CRUD, update cases forms)
6. Routes & Navigation
7. Policies
8. Translations
9. Testing & Validation

---

## Notes

- Keep `matter_court_text` field for backward compatibility and import mapping
- The 4 dropdown option sets (circuit, secretary, floor, hall) will be seeded later by user
- Cascading dropdowns in cases form should only show the specific court's circuit/secretary/floor/hall options
- AJAX response should return option_value records that match the court's stored values
- Maintain `Delayed_Ideas_Plans.md` entry for hearings/tasks integration in court view

