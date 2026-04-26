<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $admin = $this->route('admin');
        $wantToUpdatePassword = request()->password ? true : false;

        return $user->can('update', [$admin, $wantToUpdatePassword]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $admin  = $this->route('admin');
        $rules =  [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'min:8',
            'roles' => 'array|min:1',

        ];
        if (request()->roles) {
            $rules['roles.*.name'] = ['required', Rule::exists('roles', 'name')->where(function ($query) use ($admin) {
                return $query->where('guard_name', 'admin')->where(function ($query) use ($admin) {
                    $equal = $admin->hasRole('super-admin') ? '=' : '!=';

                    return $query->where('guard_name', 'admin')->where('name', $equal, 'super-admin');
                });
            })];
        }
        if ($this->avatar) {

            $rules['avatar'] = 'image|max:4000';
        }

        return $rules;
    }
}
