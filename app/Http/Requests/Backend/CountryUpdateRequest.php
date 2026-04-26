<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CountryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $country = $this->route('country');

        return $country && $this->user()->can('update', $country);
    }

    public function rules()
    {
        $country = $this->route('country');

        return [
            'iso' => 'required|string|size:2|unique:countries,iso,' . $country->id,
        ];
    }
}
