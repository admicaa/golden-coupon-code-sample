<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;

/**
 * Search filter option (e.g. category / tag) that can be attached to coupons.
 *
 * Class name is kept plural for backwards compatibility with the original
 * namespace (`App\SearchOptions`). A future cleanup may rename it to
 * `SearchOption`; the table name is pinned via `$table` so a rename would
 * not silently change which table Eloquent looks at.
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
        return $this->mainPage();
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
