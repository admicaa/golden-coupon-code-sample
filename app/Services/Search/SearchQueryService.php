<?php

namespace App\Services\Search;

use App\Models\Search;
use Illuminate\Database\Eloquent\Builder;

class SearchQueryService
{
    public function build($term, array $countries, array $filters, $storeOnly, $couponOnly, $count = false)
    {
        if ($count) {
            return $this->buildMatchedPairsQuery($term, $countries, $filters, $storeOnly, $couponOnly);
        }

        return Search::query()->search($term, $countries, false, $filters, $storeOnly, $couponOnly);
    }

    public function buildMatchedPairsQuery($term, array $countries, array $filters, $storeOnly, $couponOnly)
    {
        return Search::query()
            ->search($term, $countries, true, $filters, $storeOnly, $couponOnly)
            ->select('searches.store_id', 'searches.coupon_id');
    }

    public function applyResultRelations(Builder $query)
    {
        return $query->with([
            'store' => function ($query) {
                $query->frontFormula();
            },
            'coupon' => function ($query) {
                $query->frontFormula();
            },
        ]);
    }
}
