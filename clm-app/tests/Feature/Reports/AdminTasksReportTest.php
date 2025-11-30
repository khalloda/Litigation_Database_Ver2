<?php

namespace Tests\Feature\Reports;

use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Lawyer;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AdminTasksReportTest extends TestCase
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
        $response = $this->postJson('/api/reports/admin-tasks/pdf');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_requires_reports_view_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/pdf');

        $response->assertForbidden();
    }

    /** @test */
    public function it_generates_a_pdf_report_with_tasks(): void
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

        AdminTask::create([
            'matter_id' => $case->id,
            'required_work' => 'عمل مطلوب',
            'execution_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')->once()->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/pdf', [
                'date_range_type' => 'this_month',
            ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_filters_tasks_by_lawyer(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $lawyer1 = Lawyer::create([
            'lawyer_name_ar' => 'محامي 1',
            'lawyer_name_en' => 'Lawyer 1',
        ]);
        $lawyer2 = Lawyer::create([
            'lawyer_name_ar' => 'محامي 2',
            'lawyer_name_en' => 'Lawyer 2',
        ]);

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية',
            'matter_status' => 'سارية',
        ]);

        AdminTask::create([
            'matter_id' => $case->id,
            'lawyer_id' => $lawyer1->id,
            'required_work' => 'عمل 1',
            'execution_date' => now()->addDay(),
        ]);

        AdminTask::create([
            'matter_id' => $case->id,
            'lawyer_id' => $lawyer2->id,
            'required_work' => 'عمل 2',
            'execution_date' => now()->addDay(),
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.admin_tasks_pdf', Mockery::on(function ($data) use ($lawyer1) {
                return isset($data['tasks']) 
                    && $data['tasks']->count() === 1
                    && $data['tasks']->first()->lawyer_id === $lawyer1->id;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/pdf', [
                'date_range_type' => 'this_month',
                'lawyer_id' => $lawyer1->id,
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_detects_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية',
            'matter_status' => 'سارية',
        ]);

        // Create overdue task (past execution date, no result)
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
            ->with('reports.admin_tasks_pdf', Mockery::on(function ($data) {
                return isset($data['overdue']) && $data['overdue']->count() > 0;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/pdf', [
                'date_range_type' => 'all',
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_calculates_completion_rate(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية',
            'matter_status' => 'سارية',
        ]);

        // Create 3 completed tasks and 1 pending
        AdminTask::create([
            'matter_id' => $case->id,
            'required_work' => 'عمل 1',
            'execution_date' => now()->addDay(),
            'result' => 'مكتمل',
        ]);
        AdminTask::create([
            'matter_id' => $case->id,
            'required_work' => 'عمل 2',
            'execution_date' => now()->addDay(),
            'result' => 'مكتمل',
        ]);
        AdminTask::create([
            'matter_id' => $case->id,
            'required_work' => 'عمل 3',
            'execution_date' => now()->addDay(),
            'result' => 'مكتمل',
        ]);
        AdminTask::create([
            'matter_id' => $case->id,
            'required_work' => 'عمل 4',
            'execution_date' => now()->addDay(),
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
            ->with('reports.admin_tasks_pdf', Mockery::on(function ($data) {
                // 3 completed out of 4 = 75% completion rate
                return isset($data['completionRate']) 
                    && abs($data['completionRate'] - 75.0) < 0.1;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/pdf', [
                'date_range_type' => 'all',
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

        AdminTask::create([
            'matter_id' => $case->id,
            'required_work' => 'عمل مطلوب',
            'execution_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/excel', [
                'date_range_type' => 'this_month',
            ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function it_supports_grouping_by_lawyer_in_excel(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $lawyer = Lawyer::create([
            'lawyer_name_ar' => 'محامي',
            'lawyer_name_en' => 'Lawyer',
        ]);

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية',
            'matter_status' => 'سارية',
        ]);

        AdminTask::create([
            'matter_id' => $case->id,
            'lawyer_id' => $lawyer->id,
            'required_work' => 'عمل',
            'execution_date' => now()->addDay(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/excel', [
                'date_range_type' => 'this_month',
                'group_by' => 'lawyer',
            ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
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
            ->with('reports.admin_tasks_pdf', Mockery::on(function ($data) {
                return isset($data['tasks']) && $data['tasks']->isEmpty();
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/admin-tasks/pdf', [
                'date_range_type' => 'this_month',
            ]);

        $response->assertOk();
    }
}

