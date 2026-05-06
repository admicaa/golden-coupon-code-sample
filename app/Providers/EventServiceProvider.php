<?php

namespace App\Providers;

use App\Events\SearchableChange;
use App\Listeners\SearchableChange as ListenersSearchableChange;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /** @var array<class-string, array<int, class-string>> */
    protected $listen = [
        SearchableChange::class => [
            ListenersSearchableChange::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
