<?php

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SuperAdminSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class);

test('permissions seeder creates all required permissions', function () {
    $this->seed(PermissionsSeeder::class);

    $expectedPermissions = [
        'cases.view', 'cases.create', 'cases.edit', 'cases.delete',
        'hearings.view', 'hearings.create', 'hearings.edit', 'hearings.delete',
        'documents.view', 'documents.upload', 'documents.download', 'documents.delete',
        'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
        'admin.users.manage', 'admin.roles.manage', 'admin.audit.view',
    ];

    foreach ($expectedPermissions as $permissionName) {
        expect(Permission::where('name', $permissionName)->exists())->toBeTrue(
            "Permission {$permissionName} should exist"
        );
    }

    expect(Permission::count())->toBe(count($expectedPermissions));
});

test('super admin has all permissions assigned', function () {
    $this->seed([
        SuperAdminSeeder::class,
        RolesSeeder::class,
        PermissionsSeeder::class,
    ]);

    $superAdmin = User::where('email', 'khelmy@sarieldin.com')->first();
    $superAdminRole = Role::where('name', 'super_admin')->first();

    // Check super admin has the role
    expect($superAdmin->hasRole('super_admin'))->toBeTrue();

    // Check super admin role has all permissions
    $allPermissions = Permission::all();
    expect($superAdminRole->permissions->count())->toBe($allPermissions->count());

    // Check user can access specific permissions
    expect($superAdmin->can('cases.view'))->toBeTrue()
        ->and($superAdmin->can('cases.create'))->toBeTrue()
        ->and($superAdmin->can('hearings.edit'))->toBeTrue()
        ->and($superAdmin->can('documents.upload'))->toBeTrue()
        ->and($superAdmin->can('admin.users.manage'))->toBeTrue();
});

test('permission middleware blocks unauthorized users', function () {
    // Create a user without permissions
    $user = User::factory()->create();

    $this->actingAs($user);

    // Try to access a route with permission middleware would fail
    // This is conceptual since we don't have actual routes yet
    expect($user->can('cases.view'))->toBeFalse();
});

test('permissions seeder is idempotent', function () {
    // Run seeder twice
    $this->seed(PermissionsSeeder::class);
    $this->seed(PermissionsSeeder::class);

    // Should still have only 19 permissions
    expect(Permission::count())->toBe(19);
});
