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
        Schema::create('power_of_attorneys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->string('client_print_name')->nullable();
            $table->string('principal_name');
            $table->integer('year')->nullable();
            $table->string('capacity')->nullable();
            $table->text('authorized_lawyers')->nullable();
            $table->date('issue_date')->nullable();
            $table->boolean('inventory')->default(true);
            $table->string('issuing_authority')->nullable();
            $table->string('letter')->nullable();
            $table->integer('poa_number')->nullable();
            $table->string('principal_capacity')->nullable();
            $table->integer('copies_count')->nullable();
            $table->string('serial')->nullable();
            $table->text('notes')->nullable();

            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('client_id');
            $table->index('issue_date');
            $table->index('poa_number');

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
        Schema::dropIfExists('power_of_attorneys');
    }
};
