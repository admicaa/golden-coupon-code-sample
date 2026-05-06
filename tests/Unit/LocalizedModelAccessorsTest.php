<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\CountryNames;
use App\Models\SearchOptions;
use App\Models\SearchOptionsPages;
use App\Models\Section;
use App\Models\SectionPages;
use App\Models\Store;
use App\Models\StorePage;
use App\Models\StorePageMetaTag;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

class LocalizedModelAccessorsTest extends TestCase
{
    protected function tearDown(): void
    {
        request()->replace([]);

        parent::tearDown();
    }

    public function test_store_page_accessor_uses_loaded_pages_relation()
    {
        $store = new Store(['id' => 1]);
        $store->setRelation('pages', new EloquentCollection([
            new StorePage([
                'language' => 'GB',
                'title' => 'Store title',
                'name' => 'Store name',
                'slug' => 'store-slug',
                'body' => 'Store body',
            ]),
        ]));

        $this->assertSame([
            'title' => 'Store title',
            'name' => 'Store name',
            'slug' => 'store-slug',
        ], $store->page);
    }

    public function test_section_page_accessor_uses_loaded_pages_relation()
    {
        $page = new SectionPages([
            'language' => 'GB',
            'title' => 'Section title',
            'subtitle' => 'Section subtitle',
            'description' => 'Section description',
        ]);

        $section = new Section(['id' => 1]);
        $section->setRelation('pages', new EloquentCollection([$page]));

        $this->assertSame('Section title', $section->page->title);
        $this->assertSame('Section subtitle', $section->page->subtitle);
    }

    public function test_search_option_page_accessor_uses_loaded_pages_relation()
    {
        $page = new SearchOptionsPages([
            'language' => 'GB',
            'name' => 'Featured',
        ]);

        $option = new SearchOptions(['id' => 1]);
        $option->setRelation('pages', new EloquentCollection([$page]));

        $this->assertSame('Featured', $option->page->name);
    }

    public function test_country_serialization_uses_loaded_names_relation()
    {
        $name = new CountryNames([
            'language' => 'GB',
            'name' => 'Egypt',
            'header_name' => 'egypt',
        ]);
        $name->setRelation('metatags', new EloquentCollection([
            new StorePageMetaTag([
                'name' => 'description',
                'value' => 'Country description',
            ]),
        ]));

        $country = new Country(['id' => 1, 'iso' => 'EG']);
        $country->setRelation('names', new EloquentCollection([$name]));

        $payload = $country->toArray();

        $this->assertSame('Egypt', $payload['name']);
        $this->assertSame('egypt', $payload['header_name']);
        $this->assertSame('Country description', $payload['metatags'][0]['value']);

        request()->merge(['hide_tour_page_description' => true]);

        $hiddenPayload = $country->toArray();

        $this->assertArrayHasKey('name', $hiddenPayload);
        $this->assertArrayHasKey('header_name', $hiddenPayload);
        $this->assertArrayNotHasKey('metatags', $hiddenPayload);
    }
}
