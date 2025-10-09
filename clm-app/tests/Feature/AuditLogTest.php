<?php

namespace Tests\Feature;

use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
uses(RefreshDatabase::class, Tests\TestCase::class);

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->superAdmin = User::factory()->create(['email' => 'superadmin@example.com']);
    $this->superAdmin->assignRole('super_admin');
    $this->actingAs($this->superAdmin);
});

test('audit log records client creation', function () {
    $client = Client::create([
        'client_name_ar' => 'عميل تجريبي',
        'client_name_en' => 'Test Client',
        'client_print_name' => 'Test Client',
        'status' => 'Active',
        'cash_or_probono' => 'Cash',
    ]);

    $activity = Activity::latest()->first();
    
    expect($activity)->not->toBeNull();
    expect($activity->subject_type)->toBe(Client::class);
    expect($activity->subject_id)->toBe($client->id);
    expect($activity->event)->toBe('created');
    expect($activity->causer_id)->toBe($this->superAdmin->id);
    expect($activity->description)->toContain('Client was created');
});

test('audit log records case updates', function () {
    // Create a case first
    $case = CaseModel::create([
        'matter_name_ar' => 'قضية تجريبية',
        'matter_name_en' => 'Test Case',
        'matter_status' => 'Open',
        'client_id' => 1, // Assuming client exists
    ]);

    // Update the case
    $case->update([
        'matter_status' => 'Closed',
        'matter_name_en' => 'Updated Test Case',
    ]);

    $activity = Activity::latest()->first();
    
    expect($activity)->not->toBeNull();
    expect($activity->subject_type)->toBe(CaseModel::class);
    expect($activity->subject_id)->toBe($case->id);
    expect($activity->event)->toBe('updated');
    expect($activity->description)->toContain('Case was updated');
    
    // Check that only changed fields are logged
    $changes = $activity->properties['attributes'] ?? [];
    expect($changes)->toHaveKey('matter_status');
    expect($changes)->toHaveKey('matter_name_en');
    expect($changes)->not->toHaveKey('matter_name_ar'); // Should not be logged as it didn't change
});

test('audit log records admin task creation', function () {
    $task = AdminTask::create([
        'matter_id' => 1,
        'lawyer_id' => 1,
        'status' => 'Pending',
        'required_work' => 'Review documents',
        'performer' => 'John Doe',
    ]);

    $activity = Activity::latest()->first();
    
    expect($activity)->not->toBeNull();
    expect($activity->subject_type)->toBe(AdminTask::class);
    expect($activity->subject_id)->toBe($task->id);
    expect($activity->event)->toBe('created');
    expect($activity->description)->toContain('Admin task was created');
});

test('audit log viewer requires authentication', function () {
    $this->post('/logout'); // Log out the super admin
    
    $this->get('/audit-logs')
        ->assertRedirect('/login');
});

test('audit log viewer requires admin.audit.view permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/audit-logs')
        ->assertStatus(403); // Forbidden
    
    $user->givePermissionTo('admin.audit.view');
    $this->actingAs($user)
        ->get('/audit-logs')
        ->assertStatus(200); // OK
});

test('audit log viewer displays activities', function () {
    // Create some test activities
    Client::create([
        'client_name_ar' => 'عميل 1',
        'client_name_en' => 'Client 1',
        'client_print_name' => 'Client 1',
        'status' => 'Active',
        'cash_or_probono' => 'Cash',
    ]);

    CaseModel::create([
        'matter_name_ar' => 'قضية 1',
        'matter_name_en' => 'Case 1',
        'matter_status' => 'Open',
        'client_id' => 1,
    ]);

    $response = $this->get('/audit-logs');
    
    $response->assertStatus(200);
    $response->assertSee('Audit Logs');
    $response->assertSee('Client was created');
    $response->assertSee('Case was created');
    $response->assertSee('Filter');
    $response->assertSee('Export CSV');
});

test('audit log viewer filters work', function () {
    // Create activities for different entity types
    Client::create([
        'client_name_ar' => 'عميل للفلترة',
        'client_name_en' => 'Filter Client',
        'client_print_name' => 'Filter Client',
        'status' => 'Active',
        'cash_or_probono' => 'Cash',
    ]);

    CaseModel::create([
        'matter_name_ar' => 'قضية للفلترة',
        'matter_name_en' => 'Filter Case',
        'matter_status' => 'Open',
        'client_id' => 1,
    ]);

    // Filter by Client type
    $response = $this->get('/audit-logs?subject_type=' . urlencode(Client::class));
    
    $response->assertStatus(200);
    $response->assertSee('Client was created');
    $response->assertDontSee('Case was created');
});

test('audit log export works', function () {
    // Create some test data
    Client::create([
        'client_name_ar' => 'عميل للتصدير',
        'client_name_en' => 'Export Client',
        'client_print_name' => 'Export Client',
        'status' => 'Active',
        'cash_or_probono' => 'Cash',
    ]);

    $response = $this->get('/audit-logs/export/csv');
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv');
    $response->assertHeader('Content-Disposition', function ($value) {
        return str_contains($value, 'attachment') && str_contains($value, '.csv');
    });
});

test('audit log detail view shows activity information', function () {
    $client = Client::create([
        'client_name_ar' => 'عميل للتفاصيل',
        'client_name_en' => 'Detail Client',
        'client_print_name' => 'Detail Client',
        'status' => 'Active',
        'cash_or_probono' => 'Cash',
    ]);

    $activity = Activity::latest()->first();
    
    $response = $this->get("/audit-logs/{$activity->id}");
    
    $response->assertStatus(200);
    $response->assertSee('Audit Log Details');
    $response->assertSee('Detail Client');
    $response->assertSee('Client was created');
    $response->assertSee('Activity Information');
    $response->assertSee('Changes Made');
});

test('audit logging works with bulk operations', function () {
    // Create multiple clients
    $clients = [];
    for ($i = 1; $i <= 5; $i++) {
        $clients[] = Client::create([
            'client_name_ar' => "عميل {$i}",
            'client_name_en' => "Client {$i}",
            'client_print_name' => "Client {$i}",
            'status' => 'Active',
            'cash_or_probono' => 'Cash',
        ]);
    }

    // Check that we have 5 activities
    $activities = Activity::where('subject_type', Client::class)
        ->where('event', 'created')
        ->get();
    
    expect($activities)->toHaveCount(5);
    
    // Check that each activity has the correct subject_id
    foreach ($activities as $activity) {
        expect($activity->subject_id)->toBeIn(array_column($clients, 'id'));
    }
});

test('audit log respects logOnly configuration', function () {
    $client = Client::create([
        'client_name_ar' => 'عميل للاختبار',
        'client_name_en' => 'Test Client',
        'client_print_name' => 'Test Client',
        'status' => 'Active',
        'cash_or_probono' => 'Cash',
        'client_start' => now(),
        'client_end' => now()->addYear(),
    ]);

    $activity = Activity::latest()->first();
    $changes = $activity->properties['attributes'] ?? [];
    
    // Only configured fields should be logged
    $allowedFields = ['client_name_ar', 'client_name_en', 'client_print_name', 'status', 'cash_or_probono', 'client_start', 'client_end'];
    
    foreach ($changes as $field => $value) {
        expect($field)->toBeIn($allowedFields);
    }
});
