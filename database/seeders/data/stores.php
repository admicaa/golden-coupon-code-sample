<?php

/*
 |--------------------------------------------------------------------------
 | Store dump / demo data
 |--------------------------------------------------------------------------
 |
 | The `stores` table itself only carries `country_id` (see
 | 2020_06_18_042021_create_stores_table). The customer-facing slug, name,
 | title and body live on `store_pages` keyed by the unique
 | `(store_id, language)` composite. `store_pages.slug` is globally unique
 | and is what front-end URLs resolve against (MainPageController::store).
 |
 | For seeder idempotency we treat the GB page slug as the stable identity
 | of a store — the StoreSeeder looks an existing store up via that slug
 | before creating a new row. `country_code` resolves to a `countries.iso`.
 |
 */

return [
    [
        'country_code' => 'SA',
        // GB slug is the stable store identifier used by the seeder.
        'slug' => 'noon-sa',
        'pages' => [
            'GB' => [
                'name' => 'Noon',
                'title' => 'Noon Saudi Arabia coupons & promo codes',
                'slug' => 'noon-sa',
                'body' => 'Save on Noon SA orders with the latest verified coupons and promo codes.',
            ],
            'AR' => [
                'name' => 'نون',
                'title' => 'كوبونات وأكواد خصم نون السعودية',
                'slug' => 'noon-sa-ar',
                'body' => 'وفر على طلبات نون السعودية باستخدام أحدث كوبونات وأكواد الخصم الموثقة.',
            ],
        ],
    ],
    [
        'country_code' => 'AE',
        'slug' => 'noon-ae',
        'pages' => [
            'GB' => [
                'name' => 'Noon',
                'title' => 'Noon UAE coupons & promo codes',
                'slug' => 'noon-ae',
                'body' => 'Verified Noon UAE discount codes updated regularly.',
            ],
            'AR' => [
                'name' => 'نون',
                'title' => 'كوبونات نون الإمارات',
                'slug' => 'noon-ae-ar',
                'body' => 'أكواد خصم نون الإمارات يتم تحديثها باستمرار.',
            ],
        ],
    ],
    [
        'country_code' => 'SA',
        'slug' => 'amazon-sa',
        'pages' => [
            'GB' => [
                'name' => 'Amazon',
                'title' => 'Amazon Saudi Arabia coupons & deals',
                'slug' => 'amazon-sa',
                'body' => 'Latest Amazon SA coupon codes for electronics, fashion and groceries.',
            ],
            'AR' => [
                'name' => 'أمازون',
                'title' => 'كوبونات وعروض أمازون السعودية',
                'slug' => 'amazon-sa-ar',
                'body' => 'أحدث أكواد خصم أمازون السعودية للإلكترونيات والأزياء والبقالة.',
            ],
        ],
    ],
    [
        'country_code' => 'AE',
        'slug' => 'namshi-ae',
        'pages' => [
            'GB' => [
                'name' => 'Namshi',
                'title' => 'Namshi UAE fashion coupons',
                'slug' => 'namshi-ae',
                'body' => 'Save on Namshi UAE fashion orders with verified discount codes.',
            ],
            'AR' => [
                'name' => 'نمشي',
                'title' => 'كوبونات نمشي الإمارات للأزياء',
                'slug' => 'namshi-ae-ar',
                'body' => 'وفر على طلبات نمشي الإمارات بأكواد خصم موثقة.',
            ],
        ],
    ],
    [
        'country_code' => 'EG',
        'slug' => 'jumia-eg',
        'pages' => [
            'GB' => [
                'name' => 'Jumia',
                'title' => 'Jumia Egypt coupons & promo codes',
                'slug' => 'jumia-eg',
                'body' => 'Latest Jumia Egypt deals and free shipping codes.',
            ],
            'AR' => [
                'name' => 'جوميا',
                'title' => 'كوبونات وأكواد جوميا مصر',
                'slug' => 'jumia-eg-ar',
                'body' => 'أحدث عروض جوميا مصر وأكواد الشحن المجاني.',
            ],
        ],
    ],
    [
        'country_code' => 'TR',
        'slug' => 'trendyol-tr',
        'pages' => [
            'GB' => [
                'name' => 'Trendyol',
                'title' => 'Trendyol coupons & discount codes',
                'slug' => 'trendyol-tr',
                'body' => 'Verified Trendyol Turkey coupon codes for fashion and electronics.',
            ],
            'AR' => [
                'name' => 'تريند يول',
                'title' => 'كوبونات وأكواد خصم تريند يول',
                'slug' => 'trendyol-tr-ar',
                'body' => 'كوبونات تريند يول تركيا للأزياء والإلكترونيات.',
            ],
        ],
    ],
];
