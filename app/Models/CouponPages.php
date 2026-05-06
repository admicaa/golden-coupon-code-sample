<?php

namespace App\Models;

use App\Traits\Search;

class CouponPages extends Model
{
    //
    use Search;
    public $search_column_id = 'coupon_id';

    public function getSearchColumnAttribute()
    {
        return [
            $this->search_column_id => $this->coupon->id,
            'language' => $this->language
        ];
    }

    public function getSearchUpdateAttribute()
    {
        return [
            'stage_1' => $this->title,
            'stage_2' => $this->description,
            'stage_3' => $this->coupon->coupon_key . ' ' . $this->coupon->store->country->name,
            'stage_4' => $this->coupon->redirect_url . ' - ' . $this->coupon->percentage . ' %',
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

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    public function metatags()
    {
        return $this->hasMany(StorePageMetaTag::class, 'coupon_page_id', 'id');
    }

    public function scopeAdminFormula($query)
    {
        return $query->with('metatags');
    }
    
    public function scopeFrontFormula($query)
    {
        return $query->with('metatags');
    }
}
