# SPA Deployment Fix - Locale Files

> **Issue**: Blank screen due to missing locale translation files  
> **Date**: 2025-11-15

---

## Problem

After copying React build files, the app showed a blank screen with console errors:
```
GET http://litigation.local/locales/en.json 404 (Not Found)
Translation file error: Error: Could not load translations for en
```

**Root Cause**: The locale translation files (`en.json`, `ar.json`) were not copied to the public directory.

---

## Solution

### Step 1: Copy Locale Files

The React app loads translation files from `/locales/{language}.json`. These files must be in `clm-app/public/locales/`.

```powershell
# Create locales directory if it doesn't exist
New-Item -ItemType Directory -Path "clm-app\public\locales" -Force | Out-Null

# Copy locale files
Copy-Item -Path "AiStudio-CLMS2\locales\en.json" -Destination "clm-app\public\locales\en.json" -Force
Copy-Item -Path "AiStudio-CLMS2\locales\ar.json" -Destination "clm-app\public\locales\ar.json" -Force
```

Or copy all JSON files:
```powershell
Copy-Item -Path "AiStudio-CLMS2\locales\*.json" -Destination "clm-app\public\locales\" -Force
```

### Step 2: Verify Files

```powershell
# Check files exist
Test-Path "clm-app\public\locales\en.json"  # Should return: True
Test-Path "clm-app\public\locales\ar.json"  # Should return: True

# List files
Get-ChildItem "clm-app\public\locales"
```

### Step 3: Test Access

Try accessing the files directly in browser:
- `http://litigation.local/locales/en.json` - Should show JSON content
- `http://litigation.local/locales/ar.json` - Should show JSON content

---

## Complete Deployment Script

Here's a complete script to deploy the React SPA:

```powershell
# Build React app
Set-Location "AiStudio-CLMS2"
npm run build
Set-Location ".."

# Copy build files
Copy-Item -Path "AiStudio-CLMS2\dist\*" -Destination "clm-app\public\" -Recurse -Force

# Copy locale files
New-Item -ItemType Directory -Path "clm-app\public\locales" -Force | Out-Null
Copy-Item -Path "AiStudio-CLMS2\locales\*.json" -Destination "clm-app\public\locales\" -Force

# Verify
Write-Host "Verifying deployment..."
Write-Host "index.html: $(Test-Path 'clm-app\public\index.html')"
Write-Host "assets: $(Test-Path 'clm-app\public\assets\index-ZFxIBYC0.js')"
Write-Host "en.json: $(Test-Path 'clm-app\public\locales\en.json')"
Write-Host "ar.json: $(Test-Path 'clm-app\public\locales\ar.json')"
```

---

## Files Structure After Deployment

```
clm-app/public/
├── index.html              ✅ React SPA entry point
├── assets/
│   └── index-ZFxIBYC0.js   ✅ React app bundle
└── locales/                ✅ Translation files
    ├── en.json             ✅ English translations
    └── ar.json             ✅ Arabic translations
```

---

## Additional Notes

### Tailwind CDN Warning

The console shows:
```
cdn.tailwindcss.com should not be used in production
```

This is a warning, not an error. The app will work, but for production you should:
1. Build Tailwind CSS during the build process
2. Include compiled CSS in the build
3. Remove the CDN script from `index.html`

**Current Status**: App works with CDN (acceptable for now, but should be fixed for production)

---

## Testing

After copying locale files:

1. **Hard refresh browser**: `Ctrl+Shift+R` or `Ctrl+F5`
2. **Check console**: Should see no 404 errors for locale files
3. **Check app**: Should see React app loading (not blank screen)

---

**Status**: ✅ Fixed - Locale files copied and accessible

