# Quick Fix: API Routes 404 Errors

## Changes Made

1. **Fixed options route path**: `/api/options/api/{setKey}` → `/api/options/{setKey}`
2. **Made options route public**: Moved outside `auth:sanctum` group so app can load initial data
3. **Updated route order**: Public route before protected routes

## What You Need to Do

### 1. Clear Route Cache

```powershell
cd clm-app
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 2. Test

Refresh your browser at `http://litigation.local` and check console:
- Options routes (`/api/options/case.status`, etc.) should work ✅
- Other routes (`/api/cases`, `/api/clients`, etc.) will return **401** (not 404) - this is expected until you log in

### 3. Next Steps

**Option A**: Implement login page and authentication flow  
**Option B**: Make more routes public for testing (not recommended for production)

---

**Files Changed**:
- `clm-app/routes/api.php` - Options route made public and path fixed

