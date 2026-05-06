<?php

namespace App\Services\Search;

use App\Models\Search;
use Illuminate\Database\Eloquent\Builder;

class SearchQueryService
{
    /**
     * Build the base searches query.
     *
     * When `$count` is true we yield the "matched pairs" form (no select-list
     * relevance, no ORDER BY) suitable for wrapping in aggregation queries.
     */
    public function build(
        ?string $term,
        array $countries,
        array $filters,
        bool $storeOnly,
        bool $couponOnly,
        bool $count = false,
    ): Builder {
        if ($count) {
            return $this->buildMatchedPairsQuery($term, $countries, $filters, $storeOnly, $couponOnly);
        }

        return Search::query()->search($term, $countries, false, $filters, $storeOnly, $couponOnly);
    }

    /**
     * The (store_id, coupon_id) projection used as the foundation of every facet count.
     */
    public function buildMatchedPairsQuery(
        ?string $term,
        array $countries,
        array $filters,
        bool $storeOnly,
        bool $couponOnly,
    ): Builder {
        return Search::query()
            ->search($term, $countries, true, $filters, $storeOnly, $couponOnly)
            ->select('searches.store_id', 'searches.coupon_id');
    }

    /**
     * Eager-load the public-facing store/coupon payloads onto a search builder.
     */
    public function applyResultRelations(Builder $query): Builder
    {
        return $query->with([
            'store' => fn ($q) => $q->frontFormula(),
            'coupon' => fn ($q) => $q->frontFormula(),
        ]);
    }
}
