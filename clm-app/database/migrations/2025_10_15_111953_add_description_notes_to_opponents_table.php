<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opponents', function (Blueprint $table) {
            if (!Schema::hasColumn('opponents', 'description')) {
                $table->text('description')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('opponents', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opponents', function (Blueprint $table) {
                if (Schema::hasColumn('opponents', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (Schema::hasColumn('opponents', 'description')) {
                    $table->dropColumn('description');
                }
        });
    }
};
