<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage users',
            'manage games',
            'manage services',
            'manage accounts',
            'manage orders',
            'manage tickets',
            'manage settings',
            'view analytics',
            'manage payments',
            'manage audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'manage games',
            'manage services',
            'manage accounts',
            'manage orders',
            'manage tickets',
            'view analytics',
            'manage payments',
        ]);

        $editor = Role::create(['name' => 'editor']);
        $editor->givePermissionTo([
            'manage games',
            'manage services',
            'manage accounts',
        ]);

        $support = Role::create(['name' => 'support']);
        $support->givePermissionTo([
            'manage orders',
            'manage tickets',
        ]);

        Role::create(['name' => 'user']);
    }
}
