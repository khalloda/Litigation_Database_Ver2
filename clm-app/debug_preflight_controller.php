<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING PREFLIGHT CONTROLLER DATA ===\n\n";

// Simulate the exact preflight controller logic
$importSessionId = 38;
$session = \App\Models\ImportSession::findOrFail($importSessionId);

echo "1. RAW DATABASE DATA\n";
echo "====================\n";
echo "Total errors in DB: " . count($session->preflight_errors) . "\n";
echo "Error count in DB: " . $session->preflight_error_count . "\n";
echo "Warning count in DB: " . $session->preflight_warning_count . "\n\n";

// Check resolved errors in raw data
$resolvedCount = collect($session->preflight_errors)->where('resolved', true)->count();
echo "Resolved errors in DB: " . $resolvedCount . "\n\n";

echo "2. SIMULATING PREFLIGHT CONTROLLER LOGIC\n";
echo "========================================\n";

// This is the exact logic from the preflight controller
$results = [
    'errors' => $session->preflight_errors,
    'error_count' => $session->preflight_error_count,
    'warning_count' => $session->preflight_warning_count,
    'total_rows' => $session->total_rows ?? 0,
    'success_rate' => $session->total_rows ? (($session->total_rows - $session->preflight_error_count) / $session->total_rows) * 100 : 0
];

echo "Results array:\n";
echo "  errors: " . count($results['errors']) . " items\n";
echo "  error_count: " . $results['error_count'] . "\n";
echo "  warning_count: " . $results['warning_count'] . "\n";
echo "  total_rows: " . $results['total_rows'] . "\n";
echo "  success_rate: " . number_format($results['success_rate'], 1) . "%\n\n";

echo "3. CHECKING FIRST 10 ERRORS (what Blade template sees)\n";
echo "=====================================================\n";

foreach (array_slice($results['errors'], 0, 10) as $index => $error) {
    $resolved = isset($error['resolved']) && $error['resolved'] ? 'YES' : 'NO';
    $shouldShow = !isset($error['resolved']) || !$error['resolved'];
    echo ($index + 1) . ". Row {$error['row']} - {$error['column']} - '{$error['value']}' - Resolved: {$resolved} - Should show: " . ($shouldShow ? 'YES' : 'NO') . "\n";
}

echo "\n4. CHECKING OPPONENT_CAPACITY_ID ERRORS SPECIFICALLY\n";
echo "====================================================\n";

$opponentErrors = collect($results['errors'])->where('column', 'opponent_capacity_id');
echo "Total opponent_capacity_id errors: " . $opponentErrors->count() . "\n";

$resolvedOpponentErrors = $opponentErrors->where('resolved', true);
echo "Resolved opponent_capacity_id errors: " . $resolvedOpponentErrors->count() . "\n\n";

foreach ($opponentErrors->take(10) as $index => $error) {
    $resolved = isset($error['resolved']) && $error['resolved'] ? 'YES' : 'NO';
    $shouldShow = !isset($error['resolved']) || !$error['resolved'];
    echo ($index + 1) . ". Row {$error['row']} - '{$error['value']}' - Resolved: {$resolved} - Should show: " . ($shouldShow ? 'YES' : 'NO') . "\n";
}

echo "\n5. TESTING BLADE FILTERING LOGIC\n";
echo "================================\n";

$filteredErrors = [];
foreach ($results['errors'] as $error) {
    if (!isset($error['resolved']) || !$error['resolved']) {
        $filteredErrors[] = $error;
    }
}

echo "Total errors before filtering: " . count($results['errors']) . "\n";
echo "Total errors after filtering: " . count($filteredErrors) . "\n";
echo "Filtered out: " . (count($results['errors']) - count($filteredErrors)) . " errors\n\n";

echo "6. CHECKING IF THERE'S A CACHING ISSUE\n";
echo "=====================================\n";

// Force refresh the session from database
$freshSession = \App\Models\ImportSession::find($importSessionId);
echo "Fresh session error count: " . $freshSession->preflight_error_count . "\n";

$freshResolvedCount = collect($freshSession->preflight_errors)->where('resolved', true)->count();
echo "Fresh session resolved count: " . $freshResolvedCount . "\n";

// Check if the data is the same
$isSame = $session->preflight_errors === $freshSession->preflight_errors;
echo "Data is same as before: " . ($isSame ? 'YES' : 'NO') . "\n";
