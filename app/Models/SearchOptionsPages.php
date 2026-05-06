<?php

namespace App\Models;

/**
 * Per-language localization rows for a SearchOptions filter.
 *
 * Class name is kept plural for backwards compatibility with the original
 * namespace (`App\SearchOptionsPages`); table is pinned explicitly.
 */
class SearchOptionsPages extends Model
{
    protected $table = 'search_options_pages';

    public function option()
    {
        return $this->belongsTo(SearchOptions::class, 'search_option_id', 'id');
    }
}
