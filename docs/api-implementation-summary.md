# Laravel API Implementation Summary

> **Date**: 2025-11-15  
> **Status**: ✅ Complete  
> **Purpose**: Implement RESTful API endpoints for React SPA frontend

---

## Overview

All API endpoints have been implemented to support the React SPA frontend. The API uses Laravel Sanctum for authentication with session-based cookies (SPA mode).

---

## API Controllers Created

### Authentication
- **`App\Http\Controllers\Api\AuthController`**
  - `POST /api/login` - Login user
  - `POST /api/logout` - Logout user
  - `GET /api/user` - Get current authenticated user

### Core Resources
- **`App\Http\Controllers\Api\CaseController`**
  - Full CRUD operations for cases
  - Filtering by status, client_id, partner_id, opponent_id, search

- **`App\Http\Controllers\Api\ClientController`**
  - Full CRUD operations for clients
  - Filtering by search, status_id, client_id

- **`App\Http\Controllers\Api\OpponentController`**
  - Full CRUD operations for opponents
  - Search functionality

- **`App\Http\Controllers\Api\LawyerController`**
  - Full CRUD operations for lawyers
  - Search functionality
  - Includes title relationship

- **`App\Http\Controllers\Api\CourtController`**
  - Full CRUD operations for courts
  - Filtering by search, status (active/inactive)

- **`App\Http\Controllers\Api\HearingController`**
  - Full CRUD operations for hearings
  - Filtering by case_id, date range
  - Includes case and lawyer relationships

- **`App\Http\Controllers\Api\DocumentController`**
  - Full CRUD operations for documents
  - File upload support (max 10MB)
  - Filtering by client_id, matter_id, document_type, search

- **`App\Http\Controllers\Api\TaskController`**
  - Full CRUD operations for tasks (AdminTask)
  - Filtering by case_id, status, lawyer_id

### User Management
- **`App\Http\Controllers\Api\UserController`**
  - Full CRUD operations for users
  - Role assignment support
  - Password hashing

- **`App\Http\Controllers\Api\RoleController`**
  - Full CRUD operations for roles (Spatie Permission)
  - Permission assignment support

### Options Management
- **`App\Http\Controllers\Api\OptionController`**
  - `GET /api/options/api/{setKey}` - Get options by set key
  - Full CRUD for option sets
  - CRUD for option values

### AI Integration
- **`App\Http\Controllers\Api\AiController`**
  - `POST /api/ai/case-summary` - Generate case summary using Gemini
  - `POST /api/ai/analyze-document` - Analyze document using Gemini
  - Proxy for Gemini API (keeps API key on backend)

---

## API Routes

All routes are defined in `routes/api.php`:

```php
// Public routes
POST /api/login

// Protected routes (require auth:sanctum)
POST /api/logout
GET  /api/user

// Resource routes (RESTful)
GET    /api/{resource}           - List resources
POST   /api/{resource}           - Create resource
GET    /api/{resource}/{id}      - Show resource
PUT    /api/{resource}/{id}      - Update resource
DELETE /api/{resource}/{id}      - Delete resource

// Special routes
GET  /api/options/api/{setKey}    - Get options by set key
POST /api/options/{set}/values    - Create option value
PUT  /api/options/values/{id}     - Update option value
DELETE /api/options/values/{id}   - Delete option value
POST /api/ai/case-summary         - Generate case summary
POST /api/ai/analyze-document     - Analyze document
```

---

## Configuration

### Sanctum SPA Configuration

1. **Middleware Enabled**: `EnsureFrontendRequestsAreStateful` enabled in `app/Http/Kernel.php`
2. **Stateful Domains**: Configured in `config/sanctum.php` (includes `litigation.local` by default)
3. **CSRF Protection**: Handled automatically by Sanctum for same-origin requests
4. **Base URL**: `http://litigation.local` (configured via `SANCTUM_STATEFUL_DOMAINS` in `.env`)

### Services Configuration

