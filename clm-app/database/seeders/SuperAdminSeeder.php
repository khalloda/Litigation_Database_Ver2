<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'khelmy@sarieldin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('P@ssw0rd'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );

        $this->command->info('Super admin user created/updated: ' . $superAdmin->email);
    }
}
