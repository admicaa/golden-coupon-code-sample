<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\Country;
use Illuminate\Auth\Access\HandlesAuthorization;

class CountriesPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-countries');
    }

    public function create(Admin $user)
    {
        return $user->can('create-countries');
    }

    public function update(Admin $user, Country $country)
    {
        return $user->can('edit-countries');
    }

    public function delete(Admin $user, Country $country)
    {
        return $user->can('delete-countries');
    }
}
