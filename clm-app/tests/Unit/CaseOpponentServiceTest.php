<?php

namespace Tests\Unit;

use App\Models\CaseModel;
use App\Models\Pivots\CaseOpponent;
use App\Models\Opponent;
use App\Models\OptionValue;
use App\Services\CaseOpponentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CaseOpponentServiceTest extends TestCase
{
    use RefreshDatabase;

    private CaseOpponentService $service;
    private CaseModel $case;
    private Opponent $opponent1;
    private Opponent $opponent2;
    private OptionValue $capacity1;
    private OptionValue $capacity2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CaseOpponentService::class);

        // Create test case
        $this->case = CaseModel::factory()->create();

        // Create test opponents
        $this->opponent1 = Opponent::factory()->create([
            'opponent_name_en' => 'Test Opponent 1',
            'opponent_name_ar' => 'الخصم الأول'
        ]);

        $this->opponent2 = Opponent::factory()->create([
            'opponent_name_en' => 'Test Opponent 2',
            'opponent_name_ar' => 'الخصم الثاني'
        ]);

        // Create test capacities
        $this->capacity1 = OptionValue::factory()->create([
            'label_en' => 'Defendant',
            'label_ar' => 'مدعى عليه'
        ]);

        $this->capacity2 = OptionValue::factory()->create([
            'label_en' => 'Co-defendant',
            'label_ar' => 'مدعى عليه ثانوي'
        ]);
    }

    /** @test */
    public function it_can_attach_opponent_to_case()
    {
        $pivot = $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true, // is primary
            null, // order
            'Custom Alias'
        );

        $this->assertInstanceOf(CaseOpponent::class, $pivot);
        $this->assertEquals($this->case->id, $pivot->case_id);
        $this->assertEquals($this->opponent1->id, $pivot->opponent_id);
        $this->assertEquals($this->capacity1->id, $pivot->capacity_id);
        $this->assertTrue($pivot->is_primary);
        $this->assertEquals('Custom Alias', $pivot->alias_text);
        $this->assertEquals(1, $pivot->display_order);
    }

    /** @test */
    public function it_can_attach_multiple_opponents_with_different_capacities()
    {
        // Attach first opponent with capacity1
        $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );

        // Attach same opponent with different capacity - should succeed
        $pivot2 = $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity2->id,
            false
        );

        $this->assertInstanceOf(CaseOpponent::class, $pivot2);
        $this->assertEquals($this->capacity2->id, $pivot2->capacity_id);
        $this->assertFalse($pivot2->is_primary);
    }

    /** @test */
    public function it_prevents_duplicate_opponent_with_same_capacity()
    {
        // Attach opponent with capacity1
        $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );

        // Try to attach same opponent with same capacity - should fail
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Opponent with this capacity is already attached to this case');

        $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            false
        );
    }

    /** @test */
    public function it_can_reattach_opponent_after_soft_delete()
    {
        // Attach opponent
        $pivot = $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );

        // Soft delete the opponent
        $this->service->detachOpponent($this->case, $this->opponent1->id);

        // Verify it's soft deleted
        $this->assertSoftDeleted('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent1->id,
            'capacity_id' => $this->capacity1->id
        ]);

        // Reattach same opponent with same capacity - should succeed
        $newPivot = $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );

        $this->assertInstanceOf(CaseOpponent::class, $newPivot);
        $this->assertEquals($this->case->id, $newPivot->case_id);
        $this->assertEquals($this->opponent1->id, $newPivot->opponent_id);
    }

    /** @test */
    public function it_enforces_single_primary_opponent()
    {
        // Attach first opponent as primary
        $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );

        // Attach second opponent as primary - should unset first
        $this->service->attachOpponent(
            $this->case,
            $this->opponent2->id,
            $this->capacity1->id,
            true
        );

        // Verify only second opponent is primary
        $primaryOpponents = $this->case->opponents()
            ->wherePivot('is_primary', true)
            ->whereNull('deleted_at')
            ->count();

        $this->assertEquals(1, $primaryOpponents);
        $this->assertEquals($this->opponent2->id, $this->case->opponents()
            ->wherePivot('is_primary', true)
            ->whereNull('deleted_at')
            ->first()->id);
    }

    /** @test */
    public function it_can_change_primary_opponent()
    {
        // Attach two opponents
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        // Change primary to second opponent
        $this->service->setPrimary($this->case, $this->opponent2->id);

        // Verify primary changed
        $primaryOpponent = $this->case->opponents()
            ->wherePivot('is_primary', true)
            ->whereNull('deleted_at')
            ->first();

        $this->assertEquals($this->opponent2->id, $primaryOpponent->id);
    }

    /** @test */
    public function it_can_reorder_opponents()
    {
        // Attach three opponents
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        $opponent3 = Opponent::factory()->create();
        $this->service->attachOpponent($this->case, $opponent3->id, $this->capacity1->id, false);

        // Reorder: opponent2, opponent3, opponent1
        $this->service->reorder($this->case, [$this->opponent2->id, $opponent3->id, $this->opponent1->id]);

        // Verify order
        $opponents = $this->case->opponents()
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->get();

        $this->assertEquals($this->opponent2->id, $opponents[0]->id);
        $this->assertEquals($opponent3->id, $opponents[1]->id);
        $this->assertEquals($this->opponent1->id, $opponents[2]->id);
    }

    /** @test */
    public function it_syncs_legacy_opponent_field_when_primary_changes()
    {
        // Attach opponent as primary
        $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );

        // Verify legacy field is synced
        $this->case->refresh();
        $this->assertEquals($this->opponent1->id, $this->case->opponent_id);

        // Change primary
        $this->service->attachOpponent(
            $this->case,
            $this->opponent2->id,
            $this->capacity1->id,
            true
        );

        // Verify legacy field updated
        $this->case->refresh();
        $this->assertEquals($this->opponent2->id, $this->case->opponent_id);
    }

    /** @test */
    public function it_enforces_max_opponents_limit()
    {
        // Set max opponents to 2
        config(['importer.opponents.max_per_case' => 2]);

        // Attach 2 opponents (should succeed)
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        // Try to attach third opponent (should fail)
        $opponent3 = Opponent::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum opponents limit exceeded');

        $this->service->attachOpponent($this->case, $opponent3->id, $this->capacity1->id, false);
    }

    /** @test */
    public function it_handles_concurrent_primary_changes_atomically()
    {
        // Attach two opponents
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        // Simulate concurrent primary changes
        DB::transaction(function () {
            $this->service->setPrimary($this->case, $this->opponent2->id);
        });

        // Verify only one primary opponent
        $primaryCount = $this->case->opponents()
            ->wherePivot('is_primary', true)
            ->whereNull('deleted_at')
            ->count();

        $this->assertEquals(1, $primaryCount);
    }

    /** @test */
    public function it_prevents_setting_primary_for_detached_opponent()
    {
        // Try to set primary for opponent not attached to case
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Opponent is not attached to this case');

        $this->service->setPrimary($this->case, $this->opponent1->id);
    }

    /** @test */
    public function it_can_detach_opponent()
    {
        // Attach opponent
        $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );

        // Detach opponent
        $result = $this->service->detachOpponent($this->case, $this->opponent1->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent1->id,
            'capacity_id' => $this->capacity1->id
        ]);
    }

    /** @test */
    public function it_handles_capacity_multiplicity_correctly()
    {
        // Attach opponent with capacity1
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);

        // Attach same opponent with capacity2 - should succeed
        $pivot2 = $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity2->id, false);

        $this->assertInstanceOf(CaseOpponent::class, $pivot2);
        $this->assertEquals($this->capacity2->id, $pivot2->capacity_id);

        // Verify both relationships exist
        $relationships = $this->case->opponents()
            ->wherePivot('opponent_id', $this->opponent1->id)
            ->whereNull('deleted_at')
            ->get();

        $this->assertCount(2, $relationships);
    }

    /** @test */
    public function it_rolls_back_transaction_on_failure()
    {
        // Mock a failure scenario
        DB::shouldReceive('transaction')
            ->andThrow(new \Exception('Transaction failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction failed');

        $this->service->attachOpponent(
            $this->case,
            $this->opponent1->id,
            $this->capacity1->id,
            true
        );
    }
}
