<?php

namespace App\Http\Requests\Backend;

use App\Http\Requests\Concerns\ValidatesLocalizedPayload;
use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class CountryCreateRequest extends FormRequest
{
    use ValidatesLocalizedPayload;

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
            'names.*' => 'required|array|min:1',
            'names.*.name' => 'required|string|max:191',
            'names.*.header_name' => 'required|string|max:191',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateAllowedLanguageKeys(
                $validator,
                (array) $this->input('names', []),
                'names'
            );
        });
    }
}
