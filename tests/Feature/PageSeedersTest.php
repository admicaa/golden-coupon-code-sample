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

        $this->assertCount(5, $homeSections);
        $this->assertSame(2, $homeSections->where('template', 0)->count());
        $this->assertSame(2, $homeSections->where('template', 2)->count());
        $this->assertSame(1, $homeSections->where('template', 3)->count());
        $this->assertGreaterThan(0, $homeSections->filter(fn ($section) => $section->contents->whereNotNull('coupon_id')->isNotEmpty())->count());
        $this->assertGreaterThan(0, $homeSections->filter(fn ($section) => $section->contents->whereNotNull('store_id')->isNotEmpty())->count());
        $this->assertGreaterThan(0, $homeSections->filter(fn ($section) => $section->contents->whereNotNull('country_id')->isNotEmpty())->count());
        $this->assertSame(
            ['GB', 'AR'],
            $homeSections[0]->pages->pluck('language')->sort()->values()->all()
        );
        $this->assertSame(Coupon::count() * 2, CouponPages::count());
        $this->assertSame(0, Coupon::query()->doesntHave('pages')->count());

        $storeSections = Section::query()
            ->whereNotNull('store_id')
            ->with('contents')
            ->get();
        $storeSectionsByStore = $storeSections
            ->groupBy('store_id');

        $countrySections = Section::query()
            ->whereNotNull('country_id')
            ->with('contents')
            ->get();
        $countrySectionsByCountry = $countrySections
            ->groupBy('country_id');

        $storeSectionCount = $storeSections
            ->count();
        $countrySectionCount = $countrySections
            ->count();

        $this->assertSame(6, $storeSectionsByStore->count());
        $this->assertGreaterThanOrEqual(18, $storeSectionCount);
        $this->assertSame(4, $countrySectionsByCountry->count());
        $this->assertGreaterThanOrEqual(16, $countrySectionCount);
        $this->assertSame(0, $storeSectionsByStore->filter(function ($sections) {
            return $sections->where('template', 3)->count() === 0;
        })->count());
        $this->assertSame(0, $storeSectionsByStore->filter(function ($sections) {
            return $sections->filter(fn ($section) => $section->contents->whereNotNull('coupon_id')->isNotEmpty())->count() === 0;
        })->count());
        $this->assertSame(0, $countrySectionsByCountry->filter(function ($sections) {
            return $sections->where('template', 3)->count() === 0;
        })->count());
        $this->assertSame(0, $countrySectionsByCountry->filter(function ($sections) {
            return $sections->filter(fn ($section) => $section->contents->whereNotNull('store_id')->isNotEmpty())->count() === 0;
        })->count());
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
