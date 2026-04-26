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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
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
        $data = $request->validated();

        $coupon = DB::transaction(function () use ($data) {
            $coupon = Coupon::create([
                'store_id' => $data['store_id'],
                'redirect_url' => $data['redirect_url'],
                'percentage' => $data['percentage'],
                'coupon_key' => $data['coupon_key'],
                'valid' => $data['valid'],
                'valid_until' => $data['valid_until'] ?? null,
            ]);

            $tags = config('seo.default_meta_tags', []);
            foreach (languages() as $language) {
                $page = CouponPages::create([
                    'coupon_id' => $coupon->id,
                    'language' => $language->shortcut,
                    'title' => $data['pages']['GB']['title'],
                    'slug' => $language->shortcut === 'GB'
                        ? $data['pages']['GB']['slug']
                        : $data['pages']['GB']['slug'] . '-' . $language->shortcut,
                    'description' => $data['pages']['GB']['description'] ?? null,
                ]);

                foreach ($tags as $tag) {
                    $page->metatags()->firstOrCreate(
                        ['name' => $tag['name']],
                        ['value' => $tag['value']]
                    );
                }
            }

            return $coupon;
        });

        return $coupon->adminFormula()->find($coupon->id);
    }

    public function update(CouponUpdateRequest $request, Coupon $coupon)
    {
        $coupon->update($request->only([
            'coupon_key', 'valid', 'valid_until', 'redirect_url', 'percentage',
        ]));

        return $coupon;
    }

    public function updatePage(CouponPageUpdateRequest $request, CouponPages $page)
    {
        $page->update($request->only(['title', 'description', 'slug']));

        return $page->coupon->adminFormula()->find($page->coupon_id);
    }

    public function updateMetaTags(MetaTagsRequest $request, CouponPages $page)
    {
        $this->authorize('update', $page->coupon);

        DB::transaction(function () use ($request, $page) {
            foreach ($request->input('content') as $tag) {
                $type = $tag['type'] ?? 1;
                if (!empty($tag['id'])) {
                    $metaTag = $page->metatags()->where('id', $tag['id'])->firstOrFail();
                    $metaTag->update([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                } else {
                    $page->metatags()->create([
                        'name' => $tag['name'],
                        'value' => $tag['value'],
                        'type' => $type,
                    ]);
                }
            }
            $page->touch();
        });

        return $page->metatags;
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
