<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class LanguagesController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $this->authorize('viewAny', Languages::class);

        return Languages::paginate(per_page($request->input('itemsPerPage')));
    }
}
