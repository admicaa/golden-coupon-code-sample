<?php

namespace App\Models;

/**
 * Per-language localization rows for a SearchOptions filter.
 */
class SearchOptionsPages extends Model
{
    protected $table = 'search_options_pages';

    public function option()
    {
        return $this->belongsTo(SearchOptions::class, 'search_option_id', 'id');
    }
}
