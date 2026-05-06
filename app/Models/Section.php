<?php

namespace App\Models;

use App\Models\Concerns\ResolvesLocalizedRelations;

class Section extends Model
{
    use ResolvesLocalizedRelations;

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
        return $this->localizedRelationOrNull('pages') ?: $this->localizedRelation('pages');
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
            'pages' => function ($query) {
                return $query->frontFormula();
            },
        ]);
    }
}
