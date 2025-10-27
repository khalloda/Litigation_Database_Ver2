<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing CSRF middleware directly...\n\n";

try {
    // Get a valid CSRF token first
    $user = \App\Models\User::first();
    auth()->login($user);
    
    // Start a session to get CSRF token
    session()->start();
    $csrfToken = csrf_token();
    echo "CSRF Token: {$csrfToken}\n";
    
    // Test with the EXACT same token from the browser
    $browserToken = 'j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy';
    echo "Browser Token: {$browserToken}\n";
    echo "Tokens match: " . ($csrfToken === $browserToken ? 'YES' : 'NO') . "\n";
    
    // Test the actual route with the browser's token
    $response = $app->handle(\Illuminate\Http\Request::create(
        '/fuzzy-matching/apply-choice',
        'POST',
        [
            'field' => 'opponent_capacity_id',
            'search_value' => 'مدعى عليهم',
            'choice_type' => 'existing',
            'choice_data' => [
                'id' => 298,
                'label_ar' => 'مدعى عليه',
                'label_en' => 'Defendant (Civil)',
                'display' => 'مدعى عليه (Defendant (Civil))'
            ],
            'import_session_id' => 38,
            '_token' => $browserToken
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
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