Added Gemini API key configuration in `config/services.php`:
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
```

### SPA Fallback Route

Added catch-all route in `routes/web.php` to serve React SPA:
- Serves `public/index.html` for all non-API routes
- Allows React Router to handle client-side routing
- Serves static assets from `public/` directory

---

## Authentication Flow

1. **Login**: `POST /api/login` with email/password
   - Returns user data with roles/permissions
   - Sets session cookie automatically

2. **Authenticated Requests**: Include session cookie automatically
   - Sanctum middleware validates session
   - User available via `$request->user()`

3. **Logout**: `POST /api/logout`
   - Invalidates session
   - Regenerates CSRF token

---

## Authorization

All API controllers use Laravel's authorization policies:
- `$this->authorize('viewAny', Model::class)` - List
- `$this->authorize('view', $model)` - Show
- `$this->authorize('create', Model::class)` - Create
- `$this->authorize('update', $model)` - Update
- `$this->authorize('delete', $model)` - Delete

Policies must exist for each model (already implemented for web controllers).

---

## Response Format

All API responses follow consistent format:

**Success (200/201)**:
```json
{
  "data": {...},
  "message": "Operation successful"
}
```

**Paginated List**:
```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 25,
  "total": 100,
  ...
}
```

**Error (400/404/500)**:
```json
{
  "error": "Error message",
  "message": "Detailed error message"
}
```

---

## Environment Variables Required

Add to `.env`:
```env
GEMINI_API_KEY=your_gemini_api_key_here
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,127.0.0.1:8000,litigation.local
```

**Note**: The base URL is `http://litigation.local`. Make sure your hosts file includes:
```
127.0.0.1 litigation.local
```

---

## Testing

### Test Authentication
```bash
# Login
curl -X POST http://litigation.local/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' \
  -c cookies.txt

# Get current user
curl -X GET http://litigation.local/api/user \
  -b cookies.txt

# Logout
curl -X POST http://litigation.local/api/logout \
  -b cookies.txt
```

### Test Resource Endpoints
```bash
# List cases
curl -X GET http://litigation.local/api/cases \
  -b cookies.txt

# Create case
curl -X POST http://litigation.local/api/cases \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"case_name_en":"Test Case","case_name_ar":"قضية تجريبية"}'
```

---

## Files Created/Modified

### Created
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/CaseController.php`
- `app/Http/Controllers/Api/ClientController.php`
- `app/Http/Controllers/Api/OpponentController.php`
- `app/Http/Controllers/Api/LawyerController.php`
- `app/Http/Controllers/Api/CourtController.php`
- `app/Http/Controllers/Api/HearingController.php`
- `app/Http/Controllers/Api/DocumentController.php`
- `app/Http/Controllers/Api/TaskController.php`
- `app/Http/Controllers/Api/UserController.php`
- `app/Http/Controllers/Api/RoleController.php`
- `app/Http/Controllers/Api/OptionController.php`
- `app/Http/Controllers/Api/AiController.php`

### Modified
- `routes/api.php` - Complete rewrite with all API routes
- `routes/web.php` - Added SPA fallback route
- `app/Http/Kernel.php` - Enabled Sanctum SPA middleware
- `config/services.php` - Added Gemini API key config

---

## Next Steps

1. **Test API endpoints** - Verify all endpoints work correctly
2. **Set GEMINI_API_KEY** - Add to `.env` file
3. **Configure SANCTUM_STATEFUL_DOMAINS** - Add production domain
4. **Deploy React build** - Copy `dist/` contents to `public/`
5. **Test SPA routing** - Verify React Router works with Laravel fallback

---

## Notes

- All API controllers extend `App\Http\Controllers\Controller`
- Authorization policies are reused from web controllers
- File uploads stored in `storage/app/public/documents/`
- Gemini API calls are proxied through backend (API key never exposed)
- Session-based authentication for same-origin SPA deployment

---

**Status**: ✅ All API endpoints implemented and ready for testing

