<?php

namespace App\Http\Requests\Backend;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Store::class);
    }

    public function rules()
    {
        return [
            'country_id' => 'required|exists:countries,id',
            'pages' => 'required|array',
            'pages.GB' => 'required|array',
            'pages.GB.slug' => 'required|string|max:191|unique:store_pages,slug',
            'pages.GB.name' => 'required|string|max:191',
            'pages.GB.title' => 'required|string|max:191',
            'pages.GB.body' => 'nullable|string',
        ];
    }
}
