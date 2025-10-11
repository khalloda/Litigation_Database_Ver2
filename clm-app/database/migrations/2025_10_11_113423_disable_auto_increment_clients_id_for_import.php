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
        // Disable auto-increment on clients.id for import
        DB::statement('ALTER TABLE clients MODIFY COLUMN id INT(11) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-enable auto-increment on clients.id
        DB::statement('ALTER TABLE clients MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT');
    }
};
