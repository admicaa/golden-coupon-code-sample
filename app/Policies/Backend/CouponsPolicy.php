<?php

namespace App\Policies\Backend;

use App\Models\Admin;
use App\Models\Coupon;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponsPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $user)
    {
        return $user->can('view-coupons');
    }

    public function create(Admin $user)
    {
        return $user->can('create-coupons');
    }

    public function update(Admin $user, Coupon $coupon)
    {
        return $user->can('edit-coupons');
    }

    public function delete(Admin $user, Coupon $coupon)
    {
        return $user->can('delete-coupons');
    }
}
