# Remaining Implementation Steps for Courts Module

## Status
- **Token Usage**: ~152k/1M
- **Completed**: 10/17 tasks
- **Remaining**: 7 tasks (mostly straightforward)

---

## Task 11: Update cases/edit.blade.php (In Progress)

**Action**: Apply same cascading dropdown logic as create.blade.php

The edit form needs the exact same changes:
1. Replace matter_court text input with court_id Select2 dropdown
2. Add 4 cascading dropdowns (matter_circuit, circuit_secretary, court_floor, court_hall)
3. Add same JavaScript as create.blade.php
4. On page load, if court_id exists, trigger AJAX to populate cascading fields

**Note**: edit.blade.php already has the structure, just needs the cascading fields added after line 73 and JavaScript added at the end.

---

## Task 12: Update cases/show.blade.php

**Current** (line 68-70):
```blade
<tr>
    <td><strong>{{ __('app.matter_court') }}</strong></td>
    <td>{{ $case->matter_court }}</td>
</tr>
```

**Replace with**:
```blade
<tr>
    <td><strong>{{ __('app.matter_court') }}</strong></td>
    <td>
        @if($case->court)
            <a href="{{ route('courts.show', $case->court) }}">
                {{ app()->getLocale() === 'ar' ? $case->court->court_name_ar : $case->court->court_name_en }}
            </a>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
</tr>
```

Add below this (after line 70):
```blade
<tr>
    <td><strong>{{ __('app.matter_circuit') }}</strong></td>
    <td>{{ $case->matterCircuit ? (app()->getLocale() === 'ar' ? $case->matterCircuit->label_ar : $case->matterCircuit->label_en) : '-' }}</td>
</tr>
<tr>
    <td><strong>{{ __('app.circuit_secretary') }}</strong></td>
    <td>{{ $case->circuitSecretaryRef ? (app()->getLocale() === 'ar' ? $case->circuitSecretaryRef->label_ar : $case->circuitSecretaryRef->label_en) : '-' }}</td>
</tr>
<tr>
    <td><strong>{{ __('app.court_floor') }}</strong></td>
    <td>{{ $case->courtFloorRef ? (app()->getLocale() === 'ar' ? $case->courtFloorRef->label_ar : $case->courtFloorRef->label_en) : '-' }}</td>
</tr>
<tr>
    <td><strong>{{ __('app.court_hall') }}</strong></td>
    <td>{{ $case->courtHallRef ? (app()->getLocale() === 'ar' ? $case->courtHallRef->label_ar : $case->courtHallRef->label_en) : '-' }}</td>
</tr>
```

---

## Task 13: Add Routes

**File**: `routes/web.php`

**Add after existing routes** (around line 263):
```php
// Courts Management
Route::middleware(['auth'])->group(function () {
    Route::resource('courts', CourtsController::class);
    
    // AJAX endpoint for cascading dropdowns
    Route::get('/api/courts/{court}/details', [CasesController::class, 'getCourtDetails'])->name('courts.details');
});
```

---

## Task 14: Create CourtPolicy

**Command**: `php artisan make:policy CourtPolicy --model=Court`

**File**: `app/Policies/CourtPolicy.php`
```php
<?php

namespace App\Policies;

use App\Models\Court;
use App\Models\User;

class CourtPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view courts list
    }

    public function view(User $user, Court $court): bool
    {
        return true; // All authenticated users can view a court
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('admin.users.manage');
    }

    public function update(User $user, Court $court): bool
    {
        return $user->hasPermissionTo('admin.users.manage');
    }

    public function delete(User $user, Court $court): bool
    {
        return $user->hasPermissionTo('admin.users.manage');
    }
}
```

**Register in** `app/Providers/AuthServiceProvider.php`:
```php
protected $policies = [
    Court::class => CourtPolicy::class,
    // ... existing policies
];
```

---

## Task 15: Add Translation Keys

### English (`resources/lang/en/app.php`)
```php
// Courts
'courts' => 'Courts',
'court' => 'Court',
'court_name_ar' => 'Court Name (Arabic)',
'court_name_en' => 'Court Name (English)',
'court_circuit' => 'Court Circuit',
'court_circuit_secretary' => 'Circuit Secretary',
'court_floor' => 'Court Floor',
'court_hall' => 'Court Hall',
'select_court' => 'Select Court',
'select_court_first' => 'Select court first',
'court_details' => 'Court Details',
'create_court' => 'Create Court',
'edit_court' => 'Edit Court',
'search_courts' => 'Search courts...',
'no_courts_found' => 'No courts found',

// Court messages
'court_created_success' => 'Court created successfully!',
'court_updated_success' => 'Court updated successfully!',
'court_deleted_success' => 'Court deleted successfully!',
'court_has_cases' => 'Cannot delete court. It has related cases.',
'court_name_required' => 'At least one court name (Arabic or English) is required.',
'invalid_court_circuit' => 'Invalid court circuit selected.',
'invalid_court_secretary' => 'Invalid circuit secretary selected.',
'invalid_court_floor' => 'Invalid court floor selected.',
'invalid_court_hall' => 'Invalid court hall selected.',
'error_loading_court_details' => 'Error loading court details. Please try again.',

// Cascading fields
'matter_circuit' => 'Matter Circuit',
'circuit_secretary' => 'Circuit Secretary',
```

