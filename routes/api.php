<?php

use App\Http\Controllers\Backend\AdminsController;
use App\Http\Controllers\Backend\ArticlesController;
use App\Http\Controllers\Backend\ArticlesSectionsController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\CountriesController;
use App\Http\Controllers\Backend\CountriesSectionController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\LanguagesController;
use App\Http\Controllers\Backend\LinksController;
use App\Http\Controllers\Backend\MainPageController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\SearchOptionsController;
use App\Http\Controllers\Backend\SectionsController;
use App\Http\Controllers\Backend\StoreController;
use App\Http\Controllers\Backend\StoreSectionsController;
use App\Http\Controllers\Backend\StoresMetaTagsController;
use App\Http\Controllers\Backend\TranslationFilesController;
use App\Http\Controllers\Front\MainPageController as FrontMainPageController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Home\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/js/lang/{lang}.js', [LanguageController::class, 'languageFiles']);

Route::post('/login/admin', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::prefix('front')->group(function () {
    Route::get('/', [FrontMainPageController::class, 'index']);
    Route::get('/header', [FrontMainPageController::class, 'header']);
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('{slug}', [FrontMainPageController::class, 'country']);
    Route::get('/page/{slug}', [FrontMainPageController::class, 'article'])->where('slug', '.*');
    Route::get('/store/{slug}', [FrontMainPageController::class, 'store']);
    Route::get('/coupon/{slug}', [FrontMainPageController::class, 'coupon']);
});

Route::middleware(['multiauth:admin'])->group(function () {
    Route::get('/admin/user', function () {
        return request()->user();
    });

    Route::get('/mainpage', [MainPageController::class, 'index']);
    Route::post('/mainpage/save', [MainPageController::class, 'save']);

    Route::apiResource('languages', LanguagesController::class);
    Route::apiResource('links', LinksController::class);

    Route::apiResource('countries', CountriesController::class);
    Route::prefix('countries')->group(function () {
        Route::put('/names/{name}', [CountriesController::class, 'updatePage']);
        Route::put('/metatags/{storePage}', [CountriesController::class, 'updateMetaTags']);
        Route::delete('/metatags/{tag}', [CountriesController::class, 'destroyMetaTag']);

        Route::get('/sections/{country}', [CountriesSectionController::class, 'getCountry']);
        Route::post('/sections/{country}', [CountriesSectionController::class, 'store']);
    });

    Route::prefix('sections')->group(function () {
        Route::delete('/{section}', [SectionsController::class, 'delete']);
        Route::delete('/contents/{content}', [SectionsController::class, 'deleteContent']);
    });

    Route::apiResource('search/options', SearchOptionsController::class);
    Route::post('search/options/assign', [SearchOptionsController::class, 'assign']);

    Route::apiResource('stores', StoreController::class)->except(['show']);
    Route::prefix('stores')->group(function () {
        Route::prefix('tags')->group(function () {
            Route::put('/{storePage}', [StoresMetaTagsController::class, 'update']);
            Route::delete('/{tag}', [StoresMetaTagsController::class, 'destroy']);
        });
        Route::prefix('pages')->group(function () {
            Route::put('{article}', [StoreController::class, 'updateArticle']);
        });
        Route::prefix('images')->group(function () {
            Route::post('/{store}', [StoreController::class, 'addImages']);
            Route::put('/{image}', [StoreController::class, 'editImages']);
            Route::delete('/{image}', [StoreController::class, 'deleteImage']);
        });
        Route::get('/sections/{store}', [StoreSectionsController::class, 'getStore']);
        Route::post('/sections/{store}', [StoreSectionsController::class, 'store']);
    });

    Route::apiResource('coupons', CouponController::class)->except(['show']);
    Route::prefix('coupons')->group(function () {
        Route::put('/pages/{page}', [CouponController::class, 'updatePage']);
        Route::put('/tags/{page}', [CouponController::class, 'updateMetaTags']);
        Route::delete('/tags/{tag}', [CouponController::class, 'destroyMetaTag']);
    });

    Route::apiResource('articles', ArticlesController::class)->except(['show']);
    Route::prefix('articles')->group(function () {
        Route::prefix('images')->group(function () {
            Route::post('{article}', [ArticlesController::class, 'changeImage']);
            Route::put('{image}', [ArticlesController::class, 'updateImage']);
        });
        Route::prefix('tags')->group(function () {
            Route::put('/{page}', [ArticlesController::class, 'updateMetaTags']);
            Route::delete('/{tag}', [ArticlesController::class, 'destroyMetaTag']);
        });
        Route::put('/pages/{page}', [ArticlesController::class, 'updatePage']);
        Route::get('/sections/{article}', [ArticlesSectionsController::class, 'getArticle']);
        Route::post('/sections/{article}', [ArticlesSectionsController::class, 'store']);
    });

    Route::prefix('admins')->group(function () {
        Route::get('/', [AdminsController::class, 'index']);
        Route::post('/create', [AdminsController::class, 'store']);
        Route::post('/update/{admin}', [AdminsController::class, 'update']);
        Route::delete('/delete/{admin}', [AdminsController::class, 'destroy']);
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [RolesController::class, 'index']);
        Route::get('/permissions', [RolesController::class, 'permissions']);
        Route::post('/create', [RolesController::class, 'store']);
        Route::put('/edit/{role}', [RolesController::class, 'update']);
        Route::delete('/delete/{role}', [RolesController::class, 'destroy']);
    });

    Route::get('/translation/{language}', [TranslationFilesController::class, 'getFiles']);
    Route::get('/translation', [TranslationFilesController::class, 'get']);
    Route::post('/translation', [TranslationFilesController::class, 'saveFile']);
});
