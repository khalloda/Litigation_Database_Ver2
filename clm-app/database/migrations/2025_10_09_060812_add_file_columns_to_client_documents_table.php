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
            // Add file-related columns for document management
            $table->string('document_name')->nullable()->after('client_name');
            $table->string('document_type')->nullable()->after('document_name');
            $table->string('file_path')->nullable()->after('document_type');
            $table->bigInteger('file_size')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('file_size');
            
            // Add indexes for file operations
            $table->index('document_type');
            $table->index('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropIndex(['document_type']);
            $table->dropIndex(['mime_type']);
            
            $table->dropColumn([
                'document_name',
                'document_type', 
                'file_path',
                'file_size',
                'mime_type'
            ]);
        });
    }
};