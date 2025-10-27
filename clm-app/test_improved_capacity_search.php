<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing improved capacity search...\n\n";

$service = new \App\Services\FuzzyMatchingChoiceService();

// Test with the problematic search value
$searchValue = "\nمستأنفة"; // Include the newline character
echo "Testing search for: '{$searchValue}'\n";

$choices = $service->getChoicesForField('client_capacity_id', $searchValue);

echo "Found " . count($choices['choices']) . " matches:\n";
foreach ($choices['choices'] as $choice) {
    echo "- ID: {$choice['id']}, AR: '{$choice['label_ar']}', EN: '{$choice['label_en']}'\n";
}

echo "\nCan create: " . ($choices['can_create'] ? 'Yes' : 'No') . "\n";
echo "Create suggestion: " . json_encode($choices['create_suggestion']) . "\n";
