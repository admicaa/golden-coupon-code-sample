<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ArticlePages;
use App\Models\CountryNames;
use App\Models\CouponPages;
use App\Models\Link;
use App\Models\Section;
use App\Models\StorePage;

class MainPageController extends Controller
{
    public function index()
    {
        return Section::where('page_id', 1)->orderBy('sort')->frontFormula()->get();
    }

    public function header()
    {
        return Link::whereNull('link_id')->frontFormula()->get();
    }

    public function country($slug)
    {
        $name = CountryNames::where('header_name', $slug)->firstOrFail();
        $country = $name->country;

        if ($name->language !== language() && $country->names()->where('language', language())->exists()) {
            abortJson(['country_id' => $country->countryName()->header_name], 409);
        }

        return $country->frontFormula()->sectionsFormula()->find($country->id);
    }

    public function store($slug)
    {
        $page = StorePage::where('slug', $slug)->firstOrFail();
        $store = $page->store;

        if ($page->language !== language() && $store->pages()->where('language', language())->exists()) {
            abortJson(['slug' => $store->page['slug']], 409);
        }

        return $store->frontFormula()->sectionsFormula()->find($store->id);
    }

    public function coupon($slug)
    {
        $page = CouponPages::where('slug', $slug)->firstOrFail();
        $coupon = $page->coupon;

        if ($page->language !== language() && $coupon->pages()->where('language', language())->exists()) {
            abortJson(['slug' => $coupon->page['slug']], 409);
        }

        return $coupon->frontFormula()->find($coupon->id);
    }

    public function article($slug)
    {
        $page = ArticlePages::where('slug', $slug)->where('language', language())->first();
        if (!$page) {
            $page = ArticlePages::where('slug', $slug)->firstOrFail();
        }

        $article = $page->article;
        if ($page->language !== language() && $article->pages()->where('language', language())->exists()) {
            abortJson(['slug' => $article->page['slug']], 409);
        }

        return $article->frontFormula()->sectionsFormula()->find($article->id);
    }
}
