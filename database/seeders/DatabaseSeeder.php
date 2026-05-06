<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Order matters:
 *   1. Bootstrap (languages, permissions, roles, default admin) via the
 *      global-namespace `DatabaseSeeder` autoloaded from `database/seeds/`.
 *      The catalog seeders below rely on `languages` rows existing.
 *   2. CountrySeeder — countries are referenced by stores via country_id.
 *   3. StoreSeeder — stores are referenced by coupons via store_id.
 *   4. CouponSeeder — coupons are referenced by the option pivot.
 *   5. OptionSeeder — last, pivots stores and coupons together.
 *   6. StorePageSeeder — bootstraps store sections from seeded coupons.
 *   7. CountryPageSeeder — seeds country landing sections with mixed layouts.
 *   8. MainPageSeeder — bootstraps the public home page sections.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(\DatabaseSeeder::class);

        $this->call([
            CountrySeeder::class,
            StoreSeeder::class,
            CouponSeeder::class,
            OptionSeeder::class,
            StorePageSeeder::class,
            CountryPageSeeder::class,
            MainPageSeeder::class,
        ]);
    }
}
