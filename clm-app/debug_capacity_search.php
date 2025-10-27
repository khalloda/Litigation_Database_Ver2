<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Debugging capacity search...\n\n";

$searchValue = "\nمستأنفة";
echo "Original search value: '" . $searchValue . "'\n";
echo "Length: " . strlen($searchValue) . "\n";
echo "Trimmed: '" . trim($searchValue) . "'\n";
echo "Trimmed length: " . strlen(trim($searchValue)) . "\n\n";

// Test the regex
$baseAr = preg_replace('/[ةه]$/', '', trim($searchValue));
echo "Base Arabic (after removing feminine endings): '" . $baseAr . "'\n";

// Test against actual capacity values
$capacities = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
    $q->where('key', 'capacity.type');
})->get();

echo "\nTesting against capacity values:\n";
foreach ($capacities as $capacity) {
    if (strpos($capacity->label_ar, 'مستأنف') !== false) {
        echo "Found: '{$capacity->label_ar}'\n";
        $baseCapacityAr = preg_replace('/[ةه]$/', '', $capacity->label_ar);
        echo "  Base: '{$baseCapacityAr}'\n";
        echo "  Match: " . ($baseAr === $baseCapacityAr ? 'YES' : 'NO') . "\n";
    }
}
