<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Hash;

uses(Tests\TestCase::class);

test('super admin seeder creates super admin user', function () {
    // Run the seeder
    $this->seed(SuperAdminSeeder::class);

    // Assert user exists with correct email
    $superAdmin = User::where('email', 'khelmy@sarieldin.com')->first();
    
    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->name)->toBe('Super Admin')
        ->and($superAdmin->email)->toBe('khelmy@sarieldin.com')
        ->and($superAdmin->locale)->toBe('en')
        ->and($superAdmin->email_verified_at)->not->toBeNull();
    
    // Assert password is correctly hashed
    expect(Hash::check('P@ssw0rd', $superAdmin->password))->toBeTrue();
});

test('super admin is assigned super_admin role', function () {
    // Run both seeders
    $this->seed([
        SuperAdminSeeder::class,
        RolesSeeder::class,
    ]);

    $superAdmin = User::where('email', 'khelmy@sarieldin.com')->first();
    
    expect($superAdmin->hasRole('super_admin'))->toBeTrue();
});

test('super admin seeder is idempotent', function () {
    // Run seeder twice
    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    // Assert only one user was created
    $count = User::where('email', 'khelmy@sarieldin.com')->count();
    
    expect($count)->toBe(1);
});
