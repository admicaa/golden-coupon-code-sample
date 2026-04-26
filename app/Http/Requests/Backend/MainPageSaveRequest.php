<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class MainPageSaveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'sections' => 'required|array|min:1',
            'sections.*.template' => 'required|in:0,1,2,3,4',
            'sections.*.is_blog' => 'nullable|boolean',
            'sections.*.id' => 'nullable|integer',
            'sections.*.contents' => 'array',
            'sections.*.contents.*.type' => 'required|in:coupon,store,country,other,article',
            'sections.*.contents.*.id' => 'nullable|integer',
            'sections.*.contents.*.coupon_id' => 'nullable|exists:coupons,id',
            'sections.*.contents.*.store_id' => 'nullable|exists:stores,id',
            'sections.*.contents.*.country_id' => 'nullable|exists:countries,id',
            'sections.*.contents.*.page_id' => 'nullable|exists:articles,id',
            'sections.*.contents.*.url' => 'nullable|url',
            'sections.*.contents.*.image' => 'nullable|array',
            'sections.*.contents.*.image.path' => 'nullable|string',
            'sections.*.contents.*.image.title' => 'nullable|string|max:191',
            'sections.*.contents.*.image.alt' => 'nullable|string|max:191',
            'sections.*.contents.*.image.image' => 'nullable|array|max:1',
            'sections.*.contents.*.image.image.0' => 'nullable|image|max:40000',
            'sections.*.pages' => 'required|array',
        ];

        foreach (languages() as $language) {
            $key = 'sections.*.pages.' . $language->shortcut;
            $rules[$key] = 'required|array';
            $rules[$key . '.title'] = 'nullable|string|max:191';
            $rules[$key . '.subtitle'] = 'nullable|string|max:191';
            $rules[$key . '.description'] = 'nullable|string';
        }

        return $rules;
    }
}
