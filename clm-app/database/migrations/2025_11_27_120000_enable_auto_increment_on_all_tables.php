<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tables that need auto-increment enabled.
     * Each entry: table_name => id_column_type
     */
    private array $tables = [
        'clients' => 'BIGINT UNSIGNED',
        'lawyers' => 'BIGINT UNSIGNED',
        'cases' => 'BIGINT UNSIGNED',
        'hearings' => 'BIGINT UNSIGNED',
        'engagement_letters' => 'BIGINT UNSIGNED',
        'contacts' => 'BIGINT UNSIGNED',
        'power_of_attorneys' => 'BIGINT UNSIGNED',
        'admin_tasks' => 'BIGINT UNSIGNED',
        'admin_subtasks' => 'BIGINT UNSIGNED',
        'client_documents' => 'BIGINT UNSIGNED',
        'option_sets' => 'BIGINT UNSIGNED',
        'option_values' => 'BIGINT UNSIGNED',
        'opponents' => 'BIGINT UNSIGNED',
        'courts' => 'BIGINT UNSIGNED',
        'case_opponents' => 'BIGINT UNSIGNED',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table => $columnType) {
            // Check if table exists
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            // Get the max ID currently in the table
            $maxId = DB::table($table)->max('id') ?? 0;
            $nextId = $maxId + 1;

            // Enable auto-increment on the id column
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` {$columnType} NOT NULL AUTO_INCREMENT");
            
            // Set the AUTO_INCREMENT value to be greater than max existing ID
            DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$nextId}");
            
            echo "Enabled auto-increment on {$table}.id (next ID: {$nextId})\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table => $columnType) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            // Disable auto-increment (for import purposes if needed)
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` {$columnType} NOT NULL");
            
            echo "Disabled auto-increment on {$table}.id\n";
        }
    }
};

