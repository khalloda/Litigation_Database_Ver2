<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing with fresh CSRF token...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

// Regenerate session to get fresh token
session()->regenerate();
$freshToken = csrf_token();

echo "Fresh CSRF Token: {$freshToken}\n";

// Test the applyChoice route with fresh token
$response = $app->handle(\Illuminate\Http\Request::create(
    '/fuzzy-matching/apply-choice',
    'POST',
    [
        'field' => 'client_capacity_id',
        'search_value' => 'مستأنف ضدها',
        'choice_type' => 'create',
        'choice_data' => [
            'label_ar' => 'مستأنف ضدها',
            'label_en' => 'Appealed Against Her'
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

// Check if controller was reached
echo "\nChecking logs for controller execution...\n";
$logContent = file_get_contents(storage_path('logs/laravel.log'));
$lastControllerLog = strrpos($logContent, 'FuzzyMatchingController::applyChoice called');
if ($lastControllerLog !== false) {
    echo "Controller was reached! Last log entry found.\n";
} else {
    echo "Controller was NOT reached - no logs found.\n";
}
