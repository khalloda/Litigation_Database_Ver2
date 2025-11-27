<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all table names in the current database
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            // The column name varies depending on the DB name, get first value
            $tableName = array_values((array) $table)[0];
            DB::statement("ALTER TABLE `$tableName` ENGINE=InnoDB");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: revert back to MyISAM if needed
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            DB::statement("ALTER TABLE `$tableName` ENGINE=MyISAM");
        }
    }
};
