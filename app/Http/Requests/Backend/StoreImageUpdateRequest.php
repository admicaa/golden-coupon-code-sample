<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $image = $this->route('image');

        return $image && $image->store && $this->user()->can('update', $image->store);
    }

    public function rules()
    {
        $image = $this->route('image');

        return [
            'path' => 'required|starts_with:/storage/|unique:store_images,path,' . $image->id,
            'title' => 'nullable|string|max:191',
            'alt' => 'nullable|string|max:191',
            'is_logo' => 'nullable|boolean',
        ];
    }
}
