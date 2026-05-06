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
use App\Queries\ArticleIndexQuery;
use App\Services\Catalog\ArticleService;
use App\Services\Content\MetaTagService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function __construct(
        protected ArticleService $articles,
        protected MetaTagService $metaTags,
    ) {
    }

    public function index(Request $request, ArticleIndexQuery $query): LengthAwarePaginator
    {
        $this->authorize('viewAny', Article::class);

        return $query->paginate($request);
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
        $page = $tag->articlePage ?? abort(404);
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
        $article = $image->article ?? abort(404);
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
