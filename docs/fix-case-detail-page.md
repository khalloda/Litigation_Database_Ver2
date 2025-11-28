# Fix: Case Detail Page Blank Screen

> **Issue**: Blank page when opening `/cases/336` with error "Cannot read properties of undefined (reading 'length')"  
> **Root Cause**: Missing null checks for arrays (hearings, tasks, documents, opponents)  
> **Status**: ✅ Fixed

---

## Problem

When navigating to a case detail page (e.g., `/cases/336`), the page was blank with console error:
```
Uncaught TypeError: Cannot read properties of undefined (reading 'length')
```

**Root Cause:**
The component was accessing `caseData.hearings.length`, `caseData.tasks.length`, `caseData.documents.length`, and `caseData.opponents.map()` without checking if these arrays exist.

---

## Solution

### 1. Added Null Safety in React Component

**CaseDetailPage.tsx:**
- Added null checks for arrays before accessing `.length` or `.map()`
- Ensured arrays are initialized as empty arrays if undefined
- Added null checks for `client` and `partner` objects

**Before:**
```typescript
count={caseData.hearings.length}  // ❌ Crashes if undefined
{caseData.opponents.map(...)}     // ❌ Crashes if undefined
```

**After:**
```typescript
count={(caseData.hearings || []).length}  // ✅ Safe
{(caseData.opponents || []).map(...)}     // ✅ Safe
```

### 2. Added Array Initialization in useEffect

```typescript
if (caseData) {
    caseData.hearings = caseData.hearings || [];
    caseData.tasks = caseData.tasks || [];
    caseData.documents = caseData.documents || [];
    caseData.opponents = caseData.opponents || [];
}
```

### 3. Added Relationships to API Response

**CaseController.php:**
- Added `hearings`, `documents`, and `adminTasks` to the transformation
- Ensured these relationships are loaded in the `show` method
- Mapped them to arrays in the API response

**Before:**
```php
// Relationships not included in transformation
```

**After:**
```php
'hearings' => $case->hearings ? $case->hearings->map(...)->toArray() : [],
'documents' => $case->documents ? $case->documents->map(...)->toArray() : [],
'tasks' => $case->adminTasks ? $case->adminTasks->map(...)->toArray() : [],
```

### 4. Added Debug Logging

Added console logs to help debug:
- `console.log('Case detail data:', data)` - Shows API response
- `console.error('Error loading case:', err)` - Shows errors

---

## Files Changed

1. ✅ `AiStudio-CLMS2/pages/CaseDetailPage.tsx`
   - Added null safety for arrays
   - Added array initialization in useEffect
   - Added null checks for client/partner
   - Added debug logging

2. ✅ `clm-app/app/Http/Controllers/Api/CaseController.php`
   - Added `hearings`, `documents`, `adminTasks` to transformation
   - Ensured relationships are loaded in `show` method

---

## Testing

After rebuilding and deploying:

1. **Navigate to a case detail page**: `/cases/336`
2. **Check console** for:
   - "Case detail data:" log showing API response
   - No errors about undefined properties
3. **Verify page displays**:
   - Case information
   - Client details
   - Opponents list
   - Hearings/Tasks/Documents counts

---

## Related Issues

This same pattern should be checked for other detail pages:
- ClientDetailPage
- LawyerDetailPage
- OpponentDetailPage
- HearingDetailPage
- DocumentDetailPage

If they access arrays without null checks, they'll have the same issue.

---

**Status**: ✅ Fixed - Ready for testing

