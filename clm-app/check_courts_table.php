<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking courts table structure...\n\n";

// Check courts table
echo "=== courts table ===\n";
$columns = \DB::select('DESCRIBE courts');
foreach($columns as $col) {
    echo "{$col->Field} - {$col->Type} - {$col->Extra}\n";
}

// Check if we can create a new court
echo "\n=== Testing court creation ===\n";
try {
    $newId = \DB::table('courts')->insertGetId([
        'court_name_ar' => 'اختبار',
        'court_name_en' => 'Test Court',
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Successfully created court with ID: {$newId}\n";
    
    // Clean up
    \DB::table('courts')->where('id', $newId)->delete();
    echo "Test record deleted\n";
} catch (\Exception $e) {
    echo "Error creating court: " . $e->getMessage() . "\n";
}
