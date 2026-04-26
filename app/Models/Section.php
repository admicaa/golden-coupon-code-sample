<?php

namespace App\Models;


class Section extends Model
{
    //
    protected $appends = ['page'];
    protected $with = ['contents'];
    public function contents()
    {
        return $this->hasMany(SectionContents::class, 'section_id', 'id');
    }

    public function pages()
    {
        return $this->hasMany(SectionPages::class, 'section_id', 'id');
    }

    public function getPageAttribute()
    {
        return $this->pages()->where('language', language())->firstOrFail();
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function image()
    {
        return $this->belongsTo(StoreImages::class, 'image_id', 'id');
    }

    public function scopeAdminFormula($query)
    {
        return $query->with([
            'pages',
            'contents'
        ])->orderBy('sort', 'ASC');
    }

    public function scopeFrontFormula($query)
    {
        return $query->orderBy('sort', 'ASC')->with([

            'contents' => function ($query) {
                return $query->frontFormula();
            },

        ]);
    }
}
