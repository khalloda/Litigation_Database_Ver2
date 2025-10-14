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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained('engagement_letters')->nullOnDelete();

            // Case identification
            $table->string('matter_name_ar');
            $table->string('matter_name_en');
            $table->text('matter_description')->nullable();
            $table->string('matter_status')->nullable();

            // Case details
            $table->string('matter_category')->nullable();
            $table->string('matter_degree')->nullable();
            $table->string('matter_court')->nullable();
            $table->string('matter_circuit')->nullable();
            $table->string('matter_destination')->nullable();
            $table->string('matter_importance')->nullable();
            $table->string('matter_evaluation')->nullable();

            // Dates and amounts
            $table->date('matter_start_date')->nullable();
            $table->date('matter_end_date')->nullable();
            $table->decimal('matter_asked_amount', 15, 2)->nullable();
            $table->decimal('matter_judged_amount', 15, 2)->nullable();

            // Organization and team
            $table->string('matter_shelf')->nullable();
            $table->string('matter_partner')->nullable();
            $table->string('lawyer_a')->nullable();
            $table->string('lawyer_b')->nullable();
            $table->string('circuit_secretary')->nullable();
            $table->integer('court_floor')->nullable();
            $table->integer('court_hall')->nullable();
            $table->decimal('fee_letter', 15, 2)->nullable();
            $table->integer('team_id')->nullable();

            // Additional info
            $table->text('legal_opinion')->nullable();
            $table->text('financial_provision')->nullable();
            $table->text('current_status')->nullable();
            $table->text('notes_1')->nullable();
            $table->text('notes_2')->nullable();

            // Parties
            $table->text('client_and_capacity')->nullable();
            $table->text('opponent_and_capacity')->nullable();
            $table->string('client_branch')->nullable();
            $table->string('client_type')->nullable();

            $table->boolean('matter_select')->default(true);

            // Audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes (avoiding composite string indexes due to MySQL utf8mb4 length limit)
            $table->index(['client_id', 'matter_status']);
            $table->index('matter_name_ar');
            $table->index('matter_name_en');
            $table->index('matter_status');
            $table->index('matter_start_date');
            $table->index('contract_id');

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
        Schema::dropIfExists('cases');
    }
};
