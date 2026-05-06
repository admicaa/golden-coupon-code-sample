<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Section;
use App\Models\Store;
use App\Services\MainPageSectionsService;
use Illuminate\Database\Seeder;

class MainPageSeeder extends Seeder
{
    public function __construct(
        protected MainPageSectionsService $sections,
    ) {
    }

    public function run(): void
    {
        if (Section::query()->where('page_id', 1)->exists()) {
            $this->command?->info('Main page sections already exist; skipping.');
            return;
        }

        $coupons = Coupon::query()
            ->where('valid', true)
            ->whereHas('pages')
            ->orderByDesc('percentage')
            ->limit(6)
            ->get();

        $stores = Store::query()
            ->whereHas('pages')
            ->withCount([
                'coupons' => function ($query) {
                    $query->where('valid', true);
                },
            ])
            ->orderByDesc('coupons_count')
            ->orderBy('id')
            ->limit(6)
            ->get();

        $countries = Country::query()
            ->whereHas('names')
            ->orderBy('iso')
            ->get();

        $sections = [];

        if ($coupons->isNotEmpty()) {
            $sections[] = [
                'template' => 0,
                'is_blog' => false,
                'pages' => $this->pageCopy(
                    'Featured coupons',
                    'Fresh discount codes from popular stores.',
                    'كوبونات مميزة',
                    'أحدث أكواد الخصم من أشهر المتاجر.'
                ),
                'contents' => $coupons->map(fn ($coupon) => [
                    'type' => 'coupon',
                    'coupon_id' => $coupon->id,
                ])->all(),
            ];
        }

        if ($stores->isNotEmpty()) {
            $sections[] = [
                'template' => 0,
                'is_blog' => false,
                'pages' => $this->pageCopy(
                    'Popular stores',
                    'Browse the stores with the most active offers right now.',
                    'متاجر شائعة',
                    'تصفح المتاجر التي تحتوي على أكثر العروض نشاطا حاليا.'
                ),
                'contents' => $stores->map(fn ($store) => [
                    'type' => 'store',
                    'store_id' => $store->id,
                ])->all(),
            ];
        }

        if ($countries->isNotEmpty()) {
            $sections[] = [
                'template' => 0,
                'is_blog' => false,
                'pages' => $this->pageCopy(
                    'Browse by country',
                    'Jump straight into the markets we cover.',
                    'تصفح حسب الدولة',
                    'انتقل مباشرة إلى الأسواق التي نغطيها.'
                ),
                'contents' => $countries->map(fn ($country) => [
                    'type' => 'country',
                    'country_id' => $country->id,
                ])->all(),
            ];
        }

        if (empty($sections)) {
            $this->command?->warn('MainPageSeeder found no countries, stores, or coupons to seed.');
            return;
        }

        $this->sections->save($sections, 'page_id', 1);

        $this->command?->info(sprintf(
            'Seeded %d main page section(s).',
            count($sections)
        ));
    }

    protected function pageCopy(
        string $gbTitle,
        string $gbDescription,
        string $arTitle,
        string $arDescription,
    ): array {
        return [
            'GB' => [
                'title' => $gbTitle,
                'subtitle' => null,
                'description' => $gbDescription,
            ],
            'AR' => [
                'title' => $arTitle,
                'subtitle' => null,
                'description' => $arDescription,
            ],
        ];
    }
}
