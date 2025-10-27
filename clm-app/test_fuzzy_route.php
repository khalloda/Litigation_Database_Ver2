<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing fuzzy matching route...\n\n";

try {
    // Test the actual route
    $response = $app->handle(\Illuminate\Http\Request::create(
        '/fuzzy-matching/apply-choice',
        'POST',
        [
            'field' => 'client_capacity_id',
            'search_value' => 'طاعنة',
            'choice_type' => 'create',
            'choice_data' => [
                'label_ar' => 'طاعنة',
                'label_en' => 'Challenger (Female)'
            ],
            'import_session_id' => 37
        ],
        [],
        [],
        ['HTTP_ACCEPT' => 'application/json']
    ));
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response headers: " . json_encode($response->headers->all()) . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
