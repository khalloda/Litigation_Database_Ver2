<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\Pivots\CaseOpponent;
use App\Models\Opponent;
use App\Models\OptionValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CaseOpponentBackfillTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_run_backfill_migration()
    {
        // Create test cases with existing opponent data
        $case1 = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1
        ]);

        $case2 = CaseModel::factory()->create([
            'opponent_id' => 2,
            'opponent_capacity_id' => 2
        ]);

        $case3 = CaseModel::factory()->create([
            'opponent_id' => null, // No opponent
            'opponent_capacity_id' => null
        ]);

        // Create opponents
        $opponent1 = Opponent::factory()->create(['id' => 1]);
        $opponent2 = Opponent::factory()->create(['id' => 2]);

        // Create capacities
        $capacity1 = OptionValue::factory()->create(['id' => 1]);
        $capacity2 = OptionValue::factory()->create(['id' => 2]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify case_opponents records were created
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $case1->id,
            'opponent_id' => 1,
            'capacity_id' => 1,
            'is_primary' => true,
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $case2->id,
            'opponent_id' => 2,
            'capacity_id' => 2,
            'is_primary' => true,
            'display_order' => 1
        ]);

        // Verify case without opponent was not processed
        $this->assertDatabaseMissing('case_opponents', [
            'case_id' => $case3->id
        ]);
    }

    /** @test */
    public function it_handles_cases_with_null_capacity()
    {
        // Create case with opponent but no capacity
        $case = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => null
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify record was created with null capacity
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $case->id,
            'opponent_id' => 1,
            'capacity_id' => null,
            'is_primary' => true,
            'display_order' => 1
        ]);
    }

    /** @test */
    public function it_skips_deleted_cases()
    {
        // Create case and then soft delete it
        $case = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1
        ]);

        $case->delete(); // Soft delete

        $opponent = Opponent::factory()->create(['id' => 1]);
        $capacity = OptionValue::factory()->create(['id' => 1]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify deleted case was not processed
        $this->assertDatabaseMissing('case_opponents', [
            'case_id' => $case->id
        ]);
    }

    /** @test */
    public function it_handles_duplicate_opponent_ids()
    {
        // Create cases with same opponent
        $case1 = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1
        ]);

        $case2 = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 2
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);
        $capacity1 = OptionValue::factory()->create(['id' => 1]);
        $capacity2 = OptionValue::factory()->create(['id' => 2]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify both cases got the opponent
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $case1->id,
            'opponent_id' => 1,
            'capacity_id' => 1
        ]);

        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $case2->id,
            'opponent_id' => 1,
            'capacity_id' => 2
        ]);
    }

    /** @test */
    public function it_preserves_audit_fields()
    {
        // Create case with audit fields
        $case = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1,
            'created_by' => 1,
            'updated_by' => 2
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);
        $capacity = OptionValue::factory()->create(['id' => 1]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify audit fields were preserved
        $caseOpponent = CaseOpponent::where('case_id', $case->id)->first();
        $this->assertEquals(1, $caseOpponent->created_by);
        $this->assertEquals(2, $caseOpponent->updated_by);
        $this->assertNotNull($caseOpponent->created_at);
        $this->assertNotNull($caseOpponent->updated_at);
    }

    /** @test */
    public function it_can_rollback_backfill_migration()
    {
        // Create test data
        $case = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);
        $capacity = OptionValue::factory()->create(['id' => 1]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify record was created
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $case->id,
            'opponent_id' => 1
        ]);

        // Rollback the migration
        $this->artisan('migrate:rollback', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify record was removed
        $this->assertDatabaseMissing('case_opponents', [
            'case_id' => $case->id,
            'opponent_id' => 1
        ]);
    }

    /** @test */
    public function it_handles_large_datasets_efficiently()
    {
        // Create 100 cases with opponents
        $cases = CaseModel::factory()->count(100)->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);
        $capacity = OptionValue::factory()->create(['id' => 1]);

        // Run the backfill migration
        $startTime = microtime(true);
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);
        $endTime = microtime(true);

        // Verify all records were created
        $this->assertDatabaseCount('case_opponents', 100);

        // Verify migration completed in reasonable time (less than 5 seconds)
        $this->assertLessThan(5, $endTime - $startTime);
    }

    /** @test */
    public function it_logs_migration_results()
    {
        // Create test data
        $case = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);
        $capacity = OptionValue::factory()->create(['id' => 1]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify migration was logged
        $this->assertDatabaseHas('migrations', [
            'migration' => '2025_10_25_104241_backfill_case_opponents_from_legacy'
        ]);
    }

    /** @test */
    public function it_handles_missing_opponents_gracefully()
    {
        // Create case with non-existent opponent
        $case = CaseModel::factory()->create([
            'opponent_id' => 999, // Non-existent
            'opponent_capacity_id' => 1
        ]);

        $capacity = OptionValue::factory()->create(['id' => 1]);

        // Run the backfill migration (should not fail)
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify no record was created for non-existent opponent
        $this->assertDatabaseMissing('case_opponents', [
            'case_id' => $case->id,
            'opponent_id' => 999
        ]);
    }

    /** @test */
    public function it_handles_missing_capacities_gracefully()
    {
        // Create case with non-existent capacity
        $case = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 999 // Non-existent
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);

        // Run the backfill migration (should not fail)
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify record was created with null capacity
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $case->id,
            'opponent_id' => 1,
            'capacity_id' => null
        ]);
    }

    /** @test */
    public function it_verifies_data_integrity_after_backfill()
    {
        // Create test data
        $case = CaseModel::factory()->create([
            'opponent_id' => 1,
            'opponent_capacity_id' => 1
        ]);

        $opponent = Opponent::factory()->create(['id' => 1]);
        $capacity = OptionValue::factory()->create(['id' => 1]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify data integrity
        $caseOpponent = CaseOpponent::where('case_id', $case->id)->first();

        $this->assertNotNull($caseOpponent);
        $this->assertEquals($case->id, $caseOpponent->case_id);
        $this->assertEquals(1, $caseOpponent->opponent_id);
        $this->assertEquals(1, $caseOpponent->capacity_id);
        $this->assertTrue($caseOpponent->is_primary);
        $this->assertEquals(1, $caseOpponent->display_order);
        $this->assertNull($caseOpponent->alias_text);
        $this->assertNull($caseOpponent->deleted_at);
    }

    /** @test */
    public function it_handles_empty_cases_table()
    {
        // Run the backfill migration with no cases
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify no records were created
        $this->assertDatabaseCount('case_opponents', 0);
    }

    /** @test */
    public function it_handles_cases_without_opponents()
    {
        // Create cases without opponents
        CaseModel::factory()->count(5)->create([
            'opponent_id' => null,
            'opponent_capacity_id' => null
        ]);

        // Run the backfill migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_25_104241_backfill_case_opponents_from_legacy.php']);

        // Verify no records were created
        $this->assertDatabaseCount('case_opponents', 0);
    }
}
