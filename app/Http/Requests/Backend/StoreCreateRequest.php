<?php

namespace App\Http\Requests\Backend;

use App\Http\Requests\Concerns\ValidatesLocalizedPayload;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreateRequest extends FormRequest
{
    use ValidatesLocalizedPayload;

    public function authorize()
    {
        return $this->user()->can('create', Store::class);
    }

    public function rules()
    {
        return [
            'country_id' => 'required|exists:countries,id',
            'pages' => 'required|array|min:1',
            'pages.GB' => 'required|array',
            'pages.*' => 'required|array',
            'pages.*.slug' => 'required|string|max:191|distinct|unique:store_pages,slug',
            'pages.*.name' => 'required|string|max:191',
            'pages.*.title' => 'required|string|max:191',
            'pages.*.body' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateAllowedLanguageKeys(
                $validator,
                (array) $this->input('pages', []),
                'pages'
            );
        });
    }
}
