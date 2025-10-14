<?php

use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Lawyer;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->seed(PermissionsSeeder::class);
});

test('data quality dashboard requires authentication', function () {
    $response = $this->get('/data-quality');

    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

test('data quality dashboard requires admin.audit.view permission', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(403);
});

test('data quality dashboard displays for authorized user', function () {
    $user = User::factory()->create();
    $role = Role::findByName('super_admin');
    $user->assignRole($role);

    // Create some test data
    $client = Client::factory()->create();
    $case = CaseModel::factory()->create(['client_id' => $client->id]);

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(200);
    $response->assertViewIs('data-quality.index');
    $response->assertViewHas('counts');
    $response->assertViewHas('integrity');
    $response->assertViewHas('completeness');
    $response->assertViewHas('stats');
    $response->assertViewHas('topClients');
});

test('data quality dashboard shows correct record counts', function () {
    $user = User::factory()->create();
    $role = Role::findByName('super_admin');
    $user->assignRole($role);

    // Create test data
    $clients = Client::factory()->count(5)->create();
    $lawyer = Lawyer::factory()->create();

    foreach ($clients as $client) {
        CaseModel::factory()->count(2)->create(['client_id' => $client->id]);
    }

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(200);
    $response->assertSee('Clients');
    $response->assertSee('Cases');
    $response->assertSee('5'); // 5 clients
    $response->assertSee('10'); // 10 cases (5 clients × 2 cases)
});

test('data quality dashboard shows 100% referential integrity when all FKs valid', function () {
    $user = User::factory()->create();
    $role = Role::findByName('super_admin');
    $user->assignRole($role);

    // Create test data with valid FKs
    $client = Client::factory()->create();
    $case = CaseModel::factory()->create(['client_id' => $client->id]);
    AdminTask::factory()->create(['matter_id' => $case->id]);

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(200);
    $response->assertSee('100.00%'); // Should show 100% integrity
    $response->assertSee('✓ Excellent'); // Status badge
});

test('data quality dashboard calculates averages correctly', function () {
    $user = User::factory()->create();
    $role = Role::findByName('super_admin');
    $user->assignRole($role);

    // Create test data: 2 clients, 4 cases (avg 2 cases per client)
    $client1 = Client::factory()->create();
    $client2 = Client::factory()->create();
    CaseModel::factory()->count(3)->create(['client_id' => $client1->id]);
    CaseModel::factory()->count(1)->create(['client_id' => $client2->id]);

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(200);
    $response->assertViewHas('stats', function ($stats) {
        return $stats['avg_cases_per_client'] == 2.0; // 4 cases / 2 clients
    });
});

test('data quality dashboard shows top clients by case count', function () {
    $user = User::factory()->create();
    $role = Role::findByName('super_admin');
    $user->assignRole($role);

    // Create clients with varying case counts
    $topClient = Client::factory()->create(['client_name_ar' => 'أفضل عميل']);
    $regularClient = Client::factory()->create(['client_name_ar' => 'عميل عادي']);

    CaseModel::factory()->count(5)->create(['client_id' => $topClient->id]);
    CaseModel::factory()->count(1)->create(['client_id' => $regularClient->id]);

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(200);
    $response->assertSee('أفضل عميل'); // Top client name should appear
    $response->assertSee('5'); // Case count for top client
});

test('data quality dashboard handles empty database gracefully', function () {
    $user = User::factory()->create();
    $role = Role::findByName('super_admin');
    $user->assignRole($role);

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(200);
    $response->assertSee('0'); // Should show 0 for counts
    $response->assertSee('N/A'); // Should show N/A for percentages with no data
});

test('data quality dashboard displays all required sections', function () {
    $user = User::factory()->create();
    $role = Role::findByName('super_admin');
    $user->assignRole($role);

    $response = $this->actingAs($user)->get('/data-quality');

    $response->assertStatus(200);
    $response->assertSee('📊 Record Counts');
    $response->assertSee('🔗 Referential Integrity');
    $response->assertSee('✅ Data Completeness');
    $response->assertSee('📈 Relationship Statistics');
    $response->assertSee('🏆 Top 10 Clients');
});
