<?php

namespace Tests\Unit;

use App\Models\CaseModel;
use App\Models\Hearing;
use App\Services\HearingLifecycleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HearingLifecycleServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected HearingLifecycleService $service;
    protected int $userId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(HearingLifecycleService::class);
    }

    protected function createCase(): CaseModel
    {
        return CaseModel::factory()->create([
            'matter_name_en' => 'Test case',
            'matter_name_ar' => 'قضية تجريبية',
        ]);
    }

    public function test_first_hearing_defaults_to_pending_with_no_decision_or_next_date(): void
    {
        $case = $this->createCase();

        $hearing = $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => now()->toDateString(),
        ], $this->userId);

        $this->assertEquals('pending', $hearing->status);
        $this->assertNull($hearing->completed_at);
        $this->assertNull($hearing->last_decision);
    }

    public function test_hearing_with_decision_is_marked_complete_and_stamps_completed_at(): void
    {
        $case = $this->createCase();

        $hearing = $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => now()->toDateString(),
            'decision' => 'Adjourned for pleading',
        ], $this->userId);

        $this->assertEquals('complete', $hearing->status);
        $this->assertNotNull($hearing->completed_at);
    }

    public function test_setting_next_hearing_only_marks_complete_and_auto_creates_next_pending_hearing(): void
    {
        $case = $this->createCase();
        $today = now()->toDateString();
        $nextDate = now()->addWeek()->toDateString();

        $hearing = $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => $today,
            'next_hearing_date' => $nextDate,
        ], $this->userId);

        $this->assertEquals('complete', $hearing->status);

        $next = Hearing::where('matter_id', $case->id)
            ->whereDate('date', $nextDate)
            ->first();

        $this->assertNotNull($next);
        $this->assertEquals('pending', $next->status);
        $this->assertEquals($hearing->decision, $next->last_decision);
    }

    public function test_last_decision_is_taken_from_previous_hearing(): void
    {
        $case = $this->createCase();

        $h1 = $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => now()->subDays(10)->toDateString(),
            'decision' => 'Adjourned for documents',
        ], $this->userId);

        $h2 = $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => now()->subDays(5)->toDateString(),
        ], $this->userId);

        $this->assertNull($h1->last_decision);
        $this->assertEquals('Adjourned for documents', $h2->last_decision);
    }

    public function test_overlapping_pending_hearings_on_same_date_are_rejected(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $case = $this->createCase();
        $date = now()->toDateString();

        $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => $date,
        ], $this->userId);

        // Second pending on same date should fail
        $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => $date,
        ], $this->userId);
    }

    public function test_case_summary_fields_are_refreshed_after_hearings_changes(): void
    {
        $case = $this->createCase();

        $h1 = $this->service->createHearing([
            'case_id' => $case->id,
            'hearing_date' => now()->subDays(7)->toDateString(),
            'decision' => 'Adjourned',
            'next_hearing_date' => now()->addDays(7)->toDateString(),
        ], $this->userId);

        $case->refresh();

        $this->assertEquals(
            $h1->next_hearing?->toDateString(),
            optional($case->next_hearing_date)->toDateString()
        );
        $this->assertEquals(
            $h1->date?->toDateString(),
            optional($case->last_hearing_date)->toDateString()
        );
        $this->assertEquals('Adjourned', $case->latest_decision);
    }
}


