<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing fuzzy matching route directly...\n\n";

try {
    // Get a valid CSRF token first
    $user = \App\Models\User::first();
    auth()->login($user);
    
    $csrfToken = csrf_token();
    echo "CSRF Token: {$csrfToken}\n";
    
    // Test the actual route with proper CSRF token
    $response = $app->handle(\Illuminate\Http\Request::create(
        '/fuzzy-matching/apply-choice',
        'POST',
        [
            'field' => 'opponent_capacity_id',
            'search_value' => 'مطعون ضدها',
            'choice_type' => 'create',
            'choice_data' => [
                'label_ar' => 'مطعون ضدها',
                'label_en' => 'Appealed Against'
            ],
            'import_session_id' => 37,
            '_token' => $csrfToken
        ],
        [],
        [],
        [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]
    ));
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response headers: " . json_encode($response->headers->all()) . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
