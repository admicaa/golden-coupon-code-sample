<?php

/*
 |--------------------------------------------------------------------------
 | Coupon dump / demo data
 |--------------------------------------------------------------------------
 |
 | The `coupons` table holds the redeemable code (`coupon_key`), redirect
 | URL, validity flags and a percentage label. Per-language metadata
 | (title, description, slug) lives on `coupon_pages` with a unique
 | composite of `(coupon_id, language)` and a globally unique `slug`.
 |
 | For seeder idempotency the CouponSeeder identifies a coupon row by
 | (`store_slug`, `coupon_key`) — `store_slug` resolves to the underlying
 | store via that store's GB `store_pages.slug`. `valid_until` accepts a
 | parseable date string or null.
 |
 | The optional `options` array references search-option keys defined in
 | `options.php` and is used to attach this coupon to those filters
 | through the `search_options_coupons` pivot.
 |
 */

return [
    [
        'store_slug' => 'noon-sa',
        'coupon_key' => 'NOON10',
        'percentage' => '10',
        'redirect_url' => 'https://www.noon.com/saudi-en/',
        'store_link' => 'https://www.noon.com/saudi-en/',
        'valid' => true,
        'valid_until' => null,
        'pages' => [
            'GB' => [
                'title' => '10% off Noon Saudi Arabia',
                'slug' => 'noon-sa-10-off-gb',
                'description' => 'Get 10% off your next Noon Saudi Arabia order with this verified code.',
            ],
            'AR' => [
                'title' => 'خصم 10٪ على نون السعودية',
                'slug' => 'noon-sa-10-off-ar',
                'description' => 'احصل على خصم 10٪ على طلبك القادم من نون السعودية بهذا الكود الموثق.',
            ],
        ],
        'options' => ['electronics', 'fashion'],
    ],
    [
        'store_slug' => 'noon-sa',
        'coupon_key' => 'FREESHIP',
        'percentage' => '0',
        'redirect_url' => 'https://www.noon.com/saudi-en/free-shipping',
        'store_link' => 'https://www.noon.com/saudi-en/',
        'valid' => true,
        'valid_until' => null,
        'pages' => [
            'GB' => [
                'title' => 'Free shipping on Noon Saudi Arabia',
                'slug' => 'noon-sa-free-shipping-gb',
                'description' => 'Enjoy free shipping on eligible Noon Saudi Arabia orders.',
            ],
            'AR' => [
                'title' => 'شحن مجاني من نون السعودية',
                'slug' => 'noon-sa-free-shipping-ar',
                'description' => 'استمتع بشحن مجاني على طلبات نون السعودية المؤهلة.',
            ],
        ],
        'options' => ['shipping'],
    ],
    [
        'store_slug' => 'noon-sa',
        'coupon_key' => 'NOONSA',
        'percentage' => '15',
        'redirect_url' => 'https://www.noon.com/saudi-en/sale',
        'store_link' => 'https://www.noon.com/saudi-en/',
        'valid' => true,
        'valid_until' => '2027-12-31 23:59:59',
        'pages' => [
            'GB' => [
                'title' => '15% off Noon SA sitewide',
                'slug' => 'noon-sa-15-sitewide-gb',
                'description' => 'Sitewide 15% discount on Noon Saudi Arabia.',
            ],
            'AR' => [
                'title' => 'خصم 15٪ على نون السعودية',
                'slug' => 'noon-sa-15-sitewide-ar',
                'description' => 'خصم 15٪ على جميع منتجات نون السعودية.',
            ],
        ],
        'options' => ['electronics'],
    ],
    [
        'store_slug' => 'noon-ae',
        'coupon_key' => 'NOONAE20',
        'percentage' => '20',
        'redirect_url' => 'https://www.noon.com/uae-en/',
        'store_link' => 'https://www.noon.com/uae-en/',
        'valid' => true,
        'valid_until' => null,
        'pages' => [
            'GB' => [
                'title' => '20% off Noon UAE',
                'slug' => 'noon-ae-20-off-gb',
                'description' => 'Get 20% off your Noon UAE order with this verified code.',
            ],
            'AR' => [
                'title' => 'خصم 20٪ على نون الإمارات',
                'slug' => 'noon-ae-20-off-ar',
                'description' => 'احصل على خصم 20٪ على طلبك من نون الإمارات.',
            ],
        ],
        'options' => ['fashion'],
    ],
    [
        'store_slug' => 'amazon-sa',
        'coupon_key' => 'AMZSA5',
        'percentage' => '5',
        'redirect_url' => 'https://www.amazon.sa',
        'store_link' => 'https://www.amazon.sa',
        'valid' => true,
        'valid_until' => null,
        'pages' => [
            'GB' => [
                'title' => '5% off Amazon Saudi Arabia',
                'slug' => 'amazon-sa-5-off-gb',
                'description' => 'Stack 5% extra savings on top of Amazon SA deals.',
            ],
            'AR' => [
                'title' => 'خصم 5٪ على أمازون السعودية',
                'slug' => 'amazon-sa-5-off-ar',
                'description' => 'احصل على خصم إضافي 5٪ على عروض أمازون السعودية.',
            ],
        ],
        'options' => ['electronics'],
    ],
    [
        'store_slug' => 'namshi-ae',
        'coupon_key' => 'NAMSHI25',
        'percentage' => '25',
        'redirect_url' => 'https://www.namshi.com/uae-en/',
        'store_link' => 'https://www.namshi.com/uae-en/',
        'valid' => true,
        'valid_until' => null,
        'pages' => [
            'GB' => [
                'title' => '25% off Namshi UAE',
                'slug' => 'namshi-ae-25-off-gb',
                'description' => 'Get 25% off Namshi UAE fashion with this verified code.',
            ],
            'AR' => [
                'title' => 'خصم 25٪ على نمشي الإمارات',
                'slug' => 'namshi-ae-25-off-ar',
                'description' => 'احصل على خصم 25٪ على أزياء نمشي الإمارات.',
            ],
        ],
        'options' => ['fashion'],
    ],
    [
        'store_slug' => 'jumia-eg',
        'coupon_key' => 'JUMIAEG',
        'percentage' => '12',
        'redirect_url' => 'https://www.jumia.com.eg',
        'store_link' => 'https://www.jumia.com.eg',
        'valid' => true,
        'valid_until' => null,
        'pages' => [
            'GB' => [
                'title' => '12% off Jumia Egypt',
                'slug' => 'jumia-eg-12-off-gb',
                'description' => 'Save 12% on Jumia Egypt orders with this verified code.',
            ],
            'AR' => [
                'title' => 'خصم 12٪ على جوميا مصر',
                'slug' => 'jumia-eg-12-off-ar',
                'description' => 'وفر 12٪ على طلبات جوميا مصر بهذا الكود الموثق.',
            ],
        ],
        'options' => ['electronics', 'shipping'],
    ],
    [
        'store_slug' => 'trendyol-tr',
        'coupon_key' => 'TRENDY30',
        'percentage' => '30',
        'redirect_url' => 'https://www.trendyol.com',
        'store_link' => 'https://www.trendyol.com',
        'valid' => true,
        'valid_until' => null,
        'pages' => [
            'GB' => [
                'title' => '30% off Trendyol fashion',
                'slug' => 'trendyol-tr-30-off-gb',
                'description' => 'Get 30% off Trendyol fashion items with this verified code.',
            ],
            'AR' => [
                'title' => 'خصم 30٪ على أزياء تريند يول',
                'slug' => 'trendyol-tr-30-off-ar',
                'description' => 'احصل على خصم 30٪ على أزياء تريند يول.',
            ],
        ],
        'options' => ['fashion'],
    ],
];
