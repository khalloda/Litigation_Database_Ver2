<?php

use App\Models\CaseModel;
use App\Models\Client;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    // Create a test user with permissions
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);
    
    // Grant cases.view permission
    $user->givePermissionTo('cases.view');
});

test('cases show page renders all database columns', function () {
    // Create test data
    $client = Client::factory()->create([
        'client_name_ar' => 'Test Client AR',
        'client_name_en' => 'Test Client EN',
    ]);
    
    $case = CaseModel::factory()->create([
        'client_id' => $client->id,
        'matter_name_ar' => 'Test Case AR',
        'matter_name_en' => 'Test Case EN',
    ]);
    
    // Get all columns from the database schema
    $dbColumns = Schema::getColumnListing('cases');
    
    // Visit the show page
    $response = $this->get(route('cases.show', $case));
    
    // Assert page loads successfully
    $response->assertStatus(200);
    $response->assertSee('All Fields (Schema-Driven)', false);
    
    // Assert that schema data is passed to the view
    $response->assertViewHas('schemaData');
    $schemaData = $response->viewData('schemaData');
    
    // Assert schema data structure
    expect($schemaData)->toHaveKeys(['columns', 'types', 'fkHints']);
    expect($schemaData['columns'])->toBeArray();
    expect($schemaData['types'])->toBeArray();
    expect($schemaData['fkHints'])->toBeArray();
    
    // Assert all DB columns are included in schema columns
    $schemaColumns = $schemaData['columns'];
    foreach ($dbColumns as $dbColumn) {
        expect($schemaColumns)->toContain($dbColumn);
    }
    
    // Assert that each column has a type
    foreach ($schemaColumns as $column) {
        expect($schemaData['types'])->toHaveKey($column);
        expect($schemaData['fkHints'])->toHaveKey($column);
    }
    
    // Assert component is rendered (check for component structure)
    $response->assertSee('Field', false);
    $response->assertSee('Type', false);
    $response->assertSee('Value', false);
    
    // Assert at least some expected columns are present in the rendered HTML
    // (We can't check all because some might be NULL and not rendered)
    $response->assertSee('id', false);
    $response->assertSee('client_id', false);
    $response->assertSee('matter_name_ar', false);
});

test('cases show page handles missing relations gracefully', function () {
    // Create a case without relations
    $case = CaseModel::factory()->create([
        'client_id' => null, // Orphaned case
        'matter_name_ar' => 'Orphaned Case',
    ]);
    
    // Visit the show page - should not crash
    $response = $this->get(route('cases.show', $case));
    
    $response->assertStatus(200);
    $response->assertViewHas('schemaData');
    
    // Component should render even with missing relations
    $response->assertSee('All Fields (Schema-Driven)', false);
});

