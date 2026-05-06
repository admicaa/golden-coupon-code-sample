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
use App\Services\Catalog\CouponService;
use App\Services\Content\MetaTagService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $coupons;
    protected $metaTags;

    public function __construct(CouponService $coupons, MetaTagService $metaTags)
    {
        $this->coupons = $coupons;
        $this->metaTags = $metaTags;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Coupon::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Coupon::query()
            ->when($request->filled('store_id'), function ($query) use ($request) {
                $query->where('store_id', $request->input('store_id'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('coupon_key', 'like', '%' . $request->input('search') . '%');
            })
            ->adminFormula()
            ->paginate($perPage);
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
        $page = $tag->couponPage;
        
        if (!$page) {
            abort(404);
        }

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
