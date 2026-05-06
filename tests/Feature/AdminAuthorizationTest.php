<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Languages;
use App\Models\Store;
use App\Models\StoreImages;
use Tests\Concerns\RefreshMySqlDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

/**
 * Locks down the admin endpoints that historically had inconsistent
 * authorization (image edits, coupon page meta-tags, role / admin custom
 * actions). Each test asserts that an admin without the right permissions
 * receives a 403.
 */
class AdminAuthorizationTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshMySqlDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);
    }

    public function test_unauthorized_admin_cannot_edit_store_image(): void
    {
        $this->actingAsAdminWithPermissions([]);
        $store = $this->createStore();
        $image = $store->images()->create([
            'storage_path' => 'stores/' . $store->id . '/logo.jpg',
            'path' => '/storage/stores/' . $store->id . '/logo.jpg',
            'is_logo' => true,
        ]);

        $this->putJson('/api/stores/images/' . $image->id, [
            'path' => '/storage/stores/' . $store->id . '/changed.jpg',
        ])->assertStatus(403);
    }

    public function test_unauthorized_admin_cannot_delete_store_image(): void
    {
        $this->actingAsAdminWithPermissions([]);
        $store = $this->createStore();
        $image = $store->images()->create([
            'storage_path' => 'stores/' . $store->id . '/logo.jpg',
            'path' => '/storage/stores/' . $store->id . '/logo.jpg',
            'is_logo' => true,
        ]);

        $this->deleteJson('/api/stores/images/' . $image->id)->assertStatus(403);
    }

    public function test_unauthorized_admin_cannot_update_coupon_page_meta_tags(): void
    {
        $this->actingAsAdminWithPermissions([]);
        $coupon = $this->createCoupon();
        $page = $coupon->pages()->where('language', 'GB')->firstOrFail();

        $this->putJson('/api/coupons/tags/' . $page->id, [
            'content' => [
                ['name' => 'title', 'value' => 'X'],
            ],
        ])->assertStatus(403);
    }

    public function test_unauthorized_admin_cannot_update_coupon_page(): void
    {
        $this->actingAsAdminWithPermissions([]);
        $coupon = $this->createCoupon();
        $page = $coupon->pages()->where('language', 'GB')->firstOrFail();

        $this->putJson('/api/coupons/pages/' . $page->id, [
            'title' => 'X',
            'slug' => 'x',
        ])->assertStatus(403);
    }

    public function test_unauthorized_admin_cannot_delete_role(): void
    {
        $this->actingAsAdminWithPermissions([]);
        $role = $this->createRole('editor', ['view-articles']);

        $this->deleteJson('/api/roles/' . $role->id)->assertStatus(403);
    }

    public function test_unauthorized_admin_cannot_assign_search_options(): void
    {
        $this->actingAsAdminWithPermissions([]);
        $store = $this->createStore();

        $this->postJson('/api/search/options/assign', [
            'store_id' => $store->id,
            'options' => [],
        ])->assertStatus(403);
    }

    protected function createStore(): Store
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
            'slug' => 'guarded-store',
            'name' => 'Guarded Store',
            'title' => 'Guarded Store',
            'body' => 'body',
        ]);

        return $store;
    }

    protected function createCoupon(): Coupon
    {
        $coupon = Coupon::create([
            'store_id' => $this->createStore()->id,
            'coupon_key' => 'GUARDED10',
            'redirect_url' => 'https://example.com/guarded',
            'percentage' => 10,
            'valid' => true,
        ]);

        $coupon->pages()->create([
            'language' => 'GB',
            'title' => 'Guarded Coupon',
            'slug' => 'guarded-coupon',
            'description' => 'Guarded coupon description',
        ]);

        return $coupon;
    }
}
