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
use App\Models\SearchOptions;
use App\Policies\TranslationFilesPolicy;
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
        $this->registerPolicies();

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
