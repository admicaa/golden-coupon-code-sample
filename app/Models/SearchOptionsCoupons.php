<?php

namespace App\Models;

/**
 * Pivot row linking a search option to a coupon.
 *
 * The actual `belongsToMany` relationship on `App\Models\Coupon::options()`
 * still wires the pivot by table name (`search_options_coupons`), so this
 * model exists primarily for direct queries against the pivot table. Class
 * name kept plural for backwards compatibility with the original namespace.
 */
class SearchOptionsCoupons extends Model
{
    protected $table = 'search_options_coupons';

    protected $primaryKey = 'search_option_id';

    public $timestamps = false;
}
