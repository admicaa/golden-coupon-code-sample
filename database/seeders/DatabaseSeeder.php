<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Laravel 12's `db:seed` defaults to running `Database\Seeders\DatabaseSeeder`.
 *
 * The project still uses the legacy global-namespace seeders under
 * `database/seeds/` (autoloaded via the `classmap` entry in composer.json).
 * This shim forwards to the legacy `DatabaseSeeder` so existing seeder logic
 * is untouched and `php artisan db:seed` works without `--class=…`.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(\DatabaseSeeder::class);
    }
}
