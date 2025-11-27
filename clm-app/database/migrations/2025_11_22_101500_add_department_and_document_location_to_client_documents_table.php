<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->string('department')->nullable()->after('document_type');
            $table->string('admin_staff')->nullable()->after('department');
            $table->string('lawyer')->nullable()->after('admin_staff');
            $table->string('document_location')->nullable()->after('client_id');
        });

        // Backfill document_location from related clients
        DB::statement("
            UPDATE client_documents cd
            INNER JOIN clients c ON c.id = cd.client_id
            SET cd.document_location = c.documents_location
            WHERE c.documents_location IS NOT NULL
              AND c.documents_location <> ''
              AND cd.document_location IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropColumn(['department', 'admin_staff', 'lawyer', 'document_location']);
        });
    }
};

