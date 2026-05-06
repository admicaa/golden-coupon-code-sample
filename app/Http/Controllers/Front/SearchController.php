<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryNames;
use App\Models\Search;
use App\SearchOptions;
use App\SearchOptionsPages;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->validate($request, [
            'q' => 'nullable|string|max:191',
            'countries' => 'array|max:30',
            'countries.*' => 'string|max:191',
            'types' => 'array|max:2',
            'types.*' => 'in:stores,coupons',
            'filters' => 'array|max:50',
            'filters.*' => 'string|max:191',
        ]);

        $countryIds = $this->resolveCountries($data['countries'] ?? []);
        $filterIds = $this->resolveFilters($data['filters'] ?? []);
        $types = $data['types'] ?? [];
        $storeOnly = in_array('stores', $types, true);
        $couponOnly = in_array('coupons', $types, true);

        $request->merge(['hide_tour_page_description' => true]);

        $results = $this->searchQuery($request->input('q'), $countryIds, $filterIds, $storeOnly, $couponOnly)
            ->with([
                'store' => function ($query) {
                    $query->frontFormula();
                },
                'coupon' => function ($query) {
                    $query->frontFormula();
                },
            ])
            ->paginate(10)
            ->appends($request->query());

        $facets = $request->filled('page')
            ? null
            : $this->buildFacets($request->input('q'), $countryIds, $filterIds, $storeOnly, $couponOnly);

        return ['results' => $results, 'facets' => $facets];
    }

    protected function searchQuery($term, array $countries, array $filters, $storeOnly, $couponOnly, $count = false)
    {
        return Search::query()->search($term, $countries, $count, $filters, $storeOnly, $couponOnly);
    }

    protected function resolveCountries(array $countryNames)
    {
        if (empty($countryNames)) {
            return [];
        }

        return CountryNames::whereIn('name', $countryNames)
            ->pluck('country_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveFilters(array $filterNames)
    {
        if (empty($filterNames)) {
            return [];
        }

        return SearchOptionsPages::whereIn('name', $filterNames)
            ->pluck('search_option_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function buildFacets($term, array $countries, array $filters, $storeOnly, $couponOnly)
    {
        $base = $this->searchQuery($term, $countries, $filters, $storeOnly, $couponOnly, true);
        $rows = (clone $base)
            ->getQuery()
            ->select(['store_id', 'coupon_id'])
            ->get();

        $couponsCount = $rows->whereNotNull('coupon_id')->unique('coupon_id')->count();
        $storesCount = $rows->whereNotNull('store_id')->unique('store_id')->count();

        $countriesFacet = $this->buildCountriesFacet($term, $filters, $storeOnly, $couponOnly);
        $filtersFacet = $this->buildFiltersFacet($term, $countries, $storeOnly, $couponOnly);

        return [
            [
                'name' => 'types',
                'values' => [
                    ['name' => 'stores', 'count' => $storesCount, 'trans' => 'stores'],
                    ['name' => 'coupons', 'count' => $couponsCount, 'trans' => 'coupons'],
                ],
            ],
            ['name' => 'countries', 'values' => $countriesFacet],
            ['name' => 'filters', 'values' => $filtersFacet],
        ];
    }

    protected function buildCountriesFacet($term, array $filters, $storeOnly, $couponOnly)
    {
        $countries = Country::query()->frontFormula()->get(['id', 'iso']);
        $output = [];

        foreach ($countries as $country) {
            $rows = $this->searchQuery($term, [$country->id], $filters, $storeOnly, $couponOnly, true)
                ->getQuery()
                ->select(['store_id', 'coupon_id'])
                ->get();

            $output[] = [
                'name' => $country->name,
                'iso' => $country->iso,
                'count' => $rows->unique(function ($row) {
                    return ($row->store_id ?? 'n') . ':' . ($row->coupon_id ?? 'n');
                })->count(),
            ];
        }

        return $output;
    }

    protected function buildFiltersFacet($term, array $countries, $storeOnly, $couponOnly)
    {
        $options = SearchOptions::with(['pages' => function ($query) {
            return $query->where('language', language());
        }])->get();
        $output = [];

        foreach ($options as $option) {
            $rows = $this->searchQuery($term, $countries, [$option->id], $storeOnly, $couponOnly, true)
                ->getQuery()
                ->select(['store_id', 'coupon_id'])
                ->get();

            $output[] = [
                'id' => $option->id,
                'name' => optional($option->page)->name,
                'count' => $rows->unique(function ($row) {
                    return ($row->store_id ?? 'n') . ':' . ($row->coupon_id ?? 'n');
                })->count(),
            ];
        }

        return $output;
    }
}
