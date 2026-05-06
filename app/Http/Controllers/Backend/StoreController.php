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
use App\Services\Catalog\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $stores;

    public function __construct(StoreService $stores)
    {
        $this->stores = $stores;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Store::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Store::query()
            ->when($request->filled('country_id'), function ($query) use ($request) {
                $query->where('country_id', $request->input('country_id'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->input('search');
                $query->whereHas('pages', function ($pages) use ($term) {
                    $pages->where('name', 'like', '%' . $term . '%')
                        ->orWhere('title', 'like', '%' . $term . '%');
                });
            })
            ->when($request->boolean('country'), function ($query) {
                $query->with('country');
            })
            ->adminFormula()
            ->paginate($perPage);
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
        $store = $image->store;
        if (!$store) {
            abort(404);
        }
        $this->authorize('update', $store);

        return $this->stores->deleteImage($image);
    }

    public function updateArticle(StorePageUpdateRequest $request, StorePage $article)
    {
        return $this->stores->updatePage($article, $request->validated());
    }
}
