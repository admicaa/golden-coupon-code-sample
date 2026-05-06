<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\LinksSaveRequest;
use App\Models\Link;
use App\Services\Navigation\LinkTreeService;
use Illuminate\Database\Eloquent\Collection;

class LinksController extends Controller
{
    public function __construct(
        protected LinkTreeService $links,
    ) {
    }

    public function index(): Collection
    {
        $this->authorize('viewAny', Link::class);

        return Link::whereNull('link_id')->adminFormula()->get();
    }

    public function store(LinksSaveRequest $request): Collection
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
