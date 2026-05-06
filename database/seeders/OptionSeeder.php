<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Languages;
use App\Models\SearchOptions;
use App\Models\SearchOptionsPages;
use App\Models\StorePage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent search-option seeder.
 *
 * The project does not have a generic key/value settings table; the closest
 * concept is the `search_options` filter system that powers the on-site
 * search facets. Because `search_options` itself has no unique business
 * key, the seeder uses the GB page name as the stable identifier — it
 * looks up an existing option through `search_options_pages` (language=GB)
 * and falls back to creating a fresh option row.
 *
 * Pivot rows in `search_options_coupons` (the same pivot is reused for
 * stores and coupons — see migration
 * 2020_08_26_030734_create_search_options_coupons_table) are upserted via
 * `updateOrInsert` so re-running the seeder will not produce duplicate
 * attachments. Stale attachments that are no longer declared in
 * `options.php` are intentionally NOT removed, matching the conservative
 * "never destroy data" posture of the other seeders in this project.
 */
class OptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = require database_path('seeders/data/options.php');
        $languages = Languages::query()->pluck('shortcut')->all();

        foreach ($options as $row) {
            $option = $this->resolveOption($row);

            foreach ($row['pages'] ?? [] as $language => $pageData) {
                if (!in_array($language, $languages, true)) {
                    continue;
                }

                $option->pages()->updateOrCreate(
                    ['language' => $language],
                    ['name' => $pageData['name']]
                );
            }

            $this->attachStores($option, $row['stores'] ?? []);
            $this->attachCoupons($option, $row['coupons'] ?? []);
        }

        $this->command?->info(sprintf('Seeded %d search options.', count($options)));
    }

    /**
     * Find an option by its GB page name (the stable identifier this seeder
     * uses) or create a fresh one.
     */
    protected function resolveOption(array $row): SearchOptions
    {
        $gbName = $row['pages']['GB']['name'] ?? null;

        if ($gbName !== null) {
            $existing = SearchOptionsPages::query()
                ->where('language', 'GB')
                ->where('name', $gbName)
                ->first();

            if ($existing && $existing->search_option_id) {
                return SearchOptions::query()->findOrFail($existing->search_option_id);
            }
        }

        return SearchOptions::create([]);
    }

    /**
     * Attach a search option to stores resolved by their GB store_pages slug.
     */
    protected function attachStores(SearchOptions $option, array $storeSlugs): void
    {
        if (empty($storeSlugs)) {
            return;
        }

        $storeIds = StorePage::query()
            ->whereIn('slug', $storeSlugs)
            ->pluck('store_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($storeIds as $storeId) {
            // (search_option_id, store_id) is unique on the pivot — upsert
            // so re-running the seeder doesn't duplicate the attachment.
            DB::table('search_options_coupons')->updateOrInsert(
                [
                    'search_option_id' => $option->id,
                    'store_id' => $storeId,
                    'coupon_id' => null,
                ],
                [
                    'search_option_id' => $option->id,
                    'store_id' => $storeId,
                    'coupon_id' => null,
                ]
            );
        }
    }

    /**
     * Attach a search option to coupons resolved by their `coupon_key`.
     *
     * `coupon_key` is unique per store in practice; if the same key is used
     * across multiple stores this will attach the option to all of them.
     */
    protected function attachCoupons(SearchOptions $option, array $couponKeys): void
    {
        if (empty($couponKeys)) {
            return;
        }

        $couponIds = Coupon::query()
            ->whereIn('coupon_key', $couponKeys)
            ->pluck('id')
            ->all();

        foreach ($couponIds as $couponId) {
            DB::table('search_options_coupons')->updateOrInsert(
                [
                    'search_option_id' => $option->id,
                    'coupon_id' => $couponId,
                    'store_id' => null,
                ],
                [
                    'search_option_id' => $option->id,
                    'coupon_id' => $couponId,
                    'store_id' => null,
                ]
            );
        }
    }
}
