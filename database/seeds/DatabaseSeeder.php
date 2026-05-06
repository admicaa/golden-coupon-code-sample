<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed order matters here:
     *  1. mainLanguageSeeder seeds the `languages` table. Permission names
     *     contain `{lang}` placeholders that expand against those rows, so
     *     this has to run first.
     *  2. PermissionsSeeder creates every permission under the `admin` guard.
     *  3. RolesSeeder creates super-admin (full set) plus content-manager,
     *     support-admin, and viewer, and syncs their permission lists.
     *  4. MainAdminSeeder creates the default admin and assigns super-admin.
     */
    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call(mainLanguageSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(MainAdminSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
