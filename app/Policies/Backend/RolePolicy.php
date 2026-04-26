<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-roles');
    }

    public function create(Admin $user)
    {
        return $user->can('create-roles');
    }

    public function update(Admin $user, Role $role)
    {
        return $user->can('edit-roles') && $role->name !== 'super-admin';
    }

    public function delete(Admin $user, Role $role)
    {
        return $user->can('delete-roles') && $role->name !== 'super-admin';
    }
}
