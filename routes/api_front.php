<?php

use App\Http\Controllers\Front\MainPageController;
use App\Http\Controllers\Front\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('front')->group(function () {
    Route::get('/', [MainPageController::class, 'index']);
    Route::get('/header', [MainPageController::class, 'header']);
    Route::get('/search', [SearchController::class, 'index']);

    Route::get('/page/{slug}', [MainPageController::class, 'article'])->where('slug', '.*');
    Route::get('/store/{slug}', [MainPageController::class, 'store']);
    Route::get('/coupon/{slug}', [MainPageController::class, 'coupon']);

    // Country pages live at the prefix root (e.g. /front/egypt) — keep last.
    Route::get('{slug}', [MainPageController::class, 'country']);
});
