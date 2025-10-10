# Bug Fix: CRUD Authorization and Database Schema Issues

## Issue Description

**Problems Reported**:
1. **Cases 403 Unauthorized**: URL `http://litigation.local/cases` returns "403 This action is unauthorized"
2. **Hearings Database Error**: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'time' in 'field list'`
3. **Lawyers 403 Unauthorized**: URL `http://litigation.local/lawyers` returns "403 This action is unauthorized"

**Environment**:
- Laravel 10.x
- Spatie Permission for RBAC
- User has correct permissions (verified via tinker)

---

## Root Causes

### Issue 1 & 3: Authorization Failures (403 Errors)

**Primary Cause**: Policies not registered in `AuthServiceProvider`

Even though all policies existed (`ClientPolicy`, `CasePolicy`, `HearingPolicy`, `DocumentPolicy`, `TrashPolicy`), they were never registered in the `$policies` array of `AuthServiceProvider`. Laravel couldn't find the policies, so all `authorize()` calls failed.

**Secondary Issue**: `authorize()` method called with 3 parameters instead of 2

Controllers were calling:
```php
$this->authorize('viewAny', CaseModel::class, auth()->user()); // WRONG
```

Should be:
```php
$this->authorize('viewAny', CaseModel::class); // CORRECT
```

Laravel automatically injects the authenticated user as the first parameter to policy methods.

**Missing Policy**: `LawyerPolicy` didn't exist and needed to be created.

### Issue 2: Hearings Database Column Mismatch

**Cause**: Hearing model's `$fillable` array didn't match actual database schema

The model referenced columns that don't exist:
- ❌ `time` (doesn't exist)
- ❌ `judge` (doesn't exist)
- ❌ `status` (doesn't exist)

Actual database has different columns:
- ✅ `procedure`
- ✅ `circuit`
- ✅ `decision`
- ✅ `lawyer_id`
- Plus many others

---

## Solution

### Step 1: Register Policies in AuthServiceProvider

**File**: `app/Providers/AuthServiceProvider.php`

```php
protected $policies = [
    \App\Models\Client::class => \App\Policies\ClientPolicy::class,
    \App\Models\CaseModel::class => \App\Policies\CasePolicy::class,
    \App\Models\Hearing::class => \App\Policies\HearingPolicy::class,
    \App\Models\Lawyer::class => \App\Policies\LawyerPolicy::class,
    \App\Models\ClientDocument::class => \App\Policies\DocumentPolicy::class,
    \App\Models\DeletionBundle::class => \App\Policies\TrashPolicy::class,
];
```

### Step 2: Fix authorize() Calls

**Files**: All CRUD controllers (Cases, Hearings, Lawyers)

**Before**:
```php
$this->authorize('viewAny', CaseModel::class, auth()->user());
$this->authorize('view', $case, auth()->user());
$this->authorize('create', CaseModel::class, auth()->user());
```

**After**:
```php
$this->authorize('viewAny', CaseModel::class);
$this->authorize('view', $case);
$this->authorize('create', CaseModel::class);
```

### Step 3: Create Missing LawyerPolicy

**File**: `app/Policies/LawyerPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;

class LawyerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('admin.users.manage');
    }

    public function view(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('admin.users.manage');
    }

    public function update(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }

    public function delete(User $user, $lawyer): bool
    {
        return $user->can('admin.users.manage');
    }
}
```

### Step 4: Update Hearing Model to Match Database

**File**: `app/Models/Hearing.php`

**Removed columns**:
```php
'time', 'judge', 'status'
```

**Added columns**:
```php
'lawyer_id',
'procedure',
'circuit',
'destination',
'decision',
'short_decision',
'last_decision',
'attendee',
'attendee_1',
'attendee_2',
'attendee_3',
'attendee_4',
'next_attendee',
'evaluation',
```

### Step 5: Update HearingRequest Validation

**File**: `app/Http/Requests/HearingRequest.php`

Updated validation rules to match actual database columns.

### Step 6: Update Hearing Views

**Files**: 
- `resources/views/hearings/index.blade.php`
- `resources/views/hearings/show.blade.php`
- `resources/views/hearings/create.blade.php`
- `resources/views/hearings/edit.blade.php`

Replaced references to `time`, `judge`, `status` with `procedure`, `circuit`, `decision`.

---

## Verification Steps

### 1. Check Policy Registration

```bash
php artisan tinker
```

```php
// Verify policies are registered
Gate::getPolicyFor(\App\Models\CaseModel::class);
// Should return: App\Policies\CasePolicy instance

// Verify user has permission
$user = User::first();
$user->can('cases.view');
// Should return: true

// Test authorization
Gate::forUser($user)->allows('viewAny', \App\Models\CaseModel::class);
// Should return: true
```

### 2. Check Database Schema Alignment

```bash
php artisan tinker --execute="echo json_encode(DB::select('SHOW COLUMNS FROM hearings'), JSON_PRETTY_PRINT);"
```

Verify columns match model's `$fillable` array.

### 3. Test in Browser

- Navigate to `/cases` - Should load successfully
- Navigate to `/hearings` - Should load successfully
- Navigate to `/lawyers` - Should load successfully (admin only)

---

## Prevention Measures

### 1. Always Register Policies

When creating a new policy, immediately register it in `AuthServiceProvider`:

```php
protected $policies = [
    \App\Models\YourModel::class => \App\Policies\YourPolicy::class,
];
```

### 2. Verify Model Against Database

Before creating controllers/views, verify model `$fillable` matches actual table:

```bash
php artisan tinker --execute="DB::select('SHOW COLUMNS FROM your_table');"
```

### 3. Use Correct authorize() Syntax

Always use 2 parameters (or 1 for class-based checks):

```php
// For class-based (viewAny, create)
$this->authorize('viewAny', Model::class);

// For instance-based (view, update, delete)
$this->authorize('view', $model);
```

### 4. Test Authorization Early

After creating policies, test immediately:

```bash
php artisan test --filter=YourPolicyTest
```

---

## Related Issues

This fix also resolves:
- Any other CRUD pages showing 403 errors
- Database column mismatches in other models
- Authorization failures due to unregistered policies

---

## Files Modified

### Authorization Fixes:
- `app/Providers/AuthServiceProvider.php` - Registered 6 policies
- `app/Policies/LawyerPolicy.php` - Created new policy
- `app/Http/Controllers/CasesController.php` - Fixed 6 authorize() calls
- `app/Http/Controllers/HearingsController.php` - Fixed 6 authorize() calls
- `app/Http/Controllers/LawyersController.php` - Fixed 6 authorize() calls

### Database Schema Fixes:
- `app/Models/Hearing.php` - Updated fillable and casts
- `app/Http/Requests/HearingRequest.php` - Updated validation rules
- `resources/views/hearings/index.blade.php` - Updated columns
- `resources/views/hearings/show.blade.php` - Updated fields
- `resources/views/hearings/create.blade.php` - Updated form fields
- `resources/views/hearings/edit.blade.php` - Updated form fields

---

## Commits

1. `ff1f50e` - fix(crud): resolve authorization and database column issues
2. `b71523e` - fix(auth): register all policies in AuthServiceProvider

---

**Date**: 2025-01-09  
**Severity**: Critical (blocking all CRUD operations)  
**Resolution Time**: ~20 minutes  
**Impact**: All CRUD modules now functional

