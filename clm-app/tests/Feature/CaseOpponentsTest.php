<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Models\CaseOpponent;
use App\Models\Opponent;
use App\Models\OptionValue;
use App\Models\User;
use App\Services\CaseOpponentService;
use App\Services\DeletionBundleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CaseOpponentsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CaseModel $case;
    private Opponent $opponent1;
    private Opponent $opponent2;
    private OptionValue $capacity1;
    private OptionValue $capacity2;
    private CaseOpponentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with permissions
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'cases.opponents.view',
            'cases.opponents.edit',
            'cases.opponents.attach',
            'cases.opponents.detach'
        ]);

        $this->actingAs($this->user);

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

        $this->service = app(CaseOpponentService::class);
    }

    /** @test */
    public function it_can_view_opponents_for_case()
    {
        // Attach opponents to case
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        $response = $this->get(route('case-opponents.index', $this->case));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'opponents' => [
                '*' => [
                    'id',
                    'name_en',
                    'name_ar',
                    'capacity',
                    'is_primary',
                    'display_order'
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_add_opponent_via_ajax()
    {
        $response = $this->postJson(route('case-opponents.store', $this->case), [
            'opponent_id' => $this->opponent1->id,
            'capacity_id' => $this->capacity1->id,
            'alias_text' => 'Custom Alias',
            'is_primary' => true
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('app.opponent_added_successfully')
        ]);

        // Verify opponent was attached
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent1->id,
            'capacity_id' => $this->capacity1->id,
            'is_primary' => true,
            'alias_text' => 'Custom Alias'
        ]);
    }

    /** @test */
    public function it_can_remove_opponent_via_ajax()
    {
        // Attach opponent first
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);

        $response = $this->deleteJson(route('case-opponents.destroy', $this->case), [
            'opponent_id' => $this->opponent1->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('app.opponent_removed_successfully')
        ]);

        // Verify opponent was soft deleted
        $this->assertSoftDeleted('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent1->id
        ]);
    }

    /** @test */
    public function it_can_set_primary_opponent_via_ajax()
    {
        // Attach two opponents
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        $response = $this->postJson(route('case-opponents.set-primary', $this->case), [
            'opponent_id' => $this->opponent2->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('app.primary_opponent_updated')
        ]);

        // Verify primary changed
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent2->id,
            'is_primary' => true
        ]);

        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent1->id,
            'is_primary' => false
        ]);
    }

    /** @test */
    public function it_can_reorder_opponents_via_ajax()
    {
        // Attach three opponents
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        $opponent3 = Opponent::factory()->create();
        $this->service->attachOpponent($this->case, $opponent3->id, $this->capacity1->id, false);

        $response = $this->postJson(route('case-opponents.reorder', $this->case), [
            'opponent_ids' => [$this->opponent2->id, $opponent3->id, $this->opponent1->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('app.opponents_reordered_successfully')
        ]);

        // Verify order changed
        $opponents = $this->case->opponents()
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->get();

        $this->assertEquals($this->opponent2->id, $opponents[0]->id);
        $this->assertEquals($opponent3->id, $opponents[1]->id);
        $this->assertEquals($this->opponent1->id, $opponents[2]->id);
    }

    /** @test */
    public function it_can_search_opponents_for_modal()
    {
        $response = $this->getJson(route('opponents.search', ['q' => 'Test Opponent 1']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'opponent_name_en',
                'opponent_name_ar'
            ]
        ]);
    }

    /** @test */
    public function it_validates_opponent_attachment_data()
    {
        $response = $this->postJson(route('case-opponents.store', $this->case), [
            'opponent_id' => 999, // Non-existent opponent
            'capacity_id' => $this->capacity1->id
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['opponent_id']);
    }

    /** @test */
    public function it_prevents_duplicate_opponent_attachment()
    {
        // Attach opponent first
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);

        // Try to attach same opponent with same capacity
        $response = $this->postJson(route('case-opponents.store', $this->case), [
            'opponent_id' => $this->opponent1->id,
            'capacity_id' => $this->capacity1->id
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Opponent with this capacity is already attached to this case. Same opponent can have different capacities, but not the same capacity.'
        ]);
    }

    /** @test */
    public function it_enforces_permissions_for_opponent_management()
    {
        // Create user without permissions
        $unauthorizedUser = User::factory()->create();
        $this->actingAs($unauthorizedUser);

        $response = $this->postJson(route('case-opponents.store', $this->case), [
            'opponent_id' => $this->opponent1->id,
            'capacity_id' => $this->capacity1->id
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_import_case_opponents_via_companion_import()
    {
        Storage::fake('local');

        // Create CSV file for companion import
        $csvContent = "case_id,opponent_id,capacity_id,is_primary,display_order,alias_text\n";
        $csvContent .= "{$this->case->id},{$this->opponent1->id},{$this->capacity1->id},1,1,Primary Opponent\n";
        $csvContent .= "{$this->case->id},{$this->opponent2->id},{$this->capacity1->id},0,2,Secondary Opponent\n";

        $file = UploadedFile::fake()->createWithContent('case_opponents.csv', $csvContent);

        $response = $this->post(route('import.case-opponents.upload'), [
            'file' => $file,
            'table_name' => 'case_opponents'
        ]);

        $response->assertStatus(302); // Redirect after successful import

        // Verify opponents were attached
        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent1->id,
            'is_primary' => true,
            'alias_text' => 'Primary Opponent'
        ]);

        $this->assertDatabaseHas('case_opponents', [
            'case_id' => $this->case->id,
            'opponent_id' => $this->opponent2->id,
            'is_primary' => false,
            'alias_text' => 'Secondary Opponent'
        ]);
    }

    /** @test */
    public function it_can_export_case_opponents_template()
    {
        $response = $this->get(route('import.case-opponents.template.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Case_Opponents_Import_Template.csv"');
    }

    /** @test */
    public function it_can_export_case_opponents_xlsx_template()
    {
        $response = $this->get(route('import.case-opponents.template.xlsx'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="Case_Opponents_Import_Template.xlsx"');
    }

    /** @test */
    public function it_captures_opponents_in_deletion_bundle()
    {
        // Attach opponents to case
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        // Create deletion bundle
        $deletionService = app(DeletionBundleService::class);
        $bundleId = $deletionService->createBundle($this->case, 'Test deletion');

        // Verify bundle contains opponents
        $bundle = \App\Models\DeletionBundle::find($bundleId);
        $snapshot = $bundle->snapshot_json;

        $this->assertArrayHasKey('case_opponents', $snapshot);
        $this->assertCount(2, $snapshot['case_opponents']);
    }

    /** @test */
    public function it_can_restore_opponents_from_deletion_bundle()
    {
        // Attach opponents to case
        $this->service->attachOpponent($this->case, $this->opponent1->id, $this->capacity1->id, true);
        $this->service->attachOpponent($this->case, $this->opponent2->id, $this->capacity1->id, false);

        // Create deletion bundle
        $deletionService = app(DeletionBundleService::class);
        $bundleId = $deletionService->createBundle($this->case, 'Test deletion');

        // Soft delete the case
        $this->case->delete();

        // Restore from bundle
        $report = $deletionService->restoreBundle($bundleId);

        $this->assertArrayHasKey('restored', $report);
        $this->assertCount(1, $report['restored']); // Case restored

        // Verify opponents were restored
        $restoredCase = CaseModel::withTrashed()->find($this->case->id);
        $this->assertNotNull($restoredCase);
        $this->assertCount(2, $restoredCase->opponents()->whereNull('deleted_at')->get());
    }

    /** @test */
    public function it_handles_concurrent_opponent_operations()
    {
        // Simulate concurrent operations
        $promises = [];

        // Start multiple concurrent operations
        for ($i = 0; $i < 5; $i++) {
            $opponent = Opponent::factory()->create();
            $isPrimary = $i === 0; // First one is primary
            $promises[] = function () use ($opponent, $isPrimary) {
                return $this->service->attachOpponent(
                    $this->case,
                    $opponent->id,
                    $this->capacity1->id,
                    $isPrimary
                );
            };
        }

        // Execute all operations
        foreach ($promises as $promise) {
            $promise();
        }

        // Verify all opponents were attached
        $opponents = $this->case->opponents()->whereNull('deleted_at')->get();
        $this->assertCount(5, $opponents);

        // Verify only one primary
        $primaryCount = $opponents->where('pivot.is_primary', true)->count();
        $this->assertEquals(1, $primaryCount);
    }

    /** @test */
    public function it_validates_max_opponents_limit_in_import()
    {
        // Set max opponents to 2
        config(['importer.opponents.max_per_case' => 2]);

        Storage::fake('local');

        // Create CSV with 3 opponents
        $csvContent = "case_id,opponent_id,capacity_id,is_primary,display_order\n";
        $csvContent .= "{$this->case->id},{$this->opponent1->id},{$this->capacity1->id},1,1\n";
        $csvContent .= "{$this->case->id},{$this->opponent2->id},{$this->capacity1->id},0,2\n";

        $opponent3 = Opponent::factory()->create();
        $csvContent .= "{$this->case->id},{$opponent3->id},{$this->capacity1->id},0,3\n";

        $file = UploadedFile::fake()->createWithContent('case_opponents.csv', $csvContent);

        $response = $this->post(route('import.case-opponents.upload'), [
            'file' => $file,
            'table_name' => 'case_opponents'
        ]);

        // Should fail due to max opponents limit
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_handles_opponent_search_with_arabic_text()
    {
        // Create opponent with Arabic name
        $arabicOpponent = Opponent::factory()->create([
            'opponent_name_ar' => 'شركة النيل للاستثمار',
            'opponent_name_en' => 'Nile Investment Company'
        ]);

        // Search with Arabic text
        $response = $this->getJson(route('opponents.search', ['q' => 'النيل']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $arabicOpponent->id,
            'opponent_name_ar' => 'شركة النيل للاستثمار'
        ]);
    }

    /** @test */
    public function it_handles_opponent_search_with_english_text()
    {
        // Search with English text
        $response = $this->getJson(route('opponents.search', ['q' => 'Test Opponent 1']));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $this->opponent1->id,
            'opponent_name_en' => 'Test Opponent 1'
        ]);
    }
}
