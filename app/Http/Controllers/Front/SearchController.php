<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CountryNames;
use App\Models\SearchOptionsPages;
use App\Services\Search\SearchFacetService;
use App\Services\Search\SearchQueryService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(
        Request $request,
        SearchQueryService $searchQueryService,
        SearchFacetService $searchFacetService
    )
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

        $results = $searchQueryService
            ->applyResultRelations(
                $searchQueryService->build($data['q'] ?? null, $countryIds, $filterIds, $storeOnly, $couponOnly)
            )
            ->paginate(10)
            ->appends($request->query());

        $facets = $request->filled('page')
            ? null
            : $searchFacetService->build($data['q'] ?? null, $countryIds, $filterIds, $storeOnly, $couponOnly);

        return ['results' => $results, 'facets' => $facets];
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
}
