<?php

namespace App\Models;


class SectionPages extends Model
{
    public function scopeFrontFormula($query)
    {
        return $query->where('language', language())->select([
            'language',
            'section_id',
            'title',
            'subtitle',
            'description',

        ]);
    }
}
