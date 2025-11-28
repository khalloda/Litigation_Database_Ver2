<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'clients',
    'lawyers', 
    'cases',
    'hearings',
    'engagement_letters',
    'contacts',
    'power_of_attorneys',
    'admin_tasks',
    'admin_subtasks',
    'client_documents',
    'option_sets',
    'option_values',
    'opponents',
    'courts',
    'case_opponents',
];

$output = "Auto-Increment Status Check\n";
$output .= "============================\n\n";

foreach ($tables as $table) {
    if (!Schema::hasTable($table)) {
        $output .= "Table '{$table}' does not exist\n";
        continue;
    }
    
    // Get column info
    $result = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'");
    if (empty($result)) {
        $output .= "Table '{$table}' has no 'id' column\n";
        continue;
    }
    
    $column = $result[0];
    $isAutoIncrement = str_contains(strtolower($column->Extra ?? ''), 'auto_increment');
    $maxId = DB::table($table)->max('id') ?? 0;
    
    // Get AUTO_INCREMENT value
    $tableStatus = DB::select("SHOW TABLE STATUS WHERE Name = '{$table}'");
    $autoIncrementValue = $tableStatus[0]->Auto_increment ?? 'N/A';
    
    $status = $isAutoIncrement ? 'YES' : 'NO';
    $output .= sprintf(
        "%-25s | Auto-Increment: %-3s | Max ID: %-6d | Next ID: %s\n",
        $table,
        $status,
        $maxId,
        $autoIncrementValue
    );
}

// Write to multiple locations
$logPath = __DIR__.'/storage/logs/auto_increment_status.log';
file_put_contents($logPath, $output);
file_put_contents(__DIR__.'/public/auto_increment_status.txt', $output);
echo $output;

