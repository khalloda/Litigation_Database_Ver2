# Debugging Steps: Cases Not Displaying

> **Status**: Data loads (25 cases) but UI shows "No cases match the current filters"

---

## Current Situation

✅ **Working:**
- API calls return 200 OK
- Data loads: `{cases: 25, clients: 25, lawyers: 25}`
- No API errors

❌ **Not Working:**
- UI shows "No cases match the current filters"
- New debug logs not appearing (suggests cached build)

---

## Immediate Steps

### 1. Hard Refresh Browser
Clear browser cache to load the new build:
- **Chrome/Edge**: `Ctrl + Shift + R` or `Ctrl + F5`
- **Firefox**: `Ctrl + Shift + R`
- Or: Open DevTools → Right-click refresh button → "Empty Cache and Hard Reload"

### 2. Check Console for New Logs
After hard refresh, you should see:
```
First case structure: {...}
Total cases to filter: 25
Filtered cases count: X
```

If you see these logs, check:
- What does "First case structure" show?
- What is the "Filtered cases count"?

### 3. Check for Errors
Look for any red error messages in the console. Common issues:
- `Cannot read property 'client' of undefined`
- `opponents is not iterable`
- `Cannot read property 'id' of null`

### 4. Check Network Tab
Verify the API response structure:
1. Open Network tab
2. Click on `/api/cases` request
3. Go to "Response" tab
4. Check the structure of the `data` array

Expected structure:
```json
{
  "data": [
    {
      "id": 1,
      "case_name_en": "...",
      "case_name_ar": "...",
      "status": "...",
      "client": {
        "id": 1,
        "client_name_ar": "...",
        "client_name_en": "..."
      },
      "opponents": [...],
      "partner": {...}
    }
  ]
}
```

---

## If Debug Logs Appear

### Check "First case structure"
Look for:
- ✅ `client` object exists
- ✅ `opponents` is an array (not null/undefined)
- ✅ `status` field exists
- ✅ `case_name_en` and `case_name_ar` exist

### Check "Filtered cases count"
- If count is 0: Filtering logic is too restrictive
- If count is 25: Rendering issue (check CaseCard component)

---

## If Debug Logs Don't Appear

1. **Verify build was copied:**
   ```powershell
   Get-ChildItem clm-app\public\assets\*.js | Select-Object Name, LastWriteTime
   ```
   Should show recent timestamp

2. **Check if index.html references correct JS:**
   ```powershell
   Get-Content clm-app\public\index.html | Select-String "index-"
   ```
   Should match the JS file in `assets/`

3. **Rebuild and redeploy:**
   ```powershell
   cd AiStudio-CLMS2
   npm run build
   cd ..
   Copy-Item -Path "AiStudio-CLMS2\dist\*" -Destination "clm-app\public\" -Recurse -Force
   ```

---

## Common Issues & Fixes

### Issue: Cases filtered out due to missing client
**Fix**: Check API response - cases might not have `client` relationship loaded

### Issue: Opponents not an array
**Fix**: API might return `null` instead of `[]` - code handles this but check logs

### Issue: Status field mismatch
**Fix**: API returns `matter_status` but code checks `status` - transformation should handle this

---

## Next Steps

1. **Hard refresh browser** (Ctrl+Shift+R)
2. **Check console** for new debug logs
3. **Share console output** - especially:
   - "First case structure" log
   - "Filtered cases count" log
   - Any error messages
4. **Check Network tab** - Share the `/api/cases` response structure

---

**Status**: 🔍 Waiting for browser refresh and console logs

