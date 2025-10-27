<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking capacity search patterns...\n\n";

$searchValue = 'مستأنفة';
echo "Searching for: '{$searchValue}'\n\n";

// Check for partial matches
$capacities = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
    $q->where('key', 'capacity.type');
})->get();

echo "All capacity values containing 'مست':\n";
foreach ($capacities as $capacity) {
    if (strpos($capacity->label_ar, 'مست') !== false) {
        echo "- ID: {$capacity->id}, AR: '{$capacity->label_ar}', EN: '{$capacity->label_en}'\n";
    }
}

echo "\nAll capacity values containing 'أنف':\n";
foreach ($capacities as $capacity) {
    if (strpos($capacity->label_ar, 'أنف') !== false) {
        echo "- ID: {$capacity->id}, AR: '{$capacity->label_ar}', EN: '{$capacity->label_en}'\n";
    }
}

echo "\nAll capacity values containing 'مستأنف':\n";
foreach ($capacities as $capacity) {
    if (strpos($capacity->label_ar, 'مستأنف') !== false) {
        echo "- ID: {$capacity->id}, AR: '{$capacity->label_ar}', EN: '{$capacity->label_en}'\n";
    }
}
