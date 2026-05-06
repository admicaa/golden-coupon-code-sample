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
use App\Services\Catalog\CountryService;
use App\Services\Content\MetaTagService;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    protected $countries;
    protected $metaTags;

    public function __construct(CountryService $countries, MetaTagService $metaTags)
    {
        $this->countries = $countries;
        $this->metaTags = $metaTags;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Country::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Country::with('names')->paginate($perPage);
    }

    public function store(CountryCreateRequest $request)
    {
        return $this->countries->create($request->validated());
    }

    public function update(CountryUpdateRequest $request, Country $country)
    {
        return $this->countries->update($country, $request->validated());
    }

    public function updatePage(CountryNameUpdateRequest $request, CountryNames $name)
    {
        return $this->countries->updateName($name, $request->validated());
    }

    public function updateMetaTags(MetaTagsRequest $request, CountryNames $storePage)
    {
        $this->authorize('update', $storePage->country);

        return $this->metaTags->sync($storePage, $request->input('content'));
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
