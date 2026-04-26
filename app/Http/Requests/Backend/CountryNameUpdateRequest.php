<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CountryNameUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $name = $this->route('name');

        return $name && $this->user()->can('update', $name->country);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:191',
            'header_name' => 'required|string|max:191',
        ];
    }
}
