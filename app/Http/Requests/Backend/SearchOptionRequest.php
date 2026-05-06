<?php

namespace App\Http\Requests\Backend;

use App\SearchOptions;
use Illuminate\Foundation\Http\FormRequest;

class SearchOptionRequest extends FormRequest
{
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
