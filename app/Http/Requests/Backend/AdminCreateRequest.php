<?php

namespace App\Http\Requests\Backend;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roles = collect((array) $this->input('roles', []))
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            if (empty($roles)) {
                return;
            }

            $selectedRoles = Role::query()
                ->where('guard_name', 'admin')
                ->whereIn('name', $roles)
                ->with('permissions')
                ->get();

            if ($selectedRoles->count() !== count($roles)) {
                $validator->errors()->add('roles', 'One or more selected roles are invalid.');
                return;
            }

            if ($this->user()->hasRole('super-admin')) {
                return;
            }

            $userPermissions = $this->user()->getAllPermissions()->pluck('name');

            foreach ($selectedRoles as $role) {
                if ($role->permissions->pluck('name')->diff($userPermissions)->isNotEmpty()) {
                    $validator->errors()->add('roles', 'You are not allowed to assign the role "' . $role->name . '".');
                }
            }
        });
    }
}
