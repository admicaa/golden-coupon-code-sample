<?php

namespace App\Services\Catalog;

use App\Models\Coupon;
use App\Models\CouponPages;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function create(array $data)
    {
        $coupon = DB::transaction(function () use ($data) {
            $coupon = Coupon::create([
                'store_id' => $data['store_id'],
                'redirect_url' => $data['redirect_url'],
                'percentage' => $data['percentage'],
                'coupon_key' => $data['coupon_key'],
                'valid' => $data['valid'],
                'valid_until' => $data['valid_until'] ?? null,
            ]);

            foreach ($data['pages'] as $language => $pageData) {
                $page = $coupon->pages()->create([
                    'language' => $language,
                    'title' => $pageData['title'],
                    'slug' => $pageData['slug'],
                    'description' => $pageData['description'] ?? null,
                ]);

                $this->createDefaultMetaTags($page);
            }

            return $coupon;
        });

        return $coupon->adminFormula()->find($coupon->id);
    }

    public function update(Coupon $coupon, array $data)
    {
        $coupon->update([
            'coupon_key' => $data['coupon_key'],
            'valid' => $data['valid'],
            'valid_until' => $data['valid_until'] ?? null,
            'redirect_url' => $data['redirect_url'],
            'percentage' => $data['percentage'],
        ]);

        return $coupon;
    }

    public function updatePage(CouponPages $page, array $data)
    {
        $page->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
        ]);

        return $page->coupon->adminFormula()->find($page->coupon_id);
    }

    protected function createDefaultMetaTags(CouponPages $page)
    {
        foreach (config('seo.default_meta_tags', []) as $tag) {
            $page->metatags()->firstOrCreate(
                ['name' => $tag['name']],
                ['value' => $tag['value']]
            );
        }
    }
}
