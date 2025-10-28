<?php

namespace Tests\Unit;

use App\Models\ImportProfile;
use App\Services\Import\ImportProfileService;
use Tests\TestCase;

class ImportProfileServiceTest extends TestCase
{
    public function test_select_profile_prefers_exact_header_hash(): void
    {
        $service = app(ImportProfileService::class);
        // Create two profiles: one null hash, one exact
        $p1 = ImportProfile::factory()->create(['table_name' => 'cases', 'header_hash' => null, 'is_active' => true, 'name' => 'fallback']);
        $headers = ['A','B'];
        $hash = \App\Support\Import\HeaderHasher::hash($headers);
        $p2 = ImportProfile::factory()->create(['table_name' => 'cases', 'header_hash' => $hash, 'is_active' => true, 'name' => 'exact']);

        $picked = $service->selectProfile('cases', $headers);
        $this->assertNotNull($picked);
        $this->assertEquals('exact', $picked->name);
    }
}


