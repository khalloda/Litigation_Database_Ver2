# Circuit Option Sets Implementation Documentation

## Overview
This document details the implementation of a complex circuit structure for the Central Litigation Management system. The implementation replaces a single circuit field with a composite structure consisting of three components: Circuit Name, Circuit Serial, and Circuit Shift.

## Architecture Decision

### Why Circuit Option Sets?
The user requested a more complex circuit structure where a circuit is composed of:
- **Circuit Name**: A selection from an option list (e.g., "Labor", "Civil", "Commercial")
- **Circuit Serial**: An optional selection (1-100, A-Z, أ-ي) from an option list
- **Circuit Shift**: An optional selection (Morning/Night, default Morning) from an option list

This approach provides:
- **Flexibility**: Users can create any combination of circuit components
- **Consistency**: Standardized option lists prevent data inconsistencies
- **Scalability**: Easy to add new circuit names, serials, or shifts
- **Localization**: Full Arabic/English support for all components

## Database Structure

### 1. Circuit Option Sets
Three new option sets were created:

#### `circuit.name` (48 values)
Contains circuit names from the CSV data:
- Arabic: "إداري", "استئناف", "جنح مستأنف", "مباني", "طعون", etc.
- English: "Administrative", "Appeal", "Appeals – Misdemeanors", "Buildings", "Cassations", etc.

#### `circuit.serial` (158 values)
Contains serial numbers and letters:
- Numbers: 1-100
- English letters: A-Z
- Arabic letters: أ-ي

#### `circuit.shift` (2 values)
Contains shift options:
- Arabic: "صباحي", "مسائي"
- English: "Morning", "Night"

### 2. Cases Table Updates
The `cases` table was modified to include three new foreign key columns:

```sql
ALTER TABLE cases ADD COLUMN circuit_name_id BIGINT UNSIGNED NULL;
ALTER TABLE cases ADD COLUMN circuit_serial_id BIGINT UNSIGNED NULL;
ALTER TABLE cases ADD COLUMN circuit_shift_id BIGINT UNSIGNED NULL;

-- Foreign key constraints
ALTER TABLE cases ADD CONSTRAINT cases_circuit_name_id_foreign 
    FOREIGN KEY (circuit_name_id) REFERENCES option_values(id) ON DELETE SET NULL;
ALTER TABLE cases ADD CONSTRAINT cases_circuit_serial_id_foreign 
    FOREIGN KEY (circuit_serial_id) REFERENCES option_values(id) ON DELETE SET NULL;
ALTER TABLE cases ADD CONSTRAINT cases_circuit_shift_id_foreign 
    FOREIGN KEY (circuit_shift_id) REFERENCES option_values(id) ON DELETE SET NULL;
```

The original `matter_circuit` column was renamed to `matter_circuit_legacy` for import/legacy purposes.

### 3. Court-Circuit Pivot Table Updates
The `court_circuit` pivot table was restructured to support the new circuit components:

```sql
-- Old structure (dropped)
court_id + option_value_id

-- New structure
court_id + circuit_name_id + circuit_serial_id + circuit_shift_id
```

With a unique constraint on the combination to prevent duplicate circuit assignments per court.

## Model Updates

### 1. CaseModel
Updated to include new circuit relationships:

```php
// New fillable fields
'circuit_name_id', 'circuit_serial_id', 'circuit_shift_id', 'matter_circuit_legacy'

// New relationships
public function circuitName()
{
    return $this->belongsTo(OptionValue::class, 'circuit_name_id');
}

public function circuitSerial()
{
    return $this->belongsTo(OptionValue::class, 'circuit_serial_id');
}

public function circuitShift()
{
    return $this->belongsTo(OptionValue::class, 'circuit_shift_id');
}
```

### 2. Court Model
Updated to use the new CourtCircuit model:

```php
public function circuits()
{
    return $this->hasMany(CourtCircuit::class);
}
```

### 3. CourtCircuit Model (New)
Created to handle the new pivot table structure:

