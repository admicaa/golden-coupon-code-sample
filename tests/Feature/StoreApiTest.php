<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Languages;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class StoreApiTest extends TestCase
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

    public function test_authorized_admin_can_create_a_store()
    {
        $this->actingAsAdminWithPermissions(['create-stores']);
        $country = $this->createCountry();

        $response = $this->postJson('/api/stores', [
            'country_id' => $country->id,
            'pages' => [
                'GB' => [
                    'slug' => 'service-store',
                    'name' => 'Service Store',
                    'title' => 'Service Store',
                    'body' => 'Store body',
                ],
            ],
        ]);

        $storeId = $response->json('id');

        $response->assertOk()
            ->assertJsonPath('id', $storeId)
            ->assertJsonPath('page.slug', 'service-store')
            ->assertJsonPath('page.name', 'Service Store');

        $this->assertDatabaseHas('stores', [
            'id' => $storeId,
            'country_id' => $country->id,
        ]);
        $this->assertDatabaseHas('store_pages', [
            'store_id' => $storeId,
            'language' => 'GB',
            'slug' => 'service-store',
            'name' => 'Service Store',
        ]);
    }

    public function test_authorized_admin_can_update_store_page_content()
    {
        $this->actingAsAdminWithPermissions(['edit-stores']);
        $store = $this->createStore();
        $page = $store->pages()->where('language', 'GB')->firstOrFail();

        $response = $this->putJson('/api/stores/pages/' . $page->id, [
            'slug' => 'updated-store',
            'name' => 'Updated Store',
            'title' => 'Updated Store',
            'body' => 'Updated body',
        ]);

        $response->assertOk()
            ->assertJsonPath('slug', 'updated-store')
            ->assertJsonPath('name', 'Updated Store')
            ->assertJsonPath('body', 'Updated body');

        $this->assertDatabaseHas('store_pages', [
            'id' => $page->id,
            'slug' => 'updated-store',
            'name' => 'Updated Store',
            'body' => 'Updated body',
        ]);
    }

    public function test_authorized_admin_can_add_store_images()
    {
        Storage::fake('local');

        $this->actingAsAdminWithPermissions(['edit-stores']);
        $store = $this->createStore();

        $response = $this->post('/api/stores/images/' . $store->id, [
            'images' => [
                UploadedFile::fake()->image('logo.jpg'),
            ],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.store_id', $store->id);

        $this->assertDatabaseHas('store_images', [
            'store_id' => $store->id,
        ]);
    }

    protected function createCountry()
    {
        $country = Country::create(['iso' => 'EG']);
        $country->names()->create([
            'language' => 'GB',
            'name' => 'Egypt',
            'header_name' => 'egypt',
        ]);

        return $country;
    }

    protected function createStore()
    {
        $store = Store::create(['country_id' => $this->createCountry()->id]);
        $store->pages()->create([
            'language' => 'GB',
            'slug' => 'existing-store',
            'name' => 'Existing Store',
            'title' => 'Existing Store',
            'body' => 'Existing body',
        ]);

        return $store;
    }
}
