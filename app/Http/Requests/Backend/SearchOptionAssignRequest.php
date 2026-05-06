<?php

namespace App\Http\Requests\Backend;

use App\SearchOptions;
use Illuminate\Foundation\Http\FormRequest;

class SearchOptionAssignRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()
            && $this->user()->can('assign', SearchOptions::class);
    }

    public function rules()
    {
        return [
            'options' => 'array',
            'options.*' => 'required|exists:search_options,id',
            'coupon_id' => 'required_without:store_id|exists:coupons,id',
            'store_id' => 'required_without:coupon_id|exists:stores,id',
        ];
    }
}
