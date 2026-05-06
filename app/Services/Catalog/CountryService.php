<?php

namespace App\Services\Catalog;

use App\Models\Country;
use App\Models\CountryNames;
use Illuminate\Support\Facades\DB;

class CountryService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $country = Country::create(['iso' => $data['iso']]);

            foreach ($data['names'] as $language => $nameData) {
                $page = $country->names()->create([
                    'language' => $language,
                    'name' => $nameData['name'],
                    'header_name' => $nameData['header_name'],
                ]);

                $this->createDefaultMetaTags($page);
            }

            return $country;
        });
    }

    public function update(Country $country, array $data)
    {
        $country->update(['iso' => $data['iso']]);

        return $country;
    }

    public function updateName(CountryNames $name, array $data)
    {
        $name->update([
            'name' => $data['name'],
            'header_name' => $data['header_name'],
        ]);

        return $name->country;
    }

    protected function createDefaultMetaTags(CountryNames $page)
    {
        foreach (config('seo.default_meta_tags', []) as $tag) {
            $page->metatags()->create($tag);
        }
    }
}
