<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root route - serve React SPA
Route::get('/', function () {
    return file_exists(public_path('index.html'))
        ? response()->file(public_path('index.html'))
        : view('welcome');
});

// DISABLED: Laravel Auth routes - React SPA handles authentication via API
// Auth::routes();

// DISABLED: Laravel home route - React SPA handles this
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Locale switch
Route::get('/locale/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])
    ->whereIn('locale', ['en', 'ar'])
    ->name('locale.switch');

// Basic CRUD stubs - Client Management
// Blade show route enabled for schema-driven all-fields view
Route::middleware(['auth'])->group(function () {
    Route::get('/blade/clients/{client}', [App\Http\Controllers\ClientsController::class, 'show'])->name('clients.show.blade');
});
// Other routes still handled by React SPA
/*
Route::middleware(['auth'])->group(function () {
    // List clients
    Route::get('/clients', [App\Http\Controllers\ClientsController::class, 'index'])->name('clients.index');

    // Create client - MUST be before {client} routes
    Route::get('/clients/create', [App\Http\Controllers\ClientsController::class, 'create'])->name('clients.create');
    Route::post('/clients', [App\Http\Controllers\ClientsController::class, 'store'])->name('clients.store');

    // Edit, Delete specific client
    Route::get('/clients/{client}/edit', [App\Http\Controllers\ClientsController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [App\Http\Controllers\ClientsController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [App\Http\Controllers\ClientsController::class, 'destroy'])->name('clients.destroy');
});
*/

// Admin Import Profiles & Choices
Route::middleware(['auth', 'permission:import.manage'])->prefix('admin/import')->name('admin.import.')->group(function () {
    Route::get('/profiles', [App\Http\Controllers\Admin\ImportProfilesController::class, 'index'])->name('profiles.index');
    Route::post('/profiles', [App\Http\Controllers\Admin\ImportProfilesController::class, 'store'])->name('profiles.store');
    Route::get('/profiles/{profile}/export', [App\Http\Controllers\Admin\ImportProfilesController::class, 'export'])->name('profiles.export');
    Route::post('/profiles/{profile}/import', [App\Http\Controllers\Admin\ImportProfilesController::class, 'import'])->name('profiles.import');

    Route::get('/profiles/{profile}/choices', [App\Http\Controllers\Admin\ImportChoicesController::class, 'index'])->name('choices.index');
    Route::post('/profiles/{profile}/choices', [App\Http\Controllers\Admin\ImportChoicesController::class, 'store'])->name('choices.store');
    Route::patch('/profiles/{profile}/choices/{choice}', [App\Http\Controllers\Admin\ImportChoicesController::class, 'update'])->name('choices.update');
    Route::delete('/profiles/{profile}/choices/{choice}', [App\Http\Controllers\Admin\ImportChoicesController::class, 'destroy'])->name('choices.destroy');
});
// Case Management
// Blade show route enabled for schema-driven all-fields view
Route::middleware(['auth', 'permission:cases.view'])->group(function () {
    Route::get('/blade/cases/{case}', [App\Http\Controllers\CasesController::class, 'show'])->name('cases.show.blade');
});
// Other routes still handled by React SPA
/*
Route::middleware(['auth', 'permission:cases.view'])->group(function () {
    Route::get('/cases', [App\Http\Controllers\CasesController::class, 'index'])->name('cases.index');
});
Route::middleware(['auth', 'permission:cases.create'])->group(function () {
    Route::get('/cases/create', [App\Http\Controllers\CasesController::class, 'create'])->name('cases.create');
    Route::post('/cases', [App\Http\Controllers\CasesController::class, 'store'])->name('cases.store');
});
Route::middleware(['auth', 'permission:cases.edit'])->group(function () {
    Route::get('/cases/{case}/edit', [App\Http\Controllers\CasesController::class, 'edit'])->name('cases.edit');
    Route::put('/cases/{case}', [App\Http\Controllers\CasesController::class, 'update'])->name('cases.update');
});
Route::middleware(['auth', 'permission:cases.delete'])->group(function () {
    Route::delete('/cases/{case}', [App\Http\Controllers\CasesController::class, 'destroy'])->name('cases.destroy');
});
*/

// Case Opponents Management
Route::middleware(['auth', 'permission:cases.opponents.view'])->group(function () {
    Route::get('/cases/{case}/opponents', [App\Http\Controllers\CaseOpponentController::class, 'index'])->name('case-opponents.index');
});

Route::middleware(['auth', 'permission:cases.opponents.edit'])->group(function () {
    Route::post('/cases/{case}/opponents', [App\Http\Controllers\CaseOpponentController::class, 'store'])->name('case-opponents.store');
    Route::delete('/cases/{case}/opponents', [App\Http\Controllers\CaseOpponentController::class, 'destroy'])->name('case-opponents.destroy');
    Route::post('/cases/{case}/opponents/set-primary', [App\Http\Controllers\CaseOpponentController::class, 'setPrimary'])->name('case-opponents.set-primary');
    Route::post('/cases/{case}/opponents/reorder', [App\Http\Controllers\CaseOpponentController::class, 'reorder'])->name('case-opponents.reorder');
});

// Opponent Search (for modal)
Route::middleware(['auth'])->group(function () {
    Route::get('/opponents/search', [App\Http\Controllers\OpponentsController::class, 'search'])->name('opponents.search');
});

// Fuzzy Matching Choice System
Route::get('/fuzzy-matching/choices', [App\Http\Controllers\FuzzyMatchingController::class, 'getChoices'])->name('fuzzy-matching.choices');
Route::post('/fuzzy-matching/apply-choice', [App\Http\Controllers\FuzzyMatchingController::class, 'applyChoice'])->name('fuzzy-matching.apply-choice');

// Hearing Management
// Blade show route enabled for schema-driven all-fields view
Route::middleware(['auth', 'permission:hearings.view'])->group(function () {
    Route::get('/blade/hearings/{hearing}', [App\Http\Controllers\HearingsController::class, 'show'])->name('hearings.show.blade');
});
// Other routes still handled by React SPA
/*
Route::middleware(['auth', 'permission:hearings.view'])->group(function () {
    Route::get('/hearings', [App\Http\Controllers\HearingsController::class, 'index'])->name('hearings.index');
});
Route::middleware(['auth', 'permission:hearings.create'])->group(function () {
    Route::get('/hearings/create', [App\Http\Controllers\HearingsController::class, 'create'])->name('hearings.create');
    Route::post('/hearings', [App\Http\Controllers\HearingsController::class, 'store'])->name('hearings.store');
});
Route::middleware(['auth', 'permission:hearings.edit'])->group(function () {
    Route::get('/hearings/{hearing}/edit', [App\Http\Controllers\HearingsController::class, 'edit'])->name('hearings.edit');
    Route::put('/hearings/{hearing}', [App\Http\Controllers\HearingsController::class, 'update'])->name('hearings.update');
});
Route::middleware(['auth', 'permission:hearings.delete'])->group(function () {
    Route::delete('/hearings/{hearing}', [App\Http\Controllers\HearingsController::class, 'destroy'])->name('hearings.destroy');
});
*/

// Lawyer Management (admin only)
// COMMENTED OUT: These routes are now handled by React SPA
// Uncomment if you need to access the old Blade views
/*
Route::middleware(['auth', 'permission:admin.users.manage'])->group(function () {
    Route::get('/lawyers', [App\Http\Controllers\LawyersController::class, 'index'])->name('lawyers.index');
    Route::get('/lawyers/create', [App\Http\Controllers\LawyersController::class, 'create'])->name('lawyers.create');
    Route::post('/lawyers', [App\Http\Controllers\LawyersController::class, 'store'])->name('lawyers.store');
    Route::get('/lawyers/{lawyer}', [App\Http\Controllers\LawyersController::class, 'show'])->name('lawyers.show');
    Route::get('/lawyers/{lawyer}/edit', [App\Http\Controllers\LawyersController::class, 'edit'])->name('lawyers.edit');
    Route::put('/lawyers/{lawyer}', [App\Http\Controllers\LawyersController::class, 'update'])->name('lawyers.update');
    Route::delete('/lawyers/{lawyer}', [App\Http\Controllers\LawyersController::class, 'destroy'])->name('lawyers.destroy');
});
*/

// Engagement Letter Management
Route::middleware(['auth'])->group(function () {
    Route::get('/engagement-letters', [App\Http\Controllers\EngagementLetterController::class, 'index'])->name('engagement-letters.index');
    Route::get('/engagement-letters/create', [App\Http\Controllers\EngagementLetterController::class, 'create'])->name('engagement-letters.create');
    Route::post('/engagement-letters', [App\Http\Controllers\EngagementLetterController::class, 'store'])->name('engagement-letters.store');
    Route::get('/engagement-letters/{engagementLetter}', [App\Http\Controllers\EngagementLetterController::class, 'show'])->name('engagement-letters.show');
    Route::get('/engagement-letters/{engagementLetter}/edit', [App\Http\Controllers\EngagementLetterController::class, 'edit'])->name('engagement-letters.edit');
    Route::put('/engagement-letters/{engagementLetter}', [App\Http\Controllers\EngagementLetterController::class, 'update'])->name('engagement-letters.update');
    Route::delete('/engagement-letters/{engagementLetter}', [App\Http\Controllers\EngagementLetterController::class, 'destroy'])->name('engagement-letters.destroy');
});

// Contact Management
Route::middleware(['auth'])->group(function () {
    Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/create', [App\Http\Controllers\ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [App\Http\Controllers\ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{contact}', [App\Http\Controllers\ContactController::class, 'show'])->name('contacts.show');
    Route::get('/contacts/{contact}/edit', [App\Http\Controllers\ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [App\Http\Controllers\ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [App\Http\Controllers\ContactController::class, 'destroy'])->name('contacts.destroy');
});

// Power of Attorney Management
Route::middleware(['auth'])->group(function () {
    Route::get('/power-of-attorneys', [App\Http\Controllers\PowerOfAttorneyController::class, 'index'])->name('power-of-attorneys.index');
    Route::get('/power-of-attorneys/create', [App\Http\Controllers\PowerOfAttorneyController::class, 'create'])->name('power-of-attorneys.create');
    Route::post('/power-of-attorneys', [App\Http\Controllers\PowerOfAttorneyController::class, 'store'])->name('power-of-attorneys.store');
    Route::get('/power-of-attorneys/{powerOfAttorney}', [App\Http\Controllers\PowerOfAttorneyController::class, 'show'])->name('power-of-attorneys.show');
    Route::get('/blade/power-of-attorneys/{powerOfAttorney}', [App\Http\Controllers\PowerOfAttorneyController::class, 'show'])->name('power-of-attorneys.show.blade');
    Route::get('/power-of-attorneys/{powerOfAttorney}/edit', [App\Http\Controllers\PowerOfAttorneyController::class, 'edit'])->name('power-of-attorneys.edit');
    Route::put('/power-of-attorneys/{powerOfAttorney}', [App\Http\Controllers\PowerOfAttorneyController::class, 'update'])->name('power-of-attorneys.update');
    Route::delete('/power-of-attorneys/{powerOfAttorney}', [App\Http\Controllers\PowerOfAttorneyController::class, 'destroy'])->name('power-of-attorneys.destroy');
});

// Trash/Recycle Bin routes (protected by permission middleware)
Route::middleware(['auth', 'permission:trash.view'])->prefix('trash')->name('trash.')->group(function () {
    Route::get('/', [App\Http\Controllers\TrashController::class, 'index'])->name('index');
    Route::get('/{bundle}', [App\Http\Controllers\TrashController::class, 'show'])->name('show');
    Route::post('/{bundle}/dry-run', [App\Http\Controllers\TrashController::class, 'dryRunRestore'])
        ->name('dry-run');
});

Route::middleware(['auth', 'permission:trash.restore'])->group(function () {
    Route::post('/trash/{bundle}/restore', [App\Http\Controllers\TrashController::class, 'restore'])
        ->name('trash.restore');
});

Route::middleware(['auth', 'permission:trash.purge'])->group(function () {
    Route::delete('/trash/{bundle}', [App\Http\Controllers\TrashController::class, 'purge'])
        ->name('trash.purge');
});

// Data Quality Dashboard (for admins)
Route::middleware(['auth', 'permission:admin.audit.view'])->group(function () {
    Route::get('/data-quality', [App\Http\Controllers\DataQualityController::class, 'index'])
        ->name('data-quality.index');
});

// Audit Logs (for admins)
Route::middleware(['auth', 'permission:admin.audit.view'])->group(function () {
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])
        ->name('audit-logs.index');
    Route::get('/audit-logs/{activity}', [App\Http\Controllers\AuditLogController::class, 'show'])
        ->name('audit-logs.show');
    Route::get('/audit-logs/export/csv', [App\Http\Controllers\AuditLogController::class, 'export'])
        ->name('audit-logs.export');
});

