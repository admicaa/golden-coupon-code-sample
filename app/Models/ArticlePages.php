<?php

namespace App\Models;


class ArticlePages extends Model
{
    //
    public function metatags()
    {
        return $this->hasMany(StorePageMetaTag::class, 'article_id', 'id');
    }

    public function scopeAdminFormula($query)
    {
        return $query->with('metatags');
    }

    public function scopeFrontFormula($query)
    {
        return $query->with('metatags');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }
}
