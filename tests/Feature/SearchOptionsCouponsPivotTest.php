<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\SearchOptions;
use App\Models\Store;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchOptionsCouponsPivotTest extends TestCase
{
    use RefreshDatabase;

    public function test_pivot_rejects_duplicate_option_coupon_pair()
    {
        $store = Store::create([]);
        $coupon = Coupon::create(['store_id' => $store->id]);
        $option = SearchOptions::create();

        DB::table('search_options_coupons')->insert([
            'search_option_id' => $option->id,
            'coupon_id' => $coupon->id,
        ]);

        $this->expectException(QueryException::class);

        DB::table('search_options_coupons')->insert([
            'search_option_id' => $option->id,
            'coupon_id' => $coupon->id,
        ]);
    }

    public function test_pivot_rejects_duplicate_option_store_pair()
    {
        $store = Store::create([]);
        $option = SearchOptions::create();

        DB::table('search_options_coupons')->insert([
            'search_option_id' => $option->id,
            'store_id' => $store->id,
        ]);

        $this->expectException(QueryException::class);

        DB::table('search_options_coupons')->insert([
            'search_option_id' => $option->id,
            'store_id' => $store->id,
        ]);
    }

    public function test_pivot_allows_distinct_coupon_and_store_attachments()
    {
        $store = Store::create([]);
        $coupon = Coupon::create(['store_id' => $store->id]);
        $option = SearchOptions::create();

        DB::table('search_options_coupons')->insert([
            ['search_option_id' => $option->id, 'coupon_id' => $coupon->id],
            ['search_option_id' => $option->id, 'store_id' => $store->id],
        ]);

        $this->assertSame(
            2,
            DB::table('search_options_coupons')->where('search_option_id', $option->id)->count()
        );
    }
}
