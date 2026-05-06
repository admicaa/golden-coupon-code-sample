<?php

namespace App\Services\Catalog;

use App\Models\Article;
use App\Models\ArticlePages;
use App\Models\StoreImages;
use App\Models\StorePageMetaTag;
use App\Services\Media\ImageStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleService
{
    protected $imageStorage;

    public function __construct(ImageStorageService $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    public function create(array $data)
    {
        $pagesData = $this->pagesData($data);

        $article = DB::transaction(function () use ($pagesData) {
            $article = Article::create();

            foreach ($pagesData as $language => $pageData) {
                $page = $article->pages()->create([
                    'language' => $language,
                    'slug' => $pageData['slug'],
                    'name' => $pageData['name'],
                    'title' => $pageData['title'],
                    'description' => $pageData['description'],
                ]);

                $this->createDefaultMetaTags($page);
            }

            return $article;
        });

        return $article->adminFormula()->find($article->id);
    }

    public function update(Article $article, array $data, $language)
    {
        $page = $article->pages()->where('language', $language)->first()
            ?: $article->pages()->where('language', 'GB')->firstOrFail();

        $page->update($this->pageData($data, $page));

        return $article->adminFormula()->find($article->id);
    }

    public function updatePage(ArticlePages $page, array $data)
    {
        $page->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'name' => $data['name'],
        ]);

        return $page->article->adminFormula()->find($page->article_id);
    }

    public function replaceImage(Article $article, $image)
    {
        $payload = $this->imageStorage->storeUploadedImage($image, 'blog/' . $article->id);

        return DB::transaction(function () use ($article, $payload) {
            if ($article->image) {
                $this->imageStorage->deleteStoredPath($article->image->storage_path);
                $article->image()->update($payload);

                return [$article->fresh()->image];
            }

            $created = $article->image()->create($payload);

            return [$created];
        });
    }

    public function updateImage(StoreImages $image, array $data)
    {
        $article = $image->article;

        if (!$article) {
            abort(404);
        }

        $oldPath = $image->path;

        return DB::transaction(function () use ($image, $data, $oldPath, $article) {
            $image->update([
                'path' => $data['path'],
                'image_path' => url($data['path']),
                'title' => $data['title'] ?? $image->title,
                'alt' => $data['alt'] ?? $image->alt,
            ]);

            if ($oldPath !== $image->path) {
                StorePageMetaTag::query()
                    ->whereIn('article_id', $article->pages()->pluck('id'))
                    ->where('value', url($oldPath))
                    ->update(['value' => url($image->path)]);
            }

            return [$article->fresh()->image];
        });
    }

    public function pageData(array $data, ?ArticlePages $page = null)
    {
        $name = $data['name'];

        return [
            'name' => $name,
            'title' => $data['title'] ?? ($page ? ($page->title === $page->name ? $name : $page->title) : $name),
            'slug' => $data['slug'] ?? ($page ? $page->slug : Str::slug($name)),
            'description' => array_key_exists('description', $data)
                ? $data['description']
                : (array_key_exists('body', $data) ? $data['body'] : ($page ? $page->description : null)),
        ];
    }

    public function pagesData(array $data)
    {
        if (!isset($data['pages'])) {
            return [
                'GB' => $this->pageData($data),
            ];
        }

        return $data['pages'];
    }

    protected function createDefaultMetaTags(ArticlePages $page)
    {
        foreach (config('seo.default_meta_tags', []) as $tag) {
            $page->metatags()->create($tag);
        }
    }
}
