<?php

namespace Tests\Unit;

use App\Events\SearchableChange;
use App\Listeners\SearchableChange as SearchableChangeListener;
use App\Models\Country;
use App\Models\Languages;
use App\Models\Store;
use App\Models\StorePage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\Concerns\RefreshMySqlDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SearchableIndexingTest extends TestCase
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

    public function test_searchable_models_dispatch_the_searchable_change_event_on_save()
    {
        Event::fake([SearchableChange::class]);

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
            'slug' => 'event-store',
            'name' => 'Event Store',
            'title' => 'Event Store',
            'body' => 'Body',
        ]);

        Event::assertDispatched(SearchableChange::class, function ($event) use ($page) {
            return $event->searchable->is($page);
        });
    }

    public function test_searchable_change_listener_is_queued()
    {
        $this->assertContains(ShouldQueue::class, class_implements(SearchableChangeListener::class));
    }
}
