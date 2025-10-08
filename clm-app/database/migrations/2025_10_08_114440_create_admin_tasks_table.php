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
        Schema::create('admin_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matter_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('last_follow_up')->nullable();
            $table->date('last_date')->nullable();
            $table->string('authority')->nullable();
            $table->string('status')->nullable();
            $table->string('circuit')->nullable();
            $table->text('required_work')->nullable();
            $table->string('performer')->nullable();
            $table->text('previous_decision')->nullable();
            $table->string('court')->nullable();
            $table->text('result')->nullable();
            $table->dateTime('creation_date')->nullable();
            $table->dateTime('execution_date')->nullable();
            $table->boolean('alert')->default(false);
            
            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['matter_id', 'status']);
            $table->index('lawyer_id');
            $table->index('execution_date');
            
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
        Schema::dropIfExists('admin_tasks');
    }
};
