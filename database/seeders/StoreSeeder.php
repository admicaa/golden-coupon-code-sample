<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Languages;
use App\Models\Store;
use App\Models\StorePage;
use Illuminate\Database\Seeder;

/**
 * Idempotent store seeder.
 *
 * The `stores` table only carries `country_id`, so there is no natural
 * unique key on the row itself. We treat the GB `store_pages.slug` as the
 * stable identifier of a store: if a store_page with that slug already
 * exists we reuse its parent store, otherwise we create a fresh store and
 * its localized pages.
 *
 * Localized pages are upserted on the `(store_id, language)` composite
 * unique. Default SEO meta tags (config/seo.php) are seeded once per
 * page via firstOrCreate keyed by tag name.
 */
class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = require database_path('seeders/data/stores.php');
        $languages = Languages::query()->pluck('shortcut')->all();
        $defaultMetaTags = (array) config('seo.default_meta_tags', []);

        foreach ($stores as $row) {
            $country = Country::query()->where('iso', $row['country_code'])->first();

            if (!$country) {
                $this->command?->warn(sprintf(
                    'Skipping store "%s": country with iso "%s" not found.',
                    $row['slug'],
                    $row['country_code']
                ));
                continue;
            }

            // Resolve an existing store via the GB page slug (the stable
            // identifier the seeder uses), otherwise create a new store.
            $existingPage = StorePage::query()->where('slug', $row['slug'])->first();
            $store = $existingPage?->store;

            if (!$store) {
                $store = Store::create(['country_id' => $country->id]);
            } elseif ((int) $store->country_id !== (int) $country->id) {
                // Country reassignment: keep the row, refresh the link.
                $store->update(['country_id' => $country->id]);
            }

            foreach ($row['pages'] ?? [] as $language => $pageData) {
                if (!in_array($language, $languages, true)) {
                    continue;
                }

                $page = $store->pages()->updateOrCreate(
                    ['language' => $language],
                    [
                        'slug' => $pageData['slug'],
                        'name' => $pageData['name'],
                        'title' => $pageData['title'],
                        'body' => $pageData['body'] ?? '',
                    ]
                );

                foreach ($defaultMetaTags as $tag) {
                    $page->metatags()->firstOrCreate(
                        ['name' => $tag['name']],
                        ['value' => $tag['value'] ?? '']
                    );
                }
            }
        }

        $this->command?->info(sprintf('Seeded %d stores.', count($stores)));
    }
}
