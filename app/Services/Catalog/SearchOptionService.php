<?php

namespace App\Services\Catalog;

use App\Models\SearchOptions;
use Illuminate\Support\Facades\DB;

class SearchOptionService
{
    public function create(array $data)
    {
        $option = DB::transaction(function () use ($data) {
            $option = SearchOptions::create([]);

            foreach ($data['pages'] as $language => $pageData) {
                $option->pages()->create([
                    'language' => $language,
                    'name' => $pageData['name'],
                ]);
            }

            return $option;
        });

        return $option->adminFormula()->find($option->id);
    }

    public function update(SearchOptions $option, array $data)
    {
        DB::transaction(function () use ($option, $data) {
            foreach ($data['pages'] as $language => $pageData) {
                $option->pages()->updateOrCreate(
                    ['language' => $language],
                    ['name' => $pageData['name']]
                );
            }
        });

        return $option->adminFormula()->find($option->id);
    }

    public function assign($element, array $options)
    {
        $element->options()->sync($options);

        return $element->options()->get();
    }
}
