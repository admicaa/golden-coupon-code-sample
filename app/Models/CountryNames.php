<?php

namespace App\Models;


class CountryNames extends Model
{
    protected $with = ['metatags'];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
    
    public function metatags()
    {
        return $this->hasMany(StorePageMetaTag::class, 'country_name_id', 'id');
    }
}
