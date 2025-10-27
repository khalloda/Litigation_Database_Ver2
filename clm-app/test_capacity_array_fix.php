<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing capacity array fix...\n\n";

$service = new \App\Services\FuzzyMatchingChoiceService();

// Test with the problematic search value
$searchValue = "\n\n\nمستأنف ضدها"; // Include the newline characters
echo "Testing search for: '{$searchValue}'\n";

$choices = $service->getChoicesForField('client_capacity_id', $searchValue);

echo "Choices type: " . gettype($choices['choices']) . "\n";
echo "Is array: " . (is_array($choices['choices']) ? 'Yes' : 'No') . "\n";
echo "Count: " . count($choices['choices']) . "\n";

if (is_array($choices['choices'])) {
    echo "✅ Choices is now an array!\n";
    foreach ($choices['choices'] as $choice) {
        echo "- ID: {$choice['id']}, AR: '{$choice['label_ar']}', EN: '{$choice['label_en']}'\n";
    }
} else {
    echo "❌ Choices is still not an array: " . print_r($choices['choices'], true) . "\n";
}
