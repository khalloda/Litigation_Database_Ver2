# Debug: No Data Showing Issue

> **Issue**: No errors, but no data shown from database  
> **Status**: 🔍 Investigating

---

## Possible Causes

### 1. Pagination Structure Mismatch
Laravel pagination returns:
```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 25,
  "total": 100
}
```

React expects: `response.data.data` for paginated, `response.data` for arrays

### 2. Empty Database
Database tables might be empty

### 3. Authorization Issues
API might be returning empty arrays due to policy restrictions

### 4. Data Structure Mismatch
API response structure might not match what React expects

---

## Debugging Steps

### Step 1: Check Browser Console
Open browser DevTools → Network tab:
- Check API requests (e.g., `/api/cases`)
- Check response status (200 OK?)
- Check response body (is data empty or structure wrong?)

### Step 2: Check API Response Structure
Test API directly:
```bash
curl http://litigation.local/api/cases \
  -H "Accept: application/json" \
  -b cookies.txt \
  -v
```

### Step 3: Check Database
```sql
SELECT COUNT(*) FROM cases;
SELECT COUNT(*) FROM clients;
SELECT COUNT(*) FROM lawyers;
```

### Step 4: Check Laravel Logs
```powershell
Get-Content clm-app\storage\logs\laravel.log -Tail 50
```

---

## Quick Fixes to Try

### Fix 1: Update Service Functions to Handle Pagination
If API returns paginated data, extract `data` property:

```typescript
// In services/cases.ts
export async function fetchCases(params?: {...}) {
  const response = await api.get('/cases', { params });
  // Handle pagination
  return response.data.data || response.data;
}
```

### Fix 2: Check if Data is Wrapped
Some controllers might wrap data:
```json
{
  "data": {
    "data": [...],
    "current_page": 1
  }
}
```

### Fix 3: Verify Authentication
Make sure user is logged in and has permissions

---

**Next Steps**: Check browser console Network tab to see actual API responses

