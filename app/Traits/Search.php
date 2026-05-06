<?php

namespace App\Traits;

use App\Events\SearchableChange;

trait Search
{
    public static function bootSearch()
    {
        static::saved(function ($model) {
            event(new SearchableChange($model));
        });
    }
}
