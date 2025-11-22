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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name_ar');
            $table->string('client_name_en')->nullable();
            $table->string('client_print_name');
            $table->string('status')->default('Active');
            $table->string('cash_or_probono')->nullable();
            $table->date('client_start')->nullable();
            $table->date('client_end')->nullable();
            $table->string('contact_lawyer')->nullable();
            $table->string('logo')->nullable();
            $table->string('power_of_attorney_location');
            $table->string('documents_location')->nullable();
            
            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes (separate to avoid MySQL utf8mb4 length limit)
            $table->index('client_name_ar');
            $table->index('client_name_en');
            $table->index('status');
            $table->index('client_start');
            
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
        Schema::dropIfExists('clients');
    }
};
