# API 404 Errors - Troubleshooting Guide

> **Issue**: All API endpoints returning 404 (Not Found)  
> **Date**: 2025-11-15

---

## Problem

After deploying the React SPA, all API calls are returning 404 errors:
- `/api/cases` → 404
- `/api/clients` → 404
- `/api/options/case.status` → 404
- All other API endpoints → 404

---

## Root Causes

### 1. Route Cache Issue
Laravel caches routes for performance. After modifying `routes/api.php`, the cache needs to be cleared.

### 2. Options Route Path Mismatch (FIXED)
- **Before**: Route was `/api/options/api/{setKey}` 
- **After**: Changed to `/api/options/{setKey}` to match React app calls
- **Status**: ✅ Fixed in `clm-app/routes/api.php`

### 3. Authentication Required
All API routes are protected by `auth:sanctum` middleware. The React app needs to authenticate first before accessing data.

---

## Solutions

### Step 1: Clear Route Cache

Run these commands in `clm-app/` directory:

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

**Note**: If using route caching in production:
```bash
php artisan route:cache  # After clearing, rebuild cache
```

### Step 2: Verify Routes Are Registered

```bash
php artisan route:list --path=api
```

You should see routes like:
- `GET|HEAD api/cases`
- `GET|HEAD api/clients`
- `GET|HEAD api/options/{setKey}`
- etc.

### Step 3: Test API Endpoints Directly

#### Test Login (Public Route)
```bash
curl -X POST http://litigation.local/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"your-email@example.com","password":"your-password"}' \
  -c cookies.txt \
  -v
```

#### Test Protected Route (After Login)
```bash
curl -X GET http://litigation.local/api/cases \
  -H "Accept: application/json" \
  -b cookies.txt \
  -v
```

### Step 4: Check Web Server Configuration

#### Apache (.htaccess)
Ensure `clm-app/public/.htaccess` exists and has:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

#### Nginx
Ensure API routes are passed to Laravel:
```nginx
location /api {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 5: React App Authentication Flow

The React app needs to:
1. **Check authentication** on app load
2. **Redirect to login** if not authenticated
3. **Store session cookie** after login
4. **Include credentials** in API requests (`withCredentials: true`)

**Current Status**: 
- ✅ API client configured with `withCredentials: true`
- ✅ ProtectedRoute component exists but not used in App.tsx
- ❌ No login page visible
- ❌ Routes not wrapped in ProtectedRoute

---

## Immediate Fixes Applied

### 1. Fixed Options Route Path
**File**: `clm-app/routes/api.php`

**Changed**:
```php
// Before
Route::get('/options/api/{setKey}', [App\Http\Controllers\Api\OptionController::class, 'getBySetKey']);

// After
Route::get('/options/{setKey}', [App\Http\Controllers\Api\OptionController::class, 'getBySetKey'])
    ->where('setKey', '[a-zA-Z0-9._-]+');
```

**Why**: React app calls `/api/options/case.status`, not `/api/options/api/case.status`

### 2. Route Order
Placed the specific route **BEFORE** `apiResource('options')` to avoid route conflicts.

---

## Next Steps

### Option A: Make Some Routes Public (Quick Fix)
For initial page load, make options routes public:

```php
// In routes/api.php - BEFORE auth:sanctum group
Route::get('/options/{setKey}', [App\Http\Controllers\Api\OptionController::class, 'getBySetKey'])
    ->where('setKey', '[a-zA-Z0-9._-]+');
```

### Option B: Implement Login Flow (Proper Fix)
1. Create login page component
2. Wrap routes in ProtectedRoute
3. Handle 401 errors and redirect to login
4. Store session after successful login

### Option C: Temporary Bypass (Development Only)
Remove `auth:sanctum` middleware temporarily for testing:

```php
// TEMPORARY - Remove for production!
Route::middleware('api')->group(function () {
    Route::apiResource('cases', App\Http\Controllers\Api\CaseController::class);
    // ... other routes
});
```

---

## Verification Checklist

- [ ] Route cache cleared: `php artisan route:clear`
- [ ] Routes visible: `php artisan route:list --path=api`
- [ ] Options route fixed: `/api/options/{setKey}` exists
- [ ] Web server configured correctly (.htaccess or Nginx)
- [ ] Test login endpoint: `POST /api/login` works
- [ ] Test protected endpoint: `GET /api/cases` returns 401 (not 404) when not authenticated
- [ ] React app handles authentication flow

---

## Expected Behavior

### Without Authentication
- **401 Unauthorized**: Routes exist but require auth
- **404 Not Found**: Routes don't exist or cache issue

### With Authentication
- **200 OK**: Data returned successfully
- **404 Not Found**: Resource doesn't exist (e.g., case ID not found)

---

## Debugging Commands

```bash
# Check if routes are registered
php artisan route:list --path=api

# Clear all caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Check Laravel logs
tail -f storage/logs/laravel.log

# Test API endpoint
curl -v http://litigation.local/api/cases
```

---

**Status**: 🔧 Route path fixed, but route cache needs clearing and authentication flow needs implementation.

