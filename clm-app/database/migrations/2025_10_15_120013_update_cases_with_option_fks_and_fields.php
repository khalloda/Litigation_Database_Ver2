<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // New textual fields
            if (!Schema::hasColumn('cases', 'client_in_case_name')) {
                $table->string('client_in_case_name')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('cases', 'opponent_in_case_name')) {
                $table->string('opponent_in_case_name')->nullable()->after('client_in_case_name');
            }
            if (Schema::hasColumn('cases', 'matter_shelf')) {
                try {
                    $table->string('matter_shelf', 10)->nullable()->change();
                } catch (\Throwable $e) {
                    // ignore if dbal not available or change not supported
                }
            }
            if (!Schema::hasColumn('cases', 'allocated_budget')) {
                $table->text('allocated_budget')->nullable()->after('fee_letter');
            }
            if (!Schema::hasColumn('cases', 'engagement_letter_no')) {
                $table->string('engagement_letter_no')->nullable()->after('contract_id');
            }
        });

        // Add FK columns with constraints (option_values / courts / lawyers / opponents)
        Schema::table('cases', function (Blueprint $table) {
            // Option values
            if (!Schema::hasColumn('cases', 'matter_category_id')) {
                $table->unsignedBigInteger('matter_category_id')->nullable()->after('matter_category');
            }
            if (!Schema::hasColumn('cases', 'matter_degree_id')) {
                $table->unsignedBigInteger('matter_degree_id')->nullable()->after('matter_degree');
            }
            if (!Schema::hasColumn('cases', 'matter_status_id')) {
                $table->unsignedBigInteger('matter_status_id')->nullable()->after('matter_status');
            }
            if (!Schema::hasColumn('cases', 'matter_importance_id')) {
                $table->unsignedBigInteger('matter_importance_id')->nullable()->after('matter_importance');
            }
            if (!Schema::hasColumn('cases', 'matter_branch_id')) {
                $table->unsignedBigInteger('matter_branch_id')->nullable()->after('client_branch');
            }
            if (!Schema::hasColumn('cases', 'client_capacity_id')) {
                $table->unsignedBigInteger('client_capacity_id')->nullable()->after('client_and_capacity');
            }
            if (!Schema::hasColumn('cases', 'client_type_id')) {
                $table->unsignedBigInteger('client_type_id')->nullable()->after('client_type');
            }
            if (!Schema::hasColumn('cases', 'opponent_capacity_id')) {
                $table->unsignedBigInteger('opponent_capacity_id')->nullable()->after('opponent_and_capacity');
            }

            // Courts / Lawyers / Opponents
            if (!Schema::hasColumn('cases', 'matter_destination_id')) {
                $table->unsignedBigInteger('matter_destination_id')->nullable()->after('matter_destination');
            }
            if (!Schema::hasColumn('cases', 'matter_partner_id')) {
                $table->unsignedBigInteger('matter_partner_id')->nullable()->after('matter_partner');
            }
            if (!Schema::hasColumn('cases', 'opponent_id')) {
                $table->unsignedBigInteger('opponent_id')->nullable()->after('opponent_and_capacity');
            }
        });

        // Add foreign key constraints (separate to avoid errors if columns already exist)
        Schema::table('cases', function (Blueprint $table) {
            $optionFkCols = [
                'matter_category_id',
                'matter_degree_id',
                'matter_status_id',
                'matter_importance_id',
                'matter_branch_id',
                'client_capacity_id',
                'client_type_id',
                'opponent_capacity_id'
            ];
            foreach ($optionFkCols as $col) {
                if (Schema::hasColumn('cases', $col)) {
                    try {
                        $table->foreign($col)->references('id')->on('option_values')->nullOnDelete();
                    } catch (\Throwable $e) {
                        // ignore if FK exists
                    }
                }
            }
            if (Schema::hasColumn('cases', 'matter_destination_id')) {
                try {
                    $table->foreign('matter_destination_id')->references('id')->on('courts')->nullOnDelete();
                } catch (\Throwable $e) {
                }
            }
            if (Schema::hasColumn('cases', 'matter_partner_id')) {
                try {
                    $table->foreign('matter_partner_id')->references('id')->on('lawyers')->nullOnDelete();
                } catch (\Throwable $e) {
                }
            }
            if (Schema::hasColumn('cases', 'opponent_id')) {
                try {
                    $table->foreign('opponent_id')->references('id')->on('opponents')->nullOnDelete();
                } catch (\Throwable $e) {
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $fkCols = [
                'matter_category_id',
                'matter_degree_id',
                'matter_status_id',
                'matter_importance_id',
                'matter_branch_id',
                'client_capacity_id',
                'client_type_id',
                'opponent_capacity_id',
                'matter_destination_id',
                'matter_partner_id',
                'opponent_id'
            ];
            foreach ($fkCols as $col) {
                if (Schema::hasColumn('cases', $col)) {
                    try {
                        $table->dropForeign([$col]);
                    } catch (\Throwable $e) {
                    }
                }
            }
            foreach (array_reverse($fkCols) as $col) {
                if (Schema::hasColumn('cases', $col)) {
                    $table->dropColumn($col);
                }
            }

            if (Schema::hasColumn('cases', 'client_in_case_name')) {
                $table->dropColumn('client_in_case_name');
            }
            if (Schema::hasColumn('cases', 'opponent_in_case_name')) {
                $table->dropColumn('opponent_in_case_name');
            }
            if (Schema::hasColumn('cases', 'allocated_budget')) {
                $table->dropColumn('allocated_budget');
            }
            if (Schema::hasColumn('cases', 'engagement_letter_no')) {
                $table->dropColumn('engagement_letter_no');
            }
        });
    }
};
