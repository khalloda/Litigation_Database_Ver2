<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Debugging session cookie issue...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

echo "Current session ID: " . session()->getId() . "\n";
echo "Current CSRF token: " . csrf_token() . "\n";

// Regenerate session
session()->regenerate();
$newSessionId = session()->getId();
$newToken = csrf_token();

echo "New session ID: " . $newSessionId . "\n";
echo "New CSRF token: " . $newToken . "\n";

// Check if session file exists
$sessionFile = storage_path('framework/sessions/' . $newSessionId);
echo "Session file exists: " . (file_exists($sessionFile) ? 'YES' : 'NO') . "\n";

if (file_exists($sessionFile)) {
    echo "Session file content:\n";
    $content = file_get_contents($sessionFile);
    echo $content . "\n";
}

// Test the choices route with refresh_csrf
echo "\nTesting choices route with refresh_csrf...\n";
$response = $app->handle(\Illuminate\Http\Request::create(
    '/fuzzy-matching/choices?refresh_csrf=1',
    'GET',
    [],
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

// Check if Set-Cookie header is present
$headers = $response->headers->all();
echo "Set-Cookie headers: " . (isset($headers['set-cookie']) ? implode(', ', $headers['set-cookie']) : 'NONE') . "\n";
