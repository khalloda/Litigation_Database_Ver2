<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing AJAX detection...\n\n";

// Test with AJAX headers
$request = \Illuminate\Http\Request::create(
    '/fuzzy-matching/apply-choice',
    'POST',
    ['_token' => 'invalid'],
    [],
    [],
    [
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_HOST' => 'litigation.local'
    ]
);

echo "Request is AJAX: " . ($request->ajax() ? 'YES' : 'NO') . "\n";
echo "Request wants JSON: " . ($request->wantsJson() ? 'YES' : 'NO') . "\n";
echo "Accept header: " . $request->header('Accept') . "\n";
echo "X-Requested-With header: " . $request->header('X-Requested-With') . "\n";

// Test the CSRF middleware directly
try {
    $middleware = new \App\Http\Middleware\VerifyCsrfToken();
    $response = $middleware->handle($request, function($req) {
        return response()->json(['success' => true]);
    });
    
    echo "CSRF middleware response status: " . $response->getStatusCode() . "\n";
    echo "CSRF middleware response content: " . $response->getContent() . "\n";
    
} catch (\Exception $e) {
    echo "CSRF middleware error: " . $e->getMessage() . "\n";
}
