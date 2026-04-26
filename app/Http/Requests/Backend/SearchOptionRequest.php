<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class SearchOptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($this->isMethod('POST')) {
            return [
                'pages' => 'required|array',
                'pages.GB' => 'required|array',
                'pages.GB.name' => 'required|string|max:191',
            ];
        }

        return [
            'pages' => 'required|array',
            'pages.*' => 'required|array',
            'pages.*.name' => 'required|string|max:191',
        ];
    }
}
