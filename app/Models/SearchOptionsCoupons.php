<?php

namespace App\Models;

/**
 * Pivot row linking a search option to a coupon (or store).
 */
class SearchOptionsCoupons extends Model
{
    protected $table = 'search_options_coupons';

    protected $primaryKey = 'search_option_id';

    public $timestamps = false;
}
