<?php

use App\Models\AdminSubtask;
use App\Models\AdminTask;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Contact;
use App\Models\DeletionBundle;
use App\Models\EngagementLetter;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\PowerOfAttorney;
use App\Services\DeletionBundleService;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SuperAdminSeeder;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->seed([
        SuperAdminSeeder::class,
        RolesSeeder::class,
        PermissionsSeeder::class,
    ]);
});

// === Client Bundle Tests ===

test('deleting client creates deletion bundle with full cascade', function () {
    $client = Client::create([
        'client_name_ar' => 'Test Client',
        'client_name_en' => 'Test Client EN',
        'client_print_name' => 'Test Client',
        'power_of_attorney_location' => 'Office',
    ]);

    // Add related entities
    $case = CaseModel::create([
        'client_id' => $client->id,
        'matter_name_ar' => 'Test Case',
        'matter_name_en' => 'Test Case EN',
    ]);

    $contact = Contact::create([
        'client_id' => $client->id,
        'full_name' => 'John Doe',
    ]);

    // Delete client
    $client->delete();

    // Assert bundle was created
    $bundle = DeletionBundle::where('root_id', $client->id)
        ->where('root_type', 'Client')
        ->first();

    expect($bundle)->not->toBeNull()
        ->and($bundle->root_label)->toContain('Test Client')
        ->and($bundle->status)->toBe('trashed')
        ->and($bundle->cascade_count)->toBeGreaterThan(0);

    // Verify snapshot contains all related data
    $snapshot = $bundle->snapshot_json;
    expect($snapshot)->toHaveKey('client')
        ->and($snapshot)->toHaveKey('cases')
        ->and($snapshot)->toHaveKey('contacts')
        ->and($snapshot['cases'])->toHaveCount(1)
        ->and($snapshot['contacts'])->toHaveCount(1);
});

// === Case Bundle Tests ===

test('deleting case creates bundle with hearings and tasks', function () {
    $client = Client::create([
        'client_name_ar' => 'Client',
        'client_name_en' => 'Client EN',
        'client_print_name' => 'Client',
        'power_of_attorney_location' => 'Office',
    ]);

    $case = CaseModel::create([
        'client_id' => $client->id,
        'matter_name_ar' => 'Case 123',
        'matter_name_en' => 'Case 123 EN',
    ]);

    $lawyer = Lawyer::create([
        'lawyer_name_ar' => 'Lawyer',
        'lawyer_name_en' => 'Lawyer EN',
    ]);

    Hearing::create([
        'matter_id' => $case->id,
        'lawyer_id' => $lawyer->id,
        'date' => now(),
    ]);

    AdminTask::create([
        'matter_id' => $case->id,
        'lawyer_id' => $lawyer->id,
    ]);

    // Delete case
    $case->delete();

    $bundle = DeletionBundle::where('root_id', $case->id)
        ->where('root_type', 'CaseModel')
        ->first();

    expect($bundle)->not->toBeNull()
        ->and($bundle->cascade_count)->toBeGreaterThan(0);

    $snapshot = $bundle->snapshot_json;
    expect($snapshot)->toHaveKey('case')
        ->and($snapshot)->toHaveKey('hearings')
        ->and($snapshot)->toHaveKey('admin_tasks');
});

// === Individual Model Tests ===

test('deleting contact creates bundle', function () {
    $client = Client::create([
        'client_name_ar' => 'Client',
        'client_name_en' => 'Client',
        'client_print_name' => 'Client',
        'power_of_attorney_location' => 'Office',
    ]);

    $contact = Contact::create([
        'client_id' => $client->id,
        'full_name' => 'Jane Smith',
    ]);

    $contact->delete();

    $bundle = DeletionBundle::where('root_type', 'Contact')->first();

    expect($bundle)->not->toBeNull()
        ->and($bundle->root_label)->toContain('Jane Smith');
});

test('deleting hearing creates bundle', function () {
    $client = Client::create([
        'client_name_ar' => 'Client',
        'client_name_en' => 'Client',
        'client_print_name' => 'Client',
        'power_of_attorney_location' => 'Office',
    ]);

    $case = CaseModel::create([
        'client_id' => $client->id,
        'matter_name_ar' => 'Case',
        'matter_name_en' => 'Case',
    ]);

    $hearing = Hearing::create([
        'matter_id' => $case->id,
        'date' => now(),
        'court' => 'Supreme Court',
    ]);

    $hearing->delete();

    $bundle = DeletionBundle::where('root_type', 'Hearing')->first();

    expect($bundle)->not->toBeNull()
        ->and($bundle->root_label)->toContain('Supreme Court');
});

test('deleting admin task creates bundle with subtasks', function () {
    $client = Client::create([
        'client_name_ar' => 'Client',
        'client_name_en' => 'Client',
        'client_print_name' => 'Client',
        'power_of_attorney_location' => 'Office',
    ]);

    $case = CaseModel::create([
        'client_id' => $client->id,
        'matter_name_ar' => 'Case',
        'matter_name_en' => 'Case',
    ]);

    $task = AdminTask::create([
        'matter_id' => $case->id,
        'required_work' => 'File motion',
    ]);

    AdminSubtask::create([
        'task_id' => $task->id,
        'result' => 'Research completed',
    ]);

    $task->delete();

    $bundle = DeletionBundle::where('root_type', 'AdminTask')->first();

    expect($bundle)->not->toBeNull();

    $snapshot = $bundle->snapshot_json;
    expect($snapshot)->toHaveKey('admin_task')
        ->and($snapshot)->toHaveKey('admin_subtasks')
        ->and($snapshot['admin_subtasks'])->toHaveCount(1);
});

