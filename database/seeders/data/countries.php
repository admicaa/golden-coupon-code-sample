<?php

/*
 |--------------------------------------------------------------------------
 | Country dump / demo data
 |--------------------------------------------------------------------------
 |
 | Each entry maps to one row in `countries` (keyed by the unique `iso`
 | column) plus one row per language in `country_names` (keyed by the
 | composite `(country_id, language)` unique). `header_name` is what the
 | front-end uses as the country slug in URLs (see MainPageController::country
 | which looks the country up via `country_names.header_name`).
 |
 | Languages must match `languages.shortcut` rows seeded by
 | mainLanguageSeeder (currently `GB` and `AR`).
 |
 */

return [
    [
        'iso' => 'SA',
        'names' => [
            'GB' => [
                'name' => 'Saudi Arabia',
                'header_name' => 'saudi-arabia',
            ],
            'AR' => [
                'name' => 'المملكة العربية السعودية',
                'header_name' => 'al-saudia',
            ],
        ],
    ],
    [
        'iso' => 'AE',
        'names' => [
            'GB' => [
                'name' => 'United Arab Emirates',
                'header_name' => 'united-arab-emirates',
            ],
            'AR' => [
                'name' => 'الإمارات العربية المتحدة',
                'header_name' => 'al-emarat',
            ],
        ],
    ],
    [
        'iso' => 'EG',
        'names' => [
            'GB' => [
                'name' => 'Egypt',
                'header_name' => 'egypt',
            ],
            'AR' => [
                'name' => 'مصر',
                'header_name' => 'masr',
            ],
        ],
    ],
    [
        'iso' => 'TR',
        'names' => [
            'GB' => [
                'name' => 'Turkey',
                'header_name' => 'turkey',
            ],
            'AR' => [
                'name' => 'تركيا',
                'header_name' => 'turkia',
            ],
        ],
    ],
];
