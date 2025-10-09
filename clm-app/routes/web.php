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
