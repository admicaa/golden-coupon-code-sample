<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $role = $this->route('role');
        $unique = 'unique:roles,name';
        if ($role) {
            $unique .= ',' . $role->id;
        }

        return [
            'name' => 'required|string|max:191|' . $unique,
            'permissions' => 'required|array|min:1',
            'permissions.*.id' => 'required|exists:permissions,id',
            'permissions.*.required' => 'array',
            'permissions.*.required.*.id' => 'required|exists:permissions,id',
        ];
    }
}
