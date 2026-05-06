<?php

namespace Tests\Feature;

use App\Models\Languages;
use App\Models\Coupon;
use App\Models\CouponPages;
use App\Models\Section;
use App\Models\SectionContents;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CountryPageSeeder;
use Database\Seeders\CouponSeeder;
use Database\Seeders\MainPageSeeder;
use Database\Seeders\StorePageSeeder;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSeedersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Languages::insert([
            ['name' => 'English', 'shortcut' => 'GB'],
            ['name' => 'Arabic', 'shortcut' => 'AR'],
        ]);
    }

    public function test_store_and_main_page_seeders_create_bootstrap_sections(): void
    {
        $this->seed([
            CountrySeeder::class,
            StoreSeeder::class,
            CouponSeeder::class,
            StorePageSeeder::class,
            CountryPageSeeder::class,
            MainPageSeeder::class,
        ]);

        $homeSections = Section::query()
            ->where('page_id', 1)
            ->with(['pages', 'contents'])
            ->orderBy('sort')
            ->get();

        $this->assertCount(3, $homeSections);
        $this->assertSame(6, $homeSections[0]->contents->whereNotNull('coupon_id')->count());
        $this->assertSame(6, $homeSections[1]->contents->whereNotNull('store_id')->count());
        $this->assertSame(4, $homeSections[2]->contents->whereNotNull('country_id')->count());
        $this->assertSame(
            ['GB', 'AR'],
            $homeSections[0]->pages->pluck('language')->sort()->values()->all()
        );
        $this->assertSame(Coupon::count() * 2, CouponPages::count());
        $this->assertSame(0, Coupon::query()->doesntHave('pages')->count());

        $storeSectionCount = Section::query()
            ->whereNotNull('store_id')
            ->count();
        $countrySectionCount = Section::query()
            ->whereNotNull('country_id')
            ->count();

        $this->assertSame(6, $storeSectionCount);
        $this->assertSame(16, $countrySectionCount);
        $this->assertSame(0, Section::query()->whereNotNull('store_id')->whereNull('page_id')->whereHas('contents', function ($query) {
            $query->whereNull('coupon_id');
        })->count());
        $this->assertSame(0, SectionContents::query()
            ->whereNotNull('coupon_id')
            ->whereHas('coupon', function ($query) {
                $query->where('valid', false);
            })
            ->count());
    }

    public function test_page_seeders_are_idempotent(): void
    {
        $this->seed([
            CountrySeeder::class,
            StoreSeeder::class,
            CouponSeeder::class,
            StorePageSeeder::class,
            CountryPageSeeder::class,
            MainPageSeeder::class,
        ]);

        $before = [
            'sections' => Section::count(),
            'pages' => \App\Models\SectionPages::count(),
            'contents' => \App\Models\SectionContents::count(),
        ];

        $this->seed([
            StorePageSeeder::class,
            CountryPageSeeder::class,
            MainPageSeeder::class,
        ]);

        $this->assertSame($before['sections'], Section::count());
        $this->assertSame($before['pages'], \App\Models\SectionPages::count());
        $this->assertSame($before['contents'], \App\Models\SectionContents::count());
    }
}
