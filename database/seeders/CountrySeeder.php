<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Languages;
use Illuminate\Database\Seeder;

/**
 * Idempotent country seeder.
 *
 * - `countries` is upserted by `iso` (the table's only unique business key).
 * - `country_names` is upserted by the `(country_id, language)` composite
 *   unique. Languages absent from the `languages` table are skipped.
 */
class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = require database_path('seeders/data/countries.php');
        $languages = Languages::query()->pluck('shortcut')->all();

        foreach ($countries as $row) {
            $country = Country::updateOrCreate(
                ['iso' => $row['iso']],
                []
            );

            foreach ($row['names'] ?? [] as $language => $nameData) {
                if (!in_array($language, $languages, true)) {
                    continue;
                }

                $country->names()->updateOrCreate(
                    ['language' => $language],
                    [
                        'name' => $nameData['name'],
                        'header_name' => $nameData['header_name'] ?? null,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            'Seeded %d countries (%d languages available).',
            count($countries),
            count($languages)
        ));
    }
}
