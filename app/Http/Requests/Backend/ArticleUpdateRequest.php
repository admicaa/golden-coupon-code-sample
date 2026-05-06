<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $article = $this->route('article');

        return $article && $this->user()->can('update', $article);
    }

    public function rules()
    {
        $article = $this->route('article');

        return [
            'name' => 'required|string|max:191',
            'title' => 'nullable|string|max:191',
            'slug' => [
                'nullable',
                'string',
                'max:191',
                Rule::unique('article_pages', 'slug')->where(function ($query) use ($article) {
                    return $query->where('article_id', '!=', $article->id);
                }),
            ],
            'description' => 'nullable|string',
            'body' => 'nullable|string',
        ];
    }
}
