<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\LinksSaveRequest;
use App\Models\Link;
use App\Services\Navigation\LinkTreeService;

class LinksController extends Controller
{
    protected $links;

    public function __construct(LinkTreeService $links)
    {
        $this->links = $links;
    }

    public function index()
    {
        $this->authorize('viewAny', Link::class);

        return Link::whereNull('link_id')->adminFormula()->get();
    }

    public function store(LinksSaveRequest $request)
    {
        $this->authorize('create', Link::class);

        $this->links->save($request->input('links'));

        return $this->index();
    }

    public function destroy(Link $link)
    {
        $this->authorize('delete', $link);

        $link->delete();

        return $link->id;
    }
}
