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
        Schema::table('clients', function (Blueprint $table) {
            // Add nullable FK columns for managed dropdowns
            $table->foreignId('cash_or_probono_id')->nullable()->constrained('option_values')->onDelete('set null')->after('cash_or_probono');
            $table->foreignId('status_id')->nullable()->constrained('option_values')->onDelete('set null')->after('status');
            $table->foreignId('power_of_attorney_location_id')->nullable()->constrained('option_values')->onDelete('set null')->after('power_of_attorney_location');
            $table->foreignId('documents_location_id')->nullable()->constrained('option_values')->onDelete('set null')->after('documents_location');
            
            // Add indexes for performance
            $table->index('cash_or_probono_id');
            $table->index('status_id');
            $table->index('power_of_attorney_location_id');
            $table->index('documents_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['cash_or_probono_id']);
            $table->dropForeign(['status_id']);
            $table->dropForeign(['power_of_attorney_location_id']);
            $table->dropForeign(['documents_location_id']);
            
            $table->dropIndex(['cash_or_probono_id']);
            $table->dropIndex(['status_id']);
            $table->dropIndex(['power_of_attorney_location_id']);
            $table->dropIndex(['documents_location_id']);
            
            $table->dropColumn([
                'cash_or_probono_id',
                'status_id', 
                'power_of_attorney_location_id',
                'documents_location_id'
            ]);
        });
    }
};