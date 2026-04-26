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
use App\Models\StorePageMetaTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
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
        $data = $request->validated();

        $store = DB::transaction(function () use ($data) {
            $store = Store::create(['country_id' => $data['country_id']]);
            $tags = config('seo.default_meta_tags', []);

            foreach (languages() as $language) {
                $payload = [
                    'language' => $language->shortcut,
                    'store_id' => $store->id,
                    'slug' => $language->shortcut === 'GB'
                        ? $data['pages']['GB']['slug']
                        : $data['pages']['GB']['slug'] . $language->shortcut,
                    'name' => $data['pages']['GB']['name'],
                    'title' => $data['pages']['GB']['title'],
                    'body' => $data['pages']['GB']['body'] ?? null,
                ];

                $page = StorePage::create($payload);

                foreach ($tags as $tag) {
                    $page->metatags()->create($tag);
                }
            }

            return $store;
        });

        return $store->adminFormula()->find($store->id);
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

        DB::transaction(function () use ($request, $store) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('stores/' . $store->id);
                $publicPath = Storage::url($path);

                $store->images()->create([
                    'storage_path' => ltrim(str_replace('/storage/', '', $publicPath), '/'),
                    'path' => $publicPath,
                    'image_path' => url($publicPath),
                ]);
            }
        });

        return $store->images;
    }

    public function editImages(StoreImageUpdateRequest $request, StoreImages $image)
    {
        $store = $image->store;
        if (!$store) {
            abort(404);
        }

        $data = $request->validated();
        $oldPath = $image->path;

        DB::transaction(function () use ($image, $data, $oldPath) {
            $image->update([
                'path' => $data['path'],
                'image_path' => url($data['path']),
                'title' => $data['title'] ?? $image->title,
                'alt' => $data['alt'] ?? $image->alt,
                'is_logo' => $data['is_logo'] ?? $image->is_logo,
            ]);

            if ($oldPath !== $image->path) {
                StorePageMetaTag::query()
                    ->whereIn('page_id', $image->store->pages()->pluck('id'))
                    ->where('value', url($oldPath))
                    ->update(['value' => url($image->path)]);
            }
        });

        return $store->fresh()->images;
    }

    public function deleteImage(StoreImages $image)
    {
        $store = $image->store;
        if (!$store) {
            abort(404);
        }
        $this->authorize('update', $store);

        $absolute = realpath(storage_path('app/' . ltrim($image->storage_path, '/')));
        $root = realpath(storage_path('app'));
        if ($absolute && $root && strpos($absolute, $root . DIRECTORY_SEPARATOR) === 0 && File::exists($absolute)) {
            File::delete($absolute);
        }

        $image->delete();

        return $store->images;
    }

    public function updateArticle(StorePageUpdateRequest $request, StorePage $article)
    {
        $article->update($request->only(['slug', 'name', 'title', 'body']));

        return $article->fresh();
    }
}
