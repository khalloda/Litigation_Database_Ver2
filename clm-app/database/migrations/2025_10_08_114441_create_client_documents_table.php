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
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('cases')->nullOnDelete();

            $table->string('client_name')->nullable();
            $table->string('responsible_lawyer')->nullable();
            $table->boolean('movement_card')->default(false);
            $table->text('document_description');
            $table->date('deposit_date');
            $table->date('document_date')->nullable();
            $table->string('case_number')->nullable();
            $table->string('pages_count')->nullable();
            $table->text('notes')->nullable();

            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['client_id', 'matter_id', 'deposit_date']);
            $table->index('deposit_date');

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_documents');
    }
};
