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

        $topCoupons = Coupon::query()
            ->where('valid', true)
            ->whereHas('pages')
            ->orderByDesc('percentage')
            ->limit(6)
            ->get();

        $latestCoupons = Coupon::query()
            ->where('valid', true)
            ->whereHas('pages')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $stores = Store::query()
            ->whereHas('pages')
            ->withCount([
                'coupons' => function ($query) {
                    $query->where('valid', true)->whereHas('pages');
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

        if ($topCoupons->isNotEmpty() || $stores->isNotEmpty() || $countries->isNotEmpty()) {
            $sections[] = [
                'template' => 3,
                'is_blog' => false,
                'pages' => $this->pageCopy(
                    'Today\'s best savings in one place',
                    sprintf(
                        'Browse %d featured coupon%s, %d trusted store%s, and %d market%s from the seeded catalog.',
                        $topCoupons->count(),
                        $topCoupons->count() === 1 ? '' : 's',
                        $stores->count(),
                        $stores->count() === 1 ? '' : 's',
                        $countries->count(),
                        $countries->count() === 1 ? '' : 's'
                    ),
                    'أفضل العروض في مكان واحد',
                    'ابدأ بأقوى الكوبونات والمتاجر والدول المجهزة داخل الكتالوج التجريبي.'
                ),
                'contents' => [],
            ];
        }

        if ($topCoupons->isNotEmpty()) {
            $sections[] = [
                'template' => 2,
                'is_blog' => false,
                'pages' => $this->pageCopy(
                    'Featured coupons',
                    'Fresh discount codes from popular stores in a quick swipeable row.',
                    'كوبونات مميزة',
                    'أحدث أكواد الخصم من أشهر المتاجر في صف سريع التصفح.'
                ),
                'contents' => $topCoupons->map(fn ($coupon) => [
                    'type' => 'coupon',
                    'coupon_id' => $coupon->id,
                ])->all(),
            ];
        }

        if ($latestCoupons->isNotEmpty()) {
            $sections[] = [
                'template' => 0,
                'is_blog' => false,
                'pages' => $this->pageCopy(
                    'Fresh coupon codes',
                    'Recently seeded deals ready to open from the main page.',
                    'أحدث أكواد الخصم',
                    'كوبونات تمت إضافتها حديثا ويمكن فتحها مباشرة من الصفحة الرئيسية.'
                ),
                'contents' => $latestCoupons->map(fn ($coupon) => [
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
                'template' => 2,
                'is_blog' => false,
                'pages' => $this->pageCopy(
                    'Browse by country',
                    'Jump straight into the markets we cover with a compact carousel.',
                    'تصفح حسب الدولة',
                    'انتقل مباشرة إلى الأسواق التي نغطيها عبر شريط سريع.'
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
