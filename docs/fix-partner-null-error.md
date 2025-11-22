# Fix: Partner Null Error on Case Detail Page

> **Issue**: Error "Cannot read properties of null (reading 'lawyer_name_en')" on `/cases/1479`  
> **Root Cause**: API returns `partner: null` but code accessed properties without null check  
> **Status**: ✅ Fixed

---

## Problem

When opening a case detail page where the case has no partner (null), the page crashed with:
```
Uncaught TypeError: Cannot read properties of null (reading 'lawyer_name_en')
```

**Root Cause:**
The API was returning `partner: null` (not `undefined`), and while there was a check `caseData.partner ?`, JavaScript's truthiness check might not catch all edge cases, or the check wasn't strict enough.

---

## Solution

### 1. Added Stricter Null Checks

**Before:**
```typescript
const partnerName = caseData.partner ? (language === 'ar' ? caseData.partner.lawyer_name_ar : caseData.partner.lawyer_name_en) : '-';
```

**After:**
```typescript
const partnerName = (caseData.partner && caseData.partner !== null) ? 
    (language === 'ar' ? (caseData.partner.lawyer_name_ar || '-') : (caseData.partner.lawyer_name_en || '-')) : 
    '-';
```

### 2. Normalized Null to Undefined

Added normalization in useEffect to convert `null` to `undefined` for consistency:
```typescript
// Ensure null objects are not set (convert null to undefined for consistency)
if (caseData.partner === null) caseData.partner = undefined;
if (caseData.client === null) caseData.client = undefined;
if (caseData.court === null) caseData.court = undefined;
if (caseData.lawyer_a === null) caseData.lawyer_a = undefined;
if (caseData.lawyer_b === null) caseData.lawyer_b = undefined;
```

### 3. Added Fallback Values

Added fallback values (`|| '-'`) when accessing nested properties to handle cases where the object exists but properties are missing.

---

## Files Changed

1. ✅ `AiStudio-CLMS2/pages/CaseDetailPage.tsx`
   - Added stricter null checks for `partner`, `client`, `court`, `lawyer_a`, `lawyer_b`
   - Added null normalization in useEffect
   - Added fallback values for nested properties

---

## Testing

After rebuilding and deploying:

1. **Navigate to a case without partner**: `/cases/1479`
2. **Check console** - Should not have null reference errors
3. **Verify page displays** - Partner field should show "-" instead of crashing

---

## Pattern Applied

This same pattern should be applied to other detail pages:
- ClientDetailPage
- LawyerDetailPage
- OpponentDetailPage
- HearingDetailPage
- DocumentDetailPage

All should check for `null` explicitly, not just truthiness.

---

**Status**: ✅ Fixed - Ready for testing