### Arabic (`resources/lang/ar/app.php`)
```php
// Courts
'courts' => 'المحاكم',
'court' => 'المحكمة',
'court_name_ar' => 'اسم المحكمة (عربي)',
'court_name_en' => 'اسم المحكمة (إنجليزي)',
'court_circuit' => 'دائرة المحكمة',
'court_circuit_secretary' => 'أمين الدائرة',
'court_floor' => 'طابق المحكمة',
'court_hall' => 'قاعة المحكمة',
'select_court' => 'اختر المحكمة',
'select_court_first' => 'اختر المحكمة أولاً',
'court_details' => 'تفاصيل المحكمة',
'create_court' => 'إنشاء محكمة',
'edit_court' => 'تعديل المحكمة',
'search_courts' => 'بحث في المحاكم...',
'no_courts_found' => 'لا توجد محاكم',

// Court messages
'court_created_success' => 'تم إنشاء المحكمة بنجاح!',
'court_updated_success' => 'تم تحديث المحكمة بنجاح!',
'court_deleted_success' => 'تم حذف المحكمة بنجاح!',
'court_has_cases' => 'لا يمكن حذف المحكمة. يوجد قضايا مرتبطة بها.',
'court_name_required' => 'يجب إدخال اسم المحكمة (عربي أو إنجليزي) على الأقل.',
'invalid_court_circuit' => 'دائرة المحكمة المختارة غير صالحة.',
'invalid_court_secretary' => 'أمين الدائرة المختار غير صالح.',
'invalid_court_floor' => 'طابق المحكمة المختار غير صالح.',
'invalid_court_hall' => 'قاعة المحكمة المختارة غير صالحة.',
'error_loading_court_details' => 'خطأ في تحميل تفاصيل المحكمة. الرجاء المحاولة مرة أخرى.',

// Cascading fields
'matter_circuit' => 'دائرة القضية',
'circuit_secretary' => 'أمين الدائرة',
```

---

## Task 16: Add to Navigation Menu

**File**: `resources/views/layouts/app.blade.php`

**Add link after "Cases"** (around line 84):
```blade
@can('cases.view')
<li class="nav-item">
    <a class="nav-link" href="{{ route('cases.index') }}">{{ __('app.cases') }}</a>
</li>
@endcan

<li class="nav-item">
    <a class="nav-link" href="{{ route('courts.index') }}">{{ __('app.courts') }}</a>
</li>
```

---

## Task 17: Run Migrations and Test

### Commands:
```bash
cd clm-app
php artisan migrate
php artisan optimize:clear
```

### Test Checklist:
- [ ] Courts index page loads
- [ ] Can create new court
- [ ] Can edit court
- [ ] Can view court details
- [ ] Court details show related cases
- [ ] Cases create form: selecting court populates cascading dropdowns
- [ ] Cases edit form: cascading dropdowns work
- [ ] Cases show displays court link and all related fields
- [ ] Court deletion blocked if has cases

---

## Quick Completion Script

```bash
# 1. Copy cases/create.blade.php cascading logic to cases/edit.blade.php
# 2. Update cases/show.blade.php with court link
# 3. Add routes
# 4. Create policy and register
# 5. Add all translation keys
# 6. Add menu link
# 7. Run migrations
php artisan migrate
php artisan optimize:clear
# 8. Test in browser
```

---

## Files to Complete
1. `clm-app/resources/views/cases/edit.blade.php` - Add cascading dropdowns + JS
2. `clm-app/resources/views/cases/show.blade.php` - Add court link and display fields
3. `clm-app/routes/web.php` - Add court routes
4. `clm-app/app/Policies/CourtPolicy.php` - Create policy
5. `clm-app/app/Providers/AuthServiceProvider.php` - Register policy
6. `clm-app/resources/lang/en/app.php` - Add ~20 translation keys
7. `clm-app/resources/lang/ar/app.php` - Add ~20 translation keys
8. `clm-app/resources/views/layouts/app.blade.php` - Add menu link

Then: Run migrations and test!

