<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            if (!Schema::hasColumn('hearings', 'status')) {
                $table->string('status', 32)->default('pending')->after('next_hearing');
            }
            if (!Schema::hasColumn('hearings', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('last_decision');
            }
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->date('next_hearing_date')->nullable()->after('matter_end_date');
            $table->date('last_hearing_date')->nullable()->after('next_hearing_date');
            $table->text('latest_decision')->nullable()->after('last_hearing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            if (Schema::hasColumn('hearings', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('hearings', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['next_hearing_date', 'last_hearing_date', 'latest_decision']);
        });
    }
};


