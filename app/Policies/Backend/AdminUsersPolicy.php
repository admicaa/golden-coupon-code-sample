<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminUsersPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-admins');
    }

    public function view(Admin $user, Admin $admin)
    {
        return $user->hasRole('super-admin');
    }

    public function create(Admin $user)
    {
        return $user->can('create-admins');
    }

    public function update(Admin $user, Admin $admin, $wantToUpdatePassword = false)
    {
        $sameUser = $user->id === $admin->id;
        if (!$this->canUpdatePassword($user, $wantToUpdatePassword, $sameUser)) {
            return false;
        }

        if ($sameUser) {
            return $user->can('edit-his-profile-data');
        }

        return $user->can('edit-admins') && !$admin->hasRole('super-admin');
    }

    public function delete(Admin $user, Admin $admin)
    {
        if ($admin->hasRole('super-admin')) {
            return false;
        }

        return $user->can('delete-admins');
    }

    protected function canUpdatePassword(Admin $user, $wantToUpdatePassword, $sameUser)
    {
        if (!$wantToUpdatePassword) {
            return true;
        }

        return $user->can('edit-admins-passwords')
            || ($sameUser && $user->can('edit-his-password'));
    }
}
