<?php

namespace App\Models;


class Link extends Model
{
    //
    public function links()
    {
        return $this->hasMany(Link::class, 'link_id', 'id');
    }

    public function scopeAdminFormula($query)
    {
        return $query->with(['links' => function ($query) {
            return $query->adminFormula();
        }]);
    }

    public function scopeFrontFormula($query)
    {
        return $query->with(['links' => function ($query) {
            return $query->frontFormula();
        }]);
    }
}
