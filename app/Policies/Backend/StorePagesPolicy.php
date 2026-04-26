<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\StorePage;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePagesPolicy
{
    use HandlesAuthorization;

    public function update(Admin $user, StorePage $storePage)
    {
        return $user->can('edit-stores')
            || $user->can('edit-stores-' . $storePage->language);
    }
}
