<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MainPageSaveRequest;
use App\Models\Article;
use App\Services\MainPageSectionsService;

class ArticlesSectionsController extends Controller
{
    protected $sectionsService;

    public function __construct(MainPageSectionsService $sectionsService)
    {
        $this->sectionsService = $sectionsService;
    }

    public function getArticle(Article $article)
    {
        $this->authorize('update', $article);

        return $article->sections()->adminFormula()->get();
    }

    public function store(MainPageSaveRequest $request, Article $article)
    {
        $this->authorize('update', $article);
        $this->sectionsService->save($request->input('sections'), 'article_id', $article->id);

        return $article->sections()->adminFormula()->get();
    }
}
