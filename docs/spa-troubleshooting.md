# SPA Blank Screen Troubleshooting

> **Issue**: Blank screen after deploying React build to Laravel public directory

---

## Quick Fixes Applied

✅ **Fixed Root Route** - Updated `routes/web.php` to serve `index.html` instead of Laravel welcome view

---

## Common Causes & Solutions

### 1. Root Route Serving Wrong View ✅ FIXED
**Problem**: Laravel root route (`/`) was serving `view('welcome')` instead of React SPA

**Solution**: Updated root route to serve `index.html`:
```php
Route::get('/', function () {
    return file_exists(public_path('index.html'))
        ? response()->file(public_path('index.html'))
        : view('welcome');
});
```

### 2. JavaScript File Not Loading

**Check Browser Console** (F12 → Console tab):
- Look for 404 errors on `/assets/index-ZFxIBYC0.js`
- Look for CORS errors
- Look for module loading errors

**Verify Files Exist**:
```bash
# Check if JS file exists
Test-Path "clm-app\public\assets\index-ZFxIBYC0.js"

# Should return: True
```

**If Missing, Copy Again**:
```powershell
Copy-Item -Path "AiStudio-CLMS2\dist\assets\*" -Destination "clm-app\public\assets\" -Recurse -Force
```

### 3. Browser Console Errors

**Common Errors**:

#### Error: "Failed to load module script"
- **Cause**: ES modules not supported or path issue
- **Solution**: Check browser supports ES modules (Chrome/Edge/Firefox modern versions)
- **Check**: Verify script tag has `type="module"` in `index.html`

#### Error: "CORS policy blocked"
- **Cause**: Cross-origin request blocked
- **Solution**: Since using same domain (`litigation.local`), this shouldn't happen
- **Check**: Ensure accessing via `http://litigation.local` (not localhost)

#### Error: "Cannot find module 'react'"
- **Cause**: Import map CDN not loading
- **Solution**: Check internet connection (React loads from CDN)
- **Check**: Verify `importmap` in `index.html` is correct

### 4. .htaccess Interference

**Check**: `clm-app/public/.htaccess` should allow static files

**Current .htaccess** (should be fine):
```apache
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

This means: "If file exists, serve it directly. Otherwise, route to Laravel."

**If assets still not loading**:
- Check file permissions on `public/assets/`
- Check web server (Apache/Nginx) configuration
- Verify `public/assets/` directory is readable

### 5. React Router Not Mounting

**Check**: Open browser console and run:
```javascript
// Check if root div exists
document.getElementById('root')

// Should return: <div id="root"></div>

// Check if React is loaded
window.React

// Should return: Object (React library)
```

**If root div is empty**:
- React app didn't mount
- Check for JavaScript errors in console
- Check if `index-ZFxIBYC0.js` loaded successfully (Network tab)

### 6. Network Tab Check

**Open DevTools → Network Tab**:
1. Refresh page (`Ctrl+F5` to hard refresh)
2. Check if `index.html` loads (200 status)
3. Check if `index-ZFxIBYC0.js` loads (200 status)
4. Check if CDN resources load (React, React-DOM)

**If 404 on JS file**:
- File not copied correctly
- Path mismatch in `index.html`
- Web server not serving from `public/` directory

---

## Step-by-Step Debugging

### Step 1: Verify Files
```powershell
# Check source
Get-ChildItem "AiStudio-CLMS2\dist" -Recurse | Select-Object Name

# Check destination
Get-ChildItem "clm-app\public" -Recurse | Select-Object Name

# Should see:
# - index.html
# - assets/index-ZFxIBYC0.js
```

### Step 2: Check Browser
1. Open `http://litigation.local`
2. Open DevTools (F12)
3. Check **Console** tab for errors
4. Check **Network** tab for failed requests
5. Check **Elements** tab - is `<div id="root"></div>` empty?

### Step 3: Test Direct Access
Try accessing files directly:
- `http://litigation.local/index.html` - Should show React app
- `http://litigation.local/assets/index-ZFxIBYC0.js` - Should download JS file

### Step 4: Check Laravel Routes
```bash
cd clm-app
php artisan route:list | grep "^GET.*/$"
```

Should show root route serving `index.html`

---

## Quick Fixes

### Fix 1: Re-copy Files
```powershell
# Remove old files
Remove-Item "clm-app\public\index.html" -Force -ErrorAction SilentlyContinue
Remove-Item "clm-app\public\assets" -Recurse -Force -ErrorAction SilentlyContinue

# Copy fresh build
Copy-Item -Path "AiStudio-CLMS2\dist\*" -Destination "clm-app\public\" -Recurse -Force

# Verify
Test-Path "clm-app\public\index.html"
Test-Path "clm-app\public\assets\index-ZFxIBYC0.js"
```

### Fix 2: Clear Laravel Cache
```bash
cd clm-app
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Fix 3: Hard Refresh Browser
- **Chrome/Edge**: `Ctrl+Shift+R` or `Ctrl+F5`
- **Firefox**: `Ctrl+Shift+R`
- Or clear browser cache

### Fix 4: Check Web Server Config

**Apache**: Ensure `DocumentRoot` points to `clm-app/public`

**Nginx**: Ensure `root` points to `clm-app/public`

---

## Expected Behavior

After fixes:
1. ✅ `http://litigation.local` shows React app (not blank)
2. ✅ Browser console shows no errors
3. ✅ Network tab shows all files loading (200 status)
4. ✅ React app mounts and shows dashboard/login

---

## Still Not Working?

1. **Check Laravel Logs**:
   ```bash
   tail -f clm-app/storage/logs/laravel.log
   ```

2. **Check Web Server Logs**:
   - Apache: `error.log`
   - Nginx: `error.log`

3. **Test with Simple HTML**:
   Create `clm-app/public/test.html`:
   ```html
   <!DOCTYPE html>
   <html>
   <body>
     <h1>Test</h1>
   </body>
   </html>
   ```
   Access: `http://litigation.local/test.html`
   - If this works, issue is with React build
   - If this doesn't work, issue is with web server config

---

**Status**: Root route fixed. Check browser console for specific errors.

