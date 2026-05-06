<?php

namespace App\Http\Requests\Backend;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleCreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Article::class);
    }

    public function rules()
    {
        if ($this->has('pages')) {
            return [
                'pages' => 'required|array',
                'pages.GB' => 'required|array',
                'pages.GB.slug' => 'required|string|max:191|unique:article_pages,slug',
                'pages.GB.name' => 'required|string|max:191',
                'pages.GB.title' => 'required|string|max:191',
                'pages.GB.description' => 'nullable|string',
            ];
        }

        return [
            'name' => 'required|string|max:191',
            'title' => 'nullable|string|max:191',
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('article_pages', 'slug')],
            'description' => 'nullable|string',
            'body' => 'nullable|string',
        ];
    }
}
