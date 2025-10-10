<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions using dot notation: {entity}.{action}
        $permissions = [
            // Cases/Matters permissions
            'cases.view',
            'cases.create',
            'cases.edit',
            'cases.delete',

            // Hearings permissions
            'hearings.view',
            'hearings.create',
            'hearings.edit',
            'hearings.delete',

            // Documents permissions
            'documents.view',
            'documents.upload',
            'documents.download',
            'documents.delete',

            // Clients permissions
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            // Admin-level permissions
            'admin.users.manage',
            'admin.roles.manage',
            'admin.audit.view',

            // Trash/Recycle Bin permissions
            'trash.view',
            'trash.restore',
            'trash.purge',
        ];

        // Create each permission if it doesn't exist
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $this->command->info("Permission created/exists: {$permission->name}");
        }

        // Assign ALL permissions to super_admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(Permission::all());
            $this->command->info('All permissions assigned to super_admin role');
        }

        $this->command->info('Total permissions created: ' . count($permissions));
    }
}
