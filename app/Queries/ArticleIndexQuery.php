<?php

namespace App\Queries;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ArticleIndexQuery
{
    /**
     * Build the admin Articles index listing from a request.
     *
     * Filters: search (matches article_pages.name | title).
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        return Article::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = (string) $request->input('search');
                $q->whereHas('pages', function ($pages) use ($term) {
                    $pages->where('name', 'like', '%' . $term . '%')
                        ->orWhere('title', 'like', '%' . $term . '%');
                });
            })
            ->adminFormula()
            ->paginate(per_page($request->input('itemsPerPage')));
    }
}
