<?php

namespace App;

use App\Models\Model;

class SearchOptionsPages extends Model
{
    //
    public function option()
    {
        return $this->belongsTo(SearchOptions::class, 'search_option_id', 'id');
    }
}
