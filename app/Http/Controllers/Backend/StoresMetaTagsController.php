<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MetaTagsRequest;
use App\Models\StorePage;
use App\Models\StorePageMetaTag;
use App\Services\Content\MetaTagService;

class StoresMetaTagsController extends Controller
{
    protected $metaTags;

    public function __construct(MetaTagService $metaTags)
    {
        $this->metaTags = $metaTags;
    }

    public function update(MetaTagsRequest $request, StorePage $storePage)
    {
        $this->authorize('update', $storePage);

        return $this->metaTags->sync($storePage, $request->input('content'));
    }

    public function destroy(StorePageMetaTag $tag)
    {
        $page = $tag->storePage;
        if (!$page) {
            abort(404);
        }
        $this->authorize('update', $page);

        $tag->delete();

        return $tag->id;
    }
}
