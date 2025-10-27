<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Debugging hardcoded token issue...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

echo "Current Session ID: " . session()->getId() . "\n";
echo "Current CSRF Token: " . csrf_token() . "\n";
echo "Browser Token: j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy\n";
echo "Tokens match: " . (csrf_token() === 'j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy' ? 'YES' : 'NO') . "\n";

// Check if there are multiple sessions
echo "\nChecking session storage...\n";
$sessionPath = storage_path('framework/sessions');
if (is_dir($sessionPath)) {
    $files = glob($sessionPath . '/*');
    echo "Session files count: " . count($files) . "\n";
    
    // Check if the browser token exists in any session file
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if (strpos($content, 'j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy') !== false) {
            echo "Found browser token in session file: " . basename($file) . "\n";
        }
    }
}

// Test the preflight page directly
echo "\nTesting preflight page...\n";
$request = \Illuminate\Http\Request::create(
    '/import/38/preflight',
    'GET',
    [],
    [],
    [],
    [
        'HTTP_HOST' => 'litigation.local'
    ]
);

$response = $app->handle($request);
$content = $response->getContent();

// Extract CSRF token from HTML
if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $content, $matches)) {
    echo "CSRF token in preflight page: " . $matches[1] . "\n";
    echo "Matches browser token: " . ($matches[1] === 'j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy' ? 'YES' : 'NO') . "\n";
} else {
    echo "Could not find CSRF token in preflight page\n";
}
