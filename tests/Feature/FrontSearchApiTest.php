<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\Languages;
use App\Models\SearchOptions;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FrontSearchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);

        $this->createSearchFixtures();
    }

    public function test_search_results_paginate_and_return_expected_facet_shape()
    {
        $response = $this->getJson('/api/front/search');

        $response->assertOk()->assertJsonStructure([
            'results' => ['current_page', 'data', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
            'facets',
        ]);

        $this->assertSame(4, $response->json('results.total'));
        $this->assertCount(4, $response->json('results.data'));
        $this->assertCount(3, $response->json('facets'));

        $types = $this->facetValuesByKey($response, 'types', 'name');
        $countries = $this->facetValuesByKey($response, 'countries', 'name');
        $filters = $this->facetValuesByKey($response, 'filters', 'name');

        $this->assertSame(2, $types['stores']['count']);
        $this->assertSame(2, $types['coupons']['count']);
        $this->assertSame(2, $countries['Egypt']['count']);
        $this->assertSame(2, $countries['United Arab Emirates']['count']);
        $this->assertSame(2, $filters['Featured']['count']);
        $this->assertSame(2, $filters['Online']['count']);
        $this->assertSame(0, $filters['Weekend']['count']);
    }

    public function test_country_selection_affects_results_and_filter_facet_but_not_country_facet()
    {
        $response = $this->getJson('/api/front/search?countries[]=Egypt');

        $response->assertOk();

        $types = $this->facetValuesByKey($response, 'types', 'name');
        $countries = $this->facetValuesByKey($response, 'countries', 'name');
        $filters = $this->facetValuesByKey($response, 'filters', 'name');

        $this->assertSame(2, $response->json('results.total'));
        $this->assertSame(1, $types['stores']['count']);
        $this->assertSame(1, $types['coupons']['count']);
        $this->assertSame(2, $countries['Egypt']['count']);
        $this->assertSame(2, $countries['United Arab Emirates']['count']);
        $this->assertSame(2, $filters['Featured']['count']);
        $this->assertSame(0, $filters['Online']['count']);
    }

    public function test_filter_selection_affects_results_and_country_facet_but_not_filter_facet()
    {
        $response = $this->getJson('/api/front/search?filters[]=Featured');

        $response->assertOk();

        $types = $this->facetValuesByKey($response, 'types', 'name');
        $countries = $this->facetValuesByKey($response, 'countries', 'name');
        $filters = $this->facetValuesByKey($response, 'filters', 'name');

        $this->assertSame(2, $response->json('results.total'));
        $this->assertSame(1, $types['stores']['count']);
        $this->assertSame(1, $types['coupons']['count']);
        $this->assertSame(2, $countries['Egypt']['count']);
        $this->assertSame(0, $countries['United Arab Emirates']['count']);
        $this->assertSame(2, $filters['Featured']['count']);
        $this->assertSame(2, $filters['Online']['count']);
        $this->assertSame(0, $filters['Weekend']['count']);
    }

    public function test_search_executes_a_constant_number_of_search_queries()
    {
        DB::connection()->flushQueryLog();
        DB::connection()->enableQueryLog();

        $response = $this->getJson('/api/front/search');

        $response->assertOk();

        $searchQueries = collect(DB::connection()->getQueryLog())->filter(function ($query) {
            return strpos($query['query'], 'searches') !== false;
        });

        $this->assertLessThanOrEqual(5, $searchQueries->count());
    }

    public function test_search_skips_facet_queries_when_page_is_present()
    {
        DB::connection()->flushQueryLog();
        DB::connection()->enableQueryLog();

        $response = $this->getJson('/api/front/search?page=2');

        $response->assertOk()
            ->assertJson(['facets' => null]);

        $searchQueries = collect(DB::connection()->getQueryLog())->filter(function ($query) {
            return strpos($query['query'], 'searches') !== false;
        });

        $this->assertLessThanOrEqual(2, $searchQueries->count());
    }

    protected function createSearchFixtures()
    {
        $egypt = Country::create(['iso' => 'EG']);
        $egypt->names()->create([
            'language' => 'GB',
            'name' => 'Egypt',
            'header_name' => 'egypt',
        ]);

        $uae = Country::create(['iso' => 'AE']);
        $uae->names()->create([
            'language' => 'GB',
            'name' => 'United Arab Emirates',
            'header_name' => 'united-arab-emirates',
        ]);

        $egyptStore = Store::create(['country_id' => $egypt->id]);
        $egyptStore->pages()->create([
            'language' => 'GB',
            'slug' => 'egypt-store',
            'name' => 'Egypt Store',
            'title' => 'Egypt Store',
            'body' => 'Egypt store body',
        ]);

        $uaeStore = Store::create(['country_id' => $uae->id]);
        $uaeStore->pages()->create([
            'language' => 'GB',
            'slug' => 'uae-store',
            'name' => 'UAE Store',
            'title' => 'UAE Store',
            'body' => 'UAE store body',
        ]);

        $egyptCoupon = Coupon::create([
            'store_id' => $egyptStore->id,
            'coupon_key' => 'EGYPT10',
            'redirect_url' => 'https://example.com/egypt',
            'percentage' => '10',
            'valid' => true,
        ]);
        $egyptCoupon->pages()->create([
            'language' => 'GB',
            'title' => 'Egypt Coupon',
            'slug' => 'egypt-coupon',
            'description' => 'Egypt coupon description',
        ]);

        $uaeCoupon = Coupon::create([
            'store_id' => $uaeStore->id,
            'coupon_key' => 'UAE15',
            'redirect_url' => 'https://example.com/uae',
            'percentage' => '15',
            'valid' => true,
        ]);
        $uaeCoupon->pages()->create([
            'language' => 'GB',
            'title' => 'UAE Coupon',
            'slug' => 'uae-coupon',
            'description' => 'UAE coupon description',
        ]);

        $featured = SearchOptions::create();
        $featured->pages()->create(['language' => 'GB', 'name' => 'Featured']);

        $online = SearchOptions::create();
        $online->pages()->create(['language' => 'GB', 'name' => 'Online']);

        $weekend = SearchOptions::create();
        $weekend->pages()->create(['language' => 'GB', 'name' => 'Weekend']);

        DB::table('search_options_coupons')->insert([
            ['search_option_id' => $featured->id, 'store_id' => $egyptStore->id],
            ['search_option_id' => $featured->id, 'store_id' => $egyptStore->id],
            ['search_option_id' => $featured->id, 'coupon_id' => $egyptCoupon->id],
            ['search_option_id' => $online->id, 'store_id' => $uaeStore->id],
            ['search_option_id' => $online->id, 'coupon_id' => $uaeCoupon->id],
        ]);

        return [
            'countries' => [
                'egypt' => $egypt,
                'uae' => $uae,
            ],
            'stores' => [
                'egypt' => $egyptStore,
                'uae' => $uaeStore,
            ],
            'coupons' => [
                'egypt' => $egyptCoupon,
                'uae' => $uaeCoupon,
            ],
            'filters' => [
                'featured' => $featured,
                'online' => $online,
                'weekend' => $weekend,
            ],
        ];
    }

    protected function facetValuesByKey($response, $facetName, $key)
    {
        $facet = collect($response->json('facets'))->firstWhere('name', $facetName);

        return collect($facet['values'])->keyBy($key)->all();
    }
}
