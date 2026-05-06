<?php

namespace App\Listeners;

use App\Events\SearchableChange as SearchableChangeEvent;
use App\Models\Search;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SearchableChange implements ShouldQueue
{
    use InteractsWithQueue;

    public $delay = 5;
    public $tries = 3;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  SearchableChange  $event
     * @return void
     */
    public function handle(SearchableChangeEvent $event)
    {
        $searchable = $event->searchable;
        $searchUpdate = $searchable->SearchUpdate;

        foreach ($searchUpdate as $key => $value) {
            $searchUpdate[$key] = html_entity_decode(strip_tags((string) $value));
        }

        Search::updateOrCreate($searchable->SearchColumn, $searchUpdate);
    }

    public function failed(SearchableChangeEvent $event, $exception)
    {
        report($exception);
    }
}
