<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking user permissions...\n\n";

try {
    $user = \App\Models\User::first();
    
    if (!$user) {
        echo "❌ No user found\n";
        exit(1);
    }
    
    echo "✅ User found: {$user->email}\n";
    echo "✅ User roles: " . json_encode($user->getRoleNames()) . "\n";
    echo "✅ User permissions: " . json_encode($user->getAllPermissions()->pluck('name')) . "\n";
    
    $hasImportView = $user->hasPermissionTo('import.view');
    echo "✅ Has import.view permission: " . ($hasImportView ? 'YES' : 'NO') . "\n";
    
    if (!$hasImportView) {
        echo "\n❌ MISSING PERMISSION: import.view\n";
        echo "This is why the fuzzy matching route returns HTML instead of JSON!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