// Document Management
// Blade show route enabled for schema-driven all-fields view
Route::middleware(['auth', 'permission:documents.view'])->group(function () {
    Route::get('/blade/documents/{document}', [App\Http\Controllers\DocumentController::class, 'show'])
        ->name('documents.show.blade');
    // Inline preview via signed route
    Route::get('/documents/{document}/inline', [App\Http\Controllers\DocumentController::class, 'inline'])
        ->name('documents.inline')->middleware('signed');
    Route::get('/documents/{document}/download', [App\Http\Controllers\DocumentController::class, 'download'])
        ->name('documents.download');
    Route::get('/documents/{document}/signed-url', [App\Http\Controllers\DocumentController::class, 'signedUrl'])
        ->name('documents.signed-url');
});
// AJAX endpoint for getting client cases
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/client-cases', [App\Http\Controllers\DocumentController::class, 'getClientCases'])
        ->name('documents.client-cases');
});
// Other routes still handled by React SPA
/*
Route::middleware(['auth'])->group(function () {
    // Document upload (requires documents.upload permission)
    Route::middleware(['permission:documents.upload'])->group(function () {
        Route::get('/documents/create', [App\Http\Controllers\DocumentController::class, 'create'])
            ->name('documents.create');
        Route::post('/documents', [App\Http\Controllers\DocumentController::class, 'store'])
            ->name('documents.store');
    });

    // Document listing (requires documents.view permission)
    Route::middleware(['permission:documents.view'])->group(function () {
        Route::get('/documents', [App\Http\Controllers\DocumentController::class, 'index'])
            ->name('documents.index');
    });

    // Document editing (requires documents.edit permission)
    Route::middleware(['permission:documents.edit'])->group(function () {
        Route::get('/documents/{document}/edit', [App\Http\Controllers\DocumentController::class, 'edit'])
            ->name('documents.edit');
        Route::put('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'update'])
            ->name('documents.update');
    });

    // Document deletion (requires documents.delete permission)
    Route::middleware(['permission:documents.delete'])->group(function () {
        Route::delete('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'destroy'])
            ->name('documents.destroy');
    });
});
*/

