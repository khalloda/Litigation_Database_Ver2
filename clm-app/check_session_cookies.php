<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking session cookies...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

echo "Current Session ID: " . session()->getId() . "\n";
echo "Current CSRF Token: " . csrf_token() . "\n";

// Check session configuration
echo "\nSession configuration:\n";
echo "Session driver: " . config('session.driver') . "\n";
echo "Session lifetime: " . config('session.lifetime') . " minutes\n";
echo "Session cookie name: " . config('session.cookie') . "\n";
echo "Session cookie domain: " . config('session.domain') . "\n";
echo "Session cookie path: " . config('session.path') . "\n";

// Test if we can regenerate the session
echo "\nRegenerating session...\n";
session()->regenerate();
$newSessionId = session()->getId();
$newToken = csrf_token();

echo "New Session ID: {$newSessionId}\n";
echo "New CSRF Token: {$newToken}\n";

// Check if the old session file still exists
$oldSessionFile = storage_path('framework/sessions/8aZac1vuEZiRGr2DjUtx4vq2HkTd2KLVrI66U10i');
if (file_exists($oldSessionFile)) {
    echo "\nOld session file still exists: " . basename($oldSessionFile) . "\n";
    echo "Old session content: " . file_get_contents($oldSessionFile) . "\n";
} else {
    echo "\nOld session file not found (good!)\n";
}
