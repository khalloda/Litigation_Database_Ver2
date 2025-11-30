<?php

namespace Tests\Feature\Reports;

use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CaseStatusDashboardReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionsSeeder::class);
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $response = $this->postJson('/api/reports/case-status-dashboard/pdf');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_requires_reports_view_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/case-status-dashboard/pdf');

        $response->assertForbidden();
    }

    /** @test */
    public function it_generates_a_pdf_dashboard(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $court = Court::create([
            'court_name_ar' => 'محكمة',
            'court_name_en' => 'Court',
            'is_active' => true,
        ]);

        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية سارية',
            'matter_name_en' => 'Active Case',
            'matter_status' => 'سارية',
            'court_id' => $court->id,
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')->once()->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/case-status-dashboard/pdf', []);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_calculates_statistics_correctly(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();

        // Create 3 active cases
        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية سارية 1',
            'matter_status' => 'سارية',
        ]);
        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية سارية 2',
            'matter_status' => 'سارية',
        ]);
        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية سارية 3',
            'matter_status' => 'سارية',
        ]);

        // Create 2 closed cases
        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية منتهية 1',
            'matter_status' => 'منتهية',
        ]);
        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية منتهية 2',
            'matter_status' => 'منتهية',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.case_status_dashboard_pdf', Mockery::on(function ($data) {
                // Should have 5 total, 3 active, 2 closed
                return isset($data['stats'])
                    && $data['stats']['total'] >= 5
                    && $data['stats']['active'] >= 3
                    && $data['stats']['closed'] >= 2;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/case-status-dashboard/pdf', [
                'status' => 'all',
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_filters_by_status(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();

        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية سارية',
            'matter_status' => 'سارية',
        ]);
        CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية منتهية',
            'matter_status' => 'منتهية',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.case_status_dashboard_pdf', Mockery::on(function ($data) {
                // Should only have active cases
                return isset($data['cases'])
                    && $data['cases']->every(fn($case) => $case->matter_status === 'سارية');
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/case-status-dashboard/pdf', [
                'status' => 'active',
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_detects_cases_requiring_attention(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية تحتاج متابعة',
            'matter_status' => 'سارية',
        ]);

        // Create overdue task to trigger attention-required
        AdminTask::create([
            'matter_id' => $case->id,
            'required_work' => 'عمل متأخر',
            'execution_date' => now()->subDays(5),
            'result' => null,
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.case_status_dashboard_pdf', Mockery::on(function ($data) {
                return isset($data['attentionRequired']) 
                    && $data['attentionRequired']->count() > 0;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/case-status-dashboard/pdf', [
                'show_attention_required' => true,
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_shows_recent_activity(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية نشطة',
            'matter_status' => 'سارية',
        ]);

        // Create recent hearing
        Hearing::create([
            'matter_id' => $case->id,
            'date' => now()->subDay(),
            'procedure' => 'جلسة',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.case_status_dashboard_pdf', Mockery::on(function ($data) {
                return isset($data['recentActivity']) 
                    && $data['recentActivity']->count() > 0;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/case-status-dashboard/pdf', [
                'show_recent_activity' => true,
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_handles_empty_results_gracefully(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.case_status_dashboard_pdf', Mockery::on(function ($data) {
                return isset($data['stats']) && $data['stats']['total'] === 0;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/case-status-dashboard/pdf', []);

        $response->assertOk();
    }
}

