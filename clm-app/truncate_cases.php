<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    DB::table('cases')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "✓ Truncated table: cases\n";
    exit(0);
} catch (\Throwable $e) {
    // Ensure FK checks are re-enabled on failure
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    } catch (\Throwable $ignored) {
    }
    fwrite(STDERR, "Error truncating cases: " . $e->getMessage() . "\n");
    exit(1);
}
