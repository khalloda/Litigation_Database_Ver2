# API 404 Errors - Fix Instructions

> **Status**: Routes fixed, but need route cache cleared and authentication handled

---

## Issues Found

1. ✅ **Options route path fixed**: Changed from `/api/options/api/{setKey}` to `/api/options/{setKey}`
2. ⚠️ **Route cache needs clearing**: Laravel may be serving cached routes
3. ⚠️ **Authentication required**: All API routes require `auth:sanctum` but user isn't logged in

---

## Immediate Actions Required

### Step 1: Clear Route Cache

**Run these commands in `clm-app/` directory:**

```powershell
# Navigate to Laravel app
cd clm-app

# Clear route cache
php artisan route:clear

# Clear all caches (recommended)
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild route cache (if using in production)
php artisan route:cache
```

### Step 2: Verify Routes Are Registered

```powershell
php artisan route:list --path=api
```

**Expected output**: You should see routes like:
- `GET|HEAD api/cases`
- `GET|HEAD api/clients`
- `GET|HEAD api/options/{setKey}` ← This should now exist
- etc.

### Step 3: Test API Endpoint Directly

**Test login (should work - public route):**
```powershell
curl -X POST http://litigation.local/api/login `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d '{\"email\":\"your-email@example.com\",\"password\":\"your-password\"}' `
  -c cookies.txt `
  -v
```

**Test protected route (should return 401 if not authenticated, 200 if authenticated):**
```powershell
curl -X GET http://litigation.local/api/cases `
  -H "Accept: application/json" `
  -v
```

**Expected responses:**
- **Without auth**: `401 Unauthorized` (not 404!)
- **With auth**: `200 OK` with data

---

## If Still Getting 404 After Clearing Cache

### Check 1: Verify RouteServiceProvider Order

**File**: `clm-app/app/Providers/RouteServiceProvider.php`

Should have:
```php
Route::middleware('api')
    ->prefix('api')
    ->group(base_path('routes/api.php'));  // ← API routes loaded FIRST

Route::middleware('web')
    ->group(base_path('routes/web.php'));   // ← Web routes loaded SECOND
```

### Check 2: Verify .htaccess Configuration

**File**: `clm-app/public/.htaccess`

Should redirect all requests to `index.php`:
```apache
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

### Check 3: Check Laravel Logs

```powershell
Get-Content clm-app\storage\logs\laravel.log -Tail 50
```

Look for route matching errors or exceptions.

---

## Authentication Flow Issue

**Current Problem**: React app tries to load data immediately, but all API routes require authentication.

**Solutions**:

### Option A: Make Options Routes Public (Quick Fix)

**File**: `clm-app/routes/api.php`

Move options route outside auth group:
```php
// Public routes (before auth:sanctum group)
Route::get('/options/{setKey}', [App\Http\Controllers\Api\OptionController::class, 'getBySetKey'])
    ->where('setKey', '[a-zA-Z0-9._-]+');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // ... other routes
});
```

### Option B: Implement Login Page (Proper Fix)

1. Create login page component
2. Wrap routes in `ProtectedRoute` component
3. Handle 401 errors and redirect to login
4. Store session cookie after login

---

## Files Changed

1. ✅ `clm-app/routes/api.php` - Fixed options route path
2. ✅ `clm-app/routes/web.php` - Updated comments (no functional change)
3. ✅ `docs/api-404-troubleshooting.md` - Created troubleshooting guide

---

## Next Steps

1. **Clear route cache** (see Step 1 above)
2. **Test API endpoints** (see Step 3 above)
3. **If still 404**: Check logs and RouteServiceProvider
4. **If 401**: Implement authentication flow or make some routes public
5. **If 200**: Success! Routes are working

---

**Status**: 🔧 Ready for testing after route cache cleared

