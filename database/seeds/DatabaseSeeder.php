<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->call(mainLanguageSeeder::class);

        $this->call(PermissionsSeeder::class);

        $this->call(RolesSeeder::class);
        $this->call(MainAdminSeeder::class);
    }
}
