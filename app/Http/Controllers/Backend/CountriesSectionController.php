<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Country;
use App\Services\MainPageSectionsService;

class CountriesSectionController extends Controller
{
    protected $sectionsService;

    public function __construct(MainPageSectionsService $sectionsService)
    {
        $this->sectionsService = $sectionsService;
    }

    public function getCountry(Country $country)
    {
        $this->authorize('update', $country);

        return $country->sections()->adminFormula()->get();
    }

    public function store(MainPageSaveRequest $request, Country $country)
    {
        $this->authorize('update', $country);
        $this->sectionsService->save($request->input('sections'), 'country_id', $country->id);

        return $country->sections()->adminFormula()->get();
    }
}
