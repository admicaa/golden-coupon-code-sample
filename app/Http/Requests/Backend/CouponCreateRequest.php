<?php

namespace App\Http\Requests\Backend;

use App\Http\Requests\Concerns\ValidatesLocalizedPayload;
use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;

class CouponCreateRequest extends FormRequest
{
    use ValidatesLocalizedPayload;

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
            'pages' => 'required|array|min:1',
            'pages.GB' => 'required|array',
            'pages.*' => 'required|array',
            'pages.*.title' => 'required|string|max:191',
            'pages.*.slug' => 'required|string|max:191|distinct|unique:coupon_pages,slug',
            'pages.*.description' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateAllowedLanguageKeys(
                $validator,
                (array) $this->input('pages', []),
                'pages'
            );
        });
    }
}
