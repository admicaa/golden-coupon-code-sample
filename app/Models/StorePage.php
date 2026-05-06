<?php

namespace App\Models;

use App\Traits\Search;

class StorePage extends Model
{
    use Search;
    public $search_column_id = 'store_id';
    public function getSearchColumnAttribute()
    {
        return [
            $this->search_column_id => $this->store->id,
            'language' => $this->language
        ];
    }

    public function getSearchUpdateAttribute()
    {
        return [
            'stage_1' => $this->title . ' ' . $this->name,
            'stage_2' => $this->body,
            'stage_3' => $this->store->country->name,
            'stage_4' => $this->store->slug,
            'stage_5' => $this->getMetatagsString()
        ];
    }

    public function getMetatagsString()
    {
        $tags = $this->metatags;
        $tagsText = $tags->reduce(function ($carry, $item) {
            return $carry . $item->value . ' ';
        });
        return $tagsText;
    }

    public function metatags()
    {
        return $this->hasMany(StorePageMetaTag::class, 'page_id', 'id');
    }

    public function scopeAdminFormula($query)
    {
        return $query->with('metatags');
    }

    public function scopeFrontFormula($query)
    {
        return $query->with('metatags');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
