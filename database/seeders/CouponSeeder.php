<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Languages;
use App\Models\StorePage;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Idempotent coupon seeder.
 *
 * - The store the coupon belongs to is resolved by matching `store_slug`
 *   against `store_pages.slug` (the same lookup StoreSeeder uses for
 *   identity). This avoids hard-coding `store_id` values.
 * - `coupons` rows are upserted on the `(store_id, coupon_key)` pair —
 *   `coupon_key` itself is not unique in the schema, but it is unique
 *   per store in practice and is the natural code customers redeem.
 * - `coupon_pages` rows are upserted on the `(coupon_id, language)`
 *   composite unique. Default SEO meta tags (config/seo.php) are seeded
 *   once per page via firstOrCreate keyed by tag name.
 *
 * Search-option attachments declared inline on a coupon are intentionally
 * NOT applied here — the OptionSeeder owns the `search_options_coupons`
 * pivot so the relationship can be re-synced cleanly when the option list
 * changes. The `options` array on each coupon row is consumed by
 * OptionSeeder via reverse lookup.
 */
class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = require database_path('seeders/data/coupons.php');
        $languages = Languages::query()->pluck('shortcut')->all();
        $defaultMetaTags = (array) config('seo.default_meta_tags', []);

        foreach ($coupons as $row) {
            // Resolve via the GB store_pages slug specifically — that's the
            // canonical identifier we use across the catalog seeders. Pinning
            // the language guards against accidentally matching an AR/other
            // localized slug that happens to collide.
            $storePage = StorePage::query()
                ->where('slug', $row['store_slug'])
                ->where('language', 'GB')
                ->first();

            // Fail loudly: silently skipping leaves the DB in a state where
            // a coupon row exists but its `store_id` is null (the exact bug
            // the user reported). It is far better to abort the whole seed
            // run and surface the bad data than to ship an orphan coupon.
            if (!$storePage || !$storePage->store_id) {
                throw new RuntimeException(sprintf(
                    'CouponSeeder: cannot resolve store for coupon "%s" — '
                    .'no store_pages row with slug="%s" and language="GB". '
                    .'Run StoreSeeder first or fix the store_slug in '
                    .'database/seeders/data/coupons.php.',
                    $row['coupon_key'],
                    $row['store_slug']
                ));
            }

            $coupon = Coupon::updateOrCreate(
                [
                    'store_id' => $storePage->store_id,
                    'coupon_key' => $row['coupon_key'],
                ],
                [
                    'redirect_url' => $row['redirect_url'] ?? null,
                    'percentage' => $row['percentage'] ?? '0',
                    'valid' => $row['valid'] ?? true,
                    'valid_until' => $row['valid_until'] ?? null,
                    'store_link' => $row['store_link'] ?? null,
                ]
            );

            foreach ($row['pages'] ?? [] as $language => $pageData) {
                if (!in_array($language, $languages, true)) {
                    continue;
                }

                $page = $coupon->pages()->updateOrCreate(
                    ['language' => $language],
                    [
                        'title' => $pageData['title'],
                        'slug' => $pageData['slug'],
                        'description' => $pageData['description'] ?? null,
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

        $this->command?->info(sprintf('Seeded %d coupons.', count($coupons)));
    }
}
