<?php

namespace App\Http\Requests\Backend;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;

class CouponCreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Coupon::class);
    }

    public function rules()
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'coupon_key' => 'required|string|max:191',
            'valid' => 'required|boolean',
            'valid_until' => 'nullable|date',
            'percentage' => 'required|numeric|min:0|max:100',
            'redirect_url' => 'required|url',
            'pages' => 'required|array',
            'pages.GB' => 'required|array',
            'pages.GB.title' => 'required|string|max:191',
            'pages.GB.slug' => 'required|string|max:191|unique:coupon_pages,slug',
            'pages.GB.description' => 'nullable|string',
        ];
    }
}
