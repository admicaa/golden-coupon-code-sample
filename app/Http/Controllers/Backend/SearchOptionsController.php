<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\SearchOptionAssignRequest;
use App\Http\Requests\Backend\SearchOptionRequest;
use App\Models\Coupon;
use App\Models\SearchOptions;
use App\Models\Store;
use App\Services\Catalog\SearchOptionService;
use Illuminate\Database\Eloquent\Collection;

class SearchOptionsController extends Controller
{
    public function __construct(
        protected SearchOptionService $options,
    ) {
    }

    public function index(): Collection
    {
        $this->authorize('viewAny', SearchOptions::class);

        return SearchOptions::query()->adminFormula()->get();
    }

    public function store(SearchOptionRequest $request)
    {
        $this->authorize('create', SearchOptions::class);

        return $this->options->create($request->validated());
    }

    public function update(SearchOptionRequest $request, SearchOptions $option)
    {
        $this->authorize('update', $option);

        return $this->options->update($option, $request->validated());
    }

    public function destroy(SearchOptions $option)
    {
        $this->authorize('delete', $option);

        $option->delete();

        return $option->id;
    }

    /**
     * Attach search options to a store or a coupon.
     *
     * The request validates that exactly one of `store_id` / `coupon_id` is
     * present. We authorize both the assign capability and update on the target.
     */
    public function assign(SearchOptionAssignRequest $request)
    {
        $this->authorize('assign', SearchOptions::class);

        $target = $request->filled('coupon_id')
            ? Coupon::findOrFail($request->input('coupon_id'))
            : Store::findOrFail($request->input('store_id'));

        $this->authorize('update', $target);

        return $this->options->assign($target, $request->input('options', []));
    }
}