```php
class CourtCircuit extends Model
{
    protected $fillable = [
        'court_id', 'circuit_name_id', 'circuit_serial_id', 'circuit_shift_id'
    ];

    // Relationships to option values
    public function circuitName() { /* ... */ }
    public function circuitSerial() { /* ... */ }
    public function circuitShift() { /* ... */ }

    // Accessor for full circuit display
    public function getFullNameAttribute()
    {
        // Returns formatted string like "Labor 11 (N)"
    }
}
```

## Controller Updates

### 1. CasesController
Updated to handle the new circuit structure:

```php
// Load circuit option values in create/edit methods
$circuitNames = OptionValue::whereHas('optionSet', function ($q) {
    $q->where('key', 'circuit.name');
})->where('is_active', true)->orderBy('id')->get();

// Similar for circuitSerials and circuitShifts

// Updated show method to load new relationships
$case->load('circuitName', 'circuitSerial', 'circuitShift', /* ... */);
```

### 2. CourtsController
Updated to handle the new circuit structure in store/update methods:

```php
// Handle new circuit structure: array of [name_id, serial_id, shift_id]
$circuitData = [];
foreach ($request->court_circuits as $circuit) {
    if (isset($circuit['name_id']) && isset($circuit['shift_id'])) {
        $circuitData[] = [
            'circuit_name_id' => $circuit['name_id'],
            'circuit_serial_id' => $circuit['serial_id'] ?? null,
            'circuit_shift_id' => $circuit['shift_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
$court->circuits()->delete();
$court->circuits()->createMany($circuitData);
```

## UI Implementation

### 1. Case Forms (Create/Edit)
Implemented a "Circuit Container" with three dropdowns:

```blade
<div class="card border-primary">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">{{ __('app.circuit_container') }}</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <!-- Circuit Name Dropdown -->
            </div>
            <div class="col-md-4">
                <!-- Circuit Serial Dropdown -->
            </div>
            <div class="col-md-4">
                <!-- Circuit Shift Dropdown -->
            </div>
        </div>
    </div>
</div>
```

### 2. Court Forms (Create/Edit)
Implemented multiple Circuit rows with add/remove functionality:

```blade
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ __('app.court_circuits') }}</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-circuit-row">
        <i class="fas fa-plus"></i> {{ __('app.add_circuit') }}
    </button>
</div>

<div id="circuit-rows-container">
    <!-- Dynamic circuit rows with 3 dropdowns each -->
</div>
```

### 3. JavaScript Functionality
Added dynamic row management for court forms:

```javascript
// Add circuit row
$('#add-circuit-row').on('click', function() {
    // Clone template and append with incremented index
});

// Remove circuit row
$(document).on('click', '.remove-circuit-row', function() {
    $(this).closest('.circuit-row').remove();
});
```

## Display Logic

### 1. Case Show View
Displays concatenated circuit information:

```blade
@if($case->circuitName || $case->circuitSerial || $case->circuitShift)
    @php
        $name = $case->circuitName ? (app()->getLocale() === 'ar' ? $case->circuitName->label_ar : $case->circuitName->label_en) : '';
        $serial = $case->circuitSerial ? (app()->getLocale() === 'ar' ? $case->circuitSerial->label_ar : $case->circuitSerial->label_en) : '';
        $shift = $case->circuitShift ? (app()->getLocale() === 'ar' ? $case->circuitShift->label_ar : $case->circuitShift->label_en) : '';
        
        $result = $name;
        if ($serial) $result .= " {$serial}";
        if ($shift && $shift !== 'Morning') $result .= " ({$shift})";
    @endphp
    {{ $result }}
@else
    <span class="text-muted">-</span>
@endif
```

### 2. Court Show View
Uses the `full_name` accessor from CourtCircuit model:

```blade
@forelse($court->circuits as $circuit)
    <span class="badge bg-primary me-1 mb-1">
        {{ $circuit->full_name }}
    </span>
@empty
    <span class="text-muted">-</span>
@endforelse
```

## Validation Updates

### 1. CaseRequest
Updated validation rules:

```php
'circuit_name_id' => 'nullable|exists:option_values,id',
'circuit_serial_id' => 'nullable|exists:option_values,id',
'circuit_shift_id' => 'nullable|exists:option_values,id',
```

### 2. CourtRequest
Updated to handle circuit arrays:

