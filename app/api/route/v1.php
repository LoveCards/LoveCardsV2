<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use yunarch\app\roles\middleware\RolesCheck;

//不鉴权
Route::get('theme/config', 'theme/Config');
Route::post('upload/image', 'upload/Images');

Route::group('', function () {
    Route::post('upload/user-images', 'upload/UserImages');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);
