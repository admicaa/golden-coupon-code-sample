<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class MetaTagsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|array|min:1',
            'content.*' => 'required|array',
            'content.*.id' => 'nullable|integer',
            'content.*.name' => 'required|string|max:191',
            'content.*.value' => 'required|string',
            'content.*.type' => 'nullable|integer',
        ];
    }
}
