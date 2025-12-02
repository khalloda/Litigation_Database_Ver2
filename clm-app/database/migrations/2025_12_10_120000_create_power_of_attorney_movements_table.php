<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power_of_attorney_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('power_of_attorney_id');
            $table->date('date');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->string('status', 32);
            $table->unsignedBigInteger('lawyer_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('power_of_attorney_id')
                ->references('id')
                ->on('power_of_attorneys')
                ->onDelete('cascade');

            $table->foreign('lawyer_id')
                ->references('id')
                ->on('lawyers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power_of_attorney_movements');
    }
};


