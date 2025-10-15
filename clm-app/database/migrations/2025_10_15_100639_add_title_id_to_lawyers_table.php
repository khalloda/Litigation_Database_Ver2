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
        Schema::table('lawyers', function (Blueprint $table) {
            if (!Schema::hasColumn('lawyers', 'title_id')) {
                $table->unsignedBigInteger('title_id')->nullable()->after('lawyer_name_title');
            }
        });

        // Add FK constraint in a separate statement to avoid issues if column pre-exists
        if (!DB::select("SHOW KEYS FROM lawyers WHERE Key_name = 'lawyers_title_id_foreign'")) {
            Schema::table('lawyers', function (Blueprint $table) {
                $table->foreign('title_id')->references('id')->on('option_values')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            if (Schema::hasColumn('lawyers', 'title_id')) {
                $table->dropForeign(['title_id']);
                $table->dropColumn('title_id');
            }
        });
    }
};
