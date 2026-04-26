<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticlePageUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $page = $this->route('page');

        return $page && $this->user()->can('update', $page->article);
    }

    public function rules()
    {
        $page = $this->route('page');

        return [
            'title' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'slug' => [
                'required', 'string', 'max:191',
                Rule::unique('article_pages', 'slug')
                    ->where(function ($query) use ($page) {
                        return $query->where('article_id', '!=', $page->article_id);
                    })
                    ->ignore($page->id),
            ],
            'description' => 'nullable|string',
        ];
    }
}
