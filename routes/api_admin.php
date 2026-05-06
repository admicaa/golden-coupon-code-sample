<?php

use App\Http\Controllers\Backend\AdminsController;
use App\Http\Controllers\Backend\ArticlesController;
use App\Http\Controllers\Backend\ArticlesSectionsController;
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
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/user', fn () => request()->user());

    Route::get('/mainpage', [MainPageController::class, 'index']);
    Route::post('/mainpage', [MainPageController::class, 'save']);
    Route::post('/mainpage/save', [MainPageController::class, 'save']);

    Route::apiResource('languages', LanguagesController::class)->only(['index']);

    Route::apiResource('links', LinksController::class)->only(['index', 'store', 'destroy']);

    Route::apiResource('countries', CountriesController::class)->except(['show']);
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
        Route::put('/pages/{article}', [StoreController::class, 'updateArticle']);
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

    Route::apiResource('articles', ArticlesController::class);
    Route::prefix('articles')->group(function () {
        Route::prefix('images')->group(function () {
            Route::post('/{article}', [ArticlesController::class, 'changeImage']);
            Route::put('/{image}', [ArticlesController::class, 'updateImage']);
        });
        Route::prefix('tags')->group(function () {
            Route::put('/{page}', [ArticlesController::class, 'updateMetaTags']);
            Route::delete('/{tag}', [ArticlesController::class, 'destroyMetaTag']);
        });
        Route::put('/pages/{page}', [ArticlesController::class, 'updatePage']);
        Route::get('/sections/{article}', [ArticlesSectionsController::class, 'getArticle']);
        Route::post('/sections/{article}', [ArticlesSectionsController::class, 'store']);
    });

    // The verb-in-URL admin paths must precede the wildcard `POST /admins/{admin}`,
    // otherwise Laravel binds `admin = "create"` and 404s.
    Route::post('/admins/create', [AdminsController::class, 'store']);
    Route::post('/admins/update/{admin}', [AdminsController::class, 'update']);
    Route::delete('/admins/delete/{admin}', [AdminsController::class, 'destroy']);
    Route::apiResource('admins', AdminsController::class)->except(['show']);
    // POST is kept for `update` because the admin form is multipart (avatar upload).
    Route::post('/admins/{admin}', [AdminsController::class, 'update']);

    Route::get('/roles/permissions', [RolesController::class, 'permissions']);
    Route::apiResource('roles', RolesController::class)->except(['show']);
    Route::post('/roles/create', [RolesController::class, 'store']);
    Route::put('/roles/edit/{role}', [RolesController::class, 'update']);
    Route::delete('/roles/delete/{role}', [RolesController::class, 'destroy']);

    Route::get('/translation', [TranslationFilesController::class, 'get']);
    Route::post('/translation', [TranslationFilesController::class, 'saveFile']);
    Route::get('/translation/{language}', [TranslationFilesController::class, 'getFiles']);
});
