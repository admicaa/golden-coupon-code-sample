<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\SearchOptionAssignRequest;
use App\Http\Requests\Backend\SearchOptionRequest;
use App\Models\Coupon;
use App\Models\Store;
use App\Services\Catalog\SearchOptionService;
use App\SearchOptions;

class SearchOptionsController extends Controller
{
    protected $options;

    public function __construct(SearchOptionService $options)
    {
        $this->options = $options;
    }

    public function index()
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

    public function assign(SearchOptionAssignRequest $request)
    {
        $this->authorize('assign', SearchOptions::class);

        $element = $request->filled('coupon_id')
            ? Coupon::findOrFail($request->input('coupon_id'))
            : Store::findOrFail($request->input('store_id'));

        $this->authorize('update', $element);

        return $this->options->assign($element, $request->input('options', []));
    }
}
