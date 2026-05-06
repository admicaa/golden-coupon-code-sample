<?php

/*
 |--------------------------------------------------------------------------
 | Search option (filter) dump / demo data
 |--------------------------------------------------------------------------
 |
 | The project does NOT have a generic key/value settings table. The closest
 | "options" concept is the `search_options` filter system used to tag
 | coupons and stores for the on-site search facets (see SearchOptions
 | model and SearchFacetService). Each option has zero or more localized
 | rows in `search_options_pages` (`search_option_id` + `language`, with
 | a `name` column).
 |
 | Because `search_options` itself has no unique business key, the seeder
 | uses the GB page name as a stable identifier — `key` here matches the
 | English `pages.GB.name` value and lets us upsert idempotently.
 |
 | `stores` and `coupons` arrays are optional cross-references that the
 | OptionSeeder uses to build pivot rows in `search_options_coupons`
 | (the same pivot is reused for both attachments — see migration
 | 2020_08_26_030734_create_search_options_coupons_table).
 |
 | If new generic settings (e.g. site name, default tracking pixel) are
 | required later, they should be added to a dedicated config or settings
 | table; do not hijack `search_options` for that.
 |
 */

return [
    [
        'key' => 'Electronics',
        'pages' => [
            'GB' => ['name' => 'Electronics'],
            'AR' => ['name' => 'إلكترونيات'],
        ],
        'stores' => ['noon-sa', 'noon-ae', 'amazon-sa', 'jumia-eg'],
        'coupons' => ['NOON10', 'NOONSA', 'AMZSA5', 'JUMIAEG'],
    ],
    [
        'key' => 'Fashion',
        'pages' => [
            'GB' => ['name' => 'Fashion'],
            'AR' => ['name' => 'أزياء'],
        ],
        'stores' => ['noon-sa', 'noon-ae', 'namshi-ae', 'trendyol-tr'],
        'coupons' => ['NOON10', 'NOONAE20', 'NAMSHI25', 'TRENDY30'],
    ],
    [
        'key' => 'Shipping',
        'pages' => [
            'GB' => ['name' => 'Free Shipping'],
            'AR' => ['name' => 'شحن مجاني'],
        ],
        'stores' => ['noon-sa', 'jumia-eg'],
        'coupons' => ['FREESHIP', 'JUMIAEG'],
    ],
    [
        'key' => 'Groceries',
        'pages' => [
            'GB' => ['name' => 'Groceries'],
            'AR' => ['name' => 'بقالة'],
        ],
        'stores' => ['amazon-sa'],
        'coupons' => [],
    ],
];
