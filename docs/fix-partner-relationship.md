# Fix: Missing Partner Relationship

> **Issue**: 500 Internal Server Error on `/api/cases`  
> **Root Cause**: `CaseController` tries to eager load `partner` relationship that doesn't exist  
> **Status**: ✅ Fixed

---

## Error

```
Call to undefined relationship [partner] on model [App\Models\CaseModel].
```

## Problem

The `CaseController` was trying to eager load a `partner` relationship:

```php
CaseModel::with([
    'client:id,client_name_ar,client_name_en',
    'opponent:id,opponent_name_ar,opponent_name_en',
    'partner:id,lawyer_name_ar,lawyer_name_en',  // ← This relationship didn't exist
    'court:id,court_name_ar,court_name_en',
]);
```

But the `CaseModel` didn't have a `partner()` relationship method defined.

## Solution

Added the missing `partner()` relationship to `CaseModel`:

```php
public function partner()
{
    return $this->belongsTo(Lawyer::class, 'matter_partner_id');
}
```

**File**: `clm-app/app/Models/CaseModel.php`

## Note

There was already a `matterPartnerRef()` relationship that does the same thing, but the controller uses `partner`, so we added that method for consistency with the API controller's expectations.

---

**Status**: ✅ Fixed - Refresh browser and test `/api/cases` endpoint

