<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;

/**
 * Search filter option (e.g. category / tag) that can be attached to coupons.
 */
class SearchOptions extends Model
{
    use ResolvesLocalizedRelations;

    protected $table = 'search_options';

    protected $appends = ['page'];

    public function pages()
    {
        return $this->hasMany(SearchOptionsPages::class, 'search_option_id', 'id');
    }

    public function getPageAttribute()
    {
        return $this->localizedRelationOrNull('pages') ?: $this->mainPage();
    }

    public function mainPage()
    {
        return $this->localizedRelation('pages');
    }

    public function scopeAdminFormula($query)
    {
        return $query->with('pages');
    }
}
