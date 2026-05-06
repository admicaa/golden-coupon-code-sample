<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Store;
use App\Services\MainPageSectionsService;
use Illuminate\Database\Eloquent\Collection;

class StoreSectionsController extends Controller
{
    public function __construct(
        protected MainPageSectionsService $sectionsService,
    ) {
    }

    public function getStore(Store $store): Collection
    {
        $this->authorize('update', $store);

        return $store->sections()->adminFormula()->get();
    }

    public function store(MainPageSaveRequest $request, Store $store): Collection
    {
        $this->authorize('update', $store);

        $this->sectionsService->save($request->input('sections'), 'store_id', $store->id);

        return $store->sections()->adminFormula()->get();
    }
}
