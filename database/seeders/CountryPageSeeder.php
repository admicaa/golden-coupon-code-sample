<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Coupon;
use App\Services\MainPageSectionsService;
use Illuminate\Database\Seeder;

class CountryPageSeeder extends Seeder
{
    public function __construct(
        protected MainPageSectionsService $sections,
    ) {
    }

    public function run(): void
    {
        $seeded = 0;
        $skipped = 0;

        $countries = Country::query()
            ->with('names')
            ->get();

        foreach ($countries as $country) {
            if ($country->sections()->exists()) {
                $skipped++;
                continue;
            }

            $stores = $country->stores()
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

            $coupons = Coupon::query()
                ->where('valid', true)
                ->whereHas('pages')
                ->whereHas('store', function ($query) use ($country) {
                    $query->where('country_id', $country->id);
                })
                ->orderByDesc('percentage')
                ->limit(6)
                ->get();

            $latestCoupons = Coupon::query()
                ->where('valid', true)
                ->whereHas('pages')
                ->whereHas('store', function ($query) use ($country) {
                    $query->where('country_id', $country->id);
                })
                ->orderByDesc('id')
                ->limit(6)
                ->get();

            if ($stores->isEmpty() && $coupons->isEmpty()) {
                $skipped++;
                continue;
            }

            $otherCountries = Country::query()
                ->where('id', '!=', $country->id)
                ->whereHas('names')
                ->orderBy('iso')
                ->limit(3)
                ->get();

            $sections = [
                [
                    'template' => 3,
                    'is_blog' => false,
                    'pages' => $this->introCopy($country, $stores->count(), $coupons->count()),
                    'contents' => [],
                ],
            ];

            if ($coupons->isNotEmpty()) {
                $sections[] = [
                    'template' => 0,
                    'is_blog' => false,
                    'pages' => $this->localizedCopy(
                        'Top ' . $country->name . ' coupons',
                        'Best promo codes and verified deals for shoppers in ' . $country->name . '.',
                        'أفضل كوبونات ' . $country->name,
                        'أفضل أكواد الخصم والعروض الموثقة للمتسوقين في ' . $country->name . '.'
                    ),
                    'contents' => $coupons->map(fn ($coupon) => [
                        'type' => 'coupon',
                        'coupon_id' => $coupon->id,
                    ])->all(),
                ];
            }

            if ($latestCoupons->count() > 1) {
                $sections[] = [
                    'template' => 2,
                    'is_blog' => false,
                    'pages' => $this->localizedCopy(
                        'Fresh coupon pages in ' . $country->name,
                        'Open the most recently seeded coupon pages from this market in one swipe.',
                        'أحدث صفحات الكوبونات في ' . $country->name,
                        'افتح أحدث صفحات الكوبونات المضافة في هذا السوق عبر صف سريع.'
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
                    'pages' => $this->localizedCopy(
                        'Popular stores in ' . $country->name,
                        'Shop the stores with the most active offers in ' . $country->name . '.',
                        'متاجر شائعة في ' . $country->name,
                        'تسوق من المتاجر التي تحتوي على أكثر العروض نشاطا في ' . $country->name . '.'
                    ),
                    'contents' => $stores->map(fn ($store) => [
                        'type' => 'store',
                        'store_id' => $store->id,
                    ])->all(),
                ];
            }

            if ($otherCountries->isNotEmpty()) {
                $sections[] = [
                    'template' => 2,
                    'is_blog' => false,
                    'pages' => $this->localizedCopy(
                        'More markets to explore',
                        'Browse other countries for fresh coupon pages and store roundups in a compact carousel.',
                        'أسواق أخرى تستحق التصفح',
                        'تصفح دولا أخرى لاكتشاف صفحات كوبونات ومتاجر جديدة عبر شريط سريع.'
                    ),
                    'contents' => $otherCountries->map(fn ($otherCountry) => [
                        'type' => 'country',
                        'country_id' => $otherCountry->id,
                    ])->all(),
                ];
            }

            $this->sections->save($sections, 'country_id', $country->id);
            $seeded++;
        }

        $this->command?->info(sprintf(
            'Seeded country page sections for %d country(ies); skipped %d.',
            $seeded,
            $skipped
        ));
    }

    protected function introCopy(Country $country, int $storeCount, int $couponCount): array
    {
        return $this->localizedCopy(
            $country->name . ' coupon guide',
            sprintf(
                'Explore %d featured store%s and %d verified coupon%s for %s.',
                $storeCount,
                $storeCount === 1 ? '' : 's',
                $couponCount,
                $couponCount === 1 ? '' : 's',
                $country->name
            ),
            'دليل كوبونات ' . $country->name,
            'اكتشف أفضل المتاجر وأحدث أكواد الخصم الموثقة في ' . $country->name . '.'
        );
    }

    protected function localizedCopy(
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
