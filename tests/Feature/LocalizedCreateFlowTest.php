<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Languages;
use App\SearchOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class LocalizedCreateFlowTest extends TestCase
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

    public function test_store_create_only_persists_languages_that_were_provided()
    {
        $this->actingAsAdminWithPermissions(['create-stores']);
        $country = Country::create(['iso' => 'EG']);
        $country->names()->create([
            'language' => 'GB',
            'name' => 'Egypt',
            'header_name' => 'egypt',
        ]);

        $response = $this->postJson('/api/stores', [
            'country_id' => $country->id,
            'pages' => [
                'GB' => [
                    'slug' => 'cairo-store',
                    'name' => 'Cairo Store',
                    'title' => 'Cairo Store',
                    'body' => 'English body',
                ],
            ],
        ]);

        $storeId = $response->json('id');

        $response->assertOk()
            ->assertJsonPath('pages.0.language', 'GB');

        $this->assertDatabaseHas('store_pages', [
            'store_id' => $storeId,
            'language' => 'GB',
            'slug' => 'cairo-store',
            'name' => 'Cairo Store',
        ]);
        $this->assertDatabaseMissing('store_pages', [
            'store_id' => $storeId,
            'language' => 'AR',
        ]);

        $frontResponse = $this->withHeader('Content-Language', 'AR')
            ->getJson('/api/front/store/cairo-store');

        $frontResponse->assertOk()
            ->assertJsonPath('id', $storeId)
            ->assertJsonPath('page.slug', 'cairo-store');
    }

    public function test_country_and_search_option_create_keep_real_language_values_only()
    {
        $this->actingAsAdminWithPermissions(['create-countries', 'create-search-options']);

        $countryResponse = $this->postJson('/api/countries', [
            'iso' => 'AE',
            'names' => [
                'GB' => [
                    'name' => 'United Arab Emirates',
                    'header_name' => 'united-arab-emirates',
                ],
                'AR' => [
                    'name' => 'الامارات',
                    'header_name' => 'الامارات',
                ],
            ],
        ]);

        $countryId = $countryResponse->json('id');

        $countryResponse->assertOk();
        $this->assertDatabaseHas('country_names', [
            'country_id' => $countryId,
            'language' => 'GB',
            'name' => 'United Arab Emirates',
        ]);
        $this->assertDatabaseHas('country_names', [
            'country_id' => $countryId,
            'language' => 'AR',
            'name' => 'الامارات',
        ]);

        $optionResponse = $this->postJson('/api/search/options', [
            'pages' => [
                'GB' => ['name' => 'Featured'],
            ],
        ]);

        $optionId = $optionResponse->json('id');

        $optionResponse->assertOk();
        $this->assertDatabaseHas('search_options_pages', [
            'search_option_id' => $optionId,
            'language' => 'GB',
            'name' => 'Featured',
        ]);
        $this->assertDatabaseMissing('search_options_pages', [
            'search_option_id' => $optionId,
            'language' => 'AR',
        ]);

        $option = SearchOptions::with('pages')->findOrFail($optionId);
        $this->assertSame('Featured', $option->page->name);
    }
}
