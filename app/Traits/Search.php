<?php

namespace App\Traits;

use App\Events\SearchableChange;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Search
{
    public static function boot()
    {
        self::saved(function ($model) {
            event(new SearchableChange($model));
        });

        parent::boot();
    }
}
