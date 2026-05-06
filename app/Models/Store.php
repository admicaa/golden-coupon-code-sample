<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;
use App\Traits\Search;

class Store extends Model
{
    use Search;
    use ResolvesLocalizedRelations;
    protected $appends = ['page'];

    public function getSearchColumnAttribute()
    {

        return [
            'store_id' => $this->id
        ];
    }

    public function getSearchUpdateAttribute()
    {
        $countryName = optional($this->country)->name;

        return [
            'stage_3' => $countryName ?: '',
            'stage_4' => $this->slug ?: '',
        ];
    }

    public function options()
    {
        return $this->belongsToMany(SearchOptions::class, 'search_options_coupons', 'store_id', 'search_option_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function pages()
    {
        return $this->hasMany(StorePage::class, 'store_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(StoreImages::class, 'store_id', 'id');
    }

    public function image()
    {
        return $this->hasOne(StoreImages::class, 'store_id', 'id')->orderBy('is_logo', 'DESC');
    }

    public function scopeSectionsFormula($query)
    {
        return $query->with(['sections' => function ($query) {
            return $query->frontFormula();
        }]);
    }

    public function scopeFrontFormula($query)
    {
        return $query->with([
            'image',
            'country' => function ($query) {
                return $query->frontFormula();
            },
            'pages' => function ($query) {
                return $query->frontFormula()->whereIn('language', language_fallbacks());
            },
        ]);
    }

    public function scopeAdminFormula($query)
    {
        return $query->with(['pages' => function ($query) {
            return $query->AdminFormula();
        }, 'images', 'options']);
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'store_id', 'id');
    }

    public function mainPage()
    {
        return $this->localizedRelation('pages');
    }

    public function getPageAttribute()
    {
        $page = $this->localizedRelationOrNull('pages') ?: $this->mainPage();

        return $page ? $page->only($this->pageColumns()) : [];
    }

    protected function pageColumns()
    {
        if (should_include_page_body()) {
            return ['title', 'name', 'metatags', 'slug', 'body'];
        }

        return ['title', 'name', 'slug'];
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'store_id', 'id');
    }
}
