<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\ImagesRequest;
use App\Http\Requests\Backend\StoreCreateRequest;
use App\Http\Requests\Backend\StoreImageUpdateRequest;
use App\Http\Requests\Backend\StorePageUpdateRequest;
use App\Models\Store;
use App\Models\StoreImages;
use App\Models\StorePage;
use App\Queries\StoreIndexQuery;
use App\Services\Catalog\StoreService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $stores,
    ) {
    }

    public function index(Request $request, StoreIndexQuery $query): LengthAwarePaginator
    {
        $this->authorize('viewAny', Store::class);

        return $query->paginate($request);
    }

    public function store(StoreCreateRequest $request)
    {
        return $this->stores->create($request->validated());
    }

    public function destroy(Store $store)
    {
        $this->authorize('delete', $store);
        $id = $store->id;
        $store->delete();

        return $id;
    }

    public function addImages(ImagesRequest $request, Store $store)
    {
        $this->authorize('update', $store);

        return $this->stores->addImages($store, $request->file('images'));
    }

    public function editImages(StoreImageUpdateRequest $request, StoreImages $image)
    {
        return $this->stores->updateImage($image, $request->validated());
    }

    public function deleteImage(StoreImages $image)
    {
        $store = $image->store ?? abort(404);
        $this->authorize('update', $store);

        return $this->stores->deleteImage($image);
    }

    public function updateArticle(StorePageUpdateRequest $request, StorePage $article)
    {
        return $this->stores->updatePage($article, $request->validated());
    }
}
