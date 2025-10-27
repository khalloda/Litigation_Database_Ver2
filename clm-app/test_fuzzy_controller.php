<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing FuzzyMatchingController directly...\n\n";

try {
    // Test the controller directly
    $controller = new \App\Http\Controllers\FuzzyMatchingController(
        new \App\Services\FuzzyMatchingChoiceService()
    );
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'field' => 'client_capacity_id',
        'search_value' => 'طاعنة',
        'choice_type' => 'create',
        'choice_data' => [
            'label_ar' => 'طاعنة',
            'label_en' => 'Challenger (Female)'
        ],
        'import_session_id' => 37
    ]);
    
    echo "Testing applyChoice method...\n";
    echo "Request data: " . json_encode($request->all()) . "\n\n";
    
    $response = $controller->applyChoice($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
