<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEEP DEBUGGING FUZZY MATCHING RESOLUTION ===\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

echo "1. CHECKING IMPORT SESSION DATA\n";
echo "================================\n";

$session = \App\Models\ImportSession::find(38);
if (!$session) {
    echo "❌ Import session 38 not found\n";
    exit;
}

echo "✅ Import session found\n";
echo "Total errors: " . count($session->preflight_errors) . "\n";
echo "Error count: " . $session->preflight_error_count . "\n";
echo "Warning count: " . $session->preflight_warning_count . "\n\n";

echo "2. CHECKING SPECIFIC ERROR BEFORE RESOLUTION\n";
echo "============================================\n";

// Find all opponent_capacity_id errors with "مدعي عليه"
$opponentErrors = collect($session->preflight_errors)->filter(function($error) {
    return isset($error['column']) && $error['column'] === 'opponent_capacity_id' && 
           isset($error['value']) && $error['value'] === 'مدعي عليه';
});

echo "Found " . $opponentErrors->count() . " errors with 'مدعي عليه'\n";

foreach ($opponentErrors as $index => $error) {
    echo "Error " . ($index + 1) . ":\n";
    echo "  Row: " . ($error['row'] ?? 'N/A') . "\n";
    echo "  Column: " . ($error['column'] ?? 'N/A') . "\n";
    echo "  Value: " . ($error['value'] ?? 'N/A') . "\n";
    echo "  Message: " . ($error['message'] ?? 'N/A') . "\n";
    echo "  Resolved: " . (isset($error['resolved']) ? ($error['resolved'] ? 'Yes' : 'No') : 'Not set') . "\n";
    echo "  Keys: " . implode(', ', array_keys($error)) . "\n\n";
}

echo "3. TESTING FUZZY MATCHING RESOLUTION\n";
echo "====================================\n";

// Regenerate session to get fresh token
session()->regenerate();
$freshToken = csrf_token();
echo "Fresh CSRF Token: {$freshToken}\n\n";

// Test the applyChoice route
echo "Making applyChoice request...\n";

$response = $app->handle(\Illuminate\Http\Request::create(
    '/fuzzy-matching/apply-choice',
    'POST',
    [
        'field' => 'opponent_capacity_id',
        'search_value' => 'مدعي عليه',
        'choice_type' => 'existing',
        'choice_data' => [
            'id' => 290,
            'label_ar' => 'مدعى عليه',
            'label_en' => 'Defendant (Civil)',
            'display' => 'مدعى عليه (Defendant (Civil))'
        ],
        'import_session_id' => 38,
        '_token' => $freshToken
    ],
    [],
    [],
    [
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_HOST' => 'litigation.local'
    ]
));

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content: " . $response->getContent() . "\n\n";

echo "4. CHECKING IMPORT SESSION DATA AFTER RESOLUTION\n";
echo "================================================\n";

// Refresh the session from database
$session->refresh();

echo "Total errors after: " . count($session->preflight_errors) . "\n";
echo "Error count after: " . $session->preflight_error_count . "\n";
echo "Warning count after: " . $session->preflight_warning_count . "\n\n";

// Check if any errors were marked as resolved
$resolvedErrors = collect($session->preflight_errors)->where('resolved', true);
echo "Resolved errors: " . $resolvedErrors->count() . "\n\n";

if ($resolvedErrors->count() > 0) {
    echo "Resolved error details:\n";
    foreach ($resolvedErrors as $index => $error) {
        echo "Resolved " . ($index + 1) . ":\n";
        echo "  Row: " . ($error['row'] ?? 'N/A') . "\n";
        echo "  Column: " . ($error['column'] ?? 'N/A') . "\n";
        echo "  Value: " . ($error['value'] ?? 'N/A') . "\n";
        echo "  Resolved ID: " . ($error['resolved_id'] ?? 'N/A') . "\n";
        echo "  Resolved at: " . ($error['resolved_at'] ?? 'N/A') . "\n\n";
    }
}

echo "5. CHECKING SPECIFIC ERRORS AFTER RESOLUTION\n";
echo "============================================\n";

// Check if the specific errors are still there
$opponentErrorsAfter = collect($session->preflight_errors)->filter(function($error) {
    return isset($error['column']) && $error['column'] === 'opponent_capacity_id' && 
           isset($error['value']) && $error['value'] === 'مدعي عليه';
});

echo "Found " . $opponentErrorsAfter->count() . " errors with 'مدعي عليه' after resolution\n";

foreach ($opponentErrorsAfter as $index => $error) {
    echo "Error " . ($index + 1) . ":\n";
    echo "  Row: " . ($error['row'] ?? 'N/A') . "\n";
    echo "  Column: " . ($error['column'] ?? 'N/A') . "\n";
    echo "  Value: " . ($error['value'] ?? 'N/A') . "\n";
    echo "  Message: " . ($error['message'] ?? 'N/A') . "\n";
    echo "  Resolved: " . (isset($error['resolved']) ? ($error['resolved'] ? 'Yes' : 'No') : 'Not set') . "\n\n";
}

echo "6. CHECKING CONTROLLER LOGS\n";
echo "===========================\n";

// Check if there are any logs from the controller
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lastLogEntry = substr($logContent, strrpos($logContent, 'Import session data updated with resolved ID'));
    if ($lastLogEntry !== false) {
        echo "✅ Found controller log entry:\n";
        echo substr($lastLogEntry, 0, 500) . "\n\n";
    } else {
        echo "❌ No controller log entry found\n\n";
    }
} else {
    echo "❌ Log file not found\n\n";
}

echo "7. TESTING DATABASE UPDATE DIRECTLY\n";
echo "===================================\n";

// Test if we can update the session directly
$testSession = \App\Models\ImportSession::find(38);
$testErrors = $testSession->preflight_errors;

// Find first opponent_capacity_id error
$firstErrorIndex = null;
foreach ($testErrors as $index => $error) {
    if (isset($error['column']) && $error['column'] === 'opponent_capacity_id' && 
        isset($error['value']) && $error['value'] === 'مدعي عليه' && 
        (!isset($error['resolved']) || !$error['resolved'])) {
        $firstErrorIndex = $index;
        break;
    }
}

if ($firstErrorIndex !== null) {
    echo "Found unresolved error at index: {$firstErrorIndex}\n";
    
    // Update it directly
    $testErrors[$firstErrorIndex]['resolved'] = true;
    $testErrors[$firstErrorIndex]['resolved_id'] = 999;
    $testErrors[$firstErrorIndex]['resolved_at'] = now()->toISOString();
    
    // Save to database
    $testSession->preflight_errors = $testErrors;
    $testSession->preflight_error_count = collect($testErrors)->where('resolved', false)->count();
    $testSession->save();
    
    echo "✅ Direct database update successful\n";
    echo "New error count: " . $testSession->preflight_error_count . "\n\n";
} else {
    echo "❌ No unresolved errors found for direct update\n\n";
}
