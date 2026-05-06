<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;

class Article extends Model
{
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
        $page = $this->localizedRelationOrNull('pages') ?: $this->mainPage();

        return $page ? $page->only($this->pageColumns()) : [];
    }

    protected function pageColumns()
    {
        if (should_include_page_body()) {
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
                return $query->frontFormula()->whereIn('language', language_fallbacks());
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
