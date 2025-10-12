<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable auto-increment on hearings.id for import with ID preservation
        DB::statement('ALTER TABLE hearings MODIFY COLUMN id INT(11) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-enable auto-increment on hearings.id
        DB::statement('ALTER TABLE hearings MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT');
    }
};