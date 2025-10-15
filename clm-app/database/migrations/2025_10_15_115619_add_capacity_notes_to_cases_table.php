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
        Schema::table('cases', function (Blueprint $table) {
            if (!Schema::hasColumn('cases', 'client_capacity_note')) {
                $table->text('client_capacity_note')->nullable();
            }
            if (!Schema::hasColumn('cases', 'opponent_capacity_note')) {
                $table->text('opponent_capacity_note')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            if (Schema::hasColumn('cases', 'opponent_capacity_note')) {
                $table->dropColumn('opponent_capacity_note');
            }
            if (Schema::hasColumn('cases', 'client_capacity_note')) {
                $table->dropColumn('client_capacity_note');
            }
        });
    }
};
