<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking capacity data...\n\n";

// Check if capacity option set exists
$capacitySet = \App\Models\OptionSet::where('key', 'capacity.type')->first();

if ($capacitySet) {
    echo "✅ Capacity option set found: {$capacitySet->name}\n";
    echo "Values count: " . $capacitySet->optionValues()->count() . "\n\n";
    
    // Show first 5 values
    $values = $capacitySet->optionValues()->take(5)->get();
    foreach ($values as $value) {
        echo "- ID: {$value->id}, AR: {$value->label_ar}, EN: {$value->label_en}\n";
    }
} else {
    echo "❌ Capacity option set not found!\n";
}

// Test the search
echo "\nTesting search for 'مستأنفة':\n";
$searchValue = 'مستأنفة';
$capacities = \App\Models\OptionValue::whereHas('optionSet', function ($q) {
    $q->where('key', 'capacity.type');
})->where(function ($q) use ($searchValue) {
    $q->where('label_en', 'like', '%' . $searchValue . '%')
        ->orWhere('label_ar', 'like', '%' . $searchValue . '%');
})->get();

echo "Found " . $capacities->count() . " matches:\n";
foreach ($capacities as $capacity) {
    echo "- ID: {$capacity->id}, AR: {$capacity->label_ar}, EN: {$capacity->label_en}\n";
}
