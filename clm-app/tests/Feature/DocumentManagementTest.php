<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->superAdmin = User::factory()->create(['email' => 'superadmin@example.com']);
    $this->superAdmin->assignRole('super_admin');
    $this->actingAs($this->superAdmin);
    
    // Create test client
    $this->client = Client::create([
        'client_name_ar' => 'عميل للاختبار',
        'client_name_en' => 'Test Client',
        'client_print_name' => 'Test Client',
        'status' => 'Active',
        'cash_or_probono' => 'Cash',
        'power_of_attorney_location' => 'Office',
    ]);
});

test('document listing requires authentication', function () {
    $this->post('/logout'); // Log out the super admin
    
    $this->get('/documents')
        ->assertRedirect('/login');
});

test('document listing requires documents.view permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/documents')
        ->assertStatus(403); // Forbidden
    
    $user->givePermissionTo('documents.view');
    $this->actingAs($user)
        ->get('/documents')
        ->assertStatus(200); // OK
});

test('document upload form requires documents.upload permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/documents/create')
        ->assertStatus(403); // Forbidden
    
    $user->givePermissionTo('documents.upload');
    $this->actingAs($user)
        ->get('/documents/create')
        ->assertStatus(200); // OK
});

test('document upload works with valid file', function () {
    Storage::fake('secure');
    
    $file = UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf');
    
    $response = $this->post('/documents', [
        'document' => $file,
        'client_id' => $this->client->id,
        'document_type' => 'Test Document',
        'description' => 'This is a test document',
    ]);
    
    $response->assertRedirect();
    
    // Check document was created
    $this->assertDatabaseHas('client_documents', [
        'client_id' => $this->client->id,
        'document_type' => 'Test Document',
        'description' => 'This is a test document',
    ]);
    
    // Check file was stored
    Storage::disk('secure')->assertExists('documents/' . basename(ClientDocument::first()->file_path));
});

test('document upload validates file size', function () {
    Storage::fake('secure');
    
    // Create a file larger than 10MB
    $file = UploadedFile::fake()->create('large-document.pdf', 11000, 'application/pdf');
    
    $response = $this->post('/documents', [
        'document' => $file,
        'client_id' => $this->client->id,
        'document_type' => 'Large Document',
    ]);
    
    $response->assertSessionHasErrors('document');
    $this->assertDatabaseMissing('client_documents', [
        'document_type' => 'Large Document',
    ]);
});

test('document upload validates file type', function () {
    Storage::fake('secure');
    
    // Create an unsupported file type
    $file = UploadedFile::fake()->create('test.exe', 100, 'application/x-executable');
    
    $response = $this->post('/documents', [
        'document' => $file,
        'client_id' => $this->client->id,
        'document_type' => 'Executable',
    ]);
    
    $response->assertSessionHasErrors('document');
    $this->assertDatabaseMissing('client_documents', [
        'document_type' => 'Executable',
    ]);
});

test('document upload requires client selection', function () {
    Storage::fake('secure');
    
    $file = UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf');
    
    $response = $this->post('/documents', [
        'document' => $file,
        'document_type' => 'Test Document',
        // Missing client_id
    ]);
    
    $response->assertSessionHasErrors('client_id');
    $this->assertDatabaseMissing('client_documents', [
        'document_type' => 'Test Document',
    ]);
});

test('document download works for authorized users', function () {
    Storage::fake('secure');
    
    // Create a document
    $file = UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf');
    $filePath = $file->store('documents', 'secure');
    
    $document = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'test-document.pdf',
        'document_type' => 'Test Document',
        'file_path' => $filePath,
        'file_size' => $file->getSize(),
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    $response = $this->get("/documents/{$document->id}/download");
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename="test-document.pdf"');
});

test('document download requires documents.download permission', function () {
    $user = User::factory()->create();
    
    // Create a document
    $document = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'test-document.pdf',
        'document_type' => 'Test Document',
        'file_path' => 'documents/test.pdf',
        'file_size' => 100,
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    $this->actingAs($user)
        ->get("/documents/{$document->id}/download")
        ->assertStatus(403); // Forbidden
    
    $user->givePermissionTo('documents.download');
    $this->actingAs($user)
        ->get("/documents/{$document->id}/download")
        ->assertStatus(200); // OK
});

test('document deletion works for authorized users', function () {
    Storage::fake('secure');
    
    // Create a document
    $file = UploadedFile::fake()->create('test-document.pdf', 100, 'application/pdf');
    $filePath = $file->store('documents', 'secure');
    
    $document = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'test-document.pdf',
        'document_type' => 'Test Document',
        'file_path' => $filePath,
        'file_size' => $file->getSize(),
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    $response = $this->delete("/documents/{$document->id}");
    
    $response->assertRedirect('/documents');
    
    // Check document was soft deleted
    $this->assertSoftDeleted('client_documents', [
        'id' => $document->id,
    ]);
    
    // Check file was deleted
    Storage::disk('secure')->assertMissing($filePath);
});

test('document deletion requires documents.delete permission', function () {
    $user = User::factory()->create();
    
    // Create a document
    $document = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'test-document.pdf',
        'document_type' => 'Test Document',
        'file_path' => 'documents/test.pdf',
        'file_size' => 100,
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    $this->actingAs($user)
        ->delete("/documents/{$document->id}")
        ->assertStatus(403); // Forbidden
    
    $user->givePermissionTo('documents.delete');
    $this->actingAs($user)
        ->delete("/documents/{$document->id}")
        ->assertStatus(302); // Redirect after successful deletion
});

test('document filtering works', function () {
    // Create multiple documents
    $document1 = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'Contract.pdf',
        'document_type' => 'Contract',
        'file_path' => 'documents/contract.pdf',
        'file_size' => 100,
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    $document2 = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'Invoice.pdf',
        'document_type' => 'Invoice',
        'file_path' => 'documents/invoice.pdf',
        'file_size' => 200,
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    // Filter by document type
    $response = $this->get('/documents?document_type=Contract');
    
    $response->assertStatus(200);
    $response->assertSee('Contract.pdf');
    $response->assertDontSee('Invoice.pdf');
    
    // Filter by client
    $response = $this->get("/documents?client_id={$this->client->id}");
    
    $response->assertStatus(200);
    $response->assertSee('Contract.pdf');
    $response->assertSee('Invoice.pdf');
});

test('document search works', function () {
    // Create documents with different names
    $document1 = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'Important Contract.pdf',
        'document_type' => 'Contract',
        'file_path' => 'documents/contract.pdf',
        'file_size' => 100,
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    $document2 = ClientDocument::create([
        'client_id' => $this->client->id,
        'document_name' => 'Invoice.pdf',
        'document_type' => 'Invoice',
        'file_path' => 'documents/invoice.pdf',
        'file_size' => 200,
        'mime_type' => 'application/pdf',
        'deposit_date' => now(),
    ]);
    
    // Search for "Important"
    $response = $this->get('/documents?search=Important');
    
    $response->assertStatus(200);
    $response->assertSee('Important Contract.pdf');
    $response->assertDontSee('Invoice.pdf');
    
    // Search for "Invoice"
    $response = $this->get('/documents?search=Invoice');
    
    $response->assertStatus(200);
    $response->assertSee('Invoice.pdf');
    $response->assertDontSee('Important Contract.pdf');
});
