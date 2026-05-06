<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Languages;
use App\Models\Store;
use Tests\Concerns\RefreshMySqlDatabase;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class CouponIndexFiltersTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshMySqlDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);
    }

    public function test_index_filters_by_store_id(): void
    {
        $this->actingAsAdminWithPermissions(['view-coupons']);

        $storeA = $this->createStore('A');
        $storeB = $this->createStore('B');

        $this->createCoupon($storeA, 'AKEY10');
        $this->createCoupon($storeB, 'BKEY10');

        $response = $this->getJson('/api/coupons?store_id=' . $storeA->id);

        $response->assertOk();
        $this->assertSame(1, $response->json('total'));
        $this->assertSame('AKEY10', $response->json('data.0.coupon_key'));
    }

    public function test_index_filters_by_search_term_against_coupon_key(): void
    {
        $this->actingAsAdminWithPermissions(['view-coupons']);
        $store = $this->createStore('S');

        $this->createCoupon($store, 'SAVE20');
        $this->createCoupon($store, 'PROMO10');

        $response = $this->getJson('/api/coupons?search=SAVE');

        $response->assertOk();
        $this->assertSame(1, $response->json('total'));
        $this->assertSame('SAVE20', $response->json('data.0.coupon_key'));
    }

    public function test_index_requires_view_coupons_permission(): void
    {
        $this->actingAsAdminWithPermissions([]);

        $this->getJson('/api/coupons')->assertStatus(403);
    }

    protected function createStore(string $suffix): Store
    {
        $country = Country::create(['iso' => 'E' . $suffix]);
        $country->names()->create([
            'language' => 'GB',
            'name' => 'Country ' . $suffix,
            'header_name' => 'country-' . strtolower($suffix),
        ]);

        $store = Store::create(['country_id' => $country->id]);
        $store->pages()->create([
            'language' => 'GB',
            'slug' => 'store-' . strtolower($suffix),
            'name' => 'Store ' . $suffix,
            'title' => 'Store ' . $suffix,
            'body' => 'Body',
        ]);

        return $store;
    }

    protected function createCoupon(Store $store, string $key): Coupon
    {
        $coupon = Coupon::create([
            'store_id' => $store->id,
            'coupon_key' => $key,
            'redirect_url' => 'https://example.com/' . $key,
            'percentage' => 10,
            'valid' => true,
        ]);

        $coupon->pages()->create([
            'language' => 'GB',
            'title' => 'Coupon ' . $key,
            'slug' => 'coupon-' . strtolower($key),
            'description' => $key . ' description',
        ]);

        return $coupon;
    }
}
