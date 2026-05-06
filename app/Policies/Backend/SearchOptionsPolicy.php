<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\SearchOptions;
use Illuminate\Auth\Access\HandlesAuthorization;

class SearchOptionsPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-search-options');
    }

    public function view(Admin $user, SearchOptions $option)
    {
        return $this->viewAny($user);
    }

    public function create(Admin $user)
    {
        return $user->can('create-search-options');
    }

    public function update(Admin $user, SearchOptions $option)
    {
        return $user->can('edit-search-options');
    }

    public function delete(Admin $user, SearchOptions $option)
    {
        return $user->can('delete-search-options');
    }

    public function assign(Admin $user)
    {
        return $user->can('assign-search-options')
            || $user->can('edit-search-options');
    }
}
