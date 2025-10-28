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
            // Add MFiles ID (number) and Client Code (alphanumeric) fields at the top
            $table->integer('mfiles_id')->nullable()->after('id')->comment('MFiles system identifier');
            $table->string('client_code', 50)->nullable()->after('mfiles_id')->comment('Unique client code for identification');
            
            // Add unique index for client_code to ensure uniqueness
            $table->unique('client_code', 'clients_client_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_client_code_unique');
            $table->dropColumn(['mfiles_id', 'client_code']);
        });
    }
};