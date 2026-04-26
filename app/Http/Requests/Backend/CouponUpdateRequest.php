<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CouponUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $coupon = $this->route('coupon');

        return $coupon && $this->user()->can('update', $coupon);
    }

    public function rules()
    {
        return [
            'coupon_key' => 'required|string|max:191',
            'valid' => 'required|boolean',
            'valid_until' => 'nullable|date',
            'percentage' => 'required|numeric|min:0|max:100',
            'redirect_url' => 'required|url',
        ];
    }
}
