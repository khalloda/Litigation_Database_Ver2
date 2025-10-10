<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Locale switch
Route::get('/locale/{locale}', [App\Http\Controllers\LocaleController::class, 'switch'])
    ->whereIn('locale', ['en', 'ar'])
    ->name('locale.switch');

// Basic CRUD stubs
Route::middleware(['auth', 'permission:clients.view'])->group(function () {
    Route::get('/clients', [App\Http\Controllers\ClientsController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}', [App\Http\Controllers\ClientsController::class, 'show'])->name('clients.show');
});
Route::middleware(['auth', 'permission:clients.create'])->group(function () {
    Route::get('/clients/create', [App\Http\Controllers\ClientsController::class, 'create'])->name('clients.create');
    Route::post('/clients', [App\Http\Controllers\ClientsController::class, 'store'])->name('clients.store');
});
Route::middleware(['auth', 'permission:clients.edit'])->group(function () {
    Route::get('/clients/{client}/edit', [App\Http\Controllers\ClientsController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [App\Http\Controllers\ClientsController::class, 'update'])->name('clients.update');
});
Route::middleware(['auth', 'permission:clients.delete'])->group(function () {
    Route::delete('/clients/{client}', [App\Http\Controllers\ClientsController::class, 'destroy'])->name('clients.destroy');
});
// Case Management
Route::middleware(['auth', 'permission:cases.view'])->group(function () {
    Route::get('/cases', [App\Http\Controllers\CasesController::class, 'index'])->name('cases.index');
});
Route::middleware(['auth', 'permission:cases.create'])->group(function () {
    Route::get('/cases/create', [App\Http\Controllers\CasesController::class, 'create'])->name('cases.create');
    Route::post('/cases', [App\Http\Controllers\CasesController::class, 'store'])->name('cases.store');
});
Route::middleware(['auth', 'permission:cases.view'])->group(function () {
    Route::get('/cases/{case}', [App\Http\Controllers\CasesController::class, 'show'])->name('cases.show');
});
Route::middleware(['auth', 'permission:cases.edit'])->group(function () {
    Route::get('/cases/{case}/edit', [App\Http\Controllers\CasesController::class, 'edit'])->name('cases.edit');
    Route::put('/cases/{case}', [App\Http\Controllers\CasesController::class, 'update'])->name('cases.update');
});
Route::middleware(['auth', 'permission:cases.delete'])->group(function () {
    Route::delete('/cases/{case}', [App\Http\Controllers\CasesController::class, 'destroy'])->name('cases.destroy');
});

// Hearing Management
Route::middleware(['auth', 'permission:hearings.view'])->group(function () {
    Route::get('/hearings', [App\Http\Controllers\HearingsController::class, 'index'])->name('hearings.index');
});
Route::middleware(['auth', 'permission:hearings.create'])->group(function () {
    Route::get('/hearings/create', [App\Http\Controllers\HearingsController::class, 'create'])->name('hearings.create');
    Route::post('/hearings', [App\Http\Controllers\HearingsController::class, 'store'])->name('hearings.store');
});
Route::middleware(['auth', 'permission:hearings.view'])->group(function () {
    Route::get('/hearings/{hearing}', [App\Http\Controllers\HearingsController::class, 'show'])->name('hearings.show');
});
Route::middleware(['auth', 'permission:hearings.edit'])->group(function () {
    Route::get('/hearings/{hearing}/edit', [App\Http\Controllers\HearingsController::class, 'edit'])->name('hearings.edit');
    Route::put('/hearings/{hearing}', [App\Http\Controllers\HearingsController::class, 'update'])->name('hearings.update');
});
Route::middleware(['auth', 'permission:hearings.delete'])->group(function () {
    Route::delete('/hearings/{hearing}', [App\Http\Controllers\HearingsController::class, 'destroy'])->name('hearings.destroy');
});

// Lawyer Management (admin only)
Route::middleware(['auth', 'permission:admin.users.manage'])->group(function () {
    Route::get('/lawyers', [App\Http\Controllers\LawyersController::class, 'index'])->name('lawyers.index');
    Route::get('/lawyers/create', [App\Http\Controllers\LawyersController::class, 'create'])->name('lawyers.create');
    Route::post('/lawyers', [App\Http\Controllers\LawyersController::class, 'store'])->name('lawyers.store');
    Route::get('/lawyers/{lawyer}', [App\Http\Controllers\LawyersController::class, 'show'])->name('lawyers.show');
    Route::get('/lawyers/{lawyer}/edit', [App\Http\Controllers\LawyersController::class, 'edit'])->name('lawyers.edit');
    Route::put('/lawyers/{lawyer}', [App\Http\Controllers\LawyersController::class, 'update'])->name('lawyers.update');
    Route::delete('/lawyers/{lawyer}', [App\Http\Controllers\LawyersController::class, 'destroy'])->name('lawyers.destroy');
});

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
Route::middleware(['auth'])->group(function () {
    // AJAX endpoint for getting client cases (must be before parameterized routes)
    Route::get('/documents/client-cases', [App\Http\Controllers\DocumentController::class, 'getClientCases'])
        ->name('documents.client-cases');

    // Document upload (requires documents.upload permission) - MUST be before /documents/{document}
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
        // Inline preview via signed route
        Route::get('/documents/{document}/inline', [App\Http\Controllers\DocumentController::class, 'inline'])
            ->name('documents.inline')->middleware('signed');
    });

    // Document editing (requires documents.edit permission) - MUST be before /documents/{document}
    Route::middleware(['permission:documents.edit'])->group(function () {
        Route::get('/documents/{document}/edit', [App\Http\Controllers\DocumentController::class, 'edit'])
            ->name('documents.edit');
        Route::put('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'update'])
            ->name('documents.update');
    });

    // Document deletion (requires documents.delete permission) - MUST be before /documents/{document}
    Route::middleware(['permission:documents.delete'])->group(function () {
        Route::delete('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'destroy'])
            ->name('documents.destroy');
    });

    // Document operations that require documents.view permission
    Route::middleware(['permission:documents.view'])->group(function () {
        Route::get('/documents/{document}/download', [App\Http\Controllers\DocumentController::class, 'download'])
            ->name('documents.download');
        Route::get('/documents/{document}/signed-url', [App\Http\Controllers\DocumentController::class, 'signedUrl'])
            ->name('documents.signed-url');
        // This MUST be last - it catches any remaining /documents/{anything} patterns
        Route::get('/documents/{document}', [App\Http\Controllers\DocumentController::class, 'show'])
            ->name('documents.show');
    });
});

// Admin Task Management
Route::middleware(['auth'])->group(function () {
    Route::get('/admin-tasks/create', [App\Http\Controllers\AdminTaskController::class, 'create'])->name('admin-tasks.create');
    Route::post('/admin-tasks', [App\Http\Controllers\AdminTaskController::class, 'store'])->name('admin-tasks.store');
    Route::get('/admin-tasks', [App\Http\Controllers\AdminTaskController::class, 'index'])->name('admin-tasks.index');
    Route::get('/admin-tasks/{adminTask}', [App\Http\Controllers\AdminTaskController::class, 'show'])->name('admin-tasks.show');
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
