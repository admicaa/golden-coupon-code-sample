<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StorePageUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $page = $this->route('article');

        return $page && $this->user()->can('update', $page->store);
    }

    public function rules()
    {
        $page = $this->route('article');

        return [
            'slug' => 'required|string|max:191|unique:store_pages,slug,' . $page->id,
            'name' => 'required|string|max:191',
            'title' => 'required|string|max:191',
            'body' => 'nullable|string',
        ];
    }
}
