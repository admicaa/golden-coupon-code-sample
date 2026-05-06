<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Languages;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class CouponApiTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);
    }

    public function test_authorized_admin_can_create_a_coupon()
    {
        $this->actingAsAdminWithPermissions(['create-coupons']);
        $store = $this->createStore();

        $response = $this->postJson('/api/coupons', [
            'store_id' => $store->id,
            'coupon_key' => 'SAVE20',
            'valid' => true,
            'percentage' => 20,
            'redirect_url' => 'https://example.com/coupon',
            'pages' => [
                'GB' => [
                    'title' => 'Save 20',
                    'slug' => 'save-20',
                    'description' => 'Save big',
                ],
            ],
        ]);

        $couponId = $response->json('id');

        $response->assertOk()
            ->assertJsonPath('id', $couponId)
            ->assertJsonPath('page.slug', 'save-20')
            ->assertJsonPath('page.description', 'Save big');

        $this->assertDatabaseHas('coupons', [
            'id' => $couponId,
            'store_id' => $store->id,
            'coupon_key' => 'SAVE20',
        ]);
        $this->assertDatabaseHas('coupon_pages', [
            'coupon_id' => $couponId,
            'language' => 'GB',
            'slug' => 'save-20',
            'title' => 'Save 20',
        ]);
    }

    public function test_authorized_admin_can_update_coupon_page_content()
    {
        $this->actingAsAdminWithPermissions(['edit-coupons']);
        $coupon = $this->createCoupon();
        $page = $coupon->pages()->where('language', 'GB')->firstOrFail();

        $response = $this->putJson('/api/coupons/pages/' . $page->id, [
            'title' => 'Updated Coupon',
            'slug' => 'updated-coupon',
            'description' => 'Updated description',
        ]);

        $response->assertOk()
            ->assertJsonPath('page.slug', 'updated-coupon')
            ->assertJsonPath('page.description', 'Updated description');

        $this->assertDatabaseHas('coupon_pages', [
            'id' => $page->id,
            'slug' => 'updated-coupon',
            'description' => 'Updated description',
        ]);
    }

    protected function createStore()
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

        return $store;
    }

    protected function createCoupon()
    {
        $coupon = Coupon::create([
            'store_id' => $this->createStore()->id,
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

        return $coupon;
    }
}
