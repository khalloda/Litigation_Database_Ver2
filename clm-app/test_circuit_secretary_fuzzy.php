<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing circuit secretary fuzzy matching...\n\n";

// Get the current user and session
$user = \App\Models\User::first();
auth()->login($user);

// Start the session
session()->start();

// Regenerate session to get fresh token
session()->regenerate();
$freshToken = csrf_token();

echo "Fresh CSRF Token: {$freshToken}\n";

// Test the choices route first
echo "Testing choices route...\n";
$choicesResponse = $app->handle(\Illuminate\Http\Request::create(
    '/fuzzy-matching/choices?field=circuit_secretary&search_value=هبة عبد السلام&import_session_id=38',
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

echo "Choices response status: " . $choicesResponse->getStatusCode() . "\n";
echo "Choices response content: " . $choicesResponse->getContent() . "\n";

// Test the applyChoice route with circuit secretary creation
echo "\nTesting applyChoice route...\n";
$response = $app->handle(\Illuminate\Http\Request::create(
    '/fuzzy-matching/apply-choice',
    'POST',
    [
        'field' => 'circuit_secretary',
        'search_value' => 'هبة عبد السلام',
        'choice_type' => 'create',
        'choice_data' => [
            'label_ar' => 'هبة عبد السلام',
            'label_en' => 'Hiba Abd Al-Salam',
            'code' => 'secretary_hiba_abd_alsalam_' . time(),
            'position' => 999
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

echo "Apply choice response status: " . $response->getStatusCode() . "\n";
echo "Apply choice response content: " . $response->getContent() . "\n";

// Check if the circuit secretary was created
$secretary = \DB::table('option_values')
    ->join('option_sets', 'option_values.set_id', '=', 'option_sets.id')
    ->where('option_sets.key', 'court.circuit_secretary')
    ->where('option_values.label_ar', 'هبة عبد السلام')
    ->first();

if ($secretary) {
    echo "Circuit secretary created successfully with ID: {$secretary->id}\n";
    
    // Clean up
    \DB::table('option_values')->where('id', $secretary->id)->delete();
    echo "Test circuit secretary deleted\n";
} else {
    echo "Circuit secretary was not created\n";
}
