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
        Schema::table('client_documents', function (Blueprint $table) {
            // Add document storage type (physical, digital, both)
            $table->enum('document_storage_type', ['physical', 'digital', 'both'])
                ->default('physical')
                ->after('mime_type');

            // Add M-Files integration fields
            $table->boolean('mfiles_uploaded')->default(false)->after('document_storage_type');
            $table->string('mfiles_id')->nullable()->after('mfiles_uploaded');

            // Make file upload fields nullable for physical documents
            $table->string('document_name')->nullable()->change();
            $table->string('file_path')->nullable()->change();
            $table->bigInteger('file_size')->nullable()->change();
            $table->string('mime_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn(['document_storage_type', 'mfiles_uploaded', 'mfiles_id']);

            // Revert file fields to not nullable
            $table->string('document_name')->nullable(false)->change();
            $table->string('file_path')->nullable(false)->change();
            $table->bigInteger('file_size')->nullable(false)->change();
            $table->string('mime_type')->nullable(false)->change();
        });
    }
};
