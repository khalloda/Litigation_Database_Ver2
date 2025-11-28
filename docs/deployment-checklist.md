# Deployment Checklist - React SPA + Laravel API

> **Base URL**: `http://litigation.local`  
> **Date**: 2025-11-15

---

## Pre-Deployment Checklist

### Environment Configuration
- [x] `GEMINI_API_KEY` added to `.env`
- [x] `SANCTUM_STATEFUL_DOMAINS` configured (includes `litigation.local`)
- [ ] `APP_ENV=production` (when deploying to production)
- [ ] `APP_DEBUG=false` (when deploying to production)
- [ ] Database credentials configured
- [ ] Mail configuration (if needed)

### Server Configuration
- [ ] Web server (Apache/Nginx) configured for `litigation.local`
- [ ] Virtual host points to `clm-app/public` directory
- [ ] PHP version >= 8.1
- [ ] Required PHP extensions installed
- [ ] `.htaccess` or Nginx config allows SPA routing

### Laravel Setup
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `php artisan migrate` (if needed)
- [ ] Set proper file permissions (`storage/` and `bootstrap/cache/` writable)

### React Build Deployment
- [ ] Build React app: `cd AiStudio-CLMS2 && npm run build`
- [ ] Copy build files to Laravel public:
  ```powershell
  Copy-Item -Path "AiStudio-CLMS2\dist\*" -Destination "clm-app\public\" -Recurse -Force
  ```
- [ ] Copy locale files (required for i18n):
  ```powershell
  New-Item -ItemType Directory -Path "clm-app\public\locales" -Force | Out-Null
  Copy-Item -Path "AiStudio-CLMS2\locales\*.json" -Destination "clm-app\public\locales\" -Force
  ```
- [ ] Verify `index.html` exists in `clm-app/public/`
- [ ] Verify assets folder exists in `clm-app/public/assets/`
- [ ] Verify locale files exist: `clm-app/public/locales/en.json` and `ar.json`

---

## Post-Deployment Testing

### API Endpoints
- [ ] Test login: `POST http://litigation.local/api/login`
- [ ] Test authenticated endpoint: `GET http://litigation.local/api/user`
- [ ] Test CRUD operations (cases, clients, etc.)
- [ ] Test file uploads
- [ ] Test AI endpoints (Gemini)

### SPA Functionality
- [ ] React app loads at `http://litigation.local`
- [ ] Login page displays correctly
- [ ] After login, dashboard loads
- [ ] Navigation works (sidebar links)
- [ ] Direct URL access works (e.g., `http://litigation.local/cases/1`)
- [ ] Browser back/forward buttons work
- [ ] 404 handling works (redirects to home)

### Browser Compatibility
- [ ] Test in Chrome
- [ ] Test in Firefox
- [ ] Test in Edge
- [ ] Test in Safari (if applicable)
- [ ] Test RTL layout (Arabic)

---

## Troubleshooting

### React App Not Loading
- Check `index.html` exists in `public/`
- Check assets are in `public/assets/`
- Check browser console for 404 errors
- Verify web server serves `index.html` for all non-API routes

### API Calls Failing
- Check `SANCTUM_STATEFUL_DOMAINS` includes `litigation.local`
- Verify cookies are being sent (`credentials: 'include'` in fetch)
- Check Laravel logs: `storage/logs/laravel.log`
- Verify CORS settings if needed

### 419 CSRF Token Mismatch
- Ensure accessing via `http://litigation.local` (not localhost)
- Check `EnsureFrontendRequestsAreStateful` middleware enabled
- Verify `Accept: application/json` header in requests

### File Uploads Not Working
- Check `storage/app/public` directory exists
- Verify symlink: `php artisan storage:link`
- Check file permissions on `storage/` directory
- Verify max upload size in PHP config

---

## Production Considerations

### Security
- [ ] `APP_DEBUG=false`
- [ ] Strong database passwords
- [ ] HTTPS enabled (recommended)
- [ ] CSRF protection enabled (✅ Done)
- [ ] Rate limiting configured
- [ ] File upload validation strict

### Performance
- [ ] Laravel config cached
- [ ] Routes cached
- [ ] Views cached
- [ ] OPcache enabled (PHP)
- [ ] Gzip compression enabled (web server)

### Monitoring
- [ ] Error logging configured
- [ ] Log rotation set up
- [ ] Backup strategy in place
- [ ] Database backups scheduled

---

## Quick Reference

**Base URL**: `http://litigation.local`  
**API Base**: `http://litigation.local/api`  
**React App**: `http://litigation.local` (served from `public/index.html`)  
**Laravel Public**: `clm-app/public/`

**Important Files**:
- `.env` - Environment configuration
- `routes/api.php` - API routes
- `routes/web.php` - Web routes (includes SPA fallback)
- `public/index.html` - React SPA entry point

---

**Status**: Ready for deployment! 🚀

