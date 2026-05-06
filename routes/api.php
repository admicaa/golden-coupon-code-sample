<?php

use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Home\LanguageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
|
| Split into three files:
|   - routes/api.php          public auth + composition (this file)
|   - routes/api_front.php    public site endpoints
|   - routes/api_admin.php    authenticated admin SPA
|
*/

Route::get('/js/lang/{lang}.js', [LanguageController::class, 'languageFiles']);

Route::post('/login/admin', [AuthController::class, 'login'])->middleware('throttle:10,1');

require __DIR__ . '/api_front.php';
require __DIR__ . '/api_admin.php';
