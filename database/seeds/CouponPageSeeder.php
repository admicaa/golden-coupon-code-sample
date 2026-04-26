<?php

use App\Models\Coupon;
use App\Models\CouponPages;
use Illuminate\Database\Seeder;

class CouponPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $coupons = Coupon::all();
        $languages = languages();
        $tags = require database_path('seeds/defaults/tags.php');
        foreach ($coupons as $coupon) {
            foreach ($languages as $lang) {


                $page = CouponPages::firstOrCreate([
                    'language' => $lang->shortcut,
                    'coupon_id' => $coupon->id
                ], [
                    'title' => $coupon->percentage,
                    'slug' => $coupon->coupon_key . '-' . $lang->shortcut . Str::random(4)
                ]);
                foreach ($tags as $tag) {
                    $page->metatags()->firstOrCreate([
                        'name' => $tag['name']
                    ], [
                        'value' => $tag['value']
                    ]);
                }
            }
        }
    }
}