// Admin Task Management
Route::middleware(['auth'])->group(function () {
    Route::get('/admin-tasks/create', [App\Http\Controllers\AdminTaskController::class, 'create'])->name('admin-tasks.create');
    Route::post('/admin-tasks', [App\Http\Controllers\AdminTaskController::class, 'store'])->name('admin-tasks.store');
    Route::get('/admin-tasks', [App\Http\Controllers\AdminTaskController::class, 'index'])->name('admin-tasks.index');
    Route::get('/admin-tasks/{adminTask}', [App\Http\Controllers\AdminTaskController::class, 'show'])->name('admin-tasks.show');
    Route::get('/blade/admin-tasks/{adminTask}', [App\Http\Controllers\AdminTaskController::class, 'show'])->name('admin-tasks.show.blade');
    Route::get('/admin-tasks/{adminTask}/edit', [App\Http\Controllers\AdminTaskController::class, 'edit'])->name('admin-tasks.edit');
    Route::put('/admin-tasks/{adminTask}', [App\Http\Controllers\AdminTaskController::class, 'update'])->name('admin-tasks.update');
    Route::delete('/admin-tasks/{adminTask}', [App\Http\Controllers\AdminTaskController::class, 'destroy'])->name('admin-tasks.destroy');
});

// Admin Subtask Management
Route::middleware(['auth'])->group(function () {
    Route::get('/admin-subtasks/create', [App\Http\Controllers\AdminSubtaskController::class, 'create'])->name('admin-subtasks.create');
    Route::post('/admin-subtasks', [App\Http\Controllers\AdminSubtaskController::class, 'store'])->name('admin-subtasks.store');
    Route::get('/admin-subtasks', [App\Http\Controllers\AdminSubtaskController::class, 'index'])->name('admin-subtasks.index');
    Route::get('/admin-subtasks/{adminSubtask}', [App\Http\Controllers\AdminSubtaskController::class, 'show'])->name('admin-subtasks.show');
    Route::get('/admin-subtasks/{adminSubtask}/edit', [App\Http\Controllers\AdminSubtaskController::class, 'edit'])->name('admin-subtasks.edit');
    Route::put('/admin-subtasks/{adminSubtask}', [App\Http\Controllers\AdminSubtaskController::class, 'update'])->name('admin-subtasks.update');
    Route::delete('/admin-subtasks/{adminSubtask}', [App\Http\Controllers\AdminSubtaskController::class, 'destroy'])->name('admin-subtasks.destroy');
});

