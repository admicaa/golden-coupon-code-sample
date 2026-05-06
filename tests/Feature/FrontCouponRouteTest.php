<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Languages;
use App\Models\Store;
use Tests\Concerns\RefreshMySqlDatabase;
use Tests\TestCase;

class FrontCouponRouteTest extends TestCase
{
    use RefreshMySqlDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);
    }

    public function test_coupon_route_returns_the_coupon_payload(): void
    {
        $coupon = $this->createCoupon();
        $slug = $coupon->pages()->where('language', 'GB')->value('slug');

        $this->getJson('/api/front/coupon/' . $slug)
            ->assertOk()
            ->assertJsonPath('id', $coupon->id)
            ->assertJsonPath('page.slug', $slug);
    }

    protected function createCoupon(): Coupon
    {
        $country = Country::create(['iso' => 'EG']);
        $country->names()->create([
            'language' => 'GB',
            'name' => 'Egypt',
            'header_name' => 'egypt',
        ]);

        $store = Store::create(['country_id' => $country->id]);
        $store->pages()->create([
            'language' => 'GB',
            'slug' => 'coupon-store',
            'name' => 'Coupon Store',
            'title' => 'Coupon Store',
            'body' => 'Store body',
        ]);

        $coupon = Coupon::create([
            'store_id' => $store->id,
            'coupon_key' => 'SAVE10',
            'redirect_url' => 'https://example.com/original',
            'percentage' => 10,
            'valid' => true,
        ]);

        $coupon->pages()->create([
            'language' => 'GB',
            'title' => 'Original Coupon',
            'slug' => 'original-coupon',
            'description' => 'Original description',
        ]);

        return $coupon->fresh();
    }
}
