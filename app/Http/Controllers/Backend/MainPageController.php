<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Section;
use App\Services\MainPageSectionsService;
use Illuminate\Database\Eloquent\Collection;

class MainPageController extends Controller
{
    public function __construct(
        protected MainPageSectionsService $sectionsService,
    ) {
    }

    public function index(): Collection
    {
        $this->authorize('viewMainPage', Section::class);

        return Section::where('page_id', 1)->adminFormula()->get();
    }

    public function save(MainPageSaveRequest $request): Collection
    {
        $this->authorize('updateMainPage', Section::class);

        $this->sectionsService->save($request->input('sections'), 'page_id', 1);

        return $this->index();
    }
}
