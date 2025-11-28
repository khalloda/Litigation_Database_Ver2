# Fix: SSL Certificate Error for Gemini API

> **Issue**: `cURL error 60: SSL certificate problem: unable to get local issuer certificate`  
> **Root Cause**: Windows/local development environment missing CA certificate bundle  
> **Status**: ✅ Fixed with configurable SSL verification

---

## Problem

When calling Gemini API, Laravel's HTTP client (Guzzle/cURL) fails with:
```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

This is a common issue on Windows/local development environments where:
- cURL doesn't have access to CA certificate bundle
- SSL certificate verification fails
- HTTPS requests are blocked

---

## Solution

### 1. Added Configurable SSL Verification

**File**: `clm-app/config/services.php`
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'verify_ssl' => env('GEMINI_VERIFY_SSL', true), // Set to false for local dev if SSL issues
],
```

### 2. Updated AI Controller

**File**: `clm-app/app/Http/Controllers/Api/AiController.php`

Added conditional SSL verification:
- Only disables SSL verification in `local` environment
- Only if `GEMINI_VERIFY_SSL=false` in `.env`
- Logs warning when SSL verification is disabled
- Defaults to `true` (secure) for production

```php
// Disable SSL verification for local development (Windows SSL certificate issue)
// Only disable if APP_ENV is local and GEMINI_VERIFY_SSL is false
if (config('app.env') === 'local' && !config('services.gemini.verify_ssl', true)) {
    $httpOptions['verify'] = false;
    Log::warning('SSL verification disabled for Gemini API (local development only)');
}
```

---

## Configuration

### For Local Development (Windows)

Add to `.env`:
```env
APP_ENV=local
GEMINI_VERIFY_SSL=false
```

### For Production

Keep default (SSL verification enabled):
```env
APP_ENV=production
# GEMINI_VERIFY_SSL defaults to true
```

---

## Files Changed

1. ✅ `clm-app/config/services.php`
   - Added `verify_ssl` configuration option

2. ✅ `clm-app/app/Http/Controllers/Api/AiController.php`
   - Added conditional SSL verification
   - Added warning log when SSL verification is disabled

---

## Steps to Fix

1. **Add to `.env`**:
   ```env
   GEMINI_VERIFY_SSL=false
   ```

2. **Clear config cache**:
   ```bash
   cd clm-app
   php artisan config:clear
   ```

3. **Test AI summary generation**:
   - Navigate to Cases page
   - Click "Generate AI Summary"
   - Should work without SSL error

---

## Security Notes

⚠️ **Important**: 
- SSL verification is **only disabled in `local` environment**
- It requires explicit `GEMINI_VERIFY_SSL=false` setting
- **Never disable SSL verification in production**
- Default is `true` (secure)

---

## Alternative Solutions (If Needed)

### Option 1: Download CA Certificate Bundle

1. Download `cacert.pem` from: https://curl.se/ca/cacert.pem
2. Save to `clm-app/storage/cacert.pem`
3. Update `php.ini`:
   ```ini
   curl.cainfo = "D:\path\to\clm-app\storage\cacert.pem"
   ```
4. Restart PHP/Laravel server

### Option 2: Use System Certificate Store

Update `php.ini`:
```ini
openssl.cafile = "C:\path\to\cacert.pem"
```

---

## Testing

After adding `GEMINI_VERIFY_SSL=false` to `.env`:

1. Clear config: `php artisan config:clear`
2. Try generating summary
3. Check logs for: "SSL verification disabled for Gemini API (local development only)"
4. Summary should generate successfully

---

**Status**: ✅ Fixed with configurable SSL verification - Ready for testing

