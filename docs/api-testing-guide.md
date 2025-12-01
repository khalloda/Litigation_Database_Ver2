# API Testing Guide

> **Date**: 2025-11-15  
> **Purpose**: Guide for testing Laravel API endpoints

---

## Prerequisites

✅ Environment variables configured:
- `GEMINI_API_KEY` - Your Gemini API key
- `SANCTUM_STATEFUL_DOMAINS` - Your domain(s) (should include `litigation.local`)

**Base URL**: `http://litigation.local` (or `https://litigation.local` if using SSL)

---

## Quick Test Checklist

### 1. Verify Laravel Server is Running
```bash
# Access via: http://litigation.local
# Make sure your hosts file includes: 127.0.0.1 litigation.local
# Or configure your web server (Apache/Nginx) to serve the app
```

### 2. Test Authentication Endpoints

#### Login
```bash
curl -X POST http://litigation.local/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"your-email@example.com","password":"your-password"}' \
  -c cookies.txt \
  -v
```

**Expected Response** (200):
```json
{
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "roles": [...]
  },
  "message": "Login successful"
}
```

#### Get Current User
```bash
curl -X GET http://litigation.local/api/user \
  -H "Accept: application/json" \
  -b cookies.txt \
  -v
```

#### Logout
```bash
curl -X POST http://litigation.local/api/logout \
  -H "Accept: application/json" \
  -b cookies.txt \
  -v
```

### 3. Test Resource Endpoints

#### List Cases
```bash
curl -X GET "http://litigation.local/api/cases?per_page=10" \
  -H "Accept: application/json" \
  -b cookies.txt
```

#### Create Case
```bash
curl -X POST http://litigation.local/api/cases \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{
    "case_name_en": "Test Case",
    "case_name_ar": "قضية تجريبية",
    "client_id": 1
  }'
```

#### Get Single Case
```bash
curl -X GET http://litigation.local/api/cases/1 \
  -H "Accept: application/json" \
  -b cookies.txt
```

### 4. Test Options Endpoint
```bash
curl -X GET http://litigation.local/api/options/api/lawyer.title \
  -H "Accept: application/json" \
  -b cookies.txt
```

### 5. Test AI Endpoints

#### Generate Case Summary
```bash
curl -X POST http://litigation.local/api/ai/case-summary \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{
    "case_id": 1,
    "language": "en"
  }'
```

#### Analyze Document
```bash
curl -X POST http://litigation.local/api/ai/analyze-document \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{
    "text": "Sample legal document text...",
    "language": "en"
  }'
```

---

## Testing with Browser DevTools

### 1. Open Browser Console
- Navigate to your Laravel app: `http://litigation.local`
- Open DevTools (F12)
- Go to Console tab

### 2. Test Login
```javascript
fetch('/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  credentials: 'include', // Important for cookies
  body: JSON.stringify({
    email: 'your-email@example.com',
    password: 'your-password'
  })
})
.then(res => res.json())
.then(data => console.log('Login:', data))
.catch(err => console.error('Error:', err));
```

### 3. Test API Call
```javascript
fetch('/api/cases', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
  },
  credentials: 'include', // Important for cookies
})
.then(res => res.json())
.then(data => console.log('Cases:', data))
.catch(err => console.error('Error:', err));
```

---

## Common Issues & Solutions

### Issue: 401 Unauthorized
**Solution**: 
- Make sure you're logged in first
- Check that cookies are being sent (`credentials: 'include'` in fetch)
- Verify `SANCTUM_STATEFUL_DOMAINS` includes `litigation.local`
- Ensure you're accessing via `http://litigation.local` (not localhost)

### Issue: 419 CSRF Token Mismatch
**Solution**:
- Ensure `EnsureFrontendRequestsAreStateful` middleware is enabled (✅ Done)
- Make sure request includes `Accept: application/json` header
- Verify `litigation.local` is in `SANCTUM_STATEFUL_DOMAINS`
- Access the app via `http://litigation.local` (not localhost or IP)

### Issue: 404 Not Found
**Solution**:
- Check route exists: `php artisan route:list | grep api`
- Verify URL is correct (should start with `/api/`)

### Issue: 403 Forbidden
**Solution**:
- Check user has required permissions
- Verify authorization policies exist for the model

### Issue: Gemini API Error
**Solution**:
- Verify `GEMINI_API_KEY` is set in `.env`
- Check API key is valid
- Review Laravel logs: `tail -f storage/logs/laravel.log`

---

## Verify Configuration

### Check Sanctum Configuration
```bash
php artisan tinker
>>> config('sanctum.stateful')
```

### Check Gemini Config
```bash
php artisan tinker
>>> config('services.gemini.api_key')
```

### List All API Routes
```bash
php artisan route:list --path=api
```

---

## Next Steps

1. ✅ **Test Authentication** - Verify login/logout works
2. ✅ **Test CRUD Operations** - Test creating/reading/updating/deleting resources
3. ✅ **Test File Uploads** - Test document upload endpoint
4. ✅ **Test AI Endpoints** - Verify Gemini integration works
5. ✅ **Deploy React Build** - Copy `AiStudio-CLMS2/dist/*` to `clm-app/public/`
6. ✅ **Test SPA Routing** - Verify React Router works with Laravel fallback

---

## Production Checklist

Before deploying to production:

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Add production domain to `SANCTUM_STATEFUL_DOMAINS`
- [ ] Verify `GEMINI_API_KEY` is set
- [ ] Test all API endpoints
- [ ] Deploy React build to `public/` directory
- [ ] Test SPA routing (direct URL access)
- [ ] Verify file uploads work
- [ ] Check CORS settings if needed

---

**Status**: Ready for testing! 🚀
