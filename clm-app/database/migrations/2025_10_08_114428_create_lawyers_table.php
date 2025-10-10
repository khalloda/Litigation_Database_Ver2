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
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->string('lawyer_name_ar');
            $table->string('lawyer_name_en');
            $table->string('lawyer_name_title')->nullable();
            $table->string('lawyer_email')->nullable();
            $table->boolean('attendance_track')->default(false);
            
            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes (separate to avoid MySQL utf8mb4 length limit)
            $table->index('lawyer_name_ar');
            $table->index('lawyer_name_en');
            $table->index('lawyer_email');
            
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
        Schema::dropIfExists('lawyers');
    }
};
