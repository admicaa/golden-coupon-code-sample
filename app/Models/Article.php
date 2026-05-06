<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;

class Article extends Model
{
    //
    use ResolvesLocalizedRelations;

    protected $appends = ['page'];

    public function pages()
    {
        return $this->hasMany(ArticlePages::class, 'article_id', 'id');
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
        if (request()->body) {
            return ['title', 'name', 'metatags', 'slug', 'description'];
        }

        return ['title', 'name', 'slug'];
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
            'pages' => function ($query) {
                return $query->frontFormula()->where('language', language());
            },
        ]);
    }

    public function scopeAdminFormula($query)
    {

        return $query->with(['pages' => function ($query) {
            return $query->AdminFormula();
        }, 'images']);
    }
}
