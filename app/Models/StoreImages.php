<?php

namespace App\Models;


class StoreImages extends Model
{
    //
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
