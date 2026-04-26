<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CouponPageUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $page = $this->route('page');

        return $page && $this->user()->can('update', $page->coupon);
    }

    public function rules()
    {
        $page = $this->route('page');

        return [
            'title' => 'required|string|max:191',
            'slug' => 'required|string|max:191|unique:coupon_pages,slug,' . $page->id,
            'description' => 'nullable|string',
        ];
    }
}
