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
            // Add contact_lawyer_id foreign key
            $table->foreignId('contact_lawyer_id')->nullable()->after('contact_lawyer')->constrained('lawyers')->onDelete('set null');
            
            // Add index for performance
            $table->index('contact_lawyer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['contact_lawyer_id']);
            $table->dropColumn('contact_lawyer_id');
        });
    }
};
