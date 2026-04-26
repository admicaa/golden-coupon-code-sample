<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CountryCreateRequest;
use App\Http\Requests\Backend\CountryNameUpdateRequest;
use App\Http\Requests\Backend\CountryUpdateRequest;
use App\Http\Requests\Backend\MetaTagsRequest;
use App\Models\Country;
use App\Models\CountryNames;
use App\Models\StorePageMetaTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountriesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Country::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Country::with('names')->paginate($perPage);
    }

    public function store(CountryCreateRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $country = Country::create(['iso' => $data['iso']]);
            $tags = config('seo.default_meta_tags', []);

            foreach (languages() as $language) {
                $page = $country->names()->create([
                    'language' => $language->shortcut,
                    'name' => $data['names']['GB']['name'],
                    'header_name' => $data['names']['GB']['header_name'],
                ]);

                foreach ($tags as $tag) {
                    $page->metatags()->create($tag);
                }
            }

            return $country;
        });
    }

    public function update(CountryUpdateRequest $request, Country $country)
    {
        $country->update($request->only(['iso']));

        return $country;
    }

    public function updatePage(CountryNameUpdateRequest $request, CountryNames $name)
    {
        $name->update($request->only(['name', 'header_name']));

        return $name->country;
    }

    public function updateMetaTags(MetaTagsRequest $request, CountryNames $storePage)
    {
        $this->authorize('update', $storePage->country);

        DB::transaction(function () use ($request, $storePage) {
            foreach ($request->input('content') as $tag) {
                $type = $tag['type'] ?? 1;
                if (!empty($tag['id'])) {
                    $metaTag = $storePage->metatags()->where('id', $tag['id'])->firstOrFail();
                    $metaTag->update([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                } else {
                    $storePage->metatags()->create([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                }
            }
        });

        return $storePage->metatags;
    }

    public function destroyMetaTag(StorePageMetaTag $tag)
    {
        $name = $tag->countryName;
        if (!$name) {
            abort(404);
        }
        $this->authorize('update', $name->country);

        $tag->delete();

        return $tag->id;
    }

    public function destroy(Country $country)
    {
        $this->authorize('delete', $country);
        $country->delete();

        return $country->id;
    }
}
