<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\ArticleCreateRequest;
use App\Http\Requests\Backend\ArticleImageRequest;
use App\Http\Requests\Backend\ArticlePageUpdateRequest;
use App\Http\Requests\Backend\ArticleUpdateRequest;
use App\Http\Requests\Backend\MetaTagsRequest;
use App\Models\Article;
use App\Models\ArticlePages;
use App\Models\StoreImages;
use App\Models\StorePageMetaTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticlesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Article::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Article::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->input('search');
                $query->whereHas('pages', function ($pages) use ($term) {
                    $pages->where('name', 'like', '%' . $term . '%')
                        ->orWhere('title', 'like', '%' . $term . '%');
                });
            })
            ->adminFormula()
            ->paginate($perPage);
    }

    public function show(Article $article)
    {
        $this->authorize('view', $article);

        return $article->adminFormula()->find($article->id);
    }

    public function store(ArticleCreateRequest $request)
    {
        $data = $request->validated();
        $pageData = $this->pageData($data);

        $article = DB::transaction(function () use ($pageData) {
            $article = Article::create();
            $tags = config('seo.default_meta_tags', []);

            foreach (languages() as $language) {
                $payload = [
                    'language' => $language->shortcut,
                    'slug' => $language->shortcut === 'GB'
                        ? $pageData['slug']
                        : $pageData['slug'] . '-' . $language->shortcut,
                    'name' => $pageData['name'],
                    'title' => $pageData['title'],
                    'description' => $pageData['description'],
                ];
                $page = $article->pages()->create($payload);

                foreach ($tags as $tag) {
                    $page->metatags()->create($tag);
                }
            }

            return $article;
        });

        return $article->adminFormula()->find($article->id);
    }

    public function update(ArticleUpdateRequest $request, Article $article)
    {
        $page = $article->pages()->where('language', language())->first()
            ?: $article->pages()->where('language', 'GB')->firstOrFail();
        $pageData = $this->pageData($request->validated(), $page);

        $page->update($pageData);

        return $article->adminFormula()->find($article->id);
    }

    public function updatePage(ArticlePageUpdateRequest $request, ArticlePages $page)
    {
        $page->update($request->only(['title', 'slug', 'description', 'name']));

        return $page->article->adminFormula()->find($page->article_id);
    }

    public function updateMetaTags(MetaTagsRequest $request, ArticlePages $page)
    {
        $this->authorize('update', $page->article);

        DB::transaction(function () use ($request, $page) {
            foreach ($request->input('content') as $tag) {
                $type = $tag['type'] ?? 1;
                if (!empty($tag['id'])) {
                    $metaTag = $page->metatags()->where('id', $tag['id'])->firstOrFail();
                    $metaTag->update([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                } else {
                    $page->metatags()->create([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                }
            }
        });

        return $page->metatags;
    }

    public function destroyMetaTag(StorePageMetaTag $tag)
    {
        $page = $tag->articlePage;
        if (!$page) {
            abort(404);
        }
        $this->authorize('update', $page->article);

        $tag->delete();

        return $tag->id;
    }

    public function changeImage(ArticleImageRequest $request, Article $article)
    {
        $image = $request->file('images')[0];
        $path = $image->store('blog/' . $article->id);
        $publicPath = Storage::url($path);

        return DB::transaction(function () use ($article, $publicPath) {
            if ($article->image) {
                $absolute = realpath(storage_path('app/' . ltrim($article->image->storage_path, '/')));
                $root = realpath(storage_path('app'));
                if ($absolute && $root && strpos($absolute, $root . DIRECTORY_SEPARATOR) === 0 && File::exists($absolute)) {
                    File::delete($absolute);
                }
                $article->image()->update([
                    'storage_path' => ltrim(str_replace('/storage/', '', $publicPath), '/'),
                    'path' => $publicPath,
                    'image_path' => url($publicPath),
                ]);

                return [$article->fresh()->image];
            }

            $created = $article->image()->create([
                'storage_path' => ltrim(str_replace('/storage/', '', $publicPath), '/'),
                'path' => $publicPath,
                'image_path' => url($publicPath),
            ]);

            return [$created];
        });
    }

    public function updateImage(Request $request, StoreImages $image)
    {
        $article = $image->article;
        if (!$article) {
            abort(404);
        }
        $this->authorize('update', $article);

        $data = $this->validate($request, [
            'path' => 'required|starts_with:/storage/|unique:store_images,path,' . $image->id,
            'title' => 'nullable|string|max:191',
            'alt' => 'nullable|string|max:191',
        ]);

        $oldPath = $image->path;
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
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);
        $article->delete();

        return $article->id;
    }

    protected function pageData(array $data, ?ArticlePages $page = null)
    {
        if (isset($data['pages']['GB'])) {
            return [
                'name' => $data['pages']['GB']['name'],
                'title' => $data['pages']['GB']['title'],
                'slug' => $data['pages']['GB']['slug'],
                'description' => $data['pages']['GB']['description'] ?? null,
            ];
        }

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
}
