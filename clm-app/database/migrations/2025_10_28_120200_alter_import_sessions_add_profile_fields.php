<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('import_sessions', 'profile_id')) {
                $table->foreignId('profile_id')->nullable()->after('transforms')->constrained('import_profiles')->nullOnDelete();
            }
            if (!Schema::hasColumn('import_sessions', 'settings_snapshot')) {
                $table->json('settings_snapshot')->nullable()->after('profile_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('import_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('import_sessions', 'settings_snapshot')) {
                $table->dropColumn('settings_snapshot');
            }
            if (Schema::hasColumn('import_sessions', 'profile_id')) {
                $table->dropConstrainedForeignId('profile_id');
            }
        });
    }
};


