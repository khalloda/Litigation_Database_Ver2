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
        Schema::create('client_documents_staging', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_document_id')->nullable();
            $table->unsignedBigInteger('legacy_client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('legacy_matter_name')->nullable();
            $table->text('document_description')->nullable();
            $table->string('document_date_raw')->nullable();
            $table->date('document_date')->nullable();
            $table->string('pages_count_raw')->nullable();
            $table->string('pages_count')->nullable();
            $table->string('deposit_date_raw')->nullable();
            $table->date('deposit_date')->nullable();
            $table->string('department')->nullable();
            $table->string('admin_staff')->nullable();
            $table->string('lawyer')->nullable();
            $table->string('responsible_lawyer')->nullable();
            $table->text('notes')->nullable();
            $table->string('movement_card_raw')->nullable();
            $table->boolean('movement_card')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('legacy_document_id');
            $table->index('legacy_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_documents_staging');
    }
};

