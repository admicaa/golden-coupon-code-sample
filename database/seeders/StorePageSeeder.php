<?php

namespace Database\Seeders;

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

            $this->sections->save([
                [
                    'template' => 0,
                    'is_blog' => false,
                    'pages' => $this->buildPages($store),
                    'contents' => $store->coupons
                        ->sortByDesc(fn ($coupon) => (float) $coupon->percentage)
                        ->values()
                        ->map(fn ($coupon) => [
                            'type' => 'coupon',
                            'coupon_id' => $coupon->id,
                        ])
                        ->all(),
                ],
            ], 'store_id', $store->id);

            $seeded++;
        }

        $this->command?->info(sprintf(
            'Seeded store page sections for %d store(s); skipped %d.',
            $seeded,
            $skipped
        ));
    }

    protected function buildPages(Store $store): array
    {
        $localizedPages = $store->pages->keyBy('language');
        $fallback = $localizedPages->get('GB') ?: $localizedPages->first();

        return languages()
            ->mapWithKeys(function ($language) use ($localizedPages, $fallback) {
                $page = $localizedPages->get($language->shortcut) ?: $fallback;
                $name = $page?->name ?: $page?->title ?: 'Store';

                if ($language->shortcut === 'AR') {
                    return [$language->shortcut => [
                        'title' => 'أفضل كوبونات ' . $name,
                        'subtitle' => null,
                        'description' => 'استعرض أحدث أكواد الخصم والعروض المتاحة لمتجر ' . $name . '.',
                    ]];
                }

                return [$language->shortcut => [
                    'title' => 'Best ' . $name . ' coupons',
                    'subtitle' => null,
                    'description' => 'Explore the latest verified discount codes and deals for ' . $name . '.',
                ]];
            })
            ->all();
    }
}
