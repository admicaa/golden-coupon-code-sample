<?php

namespace App\Http\Requests\Backend;

use App\Http\Requests\Concerns\ValidatesLocalizedPayload;
use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleCreateRequest extends FormRequest
{
    use ValidatesLocalizedPayload;

    public function authorize()
    {
        return $this->user()->can('create', Article::class);
    }

    public function rules()
    {
        if ($this->has('pages')) {
            return [
                'pages' => 'required|array|min:1',
                'pages.GB' => 'required|array',
                'pages.*' => 'required|array',
                'pages.*.slug' => 'required|string|max:191|distinct|unique:article_pages,slug',
                'pages.*.name' => 'required|string|max:191',
                'pages.*.title' => 'required|string|max:191',
                'pages.*.description' => 'nullable|string',
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

    public function withValidator($validator)
    {
        if (!$this->has('pages')) {
            return;
        }

        $validator->after(function ($validator) {
            $this->validateAllowedLanguageKeys(
                $validator,
                (array) $this->input('pages', []),
                'pages'
            );
        });
    }
}
