<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\Store;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-stores');
    }

    public function create(Admin $user)
    {
        return $user->can('create-stores');
    }

    public function update(Admin $user, Store $store)
    {
        return $user->can('edit-stores');
    }

    public function delete(Admin $user, Store $store)
    {
        return $user->can('delete-stores');
    }
}
