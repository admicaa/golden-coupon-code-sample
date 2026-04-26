<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class ArticleImageRequest extends FormRequest
{
    public function authorize()
    {
        $article = $this->route('article');

        return $article && $this->user()->can('update', $article);
    }

    public function rules()
    {
        return [
            'images' => 'required|array|min:1|max:1',
            'images.0' => 'required|image|max:40000',
        ];
    }
}
