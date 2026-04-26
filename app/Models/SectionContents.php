<?php

namespace App\Models;


class SectionContents extends Model
{
    //
    protected $with = ['image'];
    public function article()
    {
        return $this->belongsTo(Article::class, 'page_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function image()
    {
        return $this->belongsTo(StoreImages::class, 'image_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function scopeFrontFormula($query)
    {
        return $query->with([
            'image',
            'store' => function ($query) {
                return $query->FrontFormula();
            },
            'article' => function ($query) {
                return $query->FrontFormula();
            },
            'coupon' => function ($query) {
                return $query->frontFormula();
            },
            'country'
        ]);
    }
}
