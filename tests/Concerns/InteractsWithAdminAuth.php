<?php

namespace Tests\Concerns;

use App\Models\Admin;
use App\Models\Permission;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait InteractsWithAdminAuth
{
    protected function actingAsAdminWithPermissions(array $permissions, array $roles = [], array $attributes = [])
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        $admin = Admin::create(array_merge([
            'name' => 'Test Admin',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ], $attributes));

        if (!empty($permissions)) {
            $admin->givePermissionTo($permissions);
        }

        if (!empty($roles)) {
            $admin->assignRole($roles);
        }

        Passport::actingAs($admin, [], 'admin');

        return $admin;
    }

    protected function createRole($name, array $permissions = [])
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        $role = Role::firstOrCreate([
            'name' => $name,
            'guard_name' => 'admin',
        ]);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }
}
