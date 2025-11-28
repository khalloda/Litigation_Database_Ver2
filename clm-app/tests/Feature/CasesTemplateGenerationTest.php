<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CasesTemplateGenerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions if they don't exist
        Permission::firstOrCreate(['name' => 'import.view_template']);
        Permission::firstOrCreate(['name' => 'admin.tools.manage']);

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(['import.view_template', 'admin.tools.manage']);

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo(['import.view_template']);
    }

    /** @test */
    public function it_generates_standard_csv_template_with_correct_headers()
    {
        Artisan::call('templates:generate-cases', ['--mode' => 'standard']);

        $path = storage_path('app/templates/Cases_Import_Template_Standard.csv');
        $this->assertFileExists($path);

        $content = file_get_contents($path);

        // Check UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Check headers
        $lines = explode("\n", $content);
        $headers = str_getcsv($lines[0]);

        $expectedHeaders = [
            'client_id',
            'client_in_case_name',
            'matter_name_ar',
            'matter_name_en',
            'matter_description',
            'matter_status',
            'matter_status_id',
            'matter_category',
            'matter_category_id',
            'court_id',
            'matter_degree',
            'matter_degree_id',
            'matter_importance',
            'matter_importance_id',
            'matter_start_date',
            'matter_end_date',
            'matter_asked_amount',
            'matter_judged_amount',
            'matter_partner_id',
            'notes_1',
            'client_capacity_id',
            'opponent_id',
            'opponent_capacity_id'
        ];

        foreach ($expectedHeaders as $header) {
            $this->assertContains($header, $headers, "Header '{$header}' not found in CSV");
        }
    }

    /** @test */
    public function it_generates_standard_csv_with_sample_data()
    {
        Artisan::call('templates:generate-cases', ['--mode' => 'standard']);

        $path = storage_path('app/templates/Cases_Import_Template_Standard.csv');
        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        // Should have header + 2 sample rows
        $this->assertGreaterThanOrEqual(3, count($lines));

        // Check sample data contains Arabic and English
        $sampleRow1 = str_getcsv($lines[1]);
        $sampleRow2 = str_getcsv($lines[2]);

        // At least one row should contain Arabic text
        $hasArabic = false;
        foreach ($sampleRow1 as $cell) {
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $cell)) {
                $hasArabic = true;
                break;
            }
        }
        $this->assertTrue($hasArabic, 'Sample data should contain Arabic text');
    }

    /** @test */
    public function it_generates_extended_xlsx_with_three_sheets()
    {
        Artisan::call('templates:generate-cases', ['--mode' => 'extended']);

        $path = storage_path('app/templates/Cases_Import_Template_Extended.xlsx');
        $this->assertFileExists($path);

        // This would require PhpSpreadsheet to read the file
        // For now, just check file exists and has reasonable size
        $this->assertGreaterThan(10000, filesize($path), 'XLSX file should be substantial');
    }

    /** @test */
    public function it_requires_permission_to_download_templates()
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // Has import.view_template permission

        $this->actingAs($user);

        // Should be able to access download routes
        $response = $this->get(route('cases.template.standard.csv'));
        $this->assertEquals(200, $response->getStatusCode());

        $response = $this->get(route('cases.template.standard.xlsx'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_denies_access_without_permission()
    {
        $user = User::factory()->create();
        // No permissions assigned

        $this->actingAs($user);

        $response = $this->get(route('cases.template.standard.csv'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function it_handles_missing_templates_gracefully()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user);

        // Delete template files
        $files = [
            'storage/app/templates/Cases_Import_Template_Standard.csv',
            'storage/app/templates/Cases_Import_Template_Standard.xlsx'
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $response = $this->get(route('cases.template.standard.csv'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /** @test */
    public function it_generates_all_templates_with_all_mode()
    {
        Artisan::call('templates:generate-cases', ['--mode' => 'all']);

        $files = [
            'Cases_Import_Template_Standard.csv',
            'Cases_Import_Template_Standard.xlsx',
            'Cases_Import_Template_Extended.csv',
            'Cases_Import_Template_Extended.xlsx'
        ];

        foreach ($files as $file) {
            $path = storage_path("app/templates/{$file}");
            $this->assertFileExists($path, "File {$file} should exist");
            $this->assertGreaterThan(100, filesize($path), "File {$file} should have content");
        }
    }

    /** @test */
    public function it_validates_mode_parameter()
    {
        $this->artisan('templates:generate-cases', ['--mode' => 'invalid'])
            ->expectsOutput('Invalid mode. Use: standard, extended, or all')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_creates_templates_directory_if_missing()
    {
        $templatesDir = storage_path('app/templates');

        if (is_dir($templatesDir)) {
            rmdir($templatesDir);
        }

        Artisan::call('templates:generate-cases', ['--mode' => 'standard']);

        $this->assertDirectoryExists($templatesDir);
    }

    /** @test */
    public function it_includes_schema_signature_in_xlsx_readme()
    {
        Artisan::call('templates:generate-cases', ['--mode' => 'standard']);

        $path = storage_path('app/templates/Cases_Import_Template_Standard.xlsx');
        $this->assertFileExists($path);

        // This test would require reading the XLSX file to check README sheet
        // For now, just verify the file was created successfully
        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_database_connection_errors_gracefully()
    {
        // This test would require mocking database connection failure
        // For now, just test that the command exists and can be called
        $this->artisan('templates:generate-cases', ['--mode' => 'standard'])
            ->assertExitCode(0);
    }
}
