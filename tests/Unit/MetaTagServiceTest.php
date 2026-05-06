<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Languages;
use App\Models\Store;
use App\Models\StorePage;
use App\Services\Content\MetaTagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaTagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);
    }

    public function test_sync_creates_and_updates_metatags_for_a_page()
    {
        $country = Country::create(['iso' => 'EG']);
        $country->names()->create([
            'language' => 'GB',
            'name' => 'Egypt',
            'header_name' => 'egypt',
        ]);

        $store = Store::create(['country_id' => $country->id]);
        $page = StorePage::create([
            'store_id' => $store->id,
            'language' => 'GB',
            'slug' => 'meta-store',
            'name' => 'Meta Store',
            'title' => 'Meta Store',
            'body' => 'Meta Store Body',
        ]);

        $service = $this->app->make(MetaTagService::class);

        $created = $service->sync($page, [
            ['name' => 'description', 'value' => 'First description'],
            ['name' => 'og:title', 'value' => 'Store OG Title', 'type' => 2],
        ]);

        $this->assertCount(2, $created);
        $firstTag = $created->firstWhere('name', 'description');

        $updated = $service->sync($page, [
            ['id' => $firstTag->id, 'name' => 'description', 'value' => 'Updated description'],
            ['name' => 'robots', 'value' => 'index,follow'],
        ]);

        $this->assertCount(3, $updated);
        $this->assertDatabaseHas('store_page_meta_tags', [
            'page_id' => $page->id,
            'name' => 'description',
            'value' => 'Updated description',
            'type' => 1,
        ]);
        $this->assertDatabaseHas('store_page_meta_tags', [
            'page_id' => $page->id,
            'name' => 'robots',
            'value' => 'index,follow',
        ]);
    }
}
