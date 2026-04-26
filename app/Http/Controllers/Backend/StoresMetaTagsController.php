<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MetaTagsRequest;
use App\Models\StorePage;
use App\Models\StorePageMetaTag;
use Illuminate\Support\Facades\DB;

class StoresMetaTagsController extends Controller
{
    public function update(MetaTagsRequest $request, StorePage $storePage)
    {
        $this->authorize('update', $storePage);

        DB::transaction(function () use ($request, $storePage) {
            foreach ($request->input('content') as $tag) {
                $type = $tag['type'] ?? 1;
                if (!empty($tag['id'])) {
                    $metaTag = $storePage->metatags()->where('id', $tag['id'])->firstOrFail();
                    $metaTag->update([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                } else {
                    $storePage->metatags()->create([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                }
            }
        });

        return $storePage->metatags;
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
