<?php

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MainAdminSeeder extends Seeder
{
    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Default Admin',
                'password' => bcrypt('1234admin'),
            ]
        );

        $superAdmin = Role::where('guard_name', 'admin')
            ->where('name', 'super-admin')
            ->first();

        if ($superAdmin && !$admin->hasRole($superAdmin)) {
            $admin->assignRole($superAdmin);
        }

        // Surface the result in seeder output so it is obvious from the
        // console whether the default admin is fully wired up.
        $count = $admin->fresh()->getAllPermissions()->count();
        $this->command?->info(sprintf(
            'Default admin (%s) has %d permission(s) via role(s): %s',
            $admin->email,
            $count,
            $admin->getRoleNames()->implode(', ') ?: '(none)'
        ));
    }
}
