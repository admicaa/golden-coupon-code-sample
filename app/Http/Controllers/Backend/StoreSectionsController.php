<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Store;
use App\Services\MainPageSectionsService;

class StoreSectionsController extends Controller
{
    protected $sectionsService;

    public function __construct(MainPageSectionsService $sectionsService)
    {
        $this->sectionsService = $sectionsService;
    }

    public function getStore(Store $store)
    {
        $this->authorize('update', $store);

        return $store->sections()->adminFormula()->get();
    }

    public function store(MainPageSaveRequest $request, Store $store)
    {
        $this->authorize('update', $store);
        $this->sectionsService->save($request->input('sections'), 'store_id', $store->id);

        return $store->sections()->adminFormula()->get();
    }
}
