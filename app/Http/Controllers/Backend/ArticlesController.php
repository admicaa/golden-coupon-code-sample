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
use App\Services\Catalog\ArticleService;
use App\Services\Content\MetaTagService;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    protected $articles;
    protected $metaTags;

    public function __construct(ArticleService $articles, MetaTagService $metaTags)
    {
        $this->articles = $articles;
        $this->metaTags = $metaTags;
    }

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
        return $this->articles->create($request->validated());
    }

    public function update(ArticleUpdateRequest $request, Article $article)
    {
        return $this->articles->update($article, $request->validated(), language());
    }

    public function updatePage(ArticlePageUpdateRequest $request, ArticlePages $page)
    {
        return $this->articles->updatePage($page, $request->validated());
    }

    public function updateMetaTags(MetaTagsRequest $request, ArticlePages $page)
    {
        $this->authorize('update', $page->article);

        return $this->metaTags->sync($page, $request->input('content'));
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
        return $this->articles->replaceImage($article, $request->file('images')[0]);
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

        return $this->articles->updateImage($image, $data);
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);
        $article->delete();

        return $article->id;
    }

}
