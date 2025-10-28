# Lawyer View & Client Edit Form Fixes

**Date:** January 11, 2025  
**Branch:** `mod/clients-model`  
**Commits:** `33392e7`, `10c74c1`

## Issues Fixed

### 1. Lawyer View Case Relationship Error

**Problem:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'cases.lawyer_id' in 'where clause'
```

**Root Cause:**
The `Lawyer` model was using a default `hasMany(CaseModel::class)` relationship that expected a `lawyer_id` column in the `cases` table. However, the actual `cases` table structure uses `lawyer_a` and `lawyer_b` columns to support multiple lawyers per case.

**Solution:**
1. **Updated Lawyer Model Relationships:**
   ```php
   // Cases where this lawyer is lawyer A
   public function casesAsLawyerA()
   {
       return $this->hasMany(CaseModel::class, 'lawyer_a', 'id');
   }

   // Cases where this lawyer is lawyer B
   public function casesAsLawyerB()
   {
       return $this->hasMany(CaseModel::class, 'lawyer_b', 'id');
   }

   // Get all cases (lawyer A or lawyer B) - returns collection
   public function getAllCases()
   {
       return CaseModel::where('lawyer_a', $this->id)
           ->orWhere('lawyer_b', $this->id)
           ->get();
   }
   ```

2. **Updated LawyersController:**
   ```php
   public function show(Lawyer $lawyer)
   {
       $this->authorize('view', $lawyer);
       
       // Load relationships
       $lawyer->load(['casesAsLawyerA', 'casesAsLawyerB', 'adminTasks']);
       
       // Get all cases (merge both relationships)
       $cases = $lawyer->getAllCases();
       
       return view('lawyers.show', compact('lawyer', 'cases'));
   }
   ```

3. **Updated View Template:**
   ```blade
   @forelse($cases->take(10) as $case)
   <div class="mb-2">
       <a href="{{ route('cases.show', $case) }}">
           <strong>{{ $case->matter_name_ar ?? $case->matter_name_en }}</strong>
       </a> - {{ $case->matter_status }}
   </div>
   @empty
   <p>{{ __('app.no_assigned_cases_found') }}</p>
   @endforelse
   ```

**Files Modified:**
- `clm-app/app/Models/Lawyer.php`
- `clm-app/app/Http/Controllers/LawyersController.php`
- `clm-app/resources/views/lawyers/show.blade.php`

### 2. Incomplete Client Edit Form

**Problem:**
The client edit form was showing only basic name fields (Arabic and English names) instead of the complete set of fields available in the create and view forms. This included missing:
- Cash or Pro Bono dropdown
- Status dropdown
- Client start/end dates
- Contact lawyer selection
- Logo upload with current logo preview
- Power of Attorney Location dropdown
- Documents Location dropdown

**Root Cause:**
1. The `ClientsController::edit()` method was only passing the `$client` object to the view
2. The `clients/edit.blade.php` template was minimal and didn't include all form fields
3. Missing dropdown options data needed for the form

**Solution:**

1. **Enhanced Controller Method:**
   ```php
   public function edit(Client $client)
   {
       $this->authorize('edit', $client);
       
       // Load dropdown options
       $cashOrProbonoOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
           $query->where('key', 'client.cash_or_probono');
       })->orderBy('id')->get();

       $statusOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
           $query->where('key', 'client.status');
       })->orderBy('id')->get();

       $powerOfAttorneyLocationOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
           $query->where('key', 'client.power_of_attorney_location');
       })->orderBy('id')->get();

       $documentsLocationOptions = \App\Models\OptionValue::whereHas('optionSet', function ($query) {
           $query->where('key', 'client.documents_location');
       })->orderBy('id')->get();

       $lawyers = \App\Models\Lawyer::select('id', 'lawyer_name_ar', 'lawyer_name_en')
           ->orderBy('lawyer_name_ar')
           ->get();

       return view('clients.edit', compact(
           'client',
           'cashOrProbonoOptions',
           'statusOptions',
           'powerOfAttorneyLocationOptions',
           'documentsLocationOptions',
           'lawyers'
       ));
   }
   ```

2. **Completely Rewrote Edit Form:**
   - Matched the structure of the create form
   - Added all missing fields with proper validation
   - Pre-populated all fields with existing client data
   - Added current logo preview functionality
   - Included proper error handling and validation messages

3. **Added Missing Translations:**
   ```php
   // English
   'update_client' => 'Update Client',
   'current_logo' => 'Current Logo',

   // Arabic
   'update_client' => 'تحديث العميل',
   'current_logo' => 'الشعار الحالي',
   ```

**Files Modified:**
- `clm-app/app/Http/Controllers/ClientsController.php`
- `clm-app/resources/views/clients/edit.blade.php`
- `clm-app/resources/lang/en/app.php`
- `clm-app/resources/lang/ar/app.php`

## Testing Results

### Lawyer View Fix
- ✅ Lawyer view now loads without database errors
- ✅ Cases are properly displayed for lawyers in either lawyer_a or lawyer_b role
- ✅ Case count and listing work correctly

### Client Edit Form Fix
- ✅ All form fields now visible and functional
- ✅ Dropdown options properly populated
- ✅ Form pre-populated with existing client data
- ✅ Current logo preview working
- ✅ Validation and error handling working
- ✅ Form submission working correctly

## Impact

### User Experience
- **Lawyer View:** Users can now view lawyer details and their associated cases without errors
- **Client Edit:** Users can now edit all client fields through a complete form interface

### Code Quality
- **Database Relationships:** Properly aligned with actual database schema
- **Form Consistency:** Edit form now matches create and view form functionality
- **Internationalization:** All new features properly localized

### Maintenance
- **Documentation:** Both fixes are well-documented for future reference
- **Code Structure:** Follows Laravel best practices for relationships and form handling
- **Error Handling:** Proper validation and error messages throughout

## Related Files
- Database Schema: `cases` table structure with `lawyer_a` and `lawyer_b` columns
- Client Model: Option set relationships and managed dropdowns
- Translation Files: Bilingual support for all new features
