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
