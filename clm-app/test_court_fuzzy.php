<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing court fuzzy matching...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

// Regenerate session to get fresh token
session()->regenerate();
$freshToken = csrf_token();

echo "Fresh CSRF Token: {$freshToken}\n";

// Test the applyChoice route with court creation
$response = $app->handle(\Illuminate\Http\Request::create(
    '/fuzzy-matching/apply-choice',
    'POST',
    [
        'field' => 'court_id',
        'search_value' => 'العجوزة الجزئية',
        'choice_type' => 'create',
        'choice_data' => [
            'court_name_ar' => 'العجوزة الجزئية',
            'court_name_en' => 'Agouza (Partial)'
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

// Check if the court was created
$court = \DB::table('courts')->where('court_name_ar', 'العجوزة الجزئية')->first();
if ($court) {
    echo "Court created successfully with ID: {$court->id}\n";

    // Clean up
    \DB::table('courts')->where('id', $court->id)->delete();
    echo "Test court deleted\n";
} else {
    echo "Court was not created\n";
}
