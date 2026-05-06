<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Article;
use App\Models\Coupon;
use App\Models\Country;
use App\Models\Languages;
use App\Models\Link;
use App\Models\Section;
use App\Models\Store;
use App\Models\StorePage;
use App\Models\TranslationFiles;
use App\Policies\Backend\AdminUsersPolicy;
use App\Policies\Backend\ArticlesPolicy;
use App\Policies\Backend\CouponsPolicy;
use App\Policies\Backend\CountriesPolicy;
use App\Policies\Backend\LanguagePolicy;
use App\Policies\Backend\LinkPolicy;
use App\Policies\Backend\RolePolicy;
use App\Policies\Backend\SearchOptionsPolicy;
use App\Policies\Backend\SectionsPolicy;
use App\Policies\Backend\StorePagesPolicy;
use App\Policies\Backend\StorePolicy;
use App\Policies\TranslationFilesPolicy;
use App\SearchOptions;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Admin::class => AdminUsersPolicy::class,
        Role::class => RolePolicy::class,
        Article::class => ArticlesPolicy::class,
        Languages::class => LanguagePolicy::class,
        Country::class => CountriesPolicy::class,
        Store::class => StorePolicy::class,
        StorePage::class => StorePagesPolicy::class,
        Coupon::class => CouponsPolicy::class,
        TranslationFiles::class => TranslationFilesPolicy::class,
        Section::class => SectionsPolicy::class,
        Link::class => LinkPolicy::class,
        SearchOptions::class => SearchOptionsPolicy::class,
    ];

    public function boot(): void
    {
        // `registerPolicies()` is still called automatically by the parent
        // boot() in Laravel 12, but calling it explicitly is harmless and
        // matches the pre-existing behaviour.
        $this->registerPolicies();

        // Passport >= 11 auto-registers its routes via the Passport service
        // provider, so `Passport::routes()` was removed. Token lifetimes are
        // unchanged from the legacy configuration.
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
