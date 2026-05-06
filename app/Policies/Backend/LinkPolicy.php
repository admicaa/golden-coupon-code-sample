<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\Link;
use Illuminate\Auth\Access\HandlesAuthorization;

class LinkPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-main-page');
    }

    public function view(Admin $user, Link $link)
    {
        return $this->viewAny($user);
    }

    public function create(Admin $user)
    {
        return $user->can('edit-main-page');
    }

    public function delete(Admin $user, Link $link)
    {
        return $user->can('edit-main-page');
    }
}
