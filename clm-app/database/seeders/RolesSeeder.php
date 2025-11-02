<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define base roles for the application
        $roles = [
            'super_admin',
            'admin',
            'lawyer',
            'staff',
            'client_portal',
        ];

        // Create each role if it doesn't exist
        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $this->command->info("Role created/exists: {$role->name}");
        }

        // Assign super_admin role to the super admin user
        $superAdmin = \App\Models\User::where('email', 'khelmy@sarieldin.com')->first();
        if ($superAdmin) {
            $superAdmin->assignRole('super_admin');
            $this->command->info('Super admin role assigned to: ' . $superAdmin->email);
        }
    }
}
