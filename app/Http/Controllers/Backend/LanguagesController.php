<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use Illuminate\Http\Request;

class LanguagesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Languages::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Languages::paginate($perPage);
    }
}
