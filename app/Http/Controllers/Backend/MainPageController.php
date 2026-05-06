<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Section;
use App\Services\MainPageSectionsService;

class MainPageController extends Controller
{
    protected $sectionsService;

    public function __construct(MainPageSectionsService $sectionsService)
    {
        $this->sectionsService = $sectionsService;
    }

    public function index()
    {
        $this->authorize('viewMainPage', Section::class);

        return Section::where('page_id', 1)->adminFormula()->get();
    }

    public function save(MainPageSaveRequest $request)
    {
        $this->authorize('updateMainPage', Section::class);

        $this->sectionsService->save($request->input('sections'), 'page_id', 1);

        return $this->index();
    }
}