test('deleting lawyer creates bundle with assignments metadata', function () {
    $lawyer = Lawyer::create([
        'lawyer_name_ar' => 'Ahmed',
        'lawyer_name_en' => 'Ahmed',
    ]);

    $lawyer->delete();

    $bundle = DeletionBundle::where('root_type', 'Lawyer')->first();

    expect($bundle)->not->toBeNull()
        ->and($bundle->root_label)->toContain('Ahmed');

    $snapshot = $bundle->snapshot_json;
    expect($snapshot)->toHaveKey('lawyer')
        ->and($snapshot)->toHaveKey('assignments');
});

// === Restore Tests ===

test('restore bundle returns proper report structure', function () {
    $service = app(DeletionBundleService::class);

    $client = Client::create([
        'client_name_ar' => 'Restore Client',
        'client_name_en' => 'Restore Client EN',
        'client_print_name' => 'Restore Client',
        'power_of_attorney_location' => 'Office',
    ]);

    $clientId = $client->id;
    $client->delete();

    $bundle = DeletionBundle::where('root_id', $clientId)->first();
    expect($bundle)->not->toBeNull();

    // Test restore report structure (dry run to avoid actual restoration)
    $report = $service->restoreBundle($bundle->id, ['dry_run' => true]);

    expect($report)->toHaveKeys([
        'bundle_id',
        'root_type',
        'root_label',
        'dry_run',
        'conflict_strategy',
        'restored',
        'skipped',
        'conflicts',
        'errors',
    ])->and($report['dry_run'])->toBeTrue();
});

test('dry run restore does not modify database', function () {
    $service = app(DeletionBundleService::class);

    $client = Client::create([
        'client_name_ar' => 'Dry Run Client',
        'client_name_en' => 'Dry Run Client',
        'client_print_name' => 'Dry Run Client',
        'power_of_attorney_location' => 'Office',
    ]);

    $clientId = $client->id;
    $client->delete();

    $bundle = DeletionBundle::where('root_id', $clientId)->first();

    // Dry run restore
    $report = $service->restoreBundle($bundle->id, ['dry_run' => true]);

    expect($report['dry_run'])->toBeTrue();
    expect($bundle->fresh()->status)->toBe('trashed'); // Still trashed
});

test('bundle tracks cascade count correctly', function () {
    $client = Client::create([
        'client_name_ar' => 'Count Client',
        'client_name_en' => 'Count Client',
        'client_print_name' => 'Count Client',
        'power_of_attorney_location' => 'Office',
    ]);

    // Add multiple related entities
    CaseModel::create([
        'client_id' => $client->id,
        'matter_name_ar' => 'Case 1',
        'matter_name_en' => 'Case 1',
    ]);

    Contact::create([
        'client_id' => $client->id,
        'full_name' => 'Contact 1',
    ]);

    Contact::create([
        'client_id' => $client->id,
        'full_name' => 'Contact 2',
    ]);

    $client->delete();

    $bundle = DeletionBundle::where('root_id', $client->id)->first();

    // Should count client + 1 case + 2 contacts = 4
    expect($bundle->cascade_count)->toBeGreaterThanOrEqual(4);
});

// === Permission Tests ===

test('trash commands require proper permissions', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user);

    expect($user->can('trash.view'))->toBeFalse()
        ->and($user->can('trash.restore'))->toBeFalse()
        ->and($user->can('trash.purge'))->toBeFalse();
});

test('super admin can access all trash operations', function () {
    $superAdmin = \App\Models\User::where('email', 'khelmy@sarieldin.com')->first();

    expect($superAdmin->can('trash.view'))->toBeTrue()
        ->and($superAdmin->can('trash.restore'))->toBeTrue()
        ->and($superAdmin->can('trash.purge'))->toBeTrue();
});

// === Configuration Tests ===

test('trash config has all models enabled', function () {
    $enabledModels = config('trash.enabled_for');

    expect($enabledModels)->toHaveKeys([
        'Client',
        'CaseModel',
        'ClientDocument',
        'Hearing',
        'AdminTask',
        'AdminSubtask',
        'EngagementLetter',
        'PowerOfAttorney',
        'Contact',
        'Lawyer',
    ]);

    foreach ($enabledModels as $enabled) {
        expect($enabled)->toBeTrue();
    }
});

test('all collectors are configured and exist', function () {
    $collectors = config('trash.collectors');

    foreach ($collectors as $modelClass => $collectorClass) {
        expect(class_exists($modelClass))->toBeTrue("Model {$modelClass} should exist");
        expect(class_exists($collectorClass))->toBeTrue("Collector {$collectorClass} should exist");
    }
});