// Import/Export Management
Route::middleware(['auth'])->group(function () {
    Route::get('/import', [App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
    Route::get('/import/upload', [App\Http\Controllers\ImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/upload', [App\Http\Controllers\ImportController::class, 'processUpload'])->name('import.process-upload');
    Route::get('/import/{importSession}/map', [App\Http\Controllers\ImportController::class, 'map'])->name('import.map');
    Route::post('/import/{importSession}/map', [App\Http\Controllers\ImportController::class, 'saveMapping'])->name('import.save-mapping');
    Route::get('/import/{importSession}/preflight', [App\Http\Controllers\ImportController::class, 'preflight'])->name('import.preflight');
    Route::post('/import/{importSession}/save-choices', [App\Http\Controllers\ImportController::class, 'saveChoicesNow'])->name('import.save-choices');
    Route::post('/import/{importSession}/run', [App\Http\Controllers\ImportController::class, 'runImport'])->name('import.run');
    Route::get('/import/{importSession}', [App\Http\Controllers\ImportController::class, 'show'])->name('import.show');
    Route::put('/import/{importSession}/cancel', [App\Http\Controllers\ImportController::class, 'cancel'])->name('import.cancel');
    Route::delete('/import/{importSession}', [App\Http\Controllers\ImportController::class, 'destroy'])->name('import.destroy');
});

// Cases Import Templates (Standard & Extended)
Route::middleware(['auth', 'permission:import.view_template'])->group(function () {
    // Standard Templates
    Route::get('/cases/import/template/standard/csv', [App\Http\Controllers\ImportController::class, 'downloadCaseTemplateStandardCsv'])
        ->name('cases.template.standard.csv');

    Route::get('/cases/import/template/standard/xlsx', [App\Http\Controllers\ImportController::class, 'downloadCaseTemplateStandardXlsx'])
        ->name('cases.template.standard.xlsx');

    // Extended Templates
    Route::get('/cases/import/template/extended/csv', [App\Http\Controllers\ImportController::class, 'downloadCaseTemplateExtendedCsv'])
        ->name('cases.template.extended.csv');

    Route::get('/cases/import/template/extended/xlsx', [App\Http\Controllers\ImportController::class, 'downloadCaseTemplateExtendedXlsx'])
        ->name('cases.template.extended.xlsx');

    // Case Opponents Companion Import Templates
    Route::get('/case-opponents/import/template/csv', [App\Http\Controllers\ImportController::class, 'downloadCaseOpponentsTemplateCsv'])
        ->name('case-opponents.template.csv');

    Route::get('/case-opponents/import/template/xlsx', [App\Http\Controllers\ImportController::class, 'downloadCaseOpponentsTemplateXlsx'])
        ->name('case-opponents.template.xlsx');

    // Hearings Import Templates
    Route::get('/hearings/import/template/csv', [App\Http\Controllers\ImportController::class, 'downloadHearingsTemplateCsv'])
        ->name('hearings.template.csv');

    Route::get('/hearings/import/template/xlsx', [App\Http\Controllers\ImportController::class, 'downloadHearingsTemplateXlsx'])
        ->name('hearings.template.xlsx');
});

// Case Opponents Companion Import
Route::middleware(['auth', 'permission:import.upload'])->group(function () {
    Route::get('/case-opponents/import', [App\Http\Controllers\ImportController::class, 'uploadCaseOpponents'])->name('case-opponents.import.upload');
    Route::post('/case-opponents/import', [App\Http\Controllers\ImportController::class, 'processCaseOpponentsUpload'])->name('case-opponents.import.process-upload');
});

// Admin: Regenerate Templates
Route::middleware(['auth', 'permission:admin.tools.manage'])->group(function () {
    Route::post('/admin/templates/regenerate-cases', [App\Http\Controllers\ImportController::class, 'regenerateCaseTemplates'])
        ->name('admin.templates.regenerate-cases');
});

// Option Management (Admin only)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // AJAX endpoint for getting options by set key
    Route::get('/options/api/{setKey}', [App\Http\Controllers\Admin\OptionController::class, 'getOptions'])->name('options.get');

    // Option Set CRUD
    Route::get('/options', [App\Http\Controllers\Admin\OptionController::class, 'index'])->name('options.index');
    Route::get('/options/create', [App\Http\Controllers\Admin\OptionController::class, 'create'])->name('options.create');
    Route::post('/options', [App\Http\Controllers\Admin\OptionController::class, 'store'])->name('options.store');
    Route::get('/options/{optionSet}', [App\Http\Controllers\Admin\OptionController::class, 'show'])->name('options.show');
    Route::get('/options/{optionSet}/edit', [App\Http\Controllers\Admin\OptionController::class, 'edit'])->name('options.edit');
    Route::put('/options/{optionSet}', [App\Http\Controllers\Admin\OptionController::class, 'update'])->name('options.update');
    Route::delete('/options/{optionSet}', [App\Http\Controllers\Admin\OptionController::class, 'destroy'])->name('options.destroy');

    // Option Values
    Route::post('/options/{optionSet}/values', [App\Http\Controllers\Admin\OptionController::class, 'storeValue'])->name('options.values.store');
    Route::put('/options/values/{optionValue}', [App\Http\Controllers\Admin\OptionController::class, 'updateValue'])->name('options.values.update');
    Route::delete('/options/values/{optionValue}', [App\Http\Controllers\Admin\OptionController::class, 'destroyValue'])->name('options.values.destroy');
});

// Courts Management
// Blade show route enabled for schema-driven all-fields view
Route::middleware(['auth'])->group(function () {
    Route::get('/blade/courts/{court}', [App\Http\Controllers\CourtsController::class, 'show'])->name('courts.show.blade');
    // AJAX endpoint for cascading dropdowns
    Route::get('/api/courts/{court}/details', [App\Http\Controllers\CasesController::class, 'getCourtDetails'])->name('courts.details');
});
// Other routes still handled by React SPA
/*
Route::middleware(['auth'])->group(function () {
    Route::get('/courts', [App\Http\Controllers\CourtsController::class, 'index'])->name('courts.index');
    Route::get('/courts/create', [App\Http\Controllers\CourtsController::class, 'create'])->name('courts.create');
    Route::post('/courts', [App\Http\Controllers\CourtsController::class, 'store'])->name('courts.store');
    Route::get('/courts/{court}/edit', [App\Http\Controllers\CourtsController::class, 'edit'])->name('courts.edit');
    Route::put('/courts/{court}', [App\Http\Controllers\CourtsController::class, 'update'])->name('courts.update');
    Route::delete('/courts/{court}', [App\Http\Controllers\CourtsController::class, 'destroy'])->name('courts.destroy');
});
*/

// Opponents Management
// Blade show route enabled for schema-driven all-fields view
Route::middleware(['auth'])->group(function () {
    Route::get('/blade/opponents/{opponent}', [App\Http\Controllers\OpponentsController::class, 'show'])->name('opponents.show.blade');
});
// Other routes still handled by React SPA
/*
Route::middleware(['auth'])->group(function () {
    Route::get('/opponents', [App\Http\Controllers\OpponentsController::class, 'index'])->name('opponents.index');
    Route::get('/opponents/create', [App\Http\Controllers\OpponentsController::class, 'create'])->name('opponents.create');
    Route::post('/opponents', [App\Http\Controllers\OpponentsController::class, 'store'])->name('opponents.store');
    Route::get('/opponents/{opponent}/edit', [App\Http\Controllers\OpponentsController::class, 'edit'])->name('opponents.edit');
    Route::put('/opponents/{opponent}', [App\Http\Controllers\OpponentsController::class, 'update'])->name('opponents.update');
    Route::delete('/opponents/{opponent}', [App\Http\Controllers\OpponentsController::class, 'destroy'])->name('opponents.destroy');
});
*/

// Temporary admin route to check/fix auto-increment (REMOVE AFTER USE)
Route::get('/admin/fix-auto-increment', function () {
    $tables = [
        'clients', 'lawyers', 'cases', 'hearings', 'engagement_letters',
        'contacts', 'power_of_attorneys', 'admin_tasks', 'admin_subtasks',
        'client_documents', 'option_sets', 'option_values', 'opponents',
        'courts', 'case_opponents',
    ];
    
    $results = [];
    
    foreach ($tables as $table) {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            $results[$table] = ['status' => 'TABLE_NOT_FOUND'];
            continue;
        }
        
        // Check current state
        $columnInfo = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'");
        if (empty($columnInfo)) {
            $results[$table] = ['status' => 'NO_ID_COLUMN'];
            continue;
        }
        
        $isAutoInc = stripos($columnInfo[0]->Extra ?? '', 'auto_increment') !== false;
        $maxId = \Illuminate\Support\Facades\DB::table($table)->max('id') ?? 0;
        
        // Get current AUTO_INCREMENT value
        $tableStatus = \Illuminate\Support\Facades\DB::select("SHOW TABLE STATUS WHERE Name = '{$table}'");
        $currentAutoInc = $tableStatus[0]->Auto_increment ?? null;
        
        $needsFix = false;
        $reason = '';
        
        // Check if fix is needed
        if (!$isAutoInc) {
            $needsFix = true;
            $reason = 'auto_increment not enabled';
        } elseif ($currentAutoInc === null || $currentAutoInc <= $maxId) {
            $needsFix = true;
            $reason = 'auto_increment value is null or too low';
        }
        
        if ($needsFix) {
            try {
                // Ensure auto-increment is enabled on the column
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                
                // Set the AUTO_INCREMENT value to max + 1
                $nextId = $maxId + 1;
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$nextId}");
                
                $results[$table] = [
                    'status' => 'FIXED',
                    'reason' => $reason,
                    'max_id' => $maxId,
                    'next_id' => $nextId
                ];
            } catch (\Exception $e) {
                $results[$table] = [
                    'status' => 'ERROR',
                    'error' => $e->getMessage()
                ];
            }
        } else {
            $results[$table] = [
                'status' => 'OK',
                'max_id' => $maxId,
                'auto_increment' => $currentAutoInc
            ];
        }
    }
    
    return response()->json([
        'message' => 'Auto-increment check/fix completed',
        'results' => $results
    ], 200, [], JSON_PRETTY_PRINT);
})->name('admin.fix-auto-increment');

