<?php

namespace Tests\Feature\Reports;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class HearingScheduleReportTest extends TestCase
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
        $response = $this->postJson('/api/reports/hearing-schedule/pdf');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_requires_reports_view_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/hearing-schedule/pdf');

        $response->assertForbidden();
    }

    /** @test */
    public function it_generates_a_pdf_report_with_hearings(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $court = Court::create([
            'court_name_ar' => 'محكمة النقض',
            'court_name_en' => 'Court of Cassation',
            'is_active' => true,
        ]);
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية اختبار',
            'matter_name_en' => 'Test Case',
            'court_id' => $court->id,
            'matter_status' => 'سارية',
        ]);

        Hearing::create([
            'matter_id' => $case->id,
            'date' => now()->addDay(),
            'procedure' => 'جلسة استماع',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')->once()->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/hearing-schedule/pdf', [
                'date_range_type' => 'this_month',
            ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_filters_hearings_by_court(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $court1 = Court::create([
            'court_name_ar' => 'محكمة أولى',
            'court_name_en' => 'First Court',
            'is_active' => true,
        ]);
        $court2 = Court::create([
            'court_name_ar' => 'محكمة ثانية',
            'court_name_en' => 'Second Court',
            'is_active' => true,
        ]);

        $case1 = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية 1',
            'court_id' => $court1->id,
            'matter_status' => 'سارية',
        ]);
        $case2 = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية 2',
            'court_id' => $court2->id,
            'matter_status' => 'سارية',
        ]);

        Hearing::create(['matter_id' => $case1->id, 'date' => now()->addDay()]);
        Hearing::create(['matter_id' => $case2->id, 'date' => now()->addDay()]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.hearing_schedule_pdf', Mockery::on(function ($data) use ($court1) {
                return isset($data['hearings']) 
                    && $data['hearings']->count() === 1
                    && $data['hearings']->first()->case->court_id === $court1->id;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/hearing-schedule/pdf', [
                'date_range_type' => 'this_month',
                'court_id' => $court1->id,
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_generates_an_excel_report(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية اختبار',
            'matter_name_en' => 'Test Case',
            'matter_status' => 'سارية',
        ]);

        Hearing::create([
            'matter_id' => $case->id,
            'date' => now()->addDay(),
            'procedure' => 'جلسة استماع',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/hearing-schedule/excel', [
                'date_range_type' => 'this_month',
            ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function it_detects_overdue_hearings(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية',
            'matter_status' => 'سارية',
        ]);

        // Create overdue hearing (past date, no decision)
        Hearing::create([
            'matter_id' => $case->id,
            'date' => now()->subDays(5),
            'procedure' => 'جلسة',
            'decision' => null,
            'short_decision' => null,
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.hearing_schedule_pdf', Mockery::on(function ($data) {
                return isset($data['overdue']) && $data['overdue']->count() > 0;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/hearing-schedule/pdf', [
                'date_range_type' => 'all',
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
            ->with('reports.hearing_schedule_pdf', Mockery::on(function ($data) {
                return isset($data['hearings']) && $data['hearings']->isEmpty();
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/hearing-schedule/pdf', [
                'date_range_type' => 'this_month',
            ]);

        $response->assertOk();
    }
}

