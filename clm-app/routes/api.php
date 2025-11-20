<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// Public options route (needed for initial page load)
Route::get('/options/{setKey}', [App\Http\Controllers\Api\OptionController::class, 'getBySetKey'])
    ->where('setKey', '[a-zA-Z0-9._-]+');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\Api\AuthController::class, 'user']);

    // Cases
    Route::apiResource('cases', App\Http\Controllers\Api\CaseController::class);

    // Clients
    Route::apiResource('clients', App\Http\Controllers\Api\ClientController::class);
    Route::get('clients/{client}/schema', [App\Http\Controllers\Api\ClientController::class, 'schema'])->name('clients.schema');

    // Opponents
    Route::apiResource('opponents', App\Http\Controllers\Api\OpponentController::class);

    // Lawyers
    Route::apiResource('lawyers', App\Http\Controllers\Api\LawyerController::class);

    // Courts
    Route::apiResource('courts', App\Http\Controllers\Api\CourtController::class);

    // Hearings
    Route::apiResource('hearings', App\Http\Controllers\Api\HearingController::class);

    // Documents
    Route::apiResource('documents', App\Http\Controllers\Api\DocumentController::class);

    // Tasks
    Route::apiResource('tasks', App\Http\Controllers\Api\TaskController::class);

    // Users
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);

    // Roles
    Route::apiResource('roles', App\Http\Controllers\Api\RoleController::class);

    // Options (management routes - require auth)
    Route::apiResource('options', App\Http\Controllers\Api\OptionController::class);
    Route::post('/options/{optionSet}/values', [App\Http\Controllers\Api\OptionController::class, 'storeValue']);
    Route::put('/options/values/{optionValue}', [App\Http\Controllers\Api\OptionController::class, 'updateValue']);
    Route::delete('/options/values/{optionValue}', [App\Http\Controllers\Api\OptionController::class, 'destroyValue']);

    // AI endpoints
    Route::post('/ai/case-summary', [App\Http\Controllers\Api\AiController::class, 'generateCaseSummary']);
    Route::post('/ai/analyze-document', [App\Http\Controllers\Api\AiController::class, 'analyzeDocument']);

    // Dashboard
    Route::get('/dashboard/statistics', [App\Http\Controllers\Api\DashboardController::class, 'statistics']);
});
