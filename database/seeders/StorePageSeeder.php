<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Store;
use App\Services\MainPageSectionsService;
use Illuminate\Database\Seeder;

class StorePageSeeder extends Seeder
{
    public function __construct(
        protected MainPageSectionsService $sections,
    ) {
    }

    public function run(): void
    {
        $seeded = 0;
        $skipped = 0;

        $stores = Store::query()
            ->with([
                'pages',
                'country.names',
                'coupons' => function ($query) {
                    $query
                        ->where('valid', true)
                        ->whereHas('pages')
                        ->with('pages');
                },
            ])
            ->get();

        foreach ($stores as $store) {
            if ($store->sections()->exists() || $store->coupons->isEmpty()) {
                $skipped++;
                continue;
            }

            $relatedCoupons = Coupon::query()
                ->where('valid', true)
                ->whereHas('pages')
                ->whereHas('store', function ($query) use ($store) {
                    $query
                        ->where('country_id', $store->country_id)
                        ->where('id', '!=', $store->id);
                })
                ->orderByDesc('percentage')
                ->limit(6)
                ->get();

            $relatedStores = Store::query()
                ->where('country_id', $store->country_id)
                ->where('id', '!=', $store->id)
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

            $otherCountries = Country::query()
                ->where('id', '!=', $store->country_id)
                ->whereHas('names')
                ->orderBy('iso')
                ->limit(3)
                ->get();

            $sections = [
                [
                    'template' => 3,
                    'is_blog' => false,
                    'pages' => $this->introPages($store),
                    'contents' => [],
                ],
                [
                    'template' => 0,
                    'is_blog' => false,
                    'pages' => $this->couponsPages($store),
                    'contents' => $store->coupons
                        ->sortByDesc(fn ($coupon) => (float) $coupon->percentage)
                        ->values()
                        ->map(fn ($coupon) => [
                            'type' => 'coupon',
                            'coupon_id' => $coupon->id,
                        ])
                        ->all(),
                ],
            ];

            if ($relatedCoupons->isNotEmpty()) {
                $sections[] = [
                    'template' => 2,
                    'is_blog' => false,
                    'pages' => $this->relatedCouponsPages($store),
                    'contents' => $relatedCoupons->map(fn ($coupon) => [
                        'type' => 'coupon',
                        'coupon_id' => $coupon->id,
                    ])->all(),
                ];
            }

            if ($relatedStores->isNotEmpty()) {
                $sections[] = [
                    'template' => 2,
                    'is_blog' => false,
                    'pages' => $this->relatedStoresPages($store),
                    'contents' => $relatedStores->map(fn ($relatedStore) => [
                        'type' => 'store',
                        'store_id' => $relatedStore->id,
                    ])->all(),
                ];
            }

            if ($otherCountries->isNotEmpty()) {
                $sections[] = [
                    'template' => 0,
                    'is_blog' => false,
                    'pages' => $this->otherCountriesPages($store),
                    'contents' => $otherCountries->map(fn ($country) => [
                        'type' => 'country',
                        'country_id' => $country->id,
                    ])->all(),
                ];
            }

            $this->sections->save($sections, 'store_id', $store->id);

            $seeded++;
        }

        $this->command?->info(sprintf(
            'Seeded store page sections for %d store(s); skipped %d.',
            $seeded,
            $skipped
        ));
    }

    protected function introPages(Store $store): array
    {
        $name = $this->storeName($store);
        $countryName = $this->countryName($store->country);
        $couponCount = $store->coupons->count();

        return $this->localizedCopy(
            $name . ' coupon guide',
            sprintf(
                'Browse %d verified coupon%s for %s and keep exploring nearby store pages in %s.',
                $couponCount,
                $couponCount === 1 ? '' : 's',
                $name,
                $countryName
            ),
            'دليل كوبونات ' . $name,
            'تصفح أفضل كوبونات ' . $name . ' واستكشف متاجر إضافية داخل ' . $countryName . '.'
        );
    }

    protected function couponsPages(Store $store): array
    {
        $name = $this->storeName($store);

        return $this->localizedCopy(
            'Best ' . $name . ' coupons',
            'Explore the latest verified discount codes and deals for ' . $name . '.',
            'أفضل كوبونات ' . $name,
            'استعرض أحدث أكواد الخصم والعروض المتاحة لمتجر ' . $name . '.'
        );
    }

    protected function relatedCouponsPages(Store $store): array
    {
        $countryName = $this->countryName($store->country);

        return $this->localizedCopy(
            'More coupons from ' . $countryName,
            'Compare nearby coupon pages from the same market before you leave.',
            'كوبونات إضافية من ' . $countryName,
            'قارن بين كوبونات أخرى من نفس السوق قبل مغادرة الصفحة.'
        );
    }

    protected function relatedStoresPages(Store $store): array
    {
        $countryName = $this->countryName($store->country);

        return $this->localizedCopy(
            'Stores shoppers also browse in ' . $countryName,
            'Open related stores from the same market with active coupon pages.',
            'متاجر يتصفحها المستخدمون أيضا في ' . $countryName,
            'افتح متاجر مرتبطة من نفس السوق وتحتوي على صفحات كوبونات نشطة.'
        );
    }

    protected function otherCountriesPages(Store $store): array
    {
        $name = $this->storeName($store);

        return $this->localizedCopy(
            'More markets beyond ' . $name,
            'Jump to other country landing pages for broader coupon coverage.',
            'أسواق أخرى بجانب ' . $name,
            'انتقل إلى صفحات دول أخرى للحصول على تغطية أوسع للكوبونات.'
        );
    }

    protected function storeName(Store $store): string
    {
        $localizedPages = $store->pages->keyBy('language');
        $fallback = $localizedPages->get('GB') ?: $localizedPages->first();

        return $fallback?->name ?: $fallback?->title ?: 'Store';
    }

    protected function countryName(Country $country): string
    {
        $localizedNames = $country->names->keyBy('language');
        $fallback = $localizedNames->get('GB') ?: $localizedNames->first();

        return $fallback?->name ?: 'this market';
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
