<?php

use App\Models\Client;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->superAdmin = User::factory()->create(['email' => 'superadmin@example.com']);
    $this->superAdmin->assignRole('super_admin');
    $this->actingAs($this->superAdmin);
});

test('clients index page loads successfully', function () {
    $response = $this->get('/clients');
    $response->assertStatus(200);
    $response->assertSee('Clients');
});

test('can create a new client', function () {
    $clientData = [
        'client_name_ar' => 'عميل تجريبي',
        'client_name_en' => 'Test Client',
        'client_type' => 'Individual',
        'contact_person' => 'John Doe',
        'contact_email' => 'john@example.com',
        'contact_phone' => '+1234567890',
    ];

    $response = $this->post('/clients', $clientData);
    $response->assertRedirect();
    
    $this->assertDatabaseHas('clients', [
        'client_name_ar' => 'عميل تجريبي',
        'client_name_en' => 'Test Client',
    ]);
});

test('can view client details', function () {
    $client = Client::factory()->create([
        'client_name_ar' => 'عميل للعرض',
        'client_name_en' => 'Client to View',
    ]);

    $response = $this->get("/clients/{$client->id}");
    $response->assertStatus(200);
    $response->assertSee('عميل للعرض');
    $response->assertSee('Client to View');
});

test('can edit client', function () {
    $client = Client::factory()->create([
        'client_name_ar' => 'عميل للتعديل',
        'client_name_en' => 'Client to Edit',
    ]);

    $response = $this->get("/clients/{$client->id}/edit");
    $response->assertStatus(200);
    $response->assertSee('عميل للتعديل');
});

test('can update client', function () {
    $client = Client::factory()->create([
        'client_name_ar' => 'الاسم القديم',
        'client_name_en' => 'Old Name',
    ]);

    $updateData = [
        'client_name_ar' => 'الاسم الجديد',
        'client_name_en' => 'New Name',
        'contact_person' => 'Updated Contact',
    ];

    $response = $this->put("/clients/{$client->id}", $updateData);
    $response->assertRedirect();
    
    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'client_name_ar' => 'الاسم الجديد',
        'client_name_en' => 'New Name',
    ]);
});

test('can delete client with soft delete', function () {
    $client = Client::factory()->create([
        'client_name_ar' => 'عميل للحذف',
        'client_name_en' => 'Client to Delete',
    ]);

    $response = $this->delete("/clients/{$client->id}");
    $response->assertRedirect('/clients');
    
    // Check that client is soft deleted
    $this->assertSoftDeleted('clients', [
        'id' => $client->id,
    ]);
});

test('delete requires confirmation', function () {
    $client = Client::factory()->create();
    
    $response = $this->get("/clients/{$client->id}");
    $response->assertStatus(200);
    $response->assertSee('confirm');
});
