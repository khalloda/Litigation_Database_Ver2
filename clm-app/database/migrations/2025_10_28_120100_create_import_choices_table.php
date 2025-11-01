<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('import_choices')) {
            Schema::create('import_choices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('profile_id')->constrained('import_profiles')->cascadeOnDelete();
                $table->string('table_name', 64)->index();
                $table->string('column', 128)->index();
                $table->string('raw_value', 255)->nullable();
                $table->string('normalized_value', 255);
                $table->enum('action', ['match', 'alias', 'capacity', 'ignore'])->index();
                $table->string('entity_model', 128)->nullable()->index();
                $table->unsignedBigInteger('entity_id')->nullable()->index();
                $table->json('metadata_json')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Create composite index with safe prefix lengths for utf8mb4
        try {
            DB::statement('CREATE INDEX `choices_lookup_idx` ON `import_choices` (`table_name`(32), `column`(64), `normalized_value`(191), `is_active`)');
        } catch (\Throwable $e) {
            // ignore if already exists or backend doesn't support prefix
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('import_choices');
    }
};
