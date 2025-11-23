<?php

namespace Tests\Feature\Reports;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Court;
use App\Models\Hearing;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCasesReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_a_pdf_report_for_a_client(): void
    {
        $this->seed(PermissionsSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo('reports.view');

        $client = Client::factory()->create([
            'client_name_ar' => 'تويوتا إيجيبت',
            'client_name_en' => 'Toyota Egypt',
        ]);

        $court = Court::create([
            'court_name_ar' => 'محكمة النقض',
            'court_name_en' => 'Court of Cassation',
            'is_active' => true,
        ]);

        $case = CaseModel::create([
            'client_id' => $client->id,
            'matter_name_ar' => 'القضية رقم 123/ق',
            'matter_name_en' => 'Case 123',
            'client_in_case_name' => 'تويوتا إيجيبت',
            'opponent_in_case_name' => 'اسم الخصم',
            'matter_description' => 'نص مختصر عن الدعوى.',
            'court_id' => $court->id,
            'matter_evaluation' => 'متوسط',
            'financial_provision' => '150000',
            'current_status' => 'جار المتابعة',
        ]);

        Hearing::create([
            'matter_id' => $case->id,
            'date' => now()->subDay(),
            'decision' => 'تأجيل الجلسة',
        ]);

        SnappyPdf::shouldReceive('loadView')
            ->once()
            ->andReturn(new class {
                public function setPaper(): self
                {
                    return $this;
                }

                public function download($fileName)
                {
                    return response('PDF', 200, ['Content-Type' => 'application/pdf']);
                }
            });

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/reports/client-cases/pdf', [
            'client_id' => $client->id,
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}

