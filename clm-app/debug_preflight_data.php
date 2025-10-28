<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING PREFLIGHT DATA ===\n\n";

// Simulate the preflight controller logic
$importSessionId = 38;
$session = \App\Models\ImportSession::findOrFail($importSessionId);

echo "Import Session Data:\n";
echo "Total errors: " . count($session->preflight_errors) . "\n";
echo "Error count: " . $session->preflight_error_count . "\n";
echo "Warning count: " . $session->preflight_warning_count . "\n\n";

// Simulate the preflight results
$results = [
    'errors' => $session->preflight_errors,
    'error_count' => $session->preflight_error_count,
    'warning_count' => $session->preflight_warning_count,
    'total_rows' => $session->total_rows ?? 0,
    'success_rate' => $session->total_rows ? (($session->total_rows - $session->preflight_error_count) / $session->total_rows) * 100 : 0
];

echo "Preflight Results:\n";
echo "Errors: " . $results['error_count'] . "\n";
echo "Warnings: " . $results['warning_count'] . "\n";
echo "Total rows: " . $results['total_rows'] . "\n";
echo "Success rate: " . number_format($results['success_rate'], 1) . "%\n\n";

// Check first 10 errors
echo "First 10 errors:\n";
foreach (array_slice($results['errors'], 0, 10) as $index => $error) {
    $resolved = isset($error['resolved']) && $error['resolved'] ? 'YES' : 'NO';
    echo ($index + 1) . ". Row {$error['row']} - {$error['column']} - {$error['value']} - Resolved: {$resolved}\n";
}

echo "\nChecking resolved errors specifically:\n";
$resolvedErrors = collect($results['errors'])->where('resolved', true);
echo "Total resolved errors: " . $resolvedErrors->count() . "\n";

foreach ($resolvedErrors->take(5) as $index => $error) {
    echo ($index + 1) . ". Row {$error['row']} - {$error['column']} - {$error['value']} - ID: {$error['resolved_id']}\n";
}

echo "\nChecking opponent_capacity_id errors specifically:\n";
$opponentErrors = collect($results['errors'])->where('column', 'opponent_capacity_id');
echo "Total opponent_capacity_id errors: " . $opponentErrors->count() . "\n";

$resolvedOpponentErrors = $opponentErrors->where('resolved', true);
echo "Resolved opponent_capacity_id errors: " . $resolvedOpponentErrors->count() . "\n";

foreach ($resolvedOpponentErrors as $index => $error) {
    echo ($index + 1) . ". Row {$error['row']} - Value: {$error['value']} - Resolved: " . ($error['resolved'] ? 'YES' : 'NO') . "\n";
}
