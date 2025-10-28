<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->index(['table_name', 'column', 'normalized_value', 'is_active'], 'choices_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_choices');
    }
};


