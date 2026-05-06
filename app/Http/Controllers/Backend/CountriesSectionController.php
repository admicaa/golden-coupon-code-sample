<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Country;
use App\Services\MainPageSectionsService;
use Illuminate\Database\Eloquent\Collection;

class CountriesSectionController extends Controller
{
    public function __construct(
        protected MainPageSectionsService $sectionsService,
    ) {
    }

    public function getCountry(Country $country): Collection
    {
        $this->authorize('update', $country);

        return $country->sections()->adminFormula()->get();
    }

    public function store(MainPageSaveRequest $request, Country $country): Collection
    {
        $this->authorize('update', $country);

        $this->sectionsService->save($request->input('sections'), 'country_id', $country->id);

        return $country->sections()->adminFormula()->get();
    }
}
