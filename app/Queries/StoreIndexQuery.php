<?php

namespace App\Queries;

use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class StoreIndexQuery
{
    /**
     * Build the admin Stores index listing from a request.
     *
     * Filters: country_id, search (matches store_pages.name | title).
     * The optional `country=1` flag eager-loads the country relation.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        return Store::query()
            ->when($request->filled('country_id'), fn ($q) => $q->where('country_id', $request->input('country_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = (string) $request->input('search');
                $q->whereHas('pages', function ($pages) use ($term) {
                    $pages->where('name', 'like', '%' . $term . '%')
                        ->orWhere('title', 'like', '%' . $term . '%');
                });
            })
            ->when($request->boolean('country'), fn ($q) => $q->with('country'))
            ->adminFormula()
            ->paginate(per_page($request->input('itemsPerPage')));
    }
}
