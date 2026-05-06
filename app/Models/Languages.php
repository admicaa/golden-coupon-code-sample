<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class Languages extends Model
{
    protected static function boot()
    {
        parent::boot();

        $flush = function () {
            Cache::forget('languages.all');
        };

        static::saved($flush);
        static::deleted($flush);
    }

}
