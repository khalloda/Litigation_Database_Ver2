<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing capacity creation...\n\n";

try {
    // Test creating a new capacity
    $optionSet = \App\Models\OptionSet::where('key', 'capacity.type')->first();
    
    if (!$optionSet) {
        echo "❌ Capacity option set not found!\n";
        exit(1);
    }
    
    echo "✅ Found capacity option set: ID {$optionSet->id}, Key: {$optionSet->key}\n";
    
    // Test creating a new option value
    $testData = [
        'label_ar' => 'طاعنة',
        'label_en' => 'Challenger (Female)'
    ];
    
    echo "Creating new capacity with data: " . json_encode($testData) . "\n";
    
    $optionValue = \App\Models\OptionValue::create([
        'set_id' => $optionSet->id,
        'code' => strtolower(str_replace(' ', '_', $testData['label_en'])),
        'label_ar' => $testData['label_ar'],
        'label_en' => $testData['label_en'],
        'position' => 0,
        'is_active' => true,
    ]);
    
    echo "✅ Successfully created OptionValue with ID: {$optionValue->id}\n";
    echo "Code: {$optionValue->code}\n";
    echo "AR: {$optionValue->label_ar}\n";
    echo "EN: {$optionValue->label_en}\n";
    
    // Clean up - delete the test record
    $optionValue->delete();
    echo "✅ Test record cleaned up\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
