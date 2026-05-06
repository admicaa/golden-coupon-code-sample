<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Languages;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class StoreIndexFiltersTest extends TestCase
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

    public function test_index_filters_by_country_id(): void
    {
        $this->actingAsAdminWithPermissions(['view-stores']);
        $egypt = $this->createCountry('EG', 'Egypt');
        $uae = $this->createCountry('AE', 'United Arab Emirates');

        $this->createStore($egypt, 'egypt-store', 'Egypt Store');
        $this->createStore($uae, 'uae-store', 'UAE Store');

        $response = $this->getJson('/api/stores?country_id=' . $egypt->id);

        $response->assertOk();
        $this->assertSame(1, $response->json('total'));
        $this->assertSame('Egypt Store', $response->json('data.0.page.name'));
    }

    public function test_index_filters_by_search_term_against_page_name_and_title(): void
    {
        $this->actingAsAdminWithPermissions(['view-stores']);
        $country = $this->createCountry('EG', 'Egypt');

        $this->createStore($country, 'apple-store', 'Apple Store');
        $this->createStore($country, 'banana-store', 'Banana Store');

        $response = $this->getJson('/api/stores?search=Apple');

        $response->assertOk();
        $this->assertSame(1, $response->json('total'));
        $this->assertSame('Apple Store', $response->json('data.0.page.name'));
    }

    public function test_index_eager_loads_country_when_flag_is_set(): void
    {
        $this->actingAsAdminWithPermissions(['view-stores']);
        $country = $this->createCountry('EG', 'Egypt');
        $this->createStore($country, 'flagged-store', 'Flagged Store');

        $response = $this->getJson('/api/stores?country=1');

        $response->assertOk()
            ->assertJsonPath('data.0.country.id', $country->id);
    }

    public function test_index_requires_view_stores_permission(): void
    {
        $this->actingAsAdminWithPermissions([]);

        $this->getJson('/api/stores')->assertStatus(403);
    }

    protected function createCountry(string $iso, string $name): Country
    {
        $country = Country::create(['iso' => $iso]);
        $country->names()->create([
            'language' => 'GB',
            'name' => $name,
            'header_name' => strtolower(str_replace(' ', '-', $name)),
        ]);

        return $country;
    }

    protected function createStore(Country $country, string $slug, string $name): Store
    {
        $store = Store::create(['country_id' => $country->id]);
        $store->pages()->create([
            'language' => 'GB',
            'slug' => $slug,
            'name' => $name,
            'title' => $name,
            'body' => $name . ' body',
        ]);

        return $store;
    }
}
