<?php

namespace App\Services\Search;

use App\Models\Country;
use App\Models\SearchOptions;
use Illuminate\Support\Facades\DB;

class SearchFacetService
{
    protected $searchQueryService;

    public function __construct(SearchQueryService $searchQueryService)
    {
        $this->searchQueryService = $searchQueryService;
    }

    public function build($term, array $countries, array $filters, $storeOnly, $couponOnly)
    {
        $baseQuery = $this->searchQueryService->buildMatchedPairsQuery(
            $term,
            $countries,
            $filters,
            $storeOnly,
            $couponOnly
        );

        return [
            $this->buildTypeFacet($baseQuery),
            ['name' => 'countries', 'values' => $this->buildCountriesFacet($term, $filters, $storeOnly, $couponOnly)],
            ['name' => 'filters', 'values' => $this->buildFiltersFacet($term, $countries, $storeOnly, $couponOnly)],
        ];
    }

    public function buildTypeFacet($baseQuery)
    {
        $counts = DB::query()
            ->fromSub(clone $baseQuery, 'matched_pairs')
            ->selectRaw('COUNT(DISTINCT store_id) AS stores_count')
            ->selectRaw('COUNT(DISTINCT coupon_id) AS coupons_count')
            ->first();

        return [
            'name' => 'types',
            'values' => [
                ['name' => 'stores', 'count' => (int) $counts->stores_count, 'trans' => 'stores'],
                ['name' => 'coupons', 'count' => (int) $counts->coupons_count, 'trans' => 'coupons'],
            ],
        ];
    }

    public function buildCountriesFacet($term, array $filters, $storeOnly, $couponOnly)
    {
        $counts = DB::query()
            ->fromSub(
                $this->searchQueryService->buildMatchedPairsQuery($term, [], $filters, $storeOnly, $couponOnly),
                'matched_pairs'
            )
            ->leftJoin('stores as direct_stores', 'direct_stores.id', '=', 'matched_pairs.store_id')
            ->leftJoin('coupons', 'coupons.id', '=', 'matched_pairs.coupon_id')
            ->leftJoin('stores as coupon_stores', 'coupon_stores.id', '=', 'coupons.store_id')
            ->selectRaw('COALESCE(direct_stores.country_id, coupon_stores.country_id) AS country_id')
            ->selectRaw($this->distinctPairCountExpression() . ' AS pair_count')
            ->groupBy('country_id')
            ->get()
            ->pluck('pair_count', 'country_id');

        return Country::query()
            ->frontFormula()
            ->get(['id', 'iso'])
            ->map(function ($country) use ($counts) {
                return [
                    'name' => $country->name,
                    'iso' => $country->iso,
                    'count' => (int) $counts->get($country->id, 0),
                ];
            })
            ->values()
            ->all();
    }

    public function buildFiltersFacet($term, array $countries, $storeOnly, $couponOnly)
    {
        $counts = DB::query()
            ->fromSub(
                $this->searchQueryService->buildMatchedPairsQuery($term, $countries, [], $storeOnly, $couponOnly),
                'matched_pairs'
            )
            ->join('search_options_coupons', function ($join) {
                $join->on('matched_pairs.coupon_id', '=', 'search_options_coupons.coupon_id')
                    ->orOn('matched_pairs.store_id', '=', 'search_options_coupons.store_id');
            })
            ->whereNotNull('search_options_coupons.search_option_id')
            ->selectRaw('search_options_coupons.search_option_id AS search_option_id')
            ->selectRaw($this->distinctPairCountExpression() . ' AS pair_count')
            ->groupBy('search_options_coupons.search_option_id')
            ->get()
            ->pluck('pair_count', 'search_option_id');

        return SearchOptions::with(['pages' => function ($query) {
            $query->whereIn('language', language_fallbacks());
        }])->get()
            ->map(function ($option) use ($counts) {
                return [
                    'id' => $option->id,
                    'name' => optional($option->page)->name,
                    'count' => (int) $counts->get($option->id, 0),
                ];
            })
            ->values()
            ->all();
    }

    public function distinctPairCountExpression($storeColumn = 'matched_pairs.store_id', $couponColumn = 'matched_pairs.coupon_id')
    {
        return 'COUNT(DISTINCT CONCAT(COALESCE(' . $storeColumn . ', 0), \':\', COALESCE(' . $couponColumn . ', 0)))';
    }
}
