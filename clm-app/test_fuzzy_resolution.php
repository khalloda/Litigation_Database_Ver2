<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing fuzzy matching resolution...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

// Regenerate session to get fresh token
session()->regenerate();
$freshToken = csrf_token();

echo "Fresh CSRF Token: {$freshToken}\n";

// Test the applyChoice route with existing choice
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
echo "Response content: " . $response->getContent() . "\n";

// Check if the import session was updated
$session = \App\Models\ImportSession::find(38);
if ($session) {
    echo "\nImport session preflight_errors count: " . count($session->preflight_errors) . "\n";
    echo "Error count: " . $session->preflight_error_count . "\n";
    echo "Warning count: " . $session->preflight_warning_count . "\n";
    
    // Check if any errors were marked as resolved
    $resolvedErrors = collect($session->preflight_errors)->where('resolved', true);
    echo "Resolved errors: " . $resolvedErrors->count() . "\n";
    
    if ($resolvedErrors->count() > 0) {
        echo "First resolved error:\n";
        $firstResolved = $resolvedErrors->first();
        echo "  Field: " . $firstResolved['column'] . "\n";
        echo "  Value: " . $firstResolved['value'] . "\n";
        echo "  Resolved ID: " . ($firstResolved['resolved_id'] ?? 'N/A') . "\n";
        echo "  Resolved at: " . ($firstResolved['resolved_at'] ?? 'N/A') . "\n";
    }
} else {
    echo "Import session not found\n";
}
