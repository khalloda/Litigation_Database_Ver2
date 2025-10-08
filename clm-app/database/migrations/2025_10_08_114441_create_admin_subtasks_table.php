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
        Schema::create('admin_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('admin_tasks')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('performer')->nullable();
            $table->date('next_date')->nullable();
            $table->text('result')->nullable();
            $table->date('procedure_date')->nullable();
            $table->boolean('report')->default(false);
            
            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('task_id');
            $table->index('lawyer_id');
            $table->index('next_date');
            
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
        Schema::dropIfExists('admin_subtasks');
    }
};
