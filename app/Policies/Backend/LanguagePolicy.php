<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\Languages;
use Illuminate\Auth\Access\HandlesAuthorization;

class LanguagePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-languages');
    }

    public function create(Admin $user)
    {
        return $user->can('create-languages');
    }

    public function update(Admin $user, Languages $language)
    {
        return $user->can('edit-languages');
    }

    public function delete(Admin $user, Languages $language)
    {
        return $user->can('delete-languages');
    }
}
