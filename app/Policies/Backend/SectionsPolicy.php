<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\Country;
use App\Models\Section;
use App\Models\Store;
use Illuminate\Auth\Access\HandlesAuthorization;

class SectionsPolicy
{
    use HandlesAuthorization;

    public function delete(Admin $user, Section $section)
    {
        if ($section->page_id) {
            return $user->can('edit-main-page');
        }

        if ($section->store_id) {
            return (new StorePolicy())->update($user, Store::findOrFail($section->store_id));
        }

        if ($section->country_id) {
            return (new CountriesPolicy())->update($user, Country::findOrFail($section->country_id));
        }

        return true;
    }
}
