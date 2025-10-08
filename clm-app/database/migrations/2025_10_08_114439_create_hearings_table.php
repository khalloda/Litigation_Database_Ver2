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
        Schema::create('hearings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matter_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->nullable()->constrained()->nullOnDelete();
            
            $table->date('date')->nullable();
            $table->string('procedure')->nullable();
            $table->string('court')->nullable();
            $table->string('circuit')->nullable();
            $table->string('destination')->nullable();
            
            $table->text('decision')->nullable();
            $table->string('short_decision')->nullable();
            $table->string('last_decision')->nullable();
            $table->date('next_hearing')->nullable();
            
            $table->boolean('report')->default(false);
            $table->boolean('notify_client')->default(false);
            
            // Attendees
            $table->string('attendee')->nullable();
            $table->string('attendee_1')->nullable();
            $table->string('attendee_2')->nullable();
            $table->string('attendee_3')->nullable();
            $table->string('attendee_4')->nullable();
            $table->string('next_attendee')->nullable();
            
            $table->string('evaluation')->nullable();
            $table->text('notes')->nullable();
            
            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['matter_id', 'date']);
            $table->index('next_hearing');
            $table->index('lawyer_id');
            
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
        Schema::dropIfExists('hearings');
    }
};
