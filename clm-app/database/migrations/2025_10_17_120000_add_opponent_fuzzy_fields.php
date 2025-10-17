<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opponents', function (Blueprint $table) {
            // Ensure utf8mb4 collation on new columns
            $table->string('normalized_name', 512)->nullable()->collation('utf8mb4_unicode_ci')->after('opponent_name_en');
            $table->string('first_token', 64)->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('last_token', 64)->nullable()->collation('utf8mb4_unicode_ci');
            $table->unsignedTinyInteger('token_count')->nullable();
            $table->string('latin_key', 64)->nullable()->collation('utf8mb4_unicode_ci');
        });

        // Add indexes with safe prefix lengths for utf8mb4
        Schema::table('opponents', function (Blueprint $table) {
            $table->index([DB::raw('normalized_name(191)')], 'opponents_normalized_name_prefix');
            $table->index('first_token');
            $table->index('last_token');
            $table->index('token_count');
            $table->index('latin_key');
        });

        Schema::create('opponent_aliases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opponent_id')->index();
            $table->string('alias_normalized', 512)->collation('utf8mb4_unicode_ci');
            $table->timestamps();

            // Unique per opponent
            $table->unique(['opponent_id', DB::raw('alias_normalized(191)')], 'opponent_alias_unique');
            $table->foreign('opponent_id')->references('id')->on('opponents')->cascadeOnDelete();
        });

        // Optional pruning accelerator table
        Schema::create('opponent_trigrams', function (Blueprint $table) {
            $table->unsignedBigInteger('opponent_id');
            $table->char('tri', 3);
            $table->primary(['opponent_id', 'tri']);
            $table->index('tri');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opponent_trigrams');
        Schema::dropIfExists('opponent_aliases');

        Schema::table('opponents', function (Blueprint $table) {
            // Drop indexes first if needed (Laravel will auto-drop with columns but keep safe)
            // Then drop columns
            $table->dropColumn(['normalized_name', 'first_token', 'last_token', 'token_count', 'latin_key']);
        });
    }
};
