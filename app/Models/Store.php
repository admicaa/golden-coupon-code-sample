<?php

namespace App\Models;

use App\SearchOptions;
use App\Traits\Search;

class Store extends Model
{
    //
    use Search;
    protected $appends = ['page'];

    public function getSearchColumnAttribute()
    {

        return [
            'store_id' => $this->id
        ];
    }

    public function getSearchUpdateAttribute()
    {
        return [
            'stage_3' => $this->country->name,
            'stage_4' => $this->slug
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
            'country'
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
        return $this->pages()->where('language', language())->firstOrFail();
    }

    public function getPageAttribute()
    {
        $onlyArray = ['title', 'name', 'slug'];
        if (request()->body) {
            $onlyArray = ['title', 'name', 'metatags', 'slug', 'body'];
        }
        return $this->mainPage()->only($onlyArray);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'store_id', 'id');
    }
}
