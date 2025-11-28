# Fix: Opponent Relationship Foreign Key Error

> **Issue**: SQL error `Column not found: case_model_id` when loading opponent with cases  
> **Root Cause**: Laravel guessing wrong foreign key name for pivot table  
> **Status**: ✅ Fixed by explicitly specifying foreign keys

---

## Problem

When accessing `/opponents/811`, got SQL error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'case_opponents.case_model_id' in 'field list'
```

Laravel was trying to use `case_model_id` instead of `case_id` in the pivot table.

---

## Root Cause

When using `belongsToMany` with a model that has a non-standard table name:
- Model: `CaseModel` (class name)
- Table: `cases` (actual table name)
- Laravel guesses: `case_model_id` (based on class name)

But the pivot table `case_opponents` uses:
- `case_id` (not `case_model_id`)
- `opponent_id`

---

## Solution

Explicitly specify the foreign keys in the relationship:

**Before:**
```php
public function cases()
{
    return $this->belongsToMany(\App\Models\CaseModel::class, 'case_opponents')
        ->withPivot(['capacity_id', 'is_primary', 'display_order', 'alias_text', 'id'])
        ->withTimestamps();
}
```

**After:**
```php
public function cases()
{
    return $this->belongsToMany(\App\Models\CaseModel::class, 'case_opponents', 'opponent_id', 'case_id')
        ->withPivot(['capacity_id', 'is_primary', 'display_order', 'alias_text', 'id'])
        ->withTimestamps();
}
```

### Parameters Explained

`belongsToMany(Model::class, 'pivot_table', 'foreign_key', 'related_key')`

- `Model::class`: `CaseModel` - the related model
- `'case_opponents'`: The pivot table name
- `'opponent_id'`: Foreign key in pivot table pointing to `opponents` table
- `'case_id'`: Foreign key in pivot table pointing to `cases` table

---

## Database Structure

The `case_opponents` pivot table:
```sql
CREATE TABLE case_opponents (
    id BIGINT PRIMARY KEY,
    case_id BIGINT,        -- Foreign key to cases table
    opponent_id BIGINT,    -- Foreign key to opponents table
    capacity_id BIGINT,
    is_primary BOOLEAN,
    display_order INT,
    alias_text VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id),
    FOREIGN KEY (opponent_id) REFERENCES opponents(id)
);
```

---

## Files Changed

1. ✅ `clm-app/app/Models/Opponent.php`
   - Added explicit foreign key parameters to `cases()` relationship

---

## Testing

After the fix:

1. **Config cache cleared** (already done)
2. **Test opponent detail page**: `/opponents/811`
   - Should load without SQL error
   - Should show associated cases in "Associated Cases" tab

---

## Related Models

The reverse relationship in `CaseModel` already has correct foreign keys:
```php
public function opponents()
{
    return $this->belongsToMany(Opponent::class, 'case_opponents', 'case_id', 'opponent_id')
        ->withPivot(['capacity_id', 'is_primary', 'display_order', 'alias_text', 'id', 'deleted_at'])
        ->withTimestamps();
}
```

This is correct because:
- `'case_id'` is the foreign key from `cases` table
- `'opponent_id'` is the foreign key to `opponents` table

---

**Status**: ✅ Fixed - Relationship now uses correct foreign keys

