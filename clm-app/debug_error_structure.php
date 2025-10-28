<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Debugging error structure for import session 38...\n\n";

$session = \App\Models\ImportSession::find(38);

if (!$session) {
    echo "Import session not found\n";
    exit;
}

echo "Total errors: " . count($session->preflight_errors) . "\n";
echo "Error count: " . $session->preflight_error_count . "\n";
echo "Warning count: " . $session->preflight_warning_count . "\n\n";

// Look for opponent_capacity_id errors
$opponentErrors = collect($session->preflight_errors)->where('column', 'opponent_capacity_id');

echo "Opponent capacity errors: " . $opponentErrors->count() . "\n";

foreach ($opponentErrors->take(5) as $index => $error) {
    echo "\nError " . $index . ":\n";
    echo "  Row: " . ($error['row'] ?? 'N/A') . "\n";
    echo "  Column: " . ($error['column'] ?? 'N/A') . "\n";
    echo "  Value: " . ($error['value'] ?? 'N/A') . "\n";
    echo "  Message: " . ($error['message'] ?? 'N/A') . "\n";
    echo "  Resolved: " . (isset($error['resolved']) ? ($error['resolved'] ? 'Yes' : 'No') : 'Not set') . "\n";
    echo "  Keys: " . implode(', ', array_keys($error)) . "\n";
}

// Look for specific value "مدعي عليه"
$specificErrors = collect($session->preflight_errors)->filter(function ($error) {
    return isset($error['column']) && $error['column'] === 'opponent_capacity_id' &&
        isset($error['value']) && $error['value'] === 'مدعي عليه';
});

echo "\n\nSpecific errors for 'مدعي عليه': " . $specificErrors->count() . "\n";

foreach ($specificErrors as $index => $error) {
    echo "\nSpecific Error " . $index . ":\n";
    echo "  Row: " . ($error['row'] ?? 'N/A') . "\n";
    echo "  Column: " . ($error['column'] ?? 'N/A') . "\n";
    echo "  Value: " . ($error['value'] ?? 'N/A') . "\n";
    echo "  Message: " . ($error['message'] ?? 'N/A') . "\n";
    echo "  Resolved: " . (isset($error['resolved']) ? ($error['resolved'] ? 'Yes' : 'No') : 'Not set') . "\n";
}
