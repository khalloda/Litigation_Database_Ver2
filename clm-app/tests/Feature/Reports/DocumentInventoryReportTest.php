<?php

namespace Tests\Feature\Reports;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DocumentInventoryReportTest extends TestCase
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
        $response = $this->postJson('/api/reports/document-inventory/pdf');

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_requires_reports_view_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/pdf');

        $response->assertForbidden();
    }

    /** @test */
    public function it_generates_a_pdf_report_with_documents(): void
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

        ClientDocument::create([
            'client_id' => $client->id,
            'matter_id' => $case->id,
            'document_name' => 'مستند اختبار',
            'document_type' => 'عقد',
            'document_storage_type' => 'physical',
            'document_location' => 'الخزنة A',
            'deposit_date' => now(),
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')->once()->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/pdf', []);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_filters_documents_by_client(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();

        $case1 = CaseModel::create([
            'client_id' => $client1->id,
            'matter_name_ar' => 'قضية 1',
            'matter_status' => 'سارية',
        ]);
        $case2 = CaseModel::create([
            'client_id' => $client2->id,
            'matter_name_ar' => 'قضية 2',
            'matter_status' => 'سارية',
        ]);

        ClientDocument::create([
            'client_id' => $client1->id,
            'matter_id' => $case1->id,
            'document_name' => 'مستند 1',
            'document_type' => 'عقد',
            'document_storage_type' => 'physical',
        ]);
        ClientDocument::create([
            'client_id' => $client2->id,
            'matter_id' => $case2->id,
            'document_name' => 'مستند 2',
            'document_type' => 'عقد',
            'document_storage_type' => 'physical',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.document_inventory_pdf', Mockery::on(function ($data) use ($client1) {
                return isset($data['documents']) 
                    && $data['documents']->count() === 1
                    && $data['documents']->first()->client_id === $client1->id;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/pdf', [
                'client_id' => $client1->id,
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_filters_documents_by_storage_type(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية',
            'matter_status' => 'سارية',
        ]);

        ClientDocument::create([
            'client_id' => $client->id,
            'matter_id' => $case->id,
            'document_name' => 'مستند مادي',
            'document_type' => 'عقد',
            'document_storage_type' => 'physical',
        ]);
        ClientDocument::create([
            'client_id' => $client->id,
            'matter_id' => $case->id,
            'document_name' => 'مستند رقمي',
            'document_type' => 'عقد',
            'document_storage_type' => 'digital',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.document_inventory_pdf', Mockery::on(function ($data) {
                return isset($data['documents']) 
                    && $data['documents']->every(fn($doc) => $doc->document_storage_type === 'physical');
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/pdf', [
                'storage_type' => 'physical',
            ]);

        $response->assertOk();
    }

    /** @test */
    public function it_detects_missing_documents(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        
        // Create a case without documents
        $caseWithoutDocs = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية بدون مستندات',
            'matter_name_en' => 'Case Without Documents',
            'matter_status' => 'سارية',
            'matter_description' => 'Test',
        ]);

        $mockPdf = Mockery::mock();
        $mockPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $mockPdf->shouldReceive('setOption')->times(7)->andReturnSelf();
        $mockPdf->shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->with('reports.document_inventory_pdf', Mockery::on(function ($data) {
                return isset($data['missingDocuments']) 
                    && $data['missingDocuments']->count() > 0;
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/pdf', [
                'show_missing' => true,
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

        ClientDocument::create([
            'client_id' => $client->id,
            'matter_id' => $case->id,
            'document_name' => 'مستند اختبار',
            'document_type' => 'عقد',
            'document_storage_type' => 'physical',
            'deposit_date' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/excel', []);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('xlsx', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function it_supports_grouping_by_location_in_excel(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create();
        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'قضية',
            'matter_status' => 'سارية',
        ]);

        ClientDocument::create([
            'client_id' => $client->id,
            'matter_id' => $case->id,
            'document_name' => 'مستند',
            'document_type' => 'عقد',
            'document_storage_type' => 'physical',
            'document_location' => 'الخزنة A',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/excel', [
                'group_by' => 'location',
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
            ->with('reports.document_inventory_pdf', Mockery::on(function ($data) {
                return isset($data['documents']) && $data['documents']->isEmpty();
            }))
            ->andReturn($mockPdf);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/reports/document-inventory/pdf', []);

        $response->assertOk();
    }
}