// SPA Fallback Route - Must be last
// This route catches all non-API routes and serves the React SPA index.html
// The React Router will handle client-side routing
// NOTE: API routes are handled by routes/api.php and should not be caught here
// NOTE: Blade show routes (cases.show, clients.show, etc.) are matched before this catch-all
Route::get('/{any}', function () {
    // Don't catch API routes - they're handled by routes/api.php
    // Laravel's RouteServiceProvider loads API routes before web routes,
    // so API routes will be matched first. This is just a safety check.
    if (request()->is('api/*')) {
        abort(404); // API route not found in api.php
    }
    
    // Exclude Blade show routes - these should have been matched above
    // If we reach here, it means the route wasn't matched, so let React handle it
    $excludedPaths = ['cases', 'clients', 'opponents', 'courts', 'hearings', 'documents', 'power-of-attorneys', 'admin-tasks'];
    $pathSegments = explode('/', request()->path());
    if (count($pathSegments) >= 2 && in_array($pathSegments[0], $excludedPaths) && is_numeric($pathSegments[1])) {
        // This looks like a show route that should have matched above - abort 404
        abort(404, 'Route not found. Blade show routes should be matched before SPA fallback.');
    }
    
    // Check if the file exists in public directory (for assets like CSS, JS, images)
    $path = public_path(request()->path());
    if (file_exists($path) && !is_dir($path)) {
        return response()->file($path);
    }

    // Serve the React SPA index.html for all other routes
    return file_exists(public_path('index.html'))
        ? response()->file(public_path('index.html'))
        : view('welcome');
})->where('any', '.*');
