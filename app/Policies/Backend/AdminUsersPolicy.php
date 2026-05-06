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

        if (!$user->can('edit-admins') || $admin->hasRole('super-admin')) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $admin->getAllPermissions()->pluck('name')
            ->diff($user->getAllPermissions()->pluck('name'))
            ->isEmpty();
    }

    public function delete(Admin $user, Admin $admin)
    {
        if ($admin->hasRole('super-admin')) {
            return false;
        }

        if (!$user->can('delete-admins')) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $admin->getAllPermissions()->pluck('name')
            ->diff($user->getAllPermissions()->pluck('name'))
            ->isEmpty();
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
