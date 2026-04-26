<?php

namespace App\Http\Requests\Backend;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Admin::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:admins,email',
            'password' => 'required|string|min:8',
            'roles' => 'required|array|min:1',
            'roles.*.name' => [
                'required',
                Rule::exists('roles', 'name')->where(function ($query) {
                    $query->where('guard_name', 'admin')->where('name', '!=', 'super-admin');
                }),
            ],
        ];
    }
}
