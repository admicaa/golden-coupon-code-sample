<?php

namespace App\Http\Requests\Backend;

use App\Http\Requests\Concerns\ValidatesLocalizedPayload;
use App\SearchOptions;
use Illuminate\Foundation\Http\FormRequest;

class SearchOptionRequest extends FormRequest
{
    use ValidatesLocalizedPayload;

    public function authorize()
    {
        if (!$this->user()) {
            return false;
        }

        if ($this->isMethod('POST')) {
            return $this->user()->can('create', SearchOptions::class);
        }

        $option = $this->route('option');

        return $option && $this->user()->can('update', $option);
    }

    public function rules()
    {
        if ($this->isMethod('POST')) {
            return [
                'pages' => 'required|array|min:1',
                'pages.GB' => 'required|array',
                'pages.*' => 'required|array',
                'pages.*.name' => 'required|string|max:191',
            ];
        }

        return [
            'pages' => 'required|array',
            'pages.*' => 'required|array',
            'pages.*.name' => 'required|string|max:191',
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
