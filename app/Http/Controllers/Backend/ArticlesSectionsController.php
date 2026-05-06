<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Article;
use App\Services\MainPageSectionsService;
use Illuminate\Database\Eloquent\Collection;

class ArticlesSectionsController extends Controller
{
    public function __construct(
        protected MainPageSectionsService $sectionsService,
    ) {
    }

    public function getArticle(Article $article): Collection
    {
        $this->authorize('update', $article);

        return $article->sections()->adminFormula()->get();
    }

    public function store(MainPageSaveRequest $request, Article $article): Collection
    {
        $this->authorize('update', $article);

        $this->sectionsService->save($request->input('sections'), 'article_id', $article->id);

        return $article->sections()->adminFormula()->get();
    }
}
