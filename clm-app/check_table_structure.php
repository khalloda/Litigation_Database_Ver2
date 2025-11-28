<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking table structures...\n\n";

// Check option_sets table
echo "=== option_sets table ===\n";
$columns = \DB::select('DESCRIBE option_sets');
foreach ($columns as $col) {
    echo "{$col->Field} - {$col->Type} - {$col->Extra}\n";
}

echo "\n=== option_values table ===\n";
$columns = \DB::select('DESCRIBE option_values');
foreach ($columns as $col) {
    echo "{$col->Field} - {$col->Type} - {$col->Extra}\n";
}

// Check if there are any records
echo "\n=== Record counts ===\n";
echo "option_sets count: " . \DB::table('option_sets')->count() . "\n";
echo "option_values count: " . \DB::table('option_values')->count() . "\n";

// Check if we can create a new option_value
echo "\n=== Testing option_value creation ===\n";
try {
    $newId = \DB::table('option_values')->insertGetId([
        'set_id' => 1, // Assuming set_id 1 exists
        'code' => 'test_' . time(),
        'label_ar' => 'اختبار',
        'label_en' => 'Test',
        'position' => 999,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Successfully created option_value with ID: {$newId}\n";

    // Clean up
    \DB::table('option_values')->where('id', $newId)->delete();
    echo "Test record deleted\n";
} catch (\Exception $e) {
    echo "Error creating option_value: " . $e->getMessage() . "\n";
}
