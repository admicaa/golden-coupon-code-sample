<?php

namespace App\Models;


class Article extends Model
{
    //
    protected $appends = ['page'];

    public function pages()
    {
        return $this->hasMany(ArticlePages::class, 'article_id', 'id');
    }

    public function mainPage()
    {
        return $this->pages()->where('language', language())->firstOrFail();
    }

    public function getPageAttribute()
    {
        $onlyArray = ['title', 'name', 'slug'];
        if (request()->body) {
            $onlyArray = ['title', 'name', 'metatags', 'slug', 'description'];
        }
        return $this->mainPage()->only($onlyArray);
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'article_id', 'id');
    }

    public function scopeSectionsFormula($query)
    {
        return $query->with(['sections' => function ($query) {
            return $query->frontFormula();
        }]);
    }

    public function image()
    {
        return $this->hasOne(StoreImages::class, 'article_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(StoreImages::class, 'article_id', 'id');
    }

    public function scopeFrontFormula($query)
    {
        return $query->with([


            'image',
        ]);
    }

    public function scopeAdminFormula($query)
    {

        return $query->with(['pages' => function ($query) {
            return $query->AdminFormula();
        }, 'images']);
    }
}
