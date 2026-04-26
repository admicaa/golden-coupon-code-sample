<?php

namespace App\Models;

class StorePageMetaTag extends Model
{
    public function storePage()
    {
        return $this->belongsTo(StorePage::class, 'page_id', 'id');
    }

    public function couponPage()
    {
        return $this->belongsTo(CouponPages::class, 'coupon_page_id', 'id');
    }

    public function articlePage()
    {
        return $this->belongsTo(ArticlePages::class, 'article_id', 'id');
    }

    public function countryName()
    {
        return $this->belongsTo(CountryNames::class, 'country_name_id', 'id');
    }
}
