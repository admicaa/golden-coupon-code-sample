<?php

namespace App\Models;

use App\Models\CountryNames;

class Country extends Model
{
    //
    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = ['name', 'header_name', 'metatags'];

    public function __construct()
    {
        if (request()->hide_tour_page_description) {
            $this->setAppends(['name', 'header_name']);
        }
    }

    public function names()
    {
        return $this->hasMany(CountryNames::class, 'country_id', 'id');
    }

    public function countryName()
    {
        return $this->names()->where('language', language())->firstOrFail();
    }

    public function getmetatagsAttribute()
    {
        return $this->countryName()->metatags;
    }

    public function getHeaderNameAttribute()
    {
        return $this->countryName()->header_name;
    }

    public function getNameAttribute()
    {
        return $this->countryName()->name;
    }
    // stores
    public function scopeSectionsFormula($query)
    {
        return $query->with(['sections' => function ($query) {
            return $query->frontFormula();
        }]);
    }

    public function scopeFrontFormula($query)
    {

        return $query;
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'country_id', 'id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'country_id', 'id');
    }
}
