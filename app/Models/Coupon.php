<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;
use App\Traits\Search;

class Coupon extends Model
{
    //
    use Search;
    use ResolvesLocalizedRelations;

    protected $appends = ['page'];

    public function options()
    {
        return $this->belongsToMany(SearchOptions::class, 'search_options_coupons', 'coupon_id', 'search_option_id');
    }
    
    public function getSearchColumnAttribute()
    {
        return [
            'coupon_id' => $this->id
        ];
    }

    public function getSearchUpdateAttribute()
    {
        return [
            'stage_3' => $this->coupon_key . ' ' . $this->store->country->name,
            'stage_4' => $this->redirect_url . ' - ' . $this->percentage . ' %'
        ];
    }

    public function scopeAdminFormula($query)
    {
        return $query->with(['images', 'pages' => function ($query) {
            return $query->adminFormula();
        }, 'options']);
    }

    public function images()
    {
        return $this->hasMany(StoreImages::class, 'coupon_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function scopeFrontFormula($query)
    {
        return $query->with(['store' => function ($query) {
            return $query->frontFormula();
        }, 'pages' => function ($query) {
            return $query->frontFormula()->whereIn('language', language_fallbacks());
        }]);
    }

    public function mainPage()
    {
        return $this->localizedRelation('pages');
    }

    public function getPageAttribute()
    {
        return $this->mainPage()->only($this->pageColumns());
    }

    protected function pageColumns()
    {
        if (should_hide_tour_page_description()) {
            return ['title', 'name', 'slug'];
        }

        return ['title', 'name', 'metatags', 'slug', 'description'];
    }

    public function pages()
    {
        return $this->hasMany(CouponPages::class, 'coupon_id', 'id');
    }
}
