<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CouponCreateRequest;
use App\Http\Requests\Backend\CouponPageUpdateRequest;
use App\Http\Requests\Backend\CouponUpdateRequest;
use App\Http\Requests\Backend\MetaTagsRequest;
use App\Models\Coupon;
use App\Models\CouponPages;
use App\Models\StorePageMetaTag;
use App\Queries\CouponIndexQuery;
use App\Services\Catalog\CouponService;
use App\Services\Content\MetaTagService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        protected CouponService $coupons,
        protected MetaTagService $metaTags,
    ) {
    }

    public function index(Request $request, CouponIndexQuery $query): LengthAwarePaginator
    {
        $this->authorize('viewAny', Coupon::class);

        return $query->paginate($request);
    }

    public function store(CouponCreateRequest $request)
    {
        return $this->coupons->create($request->validated());
    }

    public function update(CouponUpdateRequest $request, Coupon $coupon)
    {
        return $this->coupons->update($coupon, $request->validated());
    }

    public function updatePage(CouponPageUpdateRequest $request, CouponPages $page)
    {
        return $this->coupons->updatePage($page, $request->validated());
    }

    public function updateMetaTags(MetaTagsRequest $request, CouponPages $page)
    {
        $this->authorize('update', $page->coupon);

        return $this->metaTags->sync($page, $request->input('content'), true);
    }

    public function destroyMetaTag(StorePageMetaTag $tag)
    {
        $page = $tag->couponPage ?? abort(404);
        $this->authorize('update', $page->coupon);

        $tag->delete();

        return $tag->id;
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorize('delete', $coupon);
        $coupon->delete();

        return $coupon->id;
    }
}
