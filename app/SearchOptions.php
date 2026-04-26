<?php

namespace App;

use App\Models\Model;

class SearchOptions extends Model
{
    //
    protected $appends = ['page'];
    public function pages()
    {
        return $this->hasMany(SearchOptionsPages::class, 'search_option_id', 'id');
    }

    public function getPageAttribute()
    {
        return $this->mainPage();
    }

    public function mainPage()
    {
        return $this->pages()->where('language', language())->firstOrFail();
    }

    public function scopeAdminFormula($query)
    {
        return $query->with('pages');
    }
}
