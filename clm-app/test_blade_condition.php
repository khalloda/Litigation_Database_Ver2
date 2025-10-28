<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING BLADE CONDITION ===\n\n";

// Test the exact condition from the Blade template
$testErrors = [
    [
        'row' => 5,
        'column' => 'opponent_capacity_id',
        'value' => 'مدعي عليه',
        'message' => 'Expected integer, got \'مدعي عليه\'',
        'resolved' => true,
        'resolved_id' => 290
    ],
    [
        'row' => 6,
        'column' => 'client_capacity_id',
        'value' => 'مستأنف',
        'message' => 'Expected integer, got \'مستأنف\'',
        // resolved not set
    ]
];

echo "Testing Blade condition: !isset(\$error['resolved']) || !\$error['resolved']\n\n";

foreach ($testErrors as $index => $error) {
    $condition = !isset($error['resolved']) || !$error['resolved'];
    $resolved = isset($error['resolved']) ? ($error['resolved'] ? 'true' : 'false') : 'not set';

    echo "Error " . ($index + 1) . ":\n";
    echo "  Row: " . $error['row'] . "\n";
    echo "  Column: " . $error['column'] . "\n";
    echo "  Value: " . $error['value'] . "\n";
    echo "  Resolved: " . $resolved . "\n";
    echo "  Condition result: " . ($condition ? 'SHOW' : 'HIDE') . "\n";
    echo "  Should be: " . ($resolved === 'true' ? 'HIDE' : 'SHOW') . "\n\n";
}
