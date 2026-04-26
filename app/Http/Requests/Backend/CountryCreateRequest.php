<?php

namespace App\Http\Requests\Backend;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class CountryCreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Country::class);
    }

    public function rules()
    {
        return [
            'iso' => 'required|string|size:2|unique:countries,iso',
            'names' => 'required|array|min:1',
            'names.GB' => 'required|array|min:1',
            'names.GB.name' => 'required|string|max:191',
            'names.GB.header_name' => 'required|string|max:191',
        ];
    }
}
