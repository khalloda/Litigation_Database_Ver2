<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing capacity choices for fuzzy matching...\n\n";

// Test the FuzzyMatchingChoiceService
$choiceService = new \App\Services\FuzzyMatchingChoiceService();

echo "=== Testing getChoicesForField for client_capacity_id ===\n";
$choices = $choiceService->getChoicesForField('client_capacity_id', 'طاعة');

echo "Field: " . $choices['field'] . "\n";
echo "Search Value: " . $choices['search_value'] . "\n";
echo "Can Create: " . ($choices['can_create'] ? 'Yes' : 'No') . "\n";
echo "Choices Count: " . count($choices['choices']) . "\n";

if (!empty($choices['choices'])) {
    echo "First few choices:\n";
    foreach (array_slice($choices['choices'], 0, 3) as $choice) {
        echo "- ID: {$choice['id']}, AR: '{$choice['label_ar']}', EN: '{$choice['label_en']}'\n";
    }
} else {
    echo "No choices found. Let's check what's in the database...\n";
    
    // Check option_sets
    $capacitySet = \App\Models\OptionSet::where('key', 'capacity.type')->first();
    if ($capacitySet) {
        echo "Found capacity option set: ID {$capacitySet->id}, Key: {$capacitySet->key}\n";
        
        // Check option_values
        $values = \App\Models\OptionValue::where('set_id', $capacitySet->id)->limit(5)->get();
        echo "Found {$values->count()} capacity values:\n";
        foreach ($values as $value) {
            echo "- ID: {$value->id}, AR: '{$value->label_ar}', EN: '{$value->label_en}'\n";
        }
    } else {
        echo "No capacity option set found!\n";
    }
}

echo "\n=== Testing with different search value ===\n";
$choices2 = $choiceService->getChoicesForField('client_capacity_id', 'مدعى');
echo "Search Value: 'مدعى'\n";
echo "Choices Count: " . count($choices2['choices']) . "\n";
