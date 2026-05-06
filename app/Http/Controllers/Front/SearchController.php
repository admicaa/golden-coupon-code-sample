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
    /**
     * Public search endpoint.
     *
     * Returns paginated results plus three facets (types, countries, filters)
     * on the first page only — subsequent pages skip facet computation since
     * the values are stable across the result set.
     */
    public function index(
        Request $request,
        SearchQueryService $searchQueryService,
        SearchFacetService $searchFacetService,
    ): array {
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
        $term = $data['q'] ?? null;

        // The public results don't carry the long body/description text.
        $request->merge(['hide_tour_page_description' => true]);

        $results = $searchQueryService
            ->applyResultRelations(
                $searchQueryService->build($term, $countryIds, $filterIds, $storeOnly, $couponOnly)
            )
            ->paginate(10)
            ->appends($request->query());

        $facets = $request->filled('page')
            ? null
            : $searchFacetService->build($term, $countryIds, $filterIds, $storeOnly, $couponOnly);

        return ['results' => $results, 'facets' => $facets];
    }

    /**
     * Resolve user-facing country names to country ids.
     */
    protected function resolveCountries(array $countryNames): array
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

    /**
     * Resolve user-facing filter names to search-option ids.
     */
    protected function resolveFilters(array $filterNames): array
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
