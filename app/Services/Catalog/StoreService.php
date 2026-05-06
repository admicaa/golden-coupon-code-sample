<?php

namespace App\Services\Catalog;

use App\Models\Store;
use App\Models\StoreImages;
use App\Models\StorePage;
use App\Models\StorePageMetaTag;
use App\Services\Media\ImageStorageService;
use Illuminate\Support\Facades\DB;

class StoreService
{
    protected $imageStorage;

    public function __construct(ImageStorageService $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    public function create(array $data)
    {
        $store = DB::transaction(function () use ($data) {
            $store = Store::create(['country_id' => $data['country_id']]);

            foreach ($data['pages'] as $language => $pageData) {
                $page = $store->pages()->create([
                    'language' => $language,
                    'slug' => $pageData['slug'],
                    'name' => $pageData['name'],
                    'title' => $pageData['title'],
                    'body' => $pageData['body'] ?? null,
                ]);

                $this->createDefaultMetaTags($page);
            }

            return $store;
        });

        return $store->adminFormula()->find($store->id);
    }

    public function addImages(Store $store, array $images)
    {
        DB::transaction(function () use ($store, $images) {
            foreach ($images as $image) {
                $store->images()->create(
                    $this->imageStorage->storeUploadedImage($image, 'stores/' . $store->id)
                );
            }
        });

        return $store->fresh('images')->images;
    }

    public function updateImage(StoreImages $image, array $data)
    {
        $store = $image->store;

        if (!$store) {
            abort(404);
        }

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

        return $store->fresh('images')->images;
    }

    public function deleteImage(StoreImages $image)
    {
        $store = $image->store;

        if (!$store) {
            abort(404);
        }

        $this->imageStorage->deleteStoredPath($image->storage_path);
        $image->delete();

        return $store->fresh('images')->images;
    }

    public function updatePage(StorePage $page, array $data)
    {
        $page->update([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
        ]);

        return $page->fresh();
    }

    protected function createDefaultMetaTags(StorePage $page)
    {
        foreach (config('seo.default_meta_tags', []) as $tag) {
            $page->metatags()->create($tag);
        }
    }
}
