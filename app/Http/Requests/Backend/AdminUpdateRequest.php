<?php

namespace App\Http\Requests\Backend;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();
        $admin = $this->route('admin');

        if (!$user || !$admin) {
            return false;
        }

        $wantsPasswordUpdate = $this->filled('password');

        if (!$user->can('update', [$admin, $wantsPasswordUpdate])) {
            return false;
        }

        if ($this->filled('roles') && !$user->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        $admin = $this->route('admin');

        $rules = [
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8',
            'avatar' => 'nullable|image|max:4000',
            'roles' => 'sometimes|array|min:1',
            'roles.*.name' => [
                'required',
                Rule::exists('roles', 'name')->where(function ($query) {
                    $query->where('guard_name', 'admin');
                }),
            ],
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roles = $this->roleNames();

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

            foreach ($selectedRoles as $role) {
                if (!$this->roleCanBeAssigned($role)) {
                    $validator->errors()->add('roles', 'You are not allowed to assign the role "' . $role->name . '".');
                }
            }
        });
    }

    protected function roleNames()
    {
        return collect((array) $this->input('roles', []))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    protected function roleCanBeAssigned(Role $role)
    {
        $user = $this->user();

        if ($role->name === 'super-admin') {
            return $user->hasRole('super-admin');
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        $userPermissions = $user->getAllPermissions()->pluck('name');
        $rolePermissions = $role->permissions->pluck('name');

        return $rolePermissions->diff($userPermissions)->isEmpty();
    }
}
