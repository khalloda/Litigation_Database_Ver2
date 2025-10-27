<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking current session...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

$currentToken = csrf_token();
echo "Current CSRF Token: {$currentToken}\n";
echo "Browser Token: j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy\n";
echo "Tokens match: " . ($currentToken === 'j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy' ? 'YES' : 'NO') . "\n";

// Check session data
echo "\nSession ID: " . session()->getId() . "\n";
echo "Session data: " . print_r(session()->all(), true) . "\n";

// Test if we can regenerate the token
echo "\nRegenerating CSRF token...\n";
session()->regenerateToken();
$newToken = csrf_token();
echo "New CSRF Token: {$newToken}\n";
echo "New token matches browser: " . ($newToken === 'j12yf2Npn3WNHg7xSbaDmr8AkOzcB9znJcBeYvAy' ? 'YES' : 'NO') . "\n";
