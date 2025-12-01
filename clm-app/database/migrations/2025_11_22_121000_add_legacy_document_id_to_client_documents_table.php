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
            $table->unsignedBigInteger('legacy_document_id')->nullable()->after('id');
            $table->unique('legacy_document_id', 'client_documents_legacy_document_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropUnique('client_documents_legacy_document_id_unique');
            $table->dropColumn('legacy_document_id');
        });
    }
};