```php
'court_circuits' => 'nullable|array',
'court_circuits.*' => 'exists:option_values,id',
```

## Translation Keys

Added comprehensive translations for circuit terms:

### English
```php
'circuit' => 'Circuit',
'circuit_name' => 'Circuit Name',
'circuit_serial' => 'Circuit Serial',
'circuit_shift' => 'Circuit Shift',
'circuit_container' => 'Circuit',
'add_circuit' => 'Add Circuit',
'remove_circuit' => 'Remove Circuit',
'select_circuit_name' => 'Select Circuit Name',
'select_circuit_serial' => 'Select Circuit Serial',
'select_circuit_shift' => 'Select Circuit Shift',
'morning' => 'Morning',
'night' => 'Night',
```

### Arabic
```php
'circuit' => 'الدائرة',
'circuit_name' => 'اسم الدائرة',
'circuit_serial' => 'رقم الدائرة التسلسلي',
'circuit_shift' => 'دوام الدائرة',
'circuit_container' => 'الدائرة',
'add_circuit' => 'إضافة دائرة',
'remove_circuit' => 'إزالة الدائرة',
'select_circuit_name' => 'اختر اسم الدائرة',
'select_circuit_serial' => 'اختر الرقم التسلسلي',
'select_circuit_shift' => 'اختر الدوام',
'morning' => 'صباحي',
'night' => 'مسائي',
```

## Migration Files

### 1. Create Circuit Option Sets
`2025_10_14_113613_create_circuit_option_sets.php`
- Creates 3 option sets with all values
- Handles auto-increment disabled for option_sets table
- Seeds 48 circuit names, 158 serials, 2 shifts

### 2. Update Cases Table
`2025_10_14_113746_update_cases_for_circuit_option_sets.php`
- Adds 3 new FK columns to cases table
- Renames matter_circuit to matter_circuit_legacy
- Sets default shift to Morning

### 3. Update Court-Circuit Pivot
`2025_10_14_113840_update_court_circuit_pivot_for_option_sets.php`
- Recreates pivot table with 3 circuit FK columns
- Adds unique constraint on combination
- Maintains referential integrity

## User Experience

### For Cases:
1. User selects a court from dropdown
2. Circuit dropdowns become enabled
3. User selects Circuit Name, Serial, and Shift from separate dropdowns
4. System displays concatenated result (e.g., "Labor 11 (N)")

### For Courts:
1. User can add multiple circuit rows
2. Each row has 3 dropdowns (Name, Serial, Shift)
3. User can remove circuit rows as needed
4. System enforces unique circuit combinations per court

## Import Considerations

The current implementation requires manual parsing for imports with values like:
- `"3 عمال (مسائي)"`
- `"10 عمال"`
- `"1 طعون"`

The `matter_circuit_legacy` field is preserved for import purposes, but automatic mapping to the 3-component structure would require additional parsing logic.

## Benefits

1. **Flexibility**: Users can create any circuit combination
2. **Consistency**: Standardized option lists prevent data entry errors
3. **Scalability**: Easy to add new circuit components
4. **Localization**: Full Arabic/English support
5. **Data Integrity**: Foreign key constraints ensure valid references
6. **User Experience**: Intuitive UI with clear visual separation

## Future Enhancements

1. **Import Parsing**: Add logic to automatically parse legacy circuit text
2. **Circuit Templates**: Pre-defined circuit combinations for common cases
3. **Circuit Analytics**: Reporting on circuit usage and performance
4. **Circuit Scheduling**: Integration with hearing scheduling system

## Testing Checklist

- [ ] Create case with circuit components
- [ ] Edit case circuit components
- [ ] Create court with multiple circuits
- [ ] Edit court circuits (add/remove)
- [ ] Display circuit information correctly
- [ ] Validate circuit combinations
- [ ] Test Arabic/English localization
- [ ] Verify unique constraints work
- [ ] Test cascading dropdown behavior

## Conclusion

The Circuit Option Sets implementation provides a robust, flexible, and user-friendly system for managing circuit information in the Central Litigation Management system. The three-component structure allows for precise circuit identification while maintaining data consistency and providing an intuitive user experience.
