<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\SearchOptionAssignRequest;
use App\Http\Requests\Backend\SearchOptionRequest;
use App\Models\Coupon;
use App\Models\Store;
use App\SearchOptions;
use Illuminate\Support\Facades\DB;

class SearchOptionsController extends Controller
{
    public function index()
    {
        return SearchOptions::query()->adminFormula()->get();
    }

    public function store(SearchOptionRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $option = SearchOptions::create([]);

            foreach (languages() as $language) {
                $option->pages()->create([
                    'language' => $language->shortcut,
                    'name' => $data['pages']['GB']['name'],
                ]);
            }

            return $option->adminFormula()->find($option->id);
        });
    }

    public function update(SearchOptionRequest $request, SearchOptions $option)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $option) {
            foreach (languages() as $language) {
                if (!isset($data['pages'][$language->shortcut])) {
                    continue;
                }
                $option->pages()
                    ->where('language', $language->shortcut)
                    ->update(['name' => $data['pages'][$language->shortcut]['name']]);
            }
        });

        return $option->adminFormula()->find($option->id);
    }

    public function destroy(SearchOptions $option)
    {
        $option->delete();

        return $option->id;
    }

    public function assign(SearchOptionAssignRequest $request)
    {
        $element = $request->filled('coupon_id')
            ? Coupon::findOrFail($request->input('coupon_id'))
            : Store::findOrFail($request->input('store_id'));

        $element->options()->sync($request->input('options', []));

        return $element->options()->get();
    }
}
