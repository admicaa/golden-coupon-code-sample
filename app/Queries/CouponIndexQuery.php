<?php

namespace App\Queries;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CouponIndexQuery
{
    /**
     * Build the admin Coupons index listing from a request.
     *
     * Filters: store_id, search (matches coupons.coupon_key).
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        return Coupon::query()
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->input('store_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('coupon_key', 'like', '%' . $request->input('search') . '%');
            })
            ->adminFormula()
            ->paginate(per_page($request->input('itemsPerPage')));
    }
}
